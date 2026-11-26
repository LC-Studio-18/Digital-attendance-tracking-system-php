<?php
declare(strict_types=1);

require_once __DIR__ . '/../backend/includes/dashboard-queries.php';

class TestStatement extends PDOStatement
{
    public function __construct()
    {
    }

    public function execute(?array $params = null): bool
    {
        return true;
    }

    public function fetchAll(int $mode = PDO::FETCH_DEFAULT, mixed ...$args): array
    {
        return [];
    }

    public function fetch(int $mode = PDO::FETCH_DEFAULT, int $cursorOrientation = PDO::FETCH_ORI_NEXT, int $cursorOffset = 0): mixed
    {
        return false;
    }
}

class TestPDO extends PDO
{
    public function __construct()
    {
    }

    public function prepare(string $query, array $options = []): PDOStatement|false
    {
        return new TestStatement();
    }
}

$pdo = new TestPDO();

$functions = [
    'getStaffHoursSummary',
    'getRecentAttendanceRecords',
    'getStaffCurrentStatus'
];

echo "Testing invalid staff IDs:\n";

foreach ($functions as $function) {
    try {
        $function($pdo, 0);
        echo "$function: FAILED - Invalid ID was accepted.\n";
    } catch (InvalidArgumentException $exception) {
        echo "$function: PASSED - Invalid staff ID rejected.\n";
    }
}

echo "\nTesting valid staff IDs:\n";

foreach ($functions as $function) {
    try {
        $result = $function($pdo, 1);
        echo "$function: PASSED - Query executed successfully.\n";
    } catch (Throwable $exception) {
        echo "$function: FAILED - " . $exception->getMessage() . "\n";
    }
}
