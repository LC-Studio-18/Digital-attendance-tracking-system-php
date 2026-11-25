<?php

declare(strict_types=1);

/**
 * Generates an active QR token, saves its record via a callback,
 * and returns the token details plus the redirect URL.
 *
 * @param callable(array): void $saveToken
 * @return array<string, mixed>
 */
function issueQrToken(
    int $siteId,
    int $qrCodeId,
    int $ttlSeconds,
    string $appUrl,
    callable $saveToken,
    ?int $nowUtc = null
): array {
    $currentUtc = $nowUtc ?? time();
    $expiresAtUtcInt = $currentUtc + max(1, $ttlSeconds);
    $expiresAtUtc = gmdate('Y-m-d H:i:s', $expiresAtUtcInt);
    $token = bin2hex(random_bytes(32));

    $record = [
        'token' => $token,
        'site_id' => $siteId,
        'qr_code_id' => $qrCodeId,
        'is_active' => 1,
        'expires_at_utc' => $expiresAtUtc,
    ];

    $saveToken($record);

    $baseUrl = rtrim($appUrl, '/');
    $clockInUrl = $baseUrl . '/clock-in?qrToken=' . urlencode($token);

    return [
        'token' => $token,
        'site_id' => $siteId,
        'qr_code_id' => $qrCodeId,
        'is_active' => 1,
        'expires_at_utc' => $expiresAtUtc,
        'clock_in_url' => $clockInUrl,
    ];
}

/**
 * Validates a QR token against active status and UTC expiration.
 *
 * @param callable(string): ?array $findToken
 * @return array<string, mixed>
 */
function validateQrToken(
    string $qrToken,
    callable $findToken,
    ?int $nowUtc = null
): array {
    $trimmed = trim($qrToken);

    // Expect a 64-character lowercase hex string from bin2hex(random_bytes(32))
    if ($trimmed === '' || !preg_match('/^[a-f0-9]{64}$/', $trimmed)) {
        return [
            'valid' => false,
            'code' => 'INVALID_QR',
            'message' => 'This QR code is invalid.',
        ];
    }

    $record = $findToken($trimmed);
    if (!is_array($record)) {
        return [
            'valid' => false,
            'code' => 'INVALID_QR',
            'message' => 'This QR code is invalid.',
        ];
    }

    $isActive = !empty($record['is_active']);
    if (!$isActive) {
        return [
            'valid' => false,
            'code' => 'INVALID_QR',
            'message' => 'This QR code is invalid.',
        ];
    }

    $expiresAtRaw = (string) ($record['expires_at_utc'] ?? '');
    $expiresAtInt = strtotime($expiresAtRaw . ' UTC');
    if ($expiresAtRaw === '' || $expiresAtInt === false) {
        return [
            'valid' => false,
            'code' => 'INVALID_QR',
            'message' => 'This QR code is invalid.',
        ];
    }

    $currentUtc = $nowUtc ?? time();
    if ($currentUtc >= $expiresAtInt) {
        return [
            'valid' => false,
            'code' => 'EXPIRED_QR',
            'message' => 'This QR code has expired.',
        ];
    }

    return [
        'valid' => true,
        'site_id' => (int) ($record['site_id'] ?? 0),
        'qr_code_id' => (int) ($record['qr_code_id'] ?? 0),
        'expires_at_utc' => $expiresAtRaw,
    ];
}