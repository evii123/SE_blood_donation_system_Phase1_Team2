<?php

require_once __DIR__ . '/../includes/bootstrap.php';

require_role('donor');

$donorId = current_user_id();
$selectedBankId = (int) ($_GET['bank_id'] ?? 0);
$selectedTimeSlot = $_POST['time_slot'] ?? 'morning';
$hasTimeSlot = ensure_appointment_time_slot_column($pdo);
$donorInfo = get_donor_profile($pdo, $donorId);
$lastDonation = $donorInfo['last_donation_date'] ?? null;
$donorBloodGroup = $donorInfo['blood_group'] ?? '';

if ($donorInfo && !donor_is_eligible($donorInfo)) {
    $_SESSION['error'] = 'Not eligible yet. Last donation: ' . date('d M Y', strtotime($lastDonation));
    redirect('donor_dashboard.php');
}

$donorCity = get_donor_city($pdo, $donorId);
$banks = get_bank_options_for_donor($pdo, (string) $donorBloodGroup, $donorCity);
$errors = [];

if (is_post_request()) {
    $selectedBankId = (int) ($_POST['bank_id'] ?? 0);
    $selectedTimeSlot = $_POST['time_slot'] ?? '';
    $errors = validate_appointment_booking($pdo, $donorId, $_POST, $hasTimeSlot);

    if (empty($errors) && book_donor_appointment($pdo, $donorId, $_POST, $hasTimeSlot)) {
        redirect('donor_dashboard.php');
    }

    if (empty($errors)) {
        $errors[] = 'Booking failed. Try again.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Donation Appointment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-danger">
        <div class="container">
            <a class="navbar-brand" href="donor_dashboard.php">Donor Appointment Booking</a>
            <span class="navbar-text">Book Your Life-Saving Donation</span>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-danger text-white text-center">
                        <h3>Schedule Donation</h3>
                        <small>Your blood can save lives today.</small>
                    </div>
                    <div class="card-body">
                        <?php foreach ($errors as $error): ?>
                            <div class="alert alert-danger"><?php echo e($error); ?></div>
                        <?php endforeach; ?>

                        <form method="post">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Blood Bank <span class="text-danger">*</span></label>
                                    <select name="bank_id" class="form-select" required>
                                        <option value="">Choose a bank...</option>
                                        <?php foreach ($banks as $bank): ?>
                                            <option value="<?php echo (int) $bank['id']; ?>" <?php echo $selectedBankId === (int) $bank['id'] ? 'selected' : ''; ?>>
                                                <?php echo e($bank['name'] . ' - ' . ($bank['address'] ?: 'N/A')); ?>
                                                <?php echo $bank['city'] === $donorCity ? ' (Local)' : ''; ?>
                                                <?php echo ' | ' . (int) $bank['units'] . ' units'; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Date <span class="text-danger">*</span></label>
                                    <input type="date" name="appointment_date" class="form-control"
                                           min="<?php echo date('Y-m-d'); ?>" required
                                           value="<?php echo e($_POST['appointment_date'] ?? ''); ?>">
                                    <div class="form-text">Earliest: Today</div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Time Slot <span class="text-danger">*</span></label>
                                <div class="d-flex gap-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="time_slot" id="slot_morning" value="morning" <?php echo $selectedTimeSlot === 'morning' ? 'checked' : ''; ?> required>
                                        <label class="form-check-label" for="slot_morning">Morning (9:00 - 12:00)</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="time_slot" id="slot_afternoon" value="afternoon" <?php echo $selectedTimeSlot === 'afternoon' ? 'checked' : ''; ?> required>
                                        <label class="form-check-label" for="slot_afternoon">Afternoon (14:00 - 17:00)</label>
                                    </div>
                                </div>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-danger btn-lg">
                                    Confirm Booking
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="card-footer text-center text-muted">
                        <a href="donor_dashboard.php" class="btn btn-link">Back to Dashboard</a>
                    </div>
                </div>

                <?php if (!empty($banks)): ?>
                <div class="card mt-4">
                    <div class="card-header">
                        <h6>Tips</h6>
                    </div>
                    <div class="card-body">
                        <ul class="mb-0">
                            <li>Choose local banks for convenience.</li>
                            <li>Urgent banks need your help now.</li>
                            <li>Bring ID and eat well before donating.</li>
                        </ul>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
