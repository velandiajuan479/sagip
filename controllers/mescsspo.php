<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../models/mesmsspo.php';

$mesmsspo = new Mesmsspo();

$idcen = isset($_SESSION['idcen']) ? (int) $_SESSION['idcen'] : null;
$idper = isset($_SESSION['idper']) ? (int) $_SESSION['idper'] : null;
$idusu_session = isset($_SESSION['idusu']) ? (int) $_SESSION['idusu'] : null;

$idsop = isset($_REQUEST['idsop']) ? (int) $_REQUEST['idsop'] : 0;
$iddet = isset($_REQUEST['iddet']) ? (int) $_REQUEST['iddet'] : 0;
$ope = isset($_REQUEST['ope']) ? trim($_REQUEST['ope']) : '';
$action = isset($_REQUEST['action']) ? trim($_REQUEST['action']) : '';

if (isset($_POST['ajax']) && $_POST['ajax'] === 'asignar_tecnico') {
    header('Content-Type: application/json; charset=utf-8');
    if (!$idsop) {
        echo json_encode(['success' => false, 'message' => 'ID de soporte no válido']);
        exit;
    }

    $idusu2_target = isset($_POST['idusu2']) && $_POST['idusu2'] !== '' 
        ? (int) $_POST['idusu2'] 
        : $idusu_session;

    $mesmsspo->setIdsop($idsop);
    $ok = $mesmsspo->asignarTecnico($idusu2_target);

    if ($ok) {
        $ticketInfo = $mesmsspo->getOne();
        $nomTecnico = !empty($ticketInfo['nom_tecnico']) ? $ticketInfo['nom_tecnico'] : 'Sin asignar';
        echo json_encode(['success' => true, 'nomtecnico' => $nomTecnico, 'idusu2' => $idusu2_target]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No se pudo procesar la asignación']);
    }
    exit;
}

$mesmsspo->setIdsop($idsop);
$mesmsspo->setIddet($iddet);

if ($ope === 'save' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $idusu_registro = $idusu_session ?: (isset($_POST['idusu']) && $_POST['idusu'] !== '' ? (int) $_POST['idusu'] : 0);
    $detest = isset($_POST['detest']) ? (int) $_POST['detest'] : 0;
    $detcom = isset($_POST['detcom']) ? trim($_POST['detcom']) : '';

    $mesmsspo->setIdusu($idusu_registro);
    $mesmsspo->setDetest($detest);
    $mesmsspo->setDetcom($detcom);
    $mesmsspo->setDetevi(null);

    $newIddet = $mesmsspo->save();
    if ($newIddet) {
        $mesmsspo->setIddet($newIddet);
        if (!empty($_FILES['foto']['name']) && isset($_FILES['foto']['error']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            if (function_exists('opti')) {
                $filename = opti($_FILES['foto'], $newIddet, 'img/fevi', 'fdetevi');
                if ($filename) {
                    $mesmsspo->setDetevi('img/fevi/' . $filename);
                    $mesmsspo->updateDetevi();
                }
            }
        }
    }

    if ($detest === 1074) {
        $mesmsspo->finalizarSoporte();
    }

    if (!isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
        $pg = isset($_GET['pg']) ? $_GET['pg'] : '2204';
        echo "<script>window.location.href='home.php?pg=" . $pg . "&idsop=" . $idsop . "';</script>";
        exit;
    }
}

if ($ope === 'del' && $iddet) {
    $mesmsspo->del();
    $pg = isset($_GET['pg']) ? $_GET['pg'] : '2204';
    echo "<script>window.location.href='home.php?pg=" . $pg . "&idsop=" . $idsop . "';</script>";
    exit;
}

$datOne = $idsop ? $mesmsspo->getOne() : null;
$dat = $idsop ? $mesmsspo->getAll() : [];
$dtSelsop = $mesmsspo->getSelSop($idcen);
$dtEst = $mesmsspo->getEstado();

$ultimoEstadoRow = $idsop ? $mesmsspo->getUltimoEstado() : null;
$estadoFinalizado = ($ultimoEstadoRow && (int) $ultimoEstadoRow['detest'] === 1074);