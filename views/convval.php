<?php require_once ('controllers/concval.php');

$domMap = [];
if (!empty($datDom)) {
    foreach ($datDom as $dd) {
        $domMap[$dd['iddom']] = $dd['nomdom'];
    }
}
?>

<!-- Dashboard -->
<div class="conte">
    <?php echo titulo2("<i class='".($icono ?? 'fa-solid fa-file')."'></i> Valor",1); ?>

    <div class="row my-4 g-4">
        <!-- Total Valores -->
        <div class="col-12 col-md-4">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #3e4a3d;">Total en Sistema</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #0b1c30;"><?= $stats['total'] ?? count($datAll ?? []); ?></span>
                            <span style="font-size: 13px; color: #3e4a3d;">valores</span>
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

        <!-- Activos -->
        <div class="col-12 col-md-4">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #006b29;">Activos</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #006b29;"><?= $stats['activos'] ?? 0; ?></span>
                            <span style="font-size: 13px; color: rgba(0, 107, 41, 0.8);">en uso</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box" style="background-color: #7afd88; color: #006b24;">
                        <i class="fa-solid fa-circle-check fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="fw-semibold d-inline-flex align-items-center gap-2" style="font-size: 11px; color: #006b29;">
                        <span class="pulse-dot"></span> Habilitados
                    </span>
                    <span style="font-size: 11px; color: #3e4a3d;">Operativos</span>
                </div>
            </div>
        </div>

        <!-- Inactivos -->
        <div class="col-12 col-md-4">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #ba1a1a;">Inactivos</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #0b1c30;"><?= $stats['inactivos'] ?? 0; ?></span>
                            <span style="font-size: 13px; color: #3e4a3d;">deshabilitados</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box" style="background-color: #ffdad6; color: #ba1a1a;">
                        <i class="fa-solid fa-circle-pause fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="d-inline-flex align-items-center gap-1" style="font-size: 11px; color: #3e4a3d;">
                        <i class="fa-solid fa-clock-rotate-left" style="font-size: 12px;"></i> Fuera de uso
                    </span>
                    <span class="fw-semibold" style="font-size: 11px; color: #3c627f;">Pausados</span>
                </div>
            </div>
        </div>
    </div>

    <div class="inser">
        <form id="frmins" class="filter-toolbar-container" action="home.php?pg=<?= $pg; ?>" method="POST">
            <h2><i class="fa-solid fa-list-check"></i> <?php if ($datOne) echo "Actualizar valor"; else echo "Nuevo valor"; ?></h2>
            <div class="row">
                <div class="form-group col-md-6">
                    <label for="nomval" class="fw-semibold">Nombre</label>
                    <input type="text" name="nomval" id="nomval" maxlength="70" class="form-control" required value="<?php if ($datOne) echo htmlspecialchars($datOne[0]['nomval']); ?>">
                </div>
                <div class="form-group col-md-6">
                    <label for="iddom" class="fw-semibold">Dominio</label>
                    <select name="iddom" id="iddom" class="form-select" required>
                        <option value="">Seleccione un dominio...</option>
                        <?php if ($datDom) { foreach ($datDom as $ddo) { ?>
                            <option value="<?= $ddo['iddom']; ?>" <?php if ($datOne && $datOne[0]['iddom'] == $ddo['iddom']) echo "selected"; ?>><?= htmlspecialchars($ddo['nomdom']); ?></option>
                        <?php }} ?>
                    </select>
                </div>
                <div class="form-group col-md-6">
                    <label for="parval" class="fw-semibold">Parámetros</label>
                    <input type="text" name="parval" id="parval" class="form-control" value="<?php if ($datOne) echo htmlspecialchars($datOne[0]['parval'] ?? ''); ?>">
                </div>
                <div class="form-group col-md-6">
                    <label for="act" class="fw-semibold">Activo</label>
                    <select name="act" id="act" class="form-select">
                        <option value="1" <?php if (!$datOne || ($datOne && $datOne[0]['act'] == 1)) echo "selected"; ?>>Sí</option>
                        <option value="2" <?php if ($datOne && $datOne[0]['act'] == 2) echo "selected"; ?>>No</option>
                    </select>
                </div>
                <div class="form-group col-md-6 d-flex align-items-end">
                    <?php if ($datOne) { ?>
                        <a href="home.php?pg=<?= $pg; ?>" class="btn btn-outline-secondary me-2">Cancelar</a>
                    <?php } ?>
                    <input type="submit" class="btn btn-success" value="<?php if ($datOne) echo "Actualizar"; else echo "Registrar"; ?>">
                    <input type="hidden" name="opera" value="save">
                    <input type="hidden" name="idval" id="idval" value="<?php if ($datOne) echo $datOne[0]['idval']; ?>">
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Toolbar de Control y Filtros -->
<div class="filter-toolbar-container">
    <!-- Izquierda: Píldoras de Filtro de Estado y Exportación -->
    <div class="toolbar-left-group">
        <div class="filter-pills-group" id="tabValores" role="tablist">
            <button class="btn-filter-pill active" id="valores-tab" data-bs-toggle="tab" data-bs-target="#valores" type="button" role="tab" aria-controls="valores" aria-selected="true" onclick="filterByTab('valores')">
                <span class="pill-dot" style="background-color: #3c627f;"></span>
                <span>Valores</span>
                <span class="pill-count"><?= count($datAll ?? []); ?></span>
            </button>
        </div>

        <!-- Separador -->
        <div class="toolbar-divider d-none d-sm-block"></div>

        <!-- Botones de Exportación integrados -->
        <div class="toolbar-export-buttons">
            <button type="button" class="toolbar-export-btn" title="Copiar al portapapeles" onclick="triggerValExport('copy')">
                <i class="fa-solid fa-copy"></i>
            </button>
            <button type="button" class="toolbar-export-btn" title="Descargar CSV" onclick="triggerValExport('csv')">
                <i class="fa-solid fa-file-csv"></i>
            </button>
            <button type="button" class="toolbar-export-btn" title="Exportar a Excel" onclick="triggerValExport('excel')">
                <i class="fa-solid fa-file-excel"></i>
            </button>
        </div>
    </div>

    <!-- Derecha: Búsqueda, Ordenamiento y Vista Grid/Lista -->
    <div class="toolbar-search-sort-group">
        <!-- Input de búsqueda -->
        <div class="toolbar-search-box">
            <i class="fa-solid fa-magnifying-glass icoldiz"></i>
            <input type="text" id="customValSearch" placeholder="Buscar valor..." onkeyup="syncValSearch(this.value)">
        </div>

        <!-- Controles Secundarios: Selector de Orden y Conmutador de Vistas -->
        <div class="toolbar-secondary-controls">
            <!-- Selector de ordenamiento -->
            <select class="toolbar-select-sort" id="customValSort" onchange="syncValSort(this.value)">
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

<div class="tab-content" id="tabValoresContent">
    <div class="tab-pane fade show active" id="valores" role="tabpanel" aria-labelledby="valores-tab">
        <table id="example" class="table table-striped table-vid-cards" style="width:100%">
            <thead>
                <tr>
                    <th>Valores</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($datAll) {
                    foreach ($datAll as $dta) {
                        $nomDom = $domMap[$dta['iddom']] ?? 'Sin Dominio';
                ?>
                <tr data-order="<?= $dta['idval']; ?>">
                    <td data-search="<?= $dta['idval'] . ' ' . $dta['nomval'] . ' ' . $nomDom . ' ' . ($dta['parval'] ?? ''); ?>">

                        <!-- Tarjeta de Valor -->
                        <div class="card h-100 shadow-sm border-0 vid-card">

                            <!-- Cuerpo de la Tarjeta -->
                            <div class="card-body p-3 d-flex flex-column justify-content-between">
                                <div>
                                    <!-- Icono principal -->
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <div class="d-flex align-items-center justify-content-center rounded-3" style="width:38px;height:38px;background:#f1f5f9;">
                                            <i class="fa-solid fa-tag text-secondary"></i>
                                        </div>
                                        <!-- Título del valor con ID -->
                                        <h6 class="fw-bold text-dark mb-0 text-truncate flex-grow-1" title="<?= $dta['idval']; ?> - <?= htmlspecialchars($dta['nomval']); ?>" style="font-size: 1.05rem;">
                                            <span class="text-primary">#<?= $dta['idval']; ?></span> - <?= htmlspecialchars($dta['nomval']); ?>
                                        </h6>
                                        <?php if ($dta['act'] == 1) { ?>
                                            <span class="badge bg-success" title="Activo"><i class="fa-solid fa-circle-check me-1"></i>Activo</span>
                                        <?php } else { ?>
                                            <span class="badge bg-danger" title="Inactivo"><i class="fa-solid fa-circle-xmark me-1"></i>Inactivo</span>
                                        <?php } ?>
                                    </div>

                                    <!-- Caja Dominio / Parámetros -->
                                    <div class="p-2 mb-3 rounded-3 bg-light border border-light-subtle d-flex justify-content-between align-items-center" style="font-size: 0.8rem;">
                                        <div>
                                            <span class="text-muted d-block" style="font-size: 0.72rem;">Dominio</span>
                                            <span class="fw-bold text-success">
                                                <i class="fa-solid fa-folder me-1"></i><?= htmlspecialchars($nomDom); ?>
                                            </span>
                                        </div>
                                        <div class="text-muted"><i class="fa-solid fa-arrow-right-long mx-1"></i></div>
                                        <div class="text-end" style="max-width: 55%;">
                                            <span class="text-muted d-block" style="font-size: 0.72rem;">Parámetros</span>
                                            <span class="fw-bold text-primary text-truncate d-block" title="<?= htmlspecialchars($dta['parval'] ?? ''); ?>">
                                                <i class="fa-solid fa-sliders me-1"></i><?= !empty($dta['parval']) ? htmlspecialchars($dta['parval']) : 'N/A'; ?>
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Metadatos -->
                                    <div class="d-flex justify-content-between text-muted mb-1" style="font-size: 0.78rem;">
                                        <span><i class="fa-solid fa-hashtag text-primary me-1"></i> <strong>ID Dominio:</strong> <?= $dta['iddom']; ?></span>
                                        <span><i class="fa-solid fa-toggle-on text-secondary me-1"></i> <strong>Estado:</strong> <?= ($dta['act'] == 1) ? 'Sí' : 'No'; ?></span>
                                    </div>
                                    <?php if (!empty($dta['parval'])) { ?>
                                    <div class="text-muted mt-2 p-2 rounded bg-white border" style="font-size: 0.78rem;">
                                        <i class="fa-solid fa-align-left text-secondary me-1"></i> <?= htmlspecialchars($dta['parval']); ?>
                                    </div>
                                    <?php } ?>
                                </div>
                            </div>

                            <!-- Footer con Botones de Acción -->
                            <div class="card-footer bg-white border-0 pt-0 pb-3 px-3">
                                <hr class="mt-0 mb-3 text-muted opacity-25">
                                <div class="vid-actions-bar" style="grid-template-columns: 1fr 1fr 1fr;">
                                    <!-- Botón Toggle Activo -->
                                    <?php if ($dta['act'] == 1) { ?>
                                        <a href="home.php?pg=<?= $pg; ?>&idval=<?= $dta['idval']; ?>&opera=acti&act=2" class="btn btn-outline-success shadow-sm" title="Desactivar">
                                            <i class="fa-solid fa-toggle-on"></i> <span class="d-none d-sm-inline">Desact.</span>
                                        </a>
                                    <?php } else { ?>
                                        <a href="home.php?pg=<?= $pg; ?>&idval=<?= $dta['idval']; ?>&opera=acti&act=1" class="btn btn-outline-secondary shadow-sm" title="Activar">
                                            <i class="fa-solid fa-toggle-off"></i> <span class="d-none d-sm-inline">Activar</span>
                                        </a>
                                    <?php } ?>

                                    <!-- Botón Editar -->
                                    <a href="home.php?pg=<?= $pg; ?>&idval=<?= $dta['idval']; ?>&opera=edi" class="btn btn-outline-warning text-dark shadow-sm" title="Editar">
                                        <i class="fa-solid fa-pen-to-square"></i> <span class="d-none d-sm-inline">Editar</span>
                                    </a>

                                    <!-- Botón Eliminar -->
                                    <a href="home.php?pg=<?= $pg; ?>&idval=<?= $dta['idval']; ?>&opera=eli" class="btn btn-outline-danger shadow-sm" title="Eliminar" onclick="return confirm('¿Está seguro de eliminar este valor?');">
                                        <i class="fa-solid fa-trash-can"></i> <span class="d-none d-sm-inline">Eliminar</span>
                                    </a>
                                </div>
                            </div>
                        </div>

                    </td>
                </tr>
                <?php
                    }
                }
                ?>
            </tbody>
            <tfoot>
                <tr>
                    <th>Valores</th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<div style="margin-bottom: 150px"></div>

<script>
// Compatibilidad con valida.js (reutiliza lógica exacta de convpag)
window.syncValSearch = function(query) {
    if (typeof window.syncCustomSearch === 'function') {
        window.syncCustomSearch(query);
    } else if (typeof $.fn.DataTable !== 'undefined') {
        $('.table-vid-cards').each(function () {
            if ($.fn.DataTable.isDataTable(this)) $(this).DataTable().search(query).draw();
        });
        if ($.fn.DataTable.isDataTable('#example')) $('#example').DataTable().search(query).draw();
    }
};
window.syncValSort = function(orderDir) {
    if (typeof window.syncCustomSort === 'function') {
        window.syncCustomSort(orderDir);
    } else if (typeof $.fn.DataTable !== 'undefined') {
        $('.table-vid-cards').each(function () {
            if ($.fn.DataTable.isDataTable(this)) $(this).DataTable().order([0, orderDir]).draw();
        });
    }
};
window.triggerValExport = function(type) {
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
// Listener adicional para input valores (valida.js ya escucha #customVideoSearch, aquí para #customValSearch)
$(document).ready(function(){
    $(document).on('input keyup change paste', '#customValSearch', function () {
        window.syncValSearch(this.value);
    });
    // Restaurar vista grid/list guardada (misma key que convpag para coherencia)
    const savedView = localStorage.getItem('sagip_vid_view');
    if (savedView === 'list') {
        if (typeof window.switchCardView === 'function') window.switchCardView('list');
    }
});
</script>
