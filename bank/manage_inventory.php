<?php

require_once __DIR__ . '/../includes/bootstrap.php';

require_role('bank');

$bankId = current_user_id();
$message = '';

if (is_post_request()) {
    $bloodGroup = $_POST['blood_group'] ?? '';
    $units = max(0, (int) ($_POST['units'] ?? 0));

    if (in_array($bloodGroup, ['A+','A-','B+','B-','AB+','AB-','O+','O-'], true)) {
        upsert_bank_inventory($pdo, $bankId, $bloodGroup, $units);
        $message = 'Inventory updated.';
    }
}
$rows = get_bank_inventory($pdo, $bankId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Inventory</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3">Manage Inventory</h1>
        <a href="bank_dashboard.php" class="btn btn-secondary">Back</a>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-success"><?php echo e($message); ?></div>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-header">Add / Update Stock</div>
        <div class="card-body">
            <form method="post" class="row g-2">
                <div class="col-md-4">
                    <select name="blood_group" class="form-select" required>
                        <option value="">Blood Group</option>
                        <?php foreach (['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bg): ?>
                            <option value="<?php echo $bg; ?>"><?php echo $bg; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <input type="number" min="0" name="units" class="form-control" placeholder="Units" required>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Save</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-striped mb-0">
                <thead><tr><th>Blood Group</th><th>Units</th><th>Action</th></tr></thead>
                <tbody>
                <?php if (!$rows): ?>
                    <tr><td colspan="3" class="text-muted">No inventory rows yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($rows as $row): ?>
                    <tr>
                        <td><?php echo e($row['blood_group']); ?></td>
                        <td><?php echo (int)$row['units']; ?></td>
                        <td><a class="btn btn-sm btn-outline-primary" href="update_inventory.php?id=<?php echo (int)$row['id']; ?>">Edit</a></td>
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
