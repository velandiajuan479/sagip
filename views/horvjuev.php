<?php require_once 'controllers/horcjuev.php'; ?>
<?php
$pg = isset($pg) ? $pg : '2414';
$icono = isset($icono) ? $icono : 'fa-solid fa-gavel';
// INTEGRACIÓN CON EL MÓDULO: la página 2414 tiene mospas=1, por lo que home.php
// ejecuta ocul(mospas, est) y titulo2() debe imprimir los botones #mas / #menos.
// Antes se pasaba 2 fijo: ocul() buscaba #mas/#menos inexistentes (error JS) y el
// formulario #frmins quedaba oculto sin forma de mostrarlo.
$mosPagina = isset($mos) ? (int)$mos : 2;
$plsToggleForm = ($mosPagina === 1);
?>
<style>
@media print {
  .no-print, header, nav, footer, .header-home, .btn, .card-header .btn,
  .alert-dismissible, #card-formulario, #card-filtros, #card-importacion, .filter-toolbar-container { display: none !important; }
  body, .contenido, .contenido2 { margin: 0 !important; padding: 0 !important; background: #fff !important; }
  .card { border: 1px solid #999 !important; box-shadow: none !important; }
  .table { font-size: 11px !important; }
  .badge { border: 1px solid #555 !important; background: transparent !important; color: #000 !important; }
}
.jui-lista { max-height: 260px; overflow-y: auto; }
.modal-body .table th { width: 34%; background-color: #f8f9fa; }
.jui-card-grid { min-width: 320px; }
.jui-card-grid tbody { display: grid !important; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)) !important; gap: 1.5rem !important; }
.jui-card-grid tbody tr { display: flex !important; flex-direction: column !important; height: 100%; }
.jui-card-grid tbody tr > td { display: flex !important; flex-direction: column !important; height: 100%; padding: 0 !important; border: 0 !important; }
.jui-card { border: 1px solid rgba(11,28,48,.08) !important; border-radius: 16px !important; overflow: hidden; transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease; }
.jui-card:hover { transform: translateY(-4px); box-shadow: 0 14px 30px rgba(0,0,0,.12) !important; border-color: rgba(0,107,41,.28) !important; }
.jui-card-head { min-height: 92px; padding: 1rem; color: #fff; background: linear-gradient(135deg, #0b1c30, #006b29); }
.jui-card-head .jui-card-kicker { font-size: .72rem; text-transform: uppercase; letter-spacing: .08em; opacity: .78; }
.jui-card-head h6 { margin: .35rem 0 0; font-size: 1.05rem; overflow-wrap: anywhere; }
.jui-card-body { padding: 1rem; background: #fff; }
.jui-card-meta { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .65rem; margin-bottom: .8rem; }
.jui-card-meta > div { min-width: 0; padding: .55rem .65rem; border-radius: .65rem; background: #f5f8fc; }
.jui-card-meta small, .jui-card-meta strong { display: block; overflow-wrap: anywhere; }
.jui-card-meta small { color: #6c757d; font-size: .7rem; text-transform: uppercase; }
.jui-card-meta strong { color: #0b1c30; font-size: .82rem; }
.jui-card-footer { padding: .8rem 1rem 1rem; background: #fff; border-top: 1px solid #eef1f4; }
.jui-card-footer .btn { border-radius: .5rem; font-size: .78rem; }
.jui-card-grid.is-list-view tbody { display: flex !important; flex-direction: column !important; }
.jui-card-grid.is-list-view tbody tr { display: block !important; }
.jui-card-grid.is-list-view .jui-card { display: flex; flex-direction: row; min-height: 150px; }
.jui-card-grid.is-list-view .jui-card-head { width: 190px; min-width: 190px; }
.jui-card-grid.is-list-view .jui-card-body { flex: 1; }
.jui-card-grid.is-list-view .jui-card-footer { width: 205px; border-top: 0; border-left: 1px solid #eef1f4; }
@media (max-width: 991.98px) {
  .jui-card-grid { min-width: 0; }
  .jui-card-grid tbody { grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)) !important; gap: 1rem !important; }
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
  .jui-card-grid thead { display: none !important; }
  .jui-card-grid tbody { grid-template-columns: 1fr !important; gap: .9rem !important; }
  .jui-card-grid.is-list-view .jui-card { flex-direction: column; }
  .jui-card-grid.is-list-view .jui-card-head, .jui-card-grid.is-list-view .jui-card-footer { width: 100%; min-width: 0; }
  .jui-card-grid.is-list-view .jui-card-footer { border-left: 0; border-top: 1px solid #eef1f4; }
  .card-header { padding: .85rem 1rem !important; }
  .card-header h5 { font-size: 1rem; }
  .jui-card-head { min-height: 82px; padding: .9rem; }
  .jui-card-head h6 { font-size: .98rem; }
  .jui-card-body { padding: .9rem; }
  .jui-card-meta { gap: .5rem; }
  .jui-card-meta > div { padding: .5rem; }
  .jui-card-meta strong { font-size: .78rem; }
  .jui-card-footer { padding: .75rem .9rem .9rem; }
  .jui-card-footer .btn-group { display: grid !important; grid-template-columns: repeat(2, minmax(0, 1fr)); width: 100%; gap: .45rem; }
  .jui-card-footer .btn-group .btn { width: 100%; min-height: 36px; }
  .pagination { flex-wrap: wrap; gap: .25rem; }
}
@media (max-width: 575.98px) {
  .jui-card-grid tbody { gap: .75rem !important; }
  .jui-card { border-radius: 14px !important; }
  .jui-card-meta { grid-template-columns: 1fr; }
  .jui-card-body > .d-flex { gap: .5rem; align-items: flex-start !important; }
  .jui-card-body > .d-flex small { max-width: 48%; text-align: right; }
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
<?php echo titulo2("<i class='$icono'></i> Juicio de Evaluación", $mosPagina); ?>
<?php require_once("views/vscopehya.php"); ?>

<?php if (!empty($msgSave)): ?>
<div class="alert alert-<?= in_array($tipoMsg, ['danger','info','warning'], true) ? $tipoMsg : 'success'; ?> alert-dismissible fade show" role="alert">
  <i class="fa-solid <?= $tipoMsg === 'danger' ? 'fa-exclamation-triangle' : ($tipoMsg === 'info' ? 'fa-circle-info' : 'fa-check-circle'); ?>"></i>
  <?= htmlspecialchars($msgSave); ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- ============ BARRA DE ACCIONES — estilo Vvidgen (filter-toolbar-container) ============ -->
<div id="barra-herramientas" class="filter-toolbar-container no-print" style="padding: 0.75rem 1rem;">
  <div class="toolbar-left-group">
    <a href="#frmins" <?= $plsToggleForm ? 'onclick="ocul(1,1);"' : ''; ?> class="btn-filter-pill active" style="text-decoration:none; background-color: #006b29; color:#fff;"><i class="fa fa-user-plus"></i> Nuevo juicio</a>
    <a href="#frmimport" class="btn-filter-pill" style="text-decoration:none;"><i class="fa-solid fa-file-import"></i> Importar CSV</a>
    <div class="toolbar-divider d-none d-sm-block"></div>
    <button type="button" class="toolbar-export-btn" title="Exportar CSV" onclick="exportarCSV()"><i class="fa-solid fa-file-csv"></i></button>
  </div>
  <div class="toolbar-search-sort-group">
    <div class="toolbar-search-box"><i class="fa-solid fa-magnifying-glass icoldiz"></i><input type="text" id="busquedaJuicios" placeholder="Buscar juicios" oninput="filtrarTarjetasJuicios(this.value)"></div>
    <select class="toolbar-select-sort" id="ordenJuicios" onchange="ordenarTarjetasJuicios(this.value)"><option value="asc">Más antiguos</option><option value="desc">Más recientes</option></select>
    <div class="toolbar-view-toggle"><button type="button" class="toolbar-view-btn active" id="btnJuicioGrid" title="Vista cuadrícula" onclick="cambiarVistaJuicios('grid')"><i class="fa-solid fa-grip"></i></button><button type="button" class="toolbar-view-btn" id="btnJuicioList" title="Vista lista" onclick="cambiarVistaJuicios('list')"><i class="fa-solid fa-bars"></i></button></div>
  </div>
</div>

<!-- ============ KPIs — estilo Vvidgen kpi-card (solo estilos) ============ -->
<div class="row my-4 g-4 no-print">
  <div class="col-12 col-md-6 col-xl-3">
    <div class="kpi-card h-100 d-flex flex-column justify-content-between" role="button" tabindex="0" style="cursor:pointer;" title="Ver todos los juicios" onclick="filtrarKpiJuicio('TODOS')" onkeydown="if(event.key==='Enter'){filtrarKpiJuicio('TODOS');}">
      <div class="d-flex align-items-center justify-content-between">
        <div>
          <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #3e4a3d;">Total en Sistema</span>
          <div class="d-flex align-items-baseline gap-2 mt-2">
            <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #0b1c30;"><?= (int)($estadisticas['total'] ?? 0); ?></span>
            <span style="font-size: 13px; color: #3e4a3d;">juicios</span>
          </div>
        </div>
        <div class="kpi-icon-box" style="background-color: #b5dcfe; color: #3c627f;">
          <i class="fa-solid fa-layer-group fs-4"></i>
        </div>
      </div>
      <div class="kpi-footer-strip">
        <span class="fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 11px; color: #3c627f;">
          <i class="fa-solid fa-arrow-trend-up" style="font-size: 12px;"></i> Registrados
        </span>
        <span style="font-size: 11px; color: #3e4a3d;">Disponibles: 100%</span>
      </div>
    </div>
  </div>
  <div class="col-12 col-md-6 col-xl-3">
    <div class="kpi-card h-100 d-flex flex-column justify-content-between" role="button" tabindex="0" style="cursor:pointer;" title="Ver aprobados" onclick="filtrarKpiJuicio('APROBADO')" onkeydown="if(event.key==='Enter'){filtrarKpiJuicio('APROBADO');}">
      <div class="d-flex align-items-center justify-content-between">
        <div>
          <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #006b29;">Aprobados</span>
          <div class="d-flex align-items-baseline gap-2 mt-2">
            <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #006b29;"><?= (int)($estadisticas['aprobados'] ?? 0); ?></span>
            <span style="font-size: 13px; color: rgba(0, 107, 41, 0.8);">en sistema</span>
          </div>
        </div>
        <div class="kpi-icon-box" style="background-color: #7afd88; color: #006b24;">
          <i class="fa-solid fa-circle-check fs-4"></i>
        </div>
      </div>
      <div class="kpi-footer-strip">
        <span class="fw-semibold d-inline-flex align-items-center gap-2" style="font-size: 11px; color: #006b29;">
          <span class="pulse-dot"></span> Juicios OK
        </span>
        <span style="font-size: 11px; color: #3e4a3d;">Aprobados</span>
      </div>
    </div>
  </div>
  <div class="col-12 col-md-6 col-xl-3">
    <div class="kpi-card h-100 d-flex flex-column justify-content-between" role="button" tabindex="0" style="cursor:pointer;" title="Ver no aprobados" onclick="filtrarKpiJuicio('NO APROBADO')" onkeydown="if(event.key==='Enter'){filtrarKpiJuicio('NO APROBADO');}">
      <div class="d-flex align-items-center justify-content-between">
        <div>
          <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #ba1a1a;">No Aprobados</span>
          <div class="d-flex align-items-baseline gap-2 mt-2">
            <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #0b1c30;"><?= (int)($estadisticas['no_aprobados'] ?? 0); ?></span>
            <span style="font-size: 13px; color: #3e4a3d;">en revisión</span>
          </div>
        </div>
        <div class="kpi-icon-box" style="background-color: #ffdad6; color: #ba1a1a;">
          <i class="fa-solid fa-circle-xmark fs-4"></i>
        </div>
      </div>
      <div class="kpi-footer-strip">
        <span class="d-inline-flex align-items-center gap-1" style="font-size: 11px; color: #3e4a3d;">
          <i class="fa-solid fa-triangle-exclamation" style="font-size: 12px;"></i> Requiere plan
        </span>
        <span class="fw-semibold" style="font-size: 11px; color: #ba1a1a;">No aprobados</span>
      </div>
    </div>
  </div>
  <div class="col-12 col-md-6 col-xl-3">
    <div class="kpi-card h-100 d-flex flex-column justify-content-between" role="button" tabindex="0" style="cursor:pointer;" title="Ver pendientes por evaluar" onclick="filtrarKpiJuicio('PENDIENTES')" onkeydown="if(event.key==='Enter'){filtrarKpiJuicio('PENDIENTES');}">
      <div class="d-flex align-items-center justify-content-between">
        <div>
          <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #7a5c00;">Por Evaluar</span>
          <div class="d-flex align-items-baseline gap-2 mt-2">
            <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #0b1c30;"><?= (int)($estadisticas['por_evaluar'] ?? 0); ?></span>
            <span style="font-size: 13px; color: #3e4a3d;">pendientes</span>
          </div>
        </div>
        <div class="kpi-icon-box" style="background-color: #ffe08a; color: #7a5c00;">
          <i class="fa-solid fa-hourglass-half fs-4"></i>
        </div>
      </div>
      <div class="kpi-footer-strip">
        <span class="d-inline-flex align-items-center gap-1" style="font-size: 11px; color: #3e4a3d;">
          <i class="fa-solid fa-clock" style="font-size: 12px;"></i> En espera
        </span>
        <span class="fw-semibold" style="font-size: 11px; color: #7a5c00;">Por evaluar</span>
      </div>
    </div>
  </div>
</div>



<!-- ============ EDICIÓN RÁPIDA ============ -->
<div class="card border-0 shadow-sm rounded-3 mb-4 no-print" id="card-edicion-rapida" style="display:none;">
  <div class="card-header text-white fw-bold" style="background-color: #0b1c30;">
    <i class="fa-solid fa-bolt"></i> Edición Rápida de Calificación
  </div>
  <div class="card-body">
    <div class="row g-3 align-items-center">
      <div class="col-12 col-md-8">
        <p class="mb-1"><strong>Estudiante:</strong> <span id="estudiante-edicion"></span></p>
        <p class="mb-1"><strong>Competencia:</strong> <span id="competencia-edicion"></span></p>
        <p class="mb-1"><strong>Resultado:</strong> <span id="resultado-edicion"></span></p>
        <p class="mb-0"><strong>Calificación actual:</strong> <span id="calificacion-actual" class="badge bg-secondary">—</span></p>
      </div>
      <div class="col-12 col-md-4 text-center">
        <p class="fw-bold mb-2">Nueva calificación:</p>
        <div class="d-grid gap-2">
          <button type="button" class="btn btn-outline-success" onclick="cambiarCalificacion('APROBADO')"><i class="fa-solid fa-check"></i> Aprobado</button>
          <button type="button" class="btn btn-outline-danger" onclick="cambiarCalificacion('NO APROBADO')"><i class="fa-solid fa-times"></i> No Aprobado</button>
          <button type="button" class="btn btn-outline-warning" onclick="cambiarCalificacion('POR EVALUAR')"><i class="fa-solid fa-hourglass-half"></i> Por Evaluar</button>
          <button type="button" class="btn btn-outline-secondary btn-sm mt-1" onclick="cancelarEdicion()"><i class="fa-solid fa-xmark"></i> Cancelar</button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ============ FORMULARIO MANUAL — estilo Vvidgen (inser) solo estilos ============ -->
<div class="inser no-print" id="card-formulario">
  <form id="frmins" class="filter-toolbar-container" action="home.php?pg=<?= htmlspecialchars($pg); ?>" method="POST" enctype="multipart/form-data" style="flex-direction: column; align-items: stretch;">
    <h2><i class="fa-solid fa-user-plus"></i> <?= isset($datOne) && !empty($datOne['idjui']) ? 'Editar Juicio de Evaluación' : 'Registro Manual de Juicio'; ?></h2>
    <input type="hidden" name="opera" id="opera" value="<?= isset($datOne) && !empty($datOne['idjui']) ? 'edit' : 'save'; ?>">
    <input type="hidden" name="csrf_token" id="csrf_token" value="<?= htmlspecialchars($csrfToken ?? ''); ?>">
    <input type="hidden" name="idjui" id="idjui" value="<?= htmlspecialchars($datOne['idjui'] ?? ''); ?>">
    <input type="hidden" name="idusu" id="idusu" value="<?= htmlspecialchars($datOne['idusu'] ?? ''); ?>">
    <input type="hidden" name="tidoc" id="tidoc" value="CC">
    <div class="row">
      <div class="form-group col-md-3">
        <label for="ndoce"><i class="fa-solid fa-id-card" style="color:#006b29;"></i> Número de Documento <span class="text-danger">*</span></label>
        <div class="input-group">
          <input type="text" class="form-control" id="ndoce" name="ndoce" required value="<?= htmlspecialchars($datOne['ndoce'] ?? ''); ?>">
          <button class="btn btn-outline-secondary" type="button" onclick="buscarEstudiante()" title="Buscar aprendiz"><i class="fa-solid fa-search"></i></button>
        </div>
        <small class="text-muted">Enter o botón para buscar</small>
      </div>
      <div class="form-group col-md-3">
        <label for="nomes"><i class="fa-solid fa-user" style="color:#006b29;"></i> Nombres</label>
        <input type="text" class="form-control" id="nomes" name="nomes" readonly value="<?= htmlspecialchars($datOne['nomes'] ?? ''); ?>">
      </div>
      <div class="form-group col-md-3">
        <label for="apees"><i class="fa-solid fa-user" style="color:#006b29;"></i> Apellidos</label>
        <input type="text" class="form-control" id="apees" name="apees" readonly value="<?= htmlspecialchars($datOne['apees'] ?? ''); ?>">
      </div>
      <div class="form-group col-md-3">
        <label for="estes"><i class="fa-solid fa-user-check" style="color:#006b29;"></i> Estado Estudiante</label>
        <select class="form-control form-select" id="estes" name="estes">
          <option value="EN FORMACION" <?= (isset($datOne) && ($datOne['estes'] ?? '') === 'EN FORMACION') ? 'selected' : ''; ?>>EN FORMACIÓN</option>
          <option value="RETIRO VOLUNTARIO" <?= (isset($datOne) && ($datOne['estes'] ?? '') === 'RETIRO VOLUNTARIO') ? 'selected' : ''; ?>>RETIRO VOLUNTARIO</option>
          <option value="CANCELADO" <?= (isset($datOne) && ($datOne['estes'] ?? '') === 'CANCELADO') ? 'selected' : ''; ?>>CANCELADO</option>
          <option value="TRASLADADO" <?= (isset($datOne) && ($datOne['estes'] ?? '') === 'TRASLADADO') ? 'selected' : ''; ?>>TRASLADADO</option>
        </select>
      </div>
      <div class="form-group col-md-3">
        <label for="idfic"><i class="fa-solid fa-hashtag" style="color:#006b29;"></i> Ficha <span class="text-danger">*</span></label>
        <select class="form-control form-select" id="idfic" name="idfic" required>
          <option value="">Seleccione la ficha</option>
          <?php if ($datFic) { foreach ($datFic as $df) { ?>
            <option value="<?= $df['idfic']; ?>" <?= (isset($datOne) && ($datOne['idfic'] ?? '') == $df['idfic']) ? 'selected' : ''; ?>><?= $df['idfic']; ?> - <?= htmlspecialchars($df['nomfic']); ?></option>
          <?php } } ?>
        </select>
      </div>
      <div class="form-group col-md-4">
        <label for="compe"><i class="fa-solid fa-book-open" style="color:#006b29;"></i> Competencia <span class="text-danger">*</span></label>
        <select class="form-control form-select" id="compe" name="compe" required onchange="actualizarResultadosPorCompetencia();">
          <option value="">Seleccione la competencia</option>
          <?php
          $compeEncontrada = false;
          if ($datCompe) {
            foreach ($datCompe as $c) {
              $cv = ($c['idcom'] ?? '') . ' - ' . ($c['compe'] ?? $c['descom'] ?? '');
              $selC = (isset($datOne) && ($datOne['idcom'] ?? '') == ($c['idcom'] ?? ''));
              if ($selC) { $compeEncontrada = true; }
          ?>
            <option value="<?= htmlspecialchars($cv); ?>" <?= $selC ? 'selected' : ''; ?>><?= htmlspecialchars($cv); ?></option>
          <?php } }
          if (isset($datOne) && !empty($datOne['idcom']) && !$compeEncontrada) {
            $cvOne = ($datOne['idcom'] ?? '') . ' - ' . ($datOne['compe'] ?? '');
          ?>
            <option value="<?= htmlspecialchars($cvOne); ?>" selected><?= htmlspecialchars($cvOne); ?></option>
          <?php } ?>
          <option value="__OTRO__">OTRO (especificar)</option>
        </select>
        <input type="text" class="form-control mt-2" id="compe_otro" name="compe_otro" placeholder="ID - Descripción" style="display:none;">
      </div>
      <div class="form-group col-md-5">
        <label for="resapr"><i class="fa-solid fa-list-check" style="color:#006b29;"></i> Resultado de Aprendizaje <span class="text-danger">*</span></label>
        <select class="form-control form-select" id="resapr" name="resapr" required>
          <option value="">Seleccione el resultado</option>
          <?php
          $resEncontrado = false;
          if ($datRes) {
            foreach ($datRes as $r) {
              $rv = ($r['idres'] ?? '') . ' - ' . ($r['resapr'] ?? $r['nomres'] ?? '');
              $selR = (isset($datOne) && ($datOne['idres'] ?? '') == ($r['idres'] ?? ''));
              if ($selR) { $resEncontrado = true; }
          ?>
            <option value="<?= htmlspecialchars($rv); ?>" <?= $selR ? 'selected' : ''; ?>><?= htmlspecialchars($rv); ?></option>
          <?php } }
          if (isset($datOne) && !empty($datOne['idres']) && !$resEncontrado) {
            $rvOne = ($datOne['idres'] ?? '') . ' - ' . ($datOne['resapr'] ?? '');
          ?>
            <option value="<?= htmlspecialchars($rvOne); ?>" selected><?= htmlspecialchars($rvOne); ?></option>
          <?php } ?>
          <option value="__OTRO__">OTRO (especificar)</option>
        </select>
        <input type="text" class="form-control mt-2" id="resapr_otro" name="resapr_otro" placeholder="ID - Descripción" style="display:none;">
      </div>
      <div class="form-group col-md-4">
        <label for="caljui"><i class="fa-solid fa-award" style="color:#006b29;"></i> Calificación <span class="text-danger">*</span></label>
        <select class="form-control form-select" id="caljui" name="caljui" required>
          <option value="">Seleccione</option>
          <option value="APROBADO" <?= (isset($datOne) && ($datOne['caljui'] ?? '') === 'APROBADO') ? 'selected' : ''; ?>>APROBADO</option>
          <option value="NO APROBADO" <?= (isset($datOne) && ($datOne['caljui'] ?? '') === 'NO APROBADO') ? 'selected' : ''; ?>>NO APROBADO</option>
          <option value="POR EVALUAR" <?= (isset($datOne) && ($datOne['caljui'] ?? '') === 'POR EVALUAR') ? 'selected' : ''; ?>>POR EVALUAR</option>
        </select>
      </div>
      <div class="form-group col-md-8">
        <div id="estado-formulario" class="alert alert-info w-100 mb-0" style="display:none;"><span id="mensaje-estado"></span></div>
      </div>
      <div class="form-group col-md-12 d-flex justify-content-end gap-2 flex-wrap">
        <button type="button" class="btn btn-outline-success" onclick="buscarEstudiante()"><i class="fa-solid fa-magnifying-glass"></i> Buscar Estudiante</button>
        <button type="submit" class="btn btn-success" style="background-color:#006b29; border-color:#006b29;"><i class="fa-solid fa-floppy-disk"></i> <?= isset($datOne) && !empty($datOne['idjui']) ? 'Actualizar Juicio' : 'Guardar Juicio'; ?></button>
        <button type="button" class="btn btn-outline-secondary" onclick="limpiarFormulario()"><i class="fa-solid fa-rotate-left"></i> Limpiar</button>
      </div>
    </div>
  </form>
</div>

<!-- ============ IMPORTACIÓN CSV — estilo Vvidgen (inser) ============ -->
<div class="inser no-print" id="card-importacion">
  <form id="frmimport" class="filter-toolbar-container" enctype="multipart/form-data" style="flex-direction: column; align-items: stretch;">
    <h2><i class="fa-solid fa-file-import"></i> Importación de Juicios desde CSV (Paso 1 y 2)</h2>
    <div class="row">
      <div class="form-group col-md-4">
        <label for="idfic_import"><i class="fa-solid fa-hashtag" style="color:#006b29;"></i> Ficha destino <span class="text-danger">*</span></label>
        <select class="form-control form-select" id="idfic_import" name="idfic" required>
          <option value="">Seleccione ficha</option>
          <?php if ($datFic) { foreach ($datFic as $df) { ?>
            <option value="<?= $df['idfic']; ?>"><?= $df['idfic']; ?> - <?= htmlspecialchars($df['nomfic']); ?></option>
          <?php } } ?>
        </select>
      </div>
      <div class="form-group col-md-5">
        <label for="csv_file"><i class="fa-solid fa-file-csv" style="color:#006b29;"></i> Archivo CSV</label>
        <input type="file" class="form-control" id="csv_file" name="csv_file" accept=".csv,.txt" required>
        <small class="text-muted">Formato: Documento, Competencia, Resultado, Calificación</small>
      </div>
      <div class="form-group col-md-3 d-flex align-items-end">
        <button type="submit" class="btn btn-success w-100" style="background-color:#006b29; border-color:#006b29;" id="btn-validar-csv"><i class="fa-solid fa-file-circle-check"></i> Paso 1: Validar CSV</button>
      </div>
    </div>
    <div class="row mt-3 d-none" id="import-step2-box">
      <div class="col-12">
        <div class="alert alert-info mb-2"><strong>Paso 2:</strong> Validación completada. Haga clic abajo para guardar permanentemente los juicios.</div>
        <button type="button" class="btn btn-success" style="background-color:#006b29; border-color:#006b29;" id="btn-import-step2"><i class="fa-solid fa-database"></i> Paso 2: Confirmar e Importar</button>
      </div>
    </div>
    <div class="row mt-3" id="import-progress" style="display:none;">
      <div class="col-12">
        <div class="progress"><div class="progress-bar progress-bar-striped progress-bar-animated" style="width:0%; background-color:#006b29;" id="progress-bar">0%</div></div>
        <div class="mt-2 text-muted" id="progress-text">Iniciando importación...</div>
      </div>
    </div>
    <div class="row mt-3" id="import-results" style="display:none;">
      <div class="col-12"><div class="alert" id="results-alert"><h6 class="fw-bold"><i class="fa-solid fa-chart-simple"></i> Resultados de la Importación</h6><div id="results-content"></div></div></div>
    </div>
  </form>
</div>

<!-- ============ FILTROS DE BÚSQUEDA — funcionamiento original, solo estilos Vvidgen ============ -->
<div class="conte mb-3 no-print" id="card-filtros">
  <div class="card border-0 shadow-sm p-3 mb-3" style="background: #ffffff; border-radius: 0.75rem; border: 1px solid rgba(0,0,0,0.05);">
    <div class="row g-3 align-items-end">
      <div class="col-12 col-md-4 col-lg-2">
        <label for="filtro_ficha" class="form-label fw-bold" style="font-size: 0.9rem;">
          <i class="fa-solid fa-hashtag" style="color:#006b29;"></i> Ficha:
        </label>
        <select id="filtro_ficha" class="form-control form-select" onchange="aplicarFiltros()">
          <option value="">Todas las fichas</option>
          <?php if ($datFic) { foreach ($datFic as $df) { ?>
            <option value="<?= $df['idfic']; ?>"><?= $df['idfic']; ?></option>
          <?php } } ?>
        </select>
      </div>
      <div class="col-12 col-md-4 col-lg-3">
        <label for="filtro_competencia" class="form-label fw-bold" style="font-size: 0.9rem;">
          <i class="fa-solid fa-book-open" style="color:#006b29;"></i> Competencia:
        </label>
        <select id="filtro_competencia" class="form-control form-select" onchange="aplicarFiltros()">
          <option value="">Todas las competencias</option>
          <?php $filtroCompeList = isset($datCompeFiltro) && is_array($datCompeFiltro) ? $datCompeFiltro : ($datCompe ?? []); if ($filtroCompeList) { foreach ($filtroCompeList as $c) { ?>
            <option value="<?= $c['idcom']; ?>"><?= htmlspecialchars(mb_substr($c['compe'] ?? $c['descom'] ?? '', 0, 40)); ?>...</option>
          <?php } } ?>
        </select>
      </div>
      <div class="col-12 col-md-4 col-lg-2">
        <label for="filtro_estado" class="form-label fw-bold" style="font-size: 0.9rem;">
          <i class="fa-solid fa-award" style="color:#006b29;"></i> Calificación:
        </label>
        <select id="filtro_estado" class="form-control form-select" onchange="aplicarFiltros()">
          <option value="">Toda calificación</option>
          <option value="APROBADO">APROBADO</option>
          <option value="NO APROBADO">NO APROBADO</option>
          <option value="POR EVALUAR">POR EVALUAR</option>
        </select>
      </div>
      <div class="col-12 col-md-6 col-lg-2">
        <label for="filtro_ndocins" class="form-label fw-bold" style="font-size: 0.9rem;">
          <i class="fa-solid fa-user-tie" style="color:#006b29;"></i> Doc. Instructor:
        </label>
        <input type="text" id="filtro_ndocins" class="form-control" placeholder="Cédula instructor" onkeyup="if(event.key==='Enter') aplicarFiltros();">
      </div>
      <div class="col-12 col-lg-3">
        <label for="filtro_busqueda" class="form-label fw-bold" style="font-size: 0.9rem;">
          <i class="fa-solid fa-magnifying-glass" style="color:#006b29;"></i> Búsqueda libre:
        </label>
        <div class="input-group">
          <input type="text" id="filtro_busqueda" class="form-control" placeholder="Resultado, competencia, ficha..." onkeyup="if(event.key==='Enter') aplicarFiltros();">
          <button type="button" class="btn btn-success" style="background-color: #006b29; border-color: #006b29;" onclick="aplicarFiltros()" title="Buscar"><i class="fa-solid fa-magnifying-glass"></i></button>
          <button type="button" class="btn btn-outline-secondary" onclick="limpiarFiltros()" title="Limpiar filtros"><i class="fa-solid fa-rotate-left"></i></button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ============ TABLA DE JUICIOS — funcionamiento original intacto, solo estilos Vvidgen ============ -->
<div class="card border-0 shadow-sm rounded-3 mb-4 no-print">
  <div class="card-header text-white d-flex justify-content-between align-items-center flex-wrap gap-2" style="background-color: #0b1c30;"><h5 class="mb-0 fw-bold"><i class="fa-solid fa-table-list"></i> Visualización de Juicios de Evaluación</h5><span class="badge bg-light text-dark" id="info_paginacion">Mostrando 0 registros</span></div>
  <div class="card-body">
    <div class="table-responsive card-table-responsive"><table class="table jui-card-grid" id="tablaJuicios" style="width:100%"><thead><tr><th>Juicios de evaluación</th></tr></thead><tbody id="tbody_juicios_importados"><tr><td class="text-center py-4"><i class="fa-solid fa-spinner fa-spin"></i> Cargando datos...</td></tr></tbody></table></div>
    <div class="row mt-3 align-items-center"><div class="col-12 col-md-6"><span class="text-muted" id="info_paginacion_detalle"></span></div><div class="col-12 col-md-6"><nav><ul class="pagination justify-content-center justify-content-md-end mb-0" id="paginacion"></ul></nav></div></div>
  </div>
</div>

<!-- Modal Detalle -->
<div class="modal fade" id="modalDetalleJuicio" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content modal-detail-card shadow-lg border-0" style="border-radius: 16px; overflow: hidden;">
      <div class="modal-header text-white border-0 px-4 py-3" style="background-color: #0b1c30;">
        <h5 class="modal-title fw-bold"><i class="fa-solid fa-eye" style="color:#7afd88;"></i> Detalle del Juicio</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="modal-detalle-body">
        <p class="text-center"><i class="fa-solid fa-spinner fa-spin"></i> Cargando...</p>
      </div>
      <div class="modal-footer bg-light border-0">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

</div><!-- /.conte -->

<script>
const CSRF_TOKEN = <?= json_encode($csrfToken ?? '', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
const PG_JUI = <?= json_encode($pg ?? '2414', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
let paginaActual = 1;
let importToken = null;
let juicioEnEdicion = null;
let cargandoJuicios = false;
let inicializado = false;
let enviandoJui = false;

document.addEventListener('DOMContentLoaded', function() {
  // Guard de estabilidad pg=2414: evita doble inicialización y bucle de recarga.
  // Antes un segundo DOMContentLoaded o un fetch que devolvía 401 podía
  // disparar window.location y generar recarga continua. Ahora se bloquea
  // cualquier segunda navegación automática y se informa sin recargar.
  if (inicializado) return;
  inicializado = true;
  cargarJuicios(1);
  const inputNdoce = document.getElementById('ndoce');
  if (inputNdoce) {
    inputNdoce.addEventListener('keypress', function(e) {
      if (e.key === 'Enter') {
        e.preventDefault();
        buscarEstudiante();
      }
    });
    // Si el documento cambia después de "Buscar Estudiante", el idusu oculto y los
    // nombres quedaban con los del aprendiz anterior y el juicio se guardaba contra
    // ese aprendiz. Se limpian para obligar a una nueva búsqueda (el controlador
    // resuelve el aprendiz por el documento de todos modos).
    inputNdoce.addEventListener('input', function() {
      const idusuHidden = document.getElementById('idusu');
      if (idusuHidden) idusuHidden.value = '';
      const nomes = document.getElementById('nomes');
      if (nomes) nomes.value = '';
      const apees = document.getElementById('apees');
      if (apees) apees.value = '';
    });
  }
  const selectCompe = document.getElementById('compe');
  if (selectCompe) {
    selectCompe.addEventListener('change', function() {
      const otro = document.getElementById('compe_otro');
      if (otro) otro.style.display = (this.value === '__OTRO__') ? 'block' : 'none';
    });
  }
  const selectRes = document.getElementById('resapr');
  if (selectRes) {
    selectRes.addEventListener('change', function() {
      const otro = document.getElementById('resapr_otro');
      if (otro) otro.style.display = (this.value === '__OTRO__') ? 'block' : 'none';
    });
  }
  const frmImport = document.getElementById('frmimport');
  if (frmImport) {
    frmImport.addEventListener('submit', function(e) {
      e.preventDefault();
      validarCSV();
    });
  }
  const btnStep2 = document.getElementById('btn-import-step2');
  if (btnStep2) {
    btnStep2.addEventListener('click', function() {
      ejecutarImportacionPaso2();
    });
  }
  // PRG CORRECTO: intercepta el submit tradicional de #frmins (save/edit) y lo convierte
  // en fetch + redirect GET. Así el POST nunca queda como última navegación y F5
  // no muestra "La página que buscas ha utilizado la información...".
  const formJui = document.getElementById('frmins');
  if (formJui) {
    formJui.addEventListener('submit', function(e) {
      e.preventDefault();
      if (enviandoJui) return;
      enviandoJui = true;
      const btn = formJui.querySelector('button[type="submit"]');
      const origText = btn ? btn.innerHTML : '';
      if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Guardando...'; }
      const fd = new FormData(formJui);
      // Marca petición AJAX para que horcjuev.php responda JSON con redirect
      fd.set('ajax', '1');
      const url = 'controllers/horcjuev.php?pg=' + encodeURIComponent(PG_JUI);
      fetch(url, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
        .then(function(r){ return r.json().then(function(j){ j._status=r.status; return j; }); })
        .then(function(data){
          if (data.success) {
            const redir = data.redirect || ('home.php?pg=' + encodeURIComponent(PG_JUI) + '&msg=edit');
            window.location.href = redir;
          } else {
            alert(data.message || data.error || 'No se pudo guardar el juicio.');
            enviandoJui = false;
            if (btn) { btn.disabled = false; btn.innerHTML = origText; }
          }
        })
        .catch(function(){
          alert('Error de conexión al guardar el juicio.');
          enviandoJui = false;
          if (btn) { btn.disabled = false; btn.innerHTML = origText; }
        });
    });
  }
});

function buscarEstudiante() {
  const ndoce = document.getElementById('ndoce').value.trim();
  if (!ndoce) {
    alert('Ingrese un número de documento.');
    return;
  }
  fetch('controllers/horcjuev.php?opera=buscar_juicio&ndoce=' + encodeURIComponent(ndoce))
    .then(function(r){ return r.json(); })
    .then(function(data) {
      if (data.success && data.data) {
        document.getElementById('nomes').value = data.data.nomes || '';
        document.getElementById('apees').value = data.data.apees || '';
        if (data.data.idusu) document.getElementById('idusu').value = data.data.idusu;
        if (data.data.idfic) document.getElementById('idfic').value = data.data.idfic;
      } else {
        alert(data.message || 'Estudiante no encontrado');
      }
    })
    .catch(function(){ alert('Error al consultar estudiante'); });
}

function actualizarResultadosPorCompetencia() {
  const compeVal = document.getElementById('compe').value;
  if (!compeVal || compeVal === '__OTRO__') return;
  fetch('controllers/horcjuev.php?opera=get_resultados_competencia&compe=' + encodeURIComponent(compeVal))
    .then(function(r){ return r.json(); })
    .then(function(data) {
      if (data.success && data.resultados) {
        const resSelect = document.getElementById('resapr');
        resSelect.innerHTML = '<option value="">Seleccione el resultado</option>';
        data.resultados.forEach(function(item) {
          const val = item.idres + ' - ' + (item.nomres || item.resapr || '');
          const opt = document.createElement('option');
          opt.value = val;
          opt.textContent = val;
          resSelect.appendChild(opt);
        });
        const optOtro = document.createElement('option');
        optOtro.value = '__OTRO__';
        optOtro.textContent = 'OTRO (especificar)';
        resSelect.appendChild(optOtro);
      }
    })
    .catch(function(){});
}

function limpiarFormulario() {
  document.getElementById('frmins').reset();
  document.getElementById('opera').value = 'save';
  document.getElementById('idjui').value = '';
  document.getElementById('idusu').value = '';
  const cOtro = document.getElementById('compe_otro');
  if (cOtro) cOtro.style.display = 'none';
  const rOtro = document.getElementById('resapr_otro');
  if (rOtro) rOtro.style.display = 'none';
  const st = document.getElementById('estado-formulario');
  if (st) st.style.display = 'none';
}

function cargarJuicios(pagina) {
  if (cargandoJuicios) return;
  cargandoJuicios = true;
  pagina = pagina || 1;
  paginaActual = pagina;
  const ficha = document.getElementById('filtro_ficha') ? document.getElementById('filtro_ficha').value : '';
  const compe = document.getElementById('filtro_competencia') ? document.getElementById('filtro_competencia').value : '';
  const caljui = document.getElementById('filtro_estado') ? document.getElementById('filtro_estado').value : '';
  const ndocins = document.getElementById('filtro_ndocins') ? document.getElementById('filtro_ndocins').value.trim() : '';
  const busqueda = document.getElementById('filtro_busqueda') ? document.getElementById('filtro_busqueda').value.trim() : '';
  const params = new URLSearchParams({
    opera: 'get_importados_json',
    page: pagina,
    limite: 10,
    idfic: ficha,
    compe: compe,
    caljui: caljui,
    ndocins: ndocins,
    busqueda: busqueda
  });
  fetch('controllers/horcjuev.php?' + params.toString())
    .then(function(r){
      // Si la sesión expiró el controlador responde 401 JSON, no se debe recargar
      if (r.status === 401) throw new Error('Sesión expirada');
      return r.json();
    })
    .then(function(data) {
      renderizarTablaJuicios(data);
    })
    .catch(function(e){
      var msg = (e && e.message === 'Sesión expirada') ? 'Sesión expirada. Recargue la página e inicie sesión de nuevo.' : 'Error al cargar datos.';
      var tb = document.getElementById('tbody_juicios_importados');
      if (tb) tb.innerHTML = '<tr><td colspan="5" class="text-danger text-center">' + escapeHtml(msg) + '</td></tr>';
    })
    .finally(function(){ cargandoJuicios = false; });
}

function renderizarTablaJuicios(data) {
  const tbody = document.getElementById('tbody_juicios_importados');
  if (!data.success || !data.data || data.data.length === 0) {
    tbody.innerHTML = '<tr><td class="text-center text-muted py-4">No se encontraron juicios registrados.</td></tr>';
    document.getElementById('info_paginacion').textContent = 'Mostrando 0 registros';
    document.getElementById('paginacion').innerHTML = '';
    return;
  }
  let html = '';
  data.data.forEach(function(item) {
    const cal = item.caljui || 'POR EVALUAR';
    const badgeClass = cal === 'APROBADO' ? 'bg-success' : (cal === 'NO APROBADO' ? 'bg-danger' : 'bg-warning text-dark');
    const nombreAprendiz = item.nomusu || item.nomes || 'Aprendiz';
    const docAprendiz = item.ndoce || '';
    const esIdSinNombre = docAprendiz.indexOf('ID:') === 0 || nombreAprendiz.indexOf('Aprendiz-') === 0;
    const nomIns = item.nom_instructor || item.ndocins || 'N/A';
    const compe = item.descom || item.compe || 'Sin competencia';
    const res = item.nomres || item.resapr || 'Sin resultado';
    const aprendiz = esIdSinNombre ? 'ID aprendiz: ' + escapeHtml(docAprendiz) : escapeHtml(nombreAprendiz) + ' · ' + escapeHtml(docAprendiz);
    const datos = escapeHtml(JSON.stringify(item)).replace(/'/g, '&#039;');
    const texto = escapeHtml([nombreAprendiz, docAprendiz, nomIns, compe, res, cal].join(' ').toLowerCase());
    html += '<tr data-search="' + texto + '" data-cal="' + escapeHtml(cal) + '"><td><div class="card h-100 jui-card">'
      + '<div class="jui-card-head"><div class="jui-card-kicker">Juicio de evaluación · Ficha ' + escapeHtml(item.idfic || 'N/A') + '</div><h6><i class="fa-solid fa-user-graduate me-1"></i>' + aprendiz + '</h6><small class="opacity-75"><i class="fa-solid fa-chalkboard-teacher me-1"></i>' + escapeHtml(nomIns) + '</small></div>'
      + '<div class="jui-card-body"><div class="d-flex justify-content-between align-items-center mb-2"><span class="badge ' + badgeClass + '">' + escapeHtml(cal) + '</span><small class="text-muted">Doc: ' + escapeHtml(docAprendiz || 'N/A') + '</small></div><div class="jui-card-meta"><div><small>Competencia</small><strong>' + escapeHtml(compe) + '</strong></div><div><small>Resultado</small><strong>' + escapeHtml(res) + '</strong></div></div></div>'
      + '<div class="jui-card-footer"><div class="btn-group btn-group-sm w-100">'
      + '<button type="button" class="btn btn-outline-info" title="Ver detalle" data-item=\'' + datos + '\' onclick="verDetalle(JSON.parse(this.dataset.item))"><i class="fa-solid fa-eye"></i> Ver</button>'
      + '<button type="button" class="btn btn-outline-primary" title="Edición rápida" data-item=\'' + datos + '\' onclick="iniciarEdicion(JSON.parse(this.dataset.item))"><i class="fa-solid fa-pencil"></i></button>'
      + '<a href="home.php?pg=<?= htmlspecialchars($pg); ?>&opera=edi&idjui=' + encodeURIComponent(item.idjui) + '" class="btn btn-outline-warning" title="Editar completo"><i class="fa-solid fa-edit"></i></a>'
      + '<button type="button" class="btn btn-outline-danger" title="Eliminar" onclick="eliminarJuicio(' + encodeURIComponent(item.idjui) + ')"><i class="fa-solid fa-trash"></i></button>'
      + '</div></div></div></td></tr>';
  });
  tbody.innerHTML = html;
  const total = data.total || 0;
  const paginas = data.total_paginas || 1;
  document.getElementById('info_paginacion').textContent = 'Mostrando página ' + paginaActual + ' de ' + paginas + ' (Total: ' + total + ' juicios)';
  let pagHtml = '';
  if (paginaActual > 1) pagHtml += '<li class="page-item"><a class="page-link" href="#" onclick="cargarJuicios(' + (paginaActual - 1) + '); return false;">&laquo;</a></li>';
  for (let p = Math.max(1, paginaActual - 2); p <= Math.min(paginas, paginaActual + 2); p++) pagHtml += '<li class="page-item ' + (p === paginaActual ? 'active' : '') + '"><a class="page-link" href="#" onclick="cargarJuicios(' + p + '); return false;">' + p + '</a></li>';
  if (paginaActual < paginas) pagHtml += '<li class="page-item"><a class="page-link" href="#" onclick="cargarJuicios(' + (paginaActual + 1) + '); return false;">&raquo;</a></li>';
  document.getElementById('paginacion').innerHTML = pagHtml;
}

function cambiarVistaJuicios(modo) {
  var tabla = document.getElementById('tablaJuicios');
  if (tabla) tabla.classList.toggle('is-list-view', modo === 'list');
  var grid = document.getElementById('btnJuicioGrid');
  var list = document.getElementById('btnJuicioList');
  if (grid) grid.classList.toggle('active', modo !== 'list');
  if (list) list.classList.toggle('active', modo === 'list');
}

function filtrarTarjetasJuicios(texto) {
  var consulta = String(texto || '').toLowerCase().trim();
  document.querySelectorAll('#tbody_juicios_importados tr').forEach(function (fila) {
    fila.style.display = !consulta || (fila.getAttribute('data-search') || '').indexOf(consulta) !== -1 ? '' : 'none';
  });
}

// Clic en KPIs: filtra las tarjetas por estado exacto y baja a la tabla.
function filtrarKpiJuicio(estado) {
  var caja = document.getElementById('busquedaJuicios');
  if (caja) caja.value = '';
  var est = String(estado || '').toUpperCase();
  document.querySelectorAll('#tbody_juicios_importados tr').forEach(function (fila) {
    var cal = String(fila.getAttribute('data-cal') || '').toUpperCase();
    var mostrar = false;
    if (!est || est === 'TODOS') mostrar = true;
    else if (est === 'PENDIENTES') mostrar = (cal !== 'APROBADO' && cal !== 'NO APROBADO');
    else mostrar = (cal === est);
    fila.style.display = mostrar ? '' : 'none';
  });
  var tabla = document.getElementById('tablaJuicios');
  if (tabla && tabla.scrollIntoView) tabla.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function ordenarTarjetasJuicios(direccion) {
  var cuerpo = document.getElementById('tbody_juicios_importados');
  if (!cuerpo) return;
  var filas = Array.from(cuerpo.querySelectorAll('tr'));
  filas.sort(function (a, b) {
    var fa = a.querySelector('.jui-card-head h6');
    var fb = b.querySelector('.jui-card-head h6');
    return (fa ? fa.textContent : '').localeCompare(fb ? fb.textContent : '', undefined, { numeric: true }) * (direccion === 'asc' ? 1 : -1);
  });
  filas.forEach(function (fila) { cuerpo.appendChild(fila); });
}

function aplicarFiltros() {
  cargarJuicios(1);
}

function limpiarFiltros() {
  document.getElementById('filtro_ficha').value = '';
  document.getElementById('filtro_competencia').value = '';
  document.getElementById('filtro_estado').value = '';
  const elNdocins = document.getElementById('filtro_ndocins');
  if (elNdocins) elNdocins.value = '';
  document.getElementById('filtro_busqueda').value = '';
  cargarJuicios(1);
}

function iniciarEdicion(item) {
  juicioEnEdicion = item;
  const nombre = ((item.nomes || '') + ' ' + (item.apees || '')).trim() || item.nomusu || 'N/A';
  document.getElementById('estudiante-edicion').textContent = nombre + ' (' + (item.ndoce || item.ndocusu || '') + ')';
  document.getElementById('competencia-edicion').textContent = item.descom || item.compe || '';
  document.getElementById('resultado-edicion').textContent = item.nomres || item.resapr || '';
  const spanCalif = document.getElementById('calificacion-actual');
  spanCalif.textContent = item.caljui || 'POR EVALUAR';
  spanCalif.className = 'badge ' + (item.caljui === 'APROBADO' ? 'bg-success' : (item.caljui === 'NO APROBADO' ? 'bg-danger' : 'bg-warning text-dark'));
  const cardEd = document.getElementById('card-edicion-rapida');
  cardEd.style.display = 'block';
  cardEd.scrollIntoView({ behavior: 'smooth' });
}

function cancelarEdicion() {
  juicioEnEdicion = null;
  document.getElementById('card-edicion-rapida').style.display = 'none';
}

function cambiarCalificacion(nuevaCalif) {
  if (!juicioEnEdicion || !juicioEnEdicion.idjui) return;
  const formData = new FormData();
  formData.append('opera', 'editar_calificacion_rapida');
  formData.append('idjui', juicioEnEdicion.idjui);
  formData.append('calificacion', nuevaCalif);
  formData.append('csrf_token', CSRF_TOKEN);
  fetch('controllers/horcjuev.php', {
    method: 'POST',
    body: formData
  })
    .then(function(r){ return r.json(); })
    .then(function(data) {
      if (data.success) {
        alert('Calificación actualizada con éxito.');
        cancelarEdicion();
        cargarJuicios(paginaActual);
      } else {
        alert(data.message || 'Error al actualizar calificación');
      }
    })
    .catch(function(){ alert('Error de conexión.'); });
}

function eliminarJuicio(idjui) {
  if (!confirm('¿Está seguro de eliminar este juicio de evaluación?')) return;
  const formData = new FormData();
  formData.append('opera', 'eli');
  formData.append('idjui', idjui);
  formData.append('csrf_token', CSRF_TOKEN);
  fetch('controllers/horcjuev.php', {
    method: 'POST',
    body: formData
  })
    .then(function(r){ return r.json(); })
    .then(function(data) {
      if (data.success) {
        cargarJuicios(paginaActual);
      } else {
        alert(data.message || data.error || 'No se pudo eliminar el juicio.');
      }
    })
    .catch(function(){ alert('Error de conexión al eliminar el juicio.'); });
}

function verDetalle(item) {
  const modalBody = document.getElementById('modal-detalle-body');
  const nombre = ((item.nomes || '') + ' ' + (item.apees || '')).trim() || item.nomusu || 'N/A';
  modalBody.innerHTML = '<table class="table table-bordered">'
    + '<tr><th>ID Juicio</th><td>' + escapeHtml(item.idjui) + '</td></tr>'
    + '<tr><th>Estudiante</th><td>' + escapeHtml(nombre) + ' (' + escapeHtml(item.ndoce || item.ndocusu || '') + ')</td></tr>'
    + '<tr><th>Ficha</th><td>' + escapeHtml(item.idfic || '') + ' - ' + escapeHtml(item.nomfic || '') + '</td></tr>'
    + '<tr><th>Competencia</th><td>' + escapeHtml(item.descom || item.compe || '') + '</td></tr>'
    + '<tr><th>Resultado</th><td>' + escapeHtml(item.nomres || item.resapr || '') + '</td></tr>'
    + '<tr><th>Calificación</th><td><span class="badge ' + (item.caljui === 'APROBADO' ? 'bg-success' : (item.caljui === 'NO APROBADO' ? 'bg-danger' : 'bg-warning text-dark')) + '">' + escapeHtml(item.caljui || '') + '</span></td></tr>'
    + '<tr><th>Instructor Doc.</th><td>' + escapeHtml(item.ndocins || 'N/A') + '</td></tr>'
    + '<tr><th>Fecha Reporte</th><td>' + escapeHtml(item.fecha_reporte || 'N/A') + '</td></tr>'
    + '<tr><th>Fecha Importación</th><td>' + escapeHtml(item.fecha_importacion || 'N/A') + '</td></tr>'
    + '<tr><th>Archivo Origen</th><td>' + escapeHtml(item.archivo_correlativo || 'Manual') + '</td></tr>'
    + '</table>';
  const modal = new bootstrap.Modal(document.getElementById('modalDetalleJuicio'));
  modal.show();
}

function validarCSV() {
  const fileInput = document.getElementById('csv_file');
  const idfic = document.getElementById('idfic_import').value;
  if (!fileInput.files.length) {
    alert('Seleccione un archivo CSV.');
    return;
  }
  if (!idfic) {
    alert('Seleccione la ficha destino.');
    return;
  }
  const formData = new FormData();
  formData.append('opera', 'import_step1');
  formData.append('csv_file', fileInput.files[0]);
  formData.append('idfic', idfic);
  formData.append('csrf_token', CSRF_TOKEN);
  document.getElementById('import-progress').style.display = 'block';
  document.getElementById('progress-bar').style.width = '40%';
  document.getElementById('progress-bar').textContent = '40% - Validando estructura...';
  fetch('controllers/horcjuev.php', {
    method: 'POST',
    body: formData
  })
    .then(function(r){ return r.json(); })
    .then(function(data) {
      document.getElementById('progress-bar').style.width = '100%';
      document.getElementById('progress-bar').textContent = '100%';
      if (data.success) {
        importToken = data.token;
        document.getElementById('import-step2-box').classList.remove('d-none');
        document.getElementById('import-results').style.display = 'block';
        document.getElementById('results-alert').className = 'alert alert-info';
        document.getElementById('results-content').innerHTML = '<p><strong>Archivo validado correctamente.</strong></p><ul><li>Total filas válidas: <strong>' + (data.resumen && data.resumen.validos != null ? data.resumen.validos : (data.resumen && data.resumen.total || 0)) + '</strong></li><li>Ficha: <strong>' + escapeHtml(idfic) + '</strong></li></ul>';
      } else {
        document.getElementById('import-results').style.display = 'block';
        document.getElementById('results-alert').className = 'alert alert-danger';
        document.getElementById('results-content').innerHTML = '<p class="mb-0 text-danger">' + escapeHtml(data.message || 'Error en validación') + '</p>';
      }
    })
    .catch(function(){ alert('Error durante la validación del CSV'); });
}

function ejecutarImportacionPaso2() {
  if (!importToken) {
    alert('No hay una validación activa. Repita el Paso 1.');
    return;
  }
  const idfic = document.getElementById('idfic_import').value;
  const formData = new FormData();
  formData.append('opera', 'import_step2');
  formData.append('token', importToken);
  formData.append('idfic', idfic);
  formData.append('csrf_token', CSRF_TOKEN);
  document.getElementById('import-progress').style.display = 'block';
  document.getElementById('progress-bar').style.width = '60%';
  document.getElementById('progress-bar').textContent = '60% - Guardando en base de datos...';
  fetch('controllers/horcjuev.php', {
    method: 'POST',
    body: formData
  })
    .then(function(r){ return r.json(); })
    .then(function(data) {
      document.getElementById('progress-bar').style.width = '100%';
      document.getElementById('progress-bar').textContent = '100% - Completado';
      document.getElementById('import-step2-box').classList.add('d-none');
      importToken = null;
      document.getElementById('import-results').style.display = 'block';
      if (data.success) {
        document.getElementById('results-alert').className = 'alert alert-success';
        document.getElementById('results-content').innerHTML = '<p class="mb-1"><strong><i class="fa-solid fa-check-circle"></i> Importación finalizada con éxito!</strong></p><p class="mb-0">' + escapeHtml(data.message || '') + '</p>';
        cargarJuicios(1);
      } else {
        document.getElementById('results-alert').className = 'alert alert-danger';
        document.getElementById('results-content').innerHTML = '<p class="mb-0 text-danger">' + escapeHtml(data.message || 'Error en importación') + '</p>';
      }
    })
    .catch(function(){ alert('Error al ejecutar la importación final.'); });
}

function escapeHtml(text) {
  if (text === null || text === undefined) return '';
  return String(text)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}

function exportarCSV() {
  const ficha = document.getElementById('filtro_ficha') ? document.getElementById('filtro_ficha').value : '';
  const compe = document.getElementById('filtro_competencia') ? document.getElementById('filtro_competencia').value : '';
  const caljui = document.getElementById('filtro_estado') ? document.getElementById('filtro_estado').value : '';
  const ndocins = document.getElementById('filtro_ndocins') ? document.getElementById('filtro_ndocins').value.trim() : '';
  const busqueda = document.getElementById('filtro_busqueda') ? document.getElementById('filtro_busqueda').value.trim() : '';
  const params = new URLSearchParams({
    opera: 'get_importados_json',
    page: 1,
    limite: 5000,
    idfic: ficha,
    compe: compe,
    caljui: caljui,
    ndocins: ndocins,
    busqueda: busqueda
  });
  fetch('controllers/horcjuev.php?' + params.toString())
    .then(function(r){ return r.json(); })
    .then(function(data) {
      if (!data.success || !data.data || data.data.length === 0) {
        alert('No hay juicios para exportar con los filtros seleccionados.');
        return;
      }
      const headers = [
        'ID Juicio',
        'Documento Aprendiz',
        'Nombre Aprendiz',
        'Ficha',
        'Competencia',
        'Resultado de Aprendizaje',
        'Calificacion',
        'Documento Instructor',
        'Nombre Instructor',
        'Fecha Reporte',
        'Fecha Juicio'
      ];
      function escapeCsv(val) {
        if (val === null || val === undefined) return '""';
        const str = String(val).replace(/"/g, '""');
        return '"' + str + '"';
      }
      let csv = '\uFEFF';
      csv += headers.map(escapeCsv).join(';') + '\r\n';
      data.data.forEach(function(item) {
        const nomAprendiz = item.nomusu || ((item.nomes || '') + ' ' + (item.apees || '')).trim() || '';
        const row = [
          item.idjui || '',
          item.ndoce || item.ndocusu || '',
          nomAprendiz,
          item.idfic || '',
          item.descom || item.compe || '',
          item.nomres || item.resapr || '',
          item.caljui || 'POR EVALUAR',
          item.ndocins || item.ndoc_instructor || '',
          item.nom_instructor || item.funre || '',
          item.fecha_reporte || item.fecre || '',
          item.fecjuieva || item.fecha_juicio || item.fecju || ''
        ];
        csv += row.map(escapeCsv).join(';') + '\r\n';
      });
      const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
      const url = URL.createObjectURL(blob);
      const link = document.createElement('a');
      link.setAttribute('href', url);
      const fechaHoy = new Date().toISOString().slice(0, 10);
      link.setAttribute('download', 'juicios_evaluacion_' + fechaHoy + '.csv');
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
      URL.revokeObjectURL(url);
    })
    .catch(function(){ alert('Error al generar la exportación CSV.'); });
}
</script>
