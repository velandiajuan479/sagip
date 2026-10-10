<?php
  $rootPath = file_exists('models/premprele.php') ? '' : '../';
  require_once $rootPath . 'models/premprele.php';

  $mprele = new Mprele();
 
  $date = date('Y-m-d H:i:s');
  $idprele = isset($_REQUEST['idprele']) ? $_REQUEST['idprele'] : NULL;
  $idele = isset($_REQUEST['idele']) ? $_REQUEST['idele'] : NULL;
  $docusu = isset($_REQUEST['docusu']) ? trim($_REQUEST['docusu']) : (isset($_REQUEST['idusu']) ? trim($_REQUEST['idusu']) : NULL);
  $fhpre = isset($_POST['fhpre']) && !empty($_POST['fhpre']) ? $_POST['fhpre'] : $date;
  $fhent = isset($_POST['fhent']) ? $_POST['fhent'] : NULL;
  $estpre = isset($_POST['estpre']) ? $_POST['estpre'] : NULL;
  $whopre = isset($_SESSION['idusu']) ? $_SESSION['idusu'] : 1;
	$can = isset($_POST['can']) ? $_POST['can']	:	1;
  $idusu = isset($_GET['idusu']) ? $_GET['idusu'] : NULL;

  $ope = isset($_REQUEST['ope']) ? $_REQUEST['ope'] : NULL;
  $dtOne = NULL;

  $mprele->setIdprele($idprele);
  if ($ope == 'save') {
    $userRow = $mprele->getUsu($docusu);
    if (!$userRow) {
      echo "<script>
        Swal.fire({
          icon: 'error',
          title: 'Usuario no encontrado',
          text: 'El documento \"" . htmlspecialchars($docusu) . "\" no corresponde a ningún aprendiz o funcionario registrado en el sistema.'
        }).then(function() { window.location.href='home.php?pg=" . $pg . "'; });
      </script>";
      return;
    }

    $idBeneficiario = $userRow[0]['idusu'];

    // Control de Morosos: Verificar si el beneficiario tiene préstamos activos pendientes de entrega
    $prestamosPendientes = $mprele->getPrestamosPendientes($idBeneficiario);
    if (!empty($prestamosPendientes) && !$idprele) {
      $listaItems = '';
      foreach ($prestamosPendientes as $pend) {
        $listaItems .= $pend['nomele'] . ' (Placa: ' . $pend['noplasena'] . ') prestado el ' . $pend['fhpre'] . '<br>';
      }
      echo "<script>
        Swal.fire({
          icon: 'warning',
          title: 'Bloqueo por Morosidad',
          html: 'El usuario <strong>" . htmlspecialchars($userRow[0]['nomusu']) . "</strong> tiene elementos pendientes por entregar:<br><br><code>" . $listaItems . "</code><br>Debe registrar la devolución del elemento pendiente antes de autorizar un nuevo préstamo.',
          confirmButtonColor: '#117f09'
        }).then(function() { window.location.href='home.php?pg=" . $pg . "'; });
      </script>";
      return;
    }

    $mprele->setIdusu($idBeneficiario);
    $mprele->setIdele($idele);
    $mprele->setFhpre($fhpre);
    $mprele->setEstpre(1);
    $mprele->setWhopre($whopre);
    if ($idprele) $mprele->edit();
    else $mprele->save();

    if ($idele) {
      $mprele->saveMinuta($idBeneficiario, $idele, $fhpre, $can, 'A');
    }
    echo '<script type="text/javascript"> window.location.href ="home.php?pg='.$pg.'";</script>';
  }
  
  if ($ope == 'entEle' && $idprele && $idele && $idusu) {
    $mprele->setEstpre(2);
    $mprele->setFhent($date);
    $mprele->entEle();
    $mprele->setIdele($idele);
    $mprele->setIdusu($idusu);
    $min = $mprele->getMin();

    if ($min) {
      $mprele->updMinuta($min[0]['nummin'], $date, 'E');
    }
    echo '<script type="text/javascript"> window.location.href ="home.php?pg='.$pg.'";</script>';
  }
  if ($ope == 'del' && $idprele) $mprele->del();

  // Filtros de búsqueda
  $filtro_estado = isset($_REQUEST['filtro_estado']) ? trim($_REQUEST['filtro_estado']) : '';
  $filtro_fin = isset($_REQUEST['filtro_fin']) ? trim($_REQUEST['filtro_fin']) : '';
  $filtro_ffi = isset($_REQUEST['filtro_ffi']) ? trim($_REQUEST['filtro_ffi']) : '';
  $filtro_idele = isset($_REQUEST['filtro_idele']) ? trim($_REQUEST['filtro_idele']) : '';
  $filtro_texto = isset($_REQUEST['filtro_texto']) ? trim($_REQUEST['filtro_texto']) : '';

  // Métricas Dashboard
  $idper_sess = isset($_SESSION['idper']) ? $_SESSION['idper'] : 1;
  $idusu_sess = isset($_SESSION['idusu']) ? $_SESSION['idusu'] : 1;
  $statsPre = $mprele->getEstadisticasPrestamos($idper_sess, $idusu_sess);
  $resumenMorosos = $mprele->getResumenMorosos();

  // Consulta con filtros aplicados
  $dtAll = $mprele->getAll($idper_sess, $idusu_sess, $filtro_estado, $filtro_fin, $filtro_ffi, $filtro_idele, $filtro_texto);
  $dtEle = $mprele->getEles();
  $dtEleFiltro = $mprele->getElementosFiltro();
?>