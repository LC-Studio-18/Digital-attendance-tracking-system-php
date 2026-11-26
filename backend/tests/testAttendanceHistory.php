<?php
declare(strict_types=1);

require_once __DIR__ . '/../services/attendanceHistoryService.php';

// Test 1: No filters should return an empty WHERE clause.
$result = buildAttendanceHistoryFilters([]);

assert($result['where_sql'] === '');
assert($result['params'] === []);

echo "Test 1 passed: No filters supplied.\n";

// Test 2: Start date should filter records from that date onwards.
$result = buildAttendanceHistoryFilters([
    'date_from' => '2026-11-01',
]);

assert(str_contains($result['where_sql'], 'a.clock_in >= :date_from'));
assert($result['params']['date_from'] === '2026-11-01 00:00:00');

echo "Test 2 passed: Start date filter.\n";

// Test 3: End date should include the entire selected day.
$result = buildAttendanceHistoryFilters([
    'date_to' => '2026-11-26',
]);

assert(str_contains($result['where_sql'], 'a.clock_in < :date_to'));
assert($result['params']['date_to'] === '2026-11-27 00:00:00');

echo "Test 3 passed: End date filter.\n";

// Test 4: Employee search should check both name and employee number.
$result = buildAttendanceHistoryFilters([
    'employee' => 'Khanya',
]);

assert(str_contains($result['where_sql'], 'employee_name'));
assert(str_contains($result['where_sql'], 'employee_number'));
assert($result['params']['employee_name'] === '%Khanya%');

echo "Test 4 passed: Employee search filter.\n";

// Test 5: Date and employee filters should work together.
$result = buildAttendanceHistoryFilters([
    'date_from' => '2026-11-01',
    'date_to' => '2026-11-26',
    'employee' => 'Khanya',
]);

assert(substr_count($result['where_sql'], ' AND ') === 2);
assert(count($result['params']) === 4);

echo "Test 5 passed: Combined filters.\n";

// Test 6: Invalid date ranges should return a validation error.
try {
    buildAttendanceHistoryFilters([
        'date_from' => '2026-11-27',
        'date_to' => '2026-11-01',
    ]);

    echo "Test 6 failed: Invalid date range was accepted.\n";
    exit(1);

} catch (InvalidArgumentException $exception) {
    echo "Test 6 passed: Invalid date range rejected.\n";
}

echo "\nAll attendance history filter tests completed.\n";
