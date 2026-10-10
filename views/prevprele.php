<?php 
$ctrlPath = file_exists('controllers/precprele.php') ? 'controllers/precprele.php' : '../controllers/precprele.php';
require_once $ctrlPath;
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!isset($pg)) {
    $pg = isset($_GET['pg']) ? $_GET['pg'] : 1503;
}
if (!function_exists('titulo2')) {
    $optPath = file_exists('controllers/optimg.php') ? 'controllers/optimg.php' : '../controllers/optimg.php';
    if (file_exists($optPath)) require_once $optPath;
}
?>

<div class="conte">
  <?php echo titulo2("<i class='".($icono ?? 'fa-solid fa-hand-holding-hand') . "'></i> Préstamo de Elementos y Control de Morosidad", 1); ?>

  <div class="alert alert-info py-2 mb-4 d-flex align-items-center gap-2 border-0 shadow-sm" role="alert">
    <i class="fa-solid fa-circle-info fs-5 text-primary"></i>
    <div>
      <strong>Gestión de Préstamos Circulantes (Deportes, Biblioteca, Mesa de Ayuda TIC):</strong> 
      Registre aquí las salidas temporales de activos institucionales a aprendices o funcionarios. El sistema verifica la disponibilidad del elemento y bloquea automáticamente préstamos a usuarios con pendientes o en mora.
    </div>
  </div>

  <!-- Dashboard de Métricas (Tarjetas KPI Estilo vvidgen.php) -->
  <div class="row my-4 g-4">
    <!-- Elementos Disponibles -->
    <div class="col-12 col-sm-6 col-xl-3">
      <div class="kpi-card h-100 d-flex flex-column justify-content-between">
        <div class="d-flex align-items-center justify-content-between">
          <div>
            <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #006b29;">Disponibles</span>
            <div class="d-flex align-items-baseline gap-2 mt-2">
              <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #006b29;"><?= number_format($statsPre['disponibles'] ?? 0); ?></span>
              <span style="font-size: 13px; color: rgba(0, 107, 41, 0.8);">en almacén</span>
            </div>
          </div>
          <div class="kpi-icon-box" style="background-color: #7afd88; color: #006b24;">
            <i class="fa-solid fa-boxes-packing fs-4"></i>
          </div>
        </div>
        <div class="kpi-footer-strip">
          <span class="fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 11px; color: #006b29;">
            <i class="fa-solid fa-circle-check" style="font-size: 12px;"></i> Listos para prestar
          </span>
          <span style="font-size: 11px; color: #3e4a3d;">En stock</span>
        </div>
      </div>
    </div>

    <!-- Préstamos Activos -->
    <div class="col-12 col-sm-6 col-xl-3">
      <div class="kpi-card h-100 d-flex flex-column justify-content-between">
        <div class="d-flex align-items-center justify-content-between">
          <div>
            <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #0d6efd;">Préstamos Activos</span>
            <div class="d-flex align-items-baseline gap-2 mt-2">
              <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #0b1c30;"><?= number_format($statsPre['activos'] ?? 0); ?></span>
              <span style="font-size: 13px; color: #3e4a3d;">en uso</span>
            </div>
          </div>
          <div class="kpi-icon-box" style="background-color: #cfe2ff; color: #084298;">
            <i class="fa-solid fa-hand-holding-hand fs-4"></i>
          </div>
        </div>
        <div class="kpi-footer-strip">
          <span class="fw-semibold d-inline-flex align-items-center gap-2" style="font-size: 11px; color: #084298;">
            <span class="pulse-dot"></span> Circulación Vigente
          </span>
          <span style="font-size: 11px; color: #3e4a3d;">Por entregar</span>
        </div>
      </div>
    </div>

    <!-- Devueltos -->
    <div class="col-12 col-sm-6 col-xl-3">
      <div class="kpi-card h-100 d-flex flex-column justify-content-between">
        <div class="d-flex align-items-center justify-content-between">
          <div>
            <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #3e4a3d;">Devueltos</span>
            <div class="d-flex align-items-baseline gap-2 mt-2">
              <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #0b1c30;"><?= number_format($statsPre['devueltos'] ?? 0); ?></span>
              <span style="font-size: 13px; color: #3e4a3d;">cerrados</span>
            </div>
          </div>
          <div class="kpi-icon-box" style="background-color: #b5dcfe; color: #3c627f;">
            <i class="fa-solid fa-clipboard-check fs-4"></i>
          </div>
        </div>
        <div class="kpi-footer-strip">
          <span class="fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 11px; color: #3c627f;">
            <i class="fa-solid fa-check-double" style="font-size: 12px;"></i> Finalizados
          </span>
          <span style="font-size: 11px; color: #3e4a3d;">Completados</span>
        </div>
      </div>
    </div>

    <!-- En Alerta de Mora -->
    <div class="col-12 col-sm-6 col-xl-3">
      <div class="kpi-card h-100 d-flex flex-column justify-content-between">
        <div class="d-flex align-items-center justify-content-between">
          <div>
            <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #ba1a1a;">En Alerta de Mora</span>
            <div class="d-flex align-items-baseline gap-2 mt-2">
              <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #ba1a1a;"><?= number_format($statsPre['mora'] ?? 0); ?></span>
              <span style="font-size: 13px; color: #3e4a3d;">vencidos</span>
            </div>
          </div>
          <div class="kpi-icon-box" style="background-color: #ffdad6; color: #ba1a1a;">
            <i class="fa-solid fa-triangle-exclamation fs-4"></i>
          </div>
        </div>
        <div class="kpi-footer-strip">
          <span class="d-inline-flex align-items-center gap-1" style="font-size: 11px; color: #ba1a1a;">
            <i class="fa-solid fa-clock-rotate-left" style="font-size: 12px;"></i> Supera 24 horas
          </span>
          <span class="fw-semibold" style="font-size: 11px; color: #ba1a1a;">Pendientes</span>
        </div>
      </div>
    </div>
  </div>

  <!-- Formulario de Registro de Salida / Préstamo -->
  <div class="inser">
  <?php if (isset($_SESSION['idper']) && in_array($_SESSION['idper'], [1, 2, 6, 9, 29])) { ?>
    <form id="frmins" class="filter-toolbar-container" name="frm1" action="home.php?pg=<?=$pg;?>" method="POST">
      <div class="w-100">
        <h2>
          <i class="fa-solid fa-file-circle-plus"></i> Registrar Salida / Préstamo de Elemento Institucional
        </h2>
      </div>
      <div class="row g-3 w-100">
        <div class="form-group col-md-4">
          <label for="docusu">Documento del Beneficiario *</label>
          <div class="input-group">
            <span class="input-group-text"><i class="fa-solid fa-id-card"></i></span>
            <input type="text" name="docusu" id="docusu" class="form-control" required placeholder="Cédula o T.I. del aprendiz / funcionario" value="<?php if ($dtOne) echo htmlspecialchars($dtOne[0]['docu']); ?>">
          </div>
          <small class="text-muted">El sistema validará si el usuario está al día.</small>
        </div>
        <div class="form-group col-md-5">
          <label for="idele">Elemento Institucional Disponible *</label>
          <select class="form-control form-select" name="idele" id="idele" required>
            <option value="">Seleccione un elemento disponible...</option>
            <?php if (!empty($dtEle)) { foreach ($dtEle as $de) { ?>
              <option value="<?=$de['idele'];?>">
                <?= htmlspecialchars($de['nomele']); ?> | Placa SENA: <?= htmlspecialchars($de['noplasena']); ?> (<?= htmlspecialchars($de['marele']); ?>)
              </option>
            <?php }} else { ?>
              <option value="" disabled>No hay elementos institucionales disponibles para préstamo</option>
            <?php } ?>
          </select>
          <small class="text-muted">Solo se listan elementos institucionales no prestados actualmente.</small>
        </div>
        <div class="form-group col-md-3">
          <label for="can">Cantidad *</label>
          <input type="number" min="1" max="50" name="can" id="can" class="form-control" value="1" required>
        </div>
        <div class="form-group col-md-12 d-flex align-items-center gap-2 pt-2">
          <button class="btn btn-success d-inline-flex align-items-center gap-2" type="submit">
            <i class="fa-solid fa-floppy-disk"></i> Autorizar y Registrar Préstamo
          </button>
          <input type="hidden" name="ope" value="save">
          <input type="hidden" name="idprele" value="<?php if ($dtOne) echo $dtOne[0]['idprele'];?>">
        </div>
      </div>
    </form>
  <?php } ?>
  </div>

<?php
// Clasificar préstamos para las pestañas
$datActivos = [];
$datMora = [];
$datDevueltos = [];

if (!empty($dtAll)) {
    foreach ($dtAll as $d) {
        $esMora = false;
        if ($d['estpre'] == 1 && !empty($d['fhpre'])) {
            $fechaPre = strtotime($d['fhpre']);
            $horas = (time() - $fechaPre) / 3600;
            if ($horas >= 24 || date('Y-m-d', $fechaPre) < date('Y-m-d')) {
                $esMora = true;
            }
        }
        
        if ($d['estpre'] == 2) {
            $datDevueltos[] = $d;
        } elseif ($esMora) {
            $datMora[] = $d;
        } else {
            $datActivos[] = $d;
        }
    }
}
$totPrestamos = count($dtAll ?? []);

// Helper para renderizar cada tarjeta de préstamo en cuadrícula
if (!function_exists('renderPrestamoCard')) {
function renderPrestamoCard($d, $pg) {
    $esMora = false;
    if ($d['estpre'] == 1 && !empty($d['fhpre'])) {
        $fechaPre = strtotime($d['fhpre']);
        $horas = (time() - $fechaPre) / 3600;
        if ($horas >= 24 || date('Y-m-d', $fechaPre) < date('Y-m-d')) {
            $esMora = true;
        }
    }

    $fotoPre = '';
    if (!empty($d['rutfot'])) {
        if (file_exists($d['rutfot'])) {
            $fotoPre = $d['rutfot'];
        } elseif (file_exists('fotele/' . basename($d['rutfot']))) {
            $fotoPre = 'fotele/' . basename($d['rutfot']);
        } elseif (file_exists('img/fele/' . basename($d['rutfot']))) {
            $fotoPre = 'img/fele/' . basename($d['rutfot']);
        }
    }
?>
    <div class="card h-100 shadow-sm border-0 vid-card">
        <!-- Preview Box del Préstamo -->
        <div class="vid-preview-box" <?= ($d['estpre'] == 2) ? 'style="filter: grayscale(0.25);"' : ''; ?>>
            <?php if (!empty($fotoPre)) { ?>
                <img src="<?=$fotoPre;?>" alt="<?=htmlspecialchars($d['nomele']);?>" style="width:100%; height:100%; object-fit:cover; opacity:0.88;">
            <?php } else { ?>
                <div class="d-flex flex-column align-items-center justify-content-center text-white-50">
                    <i class="fa-solid fa-hand-holding-hand fa-3x mb-2 text-white opacity-75"></i>
                    <small class="text-uppercase fw-semibold" style="font-size: 0.72rem; letter-spacing: 0.5px;">Elemento Institucional</small>
                </div>
            <?php } ?>

            <!-- Badges Superiores -->
            <div class="vid-badges-top">
                <?php if ($d['estpre'] == 2) { ?>
                    <span class="badge bg-secondary shadow-sm px-2 py-1">
                        <i class="fa-solid fa-check-double me-1"></i> Devuelto
                    </span>
                <?php } elseif ($esMora) { ?>
                    <span class="badge bg-danger shadow-sm px-2 py-1">
                        <i class="fa-solid fa-triangle-exclamation me-1"></i> En Mora
                    </span>
                <?php } else { ?>
                    <span class="badge bg-success shadow-sm px-2 py-1">
                        <i class="fa-solid fa-clock me-1"></i> Activo al Día
                    </span>
                <?php } ?>

                <?php if (!empty($d['noplasena'])) { ?>
                    <span class="badge vid-badge-blur px-2 py-1 shadow-sm">
                        <i class="fa-solid fa-building-columns me-1 text-info"></i> Placa: <?=$d['noplasena'];?>
                    </span>
                <?php } ?>
            </div>

            <!-- Botón Central (Ver Foto o Detalle) -->
            <?php if (!empty($fotoPre)) { ?>
                <div class="vid-play-btn" data-bs-toggle="modal" data-bs-target="#modPre<?=$d['idprele'];?>" title="Ver Fotografía" role="button">
                    <i class="fa-solid fa-eye"></i>
                </div>
            <?php } ?>

            <!-- Badges Inferiores -->
            <div class="vid-badges-bottom">
                <span class="badge vid-badge-blur px-2 py-1">
                    <i class="fa-solid fa-user-tag me-1 text-primary"></i> <?= htmlspecialchars($d['peru'] ?? 'Usuario'); ?>
                </span>
                <span class="badge vid-badge-blur px-2 py-1">
                    <i class="fa-regular fa-calendar me-1 text-warning"></i> <?= htmlspecialchars($d['fhpre'] ?? ''); ?>
                </span>
            </div>
        </div>

        <!-- Cuerpo de la Tarjeta -->
        <div class="card-body p-3 d-flex flex-column justify-content-between">
            <div>
                <!-- Título del préstamo con ID -->
                <h6 class="fw-bold text-dark mb-2 text-truncate" title="#<?=$d['idprele'];?> - <?=htmlspecialchars($d['nomele']);?>" style="font-size: 1.05rem;">
                    <span class="<?= ($esMora) ? 'text-danger' : 'text-primary'; ?>">#<?=$d['idprele'];?></span> - <?=htmlspecialchars($d['nomele']);?>
                </h6>

                <!-- Caja de Fechas (Préstamo -> Entrega) -->
                <div class="p-2 mb-3 rounded-3 bg-light border border-light-subtle d-flex justify-content-between align-items-center" style="font-size: 0.8rem;">
                    <div>
                        <span class="text-muted d-block" style="font-size: 0.72rem;">Préstamo</span>
                        <span class="fw-bold text-success"><i class="fa-regular fa-calendar-check me-1"></i><?= htmlspecialchars($d['fhpre'] ?? ''); ?></span>
                    </div>
                    <div class="text-muted"><i class="fa-solid fa-arrow-right-long mx-1"></i></div>
                    <div class="text-end">
                        <span class="text-muted d-block" style="font-size: 0.72rem;">Entrega</span>
                        <span class="fw-bold <?= ($d['estpre'] == 2) ? 'text-secondary' : 'text-danger'; ?>">
                            <?= (!empty($d['fhent'])) ? htmlspecialchars($d['fhent']) : 'En posesión'; ?>
                        </span>
                    </div>
                </div>

                <!-- Metadatos de Beneficiario y Ficha -->
                <div class="d-flex justify-content-between text-muted mb-1" style="font-size: 0.78rem;">
                    <span><i class="fa-solid fa-id-card text-primary me-1"></i><strong>Beneficiario:</strong> <?= htmlspecialchars($d['nomu'] ?? ''); ?> (<?= htmlspecialchars($d['docu'] ?? ''); ?>)</span>
                </div>
                <?php if (!empty($d['nomfic'])) { ?>
                    <div class="text-muted text-truncate mb-1" style="font-size: 0.78rem;" title="Ficha: <?=htmlspecialchars($d['idfic']);?> - <?=htmlspecialchars($d['nomfic']);?>">
                        <i class="fa-solid fa-graduation-cap text-secondary me-1"></i>Ficha: <?= htmlspecialchars($d['idfic']); ?> - <?= htmlspecialchars($d['nomfic']); ?>
                    </div>
                <?php } ?>
                <?php if (!empty($d['nomw'])) { ?>
                    <div class="text-muted text-truncate" style="font-size: 0.78rem;">
                        <i class="fa-solid fa-user-check text-success me-1"></i><strong>Autorizó:</strong> <?= htmlspecialchars($d['nomw']); ?>
                    </div>
                <?php } ?>
            </div>
        </div>

        <!-- Footer con Botones de Acción -->
        <div class="card-footer bg-white border-0 pt-0 pb-3 px-3">
            <hr class="mt-0 mb-3 text-muted opacity-25">
            <div class="vid-actions-bar" style="grid-template-columns: 1fr 1fr;">
                <!-- Botón Ver Fotografía -->
                <?php if (!empty($fotoPre)) { ?>
                    <button type="button" class="btn btn-outline-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modPre<?=$d['idprele'];?>" title="Ver Fotografía">
                        <i class="fa-solid fa-eye"></i> <span class="d-none d-sm-inline">Foto</span>
                    </button>
                <?php } else { ?>
                    <button type="button" class="btn btn-outline-secondary shadow-sm disabled" title="Sin foto">
                        <i class="fa-solid fa-camera"></i> <span class="d-none d-sm-inline">S/F</span>
                    </button>
                <?php } ?>

                <!-- Botón Recibir / Devolver si está activo -->
                <?php if (isset($_SESSION['idper']) && in_array($_SESSION['idper'], [1, 2, 6, 9, 29])) { ?>
                    <?php if ($d['estpre'] == 1) { ?>
                        <a href="home.php?pg=<?=$pg;?>&idprele=<?=$d['idprele'];?>&idele=<?=$d['idele'];?>&idusu=<?=$d['idusu'];?>&ope=entEle" 
                           class="btn btn-outline-warning text-dark shadow-sm d-inline-flex align-items-center justify-content-center gap-1" 
                           title="Registrar devolución de este elemento" 
                           onclick="return confirmarEntrega(this, '<?= htmlspecialchars(addslashes($d['nomele'] ?? 'Elemento')); ?>');">
                            <i class="fa-solid fa-arrow-rotate-left text-warning"></i> <span>Recibir</span>
                        </a>
                    <?php } else { ?>
                        <span class="badge bg-success-subtle text-success border border-success-subtle d-inline-flex align-items-center justify-content-center py-2">
                            <i class="fa-solid fa-circle-check me-1"></i> Entregado
                        </span>
                    <?php } ?>
                <?php } else { ?>
                    <span class="badge bg-light text-muted border d-inline-flex align-items-center justify-content-center py-2">
                        <?= ($d['estpre'] == 1) ? 'En posesión' : 'Cerrado'; ?>
                    </span>
                <?php } ?>
            </div>
        </div>
    </div>

    <!-- Modal Fotografía -->
    <?php if (!empty($fotoPre)) { ?>
        <div class="modal fade" id="modPre<?=$d['idprele'];?>" tabindex="-1" aria-labelledby="modPreLabel<?=$d['idprele'];?>" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content shadow-lg border-0" style="border-radius: 16px; overflow: hidden;">
                    <div class="modal-header bg-dark text-white border-0 px-4 py-3">
                        <h5 class="modal-title fs-5 d-flex align-items-center gap-2" id="modPreLabel<?=$d['idprele'];?>">
                            <i class="fa-solid fa-boxes-stacked text-primary"></i>
                            <span class="text-truncate" style="max-width: 650px;">#<?=$d['idprele'];?> - <?=$d['nomele'];?></span>
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body bg-black p-0 text-center">
                        <img src="<?=$fotoPre;?>" alt="<?=htmlspecialchars($d['nomele']);?>" style="max-height: 70vh; max-width: 100%; object-fit: contain;">
                    </div>
                    <div class="modal-footer bg-light border-0 px-4 py-3 d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            <i class="fa-solid fa-user me-1"></i>Beneficiario: <?=$d['nomu'];?> (<?=$d['docu'];?>)
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
        <div class="filter-pills-group" id="tabPrestamos" role="tablist">
            <!-- Píldora Activos al Día (Activa por defecto) -->
            <button class="btn-filter-pill active" id="activos-tab" data-bs-toggle="tab" data-bs-target="#activos" type="button" role="tab" aria-controls="activos" aria-selected="true" onclick="filterByTab('activos')">
                <span class="pill-dot" style="background-color: #006b29;"></span>
                <span>Activos al Día</span>
                <span class="pill-count"><?= count($datActivos); ?></span>
            </button>

            <!-- Píldora En Mora -->
            <button class="btn-filter-pill" id="mora-tab" data-bs-toggle="tab" data-bs-target="#mora" type="button" role="tab" aria-controls="mora" aria-selected="false" onclick="filterByTab('mora')">
                <span class="pill-dot" style="background-color: #ba1a1a;"></span>
                <span>En Mora</span>
                <span class="pill-count"><?= count($datMora); ?></span>
            </button>

            <!-- Píldora Devueltos -->
            <button class="btn-filter-pill" id="devueltos-tab" data-bs-toggle="tab" data-bs-target="#devueltos" type="button" role="tab" aria-controls="devueltos" aria-selected="false" onclick="filterByTab('devueltos')">
                <span class="pill-dot" style="background-color: #3c627f;"></span>
                <span>Devueltos</span>
                <span class="pill-count"><?= count($datDevueltos); ?></span>
            </button>

            <!-- Píldora Todos -->
            <button class="btn-filter-pill" id="todos-tab" data-bs-toggle="tab" data-bs-target="#todos" type="button" role="tab" aria-controls="todos" aria-selected="false" onclick="filterByTab('todos')">
                <span class="pill-dot" style="background-color: #0d6efd;"></span>
                <span>Todos</span>
                <span class="pill-count"><?= $totPrestamos; ?></span>
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
            <input type="text" id="customVideoSearch" placeholder="Buscar préstamo..." onkeyup="syncCustomSearch(this.value)">
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
<div class="tab-content" id="tabPrestamosContent">
    <!-- Pestaña 1: Préstamos Activos al Día -->
    <div class="tab-pane fade show active" id="activos" role="tabpanel" aria-labelledby="activos-tab">
        <table id="example" class="table table-striped table-vid-cards" style="width:100%">
            <thead>
                <tr>
                    <th>Activos al Día</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($datActivos) { foreach ($datActivos as $d) { ?>
                <tr data-order="<?=$d['idprele'];?>">
                    <td data-search="<?=$d["idprele"];?> <?=$d["nomele"];?> <?=$d["nomu"];?> <?=$d["docu"];?> <?=($d["noplasena"] ?? '');?> <?=($d["nomfic"] ?? '');?>">
                        <?php renderPrestamoCard($d, $pg); ?>
                    </td>
                </tr>
                <?php }} ?>
            </tbody>
            <tfoot>
                <tr>
                    <th>Activos al Día</th>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Pestaña 2: En Alerta de Mora -->
    <div class="tab-pane fade" id="mora" role="tabpanel" aria-labelledby="mora-tab">
        <table id="myt" class="table table-striped table-vid-cards" style="width:100%">
            <thead>
                <tr>
                    <th>En Alerta de Mora</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($datMora) { foreach ($datMora as $d) { ?>
                <tr data-order="<?=$d['idprele'];?>">
                    <td data-search="<?=$d["idprele"];?> <?=$d["nomele"];?> <?=$d["nomu"];?> <?=$d["docu"];?> <?=($d["noplasena"] ?? '');?> <?=($d["nomfic"] ?? '');?>">
                        <?php renderPrestamoCard($d, $pg); ?>
                    </td>
                </tr>
                <?php }} ?>
            </tbody>
            <tfoot>
                <tr>
                    <th>En Alerta de Mora</th>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Pestaña 3: Devueltos -->
    <div class="tab-pane fade" id="devueltos" role="tabpanel" aria-labelledby="devueltos-tab">
        <table id="tblDevueltos" class="table table-striped table-vid-cards" style="width:100%">
            <thead>
                <tr>
                    <th>Devueltos</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($datDevueltos) { foreach ($datDevueltos as $d) { ?>
                <tr data-order="<?=$d['idprele'];?>">
                    <td data-search="<?=$d["idprele"];?> <?=$d["nomele"];?> <?=$d["nomu"];?> <?=$d["docu"];?> <?=($d["noplasena"] ?? '');?> <?=($d["nomfic"] ?? '');?>">
                        <?php renderPrestamoCard($d, $pg); ?>
                    </td>
                </tr>
                <?php }} ?>
            </tbody>
            <tfoot>
                <tr>
                    <th>Devueltos</th>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Pestaña 4: Todos los Préstamos -->
    <div class="tab-pane fade" id="todos" role="tabpanel" aria-labelledby="todos-tab">
        <table id="tblTodos" class="table table-striped table-vid-cards" style="width:100%">
            <thead>
                <tr>
                    <th>Todos los Préstamos</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($dtAll) { foreach ($dtAll as $d) { ?>
                <tr data-order="<?=$d['idprele'];?>">
                    <td data-search="<?=$d["idprele"];?> <?=$d["nomele"];?> <?=$d["nomu"];?> <?=$d["docu"];?> <?=($d["noplasena"] ?? '');?> <?=($d["nomfic"] ?? '');?>">
                        <?php renderPrestamoCard($d, $pg); ?>
                    </td>
                </tr>
                <?php }} ?>
            </tbody>
            <tfoot>
                <tr>
                    <th>Todos los Préstamos</th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<script type="text/javascript">
function confirmarEntrega(link, elemento) {
  event.preventDefault();
  Swal.fire({
    title: '¿Registrar Devolución?',
    html: '¿Confirma la entrega y recepción del elemento:<br><strong>' + elemento + '</strong>?',
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Sí, recibir elemento',
    confirmButtonColor: '#117f09',
    cancelButtonText: 'Cancelar'
  }).then((result) => {
    if (result.isConfirmed) {
      window.location.href = link.href;
    }
  });
  return false;
}
</script>