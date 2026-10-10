<?php
require_once 'models/etamgssol.php';

$mcerticor = new Etamgssol();

$opera = $_REQUEST['opera'] ?? null;
$pg = $_REQUEST['pg'] ?? 'vcerticor';

// FUNCIÓN DE REDIRECCIÓN SEGURA
function redirigir($url) {
    if (!headers_sent()) {
        header("Location: " . $url);
    } else {
        echo "<script>window.location.href='" . $url . "';</script>";
    }
    exit;
}

// Mensajes según parámetro de redirección
$mensaje = '';
$tipoMensaje = '';

$msg = $_GET['msg'] ?? null;
if ($msg === 'solicitud_aprobada') {
    $mensaje = 'La solicitud ha sido aprobada exitosamente por Coordinación.';
    $tipoMensaje = 'success';
} elseif ($msg === 'solicitud_rechazada') {
    $mensaje = 'La solicitud ha sido rechazada por Coordinación.';
    $tipoMensaje = 'success';
} elseif ($msg === 'error_datos') {
    $mensaje = 'Error: Faltan datos obligatorios para procesar la solicitud.';
    $tipoMensaje = 'danger';
} elseif ($msg === 'error_ya_procesada') {
    $mensaje = 'Error: La solicitud ya fue procesada anteriormente.';
    $tipoMensaje = 'warning';
} elseif ($msg === 'error_no_existe') {
    $mensaje = 'Error: La solicitud no existe.';
    $tipoMensaje = 'danger';
} elseif ($msg === 'error_bd') {
    $mensaje = 'Error al actualizar el estado de la solicitud en la base de datos.';
    $tipoMensaje = 'danger';
}

// PROCESAR SOLICITUD INDIVIDUAL (POST)
if ($opera === 'procesar_solicitud' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $idsolicitud = (int)($_POST['idsolicitud'] ?? 0);
    $accion = $_POST['accion'] ?? '';
    $observaciones = trim($_POST['observaciones'] ?? '');

    if (empty($idsolicitud) || empty($accion)) {
        redirigir("home.php?pg=" . urlencode($pg) . "&msg=error_datos");
    }

    $solicitudDetalle = $mcerticor->getDetalleSolicitudConPrograma($idsolicitud);

    if (!$solicitudDetalle) {
        redirigir("home.php?pg=" . urlencode($pg) . "&msg=error_no_existe");
    }

    if (($solicitudDetalle['estado'] ?? '') !== 'pendiente') {
        redirigir("home.php?pg=" . urlencode($pg) . "&msg=error_ya_procesada");
    }

    $nuevoEstado = '';
    $observacionesCompletas = '';

    if ($accion === 'aprobar') {
        $nuevoEstado = 'aprobada_coordinacion';
        $observacionesCompletas = "✅ SOLICITUD APROBADA POR COORDINACIÓN\n";
        $observacionesCompletas .= "📅 Fecha: " . date('d/m/Y H:i:s') . "\n";
        $observacionesCompletas .= "🎓 Programa: " . ($solicitudDetalle['nombre_programa'] ?? 'N/A') . "\n";
        if (!empty($observaciones)) {
            $observacionesCompletas .= "📝 Observaciones: " . $observaciones;
        }
    } elseif ($accion === 'rechazar') {
        $nuevoEstado = 'rechazada';
        $observacionesCompletas = "❌ SOLICITUD RECHAZADA POR COORDINACIÓN\n";
        $observacionesCompletas .= "📅 Fecha: " . date('d/m/Y H:i:s') . "\n";
        $observacionesCompletas .= "🎓 Programa: " . ($solicitudDetalle['nombre_programa'] ?? 'N/A') . "\n";
        if (!empty($observaciones)) {
            $observacionesCompletas .= "📝 Motivo: " . $observaciones;
        } else {
            $observacionesCompletas .= "📝 Motivo: No cumple con los requisitos establecidos.";
        }
    } else {
        redirigir("home.php?pg=" . urlencode($pg) . "&msg=error_datos");
    }

    $resultado = $mcerticor->actualizarEstadoSolicitudCorregido(
        $idsolicitud,
        $nuevoEstado,
        $observacionesCompletas
    );

    if ($resultado) {
        $msgResult = ($accion === 'aprobar') ? 'solicitud_aprobada' : 'solicitud_rechazada';
        redirigir("home.php?pg=" . urlencode($pg) . "&msg=" . urlencode($msgResult));
    } else {
        redirigir("home.php?pg=" . urlencode($pg) . "&msg=error_bd");
    }
}

// SOLICITUD SELECCIONADA PARA GESTIONAR (POR GET)
$id_gestionar = isset($_GET['id_gestionar']) ? (int)$_GET['id_gestionar'] : 0;
$solicitud_gestionar = null;
if ($id_gestionar > 0) {
    $solicitud_gestionar = $mcerticor->getDetalleSolicitudConPrograma($id_gestionar);
    if (!$solicitud_gestionar || ($solicitud_gestionar['estado'] ?? '') !== 'pendiente') {
        $solicitud_gestionar = null;
    }
}

// CARGAR DATOS PARA LA VISTA
try {
    $datEstadisticas = $mcerticor->getEstadisticasSolicitudes();
    if (!$datEstadisticas) {
        $datEstadisticas = ['total' => 0, 'pendientes' => 0, 'aprobadas' => 0, 'completadas' => 0, 'rechazadas' => 0, 'automaticas' => 0];
    }

    $datSolicitudes = $mcerticor->getAllSolicitudesDetalladas();
    if (!is_array($datSolicitudes)) {
        $datSolicitudes = [];
    }
} catch (Exception $e) {
    error_log("Error al cargar solicitudes en ccerticor: " . $e->getMessage());
    $datEstadisticas = ['total' => 0, 'pendientes' => 0, 'aprobadas' => 0, 'completadas' => 0, 'rechazadas' => 0, 'automaticas' => 0];
    $datSolicitudes = [];
}

// SEPARACIÓN DE SOLICITUDES POR ESTADO
$solicitudes_pendientes = [];
$solicitudes_aprobadas = [];
$solicitudes_rechazadas = [];

foreach ($datSolicitudes as $sol) {
    $estado = $sol['estado'] ?? '';
    if ($estado === 'pendiente') {
        $solicitudes_pendientes[] = $sol;
    } elseif ($estado === 'aprobada_coordinacion' || $estado === 'completada') {
        $solicitudes_aprobadas[] = $sol;
    } elseif ($estado === 'rechazada') {
        $solicitudes_rechazadas[] = $sol;
    }
}

// FUNCIONES AUXILIARES
function getTextoEstado($estado) {
    $estados = [
        'pendiente' => 'Pendiente Revisión',
        'aprobada_coordinacion' => 'Aprobada - Esp. Firma',
        'completada' => 'Completada',
        'rechazada' => 'Rechazada'
    ];
    return $estados[$estado] ?? ucfirst((string)$estado);
}

function formatearTipoCertificacion($tipo) {
    $tipos = [
        'certificacion_inicio' => 'Inicio de Formación',
        'certificacion_parcial' => 'Competencias Parciales',
        'certificacion_etapa_productiva' => 'Etapa Productiva',
        'certificacion_tecnica_completa' => 'Técnica Completa',
        'certificacion_tecnologica_completa' => 'Tecnológica Completa',
        'certificacion_curso_inicio' => 'Participación en Curso',
        'certificacion_curso_intermedio' => 'Progreso en Curso',
        'certificacion_curso_completo' => 'Finalización de Curso',
        'constancia_estudiante_activo' => 'Constancia de Estudiante'
    ];
    return $tipos[$tipo] ?? ucfirst(str_replace('_', ' ', (string)$tipo));
}