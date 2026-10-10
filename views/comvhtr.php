<?php
// ==========================================
// Controlador principal de la Hoja de Trabajo
// ==========================================
require_once("controllers/comchtr.php");
?>

<?php
// Listas separadas por estado para las pestañas (Activas / Inactivas)
$datAct = [];
$datInact = [];
if (isset($datAll) && is_array($datAll)) {
    foreach ($datAll as $ht) {
        if (isset($ht['act']) && $ht['act'] == 1) {
            $datAct[] = $ht;
        } else {
            $datInact[] = $ht;
        }
    }
}
$totAct = count($datAct);
$totInact = count($datInact);
?>

<?php
// Tarjeta de Hoja de Trabajo con la misma estructura que Página (convpag.php).
// Solo clases Bootstrap + estilos en línea ya usados en Página. Sin CSS nuevo.
// Conserva todos los datos y botones: jornada, cupo, empresa, programa,
// fechas, prog. especial, ficha, cód. empresa, convenio,
// accesos rápidos (Horario, Instructores, Notas, Bloqueo) y
// acciones (Imprimir, Editar, Activar/Desactivar, Eliminar).
if (!function_exists('tarjetaHojaTrabajo')) {
    function tarjetaHojaTrabajo($dta, $pg, $esActiva) {
        $activa = (bool)$esActiva;
        ob_start();
?>
        <!-- Tarjeta de Hoja de Trabajo (estructura Página) -->
        <div class="card h-100 shadow-sm border-0 vid-card">

            <!-- Cuerpo de la Tarjeta -->
            <div class="card-body p-3 d-flex flex-column justify-content-between">
                <div>
                    <!-- Icono principal + título + estado -->
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <div class="d-flex align-items-center justify-content-center rounded-3 flex-shrink-0" style="width:38px;height:38px;background:#f1f5f9;">
                            <i class="fa-solid fa-file-lines <?= $activa ? 'text-success' : 'text-secondary'; ?>"></i>
                        </div>
                        <h6 class="fw-bold text-dark mb-0 text-truncate flex-grow-1" title="Hoja No. <?= $dta["idnorad"]; ?> - <?= htmlspecialchars($dta["nompro"]); ?>" style="font-size: 1.05rem;min-width:0;">
                            <span class="text-primary">#<?= $dta["idnorad"]; ?></span> - <?= htmlspecialchars($dta["nompro"]); ?>
                        </h6>
                        <?php if ($activa) { ?>
                            <span class="badge bg-success text-nowrap flex-shrink-0" title="Hoja activa"><i class="fa-solid fa-circle-check me-1"></i>Activa</span>
                        <?php } else { ?>
                            <span class="badge bg-danger text-nowrap flex-shrink-0" title="Hoja inactiva"><i class="fa-solid fa-circle-xmark me-1"></i>Inactiva</span>
                        <?php } ?>
                    </div>

                    <!-- Caja Empresa / Programa (igual a Módulo / Ruta de Página) -->
                    <div class="p-2 mb-3 rounded-3 bg-light border border-light-subtle d-flex justify-content-between align-items-center gap-2" style="font-size: 0.8rem;">
                        <div class="flex-fill" style="min-width:0;">
                            <span class="text-muted d-block" style="font-size: 0.72rem;">Empresa</span>
                            <span class="fw-bold text-success text-truncate d-block" title="<?= htmlspecialchars($dta["nomemp"]); ?>">
                                <i class="fa-solid fa-briefcase me-1"></i><?= htmlspecialchars($dta["nomemp"]); ?>
                            </span>
                        </div>
                        <div class="text-muted flex-shrink-0"><i class="fa-solid fa-arrow-right-long mx-1"></i></div>
                        <div class="text-end flex-fill" style="min-width:0;max-width:45%;">
                            <span class="text-muted d-block" style="font-size: 0.72rem;">Programa</span>
                            <span class="fw-bold text-primary text-truncate d-block" title="<?= htmlspecialchars($dta["codpro"]); ?>">
                                <i class="fa-solid fa-cube me-1"></i><?= htmlspecialchars($dta["codpro"]); ?>
                            </span>
                        </div>
                    </div>

                    <!-- Metadatos Jornada / Cupo (igual a Orden / Icono de Página) -->
                    <div class="d-flex flex-wrap justify-content-between gap-1 text-muted mb-1" style="font-size: 0.78rem;">
                        <span class="text-break"><i class="fa-regular fa-clock text-warning me-1"></i> <strong>Jornada:</strong> <?= htmlspecialchars($dta["jorn"]); ?></span>
                        <span><i class="fa-solid fa-users text-primary me-1"></i> <strong>Cupo:</strong> <?= $dta["cupo"]; ?></span>
                    </div>

                    <!-- Caja Vigencia Inicio / Cierre (misma caja de Página) -->
                    <div class="p-2 mb-2 rounded-3 bg-light border border-light-subtle d-flex justify-content-between align-items-center gap-2" style="font-size: 0.8rem;">
                        <div class="flex-fill" style="min-width:0;">
                            <span class="text-muted d-block" style="font-size: 0.72rem;">Inicio</span>
                            <span class="fw-bold <?= $activa ? 'text-success' : 'text-secondary'; ?> text-nowrap">
                                <i class="fa-regular fa-calendar-check me-1"></i><?= $dta["feclini"]; ?>
                            </span>
                        </div>
                        <div class="text-muted flex-shrink-0"><i class="fa-solid fa-arrow-right-long mx-1"></i></div>
                        <div class="text-end flex-fill" style="min-width:0;">
                            <span class="text-muted d-block" style="font-size: 0.72rem;">Cierre</span>
                            <span class="fw-bold text-danger text-nowrap">
                                <i class="fa-regular fa-calendar-xmark me-1"></i><?= $dta["feclin"]; ?>
                            </span>
                        </div>
                    </div>

                    <!-- Detalles (misma caja de descripción de Página) -->
                    <div class="text-muted mt-2 p-2 rounded bg-white border d-flex flex-column gap-1" style="font-size: 0.78rem;">
                        <span class="text-break"><i class="fa-solid fa-list-check text-info me-1"></i> <strong>Prog. Esp.:</strong> <?= htmlspecialchars($dta["proesp"]); ?></span>
                        <span><i class="fa-solid fa-id-card text-secondary me-1"></i> <strong>Ficha:</strong> <?= !empty($dta["idfic"]) ? $dta["idfic"] : 'N/D'; ?></span>
                        <span class="text-break"><i class="fa-solid fa-hashtag text-secondary me-1"></i> <strong>Cód. Emp.:</strong> <?= !empty($dta["codslem"]) ? htmlspecialchars($dta["codslem"]) : 'N/D'; ?></span>
                        <?php if (!empty($dta["convht"])): ?>
                            <span class="text-break"><i class="fa-solid fa-handshake text-secondary me-1"></i> <strong>Convenio:</strong> <?= htmlspecialchars($dta["convht"]); ?></span>
                        <?php endif; ?>
                    </div>

                    <!-- Accesos Rápidos: solo Bootstrap row-cols (2 por fila en móvil, 4 en desktop) -->
                    <div class="row row-cols-2 row-cols-sm-4 g-1 mt-2 pt-2 border-top">
                        <div class="col">
                            <a href="home.php?pg=2002&idnorad=<?= $dta["idnorad"] ?>" class="btn btn-sm btn-light border w-100 text-truncate text-primary" style="font-size: 0.78rem;" title="Horario Complementario" data-bs-toggle="tooltip"><i class="fa-solid fa-calendar-days me-1"></i>Horario</a>
                        </div>
                        <div class="col">
                            <a href="home.php?pg=2007&idnorad=<?= $dta["idnorad"] ?>" class="btn btn-sm btn-light border w-100 text-truncate text-info" style="font-size: 0.78rem;" title="Instructores Asignados" data-bs-toggle="tooltip"><i class="fa-solid fa-user-group me-1"></i>Instructores</a>
                        </div>
                        <div class="col">
                            <a href="home.php?pg=2010&idnorad=<?= $dta["idnorad"] ?>" class="btn btn-sm btn-light border w-100 text-truncate text-success" style="font-size: 0.78rem;" title="Notas / Seguimiento" data-bs-toggle="tooltip"><i class="fa-solid fa-note-sticky me-1"></i>Notas</a>
                        </div>
                        <div class="col">
                            <a href="home.php?pg=2004&idnorad=<?= $dta["idnorad"] ?>" class="btn btn-sm btn-light border w-100 text-truncate text-dark" style="font-size: 0.78rem;" title="Bloqueo / Seguridad" data-bs-toggle="tooltip"><i class="fa-solid fa-lock me-1"></i>Bloqueo</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer con Botones de Acción (igual a Página: texto oculto en móvil) -->
            <div class="card-footer bg-white border-0 pt-0 pb-3 px-3">
                <hr class="mt-0 mb-3 text-muted opacity-25">
                <div class="vid-actions-bar" style="grid-template-columns: 1fr 1fr;">
                    <a href="home.php?pg=2010&id=<?= $dta["idnorad"]; ?>" class="btn btn-outline-secondary shadow-sm" target="_blank" title="Imprimir Formato Oficial">
                        <i class="fa-solid fa-print"></i> <span class="d-none d-sm-inline">Imprimir</span>
                    </a>
                    <a href="home.php?pg=<?= $pg; ?>&opera=edi&idnorad=<?= $dta["idnorad"] ?>" class="btn btn-outline-warning text-dark shadow-sm" title="Editar Hoja de Trabajo">
                        <i class="fa-solid fa-pen-to-square"></i> <span class="d-none d-sm-inline">Editar</span>
                    </a>
                    <?php if ($activa) { ?>
                        <form action="home.php?pg=<?= $pg; ?>" method="POST" class="m-0 d-grid">
                            <input type="hidden" name="idnorad" value="<?= $dta["idnorad"]; ?>">
                            <input type="hidden" name="act" value="2">
                            <input type="hidden" name="opera" value="acti">
                            <button type="submit" class="btn btn-outline-secondary w-100 shadow-sm" title="Desactivar Hoja de Trabajo">
                                <i class="fa-solid fa-toggle-on text-primary"></i> <span class="d-none d-sm-inline">Desact.</span>
                            </button>
                        </form>
                    <?php } else { ?>
                        <form action="home.php?pg=<?= $pg; ?>" method="POST" class="m-0 d-grid">
                            <input type="hidden" name="idnorad" value="<?= $dta["idnorad"]; ?>">
                            <input type="hidden" name="act" value="1">
                            <input type="hidden" name="opera" value="acti">
                            <button type="submit" class="btn btn-outline-success w-100 shadow-sm" title="Activar Hoja de Trabajo">
                                <i class="fa-solid fa-toggle-off text-success"></i> <span class="d-none d-sm-inline">Activar</span>
                            </button>
                        </form>
                    <?php } ?>
                    <a href="home.php?pg=<?= $pg; ?>&opera=eli&idnorad=<?= $dta["idnorad"] ?>" class="btn btn-outline-danger shadow-sm" title="Eliminar Hoja de Trabajo" onclick="return eliminar(this);">
                        <i class="fa-solid fa-trash-can"></i> <span class="d-none d-sm-inline">Elim.</span>
                    </a>
                </div>
            </div>
        </div>

<?php
        return ob_get_clean();
    }
}
?>

<!--DASHBOARD-->
<div class="conte">
    <!-- Título de la Sección -->
    <div class="mb-3">
        <?php echo titulo2("<i class='" . $icono . "'></i> Hoja de Trabajo", 1); ?>
    </div>

    <div class="row my-4 g-4">
        <!-- Total de Hojas de Trabajo -->
        <div class="col-12 col-md-4">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #3e4a3d;">Total en Sistema</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #0b1c30;"><?= $totalCursos ?? 0; ?></span>
                            <span style="font-size: 13px; color: #3e4a3d;">hojas de trabajo</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box" style="background-color: #b5dcfe; color: #3c627f;">
                        <i class="fa-solid fa-layer-group fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 11px; color: #3c627f;">
                        <i class="fa-solid fa-arrow-trend-up" style="font-size: 12px;"></i> Registradas
                    </span>
                    <span style="font-size: 11px; color: #3e4a3d;">Disponibles: 100%</span>
                </div>
            </div>
        </div>

        <!-- Hojas de Trabajo Activas -->
        <div class="col-12 col-md-4">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #006b29;">Activas (En ejecución)</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #006b29;"><?= $cursosActivos ?? 0; ?></span>
                            <span style="font-size: 13px; color: rgba(0, 107, 41, 0.8);">en ejecución</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box" style="background-color: #7afd88; color: #006b24;">
                        <i class="fa-solid fa-file-circle-check fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="fw-semibold d-inline-flex align-items-center gap-2" style="font-size: 11px; color: #006b29;">
                        <span class="pulse-dot"></span> En Ejecución Activa
                    </span>
                    <span style="font-size: 11px; color: #3e4a3d;">En ejecución</span>
                </div>
            </div>
        </div>

        <!-- Hojas de Trabajo Finalizadas/Inactivas -->
        <div class="col-12 col-md-4">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #ba1a1a;">Finalizados / Inactivos</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #0b1c30;"><?= $cursosInactivos ?? 0; ?></span>
                            <span style="font-size: 13px; color: #3e4a3d;">archivados</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box" style="background-color: #ffdad6; color: #ba1a1a;">
                        <i class="fa-solid fa-box-archive fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="d-inline-flex align-items-center gap-1" style="font-size: 11px; color: #3e4a3d;">
                        <i class="fa-solid fa-clock-rotate-left" style="font-size: 12px;"></i> Fuera de cronograma
                    </span>
                    <span class="fw-semibold" style="font-size: 11px; color: #3c627f;">Finalizados</span>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Contenedor Principal -->
<div class="container-fluid px-0">
    <!-- ========================================== -->
    <!-- Formulario de Registro / Edición           -->
    <!-- ========================================== -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-4">
            <form id="frmins" action="home.php?pg=<?= $pg; ?>" method="POST">
                <div class="row g-3">
                    <div class="card-header bg-light py-3 border-0">
                        <h5 class="card-title mb-0 fw-bold text-secondary">
                            <i class="fa-solid <?= isset($datOne) ? 'fa-pen-to-square text-warning' : 'fa-circle-plus text-success'; ?> me-2"></i>
                            <?= isset($datOne) ? 'Modificar Hoja de Trabajo No. ' . htmlspecialchars($datOne[0]['idnorad']) : 'Nueva Hoja de Trabajo'; ?>
                        </h5>
                    </div>


                    <!-- Programa de Formación (Select Grande) -->
                    <div class="col-md-6">
                        <label for="codpro" class="form-label fw-semibold">Programa <span class="text-danger">*</span></label>
                        <select name="codpro" id="codpro" class="form-select form-select-lg" required>
                            <option value="">Seleccione un programa...</option>
                            <?php if ($datPr) {
                                foreach ($datPr as $dt) { ?>
                                    <option value="<?= $dt["codpro"]; ?>" <?php if ($datOne && $datOne[0]['codpro'] == $dt['codpro']) echo 'selected'; ?>>
                                        <?= $dt["codpro"] . " - " . $dt["nompro"]; ?>
                                    </option>
                            <?php }
                            } ?>
                        </select>
                    </div>

                    <!-- Empresa (Select Grande) -->
                    <div class="col-md-6">
                        <label for="idemp" class="form-label fw-semibold">Empresa <span class="text-danger">*</span></label>
                        <select name="idemp" id="idemp" class="form-select form-select-lg" required>
                            <option value="">Seleccione una empresa...</option>
                            <?php if ($datEm) {
                                foreach ($datEm as $dt) { ?>
                                    <option value="<?= $dt["idemp"]; ?>" <?php if ($datOne && $datOne[0]['idemp'] == $dt['idemp']) echo 'selected'; ?>>
                                        <?= $dt["nomemp"]; ?>
                                    </option>
                            <?php }
                            } ?>
                        </select>
                    </div>

                    <!-- Programa Especial (Select Grande) -->
                    <div class="col-md-6">
                        <label for="codproesp" class="form-label fw-semibold">Programa Especial <span class="text-danger">*</span></label>
                        <select name="codproesp" id="codproesp" class="form-select form-select-lg" required>
                            <option value="">Seleccione programa especial...</option>
                            <?php if ($datPe) {
                                foreach ($datPe as $dt) { ?>
                                    <option value="<?= $dt["idval"]; ?>" <?php if ($datOne && $datOne[0]['codproesp'] == $dt['idval']) echo 'selected'; ?>>
                                        <?= $dt["nomval"]; ?>
                                    </option>
                            <?php }
                            } ?>
                        </select>
                    </div>

                    <!-- Jornada (Select Grande) -->
                    <div class="col-md-6">
                        <label for="jornada" class="form-label fw-semibold">Jornada <span class="text-danger">*</span></label>
                        <select name="jornada" id="jornada" class="form-select form-select-lg" required>
                            <option value="">Seleccione jornada...</option>
                            <?php if ($datJo) {
                                foreach ($datJo as $dt) { ?>
                                    <option value="<?= $dt["idval"] ?>" <?php if ($datOne && $datOne[0]['jornada'] == $dt['idval']) echo 'selected'; ?>>
                                        <?= $dt["nomval"] ?>
                                    </option>
                            <?php }
                            } ?>
                        </select>
                    </div>

                    <!-- Fecha Inicial -->
                    <div class="col-md-4">
                        <label for="feclini" class="form-label fw-semibold">Fecha Inicial <span class="text-danger">*</span></label>
                        <input type="date" name="feclini" id="feclini" onchange="nFfin(this.value);" class="form-control form-control-lg" value="<?php if ($datOne && $datOne[0]['feclini']) echo $datOne[0]['feclini'];
                                                                                                                                                    else echo $hoy; ?>" min="<?= $hoy; ?>" max="<?= $hmfin; ?>" required>
                    </div>

                    <!-- Fecha Final -->
                    <div class="col-md-4">
                        <label for="feclin" class="form-label fw-semibold">Fecha Final <span class="text-danger">*</span></label>
                        <input type="date" name="feclin" id="feclin" class="form-control form-control-lg" value="<?php if ($datOne && $datOne[0]['feclin']) echo $datOne[0]['feclin'];
                                                                                                                    else echo $hmes; ?>" min="<?= $hoy; ?>" max="<?= $hmfin; ?>" required>
                    </div>

                    <!-- Cupo -->
                    <div class="col-md-4">
                        <label for="cupo" class="form-label fw-semibold">Cupo <span class="text-danger">*</span></label>
                        <input type="number" min="1" name="cupo" id="cupo" class="form-control form-control-lg" value="<?php if ($datOne && $datOne[0]['cupo']) echo $datOne[0]['cupo']; ?>" placeholder="Ej: 30" required>
                    </div>

                    <!-- Campos adicionales (visibles al editar) -->
                    <?php if (isset($datOne)) { ?>
                        <div class="col-md-4">
                            <label for="idfic" class="form-label fw-semibold">Código Ficha</label>
                            <input type="number" name="idfic" id="idfic" class="form-control form-control-lg" value="<?php if ($datOne && $datOne[0]['idfic']) echo $datOne[0]['idfic']; ?>" placeholder="Opcional">
                        </div>
                        <div class="col-md-4">
                            <label for="codslem" class="form-label fw-semibold">Cód. Solicitud Empresa</label>
                            <input type="text" name="codslem" id="codslem" class="form-control form-control-lg" value="<?php if ($datOne && $datOne[0]['codslem']) echo $datOne[0]['codslem']; ?>" placeholder="Opcional">
                        </div>
                        <div class="col-md-4">
                            <label for="convht" class="form-label fw-semibold">Convenio</label>
                            <input type="text" name="convht" id="convht" class="form-control form-control-lg" value="<?php if ($datOne && $datOne[0]['convht']) echo $datOne[0]['convht']; ?>" placeholder="Opcional">
                        </div>
                    <?php } ?>

                    <!-- Botones de Acción -->
                    <div class="col-12 mt-4 d-flex align-items-center gap-2">
                        <button type="submit" class="btn <?= isset($datOne) ? 'btn-warning text-dark' : 'btn-success'; ?> btn-lg px-4 shadow-sm" title="<?= isset($datOne) ? 'Actualizar cambios de la hoja' : 'Guardar nueva hoja de trabajo'; ?>" data-bs-toggle="tooltip">
                            <i class="fa-solid <?= isset($datOne) ? 'fa-arrows-rotate' : 'fa-floppy-disk'; ?> me-2"></i>
                            <?= isset($datOne) ? 'Actualizar' : 'Guardar'; ?>
                        </button>

                        <?php if (isset($datOne)) { ?>
                            <a href="home.php?pg=<?= $pg; ?>" class="btn btn-outline-secondary btn-lg px-4" title="Cancelar edición" data-bs-toggle="tooltip">
                                <i class="fa-solid fa-xmark me-2"></i> Cancelar
                            </a>
                        <?php } ?>

                        <input type="hidden" name="opera" value="save">
                        <input type="hidden" name="idnorad" value="<?php if ($datOne && $datOne[0]['idnorad']) echo $datOne[0]['idnorad']; ?>">
                    </div>

                </div>
            </form>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- Listado de Hojas de Trabajo (Vista Tarjetas) -->
    <!-- ========================================== -->

    <!-- Toolbar de Control y Filtros -->
    <div class="filter-toolbar-container">
        <!-- Izquierda: Píldoras de Filtro de Estado y Exportación -->
        <div class="toolbar-left-group">
            <div class="filter-pills-group" id="tabHojas" role="tablist">
                <!-- Píldora Activas (Activa por defecto al ingresar) -->
                <button class="btn-filter-pill active" id="activos-tab" data-bs-toggle="tab" data-bs-target="#activos" type="button" role="tab" aria-controls="activos" aria-selected="true" onclick="filterByTab('activos')">
                    <span class="pill-dot"></span>
                    <span>Activas</span>
                    <span class="pill-count"><?= $totAct; ?></span>
                </button>

                <!-- Píldora Inactivas -->
                <button class="btn-filter-pill" id="inactivos-tab" data-bs-toggle="tab" data-bs-target="#inactivos" type="button" role="tab" aria-controls="inactivos" aria-selected="false" onclick="filterByTab('inactivos')">
                    <span class="pill-dot"></span>
                    <span>Inactivas</span>
                    <span class="pill-count"><?= $totInact; ?></span>
                </button>

                <!-- Píldora Todas -->
                <button class="btn-filter-pill" id="todos-tab" data-bs-toggle="tab" data-bs-target="#todos" type="button" role="tab" aria-controls="todos" aria-selected="false" onclick="filterByTab('todos')">
                    <span class="pill-dot"></span>
                    <span>Todas</span>
                    <span class="pill-count"><?= $totalCursos ?? 0; ?></span>
                </button>
            </div>

            <!-- Separador -->
            <div class="toolbar-divider d-none d-sm-block"></div>

            <!-- Botones de Exportación integrados -->
            <div class="toolbar-export-buttons">
                <button type="button" class="toolbar-export-btn" title="Copiar al portapapeles" onclick="triggerDTExport('copy')">
                    <i class="fa-solid fa-copy"></i>
                </button>
                <button type="button" class="toolbar-export-btn" title="Descargar CSV" onclick="triggerDTExport('csv')">
                    <i class="fa-solid fa-file-csv"></i>
                </button>
                <button type="button" class="toolbar-export-btn" title="Exportar a Excel" onclick="triggerDTExport('excel')">
                    <i class="fa-solid fa-file-excel"></i>
                </button>
            </div>
        </div>

        <!-- Derecha: Búsqueda, Ordenamiento y Vista Grid/Lista -->
        <div class="toolbar-search-sort-group">
            <!-- Input de búsqueda -->
            <div class="toolbar-search-box">
                <i class="fa-solid fa-magnifying-glass icoldiz"></i>
                <input type="text" id="customHojaSearch" placeholder="Buscar" onkeyup="syncCustomSearch(this.value)">
            </div>

            <!-- Controles Secundarios: Selector de Orden y Conmutador de Vistas -->
            <div class="toolbar-secondary-controls">
                <!-- Selector de ordenamiento -->
                <select class="toolbar-select-sort" id="customHojaSort" onchange="syncCustomSort(this.value)">
                    <option value="asc">Ascendente</option>
                    <option value="desc">Descendente</option>
                </select>

                <!-- Conmutador de cuadrícula / lista -->
                <div class="toolbar-view-toggle">
                    <button type="button" class="toolbar-view-btn active" id="btnViewGrid" title="Vista Cuadrícula" onclick="switchCardView('grid')">
                        <i class="fa-solid fa-grip"></i>
                    </button>
                    <button type="button" class="toolbar-view-btn" id="btnViewList" title="Vista Lista" onclick="switchCardView('list')">
                        <i class="fa-solid fa-bars"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="tab-content" id="tabHojasContent">
        <!-- Pestaña 1: Hojas Activas (Activa por defecto al ingresar) -->
        <div class="tab-pane fade show active" id="activos" role="tabpanel" aria-labelledby="activos-tab">
            <table id="example" class="table table-striped table-vid-cards w-100">
                <thead>
                    <tr>
                        <th>H. Activas</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($datAct) {
                        foreach ($datAct as $dta) { ?>
                            <tr data-order="<?= $dta['idnorad']; ?>">
                                <td data-search="<?= $dta["idnorad"]; ?> <?= $dta["codpro"]; ?> <?= htmlspecialchars($dta["nompro"]); ?> <?= htmlspecialchars($dta["nomemp"]); ?> <?= htmlspecialchars($dta["proesp"]); ?> <?= htmlspecialchars($dta["jorn"]); ?> <?= $dta["feclini"]; ?> <?= $dta["feclin"]; ?> <?= $dta["cupo"]; ?> <?= $dta["idfic"]; ?> <?= htmlspecialchars($dta["codslem"]); ?> <?= htmlspecialchars($dta["convht"]); ?>">

                                    <?= tarjetaHojaTrabajo($dta, $pg, true); ?>

                                </td>
                            </tr>
                    <?php }
                    } ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th>H. Activas</th>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Pestaña 2: Hojas Inactivas (Vista en Tarjetas con mismo diseño) -->
        <div class="tab-pane fade" id="inactivos" role="tabpanel" aria-labelledby="inactivos-tab">
            <table id="myt" class="table table-striped table-vid-cards w-100">
                <thead>
                    <tr>
                        <th>H. Inactivas</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($datInact) {
                        foreach ($datInact as $dta) { ?>
                            <tr data-order="<?= $dta['idnorad']; ?>">
                                <td data-search="<?= $dta["idnorad"]; ?> <?= $dta["codpro"]; ?> <?= htmlspecialchars($dta["nompro"]); ?> <?= htmlspecialchars($dta["nomemp"]); ?> <?= htmlspecialchars($dta["proesp"]); ?> <?= htmlspecialchars($dta["jorn"]); ?> <?= $dta["feclini"]; ?> <?= $dta["feclin"]; ?> <?= $dta["cupo"]; ?> <?= $dta["idfic"]; ?> <?= htmlspecialchars($dta["codslem"]); ?> <?= htmlspecialchars($dta["convht"]); ?>">

                                    <?= tarjetaHojaTrabajo($dta, $pg, false); ?>

                                </td>
                            </tr>
                    <?php }
                    } ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th>H. Inactivas</th>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Pestaña 3: Todas las Hojas de Trabajo (Se visualiza al hacer clic en 'Todas') -->
        <div class="tab-pane fade" id="todos" role="tabpanel" aria-labelledby="todos-tab">
            <table id="tblTodos" class="table table-striped table-vid-cards w-100">
                <thead>
                    <tr>
                        <th>Todas las Hojas de Trabajo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $datTodos = array_merge($datAct ?? [], $datInact ?? []);
                    if ($datTodos) {
                        foreach ($datTodos as $dta) {
                            $esActiva = (isset($dta['act']) && $dta['act'] == 1);
                    ?>
                            <tr data-order="<?= $dta['idnorad']; ?>">
                                <td data-search="<?= $dta["idnorad"]; ?> <?= $dta["codpro"]; ?> <?= htmlspecialchars($dta["nompro"]); ?> <?= htmlspecialchars($dta["nomemp"]); ?> <?= htmlspecialchars($dta["proesp"]); ?> <?= htmlspecialchars($dta["jorn"]); ?> <?= $dta["feclini"]; ?> <?= $dta["feclin"]; ?> <?= $dta["cupo"]; ?> <?= $dta["idfic"]; ?> <?= htmlspecialchars($dta["codslem"]); ?> <?= htmlspecialchars($dta["convht"]); ?>">

                                    <?= tarjetaHojaTrabajo($dta, $pg, $esActiva); ?>

                                </td>
                            </tr>
                    <?php }
                    } ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th>Todas las Hojas de Trabajo</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
