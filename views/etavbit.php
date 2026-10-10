<!-- 
  Formulario GFPI-F-147 V5 - Bitácora de Seguimiento de la Etapa Productiva
  Implementación MVC Integrada con SAGI SENA
-->
<main class="container-fluid py-4 bg-light" role="main">
    <div class="row justify-content-center">
        <div class="col-12 col-xl-10">
            <?php if (!empty($msj)): ?>
                <div class="alert alert-<?= (strpos($msj, 'correctamente') !== false || strpos($msj, 'éxito') !== false) ? 'success' : 'danger'; ?>" role="alert">
                    <?= htmlspecialchars($msj, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>

            <header class="text-center mb-4 pb-3 border-bottom">
                <h1 class="h3 fw-bold text-success" id="form-title">
                    <i class="<?= htmlspecialchars($icono ?? 'fa-solid fa-file-signature'); ?>" aria-hidden="true"></i>
                    Formato GFPI-F-147 V5: Bitácora de Seguimiento de la Etapa Productiva
                </h1>
                <p class="text-muted" id="form-description">Diligencie las actividades desarrolladas durante su etapa productiva conforme a los lineamientos del SENA.</p>
            </header>

            <form id="frmBitacora" action="home.php?pg=<?= htmlspecialchars($pg ?? '1534'); ?>" method="POST" enctype="multipart/form-data" aria-labelledby="form-title" class="needs-validation" novalidate>

                <!-- Campos ocultos de control -->
                <input type="hidden" name="opera" value="save">
                <input type="hidden" name="idbitacora" value="<?= htmlspecialchars($datOneBit['idbitacora'] ?? ''); ?>">
                <input type="hidden" name="idjefe" value="<?= htmlspecialchars($datJefes[0]['idusu'] ?? ''); ?>">
                <input type="hidden" name="idinstructor" value="<?= htmlspecialchars($datInst[0]['idusu'] ?? ''); ?>">

                <!-- SECCIÓN 1: INFORMACIÓN GENERAL -->
                <fieldset class="card mb-4 shadow-sm">
                    <legend class="card-header h5 bg-white text-success fw-bold p-3 mb-0">
                        1. Información General y Datos del Aprendiz
                    </legend>
                    <div class="card-body row g-3">
                        <div class="col-md-6 col-lg-4">
                            <label for="programa_formacion" class="form-label fw-semibold">Programa de Formación</label>
                            <input type="text" id="programa_formacion" name="programa_formacion" class="form-control bg-light" value="<?= htmlspecialchars($datPrograma[0]['nombre_programa'] ?? 'No asignado'); ?>" readonly>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <label for="numero_grupo" class="form-label fw-semibold">Número de Ficha</label>
                            <input type="text" id="numero_grupo" name="numero_grupo" class="form-control bg-light" value="<?= htmlspecialchars($datFicha[0]['idfic'] ?? 'No asignado'); ?>" readonly>
                        </div>
                        <div class="col-md-12 col-lg-4">
                            <label for="nombre_aprendiz" class="form-label fw-semibold">Nombre del Aprendiz</label>
                            <input type="text" id="nombre_aprendiz" name="nombre_aprendiz" class="form-control bg-light" value="<?= htmlspecialchars($datOne[0]['nomusu'] ?? ''); ?>" readonly>
                        </div>

                        <div class="col-md-4">
                            <label for="tipo_documento" class="form-label fw-semibold">Tipo de Documento</label>
                            <select id="tipo_documento" name="tipo_documento" class="form-select bg-light" disabled>
                                <option value="CC" selected>Cédula de Ciudadanía</option>
                                <option value="TI">Tarjeta de Identidad</option>
                                <option value="CE">Cédula de Extranjería</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="numero_id" class="form-label fw-semibold">Número de Documento</label>
                            <input type="text" id="numero_id" name="numero_id" class="form-control bg-light" value="<?= htmlspecialchars($datOne[0]['ndocusu'] ?? ''); ?>" readonly>
                        </div>
                        <div class="col-md-4">
                            <label for="telefono_aprendiz" class="form-label fw-semibold">Teléfono de Contacto</label>
                            <input type="tel" id="telefono_aprendiz" name="telefono_aprendiz" class="form-control bg-light" value="<?= htmlspecialchars($datOne[0]['telcan'] ?? ''); ?>" readonly>
                        </div>

                        <div class="col-md-6">
                            <label for="correo_institucional" class="form-label fw-semibold">Correo Institucional / Personal</label>
                            <input type="email" id="correo_institucional" name="correo_institucional" class="form-control bg-light" value="<?= htmlspecialchars($datOne[0]['emausu'] ?? ''); ?>" readonly>
                        </div>
                    </div>
                </fieldset>

                <!-- SECCIÓN 2: DATOS DE LA EMPRESA -->
                <fieldset class="card mb-4 shadow-sm">
                    <legend class="card-header h5 bg-white text-success fw-bold p-3 mb-0">
                        2. Datos de la Empresa / Organización y Período Reportado
                    </legend>
                    <div class="card-body row g-3">
                        <div class="col-md-8">
                            <label for="nombre_empresa" class="form-label fw-semibold">Razón Social de la Empresa <span class="text-danger">*</span></label>
                            <input type="text" id="nombre_empresa" name="nombre_empresa" class="form-control" value="<?= htmlspecialchars($datOneBit['nombre_empresa'] ?? ($datEmpresa['nomemp'] ?? '')); ?>" required placeholder="Ej. Tech Solutions SAS">
                            <div class="invalid-feedback">El nombre de la empresa es obligatorio.</div>
                        </div>
                        <div class="col-md-4">
                            <label for="nit" class="form-label fw-semibold">NIT <span class="text-danger">*</span></label>
                            <input type="text" id="nit" name="nit" class="form-control" value="<?= htmlspecialchars($datOneBit['nit'] ?? ($datEmpresa['numdocemp'] ?? '')); ?>" required placeholder="Ej. 900.123.456-7">
                            <div class="invalid-feedback">El NIT es obligatorio.</div>
                        </div>

                        <div class="col-md-3">
                            <label for="numero_bitacora_mostrar" class="form-label fw-semibold">Bitácora N°</label>
                            <?php if (!empty($datOneBit['numero_bitacora'])): ?>
                                <input type="text" id="numero_bitacora_mostrar" class="form-control bg-light text-center fw-bold" value="<?= htmlspecialchars($datOneBit['numero_bitacora']); ?>" readonly>
                                <input type="hidden" name="numero_bitacora" value="<?= htmlspecialchars($datOneBit['numero_bitacora']); ?>">
                            <?php elseif (!empty($proximoNumero)): ?>
                                <input type="text" id="numero_bitacora_mostrar" class="form-control bg-light text-center fw-bold" value="<?= htmlspecialchars($proximoNumero); ?>" readonly>
                                <input type="hidden" name="numero_bitacora" value="<?= htmlspecialchars($proximoNumero); ?>">
                            <?php else: ?>
                                <input type="text" id="numero_bitacora_mostrar" class="form-control is-invalid text-center fw-bold" value="Límite alcanzado" readonly>
                                <div class="invalid-feedback">Ha completado las 12 bitácoras requeridas.</div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-3">
                            <label for="fecha_inicio" class="form-label fw-semibold">Fecha de Inicio</label>
                            <input type="date" id="fecha_inicio" name="fecha_inicio" class="form-control bg-light" value="<?= htmlspecialchars($datOneBit['fecha_inicio'] ?? $fechaInicioValor); ?>" readonly>
                        </div>
                        <div class="col-md-3">
                            <label for="fecha_fin" class="form-label fw-semibold">Fecha de Finalización</label>
                            <input type="date" id="fecha_fin" name="fecha_fin" class="form-control bg-light" value="<?= htmlspecialchars($datOneBit['fecha_fin'] ?? $fechaFinValor); ?>" readonly>
                        </div>

                        <div class="col-md-3">
                            <label for="nvlarl" class="form-label fw-semibold">Nivel de Riesgo ARL <span class="text-danger">*</span></label>
                            <select id="nvlarl" name="nvlarl" class="form-select" required>
                                <option value="">Seleccione...</option>
                                <?php
                                $niveles = ['1', '2', '3', '4', '5'];
                                $valorActual = $datOneBit['nvlarl'] ?? '1';
                                foreach ($niveles as $nivel):
                                ?>
                                    <option value="<?= htmlspecialchars($nivel) ?>" <?= $nivel == $valorActual ? 'selected' : '' ?>>Riesgo Nivel <?= htmlspecialchars($nivel) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">Seleccione el nivel de ARL.</div>
                        </div>
                    </div>
                </fieldset>

                <!-- SECCIÓN 3: ENTE COFORMADOR -->
                <fieldset class="card mb-4 shadow-sm">
                    <legend class="card-header h5 bg-white text-success fw-bold p-3 mb-0">
                        3. Datos del Ente Coformador e Instructor de Seguimiento
                    </legend>
                    <div class="card-body row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary">Nombre del Jefe Inmediato</label>
                            <input type="text" class="form-control bg-light" value="<?= htmlspecialchars($datJefes[0]['nomusu'] ?? 'Pendiente por asignar'); ?>" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary">Cargo del Jefe Inmediato</label>
                            <input type="text" class="form-control bg-light" value="<?= htmlspecialchars($datCargo[0]['nomper'] ?? 'Pendiente'); ?>" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary">Correo Electrónico (Jefe)</label>
                            <input type="email" class="form-control bg-light" value="<?= htmlspecialchars($datJefes[0]['emausu'] ?? ''); ?>" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary">Teléfono de Contacto (Jefe)</label>
                            <input type="tel" class="form-control bg-light" value="<?= htmlspecialchars($datJefes[0]['telcan'] ?? ''); ?>" readonly>
                        </div>

                        <hr class="my-3 text-muted">

                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary">Nombre del Instructor Asignado</label>
                            <input type="text" class="form-control bg-light" value="<?= htmlspecialchars($datInst[0]['nomusu'] ?? 'Pendiente por asignar'); ?>" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary">Correo del Instructor</label>
                            <input type="email" class="form-control bg-light" value="<?= htmlspecialchars($datInst[0]['emausu'] ?? ''); ?>" readonly>
                        </div>
                    </div>
                </fieldset>

                <!-- SECCIÓN 4: ALTERNATIVA -->
                <fieldset class="card mb-4 shadow-sm border-success">
                    <legend class="card-header h5 bg-success text-white fw-bold p-3 mb-0">
                        4. Modalidad y Alternativa de Etapa Productiva
                    </legend>
                    <div class="card-body row g-3">
                        <div class="col-md-6">
                            <label for="idaltep" class="form-label fw-semibold">Alternativa Registrada <span class="text-danger">*</span></label>
                            <select id="idaltep" name="idaltep" class="form-select border-success" required>
                                <option value="">Seleccione la alternativa...</option>
                                <?php foreach ($datDominios as $dominio): ?>
                                    <option value="<?= htmlspecialchars($dominio['iddom']); ?>" <?= (!empty($datOneBit) && $datOneBit['idaltep'] == $dominio['iddom']) ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars($dominio['nomdom']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">Debe seleccionar una alternativa.</div>
                        </div>
                        <div class="col-md-6">
                            <label for="idsubaltep" class="form-label fw-semibold">Subalternativa <span class="text-danger">*</span></label>
                            <select id="idsubaltep" name="idsubaltep" class="form-select border-success" required>
                                <option value="">Seleccione la subalternativa...</option>
                                <?php foreach ($datValores as $valor): ?>
                                    <option value="<?= htmlspecialchars($valor['idval']); ?>" <?= (!empty($datOneBit) && $datOneBit['idsubaltep'] == $valor['idval']) ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars($valor['nomval']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">Debe seleccionar una subalternativa válida.</div>
                        </div>
                    </div>
                </fieldset>

                <!-- SECCIÓN 5: DESARROLLO DE ACTIVIDADES -->
                <fieldset class="card mb-4 shadow-sm">
                    <legend class="card-header h5 bg-white text-success fw-bold p-3 mb-0">
                        5. Planeación y Descripción de Actividades
                    </legend>
                    <div class="card-body">
                        <div class="alert alert-info mb-4" role="alert">
                            <i class="fa-solid fa-circle-info me-2"></i> Relacione las funciones desarrolladas durante la quincena.
                        </div>

                        <div id="actividades-container">
                            <?php $actividadesFormulario = $actividadesEditar ?? []; ?>
                            <?php $totalActividades = max(2, count($actividadesFormulario)); ?>
                            <?php for ($i = 0; $i < $totalActividades; $i++): ?>
                                <?php $actividadEditar = $actividadesFormulario[$i] ?? []; ?>
                                <div class="actividad-item border rounded p-3 mb-3 bg-light position-relative">
                                    <span class="position-absolute top-0 start-0 translate-middle badge rounded-pill bg-success border border-light">Act. <?= $i + 1 ?></span>
                                    <div class="row g-3 mt-1">
                                        <div class="col-md-4">
                                            <label for="fecha_inicio_act_<?= $i ?>" class="form-label fw-semibold">Inicio Actividad <span class="text-danger">*</span></label>
                                            <input type="date" id="fecha_inicio_act_<?= $i ?>" name="fecha_inicio_act[]" class="form-control" value="<?= htmlspecialchars($actividadEditar['fecha_inicio_act'] ?? '') ?>" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="fecha_fin_act_<?= $i ?>" class="form-label fw-semibold">Fin Actividad <span class="text-danger">*</span></label>
                                            <input type="date" id="fecha_fin_act_<?= $i ?>" name="fecha_fin_act[]" class="form-control" value="<?= htmlspecialchars($actividadEditar['fecha_fin_act'] ?? '') ?>" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="evidencia_act_<?= $i ?>" class="form-label fw-semibold">Evidencia <span class="text-danger">*</span></label>
                                            <input type="text" id="evidencia_act_<?= $i ?>" name="actividad_evidencia[]" class="form-control" placeholder="Ej. Repositorio GIT" value="<?= htmlspecialchars($actividadEditar['evidencia_cumplimiento'] ?? '') ?>" required>
                                        </div>
                                        <div class="col-12">
                                            <label for="desc_act_<?= $i ?>" class="form-label fw-semibold">Descripción Detallada <span class="text-danger">*</span></label>
                                            <textarea id="desc_act_<?= $i ?>" name="actividad_descripcion[]" class="form-control" rows="2" placeholder="Describa la función realizada..." required><?= htmlspecialchars($actividadEditar['descripcion_actividad'] ?? '') ?></textarea>
                                        </div>
                                    </div>
                                </div>
                            <?php endfor; ?>
                        </div>

                        <button type="button" class="btn btn-outline-success mt-2" onclick="agregarActividad()">
                            <i class="fa-solid fa-plus me-1"></i> Agregar otra actividad
                        </button>
                    </div>
                </fieldset>

                <!-- SECCIÓN 6: EVIDENCIAS Y OBSERVACIONES -->
                <fieldset class="card mb-4 shadow-sm">
                    <legend class="card-header h5 bg-white text-success fw-bold p-3 mb-0">
                        6. Soporte General y Observaciones
                    </legend>
                    <div class="card-body row g-3">
                        <div class="col-md-4">
                            <label for="tipo_evidencia" class="form-label fw-semibold">Formato de Evidencia Integral <span class="text-danger">*</span></label>
                            <select id="tipo_evidencia" name="tipo_evidencia" class="form-select" onchange="gestionarCamposEvidencia()" required>
                                <option value="">Seleccione el tipo...</option>
                                <option value="archivo" <?= (!empty($datOneBit['archivo_evidencia'])) ? 'selected' : ''; ?>>Cargar Documento Físico (PDF/ZIP)</option>
                                <option value="link" <?= (!empty($datOneBit['link_evidencia'])) ? 'selected' : ''; ?>>Enlace Repositorio / Carpeta Compartida</option>
                            </select>
                        </div>

                        <div class="col-md-8" id="bloque_archivo" style="display:none;">
                            <label for="archivo_evidencia" class="form-label fw-semibold">Soporte Documental</label>
                            <input type="file" id="archivo_evidencia" name="archivo_evidencia" class="form-control" accept=".pdf,.zip,.rar,.png,.jpg,.jpeg">
                            <div class="form-text">Tamaño máximo permitido: 10MB.</div>
                        </div>

                        <div class="col-md-8" id="bloque_link" style="display:none;">
                            <label for="link_evidencia" class="form-label fw-semibold">URL de la Evidencia</label>
                            <input type="url" id="link_evidencia" name="link_evidencia" class="form-control" placeholder="https://drive.google.com/..." value="<?= htmlspecialchars($datOneBit['link_evidencia'] ?? ''); ?>">
                        </div>

                        <div class="col-12">
                            <label for="observacion" class="form-label fw-semibold">Observación del aprendiz</label>
                            <textarea id="observacion" name="observacion" class="form-control" rows="3" placeholder="Agregue una observación opcional..."><?= htmlspecialchars($datOneBit['observación'] ?? ($datOneBit['observacion'] ?? 'Sin observación')); ?></textarea>
                        </div>
                    </div>
                </fieldset>

                <!-- ACCIONES DEL FORMULARIO -->
                <div class="d-flex justify-content-end gap-3 border-top pt-4">
                    <a href="home.php?pg=<?= htmlspecialchars($pg ?? '1534'); ?>" class="btn btn-secondary px-4">Cancelar</a>
                    <button type="submit" class="btn btn-success px-5 fw-bold">
                        <i class="fa-solid fa-floppy-disk me-2"></i>
                        <?= !empty($datOneBit) ? "Actualizar Bitácora" : "Radicar Bitácora Oficial"; ?>
                    </button>
                </div>
            </form>

            <!-- HISTÓRICO DE BITÁCORAS -->
            <section class="mt-5 pt-3">
                <h2 class="h4 border-bottom pb-2 mb-4 text-success">Histórico de Bitácoras Registradas</h2>

                <?php if (!empty($bitacorasAMostrar)): ?>
                    <div class="table-responsive shadow-sm rounded">
                        <table id="tablaBitacoras" class="table table-hover align-middle mb-0 bg-white">
                            <thead class="table-success">
                                <tr>
                                    <th class="text-center" style="width: 5%;">N°</th>
                                    <th style="width: 25%;">Período Evaluado</th>
                                    <th style="width: 25%;">Empresa Receptora</th>
                                    <th class="text-center" style="width: 15%;">Estado</th>
                                    <th class="text-center" style="width: 30%;">Acciones del Formato</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($bitacorasAMostrar as $bit): ?>
                                    <tr>
                                        <td class="text-center fw-bold text-secondary">#<?= htmlspecialchars($bit['numero_bitacora']) ?></td>

                                        <td>
                                            <span class="d-block fw-semibold text-dark">
                                                <i class="fa-regular fa-calendar me-1"></i> <?= htmlspecialchars($bit['fecha_inicio']) ?> a <?= htmlspecialchars($bit['fecha_fin']) ?>
                                            </span>
                                            <small class="text-muted d-block mt-1">Radicado: <?= htmlspecialchars($bit['fecha_entrega']) ?></small>
                                        </td>

                                        <td>
                                            <span class="fw-semibold d-block"><?= htmlspecialchars($bit['nombre_empresa']) ?></span>
                                            <small class="text-muted">NIT: <?= htmlspecialchars($bit['nit']) ?></small>
                                        </td>

                                        <td class="text-center">
                                            <?php
                                            $estado = trim($bit['estado'] ?? 'Pendiente');
                                            if (strcasecmp($estado, 'Aprobado') === 0) {
                                                echo '<span class="badge bg-success rounded-pill px-3 py-2"><i class="fa-solid fa-check me-1"></i> Aprobado</span>';
                                            } elseif (strcasecmp($estado, 'Rechazado') === 0) {
                                                echo '<span class="badge bg-danger rounded-pill px-3 py-2"><i class="fa-solid fa-xmark me-1"></i> Rechazado</span>';
                                            } else {
                                                echo '<span class="badge bg-warning text-dark rounded-pill px-3 py-2"><i class="fa-solid fa-clock me-1"></i> Pendiente</span>';
                                            }
                                            ?>
                                        </td>

                                        <!-- Celdas de Acciones con estilos forzados !important -->
                                        <td class="text-center" style="vertical-align: middle !important; overflow: visible !important;">
                                            <div style="display: flex !important; justify-content: center !important; gap: 4px !important; flex-wrap: wrap !important; visibility: visible !important; opacity: 1 !important; position: relative !important; z-index: 1 !important;">
                                                <!-- 1. Ver PDF -->
                                                <a href="views/pdfbit.php?idusu=<?= urlencode($_SESSION['idusu'] ?? $bit['idaprendiz'] ?? ''); ?>&idbitacora=<?= urlencode($bit['idbitacora']); ?>&pdf=ok"
                                                    target="_blank"
                                                    class="btn btn-sm btn-danger text-white fw-bold"
                                                    style="display: inline-block !important; visibility: visible !important; opacity: 1 !important; position: static !important; top: auto !important; background-color: #dc3545 !important; border-color: #dc3545 !important; color: #ffffff !important; pointer-events: auto !important; z-index: 2 !important;"
                                                    title="Ver PDF GFPI-F-147">
                                                    <i class="fa-solid fa-file-pdf me-1"></i> PDF
                                                </a>

                                                <!-- 2. Ver Actividades -->
                                                <button type="button"
                                                    class="btn btn-sm btn-primary text-white fw-bold"
                                                    style="display: inline-block !important; visibility: visible !important; opacity: 1 !important; position: static !important; top: auto !important; background-color: #0d6efd !important; border-color: #0d6efd !important; color: #ffffff !important; pointer-events: auto !important; z-index: 2 !important;"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#bitDet<?= (int)$bit['idbitacora']; ?>"
                                                    title="Ver Actividades">
                                                    <i class="fa-solid fa-list-check me-1"></i> Actividades
                                                </button>

                                                <!-- 3. Ver Observación -->
                                                <button type="button"
                                                    class="btn btn-sm btn-info text-white fw-bold"
                                                    style="display: inline-block !important; visibility: visible !important; opacity: 1 !important; position: static !important; top: auto !important; background-color: #0dcaf0 !important; border-color: #0dcaf0 !important; color: #ffffff !important; pointer-events: auto !important; z-index: 2 !important;"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#modalObs<?= (int)$bit['idbitacora']; ?>"
                                                    title="Ver Observaciones">
                                                    <i class="fa-solid fa-comment-dots me-1"></i> Obs
                                                </button>

                                                <!-- 4. Editar (Solo Pendientes) -->
                                                <?php if (empty($estado) || strcasecmp($estado, 'Pendiente') === 0): ?>
                                                    <a href="home.php?pg=<?= urlencode($pg ?? '1534'); ?>&idbitacora=<?= urlencode($bit['idbitacora']); ?>&opera=edit"
                                                        class="btn btn-sm btn-warning text-dark fw-bold"
                                                        style="display: inline-block !important; visibility: visible !important; opacity: 1 !important; position: static !important; top: auto !important; background-color: #ffc107 !important; border-color: #ffc107 !important; color: #000000 !important; pointer-events: auto !important; z-index: 2 !important;"
                                                        title="Editar Radicación">
                                                        <i class="fa-solid fa-pen me-1"></i> Editar
                                                    </a>

                                                    <!-- 5. Eliminar (Solo Pendientes) -->
                                                    <form method="POST" action="home.php?pg=<?= urlencode($pg ?? '1534'); ?>" class="d-inline" onsubmit="return confirm('¿Está seguro de eliminar la bitácora #<?= htmlspecialchars($bit['numero_bitacora']); ?>?');">
                                                        <input type="hidden" name="opera" value="Eliminar">
                                                        <input type="hidden" name="idbitacora" value="<?= htmlspecialchars($bit['idbitacora']); ?>">
                                                        <button type="submit"
                                                            class="btn btn-sm btn-secondary text-white fw-bold"
                                                            style="display: inline-block !important; visibility: visible !important; opacity: 1 !important; position: static !important; top: auto !important; background-color: #6c757d !important; border-color: #6c757d !important; color: #ffffff !important; pointer-events: auto !important; z-index: 2 !important;"
                                                            title="Eliminar Registro">
                                                            <i class="fa-solid fa-trash me-1"></i> Eliminar
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Modales renderizados de forma independiente al final de la tabla -->
                    <?php foreach ($bitacorasAMostrar as $bit): ?>
                        <?php
                        if (function_exists('modalBitacoraDetalles')) {
                            echo modalBitacoraDetalles($bit['idbitacora'], $bit['numero_bitacora'], $pg ?? '1534');
                        }
                        if (function_exists('modalVerObservacion')) {
                            echo modalVerObservacion($bit['idbitacora'], $bit['numero_bitacora'], $bit['observacion'] ?? 'Sin observación');
                        }
                        ?>
                    <?php endforeach; ?>

                <?php else: ?>
                    <div class="alert alert-secondary text-center py-4 rounded shadow-sm">
                        <i class="fa-solid fa-folder-open fa-3x text-muted mb-3 d-block"></i>
                        No posee bitácoras registradas en la plataforma.
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </div>
</main>

<script>
    const gFpiConfig = {
        fechaInicioLimit: '<?= htmlspecialchars($fechaInicioPeriodo ?? '') ?>',
        fechaFinLimit: '<?= htmlspecialchars($fechaFinPeriodo ?? '') ?>',
        actividadCount: <?= count($actividadesFormulario ?? []); ?> || 2
    };

    function gestionarCamposEvidencia() {
        const tipo = document.getElementById('tipo_evidencia').value;
        const bArchivo = document.getElementById('bloque_archivo');
        const bLink = document.getElementById('bloque_link');

        bArchivo.style.display = (tipo === 'archivo') ? 'block' : 'none';
        bLink.style.display = (tipo === 'link') ? 'block' : 'none';

        document.getElementById('archivo_evidencia').required = (tipo === 'archivo' && !<?= !empty($datOneBit['archivo_evidencia']) ? 'true' : 'false' ?>);
        document.getElementById('link_evidencia').required = (tipo === 'link');
    }

    function agregarActividad() {
        const container = document.getElementById('actividades-container');
        const idx = gFpiConfig.actividadCount;

        const wrapper = document.createElement('div');
        wrapper.className = 'actividad-item border rounded p-3 mb-3 bg-light position-relative';
        wrapper.innerHTML = `
            <span class="position-absolute top-0 start-0 translate-middle badge rounded-pill bg-success border border-light">Act. ${idx + 1}</span>
            <button type="button" class="btn-close position-absolute top-0 end-0 m-2" onclick="this.closest('.actividad-item').remove()"></button>
            <div class="row g-3 mt-1">
                <div class="col-md-4">
                    <label for="fecha_inicio_act_${idx}" class="form-label fw-semibold">Inicio Actividad <span class="text-danger">*</span></label>
                    <input type="date" id="fecha_inicio_act_${idx}" name="fecha_inicio_act[]" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label for="fecha_fin_act_${idx}" class="form-label fw-semibold">Fin Actividad <span class="text-danger">*</span></label>
                    <input type="date" id="fecha_fin_act_${idx}" name="fecha_fin_act[]" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label for="evidencia_act_${idx}" class="form-label fw-semibold">Evidencia <span class="text-danger">*</span></label>
                    <input type="text" id="evidencia_act_${idx}" name="actividad_evidencia[]" class="form-control" placeholder="Ej. Repositorio GIT" required>
                </div>
                <div class="col-12">
                    <label for="desc_act_${idx}" class="form-label fw-semibold">Descripción Detallada <span class="text-danger">*</span></label>
                    <textarea id="desc_act_${idx}" name="actividad_descripcion[]" class="form-control" rows="2" placeholder="Describa la función realizada..." required></textarea>
                </div>
            </div>
        `;

        container.appendChild(wrapper);
        gFpiConfig.actividadCount++;
    }

    document.addEventListener('DOMContentLoaded', function() {
        const selectEvidencia = document.getElementById('tipo_evidencia');
        if (selectEvidencia && selectEvidencia.value !== '') {
            gestionarCamposEvidencia();
        }

        var forms = document.querySelectorAll('.needs-validation');
        Array.prototype.slice.call(forms).forEach(function(form) {
            form.addEventListener('submit', function(event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    });
</script>