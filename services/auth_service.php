<?php

function allowed_roles(): array
{
    return ['donor', 'hospital', 'bank'];
}

function allowed_blood_groups(): array
{
    return ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
}

function find_user_by_email(PDO $pdo, string $email): ?array
{
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    return $user ?: null;
}

function validate_registration_input(array $input): array
{
    $errors = [];
    $role = $input['role'] ?? '';
    $name = trim((string) ($input['name'] ?? ''));
    $email = trim((string) ($input['email'] ?? ''));
    $password = (string) ($input['password'] ?? '');
    $bloodGroup = trim((string) ($input['blood_group'] ?? ''));
    $telephone = trim((string) ($input['telephone'] ?? ''));
    $donorAddress = trim((string) ($input['donor_address'] ?? ''));
    $donorIdNumber = trim((string) ($input['donor_id_number'] ?? ''));

    if ($name === '' || $email === '' || $password === '') {
        $errors[] = 'All fields are required.';
    }

    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email address.';
    }

    if (!in_array($role, allowed_roles(), true)) {
        $errors[] = 'Invalid role selected.';
    }

    if ($role === 'donor' && !in_array($bloodGroup, allowed_blood_groups(), true)) {
        $errors[] = 'Please select a valid blood group.';
    }

    if ($role === 'donor') {
        if ($telephone === '' || $donorAddress === '' || $donorIdNumber === '') {
            $errors[] = 'Donor telephone, donor ID, and donor address are required.';
        }

        if ($telephone !== '' && !preg_match('/^[0-9+\-\s]{6,20}$/', $telephone)) {
            $errors[] = 'Please provide a valid donor telephone number.';
        }
    }

    return $errors;
}

function donor_profile_columns(PDO $pdo): array
{
    try {
        $columns = $pdo->query('SHOW COLUMNS FROM donors')->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $exception) {
        return [];
    }

    return array_map(static fn ($column) => $column['Field'] ?? '', $columns);
}

function ensure_donor_profile_columns(PDO $pdo): void
{
    $columns = donor_profile_columns($pdo);
    if (empty($columns)) {
        return;
    }

    try {
        if (!in_array('donor_id_number', $columns, true)) {
            $pdo->exec('ALTER TABLE donors ADD COLUMN donor_id_number VARCHAR(50) NULL');
            $pdo->exec('CREATE UNIQUE INDEX donor_id_number_unique ON donors(donor_id_number)');
        }
    } catch (Throwable $exception) {
        // Ignore migration errors for compatibility with restricted DB users.
    }

    try {
        if (!in_array('address', $columns, true)) {
            $pdo->exec('ALTER TABLE donors ADD COLUMN address VARCHAR(255) NULL');
        }
    } catch (Throwable $exception) {
        // Ignore migration errors for compatibility with restricted DB users.
    }
}

function register_user(PDO $pdo, array $input): array
{
    $data = [
        'name' => trim((string) ($input['name'] ?? '')),
        'email' => trim((string) ($input['email'] ?? '')),
        'password' => (string) ($input['password'] ?? ''),
        'role' => $input['role'] ?? '',
        'blood_group' => trim((string) ($input['blood_group'] ?? '')),
        'address' => trim((string) ($input['address'] ?? '')),
        'telephone' => trim((string) ($input['telephone'] ?? '')),
        'donor_address' => trim((string) ($input['donor_address'] ?? '')),
        'donor_id_number' => trim((string) ($input['donor_id_number'] ?? '')),
    ];

    $errors = validate_registration_input($data);
    if (find_user_by_email($pdo, $data['email'])) {
        $errors[] = 'Email already exists.';
    }

    if (!empty($errors)) {
        return ['errors' => $errors];
    }

    $pdo->beginTransaction();

    try {
        $stmt = $pdo->prepare('INSERT INTO users (name, email, password, role, phone) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([
            $data['name'],
            $data['email'],
            password_hash($data['password'], PASSWORD_BCRYPT),
            $data['role'],
            $data['telephone'] !== '' ? $data['telephone'] : null,
        ]);

        $userId = (int) $pdo->lastInsertId();

        if ($data['role'] === 'donor') {
            ensure_donor_profile_columns($pdo);
            $columns = donor_profile_columns($pdo);

            if (in_array('donor_id_number', $columns, true) && in_array('address', $columns, true)) {
                $stmt = $pdo->prepare('INSERT INTO donors (id, blood_group, donor_id_number, address) VALUES (?, ?, ?, ?)');
                $stmt->execute([$userId, $data['blood_group'], $data['donor_id_number'], $data['donor_address']]);
            } else {
                $stmt = $pdo->prepare('INSERT INTO donors (id, blood_group) VALUES (?, ?)');
                $stmt->execute([$userId, $data['blood_group']]);
            }
        }

        if ($data['role'] === 'hospital') {
            $stmt = $pdo->prepare('INSERT INTO hospitals (id, address) VALUES (?, ?)');
            $stmt->execute([$userId, $data['address']]);
        }

        if ($data['role'] === 'bank') {
            $stmt = $pdo->prepare('INSERT INTO banks (id, address) VALUES (?, ?)');
            $stmt->execute([$userId, $data['address']]);
        }

        $pdo->commit();

        return ['errors' => [], 'user_id' => $userId];
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

       return ['errors' => [$exception->getMessage()]];
    }
}

function authenticate_user(PDO $pdo, string $email, string $password): ?array
{
    $user = find_user_by_email($pdo, trim($email));

    if ($user && password_verify($password, $user['password'])) {
        return $user;
    }

    return null;
}
