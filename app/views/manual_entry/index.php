<?php
$title = 'Lançamento Manual';
$bodyClass = 'app-body';
$headerSubtitle = $user['name'] ?? '';
require BASE_PATH . '/app/views/partials/layout_start.php';

$manualOldAuditIds = $old['audit_type_ids'] ?? [];

if (!is_array($manualOldAuditIds)) {
    $manualOldAuditIds = [];
}

$manualOldAuditIds = array_map(
    'intval',
    $manualOldAuditIds
);
?>

<section class="manual-page-wrap">
    <section class="manual-page-head">
        <div>
            <div class="manual-kicker">Correção de registo</div>
            <h1>Lançamento manual</h1>
            <p>
                Regista um dia completo mantendo o mesmo padrão
                visual do TimeClock.
            </p>
        </div>
    </section>

    <?php if (!empty($success)): ?>
        <div class="alert success">
            <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="alert">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form
        method="POST"
        action="/auditor-app/public/manual-entry/store"
        class="manual-entry-card"
        id="manual-entry-form"
    >
        <section class="manual-section">
            <div class="manual-section-head">
                <div>
                    <span class="manual-section-step">01</span>
                    <h2>Dia e lojas</h2>
                </div>

                <p>
                    Adiciona as lojas na ordem em que foram visitadas.
                </p>
            </div>

            <label for="manual-date">Data</label>
            <input
                type="date"
                id="manual-date"
                name="date"
                value="<?= htmlspecialchars(
                    $old['date'] ?? date('Y-m-d')
                ) ?>"
                required
            >

            <label for="manual-store-search">
                Lojas visitadas por sequência
            </label>

            <div class="manual-store-picker">
                <div class="manual-store-search-wrap">
                    <input
                        type="text"
                        id="manual-store-search"
                        placeholder="Digite nome, marca ou cidade"
                        autocomplete="off"
                        inputmode="search"
                    >

                    <button
                        type="button"
                        class="manual-store-clear"
                        id="manual-store-clear"
                        aria-label="Limpar pesquisa"
                    >
                        ×
                    </button>

                    <div
                        class="manual-store-suggestions"
                        id="manual-store-suggestions"
                        hidden
                    ></div>
                </div>

                <div
                    class="manual-store-sequence"
                    id="manual-store-sequence"
                ></div>
            </div>

            <input
                type="hidden"
                name="stores_sequence"
                id="manual-stores-sequence-value"
                value="<?= htmlspecialchars(
                    $old['stores_sequence'] ?? ''
                ) ?>"
            >

            <div class="manual-field-help">
                A sequência acima é a que será enviada para a Folha Mensal.
            </div>

            <script
                type="application/json"
                id="manual-store-data"
            >
                <?= json_encode(
                    array_map(
                        static function (array $store): array {
                            $brand = trim(
                                (string) ($store['brand'] ?? '')
                            );

                            $name = trim(
                                (string) ($store['name'] ?? '')
                            );

                            $city = trim(
                                (string) ($store['city'] ?? '')
                            );

                            $district = trim(
                                (string) ($store['district'] ?? '')
                            );

                            $reportLabel = $name;

                            if (
                                $brand !== '' &&
                                $name !== '' &&
                                stripos($name, $brand) === false
                            ) {
                                $reportLabel =
                                    $brand . ' - ' . $name;
                            } elseif (
                                $reportLabel === '' &&
                                $brand !== ''
                            ) {
                                $reportLabel = $brand;
                            }

                            $location =
                                $city !== ''
                                    ? $city
                                    : $district;

                            $searchLabel =
                                $reportLabel .
                                (
                                    $location !== ''
                                        ? ' • ' . $location
                                        : ''
                                );

                            return [
                                'id' => (int) $store['id'],
                                'brand' => $brand,
                                'name' => $name,
                                'city' => $city,
                                'district' => $district,
                                'report_label' => $reportLabel,
                                'search_label' => $searchLabel,
                            ];
                        },
                        $stores
                    ),
                    JSON_UNESCAPED_UNICODE |
                    JSON_UNESCAPED_SLASHES |
                    JSON_HEX_TAG |
                    JSON_HEX_AMP |
                    JSON_HEX_APOS |
                    JSON_HEX_QUOT
                ) ?>
            </script>
        </section>

        <section class="manual-section">
            <div class="manual-section-head">
                <div>
                    <span class="manual-section-step">02</span>
                    <h2>Horário</h2>
                </div>

                <p>
                    Entrada e saída são obrigatórias.
                    O almoço é opcional.
                </p>
            </div>

            <div class="manual-time-grid">
                <div>
                    <label for="manual-entry-time">Entrada</label>
                    <input
                        type="time"
                        id="manual-entry-time"
                        name="entry_time"
                        value="<?= htmlspecialchars(
                            $old['entry_time'] ?? ''
                        ) ?>"
                        required
                    >
                </div>

                <div>
                    <label for="manual-exit-time">Saída</label>
                    <input
                        type="time"
                        id="manual-exit-time"
                        name="exit_time"
                        value="<?= htmlspecialchars(
                            $old['exit_time'] ?? ''
                        ) ?>"
                        required
                    >
                </div>

                <div>
                    <label for="manual-lunch-start">
                        Início do almoço
                    </label>

                    <input
                        type="time"
                        id="manual-lunch-start"
                        name="lunch_start"
                        value="<?= htmlspecialchars(
                            $old['lunch_start'] ?? ''
                        ) ?>"
                    >
                </div>

                <div>
                    <label for="manual-lunch-end">
                        Fim do almoço
                    </label>

                    <input
                        type="time"
                        id="manual-lunch-end"
                        name="lunch_end"
                        value="<?= htmlspecialchars(
                            $old['lunch_end'] ?? ''
                        ) ?>"
                    >
                </div>
            </div>
        </section>

        <section class="manual-section">
            <div class="manual-section-head">
                <div>
                    <span class="manual-section-step">03</span>
                    <h2>Auditoria</h2>
                </div>

                <p>
                    Selecione 1 ou até 2 tipos realizados nesse dia.
                </p>
            </div>

            <div
                class="manual-audit-grid"
                id="manual-audit-grid"
            >
                <?php foreach ($auditTypes as $auditType): ?>
                    <?php
                    $auditId = (int) $auditType['id'];
                    ?>

                    <label class="manual-audit-option">
                        <input
                            type="checkbox"
                            name="audit_type_ids[]"
                            value="<?= $auditId ?>"
                            class="manual-audit-checkbox"
                            <?= in_array(
                                $auditId,
                                $manualOldAuditIds,
                                true
                            ) ? 'checked' : '' ?>
                        >

                        <span>
                            <?= htmlspecialchars(
                                $auditType['name']
                            ) ?>
                        </span>
                    </label>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="manual-section">
            <div class="manual-section-head">
                <div>
                    <span class="manual-section-step">04</span>
                    <h2>Observação</h2>
                </div>

                <p>Opcional.</p>
            </div>

            <textarea
                name="notes"
                rows="4"
                placeholder="Observações do dia, resumo ou notas importantes..."
            ><?= htmlspecialchars($old['notes'] ?? '') ?></textarea>
        </section>

        <div class="manual-submit-row">
            <button
                type="submit"
                class="primary-btn clean-primary-btn manual-submit-btn"
            >
                Guardar lançamento manual
            </button>
        </div>
    </form>
</section>

<style>
.manual-page-wrap {
    width: min(100%, 860px);
    margin: 0 auto;
    padding-bottom: 32px;
}

.manual-page-head {
    margin-bottom: 18px;
}

.manual-kicker {
    margin-bottom: 6px;
    color: #64748b;
    font-size: 12px;
    font-weight: 800;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.manual-page-head h1 {
    margin: 0;
    color: #0f172a;
    font-size: clamp(28px, 5vw, 38px);
    line-height: 1.05;
}

.manual-page-head p {
    max-width: 620px;
    margin: 9px 0 0;
    color: #64748b;
    font-size: 15px;
    line-height: 1.55;
}

.manual-entry-card {
    display: grid;
    gap: 16px;
}

.manual-section {
    padding: 20px;
    border: 1px solid #e2e8f0;
    border-radius: 20px;
    background: #ffffff;
}

.manual-section-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 20px;
    margin-bottom: 18px;
}

.manual-section-head > div {
    display: flex;
    align-items: center;
    gap: 10px;
}

.manual-section-head h2 {
    margin: 0;
    color: #0f172a;
    font-size: 18px;
}

.manual-section-head p {
    max-width: 360px;
    margin: 1px 0 0;
    color: #64748b;
    font-size: 12px;
    line-height: 1.45;
    text-align: right;
}

.manual-section-step {
    display: inline-grid;
    width: 30px;
    height: 30px;
    place-items: center;
    border-radius: 10px;
    background: #f1f5f9;
    color: #475569;
    font-size: 11px;
    font-weight: 900;
}

.manual-section label {
    display: block;
    margin: 14px 0 7px;
    color: #334155;
    font-size: 13px;
    font-weight: 800;
}

.manual-section input[type="date"],
.manual-section input[type="time"],
.manual-section input[type="text"],
.manual-section textarea {
    width: 100%;
    box-sizing: border-box;
}

.manual-time-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0 14px;
}

.manual-store-search-wrap {
    position: relative;
}

.manual-store-search-wrap input {
    padding-right: 48px;
}

.manual-store-clear {
    position: absolute;
    top: 50%;
    right: 10px;
    width: 34px;
    height: 34px;
    transform: translateY(-50%);
    border: 0;
    border-radius: 10px;
    background: transparent;
    color: #64748b;
    font-size: 25px;
    line-height: 1;
    cursor: pointer;
}

.manual-store-suggestions {
    position: absolute;
    z-index: 40;
    top: calc(100% + 7px);
    left: 0;
    right: 0;
    max-height: 300px;
    overflow-y: auto;
    padding: 7px;
    border: 1px solid #d8dee8;
    border-radius: 16px;
    background: #ffffff;
    box-shadow: 0 18px 40px rgba(15, 23, 42, 0.16);
}

.manual-store-suggestion {
    display: block;
    width: 100%;
    padding: 12px 13px;
    border: 0;
    border-radius: 12px;
    background: transparent;
    text-align: left;
    cursor: pointer;
}

.manual-store-suggestion:hover,
.manual-store-suggestion:focus {
    background: #f1f5f9;
    outline: none;
}

.manual-store-suggestion strong,
.manual-store-suggestion small {
    display: block;
}

.manual-store-suggestion strong {
    color: #0f172a;
    font-size: 14px;
}

.manual-store-suggestion small {
    margin-top: 3px;
    color: #64748b;
    font-size: 12px;
}

.manual-store-empty {
    padding: 12px 13px;
    color: #64748b;
    font-size: 13px;
}

.manual-store-sequence {
    display: grid;
    gap: 8px;
    margin-top: 12px;
}

.manual-store-sequence:empty {
    display: none;
}

.manual-sequence-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 11px;
    border: 1px solid #e2e8f0;
    border-radius: 13px;
    background: #f8fafc;
}

.manual-sequence-number {
    display: inline-grid;
    flex: 0 0 auto;
    width: 26px;
    height: 26px;
    place-items: center;
    border-radius: 9px;
    background: #ffffff;
    color: #475569;
    font-size: 11px;
    font-weight: 900;
}

.manual-sequence-label {
    min-width: 0;
    flex: 1;
    color: #0f172a;
    font-size: 13px;
    font-weight: 750;
}

.manual-sequence-actions {
    display: flex;
    flex: 0 0 auto;
    gap: 4px;
}

.manual-sequence-actions button {
    width: 30px;
    height: 30px;
    padding: 0;
    border: 1px solid #d8dee8;
    border-radius: 9px;
    background: #ffffff;
    color: #475569;
    font-size: 14px;
    cursor: pointer;
}

.manual-sequence-actions button:disabled {
    opacity: 0.35;
    cursor: default;
}

.manual-field-help {
    margin-top: 8px;
    color: #64748b;
    font-size: 11px;
    line-height: 1.4;
}

.manual-audit-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 10px;
}

.manual-section .manual-audit-option {
    display: flex;
    align-items: center;
    gap: 11px;
    margin: 0;
    padding: 13px 14px;
    border: 1px solid #d8dee8;
    border-radius: 14px;
    background: #ffffff;
    color: #111827;
    cursor: pointer;
}

.manual-audit-option:has(input:checked) {
    border-color: #94a3b8;
    background: #f8fafc;
}

.manual-audit-option input {
    width: 18px;
    height: 18px;
    margin: 0;
    accent-color: #334155;
}

.manual-audit-option:has(input:disabled) {
    opacity: 0.5;
    cursor: not-allowed;
}

.manual-submit-row {
    display: flex;
    justify-content: flex-end;
}

.manual-submit-btn {
    min-width: 240px;
}

@media (max-width: 680px) {
    .manual-section {
        padding: 17px;
        border-radius: 18px;
    }

    .manual-section-head {
        display: block;
    }

    .manual-section-head p {
        margin-top: 8px;
        text-align: left;
    }

    .manual-time-grid,
    .manual-audit-grid {
        grid-template-columns: 1fr;
    }

    .manual-submit-row,
    .manual-submit-btn {
        width: 100%;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const storeDataNode =
        document.getElementById('manual-store-data');

    const storeSearch =
        document.getElementById('manual-store-search');

    const storeClear =
        document.getElementById('manual-store-clear');

    const suggestions =
        document.getElementById('manual-store-suggestions');

    const sequenceWrap =
        document.getElementById('manual-store-sequence');

    const hiddenSequence =
        document.getElementById('manual-stores-sequence-value');

    const form =
        document.getElementById('manual-entry-form');

    const auditCheckboxes =
        Array.from(
            document.querySelectorAll(
                '.manual-audit-checkbox'
            )
        );

    let stores = [];
    let sequence = [];

    try {
        stores = storeDataNode
            ? JSON.parse(storeDataNode.textContent || '[]')
            : [];
    } catch (error) {
        stores = [];
    }

    function normalize(value) {
        return String(value || '')
            .trim()
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '');
    }

    function closeSuggestions() {
        if (!suggestions) {
            return;
        }

        suggestions.hidden = true;
        suggestions.innerHTML = '';
    }

    function syncHiddenSequence() {
        if (!hiddenSequence) {
            return;
        }

        hiddenSequence.value =
            sequence
                .map(function (item) {
                    return item.label;
                })
                .filter(Boolean)
                .join(' / ');
    }

    function moveSequenceItem(fromIndex, toIndex) {
        if (
            toIndex < 0 ||
            toIndex >= sequence.length
        ) {
            return;
        }

        const item =
            sequence.splice(fromIndex, 1)[0];

        sequence.splice(toIndex, 0, item);

        renderSequence();
    }

    function removeSequenceItem(index) {
        sequence.splice(index, 1);
        renderSequence();
    }

    function renderSequence() {
        if (!sequenceWrap) {
            return;
        }

        sequenceWrap.innerHTML = '';

        sequence.forEach(function (item, index) {
            const row =
                document.createElement('div');

            row.className =
                'manual-sequence-item';

            const number =
                document.createElement('span');

            number.className =
                'manual-sequence-number';

            number.textContent =
                String(index + 1);

            const label =
                document.createElement('span');

            label.className =
                'manual-sequence-label';

            label.textContent =
                item.label;

            const actions =
                document.createElement('div');

            actions.className =
                'manual-sequence-actions';

            const up =
                document.createElement('button');

            up.type = 'button';
            up.textContent = '↑';
            up.title = 'Subir';
            up.disabled = index === 0;

            up.addEventListener(
                'click',
                function () {
                    moveSequenceItem(
                        index,
                        index - 1
                    );
                }
            );

            const down =
                document.createElement('button');

            down.type = 'button';
            down.textContent = '↓';
            down.title = 'Descer';
            down.disabled =
                index === sequence.length - 1;

            down.addEventListener(
                'click',
                function () {
                    moveSequenceItem(
                        index,
                        index + 1
                    );
                }
            );

            const remove =
                document.createElement('button');

            remove.type = 'button';
            remove.textContent = '×';
            remove.title = 'Remover';

            remove.addEventListener(
                'click',
                function () {
                    removeSequenceItem(index);
                }
            );

            actions.append(
                up,
                down,
                remove
            );

            row.append(
                number,
                label,
                actions
            );

            sequenceWrap.appendChild(row);
        });

        syncHiddenSequence();
    }

    function addStore(store) {
        const label =
            String(
                store.report_label ||
                store.name ||
                ''
            ).trim();

        if (label === '') {
            return;
        }

        sequence.push({
            id: Number(store.id || 0),
            label: label,
        });

        renderSequence();

        if (storeSearch) {
            storeSearch.value = '';
            storeSearch.focus();
        }

        closeSuggestions();
    }

    function renderSuggestions() {
        if (
            !storeSearch ||
            !suggestions
        ) {
            return;
        }

        const query =
            normalize(storeSearch.value);

        suggestions.innerHTML = '';

        if (query.length < 1) {
            closeSuggestions();
            return;
        }

        const matches =
            stores
                .filter(function (store) {
                    const haystack =
                        normalize(
                            [
                                store.brand,
                                store.name,
                                store.city,
                                store.district,
                                store.search_label,
                            ]
                                .filter(Boolean)
                                .join(' ')
                        );

                    return haystack.includes(query);
                })
                .slice(0, 20);

        if (matches.length === 0) {
            const empty =
                document.createElement('div');

            empty.className =
                'manual-store-empty';

            empty.textContent =
                'Nenhuma loja encontrada.';

            suggestions.appendChild(empty);
            suggestions.hidden = false;
            return;
        }

        matches.forEach(function (store) {
            const button =
                document.createElement('button');

            button.type = 'button';
            button.className =
                'manual-store-suggestion';

            const title =
                document.createElement('strong');

            title.textContent =
                store.report_label ||
                store.name ||
                'Loja';

            const location =
                document.createElement('small');

            location.textContent =
                [
                    store.city,
                    store.district,
                ]
                    .filter(Boolean)
                    .join(' • ');

            button.appendChild(title);

            if (location.textContent !== '') {
                button.appendChild(location);
            }

            button.addEventListener(
                'click',
                function () {
                    addStore(store);
                }
            );

            suggestions.appendChild(button);
        });

        suggestions.hidden = false;
    }

    const existingSequence =
        String(hiddenSequence?.value || '')
            .split(/\s*\/\s*/)
            .map(function (label) {
                return label.trim();
            })
            .filter(Boolean);

    sequence =
        existingSequence.map(function (label) {
            return {
                id: 0,
                label: label,
            };
        });

    renderSequence();

    storeSearch?.addEventListener(
        'input',
        renderSuggestions
    );

    storeSearch?.addEventListener(
        'focus',
        function () {
            if (storeSearch.value.trim() !== '') {
                renderSuggestions();
            }
        }
    );

    storeClear?.addEventListener(
        'click',
        function () {
            if (storeSearch) {
                storeSearch.value = '';
                storeSearch.focus();
            }

            closeSuggestions();
        }
    );

    document.addEventListener(
        'click',
        function (event) {
            if (
                !event.target.closest(
                    '.manual-store-search-wrap'
                )
            ) {
                closeSuggestions();
            }
        }
    );

    function selectedAudits() {
        return auditCheckboxes.filter(
            function (checkbox) {
                return checkbox.checked;
            }
        );
    }

    function syncAuditLimit() {
        const selected =
            selectedAudits();

        const limitReached =
            selected.length >= 2;

        auditCheckboxes.forEach(
            function (checkbox) {
                checkbox.disabled =
                    limitReached &&
                    !checkbox.checked;
            }
        );
    }

    auditCheckboxes.forEach(
        function (checkbox) {
            checkbox.addEventListener(
                'change',
                syncAuditLimit
            );
        }
    );

    syncAuditLimit();

    form?.addEventListener(
        'submit',
        function (event) {
            syncHiddenSequence();

            const selected =
                selectedAudits();

            if (selected.length < 1) {
                event.preventDefault();

                alert(
                    'Selecione pelo menos um tipo de auditoria.'
                );

                auditCheckboxes[0]?.focus();
                return;
            }

            if (selected.length > 2) {
                event.preventDefault();

                alert(
                    'Selecione no máximo dois tipos de auditoria.'
                );
            }
        }
    );
});
</script>

<?php
require BASE_PATH . '/app/views/partials/layout_end.php';
?>