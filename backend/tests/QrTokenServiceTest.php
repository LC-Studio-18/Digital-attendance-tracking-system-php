<?php

declare(strict_types=1);

ini_set('display_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/../services/QrTokenService.php';

$store = [];

$saveToken = static function (array $record) use (&$store): void {
    $store[$record['token']] = $record;
};

$findToken = static function (string $token) use (&$store): ?array {
    return $store[$token] ?? null;
};

$assert = static function (bool $condition, string $message): void {
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
    echo "PASS: {$message}\n";
};

$startUtc = 1764000000;
$issued = issueQrToken(2, 9, 300, 'https://attendance.example.com/', $saveToken, $startUtc);

$assert(strlen($issued['token']) === 64, 'Token is a 64-character hex string');
$assert(
    $issued['clock_in_url'] === 'https://attendance.example.com/clock-in?qrToken=' . $issued['token'],
    'Redirect URL matches <app-url>/clock-in?qrToken=...'
);

$validResult = validateQrToken($issued['token'], $findToken, $startUtc + 60);
$assert(
    $validResult['valid'] === true && $validResult['site_id'] === 2 && $validResult['qr_code_id'] === 9,
    'Active unexpired token validates with site_id and qr_code_id'
);

$expiredResult = validateQrToken($issued['token'], $findToken, $startUtc + 301);
$assert(
    $expiredResult['valid'] === false && $expiredResult['code'] === 'EXPIRED_QR',
    'Expired token returns EXPIRED_QR'
);

$store[$issued['token']]['is_active'] = 0;
$inactiveResult = validateQrToken($issued['token'], $findToken, $startUtc + 30);
$assert(
    $inactiveResult['valid'] === false && $inactiveResult['code'] === 'INVALID_QR',
    'Inactive token returns INVALID_QR'
);

$unknownResult = validateQrToken('not-a-valid-token', $findToken, $startUtc);
$assert(
    $unknownResult['valid'] === false && $unknownResult['code'] === 'INVALID_QR',
    'Malformed or unknown token returns INVALID_QR'
);

echo "\nAll QR token service tests passed.\n";
