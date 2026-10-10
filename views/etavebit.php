<?php
require_once 'controllers/etacebit.php';

if (!function_exists('formatearFecha')) {
    function formatearFecha($fecha) {
        if (empty($fecha)) return '';
        $time = strtotime($fecha);
        return $time ? date('d/m/Y', $time) : $fecha;
    }
}

if (!function_exists('agruparBitacorasPorAprendiz')) {
    function agruparBitacorasPorAprendiz(array $lista) {
        $agrupados = [];
        foreach ($lista as $item) {
            $idusu = $item['idusu'];
            if (!isset($agrupados[$idusu])) {
                $agrupados[$idusu] = [
                    'idusu' => $idusu,
                    'nomusu' => $item['nomusu'],
                    'ndocusu' => $item['ndocusu'],
                    'nombre_empresa' => $item['nombre_empresa'] ?? 'Sin Empresa Asignada',
                    'nit' => $item['nit'] ?? 'N/A',
                    'bitacoras' => []
                ];
            }
            $agrupados[$idusu]['bitacoras'][] = $item;
        }

        uasort($agrupados, function($a, $b) {
            return strcasecmp($a['nomusu'], $b['nomusu']);
        });

        foreach ($agrupados as &$ap) {
            usort($ap['bitacoras'], function($a, $b) {
                return intval($a['numero_bitacora']) <=> intval($b['numero_bitacora']);
            });
        }
        unset($ap);

        return $agrupados;
    }
}

$aprendices_agrupados = !empty($aprendices) ? agruparBitacorasPorAprendiz($aprendices) : [];
$aprobados_agrupados = !empty($aprendices_aprobados) ? agruparBitacorasPorAprendiz($aprendices_aprobados) : [];
$rechazados_agrupados = !empty($aprendices_rechazados) ? agruparBitacorasPorAprendiz($aprendices_rechazados) : [];

// Conteos desde base de datos / variables del controlador
$total_aprendices_ficha = $estadisticas_ficha['total_aprendices'] ?? 0;
$total_recibidas_ficha = ($estadisticas_ficha['pendientes'] ?? count($aprendices_agrupados)) + ($estadisticas_ficha['aprobadas'] ?? 0) + ($estadisticas_ficha['rechazadas'] ?? 0);
$total_aprobadas_ficha = $estadisticas_ficha['aprobadas'] ?? 0;
$total_devueltas_ficha = $estadisticas_ficha['rechazadas'] ?? 0;
?>

<div class="w-100 px-3">
    <div class="tit mb-3">
        <h1><i class="<?= $icono; ?> icoiuv"></i> Evaluar Bitácoras</h1>
        <hr class="lintit">
    </div>

    <!-- ================== SELECCIÓN DE FICHA ================== -->
    <h2>Fichas Asignadas</h2>
    <form method="GET" action="home.php" class="mt-3 mb-4">
        <input type="hidden" name="pg" value="<?= $pg; ?>">
        <input type="hidden" name="opera" value="evaluar_bitacora">
        <div class="row align-items-end g-2">
            <div class="col-md-9">
                <label for="idficha_eval" class="form-label fw-bold">Selecciona una ficha:</label>
                <select name="idficha" id="idficha_eval" class="form-select" required>
                    <option value="" disabled <?= empty($idficha_seleccionada) ? 'selected' : '' ?>>Elige una ficha</option>
                    <?php foreach ($fichas as $ficha): ?>
                        <option value="<?= $ficha['idficha']; ?>" <?= ($idficha_seleccionada == $ficha['idficha']) ? 'selected' : '' ?>>
                            Ficha <?= $ficha['idficha']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn bg-sena-900 text-white w-100 fw-bold">Consultar Ficha</button>
            </div>
        </div>
    </form>

    <?php if (!empty($idficha_seleccionada)): ?>

    <!-- ================== TARJETAS KPI ================== -->
    <div class="row mb-4 g-3">
        <!-- Tarjeta 1: TOTAL APRENDICES -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi-card h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted small fw-bold text-uppercase">TOTAL APRENDICES</span>
                        <h2 class="mb-0 mt-1"><?= $total_aprendices_ficha; ?></h2>
                    </div>
                    <div class="kpi-icon-box" style="background-color: #e5eeff; color: #117f09;">
                        <i class="fa-solid fa-users fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="small text-muted"><i class="fa-solid fa-user-check icoldiz"></i> Matriculados activos</span>
                </div>
            </div>
        </div>

        <!-- Tarjeta 2: RECIBIDAS -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi-card h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted small fw-bold text-uppercase">RECIBIDAS</span>
                        <h2 class="mb-0 mt-1"><?= $total_recibidas_ficha; ?></h2>
                    </div>
                    <div class="kpi-icon-box" style="background-color: #dce9ff; color: #117f09;">
                        <i class="fa-solid fa-box-archive fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="small text-muted"><i class="fa-solid fa-inbox icoldiz"></i> Total entregadas</span>
                </div>
            </div>
        </div>

        <!-- Tarjeta 3: APROBADAS -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi-card h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted small fw-bold text-uppercase">APROBADAS</span>
                        <h2 class="mb-0 mt-1"><?= $total_aprobadas_ficha; ?></h2>
                    </div>
                    <div class="kpi-icon-box" style="background-color: #e5eeff; color: #117f09;">
                        <i class="fa-solid fa-circle-check fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="small text-muted"><i class="fa-solid fa-check icoldiz"></i> Listas y revisadas</span>
                </div>
            </div>
        </div>

        <!-- Tarjeta 4: DEVUELTAS -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi-card h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted small fw-bold text-uppercase" style="color: #d92525;">DEVUELTAS</span>
                        <h2 class="mb-0 mt-1" style="color: #d92525;"><?= $total_devueltas_ficha; ?></h2>
                    </div>
                    <div class="kpi-icon-box" style="background-color: #fde8e8; color: #d92525;">
                        <i class="fa-solid fa-rotate-left fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip" style="background-color: #fde8e8;">
                    <span class="small text-muted" style="color: #d92525;"><i class="fa-solid fa-clock-rotate-left icoldiz"></i> Esperando corrección</span>
                </div>
            </div>
        </div>
    </div>

    <!-- ================== 1. ALINEACIÓN DE PESTAÑAS Y BUSCADOR EN UNA SOLA FILA ================== -->
    <div class="filter-toolbar-container mb-4 p-2 bg-white rounded shadow-sm border">
        <div class="d-flex flex-row align-items-center justify-content-between gap-3 w-100">
            <!-- Agrupación de Pestañas (Izquierda) -->
            <div class="filter-pills-group d-flex flex-nowrap align-items-center gap-1" role="tablist">
                <button type="button" class="btn-filter-pill active text-nowrap" id="pendientes-tab" data-bs-toggle="tab" data-bs-target="#sec-pendientes" onclick="filterByTab('pendientes')">
                    <span class="pill-dot" style="background-color: #ffffff;"></span>
                    Pendientes <span class="pill-count"><?= count($aprendices_agrupados); ?></span>
                </button>
                <button type="button" class="btn-filter-pill text-nowrap" id="devueltas-tab" data-bs-toggle="tab" data-bs-target="#sec-devueltas" onclick="filterByTab('devueltas')">
                    <span class="pill-dot" style="background-color: #d92525;"></span>
                    Devueltas <span class="pill-count"><?= count($rechazados_agrupados); ?></span>
                </button>
                <button type="button" class="btn-filter-pill text-nowrap" id="aprobadas-tab" data-bs-toggle="tab" data-bs-target="#sec-aprobadas" onclick="filterByTab('aprobadas')">
                    <span class="pill-dot" style="background-color: #117f09;"></span>
                    Aprobadas <span class="pill-count"><?= count($aprobados_agrupados); ?></span>
                </button>
            </div>

            <!-- Buscador Extendido (Derecha) -->
            <div class="toolbar-search-box flex-grow-1">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="customVideoSearch" class="form-control border-0 bg-transparent shadow-none" placeholder="Buscar aprendiz por nombre, CC o empresa...">
            </div>
        </div>
    </div>

    <!-- ================== CONTENIDO POR PESTAÑAS ================== -->
    <div class="tab-content mb-4" id="bitacoraTabContent">

        <!-- 1. PENDIENTES -->
        <div class="tab-pane fade show active" id="sec-pendientes" role="tabpanel">
            <h2>Aprendices con Bitácoras Pendientes</h2>
            <?php if (!empty($aprendices_agrupados)): ?>
                <form method="POST" action="home.php?pg=<?= $pg; ?>">
                    <input type="hidden" name="opera" value="evaluar_masivamente">
                    <input type="hidden" name="idficha" value="<?= $idficha_seleccionada; ?>">

                    <div class="mb-4">
                        <?php foreach ($aprendices_agrupados as $idusu => $ap): ?>
                            <!-- 2. DISEÑO Y CONTRASTE DE LA TARJETA DEL APRENDIZ -->
                            <details class="border rounded-3 bg-white mb-3 shadow-sm overflow-hidden border-light-subtle">
                                <summary class="p-3 bg-light bg-gradient text-dark d-flex align-items-center justify-content-between border-bottom" style="cursor: pointer; user-select: none;">
                                    <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between w-100 me-2">
                                        <div>
                                            <span class="fs-6 text-dark fw-bold"><?= mb_strtoupper($ap['nomusu'], 'UTF-8') ?></span>
                                            <span class="text-muted ms-sm-2 small fw-normal">CC: <?= $ap['ndocusu'] ?></span>
                                            <div class="text-secondary small fw-semibold mt-1">
                                                Empresa: <?= $ap['nombre_empresa'] ?> <?= (!empty($ap['nit']) && $ap['nit'] !== 'N/A') ? '(NIT: '.$ap['nit'].')' : '' ?>
                                            </div>
                                        </div>
                                        <span class="badge bg-warning text-dark mt-2 mt-sm-0 align-self-sm-center px-3 py-2 fs-7 rounded-pill">
                                            <?= count($ap['bitacoras']) ?> pendiente<?= count($ap['bitacoras']) > 1 ? 's' : '' ?>
                                        </span>
                                    </div>
                                </summary>
                                <div class="p-0 border-top">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover align-middle mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th style="width: 40px; text-align: center;"></th>
                                                    <th>N° Bitácora</th>
                                                    <th>Periodo Reportado (Inicio – Fin)</th>
                                                    <th style="text-align:right;">Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($ap['bitacoras'] as $bitacora): ?>
                                                    <tr>
                                                        <td style="text-align: center;">
                                                            <input type="checkbox" name="bitacoras_seleccionadas[]" value="<?= $bitacora['idbitacora']; ?>">
                                                        </td>
                                                        <td><strong>Bitácora #<?= $bitacora['numero_bitacora']; ?></strong></td>
                                                        <td><?= formatearFecha($bitacora['fecha_inicio']) ?> – <?= formatearFecha($bitacora['fecha_fin']) ?></td>
                                                        <td style="text-align:right;">
                                                            <i class="fa-solid fa-eye fa-2x icoiuv" title="Ver actividades" style="cursor: pointer; margin-right: 12px;" data-bs-toggle="modal" data-bs-target="#bitDet<?= $bitacora['idbitacora'] ?>"></i>
                                                            <a href="views/pdfbit.php?idusu=<?= $bitacora['idusu']; ?>&idbitacora=<?= $bitacora['idbitacora'] ?>&pdf=ok" target="_blank" title="Ver bitácora PDF" style="text-decoration:none;">
                                                                <i class="fa-solid fa-file-pdf fa-2x icoiuv" style="cursor: pointer;"></i>
                                                            </a>
                                                            <?= modalBitacoraDetalles($bitacora['idbitacora'], $bitacora['numero_bitacora'], $pg); ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </details>
                        <?php endforeach; ?>
                    </div>

                    <!-- 3. JERARQUÍA Y ESPACIADO DEL PANEL INFERIOR "EVALUAR SELECCIONADAS" -->
                    <div class="mt-5 p-4 border rounded-3 bg-white shadow-sm border-top border-success border-3">
                        <h2 class="fs-5 text-dark fw-bold mb-3"><i class="fa-solid fa-list-check me-2 text-success"></i>Evaluar Seleccionadas</h2>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="estado_masivo" class="form-label fw-bold">Estado:</label>
                                <select name="estado_masivo" id="estado_masivo" class="form-select" required>
                                    <option value="">Seleccione</option>
                                    <option value="aprobado">Aprobado</option>
                                    <option value="rechazado">Devolver para revisión</option>
                                </select>
                            </div>
                            <div class="col-md-9">
                                <label for="observacion_masiva" class="form-label fw-bold">Observación común:</label>
                                <textarea name="observacion_masiva" id="observacion_masiva" class="form-control" rows="2" required placeholder="Escriba aquí los comentarios u observaciones para los registros seleccionados..."></textarea>
                            </div>
                        </div>
                        <button type="submit" class="btn bg-sena-900 text-white fw-bold mt-3 px-4 py-2">Evaluar Seleccionadas</button>
                    </div>
                </form>
            <?php else: ?>
                <div class="alert alert-warning mt-3">No hay bitácoras pendientes para esta ficha.</div>
            <?php endif; ?>
        </div>

        <!-- 2. DEVUELTAS -->
        <div class="tab-pane fade" id="sec-devueltas" role="tabpanel">
            <h2 style="color: #d92525 !important;">Bitácoras Devueltas para Revisión</h2>
            <?php if (!empty($rechazados_agrupados)): ?>
                <div class="mb-3">
                    <?php foreach ($rechazados_agrupados as $idusu => $ap): ?>
                        <details class="border rounded-3 bg-white mb-3 shadow-sm overflow-hidden border-light-subtle">
                            <summary class="p-3 bg-light bg-gradient text-dark d-flex align-items-center justify-content-between border-bottom" style="cursor: pointer; user-select: none;">
                                <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between w-100 me-2">
                                    <div>
                                        <span class="fs-6 text-dark fw-bold"><?= mb_strtoupper($ap['nomusu'], 'UTF-8') ?></span>
                                        <span class="text-muted ms-sm-2 small fw-normal">CC: <?= $ap['ndocusu'] ?></span>
                                        <div class="text-secondary small fw-semibold mt-1">
                                            Empresa: <?= $ap['nombre_empresa'] ?> <?= (!empty($ap['nit']) && $ap['nit'] !== 'N/A') ? '(NIT: '.$ap['nit'].')' : '' ?>
                                        </div>
                                    </div>
                                    <span class="badge bg-danger text-white mt-2 mt-sm-0 align-self-sm-center px-3 py-2 fs-7 rounded-pill">
                                        <?= count($ap['bitacoras']) ?> devuelta<?= count($ap['bitacoras']) > 1 ? 's' : '' ?>
                                    </span>
                                </div>
                            </summary>
                            <div class="p-0 border-top">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover align-middle mb-0">
                                        <thead class="table-danger">
                                            <tr>
                                                <th>N° Bitácora</th>
                                                <th>Periodo Reportado (Inicio – Fin)</th>
                                                <th style="text-align:right;">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($ap['bitacoras'] as $bit): ?>
                                                <tr>
                                                    <td><strong>Bitácora #<?= $bit['numero_bitacora'] ?></strong></td>
                                                    <td><?= formatearFecha($bit['fecha_inicio']) ?> – <?= formatearFecha($bit['fecha_fin']) ?></td>
                                                    <td style="text-align:right;">
                                                        <i class="fa-solid fa-eye fa-2x icoiuv" title="Ver actividades" style="cursor: pointer; margin-right: 12px;" data-bs-toggle="modal" data-bs-target="#bitDet<?= $bit['idbitacora'] ?>"></i>
                                                        <a href="views/pdfbit.php?idusu=<?= $bit['idusu']; ?>&idbitacora=<?= $bit['idbitacora'] ?>&pdf=ok" target="_blank" title="Ver bitácora PDF" style="text-decoration:none;">
                                                            <i class="fa-solid fa-file-pdf fa-2x icoiuv" style="cursor: pointer;"></i>
                                                        </a>
                                                        <?= modalBitacoraDetalles($bit['idbitacora'], $bit['numero_bitacora'], $pg); ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </details>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-warning mt-3">No hay bitácoras devueltas para revisión en esta ficha.</div>
            <?php endif; ?>
        </div>

        <!-- 3. APROBADAS -->
        <div class="tab-pane fade" id="sec-aprobadas" role="tabpanel">
            <h2>Bitácoras Aprobadas</h2>
            <?php if (!empty($aprobados_agrupados)): ?>
                <div class="mb-3">
                    <?php foreach ($aprobados_agrupados as $idusu => $ap): ?>
                        <details class="border rounded-3 bg-white mb-3 shadow-sm overflow-hidden border-light-subtle">
                            <summary class="p-3 bg-light bg-gradient text-dark d-flex align-items-center justify-content-between border-bottom" style="cursor: pointer; user-select: none;">
                                <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between w-100 me-2">
                                    <div>
                                        <span class="fs-6 text-dark fw-bold"><?= mb_strtoupper($ap['nomusu'], 'UTF-8') ?></span>
                                        <span class="text-muted ms-sm-2 small fw-normal">CC: <?= $ap['ndocusu'] ?></span>
                                        <div class="text-secondary small fw-semibold mt-1">
                                            Empresa: <?= $ap['nombre_empresa'] ?> <?= (!empty($ap['nit']) && $ap['nit'] !== 'N/A') ? '(NIT: '.$ap['nit'].')' : '' ?>
                                        </div>
                                    </div>
                                    <span class="badge bg-success text-white mt-2 mt-sm-0 align-self-sm-center px-3 py-2 fs-7 rounded-pill">
                                        <?= count($ap['bitacoras']) ?> aprobada<?= count($ap['bitacoras']) > 1 ? 's' : '' ?>
                                    </span>
                                </div>
                            </summary>
                            <div class="p-0 border-top">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover align-middle mb-0">
                                        <thead class="table-success">
                                            <tr>
                                                <th>N° Bitácora</th>
                                                <th>Periodo Reportado (Inicio – Fin)</th>
                                                <th style="text-align:right;">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($ap['bitacoras'] as $bit): ?>
                                                <tr>
                                                    <td><strong>Bitácora #<?= $bit['numero_bitacora'] ?></strong></td>
                                                    <td><?= formatearFecha($bit['fecha_inicio']) ?> – <?= formatearFecha($bit['fecha_fin']) ?></td>
                                                    <td style="text-align:right;">
                                                        <i class="fa-solid fa-eye fa-2x icoiuv" title="Ver actividades" style="cursor: pointer; margin-right: 12px;" data-bs-toggle="modal" data-bs-target="#bitDet<?= $bit['idbitacora'] ?>"></i>
                                                        <a href="views/pdfbit.php?idusu=<?= $bit['idusu']; ?>&idbitacora=<?= $bit['idbitacora'] ?>&pdf=ok" target="_blank" title="Ver bitácora PDF" style="text-decoration:none;">
                                                            <i class="fa-solid fa-file-pdf fa-2x icoiuv" style="cursor: pointer;"></i>
                                                        </a>
                                                        <?= modalBitacoraDetalles($bit['idbitacora'], $bit['numero_bitacora'], $pg); ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </details>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-info mt-3">No hay bitácoras aprobadas en esta ficha.</div>
            <?php endif; ?>
        </div>

    </div>

    <?php endif; ?>