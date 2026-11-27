<?php

declare(strict_types=1);

function redirectTo(string $path): void
{
    header('Location: ' . $path);
    exit;
}

/**
 * Returns the current attendance status for one staff member.
 *
 * IN is determined by any open record (clock_out IS NULL), regardless of
 * when that record was opened. If no open record exists, the latest
 * clock-out is returned; with no records at all, the timestamp is null.
 *
 * The timestamp is the raw MySQL DATETIME value in SAST. Formatting it for
 * the API response belongs to the endpoint, not to this query.
 *
 * @return array{status: 'IN'|'OUT', timestamp: ?string}
 */
function getAttendanceStatus(PDO $pdo, int $staffId): array
{
    if ($staffId <= 0) {
        throw new InvalidArgumentException('Invalid staff ID.');
    }

    // A single indexed lookup by staff_id. Open records take priority over
    // closed ones, so an open shift from any previous day still means IN.
    $stmt = $pdo->prepare(
        "SELECT
            CASE WHEN clock_out IS NULL THEN 'IN' ELSE 'OUT' END AS status,
            CASE WHEN clock_out IS NULL THEN clock_in ELSE clock_out END AS status_timestamp
         FROM attendance
         WHERE staff_id = :staff_id
         ORDER BY
            CASE WHEN clock_out IS NULL THEN 1 ELSE 0 END DESC,
            CASE WHEN clock_out IS NULL THEN clock_in ELSE clock_out END DESC
         LIMIT 1"
    );
    $stmt->execute(['staff_id' => $staffId]);
    $record = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($record === false) {
        return ['status' => 'OUT', 'timestamp' => null];
    }

    return [
        'status' => $record['status'] === 'IN' ? 'IN' : 'OUT',
        'timestamp' => $record['status_timestamp'] === null
            ? null
            : (string) $record['status_timestamp'],
    ];
}