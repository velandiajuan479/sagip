<?php
require_once __DIR__ . '/../models/conexion.php';
require_once __DIR__ . '/../models/admmaps.php';

$admmaps = new ADMMaps();

$action = isset($_GET['action']) ? $_GET['action'] : null;

// Listado paginado de contratistas pendientes de la dependencia
if ($action === 'get_contratistas') {
    header('Content-Type: application/json; charset=utf-8');
    try {
        $id_dependencia = $_GET['id_dependencia'] ?? null;
        if (!$id_dependencia) {
            echo json_encode(['success' => false, 'error' => 'Dependencia no proporcionada']);
            exit;
        }

        $busqueda = trim($_GET['busqueda'] ?? '');
        $limite = in_array((int)($_GET['limite'] ?? 50), [50, 100, 200], true) ? (int)$_GET['limite'] : 50;
        $pagina = max(1, (int)($_GET['pagina'] ?? 1));
        $inicio = ($pagina - 1) * $limite;

        $total = $admmaps->contarContratistas($id_dependencia, $busqueda);
        $data = $admmaps->obtenerContratistas($id_dependencia, $busqueda, $inicio, $limite);
        $totalPaginas = $total > 0 ? (int)ceil($total / $limite) : 1;

        echo json_encode([
            'success'      => true,
            'count'        => count($data),
            'total'        => $total,
            'pagina'       => $pagina,
            'totalPaginas' => $totalPaginas,
            'limit'        => $limite,
            'data'         => $data
        ]);
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error al obtener contratistas: ' . $e->getMessage()]);
    }
    exit;
}

// Todos los ids pendientes de la dependencia (para el estado pre-marcado y acciones masivas)
if ($action === 'get_ids') {
    header('Content-Type: application/json; charset=utf-8');
    try {
        $id_dependencia = $_GET['id_dependencia'] ?? null;
        if (!$id_dependencia) {
            echo json_encode(['success' => false, 'error' => 'Dependencia no proporcionada']);
            exit;
        }

        $ids = $admmaps->obtenerIdsContratistas($id_dependencia);

        echo json_encode([
            'success' => true,
            'count'   => count($ids),
            'ids'     => $ids
        ]);
    } catch (Throwable $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// Guardado masivo: aprueba los marcados y rechaza con motivo los que traen observacion
if ($action === 'guardar_masivo' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        $id_dependencia = $input['id_dependencia'] ?? null;
        if (!$id_dependencia) {
            echo json_encode(['success' => false, 'error' => 'Dependencia no proporcionada']);
            exit;
        }

        $aprobados = $input['aprobados'] ?? [];
        $rechazados = $input['rechazados'] ?? [];

        $res = $admmaps->guardarAprobacionMasiva($id_dependencia, $aprobados, $rechazados);
        echo json_encode($res);
    } catch (Throwable $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// Carga normal de la pagina
$dependencias = $admmaps->obtenerDependencias();
?>