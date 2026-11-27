<?php
declare(strict_types=1);

function redirectTo(string $path): void
{
    header('Location: ' . $path);
    exit;
}

// Checks whether a user is logged in.
function requireLogin(): void
{
    require_once __DIR__ . '/session.php';

    startSession();

    if (
        !isset($_SESSION['staff_id'], $_SESSION['role']) ||
        !is_scalar($_SESSION['staff_id']) ||
        !is_string($_SESSION['role']) ||
        trim($_SESSION['role']) === ''
    ) {
        redirectTo('/login.php');
    }
}

// Checks whether the logged-in user is an administrator.
function requireAdmin(): void
{
    requireLogin();

    if ($_SESSION['role'] !== 'admin') {
        http_response_code(403);
        exit('Access denied. You do not have permission to access this page.');
    }
}
