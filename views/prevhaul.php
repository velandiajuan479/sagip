<?php 
$ctrlPath = file_exists('controllers/prechaul.php') ? 'controllers/prechaul.php' : '../controllers/prechaul.php';
require_once $ctrlPath;
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!isset($pg)) {
    $pg = isset($_GET['pg']) ? $_GET['pg'] : 1533;
}
if (!function_exists('titulo2')) {
    $optPath = file_exists('controllers/optimg.php') ? 'controllers/optimg.php' : '../controllers/optimg.php';
    if (file_exists($optPath)) require_once $optPath;
}
?>

<div class="conte">
  <?php echo titulo2("<i class='".($icono ?? 'fa fa-solid fa-clock-rotate-left')."'></i> Histórico de Préstamo de Aulas", 2); ?>

  <!-- Dashboard de Métricas (Tarjetas KPI Estilo vvidgen.php) -->
  <div class="row my-4 g-4">
    <!-- Total Préstamos -->
    <div class="col-12 col-sm-6 col-xl-3">
      <div class="kpi-card h-100 d-flex flex-column justify-content-between">
        <div class="d-flex align-items-center justify-content-between">
          <div>
            <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #3c627f;">Total Préstamos</span>
            <div class="d-flex align-items-baseline gap-2 mt-2">
              <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #0b1c30;"><?= number_format($stats['total'] ?? 0); ?></span>
              <span style="font-size: 13px; color: #3e4a3d;">registros</span>
            </div>
          </div>
          <div class="kpi-icon-box" style="background-color: #b5dcfe; color: #3c627f;">
            <i class="fa-solid fa-list-check fs-4"></i>
          </div>
        </div>
        <div class="kpi-footer-strip">
          <span class="fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 11px; color: #3c627f;">
            <i class="fa-solid fa-clock-rotate-left" style="font-size: 12px;"></i> Historial Consolidado
          </span>
          <span style="font-size: 11px; color: #3e4a3d;">Total</span>
        </div>
      </div>
    </div>

    <!-- Aulas Utilizadas -->
    <div class="col-12 col-sm-6 col-xl-3">
      <div class="kpi-card h-100 d-flex flex-column justify-content-between">
        <div class="d-flex align-items-center justify-content-between">
          <div>
            <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #0d6efd;">Aulas Usadas</span>
            <div class="d-flex align-items-baseline gap-2 mt-2">
              <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #0b1c30;"><?= number_format($stats['aulas_utilizadas'] ?? 0); ?></span>
              <span style="font-size: 13px; color: #3e4a3d;">ambientes</span>
            </div>
          </div>
          <div class="kpi-icon-box" style="background-color: #cfe2ff; color: #084298;">
            <i class="fa-solid fa-door-open fs-4"></i>
          </div>
        </div>
        <div class="kpi-footer-strip">
          <span class="fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 11px; color: #084298;">
            <i class="fa-solid fa-school" style="font-size: 12px;"></i> Espacios Pedagógicos
          </span>
          <span style="font-size: 11px; color: #3e4a3d;">Sede</span>
        </div>
      </div>
    </div>

    <!-- Aulas en Uso -->
    <div class="col-12 col-sm-6 col-xl-3">
      <div class="kpi-card h-100 d-flex flex-column justify-content-between">
        <div class="d-flex align-items-center justify-content-between">
          <div>
            <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #8a6d00;">Aulas en Uso</span>
            <div class="d-flex align-items-baseline gap-2 mt-2">
              <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #0b1c30;"><?= number_format($stats['ocupadas'] ?? 0); ?></span>
              <span style="font-size: 13px; color: #3e4a3d;">activas</span>
            </div>
          </div>
          <div class="kpi-icon-box" style="background-color: #ffe082; color: #8a6d00;">
            <i class="fa-solid fa-clock fs-4"></i>
          </div>
        </div>
        <div class="kpi-footer-strip">
          <span class="fw-semibold d-inline-flex align-items-center gap-2" style="font-size: 11px; color: #8a6d00;">
            <span class="pulse-dot"></span> Sesiones en Curso
          </span>
          <span style="font-size: 11px; color: #3e4a3d;">Activas</span>
        </div>
      </div>
    </div>

    <!-- Novedades / Llaves -->
    <div class="col-12 col-sm-6 col-xl-3">
      <div class="kpi-card h-100 d-flex flex-column justify-content-between">
        <div class="d-flex align-items-center justify-content-between">
          <div>
            <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: <?= ($stats['novedades'] > 0) ? '#ba1a1a' : '#006b29'; ?>;">Novedades Llaves</span>
            <div class="d-flex align-items-baseline gap-2 mt-2">
              <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #0b1c30;"><?= number_format($stats['novedades'] ?? 0); ?></span>
              <span style="font-size: 13px; color: #3e4a3d;">incidentes</span>
            </div>
          </div>
          <div class="kpi-icon-box" style="background-color: <?= ($stats['novedades'] > 0) ? '#ffdad6' : '#7afd88'; ?>; color: <?= ($stats['novedades'] > 0) ? '#ba1a1a' : '#006b24'; ?>;">
            <i class="fa-solid fa-key fs-4"></i>
          </div>
        </div>
        <div class="kpi-footer-strip">
          <span class="d-inline-flex align-items-center gap-1" style="font-size: 11px; color: <?= ($stats['novedades'] > 0) ? '#ba1a1a' : '#006b29'; ?>;">
            <i class="fa-solid <?= ($stats['novedades'] > 0) ? 'fa-triangle-exclamation' : 'fa-circle-check'; ?>" style="font-size: 12px;"></i> Incidentes Entrega
          </span>
          <span class="fw-semibold" style="font-size: 11px; color: #3c627f;"><?= ($stats['novedades'] > 0) ? 'Atención' : 'Normal'; ?></span>
        </div>
      </div>
    </div>
  </div>

<?php
// Clasificar registros para las pestañas
$datEnCurso = [];
$datLiberados = [];

if (!empty($dtHis)) {
    foreach ($dtHis as $d) {
        $estTexto = trim($d['estado'] ?? '');
        if (strcasecmp($estTexto, 'Ocupado') === 0) {
            $datEnCurso[] = $d;
        } else {
            $datLiberados[] = $d;
        }
    }
}
$totHistorico = count($dtHis ?? []);

// Helper para renderizar cada registro histórico como tarjeta
if (!function_exists('renderHistoricoCard')) {
function renderHistoricoCard($d) {
    $fechinFormateada = (!empty($d['fechin']) && $d['fechin'] !== '0000-00-00 00:00:00') ? date('d/m/Y H:i', strtotime($d['fechin'])) : 'N/D';
    $fechfinFormateada = (!empty($d['fechfin']) && $d['fechfin'] !== '0000-00-00 00:00:00') ? date('d/m/Y H:i', strtotime($d['fechfin'])) : null;

    $duracion = '---';
    if (!empty($d['fechin']) && $d['fechin'] !== '0000-00-00 00:00:00') {
      $inicio = new DateTime($d['fechin']);
      if ($fechfinFormateada) {
        $finDt = new DateTime($d['fechfin']);
        $diff = $inicio->diff($finDt);
        if ($diff->days > 0) {
          $duracion = $diff->days . 'd ' . $diff->h . 'h';
        } else if ($diff->h > 0) {
          $duracion = $diff->h . 'h ' . $diff->i . 'm';
        } else {
          $duracion = $diff->i . ' min';
        }
      } else {
        $duracion = 'En curso';
      }
    }

    $piso = !empty($d['piso']) ? 'Piso ' . htmlspecialchars($d['piso']) : '';
    $bloque = !empty($d['bloqau']) ? 'Bloque ' . htmlspecialchars($d['bloqau']) : '';
    $ubicacion = trim($piso . ' ' . $bloque);
    if (empty($ubicacion)) $ubicacion = 'Sede Central';

    $estTexto = trim($d['estado'] ?? '');
    $badgeClass = 'bg-secondary';
    $iconClass = 'fa-circle-info';

    if (strcasecmp($estTexto, 'Liberado') === 0) {
      $badgeClass = 'bg-success';
      $iconClass = 'fa-circle-check';
    } else if (strcasecmp($estTexto, 'Ocupado') === 0) {
      $badgeClass = 'bg-warning text-dark';
      $iconClass = 'fa-clock';
    } else if (stripos($estTexto, 'llave') !== false || stripos($estTexto, 'novedad') !== false) {
      $badgeClass = 'bg-danger';
      $iconClass = 'fa-triangle-exclamation';
    }
?>
    <div class="card h-100 shadow-sm border-0 vid-card">
        <!-- Cuerpo de la Tarjeta -->
        <div class="card-body p-3 d-flex flex-column justify-content-between">
            <div>
                <!-- Badges de Estado, Duración y Jornada -->
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2 pb-1 border-bottom">
                    <span class="badge <?= $badgeClass; ?> px-2 py-1 shadow-sm">
                        <i class="fa-solid <?= $iconClass; ?> me-1"></i> <?= htmlspecialchars($estTexto); ?>
                    </span>
                    <div class="d-flex align-items-center gap-1">
                        <span class="badge bg-light text-muted border px-2 py-1">
                            <i class="fa-regular fa-clock me-1 text-primary"></i> <?=$duracion;?>
                        </span>
                        <span class="badge bg-light text-dark border px-2 py-1">
                            <i class="fa-solid fa-sun me-1 text-warning"></i> <?= htmlspecialchars($d['nomjor'] ?? ('Jornada ' . ($d['jornada'] ?? '1'))); ?>
                        </span>
                    </div>
                </div>

                <!-- Título del Aula -->
                <h6 class="fw-bold text-dark mb-1 text-truncate" title="<?=htmlspecialchars($d['nomaul'] ?? 'Aula ' . $d['idaul']);?>" style="font-size: 1.05rem;">
                    <span class="text-primary">#<?=$d['idpres'];?></span> - <?=htmlspecialchars($d['nomaul'] ?? 'Aula ' . $d['idaul']);?>
                </h6>
                <div class="text-muted small mb-3">
                    <i class="fa-solid fa-location-dot text-danger me-1"></i><?=$ubicacion;?>
                </div>

                <!-- Caja de Fechas (Inicio -> Fin) -->
                <div class="p-2 mb-3 rounded-3 bg-light border border-light-subtle d-flex justify-content-between align-items-center" style="font-size: 0.8rem;">
                    <div>
                        <span class="text-muted d-block" style="font-size: 0.72rem;">Recibido</span>
                        <span class="fw-bold text-dark"><?=$fechinFormateada;?></span>
                    </div>
                    <div class="text-muted"><i class="fa-solid fa-arrow-right-long mx-1"></i></div>
                    <div class="text-end">
                        <span class="text-muted d-block" style="font-size: 0.72rem;">Entrega</span>
                        <span class="fw-bold <?= $fechfinFormateada ? 'text-dark' : 'text-danger'; ?>">
                            <?= $fechfinFormateada ?: 'En curso'; ?>
                        </span>
                    </div>
                </div>

                <!-- Datos del Instructor -->
                <div class="d-flex justify-content-between text-muted mb-1" style="font-size: 0.78rem;">
                    <span><i class="fa-solid fa-user-check text-primary me-1"></i><strong>Instructor:</strong> <?= htmlspecialchars($d['nomusu'] ?? 'ID ' . $d['idusu']); ?></span>
                </div>
                <?php if (!empty($d['ndocusu'])) { ?>
                    <div class="text-muted small" style="font-size: 0.78rem;">
                        <i class="fa-solid fa-id-card text-secondary me-1"></i><strong>Doc:</strong> <?= htmlspecialchars($d['ndocusu']); ?>
                    </div>
                <?php } ?>
            </div>
        </div>

        <!-- Footer -->
        <div class="card-footer bg-white border-0 pt-0 pb-3 px-3">
            <hr class="mt-0 mb-2 text-muted opacity-25">
            <div class="d-flex justify-content-between align-items-center text-muted" style="font-size: 0.75rem;">
                <span>
                    <i class="fa-solid fa-hashtag me-1 text-primary"></i>Préstamo: <?=$d['idpres'];?>
                </span>
                <span>
                    <i class="fa-solid fa-calendar-day me-1 text-success"></i><?= (!empty($d['fechin']) && $d['fechin'] !== '0000-00-00 00:00:00') ? date('d/m/Y', strtotime($d['fechin'])) : ''; ?>
                </span>
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
        <div class="filter-pills-group" id="tabHistorico" role="tablist">
            <!-- Píldora En Curso -->
            <button class="btn-filter-pill active" id="activos-tab" data-bs-toggle="tab" data-bs-target="#activos" type="button" role="tab" aria-controls="activos" aria-selected="true" onclick="filterByTab('activos')">
                <span class="pill-dot" style="background-color: #8a6d00;"></span>
                <span>En Sesión</span>
                <span class="pill-count"><?= count($datEnCurso); ?></span>
            </button>

            <!-- Píldora Liberados -->
            <button class="btn-filter-pill" id="inactivos-tab" data-bs-toggle="tab" data-bs-target="#inactivos" type="button" role="tab" aria-controls="inactivos" aria-selected="false" onclick="filterByTab('inactivos')">
                <span class="pill-dot" style="background-color: #006b29;"></span>
                <span>Liberados</span>
                <span class="pill-count"><?= count($datLiberados); ?></span>
            </button>

            <!-- Píldora Todos -->
            <button class="btn-filter-pill" id="todos-tab" data-bs-toggle="tab" data-bs-target="#todos" type="button" role="tab" aria-controls="todos" aria-selected="false" onclick="filterByTab('todos')">
                <span class="pill-dot" style="background-color: #3c627f;"></span>
                <span>Todos</span>
                <span class="pill-count"><?= $totHistorico; ?></span>
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
            <input type="text" id="customVideoSearch" placeholder="Buscar en historial..." onkeyup="syncCustomSearch(this.value)">
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
<div class="tab-content" id="tabHistoricoContent">
    <!-- Pestaña 1: Préstamos en Curso -->
    <div class="tab-pane fade show active" id="activos" role="tabpanel" aria-labelledby="activos-tab">
        <table id="example" class="table table-striped table-vid-cards" style="width:100%">
            <thead>
                <tr>
                    <th>Préstamos en Curso</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($datEnCurso) { foreach ($datEnCurso as $d) { ?>
                <tr data-order="<?=$d['idpres'];?>">
                    <td data-search="<?=$d["idpres"];?> <?=$d["nomaul"];?> <?=$d["nomusu"];?> <?=$d["ndocusu"];?> <?=$d["piso"];?> <?=$d["bloqau"];?> <?=$d["estado"];?>">
                        <?php renderHistoricoCard($d); ?>
                    </td>
                </tr>
                <?php }} ?>
            </tbody>
            <tfoot>
                <tr>
                    <th>Préstamos en Curso</th>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Pestaña 2: Préstamos Liberados -->
    <div class="tab-pane fade" id="inactivos" role="tabpanel" aria-labelledby="inactivos-tab">
        <table id="myt" class="table table-striped table-vid-cards" style="width:100%">
            <thead>
                <tr>
                    <th>Préstamos Liberados</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($datLiberados) { foreach ($datLiberados as $d) { ?>
                <tr data-order="<?=$d['idpres'];?>">
                    <td data-search="<?=$d["idpres"];?> <?=$d["nomaul"];?> <?=$d["nomusu"];?> <?=$d["ndocusu"];?> <?=$d["piso"];?> <?=$d["bloqau"];?> <?=$d["estado"];?>">
                        <?php renderHistoricoCard($d); ?>
                    </td>
                </tr>
                <?php }} ?>
            </tbody>
            <tfoot>
                <tr>
                    <th>Préstamos Liberados</th>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Pestaña 3: Todo el Historial -->
    <div class="tab-pane fade" id="todos" role="tabpanel" aria-labelledby="todos-tab">
        <table id="tblTodos" class="table table-striped table-vid-cards" style="width:100%">
            <thead>
                <tr>
                    <th>Todo el Historial</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($dtHis) { foreach ($dtHis as $d) { ?>
                <tr data-order="<?=$d['idpres'];?>">
                    <td data-search="<?=$d["idpres"];?> <?=$d["nomaul"];?> <?=$d["nomusu"];?> <?=$d["ndocusu"];?> <?=$d["piso"];?> <?=$d["bloqau"];?> <?=$d["estado"];?>">
                        <?php renderHistoricoCard($d); ?>
                    </td>
                </tr>
                <?php }} ?>
            </tbody>
            <tfoot>
                <tr>
                    <th>Todo el Historial</th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>