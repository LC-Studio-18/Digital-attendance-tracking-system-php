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
    // Load the existing session implementation.
    require_once __DIR__ . '/session.php';

    // Start the session using the project's security settings.
    startSession();

    // Check that the required login details exist.
    if (
        !isset($_SESSION['staff_id'], $_SESSION['role']) ||
        !is_scalar($_SESSION['staff_id']) ||
        !is_string($_SESSION['role']) ||
        trim($_SESSION['role']) === ''
    ) {
        // Redirect users who are not logged in.
        redirectTo('../frontend/login.php');
    }
}

// Checks whether the logged-in user is an administrator.
function requireAdmin(): void
{
    // First make sure the user is logged in.
    requireLogin();

    // Only users with the admin role can continue.
    if ($_SESSION['role'] !== 'admin') {

        // Return HTTP 403 for users without permission.
        http_response_code(403);

        // Display the agreed Frontend access-denied message.
        exit('Access denied. You do not have permission to access this page.');
    }
}
