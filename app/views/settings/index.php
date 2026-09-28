<?php
$title = 'Configurações';
$bodyClass = 'app-body';
$headerSubtitle = $user['name'] ?? '';
require BASE_PATH . '/app/views/partials/layout_start.php';
?>

<section class="hero-card compact-hero">
    <div>
        <div class="hero-kicker">Sistema</div>
        <h1 class="hero-heading">Configurações gerais</h1>
        <p class="hero-text">Define os dados do cabeçalho, logo e informação usada no relatório mensal.</p>
    </div>
</section>

<section class="content-grid single-col">
    <section class="panel-card">
        <?php if (!empty($success)): ?>
            <div class="alert success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="/auditor-app/public/settings" class="form-stack" enctype="multipart/form-data">
            <label>Empresa / Nome no cabeçalho</label>
            <input type="text" name="company_name" value="<?= htmlspecialchars($settings['company_name'] ?? '') ?>" placeholder="Ex.: TimeClock ou nome da empresa">

            <label>Logo do cabeçalho (PNG, JPG ou WEBP)</label>
            <input type="file" name="company_logo" accept=".png,.jpg,.jpeg,.webp">

            <?php if (!empty($settings['company_logo_path'])): ?>
                <div class="preview-list">
                    <div><strong>Logo atual:</strong></div>
                    <div style="margin-top:8px;">
                        <img src="<?= htmlspecialchars($settings['company_logo_path']) ?>" alt="Logo atual" style="max-height:80px; max-width:240px; object-fit:contain; display:block;">
                    </div>
                </div>

                <label class="checkbox-line">
                    <input type="checkbox" name="delete_company_logo" value="1">
                    <span>Excluir logo atual</span>
                </label>
            <?php endif; ?>

            <hr class="soft-separator">

            <label>Trabalhador(a)</label>
            <input type="text" name="auditor_name" value="<?= htmlspecialchars($settings['auditor_name'] ?? '') ?>">

            <label>Email destino</label>
            <input type="email" name="email_to" value="<?= htmlspecialchars($settings['email_to'] ?? '') ?>">

            <label>Hora semanal</label>
            <input type="text" name="weekly_hours" value="<?= htmlspecialchars($settings['weekly_hours'] ?? '40 horas') ?>">

            <label>Horário de trabalho</label>
            <input type="text" name="work_schedule" value="<?= htmlspecialchars($settings['work_schedule'] ?? 'Das 8:30 às 13:00 e das 14:00 às 17:30') ?>">

            <label>Local</label>
            <input type="text" name="work_location" value="<?= htmlspecialchars($settings['work_location'] ?? '') ?>">

            <label>Matrícula viatura</label>
            <input type="text" name="vehicle_plate" value="<?= htmlspecialchars($settings['vehicle_plate'] ?? '') ?>">

            <label>Nº empregado</label>
            <input type="text" name="employee_number" value="<?= htmlspecialchars($settings['employee_number'] ?? '') ?>">

            <label>Categoria profissional</label>
            <input type="text" name="professional_category" value="<?= htmlspecialchars($settings['professional_category'] ?? 'Operador registo de dados') ?>">

            <label>Mensagem final</label>
            <input type="text" name="success_message" value="<?= htmlspecialchars($settings['success_message'] ?? 'Dia finalizado. Bom descanso e até amanhã.') ?>">

            <hr class="soft-separator">

            <label class="checkbox-line">
                <input type="checkbox" name="seed_north_stores" value="1">
                <span>Carregar base inicial de lojas do norte / centro-norte</span>
            </label>

            <button type="submit" class="primary-btn">Guardar configurações</button>
        </form>
    </section>
</section>

<?php require BASE_PATH . '/app/views/partials/layout_end.php'; ?>
