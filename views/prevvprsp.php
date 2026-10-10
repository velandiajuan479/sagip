<?php
$ctrlPath = file_exists('controllers/precvprsp.php') ? 'controllers/precvprsp.php' : '../controllers/precvprsp.php';
require_once $ctrlPath;
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!isset($pg)) {
    $pg = isset($_GET['pg']) ? $_GET['pg'] : 2402;
}
if (!function_exists('titulo2')) {
    $optPath = file_exists('controllers/optimg.php') ? 'controllers/optimg.php' : '../controllers/optimg.php';
    if (file_exists($optPath)) require_once $optPath;
}
?>

<div class="conte">
  <?php echo titulo2("<i class='".($icono ?? 'fa fa-solid fa-file-lines')."'></i> Préstamo de Espacios Especializados", 2); ?>

  <?php if(isset($cnt) && $cnt): ?>
  <!-- Dashboard de Métricas (Tarjetas KPI Estilo vvidgen.php) -->
  <div class="row g-3 g-md-4 my-2 my-md-4">
    <!-- Aulas Especiales -->
    <div class="col-12 col-sm-6 col-xl-3">
      <div class="kpi-card h-100 d-flex flex-column justify-content-between">
        <div class="d-flex align-items-center justify-content-between">
          <div>
            <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #3c627f;">Espacios</span>
            <div class="d-flex align-items-baseline gap-2 mt-2">
              <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #0b1c30;"><?= $cnt[0]['aulas']; ?></span>
              <span style="font-size: 13px; color: #3e4a3d;">aulas</span>
            </div>
          </div>
          <div class="kpi-icon-box" style="background-color: #b5dcfe; color: #3c627f;">
            <i class="fa-solid fa-chalkboard-user fs-4"></i>
          </div>
        </div>
        <div class="kpi-footer-strip">
          <span class="fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 11px; color: #3c627f;">
            <i class="fa-solid fa-layer-group" style="font-size: 12px;"></i> Especializados
          </span>
          <span style="font-size: 11px; color: #3e4a3d;">Habilitados</span>
        </div>
      </div>
    </div>

    <!-- Solicitudes Pendientes -->
    <div class="col-12 col-sm-6 col-xl-3">
      <div class="kpi-card h-100 d-flex flex-column justify-content-between">
        <div class="d-flex align-items-center justify-content-between">
          <div>
            <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #8a6d00;">Pendientes</span>
            <div class="d-flex align-items-baseline gap-2 mt-2">
              <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #0b1c30;"><?= $cnt[0]['pendientes']; ?></span>
              <span style="font-size: 13px; color: #3e4a3d;">solicitudes</span>
            </div>
          </div>
          <div class="kpi-icon-box" style="background-color: #ffe082; color: #8a6d00;">
            <i class="fa-solid fa-hourglass-half fs-4"></i>
          </div>
        </div>
        <div class="kpi-footer-strip">
          <span class="fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 11px; color: #8a6d00;">
            <i class="fa-solid fa-clock" style="font-size: 12px;"></i> Por revisar
          </span>
          <span style="font-size: 11px; color: #3e4a3d;">En cola</span>
        </div>
      </div>
    </div>

    <!-- Reservas Aprobadas -->
    <div class="col-12 col-sm-6 col-xl-3">
      <div class="kpi-card h-100 d-flex flex-column justify-content-between">
        <div class="d-flex align-items-center justify-content-between">
          <div>
            <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #006b29;">Aprobadas</span>
            <div class="d-flex align-items-baseline gap-2 mt-2">
              <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #006b29;"><?= $cnt[0]['aprobadas']; ?></span>
              <span style="font-size: 13px; color: rgba(0, 107, 41, 0.8);">reservas</span>
            </div>
          </div>
          <div class="kpi-icon-box" style="background-color: #7afd88; color: #006b24;">
            <i class="fa-solid fa-circle-check fs-4"></i>
          </div>
        </div>
        <div class="kpi-footer-strip">
          <span class="fw-semibold d-inline-flex align-items-center gap-2" style="font-size: 11px; color: #006b29;">
            <span class="pulse-dot"></span> Autorizadas
          </span>
          <span style="font-size: 11px; color: #3e4a3d;">Vigentes</span>
        </div>
      </div>
    </div>

    <!-- Reservas Rechazadas -->
    <div class="col-12 col-sm-6 col-xl-3">
      <div class="kpi-card h-100 d-flex flex-column justify-content-between">
        <div class="d-flex align-items-center justify-content-between">
          <div>
            <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #ba1a1a;">Rechazadas</span>
            <div class="d-flex align-items-baseline gap-2 mt-2">
              <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #0b1c30;"><?= $cnt[0]['rechazadas']; ?></span>
              <span style="font-size: 13px; color: #3e4a3d;">denegadas</span>
            </div>
          </div>
          <div class="kpi-icon-box" style="background-color: #ffdad6; color: #ba1a1a;">
            <i class="fa-solid fa-ban fs-4"></i>
          </div>
        </div>
        <div class="kpi-footer-strip">
          <span class="d-inline-flex align-items-center gap-1" style="font-size: 11px; color: #ba1a1a;">
            <i class="fa-solid fa-xmark" style="font-size: 12px;"></i> No procedentes
          </span>
          <span class="fw-semibold" style="font-size: 11px; color: #3c627f;">Canceladas</span>
        </div>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <div class="inser">
    <?php if ($dtAuls) { foreach ($dtAuls as $da) { 
      $mpresp->setIdaul($da['idaul']);
      $userProfile = isset($_SESSION['idper']) ? $_SESSION['idper'] : 0;
      $userId = isset($_SESSION['idusu']) ? $_SESSION['idusu'] : 0;
      $solicited = ($userProfile == 29) ? $mpresp->getSpaceSolicited() : null;
      if ($userProfile != 29) {
        $mpresp->setIdusu($userId);
        $usuSol = $mpresp->usuSolicited();
      }
    ?>
      <div class="card border-0 shadow-sm rounded-3 p-3 p-sm-4 mb-4 bg-white classroom-card">
        <!-- Encabezado del Ambiente Especializado -->
        <div class="border-bottom pb-3 mb-3 d-flex justify-content-between align-items-sm-center flex-column flex-sm-row gap-2">
          <div>
            <h2 class="fs-4 fs-md-3 fw-bold text-dark mb-1">
              <i class="fa-solid fa-chalkboard text-success me-2"></i> <?= htmlspecialchars($da['nomaul']); ?>
            </h2>
            <span class="badge bg-light text-muted border">Ambiente Especializado</span>
          </div>
          <?php if ($userProfile != 29) { ?>
            <div class="w-100 w-sm-auto">
              <button type="button" class="btn btn-success d-inline-flex align-items-center justify-content-center gap-2 w-100 w-sm-auto" data-bs-toggle="modal" data-bs-target="#solE<?=$da['idaul'];?>">
                <i class="fa-solid fa-plus"></i> Solicitar Reserva
              </button>
              <?= solEvent($da['idaul'], $da['nomaul'], $pg); ?>
            </div>
          <?php } ?>
        </div>

        <!-- Calendario del Espacio (Completamente Responsivo sin Scroll Horizontal) -->
        <div class="p-2 p-sm-3 border rounded-3 bg-white mb-4" style="overflow: hidden;">
          <?php echo calPorEsp($da['idaul'], $da['nomaul'], $pg); ?>
        </div>

        <!-- Toolbar y Listado de Reservas en Tarjetas / DataTables -->
        <div class="border-top pt-3">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold text-dark mb-0 fs-6 fs-md-5">
              <i class="fa-solid fa-clipboard-list text-primary me-2"></i>
              <?= ($userProfile == 29) ? 'Reservas y Solicitudes de ' . htmlspecialchars($da['nomaul']) : 'Mis Solicitudes de Reserva'; ?>
            </h5>
          </div>

          <?php if ($userProfile == 29) { 
            if ($solicited) { ?>
              <!-- Toolbar de Control y Filtros de la Tabla -->
              <div class="filter-toolbar-container mb-3">
                <div class="toolbar-left-group">
                  <div class="toolbar-export-buttons">
                    <button type="button" class="toolbar-export-btn" title="Copiar al portapapeles" onclick="triggerDTExport('copy')">
                      <i class="fa-solid fa-copy"></i>
                    </button>
                    <button type="button" class="toolbar-export-btn" title="Descargar CSV" onclick="triggerDTExport('csv')">
                      <i class="fa-solid fa-file-csv"></i>
                    </button>
                    <button type="button" class="toolbar-export-btn" title="Exportar a Excel" onclick="triggerDTExport('excel')">
                      <i class="fa-solid fa-file-excel"></i>
                    </button>
                  </div>
                </div>
                <div class="toolbar-search-sort-group">
                  <div class="toolbar-search-box">
                    <i class="fa-solid fa-magnifying-glass icoldiz"></i>
                    <input type="text" id="customVideoSearch" placeholder="Buscar solicitud..." onkeyup="syncCustomSearch(this.value)">
                  </div>
                  <div class="toolbar-secondary-controls">
                    <select class="toolbar-select-sort" id="customVideoSort" onchange="syncCustomSort(this.value)">
                      <option value="desc">Descendente</option>
                      <option value="asc">Ascendente</option>
                    </select>
                    <div class="toolbar-view-toggle">
                      <button type="button" class="toolbar-view-btn active" id="btnViewGrid" title="Vista Cuadrícula" onclick="switchCardView('grid')">
                        <i class="fa-solid fa-grip"></i>
                      </button>
                      <button type="button" class="toolbar-view-btn" id="btnViewList" title="Vista Lista" onclick="switchCardView('list')">
                        <i class="fa-solid fa-bars"></i>
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              <!-- DataTable en Formato Tarjetas -->
              <table class="table table-striped table-vid-cards" style="width:100%">
                <thead>
                  <tr>
                    <th>Solicitudes</th>
                  </tr>
                </thead>
                <tbody>
                <?php foreach ($solicited as $so) { 
                  $esPendiente = ($so['apresp'] == '2');
                  $esAprobada = ($so['apresp'] == '1');
                ?>
                  <tr data-order="<?=$so['idpresp'];?>">
                    <td data-search="<?=$so['idpresp'];?> <?=$so['nomusu'];?> <?=$so['ndocusu'];?> <?=$so['nomper'];?> <?=$so['fecfin'];?> <?=$so['horini'];?>">
                      <div class="card h-100 shadow-sm border-0 vid-card">
                        <!-- Preview Box -->
                        <div class="vid-preview-box">
                          <div class="d-flex flex-column align-items-center justify-content-center text-white-50">
                            <i class="fa-solid fa-chalkboard-user fa-3x mb-2 text-white opacity-75"></i>
                            <small class="text-uppercase fw-semibold" style="font-size: 0.72rem; letter-spacing: 0.5px;"><?= htmlspecialchars($da['nomaul']); ?></small>
                          </div>

                          <!-- Badges Superiores -->
                          <div class="vid-badges-top">
                            <?php if ($esPendiente) { ?>
                              <span class="badge bg-warning text-dark shadow-sm px-2 py-1">
                                <i class="fa-solid fa-hourglass-half me-1"></i> Pendiente
                              </span>
                            <?php } elseif ($esAprobada) { ?>
                              <span class="badge bg-success shadow-sm px-2 py-1">
                                <i class="fa-solid fa-circle-check me-1"></i> Aprobada
                              </span>
                            <?php } else { ?>
                              <span class="badge bg-danger shadow-sm px-2 py-1">
                                <i class="fa-solid fa-ban me-1"></i> No Aprobada
                              </span>
                            <?php } ?>

                            <span class="badge vid-badge-blur px-2 py-1 shadow-sm">
                              <i class="fa-solid fa-calendar-day me-1 text-info"></i> <?=$so['fecfin'];?>
                            </span>
                          </div>

                          <!-- Badges Inferiores -->
                          <div class="vid-badges-bottom">
                            <span class="badge vid-badge-blur px-2 py-1">
                              <i class="fa-solid fa-clock me-1 text-warning"></i> <?=$so['horini'];?> - <?=$so['horfin'];?>
                            </span>
                            <span class="badge vid-badge-blur px-2 py-1">
                              <i class="fa-solid fa-id-card me-1 text-primary"></i> <?=$so['ndocusu'];?>
                            </span>
                          </div>
                        </div>

                        <!-- Cuerpo de la Tarjeta -->
                        <div class="card-body p-3 d-flex flex-column justify-content-between">
                          <div>
                            <h6 class="fw-bold text-dark mb-2 text-truncate" title="<?=htmlspecialchars($so['nomusu']);?>" style="font-size: 1.05rem;">
                              <span class="text-primary">#<?=$so['idpresp'];?></span> - <?=htmlspecialchars($so['nomusu']);?>
                            </h6>

                            <div class="p-2 mb-2 rounded-3 bg-light border border-light-subtle" style="font-size: 0.8rem;">
                              <div class="text-muted small"><strong>Rol:</strong> <?= htmlspecialchars($so['nomper']); ?></div>
                              <div class="text-muted small"><strong>Documento:</strong> <?= htmlspecialchars($so['ndocusu']); ?></div>
                            </div>
                          </div>
                        </div>

                        <!-- Footer con Botones de Acción -->
                        <div class="card-footer bg-white border-0 pt-0 pb-3 px-3">
                          <hr class="mt-0 mb-3 text-muted opacity-25">
                          <div class="vid-actions-bar" style="grid-template-columns: 1fr 1fr;">
                            <?php if ($esPendiente) { ?>
                              <a href="home.php?pg=<?=$pg;?>&idpresp=<?=$so['idpresp'];?>&idaul=<?=$da['idaul'];?>&idusu=<?=$so['idusu'];?>&fecfin=<?=$so['fecfin'];?>&whoapr=<?=$userId;?>&ope=aprSpace" 
                                 class="btn btn-outline-success shadow-sm d-inline-flex align-items-center justify-content-center gap-1">
                                <i class="fa-solid fa-check"></i> <span>Aprobar</span>
                              </a>
                              <a href="home.php?pg=<?=$pg;?>&idpresp=<?=$so['idpresp'];?>&idaul=<?=$da['idaul'];?>&idusu=<?=$so['idusu'];?>&fecfin=<?=$so['fecfin'];?>&whoapr=<?=$userId;?>&ope=noApro" 
                                 class="btn btn-outline-danger shadow-sm d-inline-flex align-items-center justify-content-center gap-1">
                                <i class="fa-solid fa-xmark"></i> <span>Rechazar</span>
                              </a>
                              <?= timeOff($so['idpresp']); ?>
                            <?php } else { ?>
                              <span class="badge <?= $esAprobada ? 'bg-success-subtle text-success border-success-subtle' : 'bg-danger-subtle text-danger border-danger-subtle'; ?> border py-2 text-center w-100" style="grid-column: span 2;">
                                <?= $esAprobada ? 'Reserva Confirmada' : 'Solicitud Cancelada'; ?>
                              </span>
                            <?php } ?>
                          </div>
                        </div>
                      </div>
                    </td>
                  </tr>
                <?php } ?>
                </tbody>
              </table>
            <?php } else { ?>
              <div class="alert alert-light border text-center text-muted py-4 mb-0" role="alert">
                <i class="fa-solid fa-inbox fa-3x mb-2 d-block opacity-50"></i>
                Aún no hay reservas registradas para este ambiente.
              </div>
            <?php } 
          } else { 
            if (!empty($usuSol)) { ?>
              <div class="row g-3">
                <?php foreach ($usuSol as $us) { ?>
                  <div class="col-12 col-sm-6 col-lg-4">
                    <div class="card h-100 shadow-sm border-0 vid-card p-3 bg-light">
                      <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                        <span class="badge bg-primary px-2 py-1"><i class="fa-regular fa-calendar-check me-1"></i><?=$us['fecfin'];?></span>
                        <div class="d-flex gap-1">
                          <a href="#" data-bs-toggle="modal" data-bs-target="#space<?=$da['idaul'];?>" class="btn btn-outline-warning btn-sm d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" title="Editar solicitud">
                            <i class="fa-solid fa-pen-to-square"></i>
                          </a>
                          <?= spaceModal($da['idaul'], $da['nomaul'], $us['idpresp'], $pg); ?>
                          <a href="home.php?pg=<?=$pg;?>&idaul=<?=$da['idaul'];?>&idusu=<?=$userId;?>&ope=dessol" class="btn btn-outline-danger btn-sm d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" title="Cancelar solicitud">
                            <i class="fa-solid fa-trash-can"></i>
                          </a>
                        </div>
                      </div>
                      <small class="text-muted d-block mt-2">
                        <i class="fa-regular fa-clock me-1 text-primary"></i><strong>Horario:</strong> <?=$us['horini'];?> a <?=$us['horfin'];?>
                      </small>
                    </div>
                  </div>
                <?php } ?>
              </div>
            <?php } else { ?>
              <div class="alert alert-light border text-center text-muted py-3 mb-0" role="alert">
                <i class="fa-solid fa-calendar-xmark fa-2x mb-2 d-block opacity-50"></i>
                No tiene solicitudes activas en este espacio.
              </div>
            <?php } ?>
          <?php } ?>
        </div>
      </div>
    <?php }} ?>
  </div>

  <!-- Modal de Solicitud de Espacio -->
  <div class="modal fade" id="solDateModal" tabindex="-1" aria-labelledby="solDateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content shadow-lg border-0 rounded-3 overflow-hidden">
        <div class="modal-header bg-dark text-white border-0 px-3 px-sm-4 py-3">
          <h5 class="modal-title fs-5 d-flex align-items-center gap-2" id="solDateModalLabel">
            <i class="fa-solid fa-calendar-plus text-success"></i> Aula
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-3 p-sm-4">
          <form method="POST" action="home.php?pg=<?=$pg;?>">
            <div class="row g-3">
              <div class="form-group col-12">
                <label for="solIdent" class="form-label fw-bold small text-muted">Número de identificación del solicitante *</label>
                <input type="text" id="solIdent" name="ndocusu" class="form-control" required placeholder="Ej. 1065487523">
              </div>
              <div class="form-group col-12">
                <label for="solFecha" class="form-label fw-bold small text-muted">Día de Reserva *</label>
                <input type="date" id="solFecha" name="fecfin" class="form-control" required>
              </div>
              <div class="form-group col-6 col-sm-6">
                <label for="solHorini" class="form-label fw-bold small text-muted">Hora inicio *</label>
                <input type="time" id="solHorini" name="horini" class="form-control" required>
              </div>
              <div class="form-group col-6 col-sm-6">
                <label for="solHorfin" class="form-label fw-bold small text-muted">Hora fin *</label>
                <input type="time" id="solHorfin" name="horfin" class="form-control" required>
              </div>
            </div>
            <input type="hidden" id="solIdaul" name="idaul">
            <input type="hidden" name="ope" value="save">
            <div class="modal-footer bg-light border-0 px-0 pb-0 mt-4 d-flex justify-content-end gap-2 flex-column-reverse flex-sm-row">
              <button type="button" class="btn btn-secondary w-100 w-sm-auto" data-bs-dismiss="modal">Cancelar</button>
              <button type="submit" class="btn btn-success d-inline-flex align-items-center justify-content-center gap-2 w-100 w-sm-auto">
                <i class="fa-solid fa-check"></i> Confirmar Solicitud
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>


  <script>
  function abrirSol(fecha, idaul, nomaul){
    document.getElementById('solFecha').value = fecha;
    document.getElementById('solIdaul').value = idaul;
    document.getElementById('solIdent').value = '';
    document.getElementById('solHorini').value = '';
    document.getElementById('solHorfin').value = '';
    document.getElementById('solDateModalLabel').innerHTML = '<i class="fa-solid fa-calendar-plus text-success me-2"></i> Reservar ' + nomaul;
    new bootstrap.Modal(document.getElementById('solDateModal')).show();
  }
  </script>
</div>