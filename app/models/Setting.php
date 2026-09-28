<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class Setting
{
    public static function ensureTable(): void
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS settings (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id INT UNSIGNED NOT NULL,
                email_to VARCHAR(190) NULL,
                auditor_name VARCHAR(190) NULL,
                success_message VARCHAR(255) NULL,
                weekly_hours VARCHAR(50) NULL,
                work_schedule VARCHAR(255) NULL,
                work_location VARCHAR(190) NULL,
                vehicle_plate VARCHAR(100) NULL,
                employee_number VARCHAR(100) NULL,
                professional_category VARCHAR(190) NULL,
                company_name VARCHAR(190) NULL,
                company_logo_path VARCHAR(255) NULL,
                signature_path VARCHAR(255) NULL,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uniq_user_settings (user_id)
            )
        ";

        Database::connection()->exec($sql);

        $columns = [
            "weekly_hours" => "ALTER TABLE settings ADD COLUMN weekly_hours VARCHAR(50) NULL",
            "work_schedule" => "ALTER TABLE settings ADD COLUMN work_schedule VARCHAR(255) NULL",
            "work_location" => "ALTER TABLE settings ADD COLUMN work_location VARCHAR(190) NULL",
            "vehicle_plate" => "ALTER TABLE settings ADD COLUMN vehicle_plate VARCHAR(100) NULL",
            "employee_number" => "ALTER TABLE settings ADD COLUMN employee_number VARCHAR(100) NULL",
            "professional_category" => "ALTER TABLE settings ADD COLUMN professional_category VARCHAR(190) NULL",
            "company_name" => "ALTER TABLE settings ADD COLUMN company_name VARCHAR(190) NULL",
            "company_logo_path" => "ALTER TABLE settings ADD COLUMN company_logo_path VARCHAR(255) NULL",
            "signature_path" => "ALTER TABLE settings ADD COLUMN signature_path VARCHAR(255) NULL",
        ];

        foreach ($columns as $column => $statement) {
            try {
                Database::connection()->query("SELECT {$column} FROM settings LIMIT 1");
            } catch (\Throwable $e) {
                Database::connection()->exec($statement);
            }
        }
    }

    public static function findByUserId(int $userId): ?array
    {
        self::ensureTable();

        $stmt = Database::connection()->prepare('SELECT * FROM settings WHERE user_id = :user_id LIMIT 1');
        $stmt->execute(['user_id' => $userId]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public static function save(int $userId, array $data): void
    {
        self::ensureTable();

        $existing = self::findByUserId($userId);

        $payload = [
            'email_to' => $data['email_to'],
            'auditor_name' => $data['auditor_name'],
            'success_message' => $data['success_message'],
            'weekly_hours' => $data['weekly_hours'],
            'work_schedule' => $data['work_schedule'],
            'work_location' => $data['work_location'],
            'vehicle_plate' => $data['vehicle_plate'],
            'employee_number' => $data['employee_number'],
            'professional_category' => $data['professional_category'],
            'company_name' => $data['company_name'],
            'company_logo_path' => $data['company_logo_path'] ?? null,
            'signature_path' => $data['signature_path'] ?? null,
            'user_id' => $userId,
        ];

        if ($existing) {
            $stmt = Database::connection()->prepare('
                UPDATE settings
                SET
                    email_to = :email_to,
                    auditor_name = :auditor_name,
                    success_message = :success_message,
                    weekly_hours = :weekly_hours,
                    work_schedule = :work_schedule,
                    work_location = :work_location,
                    vehicle_plate = :vehicle_plate,
                    employee_number = :employee_number,
                    professional_category = :professional_category,
                    company_name = :company_name,
                    company_logo_path = :company_logo_path,
                    signature_path = :signature_path
                WHERE user_id = :user_id
            ');
        } else {
            $stmt = Database::connection()->prepare('
                INSERT INTO settings (
                    email_to,
                    auditor_name,
                    success_message,
                    weekly_hours,
                    work_schedule,
                    work_location,
                    vehicle_plate,
                    employee_number,
                    professional_category,
                    company_name,
                    company_logo_path,
                    signature_path,
                    user_id
                ) VALUES (
                    :email_to,
                    :auditor_name,
                    :success_message,
                    :weekly_hours,
                    :work_schedule,
                    :work_location,
                    :vehicle_plate,
                    :employee_number,
                    :professional_category,
                    :company_name,
                    :company_logo_path,
                    :signature_path,
                    :user_id
                )
            ');
        }

        $stmt->execute($payload);
    }
}
