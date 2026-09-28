<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= htmlspecialchars($title ?? 'Redefinir password') ?></title>

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

                <h2>Redefinir password</h2>

                <p class="auth-muted">
                    Introduz e confirma a nova password.
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

                <form
                    method="POST"
                    action="/auditor-app/public/reset-password"
                    class="form-stack"
                >
                    <input
                        type="hidden"
                        name="token"
                        value="<?= htmlspecialchars($token ?? '') ?>"
                    >

                    <label for="password">
                        Nova password
                    </label>

                    <div class="password-field">
                        <input
                            id="password"
                            class="password-input"
                            type="password"
                            name="password"
                            autocomplete="new-password"
                            required
                        >

                        <button
                            type="button"
                            class="password-toggle"
                            aria-label="Mostrar password"
                            aria-pressed="false"
                        >
                            <svg
                                class="eye-closed"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                aria-hidden="true"
                            >
                                <path d="M3 3l18 18"></path>
                                <path d="M10.58 10.58A2 2 0 0 0 12 14a2 2 0 0 0 1.42-.58"></path>
                                <path d="M9.88 5.09A9.77 9.77 0 0 1 12 5c7 0 11 7 11 7a21.8 21.8 0 0 1-5.17 5.94"></path>
                                <path d="M6.61 6.61A21.76 21.76 0 0 0 1 12s4 7 11 7a10.8 10.8 0 0 0 5.39-1.39"></path>
                            </svg>

                            <svg
                                class="eye-open"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                aria-hidden="true"
                            >
                                <path d="M1 12s4-7 11-7s11 7 11 7s-4 7-11 7S1 12 1 12Z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                        </button>
                    </div>

                    <label for="password_confirm">
                        Confirmar nova password
                    </label>

                    <div class="password-field">
                        <input
                            id="password_confirm"
                            class="password-input"
                            type="password"
                            name="password_confirm"
                            autocomplete="new-password"
                            required
                        >

                        <button
                            type="button"
                            class="password-toggle"
                            aria-label="Mostrar password"
                            aria-pressed="false"
                        >
                            <svg
                                class="eye-closed"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                aria-hidden="true"
                            >
                                <path d="M3 3l18 18"></path>
                                <path d="M10.58 10.58A2 2 0 0 0 12 14a2 2 0 0 0 1.42-.58"></path>
                                <path d="M9.88 5.09A9.77 9.77 0 0 1 12 5c7 0 11 7 11 7a21.8 21.8 0 0 1-5.17 5.94"></path>
                                <path d="M6.61 6.61A21.76 21.76 0 0 0 1 12s4 7 11 7a10.8 10.8 0 0 0 5.39-1.39"></path>
                            </svg>

                            <svg
                                class="eye-open"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                aria-hidden="true"
                            >
                                <path d="M1 12s4-7 11-7s11 7 11 7s-4 7-11 7S1 12 1 12Z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                        </button>
                    </div>

                    <button
                        type="submit"
                        class="primary-btn"
                    >
                        Guardar nova password
                    </button>
                </form>

                <div class="auth-links single">
                    <a href="/auditor-app/public/login">
                        Voltar ao login
                    </a>
                </div>
            </div>
        </section>
    </div>

    <script src="/auditor-app/public/assets/js/auth-password-toggle.js?v=20260410_googlefix"></script>
</body>
</html>