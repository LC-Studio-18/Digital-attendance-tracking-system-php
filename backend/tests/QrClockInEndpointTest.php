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

$now = 1764000500;
$tokens = [];
issueQrToken(
    2,
    9,
    600,
    'https://attendance.example.com',
    static function (array $row) use (&$tokens): void {
        $tokens[$row['token']] = $row;
    },
    $now
);

$validToken = array_key_first($tokens);
$findToken = static fn(string $t): ?array => $tokens[$t] ?? null;

$res401 = processQrClockIn(
    $validToken,
    static fn(): ?int => null,
    $findToken,
    static fn(int $id): bool => false,
    static fn(array $row): array => $row + ['record_id' => 501],
    $now + 10
);
$assert($res401['status'] === 401 && $res401['body']['code'] === 'UNAUTHORIZED', 'Endpoint flow rejects unauthenticated user with 401');

$res400 = processQrClockIn(
    'invalid',
    static fn(): ?int => 7,
    $findToken,
    static fn(int $id): bool => false,
    static fn(array $row): array => $row + ['record_id' => 502],
    $now + 10
);
$assert($res400['status'] === 400 && $res400['body']['code'] === 'INVALID_QR', 'Endpoint flow rejects invalid token with 400 INVALID_QR');

$res409 = processQrClockIn(
    $validToken,
    static fn(): ?int => 7,
    $findToken,
    static fn(int $id): bool => true,
    static fn(array $row): array => $row + ['record_id' => 503],
    $now + 10
);
$assert($res409['status'] === 409 && $res409['body']['code'] === 'ALREADY_CLOCKED_IN', 'Endpoint flow blocks duplicate active shift with 409');

$res201 = processQrClockIn(
    $validToken,
    static fn(): ?int => 7,
    $findToken,
    static fn(int $id): bool => false,
    static fn(array $row): array => $row + ['record_id' => 504],
    $now + 10
);
$assert(
    $res201['status'] === 201
    && $res201['body']['code'] === 'CLOCKED_IN'
    && $res201['body']['data']['method'] === 'QR'
    && $res201['body']['data']['record_id'] === 504,
    'Endpoint flow records QR clock-in with 201 CLOCKED_IN'
);

echo "\nAll QR clock-in endpoint tests passed.\n";
