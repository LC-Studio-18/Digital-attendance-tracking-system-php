<?php
declare(strict_types=1);

require_once __DIR__ . '/../backend/config/database.php';
require_once __DIR__ . '/../backend/includes/dashboard-queries.php';

$pdo = getDatabaseConnection();

$staffIds = [1, 2, 3];
$passed = 0;
$failed = 0;

function checkResult(string $description, bool $condition): void
{
    global $passed, $failed;

    if ($condition) {
        echo "[PASS] {$description}\n";
        $passed++;
    } else {
        echo "[FAIL] {$description}\n";
        $failed++;
    }
}

foreach ($staffIds as $staffId) {
    echo "\n====================================\n";
    echo "TESTING STAFF ID: {$staffId}\n";
    echo "====================================\n";

    // Run the dashboard query functions.
    $hours = getStaffHoursSummary($pdo, $staffId);
    $records = getRecentAttendanceRecords($pdo, $staffId);
    $status = getStaffCurrentStatus($pdo, $staffId);

    echo "Hours summary:\n";
    print_r($hours);

    echo "Current status: {$status}\n";
    echo "Records returned: " . count($records) . "\n";

    // Seed data expectations.
    if ($staffId === 1 || $staffId === 2) {
        checkResult(
            "Staff {$staffId} has seeded attendance records",
            count($records) > 0
        );
    }

    if ($staffId === 3) {
        checkResult(
            "Staff 3 has no attendance records",
            count($records) === 0
        );
    }

    // Verify ownership of every returned attendance record.
    foreach ($records as $record) {
        $ownershipStmt = $pdo->prepare(
            'SELECT staff_id FROM attendance WHERE record_id = :record_id'
        );

        $ownershipStmt->execute([
            ':record_id' => $record['record_id']
        ]);

        $actualStaffId = $ownershipStmt->fetchColumn();

        checkResult(
            "Record {$record['record_id']} belongs to Staff {$staffId}",
            (int) $actualStaffId === $staffId
        );
    }
}

echo "\n====================================\n";
echo "TEST SUMMARY\n";
echo "====================================\n";
echo "Passed: {$passed}\n";
echo "Failed: {$failed}\n";

if ($failed > 0) {
    exit(1);
}

echo "\nAll database query isolation tests passed.\n";
