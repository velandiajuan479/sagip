<?php require_once ('controllers/comchtu.php'); ?>
<?php
$pg = $_REQUEST['pg'] ?? $pg ?? '2005';
$icono = $icono ?? 'fa-solid fa-file-lines';
$totAsig = count($datAll ?? []);
$totInst = count($datUsuarios ?? []);
$totHoj = count($datHojas ?? []);
?>

<!-- Dashboard -->
<div class="conte">
    <?php echo titulo2("<i class='".($icono ?? 'fa-solid fa-file-lines')."'></i> Hoja por usuario",1); ?>

    <?php if (!empty($success)) { ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($success); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php } ?>
    <?php if (!empty($error)) { ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php } ?>

    <div class="row my-4 g-4">
        <!-- Total Asignaciones -->
        <div class="col-12 col-md-4">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #3e4a3d;">Total en Sistema</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #0b1c30;"><?= $totAsig ?? count($datAll ?? []); ?></span>
                            <span style="font-size: 13px; color: #3e4a3d;">asignaciones</span>
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
                    <span style="font-size: 11px; color: #3e4a3d;">Disponibles: 100%</span>
                </div>
            </div>
        </div>

        <!-- Instructores -->
        <div class="col-12 col-md-4">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #006b29;">Instructores</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #006b29;"><?= $totInst ?? 0; ?></span>
                            <span style="font-size: 13px; color: rgba(0, 107, 41, 0.8);">registrados</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box" style="background-color: #7afd88; color: #006b24;">
                        <i class="fa-solid fa-users fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="fw-semibold d-inline-flex align-items-center gap-2" style="font-size: 11px; color: #006b29;">
                        <span class="pulse-dot"></span> Disponibles
                    </span>
                    <span style="font-size: 11px; color: #3e4a3d;">Plataforma</span>
                </div>
            </div>
        </div>

        <!-- Fichas / Hojas -->
        <div class="col-12 col-md-4">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #ba1a1a;">Fichas / Hojas</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #0b1c30;"><?= $totHoj ?? 0; ?></span>
                            <span style="font-size: 13px; color: #3e4a3d;">disponibles</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box" style="background-color: #ffdad6; color: #ba1a1a;">
                        <i class="fa-solid fa-file-lines fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="d-inline-flex align-items-center gap-1" style="font-size: 11px; color: #3e4a3d;">
                        <i class="fa-solid fa-clock-rotate-left" style="font-size: 12px;"></i> Activas
                    </span>
                    <span class="fw-semibold" style="font-size: 11px; color: #3c627f;">Cronograma</span>
                </div>
            </div>
        </div>
    </div>

    <div class="inser">
        <form id="frmins" class="filter-toolbar-container" action="home.php?pg=<?= $pg; ?>" method="POST">
            <h2><i class="fa-solid fa-list-check"></i> Asignar instructor a ficha</h2>
            <div class="row">
                <div class="form-group col-md-6">
                    <label for="idnorad" class="fw-semibold">Ficha / Hoja de trabajo</label>
                    <select name="idnorad" id="idnorad" class="form-select" required>
                        <option value="">Seleccione una ficha...</option>
                        <?php if ($datHojas) { foreach ($datHojas as $dh) { ?>
                            <option value="<?= htmlspecialchars($dh['idnorad']); ?>"><?= htmlspecialchars("Ficha " . $dh['idnorad'] . " - " . $dh['nompro']); ?></option>
                        <?php }} ?>
                    </select>
                </div>
                <div class="form-group col-md-6">
                    <label for="idusu" class="fw-semibold">Instructor (Usuario)</label>
                    <select name="idusu" id="idusu" class="form-select" required>
                        <option value="">Seleccione un instructor...</option>
                        <?php if ($datUsuarios) { foreach ($datUsuarios as $du) { ?>
                            <option value="<?= htmlspecialchars($du['idusu']); ?>"><?= htmlspecialchars($du['nomusu']); ?></option>
                        <?php }} ?>
                    </select>
                </div>
                <div class="form-group col-md-6 d-flex align-items-end">
                    <input type="submit" class="btn btn-success" value="Asignar">
                    <input type="hidden" name="opera" value="save">
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Toolbar de Control y Filtros -->
<div class="filter-toolbar-container">
    <!-- Izquierda: Píldoras de Filtro de Estado y Exportación -->
    <div class="toolbar-left-group">
        <div class="filter-pills-group" id="tabHojas" role="tablist">
            <button class="btn-filter-pill active" id="hojas-tab" data-bs-toggle="tab" data-bs-target="#hojas" type="button" role="tab" aria-controls="hojas" aria-selected="true" onclick="filterByTab('hojas')">
                <span class="pill-dot" style="background-color: #3c627f;"></span>
                <span>Asignaciones</span>
                <span class="pill-count"><?= count($datAll ?? []); ?></span>
            </button>
        </div>

        <!-- Separador -->
        <div class="toolbar-divider d-none d-sm-block"></div>

        <!-- Botones de Exportación integrados -->
        <div class="toolbar-export-buttons">
            <button type="button" class="toolbar-export-btn" title="Copiar al portapapeles" onclick="triggerHtuExport('copy')">
                <i class="fa-solid fa-copy"></i>
            </button>
            <button type="button" class="toolbar-export-btn" title="Descargar CSV" onclick="triggerHtuExport('csv')">
                <i class="fa-solid fa-file-csv"></i>
            </button>
            <button type="button" class="toolbar-export-btn" title="Exportar a Excel" onclick="triggerHtuExport('excel')">
                <i class="fa-solid fa-file-excel"></i>
            </button>
        </div>
    </div>

    <!-- Derecha: Búsqueda, Ordenamiento y Vista Grid/Lista -->
    <div class="toolbar-search-sort-group">
        <!-- Input de búsqueda -->
        <div class="toolbar-search-box">
            <i class="fa-solid fa-magnifying-glass icoldiz"></i>
            <input type="text" id="customHtuSearch" placeholder="Buscar asignación..." onkeyup="syncHtuSearch(this.value)">
        </div>

        <!-- Controles Secundarios: Selector de Orden y Conmutador de Vistas -->
        <div class="toolbar-secondary-controls">
            <!-- Selector de ordenamiento -->
            <select class="toolbar-select-sort" id="customHtuSort" onchange="syncHtuSort(this.value)">
                <option value="asc">Ascendente</option>
                <option value="desc" selected>Descendente</option>
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

<div class="tab-content" id="tabHojasContent">
    <div class="tab-pane fade show active" id="hojas" role="tabpanel" aria-labelledby="hojas-tab">
        <table id="example" class="table table-striped table-vid-cards" style="width:100%">
            <thead>
                <tr>
                    <th>Asignaciones</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($datAll) {
                    foreach ($datAll as $dta) {
                        $keyAsig = $dta['idnorad'] . '-' . $dta['idusu'];
                ?>
                <tr data-order="<?= $keyAsig; ?>">
                    <td data-search="<?= $dta['idnorad'] . ' ' . $dta['idusu'] . ' ' . $dta['nomusu'] . ' ' . $dta['nompro'] . ' ' . $dta['cupo'] . ' ' . ($dta['feclini'] ?? '') . ' ' . ($dta['feclin'] ?? ''); ?>">

                        <!-- Tarjeta de Asignación -->
                        <div class="card h-100 shadow-sm border-0 vid-card">

                            <!-- Cuerpo de la Tarjeta -->
                            <div class="card-body p-3 d-flex flex-column justify-content-between">
                                <div>
                                    <!-- Icono principal -->
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <div class="d-flex align-items-center justify-content-center rounded-3" style="width:38px;height:38px;background:#f1f5f9;">
                                            <i class="fa-solid fa-file-lines text-secondary"></i>
                                        </div>
                                        <!-- Título de la asignación con ficha e instructor -->
                                        <h6 class="fw-bold text-dark mb-0 text-truncate flex-grow-1" title="Ficha <?= htmlspecialchars($dta['idnorad']); ?> - <?= htmlspecialchars($dta['nomusu'] ?? ''); ?>" style="font-size: 1.05rem;">
                                            <span class="text-primary">#<?= htmlspecialchars($dta['idnorad']); ?></span> - <?= htmlspecialchars($dta['nomusu'] ?? ''); ?>
                                        </h6>
                                        <span class="badge bg-success" title="Asignación activa"><i class="fa-solid fa-check me-1"></i>Asignada</span>
                                    </div>

                                    <!-- Caja Ficha / Programa -->
                                    <div class="p-2 mb-3 rounded-3 bg-light border border-light-subtle d-flex justify-content-between align-items-center" style="font-size: 0.8rem;">
                                        <div>
                                            <span class="text-muted d-block" style="font-size: 0.72rem;">Ficha</span>
                                            <span class="fw-bold text-success">
                                                <i class="fa-solid fa-cube me-1"></i><?= htmlspecialchars($dta['idnorad'] ?? '---'); ?>
                                            </span>
                                        </div>
                                        <div class="text-muted"><i class="fa-solid fa-arrow-right-long mx-1"></i></div>
                                        <div class="text-end" style="max-width: 55%;">
                                            <span class="text-muted d-block" style="font-size: 0.72rem;">Tecnólogo</span>
                                            <span class="fw-bold text-primary text-truncate d-block" title="<?= htmlspecialchars($dta['nompro'] ?? ''); ?>">
                                                <i class="fa-solid fa-link me-1"></i><?= htmlspecialchars($dta['nompro'] ?? '---'); ?>
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Metadatos -->
                                    <div class="d-flex justify-content-between text-muted mb-1" style="font-size: 0.78rem;">
                                        <span><i class="fa-solid fa-users text-primary me-1"></i> <strong>Cupo:</strong> <?= htmlspecialchars($dta['cupo'] ?? '---'); ?></span>
                                        <span><i class="fa-solid fa-clock text-secondary me-1"></i> <strong>Inicio:</strong> <?= htmlspecialchars($dta['feclini'] ?? '---'); ?></span>
                                    </div>
                                    <?php if (!empty($dta['feclin'])) { ?>
                                    <div class="text-muted mt-2 p-2 rounded bg-white border" style="font-size: 0.78rem;">
                                        <i class="fa-solid fa-calendar-days text-secondary me-1"></i> <?= htmlspecialchars($dta['feclini']); ?> / <?= htmlspecialchars($dta['feclin']); ?>
                                    </div>
                                    <?php } ?>
                                </div>
                            </div>

                            <!-- Footer con Botones de Acción -->
                            <div class="card-footer bg-white border-0 pt-0 pb-3 px-3">
                                <hr class="mt-0 mb-3 text-muted opacity-25">
                                <div class="vid-actions-bar" style="grid-template-columns: 1fr;">
                                    <!-- Botón Eliminar -->
                                    <a href="home.php?pg=<?= $pg; ?>&opera=delete&idnorad=<?= $dta['idnorad']; ?>&idusu=<?= $dta['idusu']; ?>" class="btn btn-outline-danger shadow-sm" title="Eliminar" onclick="return confirm('¿Está seguro de eliminar esta asignación?');">
                                        <i class="fa-solid fa-trash-can"></i> <span class="d-none d-sm-inline">Eliminar</span>
                                    </a>
                                </div>
                            </div>
                        </div>

                    </td>
                </tr>
                <?php
                    }
                } else {
                ?>
                <tr>
                    <td>
                        <div class="alert alert-info text-center mb-0">No hay asignaciones registradas. Seleccione una ficha y un instructor y pulse Asignar.</div>
                    </td>
                </tr>
                <?php
                }
                ?>
            </tbody>
            <tfoot>
                <tr>
                    <th>Asignaciones</th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<div style="margin-bottom: 150px"></div>

<script>
// Compatibilidad con valida.js (reutiliza lógica exacta de convpag/vvidgen/usuvpef)
window.syncHtuSearch = function(query) {
    if (typeof window.syncCustomSearch === 'function') {
        window.syncCustomSearch(query);
    } else if (typeof $.fn.DataTable !== 'undefined') {
        $('.table-vid-cards').each(function () {
            if ($.fn.DataTable.isDataTable(this)) $(this).DataTable().search(query).draw();
        });
        if ($.fn.DataTable.isDataTable('#example')) $('#example').DataTable().search(query).draw();
    }
};
window.syncHtuSort = function(orderDir) {
    if (typeof window.syncCustomSort === 'function') {
        window.syncCustomSort(orderDir);
    } else if (typeof $.fn.DataTable !== 'undefined') {
        $('.table-vid-cards').each(function () {
            if ($.fn.DataTable.isDataTable(this)) $(this).DataTable().order([0, orderDir]).draw();
        });
    }
};
window.triggerHtuExport = function(type) {
    if (typeof window.triggerDTExport === 'function') {
        // triggerDTExport ya busca .tab-pane.active .table-vid-cards
        window.triggerDTExport(type);
    } else if (typeof $.fn.DataTable !== 'undefined' && $.fn.DataTable.isDataTable('#example')) {
        const dtApi = $('#example').DataTable();
        if (type === 'copy') dtApi.button('.buttons-copy').trigger();
        else if (type === 'csv') dtApi.button('.buttons-csv').trigger();
        else if (type === 'excel') dtApi.button('.buttons-excel').trigger();
    }
};
// Listener adicional para input asignaciones (valida.js ya escucha #customVideoSearch, aquí para #customHtuSearch)
$(document).ready(function(){
    $(document).on('input keyup change paste', '#customHtuSearch', function () {
        window.syncHtuSearch(this.value);
    });
    // Restaurar vista grid/list guardada (misma key que vidgen/usuvpef/convpag para coherencia)
    const savedView = localStorage.getItem('sagip_vid_view');
    if (savedView === 'list') {
        if (typeof window.switchCardView === 'function') window.switchCardView('list');
    }
});
</script>
