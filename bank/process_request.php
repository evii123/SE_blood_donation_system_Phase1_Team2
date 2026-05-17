<?php

require_once __DIR__ . '/../includes/bootstrap.php';

require_role('bank');

$bankId = current_user_id();
$id = (int) ($_GET['id'] ?? 0);
$message = '';
$request = get_bank_request($pdo, $bankId, $id);

if (!$request) {
    redirect('manage_requests.php');
}

if (is_post_request()) {
    $action = $_POST['action'] ?? '';
    $result = process_bank_request_action($pdo, $bankId, $id, $action);
    $message = $result['message'];
    $request = get_bank_request($pdo, $bankId, $id) ?? $request;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Process Request</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4" style="max-width: 700px;">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3">Process Request #<?php echo (int)$request['id']; ?></h1>
        <a href="manage_requests.php" class="btn btn-secondary">Back</a>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-info"><?php echo e($message); ?></div>
    <?php endif; ?>

    <div class="card card-body mb-3">
        <p><strong>Hospital:</strong> <?php echo e($request['hospital_name']); ?></p>
        <p><strong>Blood Group:</strong> <?php echo e($request['blood_group']); ?></p>
        <p><strong>Units Requested:</strong> <?php echo (int)$request['units_requested']; ?></p>
        <p><strong>Status:</strong> <?php echo e(ucfirst($request['status'])); ?></p>
    </div>

    <?php if (in_array($request['status'], ['pending','approved'], true)): ?>
    <form method="post" class="d-flex gap-2">
        <?php if ($request['status'] === 'pending'): ?>
            <button type="submit" name="action" value="approve" class="btn btn-primary">Approve</button>
        <?php endif; ?>
        <button type="submit" name="action" value="fulfill" class="btn btn-success">Fulfill</button>
        <button type="submit" name="action" value="reject" class="btn btn-danger">Reject</button>
    </form>
    <?php endif; ?>
</div>
</body>
</html>
