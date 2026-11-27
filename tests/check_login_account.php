<?php
require_once __DIR__ . '/../backend/config/database.php';

$pdo = getDatabaseConnection();

echo "Database: " . $pdo->query("SELECT DATABASE()")->fetchColumn() . PHP_EOL;

$email = 'intern@lifechoices.test';
$password = 'LCAttends2026!';

$stmt = $pdo->prepare(
    "SELECT email, password_hash, is_active, role
     FROM staff
     WHERE email = :email
     LIMIT 1"
);

$stmt->execute([':email' => $email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    exit("RESULT: Test account NOT FOUND." . PHP_EOL);
}

echo "Account found: YES" . PHP_EOL;
echo "Active status: " . $user['is_active'] . PHP_EOL;
echo "Role: " . $user['role'] . PHP_EOL;
echo "Password verification: " .
    (password_verify($password, $user['password_hash']) ? 'PASS' : 'FAIL') .
    PHP_EOL;
