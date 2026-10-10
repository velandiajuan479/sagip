<?php require_once ('controllers/concpag.php'); ?>

<!-- Dashboard -->
<div class="conte">
    <?php echo titulo2("<i class='".($icono ?? 'fa-solid fa-file')."'></i> Página",1); ?>

    <div class="row my-4 g-4">
        <!-- Total Páginas -->
        <div class="col-12 col-md-4">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #3e4a3d;">Total en Sistema</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #0b1c30;"><?= $totalPaginas ?? count($datAll ?? []); ?></span>
                            <span style="font-size: 13px; color: #3e4a3d;">páginas</span>
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

        <!-- Visibles (Menú) -->
        <div class="col-12 col-md-4">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #006b29;">Visibles (Menú)</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #006b29;"><?= $paginasVisibles ?? 0; ?></span>
                            <span style="font-size: 13px; color: rgba(0, 107, 41, 0.8);">en menú</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box" style="background-color: #7afd88; color: #006b24;">
                        <i class="fa-solid fa-eye fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="fw-semibold d-inline-flex align-items-center gap-2" style="font-size: 11px; color: #006b29;">
                        <span class="pulse-dot"></span> Estructura Activa
                    </span>
                    <span style="font-size: 11px; color: #3e4a3d;">Configuradas</span>
                </div>
            </div>
        </div>

        <!-- Ocultas -->
        <div class="col-12 col-md-4">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #ba1a1a;">Ocultas</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #0b1c30;"><?= $paginasOcultas ?? 0; ?></span>
                            <span style="font-size: 13px; color: #3e4a3d;">ocultas</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box" style="background-color: #ffdad6; color: #ba1a1a;">
                        <i class="fa-solid fa-eye-slash fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="d-inline-flex align-items-center gap-1" style="font-size: 11px; color: #3e4a3d;">
                        <i class="fa-solid fa-sitemap" style="font-size: 12px;"></i> Enrutadas
                    </span>
                    <span class="fw-semibold" style="font-size: 11px; color: #3c627f;">Activas</span>
                </div>
            </div>
        </div>
    </div>

    <div class="inser">
        <form id="frmins" class="filter-toolbar-container" action="home.php?pg=<?= $pg; ?>" method="POST">
            <h2><i class="fa-solid fa-list-check"></i> <?php if ($datOne) echo "Actualizar página"; else echo "Nueva página"; ?></h2>
            <div class="row">
                <div class="form-group col-md-6">
                    <label for="nompag" class="fw-semibold">Nombre</label>
                    <input type="text" name="nompag" id="nompag" maxlength="70" class="form-control" required value="<?php if ($datOne) echo htmlspecialchars($datOne[0]['nompag']); ?>">
                </div>
                <div class="form-group col-md-6">
                    <label for="rutpag" class="fw-semibold">Ruta</label>
                    <input type="text" name="rutpag" id="rutpag" class="form-control" required value="<?php if ($datOne) echo htmlspecialchars($datOne[0]['rutpag']); ?>">
                </div>
                <div class="form-group col-md-6">
                    <label for="mospas" class="fw-semibold">Mostrar</label>
                    <select name="mospas" id="mospas" class="form-select">
                        <option value="1" <?php if ($datOne && $datOne[0]['mospas'] == 1) echo "selected"; ?>>Sí</option>
                        <option value="2" <?php if ($datOne && $datOne[0]['mospas'] == 2) echo "selected"; ?>>No</option>
                    </select>
                </div>
                <div class="form-group col-md-6">
                    <label for="ordpag" class="fw-semibold">Ordenar</label>
                    <input type="number" name="ordpag" id="ordpag" class="form-control" required value="<?php if ($datOne) echo $datOne[0]['ordpag']; ?>">
                </div>
                <div class="form-group col-md-6">
                    <label for="icopag" class="fw-semibold">Icono (FontAwesome)</label>
                    <input type="text" name="icopag" id="icopag" class="form-control" value="<?php if ($datOne) echo htmlspecialchars($datOne[0]['icopag']); ?>">
                </div>
                <div class="form-group col-md-6">
                    <label for="idmod" class="fw-semibold">Módulo</label>
                    <select name="idmod" id="idmod" class="form-select" required>
                        <option value="">Seleccione un módulo...</option>
                        <?php if ($datMod) { foreach ($datMod as $dt) { ?>
                            <option value="<?= $dt['idmod']; ?>" <?php if ($datOne && $datOne[0]['idmod'] == $dt['idmod']) echo "selected"; ?>><?= htmlspecialchars($dt['nommod']); ?></option>
                        <?php }} ?>
                    </select>
                </div>
                <div class="form-group col-md-12">
                    <label for="despag" class="fw-semibold">Descripción</label>
                    <textarea name="despag" id="despag" rows="2" class="form-control"><?php if ($datOne) echo htmlspecialchars($datOne[0]['despag']); ?></textarea>
                </div>
                <div class="form-group col-md-6 d-flex align-items-end">
                    <?php if ($datOne) { ?>
                        <a href="home.php?pg=<?= $pg; ?>" class="btn btn-outline-secondary me-2">Cancelar</a>
                    <?php } ?>
                    <input type="submit" class="btn btn-success" value="<?php if ($datOne) echo "Actualizar"; else echo "Registrar"; ?>">
                    <input type="hidden" name="opera" value="save">
                    <input type="hidden" name="idpag" id="idpag" value="<?php if ($datOne) echo $datOne[0]['idpag']; ?>">
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Toolbar de Control y Filtros -->
<div class="filter-toolbar-container">
    <!-- Izquierda: Píldoras de Filtro de Estado y Exportación -->
    <div class="toolbar-left-group">
        <div class="filter-pills-group" id="tabPaginas" role="tablist">
            <button class="btn-filter-pill active" id="paginas-tab" data-bs-toggle="tab" data-bs-target="#paginas" type="button" role="tab" aria-controls="paginas" aria-selected="true" onclick="filterByTab('paginas')">
                <span class="pill-dot" style="background-color: #3c627f;"></span>
                <span>Páginas</span>
                <span class="pill-count"><?= count($datAll ?? []); ?></span>
            </button>
        </div>

        <!-- Separador -->
        <div class="toolbar-divider d-none d-sm-block"></div>

        <!-- Botones de Exportación integrados -->
        <div class="toolbar-export-buttons">
            <button type="button" class="toolbar-export-btn" title="Copiar al portapapeles" onclick="triggerPagExport('copy')">
                <i class="fa-solid fa-copy"></i>
            </button>
            <button type="button" class="toolbar-export-btn" title="Descargar CSV" onclick="triggerPagExport('csv')">
                <i class="fa-solid fa-file-csv"></i>
            </button>
            <button type="button" class="toolbar-export-btn" title="Exportar a Excel" onclick="triggerPagExport('excel')">
                <i class="fa-solid fa-file-excel"></i>
            </button>
        </div>
    </div>

    <!-- Derecha: Búsqueda, Ordenamiento y Vista Grid/Lista -->
    <div class="toolbar-search-sort-group">
        <!-- Input de búsqueda -->
        <div class="toolbar-search-box">
            <i class="fa-solid fa-magnifying-glass icoldiz"></i>
            <input type="text" id="customPagSearch" placeholder="Buscar página..." onkeyup="syncPagSearch(this.value)">
        </div>

        <!-- Controles Secundarios: Selector de Orden y Conmutador de Vistas -->
        <div class="toolbar-secondary-controls">
            <!-- Selector de ordenamiento -->
            <select class="toolbar-select-sort" id="customPagSort" onchange="syncPagSort(this.value)">
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

<div class="tab-content" id="tabPaginasContent">
    <div class="tab-pane fade show active" id="paginas" role="tabpanel" aria-labelledby="paginas-tab">
        <table id="example" class="table table-striped table-vid-cards" style="width:100%">
            <thead>
                <tr>
                    <th>Páginas</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($datAll) {
                    foreach ($datAll as $dta) {
                ?>
                <tr data-order="<?= $dta['idpag']; ?>">
                    <td data-search="<?= $dta['idpag'] . ' ' . $dta['nompag'] . ' ' . $dta['rutpag'] . ' ' . $dta['nommod'] . ' ' . $dta['icopag'] . ' ' . $dta['despag']; ?>">

                        <!-- Tarjeta de Página -->
                        <div class="card h-100 shadow-sm border-0 vid-card">

                            <!-- Cuerpo de la Tarjeta -->
                            <div class="card-body p-3 d-flex flex-column justify-content-between">
                                <div>
                                    <!-- Icono principal -->
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <div class="d-flex align-items-center justify-content-center rounded-3" style="width:38px;height:38px;background:#f1f5f9;">
                                            <i class="<?= htmlspecialchars($dta['icopag'] ? $dta['icopag'] : 'fa-solid fa-file'); ?> text-secondary"></i>
                                        </div>
                                        <!-- Título de la página con ID -->
                                        <h6 class="fw-bold text-dark mb-0 text-truncate flex-grow-1" title="<?= $dta['idpag']; ?> - <?= htmlspecialchars($dta['nompag']); ?>" style="font-size: 1.05rem;">
                                            <span class="text-primary">#<?= $dta['idpag']; ?></span> - <?= htmlspecialchars($dta['nompag']); ?>
                                        </h6>
                                        <?php if ($dta['mospas'] == 1) { ?>
                                            <span class="badge bg-success" title="Visible en menú"><i class="fa-solid fa-eye me-1"></i>Visible</span>
                                        <?php } else { ?>
                                            <span class="badge bg-danger" title="Oculta"><i class="fa-solid fa-eye-slash me-1"></i>Oculta</span>
                                        <?php } ?>
                                    </div>

                                    <!-- Caja Módulo / Ruta -->
                                    <div class="p-2 mb-3 rounded-3 bg-light border border-light-subtle d-flex justify-content-between align-items-center" style="font-size: 0.8rem;">
                                        <div>
                                            <span class="text-muted d-block" style="font-size: 0.72rem;">Módulo</span>
                                            <span class="fw-bold text-success">
                                                <i class="fa-solid fa-cube me-1"></i><?= htmlspecialchars($dta['nommod'] ?? '---'); ?>
                                            </span>
                                        </div>
                                        <div class="text-muted"><i class="fa-solid fa-arrow-right-long mx-1"></i></div>
                                        <div class="text-end" style="max-width: 55%;">
                                            <span class="text-muted d-block" style="font-size: 0.72rem;">Ruta</span>
                                            <span class="fw-bold text-primary text-truncate d-block" title="<?= htmlspecialchars($dta['rutpag']); ?>">
                                                <i class="fa-solid fa-link me-1"></i><?= htmlspecialchars($dta['rutpag']); ?>
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Metadatos -->
                                    <div class="d-flex justify-content-between text-muted mb-1" style="font-size: 0.78rem;">
                                        <span><i class="fa-solid fa-arrow-up-1-9 text-primary me-1"></i> <strong>Orden:</strong> <?= $dta['ordpag']; ?></span>
                                        <span><i class="<?= htmlspecialchars($dta['icopag'] ? $dta['icopag'] : 'fa-solid fa-file'); ?> text-secondary me-1"></i> <strong>Icono:</strong> <?= htmlspecialchars($dta['icopag'] ?: 'fa-solid fa-file'); ?></span>
                                    </div>
                                    <?php if (!empty($dta['despag'])) { ?>
                                    <div class="text-muted mt-2 p-2 rounded bg-white border" style="font-size: 0.78rem;">
                                        <i class="fa-solid fa-align-left text-secondary me-1"></i> <?= htmlspecialchars($dta['despag']); ?>
                                    </div>
                                    <?php } ?>
                                </div>
                            </div>

                            <!-- Footer con Botones de Acción -->
                            <div class="card-footer bg-white border-0 pt-0 pb-3 px-3">
                                <hr class="mt-0 mb-3 text-muted opacity-25">
                                <div class="vid-actions-bar" style="grid-template-columns: 1fr 1fr 1fr;">
                                    <!-- Botón Toggle Visibilidad -->
                                    <?php if ($dta['mospas'] == 1) { ?>
                                        <a href="home.php?pg=<?= $pg; ?>&idpag=<?= $dta['idpag']; ?>&opera=acti&mospas=2" class="btn btn-outline-success shadow-sm" title="Ocultar">
                                            <i class="fa-solid fa-eye"></i> <span class="d-none d-sm-inline">Ocultar</span>
                                        </a>
                                    <?php } else { ?>
                                        <a href="home.php?pg=<?= $pg; ?>&idpag=<?= $dta['idpag']; ?>&opera=acti&mospas=1" class="btn btn-outline-secondary shadow-sm" title="Mostrar">
                                            <i class="fa-solid fa-eye-slash"></i> <span class="d-none d-sm-inline">Mostrar</span>
                                        </a>
                                    <?php } ?>

                                    <!-- Botón Editar -->
                                    <a href="home.php?pg=<?= $pg; ?>&idpag=<?= $dta['idpag']; ?>&opera=edi" class="btn btn-outline-warning text-dark shadow-sm" title="Editar">
                                        <i class="fa-solid fa-pen-to-square"></i> <span class="d-none d-sm-inline">Editar</span>
                                    </a>

                                    <!-- Botón Eliminar -->
                                    <a href="home.php?pg=<?= $pg; ?>&idpag=<?= $dta['idpag']; ?>&opera=eli" class="btn btn-outline-danger shadow-sm" title="Eliminar" onclick="return confirm('¿Está seguro de eliminar esta página?');">
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
                    <th>Páginas</th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<div style="margin-bottom: 150px"></div>

<script>
// Compatibilidad con valida.js (reutiliza lógica exacta de vvidgen/usuvpef)
window.syncPagSearch = function(query) {
    if (typeof window.syncCustomSearch === 'function') {
        window.syncCustomSearch(query);
    } else if (typeof $.fn.DataTable !== 'undefined') {
        $('.table-vid-cards').each(function () {
            if ($.fn.DataTable.isDataTable(this)) $(this).DataTable().search(query).draw();
        });
        if ($.fn.DataTable.isDataTable('#example')) $('#example').DataTable().search(query).draw();
    }
};
window.syncPagSort = function(orderDir) {
    if (typeof window.syncCustomSort === 'function') {
        window.syncCustomSort(orderDir);
    } else if (typeof $.fn.DataTable !== 'undefined') {
        $('.table-vid-cards').each(function () {
            if ($.fn.DataTable.isDataTable(this)) $(this).DataTable().order([0, orderDir]).draw();
        });
    }
};
window.triggerPagExport = function(type) {
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
// Listener adicional para input páginas (valida.js ya escucha #customVideoSearch, aquí para #customPagSearch)
$(document).ready(function(){
    $(document).on('input keyup change paste', '#customPagSearch', function () {
        window.syncPagSearch(this.value);
    });
    // Restaurar vista grid/list guardada (misma key que vidgen/usuvpef para coherencia)
    const savedView = localStorage.getItem('sagip_vid_view');
    if (savedView === 'list') {
        if (typeof window.switchCardView === 'function') window.switchCardView('list');
    }
});
</script>
