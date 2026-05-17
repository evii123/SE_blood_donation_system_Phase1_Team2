<?php

function get_hospital_dashboard_data(PDO $pdo, int $hospitalId): array
{
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM requests WHERE hospital_id = ?');
    $stmt->execute([$hospitalId]);
    $totalRequests = (int) $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM requests WHERE hospital_id = ? AND status = 'pending'");
    $stmt->execute([$hospitalId]);
    $pendingRequests = (int) $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM requests WHERE hospital_id = ? AND status IN ('approved', 'fulfilled')");
    $stmt->execute([$hospitalId]);
    $fulfilledRequests = (int) $stmt->fetchColumn();

    $bloodStats = $pdo->query(
        'SELECT i.blood_group, SUM(i.units) AS total_units, COUNT(DISTINCT i.bank_id) AS banks
         FROM inventory i
         GROUP BY i.blood_group'
    )->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare(
        'SELECT r.id, r.blood_group, r.units_requested, r.status, b.address AS bank_address
         FROM requests r
         LEFT JOIN banks b ON r.bank_id = b.id
         WHERE r.hospital_id = ?
         ORDER BY r.requested_at DESC
         LIMIT 5'
    );
    $stmt->execute([$hospitalId]);
    $recentRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return [
        'totalRequests' => $totalRequests,
        'pendingRequests' => $pendingRequests,
        'fulfilledRequests' => $fulfilledRequests,
        'bloodStats' => $bloodStats,
        'recentRequests' => $recentRequests,
    ];
}

function get_hospital_bank_options(PDO $pdo): array
{
    return $pdo->query(
        'SELECT b.id, u.name, u.city, b.address
         FROM banks b
         JOIN users u ON u.id = b.id
         ORDER BY u.name'
    )->fetchAll(PDO::FETCH_ASSOC);
}

function create_hospital_request(PDO $pdo, int $hospitalId, int $bankId, string $bloodGroup, int $units): bool
{
    $stmt = $pdo->prepare(
        'INSERT INTO requests (hospital_id, bank_id, blood_group, units_requested)
         VALUES (?, ?, ?, ?)'
    );

    return $stmt->execute([$hospitalId, $bankId, $bloodGroup, $units]);
}

function get_hospital_request_history(PDO $pdo, int $hospitalId): array
{
    $stmt = $pdo->prepare(
        'SELECT r.id, r.blood_group, r.units_requested, r.status, r.requested_at, u.name AS bank_name, b.address
         FROM requests r
         JOIN users u ON u.id = r.bank_id
         LEFT JOIN banks b ON b.id = r.bank_id
         WHERE r.hospital_id = ?
         ORDER BY r.requested_at DESC, r.id DESC'
    );
    $stmt->execute([$hospitalId]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_hospital_bank_inventory_overview(PDO $pdo): array
{
    return $pdo->query(
        'SELECT u.name AS bank_name, u.city, b.address, i.blood_group, i.units
         FROM banks b
         JOIN users u ON u.id = b.id
         LEFT JOIN inventory i ON i.bank_id = b.id
         ORDER BY u.name, i.blood_group'
    )->fetchAll(PDO::FETCH_ASSOC);
}

function get_hospital_shared_appointments(PDO $pdo): array
{
    $timeSlotSelect = appointments_have_time_slot($pdo) ? 'a.time_slot' : "'morning' AS time_slot";
    return $pdo->query(
        "SELECT a.id, a.appointment_date, {$timeSlotSelect}, a.status, u_donor.name AS donor_name, d.blood_group, u_bank.name AS bank_name
         FROM appointments a
         JOIN users u_donor ON u_donor.id = a.donor_id
         JOIN donors d ON d.id = a.donor_id
         JOIN users u_bank ON u_bank.id = a.bank_id
         ORDER BY a.appointment_date DESC, a.id DESC
         LIMIT 100"
    )->fetchAll(PDO::FETCH_ASSOC);
}
