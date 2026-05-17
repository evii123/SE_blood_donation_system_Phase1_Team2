<?php

require_once __DIR__ . '/../includes/bootstrap.php';

require_role('hospital');

// Legacy route kept for compatibility.
redirect('submit_request.php');
