<?php

require_once __DIR__ . '/../includes/bootstrap.php';

require_role('donor');

$donorId = current_user_id();
$appointmentId = (int) ($_GET['id'] ?? 0);

if ($appointmentId > 0) {
    cancel_pending_donor_appointment($pdo, $donorId, $appointmentId);
}

redirect('donor_dashboard.php');
