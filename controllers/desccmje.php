<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once(__DIR__ . '/../models/desmcmje.php');

$desmcmje = new Desmcmje();

// Captura de datos
$idfic = isset($_REQUEST['idfic']) ? trim($_REQUEST['idfic']) : null;
$ope = isset($_REQUEST['ope']) ? $_REQUEST['ope'] : null;
$pg = isset($_REQUEST['pg']) ? $_REQUEST['pg'] : 2458;
$estado = isset($_REQUEST['estado']) ? strtoupper(trim($_REQUEST['estado'])) : null;
if (!in_array($estado, ['APROBADO', 'POR EVALUAR', 'NO APROBADO'], true)) {
    $estado = null;
}

$mensajeExito = null;
$mensajeError = null;
$resumenImport = null;

// Procesar Acción: Cargar Reporte de Juicios Evaluativos (SENA Sofía Plus)
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && $ope === 'cargarReporte') {
    $archivo = isset($_FILES['archivo']) ? $_FILES['archivo'] : null;
    $fechaReporte = isset($_POST['fecha_reporte']) ? trim($_POST['fecha_reporte']) : '';

    if (!$archivo || !isset($archivo['tmp_name']) || empty($archivo['tmp_name'])) {
        $mensajeError = "Debe seleccionar el archivo del Reporte de Juicios Evaluativos (formato .xls o .xlsx).";
    } elseif ($archivo['error'] !== UPLOAD_ERR_OK) {
        $mensajeError = "Error al subir el archivo (código " . $archivo['error'] . ").";
    } else {
        $nombreOriginal = basename($archivo['name']);
        $extension = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));

        if (!in_array($extension, ['xls', 'xlsx'])) {
            $mensajeError = "Formato no permitido. Solo se admite archivos .xls y .xlsx.";
        } elseif ($archivo['size'] > 20 * 1024 * 1024) {
            $mensajeError = "El archivo supera el tamaño máximo permitido (20 MB).";
        } else {
            // Copiar el archivo a la carpeta de reportes para dejar trazabilidad
            $dirDest = __DIR__ . '/../archivos/juicios';
            if (!is_dir($dirDest)) {
                mkdir($dirDest, 0777, true);
            }
            $destino = $dirDest . '/' . $nombreOriginal;
            if (is_file($destino)) {
                $destino = $dirDest . '/' . date('Ymd_His') . '_' . $nombreOriginal;
            }

            if (!move_uploaded_file($archivo['tmp_name'], $destino)) {
                $mensajeError = "No fue posible almacenar el archivo. Verifique los permisos de la carpeta archivos/juicios.";
            } else {
                $fechaOk = (strtotime($fechaReporte) !== false) ? date('Y-m-d', strtotime($fechaReporte)) : null;
                $resumenImport = $desmcmje->importarReporte($destino, $nombreOriginal, $fechaOk);

                if ($resumenImport['exito']) {
                    $mensajeExito = $resumenImport['mensaje'];
                } else {
                    $mensajeError = $resumenImport['mensaje'];
                }

                if ($idfic === '' || $idfic === null) {
                    $idfic = isset($resumenImport['idfic']) ? $resumenImport['idfic'] : $idfic;
                }
            }
        }
    }
}

// Carga de Datos para la Vista
$resumenPorFicha = $desmcmje->getResumenPorFicha();

// Si no se especificó ficha, tomar la primera con juicios cargados
$fichasJuicios = [];
if (!empty($resumenPorFicha)) {
    foreach ($resumenPorFicha as $rp) {
        $fichasJuicios[$rp['idfic']] = $rp['nomfic'];
    }
}
if (!$idfic && !empty($fichasJuicios)) {
    $idfic = array_keys($fichasJuicios)[0];
}

$juicios = $desmcmje->getJuicios($idfic, 400, $estado);
$contadores = $desmcmje->getContadoresJuicios($idfic);
$dashboard = $desmcmje->getDashboard();
?>