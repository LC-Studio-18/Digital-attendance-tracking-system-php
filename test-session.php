<?php

declare(strict_types=1);

require_once __DIR__ . '/backend/includes/session.php';

session_start();
$beforeId = session_id();

startAuthenticatedSession(['id' => 42, 'role' => 'employee']);

$afterId = session_id();

echo "Session ID changed: " . ($beforeId !== $afterId ? "YES" : "NO") . "\n";
echo "staff_id stored: " . $_SESSION['staff_id'] . "\n";
echo "role stored: " . $_SESSION['role'] . "\n";

echo "\n--- Testing login error ---\n";

setLoginError('Invalid email or password.');
echo "First read: " . getAndClearLoginError() . "\n";

$second = getAndClearLoginError();
echo "Second read (should be empty): " . var_export($second, true) . "\n";