<?php
  $rootPath = file_exists('models/premvele.php') ? '' : '../';
  require_once $rootPath . 'models/premvele.php';
  require_once $rootPath . 'models/premprele.php';

  $mele = new Mele();
  $mprele = new Mprele();
  
  $idper_sess = isset($_SESSION['idper']) ? $_SESSION['idper'] : 1;
  $idusu_sess = isset($_SESSION['idusu']) ? $_SESSION['idusu'] : 1;
  $dtAllPr = $mprele->getAll($idper_sess, $idusu_sess);

  $fecha = date("YmdHms");
  $idele = (isset($_REQUEST['idele']) && $_REQUEST['idele'] !== '') ? $_REQUEST['idele'] : NULL;
  $idusu = isset($_POST['idusu']) ? $_POST['idusu'] : (isset($_SESSION["idusu"]) ? $_SESSION["idusu"] : 1);
  $nomele = isset($_POST['nomele']) ? $_POST['nomele'] : NULL;
  $nidele = isset($_POST['nidele']) ? $_POST['nidele'] : NULL;
  $marele = isset($_POST['marele']) ? $_POST['marele'] : NULL;
  $tipele = isset($_POST['tipele']) ? $_POST['tipele'] : NULL;
  $noplasena = isset($_POST['noplasena']) ? $_POST['noplasena'] : NULL;
  $desele = isset($_POST['desele']) ? $_POST['desele'] : NULL;
  $preele = isset($_REQUEST['preele']) ? $_REQUEST['preele'] : NULL;
  $rutfot = isset($_POST['rutfot']) ? $_POST['rutfot'] : NULL;
  $foto = isset($_FILES['foto']['name']) ? $_FILES['foto']['name'] : NULL;

  $opera = isset($_REQUEST['opera']) ? $_REQUEST['opera'] : NULL;
  $ope = isset($_REQUEST['ope']) ? $_REQUEST['ope'] : NULL;
  $datOne = NULL;

  if (!empty($foto) && !empty($_FILES['foto']['tmp_name'])) {
    if (!is_dir('fotele')) {
      @mkdir('fotele', 0777, true);
    }
    $nomFot = opti($_FILES['foto'], $idusu, 'fotele', $fecha);
    if (!empty($nomFot)) {
      $rutfot = (strpos($nomFot, 'fotele/') === 0) ? $nomFot : 'fotele/' . $nomFot;
    }
  }

  $mele->setIdele($idele);
  if ($opera=="save"){
    $mele->setIdusu($idusu);
    $mele->setNomele($nomele);
    $mele->setNidele($nidele);
    $mele->setMarele($marele);
    $mele->setTipele($tipele);
    $mele->setNoplasena($noplasena);
    $mele->setDesele($desele);
    $mele->setRutfot($rutfot);
    if ($idele === NULL) {
      $idele = $mele->save();
      if (!isset($idele)) {
        echo 'Id de elemento no encontrado';
        exit();
      }
      if (!empty($rutfot)) {
        $mele->setIdele($idele);
        $mele->setRutfot($rutfot);
        $mele->saveFoto();
      }
    } else {
      $mele->edit();
      if (!empty($rutfot)) {
        $mele->setIdele($idele);
        $mele->setRutfot($rutfot);
        $mele->editFoto();
      }
    } 
    echo "<script>window.location='home.php?pg=" . $pg . "';</script>";
  }

  if ($idele !== NULL && $opera=="editPre") {
    $mele->setPreele($preele);
    $mele->editPre();
    echo "<script>window.location='home.php?pg=" . $pg . "';</script>";
  }

  if ($opera=="eli" && $idele !== NULL) {
    $mele->setIdele($idele);
    $mele->delFot();
    $mele->del();
    echo "<script>window.location='home.php?pg=" . $pg . "';</script>";
  }

  if ($ope=="edi" && $idele !== NULL) $datOne = $mele->getOne();

  // Parámetros de filtros de búsqueda
  $filtro_origen = isset($_REQUEST['filtro_origen']) ? trim($_REQUEST['filtro_origen']) : '';
  $filtro_preele = isset($_REQUEST['filtro_preele']) ? trim($_REQUEST['filtro_preele']) : '';
  $filtro_tipele = isset($_REQUEST['filtro_tipele']) ? trim($_REQUEST['filtro_tipele']) : '';
  $filtro_texto = isset($_REQUEST['filtro_texto']) ? trim($_REQUEST['filtro_texto']) : '';

  // Métricas de Dashboard
  $statsEle = $mele->getEstadisticasElementos();

  // Consulta con filtros aplicados
  $datAll = $mele->getAll($filtro_origen, $filtro_preele, $filtro_tipele, $filtro_texto);
  $datAllTe = $mele->getEle();
?>