<?php

declare(strict_types=1);

require_once __DIR__ . '/../services/auth_service.php';
require_once __DIR__ . '/../services/donor_service.php';
require_once __DIR__ . '/../services/bank_service.php';
require_once __DIR__ . '/../services/hospital_service.php';

$tests = [];

function test(string $name, callable $fn): void
{
    global $tests;
    $tests[] = [$name, $fn];
}

function assert_true(bool $condition, string $message = 'Expected condition to be true'): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function assert_same($expected, $actual, string $message = ''): void
{
    if ($expected !== $actual) {
        $prefix = $message !== '' ? $message . ' ' : '';
        throw new RuntimeException($prefix . 'Expected ' . var_export($expected, true) . ', got ' . var_export($actual, true));
    }
}

function create_test_pdo(): PDO
{
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    return $pdo;
}

function create_test_schema(PDO $pdo): void
{
    $pdo->exec(
        "CREATE TABLE users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            email TEXT UNIQUE NOT NULL,
            password TEXT NOT NULL,
            role TEXT NOT NULL,
            phone TEXT,
            city TEXT,
            created_at TEXT DEFAULT CURRENT_TIMESTAMP
        )"
    );

    $pdo->exec(
        "CREATE TABLE donors (
            id INTEGER PRIMARY KEY,
            blood_group TEXT NOT NULL,
            last_donation_date TEXT NULL
        )"
    );

    $pdo->exec(
        "CREATE TABLE hospitals (
            id INTEGER PRIMARY KEY,
            address TEXT
        )"
    );

    $pdo->exec(
        "CREATE TABLE banks (
            id INTEGER PRIMARY KEY,
            address TEXT
        )"
    );

    $pdo->exec(
        "CREATE TABLE appointments (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            donor_id INTEGER NOT NULL,
            bank_id INTEGER NOT NULL,
            appointment_date TEXT NOT NULL,
            time_slot TEXT NOT NULL DEFAULT 'morning',
            status TEXT DEFAULT 'pending',
            created_at TEXT DEFAULT CURRENT_TIMESTAMP
        )"
    );

    $pdo->exec(
        "CREATE TABLE inventory (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            bank_id INTEGER NOT NULL,
            blood_group TEXT NOT NULL,
            units INTEGER NOT NULL DEFAULT 0,
            UNIQUE (bank_id, blood_group)
        )"
    );

    $pdo->exec(
        "CREATE TABLE requests (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            hospital_id INTEGER NOT NULL,
            bank_id INTEGER NOT NULL,
            blood_group TEXT NOT NULL,
            units_requested INTEGER NOT NULL,
            status TEXT DEFAULT 'pending',
            requested_at TEXT DEFAULT CURRENT_TIMESTAMP
        )"
    );
}

function seed_user(PDO $pdo, string $name, string $email, string $role, string $password = 'secret'): int
{
    $stmt = $pdo->prepare('INSERT INTO users (name, email, password, role, city) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute([$name, $email, password_hash($password, PASSWORD_BCRYPT), $role, 'Tirane']);
    return (int) $pdo->lastInsertId();
}

function seed_bank(PDO $pdo, string $name = 'Bank A', string $email = 'bank@example.com'): int
{
    $id = seed_user($pdo, $name, $email, 'bank');
    $stmt = $pdo->prepare('INSERT INTO banks (id, address) VALUES (?, ?)');
    $stmt->execute([$id, 'Main Street']);
    return $id;
}

function seed_hospital(PDO $pdo, string $name = 'Hospital A', string $email = 'hospital@example.com'): int
{
    $id = seed_user($pdo, $name, $email, 'hospital');
    $stmt = $pdo->prepare('INSERT INTO hospitals (id, address) VALUES (?, ?)');
    $stmt->execute([$id, 'Health Ave']);
    return $id;
}

function seed_donor(PDO $pdo, string $name = 'Donor A', string $email = 'donor@example.com', string $bloodGroup = 'A+'): int
{
    $id = seed_user($pdo, $name, $email, 'donor');
    $stmt = $pdo->prepare('INSERT INTO donors (id, blood_group, last_donation_date) VALUES (?, ?, NULL)');
    $stmt->execute([$id, $bloodGroup]);
    return $id;
}

test('auth: allowed roles and blood groups', function (): void {
    assert_same(['donor', 'hospital', 'bank'], allowed_roles());
    assert_true(in_array('O-', allowed_blood_groups(), true));
});

test('auth: registration validation', function (): void {
    $errors = validate_registration_input([
        'name' => '',
        'email' => 'bad-email',
        'password' => '',
        'role' => 'invalid',
        'blood_group' => 'X',
    ]);

    assert_true(count($errors) >= 3, 'Expected multiple validation errors');

    $valid = validate_registration_input([
        'name' => 'John',
        'email' => 'john@example.com',
        'password' => 'secret',
        'role' => 'donor',
        'blood_group' => 'A+',
    ]);

    assert_same([], $valid);
});

test('auth: register and authenticate user', function (): void {
    $pdo = create_test_pdo();
    create_test_schema($pdo);

    $result = register_user($pdo, [
        'name' => 'Alice',
        'email' => 'alice@example.com',
        'password' => 'secret123',
        'role' => 'donor',
        'blood_group' => 'B+',
    ]);

    assert_same([], $result['errors']);
    assert_true(isset($result['user_id']) && $result['user_id'] > 0, 'Expected created user id');

    $user = authenticate_user($pdo, 'alice@example.com', 'secret123');
    assert_true($user !== null, 'Authentication should succeed');
    assert_same('donor', $user['role']);

    $dup = register_user($pdo, [
        'name' => 'Alice2',
        'email' => 'alice@example.com',
        'password' => 'secret123',
        'role' => 'donor',
        'blood_group' => 'B+',
    ]);
    assert_true(!empty($dup['errors']), 'Duplicate email should fail');
});

test('donor: eligibility rule', function (): void {
    assert_true(donor_is_eligible(['last_donation_date' => null]));
    assert_true(donor_is_eligible(['last_donation_date' => date('Y-m-d', strtotime('-120 days'))]));
    assert_true(!donor_is_eligible(['last_donation_date' => date('Y-m-d', strtotime('-20 days'))]));
});

test('donor: booking validation and insert with timeslot', function (): void {
    $pdo = create_test_pdo();
    create_test_schema($pdo);

    $donorId = seed_donor($pdo, 'D1', 'd1@example.com');
    $bankId = seed_bank($pdo, 'B1', 'b1@example.com');

    $date = date('Y-m-d', strtotime('+2 days'));

    $errors = validate_appointment_booking($pdo, $donorId, [
        'bank_id' => $bankId,
        'appointment_date' => $date,
        'time_slot' => 'morning',
    ], true);
    assert_same([], $errors);

    $ok = book_donor_appointment($pdo, $donorId, [
        'bank_id' => $bankId,
        'appointment_date' => $date,
        'time_slot' => 'morning',
    ], true);
    assert_true($ok, 'Booking insert should succeed');

    $stmt = $pdo->query('SELECT time_slot, status FROM appointments LIMIT 1');
    $row = $stmt->fetch();
    assert_same('morning', $row['time_slot']);
    assert_same('pending', $row['status']);
});

test('bank: fulfill request updates status and inventory', function (): void {
    $pdo = create_test_pdo();
    create_test_schema($pdo);

    $bankId = seed_bank($pdo, 'B2', 'b2@example.com');
    $hospitalId = seed_hospital($pdo, 'H1', 'h1@example.com');

    $stmt = $pdo->prepare('INSERT INTO inventory (bank_id, blood_group, units) VALUES (?, ?, ?)');
    $stmt->execute([$bankId, 'A+', 5]);

    $stmt = $pdo->prepare('INSERT INTO requests (hospital_id, bank_id, blood_group, units_requested, status) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute([$hospitalId, $bankId, 'A+', 2, 'approved']);
    $requestId = (int) $pdo->lastInsertId();

    $result = process_bank_request_action($pdo, $bankId, $requestId, 'fulfill');
    assert_true($result['ok'] === true, $result['message']);

    $status = $pdo->query("SELECT status FROM requests WHERE id = {$requestId}")->fetchColumn();
    assert_same('fulfilled', $status);

    $units = (int) $pdo->query("SELECT units FROM inventory WHERE bank_id = {$bankId} AND blood_group = 'A+'")->fetchColumn();
    assert_same(3, $units);
});

test('hospital: create and list requests', function (): void {
    $pdo = create_test_pdo();
    create_test_schema($pdo);

    $bankId = seed_bank($pdo, 'B3', 'b3@example.com');
    $hospitalId = seed_hospital($pdo, 'H2', 'h2@example.com');

    $ok = create_hospital_request($pdo, $hospitalId, $bankId, 'O+', 4);
    assert_true($ok, 'Hospital request insert should succeed');

    $history = get_hospital_request_history($pdo, $hospitalId);
    assert_same(1, count($history));
    assert_same('O+', $history[0]['blood_group']);
    assert_same(4, (int) $history[0]['units_requested']);
});

$passed = 0;
$failed = 0;

foreach ($tests as [$name, $fn]) {
    try {
        $fn();
        $passed++;
        echo "[PASS] {$name}\n";
    } catch (Throwable $e) {
        $failed++;
        echo "[FAIL] {$name}\n";
        echo '       ' . $e->getMessage() . "\n";
    }
}

echo "\nSummary: {$passed} passed, {$failed} failed\n";
exit($failed > 0 ? 1 : 0);
