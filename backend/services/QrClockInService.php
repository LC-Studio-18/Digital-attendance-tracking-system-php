<?php

declare(strict_types=1);

require_once __DIR__ . '/QrTokenService.php';

/**
 * Orchestrates QR clock-in flow using injectable session, token lookup,
 * duplicate-session, and persistence callbacks.
 *
 * Expected callback signatures:
 * - $getCurrentUserId(): ?int
 * - $findToken(string $token): ?array
 * - $hasActiveSession(int $userId): bool
 * - $createAttendance(array $payload): array
 *
 * @return array{status: int, body: array<string, mixed>}
 */
function processQrClockIn(
    string $qrToken,
    callable $getCurrentUserId,
    callable $findToken,
    callable $hasActiveSession,
    callable $createAttendance,
    ?int $nowUtc = null
): array {
    $currentUtc = $nowUtc ?? time();

    // 1. Auth check (401 if missing/unauthenticated)
    $userId = $getCurrentUserId();
    if (!is_int($userId) || $userId <= 0) {
        return [
            'status' => 401,
            'body' => [
                'ok' => false,
                'code' => 'UNAUTHORIZED',
                'message' => 'Authentication is required to clock in.',
            ],
        ];
    }

    // 2. QR token check (400 with INVALID_QR or EXPIRED_QR)
    $tokenCheck = validateQrToken($qrToken, $findToken, $currentUtc);
    if (empty($tokenCheck['valid'])) {
        $code = (string) ($tokenCheck['code'] ?? 'INVALID_QR');
        $message = (string) ($tokenCheck['message'] ?? 'This QR code is invalid.');

        return [
            'status' => 400,
            'body' => [
                'ok' => false,
                'code' => $code,
                'message' => $message,
            ],
        ];
    }

    // 3. Duplicate active-session check (409 if already clocked in)
    if ($hasActiveSession($userId)) {
        return [
            'status' => 409,
            'body' => [
                'ok' => false,
                'code' => 'ALREADY_CLOCKED_IN',
                'message' => 'You already have an active clock-in session.',
            ],
        ];
    }

    // 4. Persist attendance record with server UTC timestamp and method = 'QR'
    $payload = [
        'user_id' => $userId,
        'site_id' => (int) $tokenCheck['site_id'],
        'qr_code_id' => (int) $tokenCheck['qr_code_id'],
        'method' => 'QR',
        'clocked_in_at_utc' => gmdate('Y-m-d H:i:s', $currentUtc),
    ];

    $savedRecord = $createAttendance($payload);

    return [
        'status' => 201,
        'body' => [
            'ok' => true,
            'code' => 'CLOCKED_IN',
            'data' => array_merge($payload, $savedRecord),
        ],
    ];
}