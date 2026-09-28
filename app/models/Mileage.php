<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class Mileage
{
    public static function ensureTables(): void
    {
        $pdo = Database::connection();

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS mileage_settings (
                user_id INT UNSIGNED NOT NULL PRIMARY KEY,
                default_origin_address VARCHAR(255) NULL,
                updated_at TIMESTAMP
                    DEFAULT CURRENT_TIMESTAMP
                    ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB
              DEFAULT CHARSET=utf8mb4
              COLLATE=utf8mb4_unicode_ci
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS work_day_mileage (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                work_day_id INT UNSIGNED NOT NULL,
                user_id INT UNSIGNED NOT NULL,
                origin_address VARCHAR(255) NOT NULL,
                distance_meters INT UNSIGNED NOT NULL DEFAULT 0,
                provider VARCHAR(50) NOT NULL DEFAULT 'google_routes',
                route_json LONGTEXT NULL,
                calculated_at DATETIME NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP
                    DEFAULT CURRENT_TIMESTAMP
                    ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uniq_work_day_mileage (work_day_id),
                KEY idx_work_day_mileage_user (user_id)
            ) ENGINE=InnoDB
              DEFAULT CHARSET=utf8mb4
              COLLATE=utf8mb4_unicode_ci
        ");
    }

    public static function getDefaultOrigin(int $userId): string
    {
        self::ensureTables();

        $stmt = Database::connection()->prepare('
            SELECT default_origin_address
            FROM mileage_settings
            WHERE user_id = :user_id
            LIMIT 1
        ');

        $stmt->execute([
            'user_id' => $userId,
        ]);

        $row = $stmt->fetch();

        return trim((string) ($row['default_origin_address'] ?? ''));
    }

    public static function saveDefaultOrigin(
        int $userId,
        string $originAddress
    ): void {
        self::ensureTables();

        $stmt = Database::connection()->prepare('
            INSERT INTO mileage_settings (
                user_id,
                default_origin_address
            ) VALUES (
                :user_id,
                :default_origin_address
            )
            ON DUPLICATE KEY UPDATE
                default_origin_address =
                    VALUES(default_origin_address)
        ');

        $stmt->execute([
            'user_id' => $userId,
            'default_origin_address' => trim($originAddress),
        ]);
    }

    public static function findByWorkDayId(
        int $workDayId,
        int $userId
    ): ?array {
        self::ensureTables();

        $stmt = Database::connection()->prepare('
            SELECT *
            FROM work_day_mileage
            WHERE work_day_id = :work_day_id
              AND user_id = :user_id
            LIMIT 1
        ');

        $stmt->execute([
            'work_day_id' => $workDayId,
            'user_id' => $userId,
        ]);

        $row = $stmt->fetch();

        return $row ?: null;
    }

    public static function saveForWorkDay(
        int $workDayId,
        int $userId,
        string $originAddress,
        int $distanceMeters,
        array $routeData
    ): void {
        self::ensureTables();

        $routeJson = json_encode(
            $routeData,
            JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES
        );

        $stmt = Database::connection()->prepare('
            INSERT INTO work_day_mileage (
                work_day_id,
                user_id,
                origin_address,
                distance_meters,
                provider,
                route_json,
                calculated_at
            ) VALUES (
                :work_day_id,
                :user_id,
                :origin_address,
                :distance_meters,
                :provider,
                :route_json,
                NOW()
            )
            ON DUPLICATE KEY UPDATE
                user_id = VALUES(user_id),
                origin_address = VALUES(origin_address),
                distance_meters = VALUES(distance_meters),
                provider = VALUES(provider),
                route_json = VALUES(route_json),
                calculated_at = NOW()
        ');

        $stmt->execute([
            'work_day_id' => $workDayId,
            'user_id' => $userId,
            'origin_address' => trim($originAddress),
            'distance_meters' => max(0, $distanceMeters),
            'provider' => 'google_routes',
            'route_json' => $routeJson ?: null,
        ]);
    }
}