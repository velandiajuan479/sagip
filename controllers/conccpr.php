<?php
require_once('models/conmdcpr.php');
$mdcpr = new Mdcpr();

// Usuario logueado (o aprendiz seleccionado en el detalle via ?idusu=)
$idusu = isset($_GET['idusu']) ? (int)$_GET['idusu'] : (isset($_SESSION['idusu']) ? (int)$_SESSION['idusu'] : 0);

// Datos del aprendiz para el documento
$datUsu = array();
$datCom = array();
$datHist = array();
$detDcpr = null;
if($idusu){
    $datUsu  = $mdcpr->getDatosUsuario($idusu);
    $datHist = $mdcpr->getDiligenciamientos($idusu);
}
$datCom = $mdcpr->getCompromisos();

// Documento seleccionado: por nombre (viene del detalle vía for_dcpr), por idcom,
// o se recuerda el del último detalle de documentación guardado del aprendiz.
$forSelCpr = isset($_GET['for_dcpr']) ? trim((string)$_GET['for_dcpr']) : '';
$idcom = isset($_GET['idcom']) ? (int)$_GET['idcom'] : 0;
$selCom = null;
if($forSelCpr !== '' && !$selCom) $selCom = $mdcpr->buscarPorNombre($forSelCpr);
if($idcom && !$selCom) $selCom = $mdcpr->getCompromiso($idcom);
if(!$selCom && $idusu > 0){
	$ultDet = $mdcpr->getDetalle($idusu);
	if($ultDet && trim((string)$ultDet['for_dcpr']) !== ''){
		$selCom = $mdcpr->buscarPorNombre(trim((string)$ultDet['for_dcpr']));
	}
}
if(!$selCom && !empty($datCom)) $selCom = $datCom[0];

// Nombre del documento seleccionado (título dinámico de la hoja)
$nomDocSel = '';
if($selCom){
	$nomDocSel = trim((string)$selCom['nom_dcpr']);
	if($nomDocSel === '') $nomDocSel = trim((string)$selCom['p1com']);
	if($nomDocSel === '') $nomDocSel = trim((string)$selCom['p2com']);
}

// Datos del detalle registrado en el taller de documentación (devuelve es_menor/acudiente)
if($idusu > 0 && $selCom){
	$detDcpr = $mdcpr->getDetalle($idusu, $nomDocSel);
	if(!$detDcpr) $detDcpr = $mdcpr->getDetalle($idusu);
}

// Condición: mayor o menor de edad
// 1) Si el formato es el destinado a menores (p2com) => menor siempre.
// 2) Si el detalle del taller registró la condición => se usa esa.
// 3) Como respaldo: tipo de documento Tarjeta de Identidad (TI) => menor.
$esMenor = ($selCom && isset($selCom['p2com']) && trim((string)$selCom['p2com']) !== '') ? true : false;
if(!$esMenor && $detDcpr && isset($detDcpr['es_menor']) && $detDcpr['es_menor'] !== ''){
	$esMenor = ((int)$detDcpr['es_menor'] == 1);
}elseif(!$esMenor && !empty($datUsu[0]) && strtoupper(trim((string)$datUsu[0]['tdoc'])) === 'TI'){
	$esMenor = true;
}

$ope = isset($_POST['ope']) ? $_POST['ope'] : '';

// Guardar registro de fecha de diligenciamiento
if($ope == 'guardar'){
    $idcomG = isset($_POST['idcom']) ? (int)$_POST['idcom'] : 0;
    if($idusu > 0 && $idcomG > 0){
        $fec = $mdcpr->guardarDiligenciamiento($idusu, $idcomG);
        $datHist = $mdcpr->getDiligenciamientos($idusu);
        echo '<div class="d-none" data-notify="success" data-msg="Fecha de diligenciamiento registrada correctamente." data-redir="home.php?pg=' . $pg . '"></div>';
    } else {
        echo '<div class="d-none" data-notify="error" data-msg="Debe iniciar sesión para registrar el diligenciamiento."></div>';
    }
}

// Generador: crear un documento nuevo con los textos indicados
if($ope == 'crear'){
    $titulo = isset($_POST['titulo']) ? trim($_POST['titulo']) : '';
    $texto  = isset($_POST['texto'])  ? trim($_POST['texto'])  : '';
    if($titulo !== '' && $texto !== ''){
        $nidcom = $mdcpr->crearCompromiso($titulo, $texto);
        if($nidcom > 0){
            echo '<div class="d-none" data-notify="success" data-msg="El documento #' . $nidcom . ' fue creado correctamente." data-redir="home.php?pg=' . $pg . '&idcom=' . $nidcom . '"></div>';
        } else {
            echo '<div class="d-none" data-notify="error" data-msg="No fue posible crear el documento."></div>';
        }
    } else {
        echo '<div class="d-none" data-notify="error" data-msg="El título y el texto del documento son obligatorios."></div>';
    }
}
?>