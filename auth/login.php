<?php

require_once __DIR__ . '/../includes/bootstrap.php';

$error = '';
$formData = $_POST;

if (is_post_request()) {
    $user = authenticate_user($pdo, $_POST['email'] ?? '', $_POST['password'] ?? '');

    if ($user) {
        login_user($user);
        redirect_to_dashboard($user['role']);
    }

    $error = 'Invalid credentials.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - National Blood Donation</title>
    <link rel="stylesheet" href="../src/css/login.css">
</head>
<body>

<div class="login-container">
    <div class="logo">
       <nav>
            <div>
                <a href="/blood_donation_system/index.php">National Blood Donation Login page</a>
            </div>
            <div></div>
        </nav>
    </div>

    <form method="POST" action="">
        <?php if ($error !== ''): ?>
            <div class="alert alert-danger"><?php echo e($error); ?></div>
        <?php endif; ?>

        <div class="form-group">
            <label for="email">Email</label>
            <input id="email" type="email" name="email" value="<?php echo e(old($formData, 'email')); ?>" required><br>
            <label for="password">Password</label>
            <input
                type="password"
                id="password"
                name="password"
                required
            >
        </div>

        <button type="submit">Login</button>

        <br><br><br>
        <a href="../index.php" class="btn">Cancel</a>
    </form>

</div>
</body>
</html>
