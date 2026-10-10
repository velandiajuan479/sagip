<?php
require_once("controllers/comctrz.php");

$rolUsuarioActual = $_SESSION['pefnom'] ?? 'Funcionario SENA';
$nomUsuarioActual = $_SESSION['nomusu'] ?? 'Usuario Actual';
$idUsuarioActual  = $_SESSION['idusu'] ?? null;

// Conteos KPI
$totHojas      = !empty($cnt['total_hojas'])  ? (int)$cnt['total_hojas']  : 0;
$totEnProceso  = !empty($cnt['en_proceso'])   ? (int)$cnt['en_proceso']   : 0;
$totDevueltas  = !empty($cnt['devueltas'])    ? (int)$cnt['devueltas']    : 0;
$totFinalizadas= !empty($cnt['finalizadas'])  ? (int)$cnt['finalizadas']  : 0;
?>

<div class="conte">
    <?php echo titulo2("<i class='".$icono."'></i> Trazabilidad y Flujo de Aprobación", 0); ?>

    <?php if (!empty($esDemo)): ?>
    <!-- ===== BANNER MODO DEMO ===== -->
    <div class="filter-toolbar-container mb-3">
        <div class="alert alert-warning d-flex align-items-center gap-3 flex-wrap mb-0" role="alert">
            <div class="d-flex align-items-center gap-3 flex-wrap">
                <i class="fa-solid fa-flask-vial fa-2x"></i>
                <div>
                    <strong>MODO DEMO</strong> — No hay hojas de trabajo en la base de datos.
                    Se muestran <strong>hojas de ejemplo</strong> (radicados 900001–900006, en memoria)
                    para probar el flujo institucional. Presiona <strong>Enviar / Aprobar / Devolver</strong> y
                    observa cómo avanza el historial auditado. <span class="text-danger fw-semibold">Nada se guarda en la BD.</span>
                </div>
            </div>
            <a href="home.php?pg=2016&demo=0" class="btn btn-sm btn-outline-danger ms-auto">Ocultar ejemplo</a>
        </div>
    </div>
    <?php else: ?>
    <div class="filter-toolbar-container mb-3">
        <div class="d-flex justify-content-end">
            <a href="home.php?pg=2016&demo=1" class="btn btn-sm btn-outline-primary">
                <i class="fa-solid fa-flask-vial me-1"></i> Ver con datos de ejemplo
            </a>
        </div>
    </div>
    <?php endif; ?>
    <!-- ===== /BANNER MODO DEMO ===== -->

    <!-- ===== KPI CARDS ===== -->
    <?php if (!empty($cnt)): ?>
    <div class="row my-4 g-4">

        <!-- Total Hojas -->
        <div class="col-12 col-md-3">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #3e4a3d;">Total en Sistema</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #0b1c30;"><?= $totHojas; ?></span>
                            <span style="font-size: 13px; color: #3e4a3d;">hojas</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box" style="background-color: #b5dcfe; color: #3c627f;">
                        <i class="fa-solid fa-file-contract fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 11px; color: #3c627f;">
                        <i class="fa-solid fa-arrow-trend-up" style="font-size: 12px;"></i> Registradas
                    </span>
                    <span style="font-size: 11px; color: #3e4a3d;">Auditoría: 100%</span>
                </div>
            </div>
        </div>

        <!-- En Proceso -->
        <div class="col-12 col-md-3">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #0284c7;">En Revisión</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #0284c7;"><?= $totEnProceso; ?></span>
                            <span style="font-size: 13px; color: rgba(2, 132, 199, 0.8);">en flujo</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box" style="background-color: #cbe6ff; color: #0284c7;">
                        <i class="fa-solid fa-spinner fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="fw-semibold d-inline-flex align-items-center gap-2" style="font-size: 11px; color: #0284c7;">
                        <span class="pulse-dot" style="background-color: #0284c7;"></span> Trámite Activo
                    </span>
                    <span style="font-size: 11px; color: #3e4a3d;">Pasos 1 al 4</span>
                </div>
            </div>
        </div>

        <!-- Devueltas -->
        <div class="col-12 col-md-3">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #ba1a1a;">Devueltas</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #0b1c30;"><?= $totDevueltas; ?></span>
                            <span style="font-size: 13px; color: #3e4a3d;">por corregir</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box" style="background-color: #ffdad6; color: #ba1a1a;">
                        <i class="fa-solid fa-arrow-rotate-left fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="d-inline-flex align-items-center gap-1" style="font-size: 11px; color: #3e4a3d;">
                        <i class="fa-solid fa-clock-rotate-left" style="font-size: 12px;"></i> Con observaciones
                    </span>
                    <span class="fw-semibold" style="font-size: 11px; color: #ba1a1a;">Pausadas</span>
                </div>
            </div>
        </div>

        <!-- Aprobadas / Finalizadas -->
        <div class="col-12 col-md-3">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #006b29;">Aprobadas</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #006b29;"><?= $totFinalizadas; ?></span>
                            <span style="font-size: 13px; color: rgba(0, 107, 41, 0.8);">finalizadas</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box" style="background-color: #7afd88; color: #006b24;">
                        <i class="fa-solid fa-circle-check fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="fw-semibold d-inline-flex align-items-center gap-2" style="font-size: 11px; color: #006b29;">
                        <span class="pulse-dot"></span> Ciclo Concluido (100% OK)
                    </span>
                    <span style="font-size: 11px; color: #3e4a3d;">Historial CEDA</span>
                </div>
            </div>
        </div>

    </div>
    <?php endif; ?>
    <!-- ===== /KPI CARDS ===== -->

    <!-- ===== TOOLBAR: Selector de Hoja + navegación ===== -->
    <div class="filter-toolbar-container">
        <!-- Izquierda: píldora de estado y botones rápidos -->
        <div class="toolbar-left-group">
            <div class="filter-pills-group">
                <?php if (!empty($hojaDetalle)): ?>
                    <button class="btn-filter-pill active" type="button">
                        <span class="pill-dot" style="background-color: #006b29;"></span>
                        <span>Hoja #<?= str_pad($hojaDetalle['idnorad'], 5, '0', STR_PAD_LEFT); ?></span>
                        <span class="pill-count">Paso <?= (int)$hojaDetalle['paso_actual']; ?></span>
                        <?php if (!empty($esDemo)): ?><span class="badge text-bg-warning" style="font-size:9px;">demo</span><?php endif; ?>
                    </button>
                <?php else: ?>
                    <button class="btn-filter-pill active" type="button">
                        <span class="pill-dot" style="background-color: #3c627f;"></span>
                        <span>Todas</span>
                        <span class="pill-count"><?= $totHojas; ?></span>
                    </button>
                <?php endif; ?>
            </div>

            <div class="toolbar-divider d-none d-sm-block"></div>

            <div class="toolbar-export-buttons">
                <a href="home.php?pg=2004" class="toolbar-export-btn" title="Hojas de Trabajo">
                    <i class="fa-solid fa-folder-open"></i>
                </a>
                <a href="home.php?pg=2011" class="toolbar-export-btn" title="Cargar Hoja de Trabajo">
                    <i class="fa-solid fa-file-circle-plus"></i>
                </a>
                <?php if ($hojaSele): ?>
                <a href="home.php?pg=2010&id=<?= $hojaSele; ?>" class="toolbar-export-btn" target="_blank" title="Ver Reporte Oficial Imprimible">
                    <i class="fa-solid fa-print"></i>
                </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Derecha: selector de hoja con buscador -->
        <div class="toolbar-search-sort-group">
            <form action="home.php" method="GET" class="d-flex align-items-center gap-2 m-0 p-0">
                <input type="hidden" name="pg" value="2016">
                <div class="toolbar-search-box">
                    <i class="fa-solid fa-magnifying-glass icoldiz"></i>
                    <select name="idnorad" id="idnorad_sel" onchange="this.form.submit()">
                        <?php if (!empty($datHoj)): ?>
                            <?php foreach ($datHoj as $h): ?>
                            <option value="<?= (int)$h['idnorad']; ?>" <?= ($hojaSele == $h['idnorad']) ? 'selected' : ''; ?>>
                                #<?= str_pad($h['idnorad'], 5, '0', STR_PAD_LEFT); ?> — <?= htmlspecialchars(mb_strimwidth($h['nompro'] ?? 'Programa', 0, 24, '...')); ?> (<?= htmlspecialchars(mb_strimwidth($h['nomemp'] ?? 'Empresa', 0, 15, '...')); ?>) [Paso <?= (int)$h['paso_actual']; ?>]
                            </option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="">No hay Hojas registradas</option>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="toolbar-secondary-controls">
                    <div class="toolbar-view-toggle">
                        <button type="submit" class="toolbar-view-btn active" title="Cargar Hoja">
                            <i class="fa-solid fa-filter"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- ===== /TOOLBAR ===== -->

    <?php if (!empty($hojaDetalle)): ?>
        <?php
        $pasoActual   = (int)$hojaDetalle['paso_actual'];
        $estadoActual = $hojaDetalle['estado'];
        $esDevuelto   = (stripos($estadoActual, 'Devuelto') !== false || stripos($estadoActual, 'Rechazado') !== false);
        $esFinalizado = ($pasoActual === 4 && stripos($estadoActual, 'Aprobado Final') !== false);
        ?>

    <!-- ===== DETALLE DE HOJA: Card principal con stepper ===== -->
    <div class="inser">
        <div class="filter-toolbar-container">

            <!-- Encabezado de la hoja seleccionada -->
            <div class="d-flex align-items-center justify-content-between w-100 flex-wrap gap-2 mb-2">
                <h2>
                    <i class="fa-solid fa-file-invoice"></i>
                    Hoja #<?= str_pad($hojaDetalle['idnorad'], 5, '0', STR_PAD_LEFT); ?>
                    — <?= htmlspecialchars($hojaDetalle['nompro'] ?? 'Curso SENA'); ?>
                </h2>
                <div>
                    <?php if ($esFinalizado): ?>
                        <span class="badge bg-success px-3 py-2">
                            <i class="fa-solid fa-circle-check me-1"></i> <?= htmlspecialchars($estadoActual); ?>
                        </span>
                    <?php elseif ($esDevuelto): ?>
                        <span class="badge bg-danger px-3 py-2">
                            <i class="fa-solid fa-triangle-exclamation me-1"></i> <?= htmlspecialchars($estadoActual); ?>
                        </span>
                    <?php else: ?>
                        <span class="badge bg-info px-3 py-2">
                            <i class="fa-solid fa-clock me-1"></i> <?= htmlspecialchars($estadoActual); ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Metadatos rápidos (campos de solo lectura) -->
            <div class="row w-100">
                <div class="form-group col-md-3">
                    <label>Empresa / Solicitante</label>
                    <input type="text" class="form-control" readonly value="<?= htmlspecialchars($hojaDetalle['nomemp'] ?? 'No registrada'); ?>">
                </div>
                <div class="form-group col-md-3">
                    <label>Ficha SOFIA Plus</label>
                    <input type="text" class="form-control" readonly value="<?= !empty($hojaDetalle['idfic']) ? htmlspecialchars($hojaDetalle['idfic']) : 'Pendiente por asignar'; ?>">
                </div>
                <div class="form-group col-md-3">
                    <label>Fecha Inicio</label>
                    <input type="text" class="form-control" readonly value="<?= !empty($hojaDetalle['feclini']) ? date('d/m/Y', strtotime($hojaDetalle['feclini'])) : '---'; ?>">
                </div>
                <div class="form-group col-md-3">
                    <label>Instructor a Cargo</label>
                    <input type="text" class="form-control" readonly value="<?= htmlspecialchars($hojaDetalle['nombre_instructor'] ?? 'Pendiente por vincular'); ?>">
                </div>
            </div>

            <!-- Stepper institucional de 4 pasos -->
            <div class="row w-100 mt-3">
                <div class="col-12">
                    <h2><i class="fa-solid fa-bars-progress"></i> Progreso en el Flujo Institucional</h2>
                </div>
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-start gap-2 my-3 flex-wrap">

                        <!-- Paso 1 -->
                        <?php
                        $fin = $esFinalizado;
                        $cls1 = $fin ? 'badge bg-success' : (($pasoActual === 1 && $esDevuelto) ? 'badge bg-danger' : (($pasoActual > 1) ? 'badge bg-success' : (($pasoActual === 1) ? 'badge bg-primary' : 'badge bg-light text-dark border')));
                        ?>
                        <div class="text-center flex-fill">
                            <div class="mb-2">
                                <span class="<?= $cls1; ?> rounded-circle p-3 fs-5">
                                    <i class="fa-solid fa-user-pen"></i>
                                </span>
                            </div>
                            <div class="fw-bold small">1. Instructor</div>
                            <div class="text-muted" style="font-size:.75rem;">Creación / Corrección</div>
                        </div>

                        <!-- Paso 2 -->
                        <?php
                        $cls2 = $fin ? 'badge bg-success' : (($pasoActual === 2 && $esDevuelto) ? 'badge bg-danger' : (($pasoActual > 2) ? 'badge bg-success' : (($pasoActual === 2) ? 'badge bg-primary' : 'badge bg-light text-dark border')));
                        ?>
                        <div class="text-center flex-fill">
                            <div class="mb-2">
                                <span class="<?= $cls2; ?> rounded-circle p-3 fs-5">
                                    <i class="fa-solid fa-clipboard-check"></i>
                                </span>
                            </div>
                            <div class="fw-bold small">2. Coordinación</div>
                            <div class="text-muted" style="font-size:.75rem;">Revisión Académica</div>
                        </div>

                        <!-- Paso 3 -->
                        <?php
                        $cls3 = $fin ? 'badge bg-success' : (($pasoActual === 3 && $esDevuelto) ? 'badge bg-danger' : (($pasoActual > 3) ? 'badge bg-success' : (($pasoActual === 3) ? 'badge bg-primary' : 'badge bg-light text-dark border')));
                        ?>
                        <div class="text-center flex-fill">
                            <div class="mb-2">
                                <span class="<?= $cls3; ?> rounded-circle p-3 fs-5">
                                    <i class="fa-solid fa-building-columns"></i>
                                </span>
                            </div>
                            <div class="fw-bold small">3. Admin. Educativa</div>
                            <div class="text-muted" style="font-size:.75rem;">Validación y Ficha</div>
                        </div>

                        <!-- Paso 4 -->
                        <?php
                        $cls4 = $fin ? 'badge bg-success' : (($pasoActual === 4) ? 'badge bg-primary' : 'badge bg-light text-dark border');
                        ?>
                        <div class="text-center flex-fill">
                            <div class="mb-2">
                                <span class="<?= $cls4; ?> rounded-circle p-3 fs-5">
                                    <i class="fa-solid fa-award"></i>
                                </span>
                            </div>
                            <div class="fw-bold small">4. Relaciones Corp.</div>
                            <div class="text-muted" style="font-size:.75rem;">Aprobación Final</div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Alerta de devolución -->
            <?php if (!empty($hojaDetalle['devuelto_de']) && $pasoActual === 1): ?>
            <div class="row w-100">
                <div class="col-12">
                    <div class="alert alert-warning d-flex align-items-center" role="alert">
                        <i class="fa-solid fa-triangle-exclamation fa-2x me-3"></i>
                        <div>
                            <strong>SOLICITUD EN PROCESO DE CORRECCIÓN:</strong><br>
                            Esta hoja de trabajo fue devuelta desde el <strong>Paso <?= (int)$hojaDetalle['devuelto_de']; ?></strong>. Al ser corregida y reenviada por el instructor, volverá automáticamente a la dependencia remitente sin reprocesos.
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Formulario de aprobación / devolución -->
            <div class="row w-100 mt-2">
                <div class="col-12">
                    <h2>
                        <i class="fa-solid fa-gavel"></i> Panel de Gestión — Paso <?= $pasoActual; ?>
                        <small class="text-muted fs-6 fw-normal ms-2">Usuario: <?= htmlspecialchars($nomUsuarioActual); ?> (<?= htmlspecialchars($rolUsuarioActual); ?>)</small>
                        <?php if (!$esFinalizado): ?>
                        <span class="badge bg-primary align-middle ms-2" style="font-size:.72rem;">
                            <i class="fa-solid fa-person-circle-question me-1"></i>Actúa: <?= htmlspecialchars($DEPENDENCIA_PASO[$pasoActual] ?? 'Siguiente dependencia'); ?>
                        </span>
                        <?php endif; ?>
                    </h2>
                </div>
            </div>

            <form action="home.php?pg=2016" method="POST" id="formTrazabilidad" onsubmit="return validarAccionFlujo(this);">
                <input type="hidden" name="opera"   value="flujo_accion">
                <input type="hidden" name="idnorad"  value="<?= $hojaDetalle['idnorad']; ?>">
                <input type="hidden" name="accion"   id="inputAccion" value="">

                <div class="row w-100">
                    <div class="form-group col-md-12">
                        <label for="txtObservacion">
                            Observaciones / Motivo de la Decisión
                            <span class="text-danger" id="spanRequerido" style="display:none;">* (Obligatorio en caso de Devolución/Rechazo)</span>
                        </label>
                        <textarea name="observacion" id="txtObservacion" class="form-control" rows="3"
                                  placeholder="Escriba los comentarios académicos, motivos de devolución o justificación de aprobación..."></textarea>
                    </div>
                    <div class="form-group col-md-12 d-flex gap-2 justify-content-end flex-wrap">
                        <?php if (empty($esDemo) && empty($puedeActuar)): ?>
                        <div class="alert alert-danger py-2 px-3 mb-0 w-100 text-center fw-bold">
                            <i class="fa-solid fa-user-lock me-1"></i> Su perfil actual no tiene autorización para actuar en el Paso <?= $pasoActual; ?> (<?= htmlspecialchars($DEPENDENCIA_PASO[$pasoActual] ?? ''); ?>).
                        </div>
                        <?php endif; ?>
                        <?php $blq = (empty($esDemo) && empty($puedeActuar)) ? 'disabled' : ''; ?>
                        <?php if ($pasoActual === 1): ?>
                            <button type="button" class="btn btn-primary px-4 fw-bold" <?= $blq; ?> onclick="ejecutarAccion('Enviar');">
                                <i class="fa-solid fa-paper-plane"></i> Enviar Hoja a Revisión
                            </button>
                        <?php elseif ($pasoActual >= 2 && $pasoActual <= 3): ?>
                            <button type="button" class="btn btn-danger px-4 fw-bold" <?= $blq; ?> onclick="ejecutarAccion('Devolver');">
                                <i class="fa-solid fa-arrow-rotate-left"></i> Devolver con Observaciones
                            </button>
                            <button type="button" class="btn btn-success px-4 fw-bold" <?= $blq; ?> onclick="ejecutarAccion('Aprobar');">
                                <i class="fa-solid fa-check-double"></i> Aprobar y Avanzar Paso
                            </button>
                        <?php elseif ($pasoActual === 4 && !$esFinalizado): ?>
                            <button type="button" class="btn btn-danger px-4 fw-bold" <?= $blq; ?> onclick="ejecutarAccion('Devolver');">
                                <i class="fa-solid fa-arrow-rotate-left"></i> Devolver para Ajustes
                            </button>
                            <button type="button" class="btn btn-success px-4 fw-bold" <?= $blq; ?> onclick="ejecutarAccion('Aprobar');">
                                <i class="fa-solid fa-stamp"></i> Conceder Aprobación Final
                            </button>
                        <?php else: ?>
                            <div class="alert alert-success py-2 px-3 mb-0 w-100 text-center fw-bold">
                                <i class="fa-solid fa-circle-check"></i> Esta Hoja de Trabajo ha completado el 100% de su ciclo de vida institucional y se encuentra cerrada.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </form>

            <!-- Historial Auditado -->
            <div class="row w-100 mt-3">
                <div class="col-12">
                    <h2><i class="fa-solid fa-clock-rotate-left"></i> Historial Auditado de Movimientos</h2>
                </div>
                <div class="col-12">
                    <table id="tblHistorial" class="table table-striped" style="width:100%">
                        <thead>
                            <tr>
                                <th># Paso</th>
                                <th>Fecha y Hora</th>
                                <th>Usuario / Rol</th>
                                <th>Acción Realizada</th>
                                <th>Observaciones / Motivo</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($historial)): ?>
                                <?php foreach ($historial as $t):
                                    $acc = $t['accion'];
                                    $bCls = 'bg-info';
                                    if (stripos($acc, 'Aprob') !== false)                                     { $bCls = 'bg-success'; }
                                    elseif (stripos($acc, 'Devuel') !== false || stripos($acc, 'Rechaz') !== false) { $bCls = 'bg-danger'; }
                                    elseif (stripos($acc, 'Cerr') !== false)                                  { $bCls = 'bg-secondary'; }
                                ?>
                                <tr>
                                    <td class="text-center">
                                        <span class="badge bg-secondary fw-bold">Paso <?= (int)$t['paso_flujo']; ?></span>
                                    </td>
                                    <td>
                                        <i class="fa-regular fa-calendar-days text-muted me-1"></i>
                                        <?= date('d/m/Y H:i:s', strtotime($t['fecha_registro'])); ?>
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($t['nomusu'] ?? 'Usuario ID '.$t['id_usuario']); ?></strong>
                                        <br><small class="text-muted"><?= htmlspecialchars($t['rol_usuario'] ?? 'Funcionario'); ?></small>
                                    </td>
                                    <td>
                                        <span class="badge <?= $bCls; ?>"><?= htmlspecialchars($acc); ?></span>
                                    </td>
                                    <td><?= nl2br(htmlspecialchars($t['observacion'] ?? '---')); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        <i class="fa-solid fa-inbox fa-2x mb-2 d-block"></i>
                                        No existen movimientos de trazabilidad registrados para esta Hoja de Trabajo.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th># Paso</th>
                                <th>Fecha y Hora</th>
                                <th>Usuario / Rol</th>
                                <th>Acción Realizada</th>
                                <th>Observaciones / Motivo</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

        </div><!-- /.filter-toolbar-container -->
    </div><!-- /.inser -->

    <?php else: ?>

    <!-- Estado sin hoja seleccionada -->
    <div class="filter-toolbar-container text-center">
        <div class="w-100 py-5">
            <i class="fa-solid fa-folder-open fa-3x text-muted mb-3 d-block"></i>
            <h4 class="fw-bold">Seleccione una Hoja de Trabajo</h4>
            <p class="text-muted mx-auto" style="max-width:480px;">
                Utilice el selector superior para consultar el historial auditado y gestionar los estados del flujo institucional.
            </p>
        </div>
    </div>

    <?php endif; ?>

    <!-- ===== TABLA GENERAL DE MONITOREO ===== -->
    <div class="filter-toolbar-container">
        <h2><i class="fa-solid fa-list-check"></i> Monitoreo General de Hojas de Trabajo</h2>
        <div class="w-100">
            <table id="tblMonitoreo" class="table table-striped" style="width:100%">
                <thead>
                    <tr>
                        <th>Radicado</th>
                        <th>Programa de Formación</th>
                        <th>Empresa Solicitante</th>
                        <th>Cupo</th>
                        <th>Paso Actual</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($datHoj)): ?>
                        <?php foreach ($datHoj as $dh):
                            $esDev = (stripos($dh['estado'], 'Devuelto') !== false || stripos($dh['estado'], 'Rechazado') !== false);
                            $esFin = ((int)$dh['paso_actual'] === 4 && stripos($dh['estado'], 'Aprobado') !== false);
                        ?>
                        <tr>
                            <td class="fw-bold">#<?= str_pad($dh['idnorad'], 5, '0', STR_PAD_LEFT); ?>
                                <?php if ($esDemo && $commtrz->esDemo($dh['idnorad'])): ?><span class="badge text-bg-warning" style="font-size:9px;">demo</span><?php endif; ?>
                            </td>
                            <td><strong><?= htmlspecialchars($dh['nompro'] ?? 'Programa sin asignar'); ?></strong></td>
                            <td><?= htmlspecialchars($dh['nomemp'] ?? 'No registrada'); ?></td>
                            <td><?= (int)$dh['cupo']; ?> cupos</td>
                            <td><span class="badge bg-secondary">Paso <?= (int)$dh['paso_actual']; ?></span></td>
                            <td>
                                <?php if ($esFin): ?>
                                    <span class="badge bg-success">Finalizado</span>
                                <?php elseif ($esDev): ?>
                                    <span class="badge bg-danger">Devuelto</span>
                                <?php else: ?>
                                    <span class="badge bg-info">En Proceso</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="home.php?pg=2016&idnorad=<?= $dh['idnorad']; ?>" class="btn btn-outline-primary btn-sm shadow-sm" title="Gestionar Flujo">
                                    <i class="fa-solid fa-arrows-spin"></i> Gestionar
                                </a>
                                <a href="home.php?pg=2010&id=<?= $dh['idnorad']; ?>" class="btn btn-outline-secondary btn-sm shadow-sm" target="_blank" title="Imprimir Formato Oficial">
                                    <i class="fa-solid fa-print"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                No existen Hojas de Trabajo registradas en el sistema.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th>Radicado</th>
                        <th>Programa de Formación</th>
                        <th>Empresa Solicitante</th>
                        <th>Cupo</th>
                        <th>Paso Actual</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <!-- ===== /TABLA GENERAL ===== -->

</div>

<script>
function ejecutarAccion(accion) {
    var inputAcc = document.getElementById('inputAccion');
    var txtObs   = document.getElementById('txtObservacion');
    var spanReq  = document.getElementById('spanRequerido');

    inputAcc.value = accion;

    if (accion.toLowerCase() === 'devolver' || accion.toLowerCase() === 'rechazar') {
        spanReq.style.display = 'inline';
        if (!txtObs.value.trim()) {
            alert('ATENCIÓN: Para devolver o rechazar una Hoja de Trabajo, debe ingresar obligatoriamente una observación o motivo detallado.');
            txtObs.focus();
            return false;
        }
        if (!confirm('¿Confirma que desea DEVOLVER esta Hoja de Trabajo con las observaciones ingresadas?')) {
            return false;
        }
    } else {
        spanReq.style.display = 'none';
        if (!confirm('¿Confirma que desea ' + accion.toUpperCase() + ' esta Hoja de Trabajo y avanzar en el flujo institucional?')) {
            return false;
        }
    }

    document.getElementById('formTrazabilidad').submit();
}

function validarAccionFlujo(form) {
    var acc = (form.accion.value || '').trim();
    var obs = (form.observacion.value || '').trim();
    if (!acc) {
        alert('Seleccione primero una acción del flujo (Enviar, Aprobar o Devolver).');
        return false;
    }
    if ((acc.toLowerCase() === 'devolver' || acc.toLowerCase() === 'rechazar') && !obs) {
        alert('ATENCIÓN: Debe diligenciar el campo de observaciones para proceder con la devolución.');
        return false;
    }
    return true;
}

$(function () {
    // Evitar que Enter dentro de la observación envíe el formulario sin acción
    if (document.getElementById('txtObservacion')) {
        document.getElementById('txtObservacion').addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && !e.shiftKey && !e.ctrlKey) {
                e.preventDefault();
            }
        });
    }

    // DataTables para el historial y el monitoreo (si la librería está cargada)
    if (typeof $.fn.DataTable !== 'undefined') {
        var $mon = $('#tblMonitoreo');
        if ($mon.length && !$.fn.DataTable.isDataTable($mon)) {
            $mon.DataTable({
                pageLength: 10,
                lengthMenu: [[5, 10, 25, -1], [5, 10, 25, 'Todos']],
                order: [[0, 'desc']],
                language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' }
            });
        }
        var $his = $('#tblHistorial');
        if ($his.length && !$.fn.DataTable.isDataTable($his)) {
            $his.DataTable({
                pageLength: 10,
                lengthMenu: [[5, 10, 25, -1], [5, 10, 25, 'Todos']],
                order: [],
                language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' }
            });
        }
    }
});
</script>