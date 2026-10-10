<?php
    require_once('models/molv.php');
    
    $ko = isset($_POST["ko"]) ? $_POST["ko"]:NULL;
    $emausu = isset($_POST["ml"]) ? $_POST["ml"]:NULL;
    $pas1 = isset($_POST["pas1"]) ? $_POST["pas1"]:NULL;
    $pas2 = isset($_POST["pas2"]) ? $_POST["pas2"]:NULL;
    $idusu = isset($_POST["idusu"]) ? $_POST["idusu"]:NULL;
    $ope = isset($_POST["ope"]) ? $_POST["ope"]:NULL;
    
    //echo $ko." - ".$emausu." - ".$pas1." - ".$pas2." - ".$idusu." - ".$ope;
    
    $molv = new Molv();
    $act = false;

    if($ko AND $emausu){
        date_default_timezone_set('America/Bogota');
        $fecsol = date('Y-m-d H:i:s');
        $molv->setEmausu($emausu);
        $molv->setKeyolv($ko);
        $molv->setFecsol($fecsol);
        $datAll = $molv->getOneKO();
        if($datAll){
            $act = true;
        }else{
            echo "<script>alert('Datos incorrectos. Vuelva a iniciar el proceso de solicitud de cambio de contraseña.');</script>";
            sal();
        }
    }elseif($pg==183){
        if($ope=="AcTuAl" AND $pas1===$pas2){
            $pas1 = sha1(md5($pas1));
            $molv->setPas($pas1);
            $molv->setIdusu($idusu);
            $molv->updPas();
            sal();
        }else{
            echo "<script>alert('No se registro el cambio de clave. Vuelva a iniciar el proceso de solicitud de cambio de contraseña.');</script>";
            sal();
        }
    }
    
function sal(){
    echo "<script>window.location.href='https://senacda.com/sagi/index.php';</script>";
}

?>