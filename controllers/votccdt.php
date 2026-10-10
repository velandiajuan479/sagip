<?php
require_once 'models/votmcdt.php';

if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION['idusu']) || !isset($_SESSION['idper'])) {
    header("Location: ../index.php?error=sesion");
    exit();
}

$perfilesPermitidos = [1, 2, 3, 4];
if (!in_array($_SESSION['idper'], $perfilesPermitidos)) {
    header("Location: ../home.php?error=permiso");
    exit();
}

$votmcdt = new Votmcdt();

$idusu = isset($_REQUEST['idusu']) ? $_REQUEST['idusu'] : NULL;
$fidcen = isset($_REQUEST['fidcen']) ? $_REQUEST['fidcen'] : $_SESSION['idcen'];
$fidfic = isset($_REQUEST['fidfic']) ? $_REQUEST['fidfic'] : NULL;
$fidval = isset($_REQUEST['fidval']) ? $_REQUEST['fidval'] : 1;

$ndocusu = isset($_POST['ndocusu']) ? trim($_POST['ndocusu']) : NULL;
$noca = isset($_POST['noca']) ? trim($_POST['noca']) : NULL;
$actusu = isset($_POST['actusu']) ? (int)$_POST['actusu'] : 1;
$fotcan = isset($_POST['fotcan']) ? $_POST['fotcan'] : NULL;

$arch = isset($_FILES['arch']['name']) ? $_FILES['arch']['name'] : NULL;
$opera = isset($_REQUEST['opera']) ? $_REQUEST['opera'] : NULL;
$pg = isset($_REQUEST['pg']) ? $_REQUEST['pg'] : 1200;

$ndocusu_buscar = isset($_REQUEST['ndocusu_buscar']) ? trim($_REQUEST['ndocusu_buscar']) : NULL;
$encontrado = NULL;

$dat = NULL;
$datOne = NULL;
$dcentros = $votmcdt->getCentros();
$djornadas = $votmcdt->getJornadas();
$dfichas = [];
$mensaje = '';
$tipoMensaje = '';

if ($fidcen) {
    $dfichas = $votmcdt->getFichasByCentro($fidcen);
}

if ($fidfic) {
    $dat = $votmcdt->getByFicha($fidfic);
} else {
    $dat = $votmcdt->getAll($fidval);
}

if ($opera === "Buscar" && $ndocusu_buscar) {
    $encontrado = $votmcdt->buscarPorDocumento($ndocusu_buscar);
    if (!$encontrado) {
        $mensaje = "No se encontró ningún usuario activo con el documento: " . htmlspecialchars($ndocusu_buscar);
        $tipoMensaje = "error";
    } elseif ($encontrado['es_candidato']) {
        $mensaje = "El usuario ya está registrado como candidato representante (N° " . htmlspecialchars($encontrado['noca']) . ")";
        $tipoMensaje = "error";
        $encontrado = NULL;
    }
}

if ($arch && $idusu) {
    require_once 'optimg.php';
    $fotcan = opti($_FILES['arch'], $idusu, "fcan", "can");
}

if ($opera === "Registrar" && $ndocusu) {
    $errores = [];
    
    if (empty($noca)) $errores[] = "El número de candidato es obligatorio";
    elseif (!preg_match('/^\d{1,3}$/', $noca)) $errores[] = "El número de candidato debe ser numérico (máx 3 dígitos)";
    
    $usuarioEncontrado = $votmcdt->buscarPorDocumento($ndocusu);
    if (!$usuarioEncontrado) {
        $errores[] = "El usuario con documento " . htmlspecialchars($ndocusu) . " no fue encontrado o no está activo";
    } elseif ($usuarioEncontrado['es_candidato']) {
        $errores[] = "Este usuario ya está registrado como candidato";
    }
    if ($votmcdt->checkNocaExists($noca)) {
        $errores[] = "Ya existe un candidato con este número";
    }

    if (empty($errores)) {
        $votmcdt->setIdusu($usuarioEncontrado['idusu']);
        
        if ($arch) {
            require_once 'optimg.php';
            $fotcan = opti($_FILES['arch'], $usuarioEncontrado['idusu'], "fcan", "can");
        }
        
        if ($votmcdt->registrarExistente($noca, 3, $actusu, $fotcan)) {
            if ($usuarioEncontrado['idfic']) {
                $votmcdt->asignarFicha($usuarioEncontrado['idusu'], $usuarioEncontrado['idfic']);
            }
            $mensaje = "Candidato registrado correctamente";
            $tipoMensaje = "success";
        } else {
            $mensaje = "Error al registrar el candidato";
            $tipoMensaje = "error";
        }
        $dat = $votmcdt->getByFicha($fidfic ?: $fidval);
    } else {
        $mensaje = implode("<br>", $errores);
        $tipoMensaje = "error";
    }
}

if ($opera === "Actualizar" && $idusu) {
    $errores = [];
    
    if (empty($noca)) $errores[] = "El número de candidato es obligatorio";
    elseif (!preg_match('/^\d{1,3}$/', $noca)) $errores[] = "El número de candidato debe ser numérico (máx 3 dígitos)";
    
    if ($votmcdt->checkNocaExists($noca, $idusu)) {
        $errores[] = "Ya existe otro candidato con este número";
    }

    if (empty($errores)) {
        $votmcdt->setIdusu($idusu);
        
        if ($arch) {
            require_once 'optimg.php';
            $fotcan = opti($_FILES['arch'], $idusu, "fcan", "can");
            $votmcdt->setFotcan($fotcan);
        }
        
        if ($votmcdt->actualizarCandidato($noca, $actusu, $fotcan)) {
            $mensaje = "Candidato actualizado correctamente";
            $tipoMensaje = "success";
            $idusu = NULL;
            $datOne = NULL;
        } else {
            $mensaje = "Error al actualizar el candidato";
            $tipoMensaje = "error";
        }
        $dat = $votmcdt->getByFicha($fidfic ?: $fidval);
    } else {
        $mensaje = implode("<br>", $errores);
        $tipoMensaje = "error";
    }
}

if ($opera === "Eliminar" && $idusu) {
    $votmcdt->setIdusu($idusu);
    if ($votmcdt->delete()) {
        $mensaje = "Candidato eliminado correctamente";
        $tipoMensaje = "success";
        $idusu = NULL;
        $datOne = NULL;
        $dat = $votmcdt->getByFicha($fidfic ?: $fidval);
    } else {
        $mensaje = "Error al eliminar el candidato";
        $tipoMensaje = "error";
    }
}

if ($idusu) {
    $votmcdt->setIdusu($idusu);
    $datOne = $votmcdt->getById();
}