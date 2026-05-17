<?php

require_once __DIR__ . '/../includes/bootstrap.php';

require_role('bank');

$bankId = current_user_id();
$dashboard = get_bank_dashboard_data($pdo, $bankId);
$totalDonors = $dashboard['totalDonors'];
$totalInventory = $dashboard['totalInventory'];
$donorStats = $dashboard['donorStats'];
$inventory = $dashboard['inventory'];
$totalRequests = $dashboard['totalRequests'];
$lowStock = $dashboard['lowStock'];
$upcomingAppointments = $dashboard['upcomingAppointments'];
$recentRequests = $dashboard['recentRequests'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Blood Bank Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <nav class="navbar navbar-dark bg-primary">
        <div class="container-fluid">
            <span class="navbar-brand">Blood Bank Dashboard - <?php echo e($_SESSION['name'] ?? 'Admin'); ?></span>
            <a href="../auth/logout.php" class="btn btn-outline-light">Logout</a>
        </div>
    </nav>
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-3">
                <div class="card text-white bg-success mb-3">
                    <div class="card-body">
                        <h5>Total Donors</h5>
                        <h2><?php echo $totalDonors; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-warning mb-3">
                    <div class="card-body">
                        <h5>Pending Requests</h5>
                        <h2><?php echo $totalRequests; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-info mb-3">
                    <div class="card-body">
                        <h5>Blood Groups in Stock</h5>
                        <h2><?php echo count($inventory); ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-danger mb-3">
                    <div class="card-body">
                        <h5>Low Stock Alerts</h5>
                        <h2><?php echo $lowStock; ?></h2>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">Donors by Blood Group</div>
                    <div class="card-body">
                        <canvas id="donorChart" height="200"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">Bank Inventory (<?php echo $totalInventory; ?>)</div>
                    <div class="card-body">
                        <table class="table table-striped">
                            <thead><tr><th>Blood Group</th><th>Units</th><th>Status</th><th>Action</th></tr></thead>
                            <tbody>
                                <?php foreach ($inventory as $item): ?>
                                <tr <?php if ($item['units'] < 10) echo 'class="table-danger"'; ?>>
                                    <td><?php echo e($item['blood_group']); ?></td>
                                    <td><strong><?php echo (int) $item['units']; ?></strong></td>
                                    <td><?php echo $item['units'] < 10 ? 'Low' : 'OK'; ?></td>
                                    <td><a href="update_inventory.php?id=<?php echo (int) $item['id']; ?>" class="btn btn-sm btn-primary">Edit</a></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <a href="manage_inventory.php" class="btn btn-primary">Manage Inventory</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">Upcoming Appointments</div>
                    <div class="card-body">
                        <?php foreach ($upcomingAppointments as $row): ?>
                            <p><strong><?php echo e($row['appointment_date']); ?></strong> (<?php echo e(ucfirst($row['time_slot'])); ?>) - <?php echo e($row['donor_name']); ?></p>
                        <?php endforeach; ?>
                        <a href="manage_appointments.php" class="btn btn-secondary">View All</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">Recent Requests</div>
                    <div class="card-body">
                        <?php foreach ($recentRequests as $row): ?>
                            <p><?php echo e($row['hospital_name']); ?> requests <?php echo (int) $row['units_requested']; ?> <?php echo e($row['blood_group']); ?> <a href="process_request.php?id=<?php echo (int) $row['id']; ?>">Process</a></p>
                        <?php endforeach; ?>
                        <a href="manage_requests.php" class="btn btn-secondary">View All Requests</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const ctx = document.getElementById('donorChart').getContext('2d');
        const labels = [<?php echo "'" . implode("','", array_column($donorStats, 'blood_group')) . "'"; ?>];
        const data = [<?php echo implode(',', array_column($donorStats, 'count')); ?>];
        new Chart(ctx, {
            type: 'pie',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: ['#FF6384','#36A2EB','#FFCE56','#4BC0C0','#9966FF','#FF9F40','#FF6384','#C9CBCF']
                }]
            },
            options: { responsive: true }
        });
    </script>
</body>
</html>
