<?php

require_once __DIR__ . '/../includes/bootstrap.php';

require_role('bank');

$bankId = current_user_id();
$id = (int) ($_GET['id'] ?? 0);
$row = get_bank_inventory_item($pdo, $bankId, $id);

if (!$row) {
    redirect('manage_inventory.php');
}

if (is_post_request()) {
    $units = max(0, (int) ($_POST['units'] ?? 0));
    update_bank_inventory_units($pdo, $bankId, $id, $units);
    redirect('manage_inventory.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Inventory</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4" style="max-width: 600px;">
    <h1 class="h3 mb-3">Update <?php echo e($row['blood_group']); ?> Stock</h1>
    <form method="post" class="card card-body">
        <label class="form-label">Units</label>
        <input class="form-control mb-3" type="number" min="0" name="units" value="<?php echo (int)$row['units']; ?>" required>
        <div class="d-flex gap-2">
            <button class="btn btn-primary" type="submit">Save</button>
            <a class="btn btn-secondary" href="manage_inventory.php">Cancel</a>
        </div>
    </form>
</div>
</body>
</html>
