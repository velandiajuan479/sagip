<?php require_once 'controllers/usucpef.php'; ?>

<!-- Dashboard -->
<div class="conte">
    <?php echo titulo2("<i class='".$icono."'></i> Perfil",1); ?>

    <div class="row my-4 g-4">
        <!-- Total Perfiles -->
        <div class="col-12 col-md-4">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #3e4a3d;">Total en Sistema</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #0b1c30;"><?= $dash[0]['totalperf'] ?? 0; ?></span>
                            <span style="font-size: 13px; color: #3e4a3d;">perfiles</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box" style="background-color: #b5dcfe; color: #3c627f;">
                        <i class="fa-solid fa-users fs-4"></i>
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

        <!-- Módulos Disponibles -->
        <div class="col-12 col-md-4">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #006b29;">Módulos Disponibles</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #006b29;"><?= count($datmod ?? []); ?></span>
                            <span style="font-size: 13px; color: rgba(0, 107, 41, 0.8);">en sistema</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box" style="background-color: #7afd88; color: #006b24;">
                        <i class="fa-solid fa-cubes fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="fw-semibold d-inline-flex align-items-center gap-2" style="font-size: 11px; color: #006b29;">
                        <span class="pulse-dot"></span> Estructura Activa
                    </span>
                    <span style="font-size: 11px; color: #3e4a3d;">Configurados</span>
                </div>
            </div>
        </div>

        <!-- Páginas Sistema -->
        <div class="col-12 col-md-4">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #ba1a1a;">Páginas Sistema</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #0b1c30;"><?= count($datpag ?? []); ?></span>
                            <span style="font-size: 13px; color: #3e4a3d;">registradas</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box" style="background-color: #ffdad6; color: #ba1a1a;">
                        <i class="fa-solid fa-file-lines fs-4"></i>
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
            <h2><i class="fa-solid fa-list-check"></i> <?php if ($datOne) echo "Actualizar perfil"; else echo "Nuevo perfil"; ?></h2>
            <div class="row">
                <div class="form-group col-md-6">
                    <label for="nomper" class="fw-semibold">Nombre de perfil</label>
                    <input type="text" name="nomper" id="nomper" class="form-control" required value="<?php if ($datOne) echo $datOne[0]['nomper']; ?>">
                </div>
                <div class="form-group col-md-6">
                    <label for="idpag" class="fw-semibold">Página Inicial</label>
                    <select name="idpag" id="idpag" class="form-select">
                        <?php
                        if ($datpag) {
                            foreach ($datpag  as $dt) { ?>
                                <option value="<?= $dt['idpag']; ?>" <?php
                                    if ($datOne && $datOne[0]['idpag'] == $dt['idpag']) echo " selected "; ?>><?= $dt['nompag']; ?></option>
                        <?php }
                        } ?>
                    </select>
                </div>
                <div class="form-group col-md-6">
                    <label for="idmod" class="fw-semibold">Módulo</label>
                    <select name="idmod" id="idmod" class="form-select">
                        <?php if ($datmod) {
                            foreach ($datmod as $dtm) { ?>
                                <option value="<?= $dtm['idmod']; ?>" <?php if ($datOne && $datOne[0]['idmod'] == $dtm['idmod']) echo " selected ";
                                    ?>><?= $dtm['nommod']; ?></option>
                        <?php }
                        } ?>
                    </select>
                </div>
                <div class="form-group col-md-6 d-flex align-items-end">
                    <input type="submit" class="btn btn-success" value="<?php if ($datOne) echo "Actualizar"; else echo "Registrar"; ?>">
                    <input type="hidden" name="opera" value="save">
                    <input type="hidden" name="idper" value="<?php if ($datOne) echo $datOne[0]['idper']; ?>">
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Toolbar de Control y Filtros -->
<div class="filter-toolbar-container">
    <!-- Izquierda: Píldoras de Filtro de Estado y Exportación -->
    <div class="toolbar-left-group">
        <div class="filter-pills-group" id="tabPerfiles" role="tablist">
            <button class="btn-filter-pill active" id="perfiles-tab" data-bs-toggle="tab" data-bs-target="#perfiles" type="button" role="tab" aria-controls="perfiles" aria-selected="true" onclick="filterByTab('perfiles')">
                <span class="pill-dot" style="background-color: #3c627f;"></span>
                <span>Perfiles</span>
                <span class="pill-count"><?= count($dat ?? []); ?></span>
            </button>
        </div>

        <!-- Separador -->
        <div class="toolbar-divider d-none d-sm-block"></div>

        <!-- Botones de Exportación integrados -->
        <div class="toolbar-export-buttons">
            <button type="button" class="toolbar-export-btn" title="Copiar al portapapeles" onclick="triggerPerfilExport('copy')">
                <i class="fa-solid fa-copy"></i>
            </button>
            <button type="button" class="toolbar-export-btn" title="Descargar CSV" onclick="triggerPerfilExport('csv')">
                <i class="fa-solid fa-file-csv"></i>
            </button>
            <button type="button" class="toolbar-export-btn" title="Exportar a Excel" onclick="triggerPerfilExport('excel')">
                <i class="fa-solid fa-file-excel"></i>
            </button>
        </div>
    </div>

    <!-- Derecha: Búsqueda, Ordenamiento y Vista Grid/Lista -->
    <div class="toolbar-search-sort-group">
        <!-- Input de búsqueda -->
        <div class="toolbar-search-box">
            <i class="fa-solid fa-magnifying-glass icoldiz"></i>
            <input type="text" id="customPerfilSearch" placeholder="Buscar perfil..." onkeyup="syncPerfilSearch(this.value)">
        </div>

        <!-- Controles Secundarios: Selector de Orden y Conmutador de Vistas -->
        <div class="toolbar-secondary-controls">
            <!-- Selector de ordenamiento -->
            <select class="toolbar-select-sort" id="customPerfilSort" onchange="syncPerfilSort(this.value)">
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

<div class="tab-content" id="tabPerfilesContent">
    <div class="tab-pane fade show active" id="perfiles" role="tabpanel" aria-labelledby="perfiles-tab">
        <table id="example" class="table table-striped table-vid-cards" style="width:100%">
            <thead>
                <tr>
                    <th>Perfiles</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($dat) {
                    foreach ($dat as $d) {
                ?>
                <tr data-order="<?= $d['idper']; ?>">
                    <td data-search="<?= $d['idper'] . ' ' . $d['nomper'] . ' ' . $d['nommod'] . ' ' . $d['nompag']; ?>">

                        <!-- Tarjeta de Perfil -->
                        <div class="card h-100 shadow-sm border-0 vid-card">

                            <!-- Cuerpo de la Tarjeta -->
                            <div class="card-body p-3 d-flex flex-column justify-content-between">
                                <div>
                                    <!-- Título del perfil con ID -->
                                    <h6 class="fw-bold text-dark mb-2 text-truncate" title="<?= $d['idper']; ?> - <?= htmlspecialchars($d['nomper']); ?>" style="font-size: 1.05rem;">
                                        <span class="text-primary">#<?= $d['idper']; ?></span> - <?= htmlspecialchars($d['nomper']); ?>
                                    </h6>

                                    <!-- Caja Módulo / Página Inicial -->
                                    <div class="p-2 mb-3 rounded-3 bg-light border border-light-subtle d-flex justify-content-between align-items-center" style="font-size: 0.8rem;">
                                        <div>
                                            <span class="text-muted d-block" style="font-size: 0.72rem;">Módulo</span>
                                            <span class="fw-bold text-success">
                                                <i class="fa-solid fa-cube me-1"></i><?= htmlspecialchars($d['nommod']); ?>
                                            </span>
                                        </div>
                                        <div class="text-muted"><i class="fa-solid fa-arrow-right-long mx-1"></i></div>
                                        <div class="text-end">
                                            <span class="text-muted d-block" style="font-size: 0.72rem;">Página Inicial</span>
                                            <span class="fw-bold text-primary">
                                                <i class="fa-solid fa-file-lines me-1"></i><?= htmlspecialchars($d['nompag']); ?>
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Metadatos -->
                                    <div class="d-flex justify-content-between text-muted mb-1" style="font-size: 0.78rem;">
                                        <span><i class="fa-solid fa-cubes text-primary me-1"></i> <strong>Módulo ID:</strong> <?= $d['idmod']; ?></span>
                                    </div>
                                    <div class="d-flex justify-content-between text-muted" style="font-size: 0.78rem;">
                                        <span><i class="fa-solid fa-link text-secondary me-1"></i> <strong>Página ID:</strong> <?= $d['idpag']; ?></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Footer con Botones de Acción -->
                            <div class="card-footer bg-white border-0 pt-0 pb-3 px-3">
                                <hr class="mt-0 mb-3 text-muted opacity-25">
                                <div class="vid-actions-bar" style="grid-template-columns: 1fr 1fr;">
                                    <!-- Botón Ver Páginas -->
                                    <button type="button" class="btn btn-outline-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#myModal<?= $d['idper']; ?>" title="Ver Páginas">
                                        <i class="fa-solid fa-clipboard-check"></i> <span class="d-none d-sm-inline">Páginas</span>
                                    </button>

                                    <!-- Botón Editar -->
                                    <a href="home.php?pg=<?= $pg; ?>&idper=<?= $d['idper']; ?>&opera=edi" class="btn btn-outline-warning text-dark shadow-sm" title="Editar">
                                        <i class="fa-solid fa-pen-to-square"></i> <span class="d-none d-sm-inline">Editar</span>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <?php
                        $mper->setIdmod($d['idmod']);
                        $dtm = $mper->getPagMod();
                        $mper->setIdper($d['idper']);
                        $dps = $mper->getPxP();
                        $dms = arrstr($dps);
                        modal($d['idper'], $d['nomper'], $dtm, $pg, $dms); ?>

                    </td>
                </tr>
                <?php
                    }
                }
                ?>
            </tbody>
            <tfoot>
                <tr>
                    <th>Perfiles</th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<div style="margin-bottom: 150px"></div>

<script>
// Compatibilidad con valida.js (reutiliza lógica exacta de vvidgen)
window.syncPerfilSearch = function(query) {
    if (typeof window.syncCustomSearch === 'function') {
        window.syncCustomSearch(query);
    } else if (typeof $.fn.DataTable !== 'undefined') {
        $('.table-vid-cards').each(function () {
            if ($.fn.DataTable.isDataTable(this)) $(this).DataTable().search(query).draw();
        });
        if ($.fn.DataTable.isDataTable('#example')) $('#example').DataTable().search(query).draw();
    }
};
window.syncPerfilSort = function(orderDir) {
    if (typeof window.syncCustomSort === 'function') {
        window.syncCustomSort(orderDir);
    } else if (typeof $.fn.DataTable !== 'undefined') {
        $('.table-vid-cards').each(function () {
            if ($.fn.DataTable.isDataTable(this)) $(this).DataTable().order([0, orderDir]).draw();
        });
    }
};
window.triggerPerfilExport = function(type) {
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
// Listener adicional para input perfil (valida.js ya escucha #customVideoSearch, aquí para #customPerfilSearch)
$(document).ready(function(){
    $(document).on('input keyup change paste', '#customPerfilSearch', function () {
        window.syncPerfilSearch(this.value);
    });
    // Restaurar vista grid/list guardada (misma key que vidgen para coherencia)
    const savedView = localStorage.getItem('sagip_vid_view');
    if (savedView === 'list') {
        if (typeof window.switchCardView === 'function') window.switchCardView('list');
    }
});
</script>
