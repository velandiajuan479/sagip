<?php
require_once __DIR__ . '/../models/conexion.php';
require_once __DIR__ . '/../models/hormcali.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function horccali_json($payload, $status = 200)
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

function horccali_continuarJson($request)
{
    return isset($request['action']);
}

$hormcali = new Hormcali();
$contexto = $hormcali->getContexto();
$pag = isset($_REQUEST['pg']) ? (int)$_REQUEST['pg'] : 1547;
$idficSeleccionada = isset($_REQUEST['idfic']) ? trim((string)$_REQUEST['idfic']) : '';
$idactSeleccionada = isset($_REQUEST['idact']) ? trim((string)$_REQUEST['idact']) : '';
$idinstructorSesion = $contexto['idusu'];
$idperSesion = $contexto['idper_sesion'];
$nomusuSesion = $contexto['nomusu'] ?? 'Usuario';
$esDocenteOAdmin = (bool)$contexto['can_write_grades'];
$esAprendiz = (bool)$contexto['is_learner'];
$mensaje = null;
$tipoMensaje = 'info';

// Alcance por rol (módulo HyA) para la cinta de alcance de la vista.
$scopeAcceso = $hormcali->getAcceso();
$scopeFichas = $scopeAcceso->getFichasPermitidas($contexto);
$scopeRol = $scopeAcceso->etiquetaRol($contexto);
$scopeN = count($scopeFichas);
$scopeTotal = !empty($contexto['is_admin']);

if (!$contexto['authenticated']) {
    if (horccali_continuarJson($_REQUEST)) {
        horccali_json(['status' => 'error', 'message' => 'Sesión no válida.'], 401);
    }
    http_response_code(401);
    echo 'Sesión no válida.';
    exit;
}

$accion = $_POST['action'] ?? $_GET['action'] ?? null;
if ($accion !== null) {
    if ($accion === 'cargar_actividades') {
        $idfic = trim((string)($_POST['idfic'] ?? $_GET['idfic'] ?? ''));
        if ($idfic === '' || !$hormcali->puedeVerFicha($idfic)) {
            horccali_json(['status' => 'error', 'message' => 'No tiene acceso a la ficha solicitada.'], 403);
        }
        horccali_json([
            'status' => 'success',
            'actividades' => $hormcali->getActividadesByFicha($idfic),
        ]);
    }

    if ($accion === 'guardar_evaluacion_ajax') {
        $idfic = trim((string)($_POST['idfic'] ?? ''));
        $idact = (int)($_POST['idact'] ?? 0);
        $evaluaciones = $_POST['evaluaciones'] ?? [];
        if (!$hormcali->puedeCalificar($idfic) || !$hormcali->puedeVerActividad($idfic, $idact)) {
            horccali_json(['status' => 'error', 'message' => 'No tiene permisos para calificar esta actividad.'], 403);
        }
        if ($idfic === '' || $idfic === '0' || $idact <= 0 || empty($evaluaciones)) {
            horccali_json(['status' => 'error', 'message' => 'Parámetros incompletos para guardar la evaluación.'], 422);
        }
        horccali_json($hormcali->guardarEvaluacionCompleta($idact, $idfic, $evaluaciones, $idinstructorSesion));
    }

    if ($accion === 'agregar_criterio_ajax') {
        $idfic = trim((string)($_POST['idfic'] ?? ''));
        $idact = (int)($_POST['idact'] ?? 0);
        $nomcri = trim((string)($_POST['nomcri'] ?? ''));
        if (!$hormcali->puedeCalificar($idfic) || !$hormcali->puedeVerActividad($idfic, $idact)) {
            horccali_json(['status' => 'error', 'message' => 'No tiene permisos para agregar criterios.'], 403);
        }
        if ($nomcri === '') {
            horccali_json(['status' => 'error', 'message' => 'Escriba la descripción del criterio.'], 422);
        }
        $ok = $hormcali->agregarCriterio($idact, $nomcri, $idfic);
        horccali_json([
            'status' => $ok ? 'success' : 'error',
            'message' => $ok ? 'Criterio agregado correctamente.' : 'No se pudo agregar el criterio.',
        ], $ok ? 200 : 403);
    }

    if ($accion === 'eliminar_criterio_ajax') {
        $idcri = (int)($_POST['idcri'] ?? 0);
        $ok = $idcri > 0 && $hormcali->eliminarCriterio($idcri);
        horccali_json([
            'status' => $ok ? 'success' : 'error',
            'message' => $ok ? 'Criterio eliminado correctamente.' : 'No se pudo eliminar el criterio.',
        ], $ok ? 200 : 403);
    }

    horccali_json(['status' => 'error', 'message' => 'Acción no reconocida.'], 400);
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    if (isset($_POST['btnGuardarEvaluacion'])) {
        $idficPost = trim((string)($_POST['idfic'] ?? ''));
        $idactPost = (int)($_POST['idact'] ?? 0);
        $evaluacionesPost = $_POST['evaluaciones'] ?? [];
        if (!$hormcali->puedeCalificar($idficPost) || !$hormcali->puedeVerActividad($idficPost, $idactPost)) {
            $tipoMensaje = 'danger';
            $mensaje = 'No tiene permisos para registrar calificaciones.';
        } elseif (empty($evaluacionesPost)) {
            $tipoMensaje = 'warning';
            $mensaje = 'No se enviaron datos completos de evaluación.';
        } else {
            $resultado = $hormcali->guardarEvaluacionCompleta($idactPost, $idficPost, $evaluacionesPost, $idinstructorSesion);
            $tipoMensaje = ($resultado['status'] ?? 'error') === 'success' ? 'success' : 'danger';
            $mensaje = $resultado['message'] ?? 'No se pudo guardar la evaluación.';
        }
    }

    if (isset($_POST['btnAgregarCriterio'])) {
        $idficPost = trim((string)($_POST['idfic'] ?? ''));
        $idactPost = (int)($_POST['idact'] ?? 0);
        $nomcriPost = trim((string)($_POST['nuevo_criterio_nom'] ?? ''));
        if (!$hormcali->puedeCalificar($idficPost) || !$hormcali->puedeVerActividad($idficPost, $idactPost)) {
            $tipoMensaje = 'danger';
            $mensaje = 'No tiene permisos para agregar criterios.';
        } elseif ($nomcriPost === '' || !$hormcali->agregarCriterio($idactPost, $nomcriPost, $idficPost)) {
            $tipoMensaje = 'danger';
            $mensaje = 'No se pudo agregar el criterio.';
        } else {
            $tipoMensaje = 'success';
            $mensaje = 'Criterio agregado correctamente.';
        }
    }

    if (isset($_POST['btnEliminarCriterio'])) {
        $idcriPost = (int)($_POST['idcri_eliminar'] ?? 0);
        if ($idcriPost <= 0 || !$hormcali->eliminarCriterio($idcriPost)) {
            $tipoMensaje = 'danger';
            $mensaje = 'No se pudo eliminar el criterio.';
        } else {
            $tipoMensaje = 'success';
            $mensaje = 'Criterio eliminado correctamente.';
        }
    }
}

$listaFichas = $hormcali->getFichasPermitidas();
if ($idficSeleccionada === '' && !empty($listaFichas)) {
    $idficSeleccionada = (string)$listaFichas[0]['idfic'];
}
if ($idficSeleccionada !== '' && !$hormcali->puedeVerFicha($idficSeleccionada)) {
    $idficSeleccionada = '';
    $idactSeleccionada = '';
}

$listaActividades = $idficSeleccionada !== '' ? $hormcali->getActividadesByFicha($idficSeleccionada) : [];
if ($idactSeleccionada === '' && count($listaActividades) === 1) {
    $idactSeleccionada = (string)$listaActividades[0]['idact'];
}

$actividadActual = null;
$criterios = [];
$aprendices = [];
$evaluacionesGuardadas = [];
$notasGuardadas = [];
$evaluacionMiAprendiz = null;
$misActividadesEvaluadas = [];
$metricasDashboard = [
    'total_aprendices' => 0,
    'evaluados' => 0,
    'pendientes' => 0,
    'aprobados' => 0,
    'parciales' => 0,
    'no_aprobados' => 0,
    'promedio_general' => 0,
    'porcentaje_aprobacion' => 0,
];
$totalPaginas = 1;
$paginaActual = 1;
$porPagina = 10;
$totalAprendices = 0;
$offsetAprendices = 0;
$aprendicesPaginados = [];

if ($idficSeleccionada !== '' && $idactSeleccionada !== '' && $hormcali->puedeVerActividad($idficSeleccionada, $idactSeleccionada)) {
    $actividadActual = $hormcali->getActividadInfo($idactSeleccionada, $idficSeleccionada);
    $criterios = $hormcali->getCriteriosByActividad($idactSeleccionada, $idficSeleccionada);

    if ($esAprendiz) {
        $evaluacionMiAprendiz = $hormcali->getEvaluacionAprendiz($idactSeleccionada, $idficSeleccionada, $idinstructorSesion);
    } elseif ($esDocenteOAdmin) {
        $aprendices = $hormcali->getAprendicesByFicha($idficSeleccionada);
        $evaluacionesGuardadas = $hormcali->getEvaluacionesActividad($idactSeleccionada, $idficSeleccionada);
        $notasGuardadas = $hormcali->getNotasActividad($idactSeleccionada, $idficSeleccionada);
        $metricasDashboard = $hormcali->getMetricasDashboard($idactSeleccionada, $idficSeleccionada, count($aprendices));

        $porPagina = 10;
        $totalAprendices = count($aprendices);
        $totalPaginas = max(1, (int)ceil($totalAprendices / $porPagina));
        $paginaActual = max(1, (int)($_GET['p'] ?? 1));
        $paginaActual = min($paginaActual, $totalPaginas);
        $offsetAprendices = ($paginaActual - 1) * $porPagina;
        $aprendicesPaginados = array_slice($aprendices, $offsetAprendices, $porPagina);
    }
}

if ($esAprendiz && $contexto['authenticated']) {
    $misActividadesEvaluadas = $hormcali->getActividadesEvaluadasAprendiz($idinstructorSesion, $idficSeleccionada ?: null);
}
