<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class User
{
    public static function findByEmail(string $email): ?array
    {
        $sql = 'SELECT * FROM users WHERE email = :email LIMIT 1';
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        return $user ?: null;
    }

    public static function findById(int $id): ?array
    {
        $sql = 'SELECT * FROM users WHERE id = :id LIMIT 1';
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch();

        return $user ?: null;
    }

    public static function findByGoogleId(string $googleId): ?array
    {
        $sql = 'SELECT * FROM users WHERE google_id = :google_id LIMIT 1';
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute(['google_id' => $googleId]);
        $user = $stmt->fetch();

        return $user ?: null;
    }

    public static function create(string $name, string $email, string $passwordHash): int
    {
        $sql = 'INSERT INTO users (name, email, password_hash, active) VALUES (:name, :email, :password_hash, 1)';
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute([
            'name' => $name,
            'email' => $email,
            'password_hash' => $passwordHash,
        ]);

        return (int) Database::connection()->lastInsertId();
    }

    public static function createGoogleUser(string $name, string $email, string $googleId, ?string $avatar = null): int
    {
        $randomPassword = password_hash(bin2hex(random_bytes(24)), PASSWORD_DEFAULT);

        $sql = 'INSERT INTO users (name, email, google_id, avatar, password_hash, active, last_login_at) 
                VALUES (:name, :email, :google_id, :avatar, :password_hash, 1, NOW())';

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute([
            'name' => $name,
            'email' => $email,
            'google_id' => $googleId,
            'avatar' => $avatar,
            'password_hash' => $randomPassword,
        ]);

        return (int) Database::connection()->lastInsertId();
    }

    public static function linkGoogleAccount(int $id, string $googleId, ?string $avatar = null): void
    {
        $sql = 'UPDATE users SET google_id = :google_id, avatar = :avatar WHERE id = :id LIMIT 1';
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute([
            'id' => $id,
            'google_id' => $googleId,
            'avatar' => $avatar,
        ]);
    }

    public static function updateGoogleProfile(int $id, string $name, ?string $avatar = null): void
    {
        $sql = 'UPDATE users SET name = :name, avatar = :avatar WHERE id = :id LIMIT 1';
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute([
            'id' => $id,
            'name' => $name,
            'avatar' => $avatar,
        ]);
    }

    public static function updateProfile(int $id, string $name, string $email): void
    {
        $sql = 'UPDATE users SET name = :name, email = :email WHERE id = :id LIMIT 1';
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute([
            'id' => $id,
            'name' => $name,
            'email' => $email,
        ]);
    }

    public static function updatePassword(int $id, string $passwordHash): void
    {
        $sql = 'UPDATE users SET password_hash = :password_hash WHERE id = :id LIMIT 1';
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute([
            'id' => $id,
            'password_hash' => $passwordHash,
        ]);
    }

    public static function touchLastLogin(int $id): void
    {
        $sql = 'UPDATE users SET last_login_at = NOW() WHERE id = :id LIMIT 1';
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute(['id' => $id]);
    }

    public static function emailExists(string $email, ?int $ignoreId = null): bool
    {
        $sql = 'SELECT id FROM users WHERE email = :email';
        $params = ['email' => $email];

        if ($ignoreId !== null) {
            $sql .= ' AND id <> :ignore_id';
            $params['ignore_id'] = $ignoreId;
        }

        $sql .= ' LIMIT 1';

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($params);

        return (bool) $stmt->fetch();
    }

    public static function isGoogleLinked(array $user): bool
    {
        return !empty($user['google_id']);
    }
}
