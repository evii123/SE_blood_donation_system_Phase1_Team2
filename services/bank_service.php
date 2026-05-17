<?php

function get_bank_dashboard_data(PDO $pdo, int $bankId): array
{
    $totalDonors = (int) $pdo->query(
        "SELECT COUNT(DISTINCT u.id)
         FROM users u
         JOIN donors d ON u.id = d.id
         WHERE u.role = 'donor'"
    )->fetchColumn();

    $stmt = $pdo->prepare('SELECT COUNT(*) FROM inventory WHERE bank_id = ?');
    $stmt->execute([$bankId]);
    $totalInventory = (int) $stmt->fetchColumn();

    $donorStats = $pdo->query(
        "SELECT d.blood_group, COUNT(*) AS count
         FROM donors d
         JOIN users u ON d.id = u.id
         WHERE u.role = 'donor'
         GROUP BY d.blood_group"
    )->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare(
        'SELECT id, blood_group, units, DATE_ADD(NOW(), INTERVAL 7 DAY) AS expiry_check
         FROM inventory
         WHERE bank_id = ?'
    );
    $stmt->execute([$bankId]);
    $inventory = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM requests WHERE bank_id = ? AND status = 'pending'");
    $stmt->execute([$bankId]);
    $totalRequests = (int) $stmt->fetchColumn();

    $stmt = $pdo->prepare('SELECT COUNT(*) FROM inventory WHERE bank_id = ? AND units < 10');
    $stmt->execute([$bankId]);
    $lowStock = (int) $stmt->fetchColumn();

    $timeSlotSelect = appointments_have_time_slot($pdo) ? 'a.time_slot' : "'morning' AS time_slot";
    $stmt = $pdo->prepare(
        "SELECT a.appointment_date, {$timeSlotSelect}, u.name AS donor_name
         FROM appointments a
         JOIN users u ON a.donor_id = u.id
         WHERE a.bank_id = ? AND a.status = 'approved'
         ORDER BY a.appointment_date
         LIMIT 5"
    );
    $stmt->execute([$bankId]);
    $upcomingAppointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare(
        'SELECT r.id, r.blood_group, r.units_requested, u.name AS hospital_name
         FROM requests r
         JOIN users u ON r.hospital_id = u.id
         WHERE r.bank_id = ?
         ORDER BY r.requested_at DESC
         LIMIT 5'
    );
    $stmt->execute([$bankId]);
    $recentRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return [
        'totalDonors' => $totalDonors,
        'totalInventory' => $totalInventory,
        'donorStats' => $donorStats,
        'inventory' => $inventory,
        'totalRequests' => $totalRequests,
        'lowStock' => $lowStock,
        'upcomingAppointments' => $upcomingAppointments,
        'recentRequests' => $recentRequests,
    ];
}

function get_bank_inventory(PDO $pdo, int $bankId): array
{
    $stmt = $pdo->prepare('SELECT id, blood_group, units FROM inventory WHERE bank_id = ? ORDER BY blood_group');
    $stmt->execute([$bankId]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function upsert_bank_inventory(PDO $pdo, int $bankId, string $bloodGroup, int $units): bool
{
    $stmt = $pdo->prepare(
        'INSERT INTO inventory (bank_id, blood_group, units)
         VALUES (?, ?, ?)
         ON DUPLICATE KEY UPDATE units = VALUES(units)'
    );

    return $stmt->execute([$bankId, $bloodGroup, $units]);
}

function get_bank_inventory_item(PDO $pdo, int $bankId, int $inventoryId): ?array
{
    $stmt = $pdo->prepare('SELECT id, blood_group, units FROM inventory WHERE id = ? AND bank_id = ?');
    $stmt->execute([$inventoryId, $bankId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row ?: null;
}

function update_bank_inventory_units(PDO $pdo, int $bankId, int $inventoryId, int $units): bool
{
    $stmt = $pdo->prepare('UPDATE inventory SET units = ? WHERE id = ? AND bank_id = ?');

    return $stmt->execute([$units, $inventoryId, $bankId]);
}

function bank_donor_extended_columns_available(PDO $pdo): bool
{
    try {
        $idColumn = (bool) $pdo->query("SHOW COLUMNS FROM donors LIKE 'donor_id_number'")->fetch();
        $addressColumn = (bool) $pdo->query("SHOW COLUMNS FROM donors LIKE 'address'")->fetch();
        return $idColumn && $addressColumn;
    } catch (Throwable $exception) {
        return false;
    }
}

function get_bank_manage_appointments(PDO $pdo, int $bankId, array $filters = []): array
{
    $hasTimeSlot = appointments_have_time_slot($pdo);
    $timeSlotSelect = $hasTimeSlot ? 'a.time_slot' : "'morning' AS time_slot";

    $hasExtendedColumns = bank_donor_extended_columns_available($pdo);
    $extendedSelect = $hasExtendedColumns
        ? 'd.donor_id_number, d.address AS donor_address'
        : "NULL AS donor_id_number, NULL AS donor_address";

    $where = ['a.bank_id = ?'];
    $params = [$bankId];

    $queryText = trim((string) ($filters['q'] ?? ''));
    if ($queryText !== '') {
        $searchParts = [
            'u.name LIKE ?',
            'u.email LIKE ?',
            'u.phone LIKE ?',
            'u.city LIKE ?',
            'd.blood_group LIKE ?',
            'a.appointment_date LIKE ?',
        ];
        if ($hasExtendedColumns) {
            $searchParts[] = 'd.donor_id_number LIKE ?';
            $searchParts[] = 'd.address LIKE ?';
        }

        $where[] = '(' . implode(' OR ', $searchParts) . ')';
        $like = '%' . $queryText . '%';
        $params = array_merge($params, array_fill(0, count($searchParts), $like));
    }

    $status = trim((string) ($filters['status'] ?? ''));
    if ($status !== '' && in_array($status, ['pending', 'approved', 'completed', 'cancelled'], true)) {
        $where[] = 'a.status = ?';
        $params[] = $status;
    }

    $bloodGroup = trim((string) ($filters['blood_group'] ?? ''));
    if ($bloodGroup !== '' && in_array($bloodGroup, ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'], true)) {
        $where[] = 'd.blood_group = ?';
        $params[] = $bloodGroup;
    }

    $slot = trim((string) ($filters['time_slot'] ?? ''));
    if ($slot !== '' && in_array($slot, ['morning', 'afternoon'], true) && $hasTimeSlot) {
        $where[] = 'a.time_slot = ?';
        $params[] = $slot;
    }

    $date = trim((string) ($filters['appointment_date'] ?? ''));
    if ($date !== '') {
        $where[] = 'a.appointment_date = ?';
        $params[] = $date;
    }

    $sql = "SELECT a.id, a.appointment_date, {$timeSlotSelect}, a.status,
                   u.name AS donor_name, u.email AS donor_email, u.phone AS donor_phone, u.city AS donor_city,
                   d.blood_group, {$extendedSelect}
            FROM appointments a
            JOIN users u ON u.id = a.donor_id
            JOIN donors d ON d.id = a.donor_id
            WHERE " . implode(' AND ', $where) . "
            ORDER BY a.appointment_date DESC, a.id DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function process_bank_appointment_action(PDO $pdo, int $bankId, int $appointmentId, string $action): bool
{
    if (!in_array($action, ['approve', 'complete', 'cancel'], true)) {
        return false;
    }

    if ($action === 'approve') {
        $stmt = $pdo->prepare("UPDATE appointments SET status = 'approved' WHERE id = ? AND bank_id = ? AND status = 'pending'");
        return $stmt->execute([$appointmentId, $bankId]);
    }

    if ($action === 'cancel') {
        $stmt = $pdo->prepare("UPDATE appointments SET status = 'cancelled' WHERE id = ? AND bank_id = ? AND status IN ('pending','approved')");
        return $stmt->execute([$appointmentId, $bankId]);
    }

    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare(
            "SELECT a.id, a.donor_id, d.blood_group
             FROM appointments a
             JOIN donors d ON d.id = a.donor_id
             WHERE a.id = ? AND a.bank_id = ? AND a.status IN ('approved','pending')
             FOR UPDATE"
        );
        $stmt->execute([$appointmentId, $bankId]);
        $appointment = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$appointment) {
            $pdo->rollBack();
            return false;
        }

        $stmt = $pdo->prepare("UPDATE appointments SET status = 'completed' WHERE id = ? AND bank_id = ?");
        $stmt->execute([$appointmentId, $bankId]);

        $stmt = $pdo->prepare('UPDATE donors SET last_donation_date = CURDATE() WHERE id = ?');
        $stmt->execute([(int) $appointment['donor_id']]);

        $stmt = $pdo->prepare(
            'INSERT INTO inventory (bank_id, blood_group, units)
             VALUES (?, ?, 1)
             ON DUPLICATE KEY UPDATE units = units + 1'
        );
        $stmt->execute([$bankId, $appointment['blood_group']]);

        $pdo->commit();
        return true;
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        return false;
    }
}

function get_bank_requests(PDO $pdo, int $bankId): array
{
    $stmt = $pdo->prepare(
        'SELECT r.id, r.blood_group, r.units_requested, r.status, r.requested_at, u.name AS hospital_name
         FROM requests r
         JOIN users u ON u.id = r.hospital_id
         WHERE r.bank_id = ?
         ORDER BY r.requested_at DESC, r.id DESC'
    );
    $stmt->execute([$bankId]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_bank_request(PDO $pdo, int $bankId, int $requestId): ?array
{
    $stmt = $pdo->prepare(
        'SELECT r.*, u.name AS hospital_name
         FROM requests r
         JOIN users u ON u.id = r.hospital_id
         WHERE r.id = ? AND r.bank_id = ?'
    );
    $stmt->execute([$requestId, $bankId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row ?: null;
}

function process_bank_request_action(PDO $pdo, int $bankId, int $requestId, string $action): array
{
    $request = get_bank_request($pdo, $bankId, $requestId);
    if (!$request) {
        return ['ok' => false, 'message' => 'Request not found.'];
    }

    if ($action === 'approve' && $request['status'] === 'pending') {
        $stmt = $pdo->prepare("UPDATE requests SET status = 'approved' WHERE id = ? AND bank_id = ?");
        $stmt->execute([$requestId, $bankId]);
        return ['ok' => true, 'message' => 'Request approved.'];
    }

    if ($action === 'reject' && in_array($request['status'], ['pending', 'approved'], true)) {
        $stmt = $pdo->prepare("UPDATE requests SET status = 'rejected' WHERE id = ? AND bank_id = ?");
        $stmt->execute([$requestId, $bankId]);
        return ['ok' => true, 'message' => 'Request rejected.'];
    }

    if ($action === 'fulfill' && in_array($request['status'], ['pending', 'approved'], true)) {
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare('SELECT id, units FROM inventory WHERE bank_id = ? AND blood_group = ? FOR UPDATE');
            $stmt->execute([$bankId, $request['blood_group']]);
            $stock = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$stock || (int) $stock['units'] < (int) $request['units_requested']) {
                $pdo->rollBack();
                return ['ok' => false, 'message' => 'Not enough units to fulfill this request.'];
            }

            $stmt = $pdo->prepare('UPDATE inventory SET units = units - ? WHERE id = ?');
            $stmt->execute([(int) $request['units_requested'], (int) $stock['id']]);

            $stmt = $pdo->prepare("UPDATE requests SET status = 'fulfilled' WHERE id = ? AND bank_id = ?");
            $stmt->execute([$requestId, $bankId]);

            $pdo->commit();
            return ['ok' => true, 'message' => 'Request fulfilled and inventory updated.'];
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            return ['ok' => false, 'message' => 'Unable to process request right now.'];
        }
    }

    return ['ok' => false, 'message' => 'No action taken for current request status.'];
}
