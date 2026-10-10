<?php
require_once("models/desmlat.php");

$mlat = new Desmlat();

$fin = isset($_REQUEST['fin']) ? $_REQUEST['fin'] : NULL;
$ffi = isset($_REQUEST['ffi']) ? $_REQUEST['ffi'] : NULL;
$ndocusu = isset($_REQUEST['ndocusu']) ? $_REQUEST['ndocusu'] : NULL;
$idfic = isset($_REQUEST['idfic']) && $_REQUEST['idfic'] != "" ? $_REQUEST['idfic'] : NULL;

$dfi = $mlat->getFicha();

$idusu = NULL;
if (!empty($ndocusu)) {
  $usu = $mlat->getUser($ndocusu);
  if ($usu) $idusu = $usu[0]['idusu'];
}

$datLlam = NULL;
$totLlam = NULL;
if ($fin || $ffi || $idusu || $idfic) {
  $datLlam = $mlat->getLlamados($fin, $ffi, $idusu, $idfic);
  $totLlam = $mlat->getTotales($fin, $ffi, $idusu, $idfic);
}

function reportLlam($fin, $ffi, $ndocusu, $idfic) {
  $inte = ($fin || $ffi || $ndocusu || $idfic) ? '?' : '';
  $fin = ($fin) ? '&fin=' . $fin : '';
  $ffi = ($ffi) ? '&ffi=' . $ffi : '';
  $ndocusu = ($ndocusu) ? '&ndocusu=' . $ndocusu : '';
  $idfic = ($idfic) ? '&idfic=' . $idfic : '';
  $html = '';
  $html .= '<a href="views/desrlat.php' . $inte . $fin . $ffi . $ndocusu . $idfic . '" title="Imprimir" target="_blank">
              <i class="fa-solid fa-print fa-2x" style="color:#027902;"></i>
            </a>';
  $html .= '<a href="views/desrlat.php' . $inte . $fin . $ffi . $ndocusu . $idfic . '&pdf=ok" title="PDF" target="_blank">
              <i class="fa-solid fa-file-pdf fa-2x" style="color:#027902;"></i>
            </a>';
  return $html;
}
?>