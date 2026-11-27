<?php

// http://localhost/Digital-attendance-tracking-system-php/backend/api/staff-onsite.php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/database.php';

try {

    // Connect to database
    $pdo = getDatabaseConnection();

    // Retrieve staff with open attendance records
    $sql = "
        SELECT
            s.first_name,
            s.last_name,
            s.employee_number,
            a.clock_in
        FROM staff s
        INNER JOIN attendance a
            ON s.staff_id = a.staff_id
        WHERE a.clock_out IS NULL
          AND s.is_active = 1
        ORDER BY a.clock_in ASC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Set the South African timezone
    $timezone = new DateTimeZone('Africa/Johannesburg');

    // Get the current date and time
    $now = new DateTimeImmutable('now', $timezone);

    // Create an array to store processed staff information
    $staff = [];

    foreach ($records as $record) {

        // Convert the database clock-in time into a DateTime object
        $clockin = new DateTimeImmutable(
            $record['clock_in'],
            $timezone
        );

        $previousDay = $clockin->format('Y-m-d') !== $now->format('Y-m-d');

        // Calculate the number of seconds the employee has been onsite
        $seconds = max(
            0,
            $now->getTimestamp() - $clockin->getTimestamp()
        );

        // Convert seconds into hours, minutes and seconds
        $hours = intdiv($seconds, 3600);

        $minutes = intdiv($seconds % 3600, 60);

        $remainingseconds = $seconds % 60;

        // Format the time as HH:MM:SS
        $timeOnsite = sprintf(
            '%02d:%02d:%02d',
            $hours,
            $minutes,
            $remainingseconds
        );

        // Add employee information to the response array
        $staff[] = [
            'name' => $record['first_name'] . ' ' . $record['last_name'],
            'employee_id' => $record['employee_number'],
            'clock_in' => $record['clock_in'],
            'time_onsite' => $timeOnsite,
            'previous_day' => $previousDay
        ];
    }

    // Return the JSON response
    echo json_encode([
        'success' => true,
        'total_count' => count($staff),
        'data' => $staff
    ], JSON_PRETTY_PRINT);

} catch (Throwable $exception) {

    // Log the technical error privately
    error_log($exception->getMessage());

    http_response_code(500);

    // Return a general error message
    echo json_encode([
        'success' => false,
        'message' => 'Unable to retrieve staff attendance information.'
    ]);
}
