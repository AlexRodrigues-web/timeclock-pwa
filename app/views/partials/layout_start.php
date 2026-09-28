<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'TimeClock') ?></title>
    <meta name="theme-color" content="#f5f7fb">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="TimeClock">
    <link rel="manifest" href="/auditor-app/public/manifest.json">
    <link rel="apple-touch-icon" href="/auditor-app/public/assets/img/icon-192.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/auditor-app/public/assets/css/app.css?v=20260410_menu_refresh">
    <script defer src="/auditor-app/public/assets/js/app.js?v=20260410_menu_refresh"></script>
    <script defer src="/auditor-app/public/assets/js/startup-splash.js?v=20260410_menu_refresh"></script>
</head>
<body class="<?= htmlspecialchars(trim(($bodyClass ?? '') . ' has-startup-splash')) ?>">
    <?php $currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/'; ?>

    <div class="app-shell app-shell-modern">
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-brand">
                <div class="brand-mark brand-mark-modern">
                    <img src="/auditor-app/public/assets/img/timeclock-logo.png" alt="TimeClock" class="brand-mark-logo">
                </div>
                <div>
                    <div class="brand-title">TimeClock</div>
                    <div class="brand-subtitle">Operação em campo</div>
                </div>
            </div>

            <nav class="sidebar-nav">
                <a class="<?= str_contains($currentPath, '/public/') && !str_contains($currentPath, '/history') && !str_contains($currentPath, '/manual-entry') && !str_contains($currentPath, '/export/pdf') && !str_contains($currentPath, '/settings') && !str_contains($currentPath, '/account') ? 'active' : '' ?>" href="/auditor-app/public/">
                    <span class="nav-label">Início</span>
                </a>

                <a class="<?= str_contains($currentPath, '/history') ? 'active' : '' ?>" href="/auditor-app/public/history">
                    <span class="nav-label">Histórico</span>
                </a>

                <a class="<?= str_contains($currentPath, '/manual-entry') ? 'active' : '' ?>" href="/auditor-app/public/manual-entry">
                    <span class="nav-label">Lançamento Manual</span>
                </a>

                <a class="<?= str_contains($currentPath, '/export/pdf') || str_contains($currentPath, '/report/monthly') ? 'active' : '' ?>" href="/auditor-app/public/export/pdf">
                    <span class="nav-label">Relatórios</span>
                </a>

                <a class="<?= str_contains($currentPath, '/settings') ? 'active' : '' ?>" href="/auditor-app/public/settings">
                    <span class="nav-label">Configurações</span>
                </a>

                <a class="<?= str_contains($currentPath, '/account') ? 'active' : '' ?>" href="/auditor-app/public/account">
                    <span class="nav-label">Minha Conta</span>
                </a>

                <a href="/auditor-app/public/logout">
                    <span class="nav-label">Logout</span>
                </a>
            </nav>

            <div class="sidebar-footer sidebar-footer-modern">
                <div class="sidebar-footer-title">Trabalho mais leve</div>
                <div class="sidebar-footer-text">Registo rápido, relatórios melhores e fluxo mais limpo no terreno.</div>
            </div>
        </aside>

        <div class="mobile-backdrop" id="mobile-backdrop"></div>

        <div class="main-shell">
            <header class="app-header app-header-modern">
                <div class="header-left">
                    <button class="mobile-menu-btn" type="button" id="sidebar-open" aria-label="Abrir menu" aria-expanded="false" aria-controls="sidebar">
                        <span></span>
                        <span></span>
                        <span></span>
                    </button>

                    <div class="header-user-block">
                        <div class="page-title"><?= htmlspecialchars($user['name'] ?? $title ?? 'TimeClock') ?></div>
                        <div class="page-subtitle"><?= htmlspecialchars($headerSubtitle ?? 'Área operacional') ?></div>
                    </div>
                </div>

                <div class="header-right">
                    <button type="button" class="install-app-btn" id="install-app-btn" hidden>Instalar app</button>

                    <?php if (!empty($headerActionHref) && !empty($headerActionLabel)): ?>
                        <a class="header-action" href="<?= htmlspecialchars($headerActionHref) ?>"><?= htmlspecialchars($headerActionLabel) ?></a>
                    <?php endif; ?>
                </div>
            </header>

            <main class="page-content">