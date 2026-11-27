<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: ./login.php');
  exit;
}

$backendHandler = __DIR__ . '/../backend/login.php';

if (!file_exists($backendHandler)) {
  $_SESSION['login_error'] = 'Backend handler (backend/login.php) not merged locally yet.';
  $_SESSION['old_email'] = trim($_POST['email'] ?? '');
  header('Location: ./login.php');
  exit;
}

require_once $backendHandler;

<?php if (isset($_GET['reset']) && $_GET['reset'] === 'success'): ?>
  <div class="auth-alert auth-alert--success" role="status">
    Your password has been updated. You can now sign in with your new password.
  </div>
<?php endif; ?>