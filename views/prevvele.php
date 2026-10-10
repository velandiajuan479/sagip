<?php 
$ctrlPath = file_exists('controllers/precvele.php') ? 'controllers/precvele.php' : '../controllers/precvele.php';
require_once $ctrlPath;
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!isset($pg)) {
    $pg = isset($_GET['pg']) ? $_GET['pg'] : 1406;
}
if (!function_exists('titulo2')) {
    $optPath = file_exists('controllers/optimg.php') ? 'controllers/optimg.php' : '../controllers/optimg.php';
    if (file_exists($optPath)) require_once $optPath;
}
?>

<div class="conte">
  <?php echo titulo2("<i class='" . ($icono ?? 'fa-solid fa-boxes-stacked') . "'></i> Elementos", 1); ?>
  
  <div class="alert alert-info py-2 mb-4 d-flex align-items-center gap-2 border-0 shadow-sm" role="alert">
    <i class="fa-solid fa-circle-info fs-5 text-primary"></i> 
    <div>
      <strong>Control de Tránsito en Portería:</strong> Registre aquí sus bienes personales (bicicletas, portátiles, celulares, vehículos). 
      Use la estrella en la tarjeta para marcar los elementos que <strong>llevará hoy a la sede</strong>. El personal de seguridad en portería solo autorizará el ingreso de los elementos marcados como activos.
    </div>
  </div>

  <!-- Dashboard de Métricas (Tarjetas KPI Estilo vvidgen.php) -->
  <div class="row my-4 g-4">
    <!-- Total Elementos -->
    <div class="col-12 col-sm-6 col-xl-3">
      <div class="kpi-card h-100 d-flex flex-column justify-content-between">
        <div class="d-flex align-items-center justify-content-between">
          <div>
            <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #3e4a3d;">Total Elementos</span>
            <div class="d-flex align-items-baseline gap-2 mt-2">
              <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #0b1c30;"><?= number_format($statsEle['total'] ?? 0); ?></span>
              <span style="font-size: 13px; color: #3e4a3d;">registrados</span>
            </div>
          </div>
          <div class="kpi-icon-box" style="background-color: #b5dcfe; color: #3c627f;">
            <i class="fa-solid fa-boxes-stacked fs-4"></i>
          </div>
        </div>
        <div class="kpi-footer-strip">
          <span class="fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 11px; color: #3c627f;">
            <i class="fa-solid fa-warehouse" style="font-size: 12px;"></i> Inventario
          </span>
          <span style="font-size: 11px; color: #3e4a3d;">Bienes en sistema</span>
        </div>
      </div>
    </div>

    <!-- Elementos Prestados -->
    <div class="col-12 col-sm-6 col-xl-3">
      <div class="kpi-card h-100 d-flex flex-column justify-content-between">
        <div class="d-flex align-items-center justify-content-between">
          <div>
            <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #0d6efd;">Elementos Prestados</span>
            <div class="d-flex align-items-baseline gap-2 mt-2">
              <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #0b1c30;"><?= number_format($statsEle['prestados'] ?? 0); ?></span>
              <span style="font-size: 13px; color: #3e4a3d;">en préstamo</span>
            </div>
          </div>
          <div class="kpi-icon-box" style="background-color: #cfe2ff; color: #084298;">
            <i class="fa-solid fa-handshake fs-4"></i>
          </div>
        </div>
        <div class="kpi-footer-strip">
          <span class="fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 11px; color: #084298;">
            <i class="fa-solid fa-arrow-right-arrow-left" style="font-size: 12px;"></i> Préstamo Activo
          </span>
          <span style="font-size: 11px; color: #3e4a3d;">En uso</span>
        </div>
      </div>
    </div>

    <!-- Elementos Activos Hoy -->
    <div class="col-12 col-sm-6 col-xl-3">
      <div class="kpi-card h-100 d-flex flex-column justify-content-between">
        <div class="d-flex align-items-center justify-content-between">
          <div>
            <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #006b29;">Elementos Activos</span>
            <div class="d-flex align-items-baseline gap-2 mt-2">
              <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #006b29;"><?= number_format($statsEle['activos'] ?? 0); ?></span>
              <span style="font-size: 13px; color: rgba(0, 107, 41, 0.8);">hoy</span>
            </div>
          </div>
          <div class="kpi-icon-box" style="background-color: #7afd88; color: #006b24;">
            <i class="fa-solid fa-star fs-4"></i>
          </div>
        </div>
        <div class="kpi-footer-strip">
          <span class="fw-semibold d-inline-flex align-items-center gap-2" style="font-size: 11px; color: #006b29;">
            <span class="pulse-dot"></span> Autorizados en Portería
          </span>
          <span style="font-size: 11px; color: #3e4a3d;">Llevaré hoy</span>
        </div>
      </div>
    </div>

    <!-- Elementos Inactivos -->
    <div class="col-12 col-sm-6 col-xl-3">
      <div class="kpi-card h-100 d-flex flex-column justify-content-between">
        <div class="d-flex align-items-center justify-content-between">
          <div>
            <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #ba1a1a;">Elementos Inactivos</span>
            <div class="d-flex align-items-baseline gap-2 mt-2">
              <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #0b1c30;"><?= number_format($statsEle['inactivos'] ?? 0); ?></span>
              <span style="font-size: 13px; color: #3e4a3d;">no declarados</span>
            </div>
          </div>
          <div class="kpi-icon-box" style="background-color: #ffdad6; color: #ba1a1a;">
            <i class="fa-regular fa-star fs-4"></i>
          </div>
        </div>
        <div class="kpi-footer-strip">
          <span class="d-inline-flex align-items-center gap-1" style="font-size: 11px; color: #ba1a1a;">
            <i class="fa-solid fa-ban" style="font-size: 12px;"></i> No autorizados hoy
          </span>
          <span class="fw-semibold" style="font-size: 11px; color: #3c627f;">Pausados</span>
        </div>
      </div>
    </div>
  </div>

  <!-- Formulario de Registro / Edición -->
  <div class="inser">
    <form id="frmins" class="filter-toolbar-container" action="home.php?pg=<?=$pg;?>" method="POST" enctype="multipart/form-data">
      <div class="w-100">
        <h2>
          <i class="fa-solid <?= ($datOne) ? 'fa-pen-to-square text-warning' : 'fa-circle-plus text-success'; ?>"></i> 
          <?= ($datOne) ? 'Editar Información del Elemento' : 'Registrar Nuevo Elemento'; ?>
        </h2>
      </div>
      <div class="row g-3 w-100">
        <div class="form-group col-md-3">
          <label for="nomele">Nombre del Elemento *</label>
          <input type="text" name="nomele" id="nomele" maxlength="255" class="form-control" required placeholder="Ej. Bicicleta, Portátil, Celular..." value="<?php if ($datOne) echo htmlspecialchars($datOne[0]['nomele']); ?>">
        </div>
        <div class="form-group col-md-2">
          <label for="nidele">Serial, Chasis o Placa *</label>
          <input type="text" name="nidele" id="nidele" class="form-control" maxlength="255" required placeholder="Serial o Placa vehicular" value="<?php if ($datOne) echo htmlspecialchars($datOne[0]['nidele']); ?>">
        </div>
        <?php if (isset($_SESSION['idper']) && $_SESSION['idper'] == 29) { ?>
          <div class="form-group col-md-2">
            <label for="noplasena">No. Placa Sena (Almacén)</label>
            <input type="number" name="noplasena" id="noplasena" class="form-control" placeholder="Solo activo institucional" value="<?php if ($datOne) echo htmlspecialchars($datOne[0]['noplasena']); ?>">
          </div>
        <?php } ?>
        <div class="form-group col-md-2">
          <label for="marele">Marca *</label>
          <input type="text" name="marele" id="marele" class="form-control" maxlength="255" required placeholder="Ej. GW, Lenovo, Samsung" value="<?php if ($datOne) echo htmlspecialchars($datOne[0]['marele']); ?>">
        </div>
        <div class="form-group col-md-3">
          <label for="tipele">Tipo de Elemento *</label>
          <select name="tipele" id="tipele" class="form-control form-select" required>
            <option value="">Seleccione tipo...</option>
            <?php
            if (!empty($datAllTe)) {
              foreach ($datAllTe as $dtte) {
                $esSena = (stripos($dtte['nomval'], 'Sena') !== false);
                if (isset($_SESSION['idper']) && $_SESSION['idper'] != 29) {
                  if (!$esSena) { ?>
                    <option value="<?= $dtte['idval']; ?>" <?php if ($datOne && $datOne[0]['tipele'] == $dtte['idval']) echo " selected "; ?>><?= htmlspecialchars($dtte['nomval']); ?></option>
                  <?php }
                } else { ?>
                  <option value="<?= $dtte['idval']; ?>" <?php if ($datOne && $datOne[0]['tipele'] == $dtte['idval']) echo " selected "; ?>><?= htmlspecialchars($dtte['nomval']); ?></option>
                <?php }
              }
            } ?>
          </select>
        </div>
        <div class="form-group col-md-4">
          <label for="rutfot">Fotografía del Elemento</label>
          <?php 
            $fotoActual = '';
            if (isset($datOne) && !empty($datOne[0]['rutfot'])) {
              $rawFot = $datOne[0]['rutfot'];
              if (file_exists($rawFot)) {
                $fotoActual = $rawFot;
              } elseif (file_exists('fotele/' . basename($rawFot))) {
                $fotoActual = 'fotele/' . basename($rawFot);
              } elseif (file_exists('img/fele/' . basename($rawFot))) {
                $fotoActual = 'img/fele/' . basename($rawFot);
              }
              echo '<input type="hidden" name="rutfot" value="'.htmlspecialchars($rawFot).'">';
            }
          ?>
          <input class="form-control" type="file" id="rutfot" name="foto" accept="image/png,image/jpeg,image/jfif,image/gif" onchange="previewImg(this)">
          <?php if (!empty($fotoActual)) { ?>
            <div class="d-flex align-items-center gap-2 mt-2 p-1 border rounded bg-light">
              <img src="<?=$fotoActual;?>" id="imgPrev" class="rounded border" style="width: 45px; height: 45px; object-fit: cover;">
              <small class="text-muted" style="font-size: 0.75rem;">Foto actual conservada (seleccione otro archivo si desea cambiarla)</small>
            </div>
          <?php } else { ?>
            <div id="prevContainer" class="d-none align-items-center gap-2 mt-2 p-1 border rounded bg-light">
              <img id="imgPrev" class="rounded border" style="width: 45px; height: 45px; object-fit: cover;">
              <small class="text-success fw-bold" style="font-size: 0.75rem;">Nueva foto seleccionada</small>
            </div>
          <?php } ?>
        </div>
        <div class="form-group col-md-8">
          <label for="desele">Descripción Detallada *</label>
          <input type="text" name="desele" id="desele" class="form-control" placeholder="Describa características físicas (Color, estado, detalles, rayones visibles)..." value="<?php if ($datOne) echo htmlspecialchars($datOne[0]['desele']); ?>">
        </div>
        <div class="form-group col-md-12 d-flex align-items-center gap-2 pt-2">
          <button class="btn btn-success d-inline-flex align-items-center gap-2" type="submit">
            <i class="fa-solid fa-floppy-disk"></i> <?= ($datOne) ? 'Actualizar Elemento' : 'Guardar Elemento'; ?>
          </button>
          <?php if ($datOne) { ?>
            <a href="home.php?pg=<?=$pg;?>" class="btn btn-secondary d-inline-flex align-items-center gap-2">
              <i class="fa-solid fa-xmark"></i> Cancelar Edición
            </a>
          <?php } ?>
          <input type="hidden" name="opera" value="save">
          <input type="hidden" name="idele" id="idele" value="<?php if ($datOne) echo $datOne[0]['idele']; ?>">
        </div>
      </div>
    </form>
  </div>




<?php
// Separar elementos para las pestañas
$datAct = [];
$datInact = [];
if (!empty($datAll)) {
    foreach ($datAll as $d) {
        if ($d['preele'] == 1) {
            $datAct[] = $d;
        } else {
            $datInact[] = $d;
        }
    }
}
$totEle = count($datAll ?? []);

// Helper para renderizar cada tarjeta de elemento
if (!function_exists('renderElementCard')) {
function renderElementCard($d, $dtAllPr, $pg) {
    $esActivo = ($d['preele'] == 1);
    
    // Obtener ruta de la foto
    $fotoSrc = '';
    if (!empty($d['rutfot'])) {
        if (file_exists($d['rutfot'])) {
            $fotoSrc = $d['rutfot'];
        } elseif (file_exists('fotele/' . basename($d['rutfot']))) {
            $fotoSrc = 'fotele/' . basename($d['rutfot']);
        } elseif (file_exists('img/fele/' . basename($d['rutfot']))) {
            $fotoSrc = 'img/fele/' . basename($d['rutfot']);
        }
    }

    // Verificar si está en préstamo
    $enPrestamo = false;
    if (!empty($dtAllPr)) {
        foreach ($dtAllPr as $prAct) {
            if ($prAct['idele'] == $d['idele'] && $prAct['estpre'] == 1) {
                $enPrestamo = true;
                break;
            }
        }
    }
?>
    <div class="card h-100 shadow-sm border-0 vid-card">
        <!-- Preview Box del Elemento -->
        <div class="vid-preview-box" <?= !$esActivo ? 'style="filter: grayscale(0.25);"' : ''; ?>>
            <?php if (!empty($fotoSrc)) { ?>
                <img src="<?=$fotoSrc;?>" alt="<?=htmlspecialchars($d['nomele']);?>" style="width:100%; height:100%; object-fit:cover; opacity:0.88;">
            <?php } else { ?>
                <div class="d-flex flex-column align-items-center justify-content-center text-white-50">
                    <i class="fa-solid fa-boxes-stacked fa-3x mb-2 text-white opacity-75"></i>
                    <small class="text-uppercase fw-semibold" style="font-size: 0.72rem; letter-spacing: 0.5px;">Sin Fotografía</small>
                </div>
            <?php } ?>

            <!-- Badges Superiores -->
            <div class="vid-badges-top">
                <?php if ($esActivo) { ?>
                    <span class="badge bg-success shadow-sm px-2 py-1">
                        <i class="fa-solid fa-circle-check me-1"></i> Activo Hoy
                    </span>
                <?php } else { ?>
                    <span class="badge bg-danger shadow-sm px-2 py-1">
                        <i class="fa-regular fa-circle-xmark me-1"></i> Inactivo
                    </span>
                <?php } ?>
                
                <?php if (!empty($d['noplasena']) && $d['noplasena'] != '0') { ?>
                    <span class="badge vid-badge-blur px-2 py-1 shadow-sm">
                        <i class="fa-solid fa-building-columns me-1 text-info"></i> Placa: <?=$d['noplasena'];?>
                    </span>
                <?php } else { ?>
                    <span class="badge vid-badge-blur px-2 py-1 shadow-sm">
                        <i class="fa-solid fa-tag me-1 text-warning"></i> Personal
                    </span>
                <?php } ?>
            </div>

            <!-- Botón central para ver foto ampliada -->
            <?php if (!empty($fotoSrc)) { ?>
                <div class="vid-play-btn" data-bs-toggle="modal" data-bs-target="#modFot<?=$d['idele'];?>" title="Ver Fotografía" role="button">
                    <i class="fa-solid fa-eye"></i>
                </div>
            <?php } ?>

            <!-- Badges Inferiores -->
            <div class="vid-badges-bottom">
                <span class="badge vid-badge-blur px-2 py-1">
                    <i class="fa-solid fa-layer-group me-1 text-primary"></i> <?= htmlspecialchars($d['nomval']); ?>
                </span>
                <?php if ($enPrestamo) { ?>
                    <span class="badge bg-warning text-dark px-2 py-1 shadow-sm">
                        <i class="fa-solid fa-clock me-1"></i> En Préstamo
                    </span>
                <?php } ?>
            </div>
        </div>

        <!-- Cuerpo de la Tarjeta -->
        <div class="card-body p-3 d-flex flex-column justify-content-between">
            <div>
                <!-- Título del elemento con ID -->
                <h6 class="fw-bold text-dark mb-2 text-truncate" title="#<?=$d['idele'];?> - <?=htmlspecialchars($d['nomele']);?>" style="font-size: 1.05rem;">
                    <span class="<?= $esActivo ? 'text-primary' : 'text-danger'; ?>">#<?=$d['idele'];?></span> - <?=htmlspecialchars($d['nomele']);?>
                </h6>

                <!-- Caja de Detalles / Marca y Serial -->
                <div class="p-2 mb-3 rounded-3 bg-light border border-light-subtle d-flex justify-content-between align-items-center" style="font-size: 0.8rem;">
                    <div>
                        <span class="text-muted d-block" style="font-size: 0.72rem;">Marca</span>
                        <span class="fw-bold text-dark"><i class="fa-solid fa-tag me-1 text-primary"></i><?= htmlspecialchars($d['marele']); ?></span>
                    </div>
                    <div class="text-muted"><i class="fa-solid fa-arrow-right-long mx-1"></i></div>
                    <div class="text-end">
                        <span class="text-muted d-block" style="font-size: 0.72rem;">Serial / Placa</span>
                        <span class="fw-bold text-dark"><?= htmlspecialchars($d['nidele']); ?></span>
                    </div>
                </div>

                <!-- Metadatos de Cuentadante y Descripción -->
                <?php if (!empty($d['nomusu'])) { ?>
                    <div class="d-flex justify-content-between text-muted mb-1" style="font-size: 0.78rem;">
                        <span><i class="fa-solid fa-user text-secondary me-1"></i><strong>Cuentadante:</strong> <?= htmlspecialchars($d['nomusu']); ?> (<?= htmlspecialchars($d['ndocusu']); ?>)</span>
                    </div>
                <?php } ?>
                <?php if (!empty($d['desele'])) { ?>
                    <div class="text-muted text-truncate" style="font-size: 0.78rem;" title="<?=htmlspecialchars($d['desele']);?>">
                        <i class="fa-solid fa-align-left text-muted me-1"></i><em><?= htmlspecialchars($d['desele']); ?></em>
                    </div>
                <?php } ?>
            </div>
        </div>

        <!-- Footer con Botones de Acción -->
        <div class="card-footer bg-white border-0 pt-0 pb-3 px-3">
            <hr class="mt-0 mb-3 text-muted opacity-25">
            <div class="vid-actions-bar">
                <!-- Botón Ver Foto -->
                <?php if (!empty($fotoSrc)) { ?>
                    <button type="button" class="btn btn-outline-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modFot<?=$d['idele'];?>" title="Ver Foto">
                        <i class="fa-solid fa-eye"></i> <span class="d-none d-sm-inline">Ver</span>
                    </button>
                <?php } else { ?>
                    <button type="button" class="btn btn-outline-secondary shadow-sm disabled" title="Sin foto">
                        <i class="fa-solid fa-camera"></i> <span class="d-none d-sm-inline">S/F</span>
                    </button>
                <?php } ?>

                <!-- Botón Editar -->
                <a href="home.php?pg=<?=$pg;?>&idele=<?=$d['idele'];?>&ope=edi" class="btn btn-outline-warning text-dark shadow-sm" title="Editar">
                    <i class="fa-solid fa-pen-to-square"></i> <span class="d-none d-sm-inline">Editar</span>
                </a>

                <!-- Botón Activar / Desactivar en Portería -->
                <?php 
                $curUsu = isset($_SESSION["idusu"]) ? $_SESSION["idusu"] : 0; 
                $curPer = isset($_SESSION["idper"]) ? $_SESSION["idper"] : 0; 
                if ($curUsu == $d['idusu'] || in_array($curPer, [1, 2, 29])) { ?>
                    <?php if ($esActivo) { ?>
                        <a href="home.php?pg=<?=$pg;?>&idele=<?=$d['idele'];?>&opera=editPre&preele=2" class="btn btn-outline-secondary shadow-sm" title="Desactivar en portería">
                            <i class="fa-solid fa-toggle-on text-primary"></i> <span class="d-none d-sm-inline">Desact.</span>
                        </a>
                    <?php } else { ?>
                        <a href="home.php?pg=<?=$pg;?>&idele=<?=$d['idele'];?>&opera=editPre&preele=1" class="btn btn-outline-success shadow-sm" title="Activar hoy en portería">
                            <i class="fa-solid fa-toggle-off text-success"></i> <span class="d-none d-sm-inline">Activar</span>
                        </a>
                    <?php } ?>
                <?php } else { ?>
                    <button type="button" class="btn btn-outline-secondary shadow-sm disabled" title="Sin permisos">
                        <i class="fa-solid fa-lock"></i>
                    </button>
                <?php } ?>

                <!-- Botón Eliminar -->
                <a href="home.php?pg=<?=$pg;?>&idele=<?=$d['idele'];?>&opera=eli" class="btn btn-outline-danger shadow-sm" title="Eliminar" onclick="return eliminar(this);">
                    <i class="fa-solid fa-trash-can"></i> <span class="d-none d-sm-inline">Elim.</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Modal para Foto Ampliada -->
    <?php if (!empty($fotoSrc)) { ?>
        <div class="modal fade" id="modFot<?=$d['idele'];?>" tabindex="-1" aria-labelledby="modFotLabel<?=$d['idele'];?>" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content shadow-lg border-0" style="border-radius: 16px; overflow: hidden;">
                    <div class="modal-header bg-dark text-white border-0 px-4 py-3">
                        <h5 class="modal-title fs-5 d-flex align-items-center gap-2" id="modFotLabel<?=$d['idele'];?>">
                            <i class="fa-solid fa-image text-primary"></i>
                            <span class="text-truncate" style="max-width: 650px;">#<?=$d['idele'];?> - <?=$d['nomele'];?></span>
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body bg-black p-0 text-center">
                        <img src="<?=$fotoSrc;?>" alt="<?=htmlspecialchars($d['nomele']);?>" style="max-height: 70vh; max-width: 100%; object-fit: contain;">
                    </div>
                    <div class="modal-footer bg-light border-0 px-4 py-3 d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            <i class="fa-solid fa-barcode me-1"></i>Serial: <?=$d['nidele'];?> | 
                            <i class="fa-solid fa-tag me-1"></i>Marca: <?=$d['marele'];?>
                        </div>
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    <?php } ?>
<?php
}
}
?>

<!-- Toolbar de Control y Filtros (Estilo Institucional vvidgen.php) -->
<div class="filter-toolbar-container">
    <!-- Izquierda: Píldoras de Filtro de Estado y Exportación -->
    <div class="toolbar-left-group">
        <div class="filter-pills-group" id="tabElementos" role="tablist">
            <!-- Píldora Activos Hoy (Activa por defecto) -->
            <button class="btn-filter-pill active" id="activos-tab" data-bs-toggle="tab" data-bs-target="#activos" type="button" role="tab" aria-controls="activos" aria-selected="true" onclick="filterByTab('activos')">
                <span class="pill-dot" style="background-color: #006b29;"></span>
                <span>Activos Hoy</span>
                <span class="pill-count"><?= count($datAct); ?></span>
            </button>

            <!-- Píldora Inactivos -->
            <button class="btn-filter-pill" id="inactivos-tab" data-bs-toggle="tab" data-bs-target="#inactivos" type="button" role="tab" aria-controls="inactivos" aria-selected="false" onclick="filterByTab('inactivos')">
                <span class="pill-dot" style="background-color: #ba1a1a;"></span>
                <span>Inactivos</span>
                <span class="pill-count"><?= count($datInact); ?></span>
            </button>

            <!-- Píldora Todos -->
            <button class="btn-filter-pill" id="todos-tab" data-bs-toggle="tab" data-bs-target="#todos" type="button" role="tab" aria-controls="todos" aria-selected="false" onclick="filterByTab('todos')">
                <span class="pill-dot" style="background-color: #3c627f;"></span>
                <span>Todos</span>
                <span class="pill-count"><?= $totEle; ?></span>
            </button>
        </div>

        <!-- Separador -->
        <div class="toolbar-divider d-none d-sm-block"></div>

        <!-- Botones de Exportación integrados -->
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

    <!-- Derecha: Búsqueda, Ordenamiento y Vista Grid/Lista -->
    <div class="toolbar-search-sort-group">
        <!-- Input de búsqueda sincronizada con DataTables -->
        <div class="toolbar-search-box">
            <i class="fa-solid fa-magnifying-glass icoldiz"></i>
            <input type="text" id="customVideoSearch" placeholder="Buscar elemento..." onkeyup="syncCustomSearch(this.value)">
        </div>

        <!-- Controles Secundarios: Selector de Orden y Conmutador de Vistas -->
        <div class="toolbar-secondary-controls">
            <!-- Selector de ordenamiento -->
            <select class="toolbar-select-sort" id="customVideoSort" onchange="syncCustomSort(this.value)">
                <option value="desc">Descendente</option>
                <option value="asc">Ascendente</option>
            </select>

            <!-- Conmutador de cuadrícula / lista -->
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

<!-- Contenedor de Pestañas con Tablas en Formato Cuadrícula / Tarjetas -->
<div class="tab-content" id="tabElementosContent">
    <!-- Pestaña 1: Elementos Activos Hoy -->
    <div class="tab-pane fade show active" id="activos" role="tabpanel" aria-labelledby="activos-tab">
        <table id="example" class="table table-striped table-vid-cards" style="width:100%">
            <thead>
                <tr>
                    <th>Elementos Activos</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($datAct) { foreach ($datAct as $d) { ?>
                <tr data-order="<?=$d['idele'];?>">
                    <td data-search="<?=$d["idele"];?> <?=$d["nomele"];?> <?=$d["nidele"];?> <?=$d["noplasena"];?> <?=$d["marele"];?> <?=$d["nomval"];?> <?=$d["desele"];?> <?=($d["nomusu"] ?? '');?>">
                        <?php renderElementCard($d, $dtAllPr, $pg); ?>
                    </td>
                </tr>
                <?php }} ?>
            </tbody>
            <tfoot>
                <tr>
                    <th>Elementos Activos</th>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Pestaña 2: Elementos Inactivos -->
    <div class="tab-pane fade" id="inactivos" role="tabpanel" aria-labelledby="inactivos-tab">
        <table id="myt" class="table table-striped table-vid-cards" style="width:100%">
            <thead>
                <tr>
                    <th>Elementos Inactivos</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($datInact) { foreach ($datInact as $d) { ?>
                <tr data-order="<?=$d['idele'];?>">
                    <td data-search="<?=$d["idele"];?> <?=$d["nomele"];?> <?=$d["nidele"];?> <?=$d["noplasena"];?> <?=$d["marele"];?> <?=$d["nomval"];?> <?=$d["desele"];?> <?=($d["nomusu"] ?? '');?>">
                        <?php renderElementCard($d, $dtAllPr, $pg); ?>
                    </td>
                </tr>
                <?php }} ?>
            </tbody>
            <tfoot>
                <tr>
                    <th>Elementos Inactivos</th>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Pestaña 3: Todos los Elementos -->
    <div class="tab-pane fade" id="todos" role="tabpanel" aria-labelledby="todos-tab">
        <table id="tblTodos" class="table table-striped table-vid-cards" style="width:100%">
            <thead>
                <tr>
                    <th>Todos los Elementos</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($datAll) { foreach ($datAll as $d) { ?>
                <tr data-order="<?=$d['idele'];?>">
                    <td data-search="<?=$d["idele"];?> <?=$d["nomele"];?> <?=$d["nidele"];?> <?=$d["noplasena"];?> <?=$d["marele"];?> <?=$d["nomval"];?> <?=$d["desele"];?> <?=($d["nomusu"] ?? '');?>">
                        <?php renderElementCard($d, $dtAllPr, $pg); ?>
                    </td>
                </tr>
                <?php }} ?>
            </tbody>
            <tfoot>
                <tr>
                    <th>Todos los Elementos</th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<script>


function previewImg(input) {
  if (input.files && input.files[0]) {
    var reader = new FileReader();
    reader.onload = function(e) {
      var img = document.getElementById('imgPrev');
      var container = document.getElementById('prevContainer');
      if (img) img.src = e.target.result;
      if (container) {
        container.classList.remove('d-none');
        container.classList.add('d-flex');
      }
    };
    reader.readAsDataURL(input.files[0]);
  }
}
</script>