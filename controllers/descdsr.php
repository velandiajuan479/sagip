<?php
  require_once("models/desmdsr.php");
  require_once("models/macta.php");

  $mdsr = new Desmdsr();
  $pg = isset($_GET['pg']) ? $_GET['pg'] : 1407;

  $idusu = isset($_POST['idusu']) ? $_POST['idusu'] : NULL;

  date_default_timezone_set('America/Bogota');
  $fechor = date("Y-m-d H:i:s");

  // La vista siempre carga todos los aprendices; el filtrado por
  // documento o ficha se hace en el cliente con los campos de búsqueda.
  $idfic = NULL;

  // Registrar llamado de atención
  if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['tipllam']) && isset($_POST['idusu'])) {
    $mdsr->guardarLlamado($_POST['idusu'], $_POST['tipllam'], isset($_POST['obsllam']) ? $_POST['obsllam'] : NULL, $fechor);
    echo '<script type="text/javascript"> alert("Llamado de atención registrado"); window.location.href ="home.php?pg='.$pg.'";</script>';
    exit;
  }

  // Cambiar estado del aprendiz (Deserción/Cancelado/Traslado/Retiro)
  if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['ope']) && $_POST['ope'] === 'infor') {
    $idusu = isset($_POST['idusu']) ? $_POST['idusu'] : NULL;
    $actusu = isset($_POST['actusu']) ? $_POST['actusu'] : NULL;
    if ($idusu && $actusu) {
      $ok = $mdsr->camactusu($idusu, $actusu);
      if ($ok) {
        echo '<script type="text/javascript"> alert("Estado actualizado correctamente."); window.location.href ="home.php?pg='.$pg.'";</script>';
      } else {
        echo '<script type="text/javascript"> alert("No se pudo actualizar el estado.");</script>';
      }
    }
    exit;
  }

  // Generar acta de deserción
  if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['ope']) && $_POST['ope'] === 'InAct') {
    $conact = isset($_POST['conact']) ? $_POST['conact'] : NULL;
    $coorusu = isset($_POST['coorusu']) ? $_POST['coorusu'] : NULL;
    $idusu = isset($_POST['idusu']) ? $_POST['idusu'] : NULL;
    $idfic = isset($_POST['idfic']) ? trim($_POST['idfic']) : (isset($_SESSION['idfic']) ? trim($_SESSION['idfic']) : NULL);

    if (!$idfic || !$idusu || !$coorusu) {
      echo '<script type="text/javascript"> alert("Faltan datos para generar el acta.");</script>';
      exit;
    }

    $macta = new Macta();
    $nac = $macta->getNacGen($idfic);
    if ($nac) {
      $macta->setNac($nac['can']);
      $macta->setTipact(201);
      $macta->setFecact($fechor);
      $macta->setConact($conact);
      $macta->setIdusu($_SESSION["idusu"]);
      $macta->setCoorusu($coorusu);
      $macta->save();

      $idact = $macta->getOneIdact();
      if ($idact) {
        $macta->setIdact($idact['idact']);
        $macta->setDesacde("Enviar esta acta a Coordinación Misional y Coordinación académica");
        $macta->setFecacde($fechor);
        $macta->saveAcDe();

        $macta->setFecasi($fechor);
        $macta->saveAsis();
        $macta->setIdusu($coorusu);
        $macta->setFecasi(NULL);
        $macta->saveAsis();

        $macta->setIdusu($idusu);
        $macta->setIdfic($idfic);
        $macta->setFecregdec($fechor);
        $macta->setObsdec("Deserción solicitada");
        $macta->saveDes();

        echo '<script type="text/javascript"> window.location.href ="views/pdfdes.php?idact='.$idact['idact'].'";</script>';
      }
    }
    exit;
  }

  // Cargar imagen de Sofia Plus
  if (isset($_POST['ope']) && $_POST['ope'] === 'cgImg') {
    $idusu = isset($_POST['idusu']) ? $_POST['idusu'] : NULL;
    if (!$idusu || !isset($_FILES['arc']) || $_FILES['arc']['error'] !== 0) {
      echo "Falta el ID de usuario o el archivo no se subió correctamente.";
      exit;
    }
    $nombreArchivo = "Ina" . $idusu . date("YmdHis");
    $rutaDestino = "img/inasi";
    if (!file_exists($rutaDestino)) {
      mkdir($rutaDestino, 0777, true);
    }
    $nombreGuardado = opti($_FILES['arc'], $nombreArchivo, $rutaDestino, "");
    if ($nombreGuardado != '') {
      $modelo = new conexion();
      $conexion = $modelo->get_conexion();
      $sql = "INSERT INTO imagen_usuario (idusu, nomimg) VALUES (:idusu, :nomimg)";
      $stmt = $conexion->prepare($sql);
      $stmt->bindParam(":idusu", $idusu);
      $stmt->bindParam(":nomimg", $nombreGuardado);
      if ($stmt->execute()) {
        echo '<script type="text/javascript"> window.location.href ="home.php?pg='.$pg.'";</script>';
        exit;
      }
    }
    exit;
  }

  // Eliminar imagen de Sofia Plus
  if (isset($_GET['img']) && isset($_GET['idusu'])) {
    require_once(__DIR__ . '/../models/conexion.php');
    $img = $_GET['img'];
    $idusu = $_GET['idusu'];
    $ruta = __DIR__ . "/../img/inasi/" . $img;
    if (file_exists($ruta)) {
      unlink($ruta);
    }
    $modelo = new conexion();
    $conexion = $modelo->get_conexion();
    $sql = "DELETE FROM imagen_usuario WHERE nomimg = :nomimg AND idusu = :idusu";
    $stmt = $conexion->prepare($sql);
    $stmt->bindParam(':nomimg', $img);
    $stmt->bindParam(':idusu', $idusu);
    $stmt->execute();
    echo '<script type="text/javascript"> window.location.href ="home.php?pg='.$pg.'";</script>';
    exit;
  }

  // Datos para la vista
  $dfi = $mdsr->getFicha();
  $datAll = $mdsr->getAprendices($idfic);
  $datTodos = $datAll ? array_values($datAll) : array();

  // Métricas del dashboard (KPI)
  $cnt = array('total' => 0, 'vigentes' => 0, 'llamados' => 0, 'horas' => 0, 'novedades' => 0, 'treinta' => 0);
  foreach ($datTodos as $d) {
    $actusu = (int)$d['actusu'];
    $horas = (int)$d['horasina'];
    $llam = (int)$d['llamver'] + (int)$d['llamesc'];
    $cnt['total']++;
    if ($actusu == 1) $cnt['vigentes']++;
    if ($llam > 0) $cnt['llamados']++;
    $cnt['horas'] += $horas;
    if (in_array($actusu, array(3, 4, 5, 6))) $cnt['novedades']++;
    if ($horas > 30) $cnt['treinta']++;
  }

  $datForm = array_values(array_filter($datTodos, function ($d) { return (int)$d['actusu'] == 1; }));
  $datLlam = array_values(array_filter($datTodos, function ($d) { return ((int)$d['llamver'] + (int)$d['llamesc']) > 0; }));
  $dat30 = array_values(array_filter($datTodos, function ($d) { return (int)$d['horasina'] > 30; }));

  $dcor = $mdsr->getCoordinadores();

  // Tarjeta de aprendiz (una <tr> = una tarjeta, igual que vvidgen)
  function filaAprendiz($dt, $pg) {
    $id = (int)$dt['idusu'];
    $nom = htmlspecialchars($dt['nomusu'], ENT_QUOTES);
    $ndoc = htmlspecialchars($dt['ndocusu'], ENT_QUOTES);
    $estado = htmlspecialchars((trim($dt['nomest']) !== '') ? $dt['nomest'] : 'Sin estado', ENT_QUOTES);
    $activo = ((int)$dt['actusu'] == 1);
    $fic = htmlspecialchars($dt['idfic'], ENT_QUOTES);
    $nomfic = htmlspecialchars($dt['nomfic'], ENT_QUOTES);
    $nomjor = (!empty($dt['nomjor'])) ? htmlspecialchars(' (' . $dt['nomjor'] . ')', ENT_QUOTES) : '';
    $horas = (int)$dt['horasina'];
    $llver = (int)$dt['llamver'];
    $lles = (int)$dt['llamesc'];
    $fecllam = !empty($dt['fecllam']) ? date("d/m/Y", strtotime($dt['fecllam'])) : '---';
    $inicial = htmlspecialchars(strtoupper(substr($dt['nomusu'], 0, 2)), ENT_QUOTES);
    $busq = trim(implode(' ', array($id, $ndoc, $dt['nomusu'], $fic, $dt['nomfic'], $dt['nomest'], $horas, $llver, $lles, $fecllam)));

    $mactaA = new Macta();
    $idactD = $mactaA->getOneActDes($id, $fic);

    $htm = '';
    $htm .= '<tr data-order="' . $id . '" data-doc="' . $ndoc . '" data-fic="' . $fic . '">';
    $htm .= '<td data-search="' . htmlspecialchars($busq, ENT_QUOTES) . '" data-order="' . $id . '">';
    $htm .= '<div class="card h-100 shadow-sm border-0 vid-card">';

    // Cabecera con avatar
    $htm .= '<div class="vid-preview-box">';
    $htm .= '<div class="apr-avatar">' . $inicial . '</div>';
    $htm .= '<div class="apr-badges-top">';
    $htm .= '<span class="badge ' . ($activo ? 'bg-success' : 'bg-danger') . ' shadow-sm px-2 py-1"><i class="fa-solid ' . ($activo ? 'fa-circle-check' : 'fa-circle-xmark') . ' me-1"></i> ' . $estado . '</span>';
    $htm .= '<span class="badge vid-badge-blur px-2 py-1 shadow-sm"><i class="fa-solid fa-id-card me-1 text-info"></i> Doc: ' . $ndoc . '</span>';
    $htm .= '</div>';
    $htm .= '</div>';

    // Cuerpo de la tarjeta
    $htm .= '<div class="card-body p-3 d-flex flex-column justify-content-between">';
    $htm .= '<div>';
    $htm .= '<h6 class="fw-bold text-dark mb-1 text-truncate" title="' . $id . ' - ' . $nom . '"><span class="text-primary">#' . $id . '</span> - ' . $nom . '</h6>';
    $htm .= '<div class="mb-2 text-muted meta-small"><i class="fa-solid fa-users text-secondary me-1"></i> Ficha: <strong>' . $fic . '</strong> - ' . $nomfic . $nomjor . '</div>';
    $htm .= '<div class="p-2 mb-3 rounded-3 bg-light border border-light-subtle d-flex justify-content-between align-items-center box-small">';
    $htm .= '<div><span class="text-muted d-block meta-xs">Horas inasistencia</span><span class="fw-bold text-danger"><i class="fa-regular fa-clock me-1"></i>' . $horas . ' h</span></div>';
    $htm .= '<div class="text-end"><span class="text-muted d-block meta-xs">Llamados</span><span class="fw-bold text-warning"><i class="fa-solid fa-comment-dots me-1"></i>' . $llver . 'V / ' . $lles . 'E</span></div>';
    $htm .= '</div>';
    $htm .= '<div class="text-muted meta-small"><i class="fa-solid fa-calendar-check text-success me-1"></i> <span>Últ. llamado:</span> <strong>' . $fecllam . '</strong></div>';
    $htm .= '</div>';
    $htm .= '</div>';

    // Pie con botones de acción
    $htm .= '<div class="card-footer bg-white border-0 pt-0 pb-3 px-3">';
    $htm .= '<hr class="mt-0 mb-3 text-muted opacity-25">';
    $htm .= '<div class="vid-actions-bar five">';
    $htm .= '<button type="button" class="btn btn-outline-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modllamado' . $id . '" title="Registrar llamado de atención"><i class="fa-solid fa-comment-dots"></i> <span class="d-none d-sm-inline">Llamado</span></button>';
    $htm .= '<button type="button" class="btn btn-outline-info shadow-sm" data-bs-toggle="modal" data-bs-target="#modhisina' . $id . '" title="Historial de inasistencias"><i class="fa-solid fa-clock-rotate-left"></i> <span class="d-none d-sm-inline">Historial</span></button>';
    $htm .= '<button type="button" class="btn btn-outline-danger shadow-sm" data-bs-toggle="modal" data-bs-target="#modnove' . $id . '" title="Novedad / Deserción"><i class="fa-solid fa-ban"></i> <span class="d-none d-sm-inline">Novedad</span></button>';
    $htm .= '<button type="button" class="btn btn-outline-secondary shadow-sm" data-bs-toggle="modal" data-bs-target="#mdcgImg' . $id . '" title="Cargar imagen Sofía Plus"><i class="fa-solid fa-camera"></i> <span class="d-none d-sm-inline">Sofía+</span></button>';
    if ($idactD) {
      $htm .= '<a href="views/pdfdes.php?idact=' . (int)$idactD['idact'] . '" target="_blank" class="btn btn-outline-success shadow-sm" title="Imprimir acta de deserción"><i class="fa-solid fa-print"></i> <span class="d-none d-sm-inline">Acta</span></a>';
    } else {
      $htm .= '<button type="button" class="btn btn-outline-warning shadow-sm" data-bs-toggle="modal" data-bs-target="#CrearActModal' . $id . '" title="Generar acta de deserción"><i class="fa-solid fa-file-pen"></i> <span class="d-none d-sm-inline">Acta</span></button>';
    }
    $htm .= '</div>';

    // Botón del reporte de llamados de atención (filtrado por documento)
    $htm .= '<a href="views/desrlat.php?ndocusu=' . $ndoc . '" target="_blank" class="btn btn-outline-success w-100 shadow-sm mt-2" title="Reporte Llamados de atención de ' . $nom . '">';
    $htm .= '<i class="fa-solid fa-print me-1"></i> Reporte Llamados</a>';

    $htm .= '</div>';

    $htm .= '</div>';
    $htm .= '</td>';
    $htm .= '</tr>';
    return $htm;
  }

  function modalLlamado($id, $nom, $fic, $nomfic, $pg) {
    $html = '';
    $html .= '<div class="modal fade" id="modllamado'.$id.'" tabindex="-1" aria-hidden="true">';
      $html .= '<div class="modal-dialog">';
        $html .= '<form action="home.php?pg='.$pg.'" method="POST">';
          $html .= '<div class="modal-content">';
            $html .= '<div class="modal-header">';
              $html .= '<h3>Registrar llamado de atención</h3>';
              $html .= '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>';
            $html .= '</div>';
            $html .= '<div class="modal-body">';
              $html .= '<h5>Aprendiz: '.$nom.'<br>Ficha: '.$fic." ".$nomfic.'</h5><br>';
              $html .= '<div class="form-group col-md-12 mb-3">';
                $html .= '<label for="tipllam'.$id.'" class="form-label">Tipo de llamado</label>';
                $html .= '<select name="tipllam" id="tipllam'.$id.'" class="form-select">';
                  $html .= '<option value="1">Verbal</option>';
                  $html .= '<option value="2">Escrito</option>';
                $html .= '</select>';
              $html .= '</div>';
              $html .= '<div class="form-group col-md-12 mb-3">';
                $html .= '<label for="obsllam'.$id.'" class="form-label">Observaciones</label>';
                $html .= '<textarea name="obsllam" id="obsllam'.$id.'" class="form-control" required></textarea>';
              $html .= '</div>';
              $html .= '<input type="hidden" name="idusu" value="'.$id.'">';
            $html .= '</div>';
            $html .= '<div class="modal-footer">';
              $html .= '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>';
              $html .= '<button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> Registrar llamado de atención</button>';
            $html .= '</div>';
          $html .= '</div>';
        $html .= '</form>';
      $html .= '</div>';
    $html .= '</div>';
    return $html;
  }

  function modalNovedad($id, $nom, $fic, $nomfic, $pg) {
    $html = '';
    $html .= '<div class="modal fade" id="modnove'.$id.'" tabindex="-1" aria-hidden="true">';
      $html .= '<div class="modal-dialog">';
        $html .= '<form action="home.php?pg='.$pg.'" method="POST">';
          $html .= '<div class="modal-content">';
            $html .= '<div class="modal-header">';
              $html .= '<h3>Novedad - Deserción</h3>';
              $html .= '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>';
            $html .= '</div>';
            $html .= '<div class="modal-body">';
              $html .= '<h5>Aprendiz: '.$nom.'<br>Ficha: '.$fic." ".$nomfic.'</h5><br>';
              $html .= '<div class="mb-3">';
                $html .= '<label for="actusu'.$id.'" class="form-label">Estado de la novedad</label>';
                $html .= '<select class="form-select" name="actusu" id="actusu'.$id.'">';
                  $html .= '<option value="3">Deserción</option>';
                  $html .= '<option value="4">Cancelado</option>';
                  $html .= '<option value="5">Traslado</option>';
                  $html .= '<option value="6">Retiro Voluntario</option>';
                $html .= '</select>';
              $html .= '</div>';
              $html .= '<input type="hidden" name="idusu" value="'.$id.'">';
              $html .= '<input type="hidden" name="ope" value="infor">';
            $html .= '</div>';
            $html .= '<div class="modal-footer">';
              $html .= '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>';
              $html .= '<button type="submit" class="btn btn-primary">Guardar</button>';
            $html .= '</div>';
          $html .= '</div>';
        $html .= '</form>';
      $html .= '</div>';
    $html .= '</div>';
    return $html;
  }

  function modalActa($id, $nom, $fic, $nomfic, $pg, $dcor) {
    $html = '';
    $html .= '<div class="modal fade" id="CrearActModal'.$id.'" tabindex="-1" aria-hidden="true">';
      $html .= '<div class="modal-dialog">';
        $html .= '<form action="home.php?pg='.$pg.'" method="POST">';
          $html .= '<div class="modal-content">';
            $html .= '<div class="modal-header">';
              $html .= '<h3>Acta de Deserción</h3>';
              $html .= '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>';
            $html .= '</div>';
            $html .= '<div class="modal-body">';
              $html .= '<h5>Aprendiz a desertar: '.$nom.'<br>Ficha: '.$fic." ".$nomfic.'</h5><br>';
              $html .= '<div class="row">';
                $html .= '<div class="form-group col-md-12 mb-3">';
                  $html .= '<label for="conact'.$id.'" class="form-label">Conclusiones</label>';
                  $html .= '<textarea name="conact" id="conact'.$id.'" class="form-control"></textarea>';
                $html .= '</div>';
                $html .= '<div class="form-group col-md-12 mb-3">';
                  $html .= '<label for="coorusu'.$id.'" class="form-label">Coordinador</label>';
                  $html .= '<select name="coorusu" id="coorusu'.$id.'" class="form-select">';
                    foreach ($dcor as $dc) {
                      $html .= '<option value="'.$dc['idusu'].'">'.$dc['nomusu'].'</option>';
                    }
                  $html .= '</select>';
                $html .= '</div>';
              $html .= '</div>';
              $html .= '<input type="hidden" name="idusu" value="'.$id.'">';
              $html .= '<input type="hidden" name="idfic" value="'.$fic.'">';
              $html .= '<input type="hidden" name="ope" value="InAct">';
            $html .= '</div>';
            $html .= '<div class="modal-footer">';
              $html .= '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>';
              $html .= '<button type="submit" class="btn btn-primary">Guardar</button>';
            $html .= '</div>';
          $html .= '</div>';
        $html .= '</form>';
      $html .= '</div>';
    $html .= '</div>';
    return $html;
  }

  function modalHistorial($id, $nom, $fic, $nomfic, $pg, $hist, $llam) {
    $html = '';
    $html .= '<div class="modal fade" id="modhisina'.$id.'" tabindex="-1" aria-hidden="true">';
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

  function modalCarImg($id, $nom, $pg) {
    require_once("models/macta.php");
    $macta = new Macta();
    $imagenes = $macta->obtenerImagenesUsuario($id);
    $txt = '';
    $txt .= '<div class="modal fade" id="mdcgImg'.$id.'" tabindex="-1" aria-hidden="true">';
      $txt .= '<div class="modal-dialog">';
        $txt .= '<div class="modal-content">';
          $txt .= '<div class="modal-header">';
            $txt .= '<h2 class="modal-title fs-5">Registro de Inasistencia</h2>';
            $txt .= '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>';
          $txt .= '</div>';
          $txt .= '<form action="home.php?pg='.$pg.'" method="POST" enctype="multipart/form-data">';
            $txt .= '<div class="modal-body">';
              $txt .= $nom.'<br><br>';
              $txt .= '<div class="form-group col-md-12">';
                $txt .= '<label for="arc'.$id.'">Cargar imagen de SENASOFIAPLUS</label>';
                $txt .= '<input type="file" name="arc" id="arc'.$id.'" class="form-control">';
                $txt .= '<input type="hidden" name="idusu" value="'.$id.'">';
                $txt .= '<input type="hidden" name="ope" value="cgImg">';
              $txt .= '</div>';
              if (!empty($imagenes)) {
                $txt .= '<hr><strong>Imágenes cargadas:</strong><br><div class="row">';
                foreach ($imagenes as $img) {
                  $url = 'img/inasi/'.$img['nomimg'];
                  $txt .= '<div class="col-12" style="position:relative; margin-bottom:15px;">';
                    $txt .= '<a href="'.$url.'" target="_blank" style="display:inline-block;">'.$url.'</a>';
                    $txt .= '<a href="controllers/descdsr.php?img='.$img['nomimg'].'&idusu='.$id.'&pg='.$pg.'" ';
                    $txt .= 'style="position:absolute; top:50%; right:0; transform:translateY(-50%); color:green; font-size:20px; font-weight:bold; text-decoration:none;" ';
                    $txt .= 'onclick="return confirm(\'¿Eliminar esta imagen?\')">×</a>';
                  $txt .= '</div>';
                }
                $txt .= '</div>';
              }
            $txt .= '</div>';
            $txt .= '<div class="modal-footer">';
              $txt .= '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>';
              $txt .= '<button type="submit" class="btn btn-primary">Cargar Imagen</button>';
            $txt .= '</div>';
          $txt .= '</form>';
        $txt .= '</div>';
      $txt .= '</div>';
    $txt .= '</div>';
    return $txt;
  }
?>