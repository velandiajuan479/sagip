<?php require_once 'controllers/horcplss.php'; ?>
<?php
$pg      = isset($pg) ? $pg : '1512';
$icono   = isset($icono) ? $icono : 'fa-solid fa-calendar-days';
$datFic   = isset($datFic) && is_array($datFic) ? $datFic : [];
$datIns   = isset($datIns) && is_array($datIns) ? $datIns : [];
$datComF  = isset($datComF) && is_array($datComF) ? $datComF : [];
$datResF  = isset($datResF) && is_array($datResF) ? $datResF : [];
$datTipos = isset($datTipos) && is_array($datTipos) ? $datTipos : [];
$datFichaSel = isset($datFichaSel) && is_array($datFichaSel) ? $datFichaSel : [];
$datAll   = isset($datAll) && is_array($datAll) ? $datAll : [];
$datPag   = isset($datPag) && is_array($datPag) ? $datPag : ['data' => [], 'total' => 0, 'total_paginas' => 1, 'pagina' => 1];
$filtros  = isset($filtros) && is_array($filtros) ? $filtros : [];
$plsConDatos = isset($plsConDatos) ? (bool)$plsConDatos : false;
$plsPage  = isset($plsPage) ? (int)$plsPage : 1;
function pls_qs($extra = []) {
    $f = isset($GLOBALS['filtros']) ? $GLOBALS['filtros'] : [];
    $parts = [];
    foreach ($f as $k => $v) { if ($v !== '' && $v !== null) { $parts[] = rawurlencode($k) . '=' . rawurlencode($v); } }
    foreach ($extra as $k => $v) { $parts[] = rawurlencode($k) . '=' . rawurlencode($v); }
    return implode('&', $parts);
}
?>
<style>
@media print {
  .no-print, header, nav, footer, .header-home, .btn, .card-header .btn,
  .alert-dismissible, #card-formulario, #card-filtros, .filter-toolbar-container { display: none !important; }
  body, .contenido, .contenido2 { margin: 0 !important; padding: 0 !important; background: #fff !important; }
  .card { border: 1px solid #999 !important; box-shadow: none !important; }
  .table { font-size: 11px !important; }
  .badge { border: 1px solid #555 !important; background: transparent !important; color: #000 !important; }
}
.planpse-lista { max-height: 260px; overflow-y: auto; }
.modal-body .table th { width: 34%; background-color: #f8f9fa; }
.session-card-grid { min-width: 320px; }
.session-card-grid tbody { display: grid !important; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)) !important; gap: 1.5rem !important; }
.session-card-grid tbody tr { display: flex !important; flex-direction: column !important; height: 100%; }
.session-card-grid tbody tr > td { display: flex !important; flex-direction: column !important; height: 100%; padding: 0 !important; border: 0 !important; }
.session-card { border: 1px solid rgba(11,28,48,.08) !important; border-radius: 16px !important; overflow: hidden; transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease; }
.session-card:hover { transform: translateY(-4px); box-shadow: 0 14px 30px rgba(0,0,0,.12) !important; border-color: rgba(0,107,41,.28) !important; }
.session-card-head { min-height: 92px; padding: 1rem; color: #fff; background: linear-gradient(135deg, #0b1c30, #155d36); }
.session-card-head .session-card-kicker { font-size: .72rem; text-transform: uppercase; letter-spacing: .08em; opacity: .78; }
.session-card-head h6 { margin: .35rem 0 0; font-size: 1.05rem; }
.session-card-body { padding: 1rem; background: #fff; }
.session-card-meta { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .65rem; margin-bottom: .8rem; }
.session-card-meta > div { min-width: 0; padding: .55rem .65rem; border-radius: .65rem; background: #f5f8fc; }
.session-card-meta small, .session-card-meta strong { display: block; overflow-wrap: anywhere; }
.session-card-meta small { color: #6c757d; font-size: .7rem; text-transform: uppercase; }
.session-card-meta strong { color: #0b1c30; font-size: .82rem; }
.session-card-description { min-height: 3.1rem; color: #3e4a3d; font-size: .85rem; }
.session-card-footer { margin-top: auto; padding: .8rem 1rem 1rem; background: #fff; border-top: 1px solid #eef1f4; }
.session-card-footer .btn { border-radius: .5rem; font-size: .78rem; }
.session-card-grid.is-list-view tbody { display: flex !important; flex-direction: column !important; }
.session-card-grid.is-list-view tbody tr { display: block !important; }
.session-card-grid.is-list-view .session-card { display: flex; flex-direction: row; min-height: 150px; }
.session-card-grid.is-list-view .session-card-head { width: 190px; min-width: 190px; }
.session-card-grid.is-list-view .session-card-body { flex: 1; }
.session-card-grid.is-list-view .session-card-footer { width: 205px; border-top: 0; border-left: 1px solid #eef1f4; }
@media (max-width: 991.98px) {
  .session-card-grid { min-width: 0; }
  .session-card-grid tbody { grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)) !important; gap: 1rem !important; }
}
@media (max-width: 767.98px) {
  #barra-herramientas { padding: .75rem !important; }
  #barra-herramientas .toolbar-left-group, #barra-herramientas .toolbar-search-sort-group { width: 100%; }
  #barra-herramientas .toolbar-left-group { justify-content: flex-start; overflow: visible; flex-wrap: wrap; }
  #barra-herramientas .btn-filter-pill { flex: 1 1 auto; justify-content: center; }
  #barra-herramientas .toolbar-search-box { min-width: 100%; max-width: 100%; }
  #barra-herramientas .toolbar-secondary-controls { width: 100%; }
  #barra-herramientas .toolbar-select-sort { min-width: 0; flex: 1 1 auto; }
  .card-body > .card-table-responsive { overflow-x: visible; }
  .session-card-grid thead { display: none !important; }
  .session-card-grid tbody { grid-template-columns: 1fr !important; gap: .9rem !important; }
  .session-card-grid.is-list-view .session-card { flex-direction: column; }
  .session-card-grid.is-list-view .session-card-head, .session-card-grid.is-list-view .session-card-footer { width: 100%; min-width: 0; }
  .session-card-grid.is-list-view .session-card-footer { border-left: 0; border-top: 1px solid #eef1f4; }
  .card-header { padding: .85rem 1rem !important; }
  .card-header h5 { font-size: 1rem; }
  .session-card-head { min-height: 82px; padding: .9rem; }
  .session-card-head h6 { font-size: .98rem; }
  .session-card-body { padding: .9rem; }
  .session-card-meta { gap: .5rem; }
  .session-card-meta > div { padding: .5rem; }
  .session-card-meta strong { font-size: .78rem; }
  .session-card-description { min-height: 0; }
  .session-card-footer { padding: .75rem .9rem .9rem; }
  .session-card-footer .btn-group { display: grid !important; grid-template-columns: repeat(2, minmax(0, 1fr)); width: 100%; gap: .45rem; }
  .session-card-footer .btn-group .btn { width: 100%; min-height: 36px; }
  .pagination { flex-wrap: wrap; gap: .25rem; }
}
@media (max-width: 575.98px) {
  .session-card-grid tbody { gap: .75rem !important; }
  .session-card { border-radius: 14px !important; }
  .session-card-meta { grid-template-columns: 1fr; }
  .session-card-footer > .d-flex { gap: .5rem; align-items: flex-start !important; }
  .session-card-footer > .d-flex .text-muted { max-width: 58%; text-align: right; }
  .modal-dialog { margin: .5rem; }
  .modal-detail-card .table { font-size: .8rem; }
  .modal-detail-card .table th { width: 36%; white-space: normal; }
}
.modal-detail-card { border: 0; border-radius: 16px; overflow: hidden; }
.modal-detail-card .table { margin: 0; }
.modal-detail-card .table th { width: 34%; background: #f5f8fc; color: #0b1c30; white-space: nowrap; }
.modal-detail-card .table td { overflow-wrap: anywhere; }
</style>

<div class="conte">
<?php echo titulo2("<i class='$icono'></i> Plan de Sesión", 2); ?>
<?php require_once("views/vscopehya.php"); ?>

<?php if (!empty($msg)): ?>
<div class="alert alert-<?= in_array($tipoMsg, ['success','danger','warning','info'], true) ? $tipoMsg : 'success'; ?> alert-dismissible fade show" role="alert">
  <i class="fa-solid <?= $tipoMsg === 'danger' ? 'fa-exclamation-triangle' : 'fa-check-circle'; ?>"></i>
  <?= htmlspecialchars($msg); ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- ============ BARRA DE ACCIONES — estilo Vvidgen ============ -->
<div id="barra-herramientas" class="filter-toolbar-container no-print">
  <div class="toolbar-left-group">
    <button type="button" class="btn-filter-pill active" onclick="nuevoPlan();"><i class="fa fa-plus"></i> Nuevo</button>
    <a href="home.php?pg=1522" class="btn-filter-pill" style="text-decoration:none;"><i class="fa-solid fa-file-invoice"></i> Reporte 1522</a>
    <div class="toolbar-divider d-none d-sm-block"></div>
    <button type="button" class="toolbar-export-btn" title="Exportar CSV" onclick="exportarPlanesCSV()"><i class="fa-solid fa-file-csv"></i></button>
  </div>
  <div class="toolbar-search-sort-group">
    <div class="toolbar-search-box"><i class="fa-solid fa-magnifying-glass icoldiz"></i><input type="text" id="busquedaPlanes" placeholder="Buscar en actividades" oninput="filtrarTarjetasPlanes(this.value)"></div>
    <select class="toolbar-select-sort" id="ordenPlanes" onchange="ordenarTarjetasPlanes(this.value)"><option value="asc">Más antiguos</option><option value="desc">Más recientes</option></select>
    <div class="toolbar-view-toggle"><button type="button" class="toolbar-view-btn active" id="btnPlanGrid" title="Vista cuadrícula" onclick="cambiarVistaPlanes('grid')"><i class="fa-solid fa-grip"></i></button><button type="button" class="toolbar-view-btn" id="btnPlanList" title="Vista lista" onclick="cambiarVistaPlanes('list')"><i class="fa-solid fa-bars"></i></button></div>
  </div>
</div>

<!-- ============ KPIs — estilo Vvidgen kpi-card (solo estilos) ============ -->
<div class="row my-4 g-4 no-print">
  <div class="col-12 col-md-6 col-xl-3">
    <div class="kpi-card h-100 d-flex flex-column justify-content-between">
      <div class="d-flex align-items-center justify-content-between">
        <div>
          <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #3e4a3d;">Actividades</span>
          <div class="d-flex align-items-baseline gap-2 mt-2">
            <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #0b1c30;"><?= (int)($estadisticas['total_actividades'] ?? 0); ?></span>
            <span style="font-size: 13px; color: #3e4a3d;">planificadas</span>
          </div>
        </div>
        <div class="kpi-icon-box" style="background-color: #b5dcfe; color: #3c627f;">
          <i class="fa-solid fa-list-check fs-4"></i>
        </div>
      </div>
      <div class="kpi-footer-strip">
        <span class="fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 11px; color: #3c627f;">
          <i class="fa-solid fa-arrow-trend-up" style="font-size: 12px;"></i> Registradas
        </span>
        <span style="font-size: 11px; color: #3e4a3d;">Total: <?= (int)($estadisticas['total_actividades'] ?? 0); ?></span>
      </div>
    </div>
  </div>
  <div class="col-12 col-md-6 col-xl-3">
    <div class="kpi-card h-100 d-flex flex-column justify-content-between">
      <div class="d-flex align-items-center justify-content-between">
        <div>
          <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #006b29;">Sesiones</span>
          <div class="d-flex align-items-baseline gap-2 mt-2">
            <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #006b29;"><?= (int)($estadisticas['total_sesiones'] ?? 0); ?></span>
            <span style="font-size: 13px; color: rgba(0, 107, 41, 0.8);">de formación</span>
          </div>
        </div>
        <div class="kpi-icon-box" style="background-color: #7afd88; color: #006b24;">
          <i class="fa-solid fa-calendar-day fs-4"></i>
        </div>
      </div>
      <div class="kpi-footer-strip">
        <span class="fw-semibold d-inline-flex align-items-center gap-2" style="font-size: 11px; color: #006b29;">
          <span class="pulse-dot"></span> Sesiones activas
        </span>
        <span style="font-size: 11px; color: #3e4a3d;">Formación</span>
      </div>
    </div>
  </div>
  <div class="col-12 col-md-6 col-xl-3">
    <div class="kpi-card h-100 d-flex flex-column justify-content-between">
      <div class="d-flex align-items-center justify-content-between">
        <div>
          <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #7a5c00;">Duración</span>
          <div class="d-flex align-items-baseline gap-2 mt-2">
            <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #0b1c30;"><?= $estadisticas['total_duracion_horas'] ?? 0; ?> hrs</span>
            <span style="font-size: 13px; color: #3e4a3d;">acumuladas</span>
          </div>
        </div>
        <div class="kpi-icon-box" style="background-color: #ffe08a; color: #7a5c00;">
          <i class="fa-solid fa-clock fs-4"></i>
        </div>
      </div>
      <div class="kpi-footer-strip">
        <span class="d-inline-flex align-items-center gap-1" style="font-size: 11px; color: #3e4a3d;">
          <i class="fa-solid fa-hourglass-half" style="font-size: 12px;"></i> <?= (int)($estadisticas['total_duracion_minutos'] ?? 0); ?> minutos
        </span>
        <span class="fw-semibold" style="font-size: 11px; color: #7a5c00;">Tiempo total</span>
      </div>
    </div>
  </div>
  <div class="col-12 col-md-6 col-xl-3">
    <div class="kpi-card h-100 d-flex flex-column justify-content-between">
      <div class="d-flex align-items-center justify-content-between">
        <div>
          <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #4a2c8a;">Fichas</span>
          <div class="d-flex align-items-baseline gap-2 mt-2">
            <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #0b1c30;"><?= (int)($estadisticas['total_fichas'] ?? 0); ?></span>
            <span style="font-size: 13px; color: #3e4a3d;">con agenda</span>
          </div>
        </div>
        <div class="kpi-icon-box" style="background-color: #e0d4ff; color: #4a2c8a;">
          <i class="fa-solid fa-users fs-4"></i>
        </div>
      </div>
      <div class="kpi-footer-strip">
        <span class="d-inline-flex align-items-center gap-1" style="font-size: 11px; color: #3e4a3d;">
          <i class="fa-solid fa-building-columns" style="font-size: 12px;"></i> Programa
        </span>
        <span class="fw-semibold" style="font-size: 11px; color: #4a2c8a;">Fichas</span>
      </div>
    </div>
  </div>
</div>



<!-- ============ FORMULARIO — estilo Vvidgen (inser) solo estilos ============ -->
<div class="inser no-print" id="card-formulario">
  <form id="frmactividad" class="filter-toolbar-container" autocomplete="off" style="flex-direction: column; align-items: stretch;">
    <h2><i class="fa-solid fa-clipboard-list"></i> <span id="titulo-formulario">Registrar Nuevo Plan de Sesión</span></h2>
    <input type="hidden" name="opera" id="opera" value="guardar_actividad">
    <input type="hidden" name="idact" id="idact" value="">
    <input type="hidden" name="opc" id="opc" value="">
    <div class="row">
      <div class="form-group col-md-4">
        <label for="frm_idfic"><i class="fa-solid fa-hashtag" style="color:#006b29;"></i> Ficha <span class="text-danger">*</span></label>
        <select class="form-control form-select" id="frm_idfic" name="idfic" onchange="cargarSesionesFicha();">
          <option value="">-- Seleccione Ficha --</option>
          <?php foreach ($datFic as $df): ?>
            <option value="<?= htmlspecialchars((string)$df['idfic']); ?>">Ficha <?= htmlspecialchars((string)$df['idfic']); ?> - <?= htmlspecialchars(mb_substr((string)($df['nomfic'] ?? ''), 0, 50)); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group col-md-4">
        <label for="frm_idses"><i class="fa-solid fa-calendar-day" style="color:#006b29;"></i> Sesión <span class="text-danger">*</span></label>
        <select class="form-control form-select" id="frm_idses" name="idses" onchange="toggleNuevaSesion();">
          <option value="">-- Seleccione Sesión --</option>
          <option value="__NUEVA__">Nueva sesión...</option>
        </select>
        <small class="text-muted" id="info-sesion"></small>
      </div>
      <div class="form-group col-md-4">
        <label for="frm_fecses"><i class="fa-regular fa-calendar" style="color:#006b29;"></i> Fecha de la sesión</label>
        <input type="date" class="form-control" id="frm_fecses" name="fecses" readonly>
      </div>
      <div class="form-group col-md-5 d-none" id="panel-nueva-sesion">
        <label for="frm_idage"><i class="fa-solid fa-list" style="color:#006b29;"></i> Agenda de la ficha</label>
        <select class="form-control form-select" id="frm_idage" name="idage">
          <option value="">-- Seleccione Agenda --</option>
        </select>
      </div>
      <div class="form-group col-md-3 d-none" id="panel-nueva-sesion-fecha">
        <label for="frm_fecses_nueva">Fecha nueva sesión <span class="text-danger">*</span></label>
        <input type="date" class="form-control" id="frm_fecses_nueva" name="fecses_nueva">
      </div>
      <div class="form-group col-md-4" id="panel-nueva-sesion-info">
        <small class="text-muted"><i class="fa-solid fa-circle-info" style="color:#006b29;"></i> Al guardar se creará la sesión sobre la agenda elegida y luego la actividad.</small>
      </div>
      <div class="form-group col-md-5">
        <label for="frm_nomact"><i class="fa-solid fa-list-check" style="color:#006b29;"></i> Actividad <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="frm_nomact" name="nomact" placeholder="Nombre de la actividad">
      </div>
      <div class="form-group col-md-7">
        <label for="frm_desact"><i class="fa-solid fa-align-left" style="color:#006b29;"></i> Descripción / Orientación</label>
        <textarea class="form-control" id="frm_desact" name="desact" rows="2" placeholder="Descripción de la actividad"></textarea>
      </div>
      <div class="form-group col-md-2">
        <label for="frm_duract"><i class="fa-regular fa-clock" style="color:#006b29;"></i> Duración (min)</label>
        <input type="number" class="form-control" id="frm_duract" name="duract" min="0" step="1" value="0">
      </div>
      <div class="form-group col-md-3">
        <label for="frm_tipact"><i class="fa-solid fa-layer-group" style="color:#006b29;"></i> Tipo / Momento</label>
        <select class="form-control form-select" id="frm_tipact" name="tipact">
          <?php if (!empty($datTipos)): foreach ($datTipos as $t): ?>
            <option value="<?= (int)($t['tipact'] ?? 0); ?>"><?= htmlspecialchars((string)($t['nomval'] ?? 'General')); ?></option>
          <?php endforeach; else: ?>
            <option value="1">General</option>
          <?php endif; ?>
        </select>
      </div>
      <div class="form-group col-md-2">
        <label for="frm_ordact"><i class="fa-solid fa-sort-numeric-asc" style="color:#006b29;"></i> Orden</label>
        <input type="number" class="form-control" id="frm_ordact" name="ordact" min="1" step="1" value="1">
      </div>
      <div class="form-group col-md-5">
        <div id="estado-formulario" class="alert alert-info d-none mb-0 py-2"><span id="mensaje-estado"></span></div>
      </div>
      <div class="form-group col-md-12 d-flex justify-content-end gap-2 flex-wrap">
        <button type="button" class="btn btn-success" style="background-color:#006b29; border-color:#006b29;" onclick="guardarActividad()"><i class="fa fa-floppy-disk"></i> Guardar actividad</button>
        <button type="button" class="btn btn-outline-secondary" onclick="limpiarFormulario()"><i class="fa fa-rotate-left"></i> Limpiar</button>
        <button type="button" class="btn btn-sm btn-light text-success" id="btn-cancelar-edicion" style="display:none;" onclick="limpiarFormulario()"><i class="fa-solid fa-xmark"></i> Cancelar edición</button>
      </div>
    </div>
  </form>
</div>

<!-- ============ FILTROS GET — funcionamiento original intacto, solo estilos Vvidgen ============ -->
<div class="inser no-print" id="card-filtros">
  <form method="GET" action="home.php" class="filter-toolbar-container" style="flex-direction: column; align-items: stretch;">
    <h2><i class="fa-solid fa-filter"></i> Filtros de Búsqueda</h2>
    <input type="hidden" name="pg" value="<?= htmlspecialchars((string)$pg); ?>">
    <div class="row">
      <div class="form-group col-md-2">
        <label for="f_idfic"><i class="fa-solid fa-hashtag" style="color:#006b29;"></i> Ficha</label>
        <select name="idfic" id="f_idfic" class="form-control form-select">
          <option value="">-- Todas --</option>
          <?php foreach ($datFic as $df): ?>
            <option value="<?= htmlspecialchars((string)$df['idfic']); ?>" <?= ($filtros['idfic'] ?? '') === (string)$df['idfic'] ? 'selected' : ''; ?>>Ficha <?= htmlspecialchars((string)$df['idfic']); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group col-md-2">
        <label for="f_idcom"><i class="fa-solid fa-book-open" style="color:#006b29;"></i> Competencia</label>
        <select name="idcom" id="f_idcom" class="form-control form-select">
          <option value="">-- Todas --</option>
          <?php foreach ($datComF as $c): ?>
            <option value="<?= htmlspecialchars((string)($c['idcom'] ?? '')); ?>" <?= ($filtros['idcom'] ?? '') === (string)($c['idcom'] ?? '') ? 'selected' : ''; ?>><?= htmlspecialchars(mb_substr((string)($c['descom'] ?? ''), 0, 40)); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group col-md-2">
        <label for="f_idres"><i class="fa-solid fa-list-check" style="color:#006b29;"></i> Resultado</label>
        <select name="idres" id="f_idres" class="form-control form-select">
          <option value="">-- Todos --</option>
          <?php foreach ($datResF as $r): ?>
            <option value="<?= htmlspecialchars((string)($r['idres'] ?? '')); ?>" <?= ($filtros['idres'] ?? '') === (string)($r['idres'] ?? '') ? 'selected' : ''; ?>><?= htmlspecialchars(mb_substr((string)($r['nomres'] ?? ''), 0, 40)); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group col-md-3">
        <label for="f_idusu"><i class="fa-solid fa-user-tie" style="color:#006b29;"></i> Instructor</label>
        <select name="idusu" id="f_idusu" class="form-control form-select">
          <option value="">-- Todos --</option>
          <?php foreach ($datIns as $i): ?>
            <option value="<?= htmlspecialchars((string)($i['idusu'] ?? '')); ?>" <?= ($filtros['idusu'] ?? '') === (string)($i['idusu'] ?? '') ? 'selected' : ''; ?>><?= htmlspecialchars(mb_substr((string)($i['nomusu'] ?? ''), 0, 35)); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group col-md-2">
        <label for="f_desde"><i class="fa-regular fa-calendar" style="color:#006b29;"></i> Desde</label>
        <input type="date" name="desde" id="f_desde" class="form-control" value="<?= htmlspecialchars((string)($filtros['desde'] ?? '')); ?>">
      </div>
      <div class="form-group col-md-1">
        <label for="f_hasta"><i class="fa-regular fa-calendar" style="color:#006b29;"></i> Hasta</label>
        <input type="date" name="hasta" id="f_hasta" class="form-control" value="<?= htmlspecialchars((string)($filtros['hasta'] ?? '')); ?>">
      </div>
      <div class="form-group col-md-4">
        <label for="f_busqueda"><i class="fa-solid fa-magnifying-glass" style="color:#006b29;"></i> Búsqueda</label>
        <input type="text" name="busqueda" id="f_busqueda" class="form-control" placeholder="Actividad, competencia, resultado, ficha, instructor..." value="<?= htmlspecialchars((string)($filtros['busqueda'] ?? '')); ?>">
      </div>
      <div class="form-group col-md-2 d-flex align-items-end gap-2">
        <button type="submit" class="btn btn-success" style="background-color:#006b29; border-color:#006b29; width:100%;"><i class="fa fa-search"></i> Consultar</button>
        <a href="home.php?pg=<?= htmlspecialchars((string)$pg); ?>" class="btn btn-outline-secondary" style="width:100%;"><i class="fa fa-rotate-left"></i> Limpiar</a>
      </div>
    </div>
  </form>
</div>

<?php if (!empty($datFichaSel)): $fs = $datFichaSel[0]; ?>
<div class="card border-0 shadow-sm rounded-3 mb-3 no-print">
  <div class="card-body p-0">
    <table class="table table-bordered mb-0" style="width: 100%; font-size: 1rem;">
      <thead><tr><th colspan="4" class="text-center text-white" style="background-color: #006b29; font-size: 1.2rem;">FICHA <?= htmlspecialchars((string)($fs['idfic'] ?? '')); ?> - <?= htmlspecialchars((string)($fs['nomfic'] ?? '')); ?></th></tr></thead>
      <tbody>
        <tr><th style="background-color: #eff4ff; color: #006b29; width: 15%;">Programa</th><td colspan="3"><?= htmlspecialchars((string)($fs['codpro'] ?? '')); ?> - <?= htmlspecialchars((string)($fs['nompro'] ?? '')); ?><?php if (!empty($fs['verpro'])): ?> Versión: <?= htmlspecialchars((string)$fs['verpro']); ?><?php endif; ?></td></tr>
        <tr><th style="background-color: #eff4ff; color: #006b29;">Jornada</th><td><?= htmlspecialchars((string)($fs['jornada'] ?? '')); ?></td><th style="background-color: #eff4ff; color: #006b29; width: 15%;">Fechas</th><td><?= htmlspecialchars((string)($fs['finific'] ?? '')); ?> a <?= htmlspecialchars((string)($fs['ffinfic'] ?? '')); ?></td></tr>
        <tr><th style="background-color: #eff4ff; color: #006b29;">Actividades</th><td><?= (int)($datPag['total'] ?? 0); ?></td><th style="background-color: #eff4ff; color: #006b29;">Horas lectiva / productiva</th><td><?= htmlspecialchars((string)($fs['horlpro'] ?? '0')); ?> h / <?= htmlspecialchars((string)($fs['horppro'] ?? '0')); ?> h</td></tr>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<!-- ============ TABLA PRINCIPAL — funcionamiento original intacto, solo estilos Vvidgen ============ -->
<div class="card border-0 shadow-sm rounded-3 mb-4 no-print">
  <div class="card-header text-white d-flex justify-content-between align-items-center flex-wrap gap-2" style="background-color: #0b1c30;">
    <h5 class="mb-0 fw-bold"><i class="fa-solid fa-table-list"></i> Cronograma y Actividades del Plan de Sesión</h5>
    <span class="badge bg-light text-dark">Total: <?= (int)($datPag['total'] ?? 0); ?> actividades</span>
  </div>
  <div class="card-body">
    <div class="table-responsive card-table-responsive">
      <table class="table session-card-grid" id="tablaPlanes" style="width:100%">
        <thead><tr><th>Actividades del plan de sesión</th></tr></thead>
        <tbody id="tbody_planes">
          <?php if (!empty($datPag['data'])): foreach ($datPag['data'] as $row): ?>
            <?php $idactRow = (int)($row['idact'] ?? 0); $json = htmlspecialchars(json_encode($row, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8'); $fechaCard = !empty($row['fecha_ses']) ? $row['fecha_ses'] : ($row['age_fechas'] ?? 'Sin fecha'); $estadoCard = (string)($row['estado'] ?? 'SIN ESTADO'); $estadoClase = $estadoCard === 'COMPLETADA' ? 'bg-success' : ($estadoCard === 'EN_CURSO' ? 'bg-warning text-dark' : 'bg-light text-dark'); ?>
            <tr data-detalle="<?= $json; ?>" data-idact="<?= $idactRow; ?>" data-search="<?= htmlspecialchars(strtolower(($row['nomact'] ?? '').' '.($row['descom'] ?? '').' '.($row['nompro'] ?? '').' '.($row['nomusu'] ?? '').' '.($row['idfic'] ?? ''))); ?>">
              <td>
                <div class="card h-100 session-card">
                  <div class="session-card-head"><div class="session-card-kicker">Plan de sesión · Ficha <?= htmlspecialchars((string)($row['idfic'] ?? '')); ?></div><h6><i class="fa-regular fa-calendar-days me-1"></i><?= htmlspecialchars((string)$fechaCard); ?></h6><small class="opacity-75"><?= !empty($row['ses_real']) ? 'Sesión #' . (int)($row['ses_ord'] ?? 0) : 'Rango de agenda'; ?></small></div>
                  <div class="session-card-body"><h6 class="fw-bold mb-2"><?= htmlspecialchars((string)($row['nomact'] ?? 'Actividad')); ?></h6><div class="session-card-meta"><div><small>Programa</small><strong><?= htmlspecialchars(mb_substr((string)($row['nompro'] ?? $row['nomfic'] ?? 'N/A'), 0, 38)); ?></strong></div><div><small>Duración</small><strong><i class="fa-regular fa-clock me-1"></i><?= (int)($row['duract'] ?? 0); ?> min</strong></div><div><small>Competencia</small><strong><?= htmlspecialchars(mb_substr((string)($row['descom'] ?? 'Sin competencia'), 0, 38)); ?></strong></div><div><small>Resultado</small><strong><?= htmlspecialchars(mb_substr((string)($row['nomres'] ?? 'Sin resultado'), 0, 38)); ?></strong></div></div><p class="session-card-description mb-0"><?= htmlspecialchars(mb_substr((string)($row['desact'] ?? 'Sin descripción'), 0, 140)); ?></p></div>
                  <div class="session-card-footer"><div class="d-flex justify-content-between align-items-center mb-2"><span class="badge <?= $estadoClase; ?>"><?= htmlspecialchars($estadoCard); ?></span><span class="text-muted small"><i class="fa-solid fa-user-tie me-1"></i><?= htmlspecialchars(mb_substr((string)($row['nomusu'] ?? 'Sin instructor'), 0, 28)); ?></span></div><div class="btn-group btn-group-sm w-100"><button type="button" class="btn btn-outline-info" title="Ver detalle" onclick="verDetalle(this)"><i class="fa-solid fa-eye"></i> Ver</button><?php if ($idactRow > 0): ?><button type="button" class="btn btn-outline-primary" title="Editar" onclick="editarActividad(this)"><i class="fa-solid fa-edit"></i></button><button type="button" class="btn btn-outline-danger" title="Eliminar" onclick="eliminarActividad(<?= $idactRow; ?>)"><i class="fa-solid fa-trash"></i></button><?php else: ?><button type="button" class="btn btn-outline-success" title="Registrar actividad" onclick="registrarActividad(this)"><i class="fa-solid fa-plus"></i></button><?php endif; ?></div></div>
                </div>
              </td>
            </tr>
          <?php endforeach; else: ?>
            <tr><td class="text-center text-muted py-4">No se encontraron actividades de sesión con los filtros indicados.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
    <?php if ((int)($datPag['total_paginas'] ?? 1) > 1): ?><nav class="no-print mt-3"><ul class="pagination justify-content-center mb-0"><?php if ($plsPage > 1): ?><li class="page-item"><a class="page-link" href="home.php?pg=<?= htmlspecialchars((string)$pg); ?>&<?= pls_qs(['pls_page' => $plsPage - 1]); ?>">&laquo;</a></li><?php endif; ?><?php for ($p = max(1, $plsPage - 2); $p <= min((int)$datPag['total_paginas'], $plsPage + 2); $p++): ?><li class="page-item <?= $p === $plsPage ? 'active' : ''; ?>"><a class="page-link" href="home.php?pg=<?= htmlspecialchars((string)$pg); ?>&<?= pls_qs(['pls_page' => $p]); ?>"><?= $p; ?></a></li><?php endfor; ?><?php if ($plsPage < (int)$datPag['total_paginas']): ?><li class="page-item"><a class="page-link" href="home.php?pg=<?= htmlspecialchars((string)$pg); ?>&<?= pls_qs(['pls_page' => $plsPage + 1]); ?>">&raquo;</a></li><?php endif; ?></ul></nav><?php endif; ?>
  </div>
</div>

<!-- ============ MODAL DETALLE — estilo Vvidgen ============ -->
<div class="modal fade" id="modalDetallePlan" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content modal-detail-card shadow-lg border-0" style="border-radius: 16px; overflow: hidden;">
      <div class="modal-header text-white border-0 px-4 py-3" style="background-color: #0b1c30;">
        <h5 class="modal-title fw-bold"><i class="fa-solid fa-eye" style="color:#7afd88;"></i> Detalle del Plan de Sesión</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="modal-detalle-plan-body"></div>
      <div class="modal-footer bg-light border-0">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fa fa-xmark"></i> Cerrar</button>
      </div>
    </div>
  </div>
</div>

</div><!-- /.conte -->

<script>
var PLS_CTRL = 'controllers/horcplss.php';
var PG_PLS = <?= json_encode((string)$pg); ?>;
var sesionesCache = [];
var agendasCache = [];
var plsCargando = false;

function escapeHtml(t) {
  if (t === null || t === undefined) return '';
  return String(t).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;').replace(/'/g, '&#039;');
}

function leerFila(btn) {
  var tr = btn.closest('tr');
  try { return JSON.parse(tr.getAttribute('data-detalle') || '{}'); } catch (e) { return {}; }
}

function mostrarEstadoPls(msg, tipo) {
  var caja = document.getElementById('estado-formulario');
  var span = document.getElementById('mensaje-estado');
  if (!caja || !span) return;
  caja.classList.remove('d-none', 'alert-info', 'alert-danger', 'alert-success');
  caja.classList.add('alert-' + (tipo || 'info'));
  span.textContent = msg || '';
  caja.classList.remove('d-none');
}

function cargarSesionesFicha(idsesSeleccionar) {
  if (plsCargando) return;
  plsCargando = true;
  var idfic = document.getElementById('frm_idfic').value;
  var selSes = document.getElementById('frm_idses');
  var selAge = document.getElementById('frm_idage');
  sesionesCache = []; agendasCache = [];
  selSes.innerHTML = '<option value="">Seleccione la sesión</option><option value="__NUEVA__">➕ Nueva sesión...</option>';
  selAge.innerHTML = '<option value="">Seleccione la agenda</option>';
  document.getElementById('info-sesion').textContent = '';
  if (!idfic) { toggleNuevaSesion(); plsCargando = false; return; }
  fetch(PLS_CTRL + '?opera=get_sesiones_ficha&idfic=' + encodeURIComponent(idfic))
    .then(function (r) {
      if (r.status === 401) throw new Error('Sesión expirada');
      return r.json();
    })
    .then(function (data) {
      if (!data.success) { mostrarEstadoPls(data.message || 'No se pudieron cargar las sesiones.', 'danger'); return; }
      sesionesCache = data.sesiones || [];
      agendasCache = data.agendas || [];
      selSes.innerHTML = '<option value="">Seleccione la sesión</option>';
      sesionesCache.forEach(function (s) {
        var o = document.createElement('option');
        o.value = s.idses;
        o.textContent = 'Sesión ' + s.idses + ' - ' + s.fecses;
        selSes.appendChild(o);
      });
      var oNueva = document.createElement('option');
      oNueva.value = '__NUEVA__';
      oNueva.textContent = '➕ Nueva sesión...';
      selSes.appendChild(oNueva);
      selAge.innerHTML = '<option value="">Seleccione la agenda</option>';
      agendasCache.forEach(function (a) {
        var o = document.createElement('option');
        o.value = a.idage;
        o.textContent = 'Agenda ' + a.idage + (a.actpro ? (' - ' + a.actpro.substring(0, 30)) : '');
        selAge.appendChild(o);
      });
      if (sesionesCache.length === 0) {
        document.getElementById('info-sesion').textContent = 'No hay sesiones para esta ficha. Use "Nueva sesión..."';
      }
      if (idsesSeleccionar) {
        for (var i = 0; i < selSes.options.length; i++) {
          if (String(selSes.options[i].value) === String(idsesSeleccionar)) { selSes.selectedIndex = i; break; }
        }
      }
      toggleNuevaSesion();
    })
    .catch(function (e) {
      if (e && e.message === 'Sesión expirada') mostrarEstadoPls('Sesión expirada. Recargue la página.', 'danger');
    })
    .finally(function(){ plsCargando = false; });
}

function toggleNuevaSesion() {
  var val = document.getElementById('frm_idses').value;
  var show = val === '__NUEVA__';
  var p1 = document.getElementById('panel-nueva-sesion');
  var p2 = document.getElementById('panel-nueva-sesion-fecha');
  var p3 = document.getElementById('panel-nueva-sesion-info');
  if (p1) p1.classList.toggle('d-none', !show);
  if (p2) p2.classList.toggle('d-none', !show);
  if (p3) p3.classList.toggle('d-none', !show);
}

function limpiarFormulario() {
  var frm = document.getElementById('frmactividad');
  if (frm) frm.reset();
  document.getElementById('idact').value = '';
  document.getElementById('opc').value = '';
  document.getElementById('titulo-formulario').textContent = 'Registrar Nuevo Plan de Sesión';
  document.getElementById('btn-cancelar-edicion').style.display = 'none';
  var p1 = document.getElementById('panel-nueva-sesion'); if (p1) p1.classList.add('d-none');
  var p2 = document.getElementById('panel-nueva-sesion-fecha'); if (p2) p2.classList.add('d-none');
  var p3 = document.getElementById('panel-nueva-sesion-info'); if (p3) p3.classList.add('d-none');
  document.getElementById('info-sesion').textContent = '';
  var caja = document.getElementById('estado-formulario');
  if (caja) { caja.classList.add('d-none'); }
}

function nuevoPlan() {
  limpiarFormulario();
  var el = document.getElementById('card-formulario');
  if (el) el.scrollIntoView({ behavior: 'smooth' });
}

function guardarActividad() {
  var idfic = document.getElementById('frm_idfic').value;
  var idses = document.getElementById('frm_idses').value;
  var nomact = document.getElementById('frm_nomact').value.trim();
  var idact = document.getElementById('idact').value;
  if (!idfic) { mostrarEstadoPls('Debe seleccionar la ficha.', 'danger'); return; }
  if (!nomact) { mostrarEstadoPls('El nombre de la actividad es obligatorio.', 'danger'); return; }
  var opc = '';
  if (idses === '__NUEVA__') {
    opc = 'nueva_sesion';
    if (!document.getElementById('frm_idage').value) { mostrarEstadoPls('Debe seleccionar la agenda para la nueva sesión.', 'danger'); return; }
    if (!document.getElementById('frm_fecses_nueva').value) { mostrarEstadoPls('Debe indicar la fecha de la nueva sesión.', 'danger'); return; }
  } else if (!idses) {
    mostrarEstadoPls('Debe seleccionar o crear una sesión.', 'danger'); return;
  }
  var formData = new FormData();
  formData.append('opera', 'guardar_actividad');
  formData.append('idact', idact || '');
  formData.append('idfic', idfic || '');
  formData.append('idses', idses === '__NUEVA__' ? '' : idses);
  formData.append('nomact', nomact);
  formData.append('desact', document.getElementById('frm_desact').value.trim());
  formData.append('duract', document.getElementById('frm_duract').value || '0');
  formData.append('tipact', document.getElementById('frm_tipact').value || '1');
  formData.append('ordact', document.getElementById('frm_ordact').value || '1');
  formData.append('opc', opc);
  if (opc === 'nueva_sesion') {
    formData.append('idage', document.getElementById('frm_idage').value);
    formData.append('fecses', document.getElementById('frm_fecses_nueva').value);
  }
  fetch(PLS_CTRL, { method: 'POST', body: formData })
    .then(function (r) { return r.json(); })
    .then(function (data) {
      if (data.success) {
        mostrarEstadoPls(data.message || 'Guardado correctamente.', 'success');
        setTimeout(function () { window.location.href = 'home.php?pg=' + encodeURIComponent(PG_PLS); }, 700);
      } else {
        mostrarEstadoPls(data.message || 'No fue posible guardar.', 'danger');
      }
    })
    .catch(function () { mostrarEstadoPls('Error de conexión.', 'danger'); });
}

function eliminarActividad(idact) {
  if (!confirm('¿Está seguro de eliminar esta actividad del plan de sesión?')) return;
  var formData = new FormData();
  formData.append('opera', 'eliminar_actividad');
  formData.append('idact', idact);
  fetch(PLS_CTRL, { method: 'POST', body: formData })
    .then(function (r) { return r.json(); })
    .then(function (data) {
      if (data.success) { window.location.href = 'home.php?pg=' + encodeURIComponent(PG_PLS); }
      else { mostrarEstadoPls(data.message || 'No fue posible eliminar.', 'danger'); }
    })
    .catch(function () {});
}

function editarActividad(btn) {
  var row = leerFila(btn);
  document.getElementById('titulo-formulario').textContent = 'Editar Actividad del Plan de Sesión';
  document.getElementById('btn-cancelar-edicion').style.display = 'inline-block';
  document.getElementById('idact').value = row.idact || '';
  document.getElementById('frm_nomact').value = row.nomact || '';
  document.getElementById('frm_desact').value = row.desact || '';
  document.getElementById('frm_duract').value = row.duract || '0';
  document.getElementById('frm_ordact').value = row.ordact || '1';
  if (row.tipact) { document.getElementById('frm_tipact').value = row.tipact; }
  document.getElementById('frm_idfic').value = row.idfic || '';
  if (row.fecses) { document.getElementById('frm_fecses').value = row.fecses; }
  cargarSesionesFicha(row.idses || null);
  document.getElementById('card-formulario').scrollIntoView({ behavior: 'smooth' });
}

function registrarActividad(btn) {
  var row = leerFila(btn);
  limpiarFormulario();
  document.getElementById('frm_idfic').value = row.idfic || '';
  if (row.fecses) { document.getElementById('frm_fecses').value = row.fecses; }
  cargarSesionesFicha(row.idses || null);
  document.getElementById('card-formulario').scrollIntoView({ behavior: 'smooth' });
}

function cambiarVistaPlanes(modo) {
  var tabla = document.getElementById('tablaPlanes');
  if (tabla) tabla.classList.toggle('is-list-view', modo === 'list');
  var grid = document.getElementById('btnPlanGrid');
  var list = document.getElementById('btnPlanList');
  if (grid) grid.classList.toggle('active', modo !== 'list');
  if (list) list.classList.toggle('active', modo === 'list');
}

function filtrarTarjetasPlanes(texto) {
  var consulta = String(texto || '').toLowerCase().trim();
  document.querySelectorAll('#tbody_planes tr').forEach(function (fila) {
    fila.style.display = !consulta || (fila.getAttribute('data-search') || '').indexOf(consulta) !== -1 ? '' : 'none';
  });
}

function ordenarTarjetasPlanes(direccion) {
  var cuerpo = document.getElementById('tbody_planes');
  if (!cuerpo) return;
  var filas = Array.from(cuerpo.querySelectorAll('tr'));
  filas.sort(function (a, b) {
    var fa = a.querySelector('.session-card-head h6');
    var fb = b.querySelector('.session-card-head h6');
    return (fa ? fa.textContent : '').localeCompare(fb ? fb.textContent : '', undefined, { numeric: true }) * (direccion === 'asc' ? 1 : -1);
  });
  filas.forEach(function (fila) { cuerpo.appendChild(fila); });
}

function exportarPlanesCSV() {
  var filas = [['Fecha', 'Ficha', 'Programa', 'Competencia', 'Resultado', 'Actividad', 'Duracion', 'Estado']];
  document.querySelectorAll('#tbody_planes tr').forEach(function (fila) {
    if (fila.style.display === 'none') return;
    try {
      var row = JSON.parse(fila.getAttribute('data-detalle') || '{}');
      filas.push([row.fecha_ses || row.age_fechas || '', row.idfic || '', row.nompro || row.nomfic || '', row.descom || '', row.nomres || '', row.nomact || '', (row.duract || 0) + ' min', row.estado || '']);
    } catch (e) {}
  });
  var csv = filas.map(function (fila) { return fila.map(function (valor) { return '"' + String(valor).replace(/"/g, '""') + '"'; }).join(','); }).join('\\n');
  var enlace = document.createElement('a');
  enlace.href = URL.createObjectURL(new Blob(['\\ufeff' + csv], { type: 'text/csv;charset=utf-8;' }));
  enlace.download = 'plan-de-sesion.csv';
  enlace.click();
  URL.revokeObjectURL(enlace.href);
}

function verDetalle(btn) {
  var row = leerFila(btn);
  var h = '';
  if (row.ses_real) {
    h += fila('Fecha sesión', row.fecha_ses || 'Sin fecha');
    h += fila('Sesión N° (agenda)', row.ses_ord || row.idses);
  } else if (row.age_fechas) {
    h += fila('Rango agenda', row.age_fechas);
  } else {
    h += fila('Fecha', 'Sin fecha programada');
  }
  h += fila('Ficha', (row.idfic || '') + ' - ' + (row.nomfic || ''));
  if (row.idage) { h += fila('Agenda', 'Agenda #' + row.idage + (row.actpro ? (' - ' + row.actpro) : '')); }
  if (row.nompro) { h += fila('Programa', row.nompro); }
  h += fila('Competencia', row.tiene_competencia ? (row.descom || 'Sin competencia') : 'Sin competencia registrada');
  h += fila('Resultado de Aprendizaje', row.tiene_resultado ? (row.nomres || 'Sin resultado') : 'Sin resultado registrado');
  h += fila('Actividad', row.nomact || 'Actividad');
  h += fila('Descripción', row.desact || 'Sin descripción');
  h += fila('Duración', (row.duract || 0) + ' min');
  if (row.fas) { h += fila('Fase / Momento', row.fas); }
  if (row.estado) { h += fila('Estado', row.estado); }
  if (row.titulo_sesion) { h += fila('Título de la sesión', row.titulo_sesion); }
  if (row.cont) { h += fila('Contenidos', row.cont); }
  if (row.matfor) { h += fila('Materiales / Recursos', row.matfor); }
  h += fila('Instructor', row.tiene_instructor ? ((row.nomusu || 'Instructor') + (row.ndocusu ? (' - Doc. ' + row.ndocusu) : '')) : 'Sin instructor asignado');
  h += fila('Fecha Inicio', row.age_fecini || 'Sin fecha');
  h += fila('Fecha Final', row.age_fecfin || 'Sin fecha');
  document.getElementById('modal-detalle-plan-body').innerHTML = '<table class="table table-bordered table-sm">' + h + '</table>';
  var modalEl = document.getElementById('modalDetallePlan');
  var modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
  modal.show();
}

function fila(titulo, valor) {
  return '<tr><th style="width:34%;">' + escapeHtml(titulo) + '</th><td>' + escapeHtml(valor) + '</td></tr>';
}
</script>
