<?php require_once ('controllers/comchcur.php'); ?>
<?php
$pg = $_REQUEST['pg'] ?? $pg ?? '2013';
$icono = $icono ?? 'fa-solid fa-clock-rotate-left';
$totCursos = count($datAll ?? []);
$anioFiltro = $anioFiltro ?? ($_GET['anio'] ?? '');
?>

<!-- Dashboard -->
<div class="conte">
    <?php echo titulo2("<i class='".($icono ?? 'fa-solid fa-clock-rotate-left')."'></i> Histórico de cursos",1); ?>

    <?php if (!empty($success)) { ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($success); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php } ?>

    <div class="row my-4 g-4">
        <!-- Total Cursos -->
        <div class="col-12 col-md-4">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #3e4a3d;">Total en Sistema</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #0b1c30;"><?= $totCursos ?? count($datAll ?? []); ?></span>
                            <span style="font-size: 13px; color: #3e4a3d;">cursos</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box" style="background-color: #b5dcfe; color: #3c627f;">
                        <i class="fa-solid fa-layer-group fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 11px; color: #3c627f;">
                        <i class="fa-solid fa-arrow-trend-up" style="font-size: 12px;"></i> Historial
                    </span>
                    <span style="font-size: 11px; color: #3e4a3d;">Disponibles: 100%</span>
                </div>
            </div>
        </div>

        <!-- Año Seleccionado -->
        <div class="col-12 col-md-4">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #006b29;">Año Seleccionado</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #006b29;"><?= !empty($anioFiltro) ? htmlspecialchars($anioFiltro) : 'Todos'; ?></span>
                            <span style="font-size: 13px; color: rgba(0, 107, 41, 0.8);">periodo</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box" style="background-color: #7afd88; color: #006b24;">
                        <i class="fa-solid fa-calendar-days fs-4"></i>
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

        <!-- Estado -->
        <div class="col-12 col-md-4">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #ba1a1a;">Estado</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #0b1c30;">Activo</span>
                            <span style="font-size: 13px; color: #3e4a3d;">verificado</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box" style="background-color: #ffdad6; color: #ba1a1a;">
                        <i class="fa-solid fa-clock-rotate-left fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="d-inline-flex align-items-center gap-1" style="font-size: 11px; color: #3e4a3d;">
                        <i class="fa-solid fa-check-circle" style="font-size: 12px;"></i> Sincronizado
                    </span>
                    <span class="fw-semibold" style="font-size: 11px; color: #3c627f;">Cronograma</span>
                </div>
            </div>
        </div>
    </div>

    <div class="inser">
        <form id="frmins" class="filter-toolbar-container" action="home.php?pg=<?= $pg; ?>" method="POST">
            <h2><i class="fa-solid fa-list-check"></i> <?php if (!empty($dtaEdit)) echo "Actualizar curso"; else echo "Nuevo curso"; ?></h2>
            <div class="row">
                <div class="form-group col-md-6">
                    <label for="codpro" class="fw-semibold">Programa</label>
                    <select name="codpro" id="codpro" class="form-select" required>
                        <option value="">Seleccione un programa...</option>
                        <?php if ($datPr) { foreach ($datPr as $dt) { ?>
                            <option value="<?= htmlspecialchars($dt['codpro']); ?>" <?php if (!empty($dtaEdit) && $dtaEdit['codpro'] == $dt['codpro']) echo "selected"; ?>><?= htmlspecialchars($dt['nompro']); ?></option>
                        <?php }} ?>
                    </select>
                </div>
                <div class="form-group col-md-6">
                    <label for="idemp" class="fw-semibold">Empresa</label>
                    <select name="idemp" id="idemp" class="form-select" required>
                        <option value="">Seleccione una empresa...</option>
                        <?php if ($datEm) { foreach ($datEm as $dt) { ?>
                            <option value="<?= htmlspecialchars($dt['idemp']); ?>" <?php if (!empty($dtaEdit) && $dtaEdit['idemp'] == $dt['idemp']) echo "selected"; ?>><?= htmlspecialchars($dt['nomemp']); ?></option>
                        <?php }} ?>
                    </select>
                </div>
                <div class="form-group col-md-6">
                    <label for="codproesp" class="fw-semibold">Programa especial</label>
                    <select name="codproesp" id="codproesp" class="form-select" required>
                        <option value="">Seleccione un valor...</option>
                        <?php if ($datPe) { foreach ($datPe as $dt) { ?>
                            <option value="<?= htmlspecialchars($dt['idval']); ?>" <?php if (!empty($dtaEdit) && $dtaEdit['codproesp'] == $dt['idval']) echo "selected"; ?>><?= htmlspecialchars($dt['nomval']); ?></option>
                        <?php }} ?>
                    </select>
                </div>
                <div class="form-group col-md-6">
                    <label for="jornada" class="fw-semibold">Jornada</label>
                    <select name="jornada" id="jornada" class="form-select" required>
                        <option value="">Seleccione una jornada...</option>
                        <?php if ($datJo) { foreach ($datJo as $dt) { ?>
                            <option value="<?= htmlspecialchars($dt['idval']); ?>" <?php if (!empty($dtaEdit) && $dtaEdit['jornada'] == $dt['idval']) echo "selected"; ?>><?= htmlspecialchars($dt['nomval']); ?></option>
                        <?php }} ?>
                    </select>
                </div>
                <div class="form-group col-md-6">
                    <label for="feclini" class="fw-semibold">Fecha inicio</label>
                    <input type="date" name="feclini" id="feclini" class="form-control" required value="<?php if (!empty($dtaEdit)) echo htmlspecialchars($dtaEdit['feclini']); ?>">
                </div>
                <div class="form-group col-md-6">
                    <label for="feclin" class="fw-semibold">Fecha fin</label>
                    <input type="date" name="feclin" id="feclin" class="form-control" required value="<?php if (!empty($dtaEdit)) echo htmlspecialchars($dtaEdit['feclin']); ?>">
                </div>
                <div class="form-group col-md-6">
                    <label for="cupo" class="fw-semibold">Cupo</label>
                    <input type="number" name="cupo" id="cupo" class="form-control" required value="<?php if (!empty($dtaEdit)) echo htmlspecialchars($dtaEdit['cupo']); ?>">
                </div>
                <div class="form-group col-md-6">
                    <label for="idfic" class="fw-semibold">Ficha</label>
                    <select name="idfic" id="idfic" class="form-select" required>
                        <option value="">Seleccione una ficha...</option>
                        <?php if ($datFic) { foreach ($datFic as $dt) { ?>
                            <option value="<?= htmlspecialchars($dt['idfic']); ?>" <?php if (!empty($dtaEdit) && $dtaEdit['idfic'] == $dt['idfic']) echo "selected"; ?>><?= htmlspecialchars($dt['nomfic']); ?></option>
                        <?php }} ?>
                    </select>
                </div>
                <div class="form-group col-md-6 d-flex align-items-end">
                    <?php if (!empty($dtaEdit)) { ?>
                        <a href="home.php?pg=<?= $pg; ?>" class="btn btn-outline-secondary me-2">Cancelar</a>
                    <?php } ?>
                    <input type="submit" class="btn btn-success" value="<?php if (!empty($dtaEdit)) echo "Actualizar"; else echo "Registrar"; ?>">
                    <input type="hidden" name="opera" value="save">
                    <input type="hidden" name="idnorad" id="idnorad" value="<?php if (!empty($dtaEdit)) echo htmlspecialchars($dtaEdit['idnorad']); ?>">
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Toolbar de Control y Filtros -->
<div class="filter-toolbar-container">
    <!-- Izquierda: Píldoras de Filtro de Estado y Exportación -->
    <div class="toolbar-left-group">
        <div class="filter-pills-group" id="tabHistorico" role="tablist">
            <button class="btn-filter-pill active" id="historico-tab" data-bs-toggle="tab" data-bs-target="#historico" type="button" role="tab" aria-controls="historico" aria-selected="true" onclick="filterByTab('historico')">
                <span class="pill-dot" style="background-color: #3c627f;"></span>
                <span>Histórico</span>
                <span class="pill-count"><?= count($datAll ?? []); ?></span>
            </button>
        </div>

        <!-- Separador -->
        <div class="toolbar-divider d-none d-sm-block"></div>

        <!-- Botones de Exportación integrados -->
        <div class="toolbar-export-buttons">
            <button type="button" class="toolbar-export-btn" title="Copiar al portapapeles" onclick="triggerHisExport('copy')">
                <i class="fa-solid fa-copy"></i>
            </button>
            <button type="button" class="toolbar-export-btn" title="Descargar CSV" onclick="triggerHisExport('csv')">
                <i class="fa-solid fa-file-csv"></i>
            </button>
            <button type="button" class="toolbar-export-btn" title="Exportar a Excel" onclick="triggerHisExport('excel')">
                <i class="fa-solid fa-file-excel"></i>
            </button>
        </div>
    </div>

    <!-- Derecha: Búsqueda, Ordenamiento y Vista Grid/Lista -->
    <div class="toolbar-search-sort-group">
        <!-- Input de búsqueda -->
        <div class="toolbar-search-box">
            <i class="fa-solid fa-magnifying-glass icoldiz"></i>
            <input type="text" id="customHisSearch" placeholder="Buscar en histórico..." onkeyup="syncHisSearch(this.value)">
        </div>

        <!-- Controles Secundarios: Filtro de Año, Orden y Conmutador de Vistas -->
        <div class="toolbar-secondary-controls">
            <!-- Filtro por año (negocio de datos: GET anio) -->
            <select class="toolbar-select-sort" id="customHisYear" onchange="filterHisYear(this.value)" title="Filtrar por año">
                <option value="">Todos los años</option>
                <?php
                $anioActual = date('Y');
                for ($a = $anioActual; $a >= $anioActual - 5; $a--) {
                ?>
                    <option value="<?= $a; ?>" <?php if ((string)$anioFiltro === (string)$a) echo "selected"; ?>><?= $a; ?></option>
                <?php } ?>
            </select>

            <!-- Selector de ordenamiento -->
            <select class="toolbar-select-sort" id="customHisSort" onchange="syncHisSort(this.value)">
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

<div class="tab-content" id="tabHistoricoContent">
    <div class="tab-pane fade show active" id="historico" role="tabpanel" aria-labelledby="historico-tab">
        <table id="example" class="table table-striped table-vid-cards" style="width:100%">
            <thead>
                <tr>
                    <th>Histórico</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($datAll) {
                    foreach ($datAll as $dta) {
                ?>
                <tr data-order="<?= $dta['idnorad']; ?>">
                    <td data-search="<?= $dta['idnorad'] . ' ' . $dta['codpro'] . ' ' . $dta['nompro'] . ' ' . $dta['nomemp'] . ' ' . ($dta['proesp'] ?? '') . ' ' . ($dta['jorn'] ?? '') . ' ' . $dta['cupo'] . ' ' . $dta['feclini'] . ' ' . $dta['feclin'] . ' ' . ($dta['nomfic'] ?? ''); ?>">

                        <!-- Tarjeta de Histórico -->
                        <div class="card h-100 shadow-sm border-0 vid-card">

                            <!-- Cuerpo de la Tarjeta -->
                            <div class="card-body p-3 d-flex flex-column justify-content-between">
                                <div>
                                    <!-- Icono principal -->
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <div class="d-flex align-items-center justify-content-center rounded-3" style="width:38px;height:38px;background:#f1f5f9;">
                                            <i class="fa-solid fa-clock-rotate-left text-secondary"></i>
                                        </div>
                                        <!-- Título del curso con ID -->
                                        <h6 class="fw-bold text-dark mb-0 text-truncate flex-grow-1" title="<?= $dta['idnorad']; ?> - <?= htmlspecialchars($dta['nompro'] ?? ''); ?>" style="font-size: 1.05rem;">
                                            <span class="text-primary">#<?= $dta['idnorad']; ?></span> - <?= htmlspecialchars($dta['nompro'] ?? ''); ?>
                                        </h6>
                                        <span class="badge bg-success" title="Jornada"><i class="fa-solid fa-calendar-days me-1"></i><?= htmlspecialchars($dta['jorn'] ?? '---'); ?></span>
                                    </div>

                                    <!-- Caja Programa / Empresa -->
                                    <div class="p-2 mb-3 rounded-3 bg-light border border-light-subtle d-flex justify-content-between align-items-center" style="font-size: 0.8rem;">
                                        <div>
                                            <span class="text-muted d-block" style="font-size: 0.72rem;">Programa</span>
                                            <span class="fw-bold text-success">
                                                <i class="fa-solid fa-cube me-1"></i><?= htmlspecialchars(($dta['codpro'] ?? '') . ' - ' . ($dta['nompro'] ?? '---')); ?>
                                            </span>
                                        </div>
                                        <div class="text-muted"><i class="fa-solid fa-arrow-right-long mx-1"></i></div>
                                        <div class="text-end" style="max-width: 55%;">
                                            <span class="text-muted d-block" style="font-size: 0.72rem;">Empresa</span>
                                            <span class="fw-bold text-primary text-truncate d-block" title="<?= htmlspecialchars($dta['nomemp'] ?? ''); ?>">
                                                <i class="fa-solid fa-link me-1"></i><?= htmlspecialchars($dta['nomemp'] ?? '---'); ?>
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Metadatos -->
                                    <div class="d-flex justify-content-between text-muted mb-1" style="font-size: 0.78rem;">
                                        <span><i class="fa-solid fa-users text-primary me-1"></i> <strong>Cupo:</strong> <?= htmlspecialchars($dta['cupo'] ?? '---'); ?></span>
                                        <span><i class="fa-solid fa-calendar-days text-secondary me-1"></i> <strong>Ficha:</strong> <?= htmlspecialchars($dta['nomfic'] ?? '---'); ?></span>
                                    </div>
                                    <div class="text-muted mt-2 p-2 rounded bg-white border" style="font-size: 0.78rem;">
                                        <i class="fa-solid fa-clock text-secondary me-1"></i> <?= htmlspecialchars($dta['feclini'] ?? ''); ?> / <?= htmlspecialchars($dta['feclin'] ?? ''); ?>
                                        <?php if (!empty($dta['proesp'])) { ?>
                                            <span class="d-block mt-1"><i class="fa-solid fa-align-left text-secondary me-1"></i> <?= htmlspecialchars($dta['proesp']); ?></span>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Footer con Botones de Acción -->
                            <div class="card-footer bg-white border-0 pt-0 pb-3 px-3">
                                <hr class="mt-0 mb-3 text-muted opacity-25">
                                <div class="vid-actions-bar" style="grid-template-columns: 1fr;">
                                    <!-- Botón Editar -->
                                    <a href="home.php?pg=<?= $pg; ?><?= !empty($anioFiltro) ? '&anio=' . urlencode($anioFiltro) : ''; ?>&idnorad=<?= $dta['idnorad']; ?>" class="btn btn-outline-warning text-dark shadow-sm" title="Editar">
                                        <i class="fa-solid fa-pen-to-square"></i> <span class="d-none d-sm-inline">Editar</span>
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
                    <th>Histórico</th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<div style="margin-bottom: 150px"></div>

<script>
// Compatibilidad con valida.js (reutiliza lógica exacta de convpag/vvidgen/usuvpef)
window.filterHisYear = function(anio) {
    const pg = '<?= $pg; ?>';
    if (anio) window.location.href = 'home.php?pg=' + pg + '&anio=' + encodeURIComponent(anio);
    else window.location.href = 'home.php?pg=' + pg;
};
window.syncHisSearch = function(query) {
    if (typeof window.syncCustomSearch === 'function') {
        window.syncCustomSearch(query);
    } else if (typeof $.fn.DataTable !== 'undefined') {
        $('.table-vid-cards').each(function () {
            if ($.fn.DataTable.isDataTable(this)) $(this).DataTable().search(query).draw();
        });
        if ($.fn.DataTable.isDataTable('#example')) $('#example').DataTable().search(query).draw();
    }
};
window.syncHisSort = function(orderDir) {
    if (typeof window.syncCustomSort === 'function') {
        window.syncCustomSort(orderDir);
    } else if (typeof $.fn.DataTable !== 'undefined') {
        $('.table-vid-cards').each(function () {
            if ($.fn.DataTable.isDataTable(this)) $(this).DataTable().order([0, orderDir]).draw();
        });
    }
};
window.triggerHisExport = function(type) {
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
// Listener adicional para input histórico (valida.js ya escucha #customVideoSearch, aquí para #customHisSearch)
$(document).ready(function(){
    $(document).on('input keyup change paste', '#customHisSearch', function () {
        window.syncHisSearch(this.value);
    });
    // Restaurar vista grid/list guardada (misma key que vidgen/usuvpef/convpag para coherencia)
    const savedView = localStorage.getItem('sagip_vid_view');
    if (savedView === 'list') {
        if (typeof window.switchCardView === 'function') window.switchCardView('list');
    }
});
</script>
