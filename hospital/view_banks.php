<?php

require_once __DIR__ . '/../includes/bootstrap.php';

require_role('hospital');

$rows = get_hospital_bank_inventory_overview($pdo);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bank Inventories</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3">Bank Inventories</h1>
        <a href="hospital_dashboard.php" class="btn btn-secondary">Back</a>
    </div>

    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-striped mb-0">
                <thead><tr><th>Bank</th><th>City</th><th>Address</th><th>Blood Group</th><th>Units</th></tr></thead>
                <tbody>
                <?php if (!$rows): ?>
                    <tr><td colspan="5" class="text-muted">No inventory data found.</td></tr>
                <?php else: ?>
                    <?php foreach ($rows as $row): ?>
                    <tr>
                        <td><?php echo e($row['bank_name']); ?></td>
                        <td><?php echo e($row['city'] ?? 'N/A'); ?></td>
                        <td><?php echo e($row['address'] ?? 'N/A'); ?></td>
                        <td><?php echo e($row['blood_group'] ?? '-'); ?></td>
                        <td><?php echo isset($row['units']) ? (int)$row['units'] : 0; ?></td>
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
