<?php
require_once(__DIR__ . "/../controllers/descadsc.php");

// Página actual para enlaces dentro de las tarjetas (accesible desde funciones PHP locales)
$GLOBALS['ADSC_PG'] = isset($pg) ? (int) $pg : 2455;

/**
 * Tarjeta estilo Videos TV para el consolidado de fichas (Tab Dashboard)
 */
function renderCardFicha($f)
{
    $des   = (int) $f['desertados'];
    $tot   = (int) $f['total_aprendices'];
    $act   = (int) $f['activos'];
    $horas = (int) $f['total_horas_inasistencia'];
    $tasa  = (float) $f['tasa_desercion'];
    $grad  = $des > 0 ? 'linear-gradient(135deg, #dc3545 0%, #842029 100%)' : 'linear-gradient(135deg, #0d6efd 0%, #084298 100%)';
    $badge = $des > 0
        ? '<span class="badge bg-danger shadow-sm px-2 py-1"><i class="fa-solid fa-user-slash me-1"></i>Con deserción</span>'
        : '<span class="badge bg-primary shadow-sm px-2 py-1"><i class="fa-solid fa-users me-1"></i>Sin deserción</span>';
    $search = htmlspecialchars($f['idfic'] . ' ' . $f['nomfic'] . ' ' . $f['nompro'] . ' ' . $f['jornada']); ?>
    <tr data-order="<?= $des * 1000000 + $horas; ?>" data-cond="<?= $des > 0 ? 'con' : 'sin'; ?>">
        <td data-search="<?= $search; ?>">
            <div class="card h-100 shadow-sm border-0 vid-card">
                <div class="vid-preview-box" style="height: 130px; background: <?= $grad; ?>;">
                    <div class="vid-badges-top">
                        <?= $badge; ?>
                        <span class="badge vid-badge-blur px-2 py-1 shadow-sm">
                            <i class="fa-solid fa-hashtag me-1 text-info"></i><?= htmlspecialchars($f['idfic']); ?>
                        </span>
                    </div>
                    <div class="text-center text-white px-3 w-100">
                        <div class="fs-5 fw-bold text-truncate"><?= htmlspecialchars($f['nomfic']); ?></div>
                        <div class="small opacity-75 text-truncate"><?= htmlspecialchars($f['nompro']); ?></div>
                        <span class="badge bg-white bg-opacity-25 border border-light mt-1">
                            <i class="fa-regular fa-clock me-1"></i><?= htmlspecialchars($f['jornada']); ?>
                        </span>
                    </div>
                    <div class="vid-badges-bottom">
                        <span class="badge vid-badge-blur px-2 py-1">
                            <i class="fa-solid fa-graduation-cap me-1 text-info"></i> <?= $tot; ?> aprendices
                        </span>
                    </div>
                </div>
                <div class="card-body p-3 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small">Estudiantes</span>
                            <span class="fw-bold text-dark"><i class="fa-solid fa-users me-1 text-primary"></i> <?= $tot; ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small">Desertados</span>
                            <span class="badge <?= $des > 0 ? 'bg-danger' : 'bg-light text-muted border'; ?>"><?= $des; ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small">Activos</span>
                            <span class="fw-semibold text-success"><i class="fa-solid fa-user-check me-1"></i> <?= $act; ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small">Horas faltadas</span>
                            <span class="fw-semibold <?= $horas > 30 ? 'text-danger' : 'text-secondary'; ?>">
                                <i class="fa-regular fa-clock me-1"></i><?= $horas; ?> hrs
                            </span>
                        </div>
                        <div class="mb-1">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted small">Tasa de deserción</span>
                                <span class="fw-bold small <?= $tasa > 0 ? 'text-danger' : 'text-success'; ?>"><?= $tasa; ?>%</span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar <?= $tasa > 0 ? 'bg-danger' : 'bg-success'; ?>" style="width: <?= min(100, $tasa); ?>%;"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-white border-0 pt-0 pb-3 px-3">
                    <hr class="mt-0 mb-3 text-muted opacity-25">
                    <div class="vid-actions-bar">
                        <a href="home.php?pg=<?= (int) $GLOBALS['ADSC_PG']; ?>&tab=fichas&idfic=<?= urlencode($f['idfic']); ?>"
                            class="btn btn-outline-primary btn-sm shadow-sm">
                            <i class="fa-solid fa-users me-1"></i> Ver aprendices
                        </a>
                        <a href="home.php?pg=<?= (int) $GLOBALS['ADSC_PG']; ?>&tab=justificaciones&idfic=<?= urlencode($f['idfic']); ?>"
                            class="btn btn-outline-success btn-sm shadow-sm">
                            <i class="fa-solid fa-shield-halved me-1"></i> Soportes
                        </a>
                    </div>
                </div>
            </div>
        </td>
    </tr>
<?php
}

/**
 * Tarjeta estilo Videos TV para el listado de aprendices (Tab Fichas)
 */
function renderCardAprendiz($ap)
{
    $des   = (int) $ap['es_desertado'];
    $horas = (int) $ap['horasina'];
    if ($des === 1) {
        $cond  = 'desertado';
        $grad  = 'linear-gradient(135deg, #dc3545 0%, #842029 100%)';
        $badge = '<span class="badge bg-danger shadow-sm px-2 py-1"><i class="fa-solid fa-user-slash me-1"></i>DESERTADO</span>';
    } elseif ($horas >= 30) {
        $cond  = 'riesgo';
        $grad  = 'linear-gradient(135deg, #fd7e14 0%, #b25e00 100%)';
        $badge = '<span class="badge bg-warning text-dark shadow-sm px-2 py-1"><i class="fa-solid fa-triangle-exclamation me-1"></i>RIESGO (>30h)</span>';
    } else {
        $cond  = 'regular';
        $grad  = 'linear-gradient(135deg, #198754 0%, #0f5132 100%)';
        $badge = '<span class="badge bg-success shadow-sm px-2 py-1"><i class="fa-solid fa-user-check me-1"></i>Regular</span>';
    }
    $search = htmlspecialchars($ap['nomusu'] . ' ' . $ap['ndocusu'] . ' ' . $ap['nomest']); ?>
    <tr data-order="<?= $horas; ?>" data-cond="<?= $cond; ?>">
        <td data-search="<?= $search; ?>">
            <div class="card h-100 shadow-sm border-0 vid-card">
                <div class="vid-preview-box" style="height: 120px; background: <?= $grad; ?>;">
                    <div class="vid-badges-top">
                        <?= $badge; ?>
                        <span class="badge vid-badge-blur px-2 py-1 shadow-sm">
                            <i class="fa-solid fa-id-card me-1 text-info"></i><?= htmlspecialchars($ap['ndocusu']); ?>
                        </span>
                    </div>
                    <div class="text-center text-white px-3 w-100">
                        <div class="fs-5 fw-bold text-truncate"><?= htmlspecialchars($ap['nomusu']); ?></div>
                        <span class="badge bg-white bg-opacity-25 border border-light mt-1">
                            <i class="fa-solid fa-circle-check me-1"></i><?= htmlspecialchars($ap['nomest']); ?>
                        </span>
                    </div>
                    <div class="vid-badges-bottom">
                        <span class="badge vid-badge-blur px-2 py-1">
                            <i class="fa-solid fa-envelope me-1 text-info"></i> <?= htmlspecialchars($ap['emausu'] ?: 'Sin correo'); ?>
                        </span>
                    </div>
                </div>
                <div class="card-body p-3 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small">Horas faltadas</span>
                            <span class="adsc-hours-pill <?= $horas >= 30 ? 'danger' : ($horas > 0 ? 'warning' : 'normal'); ?>">
                                <?= $horas; ?> hrs
                            </span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small">Llamados verbales</span>
                            <span class="fw-bold text-secondary"><?= (int) $ap['llamver']; ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small">Llamados escritos</span>
                            <span class="fw-bold text-danger"><?= (int) $ap['llamesc']; ?></span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted small">Soportes pendientes</span>
                            <span class="badge <?= (int) $ap['just_pendientes'] > 0 ? 'bg-warning text-dark' : 'bg-light text-muted border'; ?>">
                                <?= (int) $ap['just_pendientes']; ?>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-white border-0 pt-0 pb-3 px-3">
                    <hr class="mt-0 mb-3 text-muted opacity-25">
                    <div class="vid-actions-bar">
                        <a href="home.php?pg=<?= (int) $GLOBALS['ADSC_PG']; ?>&tab=acta&idfic=<?= urlencode($ap['idfic'] ?? (isset($GLOBALS['ADSC_IDFIC_ACTUAL']) ? $GLOBALS['ADSC_IDFIC_ACTUAL'] : '')); ?>&idusu_sel=<?= (int) $ap['idusu']; ?>"
                            class="btn btn-warning btn-sm fw-bold shadow-sm">
                            <i class="fa-solid fa-file-lines me-1"></i> Acta Deserción
                        </a>
                        <button type="button" class="btn btn-outline-danger btn-sm shadow-sm" data-bs-toggle="modal"
                            data-bs-target="#modalFaltaDirecta_<?= (int) $ap['idusu']; ?>" title="Añadir falta directamente">
                            <i class="fa-solid fa-plus me-1"></i> Falta
                        </button>
                    </div>
                </div>
            </div>
        </td>
    </tr>
<?php
}
?>

<!-- Estilo general del proyecto + estilos propios del módulo de actas -->
<link rel="stylesheet" type="text/css" href="vendor/fontawesome/css/all.min.css">
<link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
<link rel="stylesheet" type="text/css" href="css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" type="text/css" href="css/style.css">
<link rel="stylesheet" type="text/css" href="css/menu.css">
<link rel="stylesheet" type="text/css" href="css/adsc.css">

<div class="adsc-container">
    <!-- Encabezado del Módulo -->
    <div class="d-flex justify-content-between align-items-center flex-wrap mb-4">
        <div>
            <h1 class="h2 fw-bold text-success mb-1">
                <i class="fa-solid fa-file-signature text-success me-2"></i>Módulo de Acta de Deserción
            </h1>
            <p class="text-muted fs-6 mb-0">
                Gestión integral de deserción estudiantil, validación de soportes médicos/legales e informes técnicos.
            </p>
        </div>
        <div class="d-flex align-items-center gap-2 mt-2 mt-md-0">
            <!-- Selector Rápido de Ficha -->
            <form method="POST" action="home.php?pg=<?= $pg; ?>&tab=<?= htmlspecialchars($tabActiva); ?>"
                class="d-flex align-items-center gap-2">
                <label for="selectFichaTop" class="fw-bold text-secondary text-nowrap mb-0 fs-6">Ficha Activa:</label>
                <select name="idfic_sel" id="selectFichaTop" class="form-select form-select-sm"
                    onchange="this.form.submit();">
                    <?php if (!empty($fichasDisponibles)): ?>
                        <?php foreach ($fichasDisponibles as $f): ?>
                            <option value="<?= htmlspecialchars($f['idfic']); ?>" <?= ($idficActiva == $f['idfic']) ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($f['idfic']); ?> - <?= htmlspecialchars($f['nomfic']); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <option value="">Sin fichas disponibles</option>
                    <?php endif; ?>
                </select>
            </form>
        </div>
    </div>

    <!-- Mensaje Flash / Alerta -->
    <?php if ($alerta): ?>
        <div class="adsc-alert-box adsc-alert-<?= ($alerta['tipo'] === 'success') ? 'success' : 'danger'; ?> alert-dismissible fade show"
            role="alert">
            <i
                class="fa-solid <?= ($alerta['tipo'] === 'success') ? 'fa-circle-check text-success' : 'fa-triangle-exclamation text-danger'; ?> fs-4"></i>
            <div class="flex-grow-1">
                <?= htmlspecialchars($alerta['mensaje']); ?>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Pestañas de Navegación del Módulo -->
    <ul class="nav nav-pills adsc-nav-pills mb-4" id="pills-adsc" role="tablist">
        <li class="nav-item" role="presentation">
            <a class="nav-link <?= ($tabActiva === 'dashboard') ? 'active' : ''; ?>"
                href="home.php?pg=<?= $pg; ?>&tab=dashboard<?= $idficActiva ? '&idfic=' . urlencode($idficActiva) : ''; ?>">
                <i class="fa-solid fa-chart-pie me-2"></i>Dashboard de Deserción
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link <?= ($tabActiva === 'fichas') ? 'active' : ''; ?>"
                href="home.php?pg=<?= $pg; ?>&tab=fichas<?= $idficActiva ? '&idfic=' . urlencode($idficActiva) : ''; ?>">
                <i class="fa-solid fa-users-viewfinder me-2"></i>Aprendices por Ficha
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link <?= ($tabActiva === 'acta') ? 'active' : ''; ?>"
                href="home.php?pg=<?= $pg; ?>&tab=acta<?= ($idficActiva && $idusuSel) ? '&idfic=' . urlencode($idficActiva) . '&idusu_sel=' . $idusuSel : ($idficActiva ? '&idfic=' . urlencode($idficActiva) : ''); ?>">
                <i class="fa-solid fa-file-lines me-2"></i>Acta de Deserción Detallada
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link <?= ($tabActiva === 'justificaciones') ? 'active' : ''; ?>"
                href="home.php?pg=<?= $pg; ?>&tab=justificaciones<?= $idficActiva ? '&idfic=' . urlencode($idficActiva) : ''; ?>">
                <i class="fa-solid fa-file-shield me-2"></i>Carga y Validación de Soportes
                <?php if (!empty($dashboardResumen['just_pendientes'])): ?>
                    <span class="badge bg-danger rounded-pill ms-2"><?= $dashboardResumen['just_pendientes']; ?></span>
                <?php endif; ?>
            </a>
        </li>
    </ul>

    <!-- =========================================================
         TAB 1: DASHBOARD DE DESERCIÓN POR FICHA
         ========================================================= -->
    <?php if ($tabActiva === 'dashboard'): ?>
        <!-- Tarjetas KPI (Estilo Videos TV) -->
        <div class="row my-4 g-4">
            <div class="col-12 col-md-6 col-xl-3">
                <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-uppercase fw-bold d-block" style="font-size: 11px; letter-spacing: 0.6px; color: #3e4a3d;">Fichas en Seguimiento</span>
                            <div class="d-flex align-items-baseline gap-2 mt-2">
                                <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #3c627f;"><?= (int) $dashboardResumen['total_fichas']; ?></span>
                                <span style="font-size: 13px; color: #3e4a3d;">fichas</span>
                            </div>
                        </div>
                        <div class="kpi-icon-box" style="background-color: #b5dcfe; color: #3c627f;">
                            <i class="fa-solid fa-layer-group fs-4"></i>
                        </div>
                    </div>
                    <div class="kpi-footer-strip">
                        <span class="fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 11px; color: #3c627f;">
                            <i class="fa-solid fa-arrow-trend-up" style="font-size: 12px;"></i> Monitoreadas
                        </span>
                        <span style="font-size: 11px; color: #3e4a3d;">Validez: 100%</span>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-xl-3">
                <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-uppercase fw-bold d-block" style="font-size: 11px; letter-spacing: 0.6px; color: #3e4a3d;">Estudiantes Desertados</span>
                            <div class="d-flex align-items-baseline gap-2 mt-2">
                                <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #ba1a1a;"><?= (int) $dashboardResumen['total_desertados']; ?></span>
                                <span style="font-size: 13px; color: #3e4a3d;">aprendices</span>
                            </div>
                        </div>
                        <div class="kpi-icon-box" style="background-color: #ffdad6; color: #ba1a1a;">
                            <i class="fa-solid fa-user-xmark fs-4"></i>
                        </div>
                    </div>
                    <div class="kpi-footer-strip">
                        <span class="fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 11px; color: #ba1a1a;">
                            <i class="fa-solid fa-user-slash" style="font-size: 12px;"></i> En estado de deserción
                        </span>
                        <span style="font-size: 11px; color: #3e4a3d;">Con acta</span>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-xl-3">
                <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-uppercase fw-bold d-block" style="font-size: 11px; letter-spacing: 0.6px; color: #3e4a3d;">Horas Inasistencia</span>
                            <div class="d-flex align-items-baseline gap-2 mt-2">
                                <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #b25e00;"><?= (int) $dashboardResumen['total_horas_ina']; ?></span>
                                <span style="font-size: 13px; color: #3e4a3d;">horas</span>
                            </div>
                        </div>
                        <div class="kpi-icon-box" style="background-color: #ffe69c; color: #b25e00;">
                            <i class="fa-solid fa-clock-rotate-left fs-4"></i>
                        </div>
                    </div>
                    <div class="kpi-footer-strip">
                        <span class="fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 11px; color: #b25e00;">
                            <i class="fa-regular fa-clock" style="font-size: 12px;"></i> Acumuladas
                        </span>
                        <span style="font-size: 11px; color: #3e4a3d;">En el sistema</span>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-xl-3">
                <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-uppercase fw-bold d-block" style="font-size: 11px; letter-spacing: 0.6px; color: #3e4a3d;">Soportes Pendientes</span>
                            <div class="d-flex align-items-baseline gap-2 mt-2">
                                <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #006b29;"><?= (int) $dashboardResumen['just_pendientes']; ?></span>
                                <span style="font-size: 13px; color: #3e4a3d;">soportes</span>
                            </div>
                        </div>
                        <div class="kpi-icon-box" style="background-color: #7afd88; color: #006b24;">
                            <i class="fa-solid fa-shield-halved fs-4"></i>
                        </div>
                    </div>
                    <div class="kpi-footer-strip">
                        <span class="fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 11px; color: #006b29;">
                            <i class="fa-solid fa-circle-pause pulse-dot" style="font-size: 12px;"></i> Revisión activa
                        </span>
                        <span style="font-size: 11px; color: #3e4a3d;">Pendientes</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Toolbar + Tarjetas de Fichas (Estilo Videos TV) -->
        <div class="filter-toolbar-container mt-2 mb-2">
            <div class="toolbar-left-group">
                <div class="filter-pills-group" role="tablist">
                    <button class="btn-filter-pill active" id="fich-todas" type="button" onclick="filtrarFichas('todas')">
                        <span class="pill-dot" style="background-color: #3c627f;"></span>
                        <span>Todas</span>
                        <span class="pill-count"><?= count($fichasDesercion); ?></span>
                    </button>
                    <button class="btn-filter-pill" id="fich-con" type="button" onclick="filtrarFichas('con')">
                        <span class="pill-dot" style="background-color: #ba1a1a;"></span>
                        <span>Con desertados</span>
                        <span class="pill-count"><?= count(array_filter($fichasDesercion, function ($f) { return (int) $f['desertados'] > 0; })); ?></span>
                    </button>
                    <button class="btn-filter-pill" id="fich-sin" type="button" onclick="filtrarFichas('sin')">
                        <span class="pill-dot" style="background-color: #006b29;"></span>
                        <span>Sin desertados</span>
                        <span class="pill-count"><?= count(array_filter($fichasDesercion, function ($f) { return (int) $f['desertados'] === 0; })); ?></span>
                    </button>
                </div>
                <div class="toolbar-divider d-none d-sm-block"></div>
                <div class="toolbar-export-buttons">
                    <button type="button" class="btn btn-sm" style="background: #0b1c30; color: #fff;" onclick="triggerDTExport('copy')">
                        <i class="fa-solid fa-copy"></i>
                    </button>
                    <button type="button" class="btn btn-sm" style="background: #006b29; color: #fff;" onclick="triggerDTExport('csv')">
                        <i class="fa-solid fa-file-csv"></i>
                    </button>
                    <button type="button" class="btn btn-sm" style="background: #3c627f; color: #fff;" onclick="triggerDTExport('excel')">
                        <i class="fa-solid fa-file-excel"></i>
                    </button>
                </div>
            </div>
            <div class="toolbar-search-sort-group">
                <div class="toolbar-search-box">
                    <i class="fa-solid fa-magnifying-glass icoldiz"></i>
                    <input type="text" id="customVideoSearch" placeholder="Buscar ficha, programa, jornada..." onkeyup="syncCustomSearch(this.value)">
                </div>
                <div class="toolbar-secondary-controls">
                    <select class="toolbar-select-sort" id="customVideoSort" onchange="syncCustomSort(this.value)">
                        <option value="desc">Mayor deserción</option>
                        <option value="asc">Menor deserción</option>
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

        <div class="tab-content" id="tabFichasContent">
            <div class="tab-pane fade show active">
                <table id="tblDashboardFichas" class="table table-striped table-vid-cards" style="width: 100%;">
                    <thead>
                        <tr>
                            <th>Consolidado de Deserción por Ficha</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($fichasDesercion)): ?>
                            <?php foreach ($fichasDesercion as $f): ?>
                                <?php renderCardFicha($f); ?>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td class="text-center py-4 text-muted">No hay fichas para mostrar.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

    <!-- =========================================================
         TAB 2: LISTADO DE APRENDICES POR FICHA
         ========================================================= -->
    <?php if ($tabActiva === 'fichas'): ?>
        <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
            <h3 class="fw-bold text-secondary mb-0">
                <i class="fa-solid fa-graduation-cap text-success me-2"></i>Aprendices de la Ficha:
                <span class="text-dark"><?= htmlspecialchars($idficActiva); ?></span>
            </h3>
            <a href="home.php?pg=<?= $pg; ?>&tab=dashboard" class="btn btn-outline-secondary btn-sm shadow-sm">
                <i class="fa-solid fa-arrow-left me-1"></i>Volver al Dashboard
            </a>
        </div>

        <!-- Toolbar + Tarjetas de Aprendices (Estilo Videos TV) -->
        <div class="filter-toolbar-container mb-2">
            <div class="toolbar-left-group">
                <div class="filter-pills-group" role="tablist">
                    <button class="btn-filter-pill active" id="apr-todos" type="button" onclick="filtrarAprendices('todos')">
                        <span class="pill-dot" style="background-color: #3c627f;"></span>
                        <span>Todos</span>
                        <span class="pill-count"><?= count($aprendicesFicha); ?></span>
                    </button>
                    <button class="btn-filter-pill" id="apr-desertado" type="button" onclick="filtrarAprendices('desertado')">
                        <span class="pill-dot" style="background-color: #ba1a1a;"></span>
                        <span>Desertados</span>
                        <span class="pill-count"><?= count(array_filter($aprendicesFicha, function ($a) { return (int) $a['es_desertado'] === 1; })); ?></span>
                    </button>
                    <button class="btn-filter-pill" id="apr-riesgo" type="button" onclick="filtrarAprendices('riesgo')">
                        <span class="pill-dot" style="background-color: #fd7e14;"></span>
                        <span>En riesgo</span>
                        <span class="pill-count"><?= count(array_filter($aprendicesFicha, function ($a) { return (int) $a['es_desertado'] !== 1 && (int) $a['horasina'] >= 30; })); ?></span>
                    </button>
                    <button class="btn-filter-pill" id="apr-regular" type="button" onclick="filtrarAprendices('regular')">
                        <span class="pill-dot" style="background-color: #006b29;"></span>
                        <span>Regulares</span>
                        <span class="pill-count"><?= count(array_filter($aprendicesFicha, function ($a) { return (int) $a['es_desertado'] !== 1 && (int) $a['horasina'] < 30; })); ?></span>
                    </button>
                </div>
                <div class="toolbar-divider d-none d-sm-block"></div>
                <div class="toolbar-export-buttons">
                    <button type="button" class="btn btn-sm" style="background: #0b1c30; color: #fff;" onclick="triggerDTExport('copy')">
                        <i class="fa-solid fa-copy"></i>
                    </button>
                    <button type="button" class="btn btn-sm" style="background: #006b29; color: #fff;" onclick="triggerDTExport('csv')">
                        <i class="fa-solid fa-file-csv"></i>
                    </button>
                    <button type="button" class="btn btn-sm" style="background: #3c627f; color: #fff;" onclick="triggerDTExport('excel')">
                        <i class="fa-solid fa-file-excel"></i>
                    </button>
                </div>
            </div>
            <div class="toolbar-search-sort-group">
                <div class="toolbar-search-box">
                    <i class="fa-solid fa-magnifying-glass icoldiz"></i>
                    <input type="text" id="customVideoSearch" placeholder="Buscar aprendiz, documento, estado..." onkeyup="syncCustomSearch(this.value)">
                </div>
                <div class="toolbar-secondary-controls">
                    <select class="toolbar-select-sort" id="customVideoSort" onchange="syncCustomSort(this.value)">
                        <option value="desc">Más horas</option>
                        <option value="asc">Menos horas</option>
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

        <div class="tab-content" id="tabAprendicesContent">
            <div class="tab-pane fade show active">
                <table id="tblAprendicesFicha" class="table table-striped table-vid-cards" style="width: 100%;">
                    <thead>
                        <tr>
                            <th>Aprendices de la Ficha <?= htmlspecialchars($idficActiva); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($aprendicesFicha)): ?>
                            <?php $GLOBALS['ADSC_IDFIC_ACTUAL'] = $idficActiva; ?>
                            <?php foreach ($aprendicesFicha as $ap): ?>
                                <?php renderCardAprendiz($ap); ?>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td class="text-center py-4 text-muted">
                                    No se encontraron aprendices registrados en la ficha seleccionada.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modales de falta directa (uno por aprendiz) -->
        <?php if (!empty($aprendicesFicha)): ?>
            <?php foreach ($aprendicesFicha as $ap): ?>
                <div class="modal fade" id="modalFaltaDirecta_<?= (int) $ap['idusu']; ?>" tabindex="-1"
                    aria-hidden="true">
                    <div class="modal-dialog">
                        <form method="POST" action="home.php?pg=<?= $pg; ?>&tab=fichas">
                            <input type="hidden" name="accion" value="agregar_falta_manual">
                            <input type="hidden" name="idusu_falta" value="<?= (int) $ap['idusu']; ?>">
                            <input type="hidden" name="idfic_falta"
                                value="<?= htmlspecialchars($idficActiva); ?>">
                            <div class="modal-content">
                                <div class="modal-header bg-danger text-white">
                                    <h5 class="modal-title">
                                        <i class="fa-solid fa-calendar-xmark me-2"></i>Añadir Falta al Acta de
                                        Inasistencias
                                    </h5>
                                    <button type="button" class="btn-close btn-close-white"
                                        data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold text-secondary">Aprendiz:</label>
                                        <div class="p-2 bg-light border rounded fw-bold text-dark">
                                            <?= htmlspecialchars($ap['nomusu']); ?>
                                            (<?= htmlspecialchars($ap['ndocusu']); ?>)
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="fecina_<?= (int) $ap['idusu']; ?>"
                                                class="form-label fw-bold">Fecha de la Falta:</label>
                                            <input type="date" name="fecina"
                                                id="fecina_<?= (int) $ap['idusu']; ?>" class="form-control"
                                                value="<?= date('Y-m-d'); ?>" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="horasina_<?= (int) $ap['idusu']; ?>"
                                                class="form-label fw-bold">Horas de Falta:</label>
                                            <input type="number" name="horasina"
                                                id="horasina_<?= (int) $ap['idusu']; ?>" class="form-control"
                                                value="6" min="1" max="24" required>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="comina_<?= (int) $ap['idusu']; ?>"
                                            class="form-label fw-bold">Motivo / Justificación Invalidad:</label>
                                        <textarea name="comina" id="comina_<?= (int) $ap['idusu']; ?>"
                                            class="form-control" rows="3"
                                            placeholder="Ej: Inasistencia no justificada / Documento presentado no válido"
                                            required></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary"
                                        data-bs-dismiss="modal">Cancelar</button>
                                    <button type="submit" class="btn btn-danger fw-bold">
                                        <i class="fa-solid fa-check me-1"></i>Registrar Falta en Acta
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    <?php endif; ?>

    <!-- =========================================================
         TAB 3: ACTA DE DESERCIÓN DETALLADA DEL ESTUDIANTE
         ========================================================= -->
    <?php if ($tabActiva === 'acta'): ?>
        <?php if ($detalleActa && !empty($detalleActa['estudiante'])): ?>
            <?php $est = $detalleActa['estudiante']; ?>
            <!-- Banner del Estudiante -->
            <div class="adsc-student-banner">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <div class="badge bg-warning text-dark px-3 py-1 mb-2 fw-bold">
                            <i class="fa-solid fa-id-card me-1"></i><?= htmlspecialchars($est['tipdoc']); ?>:
                            <?= htmlspecialchars($est['ndocusu']); ?>
                        </div>
                        <h2 class="adsc-student-name"><?= htmlspecialchars($est['nomusu']); ?></h2>
                        <div class="adsc-student-meta">
                            <span><i class="fa-solid fa-graduation-cap me-1"></i>Ficha:
                                <strong><?= htmlspecialchars($est['idfic']); ?> -
                                    <?= htmlspecialchars($est['nomfic']); ?></strong></span>
                            <span><i class="fa-solid fa-book-open me-1"></i>Programa:
                                <?= htmlspecialchars($est['nompro']); ?></span>
                            <span><i class="fa-solid fa-sun me-1"></i>Jornada: <?= htmlspecialchars($est['jornada']); ?></span>
                            <span><i class="fa-solid fa-signal me-1"></i>Estado: <span
                                    class="badge bg-light text-dark fw-bold"><?= htmlspecialchars($est['nomest']); ?></span></span>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-danger fw-bold" data-bs-toggle="modal"
                            data-bs-target="#modalAñadirFaltaActa">
                            <i class="fa-solid fa-calendar-plus me-1"></i>Añadir Falta Directa
                        </button>
                        <?php if (empty($detalleActa['acta_existente'])): ?>
                            <button type="button" class="btn btn-warning fw-bold text-dark" data-bs-toggle="modal"
                                data-bs-target="#modalFormalizarActa">
                                <i class="fa-solid fa-stamp me-1"></i>Formalizar Acta Deserción
                            </button>
                        <?php else: ?>
                            <a href="views/pdfdes.php?idact=<?= (int) $detalleActa['acta_existente']['idact']; ?>" target="_blank"
                                class="btn btn-light fw-bold text-dark">
                                <i class="fa-solid fa-print me-1"></i>Imprimir Acta N°
                                <?= (int) $detalleActa['acta_existente']['idact']; ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Resumen de Inasistencias y Resultados -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="adsc-card-box p-3 text-center">
                        <div class="text-muted fw-bold text-uppercase fs-6">Horas Faltadas Acumuladas</div>
                        <div
                            class="display-5 fw-bold <?= ($detalleActa['total_horas_faltadas'] >= 30) ? 'text-danger' : 'text-primary'; ?> my-2">
                            <?= (int) $detalleActa['total_horas_faltadas']; ?> <span class="fs-4">hrs</span>
                        </div>
                        <?php if ($detalleActa['total_horas_faltadas'] >= 30): ?>
                            <div class="badge bg-danger px-3 py-2 fs-6">
                                <i class="fa-solid fa-triangle-exclamation me-1"></i>Supera límite (> 30 hrs) - Causal Deserción
                            </div>
                        <?php else: ?>
                            <div class="badge bg-success px-3 py-2 fs-6">
                                <i class="fa-solid fa-shield-check me-1"></i>Dentro de margen permitido (< 30 hrs) </div>
                                <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="adsc-card-box p-3 text-center">
                            <div class="text-muted fw-bold text-uppercase fs-6">Total Días con Inasistencia</div>
                            <div class="display-5 fw-bold text-secondary my-2">
                                <?= count($detalleActa['fechas_faltas']); ?> <span class="fs-4">sesiones</span>
                            </div>
                            <div class="text-muted fs-6">
                                <?= !empty($detalleActa['fechas_faltas']) ? 'Última falta: ' . htmlspecialchars($detalleActa['fechas_faltas'][0]['fecha']) : 'Sin registros de inasistencias'; ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="adsc-card-box p-3 text-center">
                            <div class="text-muted fw-bold text-uppercase fs-6">Resultados No Aprobados / Atrasados</div>
                            <div class="display-5 fw-bold text-warning my-2">
                                <?= count($detalleActa['resultados']); ?> <span class="fs-4">resultados</span>
                            </div>
                            <div class="text-muted fs-6">
                                Competencias pendientes de evaluación o aprobación
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECCIÓN: RESULTADOS NO APROBADOS Y ATRASADOS -->
                <div class="adsc-card-box mb-4">
                    <div class="adsc-card-header bg-light">
                        <h3 class="adsc-card-title text-danger">
                            <i class="fa-solid fa-list-check me-2"></i>Resultados de Aprendizaje No Aprobados y Atrasados
                        </h3>
                        <span class="text-muted fs-6">Los resultados con fecha límite vencida aparecen marcados claramente como
                            <strong>[ATRASADO]</strong></span>
                    </div>
                    <div class="adsc-card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle adsc-table-custom w-100">
                                <thead>
                                    <tr>
                                        <th>Cód. Resultado</th>
                                        <th>Resultado de Aprendizaje</th>
                                        <th>Competencia</th>
                                        <th class="text-center">Calificación Actual</th>
                                        <th class="text-center">Fecha Programada / Límite</th>
                                        <th class="text-center">Estado en Acta</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($detalleActa['resultados'])): ?>
                                        <?php foreach ($detalleActa['resultados'] as $r): ?>
                                            <tr class="<?= ((int) $r['atrasado'] === 1) ? 'table-danger' : ''; ?>">
                                                <td class="fw-bold">
                                                    <?= htmlspecialchars($r['idres']); ?>
                                                </td>
                                                <td>
                                                    <div class="fw-semibold text-dark"><?= htmlspecialchars($r['nomres']); ?></div>
                                                </td>
                                                <td>
                                                    <small class="text-secondary"><?= htmlspecialchars($r['descom']); ?></small>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-secondary fs-6">
                                                        <?= htmlspecialchars($r['caljui']); ?>
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <?= !empty($r['fecha_programada']) ? htmlspecialchars($r['fecha_programada']) : (!empty($r['fecjuieva']) ? htmlspecialchars($r['fecjuieva']) : 'Sin fecha fija'); ?>
                                                </td>
                                                <td class="text-center">
                                                    <?php if ((int) $r['atrasado'] === 1): ?>
                                                        <span class="badge-atrasado">
                                                            <i class="fa-solid fa-triangle-exclamation"></i>ATRASADO
                                                        </span>
                                                    <?php elseif ((int) $r['no_aprobado'] === 1): ?>
                                                        <span class="badge-no-aprobado">
                                                            <i class="fa-solid fa-xmark me-1"></i>No Aprobado
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge-aprobado">
                                                            <i class="fa-solid fa-check me-1"></i>Pendiente
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-4 text-muted">
                                                <i class="fa-solid fa-circle-check text-success fs-3 d-block mb-2"></i>
                                                El aprendiz no tiene resultados no aprobados ni atrasados registrados.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- SECCIÓN: HISTORIAL DETALLADO DE INASISTENCIAS (FECHAS Y HORAS) -->
                <div class="adsc-card-box">
                    <div class="adsc-card-header bg-light">
                        <h3 class="adsc-card-title text-primary">
                            <i class="fa-solid fa-calendar-days me-2"></i>Detalle de Inasistencias (Fechas y Cantidad de Horas
                            Faltadas)
                        </h3>
                        <span class="badge bg-primary fs-6">Total Horas: <?= (int) $detalleActa['total_horas_faltadas']; ?>
                            hrs</span>
                    </div>
                    <div class="adsc-card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle adsc-table-custom w-100">
                                <thead>
                                    <tr>
                                        <th>N° Registro</th>
                                        <th>Fecha de la Falta</th>
                                        <th class="text-center">Horas Faltadas</th>
                                        <th>Motivo / Comentario de la Falta</th>
                                        <th>Fecha de Registro</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($detalleActa['inasistencias'])): ?>
                                        <?php foreach ($detalleActa['inasistencias'] as $ina): ?>
                                            <?php $esPorInvalido = (strpos($ina['comina'], 'FALTA APLICADA POR INSTRUCTOR') !== false); ?>
                                            <tr class="<?= $esPorInvalido ? 'table-warning' : ''; ?>">
                                                <td class="fw-bold">#<?= htmlspecialchars($ina['idina']); ?></td>
                                                <td class="fw-bold text-dark">
                                                    <i
                                                        class="fa-solid fa-calendar-xmark text-danger me-2"></i><?= htmlspecialchars($ina['fecina']); ?>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-danger rounded-pill px-3 py-2 fs-6">
                                                        <?= (int) $ina['horasina']; ?> horas
                                                    </span>
                                                </td>
                                                <td>
                                                    <div><?= htmlspecialchars($ina['comina']); ?></div>
                                                    <?php if ($esPorInvalido): ?>
                                                        <span class="badge bg-danger text-white mt-1">
                                                            <i class="fa-solid fa-gavel me-1"></i>Falta impuesta tras invalidar
                                                            justificación falsa
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-muted"><small><?= htmlspecialchars($ina['fecreg']); ?></small></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-muted">
                                                <i class="fa-solid fa-circle-check text-success fs-3 d-block mb-2"></i>
                                                No se registran inasistencias para este aprendiz.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Modal Añadir Falta Directa en el Acta -->
                <div class="modal fade" id="modalAñadirFaltaActa" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                        <form method="POST" action="home.php?pg=<?= $pg; ?>&tab=acta">
                            <input type="hidden" name="accion" value="agregar_falta_manual">
                            <input type="hidden" name="idusu_falta" value="<?= (int) $est['idusu']; ?>">
                            <input type="hidden" name="idfic_falta" value="<?= htmlspecialchars($idficActiva); ?>">
                            <div class="modal-content">
                                <div class="modal-header bg-danger text-white">
                                    <h5 class="modal-title">
                                        <i class="fa-solid fa-gavel me-2"></i>Añadir Falta Directamente al Acta
                                    </h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <p class="text-muted fs-6">
                                        Como instructor, puedes registrar una falta disciplinaria directamente en el acta de
                                        inasistencias del aprendiz.
                                    </p>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Aprendiz:</label>
                                        <div class="p-2 bg-light border rounded fw-bold text-dark">
                                            <?= htmlspecialchars($est['nomusu']); ?> (<?= htmlspecialchars($est['ndocusu']); ?>)
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="fecina_acta" class="form-label fw-bold">Fecha de la Falta:</label>
                                            <input type="date" name="fecina" id="fecina_acta" class="form-control"
                                                value="<?= date('Y-m-d'); ?>" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="horasina_acta" class="form-label fw-bold">Cantidad de Horas:</label>
                                            <input type="number" name="horasina" id="horasina_acta" class="form-control"
                                                value="6" min="1" max="24" required>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="comina_acta" class="form-label fw-bold">Motivo / Causa de la Falta:</label>
                                        <textarea name="comina" id="comina_acta" class="form-control" rows="3"
                                            placeholder="Ej: Falta injustificada / Falsedad en soporte presentado"
                                            required></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                    <button type="submit" class="btn btn-danger fw-bold">
                                        <i class="fa-solid fa-plus me-1"></i>Registrar Falta en Acta
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Modal Formalizar Acta de Deserción -->
                <div class="modal fade" id="modalFormalizarActa" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <form method="POST" action="home.php?pg=<?= $pg; ?>&tab=acta">
                            <input type="hidden" name="accion" value="formalizar_acta">
                            <input type="hidden" name="idusu_acta" value="<?= (int) $est['idusu']; ?>">
                            <input type="hidden" name="idfic_acta" value="<?= htmlspecialchars($idficActiva); ?>">
                            <div class="modal-content">
                                <div class="modal-header bg-warning text-dark">
                                    <h5 class="modal-title fw-bold">
                                        <i class="fa-solid fa-stamp me-2"></i>Formalizar Acta de Deserción Oficial
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="alert alert-warning">
                                        <i class="fa-solid fa-triangle-exclamation me-1"></i>
                                        Esta acción registrará formalmente el acta de deserción en el sistema y cambiará el
                                        estado del aprendiz a <strong>Deserción (actusu = 3)</strong>.
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Coordinador Destino:</label>
                                        <select name="coorusu" class="form-select" required>
                                            <?php foreach ($coordinadores as $co): ?>
                                                <option value="<?= (int) $co['idusu']; ?>"><?= htmlspecialchars($co['nomusu']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Consideraciones y Fundamentos de la Deserción:</label>
                                        <textarea name="conact" class="form-control" rows="5" required
                                            placeholder="Detalla el informe técnico de inasistencias continuas, llamados de atención previos y resultados no aprobados/atrasados..."></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                    <button type="submit" class="btn btn-warning fw-bold text-dark">
                                        <i class="fa-solid fa-check me-1"></i>Generar y Guardar Acta
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <div class="adsc-empty-state">
                    <i class="fa-solid fa-user-xmark"></i>
                    <h4>Seleccione un aprendiz para consultar su Acta de Deserción</h4>
                    <p>Elija un estudiante desde la pestaña "Aprendices por Ficha" para visualizar sus resultados atrasados,
                        inasistencias y gestión de actas.</p>
                    <a href="home.php?pg=<?= $pg; ?>&tab=fichas&idfic=<?= urlencode($idficActiva); ?>"
                        class="btn btn-success fw-bold mt-3">
                        <i class="fa-solid fa-users me-2"></i>Ir a Lista de Aprendices
                    </a>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <!-- =========================================================
         TAB 4: CARGA Y VALIDACIÓN DE DOCUMENTOS DE JUSTIFICACIÓN
         ========================================================= -->
        <?php if ($tabActiva === 'justificaciones'): ?>
            <div class="row">
                <!-- Formulario de Carga (Aprendiz / Usuario) -->
                <div class="col-lg-4 mb-4">
                    <div class="adsc-card-box">
                        <div class="adsc-card-header bg-success text-white">
                            <h3 class="adsc-card-title text-white">
                                <i class="fa-solid fa-cloud-arrow-up me-2"></i>Subir Justificación de Inasistencia
                            </h3>
                        </div>
                        <div class="adsc-card-body">
                            <form method="POST" action="home.php?pg=<?= $pg; ?>&tab=justificaciones"
                                enctype="multipart/form-data">
                                <input type="hidden" name="accion" value="subir_justificacion">
                                <input type="hidden" name="idfic_just" value="<?= htmlspecialchars($idficActiva); ?>">

                                <!-- Selección de Aprendiz -->
                                <div class="mb-3">
                                    <label for="idusu_just" class="form-label fw-bold">Aprendiz que justifica:</label>
                                    <select name="idusu_just" id="idusu_just" class="form-select" required>
                                        <option value="">-- Seleccione el aprendiz --</option>
                                        <?php if (!empty($aprendicesFicha)): ?>
                                            <?php foreach ($aprendicesFicha as $ap): ?>
                                                <option value="<?= (int) $ap['idusu']; ?>" <?= ($idusuSel == $ap['idusu']) ? 'selected' : ''; ?>>
                                                    <?= htmlspecialchars($ap['nomusu']); ?>
                                                    (<?= htmlspecialchars($ap['ndocusu']); ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>

                                <!-- Tipo de Documento Válido (Lista Blanca Estricta) -->
                                <div class="mb-3">
                                    <label for="tipodoc" class="form-label fw-bold">Tipo de Justificación
                                        Autorizada:</label>
                                    <select name="tipodoc" id="tipodoc" class="form-select" required>
                                        <option value="">-- Seleccione tipo válido --</option>
                                        <?php foreach ($tiposDocValidos as $tipo): ?>
                                            <option value="<?= htmlspecialchars($tipo); ?>">
                                                <?= htmlspecialchars($tipo); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="form-text text-muted">
                                        <i class="fa-solid fa-circle-info text-primary me-1"></i>
                                        Reglamento SENA: Solo se admiten Citas médicas, Incapacidades o Muerte de familiar
                                        de 1er grado.
                                    </div>
                                </div>

                                <!-- Fecha de la Falta Justificada -->
                                <div class="mb-3">
                                    <label for="fecjust" class="form-label fw-bold">Fecha de la Ausencia:</label>
                                    <input type="date" name="fecjust" id="fecjust" class="form-control"
                                        value="<?= date('Y-m-d'); ?>" required>
                                </div>

                                <!-- Archivo de Soporte -->
                                <div class="mb-3">
                                    <label for="archivo_just" class="form-label fw-bold">Archivo Adjunto (Soporte):</label>
                                    <input type="file" name="archivo_just" id="archivo_just" class="form-control"
                                        accept=".pdf, .jpg, .jpeg, .png" required>
                                    <div class="form-text text-muted">Formatos permitidos: PDF, JPG, PNG (Máx 5MB).</div>
                                </div>

                                <!-- Observaciones del Aprendiz -->
                                <div class="mb-3">
                                    <label for="obsjust" class="form-label fw-bold">Observaciones / Detalles:</label>
                                    <textarea name="obsjust" id="obsjust" class="form-control" rows="3"
                                        placeholder="Información adicional del soporte médico o legal..."></textarea>
                                </div>

                                <button type="submit" class="btn btn-success fw-bold w-100 py-2">
                                    <i class="fa-solid fa-upload me-1"></i>Cargar Documento de Justificación
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Listado y Validación de Soportes (Autoridad del Instructor) -->
                <div class="col-lg-8">
                    <div class="adsc-card-box">
                        <div class="adsc-card-header">
                            <h3 class="adsc-card-title text-navy">
                                <i class="fa-solid fa-clipboard-check text-success"></i>Bandeja de Validación de Soportes
                                (Ficha <?= htmlspecialchars($idficActiva); ?>)
                            </h3>
                            <span class="badge bg-secondary fs-6"><?= count($justificaciones); ?> soportes</span>
                        </div>
                        <div class="adsc-card-body">
                            <div class="table-responsive">
                                <table id="tblJustificaciones"
                                    class="table table-hover align-middle adsc-table-custom w-100">
                                    <thead>
                                        <tr>
                                            <th>Aprendiz</th>
                                            <th>Tipo de Documento</th>
                                            <th>Fecha Falta</th>
                                            <th class="text-center">Soporte</th>
                                            <th class="text-center">Estado</th>
                                            <th class="text-center">Acción Instructor</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($justificaciones)): ?>
                                            <?php foreach ($justificaciones as $j): ?>
                                                <tr>
                                                    <td>
                                                        <div class="fw-bold text-dark"><?= htmlspecialchars($j['nomusu']); ?></div>
                                                        <small class="text-muted"><?= htmlspecialchars($j['ndocusu']); ?></small>
                                                    </td>
                                                    <td>
                                                        <span class="adsc-doc-type-badge">
                                                            <i
                                                                class="fa-solid fa-file-medical me-1"></i><?= htmlspecialchars($j['tipodoc']); ?>
                                                        </span>
                                                        <?php if (!empty($j['obsjust'])): ?>
                                                            <div class="text-muted fs-7 mt-1">
                                                                <small><?= htmlspecialchars($j['obsjust']); ?></small></div>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <div class="fw-semibold"><?= htmlspecialchars($j['fecjust']); ?></div>
                                                        <small class="text-muted">Cargado:
                                                            <?= date('d/m/Y', strtotime($j['fecreg'])); ?></small>
                                                    </td>
                                                    <td class="text-center">
                                                        <a href="<?= htmlspecialchars($j['rutarc']); ?>" target="_blank"
                                                            class="btn btn-outline-primary btn-sm" title="Ver documento adjunto">
                                                            <i class="fa-solid fa-arrow-up-right-from-square me-1"></i>Ver Soporte
                                                        </a>
                                                    </td>
                                                    <td class="text-center">
                                                        <?php if ($j['estado'] === 'Pendiente'): ?>
                                                            <span class="badge-just-pendiente">
                                                                <i class="fa-solid fa-clock me-1"></i>Pendiente
                                                            </span>
                                                        <?php elseif ($j['estado'] === 'Aprobado'): ?>
                                                            <span class="badge-just-aprobada">
                                                                <i class="fa-solid fa-circle-check me-1"></i>Aprobado
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="badge-just-invalida">
                                                                <i class="fa-solid fa-circle-xmark me-1"></i>Inválido / Falso
                                                            </span>
                                                            <?php if ((int) $j['falta_agregada'] === 1): ?>
                                                                <div class="adsc-notice-falta">
                                                                    <i class="fa-solid fa-gavel me-1"></i>Falta aplicada en acta
                                                                </div>
                                                            <?php endif; ?>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-center text-nowrap">
                                                        <button type="button" class="btn btn-outline-secondary btn-sm fw-bold"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#modalRevisarDoc_<?= (int) $j['idjust']; ?>"
                                                            title="Revisar y dictaminar documento">
                                                            <i class="fa-solid fa-gavel me-1"></i>Dictaminar
                                                        </button>
                                                    </td>
                                                </tr>

                                                <!-- Modal de Dictamen del Instructor para este Documento -->
                                                <div class="modal fade" id="modalRevisarDoc_<?= (int) $j['idjust']; ?>" tabindex="-1"
                                                    aria-hidden="true">
                                                    <div class="modal-dialog modal-lg">
                                                        <form method="POST" action="home.php?pg=<?= $pg; ?>&tab=justificaciones">
                                                            <input type="hidden" name="accion" value="revisar_documento">
                                                            <input type="hidden" name="idjust" value="<?= (int) $j['idjust']; ?>">
                                                            <div class="modal-content">
                                                                <div class="modal-header bg-navy text-white">
                                                                    <h5 class="modal-title text-white">
                                                                        <i class="fa-solid fa-shield-halved me-2"></i>Revisión de
                                                                        Soporte #<?= (int) $j['idjust']; ?> - Autoridad del
                                                                        Instructor
                                                                    </h5>
                                                                    <button type="button" class="btn-close btn-close-white"
                                                                        data-bs-dismiss="modal" aria-label="Close"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <div class="row mb-3">
                                                                        <div class="col-md-6">
                                                                            <label
                                                                                class="form-label fw-bold text-muted">Aprendiz:</label>
                                                                            <div class="fw-bold text-dark">
                                                                                <?= htmlspecialchars($j['nomusu']); ?></div>
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                            <label class="form-label fw-bold text-muted">Tipo
                                                                                Declarado:</label>
                                                                            <div class="fw-bold text-primary">
                                                                                <?= htmlspecialchars($j['tipodoc']); ?></div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="row mb-3">
                                                                        <div class="col-md-6">
                                                                            <label class="form-label fw-bold text-muted">Fecha de la
                                                                                Falta:</label>
                                                                            <div><?= htmlspecialchars($j['fecjust']); ?></div>
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                            <label class="form-label fw-bold text-muted">Documento
                                                                                Adjunto:</label>
                                                                            <div>
                                                                                <a href="<?= htmlspecialchars($j['rutarc']); ?>"
                                                                                    target="_blank"
                                                                                    class="btn btn-outline-primary btn-sm">
                                                                                    <i class="fa-solid fa-eye me-1"></i>Abrir y
                                                                                    Examinar Archivo
                                                                                </a>
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                    <hr>

                                                                    <div class="mb-3">
                                                                        <label class="form-label fw-bold">Dictamen del
                                                                            Instructor:</label>
                                                                        <div class="d-flex gap-4">
                                                                            <div class="form-check">
                                                                                <input class="form-check-input" type="radio"
                                                                                    name="decision"
                                                                                    id="dec_apr_<?= (int) $j['idjust']; ?>"
                                                                                    value="aprobar" checked
                                                                                    onchange="toggleFaltaSection(<?= (int) $j['idjust']; ?>, false)">
                                                                                <label class="form-check-label fw-bold text-success"
                                                                                    for="dec_apr_<?= (int) $j['idjust']; ?>">
                                                                                    <i
                                                                                        class="fa-solid fa-circle-check me-1"></i>Aprobar
                                                                                    Justificación
                                                                                </label>
                                                                            </div>
                                                                            <div class="form-check">
                                                                                <input class="form-check-input" type="radio"
                                                                                    name="decision"
                                                                                    id="dec_rec_<?= (int) $j['idjust']; ?>"
                                                                                    value="rechazar"
                                                                                    onchange="toggleFaltaSection(<?= (int) $j['idjust']; ?>, true)">
                                                                                <label class="form-check-label fw-bold text-danger"
                                                                                    for="dec_rec_<?= (int) $j['idjust']; ?>">
                                                                                    <i
                                                                                        class="fa-solid fa-triangle-exclamation me-1"></i>Declarar
                                                                                    Falso / Inválido
                                                                                </label>
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                    <div class="mb-3">
                                                                        <label for="obs_ins_<?= (int) $j['idjust']; ?>"
                                                                            class="form-label fw-bold">Observación del
                                                                            Instructor:</label>
                                                                        <textarea name="obs_instructor"
                                                                            id="obs_ins_<?= (int) $j['idjust']; ?>"
                                                                            class="form-control" rows="3"
                                                                            placeholder="Indique las conclusiones de la verificación médica/legal..."
                                                                            required><?= htmlspecialchars($j['obs_instructor'] ?: ''); ?></textarea>
                                                                    </div>

                                                                    <!-- Sección Condicional para Añadir Falta si es Falso / Inválido -->
                                                                    <div id="secFalta_<?= (int) $j['idjust']; ?>"
                                                                        class="p-3 bg-light border border-danger rounded mb-3 d-none">
                                                                        <div class="form-check mb-2">
                                                                            <input class="form-check-input" type="checkbox"
                                                                                name="agregar_falta"
                                                                                id="chk_falta_<?= (int) $j['idjust']; ?>" value="1"
                                                                                checked>
                                                                            <label class="form-check-label fw-bold text-danger"
                                                                                for="chk_falta_<?= (int) $j['idjust']; ?>">
                                                                                <i class="fa-solid fa-gavel me-1"></i>Añadir falta
                                                                                disciplinaria directamente al acta de deserción
                                                                            </label>
                                                                        </div>
                                                                        <div class="row">
                                                                            <div class="col-md-6 mb-2">
                                                                                <label class="form-label fw-bold">Horas de
                                                                                    Falta:</label>
                                                                                <input type="number" name="horas_falta"
                                                                                    class="form-control" value="6" min="1" max="24">
                                                                            </div>
                                                                            <div class="col-md-6 mb-2">
                                                                                <label class="form-label fw-bold">Fecha de la
                                                                                    Falta:</label>
                                                                                <input type="date" name="fecha_falta"
                                                                                    class="form-control"
                                                                                    value="<?= htmlspecialchars($j['fecjust']); ?>">
                                                                            </div>
                                                                        </div>
                                                                        <small class="text-danger fw-semibold">
                                                                            * Al marcar esta opción, se insertará automáticamente un
                                                                            registro de inasistencia en la base de datos penalizando
                                                                            la justificación fraudulenta.
                                                                        </small>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary"
                                                                        data-bs-dismiss="modal">Cerrar</button>
                                                                    <button type="submit" class="btn btn-primary fw-bold">
                                                                        <i class="fa-solid fa-floppy-disk me-1"></i>Guardar Dictamen
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="6" class="text-center py-4 text-muted">
                                                    No hay documentos de justificación registrados para esta ficha.
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Inicialización de DataTables y Scripts de Interacción -->
    <script>
        // Filtros por pill (filtrado cliente sobre las tarjetas estilo Videos TV)
        var FILTRO_FICHAS = 'todas';
        var FILTRO_APRENDICES = 'todos';

        $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
            var tr = settings.aoData[dataIndex].nTr;
            if (!tr || !settings.nTable) { return true; }
            if (settings.nTable.id === 'tblDashboardFichas') {
                if (FILTRO_FICHAS === 'todas') { return true; }
                return tr.getAttribute('data-cond') === FILTRO_FICHAS;
            }
            if (settings.nTable.id === 'tblAprendicesFicha') {
                if (FILTRO_APRENDICES === 'todos') { return true; }
                return tr.getAttribute('data-cond') === FILTRO_APRENDICES;
            }
            return true;
        });

        function setPillActive(idActivo) {
            document.querySelectorAll('.btn-filter-pill').forEach(function (b) {
                b.classList.remove('active');
            });
            var btn = document.getElementById(idActivo);
            if (btn) { btn.classList.add('active'); }
        }

        function filtrarFichas(v) {
            FILTRO_FICHAS = v;
            setPillActive('fich-' + v);
            var t = $('#tblDashboardFichas').DataTable();
            t.draw();
        }

        function filtrarAprendices(v) {
            FILTRO_APRENDICES = v;
            setPillActive('apr-' + v);
            var t = $('#tblAprendicesFicha').DataTable();
            t.draw();
        }

        // Inicialización inmediata (antes del $(document).ready de valida.js) para que la
        // configuración de las tarjetas no sea reemplazada por la genérica de Video TV.
        (function () {
            if (typeof window.jQuery === 'undefined' || typeof $.fn.DataTable === 'undefined') { return; }
            var idiomaES = { "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json" };
            var botones = [
                { extend: 'copy', className: 'd-none' },
                { extend: 'csv', className: 'd-none' },
                { extend: 'excel', className: 'd-none' }
            ];
            if ($('#tblDashboardFichas').length && !$.fn.DataTable.isDataTable('#tblDashboardFichas')) {
                $('#tblDashboardFichas').DataTable({
                    "language": idiomaES,
                    "pageLength": 12,
                    "dom": "Bfrtip",
                    "buttons": botones,
                    "order": [[0, "desc"]],
                    "autoWidth": false
                });
            }
            if ($('#tblAprendicesFicha').length && !$.fn.DataTable.isDataTable('#tblAprendicesFicha')) {
                $('#tblAprendicesFicha').DataTable({
                    "language": idiomaES,
                    "pageLength": 12,
                    "dom": "Bfrtip",
                    "buttons": botones,
                    "order": [[0, "desc"]],
                    "autoWidth": false
                });
            }
            if ($('#tblJustificaciones').length && !$.fn.DataTable.isDataTable('#tblJustificaciones')) {
                $('#tblJustificaciones').DataTable({
                    "language": idiomaES,
                    "pageLength": 10,
                    "order": [[2, "desc"]]
                });
            }
        })();

        function toggleFaltaSection(idjust, show) {
            var sec = document.getElementById('secFalta_' + idjust);
            if (sec) {
                if (show) {
                    sec.classList.remove('d-none');
                } else {
                    sec.classList.add('d-none');
                }
            }
        }
    </script>