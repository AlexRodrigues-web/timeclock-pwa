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

class HistoryController
{
    public function index(): void
    {
        if (!Auth::check()) {
            redirect_to('/auditor-app/public/login');
        }

        require BASE_PATH . '/app/models/TimeEntry.php';
        require BASE_PATH . '/app/models/WorkDay.php';
        require BASE_PATH . '/app/models/Mileage.php';

        $user = Auth::user();
        $userId = (int) $user['id'];

        $date = trim($_GET['date'] ?? date('Y-m-d'));

        $entries = TimeEntry::byDate(
            $userId,
            $date
        );

        $workDay = WorkDay::findByDate(
            $userId,
            $date
        );

        $kmMileage = null;
        $kmDefaultOrigin = '';
        $kmRouteStores = [];

        if ($workDay) {
            $kmMileage =
                Mileage::findByWorkDayId(
                    (int) $workDay['id'],
                    $userId
                );

            $kmDefaultOrigin =
                Mileage::getDefaultOrigin(
                    $userId
                );

            $kmRouteStores =
                TimeEntry::routeStoresByDate(
                    $userId,
                    $date
                );
        }

        View::render('history/index', [
            'title' => 'Histórico',
            'user' => $user,
            'date' => $date,
            'entries' => $entries,
            'workDay' => $workDay,
            'kmMileage' => $kmMileage,
            'kmDefaultOrigin' => $kmDefaultOrigin,
            'kmRouteStores' => $kmRouteStores,
            'success' => $_SESSION['success'] ?? null,
            'error' => $_SESSION['error'] ?? null,
        ]);

        unset($_SESSION['success'], $_SESSION['error']);
    }

    public function editForm(): void
    {
        if (!Auth::check()) {
            redirect_to('/auditor-app/public/login');
        }

        require BASE_PATH . '/app/models/TimeEntry.php';
        require BASE_PATH . '/app/models/Store.php';
        require BASE_PATH . '/app/models/AuditType.php';

        $user = Auth::user();
        $id = (int) ($_GET['id'] ?? 0);

        $entry = TimeEntry::findById($id, (int) $user['id']);
        if (!$entry) {
            $_SESSION['error'] = 'Registo não encontrado.';
            redirect_to('/auditor-app/public/history');
        }

        View::render('history/edit', [
            'title' => 'Editar registo',
            'user' => $user,
            'entry' => $entry,
            'stores' => Store::allActive(),
            'auditTypes' => AuditType::allActive(),

            /*
             * AUDIT_HISTORY_MULTI_2H2B
             */
            'selectedAuditTypeIds' =>
                TimeEntry::auditTypeIdsForEntry(
                    (int) $entry['id'],
                    (int) $user['id']
                ),
        ]);
    }

    public function update(): void
    {
        if (!Auth::check()) {
            redirect_to('/auditor-app/public/login');
        }

        require BASE_PATH . '/app/models/TimeEntry.php';
        require BASE_PATH . '/app/models/AuditType.php';

        $user = Auth::user();
        $id = (int) ($_POST['id'] ?? 0);
        $entry = TimeEntry::findById($id, (int) $user['id']);

        if (!$entry) {
            $_SESSION['error'] = 'Registo não encontrado.';
            redirect_to('/auditor-app/public/history');
        }

        $storeId = (int) ($_POST['store_id'] ?? 0);
        /*
         * AUDIT_HISTORY_MULTI_2H2B
         *
         * A edição preserva a mesma regra
         * de multi-auditoria do restante sistema.
         *
         * Diferente da finalização normal,
         * aqui zero tipos continua permitido,
         * preservando o antigo "Sem tipo".
         */
        $auditTypeIds =
            $_POST['audit_type_ids'] ??
            [];

        if (!is_array($auditTypeIds)) {
            $auditTypeIds = [];
        }

        $auditTypeIds =
            array_values(
                array_unique(
                    array_filter(
                        array_map(
                            'intval',
                            $auditTypeIds
                        ),
                        static fn (int $id): bool =>
                            $id > 0
                    )
                )
            );
        $entryClock = trim($_POST['entry_clock'] ?? '');
        $notes = trim($_POST['notes'] ?? '');

        if (!preg_match('/^\d{2}:\d{2}$/', $entryClock)) {
            $_SESSION['error'] = 'Hora inválida.';
            redirect_to('/auditor-app/public/history/edit?id=' . $id);
        }

        if (count($auditTypeIds) > 2) {
            $_SESSION['error'] =
                'Seleciona no máximo dois tipos de auditoria.';

            redirect_to(
                '/auditor-app/public/history/edit?id=' .
                $id
            );

            return;
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

            redirect_to(
                '/auditor-app/public/history/edit?id=' .
                $id
            );

            return;
        }
        $entryDateTime = $entry['work_date'] . ' ' . $entryClock . ':00';

        TimeEntry::updateEntry($id, (int) $user['id'], [
            'store_id' => $storeId > 0 ? $storeId : null,
            'audit_type_id' => $auditTypeIds[0] ?? null,
            'entry_time' => $entryDateTime,
            'notes' => $notes !== '' ? $notes : null,
            'was_manual' => 1,
        ]);

        /*
         * AUDIT_HISTORY_MULTI_2H2B
         *
         * Mantém a tabela de relação em sincronia
         * com a edição feita pelo utilizador.
         */
        TimeEntry::syncAuditTypes(
            $id,
            $auditTypeIds
        );

        $_SESSION['success'] = 'Registo atualizado com sucesso.';
        redirect_to('/auditor-app/public/history?date=' . urlencode($entry['work_date']));
    }

    public function delete(): void
    {
        if (!Auth::check()) {
            redirect_to('/auditor-app/public/login');
        }

        require BASE_PATH . '/app/models/TimeEntry.php';

        $user = Auth::user();
        $id = (int) ($_POST['id'] ?? 0);
        $entry = TimeEntry::findById($id, (int) $user['id']);

        if (!$entry) {
            $_SESSION['error'] = 'Registo não encontrado.';
            redirect_to('/auditor-app/public/history');
        }

        TimeEntry::softDelete($id, (int) $user['id']);
        $_SESSION['success'] = 'Registo apagado com sucesso.';
        redirect_to('/auditor-app/public/history?date=' . urlencode($entry['work_date']));
    }

    public function deleteDay(): void
    {
        if (!Auth::check()) {
            redirect_to('/auditor-app/public/login');
        }

        require BASE_PATH . '/app/models/TimeEntry.php';
        require BASE_PATH . '/app/models/WorkDay.php';

        $user = Auth::user();
        $date = trim($_POST['date'] ?? '');

        if ($date === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $_SESSION['error'] = 'Data inválida.';
            redirect_to('/auditor-app/public/history');
        }

        $workDay = WorkDay::findByDate((int) $user['id'], $date);

        if (!$workDay) {
            $_SESSION['error'] = 'Dia não encontrado.';
            redirect_to('/auditor-app/public/history?date=' . urlencode($date));
        }

        TimeEntry::softDeleteByDate((int) $user['id'], $date);
        WorkDay::updateStatus((int) $workDay['id'], 'open');

        $_SESSION['success'] = 'Dados do dia apagados com sucesso.';
        redirect_to('/auditor-app/public/history?date=' . urlencode($date));
    }

    public function reopenDay(): void
    {
        if (!Auth::check()) {
            redirect_to('/auditor-app/public/login');
        }

        require BASE_PATH . '/app/models/WorkDay.php';

        $user = Auth::user();
        $date = trim($_POST['date'] ?? '');

        $workDay = WorkDay::findByDate((int) $user['id'], $date);

        if (!$workDay) {
            $_SESSION['error'] = 'Dia não encontrado.';
            redirect_to('/auditor-app/public/history?date=' . urlencode($date));
        }

        WorkDay::reopen((int) $workDay['id']);
        $_SESSION['success'] = 'Dia reaberto com sucesso.';
        redirect_to('/auditor-app/public/history?date=' . urlencode($date));
    }
}