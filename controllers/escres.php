<?php
require_once 'models/esmres.php';
include("models/musu.php");
date_default_timezone_set('America/Bogota');

$esmres = new Esmres();
$mres = $esmres;
$musu = new Musu();

$nummin = isset($_REQUEST['nummin']) ? $_REQUEST['nummin']:NULL;
$idusu = isset($_REQUEST['idusu']) ? $_REQUEST['idusu']:NULL;
$fechos = date('Y-m-d H:i:s');
$obs = isset($_POST['obs']) ? $_POST['obs']:NULL;
$ideles = isset($_POST['ideles']) ? $_POST['ideles']:NULL;
$dtUds = isset($_REQUEST['dtUds']) ? $_REQUEST['dtUds']:NULL;
$datEle = NULL;
$msjerr = NULL;
$usuVen = false;

if($ideles) $ideles = implode(";", $ideles);

$hoy = date('Y-m-d');
$ndocusu = isset($_REQUEST['ndocusu']) ? $_REQUEST['ndocusu']:NULL;
$ope = isset($_REQUEST['ope']) ? $_REQUEST['ope']:NULL;



//$mres->getupdFecAntUsu();
$mres->updFecAnt();

$pag = 1325;
$pg = $pag;


$direc = "home.php?pg=".$pg;

// echo $ope." ".$nummin." Usu: ".$idusu." obs: ".$obs." Ori: ".$ideles." Fec: ".$fechos." Hoy: ".$hoy."<br><br>";
// die();
$ndocusu = str_replace(".","",$ndocusu);
$ndocusu = str_replace(",","",$ndocusu);
if(strlen($ndocusu)>11){
	if(substr($ndocusu,0,4)=="http")
		$ndocusu = substr($ndocusu,77,(strpos($ndocusu,'/')-77));
	elseif(substr($ndocusu,10,7)=="PubDSK?"){
		$ndocusu = substr($ndocusu,17,60);
		$pose = posiCc($ndocusu);
		$ndocusu = substr($ndocusu,$pose,10);
	}
	else
		$ndocusu = substr($ndocusu,50,8);
}

function posiCc($Texto){
	$p=0;
	for ($i=0; $i < strlen($Texto); $i++) { 
		if(substr($Texto,$i,1)>=0 AND substr($Texto,$i,1)<=9)
			$p++;
		else
			$i = strlen($Texto);
	}
	return ($p-10);
}


function verUsuVen($val, $hoy) {
    if (!$val || !isset($val[0]['fecfin'])) {
        return false;
    }
    
    $fechaFin = $val[0]['fecfin'];
    if (!$fechaFin) {
        return false;
    }
    
    return strtotime($fechaFin) < strtotime($hoy);
}

$mres->setNdocusu($ndocusu);

//echo $mres->getNdocusu()."<br><br>";

if($ope=="save"){
    if($ndocusu) $val = $mres->getUsuario($hoy);
    if($val){
        $mres->setIdusu($val[0]['idusu']);
        $idusu = $val[0]['idusu'];
        
        $usuVen = verUsuVen($val, $hoy);
        
        $dtUrF = $mres->getUsuRgF();
        $msjerr = "";
        $rdtT = $mres->getExiste($val[0]['idusu']);
        $datEle = $mres->selOneEle();
        
        if($rdtT){
            $mres->setIdusu($rdtT[0]['idusu']);
            $gvlt = $mres->getVuelta();
            if($gvlt[0]['can']>0){
                $mres->updHij($fechos,$rdtT[0]['nummin']);
            }
        }
        
    } else {
        $dtUds = $mres->getUsuDsis($ndocusu);
        $idusu = $ndocusu;
        if(!$dtUds){
            $rdtT = NULL;
            $dtUds = $ndocusu;
        }
    }
}

// echo "<br>".$ope."-".$idusu."-".$obs."-".$ideles."-".$fechos."<br>";

if($ope=="edi"){
	if($dtUds){
		$musu->ins($dtUds, "Invitado", "8", $dtUds, "951310", "1", "", "", "","");
		$datUsVis = $musu->selOneIdusu($dtUds, "Invitado", "8", "951310", "1", "", "", "");
		$idusu = $datUsVis[0]['idusu'];
		$musu->insUxP($idusu, 4);
		$musu->insUxP($idusu, 5);
		$musu->insUxP($idusu, 8);
	}
	if($idusu){
		$mres->setIdusu($idusu);
		$mres->setObs($obs);
		$mres->setIdeles($ideles);
		$mres->setFechos($fechos);
		
		$rdtExiste = $mres->getExiste($idusu);
		if($rdtExiste && count($rdtExiste) > 0) $tip = 'F'; else $tip = 'I';
		$mres->setTipmin($tip);

		if($tip == 'F' && $rdtExiste){
			$mres->setNummin($rdtExiste[0]['nummin']);
			$mres->setFhlle($fechos);
			$mres->updHij();
		}else{
			$res = $mres->save();
		}
	}
	//echo '<script>alert("Registro exitoso.");</script>';
	echo '<script>window.location=\''.$direc.'\';</script>';
}

?>