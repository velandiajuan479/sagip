<?php
    require_once('../models/conexion.php');
    require_once('../models/molv.php');
    require_once("cmail.php");
    
    $molv = new Molv();
    
    $emausu = isset($_POST["emausu"]) ? $_POST["emausu"]:NULL;
    date_default_timezone_set('America/Bogota');
    
    if($emausu){
        $molv->setEmausu($emausu);
        $dtAll = $molv->getOneEma();
        if($dtAll){
            $keyolv = genPass(15);
            $fecsol = date('Y-m-d H:i:s');
            $molv->setFecsol($fecsol);
            $molv->setKeyolv($keyolv);
            $molv->setIdusu($dtAll['idusu']);
            $molv->updUsu();
            $titu = "Cambiar clave de ingreso en SenaCDA.com";
            $mens = plaOlvCon($dtAll['nomusu'], $emausu, $keyolv);
            envmail($emausu, $titu, $mens);
            echo "<script>alert('Revise el e-mail ".$emausu." y siga los pasos para recordar su contraseña.');</script>";
        }else{
            echo "<script>alert('Este e-mail no se encuentra registrado en nuestro sistema. Por favor verifíquelo nuevamente.');</script>";
        }
    }
    echo "<script>window.location.href='https://senacda.com/sagi/index.php';</script>";
    //header('Location: https://senacda.com/sagi/index.php');
    
    function genPass($len){
        $key = "";
        $pattern = "1234567890qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM";
        $max = strlen($pattern)-1;
        for($i=0;$i<$len;$i++) $key .= substr($pattern, mt_rand(0,$max),1);
        $key = sha1(md5($key));
        return $key;
    }
?>