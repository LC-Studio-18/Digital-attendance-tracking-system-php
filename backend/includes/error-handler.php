<?php

declare(strict_types=1);

set_exception_handler(function (Throwable $exception): void {
    error_log($exception->getMessage());

    http_response_code(500);

    if (APP_DEBUG) {
        echo 'Error: ' . htmlspecialchars($exception->getMessage(), ENT_QUOTES, 'UTF-8');
        return;
    }

    echo 'An unexpected error occurred. Please try again later.';
});