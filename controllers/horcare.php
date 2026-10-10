<?php
require_once("models/hormare.php");
require_once("models/hormacceso.php");

// Alcance por rol (módulo HyA) para la cinta de alcance de la vista.
$hacceso = new HormAcceso();
$scopeCtx = $hacceso->getContexto();
$scopeFichas = $hacceso->getFichasPermitidas($scopeCtx);
$scopeRol = $hacceso->etiquetaRol($scopeCtx);
$scopeN = count($scopeFichas);
$scopeTotal = !empty($scopeCtx['is_admin']);

$mare = new Mare();

$pg = $pg ?? '';

$idare = (isset($_REQUEST["idare"]) && $_REQUEST["idare"] !== "") ? (int)$_REQUEST["idare"] : null;
if ($idare === 0) { $idare = null; }

$nomare = isset($_POST["nomare"]) ? trim($_POST["nomare"]) : null;
$idusu  = (isset($_POST["idusu"]) && $_POST["idusu"] !== "") ? (int)$_POST["idusu"] : null;
$opera  = isset($_REQUEST["opera"]) ? $_REQUEST["opera"] : null;

$datOne = null;
$datAll = [];
$datUsu = [];

// Solo administrador del sistema (Perfil 21 = Coordinación HyA, Perfil 2 = Administrador Centro)
$idper    = $_SESSION['idper'] ?? null;
$idpergen = $_SESSION['idpergen'] ?? null;
$isAdmin  = ($idper == 21 || $idper == 2 || $idpergen == 2);

if ($idare !== null) {
    $mare->setidare($idare);
}

if ($opera == "save") {
    if (!$isAdmin) {
        echo "<script>alert('Acceso denegado. Solo el administrador del sistema puede asignar usuarios al área.'); window.location.href='home.php?pg=$pg';</script>";
        exit;
    }

    $nomare = ($nomare === "") ? null : $nomare;
    $mare->setNomare($nomare);
    $mare->setIdusu($idusu);

    if ($idare === null) {
        $mare->save();
    } else {
        $mare->edit();
    }

    $idare  = null;
    $datOne = null;
}

if ($opera == "eli" && $idare !== null) {
    if (!$isAdmin) {
        echo "<script>alert('Acceso denegado. Solo el administrador del sistema puede eliminar áreas.'); window.location.href='home.php?pg=$pg';</script>";
        exit;
    }

    $mare->setidare($idare);

    if ($mare->del()) {
        echo "<script>window.location.href='home.php?pg=$pg&msg=eliminado';</script>";
    } else {
        echo "<script>setTimeout(() => { window.location.href = 'home.php?pg=$pg'; }, 3000);</script>";
    }
    exit;
}

if ($opera == "edi" && $idare !== null && $isAdmin) {
    $datOne = $mare->getOne();
}

$datUsu = $isAdmin ? $mare->getAllusu() : [];
$datAll = $mare->getAll();
$dtTot = $mare->getTotales();
$totAre = isset($dtTot[0]['total']) ? (int)$dtTot[0]['total'] : 0;
$totAsig = isset($dtTot[0]['asignadas']) ? (int)$dtTot[0]['asignadas'] : 0;
$totSin = isset($dtTot[0]['sin']) ? (int)$dtTot[0]['sin'] : 0;
$datAct = $mare->getAreAsig();
$datInact = $mare->getAreSin();

/* =========================================================================
   LÓGICA DE NEGOCIO DEL DASHBOARD: ÁREAS Y USUARIOS ASIGNADOS
   =========================================================================
   R1 Área asignada        = area.idusu con valor.
   R2 Carga de oferta      = nº de programas de formación del área (programa.idare).
   R3 Usuario operativo    = usuario activo con perfil que no es aprendiz/candidato/nulo.
   R4 Usuario asignado     = usuario con al menos un área a cargo.
   R5 Alerta               = área con responsable inactivo (actusu = 0).
   ========================================================================= */

// --- Indicadores de usuarios / oferta (una sola consulta) ---
$dtUsuTot  = $mare->getTotUsuResumen();
$totUsuAre = isset($dtUsuTot['usuarios_con_area'])     ? (int)$dtUsuTot['usuarios_con_area']     : 0;
$totUsuIna = isset($dtUsuTot['responsables_inactivos']) ? (int)$dtUsuTot['responsables_inactivos'] : 0;
$totUsuOpe = isset($dtUsuTot['usuarios_operativos'])   ? (int)$dtUsuTot['usuarios_operativos']   : 0;
$totUsuSin = isset($dtUsuTot['usuarios_sin_area'])     ? (int)$dtUsuTot['usuarios_sin_area']     : 0;
$totProg   = isset($dtUsuTot['programas'])             ? (int)$dtUsuTot['programas']             : 0;

// --- Cobertura de asignación de áreas + semáforo (>=80 óptima, >=50 parcial, <50 crítica) ---
$porCob = ($totAre > 0) ? (int)round(($totAsig * 100) / $totAre) : 0;
$cobNiv = 'baja';
$cobTxt = 'Cobertura crítica';
if ($porCob >= 80) {
    $cobNiv = 'alta';
    $cobTxt = 'Cobertura óptima';
} elseif ($porCob >= 50) {
    $cobNiv = 'media';
    $cobTxt = 'Cobertura parcial';
}

// --- Alertas operativas: áreas sin oferta (R2) y áreas con responsable inactivo (R5) ---
$totAreSinOferta = 0;
foreach ($datAll as $da) {
    if (isset($da['programas']) && (int)$da['programas'] === 0) { $totAreSinOferta++; }
}
$totAreInaResp = 0;
foreach ($datAct as $da) {
    if (isset($da['actusu']) && (int)$da['actusu'] === 0) { $totAreInaResp++; }
}

// --- Usuarios responsables agrupados por usuario (R4): áreas, carga y programas ---
$datUsuAre = $mare->getUsuAreas();
$usuAreas  = array();
foreach ($datUsuAre as $ua) {
    $idu = $ua['idusu'];
    if (!isset($usuAreas[$idu])) {
        $usuAreas[$idu] = array(
            'idusu'  => $idu,
            'nomusu' => $ua['nomusu'],
            'emausu' => $ua['emausu'] ?? null,
            'nomper' => $ua['nomper'] ?? null,
            'nomcen' => $ua['nomcen'] ?? null,
            'actusu' => isset($ua['actusu']) ? (int)$ua['actusu'] : 1,
            'tot'    => 0,
            'prog'   => 0,
            'areas'  => array()
        );
    }
    $usuAreas[$idu]['tot']++;
    $usuAreas[$idu]['prog'] += isset($ua['programas']) ? (int)$ua['programas'] : 0;
    $usuAreas[$idu]['areas'][] = array(
        'idare'     => $ua['idare'],
        'nomare'    => $ua['nomare'],
        'programas' => isset($ua['programas']) ? (int)$ua['programas'] : 0
    );
}

// --- Usuarios operativos activos sin área (candidatos a responsable) ---
$limUsu    = 200;
$datUsuSin = $mare->getUsuSinAreas($limUsu);
$hayMasUsu = ($totUsuSin > count($datUsuSin));
?>
