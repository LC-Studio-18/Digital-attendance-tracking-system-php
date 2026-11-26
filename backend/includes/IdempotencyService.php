<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

final class IdempotencyService
{
    public function __construct(private PDO $pdo) {}

    public static function fromDefaultConnection(): self
    {
        return new self(getDatabaseConnection());
    }

    /**
     * Checks whether a request_token has already been used.
     *
     * @return array{
     *   status: 'new'|'replay'|'mismatch'|'invalid',
     *   record: ?array<string, mixed>,
     *   code: ?string,
     *   message: ?string
     * }
     */
    public function inspectToken(int $staffId, ?string $requestToken): array
    {
        $token = is_string($requestToken) ? trim($requestToken) : '';

        if ($token === '' || strlen($token) > 64) {
            return [
                'status'  => 'invalid',
                'record'  => null,
                'code'    => 'INVALID_REQUEST_TOKEN',
                'message' => 'A valid request token is required.',
            ];
        }

        $stmt = $this->pdo->prepare(
            'SELECT record_id, staff_id, request_token, clock_in, clock_out, created_at, updated_at
             FROM attendance
             WHERE request_token = :request_token
             LIMIT 1'
        );
        $stmt->execute([':request_token' => $token]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row === false) {
            return [
                'status'  => 'new',
                'record'  => null,
                'code'    => null,
                'message' => null,
            ];
        }

        if ((int) $row['staff_id'] !== $staffId) {
            return [
                'status'  => 'mismatch',
                'record'  => null,
                'code'    => 'TOKEN_USER_MISMATCH',
                'message' => 'This request token cannot be used for your account.',
            ];
        }

        $row['record_id'] = (int) $row['record_id'];
        $row['staff_id']  = (int) $row['staff_id'];

        return [
            'status'  => 'replay',
            'record'  => $row,
            'code'    => 'DUPLICATE_REQUEST',
            'message' => 'This request was already processed.',
        ];
    }

    /**
     * Inserts a new attendance record with request_token safely.
     * Handles concurrent duplicate tokens (MySQL error 1062 / SQLSTATE 23000) by returning
     * the existing record for the same staff_id instead of inserting a second row.
     *
     * @return array{
     *   ok: bool,
     *   replayed: bool,
     *   record: ?array<string, mixed>,
     *   code: ?string,
     *   message: ?string
     * }
     */
    public function createAttendanceWithToken(
        int $staffId,
        string $requestToken,
        string $clockIn,
        ?string $clockOut = null
    ): array {
        $check = $this->inspectToken($staffId, $requestToken);

        if ($check['status'] === 'replay') {
            return [
                'ok'       => true,
                'replayed' => true,
                'record'   => $check['record'],
                'code'     => 'CLOCK_ALREADY_RECORDED',
                'message'  => 'Attendance already recorded for this request.',
            ];
        }

        if ($check['status'] === 'mismatch' || $check['status'] === 'invalid') {
            return [
                'ok'       => false,
                'replayed' => false,
                'record'   => null,
                'code'     => $check['code'],
                'message'  => $check['message'],
            ];
        }

        $token = trim($requestToken);

        try {
            $insert = $this->pdo->prepare(
                'INSERT INTO attendance (staff_id, request_token, clock_in, clock_out)
                 VALUES (:staff_id, :request_token, :clock_in, :clock_out)'
            );
            $insert->execute([
                ':staff_id'      => $staffId,
                ':request_token' => $token,
                ':clock_in'      => $clockIn,
                ':clock_out'     => $clockOut,
            ]);

            $id = (int) $this->pdo->lastInsertId();

            return [
                'ok'       => true,
                'replayed' => false,
                'record'   => $this->fetchById($id),
                'code'     => 'ATTENDANCE_RECORDED',
                'message'  => 'Attendance recorded successfully.',
            ];
        } catch (PDOException $e) {
            $sqlState = (string) $e->getCode();
            $driverCode = $e->errorInfo[1] ?? null;

            if ($sqlState === '23000' || (int) $driverCode === 1062) {
                $afterRace = $this->inspectToken($staffId, $token);
                if ($afterRace['status'] === 'replay') {
                    return [
                        'ok'       => true,
                        'replayed' => true,
                        'record'   => $afterRace['record'],
                        'code'     => 'CLOCK_ALREADY_RECORDED',
                        'message'  => 'Attendance already recorded for this request.',
                    ];
                }
                if ($afterRace['status'] === 'mismatch') {
                    return [
                        'ok'       => false,
                        'replayed' => false,
                        'record'   => null,
                        'code'     => $afterRace['code'],
                        'message'  => $afterRace['message'],
                    ];
                }
            }

            return [
                'ok'       => false,
                'replayed' => false,
                'record'   => null,
                'code'     => 'REQUEST_FAILED',
                'message'  => 'Unable to process your request right now. Please try again.',
            ];
        }
    }

    /**
     * @return ?array<string, mixed>
     */
    private function fetchById(int $recordId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT record_id, staff_id, request_token, clock_in, clock_out, created_at, updated_at
             FROM attendance
             WHERE record_id = :record_id
             LIMIT 1'
        );
        $stmt->execute([':record_id' => $recordId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row === false) {
            return null;
        }

        $row['record_id'] = (int) $row['record_id'];
        $row['staff_id']  = (int) $row['staff_id'];

        return $row;
    }
}