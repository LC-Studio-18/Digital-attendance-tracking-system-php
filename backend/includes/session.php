<?php

    declare(strict_types=1);

    require_once __DIR__ . '/functions.php';

    /**
    *Starts an authenticated session for a staff member who has just passed email + password verification. 
    *Expects $staff to be an array with at least 'id' and 'role' keys — confirm these exact key names match what the login handler's staff lookup actually returns.
    */
    function startAuthenticatedSession(array $staff): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        // Prevents session fixation — required by the ticket's DoD.
        session_regenerate_id(true);

        $_SESSION['staff_id'] = $staff['id'];
        $_SESSION['role'] = $staff['role'];
    }

    /**
    * Fully destroys the current session and sends the user back to the login page. Covers the cookie itself too, not just server-side data,
    * so a stale session cookie can't be reused.
    */
    function destroySessionAndLogout(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
        }

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

        redirectTo('/login.php');
    }

    /**
    * Stores a one-time error message to show on the login page after a
    * redirect. "One-time" meaning: the next call to getAndClearLoginError()
    * removes it, so refreshing the login page won't keep re-showing it.
    */
    function setLoginError(string $message): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $_SESSION['login_error'] = $message;
    }

    /**
     * Retrieves the stored login error (if any) and clears it immediately.
     */
    function getAndClearLoginError(): ?string
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $message = $_SESSION['login_error'] ?? null;
        unset($_SESSION['login_error']);

        return $message;
    }
