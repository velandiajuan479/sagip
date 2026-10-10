<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once("models/commhtr.php");

$icono = 'fa-solid fa-file-arrow-up';

$mhdt = new Mhdt();

$hoy = date("Y-m-d");
$hmes = date("Y-m-d", strtotime($hoy . " +1 month"));
$hmfin = date("Y-m-d", strtotime($hoy . " +3 month"));

$idnorad = isset($_REQUEST["idnorad"]) ? $_REQUEST["idnorad"] : NULL;
$codpro = isset($_POST["codpro"]) ? $_POST["codpro"] : NULL;
$idemp = isset($_POST["idemp"]) ? $_POST["idemp"] : NULL;
$codproesp = isset($_POST["codproesp"]) ? $_POST["codproesp"] : NULL;
$codslem = isset($_POST["codslem"]) ? $_POST["codslem"] : NULL;
$feclini = isset($_POST["feclini"]) ? $_POST["feclini"] : NULL;
$feclin = isset($_POST["feclin"]) ? $_POST["feclin"] : NULL;
$cupo = isset($_POST["cupo"]) ? $_POST["cupo"] : NULL;
$jornada = isset($_POST["jornada"]) ? $_POST["jornada"] : NULL;
$idfic = isset($_POST["idfic"]) ? $_POST["idfic"] : NULL;
$convht = isset($_POST["convht"]) ? $_POST["convht"] : NULL;

$opera = isset($_REQUEST['opera']) ? $_REQUEST['opera'] : NULL;
$act = isset($_REQUEST['act']) ? $_REQUEST['act'] : NULL;
$pg = isset($_GET['pg']) ? $_GET['pg'] : '2000';
$datOne = NULL;

if($idnorad) {
    $mhdt->setIdnorad($idnorad);
}

if($opera == "save") {
    
    if(!$codpro || !$idemp || !$codproesp || !$feclini || !$feclin || !$cupo || !$jornada) {
        echo '<script>err("Todos los campos obligatorios deben ser completados.");</script>';
    } else {
        $mhdt->setCodpro($codpro);
        $mhdt->setIdemp($idemp);
        $mhdt->setCodproesp($codproesp);
        $mhdt->setCodslem($codslem);
        $mhdt->setFeclini($feclini);
        $mhdt->setFeclin($feclin);
        $mhdt->setCupo($cupo);
        $mhdt->setJornada($jornada);
        $mhdt->setIdfic($idfic);
        $mhdt->setConvht($convht);
        
        if(!$idnorad) {
            $newId = $mhdt->save();
            if($newId) {
                $mhdt->setIdnorad($newId);
                $mhdt->saveHxU();
                // Redirect to edit mode on the same page
                echo "<script>alert('Hoja de trabajo creada exitosamente'); window.location.href='home.php?pg=".$pg."&opera=edi&idnorad=".$newId."';</script>";
            } else {
                echo '<script>err("No fue posible guardar la hoja de trabajo.");</script>';
            }
        } else {
            $mhdt->edit();
            echo '<script>alert("Hoja de trabajo actualizada exitosamente"); window.location.href="home.php?pg='.$pg.'";</script>';
        }}}

if($opera == "acti" && $idnorad && $act) {
    $mhdt->setAct($act);
    $mhdt->editAct();
    echo '<script>window.location.href="home.php?pg='.$pg.'";</script>';
    exit;
}

if($opera == "eli" && $idnorad) {
    $mhdt->del();
    echo '<script>alert("Hoja de trabajo eliminada exitosamente"); window.location.href="home.php?pg='.$pg.'";</script>';
}

if($opera == "edi" && $idnorad) {
    $datOne = $mhdt->getOne();
}

$datPr = $mhdt->getAllPro();
$datEm = $mhdt->getAllEmp();
$datPe = $mhdt->getAllVal(25); 
$datJo = $mhdt->getAllVal(1); 
$datFi = $mhdt->getAllFic();
$datAll = $mhdt->getAll();
$totalCursos = 0;
$cursosActivos = 0;
$cursosInactivos = 0;

if ($datAll) {
    $totalCursos = count($datAll);
    foreach ($datAll as $curso) {
        // Asumimos que 'act' es el campo de estado: 1 = Activo, 2 = Inactivo/Finalizado
        if (isset($curso['act']) && $curso['act'] == 1) {
            $cursosActivos++;
        } else {
            $cursosInactivos++;
        }
    }
}

?>