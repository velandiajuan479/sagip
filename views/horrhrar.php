<?php
// La vista asume que el controlador horchrar.php preparó:
// $idaul, $dtOneHeader (cabecera), $__trActual (trimestre/período), $dtOne (datos rango)
// Fallback: si se carga directamente la vista, inicializar mínimos sin cambiar formato

if (!isset($idaul)) {
    $idaul = $_REQUEST['idaul'] ?? $_REQUEST['aul'] ?? null;
}

/**
 * Utilidades de apilado (sin cambiar formato de tabla)
 */
if (!function_exists('_formatearFechaCorta')) {
    function _formatearFechaCorta($iso)
    {
        if (empty($iso) || $iso === 'SIN_FECHA') return '';
        $meses = [1=>'enero',2=>'febrero',3=>'marzo',4=>'abril',5=>'mayo',6=>'junio',7=>'julio',8=>'agosto',9=>'septiembre',10=>'octubre',11=>'noviembre',12=>'diciembre'];
        $d = new DateTime($iso);
        return $d->format('j') . ' de ' . $meses[(int)$d->format('n')];
    }
}

if (!function_exists('_ordenarFechas')) {
    function _ordenarFechas(&$fechas)
    {
        $fechas = array_values(array_unique(array_filter($fechas, fn($f) => !empty($f))));
        usort($fechas, fn($a, $b) => strcmp($a, $b));
    }
}

if (!function_exists('agruparInstructoresPorDiaYJornada')) {
    function agruparInstructoresPorDiaYJornada($dtOne, $iddia, $jornada)
    {
        if (!$dtOne) return '';
        $map = [];
        foreach ($dtOne as $it) {
            if (($it['iddia'] ?? null) == $iddia && ($it['jornada'] ?? null) == $jornada) {
                $nombre = $it['un'] ?? '';
                if ($nombre === '') continue;
                if (!isset($map[$nombre])) $map[$nombre] = [];
                $map[$nombre][] = $it['fecha_especifica'] ?? 'SIN_FECHA';
            }
        }
        if (empty($map)) return '';
        ksort($map, SORT_NATURAL | SORT_FLAG_CASE);
        $out = '';
        foreach ($map as $nombre => $fechas) {
            _ordenarFechas($fechas);
            $fechasFmt = array_map('_formatearFechaCorta', array_filter($fechas, fn($f) => $f !== 'SIN_FECHA'));

            $line = '<div class="item-line"><span class="item-name">' . htmlspecialchars($nombre) . '</span>';
            if (!empty($fechasFmt)) {
                $line .= ' <span class="item-dates">(' . htmlspecialchars(implode(', ', $fechasFmt)) . ')</span>';
            }
            if (in_array('SIN_FECHA', $fechas, true)) {
                $line .= ' <span class="item-dates">(Sin fecha específica)</span>';
            }
            $line .= '</div>';
            $out .= $line;
        }
        return $out;
    }
}

if (!function_exists('agruparGruposPorDiaYJornada')) {
    function agruparGruposPorDiaYJornada($dtOne, $iddia, $jornada)
    {
        if (!$dtOne) return '';
        // Agrupar por ficha ("idfic - nf"). Apilar actividades "otros" bajo el grupo
        $grupos = [];
        foreach ($dtOne as $it) {
            if (($it['iddia'] ?? null) == $iddia && ($it['jornada'] ?? null) == $jornada) {
                $grupo = trim(($it['idfic'] ?? '') . ' - ' . ($it['nf'] ?? ''));
                if ($grupo === ' - ') continue;
                if (!isset($grupos[$grupo])) {
                    $grupos[$grupo] = ['fechas' => [], 'actividades' => []];
                }
                $grupos[$grupo]['fechas'][] = $it['fecha_especifica'] ?? 'SIN_FECHA';
                if (!empty($it['es_otros'])) {
                    $actNombre = $it['actividad'] ?? '';
                    $actClave  = $actNombre . '_' . (string)($it['horas_otros'] ?? '0');
                    if ($actNombre !== '') {
                        if (!isset($grupos[$grupo]['actividades'][$actClave])) {
                            $grupos[$grupo]['actividades'][$actClave] = [
                                'nombre' => $actNombre,
                                'horas'  => (int)($it['horas_otros'] ?? 0),
                                'fechas' => []
                            ];
                        }
                        $grupos[$grupo]['actividades'][$actClave]['fechas'][] = $it['fecha_especifica'] ?? 'SIN_FECHA';
                    }
                }
            }
        }
        if (empty($grupos)) return '';
        ksort($grupos, SORT_NATURAL | SORT_FLAG_CASE);
        $out = '';
        foreach ($grupos as $grupoNombre => $info) {
            _ordenarFechas($info['fechas']);
            $fechasFmt = array_map('_formatearFechaCorta', array_filter($info['fechas'], fn($f) => $f !== 'SIN_FECHA'));

            $line = '<div class="item-line"><span class="item-name">' . htmlspecialchars($grupoNombre) . '</span>';
            if (!empty($fechasFmt)) {
                $line .= ' <span class="item-dates">(' . htmlspecialchars(implode(', ', $fechasFmt)) . ')</span>';
            }
            if (in_array('SIN_FECHA', $info['fechas'], true)) {
                $line .= ' <span class="item-dates">(Sin fecha específica)</span>';
            }
            $line .= '</div>';
            $out .= $line;

            // Actividades apiladas bajo el grupo
            if (!empty($info['actividades'])) {
                uasort($info['actividades'], fn($a, $b) => strnatcasecmp($a['nombre'], $b['nombre']));
                foreach ($info['actividades'] as $act) {
                    _ordenarFechas($act['fechas']);
                    $grupoIso      = array_values(array_unique(array_filter($info['fechas'], fn($f) => $f !== 'SIN_FECHA')));
                    $actIso        = array_values(array_unique(array_filter($act['fechas'], fn($f) => $f !== 'SIN_FECHA')));
                    sort($grupoIso);
                    sort($actIso);
                    $grupoSinFecha = in_array('SIN_FECHA', $info['fechas'], true);
                    $actSinFecha   = in_array('SIN_FECHA', $act['fechas'], true);
                    $actFechasFmt  = array_map('_formatearFechaCorta', $actIso);

                    $out .= '<div class="item-line"><span class="item-name">' . htmlspecialchars($act['nombre']) . '</span>';
                    if (!empty($act['horas'])) {
                        $out .= ' <span class="item-hours">(' . (int)$act['horas'] . ' hrs)</span>';
                    }
                    $mostrarFechasActividad = ($actIso !== $grupoIso) || ($actSinFecha && !$grupoSinFecha) || (!$actSinFecha && $grupoSinFecha);
                    if ($mostrarFechasActividad) {
                        if (!empty($actFechasFmt)) {
                            $out .= ' <span class="item-dates">(' . htmlspecialchars(implode(', ', $actFechasFmt)) . ')</span>';
                        }
                        if ($actSinFecha) {
                            $out .= ' <span class="item-dates">(Sin fecha específica)</span>';
                        }
                    }
                    $out .= '</div>';
                }
            }
        }
        return $out;
    }
}

// Fallback si la vista se carga directamente sin pasar por el controlador
if (!isset($mrphau) || !isset($dtOneHeader) || !isset($dtOne) || !isset($__trActual)) {
    require_once __DIR__ . '/../models/conexion.php';
    require_once __DIR__ . '/../models/hormhrar.php';
    $mrphau = new Mrphau();

    if (empty($idaul)) {
        $aulasConHorario = $mrphau->getAulasConHorarios();
        $idaul = !empty($aulasConHorario) ? $aulasConHorario[0] : null;
    }

    $mrphau->setIdaul($idaul);
    $dtOneHeader = $mrphau->getOne();
    $todosTrimestres = $mrphau->getAllTrimestres();

    $anioActual = (int)date('Y');
    $hoy = new DateTime('now', new DateTimeZone('America/Bogota'));
    $__trActual = null;

    foreach ($todosTrimestres as $tr) {
        if ((int)$tr['anio'] === $anioActual && !empty($tr['fecha_inicio']) && !empty($tr['fecha_fin'])) {
            $ini = new DateTime($tr['fecha_inicio']);
            $fin = new DateTime($tr['fecha_fin']);
            if ($hoy >= $ini && $hoy <= $fin) {
                $__trActual = [
                    'id'           => $tr['id'],
                    'nombre'       => $tr['trimestre'] ?: ($tr['titulo'] ?: 'Trimestre'),
                    'año'          => (int)$tr['anio'],
                    'fecha_inicio' => $tr['fecha_inicio'],
                    'fecha_fin'    => $tr['fecha_fin']
                ];
                break;
            }
        }
    }

    // Usar rango real del año actual en horario (NO el de años anteriores)
    if (!$__trActual && !empty($idaul)) {
        $rangoAula = $mrphau->getRangoFechasAula($idaul, $anioActual);
        if (!empty($rangoAula['fecha_min']) && !empty($rangoAula['fecha_max'])) {
            $__trActual = [
                'id'           => null,
                'nombre'       => 'Vigencia ' . $anioActual,
                'año'          => $anioActual,
                'fecha_inicio' => $anioActual . '-01-01',
                'fecha_fin'    => $anioActual . '-12-31'
            ];
        }
    }

    if (!$__trActual) {
        $__trActual = [
            'id'           => null,
            'nombre'       => 'Trimestre',
            'año'          => $anioActual,
            'fecha_inicio' => date('Y-m-01'),
            'fecha_fin'    => date('Y-m-t')
        ];
    }

    $dtOne = $mrphau->getHorariosAulaEnRango($idaul, $__trActual['fecha_inicio'], $__trActual['fecha_fin']);
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Horario por Aula</title>
    <style>
        /* Configuración de fuente Roboto */
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

        * { font-family: "Roboto", sans-serif; }
        html, body { font-size: 12px; line-height: 1.25; }
        td { text-align: left; vertical-align: top; }
        .item-line { line-height: 1.25; margin: 2px 0; }
        .item-name { font-weight: 700; margin-right: 4px; }
        .item-hours { font-weight: 600; margin-left: 2px; }
        .item-dates { color: #333; font-size: 11px; margin-left: 6px; }
        thead th { background: transparent; }
        tbody tr:nth-child(odd)  { background: transparent; }
        tbody tr:nth-child(even) { background: transparent; }
        th, td { padding: 6px 8px; }

        table { border-collapse: collapse; }
        table td, table th { border: 1px solid #B6BCC6 !important; }
        .header-box { border: 1px solid #9aa0a6 !important; }

        /* Igualar ancho de columnas de días */
        table[cellpadding] { table-layout: fixed; width: 100% !important; }
        table[cellpadding] th, table[cellpadding] td { box-sizing: border-box; }
        table[cellpadding] th:nth-child(1),
        table[cellpadding] td:nth-child(1) { width: 70px; }
        table[cellpadding] th:nth-child(2),
        table[cellpadding] td:nth-child(2) { width: 26px; }
        table[cellpadding] th:nth-child(3),  table[cellpadding] td:nth-child(3) { width: 140px; }
        table[cellpadding] th:nth-child(4),  table[cellpadding] td:nth-child(4),
        table[cellpadding] th:nth-child(5),  table[cellpadding] td:nth-child(5),
        table[cellpadding] th:nth-child(6),  table[cellpadding] td:nth-child(6),
        table[cellpadding] th:nth-child(7),  table[cellpadding] td:nth-child(7),
        table[cellpadding] th:nth-child(8),  table[cellpadding] td:nth-child(8),
        table[cellpadding] th:nth-child(9),  table[cellpadding] td:nth-child(9) { width: 115px; }

        /* Títulos de columna en vertical */
        .col-item-title {
            writing-mode: vertical-rl;
            transform: rotate(180deg);
            white-space: nowrap;
            font-weight: 700;
            text-align: center;
            vertical-align: middle;
            padding: 2px 4px;
            width: 22px;
        }

        /* Impresión en tamaño Carta */
        @page { size: Letter; margin: 10mm; }
        @media print {
            html, body { width: 216mm; height: 279mm; font-size: 9px; line-height: 1.15; margin: 0; padding: 0; }
            table { width: 100% !important; page-break-inside: avoid; }
            table[width] { width: 100% !important; }
            img { max-width: 100%; height: auto; }
            thead { display: table-header-group; }
            tr { page-break-inside: avoid; }
            th, td { padding: 3px 5px; }
            table[cellpadding] th:nth-child(1), table[cellpadding] td:nth-child(1) { width: 70px; }
            table[cellpadding] th:nth-child(2), table[cellpadding] td:nth-child(2) { width: 20px; }
            table[cellpadding] th:nth-child(3), table[cellpadding] td:nth-child(3) { width: 120px; }
            table[cellpadding] th:nth-child(4),  table[cellpadding] td:nth-child(4),
            table[cellpadding] th:nth-child(5),  table[cellpadding] td:nth-child(5),
            table[cellpadding] th:nth-child(6),  table[cellpadding] td:nth-child(6),
            table[cellpadding] th:nth-child(7),  table[cellpadding] td:nth-child(7),
            table[cellpadding] th:nth-child(8),  table[cellpadding] td:nth-child(8),
            table[cellpadding] th:nth-child(9),  table[cellpadding] td:nth-child(9) { width: 105px; }
            .col-item-title { font-size: 9px; padding: 1px 2px; width: 18px; }
        }
    </style>
</head>

<body>

    <?php
    // Datos de cabecera del aula
    $cabNomAula = !empty($dtOneHeader[0]['nomaul']) ? $dtOneHeader[0]['nomaul'] : ('Aula ' . $idaul);
    $cabMuni    = !empty($dtOneHeader[0]['nomubi'])  ? $dtOneHeader[0]['nomubi']  : 'CHIA';
    $cabPiso    = isset($dtOneHeader[0]['piso'])   && $dtOneHeader[0]['piso']   !== '' ? $dtOneHeader[0]['piso']   : '-';
    $cabBloque  = isset($dtOneHeader[0]['bloqau']) && $dtOneHeader[0]['bloqau'] !== '' ? $dtOneHeader[0]['bloqau'] : '-';
    ?>

    <table width="950px" cellspacing="0px">
        <tr>
            <td rowspan="3" align="center" style="width: 85px;">
                <img src="../image/sena.png" width="75px" onerror="this.onerror=null; this.src='image/sena.png';">
            </td>
            <td style="font-size: 19px; font-weight: 400;">SENA - REGIONAL CUNDINAMARCA</td>
            <td colspan="2" align="center" style="border: 1px solid #000;">VIGENCIA: <?= htmlspecialchars($__trActual['año'] ?? date('Y')) ?></td>
        </tr>
        <tr>
            <td style="font-size: 17px; font-weight: 400;">CENTRO DE DESARROLLO AGROEMPRESARIAL CHÍA</td>
            <td colspan="2" align="center" style="border: 1px solid #000; font-size: 14px;"><?= htmlspecialchars(strtoupper($__trActual['nombre'] ?? 'TRIMESTRE')) ?></td>
        </tr>
        <tr>
            <td></td>
            <td align="center" style="border: 1px solid #000; font-size: 12px;">INICIO: <?= htmlspecialchars($__trActual['fecha_inicio'] ?? '') ?></td>
            <td align="center" style="border: 1px solid #000; font-size: 12px;">TERMINACION: <?= htmlspecialchars($__trActual['fecha_fin'] ?? '') ?></td>
        </tr>
    </table>
    <br>

    <table width="950px" cellspacing="0px">
        <tr>
            <td align="center" style="border: 1px solid #000; width: 110px;"><b>AMBIENTE</b></td>
            <td align="center" style="border: 1px solid #000; font-weight: 500;"><?= htmlspecialchars($cabNomAula) ?> (Cód. <?= htmlspecialchars($idaul) ?>)</td>
            <td align="center" style="border: 1px solid #000; width: 60px;"><b>PISO</b></td>
            <td align="center" style="border: 1px solid #000; width: 60px;"><?= htmlspecialchars($cabPiso) ?></td>
            <td align="center" style="border: 1px solid #000; width: 70px;"><b>BLOQUE</b></td>
            <td align="center" style="border: 1px solid #000; width: 60px;"><?= htmlspecialchars($cabBloque) ?></td>
            <td align="center" style="border: 1px solid #000; width: 90px;"><b>MUNICIPIO</b></td>
            <td align="center" style="border: 1px solid #000;"><?= htmlspecialchars($cabMuni) ?></td>
        </tr>
    </table>
    <br>

    <table width="950px" cellpadding="3px" cellspacing="0">
        <colgroup>
            <col style="width: 90px">
            <col style="width: 26px">
            <col style="width: 130px"> <!-- Lunes -->
            <col style="width: 130px"> <!-- Martes -->
            <col style="width: 130px"> <!-- Miércoles -->
            <col style="width: 130px"> <!-- Jueves -->
            <col style="width: 130px"> <!-- Viernes -->
            <col style="width: 130px"> <!-- Sábado -->
            <col style="width: 130px"> <!-- Domingo -->
        </colgroup>
        <thead>
            <th colspan="2"></th>
            <th style="border: 2px solid #000;"><b>LUNES</b></th>
            <th style="border: 2px solid #000;"><b>MARTES</b></th>
            <th style="border: 2px solid #000;"><b>MIERCOLES</b></th>
            <th style="border: 2px solid #000;"><b>JUEVES</b></th>
            <th style="border: 2px solid #000;"><b>VIERNES</b></th>
            <th style="border: 2px solid #000;"><b>SABADO</b></th>
            <th style="border: 2px solid #000;"><b>DOMINGO</b></th>
        </thead>
        <tr>
            <td rowspan="2" style="border: 2px solid #000;">07:00 - 13:00</td>
            <td style="border: 2px solid #000;" class="col-item-title">INSTRUCTOR</td>
            <td style="border: 2px solid #000;"><?= agruparInstructoresPorDiaYJornada($dtOne, 1042, 1) ?></td>
            <td style="border: 2px solid #000;"><?= agruparInstructoresPorDiaYJornada($dtOne, 1043, 1) ?></td>
            <td style="border: 2px solid #000;"><?= agruparInstructoresPorDiaYJornada($dtOne, 1044, 1) ?></td>
            <td style="border: 2px solid #000;"><?= agruparInstructoresPorDiaYJornada($dtOne, 1045, 1) ?></td>
            <td style="border: 2px solid #000;"><?= agruparInstructoresPorDiaYJornada($dtOne, 1046, 1) ?></td>
            <td style="border: 2px solid #000;"><?= agruparInstructoresPorDiaYJornada($dtOne, 1047, 1) ?></td>
            <td style="border: 2px solid #000;"><?= agruparInstructoresPorDiaYJornada($dtOne, 1048, 1) ?></td>
        </tr>
        <tr>
            <td style="border: 2px solid #000;" class="col-item-title">GRUPO</td>
            <td style="border: 2px solid #000;"><?= agruparGruposPorDiaYJornada($dtOne, 1042, 1) ?></td>
            <td style="border: 2px solid #000;"><?= agruparGruposPorDiaYJornada($dtOne, 1043, 1) ?></td>
            <td style="border: 2px solid #000;"><?= agruparGruposPorDiaYJornada($dtOne, 1044, 1) ?></td>
            <td style="border: 2px solid #000;"><?= agruparGruposPorDiaYJornada($dtOne, 1045, 1) ?></td>
            <td style="border: 2px solid #000;"><?= agruparGruposPorDiaYJornada($dtOne, 1046, 1) ?></td>
            <td style="border: 2px solid #000;"><?= agruparGruposPorDiaYJornada($dtOne, 1047, 1) ?></td>
            <td style="border: 2px solid #000;"><?= agruparGruposPorDiaYJornada($dtOne, 1048, 1) ?></td>
        </tr>
        <tr>
            <td rowspan="2" style="border: 2px solid #000;">13:00 - 18:00</td>
            <td style="border: 2px solid #000;" class="col-item-title">INSTRUCTOR</td>
            <td style="border: 2px solid #000;"><?= agruparInstructoresPorDiaYJornada($dtOne, 1042, 2) ?></td>
            <td style="border: 2px solid #000;"><?= agruparInstructoresPorDiaYJornada($dtOne, 1043, 2) ?></td>
            <td style="border: 2px solid #000;"><?= agruparInstructoresPorDiaYJornada($dtOne, 1044, 2) ?></td>
            <td style="border: 2px solid #000;"><?= agruparInstructoresPorDiaYJornada($dtOne, 1045, 2) ?></td>
            <td style="border: 2px solid #000;"><?= agruparInstructoresPorDiaYJornada($dtOne, 1046, 2) ?></td>
            <td style="border: 2px solid #000;"><?= agruparInstructoresPorDiaYJornada($dtOne, 1047, 2) ?></td>
            <td style="border: 2px solid #000;"><?= agruparInstructoresPorDiaYJornada($dtOne, 1048, 2) ?></td>
        </tr>
        <tr>
            <td style="border: 2px solid #000;" class="col-item-title">GRUPO</td>
            <td style="border: 2px solid #000;"><?= agruparGruposPorDiaYJornada($dtOne, 1042, 2) ?></td>
            <td style="border: 2px solid #000;"><?= agruparGruposPorDiaYJornada($dtOne, 1043, 2) ?></td>
            <td style="border: 2px solid #000;"><?= agruparGruposPorDiaYJornada($dtOne, 1044, 2) ?></td>
            <td style="border: 2px solid #000;"><?= agruparGruposPorDiaYJornada($dtOne, 1045, 2) ?></td>
            <td style="border: 2px solid #000;"><?= agruparGruposPorDiaYJornada($dtOne, 1046, 2) ?></td>
            <td style="border: 2px solid #000;"><?= agruparGruposPorDiaYJornada($dtOne, 1047, 2) ?></td>
            <td style="border: 2px solid #000;"><?= agruparGruposPorDiaYJornada($dtOne, 1048, 2) ?></td>
        </tr>
        <tr>
            <td rowspan="2" style="border: 2px solid #000;">18:00 - 22:00</td>
            <td style="border: 2px solid #000;" class="col-item-title">INSTRUCTOR</td>
            <td style="border: 2px solid #000;"><?= agruparInstructoresPorDiaYJornada($dtOne, 1042, 3) ?></td>
            <td style="border: 2px solid #000;"><?= agruparInstructoresPorDiaYJornada($dtOne, 1043, 3) ?></td>
            <td style="border: 2px solid #000;"><?= agruparInstructoresPorDiaYJornada($dtOne, 1044, 3) ?></td>
            <td style="border: 2px solid #000;"><?= agruparInstructoresPorDiaYJornada($dtOne, 1045, 3) ?></td>
            <td style="border: 2px solid #000;"><?= agruparInstructoresPorDiaYJornada($dtOne, 1046, 3) ?></td>
            <td style="border: 2px solid #000;"><?= agruparInstructoresPorDiaYJornada($dtOne, 1047, 3) ?></td>
            <td style="border: 2px solid #000;"><?= agruparInstructoresPorDiaYJornada($dtOne, 1048, 3) ?></td>
        </tr>
        <tr>
            <td style="border: 2px solid #000;" class="col-item-title">GRUPO</td>
            <td style="border: 2px solid #000;"><?= agruparGruposPorDiaYJornada($dtOne, 1042, 3) ?></td>
            <td style="border: 2px solid #000;"><?= agruparGruposPorDiaYJornada($dtOne, 1043, 3) ?></td>
            <td style="border: 2px solid #000;"><?= agruparGruposPorDiaYJornada($dtOne, 1044, 3) ?></td>
            <td style="border: 2px solid #000;"><?= agruparGruposPorDiaYJornada($dtOne, 1045, 3) ?></td>
            <td style="border: 2px solid #000;"><?= agruparGruposPorDiaYJornada($dtOne, 1046, 3) ?></td>
            <td style="border: 2px solid #000;"><?= agruparGruposPorDiaYJornada($dtOne, 1047, 3) ?></td>
            <td style="border: 2px solid #000;"><?= agruparGruposPorDiaYJornada($dtOne, 1048, 3) ?></td>
        </tr>
    </table>

    <script type="text/javascript">
        window.addEventListener('load', function () {
            window.print();
        });
    </script>
</body>
</html>
