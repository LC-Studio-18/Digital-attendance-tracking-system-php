<?php

declare(strict_types=1);

require_once __DIR__ . '/../backend/config/database.php';
require_once __DIR__ . '/../backend/models/AttendanceModel.php';

// Connect to the local database.
$pdo = getDatabaseConnection();

$model = new AttendanceModel($pdo);

// Use staff ID 2 from your local seed data.
$staffId = 2;

// Generate the current South African timestamp.
$now = new DateTimeImmutable(
    'now',
    new DateTimeZone('Africa/Johannesburg')
);

// Attempt to clock in.
$result = $model->clockIn($staffId, $now);

if ($result === null) {
    echo "Employee already has an active clock-in.\n";
} else {
    echo "Clock-in successful!\n";
    print_r($result);
}
