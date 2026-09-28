<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class AuditType
{
    public static function allActive(): array
    {
        $sql = 'SELECT id, name FROM audit_types WHERE active = 1 ORDER BY name';
        return Database::connection()->query($sql)->fetchAll();
    }
    public static function filterActiveIds(array $ids): array
    {
        $ids = array_values(
            array_unique(
                array_filter(
                    array_map('intval', $ids),
                    static fn (int $id): bool => $id > 0
                )
            )
        );

        if (empty($ids)) {
            return [];
        }

        $placeholders = implode(
            ',',
            array_fill(0, count($ids), '?')
        );

        $stmt = Database::connection()->prepare(
            "SELECT id
             FROM audit_types
             WHERE active = 1
               AND id IN ({$placeholders})"
        );

        $stmt->execute($ids);

        $validSet = [];

        foreach ($stmt->fetchAll() as $row) {
            $validSet[(int) $row['id']] = true;
        }

        return array_values(
            array_filter(
                $ids,
                static fn (int $id): bool =>
                    isset($validSet[$id])
            )
        );
    }
}
