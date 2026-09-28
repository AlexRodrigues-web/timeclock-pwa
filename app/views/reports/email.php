<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Enviar Email') ?></title>
    <link rel="stylesheet" href="/auditor-app/public/assets/css/app.css">
</head>
<body>
    <header class="topbar">
        <a class="menu-btn" href="/auditor-app/public/">←</a>
        <div class="topbar-title">
            <strong><?= htmlspecialchars($title ?? '') ?></strong>
        </div>
    </header>

    <main class="container narrow">
        <section class="card">
            <p><strong>Email destino configurado:</strong> <?= htmlspecialchars($settings['email_to'] ?? '-') ?></p>
            <p class="muted"><?= htmlspecialchars($message ?? '') ?></p>
        </section>
    </main>
</body>
</html>
