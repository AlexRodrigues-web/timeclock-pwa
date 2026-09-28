<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Visualizar Folha Mensal') ?></title>
    <link rel="stylesheet" href="/auditor-app/public/assets/css/app.css?v=report20260410">
<style id="monthly-sheet-polish">
    /*
     * 2G1 — Polimento conservador da folha mensal.
     * Mantém a estrutura administrativa já conhecida.
     */

    .report-page-enhanced .monthly-sheet-enhanced {
        background: #ffffff;
        border: 1px solid #cfd6df;
        box-shadow:
            0 10px 28px rgba(15, 23, 42, 0.08);
    }

    .report-page-enhanced .sheet-title {
        padding-bottom: 10px;
        color: #111827;
        font-weight: 800;
        letter-spacing: 0.035em;
    }

    .report-page-enhanced .sheet-head-enhanced {
        border-color: #374151;
        background: #fbfcfd;
    }

    .report-page-enhanced .sheet-brand-box {
        background: #ffffff;
    }

    .report-page-enhanced .sheet-meta-pair {
        line-height: 1.35;
    }

    .report-page-enhanced .sheet-meta-pair strong {
        color: #111827;
        font-weight: 800;
    }

    .report-page-enhanced .sheet-meta-pair span {
        color: #374151;
    }

    .report-page-enhanced
    .sheet-table-enhanced {
        border-color: #374151;
    }

    .report-page-enhanced
    .sheet-table-enhanced th {
        background: #f1f3f5;
        color: #111827;
        font-weight: 800;
        letter-spacing: 0.015em;
        border-color: #374151;
    }

    .report-page-enhanced
    .sheet-table-enhanced td {
        border-color: #4b5563;
        color: #111827;
    }

    .report-page-enhanced
    .sheet-table-enhanced
    tbody tr:not(.sheet-row-saturday):not(.sheet-row-sunday):not(.sheet-row-holiday)
    td {
        background: #ffffff;
    }

    .report-page-enhanced
    .sheet-row-saturday td,
    .report-page-enhanced
    .sheet-row-sunday td {
        background: #fff3a6;
    }

    .report-page-enhanced
    .sheet-row-holiday td {
        background: #dfeaa1;
    }

    .report-page-enhanced
    .sheet-table-enhanced tfoot td {
        border-top: 2px solid #1f2937;
        background: #f3f4f6;
    }

    .report-page-enhanced
    .sheet-signature-area {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 28px;
        margin-top: 18px;
    }

    .report-page-enhanced
    .sheet-signature-block {
        flex: 0 0 250px;
    }

    .report-page-enhanced
    .sheet-km-note {
        flex: 1 1 auto;
        max-width: 460px;
        margin-left: auto;
        padding: 8px 0 2px 20px;
        color: #64748b;
        font-size: 10px;
        line-height: 1.45;
        text-align: right;
    }

    .report-page-enhanced
    .sheet-km-note strong {
        color: #475569;
        font-weight: 800;
    }

    @media (max-width: 760px) {
        .report-page-enhanced
        .sheet-signature-area {
            align-items: stretch;
            flex-direction: column;
        }

        .report-page-enhanced
        .sheet-km-note {
            max-width: none;
            padding-left: 0;
            text-align: left;
        }
    }
</style>

<style id="monthly-sheet-refine-2g2">

    /*
     * 2G2
     * Refinamento conservador:
     * mesma folha, mais legível e melhor acabada.
     */

    .report-page-enhanced .monthly-sheet-enhanced {
        padding: 16px 18px 12px;
    }

    .report-page-enhanced .sheet-title {
        margin-bottom: 9px;
        font-size: 15px;
        line-height: 1.2;
    }

    .report-page-enhanced .sheet-head-enhanced {
        margin-bottom: 10px;
    }

    .report-page-enhanced .sheet-brand-box {
        padding: 12px 14px;
    }

    .report-page-enhanced .sheet-brand-logo {
        max-height: 64px;
    }

    .report-page-enhanced .sheet-brand-text {
        font-size: 21px;
        font-weight: 850;
        letter-spacing: -0.02em;
    }

    .report-page-enhanced .sheet-head-grid {
        row-gap: 5px;
        column-gap: 18px;
        padding-top: 3px;
        padding-bottom: 3px;
    }

    .report-page-enhanced .sheet-meta-pair {
        min-height: 20px;
        font-size: 11px;
    }

    .report-page-enhanced .sheet-meta-pair strong {
        font-size: 10.5px;
    }

    .report-page-enhanced .sheet-meta-pair span {
        font-size: 10.5px;
    }

    .report-page-enhanced
    .sheet-table-enhanced {
        font-size: 10px;
    }

    .report-page-enhanced
    .sheet-table-enhanced th {
        padding: 5px 4px;
        line-height: 1.2;
        vertical-align: middle;
    }

    .report-page-enhanced
    .sheet-table-enhanced thead tr:first-child th {
        background: #e7eaee;
        border-bottom-color: #374151;
    }

    .report-page-enhanced
    .sheet-table-enhanced thead tr:last-child th {
        background: #f3f4f6;
        font-size: 9px;
    }

    .report-page-enhanced
    .sheet-table-enhanced td {
        min-height: 17px;
        padding: 3px 4px;
        line-height: 1.25;
    }

    .report-page-enhanced
    .sheet-table-enhanced td:nth-child(7),
    .report-page-enhanced
    .sheet-table-enhanced td:nth-child(10),
    .report-page-enhanced
    .sheet-table-enhanced td:nth-child(11) {
        font-weight: 650;
    }

    .report-page-enhanced
    .sheet-table-enhanced tfoot td {
        padding-top: 5px;
        padding-bottom: 5px;
        font-weight: 800;
    }

    .report-page-enhanced
    .sheet-signature-area {
        margin-top: 20px;
        min-height: 52px;
    }

    .report-page-enhanced
    .sheet-signature-line {
        border-top-color: #374151;
    }

    .report-page-enhanced
    .sheet-signature-label {
        margin-top: 4px;
        color: #475569;
        font-size: 9px;
    }

    .report-page-enhanced
    .sheet-km-note {
        max-width: 500px;
        padding-top: 7px;
        border-top: 1px solid #d6dbe2;
        color: #586474;
        font-size: 9.5px;
        line-height: 1.5;
    }

    .report-page-enhanced
    .sheet-km-note strong {
        color: #374151;
    }

    .report-page-enhanced
    .sheet-footer-signature {
        margin-top: 10px;
        color: #64748b;
        font-size: 8px;
    }

</style>

<style id="report-modern-2g3">

/* =========================================================
   FASE 2G3 — MODERN REPORT
   Somente aparência.
   ========================================================= */

.report-page-enhanced {
    background: #e9edf1;
}

.report-page-enhanced .report-wrap-enhanced {
    padding-top: 24px;
    padding-bottom: 32px;
}

.report-page-enhanced .monthly-sheet-enhanced {
    max-width: 1420px;
    margin: 0 auto;
    padding: 0 20px 16px;
    overflow: hidden;

    background: #ffffff;

    border: 1px solid #cbd5df;
    border-radius: 14px;

    box-shadow:
        0 12px 32px rgba(15, 23, 42, .10);
}

/* TÍTULO */

.report-page-enhanced .sheet-title {
    margin: 0 -20px 14px;
    padding: 13px 20px;

    background: #183840;
    color: #ffffff;

    border: 0;

    text-align: left;
    font-size: 15px;
    font-weight: 800;
    letter-spacing: .055em;
}

/* CABEÇALHO */

.report-page-enhanced .sheet-head-enhanced {
    overflow: hidden;

    margin-bottom: 14px;

    background: #f7f9fa;

    border: 1px solid #cbd3da;
    border-radius: 10px;
}

.report-page-enhanced .sheet-brand-box {
    background: #ffffff;
    border-right: 1px solid #d9dfe4;
}

.report-page-enhanced .sheet-brand-text {
    color: #183840;

    font-size: 22px;
    font-weight: 900;
    letter-spacing: -.02em;
}

.report-page-enhanced .sheet-brand-logo {
    max-height: 66px;
}

.report-page-enhanced .sheet-head-grid {
    padding: 10px 14px;
    gap: 5px 22px;
}

.report-page-enhanced .sheet-meta-pair {
    min-height: 22px;

    display: grid;
    grid-template-columns: 130px minmax(0, 1fr);
    align-items: center;

    border-bottom: 1px solid #e6eaed;
}

.report-page-enhanced .sheet-meta-pair strong {
    color: #53616b;

    font-size: 10px;
    font-weight: 800;
}

.report-page-enhanced .sheet-meta-pair span {
    color: #17242a;

    font-size: 10.5px;
    font-weight: 650;
}

/* TABELA */

.report-page-enhanced .sheet-table-wrap {
    overflow: hidden;

    border: 1px solid #263b43;
    border-radius: 8px;
}

.report-page-enhanced .sheet-table-enhanced {
    width: 100%;

    border: 0;
    border-collapse: collapse;

    font-size: 10px;
}

.report-page-enhanced .sheet-table-enhanced th,
.report-page-enhanced .sheet-table-enhanced td {
    border-color: #8d9aa3;
}

.report-page-enhanced
.sheet-table-enhanced
thead
tr:first-child
th {
    padding: 7px 5px;

    background: #183840;
    color: #ffffff;

    border-color: #405961;

    font-size: 9.5px;
    font-weight: 800;
    letter-spacing: .02em;
}

.report-page-enhanced
.sheet-table-enhanced
thead
tr:last-child
th {
    padding: 5px 4px;

    background: #dfe7e9;
    color: #183840;

    border-color: #85969d;

    font-size: 8.5px;
    font-weight: 850;
}

/* LINHAS */

.report-page-enhanced
.sheet-table-enhanced
tbody td {
    height: 19px;
    padding: 3px 5px;

    background: #ffffff;
    color: #25343a;

    line-height: 1.28;
}

.report-page-enhanced
.sheet-table-enhanced
tbody tr:nth-child(even):not(.sheet-row-saturday):not(.sheet-row-sunday):not(.sheet-row-holiday)
td {
    background: #fafbfc;
}

/* FINS DE SEMANA */

.report-page-enhanced
.sheet-row-saturday td,
.report-page-enhanced
.sheet-row-sunday td {
    background: #fff3c4 !important;
}

/* FERIADO */

.report-page-enhanced
.sheet-row-holiday td {
    background: #dceba8 !important;
    font-weight: 700;
}

/* VALORES IMPORTANTES */

.report-page-enhanced
.sheet-table-enhanced
tbody td:nth-child(7),
.report-page-enhanced
.sheet-table-enhanced
tbody td:nth-child(10),
.report-page-enhanced
.sheet-table-enhanced
tbody td:nth-child(11) {
    font-weight: 800;
    color: #183840;
}

/* TOTAL */

.report-page-enhanced
.sheet-table-enhanced
tfoot td {
    padding: 7px 5px;

    background: #183840 !important;
    color: #ffffff;

    border-color: #405961;

    font-weight: 850;
}

.report-page-enhanced
.sheet-table-enhanced
tfoot td strong {
    color: #ffffff;
}

/* ASSINATURA / NOTA */

.report-page-enhanced .sheet-signature-area {
    display: grid;
    grid-template-columns: 300px 1fr;
    align-items: end;
    gap: 45px;

    min-height: 78px;
    margin-top: 20px;
}

.report-page-enhanced .sheet-signature-block {
    width: 100%;
}

.report-page-enhanced .sheet-signature-line {
    border-top: 1px solid #34464d;
}

.report-page-enhanced .sheet-signature-label {
    margin-top: 5px;

    color: #53616b;

    font-size: 9px;
}

.report-page-enhanced .sheet-km-note {
    max-width: 570px;
    margin-left: auto;

    padding: 9px 12px;

    background: #f4f7f8;

    border: 1px solid #dce3e6;
    border-left: 3px solid #477c88;
    border-radius: 7px;

    color: #58666d;

    font-size: 9.5px;
    line-height: 1.48;
    text-align: left;
}

.report-page-enhanced .sheet-km-note strong {
    color: #183840;
}

/* FOOTER */

.report-page-enhanced .sheet-footer-signature {
    margin-top: 8px;

    color: #89949a;

    font-size: 7.5px;
    letter-spacing: .04em;
}

@media (max-width: 800px) {

    .report-page-enhanced .sheet-signature-area {
        grid-template-columns: 1fr;
        gap: 22px;
    }

    .report-page-enhanced .sheet-km-note {
        max-width: none;
        margin-left: 0;
    }
}

</style>

<style id="report-premium-2g5">

/* =========================================================
   FASE 2G5 — acabamento premium
   Mantém estrutura e identidade da folha 2G3.
   ========================================================= */

/* TÍTULO */

.report-page-enhanced .sheet-title {
    padding:
        14px
        24px;

    letter-spacing: .085em;
}

/* CABEÇALHO SUPERIOR */

.report-page-enhanced .sheet-brand-box {
    padding:
        16px
        20px;
}

.report-page-enhanced .sheet-head-grid {
    padding:
        13px
        17px;

    row-gap: 3px;
}

.report-page-enhanced .sheet-meta-pair {
    min-height: 21px;

    padding:
        2px
        0;

    border-bottom: 0;
}

.report-page-enhanced .sheet-meta-pair strong {
    color: #52626a;
}

.report-page-enhanced .sheet-meta-pair span {
    color: #172a31;
}

/* =========================================================
   GRELHA
   externa forte / interna leve
   ========================================================= */

.report-page-enhanced .sheet-table-wrap {
    border:
        1.5px
        solid
        #183840;
}

.report-page-enhanced
.sheet-table-enhanced th,
.report-page-enhanced
.sheet-table-enhanced td {
    border-color: #d6dde1;
}

/* divisões principais */

.report-page-enhanced
.sheet-table-enhanced
tbody td:nth-child(2),

.report-page-enhanced
.sheet-table-enhanced
tbody td:nth-child(6),

.report-page-enhanced
.sheet-table-enhanced
tbody td:nth-child(7),

.report-page-enhanced
.sheet-table-enhanced
tbody td:nth-child(8),

.report-page-enhanced
.sheet-table-enhanced
tbody td:nth-child(9),

.report-page-enhanced
.sheet-table-enhanced
tbody td:nth-child(10) {
    border-right-color: #98a5ab;
}

/* cabeçalho mantém personalidade */

.report-page-enhanced
.sheet-table-enhanced
thead
tr:first-child
th {
    border-color: #51676f;
}

.report-page-enhanced
.sheet-table-enhanced
thead
tr:last-child
th {
    border-color: #bac4c9;
}

/* =========================================================
   PROPORÇÕES DAS COLUNAS
   mais espaço para lojas
   ========================================================= */

.report-page-enhanced
.sheet-table-enhanced
thead
tr:first-child
th:nth-child(1) {
    width: 6.5%;
}

.report-page-enhanced
.sheet-table-enhanced
thead
tr:first-child
th:nth-child(2) {
    width: 8.5%;
}

.report-page-enhanced
.sheet-table-enhanced
thead
tr:first-child
th:nth-child(3) {
    width: 24%;
}

.report-page-enhanced
.sheet-table-enhanced
thead
tr:first-child
th:nth-child(4) {
    width: 8%;
}

.report-page-enhanced
.sheet-table-enhanced
thead
tr:first-child
th:nth-child(5) {
    width: 9.5%;
}

.report-page-enhanced
.sheet-table-enhanced
thead
tr:first-child
th:nth-child(6) {
    width: 31%;
}

.report-page-enhanced
.sheet-table-enhanced
thead
tr:first-child
th:nth-child(7) {
    width: 6%;
}

.report-page-enhanced
.sheet-table-enhanced
thead
tr:first-child
th:nth-child(8) {
    width: 6.5%;
}

/* =========================================================
   LEGIBILIDADE DOS DADOS
   ========================================================= */

.report-page-enhanced
.sheet-table-enhanced
tbody td {
    font-size: 10.6px;
}

/* horários */

.report-page-enhanced
.sheet-table-enhanced
tbody td:nth-child(3),

.report-page-enhanced
.sheet-table-enhanced
tbody td:nth-child(4),

.report-page-enhanced
.sheet-table-enhanced
tbody td:nth-child(5),

.report-page-enhanced
.sheet-table-enhanced
tbody td:nth-child(6) {
    font-size: 10.7px;
}

/* total de horas */

.report-page-enhanced
.sheet-table-enhanced
tbody td:nth-child(7) {
    color: #172f37;
    font-size: 10.9px;
    font-weight: 750;
}

/* observações */

.report-page-enhanced
.sheet-table-enhanced
tbody td:nth-child(8) {
    font-size: 10.4px;
    font-weight: 400;
    line-height: 1.36;
}

/* lojas */

.report-page-enhanced
.sheet-table-enhanced
tbody td:nth-child(9) {
    font-size: 10.5px;
    font-weight: 400;
    line-height: 1.42;
}

/* KM */

.report-page-enhanced
.sheet-table-enhanced
tbody td:nth-child(10) {
    color: #172f37;
    font-size: 10.9px;
    font-weight: 750;
    text-align: center;
}

/* valor */

.report-page-enhanced
.sheet-table-enhanced
tbody td:nth-child(11) {
    padding-right: 7px;

    font-weight: 400;
    text-align: right;
}

/* =========================================================
   LINHAS COM TRABALHO
   8–12% mais espaço vertical
   ========================================================= */

.report-page-enhanced
.sheet-table-enhanced
tbody
tr.sheet-row-content
td {
    padding-top: 4px;
    padding-bottom: 4px;
}

/* =========================================================
   DIAS ESPECIAIS — tons corporativos suaves
   ========================================================= */

.report-page-enhanced
.sheet-row-saturday td,

.report-page-enhanced
.sheet-row-sunday td {
    background: #fff8df !important;
}

.report-page-enhanced
.sheet-row-holiday td {
    background: #edf6df !important;

    color: #263a31;
}

/* =========================================================
   TOTAL
   ========================================================= */

.report-page-enhanced
.sheet-table-enhanced
tfoot td {
    border-color: #526970;
}

.report-page-enhanced
.sheet-table-enhanced
tfoot td:nth-child(2),

.report-page-enhanced
.sheet-table-enhanced
tfoot td:nth-child(4) {
    font-size: 11px;
    font-weight: 850;
    text-align: center;
}

/* =========================================================
   RODAPÉ
   ========================================================= */

.report-page-enhanced .sheet-signature-area {
    grid-template-columns:
        330px
        minmax(0, 1fr);

    gap: 50px;

    min-height: 90px;
    margin-top: 22px;
}

.report-page-enhanced .sheet-signature-block {
    align-self: end;
}

.report-page-enhanced .sheet-signature-line {
    width: 270px;

    border-top:
        1px
        solid
        #34474f;
}

.report-page-enhanced .sheet-signature-label {
    width: 270px;

    margin-top: 6px;

    color: #4f6068;

    font-size: 9.5px;
}

.report-page-enhanced .sheet-km-note {
    max-width: 620px;

    padding:
        11px
        13px;

    background: #f6f8f9;

    border-color: #d5dde0;
    border-left-color: #477c88;

    color: #4f5f67;

    font-size: 10.2px;
    line-height: 1.52;
}

.report-page-enhanced .sheet-km-note strong {
    color: #183840;
}

</style>

<style id="report-clean-empty-2g7">

/* =========================================================
   FASE 2G7 — linhas vazias sem efeito Excel
   ========================================================= */

/*
 * Dias sem expediente:
 * mantém Data + Dia da Semana.
 * As nove colunas restantes viram UMA faixa.
 */
.report-page-enhanced
.sheet-table-enhanced
tbody
tr.sheet-row-compact
td {
    height: 15px !important;
    min-height: 0 !important;

    padding-top: 2px;
    padding-bottom: 2px;
}

/*
 * A faixa contínua substitui:
 * entrada / saída / entrada / saída /
 * total / observação / lojas / KM / valor.
 */
.report-page-enhanced
.sheet-table-enhanced
tbody
tr.sheet-row-compact
.sheet-compact-span {
    border-left-color: #98a5ab;
    border-right-color: #98a5ab;

    text-align: center;
    vertical-align: middle;
}

/*
 * Dia útil comum sem registo.
 */
.report-page-enhanced
.sheet-table-enhanced
tbody
tr.sheet-row-compact:not(.sheet-row-saturday):not(.sheet-row-sunday):not(.sheet-row-holiday)
.sheet-compact-span {
    background: #fbfcfd !important;
}

/*
 * Texto de feriado, ausência ou nota
 * quando não existe expediente.
 */
.report-page-enhanced
.sheet-table-enhanced
tbody
tr.sheet-row-compact-special
.sheet-compact-span {
    color: #40524a;

    font-size: 10px;
    font-weight: 700;
    letter-spacing: .01em;
}

/*
 * Sábado/domingo e feriado continuam
 * usando exatamente as cores da 2G5.
 */
.report-page-enhanced
.sheet-table-enhanced
tbody
tr.sheet-row-compact
.sheet-compact-label {
    display: inline-block;
}

</style>

<style id="report-final-2g8">

/* =========================================================
   FASE 2G8 — estados das linhas + cabeçalho final
   ========================================================= */


/* =========================================================
   CABEÇALHO INSTITUCIONAL
   ========================================================= */

.report-page-enhanced
.sheet-head-enhanced {
    margin-bottom: 14px;

    background: #f7f9fa;

    border:
        1px
        solid
        #d3dce0;

    box-shadow: none;
}


/*
 * Logo solto.
 * Remove especificamente a sensação
 * de "grande célula" à esquerda.
 */
.report-page-enhanced
.sheet-brand-box {
    padding:
        17px
        22px;

    background: transparent !important;

    border-right:
        0
        !important;
}


.report-page-enhanced
.sheet-brand-logo {
    max-height: 68px;
}


.report-page-enhanced
.sheet-brand-text {
    color: #183840;

    font-size: 23px;
    font-weight: 900;
}


/*
 * Duas colunas invisíveis bem definidas.
 */
.report-page-enhanced
.sheet-head-grid {
    display: grid;

    grid-template-columns:
        minmax(0, 1fr)
        minmax(0, 1fr);

    column-gap: 30px;
    row-gap: 4px;

    padding:
        13px
        18px;
}


/*
 * Rótulo + valor.
 * Todos os valores começam no mesmo eixo.
 */
.report-page-enhanced
.sheet-meta-pair {
    display: grid;

    grid-template-columns:
        118px
        minmax(0, 1fr);

    align-items: center;

    min-height: 20px;

    padding:
        1px
        0;

    border-bottom:
        0
        !important;

    line-height: 1.25;
}


.report-page-enhanced
.sheet-meta-pair strong {
    color: #63737a;

    font-size: 9.5px;
    font-weight: 700;
}


.report-page-enhanced
.sheet-meta-pair span {
    color: #183840;

    font-size: 10.6px;
    font-weight: 650;
}


/* =========================================================
   LINHAS SEM EXPEDIENTE
   ========================================================= */

/*
 * Aproximadamente 10–15% mais leves
 * que uma linha trabalhada.
 */
.report-page-enhanced
.sheet-table-enhanced
tbody
tr.sheet-row-compact
td {
    height: 14px !important;
    min-height: 0 !important;

    padding-top: 1.5px;
    padding-bottom: 1.5px;

    border-top-color: #d8e0e3;
    border-bottom-color: #d8e0e3;
}


/*
 * Depois de DATA e DIA existe apenas
 * esta célula colspan=9.
 *
 * Não há divisões verticais internas.
 */
.report-page-enhanced
.sheet-table-enhanced
tbody
tr.sheet-row-compact
.sheet-compact-span {
    text-align: center;
    vertical-align: middle;

    border-left-color: #9ba8ad;
    border-right-color: #9ba8ad;
}


/*
 * DIA ÚTIL SEM REGISTO
 */
.report-page-enhanced
.sheet-table-enhanced
tbody
tr.sheet-row-compact:not(.sheet-row-saturday):not(.sheet-row-sunday):not(.sheet-row-holiday):not(.sheet-row-compact-special)
.sheet-compact-span {
    background:
        #f5f7f8
        !important;
}


.report-page-enhanced
.sheet-compact-dash {
    color: #9aa5aa;

    font-size: 10px;
    font-weight: 500;
}


/*
 * SÁBADO / DOMINGO
 *
 * Uma única faixa creme.
 * Sem texto no corpo da linha.
 */
.report-page-enhanced
.sheet-table-enhanced
tbody
tr.sheet-row-compact.sheet-row-saturday
td,

.report-page-enhanced
.sheet-table-enhanced
tbody
tr.sheet-row-compact.sheet-row-sunday
td {
    background:
        #fff9e8
        !important;
}


/*
 * FERIADO / AUSÊNCIA / SITUAÇÃO ESPECIAL
 */
.report-page-enhanced
.sheet-table-enhanced
tbody
tr.sheet-row-compact-special
.sheet-compact-span {
    background:
        #eef7e6
        !important;

    color: #405448;

    text-align: center;
}


.report-page-enhanced
.sheet-table-enhanced
tbody
tr.sheet-row-compact-special
.sheet-compact-label {
    color: #405448;

    font-size: 10px;
    font-weight: 650;

    letter-spacing: .01em;
}


/*
 * IMPORTANTE:
 *
 * Nenhum seletor abaixo modifica
 * .sheet-row-content.
 *
 * Dias trabalhados permanecem exatamente
 * com a grelha detalhada da 2G5/2G7.
 */

</style>

<style id="report-final-polish-2g9">

/* =========================================================
   FASE 2G9 — POLIMENTO FINAL

   Somente:
   1. contraste dos estados;
   2. hierarquia do cabeçalho;
   3. traço mais visível;
   4. rodapé refinado.
   ========================================================= */


/* =========================================================
   1. ESTADOS DAS LINHAS
   ========================================================= */

/*
 * DIA ÚTIL SEM ATIVIDADE
 */
.report-page-enhanced
.sheet-table-enhanced
tbody
tr.sheet-row-compact:not(.sheet-row-saturday):not(.sheet-row-sunday):not(.sheet-row-holiday):not(.sheet-row-compact-special)
td {
    background:
        #EEF2F3
        !important;
}


/*
 * SÁBADO / DOMINGO
 */
.report-page-enhanced
.sheet-table-enhanced
tbody
tr.sheet-row-compact.sheet-row-saturday
td,

.report-page-enhanced
.sheet-table-enhanced
tbody
tr.sheet-row-compact.sheet-row-sunday
td {
    background:
        #F6E8B6
        !important;
}


/*
 * FERIADO / AUSÊNCIA / SITUAÇÃO ESPECIAL
 */
.report-page-enhanced
.sheet-table-enhanced
tbody
tr.sheet-row-compact-special
td {
    background:
        #D8E9C5
        !important;
}


.report-page-enhanced
.sheet-table-enhanced
tbody
tr.sheet-row-compact-special
.sheet-compact-span {
    background:
        #D8E9C5
        !important;
}


/* =========================================================
   2. TRAÇO DOS DIAS ÚTEIS VAZIOS
   ========================================================= */

.report-page-enhanced
.sheet-compact-dash {
    color: #7D898F;

    font-size: 10.5px;
    font-weight: 600;
}


/* =========================================================
   3. HIERARQUIA DO CABEÇALHO
   ========================================================= */

/*
 * Logo continua solto.
 * Apenas reduzimos um pouco o espaço
 * entre logo e metadados.
 */
.report-page-enhanced
.sheet-brand-box {
    padding:
        17px
        14px
        17px
        20px;
}


/*
 * Aproxima o bloco esquerdo do logo
 * e melhora a distribuição entre os
 * dois blocos de informação.
 */
.report-page-enhanced
.sheet-head-grid {
    column-gap: 34px;

    padding:
        12px
        18px
        12px
        10px;
}


/*
 * Valor começa mais próximo do rótulo.
 */
.report-page-enhanced
.sheet-meta-pair {
    grid-template-columns:
        108px
        minmax(0, 1fr);
}


/*
 * Rótulo continua secundário.
 */
.report-page-enhanced
.sheet-meta-pair strong {
    color: #68777E;

    font-size: 9.5px;
    font-weight: 700;
}


/*
 * Valor recebe aproximadamente
 * +0,5 pt de presença visual.
 */
.report-page-enhanced
.sheet-meta-pair span {
    color: #173840;

    font-size: 11.2px;
    font-weight: 650;
}


/* =========================================================
   4. RODAPÉ
   ========================================================= */

/*
 * Aproxima assinatura e nota da tabela.
 */
.report-page-enhanced
.sheet-signature-area {
    margin-top: 16px;

    min-height: 78px;
}


/*
 * Nota ligeiramente mais legível.
 */
.report-page-enhanced
.sheet-km-note {
    padding:
        10px
        13px;

    font-size: 10.5px;
    line-height: 1.48;
}


.report-page-enhanced
.sheet-km-note strong {
    color: #173840;

    font-weight: 850;
}


/*
 * Dias trabalhados, cabeçalho da tabela,
 * TOTAL DO MÊS e TOTAL KMS:
 * SEM ALTERAÇÃO NESTA FASE.
 */

</style>

<style id="report-header-final-2g10">

/* =========================================================
   FASE 2G10 — CABEÇALHO FINAL

   Não altera:
   - tabela;
   - estados das linhas;
   - cores;
   - totais;
   - cálculos.
   ========================================================= */


/* Marca permanece praticamente igual. */

.report-page-enhanced
.sheet-brand-box {
    padding:
        16px
        12px
        16px
        19px;
}


.report-page-enhanced
.sheet-brand-text {
    font-size: 23px;
}


/*
 * Usa melhor o espaço horizontal existente.
 *
 * Bloco esquerdo e bloco direito continuam
 * perfeitamente separados, mas o conteúdo
 * deixa de parecer perdido no painel.
 */

.report-page-enhanced
.sheet-head-grid {
    column-gap: 26px;

    padding:
        11px
        18px
        11px
        7px;
}


/*
 * Rótulo → valor mais próximos.
 */

.report-page-enhanced
.sheet-meta-pair {
    grid-template-columns:
        101px
        minmax(0, 1fr);

    min-height: 21px;

    column-gap: 6px;
}


/*
 * RÓTULO
 *
 * Aproximadamente +0,4 pt em relação
 * ao refinamento anterior.
 */

.report-page-enhanced
.sheet-meta-pair strong {
    color: #617178;

    font-size: 10.1px;
    font-weight: 650;

    letter-spacing: .005em;
}


/*
 * VALOR
 *
 * Mais perceptível que o rótulo,
 * sem parecer negrito.
 */

.report-page-enhanced
.sheet-meta-pair span {
    color: #173840;

    font-size: 11.9px;
    font-weight: 500;

    line-height: 1.28;
}


/* =========================================================
   RODAPÉ VISUAL DO PREVIEW
   ========================================================= */

.report-page-enhanced
.sheet-signature-area {
    margin-top: 13px;

    min-height: 72px;
}


.report-page-enhanced
.sheet-km-note {
    font-size: 10.6px;
    line-height: 1.46;
}


.report-page-enhanced
.sheet-km-note strong {
    color: #173840;

    font-weight: 850;
}


/*
 * A paginação existe somente no PDF.
 * O preview web não possui conceito
 * físico de página.
 */

</style>

<style id="report-microfinal-2g11">

/* =========================================================
   FASE 2G11 — MICRO FINAL
   ========================================================= */


/*
 * VALORES DO CABEÇALHO
 *
 * 11.9px -> 12.6px
 * aumento de aproximadamente 5,9%.
 *
 * Rótulos permanecem exatamente como estão.
 */

.report-page-enhanced
.sheet-meta-pair span {
    font-size: 12.6px;

    color: #173840;

    font-weight: 500;
}


/* =========================================================
   ASSINATURA + NOTA KMS
   ========================================================= */

/*
 * Alinha os dois blocos no mesmo eixo visual.
 */

.report-page-enhanced
.sheet-signature-area {
    margin-top: 10px;

    min-height: 68px;

    align-items: center;
}


.report-page-enhanced
.sheet-signature-block {
    align-self: center;
}


.report-page-enhanced
.sheet-km-note {
    align-self: center;
}


/*
 * Nenhuma alteração em:
 * tabela,
 * cores,
 * linhas,
 * totais,
 * tipografia dos dias trabalhados.
 */

</style>
</head>
<body class="report-page report-page-enhanced">
    <header class="report-topbar">
        <div class="report-topbar-left">
            <a
                class="report-back-btn"
                href="/auditor-app/public/export/pdf?month=<?= urlencode((string) $report['month_number']) ?>&year=<?= urlencode((string) $report['year']) ?>"
            >
                ← Voltar
            </a>

            <div class="report-topbar-title">
                <strong><?= htmlspecialchars($title ?? '') ?></strong>
                <small>
                    <?= htmlspecialchars(
                        ($report['month_label'] ?? '') .
                        ' ' .
                        ($report['year'] ?? '')
                    ) ?>
                </small>
            </div>
        </div>

        <div class="report-topbar-actions">
            <a
                class="report-action-btn"
                href="/auditor-app/public/report/monthly-download?month=<?= urlencode((string) $report['month_number']) ?>&year=<?= urlencode((string) $report['year']) ?>&download=1&v=<?= time() ?>"
            >
                Gerar PDF
            </a>
        </div>
    </header>

    <main class="report-wrap report-wrap-enhanced">
        <section class="monthly-sheet monthly-sheet-enhanced">
            <div class="sheet-title">
                FOLHA MENSAL DE REGISTO DE HORAS
            </div>

            <div class="sheet-head sheet-head-enhanced">
                <div class="sheet-brand-box">
                    <?php if (!empty($settings['company_logo_path'])): ?>
                        <img
                            src="<?= htmlspecialchars($settings['company_logo_path']) ?>"
                            alt="Logo"
                            class="sheet-brand-logo"
                        >
                    <?php else: ?>
                        <div class="sheet-brand-text">
                            <?= htmlspecialchars(
                                $settings['company_name'] ?? 'TimeClock'
                            ) ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="sheet-head-grid">
                    <div class="sheet-meta-pair">
                        <strong>Trabalhador(a):</strong>
                        <span>
                            <?= htmlspecialchars(
                                $settings['auditor_name'] ?? '-'
                            ) ?>
                        </span>
                    </div>

                    <div class="sheet-meta-pair">
                        <strong>Local:</strong>
                        <span>
                            <?= htmlspecialchars(
                                $settings['work_location'] ?? '-'
                            ) ?>
                        </span>
                    </div>

                    <div class="sheet-meta-pair">
                        <strong>Mês / Ano:</strong>
                        <span>
                            <?= htmlspecialchars(
                                ($report['month_label'] ?? '-') .
                                '_' .
                                substr(
                                    (string) ($report['year'] ?? ''),
                                    -2
                                )
                            ) ?>
                        </span>
                    </div>

                    <div class="sheet-meta-pair">
                        <strong>Matrícula Viatura:</strong>
                        <span>
                            <?= htmlspecialchars(
                                $settings['vehicle_plate'] ?? '-'
                            ) ?>
                        </span>
                    </div>

                    <div class="sheet-meta-pair">
                        <strong>Hora Semanal:</strong>
                        <span>
                            <?= htmlspecialchars(
                                $settings['weekly_hours'] ?? '-'
                            ) ?>
                        </span>
                    </div>

                    <div class="sheet-meta-pair">
                        <strong>Nº Emp.:</strong>
                        <span>
                            <?= htmlspecialchars(
                                $settings['employee_number'] ?? '-'
                            ) ?>
                        </span>
                    </div>

                    <div class="sheet-meta-pair">
                        <strong>Horário de Trabalho:</strong>
                        <span>
                            <?= htmlspecialchars(
                                $settings['work_schedule'] ?? '-'
                            ) ?>
                        </span>
                    </div>

                    <div class="sheet-meta-pair">
                        <strong>Cat. Prof.:</strong>
                        <span>
                            <?= htmlspecialchars(
                                $settings['professional_category'] ?? '-'
                            ) ?>
                        </span>
                    </div>
                </div>
            </div>

            <div class="sheet-table-wrap">
                <table class="sheet-table sheet-table-enhanced">
                    <thead>
                        <tr>
                            <th rowspan="2">DATA</th>
                            <th rowspan="2">DIA SEMANA</th>
                            <th colspan="4">HORAS TRABALHO</th>
                            <th rowspan="2">TOTAL HORAS</th>
                            <th rowspan="2">OBSERVAÇÕES</th>
                            <th rowspan="2">Lojas visitadas por sequência</th>
                            <th rowspan="2">KMS*</th>
                            <th rowspan="2">Valor</th>
                        </tr>

                        <tr>
                            <th>ENTRADA</th>
                            <th>SAÍDA</th>
                            <th>ENTRADA</th>
                            <th>SAÍDA</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach (($report['rows'] ?? []) as $row): ?>
                            <?php
                                $rowClass = trim(
                                    (string) ($row['row_class'] ?? '')
                                );

                                $observations = trim(
                                    (string) ($row['notes'] ?? '')
                                );

                                if (!empty($row['holiday_name'])) {
                                    $observations = trim(
                                        (
                                            $observations !== ''
                                                ? $observations . ' | '
                                                : ''
                                        ) .
                                        $row['holiday_name'],
                                        ' |'
                                    );
                                }

                                /*
                                 * 2G7:
                                 * "trabalho" significa existir algum
                                 * dado operacional do expediente.
                                 *
                                 * Nota/feriado sozinho NÃO obriga a
                                 * desenhar as nove células vazias.
                                 */
                                $hasWorkData =
                                    trim((string) ($row['entry1'] ?? '')) !== '' ||
                                    trim((string) ($row['exit1'] ?? '')) !== '' ||
                                    trim((string) ($row['entry2'] ?? '')) !== '' ||
                                    trim((string) ($row['exit2'] ?? '')) !== '' ||
                                    trim((string) ($row['stores'] ?? '')) !== '' ||
                                    trim((string) ($row['kms'] ?? '')) !== '' ||
                                    trim((string) ($row['value'] ?? '')) !== '';

                                $finalRowClass = trim(
                                    $rowClass .
                                    (
                                        $hasWorkData
                                            ? ' sheet-row-content'
                                            : ' sheet-row-compact'
                                    ) .
                                    (
                                        !$hasWorkData &&
                                        $observations !== ''
                                            ? ' sheet-row-compact-special'
                                            : ''
                                    )
                                );

                                $dateDisplay =
                                    $row['date_label'] ??
                                    date(
                                        'd-M',
                                        strtotime($row['date_iso'])
                                    );
                            ?>

                            <tr class="<?= htmlspecialchars($finalRowClass) ?>">
                                <td>
                                    <?= htmlspecialchars($dateDisplay) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($row['weekday']) ?>
                                </td>

                                <?php if ($hasWorkData): ?>

                                    <td>
                                        <?= htmlspecialchars($row['entry1']) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($row['exit1']) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($row['entry2']) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($row['exit2']) ?>
                                    </td>

                                    <td>
                                        <strong>
                                            <?php
                                                /* REPORT_HHMM_2G12 */
                                                $dayMinutes =
                                                    max(
                                                        0,
                                                        (int) (
                                                            $row['total_minutes']
                                                            ?? 0
                                                        )
                                                    );

                                                $dayHoursDisplay =
                                                    sprintf(
                                                        '%d:%02d',
                                                        intdiv(
                                                            $dayMinutes,
                                                            60
                                                        ),
                                                        $dayMinutes % 60
                                                    );
                                            ?>

                                            <?= htmlspecialchars(
                                                $dayHoursDisplay
                                            ) ?>
                                        </strong>
                                    </td>

                                    <td class="cell-left">
                                        <?= htmlspecialchars($observations) ?>
                                    </td>

                                    <td class="cell-left">
                                        <?= htmlspecialchars($row['stores']) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($row['kms']) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($row['value']) ?>
                                    </td>

                                <?php else: ?>

                                    <td
                                        colspan="9"
                                        class="sheet-compact-span"
                                    >
                                        <?php if ($observations !== ''): ?>

                                            <span class="sheet-compact-label">
                                                <?= htmlspecialchars(
                                                    $observations
                                                ) ?>
                                            </span>

                                        <?php elseif (
                                            !str_contains(
                                                $rowClass,
                                                'sheet-row-saturday'
                                            ) &&
                                            !str_contains(
                                                $rowClass,
                                                'sheet-row-sunday'
                                            )
                                        ): ?>

                                            <span class="sheet-compact-dash">
                                                —
                                            </span>

                                        <?php endif; ?>
                                    </td>

                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>

                    <tfoot>
                        <?php
                        $totalKmValue = 0.0;

                        foreach (
                            ($report['rows'] ?? [])
                            as $totalKmRow
                        ) {
                            $kmText = trim(
                                (string) (
                                    $totalKmRow['kms'] ??
                                    ''
                                )
                            );

                            if ($kmText === '') {
                                continue;
                            }

                            $kmText = str_replace(
                                ["\xc2\xa0", ' '],
                                '',
                                $kmText
                            );

                            if (
                                str_contains($kmText, ',') &&
                                str_contains($kmText, '.')
                            ) {
                                $kmText = str_replace(
                                    '.',
                                    '',
                                    $kmText
                                );
                            }

                            $kmText = str_replace(
                                ',',
                                '.',
                                $kmText
                            );

                            if (is_numeric($kmText)) {
                                $totalKmValue +=
                                    (float) $kmText;
                            }
                        }

                        $totalKmDisplay =
                            number_format(
                                $totalKmValue,
                                1,
                                ',',
                                ''
                            );
                        ?>

                        <tr>
                            <td colspan="6">
                                <strong>TOTAL DO MÊS</strong>
                            </td>

                            <td>
                                <strong>
                                    <?php
                                        /* REPORT_HHMM_2G12_MONTH */
                                        $monthlyMinutes =
                                            max(
                                                0,
                                                (int) (
                                                    $report[
                                                        'monthly_total_minutes'
                                                    ] ?? 0
                                                )
                                            );

                                        $monthlyHoursDisplay =
                                            sprintf(
                                                '%d:%02d',
                                                intdiv(
                                                    $monthlyMinutes,
                                                    60
                                                ),
                                                $monthlyMinutes % 60
                                            );
                                    ?>

                                    <?= htmlspecialchars(
                                        $monthlyHoursDisplay
                                    ) ?>
                                </strong>
                            </td>

                            <td
                                colspan="2"
                                class="cell-left"
                            >
                                <strong>TOTAL KMS</strong>
                            </td>

                            <td>
                                <strong>
                                    <?= htmlspecialchars(
                                        $totalKmDisplay
                                    ) ?>
                                </strong>
                            </td>

                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="sheet-signature-area">
                <div class="sheet-signature-block">
                    <?php if (!empty($settings['signature_path'])): ?>
                        <img
                            src="<?= htmlspecialchars(
                                $settings['signature_path']
                            ) ?>"
                            alt="Assinatura"
                            class="sheet-signature-image"
                        >
                    <?php endif; ?>

                    <div class="sheet-signature-line"></div>

                    <div class="sheet-signature-label">
                        Assinatura do(a) Trabalhador(a)
                    </div>
                </div>

                <div class="sheet-km-note">
                    <strong>Nota sobre KMS:</strong>
                    Os valores de KMS são calculados automaticamente
                    com base na origem e nas lojas registadas no dia.
                    Podem existir pequenas variações face ao percurso
                    efetivamente realizado, devido a alterações de
                    trajeto, condições da via ou precisão dos endereços.
                </div>
            </div>

        </section>
    </main>
</body>
</html>