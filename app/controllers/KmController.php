<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Models\Mileage;
use App\Models\TimeEntry;
use App\Models\WorkDay;
use App\Services\GoogleRoutesService;
use Throwable;

class KmController
{
    public function calculate(): void
    {
        if (
            isset($_POST['date']) &&
            trim((string) $_POST['date']) !== ''
        ) {
            $this->calculateHistory();
            return;
        }

        if (!Auth::check()) {
            $this->json(401, [
                'ok' => false,
                'error' => 'Sessão expirada.',
            ]);
            return;
        }

        require_once BASE_PATH . '/app/models/Mileage.php';
        require_once BASE_PATH . '/app/models/TimeEntry.php';
        require_once BASE_PATH . '/app/models/WorkDay.php';
        require_once BASE_PATH . '/app/services/GoogleRoutesService.php';

        $user = Auth::user();
        $userId = (int) $user['id'];

        $workDay = WorkDay::findOrCreateToday($userId);

        if (
            ($workDay['status'] ?? 'open') ===
            'finished'
        ) {
            $this->json(409, [
                'ok' => false,
                'error' => 'O dia já foi finalizado.',
            ]);
            return;
        }

        $entryCount = TimeEntry::countToday($userId);
        $step = TimeEntry::stepByCount($entryCount);

        if ($step !== 4) {
            $this->json(409, [
                'ok' => false,
                'error' =>
                    'A quilometragem só pode ser adicionada ' .
                    'na última etapa do dia.',
            ]);
            return;
        }

        $originAddress = trim(
            (string) (
                $_POST['origin_address'] ??
                ''
            )
        );

        if ($originAddress === '') {
            $this->json(422, [
                'ok' => false,
                'error' =>
                    'Informe o ponto de saída.',
            ]);
            return;
        }

        if (mb_strlen($originAddress) > 255) {
            $this->json(422, [
                'ok' => false,
                'error' =>
                    'O endereço informado é muito longo.',
            ]);
            return;
        }

        $stores = TimeEntry::routeStoresByDate(
            $userId,
            date('Y-m-d')
        );

        if (empty($stores)) {
            $this->json(422, [
                'ok' => false,
                'error' =>
                    'Não encontrei lojas nas marcações de hoje.',
            ]);
            return;
        }

        $stopAddresses = array_column(
            $stores,
            'route_address'
        );

        try {
            $service =
                new GoogleRoutesService();

            $route =
                $service->calculateRoundTrip(
                    $originAddress,
                    $stopAddresses
                );

            $routeData = [
                'origin' => $originAddress,
                'stores' => array_map(
                    static function (
                        array $store
                    ): array {
                        return [
                            'id' =>
                                (int) $store['id'],
                            'label' =>
                                $store['label'],
                            'route_address' =>
                                $store['route_address'],
                        ];
                    },
                    $stores
                ),
                'destination' =>
                    $originAddress,
            ];

            Mileage::saveForWorkDay(
                (int) $workDay['id'],
                $userId,
                $originAddress,
                (int) $route['distance_meters'],
                $routeData
            );

            $saveDefault =
                (string) (
                    $_POST['save_default'] ??
                    ''
                ) === '1';

            if ($saveDefault) {
                Mileage::saveDefaultOrigin(
                    $userId,
                    $originAddress
                );
            }

            $this->json(200, [
                'ok' => true,
                'distance_meters' =>
                    (int) $route['distance_meters'],
                'distance_km' =>
                    (float) $route['distance_km'],
                'origin_address' =>
                    $originAddress,
                'saved_default' =>
                    $saveDefault,
            ]);
        } catch (Throwable $e) {
            $this->json(502, [
                'ok' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function calculateHistory(): void
    {
        if (!Auth::check()) {
            $this->json(401, [
                'ok' => false,
                'error' => 'Sessão expirada.',
            ]);
            return;
        }

        require_once BASE_PATH . '/app/models/Mileage.php';
        require_once BASE_PATH . '/app/models/TimeEntry.php';
        require_once BASE_PATH . '/app/models/WorkDay.php';
        require_once BASE_PATH . '/app/services/GoogleRoutesService.php';

        $user = Auth::user();
        $userId = (int) $user['id'];

        $date = trim(
            (string) (
                $_POST['date'] ??
                ''
            )
        );

        $dateTimestamp = strtotime($date);

        if (
            !preg_match(
                '/^\d{4}-\d{2}-\d{2}$/',
                $date
            ) ||
            $dateTimestamp === false ||
            date(
                'Y-m-d',
                $dateTimestamp
            ) !== $date
        ) {
            $this->json(422, [
                'ok' => false,
                'error' => 'Data inválida.',
            ]);
            return;
        }

        $workDay = WorkDay::findByDate(
            $userId,
            $date
        );

        if (!$workDay) {
            $this->json(404, [
                'ok' => false,
                'error' => 'Dia não encontrado.',
            ]);
            return;
        }

        $originAddress = trim(
            (string) (
                $_POST['origin_address'] ??
                ''
            )
        );

        if ($originAddress === '') {
            $this->json(422, [
                'ok' => false,
                'error' =>
                    'Informe o ponto de saída.',
            ]);
            return;
        }

        if (mb_strlen($originAddress) > 255) {
            $this->json(422, [
                'ok' => false,
                'error' =>
                    'O endereço informado é muito longo.',
            ]);
            return;
        }

        $stores =
            TimeEntry::routeStoresByDate(
                $userId,
                $date
            );

        if (empty($stores)) {
            $this->json(422, [
                'ok' => false,
                'error' =>
                    'Não encontrei lojas nas marcações deste dia.',
            ]);
            return;
        }

        $stopAddresses =
            array_column(
                $stores,
                'route_address'
            );

        try {
            $service =
                new GoogleRoutesService();

            $route =
                $service->calculateRoundTrip(
                    $originAddress,
                    $stopAddresses
                );

            $routeData = [
                'work_date' => $date,

                'origin' =>
                    $originAddress,

                'stores' =>
                    array_map(
                        static function (
                            array $store
                        ): array {
                            return [
                                'id' =>
                                    (int) $store['id'],

                                'label' =>
                                    $store['label'],

                                'route_address' =>
                                    $store['route_address'],
                            ];
                        },
                        $stores
                    ),

                'destination' =>
                    $originAddress,
            ];

            Mileage::saveForWorkDay(
                (int) $workDay['id'],
                $userId,
                $originAddress,
                (int) $route['distance_meters'],
                $routeData
            );

            $saveDefault =
                (string) (
                    $_POST['save_default'] ??
                    ''
                ) === '1';

            if ($saveDefault) {
                Mileage::saveDefaultOrigin(
                    $userId,
                    $originAddress
                );
            }

            $this->json(200, [
                'ok' => true,

                'date' =>
                    $date,

                'distance_meters' =>
                    (int) $route['distance_meters'],

                'distance_km' =>
                    (float) $route['distance_km'],

                'origin_address' =>
                    $originAddress,

                'saved_default' =>
                    $saveDefault,
            ]);
        } catch (Throwable $e) {
            $this->json(502, [
                'ok' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }
    private function json(
        int $status,
        array $payload
    ): void {
        http_response_code($status);

        header(
            'Content-Type: application/json; charset=UTF-8'
        );

        echo json_encode(
            $payload,
            JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES
        );
    }
}