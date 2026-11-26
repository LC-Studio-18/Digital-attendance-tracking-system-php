<?php

declare(strict_types=1);

final class AttendanceConflictException extends RuntimeException
{
    public function __construct(public readonly string $errorCode, string $message)
    {
        parent::__construct($message);
    }
}

final class AttendanceModel
{
    private const OPEN_SESSION_INDEX = 'uq_attendance_one_open_session_per_user';

    public function __construct(private PDO $pdo)
    {
    }

    /** Opens a session. The unique index makes concurrent duplicates impossible. */
    public function clockIn(int $userId): array
    {
        $this->assertUserId($userId);

        try {
            $stmt = $this->pdo->prepare(
                'INSERT INTO attendance (user_id, clock_in_time) VALUES (:user_id, NOW())'
            );
            $stmt->execute(['user_id' => $userId]);
        } catch (PDOException $e) {
            if (($e->errorInfo[1] ?? null) === 1062 && str_contains($e->getMessage(), self::OPEN_SESSION_INDEX)) {
                throw new AttendanceConflictException(
                    'ALREADY_CLOCKED_IN',
                    'You already have an active clock-in session.'
                );
            }
            throw $e;
        }

        return $this->findById((int) $this->pdo->lastInsertId());
    }

    /** Closes the open session. The row lock serializes concurrent clock-outs. */
    public function clockOut(int $userId): array
    {
        $this->assertUserId($userId);

        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare(
                'SELECT id FROM attendance
                 WHERE user_id = :user_id AND clock_out_time IS NULL
                 FOR UPDATE'
            );
            $stmt->execute(['user_id' => $userId]);
            $id = $stmt->fetchColumn();

            if ($id === false) {
                throw new AttendanceConflictException(
                    'NOT_CLOCKED_IN',
                    'There is no active clock-in session to clock out of.'
                );
            }

            $update = $this->pdo->prepare(
                'UPDATE attendance SET clock_out_time = NOW()
                 WHERE id = :id AND clock_out_time IS NULL'
            );
            $update->execute(['id' => $id]);

            if ($update->rowCount() !== 1) {
                throw new AttendanceConflictException(
                    'NOT_CLOCKED_IN',
                    'There is no active clock-in session to clock out of.'
                );
            }

            $this->pdo->commit();
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }

        return $this->findById((int) $id);
    }

    private function findById(int $id): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, user_id, clock_in_time, clock_out_time FROM attendance WHERE id = :id'
        );
        $stmt->execute(['id' => $id]);

        return $stmt->fetch();
    }

    private function assertUserId(int $userId): void
    {
        if ($userId <= 0) {
            throw new InvalidArgumentException('Invalid user id.');
        }
    }
}
