<?php

function appointments_have_time_slot(PDO $pdo): bool
{
    static $hasTimeSlot = null;

    if ($hasTimeSlot !== null) {
        return $hasTimeSlot;
    }

    $hasTimeSlot = (bool) $pdo->query("SHOW COLUMNS FROM appointments LIKE 'time_slot'")->fetch();

    return $hasTimeSlot;
}

function donor_extended_columns_available(PDO $pdo): bool
{
    try {
        $idColumn = (bool) $pdo->query("SHOW COLUMNS FROM donors LIKE 'donor_id_number'")->fetch();
        $addressColumn = (bool) $pdo->query("SHOW COLUMNS FROM donors LIKE 'address'")->fetch();
        return $idColumn && $addressColumn;
    } catch (Throwable $exception) {
        return false;
    }
}

function ensure_appointment_time_slot_column(PDO $pdo): bool
{
    if (appointments_have_time_slot($pdo)) {
        return true;
    }

    try {
        $pdo->exec("ALTER TABLE appointments ADD COLUMN time_slot ENUM('morning','afternoon') NOT NULL DEFAULT 'morning' AFTER appointment_date");
        return true;
    } catch (Throwable $exception) {
        return false;
    }
}

function get_donor_profile(PDO $pdo, int $donorId): ?array
{
    $extendedColumns = donor_extended_columns_available($pdo);
    $extendedSelect = $extendedColumns ? 'd.donor_id_number, d.address AS donor_address,' : "NULL AS donor_id_number, NULL AS donor_address,";

    $stmt = $pdo->prepare(
        "SELECT u.name, u.phone, u.city, d.blood_group, {$extendedSelect} d.last_donation_date
         FROM users u
         JOIN donors d ON u.id = d.id
         WHERE u.id = ?"
    );
    $stmt->execute([$donorId]);
    $donor = $stmt->fetch(PDO::FETCH_ASSOC);

    return $donor ?: null;
}

function donor_is_eligible(array $donor): bool
{
    return empty($donor['last_donation_date']) || strtotime($donor['last_donation_date']) < strtotime('-90 days');
}

function get_donor_total_appointments(PDO $pdo, int $donorId): int
{
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM appointments WHERE donor_id = ?');
    $stmt->execute([$donorId]);

    return (int) $stmt->fetchColumn();
}

function get_donor_recent_appointments(PDO $pdo, int $donorId, int $limit = 10): array
{
    $timeSlotSelect = appointments_have_time_slot($pdo) ? 'a.time_slot' : "'morning' AS time_slot";
    $stmt = $pdo->prepare(
        "SELECT a.*, {$timeSlotSelect}, u.name AS bank_name, b.address AS bank_address
         FROM appointments a
         JOIN banks b ON a.bank_id = b.id
         JOIN users u ON u.id = b.id
         WHERE a.donor_id = ?
         ORDER BY a.appointment_date DESC, a.id DESC
         LIMIT {$limit}"
    );
    $stmt->execute([$donorId]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_donor_appointment_history(PDO $pdo, int $donorId): array
{
    $timeSlotSelect = appointments_have_time_slot($pdo) ? 'a.time_slot' : "'morning' AS time_slot";
    $stmt = $pdo->prepare(
        "SELECT a.id, a.appointment_date, {$timeSlotSelect}, a.status, u.name AS bank_name, b.address AS bank_address
         FROM appointments a
         JOIN banks b ON a.bank_id = b.id
         JOIN users u ON u.id = b.id
         WHERE a.donor_id = ?
         ORDER BY a.appointment_date DESC, a.id DESC"
    );
    $stmt->execute([$donorId]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_urgent_banks_for_donor(PDO $pdo, string $city, string $bloodGroup): array
{
    $stmt = $pdo->prepare(
        'SELECT DISTINCT u.name, b.address, i.blood_group, i.units
         FROM banks b
         JOIN users u ON b.id = u.id
         JOIN inventory i ON b.id = i.bank_id
         WHERE u.city = ? AND i.blood_group = ? AND i.units < 20
         LIMIT 5'
    );
    $stmt->execute([$city, $bloodGroup]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_donor_city(PDO $pdo, int $donorId): string
{
    $stmt = $pdo->prepare('SELECT city FROM users WHERE id = ?');
    $stmt->execute([$donorId]);

    return (string) ($stmt->fetchColumn() ?: '');
}

function get_bank_options_for_donor(PDO $pdo, string $bloodGroup, string $donorCity): array
{
    $stmt = $pdo->prepare(
        'SELECT b.id, u.name, u.city, b.address,
                COALESCE(i.units, 0) AS units,
                CASE WHEN COALESCE(i.units, 0) < 10 THEN "Low Stock - Urgent!" ELSE "Available" END AS status
         FROM banks b
         JOIN users u ON b.id = u.id
         LEFT JOIN inventory i ON b.id = i.bank_id AND i.blood_group = ?
         ORDER BY (u.city = ?) DESC, u.name'
    );
    $stmt->execute([$bloodGroup, $donorCity]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function validate_appointment_booking(PDO $pdo, int $donorId, array $input, bool $hasTimeSlot): array
{
    $errors = [];
    $bankId = (int) ($input['bank_id'] ?? 0);
    $appointmentDate = (string) ($input['appointment_date'] ?? '');
    $timeSlot = (string) ($input['time_slot'] ?? '');

    if (!$bankId || $appointmentDate === '' || $timeSlot === '') {
        $errors[] = 'Please select bank, date, and time slot.';
        return $errors;
    }

    if (strtotime($appointmentDate) < strtotime(date('Y-m-d'))) {
        $errors[] = 'Date must be today or a future date.';
    }

    if (!in_array($timeSlot, ['morning', 'afternoon'], true)) {
        $errors[] = 'Please choose a valid time slot.';
    }

    if (!empty($errors)) {
        return $errors;
    }

    if ($hasTimeSlot) {
        $stmt = $pdo->prepare(
            "SELECT COUNT(*) FROM appointments
             WHERE donor_id = ? AND appointment_date = ? AND time_slot = ? AND status IN ('pending','approved')"
        );
        $stmt->execute([$donorId, $appointmentDate, $timeSlot]);
    } else {
        $stmt = $pdo->prepare(
            "SELECT COUNT(*) FROM appointments
             WHERE donor_id = ? AND appointment_date = ? AND status IN ('pending','approved')"
        );
        $stmt->execute([$donorId, $appointmentDate]);
    }

    if ((int) $stmt->fetchColumn() > 0) {
        $errors[] = 'You already have an active appointment in this slot on this date.';
    }

    if ($hasTimeSlot) {
        $stmt = $pdo->prepare(
            "SELECT COUNT(*) FROM appointments
             WHERE bank_id = ? AND appointment_date = ? AND time_slot = ? AND status != 'cancelled'"
        );
        $stmt->execute([$bankId, $appointmentDate, $timeSlot]);
    } else {
        $stmt = $pdo->prepare(
            "SELECT COUNT(*) FROM appointments
             WHERE bank_id = ? AND appointment_date = ? AND status != 'cancelled'"
        );
        $stmt->execute([$bankId, $appointmentDate]);
    }

    if ((int) $stmt->fetchColumn() >= 20) {
        $errors[] = 'No more seats available in this slot on this date. Choose another date or slot.';
    }

    return $errors;
}

function book_donor_appointment(PDO $pdo, int $donorId, array $input, bool $hasTimeSlot): bool
{
    $bankId = (int) ($input['bank_id'] ?? 0);
    $appointmentDate = (string) ($input['appointment_date'] ?? '');
    $timeSlot = (string) ($input['time_slot'] ?? '');

    if ($hasTimeSlot) {
        $stmt = $pdo->prepare(
            "INSERT INTO appointments (donor_id, bank_id, appointment_date, time_slot, status)
             VALUES (?, ?, ?, ?, 'pending')"
        );

        return $stmt->execute([$donorId, $bankId, $appointmentDate, $timeSlot]);
    }

    $stmt = $pdo->prepare(
        "INSERT INTO appointments (donor_id, bank_id, appointment_date, status)
         VALUES (?, ?, ?, 'pending')"
    );

    return $stmt->execute([$donorId, $bankId, $appointmentDate]);
}

function get_donor_city_and_blood_group(PDO $pdo, int $donorId): ?array
{
    $stmt = $pdo->prepare(
        'SELECT u.city, d.blood_group
         FROM users u
         JOIN donors d ON u.id = d.id
         WHERE u.id = ?'
    );
    $stmt->execute([$donorId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row ?: null;
}

function search_banks_for_donor(PDO $pdo, string $bloodGroup, string $city = ''): array
{
    $stmt = $pdo->prepare(
        'SELECT b.id, u.name, u.city, b.address, COALESCE(i.units, 0) AS units
         FROM banks b
         JOIN users u ON u.id = b.id
         LEFT JOIN inventory i ON i.bank_id = b.id AND i.blood_group = ?
         WHERE (? = "" OR u.city = ?)
         ORDER BY units ASC, u.name ASC'
    );
    $stmt->execute([$bloodGroup, $city, $city]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function cancel_pending_donor_appointment(PDO $pdo, int $donorId, int $appointmentId): bool
{
    $stmt = $pdo->prepare(
        "UPDATE appointments
         SET status = 'cancelled'
         WHERE id = ? AND donor_id = ? AND status = 'pending'"
    );

    return $stmt->execute([$appointmentId, $donorId]);
}
