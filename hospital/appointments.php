<?php

require_once __DIR__ . '/../includes/bootstrap.php';

require_role('hospital');

$rows = get_hospital_shared_appointments($pdo);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Appointments</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3">Patient Appointments (Prototype View)</h1>
        <a href="hospital_dashboard.php" class="btn btn-secondary">Back</a>
    </div>

    <div class="alert alert-info">
        This view shows donor-bank appointments from the shared system. The current schema does not store hospital-specific patient appointments yet.
    </div>

    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-striped mb-0">
                <thead><tr><th>ID</th><th>Date</th><th>Time Slot</th><th>Donor</th><th>Blood Group</th><th>Bank</th><th>Status</th></tr></thead>
                <tbody>
                <?php if (!$rows): ?>
                    <tr><td colspan="7" class="text-muted">No appointments found.</td></tr>
                <?php else: ?>
                    <?php foreach ($rows as $row): ?>
                    <tr>
                        <td><?php echo (int)$row['id']; ?></td>
                        <td><?php echo e($row['appointment_date']); ?></td>
                        <td><?php echo e(ucfirst($row['time_slot'])); ?></td>
                        <td><?php echo e($row['donor_name']); ?></td>
                        <td><?php echo e($row['blood_group']); ?></td>
                        <td><?php echo e($row['bank_name']); ?></td>
                        <td><?php echo e(ucfirst($row['status'])); ?></td>
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
