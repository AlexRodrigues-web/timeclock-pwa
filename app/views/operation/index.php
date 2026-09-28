<?php

$title = 'Operação do Dia';
$bodyClass = 'app-body';
$headerSubtitle = 'Marcação diária';

require BASE_PATH . '/app/views/partials/layout_start.php';

$requiresStore = in_array((int) $step, [1, 3], true);
$selectedStoreLabel = '';

if ($requiresStore) {
    foreach ($stores as $store) {
        if (
            (string) ($old['store_id'] ?? '') ===
            (string) $store['id']
        ) {
            $district = trim((string) ($store['district'] ?? ''));
            $city = trim((string) ($store['city'] ?? ''));
            $brand = trim((string) ($store['brand'] ?? ''));
            $name = trim((string) ($store['name'] ?? ''));

            $location = $city !== '' ? $city : $district;

            $selectedStoreLabel =
                ($location !== '' ? $location . ' • ' : '') .
                ($brand !== '' ? $brand . ' - ' : '') .
                $name;

            break;
        }
    }
}

$shouldOpenQuickStoreModal =
    !empty($quickStoreOld) &&
    empty($isFinished);
?>

<section class="operation-simple-wrap centered-op-wrap">
    <section class="operation-simple-card centered-op-card">
        <div class="operation-topline centered-op-topline no-box-topline">
            <div class="operation-date-block no-box-block">
                <div class="operation-date-label">Data</div>

                <div class="operation-date fancy-date">
                    <?= htmlspecialchars($nowDate) ?>
                </div>
            </div>

            <div class="operation-time-block no-box-block">
                <div class="operation-time-label">Hora atual</div>

                <div
                    class="operation-time fancy-time"
                    id="current-time"
                >
                    <?= htmlspecialchars($nowTime) ?>
                </div>
            </div>
        </div>

        <div class="operation-status centered-status">
            <?= htmlspecialchars($stageMessage) ?>
        </div>

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
            action="/auditor-app/public/entry/store"
            id="entry-form"
            class="form-stack centered-form-stack"
        >
            <?php if ($requiresStore): ?>
                <label for="store-search">Loja</label>

                <div
                    class="store-autocomplete"
                    id="store-autocomplete"
                >
                    <input
                        type="text"
                        id="store-search"
                        value="<?= htmlspecialchars($selectedStoreLabel) ?>"
                        placeholder="Digite o nome, marca ou cidade"
                        autocomplete="off"
                        inputmode="search"
                        <?= !empty($isFinished) ? 'disabled' : 'required' ?>
                    >

                    <button
                        type="button"
                        class="store-autocomplete-clear"
                        id="store-clear"
                        aria-label="Limpar loja"
                        <?= !empty($isFinished) ? 'disabled' : '' ?>
                    >
                        ×
                    </button>

                    <input
                        type="hidden"
                        name="store_id"
                        id="store-id"
                        value="<?= htmlspecialchars($old['store_id'] ?? '') ?>"
                    >

                    <div
                        class="store-suggestions"
                        id="store-suggestions"
                        hidden
                    ></div>
                </div>

                <script
                    type="application/json"
                    id="store-data"
                >
                    <?= json_encode(
                        array_map(
                            static function (array $store): array {
                                $district = trim(
                                    (string) ($store['district'] ?? '')
                                );

                                $city = trim(
                                    (string) ($store['city'] ?? '')
                                );

                                $brand = trim(
                                    (string) ($store['brand'] ?? '')
                                );

                                $name = trim(
                                    (string) ($store['name'] ?? '')
                                );

                                $location =
                                    $city !== '' ? $city : $district;

                                $label =
                                    ($location !== ''
                                        ? $location . ' • '
                                        : '') .
                                    ($brand !== ''
                                        ? $brand . ' - '
                                        : '') .
                                    $name;

                                return [
                                    'id' => (int) $store['id'],
                                    'brand' => $brand,
                                    'name' => $name,
                                    'city' => $city,
                                    'district' => $district,
                                    'label' => $label,
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

                <button
                    type="button"
                    class="link-btn subtle-link centered-link"
                    id="quick-store-open"
                    <?= !empty($isFinished) ? 'disabled' : '' ?>
                >
                    + Cadastrar nova loja
                </button>
            <?php elseif ((int) $step === 2): ?>
                <label>Esta marcação corresponde a</label>

                <div class="choice-box simple-choice-box">
                    <label class="choice-option simple-choice-option">
                        <input
                            type="radio"
                            name="stage2_choice"
                            value="almoco"
                            <?= (
                                ($old['stage2_choice'] ?? 'almoco') ===
                                'almoco'
                            ) ? 'checked' : '' ?>
                            <?= !empty($isFinished) ? 'disabled' : '' ?>
                        >

                        <span>Almoço</span>
                    </label>

                    <label class="choice-option simple-choice-option">
                        <input
                            type="radio"
                            name="stage2_choice"
                            value="saida"
                            <?= (
                                ($old['stage2_choice'] ?? '') ===
                                'saida'
                            ) ? 'checked' : '' ?>
                            <?= !empty($isFinished) ? 'disabled' : '' ?>
                        >

                        <span>Saída</span>
                    </label>
                </div>
            <?php endif; ?>

            <?php if (
                (int) $step === 4 &&
                empty($isFinished)
            ): ?>
                <?php
                $selectedAuditTypeIds =
                    $old['audit_type_ids'] ??
                    [];

                if (!is_array($selectedAuditTypeIds)) {
                    $selectedAuditTypeIds = [];
                }

                $selectedAuditTypeIds =
                    array_map(
                        'intval',
                        $selectedAuditTypeIds
                    );

                if (
                    empty($selectedAuditTypeIds) &&
                    !empty($old['audit_type_id'])
                ) {
                    $selectedAuditTypeIds[] =
                        (int) $old['audit_type_id'];
                }
                ?>

                <label>Tipo de auditoria</label>

                <div
                    class="audit-type-multi"
                    id="audit-type-multi"
                >
                    <?php foreach ($auditTypes as $auditType): ?>
                        <?php
                        $auditOptionId =
                            (int) $auditType['id'];
                        ?>

                        <label class="audit-type-option">
                            <input
                                type="checkbox"
                                name="audit_type_ids[]"
                                value="<?= $auditOptionId ?>"
                                class="audit-type-checkbox"
                                <?= in_array(
                                    $auditOptionId,
                                    $selectedAuditTypeIds,
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

                <div class="audit-type-helper">
                    Selecione 1 ou até 2 tipos de auditoria.
                </div>
            <?php endif; ?>

            <label>Hora</label>

            <div class="time-row clean-time-row">
                <input
                    type="time"
                    name="entry_clock"
                    id="entry_clock"
                    value="<?= htmlspecialchars(
                        $old['entry_clock'] ?? $nowTime
                    ) ?>"
                    required
                    <?= !empty($isFinished) ? 'disabled' : '' ?>
                >

                <button
                    type="button"
                    class="secondary-btn clean-secondary-btn"
                    id="edit-time-btn"
                    <?= !empty($isFinished) ? 'disabled' : '' ?>
                >
                    Editar hora
                </button>
            </div>

            <input
                type="hidden"
                name="notes"
                value=""
            >

            <?php if (
                (int) $step === 4 &&
                empty($isFinished)
            ): ?>
                <?php
                $kmButtonMeters = (int) (
                    $kmMileage['distance_meters'] ??
                    0
                );

                $kmButtonLabel =
                    $kmButtonMeters > 0
                        ? '✓ KM: ' .
                            number_format(
                                $kmButtonMeters / 1000,
                                1,
                                ',',
                                ''
                            ) .
                            ' km'
                        : '+ Adicionar KM';
                ?>

                <button
                    type="button"
                    class="km-add-trigger"
                    id="km-add-open"
                >
                    <?= htmlspecialchars($kmButtonLabel) ?>
                </button>
            <?php endif; ?>
            <button
                type="submit"
                class="primary-btn clean-primary-btn"
                id="main-submit"
                <?= !empty($isFinished) ? 'disabled' : '' ?>
            >
                <?= (
                    (int) $step === 4 ||
                    (
                        (int) $step === 2 &&
                        ($old['stage2_choice'] ?? '') === 'saida'
                    )
                ) ? 'Finalizar dia' : 'Confirmar' ?>
            </button>
        </form>
    </section>
</section>

<?php
require BASE_PATH . '/app/views/operation/km_modal.php';
?>

<?php if ($requiresStore && empty($isFinished)): ?>
    <div
        class="modal"
        id="quick-store-modal"
        aria-hidden="true"
    >
        <div class="modal-card modern-modal">
            <h3>Cadastrar e selecionar loja</h3>

            <p class="quick-store-helper">
                A nova loja ficará automaticamente selecionada
                para esta marcação.
            </p>

            <form
                method="POST"
                action="/auditor-app/public/stores/quick-create"
                id="quick-store-form"
            >
                <input
                    type="hidden"
                    name="return_entry_clock"
                    id="quick-store-entry-clock"
                    value="<?= htmlspecialchars(
                        $old['entry_clock'] ?? $nowTime
                    ) ?>"
                >

                <label for="quick-store-brand">Marca</label>

                <input
                    type="text"
                    name="brand"
                    id="quick-store-brand"
                    list="quick-store-brand-options"
                    value="<?= htmlspecialchars(
                        $quickStoreOld['brand'] ?? ''
                    ) ?>"
                    placeholder="Ex.: Pingo Doce"
                    autocomplete="off"
                    required
                >

                <datalist id="quick-store-brand-options">
                    <option value="Continente">
                    <option value="Continente Modelo">
                    <option value="Continente Bom Dia">
                    <option value="Continente Small">
                    <option value="Pingo Doce">
                    <option value="Mercadona">
                    <option value="Primaprix">
                    <option value="Lidl">
                    <option value="Aldi">
                    <option value="Auchan">
                    <option value="MyAuchan">
                    <option value="Intermarché">
                    <option value="Minipreço">
                    <option value="Froiz">
                    <option value="El Corte Inglés">
                    <option value="E.Leclerc">
                    <option value="Recheio">
                    <option value="Makro">
                </datalist>

                <label for="quick-store-name">
                    Nome da loja
                </label>

                <input
                    type="text"
                    name="name"
                    id="quick-store-name"
                    value="<?= htmlspecialchars(
                        $quickStoreOld['name'] ?? ''
                    ) ?>"
                    placeholder="Ex.: Continente GaiaShopping"
                    autocomplete="off"
                    required
                >

                <label for="quick-store-city">Cidade</label>

                <input
                    type="text"
                    name="city"
                    id="quick-store-city"
                    value="<?= htmlspecialchars(
                        $quickStoreOld['city'] ?? ''
                    ) ?>"
                    placeholder="Ex.: Vila Nova de Gaia"
                    autocomplete="address-level2"
                >

                <label for="quick-store-district">
                    Distrito
                </label>

                <input
                    type="text"
                    name="district"
                    id="quick-store-district"
                    value="<?= htmlspecialchars(
                        $quickStoreOld['district'] ?? ''
                    ) ?>"
                    placeholder="Ex.: Porto"
                    autocomplete="address-level1"
                >

                <div class="modal-actions">
                    <button
                        type="button"
                        class="secondary-btn"
                        id="quick-store-cancel"
                    >
                        Cancelar
                    </button>

                    <button
                        type="submit"
                        class="primary-btn"
                    >
                        Cadastrar e selecionar
                    </button>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>

<?php if (!empty($showFinishedToast)): ?>
    <div
        class="floating-finish-message"
        id="floating-finish-message"
    >
        <div class="floating-finish-card">
            <div class="floating-finish-title">
                Dia finalizado
            </div>

            <div class="floating-finish-text">
                <?= htmlspecialchars($finishedToastMessage) ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<style>
.store-autocomplete {
    position: relative;
    width: 100%;
}

.store-autocomplete input[type="text"] {
    width: 100%;
    padding-right: 52px;
}

.store-autocomplete-clear {
    position: absolute;
    top: 50%;
    right: 16px;
    width: 32px;
    height: 32px;
    border: 0;
    background: transparent;
    color: #6b7280;
    font-size: 28px;
    line-height: 1;
    transform: translateY(-50%);
    cursor: pointer;
}

.store-autocomplete-clear:disabled {
    opacity: 0.35;
    cursor: not-allowed;
}

.store-suggestions {
    position: absolute;
    z-index: 30;
    top: calc(100% + 8px);
    left: 0;
    right: 0;
    max-height: 320px;
    overflow-y: auto;
    overscroll-behavior: contain;
    background: #ffffff;
    border: 1px solid #d8dee8;
    border-radius: 18px;
    box-shadow: 0 18px 40px rgba(15, 23, 42, 0.16);
    padding: 8px;
}

.store-suggestion-item,
.store-suggestion-empty,
.store-suggestion-create {
    width: 100%;
    border: 0;
    text-align: left;
    padding: 14px 16px;
    border-radius: 14px;
    font-size: 16px;
    line-height: 1.35;
}

.store-suggestion-item {
    display: flex;
    flex-direction: column;
    gap: 3px;
    background: transparent;
    color: #111827;
    cursor: pointer;
}

.store-suggestion-title {
    font-weight: 700;
}

.store-suggestion-location {
    color: #64748b;
    font-size: 13px;
}

.store-suggestion-item:hover,
.store-suggestion-item:focus {
    background: #f1f5f9;
    outline: none;
}

.store-suggestion-empty {
    background: transparent;
    color: #6b7280;
}

.store-suggestion-create {
    margin-top: 6px;
    background: #eef2ff;
    color: #111827;
    font-weight: 700;
    cursor: pointer;
}

.store-suggestion-create:hover,
.store-suggestion-create:focus {
    background: #e0e7ff;
    outline: none;
}

.quick-store-helper {
    margin: -4px 0 18px;
    color: #64748b;
    font-size: 14px;
    line-height: 1.45;
}

@media (max-width: 768px) {
    .store-suggestions {
        max-height: 280px;
        border-radius: 16px;
    }

    .store-suggestion-item,
    .store-suggestion-empty,
    .store-suggestion-create {
        font-size: 15px;
        padding: 13px 14px;
    }
}

.audit-type-multi {
    display: grid;
    grid-template-columns: 1fr;
    gap: 10px;
}

.audit-type-option {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 13px 14px;
    border: 1px solid #d8dee8;
    border-radius: 14px;
    background: #ffffff;
    color: #111827;
    font-size: 15px;
    font-weight: 700;
    cursor: pointer;
}

.audit-type-option:has(input:checked) {
    border-color: #94a3b8;
    background: #f8fafc;
}

.audit-type-option input {
    width: 18px;
    height: 18px;
    margin: 0;
    flex: 0 0 auto;
    accent-color: #334155;
}

.audit-type-option:has(input:disabled) {
    opacity: 0.5;
    cursor: not-allowed;
}

.audit-type-helper {
    margin-top: -2px;
    color: #64748b;
    font-size: 12px;
    line-height: 1.35;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const entryForm =
        document.getElementById('entry-form');

    const storeSearch =
        document.getElementById('store-search');

    const storeId =
        document.getElementById('store-id');

    const storeClear =
        document.getElementById('store-clear');

    const storeSuggestions =
        document.getElementById('store-suggestions');

    const storeDataNode =
        document.getElementById('store-data');

    const quickStoreOpen =
        document.getElementById('quick-store-open');

    const quickStoreModal =
        document.getElementById('quick-store-modal');

    const quickStoreCancel =
        document.getElementById('quick-store-cancel');

    const quickStoreForm =
        document.getElementById('quick-store-form');

    const quickStoreName =
        document.getElementById('quick-store-name');

    const quickStoreBrand =
        document.getElementById('quick-store-brand');

    const quickStoreEntryClock =
        document.getElementById('quick-store-entry-clock');

    const entryClock =
        document.getElementById('entry_clock');

    const shouldOpenQuickStoreModal =
        <?= $shouldOpenQuickStoreModal ? 'true' : 'false' ?>;

    let stores = [];

    try {
        stores = storeDataNode
            ? JSON.parse(storeDataNode.textContent || '[]')
            : [];
    } catch (error) {
        stores = [];
    }

    function normalizeStoreText(value) {
        return String(value || '')
            .trim()
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '');
    }

    function detectStoreBrand(value) {
        const normalizedValue =
            normalizeStoreText(value);

        const knownBrands = [
            {
                brand: 'Continente Bom Dia',
                terms: ['continente bom dia'],
            },
            {
                brand: 'Continente Modelo',
                terms: [
                    'continente modelo',
                    'modelo continente',
                ],
            },
            {
                brand: 'Continente Small',
                terms: ['continente small'],
            },
            {
                brand: 'Pingo Doce',
                terms: ['pingo doce', 'pingo'],
            },
            {
                brand: 'Mercadona',
                terms: ['mercadona', 'merca'],
            },
            {
                brand: 'Primaprix',
                terms: [
                    'primaprix',
                    'prima prix',
                    'prima',
                ],
            },
            {
                brand: 'Intermarché',
                terms: ['intermarche'],
            },
            {
                brand: 'El Corte Inglés',
                terms: ['el corte ingles'],
            },
            {
                brand: 'E.Leclerc',
                terms: [
                    'e leclerc',
                    'eleclerc',
                    'leclerc',
                ],
            },
            {
                brand: 'MyAuchan',
                terms: ['myauchan', 'my auchan'],
            },
            {
                brand: 'Continente',
                terms: ['continente'],
            },
            {
                brand: 'Lidl',
                terms: ['lidl'],
            },
            {
                brand: 'Aldi',
                terms: ['aldi'],
            },
            {
                brand: 'Auchan',
                terms: ['auchan'],
            },
            {
                brand: 'Minipreço',
                terms: ['minipreco'],
            },
            {
                brand: 'Froiz',
                terms: ['froiz'],
            },
            {
                brand: 'Recheio',
                terms: ['recheio'],
            },
            {
                brand: 'Makro',
                terms: ['makro'],
            },
        ];

        const match = knownBrands.find(function (item) {
            return item.terms.some(function (term) {
                return normalizedValue.includes(term);
            });
        });

        return match ? match.brand : '';
    }

    function closeStoreSuggestions() {
        if (!storeSuggestions) {
            return;
        }

        storeSuggestions.hidden = true;
        storeSuggestions.innerHTML = '';
    }

    function openQuickStoreModal(prefillName = '') {
        if (!quickStoreModal) {
            return;
        }

        const currentName = quickStoreName
            ? quickStoreName.value.trim()
            : '';

        if (
            quickStoreName &&
            currentName === '' &&
            prefillName.trim() !== ''
        ) {
            quickStoreName.value = prefillName.trim();
        }

        if (
            quickStoreBrand &&
            quickStoreBrand.value.trim() === ''
        ) {
            const detectedBrand =
                detectStoreBrand(
                    quickStoreName?.value ||
                    prefillName
                );

            if (detectedBrand !== '') {
                quickStoreBrand.value =
                    detectedBrand;
            }
        }

        if (quickStoreEntryClock && entryClock) {
            quickStoreEntryClock.value = entryClock.value;
        }

        quickStoreModal.classList.add('show');
        quickStoreModal.setAttribute('aria-hidden', 'false');

        window.setTimeout(function () {
            if (
                quickStoreBrand &&
                quickStoreBrand.value === ''
            ) {
                quickStoreBrand.focus();
                return;
            }

            quickStoreName?.focus();
        }, 50);
    }

    function closeQuickStoreModal() {
        if (!quickStoreModal) {
            return;
        }

        quickStoreModal.classList.remove('show');
        quickStoreModal.setAttribute('aria-hidden', 'true');
    }

    function selectStore(store) {
        if (!storeSearch || !storeId) {
            return;
        }

        storeSearch.value = store.label;
        storeId.value = String(store.id);

        closeStoreSuggestions();
    }

    function findExactStore(value) {
        const normalizedValue =
            normalizeStoreText(value);

        return stores.find(function (store) {
            return normalizeStoreText(store.label) ===
                normalizedValue;
        });
    }

    function getStoreScore(store, query) {
        const name =
            normalizeStoreText(store.name);

        const brand =
            normalizeStoreText(store.brand);

        const city =
            normalizeStoreText(store.city);

        const district =
            normalizeStoreText(store.district);

        const label =
            normalizeStoreText(store.label);

        if (name === query) {
            return 0;
        }

        if (name.startsWith(query)) {
            return 10;
        }

        if (brand === query) {
            return 20;
        }

        if (brand.startsWith(query)) {
            return 30;
        }

        if (city === query || district === query) {
            return 40;
        }

        if (
            city.startsWith(query) ||
            district.startsWith(query)
        ) {
            return 50;
        }

        if (name.includes(query)) {
            return 60;
        }

        if (brand.includes(query)) {
            return 70;
        }

        if (
            city.includes(query) ||
            district.includes(query)
        ) {
            return 80;
        }

        if (label.includes(query)) {
            return 90;
        }

        return 999;
    }

    function createStoreSuggestionItem(store) {
        const item =
            document.createElement('button');

        item.type = 'button';
        item.className = 'store-suggestion-item';

        const title =
            document.createElement('span');

        title.className = 'store-suggestion-title';
        title.textContent =
            [store.brand, store.name]
                .filter(Boolean)
                .join(' - ');

        const location =
            document.createElement('span');

        location.className =
            'store-suggestion-location';

        location.textContent =
            [store.city, store.district]
                .filter(Boolean)
                .join(' • ');

        item.appendChild(title);

        if (location.textContent !== '') {
            item.appendChild(location);
        }

        item.addEventListener(
            'pointerdown',
            function (event) {
                event.preventDefault();
                selectStore(store);
            }
        );

        item.addEventListener(
            'click',
            function () {
                selectStore(store);
            }
        );

        return item;
    }

    function createNewStoreButton(searchText) {
        const button =
            document.createElement('button');

        button.type = 'button';
        button.className =
            'store-suggestion-create';

        button.textContent =
            'Cadastrar e selecionar “' +
            searchText.trim() +
            '”';

        button.addEventListener(
            'pointerdown',
            function (event) {
                event.preventDefault();
                closeStoreSuggestions();
                openQuickStoreModal(searchText);
            }
        );

        button.addEventListener(
            'click',
            function () {
                closeStoreSuggestions();
                openQuickStoreModal(searchText);
            }
        );

        return button;
    }

    function renderStoreSuggestions() {
        if (
            !storeSearch ||
            !storeId ||
            !storeSuggestions
        ) {
            return;
        }

        const originalQuery =
            storeSearch.value.trim();

        const query =
            normalizeStoreText(originalQuery);

        storeId.value = '';

        if (query.length < 1) {
            closeStoreSuggestions();
            return;
        }

        const matches = stores
            .map(function (store) {
                return {
                    store: store,
                    score: getStoreScore(
                        store,
                        query
                    ),
                };
            })
            .filter(function (result) {
                return result.score < 999;
            })
            .sort(function (first, second) {
                if (first.score !== second.score) {
                    return first.score - second.score;
                }

                return String(
                    first.store.name || ''
                ).localeCompare(
                    String(second.store.name || ''),
                    'pt',
                    {
                        sensitivity: 'base',
                    }
                );
            })
            .slice(0, 20)
            .map(function (result) {
                return result.store;
            });

        storeSuggestions.innerHTML = '';

        if (matches.length === 0) {
            const empty =
                document.createElement('div');

            empty.className =
                'store-suggestion-empty';

            empty.textContent =
                'Nenhuma loja encontrada.';

            storeSuggestions.appendChild(empty);
            storeSuggestions.appendChild(
                createNewStoreButton(originalQuery)
            );

            storeSuggestions.hidden = false;
            return;
        }

        matches.forEach(function (store) {
            storeSuggestions.appendChild(
                createStoreSuggestionItem(store)
            );
        });

        storeSuggestions.hidden = false;
    }

    function syncSelectedStore() {
        if (!storeSearch || !storeId) {
            return;
        }

        const selectedStore =
            findExactStore(storeSearch.value);

        storeId.value = selectedStore
            ? String(selectedStore.id)
            : '';
    }

    storeSearch?.addEventListener(
        'input',
        renderStoreSuggestions
    );

    storeSearch?.addEventListener(
        'focus',
        function () {
            renderStoreSuggestions();
        }
    );

    storeSearch?.addEventListener(
        'blur',
        function () {
            window.setTimeout(function () {
                syncSelectedStore();
                closeStoreSuggestions();
            }, 180);
        }
    );

    storeClear?.addEventListener(
        'click',
        function () {
            if (!storeSearch || !storeId) {
                return;
            }

            storeSearch.value = '';
            storeId.value = '';

            closeStoreSuggestions();
            storeSearch.focus();
        }
    );

    quickStoreOpen?.addEventListener(
        'click',
        function () {
            openQuickStoreModal(
                storeSearch?.value || ''
            );
        }
    );

    quickStoreCancel?.addEventListener(
        'click',
        closeQuickStoreModal
    );

    quickStoreModal?.addEventListener(
        'click',
        function (event) {
            if (event.target === quickStoreModal) {
                closeQuickStoreModal();
            }
        }
    );

    quickStoreName?.addEventListener(
        'input',
        function () {
            if (
                !quickStoreBrand ||
                quickStoreBrand.value.trim() !== ''
            ) {
                return;
            }

            const detectedBrand =
                detectStoreBrand(
                    quickStoreName.value
                );

            if (detectedBrand !== '') {
                quickStoreBrand.value =
                    detectedBrand;
            }
        }
    );

    quickStoreForm?.addEventListener(
        'submit',
        function () {
            if (quickStoreEntryClock && entryClock) {
                quickStoreEntryClock.value =
                    entryClock.value;
            }
        }
    );

    entryForm?.addEventListener(
        'submit',
        function (event) {
            if (
                !storeSearch ||
                !storeId ||
                storeSearch.disabled
            ) {
                return;
            }

            syncSelectedStore();

            if (!storeId.value) {
                event.preventDefault();

                alert(
                    'Selecione uma loja válida da lista.'
                );

                storeSearch.focus();
                renderStoreSuggestions();
            }
        }
    );

    document.addEventListener(
        'keydown',
        function (event) {
            if (
                event.key === 'Escape' &&
                quickStoreModal?.classList.contains('show')
            ) {
                closeQuickStoreModal();
            }
        }
    );

    syncSelectedStore();

    if (shouldOpenQuickStoreModal) {
        openQuickStoreModal();
    }
});
</script>


<script>
document.addEventListener(
    'DOMContentLoaded',
    function () {

        const auditCheckboxes =
            Array.from(
                document.querySelectorAll(
                    '.audit-type-checkbox'
                )
            );

        const form =
            document.getElementById(
                'entry-form'
            );

        if (auditCheckboxes.length === 0) {
            return;
        }

        function getSelected() {
            return auditCheckboxes.filter(
                function (checkbox) {
                    return checkbox.checked;
                }
            );
        }

        function syncLimit() {
            const selected =
                getSelected();

            const reached =
                selected.length >= 2;

            auditCheckboxes.forEach(
                function (checkbox) {
                    checkbox.disabled =
                        reached &&
                        !checkbox.checked;
                }
            );
        }

        auditCheckboxes.forEach(
            function (checkbox) {
                checkbox.addEventListener(
                    'change',
                    syncLimit
                );
            }
        );

        form?.addEventListener(
            'submit',
            function (event) {

                const selected =
                    getSelected();

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

        syncLimit();
    }
);
</script>
<?php
require BASE_PATH . '/app/views/partials/layout_end.php';
?>