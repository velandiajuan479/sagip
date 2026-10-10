<?php require_once 'controllers/etacgssol.php'; ?>

<div class="w-100 px-3">
    <!-- 1. TÍTULO DEL MÓDULO -->
    <div class="tit mb-3">
        <h1><i class="<?= $icono ?? 'fa-solid fa-file-signature'; ?> icoiuv"></i> Gestión de Solicitudes de Certificación</h1>
        <hr class="lintit">
    </div>

    <!-- 2. MENSAJES DEL SISTEMA -->
    <?php if (!empty($mensaje)): ?>
        <div class="alert alert-<?= $tipoMensaje; ?> alert-dismissible fade show mb-4" role="alert">
            <?= $mensaje; ?>
            <a href="home.php?pg=<?= $pg; ?>" class="btn-close" aria-label="Cerrar"></a>
        </div>
    <?php endif; ?>

    <!-- 4. DASHBOARD TARJETAS KPI -->
    <div class="row mb-4 g-3">
        <!-- PENDIENTES -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi-card h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted small fw-bold text-uppercase">PENDIENTES</span>
                        <h2 class="mb-0 mt-1 fw-bold text-warning"><?= $datEstadisticas['pendientes'] ?? count($solicitudes_pendientes ?? []); ?></h2>
                    </div>
                    <div class="kpi-icon-box" style="background-color: #fef3c7; color: #d97706;">
                        <i class="fa-solid fa-clock-rotate-left fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="small text-muted"><i class="fa-solid fa-hourglass-half icoldiz"></i> Por gestionar</span>
                </div>
            </div>
        </div>

        <!-- APROBADAS -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi-card h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted small fw-bold text-uppercase">APROBADAS</span>
                        <h2 class="mb-0 mt-1 fw-bold" style="color: #117f09;"><?= $datEstadisticas['aprobadas'] ?? count($solicitudes_aprobadas ?? []); ?></h2>
                    </div>
                    <div class="kpi-icon-box" style="background-color: #e5eeff; color: #117f09;">
                        <i class="fa-solid fa-circle-check fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="small text-muted"><i class="fa-solid fa-check icoldiz"></i> Listas y procesadas</span>
                </div>
            </div>
        </div>

        <!-- RECHAZADAS -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi-card h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted small fw-bold text-uppercase" style="color: #d92525;">RECHAZADAS</span>
                        <h2 class="mb-0 mt-1 fw-bold" style="color: #d92525;"><?= $datEstadisticas['rechazadas'] ?? count($solicitudes_rechazadas ?? []); ?></h2>
                    </div>
                    <div class="kpi-icon-box" style="background-color: #fde8e8; color: #d92525;">
                        <i class="fa-solid fa-circle-xmark fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip" style="background-color: #fde8e8;">
                    <span class="small text-muted" style="color: #d92525;"><i class="fa-solid fa-circle-exclamation icoldiz"></i> Con observaciones</span>
                </div>
            </div>
        </div>

        <!-- TOTAL SOLICITUDES -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi-card h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted small fw-bold text-uppercase">TOTAL SOLICITUDES</span>
                        <h2 class="mb-0 mt-1 fw-bold"><?= $datEstadisticas['total'] ?? 0; ?></h2>
                    </div>
                    <div class="kpi-icon-box" style="background-color: #dce9ff; color: #0d6efd;">
                        <i class="fa-solid fa-folder-open fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="small text-muted"><i class="fa-solid fa-file-lines icoldiz"></i> Histórico general</span>
                </div>
            </div>
        </div>
    </div>

    <!-- 5. BARRA DE NAVEGACIÓN Y BUSCADOR UNIFICADO -->
    <div class="filter-toolbar-container mb-4 p-2 bg-white rounded shadow-sm border">
        <div class="d-flex flex-row align-items-center justify-content-between gap-3 w-100">
            <!-- Pestañas de Filtro (Mapeadas exactamente con clases globales .btn-filter-pill) -->
            <div class="filter-pills-group d-flex flex-nowrap align-items-center gap-1" role="tablist">
                <button type="button" class="btn-filter-pill active text-nowrap" id="pendientes-tab" data-bs-toggle="tab" data-bs-target="#sec-pendientes">
                    <span class="pill-dot" style="background-color: #ffffff;"></span>
                    Pendientes <span class="pill-count"><?= count($solicitudes_pendientes ?? []); ?></span>
                </button>
                <button type="button" class="btn-filter-pill text-nowrap" id="devueltas-tab" data-bs-toggle="tab" data-bs-target="#sec-rechazadas">
                    <span class="pill-dot" style="background-color: #d92525;"></span>
                    Rechazadas <span class="pill-count"><?= count($solicitudes_rechazadas ?? []); ?></span>
                </button>
                <button type="button" class="btn-filter-pill text-nowrap" id="aprobadas-tab" data-bs-toggle="tab" data-bs-target="#sec-aprobadas">
                    <span class="pill-dot" style="background-color: #117f09;"></span>
                    Aprobadas <span class="pill-count"><?= count($solicitudes_aprobadas ?? []); ?></span>
                </button>
            </div>

            <!-- Buscador Extendido -->
            <div class="toolbar-search-box flex-grow-1">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="customVideoSearch" class="form-control border-0 bg-transparent shadow-none" placeholder="Buscar aprendiz por nombre, documento o programa...">
            </div>
        </div>
    </div>

    <!-- 6. CONTENIDO DINÁMICO POR PESTAÑAS -->
    <div class="tab-content mb-4" id="solicitudesTabContent">

        <!-- PESTAÑA 1: PENDIENTES -->
        <div class="tab-pane fade show active" id="sec-pendientes" role="tabpanel">
            <h2 class="fs-5 text-success fw-bold mb-3">Solicitudes Pendientes por Gestionar</h2>
            
            <?php if (!empty($solicitudes_pendientes)): ?>
                <div class="mb-4">
                    <?php foreach ($solicitudes_pendientes as $sol): ?>
                        <?php $idReal = isset($sol['idsolicitud']) ? $sol['idsolicitud'] : ($sol['id'] ?? 0); ?>
                        <details class="border rounded-3 bg-white mb-3 shadow-sm overflow-hidden border-light-subtle">
                            <summary class="p-3 bg-light text-dark d-flex align-items-center justify-content-between border-bottom" style="cursor: pointer; user-select: none;">
                                <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between w-100 me-2">
                                    <div>
                                        <span class="fs-6 text-dark fw-bold"><?= mb_strtoupper($sol['nomusu'] ?? 'N/A', 'UTF-8'); ?></span>
                                        <span class="text-muted ms-sm-2 small fw-normal">CC: <?= $sol['ndocusu'] ?? 'N/A'; ?></span>
                                        <div class="text-secondary small fw-semibold mt-1">
                                            Programa: <?= $sol['nombre_programa'] ?? 'Sin programa'; ?> (Ficha: <?= $sol['codigo_ficha'] ?? $sol['idfic'] ?? 'N/A'; ?>)
                                        </div>
                                    </div>
                                    <span class="badge bg-warning text-dark mt-2 mt-sm-0 align-self-sm-center px-3 py-2 fs-7 rounded-pill">
                                        Pendiente
                                    </span>
                                </div>
                            </summary>
                            <div class="p-3 border-top bg-white text-dark">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <p class="mb-1"><strong>Tipo de Certificación:</strong> <?= formatearTipoCertificacion($sol['tipo_certificacion'] ?? ''); ?></p>
                                        <p class="mb-0"><strong>Fecha Solicitud:</strong> <?= (!empty($sol['fecha_solicitud'])) ? date('d/m/Y H:i', strtotime($sol['fecha_solicitud'])) : 'N/A'; ?></p>
                                    </div>
                                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                                        <button type="button" class="btn btn-sm btn-success fw-bold me-1" data-bs-toggle="modal" data-bs-target="#modalAprobar<?= $idReal; ?>">
                                            <i class="fa-solid fa-check me-1"></i> Aprobar
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger fw-bold me-1" data-bs-toggle="modal" data-bs-target="#modalRechazar<?= $idReal; ?>">
                                            <i class="fa-solid fa-xmark me-1"></i> Rechazar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </details>

                        <!-- MODAL DE APROBACIÓN -->
                        <div class="modal fade" id="modalAprobar<?= $idReal; ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <form method="POST" action="home.php?pg=<?= $pg; ?>">
                                        <input type="hidden" name="opera" value="procesar_solicitud">
                                        <input type="hidden" name="idsolicitud" value="<?= $idReal; ?>">
                                        <input type="hidden" name="accion" value="aprobar">
                                        
                                        <div class="modal-header bg-success text-white">
                                            <h5 class="modal-title fw-bold"><i class="fa-solid fa-circle-check me-2"></i>Aprobar Solicitud #<?= $idReal; ?></h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                        </div>
                                        <div class="modal-body text-dark">
                                            <p class="mb-2">¿Está seguro de aprobar la solicitud de certificación para <strong><?= mb_strtoupper($sol['nomusu'] ?? '', 'UTF-8'); ?></strong>?</p>
                                            <div class="mb-3">
                                                <label for="obs_aprob_<?= $idReal; ?>" class="form-label fw-bold">Observaciones (Opcional):</label>
                                                <textarea name="observaciones" id="obs_aprob_<?= $idReal; ?>" class="form-control" rows="3" placeholder="Ingrese comentarios para el certificado..."></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                            <button type="submit" class="btn btn-success fw-bold">Confirmar Aprobación</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- MODAL DE RECHAZO -->
                        <div class="modal fade" id="modalRechazar<?= $idReal; ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <form method="POST" action="home.php?pg=<?= $pg; ?>">
                                        <input type="hidden" name="opera" value="procesar_solicitud">
                                        <input type="hidden" name="idsolicitud" value="<?= $idReal; ?>">
                                        <input type="hidden" name="accion" value="rechazar">
                                        
                                        <div class="modal-header bg-danger text-white">
                                            <h5 class="modal-title fw-bold"><i class="fa-solid fa-circle-xmark me-2"></i>Rechazar Solicitud #<?= $idReal; ?></h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                        </div>
                                        <div class="modal-body text-dark">
                                            <p class="mb-2">Indique la observación por la cual se rechaza la solicitud de <strong><?= mb_strtoupper($sol['nomusu'] ?? '', 'UTF-8'); ?></strong>:</p>
                                            <div class="mb-3">
                                                <label for="obs_rech_<?= $idReal; ?>" class="form-label fw-bold">Motivo u Observación obligatoria:</label>
                                                <textarea name="observaciones" id="obs_rech_<?= $idReal; ?>" class="form-control" rows="3" placeholder="Especifique los motivos del rechazo..." required></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                            <button type="submit" class="btn btn-danger fw-bold">Confirmar Rechazo</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-warning text-dark fw-semibold mt-3" role="alert">
                    <i class="fa-solid fa-circle-info me-2"></i> No hay solicitudes pendientes por gestionar. Las nuevas solicitudes enviadas por los aprendices aparecerán aquí automáticamente para su revisión.
                </div>
            <?php endif; ?>
        </div>

        <!-- PESTAÑA 2: RECHAZADAS -->
        <div class="tab-pane fade" id="sec-rechazadas" role="tabpanel">
            <h2 class="fs-5 text-danger fw-bold mb-3">Solicitudes Rechazadas</h2>
            <?php if (!empty($solicitudes_rechazadas)): ?>
                <div class="mb-4">
                    <?php foreach ($solicitudes_rechazadas as $sol): ?>
                        <?php $idReal = isset($sol['idsolicitud']) ? $sol['idsolicitud'] : ($sol['id'] ?? 0); ?>
                        <details class="border rounded-3 bg-white mb-3 shadow-sm overflow-hidden border-light-subtle">
                            <summary class="p-3 bg-light text-dark d-flex align-items-center justify-content-between border-bottom" style="cursor: pointer; user-select: none;">
                                <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between w-100 me-2">
                                    <div>
                                        <span class="fs-6 text-dark fw-bold"><?= mb_strtoupper($sol['nomusu'] ?? 'N/A', 'UTF-8'); ?></span>
                                        <span class="text-muted ms-sm-2 small fw-normal">CC: <?= $sol['ndocusu'] ?? 'N/A'; ?></span>
                                        <div class="text-secondary small fw-semibold mt-1">
                                            Certificación: <?= formatearTipoCertificacion($sol['tipo_certificacion'] ?? ''); ?>
                                        </div>
                                    </div>
                                    <span class="badge bg-danger text-white mt-2 mt-sm-0 align-self-sm-center px-3 py-2 fs-7 rounded-pill">
                                        Rechazada
                                    </span>
                                </div>
                            </summary>
                            <div class="p-3 border-top bg-white text-dark">
                                <p class="mb-2"><strong>Fecha Respuesta:</strong> <?= (!empty($sol['fecha_respuesta_coordinacion'])) ? date('d/m/Y H:i', strtotime($sol['fecha_respuesta_coordinacion'])) : 'N/A'; ?></p>
                                <p class="mb-0"><strong>Motivo / Observación:</strong> <span class="text-secondary"><?= nl2br($sol['respuesta_coordinacion'] ?? 'Sin motivo registrado'); ?></span></p>
                            </div>
                        </details>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-warning text-dark fw-semibold mt-3">No hay solicitudes rechazadas en el sistema.</div>
            <?php endif; ?>
        </div>

        <!-- PESTAÑA 3: APROBADAS -->
        <div class="tab-pane fade" id="sec-aprobadas" role="tabpanel">
            <h2 class="fs-5 text-success fw-bold mb-3">Solicitudes Aprobadas / Completadas</h2>
            <?php if (!empty($solicitudes_aprobadas)): ?>
                <div class="mb-4">
                    <?php foreach ($solicitudes_aprobadas as $sol): ?>
                        <?php $idReal = isset($sol['idsolicitud']) ? $sol['idsolicitud'] : ($sol['id'] ?? 0); ?>
                        <details class="border rounded-3 bg-white mb-3 shadow-sm overflow-hidden border-light-subtle">
                            <summary class="p-3 bg-light text-dark d-flex align-items-center justify-content-between border-bottom" style="cursor: pointer; user-select: none;">
                                <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between w-100 me-2">
                                    <div>
                                        <span class="fs-6 text-dark fw-bold"><?= mb_strtoupper($sol['nomusu'] ?? 'N/A', 'UTF-8'); ?></span>
                                        <span class="text-muted ms-sm-2 small fw-normal">CC: <?= $sol['ndocusu'] ?? 'N/A'; ?></span>
                                        <div class="text-secondary small fw-semibold mt-1">
                                            Certificación: <?= formatearTipoCertificacion($sol['tipo_certificacion'] ?? ''); ?>
                                        </div>
                                    </div>
                                    <span class="badge bg-success text-white mt-2 mt-sm-0 align-self-sm-center px-3 py-2 fs-7 rounded-pill">
                                        <?= getTextoEstado($sol['estado'] ?? 'Aprobada'); ?>
                                    </span>
                                </div>
                            </summary>
                            <div class="p-3 border-top bg-white text-dark">
                                <p class="mb-2"><strong>Fecha Respuesta:</strong> <?= (!empty($sol['fecha_respuesta_coordinacion'])) ? date('d/m/Y H:i', strtotime($sol['fecha_respuesta_coordinacion'])) : 'N/A'; ?></p>
                                <p class="mb-0"><strong>Respuesta de Coordinación:</strong> <span class="text-secondary"><?= nl2br($sol['respuesta_coordinacion'] ?? 'Aprobada exitosamente'); ?></span></p>
                            </div>
                        </details>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-info text-dark fw-semibold mt-3">No hay solicitudes aprobadas o completadas registradas.</div>
            <?php endif; ?>
        </div>

    </div>
</div>