<?php
require_once __DIR__ . '/../models/conexion.php';
require_once __DIR__ . '/../models/hormhrper.php';
require_once __DIR__ . '/../models/hormhor.php';
require_once __DIR__ . '/../models/hormacceso.php';
require_once __DIR__ . '/../controllers/horchor.php';

/* ══════════════════════════════════════════════════════════════
   1. DATOS BASE
   ══════════════════════════════════════════════════════════════ */
$mrphor = new Hormhrper();
$mhor   = new Hormhor();

// Alcance por rol: sin permiso de coordinación solo se ve el propio usuario.
$haccesoHrp = new HormAcceso();
$scopeCtxHrp = $haccesoHrp->getContexto();

$idusu = isset($_REQUEST['idusu']) ? $_REQUEST['idusu'] : null;
if (empty($scopeCtxHrp['is_admin'])) {
    $idusu = $scopeCtxHrp['idusu'];
}
$mrphor->setIdusu($idusu);

$instructorData     = !empty($idusu) ? $mrphor->getUsuarioById($idusu) : null;
$nombreInstructor   = $instructorData['nomusu'] ?? '';
$municipioInstructor = 'CHÍA';

/* ══════════════════════════════════════════════════════════════
   2. TRIMESTRE ACTUAL
   ══════════════════════════════════════════════════════════════ */
function obtenerTrimestreActualHor() {
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
                    'nombre'       => $ev['trimestre'] ?? 'Trimestre',
                    'anio'         => isset($ev['anio']) ? (int)$ev['anio'] : $anio,
                    'fecha_inicio' => $ev['fecha_inicio'],
                    'fecha_fin'    => $ev['fecha_fin']
                ];
            }
        }
    }
    if (!empty($eventos)) {
        $ultimo = end($eventos);
        return [
            'nombre'       => $ultimo['trimestre'] ?? 'Trimestre',
            'anio'         => isset($ultimo['anio']) ? (int)$ultimo['anio'] : $anio,
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

$__trActual = obtenerTrimestreActualHor();

/* ══════════════════════════════════════════════════════════════
   3. HORARIOS EN RANGO
   ══════════════════════════════════════════════════════════════ */
$dtRange = calcularHorasRango(
    $idusu,
    $__trActual['fecha_inicio'],
    $__trActual['fecha_fin']
)['detalle'];

if (empty($nombreInstructor) && !empty($dtRange[0]['nomusu'])) {
    $nombreInstructor = $dtRange[0]['nomusu'];
}

/* ══════════════════════════════════════════════════════════════
   4. DÍAS LABORALES Y FESTIVOS
   ══════════════════════════════════════════════════════════════ */
function esFechaFestivaLocal($fecha) {
    $festivosFijos = [
        '01-01', '01-08', '03-25', '03-28', '03-29',
        '05-01', '05-13', '06-03', '06-10', '07-01',
        '07-20', '08-07', '08-15', '08-19', '10-14', '11-04',
        '11-11', '12-08', '12-25'
    ];
    return in_array(substr($fecha, 5), $festivosFijos);
}

function contarDiasLaboralesEnRangoMes($mes, $anio, $rangoInicio, $rangoFin) {
    $inicioMes = new DateTime(sprintf('%04d-%02d-01', $anio, $mes));
    $finMes    = clone $inicioMes; $finMes->modify('last day of this month');
    $inicio    = max(new DateTime($rangoInicio), $inicioMes);
    $fin       = min(new DateTime($rangoFin), $finMes);
    if ($fin < $inicio) return 0;

    $dias = 0; $cursor = clone $inicio;
    while ($cursor <= $fin) {
        $dow = (int)$cursor->format('N');
        if ($dow >= 1 && $dow <= 5 && !esFechaFestivaLocal($cursor->format('Y-m-d'))) {
            $dias++;
        }
        $cursor->modify('+1 day');
    }
    return $dias;
}

function contarDiasLaboralesMesCompleto($mes, $anio) {
    $inicioMes = new DateTime(sprintf('%04d-%02d-01', $anio, $mes));
    $finMes    = clone $inicioMes; $finMes->modify('last day of this month');
    $dias = 0; $cursor = clone $inicioMes;
    while ($cursor <= $finMes) {
        $dow = (int)$cursor->format('N');
        if ($dow >= 1 && $dow <= 5 && !esFechaFestivaLocal($cursor->format('Y-m-d'))) {
            $dias++;
        }
        $cursor->modify('+1 day');
    }
    return $dias;
}

/* ══════════════════════════════════════════════════════════════
   5. MESES DEL TRIMESTRE
   ══════════════════════════════════════════════════════════════ */
$mesesTrimestre = obtenerMesesEntreFechas(
    $__trActual['fecha_inicio'],
    $__trActual['fecha_fin']
);

/* ══════════════════════════════════════════════════════════════
   6. UTILIDADES DE AGRUPACIÓN (aulas / fichas)
   ══════════════════════════════════════════════════════════════ */
function obtenerJornadaItem($it) {
    if (isset($it['jornada']) && is_numeric($it['jornada'])) return (int)$it['jornada'];
    if (!empty($it['nomjornada'])) {
        $nj = mb_strtolower($it['nomjornada']);
        if (strpos($nj, 'mañana') !== false || strpos($nj, 'manana') !== false) return 1;
        if (strpos($nj, 'tarde') !== false) return 2;
        if (strpos($nj, 'noche') !== false) return 3;
    }
    if (!empty($it['hinihor']) && $it['hinihor'] !== '00:00:00') {
        $h = (int)substr($it['hinihor'], 0, 2);
        if ($h >= 6  && $h < 12) return 1;
        if ($h >= 12 && $h < 18) return 2;
        if ($h >= 18) return 3;
    }
    return null;
}

function _formatearFechaCorta($iso) {
    if (empty($iso) || $iso === 'SIN_FECHA') return '';
    $meses = [1=>'enero',2=>'febrero',3=>'marzo',4=>'abril',5=>'mayo',6=>'junio',
              7=>'julio',8=>'agosto',9=>'septiembre',10=>'octubre',11=>'noviembre',12=>'diciembre'];
    try {
        $d = new DateTime($iso);
        return $d->format('j') . ' de ' . $meses[(int)$d->format('n')];
    } catch (Exception $e) { return ''; }
}

function _ordenarFechas(&$fechas) {
    $fechas = array_values(array_unique(array_filter($fechas, fn($f)=>!empty($f))));
    sort($fechas);
}

function agruparAulasPorDiaYJornada($dt, $iddia, $jornada) {
    if (!$dt) return '';
    $map = [];
    foreach ($dt as $it) {
        $itJornada = obtenerJornadaItem($it);
        $itIddia   = $it['iddia'] ?? null;
        if ($itIddia == $iddia && ($itJornada == $jornada || $itJornada === null)) {
            $amb = trim(($it['idaul'] ?? '') . ' - ' . ($it['aula'] ?? ''));
            if ($amb === ' - ' || empty($amb)) {
                $amb = !empty($it['aula']) ? $it['aula'] : 'Sin aula';
            }
            if (!isset($map[$amb])) $map[$amb] = [];
            $map[$amb][] = $it['fecha_especifica'] ?? 'SIN_FECHA';
        }
    }
    if (empty($map)) return '';
    ksort($map, SORT_NATURAL | SORT_FLAG_CASE);
    $out = '';
    foreach ($map as $nombre => $fechas) {
        _ordenarFechas($fechas);
        $fechasFmt = array_map('_formatearFechaCorta', array_filter($fechas, fn($f)=>$f!=='SIN_FECHA'));
        $line = '<div class="item-line"><span class="item-name">' . htmlspecialchars($nombre) . '</span>';
        if (!empty($fechasFmt)) $line .= ' <span class="item-dates">(' . htmlspecialchars(implode(', ', $fechasFmt)) . ')</span>';
        if (in_array('SIN_FECHA', $fechas, true)) $line .= ' <span class="item-dates">(Sin fecha específica)</span>';
        $line .= '</div>';
        $out .= $line;
    }
    return $out;
}

function agruparFichasPorDiaYJornada($dt, $iddia, $jornada) {
    if (!$dt) return '';
    $grupos = [];
    foreach ($dt as $it) {
        $itJornada = obtenerJornadaItem($it);
        $itIddia   = $it['iddia'] ?? null;
        if ($itIddia == $iddia && $itJornada == $jornada) {
            $grp = trim(($it['idfic'] ?? '') . ' - ' . ($it['nomfic'] ?? ''));
            if ($grp === ' - ' || empty($grp)) {
                if (!empty($it['es_transversal'])) {
                    $grp = '[TRANSVERSAL] ' . ($it['nombre_transversal'] ?? 'Transversal');
                } elseif (!empty($it['es_otros'])) {
                    $grp = '[OTROS] ' . ($it['actividad'] ?? 'Actividad especial');
                } else {
                    $grp = 'Sin ficha';
                }
            }
            if (!isset($grupos[$grp])) {
                $grupos[$grp] = ['fechas'=>[], 'actividades'=>[], 'transversales'=>[]];
            }
            $grupos[$grp]['fechas'][] = $it['fecha_especifica'] ?? 'SIN_FECHA';

            if (!empty($it['es_otros'])) {
                $actNombre = $it['actividad'] ?? '';
                $actClave  = $actNombre . '_' . (string)($it['horas_otros'] ?? '0');
                if ($actNombre !== '') {
                    if (!isset($grupos[$grp]['actividades'][$actClave])) {
                        $grupos[$grp]['actividades'][$actClave] = [
                            'nombre'=>$actNombre,
                            'horas'=>(int)($it['horas_otros'] ?? 0),
                            'fechas'=>[]
                        ];
                    }
                    $grupos[$grp]['actividades'][$actClave]['fechas'][] = $it['fecha_especifica'] ?? 'SIN_FECHA';
                }
            }

            if (!empty($it['es_transversal'])) {
                $transNombre = $it['nombre_transversal'] ?? '';
                if ($transNombre !== '') {
                    if (!isset($grupos[$grp]['transversales'][$transNombre])) {
                        $grupos[$grp]['transversales'][$transNombre] = [
                            'nombre'=>$transNombre,
                            'fechas'=>[]
                        ];
                    }
                    $grupos[$grp]['transversales'][$transNombre]['fechas'][] = $it['fecha_especifica'] ?? 'SIN_FECHA';
                }
            }
        }
    }
    if (empty($grupos)) return '';
    ksort($grupos, SORT_NATURAL | SORT_FLAG_CASE);
    $out = '';
    foreach ($grupos as $gNombre => $info) {
        _ordenarFechas($info['fechas']);
        $fechasFmt = array_map('_formatearFechaCorta', array_filter($info['fechas'], fn($f)=>$f!=='SIN_FECHA'));

        $line = '<div class="item-line"><span class="item-name">' . htmlspecialchars($gNombre) . '</span>';
        if (!empty($fechasFmt)) $line .= ' <span class="item-dates">(' . htmlspecialchars(implode(', ', $fechasFmt)) . ')</span>';
        if (in_array('SIN_FECHA', $info['fechas'], true)) $line .= ' <span class="item-dates">(Sin fecha específica)</span>';
        $line .= '</div>';
        $out .= $line;

        if (!empty($info['actividades'])) {
            uasort($info['actividades'], fn($a,$b)=>strnatcasecmp($a['nombre'],$b['nombre']));
            foreach ($info['actividades'] as $act) {
                _ordenarFechas($act['fechas']);
                $grupoIso = array_values(array_unique(array_filter($info['fechas'], fn($f)=>$f!=='SIN_FECHA')));
                $actIso   = array_values(array_unique(array_filter($act['fechas'],  fn($f)=>$f!=='SIN_FECHA')));
                sort($grupoIso); sort($actIso);
                $grupoSinFecha = in_array('SIN_FECHA', $info['fechas'], true);
                $actSinFecha   = in_array('SIN_FECHA', $act['fechas'], true);
                $mostrar = ($actIso !== $grupoIso) || ($actSinFecha && !$grupoSinFecha) || (!$actSinFecha && $grupoSinFecha);
                $out .= '<div class="item-line"><span class="item-name">' . htmlspecialchars($act['nombre']) . '</span>';
                if (!empty($act['horas'])) $out .= ' <span class="item-hours">(' . (int)$act['horas'] . ' hrs)</span>';
                if ($mostrar && !empty($actIso)) {
                    $out .= ' <span class="item-dates">(' . htmlspecialchars(implode(', ', array_map('_formatearFechaCorta', $actIso))) . ')</span>';
                }
                if ($mostrar && $actSinFecha) {
                    $out .= ' <span class="item-dates">(Sin fecha específica)</span>';
                }
                $out .= '</div>';
            }
        }

        if (!empty($info['transversales'])) {
            uasort($info['transversales'], fn($a,$b)=>strnatcasecmp($a['nombre'],$b['nombre']));
            foreach ($info['transversales'] as $trans) {
                _ordenarFechas($trans['fechas']);
                $grupoIso = array_values(array_unique(array_filter($info['fechas'], fn($f)=>$f!=='SIN_FECHA')));
                $transIso = array_values(array_unique(array_filter($trans['fechas'], fn($f)=>$f!=='SIN_FECHA')));
                sort($grupoIso); sort($transIso);
                $grupoSinFecha = in_array('SIN_FECHA', $info['fechas'], true);
                $transSinFecha = in_array('SIN_FECHA', $trans['fechas'], true);
                $mostrar = ($transIso !== $grupoIso) || ($transSinFecha && !$grupoSinFecha) || (!$transSinFecha && $grupoSinFecha);
                $out .= '<div class="item-line"><span class="item-name">[TRANSVERSAL] ' . htmlspecialchars($trans['nombre']) . '</span>';
                if ($mostrar && !empty($transIso)) {
                    $out .= ' <span class="item-dates">(' . htmlspecialchars(implode(', ', array_map('_formatearFechaCorta', $transIso))) . ')</span>';
                }
                if ($mostrar && $transSinFecha) {
                    $out .= ' <span class="item-dates">(Sin fecha específica)</span>';
                }
                $out .= '</div>';
            }
        }
    }
    return $out;
}

/* ══════════════════════════════════════════════════════════════
   7. PARÁMETROS DEL CONTRATO (simplificado)
   ══════════════════════════════════════════════════════════════ */
$factorForm  = defined('FACTOR_FORMACION') ? FACTOR_FORMACION : 6.4;
$factorOtros = defined('FACTOR_OTROS')      ? FACTOR_OTROS      : 2.1;
$maxContratista = defined('CONTRATISTA_MAX_HORAS') ? CONTRATISTA_MAX_HORAS : 180;

/* ══════════════════════════════════════════════════════════════
   8. CÁLCULO DE TOTALES DEL TRIMESTRE
   ══════════════════════════════════════════════════════════════ */
$mesesEspanol = [
    1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',5=>'Mayo',6=>'Junio',
    7=>'Julio',8=>'Agosto',9=>'Septiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre'
];

$totalEsperadas = 0; $totalEsperadasForm = 0; $totalEsperadasOtros = 0;
$sumFormAsig = 0; $sumOtrosAsig = 0; $sumTotalAsig = 0;
$filasResumen = [];

foreach ($mesesTrimestre as $mesData) {
    $mesNombre   = $mesesEspanol[$mesData['mes']] ?? 'Mes ' . $mesData['mes'];
    $fechaIniMes = sprintf('%04d-%02d-01', $mesData['año'], $mesData['mes']);
    $fechaFinMes = date('Y-m-t', strtotime($fechaIniMes));

    $resumenMes = calcularHorasRango($idusu, $fechaIniMes, $fechaFinMes)['resumen'];
    $horas = [
        'horas_formacion_directa' => $resumenMes['horas_formacion'] ?? 0,
        'horas_otros'             => $resumenMes['horas_otros']     ?? 0,
        'total_horas'             => $resumenMes['total_horas']     ?? 0
    ];

    $diasLabEnRango = contarDiasLaboralesEnRangoMes($mesData['mes'], $mesData['año'], $__trActual['fecha_inicio'], $__trActual['fecha_fin']);
    $diasLabMesFull = contarDiasLaboralesMesCompleto($mesData['mes'], $mesData['año']);
    if ($diasLabMesFull <= 0) $diasLabMesFull = 1;

    $esperadasMes = $maxContratista * ($diasLabEnRango / $diasLabMesFull);
    $totalFactor  = $factorForm + $factorOtros;
    $ratioForm    = $totalFactor > 0 ? ($factorForm / $totalFactor) : 0.75;

    $esperadasFormMes  = $esperadasMes * $ratioForm;
    $esperadasOtrosMes = $esperadasMes - $esperadasFormMes;

    $totalEsperadas      += $esperadasMes;
    $totalEsperadasForm  += $esperadasFormMes;
    $totalEsperadasOtros += $esperadasOtrosMes;
    $sumFormAsig  += $horas['horas_formacion_directa'];
    $sumOtrosAsig += $horas['horas_otros'];
    $sumTotalAsig += $horas['total_horas'];

    $filasResumen[] = [
        'nombre'   => $mesNombre,
        'form'     => $horas['horas_formacion_directa'],
        'otros'    => $horas['horas_otros'],
        'total'    => $horas['total_horas'],
        'espForm'  => $esperadasFormMes,
        'espOtros' => $esperadasOtrosMes,
        'espTotal' => $esperadasMes
    ];
}

/* ══════════════════════════════════════════════════════════════
   9. MAPA DE DÍAS (IDs fijos)
   ══════════════════════════════════════════════════════════════ */
$diasSemana = [
    ['id' => 1042, 'label' => 'LUNES'],
    ['id' => 1043, 'label' => 'MARTES'],
    ['id' => 1044, 'label' => 'MIERCOLES'],
    ['id' => 1045, 'label' => 'JUEVES'],
    ['id' => 1046, 'label' => 'VIERNES'],
    ['id' => 1047, 'label' => 'SABADO'],
    ['id' => 1048, 'label' => 'DOMINGO'],
];

$jornadas = [
    ['id' => 1, 'label' => '07:00 - 13:00'],
    ['id' => 2, 'label' => '13:00 - 18:00'],
    ['id' => 3, 'label' => '18:00 - 22:00'],
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reporte Horario Instructor</title>
<style>
@font-face {
    font-family: 'Roboto';
    src: url('../font/Fugaz_One-Roboto/Roboto/Roboto-VariableFont_wdth\,wght.ttf') format('woff2-variations');
    font-weight: 100 900;
    font-stretch: 75% 125%;
    font-style: normal;
}
@font-face {
    font-family: 'Roboto';
    src: url('../font/Fugaz_One-Roboto/Roboto/Roboto-Italic-VariableFont_wdth\,wght.ttf') format('woff2-variations');
    font-weight: 100 900;
    font-stretch: 75% 125%;
    font-style: italic;
}

* {
    font-family: "Roboto", sans-serif;
    font-size: 11px;
    box-sizing: border-box;
}
b {font-weight: 700;}
table {
    border-collapse: collapse;
    table-layout: fixed;
    width: 100%;
}
table td, table th {
    border: 1px solid #B6BCC6;
    word-wrap: break-word;
    overflow-wrap: break-word;
    word-break: normal;
    white-space: normal;
    hyphens: none;
}
td { text-align: center; vertical-align: middle; font-size: 11px; padding: 3px 5px; }
th { vertical-align: middle; text-align: center; padding: 3px 5px; }

.item-line  { line-height: 1.3; margin: 2px 0; }
.item-name  { font-weight: 700; margin-right: 4px; font-size: 11px; }
.item-hours { font-weight: 600; margin-left: 2px; font-size: 10px; }
.item-dates { color: #333; font-size: 10px; margin-left: 6px; display: none !important;}

.tbl-horario { width: 100%; }
.tbl-horario col.c-hora { width: 8%; }
.tbl-horario col.c-tipo { width: 5%; }
.tbl-horario col.c-dia  { width: 12.43%; }

@media print {
    @page { margin: 1cm; }
    html, body { font-size: 8px; line-height: 1.15; }
    th, td { padding: 2px 3px !important; font-size: 8px !important; }
    .item-name  { font-size: 8.5px !important; }
    .item-hours { font-size: 8px !important; }
    .item-dates { font-size: 7.5px !important; display: none !important;}
    .tbl-horario { page-break-inside: avoid; }
    .no-print { display: none !important; }
}
</style>
</head>
<body>

<!-- ══════════════════════════════════════════════════════════════
     CABECERA INSTITUCIONAL
     ══════════════════════════════════════════════════════════════ -->
<table>
    <colgroup>
        <col style="width:8%;">
        <col style="width:52%;">
        <col style="width:20%;">
        <col style="width:20%;">
    </colgroup>
    <tr>
        <td rowspan="3" align="center"><img src="..\img\sena.png" alt="Logo SENA" width="50px"></td>
        <td>SENA - REGIONAL CUNDINAMARCA</td>
        <td colspan="2" align="center">VIGENCIA: <?= htmlspecialchars($__trActual['anio']) ?></td>
    </tr>
    <tr>
        <td>CENTRO DE DESARROLLO AGROEMPRESARIAL CHIA</td>
        <td colspan="2" align="center"><?= htmlspecialchars(strtoupper($__trActual['nombre'])) ?></td>
    </tr>
    <tr>
        <td></td>
        <td align="center">INICIO: <?= htmlspecialchars($__trActual['fecha_inicio']) ?></td>
        <td align="center">TERMINACION: <?= htmlspecialchars($__trActual['fecha_fin']) ?></td>
    </tr>
</table>
<br>

<!-- ══════════════════════════════════════════════════════════════
     DATOS DEL INSTRUCTOR
     ══════════════════════════════════════════════════════════════ -->
<table>
    <colgroup>
        <col style="width:15%;">
        <col style="width:35%;">
        <col style="width:5%;">
        <col style="width:15%;">
        <col style="width:30%;">
    </colgroup>
    <tr>
        <td align="center"><b>Instructor</b></td>
        <td align="center"><?= htmlspecialchars($nombreInstructor) ?></td>
        <td style="border:none;"></td>
        <td align="center"><b>MUNICIPIO</b></td>
        <td align="center"><?= htmlspecialchars($municipioInstructor) ?></td>
    </tr>
</table>
<br>

<!-- ══════════════════════════════════════════════════════════════
     TABLA DE HORARIO SEMANAL (AULA / FICHA)
     ══════════════════════════════════════════════════════════════ -->
<table class="tbl-horario" cellpadding="0" cellspacing="0">
    <colgroup>
        <col class="c-hora">
        <col class="c-tipo">
        <?php foreach ($diasSemana as $d): ?>
            <col class="c-dia">
        <?php endforeach; ?>
    </colgroup>
    <thead>
        <tr>
            <th colspan="2"></th>
            <?php foreach ($diasSemana as $d): ?>
                <th><b><?= $d['label'] ?></b></th>
            <?php endforeach; ?>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($jornadas as $j): ?>
            <?php foreach (['AULA' => 'agruparAulasPorDiaYJornada', 'FICHA' => 'agruparFichasPorDiaYJornada'] as $tipo => $func): ?>
                <tr>
                    <?php if ($tipo === 'AULA'): ?>
                        <td rowspan="2"><?= $j['label'] ?></td>
                    <?php endif; ?>
                    <td><?= $tipo ?></td>
                    <?php foreach ($diasSemana as $d): ?>
                        <td><?= $func($dtRange, $d['id'], $j['id']) ?></td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </tbody>
</table>

<br><br>

<!-- ══════════════════════════════════════════════════════════════
     RESUMEN DE HORAS POR TRIMESTRE
     ══════════════════════════════════════════════════════════════ -->
<table cellpadding="0" cellspacing="0">
    <thead>
        <tr>
            <th colspan="4">
                <b>RESUMEN DE HORAS POR TRIMESTRE - <?= htmlspecialchars(strtoupper($__trActual['nombre'])) ?> <?= htmlspecialchars($__trActual['anio']) ?></b>
            </th>
        </tr>
        <tr>
            <th><b>MES</b></th>
            <th><b>FORMACIÓN</b></th>
            <th><b>OTROS</b></th>
            <th><b>TOTAL (x/x)</b></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($filasResumen as $fila): ?>
            <tr>
                <td style="text-align: center; font-weight: bold;"><?= htmlspecialchars($fila['nombre']) ?></td>
                <td style="text-align: center;"><?= number_format($fila['form'], 0) ?></td>
                <td style="text-align: center;"><?= number_format($fila['otros'], 0) ?></td>
                <td style="text-align: center; font-weight: bold;">
                    <?= number_format($fila['total'], 0) ?>/<?= number_format($fila['espTotal'], 1) ?>
                </td>
            </tr>
        <?php endforeach; ?>
        <tr style="background-color: #e8e8e8;">
            <td style="text-align: center; font-weight: bold;">TOTAL TRIMESTRE</td>
            <td style="text-align: center; font-weight: bold;"><?= number_format($sumFormAsig, 0) ?></td>
            <td style="text-align: center; font-weight: bold;"><?= number_format($sumOtrosAsig, 0) ?></td>
            <td style="text-align: center; font-weight: bold;">
                <?= number_format($sumTotalAsig, 0) ?>/<?= number_format($totalEsperadas, 1) ?>
            </td>
        </tr>
    </tbody>
</table>

<br><br><br><br>

<script type="text/javascript">window.print();</script>
</body>
</html>