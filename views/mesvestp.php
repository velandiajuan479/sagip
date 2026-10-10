 <?php require_once 'controllers/mescestp.php'; ?> 

<div class="conte">
    <!-- Título de la sección -->
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-3 mb-md-4 gap-2">
        <div>
            <?= titulo2("<i class='" . ($icono ?? 'fa-solid fa-chart-line') . " me-2 text-primary'></i> Reporte Estadístico Persona", 1); ?>
            <p class="text-muted small mb-0 mt-1">Panel de control de indicadores y solicitudes de soporte técnico por personal.</p>
        </div>
    </div>

    <!-- Indicadores Rápidos KPI -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-4">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold text-muted small">Total Soportes</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold fs-2 text-dark" id="kpiTotal">0</span>
                            <span class="small text-muted">registros</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box bg-primary text-white">
                        <i class="fa-solid fa-headset fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="fw-semibold text-primary d-inline-flex align-items-center gap-1 small">
                        <i class="fa-solid fa-layer-group"></i> Total Filtrado
                    </span>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-4">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold text-warning small">En Proceso</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold fs-2 text-dark" id="kpiProceso">0</span>
                            <span class="small text-muted">pendientes</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box bg-warning text-dark">
                        <i class="fa-solid fa-clock-rotate-left fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="fw-semibold text-warning d-inline-flex align-items-center gap-2 small">
                        <span class="pulse-dot"></span> En Atención
                    </span>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-4">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold text-success small">Finalizados / Solucionados</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold fs-2 text-dark" id="kpiFinalizado">0</span>
                            <span class="small text-muted">resueltos</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box bg-success text-white">
                        <i class="fa-solid fa-circle-check fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="fw-semibold text-success d-inline-flex align-items-center gap-2 small">
                        <span class="pulse-dot"></span> Solucionados
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros de búsqueda responsivos -->
    <div class="card shadow-sm border-0 rounded-4 mb-4">
        <div class="card-body p-3 p-md-4">
            <div class="row g-3 align-items-end">
                <!-- Filtro MVC por Periodo -->
                <div class="col-12 col-sm-6 col-lg-3">
                    <label for="filterSelect" class="form-label fw-semibold text-secondary small text-uppercase">
                        <i class="fa-regular fa-calendar-days me-1 text-primary"></i> Tiempo:
                    </label>
                    <select id="filterSelect" class="form-select shadow-none">
                        <option value="">Todos los periodos</option>
                        <option value="day">Día</option>
                        <option value="month">Mes</option>
                        <option value="year">Año</option>
                    </select>
                </div>
                <!-- Filtro MVC por Empleado -->
                <div class="col-12 col-sm-6 col-lg-3">
                    <label for="empleadoSelect" class="form-label fw-semibold text-secondary small text-uppercase">
                        <i class="fa-solid fa-user-tie me-1 text-primary"></i> Empleado:
                    </label>
                    <select id="empleadoSelect" class="form-select shadow-none">
                        <option value="">Todos los empleados</option>
                        <?php if (!empty($dtEmpleados)): ?>
                            <?php foreach ($dtEmpleados as $emp): ?>
                                <?php 
                                    $idEmp = $emp['idusu'] ?? $emp['id'] ?? $emp['idper'] ?? '';
                                    $nombreEmp = $emp['nomusu'] ?? $emp['nomper'] ?? $emp['nombre'] ?? '';
                                ?>
                                <option value="<?= htmlspecialchars((string)$idEmp, ENT_QUOTES, 'UTF-8'); ?>">
                                    <?= htmlspecialchars((string)$nombreEmp, ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <!-- Filtro Cliente Adicional: Búsqueda por texto en tiempo real -->
                <div class="col-12 col-sm-6 col-lg-3">
                    <label for="searchInput" class="form-label fw-semibold text-secondary small text-uppercase">
                        <i class="fa-solid fa-magnifying-glass me-1 text-primary"></i> Buscar palabra clave:
                    </label>
                    <input type="search" id="searchInput" class="form-control shadow-none" placeholder="Buscar por falla, nombre, #ID...">
                </div>
                <!-- Filtro Cliente Adicional: Estado del soporte + Reset -->
                <div class="col-12 col-sm-6 col-lg-3 d-flex gap-2">
                    <div class="flex-grow-1">
                        <label for="estadoSelect" class="form-label fw-semibold text-secondary small text-uppercase">
                            <i class="fa-solid fa-filter me-1 text-primary"></i> Estado:
                        </label>
                        <select id="estadoSelect" class="form-select shadow-none">
                            <option value="">Todos los estados</option>
                            <option value="proceso">En proceso</option>
                            <option value="finalizado">Finalizado / Solucionado</option>
                        </select>
                    </div>
                    <div class="align-self-end">
                        <button type="button" id="btnResetFilters" class="btn btn-outline-secondary shadow-none px-3" title="Limpiar filtros">
                            <i class="fa-solid fa-rotate-left"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos estadísticos fluidos y adaptativos -->
    <div class="row g-3 g-lg-4 mb-4 mb-lg-5">
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card shadow-sm border-0 rounded-4 h-100">
                <div class="card-header bg-transparent border-0 pt-3 px-3 pb-0">
                    <h6 class="fw-bold text-dark mb-0"><i class="fa-solid fa-chart-pie me-2 text-primary"></i>Soportes por Falla</h6>
                </div>
                <div class="card-body p-3 text-center">
                    <div class="position-relative w-100 h-100 min-vh-25">
                        <canvas id="graficoSoporte"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card shadow-sm border-0 rounded-4 h-100">
                <div class="card-header bg-transparent border-0 pt-3 px-3 pb-0">
                    <h6 class="fw-bold text-dark mb-0"><i class="fa-solid fa-chart-column me-2 text-primary"></i>Soportes por Cargo</h6>
                </div>
                <div class="card-body p-3 text-center">
                    <div class="position-relative w-100 h-100 min-vh-25">
                        <canvas id="graficoCargo"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-12 col-lg-4">
            <div class="card shadow-sm border-0 rounded-4 h-100">
                <div class="card-header bg-transparent border-0 pt-3 px-3 pb-0">
                    <h6 class="fw-bold text-dark mb-0"><i class="fa-solid fa-chart-pie me-2 text-primary"></i>Soportes por Usuario</h6>
                </div>
                <div class="card-body p-3 text-center">
                    <div class="position-relative w-100 h-100 min-vh-25">
                        <canvas id="graficoUsuario"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de resultados optimizada para móviles y desktop -->
    <div class="card shadow-sm border-0 rounded-4 mb-4">
        <div class="card-header bg-transparent border-0 pt-3 px-3 px-md-4 pb-0 d-flex align-items-center justify-content-between">
            <h5 class="fw-bold text-dark mb-0">
                <i class="fa-solid fa-list-check me-2 text-primary"></i>Listado de Solicitudes
            </h5>
            <span class="badge bg-light text-secondary border px-2 py-1" id="contadorResultados">0 registros</span>
        </div>
        <div class="card-body p-2 p-md-4">
            <div class="table-responsive w-100 rounded-3">
                <table id="example" class="table table-hover align-middle w-100 mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="py-3 px-3">Servicio Soporte</th>
                            <th class="py-3 px-2 text-center">Evidencia</th>
                            <th class="py-3 px-2 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            $todasEvidenciasEst = [];
                            if (!empty($dtRppr) && class_exists('conexion')) {
                                try {
                                    $arrIdsop = [];
                                    foreach ($dtRppr as $dItem) {
                                        if (!empty($dItem['idsop'])) {
                                            $arrIdsop[] = (int) $dItem['idsop'];
                                        }
                                    }
                                    if (!empty($arrIdsop)) {
                                        $inIds = implode(',', array_unique($arrIdsop));
                                        $cnxEst = (new conexion())->get_conexion();
                                        // 1. soporte_evidencia
                                        try {
                                            $stmtEviMulti = $cnxEst->query("SELECT idsop, ruta FROM soporte_evidencia WHERE idsop IN ($inIds) AND ruta IS NOT NULL AND ruta != '' ORDER BY idevi ASC");
                                            if ($stmtEviMulti) {
                                                while ($rM = $stmtEviMulti->fetch(PDO::FETCH_ASSOC)) {
                                                    $idS = (int) $rM['idsop'];
                                                    $ruta = trim((string) $rM['ruta']);
                                                    if ($ruta !== '') {
                                                        $baseR = basename($ruta);
                                                        if (file_exists('img/fevi/' . $baseR)) {
                                                            $todasEvidenciasEst[$idS][] = 'img/fevi/' . $baseR;
                                                        } elseif (file_exists($ruta)) {
                                                            $todasEvidenciasEst[$idS][] = $ruta;
                                                        }
                                                    }
                                                }
                                            }
                                        } catch (Exception $e) {}
                                        // 2. detalle_soporte
                                        try {
                                            $stmtEviDet = $cnxEst->query("SELECT idsop, detevi FROM detalle_soporte WHERE idsop IN ($inIds) AND detevi IS NOT NULL AND detevi != '' ORDER BY iddet ASC");
                                            if ($stmtEviDet) {
                                                while ($rEvi = $stmtEviDet->fetch(PDO::FETCH_ASSOC)) {
                                                    $idS = (int) $rEvi['idsop'];
                                                    $pathEvi = trim((string) $rEvi['detevi']);
                                                    if ($pathEvi !== '') {
                                                        $baseEvi = basename($pathEvi);
                                                        if (file_exists('img/fevi/' . $baseEvi)) {
                                                            $todasEvidenciasEst[$idS][] = 'img/fevi/' . $baseEvi;
                                                        } elseif (file_exists($pathEvi)) {
                                                            $todasEvidenciasEst[$idS][] = $pathEvi;
                                                        }
                                                    }
                                                }
                                            }
                                        } catch (Exception $e) {}
                                    }
                                } catch (Exception $e) {}
                            }
                        ?>
                        <?php if (!empty($dtRppr)): ?>
                            <?php foreach ($dtRppr as $dtrp): ?>
                                <?php 
                                    $estadoNombre = $dtrp["nom_ultimo_estado"] ?? '';
                                    $isFinalizado = (mb_stripos($estadoNombre, 'solucion') !== false || mb_stripos($estadoNombre, 'finaliz') !== false || mb_stripos($estadoNombre, 'cerrad') !== false);
                                ?>
                                <tr data-empleado="<?= htmlspecialchars((string)($dtrp['idusu'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                                    data-estado="<?= $isFinalizado ? 'finalizado' : 'proceso'; ?>">
                                    <td class="px-3 py-3">
                                        <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                            <span class="badge bg-dark">#<?= htmlspecialchars((string)($dtrp["idsop"] ?? ''), ENT_QUOTES, 'UTF-8'); ?></span>
                                            <strong class="text-dark fs-6"><?= htmlspecialchars((string)($dtrp["nomusu"] ?? ''), ENT_QUOTES, 'UTF-8'); ?></strong>
                                        </div>
                                        <div class="small text-secondary mb-1">
                                            <i class="fa-solid fa-triangle-exclamation me-1 text-warning"></i><strong>Falla:</strong> <?= htmlspecialchars((string)($dtrp["nom_falrep"] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                                        </div>
                                        <div class="small text-secondary mb-1">
                                            <i class="fa-solid fa-user-tag me-1 text-info"></i><strong>Personal:</strong> <?= htmlspecialchars((string)($dtrp["nom_carper"] ?? ''), ENT_QUOTES, 'UTF-8'); ?> - <?= htmlspecialchars((string)($dtrp["nomper"] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                                        </div> 
                                        <div class="small text-muted mb-2">
                                            <i class="fa-regular fa-calendar-days me-1 text-secondary"></i><?= htmlspecialchars((string)($dtrp["fecserini"] ?? ''), ENT_QUOTES, 'UTF-8'); ?> 
                                            <span class="mx-1">|</span> 
                                            <?= !empty($dtrp['fecserfin']) ? htmlspecialchars((string)$dtrp['fecserfin'], ENT_QUOTES, 'UTF-8') : '<span class="badge bg-warning text-dark">En proceso</span>'; ?>
                                        </div>
                                        <?php if (!empty($dtrp["desser"])): ?>
                                            <div class="text-secondary small text-break mb-2 p-2 bg-light rounded-2 border-start border-3 border-primary">
                                                <?= htmlspecialchars((string)($dtrp["desser"] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <span class="badge <?= $isFinalizado ? 'bg-success' : 'bg-info text-dark'; ?> px-2 py-1">
                                                <i class="<?= $isFinalizado ? 'fa-solid fa-check me-1' : 'fa-solid fa-spinner fa-spin-pulse me-1'; ?>"></i>
                                                <?= htmlspecialchars((string)($dtrp["nom_ultimo_estado"] ?? 'En proceso'), ENT_QUOTES, 'UTF-8'); ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td class="text-center align-middle px-2 py-3">
                                        <?php
                                            $fotosSoporte = [];
                                            if (!empty($dtrp['evisop'])) {
                                                $rutasIni = explode(',', (string)$dtrp['evisop']);
                                                foreach ($rutasIni as $rIni) {
                                                    $rIni = trim($rIni);
                                                    if ($rIni !== '') {
                                                        $baseIni = basename($rIni);
                                                        $rFoto = null;
                                                        if (file_exists('img/fevi/' . $baseIni)) {
                                                            $rFoto = 'img/fevi/' . $baseIni;
                                                        } elseif (file_exists($rIni)) {
                                                            $rFoto = $rIni;
                                                        }
                                                        if ($rFoto && !in_array($rFoto, $fotosSoporte)) {
                                                            $fotosSoporte[] = $rFoto;
                                                        }
                                                    }
                                                }
                                            }
                                            $curIdSop = (int)($dtrp['idsop'] ?? 0);
                                            if (!empty($todasEvidenciasEst[$curIdSop])) {
                                                foreach ($todasEvidenciasEst[$curIdSop] as $evDet) {
                                                    if (!in_array($evDet, $fotosSoporte)) {
                                                        $fotosSoporte[] = $evDet;
                                                    }
                                                }
                                            }
                                            $totalFotos = count($fotosSoporte);
                                        ?>
                                        <?php if ($totalFotos === 0): ?>
                                            <span class="text-muted small d-block"><i class="fa-solid fa-image-slash me-1 opacity-50"></i>Sin foto</span>
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
                                                    data-fotos='<?= htmlspecialchars(json_encode(array_values($fotosSoporte)), ENT_QUOTES, 'UTF-8'); ?>'
                                                    data-idsop="<?= htmlspecialchars((string)($dtrp['idsop'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                                                    title="Ver carpeta de <?= $totalFotos; ?> evidencias">
                                                <i class="fa-solid fa-folder-open text-warning fs-5"></i>
                                                <span class="badge bg-dark text-white rounded-pill px-2 py-0 mt-1" style="font-size: 0.65rem;">
                                                    <i class="fa-solid fa-images me-1"></i><?= $totalFotos; ?> fotos
                                                </span>
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center text-nowrap align-middle px-2 py-3">
                                        <a href="home.php?pg=2204&idsop=<?= htmlspecialchars((string)($dtrp["idsop"] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" 
                                           class="btn btn-sm btn-outline-primary shadow-sm me-1" 
                                           title="Ver Seguimiento">
                                            <i class="fa-solid fa-magnifying-glass"></i>
                                        </a>
                                        <?php if (isset($_SESSION['idper']) && $_SESSION['idper'] == 38): ?>
                                            <a href="home.php?pg=<?= htmlspecialchars((string)($pg ?? ''), ENT_QUOTES, 'UTF-8'); ?>&idsop=<?= htmlspecialchars((string)($dtrp['idsop'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>&ope=del" 
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

<!-- Modal Nativo de Bootstrap para Evidencias (Individual y Carpeta de Fotos) -->
<div class="modal fade" id="mesModalEvidencia" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-dark border-0 shadow-lg text-white">
            <div class="modal-header border-0 pb-1 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <h6 class="modal-title text-white mb-0">
                        <i class="fa-solid fa-image me-2 text-primary" id="mesModalIcono"></i>
                        <span id="mesModalTitulo">Evidencia de Soporte</span>
                    </h6>
                    <span id="mesModalContador" class="badge bg-secondary rounded-pill px-2 py-1 small" style="display: none;">1 / 1</span>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-2 p-md-3 text-center position-relative">
                <button type="button" id="mesBtnPrevFoto" 
                        class="btn btn-dark bg-opacity-75 text-white position-absolute start-0 top-50 translate-middle-y ms-2 ms-md-4 rounded-circle border-0 shadow d-inline-flex align-items-center justify-content-center" 
                        style="width: 44px; height: 44px; z-index: 10; display: none;" 
                        title="Foto anterior">
                    <i class="fa-solid fa-chevron-left fs-5"></i>
                </button>
                <div class="d-flex align-items-center justify-content-center" style="min-height: 250px; max-height: 65vh;">
                    <img id="mesModalImg" src="" alt="Evidencia Ampliada" class="img-fluid rounded-3 shadow" style="max-height: 65vh; max-width: 100%; object-fit: contain;">
                </div>
                <button type="button" id="mesBtnNextFoto" 
                        class="btn btn-dark bg-opacity-75 text-white position-absolute end-0 top-50 translate-middle-y me-2 me-md-4 rounded-circle border-0 shadow d-inline-flex align-items-center justify-content-center" 
                        style="width: 44px; height: 44px; z-index: 10; display: none;" 
                        title="Siguiente foto">
                    <i class="fa-solid fa-chevron-right fs-5"></i>
                </button>
                <div id="mesModalThumbnails" class="d-flex justify-content-center align-items-center gap-2 mt-3 p-2 bg-black bg-opacity-50 rounded-3 overflow-auto" style="display: none; max-width: 100%;"></div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const charts = {};
    const colores = ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#6f42c1', '#fd7e14', '#20c997'];

    const filterSelect = document.getElementById('filterSelect');
    const empleadoSelect = document.getElementById('empleadoSelect');
    const searchInput = document.getElementById('searchInput');
    const estadoSelect = document.getElementById('estadoSelect');
    const btnResetFilters = document.getElementById('btnResetFilters');

    /**
     * Cargar y actualizar gráficos dinámicos con Chart.js
     */
    function cargarGrafico(endpoint, canvasId, type, titleText, customOptions = {}) {
        const filterVal = filterSelect ? filterSelect.value : '';
        const empleadoVal = empleadoSelect ? empleadoSelect.value : '';

        const url = new URL(endpoint, window.location.href);
        if (filterVal) url.searchParams.set('filter', filterVal);
        if (empleadoVal) url.searchParams.set('empleado', empleadoVal);

        fetch(url.toString())
            .then(res => {
                if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);
                return res.json();
            })
            .then(data => {
                const items = Array.isArray(data) ? data : [];
                const labels = items.map(i => i.falla || i.cargo || i.usuario || 'N/D');
                const valores = items.map(i => Number(i.total) || 0);

                if (charts[canvasId]) {
                    charts[canvasId].data.labels = labels;
                    charts[canvasId].data.datasets[0].data = valores;
                    charts[canvasId].update();
                } else {
                    const ctx = document.getElementById(canvasId);
                    if (!ctx) return;

                    const defaultOptions = {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } }
                        }
                    };

                    charts[canvasId] = new Chart(ctx, {
                        type: type,
                        data: {
                            labels: labels,
                            datasets: [{
                                label: titleText,
                                data: valores,
                                backgroundColor: colores
                            }]
                        },
                        options: Object.assign({}, defaultOptions, customOptions)
                    });
                }
            })
            .catch(err => console.error(`Error al cargar gráfico [${canvasId}]:`, err));
    }

    /**
     * Filtra la tabla multicriterio (Empleado, Estado, Palabra Clave) y actualiza contadores KPI
     */
    function filtrarTabla() {
        const empleadoVal = empleadoSelect ? empleadoSelect.value : '';
        const searchVal = searchInput ? searchInput.value.toLowerCase().trim() : '';
        const estadoVal = estadoSelect ? estadoSelect.value : '';

        const tbody = document.querySelector('#example tbody');
        if (!tbody) return;

        const filas = tbody.querySelectorAll('tr:not(#filaSinRegistros):not(.fila-aviso)');
        let filasVisibles = 0;
        let cntProceso = 0;
        let cntFinalizado = 0;

        filas.forEach(fila => {
            const filaEmp = fila.getAttribute('data-empleado') || '';
            const filaEst = fila.getAttribute('data-estado') || '';
            const textoFila = fila.textContent.toLowerCase();

            const matchEmp = !empleadoVal || filaEmp === empleadoVal;
            const matchEst = !estadoVal || filaEst === estadoVal;
            const matchSearch = !searchVal || textoFila.includes(searchVal);

            const visible = matchEmp && matchEst && matchSearch;
            fila.style.display = visible ? '' : 'none';

            if (visible) {
                filasVisibles++;
                if (filaEst === 'finalizado') {
                    cntFinalizado++;
                } else {
                    cntProceso++;
                }
            }
        });

        // Actualizar tarjetas de KPI
        const kpiTotal = document.getElementById('kpiTotal');
        const kpiProceso = document.getElementById('kpiProceso');
        const kpiFinalizado = document.getElementById('kpiFinalizado');
        const contadorResultados = document.getElementById('contadorResultados');

        if (kpiTotal) kpiTotal.textContent = filasVisibles;
        if (kpiProceso) kpiProceso.textContent = cntProceso;
        if (kpiFinalizado) kpiFinalizado.textContent = cntFinalizado;
        if (contadorResultados) contadorResultados.textContent = `${filasVisibles} ${filasVisibles === 1 ? 'registro' : 'registros'}`;

        // Mostrar u ocultar mensaje sin resultados
        let aviso = tbody.querySelector('.fila-aviso');
        if (filasVisibles === 0 && filas.length > 0) {
            if (!aviso) {
                aviso = document.createElement('tr');
                aviso.className = 'fila-aviso';
                aviso.innerHTML = `<td colspan="3" class="text-center text-muted py-4"><i class="fa-solid fa-magnifying-glass me-1 text-secondary"></i> No se encontraron resultados que coincidan con la búsqueda.</td>`;
                tbody.appendChild(aviso);
            }
            aviso.style.display = '';
        } else if (aviso) {
            aviso.style.display = 'none';
        }
    }

    /**
     * Actualizar Dashboard (gráficos + tabla)
     */
    function actualizarDashboard() {
        cargarGrafico('controllers/mescestp.php?grafico=por_falla', 'graficoSoporte', 'pie', 'Soportes por Falla');
        cargarGrafico('controllers/mescestp.php?grafico=por_cargo', 'graficoCargo', 'bar', 'Soportes por Cargo', {
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
        });
        cargarGrafico('controllers/mescestp.php?grafico=por_usuario', 'graficoUsuario', 'doughnut', 'Soportes por Usuario');
        
        filtrarTabla();
    }

    // Listeners de los filtros
    if (filterSelect) filterSelect.addEventListener('change', actualizarDashboard);
    if (empleadoSelect) empleadoSelect.addEventListener('change', actualizarDashboard);
    if (searchInput) searchInput.addEventListener('input', filtrarTabla);
    if (estadoSelect) estadoSelect.addEventListener('change', filtrarTabla);

    if (btnResetFilters) {
        btnResetFilters.addEventListener('click', function () {
            if (filterSelect) filterSelect.value = '';
            if (empleadoSelect) empleadoSelect.value = '';
            if (searchInput) searchInput.value = '';
            if (estadoSelect) estadoSelect.value = '';
            actualizarDashboard();
        });
    }

    // Inicializar dashboard
    actualizarDashboard();

    // Visualizador de imagen y carpeta usando Modal Nativo de Bootstrap
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

            if (modalTitulo) modalTitulo.textContent = 'Evidencia de Soporte';
            if (modalIcono) modalIcono.className = 'fa-solid fa-image me-2 text-primary';

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
            if (modalIcono) modalIcono.className = 'fa-solid fa-folder-open me-2 text-warning';

            construirMiniaturas();
            renderFotoActual();
            abrirModal();
        });
    });

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
});
</script>