<?php 
include("controllers/horccali.php"); 
?>

<div class="conte">
    <?php echo titulo2("<i class='fa-solid fa-graduation-cap'></i> Calificar Actividades", 2); ?>
    <?php require_once("views/vscopehya.php"); ?>

    <!-- Alertas de Estado / Mensajes de Operación -->
    <?php if (isset($mensaje) && $mensaje): ?>
        <div class="alert alert-<?=$tipoMensaje;?> alert-dismissible fade show shadow-sm my-3" role="alert">
            <i class="fa-solid <?=($tipoMensaje === 'success') ? 'fa-circle-check text-success' : (($tipoMensaje === 'danger') ? 'fa-circle-xmark text-danger' : 'fa-triangle-exclamation text-warning');?> me-2"></i>
            <?=$mensaje;?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    <?php endif; ?>

    <!-- ==================================================================== -->
    <!-- MODO 1: VISTA PERSONAL PARA EL APRENDIZ                              -->
    <!-- ==================================================================== -->
    <?php if ($esAprendiz): ?>

        <!-- BANNER DE BIENVENIDA AL APRENDIZ (ESTILO INSTITUCIONAL SENA) -->
        <div class="card mb-3 border-0 shadow-sm" style="border-radius: 12px; background: linear-gradient(135deg, #006b29 0%, #004d1d 100%);">
            <div class="card-body p-3 text-white">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-white text-success d-flex align-items-center justify-content-center shadow-sm" style="width: 48px; height: 48px; font-size: 1.4rem;">
                            <i class="fa-solid fa-user-graduate" style="color: #006b29;"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 fw-bold text-white"><?=htmlspecialchars($nomusuSesion);?></h5>
                            <small class="text-white opacity-75">
                                <i class="fa-solid fa-id-badge me-1"></i> Consulta de Calificaciones y Criterios Evaluativos
                            </small>
                        </div>
                    </div>
                    <div>
                        <span class="badge bg-white fs-6 py-2 px-3 fw-bold shadow-sm" style="color: #006b29;">
                            <i class="fa-solid fa-shield-halved me-1"></i> Mi Calificación Personal
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- FORMULARIO DE CONSULTA: NÚMERO DE FICHA Y ACTIVIDAD (ESTILO TOOLBAR VVIDGEN) -->
        <div class="filter-toolbar-container mb-3">
            <form method="GET" action="home.php" id="formSeleccionAprendiz" class="w-100 row g-2 align-items-end m-0">
                <input type="hidden" name="pg" value="<?=$pag;?>">

                <!-- Número de Ficha -->
                <div class="col-12 col-md-5">
                    <label for="idfic" class="form-label fw-bold small mb-1 text-dark">
                        <i class="fa-solid fa-users-rectangle text-success me-1"></i> Número de Ficha:
                    </label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white"><i class="fa-solid fa-hashtag text-success"></i></span>
                        <input type="text" class="form-control" id="idfic" name="idfic" 
                               value="<?=htmlspecialchars($idficSeleccionada);?>" 
                               list="fichasAprendizList" required>
                        <datalist id="fichasAprendizList">
                            <?php foreach ($listaFichas as $f): ?>
                                <option value="<?=$f['idfic'];?>"><?=$f['idfic'];?> - <?=htmlspecialchars($f['nompro'] ?? $f['nomfic'] ?? 'Ficha');?></option>
                            <?php endforeach; ?>
                        </datalist>
                    </div>
                </div>

                <!-- Número de Actividad -->
                <div class="col-12 col-md-5">
                    <label for="idact" class="form-label fw-bold small mb-1 text-dark">
                        <i class="fa-solid fa-list-check text-success me-1"></i> Número de Actividad:
                    </label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white"><i class="fa-solid fa-list-ol text-success"></i></span>
                        <input type="number" class="form-control" id="idact" name="idact" 
                               value="<?=htmlspecialchars($idactSeleccionada);?>" 
                              
                               list="actividadesAprendizList" required>
                        <datalist id="actividadesAprendizList">
                            <?php foreach ($listaActividades as $act): ?>
                                <option value="<?=$act['idact'];?>">Actividad #<?=$act['idact'];?>: <?=htmlspecialchars($act['nomact']);?></option>
                            <?php endforeach; ?>
                        </datalist>
                    </div>
                </div>

                <!-- Botón Consultar -->
                <div class="col-12 col-md-2 d-flex gap-1">
                    <button type="submit" class="btn btn-success btn-sm flex-grow-1 fw-bold shadow-sm">
                        <i class="fa-solid fa-magnifying-glass me-1"></i> Consultar
                    </button>
                </div>
            </form>
        </div>

        <!-- TARJETA CON LA NOTA INDIVIDUAL Y DETALLE DE CRITERIOS -->
        <?php if (!empty($actividadActual) && !empty($evaluacionMiAprendiz)): ?>
            <div class="card mb-4 shadow-sm border-0" style="border-radius: 10px;">
                <div class="card-header py-3 bg-white border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h5 class="mb-0 fw-bold text-dark"><i class="fa-solid fa-clipboard-check me-2 text-success"></i><?=htmlspecialchars($actividadActual['nomact']);?></h5>
                        <small class="text-muted">
                            <strong>Competencia:</strong> <?=htmlspecialchars($actividadActual['nomcom']);?> | 
                            <strong>Ficha:</strong> <?=htmlspecialchars($idficSeleccionada);?>
                        </small>
                    </div>
                    <div>
                        <?php 
                        $notaApr = $evaluacionMiAprendiz['nota'];
                        $esCumple = ($notaApr !== null && $notaApr >= 70);
                        $bgBadge = $esCumple ? 'background-color: #dcfce7; color: #166534; border: 1px solid #bbf7d0;' : 'background-color: #ffdad6; color: #ba1a1a; border: 1px solid #ffb4ab;';
                        ?>
                        <?php if ($notaApr !== null): ?>
                            <span class="badge py-2 px-3 shadow-sm fw-bold fs-6" style="<?=$bgBadge;?>">
                                PUNTOS: <?=$notaApr;?> / 100 (<?=$esCumple ? 'CUMPLE' : 'NO CUMPLE';?>)
                            </span>
                        <?php else: ?>
                            <span class="badge bg-warning text-dark fs-6 py-2 px-3 shadow-sm">
                                <i class="fa-solid fa-clock me-1"></i> Pendiente de Calificación
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-body p-4 bg-white">
                    <!-- Observación / Retroalimentación ("el por qué") -->
                    <?php if (!empty($evaluacionMiAprendiz['observacion'])): ?>
                        <div class="alert alert-light border border-success border-start border-4 rounded-3 mb-4">
                            <div class="d-flex align-items-start gap-3">
                                <div class="text-success fs-3 mt-1">
                                    <i class="fa-solid fa-comment-dots" style="color: #006b29;"></i>
                                </div>
                                <div>
                                    <strong class="fs-6 d-block mb-1 text-success">
                                        <i class="fa-solid fa-chalkboard-user me-1"></i> Observaciones y Retroalimentación del Instructor:
                                    </strong>
                                    <p class="mb-1 text-dark fs-6"><?=nl2br(htmlspecialchars($evaluacionMiAprendiz['observacion']));?></p>
                                    <small class="text-muted">
                                        Evaluado por: <strong><?=htmlspecialchars($evaluacionMiAprendiz['nominstructor']);?></strong> 
                                        <?php if (!empty($evaluacionMiAprendiz['fecha_evaluacion'])): ?>
                                            el <?=htmlspecialchars($evaluacionMiAprendiz['fecha_evaluacion']);?>
                                        <?php endif; ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <h6 class="fw-bold mb-3 text-secondary text-uppercase" style="letter-spacing: 0.5px;">
                        <i class="fa-solid fa-tasks me-2 text-success"></i> Indicadores de Logro y Criterios Evaluados
                    </h6>

                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0">
                            <thead class="table-light text-center small">
                                <tr>
                                    <th style="width: 60px;">ÍTEM</th>
                                    <th class="text-start">INDICADOR DE LOGRO / CRITERIO</th>
                                    <th style="width: 180px;">RESULTADO</th>
                                    <th style="width: 130px;">PUNTOS</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($evaluacionMiAprendiz['criterios'] as $cri): ?>
                                    <?php 
                                    $val = (int)$cri['valor'];
                                    $badgeStyle = 'background-color: #dcfce7; color: #166534; border: 1px solid #bbf7d0;';
                                    $iconCls = 'fa-circle-check';
                                    if ($val === 70) {
                                        $badgeStyle = 'background-color: #dbeafe; color: #1e40af; border: 1px solid #bfdbfe;';
                                        $iconCls = 'fa-triangle-exclamation';
                                    } elseif ($val === 0) {
                                        $badgeStyle = 'background-color: #ffdad6; color: #ba1a1a; border: 1px solid #ffb4ab;';
                                        $iconCls = 'fa-circle-xmark';
                                    }
                                    ?>
                                    <tr>
                                        <td class="text-center fw-bold fs-5 text-success"><?=htmlspecialchars($cri['letra'] ?? 'A');?></td>
                                        <td>
                                            <div class="fw-semibold text-dark"><?=htmlspecialchars($cri['nomcri']);?></div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge py-2 px-3 fs-6" style="<?=$badgeStyle;?>">
                                                <i class="fa-solid <?=$iconCls;?> me-1"></i> <?=htmlspecialchars($cri['estado_criterio']);?>
                                            </span>
                                        </td>
                                        <td class="text-center fw-bold fs-6">
                                            <?=$val;?> / 100
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php elseif (!empty($misActividadesEvaluadas)): ?>
            <!-- Historial de todas las actividades evaluadas para el aprendiz -->
            <div class="card shadow-sm border-0 mb-4" style="border-radius: 10px;">
                <div class="card-header bg-white border-bottom py-3">
                    <strong class="text-dark fs-6"><i class="fa-solid fa-list-check text-success me-2"></i> Mis Evaluaciones Registradas en la Ficha <?=$idficSeleccionada;?>:</strong>
                </div>
                <div class="list-group list-group-flush">
                    <?php foreach ($misActividadesEvaluadas as $mact): ?>
                        <a href="home.php?pg=<?=$pag;?>&idfic=<?=$mact['idfic'];?>&idact=<?=$mact['idact'];?>" 
                           class="list-group-item list-group-item-action d-flex justify-content-between align-items-center p-3">
                            <div>
                                <h6 class="mb-1 text-dark fw-bold"><?=htmlspecialchars($mact['nomact']);?></h6>
                                <small class="text-muted">
                                    <strong>Competencia:</strong> <?=htmlspecialchars($mact['nomcom']);?> | <strong>Ficha:</strong> <?=htmlspecialchars($mact['idfic']);?>
                                </small>
                                <?php if (!empty($mact['observacion'])): ?>
                                    <div class="small text-secondary mt-1 text-truncate" style="max-width: 550px;">
                                        <i class="fa-regular fa-comment-dots text-success me-1"></i> <?=htmlspecialchars($mact['observacion']);?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div>
                                <span class="badge fs-6 py-2 px-3 shadow-sm" style="<?=($mact['nota'] >= 70) ? 'background-color: #dcfce7; color: #166534; border: 1px solid #bbf7d0;' : 'background-color: #ffdad6; color: #ba1a1a; border: 1px solid #ffb4ab;';?>">
                                    <?=$mact['nota'];?> / 100 (<?=($mact['nota'] >= 70) ? 'CUMPLE' : 'NO CUMPLE';?>)
                                </span>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="p-4 text-center text-muted bg-white border rounded shadow-sm mb-4">
                <i class="fa-solid fa-circle-info fa-2x mb-2 d-block text-success opacity-75"></i>
                <span class="fw-bold text-dark d-block">Consulta de Calificaciones</span>
                <small class="text-muted d-block">Ingrese el <strong>Número de Ficha</strong> y el <strong>Número de Actividad</strong> en el formulario superior para consultar su calificación.</small>
            </div>
        <?php endif; ?>

    <!-- ==================================================================== -->
    <!-- MODO 2: VISTA PARA EL INSTRUCTOR / EVALUADOR                         -->
    <!-- ==================================================================== -->
    <?php else: ?>

        <!-- DASHBOARD DE CALIFICACIONES (ESTILO TARJETAS KPI VVIDGEN) -->
        <?php if (!empty($actividadActual) && !empty($idficSeleccionada)): ?>
            <div class="row mt-2 mb-3 g-3">
                <!-- 1. Total Aprendices en Ficha -->
                <div class="col-12 col-md-6 col-xl-3">
                    <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #3e4a3d;">Total Aprendices</span>
                                <div class="d-flex align-items-baseline gap-2 mt-2">
                                    <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #0b1c30;"><?=number_format($metricasDashboard['total_aprendices']);?></span>
                                    <span style="font-size: 13px; color: #3e4a3d;">en ficha</span>
                                </div>
                            </div>
                            <div class="kpi-icon-box" style="background-color: #b5dcfe; color: #3c627f;">
                                <i class="fa-solid fa-users fs-4"></i>
                            </div>
                        </div>
                        <div class="kpi-footer-strip">
                            <span class="fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 11px; color: #3c627f;">
                                <i class="fa-solid fa-hashtag" style="font-size: 12px;"></i> Ficha: <?=htmlspecialchars($idficSeleccionada);?>
                            </span>
                            <span style="font-size: 11px; color: #3e4a3d;">Matriculados</span>
                        </div>
                    </div>
                </div>

                <!-- 2. Aprendices Calificados / Evaluados -->
                <div class="col-12 col-md-6 col-xl-3">
                    <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #006b29;">Calificados</span>
                                <div class="d-flex align-items-baseline gap-2 mt-2">
                                    <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #006b29;"><?=number_format($metricasDashboard['evaluados']);?></span>
                                    <span style="font-size: 13px; color: rgba(0, 107, 41, 0.8);">/ <?=number_format($metricasDashboard['total_aprendices']);?></span>
                                </div>
                            </div>
                            <div class="kpi-icon-box" style="background-color: #7afd88; color: #006b24;">
                                <i class="fa-solid fa-clipboard-check fs-4"></i>
                            </div>
                        </div>
                        <div class="kpi-footer-strip">
                            <span class="fw-semibold d-inline-flex align-items-center gap-2" style="font-size: 11px; color: #006b29;">
                                <span class="pulse-dot"></span> Evaluados: <?=($metricasDashboard['total_aprendices'] > 0 ? round(($metricasDashboard['evaluados'] / $metricasDashboard['total_aprendices']) * 100) : 0);?>%
                            </span>
                            <span style="font-size: 11px; color: #3e4a3d;">Pendientes: <?=number_format($metricasDashboard['pendientes']);?></span>
                        </div>
                    </div>
                </div>

                <!-- 3. Cumplen (>= 70) -->
                <div class="col-12 col-md-6 col-xl-3">
                    <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #006b29;">Cumplen (>= 70)</span>
                                <div class="d-flex align-items-baseline gap-2 mt-2">
                                    <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #0b1c30;"><?=number_format($metricasDashboard['aprobados'] + $metricasDashboard['parciales']);?></span>
                                    <span style="font-size: 13px; color: #3e4a3d;">aprendices</span>
                                </div>
                            </div>
                            <div class="kpi-icon-box" style="background-color: #dcfce7; color: #166534;">
                                <i class="fa-solid fa-circle-check fs-4"></i>
                            </div>
                        </div>
                        <div class="kpi-footer-strip">
                            <span class="fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 11px; color: #166534;">
                                <i class="fa-solid fa-arrow-trend-up" style="font-size: 12px;"></i> <?=$metricasDashboard['porcentaje_aprobacion'];?>% cumplimiento
                            </span>
                            <span style="font-size: 11px; color: #3e4a3d;">Aprobados</span>
                        </div>
                    </div>
                </div>

                <!-- 4. Promedio General -->
                <div class="col-12 col-md-6 col-xl-3">
                    <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #854d0e;">Promedio Grupo</span>
                                <div class="d-flex align-items-baseline gap-2 mt-2">
                                    <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #0b1c30;"><?=$metricasDashboard['promedio_general'];?></span>
                                    <span style="font-size: 13px; color: #3e4a3d;">/ 100</span>
                                </div>
                            </div>
                            <div class="kpi-icon-box" style="background-color: #fef08a; color: #854d0e;">
                                <i class="fa-solid fa-chart-line fs-4"></i>
                            </div>
                        </div>
                        <div class="kpi-footer-strip">
                            <span class="fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 11px; color: #ba1a1a;">
                                <i class="fa-solid fa-triangle-exclamation" style="font-size: 12px;"></i> No cumplen: <?=number_format($metricasDashboard['no_aprobados']);?>
                            </span>
                            <span style="font-size: 11px; color: #3e4a3d;">Escala</span>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- FORMULARIO DIRECTO: SOLICITAR NÚMERO DE FICHA Y NÚMERO DE ACTIVIDAD (ESTILO TOOLBAR VVIDGEN) -->
        <div class="filter-toolbar-container mb-3">
            <form method="GET" action="home.php" id="formSeleccionActividad" class="w-100 row g-2 align-items-end m-0">
                <input type="hidden" name="pg" value="<?=$pag;?>">

                <!-- 1. Número de Ficha -->
                <div class="col-12 col-md-5">
                    <label for="idfic" class="form-label fw-bold small mb-1 text-dark">
                        <i class="fa-solid fa-users-rectangle text-success me-1"></i> Número de Ficha de Formación:
                    </label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white"><i class="fa-solid fa-hashtag text-success"></i></span>
                        <input type="text" class="form-control fw-bold" id="idfic" name="idfic" 
                               value="<?=htmlspecialchars($idficSeleccionada);?>" 
                               list="listaFichasSugeridas" required>
                        <datalist id="listaFichasSugeridas">
                            <?php foreach ($listaFichas as $f): ?>
                                <option value="<?=$f['idfic'];?>"><?=$f['idfic'];?> - <?=htmlspecialchars($f['nompro'] ?? $f['nomfic'] ?? 'Ficha');?></option>
                            <?php endforeach; ?>
                        </datalist>
                    </div>
                </div>

                <!-- 2. Número de Actividad -->
                <div class="col-12 col-md-5">
                    <label for="idact" class="form-label fw-bold small mb-1 text-dark">
                        <i class="fa-solid fa-list-check text-success me-1"></i> Número de Actividad:
                    </label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white"><i class="fa-solid fa-list-ol text-success"></i></span>
                        <input type="number" class="form-control fw-bold" id="idact" name="idact" 
                               value="<?=htmlspecialchars($idactSeleccionada);?>" 
                               min="1" 
                               list="listaActividadesSugeridas" required>
                        <datalist id="listaActividadesSugeridas">
                            <?php foreach ($listaActividades as $act): ?>
                                <option value="<?=$act['idact'];?>">Actividad #<?=$act['idact'];?>: <?=htmlspecialchars($act['nomact']);?></option>
                            <?php endforeach; ?>
                        </datalist>
                    </div>
                </div>

                <!-- 3. Botones Cargar y Reiniciar -->
                <div class="col-12 col-md-2 d-flex gap-1">
                    <button type="submit" class="btn btn-success btn-sm flex-grow-1 fw-bold shadow-sm" title="Identificar actividad y cargar aprendices">
                        <i class="fa-solid fa-magnifying-glass me-1"></i> Cargar
                    </button>
                    <a href="home.php?pg=<?=$pag;?>" class="btn btn-outline-secondary btn-sm" title="Limpiar formulario">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                </div>
            </form>
        </div>

        <?php if (!empty($actividadActual) && !empty($idficSeleccionada)): ?>

            <!-- ==================================================================== -->
            <!-- INSTRUMENTO DE EVALUACIÓN / CABECERA OFICIAL                         -->
            <!-- ==================================================================== -->
            <div class="card mb-3 border-0 shadow-sm" style="border-radius: 8px;">
                <div class="card-header bg-white border-bottom py-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <span class="fw-bold small text-uppercase text-dark" style="letter-spacing: 0.5px;">
                        <i class="fa-solid fa-file-invoice text-success me-2"></i> Instrumento de Evaluación / Lista de Chequeo
                    </span>
                    <span class="badge bg-success shadow-sm px-3 py-1">Ficha: <?=htmlspecialchars($idficSeleccionada);?> | Actividad #<?=htmlspecialchars($idactSeleccionada);?></span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm m-0 align-middle small">
                            <tbody>
                                <!-- Programa -->
                                <tr class="table-light">
                                    <td colspan="4" class="text-center fw-bold py-2 text-uppercase text-dark" style="letter-spacing: 0.5px;">
                                        <?=htmlspecialchars($actividadActual['nompro']);?>
                                    </td>
                                </tr>
                                <!-- Fecha / Guía / Tema -->
                                <tr>
                                    <td style="width: 15%;" class="fw-bold bg-light">FECHA:</td>
                                    <td style="width: 35%;"><?=date('d / m / Y');?></td>
                                    <td style="width: 15%;" class="fw-bold bg-light">PROGRAMA:</td>
                                    <td style="width: 35%;" class="fw-bold text-dark"><?=htmlspecialchars($actividadActual['nompro']);?></td>
                                </tr>
                                <!-- Ficha / Instructor -->
                                <tr>
                                    <td class="fw-bold bg-light">N° FICHA:</td>
                                    <td class="fw-bold text-success fs-6"><?=htmlspecialchars($idficSeleccionada);?></td>
                                    <td class="fw-bold bg-light">INSTRUCTOR:</td>
                                    <td class="fw-bold text-dark"><?=htmlspecialchars($actividadActual['nominstructor'] ?? $nomusuSesion);?></td>
                                </tr>
                                <!-- Fase / Planeación -->
                                <tr>
                                    <td class="fw-bold bg-light">FASE:</td>
                                    <td><?=htmlspecialchars($actividadActual['fase'] ?? 'Planeación');?></td>
                                    <td class="fw-bold bg-light">DURACIÓN / TIPO:</td>
                                    <td><?=htmlspecialchars($actividadActual['duract'] ?? 2);?> Horas | <span class="badge bg-light text-dark border"><i class="fa-solid fa-check-square me-1 text-success"></i> Producto / Desempeño</span></td>
                                </tr>
                                <!-- Competencia -->
                                <tr>
                                    <td class="fw-bold bg-light">COMPETENCIA:</td>
                                    <td colspan="3" class="fw-semibold text-dark">
                                        <?=htmlspecialchars($actividadActual['idcom']);?> - <?=htmlspecialchars($actividadActual['nomcom']);?>
                                    </td>
                                </tr>
                                <!-- Resultado de Aprendizaje / Descripción -->
                                <tr>
                                    <td class="fw-bold bg-light">RESULTADO / ACTIVIDAD:</td>
                                    <td colspan="3" class="text-muted">
                                        <strong><?=htmlspecialchars($actividadActual['nomact']);?>:</strong> <?=htmlspecialchars($actividadActual['nomres'] ?? $actividadActual['desact']);?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- ==================================================================== -->
            <!-- TABLA DE INDICADORES DE LOGRO / CRITERIOS                            -->
            <!-- ==================================================================== -->
            <div class="card mb-3 border-0 shadow-sm" style="border-radius: 8px;">
                <div class="card-header bg-white border-bottom py-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <span class="fw-bold text-dark small text-uppercase" style="letter-spacing: 0.5px;">
                        <i class="fa-solid fa-list-ol text-success me-2"></i> Indicadores de Logro / Criterios de Evaluación
                    </span>
                    <button type="button" class="btn btn-sm btn-outline-success fw-semibold shadow-sm py-1 px-2" data-bs-toggle="collapse" data-bs-target="#panelCriterios">
                        <i class="fa-solid fa-plus me-1"></i> Gestionar / Agregar Indicador
                    </button>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($criterios)): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm m-0 align-middle small">
                            <thead class="table-light text-center">
                                <tr>
                                    <th style="width: 60px;">ÍTEM</th>
                                    <th class="text-start">INDICADOR DE LOGRO / DESCRIPCIÓN DEL CRITERIO</th>
                                    <th style="width: 100px;">ESTADO</th>
                                    <th style="width: 70px;">ACCIÓN</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($criterios as $cri): ?>
                                    <tr>
                                        <td class="text-center fw-bold fs-6 text-success"><?=htmlspecialchars($cri['letra'] ?? 'A');?></td>
                                        <td class="fw-semibold text-dark"><?=htmlspecialchars($cri['nomcri']);?></td>
                                        <td class="text-center text-muted"><span class="badge bg-light text-dark border">Aplica</span></td>
                                        <td class="text-center">
                                            <form method="POST" action="home.php?pg=<?=$pag;?>&idfic=<?=$idficSeleccionada;?>&idact=<?=$idactSeleccionada;?>" class="d-inline" onsubmit="return confirm('¿Está seguro de eliminar este criterio?');">
                                                <input type="hidden" name="idact" value="<?=$idactSeleccionada;?>">
                                                <input type="hidden" name="idfic" value="<?=$idficSeleccionada;?>">
                                                <input type="hidden" name="idcri_eliminar" value="<?=$cri['idcri'];?>">
                                                <button type="submit" name="btnEliminarCriterio" class="btn btn-sm btn-outline-danger py-0 px-2 shadow-sm" title="Eliminar Criterio">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="p-4 text-center text-muted">
                        <i class="fa-solid fa-circle-info fa-2x mb-2 d-block text-success"></i>
                        <strong class="d-block">No hay criterios configurados para esta actividad.</strong>
                        <small>Agregue el primer indicador usando el panel de gestión.</small>
                    </div>
                    <?php endif; ?>

                    <!-- Panel colapsable nativo para agregar nuevo criterio -->
                    <div class="collapse p-3 bg-light border-top" id="panelCriterios">
                        <form method="POST" action="home.php?pg=<?=$pag;?>&idfic=<?=$idficSeleccionada;?>&idact=<?=$idactSeleccionada;?>" class="row g-2 align-items-center">
                            <input type="hidden" name="idact" value="<?=$idactSeleccionada;?>">
                            <input type="hidden" name="idfic" value="<?=$idficSeleccionada;?>">
                            <div class="col-12 col-md-9">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-white fw-bold">Nuevo Indicador:</span>
                                    <input type="text" class="form-control" name="nuevo_criterio_nom" placeholder="Escriba la descripción del indicador de logro..." required>
                                </div>
                            </div>
                            <div class="col-12 col-md-3">
                                <button type="submit" name="btnAgregarCriterio" class="btn btn-success btn-sm w-100 fw-bold shadow-sm">
                                    <i class="fa-solid fa-plus me-1"></i> Guardar Criterio
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- ==================================================================== -->
            <!-- PLANILLA MATRIZ DE CALIFICACIÓN DE APRENDICES (FORMULARIO POST)      -->
            <!-- ==================================================================== -->
            <?php if (!empty($aprendices)): ?>
                <form method="POST" action="home.php?pg=<?=$pag;?>&idfic=<?=$idficSeleccionada;?>&idact=<?=$idactSeleccionada;?>&p=<?=($paginaActual ?? 1);?>" id="formEvaluaciones">
                    <input type="hidden" name="idact" value="<?=$idactSeleccionada;?>">
                    <input type="hidden" name="idfic" value="<?=$idficSeleccionada;?>">
                    <input type="hidden" name="p" value="<?=($paginaActual ?? 1);?>">

                    <div class="card shadow-sm border-0 mb-4" style="border-radius: 8px;">
                        <div class="card-header bg-white border-bottom py-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div class="d-flex align-items-center gap-2">
                                <span class="fw-bold small text-uppercase text-dark" style="letter-spacing: 0.5px;">
                                    <i class="fa-solid fa-table-cells text-success me-2"></i> Planilla de Evaluación por Tareas / Criterios
                                </span>
                                <span class="badge bg-success shadow-sm px-2 py-1"><?=count($aprendices);?> Aprendices</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <button type="submit" name="btnGuardarEvaluacion" class="btn btn-success btn-sm px-3 fw-bold shadow-sm">
                                    <i class="fa-solid fa-floppy-disk me-1"></i> Guardar Calificaciones
                                </button>
                            </div>
                        </div>

                        <div class="card-body p-2 bg-white">
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered table-sm align-middle m-0" id="tablaCalificaciones" style="width:100%;">
                                    <thead class="table-light small text-center">
                                        <tr>
                                            <th style="width: 35px;" rowspan="2" class="align-middle bg-light text-dark">#</th>
                                            <th style="min-width: 220px;" rowspan="2" class="text-start align-middle bg-light text-dark">ESTUDIANTE</th>
                                            <th colspan="<?=count($criterios);?>" class="text-center text-dark fw-bold py-1 bg-light border-bottom">
                                                PUNTOS POR TAREA / CRITERIO
                                            </th>
                                            <th style="width: 110px;" rowspan="2" class="align-middle bg-light text-dark">PUNTOS TOTALES</th>
                                            <th style="width: 110px;" rowspan="2" class="align-middle bg-light text-dark">CUMPLE</th>
                                            <th style="min-width: 190px;" rowspan="2" class="align-middle bg-light text-dark">OBSERVACIONES</th>
                                        </tr>
                                        <tr class="bg-light">
                                            <?php foreach ($criterios as $cri): ?>
                                                <th style="min-width: 140px;" class="py-1" title="<?=htmlspecialchars($cri['nomcri']);?>">
                                                    <span class="badge fs-6 px-2 py-1 bg-dark text-white"><?=htmlspecialchars($cri['letra'] ?? 'A');?></span>
                                                    <small class="d-block fw-normal text-muted text-truncate mt-1" style="max-width: 140px;">
                                                        <?=htmlspecialchars($cri['nomcri']);?>
                                                    </small>
                                                </th>
                                            <?php endforeach; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $rowIdx = ($offsetAprendices ?? 0) + 1; 
                                        $listaRenderAprendices = $aprendicesPaginados ?? $aprendices;
                                        foreach ($listaRenderAprendices as $apr): 
                                        ?>
                                            <?php 
                                            $idusu = $apr['idusu'];
                                            $evalGuardada = $evaluacionesGuardadas[$idusu] ?? [];
                                            $notaGuardada = $notasGuardadas[$idusu]['nota'] ?? null;
                                            $obsGuardada = $notasGuardadas[$idusu]['observacion'] ?? '';

                                            // Cálculo PHP de promedio inicial por si ya tiene nota o valores
                                            $sumaValoresFila = 0;
                                            $totalCriFila = count($criterios);
                                            foreach ($criterios as $criF) {
                                                $idcriF = $criF['idcri'];
                                                $sumaValoresFila += isset($evalGuardada[$idcriF]) ? (int)$evalGuardada[$idcriF]['valor'] : 100;
                                            }
                                            $notaFilaInicial = ($notaGuardada !== null) ? (int)$notaGuardada : (($totalCriFila > 0) ? round($sumaValoresFila / $totalCriFila) : 100);
                                            $esCumpleFila = ($notaFilaInicial >= 70);
                                            ?>
                                            <tr class="fila-aprendiz">
                                                <!-- Índice -->
                                                <td class="text-center fw-bold text-muted"><?=$rowIdx++;?></td>
                                                
                                                <!-- Datos Aprendiz -->
                                                <td>
                                                    <div class="fw-bold text-dark text-uppercase"><?=htmlspecialchars($apr['nomusu']);?></div>
                                                    <small class="text-muted">Doc: <?=htmlspecialchars($apr['ndocusu']);?></small>
                                                </td>

                                                <!-- Columnas de Criterios (A, B, C, D...) -->
                                                <?php foreach ($criterios as $cri): ?>
                                                    <?php 
                                                    $idcri = $cri['idcri'];
                                                    $valorActual = isset($evalGuardada[$idcri]) ? (int)$evalGuardada[$idcri]['valor'] : 100;
                                                    ?>
                                                    <td class="text-center p-1">
                                                        <select class="form-select form-select-sm" 
                                                                name="evaluaciones[<?=$idusu;?>][criterios][<?=$idcri;?>]"
                                                                style="font-size: 0.8rem; font-weight: 600;">
                                                            <option value="100" <?=($valorActual === 100) ? 'selected' : '';?>>✅ Cumple (100)</option>
                                                            <option value="70" <?=($valorActual === 70) ? 'selected' : '';?>>⚠️ Parcial (70)</option>
                                                            <option value="0" <?=($valorActual === 0) ? 'selected' : '';?>>❌ No cumple (0)</option>
                                                        </select>
                                                    </td>
                                                <?php endforeach; ?>

                                                <!-- Puntos Totales Calculados -->
                                                <td class="text-center">
                                                    <span class="badge fs-6 px-2 py-1 shadow-sm" style="<?= $esCumpleFila ? 'background-color: #dcfce7; color: #166534; border: 1px solid #bbf7d0;' : 'background-color: #ffdad6; color: #ba1a1a; border: 1px solid #ffb4ab;'; ?>">
                                                        <?=$notaFilaInicial;?>
                                                    </span>
                                                </td>

                                                <!-- Cumple SI / NO -->
                                                <td class="text-center">
                                                    <span class="badge py-1 px-2 fw-bold" style="<?= $esCumpleFila ? 'background-color: #dcfce7; color: #166534; border: 1px solid #bbf7d0;' : 'background-color: #ffdad6; color: #ba1a1a; border: 1px solid #ffb4ab;'; ?>">
                                                        <?=$esCumpleFila ? 'CUMPLE' : 'NO CUMPLE';?>
                                                    </span>
                                                </td>

                                                <!-- Observaciones -->
                                                <td class="p-1">
                                                    <input type="text" class="form-control form-control-sm" 
                                                           name="evaluaciones[<?=$idusu;?>][observacion]" 
                                                           value="<?=htmlspecialchars($obsGuardada);?>" 
                                                           style="font-size: 0.82rem;">
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Barra de Paginación de Aprendices (10 por página, máximo 10 hojitas) -->
                            <?php if (isset($totalPaginas) && $totalPaginas > 1): ?>
                                <div class="d-flex flex-wrap justify-content-between align-items-center py-2 px-3 mt-2 bg-light border rounded">
                                    <div class="small text-muted">
                                        Mostrando aprendices <strong><?=($offsetAprendices + 1);?></strong> al <strong><?=min($offsetAprendices + $porPagina, $totalAprendices);?></strong> de un total de <strong><?=$totalAprendices;?></strong>
                                    </div>
                                    <nav aria-label="Navegación de aprendices">
                                        <ul class="pagination pagination-sm m-0 gap-1">
                                            <!-- Botón Anterior -->
                                            <li class="page-item <?=($paginaActual <= 1) ? 'disabled' : '';?>">
                                                <a class="page-link shadow-none" href="home.php?pg=<?=$pag;?>&idfic=<?=$idficSeleccionada;?>&idact=<?=$idactSeleccionada;?>&p=<?=($paginaActual - 1);?>" aria-label="Anterior">
                                                    <i class="fa-solid fa-chevron-left"></i>
                                                </a>
                                            </li>

                                            <!-- Páginas Numeradas (máx 10 páginas) -->
                                            <?php 
                                            $maxHojitas = min(10, $totalPaginas);
                                            // Centrar rango de páginas si hay más de 10
                                            $inicioPag = 1;
                                            $finPag = $maxHojitas;
                                            if ($totalPaginas > 10) {
                                                $inicioPag = max(1, $paginaActual - 4);
                                                $finPag = min($totalPaginas, $inicioPag + 9);
                                                if ($finPag - $inicioPag < 9) {
                                                    $inicioPag = max(1, $finPag - 9);
                                                }
                                            }
                                            for ($p = $inicioPag; $p <= $finPag; $p++): 
                                            ?>
                                                <li class="page-item <?=($p === $paginaActual) ? 'active' : '';?>">
                                                    <a class="page-link shadow-none <?=($p === $paginaActual) ? 'fw-bold bg-success border-success text-white' : 'text-dark';?>" 
                                                       href="home.php?pg=<?=$pag;?>&idfic=<?=$idficSeleccionada;?>&idact=<?=$idactSeleccionada;?>&p=<?=$p;?>">
                                                        <?=$p;?>
                                                    </a>
                                                </li>
                                            <?php endfor; ?>

                                            <!-- Botón Siguiente -->
                                            <li class="page-item <?=($paginaActual >= $totalPaginas) ? 'disabled' : '';?>">
                                                <a class="page-link shadow-none" href="home.php?pg=<?=$pag;?>&idfic=<?=$idficSeleccionada;?>&idact=<?=$idactSeleccionada;?>&p=<?=($paginaActual + 1);?>" aria-label="Siguiente">
                                                    <i class="fa-solid fa-chevron-right"></i>
                                                </a>
                                            </li>
                                        </ul>
                                    </nav>
                                </div>
                            <?php endif; ?>

                            <!-- Pie Informativo: Escala de Desempeño y Botones de Acción -->
                            <div class="row g-2 mt-3 pt-2 border-top align-items-center">
                                <div class="col-12 col-md-6">
                                    <div class="card card-body bg-light p-2 small border rounded-3">
                                        <strong class="text-dark"><i class="fa-solid fa-scale-balanced text-success me-1"></i> ESCALA DE DESEMPEÑO:</strong>
                                        <ul class="mb-0 ps-3 mt-1 text-muted">
                                            <li><strong class="text-success">70 A 100 PUNTOS:</strong> CUMPLE (Aprobado)</li>
                                            <li><strong class="text-danger">0 A 69 PUNTOS:</strong> NO CUMPLE (No Aprobado)</li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6 d-flex align-items-center justify-content-end gap-2">
                                    <a href="home.php?pg=<?=$pag;?>&idfic=<?=$idficSeleccionada;?>&idact=<?=$idactSeleccionada;?>&p=<?=($paginaActual ?? 1);?>" class="btn btn-outline-secondary btn-sm shadow-sm">
                                        <i class="fa-solid fa-arrow-rotate-left me-1"></i> Descartar Cambios
                                    </a>
                                    <button type="submit" name="btnGuardarEvaluacion" class="btn btn-success btn-sm px-4 fw-bold shadow-sm">
                                        <i class="fa-solid fa-floppy-disk me-1"></i> Guardar Calificaciones
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            <?php else: ?>
                <div class="p-4 text-center text-muted bg-white border rounded shadow-sm mb-4">
                    <i class="fa-solid fa-triangle-exclamation fa-2x mb-2 d-block text-warning opacity-75"></i>
                    <span class="fw-bold text-dark d-block">Sin aprendices encontrados</span>
                    <small class="text-muted d-block">No se encontraron aprendices matriculados en la ficha <strong><?=htmlspecialchars($idficSeleccionada);?></strong>.</small>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <div class="p-4 text-center text-muted bg-white border rounded shadow-sm mb-4">
                <i class="fa-solid fa-circle-info fa-2x mb-2 d-block text-success opacity-75"></i>
                <span class="fw-bold text-dark d-block">Selección de Actividad y Ficha</span>
                <small class="text-muted d-block">Ingrese el <strong>Número de Ficha</strong> y el <strong>Número de Actividad</strong> en el formulario superior y presione <strong>Cargar</strong> para ver la lista de chequeo y la planilla de aprendices.</small>
            </div>
        <?php endif; ?>

    <?php endif; ?>
</div>
