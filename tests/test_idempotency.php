<?php

declare(strict_types=1);

/**
 * S2-13B request-token/idempotency tests. Run from the project root:
 *   C:\xampp\php\php.exe tests\test_idempotency.php
 *
 * The script creates two temporary staff accounts and removes only those
 * accounts and the attendance records created for them. It never changes
 * existing staff or attendance records.
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Run this test from the command line.');
}

require_once __DIR__ . '/../backend/config/database.php';
require_once __DIR__ . '/../backend/includes/IdempotencyService.php';

/**
 * Worker mode performs one service call so the main test can run five calls
 * concurrently through the same PHP environment.
 *
 * @param array<int, string> $argv
 */
function runWorker(array $argv): void
{
    try {
        $staffId = (int) ($argv[2] ?? 0);
        $requestToken = (string) ($argv[3] ?? '');
        $clockIn = (string) ($argv[4] ?? '');

        if ($staffId <= 0 || $requestToken === '' || $clockIn === '') {
            throw new RuntimeException('Invalid worker arguments.');
        }

        $service = IdempotencyService::fromDefaultConnection();
        $result = $service->createAttendanceWithToken($staffId, $requestToken, $clockIn);

        echo json_encode($result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL;
        exit(0);
    } catch (Throwable $exception) {
        error_log('S2-13B idempotency worker failed: ' . $exception->getMessage());
        echo json_encode([
            'ok'       => false,
            'replayed' => false,
            'record'   => null,
            'code'     => 'WORKER_FAILED',
            'message'  => 'The worker could not complete the request.',
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL;
        exit(1);
    }
}

if (($argv[1] ?? '') === '--worker') {
    runWorker($argv);
}

$failed = 0;

/**
 * @param array<int, int> $staffIds
 */
function cleanupTestStaff(PDO $pdo, array $staffIds): void
{
    if ($staffIds === []) {
        return;
    }

    $placeholders = implode(', ', array_fill(0, count($staffIds), '?'));

    // Temporary staff are created by this script, so their attendance rows
    // are test data too. No existing user records are touched.
    $deleteAttendance = $pdo->prepare(
        "DELETE FROM attendance WHERE staff_id IN ($placeholders)"
    );
    $deleteAttendance->execute($staffIds);

    $deleteStaff = $pdo->prepare(
        "DELETE FROM staff WHERE staff_id IN ($placeholders)"
    );
    $deleteStaff->execute($staffIds);
}

function check(string $label, bool $ok, string $detail = ''): bool
{
    global $failed;

    echo ($ok ? '[PASS] ' : '[FAIL] ') . $label . ($detail !== '' ? " ($detail)" : '') . PHP_EOL;

    if (!$ok) {
        $failed++;
    }

    return $ok;
}

function hasRequestTokenColumn(PDO $pdo): bool
{
    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM information_schema.columns
         WHERE table_schema = DATABASE() AND table_name = :table_name AND column_name = :column_name'
    );
    $stmt->execute([':table_name' => 'attendance', ':column_name' => 'request_token']);

    return (int) $stmt->fetchColumn() === 1;
}

function hasUniqueTokenIndex(PDO $pdo): bool
{
    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM information_schema.statistics
         WHERE table_schema = DATABASE() AND table_name = :table_name
           AND index_name = :index_name AND non_unique = 0'
    );
    $stmt->execute([
        ':table_name' => 'attendance',
        ':index_name' => 'uq_attendance_request_token',
    ]);

    return (int) $stmt->fetchColumn() === 1;
}

function canDeleteTestData(PDO $pdo): bool
{
    // Works for the application database and for a dedicated disposable test
    // database: only grants covering the currently selected database count.
    $database = (string) $pdo->query('SELECT DATABASE()')->fetchColumn();
    $quotedDatabase = '`' . str_replace('`', '``', $database) . '`';

    foreach ($pdo->query('SHOW GRANTS FOR CURRENT_USER()')->fetchAll(PDO::FETCH_COLUMN) as $grant) {
        $hasDelete = stripos($grant, 'DELETE') !== false || stripos($grant, 'ALL PRIVILEGES') !== false;
        $coversDatabase = stripos($grant, 'ON *.*') !== false
            || stripos($grant, 'ON ' . $quotedDatabase . '.*') !== false
            || stripos($grant, 'ON ' . $database . '.*') !== false;

        if ($hasDelete && $coversDatabase) {
            return true;
        }
    }

    return false;
}

function uniqueToken(string $label): string
{
    return 'S2-13B-' . $label . '-' . strtoupper(bin2hex(random_bytes(6)));
}

function createTestStaff(PDO $pdo, string $suffix, int $number): int
{
    $employeeNumber = 'S2B' . $suffix . $number;
    $email = 's2-13b-' . strtolower($suffix) . '-' . $number . '@example.test';
    $passwordHash = password_hash('Password123!', PASSWORD_DEFAULT);

    $stmt = $pdo->prepare(
        'INSERT INTO staff (employee_number, first_name, last_name, email, password_hash, role)
         VALUES (:employee_number, :first_name, :last_name, :email, :password_hash, :role)'
    );
    $stmt->execute([
        ':employee_number' => $employeeNumber,
        ':first_name'      => 'S2-13B',
        ':last_name'       => 'Test',
        ':email'           => $email,
        ':password_hash'   => $passwordHash,
        ':role'            => 'staff',
    ]);

    return (int) $pdo->lastInsertId();
}

function countRowsForToken(PDO $pdo, string $requestToken): int
{
    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM attendance WHERE request_token = :request_token'
    );
    $stmt->execute([':request_token' => $requestToken]);

    return (int) $stmt->fetchColumn();
}

function countRowsForStaff(PDO $pdo, int $staffId): int
{
    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM attendance WHERE staff_id = :staff_id'
    );
    $stmt->execute([':staff_id' => $staffId]);

    return (int) $stmt->fetchColumn();
}

/**
 * A service result must explicitly contain a null record, not a missing key.
 *
 * @param array<string, mixed> $result
 */
function recordIsNull(array $result): bool
{
    return array_key_exists('record', $result) && $result['record'] === null;
}

/**
 * @param array<string, mixed>|null $first
 * @param array<string, mixed>|null $second
 */
function sameAttendanceRecord(?array $first, ?array $second): bool
{
    if ($first === null || $second === null) {
        return false;
    }

    foreach (['record_id', 'staff_id', 'request_token', 'clock_in', 'clock_out'] as $field) {
        if (($first[$field] ?? null) !== ($second[$field] ?? null)) {
            return false;
        }
    }

    return true;
}

/**
 * Starts five worker processes before reading any output so the requests
 * genuinely overlap.
 *
 * @return array<int, array{exit: int, result: ?array<string, mixed>}>
 */
function runFiveConcurrentRequests(int $staffId, string $requestToken, string $clockIn): array
{
    $descriptorSpec = [
        0 => ['pipe', 'r'],
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w'],
    ];

    $processes = [];

    for ($i = 0; $i < 5; $i++) {
        $command = [PHP_BINARY, __FILE__, '--worker', (string) $staffId, $requestToken, $clockIn];
        $process = proc_open($command, $descriptorSpec, $pipes);

        if (!is_resource($process)) {
            throw new RuntimeException('Could not start an idempotency worker process.');
        }

        fclose($pipes[0]);
        $processes[] = [
            'process' => $process,
            'stdout'  => $pipes[1],
            'stderr'  => $pipes[2],
        ];
    }

    $runs = [];

    foreach ($processes as $worker) {
        $stdout = stream_get_contents($worker['stdout']);
        $stderr = stream_get_contents($worker['stderr']);
        fclose($worker['stdout']);
        fclose($worker['stderr']);
        $exitCode = proc_close($worker['process']);

        $decoded = json_decode((string) $stdout, true);

        if ($exitCode !== 0 || $stderr !== '') {
            error_log('S2-13B worker exit=' . $exitCode . ' stderr=' . $stderr);
        }

        $runs[] = [
            'exit'   => $exitCode,
            'result' => is_array($decoded) ? $decoded : null,
        ];
    }

    return $runs;
}

$pdo = getDatabaseConnection();
$service = new IdempotencyService($pdo);
$createdStaffIds = [];

$schemaReady = check(
    'attendance.request_token exists',
    hasRequestTokenColumn($pdo),
    'apply database/schema.sql to this database'
);
$indexReady = check(
    'Unique request_token constraint exists',
    hasUniqueTokenIndex($pdo),
    'apply database/schema.sql to this database'
);
$cleanupReady = check(
    'Database user can remove its own test data',
    canDeleteTestData($pdo),
    'grant DELETE on the configured database to the configured database user'
);

if (!$schemaReady || !$indexReady || !$cleanupReady) {
    echo PHP_EOL . 'S2-13B tests cannot run until the database prerequisites pass.' . PHP_EOL;
    exit(1);
}

try {
    $suffix = strtoupper(bin2hex(random_bytes(4)));
    $staffOneId = createTestStaff($pdo, $suffix, 1);
    $createdStaffIds[] = $staffOneId;
    $staffTwoId = createTestStaff($pdo, $suffix, 2);
    $createdStaffIds[] = $staffTwoId;

    $clockIn = date('Y-m-d H:i:s');

    // 1. A new request token creates exactly one attendance record.
    $newToken = uniqueToken('NEW');
    $firstResult = $service->createAttendanceWithToken($staffOneId, $newToken, $clockIn);
    $firstRecord = is_array($firstResult['record'] ?? null) ? $firstResult['record'] : null;

    check(
        'New request token creates one attendance record',
        ($firstResult['ok'] ?? false) === true
            && ($firstResult['replayed'] ?? true) === false
            && ($firstResult['code'] ?? '') === 'ATTENDANCE_RECORDED'
            && ($firstResult['message'] ?? '') === 'Attendance recorded successfully.'
            && is_array($firstRecord)
            && countRowsForToken($pdo, $newToken) === 1,
        'code=' . ($firstResult['code'] ?? 'none') . ', rows=' . countRowsForToken($pdo, $newToken)
    );

    // 2. The same token returns the original result without another record.
    $replayResult = $service->createAttendanceWithToken($staffOneId, $newToken, $clockIn);
    $replayRecord = is_array($replayResult['record'] ?? null) ? $replayResult['record'] : null;

    check(
        'Repeated token returns the original result without a second record',
        ($replayResult['ok'] ?? false) === true
            && ($replayResult['replayed'] ?? false) === true
            && ($replayResult['code'] ?? '') === 'CLOCK_ALREADY_RECORDED'
            && ($replayResult['message'] ?? '') === 'Attendance already recorded for this request.'
            && sameAttendanceRecord($firstRecord, $replayRecord)
            && countRowsForToken($pdo, $newToken) === 1,
        'code=' . ($replayResult['code'] ?? 'none') . ', rows=' . countRowsForToken($pdo, $newToken)
    );

    // 3. Five overlapping requests with the same token create only one record.
    $raceToken = uniqueToken('RACE');
    $raceRuns = runFiveConcurrentRequests($staffOneId, $raceToken, $clockIn);

    $allRunsOk = count($raceRuns) === 5;
    $replayCount = 0;
    $recordIds = [];

    foreach ($raceRuns as $run) {
        $result = $run['result'];

        if ($run['exit'] !== 0 || $result === null || ($result['ok'] ?? false) !== true) {
            $allRunsOk = false;
            continue;
        }

        if (($result['replayed'] ?? false) === true) {
            $replayCount++;
        }

        $record = $result['record'] ?? null;
        if (is_array($record) && isset($record['record_id'])) {
            $recordIds[] = (int) $record['record_id'];
        }
    }

    $distinctRecordIds = count(array_unique($recordIds));

    check(
        'Five rapid same-token requests create only one attendance record',
        $allRunsOk
            && $replayCount === 4
            && $distinctRecordIds === 1
            && countRowsForToken($pdo, $raceToken) === 1,
        'ok=' . count($raceRuns) . '/5, replays=' . $replayCount
            . ', distinct_ids=' . $distinctRecordIds . ', rows=' . countRowsForToken($pdo, $raceToken)
    );

    // 4. A token owned by one staff member cannot be reused by another.
    $otherUserToken = uniqueToken('USER');
    $ownerResult = $service->createAttendanceWithToken($staffOneId, $otherUserToken, $clockIn);
    $mismatchResult = $service->createAttendanceWithToken($staffTwoId, $otherUserToken, $clockIn);
    $ownerRecord = is_array($ownerResult['record'] ?? null) ? $ownerResult['record'] : null;

    check(
        "A different staff member cannot reuse another staff member's token",
        ($ownerResult['ok'] ?? false) === true
            && ($mismatchResult['ok'] ?? false) === false
            && ($mismatchResult['code'] ?? '') === 'TOKEN_USER_MISMATCH'
            && ($mismatchResult['message'] ?? '') === 'This request token cannot be used for your account.'
            && recordIsNull($mismatchResult)
            && ($ownerRecord['staff_id'] ?? null) === $staffOneId
            && countRowsForToken($pdo, $otherUserToken) === 1,
        'code=' . ($mismatchResult['code'] ?? 'none') . ', rows=' . countRowsForToken($pdo, $otherUserToken)
    );

    // 5. Missing, empty and over-long tokens are rejected without creating rows.
    $beforeInvalid = countRowsForStaff($pdo, $staffOneId);
    $nullCheck = $service->inspectToken($staffOneId, null);
    $emptyResult = $service->createAttendanceWithToken($staffOneId, '', $clockIn);
    $spaceResult = $service->createAttendanceWithToken($staffOneId, '   ', $clockIn);
    $longToken = str_repeat('X', 65);
    $longResult = $service->createAttendanceWithToken($staffOneId, $longToken, $clockIn);
    $afterInvalid = countRowsForStaff($pdo, $staffOneId);

    $invalidChecks = [
        $nullCheck,
        $emptyResult,
        $spaceResult,
        $longResult,
    ];
    $allInvalidRejected = true;

    foreach ($invalidChecks as $invalid) {
        if (($invalid['code'] ?? '') !== 'INVALID_REQUEST_TOKEN'
            || ($invalid['message'] ?? '') !== 'A valid request token is required.'
        ) {
            $allInvalidRejected = false;
        }
    }

    $noRecordsCreated = recordIsNull($emptyResult)
        && recordIsNull($spaceResult)
        && recordIsNull($longResult);

    check(
        'Invalid or missing tokens are rejected appropriately',
        $allInvalidRejected && $noRecordsCreated && $beforeInvalid === $afterInvalid,
        'rows_before=' . $beforeInvalid . ', rows_after=' . $afterInvalid
    );
} catch (Throwable $exception) {
    check('S2-13B idempotency tests completed', false, 'see PHP error log');
    error_log('S2-13B idempotency test error: ' . $exception->getMessage()
        . ' in ' . $exception->getFile() . ':' . $exception->getLine());
} finally {
    try {
        cleanupTestStaff($pdo, $createdStaffIds);
        if ($createdStaffIds !== []) {
            check('Temporary test staff and attendance removed', true);
        }
    } catch (Throwable $exception) {
        check('Temporary test staff and attendance removed', false, 'see PHP error log');
        error_log('S2-13B cleanup error: ' . $exception->getMessage());
    }
}

echo PHP_EOL . ($failed === 0 ? 'All S2-13B idempotency tests passed.' : "$failed check(s) failed.") . PHP_EOL;
exit($failed === 0 ? 0 : 1);
