<?php require_once ('controllers/concdom.php'); ?>

<!-- Dashboard -->
<div class="conte">
    <?php echo titulo2("<i class='".($icono ?? 'fa-solid fa-file')."'></i> Dominio",1); ?>

    <div class="row my-4 g-4">
        <!-- Total Dominios -->
        <div class="col-12 col-md-4">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #3e4a3d;">Total en Sistema</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #0b1c30;"><?= $stats['total_dominios'] ?? count($dat ?? []); ?></span>
                            <span style="font-size: 13px; color: #3e4a3d;">dominios</span>
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

        <!-- Con Valores -->
        <div class="col-12 col-md-4">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #006b29;">Con Valores</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #006b29;"><?= $stats['dominios_con_valores'] ?? 0; ?></span>
                            <span style="font-size: 13px; color: rgba(0, 107, 41, 0.8);">utilizados</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box" style="background-color: #7afd88; color: #006b24;">
                        <i class="fa-solid fa-circle-check fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="fw-semibold d-inline-flex align-items-center gap-2" style="font-size: 11px; color: #006b29;">
                        <span class="pulse-dot"></span> Activos
                    </span>
                    <span style="font-size: 11px; color: #3e4a3d;">Con datos</span>
                </div>
            </div>
        </div>

        <!-- Sin Valores -->
        <div class="col-12 col-md-4">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #ba1a1a;">Sin Valores</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #0b1c30;"><?= $stats['dominios_vacios'] ?? 0; ?></span>
                            <span style="font-size: 13px; color: #3e4a3d;">vacíos</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box" style="background-color: #ffdad6; color: #ba1a1a;">
                        <i class="fa-solid fa-folder-open fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="d-inline-flex align-items-center gap-1" style="font-size: 11px; color: #3e4a3d;">
                        <i class="fa-solid fa-circle-exclamation" style="font-size: 12px;"></i> Sin registros
                    </span>
                    <span class="fw-semibold" style="font-size: 11px; color: #3c627f;">Disponibles</span>
                </div>
            </div>
        </div>
    </div>

    <div class="inser">
        <form id="frmins" class="filter-toolbar-container" action="home.php?pg=<?= $pg; ?>" method="POST">
            <h2><i class="fa-solid fa-list-check"></i> <?php if ($datOne) echo "Actualizar dominio"; else echo "Nuevo dominio"; ?></h2>
            <div class="row">
                <div class="form-group col-md-6">
                    <label for="nomdom" class="fw-semibold">Nombre</label>
                    <input type="text" name="nomdom" id="nomdom" maxlength="70" class="form-control" required value="<?php if ($datOne) echo htmlspecialchars($datOne[0]['nomdom']); ?>">
                </div>
                <div class="form-group col-md-6 d-flex align-items-end">
                    <?php if ($datOne) { ?>
                        <a href="home.php?pg=<?= $pg; ?>" class="btn btn-outline-secondary me-2">Cancelar</a>
                    <?php } ?>
                    <input type="submit" class="btn btn-success" value="<?php if ($datOne) echo "Actualizar"; else echo "Registrar"; ?>">
                    <input type="hidden" name="opera" value="save">
                    <input type="hidden" name="iddom" id="iddom" value="<?php if ($datOne) echo $datOne[0]['iddom']; ?>">
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Toolbar de Control y Filtros -->
<div class="filter-toolbar-container">
    <!-- Izquierda: Píldoras de Filtro de Estado y Exportación -->
    <div class="toolbar-left-group">
        <div class="filter-pills-group" id="tabDominios" role="tablist">
            <button class="btn-filter-pill active" id="dominios-tab" data-bs-toggle="tab" data-bs-target="#dominios" type="button" role="tab" aria-controls="dominios" aria-selected="true" onclick="filterByTab('dominios')">
                <span class="pill-dot" style="background-color: #3c627f;"></span>
                <span>Dominios</span>
                <span class="pill-count"><?= count($dat ?? []); ?></span>
            </button>
        </div>

        <!-- Separador -->
        <div class="toolbar-divider d-none d-sm-block"></div>

        <!-- Botones de Exportación integrados -->
        <div class="toolbar-export-buttons">
            <button type="button" class="toolbar-export-btn" title="Copiar al portapapeles" onclick="triggerDomExport('copy')">
                <i class="fa-solid fa-copy"></i>
            </button>
            <button type="button" class="toolbar-export-btn" title="Descargar CSV" onclick="triggerDomExport('csv')">
                <i class="fa-solid fa-file-csv"></i>
            </button>
            <button type="button" class="toolbar-export-btn" title="Exportar a Excel" onclick="triggerDomExport('excel')">
                <i class="fa-solid fa-file-excel"></i>
            </button>
        </div>
    </div>

    <!-- Derecha: Búsqueda, Ordenamiento y Vista Grid/Lista -->
    <div class="toolbar-search-sort-group">
        <!-- Input de búsqueda -->
        <div class="toolbar-search-box">
            <i class="fa-solid fa-magnifying-glass icoldiz"></i>
            <input type="text" id="customDomSearch" placeholder="Buscar dominio..." onkeyup="syncDomSearch(this.value)">
        </div>

        <!-- Controles Secundarios: Selector de Orden y Conmutador de Vistas -->
        <div class="toolbar-secondary-controls">
            <!-- Selector de ordenamiento -->
            <select class="toolbar-select-sort" id="customDomSort" onchange="syncDomSort(this.value)">
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

<div class="tab-content" id="tabDominiosContent">
    <div class="tab-pane fade show active" id="dominios" role="tabpanel" aria-labelledby="dominios-tab">
        <table id="example" class="table table-striped table-vid-cards" style="width:100%">
            <thead>
                <tr>
                    <th>Dominios</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($dat) {
                    foreach ($dat as $dta) {
                        $cd = $mdom->getDxV($dta['iddom']);
                        $cantVal = ($cd && isset($cd[0]['can'])) ? intval($cd[0]['can']) : 0;
                ?>
                <tr data-order="<?= $dta['iddom']; ?>">
                    <td data-search="<?= $dta['iddom'] . ' ' . $dta['nomdom'] . ' ' . $cantVal; ?>">

                        <!-- Tarjeta de Dominio -->
                        <div class="card h-100 shadow-sm border-0 vid-card">

                            <!-- Cuerpo de la Tarjeta -->
                            <div class="card-body p-3 d-flex flex-column justify-content-between">
                                <div>
                                    <!-- Icono principal -->
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <div class="d-flex align-items-center justify-content-center rounded-3" style="width:38px;height:38px;background:#f1f5f9;">
                                            <i class="fa-solid fa-folder-tree text-secondary"></i>
                                        </div>
                                        <!-- Título del dominio con ID -->
                                        <h6 class="fw-bold text-dark mb-0 text-truncate flex-grow-1" title="<?= $dta['iddom']; ?> - <?= htmlspecialchars($dta['nomdom']); ?>" style="font-size: 1.05rem;">
                                            <span class="text-primary">#<?= $dta['iddom']; ?></span> - <?= htmlspecialchars($dta['nomdom']); ?>
                                        </h6>
                                        <?php if ($cantVal > 0) { ?>
                                            <span class="badge bg-success" title="Con valores"><i class="fa-solid fa-circle-check me-1"></i>Con valores</span>
                                        <?php } else { ?>
                                            <span class="badge bg-danger" title="Sin valores"><i class="fa-solid fa-folder-open me-1"></i>Sin valores</span>
                                        <?php } ?>
                                    </div>

                                    <!-- Caja Tipo / Estado -->
                                    <div class="p-2 mb-3 rounded-3 bg-light border border-light-subtle d-flex justify-content-between align-items-center" style="font-size: 0.8rem;">
                                        <div>
                                            <span class="text-muted d-block" style="font-size: 0.72rem;">Tipo</span>
                                            <span class="fw-bold text-success">
                                                <i class="fa-solid fa-cube me-1"></i>Dominio del Sistema
                                            </span>
                                        </div>
                                        <div class="text-muted"><i class="fa-solid fa-arrow-right-long mx-1"></i></div>
                                        <div class="text-end" style="max-width: 55%;">
                                            <span class="text-muted d-block" style="font-size: 0.72rem;">Valores</span>
                                            <span class="fw-bold text-primary text-truncate d-block" title="<?= $cantVal; ?> valores">
                                                <i class="fa-solid fa-shapes me-1"></i><?= $cantVal; ?> <?= ($cantVal == 1) ? 'Valor' : 'Valores'; ?>
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Metadatos -->
                                    <div class="d-flex justify-content-between text-muted mb-1" style="font-size: 0.78rem;">
                                        <span><i class="fa-solid fa-hashtag text-primary me-1"></i> <strong>ID:</strong> <?= $dta['iddom']; ?></span>
                                        <span><i class="fa-solid fa-link text-secondary me-1"></i> <strong>Estado:</strong> <?= ($cantVal > 0) ? 'Con valores' : 'Sin valores'; ?></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Footer con Botones de Acción -->
                            <div class="card-footer bg-white border-0 pt-0 pb-3 px-3">
                                <hr class="mt-0 mb-3 text-muted opacity-25">
                                <div class="vid-actions-bar" style="grid-template-columns: 1fr 1fr;">
                                    <!-- Botón Editar -->
                                    <a href="home.php?pg=<?= $pg; ?>&iddom=<?= $dta['iddom']; ?>&opera=edi" class="btn btn-outline-warning text-dark shadow-sm" title="Editar">
                                        <i class="fa-solid fa-pen-to-square"></i> <span class="d-none d-sm-inline">Editar</span>
                                    </a>

                                    <!-- Botón Eliminar / En uso -->
                                    <?php if ($cantVal == 0) { ?>
                                        <a href="home.php?pg=<?= $pg; ?>&iddom=<?= $dta['iddom']; ?>&opera=eli" class="btn btn-outline-danger shadow-sm" title="Eliminar" onclick="return confirm('¿Está seguro de eliminar este dominio?');">
                                            <i class="fa-solid fa-trash-can"></i> <span class="d-none d-sm-inline">Eliminar</span>
                                        </a>
                                    <?php } else { ?>
                                        <button type="button" class="btn btn-outline-secondary shadow-sm" disabled title="No se puede eliminar porque tiene valores asociados">
                                            <i class="fa-solid fa-lock"></i> <span class="d-none d-sm-inline">En uso</span>
                                        </button>
                                    <?php } ?>
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
                    <th>Dominios</th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<div style="margin-bottom: 150px"></div>

<script>
// Compatibilidad con valida.js (reutiliza lógica exacta de convpag)
window.syncDomSearch = function(query) {
    if (typeof window.syncCustomSearch === 'function') {
        window.syncCustomSearch(query);
    } else if (typeof $.fn.DataTable !== 'undefined') {
        $('.table-vid-cards').each(function () {
            if ($.fn.DataTable.isDataTable(this)) $(this).DataTable().search(query).draw();
        });
        if ($.fn.DataTable.isDataTable('#example')) $('#example').DataTable().search(query).draw();
    }
};
window.syncDomSort = function(orderDir) {
    if (typeof window.syncCustomSort === 'function') {
        window.syncCustomSort(orderDir);
    } else if (typeof $.fn.DataTable !== 'undefined') {
        $('.table-vid-cards').each(function () {
            if ($.fn.DataTable.isDataTable(this)) $(this).DataTable().order([0, orderDir]).draw();
        });
    }
};
window.triggerDomExport = function(type) {
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
// Listener adicional para input dominios (valida.js ya escucha #customVideoSearch, aquí para #customDomSearch)
$(document).ready(function(){
    $(document).on('input keyup change paste', '#customDomSearch', function () {
        window.syncDomSearch(this.value);
    });
    // Restaurar vista grid/list guardada (misma key que convpag para coherencia)
    const savedView = localStorage.getItem('sagip_vid_view');
    if (savedView === 'list') {
        if (typeof window.switchCardView === 'function') window.switchCardView('list');
    }
});
</script>
