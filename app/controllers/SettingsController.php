<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\View;
use App\Models\Setting;
use App\Models\Store;

class SettingsController
{
    public function index(): void
    {
        if (!Auth::check()) {
            redirect_to('/auditor-app/public/login');
        }

        require BASE_PATH . '/app/models/Setting.php';

        $user = Auth::user();
        $settings = Setting::findByUserId((int) $user['id']);

        View::render('settings/index', [
            'title' => 'Configurações',
            'user' => $user,
            'settings' => $settings ?? [],
            'success' => $_SESSION['success'] ?? null,
            'error' => $_SESSION['error'] ?? null,
        ]);

        unset($_SESSION['success'], $_SESSION['error']);
    }

    public function save(): void
    {
        if (!Auth::check()) {
            redirect_to('/auditor-app/public/login');
        }

        require BASE_PATH . '/app/models/Setting.php';
        require BASE_PATH . '/app/models/Store.php';

        $user = Auth::user();
        $userId = (int) $user['id'];

        if (($_POST['seed_north_stores'] ?? '') === '1') {
            Store::seedNorthBase();
        }

        $existing = Setting::findByUserId($userId) ?? [];
        $companyLogoPath = $existing['company_logo_path'] ?? null;
        $signaturePath = $existing['signature_path'] ?? null;

        try {
            $deleteCurrentLogo = (string) ($_POST['delete_company_logo'] ?? '') === '1';

            if ($deleteCurrentLogo && !empty($companyLogoPath)) {
                $this->deletePublicFileByWebPath($companyLogoPath);
                $companyLogoPath = null;
            }

            if (!empty($_FILES['company_logo']['name'] ?? '')) {
                if (!empty($companyLogoPath)) {
                    $this->deletePublicFileByWebPath($companyLogoPath);
                }

                $companyLogoPath = $this->handleImageUpload(
                    $_FILES['company_logo'],
                    BASE_PATH . '/public/uploads/logos',
                    '/auditor-app/public/uploads/logos',
                    'logo_' . $userId
                );
            }
        } catch (\Throwable $e) {
            $_SESSION['error'] = $e->getMessage();
            redirect_to('/auditor-app/public/settings');
        }

        Setting::save($userId, [
            'email_to' => trim($_POST['email_to'] ?? ''),
            'auditor_name' => trim($_POST['auditor_name'] ?? ''),
            'success_message' => trim($_POST['success_message'] ?? ''),
            'weekly_hours' => trim($_POST['weekly_hours'] ?? ''),
            'work_schedule' => trim($_POST['work_schedule'] ?? ''),
            'work_location' => trim($_POST['work_location'] ?? ''),
            'vehicle_plate' => trim($_POST['vehicle_plate'] ?? ''),
            'employee_number' => trim($_POST['employee_number'] ?? ''),
            'professional_category' => trim($_POST['professional_category'] ?? ''),
            'company_name' => trim($_POST['company_name'] ?? ''),
            'company_logo_path' => $companyLogoPath,
            'signature_path' => $signaturePath,
        ]);

        $_SESSION['success'] = 'Configurações guardadas com sucesso.';
        redirect_to('/auditor-app/public/settings');
    }

    private function handleImageUpload(array $file, string $targetDirFs, string $targetDirWeb, string $baseName): string
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('Falha no upload da imagem.');
        }

        $tmp = $file['tmp_name'] ?? '';
        if ($tmp === '' || !is_uploaded_file($tmp)) {
            throw new \RuntimeException('Ficheiro enviado inválido.');
        }

        $mime = mime_content_type($tmp) ?: '';
        $allowed = [
            'image/png' => 'png',
            'image/jpeg' => 'jpg',
            'image/webp' => 'webp',
        ];

        if (!isset($allowed[$mime])) {
            throw new \RuntimeException('Formato inválido. Usa PNG, JPG ou WEBP.');
        }

        if (($file['size'] ?? 0) > 5 * 1024 * 1024) {
            throw new \RuntimeException('A imagem excede o limite de 5MB.');
        }

        if (!is_dir($targetDirFs)) {
            mkdir($targetDirFs, 0777, true);
        }

        $extension = $allowed[$mime];
        $finalName = $baseName . '_' . date('Ymd_His') . '.' . $extension;
        $destination = rtrim($targetDirFs, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $finalName;

        if (!move_uploaded_file($tmp, $destination)) {
            throw new \RuntimeException('Não foi possível guardar a imagem enviada.');
        }

        return rtrim($targetDirWeb, '/') . '/' . $finalName;
    }

    private function deletePublicFileByWebPath(string $webPath): void
    {
        $normalized = trim($webPath);

        if ($normalized === '') {
            return;
        }

        $publicPrefix = '/auditor-app/public';
        if (str_starts_with($normalized, $publicPrefix)) {
            $normalized = substr($normalized, strlen($publicPrefix));
        }

        $normalized = '/' . ltrim($normalized, '/');
        $fullPath = BASE_PATH . '/public' . $normalized;

        if (is_file($fullPath)) {
            @unlink($fullPath);
        }
    }
}
