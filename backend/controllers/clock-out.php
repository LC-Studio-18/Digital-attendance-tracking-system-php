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

// TEMP STAND-IN (S2-05 / S2-06 not finished): replace with the shared "logged in" check.
$staffId = (int) ($_SESSION['staff_id'] ?? 0);

// TEMP STAND-IN (S2-13B not finished): replace with the standard error format.
if ($staffId <= 0) {
    respond(401, ['success' => false, 'error' => ['code' => 'UNAUTHENTICATED', 'message' => 'You must be logged in to clock out.']]);
}

// The server decides the clock-out time, never the client.
$now = new DateTimeImmutable('now', new DateTimeZone('Africa/Johannesburg'));
$record = (new AttendanceModel(getDatabaseConnection()))->clockOut($staffId, $now);

// No open session: never clocked in, or already clocked out (duplicate request).
if ($record === null) {
    respond(409, ['success' => false, 'error' => ['code' => 'NO_ACTIVE_SESSION', 'message' => 'You have no active clock-in session to clock out of.']]);
}

respond(200, ['success' => true, 'data' => $record]);
