<?php
require_once 'models/etamebit.php';

$etamcebit = new Etamebit();

$idusu = $_SESSION["idusu"] ?? null;

if (!$idusu) {
    die("Usuario no autenticado.");
}

// 1. Obtener fichas asignadas al instructor
$fichas = $etamcebit->getFichasBitacoras($idusu);
$fichas_asignadas = array_column($fichas, 'idficha');

$opera = $_REQUEST['opera'] ?? null;
$pg = $_GET['pg'] ?? 'evaluar_bitacora';

// =========================================================================
// 2. PROCESAMIENTO DE EVALUACIÓN MASIVA Y REDIRECCIÓN SEGURA
// =========================================================================
if ($opera === 'evaluar_masivamente') {
    $idficha_post = $_POST['idficha'] ?? null;
    $bitacoras = $_POST['bitacoras_seleccionadas'] ?? [];
    $estado_raw = $_POST['estado_masivo'] ?? null;
    $observacion = trim($_POST['observacion_masiva'] ?? '');

    // Validar pertenencia de la ficha al instructor autenticado
    if (!$idficha_post || !in_array($idficha_post, $fichas_asignadas)) {
        $msg = 'ficha_no_asignada';
    } elseif (empty($bitacoras) || !is_array($bitacoras)) {
        $msg = 'bitacoras_vacias';
    } elseif (!in_array(strtolower($estado_raw), ['aprobado', 'rechazado'])) {
        $msg = 'estado_invalido';
    } elseif ($observacion === '') {
        $msg = 'observacion_vacia';
    } else {
        $estado = (strtolower($estado_raw) === 'aprobado') ? 'Aprobado' : 'Rechazado';
        $actualizados = 0;

        foreach ($bitacoras as $idbitacora) {
            $idbitacora = intval($idbitacora);
            if ($idbitacora > 0) {
                if ($etamcebit->evaluarBitacoraSeguro($idbitacora, $estado, $observacion, $idusu, $idficha_post)) {
                    $actualizados++;
                }
            }
        }

        $msg = ($actualizados > 0) ? 'bitacoras_actualizadas' : 'error_al_actualizar_masiva';
    }

    // Construcción de la URL de redirección
    $redirect_url = "home.php?pg=" . urlencode($pg);
    if ($idficha_post && in_array($idficha_post, $fichas_asignadas)) {
        $redirect_url .= "&idficha=" . urlencode($idficha_post) . "&opera=evaluar_bitacora";
    }
    $redirect_url .= "&msg=" . urlencode($msg);

    // Redirección robusta (previene error de headers sent)
    if (!headers_sent()) {
        header("Location: " . $redirect_url);
    } else {
        echo '<script type="text/javascript">window.location.href="' . $redirect_url . '";</script>';
        echo '<noscript><meta http-equiv="refresh" content="0;url=' . $redirect_url . '" /></noscript>';
    }
    exit;
}

// =========================================================================
// 3. CARGA DE DATOS PARA LA VISTA
// =========================================================================
$idficha_seleccionada = $_REQUEST['idficha'] ?? null;
$aprendices = [];
$aprendices_aprobados = [];
$aprendices_rechazados = [];
$estadisticas_ficha = null;

// Validar que la ficha seleccionada pertenezca a las fichas asignadas al instructor
if ($idficha_seleccionada && !in_array($idficha_seleccionada, $fichas_asignadas)) {
    $idficha_seleccionada = null;
}

if ($idficha_seleccionada) {
    $aprendices = $etamcebit->getAprendicesByFicha($idficha_seleccionada);
    $aprendices_aprobados = $etamcebit->getAprendicesAprobadosByFicha($idficha_seleccionada);
    $aprendices_rechazados = $etamcebit->getAprendicesRechazadosByFicha($idficha_seleccionada);
    
    // Obtener las estadísticas por ficha para el Dashboard
    $estadisticas_ficha = $etamcebit->getEstadisticasPorFicha($idficha_seleccionada);
}

// =========================================================================
// 4. FUNCIONES DE AYUDA PARA LA VISTA
// =========================================================================
function modalBitacoraDetalles($idbitacora, $numero_bitacora, $pg, $etamcebit = null){
    if (!$etamcebit) {
        $etamcebit = new Etamebit();
    }
    
    $actividades = $etamcebit->getActividadesByBitacora($idbitacora);
    $html = '';

    $html .= '<div class="modal fade" id="bitDet'.$idbitacora.'" tabindex="-1" role="dialog" aria-hidden="true">';
        $html .= '<div class="modal-dialog modal-lg">';
            $html .= '<div class="modal-content">';
                $html .= '<div class="modal-header bg-success text-white">';
                    $html .= '<h5 class="modal-title">Actividades de la Bitácora #'.$numero_bitacora.'</h5>';
                    $html .= '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>';
                $html .= '</div>';
                $html .= '<div class="modal-body">';

                if ($actividades && count($actividades) > 0) {
                    $html .= '<div class="table-responsive">';
                        $html .= '<table class="table table-striped">';
                            $html .= '<thead><tr><th>Descripción</th><th>Fecha Inicio</th><th>Fecha Fin</th><th>Evidencia</th></tr></thead>';
                            $html .= '<tbody>';
                            foreach ($actividades as $act) {
                                $html .= '<tr>';
                                    $html .= '<td>'.htmlspecialchars($act['descripcion_actividad'] ?? '', ENT_QUOTES, 'UTF-8').'</td>';
                                    $html .= '<td>'.htmlspecialchars($act['fecha_inicio_act'] ?? '', ENT_QUOTES, 'UTF-8').'</td>';
                                    $html .= '<td>'.htmlspecialchars($act['fecha_fin_act'] ?? '', ENT_QUOTES, 'UTF-8').'</td>';
                                    $html .= '<td>'.htmlspecialchars($act['evidencia_cumplimiento'] ?? '', ENT_QUOTES, 'UTF-8').'</td>';
                                $html .= '</tr>';
                            }
                            $html .= '</tbody>';
                        $html .= '</table>';
                    $html .= '</div>';
                } else {
                    $html .= '<p class="text-muted">No hay actividades registradas para esta bitácora.</p>';
                }

                $html .= '</div>';
                $html .= '<div class="modal-footer">';
                    $html .= '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>';
                $html .= '</div>';
            $html .= '</div>';
        $html .= '</div>';
    $html .= '</div>';

    return $html;
}
?>