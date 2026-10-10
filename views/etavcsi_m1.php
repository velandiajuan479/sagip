<?php
    require_once("controllers/etaccsi.php");

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
    $tituloPagina = "<i class='" . $icono . "'></i> Gestión de Concertación y Evaluación de Etapa Productiva (GFPI-F-023)";
    echo titulo2($tituloPagina, 1);
?>

    <!-- 1. TARJETA DE FILTROS: SELECCIÓN DE MOMENTO EVALUATIVO Y FICHA ASIGNADA -->
    <div class="card p-4 mb-4 shadow-sm border-0">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <p class="text-muted small mb-0">
                Seleccione el momento evaluativo oficial del SENA y la ficha asignada para gestionar, diligenciar y supervisar las concertaciones y firmas de los aprendices.
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
            <input type="hidden" name="pg" value="<?= htmlspecialchars($pg ?? '') ?>">
            <input type="hidden" name="tipo_formato" value="<?= $momentoActual == '2' ? '302' : ($momentoActual == '3' ? '303' : '301') ?>">

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

    <!-- 2. DASHBOARD PRE-CONSULTA (PANORAMA GENERAL GLOBAL) -->
    <?php if (empty($idficha) && !empty($resumenGlobal)): ?>
        <div class="mb-4">
            <h5 class="text-secondary fw-bold mb-3 d-flex align-items-center">
                <i class="fa-solid fa-chart-pie me-2 text-success"></i> Panorama General de Seguimiento Global
            </h5>
            <div class="row g-3 mb-4">
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted fw-bold text-uppercase">Fichas Asignadas</small>
                                <h3 class="mb-0 fw-bold text-primary"><?= htmlspecialchars($resumenGlobal['total_fichas'] ?? 0) ?></h3>
                            </div>
                            <div class="kpi-icon-box bg-primary bg-opacity-10 text-primary">
                                <i class="fa-solid fa-layer-group fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted fw-bold text-uppercase">Aprendices Totales</small>
                                <h3 class="mb-0 fw-bold text-info"><?= htmlspecialchars($resumenGlobal['total_aprendices'] ?? 0) ?></h3>
                            </div>
                            <div class="kpi-icon-box bg-info bg-opacity-10 text-info">
                                <i class="fa-solid fa-users fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted fw-bold text-uppercase">Concertaciones Registradas</small>
                                <h3 class="mb-0 fw-bold text-success"><?= htmlspecialchars($resumenGlobal['total_concertados'] ?? 0) ?></h3>
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
                                <small class="text-muted fw-bold text-uppercase">Pendientes por Registrar</small>
                                <h3 class="mb-0 fw-bold text-warning"><?= htmlspecialchars($resumenGlobal['total_pendientes'] ?? 0) ?></h3>
                            </div>
                            <div class="kpi-icon-box bg-warning bg-opacity-10 text-warning">
                                <i class="fa-solid fa-clock fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Banner Orientador Institucional SENA -->
            <div class="card border-0 bg-light p-4 text-center rounded-3 shadow-sm">
                <div class="py-3">
                    <i class="fa-solid fa-folder-open fa-3x text-muted mb-3"></i>
                    <h5 class="fw-bold text-secondary">Seleccione una Ficha para Comenzar</h5>
                    <p class="text-muted mb-0 mx-auto col-md-8">
                        Para gestionar el listado individual de aprendices, registrar las concertaciones de actividades o generar el formato oficial <strong>GFPI-F-023</strong>, elija la ficha correspondiente arriba y presione el botón <strong>Consultar</strong>.
                    </p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- 3. DASHBOARD DETALLADO DE FICHA -->
    <?php if (!empty($idficha) && !empty($resumenFicha)): ?>
        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <h5 class="text-secondary fw-bold mb-0 d-flex align-items-center">
                    <i class="fa-solid fa-chart-column me-2 text-success"></i>
                    Dashboard de Seguimiento - Ficha <?= htmlspecialchars($idficha) ?>
                </h5>
                <a href="home.php?pg=<?= htmlspecialchars($pg ?? '') ?>" class="btn btn-sm btn-outline-secondary">
                    <i class="fa-solid fa-arrow-left me-1"></i> Ver Panorama Global
                </a>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted fw-bold text-uppercase">Total Aprendices</small>
                                <h3 class="mb-0 fw-bold text-primary"><?= htmlspecialchars($resumenFicha['total'] ?? 0) ?></h3>
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
                                <small class="text-muted fw-bold text-uppercase">Concertados / Realizados</small>
                                <h3 class="mb-0 fw-bold text-success"><?= htmlspecialchars($resumenFicha['concertados'] ?? 0) ?></h3>
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
                                <small class="text-muted fw-bold text-uppercase">Pendientes Diligenciar</small>
                                <h3 class="mb-0 fw-bold text-warning"><?= htmlspecialchars($resumenFicha['pendientes_diligenciar'] ?? 0) ?></h3>
                            </div>
                            <div class="kpi-icon-box bg-warning bg-opacity-10 text-warning">
                                <i class="fa-solid fa-file-circle-exclamation fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted fw-bold text-uppercase">Pendientes de Firma</small>
                                <h3 class="mb-0 fw-bold text-danger"><?= htmlspecialchars($resumenFicha['pendientes_firmas'] ?? 0) ?></h3>
                            </div>
                            <div class="kpi-icon-box bg-danger bg-opacity-10 text-danger">
                                <i class="fa-solid fa-signature fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- 4. FORMULARIO MOMENTO 1 (PLANEACIÓN Y CONCERTACIÓN COMPLETA DE ACTIVIDADES) -->
    <?php if (!empty($conEdit) || !empty($aprendizPreload)): ?>
        <?php 
            $apData = $conEdit ?: $aprendizPreload;
            $esEdicion = !empty($conEdit);

            $fechaInicioCalc = $conEdit['fecha_inicio'] ?? $conEdit['fec_ini_etapa'] ?? date('Y-m-d');
            $fechaMomento2Proyectada = date('Y-m-d', strtotime($fechaInicioCalc . ' +3 months'));
        ?>
        <div class="card p-4 mb-5 shadow-sm border-0 border-top border-success border-4" id="formDiligenciamiento">
            <h4 class="mb-3 text-secondary d-flex align-items-center">
                <i class="fa-solid fa-file-circle-plus text-success me-2"></i>
                <?= $esEdicion ? "Modificar Concertación Momento 1 (GFPI-F-023)" : "Diligenciar Concertación Momento 1 (GFPI-F-023)" ?>
            </h4>
            <p class="text-muted small mb-4">
                Complete el formulario oficial del SENA para el aprendiz <strong><?= htmlspecialchars($apData['nom_aprendiz'] ?? $apData['nomusu'] ?? '') ?></strong>.
            </p>

            <form id="frmConcertacion" name="frmConcertacion" class="row g-3" method="POST" action="home.php?pg=<?= htmlspecialchars($pg ?? '') ?>">
                <input type="hidden" name="opera" value="<?= $esEdicion ? 'editar' : 'guardar' ?>">
                <input type="hidden" name="idcon" value="<?= htmlspecialchars($conEdit['idcon'] ?? $conEdit['idconci'] ?? '') ?>">
                <input type="hidden" name="idaprendiz" value="<?= htmlspecialchars($apData['idaprendiz'] ?? $apData['idusu'] ?? '') ?>">
                <input type="hidden" name="idficha" value="<?= htmlspecialchars($idficha ?: ($apData['idficha'] ?? $apData['idfic'] ?? '')) ?>">
                <input type="hidden" name="tipo_formato" value="<?= htmlspecialchars($tipo_formato ?? '') ?>">

                <!-- INFORMACIÓN GENERAL -->
                <div class="col-12">
                    <h6 class="text-success fw-bold border-bottom pb-2">
                        <i class="fa-solid fa-building-columns me-1"></i> Información General
                    </h6>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold small">Regional</label>
                    <input type="text" class="form-control bg-light" readonly value="CUNDINAMARCA">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold small">Centro de Formación</label>
                    <input type="text" class="form-control bg-light" readonly value="CENTRO DE DESARROLLO AGROEMPRESARIAL">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold small">Programa de Formación</label>
                    <input type="text" class="form-control bg-light" readonly value="<?= htmlspecialchars($apData['nom_programa'] ?? $apData['nompro'] ?? 'ANÁLISIS Y DESARROLLO DE SOFTWARE') ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold small">No. Grupo / Ficha</label>
                    <input type="text" class="form-control bg-light" readonly value="<?= htmlspecialchars($idficha ?: ($apData['idficha'] ?? $apData['idfic'] ?? '')) ?>">
                </div>

                <!-- DATOS DEL APRENDIZ -->
                <div class="col-12 mt-4">
                    <h6 class="text-primary fw-bold border-bottom pb-2">
                        <i class="fa-solid fa-user-graduate me-1"></i> Datos del Aprendiz
                    </h6>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold small">Nombre Completo</label>
                    <input type="text" class="form-control bg-light" readonly value="<?= htmlspecialchars($apData['nom_aprendiz'] ?? $apData['nomusu'] ?? '') ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold small">Tipo Documento</label>
                    <input type="text" name="tipo_doc_aprendiz" class="form-control bg-light" readonly value="<?= htmlspecialchars($apData['tipo_doc_aprendiz'] ?? 'CC') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold small">N° de Identificación</label>
                    <input type="text" class="form-control bg-light" readonly value="<?= htmlspecialchars($apData['doc_aprendiz'] ?? $apData['ndocusu'] ?? $apData['docusu'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold small">Contacto Telefónico <span class="text-danger">*</span></label>
                    <input type="text" name="tel_aprendiz" class="form-control" value="<?= htmlspecialchars($apData['tel_aprendiz'] ?? $apData['telusu'] ?? '') ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold small">Correo Electrónico Personal <span class="text-danger">*</span></label>
                    <input type="email" name="correo_aprendiz" class="form-control" value="<?= htmlspecialchars($apData['correo_aprendiz'] ?? $apData['corusu'] ?? '') ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold small">Correo Electrónico Institucional</label>
                    <input type="email" name="correo_inst_aprendiz" class="form-control" placeholder="aprendiz@sena.edu.co" value="<?= htmlspecialchars($apData['correo_inst_aprendiz'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold small">Alternativa de EP Registrada <span class="text-danger">*</span></label>
                    <input type="text" name="alternativa_ep" class="form-control" placeholder="Ej: Contrato de Aprendizaje / Pasantía" value="<?= htmlspecialchars($conEdit['alternativa_ep'] ?? 'Contrato de Aprendizaje') ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold small">Fecha Registro SofiaPlus</label>
                    <input type="date" name="fecha_registro_sofia" class="form-control" value="<?= htmlspecialchars($conEdit['fecha_registro_sofia'] ?? date('Y-m-d')) ?>">
                </div>

                <!-- DATOS DEL INSTRUCTOR DE SEGUIMIENTO -->
                <div class="col-12 mt-4">
                    <h6 class="text-primary fw-bold border-bottom pb-2">
                        <i class="fa-solid fa-chalkboard-user me-1"></i> Datos del Instructor de Seguimiento
                    </h6>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold small">Nombre Completo</label>
                    <input type="text" class="form-control bg-light" readonly value="<?= htmlspecialchars($_SESSION['nomusu'] ?? $conEdit['nom_instructor'] ?? 'JUAN CAMILO MEDINA FORERO') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold small">Contacto Telefónico</label>
                    <input type="text" class="form-control bg-light" readonly value="<?= htmlspecialchars($_SESSION['telusu'] ?? $conEdit['tel_instructor'] ?? '322 8638817') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold small">Correo Electrónico Institucional</label>
                    <input type="email" class="form-control bg-light" readonly value="<?= htmlspecialchars($_SESSION['corusu'] ?? $conEdit['correo_inst_instructor'] ?? 'jcmedina@sena.edu.co') ?>">
                </div>

                <!-- DATOS DEL ENTE CO-FORMADOR (EMPRESA) -->
                <div class="col-12 mt-4">
                    <h6 class="text-primary fw-bold border-bottom pb-2">
                        <i class="fa-solid fa-building me-1"></i> Datos del Ente Co-formador (Jefe Inmediato o Tutor y Empresa)
                    </h6>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold small">Nombre Empresa o Entidad Co-formadora <span class="text-danger">*</span></label>
                    <input type="text" name="nombre_empresa" class="form-control" placeholder="Razón Social completa" value="<?= htmlspecialchars($conEdit['nombre_empresa'] ?? '') ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold small">NIT <span class="text-danger">*</span></label>
                    <input type="text" name="nit_empresa" class="form-control" placeholder="Ej: 900.123.456-7" value="<?= htmlspecialchars($conEdit['nit_empresa'] ?? '') ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold small">Dirección Empresa <span class="text-danger">*</span></label>
                    <input type="text" name="direccion_empresa" class="form-control" placeholder="Dirección sede de prácticas" value="<?= htmlspecialchars($conEdit['direccion_empresa'] ?? '') ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold small">Correo Electrónico Empresa <span class="text-danger">*</span></label>
                    <input type="email" name="correo_empresa" class="form-control" placeholder="contacto@empresa.com" value="<?= htmlspecialchars($conEdit['correo_empresa'] ?? '') ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold small">Nombre Jefe Inmediato / Tutor <span class="text-danger">*</span></label>
                    <input type="text" name="nombre_jefe" class="form-control" placeholder="Nombre completo" value="<?= htmlspecialchars($conEdit['nombre_jefe'] ?? '') ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold small">Cargo del Jefe / Tutor <span class="text-danger">*</span></label>
                    <input type="text" name="cargo_jefe" class="form-control" placeholder="Ej: Director de Tecnología" value="<?= htmlspecialchars($conEdit['cargo_jefe'] ?? '') ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold small">Contacto Telefónico Tutor <span class="text-danger">*</span></label>
                    <input type="text" name="telefono_jefe" class="form-control" placeholder="Celular / Extensión" value="<?= htmlspecialchars($conEdit['telefono_jefe'] ?? '') ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold small">Nombre Otro Contacto</label>
                    <input type="text" name="nombre_otro_contacto" class="form-control" placeholder="Nombre contacto secundario / RRHH" value="<?= htmlspecialchars($conEdit['nombre_otro_contacto'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold small">Teléfono Institucional (Fijo/Móvil)</label>
                    <input type="text" name="telefono_institucional" class="form-control" placeholder="Teléfono PBX empresa" value="<?= htmlspecialchars($conEdit['telefono_institucional'] ?? '') ?>">
                </div>

                <!-- PERSONA EN SITUACIÓN DE DISCAPACIDAD -->
                <div class="col-12 mt-4">
                    <h6 class="text-primary fw-bold border-bottom pb-2">
                        <i class="fa-solid fa-wheelchair me-1"></i> Persona en Situación de Discapacidad (Si aplica)
                    </h6>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold small">Nombre de la Persona que Asiste</label>
                    <input type="text" name="discapacidad_asistente" class="form-control" placeholder="N/A si no aplica" value="<?= htmlspecialchars($conEdit['discapacidad_asistente'] ?? 'N/A') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold small">Tipo de Asistencia</label>
                    <input type="text" name="discapacidad_tipo" class="form-control" placeholder="Ej: Lenguaje de señas, apoyo visual..." value="<?= htmlspecialchars($conEdit['discapacidad_tipo'] ?? 'N/A') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold small">Contacto Telefónico Asistente</label>
                    <input type="text" name="discapacidad_telefono" class="form-control" placeholder="N/A si no aplica" value="<?= htmlspecialchars($conEdit['discapacidad_telefono'] ?? 'N/A') ?>">
                </div>

                <!-- MOMENTO N° 1 - PLANEACIÓN DE LA ETAPA PRODUCTIVA -->
                <div class="col-12 mt-4">
                    <h6 class="text-success fw-bold border-bottom pb-2">
                        <i class="fa-solid fa-calendar-check me-1"></i> Momento N° 1 - Planeación de la Etapa Productiva
                    </h6>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold small">Fecha Inicio EP (DD/MM/AA) <span class="text-danger">*</span></label>
                    <input type="date" id="fecha_inicio" name="fecha_inicio" class="form-control" value="<?= htmlspecialchars($fechaInicioCalc) ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold small">Fecha Fin EP (DD/MM/AA) <span class="text-danger">*</span></label>
                    <input type="date" name="fecha_fin" class="form-control" value="<?= htmlspecialchars($conEdit['fecha_fin'] ?? date('Y-m-d', strtotime('+6 months'))) ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold small">Fecha Afiliación ARL <span class="text-danger">*</span></label>
                    <input type="date" name="fecha_arl" class="form-control" value="<?= htmlspecialchars($conEdit['fecha_arl'] ?? $fechaInicioCalc) ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold small">Número de Póliza ARL (Si aplica)</label>
                    <input type="text" name="num_poliza_arl" class="form-control" placeholder="N° Póliza de cobertura" value="<?= htmlspecialchars($conEdit['num_poliza_arl'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold small">Horario <span class="text-danger">*</span></label>
                    <input type="text" name="horario_trabajo" class="form-control" placeholder="Ej: Diurno, Lunes a Viernes de 7:00 AM a 5:00 PM (44 horas)" value="<?= htmlspecialchars($conEdit['horario_trabajo'] ?? 'Diurno, Lunes a Viernes de 8:00 AM a 5:00 PM (44 horas semanales)') ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold small">Enlace Grabación Momento 1 (Si es virtual)</label>
                    <input type="url" name="enlace_grabacion" class="form-control" placeholder="https://teams.microsoft.com/..." value="<?= htmlspecialchars($conEdit['enlace_grabacion'] ?? '') ?>">
                </div>

                <!-- CÁLCULO PROYECTADO MOMENTO 2 -->
                <div class="col-12 mt-2">
                    <div class="alert alert-info py-2 small mb-0">
                        <i class="fa-solid fa-circle-info me-1"></i>
                        <strong>Fecha Programada para Momento N° 2 (Seguimiento 3 Meses):</strong> 
                        <span id="lblMomento2Proyectado" class="fw-bold text-dark"><?= date('d/m/Y', strtotime($fechaMomento2Proyectada)) ?></span>
                    </div>
                </div>

                <!-- CONCERTACIÓN PLAN DE TRABAJO EN TEXTAREAS -->
                <div class="col-12 mt-4">
                    <h6 class="text-primary fw-bold border-bottom pb-2">
                        <i class="fa-solid fa-list-check me-1"></i> Concertación Plan de Trabajo Durante la Etapa Productiva
                    </h6>
                </div>

                <!-- COMPETENCIAS TÉCNICAS (TEXTAREA) -->
                <div class="col-12 mb-3">
                    <label class="form-label fw-bold small">Competencias a Desarrollar (Solamente Técnicas del Programa) <span class="text-danger">*</span></label>
                    <textarea name="competencias" class="form-control" rows="3" placeholder="Describa las competencias técnicas a desarrollar..." required><?= htmlspecialchars($conEdit['competencias'] ?? "220501096 - Desarrollar la solución de software de acuerdo con el diseño y requerimientos.\n220501097 - Implantar la solución de software según la arquitectura y requerimientos del cliente.") ?></textarea>
                </div>

                <!-- RESULTADOS DE APRENDIZAJE / RAPs (TEXTAREA) -->
                <div class="col-12 mb-3">
                    <label class="form-label fw-bold small">Resultados de Aprendizaje (RAPs) <span class="text-danger">*</span></label>
                    <textarea name="raps" class="form-control" rows="3" placeholder="Describa los resultados de aprendizaje (RAPs) asociados..." required><?= htmlspecialchars($conEdit['raps'] ?? "1RAP: Construir componentes de software según especificaciones de diseño.\n2RAP: Ejecutar pruebas unitarias e integrales del sistema.") ?></textarea>
                </div>

                <!-- ACTIVIDADES A DESARROLLAR (TEXTAREA) -->
                <div class="col-12 mb-3">
                    <label class="form-label fw-bold small">Actividades a Desarrollar (Durante los 6 meses de Etapa Productiva) <span class="text-danger">*</span></label>
                    <textarea name="actividades_lista" class="form-control" rows="3" placeholder="Describa el listado de actividades a desarrollar..." required><?= htmlspecialchars($conEdit['actividades_lista'] ?? "1. Diseñar y codificar módulos web bajo estándares de calidad SENA.\n2. Elaborar documentación técnica y manuales de usuario.") ?></textarea>
                </div>

                <!-- EVIDENCIAS DE APRENDIZAJE (TEXTAREA) -->
                <div class="col-12 mb-3">
                    <label class="form-label fw-bold small">Evidencias de Aprendizaje (Ingresadas como lista o texto quincenal/mensual) <span class="text-danger">*</span></label>
                    <textarea name="fechas_entrega" class="form-control" rows="4" placeholder="1. Bitácora quincenal de actividades de desarrollo.&#10;2. Certificado de entrega de código fuente.&#10;3. Informe de avance de etapa práctica firmado por el tutor." required><?= htmlspecialchars($conEdit['fechas_entrega'] ?? "1. Bitácora mensual de actividades de desarrollo.\n2. Informe técnico de avances de software.\n3. Repositorio de código fuente con documentación.") ?></textarea>
                </div>

                <!-- OBSERVACIONES ADICIONALES (TEXTAREA) -->
                <div class="col-12 mb-3">
                    <label class="form-label fw-bold small">Observaciones Adicionales</label>
                    <textarea name="tareas_acordadas" class="form-control" rows="4" placeholder="Observaciones adicionales, EPP requeridos, nivel de riesgo ARL..."><?= htmlspecialchars($conEdit['tareas_acordadas'] ?? "*El aprendiz debe presentar una bitácora mensual como lo establece el reglamento del aprendiz y la Guía GFPI-G-040. En total son 6 bitácoras equivalentes a los 6 meses de etapa productiva.") ?></textarea>
                </div>

                <!-- DATOS DE DILIGENCIAMIENTO Y CIERRE -->
                <div class="col-12 mt-4">
                    <h6 class="text-primary fw-bold border-bottom pb-2">
                        <i class="fa-solid fa-location-dot me-1"></i> Datos de Diligenciamiento y Cierre
                    </h6>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold small">Ciudad de Diligenciamiento <span class="text-danger">*</span></label>
                    <input type="text" name="ciudad_diligenciamiento" class="form-control" value="<?= htmlspecialchars($conEdit['ciudad_diligenciamiento'] ?? 'ZIPAQUIRÁ') ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold small">Fecha de Diligenciamiento <span class="text-danger">*</span></label>
                    <input type="date" name="fecha_diligenciamiento" class="form-control" value="<?= htmlspecialchars($conEdit['fecha_diligenciamiento'] ?? date('Y-m-d')) ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold small">Modalidad de Reunión <span class="text-danger">*</span></label>
                    <select name="modalidad_reunion" class="form-select" required>
                        <option value="Presencial" <?= (($conEdit['modalidad_reunion'] ?? '') === 'Presencial' ? 'selected' : '') ?>>Presencial</option>
                        <option value="Virtual" <?= (($conEdit['modalidad_reunion'] ?? 'Virtual') === 'Virtual' ? 'selected' : '') ?>>Virtual</option>
                    </select>
                </div>

                <!-- BOTONES DE ACCIÓN DEL FORMULARIO -->
                <div class="col-12 mt-4 text-md-end">
                    <a href="home.php?pg=<?= htmlspecialchars($pg ?? '') ?>&idficha=<?= htmlspecialchars($idficha ?? '') ?>&tipo_formato=<?= htmlspecialchars($tipo_formato ?? '') ?>" class="btn btn-secondary shadow me-2">
                        <i class="fa-solid fa-circle-xmark me-1"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-success shadow">
                        <i class="fa-solid fa-floppy-disk me-1"></i>
                        <?= $esEdicion ? "Actualizar Concertación" : "Registrar Concertación" ?>
                    </button>
                </div>
            </form>
        </div>
    <?php endif; ?>

    <!-- 5. TABLA DE APRENDICES DE LA FICHA -->
    <?php if (!empty($idficha)): ?>
        <div class="mt-4">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <h5 class="mb-0 text-secondary d-flex align-items-center">
                    <i class="fa-solid fa-file-lines me-2 text-success"></i>
                    Listado de Aprendices - Ficha <?= htmlspecialchars($idficha) ?>
                </h5>
                <span class="text-muted small">
                    Mostrando formato: <strong>Momento 1 (Concertación)</strong>
                </span>
            </div>

            <div class="table-responsive">
                <table id="example" class="table table-striped table-hover align-middle w-100">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center col-1">No</th>
                            <th class="col-3">Datos del Aprendiz</th>
                            <th class="col-3">Empresa Coformadora</th>
                            <th class="col-2">Estado Formato</th>
                            <th class="text-center col-2">Firmas Tripartitas</th>
                            <th class="text-end col-1">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($aprendicesFicha)): ?>
                            <?php $idx = 1; foreach ($aprendicesFicha as $ap): ?>
                                <?php 
                                    $idConciActual = $ap['idcon'] ?? $ap['idconci'] ?? null;
                                    $idAprendizActual = $ap['idaprendiz'] ?? $ap['idusu'] ?? null;
                                    $nombreAprendiz = $ap['nom_aprendiz'] ?? (trim(($ap['nomusu'] ?? '') . ' ' . ($ap['apeusu'] ?? '')));
                                    $documentoAprendiz = $ap['doc_aprendiz'] ?? $ap['ndocusu'] ?? $ap['docusu'] ?? '';
                                    $correoAprendiz = $ap['correo_aprendiz'] ?? $ap['corusu'] ?? 'Sin correo';
                                    $telefonoAprendiz = $ap['tel_aprendiz'] ?? $ap['telusu'] ?? 'Sin teléfono';

                                    $tieneFormato = !empty($idConciActual);
                                    $fAp = $ap['firma_aprendiz'] ?? $ap['fir_aprendiz'] ?? 'Pendiente';
                                    $fEm = $ap['firma_empresa'] ?? $ap['fir_empresa'] ?? 'Pendiente';
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
                                        <?php if ($tieneFormato && !empty($ap['nombre_empresa'])): ?>
                                            <strong><?= htmlspecialchars($ap['nombre_empresa']) ?></strong><br>
                                            <small class="text-muted">
                                                Tutor: <?= htmlspecialchars($ap['nombre_jefe'] ?? 'N/A') ?><br>
                                                Modalidad: <span class="badge bg-light text-dark border"><?= htmlspecialchars($ap['modalidad_reunion'] ?? $ap['modalidad'] ?? 'Presencial') ?></span>
                                            </small>
                                        <?php else: ?>
                                            <span class="text-muted fst-italic">Sin concertación registrada</span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Estado Formato -->
                                    <td>
                                        <?php if ($tieneFormato): ?>
                                            <?php if (($ap['estado'] ?? '') === 'Aprobado'): ?>
                                                <span class="badge bg-success px-2 py-1"><i class="fa-solid fa-circle-check me-1"></i> Concertado</span>
                                            <?php elseif (($ap['estado'] ?? '') === 'Rechazado'): ?>
                                                <span class="badge bg-danger px-2 py-1"><i class="fa-solid fa-circle-xmark me-1"></i> Rechazado</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning text-dark px-2 py-1"><i class="fa-solid fa-pen-ruler me-1"></i> Borrador</span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="badge bg-secondary px-2 py-1"><i class="fa-solid fa-clock me-1"></i> Pendiente</span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Firmas Tripartitas -->
                                    <td class="text-center">
                                        <?php if ($tieneFormato): ?>
                                            <div class="d-flex flex-column gap-1 align-items-center">
                                                <span class="badge bg-success w-100 py-1" title="Instructor SENA">
                                                    <i class="fa-solid fa-check me-1"></i> Instructor: Firmado
                                                </span>

                                                <a href="home.php?pg=<?= htmlspecialchars($pg ?? '') ?>&opera=alternar_firma&idcon=<?= $idConciActual ?>&rol=aprendiz&idficha=<?= htmlspecialchars($idficha) ?>&tipo_formato=<?= htmlspecialchars($tipo_formato ?? '') ?>" 
                                                   class="badge <?= ($fAp === 'Firmado' ? 'bg-success' : 'bg-warning text-dark') ?> text-decoration-none w-100 py-1" 
                                                   title="Clic para alternar firma del aprendiz">
                                                    <?= ($fAp === 'Firmado' ? '<i class="fa-solid fa-check me-1"></i>' : '<i class="fa-solid fa-clock me-1"></i>') ?> Aprendiz: <?= $fAp ?>
                                                </a>

                                                <a href="home.php?pg=<?= htmlspecialchars($pg ?? '') ?>&opera=alternar_firma&idcon=<?= $idConciActual ?>&rol=empresa&idficha=<?= htmlspecialchars($idficha) ?>&tipo_formato=<?= htmlspecialchars($tipo_formato ?? '') ?>" 
                                                   class="badge <?= ($fEm === 'Firmado' ? 'bg-success' : 'bg-warning text-dark') ?> text-decoration-none w-100 py-1" 
                                                   title="Clic para alternar firma de la empresa">
                                                    <?= ($fEm === 'Firmado' ? '<i class="fa-solid fa-check me-1"></i>' : '<i class="fa-solid fa-clock me-1"></i>') ?> Empresa: <?= $fEm ?>
                                                </a>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted small fst-italic">Requiere diligenciar formato</span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- ACCIONES HÍBRIDAS CON ALINEACIÓN FLEXBOX PURA EN BOOTSTRAP -->
                                    <td class="text-end align-middle">
                                        <div class="d-flex align-items-center justify-content-end gap-2 text-nowrap">
                                            <?php if ($tieneFormato): ?>
                                                <a href="home.php?pg=<?= htmlspecialchars($pg ?? '') ?>&opera=edi&idcon=<?= $idConciActual ?>&idficha=<?= htmlspecialchars($idficha) ?>&tipo_formato=<?= htmlspecialchars($tipo_formato ?? '') ?>#formDiligenciamiento" 
                                                   title="Editar Formato Diligenciado" 
                                                   class="btn btn-link p-0 border-0 text-decoration-none">
                                                    <i class="fa-solid fa-file-pen fa-2x text-success"></i>
                                                </a>
                                            <?php else: ?>
                                                <a href="home.php?pg=<?= htmlspecialchars($pg ?? '') ?>&opera=diligenciar&idaprendiz=<?= $idAprendizActual ?>&idficha=<?= htmlspecialchars($idficha) ?>&tipo_formato=<?= htmlspecialchars($tipo_formato ?? '') ?>#formDiligenciamiento" 
                                                   title="Diligenciar Formato" 
                                                   class="btn btn-link p-0 border-0 text-decoration-none">
                                                    <i class="fa-solid fa-file-signature fa-2x text-success"></i>
                                                </a>
                                            <?php endif; ?>

                                            <button type="button" 
                                                    class="btn btn-link p-0 border-0 text-decoration-none" 
                                                    title="Ver y Gestionar Formato GFPI-F-023" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#modalVerSena<?= $idAprendizActual ?>">
                                                <i class="fa-solid fa-eye fa-2x text-success"></i>
                                            </button>

                                            <a href="views/etavcsi_pdf.php?<?= (!empty($idConciActual) ? 'idcon=' . $idConciActual : 'idaprendiz=' . $idAprendizActual . '&idficha=' . htmlspecialchars($idficha)) ?>" 
                                               target="_blank" 
                                               title="Ver / Descargar formato PDF" 
                                               class="btn btn-link p-0 border-0 text-decoration-none">
                                                <i class="fa-solid fa-file-pdf fa-2x text-success"></i>
                                            </a>
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
                            <th>Estado Formato</th>
                            <th class="text-center">Firmas Tripartitas</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- MODALES DE VISTA PREVIA GFPI-F-023 -->
        <?php if (!empty($aprendicesFicha)): ?>
            <?php foreach ($aprendicesFicha as $ap): ?>
                <?php 
                    $idAprendizActual = $ap['idaprendiz'] ?? $ap['idusu'] ?? null;
                    $idConciActual = $ap['idcon'] ?? $ap['idconci'] ?? null;
                    $nombreAprendiz = $ap['nom_aprendiz'] ?? (trim(($ap['nomusu'] ?? '') . ' ' . ($ap['apeusu'] ?? '')));
                    $documentoAprendiz = $ap['doc_aprendiz'] ?? $ap['ndocusu'] ?? $ap['docusu'] ?? '';
                    $fAp = $ap['firma_aprendiz'] ?? $ap['fir_aprendiz'] ?? 'Pendiente';
                    $fEm = $ap['firma_empresa'] ?? $ap['fir_empresa'] ?? 'Pendiente';

                    $fInicioModal = $ap['fecha_inicio'] ?? $ap['fec_ini_etapa'] ?? date('Y-m-d');
                    $fMomento2Modal = date('d/m/Y', strtotime($fInicioModal . ' +3 months'));
                ?>
                <div class="modal fade" id="modalVerSena<?= $idAprendizActual ?>" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                        <div class="modal-content text-start">
                            
                            <div class="modal-header bg-success text-white py-2">
                                <h5 class="modal-title fs-6 fw-bold" id="lblModalSena<?= $idAprendizActual ?>">
                                    <i class="fa-solid fa-file-lines me-2"></i>
                                    Formato Oficial SENA GFPI-F-023 - <?= htmlspecialchars($nombreAprendiz) ?>
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                            </div>

                            <div class="modal-body p-4 bg-white">
                                <!-- ENCABEZADO OFICIAL -->
                                <div class="border border-dark p-2 mb-3 bg-light">
                                    <div class="row align-items-center text-center">
                                        <div class="col-md-2 border-end border-dark">
                                            <img src="img/logoSena.png" alt="SENA" class="img-fluid">
                                        </div>
                                        <div class="col-md-7 border-end border-dark">
                                            <span class="fw-bold d-block small">PROCESO GESTIÓN DE FORMACIÓN PROFESIONAL INTEGRAL</span>
                                            <span class="fw-bold d-block small text-success">FORMATO DE PLANEACIÓN, SEGUIMIENTO Y EVALUACIÓN DE ETAPA PRODUCTIVA</span>
                                            <span class="d-block text-muted small">CLASIFICACIÓN DE LA INFORMACIÓN: Pública ☒ | Pública Clasificada ☐</span>
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
                                                <th colspan="4" class="text-uppercase fw-bold">Información general</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td class="bg-light fw-bold col-2">Regional:</td>
                                                <td class="col-4">CUNDINAMARCA</td>
                                                <td class="bg-light fw-bold col-2">Centro de formación:</td>
                                                <td class="col-4">CENTRO DE DESARROLLO AGROEMPRESARIAL</td>
                                            </tr>
                                            <tr>
                                                <td class="bg-light fw-bold">Programa de formación:</td>
                                                <td><?= htmlspecialchars($ap['nom_programa'] ?? $ap['nompro'] ?? 'ANÁLISIS Y DESARROLLO DE SOFTWARE') ?></td>
                                                <td class="bg-light fw-bold">No. Grupo:</td>
                                                <td><strong><?= htmlspecialchars($idficha) ?></strong></td>
                                            </tr>
                                            <tr class="table-light">
                                                <td colspan="4" class="fw-bold text-primary">Datos del aprendiz</td>
                                            </tr>
                                            <tr>
                                                <td class="bg-light fw-bold">Nombre completo:</td>
                                                <td><strong><?= htmlspecialchars($nombreAprendiz) ?></strong></td>
                                                <td class="bg-light fw-bold">Tipo / N° Identificación:</td>
                                                <td><?= htmlspecialchars($ap['tipo_doc_aprendiz'] ?? 'CC') ?> - <?= htmlspecialchars($documentoAprendiz) ?></td>
                                            </tr>
                                            <tr>
                                                <td class="bg-light fw-bold">Contacto telefónico:</td>
                                                <td><?= htmlspecialchars($ap['tel_aprendiz'] ?? $ap['telusu'] ?? 'N/A') ?></td>
                                                <td class="bg-light fw-bold">Correo personal / inst:</td>
                                                <td><?= htmlspecialchars($ap['correo_aprendiz'] ?? $ap['corusu'] ?? 'N/A') ?> / <?= htmlspecialchars($ap['correo_inst_aprendiz'] ?? 'N/A') ?></td>
                                            </tr>
                                            <tr>
                                                <td class="bg-light fw-bold">Alternativa EP registrada:</td>
                                                <td><?= htmlspecialchars($ap['alternativa_ep'] ?? 'Contrato de Aprendizaje') ?></td>
                                                <td class="bg-light fw-bold">Fecha registro SofiaPlus:</td>
                                                <td><?= htmlspecialchars($ap['fecha_registro_sofia'] ?? date('d/m/Y')) ?></td>
                                            </tr>
                                            <tr class="table-light">
                                                <td colspan="4" class="fw-bold text-primary">Datos del instructor de seguimiento</td>
                                            </tr>
                                            <tr>
                                                <td class="bg-light fw-bold">Nombre:</td>
                                                <td>JUAN CAMILO MEDINA FORERO</td>
                                                <td class="bg-light fw-bold">Contacto telefónico:</td>
                                                <td>322 8638817</td>
                                            </tr>
                                            <tr>
                                                <td class="bg-light fw-bold">Correo institucional:</td>
                                                <td colspan="3">jcmedina@sena.edu.co</td>
                                            </tr>
                                            <tr class="table-light">
                                                <td colspan="4" class="fw-bold text-primary">Datos del ente co-formador (Jefe inmediato o tutor y empresa)</td>
                                            </tr>
                                            <tr>
                                                <td class="bg-light fw-bold">Empresa o Entidad:</td>
                                                <td><strong><?= htmlspecialchars($ap['nombre_empresa'] ?? 'Por definir') ?></strong> (NIT: <?= htmlspecialchars($ap['nit_empresa'] ?? 'Pendiente') ?>)</td>
                                                <td class="bg-light fw-bold">Dirección / Correo:</td>
                                                <td><?= htmlspecialchars($ap['direccion_empresa'] ?? 'N/A') ?> - <?= htmlspecialchars($ap['correo_empresa'] ?? 'N/A') ?></td>
                                            </tr>
                                            <tr>
                                                <td class="bg-light fw-bold">Jefe Inmediato / Cargo:</td>
                                                <td><?= htmlspecialchars($ap['nombre_jefe'] ?? 'N/A') ?> (<?= htmlspecialchars($ap['cargo_jefe'] ?? 'Tutor') ?>)</td>
                                                <td class="bg-light fw-bold">Contactos telefónicos:</td>
                                                <td>Tutor: <?= htmlspecialchars($ap['telefono_jefe'] ?? 'N/A') ?> | Inst: <?= htmlspecialchars($ap['telefono_institucional'] ?? 'N/A') ?></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- MOMENTO N° 1 - PLANEACIÓN -->
                                <div class="table-responsive mb-3">
                                    <table class="table table-bordered table-sm mb-0">
                                        <thead class="table-success">
                                            <tr>
                                                <th colspan="4" class="text-uppercase fw-bold">Momento N° 1 - Planeación de la Etapa Productiva</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td class="bg-light fw-bold col-3">Fecha inicio etapa productiva:</td>
                                                <td class="col-3"><?= date('d/m/Y', strtotime($fInicioModal)) ?></td>
                                                <td class="bg-light fw-bold col-3">Fecha fin etapa productiva:</td>
                                                <td class="col-3"><?= date('d/m/Y', strtotime($fInicioModal . ' +6 months')) ?></td>
                                            </tr>
                                            <tr>
                                                <td class="bg-light fw-bold">Fecha afiliación ARL:</td>
                                                <td><?= date('d/m/Y', strtotime($fInicioModal)) ?></td>
                                                <td class="bg-light fw-bold">Número de póliza ARL:</td>
                                                <td><?= htmlspecialchars($ap['num_poliza_arl'] ?? 'POL-ARL-99201') ?></td>
                                            </tr>
                                            <tr>
                                                <td class="bg-light fw-bold">Horario de trabajo:</td>
                                                <td colspan="3"><?= htmlspecialchars($ap['horario_trabajo'] ?? 'Diurno, Lunes a Viernes de 8:00 AM a 5:00 PM (44 horas semanales)') ?></td>
                                            </tr>
                                            <tr class="table-warning">
                                                <td class="fw-bold">Próxima cita Momento N° 2 (3 meses):</td>
                                                <td colspan="3"><strong><?= $fMomento2Modal ?></strong> (Fecha calculada legalmente 3 meses después del inicio)</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- CONCERTACIÓN PLAN DE TRABAJO -->
                                <div class="table-responsive mb-3">
                                    <table class="table table-bordered table-sm mb-0">
                                        <thead class="table-success">
                                            <tr>
                                                <th colspan="2" class="text-uppercase fw-bold">Concertación plan de trabajo durante la etapa productiva del aprendiz</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td class="bg-light fw-bold col-3">Competencias a desarrollar (Técnicas):</td>
                                                <td class="col-9">
                                                    <?= !empty($ap['competencias']) ? nl2br(htmlspecialchars($ap['competencias'])) : "220501096 - Desarrollar la solución de software de acuerdo con el diseño y requerimientos.<br>220501097 - Implantar la solución de software según la arquitectura y requerimientos del cliente." ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="bg-light fw-bold">Resultados de aprendizaje (RAPs):</td>
                                                <td>
                                                    <?= !empty($ap['raps']) ? nl2br(htmlspecialchars($ap['raps'])) : "1RAP: Construir componentes de software según especificaciones de diseño.<br>2RAP: Ejecutar pruebas unitarias e integrales del sistema." ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="bg-light fw-bold">Actividades a desarrollar:</td>
                                                <td>
                                                    <?= !empty($ap['actividades_lista']) ? nl2br(htmlspecialchars($ap['actividades_lista'])) : "1. Diseñar y codificar módulos web bajo estándares de calidad SENA.<br>2. Elaborar documentación técnica y manuales de usuario." ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="bg-light fw-bold">Evidencias de aprendizaje:</td>
                                                <td>
                                                    <?= !empty($ap['fechas_entrega']) ? nl2br(htmlspecialchars($ap['fechas_entrega'])) : "1. Bitácora mensual de actividades de desarrollo.<br>2. Informe técnico de avances de software.<br>3. Repositorio de código fuente con documentación." ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="bg-light fw-bold">Observaciones adicionales:</td>
                                                <td><?= !empty($ap['tareas_acordadas']) ? nl2br(htmlspecialchars($ap['tareas_acordadas'])) : '*El aprendiz debe presentar una bitácora mensual como lo establece el reglamento del aprendiz.' ?></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- FIRMAS TRIPARTITAS Y CIERRE -->
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm mb-0 text-center">
                                        <thead class="table-success">
                                            <tr>
                                                <th colspan="3" class="text-uppercase fw-bold">Firmas Tripartitas y Cierre</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td class="align-bottom p-3 col-4">
                                                    <div class="border-top border-dark pt-1">
                                                        <strong><?= htmlspecialchars($nombreAprendiz) ?></strong><br>
                                                        <small class="text-muted">Firma del Aprendiz</small><br>
                                                        <span class="badge <?= ($fAp === 'Firmado' ? 'bg-success' : 'bg-warning text-dark') ?>">
                                                            <?= ($fAp === 'Firmado' ? '✔ Firmado Digitalmente' : '⏳ Esperando Firma') ?>
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
                                                        <strong><?= htmlspecialchars($ap['nombre_jefe'] ?? 'Tutor Empresarial') ?></strong><br>
                                                        <small class="text-muted">Firma del Ente Co-formador</small><br>
                                                        <span class="badge <?= ($fEm === 'Firmado' ? 'bg-success' : 'bg-warning text-dark') ?>">
                                                            <?= ($fEm === 'Firmado' ? '✔ Firmado Digitalmente' : '⏳ Esperando Firma') ?>
                                                        </span>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                            </div>

                            <div class="modal-footer bg-light py-2">
                                <a href="views/etavcsi_pdf.php?<?= (!empty($idConciActual) ? 'idcon=' . $idConciActual : 'idaprendiz=' . $idAprendizActual . '&idficha=' . htmlspecialchars($idficha)) ?>" target="_blank" class="btn btn-danger shadow-sm">
                                    <i class="fa-solid fa-download me-1"></i> Descargar en PDF Oficial
                                </a>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                            </div>

                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    <?php endif; ?>

</div>