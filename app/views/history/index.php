<?php
$title = 'Histórico';
$bodyClass = 'app-body';
$headerSubtitle = $user['name'] ?? '';
require BASE_PATH . '/app/views/partials/layout_start.php';
?>

<section class="hero-card compact-hero">
    <div>
        <div class="hero-kicker">Consulta</div>
        <h1 class="hero-heading">Histórico de marcações</h1>
        <p class="hero-text">Vê, corrige, apaga e reabre dias quando necessário.</p>
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

        <form method="GET" action="/auditor-app/public/history" class="form-inline-wrap">
            <div class="field-grow">
                <label>Data</label>
                <input type="date" name="date" value="<?= htmlspecialchars($date) ?>">
            </div>
            <div class="field-fixed">
                <button type="submit" class="primary-btn">Ver</button>
            </div>
        </form>

        <?php if (!empty($workDay) && ($workDay['status'] ?? '') === 'finished'): ?>
            <form method="POST" action="/auditor-app/public/history/reopen-day" style="margin-top:12px;">
                <input type="hidden" name="date" value="<?= htmlspecialchars($date) ?>">
                <button type="submit" class="secondary-btn" style="width:100%;">Reabrir dia</button>
            </form>
        <?php endif; ?>

        <?php if (!empty($workDay)): ?>
            <form method="POST" action="/auditor-app/public/history/delete-day" style="margin-top:12px;" onsubmit="return confirm('Apagar todos os dados deste dia?');">
                <input type="hidden" name="date" value="<?= htmlspecialchars($date) ?>">
                <button type="submit" class="secondary-btn" style="width:100%; background:#b42318; color:#fff; border-color:#b42318;">
                    Apagar dados do dia
                </button>
            </form>
        <?php endif; ?>
    </section>

    <section class="panel-card">
        <div class="panel-head">
            <div>
                <div class="panel-kicker">Registos</div>
                <h2>Marcações do dia</h2>
            </div>
        </div>

        <?php if (empty($entries)): ?>
            <p class="muted">Sem registos nesta data.</p>
        <?php else: ?>
            <div class="entry-list">
                <?php foreach ($entries as $entry): ?>
                    <div class="entry-card">
                        <div class="entry-card-top">
                            <div>
                                <strong><?= htmlspecialchars($entry['entry_type']) ?></strong>
                                <div class="entry-time"><?= htmlspecialchars(date('H:i', strtotime($entry['entry_time']))) ?></div>
                            </div>
                            <div class="entry-tags">
                                <span><?= htmlspecialchars($entry['store_name'] ?? '-') ?></span>
                                <span><?= htmlspecialchars($entry['audit_type_name'] ?? '-') ?></span>
                            </div>
                        </div>

                        <div class="history-actions">
                            <a class="mini-action" href="/auditor-app/public/history/edit?id=<?= (int)$entry['id'] ?>">Editar</a>

                            <form method="POST" action="/auditor-app/public/history/delete" onsubmit="return confirm('Apagar este registo?');">
                                <input type="hidden" name="id" value="<?= (int)$entry['id'] ?>">
                                <button type="submit" class="mini-action danger">Apagar</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if (
                !empty($workDay) &&
                !empty($kmRouteStores)
            ): ?>
                <?php
                $historyKmButtonMeters =
                    (int) (
                        $kmMileage['distance_meters'] ??
                        0
                    );

                $historyKmButtonLabel =
                    $historyKmButtonMeters > 0
                        ? '✓ KM: ' .
                            number_format(
                                $historyKmButtonMeters / 1000,
                                1,
                                ',',
                                ''
                            ) .
                            ' km — Recalcular'
                        : '+ Calcular KM';
                ?>

                <div class="history-km-section">
                    <div class="history-km-section-head">
                        <strong>
                            Quilometragem do dia
                        </strong>

                        <small>
                            As lojas deste dia serão usadas
                            automaticamente para calcular a rota.
                        </small>
                    </div>

                    <button
                        type="button"
                        class="km-add-trigger"
                        id="history-km-add-open"
                    >
                        <?= htmlspecialchars(
                            $historyKmButtonLabel
                        ) ?>
                    </button>
                </div>
            <?php endif; ?>

        <?php endif; ?>
    </section>
</section>

<?php if (
    !empty($workDay) &&
    !empty($kmRouteStores)
): ?>
    <?php
    require BASE_PATH .
        '/app/views/history/km_modal.php';
    ?>
<?php endif; ?>

<?php require BASE_PATH . '/app/views/partials/layout_end.php'; ?>