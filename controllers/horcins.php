<?php
/**
 * CONTROLADOR: horcins.php
 * Responsabilidad: Orquestar el flujo de datos entre la Vista (horvins.php) y el Modelo (hormins.php)
 * Arquitectura: Controlador en patrón MVC
 */

require_once __DIR__ . '/../models/hormins.php';

// Si existe autoload de vendor para PhpSpreadsheet
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

use PhpOffice\PhpSpreadsheet\IOFactory;

// Alcance por rol (módulo HyA): fichas visibles según el usuario autenticado.
require_once 'models/hormacceso.php';
$hacceso = new HormAcceso();
$scopeCtx = $hacceso->getContexto();
$scopeFichas = $hacceso->getFichasPermitidas($scopeCtx);
$scopeRol = $hacceso->etiquetaRol($scopeCtx);
$scopeN = count($scopeFichas);
$scopeTotal = !empty($scopeCtx['is_admin']);

// ------------------------------------------------------------
// 1️⃣ Captura y sanitización de variables del request
// ------------------------------------------------------------
$idins  = isset($_REQUEST['idins']) && $_REQUEST['idins'] !== '' ? intval($_REQUEST['idins']) : null;
$idres  = isset($_REQUEST['idres']) && $_REQUEST['idres'] !== '' ? intval($_REQUEST['idres']) : null;
$idfic  = isset($_REQUEST['idfic']) && $_REQUEST['idfic'] !== '' ? intval($_REQUEST['idfic']) : null;
$ope    = $_REQUEST['ope'] ?? null;
$nomins = isset($_POST['nomins']) ? trim($_POST['nomins']) : null;
$pg     = $_REQUEST['pg'] ?? '1545';

$mensajeExito = null;
$error_message = null;

// ------------------------------------------------------------
// 2️⃣ Instanciar modelo de Instrumentos
// ------------------------------------------------------------
$mcins = new Hormins();
if ($idins) $mcins->setIdins($idins);
if ($idres) $mcins->setIdres($idres);
if ($idfic) $mcins->setIdfic($idfic);

$nomfic = $idfic ? $mcins->getNombreFicha($idfic) : "General";

// ------------------------------------------------------------
// 3️⃣ Procesar upload Excel (Importación de Instrumento con Matriz)
// ------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['EXCEL']) && $_FILES['EXCEL']['error'] === UPLOAD_ERR_OK) {
    if (class_exists('PhpOffice\PhpSpreadsheet\IOFactory')) {
        $archivoExcel = $_FILES['EXCEL']['tmp_name'];
        try {
            $spreadsheet = IOFactory::load($archivoExcel);
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            $modelo = new Conexion();
            $conexion = $modelo->get_conexion();
            $conexion->beginTransaction();

            $nominsExcel = trim($rows[0][0] ?? '');
            if (empty($nominsExcel)) {
                throw new Exception("El nombre del instrumento en la celda A1 está vacío.");
            }

            $stmtIns = $conexion->prepare("
                INSERT INTO inseva (nomins, idres, idfic)
                VALUES (:nomins, :idres, :idfic)
            ");
            $stmtIns->bindParam(':nomins', $nominsExcel);
            $stmtIns->bindParam(':idres', $idres);
            $stmtIns->bindParam(':idfic', $idfic);
            $stmtIns->execute();

            $nuevoIdins = $conexion->lastInsertId();

            $criterios = [];
            $total_vscum = 0;

            foreach ($rows as $index => $row) {
                if ($index < 2) continue; // Encabezado
                $nomcri = trim($row[0] ?? '');
                $vscum  = intval($row[1] ?? 0);
                $vpar   = intval($row[2] ?? 0);
                $vncum  = intval($row[3] ?? 0);

                if (empty($nomcri)) continue;

                $criterios[] = [
                    'nomcri' => $nomcri,
                    'vscum'  => $vscum,
                    'vpar'   => $vpar,
                    'vncum'  => $vncum
                ];
                $total_vscum += $vscum;
            }

            if ($total_vscum !== 100 && count($criterios) > 0) {
                throw new Exception("La suma de 'Si Cumple' debe ser 100. Actualmente suma: $total_vscum");
            }

            $stmtCri = $conexion->prepare("
                INSERT INTO criterio (nomcri, vscum, vpar, vncum, idins)
                VALUES (:nomcri, :vscum, :vpar, :vncum, :idins)
            ");
            foreach ($criterios as $c) {
                $stmtCri->bindParam(':nomcri', $c['nomcri']);
                $stmtCri->bindParam(':vscum',  $c['vscum']);
                $stmtCri->bindParam(':vpar',   $c['vpar']);
                $stmtCri->bindParam(':vncum',  $c['vncum']);
                $stmtCri->bindParam(':idins',  $nuevoIdins);
                $stmtCri->execute();
            }

            $conexion->commit();
            $mensajeExito = "Instrumento '$nominsExcel' importado exitosamente desde Excel.";

        } catch (Exception $e) {
            if (isset($conexion) && $conexion->inTransaction()) {
                $conexion->rollBack();
            }
            $error_message = "Error al importar archivo Excel: " . $e->getMessage();
        }
    } else {
        $error_message = "Librería PhpSpreadsheet no disponible para procesar el archivo Excel.";
    }
}

// ------------------------------------------------------------
// 4️⃣ Guardar o Editar instrumento manualmente (POST)
// ------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $ope === "save" && !empty($nomins)) {
    $mcins->setNomins($nomins);
    $mcins->setIdfic($idfic);
    $mcins->setIdres($idres);

    if ($idins) {
        $mcins->setIdins($idins);
        $mcins->edit();
        $mensajeExito = "Instrumento actualizado correctamente en el sistema.";
    } else {
        $insertId = $mcins->save();
        if ($insertId) {
            $mensajeExito = "Instrumento '$nomins' creado exitosamente.";
        } else {
            $error_message = "No fue posible guardar el instrumento en la base de datos.";
        }
    }
}

// ------------------------------------------------------------
// 5️⃣ Eliminar instrumento (ope=del)
// ------------------------------------------------------------
if ($ope === "del" && $idins) {
    $mcins->setIdins($idins);
    $mcins->del();
    $mensajeExito = "Instrumento y sus criterios asociados han sido eliminados.";
}

// ------------------------------------------------------------
// 6️⃣ Cargar datos para edición o consulta
// ------------------------------------------------------------
$dtOne = null;
if ($ope === "edi" && $idins) {
    $mcins->setIdins($idins);
    $dtOne = $mcins->getOne();
}

// Catálogos auxiliares para la interfaz
$listFichas = $hacceso->filtrarPorFicha($mcins->getAllFichas(), 'idfic', $scopeCtx);
$listResultados = $mcins->getAllResultados();

// Listado de instrumentos para la tabla de la vista
if ($idres) {
    $dat = $mcins->getByResultado();
} elseif ($idfic) {
    $dat = $mcins->getByFicha($idfic);
} else {
    $dat = $mcins->getAll();
}

// ------------------------------------------------------------
// 7️⃣ Cargar Vista solo si el controlador fue el punto de entrada
// ------------------------------------------------------------
if (!defined('HORVINS_LOADED_FROM_VIEW')) {
    require_once __DIR__ . '/../views/horvins.php';
}
?>
