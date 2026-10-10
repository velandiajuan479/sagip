<?php require_once("controllers/horcare.php"); ?>
<?php require_once("views/vscopehya.php"); ?>

<!-- ===========================================================================
     Dashboard "Áreas y Usuarios Asignados"
     Se conservan TODAS las clases y la estructura de diseño originales
     (kpi-card, kpi-icon-box, kpi-footer-strip, pulse-dot, filter-toolbar-container,
      btn-filter-pill, pill-dot, pill-count, toolbar-search-box, table-vid-cards,
      vid-card, vid-preview-box, vid-badges-top, vid-badges-bottom, vid-badge-blur,
      vid-play-btn, vid-actions-bar, is-list-view).
     El bloque <style> se agrega igual que en el resto de dashboards del sistema
     (horvprg.php, horvrsl.php, horvcmp.php...) porque esas clases NO estaban
     definidas en css/style.css: la vista se veía sin diseño.
     ========================================================================== -->
<style>
/* ---------- Contenedores de la página ---------- */
.inser { margin-bottom: 18px; }

/* ---------- Tarjetas KPI ---------- */
.kpi-card {
    background: #ffffff;
    border: 1px solid #edf1f7;
    border-radius: 14px;
    padding: 18px;
    box-shadow: 0 4px 16px rgba(0, 0, 0, .05);
    transition: all .25s cubic-bezier(.4, 0, .2, 1);
}
.kpi-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 22px rgba(0, 0, 0, .09);
    border-color: #dbe4ee;
}
.kpi-icon-box {
    width: 52px;
    height: 52px;
    flex: 0 0 52px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.kpi-footer-strip {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    flex-wrap: wrap;
    margin-top: 16px;
    padding-top: 10px;
    border-top: 1px dashed #e7ecf3;
}
.pulse-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: currentColor;
    display: inline-block;
    position: relative;
    animation: dashPulse 1.6s infinite ease-out;
}
@keyframes dashPulse {
    0%   { box-shadow: 0 0 0 0 rgba(0, 107, 41, .45); }
    70%  { box-shadow: 0 0 0 7px rgba(0, 107, 41, 0); }
    100% { box-shadow: 0 0 0 0 rgba(0, 107, 41, 0); }
}

/* ---------- Contenedor de formulario / barra de herramientas ---------- */
.filter-toolbar-container {
    background: #ffffff;
    border: 1px solid #edf1f7;
    border-radius: 14px;
    box-shadow: 0 4px 16px rgba(0, 0, 0, .04);
    padding: 16px 18px;
    margin-bottom: 18px;
}
.filter-toolbar-container h2 {
    font-size: 1.05rem;
    font-weight: 700;
    color: #0b1c30;
    margin-bottom: 14px;
    display: flex;
    align-items: center;
    gap: 8px;
}
/* Cuando el contenedor se usa como barra de herramientas (contiene las pestañas)
   se muestra en una sola fila: pestañas + exportar a la izquierda, buscador a la derecha. */
@supports selector(:has(*)) {
    .filter-toolbar-container:has(> .toolbar-left-group) {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }
}
.toolbar-left-group {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}
.toolbar-divider {
    width: 1px;
    height: 26px;
    background: #e3e9f0;
}
.filter-pills-group {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}
.btn-filter-pill {
    background: #f5f8fb;
    border: 1px solid #e3e9f0;
    color: #3e4a3d;
    border-radius: 999px;
    padding: 7px 13px;
    font-size: 12.5px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 7px;
    cursor: pointer;
    transition: all .2s;
}
.btn-filter-pill:hover { background: #eaf1f8; border-color: #cfdcea; }
.btn-filter-pill.active {
    background: #0b1c30;
    border-color: #0b1c30;
    color: #ffffff;
    box-shadow: 0 4px 12px rgba(11, 28, 48, .25);
}
.btn-filter-pill.active .pill-count { background: rgba(255, 255, 255, .22); color: #ffffff; }
.pill-dot { width: 8px; height: 8px; border-radius: 50%; flex: 0 0 8px; display: inline-block; }
.pill-count {
    background: #e3e9f0;
    color: #0b1c30;
    border-radius: 999px;
    padding: 1px 8px;
    font-size: 11px;
    font-weight: 700;
}
.toolbar-export-buttons { display: flex; align-items: center; gap: 6px; }
.toolbar-export-btn {
    width: 34px;
    height: 34px;
    border-radius: 9px;
    border: 1px solid #e3e9f0;
    background: #ffffff;
    color: #3c627f;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all .2s;
}
.toolbar-export-btn:hover { background: #0b1c30; border-color: #0b1c30; color: #ffffff; }
.toolbar-search-sort-group { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; margin-left: auto; }
.toolbar-search-box { position: relative; display: flex; align-items: center; }
.toolbar-search-box .icoldiz { position: absolute; left: 11px; color: #8a97a8; font-size: 12.5px; pointer-events: none; }
.toolbar-search-box input {
    border: 1px solid #e3e9f0;
    border-radius: 999px;
    background: #ffffff;
    padding: 8px 14px 8px 32px;
    font-size: 13px;
    min-width: 190px;
    color: #0b1c30;
}
.toolbar-search-box input:focus { outline: none; border-color: #3c627f; box-shadow: 0 0 0 3px rgba(60, 98, 127, .12); }
.toolbar-secondary-controls { display: flex; align-items: center; gap: 8px; }
.toolbar-select-sort {
    border: 1px solid #e3e9f0;
    border-radius: 9px;
    padding: 8px 10px;
    font-size: 12.5px;
    font-weight: 600;
    color: #3e4a3d;
    background: #ffffff;
}
.toolbar-view-toggle { display: flex; background: #f5f8fb; border: 1px solid #e3e9f0; border-radius: 9px; padding: 2px; gap: 2px; }
.toolbar-view-btn { width: 30px; height: 28px; border: 0; background: transparent; border-radius: 7px; color: #3c627f; cursor: pointer; }
.toolbar-view-btn.active { background: #ffffff; color: #0b1c30; box-shadow: 0 2px 6px rgba(11, 28, 48, .12); }

/* ---------- Catálogo en tarjetas (tablas convertidas a cuadrícula) ---------- */
.table-vid-cards {
    width: 100% !important;
    margin: 0 !important;
    border: 0 !important;
    background: transparent !important;
    --bs-table-bg: transparent;
}
.table-vid-cards > thead,
.table-vid-cards > tfoot { display: none; }
.table-vid-cards > tbody {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(310px, 1fr));
    gap: 20px;
}
.table-vid-cards > tbody > tr { display: block; background: transparent !important; }
.table-vid-cards > tbody > tr > td {
    display: block;
    border: 0 !important;
    padding: 0 !important;
    background: transparent !important;
    box-shadow: none !important;
}
.table-vid-cards td.dataTables_empty {
    text-align: center;
    padding: 42px 12px !important;
    color: #8a97a8;
    font-size: 14px;
    background: #ffffff !important;
    border: 2px dashed #dbe4ee !important;
    border-radius: 14px;
}

/* ---------- Tarjeta de catálogo ---------- */
.vid-card {
    border-radius: 14px !important;
    overflow: hidden;
    border: 1px solid #edf1f7 !important;
    transition: all .25s cubic-bezier(.4, 0, .2, 1);
}
.vid-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 24px rgba(0, 0, 0, .10) !important;
    border-color: #dbe4ee !important;
}
.vid-preview-box { position: relative; height: 148px; overflow: hidden; }
.vid-badges-top {
    position: absolute;
    top: 10px; left: 10px; right: 10px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 6px;
    z-index: 2;
}
.vid-badges-bottom {
    position: absolute;
    bottom: 10px; left: 10px; right: 10px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 6px;
    flex-wrap: wrap;
    z-index: 2;
}
.vid-badges-top .badge,
.vid-badges-bottom .badge {
    font-size: 10.5px;
    font-weight: 600;
    max-width: 62%;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.vid-badge-blur {
    background: rgba(15, 23, 42, .55) !important;
    color: #ffffff !important;
    border: 1px solid rgba(255, 255, 255, .18);
    backdrop-filter: blur(6px);
}
.vid-play-btn {
    position: absolute;
    top: 50%; left: 50%;
    transform: translate(-50%, -50%) scale(.9);
    width: 56px; height: 56px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, .18);
    color: #ffffff;
    border: 1px solid rgba(255, 255, 255, .4);
    backdrop-filter: blur(4px);
    cursor: pointer;
    z-index: 3;
    opacity: 0;
    transition: all .25s;
}
.vid-card:hover .vid-play-btn { opacity: 1; transform: translate(-50%, -50%) scale(1); }
.vid-actions-bar { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.vid-actions-bar .btn { flex: 1 1 auto; font-size: 12.5px; padding: 6px 10px; white-space: nowrap; }
.vid-actions-bar .w-100 { flex: 1 1 100%; }
.dash-chip {
    background: #f1f5f9;
    border: 1px solid #e3e9f0;
    color: #3c627f;
    border-radius: 999px;
    padding: 3px 9px;
    font-size: 10.5px;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    max-width: 100%;
}
a.dash-chip:hover { background: #0b1c30; border-color: #0b1c30; color: #ffffff; }

/* ---------- Vista Lista ---------- */
.table-vid-cards.is-list-view > tbody { grid-template-columns: 1fr; }
.table-vid-cards.is-list-view .vid-card { flex-direction: row; }
.table-vid-cards.is-list-view .vid-preview-box { width: 170px; flex: 0 0 170px; height: auto; min-height: 150px; }
.table-vid-cards.is-list-view .card-footer { flex: 0 0 210px; border-left: 1px solid #f1f4f8; }
.table-vid-cards.is-list-view .vid-play-btn { opacity: 1; transform: translate(-50%, -50%) scale(.82); }

/* ---------- Componentes de DataTables ---------- */
.dash-dt-hidden { display: none !important; }
.dataTables_wrapper { padding-top: 2px; }
.dataTables_wrapper .dataTables_length { font-size: 13px; color: #3e4a3d; margin-bottom: 10px; }
.dataTables_wrapper .dataTables_length select { border: 1px solid #e3e9f0; border-radius: 8px; padding: 4px 8px; margin: 0 4px; }
.dataTables_wrapper .dataTables_info { font-size: 12.5px; color: #6c7a89; padding-top: 14px; }
.dataTables_wrapper .dataTables_paginate { padding-top: 10px; }
.dataTables_wrapper .dataTables_paginate .paginate_button { border-radius: 8px !important; font-size: 12.5px; }

@media (max-width: 767.98px) {
    .table-vid-cards > tbody { grid-template-columns: 1fr; }
    .toolbar-search-box input { min-width: 130px; }
    .table-vid-cards.is-list-view .vid-card { flex-direction: column; }
    .table-vid-cards.is-list-view .vid-preview-box { width: 100%; flex: none; }
    .table-vid-cards.is-list-view .card-footer { flex: none; border-left: 0; }
}
/* Institucional HyA: estas tarjetas NO son videos (sin preview oscuro ni botón play) */
.vid-preview-box{background:linear-gradient(135deg,#00af00 0%,#008700 100%)!important;}
.vid-preview-box>div:first-child{background:linear-gradient(135deg,#00af00 0%,#008700 100%)!important;}
.vid-play-btn{display:none!important;}
</style>

<div class="conte">
    <?php echo titulo2("<i class='" . ($icono ?? '') . "'></i> Área", $isAdmin ? 1 : 0); ?>

    <!-- ===================== KPIs DE ÁREAS ===================== -->
    <div class="row my-4 g-4">
        <!-- Total Áreas -->
        <div class="col-12 col-md-4">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #3e4a3d;">Total en Sistema</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #0b1c30;"><?= $totAre; ?></span>
                            <span style="font-size: 13px; color: #3e4a3d;">áreas</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box" style="background-color: #b5dcfe; color: #3c627f;">
                        <i class="fa-solid fa-layer-group fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 11px; color: #3c627f;">
                        <i class="fa-solid fa-arrow-trend-up" style="font-size: 12px;"></i> Registradas
                    </span>
                    <span style="font-size: 11px; color: <?= $totAreSinOferta > 0 ? '#ba1a1a' : '#3e4a3d'; ?>;">
                        <i class="fa-solid fa-graduation-cap me-1"></i><?= $totProg; ?> programas · sin oferta: <?= $totAreSinOferta; ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Asignadas -->
        <div class="col-12 col-md-4">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #006b29;">Áreas Asignadas</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #006b29;"><?= $totAsig; ?></span>
                            <span style="font-size: 13px; color: rgba(0, 107, 41, 0.8);">con responsable</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box" style="background-color: #7afd88; color: #006b24;">
                        <i class="fa-solid fa-user-check fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="fw-semibold d-inline-flex align-items-center gap-2" style="font-size: 11px; color: #006b29;">
                        <span class="pulse-dot"></span> Con usuario
                    </span>
                    <span style="font-size: 11px; color: <?= $totAreInaResp > 0 ? '#ba1a1a' : '#3e4a3d'; ?>;">
                        <?= $totAreInaResp > 0 ? '<i class="fa-solid fa-triangle-exclamation me-1"></i>Responsable inactivo: ' . $totAreInaResp : 'Cobertura: ' . $porCob . '%'; ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Sin asignar -->
        <div class="col-12 col-md-4">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #ba1a1a;">Sin Asignar</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #0b1c30;"><?= $totSin; ?></span>
                            <span style="font-size: 13px; color: #3e4a3d;">pendientes</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box" style="background-color: #ffdad6; color: #ba1a1a;">
                        <i class="fa-solid fa-user-xmark fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="d-inline-flex align-items-center gap-1" style="font-size: 11px; color: #3e4a3d;">
                        <i class="fa-solid fa-clock-rotate-left" style="font-size: 12px;"></i> Sin responsable
                    </span>
                    <span class="fw-semibold" style="font-size: 11px; color: <?= $totSin > 0 ? '#ba1a1a' : '#3c627f'; ?>;">Pendientes</span>
                </div>
            </div>
        </div>
    </div>

    <!-- ===================== KPIs DE USUARIOS ASIGNADOS ===================== -->
    <div class="row my-4 g-4">
        <!-- Usuarios asignados -->
        <div class="col-12 col-md-4">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #006b29;">Usuarios Asignados</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #006b29;"><?= $totUsuAre; ?></span>
                            <span style="font-size: 13px; color: rgba(0, 107, 41, 0.8);">con áreas a cargo</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box" style="background-color: #d7f5dd; color: #006b24;">
                        <i class="fa-solid fa-users-gear fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="fw-semibold d-inline-flex align-items-center gap-2" style="font-size: 11px; color: #006b29;">
                        <span class="pulse-dot"></span> Responsables
                    </span>
                    <span style="font-size: 11px; color: <?= $totUsuIna > 0 ? '#ba1a1a' : '#3e4a3d'; ?>;">
                        <?= $totUsuIna > 0 ? '<i class="fa-solid fa-triangle-exclamation me-1"></i>Inactivos: ' . $totUsuIna : 'De ' . $totUsuOpe . ' operativos'; ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Usuarios sin área -->
        <div class="col-12 col-md-4">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #8a5a00;">Usuarios Sin Área</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #0b1c30;"><?= $totUsuSin; ?></span>
                            <span style="font-size: 13px; color: #3e4a3d;">candidatos</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box" style="background-color: #ffe8b5; color: #8a5a00;">
                        <i class="fa-solid fa-user-clock fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="d-inline-flex align-items-center gap-1" style="font-size: 11px; color: #3e4a3d;">
                        <i class="fa-solid fa-user-plus" style="font-size: 12px;"></i> Disponibles para asignar
                    </span>
                    <span class="fw-semibold" style="font-size: 11px; color: #3c627f;">Operativos activos</span>
                </div>
            </div>
        </div>

        <!-- Cobertura -->
        <?php
        $cobCol  = ($cobNiv === 'alta') ? '#006b29' : (($cobNiv === 'media') ? '#8a5a00' : '#ba1a1a');
        $cobBg   = ($cobNiv === 'alta') ? '#7afd88' : (($cobNiv === 'media') ? '#ffe8b5' : '#ffdad6');
        $cobIcon = ($cobNiv === 'alta') ? 'fa-shield-halved' : (($cobNiv === 'media') ? 'fa-circle-half-stroke' : 'fa-triangle-exclamation');
        ?>
        <div class="col-12 col-md-4">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: <?= $cobCol; ?>;">Cobertura de Áreas</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold" style="font-size: 32px; line-height: 1; color: <?= $cobCol; ?>;"><?= $porCob; ?>%</span>
                            <span style="font-size: 13px; color: #3e4a3d;">asignadas</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box" style="background-color: <?= $cobBg; ?>; color: <?= $cobCol; ?>;">
                        <i class="fa-solid <?= $cobIcon; ?> fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="fw-semibold d-inline-flex align-items-center gap-2" style="font-size: 11px; color: <?= $cobCol; ?>;">
                        <span class="pulse-dot"></span> <?= $cobTxt; ?>
                    </span>
                    <span style="font-size: 11px; color: #3e4a3d;"><?= $totAsig; ?> de <?= $totAre; ?> áreas</span>
                </div>
            </div>
        </div>
    </div>

    <?php if ($isAdmin): ?>
        <div class="inser">
            <form id="frmins" class="filter-toolbar-container" action="home.php?pg=<?= $pg; ?>" method="POST">
                <h2><i class="fa-solid fa-sitemap"></i> <?= $datOne ? 'Actualizar área' : 'Nueva área'; ?></h2>
                <div class="row">
                    <div class="form-group col-md-5">
                        <label for="nomare"><strong>Nombre del Área</strong></label>
                        <input type="text" name="nomare" id="nomare" class="form-control"
                               value="<?php if (!empty($datOne['nomare'])) echo htmlspecialchars($datOne['nomare']); ?>"
                               placeholder="Ingrese nombre del área" required>
                    </div>

                    <div class="form-group col-md-5">
                        <label for="idusu"><strong>Usuario Asignado</strong></label>
                        <select name="idusu" id="idusu" class="form-control form-select">
                            <option value="">-- Sin asignar --</option>
                            <?php if ($datUsu) { foreach ($datUsu as $du) { ?>
                                <option value="<?= $du['idusu']; ?>" <?php if (!empty($datOne['idusu']) && $datOne['idusu'] == $du['idusu']) echo "selected"; ?>>
                                    <?= htmlspecialchars($du['nomusu'] . (!empty($du['nomper']) ? ' - ' . $du['nomper'] : '')); ?>
                                </option>
                            <?php } } ?>
                        </select>
                    </div>

                    <div class="form-group col-md-2">
                        <br>
                        <input type="submit" value="<?= $datOne ? 'Actualizar' : 'Guardar'; ?>" class="btn btn-success" style="width:100%;">
                        <?php if ($datOne): ?>
                            <a href="home.php?pg=<?= $pg; ?>" class="btn btn-secondary" style="width:100%;margin-top:6px;">Cancelar</a>
                        <?php endif; ?>
                        <input type="hidden" name="opera" value="save">
                        <input type="hidden" name="idare" value="<?= htmlspecialchars($idare ?? ''); ?>">
                    </div>
                </div>
            </form>
        </div>
    <?php endif; ?>
</div>

<!-- Toolbar de Control y Filtros -->
<div class="filter-toolbar-container">
    <div class="toolbar-left-group">
        <!-- La clase nav es un requisito de Bootstrap 5.0.1 para los tablist: sin ella el
             cambio de pestaña lanza un error y el panel no se muestra. -->
        <div class="filter-pills-group nav" id="tabVideos" role="tablist">
            <button class="btn-filter-pill active" id="activos-tab" data-bs-toggle="tab" data-bs-target="#activos" type="button" role="tab" aria-controls="activos" aria-selected="true" onclick="filterByTab('activos')">
                <span class="pill-dot" style="background-color: #006b29;"></span>
                <span>Asignadas</span>
                <span class="pill-count"><?= count($datAct ?? []); ?></span>
            </button>
            <button class="btn-filter-pill" id="inactivos-tab" data-bs-toggle="tab" data-bs-target="#inactivos" type="button" role="tab" aria-controls="inactivos" aria-selected="false" onclick="filterByTab('inactivos')">
                <span class="pill-dot" style="background-color: #ba1a1a;"></span>
                <span>Sin asignar</span>
                <span class="pill-count"><?= count($datInact ?? []); ?></span>
            </button>
            <button class="btn-filter-pill" id="todos-tab" data-bs-toggle="tab" data-bs-target="#todos" type="button" role="tab" aria-controls="todos" aria-selected="false" onclick="filterByTab('todos')">
                <span class="pill-dot" style="background-color: #3c627f;"></span>
                <span>Todas</span>
                <span class="pill-count"><?= $totAre; ?></span>
            </button>
            <button class="btn-filter-pill" id="usuarios-tab" data-bs-toggle="tab" data-bs-target="#usuarios" type="button" role="tab" aria-controls="usuarios" aria-selected="false" onclick="filterByTab('usuarios')">
                <span class="pill-dot" style="background-color: #7b1fa2;"></span>
                <span>Usuarios</span>
                <span class="pill-count"><?= count($usuAreas ?? []); ?></span>
            </button>
            <button class="btn-filter-pill" id="usuariosin-tab" data-bs-toggle="tab" data-bs-target="#usuariosin" type="button" role="tab" aria-controls="usuariosin" aria-selected="false" onclick="filterByTab('usuariosin')">
                <span class="pill-dot" style="background-color: #8a5a00;"></span>
                <span>Sin área</span>
                <span class="pill-count"><?= $totUsuSin; ?></span>
            </button>
        </div>
        <div class="toolbar-divider d-none d-sm-block"></div>
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
            <input type="text" id="customVideoSearch" placeholder="Buscar" onkeyup="syncCustomSearch(this.value)">
        </div>
        <div class="toolbar-secondary-controls">
            <select class="toolbar-select-sort" id="customVideoSort" onchange="syncCustomSort(this.value)">
                <option value="asc">Ascendente</option>
                <option value="desc">Descendente</option>
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

<div class="tab-content" id="tabVideosContent">
    <!-- Asignadas -->
    <div class="tab-pane fade show active" id="activos" role="tabpanel" aria-labelledby="activos-tab">
        <table id="example" class="table table-striped table-vid-cards" style="width:100%">
            <thead><tr><th>Asignadas</th></tr></thead>
            <tbody>
                <?php if($datAct){ foreach ($datAct as $dt) {
                    $nprog    = isset($dt['programas']) ? (int)$dt['programas'] : 0;
                    $cargaTxt = ($nprog === 0) ? 'Sin oferta' : (($nprog >= 5) ? 'Oferta alta' : 'Oferta normal');
                    $cargaCol = ($nprog === 0) ? '#ba1a1a' : (($nprog >= 5) ? '#8a5a00' : '#006b29');
                    $inactivo = (isset($dt['actusu']) && (int)$dt['actusu'] === 0);
                ?>
                <tr data-order="<?=$dt['idare'];?>">
                    <td data-order="<?=$dt['idare'];?>"
                        data-search="<?=$dt["idare"];?> <?=htmlspecialchars($dt["nomare"] ?? '');?> <?=htmlspecialchars($dt["nomusu"] ?? '');?> <?=htmlspecialchars($dt["nomper"] ?? '');?>"
                        data-export="Área #<?=$dt["idare"];?> - <?=htmlspecialchars($dt["nomare"] ?? '');?> | Responsable: <?=htmlspecialchars($dt["nomusu"] ?? 'N/A');?> | Perfil: <?=htmlspecialchars($dt["nomper"] ?? 'N/A');?> | Programas: <?=$nprog;?> | Estado: <?= $inactivo ? 'Responsable inactivo' : 'Asignada';?>">
                        <div class="card h-100 shadow-sm border-0 vid-card">
                            <div class="vid-preview-box">
                                <div style="display:flex;align-items:center;justify-content:center;width:100%;height:100%;background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);color:#fff;">
                                    <i class="fa-solid fa-sitemap" style="font-size:48px;opacity:0.9;"></i>
                                </div>
                                <div class="vid-badges-top">
                                    <?php if ($inactivo): ?>
                                        <span class="badge bg-danger shadow-sm px-2 py-1">
                                            <i class="fa-solid fa-triangle-exclamation me-1"></i> Responsable inactivo
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-success shadow-sm px-2 py-1">
                                            <i class="fa-solid fa-user-check me-1"></i> Asignada
                                        </span>
                                    <?php endif; ?>
                                    <span class="badge vid-badge-blur px-2 py-1 shadow-sm">
                                        <i class="fa-solid fa-hashtag me-1 text-info"></i> #<?=$dt["idare"];?>
                                    </span>
                                </div>
                                <div class="vid-play-btn" data-bs-toggle="modal" data-bs-target="#mod<?=$dt["idare"];?>" title="Ver detalle" role="button">
                                    <i class="fa-solid fa-eye"></i>
                                </div>
                                <div class="vid-badges-bottom">
                                    <span class="badge vid-badge-blur px-2 py-1">
                                        <i class="fa-solid fa-user me-1 text-warning"></i> <?=htmlspecialchars($dt["nomusu"] ?? '');?>
                                    </span>
                                    <span class="badge vid-badge-blur px-2 py-1">
                                        <i class="fa-solid fa-id-badge me-1 text-primary"></i> <?=htmlspecialchars($dt["nomper"] ?? '');?>
                                    </span>
                                    <span class="badge vid-badge-blur px-2 py-1">
                                        <i class="fa-solid fa-graduation-cap me-1 text-info"></i> <?=$nprog;?> prog.
                                    </span>
                                </div>
                            </div>
                            <div class="card-body p-3 d-flex flex-column justify-content-between">
                                <div>
                                    <h6 class="fw-bold text-dark mb-2 text-truncate" title="<?=htmlspecialchars($dt["nomare"] ?? '');?>" style="font-size: 1.05rem;">
                                        <span class="text-primary">#<?=$dt["idare"];?></span> - <?=htmlspecialchars($dt["nomare"] ?? '');?>
                                    </h6>
                                    <div class="p-2 mb-3 rounded-3 bg-light border border-light-subtle d-flex justify-content-between align-items-center" style="font-size: 0.8rem;">
                                        <div>
                                            <span class="text-muted d-block" style="font-size: 0.72rem;">Responsable</span>
                                            <span class="fw-bold text-success">
                                                <i class="fa-solid fa-user me-1"></i><?=htmlspecialchars($dt["nomusu"] ?? 'N/A');?>
                                            </span>
                                        </div>
                                        <div class="text-muted"><i class="fa-solid fa-arrow-right-long mx-1"></i></div>
                                        <div class="text-end">
                                            <span class="text-muted d-block" style="font-size: 0.72rem;">Perfil</span>
                                            <span class="fw-bold text-dark">
                                                <?=htmlspecialchars($dt["nomper"] ?? 'N/A');?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between text-muted mb-1" style="font-size: 0.78rem;">
                                        <span><i class="fa-solid fa-sitemap me-1 text-primary"></i> <strong>Área:</strong> <?=htmlspecialchars($dt["nomare"] ?? '');?></span>
                                    </div>
                                    <div class="d-flex justify-content-between text-muted mb-1" style="font-size: 0.78rem;">
                                        <span><i class="fa-solid fa-graduation-cap me-1 text-primary"></i> <strong>Oferta:</strong> <?=$nprog;?> programas</span>
                                        <span class="fw-semibold" style="color: <?=$cargaCol;?>;"><?=$cargaTxt;?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer bg-white border-0 pt-0 pb-3 px-3">
                                <hr class="mt-0 mb-3 text-muted opacity-25">
                                <div class="vid-actions-bar">
                                    <button type="button" class="btn btn-outline-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#mod<?=$dt["idare"];?>" title="Ver">
                                        <i class="fa-solid fa-eye"></i> <span class="d-none d-sm-inline">Ver</span>
                                    </button>
                                    <?php if ($isAdmin): ?>
                                    <a href="home.php?pg=<?=$pg;?>&idare=<?=$dt["idare"];?>&opera=edi" class="btn btn-outline-warning text-dark shadow-sm" title="Editar">
                                        <i class="fa-solid fa-pen-to-square"></i> <span class="d-none d-sm-inline">Editar</span>
                                    </a>
                                    <a href="home.php?pg=<?=$pg;?>&idare=<?=$dt["idare"];?>&opera=eli" class="btn btn-outline-danger shadow-sm w-100" title="Eliminar" onclick="return eli(this);">
                                        <i class="fa-solid fa-trash-can"></i> <span class="d-none d-sm-inline">Elim.</span>
                                    </a>
                                    <?php else: ?>
                                    <span class="btn btn-outline-secondary disabled w-100" style="opacity:0.6;">Solo lectura</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="modal fade" id="mod<?=$dt["idare"];?>" tabindex="-1" aria-labelledby="modLabel<?=$dt["idare"];?>" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                <div class="modal-content shadow-lg border-0" style="border-radius: 16px; overflow: hidden;">
                                    <div class="modal-header bg-dark text-white border-0 px-4 py-3">
                                        <h5 class="modal-title fs-5 d-flex align-items-center gap-2" id="modLabel<?=$dt["idare"];?>">
                                            <i class="fa-solid fa-sitemap text-success"></i>
                                            <span class="text-truncate" style="max-width: 650px;"><?=htmlspecialchars($dt["nomare"] ?? '');?></span>
                                        </h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <strong>ID Área:</strong> <?=$dt["idare"];?><br>
                                                <strong>Nombre:</strong> <?=htmlspecialchars($dt["nomare"] ?? '');?><br>
                                                <strong>Usuario ID:</strong> <?=$dt["idusu"] ?? 'Sin asignar';?>
                                            </div>
                                            <div class="col-md-6">
                                                <strong>Responsable:</strong> <?=htmlspecialchars($dt["nomusu"] ?? 'Sin asignar');?><br>
                                                <strong>Perfil:</strong> <?=htmlspecialchars($dt["nomper"] ?? 'N/A');?><br>
                                                <strong>Programas (oferta):</strong> <?=$nprog;?> — <?=$cargaTxt;?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer bg-light border-0 px-4 py-3 d-flex justify-content-between align-items-center">
                                        <div class="text-muted small">
                                            <i class="fa-solid fa-hashtag me-1"></i>#<?=$dt["idare"];?> | <?=htmlspecialchars($dt["nomare"] ?? '');?>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <?php if ($isAdmin): ?>
                                            <a href="home.php?pg=<?=$pg;?>&idare=<?=$dt["idare"];?>&opera=edi" class="btn btn-warning btn-sm">
                                                <i class="fa-solid fa-pen-to-square me-1"></i> Editar
                                            </a>
                                            <?php endif; ?>
                                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                <?php }} ?>
            </tbody>
            <tfoot><tr><th>Asignadas</th></tr></tfoot>
        </table>
    </div>

    <!-- Sin asignar -->
    <div class="tab-pane fade" id="inactivos" role="tabpanel" aria-labelledby="inactivos-tab">
        <table id="myt" class="table table-striped table-vid-cards" style="width:100%">
            <thead><tr><th>Sin asignar</th></tr></thead>
            <tbody>
                <?php if($datInact){ foreach ($datInact as $dt) {
                    $nprog    = isset($dt['programas']) ? (int)$dt['programas'] : 0;
                    $cargaTxt = ($nprog === 0) ? 'Sin oferta' : (($nprog >= 5) ? 'Oferta alta' : 'Oferta normal');
                    $cargaCol = ($nprog === 0) ? '#ba1a1a' : (($nprog >= 5) ? '#8a5a00' : '#006b29');
                ?>
                <tr data-order="<?=$dt['idare'];?>">
                    <td data-order="<?=$dt['idare'];?>"
                        data-search="<?=$dt["idare"];?> <?=htmlspecialchars($dt["nomare"] ?? '');?>"
                        data-export="Área #<?=$dt["idare"];?> - <?=htmlspecialchars($dt["nomare"] ?? '');?> | Responsable: SIN ASIGNAR | Programas: <?=$nprog;?>">
                        <div class="card h-100 shadow-sm border-0 vid-card">
                            <div class="vid-preview-box" style="filter: grayscale(0.25);">
                                <div style="display:flex;align-items:center;justify-content:center;width:100%;height:100%;background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);color:#fff;">
                                    <i class="fa-solid fa-sitemap" style="font-size:48px;opacity:0.6;"></i>
                                </div>
                                <div class="vid-badges-top">
                                    <span class="badge bg-secondary shadow-sm px-2 py-1">
                                        <i class="fa-solid fa-user-xmark me-1"></i> Sin asignar
                                    </span>
                                    <span class="badge vid-badge-blur px-2 py-1 shadow-sm">
                                        <i class="fa-solid fa-hashtag me-1 text-info"></i> #<?=$dt["idare"];?>
                                    </span>
                                </div>
                                <div class="vid-play-btn" data-bs-toggle="modal" data-bs-target="#mod<?=$dt["idare"];?>" title="Ver detalle" role="button">
                                    <i class="fa-solid fa-eye"></i>
                                </div>
                                <div class="vid-badges-bottom">
                                    <span class="badge vid-badge-blur px-2 py-1">
                                        <i class="fa-solid fa-triangle-exclamation me-1 text-warning"></i> Pendiente
                                    </span>
                                    <span class="badge vid-badge-blur px-2 py-1">
                                        <i class="fa-solid fa-graduation-cap me-1 text-info"></i> <?=$nprog;?> prog.
                                    </span>
                                </div>
                            </div>
                            <div class="card-body p-3 d-flex flex-column justify-content-between">
                                <div>
                                    <h6 class="fw-bold text-dark mb-2 text-truncate" title="<?=htmlspecialchars($dt["nomare"] ?? '');?>" style="font-size: 1.05rem;">
                                        <span class="text-secondary">#<?=$dt["idare"];?></span> - <?=htmlspecialchars($dt["nomare"] ?? '');?>
                                    </h6>
                                    <div class="p-2 mb-3 rounded-3 bg-light border border-light-subtle d-flex justify-content-between align-items-center" style="font-size: 0.8rem;">
                                        <div>
                                            <span class="text-muted d-block" style="font-size: 0.72rem;">Estado</span>
                                            <span class="fw-bold text-secondary">
                                                <i class="fa-solid fa-circle-xmark me-1"></i>Sin responsable
                                            </span>
                                        </div>
                                        <div class="text-muted"><i class="fa-solid fa-arrow-right-long mx-1"></i></div>
                                        <div class="text-end">
                                            <span class="text-muted d-block" style="font-size: 0.72rem;">ID</span>
                                            <span class="fw-bold text-danger">#<?=$dt["idare"];?></span>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between text-muted mb-1" style="font-size: 0.78rem;">
                                        <span><i class="fa-solid fa-graduation-cap me-1 text-primary"></i> <strong>Oferta:</strong> <?=$nprog;?> programas</span>
                                        <span class="fw-semibold" style="color: <?=$cargaCol;?>;"><?=$cargaTxt;?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer bg-white border-0 pt-0 pb-3 px-3">
                                <hr class="mt-0 mb-3 text-muted opacity-25">
                                <div class="vid-actions-bar">
                                    <button type="button" class="btn btn-outline-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#mod<?=$dt["idare"];?>" title="Ver">
                                        <i class="fa-solid fa-eye"></i> <span class="d-none d-sm-inline">Ver</span>
                                    </button>
                                    <?php if ($isAdmin): ?>
                                    <a href="home.php?pg=<?=$pg;?>&idare=<?=$dt["idare"];?>&opera=edi" class="btn btn-outline-warning text-dark shadow-sm" title="Editar">
                                        <i class="fa-solid fa-pen-to-square"></i> <span class="d-none d-sm-inline">Asignar</span>
                                    </a>
                                    <a href="home.php?pg=<?=$pg;?>&idare=<?=$dt["idare"];?>&opera=eli" class="btn btn-outline-danger shadow-sm w-100" title="Eliminar" onclick="return eli(this);">
                                        <i class="fa-solid fa-trash-can"></i> <span class="d-none d-sm-inline">Elim.</span>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="modal fade" id="mod<?=$dt["idare"];?>" tabindex="-1" aria-labelledby="modLabel<?=$dt["idare"];?>" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                <div class="modal-content shadow-lg border-0" style="border-radius: 16px; overflow: hidden;">
                                    <div class="modal-header bg-dark text-white border-0 px-4 py-3">
                                        <h5 class="modal-title fs-5 d-flex align-items-center gap-2" id="modLabel<?=$dt["idare"];?>">
                                            <i class="fa-solid fa-sitemap text-secondary"></i>
                                            <span class="text-truncate" style="max-width: 650px;"><?=htmlspecialchars($dt["nomare"] ?? '');?></span>
                                        </h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <strong>Área:</strong> <?=htmlspecialchars($dt["nomare"] ?? '');?><br>
                                        <strong>Estado:</strong> Sin asignar — requiere responsable<br>
                                        <strong>Programas (oferta):</strong> <?=$nprog;?> — <?=$cargaTxt;?><br>
                                        <strong>ID:</strong> <?=$dt["idare"];?>
                                    </div>
                                    <div class="modal-footer bg-light border-0 px-4 py-3 d-flex justify-content-between align-items-center">
                                        <div class="text-muted small">
                                            <i class="fa-solid fa-hashtag me-1"></i>#<?=$dt["idare"];?>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <?php if ($isAdmin): ?>
                                            <a href="home.php?pg=<?=$pg;?>&idare=<?=$dt["idare"];?>&opera=edi" class="btn btn-warning btn-sm">
                                                <i class="fa-solid fa-pen-to-square me-1"></i> Asignar
                                            </a>
                                            <?php endif; ?>
                                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                <?php }} ?>
            </tbody>
            <tfoot><tr><th>Sin asignar</th></tr></tfoot>
        </table>
    </div>

    <!-- Todas -->
    <div class="tab-pane fade" id="todos" role="tabpanel" aria-labelledby="todos-tab">
        <table id="tblTodos" class="table table-striped table-vid-cards" style="width:100%">
            <thead><tr><th>Todas</th></tr></thead>
            <tbody>
                <?php 
                $datTodos = $datAll ?? [];
                if($datTodos){ foreach ($datTodos as $dt) {
                    $esAsignada = !empty($dt["idusu"]);
                    $nprog      = isset($dt['programas']) ? (int)$dt['programas'] : 0;
                    $cargaTxt   = ($nprog === 0) ? 'Sin oferta' : (($nprog >= 5) ? 'Oferta alta' : 'Oferta normal');
                    $cargaCol   = ($nprog === 0) ? '#ba1a1a' : (($nprog >= 5) ? '#8a5a00' : '#006b29');
                    $inactivo   = ($esAsignada && isset($dt['actusu']) && (int)$dt['actusu'] === 0);
                ?>
                <tr data-order="<?=$dt['idare'];?>">
                    <td data-order="<?=$dt['idare'];?>"
                        data-search="<?=$dt["idare"];?> <?=htmlspecialchars($dt["nomare"] ?? '');?> <?=htmlspecialchars($dt["nomusu"] ?? '');?> <?=htmlspecialchars($dt["nomper"] ?? '');?>"
                        data-export="Área #<?=$dt["idare"];?> - <?=htmlspecialchars($dt["nomare"] ?? '');?> | Responsable: <?=htmlspecialchars($dt["nomusu"] ?? 'SIN ASIGNAR');?> | Perfil: <?=htmlspecialchars($dt["nomper"] ?? 'N/A');?> | Programas: <?=$nprog;?> | Estado: <?= $esAsignada ? ($inactivo ? 'Responsable inactivo' : 'Asignada') : 'Sin asignar';?>">
                        <div class="card h-100 shadow-sm border-0 vid-card">
                            <div class="vid-preview-box" <?= !$esAsignada ? 'style="filter: grayscale(0.25);"' : ''; ?>>
                                <div style="display:flex;align-items:center;justify-content:center;width:100%;height:100%;background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);color:#fff;">
                                    <i class="fa-solid fa-sitemap" style="font-size:48px;opacity:0.9;"></i>
                                </div>
                                <div class="vid-badges-top">
                                    <?php if($esAsignada): ?>
                                        <span class="badge <?= $inactivo ? 'bg-danger' : 'bg-success'; ?> shadow-sm px-2 py-1">
                                            <i class="fa-solid <?= $inactivo ? 'fa-triangle-exclamation' : 'fa-user-check'; ?> me-1"></i> <?= $inactivo ? 'Responsable inactivo' : 'Asignada'; ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary shadow-sm px-2 py-1">
                                            <i class="fa-solid fa-user-xmark me-1"></i> Sin asignar
                                        </span>
                                    <?php endif; ?>
                                    <span class="badge vid-badge-blur px-2 py-1 shadow-sm">
                                        <i class="fa-solid fa-hashtag me-1 text-info"></i> #<?=$dt["idare"];?>
                                    </span>
                                </div>
                                <div class="vid-play-btn" data-bs-toggle="modal" data-bs-target="#modTodos<?=$dt["idare"];?>" title="Ver detalle" role="button">
                                    <i class="fa-solid fa-eye"></i>
                                </div>
                                <div class="vid-badges-bottom">
                                    <span class="badge vid-badge-blur px-2 py-1">
                                        <i class="fa-solid fa-user me-1 text-warning"></i> <?=htmlspecialchars($dt["nomusu"] ?? 'Sin asignar');?>
                                    </span>
                                    <span class="badge vid-badge-blur px-2 py-1">
                                        <i class="fa-solid fa-graduation-cap me-1 text-info"></i> <?=$nprog;?> prog.
                                    </span>
                                </div>
                            </div>
                            <div class="card-body p-3 d-flex flex-column justify-content-between">
                                <div>
                                    <h6 class="fw-bold text-dark mb-2 text-truncate" title="<?=htmlspecialchars($dt["nomare"] ?? '');?>" style="font-size: 1.05rem;">
                                        <span class="<?= $esAsignada ? 'text-primary' : 'text-secondary'; ?>">#<?=$dt["idare"];?></span> - <?=htmlspecialchars($dt["nomare"] ?? '');?>
                                    </h6>
                                    <div class="p-2 mb-3 rounded-3 bg-light border border-light-subtle d-flex justify-content-between align-items-center" style="font-size: 0.8rem;">
                                        <div>
                                            <span class="text-muted d-block" style="font-size: 0.72rem;">Responsable</span>
                                            <span class="fw-bold <?= $esAsignada ? 'text-success' : 'text-secondary'; ?>">
                                                <?=htmlspecialchars($dt["nomusu"] ?? 'Sin asignar');?>
                                            </span>
                                        </div>
                                        <div class="text-muted"><i class="fa-solid fa-arrow-right-long mx-1"></i></div>
                                        <div class="text-end">
                                            <span class="text-muted d-block" style="font-size: 0.72rem;">Perfil</span>
                                            <span class="fw-bold text-dark"><?=htmlspecialchars($dt["nomper"] ?? 'N/A');?></span>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between text-muted mb-1" style="font-size: 0.78rem;">
                                        <span><i class="fa-solid fa-graduation-cap me-1 text-primary"></i> <strong>Oferta:</strong> <?=$nprog;?> programas</span>
                                        <span class="fw-semibold" style="color: <?=$cargaCol;?>;"><?=$cargaTxt;?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer bg-white border-0 pt-0 pb-3 px-3">
                                <hr class="mt-0 mb-3 text-muted opacity-25">
                                <div class="vid-actions-bar">
                                    <button type="button" class="btn btn-outline-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modTodos<?=$dt["idare"];?>" title="Ver">
                                        <i class="fa-solid fa-eye"></i> <span class="d-none d-sm-inline">Ver</span>
                                    </button>
                                    <?php if ($isAdmin): ?>
                                    <a href="home.php?pg=<?=$pg;?>&idare=<?=$dt["idare"];?>&opera=edi" class="btn btn-outline-warning text-dark shadow-sm" title="Editar">
                                        <i class="fa-solid fa-pen-to-square"></i> <span class="d-none d-sm-inline">Editar</span>
                                    </a>
                                    <a href="home.php?pg=<?=$pg;?>&idare=<?=$dt["idare"];?>&opera=eli" class="btn btn-outline-danger shadow-sm w-100" title="Eliminar" onclick="return eli(this);">
                                        <i class="fa-solid fa-trash-can"></i> <span class="d-none d-sm-inline">Elim.</span>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="modal fade" id="modTodos<?=$dt["idare"];?>" tabindex="-1" aria-labelledby="modTodosLabel<?=$dt["idare"];?>" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                <div class="modal-content shadow-lg border-0" style="border-radius: 16px; overflow: hidden;">
                                    <div class="modal-header bg-dark text-white border-0 px-4 py-3">
                                        <h5 class="modal-title fs-5 d-flex align-items-center gap-2" id="modTodosLabel<?=$dt["idare"];?>">
                                            <i class="fa-solid fa-sitemap <?= $esAsignada ? 'text-success' : 'text-secondary'; ?>"></i>
                                            <span class="text-truncate" style="max-width: 650px;"><?=htmlspecialchars($dt["nomare"] ?? '');?></span>
                                        </h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <strong>ID:</strong> <?=$dt["idare"];?><br>
                                                <strong>Área:</strong> <?=htmlspecialchars($dt["nomare"] ?? '');?>
                                            </div>
                                            <div class="col-md-6">
                                                <strong>Responsable:</strong> <?=htmlspecialchars($dt["nomusu"] ?? 'Sin asignar');?><br>
                                                <strong>Perfil:</strong> <?=htmlspecialchars($dt["nomper"] ?? 'N/A');?><br>
                                                <strong>Programas (oferta):</strong> <?=$nprog;?> — <?=$cargaTxt;?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer bg-light border-0 px-4 py-3 d-flex justify-content-between align-items-center">
                                        <div class="text-muted small">
                                            <i class="fa-solid fa-hashtag me-1"></i>#<?=$dt["idare"];?> | <?=htmlspecialchars($dt["nomare"] ?? '');?>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <?php if ($isAdmin): ?>
                                            <a href="home.php?pg=<?=$pg;?>&idare=<?=$dt["idare"];?>&opera=edi" class="btn btn-warning btn-sm">
                                                <i class="fa-solid fa-pen-to-square me-1"></i> Editar
                                            </a>
                                            <?php endif; ?>
                                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                <?php }} ?>
            </tbody>
            <tfoot><tr><th>Todas</th></tr></tfoot>
        </table>
    </div>

    <!-- Usuarios con áreas asignadas -->
    <div class="tab-pane fade" id="usuarios" role="tabpanel" aria-labelledby="usuarios-tab">
        <table id="tblUsu" class="table table-striped table-vid-cards" style="width:100%">
            <thead><tr><th>Usuarios asignados</th></tr></thead>
            <tbody>
                <?php if($usuAreas){ foreach ($usuAreas as $uu) {
                    $idu      = $uu['idusu'];
                    $inactivo = ((int)$uu['actusu'] === 0);
                    $cargaTxt = ($uu['tot'] >= 3) ? 'Carga alta' : 'Carga normal';
                    $cargaCol = ($uu['tot'] >= 3) ? '#8a5a00' : '#006b29';
                    $nombres  = array();
                    foreach ($uu['areas'] as $ar) { $nombres[] = $ar['nomare']; }
                ?>
                <tr data-order="<?=$idu;?>">
                    <td data-order="<?=$idu;?>"
                        data-search="<?=$idu;?> <?=htmlspecialchars($uu['nomusu'] ?? '');?> <?=htmlspecialchars($uu['nomper'] ?? '');?> <?=htmlspecialchars($uu['nomcen'] ?? '');?> <?=htmlspecialchars(implode(' ', $nombres));?>"
                        data-export="Usuario #<?=$idu;?> - <?=htmlspecialchars($uu['nomusu'] ?? '');?> | Perfil: <?=htmlspecialchars($uu['nomper'] ?? 'N/A');?> | Áreas a cargo: <?=$uu['tot'];?> (<?=htmlspecialchars(implode(', ', $nombres));?>) | Programas: <?=$uu['prog'];?>">
                        <div class="card h-100 shadow-sm border-0 vid-card">
                            <div class="vid-preview-box">
                                <div style="display:flex;align-items:center;justify-content:center;width:100%;height:100%;background: linear-gradient(135deg, #312e81 0%, #0f172a 100%);color:#fff;">
                                    <i class="fa-solid fa-user-tie" style="font-size:48px;opacity:0.9;"></i>
                                </div>
                                <div class="vid-badges-top">
                                    <?php if ($inactivo): ?>
                                        <span class="badge bg-danger shadow-sm px-2 py-1">
                                            <i class="fa-solid fa-triangle-exclamation me-1"></i> Usuario inactivo
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-success shadow-sm px-2 py-1">
                                            <i class="fa-solid fa-user-check me-1"></i> Con áreas
                                        </span>
                                    <?php endif; ?>
                                    <span class="badge vid-badge-blur px-2 py-1 shadow-sm">
                                        <i class="fa-solid fa-hashtag me-1 text-info"></i> #<?=$idu;?>
                                    </span>
                                </div>
                                <div class="vid-play-btn" data-bs-toggle="modal" data-bs-target="#modU<?=$idu;?>" title="Ver detalle" role="button">
                                    <i class="fa-solid fa-eye"></i>
                                </div>
                                <div class="vid-badges-bottom">
                                    <span class="badge vid-badge-blur px-2 py-1">
                                        <i class="fa-solid fa-id-badge me-1 text-primary"></i> <?=htmlspecialchars($uu['nomper'] ?? 'Sin perfil');?>
                                    </span>
                                    <span class="badge vid-badge-blur px-2 py-1">
                                        <i class="fa-solid fa-sitemap me-1 text-warning"></i> <?=$uu['tot'];?> área<?= $uu['tot'] == 1 ? '' : 's';?>
                                    </span>
                                </div>
                            </div>
                            <div class="card-body p-3 d-flex flex-column justify-content-between">
                                <div>
                                    <h6 class="fw-bold text-dark mb-2 text-truncate" title="<?=htmlspecialchars($uu['nomusu'] ?? '');?>" style="font-size: 1.05rem;">
                                        <span class="text-primary">#<?=$idu;?></span> - <?=htmlspecialchars($uu['nomusu'] ?? '');?>
                                    </h6>
                                    <div class="p-2 mb-3 rounded-3 bg-light border border-light-subtle d-flex justify-content-between align-items-center" style="font-size: 0.8rem;">
                                        <div>
                                            <span class="text-muted d-block" style="font-size: 0.72rem;">Áreas a cargo</span>
                                            <span class="fw-bold text-success">
                                                <i class="fa-solid fa-sitemap me-1"></i><?=$uu['tot'];?>
                                            </span>
                                        </div>
                                        <div class="text-muted"><i class="fa-solid fa-arrow-right-long mx-1"></i></div>
                                        <div class="text-end">
                                            <span class="text-muted d-block" style="font-size: 0.72rem;">Programas</span>
                                            <span class="fw-bold text-dark"><?=$uu['prog'];?></span>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between text-muted mb-2" style="font-size: 0.78rem;">
                                        <span><i class="fa-solid fa-gauge-high me-1 text-primary"></i> <strong>Carga:</strong> <?=$cargaTxt;?></span>
                                        <span><i class="fa-solid fa-building-columns me-1 text-primary"></i> <?=htmlspecialchars($uu['nomcen'] ?? 'Sin centro');?></span>
                                    </div>
                                    <div class="d-flex flex-wrap gap-1">
                                        <?php foreach ($uu['areas'] as $ar) {
                                            $eti = '#' . $ar['idare'] . ' ' . $ar['nomare'] . ($ar['programas'] > 0 ? ' (' . $ar['programas'] . ' prog.)' : '');
                                            if ($isAdmin) { ?>
                                                <a class="dash-chip" href="home.php?pg=<?=$pg;?>&idare=<?=$ar['idare'];?>&opera=edi" title="Editar área">
                                                    <i class="fa-solid fa-sitemap"></i> <?=htmlspecialchars($eti);?>
                                                </a>
                                            <?php } else { ?>
                                                <span class="dash-chip"><i class="fa-solid fa-sitemap"></i> <?=htmlspecialchars($eti);?></span>
                                            <?php }
                                        } ?>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer bg-white border-0 pt-0 pb-3 px-3">
                                <hr class="mt-0 mb-3 text-muted opacity-25">
                                <div class="vid-actions-bar">
                                    <button type="button" class="btn btn-outline-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modU<?=$idu;?>" title="Ver">
                                        <i class="fa-solid fa-eye"></i> <span class="d-none d-sm-inline">Ver</span>
                                    </button>
                                    <?php if ($isAdmin): ?>
                                    <a href="home.php?pg=<?=$pg;?>" class="btn btn-outline-warning text-dark shadow-sm w-100" title="Asignar otra área a este usuario" onclick="return asignarUsuario(<?=$idu;?>);">
                                        <i class="fa-solid fa-plus"></i> <span class="d-none d-sm-inline">Asignar área</span>
                                    </a>
                                    <?php else: ?>
                                    <span class="btn btn-outline-secondary disabled w-100" style="opacity:0.6;">Solo lectura</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="modal fade" id="modU<?=$idu;?>" tabindex="-1" aria-labelledby="modULabel<?=$idu;?>" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                <div class="modal-content shadow-lg border-0" style="border-radius: 16px; overflow: hidden;">
                                    <div class="modal-header bg-dark text-white border-0 px-4 py-3">
                                        <h5 class="modal-title fs-5 d-flex align-items-center gap-2" id="modULabel<?=$idu;?>">
                                            <i class="fa-solid fa-user-tie text-success"></i>
                                            <span class="text-truncate" style="max-width: 650px;"><?=htmlspecialchars($uu['nomusu'] ?? '');?></span>
                                        </h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <strong>Usuario ID:</strong> <?=$idu;?><br>
                                                <strong>Nombre:</strong> <?=htmlspecialchars($uu['nomusu'] ?? '');?><br>
                                                <strong>Estado:</strong> <?= $inactivo ? 'Inactivo' : 'Activo';?>
                                            </div>
                                            <div class="col-md-6">
                                                <strong>Perfil:</strong> <?=htmlspecialchars($uu['nomper'] ?? 'Sin perfil');?><br>
                                                <strong>Centro:</strong> <?=htmlspecialchars($uu['nomcen'] ?? 'Sin centro');?><br>
                                                <strong>Áreas a cargo:</strong> <?=$uu['tot'];?> — <?=$cargaTxt;?> (<?=$uu['prog'];?> programas)
                                            </div>
                                            <div class="col-12">
                                                <strong>Áreas asignadas:</strong>
                                                <ul class="mb-0 mt-2">
                                                    <?php foreach ($uu['areas'] as $ar) { ?>
                                                        <li>
                                                            #<?=$ar['idare'];?> — <?=htmlspecialchars($ar['nomare']);?>
                                                            <span class="text-muted">(<?=$ar['programas'];?> programas)</span>
                                                            <?php if ($isAdmin): ?>
                                                                <a class="ms-2" href="home.php?pg=<?=$pg;?>&idare=<?=$ar['idare'];?>&opera=edi" title="Editar área">Editar</a>
                                                            <?php endif; ?>
                                                        </li>
                                                    <?php } ?>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer bg-light border-0 px-4 py-3 d-flex justify-content-between align-items-center">
                                        <div class="text-muted small">
                                            <i class="fa-solid fa-hashtag me-1"></i>#<?=$idu;?> | <?=htmlspecialchars($uu['nomper'] ?? 'Sin perfil');?>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <?php if ($isAdmin): ?>
                                            <button type="button" class="btn btn-warning btn-sm" onclick="asignarUsuario(<?=$idu;?>);" data-bs-dismiss="modal">
                                                <i class="fa-solid fa-plus me-1"></i> Asignar área
                                            </button>
                                            <?php endif; ?>
                                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                <?php }} ?>
            </tbody>
            <tfoot><tr><th>Usuarios asignados</th></tr></tfoot>
        </table>
    </div>

    <!-- Usuarios sin área (candidatos a responsable) -->
    <div class="tab-pane fade" id="usuariosin" role="tabpanel" aria-labelledby="usuariosin-tab">
        <table id="tblUsuSin" class="table table-striped table-vid-cards" style="width:100%">
            <thead><tr><th>Usuarios sin área<?= $hayMasUsu ? ' (mostrando ' . count($datUsuSin) . ' de ' . $totUsuSin . ')' : ''; ?></th></tr></thead>
            <tbody>
                <?php if($datUsuSin){ foreach ($datUsuSin as $du) {
                    $idu = $du['idusu'];
                ?>
                <tr data-order="<?=$idu;?>">
                    <td data-order="<?=$idu;?>"
                        data-search="<?=$idu;?> <?=htmlspecialchars($du['nomusu'] ?? '');?> <?=htmlspecialchars($du['nomper'] ?? '');?> <?=htmlspecialchars($du['nomcen'] ?? '');?>"
                        data-export="Usuario #<?=$idu;?> - <?=htmlspecialchars($du['nomusu'] ?? '');?> | Perfil: <?=htmlspecialchars($du['nomper'] ?? 'N/A');?> | Áreas a cargo: 0 (candidato)">
                        <div class="card h-100 shadow-sm border-0 vid-card">
                            <div class="vid-preview-box" style="filter: grayscale(0.2);">
                                <div style="display:flex;align-items:center;justify-content:center;width:100%;height:100%;background: linear-gradient(135deg, #334155 0%, #0f172a 100%);color:#fff;">
                                    <i class="fa-solid fa-user-clock" style="font-size:48px;opacity:0.85;"></i>
                                </div>
                                <div class="vid-badges-top">
                                    <span class="badge bg-warning text-dark shadow-sm px-2 py-1">
                                        <i class="fa-solid fa-user-plus me-1"></i> Candidato
                                    </span>
                                    <span class="badge vid-badge-blur px-2 py-1 shadow-sm">
                                        <i class="fa-solid fa-hashtag me-1 text-info"></i> #<?=$idu;?>
                                    </span>
                                </div>
                                <div class="vid-play-btn" data-bs-toggle="modal" data-bs-target="#modUsin<?=$idu;?>" title="Ver detalle" role="button">
                                    <i class="fa-solid fa-eye"></i>
                                </div>
                                <div class="vid-badges-bottom">
                                    <span class="badge vid-badge-blur px-2 py-1">
                                        <i class="fa-solid fa-id-badge me-1 text-primary"></i> <?=htmlspecialchars($du['nomper'] ?? 'Sin perfil');?>
                                    </span>
                                    <span class="badge vid-badge-blur px-2 py-1">
                                        <i class="fa-solid fa-circle-info me-1 text-warning"></i> Sin área
                                    </span>
                                </div>
                            </div>
                            <div class="card-body p-3 d-flex flex-column justify-content-between">
                                <div>
                                    <h6 class="fw-bold text-dark mb-2 text-truncate" title="<?=htmlspecialchars($du['nomusu'] ?? '');?>" style="font-size: 1.05rem;">
                                        <span class="text-secondary">#<?=$idu;?></span> - <?=htmlspecialchars($du['nomusu'] ?? '');?>
                                    </h6>
                                    <div class="p-2 mb-3 rounded-3 bg-light border border-light-subtle d-flex justify-content-between align-items-center" style="font-size: 0.8rem;">
                                        <div>
                                            <span class="text-muted d-block" style="font-size: 0.72rem;">Perfil</span>
                                            <span class="fw-bold text-dark">
                                                <i class="fa-solid fa-id-badge me-1"></i><?=htmlspecialchars($du['nomper'] ?? 'Sin perfil');?>
                                            </span>
                                        </div>
                                        <div class="text-muted"><i class="fa-solid fa-arrow-right-long mx-1"></i></div>
                                        <div class="text-end">
                                            <span class="text-muted d-block" style="font-size: 0.72rem;">Áreas</span>
                                            <span class="fw-bold text-secondary">0</span>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between text-muted mb-1" style="font-size: 0.78rem;">
                                        <span><i class="fa-solid fa-building-columns me-1 text-primary"></i> <?=htmlspecialchars($du['nomcen'] ?? 'Sin centro');?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer bg-white border-0 pt-0 pb-3 px-3">
                                <hr class="mt-0 mb-3 text-muted opacity-25">
                                <div class="vid-actions-bar">
                                    <button type="button" class="btn btn-outline-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modUsin<?=$idu;?>" title="Ver">
                                        <i class="fa-solid fa-eye"></i> <span class="d-none d-sm-inline">Ver</span>
                                    </button>
                                    <?php if ($isAdmin): ?>
                                    <a href="home.php?pg=<?=$pg;?>" class="btn btn-outline-warning text-dark shadow-sm w-100" title="Asignar un área a este usuario" onclick="return asignarUsuario(<?=$idu;?>);">
                                        <i class="fa-solid fa-plus"></i> <span class="d-none d-sm-inline">Asignar área</span>
                                    </a>
                                    <?php else: ?>
                                    <span class="btn btn-outline-secondary disabled w-100" style="opacity:0.6;">Solo lectura</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="modal fade" id="modUsin<?=$idu;?>" tabindex="-1" aria-labelledby="modUsinLabel<?=$idu;?>" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                <div class="modal-content shadow-lg border-0" style="border-radius: 16px; overflow: hidden;">
                                    <div class="modal-header bg-dark text-white border-0 px-4 py-3">
                                        <h5 class="modal-title fs-5 d-flex align-items-center gap-2" id="modUsinLabel<?=$idu;?>">
                                            <i class="fa-solid fa-user-clock text-warning"></i>
                                            <span class="text-truncate" style="max-width: 650px;"><?=htmlspecialchars($du['nomusu'] ?? '');?></span>
                                        </h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <strong>Usuario ID:</strong> <?=$idu;?><br>
                                                <strong>Nombre:</strong> <?=htmlspecialchars($du['nomusu'] ?? '');?><br>
                                                <strong>Correo:</strong> <?=htmlspecialchars($du['emausu'] ?? 'N/A');?>
                                            </div>
                                            <div class="col-md-6">
                                                <strong>Perfil:</strong> <?=htmlspecialchars($du['nomper'] ?? 'Sin perfil');?><br>
                                                <strong>Centro:</strong> <?=htmlspecialchars($du['nomcen'] ?? 'Sin centro');?><br>
                                                <strong>Áreas a cargo:</strong> 0 — candidato a responsable
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer bg-light border-0 px-4 py-3 d-flex justify-content-between align-items-center">
                                        <div class="text-muted small">
                                            <i class="fa-solid fa-hashtag me-1"></i>#<?=$idu;?> | <?=htmlspecialchars($du['nomper'] ?? 'Sin perfil');?>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <?php if ($isAdmin): ?>
                                            <a href="home.php?pg=<?=$pg;?>" class="btn btn-warning btn-sm" onclick="return asignarUsuario(<?=$idu;?>);">
                                                <i class="fa-solid fa-plus me-1"></i> Asignar área
                                            </a>
                                            <?php endif; ?>
                                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                <?php }} ?>
            </tbody>
            <tfoot><tr><th>Usuarios sin área</th></tr></tfoot>
        </table>
    </div>
</div>

<!-- ===========================================================================
     Lógica JavaScript del dashboard
     Implementa las funciones que el HTML ya invocaba (filterByTab, switchCardView,
     syncCustomSearch, syncCustomSort, triggerDTExport) y que NO existían en el
     proyecto, más el arranque de las 5 tablas de tarjetas con DataTables.
     ========================================================================== -->
<script>
/* ---------------------------------------------------------------------------
   IMPORTANTE: js/java.js y js/valida.js inicializan por su cuenta #myt y
   #example al cargar la página. Cuando una tabla ya está inicializada,
   DataTables responde con un alert() modal ("Cannot reinitialise DataTable")
   que BLOQUEA toda la página. Aquí se cambia ese modo de error por un aviso en
   consola: el dashboard se queda con su propia configuración y la página nunca
   se bloquea.
   -------------------------------------------------------------------------- */
if (window.jQuery && jQuery.fn.dataTable && jQuery.fn.dataTable.ext) {
    jQuery.fn.dataTable.ext.errMode = function (settings, tn, msg) {
        if (window.console && console.warn) { console.warn(msg); }
    };
}

/* Pestaña -> tabla del dashboard */
var DASH_TABLAS = {
    '#activos': 'example',
    '#inactivos': 'myt',
    '#todos': 'tblTodos',
    '#usuarios': 'tblUsu',
    '#usuariosin': 'tblUsuSin'
};
var dashApi = {};            /* APIs de DataTables por tabla */
var dashActiva = 'example';  /* tabla visible actualmente */

/* Título usado al exportar cada catálogo */
function dashTitulo(id) {
    var titulos = {
        example: 'Áreas asignadas',
        myt: 'Áreas sin asignar',
        tblTodos: 'Todas las áreas',
        tblUsu: 'Usuarios con áreas asignadas',
        tblUsuSin: 'Usuarios sin área asignada'
    };
    return titulos[id] || 'Dashboard Área';
}

/* Configuración común de las tablas de tarjetas */
function dtOptions(id) {
    return {
        order: [[0, 'asc']],
        dom: '<"dash-dt-hidden"B>lrtip',
        pageLength: 12,
        lengthMenu: [[12, 24, 48, -1], [12, 24, 48, 'Todos']],
        language: {
            lengthMenu: 'Mostrar _MENU_ tarjetas',
            zeroRecords: 'No se encontraron resultados',
            info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
            infoEmpty: 'Mostrando 0 registros',
            infoFiltered: '(filtrado de _MAX_ registros)',
            search: 'Buscar:',
            processing: 'Procesando...',
            loadingRecords: 'Cargando...',
            paginate: { first: 'Primero', last: 'Último', next: 'Siguiente', previous: 'Anterior' }
        },
        buttons: [
            { extend: 'copyHtml5',  text: 'Copiar', titleAttr: 'Copiar al portapapeles', className: 'd-none' },
            { extend: 'csvHtml5',   text: 'CSV',    titleAttr: 'Descargar CSV',            className: 'd-none', title: dashTitulo(id) },
            { extend: 'excelHtml5', text: 'Excel',  titleAttr: 'Exportar a Excel',        className: 'd-none', title: dashTitulo(id) }
        ],
        /* Al exportar se usa el resumen textual guardado en data-export
           (la tarjeta HTML no sirve como dato de exportación). */
        exportOptions: {
            format: {
                body: function (data, fila, col, nodo) {
                    var txt = (nodo && nodo.getAttribute) ? nodo.getAttribute('data-export') : null;
                    if (txt) { return txt; }
                    return $.trim($('<div>').html(data).text()).replace(/\s+/g, ' ');
                }
            }
        }
    };
}

/* Inicializa las 5 tablas. #example y #myt YA venían inicializadas por
   js/valida.js y js/java.js con la configuración antigua (botones de exportar
   y buscador propios, sin tarjetas), por eso se destruyen y se vuelven a crear
   con la configuración del dashboard. */
var dashIniciado = false;

function dashInit() {
    if (dashIniciado) { return; }
    dashIniciado = true;

    $.each(DASH_TABLAS, function (pestana, id) {
        var $t = $('#' + id);
        if (!$t.length) { return; }
        if ($.fn.DataTable.isDataTable($t)) { $t.DataTable().destroy(); }
        dashApi[id] = $t.DataTable(dtOptions(id));
    });
    dashActivar(DASH_TABLAS['#activos']);
}

/* Ajusta la tabla visible y vuelve a aplicar el texto del buscador */
function dashActivar(id) {
    if (!id || !dashApi[id]) { return; }
    dashActiva = id;
    setTimeout(function () {
        var api = dashApi[id];
        if (!api) { return; }
        api.columns.adjust();
        var caja = document.getElementById('customVideoSearch');
        if (caja) { api.search(caja.value).draw(); }
    }, 80);
}

/* Pestañas: se llama desde el onclick de cada botón.
   El cambio de panel se hace aquí para no depender de la versión de Bootstrap
   (la 5.0.1 exige que el tablist tenga la clase .nav); Bootstrap reacciona
   igualmente al data-bs-toggle="tab" del botón. */
function filterByTab(pestana) {
    var boton = document.getElementById(pestana + '-tab');
    var panel = document.getElementById(pestana);

    if (boton && panel) {
        $('#tabVideos .btn-filter-pill').removeClass('active').attr('aria-selected', 'false');
        $(boton).addClass('active').attr('aria-selected', 'true');
        $('#tabVideosContent > .tab-pane').removeClass('show active');
        $(panel).addClass('show active');
    }

    dashActivar(DASH_TABLAS['#' + pestana]);
    return true;
}

/* Vista cuadrícula / lista */
function switchCardView(modo) {
    var lista = (modo === 'list');
    $('.table-vid-cards').toggleClass('is-list-view', lista);
    $('#btnViewGrid').toggleClass('active', !lista);
    $('#btnViewList').toggleClass('active', lista);
    setTimeout(function () { if (dashApi[dashActiva]) { dashApi[dashActiva].columns.adjust(); } }, 60);
    return false;
}

/* Buscador propio de la barra de herramientas */
function syncCustomSearch(valor) {
    if (dashApi[dashActiva]) { dashApi[dashActiva].search(valor).draw(); }
    return true;
}

/* Orden ascendente / descendente por el id (primera columna) */
function syncCustomSort(valor) {
    if (dashApi[dashActiva]) {
        dashApi[dashActiva].order([[0, (valor === 'desc') ? 'desc' : 'asc']]).draw();
    }
    return true;
}

/* Exportar (copiar / CSV / Excel) la tabla visible */
function triggerDTExport(tipo) {
    var idx = { copy: 0, csv: 1, excel: 2 }[tipo];
    var api = dashApi[dashActiva];
    if (idx === undefined || !api) { return false; }
    try {
        api.button(idx).trigger();
    } catch (e) {
        if (window.console) { console.warn('Exportación no disponible: ' + tipo); }
    }
    return false;
}

/* Preselecciona el usuario en el formulario del área para asignarle un área */
function asignarUsuario(idusu) {
    var sel = document.getElementById('idusu');
    if (!sel) { return false; }

    if ($(sel).find('option[value="' + idusu + '"]').length === 0) {
        var aviso = 'El usuario #' + idusu + ' no está disponible en la lista de usuarios activos.';
        if (window.Swal) {
            Swal.fire({ icon: 'info', title: 'Asignación', text: aviso, confirmButtonColor: '#00af00' });
        } else {
            alert(aviso);
        }
        return false;
    }

    sel.value = idusu;

    var form = document.getElementById('frmins');
    if (form) {
        form.style.display = 'inherit';
        var mas = document.getElementById('mas');
        var menos = document.getElementById('menos');
        if (mas) { mas.style.display = 'none'; }
        if (menos) { menos.style.display = 'inline-block'; }

        $('html, body').animate({ scrollTop: $('#frmins').offset().top - 90 }, 350);
        var nom = document.getElementById('nomare');
        if (nom) { nom.focus(); }
    }
    return false;
}

/* Arranque del dashboard.
   1) Un instante después de "document ready" (cuando ya corrieron los $(ready)
      de js/java.js y js/valida.js) se reemplazan las tablas por las del dashboard.
   2) window.load queda como red de seguridad, sin repetir la inicialización. */
$(function () { setTimeout(dashInit, 0); });
$(window).on('load', dashInit);

/* Si se cambia de pestaña con el teclado, se ajusta igualmente la tabla */
$(function () {
    $(document).on('shown.bs.tab', '.btn-filter-pill', function () {
        var destino = $(this).attr('data-bs-target');
        if (destino && DASH_TABLAS[destino]) { dashActivar(DASH_TABLAS[destino]); }
    });
});
</script>
