<?php

declare(strict_types=1);

function getDatabaseConnection(): PDO
{
    $credentials = require __DIR__ . '/../../config/credentials.php';

    $dsn = sprintf(
        'mysql:host=%s;dbname=%s;charset=utf8mb4',
        $credentials['host'],
        $credentials['database']
    );

    try {
        return new PDO(
            $dsn,
            $credentials['username'],
            $credentials['password'],
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
    } catch (PDOException $exception) {
        error_log('Database connection failed: ' . $exception->getMessage());

        http_response_code(500);
        exit('A database error occurred.');
    }
}