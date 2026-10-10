<?php 
$ctrlPath = file_exists('controllers/precpam.php') ? 'controllers/precpam.php' : '../controllers/precpam.php';
require_once $ctrlPath;
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!isset($pg)) {
    $pg = isset($_GET['pg']) ? $_GET['pg'] : 1505;
}
if (!function_exists('titulo2')) {
    $optPath = file_exists('controllers/optimg.php') ? 'controllers/optimg.php' : '../controllers/optimg.php';
    if (file_exists($optPath)) require_once $optPath;
}
?>

<div class="conte">
  <?php echo titulo2("<i class='".($icono ?? 'fa fa-solid fa-school')."'></i> Ambientes", 1); ?>

  <?php if(isset($cnt) && $cnt): ?>
  <!-- Dashboard de Métricas (Tarjetas KPI Estilo vvidgen.php) -->
  <div class="row my-4 g-4">
    <!-- Total de Ambientes -->
    <div class="col-12 col-sm-6 col-xl-3">
      <div class="kpi-card h-100 d-flex flex-column justify-content-between">
        <div class="d-flex align-items-center justify-content-between">
          <div>
            <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #3c627f;">Total Ambientes</span>
            <div class="d-flex align-items-baseline gap-2 mt-2">
              <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #0b1c30;"><?= $cnt[0]['total']; ?></span>
              <span style="font-size: 13px; color: #3e4a3d;">registrados</span>
            </div>
          </div>
          <div class="kpi-icon-box" style="background-color: #b5dcfe; color: #3c627f;">
            <i class="fa-solid fa-door-open fs-4"></i>
          </div>
        </div>
        <div class="kpi-footer-strip">
          <span class="fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 11px; color: #3c627f;">
            <i class="fa-solid fa-building" style="font-size: 12px;"></i> Espacios Físicos
          </span>
          <span style="font-size: 11px; color: #3e4a3d;">Inventario</span>
        </div>
      </div>
    </div>

    <!-- Disponibles -->
    <div class="col-12 col-sm-6 col-xl-3">
      <div class="kpi-card h-100 d-flex flex-column justify-content-between">
        <div class="d-flex align-items-center justify-content-between">
          <div>
            <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #006b29;">Disponibles</span>
            <div class="d-flex align-items-baseline gap-2 mt-2">
              <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #006b29;"><?= $cnt[0]['disponibles']; ?></span>
              <span style="font-size: 13px; color: rgba(0, 107, 41, 0.8);">libres</span>
            </div>
          </div>
          <div class="kpi-icon-box" style="background-color: #7afd88; color: #006b24;">
            <i class="fa-solid fa-circle-check fs-4"></i>
          </div>
        </div>
        <div class="kpi-footer-strip">
          <span class="fw-semibold d-inline-flex align-items-center gap-2" style="font-size: 11px; color: #006b29;">
            <span class="pulse-dot"></span> Listos para reservar
          </span>
          <span style="font-size: 11px; color: #3e4a3d;">Habilitados</span>
        </div>
      </div>
    </div>

    <!-- Apartados -->
    <div class="col-12 col-sm-6 col-xl-3">
      <div class="kpi-card h-100 d-flex flex-column justify-content-between">
        <div class="d-flex align-items-center justify-content-between">
          <div>
            <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #8a6d00;">Apartados</span>
            <div class="d-flex align-items-baseline gap-2 mt-2">
              <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #0b1c30;"><?= $cnt[0]['apartados']; ?></span>
              <span style="font-size: 13px; color: #3e4a3d;">en préstamo</span>
            </div>
          </div>
          <div class="kpi-icon-box" style="background-color: #ffe082; color: #8a6d00;">
            <i class="fa-solid fa-clock fs-4"></i>
          </div>
        </div>
        <div class="kpi-footer-strip">
          <span class="fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 11px; color: #8a6d00;">
            <i class="fa-solid fa-key" style="font-size: 12px;"></i> En posesión
          </span>
          <span style="font-size: 11px; color: #3e4a3d;">Activos</span>
        </div>
      </div>
    </div>

    <!-- Movimientos -->
    <div class="col-12 col-sm-6 col-xl-3">
      <div class="kpi-card h-100 d-flex flex-column justify-content-between">
        <div class="d-flex align-items-center justify-content-between">
          <div>
            <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #ba1a1a;">Movimientos</span>
            <div class="d-flex align-items-baseline gap-2 mt-2">
              <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #0b1c30;"><?= $cnt[0]['movimientos']; ?></span>
              <span style="font-size: 13px; color: #3e4a3d;">historial</span>
            </div>
          </div>
          <div class="kpi-icon-box" style="background-color: #ffdad6; color: #ba1a1a;">
            <i class="fa-solid fa-arrows-rotate fs-4"></i>
          </div>
        </div>
        <div class="kpi-footer-strip">
          <span class="d-inline-flex align-items-center gap-1" style="font-size: 11px; color: #ba1a1a;">
            <i class="fa-solid fa-list-check" style="font-size: 12px;"></i> Sesiones de préstamo
          </span>
          <span class="fw-semibold" style="font-size: 11px; color: #3c627f;">Total</span>
        </div>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <!-- Formulario de Registro / Apartado -->
  <div class="inser">
    <form name="frm1" id="frmins" class="filter-toolbar-container" action="home.php?pg=<?=$pg;?>" method="POST">
      <div class="w-100">
        <h2>
          <i class="fa-solid fa-file-circle-plus"></i> Registrar Préstamo / Apartado de Ambiente
        </h2>
      </div>
      <div class="row g-3 w-100 align-items-end">
        <div class="form-group col-md-4">
          <label for="ndocusu">Número de identificación del solicitante *</label>
          <div class="input-group">
            <span class="input-group-text"><i class="fa-solid fa-id-card"></i></span>
            <input class="form-control" type="text" name="ndocusu" id="ndocusu" required placeholder="Ej. 1065487523">
          </div>
        </div>
        <div class="form-group col-md-5">
          <label for="idval">Ambiente *</label>
          <select class="form-control form-select" name="idval" id="idval" required>
            <option value="">Seleccione ambiente...</option>
            <?php if ($datAmb) { foreach ($datAmb As $dta) { ?>
              <option value="<?=$dta['idval'];?>"><?=$dta['idval'].' - '.$dta['parval'].' - '.$dta['nomval'];?></option>
            <?php }} ?>
          </select>
        </div>
        <div class="form-group col-md-3">
          <input type="hidden" name="ope" value="save">
          <button type="submit" class="btn btn-success d-inline-flex align-items-center gap-2 w-100 justify-content-center">
            <i class="fa-solid fa-plus"></i> Registrar Préstamo
          </button>
        </div>
      </div>
    </form>
  </div>

<?php
// Clasificar registros para las pestañas
$datApartados = [];

if (!empty($datAll)) {
    foreach ($datAll as $d) {
        if ($d['tipmin'] != "A") {
            $datApartados[] = $d;
        }
    }
}
$totAmbientes = count($datAll ?? []);

// Helper para renderizar cada movimiento de ambiente en formato tarjeta
if (!function_exists('renderAmbienteCard')) {
function renderAmbienteCard($d, $pg) {
    $esEntregado = ($d['tipmin'] == "A");
?>
    <div class="card h-100 shadow-sm border-0 vid-card">
        <!-- Cuerpo de la Tarjeta -->
        <div class="card-body p-3 d-flex flex-column justify-content-between">
            <div>
                <!-- Badges Superiores: Estado, Categoría e Identificador -->
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2 pb-1 border-bottom">
                    <?php if ($esEntregado) { ?>
                        <span class="badge bg-success shadow-sm px-2 py-1">
                            <i class="fa-solid fa-check-double me-1"></i> Entregado
                        </span>
                    <?php } else { ?>
                        <span class="badge bg-warning text-dark shadow-sm px-2 py-1">
                            <i class="fa-solid fa-clock me-1"></i> Apartado
                        </span>
                    <?php } ?>

                    <div class="d-flex align-items-center gap-1">
                        <span class="badge bg-light text-muted border px-2 py-1">
                            <i class="fa-solid fa-layer-group me-1 text-primary"></i> <?= htmlspecialchars($d['parval'] ?? 'Ambiente'); ?>
                        </span>
                        <span class="badge bg-light text-dark border px-2 py-1">
                            <i class="fa-solid fa-hashtag me-1 text-secondary"></i> ID: <?=$d['ideles'];?>
                        </span>
                    </div>
                </div>

                <!-- Título del ambiente -->
                <h6 class="fw-bold text-dark mb-2 text-truncate" title="<?=$d['ideles'].' - '.$d['nomval'];?>" style="font-size: 1.05rem;">
                    <span class="text-success">#<?=$d['ideles'];?></span> - <?=htmlspecialchars($d['nomval']);?>
                </h6>

                <!-- Caja de Fechas (Solicitud -> Entrega) -->
                <div class="p-2 mb-3 rounded-3 bg-light border border-light-subtle d-flex justify-content-between align-items-center" style="font-size: 0.8rem;">
                    <div>
                        <span class="text-muted d-block" style="font-size: 0.72rem;">Fecha Solicitud</span>
                        <span class="fw-bold text-success"><i class="fa-regular fa-calendar-check me-1"></i><?=$d['fechos'];?></span>
                    </div>
                    <div class="text-muted"><i class="fa-solid fa-arrow-right-long mx-1"></i></div>
                    <div class="text-end">
                        <span class="text-muted d-block" style="font-size: 0.72rem;">Fecha Entrega</span>
                        <span class="fw-bold <?= $esEntregado ? 'text-secondary' : 'text-danger'; ?>">
                            <?= (!empty($d['fhlle'])) ? $d['fhlle'] : 'Pendiente'; ?>
                        </span>
                    </div>
                </div>

                <!-- Datos del Solicitante -->
                <div class="d-flex justify-content-between text-muted mb-1" style="font-size: 0.78rem;">
                    <span><i class="fa-solid fa-user text-primary me-1"></i><strong>Solicitante:</strong> <?= htmlspecialchars($d['nomusu']); ?></span>
                </div>
                <div class="text-muted small" style="font-size: 0.78rem;">
                    <i class="fa-solid fa-id-card text-secondary me-1"></i><strong>Documento:</strong> <?= htmlspecialchars($d['ndocusu']); ?>
                </div>
            </div>
        </div>

        <!-- Footer con Acciones -->
        <div class="card-footer bg-white border-0 pt-0 pb-3 px-3">
            <hr class="mt-0 mb-3 text-muted opacity-25">
            <div>
                <?php if (!$esEntregado) { ?>
                    <a class="btn btn-outline-primary shadow-sm w-100 d-flex align-items-center justify-content-center gap-2" href="home.php?pg=<?=$pg;?>&idval=<?=$d['ideles'];?>&nummin=<?=$d['nummin'];?>&ope=edit" title="Registrar entrega del ambiente">
                        <i class="fa-solid fa-check"></i> <span>Registrar Entrega</span>
                    </a>
                <?php } else { ?>
                    <span class="badge bg-success-subtle text-success border border-success-subtle d-flex align-items-center justify-content-center py-2 w-100">
                        <i class="fa-solid fa-circle-check me-1"></i> Ambiente Liberado Conforme
                    </span>
                <?php } ?>
            </div>
        </div>
    </div>
<?php
}
}
?>

<!-- Toolbar de Control y Filtros (Estilo Institucional vvidgen.php) -->
<div class="filter-toolbar-container">
    <!-- Izquierda: Píldoras de Filtro de Estado y Exportación -->
    <div class="toolbar-left-group">
        <div class="filter-pills-group" id="tabAmbientes" role="tablist">
            <!-- Píldora Apartados (Activa por defecto) -->
            <button class="btn-filter-pill active" id="activos-tab" data-bs-toggle="tab" data-bs-target="#activos" type="button" role="tab" aria-controls="activos" aria-selected="true" onclick="filterByTab('activos')">
                <span class="pill-dot" style="background-color: #8a6d00;"></span>
                <span>Apartados</span>
                <span class="pill-count"><?= count($datApartados); ?></span>
            </button>

            <!-- Píldora Todos -->
            <button class="btn-filter-pill" id="todos-tab" data-bs-toggle="tab" data-bs-target="#todos" type="button" role="tab" aria-controls="todos" aria-selected="false" onclick="filterByTab('todos')">
                <span class="pill-dot" style="background-color: #3c627f;"></span>
                <span>Todos</span>
                <span class="pill-count"><?= $totAmbientes; ?></span>
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
            <input type="text" id="customVideoSearch" placeholder="Buscar ambiente, solicitante..." onkeyup="syncCustomSearch(this.value)">
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
<div class="tab-content" id="tabAmbientesContent">
    <!-- Pestaña 1: Ambientes Apartados -->
    <div class="tab-pane fade show active" id="activos" role="tabpanel" aria-labelledby="activos-tab">
        <table id="example" class="table table-striped table-vid-cards" style="width:100%">
            <thead>
                <tr>
                    <th>Ambientes Apartados</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($datApartados) { foreach ($datApartados as $d) { ?>
                <tr data-order="<?=$d['nummin'];?>">
                    <td data-search="<?=$d["ideles"];?> <?=$d["nomval"];?> <?=$d["parval"];?> <?=$d["nomusu"];?> <?=$d["ndocusu"];?> <?=$d["fechos"];?>">
                        <?php renderAmbienteCard($d, $pg); ?>
                    </td>
                </tr>
                <?php }} ?>
            </tbody>
            <tfoot>
                <tr>
                    <th>Ambientes Apartados</th>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Pestaña 3: Todos los Movimientos -->
    <div class="tab-pane fade" id="todos" role="tabpanel" aria-labelledby="todos-tab">
        <table id="tblTodos" class="table table-striped table-vid-cards" style="width:100%">
            <thead>
                <tr>
                    <th>Todos los Movimientos</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($datAll) { foreach ($datAll as $d) { ?>
                <tr data-order="<?=$d['nummin'];?>">
                    <td data-search="<?=$d["ideles"];?> <?=$d["nomval"];?> <?=$d["parval"];?> <?=$d["nomusu"];?> <?=$d["ndocusu"];?> <?=$d["fechos"];?>">
                        <?php renderAmbienteCard($d, $pg); ?>
                    </td>
                </tr>
                <?php }} ?>
            </tbody>
            <tfoot>
                <tr>
                    <th>Todos los Movimientos</th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
</div>