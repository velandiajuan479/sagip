<?php
  $rootPath = file_exists('models/prempra.php') ? '' : '../';
  require_once $rootPath . 'models/prempra.php';

  $mpraul = new Prempra();
  $maula = $mpraul;
 
  $idpres = isset($_REQUEST['idpres']) ? $_REQUEST['idpres'] : NULL;
  $fechin = date("Y-m-d H:i:s");
  $ndocusu = isset($_POST['ndocusu']) ? $_POST['ndocusu'] : NULL;
  $dia = isset($_POST['fecfin']) ? $_POST['fecfin'] : NULL;
  $horini = isset($_POST['horini']) ? $_POST['horini'] : NULL;
  $horfin = isset($_POST['horfin']) ? $_POST['horfin'] : NULL;
  $ope = isset($_REQUEST['ope']) ? $_REQUEST['ope'] : NULL;
  $estado = isset($_POST['estado']) ? $_POST['estado'] : 'Liberado';
  $idaul = isset($_REQUEST['idaul']) ? $_REQUEST['idaul'] : NULL;
  $idusu = isset($_REQUEST['idusu']) ? $_REQUEST['idusu'] : NULL;

  /* if ($ope=="save" && $ndocusu) {
      $ndocusu = str_replace(".", "", $ndocusu);
      $userData = $mpraul->getUser($ndocusu);
      if ($userData && isset($userData['idusu'])) {
          $mpraul->setIdusu($userData['idusu']);
          $mpraul->setIdaul($idaul);
          $mpraul->setFechin($fechin);
          if (!$idpres) $mpraul->save();
      }
  } */

  if ($ope == 'save') {
    if (!$idpres) {
      if (!$idaul) {
        echo '<script type="text/javascript"> alert("Debe seleccionar un aula"); window.location.href ="home.php?pg='.$pg.'";</script>';
        exit;
      }
      if ($ndocusu) {
        $ndocusu = str_replace(".", "", trim($ndocusu));
        $usuDoc = $mpraul->getUser($ndocusu);
        if (empty($usuDoc)) {
          echo '<script type="text/javascript"> alert("No existe un usuario con ese número de identificación"); window.location.href ="home.php?pg='.$pg.'";</script>';
          exit;
        }
        $idusu = $usuDoc[0]['idusu'];
      }
      $mpraul->setIdusu($idusu);
      $mpraul->setIdaul($idaul);
      $mpraul->setFechin(($dia && $horini) ? $dia.' '.$horini.':00' : $fechin);
      $mpraul->setFechfin(($dia && $horfin) ? $dia.' '.$horfin.':00' : '0000-00-00 00:00:00');
      $mpraul->setEstado('Ocupado');
      if ($mpraul->getAulOcu()) {
        echo '<script type="text/javascript"> alert("El aula ya se encuentra ocupada"); window.location.href ="home.php?pg='.$pg.'";</script>';
        exit;
      }
      $mpraul->save();
      echo '<script type="text/javascript"> window.location.href ="home.php?pg='.$pg.'";</script>';
      exit;
    } else {
      $mpraul->setIdpres($idpres);
      $mpraul->setEstado($estado);
      $mpraul->setFechfin($fechin);
      $mpraul->edit();
    }
  }

  if ($ope == 'desSolAul' && $idusu && $idaul) {
    $mpraul->setIdusu($idusu);
    $mpraul->setIdaul($idaul);
    $mpraul->delSol();
  }

  $dtPiso = $maula->getAllPiso();
  $cnt = $mpraul->getConteos();

  if (!function_exists('modalregaula')) {
    function modalregaula($id, $nomaul, $pg) {
      $html = '';
      $html .= '<div class="modal" id="regaula'.$id.'" tabindex="-1" aria-labelledby="regaulaLabel" aria-hidden="true">
                  <div class="modal-dialog">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h4 class="modal-title">Ambiente '.$nomaul.'</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                      </div>
                      <div class="modal-body">
                        <form action="home.php?pg='.$pg.'" method="POST">
                          <div class="mb-3">
                            <label for="ndocusu" class="form-label">Número de Documento</label>
                            <input type="text" class="form-control" id="ndocusu" name="ndocusu" required>
                            <input type="hidden" name="ope" value="save">
                            <input type="hidden" name="idaul" value="'.$id.'">
                          </div>
                          <button type="submit" class="btn btn-primary">Registrar</button>
                        </form>
                        <div id="userInfo" class="mt-3"></div>
                      </div>
                    </div>
                  </div>
                </div>';
      return $html;
    }
  }

  if (!function_exists('modalLibCom')) {
    function modalLibCom($idpres, $nomaul, $pg) {
      $html = '';
      $html .= '<div class="modal" id="libaul'.$idpres.'" tabindex="-1" aria-labelledby="regaulaLabel" aria-hidden="true">
                  <div class="modal-dialog">
                    <div class="modal-content" style="color: #000000;">
                      <div class="modal-header">
                        <h4 class="modal-title">Liberar '.$nomaul.'</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                      </div>
                      <div class="modal-body">
                        <form action="home.php?pg='.$pg.'" method="POST">
                          <div class="mb-3">
                            <label for="estado" class="form-label">Observación</label>
                            <textarea class="form-control" id="estado" name="estado" maxlength="255" required></textarea>
                            <input type="hidden" name="ope" value="save">
                            <input type="hidden" name="idpres" value="'.$idpres.'">
                          </div>
                          <button type="submit" class="btn btn-primary">Liberar</button>
                        </form>
                        <div id="userInfo" class="mt-3"></div>
                      </div>
                    </div>
                  </div>
                </div>';
      return $html;
    }
  }
?>