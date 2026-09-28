<?php

$historyKmOriginValue = trim(
    (string) (
        $kmMileage['origin_address'] ??
        $kmDefaultOrigin ??
        ''
    )
);

$historyKmExistingMeters = (int) (
    $kmMileage['distance_meters'] ??
    0
);

$historyKmExistingDisplay =
    $historyKmExistingMeters > 0
        ? number_format(
            $historyKmExistingMeters / 1000,
            1,
            ',',
            ''
        ) . ' km'
        : '';

$historyKmDateLabel = date(
    'd/m/Y',
    strtotime((string) $date)
);
?>

<div
    class="modal"
    id="history-km-modal"
    aria-hidden="true"
>
    <div class="modal-card modern-modal km-modal-card">

        <div class="km-modal-head">
            <div>
                <div class="km-modal-kicker">
                    Deslocação
                </div>

                <h3>Calcular KM do dia</h3>

                <p>
                    As lojas de
                    <?= htmlspecialchars($historyKmDateLabel) ?>
                    já foram preenchidas automaticamente.
                </p>
            </div>

            <button
                type="button"
                class="km-modal-close"
                id="history-km-modal-close"
                aria-label="Fechar"
            >
                ×
            </button>
        </div>

        <label for="history-km-origin-address">
            Ponto de saída
        </label>

        <input
            type="text"
            id="history-km-origin-address"
            value="<?= htmlspecialchars(
                $historyKmOriginValue
            ) ?>"
            placeholder="Ex.: Rua..., Vila Nova de Gaia"
            autocomplete="street-address"
        >

        <label class="km-default-choice">
            <input
                type="checkbox"
                id="history-km-save-default"
                <?= $historyKmOriginValue !== ''
                    ? 'checked'
                    : '' ?>
            >

            <span>
                Usar este endereço como padrão
            </span>
        </label>

        <div class="km-route-box">
            <strong>Rota do dia</strong>

            <div class="km-route-list">

                <div class="km-route-point">
                    <span class="km-route-dot"></span>

                    <span id="history-km-origin-preview">
                        <?= htmlspecialchars(
                            $historyKmOriginValue !== ''
                                ? $historyKmOriginValue
                                : 'Ponto de saída'
                        ) ?>
                    </span>
                </div>

                <?php foreach (
                    ($kmRouteStores ?? [])
                    as $store
                ): ?>

                    <div class="km-route-line"></div>

                    <div class="km-route-point">
                        <span class="km-route-dot"></span>

                        <span>
                            <?= htmlspecialchars(
                                $store['label'] ??
                                'Loja'
                            ) ?>
                        </span>
                    </div>

                <?php endforeach; ?>

                <div class="km-route-line"></div>

                <div class="km-route-point">
                    <span class="km-route-dot"></span>
                    <span>
                        Retorno ao ponto de saída
                    </span>
                </div>

            </div>
        </div>

        <div
            class="km-result-message <?= $historyKmExistingMeters > 0
                ? 'success'
                : '' ?>"
            id="history-km-result-message"
        >
            <?php if (
                $historyKmExistingMeters > 0
            ): ?>

                <?= htmlspecialchars(
                    $historyKmExistingDisplay
                ) ?>
                já guardados para este dia.

            <?php else: ?>

                O Google Maps calculará
                a distância por estrada.

            <?php endif; ?>
        </div>

        <div class="modal-actions km-modal-actions">

            <button
                type="button"
                class="secondary-btn"
                id="history-km-cancel-btn"
            >
                Cancelar
            </button>

            <button
                type="button"
                class="primary-btn"
                id="history-km-calculate-btn"
            >
                Calcular e guardar
            </button>

        </div>
    </div>
</div>

<style>
.history-km-section {
    margin-top: 12px;
    padding-top: 12px;
    border-top: 1px solid #e2e8f0;
}

.history-km-section-head {
    margin-bottom: 8px;
}

.history-km-section-head strong,
.history-km-section-head small {
    display: block;
}

.history-km-section-head strong {
    color: #0f172a;
    font-size: 13px;
}

.history-km-section-head small {
    margin-top: 2px;
    color: #64748b;
    font-size: 11px;
    line-height: 1.4;
}

.history-km-section .km-add-trigger {
    width: 100%;
    margin-top: 2px;
    padding: 10px 14px;
    border: 1px dashed #cbd5e1;
    border-radius: 14px;
    background: #f8fafc;
    color: #475569;
    font-size: 13px;
    line-height: 1.2;
    font-weight: 750;
    cursor: pointer;
}

.history-km-section .km-add-trigger:hover,
.history-km-section .km-add-trigger:focus {
    background: #f1f5f9;
    border-color: #94a3b8;
    color: #0f172a;
    outline: none;
}

.km-modal-card {
    max-width: 520px;
}

.km-modal-head {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 16px;
    margin-bottom: 18px;
}

.km-modal-head h3 {
    margin: 3px 0 6px;
}

.km-modal-head p {
    margin: 0;
    color: #64748b;
    font-size: 14px;
    line-height: 1.45;
}

.km-modal-kicker {
    font-size: 11px;
    line-height: 1;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #64748b;
}

.km-modal-close {
    flex: 0 0 auto;
    width: 36px;
    height: 36px;
    border: 0;
    border-radius: 50%;
    background: #f1f5f9;
    color: #64748b;
    font-size: 24px;
    line-height: 1;
    cursor: pointer;
}

.km-default-choice {
    display: flex;
    align-items: center;
    gap: 9px;
    margin: 12px 0 16px;
    font-size: 13px;
    font-weight: 700;
    color: #475569;
}

.km-default-choice input {
    width: 17px;
    height: 17px;
}

.km-route-box {
    padding: 14px;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    background: #f8fafc;
}

.km-route-box > strong {
    display: block;
    margin-bottom: 12px;
    font-size: 13px;
    color: #334155;
}

.km-route-list {
    display: flex;
    flex-direction: column;
}

.km-route-point {
    display: flex;
    align-items: center;
    gap: 10px;
    min-width: 0;
    font-size: 13px;
    line-height: 1.35;
    font-weight: 700;
    color: #0f172a;
}

.km-route-dot {
    flex: 0 0 auto;
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #64748b;
}

.km-route-line {
    width: 1px;
    height: 18px;
    margin-left: 4px;
    background: #cbd5e1;
}

.km-result-message {
    margin-top: 14px;
    min-height: 20px;
    font-size: 13px;
    line-height: 1.4;
    font-weight: 700;
    color: #64748b;
}

.km-result-message.success {
    color: #166534;
}

.km-result-message.error {
    color: #b91c1c;
}

.km-modal-actions {
    margin-top: 16px;
}

@media (max-width: 600px) {
    .km-modal-card {
        width: calc(100% - 24px);
    }

    .km-modal-actions {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
    }
}
</style>

<script>
document.addEventListener(
    'DOMContentLoaded',
    function () {

        const openButton =
            document.getElementById(
                'history-km-add-open'
            );

        const modal =
            document.getElementById(
                'history-km-modal'
            );

        const closeButton =
            document.getElementById(
                'history-km-modal-close'
            );

        const cancelButton =
            document.getElementById(
                'history-km-cancel-btn'
            );

        const calculateButton =
            document.getElementById(
                'history-km-calculate-btn'
            );

        const originInput =
            document.getElementById(
                'history-km-origin-address'
            );

        const originPreview =
            document.getElementById(
                'history-km-origin-preview'
            );

        const saveDefault =
            document.getElementById(
                'history-km-save-default'
            );

        const result =
            document.getElementById(
                'history-km-result-message'
            );

        const historyDate =
            <?= json_encode(
                (string) $date,
                JSON_UNESCAPED_UNICODE |
                JSON_UNESCAPED_SLASHES
            ) ?>;

        if (
            !openButton ||
            !modal
        ) {
            return;
        }

        function updateOriginPreview() {

            if (
                !originPreview ||
                !originInput
            ) {
                return;
            }

            const value =
                originInput.value.trim();

            originPreview.textContent =
                value !== ''
                    ? value
                    : 'Ponto de saída';
        }

        function openModal() {

            modal.classList.add('show');

            modal.setAttribute(
                'aria-hidden',
                'false'
            );

            updateOriginPreview();

            window.setTimeout(
                function () {
                    originInput?.focus();
                },
                50
            );
        }

        function closeModal() {

            modal.classList.remove('show');

            modal.setAttribute(
                'aria-hidden',
                'true'
            );
        }

        openButton.addEventListener(
            'click',
            openModal
        );

        closeButton?.addEventListener(
            'click',
            closeModal
        );

        cancelButton?.addEventListener(
            'click',
            closeModal
        );

        originInput?.addEventListener(
            'input',
            updateOriginPreview
        );

        modal.addEventListener(
            'pointerdown',
            function (event) {

                if (event.target === modal) {
                    closeModal();
                }
            }
        );

        document.addEventListener(
            'keydown',
            function (event) {

                if (
                    event.key === 'Escape' &&
                    modal.classList.contains('show')
                ) {
                    closeModal();
                }
            }
        );

        calculateButton?.addEventListener(
            'click',
            async function () {

                if (
                    !originInput ||
                    !result
                ) {
                    return;
                }

                const origin =
                    originInput.value.trim();

                if (origin === '') {

                    result.className =
                        'km-result-message error';

                    result.textContent =
                        'Informe o ponto de saída.';

                    originInput.focus();
                    return;
                }

                calculateButton.disabled = true;

                calculateButton.textContent =
                    'A calcular...';

                result.className =
                    'km-result-message';

                result.textContent =
                    'A calcular a rota no Google Maps...';

                try {

                    const body =
                        new URLSearchParams();

                    body.set(
                        'date',
                        historyDate
                    );

                    body.set(
                        'origin_address',
                        origin
                    );

                    body.set(
                        'save_default',
                        saveDefault?.checked
                            ? '1'
                            : '0'
                    );

                    const response =
                        await fetch(
                            '/auditor-app/public/km/calculate',
                            {
                                method: 'POST',

                                headers: {
                                    'Content-Type':
                                        'application/x-www-form-urlencoded;charset=UTF-8',
                                },

                                body:
                                    body.toString(),
                            }
                        );

                    let data = {};

                    try {
                        data =
                            await response.json();
                    } catch (error) {
                        data = {};
                    }

                    if (
                        !response.ok ||
                        !data.ok
                    ) {
                        throw new Error(
                            data.error ||
                            'Não foi possível calcular os KM.'
                        );
                    }

                    const km =
                        Number(
                            data.distance_km ||
                            0
                        )
                            .toFixed(1)
                            .replace('.', ',');

                    result.className =
                        'km-result-message success';

                    result.textContent =
                        km +
                        ' km guardados na folha mensal.';

                    openButton.textContent =
                        '✓ KM: ' +
                        km +
                        ' km — Recalcular';

                    window.setTimeout(
                        function () {
                            closeModal();
                            openButton.focus();
                        },
                        850
                    );

                } catch (error) {

                    result.className =
                        'km-result-message error';

                    result.textContent =
                        error?.message ||
                        'Não foi possível calcular os KM.';

                } finally {

                    calculateButton.disabled =
                        false;

                    calculateButton.textContent =
                        'Calcular e guardar';
                }
            }
        );
    }
);
</script>