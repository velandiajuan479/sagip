<?php require_once 'controllers/etaccep.php'; ?>

<div class="conte">
<?php if (!empty($idficha) && !empty($idusu)): ?>

    <!-- ENCABEZADO -->
    <?php
    $tituloPagina = "<i class='" . $icono . "'></i> Seguimiento de Bitácoras — Ficha " . htmlspecialchars($idficha);
    if (!empty($fichaInfo['nomfic'])) {
        $tituloPagina .= ' <span class="text-muted fs-5 fw-normal">– ' . htmlspecialchars($fichaInfo['nomfic']) . '</span>';
    }
    echo titulo2($tituloPagina, 1);
    ?>

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <p class="text-muted mb-0 small">
            <i class="fa-solid fa-chalkboard-user me-1"></i>
            <strong>Instructor:</strong> <?= htmlspecialchars($instructorSel['nomusu'] ?? 'No asignado') ?>
            <?php if (!empty($instructorSel['telcan'])): ?>
                &nbsp;|&nbsp;<i class="fa-solid fa-phone me-1"></i><?= htmlspecialchars($instructorSel['telcan']) ?>
            <?php endif; ?>
            <?php if (!empty($instructorSel['emausu'])): ?>
                &nbsp;|&nbsp;<i class="fa-solid fa-envelope me-1"></i><?= htmlspecialchars($instructorSel['emausu']) ?>
            <?php endif; ?>
        </p>
        <a href="home.php?pg=2404" class="btn btn-outline-secondary shadow-sm">
            <i class="fa fa-arrow-left me-1"></i> Volver
        </a>
    </div>

    <!-- DASHBOARD DE MÉTRICAS (estilo kpi-card) -->
    <div class="row my-4 g-4">
        <!-- Total Aprendices -->
        <div class="col-12 col-sm-6 col-md-3">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #3e4a3d;">Total Aprendices</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #0b1c30;"><?= $totalAprendices ?></span>
                            <span style="font-size: 13px; color: #3e4a3d;">en ficha</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box" style="background-color: #b5dcfe; color: #3c627f;">
                        <i class="fa-solid fa-users fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 11px; color: #3c627f;">
                        <i class="fa-solid fa-layer-group" style="font-size: 12px;"></i> Registrados
                    </span>
                    <span style="font-size: 11px; color: #3e4a3d;">Seguimiento</span>
                </div>
            </div>
        </div>

        <!-- Bitácoras Aprobadas -->
        <div class="col-12 col-sm-6 col-md-3">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #006b29;">Bitácoras Aprobadas</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #006b29;"><?= $totalAprobadas ?></span>
                            <span style="font-size: 13px; color: rgba(0, 107, 41, 0.8);">entregadas</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box" style="background-color: #7afd88; color: #006b24;">
                        <i class="fa-solid fa-circle-check fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="fw-semibold d-inline-flex align-items-center gap-2" style="font-size: 11px; color: #006b29;">
                        <span class="pulse-dot"></span> En buen estado
                    </span>
                    <span style="font-size: 11px; color: #3e4a3d;">Aprobadas</span>
                </div>
            </div>
        </div>

        <!-- Bitácoras Rechazadas -->
        <div class="col-12 col-sm-6 col-md-3">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #ba1a1a;">Bitácoras Rechazadas</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #0b1c30;"><?= $totalRechazadas ?></span>
                            <span style="font-size: 13px; color: #3e4a3d;">observadas</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box" style="background-color: #ffdad6; color: #ba1a1a;">
                        <i class="fa-solid fa-circle-xmark fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 11px; color: #3e4a3d;">
                        <i class="fa-solid fa-rotate-left" style="font-size: 12px;"></i> Corregir
                    </span>
                    <span style="font-size: 11px; color: #ba1a1a;">Requieren corrección</span>
                </div>
            </div>
        </div>

        <!-- Bitácoras Pendientes -->
        <div class="col-12 col-sm-6 col-md-3">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #8a5300;">Bitácoras Pendientes</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #0b1c30;"><?= $totalPendientes ?></span>
                            <span style="font-size: 13px; color: #3e4a3d;">por revisar</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box" style="background-color: #ffec99; color: #8a5300;">
                        <i class="fa-solid fa-clock fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 11px; color: #3e4a3d;">
                        <i class="fa-solid fa-hourglass-half" style="font-size: 12px;"></i> En espera
                    </span>
                    <span style="font-size: 11px; color: #8a5300;">Sin revisar</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Toolbar de Control y Búsqueda -->
    <div class="filter-toolbar-container">
        <!-- Izquierda: Píldoras de Filtro (enlaces GET, sin JavaScript) y Exportación -->
        <div class="toolbar-left-group flex-wrap">
            <?php $urlBase = 'home.php?pg=' . rawurlencode((string)$pg)
                           . '&idficha=' . rawurlencode((string)$idficha)
                           . '&idusu='   . rawurlencode((string)$idusu); ?>
            <div class="filter-pills-group flex-wrap">
                <!-- Píldora Todas (Activa por defecto al ingresar) -->
                <a class="btn-filter-pill<?= $filtro === 'todas' ? ' active' : '' ?>" href="<?= htmlspecialchars($urlBase . '&filtro=todas') ?>">
                    <span class="pill-dot" style="background-color: #3c627f;"></span>
                    <span>Todas</span>
                    <span class="pill-count"><?= $totalAprendices ?></span>
                </a>

                <!-- Píldora Bitácoras Aprobadas -->
                <a class="btn-filter-pill<?= $filtro === 'aprobadas' ? ' active' : '' ?>" href="<?= htmlspecialchars($urlBase . '&filtro=aprobadas') ?>">
                    <span class="pill-dot" style="background-color: #006b29;"></span>
                    <span>Bitácoras aprobadas</span>
                    <span class="pill-count"><?= $filAprobadas ?></span>
                </a>

                <!-- Píldora Bitácoras Pendientes -->
                <a class="btn-filter-pill<?= $filtro === 'pendientes' ? ' active' : '' ?>" href="<?= htmlspecialchars($urlBase . '&filtro=pendientes') ?>">
                    <span class="pill-dot" style="background-color: #8a5300;"></span>
                    <span>Bitácoras pendientes</span>
                    <span class="pill-count"><?= $filPendientes ?></span>
                </a>

                <!-- Píldora Bitácoras Rechazadas -->
                <a class="btn-filter-pill<?= $filtro === 'rechazadas' ? ' active' : '' ?>" href="<?= htmlspecialchars($urlBase . '&filtro=rechazadas') ?>">
                    <span class="pill-dot" style="background-color: #ba1a1a;"></span>
                    <span>Bitácoras rechazadas</span>
                    <span class="pill-count"><?= $filRechazadas ?></span>
                </a>
            </div>

            <!-- Separador -->
            <div class="toolbar-divider d-none d-md-block"></div>

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

        <!-- Derecha: Búsqueda y Ordenamiento -->
        <div class="toolbar-search-sort-group">
            <!-- Input de búsqueda -->
            <div class="toolbar-search-box">
                <i class="fa-solid fa-magnifying-glass icoldiz"></i>
                <input type="text" id="customVideoSearch" placeholder="Buscar aprendiz..." autocomplete="off" onkeyup="syncCustomSearch(this.value)">
            </div>
            <div class="toolbar-secondary-controls">
                <select class="toolbar-select-sort" id="customVideoSort" onchange="syncCustomSort(this.value)">
                    <option value="asc">Ascendente</option>
                    <option value="desc">Descendente</option>
                </select>
            </div>
        </div>
    </div>

    <!-- TABLA DE APRENDICES (se omite si no hay filas que mostrar) -->
    <?php if (!empty($filasVista)): ?>
    <div class="table-responsive mt-3">
        <table id="example" class="table table-striped table-hover align-middle w-100">
            <thead class="table-light">
                <tr>
                    <th class="text-center">No</th>
                    <th>Datos del Aprendiz</th>
                    <th class="text-center">Aprobadas</th>
                    <th class="text-center">Rechazadas</th>
                    <th class="text-center">Pendientes</th>
                    <th>Faltantes</th>
                </tr>
            </thead>
            <tbody>
                <?php $idx = 1; foreach ($filasVista as $ap): ?>
                    <tr>
                        <td class="text-center fw-bold text-secondary"><?= $idx++ ?></td>

                        <!-- Datos Aprendiz -->
                        <td>
                            <strong class="text-dark"><?= htmlspecialchars($ap['nomusu']) ?></strong><br>
                            <small class="text-muted">
                                <i class="fa-solid fa-id-card me-1"></i><?= htmlspecialchars($ap['ndocusu']) ?>
                                <?php if (!empty($ap['emausu'])): ?>
                                    <br><i class="fa-solid fa-envelope me-1"></i><?= htmlspecialchars($ap['emausu']) ?>
                                <?php endif; ?>
                                <?php if (!empty($ap['telcan'])): ?>
                                    <br><i class="fa-solid fa-phone me-1"></i><?= htmlspecialchars($ap['telcan']) ?>
                                <?php endif; ?>
                            </small>
                        </td>

                        <!-- Aprobadas -->
                        <td class="text-center">
                            <span class="badge bg-success fs-6 px-3"><?= $ap['aprobado'] ?></span>
                        </td>

                        <!-- Rechazadas -->
                        <td class="text-center">
                            <span class="badge bg-danger fs-6 px-3"><?= $ap['rechazado'] ?></span>
                        </td>

                        <!-- Pendientes -->
                        <td class="text-center">
                            <span class="badge bg-warning text-dark fs-6 px-3"><?= $ap['pendiente'] ?></span>
                        </td>

                        <!-- Faltantes -->
                        <td><?= formatearRangoBitacoras($ap['numeros_faltantes']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot class="table-light">
                <tr>
                    <th class="text-center">No</th>
                    <th>Datos del Aprendiz</th>
                    <th class="text-center">Aprobadas</th>
                    <th class="text-center">Rechazadas</th>
                    <th class="text-center">Pendientes</th>
                    <th>Faltantes</th>
                </tr>
            </tfoot>
        </table>
    </div>
    <?php else: ?>
        <!-- Estado vacío: sin <table> para que DataTables no se inicialice
             sobre una fila con colspan (evita el warning y el registro falso) -->
        <div class="text-center text-muted fst-italic py-4 mt-3">
            <i class="fa fa-inbox fa-2x mb-2 d-block"></i>
            <?php if ($totalAprendices === 0): ?>
                No hay aprendices registrados para esta ficha.
            <?php else: ?>
                Ningún aprendiz coincide con el filtro &laquo;<?= htmlspecialchars($filtroEtiqueta) ?>&raquo;.
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- BARRA INFERIOR -->
    <div class="d-flex justify-content-between align-items-center mt-4 mb-2">
        <a href="home.php?pg=2404" class="btn btn-outline-secondary shadow-sm">
            <i class="fa fa-arrow-left me-1"></i> Volver
        </a>
        <a href="#" class="btn btn-outline-secondary btn-sm">
            <i class="fa fa-arrow-up me-1"></i> Subir al inicio
        </a>
    </div>

<?php else: ?>

    <!-- Parámetros no recibidos -->
    <div class="alert alert-warning">
        <i class="fa fa-triangle-exclamation me-2"></i>
        No se recibieron los parámetros necesarios (<code>idficha</code> e <code>idusu</code>).
        Esta vista debe accederse desde la página de coordinación de etapa productiva.
    </div>
    <a href="home.php?pg=2404" class="btn btn-secondary">
        <i class="fa fa-arrow-left me-1"></i> Volver
    </a>

<?php endif; ?>

</div>
