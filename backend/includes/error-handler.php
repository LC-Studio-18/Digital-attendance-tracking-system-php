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
        // Defence in depth: technical details must never reach an API response.
        // Approved user-facing messages do not contain these markers; anything
        // that does is logged internally and replaced with a generic message.
        $technicalPattern = '/(SQLSTATE|PDOException|Exception:|stack\s*trace|password\s*=|passwd\s*=)/i';

        if (preg_match($technicalPattern, $message) === 1) {
            error_log(
                'S2-13B: technical detail blocked from API response: '
                . str_replace(["\r", "\n", "\0"], ' ', $message)
            );
            $message = $status === 'success'
                ? 'Request processed successfully.'
                : 'Unable to process your request right now. Please try again.';
        }

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

        $providedCode = isset($result['code']) && is_string($result['code']) ? $result['code'] : null;
        $recognisedCode = $providedCode !== null && in_array($providedCode, $knownCodes, true);

        $code = $recognisedCode
            ? (string) $providedCode
            : ($ok ? 'REQUEST_SUCCESS' : 'REQUEST_FAILED');

        $genericSuccessMessage = 'Request processed successfully.';
        $genericErrorMessage = 'Unable to process your request right now. Please try again.';

        // Only a message tied to a recognised code is trusted as user-facing.
        $message = $recognisedCode
            && isset($result['message'])
            && is_string($result['message'])
            && $result['message'] !== ''
                ? $result['message']
                : ($ok ? $genericSuccessMessage : $genericErrorMessage);

        if (!$recognisedCode) {
            // Keep the rejected detail in the internal log; never return it.
            $loggedCode = $providedCode ?? '(none)';
            $loggedMessage = isset($result['message']) && is_string($result['message'])
                ? $result['message']
                : '(none)';

            error_log(
                'S2-13B: unrecognised idempotency result; generic response used. code='
                . str_replace(["\r", "\n", "\0"], ' ', $loggedCode)
                . ' message=' . str_replace(["\r", "\n", "\0"], ' ', $loggedMessage)
            );
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
