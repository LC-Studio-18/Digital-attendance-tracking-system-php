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

// Clock-in only accepts POST requests.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(405, [
        'success' => false,
        'error' => [
            'code' => 'METHOD_NOT_ALLOWED',
            'message' => 'Only POST requests are allowed.'
        ]
    ]);
}


// TEMP STAND-IN (S2-05 / S2-06 not finished): replace with the shared "logged in" check.
$staffId = (int) ($_SESSION['staff_id'] ?? 0);

// TEMP STAND-IN (S2-13B not finished): replace with the standard error format.
if ($staffId <= 0) {
    respond(401, [
        'success' => false,
        'error' => [
            'code' => 'UNAUTHENTICATED',
            'message' => 'You must be logged in to clock in.'
        ]
    ]);
}

// The server decides the clock-in time, never the client.
$now = new DateTimeImmutable('now', new DateTimeZone('Africa/Johannesburg'));

$record = (new AttendanceModel(getDatabaseConnection()))->clockIn($staffId, $now);

// An active session already exists.
if ($record === null) {
    respond(409, [
        'success' => false,
        'error' => [
            'code' => 'ALREADY_CLOCKED_IN',
            'message' => 'You already have an active clock-in session.'
        ]
    ]);
}

respond(201, ['success' => true, 'data' => $record]);

