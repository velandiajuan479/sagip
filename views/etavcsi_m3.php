<?php
    require_once("controllers/etaccsi.php");

    // Identificación de la página actual y momento
    $pgActual = 1538; // Momento 3 - Evaluación Final y Juicio Evaluativo
    $tipo_formato = 303;

    // Determinación del Momento Evaluativo Seleccionado
    // 301 / Vacío = Momento 1 | 302 = Momento 2 | 303 = Momento 3
    $momentoActual = (($tipo_formato ?? '') == 302 ? '2' : (($tipo_formato ?? '') == 303 ? '3' : '1'));
    // IMPORTANTE: $vistaMomentoActual debe ser la idpag numérico registrado en tabla `pagina`
    // para que home.php lo valide correctamente con la función validar($pg)
    // idpag 1536 = etavcsi_m1 | 1537 = etavcsi_m2 | 1538 = etavcsi_m3
    $pgMomento = ($momentoActual == '2' ? 1537 : ($momentoActual == '3' ? 1538 : 1536));
?>
<div class="conte">
<?php
    $tituloPagina = "<i class='" . $icono . "'></i> Momento 3: Evaluación Final y Juicio Evaluativo de Etapa Productiva (GFPI-F-023)";
    echo titulo2($tituloPagina, 1);
?>

    <!-- 1. TARJETA DE FILTROS: SELECCIÓN DE MOMENTO EVALUATIVO Y FICHA ASIGNADA -->
    <div class="card p-4 mb-4 shadow-sm border-0">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <p class="text-muted small mb-0">
                Gestión de la <strong>Evaluación Final de Etapa Productiva</strong>. Registre el número de visitas, la retroalimentación de los 3 actores (coformador, instructor y aprendiz), valore los factores de desempeño y emita el <strong>Juicio de Evaluación Oficial (Aprobado / No Aprobado)</strong>.
            </p>
        </div>

        <div class="mb-3 fw-bold small text-secondary">
            <i class="fa-solid fa-file-lines me-1 text-primary"></i> Formato Evaluativo SENA
        </div>
        <div class="filter-pills-group flex-wrap mb-4">
            <?php $qFic = !empty($idficha) ? '&idficha=' . rawurlencode((string)$idficha) : ''; ?>
            <a class="btn-filter-pill<?= $momentoActual == '1' ? ' active' : '' ?>" href="<?= htmlspecialchars('home.php?pg=1536&tipo_formato=301' . $qFic) ?>">
                <span class="pill-dot bg-primary"></span>
                <span>Momento 1 - Concertación</span>
            </a>
            <a class="btn-filter-pill<?= $momentoActual == '2' ? ' active' : '' ?>" href="<?= htmlspecialchars('home.php?pg=1537&tipo_formato=302' . $qFic) ?>">
                <span class="pill-dot bg-success"></span>
                <span>Momento 2 - Seguimiento Parcial</span>
            </a>
            <a class="btn-filter-pill<?= $momentoActual == '3' ? ' active' : '' ?>" href="<?= htmlspecialchars('home.php?pg=1538&tipo_formato=303' . $qFic) ?>">
                <span class="pill-dot bg-warning"></span>
                <span>Momento 3 - Evaluación Final</span>
            </a>
        </div>

        <form id="frmConsultaFormatos" name="frmConsultaFormatos" class="row g-3" method="GET" action="home.php">
            <input type="hidden" name="pg" value="<?= htmlspecialchars($pgActual) ?>">
            <input type="hidden" name="tipo_formato" value="<?= $momentoActual == '2' ? '302' : ($momentoActual == '3' ? '303' : '301') ?>">

            <!-- Selector de Ficha Asignada -->
            <div class="col-md-10">
                <label for="idficha" class="form-label fw-bold small text-secondary">
                    <i class="fa-solid fa-layer-group me-1 text-primary"></i> Ficha de Formación Asignada <span class="text-danger">*</span>
                </label>
                <select id="idficha" name="idficha" class="form-select shadow-sm" required>
                    <option value="">-- Seleccione una Ficha --</option>
                    <?php if (!empty($fichasAsignadas)): ?>
                        <?php foreach ($fichasAsignadas as $f): ?>
                            <option value="<?= htmlspecialchars($f['idfic']) ?>" <?= (($idficha ?? '') == $f['idfic'] ? 'selected' : '') ?>>
                                <?= htmlspecialchars($f['idfic']) ?> - <?= htmlspecialchars($f['nomfic'] ?? $f['nompro'] ?? 'Ficha sin nombre') ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-success w-100 shadow">
                    <i class="fa-solid fa-magnifying-glass me-2"></i> Consultar
                </button>
            </div>
        </form>
    </div>

    <!-- 2. DASHBOARD DE EVALUACIÓN FINAL MOMENTO 3 -->
    <?php if (!empty($idficha) && !empty($aprendicesFicha)): ?>
        <?php
            $totAp = count($aprendicesFicha);
            $conJuicioEmitido = 0;
            $aprobados = 0;
            $noAprobados = 0;
            $pendientesM3 = 0;
            $sinConcertacionM1 = 0;

            foreach ($aprendicesFicha as $apItem) {
                if (empty($apItem['idcon'])) {
                    $sinConcertacionM1++;
                } else {
                    $tieneM3 = !empty($apItem['fecha_momento3_real']) || !empty($apItem['juicio_evaluativo']);
                    if ($tieneM3) {
                        $conJuicioEmitido++;
                        if (($apItem['juicio_evaluativo'] ?? '') === 'NO APROBADO' || ($apItem['estado'] ?? '') === 'Rechazado') {
                            $noAprobados++;
                        } else {
                            $aprobados++;
                        }
                    } else {
                        $pendientesM3++;
                    }
                }
            }
        ?>
        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <h5 class="text-secondary fw-bold mb-0 d-flex align-items-center">
                    <i class="fa-solid fa-chart-column me-2 text-success"></i>
                    Balance de Juicios Evaluativos Finales - Ficha <?= htmlspecialchars($idficha) ?>
                </h5>
                <span class="badge bg-light text-dark border px-3 py-2">
                    <i class="fa-solid fa-graduation-cap me-1 text-success"></i> Cierre oficial de etapa práctica
                </span>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted fw-bold text-uppercase">Aprendices en Ficha</small>
                                <h3 class="mb-0 fw-bold text-primary"><?= $totAp ?></h3>
                            </div>
                            <div class="kpi-icon-box bg-primary bg-opacity-10 text-primary">
                                <i class="fa-solid fa-users fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted fw-bold text-uppercase">Juicio: APROBADO</small>
                                <h3 class="mb-0 fw-bold text-success"><?= $aprobados ?></h3>
                            </div>
                            <div class="kpi-icon-box bg-success bg-opacity-10 text-success">
                                <i class="fa-solid fa-certificate fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted fw-bold text-uppercase">Juicio: NO APROBADO</small>
                                <h3 class="mb-0 fw-bold text-danger"><?= $noAprobados ?></h3>
                            </div>
                            <div class="kpi-icon-box bg-danger bg-opacity-10 text-danger">
                                <i class="fa-solid fa-triangle-exclamation fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted fw-bold text-uppercase">Pendientes Evaluación</small>
                                <h3 class="mb-0 fw-bold text-warning"><?= $pendientesM3 ?></h3>
                            </div>
                            <div class="kpi-icon-box bg-warning bg-opacity-10 text-warning">
                                <i class="fa-solid fa-clock fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php elseif (empty($idficha)): ?>
        <div class="card border-0 bg-light p-4 text-center rounded-3 shadow-sm mb-4">
            <div class="py-3">
                <i class="fa-solid fa-file-circle-check fa-3x text-muted mb-3"></i>
                <h5 class="fw-bold text-secondary">Seleccione una Ficha para Comenzar la Evaluación Final</h5>
                <p class="text-muted mb-0 mx-auto col-md-8">
                    Para emitir el <strong>Juicio Evaluativo Final de Etapa Productiva (Momento 3)</strong>, seleccione la ficha correspondiente arriba y presione <strong>Consultar</strong>.
                </p>
            </div>
        </div>
    <?php endif; ?>

    <!-- 3. FORMULARIO DILIGENCIAR / EDITAR MOMENTO 3 (EVALUACIÓN FINAL) -->
    <?php if (!empty($conEdit)): ?>
        <?php
            $fechaInicioM1 = $conEdit['fecha_inicio'] ?? date('Y-m-d');
            $fechaFinM1 = $conEdit['fecha_fin'] ?? date('Y-m-d', strtotime('+6 months'));
            $numVisitasActual = $conEdit['num_visitas'] ?? 3;
            $fechaMomento3Real = $conEdit['fecha_momento3_real'] ?? date('Y-m-d');
            $modalidadActual = $conEdit['modalidad'] ?? 'Presencial';
            $enlaceGrabacion = $conEdit['enlace_grabacion'] ?? '';
            $juicioActual = $conEdit['juicio_evaluativo'] ?? 'APROBADO';

            // Clasificar variables por dominio
            $varsTecnicas = [];
            $varsComportamentales = [];
            if (!empty($variablesEvaluacion)) {
                foreach ($variablesEvaluacion as $v) {
                    if ((int)$v['iddom'] === 31) {
                        $varsTecnicas[] = $v;
                    } elseif ((int)$v['iddom'] === 32) {
                        $varsComportamentales[] = $v;
                    }
                }
            }
        ?>
        <div class="card p-4 mb-5 shadow-sm border-0 border-top border-success border-4" id="formEvaluacionFinalM3">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <h4 class="mb-0 text-secondary d-flex align-items-center">
                    <i class="fa-solid fa-graduation-cap text-success me-2"></i>
                    Diligenciamiento Momento 3: Evaluación Final y Juicio Evaluativo (GFPI-F-023)
                </h4>
                <span class="badge bg-primary px-3 py-2">
                    Aprendiz: <?= htmlspecialchars($conEdit['nom_aprendiz'] ?? '') ?>
                </span>
            </div>
            <p class="text-muted small mb-4">
                Diligencie el formulario oficial de cierre. Registre el número de visitas, califique los factores, ingrese la retroalimentación de los 3 actores y emita el <strong>Juicio Evaluativo</strong>.
            </p>

            <form id="frmEvaluacionFinalM3" name="frmEvaluacionFinalM3" method="POST" action="home.php?pg=1538" class="row g-3">
                <input type="hidden" name="opera" value="guardar_m3">
                <input type="hidden" name="idcon" value="<?= htmlspecialchars($conEdit['idcon'] ?? '') ?>">
                <input type="hidden" name="idaprendiz" value="<?= htmlspecialchars($conEdit['idaprendiz'] ?? '') ?>">
                <input type="hidden" name="idficha" value="<?= htmlspecialchars($idficha ?? ($conEdit['idficha'] ?? '')) ?>">
                <input type="hidden" name="tipo_formato" value="303">

                <!-- SECCIÓN 1: DATOS GENERALES (READONLY DE LA CONCERTACIÓN) -->
                <div class="col-12">
                    <h6 class="text-success fw-bold border-bottom pb-2">
                        <i class="fa-solid fa-address-card me-1"></i> 1. Información General del Aprendiz y Ente Coformador
                    </h6>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold small text-muted">Nombre del Aprendiz</label>
                    <input type="text" class="form-control bg-light" readonly value="<?= htmlspecialchars($conEdit['nom_aprendiz'] ?? '') ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold small text-muted">Documento Identidad</label>
                    <input type="text" class="form-control bg-light" readonly value="<?= htmlspecialchars($conEdit['doc_aprendiz'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold small text-muted">Programa de Formación</label>
                    <input type="text" class="form-control bg-light" readonly value="<?= htmlspecialchars($conEdit['nom_programa'] ?? 'ANÁLISIS Y DESARROLLO DE SOFTWARE') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold small text-muted">No. Ficha</label>
                    <input type="text" class="form-control bg-light" readonly value="<?= htmlspecialchars($conEdit['idficha'] ?? $idficha ?? '') ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold small text-muted">Empresa Coformadora</label>
                    <input type="text" class="form-control bg-light" readonly value="<?= htmlspecialchars($conEdit['nombre_empresa'] ?? 'N/A') ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold small text-muted">NIT Empresa</label>
                    <input type="text" class="form-control bg-light" readonly value="<?= htmlspecialchars($conEdit['nit_empresa'] ?? 'N/A') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold small text-muted">Tutor / Jefe Inmediato</label>
                    <input type="text" class="form-control bg-light" readonly value="<?= htmlspecialchars($conEdit['nombre_jefe'] ?? 'N/A') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold small text-muted">Correo Empresa</label>
                    <input type="text" class="form-control bg-light" readonly value="<?= htmlspecialchars($conEdit['correo_jefe'] ?? 'N/A') ?>">
                </div>

                <!-- SECCIÓN 2: VISITAS, FECHAS Y MODALIDAD -->
                <div class="col-12 mt-4">
                    <h6 class="text-success fw-bold border-bottom pb-2">
                        <i class="fa-solid fa-calendar-check me-1"></i> 2. Parámetros de la Evaluación Final (Visitas y Fechas)
                    </h6>
                </div>

                <!-- Número de visitas (Al lado de la fecha de inicio, como solicitó el usuario) -->
                <div class="col-md-2">
                    <label class="form-label fw-bold small text-secondary">
                        <i class="fa-solid fa-list-ol me-1 text-primary"></i> No. de Visitas <span class="text-danger">*</span>
                    </label>
                    <input type="number" name="num_visitas" class="form-control shadow-sm fw-bold border-primary text-center" min="1" max="10" value="<?= htmlspecialchars($numVisitasActual) ?>" required>
                    <small class="text-muted fst-italic">Visitas ejecutadas</small>
                </div>

                <!-- Fecha Inicio EP (READONLY) -->
                <div class="col-md-2">
                    <label class="form-label fw-bold small text-secondary">
                        <i class="fa-solid fa-flag-checkered me-1 text-primary"></i> Fecha Inicio EP
                    </label>
                    <input type="date" class="form-control bg-light" readonly value="<?= htmlspecialchars($fechaInicioM1) ?>">
                    <small class="text-muted fst-italic">Concertada en M1</small>
                </div>

                <!-- Fecha Fin EP (Solicitado por el usuario) -->
                <div class="col-md-3">
                    <label class="form-label fw-bold small text-secondary">
                        <i class="fa-solid fa-calendar-xmark me-1 text-primary"></i> Fecha Fin EP <span class="text-danger">*</span>
                    </label>
                    <input type="date" name="fecha_fin" class="form-control shadow-sm" value="<?= htmlspecialchars($fechaFinM1) ?>" required>
                    <small class="text-muted fst-italic">Culminación de práctica</small>
                </div>

                <!-- Fecha Real de la Evaluación Final -->
                <div class="col-md-3">
                    <label class="form-label fw-bold small text-secondary">
                        <i class="fa-solid fa-calendar-plus me-1 text-success"></i> Fecha Evaluación Final <span class="text-danger">*</span>
                    </label>
                    <input type="date" name="fecha_momento3_real" class="form-control border-success shadow-sm" value="<?= htmlspecialchars($fechaMomento3Real) ?>" required>
                    <small class="text-muted fst-italic">Fecha de sesión de cierre</small>
                </div>

                <!-- Modalidad de la Sesión -->
                <div class="col-md-2">
                    <label class="form-label fw-bold small text-secondary">
                        <i class="fa-solid fa-video me-1 text-primary"></i> Modalidad <span class="text-danger">*</span>
                    </label>
                    <select name="modalidad" id="modalidad_seguimiento_m3" class="form-select shadow-sm" required>
                        <option value="Presencial" <?= ($modalidadActual === 'Presencial' ? 'selected' : '') ?>>Presencial</option>
                        <option value="Virtual" <?= ($modalidadActual === 'Virtual' ? 'selected' : '') ?>>Virtual</option>
                        <option value="Híbrida" <?= ($modalidadActual === 'Híbrida' ? 'selected' : '') ?>>Híbrida</option>
                    </select>
                </div>

                <!-- Enlace de Grabación (Completar cuando la sesión sea Virtual o Híbrida) -->
                <div class="col-12">
                    <div class="p-3 bg-light border border-info rounded-3">
                        <label class="form-label fw-bold small text-primary mb-1">
                            <i class="fa-solid fa-link me-1"></i> Enlace de Grabación de la Sesión Final <span class="text-danger">*</span>
                        </label>
                        <input type="url" name="enlace_grabacion" id="enlace_grabacion_m3" class="form-control" placeholder="https://teams.microsoft.com/... o https://meet.google.com/..." value="<?= htmlspecialchars($enlaceGrabacion) ?>">
                        <small class="text-muted">Requerido por lineamiento SENA para sesiones virtuales o mixtas.</small>
                    </div>
                </div>

                <!-- SECCIÓN 3: FACTORES TÉCNICOS (DOMINIO 31) -->
                <div class="col-12 mt-4">
                    <div class="p-2 bg-success bg-opacity-10 border-start border-success border-4 rounded mb-3">
                        <h6 class="text-success fw-bold mb-0">
                            <i class="fa-solid fa-gears me-2"></i> 3. Factores Técnicos (Dominio 31 - GFPI-F-023)
                        </h6>
                    </div>
                    <p class="text-muted small">
                        Valore el desempeño técnico final del aprendiz. El campo de observaciones enfrente se muestra siempre visible para registrar el detalle de cada factor marcado como <strong>"Por mejorar"</strong>.
                    </p>
                </div>

                <div class="col-12">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="col-1 text-center">Código</th>
                                    <th class="col-4">Factor Técnico a Evaluar</th>
                                    <th class="col-3 text-center">Valoración (Seleccionar 1)</th>
                                    <th class="col-4">Observaciones / Compromisos Finales</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($varsTecnicas)): ?>
                                    <?php foreach ($varsTecnicas as $vt): ?>
                                        <?php
                                            $idVal = $vt['idval'];
                                            $valActual = $detallesM2[$idVal]['valoracion'] ?? 'Satisfactorio';
                                            $obsActual = $detallesM2[$idVal]['observacion'] ?? '';
                                            $esPorMejorar = ($valActual === 'Por mejorar');
                                        ?>
                                        <tr>
                                            <td class="text-center fw-bold text-secondary"><?= $idVal ?></td>
                                            <td>
                                                <strong class="text-dark"><?= htmlspecialchars($vt['nomval']) ?></strong>
                                            </td>
                                            <td class="text-center">
                                                <div class="d-inline-flex gap-3 align-items-center">
                                                    <div class="form-check form-check-inline m-0">
                                                        <input class="form-check-input" type="radio" 
                                                               name="variables[<?= $idVal ?>][valoracion]" 
                                                               id="rad_m3_sat_<?= $idVal ?>" 
                                                               value="Satisfactorio" 
                                                               <?= (!$esPorMejorar ? 'checked' : '') ?> >
                                                        <label class="form-check-label text-success fw-semibold" for="rad_m3_sat_<?= $idVal ?>">
                                                            <i class="fa-solid fa-circle-check me-1"></i> Satisfactorio
                                                        </label>
                                                    </div>
                                                    <div class="form-check form-check-inline m-0">
                                                        <input class="form-check-input" type="radio" 
                                                               name="variables[<?= $idVal ?>][valoracion]" 
                                                               id="rad_m3_mej_<?= $idVal ?>" 
                                                               value="Por mejorar" 
                                                               <?= ($esPorMejorar ? 'checked' : '') ?> >
                                                        <label class="form-check-label text-warning-emphasis fw-semibold" for="rad_m3_mej_<?= $idVal ?>">
                                                            <i class="fa-solid fa-triangle-exclamation me-1"></i> Por mejorar
                                                        </label>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div id="box_m3_obs_<?= $idVal ?>">
                                                    <textarea name="variables[<?= $idVal ?>][observacion]" 
                                                              id="txt_m3_obs_<?= $idVal ?>" 
                                                              class="form-control form-control-sm border-warning" 
                                                              rows="2" 
                                                              placeholder="Describa la observación o recomendación final..."><?= htmlspecialchars($obsActual) ?></textarea>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- SECCIÓN 4: FACTORES COMPORTAMENTALES (DOMINIO 32) -->
                <div class="col-12 mt-4">
                    <div class="p-2 bg-primary bg-opacity-10 border-start border-primary border-4 rounded mb-3">
                        <h6 class="text-primary fw-bold mb-0">
                            <i class="fa-solid fa-user-check me-2"></i> 4. Factores Actitudinales y Comportamentales (Dominio 32 - GFPI-F-023)
                        </h6>
                    </div>
                </div>

                <div class="col-12">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="col-1 text-center">Código</th>
                                    <th class="col-4">Factor Comportamental a Evaluar</th>
                                    <th class="col-3 text-center">Valoración (Seleccionar 1)</th>
                                    <th class="col-4">Observaciones / Recomendaciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($varsComportamentales)): ?>
                                    <?php foreach ($varsComportamentales as $vc): ?>
                                        <?php
                                            $idVal = $vc['idval'];
                                            $valActual = $detallesM2[$idVal]['valoracion'] ?? 'Satisfactorio';
                                            $obsActual = $detallesM2[$idVal]['observacion'] ?? '';
                                            $esPorMejorar = ($valActual === 'Por mejorar');
                                        ?>
                                        <tr>
                                            <td class="text-center fw-bold text-secondary"><?= $idVal ?></td>
                                            <td>
                                                <strong class="text-dark"><?= htmlspecialchars($vc['nomval']) ?></strong>
                                            </td>
                                            <td class="text-center">
                                                <div class="d-inline-flex gap-3 align-items-center">
                                                    <div class="form-check form-check-inline m-0">
                                                        <input class="form-check-input" type="radio" 
                                                               name="variables[<?= $idVal ?>][valoracion]" 
                                                               id="rad_m3_sat_<?= $idVal ?>" 
                                                               value="Satisfactorio" 
                                                               <?= (!$esPorMejorar ? 'checked' : '') ?> >
                                                        <label class="form-check-label text-success fw-semibold" for="rad_m3_sat_<?= $idVal ?>">
                                                            <i class="fa-solid fa-circle-check me-1"></i> Satisfactorio
                                                        </label>
                                                    </div>
                                                    <div class="form-check form-check-inline m-0">
                                                        <input class="form-check-input" type="radio" 
                                                               name="variables[<?= $idVal ?>][valoracion]" 
                                                               id="rad_m3_mej_<?= $idVal ?>" 
                                                               value="Por mejorar" 
                                                               <?= ($esPorMejorar ? 'checked' : '') ?> >
                                                        <label class="form-check-label text-warning-emphasis fw-semibold" for="rad_m3_mej_<?= $idVal ?>">
                                                            <i class="fa-solid fa-triangle-exclamation me-1"></i> Por mejorar
                                                        </label>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div id="box_m3_obs_<?= $idVal ?>">
                                                    <textarea name="variables[<?= $idVal ?>][observacion]" 
                                                              id="txt_m3_obs_<?= $idVal ?>" 
                                                              class="form-control form-control-sm border-warning" 
                                                              rows="2" 
                                                              placeholder="Describa la observación o recomendación final..."><?= htmlspecialchars($obsActual) ?></textarea>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- SECCIÓN 5: RETROALIMENTACIÓN DE LOS 3 ACTORES (SOLICITADO SEGÚN CAPTURA OFICIAL) -->
                <div class="col-12 mt-4">
                    <div class="p-2 bg-dark bg-opacity-10 border-start border-secondary border-4 rounded mb-3">
                        <h6 class="text-secondary fw-bold mb-0">
                            <i class="fa-solid fa-comments me-2 text-success"></i> 5. Retroalimentación Ente Co-formador, Instructor de Seguimiento y Aprendiz (GFPI-F-023)
                        </h6>
                    </div>
                    <p class="text-muted small">
                        Consigne el concepto evaluativo final respecto al proceso de formación y al desempeño de las competencias técnicas y actitudinales desarrollado por cada una de las partes.
                    </p>
                </div>

                <!-- 5.1 Ente Co-formador (Empresa) -->
                <div class="col-12 mb-3">
                    <div class="card border border-success border-opacity-50 shadow-sm">
                        <div class="card-header bg-success bg-opacity-10 py-2">
                            <strong class="text-success"><i class="fa-solid fa-building me-1"></i> Retroalimentación Ente Co-formador (Empresa / Tutor)</strong>
                        </div>
                        <div class="card-body p-3">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small text-secondary">
                                        Proceso de formación del aprendiz:
                                    </label>
                                    <textarea name="retro_coformador_proceso" class="form-control" rows="3" placeholder="Concepto del coformador sobre el proceso integral del aprendiz..."><?= htmlspecialchars($retroM3['coformador_proceso'] ?? '') ?></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small text-secondary">
                                        Desempeño de las competencias técnicas y actitudinales puestas en práctica:
                                    </label>
                                    <textarea name="retro_coformador_competencias" class="form-control" rows="3" placeholder="Concepto sobre el desempeño técnico y actitudinal demostrado..."><?= htmlspecialchars($retroM3['coformador_competencias'] ?? '') ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 5.2 Instructor de Seguimiento -->
                <div class="col-12 mb-3">
                    <div class="card border border-primary border-opacity-50 shadow-sm">
                        <div class="card-header bg-primary bg-opacity-10 py-2">
                            <strong class="text-primary"><i class="fa-solid fa-chalkboard-user me-1"></i> Retroalimentación Instructor de Seguimiento SENA</strong>
                        </div>
                        <div class="card-body p-3">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small text-secondary">
                                        Proceso de formación del aprendiz:
                                    </label>
                                    <textarea name="retro_instructor_proceso" class="form-control" rows="3" placeholder="Concepto del instructor sobre el cumplimiento de la etapa práctica..."><?= htmlspecialchars($retroM3['instructor_proceso'] ?? '') ?></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small text-secondary">
                                        Desempeño de las competencias técnicas y actitudinales puestas en práctica:
                                    </label>
                                    <textarea name="retro_instructor_competencias" class="form-control" rows="3" placeholder="Evaluación del logro de resultados de aprendizaje (RAPs)..."><?= htmlspecialchars($retroM3['instructor_competencias'] ?? '') ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 5.3 Aprendiz en Formación -->
                <div class="col-12 mb-3">
                    <div class="card border border-info border-opacity-50 shadow-sm">
                        <div class="card-header bg-info bg-opacity-10 py-2">
                            <strong class="text-info"><i class="fa-solid fa-user-graduate me-1"></i> Retroalimentación y Autoevaluación del Aprendiz</strong>
                        </div>
                        <div class="card-body p-3">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small text-secondary">
                                        Proceso de formación del aprendiz:
                                    </label>
                                    <textarea name="retro_aprendiz_proceso" class="form-control" rows="3" placeholder="Autopercepción del aprendiz sobre su crecimiento formativo..."><?= htmlspecialchars($retroM3['aprendiz_proceso'] ?? '') ?></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small text-secondary">
                                        Desempeño de las competencias técnicas y actitudinales puestas en práctica:
                                    </label>
                                    <textarea name="retro_aprendiz_competencias" class="form-control" rows="3" placeholder="Apreciación sobre las habilidades técnicas aplicadas en la empresa..."><?= htmlspecialchars($retroM3['aprendiz_competencias'] ?? '') ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 5.4 Reconocimientos Especiales (Opcional) -->
                <div class="col-12 mb-3">
                    <label class="form-label fw-bold small text-secondary">
                        <i class="fa-solid fa-award me-1 text-warning"></i> Reconocimientos Especiales u Observaciones Generales (Si aplica):
                    </label>
                    <textarea name="reconocimientos_especiales" class="form-control" rows="2" placeholder="Felicitaciones especiales, menciones de honor, continuidad laboral en la empresa..."><?= htmlspecialchars($retroM3['reconocimientos'] ?? '') ?></textarea>
                </div>

                <!-- SECCIÓN 6: JUICIO DE EVALUACIÓN OFICIAL (APROBADO / NO APROBADO) -->
                <div class="col-12 mt-4">
                    <div class="card border-0 shadow p-4 text-center bg-white rounded-3 border-top border-4 <?= ($juicioActual === 'NO APROBADO' ? 'border-danger' : 'border-success') ?>">
                        <h5 class="fw-bold text-secondary mb-2">
                            <i class="fa-solid fa-scale-balanced me-2 text-success"></i>
                            JUICIO DE EVALUACIÓN DE LA ETAPA PRODUCTIVA (GFPI-F-023)
                        </h5>
                        <p class="text-muted small mb-4">
                            Seleccione el concepto final emitido para el aprendiz en el cierre oficial de su Etapa Productiva:
                        </p>

                        <div class="d-flex justify-content-center gap-4 flex-wrap">
                            <div class="p-3 border rounded-3 bg-light" style="min-width: 250px;">
                                <div class="form-check d-flex align-items-center justify-content-center gap-2 m-0">
                                    <input class="form-check-input fs-5" type="radio" name="juicio_evaluativo" id="juicio_aprobado" value="APROBADO" <?= ($juicioActual !== 'NO APROBADO' ? 'checked' : '') ?>>
                                    <label class="form-check-label fw-bold text-success fs-5 cursor-pointer" for="juicio_aprobado">
                                        <i class="fa-solid fa-circle-check me-1"></i> APROBADO
                                    </label>
                                </div>
                                <small class="text-muted d-block mt-1">Cumple con todas las competencias y evidencias requeridas</small>
                            </div>

                            <div class="p-3 border rounded-3 bg-light" style="min-width: 250px;">
                                <div class="form-check d-flex align-items-center justify-content-center gap-2 m-0">
                                    <input class="form-check-input fs-5" type="radio" name="juicio_evaluativo" id="juicio_no_aprobado" value="NO APROBADO" <?= ($juicioActual === 'NO APROBADO' ? 'checked' : '') ?>>
                                    <label class="form-check-label fw-bold text-danger fs-5 cursor-pointer" for="juicio_no_aprobado">
                                        <i class="fa-solid fa-circle-xmark me-1"></i> NO APROBADO
                                    </label>
                                </div>
                                <small class="text-muted d-block mt-1">No cumple satisfactoriamente los resultados de aprendizaje</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECCIÓN 7: FIRMAS TRIPARTITAS Y CIERRE -->
                <div class="col-12 mt-4">
                    <h6 class="text-secondary fw-bold border-bottom pb-2">
                        <i class="fa-solid fa-signature me-1 text-success"></i> 7. Cierre y Firmas Tripartitas del Juicio Evaluativo
                    </h6>
                </div>
                <div class="col-md-4">
                    <div class="card p-3 border-0 bg-light text-center h-100">
                        <div class="text-muted small fw-bold mb-1">INSTRUCTOR DE SEGUIMIENTO</div>
                        <strong class="text-dark"><?= htmlspecialchars($_SESSION['nomusu'] ?? $conEdit['nom_instructor'] ?? 'Instructor SENA') ?></strong>
                        <div class="mt-2">
                            <span class="badge bg-success px-3 py-2">
                                <i class="fa-solid fa-check me-1"></i> Firmado Digitalmente
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card p-3 border-0 bg-light text-center h-100">
                        <div class="text-muted small fw-bold mb-1">APRENDIZ EN FORMACIÓN</div>
                        <strong class="text-dark"><?= htmlspecialchars($conEdit['nom_aprendiz'] ?? '') ?></strong>
                        <div class="mt-2">
                            <span class="badge <?= (($conEdit['firma_aprendiz'] ?? '') === 'Firmado' ? 'bg-success' : 'bg-warning text-dark') ?> px-3 py-2">
                                <?= (($conEdit['firma_aprendiz'] ?? '') === 'Firmado' ? '<i class="fa-solid fa-check me-1"></i> Firmado' : '<i class="fa-solid fa-clock me-1"></i> Pendiente') ?>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card p-3 border-0 bg-light text-center h-100">
                        <div class="text-muted small fw-bold mb-1">ENTE CO-FORMADOR (EMPRESA)</div>
                        <strong class="text-dark"><?= htmlspecialchars($conEdit['nombre_jefe'] ?? 'Tutor Coformador') ?></strong>
                        <div class="mt-2">
                            <span class="badge <?= (($conEdit['firma_empresa'] ?? '') === 'Firmado' ? 'bg-success' : 'bg-warning text-dark') ?> px-3 py-2">
                                <?= (($conEdit['firma_empresa'] ?? '') === 'Firmado' ? '<i class="fa-solid fa-check me-1"></i> Firmado' : '<i class="fa-solid fa-clock me-1"></i> Pendiente') ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- BOTONES DE ACCIÓN -->
                <div class="col-12 mt-4 text-md-end">
                    <a href="home.php?pg=1538&idficha=<?= htmlspecialchars($idficha ?? '') ?>&tipo_formato=303" class="btn btn-secondary shadow me-2">
                        <i class="fa-solid fa-circle-xmark me-1"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-success shadow px-4">
                        <i class="fa-solid fa-floppy-disk me-2"></i> Guardar Evaluación Final y Juicio
                    </button>
                </div>
            </form>
        </div>
    <?php endif; ?>

    <!-- 4. TABLA DE APRENDICES DE LA FICHA (VISTA MOMENTO 3) -->
    <?php if (!empty($idficha)): ?>
        <div class="mt-4">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <h5 class="mb-0 text-secondary d-flex align-items-center">
                    <i class="fa-solid fa-graduation-cap me-2 text-success"></i>
                    Aprendices Asignados para Evaluación Final M3 - Ficha <?= htmlspecialchars($idficha) ?>
                </h5>
                <span class="text-muted small">
                    Mostrando formato: <strong>Momento 3 (Evaluación Final y Juicio)</strong>
                </span>
            </div>

            <div class="table-responsive">
                <table id="example" class="table table-striped table-hover align-middle w-100">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center col-1">No</th>
                            <th class="col-3">Datos del Aprendiz</th>
                            <th class="col-3">Empresa Coformadora</th>
                            <th class="col-2 text-center">Juicio Evaluativo M3</th>
                            <th class="text-center col-2">Firmas Tripartitas</th>
                            <th class="text-end col-1">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($aprendicesFicha)): ?>
                            <?php $idx = 1; foreach ($aprendicesFicha as $ap): ?>
                                <?php 
                                    $idConciActual = $ap['idcon'] ?? null;
                                    $idAprendizActual = $ap['idaprendiz'] ?? $ap['idusu'] ?? null;
                                    $nombreAprendiz = $ap['nom_aprendiz'] ?? (trim(($ap['nomusu'] ?? '') . ' ' . ($ap['apeusu'] ?? '')));
                                    $documentoAprendiz = $ap['doc_aprendiz'] ?? $ap['ndocusu'] ?? '';
                                    $correoAprendiz = $ap['correo_aprendiz'] ?? $ap['corusu'] ?? 'Sin correo';
                                    $telefonoAprendiz = $ap['tel_aprendiz'] ?? $ap['telusu'] ?? 'Sin teléfono';

                                    $tieneM1 = !empty($idConciActual);
                                    $tieneM3 = !empty($ap['fecha_momento3_real']) || !empty($ap['juicio_evaluativo']);
                                    $juicioFicha = $ap['juicio_evaluativo'] ?? (($ap['estado'] ?? '') === 'Aprobado' && $tieneM3 ? 'APROBADO' : null);

                                    $fAp = $ap['firma_aprendiz'] ?? 'Pendiente';
                                    $fEm = $ap['firma_empresa'] ?? 'Pendiente';
                                ?>
                                <tr>
                                    <td class="text-center fw-bold text-secondary"><?= $idx++ ?></td>

                                    <!-- Datos Aprendiz -->
                                    <td>
                                        <strong class="text-dark"><?= htmlspecialchars($nombreAprendiz) ?></strong><br>
                                        <small class="text-muted">
                                            <i class="fa-solid fa-id-card me-1"></i> <?= htmlspecialchars($documentoAprendiz) ?><br>
                                            <i class="fa-solid fa-envelope me-1"></i> <?= htmlspecialchars($correoAprendiz) ?><br>
                                            <i class="fa-solid fa-phone me-1"></i> <?= htmlspecialchars($telefonoAprendiz) ?>
                                        </small>
                                    </td>

                                    <!-- Empresa Coformadora -->
                                    <td>
                                        <?php if ($tieneM1 && !empty($ap['nombre_empresa'])): ?>
                                            <strong><?= htmlspecialchars($ap['nombre_empresa']) ?></strong><br>
                                            <small class="text-muted">
                                                Tutor: <?= htmlspecialchars($ap['nombre_jefe'] ?? 'N/A') ?><br>
                                                Visitas: <span class="badge bg-light text-dark border"><?= htmlspecialchars($ap['num_visitas'] ?? 3) ?> realizadas</span>
                                            </small>
                                        <?php else: ?>
                                            <span class="badge bg-secondary bg-opacity-25 text-secondary">
                                                <i class="fa-solid fa-triangle-exclamation me-1"></i> Sin concertación previa (M1)
                                            </span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Juicio Evaluativo Momento 3 -->
                                    <td class="text-center">
                                        <?php if (!$tieneM1): ?>
                                            <span class="badge bg-danger px-2 py-1">
                                                <i class="fa-solid fa-ban me-1"></i> Requiere Momento 1
                                            </span>
                                        <?php elseif ($tieneM3): ?>
                                            <?php if ($juicioFicha === 'APROBADO'): ?>
                                                <span class="badge bg-success fs-6 px-3 py-1">
                                                    <i class="fa-solid fa-circle-check me-1"></i> APROBADO
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-danger fs-6 px-3 py-1">
                                                    <i class="fa-solid fa-circle-xmark me-1"></i> NO APROBADO
                                                </span>
                                            <?php endif; ?>
                                            <br>
                                            <small class="text-muted" style="font-size:0.75rem;">
                                                Evaluado: <?= date('d/m/Y', strtotime($ap['fecha_momento3_real'] ?? date('Y-m-d'))) ?>
                                            </small>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark px-2 py-1">
                                                <i class="fa-solid fa-clock me-1"></i> Pendiente Juicio Final
                                            </span><br>
                                            <small class="text-muted" style="font-size:0.75rem;">
                                                Fin EP: <?= date('d/m/Y', strtotime($ap['fecha_fin'] ?? date('Y-m-d', strtotime('+6 months')))) ?>
                                            </small>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Firmas Tripartitas -->
                                    <td class="text-center">
                                        <?php if ($tieneM1): ?>
                                            <div class="d-flex flex-column gap-1 align-items-center">
                                                <span class="badge bg-success w-100 py-1" title="Instructor SENA">
                                                    <i class="fa-solid fa-check me-1"></i> Instructor: Firmado
                                                </span>

                                                <a href="home.php?pg=1538&opera=alternar_firma&idcon=<?= $idConciActual ?>&rol=aprendiz&idficha=<?= htmlspecialchars($idficha) ?>&tipo_formato=303" 
                                                   class="badge <?= ($fAp === 'Firmado' ? 'bg-success' : 'bg-warning text-dark') ?> text-decoration-none w-100 py-1" 
                                                   title="Clic para alternar firma del aprendiz">
                                                    <?= ($fAp === 'Firmado' ? '<i class="fa-solid fa-check me-1"></i>' : '<i class="fa-solid fa-clock me-1"></i>') ?> Aprendiz: <?= $fAp ?>
                                                </a>

                                                <a href="home.php?pg=1538&opera=alternar_firma&idcon=<?= $idConciActual ?>&rol=empresa&idficha=<?= htmlspecialchars($idficha) ?>&tipo_formato=303" 
                                                   class="badge <?= ($fEm === 'Firmado' ? 'bg-success' : 'bg-warning text-dark') ?> text-decoration-none w-100 py-1" 
                                                   title="Clic para alternar firma de la empresa">
                                                    <?= ($fEm === 'Firmado' ? '<i class="fa-solid fa-check me-1"></i>' : '<i class="fa-solid fa-clock me-1"></i>') ?> Empresa: <?= $fEm ?>
                                                </a>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted small fst-italic">Requiere concertar M1</span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Acciones -->
                                    <td class="text-end align-middle">
                                        <div class="d-flex align-items-center justify-content-end gap-2 text-nowrap">
                                            <?php if (!$tieneM1): ?>
                                                <!-- Redirigir a Diligenciar M1 primero -->
                                                <a href="home.php?pg=1536&opera=diligenciar&idaprendiz=<?= $idAprendizActual ?>&idficha=<?= htmlspecialchars($idficha) ?>&tipo_formato=301" 
                                                   title="Ir a Concertar Momento 1 Primero" 
                                                   class="btn btn-link p-0 border-0 text-decoration-none">
                                                    <i class="fa-solid fa-file-circle-exclamation fa-2x text-warning"></i>
                                                </a>
                                            <?php elseif ($tieneM3): ?>
                                                <!-- Editar Evaluación Final -->
                                                <a href="home.php?pg=1538&opera=edi&idcon=<?= $idConciActual ?>&idficha=<?= htmlspecialchars($idficha) ?>&tipo_formato=303#formEvaluacionFinalM3" 
                                                   title="Modificar Evaluación Final Momento 3" 
                                                   class="btn btn-link p-0 border-0 text-decoration-none">
                                                    <i class="fa-solid fa-file-pen fa-2x text-success"></i>
                                                </a>
                                            <?php else: ?>
                                                <!-- Diligenciar Evaluación Final -->
                                                <a href="home.php?pg=1538&opera=diligenciar&idcon=<?= $idConciActual ?>&idficha=<?= htmlspecialchars($idficha) ?>&tipo_formato=303#formEvaluacionFinalM3" 
                                                   title="Emitir Juicio Evaluativo Momento 3" 
                                                   class="btn btn-link p-0 border-0 text-decoration-none">
                                                    <i class="fa-solid fa-clipboard-check fa-2x text-success"></i>
                                                </a>
                                            <?php endif; ?>

                                            <!-- Modal de Vista Previa M3 -->
                                            <?php if ($tieneM1): ?>
                                                <button type="button" 
                                                        class="btn btn-link p-0 border-0 text-decoration-none" 
                                                        title="Ver Formato Momento 3" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#modalVerM3_<?= $idAprendizActual ?>">
                                                    <i class="fa-solid fa-eye fa-2x text-success"></i>
                                                </button>

                                                <!-- Descarga PDF -->
                                                <a href="views/etavcsi_pdf.php?idcon=<?= $idConciActual ?>&tipo_formato=303" 
                                                   target="_blank" 
                                                   title="Descargar Formato Momento 3 PDF" 
                                                   class="btn btn-link p-0 border-0 text-decoration-none">
                                                    <i class="fa-solid fa-file-pdf fa-2x text-success"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th class="text-center">No</th>
                            <th>Datos del Aprendiz</th>
                            <th>Empresa Coformadora</th>
                            <th class="text-center">Juicio Evaluativo M3</th>
                            <th class="text-center">Firmas Tripartitas</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- 5. MODALES DE VISTA PREVIA GFPI-F-023 (MOMENTO 3) -->
        <?php if (!empty($aprendicesFicha)): ?>
            <?php foreach ($aprendicesFicha as $ap): ?>
                <?php if (!empty($ap['idcon'])): ?>
                    <?php
                        $idApModal = $ap['idaprendiz'] ?? $ap['idusu'] ?? null;
                        $idConModal = $ap['idcon'];
                        $nombreApModal = $ap['nom_aprendiz'] ?? (trim(($ap['nomusu'] ?? '') . ' ' . ($ap['apeusu'] ?? '')));
                        $docApModal = $ap['doc_aprendiz'] ?? $ap['ndocusu'] ?? '';
                        $fInicioMod = $ap['fecha_inicio'] ?? date('Y-m-d');
                        $fFinMod = $ap['fecha_fin'] ?? date('Y-m-d', strtotime('+6 months'));
                        $fM3RealMod = !empty($ap['fecha_momento3_real']) ? date('d/m/Y', strtotime($ap['fecha_momento3_real'])) : 'Pendiente';
                        $juicioModal = $ap['juicio_evaluativo'] ?? (($ap['estado'] ?? '') === 'Aprobado' && !empty($ap['fecha_momento3_real']) ? 'APROBADO' : 'PENDIENTE');

                        // Cargar variables evaluadas para el modal
                        $detallesModal = $mcsi->getDetallesConsertamiento($idConModal);

                        // Decodificar retroalimentación
                        $retroModal = [];
                        if (!empty($ap['obs_momento3'])) {
                            $dec = json_decode($ap['obs_momento3'], true);
                            if (is_array($dec)) $retroModal = $dec;
                        }
                    ?>
                    <div class="modal fade" id="modalVerM3_<?= $idApModal ?>" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                            <div class="modal-content text-start">
                                
                                <div class="modal-header bg-success text-white py-2">
                                    <h5 class="modal-title fs-6 fw-bold">
                                        <i class="fa-solid fa-graduation-cap me-2"></i>
                                        Formato Oficial SENA GFPI-F-023 | Momento 3: Evaluación Final - <?= htmlspecialchars($nombreApModal) ?>
                                    </h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                </div>

                                <div class="modal-body p-4 bg-white">
                                    <!-- ENCABEZADO OFICIAL SENA -->
                                    <div class="border border-dark p-2 mb-3 bg-light">
                                        <div class="row align-items-center text-center">
                                            <div class="col-md-2 border-end border-dark">
                                                <img src="img/logoSena.png" alt="SENA" class="img-fluid" style="max-height:55px;">
                                            </div>
                                            <div class="col-md-7 border-end border-dark">
                                                <span class="fw-bold d-block small">PROCESO GESTIÓN DE FORMACIÓN PROFESIONAL INTEGRAL</span>
                                                <span class="fw-bold d-block small text-success">FORMATO DE PLANEACIÓN, SEGUIMIENTO Y EVALUACIÓN DE ETAPA PRODUCTIVA</span>
                                                <span class="d-block text-muted small">MOMENTO 3: EVALUACIÓN FINAL Y JUICIO EVALUATIVO</span>
                                            </div>
                                            <div class="col-md-3 text-start small">
                                                <strong>Código:</strong> GFPI-F-023<br>
                                                <strong>Versión:</strong> 03<br>
                                                <strong>Fecha:</strong> <?= date('d/m/Y') ?>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- 1. INFORMACIÓN GENERAL -->
                                    <div class="table-responsive mb-3">
                                        <table class="table table-bordered table-sm mb-0">
                                            <thead class="table-success">
                                                <tr>
                                                    <th colspan="4" class="text-uppercase fw-bold">1. Información General</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td class="bg-light fw-bold col-2">Regional:</td>
                                                    <td class="col-4">CUNDINAMARCA</td>
                                                    <td class="bg-light fw-bold col-2">Centro de Formación:</td>
                                                    <td class="col-4">CENTRO DE DESARROLLO AGROEMPRESARIAL</td>
                                                </tr>
                                                <tr>
                                                    <td class="bg-light fw-bold">Programa:</td>
                                                    <td><?= htmlspecialchars($ap['nom_programa'] ?? 'ANÁLISIS Y DESARROLLO DE SOFTWARE') ?></td>
                                                    <td class="bg-light fw-bold">No. Ficha:</td>
                                                    <td><strong><?= htmlspecialchars($idficha) ?></strong></td>
                                                </tr>
                                                <tr>
                                                    <td class="bg-light fw-bold">Aprendiz:</td>
                                                    <td><strong><?= htmlspecialchars($nombreApModal) ?></strong> (<?= htmlspecialchars($docApModal) ?>)</td>
                                                    <td class="bg-light fw-bold">Empresa:</td>
                                                    <td><?= htmlspecialchars($ap['nombre_empresa'] ?? 'N/A') ?></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- 2. PARÁMETROS DE LA EVALUACIÓN FINAL -->
                                    <div class="table-responsive mb-3">
                                        <table class="table table-bordered table-sm mb-0">
                                            <thead class="table-success">
                                                <tr>
                                                    <th colspan="4" class="text-uppercase fw-bold">2. Parámetros de la Evaluación Final</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td class="bg-light fw-bold col-3">Número de Visitas Realizadas:</td>
                                                    <td class="col-3"><strong><?= htmlspecialchars($ap['num_visitas'] ?? 3) ?> visitas</strong></td>
                                                    <td class="bg-light fw-bold col-3">Fecha Inicio Etapa Productiva:</td>
                                                    <td class="col-3"><?= date('d/m/Y', strtotime($fInicioMod)) ?></td>
                                                </tr>
                                                <tr>
                                                    <td class="bg-light fw-bold">Fecha Fin Etapa Productiva:</td>
                                                    <td><?= date('d/m/Y', strtotime($fFinMod)) ?></td>
                                                    <td class="bg-light fw-bold">Fecha Real Evaluación Final:</td>
                                                    <td class="text-success fw-bold"><?= $fM3RealMod ?></td>
                                                </tr>
                                                <tr>
                                                    <td class="bg-light fw-bold">Modalidad de la Sesión:</td>
                                                    <td colspan="3"><span class="badge bg-primary"><?= htmlspecialchars($ap['modalidad'] ?? 'Presencial') ?></span></td>
                                                </tr>
                                                <?php if (!empty($ap['enlace_grabacion'])): ?>
                                                    <tr>
                                                        <td class="bg-light fw-bold">Enlace de Grabación:</td>
                                                        <td colspan="3">
                                                            <a href="<?= htmlspecialchars($ap['enlace_grabacion']) ?>" target="_blank" class="text-primary text-break">
                                                                <i class="fa-solid fa-video me-1"></i> <?= htmlspecialchars($ap['enlace_grabacion']) ?>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- 3. FACTORES EVALUADOS (TÉCNICOS Y COMPORTAMENTALES) -->
                                    <div class="table-responsive mb-3">
                                        <table class="table table-bordered table-sm mb-0">
                                            <thead class="table-success">
                                                <tr>
                                                    <th colspan="3" class="text-uppercase fw-bold">3. Factores Técnicos y Comportamentales Evaluados</th>
                                                </tr>
                                                <tr class="table-light">
                                                    <th class="col-5">Factor / Variable</th>
                                                    <th class="col-2 text-center">Valoración</th>
                                                    <th class="col-5">Observaciones / Recomendaciones</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (!empty($detallesModal)): ?>
                                                    <?php foreach ($detallesModal as $dm): ?>
                                                        <tr>
                                                            <td>
                                                                <strong><?= htmlspecialchars($dm['nomval']) ?></strong>
                                                                <small class="text-muted d-block"><?= ((int)$dm['iddom'] === 31 ? 'Factor Técnico' : 'Factor Comportamental') ?></small>
                                                            </td>
                                                            <td class="text-center">
                                                                <?php if ($dm['valoracion'] === 'Satisfactorio'): ?>
                                                                    <span class="badge bg-success"><i class="fa-solid fa-check me-1"></i> Satisfactorio</span>
                                                                <?php else: ?>
                                                                    <span class="badge bg-warning text-dark"><i class="fa-solid fa-triangle-exclamation me-1"></i> Por mejorar</span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td>
                                                                <?= !empty($dm['observacion']) ? htmlspecialchars($dm['observacion']) : '<span class="text-muted fst-italic">Sin observaciones</span>' ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="3" class="text-center text-muted py-3">
                                                            <em>Aún no se han evaluado las variables para este aprendiz en la Evaluación Final.</em>
                                                        </td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- 4. RETROALIMENTACIÓN DE LOS 3 ACTORES (SEGÚN CAPTURA OFICIAL) -->
                                    <div class="table-responsive mb-3">
                                        <table class="table table-bordered table-sm mb-0">
                                            <thead class="table-success">
                                                <tr>
                                                    <th colspan="2" class="text-uppercase fw-bold">4. Retroalimentación Ente Co-formador o Reconocimientos Especiales</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- Ente Co-formador -->
                                                <tr class="table-light">
                                                    <td colspan="2" class="fw-bold text-success">
                                                        <i class="fa-solid fa-building me-1"></i> Concepto del Ente Co-formador (Empresa)
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="bg-light fw-bold col-4">Proceso de formación del aprendiz:</td>
                                                    <td class="col-8"><?= !empty($retroModal['coformador_proceso']) ? nl2br(htmlspecialchars($retroModal['coformador_proceso'])) : '<span class="text-muted fst-italic">Sin concepto registrado</span>' ?></td>
                                                </tr>
                                                <tr>
                                                    <td class="bg-light fw-bold">Desempeño de competencias técnicas y actitudinales en la práctica:</td>
                                                    <td><?= !empty($retroModal['coformador_competencias']) ? nl2br(htmlspecialchars($retroModal['coformador_competencias'])) : '<span class="text-muted fst-italic">Sin concepto registrado</span>' ?></td>
                                                </tr>

                                                <!-- Instructor de Seguimiento -->
                                                <tr class="table-light">
                                                    <td colspan="2" class="fw-bold text-primary">
                                                        <i class="fa-solid fa-chalkboard-user me-1"></i> Concepto del Instructor de Seguimiento SENA
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="bg-light fw-bold">Proceso de formación del aprendiz:</td>
                                                    <td><?= !empty($retroModal['instructor_proceso']) ? nl2br(htmlspecialchars($retroModal['instructor_proceso'])) : '<span class="text-muted fst-italic">Sin concepto registrado</span>' ?></td>
                                                </tr>
                                                <tr>
                                                    <td class="bg-light fw-bold">Desempeño de competencias técnicas y actitudinales en la práctica:</td>
                                                    <td><?= !empty($retroModal['instructor_competencias']) ? nl2br(htmlspecialchars($retroModal['instructor_competencias'])) : '<span class="text-muted fst-italic">Sin concepto registrado</span>' ?></td>
                                                </tr>

                                                <!-- Aprendiz -->
                                                <tr class="table-light">
                                                    <td colspan="2" class="fw-bold text-info">
                                                        <i class="fa-solid fa-user-graduate me-1"></i> Concepto / Autoevaluación del Aprendiz
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="bg-light fw-bold">Proceso de formación del aprendiz:</td>
                                                    <td><?= !empty($retroModal['aprendiz_proceso']) ? nl2br(htmlspecialchars($retroModal['aprendiz_proceso'])) : '<span class="text-muted fst-italic">Sin concepto registrado</span>' ?></td>
                                                </tr>
                                                <tr>
                                                    <td class="bg-light fw-bold">Desempeño de competencias técnicas y actitudinales en la práctica:</td>
                                                    <td><?= !empty($retroModal['aprendiz_competencias']) ? nl2br(htmlspecialchars($retroModal['aprendiz_competencias'])) : '<span class="text-muted fst-italic">Sin concepto registrado</span>' ?></td>
                                                </tr>

                                                <!-- Reconocimientos Especiales -->
                                                <?php if (!empty($retroModal['reconocimientos'])): ?>
                                                    <tr>
                                                        <td class="bg-light fw-bold">Reconocimientos especiales:</td>
                                                        <td><?= nl2br(htmlspecialchars($retroModal['reconocimientos'])) ?></td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- 5. JUICIO DE EVALUACIÓN OFICIAL -->
                                    <div class="table-responsive mb-3">
                                        <table class="table table-bordered table-sm mb-0 text-center">
                                            <thead class="table-success">
                                                <tr>
                                                    <th class="text-uppercase fw-bold">5. Juicio de Evaluación de la Etapa Productiva</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td class="p-3">
                                                        <span class="fs-5 fw-bold">JUICIO EMITIDO: </span>
                                                        <?php if ($juicioModal === 'APROBADO'): ?>
                                                            <span class="badge bg-success fs-5 px-4 py-2">
                                                                <i class="fa-solid fa-circle-check me-2"></i> APROBADO
                                                            </span>
                                                        <?php elseif ($juicioModal === 'NO APROBADO'): ?>
                                                            <span class="badge bg-danger fs-5 px-4 py-2">
                                                                <i class="fa-solid fa-circle-xmark me-2"></i> NO APROBADO
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="badge bg-secondary fs-5 px-4 py-2">
                                                                <i class="fa-solid fa-clock me-2"></i> PENDIENTE DE JUICIO
                                                            </span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- 6. FIRMAS TRIPARTITAS -->
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-sm mb-0 text-center">
                                            <thead class="table-success">
                                                <tr>
                                                    <th colspan="3" class="text-uppercase fw-bold">6. Firmas Tripartitas y Validación Oficial</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td class="align-bottom p-3 col-4">
                                                        <div class="border-top border-dark pt-1">
                                                            <strong><?= htmlspecialchars($nombreApModal) ?></strong><br>
                                                            <small class="text-muted">Firma del Aprendiz</small><br>
                                                            <span class="badge <?= ($ap['firma_aprendiz'] === 'Firmado' ? 'bg-success' : 'bg-warning text-dark') ?>">
                                                                <?= ($ap['firma_aprendiz'] === 'Firmado' ? '✔ Firmado Digitalmente' : '⏳ Esperando Firma') ?>
                                                            </span>
                                                        </div>
                                                    </td>
                                                    <td class="align-bottom p-3 col-4">
                                                        <div class="border-top border-dark pt-1">
                                                            <strong>JUAN CAMILO MEDINA FORERO</strong><br>
                                                            <small class="text-muted">Firma del Instructor de Seguimiento</small><br>
                                                            <span class="badge bg-success">
                                                                ✔ Firmado Digitalmente
                                                            </span>
                                                        </div>
                                                    </td>
                                                    <td class="align-bottom p-3 col-4">
                                                        <div class="border-top border-dark pt-1">
                                                            <strong><?= htmlspecialchars($ap['nombre_jefe'] ?? 'Tutor Coformador') ?></strong><br>
                                                            <small class="text-muted">Firma Ente Coformador (Empresa)</small><br>
                                                            <span class="badge <?= ($ap['firma_empresa'] === 'Firmado' ? 'bg-success' : 'bg-warning text-dark') ?>">
                                                                <?= ($ap['firma_empresa'] === 'Firmado' ? '✔ Firmado Digitalmente' : '⏳ Esperando Firma') ?>
                                                            </span>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                </div>

                                <div class="modal-footer bg-light py-2">
                                    <a href="views/etavcsi_pdf.php?idcon=<?= $idConModal ?>&tipo_formato=303" target="_blank" class="btn btn-danger shadow-sm">
                                        <i class="fa-solid fa-download me-1"></i> Descargar PDF Oficial M3
                                    </a>
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                </div>

                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    <?php endif; ?>

</div>
