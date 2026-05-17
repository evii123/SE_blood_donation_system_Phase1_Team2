<?php

function ensure_session_started(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}

function current_user_id(): ?int
{
    return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
}

function current_user_role(): ?string
{
    return $_SESSION['role'] ?? null;
}

function require_role(string $role): void
{
    ensure_session_started();

    if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? null) !== $role) {
        redirect('../auth/login.php');
    }
}

function login_user(array $user): void
{
    ensure_session_started();
    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['name'] = $user['name'];
}

function logout_user(): void
{
    ensure_session_started();
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    session_destroy();
}

function redirect_to_dashboard(string $role): void
{
    $routes = [
        'donor' => '../donor/donor_dashboard.php',
        'hospital' => '../hospital/hospital_dashboard.php',
        'bank' => '../bank/bank_dashboard.php',
    ];

    redirect($routes[$role] ?? '../index.php');
}

