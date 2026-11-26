<?php

// http://localhost/Digital-attendance-tracking-system-php/backend/api/staff-onsite.php 
// Test URL above

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

    // Return JSON response
    echo json_encode([
        'success' => true,
        'total_count' => count($records),
        'data' => $records
    ], JSON_PRETTY_PRINT);

} catch (Throwable $exception) {
    // Handles the problem if something goes wrong
    error_log($exception->getMessage());

    http_response_code(500); // returns respone code 500 which means inetrnal server error occured

    echo json_encode([   // Converts PHP array into JSON
        'success' => false,
        'message' => 'Unable to retrieve staff attendance information.'
    ]);
}
