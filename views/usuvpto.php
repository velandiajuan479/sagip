<?php
require_once("controllers/usucpto.php");
$idperActual = isset($_SESSION["idper"]) ? intval($_SESSION["idper"]) : (isset($_SESSION["pefid"]) ? intval($_SESSION["pefid"]) : 1);
$idusuActual = isset($_SESSION["idusu"]) ? intval($_SESSION["idusu"]) : 0;
$ndocActual  = isset($_SESSION["ndocusu"]) ? $_SESSION["ndocusu"] : '';
$nomusuActual= isset($_SESSION["nomusu"]) ? $_SESSION["nomusu"] : 'Usuario';

$perfilesAprendiz = [3, 4, 8, 13, 22, 23, 39, 41];
$esAprendiz   = in_array($idperActual, $perfilesAprendiz);
$esEmpresario = ($idperActual === 35);
$esInstructor = (!$esAprendiz && !$esEmpresario);
?>
<div class="conte">
    <?php echo titulo2("<i class='".$icono."'></i> Gestión y Seguimiento de Patrocinio", 1); ?>

    <!-- ======================================================= -->
    <!-- CASO 1: PERFIL APRENDIZ -->
    <!-- ======================================================= -->
    <?php if ($esAprendiz): ?>
    <div id="vistaAprendiz">
        <!-- Contenedor cargado dinámicamente según si ya tiene o no patrocinio -->
        <div id="contenedorFichaAprendiz" class="text-center py-5">
            <div class="spinner-border text-success" role="status">
                <span class="visually-hidden">Cargando datos de patrocinio...</span>
            </div>
            <p class="mt-2 text-muted">Consultando estado de patrocinio...</p>
        </div>
    </div>

    <!-- ======================================================= -->
    <!-- CASO 2: PERFIL EMPRESARIO -->
    <!-- ======================================================= -->
    <?php elseif ($esEmpresario): ?>
    <div id="vistaEmpresario">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body p-4 bg-light rounded border-start border-success border-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <span class="badge bg-success mb-1"><i class="fa-solid fa-building"></i> Portal Empresarial</span>
                        <h3 class="fw-bold mb-0 text-dark" id="empNombreHeader">Cargando Empresa...</h3>
                        <p class="text-muted mb-0" id="empDetallesHeader">NIT: - | Contacto: -</p>
                    </div>
                    <div>
                        <button type="button" class="btn btn-outline-success fw-bold" id="btnActaEmpresaDirecta">
                            <i class="fa-solid fa-file-pdf"></i> Generar Acta de Seguimiento
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 border-bottom">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-dark fw-bold">
                        <i class="fa-solid fa-users text-success"></i> Aprendices Patrocinados
                    </h5>
                    <span class="badge bg-success" id="totalAprendicesEmpresaBadge">0 Aprendices</span>
                </div>
            </div>
            <div class="card-body">
                <div class="alert alert-info d-flex align-items-center mb-3">
                    <i class="fa-solid fa-circle-info fa-2x me-3 text-success"></i>
                    <div>
                        <strong>Seguimiento Académico y Asistencial:</strong> Los aprendices con alertas en rojo presentan inasistencias críticas (más de 100 horas) o bajo rendimiento académico y son candidatos a cancelación de patrocinio o proceso de deserción.
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-striped table-bordered align-middle" id="tblEmpresarioAprendices">
                        <thead class="table-success">
                            <tr>
                                <th>Aprendiz</th>
                                <th>Documento</th>
                                <th>Inicio Patrocinio</th>
                                <th>Jefe / Contacto</th>
                                <th>Inasistencias</th>
                                <th>Llamados</th>
                                <th>Rendimiento</th>
                                <th>Estado</th>
                                <th class="text-center">Acción</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyEmpresarioAprendices">
                            <tr>
                                <td colspan="9" class="text-center py-4 text-muted">Cargando aprendices...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- ======================================================= -->
    <!-- CASO 3: INSTRUCTORES DE SEGUIMIENTO / COORDINADORES / ADMIN -->
    <!-- ======================================================= -->
    <?php else: ?>
    <div id="vistaInstructor">

        <!-- Tarjetas Dashboard KPI -->
        <div class="row my-4 g-4">
            <!-- Total Aprendices -->
            <div class="col-12 col-md-3">
                <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #3e4a3d;">Total Aprendices</span>
                            <div class="d-flex align-items-baseline gap-2 mt-2">
                                <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #0b1c30;" id="dashTotal">-</span>
                                <span style="font-size: 13px; color: #3e4a3d;">registrados</span>
                            </div>
                        </div>
                        <div class="kpi-icon-box" style="background-color: #b5dcfe; color: #3c627f;">
                            <i class="fa-solid fa-user-graduate fs-4"></i>
                        </div>
                    </div>
                    <div class="kpi-footer-strip">
                        <span class="fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 11px; color: #3c627f;">
                            <i class="fa-solid fa-arrow-trend-up" style="font-size: 12px;"></i> Total Sistema
                        </span>
                        <span style="font-size: 11px; color: #3e4a3d;">En base de datos</span>
                    </div>
                </div>
            </div>

            <!-- Con Patrocinio -->
            <div class="col-12 col-md-3">
                <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #006b29;">Con Patrocinio</span>
                            <div class="d-flex align-items-baseline gap-2 mt-2">
                                <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #006b29;" id="dashCon">-</span>
                                <span style="font-size: 13px; color: rgba(0, 107, 41, 0.8);">vigentes</span>
                            </div>
                        </div>
                        <div class="kpi-icon-box" style="background-color: #7afd88; color: #006b24;">
                            <i class="fa-solid fa-handshake fs-4"></i>
                        </div>
                    </div>
                    <div class="kpi-footer-strip">
                        <span class="fw-semibold d-inline-flex align-items-center gap-2" style="font-size: 11px; color: #006b29;">
                            <span class="pulse-dot"></span> Vinculación Activa
                        </span>
                        <span style="font-size: 11px; color: #3e4a3d;">En empresa</span>
                    </div>
                </div>
            </div>

            <!-- Sin Patrocinio -->
            <div class="col-12 col-md-3">
                <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #ba1a1a;">Sin Patrocinio</span>
                            <div class="d-flex align-items-baseline gap-2 mt-2">
                                <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #0b1c30;" id="dashSin">-</span>
                                <span style="font-size: 13px; color: #3e4a3d;">pendientes</span>
                            </div>
                        </div>
                        <div class="kpi-icon-box" style="background-color: #ffdad6; color: #ba1a1a;">
                            <i class="fa-solid fa-user-xmark fs-4"></i>
                        </div>
                    </div>
                    <div class="kpi-footer-strip">
                        <span class="d-inline-flex align-items-center gap-1" style="font-size: 11px; color: #3e4a3d;">
                            <i class="fa-solid fa-clock-rotate-left" style="font-size: 12px;"></i> Requieren vinculación
                        </span>
                        <span class="fw-semibold" style="font-size: 11px; color: #3c627f;">Por asignar</span>
                    </div>
                </div>
            </div>

            <!-- Empresas Activas -->
            <div class="col-12 col-md-3">
                <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #3c627f;">Empresas Activas</span>
                            <div class="d-flex align-items-baseline gap-2 mt-2">
                                <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #3c627f;" id="dashEmpresas">-</span>
                                <span style="font-size: 13px; color: #3e4a3d;">patrocinadoras</span>
                            </div>
                        </div>
                        <div class="kpi-icon-box" style="background-color: #d6e4f0; color: #3c627f;">
                            <i class="fa-solid fa-building fs-4"></i>
                        </div>
                    </div>
                    <div class="kpi-footer-strip">
                        <span class="fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 11px; color: #3c627f;">
                            <i class="fa-solid fa-building-user" style="font-size: 12px;"></i> Con aprendices
                        </span>
                        <span style="font-size: 11px; color: #3e4a3d;">Vinculadas al SENA</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pestañas de Navegación -->
        <ul class="nav nav-tabs mb-3" id="tabPatrocinio" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="tab-empresas-btn" data-bs-toggle="tab" data-bs-target="#tab-empresas" type="button" role="tab">
                    <i class="fa-solid fa-building-user text-success"></i> Empresas Patrocinadoras (Ranking)
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tab-aprendices-btn" data-bs-toggle="tab" data-bs-target="#tab-aprendices" type="button" role="tab">
                    <i class="fa-solid fa-list-check text-secondary"></i> Listado General de Aprendices
                </button>
            </li>
        </ul>

        <div class="tab-content">
            <!-- TAB 1: EMPRESAS RANKING -->
            <div class="tab-pane fade show active" id="tab-empresas" role="tabpanel">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2 border-bottom">
                        <div>
                            <h5 class="mb-0 fw-bold text-dark"><i class="fa-solid fa-layer-group text-success"></i> Empresas con Aprendices en Patrocinio</h5>
                            <small class="text-muted">Listado ordenado de mayor a menor según cantidad de aprendices activos.</small>
                        </div>
                        <div class="w-auto">
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fa-solid fa-magnifying-glass"></i></span>
                                <input type="text" class="form-control" id="txtBuscarEmpresa" placeholder="Filtrar empresas por nombre o NIT...">
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="contenedorEmpresasRanking" class="row g-3">
                            <div class="col-12 text-center py-4 text-muted">
                                <div class="spinner-border spinner-border-sm text-success"></div> Cargando listado de empresas...
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 2: TODOS LOS APRENDICES -->
            <div class="tab-pane fade" id="tab-aprendices" role="tabpanel">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                            <h5 class="mb-0 fw-bold text-dark"><i class="fa-solid fa-users text-success"></i> Aprendices Registrados</h5>
                            <button type="button" class="btn btn-success fw-bold" id="btnNuevoPatrocinioGeneral">
                                <i class="fa-solid fa-plus"></i> Registrar Aprendiz con Patrocinio
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table id="tblAprendicesGeneral" class="table table-striped table-bordered w-100 align-middle">
                                <thead class="table-success">
                                    <tr>
                                        <th>Aprendiz</th>
                                        <th>Documento</th>
                                        <th>Empresa</th>
                                        <th>Fecha Inicio</th>
                                        <th>Jefe / Encargado</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <?php endif; ?>

    <div class="inser">
        <form id="frmins" class="filter-toolbar-container" action="home.php?pg=<?=$pg;?>" method="POST">
            <h2><i class="fa-solid fa-file-circle-plus"></i> Nuevo Patrocinio</h2>
            <div class="row">
                <?php if (!$esAprendiz): ?>
                <div class="form-group col-md-6">
                    <label for="selAprendizIns">Aprendiz</label>
                    <select class="form-control form-select" name="idusu" id="selAprendizIns" required>
                        <option value="">Seleccione un aprendiz...</option>
                    </select>
                </div>
                <?php endif; ?>
                <div class="form-group col-md-6">
                    <label for="selEmpresaIns">Empresa Patrocinadora</label>
                    <select class="form-control form-select" name="idemp" id="selEmpresaIns" required>
                        <option value="">Seleccione una empresa...</option>
                    </select>
                </div>
                <div class="form-group col-md-6">
                    <label for="txtFecIniIns">Fecha de Inicio</label>
                    <input type="date" class="form-control" name="fecinipat" id="txtFecIniIns" value="<?= date('Y-m-d'); ?>" required>
                </div>
                <div class="form-group col-md-6">
                    <label for="txtNomJefeIns">Jefe / Contacto</label>
                    <input type="text" class="form-control" name="nomjefpat" id="txtNomJefeIns" placeholder="Nombre completo del jefe" required>
                </div>
                <div class="form-group col-md-6">
                    <label for="txtTelJefeIns">Teléfono de Contacto</label>
                    <input type="text" class="form-control" name="teljefpat" id="txtTelJefeIns" placeholder="Celular o fijo" required>
                </div>
                <div class="form-group col-md-6">
                    <label for="txtCorJefeIns">Correo de Contacto</label>
                    <input type="email" class="form-control" name="corjefpat" id="txtCorJefeIns" placeholder="correo@empresa.com">
                </div>
                <div class="form-group col-md-2">
                    <br>
                    <input type="hidden" name="ope" value="save">
                    <input class="btn btn-success" style="width: 100%;" type="submit" value="Enviar">
                    <br><br>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- Barra de Filtros, Exportación y Búsqueda (Diseño Toolbar Institucional) -->
<style>
</style>

<!-- Toolbar de Control y Filtros -->
<div class="filter-toolbar-container">
    <!-- Izquierda: Píldoras de Filtro de Estado y Exportación -->
    <div class="toolbar-left-group">
        <div class="filter-pills-group" id="tabPatrocinios" role="tablist">
            <!-- Píldora Activos (Activa por defecto al ingresar) -->
            <button class="btn-filter-pill active" id="activos-tab" data-bs-toggle="tab" data-bs-target="#activos" type="button" role="tab" aria-controls="activos" aria-selected="true" onclick="filterByTab('activos')">
                <span class="pill-dot" style="background-color: #006b29;"></span>
                <span>Activos</span>
                <span class="pill-count"><?= $totAct ?? 0; ?></span>
            </button>

            <!-- Píldora Cancelados -->
            <button class="btn-filter-pill" id="cancelados-tab" data-bs-toggle="tab" data-bs-target="#cancelados" type="button" role="tab" aria-controls="cancelados" aria-selected="false" onclick="filterByTab('cancelados')">
                <span class="pill-dot" style="background-color: #ba1a1a;"></span>
                <span>Cancelados</span>
                <span class="pill-count"><?= $totCan ?? 0; ?></span>
            </button>

            <!-- Píldora Todos -->
            <button class="btn-filter-pill" id="todos-tab" data-bs-toggle="tab" data-bs-target="#todos" type="button" role="tab" aria-controls="todos" aria-selected="false" onclick="filterByTab('todos')">
                <span class="pill-dot" style="background-color: #3c627f;"></span>
                <span>Todos</span>
                <span class="pill-count"><?= $totPat ?? 0; ?></span>
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
            <input type="text" id="customPatrocinioSearch" placeholder="Buscar" onkeyup="syncCustomSearch(this.value)">
        </div>

        <!-- Controles Secundarios: Selector de Orden y Conmutador de Vistas -->
        <div class="toolbar-secondary-controls">
            <!-- Selector de ordenamiento -->
            <select class="toolbar-select-sort" id="customPatrocinioSort" onchange="syncCustomSort(this.value)">
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

<div class="tab-content" id="tabPatrociniosContent">
    <!-- Pestaña 1: Patrocinios Activos (Activa por defecto al ingresar) -->
    <div class="tab-pane fade show active" id="activos" role="tabpanel" aria-labelledby="activos-tab">
        <table id="tblActivos" class="table table-striped" style="width:100%">
            <thead>
                <tr>
                    <th>Aprendiz</th>
                    <th>Documento</th>
                    <th>Empresa</th>
                    <th>Fecha Inicio</th>
                    <th>Jefe / Contacto</th>
                    <th>Estado</th>
                    <th class="text-center">Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php if(isset($datAct) && $datAct){ foreach ($datAct as $dt) { ?>
                <tr>
                    <td><?=$dt["nomusu"];?></td>
                    <td><?=$dt["ndocusu"];?></td>
                    <td><?=$dt["nomemp"];?></td>
                    <td><?=$dt["fecinipat"];?></td>
                    <td>
                        <div class="small fw-semibold"><?=$dt["nomjefpat"] ?? 'No reg.';?></div>
                        <div class="small text-muted"><?=$dt["teljefpat"] ?? '';?></div>
                    </td>
                    <td><span class="badge bg-success"><i class="fa-solid fa-circle-check me-1"></i> Activo</span></td>
                    <td class="text-center">
                        <!-- Botón Editar -->
                        <a href="home.php?pg=<?=$pg;?>&idpat=<?=$dt["idpat"];?>&ope=edi" class="btn btn-outline-warning text-dark btn-sm shadow-sm" title="Editar">
                            <i class="fa-solid fa-pen-to-square"></i> <span class="d-none d-sm-inline">Editar</span>
                        </a>
                        <!-- Botón Cancelar Patrocinio -->
                        <button class="btn btn-sm btn-outline-danger" onclick="abrirModalCancelarPatrocinio(<?=$dt["idpat"];?>, <?=$dt["idemp"] ?? 0;?>, '<?=urlencode($dt["nomusu"]);?>')" title="Cancelar Patrocinio">
                            <i class="fa-solid fa-ban"></i> <span class="d-none d-sm-inline">Cancelar</span>
                        </button>
                        <!-- Botón Eliminar -->
                        <a href="home.php?pg=<?=$pg;?>&idpat=<?=$dt["idpat"];?>&ope=eli" class="btn btn-outline-danger btn-sm shadow-sm" title="Eliminar" onclick="return eli(this);">
                            <i class="fa-solid fa-trash-can"></i> <span class="d-none d-sm-inline">Elim.</span>
                        </a>
                    </td>
                </tr>
                <?php }} ?>
            </tbody>
            <tfoot>
                <tr>
                    <th>Aprendiz</th>
                    <th>Documento</th>
                    <th>Empresa</th>
                    <th>Fecha Inicio</th>
                    <th>Jefe / Contacto</th>
                    <th>Estado</th>
                    <th class="text-center">Acción</th>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Pestaña 2: Patrocinios Cancelados -->
    <div class="tab-pane fade" id="cancelados" role="tabpanel" aria-labelledby="cancelados-tab">
        <table id="tblCancelados" class="table table-striped" style="width:100%">
            <thead>
                <tr>
                    <th>Aprendiz</th>
                    <th>Documento</th>
                    <th>Empresa</th>
                    <th>Fecha Inicio</th>
                    <th>Jefe / Contacto</th>
                    <th>Estado</th>
                    <th class="text-center">Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php if(isset($datCan) && $datCan){ foreach ($datCan as $dt) { ?>
                <tr>
                    <td><?=$dt["nomusu"];?></td>
                    <td><?=$dt["ndocusu"];?></td>
                    <td><?=$dt["nomemp"];?></td>
                    <td><?=$dt["fecinipat"];?></td>
                    <td>
                        <div class="small fw-semibold"><?=$dt["nomjefpat"] ?? 'No reg.';?></div>
                        <div class="small text-muted"><?=$dt["teljefpat"] ?? '';?></div>
                    </td>
                    <td><span class="badge bg-danger"><i class="fa-solid fa-ban me-1"></i> Cancelado</span></td>
                    <td class="text-center">
                        <!-- Botón Ver Acta -->
                        <button class="btn btn-sm btn-outline-secondary" onclick="abrirActaCancelacion(<?=$dt["idpat"];?>)" title="Ver Acta de Cancelación">
                            <i class="fa-solid fa-file-lines"></i> <span class="d-none d-sm-inline">Ver Acta</span>
                        </button>
                        <!-- Botón Eliminar -->
                        <a href="home.php?pg=<?=$pg;?>&idpat=<?=$dt["idpat"];?>&ope=eli" class="btn btn-outline-danger btn-sm shadow-sm" title="Eliminar" onclick="return eli(this);">
                            <i class="fa-solid fa-trash-can"></i> <span class="d-none d-sm-inline">Elim.</span>
                        </a>
                    </td>
                </tr>
                <?php }} ?>
            </tbody>
            <tfoot>
                <tr>
                    <th>Aprendiz</th>
                    <th>Documento</th>
                    <th>Empresa</th>
                    <th>Fecha Inicio</th>
                    <th>Jefe / Contacto</th>
                    <th>Estado</th>
                    <th class="text-center">Acción</th>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Pestaña 3: Todos los Patrocinios -->
    <div class="tab-pane fade" id="todos" role="tabpanel" aria-labelledby="todos-tab">
        <table id="tblTodos" class="table table-striped" style="width:100%">
            <thead>
                <tr>
                    <th>Aprendiz</th>
                    <th>Documento</th>
                    <th>Empresa</th>
                    <th>Fecha Inicio</th>
                    <th>Jefe / Contacto</th>
                    <th>Estado</th>
                    <th class="text-center">Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $datTodos = array_merge($datAct ?? [], $datCan ?? []);
                if($datTodos){ foreach ($datTodos as $dt) {
                    $esActPat = ($dt["estpat"] == "1");
                ?>
                <tr>
                    <td><?=$dt["nomusu"];?></td>
                    <td><?=$dt["ndocusu"];?></td>
                    <td><?=$dt["nomemp"];?></td>
                    <td><?=$dt["fecinipat"];?></td>
                    <td>
                        <div class="small fw-semibold"><?=$dt["nomjefpat"] ?? 'No reg.';?></div>
                        <div class="small text-muted"><?=$dt["teljefpat"] ?? '';?></div>
                    </td>
                    <td>
                        <?php if($esActPat): ?>
                            <span class="badge bg-success"><i class="fa-solid fa-circle-check me-1"></i> Activo</span>
                        <?php else: ?>
                            <span class="badge bg-danger"><i class="fa-solid fa-ban me-1"></i> Cancelado</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <?php if($esActPat): ?>
                            <!-- Botón Editar -->
                            <a href="home.php?pg=<?=$pg;?>&idpat=<?=$dt["idpat"];?>&ope=edi" class="btn btn-outline-warning text-dark btn-sm shadow-sm" title="Editar">
                                <i class="fa-solid fa-pen-to-square"></i> <span class="d-none d-sm-inline">Editar</span>
                            </a>
                            <!-- Botón Cancelar -->
                            <button class="btn btn-sm btn-outline-danger" onclick="abrirModalCancelarPatrocinio(<?=$dt["idpat"];?>, <?=$dt["idemp"] ?? 0;?>, '<?=urlencode($dt["nomusu"]);?>')" title="Cancelar Patrocinio">
                                <i class="fa-solid fa-ban"></i> <span class="d-none d-sm-inline">Cancelar</span>
                            </button>
                        <?php else: ?>
                            <!-- Botón Ver Acta -->
                            <button class="btn btn-sm btn-outline-secondary" onclick="abrirActaCancelacion(<?=$dt["idpat"];?>)" title="Ver Acta">
                                <i class="fa-solid fa-file-lines"></i> <span class="d-none d-sm-inline">Ver Acta</span>
                            </button>
                        <?php endif; ?>
                        <!-- Botón Eliminar -->
                        <a href="home.php?pg=<?=$pg;?>&idpat=<?=$dt["idpat"];?>&ope=eli" class="btn btn-outline-danger btn-sm shadow-sm" title="Eliminar" onclick="return eli(this);">
                            <i class="fa-solid fa-trash-can"></i> <span class="d-none d-sm-inline">Elim.</span>
                        </a>
                    </td>
                </tr>
                <?php }} ?>
            </tbody>
            <tfoot>
                <tr>
                    <th>Aprendiz</th>
                    <th>Documento</th>
                    <th>Empresa</th>
                    <th>Fecha Inicio</th>
                    <th>Jefe / Contacto</th>
                    <th>Estado</th>
                    <th class="text-center">Acción</th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<!-- ======================================================= -->
<!-- MODAL: REGISTRAR PATROCINIO (APRENDIZ O INSTRUCTOR) -->
<!-- ======================================================= -->
<div class="modal fade" id="modalRegistroPatrocinio" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fa-solid fa-handshake"></i> Registro de Patrocinio</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form id="frmRegistroPatrocinio">
                    <input type="hidden" id="regIdUsu" value="<?= $idusuActual; ?>">

                    <?php if (!$esAprendiz): ?>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Aprendiz <span class="text-danger">*</span></label>
                        <select class="form-select" id="selAprendizReg" required>
                            <option value="">Seleccione un aprendiz...</option>
                        </select>
                    </div>
                    <?php endif; ?>

                    <div class="row g-3 mb-3">
                        <div class="col-md-7">
                            <label class="form-label fw-bold">Empresa Patrocinadora <span class="text-danger">*</span></label>
                            <select class="form-select" id="selEmpresaReg" required>
                                <option value="">Seleccione una empresa...</option>
                            </select>
                            <div class="form-text">Empresa legalmente constituida que otorgará el patrocinio.</div>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label fw-bold">Fecha de Inicio de Patrocinio <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="txtFecIniReg" required value="<?= date('Y-m-d'); ?>">
                            <div class="form-text">Indique la fecha en que inició o iniciará.</div>
                        </div>
                    </div>

                    <div class="card bg-light border-0 p-3 mb-3">
                        <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-id-card-clip text-success"></i> Datos del Jefe / Contacto en la Empresa</h6>
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Nombre Completo del Jefe / Encargado <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="txtNomJefeReg" placeholder="Nombre de la persona encargada o quien firmó el contrato" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Teléfono de Contacto <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="txtTelJefeReg" placeholder="Celular o fijo directo" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Correo Electrónico de Contacto</label>
                                <input type="email" class="form-control" id="txtCorJefeReg" placeholder="correo@empresa.com">
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-warning d-flex align-items-start small mb-0">
                        <i class="fa-solid fa-triangle-exclamation fa-lg me-2 mt-1"></i>
                        <div>
                            <strong>Importante:</strong> Al guardar, el registro quedará fijado para el aprendiz y no podrá ser modificado a menos que sea cancelado formalmente por la empresa o relaciones corporativas.
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success px-4 fw-bold" id="btnGuardarRegistroPatrocinio">
                    <i class="fa-solid fa-floppy-disk"></i> Guardar Patrocinio
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ======================================================= -->
<!-- MODAL: DETALLE DE APRENDICES DE UNA EMPRESA (DRILLDOWN) -->
<!-- ======================================================= -->
<div class="modal fade" id="modalDetalleEmpresa" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <div>
                    <h5 class="modal-title mb-0" id="modDetalleEmpresaNombre">Empresa</h5>
                    <small class="text-light opacity-75" id="modDetalleEmpresaNIT">NIT: -</small>
                </div>
                <div>
                    <button type="button" class="btn btn-sm btn-light text-success fw-bold me-2" id="btnGenerarActaSeguimientoModal">
                        <i class="fa-solid fa-file-contract"></i> Generar Acta de Seguimiento
                    </button>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
            </div>
            <div class="modal-body p-4">
                <div class="table-responsive">
                    <table class="table table-hover table-striped table-bordered align-middle">
                        <thead class="table-success">
                            <tr>
                                <th>Aprendiz</th>
                                <th>Documento</th>
                                <th>Inicio Patrocinio</th>
                                <th>Jefe / Contacto</th>
                                <th>Inasistencias</th>
                                <th>Llamados</th>
                                <th>Rendimiento</th>
                                <th>Estado</th>
                                <th class="text-center">Acción</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyDetalleEmpresaAprendices">
                            <tr><td colspan="9" class="text-center py-4">Cargando...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- ======================================================= -->
<!-- MODAL: CANCELAR PATROCINIO -->
<!-- ======================================================= -->
<div class="modal fade" id="modalCancelarPatrocinio" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fa-solid fa-ban"></i> Cancelación de Patrocinio</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="canIdPat">
                <input type="hidden" id="canIdEmp">
                <p>¿Está seguro de que desea <strong>cancelar el patrocinio</strong> del aprendiz <span class="fw-bold text-danger" id="canNomAprendiz">-</span>?</p>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Motivo de la Cancelación <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="canMotivo" rows="4" placeholder="Describa detalladamente la causal (ej. Inasistencias reiteradas, bajo rendimiento académico o solicitud de la empresa)..." required></textarea>
                </div>

                <div class="alert alert-warning small mb-0">
                    <i class="fa-solid fa-triangle-exclamation me-1"></i> Se generará automáticamente el <strong>Acta Oficial de Cancelación de Patrocinio</strong> para los archivos del SENA y de la empresa.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Descartar</button>
                <button type="button" class="btn btn-danger fw-bold" id="btnConfirmarCancelacionPatrocinio">
                    <i class="fa-solid fa-xmark"></i> Confirmar Cancelación y Emitir Acta
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ======================================================= -->
<!-- MODAL / CONTENEDOR DE IMPRESIÓN DE ACTAS -->
<!-- ======================================================= -->
<div class="modal fade" id="modalVisualizarActa" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white no-print">
                <h5 class="modal-title"><i class="fa-solid fa-file-lines"></i> Acta Oficial SENA</h5>
                <div>
                    <button type="button" class="btn btn-success btn-sm me-2" onclick="window.print();">
                        <i class="fa-solid fa-print"></i> Imprimir / Guardar PDF
                    </button>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
            </div>
            <div class="modal-body p-4" id="seccionImprimible">
                <div id="cuerpoActaImprimible">
                    <!-- Contenido del Acta cargado vía JS -->
                </div>
            </div>
            <div class="modal-footer no-print">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
$(function () {
    var RUTA_CTRL = 'controllers/usucpto.php';
    var ROL_APRENDIZ   = <?= $esAprendiz ? 'true' : 'false'; ?>;
    var ROL_EMPRESARIO = <?= $esEmpresario ? 'true' : 'false'; ?>;
    var IDUSU_ACTUAL   = <?= $idusuActual; ?>;
    var listaEmpresasGlobal = [];
    var empresaSeleccionadaActual = null;

    // Cargar combo empresas global
    function cargarComboEmpresas(selectId, callback) {
        $.post(RUTA_CTRL, { ope: 'empresas' }, function (empresas) {
            listaEmpresasGlobal = empresas || [];
            var $sel = $(selectId).empty().append('<option value="">Seleccione una empresa...</option>');
            listaEmpresasGlobal.forEach(function (e) {
                $sel.append('<option value="' + e.idemp + '" data-nit="' + (e.numdocemp || '') + '" data-nom="' + e.nomemp + '">' + e.nomemp + ' (' + (e.numdocemp ? 'NIT: ' + e.numdocemp : 'Sin NIT') + ')</option>');
            });
            if (typeof callback === 'function') callback();
        }, 'json');
    }

    // =======================================================
    // FLUJO 1: PERFIL APRENDIZ
    // =======================================================
    if (ROL_APRENDIZ) {
        function cargarFichaAprendiz() {
            $('#contenedorFichaAprendiz').html('<div class="spinner-border text-primary"></div><p class="mt-2 text-muted">Cargando información...</p>');

            $.post(RUTA_CTRL, { ope: 'miPatrocinio', idusu: IDUSU_ACTUAL }, function (res) {
                var pat = res && res.data ? res.data : null;

                // Si NO tiene patrocinio registrado o si está cancelado
                if (!pat || pat.estpat == 2) {
                    var htmlAlertaCancelado = '';
                    if (pat && pat.estpat == 2) {
                        htmlAlertaCancelado = `
                            <div class="alert alert-danger d-flex align-items-center mb-4">
                                <i class="fa-solid fa-circle-exclamation fa-2x me-3"></i>
                                <div>
                                    <h6 class="alert-heading fw-bold mb-1">Patrocinio Cancelado Anteriormente</h6>
                                    <p class="mb-0 small">Su patrocinio previo con la empresa <strong>${pat.nomemp}</strong> fue cancelado el ${pat.feccanpat || ''}. Motivo: <em>${pat.motcanpat || 'No especificado'}</em>.<br>
                                    Usted puede registrar una nueva empresa patrocinadora.</p>
                                </div>
                            </div>
                        `;
                    }

                    var htmlFormulario = `
                        ${htmlAlertaCancelado}
                        <div class="card border-success shadow-sm mb-4">
                            <div class="card-header bg-success text-white py-2">
                                <h5 class="mb-0"><i class="fa-solid fa-file-signature me-2"></i> Registro de su Empresa Patrocinadora</h5>
                            </div>
                            <div class="card-body p-4 text-start">
                                <p class="text-muted">Por favor seleccione la empresa que le otorgará el patrocinio e ingrese los datos de inicio y de su jefe o encargado.</p>
                                <form id="frmAprendizDirecto" class="form-default-pages">
                                    <div class="row">
                                        <div class="form-group col-md-6 mb-3">
                                            <label for="selEmpresaAprendiz" class="fw-semibold">Empresa Patrocinadora <span class="text-danger">*</span></label>
                                            <select class="form-select form--input-default" id="selEmpresaAprendiz" required>
                                                <option value="">Seleccione una empresa...</option>
                                            </select>
                                        </div>
                                        <div class="form-group col-md-6 mb-3">
                                            <label for="txtFecIniAprendiz" class="fw-semibold">Fecha de Inicio de Patrocinio <span class="text-danger">*</span></label>
                                            <input type="date" class="form-control form--input-default" id="txtFecIniAprendiz" value="${new Date().toISOString().split('T')[0]}" required>
                                        </div>
                                        <div class="form-group col-md-6 mb-3">
                                            <label for="txtNomJefeAprendiz" class="fw-semibold">Nombre del Jefe / Contacto en Empresa <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control form--input-default" id="txtNomJefeAprendiz" placeholder="Nombre completo del jefe o quien firmó el contrato" required>
                                        </div>
                                        <div class="form-group col-md-6 mb-3">
                                            <label for="txtTelJefeAprendiz" class="fw-semibold">Teléfono de Contacto <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control form--input-default" id="txtTelJefeAprendiz" placeholder="Celular o teléfono fijo" required>
                                        </div>
                                        <div class="form-group col-md-12 mb-3">
                                            <label for="txtCorJefeAprendiz" class="fw-semibold">Correo Electrónico de Contacto</label>
                                            <input type="email" class="form-control form--input-default" id="txtCorJefeAprendiz" placeholder="correo@empresa.com">
                                        </div>
                                        <div class="form-group col-md-12 mt-3">
                                            <button type="button" class="btn btn-primary px-4 fw-bold" id="btnGuardarAprendizDirecto">
                                                <i class="fa-solid fa-floppy-disk me-1"></i> Guardar Registro de Patrocinio
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    `;

                    $('#contenedorFichaAprendiz').html(htmlFormulario);
                    cargarComboEmpresas('#selEmpresaAprendiz');

                } else {
                    // Ya tiene patrocinio Activo / Registrado
                    var badgeEstado = '<span class="badge bg-success"><i class="fa-solid fa-circle-check me-1"></i> Registrado / Activo</span>';
                    if (pat.estpat == 3) {
                        badgeEstado = '<span class="badge bg-info"><i class="fa-solid fa-flag-checkered me-1"></i> Finalizado por Tiempo</span>';
                    }

                    var htmlFicha = `
                        <div class="card border-success shadow-sm mb-4">
                            <div class="card-header bg-success text-white py-2 d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="fa-solid fa-id-badge me-2"></i> Datos de Patrocinio</h5>
                                ${badgeEstado}
                            </div>
                            <div class="card-body bg-white p-4">
                                <div class="table-responsive">
                                    <table class="table table-bordered align-middle text-start mb-3">
                                        <tbody>
                                            <tr>
                                                <th class="bg-light" style="width: 25%;">Empresa Patrocinadora</th>
                                                <td style="width: 25%;" class="fw-bold text-success">${pat.nomemp || 'No registrada'}</td>
                                                <th class="bg-light" style="width: 25%;">NIT Empresa</th>
                                                <td style="width: 25%;">${pat.numdocemp || 'N/A'}</td>
                                            </tr>
                                            <tr>
                                                <th class="bg-light">Dirección Empresa</th>
                                                <td>${pat.diremp || 'No registrada'}</td>
                                                <th class="bg-light">Teléfono Empresa</th>
                                                <td>${pat.telempresa || 'No registrado'}</td>
                                            </tr>
                                            <tr>
                                                <th class="bg-light">Jefe / Contacto Encargado</th>
                                                <td class="fw-semibold">${pat.nomjefpat || 'No registrado'}</td>
                                                <th class="bg-light">Teléfono / Correo Contacto</th>
                                                <td>${pat.teljefpat || 'N/A'}${pat.corjefpat ? ' &nbsp;|&nbsp; ' + pat.corjefpat : ''}</td>
                                            </tr>
                                            <tr>
                                                <th class="bg-light">Fecha de Inicio</th>
                                                <td><span class="badge bg-success">${pat.fecinipat || 'N/A'}</span></td>
                                                <th class="bg-light">Estado Vinculación</th>
                                                <td>${badgeEstado}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="alert alert-secondary mb-0 d-flex align-items-center">
                                    <i class="fa-solid fa-lock fa-2x me-3 text-secondary"></i>
                                    <div class="small">
                                        <strong>Registro inmutable:</strong> Su vinculación de patrocinio está en firme y no permite modificaciones directas. Cualquier cambio o novedad debe ser tramitado a través de su empresa o coordinación de seguimiento.
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;

                    $('#contenedorFichaAprendiz').html(htmlFicha);
                }
            }, 'json');
        }

        cargarFichaAprendiz();

        // Acción Guardar Aprendiz Directo
        $(document).on('click', '#btnGuardarAprendizDirecto', function () {
            var idemp     = $('#selEmpresaAprendiz').val();
            var nomEmpresa= $('#selEmpresaAprendiz option:selected').data('nom') || 'la empresa seleccionada';
            var fecini    = $('#txtFecIniAprendiz').val();
            var nomjefe   = $('#txtNomJefeAprendiz').val();
            var teljefe   = $('#txtTelJefeAprendiz').val();
            var corjefe   = $('#txtCorJefeAprendiz').val();

            if (!idemp) {
                Swal.fire('Atención', 'Por favor seleccione su empresa patrocinadora.', 'warning');
                return;
            }
            if (!fecini) {
                Swal.fire('Atención', 'Por favor indique la fecha de inicio del patrocinio.', 'warning');
                return;
            }
            if (!nomjefe || !teljefe) {
                Swal.fire('Atención', 'Debe ingresar el nombre y teléfono del jefe o contacto encargado.', 'warning');
                return;
            }

            Swal.fire({
                title: '¿Confirmar Registro de Patrocinio?',
                html: `¿Está seguro de que usted se registró en la empresa <strong>${nomEmpresa}</strong>?<br><br><small class="text-muted">Una vez guardado, no podrá modificar la empresa a menos que se cancele formalmente el patrocinio.</small>`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, estoy seguro y acepto',
                cancelButtonText: 'Revisar datos'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post(RUTA_CTRL, {
                        ope: 'guardar',
                        idusu: IDUSU_ACTUAL,
                        idemp: idemp,
                        fecinipat: fecini,
                        nomjefpat: nomjefe,
                        teljefpat: teljefe,
                        corjefpat: corjefe
                    }, function (res) {
                        if (res.ok) {
                            Swal.fire('¡Registrado!', res.msg, 'success');
                            cargarFichaAprendiz();
                        } else {
                            Swal.fire('Atención', res.msg, 'warning');
                        }
                    }, 'json');
                }
            });
        });
    }

    // =======================================================
    // FLUJO 2: PERFIL EMPRESARIO
    // =======================================================
    if (ROL_EMPRESARIO) {
        var idEmpresaActual = 0;

        function cargarDashboardEmpresario() {
            $.post(RUTA_CTRL, { ope: 'miEmpresa' }, function (res) {
                if (res.ok && res.empresa) {
                    idEmpresaActual = res.empresa.idemp;
                    $('#empNombreHeader').text(res.empresa.nomemp);
                    $('#empDetallesHeader').text('NIT: ' + (res.empresa.numdocemp || 'N/A') + ' | Dirección: ' + (res.empresa.diremp || 'N/A') + ' | Teléfono: ' + (res.empresa.telemp || 'N/A'));

                    renderTablaAprendicesEmpresa('#tbodyEmpresarioAprendices', res.aprendices || []);
                    $('#totalAprendicesEmpresaBadge').text((res.aprendices ? res.aprendices.length : 0) + ' Aprendices');
                } else {
                    $('#empNombreHeader').text('Empresa no identificada');
                    $('#tbodyEmpresarioAprendices').html('<tr><td colspan="9" class="text-center text-danger py-4">No se pudo vincular la sesión con una empresa registrada.</td></tr>');
                }
            }, 'json');
        }

        cargarDashboardEmpresario();

        $('#btnActaEmpresaDirecta').on('click', function () {
            if (idEmpresaActual) {
                abrirActaSeguimiento(idEmpresaActual);
            }
        });
    }

    // =======================================================
    // FLUJO 3: PERFIL INSTRUCTOR / COORDINADOR / ADMIN
    // =======================================================
    if (!ROL_APRENDIZ && !ROL_EMPRESARIO) {
        var tablaGeneral = null;

        function cargarKPIs() {
            $.post(RUTA_CTRL, { ope: 'dashboard' }, function (res) {
                $('#dashTotal').text(res.total || 0);
                $('#dashCon').text(res.con || 0);
                $('#dashSin').text(res.sin || 0);
                $('#dashEmpresas').text(res.empresas || 0);
            }, 'json');
        }

        function cargarEmpresasRanking() {
            $.post(RUTA_CTRL, { ope: 'empresasRanking' }, function (res) {
                var empresas = res.data || [];
                var $cont = $('#contenedorEmpresasRanking').empty();

                if (empresas.length === 0) {
                    $cont.html('<div class="col-12 text-center py-4 text-muted">No hay empresas con patrocinios registrados actualmente.</div>');
                    return;
                }

                empresas.forEach(function (emp) {
                    var card = `
                        <div class="col-md-6 col-lg-4 item-empresa-rank" data-busqueda="${(emp.nomemp + ' ' + emp.numdocemp).toLowerCase()}">
                            <div class="card h-100 shadow-sm empresa-rank-card p-3" onclick="verDetalleEmpresa(${emp.idemp}, '${encodeURIComponent(emp.nomemp)}', '${emp.numdocemp || ''}')">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="fw-bold text-dark mb-0 text-truncate" style="max-width: 75%;">${emp.nomemp}</h6>
                                    <span class="badge bg-primary fs-6">${emp.total_aprendices} <i class="fa-solid fa-user-graduate"></i></span>
                                </div>
                                <div class="text-muted small mb-2">
                                    <div><strong>NIT:</strong> ${emp.numdocemp || 'Sin NIT'}</div>
                                    <div><strong>Contacto:</strong> ${emp.nomconemp || 'N/A'} (${emp.telemp || 'N/A'})</div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-auto pt-2 border-top">
                                    <span class="badge bg-success bg-opacity-10 text-success">${emp.aprendices_activos} Activos</span>
                                    <span class="badge bg-danger bg-opacity-10 text-danger">${emp.aprendices_cancelados || 0} Cancelados</span>
                                    <button class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-eye"></i> Ver Aprendices</button>
                                </div>
                            </div>
                        </div>
                    `;
                    $cont.append(card);
                });
            }, 'json');
        }

        // Buscador de empresas en vivo
        $('#txtBuscarEmpresa').on('keyup', function () {
            var filtro = $(this).val().toLowerCase().trim();
            $('.item-empresa-rank').each(function () {
                var txt = $(this).data('busqueda') || '';
                $(this).toggle(txt.indexOf(filtro) > -1);
            });
        });

        // Inicializar DataTable general
        tablaGeneral = $('#tblAprendicesGeneral').DataTable({
            language: {
                "decimal": "",
                "emptyTable": "No hay aprendices registrados",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ aprendices",
                "infoEmpty": "Mostrando 0 aprendices",
                "infoFiltered": "(Filtrado de _MAX_ total)",
                "lengthMenu": "Mostrar _MENU_ registros",
                "loadingRecords": "Cargando...",
                "processing": "Procesando...",
                "search": "Buscar aprendiz:",
                "zeroRecords": "No se encontraron coincidencias",
                "paginate": { "first": "Primero", "last": "Último", "next": "Siguiente", "previous": "Anterior" }
            },
            ajax: {
                url: RUTA_CTRL,
                type: 'POST',
                data: { ope: 'datatable' },
                dataSrc: function (json) { return (json && json.data) ? json.data : []; }
            },
            columns: [
                { data: 'nomusu' },
                { data: 'ndocusu' },
                { data: 'nomemp' },
                { data: 'fecinipat' },
                { data: 'jefe' },
                { data: 'vinculado' }
            ]
        });

        cargarKPIs();
        cargarEmpresasRanking();

        $('#btnNuevoPatrocinioGeneral').on('click', function () {
            $('#frmRegistroPatrocinio')[0].reset();
            cargarComboEmpresas('#selEmpresaReg');

            $.post(RUTA_CTRL, { ope: 'aprendicesSin' }, function (aprendices) {
                var $sel = $('#selAprendizReg').empty().append('<option value="">Seleccione un aprendiz...</option>');
                (aprendices || []).forEach(function (a) {
                    $sel.append('<option value="' + a.idusu + '">' + a.nomusu + ' - ' + a.ndocusu + '</option>');
                });
            }, 'json');

            var modal = new bootstrap.Modal(document.getElementById('modalRegistroPatrocinio'));
            modal.show();
        });

        $('#btnGuardarRegistroPatrocinio').on('click', function () {
            var idusu     = $('#selAprendizReg').val();
            var idemp     = $('#selEmpresaReg').val();
            var nomEmpresa= $('#selEmpresaReg option:selected').data('nom') || 'la empresa seleccionada';
            var fecini    = $('#txtFecIniReg').val();
            var nomjefe   = $('#txtNomJefeReg').val();
            var teljefe   = $('#txtTelJefeReg').val();
            var corjefe   = $('#txtCorJefeReg').val();

            if (!idusu || !idemp) {
                Swal.fire('Atención', 'Debe seleccionar un aprendiz y una empresa.', 'warning');
                return;
            }
            if (!nomjefe || !teljefe) {
                Swal.fire('Atención', 'Debe diligenciar el nombre y teléfono del jefe o encargado.', 'warning');
                return;
            }

            Swal.fire({
                title: '¿Confirmar Registro?',
                html: `¿Está seguro de que el aprendiz quedará registrado en la empresa <strong>${nomEmpresa}</strong>?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, registrar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post(RUTA_CTRL, {
                        ope: 'guardar',
                        idusu: idusu,
                        idemp: idemp,
                        fecinipat: fecini,
                        nomjefpat: nomjefe,
                        teljefpat: teljefe,
                        corjefpat: corjefe
                    }, function (res) {
                        if (res.ok) {
                            bootstrap.Modal.getInstance(document.getElementById('modalRegistroPatrocinio')).hide();
                            Swal.fire('Listo', res.msg, 'success');
                            cargarKPIs();
                            cargarEmpresasRanking();
                            tablaGeneral.ajax.reload(null, false);
                        } else {
                            Swal.fire('Atención', res.msg, 'warning');
                        }
                    }, 'json');
                }
            });
        });
    }

    // =======================================================
    // FUNCIONES AUXILIARES GLOBALES (DRILLDOWN Y TABLAS)
    // =======================================================

    window.renderTablaAprendicesEmpresa = function (tbodySelector, aprendices) {
        var $tbody = $(tbodySelector).empty();

        if (!aprendices || aprendices.length === 0) {
            $tbody.html('<tr><td colspan="9" class="text-center py-4 text-muted">Esta empresa no tiene aprendices vinculados actualmente.</td></tr>');
            return;
        }

        aprendices.forEach(function (a) {
            var badgeIna = '<span class="badge bg-secondary">' + a.horas_inasistencia + ' hrs</span>';
            if (a.horas_inasistencia >= 100) {
                badgeIna = '<span class="badge badge-alerta-critica p-2"><i class="fa-solid fa-triangle-exclamation"></i> ' + a.horas_inasistencia + ' hrs (Crítica)</span>';
            } else if (a.horas_inasistencia >= 40) {
                badgeIna = '<span class="badge bg-warning text-dark">' + a.horas_inasistencia + ' hrs</span>';
            }

            var badgeLlam = a.total_llamados > 0 
                ? '<span class="badge bg-danger">' + a.total_llamados + ' llamados</span>' 
                : '<span class="badge bg-light text-muted border">0</span>';

            var badgeRend = '<span class="badge bg-success"><i class="fa-solid fa-check"></i> ' + a.estado_academico + '</span>';
            if (a.riesgo_desercion || a.estado_academico.indexOf('Bajo') > -1) {
                badgeRend = '<span class="badge bg-danger p-2"><i class="fa-solid fa-triangle-exclamation"></i> ' + a.estado_academico + '</span>';
            }

            var badgeEstado = a.estpat == 1 
                ? '<span class="badge bg-success">Activo</span>' 
                : '<span class="badge bg-danger">Cancelado</span>';

            var botonAccion = '';
            if (a.estpat == 1) {
                botonAccion = `
                    <button class="btn btn-sm btn-outline-danger" onclick="abrirModalCancelarPatrocinio(${a.idpat}, ${a.idemp || 0}, '${encodeURIComponent(a.nomusu)}')">
                        <i class="fa-solid fa-ban"></i> Cancelar
                    </button>
                `;
            } else {
                botonAccion = `
                    <button class="btn btn-sm btn-outline-secondary" onclick="abrirActaCancelacion(${a.idpat})">
                        <i class="fa-solid fa-file-lines"></i> Ver Acta
                    </button>
                `;
            }

            var tr = `
                <tr class="${a.riesgo_desercion ? 'table-danger' : ''}">
                    <td>
                        <div class="fw-bold">${a.nomusu}</div>
                        <small class="text-muted">${a.emausu || ''}</small>
                    </td>
                    <td>${a.ndocusu}</td>
                    <td>${a.fecinipat || '<span class="text-muted small">N/A</span>'}</td>
                    <td>
                        <div class="small fw-semibold">${a.nomjefpat || 'No reg.'}</div>
                        <div class="small text-muted">${a.teljefpat || ''}</div>
                    </td>
                    <td>${badgeIna}</td>
                    <td>${badgeLlam}</td>
                    <td>
                        ${badgeRend}
                        ${a.total_juicios > 0 ? '<div class="small text-muted mt-1">Avance: ' + a.porcentaje_avance + '% (' + a.juicios_aprobados + '/' + a.total_juicios + ')</div>' : ''}
                    </td>
                    <td>${badgeEstado}</td>
                    <td class="text-center">${botonAccion}</td>
                </tr>
            `;
            $tbody.append(tr);
        });
    };

    window.verDetalleEmpresa = function (idemp, nomempEncoded, nit) {
        var nomemp = decodeURIComponent(nomempEncoded);
        empresaSeleccionadaActual = idemp;

        $('#modDetalleEmpresaNombre').text(nomemp);
        $('#modDetalleEmpresaNIT').text('NIT: ' + (nit || 'No registrado'));
        $('#tbodyDetalleEmpresaAprendices').html('<tr><td colspan="9" class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div> Cargando aprendices...</td></tr>');

        var modal = new bootstrap.Modal(document.getElementById('modalDetalleEmpresa'));
        modal.show();

        $.post(RUTA_CTRL, { ope: 'aprendicesEmpresa', idemp: idemp }, function (res) {
            renderTablaAprendicesEmpresa('#tbodyDetalleEmpresaAprendices', res.data || []);
        }, 'json');
    };

    $('#btnGenerarActaSeguimientoModal').on('click', function () {
        if (empresaSeleccionadaActual) {
            abrirActaSeguimiento(empresaSeleccionadaActual);
        }
    });

    // Abrir modal Cancelar Patrocinio
    window.abrirModalCancelarPatrocinio = function (idpat, idemp, nomusuEncoded) {
        var nomusu = decodeURIComponent(nomusuEncoded);
        $('#canIdPat').val(idpat);
        $('#canIdEmp').val(idemp);
        $('#canNomAprendiz').text(nomusu);
        $('#canMotivo').val('');

        var modal = new bootstrap.Modal(document.getElementById('modalCancelarPatrocinio'));
        modal.show();
    };

    // Confirmar Cancelación de Patrocinio
    $('#btnConfirmarCancelacionPatrocinio').on('click', function () {
        var idpat  = $('#canIdPat').val();
        var idemp  = $('#canIdEmp').val();
        var motivo = $('#canMotivo').val().trim();

        if (!motivo) {
            Swal.fire('Atención', 'Por favor especifique el motivo de la cancelación.', 'warning');
            return;
        }

        $.post(RUTA_CTRL, { ope: 'cancelar', idpat: idpat, motivo: motivo }, function (res) {
            if (res.ok) {
                bootstrap.Modal.getInstance(document.getElementById('modalCancelarPatrocinio')).hide();
                Swal.fire('Patrocinio Cancelado', res.msg, 'success').then(() => {
                    // Abrir de inmediato el Acta de Cancelación para imprimir/guardar
                    abrirActaCancelacion(idpat);

                    // Recargar vistas correspondientes
                    if (ROL_EMPRESARIO) {
                        cargarDashboardEmpresario();
                    } else {
                        if (idemp) {
                            $.post(RUTA_CTRL, { ope: 'aprendicesEmpresa', idemp: idemp }, function (r) {
                                renderTablaAprendicesEmpresa('#tbodyDetalleEmpresaAprendices', r.data || []);
                            }, 'json');
                        }
                        cargarKPIs();
                        cargarEmpresasRanking();
                        if (tablaGeneral) tablaGeneral.ajax.reload(null, false);
                    }
                });
            } else {
                Swal.fire('Error', res.msg, 'error');
            }
        }, 'json');
    });

    // Generar y Visualizar Acta de Cancelación
    window.abrirActaCancelacion = function (idpat) {
        $.post(RUTA_CTRL, { ope: 'actaCancelacion', idpat: idpat }, function (res) {
            if (res.ok && res.data) {
                var d = res.data;
                var htmlActa = `
                    <div class="acta-documento">
                        <div class="acta-header d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="fw-bold mb-0 text-success">SERVICIO NACIONAL DE APRENDIZAJE - SENA</h4>
                                <div class="text-muted small">REGISTRO Y CONTROL DE ETAPA PRODUCTIVA / PATROCINIO</div>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-danger fs-6">ACTA DE CANCELACIÓN DE PATROCINIO</span>
                                <div class="small text-muted mt-1">Fecha: ${d.feccanpat || new Date().toISOString()}</div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <p class="text-justify">
                                En la fecha, se deja constancia formal de la <strong>cancelación del patrocinio</strong> del aprendiz detallado a continuación, conforme a los reportes académicos, de inasistencias o causales institucionales registradas en el sistema SAGISENA:
                            </p>
                        </div>

                        <div class="card bg-light border-0 p-3 mb-4">
                            <h6 class="fw-bold text-dark border-bottom pb-2">1. INFORMACIÓN DEL APRENDIZ</h6>
                            <div class="row">
                                <div class="col-md-6"><strong>Nombre:</strong> ${d.nomusu}</div>
                                <div class="col-md-6"><strong>Documento:</strong> ${d.ndocusu}</div>
                                <div class="col-md-6 mt-2"><strong>Correo:</strong> ${d.emausu || 'N/A'}</div>
                                <div class="col-md-6 mt-2"><strong>Teléfono:</strong> ${d.telcan || 'N/A'}</div>
                            </div>
                        </div>

                        <div class="card bg-light border-0 p-3 mb-4">
                            <h6 class="fw-bold text-dark border-bottom pb-2">2. INFORMACIÓN DE LA EMPRESA PATROCINADORA</h6>
                            <div class="row">
                                <div class="col-md-6"><strong>Empresa:</strong> ${d.nomemp}</div>
                                <div class="col-md-6"><strong>NIT:</strong> ${d.numdocemp || 'N/A'}</div>
                                <div class="col-md-6 mt-2"><strong>Jefe Encargado:</strong> ${d.nomjefpat || 'N/A'}</div>
                                <div class="col-md-6 mt-2"><strong>Fecha Inicio Patrocinio:</strong> ${d.fecinipat || 'N/A'}</div>
                            </div>
                        </div>

                        <div class="card bg-light border-0 p-3 mb-4">
                            <h6 class="fw-bold text-dark border-bottom pb-2 text-danger">3. CAUSAL Y MOTIVO DE LA CANCELACIÓN</h6>
                            <p class="mb-0 text-danger fw-semibold">${d.motcanpat || 'Cancelación de contrato de aprendizaje / patrocinio por incumplimiento o mutuo acuerdo.'}</p>
                        </div>

                        <div class="acta-footer-firmas row text-center">
                            <div class="col-6">
                                <div class="border-top border-dark pt-2 mx-4">
                                    <strong>Firma Empresa Patrocinadora</strong><br>
                                    <small class="text-muted">${d.nomjefpat || d.nomemp}</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="border-top border-dark pt-2 mx-4">
                                    <strong>Firma Coordinación / SENA</strong><br>
                                    <small class="text-muted">Relaciones Corporativas / Seguimiento</small>
                                </div>
                            </div>
                        </div>
                    </div>
                `;

                $('#cuerpoActaImprimible').html(htmlActa);
                var modal = new bootstrap.Modal(document.getElementById('modalVisualizarActa'));
                modal.show();
            } else {
                Swal.fire('Error', res.msg || 'No se pudo cargar el acta.', 'error');
            }
        }, 'json');
    };

    // Generar y Visualizar Acta de Seguimiento por Empresa
    window.abrirActaSeguimiento = function (idemp) {
        $.post(RUTA_CTRL, { ope: 'actaSeguimiento', idemp: idemp }, function (res) {
            if (res.ok && res.data) {
                var emp = res.data.empresa;
                var ap  = res.data.aprendices || [];

                var filasAprendices = '';
                ap.forEach(function (a, idx) {
                    filasAprendices += `
                        <tr>
                            <td>${idx + 1}</td>
                            <td><strong>${a.nomusu}</strong></td>
                            <td>${a.ndocusu}</td>
                            <td>${a.fecinipat || 'N/A'}</td>
                            <td>${a.horas_inasistencia} hrs</td>
                            <td>${a.total_llamados}</td>
                            <td>${a.estado_academico} (${a.porcentaje_avance}%)</td>
                            <td>${a.estpat == 1 ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Cancelado</span>'}</td>
                        </tr>
                    `;
                });

                var htmlActa = `
                    <div class="acta-documento">
                        <div class="acta-header d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="fw-bold mb-0 text-success">SERVICIO NACIONAL DE APRENDIZAJE - SENA</h4>
                                <div class="text-muted small">REPORTE OFICIAL DE SEGUIMIENTO A PATROCINIOS DE ETAPA PRODUCTIVA</div>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-primary fs-6">ACTA DE SEGUIMIENTO</span>
                                <div class="small text-muted mt-1">Fecha Emisión: ${res.data.fecha_acta}</div>
                            </div>
                        </div>

                        <div class="card bg-light border-0 p-3 mb-4">
                            <h6 class="fw-bold text-dark border-bottom pb-2">DATOS DE LA EMPRESA</h6>
                            <div class="row">
                                <div class="col-md-6"><strong>Razón Social:</strong> ${emp.nomemp}</div>
                                <div class="col-md-6"><strong>NIT:</strong> ${emp.numdocemp || 'N/A'}</div>
                                <div class="col-md-6 mt-2"><strong>Dirección:</strong> ${emp.diremp || 'N/A'}</div>
                                <div class="col-md-6 mt-2"><strong>Contacto / Teléfono:</strong> ${emp.nomconemp || 'N/A'} (${emp.telemp || 'N/A'})</div>
                            </div>
                        </div>

                        <h6 class="fw-bold text-dark mb-2">APRENDICES VINCULADOS EN PATROCINIO</h6>
                        <table class="table table-bordered table-sm small align-middle mb-4">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Aprendiz</th>
                                    <th>Documento</th>
                                    <th>Fecha Inicio</th>
                                    <th>Inasistencias</th>
                                    <th>Llamados</th>
                                    <th>Rendimiento</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${filasAprendices || '<tr><td colspan="8" class="text-center">No hay aprendices registrados.</td></tr>'}
                            </tbody>
                        </table>

                        <div class="acta-footer-firmas row text-center">
                            <div class="col-6">
                                <div class="border-top border-dark pt-2 mx-4">
                                    <strong>Firma Representante Empresa</strong><br>
                                    <small class="text-muted">${emp.nomconemp || emp.nomemp}</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="border-top border-dark pt-2 mx-4">
                                    <strong>Firma Instructor / Gestor SENA</strong><br>
                                    <small class="text-muted">Relaciones Corporativas</small>
                                </div>
                            </div>
                        </div>
                    </div>
                `;

                $('#cuerpoActaImprimible').html(htmlActa);
                var modal = new bootstrap.Modal(document.getElementById('modalVisualizarActa'));
                modal.show();
            } else {
                Swal.fire('Error', res.msg || 'No se pudo generar el acta de seguimiento.', 'error');
            }
        }, 'json');
    };

});
</script>