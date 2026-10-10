<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../models/admmadep.php';

$admmadep = new Admmadep();

// Función auxiliar para enviar respuestas JSON uniformes
if (!function_exists('sendJsonResponseAdp')) {
    function sendJsonResponseAdp($data, $statusCode = 200) {
        if (ob_get_length()) ob_clean();
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
        exit;
    }
}

// Escuchar peticiones de API / AJAX
$action = isset($_REQUEST['action']) ? trim($_REQUEST['action']) : null;

if ($action) {
    try {
        switch ($action) {
            case 'get_empleados':
                $id_dependencia = isset($_GET['id_dependencia']) ? (int)$_GET['id_dependencia'] : 0;
                if ($id_dependencia <= 0) {
                    throw new Exception('ID de dependencia no válido', 400);
                }
                $empleados = $admmadep->obtenerEmpleadosPorDependencia($id_dependencia);
                sendJsonResponseAdp([
                    'success' => true,
                    'data' => $empleados,
                    'count' => count($empleados)
                ]);
                break;

            case 'get_usuarios_disponibles':
                $usuarios = $admmadep->obtenerUsuariosDisponibles();
                sendJsonResponseAdp([
                    'success' => true,
                    'data' => $usuarios
                ]);
                break;

            case 'get_one':
                $id_dependencia = isset($_GET['id_dependencia']) ? (int)$_GET['id_dependencia'] : 0;
                if ($id_dependencia <= 0) {
                    throw new Exception('ID de dependencia requerido', 400);
                }
                $dep = $admmadep->obtenerDependenciaPorId($id_dependencia);
                if (!$dep) {
                    throw new Exception('Dependencia no encontrada', 404);
                }
                sendJsonResponseAdp([
                    'success' => true,
                    'data' => $dep
                ]);
                break;

            case 'guardar':
                $id_dependencia = isset($_POST['id_dependencia']) && !empty($_POST['id_dependencia']) ? (int)$_POST['id_dependencia'] : null;
                $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
                $descripcion = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : '';

                if (empty($nombre)) {
                    throw new Exception('El nombre de la dependencia es obligatorio', 400);
                }

                if ($id_dependencia) {
                    $admmadep->actualizarDependencia($id_dependencia, $nombre, $descripcion);
                    $msg = "Dependencia actualizada correctamente.";
                } else {
                    $admmadep->crearDependencia($nombre, $descripcion);
                    $msg = "Dependencia creada exitosamente.";
                }

                $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
                if ($isAjax) {
                    sendJsonResponseAdp([
                        'success' => true,
                        'message' => $msg
                    ]);
                } else {
                    $_SESSION['mensaje'] = $msg;
                    $pg = isset($_REQUEST['pg']) ? $_REQUEST['pg'] : 2403;
                    header("Location: home.php?pg=" . $pg);
                    exit;
                }
                break;

            case 'eliminar':
                $id_dependencia = isset($_REQUEST['id_dependencia']) ? (int)$_REQUEST['id_dependencia'] : 0;
                if ($id_dependencia <= 0) {
                    throw new Exception('ID de dependencia no válido', 400);
                }

                $admmadep->eliminarDependencia($id_dependencia);
                $msg = "Dependencia eliminada con éxito.";

                $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
                if ($isAjax) {
                    sendJsonResponseAdp([
                        'success' => true,
                        'message' => $msg
                    ]);
                } else {
                    $_SESSION['mensaje'] = $msg;
                    $pg = isset($_REQUEST['pg']) ? $_REQUEST['pg'] : 2403;
                    header("Location: home.php?pg=" . $pg);
                    exit;
                }
                break;

            case 'asignar_usuario':
                $id_dependencia = isset($_POST['id_dependencia']) ? (int)$_POST['id_dependencia'] : 0;
                $idusu = isset($_POST['idusu']) ? (int)$_POST['idusu'] : 0;

                if ($id_dependencia <= 0 || $idusu <= 0) {
                    throw new Exception('Datos incompletos para asignar el usuario', 400);
                }

                $admmadep->asignarUsuarioDependencia($idusu, $id_dependencia);
                sendJsonResponseAdp([
                    'success' => true,
                    'message' => 'Usuario asignado exitosamente'
                ]);
                break;

            case 'remover_usuario':
                $id_dependencia = isset($_POST['id_dependencia']) ? (int)$_POST['id_dependencia'] : 0;
                $idusu = isset($_POST['idusu']) ? (int)$_POST['idusu'] : 0;

                if ($id_dependencia <= 0 || $idusu <= 0) {
                    throw new Exception('Datos incompletos para desvincular el usuario', 400);
                }

                $admmadep->removerUsuarioDependencia($idusu, $id_dependencia);
                sendJsonResponseAdp([
                    'success' => true,
                    'message' => 'Usuario desvinculado correctamente'
                ]);
                break;

            case 'cambiar_estado_usuario':
                $id_dependencia = isset($_POST['id_dependencia']) ? (int)$_POST['id_dependencia'] : 0;
                $idusu = isset($_POST['idusu']) ? (int)$_POST['idusu'] : 0;
                $activo = isset($_POST['activo']) ? (int)$_POST['activo'] : 0;

                if ($id_dependencia <= 0 || $idusu <= 0) {
                    throw new Exception('Datos incompletos para cambiar el estado del usuario', 400);
                }

                $admmadep->cambiarEstadoUsuarioDependencia($idusu, $id_dependencia, $activo);
                $estadoTxt = ($activo == 1) ? 'habilitado' : 'deshabilitado';
                sendJsonResponseAdp([
                    'success' => true,
                    'message' => "Usuario {$estadoTxt} exitosamente"
                ]);
                break;
        }
    } catch (Exception $e) {
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        if ($isAjax) {
            sendJsonResponseAdp([
                'success' => false,
                'error' => $e->getMessage()
            ], $e->getCode() ?: 500);
        } else {
            $_SESSION['error'] = $e->getMessage();
            $pg = isset($_REQUEST['pg']) ? $_REQUEST['pg'] : 2403;
            header("Location: home.php?pg=" . $pg);
            exit;
        }
    }
}

// Carga estándar de la vista
try {
    $dependencias = $admmadep->obtenerDependencias();
    $total_dependencias = $admmadep->contarTotalDependencias();
    $total_empleados = $admmadep->contarTotalEmpleados();
} catch (Exception $e) {
    $dependencias = [];
    $total_dependencias = 0;
    $total_empleados = 0;
    $error_carga = $e->getMessage();
}
?>
