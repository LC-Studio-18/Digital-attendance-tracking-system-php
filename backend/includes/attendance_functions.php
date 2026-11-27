
<?php

declare(strict_types=1);

/**
 * ============================================================
 * ATTENDANCE MODEL
 * ============================================================
 *
 * ASSIGNED RESPONSIBILITIES:
 *
 * 1. Fetch the existing attendance record.
 * 2. Retrieve and preserve old_clock_in and old_clock_out.
 * 3. Update the attendance table with the new times.
 *
 * API INTEGRATION:
 * This model is intended to be called by:
 *
 * api/correct_attendance.php
 *
 * The API handles the HTTP request.
 * This model handles the database operations.
 *
 * DATABASE:
 * Uses the existing PDO connection from:
 * backend/config/database.php
 *
 * ASSUMED DATABASE STRUCTURE:
 *
 * Table: attendance
 * Primary key: attendance_id
 * Clock-in column: clock_in
 * Clock-out column: clock_out
 *
 * These names must be checked against the actual MySQL schema.
 */

class Attendance
{
    /**
     * PDO database connection.
     */
    private PDO $pdo;

    /**
     * Constructor.
     *
     * Receives an existing PDO connection rather than
     * creating a separate database connection.
     *
     * This allows the API to reuse the project's
     * existing database configuration.
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * ========================================================
     * 1. FETCH EXISTING ATTENDANCE RECORD
     * ========================================================
     *
     * Retrieves the current clock-in and clock-out values.
     *
     * FOR UPDATE:
     * Locks the selected attendance row while a transaction
     * is active.
     *
     * This prevents another transaction from changing
     * the same record before our update is completed.
     *
     * RETURNS:
     *
     * [
     *     'old_clock_in' => existing clock-in time,
     *     'old_clock_out' => existing clock-out time
     * ]
     *
     * This method is private because the public update method
     * manages the complete fetch-and-update operation.
     */
    private function getOldAttendanceTimes(
        int $attendanceId
    ): array {

        $sql = "
            SELECT
                clock_in AS old_clock_in,
                clock_out AS old_clock_out
            FROM attendance
            WHERE attendance_id = :attendance_id
            FOR UPDATE
        ";

        // Prepare the SQL statement.
        $statement = $this->pdo->prepare($sql);

        // Execute the query using the attendance record ID.
        $statement->execute([
            'attendance_id' => $attendanceId
        ]);

        // Retrieve the existing database record.
        $record = $statement->fetch(PDO::FETCH_ASSOC);

        // Stop if the attendance record does not exist.
        if ($record === false) {
            throw new RuntimeException(
                'Attendance record not found.'
            );
        }

        // Preserve the original database values.
        return [
            'old_clock_in' => $record['old_clock_in'],
            'old_clock_out' => $record['old_clock_out']
        ];
    }

    /**
     * ========================================================
     * 2. UPDATE ATTENDANCE RECORD
     * ========================================================
     *
     * Fetches the existing attendance values and then
     * updates the attendance table with the new times.
     *
     * PARAMETERS:
     *
     * $attendanceId:
     * Identifies the attendance record to update.
     *
     * $newClockIn:
     * The new clock-in time.
     *
     * $newClockOut:
     * The new clock-out time.
     *
     * TRANSACTION:
     *
     * 1. Begin a database transaction.
     * 2. Fetch the existing attendance record.
     * 3. Preserve old_clock_in and old_clock_out.
     * 4. Update clock_in and clock_out.
     * 5. Commit the transaction.
     *
     * If an operation fails, changes made within this
     * method's transaction are rolled back.
     *
     * RETURNS:
     * An array containing the old and new attendance times.
     */
    public function updateAttendanceTimes(
        int $attendanceId,
        ?string $newClockIn,
        ?string $newClockOut
    ): array {

        // Validate the attendance record ID.
        if ($attendanceId <= 0) {
            throw new InvalidArgumentException(
                'A valid attendance ID is required.'
            );
        }

        // Track whether this method owns the transaction.
        // This avoids committing or rolling back a transaction
        // that may have been started by the API or another caller.
        $ownsTransaction = false;

        try {

            // Start a transaction if one is not already active.
            if (!$this->pdo->inTransaction()) {

                $this->pdo->beginTransaction();

                $ownsTransaction = true;
            }

            /**
             * TASK 1:
             * Fetch the existing attendance record.
             *
             * The selected row is locked until the transaction
             * is completed.
             */
            $oldValues = $this->getOldAttendanceTimes(
                $attendanceId
            );

            /**
             * Preserve the original attendance times.
             *
             * These values are saved before the UPDATE query.
             */
            $oldClockIn = $oldValues['old_clock_in'];

            $oldClockOut = $oldValues['old_clock_out'];

            /**
             * TASK 2:
             * Update the attendance table.
             *
             * Prepared statements ensure that the supplied
             * attendance values are safely passed to MySQL.
             */
            $sql = "
                UPDATE attendance
                SET
                    clock_in = :clock_in,
                    clock_out = :clock_out
                WHERE attendance_id = :attendance_id
            ";

            // Prepare the update statement.
            $statement = $this->pdo->prepare($sql);

            // Supply the new attendance values and record ID.
            $statement->execute([
                'clock_in' => $newClockIn,
                'clock_out' => $newClockOut,
                'attendance_id' => $attendanceId
            ]);

            /**
             * Commit the transaction if this method started it.
             *
             * If the caller already owns the transaction,
             * the caller remains responsible for committing it.
             */
            if ($ownsTransaction) {
                $this->pdo->commit();
            }

            /**
             * Return the original and updated values.
             *
             * The API can use this result to access:
             *
             * old_clock_in
             * old_clock_out
             *
             * new_clock_in
             * new_clock_out
             */
            return [
                'attendance_id' => $attendanceId,

                // Original database values.
                'old_clock_in' => $oldClockIn,
                'old_clock_out' => $oldClockOut,

                // Newly supplied values.
                'new_clock_in' => $newClockIn,
                'new_clock_out' => $newClockOut
            ];

        } catch (Throwable $error) {

            /**
             * Roll back if this method owns the transaction.
             *
             * This prevents a failed update from leaving
             * partially completed database changes.
             */
            if (
                $ownsTransaction &&
                $this->pdo->inTransaction()
            ) {
                $this->pdo->rollBack();
            }

            // Allow the calling API to handle the exception.
            throw $error;
        }
    }
}
