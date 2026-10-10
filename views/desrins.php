<?php
/**
 * Vista Desrins (desrins.php)
 * Reporte de Asistencia / Inasistencia conectado a la planilla (minuta).
 * Estilo visual 100% igual a la vista de Videos TV (vvidgen.php):
 * - KPI cards institucionales con footer strip.
 * - Toolbar con píldoras por tipo, exportación, búsqueda en tiempo real,
 *   ordenamiento y conmutador Cuadrícula / Lista.
 * - Tarjetas (.table-vid-cards + .vid-card) gobernadas por css/style.css y js/valida.js.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../models/conexion.php';
require_once __DIR__ . '/../models/esmmin.php';
require_once __DIR__ . '/../models/desmins.php';

$modeloIna = new Desmins();

// Captura de filtros opcionales (GET desde el formulario del reporte)
$idfic = $_REQUEST['idfic'] ?? null;
$fin = $_REQUEST['fin'] ?? null;
$ffi = $_REQUEST['ffi'] ?? null;

$fichasDisponibles = $modeloIna->getFichas();
$metricas = $modeloIna->getMetricas($idfic, $fin, $ffi);
$registrosTotales = $modeloIna->getRegistros($idfic, $fin, $ffi, null);

// Clasificar registros por tipo para las pestañas
$regIngresos = array_values(array_filter($registrosTotales, function ($r) { return $r['tipmin'] === 'I'; }));
$regSalidas  = array_values(array_filter($registrosTotales, function ($r) { return $r['tipmin'] === 'S'; }));
$regFaltas   = array_values(array_filter($registrosTotales, function ($r) { return $r['tipmin'] === 'F'; }));

// Función auxiliar: pinta una tarjeta de registro idéntica en estructura a la de video
function renderCardRegistro($r)
{
    $tip = $r['tipmin'];
    $badgeCl = $tip === 'I' ? 'bg-success' : ($tip === 'S' ? 'bg-info' : 'bg-danger');
    $tipText = $tip === 'I' ? 'Ingreso' : ($tip === 'S' ? 'Salida' : 'Falta');

    // Degradado del preview según el tipo de movimiento
    $grad = $tip === 'I' ? 'linear-gradient(135deg, #198754 0%, #0f5132 100%)'
         : ($tip === 'S' ? 'linear-gradient(135deg, #0d6efd 0%, #084298 100%)'
         : 'linear-gradient(135deg, #dc3545 0%, #842029 100%)');

    $searchStr = htmlspecialchars(implode(' ', [
        $r['nummin'], $r['ndocusu'], $r['nomusu'], $r['idfic'] ?? '',
        $r['nomfic'], $r['nomper'], $r['nomval'], $r['fechos'],
        $r['fhlle'] ?? '', $r['tiempo'] ?? '', $r['obs'] ?? '', $tipText
    ]));
    ?>
    <tr data-order="<?= (int) $r['nummin']; ?>">
        <td data-search="<?= $searchStr; ?>">
            <!-- Tarjeta de Registro Estilo Videos TV (.vid-card) -->
            <div class="card h-100 shadow-sm border-0 vid-card">

                <!-- Cabecera / Preview con badges -->
                <div class="vid-preview-box" style="height: 150px; background: <?= $grad; ?>;">
                    <div class="vid-badges-top">
                        <span class="badge <?= $badgeCl; ?> shadow-sm px-2 py-1">
                            <i class="fa-solid fa-circle-dot me-1"></i> <?= $tipText; ?>
                        </span>
                        <span class="badge vid-badge-blur px-2 py-1 shadow-sm">
                            <i class="fa-solid fa-arrow-down-1-9 me-1 text-info"></i> #<?= (int) $r['nummin']; ?>
                        </span>
                    </div>

                    <div class="text-center text-white px-3 w-100">
                        <div class="fs-5 fw-bold text-truncate"><?= htmlspecialchars($r['nomusu']); ?></div>
                        <div class="small opacity-75">Doc: <?= htmlspecialchars($r['ndocusu']); ?></div>
                        <?php if (!empty($r['nomper'])): ?>
                            <span class="badge bg-white bg-opacity-25 border border-light mt-1"><?= htmlspecialchars($r['nomper']); ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="vid-badges-bottom">
                        <span class="badge vid-badge-blur px-2 py-1">
                            <i class="fa-regular fa-clock me-1 text-warning"></i> <?= htmlspecialchars($r['tiempo'] ?? '00:00:00'); ?>
                        </span>
                        <span class="badge vid-badge-blur px-2 py-1">
                            <i class="fa-solid fa-graduation-cap me-1 text-info"></i> Ficha: <?= htmlspecialchars($r['idfic'] ?? 'N/A'); ?>
                        </span>
                    </div>
                </div>

                <!-- Cuerpo de la tarjeta -->
                <div class="card-body p-3 d-flex flex-column justify-content-between">
                    <div>
                        <h6 class="fw-bold text-dark mb-1 text-truncate" title="<?= htmlspecialchars($r['nomfic']); ?>">
                            <i class="fa-solid fa-book-open text-success me-1"></i> <?= htmlspecialchars($r['nomfic']); ?>
                        </h6>
                        <?php if (!empty($r['nomval'])): ?>
                            <div class="text-muted small mb-2">
                                <i class="fa-solid fa-clock me-1 text-secondary"></i> <?= htmlspecialchars($r['nomval']); ?>
                            </div>
                        <?php endif; ?>

                        <!-- Caja de fechas (entrada / salida) -->
                        <div class="p-2 mb-2 rounded-3 bg-light border border-light-subtle d-flex justify-content-between align-items-center" style="font-size: 0.8rem;">
                            <div>
                                <span class="text-muted d-block" style="font-size: 0.72rem;">Entrada / Fecha</span>
                                <span class="fw-bold text-success" style="font-size: 0.82rem;">
                                    <i class="fa-regular fa-calendar-check me-1"></i><?= htmlspecialchars($r['fechos']); ?>
                                </span>
                            </div>
                            <div class="text-muted"><i class="fa-solid fa-arrow-right-long mx-1"></i></div>
                            <div class="text-end">
                                <span class="text-muted d-block" style="font-size: 0.72rem;">Salida / Cierre</span>
                                <span class="fw-bold text-secondary" style="font-size: 0.82rem;">
                                    <i class="fa-regular fa-calendar-xmark me-1"></i><?= htmlspecialchars($r['fhlle'] ?? '—'); ?>
                                </span>
                            </div>
                        </div>

                        <?php if (!empty($r['obs'])): ?>
                            <div class="text-muted small fst-italic text-truncate" title="<?= htmlspecialchars($r['obs']); ?>">
                                <i class="fa-regular fa-comment-dots me-1"></i>"<?= htmlspecialchars($r['obs']); ?>"
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Footer con datos de registro (estilo vid-actions-bar) -->
                <div class="card-footer bg-white border-0 pt-0 pb-3 px-3">
                    <hr class="mt-0 mb-3 text-muted opacity-25">
                    <div class="vid-actions-bar">
                        <div class="d-flex flex-column">
                            <span class="text-muted small" style="font-size: 0.72rem;">Documento</span>
                            <span class="fw-semibold text-primary small"><i class="fa-solid fa-id-card me-1"></i><?= htmlspecialchars($r['ndocusu']); ?></span>
                        </div>
                        <div class="d-flex flex-column align-items-end">
                            <span class="text-muted small" style="font-size: 0.72rem;">Estado</span>
                            <span class="badge <?= $badgeCl; ?>"><?= $tipText; ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </td>
    </tr>
    <?php
}

// Función auxiliar: pinta la tabla tipo .table-vid-cards (una sola columna con tarjetas)
function renderTablaRegistros($registros, $tableId, $titulo)
{
    ?>
    <table id="<?= $tableId; ?>" class="table table-striped table-vid-cards" style="width:100%">
        <thead>
            <tr>
                <th><?= htmlspecialchars($titulo); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($registros)): ?>
                <?php foreach ($registros as $r) { renderCardRegistro($r); } ?>
            <?php endif; ?>
        </tbody>
    </table>
    <?php
}
?>

<div class="conte py-2">
    <!-- Título del módulo -->
    <?php if (function_exists('titulo2')) {
        echo titulo2("<i class='fa-solid fa-clipboard-user'></i> Reporte de Asistencia / Inasistencia", 1);
    } else { ?>
        <div class="d-flex justify-content-between align-items-center flex-wrap mb-4">
            <h1 class="h2 fw-bold text-success mb-1">
                <i class="fa-solid fa-clipboard-user me-2"></i>Reporte de Asistencia / Inasistencia
            </h1>
            <span class="badge bg-success bg-opacity-10 text-success border border-success fs-6 px-3 py-2">
                <i class="fa-solid fa-eye me-1"></i>Solo lectura (Planilla Minuta)
            </span>
        </div>
    <?php } ?>

    <!-- =========================================================
         KPI CARDS (Estilo exacto de Videos TV)
         ========================================================= -->
    <div class="row my-4 g-4">
        <!-- Card 1: Total Registros -->
        <div class="col-12 col-md-3">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #3e4a3d;">Registros Periodo</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #0b1c30;"><?= (int) $metricas['total_registros']; ?></span>
                            <span style="font-size: 13px; color: #3e4a3d;">movimientos</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box" style="background-color: #b5dcfe; color: #3c627f;">
                        <i class="fa-solid fa-calendar-check fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 11px; color: #3c627f;">
                        <i class="fa-solid fa-arrow-trend-up" style="font-size: 12px;"></i> Acumulado
                    </span>
                    <span style="font-size: 11px; color: #3e4a3d;">Validez: 100%</span>
                </div>
            </div>
        </div>

        <!-- Card 2: Aprendices Únicos -->
        <div class="col-12 col-md-3">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #006b29;">Aprendices</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #006b29;"><?= (int) $metricas['total_aprendices']; ?></span>
                            <span style="font-size: 13px; color: rgba(0, 107, 41, 0.8);">únicos</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box" style="background-color: #7afd88; color: #006b24;">
                        <i class="fa-solid fa-users fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="fw-semibold d-inline-flex align-items-center gap-2" style="font-size: 11px; color: #006b29;">
                        <span class="pulse-dot"></span> Asistencia Activa
                    </span>
                    <span style="font-size: 11px; color: #3e4a3d;">Registrados</span>
                </div>
            </div>
        </div>

        <!-- Card 3: Fichas -->
        <div class="col-12 col-md-3">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #3c627f;">Fichas</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #0b1c30;"><?= (int) $metricas['total_fichas']; ?></span>
                            <span style="font-size: 13px; color: #3e4a3d;">programas</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box" style="background-color: #cff4fc; color: #055160;">
                        <i class="fa-solid fa-graduation-cap fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="d-inline-flex align-items-center gap-1" style="font-size: 11px; color: #3e4a3d;">
                        <i class="fa-solid fa-layer-group" style="font-size: 12px;"></i> Con movimientos
                    </span>
                    <span class="fw-semibold" style="font-size: 11px; color: #055160;">Activas</span>
                </div>
            </div>
        </div>

        <!-- Card 4: Registros Hoy -->
        <div class="col-12 col-md-3">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #b25e00;">Hoy</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #b25e00;"><?= (int) $metricas['registros_hoy']; ?></span>
                            <span style="font-size: 13px; color: #3e4a3d;">registros</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box" style="background-color: #ffe69c; color: #b25e00;">
                        <i class="fa-solid fa-calendar-day fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="d-inline-flex align-items-center gap-1" style="font-size: 11px; color: #3e4a3d;">
                        <i class="fa-solid fa-clock" style="font-size: 12px;"></i> Jornada en curso
                    </span>
                    <span class="fw-semibold" style="font-size: 11px; color: #b25e00;">En tiempo real</span>
                </div>
            </div>
        </div>
    </div>

    <!-- =========================================================
         FILTRO DE FECHA Y FICHA (reporte)
         ========================================================= -->
    <div class="card shadow-sm mb-4 border-0 rounded-4">
        <div class="card-body bg-light rounded-4 p-3">
            <form method="GET" action="home.php" class="row g-3 align-items-end">
                <input type="hidden" name="pg" value="<?= htmlspecialchars($_GET['pg'] ?? '1311'); ?>">
                <div class="col-lg-4 col-md-5">
                    <label class="form-label fw-bold text-secondary small" for="inpFicha">Ficha de Caracterización</label>
                    <input type="text" class="form-control form-control-sm bg-white" id="inpFicha" name="idfic" list="listaFichas" autocomplete="off"
                        value="<?= htmlspecialchars($idfic ?? ''); ?>" placeholder="Buscar o seleccionar ficha...">
                    <datalist id="listaFichas">
                        <?php foreach ($fichasDisponibles as $f): ?>
                            <option value="<?= htmlspecialchars($f['idfic']); ?>">
                                <?= htmlspecialchars($f['idfic'] . ' - ' . $f['nomfic'] . ($f['jornada'] ? ' (' . $f['jornada'] . ')' : '')); ?>
                            </option>
                        <?php endforeach; ?>
                    </datalist>
                </div>
                <div class="col-lg-2 col-md-3">
                    <label class="form-label fw-bold text-secondary small" for="inpFechaInicio">Fecha inicial</label>
                    <input type="date" class="form-control form-control-sm bg-white" id="inpFechaInicio" name="fin" value="<?= htmlspecialchars($fin ?? ''); ?>">
                </div>
                <div class="col-lg-2 col-md-3">
                    <label class="form-label fw-bold text-secondary small" for="inpFechaFin">Fecha final</label>
                    <input type="date" class="form-control form-control-sm bg-white" id="inpFechaFin" name="ffi" value="<?= htmlspecialchars($ffi ?? ''); ?>">
                </div>
                <div class="col-lg-4 col-md-12 d-flex gap-2">
                    <button type="submit" class="btn btn-success btn-sm flex-fill fw-semibold">
                        <i class="fa-solid fa-magnifying-glass me-1"></i>Filtrar reporte
                    </button>
                    <a href="home.php?pg=<?= htmlspecialchars($_GET['pg'] ?? '1311'); ?>" class="btn btn-outline-secondary btn-sm" title="Limpiar filtros">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- =========================================================
         TOOLBAR DE CONTROL (Estilo Videos TV: píldoras + export)
         ========================================================= -->
    <div class="filter-toolbar-container">
        <div class="toolbar-left-group">
            <div class="filter-pills-group" id="tabAsistencia" role="tablist">
                <!-- Píldora Todos (activa por defecto) -->
                <button class="btn-filter-pill active" id="todos-tab" data-bs-toggle="tab" data-bs-target="#pane-todos" type="button" role="tab" aria-controls="pane-todos" aria-selected="true" onclick="filterByTab('todos')">
                    <span class="pill-dot" style="background-color: #3c627f;"></span>
                    <span>Todos</span>
                    <span class="pill-count"><?= count($registrosTotales); ?></span>
                </button>

                <!-- Píldora Ingresos -->
                <button class="btn-filter-pill" id="ingresos-tab" data-bs-toggle="tab" data-bs-target="#pane-ingresos" type="button" role="tab" aria-controls="pane-ingresos" aria-selected="false" onclick="filterByTab('ingresos')">
                    <span class="pill-dot" style="background-color: #006b29;"></span>
                    <span>Ingresos</span>
                    <span class="pill-count"><?= count($regIngresos); ?></span>
                </button>

                <!-- Píldora Salidas -->
                <button class="btn-filter-pill" id="salidas-tab" data-bs-toggle="tab" data-bs-target="#pane-salidas" type="button" role="tab" aria-controls="pane-salidas" aria-selected="false" onclick="filterByTab('salidas')">
                    <span class="pill-dot" style="background-color: #0d6efd;"></span>
                    <span>Salidas</span>
                    <span class="pill-count"><?= count($regSalidas); ?></span>
                </button>

                <!-- Píldora Faltas -->
                <button class="btn-filter-pill" id="faltas-tab" data-bs-toggle="tab" data-bs-target="#pane-faltas" type="button" role="tab" aria-controls="pane-faltas" aria-selected="false" onclick="filterByTab('faltas')">
                    <span class="pill-dot" style="background-color: #ba1a1a;"></span>
                    <span>Faltas</span>
                    <span class="pill-count"><?= count($regFaltas); ?></span>
                </button>
            </div>

            <div class="toolbar-divider d-none d-sm-block"></div>

            <!-- Exportación -->
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

        <!-- Búsqueda, orden y vista grid/lista -->
        <div class="toolbar-search-sort-group">
            <div class="toolbar-search-box">
                <i class="fa-solid fa-magnifying-glass icoldiz"></i>
                <input type="text" id="customVideoSearch" placeholder="Buscar aprendiz, documento, ficha..." onkeyup="syncCustomSearch(this.value)">
            </div>

            <div class="toolbar-secondary-controls">
                <select class="toolbar-select-sort" id="customVideoSort" onchange="syncCustomSort(this.value)">
                    <option value="desc">Más reciente</option>
                    <option value="asc">Más antiguo</option>
                </select>

                <div class="toolbar-view-toggle">
                    <button type="button" class="toolbar-view-btn active" id="btnViewGrid" title="Vista Cuadrícula (Tarjetas)" onclick="switchCardView('grid')">
                        <i class="fa-solid fa-grip"></i>
                    </button>
                    <button type="button" class="toolbar-view-btn" id="btnViewList" title="Vista Lista (Tabla)" onclick="switchCardView('list')">
                        <i class="fa-solid fa-bars"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- =========================================================
         CONTENIDO: PESTAÑAS CON TARJETAS GRID (Estilo Videos TV)
         ========================================================= -->
    <div class="tab-content" id="tabAsistenciaContent">

        <div class="tab-pane fade show active" id="pane-todos" role="tabpanel" aria-labelledby="todos-tab">
            <?php renderTablaRegistros($registrosTotales, 'tblTodos', 'Registros de la Planilla (Todos)'); ?>
        </div>

        <div class="tab-pane fade" id="pane-ingresos" role="tabpanel" aria-labelledby="ingresos-tab">
            <?php renderTablaRegistros($regIngresos, 'tblIngresos', 'Registros de Ingresos'); ?>
        </div>

        <div class="tab-pane fade" id="pane-salidas" role="tabpanel" aria-labelledby="salidas-tab">
            <?php renderTablaRegistros($regSalidas, 'tblSalidas', 'Registros de Salidas'); ?>
        </div>

        <div class="tab-pane fade" id="pane-faltas" role="tabpanel" aria-labelledby="faltas-tab">
            <?php renderTablaRegistros($regFaltas, 'tblFaltas', 'Registros de Faltas'); ?>
        </div>

    </div>
</div>