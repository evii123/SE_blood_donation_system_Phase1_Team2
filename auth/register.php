<?php

require_once __DIR__ . '/../includes/bootstrap.php';

$errors = [];
$formData = $_POST;

if (is_post_request()) {
    $result = register_user($pdo, $_POST);
    $errors = $result['errors'];

    if (empty($errors)) {
        redirect('login.php');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - National Blood Donation</title>
    <link rel="stylesheet" href="../src/css/register.css">
</head>
<body>

<div class="register-container">
    <div class="logo">
        <h1>Register</h1>
        <p>Join our blood donation network</p>
    </div>

    <?php if (!empty($errors)): ?>
    <div class="alert alert-error">
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?php echo e($error); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label for="name">Name</label>
            <input type="text" id="name" name="name" value="<?php echo e(old($formData, 'name')); ?>" required>
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?php echo e(old($formData, 'email')); ?>" required>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>

        <div class="form-group">
            <label for="role">Role</label>
            <select name="role" id="role" onchange="toggleExtra()" required>
                <option value="donor" <?php echo old($formData, 'role', 'donor') === 'donor' ? 'selected' : ''; ?>>Donor</option>
                <option value="hospital" <?php echo old($formData, 'role') === 'hospital' ? 'selected' : ''; ?>>Hospital</option>
                <option value="bank" <?php echo old($formData, 'role') === 'bank' ? 'selected' : ''; ?>>Blood Bank</option>
            </select>
        </div>

        <div id="donorFields" style="display:none">
            <div class="form-group">
                <label for="blood_group">Blood Group</label>
                <select name="blood_group" id="blood_group">
                    <?php foreach (allowed_blood_groups() as $bloodGroup): ?>
                        <option value="<?php echo e($bloodGroup); ?>" <?php echo old($formData, 'blood_group', 'A+') === $bloodGroup ? 'selected' : ''; ?>>
                            <?php echo e($bloodGroup); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="telephone">Telephone Number</label>
                <input type="text" id="telephone" name="telephone" value="<?php echo e(old($formData, 'telephone')); ?>" placeholder="e.g. +355 69 123 4567">
            </div>

            <div class="form-group">
                <label for="donor_id_number">Donor ID Number</label>
                <input type="text" id="donor_id_number" name="donor_id_number" value="<?php echo e(old($formData, 'donor_id_number')); ?>" placeholder="National ID / Passport ID">
            </div>

            <div class="form-group">
                <label for="donor_address">Donor Address</label>
                <input type="text" id="donor_address" name="donor_address" value="<?php echo e(old($formData, 'donor_address')); ?>" placeholder="Street, area, city">
            </div>
        </div>

        <div id="addressField" class="form-group" style="display:none">
            <label for="address">Address</label>
            <input type="text" id="address" name="address" value="<?php echo e(old($formData, 'address')); ?>">
        </div>

        <button type="submit">Register</button>

        <br><br>
        <a href="login.php" class="text">Already have an account? Login</a><br><br>
        <a href="../index.php" class="btn">Cancel</a>
    </form>

</div>

<script>
function toggleExtra() {
    var role = document.getElementById('role').value;

    var donorFields = document.getElementById('donorFields');
    var addressField = document.getElementById('addressField');

    donorFields.style.display = role === 'donor' ? 'block' : 'none';
    addressField.style.display = (role === 'hospital' || role === 'bank') ? 'block' : 'none';
}
toggleExtra();
</script>
</body>
</html>
