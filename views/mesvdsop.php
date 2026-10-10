<?php require_once 'controllers/mescdsop.php'; ?>

<div class="conte container-fluid px-0 w-100">
    <?php echo titulo2("<i class='" . $icono . "'></i> Detalle Soporte", 0); ?>

    <!-- Dashboard Compacto y Sobrio -->
    <div class="card bg-light border shadow-sm mb-4">
        <div class="card-header bg-light py-2 px-3">
            <span class="fw-bold text-dark small">
                <i class="fa-solid fa-chart-bar me-2 text-primary"></i>Panel de Métricas e Indicadores
            </span>
        </div>
        <div class="card-body p-3">
            <form method="GET" action="home.php" class="row g-2 align-items-center mb-3">
                <input type="hidden" name="pg" value="<?= htmlspecialchars((string)($pg ?? '2202'), ENT_QUOTES, 'UTF-8'); ?>">
                
                <!-- Filtro Fecha (Calendario) -->
                <div class="col-auto">
                    <label for="fec_filtro" class="col-form-label border-0 bg-transparent fw-semibold text-secondary small">
                        <i class="fa-solid fa-calendar-alt me-1 text-primary"></i>Fecha:
                    </label>
                </div>
                <div class="col-auto">
                    <input type="date" id="fec_filtro" name="fec_filtro" class="form-control form-control-sm bg-white" value="<?= htmlspecialchars((string)($fec_filtro ?? ''), ENT_QUOTES, 'UTF-8'); ?>" onchange="this.form.submit()">
                </div>

                <!-- Filtro Mes -->
                <div class="col-auto">
                    <label for="mes" class="col-form-label border-0 bg-transparent fw-semibold text-secondary small ms-md-2">
                        Mes:
                    </label>
                </div>
                <div class="col-auto">
                    <select name="mes" id="mes" class="form-select form-select-sm bg-white" onchange="this.form.submit()">
                        <option value="">-- Todos --</option>
                        <?php
                        $mesesNombres = [
                            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
                            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
                            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
                        ];
                        foreach ($mesesNombres as $numMes => $nomMes): ?>
                            <option value="<?= $numMes; ?>" <?= ((int)($mes ?? 0) === $numMes) ? 'selected' : ''; ?>><?= $nomMes; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Filtro Año -->
                <div class="col-auto">
                    <label for="anio" class="col-form-label border-0 bg-transparent fw-semibold text-secondary small ms-md-2">
                        Año:
                    </label>
                </div>
                <div class="col-auto">
                    <select name="anio" id="anio" class="form-select form-select-sm bg-white" onchange="this.form.submit()">
                        <option value="">-- Todos --</option>
                        <?php
                        $anioActual = (int) date('Y');
                        for ($a = $anioActual; $a >= 2024; $a--): ?>
                            <option value="<?= $a; ?>" <?= ((int)($anio ?? 0) === $a) ? 'selected' : ''; ?>><?= $a; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>

                <div class="col-auto ms-auto">
                    <?php if (!empty($fec_filtro) || !empty($mes) || !empty($anio)): ?>
                        <a href="home.php?pg=<?= htmlspecialchars((string)($pg ?? '2202'), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-sm btn-outline-secondary">
                            <i class="fa-solid fa-sync-alt me-1"></i>Limpiar filtros
                        </a>
                    <?php endif; ?>
                </div>
            </form>

            <div class="row g-3">
                    <div class="col-12 col-sm-6 col-lg-3">
                        <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="text-uppercase fw-bold text-muted small">Atendidos (Mes)</span>
                                    <div class="d-flex align-items-baseline gap-2 mt-2">
                                        <span class="fw-bold fs-2 text-dark"><?= (int) $metricas['atendidos_mes']; ?></span>
                                        <span class="small text-muted">casos</span>
                                    </div>
                                </div>
                                <div class="kpi-icon-box bg-primary text-white">
                                    <i class="fa-solid fa-headset fs-4"></i>
                                </div>
                            </div>
                            <div class="kpi-footer-strip">
                                <span class="fw-semibold text-primary d-inline-flex align-items-center gap-1 small">
                                    <i class="fa-solid fa-calendar-days"></i> Registrados este mes
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3">
                        <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="text-uppercase fw-bold text-success small">Resueltos (Mes)</span>
                                    <div class="d-flex align-items-baseline gap-2 mt-2">
                                        <span class="fw-bold fs-2 text-dark"><?= (int) $metricas['resueltos_mes']; ?></span>
                                        <span class="small text-muted">casos</span>
                                    </div>
                                </div>
                                <div class="kpi-icon-box bg-success text-white">
                                    <i class="fa-solid fa-circle-check fs-4"></i>
                                </div>
                            </div>
                            <div class="kpi-footer-strip">
                                <span class="fw-semibold text-success d-inline-flex align-items-center gap-2 small">
                                    <span class="pulse-dot"></span> Finalizados este mes
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3">
                        <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="text-uppercase fw-bold text-info small">Resueltos (Anual)</span>
                                    <div class="d-flex align-items-baseline gap-2 mt-2">
                                        <span class="fw-bold fs-2 text-dark"><?= (int) $metricas['resueltos_anual']; ?></span>
                                        <span class="small text-muted">acumulado</span>
                                    </div>
                                </div>
                                <div class="kpi-icon-box bg-info text-white">
                                    <i class="fa-solid fa-chart-line fs-4"></i>
                                </div>
                            </div>
                            <div class="kpi-footer-strip">
                                <span class="fw-semibold text-info d-inline-flex align-items-center gap-1 small">
                                    <i class="fa-solid fa-calendar"></i> Año <?= date('Y'); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3">
                        <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="text-uppercase fw-bold text-warning small">Resueltos (Hoy)</span>
                                    <div class="d-flex align-items-baseline gap-2 mt-2">
                                        <span class="fw-bold fs-2 text-dark"><?= (int) $metricas['resueltos_fecha']; ?></span>
                                        <span class="small text-muted">casos</span>
                                    </div>
                                </div>
                                <div class="kpi-icon-box bg-warning text-dark">
                                    <i class="fa-solid fa-clock-rotate-left fs-4"></i>
                                </div>
                            </div>
                            <div class="kpi-footer-strip">
                                <span class="fw-semibold text-warning d-inline-flex align-items-center gap-1 small">
                                    <i class="fa-solid fa-clock"></i> <?= date('Y-m-d'); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <?php
        $cntTotal = !empty($dat) ? count($dat) : 0;
        $cntIniciados = 0;
        $cntProceso = 0;
        $cntFinalizados = 0;

        if (!empty($dat)) {
            foreach ($dat as $dtRow) {
                $rowEstId = (int) ($dtRow['ultimo_estado_id'] ?? 0);
                $rowEstNom = mb_strtolower(trim((string) ($dtRow['nom_ultimo_estado'] ?? '')));

                if ($rowEstId === 1074 || strpos($rowEstNom, 'finaliz') !== false || strpos($rowEstNom, 'solucion') !== false || strpos($rowEstNom, 'cerrad') !== false) {
                    $cntFinalizados++;
                } elseif ($rowEstId === 1075 || strpos($rowEstNom, 'proceso') !== false) {
                    $cntProceso++;
                } elseif ($rowEstId === 1073 || strpos($rowEstNom, 'inicia') !== false) {
                    $cntIniciados++;
                } else {
                    $cntIniciados++;
                }
            }
        }

        // Función para resolver rutas redirigiendo a la carpeta img/fevi
        $resolverRutaFoto = function ($rutaOriginal) {
            $ruta = trim((string) $rutaOriginal);
            if ($ruta === '') return null;
            $baseName = basename($ruta);
            // 1. Prioridad: carpeta img/fevi
            if (file_exists('img/fevi/' . $baseName)) {
                return 'img/fevi/' . $baseName;
            }
            // 2. Ruta directa si existe
            if (file_exists($ruta)) {
                return $ruta;
            }
            return null;
        };

        // Cargar evidencias de soporte_evidencia y detalle_soporte para agrupar en álbumes/carpetas
        $todasEvidencias = [];
        if (!empty($dat) && class_exists('conexion')) {
            try {
                $arrIdsop = [];
                foreach ($dat as $dItem) {
                    if (!empty($dItem['idsop'])) {
                        $arrIdsop[] = (int) $dItem['idsop'];
                    }
                }
                if (!empty($arrIdsop)) {
                    $inIds = implode(',', array_unique($arrIdsop));
                    $cnxSop = (new conexion())->get_conexion();

                    // 1. Evidencias subidas múltiples (soporte_evidencia)
                    try {
                        $stmtEviMulti = $cnxSop->query("SELECT idsop, ruta FROM soporte_evidencia WHERE idsop IN ($inIds) AND ruta IS NOT NULL AND ruta != '' ORDER BY idevi ASC");
                        if ($stmtEviMulti) {
                            while ($rM = $stmtEviMulti->fetch(PDO::FETCH_ASSOC)) {
                                $idS = (int) $rM['idsop'];
                                $resRuta = $resolverRutaFoto($rM['ruta']);
                                if ($resRuta && (!isset($todasEvidencias[$idS]) || !in_array($resRuta, $todasEvidencias[$idS]))) {
                                    $todasEvidencias[$idS][] = $resRuta;
                                }
                            }
                        }
                    } catch (Exception $e) {}

                    // 2. Evidencias de avances y seguimientos (detalle_soporte)
                    try {
                        $stmtEviDet = $cnxSop->query("SELECT idsop, detevi FROM detalle_soporte WHERE idsop IN ($inIds) AND detevi IS NOT NULL AND detevi != '' ORDER BY iddet ASC");
                        if ($stmtEviDet) {
                            while ($rEvi = $stmtEviDet->fetch(PDO::FETCH_ASSOC)) {
                                $idS = (int) $rEvi['idsop'];
                                $resRuta = $resolverRutaFoto($rEvi['detevi']);
                                if ($resRuta && (!isset($todasEvidencias[$idS]) || !in_array($resRuta, $todasEvidencias[$idS]))) {
                                    $todasEvidencias[$idS][] = $resRuta;
                                }
                            }
                        }
                    } catch (Exception $e) {}
                }
            } catch (Exception $e) {
                // Silencioso para asegurar que la vista no se interrumpa
            }
        }
    ?>

    <!-- Tabla Principal de Tickets con Diseño de Mesa de Ayuda y Filtro de Estados -->
    <div class="card bg-white border shadow-sm mb-4 w-100">
        <div class="card-header bg-white py-3 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h6 class="mb-0 fw-bold text-secondary">
                    <i class="fa-solid fa-list-ul me-2 text-primary"></i>Bandeja General de Casos
                </h6>
                <div class="small text-muted mt-1">
                    Filtrar por estado del proceso:
                </div>
            </div>

            <!-- Filtros por Estado del Proceso -->
            <div class="d-flex flex-wrap align-items-center gap-2" role="group" aria-label="Filtro de estados de proceso">
                <button type="button" class="btn btn-sm btn-outline-secondary active btn-filtro-estado d-inline-flex align-items-center gap-1 shadow-sm rounded-pill px-3 py-1 fw-semibold" data-filtro="todos" title="Mostrar todos los casos">
                    <i class="fa-solid fa-layer-group"></i>
                    <span>Todos</span>
                    <span class="badge bg-secondary text-white rounded-pill ms-1" id="badgeTotal"><?= $cntTotal; ?></span>
                </button>
                <button type="button" class="btn btn-sm btn-outline-info text-dark btn-filtro-estado d-inline-flex align-items-center gap-1 shadow-sm rounded-pill px-3 py-1 fw-semibold" data-filtro="iniciado" title="Ver solo casos iniciados">
                    <i class="fa-solid fa-hourglass-start text-info"></i>
                    <span>Iniciados</span>
                    <span class="badge bg-info text-dark rounded-pill ms-1" id="badgeIniciados"><?= $cntIniciados; ?></span>
                </button>
                <button type="button" class="btn btn-sm btn-outline-warning text-dark btn-filtro-estado d-inline-flex align-items-center gap-1 shadow-sm rounded-pill px-3 py-1 fw-semibold" data-filtro="proceso" title="Ver casos en proceso">
                    <i class="fa-solid fa-arrows-rotate text-warning"></i>
                    <span>En Proceso</span>
                    <span class="badge bg-warning text-dark rounded-pill ms-1" id="badgeProceso"><?= $cntProceso; ?></span>
                </button>
                <button type="button" class="btn btn-sm btn-outline-success btn-filtro-estado d-inline-flex align-items-center gap-1 shadow-sm rounded-pill px-3 py-1 fw-semibold" data-filtro="finalizado" title="Ver casos finalizados o resueltos">
                    <i class="fa-solid fa-circle-check text-success"></i>
                    <span>Finalizados</span>
                    <span class="badge bg-success text-white rounded-pill ms-1" id="badgeFinalizados"><?= $cntFinalizados; ?></span>
                </button>
            </div>
        </div>
        <div class="card-body p-2 p-md-3">
            <div class="table-responsive w-100 rounded-3">
                <table id="tblBandejaSoporte" class="table table-hover align-middle w-100 mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="py-3 px-3">Servicio Soporte</th>
                            <th class="py-3 px-2 text-center" style="width: 110px;">Evidencia</th>
                            <th class="py-3 px-2 text-center" style="width: 100px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($dat)): ?>
                            <?php foreach ($dat as $dt): ?>
                                <?php
                                    $estadoId = (int) ($dt['ultimo_estado_id'] ?? 0);
                                    $estadoNombre = mb_strtolower(trim((string) ($dt['nom_ultimo_estado'] ?? '')));

                                    $stKey = 'iniciado';
                                    $badgeClass = 'bg-info text-dark';
                                    $iconClass = 'fa-hourglass-start';

                                    if ($estadoId === 1074 || strpos($estadoNombre, 'finaliz') !== false || strpos($estadoNombre, 'solucion') !== false || strpos($estadoNombre, 'cerrad') !== false) {
                                        $stKey = 'finalizado';
                                        $badgeClass = 'bg-success text-white';
                                        $iconClass = 'fa-check';
                                    } elseif ($estadoId === 1075 || strpos($estadoNombre, 'proceso') !== false) {
                                        $stKey = 'proceso';
                                        $badgeClass = 'bg-warning text-dark';
                                        $iconClass = 'fa-spinner fa-spin-pulse';
                                    } elseif ($estadoId === 1073 || strpos($estadoNombre, 'inicia') !== false) {
                                        $stKey = 'iniciado';
                                        $badgeClass = 'bg-info text-dark';
                                        $iconClass = 'fa-hourglass-start';
                                    } else {
                                        $stKey = !empty($estadoNombre) ? 'proceso' : 'iniciado';
                                        $badgeClass = 'bg-secondary text-white';
                                        $iconClass = 'fa-circle-question';
                                    }
                                ?>
                                <tr data-estado="<?= $stKey; ?>" data-estado-id="<?= $estadoId; ?>">
                                    <td class="px-3 py-3">
                                        <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                            <span class="badge bg-dark">#<?= htmlspecialchars((string)($dt["idsop"] ?? ''), ENT_QUOTES, 'UTF-8'); ?></span>
                                            <strong class="text-dark fs-6"><?= htmlspecialchars((string)(!empty($dt['nomper']) ? $dt['nomper'] : ($dt['nomusu'] ?? '')), ENT_QUOTES, 'UTF-8'); ?></strong>
                                        </div>

                                        <div class="small text-secondary mb-1">
                                            <i class="fa-solid fa-triangle-exclamation me-1 text-warning"></i><strong>Falla:</strong> <?= htmlspecialchars((string)($dt["nom_falrep"] ?? 'N/A'), ENT_QUOTES, 'UTF-8'); ?>
                                        </div>

                                        <div class="small text-secondary mb-1">
                                            <i class="fa-solid fa-user-tag me-1 text-info"></i><strong>Personal:</strong> <?= htmlspecialchars((string)($dt["nom_carper"] ?? 'N/A'), ENT_QUOTES, 'UTF-8'); ?> - <?= htmlspecialchars((string)(!empty($dt['nomper']) ? $dt['nomper'] : ($dt['nomusu'] ?? 'N/A')), ENT_QUOTES, 'UTF-8'); ?>
                                        </div>

                                        <?php if (!empty($dt['nomtecnico'])): ?>
                                            <div class="small text-secondary mb-1">
                                                <i class="fa-solid fa-headset me-1 text-primary"></i><strong>Técnico:</strong> <?= htmlspecialchars((string)$dt['nomtecnico'], ENT_QUOTES, 'UTF-8'); ?>
                                            </div>
                                        <?php endif; ?>

                                        <div class="small text-muted mb-2">
                                            <i class="fa-regular fa-calendar-days me-1 text-secondary"></i><?= htmlspecialchars((string)($dt["fecserini"] ?? ''), ENT_QUOTES, 'UTF-8'); ?> 
                                            <span class="mx-1">|</span> 
                                            <?= !empty($dt['fecserfin']) ? htmlspecialchars((string)$dt['fecserfin'], ENT_QUOTES, 'UTF-8') : '<span class="badge bg-warning text-dark">En proceso</span>'; ?>
                                        </div>

                                        <?php if (!empty($dt["desser"])): ?>
                                            <div class="text-secondary small text-break mb-2 p-2 bg-light rounded-2 border-start border-3 border-primary">
                                                <?= nl2br(htmlspecialchars((string)$dt["desser"], ENT_QUOTES, 'UTF-8')); ?>
                                            </div>
                                        <?php endif; ?>

                                        <div>
                                            <span class="badge <?= $badgeClass; ?> px-2 py-1">
                                                <i class="fa-solid <?= $iconClass; ?> me-1"></i>
                                                <?= htmlspecialchars((string)($dt["nom_ultimo_estado"] ?: 'Iniciado'), ENT_QUOTES, 'UTF-8'); ?>
                                            </span>
                                        </div>
                                    </td>

                                    <td class="text-center align-middle px-2 py-3">
                                        <?php
                                            $fotosSoporte = [];
                                            // 1. Evidencia fotográfica inicial
                                            if (!empty($dt['evisop'])) {
                                                $evisExplode = explode(',', (string) $dt['evisop']);
                                                foreach ($evisExplode as $evItem) {
                                                    $resRuta = $resolverRutaFoto($evItem);
                                                    if ($resRuta && !in_array($resRuta, $fotosSoporte)) {
                                                        $fotosSoporte[] = $resRuta;
                                                    }
                                                }
                                            }
                                            // 2. Evidencias fotográficas de seguimientos y avances
                                            $curIdSop = (int) ($dt['idsop'] ?? 0);
                                            if (!empty($todasEvidencias[$curIdSop])) {
                                                foreach ($todasEvidencias[$curIdSop] as $evDetItem) {
                                                    if (!in_array($evDetItem, $fotosSoporte)) {
                                                        $fotosSoporte[] = $evDetItem;
                                                    }
                                                }
                                            }
                                            $totalFotos = count($fotosSoporte);
                                        ?>
                                        <?php if ($totalFotos === 0): ?>
                                            <span class="text-muted small d-inline-flex align-items-center gap-1"><i class="fa-regular fa-circle-question opacity-50"></i>Sin foto</span>
                                        <?php elseif ($totalFotos === 1): ?>
                                            <img src="<?= htmlspecialchars($fotosSoporte[0], ENT_QUOTES, 'UTF-8'); ?>" 
                                                 alt="Evidencia" 
                                                 class="img-thumbnail rounded-3 shadow-sm mes-trigger-modal" 
                                                 style="width: 75px; height: 50px; object-fit: cover; cursor: pointer;"
                                                 data-src="<?= htmlspecialchars($fotosSoporte[0], ENT_QUOTES, 'UTF-8'); ?>"
                                                 title="Clic para ampliar foto de evidencia">
                                        <?php else: ?>
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-warning text-dark border-warning-subtle bg-light d-inline-flex flex-column align-items-center justify-content-center p-1 rounded-3 shadow-sm mes-trigger-album position-relative" 
                                                    style="width: 75px; height: 50px;"
                                                    data-fotos='<?= htmlspecialchars(json_encode($fotosSoporte), ENT_QUOTES, 'UTF-8'); ?>'
                                                    data-idsop="<?= htmlspecialchars((string)($dt['idsop'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                                                    title="Ver álbum de <?= $totalFotos; ?> evidencias">
                                                <i class="fa-solid fa-folder-open text-warning fs-5"></i>
                                                <span class="badge bg-dark text-white rounded-pill px-2 py-0 mt-1" style="font-size: 0.65rem;">
                                                    <i class="fa-solid fa-images me-1"></i><?= $totalFotos; ?>
                                                </span>
                                            </button>
                                        <?php endif; ?>
                                    </td>

                                    <td class="text-center text-nowrap align-middle px-2 py-3">
                                        <a href="home.php?pg=2204&idsop=<?= htmlspecialchars((string)($dt["idsop"] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" 
                                           class="btn btn-sm btn-outline-primary shadow-sm me-1" 
                                           title="Ver Seguimiento">
                                            <i class="fa-solid fa-magnifying-glass"></i>
                                        </a>
                                        <?php if (isset($_SESSION['idper']) && (int)$_SESSION['idper'] === 38): ?>
                                            <a href="home.php?pg=<?= htmlspecialchars((string)($pg ?? '2202'), ENT_QUOTES, 'UTF-8'); ?>&idsop=<?= htmlspecialchars((string)($dt['idsop'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>&ope=del" 
                                               class="btn btn-sm btn-outline-danger shadow-sm" 
                                               title="Eliminar" 
                                               onclick="return confirm('¿Desea eliminar este registro de soporte?');">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr id="filaSinRegistros">
                                <td colspan="3" class="text-center text-muted py-4">
                                    <i class="fa-solid fa-circle-info fa-lg me-1 text-primary"></i> No hay registros de soporte disponibles.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Nativo de Bootstrap para Previsualización de Evidencias (Individual y Carpeta de Fotos) -->
<div class="modal fade" id="mesModalEvidencia" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-dark border-0 shadow-lg text-white">
            <div class="modal-header border-0 pb-1 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <h6 class="modal-title text-white mb-0" id="mesModalTituloContenedor">
                        <i class="fa-solid fa-image me-2 text-primary" id="mesModalIcono"></i>
                        <span id="mesModalTitulo">Foto Evidencia de Soporte</span>
                    </h6>
                    <span id="mesModalContador" class="badge bg-secondary rounded-pill px-2 py-1 small" style="display: none;">1 / 1</span>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-2 p-md-3 text-center position-relative">
                <!-- Botón Foto Anterior -->
                <button type="button" id="mesBtnPrevFoto" 
                        class="btn btn-dark bg-opacity-75 text-white position-absolute start-0 top-50 translate-middle-y ms-2 ms-md-4 rounded-circle border-0 shadow d-inline-flex align-items-center justify-content-center" 
                        style="width: 44px; height: 44px; z-index: 10; display: none;" 
                        title="Foto anterior">
                    <i class="fa-solid fa-chevron-left fs-5"></i>
                </button>

                <!-- Imagen Principal -->
                <div class="d-flex align-items-center justify-content-center" style="min-height: 250px; max-height: 65vh;">
                    <img id="mesModalImg" src="" alt="Evidencia Ampliada" class="img-fluid rounded-3 shadow" style="max-height: 65vh; max-width: 100%; object-fit: contain;">
                </div>

                <!-- Botón Siguiente Foto -->
                <button type="button" id="mesBtnNextFoto" 
                        class="btn btn-dark bg-opacity-75 text-white position-absolute end-0 top-50 translate-middle-y me-2 me-md-4 rounded-circle border-0 shadow d-inline-flex align-items-center justify-content-center" 
                        style="width: 44px; height: 44px; z-index: 10; display: none;" 
                        title="Siguiente foto">
                    <i class="fa-solid fa-chevron-right fs-5"></i>
                </button>

                <!-- Tira de Miniaturas para Navegación en Álbum/Carpeta -->
                <div id="mesModalThumbnails" class="d-flex justify-content-center align-items-center gap-2 mt-3 p-2 bg-black bg-opacity-50 rounded-3 overflow-auto" style="display: none; max-width: 100%;">
                    <!-- Se generan dinámicamente con JavaScript -->
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    // 1. Previsualización de Evidencias en Modal (Individual y Álbum por Carpeta)
    const modalElement = document.getElementById('mesModalEvidencia');
    const modalImg = document.getElementById('mesModalImg');
    const modalTitulo = document.getElementById('mesModalTitulo');
    const modalIcono = document.getElementById('mesModalIcono');
    const modalContador = document.getElementById('mesModalContador');
    const btnPrev = document.getElementById('mesBtnPrevFoto');
    const btnNext = document.getElementById('mesBtnNextFoto');
    const thumbnailsContainer = document.getElementById('mesModalThumbnails');

    let fotosActuales = [];
    let fotoIndexActual = 0;

    function abrirModal() {
        if (modalElement && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
            modal.show();
        } else if (window.jQuery && $('#mesModalEvidencia').modal) {
            $('#mesModalEvidencia').modal('show');
        }
    }

    function renderFotoActual() {
        if (!fotosActuales.length) return;
        const src = fotosActuales[fotoIndexActual];
        if (modalImg) modalImg.src = src;

        if (fotosActuales.length > 1) {
            if (modalContador) {
                modalContador.textContent = (fotoIndexActual + 1) + ' / ' + fotosActuales.length;
                modalContador.style.display = 'inline-block';
            }
            if (btnPrev) btnPrev.style.display = 'inline-flex';
            if (btnNext) btnNext.style.display = 'inline-flex';
            if (thumbnailsContainer) {
                thumbnailsContainer.style.display = 'flex';
                const thumbs = thumbnailsContainer.querySelectorAll('img');
                thumbs.forEach((th, idx) => {
                    if (idx === fotoIndexActual) {
                        th.classList.add('border-warning');
                        th.style.borderWidth = '2px';
                        th.style.opacity = '1';
                    } else {
                        th.classList.remove('border-warning');
                        th.style.borderWidth = '1px';
                        th.style.opacity = '0.55';
                    }
                });
            }
        } else {
            if (modalContador) modalContador.style.display = 'none';
            if (btnPrev) btnPrev.style.display = 'none';
            if (btnNext) btnNext.style.display = 'none';
            if (thumbnailsContainer) {
                thumbnailsContainer.innerHTML = '';
                thumbnailsContainer.style.display = 'none';
            }
        }
    }

    function construirMiniaturas() {
        if (!thumbnailsContainer) return;
        thumbnailsContainer.innerHTML = '';
        if (fotosActuales.length <= 1) return;

        fotosActuales.forEach((fotoSrc, idx) => {
            const thumb = document.createElement('img');
            thumb.src = fotoSrc;
            thumb.className = 'img-thumbnail rounded border';
            thumb.style.width = '55px';
            thumb.style.height = '40px';
            thumb.style.objectFit = 'cover';
            thumb.style.cursor = 'pointer';
            thumb.style.transition = 'all 0.2s ease';
            thumb.title = 'Ver foto ' + (idx + 1);

            thumb.addEventListener('click', function () {
                fotoIndexActual = idx;
                renderFotoActual();
            });

            thumbnailsContainer.appendChild(thumb);
        });
    }

    // Clic en foto individual
    document.querySelectorAll('.mes-trigger-modal').forEach(img => {
        img.addEventListener('click', function () {
            const src = this.getAttribute('data-src') || this.src;
            fotosActuales = [src];
            fotoIndexActual = 0;

            if (modalTitulo) modalTitulo.textContent = 'Foto Evidencia de Soporte';
            if (modalIcono) {
                modalIcono.className = 'fa-solid fa-image me-2 text-primary';
            }

            renderFotoActual();
            abrirModal();
        });
    });

    // Clic en carpetica con múltiples fotos
    document.querySelectorAll('.mes-trigger-album').forEach(btn => {
        btn.addEventListener('click', function () {
            try {
                const fotosData = this.getAttribute('data-fotos');
                fotosActuales = JSON.parse(fotosData) || [];
            } catch (err) {
                fotosActuales = [];
            }

            if (!fotosActuales.length) return;

            const idSop = this.getAttribute('data-idsop') || '';
            fotoIndexActual = 0;

            if (modalTitulo) modalTitulo.textContent = 'Carpeta de Evidencias - Caso #' + idSop;
            if (modalIcono) {
                modalIcono.className = 'fa-solid fa-folder-open me-2 text-warning';
            }

            construirMiniaturas();
            renderFotoActual();
            abrirModal();
        });
    });

    // Navegación con botones Prev / Next
    if (btnPrev) {
        btnPrev.addEventListener('click', function (e) {
            e.stopPropagation();
            if (fotosActuales.length > 1) {
                fotoIndexActual = (fotoIndexActual - 1 + fotosActuales.length) % fotosActuales.length;
                renderFotoActual();
            }
        });
    }

    if (btnNext) {
        btnNext.addEventListener('click', function (e) {
            e.stopPropagation();
            if (fotosActuales.length > 1) {
                fotoIndexActual = (fotoIndexActual + 1) % fotosActuales.length;
                renderFotoActual();
            }
        });
    }

    // Navegación con teclado (Flechas izquierda / derecha) al estar el modal abierto
    document.addEventListener('keydown', function (e) {
        if (!modalElement || !modalElement.classList.contains('show') || fotosActuales.length <= 1) return;
        if (e.key === 'ArrowLeft') {
            fotoIndexActual = (fotoIndexActual - 1 + fotosActuales.length) % fotosActuales.length;
            renderFotoActual();
        } else if (e.key === 'ArrowRight') {
            fotoIndexActual = (fotoIndexActual + 1) % fotosActuales.length;
            renderFotoActual();
        }
    });

    // 2. Filtro interactivo de Estados sin DataTables
    const botonesFiltro = document.querySelectorAll('.btn-filtro-estado');
    const filas = document.querySelectorAll('#tblBandejaSoporte tbody tr:not(#filaSinRegistros)');

    botonesFiltro.forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            botonesFiltro.forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            const filtro = this.getAttribute('data-filtro') || 'todos';
            let visibles = 0;

            filas.forEach(fila => {
                const estado = fila.getAttribute('data-estado');
                if (filtro === 'todos' || estado === filtro) {
                    fila.style.display = '';
                    visibles++;
                } else {
                    fila.style.display = 'none';
                }
            });

            // Mensaje cuando no hay registros para el filtro seleccionado
            let aviso = document.getElementById('filaSinCasosFiltrados');
            const tbody = document.querySelector('#tblBandejaSoporte tbody');
            if (visibles === 0 && filas.length > 0 && tbody) {
                if (!aviso) {
                    aviso = document.createElement('tr');
                    aviso.id = 'filaSinCasosFiltrados';
                    aviso.innerHTML = `<td colspan="3" class="text-center text-muted py-4"><i class="fa-solid fa-circle-info me-1 text-primary"></i> No hay casos con el estado seleccionado.</td>`;
                    tbody.appendChild(aviso);
                }
                aviso.style.display = '';
            } else if (aviso) {
                aviso.style.display = 'none';
            }
        });
    });
});
</script>