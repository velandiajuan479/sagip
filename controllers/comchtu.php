<?php
require_once("models/commhtu.php");

$mhtxus = new Mhtxus();
$success = "";
$error = "";
$pg = isset($_REQUEST["pg"]) ? $_REQUEST["pg"] : '2005';

if (isset($_POST["opera"]) && $_POST["opera"] == "save") {
    $idnorad = isset($_POST["idnorad"]) ? trim($_POST["idnorad"]) : "";
    $idusu = isset($_POST["idusu"]) ? trim($_POST["idusu"]) : "";
    if ($idnorad === "" || $idusu === "") {
        $error = "Debe seleccionar una ficha y un instructor.";
    } else {
        $mhtxus->setIdnorad($idnorad);
        $mhtxus->setIdusu($idusu);
        if ($mhtxus->existe()) {
            $error = "Ese instructor ya está asignado a esa ficha.";
        } elseif ($mhtxus->guardar()) {
            $success = "Asignación guardada correctamente.";
        } else {
            $error = "No se pudo guardar la asignación. Verifique los datos e intente de nuevo.";
        }
    }
}

if (isset($_GET["opera"]) && $_GET["opera"] == "delete" && isset($_GET["idnorad"]) && isset($_GET["idusu"])) {
    $mhtxus->setIdnorad($_GET["idnorad"]);
    $mhtxus->setIdusu($_GET["idusu"]);
    if ($mhtxus->eliminar()) {
        $success = "Asignación eliminada correctamente.";
    } else {
        $error = "No se pudo eliminar la asignación.";
    }
}

$busqueda = isset($_GET["busqueda"]) ? $_GET["busqueda"] : null;
$datAll = $mhtxus->getAll($busqueda);
$datUsuarios = $mhtxus->getAllUsuarios();
$datHojas = $mhtxus->getAllHojas();
?>
