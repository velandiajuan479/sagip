<?php
    require_once("controllers/etaccsi.php");

    // Identificación de la página actual y momento
    $pgActual = 1537; // Momento 2 - Evaluación Parcial (Seguimiento)
    $tipo_formato = 302;

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
    $tituloPagina = "<i class='" . $icono . "'></i> Momento 2: Evaluación Parcial y Seguimiento de Etapa Productiva (GFPI-F-023)";
    echo titulo2($tituloPagina, 1);
?>

    <!-- 1. TARJETA DE FILTROS: SELECCIÓN DE MOMENTO EVALUATIVO Y FICHA ASIGNADA -->
    <div class="card p-4 mb-4 shadow-sm border-0">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <p class="text-muted small mb-0">
                Gestión oficial del seguimiento a los <strong>3 meses</strong> de iniciada la Etapa Productiva. Valore los factores técnicos y comportamentales, registre las observaciones de mejora y verifique el cumplimiento del plan concertado.
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

    <!-- 2. DASHBOARD DE SEGUIMIENTO MOMENTO 2 (MÉTRICAS DE LA FICHA) -->
    <?php if (!empty($idficha) && !empty($aprendicesFicha)): ?>
        <?php
            $totAp = count($aprendicesFicha);
            $conSeguimientoM2 = 0;
            $pendientesM2 = 0;
            $sinConcertacionM1 = 0;
            $conMejoraM2 = 0;

            foreach ($aprendicesFicha as $apItem) {
                if (empty($apItem['idcon'])) {
                    $sinConcertacionM1++;
                } else {
                    $tieneM2 = !empty($apItem['fecha_momento2_real']) || (!empty($apItem['total_evaluaciones']) && (int)$apItem['total_evaluaciones'] > 0);
                    if ($tieneM2) {
                        $conSeguimientoM2++;
                    } else {
                        $pendientesM2++;
                    }
                }
            }
        ?>
        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <h5 class="text-secondary fw-bold mb-0 d-flex align-items-center">
                    <i class="fa-solid fa-chart-pie me-2 text-success"></i>
                    Estado del Seguimiento Parcial (3 Meses) - Ficha <?= htmlspecialchars($idficha) ?>
                </h5>
                <span class="badge bg-light text-dark border px-3 py-2">
                    <i class="fa-solid fa-calendar-day me-1 text-success"></i> Seguimiento a los 90 días de inicio
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
                                <small class="text-muted fw-bold text-uppercase">Seguimiento Realizado</small>
                                <h3 class="mb-0 fw-bold text-success"><?= $conSeguimientoM2 ?></h3>
                            </div>
                            <div class="kpi-icon-box bg-success bg-opacity-10 text-success">
                                <i class="fa-solid fa-circle-check fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted fw-bold text-uppercase">Pendientes Seguimiento</small>
                                <h3 class="mb-0 fw-bold text-warning"><?= $pendientesM2 ?></h3>
                            </div>
                            <div class="kpi-icon-box bg-warning bg-opacity-10 text-warning">
                                <i class="fa-solid fa-hourglass-half fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted fw-bold text-uppercase">Sin Concertar (M1)</small>
                                <h3 class="mb-0 fw-bold text-danger"><?= $sinConcertacionM1 ?></h3>
                            </div>
                            <div class="kpi-icon-box bg-danger bg-opacity-10 text-danger">
                                <i class="fa-solid fa-triangle-exclamation fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php elseif (empty($idficha)): ?>
        <div class="card border-0 bg-light p-4 text-center rounded-3 shadow-sm mb-4">
            <div class="py-3">
                <i class="fa-solid fa-clock-rotate-left fa-3x text-muted mb-3"></i>
                <h5 class="fw-bold text-secondary">Seleccione una Ficha para Comenzar el Seguimiento</h5>
                <p class="text-muted mb-0 mx-auto col-md-8">
                    Para evaluar a los aprendices en su <strong>Momento 2 (Evaluación Parcial a los 3 meses)</strong>, elija la ficha asignada arriba y presione <strong>Consultar</strong>.
                </p>
            </div>
        </div>
    <?php endif; ?>

    <!-- 3. FORMULARIO DILIGENCIAR / EDITAR MOMENTO 2 (EVALUACIÓN PARCIAL DE SEGUIMIENTO) -->
    <?php if (!empty($conEdit)): ?>
        <?php
            $fechaInicioM1 = $conEdit['fecha_inicio'] ?? date('Y-m-d');
            $fechaMomento2Pactada = date('Y-m-d', strtotime($fechaInicioM1 . ' +3 months'));
            $fechaMomento2Real = $conEdit['fecha_momento2_real'] ?? date('Y-m-d');
            $modalidadActual = $conEdit['modalidad'] ?? 'Presencial';
            $enlaceGrabacion = $conEdit['enlace_grabacion'] ?? '';
            $obsM2 = $conEdit['obs_momento2'] ?? '';

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
        <div class="card p-4 mb-5 shadow-sm border-0 border-top border-success border-4" id="formSeguimientoM2">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <h4 class="mb-0 text-secondary d-flex align-items-center">
                    <i class="fa-solid fa-file-circle-check text-success me-2"></i>
                    Diligenciamiento Momento 2: Evaluación Parcial y Seguimiento (GFPI-F-023)
                </h4>
                <span class="badge bg-primary px-3 py-2">
                    Aprendiz: <?= htmlspecialchars($conEdit['nom_aprendiz'] ?? '') ?>
                </span>
            </div>
            <p class="text-muted small mb-4">
                Evalúe los factores técnicos y comportamentales para el aprendiz. El campo de observaciones se muestra siempre visible: consigne allí las observaciones y el plan de mejoramiento de cada variable marcada como <strong>"Por mejorar"</strong>.
            </p>

            <form id="frmSeguimientoM2" name="frmSeguimientoM2" method="POST" action="home.php?pg=1537" class="row g-3">
                <input type="hidden" name="opera" value="guardar_m2">
                <input type="hidden" name="idcon" value="<?= htmlspecialchars($conEdit['idcon'] ?? '') ?>">
                <input type="hidden" name="idaprendiz" value="<?= htmlspecialchars($conEdit['idaprendiz'] ?? '') ?>">
                <input type="hidden" name="idficha" value="<?= htmlspecialchars($idficha ?? ($conEdit['idficha'] ?? '')) ?>">
                <input type="hidden" name="tipo_formato" value="302">

                <!-- SECCIÓN 1: DATOS GENERALES (READONLY DEL MOMENTO 1) -->
                <div class="col-12">
                    <h6 class="text-success fw-bold border-bottom pb-2">
                        <i class="fa-solid fa-address-card me-1"></i> 1. Información General del Aprendiz y Ente Coformador (Momento 1)
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

                <!-- SECCIÓN 2: FECHAS Y MODALIDAD DEL SEGUIMIENTO -->
                <div class="col-12 mt-4">
                    <h6 class="text-success fw-bold border-bottom pb-2">
                        <i class="fa-solid fa-calendar-check me-1"></i> 2. Parámetros y Modalidad del Seguimiento Parcial
                    </h6>
                </div>

                <!-- Fecha Inicio EP (Traída de concertación, READONLY) -->
                <div class="col-md-3">
                    <label class="form-label fw-bold small text-secondary">
                        <i class="fa-solid fa-flag-checkered me-1 text-primary"></i> Fecha Inicio EP (Concertada)
                    </label>
                    <input type="date" class="form-control bg-light" readonly value="<?= htmlspecialchars($fechaInicioM1) ?>">
                    <small class="text-muted fst-italic">Pactada en Momento 1</small>
                </div>

                <!-- Fecha Seguimiento Calculada (Exactamente 3 meses después, READONLY) -->
                <div class="col-md-3">
                    <label class="form-label fw-bold small text-secondary">
                        <i class="fa-solid fa-calendar-day me-1 text-primary"></i> Fecha Programada (3 Meses)
                    </label>
                    <input type="date" class="form-control bg-light" readonly value="<?= htmlspecialchars($fechaMomento2Pactada) ?>">
                    <small class="text-muted fst-italic">Cálculo legal (+3 meses)</small>
                </div>

                <!-- Fecha Real en que se hace el Seguimiento -->
                <div class="col-md-3">
                    <label class="form-label fw-bold small text-secondary">
                        <i class="fa-solid fa-calendar-plus me-1 text-success"></i> Fecha Real de Seguimiento <span class="text-danger">*</span>
                    </label>
                    <input type="date" name="fecha_momento2_real" class="form-control border-success shadow-sm" value="<?= htmlspecialchars($fechaMomento2Real) ?>" required>
                    <small class="text-muted fst-italic">Fecha de la sesión evaluativa</small>
                </div>

                <!-- Modalidad del Seguimiento (Select) -->
                <div class="col-md-3">
                    <label class="form-label fw-bold small text-secondary">
                        <i class="fa-solid fa-video me-1 text-primary"></i> Modalidad de Reunión <span class="text-danger">*</span>
                    </label>
                    <select name="modalidad" id="modalidad_seguimiento" class="form-select shadow-sm" required>
                        <option value="Presencial" <?= ($modalidadActual === 'Presencial' ? 'selected' : '') ?>>Presencial</option>
                        <option value="Virtual" <?= ($modalidadActual === 'Virtual' ? 'selected' : '') ?>>Virtual</option>
                        <option value="Híbrida" <?= ($modalidadActual === 'Híbrida' ? 'selected' : '') ?>>Híbrida</option>
                    </select>
                    <small class="text-muted fst-italic">Presencial / Virtual / Híbrida</small>
                </div>

                <!-- Enlace de Grabación (Completar cuando la sesión sea Virtual o Híbrida) -->
                <div class="col-12">
                    <div class="p-3 bg-light border border-info rounded-3">
                        <label class="form-label fw-bold small text-primary mb-1">
                            <i class="fa-solid fa-link me-1"></i> Enlace de la Grabación del Seguimiento Virtual <span class="text-danger">*</span>
                        </label>
                        <input type="url" name="enlace_grabacion" id="enlace_grabacion" class="form-control" placeholder="https://teams.microsoft.com/... o https://meet.google.com/..." value="<?= htmlspecialchars($enlaceGrabacion) ?>">
                        <small class="text-muted">Requerido por protocolo institucional SENA cuando la sesión se realiza en modalidad Virtual o Híbrida.</small>
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
                        Seleccione la valoración correspondiente para cada factor. El campo de observaciones enfrente se muestra siempre visible para registrar el detalle de cada factor marcado como <strong>"Por mejorar"</strong>.
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
                                    <th class="col-4">Observaciones / Plan de Mejora (Si aplica)</th>
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
                                                               id="rad_sat_<?= $idVal ?>" 
                                                               value="Satisfactorio" 
                                                               <?= (!$esPorMejorar ? 'checked' : '') ?> >
                                                        <label class="form-check-label text-success fw-semibold" for="rad_sat_<?= $idVal ?>">
                                                            <i class="fa-solid fa-circle-check me-1"></i> Satisfactorio
                                                        </label>
                                                    </div>
                                                    <div class="form-check form-check-inline m-0">
                                                        <input class="form-check-input" type="radio" 
                                                               name="variables[<?= $idVal ?>][valoracion]" 
                                                               id="rad_mej_<?= $idVal ?>" 
                                                               value="Por mejorar" 
                                                               <?= ($esPorMejorar ? 'checked' : '') ?> >
                                                        <label class="form-check-label text-warning-emphasis fw-semibold" for="rad_mej_<?= $idVal ?>">
                                                            <i class="fa-solid fa-triangle-exclamation me-1"></i> Por mejorar
                                                        </label>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div id="box_obs_<?= $idVal ?>">
                                                    <textarea name="variables[<?= $idVal ?>][observacion]" 
                                                              id="txt_obs_<?= $idVal ?>" 
                                                              class="form-control form-control-sm border-warning" 
                                                              rows="2" 
                                                              placeholder="Describa la observación o el compromiso de mejora..."><?= htmlspecialchars($obsActual) ?></textarea>
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
                    <p class="text-muted small">
                        Evalúe el desempeño actitudinal del aprendiz en la empresa u organización.
                    </p>
                </div>

                <div class="col-12">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="col-1 text-center">Código</th>
                                    <th class="col-4">Factor Comportamental a Evaluar</th>
                                    <th class="col-3 text-center">Valoración (Seleccionar 1)</th>
                                    <th class="col-4">Observaciones / Plan de Mejora (Si aplica)</th>
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
                                                               id="rad_sat_<?= $idVal ?>" 
                                                               value="Satisfactorio" 
                                                               <?= (!$esPorMejorar ? 'checked' : '') ?> >
                                                        <label class="form-check-label text-success fw-semibold" for="rad_sat_<?= $idVal ?>">
                                                            <i class="fa-solid fa-circle-check me-1"></i> Satisfactorio
                                                        </label>
                                                    </div>
                                                    <div class="form-check form-check-inline m-0">
                                                        <input class="form-check-input" type="radio" 
                                                               name="variables[<?= $idVal ?>][valoracion]" 
                                                               id="rad_mej_<?= $idVal ?>" 
                                                               value="Por mejorar" 
                                                               <?= ($esPorMejorar ? 'checked' : '') ?> >
                                                        <label class="form-check-label text-warning-emphasis fw-semibold" for="rad_mej_<?= $idVal ?>">
                                                            <i class="fa-solid fa-triangle-exclamation me-1"></i> Por mejorar
                                                        </label>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div id="box_obs_<?= $idVal ?>">
                                                    <textarea name="variables[<?= $idVal ?>][observacion]" 
                                                              id="txt_obs_<?= $idVal ?>" 
                                                              class="form-control form-control-sm border-warning" 
                                                              rows="2" 
                                                              placeholder="Describa la observación o el compromiso de mejora..."><?= htmlspecialchars($obsActual) ?></textarea>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- SECCIÓN 5: OBSERVACIONES GENERALES MOMENTO 2 -->
                <div class="col-12 mt-4">
                    <h6 class="text-secondary fw-bold border-bottom pb-2">
                        <i class="fa-solid fa-comment-dots me-1 text-success"></i> 5. Observaciones Generales del Seguimiento (Momento 2)
                    </h6>
                    <label class="form-label fw-bold small text-secondary">
                        Consigne aquí el concepto general del seguimiento, avances en el desarrollo de actividades y recomendaciones para la segunda mitad de la etapa productiva:
                    </label>
                    <textarea name="obs_momento2" class="form-control shadow-sm" rows="4" placeholder="Ej: El aprendiz presenta un desempeño favorable en el cumplimiento de sus labores asignadas. Demuestra compromiso, puntualidad y aplicación de competencias técnicas en el desarrollo de software. Continúa en etapa práctica normal hasta el momento 3..."><?= htmlspecialchars($obsM2) ?></textarea>
                </div>

                <!-- SECCIÓN 6: CIERRE Y FIRMAS -->
                <div class="col-12 mt-4">
                    <h6 class="text-secondary fw-bold border-bottom pb-2">
                        <i class="fa-solid fa-signature me-1 text-success"></i> 6. Cierre y Firmas Tripartitas
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
                    <a href="home.php?pg=1537&idficha=<?= htmlspecialchars($idficha ?? '') ?>&tipo_formato=302" class="btn btn-secondary shadow me-2">
                        <i class="fa-solid fa-circle-xmark me-1"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-success shadow px-4">
                        <i class="fa-solid fa-floppy-disk me-2"></i> Guardar Evaluación Momento 2
                    </button>
                </div>
            </form>
        </div>
    <?php endif; ?>

    <!-- 4. TABLA DE APRENDICES DE LA FICHA (VISTA MOMENTO 2) -->
    <?php if (!empty($idficha)): ?>
        <div class="mt-4">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <h5 class="mb-0 text-secondary d-flex align-items-center">
                    <i class="fa-solid fa-list-check me-2 text-success"></i>
                    Aprendices Asignados para Seguimiento M2 - Ficha <?= htmlspecialchars($idficha) ?>
                </h5>
                <span class="text-muted small">
                    Mostrando formato: <strong>Momento 2 (Evaluación Parcial a 3 Meses)</strong>
                </span>
            </div>

            <div class="table-responsive">
                <table id="example" class="table table-striped table-hover align-middle w-100">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center col-1">No</th>
                            <th class="col-3">Datos del Aprendiz</th>
                            <th class="col-3">Empresa Coformadora</th>
                            <th class="col-2">Estado Momento 2</th>
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
                                    $tieneM2 = !empty($ap['fecha_momento2_real']) || (!empty($ap['total_evaluaciones']) && (int)$ap['total_evaluaciones'] > 0);

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
                                                Modalidad: <span class="badge bg-light text-dark border"><?= htmlspecialchars($ap['modalidad'] ?? 'Presencial') ?></span>
                                            </small>
                                        <?php else: ?>
                                            <span class="badge bg-secondary bg-opacity-25 text-secondary">
                                                <i class="fa-solid fa-triangle-exclamation me-1"></i> Sin concertación previa (M1)
                                            </span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Estado Momento 2 -->
                                    <td>
                                        <?php if (!$tieneM1): ?>
                                            <span class="badge bg-danger px-2 py-1">
                                                <i class="fa-solid fa-ban me-1"></i> Requiere Momento 1
                                            </span>
                                        <?php elseif ($tieneM2): ?>
                                            <span class="badge bg-success px-2 py-1">
                                                <i class="fa-solid fa-circle-check me-1"></i> Seguimiento Realizado
                                            </span><br>
                                            <small class="text-muted" style="font-size:0.75rem;">
                                                Fecha: <?= date('d/m/Y', strtotime($ap['fecha_momento2_real'] ?? date('Y-m-d'))) ?>
                                            </small>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark px-2 py-1">
                                                <i class="fa-solid fa-clock me-1"></i> Pendiente M2 (3 Meses)
                                            </span><br>
                                            <?php if (!empty($ap['fecha_inicio'])): ?>
                                                <small class="text-muted" style="font-size:0.75rem;">
                                                    Pactado: <?= date('d/m/Y', strtotime($ap['fecha_inicio'] . ' +3 months')) ?>
                                                </small>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Firmas Tripartitas -->
                                    <td class="text-center">
                                        <?php if ($tieneM1): ?>
                                            <div class="d-flex flex-column gap-1 align-items-center">
                                                <span class="badge bg-success w-100 py-1" title="Instructor SENA">
                                                    <i class="fa-solid fa-check me-1"></i> Instructor: Firmado
                                                </span>

                                                <a href="home.php?pg=1537&opera=alternar_firma&idcon=<?= $idConciActual ?>&rol=aprendiz&idficha=<?= htmlspecialchars($idficha) ?>&tipo_formato=302" 
                                                   class="badge <?= ($fAp === 'Firmado' ? 'bg-success' : 'bg-warning text-dark') ?> text-decoration-none w-100 py-1" 
                                                   title="Clic para alternar firma del aprendiz">
                                                    <?= ($fAp === 'Firmado' ? '<i class="fa-solid fa-check me-1"></i>' : '<i class="fa-solid fa-clock me-1"></i>') ?> Aprendiz: <?= $fAp ?>
                                                </a>

                                                <a href="home.php?pg=1537&opera=alternar_firma&idcon=<?= $idConciActual ?>&rol=empresa&idficha=<?= htmlspecialchars($idficha) ?>&tipo_formato=302" 
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
                                            <?php elseif ($tieneM2): ?>
                                                <!-- Editar Seguimiento Diligenciado -->
                                                <a href="home.php?pg=1537&opera=edi&idcon=<?= $idConciActual ?>&idficha=<?= htmlspecialchars($idficha) ?>&tipo_formato=302#formSeguimientoM2" 
                                                   title="Modificar Evaluación Momento 2" 
                                                   class="btn btn-link p-0 border-0 text-decoration-none">
                                                    <i class="fa-solid fa-file-pen fa-2x text-success"></i>
                                                </a>
                                            <?php else: ?>
                                                <!-- Diligenciar Seguimiento Momento 2 -->
                                                <a href="home.php?pg=1537&opera=diligenciar&idcon=<?= $idConciActual ?>&idficha=<?= htmlspecialchars($idficha) ?>&tipo_formato=302#formSeguimientoM2" 
                                                   title="Diligenciar Evaluación Momento 2" 
                                                   class="btn btn-link p-0 border-0 text-decoration-none">
                                                    <i class="fa-solid fa-clipboard-check fa-2x text-success"></i>
                                                </a>
                                            <?php endif; ?>

                                            <!-- Modal de Vista Previa M2 -->
                                            <?php if ($tieneM1): ?>
                                                <button type="button" 
                                                        class="btn btn-link p-0 border-0 text-decoration-none" 
                                                        title="Ver Formato Momento 2" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#modalVerM2_<?= $idAprendizActual ?>">
                                                    <i class="fa-solid fa-eye fa-2x text-success"></i>
                                                </button>

                                                <!-- Descarga PDF -->
                                                <a href="views/etavcsi_pdf.php?idcon=<?= $idConciActual ?>&tipo_formato=302" 
                                                   target="_blank" 
                                                   title="Descargar Formato Momento 2 PDF" 
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
                            <th>Estado Momento 2</th>
                            <th class="text-center">Firmas Tripartitas</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- 5. MODALES DE VISTA PREVIA GFPI-F-023 (MOMENTO 2) -->
        <?php if (!empty($aprendicesFicha)): ?>
            <?php foreach ($aprendicesFicha as $ap): ?>
                <?php if (!empty($ap['idcon'])): ?>
                    <?php
                        $idApModal = $ap['idaprendiz'] ?? $ap['idusu'] ?? null;
                        $idConModal = $ap['idcon'];
                        $nombreApModal = $ap['nom_aprendiz'] ?? (trim(($ap['nomusu'] ?? '') . ' ' . ($ap['apeusu'] ?? '')));
                        $docApModal = $ap['doc_aprendiz'] ?? $ap['ndocusu'] ?? '';
                        $fInicioMod = $ap['fecha_inicio'] ?? date('Y-m-d');
                        $fM2ProgMod = date('d/m/Y', strtotime($fInicioMod . ' +3 months'));
                        $fM2RealMod = !empty($ap['fecha_momento2_real']) ? date('d/m/Y', strtotime($ap['fecha_momento2_real'])) : 'Pendiente de realización';

                        // Cargar variables evaluadas para el modal
                        $detallesModal = $mcsi->getDetallesConsertamiento($idConModal);
                    ?>
                    <div class="modal fade" id="modalVerM2_<?= $idApModal ?>" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                            <div class="modal-content text-start">
                                
                                <div class="modal-header bg-success text-white py-2">
                                    <h5 class="modal-title fs-6 fw-bold">
                                        <i class="fa-solid fa-clock-rotate-left me-2"></i>
                                        Formato Oficial SENA GFPI-F-023 | Momento 2: Evaluación Parcial - <?= htmlspecialchars($nombreApModal) ?>
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
                                                <span class="d-block text-muted small">MOMENTO 2: EVALUACIÓN PARCIAL (SEGUIMIENTO A 3 MESES)</span>
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

                                    <!-- 2. PARÁMETROS DEL SEGUIMIENTO -->
                                    <div class="table-responsive mb-3">
                                        <table class="table table-bordered table-sm mb-0">
                                            <thead class="table-success">
                                                <tr>
                                                    <th colspan="4" class="text-uppercase fw-bold">2. Fechas y Modalidad de la Evaluación Parcial</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td class="bg-light fw-bold col-3">Fecha Inicio Etapa Productiva:</td>
                                                    <td class="col-3"><?= date('d/m/Y', strtotime($fInicioMod)) ?></td>
                                                    <td class="bg-light fw-bold col-3">Fecha Programada (3 Meses):</td>
                                                    <td class="col-3"><strong><?= $fM2ProgMod ?></strong></td>
                                                </tr>
                                                <tr>
                                                    <td class="bg-light fw-bold">Fecha Real del Seguimiento:</td>
                                                    <td class="text-success fw-bold"><?= $fM2RealMod ?></td>
                                                    <td class="bg-light fw-bold">Modalidad de la Sesión:</td>
                                                    <td><span class="badge bg-primary"><?= htmlspecialchars($ap['modalidad'] ?? 'Presencial') ?></span></td>
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
                                                    <th class="col-5">Observaciones / Compromisos</th>
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
                                                            <em>Aún no se han evaluado las variables para este aprendiz en el Momento 2.</em>
                                                        </td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- 4. OBSERVACIONES GENERALES -->
                                    <div class="table-responsive mb-3">
                                        <table class="table table-bordered table-sm mb-0">
                                            <thead class="table-success">
                                                <tr>
                                                    <th class="text-uppercase fw-bold">4. Observaciones Generales del Seguimiento</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td class="p-3">
                                                        <?= !empty($ap['obs_momento2']) ? nl2br(htmlspecialchars($ap['obs_momento2'])) : '<span class="text-muted fst-italic">Sin observaciones generales registradas para el Momento 2.</span>' ?>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- 5. FIRMAS TRIPARTITAS -->
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-sm mb-0 text-center">
                                            <thead class="table-success">
                                                <tr>
                                                    <th colspan="3" class="text-uppercase fw-bold">5. Firmas Tripartitas y Validación</th>
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
                                    <a href="views/etavcsi_pdf.php?idcon=<?= $idConModal ?>&tipo_formato=302" target="_blank" class="btn btn-danger shadow-sm">
                                        <i class="fa-solid fa-download me-1"></i> Descargar PDF Oficial M2
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
