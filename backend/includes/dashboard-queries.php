<?php
declare(strict_types=1);

/**
 * Get the employee's attendance hours for today and this week.
 *
 * @return array{today: float, week: float}
 */
function getStaffHoursSummary(PDO $pdo, int $staffId): array
{
    if ($staffId <= 0) {
        throw new InvalidArgumentException('Invalid staff ID.');
    }

    // TODO: Add database queries once the attendance schema is available.
    throw new LogicException('Hours summary query not implemented yet.');
}


/**
 * Get the employee's 10 most recent attendance records.
 *
 * @return array<int, array{
 *     record_id: int,
 *     clock_in: string,
 *     clock_out: ?string,
 *     hours: ?float,
 *     is_corrected: bool
 * }>
 */
function getRecentAttendanceRecords(PDO $pdo, int $staffId): array
{
    if ($staffId <= 0) {
        throw new InvalidArgumentException('Invalid staff ID.');
    }

    // TODO: Add database query once the attendance schema is available.
    throw new LogicException('Recent records query not implemented yet.');
}


/**
 * Get the employee's current attendance status.
 *
 * @return string 'IN' or 'OUT'
 */
function getStaffCurrentStatus(PDO $pdo, int $staffId): string
{
    if ($staffId <= 0) {
        throw new InvalidArgumentException('Invalid staff ID.');
    }

    // TODO: Add database query once the attendance schema is available.
    throw new LogicException('Current status query not implemented yet.');
}
