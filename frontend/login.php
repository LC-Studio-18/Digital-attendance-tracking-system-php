<?php

declare(strict_types=1);

require_once __DIR__ . '/../backend/config/config.php';
require_once __DIR__ . '/../backend/includes/functions.php';

session_start();

// Staff who are already signed in land straight on the dashboard.
if (!empty($_SESSION['staff_id'])) {
    redirectTo('index.php');
}

$loginError = null;

// Non-JavaScript fallback. The backend team can supply authenticate_staff()
// in backend/includes/functions.php; once present this redirect works as-is.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if (function_exists('authenticate_staff')) {
        $staff = authenticate_staff($username, $password);

        if (is_array($staff) && !empty($staff['id'])) {
            session_regenerate_id(true);
            $_SESSION['staff_id'] = (int) $staff['id'];
            redirectTo('index.php');
        }
    }

    $loginError = 'Login is not connected yet. Please try again once authentication is available.';
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign in | <?php echo htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="stylesheet" href="assets/css/variables.css">
    <link rel="stylesheet" href="assets/css/base.css">
    <link rel="stylesheet" href="assets/css/login.css">
</head>
<body>
    <main class="login">
        <h1>Sign in</h1>

        <?php if ($loginError !== null): ?>
            <p class="login__error" id="login-error" role="alert">
                <?php echo htmlspecialchars($loginError, ENT_QUOTES, 'UTF-8'); ?>
            </p>
        <?php else: ?>
            <p class="login__error" id="login-error" role="alert" hidden></p>
        <?php endif; ?>

        <form id="login-form" method="post" action="login.php">
            <div class="login__field">
                <label for="username">Username</label>
                <input id="username" name="username" type="text" required autocomplete="username">
            </div>

            <div class="login__field">
                <label for="password">Password</label>
                <input id="password" name="password" type="password" required autocomplete="current-password">
            </div>

            <button type="submit">Sign in</button>
        </form>
    </main>

    <script src="assets/js/login.js"></script>
</body>
</html>
