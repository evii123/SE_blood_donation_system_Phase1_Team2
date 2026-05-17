<?php

require_once __DIR__ . '/../includes/bootstrap.php';

require_role('hospital');

$hospitalId = current_user_id();
$errors = [];
$banks = get_hospital_bank_options($pdo);

if (is_post_request()) {
    $bankId = (int) ($_POST['bank_id'] ?? 0);
    $bloodGroup = $_POST['blood_group'] ?? '';
    $units = (int) ($_POST['units'] ?? 0);

    if ($bankId <= 0 || $units <= 0) {
        $errors[] = 'Please choose a bank and valid units.';
    }
    if (!in_array($bloodGroup, ['A+','A-','B+','B-','AB+','AB-','O+','O-'], true)) {
        $errors[] = 'Please select a valid blood group.';
    }

    if (!$errors) {
        if (create_hospital_request($pdo, $hospitalId, $bankId, $bloodGroup, $units)) {
            redirect('request_history.php');
        }
        $errors[] = 'Unable to submit request. Please try again.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Blood Request</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4" style="max-width: 720px;">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3">Submit New Blood Request</h1>
        <a href="hospital_dashboard.php" class="btn btn-secondary">Back</a>
    </div>

    <?php if ($errors): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $e): ?>
                <div><?php echo e($e); ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="post" class="card card-body">
        <label class="form-label">Blood Bank</label>
        <select name="bank_id" class="form-select mb-3" required>
            <option value="">Select bank</option>
            <?php foreach ($banks as $bank): ?>
                <option value="<?php echo (int)$bank['id']; ?>"><?php echo e($bank['name'] . ' - ' . ($bank['city'] ?? '')); ?></option>
            <?php endforeach; ?>
        </select>

        <label class="form-label">Blood Group</label>
        <select name="blood_group" class="form-select mb-3" required>
            <?php foreach (['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bg): ?>
                <option value="<?php echo $bg; ?>"><?php echo $bg; ?></option>
            <?php endforeach; ?>
        </select>

        <label class="form-label">Units Needed</label>
        <input type="number" min="1" name="units" class="form-control mb-3" required>

        <button type="submit" class="btn btn-primary">Send Request</button>
    </form>
</div>
</body>
</html>
