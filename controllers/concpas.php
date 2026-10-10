<?php
require_once("models/conmpas.php");

$mpas = new Mpas();

$pg = isset($_REQUEST["pg"]) ? $_REQUEST["pg"] : 2009;
$idpas = isset($_REQUEST["idpas"]) && !empty($_REQUEST["idpas"]) ? intval($_REQUEST["idpas"]) : NULL;
$idflu = isset($_REQUEST["idflu"]) && !empty($_REQUEST["idflu"]) ? intval($_REQUEST["idflu"]) : NULL;
$numpas = isset($_POST["numpas"]) && !empty($_POST["numpas"]) ? intval($_POST["numpas"]) : NULL;
$descpas = isset($_POST["descpas"]) ? trim($_POST["descpas"]) : NULL;
$idper = isset($_POST["idper"]) && !empty($_POST["idper"]) ? intval($_POST["idper"]) : NULL;
$dir = isset($_GET["dir"]) ? $_GET["dir"] : NULL;
$opera = isset($_REQUEST["opera"]) ? $_REQUEST["opera"] : NULL;

$datOne = NULL;
$msg = isset($_GET["msg"]) ? $_GET["msg"] : NULL;

$esEdicion = !empty($_POST["es_edicion"]);

$mpas->setIdpas($idpas);

// Operación: Guardar (Crear o Actualizar)
if ($opera == "save" || $opera == "Insertar" || $opera == "Actualizar") {
    if (!empty($idflu) && !empty($descpas) && !empty($idper)) {
        $mpas->setIdflu($idflu);
        $mpas->setDescpas($descpas);
        $mpas->setIdper($idper);
        $mpas->setNumpas($numpas);

        if (!$esEdicion) {
            $mpas->save();
            echo "<script>window.location.href='home.php?pg=" . $pg . "&idflu=" . $idflu . "&msg=guardado';</script>";
            exit;
        } else {
            $mpas->edit();
            echo "<script>window.location.href='home.php?pg=" . $pg . "&idflu=" . $idflu . "&msg=actualizado';</script>";
            exit;
        }
    } else {
        $urlRedirect = "home.php?pg=" . $pg . ($idflu ? "&idflu=" . $idflu : "") . "&msg=error_campos";
        echo "<script>window.location.href='" . $urlRedirect . "';</script>";
        exit;
    }
}

// Operación: Reordenar paso (Subir / Bajar secuencia)
if ($opera == "move" && $idpas && $dir) {
    $mpas->moveStep($idpas, $dir);
    $urlRedirect = "home.php?pg=" . $pg . ($idflu ? "&idflu=" . $idflu : "") . "&msg=reordenado";
    echo "<script>window.location.href='" . $urlRedirect . "';</script>";
    exit;
}

// Operación: Eliminar paso
if ($opera == "eli" && $idpas) {
    $mpas->del();
    $urlRedirect = "home.php?pg=" . $pg . ($idflu ? "&idflu=" . $idflu : "") . "&msg=eliminado";
    echo "<script>window.location.href='" . $urlRedirect . "';</script>";
    exit;
}

// Operación: Cargar datos para edición
if ($opera == "edi" && $idpas) {
    $datOne = $mpas->getOne();
    if ($datOne && !empty($datOne)) {
        $idflu = $datOne[0]['idflu'];
    }
}

// Carga de opciones para selectores
$datFlu = $mpas->getAllFlu();
$datPef = $mpas->getAllPef();

// Carga de pasos filtrados o generales
$nomFluActual = "";
if ($idflu) {
    $datAll = $mpas->getByFlujo($idflu);
    $nextNum = $mpas->getNextStepNumber($idflu);
    
    // Obtener nombre del flujo seleccionado
    if ($datFlu) {
        foreach ($datFlu as $fl) {
            if ($fl['idflu'] == $idflu) {
                $nomFluActual = $fl['nomflu'];
                break;
            }
        }
    }
} else {
    $datAll = $mpas->getAll();
    $nextNum = 1;
}

// Clasificación de flujos con más pasos vs con menos pasos para el filtro
$conteoFlujos = [];
if (!empty($datFlu)) {
    foreach ($datFlu as $fl) {
        $c = intval($fl['cant_pasos'] ?? 0);
        if ($c > 0) {
            $conteoFlujos[$fl['idflu']] = $c;
        }
    }
}

$promedioPasos = !empty($conteoFlujos) ? (array_sum($conteoFlujos) / count($conteoFlujos)) : 1;
$maxPasos = !empty($conteoFlujos) ? max($conteoFlujos) : 0;
$minPasos = !empty($conteoFlujos) ? min($conteoFlujos) : 0;

if (!empty($datAll)) {
    foreach ($datAll as &$row) {
        $cp = isset($row['cant_pasos']) ? intval($row['cant_pasos']) : 0;
        if ($maxPasos == $minPasos) {
            $row['tipo_filtro_pasos'] = 'FILTRO_MAS_PASOS FILTRO_MENOS_PASOS';
        } else {
            if ($cp >= $promedioPasos) {
                $row['tipo_filtro_pasos'] = 'FILTRO_MAS_PASOS';
            } else {
                $row['tipo_filtro_pasos'] = 'FILTRO_MENOS_PASOS';
            }
        }
    }
    unset($row);
}

// Métricas para el dashboard de pasos
$metricsPas = $mpas->getMetrics($idflu);
?>