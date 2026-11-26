<?php

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/Staff.php';
require_once __DIR__ . '/includes/LoginController.php';
require_once __DIR__ . '/includes/session.php';

if($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: ../frontend/login.php');
  exit;
}

$pdo = getDatabaseConnection();
$staffModel = new Staff($pdo);
$login = new LoginController($staffModel);
$result = $login->handleLogin($_POST);

if ($result['success']) {
  $user = $result['user'];
  startAuthenticatedSession([
    'staff_id' => $user['staff_id'],
    'role' => $user['role'],
  ]);

  // Dashboard destinations are not present in this repository yet.
  http_response_code(501);
  exit('Dashboard routing has not been implemented.');
}

setLoginError($result['error']);
redirectTo('../frontend/login.php');
