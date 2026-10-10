<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once(__DIR__ . '/../models/desmacfh.php');
require_once(__DIR__ . '/optimg.php');

$desmacfh = new Desmacfh();

// Captura de datos
$idfic = isset($_REQUEST['idfic']) ? trim($_REQUEST['idfic']) : (isset($_SESSION['idfic']) ? $_SESSION['idfic'] : null);
$ope = isset($_REQUEST['ope']) ? $_REQUEST['ope'] : null;
$pg = isset($_REQUEST['pg']) ? $_REQUEST['pg'] : 2419;

// Mes/año del informe informativo (selector en la vista)
$mes = isset($_REQUEST['mes']) ? (int)$_REQUEST['mes'] : (int)date('n');
$anio = isset($_REQUEST['anio']) ? (int)$_REQUEST['anio'] : (int)date('Y');
if ($mes < 1 || $mes > 12) { $mes = (int)date('n'); }
if ($anio < 2000) { $anio = (int)date('Y'); }

$idusu_sesion = isset($_SESSION['idusu']) ? $_SESSION['idusu'] : 1;
$mensajeExito = null;
$mensajeError = null;

// Procesar Acciones (Audio 1 y Audio 2)
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && $ope) {

    // 1. Generar Acta de Cierre / Trimestral según fecha
    if ($ope === 'crearActa') {
        $numacta = isset($_POST['numacta']) ? $_POST['numacta'] : null;
        $conact = isset($_POST['conact']) ? trim($_POST['conact']) : '';
        $coorusu = isset($_POST['coorusu']) ? $_POST['coorusu'] : null;

        if ($idfic && $numacta) {
            $resAct = $desmacfh->insertarActaCierre($idusu_sesion, $idfic, $numacta, $conact, $coorusu);
            if ($resAct) {
                $mensajeExito = "Acta #" . ($numacta == 999 ? "Cierre" : $numacta) . " generada exitosamente.";
            } else {
                $mensajeError = "No se pudo generar el acta o ya se encuentra registrada.";
            }
        } else {
            $mensajeError = "Faltan datos obligatorios para generar el acta.";
        }
    }

    // 2. Registrar Llamado de Atención automático (Audio 2: un solo botón de registro)
    if ($ope === 'regLlamado') {
        $idusuLlam = isset($_POST['idusu']) ? $_POST['idusu'] : null;
        $obsllam = isset($_POST['obsllam']) ? trim($_POST['obsllam']) : '';

        if ($idusuLlam && !empty($obsllam)) {
            $resLlam = $desmacfh->registrarLlamado($idusuLlam, $idfic, $obsllam, $idusu_sesion);
            if ($resLlam['exito']) {
                $mensajeExito = "Llamado de atención " . $resLlam['tipo'] . " registrado correctamente. El documento PDF (" . $resLlam['tipo'] . ") quedó disponible para descargar.";
            } else {
                $mensajeError = "Error al registrar el llamado de atención.";
            }
        } else {
            $mensajeError = "Debe ingresar una observación para registrar el llamado de atención.";
        }
    }

    // 3. Registrar Deserción con coordinador y pantallazo de Sofía Plus (Audio 2)
    if ($ope === 'regDesercion') {
        $idusuDes = isset($_POST['idusu']) ? $_POST['idusu'] : null;
        $obsdec = isset($_POST['obsdec']) ? trim($_POST['obsdec']) : '';
        $coorusu = isset($_POST['coorusu']) ? $_POST['coorusu'] : null;
        $archivoImg = isset($_FILES['arc_sofia']) ? $_FILES['arc_sofia'] : null;

        if ($idusuDes && !empty($obsdec)) {
            // Procesar el pantallazo de SENA Sofía Plus (una sola imagen) con opti()
            $nomImagen = null;
            if ($archivoImg && isset($archivoImg['tmp_name']) && !empty($archivoImg['tmp_name'])) {
                $dirDest = __DIR__ . '/../img/inasi';
                if (!is_dir($dirDest)) {
                    mkdir($dirDest, 0777, true);
                }
                $nomImagen = opti($archivoImg, 'sofia_' . $idusuDes, 'img/inasi', time());
            }

            if (!$nomImagen) {
                $mensajeError = "Debe adjuntar la evidencia (pantallazo) de SENA Sofía Plus para registrar la deserción.";
            } else {
                $resDes = $desmacfh->registrarDesercion($idusuDes, $idfic, $obsdec, $coorusu, $nomImagen);
                if ($resDes['exito']) {
                    $mensajeExito = "Deserción registrada exitosamente con evidencia de SENA Sofía Plus adjunta.";
                } else {
                    $mensajeError = "Error al procesar el registro de deserción.";
                }
            }
        } else {
            $mensajeError = "Debe proporcionar la justificación o motivo de la deserción.";
        }
    }
}

// Carga de Datos para la Vista
$todasFichas = $desmacfh->getAllFichas();

// Si no se especificó idfic, tomar la primera disponible
if (!$idfic && !empty($todasFichas)) {
    $idfic = $todasFichas[0]['idfic'];
}

$datosFicha = null;
$actasCalculadas = [];
$actasRegistradas = [];
$reporteInfo = null;
$mesesDisponibles = [];
$coordinadores = $desmacfh->getCoordinadores();

if ($idfic) {
    $datosFicha = $desmacfh->getFicha($idfic);
    if ($datosFicha) {
        $actasCalculadas = $desmacfh->calcularActasPorFecha($datosFicha['finific'], $datosFicha['ffinfic'], $idfic);
        $actasRegistradas = $desmacfh->getAllActasFic($idfic);
        $mesesDisponibles = $desmacfh->getMesesDisponibles($idfic);
        $reporteInfo = $desmacfh->getReporteInformativo($idfic, $mes, $anio);
    }
}
?>
