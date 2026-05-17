<?php

require_once __DIR__ . '/../includes/bootstrap.php';

require_role('bank');

$bankId = current_user_id();
$hasTimeSlot = appointments_have_time_slot($pdo);

$filters = [
    'q' => trim((string) ($_GET['q'] ?? '')),
    'status' => trim((string) ($_GET['status'] ?? '')),
    'blood_group' => trim((string) ($_GET['blood_group'] ?? '')),
    'time_slot' => trim((string) ($_GET['time_slot'] ?? '')),
    'appointment_date' => trim((string) ($_GET['appointment_date'] ?? '')),
];

$persistedQuery = http_build_query(array_filter(
    $filters,
    static fn ($value): bool => $value !== ''
));

if (is_post_request()) {
    $id = (int) ($_POST['id'] ?? 0);
    $action = trim((string) ($_POST['action'] ?? ''));

    if ($id > 0) {
        process_bank_appointment_action($pdo, $bankId, $id, $action);
    }

    redirect('manage_appointments.php' . ($persistedQuery !== '' ? '?' . $persistedQuery : ''));
}

$rows = get_bank_manage_appointments($pdo, $bankId, $filters);
$bloodGroups = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
$statuses = ['pending', 'approved', 'completed', 'cancelled'];
$timeSlots = ['morning', 'afternoon'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Appointments</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">Manage Appointments</h1>
        <a href="bank_dashboard.php" class="btn btn-secondary">Back</a>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <form method="get" class="row g-2 align-items-end">
                <div class="col-12 col-md-4">
                    <label for="q" class="form-label">Search donor info</label>
                    <input type="text" id="q" name="q" class="form-control" value="<?php echo e($filters['q']); ?>" placeholder="Name, email, phone, city, ID, address...">
                </div>
                <div class="col-6 col-md-2">
                    <label for="status" class="form-label">Status</label>
                    <select id="status" name="status" class="form-select">
                        <option value="">All</option>
                        <?php foreach ($statuses as $status): ?>
                            <option value="<?php echo e($status); ?>" <?php echo $filters['status'] === $status ? 'selected' : ''; ?>><?php echo e(ucfirst($status)); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label for="blood_group" class="form-label">Blood Group</label>
                    <select id="blood_group" name="blood_group" class="form-select">
                        <option value="">All</option>
                        <?php foreach ($bloodGroups as $group): ?>
                            <option value="<?php echo e($group); ?>" <?php echo $filters['blood_group'] === $group ? 'selected' : ''; ?>><?php echo e($group); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php if ($hasTimeSlot): ?>
                    <div class="col-6 col-md-2">
                        <label for="time_slot" class="form-label">Time Slot</label>
                        <select id="time_slot" name="time_slot" class="form-select">
                            <option value="">All</option>
                            <?php foreach ($timeSlots as $slot): ?>
                                <option value="<?php echo e($slot); ?>" <?php echo $filters['time_slot'] === $slot ? 'selected' : ''; ?>><?php echo e(ucfirst($slot)); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>
                <div class="col-6 col-md-2">
                    <label for="appointment_date" class="form-label">Date</label>
                    <input type="date" id="appointment_date" name="appointment_date" class="form-control" value="<?php echo e($filters['appointment_date']); ?>">
                </div>
                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Search</button>
                    <a href="manage_appointments.php" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-striped table-hover align-middle mb-0">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Donor</th>
                    <th>Contact</th>
                    <th>Blood</th>
                    <th>Donor ID</th>
                    <th>Address</th>
                    <th>Date</th>
                    <th>Time Slot</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php if (!$rows): ?>
                    <tr><td colspan="10" class="text-muted">No appointments found.</td></tr>
                <?php else: ?>
                    <?php foreach ($rows as $row): ?>
                        <tr>
                            <td><?php echo (int) $row['id']; ?></td>
                            <td>
                                <div class="fw-semibold"><?php echo e($row['donor_name']); ?></div>
                                <div class="text-muted small"><?php echo e($row['donor_city'] ?? ''); ?></div>
                            </td>
                            <td>
                                <div><?php echo e($row['donor_email'] ?? ''); ?></div>
                                <div class="text-muted small"><?php echo e($row['donor_phone'] ?? ''); ?></div>
                            </td>
                            <td><?php echo e($row['blood_group']); ?></td>
                            <td><?php echo e($row['donor_id_number'] ?? 'N/A'); ?></td>
                            <td><?php echo e($row['donor_address'] ?? 'N/A'); ?></td>
                            <td><?php echo e($row['appointment_date']); ?></td>
                            <td><?php echo e(ucfirst((string) ($row['time_slot'] ?? 'morning'))); ?></td>
                            <td><?php echo e(ucfirst($row['status'])); ?></td>
                            <td class="d-flex gap-1">
                                <?php if ($row['status'] === 'pending'): ?>
                                    <form method="post" class="m-0">
                                        <input type="hidden" name="id" value="<?php echo (int) $row['id']; ?>">
                                        <input type="hidden" name="action" value="approve">
                                        <button class="btn btn-sm btn-info" type="submit">Approve</button>
                                    </form>
                                <?php endif; ?>
                                <?php if (in_array($row['status'], ['pending', 'approved'], true)): ?>
                                    <form method="post" class="m-0">
                                        <input type="hidden" name="id" value="<?php echo (int) $row['id']; ?>">
                                        <input type="hidden" name="action" value="complete">
                                        <button class="btn btn-sm btn-success" type="submit">Complete</button>
                                    </form>
                                    <form method="post" class="m-0">
                                        <input type="hidden" name="id" value="<?php echo (int) $row['id']; ?>">
                                        <input type="hidden" name="action" value="cancel">
                                        <button class="btn btn-sm btn-danger" type="submit">Cancel</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
