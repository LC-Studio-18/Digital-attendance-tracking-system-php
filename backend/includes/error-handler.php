<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';

/**
 * Sends a standard JSON API response and ends the current request.
 *
 * All API responses use status, message and code. Optional response payloads
 * are added under data so the agreed fields always remain present.
 *
 * @param array<string, mixed> $data
 */
if (!function_exists('sendJsonResponse')) {
    function sendJsonResponse(
        int $httpStatus,
        string $status,
        string $message,
        string $code,
        array $data = []
    ): void {
        $payload = [
            'status'  => $status,
            'message' => $message,
            'code'    => $code,
        ];

        if ($data !== []) {
            $payload['data'] = $data;
        }

        http_response_code($httpStatus);
        header('Content-Type: application/json; charset=utf-8');

        $json = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        if ($json === false) {
            // Never let an encoding failure expose raw data or technical details.
            http_response_code(500);
            $json = json_encode([
                'status'  => 'error',
                'message' => 'An unexpected error occurred. Please try again later.',
                'code'    => 'INTERNAL_SERVER_ERROR',
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }

        echo $json . PHP_EOL;
        exit;
    }
}

if (!function_exists('sendJsonSuccess')) {
    /**
     * @param array<string, mixed> $data
     */
    function sendJsonSuccess(
        int $httpStatus,
        string $message,
        string $code,
        array $data = []
    ): void {
        sendJsonResponse($httpStatus, 'success', $message, $code, $data);
    }
}

if (!function_exists('sendJsonError')) {
    function sendJsonError(int $httpStatus, string $message, string $code): void
    {
        sendJsonResponse($httpStatus, 'error', $message, $code);
    }
}

if (!function_exists('sendIdempotencyResponse')) {
    /**
     * Converts an IdempotencyService result into the agreed JSON response.
     *
     * @param array<string, mixed> $result
     */
    function sendIdempotencyResponse(array $result, int $createdHttpStatus = 201): void
    {
        $ok = ($result['ok'] ?? false) === true;
        $replayed = ($result['replayed'] ?? false) === true;

        $knownCodes = [
            'ATTENDANCE_RECORDED',
            'CLOCK_ALREADY_RECORDED',
            'INVALID_REQUEST_TOKEN',
            'TOKEN_USER_MISMATCH',
            'REQUEST_FAILED',
        ];

        $code = isset($result['code']) && is_string($result['code']) && in_array($result['code'], $knownCodes, true)
            ? $result['code']
            : ($ok ? 'REQUEST_SUCCESS' : 'REQUEST_FAILED');

        $message = isset($result['message']) && is_string($result['message']) && $result['message'] !== ''
            ? $result['message']
            : ($ok ? 'Request processed successfully.' : 'Unable to process your request right now. Please try again.');

        // An unknown code must never pass through an unverified service message.
        if (!in_array($code, $knownCodes, true)) {
            $message = $ok
                ? 'Request processed successfully.'
                : 'Unable to process your request right now. Please try again.';
        }

        if ($ok) {
            $data = [];
            if (isset($result['record']) && is_array($result['record'])) {
                $data['record'] = $result['record'];
            }

            sendJsonSuccess($replayed ? 200 : $createdHttpStatus, $message, $code, $data);
        }

        $errorStatuses = [
            'INVALID_REQUEST_TOKEN' => 400,
            'TOKEN_USER_MISMATCH'   => 403,
            'REQUEST_FAILED'        => 500,
        ];

        sendJsonError($errorStatuses[$code] ?? 500, $message, $code);
    }
}

set_exception_handler(function (Throwable $exception): void {
    $logMessage = 'Uncaught exception: ' . $exception->getMessage()
        . ' in ' . $exception->getFile() . ':' . $exception->getLine();

    if (APP_DEBUG) {
        $logMessage .= PHP_EOL . $exception->getTraceAsString();
    }

    error_log($logMessage);

    // Debug mode affects internal logging only; API responses stay generic.
    sendJsonError(500, 'An unexpected error occurred. Please try again later.', 'INTERNAL_SERVER_ERROR');
});
