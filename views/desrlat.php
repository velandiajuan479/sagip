<?php
session_start();
require_once("../models/seguridad.php");
require_once("../models/conexion.php");

ini_set('memory_limit', '4096M');
require_once '../vendor/autoload.php';

use Dompdf\Dompdf;

include('../models/desmlat.php');

$mlat = new Desmlat();

$fin = isset($_GET['fin']) ? $_GET['fin'] : NULL;
$ffi = isset($_GET['ffi']) ? $_GET['ffi'] : NULL;
$ndocusu = isset($_GET['ndocusu']) ? $_GET['ndocusu'] : NULL;
$idfic = isset($_GET['idfic']) && $_GET['idfic'] != "" ? $_GET['idfic'] : NULL;
$pdf = isset($_GET['pdf']) ? $_GET['pdf'] : NULL;

$idusu = NULL;
if (!empty($ndocusu)) {
  $usu = $mlat->getUser($ndocusu);
  if ($usu) $idusu = $usu[0]['idusu'];
}

$dat = ($fin || $ffi || $idusu || $idfic) ? $mlat->getLlamados($fin, $ffi, $idusu, $idfic) : NULL;
$tot = ($fin || $ffi || $idusu || $idfic) ? $mlat->getTotales($fin, $ffi, $idusu, $idfic) : NULL;

date_default_timezone_set('America/Bogota');
$mes = array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
$fecha = date('d') . " de " . $mes[date('m') - 1] . " de " . date('Y');
$fecha2 = date('YmdHis');
$ancho = 750;

function urlimg($url)
{
    $imagenBase64 = "data:image/png;base64," . base64_encode(file_get_contents($url));
    return $imagenBase64;
}

$html = '';
$html .= '<body>';
$html .= '<table style="width:' . $ancho . 'px;">';
$html .= '<tr>';
$html .= '<td style="text-align: center;">';
$html .= '<img src="' . urlimg('../image/sena.png') . '" width="80px">';
$html .= '<br><br>';
$html .= '</td>';
$html .= '</tr>';
$html .= '</table>';
$html .= '<table width="' . $ancho . 'px" cellpadding="5px" cellspacing="0">';
$html .= '<tr>';
$html .= '<td colspan="3" style="text-align: center; font-size:14px">';
$html .= '<strong>REPORTE LLAMADOS DE ATENCIÓN</strong><br>';
$html .= 'SERVICIO NACIONAL DE APRENDIZAJE - SENA<br>';
$html .= 'CENTRO DE DESARROLLO AGROEMPRESARIAL CHÍA - CUNDINAMARCA<br>';
$html .= 'Fecha de generación: ' . $fecha . ' - ' . date('H:i:s');
$html .= '</td>';
$html .= '</tr>';
$html .= '</table>';

if ($fin || $ffi) {
  $html .= '<br><table width="' . $ancho . 'px" cellpadding="3px" cellspacing="0" border="1">';
  $html .= '<tr><th>Periodo</th><th>Documento</th><th>Ficha</th></tr>';
  $html .= '<tr>';
  $html .= '<td>' . ($fin ? date("d/m/Y", strtotime($fin)) : 'Inicio') . ' - ' . ($ffi ? date("d/m/Y", strtotime($ffi)) : 'Actualidad') . '</td>';
  $html .= '<td>' . ($ndocusu ? $ndocusu : 'Todos') . '</td>';
  $html .= '<td>' . ($idfic ? $idfic : 'Todas') . '</td>';
  $html .= '</tr>';
  $html .= '</table>';
}

if ($tot) {
  $html .= '<br><table width="' . $ancho . 'px" cellpadding="3px" cellspacing="0" border="1">';
  $html .= '<tr><th>Aprendices con llamado</th><th>Llamados verbales</th><th>Llamados escritos</th><th>Total de llamados</th></tr>';
  $html .= '<tr style="text-align:center;">';
  $html .= '<td>' . $tot['aprendices'] . '</td>';
  $html .= '<td>' . $tot['verbales'] . '</td>';
  $html .= '<td>' . $tot['escritos'] . '</td>';
  $html .= '<td>' . $tot['total'] . '</td>';
  $html .= '</tr>';
  $html .= '</table>';
}

$html .= '<br><table border="1" style="border-collapse: collapse; border-color: #000; width:' . $ancho . 'px; margin: 10px 0px;">';
$html .= '<thead>';
$html .= '<tr>';
$html .= '<th>#</th>';
$html .= '<th>Aprendiz</th>';
$html .= '<th>Ficha</th>';
$html .= '<th>Tipo</th>';
$html .= '<th>Fecha</th>';
$html .= '<th>Observación</th>';
$html .= '</tr>';
$html .= '</thead>';
$html .= '<tbody>';

if ($dat) {
  $i = 1;
  foreach ($dat as $d) {
    $html .= '<tr>';
    $html .= '<td>' . $i++ . '</td>';
    $html .= '<td>' . $d['ndocusu'] . ' - ' . $d['nomusu'] . '</td>';
    $html .= '<td>' . $d['idfic'] . ' ' . $d['nomfic'] . '</td>';
    $html .= '<td>' . ($d['tipllam'] == 1 ? 'Verbal' : 'Escrito') . '</td>';
    $html .= '<td>' . $d['fecllam'] . '</td>';
    $html .= '<td>' . htmlspecialchars($d['obsllam']) . '</td>';
    $html .= '</tr>';
  }
} else {
  $html .= '<tr><td colspan="6" style="text-align:center;">No se encontraron llamados de atención en el periodo.</td></tr>';
}

$html .= '</tbody>';
$html .= '</table>';

if (isset($_GET['pdf'])) {
  if ($_GET['pdf'] == 'ok') {
    $dompdf = new Dompdf();
    $paper_size = array(0, 0, 612, 792);
    $dompdf->loadHtml($html);
    $dompdf->setPaper($paper_size);
    $dompdf->render();
    $dompdf->stream("Reporte_Llamados_Atencion_$fecha2.pdf");
    exit();
  }
}

echo $html;
echo "<script>window.print();</script>";
?>