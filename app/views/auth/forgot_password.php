<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= htmlspecialchars($title ?? 'Esqueci a password') ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="/auditor-app/public/assets/css/app.css?v=20260801-no-custom-splash"
    >
</head>

<body class="auth-body">
    <div class="auth-layout">
        <section class="auth-panel">
            <div class="auth-card-modern auth-card-auth">
                <div class="auth-logo auth-logo-image-wrap">
                    <img
                        src="/auditor-app/public/assets/img/timeclock-logo.png?v=20260801"
                        alt="TimeClock"
                        class="auth-logo-image"
                    >
                </div>

                <h2>Esqueci a password</h2>

                <p class="auth-muted">
                    Introduz o teu email para gerar um link de redefinição.
                </p>

                <?php if (!empty($error)): ?>
                    <div class="alert">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($success)): ?>
                    <div class="alert success">
                        <?= htmlspecialchars($success) ?>
                    </div>
                <?php endif; ?>

                <?php
                    $localResetLink =
                        $resetLink ??
                        $debugLink ??
                        $generatedLink ??
                        $link ??
                        null;
                ?>

                <form
                    method="POST"
                    action="/auditor-app/public/forgot-password"
                    class="form-stack"
                >
                    <label for="email">
                        Email
                    </label>

                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="<?= htmlspecialchars($old['email'] ?? '') ?>"
                        placeholder="admin@local.test"
                        autocomplete="email"
                        required
                    >

                    <button
                        type="submit"
                        class="primary-btn"
                    >
                        Gerar link de redefinição
                    </button>
                </form>

                <?php if (!empty($localResetLink)): ?>
                    <div
                        class="alert success"
                        style="margin-top: 12px;"
                    >
                        Link gerado localmente para desenvolvimento.
                    </div>

                    <a
                        href="<?= htmlspecialchars($localResetLink) ?>"
                        class="secondary-btn"
                        style="display:flex; justify-content:center; margin-top:8px; text-decoration:none;"
                    >
                        Abrir link de redefinição
                    </a>
                <?php endif; ?>

                <div class="auth-links single">
                    <a href="/auditor-app/public/login">
                        Voltar ao login
                    </a>
                </div>
            </div>
        </section>
    </div>
</body>
</html>