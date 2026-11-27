<?php
session_start();
require_once __DIR__ . '/config/database.php';
$pdo = $GLOBALS['pdo'] ?? null;

$email = trim($_POST['email'] ?? '');
$newPassword = $_POST['new_password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

function backWithError(string $msg) {
  $_SESSION['forgot_error'] = $msg;
  header('Location: ../frontend/reset-password.php');
  exit;
}

if (empty($email) || empty($newPassword) || empty($confirmPassword)) {
  backWithError('Please fill in all fields.');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  backWithError('Please enter a valid email address.');
}

if (strlen($newPassword) < 8) {
  backWithError('New password must be at least 8 characters long.');
}

if ($newPassword !== $confirmPassword) {
  backWithError('Passwords do not match.');
}

// verify Active staff account
$stmt = $pdo->prepare ("SELECT staff_id FROM staff WHERE email = ? AND is_active = 1 LIMIT 1");
$stmt->execute([$email]);
$staff = $stmt->fetch();

if (!$staff) {
  backWithError('No active staff account found with that email.');
}

$hashed = password_hash($newPassword, PASSWORD_DEFAULT);
$update = $pdo->prepare("UPDATE staff SET password = ? WHERE staff_id = ?");
$update->execute([$hashed, $staff['staff_id']]);

$_SESSION['login_success'] = 'Password updated successfully.';
header('Location: ../frontend/login.php');
exit;