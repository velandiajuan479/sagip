<?php require_once 'controllers/etacgeins.php'; ?>
<div class="conte">
    <?php echo titulo2("<i class='".$icono."'></i> Gestión Instructores"); ?>

    <!-- ===================== Dashboard KPI ===================== -->
    <div class="row my-4 g-4">
        <!-- Instructores Asignados -->
        <div class="col-12 col-md-6 col-xl-4">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #006b29;">Instructores Asignados</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #006b29;"><?= $totInsSeg; ?></span>
                            <span style="font-size: 13px; color: rgba(0, 107, 41, 0.8);">en seguimiento</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box" style="background-color: #7afd88; color: #006b24;">
                        <i class="fa-solid fa-user-tie fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="fw-semibold d-inline-flex align-items-center gap-2" style="font-size: 11px; color: #006b29;">
                        <span class="pulse-dot"></span> Seguimiento activo
                    </span>
                    <span style="font-size: 11px; color: #3e4a3d;">Registrados</span>
                </div>
            </div>
        </div>

        <!-- Instructores sin asignar -->
        <div class="col-12 col-md-6 col-xl-4">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #b76e00;">Instructores sin asignar</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #0b1c30;"><?= $totInsNseg; ?></span>
                            <span style="font-size: 13px; color: #3e4a3d;">pendientes</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box" style="background-color: #ffe9c7; color: #b76e00;">
                        <i class="fa-solid fa-users fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 11px; color: #b76e00;">
                        <i class="fa-solid fa-user-clock" style="font-size: 12px;"></i> Por asignar
                    </span>
                    <span style="font-size: 11px; color: #3e4a3d;">Disponibles</span>
                </div>
            </div>
        </div>

        <!-- Fichas Asignadas -->
        <div class="col-12 col-md-6 col-xl-4">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #3c627f;">Fichas Asignadas Completas</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #0b1c30;"><?= $totFic; ?></span>
                            <span style="font-size: 13px; color: #3e4a3d;">fichas</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box" style="background-color: #b5dcfe; color: #3c627f;">
                        <i class="fa-solid fa-layer-group fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 11px; color: #3c627f;">
                        <i class="fa-solid fa-arrow-trend-up" style="font-size: 12px;"></i> Asignaciones completas
                    </span>
                    <span style="font-size: 11px; color: #3e4a3d;">Por instructor</span>
                </div>
            </div>
        </div>
    </div>

    <!-- ===================== Convertir instructor a seguimiento ===================== -->
    <div class="inser">
        <form id="frmins" action="#" method="POST">
            <h2><i class="fa-solid fa-right-left"></i> Convertir instructor en seguimiento</h2>
            <div class="row align-items-end">
                <div class="col-md-8">
                    <label for="idusu" class="form-label fw-bold">Instructor:</label>
                    <select name="idusu" id="idusu" class="form-control" required>
                        <option value="">Seleccione un instructor</option>
                        <?php foreach ($datInst as $dtins): ?>
                            <option value="<?= $dtins['idusu'] ?>"><?= $dtins['nomusu'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4 mt-2">
                    <input type="hidden" name="ope" value="convertir">
                    <button type="submit" class="btn btn-success w-100">Convertir en seguimiento</button>
                </div>
            </div>
        </form>
    </div>

    <!-- ===================== Asignar Fichas a Instructores de Seguimiento ===================== -->
    <div class="inser mt-4">
        <form id="frmAsignarFicha" action="home.php?pg=<?= $pg ?>" method="POST" class="mb-4">
            <input type="hidden" name="ope" value="asigfi">
            <h2><i class="fa-solid fa-user-plus"></i> Asignar Fichas a Instructores de Seguimiento</h2>

            <div class="row align-items-end g-3">
                <!-- Instructor -->
                <div class="col-md-5">
                    <label for="idusu_asig" class="form-label fw-bold">Instructor:</label>
                    <select name="idusu" id="idusu_asig" class="form-control" required>
                        <option value="">Seleccione...</option>
                        <?php foreach ($datInstSegCompleto as $dtiseg): ?>
                            <option value="<?= $dtiseg['idusu'] ?>" <?= ($idusu == $dtiseg['idusu']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($dtiseg['nomusu']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Ficha -->
                <div class="col-md-5">
                    <label for="idficha" class="form-label fw-bold">Ficha:</label>
                    <select name="idficha" id="idficha" class="form-control" required>
                        <option value="">Seleccione una ficha</option>
                        <?php foreach ($datFicDisp as $f): ?>
                            <option value="<?= $f['idfic'] ?>" <?= ($idficha == $f['idfic']) ? 'selected' : '' ?>>
                                <?= $f['idfic'] . ' - ' . $f['nomfic'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Botón Asignar -->
                <div class="col-md-2">
                    <button type="submit" class="btn btn-success w-100">Asignar</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- ===================== Toolbar con Filtros (Estilo vvidgen) ===================== -->
<div class="filter-toolbar-container">
    <!-- Izquierda: Píldoras de filtro + Exportación -->
    <div class="toolbar-left-group">
        <div class="filter-pills-group" role="tablist">
            <!-- Todos -->
            <button class="btn-filter-pill active" data-filtro="todos" type="button" role="tab" onclick="filterByTab('todos')">
                <span class="pill-dot" style="background-color: #3c627f;"></span>
                <span>Todos</span>
                <span class="pill-count"><?= $totTodos; ?></span>
            </button>

            <!-- Con Fichas -->
            <button class="btn-filter-pill" data-filtro="con" type="button" role="tab" onclick="filterByTab('con')">
                <span class="pill-dot" style="background-color: #006b29;"></span>
                <span>Con Fichas</span>
                <span class="pill-count"><?= $totConFichas; ?></span>
            </button>

            <!-- Sin Fichas -->
            <button class="btn-filter-pill" data-filtro="sin" type="button" role="tab" onclick="filterByTab('sin')">
                <span class="pill-dot" style="background-color: #b76e00;"></span>
                <span>Sin Fichas</span>
                <span class="pill-count"><?= $totSinFichas; ?></span>
            </button>
        </div>

        <!-- Separador -->
        <div class="toolbar-divider d-none d-sm-block"></div>

        <!-- Botones de Exportación -->
        <div class="toolbar-export-buttons">
            <button type="button" class="toolbar-export-btn" id="exportCopyInstructores" title="Copiar al portapapeles">
                <i class="fa-solid fa-copy"></i>
            </button>
            <button type="button" class="toolbar-export-btn" id="exportCsvInstructores" title="Descargar CSV">
                <i class="fa-solid fa-file-csv"></i>
            </button>
            <button type="button" class="toolbar-export-btn" id="exportExcelInstructores" title="Exportar a Excel">
                <i class="fa-solid fa-file-excel"></i>
            </button>
        </div>
    </div>

    <!-- Derecha: Búsqueda y ordenamiento -->
    <div class="toolbar-search-sort-group">
        <div class="toolbar-search-box">
            <i class="fa-solid fa-magnifying-glass icoldiz"></i>
            <input type="text" id="searchInstructores" placeholder="Buscar instructor, documento, email o ficha">
        </div>
        <div class="toolbar-secondary-controls">
            <select class="toolbar-select-sort" id="sortInstructores">
                <option value="asc">Ascendente</option>
                <option value="desc">Descendente</option>
            </select>
        </div>
    </div>
</div>

<!-- ===================== Tabla de Instructores (Formato Lista) ===================== -->
<table id="example" class="table table-striped align-middle" style="width:100%">
    <thead>
        <tr>
            <th style="width: 30%;">Instructor de Seguimiento</th>
            <th style="width: 25%;">Contacto</th>
            <th style="width: 30%;">Fichas Asignadas</th>
            <th class="text-center" style="width: 15%; min-width: 130px;">Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($instructores)): ?>
            <?php foreach ($instructores as $inst):
                $estado = !empty($inst['fichas']) ? 'con' : 'sin';
            ?>
                <tr data-estado="<?= $estado ?>">
                    <!-- Instructor: Nombre + Documento -->
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="d-flex align-items-center justify-content-center rounded-circle"
                                 style="width: 42px; height: 42px; flex-shrink: 0;
                                        background: linear-gradient(135deg, #006b29, #7afd88);
                                        color: #fff; font-size: 1.1rem;">
                                <i class="fa-solid fa-user-tie"></i>
                            </div>
                            <div>
                                <div class="fw-bold text-dark"><?= htmlspecialchars($inst['nomusu']) ?></div>
                                <small class="text-muted">
                                    <i class="fa-solid fa-id-card me-1"></i><?= htmlspecialchars($inst['ndocusu']) ?>
                                </small>
                            </div>
                        </div>
                    </td>

                    <!-- Contacto: Email + Teléfono -->
                    <td>
                        <div class="small">
                            <i class="fa-solid fa-envelope text-primary me-1"></i>
                            <?php if (!empty($inst['emausu'])): ?>
                                <a href="mailto:<?= htmlspecialchars($inst['emausu']) ?>" class="text-decoration-none text-dark">
                                    <?= htmlspecialchars($inst['emausu']) ?>
                                </a>
                            <?php else: ?>
                                <span class="text-muted">Sin correo</span>
                            <?php endif; ?>
                        </div>
                        <div class="small mt-1">
                            <i class="fa-solid fa-phone text-success me-1"></i>
                            <?= !empty($inst['telcan']) ? htmlspecialchars($inst['telcan']) : '<span class="text-muted">Sin teléfono</span>' ?>
                        </div>
                    </td>

                    <!-- Fichas Asignadas -->
                    <td>
                        <?php if (!empty($inst['fichas'])): ?>
                            <div class="d-flex flex-wrap gap-1">
                                <?php foreach ($inst['fichas'] as $fich): ?>
                                    <a href="home.php?pg=2409&idusu=<?= $inst['idusu']; ?>&idficha=<?= $fich['idfic']; ?>"
                                       class="badge bg-success text-decoration-none"
                                       title="<?= htmlspecialchars($fich['nomfic'] ?? '') ?>">
                                        <?= $fich['idfic']; ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <span class="badge bg-warning text-dark">
                                <i class="fa-solid fa-circle-exclamation me-1"></i> Sin fichas asignadas
                            </span>
                        <?php endif; ?>
                    </td>

                    <!-- Acciones: Ver + Eliminar -->
                    <td class="text-center">
                        <div class="d-flex justify-content-center gap-2">
                            <a href="home.php?pg=2404&idusu=<?= $inst['idusu']; ?>&ope=modal"
                               class="btn btn-outline-primary shadow-sm" title="Ver Asignaciones">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                            <a href="home.php?pg=2404&idusu=<?= $inst['idusu']; ?>&ope=eliminarInst"
                               class="btn btn-outline-danger shadow-sm"
                               title="Eliminar instructor de seguimiento"
                               onclick="return confirmarEliminarInstructor('<?= htmlspecialchars($inst['nomusu'], ENT_QUOTES) ?>', <?= count($inst['fichas']) ?>);">
                                <i class="fa-solid fa-trash-can"></i>
                            </a>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
    <tfoot>
        <tr>
            <th>Instructor de Seguimiento</th>
            <th>Contacto</th>
            <th>Fichas Asignadas</th>
            <th class="text-center">Acciones</th>
        </tr>
    </tfoot>
</table>

<!-- ===================== Modal de Gestión y Eliminación (sin cambios) ===================== -->
<div class="modal fade" id="modAsigInst" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content shadow-lg border-0" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header bg-success text-white border-0 px-4 py-3">
                <h5 class="modal-title" id="modalLabel">Gestión de Fichas del Instructor: <?= htmlspecialchars($detAsigAgrupado[0]['nomusu'] ?? ($detAsig[0]['nomusu'] ?? '')); ?></h5>
                <a href="home.php?pg=<?= $pg; ?>" class="btn-close btn-close-white" aria-label="Close"></a>
            </div>
            <div class="modal-body">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Ficha</th>
                            <th>Nombre del Programa / Ficha</th>
                            <th>Tipo de Asignación</th>
                            <th class="text-center" style="min-width: 160px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($detAsigAgrupado)): ?>
                            <?php foreach ($detAsigAgrupado as $index => $det): ?>
                                <?php $subrowId = "subrow_fich_" . $index; ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($det['idficha']); ?></strong></td>
                                    <td><?= htmlspecialchars($det['nomfic']); ?></td>
                                    <td>
                                        <span class="badge bg-success">
                                            <i class="fa-solid fa-layer-group me-1"></i> Ficha Completa (<?= count($det['aprendices']); ?> aprendices)
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center align-items-center gap-2">
                                            <?php if (!empty($det['aprendices'])): ?>
                                                <button type="button" class="btn btn-outline-primary shadow-sm" onclick="toggleAprendices('<?= $subrowId; ?>', this);" title="Ver aprendices de la ficha">
                                                    <i class="fa-solid fa-chevron-down"></i>
                                                </button>
                                            <?php endif; ?>
                                            <a href="home.php?pg=<?= $pg; ?>&idusu=<?= $det['idusu']; ?>&idficha=<?= $det['idficha']; ?>&ope=eli"
                                               class="btn btn-outline-danger shadow-sm"
                                               onclick="return confirm('¿Estás seguro de eliminar la asignación de esta ficha para este instructor?');"
                                               title="Eliminar asignación de la ficha">
                                                <i class="fa-solid fa-trash-can"></i> Elim.
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php if (!empty($det['aprendices'])): ?>
                                    <tr id="<?= $subrowId; ?>" style="display: none;">
                                        <td colspan="4" class="bg-light p-3">
                                            <div class="fw-bold mb-2 text-secondary">
                                                <i class="fa-solid fa-users me-1"></i> Aprendices de la Ficha <?= htmlspecialchars($det['idficha']); ?>:
                                            </div>
                                            <div class="table-responsive">
                                                <table class="table table-sm table-hover bg-white border rounded mb-0">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>Documento</th>
                                                            <th>Nombre del Aprendiz</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($det['aprendices'] as $apr): ?>
                                                            <tr>
                                                                <td><?= htmlspecialchars($apr['docapr']); ?></td>
                                                                <td><?= htmlspecialchars($apr['nomapr']); ?></td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted">No hay asignaciones registradas para este instructor.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer border-0">
                <a href="home.php?pg=<?= $pg; ?>" class="btn btn-secondary">Cerrar</a>
            </div>
        </div>
    </div>
</div>

<script>
// Confirmación para eliminar instructor de seguimiento
window.confirmarEliminarInstructor = function(nombre, numFichas) {
    var msg = '¿Está seguro de eliminar al instructor de seguimiento?\n\n' + nombre;
    if (numFichas > 0) {
        msg = '⚠️ ADVERTENCIA ⚠️\n\n' +
              'El instructor "' + nombre + '" tiene ' + numFichas + ' ficha(s) asignada(s).\n\n' +
              'Si elimina al instructor, también se eliminarán TODAS sus asignaciones de fichas.\n\n' +
              '¿Desea continuar?';
    }
    return confirm(msg);
};

// Desplegar u ocultar fila de aprendices en el modal
function toggleAprendices(subrowId, btn) {
    var row = document.getElementById(subrowId);
    if (!row) return;
    var icon = btn.querySelector('i');
    if (row.style.display === 'none' || row.style.display === '') {
        row.style.display = 'table-row';
        if (icon) icon.className = 'fa-solid fa-chevron-up me-1';
    } else {
        row.style.display = 'none';
        if (icon) icon.className = 'fa-solid fa-chevron-down me-1';
    }
}

// Abrir modal de detalle de asignación
document.addEventListener("DOMContentLoaded", function() {
    <?php if ($idusu && $ope==='modal'): ?>
        var myModal = new bootstrap.Modal(document.getElementById('modAsigInst'));
        myModal.show();
    <?php endif; ?>
});
</script>

<script>
// ===================== Toolbar + DataTable =====================
$(document).ready(function () {
    if (typeof $.fn.DataTable === 'undefined') return;
    if (!$('#example').length) return;

    var tbl = $('#example');
    if (!$.fn.DataTable.isDataTable(tbl)) {
        tbl.DataTable({
            language: {
                search: "Buscar:",
                lengthMenu: "Mostrar _MENU_ registros",
                info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
                infoEmpty: "Sin registros",
                zeroRecords: "No se encontraron coincidencias",
                paginate: { first: "Primero", last: "Último", next: "Sig.", previous: "Ant." }
            }
        });
    }
    var dt = tbl.DataTable();

    // -------- Filtro por píldoras (Todos / Con Fichas / Sin Fichas) --------
    var currentFilter = 'todos';

    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
        if (settings.nTable.id !== 'example') return true;
        var row = settings.aoData[dataIndex] ? settings.aoData[dataIndex].nTr : null;
        if (!row) return true;
        var estado = row.getAttribute('data-estado');
        if (currentFilter === 'todos') return true;
        return estado === currentFilter;
    });

    window.filterByTab = function(filtro) {
        currentFilter = filtro;
        document.querySelectorAll('.btn-filter-pill').forEach(function(p) {
            p.classList.remove('active');
        });
        var pill = document.querySelector('[data-filtro="' + filtro + '"]');
        if (pill) pill.classList.add('active');
        dt.draw();
    };

    // -------- Búsqueda personalizada --------
    $('#searchInstructores').on('input change keyup paste', function () {
        dt.search(this.value).draw();
    });

    // -------- Ordenamiento --------
    $('#sortInstructores').on('change', function () {
        dt.order([0, this.value]).draw();
    });

    // -------- Exportaciones --------
    $('#exportCopyInstructores').on('click', function () { dt.button('.buttons-copy').trigger(); });
    $('#exportCsvInstructores').on('click', function ()  { dt.button('.buttons-csv').trigger();  });
    $('#exportExcelInstructores').on('click', function () { dt.button('.buttons-excel').trigger(); });
});
</script>