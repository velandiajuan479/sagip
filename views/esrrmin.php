<?php
require_once("../models/seguridad.php");
require_once "../models/esmmin.php";
include('../models/conexion.php');

// --------CONTROLADOR----------
$esmmin = new Esmmin();
date_default_timezone_set('America/Bogota');

// Determinar el tipo de reporte solicitado ('minuta' o 'mensual')
$tipo = isset($_REQUEST['tipo']) ? $_REQUEST['tipo'] : 'minuta';

// 1. Filtros para la primera sección (Minuta filtrada por fecha/documento)
$fechaActual = date("Y-m-d");
$fechos  = isset($_REQUEST['fechos']) && $_REQUEST['fechos'] !== '' ? $_REQUEST['fechos'] : $fechaActual;
$fhlle   = isset($_REQUEST['fhlle']) && $_REQUEST['fhlle'] !== '' ? $_REQUEST['fhlle'] : $fechos;
$ndocusu = isset($_REQUEST['ndocusu']) ? trim($_REQUEST['ndocusu']) : '';

if ($tipo === 'minuta') {
    $esmmin->setIdusu($ndocusu);
    $dat = $esmmin->getTodo($fechos, $fhlle);
}

// 2. Filtros para la segunda sección (Reporte Mensual Tops)
$nomMeses = [
    1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 5 => 'Mayo', 6 => 'Junio',
    7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
];
$mesActual  = (int) date('m');
$anioActual = (int) date('Y');
$mes  = isset($_REQUEST['mes'])  ? (int) $_REQUEST['mes']  : $mesActual;
$anio = isset($_REQUEST['anio']) ? (int) $_REQUEST['anio'] : $anioActual;

if ($mes < 1 || $mes > 12) {
    $mes = $mesActual;
}
$nomMesSel = $nomMeses[$mes];

if ($tipo === 'mensual') {
    $topIngresos   = $esmmin->getTopIngresosMes($mes, $anio, 5);
    $topMasHoras   = $esmmin->getTopPermanenciaMes($mes, $anio, 'DESC', 5);
    $topMenosHoras = $esmmin->getTopPermanenciaMes($mes, $anio, 'ASC', 5);
}

function segAhoras($segundos) {
    $segundos = (int) $segundos;
    if ($segundos <= 0) return '00h 00m';
    $h = floor($segundos / 3600);
    $m = floor(($segundos % 3600) / 60);
    return sprintf('%02dh %02dm', $h, $m);
}

function urlimg($url) {
    if (file_exists($url)) {
        return "data:image/png;base64," . base64_encode(file_get_contents($url));
    }
    return "";
}

function tablaTop($titulo, $filas, $cols) {
    $html = '<h4 style="margin-top:20px;">' . $titulo . '</h4>';
    $html .= '<table border="1" style="border-collapse: collapse; border-color: #000; width:100%; margin: 10px 0px;">';
    $html .= '<thead><tr>';
    foreach ($cols as $c) $html .= '<th>' . $c . '</th>';
    $html .= '</tr></thead><tbody>';
    if ($filas) {
        foreach ($filas as $f) {
            $html .= '<tr>';
            $html .= '<td>' . $f['ndocusu'] . ' ' . $f['nomusu'] . '</td>';
            $html .= '<td>' . $f['nomper'] . '</td>';
            if (isset($f['total_segundos'])) $html .= '<td>' . segAhoras($f['total_segundos']) . '</td>';
            $html .= '<td>' . $f['total_ingresos'] . '</td>';
            $html .= '</tr>';
        }
    } else {
        $html .= '<tr><td colspan="' . count($cols) . '">Sin registros para este período.</td></tr>';
    }
    $html .= '</tbody></table>';
    return $html;
}

$ancho = 750;
$html = '<body>';
$html .= '<table style="width:' . $ancho . 'px;">';
$html .= '<tr><td style="text-align: center;">';
$imgSrc = urlimg('../img/prueba_.png');
if ($imgSrc !== "") {
    $html .= '<img src="' . $imgSrc . '" width="80px"><br><br>';
}
$html .= '</td></tr></table>';

// MODO 1: REPORTE DE LA MINUTA (SOLO LA PRIMERA SECCIÓN)
if ($tipo === 'minuta') {
    $html .= '<table width="' . $ancho . 'px" cellpadding="5px" cellspacing="0">';
    $html .= '<tr><td style="text-align: center; font-size:16px"><b>Reporte de Minuta de Acceso</b></td></tr>';
    $html .= '<tr><td style="text-align: center; font-size:12px">Rango: ' . htmlspecialchars($fechos) . ' al ' . htmlspecialchars($fhlle) . ($ndocusu ? ' | Documento: ' . htmlspecialchars($ndocusu) : '') . '</td></tr>';
    $html .= '</table>';

    $html .= '<table border="1" style="border-collapse: collapse; border-color: #000; width:100%; margin: 15px 0px;">';
    $html .= '<thead><tr>
                <th>Usuario / Ficha</th>
                <th>Perfil</th>
                <th>Entrada</th>
                <th>Salida</th>
                <th>Tiempo</th>
                <th>Estado</th>
                <th>Elementos</th>
              </tr></thead><tbody>';

    if (!empty($dat) && is_array($dat)) {
        foreach ($dat as $d) {
            $html .= '<tr>';
            $html .= '<td><b>' . htmlspecialchars($d['ndocusu'] ?? '') . ' - ' . htmlspecialchars($d['nomusu'] ?? '') . '</b><br><small>' . htmlspecialchars($d['idfic'] ?? '') . ' ' . htmlspecialchars($d['nomfic'] ?? '') . '</small></td>';
            $html .= '<td>' . htmlspecialchars($d['nomper'] ?? '') . '</td>';
            $html .= '<td>' . htmlspecialchars($d['fechos'] ?? '') . '</td>';
            $html .= '<td>' . htmlspecialchars($d['fhlle'] ?? '') . '</td>';
            $html .= '<td>' . htmlspecialchars($d['tiempo'] ?? '') . '</td>';
            $html .= '<td>' . (($d['tipmin'] ?? '') === 'I' ? 'Entró' : 'Salió') . '</td>';
            
            $elementosTexto = '';
            if (!empty($d['ideles'])) {
                $eles = explode(";", $d['ideles']);
                foreach ($eles as $ele) {
                    $ele = trim($ele);
                    if ($ele !== '') {
                        $dtela = $esmmin->selOneEle($ele);
                        if (!empty($dtela) && isset($dtela[0])) {
                            $elementosTexto .= htmlspecialchars($dtela[0]['nomele']) . "<br>";
                        }
                    }
                }
            }
            $html .= '<td>' . $elementosTexto . '</td>';
            $html .= '</tr>';
        }
    } else {
        $html .= '<tr><td colspan="7" style="text-align: center;">No se encontraron registros para este filtro.</td></tr>';
    }
    $html .= '</tbody></table>';
}

// MODO 2: REPORTE MENSUAL (SOLO LA SEGUNDA SECCIÓN)
if ($tipo === 'mensual') {
    $html .= '<table width="' . $ancho . 'px" cellpadding="5px" cellspacing="0">';
    $html .= '<tr><td style="text-align: center; font-size:16px"><b>Reporte Mensual de Minuta - ' . $nomMesSel . ' ' . $anio . '</b></td></tr>';
    $html .= '</table>';

    $html .= tablaTop('Top 5 - Mayor cantidad de entradas y salidas', $topIngresos, ['Usuario', 'Perfil', 'Total registros']);
    $html .= tablaTop('Top 5 - Mayor cantidad de horas en el centro', $topMasHoras, ['Usuario', 'Perfil', 'Horas acumuladas', 'Registros']);
    $html .= tablaTop('Top 5 - Menor cantidad de horas en el centro', $topMenosHoras, ['Usuario', 'Perfil', 'Horas acumuladas', 'Registros']);
}

$html .= '<script>window.onload = function() { window.print(); }</script>';
$html .= '</body>';

echo $html;
exit();
?>