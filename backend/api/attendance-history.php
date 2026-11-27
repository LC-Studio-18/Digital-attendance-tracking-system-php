<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

// Load the database, session and attendance history service.
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../services/attendanceHistoryService.php';

// Start the existing application session.
startSession();

// Check whether the user is logged in.
if (
    !isset($_SESSION['staff_id'], $_SESSION['role']) ||
    !is_scalar($_SESSION['staff_id']) ||
    !is_string($_SESSION['role'])
) {
    http_response_code(401);
    echo json_encode(['error' => 'Authentication required.']);
    exit;
}

// Only administrators may view attendance history.
if ($_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied.']);
    exit;
}

// Only allow GET requests.
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('Allow: GET');
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed.']);
    exit;
}

try {
    $pdo = getDatabaseConnection();

    $filters = [
        'date_from' => $_GET['date_from'] ?? '',
        'date_to' => $_GET['date_to'] ?? '',
        'employee' => $_GET['employee'] ?? '',
    ];

    $page = filter_var(
        $_GET['page'] ?? '1',
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    );

    if ($page === false) {
        http_response_code(400);
        echo json_encode(['error' => 'Page must be a positive integer.']);
        exit;
    }

    $result = getAttendanceHistory($pdo, $filters, $page);

    http_response_code(200);
    echo json_encode($result);

} catch (InvalidArgumentException $exception) {
    http_response_code(400);
    echo json_encode(['error' => $exception->getMessage()]);

} catch (Throwable $exception) {
    error_log($exception->getMessage());

    http_response_code(500);
    echo json_encode(['error' => 'An unexpected server error occurred.']);
}
