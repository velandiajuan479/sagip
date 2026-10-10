<?php
/**
 * Controlador: etacbit.php
 * Intermediario entre la vista (etavbit.php) y el modelo (etambit.php).
 */

require_once 'models/conexion.php';
require_once 'models/etambit.php';

$db = new Conexion();
$conexion = $db->get_conexion();
$objBitacora = new etambit($conexion);

if (empty($_SESSION['idusu'])) {
    header('Location: home.php?pg=login&msg=' . urlencode('Debe iniciar sesión para gestionar bitácoras.'));
    exit;
}
$idusu = (int) $_SESSION['idusu'];
$empresaAprendiz = $objBitacora->getEmpresaAprendiz($idusu);

$opera = $_REQUEST['opera'] ?? '';
$idbitacora = $_REQUEST['idbitacora'] ?? null;
$msj = $_GET['msj'] ?? '';
$datOneBit = null;
$actividadesEditar = [];

switch ($opera) {
    case 'save':
        $rutaArchivo = null;
        if (($_POST['tipo_evidencia'] ?? '') === 'archivo' && isset($_FILES['archivo_evidencia']) && $_FILES['archivo_evidencia']['error'] === UPLOAD_ERR_OK) {
            $directorioDestino = __DIR__ . '/../archivos/evidencias/';
            if (!file_exists($directorioDestino)) {
                mkdir($directorioDestino, 0755, true);
            }

            $extension = strtolower(pathinfo($_FILES['archivo_evidencia']['name'], PATHINFO_EXTENSION));
            $extensionesPermitidas = ['pdf', 'zip', 'rar', 'png', 'jpg', 'jpeg'];
            if (!in_array($extension, $extensionesPermitidas, true) || $_FILES['archivo_evidencia']['size'] > 10 * 1024 * 1024) {
                $msj = 'El archivo no es válido o supera el límite de 10 MB.';
            } else {
                $nombreArchivo = bin2hex(random_bytes(16)) . '.' . $extension;
                $rutaCompleta = $directorioDestino . $nombreArchivo;
                
                if (move_uploaded_file($_FILES['archivo_evidencia']['tmp_name'], $rutaCompleta)) {
                    $rutaArchivo = 'archivos/evidencias/' . $nombreArchivo;
                } else {
                    $msj = "Error al subir el archivo de evidencia.";
                }
            }
        }

        // Se da prioridad a los datos ingresados en el formulario
        $dataBitacora = [
            'idbitacora'        => $_POST['idbitacora'] ?? null,
            'idaprendiz'        => $idusu,
            'idjefe'            => filter_var($_POST['idjefe'] ?? null, FILTER_VALIDATE_INT) ?: null,
            'idinstructor'      => filter_var($_POST['idinstructor'] ?? null, FILTER_VALIDATE_INT) ?: null,
            'numero_bitacora'   => $_POST['numero_bitacora'] ?? null,
            'nombre_empresa'    => !empty($_POST['nombre_empresa']) ? trim($_POST['nombre_empresa']) : ($empresaAprendiz['nomemp'] ?? ''),
            'nit'               => !empty($_POST['nit']) ? trim($_POST['nit']) : ($empresaAprendiz['numdocemp'] ?? ''),
            'fecha_inicio'      => $_POST['fecha_inicio'] ?? null,
            'fecha_fin'         => $_POST['fecha_fin'] ?? null,
            'observacion'       => trim($_POST['observacion'] ?? 'Sin observación'),
            'nvlarl'            => $_POST['nvlarl'] ?? '1',
            'idaltep'           => $_POST['idaltep'] ?? null,
            'idsubaltep'        => $_POST['idsubaltep'] ?? null,
            'tipo_evidencia'    => $_POST['tipo_evidencia'] ?? null,
            'link_evidencia'    => $_POST['link_evidencia'] ?? null,
            'archivo_evidencia' => $rutaArchivo,
            'actividades'       => [
                'fecha_inicio' => $_POST['fecha_inicio_act'] ?? [],
                'fecha_fin'    => $_POST['fecha_fin_act'] ?? [],
                'evidencia'    => $_POST['actividad_evidencia'] ?? [],
                'descripcion'  => $_POST['actividad_descripcion'] ?? []
            ]
        ];

        if ($msj === '' && $objBitacora->saveBitacora($dataBitacora, $idusu)) {
            $msj = "Bitácora guardada correctamente.";
            header("Location: home.php?pg=1534&msj=" . urlencode($msj));
            exit;
        } else {
            if ($msj === '') {
                $msj = $objBitacora->getLastError() ?: "No fue posible guardar la bitácora.";
            }
        }
        break;

    case 'Eliminar':
        $idbitacora = filter_var($idbitacora, FILTER_VALIDATE_INT);
        if ($idbitacora) {
            if ($objBitacora->deleteBitacora($idbitacora, $idusu)) {
                $msj = "Registro eliminado con éxito.";
            } else {
                $msj = "No se pudo eliminar el registro. Solo se pueden eliminar bitácoras en estado Pendiente.";
            }
        }
        break;

    case 'edit':
        $datOneBit = $objBitacora->getOneBitacora($idbitacora, $idusu);
        if ($datOneBit) {
            $actividadesEditar = $objBitacora->getActividadesByBitacora($idbitacora);
        }
        break;
}

// Preparación de variables para la vista
$datOne = $objBitacora->getAprendizData($idusu);
$datPrograma = $datOne;
$datFicha = $datOne;
$datEmpresa = $empresaAprendiz;

$datDominios = $objBitacora->getDominios();
$datValores = $objBitacora->getSubAlternativas();

$proximoNumero = $objBitacora->getSiguienteNumeroBitacora($idusu);
if ($proximoNumero > 12) {
    $proximoNumero = null;
}

$fechaInicioValor = date('Y-m-d'); 
$fechaFinValor = date('Y-m-d', strtotime('+15 days'));
$fechaInicioPeriodo = date('Y-m-d', strtotime('-30 days'));
$fechaFinPeriodo = date('Y-m-d', strtotime('+30 days'));

$bitacorasAMostrar = $objBitacora->getAllBitacoras($idusu);

// Fallbacks seguros para evitar errores en la vista si no hay relaciones cargadas
$datJefes = [['idusu' => '', 'nomusu' => 'Pendiente por asignar', 'emausu' => '', 'telcan' => '']];
$datInst = [['idusu' => '', 'nomusu' => 'Pendiente por asignar', 'emausu' => '']];
$datCargo = [['nomper' => 'Pendiente']];

function modalBitacoraDetalles($idbitacora, $numero_bitacora, $pg) {
    global $objBitacora;
    $actividades = $objBitacora->getActividadesByBitacora($idbitacora);
    ob_start();
    ?>
    <div class="modal fade" id="bitDet<?= (int) $idbitacora; ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Actividades de la Bitácora #<?= htmlspecialchars($numero_bitacora); ?></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <?php if ($actividades): ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Descripción</th>
                                        <th>Inicio</th>
                                        <th>Fin</th>
                                        <th>Evidencia</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($actividades as $actividad): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($actividad['descripcion_actividad']); ?></td>
                                            <td><?= htmlspecialchars($actividad['fecha_inicio_act'] ?? ''); ?></td>
                                            <td><?= htmlspecialchars($actividad['fecha_fin_act'] ?? ''); ?></td>
                                            <td><?= htmlspecialchars($actividad['evidencia_cumplimiento']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No hay actividades registradas para esta bitácora.</p>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function modalVerObservacion($idbitacora, $numero_bitacora, $observacion) {
    ob_start();
    ?>
    <div class="modal fade" id="modalObs<?= (int) $idbitacora; ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">Observación - Bitácora #<?= htmlspecialchars($numero_bitacora); ?></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <?= nl2br(htmlspecialchars($observacion)); ?>
                </div>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

$icono = 'fa-solid fa-file-signature';
$pg = 1534;

require_once 'views/etavbit.php';
?>