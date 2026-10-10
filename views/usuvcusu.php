<?php require_once 'controllers/usuccusu.php'; ?>

<div class="conte">
    <!-- Título de la Vista (Sin iconos de + y -) -->
    <?php echo titulo2("<i class='fa-solid fa-users-gear'></i> Carga Masiva - Usuarios y Fichas", 0); ?>

    <!-- ========================================================================= -->
    <!-- DASHBOARD EN TIEMPO REAL: FICHAS Y ASOCIACIONES ACTIVAS                  -->
    <!-- ========================================================================= -->
    <div class="row my-4 g-4">
        <!-- KPI 1: Fichas en Sistema -->
        <div class="col-12 col-md-6">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #3c627f;">Fichas en Sistema</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #0b1c30;"><?= number_format($dashboardStats['total_fichas'] ?? 0); ?></span>
                            <span style="font-size: 13px; color: #3e4a3d;">programas</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box" style="background-color: #e5eeff; color: #3c627f;">
                        <i class="fa-solid fa-folder-tree fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 11px; color: #3c627f;">
                        <i class="fa-solid fa-book-bookmark" style="font-size: 12px;"></i> Programas Activos
                    </span>
                    <span style="font-size: 11px; color: #3e4a3d;">Disponibles</span>
                </div>
            </div>
        </div>

        <!-- KPI 2: Asociaciones Activas -->
        <div class="col-12 col-md-6">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #065f46;">Asociaciones Activas</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #0b1c30;"><?= number_format($dashboardStats['total_asociaciones'] ?? 0); ?></span>
                            <span style="font-size: 13px; color: #3e4a3d;">vínculos</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box" style="background-color: #d1fae5; color: #065f46;">
                        <i class="fa-solid fa-network-wired fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 11px; color: #065f46;">
                        <i class="fa-solid fa-id-card-clip" style="font-size: 12px;"></i> Matrículas
                    </span>
                    <span style="font-size: 11px; color: #3e4a3d;">Aprendiz-Ficha</span>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- MEJORA 1.2: BANNER Y DESCARGA DIRECTA DE PLANTILLA OFICIAL               -->
    <!-- ========================================================================= -->
    <div class="card border border-success border-opacity-25 bg-light shadow-sm mb-4 rounded-3">
        <div class="card-body p-4">
            <div class="row align-items-center">
                <div class="col-lg-8 col-md-7 col-12 mb-3 mb-md-0">
                    <div class="d-flex align-items-start">
                        <div class="me-3 p-3 bg-white rounded-circle shadow-sm text-success">
                            <i class="fa-solid fa-file-excel fa-2x"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold text-success mb-1">
                                <i class="fa-solid fa-download me-1"></i> Plantilla Oficial de Carga Masiva
                            </h5>
                            <p class="text-muted mb-2 small">
                                Descargue directamente la plantilla oficial en formato Microsoft Excel (.xlsx) estructurada con todas las columnas obligatorias y opcionales. 
                                <strong>Permite registrar aprendices asociados a múltiples fichas distintas en un mismo archivo</strong> e incluye una hoja de consulta con las fichas registradas en el sistema.
                            </p>
                            <div class="d-flex flex-wrap gap-2">
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">
                                    <i class="fa-solid fa-check me-1"></i> Soporte Carga Multificha
                                </span>
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25">
                                    <i class="fa-solid fa-table-list me-1"></i> Hoja con Fichas del Sistema
                                </span>
                                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25">
                                    <i class="fa-solid fa-shield-halved me-1"></i> Validación Estructurada
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-5 col-12 text-md-end text-center">
                    <a href="controllers/usuccusu.php?action=descargar_plantilla" class="btn btn-success btn-lg px-4 shadow-sm w-100 w-md-auto rounded-pill fw-bold">
                        <i class="fa-solid fa-file-arrow-down me-2"></i> Descargar Plantilla Excel
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- FORMULARIO DE CARGA MASIVA                                               -->
    <!-- ========================================================================= -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <h5 class="card-title fw-bold text-dark mb-0">
                <i class="fa-solid fa-cloud-arrow-up text-success me-2"></i> Procesar Archivo de Carga Masiva
            </h5>
        </div>
        <div class="card-body p-4">
            <form name="frm1" action="home.php?pg=<?= $pag; ?>" method="POST" enctype="multipart/form-data" id="formCargaMasiva">
                <div class="row g-3">
                    <div class="form-group col-12 col-md-6 col-lg-3">
                        <label for="inic" class="form-label fw-bold small text-muted">
                            <i class="fa-solid fa-key me-1"></i> Contraseña Inicio (Prefijo)
                        </label>
                        <input type="text" name="inic" id="inic" class="form-control" placeholder="Ej: Sena." value="<?= htmlspecialchars($inic ?? ''); ?>">
                        <div class="form-text small">Texto inicial para la clave por defecto.</div>
                    </div>

                    <div class="form-group col-12 col-md-6 col-lg-3">
                        <label for="finc" class="form-label fw-bold small text-muted">
                            <i class="fa-solid fa-lock me-1"></i> Contraseña Fin (Sufijo)
                        </label>
                        <input type="text" name="finc" id="finc" class="form-control" placeholder="Ej: *" value="<?= htmlspecialchars($finc ?? ''); ?>">
                        <div class="form-text small">Texto final para la clave por defecto.</div>
                    </div>

                    <div class="form-group col-12 col-md-8 col-lg-4">
                        <label for="arch" class="form-label fw-bold small text-muted">
                            <i class="fa-solid fa-file-spreadsheet me-1"></i> Seleccionar Archivo Excel (*.xlsx, *.xls)
                        </label>
                        <input type="file" name="arch" id="arch" class="form-control" accept=".xls,.xlsx" required>
                        <div class="form-text small">Archivo con la estructura oficial SENA.</div>
                    </div>

                    <div class="form-group col-12 col-md-4 col-lg-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-success w-100 py-2 shadow-sm fw-bold rounded-pill" id="btnProcesar">
                            <i class="fa-solid fa-upload me-1"></i> Cargar Archivo
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- MEJORA 1.1: DETALLE ESTRUCTURADO DE ERRORES Y RESUMEN DEL PROCESAMIENTO   -->
    <!-- ========================================================================= -->
    <?php if ($resultadoCarga['procesado']): ?>
        <div class="card border-0 shadow-sm mb-4 resultado-carga-card">
            <div class="card-header bg-white border-bottom py-3 d-flex flex-wrap justify-content-between align-items-center">
                <h5 class="card-title fw-bold mb-0">
                    <i class="fa-solid fa-chart-pie me-2 text-<?= $resultadoCarga['tipo']; ?>"></i> Resumen de Procesamiento
                </h5>
                <div class="d-flex flex-wrap gap-2 mt-2 mt-md-0">
                    <span class="badge bg-primary px-3 py-2">
                        <i class="fa-solid fa-list-ol me-1"></i> Filas leídas: <?= $resultadoCarga['total_filas']; ?>
                    </span>
                    <span class="badge bg-success px-3 py-2">
                        <i class="fa-solid fa-circle-check me-1"></i> Exitosos: <?= $resultadoCarga['exitosos']; ?>
                    </span>
                    <span class="badge bg-info text-dark px-3 py-2">
                        <i class="fa-solid fa-user-plus me-1"></i> Nuevos: <?= $resultadoCarga['usuarios_creados']; ?>
                    </span>
                    <span class="badge bg-secondary px-3 py-2">
                        <i class="fa-solid fa-user-pen me-1"></i> Actualizados: <?= $resultadoCarga['usuarios_actualizados']; ?>
                    </span>
                    <span class="badge bg-dark px-3 py-2">
                        <i class="fa-solid fa-folder me-1"></i> Fichas: <?= count($resultadoCarga['fichas_procesadas']); ?>
                    </span>
                    <?php if ($resultadoCarga['errores'] > 0): ?>
                        <span class="badge bg-danger px-3 py-2">
                            <i class="fa-solid fa-triangle-exclamation me-1"></i> Inconsistencias: <?= $resultadoCarga['errores']; ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card-body p-4">
                <!-- Alerta General con Estado -->
                <div class="alert alert-<?= $resultadoCarga['tipo']; ?> d-flex align-items-center mb-4 shadow-sm" role="alert">
                    <?php if ($resultadoCarga['tipo'] === 'success'): ?>
                        <i class="fa-solid fa-circle-check fa-2x me-3 text-success"></i>
                    <?php elseif ($resultadoCarga['tipo'] === 'warning'): ?>
                        <i class="fa-solid fa-triangle-exclamation fa-2x me-3 text-warning"></i>
                    <?php else: ?>
                        <i class="fa-solid fa-circle-xmark fa-2x me-3 text-danger"></i>
                    <?php endif; ?>
                    <div>
                        <h6 class="alert-heading fw-bold mb-1">
                            <?= ($resultadoCarga['tipo'] === 'success') ? '¡Proceso Completado con Éxito!' : (($resultadoCarga['tipo'] === 'warning') ? 'Procesamiento con Observaciones' : 'Se Requiere Corrección de Inconsistencias'); ?>
                        </h6>
                        <p class="mb-0 small"><?= htmlspecialchars($resultadoCarga['mensaje']); ?></p>
                    </div>
                </div>

                <!-- TABLA ESTRUCTURADA DE ERRORES (IDENTIFICA FILA, COLUMNA Y MOTIVO ESPECÍFICO) -->
                <?php if (!empty($resultadoCarga['lista_errores'])): ?>
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold text-danger mb-0">
                                <i class="fa-solid fa-list-check me-2"></i> Detalle Estructurado de Inconsistencias Detectadas (<?= count($resultadoCarga['lista_errores']); ?>)
                            </h6>
                            <small class="text-muted">Corrija estas celdas en el archivo Excel y vuelva a cargarlo.</small>
                        </div>

                        <div class="table-responsive rounded shadow-sm border">
                            <table class="table table-hover table-striped align-middle mb-0" id="tablaErroresCarga">
                                <thead class="table-dark">
                                    <tr>
                                        <th class="text-center" style="width: 80px;">#</th>
                                        <th class="text-center" style="width: 120px;">Fila en Excel</th>
                                        <th style="width: 200px;">Columna / Campo</th>
                                        <th style="width: 180px;">Valor Recibido</th>
                                        <th>Motivo Específico del Fallo</th>
                                        <th>Acción Correctiva Sugerida</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($resultadoCarga['lista_errores'] as $idx => $err): ?>
                                        <tr>
                                            <td class="text-center text-muted fw-bold"><?= $idx + 1; ?></td>
                                            <td class="text-center">
                                                <span class="badge bg-danger fs-6 px-2 py-1">
                                                    <?= is_numeric($err['fila']) ? 'Fila ' . $err['fila'] : $err['fila']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark border px-2 py-1 fw-bold">
                                                    <?= htmlspecialchars($err['columna']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <code class="px-2 py-1 bg-light text-danger rounded border">
                                                    <?= htmlspecialchars($err['valor']); ?>
                                                </code>
                                            </td>
                                            <td class="text-danger fw-semibold">
                                                <i class="fa-solid fa-circle-exclamation me-1"></i>
                                                <?= htmlspecialchars($err['motivo']); ?>
                                            </td>
                                            <td class="text-dark small">
                                                <i class="fa-solid fa-wrench text-secondary me-1"></i>
                                                <?= htmlspecialchars($err['solucion']); ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- TABLA AUTOMATIZADA DE REGISTROS EXITOSOS (SENCILLA, LIMPIA Y RESPONSIVE) -->
                <?php if (!empty($resultadoCarga['lista_exitos'])): ?>
                    <div class="card border border-success border-opacity-25 rounded-3 shadow-sm mb-4">
                        <div class="card-header bg-success bg-opacity-10 border-bottom border-success border-opacity-25 py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                            <div class="d-flex align-items-center gap-2">
                                <span class="p-2 rounded-circle bg-success text-white d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                    <i class="fa-solid fa-circle-check fs-6"></i>
                                </span>
                                <div>
                                    <h6 class="fw-bold text-success mb-0">Registros Procesados con Éxito</h6>
                                    <small class="text-muted">Aprendices vinculados y guardados correctamente en el sistema</small>
                                </div>
                            </div>
                            <span class="badge bg-success px-3 py-2 fs-7 rounded-pill">
                                <i class="fa-solid fa-users me-1"></i> <?= count($resultadoCarga['lista_exitos']); ?> aprendiz(ces)
                            </span>
                        </div>
                        <div class="card-body p-3">
                            <div class="table-responsive">
                                <table class="table table-sm table-striped table-hover align-middle mb-0 w-100" id="tablaExitosCarga">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-center" style="width: 50px;">#</th>
                                            <th class="text-center" style="width: 80px;">Fila Excel</th>
                                            <th style="width: 130px;">Documento</th>
                                            <th>Nombre Completo</th>
                                            <th>Ficha Asignada</th>
                                            <th class="text-center" style="width: 120px;">Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($resultadoCarga['lista_exitos'] as $idx => $exito): ?>
                                            <tr>
                                                <td class="text-center text-muted fw-bold"><?= $idx + 1; ?></td>
                                                <td class="text-center">
                                                    <span class="badge bg-light text-dark border px-2 py-1">
                                                        Fila <?= htmlspecialchars($exito['fila']); ?>
                                                    </span>
                                                </td>
                                                <td class="fw-bold text-dark"><?= htmlspecialchars($exito['documento']); ?></td>
                                                <td><?= htmlspecialchars($exito['nombre']); ?></td>
                                                <td>
                                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2 py-1">
                                                        <i class="fa-solid fa-graduation-cap me-1"></i> Ficha <?= htmlspecialchars($exito['ficha']); ?>
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-success px-2 py-1">
                                                        <i class="fa-solid fa-check me-1"></i> Vinculado
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <script>
            document.addEventListener("DOMContentLoaded", function() {
                // Alerta SweetAlert2 del resultado de carga
                <?php if (!empty($resultadoCarga['mensaje'])): ?>
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: '<?= ($resultadoCarga['tipo'] === 'success') ? 'success' : (($resultadoCarga['tipo'] === 'warning') ? 'warning' : 'error'); ?>',
                            title: '<?= ($resultadoCarga['tipo'] === 'success') ? 'Carga Exitosa' : (($resultadoCarga['tipo'] === 'warning') ? 'Carga con Advertencias' : 'Inconsistencias Detectadas'); ?>',
                            text: '<?= addslashes($resultadoCarga['mensaje']); ?>',
                            confirmButtonColor: '#00af00'
                        });
                    }
                <?php endif; ?>

                // Automatizar tabla de registros exitosos de forma sencilla y ligera
                if (window.jQuery && $.fn && $.fn.DataTable && $('#tablaExitosCarga').length) {
                    if (!$.fn.DataTable.isDataTable('#tablaExitosCarga')) {
                        $('#tablaExitosCarga').DataTable({
                            pageLength: 10,
                            lengthMenu: [10, 25, 50, 100],
                            language: {
                                search: "Buscar aprendiz:",
                                lengthMenu: "Ver _MENU_ filas",
                                info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
                                infoEmpty: "0 registros disponibles",
                                infoFiltered: "(filtrado de _MAX_ registros)",
                                zeroRecords: "No se encontraron coincidencias",
                                paginate: {
                                    first: "Primero",
                                    last: "Último",
                                    next: "Siguiente",
                                    previous: "Anterior"
                                }
                            },
                            order: [[0, 'asc']],
                            responsive: true
                        });
                    }
                }
            });
        </script>
    <?php endif; ?>
</div>


