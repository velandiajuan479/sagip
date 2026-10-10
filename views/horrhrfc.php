<?php
require_once __DIR__ . '/../models/conexion.php';
require_once __DIR__ . '/../models/hormhrfc.php';
require_once __DIR__ . '/../models/hormhor.php';
require_once __DIR__ . '/../models/hormacceso.php';

/* ══════════════════════════════════════════════════════════════
   1. DATOS BASE
   ══════════════════════════════════════════════════════════════ */
$mrphfc = new Hormhrfc();
$mhor   = new Hormhor();

// Alcance por rol (módulo HyA): la ficha solicitada debe estar permitida;
// si no, se usa la primera ficha asignada al usuario (admin ve todas).
$hacceso = new HormAcceso();
$scopeCtx = $hacceso->getContexto();
$scopeFichas = $hacceso->getFichasPermitidas($scopeCtx);
$scopeRol = $hacceso->etiquetaRol($scopeCtx);
$scopeN = count($scopeFichas);
$scopeTotal = !empty($scopeCtx['is_admin']);

$idfic = isset($_REQUEST['idfic']) ? $_REQUEST['idfic'] : null;
if (!$hacceso->puedeVerFicha($idfic, $scopeCtx)) {
    $idfic = !empty($scopeFichas) ? $scopeFichas[0]['idfic'] : null;
}
$mrphfc->setIdfic($idfic);

$dtOneHeader  = $mrphfc->getOne();
$__municipio  = $mrphfc->getMunicipioPorFicha($idfic);

/* ══════════════════════════════════════════════════════════════
   2. TRIMESTRE ACTUAL (calculado UNA sola vez)
   ══════════════════════════════════════════════════════════════ */
function obtenerTrimestreActual() {
    global $mhor;

    $anio = (int)date('Y');
    $hoy  = new DateTime('now', new DateTimeZone('America/Bogota'));
    $eventos = $mhor->getEventosPorTipo('trimestre', $anio);

    foreach ($eventos as $ev) {
        if (!empty($ev['fecha_inicio']) && !empty($ev['fecha_fin'])) {
            $ini = new DateTime($ev['fecha_inicio']);
            $fin = new DateTime($ev['fecha_fin']);
            if ($hoy >= $ini && $hoy <= $fin) {
                return [
                    'nombre'       => $ev['trimestre'] ?: 'Trimestre',
                    'anio'         => (int)$ev['anio'],
                    'fecha_inicio' => $ev['fecha_inicio'],
                    'fecha_fin'    => $ev['fecha_fin']
                ];
            }
        }
    }
    if (!empty($eventos)) {
        $ultimo = end($eventos);
        return [
            'nombre'       => $ultimo['trimestre'] ?: 'Trimestre',
            'anio'         => (int)$ultimo['anio'],
            'fecha_inicio' => $ultimo['fecha_inicio'],
            'fecha_fin'    => $ultimo['fecha_fin']
        ];
    }
    return [
        'nombre'       => 'Trimestre',
        'anio'         => $anio,
        'fecha_inicio' => date('Y-m-01'),
        'fecha_fin'    => date('Y-m-t')
    ];
}

$__trActual = obtenerTrimestreActual();

// Cargar horarios del trimestre actual para la ficha
$dtOne = $mrphfc->getHorariosFichaEnRango(
    $idfic,
    $__trActual['fecha_inicio'],
    $__trActual['fecha_fin']
);

/* ══════════════════════════════════════════════════════════════
   3. DATOS DEL ENCABEZADO (extraídos UNA sola vez)
   ══════════════════════════════════════════════════════════════ */
$__jornada       = '';
$__nomvalJornada = '';
$__nomfic        = '';

if ($dtOneHeader && isset($dtOneHeader[0])) {
    $h = $dtOneHeader[0];
    $__nomvalJornada = $h['nomval']   ?? '';
    $__nomfic        = $h['nf']       ?? '';
    $jornadaId       = $h['jornada']  ?? null;

    if ($jornadaId == 1)      $__jornada = '07:00 - 13:00';
    elseif ($jornadaId == 2)  $__jornada = '13:00 - 18:00';
    elseif ($jornadaId == 3)  $__jornada = '18:00 - 22:00';
}

/* ══════════════════════════════════════════════════════════════
   4. UTILIDADES DE FECHAS
   ══════════════════════════════════════════════════════════════ */
function formatearFecha($fecha) {
    if (empty($fecha) || $fecha === 'SIN_FECHA') return '';
    $meses = [1=>'enero',2=>'febrero',3=>'marzo',4=>'abril',5=>'mayo',6=>'junio',
              7=>'julio',8=>'agosto',9=>'septiembre',10=>'octubre',11=>'noviembre',12=>'diciembre'];
    try {
        $d = new DateTime($fecha);
        return $d->format('j') . ' de ' . $meses[(int)$d->format('n')];
    } catch (Exception $e) { return ''; }
}

/**
 * Determina si un ítem del horario cae dentro del trimestre actual.
 * Devuelve la fecha ISO o 'SIN_FECHA' si aplica pero no tiene fecha específica.
 * Devuelve null si no aplica.
 */
function fechaItemEnTrimestre($item, $trimestre) {
    $iniTr = new DateTime($trimestre['fecha_inicio']);
    $finTr = new DateTime($trimestre['fecha_fin']);

    // Caso 1: tiene fecha específica
    if (!empty($item['fecha_especifica'])) {
        try {
            $f = new DateTime($item['fecha_especifica']);
            if ($f >= $iniTr && $f <= $finTr) return $item['fecha_especifica'];
        } catch (Exception $e) { /* ignore */ }
        return null;
    }

    // Caso 2: rango fecha_inicio / fecha_fin
    if (!empty($item['fecha_inicio']) && !empty($item['fecha_fin'])) {
        try {
            $fi = new DateTime($item['fecha_inicio']);
            $ff = new DateTime($item['fecha_fin']);
            if ($fi <= $finTr && $ff >= $iniTr) return 'SIN_FECHA';
        } catch (Exception $e) { /* ignore */ }
        return null;
    }

    // Caso 3: sin fechas
    return 'SIN_FECHA';
}

/* ══════════════════════════════════════════════════════════════
   5. RENDERIZADO DE LISTADOS
   ══════════════════════════════════════════════════════════════ */
function renderListadoSimple($nombreToFechas) {
    if (empty($nombreToFechas)) return '';
    ksort($nombreToFechas, SORT_NATURAL | SORT_FLAG_CASE);

    $html = '';
    foreach ($nombreToFechas as $nombre => $fechasRaw) {
        $tieneSinFecha = false;
        $soloFechas    = [];
        foreach ($fechasRaw as $f) {
            if (empty($f)) continue;
            if ($f === 'SIN_FECHA') { $tieneSinFecha = true; continue; }
            $soloFechas[] = $f;
        }
        $soloFechas = array_values(array_unique($soloFechas));
        sort($soloFechas);

        $fechasFmt = array_map('formatearFecha', $soloFechas);
        if ($tieneSinFecha) $fechasFmt[] = 'Sin fecha esp.';

        $html .= '<div class="item-line"><span class="item-name">' . htmlspecialchars($nombre) . '</span>';
        if (!empty($fechasFmt)) {
            $html .= ' <span class="item-dates">(' . htmlspecialchars(implode(', ', $fechasFmt)) . ')</span>';
        }
        $html .= '</div>';
    }
    return $html;
}

function renderListadoActividades($claveToInfo) {
    if (empty($claveToInfo)) return '';
    uasort($claveToInfo, fn($a, $b) => strnatcasecmp($a['nombre'] ?? '', $b['nombre'] ?? ''));

    $html = '';
    foreach ($claveToInfo as $info) {
        $tieneSinFecha = false;
        $soloFechas    = [];
        foreach (($info['fechas'] ?? []) as $f) {
            if ($f === 'SIN_FECHA') { $tieneSinFecha = true; continue; }
            $soloFechas[] = $f;
        }
        $soloFechas = array_values(array_unique($soloFechas));
        sort($soloFechas);

        $fechasFmt = array_map('formatearFecha', $soloFechas);
        if ($tieneSinFecha) $fechasFmt[] = 'Sin fecha esp.';

        $nombre = htmlspecialchars($info['nombre'] ?? '');
        $horas  = isset($info['horas']) && $info['horas'] ? (int)$info['horas'] : 0;

        $html .= '<div class="item-line"><span class="item-name">' . $nombre . '</span>';
        if ($horas > 0) $html .= ' <span class="item-hours">(' . $horas . ' hrs)</span>';
        if (!empty($fechasFmt)) {
            $html .= ' <span class="item-dates">(' . htmlspecialchars(implode(', ', $fechasFmt)) . ')</span>';
        }
        $html .= '</div>';
    }
    return $html;
}

/* ══════════════════════════════════════════════════════════════
   6. AGRUPADORES POR DÍA
   ══════════════════════════════════════════════════════════════ */
function agruparPorDia($dtOne, $iddia, $campo) {
    if (!$dtOne) return '';
    global $__trActual;

    $datos = [];
    foreach ($dtOne as $item) {
        if ($item['iddia'] != $iddia) continue;
        $valor = $item[$campo] ?? '';
        if (empty($valor)) continue;

        $fecha = fechaItemEnTrimestre($item, $__trActual);
        if ($fecha === null) continue;

        if (!isset($datos[$valor])) $datos[$valor] = [];
        $datos[$valor][] = $fecha;
    }
    return renderListadoSimple($datos);
}

function agruparAmbientesPorDia($dtOne, $iddia) {
    if (!$dtOne) return '';
    global $__trActual;

    $ambientes = [];
    foreach ($dtOne as $item) {
        if ($item['iddia'] != $iddia) continue;

        $ambiente = trim(($item['ai'] ?? '') . ' - ' . ($item['an'] ?? ''));
        if ($ambiente === '' || $ambiente === '-') continue;

        $fecha = fechaItemEnTrimestre($item, $__trActual);
        if ($fecha === null) continue;

        if (!isset($ambientes[$ambiente])) $ambientes[$ambiente] = [];
        $ambientes[$ambiente][] = $fecha;
    }
    return renderListadoSimple($ambientes);
}

function agruparActividadesPorDia($dtOne, $iddia) {
    if (!$dtOne) return '';
    global $__trActual;

    $actividades = [];
    foreach ($dtOne as $item) {
        if ($item['iddia'] != $iddia) continue;
        if (empty($item['es_otros'])) continue;

        $actividad = $item['actividad'] ?? '';
        if ($actividad === '') continue;

        $clave = $actividad . '_' . ($item['horas_otros'] ?? '0');
        if (!isset($actividades[$clave])) {
            $actividades[$clave] = [
                'nombre' => $actividad,
                'horas'  => $item['horas_otros'] ?? 0,
                'fechas' => []
            ];
        }

        $fecha = fechaItemEnTrimestre($item, $__trActual);
        if ($fecha === null) continue;

        $actividades[$clave]['fechas'][] = $fecha;
    }
    return renderListadoActividades($actividades);
}

/* ══════════════════════════════════════════════════════════════
   7. MAPA DE DÍAS (IDs fijos)
   ══════════════════════════════════════════════════════════════ */
$diasSemana = [
    ['id' => 1042, 'label' => 'LUNES'],
    ['id' => 1043, 'label' => 'MARTES'],
    ['id' => 1044, 'label' => 'MIÉRCOLES'],
    ['id' => 1045, 'label' => 'JUEVES'],
    ['id' => 1046, 'label' => 'VIERNES'],
    ['id' => 1047, 'label' => 'SÁBADO'],
    ['id' => 1048, 'label' => 'DOMINGO'],
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reporte de Ficha - <?= htmlspecialchars($idfic) ?></title>
<style>
@font-face {
    font-family: 'Roboto';
    src: url('../font/Fugaz_One-Roboto/Roboto/Roboto-VariableFont_wdth,wght.ttf') format('woff2-variations');
    font-weight: 100 900;
    font-stretch: 75% 125%;
    font-style: normal;
}
@font-face {
    font-family: 'Roboto';
    src: url('../font/Fugaz_One-Roboto/Roboto/Roboto-Italic-VariableFont_wdth,wght.ttf') format('woff2-variations');
    font-weight: 100 900;
    font-stretch: 75% 125%;
    font-style: italic;
}

* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
    font-family: 'Roboto', Arial, sans-serif;
    font-size: 11px;
}

body {
    padding: 10px;
    background-color: #fff;
    color: #000;
}

.container { width: 100%; max-width: 100%; margin: 0 auto; }

/* ── Tabla de horario ── */
table.horxfic {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
}
table.horxfic th,
table.horxfic td {
    border: 1px solid #B6BCC6;
    padding: 1px 4px;
    text-align: center;
    vertical-align: middle;
    font-size: 11px;
    word-wrap: break-word;
    overflow-wrap: break-word;
    word-break: normal;
    white-space: normal;
    hyphens: none;
}
table.horxfic td {
    text-align: center;
    vertical-align: middle;
}

.header-title { font-weight: bold; font-size: 11px; }

.item-name  { font-weight: 700; font-size: 11px; display: inline; margin-right: 4px; }
.item-hours { font-weight: 600; font-size: 10px; color: #222; margin-left: 2px; }
.item-dates { color: #333; font-size: 10px; margin-left: 6px; display: none !important;}

.col-item-title {
    writing-mode: vertical-rl;
    transform: rotate(180deg);
    white-space: nowrap;
    font-weight: bold;
    text-align: center !important;
    vertical-align: middle !important;
    padding: 2px !important;
    font-size: 10px;
}

.col-horario {
    font-weight: bold;
    text-align: center !important;
    vertical-align: middle !important;
}

.logo-img {
    display: block;
    width: 100%;
    max-width: 60px;
    height: auto;
    margin: 0 auto;
}
strong {font-weight: 700;}
/* ── Impresión ── */
@media print {
    @page { margin: 1cm; }
    html, body {
        font-size: 8px;
        line-height: 1.15;
        background: none;
        padding: 0;
        margin: 0;
    }
    .container { width: 100% !important; margin: 0 !important; padding: 0 !important; }
    table.horxfic { page-break-inside: avoid; }
    tr { page-break-inside: avoid; page-break-after: auto; }
    thead { display: table-header-group; }
    table.horxfic th,
    table.horxfic td { padding: 2px 3px !important; font-size: 8px !important; }
    .item-name  { font-size: 8.5px !important; }
    .item-hours { font-size: 8px !important; }
    .item-dates { font-size: 7.5px !important; display: none !important;}
}
</style>
</head>
<body>
<div class="container">
    <table class="horxfic">
        <colgroup>
            <col style="width: 8%;">       <!-- Horario -->
            <col style="width: 4%;">       <!-- Item vertical -->
            <col style="width: 12.57%;">   <!-- Lunes -->
            <col style="width: 12.57%;">   <!-- Martes -->
            <col style="width: 12.57%;">   <!-- Miércoles -->
            <col style="width: 12.57%;">   <!-- Jueves -->
            <col style="width: 12.57%;">   <!-- Viernes -->
            <col style="width: 12.57%;">   <!-- Sábado -->
            <col style="width: 12.57%;">   <!-- Domingo -->
        </colgroup>
        <thead>
            <tr>
                <th colspan="2">
                    <img src="..\img\sena.png" alt="Logo SENA" class="logo-img">
                </th>
                <th colspan="5" class="header-title">
                    <div>SENA - REGIONAL CUNDINAMARCA</div>
                    <div>CENTRO DE DESARROLLO AGROEMPRESARIAL</div>
                </th>
                <th colspan="2">
                    <div><strong>VIGENCIA:</strong> <?= htmlspecialchars((string)$__trActual['anio']) ?></div>
                    <div><strong><?= htmlspecialchars(strtoupper($__trActual['nombre'])) ?></strong></div>
                    <div style="font-size: 8.5px; margin-top:2px;">
                        INI: <?= formatearFecha($__trActual['fecha_inicio']) ?><br>
                        FIN: <?= formatearFecha($__trActual['fecha_fin']) ?>
                    </div>
                </th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <th style="text-align: right;">FICHA:</th>
                <td colspan="3"><strong><?= htmlspecialchars($idfic) ?></strong></td>
                <th style="text-align: right;">MUNICIPIO:</th>
                <td colspan="4"><?= htmlspecialchars($__municipio) ?></td>
            </tr>
            <tr>
                <th style="text-align: right;">JORNADA:</th>
                <td colspan="3"><?= htmlspecialchars($__nomvalJornada) ?></td>
                <th style="text-align: right;">PROGRAMA:</th>
                <td colspan="4"><?= htmlspecialchars($__nomfic) ?></td>
            </tr>

            <!-- Fila de títulos de días -->
            <tr style="background-color: #f2f2f2;">
                <th class="col-horario">HORARIO</th>
                <th>ITEM</th>
                <?php foreach ($diasSemana as $d): ?>
                    <th><?= $d['label'] ?></th>
                <?php endforeach; ?>
            </tr>

            <!-- INSTRUCTOR -->
            <tr>
                <th rowspan="4" class="col-horario"><?= htmlspecialchars($__jornada) ?></th>
                <th class="col-item-title">INSTRUCTOR</th>
                <?php foreach ($diasSemana as $d): ?>
                    <td><?= agruparPorDia($dtOne, $d['id'], 'un') ?></td>
                <?php endforeach; ?>
            </tr>

            <!-- AMBIENTE -->
            <tr>
                <th class="col-item-title">AMBIENTE</th>
                <?php foreach ($diasSemana as $d): ?>
                    <td><?= agruparAmbientesPorDia($dtOne, $d['id']) ?></td>
                <?php endforeach; ?>
            </tr>

            <!-- ACTIVIDAD -->
            <tr>
                <th class="col-item-title">ACTIVIDAD</th>
                <?php foreach ($diasSemana as $d): ?>
                    <td><?= agruparActividadesPorDia($dtOne, $d['id']) ?></td>
                <?php endforeach; ?>
            </tr>

            <!-- OBSERVACIONES -->
            <tr>
                <th class="col-item-title">OBSERVACIONES</th>
                <td colspan="7"></td>
            </tr>
        </tbody>
    </table>
</div>

<script type="text/javascript">
    window.onload = function() { window.print(); };
</script>
</body>
</html>