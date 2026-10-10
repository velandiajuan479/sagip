<?php 
    require_once ('models/usumuxfi.php');    
    $muxfi = new Usumuxfi();

    $idusu = isset($_REQUEST['idusu']) ? $_REQUEST['idusu']:NULL;
    $idfic = isset($_REQUEST['idfic']) ? $_REQUEST['idfic']:NULL;
    $ndocusu = isset($_POST['ndocusu']) ? $_POST['ndocusu']:NULL;
    $opera = isset($_REQUEST['opera']) ? $_REQUEST['opera']:NULL;
    $pg = 1526;

    if($opera=="save"){
        if($ndocusu AND $idfic){
            $usu = $muxfi->selUsuarioDoc(trim($ndocusu));
            if($usu){
                $muxfi->setIdusu($usu[0]['idusu']);
                $muxfi->setIdfic($idfic);
                $muxfi->save();
                header("Location: home.php?pg=".$pg); exit;
            }
            echo "<script>alert('Usuario no encontrado con el documento " . addslashes($ndocusu) . "');window.location='home.php?pg=".$pg."';</script>";
            exit;
        }
        header("Location: home.php?pg=".$pg); exit;
    }
    if($opera=="activar"){
        if($idusu AND $idfic){
            $muxfi->setIdusu($idusu);
            $muxfi->setIdfic($idfic);
            $muxfi->activar();
        }
        header("Location: home.php?pg=".$pg); exit;
    }
    if($opera=="del"){
        if($idusu AND $idfic){
            $muxfi->setIdusu($idusu);
            $muxfi->setIdfic($idfic);
            $muxfi->del();
        }
        header("Location: home.php?pg=".$pg); exit;
    }

    $dat = $muxfi->getAll();
    $dfic = $muxfi->getFichas();

    //DASHBOARD
    $dash = $muxfi->getDashboard();
?>