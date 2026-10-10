<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ---------------------------------------------------------------
// MANEJO DE SESIÓN - AUTENTICACIÓN
// ---------------------------------------------------------------
if (!isset($_SESSION['idusu'])) {
    if (isset($_REQUEST['action'])) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['status' => false, 'error' => 'Sesión expirada o inválida. Inicie sesión nuevamente.']);
    } else {
        // Redirigir si no hay sesión
        header("Location: index.php");
    }
    exit;
}

// Opción para cerrar sesión desde este controlador (si es necesario)
if (isset($_REQUEST['action']) && $_REQUEST['action'] === 'logout') {
    session_unset();
    session_destroy();
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['status' => true, 'msg' => 'Sesión cerrada']);
    exit;
}

require_once(__DIR__ . '/../models/hormsessgu.php');
$msseg = new Msseg();

// ---------------------------------------------------------------
// IDENTIFICACIÓN DEL USUARIO Y DETERMINACIÓN DE ROL REAL
// ---------------------------------------------------------------
$idusuSesion  = $_SESSION['idusu'];
$idperSesion  = $_SESSION['idper']   ?? ($_SESSION['idpergen'] ?? null);
$nomusuSesion = $_SESSION['nomusu']  ?? 'Usuario';

// Determinar rol real desde la sesión
$esAdministrador = $msseg->esAdministrador($idusuSesion, $idperSesion);
$esCoordinador   = !$esAdministrador && $msseg->esCoordinador($idusuSesion, $idperSesion);
$esInstructor    = !$esAdministrador && !$esCoordinador && $msseg->esInstructor($idusuSesion, $idperSesion);
$esAprendiz      = !$esAdministrador && !$esCoordinador && !$esInstructor;

// Rol de texto para uso interno
if ($esAdministrador) {
    $rolUsuario = 'administrador';
} elseif ($esCoordinador) {
    $rolUsuario = 'coordinador';
} elseif ($esInstructor) {
    $rolUsuario = 'instructor';
} else {
    $rolUsuario = 'aprendiz';
}

// Pueden crear/editar sesiones: Administrador, Coordinador o Instructor
$puedeCrearSesion = ($esAdministrador || $esCoordinador || $esInstructor);

// ---------------------------------------------------------------
// MANEJO DE PETICIONES AJAX
// ---------------------------------------------------------------
$action = $_REQUEST['action'] ?? null;

if ($action) {
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    header('Content-Type: application/json; charset=utf-8');

    // 1. Cargar competencias por ficha
    if ($action === 'get_competencias') {
        $idfic = $_REQUEST['idfic'] ?? null;
        if ($idfic) {
            $competencias = $msseg->getCompetenciasByFicha($idfic);
        } else {
            $competencias = $msseg->getAllCompetenciasConResultados();
        }
        echo json_encode([
            'status'       => 'success',
            'competencias' => $competencias
        ]);
        exit;
    }

    // 2. Cargar resultados de aprendizaje por competencia
    if ($action === 'get_resultados') {
        $idcom    = $_REQUEST['idcom'] ?? null;
        $resultados = $msseg->getResultadosByCompetencia($idcom);
        echo json_encode([
            'status'     => 'success',
            'resultados' => $resultados
        ]);
        exit;
    }

    // 3. Obtener estadísticas de la competencia/resultado seleccionado
    if ($action === 'get_resumen') {
        $idfic = $_REQUEST['idfic'] ?? null;
        $idcom = $_REQUEST['idcom'] ?? null;
        $idres = $_REQUEST['idres'] ?? null;
        
        $resumen      = $idres ? $msseg->getEstadisticasResultado($idfic, $idres) : $msseg->getEstadisticasByCompetencia($idfic, $idcom);
        $siguienteNum = $msseg->getNextNumSesion($idfic, $idcom);
        $total        = $msseg->getTotalSesionesByComp($idfic, $idcom);
        echo json_encode([
            'status'        => 'success',
            'resumen'       => $resumen,
            'siguiente_num' => $siguienteNum,
            'total'         => $total
        ]);
        exit;
    }

    // 4. Obtener listado paginado de sesiones
    if ($action === 'get_sesiones') {
        $idfic = $_REQUEST['idfic'] ?? null;
        $idcom = $_REQUEST['idcom'] ?? null;
        $idres = $_REQUEST['idres'] ?? null;
        $page  = max(1, (int)($_REQUEST['page'] ?? 1));
        $limit = 20; // 20 sesiones por página como pidió el usuario

        // El aprendiz solo puede ver sesiones de su propia ficha
        if ($esAprendiz) {
            $fichasAprendiz = $msseg->getFichaByAprendiz($idusuSesion);
            $fichasPermitidas = array_column($fichasAprendiz, 'idfic');
            if (!in_array($idfic, $fichasPermitidas)) {
                echo json_encode(['status' => false, 'error' => 'No tiene acceso a esta ficha.']);
                exit;
            }
        }

        // Si enviaron resultado usarlo (comportamiento agenda), sino competencia
        if ($idres) {
            $sesiones     = $msseg->getSesionesByResultado($idfic, $idres, $esAprendiz ? $idusuSesion : null);
            $total        = count($sesiones);
            $ultimaIdSes  = null;
            if (!empty($sesiones)) {
                $ultimaIdSes = end($sesiones)['idses'];
            }
        } else {
            $sesiones     = $msseg->getSesionesByCompetencia($idfic, $idcom, $page, $limit, $esAprendiz ? $idusuSesion : null);
            $total        = $msseg->getTotalSesionesByComp($idfic, $idcom);
            $ultimaIdSes  = $msseg->getIdUltimaSesion($idfic, $idcom);
        }
        
        $totalPaginas = $total > 0 ? (int)ceil($total / $limit) : 1;

        echo json_encode([
            'status'        => 'success',
            'sesiones'      => $sesiones,
            'total'         => $total,
            'page'          => $page,
            'total_paginas' => $totalPaginas,
            'ultima_idses'  => $ultimaIdSes,
            'rol'           => $rolUsuario
        ]);
        exit;
    }

    // 5. Guardar / Editar Sesión
    if ($action === 'guardar_sesion') {
        if (!$puedeCrearSesion) {
            echo json_encode(['status' => false, 'error' => 'No tiene permisos para crear o editar sesiones.']);
            exit;
        }

        $idses          = !empty($_REQUEST['idses'])  ? (int)$_REQUEST['idses']  : null;
        $idfic          = trim($_REQUEST['idfic']     ?? '');
        $idcom          = (int)($_REQUEST['idcom']    ?? 0);
        $idres          = (int)($_REQUEST['idres']    ?? 0);
        $numses         = (int)($_REQUEST['numses']   ?? 1);
        $fecses         = trim($_REQUEST['fecses']    ?? date('Y-m-d'));
        $horini         = trim($_REQUEST['horini']    ?? '');
        $horfin         = trim($_REQUEST['horfin']    ?? '');
        $mom_bienvenida = trim($_REQUEST['mom_bienvenida'] ?? '');
        $mom_teoria     = trim($_REQUEST['mom_teoria']     ?? '');
        $mom_practica   = trim($_REQUEST['mom_practica']   ?? '');
        $mom_cierre     = trim($_REQUEST['mom_cierre']     ?? '');

        if (!$idfic || !$fecses) {
            echo json_encode([
                'status' => false,
                'error'  => 'Por favor seleccione Ficha y Fecha de la sesión.'
            ]);
            exit;
        }

        // Si no se proporcionó idres, calcular siguiente número con respecto a ficha+competencia
        if (!$numses && $idcom) {
            $numses = $msseg->getNextNumSesion($idfic, $idcom);
        }

        $datos = [
            'idses'          => $idses,
            'idfic'          => $idfic,
            'idcom'          => $idcom,
            'idres'          => $idres,
            'idusu'          => $idusuSesion,
            'numses'         => $numses,
            'fecses'         => $fecses,
            'horini'         => $horini,
            'horfin'         => $horfin,
            'mom_bienvenida' => $mom_bienvenida,
            'mom_teoria'     => $mom_teoria,
            'mom_practica'   => $mom_practica,
            'mom_cierre'     => $mom_cierre
        ];

        $resp = $msseg->guardarSesion($datos);
        echo json_encode($resp);
        exit;
    }

    // 6. Eliminar Sesión
    if ($action === 'eliminar_sesion') {
        if (!$puedeCrearSesion) {
            echo json_encode(['status' => false, 'error' => 'No tiene permisos para eliminar sesiones.']);
            exit;
        }
        $idses = (int)($_REQUEST['idses'] ?? 0);
        if (!$idses) {
            echo json_encode(['status' => false, 'error' => 'ID de sesión no válido']);
            exit;
        }
        $resp = $msseg->eliminarSesion($idses);
        echo json_encode(['status' => $resp]);
        exit;
    }

    // 7. Guardar Evaluación del Aprendiz — solo rol Aprendiz, solo última sesión
    if ($action === 'guardar_evaluacion') {
        if (!$esAprendiz) {
            echo json_encode(['status' => false, 'error' => 'Solo los aprendices pueden enviar evaluaciones.']);
            exit;
        }

        $idses  = (int)($_REQUEST['idses']  ?? 0);
        $valeva = (int)($_REQUEST['valeva'] ?? 0);
        $comeva = trim($_REQUEST['comeva']  ?? '');

        if (!$idses || !in_array($valeva, [1, 2, 3], true)) {
            echo json_encode([
                'status' => false,
                'error'  => 'Seleccione una opción de evaluación válida.'
            ]);
            exit;
        }

        $sesionData = $msseg->getSesionById($idses);
        if (!$sesionData) {
            echo json_encode(['status' => false, 'error' => 'Sesión no encontrada.']);
            exit;
        }

        if (($valeva === 2 || $valeva === 3) && mb_strlen($comeva) < 10) {
            echo json_encode([
                'status' => false,
                'error'  => 'La justificación es obligatoria y debe contener al menos 10 caracteres.'
            ]);
            exit;
        }

        $resp = $msseg->guardarEvaluacion($idses, $idusuSesion, $valeva, $comeva);
        echo json_encode($resp);
        exit;
    }

    echo json_encode(['status' => false, 'error' => 'Acción desconocida']);
    exit;
}

// ---------------------------------------------------------------
// CARGA INICIAL DE LA VISTA (SIN AJAX)
// ---------------------------------------------------------------
// Ahora se espera que venga desde la agenda o parámetros
$idfic = isset($_REQUEST['idfic']) ? trim($_REQUEST['idfic']) : null;
$idcom = isset($_REQUEST['idcom']) ? trim($_REQUEST['idcom']) : null;
$idres = isset($_REQUEST['idres']) ? trim($_REQUEST['idres']) : null;
$page  = max(1, (int)($_REQUEST['page'] ?? 1));
$limit = 20;

// Validar acceso del aprendiz a la ficha
if ($esAprendiz && $idfic) {
    $dfi = $msseg->getFichaByAprendiz($idusuSesion);
    $fichasPermitidas = array_column($dfi, 'idfic');
    if (!in_array($idfic, $fichasPermitidas)) {
        // Redirigir a una página de error o vaciar parámetros
        $idfic = null;
        $idcom = null;
        $idres = null;
    }
}

// Si es instructor, asegurar que la ficha sea de él
if ($esInstructor && $idfic) {
    $dfi = $msseg->getFichasByInstructor($idusuSesion);
    $fichasPermitidas = array_column($dfi, 'idfic');
    if (!in_array($idfic, $fichasPermitidas)) {
        $idfic = null;
    }
}

// Alcance por rol (módulo HyA) para la cinta de alcance de la vista.
require_once __DIR__ . '/../models/hormacceso.php';
$hacceso = new HormAcceso();
$scopeCtx = $hacceso->getContexto();
$scopeFichas = $hacceso->getFichasPermitidas($scopeCtx);
$scopeRol = $hacceso->etiquetaRol($scopeCtx);
$scopeN = count($scopeFichas);
$scopeTotal = !empty($scopeCtx['is_admin']);

// Entrada directa sin parámetros: primera ficha permitida y su primera
// competencia, para mostrar información sin pasar por Agenda.
if (!$idfic && !empty($scopeFichas)) {
    $idfic = $scopeFichas[0]['idfic'];
}
if ($idfic && !$idcom && !$idres) {
    $compDef = $msseg->getCompetenciasByFicha($idfic);
    if (!empty($compDef) && isset($compDef[0]['idcom'])) {
        $idcom = $compDef[0]['idcom'];
    }
}

// Cargar sesiones paginadas si hay ficha y (competencia o resultado)
$resumen      = null;
$sesiones     = [];
$totalSes     = 0;
$totalPaginas = 1;
$ultimaIdSes  = null;
$siguienteNumSes = 1;

if ($idfic) {
    if ($idres) {
        $resumen         = $msseg->getEstadisticasResultado($idfic, $idres);
        $sesiones        = $msseg->getSesionesByResultado($idfic, $idres, $esAprendiz ? $idusuSesion : null);
        $totalSes        = count($sesiones);
        $totalPaginas    = 1;
        if (!empty($sesiones)) {
            $ultimaIdSes = end($sesiones)['idses'];
        }
        $siguienteNumSes = $totalSes + 1;
    } elseif ($idcom) {
        $resumen         = $msseg->getEstadisticasByCompetencia($idfic, $idcom);
        $sesiones        = $msseg->getSesionesByCompetencia($idfic, $idcom, $page, $limit, $esAprendiz ? $idusuSesion : null);
        $totalSes        = $msseg->getTotalSesionesByComp($idfic, $idcom);
        $totalPaginas    = $totalSes > 0 ? (int)ceil($totalSes / $limit) : 1;
        $ultimaIdSes     = $msseg->getIdUltimaSesion($idfic, $idcom);
        $siguienteNumSes = $msseg->getNextNumSesion($idfic, $idcom);
    }
}
?>
