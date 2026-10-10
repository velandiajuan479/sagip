<?php
  require_once("models/desmdsra.php");

  $mdsra = new Desmdsra();
  $pg = isset($_GET['pg']) ? $_GET['pg'] : 2420;

  // Selección de ficha (la mantiene en sesión)
  if (!empty($_POST['idfic1'])) {
    $_SESSION['idfic'] = $_POST['idfic1'];
  } elseif (!empty($_POST['idfic2'])) {
    $_SESSION['idfic'] = $_POST['idfic2'];
  } elseif (!empty($_REQUEST['idfic'])) {
    $_SESSION['idfic'] = $_REQUEST['idfic'];
  }
  $idfic = isset($_SESSION['idfic']) ? $_SESSION['idfic'] : NULL;

  if (isset($_POST['limpfic'])) {
    unset($_SESSION['idfic']);
    $idfic = NULL;
  }

  // Datos para la vista
  $dfi = $mdsra->getFicha();
  $datAll = ($idfic) ? $mdsra->getDeserciones($idfic) : NULL;
  $cnt = ($idfic) ? $mdsra->getConteo($idfic) : NULL;

  function modalHistoria($id, $nom, $fic, $nomfic, $pg, $hist, $llam) {
    $html = '';
    $html .= '<div class="modal fade" id="modhisdes'.$id.'" tabindex="-1" aria-hidden="true">';
      $html .= '<div class="modal-dialog modal-lg">';
        $html .= '<div class="modal-content">';
          $html .= '<div class="modal-header">';
            $html .= '<h3>Historial</h3>';
            $html .= '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>';
          $html .= '</div>';
          $html .= '<div class="modal-body">';
            $html .= '<h5>Historial de: '.$nom.'<br>Ficha: '.$fic." ".$nomfic.'</h5><br>';
            $html .= '<h4>Inasistencias</h4>';
            if (!empty($hist)) {
              $html .= '<table class="table table-striped">';
                $html .= '<thead><tr><th>Horas registradas</th><th>Fecha</th><th>Comentario</th></tr></thead>';
                $html .= '<tbody>';
                foreach ($hist as $h) {
                  $comentario = (trim($h['comina']) !== '') ? $h['comina'] : 'Sin comentario';
                  $html .= '<tr><td>'.$h['horasina'].'</td><td>'.$h['fecina'].'</td><td>'.$comentario.'</td></tr>';
                }
                $html .= '</tbody>';
              $html .= '</table>';
            } else {
              $html .= '<div class="alert alert-warning">No hay historial de inasistencias registrado.</div>';
            }
            $html .= '<h4>Llamados de Atención</h4>';
            if (!empty($llam)) {
              $html .= '<table class="table table-striped">';
                $html .= '<thead><tr><th>Tipo</th><th>Fecha</th><th>Observaciones</th></tr></thead>';
                $html .= '<tbody>';
                foreach ($llam as $l) {
                  $tipo = ($l['tipllam'] == 1) ? 'Verbal' : (($l['tipllam'] == 2) ? 'Escrito' : 'Desconocido');
                  $observacion = (trim($l['obsllam']) !== '') ? $l['obsllam'] : 'Sin observación';
                  $html .= '<tr><td>'.$tipo.'</td><td>'.$l['fecllam'].'</td><td>'.$observacion.'</td></tr>';
                }
                $html .= '</tbody>';
              $html .= '</table>';
            } else {
              $html .= '<div class="alert alert-info">No hay llamados de atención registrados.</div>';
            }
          $html .= '</div>';
          $html .= '<div class="modal-footer">';
            $html .= '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>';
          $html .= '</div>';
        $html .= '</div>';
      $html .= '</div>';
    $html .= '</div>';
    return $html;
  }
?>