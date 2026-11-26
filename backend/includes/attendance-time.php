<?php
declare(strict_types=1);

function calculateAttendanceHours(
    DateTimeImmutable $clockIn,
    ?DateTimeImmutable $clockOut = null,
    ?DateTimeImmutable $now = null
): float {
    $timezone = new DateTimeZone('Africa/Johannesburg');

    // For an open shift, use the current time.
    $end = $clockOut ?? $now ?? new DateTimeImmutable('now', $timezone);

    $seconds = $end->getTimestamp() - $clockIn->getTimestamp();

    if ($seconds < 0) {
        throw new InvalidArgumentException(
            'Clock-out time cannot be earlier than clock-in time.'
        );
    }

    return round($seconds / 3600, 2);
}
