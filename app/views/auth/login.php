<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= htmlspecialchars($title ?? 'Entrar') ?></title>

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

                <h2>Entrar</h2>

                <p class="auth-muted">
                    Acede à tua área operacional.
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
                    action="/auditor-app/public/login"
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

                    <label for="password">
                        Password
                    </label>

                    <div class="password-field">
                        <input
                            id="password"
                            class="password-input"
                            type="password"
                            name="password"
                            placeholder="••••••••"
                            autocomplete="current-password"
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
                        Entrar
                    </button>
                </form>

                <div class="auth-links">
                    <a href="/auditor-app/public/register">
                        Criar conta
                    </a>

                    <a href="/auditor-app/public/forgot-password">
                        Esqueci a password
                    </a>
                </div>

                <div class="auth-divider">
                    <span>ou</span>
                </div>

                <?php if (!empty($googleEnabled)): ?>
                    <a
                        href="/auditor-app/public/auth/google"
                        class="google-signin-btn"
                    >
                        <span
                            class="google-signin-btn-icon"
                            aria-hidden="true"
                        >
                            <svg viewBox="0 0 48 48">
                                <path
                                    fill="#EA4335"
                                    d="M24 9.5c3.54 0 6.73 1.22 9.24 3.6l6.9-6.9C35.95 2.38 30.37 0 24 0 14.62 0 6.51 5.38 2.56 13.22l8.03 6.24C12.47 13.59 17.73 9.5 24 9.5z"
                                />
                                <path
                                    fill="#4285F4"
                                    d="M46.5 24.55c0-1.64-.15-3.21-.42-4.73H24v9.04h12.65c-.55 2.98-2.23 5.5-4.75 7.18l7.3 5.66c4.27-3.94 6.73-9.75 6.73-17.15z"
                                />
                                <path
                                    fill="#FBBC05"
                                    d="M10.59 28.54A14.46 14.46 0 0 1 9.82 24c0-1.57.27-3.09.77-4.54l-8.03-6.24A23.96 23.96 0 0 0 0 24c0 3.87.93 7.53 2.56 10.78l8.03-6.24z"
                                />
                                <path
                                    fill="#34A853"
                                    d="M24 48c6.37 0 11.72-2.1 15.63-5.72l-7.3-5.66c-2.03 1.36-4.63 2.17-8.33 2.17-6.27 0-11.53-4.09-13.41-9.96l-8.03 6.24C6.51 42.62 14.62 48 24 48z"
                                />
                            </svg>
                        </span>

                        <span class="google-signin-btn-text">
                            Sign in with Google
                        </span>
                    </a>
                <?php else: ?>
                    <a
                        href="#"
                        class="google-signin-btn is-disabled"
                        aria-disabled="true"
                    >
                        <span
                            class="google-signin-btn-icon"
                            aria-hidden="true"
                        >
                            <svg viewBox="0 0 48 48">
                                <path
                                    fill="#EA4335"
                                    d="M24 9.5c3.54 0 6.73 1.22 9.24 3.6l6.9-6.9C35.95 2.38 30.37 0 24 0 14.62 0 6.51 5.38 2.56 13.22l8.03 6.24C12.47 13.59 17.73 9.5 24 9.5z"
                                />
                                <path
                                    fill="#4285F4"
                                    d="M46.5 24.55c0-1.64-.15-3.21-.42-4.73H24v9.04h12.65c-.55 2.98-2.23 5.5-4.75 7.18l7.3 5.66c4.27-3.94 6.73-9.75 6.73-17.15z"
                                />
                                <path
                                    fill="#FBBC05"
                                    d="M10.59 28.54A14.46 14.46 0 0 1 9.82 24c0-1.57.27-3.09.77-4.54l-8.03-6.24A23.96 23.96 0 0 0 0 24c0 3.87.93 7.53 2.56 10.78l8.03-6.24z"
                                />
                                <path
                                    fill="#34A853"
                                    d="M24 48c6.37 0 11.72-2.1 15.63-5.72l-7.3-5.66c-2.03 1.36-4.63 2.17-8.33 2.17-6.27 0-11.53-4.09-13.41-9.96l-8.03 6.24C6.51 42.62 14.62 48 24 48z"
                                />
                            </svg>
                        </span>

                        <span class="google-signin-btn-text">
                            Sign in with Google
                        </span>
                    </a>
                <?php endif; ?>
            </div>
        </section>
    </div>

    <script src="/auditor-app/public/assets/js/auth-password-toggle.js?v=20260410_googlefix"></script>
</body>
</html>