# Software Testing Report

## 1. Introduction
Software testing is the process of evaluating software behavior to detect defects, bugs, and unexpected outcomes.

In the Blood Donation System, testing is important because critical workflows directly affect system security, data integrity, and user trust. If authentication fails, unauthorized access may occur; if booking or fulfillment logic fails, appointment and inventory data can become incorrect.

## 2. Chosen Component
The selected components for testing are:
- `AuthService` (`validate_registration_input`, `register_user`, `authenticate_user`)
- `DonorService` (`donor_is_eligible`, `validate_appointment_booking`, `book_donor_appointment`)
- `BankService` (`process_bank_request_action`)

These were selected because they are core business-logic components:
- `AuthService` controls account validation and access.
- `DonorService` enforces donor eligibility and booking rules.
- `BankService` performs high-impact request fulfillment and inventory updates.

Any defect in these components has immediate impact on correctness and reliability of the whole system.

## 3. Test Cases

### 3.1 Test Case Table
| Test ID | Component | Scenario | Case Type | Input (summary) | Expected Result |
|---|---|---|---|---|---|
| TC01 | Auth | Allowed roles/blood groups | Normal | Helper constants | Correct role/group sets returned |
| TC02 | Auth | Invalid registration payload | Invalid | Empty name/password, bad email, invalid role/group | Validation errors returned |
| TC03 | Auth | Valid registration + login | Normal | New donor + correct password | User created and authenticated |
| TC04 | Auth | Duplicate email registration | Invalid | Same email used twice | Registration rejected |
| TC05 | Donor | Eligibility rule check | Boundary | Null/old/recent donation dates | Eligible for null/old, ineligible for recent |
| TC06 | Donor | Valid booking insert | Normal | Future date, valid bank, `morning` slot | Appointment saved as `pending` |
| TC07 | Bank | Fulfill request with enough stock | Normal | Stock `5`, requested `2` | Request `fulfilled`, inventory decremented |
| TC08 | Hospital | Create + list requests | Normal | Create request, fetch history | Created request returned in history |
| TC09 | Bank | Fulfill with insufficient stock | Invalid | Stock `1`, requested `3` | Operation fails, request status unchanged, inventory unchanged |
| TC10 | Donor | Invalid booking fields | Invalid | Past date + invalid slot `evening` | Date and slot validation errors |

### 3.2 Writing Test Code
Test code was written using separate test methods for different scenarios (valid, invalid, and boundary cases). Assertions are used to verify expected outputs.

Real test code from [`tests/run.php`](tests/run.php):

```php
test('auth: register and authenticate user', function (): void {
    $pdo = create_test_pdo();
    create_test_schema($pdo);

    $result = register_user($pdo, [
        'name' => 'Alice',
        'email' => 'alice@example.com',
        'password' => 'secret123',
        'role' => 'donor',
        'blood_group' => 'B+',
        'telephone' => '+35569999888',
        'donor_address' => 'Donor Address 1',
        'donor_id_number' => 'DNR-ALICE-001',
    ]);

    assert_same([], $result['errors']);
    assert_true(isset($result['user_id']) && $result['user_id'] > 0);

    $user = authenticate_user($pdo, 'alice@example.com', 'secret123');
    assert_true($user !== null);
    assert_same('donor', $user['role']);
});
```

```php
test('bank: fulfill request fails when stock is insufficient', function (): void {
    $pdo = create_test_pdo();
    create_test_schema($pdo);

    $bankId = seed_bank($pdo, 'B4', 'b4@example.com');
    $hospitalId = seed_hospital($pdo, 'H4', 'h4@example.com');
    $pdo->prepare('INSERT INTO inventory (bank_id, blood_group, units) VALUES (?, ?, ?)')
        ->execute([$bankId, 'B+', 1]);
    $pdo->prepare('INSERT INTO requests (hospital_id, bank_id, blood_group, units_requested, status) VALUES (?, ?, ?, ?, ?)')
        ->execute([$hospitalId, $bankId, 'B+', 3, 'approved']);
    $requestId = (int) $pdo->lastInsertId();

    $result = process_bank_request_action($pdo, $bankId, $requestId, 'fulfill');
    assert_true($result['ok'] === false);
    assert_same('approved', $pdo->query("SELECT status FROM requests WHERE id = {$requestId}")->fetchColumn());
});
```

Assertion style used:
- `assert_same(expected, actual)`
- `assert_true(condition)`

### 3.3 Running Tests
Tests are executed from project root with:

```bash
php tests/run.php
```

How to interpret results:
- `PASS`: behavior matches expected result.
- `FAIL`: assertion failed (actual output differs from expected).
- `ERROR`: runtime/setup issue (e.g., missing dependency, DB setup failure).

Result summary format:
- per-test status lines
- final summary: `Summary: X passed, Y failed`

Execution logs (actual run):

```text
[PASS] auth: allowed roles and blood groups
[PASS] auth: registration validation
[PASS] auth: register and authenticate user
[PASS] donor: eligibility rule
[PASS] donor: booking validation and insert with timeslot
[PASS] bank: fulfill request updates status and inventory
[PASS] bank: fulfill request fails when stock is insufficient
[PASS] hospital: create and list requests
[PASS] donor: booking validation rejects past date and invalid slot

Summary: 9 passed, 0 failed
```

### 3.4 Test Coverage
Test coverage is important because it shows how much of the selected component behavior is verified, especially critical paths and failure conditions.

Current coverage in this report includes:
- valid paths (successful registration, booking, and fulfillment)
- invalid paths (bad registration input, duplicate email, insufficient stock)
- boundary/edge paths (eligibility date threshold, invalid booking slot/date)

This covers the most important logic paths and common failures for the selected components, while additional UI/end-to-end coverage can be added in future phases.
