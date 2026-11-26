<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/AttendanceModel.php';

session_start();

function respond(int $status, array $body): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($body);
    exit;
}

// Clock-in status only accepts GET requests.
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    respond(405, [
        'success' => false,
        'error' => [
            'code' => 'METHOD_NOT_ALLOWED',
            'message' => 'Only GET requests are allowed.'
        ]
    ]);
}

// TEMP STAND-IN (S2-05 / S2-06 not finished): replace with shared logged-in check.
$staffId = (int) ($_SESSION['staff_id'] ?? 0);

// TEMP STAND-IN (S2-13B not finished): replace with standard error format.
if ($staffId <= 0) {
    respond(401, [
        'success' => false,
        'error' => [
            'code' => 'UNAUTHENTICATED',
            'message' => 'You must be logged in to check your attendance status.'
        ]
    ]);
}

// Retrieve the employee's active attendance record.
$model = new AttendanceModel(getDatabaseConnection());
$activeSession = $model->getActiveSession($staffId);

// Return the employee's current status.
respond(200, [
    'success' => true,
    'data' => [
        'status' => $activeSession !== null ? 'IN' : 'OUT',
        'attendance' => $activeSession
    ]
]);
