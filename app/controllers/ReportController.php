<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\View;
use App\Models\ReportMonthly;
use App\Models\Setting;
use Dompdf\Dompdf;
use Dompdf\Options;
use RuntimeException;
use Throwable;

class ReportController
{
    public function pdf(): void
    {
        if (!Auth::check()) {
            redirect_to('/auditor-app/public/login');
            return;
        }

        require BASE_PATH . '/app/models/Setting.php';
        require BASE_PATH . '/app/models/ReportMonthly.php';

        $user = Auth::user();
        $settings = Setting::findByUserId((int) $user['id']) ?? [];

        $month = $this->requestInt('month', (int) date('n'));
        $year = $this->requestInt('year', (int) date('Y'));

        $report = ReportMonthly::build((int) $user['id'], $month, $year);

        $consultDate = trim(
            (string) ($_GET['day'] ?? date('Y-m-d'))
        );

        $consultTimestamp = strtotime($consultDate);
        $todayIso = date('Y-m-d');

        if (
            !preg_match('/^\d{4}-\d{2}-\d{2}$/', $consultDate) ||
            $consultTimestamp === false ||
            date('Y-m-d', $consultTimestamp) !== $consultDate ||
            $consultDate > $todayIso
        ) {
            $consultDate = $todayIso;
            $consultTimestamp = strtotime($consultDate);
        }

        $consultMonth = (int) date('n', $consultTimestamp);
        $consultYear = (int) date('Y', $consultTimestamp);

        $consultReport =
            (
                $consultMonth === $month &&
                $consultYear === $year
            )
                ? $report
                : ReportMonthly::build(
                    (int) $user['id'],
                    $consultMonth,
                    $consultYear
                );

        $consultDay = null;

        foreach (($consultReport['rows'] ?? []) as $row) {
            if (($row['date_iso'] ?? '') === $consultDate) {
                $consultDay = $row;
                break;
            }
        }

        View::render('reports/pdf', [
            'title' => 'Exportar PDF',
            'user' => $user,
            'settings' => $settings,
            'month' => $month,
            'year' => $year,
            'report' => $report,
            'consultDate' => $consultDate,
            'consultDay' => $consultDay,
            'runtimeSignatureActive' => !empty($_SESSION['report_runtime_signature_path']),
        ]);
    }

    public function monthlyPreview(): void
    {
        if (!Auth::check()) {
            redirect_to('/auditor-app/public/login');
            return;
        }

        require BASE_PATH . '/app/models/Setting.php';
        require BASE_PATH . '/app/models/ReportMonthly.php';

        $user = Auth::user();
        $this->captureRuntimeSignature((int) $user['id']);

        $settings = Setting::findByUserId((int) $user['id']) ?? [];

        if (!empty($_SESSION['report_runtime_signature_path'])) {
            $settings['signature_path'] = $_SESSION['report_runtime_signature_path'];
        }

        $month = $this->requestInt('month', (int) date('n'));
        $year = $this->requestInt('year', (int) date('Y'));

        $report = ReportMonthly::build((int) $user['id'], $month, $year);

        View::render('reports/monthly_preview', [
            'title' => 'Visualizar Folha Mensal',
            'user' => $user,
            'settings' => $settings,
            'report' => $report,
        ]);
    }

    public function monthlyDownload(): void
    {
        if (!Auth::check()) {
            redirect_to('/auditor-app/public/login');
            return;
        }

        require BASE_PATH . '/app/models/Setting.php';
        require BASE_PATH . '/app/models/ReportMonthly.php';

        $initialBufferLevel = ob_get_level();

        try {
            $user = Auth::user();
            $this->captureRuntimeSignature((int) $user['id']);

            $settings = Setting::findByUserId((int) $user['id']) ?? [];

            if (!empty($_SESSION['report_runtime_signature_path'])) {
                $settings['signature_path'] = $_SESSION['report_runtime_signature_path'];
            }

            $month = $this->requestInt('month', (int) date('n'));
            $year = $this->requestInt('year', (int) date('Y'));

            $report = ReportMonthly::build((int) $user['id'], $month, $year);

            ob_start();

            require BASE_PATH . '/app/views/reports/monthly_pdf_template.php';

            $html = ob_get_clean();

            if (!is_string($html) || trim($html) === '') {
                throw new RuntimeException('O template do PDF gerou HTML vazio.');
            }

            $pdfDir = BASE_PATH . '/storage/pdf';
            $this->ensureWritableDirectory($pdfDir, 'storage/pdf');

            $tempDir = BASE_PATH . '/storage';

            if (!is_dir($tempDir) || !is_writable($tempDir)) {
                $tempDir = sys_get_temp_dir();
            }

            $options = new Options();
            $options->set('isRemoteEnabled', true);
            $options->set('isHtml5ParserEnabled', true);
            $options->set('defaultFont', 'DejaVu Sans');
            $options->set('chroot', BASE_PATH);
            $options->set('tempDir', $tempDir);

            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($html, 'UTF-8');
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();

            /*
             * AUDITOR_PAGE_FOOTER_2G11
             *
             * Rodapé técnico real:
             * AlexDevCode · Página 1/2
             *
             * O Canvas conhece o número atual
             * e o total real de páginas após render().
             */
            $canvas = $dompdf->getCanvas();

            $canvas->page_script(
                static function (
                    $pageNumber,
                    $pageCount,
                    $canvas,
                    $fontMetrics
                ): void {
                    $font =
                        $fontMetrics->getFont(
                            'DejaVu Sans',
                            'normal'
                        );

                    if (!$font) {
                        return;
                    }

                    $fontSize = 7.5;

                    $text = sprintf(
                        'AlexDevCode · Página %d/%d',
                        $pageNumber,
                        $pageCount
                    );

                    $textWidth =
                        $fontMetrics->getTextWidth(
                            $text,
                            $font,
                            $fontSize
                        );

                    $x = max(
                        0.0,
                        (
                            $canvas->get_width() -
                            $textWidth
                        ) / 2
                    );

                    /*
                     * Dentro da margem inferior
                     * existente da folha.
                     */
                    $y =
                        $canvas->get_height() -
                        11.5;

                    $canvas->text(
                        $x,
                        $y,
                        $text,
                        $font,
                        $fontSize,
                        [
                            0.42,
                            0.47,
                            0.49
                        ]
                    );
                }
            );

            $pdfOutput = $dompdf->output();

            if (!is_string($pdfOutput) || strlen($pdfOutput) < 500) {
                throw new RuntimeException('O Dompdf gerou um PDF vazio ou inválido.');
            }

            $fileName = sprintf(
                'folha-mensal-%s-%s.pdf',
                str_pad((string) $month, 2, '0', STR_PAD_LEFT),
                (string) $year
            );

            $pdfPath = $pdfDir . DIRECTORY_SEPARATOR . $fileName;
            $written = file_put_contents($pdfPath, $pdfOutput);

            if ($written === false) {
                throw new RuntimeException('Falha ao gravar o PDF em storage/pdf.');
            }

            while (ob_get_level() > $initialBufferLevel) {
                ob_end_clean();
            }

            if (headers_sent($file, $line)) {
                throw new RuntimeException(
                    "Headers já enviados em {$file}:{$line}. " .
                    'O PDF não consegue ser enviado depois disso.'
                );
            }

            /*
             * O novo botão envia download=1.
             *
             * Quando download=1:
             * o PDF é descarregado pelo Android, evitando o
             * visualizador interno problemático da PWA.
             *
             * Sem download=1:
             * mantém o funcionamento antigo em modo inline.
             */
            $forceDownload =
                isset($_GET['download']) &&
                (string) $_GET['download'] === '1';

            $contentDisposition = $forceDownload
                ? 'attachment'
                : 'inline';

            /*
             * Impede navegador, PWA, WebView, proxy ou CDN
             * de reutilizar uma versão anterior do PDF.
             */
            header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
            header('Cache-Control: post-check=0, pre-check=0', false);
            header('Pragma: no-cache');
            header('Expires: 0');

            header('X-Content-Type-Options: nosniff');
            header('Content-Type: application/pdf');

            header(
                'Content-Disposition: ' .
                $contentDisposition .
                '; filename="' .
                $fileName .
                '"'
            );

            header('Content-Length: ' . strlen($pdfOutput));

            echo $pdfOutput;
            exit;
        } catch (Throwable $e) {
            while (ob_get_level() > $initialBufferLevel) {
                ob_end_clean();
            }

            error_log('[TimeClock PDF] ' . $e->getMessage());

            if (!headers_sent()) {
                http_response_code(500);
                header('Content-Type: text/plain; charset=UTF-8');
            }

            echo 'Erro ao gerar PDF: ' . $e->getMessage();
            exit;
        }
    }

    public function email(): void
    {
        if (!Auth::check()) {
            redirect_to('/auditor-app/public/login');
            return;
        }

        require BASE_PATH . '/app/models/Setting.php';

        $user = Auth::user();
        $settings = Setting::findByUserId((int) $user['id']) ?? [];

        View::render('reports/email', [
            'title' => 'Enviar Email',
            'settings' => $settings,
            'message' => 'Ligação do envio por email será feita no próximo bloco.',
        ]);
    }

    private function requestInt(string $key, int $default): int
    {
        if (isset($_POST[$key]) && $_POST[$key] !== '') {
            return (int) $_POST[$key];
        }

        if (isset($_GET[$key]) && $_GET[$key] !== '') {
            return (int) $_GET[$key];
        }

        return $default;
    }

    private function captureRuntimeSignature(int $userId): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        $useDrawnSignature = trim(
            (string) ($_POST['use_drawn_signature'] ?? '0')
        );

        $signatureData = trim(
            (string) ($_POST['signature_data'] ?? '')
        );

        if ($useDrawnSignature !== '1') {
            unset($_SESSION['report_runtime_signature_path']);
            return;
        }

        unset($_SESSION['report_runtime_signature_path']);

        if (
            $signatureData === '' ||
            !str_starts_with(
                $signatureData,
                'data:image/png;base64,'
            )
        ) {
            return;
        }

        $base64 = substr(
            $signatureData,
            strlen('data:image/png;base64,')
        );

        $binary = base64_decode($base64, true);

        if ($binary === false || strlen($binary) < 256) {
            return;
        }

        $runtimeDirFs =
            BASE_PATH .
            '/public/uploads/signatures/runtime';

        $runtimeDirWeb =
            '/auditor-app/public/uploads/signatures/runtime';

        try {
            $this->ensureWritableDirectory(
                $runtimeDirFs,
                'public/uploads/signatures/runtime'
            );
        } catch (Throwable $e) {
            error_log(
                '[TimeClock PDF Signature] ' .
                $e->getMessage()
            );

            return;
        }

        $sessionToken = preg_replace(
            '/[^a-zA-Z0-9_\-]/',
            '',
            session_id()
        ) ?: 'session';

        $fileName =
            'runtime_signature_' .
            $userId .
            '_' .
            $sessionToken .
            '.png';

        $targetFs =
            $runtimeDirFs .
            DIRECTORY_SEPARATOR .
            $fileName;

        $written = file_put_contents(
            $targetFs,
            $binary
        );

        if ($written === false) {
            error_log(
                '[TimeClock PDF Signature] ' .
                'Falha ao gravar assinatura runtime.'
            );

            return;
        }

        $_SESSION['report_runtime_signature_path'] =
            rtrim($runtimeDirWeb, '/') .
            '/' .
            $fileName;
    }

    private function ensureWritableDirectory(
        string $directory,
        string $label
    ): void {
        if (!is_dir($directory)) {
            $created = mkdir(
                $directory,
                0775,
                true
            );

            if (!$created && !is_dir($directory)) {
                throw new RuntimeException(
                    "Não foi possível criar a pasta {$label}."
                );
            }
        }

        if (!is_writable($directory)) {
            throw new RuntimeException(
                "A pasta {$label} não tem permissão de escrita."
            );
        }
    }
}