<?php

require_once __DIR__ . '/../includes/bootstrap.php';

require_role('bank');

$bankId = current_user_id();
$rows = get_bank_requests($pdo, $bankId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Requests</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3">Manage Blood Requests</h1>
        <a href="bank_dashboard.php" class="btn btn-secondary">Back</a>
    </div>

    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-striped mb-0">
                <thead><tr><th>ID</th><th>Hospital</th><th>Group</th><th>Units</th><th>Status</th><th>Requested</th><th>Action</th></tr></thead>
                <tbody>
                <?php if (!$rows): ?>
                    <tr><td colspan="7" class="text-muted">No requests found.</td></tr>
                <?php else: ?>
                    <?php foreach ($rows as $row): ?>
                    <tr>
                        <td><?php echo (int)$row['id']; ?></td>
                        <td><?php echo e($row['hospital_name']); ?></td>
                        <td><?php echo e($row['blood_group']); ?></td>
                        <td><?php echo (int)$row['units_requested']; ?></td>
                        <td><?php echo e(ucfirst($row['status'])); ?></td>
                        <td><?php echo e($row['requested_at']); ?></td>
                        <td><a class="btn btn-sm btn-outline-primary" href="process_request.php?id=<?php echo (int)$row['id']; ?>">Open</a></td>
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
