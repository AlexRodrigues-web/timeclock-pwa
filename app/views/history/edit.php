<?php
$title = 'Editar registo';
$bodyClass = 'app-body';
$headerSubtitle = $user['name'] ?? '';
require BASE_PATH . '/app/views/partials/layout_start.php';

$historySelectedStoreLabel = '';

foreach ($stores as $store) {
    if (
        (string) ($entry['store_id'] ?? '') ===
        (string) $store['id']
    ) {
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

        $location =
            $city !== ''
                ? $city
                : $district;

        $historySelectedStoreLabel =
            ($location !== ''
                ? $location . ' • '
                : '') .
            ($brand !== ''
                ? $brand . ' - '
                : '') .
            $name;

        break;
    }
}
?>

<section class="hero-card compact-hero">
    <div>
        <div class="hero-kicker">Correção</div>
        <h1 class="hero-heading">Editar marcação</h1>
        <p class="hero-text">
            Ajusta hora, loja, tipo de auditoria e observações.
        </p>
    </div>
</section>

<section class="content-grid single-col">
    <section class="panel-card">
        <form
            method="POST"
            action="/auditor-app/public/history/edit"
            class="form-stack"
            id="history-edit-form"
        >
            <input
                type="hidden"
                name="id"
                value="<?= (int) $entry['id'] ?>"
            >

            <label for="history-store-search">Loja</label>

            <div
                class="history-store-autocomplete"
                id="history-store-autocomplete"
            >
                <input
                    type="text"
                    id="history-store-search"
                    value="<?= htmlspecialchars(
                        $historySelectedStoreLabel
                    ) ?>"
                    placeholder="Sem loja — digite nome, marca ou cidade"
                    autocomplete="off"
                    inputmode="search"
                >

                <button
                    type="button"
                    class="history-store-clear"
                    id="history-store-clear"
                    aria-label="Remover loja"
                >
                    ×
                </button>

                <input
                    type="hidden"
                    name="store_id"
                    id="history-store-id"
                    value="<?= htmlspecialchars(
                        $entry['store_id'] ?? ''
                    ) ?>"
                >

                <div
                    class="history-store-suggestions"
                    id="history-store-suggestions"
                    hidden
                ></div>
            </div>

            <div class="history-store-help">
                Digite e selecione uma loja da lista.
                Use × para guardar esta marcação sem loja.
            </div>

            <script
                type="application/json"
                id="history-store-data"
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
                                $city !== ''
                                    ? $city
                                    : $district;

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

            <?php
            /*
             * AUDIT_HISTORY_MULTI_2H2B
             */
            $historyAuditIds =
                $selectedAuditTypeIds ??
                [];

            if (!is_array($historyAuditIds)) {
                $historyAuditIds = [];
            }

            $historyAuditIds =
                array_values(
                    array_unique(
                        array_map(
                            'intval',
                            $historyAuditIds
                        )
                    )
                );
            ?>

            <label>Tipo de auditoria</label>

            <div
                class="audit-type-multi"
                id="history-audit-type-multi"
            >
                <?php foreach ($auditTypes as $auditType): ?>
                    <?php
                    $auditId =
                        (int) $auditType['id'];
                    ?>

                    <label class="audit-type-option">
                        <input
                            type="checkbox"
                            name="audit_type_ids[]"
                            value="<?= $auditId ?>"
                            class="history-audit-checkbox"
                            <?= in_array(
                                $auditId,
                                $historyAuditIds,
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

            <small class="muted">
                Selecione até 2 tipos de auditoria.
                Deixe todos desmarcados para “Sem tipo”.
            </small>

            <script>
            document.addEventListener(
                'DOMContentLoaded',
                function () {
                    const boxes =
                        Array.from(
                            document.querySelectorAll(
                                '.history-audit-checkbox'
                            )
                        );

                    boxes.forEach(
                        function (box) {
                            box.addEventListener(
                                'change',
                                function () {
                                    const selected =
                                        boxes.filter(
                                            function (item) {
                                                return item.checked;
                                            }
                                        );

                                    if (selected.length > 2) {
                                        this.checked = false;

                                        alert(
                                            'Selecione no máximo dois tipos de auditoria.'
                                        );
                                    }
                                }
                            );
                        }
                    );
                }
            );
            </script>

            <label>Hora</label>

            <input
                type="time"
                name="entry_clock"
                value="<?= htmlspecialchars(
                    date(
                        'H:i',
                        strtotime($entry['entry_time'])
                    )
                ) ?>"
                required
            >

            <label>Observações</label>

            <input
                type="text"
                name="notes"
                value="<?= htmlspecialchars(
                    $entry['notes'] ?? ''
                ) ?>"
            >

            <button
                type="submit"
                class="primary-btn"
            >
                Guardar alterações
            </button>
        </form>
    </section>
</section>

<style>
.history-store-autocomplete {
    position: relative;
    width: 100%;
}

.history-store-autocomplete > input[type="text"] {
    width: 100%;
    box-sizing: border-box;
    padding-right: 50px;
}

.history-store-clear {
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

.history-store-suggestions {
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

.history-store-suggestion,
.history-store-empty {
    width: 100%;
    padding: 12px 13px;
    border-radius: 12px;
    text-align: left;
}

.history-store-suggestion {
    display: block;
    border: 0;
    background: transparent;
    cursor: pointer;
}

.history-store-suggestion:hover,
.history-store-suggestion:focus {
    background: #f1f5f9;
    outline: none;
}

.history-store-suggestion strong,
.history-store-suggestion small {
    display: block;
}

.history-store-suggestion strong {
    color: #0f172a;
    font-size: 14px;
}

.history-store-suggestion small {
    margin-top: 3px;
    color: #64748b;
    font-size: 12px;
}

.history-store-empty {
    color: #64748b;
    font-size: 13px;
}

.history-store-help {
    margin-top: -6px;
    color: #64748b;
    font-size: 11px;
    line-height: 1.4;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form =
        document.getElementById('history-edit-form');

    const search =
        document.getElementById('history-store-search');

    const storeId =
        document.getElementById('history-store-id');

    const clear =
        document.getElementById('history-store-clear');

    const suggestions =
        document.getElementById('history-store-suggestions');

    const dataNode =
        document.getElementById('history-store-data');

    let stores = [];

    try {
        stores = dataNode
            ? JSON.parse(dataNode.textContent || '[]')
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

    function selectStore(store) {
        if (!search || !storeId) {
            return;
        }

        search.value = store.label;
        storeId.value = String(store.id);

        closeSuggestions();
    }

    function renderSuggestions() {
        if (
            !search ||
            !storeId ||
            !suggestions
        ) {
            return;
        }

        const originalQuery =
            search.value.trim();

        const query =
            normalize(originalQuery);

        storeId.value = '';
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
                                store.name,
                                store.brand,
                                store.city,
                                store.district,
                                store.label,
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
                'history-store-empty';

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
                'history-store-suggestion';

            const title =
                document.createElement('strong');

            title.textContent =
                [store.brand, store.name]
                    .filter(Boolean)
                    .join(' - ');

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
                    selectStore(store);
                }
            );

            suggestions.appendChild(button);
        });

        suggestions.hidden = false;
    }

    search?.addEventListener(
        'input',
        renderSuggestions
    );

    search?.addEventListener(
        'focus',
        function () {
            if (search.value.trim() !== '') {
                renderSuggestions();
            }
        }
    );

    clear?.addEventListener(
        'click',
        function () {
            if (search) {
                search.value = '';
                search.focus();
            }

            if (storeId) {
                storeId.value = '';
            }

            closeSuggestions();
        }
    );

    document.addEventListener(
        'click',
        function (event) {
            if (
                !event.target.closest(
                    '#history-store-autocomplete'
                )
            ) {
                closeSuggestions();
            }
        }
    );

    form?.addEventListener(
        'submit',
        function (event) {
            if (
                search &&
                storeId &&
                search.value.trim() !== '' &&
                storeId.value.trim() === ''
            ) {
                event.preventDefault();

                alert(
                    'Selecione uma loja da lista ou use × para deixar sem loja.'
                );

                search.focus();
            }
        }
    );
});
</script>

<?php
require BASE_PATH . '/app/views/partials/layout_end.php';
?>