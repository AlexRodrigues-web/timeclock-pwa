<?php
$companyName = $settings['company_name'] ?? 'TimeClock';
$auditorName = $settings['auditor_name'] ?? '-';
$weeklyHours = $settings['weekly_hours'] ?? '-';
$workSchedule = $settings['work_schedule'] ?? '-';
$workLocation = $settings['work_location'] ?? '-';
$vehiclePlate = $settings['vehicle_plate'] ?? '-';
$employeeNumber = $settings['employee_number'] ?? '-';
$professionalCategory = $settings['professional_category'] ?? '-';
$companyLogoPath = $settings['company_logo_path'] ?? null;
$signaturePath = $settings['signature_path'] ?? null;

if (!function_exists('timeclock_pdf_text')) {
    function timeclock_pdf_text(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_scalar($value)) {
            return (string) $value;
        }

        return '';
    }
}

if (!function_exists('timeclock_pdf_h')) {
    function timeclock_pdf_h(mixed $value): string
    {
        return htmlspecialchars(timeclock_pdf_text($value), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('timeclock_pdf_public_asset_path')) {
    function timeclock_pdf_public_asset_path(mixed $path): ?string
    {
        $path = trim(timeclock_pdf_text($path));

        if ($path === '') {
            return null;
        }

        if (preg_match('#^https?://#i', $path)) {
            return $path;
        }

        $path = str_replace('\\', '/', $path);
        $basePath = str_replace('\\', '/', BASE_PATH);
        $basePath = rtrim($basePath, '/');

        $candidates = [];

        if (preg_match('#^[A-Za-z]:/#', $path)) {
            $candidates[] = $path;
        }

        if (str_starts_with($path, $basePath)) {
            $candidates[] = $path;
        }

        $publicMarker = '/public/';
        $publicPos = strpos($path, $publicMarker);

        if ($publicPos !== false) {
            $relativeFromPublic = substr($path, $publicPos + strlen($publicMarker));
            $candidates[] = $basePath . '/public/' . ltrim($relativeFromPublic, '/');
        }

        if (str_starts_with($path, '/auditor-app/public/')) {
            $relativeFromPublic = substr($path, strlen('/auditor-app/public/'));
            $candidates[] = $basePath . '/public/' . ltrim($relativeFromPublic, '/');
        }

        if (str_starts_with($path, '/public/')) {
            $relativeFromPublic = substr($path, strlen('/public/'));
            $candidates[] = $basePath . '/public/' . ltrim($relativeFromPublic, '/');
        }

        if (str_starts_with($path, '/uploads/')) {
            $candidates[] = $basePath . '/public' . $path;
        }

        if (str_starts_with($path, 'uploads/')) {
            $candidates[] = $basePath . '/public/' . $path;
        }

        if (str_starts_with($path, 'public/')) {
            $relativeFromPublic = substr($path, strlen('public/'));
            $candidates[] = $basePath . '/public/' . ltrim($relativeFromPublic, '/');
        }

        $candidates[] = $basePath . '/public/' . ltrim($path, '/');

        foreach (array_unique($candidates) as $candidate) {
            $candidate = str_replace('\\', '/', $candidate);

            if (is_file($candidate)) {
                $realPath = realpath($candidate);

                if ($realPath !== false) {
                    return str_replace('\\', '/', $realPath);
                }

                return $candidate;
            }
        }

        return null;
    }
}

$logoFsPath = timeclock_pdf_public_asset_path($companyLogoPath);
$signatureFsPath = timeclock_pdf_public_asset_path($signaturePath);
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <style>
        @page { size: A4 landscape; margin: 16px 18px 22px 18px; }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10.5px;
            color: #000;
            margin: 0;
        }

        .title {
            text-align: center;
            font-weight: bold;
            font-size: 15px;
            margin-bottom: 8px;
        }

        .head {
            width: 100%;
            border: 1px solid #000;
            border-collapse: collapse;
            margin-bottom: 8px;
        }

        .head td {
            vertical-align: top;
            padding: 8px 10px;
        }

        .brand-box {
            width: 24%;
            text-align: center;
            vertical-align: middle;
        }

        .brand-text {
            font-size: 22px;
            font-weight: bold;
            line-height: 1.1;
        }

        .brand-logo {
            max-width: 210px;
            max-height: 68px;
        }

        .meta-table {
            width: 100%;
            border-collapse: collapse;
        }

        .meta-table td {
            padding: 2px 4px;
            border: none;
            font-size: 10.5px;
            vertical-align: top;
        }

        .meta-label {
            font-weight: bold;
            white-space: nowrap;
            width: 34%;
        }

        .sheet {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .sheet th,
        .sheet td {
            border: 1px solid #000;
            padding: 3px 4px;
            text-align: center;
            vertical-align: middle;
            word-wrap: break-word;
        }

        .sheet thead th {
            background: #efefef;
            font-weight: bold;
        }

        .left {
            text-align: left;
        }

        .small {
            font-size: 9px;
        }

        .sheet-row-saturday td {
            background: #fff46b;
        }

        .sheet-row-sunday td {
            background: #fff46b;
        }

        .sheet-row-holiday td {
            background: #d9e66a;
            font-weight: bold;
        }

        .total-row td {
            font-weight: bold;
            background: #fff46b;
        }

        .signature-wrap {
            width: 100%;
            margin-top: 14px;
        }

        .signature-box {
            width: 270px;
            text-align: center;
        }

        .signature-image {
            max-width: 180px;
            max-height: 62px;
            margin-bottom: 2px;
        }

        .signature-line {
            border-top: 1px solid #000;
            margin-top: 4px;
            width: 100%;
        }

        .signature-label {
            font-size: 9px;
            margin-top: 3px;
        }

        .footer-sign {
            position: fixed;
            bottom: -6px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 9px;
            color: #444;
        }
        /*
         * 2G1 — acabamento conservador.
         * Mantém o formato tradicional da folha entregue ao RH.
         */

        .title {
            color: #111827;
            letter-spacing: 0.35px;
        }

        .head {
            border-color: #374151;
            background: #fbfcfd;
        }

        .head td {
            padding: 7px 9px;
        }

        .meta-label {
            color: #111827;
        }

        .sheet th,
        .sheet td {
            border-color: #374151;
        }

        .sheet thead th {
            background: #f1f3f5;
            color: #111827;
        }

        .sheet-row-saturday td,
        .sheet-row-sunday td {
            background: #fff3a6;
        }

        .sheet-row-holiday td {
            background: #dfeaa1;
        }

        .total-row td {
            background: #f3f4f6;
            border-top: 2px solid #1f2937;
        }

        .signature-wrap {
            border-collapse: collapse;
        }

        .signature-box {
            width: 34%;
            vertical-align: bottom;
        }

        .km-note-box {
            width: 66%;
            padding-left: 32px;
            vertical-align: bottom;
            text-align: right;
            color: #555f6d;
            font-size: 7.6px;
            line-height: 1.38;
        }

        .km-note-title {
            font-weight: bold;
            color: #374151;
        }


        /*
         * 2G2 — refinamento final conservador.
         */

        body {
            font-size: 10px;
            color: #111827;
        }

        .title {
            font-size: 15.5px;
            margin-bottom: 9px;
        }

        .head {
            margin-bottom: 9px;
        }

        .brand-text {
            font-size: 21px;
        }

        .meta-table td {
            padding-top: 2.4px;
            padding-bottom: 2.4px;
            font-size: 9.8px;
        }

        .sheet th {
            padding: 4px 3px;
            font-size: 8.7px;
            line-height: 1.18;
        }

        .sheet thead tr:first-child th {
            background: #e7eaee;
        }

        .sheet thead tr:last-child th {
            background: #f3f4f6;
            font-size: 8.2px;
        }

        .sheet td {
            padding: 3px 3px;
            font-size: 8.7px;
            line-height: 1.22;
        }

        .sheet td:nth-child(7),
        .sheet td:nth-child(10),
        .sheet td:nth-child(11) {
            font-weight: bold;
        }

        .total-row td {
            padding-top: 4px;
            padding-bottom: 4px;
        }

        .signature-wrap {
            margin-top: 16px;
        }

        .signature-label {
            color: #475569;
        }

        .km-note-box {
            padding-top: 7px;
            border-top: 1px solid #cfd5dc;
            color: #586474;
            font-size: 7.8px;
            line-height: 1.45;
        }

        .km-note-title {
            color: #374151;
        }


        /* ================================================
           FASE 2G3 — MODERN REPORT PDF
           ================================================ */

        body {
            color: #25343a;
            font-size: 9.4px;
        }

        .title {
            margin: 0 0 10px 0;
            padding: 8px 10px;

            background: #183840;
            color: #ffffff;

            text-align: left;

            font-size: 14px;
            font-weight: bold;
            letter-spacing: .3px;
        }

        .head {
            margin-bottom: 10px;

            background: #f7f9fa;

            border: 1px solid #84939a;
        }

        .head td {
            padding: 7px 9px;
        }

        .brand-box {
            background: #ffffff;
        }

        .brand-text {
            color: #183840;

            font-size: 20px;
            font-weight: bold;
        }

        .meta-table td {
            padding: 2px 4px;

            color: #25343a;

            font-size: 9px;
        }

        .meta-label {
            color: #53616b;
            font-weight: bold;
        }

        .sheet {
            border: 1px solid #263b43;
        }

        .sheet th,
        .sheet td {
            border-color: #7d8b92;
        }

        .sheet thead tr:first-child th {
            padding: 5px 3px;

            background: #183840;
            color: #ffffff;

            font-size: 8.3px;
            font-weight: bold;
        }

        .sheet thead tr:last-child th {
            padding: 4px 3px;

            background: #dfe7e9;
            color: #183840;

            font-size: 7.8px;
            font-weight: bold;
        }

        .sheet td {
            padding: 2.8px 3px;

            color: #25343a;

            font-size: 8.2px;
            line-height: 1.20;
        }

        .sheet-row-saturday td,
        .sheet-row-sunday td {
            background: #fff3c4;
        }

        .sheet-row-holiday td {
            background: #dceba8;
        }

        .sheet td:nth-child(7),
        .sheet td:nth-child(10),
        .sheet td:nth-child(11) {
            color: #183840;
            font-weight: bold;
        }

        .total-row td {
            padding-top: 5px;
            padding-bottom: 5px;

            background: #183840;
            color: #ffffff;

            border-color: #405961;
            font-weight: bold;
        }

        .signature-wrap {
            margin-top: 16px;
        }

        .signature-box {
            width: 35%;
            padding-top: 18px;

            vertical-align: bottom;
        }

        .signature-label {
            color: #53616b;
        }

        .km-note-box {
            width: 65%;

            padding: 7px 9px;

            background: #f4f7f8;

            border: 1px solid #d5dde0;
            border-left: 3px solid #477c88;

            color: #58666d;

            font-size: 7.4px;
            line-height: 1.35;
            text-align: left;
            vertical-align: bottom;
        }

        .km-note-title {
            color: #183840;
            font-weight: bold;
        }

        .footer-sign {
            color: #89949a;
            font-size: 7px;
        }


        /* =====================================================
           FASE 2G5 — acabamento premium PDF
           ===================================================== */

        .title {
            padding-left: 14px;
            padding-right: 14px;

            letter-spacing: .55px;
        }

        /* CABEÇALHO */

        .head td {
            padding:
                8px
                10px;
        }

        .brand-box {
            padding-left: 12px;
            padding-right: 12px;
        }

        .meta-table td {
            border: none;

            padding:
                2.5px
                4px;
        }

        /* GRELHA INTERNA MAIS LEVE */

        .sheet {
            border:
                1.3px
                solid
                #183840;
        }

        .sheet th,
        .sheet td {
            border-color: #cfd7db;
        }

        .sheet thead tr:first-child th {
            border-color: #50656d;
        }

        .sheet thead tr:last-child th {
            border-color: #aebbc0;
        }

        .sheet tbody td:nth-child(2),
        .sheet tbody td:nth-child(6),
        .sheet tbody td:nth-child(7),
        .sheet tbody td:nth-child(8),
        .sheet tbody td:nth-child(9),
        .sheet tbody td:nth-child(10) {
            border-right-color: #8f9da3;
        }

        /* PROPORÇÕES */

        .sheet thead tr:first-child th:nth-child(1) {
            width: 6.5% !important;
        }

        .sheet thead tr:first-child th:nth-child(2) {
            width: 8.5% !important;
        }

        .sheet thead tr:first-child th:nth-child(3) {
            width: 24% !important;
        }

        .sheet thead tr:first-child th:nth-child(4) {
            width: 8% !important;
        }

        .sheet thead tr:first-child th:nth-child(5) {
            width: 9.5% !important;
        }

        .sheet thead tr:first-child th:nth-child(6) {
            width: 31% !important;
        }

        .sheet thead tr:first-child th:nth-child(7) {
            width: 6% !important;
        }

        .sheet thead tr:first-child th:nth-child(8) {
            width: 6.5% !important;
        }

        /* DADOS */

        .sheet td {
            font-size: 8.8px;
        }

        .sheet tbody td:nth-child(3),
        .sheet tbody td:nth-child(4),
        .sheet tbody td:nth-child(5),
        .sheet tbody td:nth-child(6) {
            font-size: 8.9px;
        }

        .sheet tbody td:nth-child(7) {
            color: #172f37;

            font-size: 9px;
            font-weight: bold;
        }

        .sheet tbody td:nth-child(8) {
            font-size: 8.6px;
            font-weight: normal;
            line-height: 1.28;
        }

        .sheet tbody td:nth-child(9) {
            font-size: 8.7px;
            font-weight: normal;
            line-height: 1.34;
        }

        .sheet tbody td:nth-child(10) {
            color: #172f37;

            font-size: 9px;
            font-weight: bold;
            text-align: center;
        }

        .sheet tbody td:nth-child(11) {
            padding-right: 4px;

            font-weight: normal;
            text-align: right;
        }

        /* SOMENTE LINHAS COM CONTEÚDO */

        .sheet tbody
        tr.sheet-row-content
        td {
            padding-top: 3.2px;
            padding-bottom: 3.2px;
        }

        /* CORES ESPECIAIS */

        .sheet-row-saturday td,
        .sheet-row-sunday td {
            background: #fff8df;
        }

        .sheet-row-holiday td {
            background: #edf6df;

            color: #263a31;
        }

        /* TOTAL */

        .total-row td {
            border-color: #50666e;
        }

        .total-row td:nth-child(2),
        .total-row td:nth-child(4) {
            font-size: 9px;
            text-align: center;
        }

        /* RODAPÉ */

        .signature-wrap {
            margin-top: 18px;
        }

        .signature-box {
            width: 38%;

            padding-top: 20px;
        }

        .signature-line {
            width: 250px;
        }

        .signature-label {
            width: 250px;

            color: #4f6068;

            font-size: 8px;
        }

        .km-note-box {
            width: 62%;

            padding:
                8px
                10px;

            background: #f6f8f9;

            border-color: #d3dcdf;
            border-left:
                3px
                solid
                #477c88;

            color: #4f5f67;

            font-size: 8.2px;
            line-height: 1.42;
            text-align: left;
        }

        .km-note-title {
            color: #183840;
        }


        /* =====================================================
           FASE 2G7 — EMPTY ROWS
           somente apresentação de dias sem expediente
           ===================================================== */

        .sheet tbody
        tr.sheet-row-compact
        td {
            padding-top: 1.8px;
            padding-bottom: 1.8px;

            line-height: 1.10;
        }

        .sheet tbody
        tr.sheet-row-compact
        .sheet-compact-span {
            text-align: center;
            vertical-align: middle;

            border-left-color: #8f9da3;
            border-right-color: #8f9da3;
        }

        .sheet tbody
        tr.sheet-row-compact:not(.sheet-row-saturday):not(.sheet-row-sunday):not(.sheet-row-holiday)
        .sheet-compact-span {
            background: #fbfcfd;
        }

        .sheet tbody
        tr.sheet-row-compact-special
        .sheet-compact-span {
            color: #40524a;

            font-size: 8.5px;
            font-weight: bold;
            line-height: 1.12;
        }


        /* =====================================================
           FASE 2G8 — FINAL REPORT
           estados das linhas + cabeçalho final
           ===================================================== */


        /* =====================================================
           CABEÇALHO
           ===================================================== */

        .head {
            background: #f7f9fa;

            border:
                1px
                solid
                #cfd8dc;
        }


        /*
         * Retira subdivisões internas do painel.
         * A borda externa da .head permanece.
         */
        .head td {
            border:
                0
                !important;
        }


        .brand-box {
            padding-left: 15px;
            padding-right: 18px;

            background:
                #f7f9fa
                !important;

            border-right:
                0
                !important;
        }


        .brand-logo {
            max-height: 62px;
        }


        .brand-text {
            color: #183840;

            font-size: 22px;
            font-weight: bold;
        }


        .meta-table {
            width: 100%;

            table-layout: fixed;
        }


        .meta-table td {
            border:
                0
                !important;

            color: #183840;

            font-size: 8.7px;
            line-height: 1.20;
        }


        .meta-label {
            color: #63737a;

            font-size: 7.9px;
            font-weight: bold;
        }


        /* =====================================================
           LINHAS COMPACTAS
           ===================================================== */

        .sheet tbody
        tr.sheet-row-compact
        td {
            padding-top: 1.5px;
            padding-bottom: 1.5px;

            border-top-color: #d5dde0;
            border-bottom-color: #d5dde0;

            line-height: 1.08;
        }


        .sheet tbody
        tr.sheet-row-compact
        .sheet-compact-span {
            text-align: center;
            vertical-align: middle;

            border-left-color: #96a4aa;
            border-right-color: #96a4aa;
        }


        /*
         * Dia útil vazio.
         */
        .sheet tbody
        tr.sheet-row-compact:not(.sheet-row-saturday):not(.sheet-row-sunday):not(.sheet-row-holiday):not(.sheet-row-compact-special)
        .sheet-compact-span {
            background: #f5f7f8;
        }


        .sheet-compact-dash {
            color: #98a3a8;

            font-size: 8.2px;
            font-weight: normal;
        }


        /*
         * Fim de semana:
         * faixa creme contínua.
         */
        .sheet tbody
        tr.sheet-row-compact.sheet-row-saturday
        td,

        .sheet tbody
        tr.sheet-row-compact.sheet-row-sunday
        td {
            background: #fff9e8;
        }


        /*
         * Feriado / ausência / especial.
         */
        .sheet tbody
        tr.sheet-row-compact-special
        .sheet-compact-span {
            background: #eef7e6;

            color: #405448;

            text-align: center;
        }


        .sheet tbody
        tr.sheet-row-compact-special
        .sheet-compact-label {
            color: #405448;

            font-size: 8.3px;
            font-weight: bold;

            line-height: 1.10;
        }


        /* =====================================================
           FASE 2G9 — FINAL POLISH
           ===================================================== */


        /* =====================================================
           1. ESTADOS
           ===================================================== */

        .sheet tbody
        tr.sheet-row-compact:not(.sheet-row-saturday):not(.sheet-row-sunday):not(.sheet-row-holiday):not(.sheet-row-compact-special)
        td {
            background: #EEF2F3;
        }


        .sheet tbody
        tr.sheet-row-compact.sheet-row-saturday
        td,

        .sheet tbody
        tr.sheet-row-compact.sheet-row-sunday
        td {
            background: #F6E8B6;
        }


        .sheet tbody
        tr.sheet-row-compact-special
        td {
            background: #D8E9C5;
        }


        .sheet tbody
        tr.sheet-row-compact-special
        .sheet-compact-span {
            background: #D8E9C5;
        }


        /* =====================================================
           2. TRAÇO
           ===================================================== */

        .sheet-compact-dash {
            color: #737F85;

            font-size: 8.5px;
            font-weight: bold;
        }


        /* =====================================================
           3. CABEÇALHO
           ===================================================== */

        /*
         * Levemente menos espaço ocupado
         * pelo bloco da marca.
         */
        .brand-box {
            width: 22%;

            padding-left: 14px;
            padding-right: 8px;
        }


        /*
         * Valores um pouco mais presentes.
         */
        .meta-table td {
            color: #173840;

            font-size: 9.2px;
            line-height: 1.22;
        }


        /*
         * Rótulos permanecem secundários.
         */
        .meta-label {
            color: #68777E;

            font-size: 8px;
            font-weight: bold;
        }


        /* =====================================================
           4. RODAPÉ
           ===================================================== */

        /*
         * Sobe assinatura e nota.
         */
        .signature-wrap {
            margin-top: 12px;
        }


        .signature-box {
            padding-top: 14px;
        }


        /*
         * Nota ganha legibilidade sem virar card.
         */
        .km-note-box {
            padding:
                8px
                10px;

            font-size: 8.5px;
            line-height: 1.38;
        }


        .km-note-title {
            color: #173840;

            font-weight: bold;
        }


        /*
         * Nenhuma regra desta fase altera
         * o corpo dos dias trabalhados.
         */


        /* =====================================================
           FASE 2G10 — HEADER + PAGING
           ===================================================== */


        /* =====================================================
           CABEÇALHO
           ===================================================== */

        /*
         * Um pouco mais de largura para os dados
         * e um pouco menos para a área da marca.
         */

        .brand-box {
            width: 20.5%;

            padding-left: 13px;
            padding-right: 7px;
        }


        .brand-text {
            font-size: 21px;
        }


        /*
         * Metadados.
         *
         * A tabela interna passa a usar
         * proporções previsíveis:
         *
         * label | valor | label | valor
         */

        .meta-table {
            width: 100%;

            table-layout: fixed;
        }


        .meta-table td {
            padding:
                2.2px
                3px;

            color: #173840;

            font-size: 9.9px;
            font-weight: normal;

            line-height: 1.23;
        }


        /*
         * Rótulos: menores que os valores,
         * mas maiores do que na versão anterior.
         */

        .meta-label {
            color: #617178;

            font-size: 8.6px;
            font-weight: bold;
        }


        /*
         * Os dois blocos passam a seguir
         * a mesma geometria.
         */

        .meta-table tr td:nth-child(1),
        .meta-table tr td:nth-child(3) {
            width: 17%;
        }


        .meta-table tr td:nth-child(2),
        .meta-table tr td:nth-child(4) {
            width: 33%;
        }


        /* =====================================================
           RODAPÉ DE ASSINATURA / NOTA
           ===================================================== */

        .signature-wrap {
            margin-top: 10px;
        }


        .signature-box {
            padding-top: 12px;
        }


        .km-note-box {
            font-size: 8.6px;
            line-height: 1.36;
        }


        .km-note-title {
            color: #173840;

            font-weight: bold;
        }


        /* =====================================================
           PAGINAÇÃO TÉCNICA
           ===================================================== */

        /*
         * Dompdf:
         * contador da página atual.
         */

        body {
            counter-reset: page 0;
            counter-increment: page 1;
        }


        .report-page-number-2g10 {
            position: fixed;

            left: 0;
            right: 0;

            bottom: -7px;

            text-align: center;

            color: #8B979C;

            font-family: DejaVu Sans, sans-serif;
            font-size: 6.8px;
            font-weight: normal;

            letter-spacing: .08px;
        }


        .report-page-number-2g10::before {
            content:
                "AlexDevCode  ·  Página "
                counter(page);
        }


        /*
         * Nenhuma regra da 2G10 modifica
         * dias trabalhados ou linhas vazias.
         */


        /* =====================================================
           FASE 2G11 — MICRO FINAL
           ===================================================== */


        /*
         * VALORES DO CABEÇALHO
         *
         * 9.9px -> 10.5px
         * aproximadamente +6%.
         *
         * .meta-label NÃO é alterado.
         */

        .meta-table td {
            font-size: 10.5px;

            color: #173840;

            font-weight: normal;
        }


        /* =====================================================
           ASSINATURA + NOTA
           ===================================================== */

        .signature-wrap {
            margin-top: 8px;
        }


        .signature-box,
        .km-note-box {
            vertical-align: middle;
        }


        .signature-box {
            padding-top: 0;
        }


        /*
         * Desativa o contador CSS da 2G10.
         *
         * A paginação real será desenhada
         * pelo Canvas do Dompdf no controller.
         */

        .report-page-number-2g10 {
            display: none !important;
        }


        /*
         * Tabela, estados, totais e cores:
         * SEM ALTERAÇÃO.
         */

    </style>
</head>
<body>
    <div
        class="report-page-number-2g10"
        aria-hidden="true"
    ></div>
    <div class="title">FOLHA MENSAL DE REGISTO DE HORAS</div>

    <table class="head">
        <tr>
            <td class="brand-box">
                <?php if ($logoFsPath): ?>
                    <img src="<?= timeclock_pdf_h($logoFsPath) ?>" alt="Logo" class="brand-logo">
                <?php else: ?>
                    <div class="brand-text"><?= timeclock_pdf_h($companyName) ?></div>
                <?php endif; ?>
            </td>
            <td style="width:38%;">
                <table class="meta-table">
                    <tr>
                        <td class="meta-label">Trabalhador(a):</td>
                        <td><?= timeclock_pdf_h($auditorName) ?></td>
                    </tr>
                    <tr>
                        <td class="meta-label">Mês / Ano:</td>
                        <td><?= timeclock_pdf_h(($report['month_label'] ?? '-') . '_' . substr((string) ($report['year'] ?? ''), -2)) ?></td>
                    </tr>
                    <tr>
                        <td class="meta-label">Hora Semanal:</td>
                        <td><?= timeclock_pdf_h($weeklyHours) ?></td>
                    </tr>
                    <tr>
                        <td class="meta-label">Horário de Trabalho:</td>
                        <td><?= timeclock_pdf_h($workSchedule) ?></td>
                    </tr>
                </table>
            </td>
            <td style="width:38%;">
                <table class="meta-table">
                    <tr>
                        <td class="meta-label">Local:</td>
                        <td><?= timeclock_pdf_h($workLocation) ?></td>
                    </tr>
                    <tr>
                        <td class="meta-label">Matrícula Viatura:</td>
                        <td><?= timeclock_pdf_h($vehiclePlate) ?></td>
                    </tr>
                    <tr>
                        <td class="meta-label">Nº Emp.:</td>
                        <td><?= timeclock_pdf_h($employeeNumber) ?></td>
                    </tr>
                    <tr>
                        <td class="meta-label">Cat. Prof.:</td>
                        <td><?= timeclock_pdf_h($professionalCategory) ?></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table class="sheet">
        <thead>
            <tr>
                <th rowspan="2" style="width:7%;">DATA</th>
                <th rowspan="2" style="width:9%;">DIA SEMANA</th>
                <th colspan="4" style="width:24%;">HORAS TRABALHO</th>
                <th rowspan="2" style="width:8%;">TOTAL HORAS</th>
                <th rowspan="2" style="width:11%;">OBSERVAÇÕES</th>
                <th rowspan="2" style="width:26%;">Lojas visitadas por sequência</th>
                <th rowspan="2" style="width:7%;">KMS*</th>
                <th rowspan="2" style="width:6%;">Valor</th>
            </tr>
            <tr>
                <th>ENTRADA</th>
                <th>SAÍDA</th>
                <th>ENTRADA</th>
                <th>SAÍDA</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach (($report['rows'] ?? []) as $row): ?>
                <?php
                    $rowClass = trim(
                        timeclock_pdf_text(
                            $row['row_class'] ?? ''
                        )
                    );

                    $dateLabel =
                        timeclock_pdf_text(
                            $row['date_label'] ?? ''
                        );

                    if (
                        $dateLabel === '' &&
                        !empty($row['date_iso'])
                    ) {
                        $timestamp =
                            strtotime(
                                timeclock_pdf_text(
                                    $row['date_iso']
                                )
                            );

                        $dateLabel =
                            $timestamp !== false
                                ? date('d-M', $timestamp)
                                : timeclock_pdf_text(
                                    $row['date_iso']
                                );
                    }

                    $observations =
                        trim(
                            timeclock_pdf_text(
                                $row['notes'] ?? ''
                            )
                        );

                    if (!empty($row['holiday_name'])) {
                        $holidayName =
                            timeclock_pdf_text(
                                $row['holiday_name']
                            );

                        $observations =
                            trim(
                                (
                                    $observations !== ''
                                        ? $observations . ' | '
                                        : ''
                                ) .
                                $holidayName,
                                ' |'
                            );
                    }

                    /*
                     * 2G7:
                     * células completas somente quando
                     * há dados efetivos do expediente.
                     */
                    $hasWorkData =
                        trim(timeclock_pdf_text($row['entry1'] ?? '')) !== '' ||
                        trim(timeclock_pdf_text($row['exit1'] ?? '')) !== '' ||
                        trim(timeclock_pdf_text($row['entry2'] ?? '')) !== '' ||
                        trim(timeclock_pdf_text($row['exit2'] ?? '')) !== '' ||
                        trim(timeclock_pdf_text($row['stores'] ?? '')) !== '' ||
                        trim(timeclock_pdf_text($row['kms'] ?? '')) !== '' ||
                        trim(timeclock_pdf_text($row['value'] ?? '')) !== '';

                    $finalRowClass =
                        trim(
                            $rowClass .
                            (
                                $hasWorkData
                                    ? ' sheet-row-content'
                                    : ' sheet-row-compact'
                            ) .
                            (
                                !$hasWorkData &&
                                $observations !== ''
                                    ? ' sheet-row-compact-special'
                                    : ''
                            )
                        );
                ?>

                <tr class="<?= timeclock_pdf_h($finalRowClass) ?>">
                    <td>
                        <?= timeclock_pdf_h($dateLabel) ?>
                    </td>

                    <td>
                        <?= timeclock_pdf_h(
                            $row['weekday'] ?? ''
                        ) ?>
                    </td>

                    <?php if ($hasWorkData): ?>

                        <td>
                            <?= timeclock_pdf_h(
                                $row['entry1'] ?? ''
                            ) ?>
                        </td>

                        <td>
                            <?= timeclock_pdf_h(
                                $row['exit1'] ?? ''
                            ) ?>
                        </td>

                        <td>
                            <?= timeclock_pdf_h(
                                $row['entry2'] ?? ''
                            ) ?>
                        </td>

                        <td>
                            <?= timeclock_pdf_h(
                                $row['exit2'] ?? ''
                            ) ?>
                        </td>

                        <td>
                            <?php
                                /* REPORT_HHMM_2G12 */
                                $dayMinutes =
                                    max(
                                        0,
                                        (int) (
                                            $row['total_minutes']
                                            ?? 0
                                        )
                                    );

                                $dayHoursDisplay =
                                    sprintf(
                                        '%d:%02d',
                                        intdiv(
                                            $dayMinutes,
                                            60
                                        ),
                                        $dayMinutes % 60
                                    );
                            ?>

                            <?= timeclock_pdf_h(
                                $dayHoursDisplay
                            ) ?>
                        </td>

                        <td class="left small">
                            <?= timeclock_pdf_h($observations) ?>
                        </td>

                        <td class="left small">
                            <?= timeclock_pdf_h(
                                $row['stores'] ?? ''
                            ) ?>
                        </td>

                        <td>
                            <?= timeclock_pdf_h(
                                $row['kms'] ?? ''
                            ) ?>
                        </td>

                        <td>
                            <?= timeclock_pdf_h(
                                $row['value'] ?? ''
                            ) ?>
                        </td>

                    <?php else: ?>

                        <td
                            colspan="9"
                            class="sheet-compact-span"
                        >
                            <?php if ($observations !== ''): ?>

                                <span class="sheet-compact-label">
                                    <?= timeclock_pdf_h(
                                        $observations
                                    ) ?>
                                </span>

                            <?php elseif (
                                !str_contains(
                                    $rowClass,
                                    'sheet-row-saturday'
                                ) &&
                                !str_contains(
                                    $rowClass,
                                    'sheet-row-sunday'
                                )
                            ): ?>

                                <span class="sheet-compact-dash">
                                    —
                                </span>

                            <?php endif; ?>
                        </td>

                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>

            <?php
                $totalKmValue = 0.0;

                foreach (
                    ($report['rows'] ?? [])
                    as $totalKmRow
                ) {
                    $kmText = trim(
                        timeclock_pdf_text(
                            $totalKmRow['kms'] ??
                            ''
                        )
                    );

                    if ($kmText === '') {
                        continue;
                    }

                    $kmText = str_replace(
                        ["\xc2\xa0", ' '],
                        '',
                        $kmText
                    );

                    if (
                        str_contains($kmText, ',') &&
                        str_contains($kmText, '.')
                    ) {
                        $kmText = str_replace(
                            '.',
                            '',
                            $kmText
                        );
                    }

                    $kmText = str_replace(
                        ',',
                        '.',
                        $kmText
                    );

                    if (is_numeric($kmText)) {
                        $totalKmValue +=
                            (float) $kmText;
                    }
                }

                $totalKmDisplay =
                    number_format(
                        $totalKmValue,
                        1,
                        ',',
                        ''
                    );
            ?>

            <tr class="total-row">
                <td colspan="6" class="left">
                    TOTAL DO MÊS
                </td>

                <td>
                    <?php
                        /* REPORT_HHMM_2G12_MONTH */
                        $monthlyMinutes =
                            max(
                                0,
                                (int) (
                                    $report[
                                        'monthly_total_minutes'
                                    ] ?? 0
                                )
                            );

                        $monthlyHoursDisplay =
                            sprintf(
                                '%d:%02d',
                                intdiv(
                                    $monthlyMinutes,
                                    60
                                ),
                                $monthlyMinutes % 60
                            );
                    ?>

                    <?= timeclock_pdf_h(
                        $monthlyHoursDisplay
                    ) ?>
                </td>

                <td colspan="2" class="left">
                    TOTAL KMS
                </td>

                <td>
                    <?= timeclock_pdf_h(
                        $totalKmDisplay
                    ) ?>
                </td>

                <td></td>
            </tr>
        </tbody>
    </table>

    <table class="signature-wrap">
        <tr>
            <td class="signature-box">
                <?php if ($signatureFsPath): ?>
                    <img src="<?= timeclock_pdf_h($signatureFsPath) ?>" alt="Assinatura" class="signature-image">
                <?php endif; ?>
                <div class="signature-line"></div>
                <div class="signature-label">Assinatura do(a) Trabalhador(a)</div>
            </td>
            <td class="km-note-box">
                <span class="km-note-title">Nota sobre KMS:</span>
                Os valores de KMS são calculados automaticamente
                com base na origem e nas lojas registadas no dia.
                Podem existir pequenas variações face ao percurso
                efetivamente realizado, devido a alterações de
                trajeto, condições da via ou precisão dos endereços.
            </td>
        </tr>
    </table>

</body>
</html>