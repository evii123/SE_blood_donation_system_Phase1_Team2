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
    <meta property="og:image" content="../src/img/icon.png">
    <meta property="og:url" content="https://yourdomain.com/blood_donation_system/">
    <meta name="twitter:card" content="summary_large_image">
    <link rel="icon" href="src/img/icon.png" type="image/png">
    <link rel="stylesheet" href="/blood_donation_system/src/css/main.css">
    <style>
        :root {
            --bg: #f4f8f9;
            --text: #1f3340;
            --muted: #5f7482;
            --surface: #ffffff;
            --primary: #3d7f8f;
            --primary-dark: #2f6773;
            --hero-a: #6fa4ae;
            --hero-b: #4f7c86;
        }
        body {
            background: linear-gradient(180deg, #f8fbfc 0%, var(--bg) 100%);
            color: var(--text);
            padding: 0 20px;
        }
        nav {
            top: 0;
            display: flex;
            justify-content: space-between;
        }
        nav a {
            color: var(--text);
            transition: all 0.3s ease;
        }
        nav a:hover {
            background: rgba(61, 127, 143, 0.14);
            border-radius: 5px;
            transform: scale(1.05);
        }
        .hero {
            text-align: center;
            padding: 50px 20px;
            background: linear-gradient(135deg, var(--hero-a), var(--hero-b));
            color: white;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 10px 24px rgba(47, 103, 115, 0.2);
        }
        .hero h1 {
            font-size: 3em;
            margin-bottom: 10px;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);
        }
        .hero p {
            font-size: 1.2em;
            margin: 0;
        }
        .features {
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            margin: 40px 0;
        }
        .feature {
            flex: 1;
            min-width: 250px;
            margin: 10px;
            padding: 20px;
            background: var(--surface);
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 8px 20px rgba(24, 53, 62, 0.08);
            transition: transform 0.3s;
        }
        .feature:hover {
            transform: translateY(-5px);
        }
        .feature h3 {
            color: var(--primary-dark);
            margin-bottom: 10px;
        }
        p, .author {
            color: var(--muted);
        }
        h2 {
            color: var(--primary-dark);
        }
        .cta {
            text-align: center;
            margin: 40px 0;
        }
        .cta a {
            display: inline-block;
            padding: 15px 30px;
            background: var(--primary);
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            transition: background 0.3s;
        }
        .cta a:hover {
            background: var(--primary-dark);
        }
    </style>
</head>
<body>
<nav>
    <div>
        <a href="/blood_donation_system/index.php"><img src="src/img/icon1.png" alt="National Blood Donation Logo" style="height: 30px; margin-right: 10px; vertical-align: middle;"> National Blood Donation</a>
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
    <div class="hero">
        <h1>Welcome to the National Blood Donation Platform</h1>
        <div class="quote">
            <p>
                <em>
                    "A few minutes of your time and a small part of you can become someone's second chance at life."
                </em>
            </p>
            <p class="author">
                Become a donor today and turn compassion into action.
            </p>
        </div>
    </div>

    <p>
        This prototype connects <strong>blood donors</strong>, <strong>hospitals</strong>, and
        <strong>blood banks</strong> to help manage blood donations and inventory.
    </p>

    <h2>What you can do</h2>

    <div class="features">
        <div class="feature">
            <h3>Donors</h3>
            <p>Register, log in, and schedule donation appointments with a nearby blood bank.</p>
        </div>
        <div class="feature">
            <h3>Blood Banks</h3>
            <p>View booked appointments, mark donations as completed, and update blood inventory.</p>
        </div>
        <div class="feature">
            <h3>Hospitals</h3>
            <p>Request blood units of specific blood groups from selected blood banks.</p>
        </div>
    </div>

    <h2>Get started</h2>

    <div class="cta">
        <?php if (!isset($_SESSION['user_id'])): ?>
            <p>
                <a href="auth/register.php">Create an account</a> or
                <a href="auth/login.php">log in</a> if you already have one.
            </p>
        <?php else: ?>
            <?php if ($_SESSION['role'] === 'donor'): ?>
                <p>
                    Go to your <a href="donor/donor_dashboard.php">Donor Dashboard</a> to manage your appointments.
                </p>
            <?php elseif ($_SESSION['role'] === 'hospital'): ?>
                <p>
                    Go to your <a href="hospital/hospital_dashboard.php">Hospital Dashboard</a> to manage your blood requests.
                </p>
            <?php elseif ($_SESSION['role'] === 'bank'): ?>
                <p>
                    Go to your <a href="bank/bank_dashboard.php">Bank Dashboard</a> to manage donations and inventory.
                </p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
<footer>
   <center> <p>&copy; <?php echo date('Y'); ?> National Blood Donation Platform</p> </center>
</footer>

</body>
</html>
