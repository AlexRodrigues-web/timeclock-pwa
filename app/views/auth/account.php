<?php
$title = 'Minha conta';
$bodyClass = 'app-body';
$headerSubtitle = $user['email'] ?? '';
require BASE_PATH . '/app/views/partials/layout_start.php';

$googleLinked = !empty($profile['google_id']);
$lastLoginText = !empty($profile['last_login_at'])
    ? date('d/m/Y H:i', strtotime($profile['last_login_at']))
    : 'Sem registo';
?>
<section class="hero-card compact-hero">
    <div>
        <div class="hero-kicker">Utilizador</div>
        <h1 class="hero-heading">Minha conta</h1>
        <p class="hero-text">Gere os teus dados, segurança e estado da tua conta.</p>
    </div>
</section>

<section class="content-grid single-col">
    <section class="panel-card">
        <?php if (!empty($error)): ?>
            <div class="alert"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <div class="preview-list" style="margin-bottom:18px;">
            <div><strong>Email atual:</strong> <?= htmlspecialchars($profile['email'] ?? '-') ?></div>
            <div><strong>Conta ativa:</strong> <?= !empty($profile['active']) ? 'Sim' : 'Não' ?></div>
            <div><strong>Google ligado:</strong> <?= $googleLinked ? 'Sim' : 'Não' ?></div>
            <div><strong>Último login:</strong> <?= htmlspecialchars($lastLoginText) ?></div>
        </div>

        <form method="POST" action="/auditor-app/public/account" class="form-stack">
            <label for="name">Nome</label>
            <input id="name" type="text" name="name" value="<?= htmlspecialchars($profile['name'] ?? '') ?>" autocomplete="name" required>

            <label for="email">Email</label>
            <input id="email" type="email" name="email" value="<?= htmlspecialchars($profile['email'] ?? '') ?>" autocomplete="email" required>

            <hr class="soft-separator">

            <div class="preview-list" style="margin-bottom:6px;">
                <div><strong>Segurança</strong></div>
                <div>Preenche os campos abaixo apenas se quiseres alterar a password.</div>
            </div>

            <label for="current_password">Password atual</label>
            <div class="password-field">
                <input id="current_password" class="password-input" type="password" name="current_password" autocomplete="current-password">
                <button type="button" class="password-toggle" aria-label="Mostrar password" aria-pressed="false">
                    <svg class="eye-closed" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M3 3l18 18"></path>
                        <path d="M10.58 10.58A2 2 0 0 0 12 14a2 2 0 0 0 1.42-.58"></path>
                        <path d="M9.88 5.09A9.77 9.77 0 0 1 12 5c7 0 11 7 11 7a21.8 21.8 0 0 1-5.17 5.94"></path>
                        <path d="M6.61 6.61A21.76 21.76 0 0 0 1 12s4 7 11 7a10.8 10.8 0 0 0 5.39-1.39"></path>
                    </svg>
                    <svg class="eye-open" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M1 12s4-7 11-7s11 7 11 7s-4 7-11 7S1 12 1 12Z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                </button>
            </div>

            <label for="new_password">Nova password</label>
            <div class="password-field">
                <input id="new_password" class="password-input" type="password" name="new_password" autocomplete="new-password">
                <button type="button" class="password-toggle" aria-label="Mostrar password" aria-pressed="false">
                    <svg class="eye-closed" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M3 3l18 18"></path>
                        <path d="M10.58 10.58A2 2 0 0 0 12 14a2 2 0 0 0 1.42-.58"></path>
                        <path d="M9.88 5.09A9.77 9.77 0 0 1 12 5c7 0 11 7 11 7a21.8 21.8 0 0 1-5.17 5.94"></path>
                        <path d="M6.61 6.61A21.76 21.76 0 0 0 1 12s4 7 11 7a10.8 10.8 0 0 0 5.39-1.39"></path>
                    </svg>
                    <svg class="eye-open" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M1 12s4-7 11-7s11 7 11 7s-4 7-11 7S1 12 1 12Z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                </button>
            </div>

            <label for="new_password_confirm">Confirmar nova password</label>
            <div class="password-field">
                <input id="new_password_confirm" class="password-input" type="password" name="new_password_confirm" autocomplete="new-password">
                <button type="button" class="password-toggle" aria-label="Mostrar password" aria-pressed="false">
                    <svg class="eye-closed" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M3 3l18 18"></path>
                        <path d="M10.58 10.58A2 2 0 0 0 12 14a2 2 0 0 0 1.42-.58"></path>
                        <path d="M9.88 5.09A9.77 9.77 0 0 1 12 5c7 0 11 7 11 7a21.8 21.8 0 0 1-5.17 5.94"></path>
                        <path d="M6.61 6.61A21.76 21.76 0 0 0 1 12s4 7 11 7a10.8 10.8 0 0 0 5.39-1.39"></path>
                    </svg>
                    <svg class="eye-open" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M1 12s4-7 11-7s11 7 11 7s-4 7-11 7S1 12 1 12Z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                </button>
            </div>

            <button type="submit" class="primary-btn">Guardar alterações</button>
        </form>
    </section>
</section>

<script src="/auditor-app/public/assets/js/auth-password-toggle.js?v=account20260410"></script>

<?php require BASE_PATH . '/app/views/partials/layout_end.php'; ?>
