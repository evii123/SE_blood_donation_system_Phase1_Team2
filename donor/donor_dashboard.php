<?php

require_once __DIR__ . '/../includes/bootstrap.php';

require_role('donor');

$donorId = current_user_id();
$donor = get_donor_profile($pdo, $donorId);
$eligible = $donor ? donor_is_eligible($donor) : false;
$totalAppointments = get_donor_total_appointments($pdo, $donorId);
$appointments = get_donor_recent_appointments($pdo, $donorId);
$urgentBanks = $donor ? get_urgent_banks_for_donor($pdo, (string) ($donor['city'] ?? ''), (string) ($donor['blood_group'] ?? '')) : [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donor Dashboard - National Blood Donation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-danger">
        <div class="container-fluid">
            <span class="navbar-brand">🩸 Donor Dashboard</span>
            <span class="navbar-text">Welcome, <?php echo e($donor['name'] ?? 'Donor'); ?> (<?php echo e($donor['blood_group'] ?? 'N/A'); ?>)</span>
            <a href="../auth/logout.php" class="btn btn-outline-light">Logout</a>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card text-center <?php echo $eligible ? 'bg-success text-white' : 'bg-secondary'; ?>">
                    <div class="card-body">
                        <h5>Eligibility</h5>
                        <h2><?php echo $eligible ? '✅ Eligible' : '⏳ Wait'; ?></h2>
                        <?php if (!empty($donor['last_donation_date'])): ?>
                            <small>Last: <?php echo date('d M Y', strtotime($donor['last_donation_date'])); ?></small>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body text-center">
                        <h5>Total Donations</h5>
                        <h2><?php echo $totalAppointments; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-dark">
                    <div class="card-body text-center">
                        <h5>Telephone</h5>
                        <h6><?php echo e($donor['phone'] ?? 'N/A'); ?></h6>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body text-center">
                        <h5>Donor ID</h5>
                        <h6><?php echo e($donor['donor_id_number'] ?? 'N/A'); ?></h6>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <strong>Address:</strong> <?php echo e($donor['donor_address'] ?? 'N/A'); ?>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <strong>City:</strong> <?php echo e($donor['city'] ?? 'N/A'); ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-12 text-center">
                <?php if ($eligible): ?>
                    <a href="book_appointment.php" class="btn btn-lg btn-danger me-3">
                        📅 Book Donation Appointment
                    </a>
                <?php endif; ?>
                <a href="find_banks.php" class="btn btn-lg btn-outline-secondary">Search Nearby Banks</a>
            </div>
        </div>

        <?php if (!empty($urgentBanks)): ?>
        <div class="alert alert-warning">
            <h5>Urgent Need Near You!</h5>
            <?php foreach ($urgentBanks as $bank): ?>
                <p><?php echo e($bank['name']); ?> (<?php echo e($bank['address']); ?>) needs <?php echo e($bank['blood_group']); ?> (<?php echo e($bank['units']); ?> units left)</p>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Your Appointments</h4>
                    </div>
                    <div class="card-body">
                        <?php if (empty($appointments)): ?>
                            <p class="text-muted">No appointments yet. Book one above!</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Blood Bank</th>
                                            <th>Date</th>
                                            <th>Time Slot</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($appointments as $appointment): ?>
                                        <tr class="<?php echo $appointment['status'] === 'pending' ? 'table-warning' : ($appointment['status'] === 'approved' ? 'table-info' : ($appointment['status'] === 'cancelled' ? 'table-secondary' : 'table-success')); ?>">
                                            <td><?php echo e($appointment['bank_name']); ?><br><small><?php echo e($appointment['bank_address']); ?></small></td>
                                            <td><?php echo date('d M Y', strtotime($appointment['appointment_date'])); ?></td>
                                            <td><?php echo e(ucfirst($appointment['time_slot'])); ?></td>
                                            <td>
                                                <span class="badge bg-<?php
                                                    echo $appointment['status'] === 'pending' ? 'warning' :
                                                        ($appointment['status'] === 'approved' ? 'info' : ($appointment['status'] === 'cancelled' ? 'secondary' : 'success'));
                                                ?>">
                                                    <?php echo e(ucfirst($appointment['status'])); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($appointment['status'] === 'pending'): ?>
                                                    <a href="cancel_appointment.php?id=<?php echo (int) $appointment['id']; ?>" class="btn btn-sm btn-outline-danger">Cancel</a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <a href="appointment_history.php" class="btn btn-secondary">View Full History</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
