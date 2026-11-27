<?php

declare(strict_types=1);

require_once __DIR__ . '/functions.php';

function sendNoCacheHeaders(): void
{
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Expires: 0');
}

/** Configure session protections before PHP starts a session. */
function configureSession(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.use_trans_sid', '0');

    $params = session_get_cookie_params();
    session_set_cookie_params([
        'lifetime' => $params['lifetime'],
        'path' => $params['path'],
        'domain' => $params['domain'],
        'secure' => $params['secure'] || (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        'httponly' => true,
        'samesite' => $params['samesite'] ?? 'Lax',
    ]);
}

function startSession(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        configureSession();
        session_start();
    }
}

/**
 * Starts an authenticated session after credentials have been verified.
 * Expects the agreed 'staff_id' and 'role' keys.
 */
function startAuthenticatedSession(array $staff): void
{
    if (!isset($staff['staff_id'], $staff['role']) || !is_scalar($staff['staff_id']) || !is_string($staff['role']) || trim($staff['role']) === '') {
        throw new InvalidArgumentException('Staff ID and role are required to start an authenticated session.');
    }

    startSession();
    session_regenerate_id(true);

    $_SESSION['staff_id'] = $staff['staff_id'];
    $_SESSION['role'] = $staff['role'];
}

/** Clear session data and the session cookie, destroy the session, then redirect. */
function destroySessionAndLogout(): void
{
    startSession();
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', [
            'expires' => time() - 42000,
            'path' => $params['path'],
            'domain' => $params['domain'],
            'secure' => $params['secure'],
            'httponly' => $params['httponly'],
            'samesite' => $params['samesite'] ?? 'Lax',
        ]);
    }

    session_destroy();
    redirectTo('login.php');
}

/** Store a one-time login error for the next page request. */
function setLoginError(string $message): void
{
    startSession();
    $_SESSION['login_error'] = $message;
}

/** Retrieve and immediately clear the one-time login error, if present. */
function getAndClearLoginError(): ?string
{
    startSession();
    $message = $_SESSION['login_error'] ?? null;
    unset($_SESSION['login_error']);

    return is_string($message) ? $message : null;
}
