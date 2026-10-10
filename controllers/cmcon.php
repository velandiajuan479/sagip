<?php
require_once("models/mmcon.php");
date_default_timezone_set('America/Bogota');

$mmcon = new Mmcon();

$nummin = $_REQUEST['nummin'] ?? NULL;
$idusu = $_REQUEST['idusu'] ?? NULL;
$fechos = date('Y-m-d H:i:s');
$hoy = date('Y-m-d');

$rdtT = NULL;
$mrt = NULL;

$ndocusu = $_REQUEST['ndocusu'] ?? NULL;
$ope = $_REQUEST['ope'] ?? NULL;

$mmcon->updFecAnt();
$pag = 1001;

if (strlen($ndocusu) > 11) {
    if (substr($ndocusu, 0, 4) == "http") {
        $ndocusu = substr($ndocusu, 77, (strpos($ndocusu, '/') - 77));
    } elseif (substr($ndocusu, 10, 7) == "PubDSK?") {
        $ndocusu = substr($ndocusu, 17, 60);
        $pose = posiCc($ndocusu);
        $ndocusu = substr($ndocusu, $pose, 10);
    } else {
        $ndocusu = substr($ndocusu, 50, 8);
    }
}

function posiCc($Texto) {
    $p = 0;
    for ($i = 0; $i < strlen($Texto); $i++) {
        if (substr($Texto, $i, 1) >= 0 AND substr($Texto, $i, 1) <= 9) {
            $p++;
        } else {
            $i = strlen($Texto);
        }
    }
    return ($p - 10);
}

$mmcon->setNdocusu($ndocusu);

if ($ope == "save") {
    if ($ndocusu) {
        $val = $mmcon->getUsuario($hoy);
        if ($val) {
            $mmcon->setIdusu($val[0]['idusu']);

            $msjerr = "";
            $rdtT = $mmcon->getExiste($val[0]['idusu'], $dtrut[0]['placa'], $dtrut[0]['idrut']);
            if ($rdtT) {
                $mmcon->setIdusu($rdtT[0]['idusu']);
                $mmcon->setFechos($fechos);
                $gvlt = $mmcon->getVuelta();
                if ($gvlt[0]['can'] > 0) {
                    echo '<script>alert("Aun no puedes registrar la ruta, debes esperar a que haya pasado mínimo una hora, cincuenta minutos desde el registro.");window.location.href = "index.php?pg=202";</script>';
                } else {
                    $mmcon->updHij($fechos, $rdtT[0]['nummin']);
                }
            }
        } else {
            echo '<script>alert("No puede registrarse, por alguna de la siguientes razones:\n\n1. Este usuario no existe.\n2. Su usuario no esta registrado con perfil de Conductor.\n3. Este usuario no tiene asignada agenda para este mes. \n\nComuníquese con el administrador del sistema.");window.location=\'index.php?pg=202\';</script>';
        }
    }
}

if ($ope == "edi") {
    $mmcon->setIdusu($idusu);
    $mmcon->setFechos($fechos);

    $gvlt = $mmcon->getVuelta();
    if ($gvlt[0]['can'] > 0) {
        echo '<script>alert("Aun no puedes registrar la ruta, debes esperar a que haya pasado mínimo una hora, cincuenta minutos desde el registro.");</script>';
    } else {
        $rti = $mmcon->getDest();

        if ($rti[0]['can'] > 0) {
            $tip = 'F';
        } else {
            $tip = 'I';
        }

        $mmcon->setTipmin($tip);

        if ($rti AND $rti[0]['nummin']) {
            $mmcon->updHij($fechos, $rti[0]['nummin']);
        } else {
            $res = $mmcon->save();
        }
        echo '<script>alert("Registro exitoso.");</script>';
    }
}

$image_url = NULL;
?>
