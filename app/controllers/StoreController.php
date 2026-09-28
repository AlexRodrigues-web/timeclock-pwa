<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Models\Store;

class StoreController
{
    public function quickCreate(): void
    {
        if (!Auth::check()) {
            redirect_to('/auditor-app/public/login');
        }

        require BASE_PATH . '/app/models/Store.php';

        $brand = trim($_POST['brand'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $district = trim($_POST['district'] ?? '');
        $returnEntryClock = trim($_POST['return_entry_clock'] ?? '');

        $_SESSION['quick_store_old'] = [
            'brand' => $brand,
            'name' => $name,
            'city' => $city,
            'district' => $district,
        ];

        /*
         * Preserva a hora que já estava preenchida
         * no formulário principal.
         */
        $_SESSION['old'] = array_merge(
            $_SESSION['old'] ?? [],
            [
                'entry_clock' => $returnEntryClock,
            ]
        );

        if ($brand === '' || $name === '') {
            $_SESSION['error'] =
                'Preenche pelo menos a marca e o nome da loja.';

            redirect_to('/auditor-app/public/');
        }

        $newStoreId = Store::create([
            'brand' => $brand,
            'name' => $name,
            'city' => $city !== '' ? $city : null,
            'district' => $district !== '' ? $district : null,
        ]);

        if ($newStoreId <= 0) {
            $_SESSION['error'] =
                'Não foi possível cadastrar a loja.';

            redirect_to('/auditor-app/public/');
        }

        /*
         * Seleciona automaticamente a loja recém-criada
         * para a marcação atual.
         */
        $_SESSION['old'] = array_merge(
            $_SESSION['old'] ?? [],
            [
                'store_id' => $newStoreId,
                'entry_clock' => $returnEntryClock,
            ]
        );

        unset($_SESSION['quick_store_old']);

        $_SESSION['success'] =
            'Loja cadastrada e selecionada para esta marcação.';

        redirect_to('/auditor-app/public/');
    }
}