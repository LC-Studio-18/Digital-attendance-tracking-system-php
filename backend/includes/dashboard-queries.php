<?php
declare(strict_types=1);

/**
 * Get the employee's attendance hours for today and this week.
 *
 * Overnight shifts are split across calendar days.
 * The week starts on Monday.
 *
 * @return array{today: float, week: float}
 */
function getStaffHoursSummary(PDO $pdo, int $staffId): array
{
    if ($staffId <= 0) {
        throw new InvalidArgumentException('Invalid staff ID.');
    }

    $timezone = new DateTimeZone('Africa/Johannesburg');
    $now = new DateTimeImmutable('now', $timezone);

    // Get the beginning of today and the current week.
    $todayStart = $now->setTime(0, 0, 0);
    $weekStart = $todayStart->modify('monday this week');

    // Retrieve attendance records that overlap the current week.
    $sql = "
        SELECT clock_in, clock_out
        FROM attendance
        WHERE staff_id = :staff_id
          AND clock_in < :now_time
          AND (clock_out IS NULL OR clock_out > :week_start)
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        'staff_id' => $staffId,
        'now_time' => $now->format('Y-m-d H:i:s'),
        'week_start' => $weekStart->format('Y-m-d H:i:s')
    ]);

    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $todaySeconds = 0;
    $weekSeconds = 0;

    foreach ($records as $record) {
        $clockIn = new DateTimeImmutable(
            $record['clock_in'],
            $timezone
        );

        // An open shift continues up to the current time.
        $clockOut = $record['clock_out'] !== null
            ? new DateTimeImmutable($record['clock_out'], $timezone)
            : $now;

        // Do not count time beyond the current moment.
        if ($clockOut > $now) {
            $clockOut = $now;
        }

        // Calculate the portion of the shift within this week.
        $weekOverlapStart = $clockIn > $weekStart
            ? $clockIn
            : $weekStart;

        $weekOverlapEnd = $clockOut < $now
            ? $clockOut
            : $now;

        if ($weekOverlapEnd > $weekOverlapStart) {
            $weekSeconds +=
                $weekOverlapEnd->getTimestamp()
                - $weekOverlapStart->getTimestamp();
        }

        // Calculate only the portion of the shift within today.
        $todayOverlapStart = $clockIn > $todayStart
            ? $clockIn
            : $todayStart;

        $todayOverlapEnd = $clockOut < $now
            ? $clockOut
            : $now;

        if ($todayOverlapEnd > $todayOverlapStart) {
            $todaySeconds +=
                $todayOverlapEnd->getTimestamp()
                - $todayOverlapStart->getTimestamp();
        }
    }

    return [
        'today' => round($todaySeconds / 3600, 2),
        'week' => round($weekSeconds / 3600, 2)
    ];
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

    $timezone = new DateTimeZone('Africa/Johannesburg');
    $now = new DateTimeImmutable('now', $timezone);
    $nowTime = $now->format('Y-m-d H:i:s');

    $sql = "
        SELECT
            a.record_id,
            a.clock_in,
            a.clock_out,
            ROUND(
                TIMESTAMPDIFF(
                    SECOND,
                    a.clock_in,
                    LEAST(
                        COALESCE(a.clock_out, :now_time_open),
                        :now_time_cap
                    )
                ) / 3600,
                2
            ) AS hours,
            CASE
                WHEN EXISTS (
                    SELECT 1
                    FROM audit_logs al
                    WHERE al.record_id = a.record_id
                ) THEN TRUE
                ELSE FALSE
            END AS is_corrected
        FROM attendance a
        WHERE a.staff_id = :staff_id
        ORDER BY a.clock_in DESC
        LIMIT 10
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        'now_time_open' => $nowTime,
        'now_time_cap' => $nowTime,
        'staff_id' => $staffId
    ]);

    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return array_map(function ($record) {
        return [
            'record_id' => (int) $record['record_id'],
            'clock_in' => $record['clock_in'],
            'clock_out' => $record['clock_out'],
            'hours' => $record['hours'] !== null
                ? (float) $record['hours']
                : null,
            'is_corrected' => (bool) $record['is_corrected']
        ];
    }, $records);
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

    $sql = "
        SELECT clock_out
        FROM attendance
        WHERE staff_id = :staff_id
        ORDER BY clock_in DESC
        LIMIT 1
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        'staff_id' => $staffId
    ]);

    $record = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$record) {
        return 'OUT';
    }

    return $record['clock_out'] === null ? 'IN' : 'OUT';
}