<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class WorkDay
{
    public static function findOrCreateToday(int $userId): array
    {
        $today = date('Y-m-d');

        $select = Database::connection()->prepare('SELECT * FROM work_days WHERE user_id = :user_id AND work_date = :work_date LIMIT 1');
        $select->execute([
            'user_id' => $userId,
            'work_date' => $today,
        ]);

        $workDay = $select->fetch();

        if ($workDay) {
            return $workDay;
        }

        $insert = Database::connection()->prepare(
            'INSERT INTO work_days (user_id, work_date, status) VALUES (:user_id, :work_date, :status)'
        );

        $insert->execute([
            'user_id' => $userId,
            'work_date' => $today,
            'status' => 'open',
        ]);

        return self::findById((int) Database::connection()->lastInsertId());
    }

    public static function findOrCreateByDate(int $userId, string $date): array
    {
        $select = Database::connection()->prepare('SELECT * FROM work_days WHERE user_id = :user_id AND work_date = :work_date LIMIT 1');
        $select->execute([
            'user_id' => $userId,
            'work_date' => $date,
        ]);

        $workDay = $select->fetch();

        if ($workDay) {
            return $workDay;
        }

        $insert = Database::connection()->prepare(
            'INSERT INTO work_days (user_id, work_date, status) VALUES (:user_id, :work_date, :status)'
        );

        $insert->execute([
            'user_id' => $userId,
            'work_date' => $date,
            'status' => 'open',
        ]);

        return self::findById((int) Database::connection()->lastInsertId());
    }

    public static function findById(int $id): array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM work_days WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public static function findByDate(int $userId, string $date): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM work_days WHERE user_id = :user_id AND work_date = :work_date LIMIT 1');
        $stmt->execute([
            'user_id' => $userId,
            'work_date' => $date,
        ]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function updateStatus(int $id, string $status): void
    {
        $stmt = Database::connection()->prepare('UPDATE work_days SET status = :status WHERE id = :id');
        $stmt->execute([
            'status' => $status,
            'id' => $id,
        ]);
    }

    public static function reopen(int $id): void
    {
        $stmt = Database::connection()->prepare("
            UPDATE work_days
            SET status = 'open',
                reopened_at = NOW(),
                reopened_count = reopened_count + 1
            WHERE id = :id
        ");
        $stmt->execute(['id' => $id]);
    }
}