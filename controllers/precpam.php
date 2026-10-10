<?php
  $rootPath = file_exists('models/prempam.php') ? '' : '../';
  require_once $rootPath . 'models/prempam.php';

  $mpam = new Prempam();

  $idval = isset($_REQUEST['idval']) ? $_REQUEST['idval'] : NULL;
  $ndocusu = isset($_REQUEST['ndocusu']) ? trim($_REQUEST['ndocusu']) : NULL;
  $fecha = date("YmdHms");
  $idusu = isset($_SESSION['idusu']) ? $_SESSION['idusu'] : 1;
  $fechos = date('Y-m-d H:i:s');
  $nummin = isset($_REQUEST['nummin']) ? $_REQUEST['nummin'] : NULL;
  $nomval = isset($_REQUEST['nomval']) ? $_REQUEST['nomval'] : NULL;
  $parval = isset($_REQUEST['parval']) ? $_REQUEST['parval'] : NULL;
  $ambi = isset($_REQUEST['nomval']) ? $_REQUEST['nomval'] : NULL;

  $ope = isset($_REQUEST['ope']) ? $_REQUEST['ope'] : NULL;
  $datOne = NULL;

  if ($ope == "save") {
    if (!$idval) {
      echo '<script type="text/javascript"> alert("Debe seleccionar un ambiente"); window.location.href ="home.php?pg='.$pg.'";</script>';
      exit;
    }
    if (!$ndocusu) {
      echo '<script type="text/javascript"> alert("Debe ingresar el número de identificación del solicitante"); window.location.href ="home.php?pg='.$pg.'";</script>';
      exit;
    }
    $mpam->setNdocusu($ndocusu);
    $usuDoc = $mpam->getUsuarioByDoc();
    if (empty($usuDoc)) {
      echo '<script type="text/javascript"> alert("No existe un usuario con ese número de identificación"); window.location.href ="home.php?pg='.$pg.'";</script>';
      exit;
    }
    $idusu = $usuDoc[0]['idusu'];
    $mpam->saveMinuta($idusu, $idval, $fechos, 'N');
    $mpam->setAct(2);
    $mpam->setIdval($idval);
    $mpam->save();
    echo '<script type="text/javascript"> window.location.href ="home.php?pg='.$pg.'";</script>';
    exit;
  }

  if ($ope == "edit" && $idval && $nummin) {
    $mpam->updMinuta($nummin, $fechos, 'A');
    $mpam->setAct(1);
    $mpam->setIdval($idval);
    $mpam->save();
    echo '<script type="text/javascript"> window.location.href ="home.php?pg='.$pg.'";</script>';
    exit;
  }

  if ($ope == "editnov" && $nomval) {
    $mpam->setIdval($idval);
    $mpam->setNomval($nomval);
    $mpam->edit();
  }

  $datAll = $mpam->getTodoA();
  $datOne = $mpam->getOne();
  $datAmb = $mpam->getOneDom();
  $cnt = $mpam->getConteos();
?>