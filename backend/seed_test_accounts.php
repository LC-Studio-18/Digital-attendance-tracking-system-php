<?php
// Prevent the test-account seeder from being accessed through a browser.
if (PHP_SAPI !== 'cli') {
    // Return HTTP 403 when browser access is attempted.
    http_response_code(403);

    // Stop execution before any test accounts are created.
    exit('Access denied. This script can only be run from the command line.');
}

// Load the shared database connection function.
require_once __DIR__ . '/config/database.php';

// Retrieve the shared PDO database connection.
$pdo = getDatabaseConnection();

if (!$pdo instanceof PDO) {
  throw new RuntimeException('Database configuration did not provide a PDO connection.');
}

$accounts = [
  // active staff
  ['email' => 'intern@lifechoices.test', 'password' => 'LCAttends2026!', 'role' => 'staff', 'is_active' => 1],
  // active admin
  ['email' => 'admin@test.com', 'password' => 'Admin123!', 'role' => 'admin', 'is_active' => 1],
  // deactivated
  ['email' => 'deactivated@test.com', 'password' => 'Deactivated123!', 'role' => 'staff', 'is_active' => 0],
];

$sql = "INSERT INTO staff (email, password_hash, role, is_active) VALUES (:email, :password_hash, :role, :is_active) ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash), role = VALUES(role), is_active = VALUES(is_active)";

$stmt = $pdo->prepare($sql);

foreach ($accounts as $acc) {
  $stmt->execute([
    ':email' => $acc['email'],
    ':password_hash' => password_hash($acc['password'], PASSWORD_DEFAULT),

    ':role' => $acc['role'],
    ':is_active' => $acc['is_active'],
  ]);
  echo "Seeded: {$acc['email']} ({$acc['role']}, active={$acc['is_active']})\n";
}

// Confirm that all test accounts have been processed.
echo "Done.\n";
