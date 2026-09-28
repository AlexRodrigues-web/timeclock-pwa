<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class PasswordReset
{
    public static function create(int $userId, string $tokenHash, string $expiresAt): void
    {
        self::invalidateOpenTokens($userId);

        $sql = 'INSERT INTO password_resets (user_id, token_hash, expires_at) VALUES (:user_id, :token_hash, :expires_at)';
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute([
            'user_id' => $userId,
            'token_hash' => $tokenHash,
            'expires_at' => $expiresAt,
        ]);
    }

    public static function findValidByToken(string $plainToken): ?array
    {
        $sql = 'SELECT pr.*, u.id AS user_id_real, u.name, u.email, u.active
                FROM password_resets pr
                INNER JOIN users u ON u.id = pr.user_id
                WHERE pr.used_at IS NULL
                  AND pr.expires_at >= NOW()
                ORDER BY pr.id DESC';
        $stmt = Database::connection()->query($sql);
        $rows = $stmt->fetchAll();

        foreach ($rows as $row) {
            if (password_verify($plainToken, $row['token_hash'])) {
                return $row;
            }
        }

        return null;
    }

    public static function markUsed(int $id): void
    {
        $sql = 'UPDATE password_resets SET used_at = NOW() WHERE id = :id LIMIT 1';
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute(['id' => $id]);
    }

    public static function invalidateOpenTokens(int $userId): void
    {
        $sql = 'UPDATE password_resets SET used_at = NOW() WHERE user_id = :user_id AND used_at IS NULL';
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
    }
}
