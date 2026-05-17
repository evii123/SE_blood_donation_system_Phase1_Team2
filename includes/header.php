<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="National Blood Donation Platform - Connect donors, hospitals, and blood banks for efficient blood management and life-saving donations.">
    <meta name="keywords" content="blood donation, donors, hospitals, blood banks, medical, healthcare">
    <meta name="author" content="National Blood Donation Team">
    <meta property="og:title" content="National Blood Donation Platform">
    <meta property="og:description" content="Connect donors, hospitals, and blood banks for efficient blood management.">
    <meta property="og:image" content="/blood_donation_system/src/img/icon.png">
    <meta property="og:url" content="https://yourdomain.com/blood_donation_system/">
    <meta name="twitter:card" content="summary_large_image">
    <link rel="icon" href="/blood_donation_system/src/img/icon.png" type="image/png">
    <link rel="stylesheet" href="/blood_donation_system/src/css/main.css">
</head>
<body>
<nav>
    <div>
        <a href="/blood_donation_system/index.php"><img src="/blood_donation_system/src/img/icon1.png" alt="National Blood Donation Logo" style="height: 30px; margin-right: 10px; vertical-align: middle;"> National Blood Donation</a>
    </div>
    <div>
        <?php if (isset($_SESSION['user_id'])): ?>
            <?php if ($_SESSION['role'] === 'donor'): ?>
                <a href="/blood_donation_system/donor/donor_dashboard.php">Donor Dashboard</a>
            <?php elseif ($_SESSION['role'] === 'hospital'): ?>
                <a href="/blood_donation_system/hospital/hospital_dashboard.php">Hospital Dashboard</a>
            <?php elseif ($_SESSION['role'] === 'bank'): ?>
                <a href="/blood_donation_system/bank/bank_dashboard.php">Bank Dashboard</a>
            <?php endif; ?>
            <a href="/blood_donation_system/auth/logout.php">Logout</a>
        <?php else: ?>
            <a href="/blood_donation_system/auth/login.php">Login</a>
            <a href="/blood_donation_system/auth/register.php">Register</a>
        <?php endif; ?>
    </div>
</nav>

<div class="container">
