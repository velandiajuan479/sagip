<?php require_once 'controllers/escmin.php'; ?>

<div class="conte minuta-page">
<style>
/* ==========================================================================
   RESPONSIVIDAD Y COMPACIDAD PROPIAS DE LA VISTA MINUTA (esvmin.php).
   Todo el alcance se limita al contenedor .minuta-page (no afecta a otras vistas).
   ========================================================================== */
.minuta-page { overflow-wrap: anywhere; }
.minuta-page .kpi-card { padding: 0.9rem 0.9rem 0 0.9rem; }
.minuta-page .kpi-icon-box { width: 36px; height: 36px; border-radius: 10px; }
.minuta-page .kpi-icon-box i { font-size: 1rem !important; }
.minuta-page .kpi-footer-strip { margin: 0.85rem -0.9rem 0 -0.9rem; padding: 0.5rem 0.85rem; }
.minuta-page .kpi-title-text,
.minuta-page .kpi-footer-text,
.minuta-page .kpi-footer-subtext { font-size: 10.5px; }

/* Rejilla de tarjetas de DataTables más compacta y densa */
.minuta-page .table-vid-cards tbody {
    grid-template-columns: repeat(auto-fill, minmax(255px, 1fr)) !important;
    gap: 1rem !important;
    padding: 0.25rem 0 !important;
}
.minuta-page .table-vid-cards tbody tr td.dataTables_empty { padding: 2rem 1rem !important; }
.minuta-page .vid-preview-box { height: 92px !important; }
.minuta-page .vid-card { border-radius: 12px !important; }
.minuta-page .top5-preview { height: 88px !important; }
.minuta-page .list-group-item { flex-wrap: wrap; column-gap: 0.5rem; }

/* Separación del filtro de consulta respecto al dashboard y a las tarjetas */
.minuta-page .minuta-filtros-card { margin-top: 1.25rem; margin-bottom: 2rem; }

/* Más aire entre títulos y secciones para que nada se vea apretado */
.minuta-page .reporte-mensual { margin-top: 4.5rem !important; }
.minuta-page .reporte-mensual h4,
.minuta-page .reporte-mensual h5 { margin-top: 2rem !important; margin-bottom: 0.9rem; }
.minuta-page .filter-toolbar-container { margin-top: 1.5rem; }
.minuta-page .tab-content { margin-top: 1rem; }
.minuta-page .card-body h6 { margin-bottom: 0.85rem; }

/* Tablets */
@media (max-width: 991.98px) {
    .minuta-page .table-vid-cards tbody {
        grid-template-columns: repeat(auto-fill, minmax(225px, 1fr)) !important;
        gap: 0.9rem !important;
    }
}
/* Móviles */
@media (max-width: 575.98px) {
    .minuta-page .table-vid-cards tbody { grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)) !important; gap: 0.5rem !important; }
    .minuta-page .vid-preview-box { height: 56px !important; }
    .minuta-page .top5-preview { height: 52px !important; }
    .minuta-page .reporte-mensual { padding: 0.75rem !important; }

    /* Tarjetas de minuta compactas en móvil */
    .minuta-page .vid-card { border-radius: 10px !important; }
    .minuta-page .vid-card,
    .minuta-page .vid-card .card-body { padding: 0.55rem !important; }
    .minuta-page .vid-card .card-footer { padding: 0.4rem 0.55rem !important; }
    .minuta-page .vid-card .card-footer hr { display: none !important; }
    .minuta-page .vid-card h6 { font-size: 0.76rem !important; margin-bottom: 0.15rem !important; }
    .minuta-page .vid-card .vid-play-btn { width: 34px !important; height: 34px !important; font-size: 0.85rem !important; padding-left: 2px !important; }
    .minuta-page .vid-card .vid-badges-top,
    .minuta-page .vid-card .vid-badges-bottom { gap: 4px !important; }
    .minuta-page .vid-card .vid-badge-blur,
    .minuta-page .vid-card .vid-badges-top .badge,
    .minuta-page .vid-card .vid-badges-bottom .badge { font-size: 0.58rem !important; padding: 0.15rem 0.35rem !important; }
    .minuta-page .vid-card .card-body .badge { font-size: 0.6rem !important; padding: 0.1rem 0.3rem !important; }

    /* Caja de Entrada/Salida apilada y compacta */
    .minuta-page .vid-card .card-body .bg-light.d-flex { flex-direction: column !important; align-items: stretch !important; font-size: 0.66rem !important; gap: 2px !important; }
    .minuta-page .vid-card .card-body .bg-light.d-flex .fa-arrow-right-long { transform: rotate(90deg); margin: 0 !important; }
    .minuta-page .vid-card .card-body .bg-light.d-flex [style*="0.68rem"] { font-size: 0.6rem !important; }

    /* Ocultar observaciones en móvil para aligerar la tarjeta */
    .minuta-page .vid-card .card-body [style*="0.74rem"] { display: none !important; }
    .minuta-page .vid-card .card-footer small { font-size: 0.64rem !important; }
}
</style>
<?php 
if (function_exists('titulo2')) {
    echo titulo2("<i class='fa " . (isset($icono) ? $icono : 'fa-list') . "'></i> Minuta", 0);
}
?>

<!-- MÓDULO PRINCIPAL SUPERIOR: DASHBOARD DE CONTROL OPERATIVO - MINUTA (SEGÚN LA JORNADA) -->
<div class="row my-4 g-3 row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-xl-5">
    <!-- 1. APRENDICES EN JORNADA -->
    <div class="col">
        <div class="kpi-card h-100 d-flex flex-column justify-content-between">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #006b29;">Aprendices en Jornada</span>
                    <div class="d-flex align-items-baseline gap-2 mt-2">
                        <span class="fw-bold" style="font-size: clamp(21px, 3.6vw, 32px); line-height: 1; color: #006b29;"><?= htmlspecialchars((string)$regAprendices); ?></span>
                        <span style="font-size: 13px; color: rgba(0, 107, 41, 0.8);">/ <?= htmlspecialchars((string)$totAprendices); ?> aprendices</span>
                    </div>
                </div>
                <div class="kpi-icon-box" style="background-color: #7afd88; color: #006b24;">
                    <i class="fa-solid fa-graduation-cap fs-4"></i>
                </div>
            </div>
            <div class="kpi-footer-strip">
                <span class="fw-semibold d-inline-flex align-items-center gap-2" style="font-size: 11px; color: #006b29;">
                    <span class="pulse-dot"></span> Jornada <?= htmlspecialchars((string)$nomJornadaActual); ?>
                </span>
                <span style="font-size: 11px; color: #3e4a3d;">Ingresaron: <?= htmlspecialchars((string)$regAprendices); ?> de <?= htmlspecialchars((string)$totAprendices); ?></span>
            </div>
        </div>
    </div>

    <!-- 2. FICHAS REGISTRADAS -->
    <div class="col">
        <div class="kpi-card h-100 d-flex flex-column justify-content-between">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #b45309;">Fichas en Jornada</span>
                    <div class="d-flex align-items-baseline gap-2 mt-2">
                        <span class="fw-bold" style="font-size: clamp(21px, 3.6vw, 32px); line-height: 1; color: #0b1c30;"><?= htmlspecialchars((string)$regFichas); ?></span>
                        <span style="font-size: 13px; color: #3e4a3d;">/ <?= htmlspecialchars((string)$totFichas); ?> fichas</span>
                    </div>
                </div>
                <div class="kpi-icon-box" style="background-color: #fde68a; color: #b45309;">
                    <i class="fa-solid fa-layer-group fs-4"></i>
                </div>
            </div>
            <div class="kpi-footer-strip">
                <span class="fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 11px; color: #b45309;">
                    <i class="fa-solid fa-chart-simple" style="font-size: 12px;"></i> Fichas Activas
                </span>
                <span style="font-size: 11px; color: #3e4a3d;">Jornada <?= htmlspecialchars((string)$nomJornadaActual); ?></span>
            </div>
        </div>
    </div>

    <!-- 3. ENTRADAS REGISTRADAS -->
    <div class="col">
        <div class="kpi-card h-100 d-flex flex-column justify-content-between">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #3e4a3d;">Entradas Registradas</span>
                    <div class="d-flex align-items-baseline gap-2 mt-2">
                        <span class="fw-bold" style="font-size: clamp(21px, 3.6vw, 32px); line-height: 1; color: #0b1c30;"><?= htmlspecialchars((string)$regEntradas); ?></span>
                        <span style="font-size: 13px; color: #3e4a3d;">ingresos</span>
                    </div>
                </div>
                <div class="kpi-icon-box" style="background-color: #b5dcfe; color: #3c627f;">
                    <i class="fa-solid fa-arrow-right-to-bracket fs-4"></i>
                </div>
            </div>
            <div class="kpi-footer-strip">
                <span class="fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 11px; color: #3c627f;">
                    <i class="fa-solid fa-arrow-trend-up" style="font-size: 12px;"></i> Jornada <?= htmlspecialchars((string)$nomJornadaActual); ?>
                </span>
                <span style="font-size: 11px; color: #3e4a3d;">Registrados</span>
            </div>
        </div>
    </div>

    <!-- 4. SALIDAS REGISTRADAS -->
    <div class="col">
        <div class="kpi-card h-100 d-flex flex-column justify-content-between">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #ba1a1a;">Salidas Registradas</span>
                    <div class="d-flex align-items-baseline gap-2 mt-2">
                        <span class="fw-bold" style="font-size: clamp(21px, 3.6vw, 32px); line-height: 1; color: #0b1c30;"><?= htmlspecialchars((string)$regSalidas); ?></span>
                        <span style="font-size: 13px; color: #3e4a3d;">salidas</span>
                    </div>
                </div>
                <div class="kpi-icon-box" style="background-color: #ffdad6; color: #ba1a1a;">
                    <i class="fa-solid fa-arrow-right-from-bracket fs-4"></i>
                </div>
            </div>
            <div class="kpi-footer-strip">
                <span class="d-inline-flex align-items-center gap-1" style="font-size: 11px; color: #3e4a3d;">
                    <i class="fa-solid fa-clock-rotate-left" style="font-size: 12px;"></i> Jornada <?= htmlspecialchars((string)$nomJornadaActual); ?>
                </span>
                <span class="fw-semibold" style="font-size: 11px; color: #3c627f;">Completadas</span>
            </div>
        </div>
    </div>

    <!-- 5. ELEMENTOS REGISTRADOS -->
    <div class="col">
        <div class="kpi-card h-100 d-flex flex-column justify-content-between">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #4f46e5;">Elementos Registrados</span>
                    <div class="d-flex align-items-baseline gap-2 mt-2">
                        <span class="fw-bold" style="font-size: clamp(21px, 3.6vw, 32px); line-height: 1; color: #0b1c30;"><?= htmlspecialchars((string)$regElementos); ?></span>
                        <span style="font-size: 13px; color: #3e4a3d;">/ <?= htmlspecialchars((string)$totElementos); ?> equipos</span>
                    </div>
                </div>
                <div class="kpi-icon-box" style="background-color: #e0e7ff; color: #4f46e5;">
                    <i class="fa-solid fa-laptop fs-4"></i>
                </div>
            </div>
            <div class="kpi-footer-strip">
                <span class="d-inline-flex align-items-center gap-1" style="font-size: 11px; color: #4f46e5;">
                    <i class="fa-solid fa-boxes-stacked" style="font-size: 12px;"></i> Control equipos
                </span>
                <span class="fw-semibold" style="font-size: 11px; color: #4f46e5;">Inventario OK</span>
            </div>
        </div>
    </div>
</div>

<!-- FILTROS DE CONSULTA DE MINUTA -->
<form action="home.php?pg=1404" method="post" class="minuta-filtros-card">
    <div class="row minuta-filtros g-3 align-items-end">
        <div class="form-group col-12 col-sm-6 col-md-2">
            <label for="fechos_input"><i class="fa-regular fa-calendar-days"></i> Fecha inicial</label>
            <input type="date" name="fechos" id="fechos_input" value="<?= htmlspecialchars((string)$fechos); ?>" max="<?= htmlspecialchars((string)$fecha); ?>" onchange="document.getElementById('fhlle_input').value=this.value; this.form.submit();" class="form-control" required>
        </div>
        <div class="form-group col-12 col-sm-6 col-md-2">
            <label for="fhlle_input"><i class="fa-regular fa-calendar-check"></i> Fecha final</label>
            <input type="date" name="fhlle" id="fhlle_input" value="<?= htmlspecialchars((string)$fhlle); ?>" max="<?= htmlspecialchars((string)$fecha); ?>" onchange="this.form.submit();" class="form-control">
        </div>
        <div class="form-group col-12 col-sm-6 col-md-2">
            <label for="ndocusu"><i class="fa-solid fa-id-card"></i> Documento Usuario</label>
            <input type="text" name="ndocusu" id="ndocusu" value="<?= htmlspecialchars((string)$ndocusu); ?>" placeholder="Ingrese N° documento..." onkeydown="return enter(event);" onchange="this.form.submit();" onkeypress="return solonum(event);" class="form-control">
        </div>
        <div class="form-group col-12 col-sm-6 col-md-2">
            <label for="idjornada_select"><i class="fa-regular fa-clock"></i> Jornada Dashboard</label>
            <select name="idjornada" id="idjornada_select" class="form-select" onchange="this.form.submit();">
                <option value="" <?= empty($idJornadaSel) ? 'selected' : ''; ?>>Auto (Actual: <?= htmlspecialchars((string)$nomJornadaAuto); ?>)</option>
                <option value="1" <?= ($idJornadaSel === 1) ? 'selected' : ''; ?>>Mañana</option>
                <option value="2" <?= ($idJornadaSel === 2) ? 'selected' : ''; ?>>Tarde</option>
                <option value="3" <?= ($idJornadaSel === 3) ? 'selected' : ''; ?>>Noche</option>
            </select>
        </div>
        <div class="form-group col-12 col-sm-6 col-md d-flex gap-2 align-items-center">
            <a href="views/esrrmin.php?tipo=minuta&fechos=<?= urlencode((string)$fechos) ?>&fhlle=<?= urlencode((string)$fhlle) ?>&ndocusu=<?= urlencode((string)$ndocusu) ?>" title="Imprimir" target="_blank" aria-label="Imprimir" class="btn-action-icon">
                <i class="fa-solid fa-print"></i>
            </a>
            <a href="views/esrrmin.php?tipo=minuta&fechos=<?= urlencode((string)$fechos) ?>&fhlle=<?= urlencode((string)$fhlle) ?>&ndocusu=<?= urlencode((string)$ndocusu) ?>&pdf=ok" title="PDF" target="_blank" aria-label="Generar PDF" class="btn-action-icon">
                <i class="fa-solid fa-file-pdf"></i>
            </a>
        </div>
    </div>
</form>

<?php
// FUNCIÓN RENDER DE TARJETA DE MINUTA (DISEÑO IDÉNTICO A VVIDGEN CON CARDS)
$renderMinutaCard = function ($d) use ($esmmin) {
    $esDentro = (($d['tipmin'] ?? '') === "I" && empty($d['fhlle']));
    $esAprendiz = (stripos($d['nomper'] ?? '', 'Aprendiz') !== false);
    ob_start();
    ?>
    <div class="card h-100 shadow-sm border-0 vid-card">
        <!-- Banner superior estilo Preview Box de vvidgen -->
        <div class="vid-preview-box" style="background: linear-gradient(135deg, #0b1c30 0%, #006b29 100%); height: 92px;">
            <!-- Badges Superiores -->
            <div class="vid-badges-top">
                <?php if ($esDentro): ?>
                    <span class="badge bg-success shadow-sm px-2 py-1">
                        <i class="fa-solid fa-arrow-right-to-bracket me-1"></i> En Centro
                    </span>
                <?php else: ?>
                    <span class="badge bg-danger shadow-sm px-2 py-1">
                        <i class="fa-solid fa-arrow-right-from-bracket me-1"></i> Salió
                    </span>
                <?php endif; ?>
                <span class="badge vid-badge-blur px-2 py-1 shadow-sm">
                    <i class="fa-solid fa-id-badge me-1 text-info"></i> <?= htmlspecialchars((string)($d['nomper'] ?? 'Sin perfil')); ?>
                </span>
            </div>

            <!-- Ícono central institucional -->
            <div class="vid-play-btn" style="pointer-events: none; background: rgba(255, 255, 255, 0.95); color: #006b29;" title="Registro de Minuta">
                <i class="fa-solid <?= $esAprendiz ? 'fa-graduation-cap' : 'fa-user'; ?>"></i>
            </div>

            <!-- Badges Inferiores -->
            <div class="vid-badges-bottom">
                <span class="badge vid-badge-blur px-2 py-1">
                    <i class="fa-solid fa-hashtag me-1 text-warning"></i> Minuta #<?= htmlspecialchars((string)($d['nummin'] ?? '')); ?>
                </span>
                <?php if (!empty($d['tiempo'])): ?>
                    <span class="badge vid-badge-blur px-2 py-1">
                        <i class="fa-regular fa-clock me-1 text-info"></i> <?= htmlspecialchars((string)$d['tiempo']); ?>
                    </span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Cuerpo de la Tarjeta -->
        <div class="card-body p-2 d-flex flex-column justify-content-between">
            <div>
                <!-- Nombre y documento -->
                <h6 class="fw-bold text-dark mb-1 text-truncate" title="<?= htmlspecialchars((string)($d['nomusu'] ?? '')); ?>" style="font-size: 0.88rem;">
                    <span class="text-success">#<?= htmlspecialchars((string)($d['ndocusu'] ?? '')); ?></span> - <?= htmlspecialchars((string)($d['nomusu'] ?? '')); ?>
                </h6>

                <!-- Información de Ficha y Jornada (si aplica) -->
                <?php if (!empty($d['idfic'])): ?>
                <div class="text-muted small mb-1 text-truncate" title="<?= htmlspecialchars((string)$d['nomfic']); ?>">
                    <i class="fa-solid fa-layer-group me-1 text-primary"></i><strong>Ficha:</strong> <?= htmlspecialchars((string)$d['idfic']); ?> - <?= htmlspecialchars((string)$d['nomfic']); ?>
                    <?php if (!empty($d['nomval'])): ?>
                        <span class="badge bg-light text-dark border ms-1"><?= htmlspecialchars((string)$d['nomval']); ?></span>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <!-- Caja de Horas de Entrada y Salida (Mismo diseño de fechas de vigencia en vvidgen) -->
                <div class="p-1 mb-1 rounded-3 bg-light border border-light-subtle d-flex justify-content-between align-items-center" style="font-size: 0.76rem;">
                    <div>
                        <span class="text-muted d-block" style="font-size: 0.68rem;">Entrada</span>
                        <span class="fw-bold text-success">
                            <i class="fa-regular fa-calendar-check me-1"></i><?= htmlspecialchars((string)($d['fechos'] ?? '')); ?>
                        </span>
                    </div>
                    <div class="text-muted"><i class="fa-solid fa-arrow-right-long mx-1"></i></div>
                    <div class="text-end">
                        <span class="text-muted d-block" style="font-size: 0.68rem;">Salida</span>
                        <span class="fw-bold <?= !empty($d['fhlle']) ? 'text-danger' : 'text-secondary'; ?>">
                            <i class="fa-regular fa-calendar-xmark me-1"></i><?= !empty($d['fhlle']) ? htmlspecialchars((string)$d['fhlle']) : 'En curso'; ?>
                        </span>
                    </div>
                </div>

                <!-- Elementos traídos -->
                <?php
                if (!empty($d['ideles'])) {
                    $eles = explode(";", $d['ideles']);
                    if ($eles) {
                        echo "<div class='mb-2'>";
                        foreach ($eles as $ele) {
                            $ele = trim($ele);
                            if ($ele !== '') {
                                $dtela = $esmmin->selOneEle($ele);
                                if (!empty($dtela) && isset($dtela[0])) {
                                    echo "<span class='badge bg-primary bg-opacity-10 text-primary border border-primary-subtle me-1 mb-1 p-1 small'><i class='fa-solid fa-laptop me-1'></i>" . htmlspecialchars((string)$dtela[0]['nomele']) . " <small class='text-muted'>(" . htmlspecialchars((string)$dtela[0]['nidele']) . ")</small></span>";
                                }
                            }
                        }
                        echo "</div>";
                    }
                }
                ?>

                <!-- Observaciones -->
                <?php if (!empty($d['obs'])): ?>
                <div class="text-muted small mt-1 text-truncate" style="font-size: 0.74rem;" title="<?= htmlspecialchars((string)$d['obs']); ?>">
                    <i class="fa-regular fa-comment-dots text-secondary me-1"></i> <strong>Obs:</strong> <?= htmlspecialchars((string)$d['obs']); ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Footer de la Tarjeta con Botones / Estado -->
        <div class="card-footer bg-white border-0 pt-0 pb-2 px-2">
            <hr class="mt-0 mb-2 text-muted opacity-25">
            <div class="d-flex justify-content-between align-items-center">
                <small class="text-muted" style="font-size: 0.72rem;">
                    <i class="fa-regular fa-clock me-1"></i> <?= !empty($d['tiempo']) ? 'Tiempo: ' . htmlspecialchars((string)$d['tiempo']) : ($esDentro ? 'Actualmente en centro' : 'Sin salida registrada'); ?>
                </small>
                <div class="d-flex gap-1">
                    <a href="views/esrrmin.php?tipo=minuta&ndocusu=<?= urlencode((string)$d['ndocusu']); ?>&pdf=ok" target="_blank" class="btn btn-sm btn-outline-danger" title="Exportar PDF de usuario">
                        <i class="fa-solid fa-file-pdf"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
};
?>

<!-- BARRA DE FILTROS, EXPORTACIÓN Y BÚSQUEDA (DISEÑO TOOLBAR DE VVIDGEN) -->
<div class="filter-toolbar-container">
    <!-- Izquierda: Píldoras de Filtro de Estado y Exportación -->
    <div class="toolbar-left-group">
        <div class="filter-pills-group" id="tabMinuta" role="tablist">
            <!-- Píldora En Centro (Activa por defecto) -->
            <button class="btn-filter-pill active" id="dentro-tab" data-bs-toggle="tab" data-bs-target="#dentro" type="button" role="tab" aria-controls="dentro" aria-selected="true" onclick="filterByTab('dentro')">
                <span class="pill-dot" style="background-color: #006b29;"></span>
                <span>En Centro</span>
                <span class="pill-count"><?= count($datDentro ?? []); ?></span>
            </button>

            <!-- Píldora Salieron -->
            <button class="btn-filter-pill" id="salieron-tab" data-bs-toggle="tab" data-bs-target="#salieron" type="button" role="tab" aria-controls="salieron" aria-selected="false" onclick="filterByTab('salieron')">
                <span class="pill-dot" style="background-color: #ba1a1a;"></span>
                <span>Salieron</span>
                <span class="pill-count"><?= count($datSalieron ?? []); ?></span>
            </button>

            <!-- Píldora Todos -->
            <button class="btn-filter-pill" id="todos-tab" data-bs-toggle="tab" data-bs-target="#todos" type="button" role="tab" aria-controls="todos" aria-selected="false" onclick="filterByTab('todos')">
                <span class="pill-dot" style="background-color: #3c627f;"></span>
                <span>Todos</span>
                <span class="pill-count"><?= count($dat ?? []); ?></span>
            </button>
        </div>

        <!-- Separador -->
        <div class="toolbar-divider d-none d-sm-block"></div>

        <!-- Botones de Exportación integrados de vvidgen -->
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

    <!-- Derecha: Búsqueda, Ordenamiento y Vista Grid/Lista de vvidgen -->
    <div class="toolbar-search-sort-group">
        <!-- Input de búsqueda con id customVideoSearch para sincronización automática -->
        <div class="toolbar-search-box">
            <i class="fa-solid fa-magnifying-glass icoldiz"></i>
            <input type="text" id="customVideoSearch" placeholder="Buscar en minuta..." onkeyup="syncCustomSearch(this.value)">
        </div>

        <!-- Controles Secundarios: Selector de Orden y Conmutador de Vistas -->
        <div class="toolbar-secondary-controls">
            <!-- Selector de ordenamiento -->
            <select class="toolbar-select-sort" id="customVideoSort" onchange="syncCustomSort(this.value)">
                <option value="desc">Más reciente</option>
                <option value="asc">Más antiguo</option>
            </select>

            <!-- Conmutador de cuadrícula / lista de vvidgen -->
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

<!-- CONTENEDOR DE PESTAÑAS CON CARDS (ESTRUCTURA DE VVIDGEN CON DATA-TABLES) -->
<div class="tab-content" id="tabMinutaContent">
    <!-- Pestaña 1: En Centro (Activa por defecto) -->
    <div class="tab-pane fade show active" id="dentro" role="tabpanel" aria-labelledby="dentro-tab">
        <table id="example" class="table table-striped table-vid-cards" style="width:100%">
            <thead>
                <tr>
                    <th>En Centro</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($datDentro)) { foreach ($datDentro as $d) { ?>
                <tr data-order="<?= htmlspecialchars((string)($d['nummin'] ?? '0')); ?>">
                    <td data-search="<?= htmlspecialchars((string)($d['nummin'] ?? '')); ?> <?= htmlspecialchars((string)($d['ndocusu'] ?? '')); ?> <?= htmlspecialchars((string)($d['nomusu'] ?? '')); ?> <?= htmlspecialchars((string)($d['nomper'] ?? '')); ?> <?= htmlspecialchars((string)($d['idfic'] ?? '')); ?> <?= htmlspecialchars((string)($d['nomfic'] ?? '')); ?> <?= htmlspecialchars((string)($d['nomval'] ?? '')); ?> <?= htmlspecialchars((string)($d['fechos'] ?? '')); ?> <?= htmlspecialchars((string)($d['obs'] ?? '')); ?>">
                        <?= $renderMinutaCard($d); ?>
                    </td>
                </tr>
                <?php } } else { ?>
                <tr>
                    <td class="text-muted text-center py-3">No hay usuarios actualmente en el centro para el período seleccionado.</td>
                </tr>
                <?php } ?>
            </tbody>
            <tfoot>
                <tr>
                    <th>En Centro</th>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Pestaña 2: Salieron -->
    <div class="tab-pane fade" id="salieron" role="tabpanel" aria-labelledby="salieron-tab">
        <table id="myt" class="table table-striped table-vid-cards" style="width:100%">
            <thead>
                <tr>
                    <th>Salieron</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($datSalieron)) { foreach ($datSalieron as $d) { ?>
                <tr data-order="<?= htmlspecialchars((string)($d['nummin'] ?? '0')); ?>">
                    <td data-search="<?= htmlspecialchars((string)($d['nummin'] ?? '')); ?> <?= htmlspecialchars((string)($d['ndocusu'] ?? '')); ?> <?= htmlspecialchars((string)($d['nomusu'] ?? '')); ?> <?= htmlspecialchars((string)($d['nomper'] ?? '')); ?> <?= htmlspecialchars((string)($d['idfic'] ?? '')); ?> <?= htmlspecialchars((string)($d['nomfic'] ?? '')); ?> <?= htmlspecialchars((string)($d['nomval'] ?? '')); ?> <?= htmlspecialchars((string)($d['fechos'] ?? '')); ?> <?= htmlspecialchars((string)($d['fhlle'] ?? '')); ?> <?= htmlspecialchars((string)($d['obs'] ?? '')); ?>">
                        <?= $renderMinutaCard($d); ?>
                    </td>
                </tr>
                <?php } } else { ?>
                <tr>
                    <td class="text-muted text-center py-3">No hay registros de salidas en este período.</td>
                </tr>
                <?php } ?>
            </tbody>
            <tfoot>
                <tr>
                    <th>Salieron</th>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Pestaña 3: Todos -->
    <div class="tab-pane fade" id="todos" role="tabpanel" aria-labelledby="todos-tab">
        <table id="tblTodos" class="table table-striped table-vid-cards" style="width:100%">
            <thead>
                <tr>
                    <th>Todos los Registros</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($dat)) { foreach ($dat as $d) { ?>
                <tr data-order="<?= htmlspecialchars((string)($d['nummin'] ?? '0')); ?>">
                    <td data-search="<?= htmlspecialchars((string)($d['nummin'] ?? '')); ?> <?= htmlspecialchars((string)($d['ndocusu'] ?? '')); ?> <?= htmlspecialchars((string)($d['nomusu'] ?? '')); ?> <?= htmlspecialchars((string)($d['nomper'] ?? '')); ?> <?= htmlspecialchars((string)($d['idfic'] ?? '')); ?> <?= htmlspecialchars((string)($d['nomfic'] ?? '')); ?> <?= htmlspecialchars((string)($d['nomval'] ?? '')); ?> <?= htmlspecialchars((string)($d['fechos'] ?? '')); ?> <?= htmlspecialchars((string)($d['fhlle'] ?? '')); ?> <?= htmlspecialchars((string)($d['obs'] ?? '')); ?>">
                        <?= $renderMinutaCard($d); ?>
                    </td>
                </tr>
                <?php } } else { ?>
                <tr>
                    <td class="text-muted text-center py-3">No hay registros de minuta para el criterio seleccionado.</td>
                </tr>
                <?php } ?>
            </tbody>
            <tfoot>
                <tr>
                    <th>Todos los Registros</th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<!-- ==========================================================================
     SECCIÓN 2: REPORTE MENSUAL DETALLADO (SOLO APRENDICES)
     ========================================================================== -->
<div class="reporte-mensual mt-5" id="reporteMensual">
    <h4><i class="fa-solid fa-chart-line text-success"></i> Reporte Mensual Detallado</h4>
    <p class="text-muted" style="font-size: 0.85rem;">Estadísticas mensuales de asistencia de todos los estudiantes registrados del centro.</p>

    <!-- Formulario de filtro de mes y año con botones de exportación -->
    <form action="home.php?pg=1404" method="post" class="mb-4">
        <div class="row g-3 align-items-end">
            <div class="form-group col-12 col-sm-6 col-md-3">
                <label for="mes_select"><i class="fa-regular fa-calendar me-1"></i> Mes</label>
                <select name="mes" id="mes_select" class="form-select" onchange="this.form.submit()">
                    <?php foreach ($nomMeses as $num => $nom): ?>
                        <option value="<?= $num ?>" <?= ($num == $mes) ? 'selected' : '' ?>><?= $nom ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group col-12 col-sm-6 col-md-3">
                <label for="anio_input"><i class="fa-solid fa-calendar-days me-1"></i> Año</label>
                <input type="number" name="anio" id="anio_input" value="<?= $anio ?>" min="2000" max="<?= $anioActual ?>" class="form-control" onchange="this.form.submit()">
            </div>
            
            <!-- Acciones Reporte Mensual (Imprimir y PDF) -->
            <div class="form-group col-12 col-md-3 d-flex gap-2 align-items-center">
                <a href="views/esrrmin.php?tipo=mensual&mes=<?= $mes ?>&anio=<?= $anio ?>" title="Imprimir Reporte Mensual" target="_blank" class="btn-action-icon">
                    <i class="fa-solid fa-print"></i>
                </a>
                <a href="views/esrrmin.php?tipo=mensual&mes=<?= $mes ?>&anio=<?= $anio ?>&pdf=ok" title="PDF Reporte Mensual" target="_blank" class="btn-action-icon">
                    <i class="fa-solid fa-file-pdf"></i>
                </a>
            </div>
        </div>
    </form>

    <!-- CARDS PARA PRESENTAR NUEVA INFORMACIÓN (TAL CUAL COMO ESTÁ EN EL DASHBOARD) -->
    <div class="row my-4 g-3 row-cols-1 row-cols-sm-2 row-cols-xl-4">
        <!-- 1. Total Aprendices -->
        <div class="col">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #0b1c30;">Total Aprendices</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold" style="font-size: clamp(21px, 3.6vw, 32px); line-height: 1; color: #0b1c30;"><?= htmlspecialchars((string)$totAprend); ?></span>
                            <span style="font-size: 13px; color: #3e4a3d;">matriculados</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box" style="background-color: #b5dcfe; color: #3c627f;">
                        <i class="fa-solid fa-users fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 11px; color: #3c627f;">
                        <i class="fa-solid fa-graduation-cap" style="font-size: 12px;"></i> Todos los aprendices registrados
                    </span>
                    <span style="font-size: 11px; color: #3e4a3d;"><?= htmlspecialchars((string)$nomMesSel); ?></span>
                </div>
            </div>
        </div>

        <!-- 2. Promedio Asisten por Día -->
        <div class="col">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #006b29;">Prom. Asisten / Día</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold" style="font-size: clamp(21px, 3.6vw, 32px); line-height: 1; color: #006b29;"><?= htmlspecialchars((string)$promAsist); ?></span>
                            <span style="font-size: 13px; color: rgba(0, 107, 41, 0.8);">asistiendo</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box" style="background-color: #7afd88; color: #006b24;">
                        <i class="fa-solid fa-user-check fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="fw-semibold d-inline-flex align-items-center gap-2" style="font-size: 11px; color: #006b29;">
                        <span class="pulse-dot"></span> <?= htmlspecialchars((string)$pctAsist); ?>% asistencia
                    </span>
                    <span style="font-size: 11px; color: #3e4a3d;">promedio diario</span>
                </div>
            </div>
        </div>

        <!-- 3. Promedio Faltan por Día -->
        <div class="col">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #ba1a1a;">Prom. Faltan / Día</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold" style="font-size: clamp(21px, 3.6vw, 32px); line-height: 1; color: #ba1a1a;"><?= htmlspecialchars((string)$promFalt); ?></span>
                            <span style="font-size: 13px; color: #3e4a3d;">hacen falta</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box" style="background-color: #ffdad6; color: #ba1a1a;">
                        <i class="fa-solid fa-user-xmark fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="d-inline-flex align-items-center gap-1" style="font-size: 11px; color: #ba1a1a;">
                        <i class="fa-solid fa-triangle-exclamation" style="font-size: 12px;"></i> <?= htmlspecialchars((string)$pctFalt); ?>% faltan
                    </span>
                    <span class="fw-semibold" style="font-size: 11px; color: #ba1a1a;">promedio diario</span>
                </div>
            </div>
        </div>

        <!-- 4. Días Hábiles con Actividad -->
        <div class="col">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #4f46e5;">Días con Actividad</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold" style="font-size: clamp(21px, 3.6vw, 32px); line-height: 1; color: #0b1c30;"><?= htmlspecialchars((string)$diasActivos); ?></span>
                            <span style="font-size: 13px; color: #3e4a3d;">días evaluados</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box" style="background-color: #e0e7ff; color: #4f46e5;">
                        <i class="fa-solid fa-calendar-day fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="d-inline-flex align-items-center gap-1" style="font-size: 11px; color: #4f46e5;">
                        <i class="fa-solid fa-clock" style="font-size: 12px;"></i> Lunes a viernes
                    </span>
                    <span class="fw-semibold" style="font-size: 11px; color: #4f46e5;">Registros de aprendices</span>
                </div>
            </div>
        </div>
    </div>

    <!-- GRÁFICA REDONDA + RESULTADOS ORGANIZADOS AL FRENTE (SOLO APRENDICES) -->
    <div class="row g-4 mt-0 mb-4">
        <!-- Gráfica Redonda (Doughnut) -->
        <div class="col-12 col-xl-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex flex-column align-items-stretch">
                    <h6 class="fw-bold text-dark mb-1"><i class="fa-solid fa-chart-pie text-success me-1"></i> Asistencia Promedio Mensual</h6>
                    <p class="text-muted mb-3" style="font-size: 0.78rem;">Distribución del promedio de aprendices que asisten vs. los que hacen falta.</p>
                    <div style="height: clamp(210px, 42vw, 300px); position: relative;">
                        <canvas id="chartAsistenciaMes"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Resultados bien organizados al frente -->
        <div class="col-12 col-xl-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3 p-md-4 d-flex flex-column justify-content-between">
                    <div>
                        <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-list-check text-success me-1"></i> Balance del Mes (<?= htmlspecialchars((string)$nomMesSel); ?> <?= htmlspecialchars((string)$anio); ?>)</h6>
                        <ul class="list-group list-group-flush mb-3">
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                                <span class="text-muted"><i class="fa-solid fa-users text-primary me-2"></i>Total de estudiantes registrados:</span>
                                <strong class="fs-6"><?= htmlspecialchars((string)$totAprend); ?> aprendices</strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                                <span class="text-muted"><i class="fa-solid fa-user-check text-success me-2"></i>Asisten en promedio por día:</span>
                                <strong class="fs-6 text-success"><?= htmlspecialchars((string)$promAsist); ?> aprendices (<?= htmlspecialchars((string)$pctAsist); ?>%)</strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                                <span class="text-muted"><i class="fa-solid fa-user-xmark text-danger me-2"></i>Hacen falta en promedio por día:</span>
                                <strong class="fs-6 text-danger"><?= htmlspecialchars((string)$promFalt); ?> aprendices (<?= htmlspecialchars((string)$pctFalt); ?>%)</strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                                <span class="text-muted"><i class="fa-solid fa-calendar-day text-info me-2"></i>Días hábiles con actividad:</span>
                                <strong class="fs-6"><?= htmlspecialchars((string)$diasActivos); ?> días</strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                                <span class="text-muted"><i class="fa-solid fa-chart-simple text-success me-2"></i>Tasa de cumplimiento promedio:</span>
                                <strong class="fs-6 text-success"><?= htmlspecialchars((string)$pctAsist); ?>%</strong>
                            </li>
                        </ul>
                    </div>

                    <!-- Barra de progreso visual comparativa -->
                    <div>
                        <div class="d-flex justify-content-between mb-1" style="font-size: 0.78rem;">
                            <span class="text-success fw-bold"><i class="fa-solid fa-check me-1"></i> Asistencia (<?= $pctAsist; ?>%)</span>
                            <span class="text-danger fw-bold"><i class="fa-solid fa-xmark me-1"></i> Inasistencia (<?= $pctFalt; ?>%)</span>
                        </div>
                        <div class="progress" style="height: 10px; border-radius: 6px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: <?= $pctAsist; ?>%;" aria-valuenow="<?= $pctAsist; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                            <div class="progress-bar bg-danger" role="progressbar" style="width: <?= $pctFalt; ?>%;" aria-valuenow="<?= $pctFalt; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- TOP 5 APRENDICES QUE MÁS FALTAN (EN CARDS COMO SALE EN LA VISTA DE VVIDGEN) -->
    <h5 class="mt-4"><i class="fa-solid fa-triangle-exclamation text-danger me-2"></i> Top 5 - Aprendices que más faltan (<?= htmlspecialchars((string)$nomMesSel); ?> <?= htmlspecialchars((string)$anio); ?>)</h5>
    <p class="text-muted mb-4" style="font-size: 0.8rem;">Los 5 aprendices con el mayor número de inasistencias en días activos durante el mes.</p>

    <div class="row g-3 mb-4">
        <?php 
        if (!empty($topFaltas)) { 
            $pos = 1;
            foreach ($topFaltas as $tf) { 
                $inasistenciaPct = max(0, 100 - (int)$tf['pct']);
        ?>
        <div class="col-12 col-sm-6 col-md-4 col-xl">
            <!-- Card con diseño de vvidgen -->
            <div class="card h-100 shadow-sm border-0 vid-card top5-card">
                <!-- Preview Box / Cabecera destacada estilo vvidgen -->
                <div class="vid-preview-box top5-preview" style="background: linear-gradient(135deg, #450a0a 0%, #ba1a1a 100%); height: 88px;">
                    <!-- Badges superiores -->
                    <div class="vid-badges-top">
                        <span class="badge bg-danger shadow-sm px-2 py-1">
                            <i class="fa-solid fa-trophy me-1"></i> Top #<?= $pos; ?>
                        </span>
                        <span class="badge vid-badge-blur px-2 py-1 shadow-sm">
                            <i class="fa-solid fa-circle-xmark me-1 text-danger"></i> <?= (int)$tf['faltas']; ?> faltas
                        </span>
                    </div>

                    <!-- Ícono central de aprendiz -->
                    <div class="vid-play-btn" style="pointer-events: none; background: rgba(255, 255, 255, 0.95); color: #ba1a1a;" title="Inasistencias">
                        <i class="fa-solid fa-user-xmark"></i>
                    </div>

                    <!-- Badges inferiores -->
                    <div class="vid-badges-bottom">
                        <span class="badge vid-badge-blur px-2 py-1">
                            <i class="fa-solid fa-percent me-1 text-warning"></i> <?= $inasistenciaPct; ?>% inasistencia
                        </span>
                        <span class="badge vid-badge-blur px-2 py-1">
                            <i class="fa-regular fa-calendar-check me-1 text-success"></i> <?= (int)$tf['atendidos']; ?>/<?= (int)$diasActivos; ?> días
                        </span>
                    </div>
                </div>

                <!-- Cuerpo de la tarjeta -->
                <div class="card-body p-2 d-flex flex-column justify-content-between">
                    <div>
                        <h6 class="fw-bold text-dark mb-1 text-truncate" title="<?= htmlspecialchars((string)$tf['nomusu']); ?>" style="font-size: 0.85rem;">
                            <span class="text-danger">#<?= $pos; ?></span> <?= htmlspecialchars((string)$tf['nomusu']); ?>
                        </h6>
                        <div class="text-muted small mb-2">
                            <i class="fa-regular fa-id-card me-1"></i><strong>Doc:</strong> <?= htmlspecialchars((string)$tf['ndocusu']); ?>
                        </div>

                        <!-- Caja de ficha y jornada (diseño de vigencia de vvidgen) -->
                        <div class="p-1 px-2 mb-2 rounded-3 bg-light border border-light-subtle" style="font-size: 0.76rem;">
                            <div class="text-truncate mb-1" title="<?= htmlspecialchars((string)($tf['nomfic'] ?? '')); ?>">
                                <i class="fa-solid fa-layer-group text-primary me-1"></i><strong>Ficha:</strong> <?= !empty($tf['nomfic']) ? (htmlspecialchars((string)($tf['idfic'] ?? '')) . ' - ' . htmlspecialchars((string)$tf['nomfic'])) : 'Sin ficha asignada'; ?>
                            </div>
                            <div>
                                <i class="fa-regular fa-clock text-info me-1"></i><strong>Jornada:</strong> <?= htmlspecialchars((string)($tf['nomjornada'] ?: 'Sin jornada')); ?>
                            </div>
                        </div>

                        <!-- Barra de progreso de inasistencia -->
                        <div class="mt-2">
                            <div class="d-flex justify-content-between mb-1" style="font-size: 0.74rem;">
                                <span class="text-muted">Asistió: <strong><?= (int)$tf['atendidos']; ?></strong> días</span>
                                <span class="text-danger fw-bold">Faltó: <strong><?= (int)$tf['faltas']; ?></strong> días</span>
                            </div>
                            <div class="progress" style="height: 6px; background-color: #f1f5f9;">
                                <div class="progress-bar bg-danger" role="progressbar" style="width: <?= $inasistenciaPct; ?>%;" aria-valuenow="<?= $inasistenciaPct; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer de la tarjeta con badge de alerta institucional -->
                <div class="card-footer bg-white border-0 pt-0 pb-2 px-2">
                    <hr class="mt-0 mb-2 text-muted opacity-25">
                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle w-100 py-1 d-flex align-items-center justify-content-center gap-1" style="font-size: 0.72rem;">
                        <i class="fa-solid fa-circle-exclamation"></i> Seguimiento Requerido
                    </span>
                </div>
            </div>
        </div>
        <?php 
                $pos++;
            } 
        } else { 
        ?>
        <div class="col-12 text-muted text-center py-4">No se registran datos de inasistencias para aprendices en este período.</div>
        <?php } ?>
    </div>
</div>

<!-- ==========================================================================
     SCRIPTS DE GRÁFICAS Y COMPORTAMIENTO
     ========================================================================== -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    var ctx = document.getElementById('chartAsistenciaMes');
    if (ctx && typeof Chart !== 'undefined') {
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Asistiendo en promedio', 'Hacen falta en promedio'],
                datasets: [{
                    data: [<?= (int)$promAsist; ?>, <?= (int)$promFalt; ?>],
                    backgroundColor: ['#006b29', '#ffdad6'],
                    borderColor: ['#ffffff', '#ffffff'],
                    borderWidth: 3,
                    hoverOffset: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '62%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { 
                            usePointStyle: true, 
                            padding: 16, 
                            font: { size: 12 } 
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                return ' ' + context.label + ': ' + context.parsed + ' aprendices';
                            }
                        }
                    }
                }
            }
        });
    }
});

function enter(event) {
    if (event.key === 'Enter') {
        var input = document.getElementById('ndocusu');
        if (input && input.form) input.form.submit();
        return false;
    }
    return true;
}

document.addEventListener("DOMContentLoaded", function() {
    var campoTexto = document.getElementById("ndocusu");
    if(campoTexto) {
        campoTexto.addEventListener("keypress", function(event) {
            if (event.keyCode === 13) {
                campoTexto.form.submit();
            }
        });
    }
});
</script>
</div>