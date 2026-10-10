<?php 
require_once 'controllers/horcprg.php'; 

// Renderizar únicamente el título estándar de SAGISENA encima del dashboard
echo titulo2("<i class='".($icono ?? 'fa-solid fa-graduation-cap')."'></i> Programas de Formación", 0);
?>

<!-- Estilos modernos y profesionales del Dashboard Curricular SENA -->
<style>
    :root {
        --sena-green: #39A900;
        --sena-green-dark: #2d8500;
        --sena-black: #111827;
        --sena-dark: #1f2937;
        --sena-gray: #4b5563;
        --card-radius: 12px;
        --transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .prg-dashboard {
        font-family: inherit;
        color: #111827;
        padding-bottom: 40px;
    }

    /* KPI Cards Grid */
    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 14px;
        margin-bottom: 20px;
    }

    .kpi-card {
        background: #ffffff;
        border-radius: var(--card-radius);
        padding: 16px 18px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        border: 1px solid #e2e8f0;
        transition: var(--transition);
        display: flex;
        align-items: center;
        justify-content: space-between;
        cursor: pointer;
        user-select: none;
        position: relative;
        overflow: hidden;
    }

    .kpi-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
        background: transparent;
        transition: var(--transition);
    }

    .kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08);
        border-color: #cbd5e1;
    }

    .kpi-card.border-green:hover, .kpi-card.border-green.active { border-color: var(--sena-green); }
    .kpi-card.border-green::before { background: var(--sena-green); }

    .kpi-card.border-dark:hover, .kpi-card.border-dark.active { border-color: #111827; }
    .kpi-card.border-dark::before { background: #111827; }

    .kpi-card.border-orange:hover, .kpi-card.border-orange.active { border-color: #ea580c; }
    .kpi-card.border-orange::before { background: #ea580c; }

    .kpi-card.border-purple:hover, .kpi-card.border-purple.active { border-color: #7c3aed; }
    .kpi-card.border-purple::before { background: #7c3aed; }

    .kpi-info .kpi-num {
        font-size: 1.75rem;
        font-weight: 800;
        line-height: 1.1;
        color: #0f172a;
    }

    .kpi-info .kpi-title {
        font-size: 0.78rem;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        margin-top: 3px;
    }

    .kpi-info .kpi-sub {
        font-size: 0.72rem;
        color: #94a3b8;
        margin-top: 2px;
    }

    .kpi-icon-wrap {
        width: 46px;
        height: 46px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }

    .icon-green { background: #ecfdf5; color: var(--sena-green); }
    .icon-dark { background: #f1f5f9; color: #1e293b; }
    .icon-orange { background: #fff7ed; color: #ea580c; }
    .icon-purple { background: #f5f3ff; color: #7c3aed; }

    /* Barra de Herramientas Inteligente */
    .smart-toolbar {
        background: #ffffff;
        border-radius: var(--card-radius);
        padding: 12px 18px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
        margin-bottom: 20px;
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        align-items: center;
        justify-content: space-between;
    }

    .pill-filter {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        font-size: 0.82rem;
        font-weight: 600;
        border-radius: 20px;
        border: 1px solid #cbd5e1;
        background: #ffffff;
        color: #475569;
        cursor: pointer;
        transition: var(--transition);
        text-decoration: none;
    }

    .pill-filter:hover {
        background: #f8fafc;
        border-color: var(--sena-green);
        color: var(--sena-black);
    }

    .pill-filter.active {
        background: var(--sena-green);
        border-color: var(--sena-green-dark);
        color: #ffffff;
        box-shadow: 0 2px 6px rgba(57, 169, 0, 0.25);
    }

    .pill-filter .count-chip {
        font-size: 0.7rem;
        padding: 1px 6px;
        border-radius: 10px;
        background: rgba(0, 0, 0, 0.08);
        color: inherit;
    }

    .pill-filter.active .count-chip {
        background: rgba(255, 255, 255, 0.28);
        color: #ffffff;
    }

    /* GRID DE TARJETAS INTELIGENTES (CARDS) */
    .cards-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
        gap: 18px;
        margin-bottom: 24px;
    }

    .prg-card {
        background: #ffffff;
        border-radius: var(--card-radius);
        border: 1px solid #e2e8f0;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        transition: var(--transition);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        position: relative;
        overflow: hidden;
    }

    .prg-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 24px rgba(0, 0, 0, 0.08);
        border-color: #cbd5e1;
    }

    .prg-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 4px;
        background: #cbd5e1;
    }

    .prg-card.is-active::before {
        background: var(--sena-green);
    }

    .prg-card-header {
        padding: 16px 18px 12px 18px;
        border-bottom: 1px solid #f1f5f9;
    }

    .prg-card-body {
        padding: 14px 18px;
        flex-grow: 1;
    }

    .prg-card-title {
        font-size: 0.96rem;
        font-weight: 700;
        color: #0f172a;
        line-height: 1.4;
        margin-bottom: 8px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        min-height: 2.7rem;
    }

    .prg-meta-pill {
        font-size: 0.73rem;
        font-weight: 600;
        padding: 2px 8px;
        border-radius: 6px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .prg-metrics-box {
        background: #f8fafc;
        border-radius: 10px;
        padding: 10px 12px;
        border: 1px solid #edf2f7;
        margin-top: 10px;
    }

    .prg-metric-item {
        font-size: 0.78rem;
        color: #475569;
    }

    .prg-metric-item strong {
        color: #0f172a;
    }

    .prg-card-footer {
        padding: 12px 18px;
        background: #fafbfc;
        border-top: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
    }

    .btn-action-primary {
        background: #ecfdf5;
        color: var(--sena-green-dark);
        border: 1px solid #bbf7d0;
        font-weight: 700;
        font-size: 0.78rem;
        padding: 6px 12px;
        border-radius: 8px;
        transition: var(--transition);
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .btn-action-primary:hover {
        background: var(--sena-green);
        color: #ffffff;
        border-color: var(--sena-green-dark);
        box-shadow: 0 2px 6px rgba(57, 169, 0, 0.25);
    }

    .badge-status-active {
        background: #ecfdf5;
        color: #166534;
        border: 1px solid #bbf7d0;
        font-weight: 700;
        font-size: 0.72rem;
        padding: 3px 8px;
        border-radius: 20px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .badge-status-inactive {
        background: #f1f5f9;
        color: #475569;
        border: 1px solid #e2e8f0;
        font-weight: 600;
        font-size: 0.72rem;
        padding: 3px 8px;
        border-radius: 20px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .btn-action-icon {
        width: 30px;
        height: 30px;
        border-radius: 6px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8rem;
        transition: var(--transition);
        border: 1px solid transparent;
        background: #f8fafc;
        color: #475569;
        border-color: #e2e8f0;
    }

    .btn-action-icon:hover {
        background: #111827;
        color: #ffffff;
        border-color: #111827;
    }

    .btn-action-icon.del:hover {
        background: #dc2626;
        color: #ffffff;
        border-color: #dc2626;
    }

    .btn-sena-green {
        background: var(--sena-green);
        color: #fff;
        border: none;
        font-weight: 600;
        border-radius: 8px;
        padding: 8px 16px;
        transition: var(--transition);
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .btn-sena-green:hover {
        background: var(--sena-green-dark);
        color: #fff;
        box-shadow: 0 4px 12px rgba(57, 169, 0, 0.25);
    }

    /* Paginación de Tarjetas Inteligentes */
    .cards-pagination-bar {
        background: #ffffff;
        border-radius: var(--card-radius);
        padding: 12px 18px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
        margin-top: 10px;
    }

    /* Barra de navegación limpia entre módulos curriculares */
    .nav-curricular-pills {
        display: inline-flex;
        gap: 6px;
        flex-wrap: wrap;
        margin-bottom: 20px;
        padding: 5px;
        background: #f8fafc;
        border-radius: 14px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
    }

    .btn-nav-pill {
        border-radius: 10px;
        padding: 8px 18px;
        font-size: 0.85rem;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border: 1px solid transparent;
        background: transparent;
        color: #475569;
        transition: var(--transition);
        user-select: none;
    }

    .btn-nav-pill:hover {
        background: #ffffff;
        border-color: #cbd5e1;
        color: var(--sena-black);
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
        transform: translateY(-1px);
    }

    .btn-nav-pill.active {
        background: var(--sena-green);
        border-color: var(--sena-green-dark);
        color: #ffffff !important;
        font-weight: 700;
        box-shadow: 0 3px 10px rgba(57, 169, 0, 0.28);
    }

    .btn-nav-pill.active i {
        color: #ffffff !important;
    }
</style>

<div class="prg-dashboard">

    <!-- 1. DASHBOARD PRINCIPAL DE INFORMACIÓN ÚTIL Y ACCIONABLE -->
    <div class="kpi-grid">
        <!-- KPI 1: TOTAL PROGRAMAS -->
        <div class="kpi-card border-green active" onclick="filtrarCardsPorEstado('todos')" id="kpiTotal" title="Clic para ver todos los programas">
            <div class="kpi-info">
                <div class="kpi-num"><?= $resumenPrg['total_programas'] ?? count($dat ?? []); ?></div>
                <div class="kpi-title">Total Programas</div>
                <div class="kpi-sub"><?= $resumenPrg['total_areas'] ?? 0; ?> áreas formativas</div>
            </div>
            <div class="kpi-icon-wrap icon-green">
                <i class="fa-solid fa-graduation-cap"></i>
            </div>
        </div>

        <!-- KPI 2: PROGRAMAS ACTIVOS -->
        <div class="kpi-card border-green" onclick="filtrarCardsPorEstado('activo')" id="kpiActivos" title="Clic para filtrar solo programas activos en sede">
            <div class="kpi-info">
                <div class="kpi-num text-success"><?= $resumenPrg['programas_activos'] ?? 0; ?></div>
                <div class="kpi-title">Programas Activos</div>
                <div class="kpi-sub"><?= $resumenPrg['fichas_vigentes'] ?? 0; ?> fichas en curso</div>
            </div>
            <div class="kpi-icon-wrap icon-green">
                <i class="fa-solid fa-circle-check text-success"></i>
            </div>
        </div>

        <!-- KPI 3: PROGRAMAS INACTIVOS / HISTÓRICOS -->
        <div class="kpi-card border-dark" onclick="filtrarCardsPorEstado('inactivo')" id="kpiInactivos" title="Clic para filtrar programas inactivos o históricos">
            <div class="kpi-info">
                <div class="kpi-num text-secondary"><?= $resumenPrg['programas_inactivos'] ?? 0; ?></div>
                <div class="kpi-title">Inactivos / Históricos</div>
                <div class="kpi-sub">Sin fichas vigentes</div>
            </div>
            <div class="kpi-icon-wrap icon-dark">
                <i class="fa-solid fa-clock-rotate-left"></i>
            </div>
        </div>

        <!-- KPI 4: FICHAS EN FORMACIÓN -->
        <div class="kpi-card border-orange" title="Fichas activas vigentes sobre el total histórico">
            <div class="kpi-info">
                <div class="kpi-num text-dark"><?= $resumenPrg['fichas_vigentes'] ?? 0; ?> <small style="font-size: 0.95rem; color: #64748b;">/ <?= $resumenPrg['fichas_totales'] ?? 0; ?></small></div>
                <div class="kpi-title">Fichas en Sede</div>
                <div class="kpi-sub">Vigentes vs Históricas</div>
            </div>
            <div class="kpi-icon-wrap icon-orange">
                <i class="fa-solid fa-users text-warning"></i>
            </div>
        </div>

        <!-- KPI 5: CARGA CURRICULAR (MALLA) -->
        <div class="kpi-card border-purple" title="Malla pedagógica total de competencias y resultados formulados">
            <div class="kpi-info">
                <div class="kpi-num text-dark"><?= $resumenPrg['total_competencias'] ?? 0; ?> <small style="font-size: 0.9rem; color: #64748b;">comp.</small></div>
                <div class="kpi-title">Malla Curricular</div>
                <div class="kpi-sub"><?= $resumenPrg['total_resultados'] ?? 0; ?> RAPs formulados</div>
            </div>
            <div class="kpi-icon-wrap icon-purple">
                <i class="fa-solid fa-diagram-project"></i>
            </div>
        </div>
    </div>

    <!-- 2. ENLACES DIRECTOS A LAS OTRAS VISTAS (DEBAJO DEL DASHBOARD) -->
    <div class="nav-curricular-pills">
        <a href="home.php?pg=1527" class="btn-nav-pill active">
            <i class="fa-solid fa-graduation-cap"></i> Programas de Formación
        </a>
        <a href="home.php?pg=1510" class="btn-nav-pill">
            <i class="fa-solid fa-folder-open"></i> Ver Competencias
        </a>
        <a href="home.php?pg=1529" class="btn-nav-pill">
            <i class="fa-solid fa-network-wired"></i> Programa por Competencia
        </a>
        <a href="home.php?pg=1528" class="btn-nav-pill">
            <i class="fa-solid fa-list-check"></i> Resultados de Aprendizaje
        </a>
    </div>

    <!-- 2. BARRA DE CONTROL INTELIGENTE (Buscador + Filtros en vivo + Nuevo + Switcher) -->
    <div class="smart-toolbar">
        <!-- Filtros de Estado en Píldoras -->
        <div class="d-flex align-items-center gap-1.5 flex-wrap">
            <span class="small text-muted fw-bold text-uppercase me-1" style="font-size: 0.72rem;">Filtrar:</span>
            <button type="button" class="pill-filter active" id="pillTodos" onclick="filtrarCardsPorEstado('todos')">
                <i class="fa-solid fa-layer-group"></i> Todos <span class="count-chip" id="chipTotal"><?= count($dat ?? []); ?></span>
            </button>
            <button type="button" class="pill-filter" id="pillActivos" onclick="filtrarCardsPorEstado('activo')">
                <i class="fa-solid fa-circle-check text-success"></i> Activos en Sede <span class="count-chip"><?= $resumenPrg['programas_activos'] ?? 0; ?></span>
            </button>
            <button type="button" class="pill-filter" id="pillInactivos" onclick="filtrarCardsPorEstado('inactivo')">
                <i class="fa-solid fa-clock-rotate-left text-muted"></i> Inactivos <span class="count-chip"><?= $resumenPrg['programas_inactivos'] ?? 0; ?></span>
            </button>

            <!-- Filtro Nivel -->
            <select id="filtroNivelPrg" class="form-select form-select-sm ms-2" style="width: auto;">
                <option value="">Todos los Niveles</option>
                <?php if (!empty($dtTippr)) { foreach ($dtTippr as $tp) { ?>
                    <option value="<?= htmlspecialchars($tp['nomval']); ?>"><?= htmlspecialchars($tp['nomval']); ?></option>
                <?php }} ?>
            </select>

            <!-- Filtro Área -->
            <select id="filtroAreaPrg" class="form-select form-select-sm" style="width: auto;">
                <option value="">Todas las Áreas</option>
                <?php if (!empty($dtArea)) { foreach ($dtArea as $ar) { ?>
                    <option value="<?= htmlspecialchars($ar['nomare']); ?>"><?= htmlspecialchars($ar['nomare']); ?></option>
                <?php }} ?>
            </select>
        </div>

        <!-- Acciones: Buscador en Vivo, Switcher de Vista y Botón Nuevo -->
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <div class="input-group input-group-sm" style="min-width: 240px;">
                <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                <input type="text" id="customSearchPrg" class="form-control border-start-0" placeholder="Buscar por nombre, código o área...">
            </div>

            <!-- Selector de Vista (Tarjetas por defecto) -->
            <div class="btn-group btn-group-sm" role="group">
                <button type="button" class="btn btn-outline-secondary active" id="btnVistaCards" title="Vista Cuadrícula de Tarjetas">
                    <i class="fa-solid fa-grip me-1"></i> Tarjetas
                </button>
                <button type="button" class="btn btn-outline-secondary" id="btnVistaTabla" title="Vista Tabla">
                    <i class="fa-solid fa-table-list me-1"></i> Tabla
                </button>
            </div>

            <button type="button" class="btn btn-sena-green" onclick="abrirModalProgramaNuevo()">
                <i class="fa-solid fa-plus"></i> Nuevo Programa
            </button>
        </div>
    </div>

    <!-- 3. CONTENEDOR 1: GRID DE TARJETAS INTELIGENTES (VISTA POR DEFECTO) -->
    <div id="contenedorCardsProgramasWrapper">
        <div class="cards-grid" id="contenedorCardsProgramas">
            <?php if (!empty($dat)) {
                foreach ($dat as $prg) { 
                    $ficVigentes = (int)($prg['fic_vigentes'] ?? 0);
                    $totFic = (int)($prg['tot_fic'] ?? 0);
                    $esActivo = ($ficVigentes > 0);
                    $estadoStr = $esActivo ? 'activo' : 'inactivo';
                    $cantComp = (int)($prg['cant_comp'] ?? 0);
                    $cantRes = (int)($prg['cant_res'] ?? 0);
                    $totalHoras = (int)($prg['horlpro'] ?? 0) + (int)($prg['horppro'] ?? 0);
                    $nivelStr = $prg['nomval'] ?? 'Titulada';
                    $areaStr = $prg['nomare'] ?? 'General';
                ?>
                    <div class="prg-card item-card-prg <?= $esActivo ? 'is-active' : ''; ?>"
                         data-estado="<?= $estadoStr; ?>"
                         data-nivel="<?= strtolower($nivelStr); ?>"
                         data-area="<?= strtolower($areaStr); ?>"
                         data-search="<?= strtolower($prg['codpro'] . ' ' . $prg['nompro'] . ' ' . $areaStr . ' ' . $nivelStr); ?>">

                        <div class="prg-card-header">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="badge bg-dark bg-opacity-10 text-dark border border-dark border-opacity-25 px-2 py-1 font-monospace">
                                    <i class="fa-solid fa-hashtag me-1 text-muted"></i><?= htmlspecialchars($prg['codpro']); ?> <small class="text-muted">(v<?= htmlspecialchars($prg['verpro'] ?? 1); ?>)</small>
                                </span>

                                <?php if ($esActivo) { ?>
                                    <span class="badge-status-active" title="<?= $ficVigentes; ?> ficha(s) vigentes en formación">
                                        <i class="fa-solid fa-circle-check"></i> Activo (<?= $ficVigentes; ?>)
                                    </span>
                                <?php } else { ?>
                                    <span class="badge-status-inactive" title="Sin fichas activas en este momento">
                                        <i class="fa-solid fa-clock-rotate-left"></i> Histórico
                                    </span>
                                <?php } ?>
                            </div>

                            <div class="d-flex align-items-center gap-1.5 flex-wrap mt-2">
                                <span class="prg-meta-pill bg-light text-dark border">
                                    <i class="fa-solid fa-award text-success me-0.5"></i> <?= htmlspecialchars($nivelStr); ?>
                                </span>
                                <span class="prg-meta-pill bg-light text-secondary border text-truncate" style="max-width: 190px;" title="<?= htmlspecialchars($areaStr); ?>">
                                    <i class="fa-solid fa-layer-group me-0.5"></i> <?= htmlspecialchars($areaStr); ?>
                                </span>
                            </div>
                        </div>

                        <div class="prg-card-body">
                            <div class="prg-card-title" title="<?= htmlspecialchars($prg['nompro']); ?>">
                                <?= htmlspecialchars($prg['nompro']); ?>
                            </div>

                            <div class="prg-metrics-box">
                                <div class="row g-2">
                                    <div class="col-6 prg-metric-item">
                                        <i class="fa-regular fa-clock text-success me-1"></i> Total: <strong><?= number_format($totalHoras); ?>h</strong>
                                        <div class="text-muted small" style="font-size: 0.7rem;">L: <?= $prg['horlpro'] ?? 0; ?>h &bull; P: <?= $prg['horppro'] ?? 0; ?>h</div>
                                    </div>
                                    <div class="col-6 prg-metric-item">
                                        <i class="fa-solid fa-diagram-project text-primary me-1"></i> Malla: <strong><?= $cantComp; ?> comp.</strong>
                                        <div class="text-muted small" style="font-size: 0.7rem;"><?= $cantRes; ?> resultados</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="prg-card-footer">
                            <!-- ACCIÓN DESTACADA: VER COMPETENCIAS -->
                            <a href="home.php?pg=1510&codpro=<?= $prg['codpro']; ?>" class="btn-action-primary" title="Ver matriz y competencias de este programa">
                                <i class="fa-solid fa-folder-open"></i> Ver competencias
                            </a>

                            <div class="d-flex align-items-center gap-1">
                                <a href="home.php?pg=1529&codpro=<?= $prg['codpro']; ?>" class="btn-action-icon" title="Ver Malla en Programa por Competencia">
                                    <i class="fa-solid fa-network-wired text-primary"></i>
                                </a>
                                <button type="button" class="btn-action-icon" title="Ver Ficha Técnica" 
                                        onclick='abrirModalFichaTecnica(<?= htmlspecialchars(json_encode($prg), ENT_QUOTES, 'UTF-8'); ?>)'>
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                                <button type="button" class="btn-action-icon" title="Editar Programa" 
                                        onclick='abrirModalEditarPrograma(<?= htmlspecialchars(json_encode($prg), ENT_QUOTES, 'UTF-8'); ?>)'>
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <button type="button" class="btn-action-icon del" title="Eliminar Programa" 
                                        onclick="confirmarEliminarPrograma('<?= $prg['codpro']; ?>', '<?= htmlspecialchars(addslashes($prg['nompro'])); ?>')">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php } 
            } ?>
        </div>

        <!-- PAGINADO E INFORMACIÓN DE LA PÁGINA (TARJETAS INTELIGENTES) -->
        <div class="cards-pagination-bar d-flex flex-wrap justify-content-between align-items-center gap-2" id="barraPaginacionCardsPrg">
            <div class="small text-muted" id="infoPaginacionCardsPrg">
                Mostrando <strong id="pagCardStartPrg">1</strong> a <strong id="pagCardEndPrg">12</strong> de <strong id="pagCardTotalPrg"><?= count($dat ?? []); ?></strong> programas
            </div>
            <div class="d-flex align-items-center gap-2">
                <select id="selItemsPorPaginaCardsPrg" class="form-select form-select-sm" style="width: auto;" onchange="cambiarItemsPorPaginaCardsPrg(this.value)">
                    <option value="9">9 por página</option>
                    <option value="12" selected>12 por página</option>
                    <option value="24">24 por página</option>
                    <option value="todos">Todos</option>
                </select>
                <nav aria-label="Navegación de tarjetas">
                    <ul class="pagination pagination-sm mb-0" id="paginadorCardsUlPrg">
                        <!-- Botones de paginación generados por JS -->
                    </ul>
                </nav>
            </div>
        </div>

        <!-- ESTADO VACÍO EN CASO DE FILTRADO SIN COINCIDENCIAS -->
        <div id="emptyStateCardsPrg" class="card shadow-sm border-0 rounded-4 text-center p-5 my-4" style="display: none;">
            <div class="p-3 rounded-circle bg-light d-inline-flex mx-auto mb-3 text-muted fs-2" style="width: 64px; height: 64px; align-items: center; justify-content: center;">
                <i class="fa-solid fa-graduation-cap"></i>
            </div>
            <h5 class="fw-bold text-dark">No se encontraron programas coincidentes</h5>
            <p class="text-muted small">Intenta cambiar los términos de búsqueda o los filtros de estado y nivel.</p>
            <div>
                <button type="button" class="btn btn-outline-success btn-sm px-3" onclick="resetearFiltrosCardsPrg()">
                    <i class="fa-solid fa-arrow-rotate-left me-1"></i> Restablecer filtros
                </button>
            </div>
        </div>
    </div>

    <!-- 4. CONTENEDOR 2: VISTA TABLA (OPCIÓN ALTERNATIVA) -->
    <div class="card shadow-sm border-0 rounded-4 overflow-hidden" id="contenedorTablaProgramas" style="display: none;">
        <div class="table-responsive p-3">
            <table id="tablaProgramas" class="table table-hover align-middle mb-0" style="width:100%">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width: 90px;">Código</th>
                        <th>Denominación del Programa</th>
                        <th>Nivel</th>
                        <th>Área Temática</th>
                        <th class="text-center">Horas (L/P/T)</th>
                        <th class="text-center">Malla Curricular</th>
                        <th class="text-center">Estado Sede</th>
                        <th class="text-end" style="width: 140px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($dat)) {
                        foreach ($dat as $prg) { 
                            $ficVigentes = (int)($prg['fic_vigentes'] ?? 0);
                            $totFic = (int)($prg['tot_fic'] ?? 0);
                            $esActivo = ($ficVigentes > 0);
                            $estadoStr = $esActivo ? 'activo' : 'inactivo';
                            $cantComp = (int)($prg['cant_comp'] ?? 0);
                            $cantRes = (int)($prg['cant_res'] ?? 0);
                            $totalHoras = (int)($prg['horlpro'] ?? 0) + (int)($prg['horppro'] ?? 0);
                        ?>
                            <tr data-estado="<?= $estadoStr; ?>" data-nivel="<?= htmlspecialchars($prg['nomval'] ?? ''); ?>" data-area="<?= htmlspecialchars($prg['nomare'] ?? ''); ?>">
                                <td class="text-center fw-bold text-dark font-monospace">
                                    <?= htmlspecialchars($prg['codpro']); ?>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark mb-0" style="font-size: 0.9rem;">
                                        <?= htmlspecialchars($prg['nompro']); ?>
                                    </div>
                                    <span class="badge bg-light text-muted border" style="font-size: 0.7rem;">v<?= htmlspecialchars($prg['verpro'] ?? 1); ?></span>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border px-2 py-1"><?= htmlspecialchars($prg['nomval'] ?? 'Titulada'); ?></span>
                                </td>
                                <td>
                                    <small class="text-muted fw-semibold"><?= htmlspecialchars($prg['nomare'] ?? 'General'); ?></small>
                                </td>
                                <td class="text-center">
                                    <span class="fw-bold text-dark"><?= number_format($totalHoras); ?> hrs</span>
                                    <div class="text-muted" style="font-size: 0.72rem;">(L: <?= $prg['horlpro'] ?? 0; ?> / P: <?= $prg['horppro'] ?? 0; ?>)</div>
                                </td>
                                <td class="text-center">
                                    <div class="d-inline-flex gap-1 align-items-center">
                                        <a href="home.php?pg=1510&codpro=<?= $prg['codpro']; ?>" class="btn btn-sm btn-outline-success px-2 py-1" title="Ver Malla de Competencias">
                                            <i class="fa-solid fa-folder-open me-1"></i> <?= $cantComp; ?> comp.
                                        </a>
                                        <a href="home.php?pg=1529&codpro=<?= $prg['codpro']; ?>" class="btn btn-sm btn-outline-primary px-2 py-1" title="Ver Malla en Programa por Competencia">
                                            <i class="fa-solid fa-network-wired"></i>
                                        </a>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <?php if ($esActivo) { ?>
                                        <span class="badge-status-active">
                                            <i class="fa-solid fa-circle-check"></i> Activo (<?= $ficVigentes; ?>)
                                        </span>
                                    <?php } else { ?>
                                        <span class="badge-status-inactive">
                                            <i class="fa-solid fa-clock-rotate-left"></i> Histórico
                                        </span>
                                    <?php } ?>
                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-1">
                                        <button type="button" class="btn-action-icon" title="Ver Detalle" 
                                                onclick='abrirModalFichaTecnica(<?= htmlspecialchars(json_encode($prg), ENT_QUOTES, 'UTF-8'); ?>)'>
                                            <i class="fa-solid fa-eye"></i>
                                        </button>
                                        <button type="button" class="btn-action-icon" title="Editar" 
                                                onclick='abrirModalEditarPrograma(<?= htmlspecialchars(json_encode($prg), ENT_QUOTES, 'UTF-8'); ?>)'>
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                        <button type="button" class="btn-action-icon del" title="Eliminar" 
                                                onclick="confirmarEliminarPrograma('<?= $prg['codpro']; ?>', '<?= htmlspecialchars(addslashes($prg['nompro'])); ?>')">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php } 
                    } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ========================================================== -->
<!-- MODALES DEL MÓDULO DE PROGRAMAS                            -->
<!-- ========================================================== -->
<!-- Modal 1: Crear / Editar Programa -->
<div class="modal fade" id="modalPrograma" tabindex="-1" aria-labelledby="modalProgramaLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form action="home.php?pg=<?= $pg; ?>" method="POST" id="formPrograma">
                <div class="modal-header border-bottom bg-light px-4 py-3">
                    <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2" id="modalProgramaLabel">
                        <i class="fa-solid fa-graduation-cap text-success"></i> <span id="lblTituloModalPrg">Registrar Nuevo Programa</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" name="ope" value="save">
                    <input type="hidden" name="ope2" id="prg_ope2" value="">

                    <ul class="nav nav-tabs nav-fill mb-3" id="prgTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active fw-bold" id="tab-basica-tab" data-bs-toggle="tab" data-bs-target="#tab-basica" type="button" role="tab">
                                <i class="fa-solid fa-circle-info me-1"></i> Información General
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-bold" id="tab-curricular-tab" data-bs-toggle="tab" data-bs-target="#tab-curricular" type="button" role="tab">
                                <i class="fa-solid fa-clock me-1"></i> Carga Horaria y Créditos
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-bold" id="tab-pedagogica-tab" data-bs-toggle="tab" data-bs-target="#tab-pedagogica" type="button" role="tab">
                                <i class="fa-solid fa-file-lines me-1"></i> Requisitos y Justificación
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="prgTabContent">
                        <!-- Tab 1: General -->
                        <div class="tab-pane fade show active p-2" id="tab-basica" role="tabpanel">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label for="prg_codpro" class="form-label small fw-bold">Código del Programa <span class="text-danger">*</span></label>
                                    <input type="number" name="codpro" id="prg_codpro" class="form-control" placeholder="Ej: 228106" required>
                                </div>
                                <div class="col-md-2">
                                    <label for="prg_verpro" class="form-label small fw-bold">Versión <span class="text-danger">*</span></label>
                                    <input type="number" name="verpro" id="prg_verpro" class="form-control" value="1" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="prg_nompro" class="form-label small fw-bold">Denominación del Programa <span class="text-danger">*</span></label>
                                    <input type="text" name="nompro" id="prg_nompro" class="form-control" placeholder="Ej: ANÁLISIS Y DESARROLLO DE SOFTWARE" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="prg_tippro" class="form-label small fw-bold">Nivel de Formación <span class="text-danger">*</span></label>
                                    <select name="tippro" id="prg_tippro" class="form-select" required>
                                        <option value="">-- Selecciona Nivel --</option>
                                        <?php if (!empty($dtTippr)) { foreach ($dtTippr as $tp) { ?>
                                            <option value="<?= $tp['idval']; ?>"><?= htmlspecialchars($tp['nomval']); ?></option>
                                        <?php }} ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="prg_idare" class="form-label small fw-bold">Área Temática <span class="text-danger">*</span></label>
                                    <select name="idare" id="prg_idare" class="form-select" required>
                                        <option value="">-- Selecciona Área --</option>
                                        <?php if (!empty($dtArea)) { foreach ($dtArea as $ar) { ?>
                                            <option value="<?= $ar['idare']; ?>"><?= htmlspecialchars($ar['nomare']); ?></option>
                                        <?php }} ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="prg_redcon" class="form-label small fw-bold">Red de Conocimiento</label>
                                    <input type="text" name="redcon" id="prg_redcon" class="form-control" placeholder="Ej: Red de Informática y Diseño">
                                </div>
                                <div class="col-md-12">
                                    <label for="prg_despro" class="form-label small fw-bold">Descripción del Perfil de Egreso</label>
                                    <textarea name="despro" id="prg_despro" class="form-control" rows="3" placeholder="Competencias globales y ocupaciones que podrá desempeñar el egresado..."></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Tab 2: Carga Horaria -->
                        <div class="tab-pane fade p-2" id="tab-curricular" role="tabpanel">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label for="prg_horlpro" class="form-label small fw-bold">Horas Etapa Lectiva <span class="text-danger">*</span></label>
                                    <input type="number" name="horlpro" id="prg_horlpro" class="form-control" value="0" min="0" required>
                                </div>
                                <div class="col-md-3">
                                    <label for="prg_horppro" class="form-label small fw-bold">Horas Etapa Práctica <span class="text-danger">*</span></label>
                                    <input type="number" name="horppro" id="prg_horppro" class="form-control" value="0" min="0" required>
                                </div>
                                <div class="col-md-3">
                                    <label for="prg_crelpro" class="form-label small fw-bold">Créditos Lectiva</label>
                                    <input type="number" name="crelpro" id="prg_crelpro" class="form-control" value="0" min="0">
                                </div>
                                <div class="col-md-3">
                                    <label for="prg_creppro" class="form-label small fw-bold">Créditos Práctica</label>
                                    <input type="number" name="creppro" id="prg_creppro" class="form-control" value="0" min="0">
                                </div>
                            </div>
                        </div>

                        <!-- Tab 3: Pedagógica -->
                        <div class="tab-pane fade p-2" id="tab-pedagogica" role="tabpanel">
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label for="prg_just" class="form-label small fw-bold">Justificación del Programa</label>
                                    <textarea name="just" id="prg_just" class="form-control" rows="2" placeholder="Motivación social, productiva y tecnológica del programa..."></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label for="prg_reqing" class="form-label small fw-bold">Requisitos de Ingreso</label>
                                    <textarea name="reqing" id="prg_reqing" class="form-control" rows="2" placeholder="Nivel académico mínimo, edad, pruebas..."></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label for="prg_reqcer" class="form-label small fw-bold">Requisitos de Certificación</label>
                                    <textarea name="reqcer" id="prg_reqcer" class="form-control" rows="2" placeholder="Aprobación de RAPs, paz y salvo etapa productiva..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top bg-light px-4 py-3">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-sena-green">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Guardar Programa
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal 2: Ficha Técnica -->
<div class="modal fade" id="modalFichaTecnica" tabindex="-1" aria-labelledby="modalFichaTecnicaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom bg-light px-4 py-3">
                <div>
                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2.5 py-1 mb-1" id="ft_cod_ver">-</span>
                    <h5 class="modal-title fw-bold text-dark" id="ft_titulo">-</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="text-muted text-uppercase small fw-bold">Nivel Formativo</label>
                        <div class="fw-semibold text-dark" id="ft_nivel">-</div>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted text-uppercase small fw-bold">Área / Red</label>
                        <div class="fw-semibold text-dark" id="ft_area_red">-</div>
                    </div>
                    <div class="col-12">
                        <label class="text-muted text-uppercase small fw-bold">Descripción del Perfil</label>
                        <div class="p-3 bg-light rounded-3 text-secondary small" id="ft_descripcion">-</div>
                    </div>
                    <div class="col-12">
                        <label class="text-muted text-uppercase small fw-bold">Justificación</label>
                        <div class="p-3 bg-light rounded-3 text-secondary small" id="ft_justificacion">-</div>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted text-uppercase small fw-bold">Requisitos de Ingreso</label>
                        <div class="p-2 bg-light rounded-3 text-secondary small" id="ft_req_ingreso">-</div>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted text-uppercase small fw-bold">Requisitos de Certificación</label>
                        <div class="p-2 bg-light rounded-3 text-secondary small" id="ft_req_certificacion">-</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top bg-light px-4 py-3">
                <a href="#" id="ft_btn_competencias" class="btn btn-sena-green">
                    <i class="fa-solid fa-folder-open me-1"></i> Ver Competencias
                </a>
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================== -->
<!-- SCRIPTS DINÁMICOS, PAGINADO Y CONTROL DE TARJETAS          -->
<!-- ========================================================== -->
<script>
let estadoFiltroActual = 'todos';
let paginaActualCardsPrg = 1;
let itemsPorPaginaCardsPrg = 12;
let tablaPrgDT = null;

$(document).ready(function() {
    // 1. Inicializar DataTable para la vista alternativa de Tabla
    if ($.fn.DataTable.isDataTable('#tablaProgramas')) {
        $('#tablaProgramas').DataTable().destroy();
    }

    tablaPrgDT = $('#tablaProgramas').DataTable({
        language: {
            processing: "Procesando...",
            search: "_INPUT_",
            searchPlaceholder: "Buscar programas...",
            lengthMenu: "Mostrar _MENU_ registros",
            info: "Mostrando _START_ a _END_ de _TOTAL_ programas",
            infoEmpty: "Mostrando 0 programas",
            infoFiltered: "(filtrado de _MAX_ totales)",
            zeroRecords: "No se encontraron programas con los filtros aplicados",
            paginate: {
                first: "Primero",
                previous: "Anterior",
                next: "Siguiente",
                last: "Último"
            }
        },
        pageLength: 10,
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Todos"]],
        responsive: true,
        order: [[0, 'asc']]
    });

    // 2. Alternar entre Vista Tarjetas y Vista Tabla
    $('#btnVistaCards').on('click', function() {
        $(this).addClass('active');
        $('#btnVistaTabla').removeClass('active');
        $('#contenedorCardsProgramasWrapper').show();
        $('#contenedorTablaProgramas').hide();
    });

    $('#btnVistaTabla').on('click', function() {
        $(this).addClass('active');
        $('#btnVistaCards').removeClass('active');
        $('#contenedorCardsProgramasWrapper').hide();
        $('#contenedorTablaProgramas').show();
        if (tablaPrgDT) {
            tablaPrgDT.columns.adjust().responsive.recalc();
        }
    });

    // 3. Buscador en Vivo unificado (para tarjetas y tabla)
    $('#customSearchPrg').on('input keyup', function() {
        paginaActualCardsPrg = 1;
        ejecutarFiltroCardsProgramas();
        if (tablaPrgDT) {
            tablaPrgDT.search(this.value).draw();
        }
    });

    // 4. Filtro por Nivel y Área
    $('#filtroNivelPrg, #filtroAreaPrg').on('change', function() {
        paginaActualCardsPrg = 1;
        ejecutarFiltroCardsProgramas();
        if (tablaPrgDT) {
            const nivelVal = $('#filtroNivelPrg').val();
            const areaVal = $('#filtroAreaPrg').val();
            tablaPrgDT.column(2).search(nivelVal ? '^' + nivelVal + '$' : '', true, false);
            tablaPrgDT.column(3).search(areaVal ? areaVal : '', false, false);
            tablaPrgDT.draw();
        }
    });

    // Inicializar paginación de tarjetas
    ejecutarFiltroCardsProgramas();

    // 5. Cargar edición por GET si aplica
    <?php if (!empty($dtOne) && !empty($dtOne[0]['codpro'])) { ?>
        abrirModalEditarPrograma(<?= json_encode($dtOne[0]); ?>);
    <?php } ?>

    // 6. SweetAlert2 notificaciones
    const urlParams = new URLSearchParams(window.location.search);
    const msg = urlParams.get('msg');
    if (msg) {
        let titulo = '¡Éxito!';
        let texto = 'Operación realizada correctamente.';
        let icono = 'success';

        switch (msg) {
            case 'guardado':
                texto = 'El programa de formación fue registrado exitosamente.';
                break;
            case 'editado':
                texto = 'El programa de formación fue actualizado correctamente.';
                break;
            case 'eliminado':
                texto = 'El programa de formación ha sido eliminado.';
                break;
            case 'duplicado':
                titulo = 'Código Duplicado';
                texto = 'El código ingresado ya se encuentra registrado para otro programa.';
                icono = 'error';
                break;
            case 'error_guardar':
                titulo = 'Error';
                texto = 'No fue posible guardar el programa.';
                icono = 'error';
                break;
        }

        Swal.fire({
            title: titulo,
            text: texto,
            icon: icono,
            timer: 2400,
            showConfirmButton: false
        });

        window.history.replaceState({}, document.title, window.location.pathname + window.location.search.replace(/[?&]msg=[^&]+/, ''));
    }
});

// Función de filtro dinámico inteligente para las Tarjetas
function filtrarCardsPorEstado(estado) {
    estadoFiltroActual = estado;
    paginaActualCardsPrg = 1;

    $('.pill-filter').removeClass('active');
    $('.kpi-card').removeClass('active');

    if (estado === 'activo') {
        $('#pillActivos').addClass('active');
        $('#kpiActivos').addClass('active');
        if (tablaPrgDT) { tablaPrgDT.column(6).search('Activo').draw(); }
    } else if (estado === 'inactivo') {
        $('#pillInactivos').addClass('active');
        $('#kpiInactivos').addClass('active');
        if (tablaPrgDT) { tablaPrgDT.column(6).search('Histórico|Inactivo', true, false).draw(); }
    } else {
        $('#pillTodos').addClass('active');
        $('#kpiTotal').addClass('active');
        if (tablaPrgDT) { tablaPrgDT.column(6).search('').draw(); }
    }

    ejecutarFiltroCardsProgramas();
}

function cambiarItemsPorPaginaCardsPrg(val) {
    itemsPorPaginaCardsPrg = (val === 'todos') ? 99999 : parseInt(val);
    paginaActualCardsPrg = 1;
    ejecutarFiltroCardsProgramas();
}

function ejecutarFiltroCardsProgramas() {
    const busqueda = $('#customSearchPrg').val().toLowerCase().trim();
    const nivelSel = $('#filtroNivelPrg').val().toLowerCase().trim();
    const areaSel = $('#filtroAreaPrg').val().toLowerCase().trim();

    // 1. Filtrar los elementos coincidentes
    const coincidentes = [];
    $('.item-card-prg').each(function() {
        const card = $(this);
        const cardEstado = card.data('estado');
        const cardNivel = String(card.data('nivel') || '');
        const cardArea = String(card.data('area') || '');
        const cardSearchText = String(card.data('search') || '');

        const coincideEstado = (estadoFiltroActual === 'todos') || (cardEstado === estadoFiltroActual);
        const coincideNivel = (nivelSel === '') || (cardNivel.indexOf(nivelSel) > -1);
        const coincideArea = (areaSel === '') || (cardArea.indexOf(areaSel) > -1);
        const coincideBusqueda = (busqueda === '') || (cardSearchText.indexOf(busqueda) > -1);

        if (coincideEstado && coincideNivel && coincideArea && coincideBusqueda) {
            coincidentes.push(card);
        } else {
            card.hide();
        }
    });

    const totalCoincidentes = coincidentes.length;

    if (totalCoincidentes === 0) {
        $('#emptyStateCardsPrg').show();
        $('#contenedorCardsProgramas').hide();
        $('#barraPaginacionCardsPrg').hide();
        return;
    }

    $('#emptyStateCardsPrg').hide();
    if (!$('#btnVistaTabla').hasClass('active')) {
        $('#contenedorCardsProgramas').show();
    }
    $('#barraPaginacionCardsPrg').show();

    // 2. Calcular límites de paginación
    const totalPaginas = Math.ceil(totalCoincidentes / itemsPorPaginaCardsPrg) || 1;
    if (paginaActualCardsPrg > totalPaginas) paginaActualCardsPrg = totalPaginas;
    if (paginaActualCardsPrg < 1) paginaActualCardsPrg = 1;

    const startIdx = (paginaActualCardsPrg - 1) * itemsPorPaginaCardsPrg;
    const endIdx = Math.min(startIdx + itemsPorPaginaCardsPrg, totalCoincidentes);

    // 3. Mostrar únicamente los elementos de la página actual
    coincidentes.forEach((card, index) => {
        if (index >= startIdx && index < endIdx) {
            card.show();
        } else {
            card.hide();
        }
    });

    // 4. Actualizar información de la página
    $('#pagCardStartPrg').text(totalCoincidentes > 0 ? (startIdx + 1) : 0);
    $('#pagCardEndPrg').text(endIdx);
    $('#pagCardTotalPrg').text(totalCoincidentes);

    // 5. Renderizar controles de paginación
    renderizarPaginadorCardsPrg(totalPaginas);
}

function irAPaginaCardsPrg(pag) {
    paginaActualCardsPrg = pag;
    ejecutarFiltroCardsProgramas();
    $('html, body').animate({ scrollTop: $('#contenedorCardsProgramasWrapper').offset().top - 80 }, 200);
}

function renderizarPaginadorCardsPrg(totalPaginas) {
    const ul = $('#paginadorCardsUlPrg');
    ul.empty();

    if (totalPaginas <= 1) return;

    // Botón Anterior
    const prevDisabled = (paginaActualCardsPrg === 1) ? 'disabled' : '';
    ul.append(`<li class="page-item ${prevDisabled}">
        <a class="page-link" href="javascript:void(0)" onclick="irAPaginaCardsPrg(${paginaActualCardsPrg - 1})">
            &laquo; Anterior
        </a>
    </li>`);

    // Botones de página
    let startPage = Math.max(1, paginaActualCardsPrg - 2);
    let endPage = Math.min(totalPaginas, startPage + 4);
    if (endPage - startPage < 4) {
        startPage = Math.max(1, endPage - 4);
    }

    if (startPage > 1) {
        ul.append(`<li class="page-item"><a class="page-link" href="javascript:void(0)" onclick="irAPaginaCardsPrg(1)">1</a></li>`);
        if (startPage > 2) {
            ul.append(`<li class="page-item disabled"><span class="page-link">...</span></li>`);
        }
    }

    for (let i = startPage; i <= endPage; i++) {
        const activeClass = (i === paginaActualCardsPrg) ? 'active bg-success border-success text-white' : '';
        ul.append(`<li class="page-item ${i === paginaActualCardsPrg ? 'active' : ''}">
            <a class="page-link ${activeClass}" href="javascript:void(0)" onclick="irAPaginaCardsPrg(${i})">${i}</a>
        </li>`);
    }

    if (endPage < totalPaginas) {
        if (endPage < totalPaginas - 1) {
            ul.append(`<li class="page-item disabled"><span class="page-link">...</span></li>`);
        }
        ul.append(`<li class="page-item"><a class="page-link" href="javascript:void(0)" onclick="irAPaginaCardsPrg(${totalPaginas})">${totalPaginas}</a></li>`);
    }

    // Botón Siguiente
    const nextDisabled = (paginaActualCardsPrg === totalPaginas) ? 'disabled' : '';
    ul.append(`<li class="page-item ${nextDisabled}">
        <a class="page-link" href="javascript:void(0)" onclick="irAPaginaCardsPrg(${paginaActualCardsPrg + 1})">
            Siguiente &raquo;
        </a>
    </li>`);
}

function resetearFiltrosCardsPrg() {
    $('#customSearchPrg').val('');
    $('#filtroNivelPrg').val('');
    $('#filtroAreaPrg').val('');
    filtrarCardsPorEstado('todos');
}

// Modales
function abrirModalProgramaNuevo() {
    $('#formPrograma')[0].reset();
    $('#lblTituloModalPrg').text('Registrar Nuevo Programa');
    $('#prg_ope2').val('');
    $('#prg_codpro').prop('readonly', false);
    const triggerFirstTab = document.querySelector('#tab-basica-tab');
    bootstrap.Tab.getInstance(triggerFirstTab)?.show() || new bootstrap.Tab(triggerFirstTab).show();
    const modal = new bootstrap.Modal(document.getElementById('modalPrograma'));
    modal.show();
}

function abrirModalEditarPrograma(prg) {
    $('#formPrograma')[0].reset();
    $('#lblTituloModalPrg').text('Editar Programa de Formación');
    $('#prg_ope2').val('edit');
    $('#prg_codpro').val(prg.codpro).prop('readonly', true);
    $('#prg_nompro').val(prg.nompro);
    $('#prg_verpro').val(prg.verpro);
    $('#prg_tippro').val(prg.tippro);
    $('#prg_idare').val(prg.idare);
    $('#prg_redcon').val(prg.redcon);
    $('#prg_despro').val(prg.despro);
    $('#prg_horlpro').val(prg.horlpro);
    $('#prg_horppro').val(prg.horppro);
    $('#prg_crelpro').val(prg.crelpro);
    $('#prg_creppro').val(prg.creppro);
    $('#prg_just').val(prg.just);
    $('#prg_reqing').val(prg.reqing);
    $('#prg_reqcer').val(prg.reqcer);

    const triggerFirstTab = document.querySelector('#tab-basica-tab');
    bootstrap.Tab.getInstance(triggerFirstTab)?.show() || new bootstrap.Tab(triggerFirstTab).show();
    const modal = new bootstrap.Modal(document.getElementById('modalPrograma'));
    modal.show();
}

function abrirModalFichaTecnica(prg) {
    $('#ft_titulo').text(prg.nompro);
    $('#ft_cod_ver').text(prg.codpro + ' (v' + (prg.verpro || '1') + ')');
    $('#ft_nivel').text(prg.nomval || 'Titulada');
    $('#ft_area_red').text((prg.nomare || 'General') + ' / ' + (prg.redcon || 'N/A'));
    $('#ft_descripcion').text(prg.despro || 'Sin descripción registrada.');
    $('#ft_justificacion').text(prg.just || 'Sin justificación registrada.');
    $('#ft_req_ingreso').text(prg.reqing || 'Sin requisitos registrados.');
    $('#ft_req_certificacion').text(prg.reqcer || 'Sin requisitos registrados.');
    $('#ft_btn_competencias').attr('href', 'home.php?pg=1510&codpro=' + prg.codpro);

    const modal = new bootstrap.Modal(document.getElementById('modalFichaTecnica'));
    modal.show();
}

function confirmarEliminarPrograma(codpro, nompro) {
    Swal.fire({
        title: '¿Eliminar Programa?',
        html: `¿Estás seguro de eliminar el programa <strong>"${nompro}"</strong> (${codpro})? Esta acción no se puede deshacer.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `home.php?pg=<?= $pg; ?>&codpro=${codpro}&ope=del`;
        }
    });
}
</script>
