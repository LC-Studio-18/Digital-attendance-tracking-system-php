<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/attendance-time.php';

$timezone = new DateTimeZone('Africa/Johannesburg');

// Test 1: Same-day shift
$sameDay = calculateAttendanceHours(
    new DateTimeImmutable('2026-11-24 08:00:00', $timezone),
    new DateTimeImmutable('2026-11-24 17:00:00', $timezone)
);

echo "Same-day shift: {$sameDay} hours\n";

// Test 2: Overnight shift
$overnight = calculateAttendanceHours(
    new DateTimeImmutable('2026-11-24 23:00:00', $timezone),
    new DateTimeImmutable('2026-11-25 03:00:00', $timezone)
);

echo "Overnight shift: {$overnight} hours\n";

// Test 3: Open shift, using a fixed current time
$openShift = calculateAttendanceHours(
    new DateTimeImmutable('2026-11-25 08:00:00', $timezone),
    null,
    new DateTimeImmutable('2026-11-25 12:30:00', $timezone)
);

echo "Open shift: {$openShift} hours\n";

// Test 4: Invalid time range
try {
    calculateAttendanceHours(
        new DateTimeImmutable('2026-11-25 17:00:00', $timezone),
        new DateTimeImmutable('2026-11-25 08:00:00', $timezone)
    );

    echo "Invalid time test: FAILED\n";
} catch (InvalidArgumentException $exception) {
    echo "Invalid time test: PASSED\n";
}


// Test 5: Exactly 24 hours
$fullDay = calculateAttendanceHours(
    new DateTimeImmutable('2026-11-24 08:00:00', $timezone),
    new DateTimeImmutable('2026-11-25 08:00:00', $timezone)
);

echo "Full-day shift: {$fullDay} hours\n";

// Test 6: Shift lasting several days
$multiDay = calculateAttendanceHours(
    new DateTimeImmutable('2026-11-24 08:00:00', $timezone),
    new DateTimeImmutable('2026-11-26 08:00:00', $timezone)
);

echo "Multi-day shift: {$multiDay} hours\n";

// Test 7: Zero-duration shift
$zeroDuration = calculateAttendanceHours(
    new DateTimeImmutable('2026-11-25 08:00:00', $timezone),
    new DateTimeImmutable('2026-11-25 08:00:00', $timezone)
);

echo "Zero-duration shift: {$zeroDuration} hours\n";

// Test 8: Exactly 48 hours
$multiDay = calculateAttendanceHours(
    new DateTimeImmutable('2026-11-24 08:00:00', $timezone),
    new DateTimeImmutable('2026-11-26 08:00:00', $timezone)
);

echo "Multi-day shift: {$multiDay} hours\n";