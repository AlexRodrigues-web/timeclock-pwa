<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class ReportMonthly
{
    public static function build(int $userId, int $month, int $year): array
    {
        require_once BASE_PATH . '/app/models/Mileage.php';
        require_once BASE_PATH . '/app/models/TimeEntry.php';

        Mileage::ensureTables();
        TimeEntry::ensureAuditTypeTable();

        $start = sprintf('%04d-%02d-01', $year, $month);
        $end = date('Y-m-t', strtotime($start));

        $sql = '
            SELECT
                wd.work_date,
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
                ) AS audit_type_name,
                mileage.distance_meters AS km_distance_meters
            FROM work_days wd
            LEFT JOIN time_entries te
                ON te.work_day_id = wd.id
               AND te.deleted_at IS NULL
            LEFT JOIN stores s ON s.id = te.store_id
            LEFT JOIN audit_types at ON at.id = te.audit_type_id
            LEFT JOIN work_day_mileage mileage
                ON mileage.work_day_id = wd.id
               AND mileage.user_id = wd.user_id
            WHERE wd.user_id = :user_id
              AND wd.work_date BETWEEN :start_date AND :end_date
            ORDER BY wd.work_date ASC, te.entry_time ASC, te.id ASC
        ';

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute([
            'user_id' => $userId,
            'start_date' => $start,
            'end_date' => $end,
        ]);

        $rows = $stmt->fetchAll();

        $grouped = [];
        foreach ($rows as $row) {
            $date = $row['work_date'];
            if (!isset($grouped[$date])) {
                $grouped[$date] = [];
            }
            if (!empty($row['entry_time'])) {
                $grouped[$date][] = $row;
            }
        }

        $holidays = self::holidaysPt($year);
        $daysInMonth = (int) date('t', strtotime($start));
        $result = [];
        $monthlyTotalMinutes = 0;

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
            $entries = $grouped[$date] ?? [];

            $entry1 = '';
            $exit1 = '';
            $entry2 = '';
            $exit2 = '';
            $auditType = '';
            $stores = [];
            $notes = '';
            $kmDistanceMeters = 0;

            foreach ($entries as $entry) {
                $type = $entry['entry_type'];
                $time = date('H:i', strtotime($entry['entry_time']));

                if (
                    $kmDistanceMeters <= 0 &&
                    !empty($entry['km_distance_meters'])
                ) {
                    $kmDistanceMeters = (int) $entry['km_distance_meters'];
                }

                if ($type === 'Entrada') {
                    $entry1 = $time;

                    if (!empty($entry['notes']) && $notes === '') {
                        $notes = $entry['notes'];
                    }
                }

                if ($type === 'Início de almoço') {
                    $exit1 = $time;
                }

                if ($type === 'Fim de almoço / Retorno') {
                    $entry2 = $time;
                }

                if ($type === 'Saída final') {
                    if ($entry2 !== '') {
                        $exit2 = $time;
                    } else {
                        $exit1 = $time;
                    }

                    if (!empty($entry['notes'])) {
                        $stores[] = $entry['notes'];
                    }
                }

                if (!empty($entry['store_name'])) {
                    $stores[] = $entry['store_name'];
                }

                if (!empty($entry['audit_type_name'])) {
                    $auditType = $entry['audit_type_name'];
                }
            }

            $stores = array_values(array_unique(array_filter(array_map('trim', $stores))));
            $storesText = implode(' / ', $stores);

            if ($auditType !== '') {
                $notes = trim(($notes !== '' ? $notes . ' | ' : '') . $auditType, ' |');
            }

            $weekdayNumber = (int) date('N', strtotime($date));
            $isSaturday = $weekdayNumber === 6;
            $isSunday = $weekdayNumber === 7;
            $isWeekend = $isSaturday || $isSunday;
            $holidayName = $holidays[$date] ?? '';
            $isHoliday = $holidayName !== '';

            $dayType = 'normal';
            if ($isHoliday) {
                $dayType = 'holiday';
            } elseif ($isSunday) {
                $dayType = 'sunday';
            } elseif ($isSaturday) {
                $dayType = 'saturday';
            }

            $rowClass = match ($dayType) {
                'holiday' => 'sheet-row-holiday',
                'sunday' => 'sheet-row-sunday',
                'saturday' => 'sheet-row-saturday',
                default => '',
            };

            $totalMinutes = self::calculateTotalMinutes($entry1, $exit1, $entry2, $exit2);
            $monthlyTotalMinutes += $totalMinutes;

            $result[] = [
                'date_iso' => $date,
                'date_label' => self::dateLabelPt($date),
                'day' => $day,
                'weekday' => self::weekdayPt($date),
                'entry1' => $entry1,
                'exit1' => $exit1,
                'entry2' => $entry2,
                'exit2' => $exit2,
                'total_hours' => self::formatDecimalHours($totalMinutes),
                'total_minutes' => $totalMinutes,
                'notes' => $notes,
                'stores' => $storesText,
                'kms' => $kmDistanceMeters > 0
                    ? number_format($kmDistanceMeters / 1000, 1, ',', '')
                    : '',
                'value' => '',
                'is_saturday' => $isSaturday,
                'is_sunday' => $isSunday,
                'is_weekend' => $isWeekend,
                'is_holiday' => $isHoliday,
                'holiday_name' => $holidayName,
                'day_type' => $dayType,
                'row_class' => $rowClass,
            ];
        }

        return [
            'rows' => $result,
            'monthly_total' => self::formatDecimalHours($monthlyTotalMinutes),
            'monthly_total_minutes' => $monthlyTotalMinutes,
            'month_label' => self::monthLabel($month),
            'month_number' => str_pad((string) $month, 2, '0', STR_PAD_LEFT),
            'year' => $year,
        ];
    }

    private static function calculateTotalMinutes(string $entry1, string $exit1, string $entry2, string $exit2): int
    {
        $total = 0;

        if ($entry1 !== '' && $exit1 !== '') {
            $total += self::diffMinutes($entry1, $exit1);
        }

        if ($entry2 !== '' && $exit2 !== '') {
            $total += self::diffMinutes($entry2, $exit2);
        }

        return max(0, $total);
    }

    private static function diffMinutes(string $start, string $end): int
    {
        [$sh, $sm] = array_map('intval', explode(':', $start));
        [$eh, $em] = array_map('intval', explode(':', $end));

        return (($eh * 60) + $em) - (($sh * 60) + $sm);
    }

    private static function formatDecimalHours(int $minutes): string
    {
        if ($minutes <= 0) {
            return '';
        }

        return number_format($minutes / 60, 2, ',', '');
    }

    private static function weekdayPt(string $date): string
    {
        $map = [
            1 => '2ª Feira',
            2 => '3ª Feira',
            3 => '4ª Feira',
            4 => '5ª Feira',
            5 => '6ª Feira',
            6 => 'Sábado',
            7 => 'Domingo',
        ];

        return $map[(int) date('N', strtotime($date))] ?? '';
    }

    private static function dateLabelPt(string $date): string
    {
        $monthMap = [
            1 => 'jan',
            2 => 'fev',
            3 => 'mar',
            4 => 'abr',
            5 => 'mai',
            6 => 'jun',
            7 => 'jul',
            8 => 'ago',
            9 => 'set',
            10 => 'out',
            11 => 'nov',
            12 => 'dez',
        ];

        $month = (int) date('n', strtotime($date));
        return sprintf('%02d-%s', (int) date('d', strtotime($date)), $monthMap[$month] ?? '');
    }

    private static function monthLabel(int $month): string
    {
        $map = [
            1 => 'janeiro',
            2 => 'fevereiro',
            3 => 'março',
            4 => 'abril',
            5 => 'maio',
            6 => 'junho',
            7 => 'julho',
            8 => 'agosto',
            9 => 'setembro',
            10 => 'outubro',
            11 => 'novembro',
            12 => 'dezembro',
        ];

        return $map[$month] ?? '';
    }

    private static function holidaysPt(int $year): array
    {
        $easter = easter_date($year);
        $easterSunday = date('Y-m-d', $easter);
        $goodFriday = date('Y-m-d', strtotime($easterSunday . ' -2 days'));
        $corpusChristi = date('Y-m-d', strtotime($easterSunday . ' +60 days'));

        return [
            sprintf('%04d-01-01', $year) => 'Ano Novo',
            $goodFriday => 'Sexta-Feira Santa',
            sprintf('%04d-04-25', $year) => 'Dia da Liberdade',
            sprintf('%04d-05-01', $year) => 'Dia do Trabalhador',
            sprintf('%04d-06-10', $year) => 'Dia de Portugal',
            $corpusChristi => 'Corpo de Deus',
            sprintf('%04d-08-15', $year) => 'Assunção de Nossa Senhora',
            sprintf('%04d-10-05', $year) => 'Implantação da República',
            sprintf('%04d-11-01', $year) => 'Dia de Todos os Santos',
            sprintf('%04d-12-01', $year) => 'Restauração da Independência',
            sprintf('%04d-12-08', $year) => 'Imaculada Conceição',
            sprintf('%04d-12-25', $year) => 'Natal',
        ];
    }
}