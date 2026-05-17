<?php

require_once __DIR__ . '/../includes/bootstrap.php';

require_role('hospital');

$hospitalId = current_user_id();
$dashboard = get_hospital_dashboard_data($pdo, $hospitalId);
$totalRequests = $dashboard['totalRequests'];
$pendingRequests = $dashboard['pendingRequests'];
$fulfilledRequests = $dashboard['fulfilledRequests'];
$bloodStats = $dashboard['bloodStats'];
$recentRequests = $dashboard['recentRequests'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Hospital Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <nav class="navbar navbar-dark bg-success">
        <div class="container-fluid">
            <span class="navbar-brand">Hospital Dashboard - <?php echo e($_SESSION['name'] ?? 'Hospital'); ?></span>
            <a href="../auth/logout.php" class="btn btn-outline-light">Logout</a>
        </div>
    </nav>
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-3">
                <div class="card text-white bg-primary mb-3">
                    <div class="card-body">
                        <h5>Total Requests</h5>
                        <h2><?php echo $totalRequests; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-warning mb-3">
                    <div class="card-body">
                        <h5>Pending Requests</h5>
                        <h2><?php echo $pendingRequests; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-success mb-3">
                    <div class="card-body">
                        <h5>Fulfilled Requests</h5>
                        <h2><?php echo $fulfilledRequests; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-info mb-3">
                    <div class="card-body">
                        <h5>Blood Groups Available</h5>
                        <h2><?php echo count($bloodStats); ?></h2>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">Available Inventory by Blood Group (All Banks)</div>
                    <div class="card-body">
                        <canvas id="inventoryChart" height="200"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">Recent Requests</div>
                    <div class="card-body">
                        <table class="table table-striped">
                            <thead><tr><th>ID</th><th>Blood Group</th><th>Units</th><th>Status</th><th>Bank</th></tr></thead>
                            <tbody>
                                <?php foreach ($recentRequests as $request): ?>
                                <tr class="<?php echo $request['status'] === 'pending' ? 'table-warning' : ($request['status'] === 'fulfilled' ? 'table-success' : ''); ?>">
                                    <td><?php echo (int) $request['id']; ?></td>
                                    <td><?php echo e($request['blood_group']); ?></td>
                                    <td><?php echo (int) $request['units_requested']; ?></td>
                                    <td><span class="badge bg-<?php echo $request['status'] === 'pending' ? 'warning' : ($request['status'] === 'fulfilled' ? 'success' : 'secondary'); ?>"><?php echo e(ucfirst($request['status'])); ?></span></td>
                                    <td><?php echo e($request['bank_address'] ?? 'N/A'); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <a href="request_history.php" class="btn btn-secondary">View All Requests</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">Quick Actions</div>
                    <div class="card-body text-center">
                        <a href="submit_request.php" class="btn btn-lg btn-primary me-3">Submit New Blood Request</a>
                        <a href="view_banks.php" class="btn btn-lg btn-info">View Bank Inventories</a>
                        <a href="appointments.php" class="btn btn-lg btn-success">Patient Appointments</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const ctxInv = document.getElementById('inventoryChart').getContext('2d');
        const invLabels = [<?php echo "'" . implode("','", array_column($bloodStats, 'blood_group')) . "'"; ?>];
        const invData = [<?php echo implode(',', array_column($bloodStats, 'total_units')); ?>];
        new Chart(ctxInv, {
            type: 'bar',
            data: {
                labels: invLabels,
                datasets: [{
                    label: 'Total Units',
                    data: invData,
                    backgroundColor: 'rgba(75, 192, 192, 0.6)'
                }]
            },
            options: { responsive: true, scales: { y: { beginAtZero: true } } }
        });
    </script>
</body>
</html>
