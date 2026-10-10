<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!function_exists('redireccionarPagina')) {
    function redireccionarPagina($url)
    {
        if (!headers_sent()) {
            header("Location: " . $url);
        } else {
            echo "<script>window.location.href='" . $url . "';</script>";
        }
        exit;
    }
}
require_once __DIR__ . '/../models/usumcdiv.php';
require_once __DIR__ . '/../models/conexion.php';

$usumcdiv = new Usumcdiv();
$pg = isset($_REQUEST['pg']) ? $_REQUEST['pg'] : 'usuvcdiv';
$opera = isset($_REQUEST['opera']) ? $_REQUEST['opera'] : '';
$idCind = isset($_REQUEST['idCind']) ? $_REQUEST['idCind'] : null;
$idTipDoc = isset($_REQUEST['idTipDoc']) ? $_REQUEST['idTipDoc'] : null;
$aprObs = isset($_REQUEST['aprObs']) ? trim($_REQUEST['aprObs']) : null;
$idPerfilUsuario = isset($_SESSION['idper']) ? (int)$_SESSION['idper'] : 0;
$esAprobador = in_array($idPerfilUsuario, [1, 2, 28, 31, 34, 36], true);
$idUsuarioSesion = isset($_SESSION['idusu']) ? (int)$_SESSION['idusu'] : 0;
$idcenSesion = isset($_SESSION['idcen']) ? (int)$_SESSION['idcen'] : null;
$uploadDir = 'uploads/documentos/identidad/';
$maxFileSize = 5 * 1024 * 1024;
$errorMsg = null;
$mensaje = null;
$tipoMensaje = 'alert-info';

if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'creado') {
        $mensaje = 'Documento enviado para revisión.';
        $tipoMensaje = 'alert-success';
    } elseif ($_GET['msg'] === 'aprobado') {
        $mensaje = 'Documento aprobado.';
        $tipoMensaje = 'alert-success';
    } elseif ($_GET['msg'] === 'rechazado') {
        $mensaje = 'Documento rechazado; puede enviar una corrección.';
        $tipoMensaje = 'alert-warning';
    } else {
        $mensaje = 'Operación realizada.';
        $tipoMensaje = 'alert-success';
    }
}

if ($idCind !== null && $idCind !== '') {
    $usumcdiv->setIdCind($idCind);
}

/* Aprobar / Rechazar documento */
if (($opera === 'aprobar' || $opera === 'rechazar') && $idCind && $esAprobador) {
    $doc = $usumcdiv->getOne();
    $doc = $doc[0] ?? null;
    if (!$doc) {
        $errorMsg = 'No se encontró el documento.';
    } elseif ($idcenSesion !== null) {
        $cnx = new conexion();
        $db = $cnx->get_conexion();
        $st = $db->prepare('SELECT idcen FROM usuario WHERE idusu=:id');
        $st->bindParam(':id', $doc['idUsu']);
        $st->execute();
        $row = $st->fetch(PDO::FETCH_ASSOC);
        if ($row && (int)$row['idcen'] !== $idcenSesion) {
            $errorMsg = 'No tiene permisos para revisar documentos de otro centro.';
        }
    }
    if (!$errorMsg) {
        $estado = $opera === 'aprobar' ? 1 : 2;
        $usumcdiv->cambiarEstado($estado, $aprObs, $idUsuarioSesion);
        redireccionarPagina('home.php?pg=' . urlencode($pg) . '&msg=' . $opera);
    }
}

/* Guardar nuevo documento o editar uno existente */
if ($opera === 'save') {
    $editando = ($idCind !== null && $idCind !== '');
    $docActual = null;

    if (!$idTipDoc) {
        $errorMsg = 'Debe seleccionar el tipo de documento.';
    }

    if (!$errorMsg && $editando) {
        $usumcdiv->setIdCind($idCind);
        $docActual = $usumcdiv->getOne();
        if (empty($docActual)) {
            $errorMsg = 'No se encontró el documento.';
            $editando = false;
        } else {
            $docActual = $docActual[0];
        }
    }

    if (!$errorMsg && !$editando) {
        $existentes = $usumcdiv->getOneByUsuarioTipo($idUsuarioSesion, $idTipDoc);
        foreach ($existentes as $e) {
            if ((int)$e['aprEst'] === 0 || (int)$e['aprEst'] === 1) {
                $errorMsg = 'Ya tiene un documento pendiente o aprobado de ese tipo.';
                break;
            }
        }
    }

    $nuevoArchivo = false;
    $rutaFinal = null;
    $hayArchivo = isset($_FILES['archivo']) && $_FILES['archivo']['error'] !== UPLOAD_ERR_NO_FILE;

    if (!$errorMsg && $hayArchivo) {
        $ext = strtolower(pathinfo($_FILES['archivo']['name'], PATHINFO_EXTENSION));
        if ($ext !== 'pdf') {
            $errorMsg = 'Solo se permiten archivos PDF.';
        } elseif ($_FILES['archivo']['size'] > $maxFileSize) {
            $errorMsg = 'El archivo supera el tamaño máximo de 5 MB.';
        } else {
            if (!is_dir($uploadDir)) {
                @mkdir($uploadDir, 0777, true);
            }
            $base = preg_replace('/[^a-zA-Z0-9._-]/', '_', pathinfo($_FILES['archivo']['name'], PATHINFO_FILENAME));
            $rutaFinal = $uploadDir . $base . '_' . uniqid() . '.pdf';
            $nuevoArchivo = move_uploaded_file($_FILES['archivo']['tmp_name'], $rutaFinal);
            if (!$nuevoArchivo) {
                $errorMsg = 'No fue posible guardar el archivo.';
            }
        }
    } elseif (!$errorMsg && !$editando) {
        $errorMsg = 'Debe seleccionar un archivo PDF.';
    }

    if (!$errorMsg) {
        $usumcdiv->setIdUsu(($editando && $docActual) ? (int)$docActual['idUsu'] : $idUsuarioSesion);
        $usumcdiv->setIdTipDoc($idTipDoc);
        $usumcdiv->setRutPdf($nuevoArchivo ? $rutaFinal : (($editando && $docActual) ? $docActual['rutPdf'] : null));
        $ok = $editando ? $usumcdiv->edit() : $usumcdiv->save();
        if ($ok) {
            redireccionarPagina('home.php?pg=' . urlencode($pg) . '&msg=' . ($editando ? 'ok' : 'creado'));
        }
        if ($nuevoArchivo) {
            @unlink($rutaFinal);
        }
        $errorMsg = 'No fue posible guardar el documento.';
    }
}

/* Cargar documento en modo edición */
if ($opera === 'edi' && $idCind) {
    $datOne = $usumcdiv->getOne();
}

/* Datos para la vista */
$docTipos = $usumcdiv->getDocTipos();
$datAll = $esAprobador
    ? $usumcdiv->getAll(null, $idcenSesion)
    : $usumcdiv->getAll($idUsuarioSesion);
if (!is_array($datAll)) {
    $datAll = [];
}
$metricas = $esAprobador
    ? $usumcdiv->getDashboardMetrics(null, $idcenSesion)
    : $usumcdiv->getDashboardMetrics($idUsuarioSesion);

/* Separación por estado para las pestañas (estilo vvidgen) */
$datAprobados = [];
$datPendientes = [];
$datRechazados = [];
foreach ($datAll as $d) {
    $est = (int)$d['aprEst'];
    if ($est === 1) {
        $datAprobados[] = $d;
    } elseif ($est === 0) {
        $datPendientes[] = $d;
    } elseif ($est === 2) {
        $datRechazados[] = $d;
    }
}
$datTodos = $datAll;

/* Usuario no aprobador: solo ve el documento más reciente por tipo */
if (!$esAprobador) {
    $latest = [];
    foreach ($datAll as $d) {
        $k = (int)$d['idTipDoc'];
        if (!isset($latest[$k])) {
            $latest[$k] = $d;
        }
    }
    $datAll = array_values($latest);
    $filtra = function ($d) use ($latest) {
        $k = (int)$d['idTipDoc'];
        return !isset($latest[$k]) || $latest[$k] === $d;
    };
    $datAprobados = array_values(array_filter($datAprobados, $filtra));
    $datPendientes = array_values(array_filter($datPendientes, $filtra));
    $datRechazados = array_values(array_filter($datRechazados, $filtra));
    $datTodos = array_values(array_filter($datTodos, $filtra));
}
