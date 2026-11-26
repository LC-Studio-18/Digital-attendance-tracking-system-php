<?php

// This tells PHP to strictly enforce data types (e.g., strings must be strings, ints must be ints).
// It helps catch bugs early.
declare(strict_types=1);

/**
 * This function saves a record of an Admin changing an employee's time.
 * It saves both the OLD times and the NEW times for security and auditing.
 */
function insertAuditLog(
    PDO $pdo,               // The database connection object
    int $attendanceId,      // The ID of the attendance record being edited
    int $adminId,           // The ID of the Admin making the change
    ?string $oldIn,         // The old clock-in time (can be null if it was empty)
    ?string $oldOut,        // The old clock-out time (can be null if it was empty)
    string $newIn,          // The new clock-in time being saved
    string $newOut,         // The new clock-out time being saved
    string $reason = 'Admin manual edit' // The reason for the change (defaults to a standard message)
): bool {                   // This function will return "true" if it works, or "false" if it fails

    // 1. Write the SQL instruction to insert the data.
    // We use placeholders (like :attendance_id) instead of putting variables directly into the string.
    // This is called a "Prepared Statement" and it prevents hackers from doing SQL Injection attacks.
    // NOW() is a built-in SQL command that gets the exact current date and time.
    $sql = "INSERT INTO audit_logs (
                attendance_id, admin_id, old_clock_in, old_clock_out, 
                new_clock_in, new_clock_out, modified_at, reason_modified
            ) VALUES (
                :attendance_id, :admin_id, :old_clock_in, :old_clock_out, 
                :new_clock_in, :new_clock_out, NOW(), :reason
            )";
            
    try {
        // 2. Prepare the SQL statement using the database connection.
        // This tells the database to get ready to receive the data.
        $stmt = $pdo->prepare($sql);
        
        // 3. Execute (run) the statement.
        // We pass in an array that matches our placeholders to the actual PHP variables.
        // This safely replaces the placeholders with the real data.
        return $stmt->execute([
            ':attendance_id' => $attendanceId,
            ':admin_id'      => $adminId,
            ':old_clock_in'  => $oldIn,
            ':old_clock_out' => $oldOut,
            ':new_clock_in'  => $newIn,
            ':new_clock_out' => $newOut,
            ':reason'        => $reason
        ]);
        
    } catch (PDOException $e) {
        // 4. If the database throws an error (e.g., the table doesn't exist or the connection drops),
        // we catch the error here so the whole app doesn't crash.
        
        // We log the exact error message to the server's error log for the developers to read later.
        error_log("Audit log insert failed: " . $e->getMessage());
        
        // We return "false" so the API knows the insert failed and can handle it gracefully.
        return false;
    }
}