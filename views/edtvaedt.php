<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'controllers/edtcaedt.php';

$pgActual = isset($_GET['pg']) ? $_GET['pg'] : '1601';
$idusu = isset($_SESSION['idusu']) ? $_SESSION['idusu'] : NULL;
$idper = isset($_SESSION['idper']) ? $_SESSION['idper'] : NULL;

// Roles autorizados
// 49: Coordinador EDT, 48: Administrador EDT, 1: Funcionario (Admin General), 2: Administrador Centro, 35: Contratista
$esCoordinador  = ($idper == 49 || $idper == 48 || $idper == 1 || $idper == 2);
$esAdminEdu     = ($idper == 48 || $idper == 1 || $idper == 2);
$esAdminGeneral = ($idper == 1 || $idper == 2);

// Métricas de aprobación
$total_solicitudes = is_array($datAll) ? count($datAll) : 0;
$c_pend_coord = 0;
$c_pend_admin = 0;
$c_aprobadas  = 0;
$c_rechazadas = 0;
$c_matriculadas = 0;

if ($datAll) {
    foreach ($datAll as $dt) {
        $est = $dt['convht'];
        if ($est === 'Pendiente Coordinador') {
            $c_pend_coord++;
        } elseif ($est === 'Aprobado Coordinador') {
            $c_pend_admin++;
        } elseif ($est === 'Aprobado Admin Educativa' || strpos($est, 'Listado Cargado') === 0) {
            $c_aprobadas++;
        } elseif ($est === 'Matriculado') {
            $c_aprobadas++;
            $c_matriculadas++;
        } elseif (strpos($est, 'Rechazado') !== false) {
            $c_rechazadas++;
        }
    }
}
?>

<div class="conte">
    <!-- Encabezado de página -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 pb-2 border-bottom">
        <div>
            <h2 class="h4 fw-bold text-dark mb-1">
                <i class="fa-solid fa-clipboard-check text-success me-2"></i>Aprobación de Solicitudes EDT
            </h2>
            <p class="text-muted small mb-0">Gestión, revisión, aprobación y seguimiento de Eventos de Divulgación Tecnológica</p>
        </div>
        <div class="d-flex gap-2">
            <a href="home.php?pg=1600" class="btn btn-outline-success btn-sm d-flex align-items-center gap-1 shadow-sm">
                <i class="fa-solid fa-file-signature"></i>
                <span>Ver / Nueva Solicitud EDT</span>
            </a>
        </div>
    </div>

    <!-- Tarjetas KPI estilo vvidgen (sin relleno de fondo, color en icono) -->
    <div class="row g-3 mb-4">
        <!-- Pend. Coordinador -->
        <div class="col-12 col-sm-6 col-md-3">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #b45309;">Pend. Coordinador</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #b45309;"><?= $c_pend_coord; ?></span>
                            <span style="font-size: 13px; color: #b45309;">solicitudes</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box" style="background-color: #fef3c7; color: #b45309;">
                        <i class="fa-solid fa-hourglass-half fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 11px; color: #b45309;">
                        <i class="fa-solid fa-clock-rotate-left" style="font-size: 12px;"></i> Etapa 1
                    </span>
                    <span style="font-size: 11px; color: #3e4a3d;">Revisión Coord.</span>
                </div>
            </div>
        </div>

        <!-- Pend. Administración -->
        <div class="col-12 col-sm-6 col-md-3">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #0284c7;">Pend. Administración</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #0284c7;"><?= $c_pend_admin; ?></span>
                            <span style="font-size: 13px; color: #0284c7;">solicitudes</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box" style="background-color: #b5dcfe; color: #0284c7;">
                        <i class="fa-solid fa-user-check fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 11px; color: #0284c7;">
                        <i class="fa-solid fa-file-signature" style="font-size: 12px;"></i> Etapa 2
                    </span>
                    <span style="font-size: 11px; color: #3e4a3d;">Admin Educativa</span>
                </div>
            </div>
        </div>

        <!-- Aprobadas / Proceso -->
        <div class="col-12 col-sm-6 col-md-3">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #006b29;">Aprobadas / Proceso</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #006b29;"><?= $c_aprobadas; ?></span>
                            <span style="font-size: 13px; color: #006b29;">activas</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box" style="background-color: #7afd88; color: #006b24;">
                        <i class="fa-solid fa-circle-check fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="fw-semibold d-inline-flex align-items-center gap-2" style="font-size: 11px; color: #006b29;">
                        <span class="pulse-dot"></span> Formación Activa
                    </span>
                    <span style="font-size: 11px; color: #3e4a3d;"><?= $c_matriculadas; ?> matriculadas</span>
                </div>
            </div>
        </div>

        <!-- Rechazadas -->
        <div class="col-12 col-sm-6 col-md-3">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #ba1a1a;">Rechazadas</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #ba1a1a;"><?= $c_rechazadas; ?></span>
                            <span style="font-size: 13px; color: #ba1a1a;">observadas</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box" style="background-color: #ffdad6; color: #ba1a1a;">
                        <i class="fa-solid fa-circle-xmark fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="d-inline-flex align-items-center gap-1" style="font-size: 11px; color: #ba1a1a;">
                        <i class="fa-solid fa-triangle-exclamation" style="font-size: 12px;"></i> Con observaciones
                    </span>
                    <span class="fw-semibold" style="font-size: 11px; color: #3c627f;">Para ajuste</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Pestañas de Filtro Rápido -->
    <ul class="nav nav-pills mb-3 gap-1 bg-white p-2 rounded shadow-sm border" id="filtroTabs" role="tablist">
        <li class="nav-item">
            <button class="nav-link active btn-sm" onclick="filtrarEstado('')">
                Todas <span class="badge bg-secondary ms-1"><?= $total_solicitudes; ?></span>
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link btn-sm text-warning-emphasis" onclick="filtrarEstado('Pendiente Coordinador')">
                Pendientes Coordinador <span class="badge bg-warning text-dark ms-1"><?= $c_pend_coord; ?></span>
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link btn-sm text-primary" onclick="filtrarEstado('Aprobado Coordinador')">
                Pendientes Administración <span class="badge bg-primary ms-1"><?= $c_pend_admin; ?></span>
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link btn-sm text-success" onclick="filtrarEstado('Aprobado')">
                Aprobadas <span class="badge bg-success ms-1"><?= $c_aprobadas; ?></span>
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link btn-sm text-danger" onclick="filtrarEstado('Rechazado')">
                Rechazadas <span class="badge bg-danger ms-1"><?= $c_rechazadas; ?></span>
            </button>
        </li>
    </ul>

    <!-- Tabla Principal de Solicitudes EDT para Aprobación -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-3">
            <div class="table-responsive">
                <table id="tblAprobaciones" class="table table-hover table-striped align-middle w-100" style="font-size: 0.88rem;">
                    <thead class="table-dark text-nowrap">
                        <tr>
                            <th style="width: 5%;">Rad.</th>
                            <th style="width: 25%;">Evento / Solicitud</th>
                            <th style="width: 18%;">Programa / Empresa</th>
                            <th style="width: 12%;">Solicitante</th>
                            <th style="width: 12%;">Cupos & Fechas</th>
                            <th style="width: 13%;">Estado Actual</th>
                            <th style="width: 15%; text-align: center;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($datAll) { foreach ($datAll as $dt) { 
                            $raw_est = $dt['convht'] ? $dt['convht'] : 'Pendiente Coordinador';
                            $estado = $raw_est;
                            $ruta_listado = NULL;
                            if (strpos($raw_est, 'Listado Cargado:') === 0) {
                                $estado = 'Listado Cargado';
                                $ruta_listado = substr($raw_est, strlen('Listado Cargado:'));
                            }

                            // Determinar badges
                            $badgeClass = 'bg-secondary';
                            if ($estado === 'Pendiente Coordinador') $badgeClass = 'bg-warning text-dark';
                            elseif ($estado === 'Aprobado Coordinador') $badgeClass = 'bg-info text-white';
                            elseif ($estado === 'Aprobado Admin Educativa') $badgeClass = 'bg-primary text-white';
                            elseif ($estado === 'Listado Cargado') $badgeClass = 'bg-secondary text-white';
                            elseif ($estado === 'Matriculado') $badgeClass = 'bg-success text-white';
                            elseif (strpos($estado, 'Rechazado') !== false) $badgeClass = 'bg-danger text-white';

                            // Permisos de acción en esta fila
                            $puedeAprobarCoord = ($esCoordinador && $estado === 'Pendiente Coordinador');
                            $puedeAprobarAdmin = ($esAdminEdu && $estado === 'Aprobado Coordinador');
                            $puedeCargarLista  = ($esAdminEdu && $estado === 'Aprobado Admin Educativa');
                            $puedeMatricular   = ($esAdminEdu && $estado === 'Listado Cargado');
                        ?>
                        <tr>
                            <td class="fw-bold text-center">#<?= $dt['idnorad']; ?></td>
                            <td>
                                <div class="fw-semibold text-dark">
                                    <?= htmlspecialchars($dt['denominacion'] ? $dt['denominacion'] : ($dt['codslem'] ? $dt['codslem'] : 'Solicitud EDT #' . $dt['idnorad'])); ?>
                                </div>
                                <div class="text-muted small">
                                    Tipo: <span class="badge bg-light text-dark border"><?= htmlspecialchars($dt['tipo_edt'] ? $dt['tipo_edt'] : 'N/A'); ?></span>
                                    <?php if ($dt['duracion']) { ?>
                                        &bull; <strong><?= $dt['duracion']; ?> hrs</strong>
                                    <?php } ?>
                                </div>
                            </td>
                            <td>
                                <div class="text-truncate" style="max-width: 220px;" title="<?= htmlspecialchars($dt['nompro']); ?>">
                                    <i class="fa-solid fa-graduation-cap text-success me-1"></i><?= htmlspecialchars($dt['nompro'] ? $dt['nompro'] : 'Sin programa'); ?>
                                </div>
                                <div class="text-muted small text-truncate" style="max-width: 220px;" title="<?= htmlspecialchars($dt['nomemp']); ?>">
                                    <i class="fa-solid fa-building text-secondary me-1"></i><?= htmlspecialchars($dt['nomemp'] ? $dt['nomemp'] : 'Sin empresa'); ?>
                                </div>
                            </td>
                            <td>
                                <div><i class="fa-solid fa-user me-1 text-muted"></i><?= htmlspecialchars($dt['nomusu'] ? $dt['nomusu'] : 'No asignado'); ?></div>
                            </td>
                            <td>
                                <div><strong>Cupos:</strong> <?= $dt['cupo'] ? $dt['cupo'] : '0'; ?></div>
                                <div class="text-muted small">
                                    <?= $dt['feclini'] ? date('d/m/Y', strtotime($dt['feclini'])) : '--'; ?> al 
                                    <?= $dt['feclin'] ? date('d/m/Y', strtotime($dt['feclin'])) : '--'; ?>
                                </div>
                            </td>
                            <td>
                                <span class="badge <?= $badgeClass; ?> p-2 d-inline-block text-wrap" style="font-size: 0.78rem;">
                                    <?= htmlspecialchars($estado); ?>
                                </span>
                                <?php if ($dt['idfic']) { ?>
                                    <div class="mt-1"><span class="badge bg-dark"><i class="fa-solid fa-hashtag"></i> Ficha: <?= htmlspecialchars($dt['idfic']); ?></span></div>
                                <?php } ?>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm mb-1">
                                    <!-- Botón Ver / Revisar Detalle Completo -->
                                    <button type="button" class="btn btn-outline-primary" onclick="verDetalle(<?= htmlspecialchars(json_encode($dt)); ?>, '<?= htmlspecialchars($estado); ?>')" title="Ver detalles y revisar">
                                        <i class="fa-solid fa-eye"></i> Revisar
                                    </button>
                                </div>

                                <!-- Acciones Rápidas Directas -->
                                <?php if ($puedeAprobarCoord) { ?>
                                    <div class="d-flex gap-1 justify-content-center mt-1">
                                        <form action="home.php?pg=<?= $pgActual; ?>" method="POST" onsubmit="return confirm('¿Aprobar solicitud como Coordinador?');">
                                            <input type="hidden" name="idnorad" value="<?= $dt['idnorad']; ?>">
                                            <input type="hidden" name="ope" value="aprobar_coordinador">
                                            <button type="submit" class="btn btn-sm btn-warning text-dark py-0 px-2 fw-semibold" title="Aprobar Coordinador">
                                                <i class="fa-solid fa-check"></i> Aprobar Coord.
                                            </button>
                                        </form>
                                        <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2" onclick="abrirModalRechazo(<?= $dt['idnorad']; ?>, 'rechazar_coordinador')" title="Rechazar">
                                            <i class="fa-solid fa-xmark"></i>
                                        </button>
                                    </div>
                                <?php } ?>

                                <?php if ($puedeAprobarAdmin) { ?>
                                    <div class="d-flex gap-1 justify-content-center mt-1">
                                        <form action="home.php?pg=<?= $pgActual; ?>" method="POST" onsubmit="return confirm('¿Aprobar solicitud como Administración Educativa?');">
                                            <input type="hidden" name="idnorad" value="<?= $dt['idnorad']; ?>">
                                            <input type="hidden" name="ope" value="aprobar_admin">
                                            <button type="submit" class="btn btn-sm btn-info text-white py-0 px-2 fw-semibold" title="Aprobar Admin">
                                                <i class="fa-solid fa-check-double"></i> Aprobar Admin
                                            </button>
                                        </form>
                                        <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2" onclick="abrirModalRechazo(<?= $dt['idnorad']; ?>, 'rechazar_admin')" title="Rechazar">
                                            <i class="fa-solid fa-xmark"></i>
                                        </button>
                                    </div>
                                <?php } ?>

                                <?php if ($puedeCargarLista) { ?>
                                    <div class="mt-1">
                                        <button type="button" class="btn btn-sm btn-primary py-0 px-2 w-100" onclick="abrirModalListado(<?= $dt['idnorad']; ?>)">
                                            <i class="fa-solid fa-upload"></i> Subir Listado
                                        </button>
                                    </div>
                                <?php } ?>

                                <?php if ($puedeMatricular) { ?>
                                    <div class="mt-1">
                                        <button type="button" class="btn btn-sm btn-success py-0 px-2 w-100" onclick="abrirModalMatricula(<?= $dt['idnorad']; ?>)">
                                            <i class="fa-solid fa-graduation-cap"></i> Matricular
                                        </button>
                                    </div>
                                <?php } ?>

                                <?php if ($ruta_listado && file_exists($ruta_listado)) { ?>
                                    <div class="mt-1">
                                        <a href="<?= $ruta_listado; ?>" target="_blank" class="btn btn-sm btn-outline-secondary py-0 px-2">
                                            <i class="fa-solid fa-file-arrow-down"></i> Ver Lista
                                        </a>
                                    </div>
                                <?php } ?>
                            </td>
                        </tr>
                        <?php }} ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- MODAL: DETALLE Y REVISIÓN COMPLETA DE SOLICITUD EDT -->
<div class="modal fade" id="modalDetalleEDT" tabindex="-1" aria-labelledby="modalDetalleLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold" id="modalDetalleLabel">
                    <i class="fa-solid fa-file-invoice me-2"></i>Revisión de Solicitud EDT
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" id="modalDetalleBody">
                <!-- Se llena dinámicamente con JavaScript -->
            </div>
            <div class="modal-footer bg-light" id="modalDetalleFooter">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL: RECHAZAR SOLICITUD -->
<div class="modal fade" id="modalRechazo" tabindex="-1" aria-labelledby="modalRechazoLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <form action="home.php?pg=<?= $pgActual; ?>" method="POST">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title fw-bold" id="modalRechazoLabel">
                        <i class="fa-solid fa-triangle-exclamation me-2"></i>Rechazar Solicitud EDT
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-3">
                    <input type="hidden" name="idnorad" id="rechazo_idnorad" value="">
                    <input type="hidden" name="ope" id="rechazo_ope" value="">
                    
                    <p class="text-muted small">Indique el motivo u observaciones del rechazo para que el solicitante pueda corregirlo:</p>
                    <div class="mb-3">
                        <label for="comentario_rechazo" class="form-label fw-bold">Motivo del Rechazo *</label>
                        <textarea class="form-control" name="comentario" id="comentario_rechazo" rows="3" required placeholder="Escriba la justificación detallada..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Confirmar Rechazo</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL: SUBIR LISTADO -->
<div class="modal fade" id="modalListado" tabindex="-1" aria-labelledby="modalListadoLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <form action="home.php?pg=<?= $pgActual; ?>" method="POST" enctype="multipart/form-data">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold" id="modalListadoLabel">
                        <i class="fa-solid fa-upload me-2"></i>Cargar Listado de Participantes
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-3">
                    <input type="hidden" name="idnorad" id="listado_idnorad" value="">
                    <input type="hidden" name="ope" value="subir_listado">
                    <div class="mb-3">
                        <label for="archivo_listado" class="form-label fw-bold">Seleccionar Archivo (.pdf, .xlsx, .csv)</label>
                        <input type="file" name="listado" id="archivo_listado" class="form-control" required accept=".pdf,.csv,.xlsx,.xls">
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Subir Archivo</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL: MATRICULAR / ASIGNAR FICHA -->
<div class="modal fade" id="modalMatricula" tabindex="-1" aria-labelledby="modalMatriculaLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <form action="home.php?pg=<?= $pgActual; ?>" method="POST">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title fw-bold" id="modalMatriculaLabel">
                        <i class="fa-solid fa-graduation-cap me-2"></i>Finalizar y Matricular Ficha
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-3">
                    <input type="hidden" name="idnorad" id="matricula_idnorad" value="">
                    <input type="hidden" name="ope" value="matricular">
                    <div class="mb-3">
                        <label for="idfic_input" class="form-label fw-bold">Número de Ficha Creada en SOFIA Plus *</label>
                        <input type="text" name="idfic" id="idfic_input" class="form-control" required placeholder="Ej: 2987654">
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Guardar y Matricular</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Inicializar DataTable
    let tablaAprob = null;
    $(document).ready(function() {
        if ($('#tblAprobaciones').length) {
            tablaAprob = $('#tblAprobaciones').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                },
                order: [[0, 'desc']],
                pageLength: 10,
                responsive: true
            });
        }
    });

    function filtrarEstado(estado) {
        if (tablaAprob) {
            tablaAprob.column(5).search(estado).draw();
        }
    }

    function abrirModalRechazo(idnorad, ope) {
        document.getElementById('rechazo_idnorad').value = idnorad;
        document.getElementById('rechazo_ope').value = ope;
        document.getElementById('comentario_rechazo').value = '';
        var myModal = new bootstrap.Modal(document.getElementById('modalRechazo'));
        myModal.show();
    }

    function abrirModalListado(idnorad) {
        document.getElementById('listado_idnorad').value = idnorad;
        var myModal = new bootstrap.Modal(document.getElementById('modalListado'));
        myModal.show();
    }

    function abrirModalMatricula(idnorad) {
        document.getElementById('matricula_idnorad').value = idnorad;
        var myModal = new bootstrap.Modal(document.getElementById('modalMatricula'));
        myModal.show();
    }

    function verDetalle(dt, estado) {
        let denominacion = dt.denominacion || dt.codslem || 'Solicitud #' + dt.idnorad;
        let html = `
            <div class="d-flex justify-content-between align-items-center mb-3 p-3 bg-light rounded border">
                <div>
                    <h5 class="fw-bold mb-1">${denominacion}</h5>
                    <div class="text-muted small">Radicado: <strong>#${dt.idnorad}</strong> | Tipo: <strong>${dt.tipo_edt || 'N/A'}</strong></div>
                </div>
                <div>
                    <span class="badge bg-success p-2">${estado}</span>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <div class="card p-3 h-100 bg-white border">
                        <h6 class="fw-bold text-success border-bottom pb-2"><i class="fa-solid fa-circle-info me-1"></i> Información General</h6>
                        <ul class="list-unstyled small mb-0 space-y-1">
                            <li><strong>Programa:</strong> ${dt.nompro || 'N/A'}</li>
                            <li><strong>Empresa:</strong> ${dt.nomemp || 'N/A'}</li>
                            <li><strong>NIT Empresa:</strong> ${dt.nitemp || dt.numdocemp || 'N/A'}</li>
                            <li><strong>Código SENA:</strong> ${dt.codsena || 'N/A'}</li>
                            <li><strong>Sector Económico:</strong> ${dt.sector_econ || 'N/A'}</li>
                            <li><strong>Modalidad:</strong> ${dt.modalidad_prog || 'N/A'}</li>
                            <li><strong>Tipo Convenio:</strong> ${dt.tipo_convenio || 'N/A'}</li>
                        </ul>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card p-3 h-100 bg-white border">
                        <h6 class="fw-bold text-success border-bottom pb-2"><i class="fa-solid fa-calendar-days me-1"></i> Logística y Fechas</h6>
                        <ul class="list-unstyled small mb-0 space-y-1">
                            <li><strong>Fechas:</strong> ${dt.feclini || '--'} al ${dt.feclin || '--'}</li>
                            <li><strong>Horas Totales:</strong> ${dt.duracion || '0'} hrs</li>
                            <li><strong>Cupos Solicitados:</strong> ${dt.cupo || '0'}</li>
                            <li><strong>Lugar de Realización:</strong> ${dt.lugar_edt || 'N/A'}</li>
                            <li><strong>Horario Propuesto:</strong> ${dt.horario_propuesto || 'N/A'}</li>
                            <li><strong>Responsable:</strong> ${dt.responsable || dt.nomusu || 'N/A'}</li>
                        </ul>
                    </div>
                </div>

                <div class="col-12">
                    <div class="card p-3 bg-white border">
                        <h6 class="fw-bold text-success border-bottom pb-2"><i class="fa-solid fa-bullseye me-1"></i> Aspectos Técnicos y Pedagógicos</h6>
                        <div class="small mb-2"><strong>Objetivos:</strong><p class="text-muted mb-1">${dt.objetivos || 'No registrados'}</p></div>
                        <div class="small mb-2"><strong>Temas a Desarrollar:</strong><p class="text-muted mb-1">${dt.temas || 'No registrados'}</p></div>
                        <div class="small mb-2"><strong>Población Objetivo:</strong><p class="text-muted mb-1">${dt.poblacion || 'No registrada'}</p></div>
                        <div class="small mb-2"><strong>Metodología:</strong><p class="text-muted mb-1">${dt.metodologia || 'No registrada'}</p></div>
                        <div class="small mb-2"><strong>Resultados Esperados:</strong><p class="text-muted mb-1">${dt.resultados || 'No registrados'}</p></div>
                        <div class="small mb-0"><strong>Perfil del Orientador:</strong><p class="text-muted mb-0">${dt.perfil_orientador || 'No registrado'}</p></div>
                    </div>
                </div>

                ${dt.observaciones ? `
                <div class="col-12">
                    <div class="card p-3 bg-light border">
                        <h6 class="fw-bold text-dark border-bottom pb-2"><i class="fa-solid fa-comment-dots me-1"></i> Historial y Observaciones</h6>
                        <pre class="small text-muted mb-0" style="white-space: pre-wrap; font-family: inherit;">${dt.observaciones}</pre>
                    </div>
                </div>
                ` : ''}
            </div>
        `;

        document.getElementById('modalDetalleBody').innerHTML = html;

        // Armar botones del footer según estado
        let footerHtml = `<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>`;
        
        if (estado === 'Pendiente Coordinador' && <?= $esCoordinador ? 'true' : 'false'; ?>) {
            footerHtml += `
                <button type="button" class="btn btn-danger" onclick="abrirModalRechazo(${dt.idnorad}, 'rechazar_coordinador')" data-bs-dismiss="modal">
                    <i class="fa-solid fa-xmark"></i> Rechazar
                </button>
                <form action="home.php?pg=<?= $pgActual; ?>" method="POST" style="display:inline;" onsubmit="return confirm('¿Aprobar solicitud como Coordinador?');">
                    <input type="hidden" name="idnorad" value="${dt.idnorad}">
                    <input type="hidden" name="ope" value="aprobar_coordinador">
                    <button type="submit" class="btn btn-warning text-dark fw-bold">
                        <i class="fa-solid fa-check"></i> Aprobar Coordinador
                    </button>
                </form>
            `;
        } else if (estado === 'Aprobado Coordinador' && <?= $esAdminEdu ? 'true' : 'false'; ?>) {
            footerHtml += `
                <button type="button" class="btn btn-danger" onclick="abrirModalRechazo(${dt.idnorad}, 'rechazar_admin')" data-bs-dismiss="modal">
                    <i class="fa-solid fa-xmark"></i> Rechazar
                </button>
                <form action="home.php?pg=<?= $pgActual; ?>" method="POST" style="display:inline;" onsubmit="return confirm('¿Aprobar solicitud como Administración Educativa?');">
                    <input type="hidden" name="idnorad" value="${dt.idnorad}">
                    <input type="hidden" name="ope" value="aprobar_admin">
                    <button type="submit" class="btn btn-info text-white fw-bold">
                        <i class="fa-solid fa-check-double"></i> Aprobar Admin Educativa
                    </button>
                </form>
            `;
        }

        document.getElementById('modalDetalleFooter').innerHTML = footerHtml;

        var myModal = new bootstrap.Modal(document.getElementById('modalDetalleEDT'));
        myModal.show();
    }
</script>
