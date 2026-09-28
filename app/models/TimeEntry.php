<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class TimeEntry
{
    public static function countToday(int $userId): int
    {
        $sql = '
            SELECT COUNT(*) AS total
            FROM time_entries te
            INNER JOIN work_days wd ON wd.id = te.work_day_id
            WHERE wd.user_id = :user_id
              AND wd.work_date = :work_date
              AND te.deleted_at IS NULL
        ';

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute([
            'user_id' => $userId,
            'work_date' => date('Y-m-d'),
        ]);

        $row = $stmt->fetch();
        return (int) ($row['total'] ?? 0);
    }

    public static function create(array $data): int
    {
        $sql = '
            INSERT INTO time_entries (
                work_day_id,
                user_id,
                store_id,
                audit_type_id,
                entry_type,
                entry_time,
                was_manual,
                notes
            ) VALUES (
                :work_day_id,
                :user_id,
                :store_id,
                :audit_type_id,
                :entry_type,
                :entry_time,
                :was_manual,
                :notes
            )
        ';

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute([
            'work_day_id' => $data['work_day_id'],
            'user_id' => $data['user_id'],
            'store_id' => $data['store_id'],
            'audit_type_id' => $data['audit_type_id'],
            'entry_type' => $data['entry_type'],
            'entry_time' => $data['entry_time'],
            'was_manual' => $data['was_manual'],
            'notes' => $data['notes'],
        ]);

        return (int) Database::connection()->lastInsertId();
    }

    public static function suggestedTypeByCount(int $count): string
    {
        return match ($count) {
            0 => 'Entrada',
            1 => 'Início de almoço',
            2 => 'Fim de almoço / Retorno',
            3 => 'Saída final',
            default => 'Saída final',
        };
    }

    public static function stepByCount(int $count): int
    {
        return match (true) {
            $count <= 0 => 1,
            $count === 1 => 2,
            $count === 2 => 3,
            default => 4,
        };
    }

    public static function byDate(int $userId, string $date): array
    {
        self::ensureAuditTypeTable();

        $sql = '
            SELECT
                te.id,
                te.entry_type,
                te.entry_time,
                te.was_manual,
                te.notes,
                s.name AS store_name,
                COALESCE(
                    NULLIF(
                        (
                            SELECT GROUP_CONCAT(
                                at_multi.name
                                ORDER BY
                                    teat.sort_order ASC,
                                    at_multi.name ASC
                                SEPARATOR \' / \'
                            )
                            FROM time_entry_audit_types teat
                            INNER JOIN audit_types at_multi
                                ON at_multi.id = teat.audit_type_id
                            WHERE teat.time_entry_id = te.id
                        ),
                        \'\'
                    ),
                    at.name
                ) AS audit_type_name
            FROM time_entries te
            INNER JOIN work_days wd ON wd.id = te.work_day_id
            LEFT JOIN stores s ON s.id = te.store_id
            LEFT JOIN audit_types at ON at.id = te.audit_type_id
            WHERE wd.user_id = :user_id
              AND wd.work_date = :work_date
              AND te.deleted_at IS NULL
            ORDER BY te.entry_time ASC, te.id ASC
        ';

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute([
            'user_id' => $userId,
            'work_date' => $date,
        ]);

        return $stmt->fetchAll();
    }

    public static function findById(int $id, int $userId): ?array
    {
        $sql = '
            SELECT te.*, wd.work_date
            FROM time_entries te
            INNER JOIN work_days wd ON wd.id = te.work_day_id
            WHERE te.id = :id
              AND te.user_id = :user_id
              AND te.deleted_at IS NULL
            LIMIT 1
        ';
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute([
            'id' => $id,
            'user_id' => $userId,
        ]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function updateEntry(int $id, int $userId, array $data): void
    {
        $sql = '
            UPDATE time_entries
            SET store_id = :store_id,
                audit_type_id = :audit_type_id,
                entry_time = :entry_time,
                notes = :notes,
                was_manual = :was_manual,
                updated_by_user_id = :updated_by_user_id
            WHERE id = :id
              AND user_id = :user_id
              AND deleted_at IS NULL
        ';
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute([
            'store_id' => $data['store_id'],
            'audit_type_id' => $data['audit_type_id'],
            'entry_time' => $data['entry_time'],
            'notes' => $data['notes'],
            'was_manual' => $data['was_manual'],
            'updated_by_user_id' => $userId,
            'id' => $id,
            'user_id' => $userId,
        ]);
    }

    public static function softDelete(int $id, int $userId): void
    {
        $stmt = Database::connection()->prepare("
            UPDATE time_entries
            SET deleted_at = NOW(), updated_by_user_id = :user_id
            WHERE id = :id AND user_id = :user_id AND deleted_at IS NULL
        ");
        $stmt->execute([
            'id' => $id,
            'user_id' => $userId,
        ]);
    }

    public static function softDeleteByDate(int $userId, string $date): void
    {
        $sql = "
            UPDATE time_entries te
            INNER JOIN work_days wd ON wd.id = te.work_day_id
            SET te.deleted_at = NOW(),
                te.updated_by_user_id = :user_id
            WHERE te.user_id = :user_id
              AND wd.work_date = :work_date
              AND te.deleted_at IS NULL
        ";

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute([
            'user_id' => $userId,
            'work_date' => $date,
        ]);
    }
    public static function routeStoresByDate(
        int $userId,
        string $date
    ): array {
        $sql = '
            SELECT
                te.store_id,
                s.brand,
                s.name,
                s.city,
                s.district,
                te.entry_time,
                te.id AS entry_id
            FROM time_entries te
            INNER JOIN work_days wd
                ON wd.id = te.work_day_id
            INNER JOIN stores s
                ON s.id = te.store_id
            WHERE wd.user_id = :user_id
              AND wd.work_date = :work_date
              AND te.deleted_at IS NULL
              AND te.store_id IS NOT NULL
            ORDER BY te.entry_time ASC, te.id ASC
        ';

        $stmt = Database::connection()->prepare($sql);

        $stmt->execute([
            'user_id' => $userId,
            'work_date' => $date,
        ]);

        $rows = $stmt->fetchAll();

        $result = [];
        $seenStoreIds = [];

        foreach ($rows as $row) {
            $storeId = (int) (
                $row['store_id'] ??
                0
            );

            if (
                $storeId <= 0 ||
                isset($seenStoreIds[$storeId])
            ) {
                continue;
            }

            $seenStoreIds[$storeId] = true;

            $brand = trim(
                (string) (
                    $row['brand'] ??
                    ''
                )
            );

            $name = trim(
                (string) (
                    $row['name'] ??
                    ''
                )
            );

            $city = trim(
                (string) (
                    $row['city'] ??
                    ''
                )
            );

            $district = trim(
                (string) (
                    $row['district'] ??
                    ''
                )
            );

            $storeLabel = $name;

            if (
                $brand !== '' &&
                $name !== '' &&
                stripos(
                    $name,
                    $brand
                ) === false
            ) {
                $storeLabel =
                    $brand . ' ' . $name;
            } elseif (
                $storeLabel === '' &&
                $brand !== ''
            ) {
                $storeLabel = $brand;
            }

            $routeParts = array_values(
                array_filter([
                    $storeLabel,
                    $city,
                    $district,
                    'Portugal',
                ])
            );

            $routeParts = array_values(
                array_unique($routeParts)
            );

            $result[] = [
                'id' => $storeId,
                'brand' => $brand,
                'name' => $name,
                'city' => $city,
                'district' => $district,
                'label' =>
                    $storeLabel !== ''
                        ? $storeLabel
                        : 'Loja ' . $storeId,
                'route_address' =>
                    implode(', ', $routeParts),
            ];
        }

        return $result;
    }
    /*
     * AUDIT_HISTORY_MULTI_2H2B
     *
     * Recupera os tipos associados a uma marcação.
     * A relação múltipla é a fonte principal.
     * audit_type_id continua como fallback legado.
     */
    public static function auditTypeIdsForEntry(
        int $timeEntryId,
        int $userId
    ): array {
        self::ensureAuditTypeTable();

        $pdo = Database::connection();

        $stmt = $pdo->prepare('
            SELECT
                teat.audit_type_id
            FROM time_entry_audit_types teat
            INNER JOIN time_entries te
                ON te.id = teat.time_entry_id
            WHERE teat.time_entry_id = :time_entry_id
              AND te.user_id = :user_id
              AND te.deleted_at IS NULL
            ORDER BY
                teat.sort_order ASC,
                teat.audit_type_id ASC
        ');

        $stmt->execute([
            'time_entry_id' => $timeEntryId,
            'user_id' => $userId,
        ]);

        $ids = array_values(
            array_filter(
                array_map(
                    'intval',
                    array_column(
                        $stmt->fetchAll(),
                        'audit_type_id'
                    )
                ),
                static fn (int $id): bool =>
                    $id > 0
            )
        );

        if (!empty($ids)) {
            return $ids;
        }

        /*
         * Compatibilidade com registos antigos
         * que ainda possuam somente audit_type_id.
         */
        $legacy = $pdo->prepare('
            SELECT audit_type_id
            FROM time_entries
            WHERE id = :id
              AND user_id = :user_id
              AND deleted_at IS NULL
            LIMIT 1
        ');

        $legacy->execute([
            'id' => $timeEntryId,
            'user_id' => $userId,
        ]);

        $row = $legacy->fetch();

        $legacyId =
            (int) (
                $row['audit_type_id'] ??
                0
            );

        return
            $legacyId > 0
                ? [$legacyId]
                : [];
    }
    public static function ensureAuditTypeTable(): void
    {
        $pdo = Database::connection();

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS time_entry_audit_types (
                time_entry_id INT UNSIGNED NOT NULL,
                audit_type_id INT UNSIGNED NOT NULL,
                sort_order TINYINT UNSIGNED NOT NULL DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

                PRIMARY KEY (
                    time_entry_id,
                    audit_type_id
                ),

                KEY idx_teat_audit_type (
                    audit_type_id
                ),

                CONSTRAINT fk_teat_time_entry
                    FOREIGN KEY (time_entry_id)
                    REFERENCES time_entries(id)
                    ON DELETE CASCADE,

                CONSTRAINT fk_teat_audit_type
                    FOREIGN KEY (audit_type_id)
                    REFERENCES audit_types(id)
                    ON DELETE RESTRICT
            )
            ENGINE=InnoDB
            DEFAULT CHARSET=utf8mb4
            COLLATE=utf8mb4_unicode_ci
        ");

        /*
         * Copia os registos antigos que possuíam
         * somente audit_type_id.
         */
        $pdo->exec("
            INSERT IGNORE INTO time_entry_audit_types (
                time_entry_id,
                audit_type_id,
                sort_order
            )
            SELECT
                id,
                audit_type_id,
                0
            FROM time_entries
            WHERE audit_type_id IS NOT NULL
        ");
    }

    public static function syncAuditTypes(
        int $timeEntryId,
        array $auditTypeIds
    ): void {
        self::ensureAuditTypeTable();

        $auditTypeIds = array_values(
            array_unique(
                array_filter(
                    array_map(
                        'intval',
                        $auditTypeIds
                    ),
                    static fn (int $id): bool => $id > 0
                )
            )
        );

        $pdo = Database::connection();

        $startedTransaction =
            !$pdo->inTransaction();

        if ($startedTransaction) {
            $pdo->beginTransaction();
        }

        try {

            $delete = $pdo->prepare('
                DELETE FROM time_entry_audit_types
                WHERE time_entry_id = :time_entry_id
            ');

            $delete->execute([
                'time_entry_id' => $timeEntryId,
            ]);

            if (!empty($auditTypeIds)) {

                $insert = $pdo->prepare('
                    INSERT INTO time_entry_audit_types (
                        time_entry_id,
                        audit_type_id,
                        sort_order
                    ) VALUES (
                        :time_entry_id,
                        :audit_type_id,
                        :sort_order
                    )
                ');

                foreach (
                    $auditTypeIds as
                    $index => $auditTypeId
                ) {
                    $insert->execute([
                        'time_entry_id' =>
                            $timeEntryId,

                        'audit_type_id' =>
                            $auditTypeId,

                        'sort_order' =>
                            $index,
                    ]);
                }
            }

            if ($startedTransaction) {
                $pdo->commit();
            }

        } catch (\Throwable $e) {

            if (
                $startedTransaction &&
                $pdo->inTransaction()
            ) {
                $pdo->rollBack();
            }

            throw $e;
        }
    }
}
