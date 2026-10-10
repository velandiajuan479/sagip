<?php
require_once 'controllers/concmod.php';

// Cálculo de métricas para las tarjetas KPI del Dashboard (negocio de módulo, intacto)
$totalModulos = $dat ? count($dat) : 0;
$activosCount = 0;
$inactivosCount = 0;

if ($dat) {
    foreach ($dat as $dt) {
        if ($dt['actmod'] == 1) {
            $activosCount++;
        } else {
            $inactivosCount++;
        }
    }
}
?>

<!-- Dashboard -->
<div class="conte">
    <?php echo titulo2("<i class='".$icono."'></i> Módulo",1); ?>

    <div class="row my-4 g-4">
        <!-- Total Módulos -->
        <div class="col-12 col-md-4">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #3e4a3d;">Total en Sistema</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #0b1c30;"><?= $totalModulos; ?></span>
                            <span style="font-size: 13px; color: #3e4a3d;">módulos</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box" style="background-color: #b5dcfe; color: #3c627f;">
                        <i class="fa-solid fa-cubes fs-4"></i>
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

        <!-- Módulos Activos -->
        <div class="col-12 col-md-4">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #006b29;">Módulos Activos</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #006b29;"><?= $activosCount; ?></span>
                            <span style="font-size: 13px; color: rgba(0, 107, 41, 0.8);">en operación</span>
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
                    <span style="font-size: 11px; color: #3e4a3d;">Habilitados</span>
                </div>
            </div>
        </div>

        <!-- Módulos Inactivos -->
        <div class="col-12 col-md-4">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #ba1a1a;">Módulos Inactivos</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #0b1c30;"><?= $inactivosCount; ?></span>
                            <span style="font-size: 13px; color: #3e4a3d;">pausados</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box" style="background-color: #ffdad6; color: #ba1a1a;">
                        <i class="fa-solid fa-eye-slash fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="d-inline-flex align-items-center gap-1" style="font-size: 11px; color: #3e4a3d;">
                        <i class="fa-solid fa-clock-rotate-left" style="font-size: 12px;"></i> Fuera de operación
                    </span>
                    <span class="fw-semibold" style="font-size: 11px; color: #3c627f;">Inactivos</span>
                </div>
            </div>
        </div>
    </div>

    <div class="inser">
        <form id="frmins" class="filter-toolbar-container" action="home.php?pg=<?= $pg; ?>" method="POST" enctype="multipart/form-data">
            <h2><i class="fa-solid fa-list-check"></i> <?php if ($datOne) echo "Actualizar módulo"; else echo "Nuevo módulo"; ?></h2>
            <div class="row">
                <div class="form-group col-md-6">
                    <label for="nommod" class="fw-semibold">Nombre de módulo</label>
                    <input type="text" name="nommod" id="nommod" class="form-control" required value="<?php if ($datOne) echo htmlspecialchars($datOne[0]['nommod']); ?>">
                </div>
                <div class="form-group col-md-6">
                    <label for="imgmod" class="fw-semibold">Imagen</label>
                    <?php if ($datOne && $datOne[0]['imgmod']) { ?>
                        <input type="hidden" name="imgmod" id="imgmod_hidden" value="<?= htmlspecialchars($datOne[0]['imgmod']); ?>">
                    <?php } ?>
                    <input type="file" name="foto" id="imgmod" class="form-control" accept="image/png, image/jpeg">
                </div>
                <div class="form-group col-md-6">
                    <label for="actmod" class="fw-semibold">Activo</label>
                    <select id="actmod" class="form-select" name="actmod">
                        <option value="1" <?php if ($datOne && $datOne[0]['actmod'] == 1) echo "selected"; ?>>Si</option>
                        <option value="2" <?php if ($datOne && $datOne[0]['actmod'] == 2) echo "selected"; ?>>No</option>
                    </select>
                </div>
                <div class="form-group col-md-6">
                    <label for="idper" class="fw-semibold">Perfil Predeterminado</label>
                    <select id="idper" class="form-select" name="idper">
                        <option value="0">Sin Perfil</option>
                        <?php if ($datPer) {
                            foreach ($datPer as $dtp) { ?>
                                <option value="<?= $dtp['idper']; ?>" <?php if ($datOne && $datOne[0]['idper'] == $dtp['idper']) echo "selected"; ?>>
                                    <?= htmlspecialchars($dtp['nomper']); ?>
                                </option>
                            <?php }
                        } ?>
                    </select>
                </div>
                <div class="form-group col-md-6 d-flex align-items-end">
                    <input type="submit" class="btn btn-success" value="<?php if ($datOne) echo "Actualizar"; else echo "Registrar"; ?>">
                    <input type="hidden" name="opera" value="<?php if ($datOne) echo "Actualizar"; else echo "Insertar"; ?>">
                    <input type="hidden" name="idmod" value="<?php if ($datOne) echo $datOne[0]['idmod']; ?>">
                    <?php if ($datOne) { ?>
                        <a href="home.php?pg=<?= $pg; ?>" class="btn btn-secondary ms-2">Cancelar</a>
                    <?php } ?>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Toolbar de Control y Filtros -->
<div class="filter-toolbar-container">
    <!-- Izquierda: Píldoras de Filtro de Estado y Exportación -->
    <div class="toolbar-left-group">
        <div class="filter-pills-group" id="tabModulos" role="tablist">
            <button class="btn-filter-pill active" id="modulos-tab" data-bs-toggle="tab" data-bs-target="#modulos" type="button" role="tab" aria-controls="modulos" aria-selected="true" onclick="filterByTab('modulos')">
                <span class="pill-dot" style="background-color: #3c627f;"></span>
                <span>Módulos</span>
                <span class="pill-count"><?= count($dat ?? []); ?></span>
            </button>
        </div>

        <!-- Separador -->
        <div class="toolbar-divider d-none d-sm-block"></div>

        <!-- Botones de Exportación integrados -->
        <div class="toolbar-export-buttons">
            <button type="button" class="toolbar-export-btn" title="Copiar al portapapeles" onclick="triggerModuloExport('copy')">
                <i class="fa-solid fa-copy"></i>
            </button>
            <button type="button" class="toolbar-export-btn" title="Descargar CSV" onclick="triggerModuloExport('csv')">
                <i class="fa-solid fa-file-csv"></i>
            </button>
            <button type="button" class="toolbar-export-btn" title="Exportar a Excel" onclick="triggerModuloExport('excel')">
                <i class="fa-solid fa-file-excel"></i>
            </button>
        </div>
    </div>

    <!-- Derecha: Búsqueda, Ordenamiento y Vista Grid/Lista -->
    <div class="toolbar-search-sort-group">
        <!-- Input de búsqueda -->
        <div class="toolbar-search-box">
            <i class="fa-solid fa-magnifying-glass icoldiz"></i>
            <input type="text" id="customModuloSearch" placeholder="Buscar módulo..." onkeyup="syncModuloSearch(this.value)">
        </div>

        <!-- Controles Secundarios: Selector de Orden y Conmutador de Vistas -->
        <div class="toolbar-secondary-controls">
            <!-- Selector de ordenamiento -->
            <select class="toolbar-select-sort" id="customModuloSort" onchange="syncModuloSort(this.value)">
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

<div class="tab-content" id="tabModulosContent">
    <div class="tab-pane fade show active" id="modulos" role="tabpanel" aria-labelledby="modulos-tab">
        <table id="example" class="table table-striped table-vid-cards" style="width:100%">
            <thead>
                <tr>
                    <th>Módulos</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($dat) {
                    foreach ($dat as $d) {
                        $esActivo = ($d['actmod'] == 1);
                        $nomPer = $d['nomper'] ? $d['nomper'] : 'Sin Perfil';
                ?>
                <tr data-order="<?= $d['idmod']; ?>">
                    <td data-search="<?= $d['idmod'] . ' ' . $d['nommod'] . ' ' . $nomPer . ' ' . ($esActivo ? 'Activo' : 'Inactivo'); ?>">

                        <!-- Tarjeta de Módulo -->
                        <div class="card h-100 shadow-sm border-0 vid-card">

                            <!-- Cuerpo de la Tarjeta -->
                            <div class="card-body p-3 d-flex flex-column justify-content-between">
                                <div>
                                    <!-- Título del módulo con ID -->
                                    <h6 class="fw-bold text-dark mb-2 text-truncate" title="<?= $d['idmod']; ?> - <?= htmlspecialchars($d['nommod']); ?>" style="font-size: 1.05rem;">
                                        <span class="text-primary">#<?= $d['idmod']; ?></span> - <?= htmlspecialchars($d['nommod']); ?>
                                    </h6>

                                    <!-- Imagen del módulo -->
                                    <?php if (!empty($d['imgmod']) && file_exists($d['imgmod'])) { ?>
                                        <div class="text-center mb-3">
                                            <img src="<?= $d['imgmod']; ?>" width="100px" class="img-thumbnail shadow-sm" alt="<?= htmlspecialchars($d['nommod']); ?>">
                                        </div>
                                    <?php } ?>

                                    <!-- Caja Módulo / Perfil Predeterminado -->
                                    <div class="p-2 mb-3 rounded-3 bg-light border border-light-subtle d-flex justify-content-between align-items-center" style="font-size: 0.8rem;">
                                        <div>
                                            <span class="text-muted d-block" style="font-size: 0.72rem;">Estado</span>
                                            <span class="fw-bold <?= $esActivo ? 'text-success' : 'text-danger'; ?>">
                                                <i class="fa-solid <?= $esActivo ? 'fa-circle-check' : 'fa-circle-xmark'; ?> me-1"></i><?= $esActivo ? 'Activo' : 'Inactivo'; ?>
                                            </span>
                                        </div>
                                        <div class="text-muted"><i class="fa-solid fa-arrow-right-long mx-1"></i></div>
                                        <div class="text-end">
                                            <span class="text-muted d-block" style="font-size: 0.72rem;">Perfil Predeterminado</span>
                                            <span class="fw-bold text-primary">
                                                <i class="fa-solid fa-user-tag me-1"></i><?= htmlspecialchars($nomPer); ?>
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Metadatos -->
                                    <div class="d-flex justify-content-between text-muted mb-1" style="font-size: 0.78rem;">
                                        <span><i class="fa-solid fa-cubes text-primary me-1"></i> <strong>Módulo ID:</strong> <?= $d['idmod']; ?></span>
                                    </div>
                                    <div class="d-flex justify-content-between text-muted" style="font-size: 0.78rem;">
                                        <span><i class="fa-solid fa-link text-secondary me-1"></i> <strong>Perfil ID:</strong> <?= $d['idper']; ?></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Footer con Botones de Acción -->
                            <div class="card-footer bg-white border-0 pt-0 pb-3 px-3">
                                <hr class="mt-0 mb-3 text-muted opacity-25">
                                <div class="vid-actions-bar" style="grid-template-columns: 1fr 1fr 1fr;">
                                    <!-- Botón Activar / Desactivar -->
                                    <?php if ($esActivo) { ?>
                                        <a href="home.php?pg=<?= $pg; ?>&idmod=<?= $d['idmod']; ?>&opera=acti&actmod=2" class="btn btn-outline-secondary shadow-sm" title="Desactivar">
                                            <i class="fa-solid fa-toggle-on"></i> <span class="d-none d-sm-inline">Desact.</span>
                                        </a>
                                    <?php } else { ?>
                                        <a href="home.php?pg=<?= $pg; ?>&idmod=<?= $d['idmod']; ?>&opera=acti&actmod=1" class="btn btn-outline-success shadow-sm" title="Activar">
                                            <i class="fa-solid fa-toggle-off"></i> <span class="d-none d-sm-inline">Activar</span>
                                        </a>
                                    <?php } ?>

                                    <?php
                                    $mxp = $mmod->getMxp($d['idmod']);
                                    if ($mxp && $mxp[0]['can'] == 0) {
                                    ?>
                                        <a href="home.php?pg=<?= $pg; ?>&idmod=<?= $d['idmod']; ?>&opera=Eliminar" class="btn btn-outline-danger shadow-sm" title="Eliminar" onclick="return confirm('¿Desea eliminar este registro?');">
                                            <i class="fa-solid fa-trash-can"></i> <span class="d-none d-sm-inline">Elim.</span>
                                        </a>
                                    <?php } ?>

                                    <!-- Botón Editar -->
                                    <a href="home.php?pg=<?= $pg; ?>&idmod=<?= $d['idmod']; ?>&ope=edi" class="btn btn-outline-warning text-dark shadow-sm" title="Editar">
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
                    <th>Módulos</th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<div style="margin-bottom: 150px"></div>

<script>
// Compatibilidad con valida.js (misma lógica que usuvpef, renombrada a módulo)
window.syncModuloSearch = function(query) {
    if (typeof window.syncCustomSearch === 'function') {
        window.syncCustomSearch(query);
    } else if (typeof $.fn.DataTable !== 'undefined') {
        $('.table-vid-cards').each(function () {
            if ($.fn.DataTable.isDataTable(this)) $(this).DataTable().search(query).draw();
        });
        if ($.fn.DataTable.isDataTable('#example')) $('#example').DataTable().search(query).draw();
    }
};
window.syncModuloSort = function(orderDir) {
    if (typeof window.syncCustomSort === 'function') {
        window.syncCustomSort(orderDir);
    } else if (typeof $.fn.DataTable !== 'undefined') {
        $('.table-vid-cards').each(function () {
            if ($.fn.DataTable.isDataTable(this)) $(this).DataTable().order([0, orderDir]).draw();
        });
    }
};
window.triggerModuloExport = function(type) {
    if (typeof window.triggerDTExport === 'function') {
        window.triggerDTExport(type);
    } else if (typeof $.fn.DataTable !== 'undefined' && $.fn.DataTable.isDataTable('#example')) {
        const dtApi = $('#example').DataTable();
        if (type === 'copy') dtApi.button('.buttons-copy').trigger();
        else if (type === 'csv') dtApi.button('.buttons-csv').trigger();
        else if (type === 'excel') dtApi.button('.buttons-excel').trigger();
    }
};
$(document).ready(function(){
    $(document).on('input keyup change paste', '#customModuloSearch', function () {
        window.syncModuloSearch(this.value);
    });
    const savedView = localStorage.getItem('sagip_vid_view');
    if (savedView === 'list') {
        if (typeof window.switchCardView === 'function') window.switchCardView('list');
    }
});
</script>
