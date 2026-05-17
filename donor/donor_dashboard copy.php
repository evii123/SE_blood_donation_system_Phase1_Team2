<?php

require_once __DIR__ . '/../includes/bootstrap.php';

require_role('donor');

// Legacy backup route kept for compatibility.
redirect('donor_dashboard.php');
