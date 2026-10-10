<?php
require_once('models/conmdcpr.php');
$mdcpr = new Mdcpr();

// Usuario logueado
$idusu = isset($_SESSION['idusu']) ? (int)$_SESSION['idusu'] : 0;

// Formatos disponibles en la barra superior (3 estándar del detalle + los registrados en BD)
$formatos = $mdcpr->getFormatosFijosDetalle();
foreach($mdcpr->getCompromisos() as $cd){
	if((int)$cd['idcom'] <= 0) continue;
	$n = trim((string)$cd['nom_dcpr']);
	if($n !== '' && !in_array($n, $formatos)) $formatos[] = $n;
}

// Datos del aprendiz consultado (por documento o ficha)
$busqueda = isset($_REQUEST['q']) ? trim($_REQUEST['q']) : '';
$datAprendiz = null;
$detalleCargado = null;

$ope = isset($_REQUEST['ope']) ? $_REQUEST['ope'] : '';
$forSel = isset($_POST['for_dcpr']) ? trim($_POST['for_dcpr']) : (isset($_GET['for_dcpr']) ? trim($_GET['for_dcpr']) : $formatos[0]);
$forEnviado = isset($_POST['for_dcpr']) && trim($_POST['for_dcpr']) !== '';

// Guardar el detalle de documentación
if($ope == 'guardar' && $_SERVER['REQUEST_METHOD'] == 'POST'){
	$idusuDet = isset($_POST['id_usu']) ? (int)$_POST['id_usu'] : 0;

	$clausulasSel = array();
	if(isset($_POST['clausulas']) && is_array($_POST['clausulas'])){
		foreach($_POST['clausulas'] as $cv){
			$cv = trim((string)$cv);
			if($cv !== ''){ $clausulasSel[] = $cv; }
		}
	}
	if(isset($_POST['clausulas']) && !is_array($_POST['clausulas']) && trim((string)$_POST['clausulas']) !== ''){
		$clausulasSel = array_map('trim', explode(',', (string)$_POST['clausulas']));
	}
	$clausulasSel = array_values(array_filter($clausulasSel, function($v){ return $v !== ''; }));

	$dat = array(
		'for_dcpr'      => isset($_POST['for_dcpr']) ? trim($_POST['for_dcpr']) : $formatos[0],
		'id_usu'        => $idusuDet,
		'ndocumento'    => isset($_POST['ndocumento']) ? trim($_POST['ndocumento']) : '',
		'nom_apre'      => isset($_POST['nom_apre']) ? trim($_POST['nom_apre']) : '',
		'tdoc_apre'     => isset($_POST['tdoc_apre']) ? trim($_POST['tdoc_apre']) : '',
		'programa'      => isset($_POST['programa']) ? trim($_POST['programa']) : '',
		'centro'        => isset($_POST['centro']) ? trim($_POST['centro']) : '',
		'idfic'         => isset($_POST['idfic']) ? trim($_POST['idfic']) : '',
		'es_menor'      => (isset($_POST['es_menor']) && $_POST['es_menor'] == '1') ? 1 : 0,
		'nom_acu'       => isset($_POST['nom_acu']) ? trim($_POST['nom_acu']) : '',
		'doc_acu'       => isset($_POST['doc_acu']) ? trim($_POST['doc_acu']) : '',
		'parentesco'    => isset($_POST['parentesco']) ? trim($_POST['parentesco']) : '',
		'consentimiento'=> (isset($_POST['consentimiento']) && $_POST['consentimiento'] == '1') ? 1 : 0,
		'clausulas'     => implode(',', $clausulasSel),
		'compromiso_esp'=> isset($_POST['compromiso_esp']) ? trim($_POST['compromiso_esp']) : '',
		'fec_dili'      => isset($_POST['fec_dili']) && $_POST['fec_dili'] !== '' ? $_POST['fec_dili'] : null
	);

	if($idusuDet > 0 && $dat['nom_apre'] !== ''){
		$mdcpr->guardarDetalle($dat);
		$forSel = $dat['for_dcpr'];
		$busqueda = $dat['ndocumento'];
		$datAprendiz = $mdcpr->buscarAprendiz($busqueda);
		$detalleCargado = $mdcpr->getDetalle($idusuDet, $forSel);
		echo '<div class="d-none" data-notify="success" data-msg="El detalle de documentación fue guardado correctamente."></div>';
	} else {
		echo '<div class="d-none" data-notify="warning" data-msg="Debe buscar primero un aprendiz válido antes de guardar."></div>';
	}
}

// Búsqueda del aprendiz (documento o ficha)
if($busqueda === '' && isset($_POST['q'])) $busqueda = trim($_POST['q']);
if($busqueda !== ''){
	$datAprendiz = $mdcpr->buscarAprendiz($busqueda);
	if($datAprendiz){
		$detalleCargado = $mdcpr->getDetalle((int)$datAprendiz['idusu']);
		if($detalleCargado && !$forEnviado){
			$forSel = $detalleCargado['for_dcpr'];
			if(!in_array($forSel, $formatos)) $forSel = $formatos[0];
		}
	}
}

// Cláusulas normativas del formato seleccionado
$clausulas = $mdcpr->getClausulas($forSel);

// Id de página por defecto si no viene definido
$pg = isset($pg) ? $pg : 2113;
?>