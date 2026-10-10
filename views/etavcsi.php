<?php
    require_once("controllers/etaccsi.php");

    // Determinación del Momento Evaluativo Seleccionado
    // 301 / Vacío = Momento 1 | 302 = Momento 2 | 303 = Momento 3
    $momentoActual = (($tipo_formato ?? '') == 302 ? '2' : (($tipo_formato ?? '') == 303 ? '3' : '1'));
    // IMPORTANTE: $vistaMomentoActual debe ser el idpag numérico registrado en tabla `pagina`
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
            <input type="hidden" name="pg" value="<?= htmlspecialchars($pg ?? 'etavcsi') ?>">
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

            <!-- Botón Consultar -->
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
                <a href="home.php?pg=<?= htmlspecialchars($pg ?? 'etavcsi') ?>" class="btn btn-sm btn-outline-secondary">
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

    <!-- 4. TABLA DE APRENDICES DE LA FICHA -->
    <?php if (!empty($idficha)): ?>
        <div class="mt-4">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <h5 class="mb-0 text-secondary d-flex align-items-center">
                    <i class="fa-solid fa-file-lines me-2 text-success"></i>
                    Listado de Aprendices - Ficha <?= htmlspecialchars($idficha) ?>
                </h5>
                <span class="badge bg-light border text-primary">
                    Mostrando Momento Evaluativo N° <?= $momentoActual ?>
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

                                                <a href="home.php?pg=<?= htmlspecialchars($pg ?? 'etavcsi') ?>&opera=alternar_firma&idcon=<?= $idConciActual ?>&rol=aprendiz&idficha=<?= htmlspecialchars($idficha) ?>&tipo_formato=<?= htmlspecialchars($tipo_formato ?? '') ?>" 
                                                   class="badge <?= ($fAp === 'Firmado' ? 'bg-success' : 'bg-warning text-dark') ?> text-decoration-none w-100 py-1" 
                                                   title="Clic para alternar firma del aprendiz">
                                                    <?= ($fAp === 'Firmado' ? '<i class="fa-solid fa-check me-1"></i>' : '<i class="fa-solid fa-clock me-1"></i>') ?> Aprendiz: <?= $fAp ?>
                                                </a>

                                                <a href="home.php?pg=<?= htmlspecialchars($pg ?? 'etavcsi') ?>&opera=alternar_firma&idcon=<?= $idConciActual ?>&rol=empresa&idficha=<?= htmlspecialchars($idficha) ?>&tipo_formato=<?= htmlspecialchars($tipo_formato ?? '') ?>" 
                                                   class="badge <?= ($fEm === 'Firmado' ? 'bg-success' : 'bg-warning text-dark') ?> text-decoration-none w-100 py-1" 
                                                   title="Clic para alternar firma de la empresa">
                                                    <?= ($fEm === 'Firmado' ? '<i class="fa-solid fa-check me-1"></i>' : '<i class="fa-solid fa-clock me-1"></i>') ?> Empresa: <?= $fEm ?>
                                                </a>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted small fst-italic">Requiere diligenciar formato</span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- ACCIONES: REDIRECCIÓN DILIGENCIAR Y EDITAR A VISTAS INDEPENDIENTES -->
                                    <!-- Celda de Acciones en views/etavcsi.php -->
                                    <td class="text-end align-middle">
                                        <?php if ($tieneFormato): ?>
                                            <!-- Editar Formato Diligenciado -->
                                            <a href="home.php?pg=<?= $pgMomento ?>&opera=edi&idcon=<?= $idConciActual ?>&idficha=<?= htmlspecialchars($idficha) ?>&tipo_formato=<?= htmlspecialchars($tipo_formato ?? '301') ?>" 
                                            title="Editar Momento <?= $momentoActual ?>" 
                                            class="text-decoration-none me-2">
                                                <i class="fa-solid fa-file-pen fa-2x text-success"></i>
                                            </a>
                                        <?php else: ?>
                                            <!-- Diligenciar Formato Inicial -->
                                            <a href="home.php?pg=<?= $pgMomento ?>&opera=diligenciar&idaprendiz=<?= $idAprendizActual ?>&idficha=<?= htmlspecialchars($idficha) ?>&tipo_formato=<?= htmlspecialchars($tipo_formato ?? '301') ?>" 
                                            title="Diligenciar Momento <?= $momentoActual ?>" 
                                            class="text-decoration-none me-2">
                                                <i class="fa-solid fa-file-signature fa-2x text-success"></i>
                                            </a>
                                        <?php endif; ?>

                                        <!-- Modal Ver SENA -->
                                        <i class="fa-solid fa-eye fa-2x text-success me-2" 
                                        role="button"
                                        title="Ver Formato Diligenciado GFPI-F-023" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#modalVerSena<?= $idAprendizActual ?>"></i>

                                        <!-- Descarga PDF -->
                                        <a href="views/etavcsi_pdf.php?<?= (!empty($idConciActual) ? 'idcon=' . $idConciActual : 'idaprendiz=' . $idAprendizActual . '&idficha=' . htmlspecialchars($idficha)) ?>" 
                                        target="_blank" 
                                        title="Ver / Descargar formato PDF" 
                                        class="text-decoration-none">
                                            <i class="fa-solid fa-file-pdf fa-2x text-success"></i>
                                        </a>
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

        <!-- MODALES DE VISTA PREVIA DEL FORMATO OFICIAL GFPI-F-023 -->
        <?php if (!empty($aprendicesFicha)): ?>
            <?php foreach ($aprendicesFicha as $ap): ?>
                <?php 
                    $idAprendizActual = $ap['idaprendiz'] ?? $ap['idusu'] ?? null;
                    $idConciActual = $ap['idcon'] ?? $ap['idconci'] ?? null;
                    $nombreAprendiz = $ap['nom_aprendiz'] ?? (trim(($ap['nomusu'] ?? '') . ' ' . ($ap['apeusu'] ?? '')));
                    $documentoAprendiz = $ap['doc_aprendiz'] ?? $ap['ndocusu'] ?? $ap['docusu'] ?? '';
                    $fAp = $ap['firma_aprendiz'] ?? $ap['fir_aprendiz'] ?? 'Pendiente';
                    $fEm = $ap['firma_empresa'] ?? $ap['fir_empresa'] ?? 'Pendiente';
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
                                <!-- Encabezado SENA -->
                                <div class="border border-dark p-2 mb-3 bg-light">
                                    <div class="row align-items-center text-center">
                                        <div class="col-md-2 border-end border-dark">
                                            <img src="img/logoSena.png" alt="SENA" class="img-fluid">
                                        </div>
                                        <div class="col-md-7 border-end border-dark">
                                            <span class="fw-bold d-block small">SERVICIO NACIONAL DE APRENDIZAJE SENA</span>
                                            <span class="fw-bold d-block small">GESTIÓN DE FORMACIÓN PROFESIONAL INTEGRAL</span>
                                            <span class="fw-bold d-block small">PROCEDIMIENTO DESARROLLO CURRICULAR</span>
                                            <span class="fw-bold text-success d-block small">FORMATO EVALUACIÓN Y SEGUIMIENTO ETAPA PRODUCTIVA</span>
                                        </div>
                                        <div class="col-md-3 text-start small">
                                            <strong>Código:</strong> GFPI-F-023<br>
                                            <strong>Versión:</strong> 03<br>
                                            <strong>Fecha:</strong> <?= date('d/m/Y') ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- 1. Información General -->
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
                                                <td class="bg-light fw-bold">Programa Formación:</td>
                                                <td><?= htmlspecialchars($ap['nom_programa'] ?? $ap['nompro'] ?? 'N/A') ?></td>
                                                <td class="bg-light fw-bold">No. de Ficha:</td>
                                                <td><strong><?= htmlspecialchars($idficha) ?></strong></td>
                                            </tr>
                                            <tr class="table-light">
                                                <td colspan="4" class="fw-bold text-primary">Datos del Aprendiz</td>
                                            </tr>
                                            <tr>
                                                <td class="bg-light fw-bold">Nombre Completo:</td>
                                                <td><strong><?= htmlspecialchars($nombreAprendiz) ?></strong></td>
                                                <td class="bg-light fw-bold">No. Documento:</td>
                                                <td><?= htmlspecialchars($documentoAprendiz) ?></td>
                                            </tr>
                                            <tr>
                                                <td class="bg-light fw-bold">Teléfono:</td>
                                                <td><?= htmlspecialchars($ap['tel_aprendiz'] ?? $ap['telusu'] ?? 'N/A') ?></td>
                                                <td class="bg-light fw-bold">Correo Electrónico:</td>
                                                <td><?= htmlspecialchars($ap['correo_aprendiz'] ?? $ap['corusu'] ?? 'N/A') ?></td>
                                            </tr>
                                            <tr class="table-light">
                                                <td colspan="4" class="fw-bold text-primary">Datos del Ente Coformador (Empresa)</td>
                                            </tr>
                                            <tr>
                                                <td class="bg-light fw-bold">Razón Social:</td>
                                                <td><strong><?= htmlspecialchars($ap['nombre_empresa'] ?? 'Por definir en concertación') ?></strong></td>
                                                <td class="bg-light fw-bold">NIT:</td>
                                                <td><?= htmlspecialchars($ap['nit_empresa'] ?? 'Pendiente') ?></td>
                                            </tr>
                                            <tr>
                                                <td class="bg-light fw-bold">Dirección Empresa:</td>
                                                <td><?= htmlspecialchars($ap['direccion_empresa'] ?? 'Pendiente') ?></td>
                                                <td class="bg-light fw-bold">Modalidad:</td>
                                                <td><?= htmlspecialchars($ap['modalidad_reunion'] ?? $ap['modalidad'] ?? 'Presencial') ?></td>
                                            </tr>
                                            <tr>
                                                <td class="bg-light fw-bold">Jefe Inmediato:</td>
                                                <td><?= htmlspecialchars($ap['nombre_jefe'] ?? 'Pendiente') ?> (<?= htmlspecialchars($ap['cargo_jefe'] ?? 'Tutor') ?>)</td>
                                                <td class="bg-light fw-bold">Contacto Jefe:</td>
                                                <td><?= htmlspecialchars($ap['telefono_jefe'] ?? 'Pendiente') ?> | <?= htmlspecialchars($ap['correo_empresa'] ?? 'Pendiente') ?></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- 2. Planeación de Actividades -->
                                <div class="table-responsive mb-3">
                                    <table class="table table-bordered table-sm mb-0">
                                        <thead class="table-success">
                                            <tr>
                                                <th colspan="3" class="text-uppercase fw-bold">2. Planeación de Etapa Productiva (Concertación de Actividades)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr class="table-light text-center small">
                                                <th class="col-6">Actividades a Desarrollar (¿Qué y Cómo?)</th>
                                                <th class="col-4">Evidencias y Fechas de Entrega (¿Cuándo?)</th>
                                                <th class="col-2">Lugar</th>
                                            </tr>
                                            <tr>
                                                <td class="p-3 align-top">
                                                    <?= !empty($ap['actividades_lista']) ? nl2br(htmlspecialchars($ap['actividades_lista'])) : (!empty($ap['tareas_acordadas']) ? nl2br(htmlspecialchars($ap['tareas_acordadas'])) : '<span class="text-muted fst-italic">Pendiente por concertar.</span>') ?>
                                                </td>
                                                <td class="p-3 align-top">
                                                    <?= !empty($ap['fechas_entrega']) ? nl2br(htmlspecialchars($ap['fechas_entrega'])) : '<span class="text-muted fst-italic">Entrega de bitácoras de seguimiento.</span>' ?>
                                                </td>
                                                <td class="p-3 align-top text-center">
                                                    <?= htmlspecialchars($ap['direccion_empresa'] ?? 'Instalaciones de la empresa') ?><br>
                                                    <small class="text-muted">(<?= htmlspecialchars($ap['modalidad_reunion'] ?? $ap['modalidad'] ?? 'Presencial') ?>)</small>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- 3. Firmas Tripartitas -->
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm mb-0 text-center">
                                        <thead class="table-success">
                                            <tr>
                                                <th colspan="3" class="text-uppercase fw-bold">3. Firmas Tripartitas del Acuerdo</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td class="align-bottom p-3 col-4">
                                                    <div class="border-top border-dark pt-1">
                                                        <strong><?= htmlspecialchars($nombreAprendiz) ?></strong><br>
                                                        <small class="text-muted">Firma del Aprendiz</small><br>
                                                        <span class="badge <?= ($fAp === 'Firmado' ? 'bg-success' : 'bg-warning text-dark') ?>">
                                                            <?= ($fAp === 'Firmado' ? '✔ Firmado Digitalmente' : '⏳ Pendiente de Firma') ?>
                                                        </span>
                                                    </div>
                                                </td>
                                                <td class="align-bottom p-3 col-4">
                                                    <div class="border-top border-dark pt-1">
                                                        <strong><?= htmlspecialchars($ap['nombre_jefe'] ?? 'Tutor Empresarial') ?></strong><br>
                                                        <small class="text-muted">Firma Ente Coformador (Empresa)</small><br>
                                                        <span class="badge <?= ($fEm === 'Firmado' ? 'bg-success' : 'bg-warning text-dark') ?>">
                                                            <?= ($fEm === 'Firmado' ? '✔ Firmado Digitalmente' : '⏳ Pendiente de Firma') ?>
                                                        </span>
                                                    </div>
                                                </td>
                                                <td class="align-bottom p-3 col-4">
                                                    <div class="border-top border-dark pt-1">
                                                        <strong><?= htmlspecialchars($ap['nom_instructor'] ?? 'JUAN CAMILO MEDINA FORERO') ?></strong><br>
                                                        <small class="text-muted">Firma Instructor SENA</small><br>
                                                        <span class="badge bg-success">
                                                            ✔ Firmado Digitalmente
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