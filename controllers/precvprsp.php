<?php
  $rootPath = file_exists('models/premvprsp.php') ? '' : '../';
  require_once $rootPath . 'models/premvprsp.php';

  $mpresp = new Premvprsp();

  $idpresp = isset($_REQUEST['idpresp']) ? $_REQUEST['idpresp'] : NULL;
  $idaul = isset($_REQUEST['idaul']) ? $_REQUEST['idaul'] : NULL;
  $idusu = isset($_REQUEST['idusu']) ? $_REQUEST['idusu'] : NULL;
  $ndocusu = isset($_REQUEST['ndocusu']) ? trim($_REQUEST['ndocusu']) : NULL;
  $apresp = isset($_POST['apresp']) ? $_POST['apresp'] : NULL;
  $whoapr = isset($_REQUEST['whoapr']) ? $_REQUEST['whoapr'] : NULL;
  $date = date('Y-m-d H:i:s');
  $fecini = isset($_POST['fecini']) ? $_POST['fecini'] : $date;
  $fecfin = isset($_REQUEST['fecfin']) ? $_REQUEST['fecfin'] : NULL;
  $horini = isset($_POST['horini']) ? $_POST['horini'] : NULL;
  $horfin = isset($_POST['horfin']) ? $_POST['horfin'] : NULL;
  $incpre = isset($_REQUEST['incpre']) ? $_REQUEST['incpre'] : NULL;

  $ope = isset($_REQUEST['ope']) ? $_REQUEST['ope'] : NULL;
  $dtOne = NULL;

  $mpresp->setIdpresp($idpresp);
  if ($ope == 'save' && $idaul) {
    if (!$fecfin || !$horini || !$horfin) {
      echo '<script> alert("Debe indicar el día y la hora de la reserva"); window.location.href = "home.php?pg='.$pg.'"; </script>';
      exit;
    }
    $mpresp->setIdaul($idaul);
    if ($ndocusu) {
      $mpresp->setNdocusu($ndocusu);
      $usDoc = $mpresp->getUsuarioByDoc();
      if (empty($usDoc)) {
        echo '<script> alert("No existe un usuario con ese número de identificación"); window.location.href = "home.php?pg='.$pg.'"; </script>';
        exit;
      }
      $idusuSol = $usDoc[0]['idusu'];
    } else {
      $idusuSol = isset($_SESSION['idusu']) ? $_SESSION['idusu'] : 1;
    }
    $mpresp->setIdusu($idusuSol);
    $mpresp->setFecini($fecini);
    $mpresp->setFecfin($fecfin);
    $mpresp->setHorini($horini);
    $mpresp->setHorfin($horfin);
    $mpresp->setIncpre(1);
    $eventApro = $mpresp->verOtherSols();
    $sess_idusu = isset($_SESSION['idusu']) ? $_SESSION['idusu'] : 1;
    if (empty($eventApro) || (isset($eventApro) && $eventApro[0]['idusu'] == $sess_idusu)) {
      if ($idpresp) $mpresp->edit();
      else $mpresp->save();
    } else {
      echo '<script> alert("Espacio ya ocupado"); </script>';
    }
    echo '<script> window.location.href = "home.php?pg='.$pg.'"; </script>';
  }

  if ($ope == 'aprSpace' || $ope == 'noApro' && $idpresp && $idusu && $idaul && $whoapr && $fecfin) {
    $mpresp->setIdusu($idusu);
    $mpresp->setIdaul($idaul);
    $mpresp->setFecfin($fecfin);
    $mpresp->setWhoapr($whoapr);
    if ($ope == 'aprSpace') {
      $mpresp->setApresp(1);
    } else if ($ope == 'noApro') {
      $mpresp->setApresp(0);
    }
    $mpresp->editApro();
    $dtEsp = $mpresp->getOne();
    $mpresp->setApresp(0);
    $mpresp->setFecfin($dtEsp[0]['fecfin']);
    $mpresp->setHorini($dtEsp[0]['horini']);
    $mpresp->setHorfin($dtEsp[0]['horfin']);
    $mpresp->editAprEvents();
    echo '<script> window.location.href = "home.php?pg='.$pg.'"; </script>';
  }

  if ($ope == 'eventInc' && $idpresp) {
    $mpresp->setIdpresp($idpresp);
    $mpresp->setIncpre(0);
    $mpresp->setWhoapr(isset($_SESSION['idusu']) ? $_SESSION['idusu'] : 1);
    $mpresp->setApresp(0);
    $mpresp->editIncpre();
  }

  /* if ($ope == 'edit' && $idpresp) $dtOne = $mpresp->getOne(); */
  if ($ope == 'del' && $idpresp) {
    $mpresp->del();
    echo '<script> window.location.href = "home.php?pg='.$pg.'"; </script>';
  } 

  if ($ope == 'dessol' && $idusu && $idaul) {
    $mpresp->setIdusu($idusu);
    $mpresp->setIdaul($idaul);
    $mpresp->delSol();
    echo '<script> window.location.href = "home.php?pg='.$pg.'"; </script>';
  }

  $dtAuls = $mpresp->getAulEsp();
  $cnt = $mpresp->getConteos();

  // Cancelacion automatica si no se toma alguna accion por el admin
  if (!function_exists('timeOff')) {
  function timeOff($idpresp) {
    $mpresp = new Premvprsp();
    $mpresp->setIdpresp($idpresp);
    $oneEvent = $mpresp->getOne();
    try {
      if ($oneEvent) {
        $eventFecini = new DateTime($oneEvent[0]['fecini']);
        $timeOff = new DateTime();
        $timeOff->format('Y-m-d');
        if ($eventFecini >= $timeOff) {
          $mpresp->setIdusu($oneEvent[0]['idusu']);
          $mpresp->setIdaul($oneEvent[0]['idaul']);
          $mpresp->setWhoapr(isset($_SESSION['idusu']) ? $_SESSION['idusu'] : 1);
          $mpresp->setApresp(0);
          $mpresp->editApro();
        }
      } else {
        echo 'No se encuentra el evento';
      }
    } catch (Exception $e) {
      echo 'Error: '.$e->getMessage();
      exit;
    }
  }
  }

  if (!function_exists('spaceModal')) {
  function spaceModal($idaul, $nomaul, $idpresp, $pg) {
    $mpresp = new Premvprsp();
    $mpresp->setIdpresp($idpresp);
    $mpresp->setIdaul($idaul);
    $dtOne = $mpresp->getOne();
    $html = '';
    $html .= '<div class="modal fade" id="space'.$idaul.'" tabindex="-1" aria-labelledby="space'.$idaul.'Label" aria-hidden="true">
              <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content shadow-lg border-0 rounded-3 overflow-hidden">
                  <div class="modal-header bg-dark text-white border-0 px-3 px-sm-4 py-3">
                    <h5 class="modal-title fs-5 d-flex align-items-center gap-2"><i class="fa-solid fa-pen-to-square text-warning"></i> '.htmlspecialchars($nomaul).'</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body p-3 p-sm-4">
                    <form method="POST" action="home.php?pg='.$pg.'">
                      <div class="row g-3">
                        <div class="form-group col-12 col-sm-4">
                          <label for="fecfin_'.$idaul.'" class="form-label fw-bold small text-muted">Fecha *</label>
                          <input type="date" id="fecfin_'.$idaul.'" name="fecfin" class="form-control" value="'; if ($dtOne) $html .= htmlspecialchars($dtOne[0]['fecfin']); $html .= '" required>
                        </div>
                        <div class="form-group col-6 col-sm-4">
                          <label for="horini_'.$idaul.'" class="form-label fw-bold small text-muted">Hora inicio *</label>
                          <input type="time" id="horini_'.$idaul.'" name="horini" class="form-control" value="'; if ($dtOne) $html .= htmlspecialchars($dtOne[0]['horini']); $html .= '" required>
                        </div>
                        <div class="form-group col-6 col-sm-4">
                          <label for="horfin_'.$idaul.'" class="form-label fw-bold small text-muted">Hora fin *</label>
                          <input type="time" id="horfin_'.$idaul.'" name="horfin" class="form-control" value="'; if ($dtOne) $html .= htmlspecialchars($dtOne[0]['horfin']); $html .= '" required>
                        </div>
                        <div class="form-group col-12 mt-4 d-flex justify-content-end gap-2 flex-column-reverse flex-sm-row">
                          <input type="hidden" name="idpresp" value="'; if ($dtOne) $html .= htmlspecialchars($dtOne[0]['idpresp']); $html .= '">
                          <input type="hidden" name="idaul" value="'.$idaul.'">
                          <input type="hidden" name="ope" value="save">
                          <button type="button" class="btn btn-secondary w-100 w-sm-auto" data-bs-dismiss="modal">Cancelar</button>
                          <button type="submit" class="btn btn-primary d-inline-flex align-items-center justify-content-center gap-1 w-100 w-sm-auto"><i class="fa-solid fa-paper-plane"></i> Solicitar</button>
                        </div>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
            </div>';
    $html .= '<script>
              const fecini_'.$idaul.' = document.getElementById("fecfin_'.$idaul.'");
              if (fecini_'.$idaul.') {
                fecini_'.$idaul.'.setAttribute("min", "'.date('Y-m-d').'");
              }
            </script>';
    return $html;
  }
  }

  if (!function_exists('solEvent')) {
  function solEvent($idaul, $nomaul, $pg) {
    $mpresp = new Premvprsp();
    $html = '';
    $html .= '<div class="modal fade" id="solE'.$idaul.'" tabindex="-1" aria-labelledby="solE'.$idaul.'Label" aria-hidden="true">
              <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content shadow-lg border-0 rounded-3 overflow-hidden">
                  <div class="modal-header bg-dark text-white border-0 px-3 px-sm-4 py-3">
                    <h5 class="modal-title fs-5 d-flex align-items-center gap-2"><i class="fa-solid fa-calendar-plus text-success"></i> '.htmlspecialchars($nomaul).'</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body p-3 p-sm-4">
                    <form method="POST" action="home.php?pg='.$pg.'">
                      <div class="row g-3">
                        <div class="form-group col-12 col-sm-4">
                          <label for="sol_fecfin_'.$idaul.'" class="form-label fw-bold small text-muted">Fecha *</label>
                          <input type="date" id="sol_fecfin_'.$idaul.'" name="fecfin" class="form-control" required min="'.date('Y-m-d').'">
                        </div>
                        <div class="form-group col-6 col-sm-4">
                          <label for="sol_horini_'.$idaul.'" class="form-label fw-bold small text-muted">Hora inicio *</label>
                          <input type="time" id="sol_horini_'.$idaul.'" name="horini" class="form-control" required>
                        </div>
                        <div class="form-group col-6 col-sm-4">
                          <label for="sol_horfin_'.$idaul.'" class="form-label fw-bold small text-muted">Hora fin *</label>
                          <input type="time" id="sol_horfin_'.$idaul.'" name="horfin" class="form-control" required>
                        </div>
                        <div class="form-group col-12 mt-4 d-flex justify-content-end gap-2 flex-column-reverse flex-sm-row">
                          <input type="hidden" name="idaul" value="'.$idaul.'">
                          <input type="hidden" name="ope" value="save">
                          <button type="button" class="btn btn-secondary w-100 w-sm-auto" data-bs-dismiss="modal">Cancelar</button>
                          <button type="submit" class="btn btn-success d-inline-flex align-items-center justify-content-center gap-1 w-100 w-sm-auto"><i class="fa-solid fa-paper-plane"></i> Solicitar</button>
                        </div>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
            </div>';
    return $html;
  }
  }

  if (!function_exists('calPorEsp')) {
  function calPorEsp($idaul, $nomaul, $pg) {
    $mpresp = new Premvprsp();
    $month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('n');
    $year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');
    if ($month < 1 || $month > 12) $month = (int)date('n');
    if ($year < 2020 || $year > 2030) $year = (int)date('Y');

    $firstDay = mktime(0, 0, 0, $month, 1, $year);
    $totDays = date('t', $firstDay);
    $currentDay = date('j');
    $currentMonth = date('n');
    $currentYear = date('Y');
    $dayWeek = date('w', $firstDay);
    // Ajustar para que la semana empiece en Lunes (0 = Lun ... 6 = Dom)
    $dayWeek = ($dayWeek == 0) ? 6 : $dayWeek - 1;
    $week = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];
    $months = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

    $prevMonth = $month - 1;
    $prevYear = $year;
    if ($prevMonth < 1) {
      $prevMonth = 12;
      $prevYear--;
    }
    $nextMonth = $month + 1;
    $nextYear = $year;
    if ($nextMonth > 12) {
      $nextMonth = 1;
      $nextYear++;
    }

    $cal = '';
    $cal .= '<div class="calendar-container">
              <div class="calendar-header">
                <div class="calendar-title">
                  <span class="month-year">'.$months[$month - 1] . ' ' . $year.'</span>
                </div>
                <div class="calendar-nav">
                  <a href="home.php?pg='.$pg.'&month='.$prevMonth.'&year='.$prevYear.'" class="btn-cal-nav"><i class="fa-solid fa-chevron-left me-1"></i> Anterior</a>
                  <a href="home.php?pg='.$pg.'&month='.date('n').'&year='.date('Y').'" class="btn-cal-nav">Hoy</a>
                  <a href="home.php?pg='.$pg.'&month='.$nextMonth.'&year='.$nextYear.'" class="btn-cal-nav">Siguiente <i class="fa-solid fa-chevron-right ms-1"></i></a>
                </div>
              </div>
              <div class="calendar-week">';
      foreach ($week as $day):
        $cal .= '<div class="cal-col-header"><span class="d-none d-sm-inline">'.$day.'</span><span class="d-sm-none">'.mb_substr($day, 0, 1).'</span></div>';
      endforeach;
      $cal .= '</div>
              <div class="calendar-days">';
      for ($i = 0; $i < $dayWeek; $i++):
        $cal .= '<div class="day empty"></div>';
      endfor;
      for ($day = 1; $day <= $totDays; $day++):
        $isPast = ($year < $currentYear) || ($year == $currentYear && $month < $currentMonth) || ($year == $currentYear && $month == $currentMonth && $day < $currentDay);
        $isCurrent = ($year == $currentYear && $month == $currentMonth && $day == $currentDay);
        $mStr = ($month < 10) ? '0'.$month : $month;
        $dStr = ($day < 10) ? '0'.$day : $day;
        $dateFecfin = $year.'-'.$mStr.'-'.$dStr;
        $mpresp->setFecfin($dateFecfin);
        $mpresp->setIdaul($idaul);
        $hasEvent = $mpresp->eventOccuped();
        $class = 'day ';
        if ($isPast) $class .= ' past ';
        if ($isCurrent) $class .= ' current ';
        if ($hasEvent) $class .= ' event ';
        if (!$isPast) $class .= ' clickable ';

        $titleTooltip = $day . ' de ' . $months[$month - 1] . ' ' . $year;
        if ($hasEvent) {
          $titleTooltip .= ' - Ocupado: ';
          $toolParts = [];
          foreach ($hasEvent as $h) {
            $toolParts[] = $h['unomusu'] . ' (' . $h['horini'] . '-' . $h['horfin'] . ')';
          }
          $titleTooltip .= implode(', ', $toolParts);
        } else {
          $titleTooltip .= ($isPast) ? ' - Fecha pasada' : ' - Disponible (clic para agendar)';
        }

        $cal .= '<div class="'.$class.'" title="'.htmlspecialchars($titleTooltip).'"';
        if (!$isPast) {
          $cal .= ' onclick="abrirSol(\''.$dateFecfin.'\', \''.$idaul.'\', \''.addslashes($nomaul).'\')" style="cursor:pointer;"';
        }
        $cal .= '>
                  <div class="day-number">'.$day.'</div>';

        if ($hasEvent) {
          // Vista Desktop (>= 768px): datos textuales del evento
          $cal .= '<div class="d-none d-md-block w-100 cal-event-box">';
          foreach ($hasEvent as $hs) {
            $cal .= '<div class="cal-event-desc">';
            $cal .= '<span class="fw-semibold text-truncate d-block" style="font-size: 11px;">'.htmlspecialchars($hs['unomusu']).'</span>';
            $cal .= '<span class="text-muted d-block" style="font-size: 10px;"><i class="fa-regular fa-clock me-1 text-warning"></i>'.$hs['horini'].'-'.$hs['horfin'].'</span>';
            $dateCurrent = date('Y-m-d');
            if (isset($_SESSION['idper']) && $_SESSION['idper'] == 29 && $hs['fecfin'] > $dateCurrent) {
              $cal .= '<a href="home.php?pg='.$pg.'&idpresp='.$hs['idpresp'].'&ope=eventInc" class="btn-incumplido" onclick="event.stopPropagation();">Incump.</a>';
            }
            $cal .= '</div>';
          }
          $cal .= '</div>';

          // Vista Móvil (< 768px): Pill compacto que nunca ensancha la columna
          $cal .= '<div class="d-md-none text-center w-100 mt-auto">';
          $cal .= '<span class="cal-mini-badge" style="background-color: #ffe082; color: #8a6d00; border-radius: 4px; padding: 1px 3px; font-size: 9px; font-weight: 600; display: inline-flex; align-items: center; justify-content: center; max-width: 100%; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">';
          $cal .= '<i class="fa-solid fa-circle text-danger me-1" style="font-size: 5px;"></i><span class="d-none d-sm-inline">'.$hasEvent[0]['horini'].'</span><span class="d-sm-none">Ocup.</span>';
          $cal .= '</span>';
          $cal .= '</div>';
        } else {
          // Libre / Disponible
          if (!$isPast) {
            $cal .= '<div class="d-none d-md-block w-100 mt-auto text-center">';
            $cal .= '<span class="text-success small fw-semibold" style="font-size: 10px;"><i class="fa-solid fa-check me-1" style="font-size: 9px;"></i>Libre</span>';
            $cal .= '</div>';
            $cal .= '<div class="d-md-none text-center w-100 mt-auto">';
            $cal .= '<span class="cal-dot-libre" style="display:inline-block; width:5px; height:5px; border-radius:50%; background-color:#22c55e;"></span>';
            $cal .= '</div>';
          }
        }

        $cal .= '</div>';
      endfor;
      $cal .= '</div>
            </div>
            <style>
              .calendar-container {
                background: #ffffff;
                width: 100%;
                max-width: 100%;
                border-radius: 12px;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
                padding: 16px;
                box-sizing: border-box;
                overflow: hidden;
              }

              .calendar-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                flex-wrap: wrap;
                gap: 8px;
                margin-bottom: 12px;
                padding-bottom: 10px;
                border-bottom: 1px solid #f1f5f9;
              }

              .calendar-title {
                font-size: 1.2rem;
                font-weight: 700;
                color: #0b1c30;
              }

              .calendar-nav {
                display: flex;
                align-items: center;
                gap: 6px;
                flex-wrap: wrap;
              }

              .calendar-nav a.btn-cal-nav {
                text-decoration: none;
                padding: 5px 12px;
                background-color: #006b29;
                color: #ffffff;
                border-radius: 6px;
                font-size: 0.8125rem;
                font-weight: 500;
                display: inline-flex;
                align-items: center;
                transition: all 0.2s ease;
              }

              .calendar-nav a.btn-cal-nav:hover {
                background-color: #005320;
                color: #ffffff;
              }

              .calendar-week {
                display: grid;
                grid-template-columns: repeat(7, minmax(0, 1fr));
                text-align: center;
                font-weight: 600;
                font-size: 0.8125rem;
                color: #3e4a3d;
                margin-bottom: 6px;
                padding: 6px 0;
                background: #f8fafc;
                border-radius: 6px;
                gap: 4px;
              }

              .calendar-days {
                display: grid;
                grid-template-columns: repeat(7, minmax(0, 1fr));
                gap: 4px;
                width: 100%;
                box-sizing: border-box;
              }

              .day {
                padding: 6px 4px;
                border-radius: 6px;
                text-align: center;
                min-height: 75px;
                background-color: #ffffff;
                border: 1px solid #e2e8f0;
                position: relative;
                box-sizing: border-box;
                display: flex;
                flex-direction: column;
                justify-content: flex-start;
                align-items: center;
                transition: all 0.15s ease;
                min-width: 0;
                overflow: hidden;
              }

              .day.empty {
                background-color: transparent;
                border-color: transparent;
                pointer-events: none;
              }

              .day.past {
                background-color: #f8fafc;
                color: #94a3b8;
                border-color: #f1f5f9;
              }

              .day.current {
                background-color: #eff6ff;
                border: 2px solid #3b82f6;
                font-weight: bold;
              }

              .day.event {
                background-color: #fffbeb !important;
                border-color: #fde68a !important;
              }

              .cal-event-desc {
                background: rgba(255, 255, 255, 0.7);
                border-radius: 4px;
                padding: 2px 4px;
                margin-top: 2px;
                border-left: 2px solid #f59e0b;
                text-align: left;
              }

              .btn-incumplido {
                background: #dc2626;
                padding: 1px 4px;
                font-size: 9px;
                font-weight: 600;
                color: #fff;
                border-radius: 3px;
                display: inline-block;
                text-decoration: none;
                margin-top: 2px;
              }

              .btn-incumplido:hover {
                background: #b91c1c;
                color: #fff;
              }

              .day-number {
                font-size: 0.9rem;
                font-weight: 600;
                margin-bottom: 2px;
                line-height: 1;
              }

              .day.clickable {
                cursor: pointer;
              }

              .day.clickable:hover {
                transform: translateY(-2px);
                box-shadow: 0 3px 8px rgba(0, 107, 41, 0.15);
                border-color: #006b29;
              }

              .month-year {
                color: #0b1c30;
              }

              @media (max-width: 767.98px) {
                .calendar-container {
                  padding: 8px 6px;
                  border-radius: 8px;
                }
                .calendar-header {
                  margin-bottom: 8px;
                  padding-bottom: 6px;
                }
                .calendar-title {
                  font-size: 0.95rem;
                }
                .calendar-nav a.btn-cal-nav {
                  padding: 4px 8px;
                  font-size: 0.75rem;
                }
                .calendar-week {
                  padding: 4px 0;
                  margin-bottom: 4px;
                  font-size: 0.7rem;
                  gap: 2px;
                }
                .calendar-days {
                  gap: 2px;
                }
                .day {
                  min-height: 48px;
                  padding: 2px 1px;
                  border-radius: 4px;
                }
                .day-number {
                  font-size: 11px;
                }
              }

              @media (max-width: 380px) {
                .calendar-container {
                  padding: 6px 4px;
                }
                .calendar-header {
                  flex-direction: column;
                  align-items: flex-start;
                  gap: 4px;
                }
                .calendar-nav {
                  width: 100%;
                  justify-content: space-between;
                }
                .calendar-nav a.btn-cal-nav {
                  padding: 3px 6px;
                  font-size: 0.7rem;
                }
                .day {
                  min-height: 44px;
                }
                .day-number {
                  font-size: 10px;
                }
              }
            </style>';
    return $cal;
  }
  }

  if (!function_exists('viewSols')) {
  function viewSols($idaul, $nomaul, $pg) {
    $mpresp = new Premvprsp();
    $mpresp->setIdaul($idaul);
    $dtSols = $mpresp->getSolsAul();
    $txt = '';
    $txt .= '<div class="modal fade" id="ocu'.$nomaul.'" tabindex="-1" role="dialog" aria-labelledby="ocu'.$nomaul.'CenterTitle" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title" id="ocu'.$nomaul.'LongTitle">'.$nomaul.' Fecha '.$dtSols[0]['fecfin'].'</h5>
                    </div>
                    <form action="home.php?pg='.$pg.'" method="post" class="formModal">
                      <div class="modal-body">
                      <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
              <style>
                .modal.fade .modal-content .modal-header h5 {
                  text-transform: uppercase;
                  justify-items: center !important;
                }

                .modal.fade .modal-content .modal-body .row {
                  display: flex;
                  gap: 10px;
                  justify-content: center;
                }

                .modal.fade .modal-content .modal-body .row .form-group {
                  box-shadow: 0 0 2px 2px rgba(0, 0, 0, 0.4);
                  padding: 10px;
                  border-radius: 1rem;
                }

                .modal.fade .modal-content .modal-body .row .form-group p {
                  font-size: 12px;
                }

                .modal.fade .modal-content .modal-body .row .form-group a {
                  background: #000;
                  color: #fff;
                  text-transform: uppercase;
                  padding: 5px 15px;
                  border-radius: 1rem;
                }

                .modal.fade .modal-content .modal-footer button {
                  justify-items: center !important;
                }
              </style>';
    return $txt;
  }
  }