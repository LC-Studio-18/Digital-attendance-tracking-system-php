<?php

declare(strict_types=1);

ini_set('display_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/../services/QrClockInService.php';

$assert = static function (bool $condition, string $message): void {
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
    echo "PASS: {$message}\n";
};

$startUtc = 1764000000;
$tokens = [];

$saveToken = static function (array $record) use (&$tokens): void {
    $tokens[$record['token']] = $record;
};

$findToken = static function (string $token) use (&$tokens): ?array {
    return $tokens[$token] ?? null;
};

$issued = issueQrToken(4, 12, 300, 'https://attendance.example.com', $saveToken, $startUtc);
$validToken = $issued['token'];

// 1. Unauthenticated user -> 401
$res401 = processQrClockIn(
    $validToken,
    static fn(): ?int => null,
    $findToken,
    static fn(int $uid): bool => false,
    static fn(array $row): array => $row + ['id' => 101],
    $startUtc + 30
);
$assert($res401['status'] === 401 && $res401['body']['code'] === 'UNAUTHORIZED', 'Unauthenticated request returns 401');

// 2. Invalid token -> 400 INVALID_QR
$resInvalid = processQrClockIn(
    'bad-token',
    static fn(): ?int => 15,
    $findToken,
    static fn(int $uid): bool => false,
    static fn(array $row): array => $row + ['id' => 102],
    $startUtc + 30
);
$assert($resInvalid['status'] === 400 && $resInvalid['body']['code'] === 'INVALID_QR', 'Invalid token returns 400 INVALID_QR');

// 3. Expired token -> 400 EXPIRED_QR
$resExpired = processQrClockIn(
    $validToken,
    static fn(): ?int => 15,
    $findToken,
    static fn(int $uid): bool => false,
    static fn(array $row): array => $row + ['id' => 103],
    $startUtc + 305
);
$assert($resExpired['status'] === 400 && $resExpired['body']['code'] === 'EXPIRED_QR', 'Expired token returns 400 EXPIRED_QR');

// 4. Duplicate active session -> 409 ALREADY_CLOCKED_IN
$res409 = processQrClockIn(
    $validToken,
    static fn(): ?int => 15,
    $findToken,
    static fn(int $uid): bool => true,
    static fn(array $row): array => $row + ['id' => 104],
    $startUtc + 30
);
$assert($res409['status'] === 409 && $res409['body']['code'] === 'ALREADY_CLOCKED_IN', 'Duplicate session returns 409 ALREADY_CLOCKED_IN');

// 5. Valid clock-in -> 201 with method = QR and UTC timestamp
$res201 = processQrClockIn(
    $validToken,
    static fn(): ?int => 15,
    $findToken,
    static fn(int $uid): bool => false,
    static fn(array $row): array => $row + ['id' => 105],
    $startUtc + 45
);
$assert(
    $res201['status'] === 201
    && $res201['body']['data']['method'] === 'QR'
    && $res201['body']['data']['user_id'] === 15
    && $res201['body']['data']['site_id'] === 4
    && $res201['body']['data']['qr_code_id'] === 12
    && $res201['body']['data']['clocked_in_at_utc'] === gmdate('Y-m-d H:i:s', $startUtc + 45),
    'Valid QR clock-in returns 201 with QR metadata and UTC timestamp'
);

echo "\nAll QR clock-in service tests passed.\n";
