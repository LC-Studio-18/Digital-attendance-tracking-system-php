<?php

declare(strict_types=1);

/**
 * S2-09B clock-out tests. Run from the project root:
 *   TEST_DB_USER=root TEST_DB_PASS=yourpass php tests/clock_out_test.php
 * Optional: TEST_DB_HOST (default 127.0.0.1), TEST_DB_PORT (default 3306).
 *
 * Builds a throwaway database "attendance_test" from database/schema.sql plus the
 * S2-09B migration, so it never touches attendance_db. It DROPS attendance_test first.
 * Clock-in records (S2-08 not finished) are inserted directly with SQL.
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Run this test from the command line.');
}

require_once __DIR__ . '/../backend/models/AttendanceModel.php';

$pdo = new PDO(
    sprintf('mysql:host=%s;port=%d;charset=utf8mb4', getenv('TEST_DB_HOST') ?: '127.0.0.1', (int) (getenv('TEST_DB_PORT') ?: 3306)),
    getenv('TEST_DB_USER') ?: 'root',
    getenv('TEST_DB_PASS') ?: '',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
);
$pdo->exec('DROP DATABASE IF EXISTS attendance_test');
foreach (['schema.sql', 's2-09b_add_duration_seconds.sql'] as $file) {
    $pdo->exec(str_replace('attendance_db', 'attendance_test', file_get_contents(__DIR__ . '/../database/' . $file)));
}
$pdo->exec("INSERT INTO staff (employee_number, first_name, last_name, email, password_hash)
            VALUES ('T1', 'A', 'One', 'a@test.local', 'x'), ('T2', 'B', 'Two', 'b@test.local', 'x')");

$failed = 0;
function check(string $label, bool $ok): void
{
    global $failed;
    echo ($ok ? '[PASS] ' : '[FAIL] ') . $label . PHP_EOL;
    $failed += $ok ? 0 : 1;
}

$model = new AttendanceModel($pdo);
$now = new DateTimeImmutable('2026-11-26 17:00:00', new DateTimeZone('Africa/Johannesburg'));
$pdo->exec("INSERT INTO attendance (staff_id, clock_in) VALUES (1, '2026-11-26 08:30:15')");

// Valid clock-out
$result = $model->clockOut(1, $now);
check('valid clock-out returns the closed record', $result !== null && $result['clock_out'] === '2026-11-26 17:00:00' && $result['duration_seconds'] === 30585);
$row = $pdo->query('SELECT clock_out, duration_seconds FROM attendance WHERE record_id = 1')->fetch();
check('clock_out and duration are saved in the database', $row['clock_out'] === '2026-11-26 17:00:00' && (int) $row['duration_seconds'] === 30585);

// Duplicate clock-out
check('duplicate clock-out returns null', $model->clockOut(1, $now->modify('+1 hour')) === null);
$row = $pdo->query('SELECT clock_out FROM attendance WHERE record_id = 1')->fetch();
check('duplicate clock-out leaves the record unchanged', $row['clock_out'] === '2026-11-26 17:00:00');

// Missing active session (staff 2 never clocked in)
check('user with no clock-in returns null', $model->clockOut(2, $now) === null);

// Unauthorized: run the real controller with no logged-in session
$output = (string) shell_exec(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg(__DIR__ . '/../backend/controllers/clock-out.php') . ' 2>/dev/null');
check('no login returns UNAUTHENTICATED (401)', str_contains($output, '"code":"UNAUTHENTICATED"'));

$pdo->exec('DROP DATABASE attendance_test');
echo PHP_EOL . ($failed === 0 ? 'All tests passed.' : "$failed test(s) FAILED.") . PHP_EOL;
exit($failed === 0 ? 0 : 1);
