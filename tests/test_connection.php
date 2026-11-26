<?php

declare(strict_types=1);

/**
 * Database connectivity test. Run from the project root:
 *   php tests/test_connection.php
 * CLI only. Not inside frontend/, so it is never served by the web server.
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Run this test from the command line.');
}

require_once __DIR__ . '/../backend/config/database.php';

$failed = 0;
function check(string $label, bool $ok, string $detail = ''): void
{
    global $failed;
    echo ($ok ? '[PASS] ' : '[FAIL] ') . $label . ($detail !== '' ? " ($detail)" : '') . PHP_EOL;
    if (!$ok) {
        $failed++;
    }
}

// If this fails, getDatabaseConnection() prints a generic error; see the PHP error log for the reason.
$pdo = getDatabaseConnection();
check('Connected to MySQL', true);

check('SELECT 1 works', (int)$pdo->query('SELECT 1')->fetchColumn() === 1);
check('PHP timezone is Africa/Johannesburg', date_default_timezone_get() === 'Africa/Johannesburg');

$tz = $pdo->query('SELECT @@session.time_zone')->fetchColumn();
check('MySQL session time zone is +02:00', $tz === '+02:00', (string)$tz);

$mysqlNow = strtotime((string)$pdo->query('SELECT NOW()')->fetchColumn());
check('MySQL NOW() matches PHP time (within 5s)', abs($mysqlNow - time()) <= 5);

foreach (['staff', 'attendance', 'audit_logs'] as $table) {
    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = :t'
    );
    $stmt->execute(['t' => $table]);
    check("Table `$table` exists", (int)$stmt->fetchColumn() === 1);
}

$fks = $pdo->query(
    "SELECT constraint_name FROM information_schema.table_constraints
     WHERE table_schema = DATABASE() AND constraint_type = 'FOREIGN KEY'"
)->fetchAll(PDO::FETCH_COLUMN);
foreach (['fk_attendance_staff', 'fk_audit_attendance', 'fk_audit_changed_by'] as $fk) {
    check("Foreign key `$fk` exists", in_array($fk, $fks, true));
}

$stmt = $pdo->prepare('SELECT COUNT(*) FROM attendance WHERE staff_id = :staff_id');
$stmt->execute(['staff_id' => 1]);
check('Prepared statement runs', $stmt->fetchColumn() !== false);

echo PHP_EOL . ($failed === 0 ? 'All checks passed.' : "$failed check(s) failed.") . PHP_EOL;
exit($failed === 0 ? 0 : 1);