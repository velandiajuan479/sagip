<?php
require_once __DIR__ . '/../models/conexion.php';
require_once __DIR__ . '/../models/admmpys.php'; 

if (!function_exists('ManejoError')) {
    function ManejoError($e) {
        error_log("Error en " . __FILE__ . ": " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        throw $e;
    }
}

$mpys = new Mpys();



try {
    $dependencias = $mpys->obtenerDependencias();
} catch (Exception $e) {
    error_log("Error cargando dependencias: " . $e->getMessage());
    $dependencias = [];
}

if (isset($_GET['action']) && $_GET['action'] === 'busqueda_manual') {
    header('Content-Type: application/json');
    
    try {
        $id_dependencia = $_GET['id_dependencia'] ?? null;
        $documento = $_GET['documento'] ?? '';
        
        if (!$id_dependencia) {
            throw new Exception('Dependencia no proporcionada');
        }
        
        $empleado = $mpys->obtenerDatosCompletosEmpleado($documento);
        
        if (!$empleado) {
            echo json_encode([
                'success' => false,
                'error' => 'Empleado no encontrado',
                'count' => 0,
                'data' => []
            ]);
            exit;
        }
        
        $lista_completa = $mpys->obtenerListaPazYSalvos($id_dependencia);
        
        $resultados_filtrados = array_filter($lista_completa, function($emp) use ($documento) {
            return $emp['ndocusu'] == $documento;
        });
        

        $data = !empty($resultados_filtrados) ? array_values($resultados_filtrados) : [
            [
                'ndocusu' => $empleado['ndocusu'],
                'nombre' => $empleado['nombre'],
                'observacion' => $empleado['ultima_observacion']['observacion'] ?? null,
                'calificacion' => $empleado['ultima_observacion']['calificacion'] ?? null,
                'fechayhora' => $empleado['ultima_observacion']['fechayhora'] ?? null,
                'total_paz_salvos' => count($empleado['historial'] ?? [])
            ]
        ];
        
        echo json_encode([
            'success' => true,
            'count' => count($data),
            'data' => $data
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage(),
            'count' => 0,
            'data' => []
        ]);
    }
    exit;
}

// seleccion de empleado por documento
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['documento'], $_POST['dependencia'])) {
    $doc = trim($_POST['documento']);

    $empleado = $mpys->obtenerDatosCompletosEmpleado($doc);
    if (!$empleado) {
        $_SESSION['error'] = "No se encontró ningún empleado con ese documento";
        header("Location: home.php?pg=2401");
        exit;
    }

    // lista de paz y salvos para la dependencia
    $dependencia_actual = $_POST['dependencia'];
    $empleados_dependencia = $mpys->obtenerListaPazYSalvos($dependencia_actual);

    $_SESSION['empleado_seleccionado'] = $empleado;
    $_SESSION['mostrar_busqueda'] = false;
    $_SESSION['id_dependencia'] = $dependencia_actual;
    $_SESSION['empleados_dependencia'] = $empleados_dependencia;

    if ($empleado['idper'] == 34) {
        $_SESSION['mensaje_planta'] = true;
    }

    header("Location: home.php?pg=2401");
    exit;
}

// obtener empleados por dependencia
if (isset($_GET['action']) && $_GET['action'] === 'empleados_dependencia') {
    $iddependencia = $_GET['iddependencia'] ?? null;
    if ($iddependencia) {
        $empleados = $mpys->obtenerEmpleadosPorDependencia($iddependencia);
        header('Content-Type: application/json');
        echo json_encode($empleados);
        exit;
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Falta el parámetro iddependencia']);
        exit;
    }
}

// obtener historial de paz y salvos
if (isset($_GET['action']) && $_GET['action'] === 'get_historial') {
    header('Content-Type: application/json');

    try {
        $documento = $_GET['documento'] ?? '';
        if (empty($documento)) {
            echo json_encode(['success' => false, 'error' => 'Documento no proporcionado']);
            exit;
        }

        $empleado = $mpys->obtenerDatosCompletosEmpleado($documento);
        if (!$empleado || !isset($empleado['idusu'])) {
            echo json_encode(['success' => false, 'error' => 'Empleado no encontrado']);
            exit;
        }

        echo json_encode([
            'success' => true,
            'data' => $empleado['historial'] ?? []
        ]);
        exit;
    } catch (Throwable $e) {
        error_log("Error en get_historial: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error: ' . $e->getMessage()]);
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['usuario_id'])) {
    try {
        $empleado = $mpys->obtenerDatosEmpleado($_POST['usuario_id']);
        if (!$empleado || empty($empleado)) {
            throw new Exception('Empleado no encontrado');
        }
        $_SESSION['empleado_seleccionado'] = $empleado[0];
        header('Location: home.php?pg=2401');
        exit;
    } catch (Exception $e) {
        error_log("Error en selección por ID: " . $e->getMessage());
        $_SESSION['error'] = 'Error al seleccionar empleado: ' . $e->getMessage();
    }
}