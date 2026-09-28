<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\View;
use App\Models\AuditType;
use App\Models\Mileage;
use App\Models\Store;
use App\Models\TimeEntry;
use App\Models\WorkDay;

class OperationController
{
    public function index(): void
    {
        if (!Auth::check()) {
            redirect_to('/auditor-app/public/login');
        }

        require BASE_PATH . '/app/models/Store.php';
        require BASE_PATH . '/app/models/AuditType.php';
        require BASE_PATH . '/app/models/WorkDay.php';
        require BASE_PATH . '/app/models/TimeEntry.php';
        require BASE_PATH . '/app/models/Mileage.php';

        $user = Auth::user();
        $workDay = WorkDay::findOrCreateToday((int) $user['id']);
        $entryCount = TimeEntry::countToday((int) $user['id']);

        TimeEntry::ensureAuditTypeTable();

        Mileage::ensureTables();

        $kmMileage = Mileage::findByWorkDayId(
            (int) $workDay['id'],
            (int) $user['id']
        );

        $kmDefaultOrigin =
            Mileage::getDefaultOrigin(
                (int) $user['id']
            );

        $kmRouteStores =
            TimeEntry::routeStoresByDate(
                (int) $user['id'],
                date('Y-m-d')
            );

        $isFinished = ($workDay['status'] ?? 'open') === 'finished';

        $showFinishedToast = !empty($_SESSION['finished_toast']);

        if ($isFinished) {
            $step = 1;
            $stageMessage = 'Preparado para o próximo dia.';
        } else {
            $step = TimeEntry::stepByCount($entryCount);
            $stageMessage = match ($step) {
                1 => 'Próxima marcação: Entrada',
                2 => 'Confirmar se esta marcação é almoço ou saída',
                3 => 'Próxima marcação: Fim de almoço / Retorno',
                4 => 'Próxima marcação: Saída final',
            };
        }

        View::render('operation/index', [
            'title' => 'Operação do Dia',
            'user' => $user,
            'nowDate' => date('d/m/Y'),
            'nowTime' => date('H:i'),
            'stores' => Store::allActive(),
            'auditTypes' => AuditType::allActive(),
            'step' => $step,
            'isFinished' => $isFinished,
            'stageMessage' => $stageMessage,
            'success' => $_SESSION['success'] ?? null,
            'error' => $_SESSION['error'] ?? null,
            'old' => $_SESSION['old'] ?? [],
            'quickStoreOld' => $_SESSION['quick_store_old'] ?? [],
            'kmMileage' => $kmMileage,
            'kmDefaultOrigin' => $kmDefaultOrigin,
            'kmRouteStores' => $kmRouteStores,
            'showFinishedToast' => $showFinishedToast,
            'finishedToastMessage' => $_SESSION['finished_toast'] ?? 'Dia finalizado. Bom descanso e até amanhã.',
        ]);

        unset($_SESSION['success'], $_SESSION['error'], $_SESSION['old'], $_SESSION['quick_store_old'], $_SESSION['finished_toast']);
    }

    public function store(): void
    {
        if (!Auth::check()) {
            redirect_to('/auditor-app/public/login');
        }

        require BASE_PATH . '/app/models/WorkDay.php';
        require BASE_PATH . '/app/models/TimeEntry.php';
        require BASE_PATH . '/app/models/AuditType.php';

        $user = Auth::user();
        $workDay = WorkDay::findOrCreateToday((int) $user['id']);

        if (($workDay['status'] ?? 'open') === 'finished') {
            $_SESSION['error'] = 'O dia de hoje já foi finalizado.';
            redirect_to('/auditor-app/public/');
        }

        $entryCount = TimeEntry::countToday((int) $user['id']);
        $step = TimeEntry::stepByCount($entryCount);

        $storeId = (int) ($_POST['store_id'] ?? 0);

        $auditTypeIdsRaw =
            $_POST['audit_type_ids'] ??
            [];

        if (!is_array($auditTypeIdsRaw)) {
            $auditTypeIdsRaw = [];
        }

        $auditTypeIds = array_values(
            array_unique(
                array_filter(
                    array_map(
                        'intval',
                        $auditTypeIdsRaw
                    ),
                    static fn (int $id): bool => $id > 0
                )
            )
        );

        $auditTypeId =
            $auditTypeIds[0] ??
            0;

        $entryClock = trim($_POST['entry_clock'] ?? '');
        $notes = trim($_POST['notes'] ?? '');

        $_SESSION['old'] = [
            'store_id' => $storeId,
            'audit_type_id' => $auditTypeId,
            'audit_type_ids' => $auditTypeIds,
            'entry_clock' => $entryClock,
            'stage2_choice' => trim($_POST['stage2_choice'] ?? 'almoco'),
        ];

        if (!preg_match('/^\d{2}:\d{2}$/', $entryClock)) {
            $_SESSION['error'] = 'Hora inválida.';
            redirect_to('/auditor-app/public/');
        }

        [$hour, $minute] = array_map('intval', explode(':', $entryClock));
        if ($hour > 23 || $minute > 59) {
            $_SESSION['error'] = 'Hora inválida.';
            redirect_to('/auditor-app/public/');
        }

        $entryType = '';
        $finalizeDay = false;
        $requiresStore = in_array($step, [1, 3], true);

        if ($step === 1) {
            $entryType = 'Entrada';
        }

        if ($step === 2) {
            $choice = trim($_POST['stage2_choice'] ?? 'almoco');

            if ($choice === 'saida') {
                $entryType = 'Saída final';
                $finalizeDay = true;
            } else {
                $entryType = 'Início de almoço';
            }
        }

        if ($step === 3) {
            $entryType = 'Fim de almoço / Retorno';
        }

        if ($step === 4) {
            $entryType = 'Saída final';
            $finalizeDay = true;
        }

        if ($requiresStore && $storeId <= 0) {
            $_SESSION['error'] = 'Seleciona uma loja.';
            redirect_to('/auditor-app/public/');
        }

        if ($step === 4) {

            if (count($auditTypeIds) < 1) {
                $_SESSION['error'] =
                    'Seleciona pelo menos um tipo de auditoria para finalizar.';

                redirect_to('/auditor-app/public/');
            }

            if (count($auditTypeIds) > 2) {
                $_SESSION['error'] =
                    'Seleciona no máximo dois tipos de auditoria.';

                redirect_to('/auditor-app/public/');
            }

            $validAuditTypeIds =
                AuditType::filterActiveIds(
                    $auditTypeIds
                );

            if (
                count($validAuditTypeIds) !==
                count($auditTypeIds)
            ) {
                $_SESSION['error'] =
                    'Um dos tipos de auditoria selecionados é inválido.';

                redirect_to('/auditor-app/public/');
            }

            $auditTypeIds =
                $validAuditTypeIds;

            $auditTypeId =
                $auditTypeIds[0];
        }

        $entryDateTime = date('Y-m-d') . ' ' . $entryClock . ':00';
        $wasManual = $entryClock !== date('H:i') ? 1 : 0;

        $timeEntryId = TimeEntry::create([
            'work_day_id' => (int) $workDay['id'],
            'user_id' => (int) $user['id'],
            'store_id' => $requiresStore ? $storeId : null,
            'audit_type_id' => $step === 4 ? $auditTypeId : null,
            'entry_type' => $entryType,
            'entry_time' => $entryDateTime,
            'was_manual' => $wasManual,
            'notes' => $notes !== '' ? $notes : null,
        ]);

        if ($step === 4) {
            TimeEntry::syncAuditTypes(
                $timeEntryId,
                $auditTypeIds
            );
        }

        if ($finalizeDay) {
            WorkDay::updateStatus((int) $workDay['id'], 'finished');
            $_SESSION['finished_toast'] = 'Dia finalizado. Bom descanso e até amanhã.';
        } else {
            $_SESSION['success'] = "Marcação registada com sucesso: {$entryType}.";
        }

        unset($_SESSION['old']);
        redirect_to('/auditor-app/public/');
    }
}
