<?php require_once 'controllers/usucuxfi.php'; ?>

<div style="margin-bottom: 150px">
    <div class="conte">
        <?php echo titulo2("<i class='" . $icono . "'></i> Usuario por Ficha"); ?>

        <div class="row my-4 g-4">
            <div class="col-12 col-sm-6 col-md-3">
                <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #3e4a3d;">Asignaciones Totales</span>
                            <div class="d-flex align-items-baseline gap-2 mt-2">
                                <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #0b1c30;"><?= $dash[0]['tot']; ?></span>
                            </div>
                        </div>
                        <div class="kpi-icon-box" style="background-color: #b5dcfe; color: #3c627f;">
                            <i class="fa-solid fa-link fs-4"></i>
                        </div>
                    </div>
                    <div class="kpi-footer-strip">
                        <span class="fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 11px; color: #3c627f;">
                            <i class="fa-solid fa-link" style="font-size: 12px;"></i> En el sistema
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-3">
                <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #006b29;">Usuarios con Ficha</span>
                            <div class="d-flex align-items-baseline gap-2 mt-2">
                                <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #006b29;"><?= $dash[0]['usuarios']; ?></span>
                            </div>
                        </div>
                        <div class="kpi-icon-box" style="background-color: #7afd88; color: #006b24;">
                            <i class="fa-solid fa-users fs-4"></i>
                        </div>
                    </div>
                    <div class="kpi-footer-strip">
                        <span class="fw-semibold d-inline-flex align-items-center gap-2" style="font-size: 11px; color: #006b29;">
                            <span class="pulse-dot"></span> Con asignación
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-3">
                <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #3e4a3d;">Fichas Asignadas</span>
                            <div class="d-flex align-items-baseline gap-2 mt-2">
                                <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #0b1c30;"><?= $dash[0]['fichas']; ?></span>
                            </div>
                        </div>
                        <div class="kpi-icon-box" style="background-color: #e2e8f0; color: #475569;">
                            <i class="fa-solid fa-layer-group fs-4"></i>
                        </div>
                    </div>
                    <div class="kpi-footer-strip">
                        <span class="fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 11px; color: #3e4a3d;">
                            <i class="fa-solid fa-layer-group" style="font-size: 12px;"></i> En uso
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-3">
                <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #006b29;">Asignaciones Activas</span>
                            <div class="d-flex align-items-baseline gap-2 mt-2">
                                <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #006b29;"><?= $dash[0]['activas']; ?></span>
                            </div>
                        </div>
                        <div class="kpi-icon-box" style="background-color: #7afd88; color: #006b24;">
                            <i class="fa-solid fa-circle-check fs-4"></i>
                        </div>
                    </div>
                    <div class="kpi-footer-strip">
                        <span class="fw-semibold d-inline-flex align-items-center gap-2" style="font-size: 11px; color: #006b29;">
                            <span class="pulse-dot"></span> Vigentes
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="inser">
            <form id="frmins" class="filter-toolbar-container" action="home.php?pg=<?= $pg; ?>" method="POST">
                <h2><i class="fa-solid fa-user-plus"></i> Nueva asignación</h2>
                <div class="row">
                    <div class="form-group col-md-6">
                        <label for="ndocusu" class="fw-semibold">Número de identificación</label>
                        <input type="text" name="ndocusu" id="ndocusu" class="form-control" placeholder="Documento del usuario" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="idfic" class="fw-semibold">Ficha</label>
                        <select name="idfic" id="idfic" class="form-select">
                            <option value="">-- Seleccione ficha --</option>
                            <?php if ($dfic) { foreach ($dfic as $df) { ?>
                                <option value="<?= $df['idfic']; ?>"><?= $df['idfic']; ?> - <?= $df['nomfic']; ?></option>
                            <?php } } ?>
                        </select>
                    </div>
                    <div class="form-group col-md-12 d-flex align-items-center gap-2 mt-2">
                        <input type="submit" class="btn btn-success" value="Asignar">
                        <input type="hidden" name="opera" value="save">
                    </div>
                </div>
            </form>
        </div>

    </div>

    <!-- Toolbar de Control, Filtros y Búsqueda (Diseño Toolbar Institucional) -->
    <div class="filter-toolbar-container">
        <div class="toolbar-left-group">
            <div class="filter-pills-group" id="tabUxfi" role="tablist">
                <button class="btn-filter-pill active" id="uxfi-todas-tab" type="button" role="tab" onclick="filterUxfiTab('todas')">
                    <span class="pill-dot" style="background-color: #3c627f;"></span>
                    <span>Todas</span>
                    <span class="pill-count"><?= count($dat ?? []); ?></span>
                </button>
                <button class="btn-filter-pill" id="uxfi-activas-tab" type="button" role="tab" onclick="filterUxfiTab('activas')">
                    <span class="pill-dot" style="background-color: #006b29;"></span>
                    <span>Activas</span>
                    <span class="pill-count"><?= count(array_filter($dat ?? [], function($r){ return $r['actfic']==1; })); ?></span>
                </button>
                <button class="btn-filter-pill" id="uxfi-inactivas-tab" type="button" role="tab" onclick="filterUxfiTab('inactivas')">
                    <span class="pill-dot" style="background-color: #ba1a1a;"></span>
                    <span>Inactivas</span>
                    <span class="pill-count"><?= count(array_filter($dat ?? [], function($r){ return $r['actfic']!=1; })); ?></span>
                </button>
            </div>

            <div class="toolbar-divider d-none d-sm-block"></div>

            <div class="toolbar-export-buttons">
                <button type="button" class="toolbar-export-btn" title="Copiar al portapapeles" onclick="triggerUxfiExport('copy')">
                    <i class="fa-solid fa-copy"></i>
                </button>
                <button type="button" class="toolbar-export-btn" title="Descargar CSV" onclick="triggerUxfiExport('csv')">
                    <i class="fa-solid fa-file-csv"></i>
                </button>
                <button type="button" class="toolbar-export-btn" title="Exportar a Excel" onclick="triggerUxfiExport('excel')">
                    <i class="fa-solid fa-file-excel"></i>
                </button>
            </div>
        </div>

        <div class="toolbar-search-sort-group">
            <div class="toolbar-search-box">
                <i class="fa-solid fa-magnifying-glass icoldiz"></i>
                <input type="text" id="customUxfiSearch" placeholder="Buscar documento, nombre o ficha..." onkeyup="syncUxfiSearch(this.value)">
            </div>
        </div>
    </div>

    <table id="example" class="table table-striped" style="width:100%">
        <thead>
            <tr>
                <th>Documento</th>
                <th>Nombre</th>
                <th>Ficha</th>
                <th>Jornada</th>
                <th>Centro</th>
                <th>Estado</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($dat) {
                foreach ($dat as $d) {
            ?>
                    <tr>
                        <td data-search="<?= $d['ndocusu']; ?>"><?= $d['ndocusu']; ?></td>
                        <td data-search="<?= $d['nomusu']; ?>"><?= $d['nomusu']; ?></td>
                        <td data-search="<?= $d['idfic'] . ' ' . $d['nomfic']; ?>"><?= $d['idfic']; ?><br><small><?= $d['nomfic']; ?></small></td>
                        <td><?= $d['jornada']; ?></td>
                        <td><?= $d['nomcen']; ?></td>
                        <td><?= ($d['actfic']==1) ? '<span class="badge bg-success">Activa</span>' : '<span class="badge bg-secondary">Inactiva</span>'; ?></td>
                        <td style="text-align:right; white-space:nowrap;">
                            <?php if($d['actfic']!=1){ ?>
                                <a href="home.php?pg=<?= $pg; ?>&idusu=<?= $d['idusu']; ?>&idfic=<?= $d['idfic']; ?>&opera=activar" title="Hacer activa">
                                    <i class="fa-solid fa-flag fa-2x"></i>
                                </a>
                            <?php } ?>
                            <a href="home.php?pg=<?= $pg; ?>&idusu=<?= $d['idusu']; ?>&idfic=<?= $d['idfic']; ?>&opera=del" title="Eliminar" onclick="return confirm('Eliminar la asignaci\u00f3n del usuario a esta ficha?');">
                                <i class="fa-solid fa-trash fa-2x text-danger"></i>
                            </a>
                        </td>
                    </tr>
            <?php
                }
            }
            ?>
        </tbody>
        <tfoot>
            <tr>
                <th>Documento</th>
                <th>Nombre</th>
                <th>Ficha</th>
                <th>Jornada</th>
                <th>Centro</th>
                <th>Estado</th>
                <th></th>
            </tr>
        </tfoot>
    </table>
</div>

<script>
$(document).ready(function () {
    if (typeof $.fn.DataTable !== 'undefined' && $('#example').length) {
        if ($.fn.DataTable.isDataTable('#example')) {
            $('#example').DataTable().destroy();
        }
        $('#example').DataTable({
            "order": [[0, "desc"]],
            dom: 'Bfrtip',
            buttons: [
                { extend: 'copyHtml5', className: 'btn btn-sm btn-outline-secondary' },
                { extend: 'csvHtml5', className: 'btn btn-sm btn-outline-secondary' },
                { extend: 'excelHtml5', className: 'btn btn-sm btn-outline-secondary' }
            ],
            "language": {
                "lengthMenu": "Mostrar _MENU_ registros",
                "zeroRecords": "No se encontraron resultados",
                "info": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
                "infoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
                "infoFiltered": "(filtrado de un total de _MAX_ registros)",
                "sSearch": "Buscar:",
                "oPaginate": { "sFirst": "Primero", "sLast": "Último", "sNext": "Siguiente", "sPrevious": "Anterior" }
            }
        });
    }
});

window.filterUxfiTab = function(tab) {
    document.querySelectorAll('#tabUxfi .btn-filter-pill').forEach(function (pill) {
        pill.classList.remove('active');
    });
    const currentPill = document.getElementById('uxfi-' + tab + '-tab');
    if (currentPill) currentPill.classList.add('active');

    if (typeof $.fn.DataTable !== 'undefined' && $.fn.DataTable.isDataTable('#example')) {
        const dt = $('#example').DataTable();
        if (tab === 'activas') dt.column(5).search('\\bActiva\\b', true, false).draw();
        else if (tab === 'inactivas') dt.column(5).search('Inactiva', false, false).draw();
        else dt.column(5).search('').draw();
    }
};

window.syncUxfiSearch = function(query) {
    if (typeof $.fn.DataTable !== 'undefined' && $.fn.DataTable.isDataTable('#example')) {
        $('#example').DataTable().search(query).draw();
    }
};

window.triggerUxfiExport = function(type) {
    if (typeof $.fn.DataTable !== 'undefined' && $.fn.DataTable.isDataTable('#example')) {
        const dtApi = $('#example').DataTable();
        if (type === 'copy') dtApi.button('.buttons-copy').trigger();
        else if (type === 'csv') dtApi.button('.buttons-csv').trigger();
        else if (type === 'excel') dtApi.button('.buttons-excel').trigger();
    }
};
</script>