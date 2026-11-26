<?php

declare(strict_types=1);

/**
 * Returns the shared PDO connection (created once per request).
 * Credentials come from config/credentials.php (git-ignored).
 *
 * Usage:
 *   require_once __DIR__ . '/../config/database.php';
 *   $pdo = getDatabaseConnection();
 */
function getDatabaseConnection(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $credentials = require __DIR__ . '/../../config/credentials.php';

    // All timestamps in the system are SAST (Africa/Johannesburg, UTC+2, no DST)
    $timezone = $credentials['timezone'] ?? 'Africa/Johannesburg';
    date_default_timezone_set($timezone);
    $offset = (new DateTime('now', new DateTimeZone($timezone)))->format('P'); // e.g. +02:00

    $dsn = sprintf(
        'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
        $credentials['host'],
        (int)($credentials['port'] ?? 3306),
        $credentials['database']
    );

    try {
        $pdo = new PDO(
            $dsn,
            $credentials['username'],
            $credentials['password'],
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::ATTR_STRINGIFY_FETCHES  => false,
            ]
        );

        // Make MySQL's NOW()/CURDATE() agree with PHP (SAST)
        $pdo->exec('SET time_zone = ' . $pdo->quote($offset));

        return $pdo;
    } catch (PDOException $exception) {
        error_log('Database connection failed: ' . $exception->getMessage());

        http_response_code(500);
        exit('A database error occurred.');
    }
}