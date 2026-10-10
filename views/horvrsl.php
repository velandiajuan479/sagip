<?php 
require_once 'controllers/horcrsl.php'; 

// Renderizar únicamente el título oficial de SAGISENA encima del dashboard
echo titulo2("<i class='".($icono ?? 'fa-solid fa-list-check')."'></i> Resultados de Aprendizaje", 0);
?>

<!-- Estilos optimizados y consistentes del módulo de Resultados de Aprendizaje (RAPs) -->
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

    .rsl-dashboard {
        font-family: inherit;
        color: #111827;
        padding-bottom: 40px;
    }

    /* KPI Cards Grid */
    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
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

    .kpi-card.border-amber:hover, .kpi-card.border-amber.active { border-color: #d97706; }
    .kpi-card.border-amber::before { background: #d97706; }

    .kpi-info .kpi-num {
        font-size: 1.75rem;
        font-weight: 800;
        line-height: 1.1;
        color: #0f172a;
    }

    .kpi-info .kpi-title {
        font-size: 0.76rem;
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
        width: 44px;
        height: 44px;
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
    .icon-amber { background: #fffbeb; color: #d97706; }

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

    /* GRID DE TARJETAS INTELIGENTES DE RAPs */
    .cards-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 18px;
        margin-bottom: 24px;
    }

    .rsl-card {
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

    .rsl-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 24px rgba(0, 0, 0, 0.08);
        border-color: #cbd5e1;
    }

    .rsl-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 4px;
        background: #cbd5e1;
    }

    .rsl-card.is-active::before {
        background: var(--sena-green);
    }

    .rsl-card.is-orphan::before {
        background: #d97706;
    }

    .rsl-card-header {
        padding: 16px 18px 12px 18px;
        border-bottom: 1px solid #f1f5f9;
    }

    .rsl-card-body {
        padding: 14px 18px;
        flex-grow: 1;
    }

    .rsl-card-title {
        font-size: 0.95rem;
        font-weight: 600;
        color: #0f172a;
        line-height: 1.45;
        margin-bottom: 10px;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
        min-height: 4.1rem;
    }

    .rsl-comp-box {
        background: #f8fafc;
        border-radius: 10px;
        padding: 10px 12px;
        border: 1px solid #edf2f7;
        margin-top: 10px;
    }

    .rsl-card-footer {
        padding: 12px 18px;
        background: #fafbfc;
        border-top: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
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

    .badge-status-orphan {
        background: #fffbeb;
        color: #b45309;
        border: 1px solid #fde68a;
        font-weight: 700;
        font-size: 0.72rem;
        padding: 3px 8px;
        border-radius: 20px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
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

<div class="rsl-dashboard">

    <!-- 1. DASHBOARD KPI CARDS -->
    <div class="kpi-grid">
        <div class="kpi-card border-dark active" onclick="filtrarCardsRslPorEstado('todos')" id="kpiRslTotal" title="Ver todos los resultados">
            <div class="kpi-info">
                <div class="kpi-num"><?= number_format($resumen['total_resultados'] ?? count($datAll ?? [])); ?></div>
                <div class="kpi-title">Resultados Formulados</div>
                <div class="kpi-sub">Total en catálogo</div>
            </div>
            <div class="kpi-icon-wrap icon-dark">
                <i class="fa-solid fa-list-check"></i>
            </div>
        </div>

        <div class="kpi-card border-green" onclick="filtrarCardsRslPorEstado('activo')" id="kpiRslActivos" title="Ver resultados con fichas activas">
            <div class="kpi-info">
                <div class="kpi-num text-success"><?= number_format($resumen['resultados_activos'] ?? 0); ?></div>
                <div class="kpi-title">En Formación Activa</div>
                <div class="kpi-sub">Con fichas en sede</div>
            </div>
            <div class="kpi-icon-wrap icon-green">
                <i class="fa-solid fa-chalkboard-user"></i>
            </div>
        </div>

        <div class="kpi-card border-orange" onclick="filtrarCardsRslPorEstado('historico')" id="kpiRslHistoricos" title="Ver resultados históricos sin fichas actuales">
            <div class="kpi-info">
                <div class="kpi-num" style="color: #ea580c;"><?= number_format($resumen['resultados_inactivos'] ?? 0); ?></div>
                <div class="kpi-title">Catálogo Histórico</div>
                <div class="kpi-sub">Sin fichas en curso</div>
            </div>
            <div class="kpi-icon-wrap icon-orange">
                <i class="fa-solid fa-box-archive"></i>
            </div>
        </div>

        <div class="kpi-card border-amber" onclick="filtrarCardsRslPorEstado('huerfano')" id="kpiRslHuerfanos" title="Ver resultados sin programa asignado">
            <div class="kpi-info">
                <div class="kpi-num" style="color: #d97706;"><?= number_format($resumen['raps_sin_programa'] ?? 0); ?></div>
                <div class="kpi-title">Sin Programa / Huérfanos</div>
                <div class="kpi-sub">Competencia no asignada</div>
            </div>
            <div class="kpi-icon-wrap icon-amber">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
        </div>

        <div class="kpi-card border-purple" title="Intensidad global de sesiones formativas">
            <div class="kpi-info">
                <div class="kpi-num" style="color: #7c3aed;"><?= number_format($resumen['total_sesiones'] ?? 0); ?></div>
                <div class="kpi-title">Sesiones Formativas</div>
                <div class="kpi-sub">Promedio: <strong><?= $resumen['promedio_sesiones'] ?? 0; ?></strong> ses/RAP</div>
            </div>
            <div class="kpi-icon-wrap icon-purple">
                <i class="fa-solid fa-stopwatch"></i>
            </div>
        </div>
    </div>

    <!-- ENLACES DIRECTOS A LAS OTRAS VISTAS (DEBAJO DEL DASHBOARD) -->
    <div class="nav-curricular-pills">
        <a href="home.php?pg=1527" class="btn-nav-pill">
            <i class="fa-solid fa-graduation-cap"></i> Programas de Formación
        </a>
        <a href="home.php?pg=1510" class="btn-nav-pill">
            <i class="fa-solid fa-folder-open"></i> Ver Competencias
        </a>
        <a href="home.php?pg=1529" class="btn-nav-pill">
            <i class="fa-solid fa-network-wired"></i> Programa por Competencia
        </a>
        <a href="home.php?pg=1528" class="btn-nav-pill active">
            <i class="fa-solid fa-list-check"></i> Resultados de Aprendizaje
        </a>
    </div>

    <!-- 2. BARRA DE CONTROL INTELIGENTE (Píldoras + Buscador + Switcher + Nuevo) -->
    <div class="smart-toolbar">
        <div class="d-flex align-items-center gap-1.5 flex-wrap">
            <span class="small text-muted fw-bold text-uppercase me-1" style="font-size: 0.72rem;">Filtrar:</span>
            <button type="button" class="pill-filter active" id="pillRslTodos" onclick="filtrarCardsRslPorEstado('todos')">
                <i class="fa-solid fa-layer-group"></i> Todos <span class="count-chip"><?= count($datAll ?? []); ?></span>
            </button>
            <button type="button" class="pill-filter" id="pillRslActivos" onclick="filtrarCardsRslPorEstado('activo')">
                <i class="fa-solid fa-circle-check text-success"></i> Activos en Sede <span class="count-chip"><?= $resumen['resultados_activos'] ?? 0; ?></span>
            </button>
            <button type="button" class="pill-filter" id="pillRslHistoricos" onclick="filtrarCardsRslPorEstado('historico')">
                <i class="fa-solid fa-clock-rotate-left text-muted"></i> Históricos <span class="count-chip"><?= $resumen['resultados_inactivos'] ?? 0; ?></span>
            </button>
            <button type="button" class="pill-filter" id="pillRslHuerfanos" onclick="filtrarCardsRslPorEstado('huerfano')">
                <i class="fa-solid fa-triangle-exclamation text-warning"></i> Sin Programa <span class="count-chip"><?= $resumen['raps_sin_programa'] ?? 0; ?></span>
            </button>

            <!-- Filtro Tipología -->
            <select id="filtroTipoRsl" class="form-select form-select-sm ms-2" style="width: auto;">
                <option value="">Todas las Tipologías</option>
                <option value="técnica">Técnica</option>
                <option value="transversal">Transversal</option>
                <option value="básica">Básica / Clave</option>
            </select>

            <!-- Filtro Programa -->
            <select id="filtroProgRsl" class="form-select form-select-sm" style="width: auto; max-width: 200px;">
                <option value="">Todos los Programas</option>
                <?php if (!empty($dtProg)) { foreach ($dtProg as $prg) { ?>
                    <option value="<?= htmlspecialchars($prg['nompro']); ?>"><?= htmlspecialchars($prg['nompro']); ?></option>
                <?php }} ?>
            </select>
        </div>

        <div class="d-flex align-items-center gap-2 flex-wrap">
            <div class="input-group input-group-sm" style="min-width: 240px;">
                <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                <input type="text" id="customSearchRsl" class="form-control border-start-0" placeholder="Buscar por código, RAP o competencia...">
            </div>

            <!-- Selector de Vista (Tarjetas por defecto) -->
            <div class="btn-group btn-group-sm" role="group">
                <button type="button" class="btn btn-outline-secondary active" id="btnVistaCardsRsl" title="Vista Cuadrícula de Tarjetas">
                    <i class="fa-solid fa-grip me-1"></i> Tarjetas
                </button>
                <button type="button" class="btn btn-outline-secondary" id="btnVistaTablaRsl" title="Vista Tabla">
                    <i class="fa-solid fa-table-list me-1"></i> Tabla
                </button>
            </div>

            <button type="button" class="btn btn-sena-green" onclick="abrirModalResultadoNuevo()">
                <i class="fa-solid fa-plus me-1"></i> Nuevo Resultado
            </button>
        </div>
    </div>

    <!-- 3. CONTENEDOR 1: GRID DE TARJETAS INTELIGENTES DE RAPs -->
    <div id="contenedorCardsResultadosWrapper">
        <div class="cards-grid" id="contenedorCardsResultados">
            <?php if (!empty($datAll)) {
                foreach ($datAll as $rsl) { 
                    $esActivo = (($rsl['fic_vigentes'] ?? 0) > 0);
                    $esHuerfano = (empty($rsl['cant_progs']) || empty($rsl['codpro']));
                    $statusKey = $esHuerfano ? 'huerfano' : ($esActivo ? 'activo' : 'historico');
                    $tipoNormalizado = strtolower($rsl['nomval'] ?? '');
                    $progNormalizado = strtolower($rsl['nompro'] ?? '');
                ?>
                    <div class="rsl-card item-card-rsl <?= $esActivo ? 'is-active' : ($esHuerfano ? 'is-orphan' : ''); ?>"
                         data-estado="<?= $statusKey; ?>"
                         data-tipo="<?= $tipoNormalizado; ?>"
                         data-prog="<?= $progNormalizado; ?>"
                         data-search="<?= strtolower($rsl['idres'] . ' ' . $rsl['nomres'] . ' ' . $rsl['idcom'] . ' ' . $rsl['descom'] . ' ' . ($rsl['nompro'] ?? '')); ?>">

                        <div class="rsl-card-header">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="badge bg-dark bg-opacity-10 text-dark border border-dark border-opacity-25 px-2 py-1 font-monospace">
                                    <i class="fa-solid fa-hashtag me-0.5 text-muted"></i>RAP: <?= htmlspecialchars($rsl['idres']); ?>
                                </span>

                                <?php if ($esHuerfano) { ?>
                                    <span class="badge-status-orphan" title="Competencia no vinculada a programas">
                                        <i class="fa-solid fa-triangle-exclamation"></i> Sin Programa
                                    </span>
                                <?php } elseif ($esActivo) { ?>
                                    <span class="badge-status-active" title="<?= $rsl['fic_vigentes']; ?> fichas activas recibiendo este resultado">
                                        <i class="fa-solid fa-circle-check"></i> Activo (<?= $rsl['fic_vigentes']; ?>)
                                    </span>
                                <?php } else { ?>
                                    <span class="badge-status-inactive" title="Sin fichas activas en este momento">
                                        <i class="fa-solid fa-clock-rotate-left"></i> Histórico
                                    </span>
                                <?php } ?>
                            </div>

                            <div class="d-flex align-items-center gap-1.5 flex-wrap mt-2">
                                <span class="badge bg-light text-dark border px-2 py-0.5 small">
                                    <?= htmlspecialchars($rsl['nomval'] ?? 'Competencia'); ?>
                                </span>
                                <span class="badge rounded-pill bg-light text-dark border px-2 py-0.5 small font-monospace">
                                    <i class="fa-regular fa-clock me-1 text-success"></i><?= htmlspecialchars($rsl['ndeses'] ?? 1); ?> sesiones
                                </span>
                            </div>
                        </div>

                        <div class="rsl-card-body">
                            <div class="rsl-card-title" title="<?= htmlspecialchars($rsl['nomres']); ?>">
                                <?= htmlspecialchars($rsl['nomres']); ?>
                            </div>

                            <div class="rsl-comp-box">
                                <div class="small fw-bold text-dark text-truncate mb-1" title="<?= htmlspecialchars($rsl['descom'] ?? ''); ?>">
                                    <span class="text-muted font-monospace">[<?= htmlspecialchars($rsl['idcom'] ?? ''); ?>]</span> <?= htmlspecialchars($rsl['descom'] ?? ''); ?>
                                </div>
                                <?php if (!empty($rsl['nompro'])) { ?>
                                    <div class="small text-muted text-truncate" title="<?= htmlspecialchars($rsl['nompro']); ?>">
                                        <i class="fa-solid fa-graduation-cap text-success me-1"></i><?= htmlspecialchars($rsl['nompro']); ?>
                                    </div>
                                <?php } else { ?>
                                    <div class="small text-warning-emphasis">
                                        <i class="fa-solid fa-triangle-exclamation me-1"></i> Sin programa vinculado
                                    </div>
                                <?php } ?>
                            </div>
                        </div>

                        <div class="rsl-card-footer">
                            <button type="button" class="btn-action-primary" onclick='verDetalleCompletoRAP(<?= htmlspecialchars(json_encode($rsl), ENT_QUOTES, 'UTF-8'); ?>)'>
                                <i class="fa-solid fa-eye"></i> Ver detalle
                            </button>

                            <div class="d-flex align-items-center gap-1">
                                <button type="button" class="btn-action-icon" title="Editar Resultado" 
                                        onclick='abrirModalEditarResultado(<?= htmlspecialchars(json_encode($rsl), ENT_QUOTES, 'UTF-8'); ?>)'>
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <button type="button" class="btn-action-icon del" title="Eliminar Resultado" 
                                        onclick="confirmarEliminarResultado('<?= $rsl['idres']; ?>', '<?= htmlspecialchars(addslashes(preg_replace('/[\r\n]+/', ' ', substr($rsl['nomres'] ?? '', 0, 80)))); ?>')">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php } 
            } ?>
        </div>

        <!-- PAGINADO E INFORMACIÓN DE LA PÁGINA (TARJETAS INTELIGENTES) -->
        <div class="cards-pagination-bar d-flex flex-wrap justify-content-between align-items-center gap-2" id="barraPaginacionCardsRsl">
            <div class="small text-muted" id="infoPaginacionCardsRsl">
                Mostrando <strong id="pagCardStartRsl">1</strong> a <strong id="pagCardEndRsl">12</strong> de <strong id="pagCardTotalRsl"><?= count($datAll ?? []); ?></strong> resultados
            </div>
            <div class="d-flex align-items-center gap-2">
                <select id="selItemsPorPaginaCardsRsl" class="form-select form-select-sm" style="width: auto;" onchange="cambiarItemsPorPaginaCardsRsl(this.value)">
                    <option value="9">9 por página</option>
                    <option value="12" selected>12 por página</option>
                    <option value="24">24 por página</option>
                    <option value="todos">Todos</option>
                </select>
                <nav aria-label="Navegación de tarjetas">
                    <ul class="pagination pagination-sm mb-0" id="paginadorCardsUlRsl">
                        <!-- Botones de paginación generados por JS -->
                    </ul>
                </nav>
            </div>
        </div>

        <!-- ESTADO VACÍO EN CASO DE FILTRADO SIN COINCIDENCIAS -->
        <div id="emptyStateCardsRsl" class="card shadow-sm border-0 rounded-4 text-center p-5 my-4" style="display: none;">
            <div class="p-3 rounded-circle bg-light d-inline-flex mx-auto mb-3 text-muted fs-2" style="width: 64px; height: 64px; align-items: center; justify-content: center;">
                <i class="fa-solid fa-list-check"></i>
            </div>
            <h5 class="fw-bold text-dark">No se encontraron resultados coincidentes</h5>
            <p class="text-muted small">Intenta cambiar los términos de búsqueda o los filtros de estado y programa.</p>
            <div>
                <button type="button" class="btn btn-outline-success btn-sm px-3" onclick="resetearFiltrosCardsRsl()">
                    <i class="fa-solid fa-arrow-rotate-left me-1"></i> Restablecer filtros
                </button>
            </div>
        </div>
    </div>

    <!-- 4. CONTENEDOR 2: VISTA TABLA (OPCIÓN ALTERNATIVA) -->
    <div class="card shadow-sm border-0 rounded-4 overflow-hidden" id="contenedorTablaResultados" style="display: none;">
        <div class="table-responsive p-3">
            <table id="tablaResultados" class="table table-hover align-middle mb-0" style="width:100%">
                <thead class="table-light">
                    <tr>
                        <th style="width: 85px;">Cód. RAP</th>
                        <th>Resultado de Aprendizaje (Logro)</th>
                        <th>Competencia Padre</th>
                        <th style="width: 100px;">Tipología</th>
                        <th class="text-center" style="width: 80px;">Sesiones</th>
                        <th class="text-center" style="width: 120px;">Estado Sede</th>
                        <th class="text-end" style="width: 100px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($datAll)) {
                        foreach ($datAll as $rsl) { 
                            $esActivo = (($rsl['fic_vigentes'] ?? 0) > 0);
                            $esHuerfano = (empty($rsl['cant_progs']) || empty($rsl['codpro']));
                        ?>
                            <tr>
                                <td class="fw-bold font-monospace text-dark">[<?= htmlspecialchars($rsl['idres']); ?>]</td>
                                <td>
                                    <div class="small fw-semibold text-dark text-truncate" style="max-width: 320px;"><?= htmlspecialchars($rsl['nomres']); ?></div>
                                </td>
                                <td>
                                    <div class="small text-muted text-truncate" style="max-width: 220px;">[<?= htmlspecialchars($rsl['idcom'] ?? ''); ?>] <?= htmlspecialchars($rsl['descom'] ?? ''); ?></div>
                                </td>
                                <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($rsl['nomval'] ?? 'Competencia'); ?></span></td>
                                <td class="text-center fw-bold"><?= $rsl['ndeses'] ?? 1; ?> ses.</td>
                                <td class="text-center">
                                    <?php if ($esHuerfano) { ?>
                                        <span class="badge-status-orphan">Sin Programa</span>
                                    <?php } elseif ($esActivo) { ?>
                                        <span class="badge-status-active">Activo (<?= $rsl['fic_vigentes']; ?>)</span>
                                    <?php } else { ?>
                                        <span class="badge-status-inactive">Histórico</span>
                                    <?php } ?>
                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-1">
                                        <button type="button" class="btn-action-icon" title="Ver Detalle" onclick='verDetalleCompletoRAP(<?= htmlspecialchars(json_encode($rsl), ENT_QUOTES, 'UTF-8'); ?>)'>
                                            <i class="fa-solid fa-eye"></i>
                                        </button>
                                        <button type="button" class="btn-action-icon" title="Editar" onclick='abrirModalEditarResultado(<?= htmlspecialchars(json_encode($rsl), ENT_QUOTES, 'UTF-8'); ?>)'>
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                        <button type="button" class="btn-action-icon del" title="Eliminar" onclick="confirmarEliminarResultado('<?= $rsl['idres']; ?>', '<?= htmlspecialchars(addslashes(preg_replace('/[\r\n]+/', ' ', substr($rsl['nomres'] ?? '', 0, 80)))); ?>')">
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
<!-- MODALES DEL MÓDULO DE RESULTADOS                           -->
<!-- ========================================================== -->
<!-- Modal 1: Crear / Editar Resultado -->
<div class="modal fade" id="modalResultadoRAP" tabindex="-1" aria-labelledby="modalResultadoRAPLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form action="home.php?pg=<?= $pg; ?>" method="POST" id="formResultadoRAP">
                <div class="modal-header bg-light border-0 py-3">
                    <h5 class="modal-title fw-bold text-dark" id="modalResultadoRAPLabel">
                        <i class="fa-solid fa-list-check text-success me-2"></i><span id="lblTituloModalRAP">Registrar Nuevo Resultado</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" name="ope" value="save">
                    <input type="hidden" name="ope2" id="rap_ope2" value="">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="rap_idres" class="form-label small fw-bold">Código del Resultado <span class="text-danger">*</span></label>
                            <input type="number" name="idres" id="rap_idres" class="form-control" placeholder="Ej: 590801" required>
                        </div>
                        <div class="col-md-6">
                            <label for="rap_ndeses" class="form-label small fw-bold">Número de Sesiones Formativas <span class="text-danger">*</span></label>
                            <input type="number" name="ndeses" id="rap_ndeses" class="form-control" min="1" max="999" value="1" required>
                        </div>
                        <div class="col-md-12">
                            <label for="rap_idcom" class="form-label small fw-bold">Competencia Curricular Padre <span class="text-danger">*</span></label>
                            <select name="idcom" id="rap_idcom" class="form-select" required>
                                <option value="">-- Selecciona la competencia asociada --</option>
                                <?php if (!empty($dtComp)) { foreach ($dtComp as $c) { ?>
                                    <option value="<?= $c['idcom']; ?>">
                                        [<?= $c['idcom']; ?>] <?= htmlspecialchars(substr($c['descom'], 0, 75)); ?>... (<?= $c['nomval']; ?> - <?= $c['horcom']; ?>h)
                                    </option>
                                <?php }} ?>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label for="rap_nomres" class="form-label small fw-bold">Enunciado / Redacción del Logro <span class="text-danger">*</span></label>
                            <textarea name="nomres" id="rap_nomres" class="form-control" rows="4" placeholder="Redacta el logro de aprendizaje que alcanzará el aprendiz..." required></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0 py-3">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-sena-green">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Guardar Resultado
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal 2: Detalle Pedagógico Completo -->
<div class="modal fade" id="modalDetalleRAP" tabindex="-1" aria-labelledby="modalDetalleRAPLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 bg-light py-3">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2.5 py-1 font-monospace" id="detRapBadge">
                        RAP
                    </span>
                    <h5 class="modal-title fw-bold text-dark mb-0" id="modalDetalleRAPLabel">Detalle Curricular del Resultado</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="text-muted text-uppercase small fw-bold mb-1">Enunciado Completo del Logro</label>
                    <div class="p-3 bg-light rounded-3 border fw-semibold text-dark" id="detRapTexto" style="line-height: 1.5; font-size: 0.95rem;">
                        -
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-7">
                        <div class="p-3 bg-white rounded-3 border h-100">
                            <label class="text-muted text-uppercase small fw-bold mb-1 d-block">Competencia Asociada</label>
                            <div class="fw-bold text-dark" id="detRapCompetencia">-</div>
                            <div class="small text-muted mt-1" id="detRapCompExtra">-</div>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="p-3 bg-white rounded-3 border h-100">
                            <label class="text-muted text-uppercase small fw-bold mb-1 d-block">Intensidad</label>
                            <div class="fs-5 fw-bold text-dark" id="detRapSesiones">-</div>
                            <div class="small text-muted mt-1">Sesiones formativas pedagógicas</div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="p-3 bg-white rounded-3 border">
                            <label class="text-muted text-uppercase small fw-bold mb-1 d-block">Programa(s) y Estado</label>
                            <div class="fw-semibold text-dark" id="detRapProgramas">-</div>
                            <div class="mt-2" id="detRapEstado">-</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light border-0 py-3">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================== -->
<!-- SCRIPTS DINÁMICOS, PAGINADO Y CONTROL DE TARJETAS          -->
<!-- ========================================================== -->
<script>
let estadoFiltroRslActual = 'todos';
let paginaActualCardsRsl = 1;
let itemsPorPaginaCardsRsl = 12;
let tablaRslDT = null;

function initTablaRsl() {
    if (!tablaRslDT && $('#tablaResultados').length) {
        tablaRslDT = $('#tablaResultados').DataTable({
            language: {
                processing: "Procesando...",
                search: "_INPUT_",
                searchPlaceholder: "Buscar resultados...",
                lengthMenu: "Mostrar _MENU_ registros",
                info: "Mostrando _START_ a _END_ de _TOTAL_ resultados",
                infoEmpty: "Mostrando 0 resultados",
                infoFiltered: "(filtrado de _MAX_ totales)",
                zeroRecords: "No se encontraron resultados con los filtros aplicados",
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
    }
}

$(document).ready(function() {
    // 2. Alternar entre Vista Tarjetas y Vista Tabla
    $('#btnVistaCardsRsl').on('click', function() {
        $(this).addClass('active');
        $('#btnVistaTablaRsl').removeClass('active');
        $('#contenedorCardsResultadosWrapper').show();
        $('#contenedorTablaResultados').hide();
    });

    $('#btnVistaTablaRsl').on('click', function() {
        $(this).addClass('active');
        $('#btnVistaCardsRsl').removeClass('active');
        $('#contenedorCardsResultadosWrapper').hide();
        $('#contenedorTablaResultados').show();
        initTablaRsl();
        if (tablaRslDT) {
            tablaRslDT.columns.adjust().responsive.recalc();
        }
    });

    // 3. Buscador en Vivo
    $('#customSearchRsl').on('input keyup', function() {
        paginaActualCardsRsl = 1;
        ejecutarFiltroCardsResultados();
        if (tablaRslDT) {
            tablaRslDT.search(this.value).draw();
        }
    });

    // 4. Filtros desplegables
    $('#filtroTipoRsl, #filtroProgRsl').on('change', function() {
        paginaActualCardsRsl = 1;
        ejecutarFiltroCardsResultados();
        if (tablaRslDT) {
            const tipo = $('#filtroTipoRsl').val();
            tablaRslDT.column(3).search(tipo ? tipo : '', true, false);
            tablaRslDT.draw();
        }
    });

    // Inicializar paginación de tarjetas
    ejecutarFiltroCardsResultados();

    // 5. Cargar edición por GET si aplica
    <?php if (!empty($datOne) && !empty($datOne[0]['idres'])) { ?>
        abrirModalEditarResultado(<?= json_encode($datOne[0]); ?>);
    <?php } ?>

    // 6. Notificaciones SweetAlert2
    const urlParams = new URLSearchParams(window.location.search);
    const msg = urlParams.get('msg');
    if (msg) {
        let titulo = '¡Éxito!';
        let texto = 'Operación realizada correctamente.';
        let icono = 'success';

        switch (msg) {
            case 'guardado': texto = 'Resultado de aprendizaje registrado exitosamente.'; break;
            case 'editado': texto = 'Resultado de aprendizaje actualizado correctamente.'; break;
            case 'eliminado': texto = 'Resultado de aprendizaje eliminado.'; break;
            case 'duplicado': titulo = 'Código Duplicado'; texto = 'El código ya existe para otro resultado.'; icono = 'error'; break;
            case 'incompleto': titulo = 'Incompleto'; texto = 'Por favor completa todos los campos requeridos.'; icono = 'warning'; break;
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

function filtrarCardsRslPorEstado(estado) {
    estadoFiltroRslActual = estado;
    paginaActualCardsRsl = 1;

    $('.pill-filter').removeClass('active');
    $('.kpi-card').removeClass('active');

    if (estado === 'activo') {
        $('#pillRslActivos').addClass('active');
        $('#kpiRslActivos').addClass('active');
        if (tablaRslDT) { tablaRslDT.column(5).search('Activo').draw(); }
    } else if (estado === 'historico') {
        $('#pillRslHistoricos').addClass('active');
        $('#kpiRslHistoricos').addClass('active');
        if (tablaRslDT) { tablaRslDT.column(5).search('Histórico', true, false).draw(); }
    } else if (estado === 'huerfano') {
        $('#pillRslHuerfanos').addClass('active');
        $('#kpiRslHuerfanos').addClass('active');
        if (tablaRslDT) { tablaRslDT.column(5).search('Sin Programa', true, false).draw(); }
    } else {
        $('#pillRslTodos').addClass('active');
        $('#kpiRslTotal').addClass('active');
        if (tablaRslDT) { tablaRslDT.column(5).search('').draw(); }
    }

    ejecutarFiltroCardsResultados();
}

function cambiarItemsPorPaginaCardsRsl(val) {
    itemsPorPaginaCardsRsl = (val === 'todos') ? 99999 : parseInt(val);
    paginaActualCardsRsl = 1;
    ejecutarFiltroCardsResultados();
}

function ejecutarFiltroCardsResultados() {
    const busqueda = $('#customSearchRsl').val().toLowerCase().trim();
    const tipoSel = $('#filtroTipoRsl').val().toLowerCase().trim();
    const progSel = $('#filtroProgRsl').val().toLowerCase().trim();

    const coincidentes = [];
    $('.item-card-rsl').each(function() {
        const card = $(this);
        const cardEstado = card.data('estado');
        const cardTipo = String(card.data('tipo') || '');
        const cardProg = String(card.data('prog') || '');
        const cardSearchText = String(card.data('search') || '');

        const coincideEstado = (estadoFiltroRslActual === 'todos') || (cardEstado === estadoFiltroRslActual);
        const coincideTipo = (tipoSel === '') || (cardTipo.indexOf(tipoSel) > -1);
        const coincideProg = (progSel === '') || (cardProg.indexOf(progSel) > -1);
        const coincideBusqueda = (busqueda === '') || (cardSearchText.indexOf(busqueda) > -1);

        if (coincideEstado && coincideTipo && coincideProg && coincideBusqueda) {
            coincidentes.push(card);
        } else {
            card.hide();
        }
    });

    const totalCoincidentes = coincidentes.length;

    if (totalCoincidentes === 0) {
        $('#emptyStateCardsRsl').show();
        $('#contenedorCardsResultados').hide();
        $('#barraPaginacionCardsRsl').hide();
        return;
    }

    $('#emptyStateCardsRsl').hide();
    if (!$('#btnVistaTablaRsl').hasClass('active')) {
        $('#contenedorCardsResultados').show();
    }
    $('#barraPaginacionCardsRsl').show();

    const totalPaginas = Math.ceil(totalCoincidentes / itemsPorPaginaCardsRsl) || 1;
    if (paginaActualCardsRsl > totalPaginas) paginaActualCardsRsl = totalPaginas;
    if (paginaActualCardsRsl < 1) paginaActualCardsRsl = 1;

    const startIdx = (paginaActualCardsRsl - 1) * itemsPorPaginaCardsRsl;
    const endIdx = Math.min(startIdx + itemsPorPaginaCardsRsl, totalCoincidentes);

    coincidentes.forEach((card, index) => {
        if (index >= startIdx && index < endIdx) {
            card.show();
        } else {
            card.hide();
        }
    });

    $('#pagCardStartRsl').text(totalCoincidentes > 0 ? (startIdx + 1) : 0);
    $('#pagCardEndRsl').text(endIdx);
    $('#pagCardTotalRsl').text(totalCoincidentes);

    renderizarPaginadorCardsRsl(totalPaginas);
}

function irAPaginaCardsRsl(pag) {
    paginaActualCardsRsl = pag;
    ejecutarFiltroCardsResultados();
    $('html, body').animate({ scrollTop: $('#contenedorCardsResultadosWrapper').offset().top - 80 }, 200);
}

function renderizarPaginadorCardsRsl(totalPaginas) {
    const ul = $('#paginadorCardsUlRsl');
    ul.empty();

    if (totalPaginas <= 1) return;

    const prevDisabled = (paginaActualCardsRsl === 1) ? 'disabled' : '';
    ul.append(`<li class="page-item ${prevDisabled}">
        <a class="page-link" href="javascript:void(0)" onclick="irAPaginaCardsRsl(${paginaActualCardsRsl - 1})">
            &laquo; Anterior
        </a>
    </li>`);

    let startPage = Math.max(1, paginaActualCardsRsl - 2);
    let endPage = Math.min(totalPaginas, startPage + 4);
    if (endPage - startPage < 4) {
        startPage = Math.max(1, endPage - 4);
    }

    if (startPage > 1) {
        ul.append(`<li class="page-item"><a class="page-link" href="javascript:void(0)" onclick="irAPaginaCardsRsl(1)">1</a></li>`);
        if (startPage > 2) {
            ul.append(`<li class="page-item disabled"><span class="page-link">...</span></li>`);
        }
    }

    for (let i = startPage; i <= endPage; i++) {
        const activeClass = (i === paginaActualCardsRsl) ? 'active bg-success border-success text-white' : '';
        ul.append(`<li class="page-item ${i === paginaActualCardsRsl ? 'active' : ''}">
            <a class="page-link ${activeClass}" href="javascript:void(0)" onclick="irAPaginaCardsRsl(${i})">${i}</a>
        </li>`);
    }

    if (endPage < totalPaginas) {
        if (endPage < totalPaginas - 1) {
            ul.append(`<li class="page-item disabled"><span class="page-link">...</span></li>`);
        }
        ul.append(`<li class="page-item"><a class="page-link" href="javascript:void(0)" onclick="irAPaginaCardsRsl(${totalPaginas})">${totalPaginas}</a></li>`);
    }

    const nextDisabled = (paginaActualCardsRsl === totalPaginas) ? 'disabled' : '';
    ul.append(`<li class="page-item ${nextDisabled}">
        <a class="page-link" href="javascript:void(0)" onclick="irAPaginaCardsRsl(${paginaActualCardsRsl + 1})">
            Siguiente &raquo;
        </a>
    </li>`);
}

function resetearFiltrosCardsRsl() {
    $('#customSearchRsl').val('');
    $('#filtroTipoRsl').val('');
    $('#filtroProgRsl').val('');
    filtrarCardsRslPorEstado('todos');
}

function abrirModalResultadoNuevo() {
    $('#formResultadoRAP')[0].reset();
    $('#lblTituloModalRAP').text('Registrar Nuevo Resultado de Aprendizaje');
    $('#rap_ope2').val('');
    $('#rap_idres').prop('readonly', false);
    const modal = new bootstrap.Modal(document.getElementById('modalResultadoRAP'));
    modal.show();
}

function abrirModalEditarResultado(rsl) {
    $('#formResultadoRAP')[0].reset();
    $('#lblTituloModalRAP').text('Editar Resultado de Aprendizaje');
    $('#rap_ope2').val('edit');
    $('#rap_idres').val(rsl.idres).prop('readonly', true);
    $('#rap_nomres').val(rsl.nomres);
    $('#rap_idcom').val(rsl.idcom);
    $('#rap_ndeses').val(rsl.ndeses);
    const modal = new bootstrap.Modal(document.getElementById('modalResultadoRAP'));
    modal.show();
}

function verDetalleCompletoRAP(rsl) {
    $('#detRapBadge').text('CÓD: ' + rsl.idres);
    $('#detRapTexto').text(rsl.nomres || 'Sin descripción');
    $('#detRapCompetencia').text('[' + rsl.idcom + '] ' + (rsl.descom || 'Competencia'));
    $('#detRapCompExtra').text('Norma ' + (rsl.vercom || 'V1') + ' • Tipología: ' + (rsl.nomval || 'N/A') + ' • Horas: ' + (rsl.horcom || 0) + ' hrs');
    $('#detRapSesiones').text((rsl.ndeses || 1) + ' sesiones');

    const esHuerfano = !rsl.cant_progs || rsl.cant_progs === 0 || !rsl.codpro;
    const esActivo = (rsl.fic_vigentes && rsl.fic_vigentes > 0);

    if (esHuerfano) {
        $('#detRapProgramas').html('<span class="text-warning-emphasis"><i class="fa-solid fa-triangle-exclamation me-1"></i> No está vinculado a ningún programa formal aún.</span>');
        $('#detRapEstado').html('<span class="badge-status-orphan"><i class="fa-solid fa-triangle-exclamation"></i> Sin Programa (Huérfano)</span>');
    } else {
        $('#detRapProgramas').html('<span><i class="fa-solid fa-graduation-cap text-success me-1"></i> ' + (rsl.nompro || 'Programa no especificado') + '</span>');
        if (esActivo) {
            $('#detRapEstado').html('<span class="badge-status-active"><i class="fa-solid fa-circle-check"></i> Activo en formación (' + rsl.fic_vigentes + ' fichas)</span>');
        } else {
            $('#detRapEstado').html('<span class="badge-status-inactive"><i class="fa-solid fa-clock-rotate-left"></i> Catálogo Histórico</span>');
        }
    }

    const modal = new bootstrap.Modal(document.getElementById('modalDetalleRAP'));
    modal.show();
}

function confirmarEliminarResultado(idres, nomres) {
    Swal.fire({
        title: '¿Eliminar Resultado?',
        html: `¿Estás seguro de eliminar el resultado <strong>"${nomres}"</strong> (${idres})?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `home.php?pg=<?= $pg; ?>&idres=${idres}&ope=del`;
        }
    });
}
</script>
