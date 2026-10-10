<?php
require_once("models/conmdocg.php");

$conmdocg = new Conmdocg();

// Capturar parámetros de solicitud
$ndocasp = isset($_REQUEST['ndocasp']) ? trim($_REQUEST['ndocasp']) : (isset($_REQUEST['ndocusu']) ? trim($_REQUEST['ndocusu']) : '');
$opera   = isset($_REQUEST['opera']) ? $_REQUEST['opera'] : NULL;
$iddocg  = isset($_REQUEST['iddocg']) ? $_REQUEST['iddocg'] : NULL;
$tipodoc = isset($_REQUEST['tipodoc']) ? $_REQUEST['tipodoc'] : NULL;
$obsdoc  = isset($_REQUEST['obsdoc']) ? trim($_REQUEST['obsdoc']) : NULL;

$msg      = isset($_REQUEST['msg']) ? $_REQUEST['msg'] : NULL;
$msg_type = isset($_REQUEST['msg_type']) ? $_REQUEST['msg_type'] : 'info';

// Procesar eliminación de documento
if ($opera == "eliminar" && $iddocg) {
    $docToDelete = $conmdocg->getOneById($iddocg);
    if ($docToDelete) {
        if (!empty($docToDelete['rutarchivo']) && file_exists($docToDelete['rutarchivo'])) {
            @unlink($docToDelete['rutarchivo']);
        }
        $conmdocg->delete($iddocg);
        $msg = "Documento eliminado correctamente.";
        $msg_type = "success";
    } else {
        $msg = "No se encontró el documento a eliminar.";
        $msg_type = "danger";
    }
}

// Procesar guardado tradicional (vía POST normal)
if ($opera == "guardar" && $ndocasp && $tipodoc) {
    if (!$conmdocg->validarTipoDocumento($tipodoc)) {
        $msg = "Tipo de documento no válido.";
        $msg_type = "danger";
    } elseif ($conmdocg->esDocumentoInvalido($tipodoc)) {
        $msg = "No se permiten documentos temporales ni carnés estudiantiles.";
        $msg_type = "danger";
    } else {
        $directorio = 'docasp/';
        if (!is_dir($directorio)) {
            mkdir($directorio, 0755, true);
        }

        $conmdocg->setNdocasp($ndocasp);
        $conmdocg->setTipodoc($tipodoc);
        $conmdocg->setObsdoc($obsdoc);
        $conmdocg->setFeccar(date('Y-m-d H:i:s'));
        $conmdocg->setEstdoc(1);

        $existing = $conmdocg->getOne();
        $archivo = isset($_FILES['archivo']['name']) ? $_FILES['archivo']['name'] : NULL;
        $archivo_tmp = isset($_FILES['archivo']['tmp_name']) ? $_FILES['archivo']['tmp_name'] : NULL;
        $archivo_error = isset($_FILES['archivo']['error']) ? $_FILES['archivo']['error'] : NULL;

        if ($archivo && $archivo_error === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($archivo, PATHINFO_EXTENSION));
            $allowed = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx', 'xls', 'xlsx'];
            if (!in_array($ext, $allowed)) {
                $msg = "Tipo de archivo no permitido. Formatos permitidos: " . strtoupper(implode(', ', $allowed));
                $msg_type = "danger";
            } else {
                $nuevoNombre = 'doc_' . $ndocasp . '_' . $tipodoc . '_' . time() . '.' . $ext;
                $rutaDestino = 'docasp/' . $nuevoNombre;
                if (move_uploaded_file($archivo_tmp, $rutaDestino)) {
                    $conmdocg->setNomarchivo($archivo);
                    $conmdocg->setRutarchivo($rutaDestino);
                    if ($existing && !empty($existing['rutarchivo']) && file_exists($existing['rutarchivo'])) {
                        @unlink($existing['rutarchivo']);
                    }
                    $res = $conmdocg->saveOrUpdate();
                    if ($res === true) {
                        $msg = $existing ? "Documento actualizado correctamente." : "Documento registrado correctamente.";
                        $msg_type = "success";
                    } elseif ($res === 'duplicate') {
                        $msg = "Ya existe un documento de este tipo registrado para este aspirante.";
                        $msg_type = "warning";
                    } else {
                        $msg = "Error al registrar el documento.";
                        $msg_type = "danger";
                    }
                } else {
                    $msg = "Error al subir el archivo al servidor.";
                    $msg_type = "danger";
                }
            }
        } elseif ($existing) {
            $conmdocg->setNomarchivo($existing['nomarchivo']);
            $conmdocg->setRutarchivo($existing['rutarchivo']);
            $conmdocg->saveOrUpdate();
            $msg = "Observaciones actualizadas correctamente.";
            $msg_type = "success";
        }
    }
}

// Consultar datos del aspirante/usuario
$usuario = null;
$dat     = array();

if (!empty($ndocasp)) {
    $usuario = $conmdocg->getUsuarioByDoc($ndocasp);
    if ($usuario) {
        $dat = $conmdocg->getDocsByNdoc($ndocasp);
    } elseif ($opera == "buscar") {
        $msg = "No se encontró ningún usuario con el número de documento ingresado.";
        $msg_type = "warning";
    }
}

// Obtener listados de tipos de documentos
$tiposDoc     = $conmdocg->getTiposDocumento();
$tiposDocFlat = $conmdocg->getAllTiposDocumentoFlat();

// Obtener estadísticas globales de aspirantes (para el dashboard del header)
$statsAsp = $conmdocg->getEstadisticasAspirantes();

// Determinar si el usuario actual es admin para aspirantes (perfiles: 1=Funcionario, 2=Admin Centro, 6=Directivo E/S, etc.)
$esAdminAsp = isset($_SESSION['idpergen']) && in_array($_SESSION['idpergen'], [1, 2, 6, 18, 24, 25, 26, 27, 28, 29, 34, 35, 37, 38, 47]);

// Variables de compatibilidad
$datAsp   = $usuario;
$datDocs  = $dat;
$datTipos = $tiposDocFlat;
$mdocg    = $conmdocg;

// Función para obtener el nombre legible del tipo de documento
if (!function_exists('getTipoNombre')) {
    function getTipoNombre($tiposDoc, $idval) {
        if (!empty($tiposDoc)) {
            foreach ($tiposDoc as $cat) {
                if (!empty($cat['tipos'])) {
                    foreach ($cat['tipos'] as $t) {
                        if ($t['idval'] == $idval) {
                            return $t['nomval'];
                        }
                    }
                }
            }
        }
        return 'Documento (ID ' . $idval . ')';
    }
}
?>