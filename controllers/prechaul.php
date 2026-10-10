<?php
$rootPath = file_exists('models/premhaul.php') ? '' : '../';
require_once $rootPath . 'models/premhaul.php';

$mpraul = new Mpraul();

// Obtener parámetros de filtro (POST o GET para persistencia y exportación)
$fin = !empty($_REQUEST['fin']) ? trim($_REQUEST['fin']) : NULL;
$ffi = !empty($_REQUEST['ffi']) ? trim($_REQUEST['ffi']) : NULL;
$ndcusu = !empty($_REQUEST['ndcusu']) ? trim($_REQUEST['ndcusu']) : NULL;
$idaul = !empty($_REQUEST['idaul']) ? (int)$_REQUEST['idaul'] : NULL;
$estado = !empty($_REQUEST['estado']) ? trim($_REQUEST['estado']) : NULL;
$ope = isset($_REQUEST['ope']) ? trim($_REQUEST['ope']) : NULL;

$idusu = NULL;
$nomUsuFiltro = NULL;
$errorUsu = NULL;

if (!empty($ndcusu)) {
  $userRows = $mpraul->getUser($ndcusu);
  if (!empty($userRows)) {
    $idusu = (int)$userRows[0]['idusu'];
    $nomUsuFiltro = $userRows[0]['nomusu'];
  } else {
    $idusu = -1; // ID que no coincide para mostrar tabla vacía sin error
    $errorUsu = "No se encontró ningún usuario o instructor con el documento: " . htmlspecialchars($ndcusu);
  }
}

// 1. Consulta regular para la vista
$dtAula = $mpraul->getAulas();
$dtHis = $mpraul->getAll($fin, $ffi, $idusu, $idaul, $estado);
$stats = $mpraul->getEstadisticasAulas();

// Helper para enlace de impresión PDF
if (!function_exists('printHis')) {
  function printHis($fin, $ffi, $idusu, $idaul, $estado = NULL) {
    $params = [];
    if (!empty($fin)) $params['fin'] = $fin;
    if (!empty($ffi)) $params['ffi'] = $ffi;
    if (!empty($idusu) && $idusu > 0) $params['idusu'] = $idusu;
    if (!empty($idaul)) $params['idaul'] = $idaul;
    if (!empty($estado)) $params['estado'] = $estado;

    $queryStr = !empty($params) ? '?' . http_build_query($params) : '';
    
    return '<a href="views/pdfaul.php' . $queryStr . '" title="Imprimir Reporte PDF" target="_blank" class="btn btn-outline-danger" style="display:inline-flex; align-items:center; gap:6px;">
              <i class="fa-solid fa-file-pdf fa-lg"></i> Imprimir PDF
            </a>';
  }
}

if (!function_exists('exportExcelLink')) {
  function exportExcelLink($fin, $ffi, $ndcusu, $idaul, $estado = NULL) {
    $params = ['pg' => 1533, 'ope' => 'exportExcel'];
    if (!empty($fin)) $params['fin'] = $fin;
    if (!empty($ffi)) $params['ffi'] = $ffi;
    if (!empty($ndcusu)) $params['ndcusu'] = $ndcusu;
    if (!empty($idaul)) $params['idaul'] = $idaul;
    if (!empty($estado)) $params['estado'] = $estado;

    return 'home.php?' . http_build_query($params);
  }
}
?>