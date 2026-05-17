<?php

require_once __DIR__ . '/../includes/bootstrap.php';

require_role('hospital');

$hospitalId = current_user_id();
$rows = get_hospital_request_history($pdo, $hospitalId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request History</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3">Blood Request History</h1>
        <a href="hospital_dashboard.php" class="btn btn-secondary">Back</a>
    </div>

    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-striped mb-0">
                <thead><tr><th>ID</th><th>Bank</th><th>Blood Group</th><th>Units</th><th>Status</th><th>Requested At</th></tr></thead>
                <tbody>
                <?php if (!$rows): ?>
                    <tr><td colspan="6" class="text-muted">No requests submitted yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($rows as $row): ?>
                    <tr>
                        <td><?php echo (int)$row['id']; ?></td>
                        <td><?php echo e($row['bank_name']); ?><br><small><?php echo e($row['address'] ?? 'N/A'); ?></small></td>
                        <td><?php echo e($row['blood_group']); ?></td>
                        <td><?php echo (int)$row['units_requested']; ?></td>
                        <td><?php echo e(ucfirst($row['status'])); ?></td>
                        <td><?php echo e($row['requested_at']); ?></td>
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
