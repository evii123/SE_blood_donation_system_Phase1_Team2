<?php

require_once __DIR__ . '/../includes/bootstrap.php';

require_role('donor');

$donorId = current_user_id();
$donor = get_donor_city_and_blood_group($pdo, $donorId) ?? ['city' => '', 'blood_group' => 'A+'];

$city = trim($_GET['city'] ?? ($donor['city'] ?? ''));
$bloodGroup = $_GET['blood_group'] ?? ($donor['blood_group'] ?? 'A+');
$banks = search_banks_for_donor($pdo, (string) $bloodGroup, (string) $city);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Find Banks</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3">Nearby Blood Banks</h1>
        <a href="donor_dashboard.php" class="btn btn-secondary">Back</a>
    </div>

    <form method="get" class="row g-2 mb-4">
        <div class="col-md-4">
            <input type="text" name="city" value="<?php echo e($city); ?>" class="form-control" placeholder="City">
        </div>
        <div class="col-md-3">
            <select name="blood_group" class="form-select">
                <?php foreach (['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bg): ?>
                    <option value="<?php echo $bg; ?>" <?php echo $bloodGroup === $bg ? 'selected' : ''; ?>><?php echo $bg; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <button class="btn btn-primary w-100" type="submit">Search</button>
        </div>
    </form>

    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>Bank</th>
                        <th>City</th>
                        <th>Address</th>
                        <th><?php echo e($bloodGroup); ?> Units</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!$banks): ?>
                    <tr><td colspan="5" class="text-muted">No banks found.</td></tr>
                <?php else: ?>
                    <?php foreach ($banks as $bank): ?>
                    <tr>
                        <td><?php echo e($bank['name']); ?></td>
                        <td><?php echo e($bank['city']); ?></td>
                        <td><?php echo e($bank['address'] ?? 'N/A'); ?></td>
                        <td><?php echo (int)$bank['units']; ?></td>
                        <td><a class="btn btn-sm btn-danger" href="book_appointment.php?bank_id=<?php echo (int)$bank['id']; ?>">Book</a></td>
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
