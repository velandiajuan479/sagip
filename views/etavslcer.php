<?php require_once 'controllers/etacslcer.php'; ?>

<div class="conte">
    <!-- 1. TÍTULO DEL MÓDULO -->
    <?= titulo2("<i class='$icono'></i> Mis Certificaciones", 2); ?>

    <!-- 2. MENSAJES DEL SISTEMA -->
    <?php if (!empty($mensaje)): ?>
        <div class="alert alert-<?= $tipoMensaje === 'error' ? 'danger' : $tipoMensaje; ?> alert-dismissible fade show" role="alert">
            <?= $mensaje; ?>
            <a href="home.php?pg=<?= $pg; ?>" class="btn-close" aria-label="Cerrar"></a>
        </div>
    <?php endif; ?>

    <!-- ===================== Dashboard KPI (Estilo etavgeins) ===================== -->
    <div class="row my-4 g-4">

        <!-- Pendientes -->
        <div class="col-12 col-md-6 col-xl-3">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #b76e00;">Pendientes</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #0b1c30;"><?= $datEstadisticasSolicitudes['pendientes'] ?? 0; ?></span>
                            <span style="font-size: 13px; color: #3e4a3d;">por revisar</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box" style="background-color: #ffe9c7; color: #b76e00;">
                        <i class="fa-solid fa-file-lines fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="fw-semibold d-inline-flex align-items-center gap-2" style="font-size: 11px; color: #b76e00;">
                        <span class="pulse-dot" style="background-color: #b76e00;"></span> Requieren atención
                    </span>
                    <span style="font-size: 11px; color: #3e4a3d;">En cola</span>
                </div>
            </div>
        </div>

        <!-- En Espera de Firma -->
        <div class="col-12 col-md-6 col-xl-3">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #3c627f;">En Espera de Firma</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #0b1c30;"><?= $datEstadisticasSolicitudes['esperando_firma'] ?? 0; ?></span>
                            <span style="font-size: 13px; color: #3e4a3d;">documentos</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box" style="background-color: #b5dcfe; color: #3c627f;">
                        <i class="fa-solid fa-file-signature fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 11px; color: #3c627f;">
                        <i class="fa-solid fa-pen-nib" style="font-size: 12px;"></i> Pendientes de firma
                    </span>
                    <span style="font-size: 11px; color: #3e4a3d;">Subdirección</span>
                </div>
            </div>
        </div>

        <!-- Completadas -->
        <div class="col-12 col-md-6 col-xl-3">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #006b29;">Completadas</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #006b29;"><?= $datEstadisticasSolicitudes['completadas'] ?? 0; ?></span>
                            <span style="font-size: 13px; color: rgba(0, 107, 41, 0.8);">finalizadas</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box" style="background-color: #7afd88; color: #006b24;">
                        <i class="fa-solid fa-circle-check fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="fw-semibold d-inline-flex align-items-center gap-2" style="font-size: 11px; color: #006b29;">
                        <span class="pulse-dot"></span> Proceso cerrado
                    </span>
                    <span style="font-size: 11px; color: #3e4a3d;">Entregadas</span>
                </div>
            </div>
        </div>

        <!-- Total Solicitudes -->
        <div class="col-12 col-md-6 col-xl-3">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #0891b2;">Total Solicitudes</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #0b1c30;"><?= $datEstadisticasSolicitudes['total'] ?? 0; ?></span>
                            <span style="font-size: 13px; color: #3e4a3d;">registradas</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box" style="background-color: #c9ecf5; color: #0891b2;">
                        <i class="fa-solid fa-folder-open fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 11px; color: #0891b2;">
                        <i class="fa-solid fa-chart-simple" style="font-size: 12px;"></i> Histórico general
                    </span>
                    <span style="font-size: 11px; color: #3e4a3d;">Acumulado</span>
                </div>
            </div>
        </div>

    </div>

    <!-- MIS PROGRAMAS Y CERTIFICACIONES DISPONIBLES -->
    <?php if (!empty($datProgramasAprendiz)): ?>
        <h2 class="mb-0">Mis Programas de Formación</h2> 
        <div class="card mb-4 mt-3 shadow-sm">
            <div class="card-body">
                <!-- Pestañas de programas (scrollable en móviles) -->
                <ul class="nav nav-tabs flex-nowrap overflow-auto pb-1" id="programasTabs" role="tablist">
                    <?php foreach ($datProgramasAprendiz as $index => $programa): ?>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link text-nowrap" 
                                    id="programa-<?= $programa['idfic']; ?>-tab" 
                                    data-bs-toggle="tab" 
                                    data-bs-target="#programa-<?= $programa['idfic']; ?>" 
                                    data-toggle="tab"
                                    data-target="#programa-<?= $programa['idfic']; ?>"
                                    type="button" role="tab">
                                <span class="badge bg-<?= getBadgeColorPrograma($programa['tipo_programa']); ?> me-1">
                                    <?= strtoupper($programa['tipo_programa']); ?>
                                </span>
                                <?= htmlspecialchars(substr($programa['nombre_programa'], 0, 22)); ?>
                                <?= strlen($programa['nombre_programa']) > 22 ? '...' : ''; ?>
                                <span class="badge bg-secondary ms-1"><?= round($programa['porcentaje_avance']); ?>%</span>
                            </button>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <!-- Contenido de las pestañas -->
                <div class="tab-content" id="programasTabsContent">
                    
                    <!-- Mensaje inicial antes de seleccionar un programa -->
                    <div id="mensaje-seleccionar-programa" class="alert alert-success text-center my-4 py-1 px-2">
                        Selecciona un programa de formación arriba para visualizar sus detalles y certificaciones disponibles.
                    </div>

                    <?php foreach ($datProgramasAprendiz as $index => $programa): ?>
                        <!-- NOTA: Se quitaron las clases 'show active' para que permanezca oculto hasta el clic -->
                        <div class="tab-pane fade" 
                             id="programa-<?= $programa['idfic']; ?>" 
                             role="tabpanel">
                            
                            <!-- Información detallada del programa con barra horizontal de avance -->
                            <div class="card mt-3 border shadow-sm">
                                <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
                                    <h5 class="mb-0"><i class="fa fa-info-circle me-1"></i> Detalles del Programa</h5>
                                    <span class="badge bg-<?= getEstadoPrograma($programa['estado_programa']); ?>">
                                        <?= ucfirst($programa['estado_programa']); ?>
                                    </span>
                                </div>
                                <div class="card-body">
                                    <div class="row align-items-center g-3">
                                        <div class="col-lg-7 col-md-12">
                                            <h5 class="text-success fw-bold mb-2"><?= htmlspecialchars($programa['nombre_programa']); ?></h5>
                                            <div class="row g-2 text-muted">
                                                <div class="col-6 col-sm-3">
                                                    <span class="d-block fw-bold">Tipo:</span>
                                                    <span class="badge bg-<?= getBadgeColorPrograma($programa['tipo_programa']); ?>">
                                                        <?= strtoupper($programa['tipo_programa']); ?>
                                                    </span>
                                                </div>
                                                <div class="col-6 col-sm-3">
                                                    <span class="d-block fw-bold">Ficha:</span>
                                                    <code><?= $programa['idfic']; ?></code>
                                                </div>
                                                <div class="col-6 col-sm-3">
                                                    <span class="d-block fw-bold">Inicio:</span>
                                                    <span><?= date('d/m/Y', strtotime($programa['fecha_inicio'])); ?></span>
                                                </div>
                                                <div class="col-6 col-sm-3">
                                                    <span class="d-block fw-bold">Trimestre:</span>
                                                    <span><?= $programa['trimestre_actual']; ?>/<?= $programa['duracion_trimestres']; ?></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-5 col-md-12">
                                            <div class="bg-light p-3 rounded border">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <span class="small fw-bold text-secondary">
                                                        <i class="fa fa-tasks me-1 text-success"></i>Avance del Programa
                                                    </span>
                                                    <span class="fw-bold text-success"><?= round($programa['porcentaje_avance']); ?>%</span>
                                                </div>
                                                <div class="progress mb-2" style="height: 10px;">
                                                    <div class="progress-bar bg-success progress-bar-striped progress-bar-animated" 
                                                         role="progressbar" 
                                                         style="width: <?= min(100, max(0, round($programa['porcentaje_avance']))); ?>%;" 
                                                         aria-valuenow="<?= round($programa['porcentaje_avance']); ?>" 
                                                         aria-valuemin="0" 
                                                         aria-valuemax="100">
                                                    </div>
                                                </div>
                                                <div class="d-flex justify-content-between small text-muted">
                                                    <span><strong>Bitácoras:</strong> <?= $programa['bitacoras_completas']; ?>/<?= $programa['total_bitacoras']; ?></span>
                                                    <span><strong>Completado:</strong> <?= round($programa['porcentaje_avance']); ?>%</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Certificaciones disponibles para este programa -->
                            <?php 
                            $certificacionesPrograma = [];
                            foreach ($datCertificacionesDisponibles as $item) {
                                if ($item['programa']['idfic'] == $programa['idfic']) {
                                    $certificacionesPrograma = $item['certificaciones_disponibles'];
                                    break;
                                }
                            }
                            ?>

                            <?php if (!empty($certificacionesPrograma)): ?>
                                
                                <div class="card mt-3 shadow-sm">
                                    <div class="card-header bg-light py-2">
                                        <h5 class="mb-0"><i class="fa fa-certificate me-1"></i> Certificaciones Disponibles</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <?php foreach ($certificacionesPrograma as $cert): ?>
                                                <?php $cumpleRequisitos = !empty($cert['requisitos_cumplidos']); ?>
                                                <div class="col-xl-4 col-md-6 col-12 d-flex align-items-stretch">
                                                    <div class="card border-<?= $cumpleRequisitos ? 'success' : 'warning'; ?> w-100 shadow-sm d-flex flex-column justify-content-between text-center">
                                                        <div class="card-body p-3 d-flex flex-column justify-content-between text-center">
                                                            <div>
                                                                <div class="mb-2">
                                                                    <i class="fa <?= getIconoCertificacion($cert['tipo']); ?> fa-2x text-<?= $cumpleRequisitos ? 'success' : 'warning'; ?>"></i>
                                                                </div>
                                                                <h6 class="card-title mb-1 fw-bold text-truncate" title="<?= htmlspecialchars($cert['nombre']); ?>">
                                                                    <?= htmlspecialchars($cert['nombre']); ?>
                                                                </h6>
                                                                <span class="badge bg-light text-secondary border small mb-2">
                                                                    <?= formatearTipoCertificacion($cert['tipo']); ?>
                                                                </span>
                                                                <p class="card-text small text-muted mb-3"><?= htmlspecialchars($cert['descripcion']); ?></p>
                                                            </div>
                                                            
                                                            <div class="d-flex justify-content-center align-items-center pt-2 border-top mt-auto text-center">
                                                                <?php if ($cumpleRequisitos): ?>
                                                                    <button type="button" 
                                                                        class="btn btn-success px-3 py-1 d-inline-flex align-items-center justify-content-center"
                                                                        onclick="solicitarCertificacionPrograma('<?= $cert['tipo']; ?>', '<?= htmlspecialchars($cert['nombre']); ?>', '<?= $programa['idfic']; ?>', '<?= htmlspecialchars($programa['nombre_programa']); ?>')">
                                                                        Solicitar
                                                                    </button>
                                                                <?php else: ?>
                                                                    <span class="badge bg-warning text-dark py-1 px-2">
                                                                        <i class="fa fa-clock-o me-1"></i> Pendiente
                                                                    </span>
                                                                    <small class="text-muted ms-2">Próximamente</small>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-info mt-3">
                                    <i class="fa fa-info-circle me-1"></i> No hay certificaciones disponibles para tu progreso actual en este programa.
                                </div>
                            <?php endif; ?> 
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-warning">
            <i class="fa fa-exclamation-triangle"></i> No tienes programas de formación asignados actualmente.
            <br>Contacta con tu coordinador académico para más información.
        </div>
    <?php endif; ?>

    <!-- 6. HISTORIAL GENERAL DE SOLICITUDES -->
    <div class="d-flex justify-content-between align-items-center mt-4 mb-3">
        <h2 class="mb-0">Historial General de Solicitudes</h2>
        <?php if (!empty($datMisSolicitudes)): ?>
            <span class="badge bg-success"><?= count($datMisSolicitudes); ?> <?= count($datMisSolicitudes) === 1 ? 'solicitud' : 'solicitudes'; ?></span>
        <?php endif; ?>
    </div>
    <?php if (!empty($datMisSolicitudes)): ?>
        <div class="table-responsive mb-4">
            <table class="table table-striped table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Programa / Ficha</th>
                        <th>Tipo de Certificación</th>
                        <th style="width: 140px;">Fecha Solicitud</th>
                        <th style="width: 150px;">Estado</th>
                        <th class="text-center" style="width: 110px;">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($datMisSolicitudes as $sol): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($sol['nombre_programa'] ?? 'Sin programa asignado'); ?></strong>
                                <br><small class="text-muted">Ficha: <?= !empty($sol['idfic']) ? htmlspecialchars($sol['idfic']) : 'N/A'; ?></small>
                            </td>
                            <td><?= formatearTipoCertificacion($sol['tipo_certificacion'] ?? ''); ?></td>
                            <td><?= !empty($sol['fecha_solicitud']) ? date('d/m/Y H:i', strtotime($sol['fecha_solicitud'])) : '-'; ?></td>
                            <td>
                                <span class="badge bg-<?= getClaseEstadoSolicitud($sol['estado'] ?? 'pendiente'); ?>">
                                    <?= getTextoEstado($sol['estado'] ?? 'pendiente'); ?>
                                </span>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="javascript:void(0);" onclick="verDetalleSolicitud(<?= $sol['id']; ?>)" title="Ver detalle">
                                        <i class="fa fa-eye text-success"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-info mb-4">
            <i class="fa fa-info-circle me-1"></i> No tienes solicitudes de certificación registradas actualmente.
        </div>
    <?php endif; ?>
</div>

<!-- Modal de confirmación para solicitar certificación -->
<div class="modal fade" id="modalConfirmarSolicitudPrograma" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fa fa-paper-plane me-2"></i>Confirmar Solicitud de Certificación
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            
            <form method="POST" action="home.php?pg=<?= $pg; ?>">
                <div class="modal-body">
                    <input type="hidden" name="opera" value="solicitar_programa">
                    <input type="hidden" name="tipo_certificacion" id="tipoSolicitudPrograma">
                    <input type="hidden" name="idfic" id="idficSolicitud">
                    
                    <div class="alert alert-success py-2">
                        <i class="fa fa-robot me-1"></i>
                        <strong>Sistema Automático:</strong> El sistema generará automáticamente la justificación basada en tu avance académico actual.
                    </div>
                    
                    <div class="text-center my-3">
                        <i class="fa fa-certificate fa-3x text-success mb-2"></i>
                        <p class="mb-1 text-muted">¿Deseas solicitar la certificación?</p>
                        <h5 class="text-success fw-bold mb-2" id="nombreCertificacionPrograma"></h5>
                        <p class="mb-0 text-muted small">Programa:</p>
                        <h6 class="text-dark fw-bold" id="nombreProgramaSolicitud"></h6>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-success">
                        Confirmar Solicitud 
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal detalle de la solicitud (Estilo etavgssol) -->
<div class="modal fade" id="modalDetalleSolicitud" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fa fa-info-circle me-2"></i>Detalle de la Solicitud</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div id="contenidoDetalleSolicitud">
                    <div class="text-center py-4">
                        <i class="fa fa-spinner fa-spin fa-2x text-primary"></i>
                        <p class="mt-2 text-muted">Cargando información...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
function solicitarCertificacionPrograma(tipo, nombre, idfic, nombrePrograma) {
    const modal = document.getElementById('modalConfirmarSolicitudPrograma');
    if (!modal) {
        alert('Error: Modal de confirmación no disponible');
        return;
    }
    $('#tipoSolicitudPrograma').val(tipo);
    $('#idficSolicitud').val(idfic);
    $('#nombreCertificacionPrograma').text(nombre);
    $('#nombreProgramaSolicitud').text(nombrePrograma);
    $('#modalConfirmarSolicitudPrograma').modal('show');
}

function descargarCertificado(idsolicitud) {
    window.open('home.php?pg=<?= $pg; ?>&opera=descargar&id=' + idsolicitud, '_blank');
}

function drawCircularProgress(canvasId, percentage) {
    // Reemplazado por barra horizontal Bootstrap
}

function getClaseEstadoJS(estado) {
    switch(estado) {
        case 'aprobada_coordinacion': return 'info';
        case 'completada': return 'success';
        case 'rechazada': return 'danger';
        case 'pendiente': return 'warning';
        default: return 'secondary';
    }
}

function getTextoEstadoJS(estado) {
    switch(estado) {
        case 'pendiente': return 'Pendiente';
        case 'aprobada_coordinacion': return 'Aprobada - Esperando Firma';
        case 'completada': return 'Completada';
        case 'rechazada': return 'Rechazada';
        default: return estado;
    }
}

function verDetalleSolicitud(idsolicitud) {
    $('#contenidoDetalleSolicitud').html(`
        <div class="text-center py-4">
            <i class="fa fa-spinner fa-spin fa-2x text-primary"></i>
            <p class="mt-2 text-muted">Cargando información...</p>
        </div>
    `);
    
    $('#modalDetalleSolicitud').modal('show');
    
    const solicitudes = <?= json_encode(!empty($datMisSolicitudes) ? $datMisSolicitudes : []); ?>;
    const solicitud = solicitudes.find(s => s.id == idsolicitud);
    
    if (solicitud) {
        let html = `
            <div class="row mb-3">
                <div class="col-md-6">
                    <p class="mb-1"><strong>ID Solicitud:</strong> #${solicitud.id}</p>
                    <p class="mb-1"><strong>Programa:</strong> ${solicitud.nombre_programa || 'N/A'}</p>
                    <p class="mb-1"><strong>Ficha:</strong> ${solicitud.idfic || 'N/A'}</p>
                </div>
                <div class="col-md-6">
                    <p class="mb-1"><strong>Tipo Certificación:</strong> ${(solicitud.tipo_certificacion || '').replace(/_/g, ' ')}</p>
                    <p class="mb-1"><strong>Fecha Solicitud:</strong> ${solicitud.fecha_solicitud ? new Date(solicitud.fecha_solicitud).toLocaleDateString('es-ES') : 'N/A'}</p>
                    <p class="mb-1"><strong>Estado:</strong> <span class="badge bg-${getClaseEstadoJS(solicitud.estado)}">${getTextoEstadoJS(solicitud.estado)}</span></p>
                </div>
            </div>
            <div class="mb-3 p-3 bg-light border rounded">
                <strong>Motivo / Justificación Automática:</strong>
                <p class="mb-0 mt-1">${solicitud.motivo || 'Sin motivo especificado'}</p>
            </div>
            ${solicitud.respuesta_coordinacion ? `
                <div class="mb-3 p-3 bg-light border rounded">
                    <strong>Respuesta de Coordinación:</strong>
                    <p class="mb-0 mt-1">${solicitud.respuesta_coordinacion}</p>
                </div>
            ` : ''}
        `;
        $('#contenidoDetalleSolicitud').html(html);
    } else {
        $('#contenidoDetalleSolicitud').html('<div class="alert alert-danger mb-0">Error al cargar los datos de la solicitud.</div>');
    }
}

$(document).ready(function() {
    // Almacenar el último programa seleccionado
    var programaActivo = null;

    $('#programasTabs button').on('click', function(e) {
        var targetId = $(this).attr('data-bs-target') || $(this).attr('data-target');

        // Si se hace clic en la pestaña que ya está activa
        if (programaActivo === targetId && $(targetId).hasClass('show')) {
            e.preventDefault();
            e.stopPropagation();

            // Desactivar la pestaña y ocultar el contenido
            $(this).removeClass('active');
            $(targetId).removeClass('show active');

            // Mostrar nuevamente el mensaje inicial
            $('#mensaje-seleccionar-programa').fadeIn();

            // Resetear la variable de control
            programaActivo = null;
        } else {
            // Si es un programa diferente, ocultar el mensaje inicial y guardar el nuevo estado
            $('#mensaje-seleccionar-programa').hide();
            programaActivo = targetId;
        }
    });
});
</script>

<?php
if (!function_exists('getBadgeColorPrograma')) {
    function getBadgeColorPrograma($tipo) {
        switch (strtoupper($tipo)) {
            case 'TECNICO': return 'primary';
            case 'TECNOLOGO': return 'success';
            case 'CURSO': case 'COMPLEMENTARIA': return 'info';
            default: return 'secondary';
        }
    }
}

if (!function_exists('getEstadoPrograma')) {
    function getEstadoPrograma($estado) {
        switch ($estado) {
            case 'activo': return 'success';
            case 'terminado': return 'primary';
            case 'inactivo': return 'secondary';
            default: return 'warning';
        }
    }
}

if (!function_exists('formatearTipoCertificacion')) {
    function formatearTipoCertificacion($tipo) {
        $tipos = [
            'certificacion_inicio' => 'Inicio de Formación',
            'certificacion_parcial' => 'Competencias Parciales',
            'certificacion_etapa_productiva' => 'Etapa Productiva',
            'certificacion_tecnica_completa' => 'Técnica Completa',
            'certificacion_tecnologica_completa' => 'Tecnológica Completa',
            'certificacion_curso_inicio' => 'Participación en Curso',
            'certificacion_curso_intermedio' => 'Progreso en Curso',
            'certificacion_curso_completo' => 'Finalización de Curso',
            'constancia_estudiante_activo' => 'Constancia de Estudiante'
        ];
        return $tipos[$tipo] ?? ucfirst(str_replace('_', ' ', $tipo));
    }
}

if (!function_exists('getIconoCertificacion')) {
    function getIconoCertificacion($tipo) {
        $iconos = [
            'certificacion_inicio' => 'fa-star',
            'certificacion_parcial' => 'fa-certificate',
            'certificacion_etapa_productiva' => 'fa-industry',
            'certificacion_tecnica_completa' => 'fa-cogs',
            'certificacion_tecnologica_completa' => 'fa-laptop',
            'certificacion_curso_inicio' => 'fa-book',
            'certificacion_curso_intermedio' => 'fa-bookmark',
            'certificacion_curso_completo' => 'fa-graduation-cap',
            'constancia_estudiante_activo' => 'fa-id-card'
        ];
        return $iconos[$tipo] ?? 'fa-certificate';
    }
}

if (!function_exists('getClaseEstadoSolicitud')) {
    function getClaseEstadoSolicitud($estado) {
        switch ($estado) {
            case 'aprobada_coordinacion': return 'info';
            case 'completada': return 'success';
            case 'rechazada': return 'danger';
            case 'pendiente': return 'warning';
            default: return 'secondary';
        }
    }
}

if (!function_exists('getTextoEstado')) {
    function getTextoEstado($estado) {
        switch ($estado) {
            case 'pendiente': return 'Pendiente';
            case 'aprobada_coordinacion': return 'Aprobada - Esp. Firma';
            case 'completada': return 'Completada';
            case 'rechazada': return 'Rechazada';
            default: return ucfirst($estado);
        }
    }
}
?>