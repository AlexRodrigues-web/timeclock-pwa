<?php
$title = 'Relatórios';
$bodyClass = 'app-body';
$headerSubtitle = 'Folha mensal e PDF';

$hourlyRate = 8.00;
$monthlyTotalMinutes = max(0, (int) ($report['monthly_total_minutes'] ?? 0));
$monthlyTotalHoursNumber = $monthlyTotalMinutes / 60;
$monthlyTotalAmount = $monthlyTotalHoursNumber * $hourlyRate;

$formatWorkedMinutes = static function (int $minutes): string {
    $minutes = max(0, $minutes);
    $hours = intdiv($minutes, 60);
    $remainingMinutes = $minutes % 60;

    return sprintf('%dh %02dmin', $hours, $remainingMinutes);
};

$monthNames = [
    1 => 'Janeiro',
    2 => 'Fevereiro',
    3 => 'Março',
    4 => 'Abril',
    5 => 'Maio',
    6 => 'Junho',
    7 => 'Julho',
    8 => 'Agosto',
    9 => 'Setembro',
    10 => 'Outubro',
    11 => 'Novembro',
    12 => 'Dezembro',
];

$monthShortNames = [
    1 => 'jan.',
    2 => 'fev.',
    3 => 'mar.',
    4 => 'abr.',
    5 => 'mai.',
    6 => 'jun.',
    7 => 'jul.',
    8 => 'ago.',
    9 => 'set.',
    10 => 'out.',
    11 => 'nov.',
    12 => 'dez.',
];

$todayIso = date('Y-m-d');
$consultDate =
    isset($consultDate) &&
    is_string($consultDate) &&
    preg_match('/^\d{4}-\d{2}-\d{2}$/', $consultDate)
        ? $consultDate
        : $todayIso;

$consultTimestamp = strtotime($consultDate);
if ($consultTimestamp === false) {
    $consultDate = $todayIso;
    $consultTimestamp = strtotime($consultDate);
}

$consultTotalMinutes = max(
    0,
    (int) ($consultDay['total_minutes'] ?? 0)
);

$consultHoursDisplay = $formatWorkedMinutes($consultTotalMinutes);
$monthlyWorkedDisplay = $formatWorkedMinutes($monthlyTotalMinutes);
$isConsultToday = $consultDate === $todayIso;

$consultDayNumber = (int) date('j', $consultTimestamp);
$consultDayMonth = (int) date('n', $consultTimestamp);
$consultDayYear = (int) date('Y', $consultTimestamp);
$consultDateDisplay = sprintf(
    '%d %s %d',
    $consultDayNumber,
    $monthShortNames[$consultDayMonth] ?? '',
    $consultDayYear
);

$reportMonthForUrl = max(1, min(12, (int) $month));
$reportYearForUrl = max(2020, min(2100, (int) $year));
$monthlyPeriodDisplay =
    ($monthNames[$reportMonthForUrl] ?? 'Mês') .
    ' ' .
    $reportYearForUrl;

$buildConsultDayUrl = static function (string $date) use (
    $reportMonthForUrl,
    $reportYearForUrl
): string {
    return
        '/auditor-app/public/export/pdf?' .
        http_build_query([
            'month' => $reportMonthForUrl,
            'year' => $reportYearForUrl,
            'day' => $date,
        ]);
};

$buildReportPeriodUrl = static function (int $targetMonth, int $targetYear): string {
    return
        '/auditor-app/public/export/pdf?' .
        http_build_query([
            'month' => $targetMonth,
            'year' => $targetYear,
        ]);
};

$periodTimestamp = strtotime(
    sprintf('%04d-%02d-01', $reportYearForUrl, $reportMonthForUrl)
);
$previousMonthTimestamp = strtotime('-1 month', $periodTimestamp);
$nextMonthTimestamp = strtotime('+1 month', $periodTimestamp);

$previousMonthUrl = $buildReportPeriodUrl(
    (int) date('n', $previousMonthTimestamp),
    (int) date('Y', $previousMonthTimestamp)
);
$nextMonthUrl = $buildReportPeriodUrl(
    (int) date('n', $nextMonthTimestamp),
    (int) date('Y', $nextMonthTimestamp)
);

$previousConsultDate = date(
    'Y-m-d',
    strtotime($consultDate . ' -1 day')
);
$nextConsultDateCandidate = date(
    'Y-m-d',
    strtotime($consultDate . ' +1 day')
);
$canGoNextConsultDay = $nextConsultDateCandidate <= $todayIso;
$nextConsultDate = $canGoNextConsultDay
    ? $nextConsultDateCandidate
    : $todayIso;

$hourlyRateDisplay =
    number_format($hourlyRate, 2, ',', '.') . ' €';
$monthlyTotalAmountDisplay =
    number_format($monthlyTotalAmount, 2, ',', '.') . ' €';

$auditorDisplayName = trim((string) (
    $settings['auditor_name'] ??
    $user['name'] ??
    ''
));

require BASE_PATH . '/app/views/partials/layout_start.php';
?>

<style id="timeclock-report-redesign-v1">
    .tc-report-page,
    .tc-report-page * {
        box-sizing: border-box;
    }

    .tc-report-page {
        width: min(100%, 920px);
        margin: 0 auto;
        padding: 22px 18px 34px;
        color: #0f2344;
    }

    .tc-report-heading {
        margin-bottom: 22px;
    }

    .tc-report-heading h1 {
        margin: 0;
        font-size: clamp(26px, 4vw, 34px);
        line-height: 1.05;
        letter-spacing: -0.025em;
        font-weight: 900;
        color: #0d2141;
    }

    .tc-report-heading p {
        margin: 8px 0 0;
        font-size: clamp(15px, 2vw, 18px);
        line-height: 1.25;
        color: #64748b;
        font-weight: 600;
    }

    .tc-report-form {
        display: grid;
        gap: 18px;
    }

    .tc-card {
        background: #ffffff;
        border: 1px solid #dbe3ed;
        border-radius: 18px;
        box-shadow: 0 8px 28px rgba(15, 35, 68, 0.045);
    }

    .tc-period-card {
        position: relative;
        display: grid;
        grid-template-columns: 54px minmax(0, 1fr) 54px;
        align-items: center;
        min-height: 82px;
        padding: 0 12px;
    }

    .tc-icon-link,
    .tc-icon-button {
        width: 44px;
        height: 44px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 0;
        border-radius: 12px;
        background: transparent;
        color: #0f2344;
        text-decoration: none;
        cursor: pointer;
        transition: background 0.18s ease, transform 0.18s ease;
    }

    .tc-icon-link:hover,
    .tc-icon-button:hover {
        background: #f3f6fa;
    }

    .tc-icon-link:active,
    .tc-icon-button:active {
        transform: scale(0.96);
    }

    .tc-icon-link svg,
    .tc-icon-button svg,
    .tc-section-icon svg,
    .tc-calendar-icon svg,
    .tc-primary-action svg {
        width: 24px;
        height: 24px;
        stroke: currentColor;
        fill: none;
        stroke-width: 2;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .tc-period-picker {
        min-width: 0;
        text-align: center;
    }

    .tc-period-picker > summary {
        list-style: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 13px;
        min-height: 54px;
        padding: 8px 14px;
        border-radius: 14px;
        color: #0f2344;
        font-size: clamp(18px, 2.5vw, 23px);
        font-weight: 750;
        cursor: pointer;
        user-select: none;
    }

    .tc-period-picker > summary::-webkit-details-marker {
        display: none;
    }

    .tc-period-picker > summary:hover {
        background: #f7f9fc;
    }

    .tc-period-picker .tc-calendar-icon {
        color: #52637c;
        display: inline-flex;
    }

    .tc-period-chevron {
        width: 10px;
        height: 10px;
        border-right: 2px solid currentColor;
        border-bottom: 2px solid currentColor;
        transform: rotate(45deg) translateY(-3px);
        transition: transform 0.18s ease;
    }

    .tc-period-picker[open] .tc-period-chevron {
        transform: rotate(225deg) translate(-2px, -1px);
    }

    .tc-period-popover {
        position: absolute;
        z-index: 30;
        left: 50%;
        top: calc(100% - 6px);
        transform: translateX(-50%);
        width: min(420px, calc(100% - 24px));
        display: grid;
        grid-template-columns: minmax(0, 1fr) 118px auto;
        gap: 10px;
        padding: 14px;
        background: #ffffff;
        border: 1px solid #dbe3ed;
        border-radius: 16px;
        box-shadow: 0 18px 42px rgba(15, 35, 68, 0.16);
    }

    .tc-period-popover select,
    .tc-period-popover input {
        width: 100%;
        min-height: 44px;
        border: 1px solid #d4deea;
        border-radius: 11px;
        background: #ffffff;
        color: #0f2344;
        padding: 0 12px;
        font: inherit;
        font-weight: 700;
        outline: none;
    }

    .tc-period-popover select:focus,
    .tc-period-popover input:focus {
        border-color: #567da7;
        box-shadow: 0 0 0 3px rgba(86, 125, 167, 0.12);
    }

    .tc-apply-period {
        min-height: 44px;
        padding: 0 15px;
        border: 1px solid #17477d;
        border-radius: 11px;
        background: #17477d;
        color: #ffffff;
        font-weight: 800;
        cursor: pointer;
    }

    .tc-summary-card {
        padding: 27px 28px 22px;
    }

    .tc-summary-label {
        display: block;
        margin-bottom: 8px;
        color: #52637c;
        font-size: 15px;
        font-weight: 650;
    }

    .tc-summary-hours {
        display: block;
        color: #0d2141;
        font-size: clamp(34px, 5.8vw, 46px);
        line-height: 1;
        letter-spacing: -0.025em;
        font-weight: 900;
    }

    .tc-summary-divider {
        height: 1px;
        margin: 24px 0 19px;
        background: #dce4ec;
    }

    .tc-rate-title {
        margin-bottom: 12px;
        color: #52637c;
        font-size: 14px;
        font-weight: 650;
    }

    .tc-rate-options {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
    }

    .tc-rate-btn {
        min-height: 56px;
        padding: 8px 10px;
        border: 1px solid #d5dfeb;
        border-radius: 11px;
        background: #ffffff;
        color: #0d2141;
        font: inherit;
        font-size: 15px;
        font-weight: 750;
        cursor: pointer;
        transition: 0.18s ease;
    }

    .tc-rate-btn:hover {
        border-color: #8fa9c5;
        background: #f7f9fc;
    }

    .tc-rate-btn.is-active {
        border-color: #17477d;
        background: linear-gradient(135deg, #123761, #174d83);
        color: #ffffff;
        box-shadow: 0 7px 18px rgba(23, 71, 125, 0.2);
    }

    .tc-total-row {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto auto;
        align-items: center;
        gap: 14px;
        margin-top: 20px;
        padding-top: 18px;
        border-top: 1px solid #dce4ec;
    }

    .tc-total-label {
        color: #66758a;
        font-size: 15px;
        font-weight: 650;
    }

    .tc-total-value {
        color: #233a5d;
        font-size: clamp(18px, 3vw, 22px);
        font-weight: 750;
        font-variant-numeric: tabular-nums;
    }

    .tc-total-value.is-hidden {
        letter-spacing: 0.1em;
    }

    .tc-eye-btn {
        border: 1px solid #d6e0ec;
        background: #ffffff;
        color: #52637c;
    }

    .tc-eye-btn [data-eye-open],
    .tc-eye-btn [data-eye-closed] {
        display: block;
    }

    .tc-eye-btn [hidden] {
        display: none !important;
    }

    .tc-section-card {
        padding: 22px 28px;
    }

    .tc-section-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 16px;
    }

    .tc-section-title-wrap {
        display: inline-flex;
        align-items: center;
        gap: 12px;
        min-width: 0;
    }

    .tc-section-icon {
        display: inline-flex;
        color: #11386b;
        flex: 0 0 auto;
    }

    .tc-section-title {
        margin: 0;
        color: #0d2141;
        font-size: clamp(19px, 3vw, 23px);
        line-height: 1.15;
        font-weight: 900;
    }

    .tc-today-link {
        min-height: 42px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0 17px;
        border: 1px solid #6d91bc;
        border-radius: 11px;
        color: #325c8d;
        background: #ffffff;
        text-decoration: none;
        font-size: 14px;
        font-weight: 750;
        white-space: nowrap;
    }

    .tc-day-nav {
        display: grid;
        grid-template-columns: 52px minmax(0, 1fr) 52px;
        align-items: center;
        min-height: 72px;
        border: 1px solid #d8e2ec;
        border-radius: 13px;
        background: #ffffff;
    }

    .tc-day-nav .tc-icon-link,
    .tc-day-nav .tc-icon-button {
        margin: 0 auto;
    }

    .tc-day-nav .is-disabled {
        opacity: 0.35;
        pointer-events: none;
    }

    .tc-day-picker {
        position: relative;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 11px;
        min-width: 0;
        color: #0d2141;
        font-size: clamp(16px, 2.7vw, 20px);
        font-weight: 750;
        cursor: pointer;
    }

    .tc-day-picker input[type="date"] {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        cursor: pointer;
    }

    .tc-day-picker .tc-calendar-icon {
        display: inline-flex;
        color: #52637c;
    }

    .tc-day-result {
        display: grid;
        grid-template-columns: auto minmax(0, 1fr) auto;
        align-items: center;
        gap: 14px;
        padding: 19px 2px 0;
    }

    .tc-day-clock {
        width: 42px;
        height: 42px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #425671;
    }

    .tc-day-clock svg {
        width: 30px;
        height: 30px;
        stroke: currentColor;
        fill: none;
        stroke-width: 1.8;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .tc-day-result-label {
        color: #30435f;
        font-size: 15px;
        font-weight: 650;
    }

    .tc-day-result-value {
        color: #0d2141;
        font-size: clamp(20px, 3.4vw, 25px);
        font-weight: 900;
        white-space: nowrap;
    }

    .tc-day-empty {
        grid-column: 2 / -1;
        margin-top: -8px;
        color: #8491a2;
        font-size: 13px;
        font-weight: 650;
    }

    .tc-details-card {
        overflow: hidden;
    }

    .tc-details-card > summary {
        list-style: none;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        min-height: 72px;
        padding: 0 28px;
        cursor: pointer;
        user-select: none;
    }

    .tc-details-card > summary::-webkit-details-marker {
        display: none;
    }

    .tc-details-chevron {
        width: 11px;
        height: 11px;
        border-right: 2px solid #52637c;
        border-bottom: 2px solid #52637c;
        transform: rotate(45deg) translateY(-3px);
        transition: transform 0.18s ease;
        flex: 0 0 auto;
    }

    .tc-details-card[open] .tc-details-chevron {
        transform: rotate(225deg) translate(-2px, -1px);
    }

    .tc-details-body {
        padding: 0 28px 22px;
        border-top: 1px solid #edf1f5;
    }

    .tc-info-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        column-gap: 28px;
    }

    .tc-info-row {
        display: flex;
        justify-content: space-between;
        gap: 18px;
        min-width: 0;
        padding: 14px 0;
        border-bottom: 1px solid #edf1f5;
    }

    .tc-info-label {
        color: #718096;
        font-size: 13px;
        font-weight: 650;
    }

    .tc-info-value {
        max-width: 62%;
        color: #1c3151;
        font-size: 13px;
        font-weight: 800;
        text-align: right;
        overflow-wrap: anywhere;
    }

    .tc-signature-card {
        padding: 24px 28px 26px;
    }

    .tc-signature-options {
        margin-top: 16px;
        border: 1px solid #d8e2ec;
        border-radius: 14px;
        overflow: hidden;
        background: #ffffff;
    }

    .tc-signature-option {
        display: grid;
        grid-template-columns: 30px minmax(0, 1fr) auto;
        align-items: center;
        gap: 13px;
        min-height: 72px;
        padding: 16px 20px;
        cursor: pointer;
    }

    .tc-signature-option + .tc-signature-option {
        border-top: 1px solid #e6ebf1;
    }

    .tc-signature-option input[type="radio"] {
        width: 22px;
        height: 22px;
        margin: 0;
        accent-color: #17477d;
        cursor: pointer;
    }

    .tc-signature-option-main {
        min-width: 0;
        display: grid;
        gap: 6px;
    }

    .tc-signature-option-title {
        color: #0f2344;
        font-size: 15px;
        font-weight: 750;
    }

    .tc-signature-preview {
        min-height: 38px;
        display: flex;
        align-items: center;
    }

    .tc-signature-preview img {
        display: block;
        max-width: 240px;
        max-height: 60px;
        object-fit: contain;
        object-position: left center;
    }

    .tc-signature-placeholder {
        color: #8a97a7;
        font-size: 13px;
        font-weight: 650;
    }

    .tc-signature-arrow {
        width: 11px;
        height: 11px;
        border-right: 2px solid #52637c;
        border-top: 2px solid #52637c;
        transform: rotate(45deg);
    }

    .signature-builder-panel {
        margin-top: 14px;
        padding: 18px;
        border: 1px solid #d8e2ec;
        border-radius: 14px;
        background: #f8fafc;
    }

    .signature-builder-panel[hidden] {
        display: none !important;
    }

    .signature-builder-head {
        display: grid;
        gap: 4px;
        margin-bottom: 12px;
    }

    .signature-builder-head strong {
        color: #0f2344;
        font-size: 16px;
    }

    .signature-builder-head small {
        color: #718096;
        line-height: 1.45;
    }

    .signature-canvas-wrap {
        width: 100%;
        padding: 8px;
        border: 1px dashed #b7c5d5;
        border-radius: 12px;
        background: #ffffff;
    }

    .signature-canvas {
        display: block;
        width: 100%;
        height: 200px;
        border-radius: 9px;
        background: #ffffff;
        touch-action: none;
        cursor: crosshair;
    }

    .signature-builder-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 12px;
    }

    .signature-builder-actions .primary-btn,
    .signature-builder-actions .secondary-btn {
        width: auto;
        min-width: 150px;
    }

    .signature-status {
        margin-top: 10px;
        color: #607086;
        font-size: 13px;
        font-weight: 650;
    }

    .tc-primary-action {
        width: 100%;
        min-height: 64px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 11px;
        padding: 13px 18px;
        border: 1px solid #17477d;
        border-radius: 13px;
        background: linear-gradient(135deg, #123761, #174d83);
        color: #ffffff;
        font: inherit;
        font-size: 16px;
        font-weight: 850;
        cursor: pointer;
        box-shadow: 0 10px 22px rgba(23, 71, 125, 0.18);
        transition: transform 0.18s ease, box-shadow 0.18s ease;
    }

    .tc-primary-action:hover {
        box-shadow: 0 13px 28px rgba(23, 71, 125, 0.24);
    }

    .tc-primary-action:active {
        transform: translateY(1px);
    }

    .tc-back-link {
        display: block;
        width: fit-content;
        margin: -4px auto 0;
        padding: 9px 14px;
        color: #65748a;
        text-decoration: none;
        font-size: 15px;
        font-weight: 650;
    }

    .tc-back-link:hover {
        color: #183e6d;
    }

    @media (max-width: 720px) {
        .tc-report-page {
            padding: 18px 14px 30px;
        }

        .tc-report-form {
            gap: 14px;
        }

        .tc-period-card {
            grid-template-columns: 46px minmax(0, 1fr) 46px;
            min-height: 74px;
            padding: 0 8px;
        }

        .tc-period-picker > summary {
            gap: 9px;
            padding: 7px 8px;
        }

        .tc-period-popover {
            grid-template-columns: 1fr 104px;
        }

        .tc-apply-period {
            grid-column: 1 / -1;
        }

        .tc-summary-card,
        .tc-section-card,
        .tc-signature-card {
            padding-left: 18px;
            padding-right: 18px;
        }

        .tc-rate-options {
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 7px;
        }

        .tc-rate-btn {
            min-height: 50px;
            padding-left: 5px;
            padding-right: 5px;
            font-size: 13px;
        }

        .tc-info-grid {
            grid-template-columns: 1fr;
        }

        .tc-details-card > summary,
        .tc-details-body {
            padding-left: 18px;
            padding-right: 18px;
        }
    }

    @media (max-width: 440px) {
        .tc-report-heading {
            margin-bottom: 17px;
        }

        .tc-summary-card {
            padding-top: 22px;
        }

        .tc-rate-options {
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 6px;
        }

        .tc-rate-btn {
            min-height: 48px;
            font-size: 12px;
        }

        .tc-total-row {
            grid-template-columns: minmax(0, 1fr) auto;
        }

        .tc-total-value {
            grid-column: 1 / 2;
            grid-row: 2;
        }

        .tc-eye-btn {
            grid-column: 2;
            grid-row: 1 / span 2;
        }

        .tc-section-head {
            align-items: flex-start;
        }

        .tc-day-result {
            grid-template-columns: 38px minmax(0, 1fr);
        }

        .tc-day-result-value {
            grid-column: 2;
            margin-top: -4px;
        }

        .tc-day-empty {
            grid-column: 2;
            margin-top: 0;
        }

        .tc-signature-option {
            grid-template-columns: 26px minmax(0, 1fr) auto;
            padding: 15px 14px;
        }
    }

    @media (max-width: 350px) {
        .tc-rate-options {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
</style>

<section class="tc-report-page">
    <header class="tc-report-heading">
        <h1>Relatório mensal</h1>
        <?php if ($auditorDisplayName !== ''): ?>
            <p><?= htmlspecialchars($auditorDisplayName) ?></p>
        <?php endif; ?>
    </header>

    <form
        method="POST"
        action="/auditor-app/public/report/monthly-preview"
        class="tc-report-form"
        data-signature-builder
        id="tc-report-form"
    >
        <section class="tc-card tc-period-card" aria-label="Selecionar período">
            <a
                class="tc-icon-link"
                href="<?= htmlspecialchars($previousMonthUrl) ?>"
                aria-label="Mês anterior"
                title="Mês anterior"
            >
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M15 18l-6-6 6-6"></path>
                </svg>
            </a>

            <details class="tc-period-picker" id="tc-period-picker">
                <summary>
                    <span class="tc-calendar-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <rect x="3" y="5" width="18" height="16" rx="2"></rect>
                            <path d="M16 3v4M8 3v4M3 10h18"></path>
                        </svg>
                    </span>
                    <span><?= htmlspecialchars($monthlyPeriodDisplay) ?></span>
                    <span class="tc-period-chevron" aria-hidden="true"></span>
                </summary>

                <div class="tc-period-popover">
                    <select name="month" id="tc-report-month" aria-label="Mês">
                        <?php for ($m = 1; $m <= 12; $m++): ?>
                            <option
                                value="<?= $m ?>"
                                <?= $reportMonthForUrl === $m ? 'selected' : '' ?>
                            >
                                <?= htmlspecialchars($monthNames[$m]) ?>
                            </option>
                        <?php endfor; ?>
                    </select>

                    <input
                        type="number"
                        name="year"
                        id="tc-report-year"
                        value="<?= htmlspecialchars((string) $reportYearForUrl) ?>"
                        min="2020"
                        max="2100"
                        inputmode="numeric"
                        aria-label="Ano"
                    >

                    <button
                        type="button"
                        class="tc-apply-period"
                        id="tc-apply-period"
                    >
                        Aplicar
                    </button>
                </div>
            </details>

            <a
                class="tc-icon-link"
                href="<?= htmlspecialchars($nextMonthUrl) ?>"
                aria-label="Mês seguinte"
                title="Mês seguinte"
            >
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M9 18l6-6-6-6"></path>
                </svg>
            </a>
        </section>

        <section
            class="tc-card tc-summary-card"
            data-monthly-minutes="<?= $monthlyTotalMinutes ?>"
            data-default-rate="<?= htmlspecialchars(number_format($hourlyRate, 2, '.', '')) ?>"
        >
            <span class="tc-summary-label">Horas do mês</span>
            <strong class="tc-summary-hours">
                <?= htmlspecialchars($monthlyWorkedDisplay) ?>
            </strong>

            <div class="tc-summary-divider"></div>

            <div class="tc-rate-title">Valor por hora</div>
            <div class="tc-rate-options" role="group" aria-label="Valor por hora">
                <?php foreach ([8.00, 8.50, 9.00, 10.00] as $rate): ?>
                    <button
                        type="button"
                        class="tc-rate-btn <?= abs($rate - $hourlyRate) < 0.001 ? 'is-active' : '' ?>"
                        data-hourly-rate="<?= htmlspecialchars(number_format($rate, 2, '.', '')) ?>"
                        aria-pressed="<?= abs($rate - $hourlyRate) < 0.001 ? 'true' : 'false' ?>"
                    >
                        <?= htmlspecialchars(number_format($rate, 2, ',', '.')) ?> €
                    </button>
                <?php endforeach; ?>
            </div>

            <div class="tc-total-row">
                <span class="tc-total-label">Total estimado</span>
                <strong
                    class="tc-total-value"
                    id="tc-total-estimated"
                    data-visible-value="<?= htmlspecialchars($monthlyTotalAmountDisplay) ?>"
                >
                    <?= htmlspecialchars($monthlyTotalAmountDisplay) ?>
                </strong>

                <button
                    type="button"
                    class="tc-icon-button tc-eye-btn"
                    id="tc-total-visibility"
                    aria-label="Ocultar total estimado"
                    aria-pressed="false"
                    title="Ocultar total estimado"
                >
                    <svg viewBox="0 0 24 24" aria-hidden="true" data-eye-open>
                        <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                    <svg viewBox="0 0 24 24" aria-hidden="true" data-eye-closed hidden>
                        <path d="M3 3l18 18"></path>
                        <path d="M10.6 10.7a2 2 0 002.7 2.7"></path>
                        <path d="M9.9 4.2A10.7 10.7 0 0112 4c6.5 0 10 8 10 8a17.8 17.8 0 01-2.1 3.1"></path>
                        <path d="M6.6 6.6C3.8 8.5 2 12 2 12s3.5 8 10 8a10.9 10.9 0 005.4-1.4"></path>
                    </svg>
                </button>
            </div>
        </section>

        <section class="tc-card tc-section-card">
            <div class="tc-section-head">
                <div class="tc-section-title-wrap">
                    <h2 class="tc-section-title">Horas por dia</h2>
                </div>

                <?php if (!$isConsultToday): ?>
                    <a
                        class="tc-today-link"
                        href="<?= htmlspecialchars($buildConsultDayUrl($todayIso)) ?>"
                    >
                        Hoje
                    </a>
                <?php else: ?>
                    <span class="tc-today-link" aria-current="date">Hoje</span>
                <?php endif; ?>
            </div>

            <div class="tc-day-nav">
                <a
                    class="tc-icon-link"
                    href="<?= htmlspecialchars($buildConsultDayUrl($previousConsultDate)) ?>"
                    aria-label="Dia anterior"
                    title="Dia anterior"
                >
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M15 18l-6-6 6-6"></path>
                    </svg>
                </a>

                <label class="tc-day-picker" title="Escolher outro dia">
                    <span><?= htmlspecialchars($consultDateDisplay) ?></span>
                    <span class="tc-calendar-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <rect x="3" y="5" width="18" height="16" rx="2"></rect>
                            <path d="M16 3v4M8 3v4M3 10h18"></path>
                        </svg>
                    </span>
                    <input
                        type="date"
                        id="tc-consult-date"
                        value="<?= htmlspecialchars($consultDate) ?>"
                        max="<?= htmlspecialchars($todayIso) ?>"
                        aria-label="Escolher dia"
                    >
                </label>

                <?php if ($canGoNextConsultDay): ?>
                    <a
                        class="tc-icon-link"
                        href="<?= htmlspecialchars($buildConsultDayUrl($nextConsultDate)) ?>"
                        aria-label="Dia seguinte"
                        title="Dia seguinte"
                    >
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M9 18l6-6-6-6"></path>
                        </svg>
                    </a>
                <?php else: ?>
                    <span
                        class="tc-icon-button is-disabled"
                        aria-hidden="true"
                    >
                        <svg viewBox="0 0 24 24">
                            <path d="M9 18l6-6-6-6"></path>
                        </svg>
                    </span>
                <?php endif; ?>
            </div>

            <div class="tc-day-result">
                <span class="tc-day-clock" aria-hidden="true">
                    <svg viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="9"></circle>
                        <path d="M12 7v5l3 2"></path>
                    </svg>
                </span>
                <span class="tc-day-result-label">Horas registadas</span>
                <strong class="tc-day-result-value">
                    <?= htmlspecialchars($consultHoursDisplay) ?>
                </strong>

                <?php if ($consultTotalMinutes <= 0): ?>
                    <span class="tc-day-empty">Sem registos neste dia</span>
                <?php endif; ?>
            </div>
        </section>

        <details class="tc-card tc-details-card">
            <summary>
                <span class="tc-section-title-wrap">
                    <span class="tc-section-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <path d="M4 6h16M4 12h16M4 18h10"></path>
                        </svg>
                    </span>
                    <span class="tc-section-title">Dados do relatório</span>
                </span>
                <span class="tc-details-chevron" aria-hidden="true"></span>
            </summary>

            <div class="tc-details-body">
                <div class="tc-info-grid">
                    <div class="tc-info-row">
                        <span class="tc-info-label">Cabeçalho</span>
                        <strong class="tc-info-value">
                            <?= htmlspecialchars($settings['company_name'] ?? 'TimeClock') ?>
                        </strong>
                    </div>

                    <div class="tc-info-row">
                        <span class="tc-info-label">Trabalhador(a)</span>
                        <strong class="tc-info-value">
                            <?= htmlspecialchars($settings['auditor_name'] ?? '-') ?>
                        </strong>
                    </div>

                    <div class="tc-info-row">
                        <span class="tc-info-label">Hora semanal</span>
                        <strong class="tc-info-value">
                            <?= htmlspecialchars($settings['weekly_hours'] ?? '-') ?>
                        </strong>
                    </div>

                    <div class="tc-info-row">
                        <span class="tc-info-label">Horário de trabalho</span>
                        <strong class="tc-info-value">
                            <?= htmlspecialchars($settings['work_schedule'] ?? '-') ?>
                        </strong>
                    </div>

                    <div class="tc-info-row">
                        <span class="tc-info-label">Local</span>
                        <strong class="tc-info-value">
                            <?= htmlspecialchars($settings['work_location'] ?? '-') ?>
                        </strong>
                    </div>

                    <div class="tc-info-row">
                        <span class="tc-info-label">Logo</span>
                        <strong class="tc-info-value">
                            <?= !empty($settings['company_logo_path']) ? 'Configurado' : 'Ainda não configurado' ?>
                        </strong>
                    </div>

                    <div class="tc-info-row">
                        <span class="tc-info-label">Assinatura guardada</span>
                        <strong class="tc-info-value">
                            <?= !empty($settings['signature_path']) ? 'Configurada' : 'Ainda não configurada' ?>
                        </strong>
                    </div>

                    <div class="tc-info-row">
                        <span class="tc-info-label">Assinatura desta sessão</span>
                        <strong class="tc-info-value">
                            <?= !empty($runtimeSignatureActive) ? 'Ativa' : 'Não ativa' ?>
                        </strong>
                    </div>
                </div>
            </div>
        </details>

        <section class="tc-card tc-signature-card">
            <div class="tc-section-title-wrap">
                <span class="tc-section-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24">
                        <path d="M12 20h9"></path>
                        <path d="M16.5 3.5a2.1 2.1 0 013 3L8 18l-4 1 1-4z"></path>
                    </svg>
                </span>
                <h2 class="tc-section-title">Assinatura no PDF</h2>
            </div>

            <div class="tc-signature-options">
                <label class="tc-signature-option">
                    <input
                        type="radio"
                        name="use_drawn_signature"
                        id="use_drawn_signature_no"
                        value="0"
                        <?= empty($runtimeSignatureActive) ? 'checked' : '' ?>
                    >

                    <span class="tc-signature-option-main">
                        <span class="tc-signature-option-title">Usar assinatura guardada</span>
                        <span class="tc-signature-preview">
                            <?php if (!empty($settings['signature_path'])): ?>
                                <img
                                    src="<?= htmlspecialchars((string) $settings['signature_path']) ?>"
                                    alt="Pré-visualização da assinatura guardada"
                                    onerror="this.style.display='none'; this.nextElementSibling.style.display='inline';"
                                >
                                <span class="tc-signature-placeholder" style="display:none;">
                                    Assinatura configurada
                                </span>
                            <?php else: ?>
                                <span class="tc-signature-placeholder">
                                    Nenhuma assinatura guardada
                                </span>
                            <?php endif; ?>
                        </span>
                    </span>

                    <span aria-hidden="true"></span>
                </label>

                <label class="tc-signature-option" for="use_drawn_signature_yes">
                    <input
                        type="radio"
                        name="use_drawn_signature"
                        id="use_drawn_signature_yes"
                        value="1"
                        <?= !empty($runtimeSignatureActive) ? 'checked' : '' ?>
                    >

                    <span class="tc-signature-option-main">
                        <span class="tc-signature-option-title">Assinar agora</span>
                    </span>

                    <span class="tc-signature-arrow" aria-hidden="true"></span>
                </label>
            </div>

            <div class="signature-builder-panel" hidden>
                <div class="signature-builder-head">
                    <strong>Assinatura do colaborador</strong>
                    <small>Assina no quadro abaixo e guarda antes de pré-visualizar o relatório.</small>
                </div>

                <div class="signature-canvas-wrap">
                    <canvas id="signature-pad" class="signature-canvas"></canvas>
                </div>

                <input type="hidden" name="signature_data" id="signature_data">

                <div class="signature-builder-actions">
                    <button
                        type="button"
                        class="secondary-btn"
                        id="signature-clear-btn"
                    >
                        Limpar
                    </button>
                    <button
                        type="button"
                        class="primary-btn"
                        id="signature-save-btn"
                    >
                        Usar assinatura
                    </button>
                </div>

                <div class="signature-status" id="signature-status">
                    <?= !empty($runtimeSignatureActive)
                        ? 'Assinatura temporária ativa nesta sessão.'
                        : 'A usar assinatura guardada nas configurações, se existir.' ?>
                </div>
            </div>
        </section>

        <button type="submit" class="tc-primary-action">
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"></path>
                <path d="M14 2v6h6M8 13h8M8 17h8"></path>
            </svg>
            <span>Pré-visualizar relatório</span>
        </button>

        <a class="tc-back-link" href="/auditor-app/public/">Voltar</a>
    </form>
</section>

<script src="/auditor-app/public/assets/js/report-signature-pad.js?v=20260410_signaturepad_v2"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const summaryCard = document.querySelector('.tc-summary-card');
    const totalElement = document.getElementById('tc-total-estimated');
    const totalVisibilityButton = document.getElementById('tc-total-visibility');
    const rateButtons = Array.from(document.querySelectorAll('.tc-rate-btn'));
    const dayInput = document.getElementById('tc-consult-date');
    const monthSelect = document.getElementById('tc-report-month');
    const yearInput = document.getElementById('tc-report-year');
    const applyPeriodButton = document.getElementById('tc-apply-period');

    const RATE_KEY = 'timeclock.report.hourlyRate';
    const HIDE_TOTAL_KEY = 'timeclock.report.hideEstimatedTotal';

    function safeStorageGet(key) {
        try {
            return window.localStorage.getItem(key);
        } catch (error) {
            return null;
        }
    }

    function safeStorageSet(key, value) {
        try {
            window.localStorage.setItem(key, value);
        } catch (error) {
            // Preferência apenas local: falhar silenciosamente é aceitável.
        }
    }

    function formatEuro(value) {
        const formatted = new Intl.NumberFormat('pt-PT', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(value);

        return formatted + ' €';
    }

    function getMonthlyHours() {
        if (!summaryCard) return 0;

        const minutes = Number(summaryCard.dataset.monthlyMinutes || '0');
        if (!Number.isFinite(minutes) || minutes <= 0) return 0;

        return minutes / 60;
    }

    function getSelectedRate() {
        const activeButton = rateButtons.find(function (button) {
            return button.classList.contains('is-active');
        });

        if (!activeButton) {
            return Number(summaryCard?.dataset.defaultRate || '8');
        }

        return Number(activeButton.dataset.hourlyRate || '8');
    }

    function updateTotalDisplay() {
        if (!totalElement) return;

        const total = getMonthlyHours() * getSelectedRate();
        const visibleValue = formatEuro(total);
        totalElement.dataset.visibleValue = visibleValue;

        const isHidden = safeStorageGet(HIDE_TOTAL_KEY) === '1';
        totalElement.textContent = isHidden ? '••••••' : visibleValue;
        totalElement.classList.toggle('is-hidden', isHidden);
    }

    function selectRate(rate) {
        rateButtons.forEach(function (button) {
            const buttonRate = Number(button.dataset.hourlyRate || '0');
            const isActive = Math.abs(buttonRate - rate) < 0.001;

            button.classList.toggle('is-active', isActive);
            button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
        });

        safeStorageSet(RATE_KEY, String(rate));
        updateTotalDisplay();
    }

    if (rateButtons.length > 0) {
        const allowedRates = rateButtons.map(function (button) {
            return Number(button.dataset.hourlyRate || '0');
        });
        const storedRate = Number(safeStorageGet(RATE_KEY));
        const defaultRate = Number(summaryCard?.dataset.defaultRate || '8');
        const initialRate = allowedRates.some(function (rate) {
            return Math.abs(rate - storedRate) < 0.001;
        }) ? storedRate : defaultRate;

        selectRate(initialRate);

        rateButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                selectRate(Number(button.dataset.hourlyRate || '8'));
            });
        });
    }

    if (totalVisibilityButton && totalElement) {
        const openEye = totalVisibilityButton.querySelector('[data-eye-open]');
        const closedEye = totalVisibilityButton.querySelector('[data-eye-closed]');

        function syncVisibilityButton() {
            const isHidden = safeStorageGet(HIDE_TOTAL_KEY) === '1';

            totalVisibilityButton.setAttribute(
                'aria-label',
                isHidden ? 'Mostrar total estimado' : 'Ocultar total estimado'
            );
            totalVisibilityButton.setAttribute(
                'title',
                isHidden ? 'Mostrar total estimado' : 'Ocultar total estimado'
            );
            totalVisibilityButton.setAttribute(
                'aria-pressed',
                isHidden ? 'true' : 'false'
            );

            if (openEye) {
                openEye.hidden = isHidden;
                openEye.style.display = isHidden ? 'none' : 'block';
            }

            if (closedEye) {
                closedEye.hidden = !isHidden;
                closedEye.style.display = isHidden ? 'block' : 'none';
            }

            updateTotalDisplay();
        }

        totalVisibilityButton.addEventListener('click', function () {
            const currentlyHidden = safeStorageGet(HIDE_TOTAL_KEY) === '1';
            safeStorageSet(HIDE_TOTAL_KEY, currentlyHidden ? '0' : '1');
            syncVisibilityButton();
        });

        syncVisibilityButton();
    }

    if (dayInput) {
        dayInput.addEventListener('change', function () {
            if (!this.value) return;

            const params = new URLSearchParams({
                month: String(<?= $reportMonthForUrl ?>),
                year: String(<?= $reportYearForUrl ?>),
                day: this.value
            });

            window.location.href =
                '/auditor-app/public/export/pdf?' + params.toString();
        });
    }

    if (applyPeriodButton && monthSelect && yearInput) {
        applyPeriodButton.addEventListener('click', function () {
            const targetMonth = Number(monthSelect.value);
            const targetYear = Number(yearInput.value);

            if (
                !Number.isInteger(targetMonth) ||
                targetMonth < 1 ||
                targetMonth > 12 ||
                !Number.isInteger(targetYear) ||
                targetYear < 2020 ||
                targetYear > 2100
            ) {
                yearInput.focus();
                return;
            }

            const params = new URLSearchParams({
                month: String(targetMonth),
                year: String(targetYear)
            });

            window.location.href =
                '/auditor-app/public/export/pdf?' + params.toString();
        });
    }
});
</script>

<?php require BASE_PATH . '/app/views/partials/layout_end.php'; ?>
