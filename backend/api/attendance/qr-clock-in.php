<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../services/QrClockInService.php';

header('Content-Type: application/json; charset=utf-8');

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'ok' => false,
        'code' => 'METHOD_NOT_ALLOWED',
        'message' => 'Only POST requests are allowed.',
    ]);
    exit;
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$rawInput = file_get_contents('php://input');
$decoded = is_string($rawInput) && $rawInput !== '' ? json_decode($rawInput, true) : [];
$qrToken = '';

if (is_array($decoded) && isset($decoded['qrToken']) && is_string($decoded['qrToken'])) {
    $qrToken = $decoded['qrToken'];
} elseif (isset($_POST['qrToken']) && is_string($_POST['qrToken'])) {
    $qrToken = $_POST['qrToken'];
}

$getCurrentUserId = static function (): ?int {
    foreach (['user_id', 'staff_id', 'id'] as $key) {
        if (isset($_SESSION[$key]) && is_numeric($_SESSION[$key])) {
            $id = (int) $_SESSION[$key];
            if ($id > 0) {
                return $id;
            }
        }
    }
    return null;
};

$pdo = getDatabaseConnection();

$findToken = static function (string $token) use ($pdo): ?array {
    $stmt = $pdo->prepare(
        'SELECT token, site_id, qr_code_id, is_active, expires_at_utc
         FROM qr_tokens
         WHERE token = :token
         LIMIT 1'
    );
    $stmt->execute([':token' => $token]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row !== false ? $row : null;
};

$hasActiveSession = static function (int $userId) use ($pdo): bool {
    $stmt = $pdo->prepare(
        'SELECT record_id
         FROM attendance
         WHERE staff_id = :staff_id AND clock_out IS NULL
         LIMIT 1'
    );
    $stmt->execute([':staff_id' => $userId]);
    return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
};

$createAttendance = static function (array $payload) use ($pdo): array {
    $startedTx = !$pdo->inTransaction();
    if ($startedTx) {
        $pdo->beginTransaction();
    }

    try {
        $lockStmt = $pdo->prepare(
            'SELECT record_id
             FROM attendance
             WHERE staff_id = :staff_id AND clock_out IS NULL
             LIMIT 1
             FOR UPDATE'
        );
        $lockStmt->execute([':staff_id' => $payload['user_id']]);
        if ($lockStmt->fetch(PDO::FETCH_ASSOC) !== false) {
            if ($startedTx && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            return ['_duplicate' => true];
        }

        $stmt = $pdo->prepare(
            'INSERT INTO attendance (staff_id, site_id, qr_code_id, method, clock_in, clock_out)
             VALUES (:staff_id, :site_id, :qr_code_id, :method, :clock_in, NULL)'
        );
        $stmt->execute([
            ':staff_id' => $payload['user_id'],
            ':site_id' => $payload['site_id'],
            ':qr_code_id' => $payload['qr_code_id'],
            ':method' => $payload['method'],
            ':clock_in' => $payload['clocked_in_at_utc'],
        ]);

        $recordId = (int) $pdo->lastInsertId();

        if ($startedTx && $pdo->inTransaction()) {
            $pdo->commit();
        }

        return ['record_id' => $recordId];
    } catch (Throwable $e) {
        if ($startedTx && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
};

$result = processQrClockIn(
    $qrToken,
    $getCurrentUserId,
    $findToken,
    $hasActiveSession,
    $createAttendance
);

if (
    $result['status'] === 201
    && isset($result['body']['data']['_duplicate'])
    && $result['body']['data']['_duplicate'] === true
) {
    $result = [
        'status' => 409,
        'body' => [
            'ok' => false,
            'code' => 'ALREADY_CLOCKED_IN',
            'message' => 'User already has an active attendance session.',
        ],
    ];
}

http_response_code($result['status']);
echo json_encode($result['body']);
