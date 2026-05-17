<?php

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../services/auth_service.php';
require_once __DIR__ . '/../services/donor_service.php';
require_once __DIR__ . '/../services/bank_service.php';
require_once __DIR__ . '/../services/hospital_service.php';

ensure_session_started();
$pdo = $conn;
