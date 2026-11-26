<?php

declare(strict_types=1);

final class AttendanceModel
{
    public function __construct(private PDO $pdo)
    {
    }

     /**
     * Records a new clock-in for an employee.
     *
     * Returns the new attendance record.
     * Returns null if the employee already has an active clock-in.
     */

    public function clockIn(int $staffId, DateTimeImmutable $now): ?array
    {

        // Check whether this employee already has an open attendance record.
        $stmt = $this->pdo->prepare(
            'SELECT record_id
             FROM attendance
             WHERE staff_id = :staff_id
             AND clock_out IS NULL
             LIMIT 1'
        );
        $stmt->execute(['staff_id' => $staffId]);
        $active = $stmt->fetch();

        if ($active) {
            return null;
        }

        $clockIn = $now->format('Y-m-d H:i:s');
        

        $insert = $this->pdo->prepare(
            'INSERT INTO attendance (staff_id, clock_in)
             VALUES (:staff_id, :clock_in)'
        );

        $insert-> execute([
            'staff_id' => $staffId,
            'clock_in' => $clockIn,
        ]);

        $recordId = (int) $this ->pdo->lastInsertId();

        return [
            'record_id'        => $recordId,
            'staff_id'         => $staffId,
            'clock_in'         => $clockIn,
            'clock_out'        => null,
        ];
    }



/**
 * Retrieves the employee's current active attendance record.
 *
 * Returns the active record if the employee is clocked in.
 * Returns null if the employee has no active clock-in.
 */

/* This method returns the newest active record.
 * Duplicate prevention is related to team ticket S2-10
 * and should be coordinated with that work.
*/

public function getActiveSession(int $staffId): ?array
{
    // Find an attendance record that has not been closed.
    $stmt = $this->pdo->prepare(
        'SELECT record_id, staff_id, clock_in, clock_out
         FROM attendance
         WHERE staff_id = :staff_id
         AND clock_out IS NULL
         ORDER BY clock_in DESC
         LIMIT 1'
    );

    $stmt->execute(['staff_id' => $staffId]);

    $active = $stmt->fetch();

    // No active attendance record was found.
    if (!$active) {
        return null;
    }

    // Return the employee's current attendance details.
    return [
        'record_id' => (int) $active['record_id'],
        'staff_id'  => (int) $active['staff_id'],
        'clock_in'  => $active['clock_in'],
        'clock_out' => $active['clock_out'],
    ];
}
   

    /**
     * Closes the user's active clock-in session: sets clock_out and duration_seconds.
     * Returns the closed record, or null if there is no active session.
     */
    public function clockOut(int $staffId, DateTimeImmutable $now): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT record_id, clock_in
             FROM attendance
             WHERE staff_id = :staff_id AND clock_out IS NULL
             ORDER BY clock_in DESC
             LIMIT 1'
        );
        $stmt->execute(['staff_id' => $staffId]);
        $active = $stmt->fetch();

        if (!$active) {
            return null;
        }

        $clockOut = $now->format('Y-m-d H:i:s');
        $clockIn = new DateTimeImmutable($active['clock_in'], $now->getTimezone());
        $duration = $now->getTimestamp() - $clockIn->getTimestamp();

        $update = $this->pdo->prepare(
            'UPDATE attendance
             SET clock_out = :clock_out, duration_seconds = :duration
             WHERE record_id = :record_id'
        );
        $update->execute([
            'clock_out' => $clockOut,
            'duration'  => $duration,
            'record_id' => $active['record_id'],
        ]);

        return [
            'record_id'        => (int) $active['record_id'],
            'clock_in'         => $active['clock_in'],
            'clock_out'        => $clockOut,
            'duration_seconds' => $duration,
        ];
    }
}
