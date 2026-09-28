<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\View;
use App\Models\AuditType;
use App\Models\Store;
use App\Models\TimeEntry;
use App\Models\WorkDay;

class ManualEntryController
{
    public function index(): void
    {
        if (!Auth::check()) {
            redirect_to('/auditor-app/public/login');
        }

        require BASE_PATH . '/app/models/Store.php';
        require BASE_PATH . '/app/models/AuditType.php';

        $user = Auth::user();

        View::render('manual_entry/index', [
            'title' => 'Lançamento Manual',
            'user' => $user,
            'stores' => Store::allActive(),
            'auditTypes' => AuditType::allActive(),
            'success' => $_SESSION['success'] ?? null,
            'error' => $_SESSION['error'] ?? null,
            'old' => $_SESSION['old'] ?? [
                'date' => date('Y-m-d'),
                'stores_sequence' => '',
                'entry_time' => '',
                'lunch_start' => '',
                'lunch_end' => '',
                'exit_time' => '',
                'notes' => '',
                'audit_type_ids' => [],
            ],
        ]);

        unset($_SESSION['success'], $_SESSION['error'], $_SESSION['old']);
    }

    public function store(): void
    {
        if (!Auth::check()) {
            redirect_to('/auditor-app/public/login');
        }

        require BASE_PATH . '/app/models/TimeEntry.php';
        require BASE_PATH . '/app/models/WorkDay.php';
        require BASE_PATH . '/app/models/AuditType.php';

        $user = Auth::user();

        $date = trim($_POST['date'] ?? '');
        $storesSequence = trim($_POST['stores_sequence'] ?? '');
        $entryTime = trim($_POST['entry_time'] ?? '');
        $lunchStart = trim($_POST['lunch_start'] ?? '');
        $lunchEnd = trim($_POST['lunch_end'] ?? '');
        $exitTime = trim($_POST['exit_time'] ?? '');
        $notes = trim($_POST['notes'] ?? '');

        $auditTypeIdsRaw = $_POST['audit_type_ids'] ?? [];

        if (!is_array($auditTypeIdsRaw)) {
            $auditTypeIdsRaw = [];
        }

        $auditTypeIds = array_values(
            array_unique(
                array_filter(
                    array_map('intval', $auditTypeIdsRaw),
                    static fn (int $id): bool => $id > 0
                )
            )
        );

        $_SESSION['old'] = [
            'date' => $date,
            'stores_sequence' => $storesSequence,
            'entry_time' => $entryTime,
            'lunch_start' => $lunchStart,
            'lunch_end' => $lunchEnd,
            'exit_time' => $exitTime,
            'notes' => $notes,
            'audit_type_ids' => $auditTypeIds,
        ];

        if (
            $date === '' ||
            !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)
        ) {
            $_SESSION['error'] = 'Data inválida.';
            redirect_to('/auditor-app/public/manual-entry');
        }

        if ($entryTime === '' || $exitTime === '') {
            $_SESSION['error'] = 'Entrada e saída são obrigatórias.';
            redirect_to('/auditor-app/public/manual-entry');
        }

        foreach (
            [$entryTime, $lunchStart, $lunchEnd, $exitTime]
            as $time
        ) {
            if (
                $time !== '' &&
                !preg_match('/^\d{2}:\d{2}$/', $time)
            ) {
                $_SESSION['error'] =
                    'Existe uma hora inválida no formulário.';

                redirect_to('/auditor-app/public/manual-entry');
            }
        }

        if (
            ($lunchStart !== '' && $lunchEnd === '') ||
            ($lunchStart === '' && $lunchEnd !== '')
        ) {
            $_SESSION['error'] =
                'Se preencher almoço, tens de indicar início e fim.';

            redirect_to('/auditor-app/public/manual-entry');
        }

        if (count($auditTypeIds) < 1) {
            $_SESSION['error'] =
                'Seleciona pelo menos um tipo de auditoria.';

            redirect_to('/auditor-app/public/manual-entry');
        }

        if (count($auditTypeIds) > 2) {
            $_SESSION['error'] =
                'Seleciona no máximo dois tipos de auditoria.';

            redirect_to('/auditor-app/public/manual-entry');
        }

        $validAuditTypeIds =
            AuditType::filterActiveIds($auditTypeIds);

        if (
            count($validAuditTypeIds) !==
            count($auditTypeIds)
        ) {
            $_SESSION['error'] =
                'Um dos tipos de auditoria selecionados é inválido.';

            redirect_to('/auditor-app/public/manual-entry');
        }

        $auditTypeIds = $validAuditTypeIds;
        $primaryAuditTypeId = $auditTypeIds[0];

        $existingEntries =
            TimeEntry::byDate((int) $user['id'], $date);

        if (!empty($existingEntries)) {
            $_SESSION['error'] =
                'Este dia já tem registos. Apaga os dados do dia no Histórico antes de lançar manualmente.';

            redirect_to('/auditor-app/public/manual-entry');
        }

        $workDay =
            WorkDay::findOrCreateByDate(
                (int) $user['id'],
                $date
            );

        WorkDay::updateStatus(
            (int) $workDay['id'],
            'open'
        );

        TimeEntry::create([
            'work_day_id' => (int) $workDay['id'],
            'user_id' => (int) $user['id'],
            'store_id' => null,
            'audit_type_id' => null,
            'entry_type' => 'Entrada',
            'entry_time' => $date . ' ' . $entryTime . ':00',
            'was_manual' => 1,
            'notes' => $notes !== '' ? $notes : null,
        ]);

        if ($lunchStart !== '' && $lunchEnd !== '') {
            TimeEntry::create([
                'work_day_id' => (int) $workDay['id'],
                'user_id' => (int) $user['id'],
                'store_id' => null,
                'audit_type_id' => null,
                'entry_type' => 'Início de almoço',
                'entry_time' => $date . ' ' . $lunchStart . ':00',
                'was_manual' => 1,
                'notes' => null,
            ]);

            TimeEntry::create([
                'work_day_id' => (int) $workDay['id'],
                'user_id' => (int) $user['id'],
                'store_id' => null,
                'audit_type_id' => null,
                'entry_type' => 'Fim de almoço / Retorno',
                'entry_time' => $date . ' ' . $lunchEnd . ':00',
                'was_manual' => 1,
                'notes' => null,
            ]);
        }

        $finalEntryId = TimeEntry::create([
            'work_day_id' => (int) $workDay['id'],
            'user_id' => (int) $user['id'],
            'store_id' => null,
            'audit_type_id' => $primaryAuditTypeId,
            'entry_type' => 'Saída final',
            'entry_time' => $date . ' ' . $exitTime . ':00',
            'was_manual' => 1,
            'notes' => $storesSequence !== ''
                ? $storesSequence
                : null,
        ]);

        TimeEntry::syncAuditTypes(
            $finalEntryId,
            $auditTypeIds
        );

        WorkDay::updateStatus(
            (int) $workDay['id'],
            'finished'
        );

        unset($_SESSION['old']);

        $_SESSION['success'] =
            'Lançamento manual guardado com sucesso.';

        redirect_to('/auditor-app/public/manual-entry');
    }
}