<?php 
require_once 'controllers/horcpgcm.php'; 

// Renderizar únicamente el título oficial de SAGISENA encima del dashboard
echo titulo2("<i class='".($icono ?? 'fa-solid fa-network-wired')."'></i> Programa por Competencia", 0);
?>

<!-- Estilos optimizados y consistentes del módulo Programa por Competencia -->
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

    .prxcm-dashboard {
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

    /* GRID DE TARJETAS INTELIGENTES DE MALLA */
    .cards-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 18px;
        margin-bottom: 24px;
    }

    .prxcm-card {
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

    .prxcm-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 24px rgba(0, 0, 0, 0.08);
        border-color: #cbd5e1;
    }

    .prxcm-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 4px;
        background: #cbd5e1;
    }

    .prxcm-card.is-active::before {
        background: var(--sena-green);
    }

    .prxcm-card-header {
        padding: 16px 18px 12px 18px;
        border-bottom: 1px solid #f1f5f9;
    }

    .prxcm-card-body {
        padding: 14px 18px;
        flex-grow: 1;
    }

    .prxcm-card-footer {
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

<div class="prxcm-dashboard">

    <!-- 1. DASHBOARD KPI CARDS -->
    <div class="kpi-grid">
        <div class="kpi-card border-green active" onclick="filtrarCardsPrxcmPorEstado('todos')" id="kpiPrxcmTotal" title="Ver todas las vinculaciones">
            <div class="kpi-info">
                <div class="kpi-num"><?= $resumen['total_vinculaciones'] ?? count($datAll ?? []); ?></div>
                <div class="kpi-title">Mallas Vinculadas</div>
                <div class="kpi-sub">Total en catálogo SENA</div>
            </div>
            <div class="kpi-icon-wrap icon-green">
                <i class="fa-solid fa-network-wired"></i>
            </div>
        </div>

        <div class="kpi-card border-green" onclick="filtrarCardsPrxcmPorEstado('activo')" id="kpiPrxcmActivas" title="Mallas con fichas activas">
            <div class="kpi-info">
                <div class="kpi-num text-success"><?= $resumen['vinculaciones_activas'] ?? 0; ?></div>
                <div class="kpi-title">En Formación Activa</div>
                <div class="kpi-sub">Con fichas en sede</div>
            </div>
            <div class="kpi-icon-wrap icon-green">
                <i class="fa-solid fa-chalkboard-user"></i>
            </div>
        </div>

        <div class="kpi-card border-dark" onclick="filtrarCardsPrxcmPorEstado('inactivo')" id="kpiPrxcmInactivas" title="Mallas históricas">
            <div class="kpi-info">
                <div class="kpi-num text-secondary"><?= $resumen['vinculaciones_inactivas'] ?? 0; ?></div>
                <div class="kpi-title">Históricas / Reserva</div>
                <div class="kpi-sub">Sin fichas en este momento</div>
            </div>
            <div class="kpi-icon-wrap icon-dark">
                <i class="fa-solid fa-box-archive"></i>
            </div>
        </div>

        <div class="kpi-card border-orange" title="Programas con al menos una competencia vinculada">
            <div class="kpi-info">
                <div class="kpi-num text-dark"><?= $resumen['programas_con_malla'] ?? 0; ?></div>
                <div class="kpi-title">Programas con Malla</div>
                <div class="kpi-sub">Estructuras activas</div>
            </div>
            <div class="kpi-icon-wrap icon-orange">
                <i class="fa-solid fa-graduation-cap text-warning"></i>
            </div>
        </div>

        <div class="kpi-card border-purple" title="Competencias vinculadas a algún programa">
            <div class="kpi-info">
                <div class="kpi-num text-dark"><?= $resumen['competencias_en_malla'] ?? 0; ?></div>
                <div class="kpi-title">Competencias Mapeadas</div>
                <div class="kpi-sub">Articuladas a programas</div>
            </div>
            <div class="kpi-icon-wrap icon-purple">
                <i class="fa-solid fa-folder-open"></i>
            </div>
        </div>
    </div>

    <!-- ENLACES DIRECTOS A LAS OTRAS VISTAS (DEBAJO DEL DASHBOARD) -->
    <div class="nav-curricular-pills">
        <a href="home.php?pg=1527" class="btn-nav-pill">
            <i class="fa-solid fa-graduation-cap"></i> Programas de Formación
        </a>
        <a href="home.php?pg=1510<?= (!empty($codpro) && $codpro != 'todos') ? '&codpro='.$codpro : ''; ?>" class="btn-nav-pill">
            <i class="fa-solid fa-folder-open"></i> Ver Competencias
        </a>
        <a href="home.php?pg=1529<?= (!empty($codpro) && $codpro != 'todos') ? '&codpro='.$codpro : ''; ?>" class="btn-nav-pill active">
            <i class="fa-solid fa-network-wired"></i> Programa por Competencia
        </a>
        <a href="home.php?pg=1528" class="btn-nav-pill">
            <i class="fa-solid fa-list-check"></i> Resultados de Aprendizaje
        </a>
    </div>

    <!-- 2. BARRA DE CONTROL INTELIGENTE (Píldoras + Buscador + Switcher + Vincular) -->
    <div class="smart-toolbar">
        <div class="d-flex align-items-center gap-1.5 flex-wrap">
            <span class="small text-muted fw-bold text-uppercase me-1" style="font-size: 0.72rem;">Filtrar:</span>
            <button type="button" class="pill-filter active" id="pillPrxcmTodos" onclick="filtrarCardsPrxcmPorEstado('todos')">
                <i class="fa-solid fa-layer-group"></i> Todas <span class="count-chip"><?= count($datAll ?? []); ?></span>
            </button>
            <button type="button" class="pill-filter" id="pillPrxcmActivas" onclick="filtrarCardsPrxcmPorEstado('activo')">
                <i class="fa-solid fa-circle-check text-success"></i> En Formación Activa <span class="count-chip"><?= $resumen['vinculaciones_activas'] ?? 0; ?></span>
            </button>
            <button type="button" class="pill-filter" id="pillPrxcmInactivas" onclick="filtrarCardsPrxcmPorEstado('inactivo')">
                <i class="fa-solid fa-clock-rotate-left text-muted"></i> Históricas <span class="count-chip"><?= $resumen['vinculaciones_inactivas'] ?? 0; ?></span>
            </button>

            <!-- Filtro por Programa -->
            <select id="filtroProgPrxcm" class="form-select form-select-sm ms-2" style="width: auto; max-width: 220px;">
                <option value="">Todos los Programas</option>
                <?php if (!empty($dtProg)) { foreach ($dtProg as $prg) { ?>
                    <option value="<?= htmlspecialchars($prg['nompro']); ?>"><?= htmlspecialchars($prg['nompro']); ?></option>
                <?php }} ?>
            </select>

            <!-- Filtro por Tipología -->
            <select id="filtroTipoPrxcm" class="form-select form-select-sm" style="width: auto;">
                <option value="">Todas las Tipologías</option>
                <option value="técnica">Técnica</option>
                <option value="transversal">Transversal</option>
                <option value="básica">Básica / Clave</option>
            </select>
        </div>

        <div class="d-flex align-items-center gap-2 flex-wrap">
            <div class="input-group input-group-sm" style="min-width: 240px;">
                <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                <input type="text" id="customSearchPrxcm" class="form-control border-start-0" placeholder="Buscar programa o competencia...">
            </div>

            <!-- Selector de Vista (Tarjetas por defecto) -->
            <div class="btn-group btn-group-sm" role="group">
                <button type="button" class="btn btn-outline-secondary active" id="btnVistaCardsPrxcm" title="Vista Cuadrícula de Tarjetas">
                    <i class="fa-solid fa-grip me-1"></i> Tarjetas
                </button>
                <button type="button" class="btn btn-outline-secondary" id="btnVistaTablaPrxcm" title="Vista Tabla">
                    <i class="fa-solid fa-table-list me-1"></i> Tabla
                </button>
            </div>

            <button type="button" class="btn btn-sena-green" onclick="abrirModalVincularPrxcm()">
                <i class="fa-solid fa-link me-1"></i> Vincular a Malla
            </button>
        </div>
    </div>

    <!-- 3. CONTENEDOR 1: GRID DE TARJETAS INTELIGENTES DE MALLA -->
    <div id="contenedorCardsPrxcmWrapper">
        <div class="cards-grid" id="contenedorCardsPrxcm">
            <?php if (!empty($datAll)) {
                foreach ($datAll as $px) { 
                    $ficVigentes = (int)($px['fic_vigentes'] ?? 0);
                    $esActiva = ($ficVigentes > 0);
                    $estadoStr = $esActiva ? 'activo' : 'inactivo';
                    $tipoNormalizado = strtolower($px['nomval'] ?? '');
                ?>
                    <div class="prxcm-card item-card-prxcm <?= $esActiva ? 'is-active' : ''; ?>"
                         data-estado="<?= $estadoStr; ?>"
                         data-prog="<?= strtolower($px['nompro']); ?>"
                         data-tipo="<?= $tipoNormalizado; ?>"
                         data-search="<?= strtolower($px['codpro'] . ' ' . $px['nompro'] . ' ' . $px['idcom'] . ' ' . $px['descom'] . ' ' . ($px['nomval'] ?? '')); ?>">

                        <div class="prxcm-card-header">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="badge bg-dark bg-opacity-10 text-dark border border-dark border-opacity-25 px-2 py-1 font-monospace">
                                    <i class="fa-solid fa-graduation-cap me-1 text-success"></i><?= htmlspecialchars($px['codpro']); ?>
                                </span>

                                <?php if ($esActiva) { ?>
                                    <span class="badge-status-active" title="Fichas activas en este programa cursando esta competencia">
                                        <i class="fa-solid fa-circle-check"></i> Activa (<?= $ficVigentes; ?>)
                                    </span>
                                <?php } else { ?>
                                    <span class="badge-status-inactive" title="Sin fichas activas en este momento">
                                        <i class="fa-solid fa-clock-rotate-left"></i> Histórico
                                    </span>
                                <?php } ?>
                            </div>

                            <div class="fw-bold text-dark text-truncate mt-1" style="font-size: 0.88rem;" title="<?= htmlspecialchars($px['nompro']); ?>">
                                <?= htmlspecialchars($px['nompro']); ?>
                            </div>
                        </div>

                        <div class="prxcm-card-body">
                            <div class="d-flex align-items-center gap-1.5 mb-2">
                                <span class="badge bg-light text-dark border px-2 py-0.5 small font-monospace">
                                    [<?= htmlspecialchars($px['idcom']); ?>]
                                </span>
                                <span class="badge bg-light text-secondary border px-2 py-0.5 small">
                                    <?= htmlspecialchars($px['nomval'] ?? 'Competencia'); ?>
                                </span>
                                <span class="badge bg-light text-muted border px-2 py-0.5 small font-monospace">
                                    <?= htmlspecialchars($px['horcom'] ?? 0); ?> hrs
                                </span>
                            </div>

                            <div class="text-secondary small fw-medium" style="line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; min-height: 3.6rem;" title="<?= htmlspecialchars($px['descom']); ?>">
                                <?= htmlspecialchars($px['descom']); ?>
                            </div>
                        </div>

                        <div class="prxcm-card-footer">
                            <a href="home.php?pg=1510&codpro=<?= $px['codpro']; ?>" class="btn-action-primary" title="Ver Malla de Competencias de este programa">
                                <i class="fa-solid fa-folder-open"></i> Ver competencias
                            </a>

                            <button type="button" class="btn-action-icon del" title="Desvincular de la Malla"
                                    onclick="confirmarDesvincularMalla('<?= $px['codpro']; ?>', '<?= $px['idcom']; ?>', '<?= htmlspecialchars(addslashes(preg_replace('/[\r\n]+/', ' ', substr($px['descom'] ?? '', 0, 70)))); ?>', '<?= htmlspecialchars(addslashes(preg_replace('/[\r\n]+/', ' ', substr($px['nompro'] ?? '', 0, 70)))); ?>')">
                                <i class="fa-solid fa-unlink"></i>
                            </button>
                        </div>
                    </div>
                <?php } 
            } ?>
        </div>

        <!-- PAGINADO E INFORMACIÓN DE LA PÁGINA (TARJETAS INTELIGENTES) -->
        <div class="cards-pagination-bar d-flex flex-wrap justify-content-between align-items-center gap-2" id="barraPaginacionCardsPrxcm">
            <div class="small text-muted" id="infoPaginacionCardsPrxcm">
                Mostrando <strong id="pagCardStartPrxcm">1</strong> a <strong id="pagCardEndPrxcm">12</strong> de <strong id="pagCardTotalPrxcm"><?= count($datAll ?? []); ?></strong> vinculaciones
            </div>
            <div class="d-flex align-items-center gap-2">
                <select id="selItemsPorPaginaCardsPrxcm" class="form-select form-select-sm" style="width: auto;" onchange="cambiarItemsPorPaginaCardsPrxcm(this.value)">
                    <option value="9">9 por página</option>
                    <option value="12" selected>12 por página</option>
                    <option value="24">24 por página</option>
                    <option value="todos">Todas</option>
                </select>
                <nav aria-label="Navegación de tarjetas">
                    <ul class="pagination pagination-sm mb-0" id="paginadorCardsUlPrxcm">
                        <!-- Botones de paginación generados por JS -->
                    </ul>
                </nav>
            </div>
        </div>

        <!-- ESTADO VACÍO EN CASO DE FILTRADO SIN COINCIDENCIAS -->
        <div id="emptyStateCardsPrxcm" class="card shadow-sm border-0 rounded-4 text-center p-5 my-4" style="display: none;">
            <div class="p-3 rounded-circle bg-light d-inline-flex mx-auto mb-3 text-muted fs-2" style="width: 64px; height: 64px; align-items: center; justify-content: center;">
                <i class="fa-solid fa-network-wired"></i>
            </div>
            <h5 class="fw-bold text-dark">No se encontraron vinculaciones coincidentes</h5>
            <p class="text-muted small">Intenta cambiar los términos de búsqueda o los filtros de programa y tipología.</p>
            <div>
                <button type="button" class="btn btn-outline-success btn-sm px-3" onclick="resetearFiltrosCardsPrxcm()">
                    <i class="fa-solid fa-arrow-rotate-left me-1"></i> Restablecer filtros
                </button>
            </div>
        </div>
    </div>

    <!-- 4. CONTENEDOR 2: VISTA TABLA (OPCIÓN ALTERNATIVA) -->
    <div class="card shadow-sm border-0 rounded-4 overflow-hidden" id="contenedorTablaPrxcm" style="display: none;">
        <div class="table-responsive p-3">
            <table id="tablaPrxcm" class="table table-hover align-middle mb-0" style="width:100%">
                <thead class="table-light">
                    <tr>
                        <th style="width: 250px;">Programa de Formación</th>
                        <th>Competencia Curricular</th>
                        <th style="width: 110px;">Tipología</th>
                        <th class="text-center" style="width: 85px;">Horas</th>
                        <th class="text-center" style="width: 120px;">Estado Sede</th>
                        <th class="text-end" style="width: 110px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($datAll)) {
                        foreach ($datAll as $px) { 
                            $ficVigentes = (int)($px['fic_vigentes'] ?? 0);
                            $esActiva = ($ficVigentes > 0);
                        ?>
                            <tr>
                                <td>
                                    <div class="fw-bold text-dark">[<?= htmlspecialchars($px['codpro']); ?>]</div>
                                    <div class="small text-muted text-truncate" style="max-width: 240px;"><?= htmlspecialchars($px['nompro']); ?></div>
                                </td>
                                <td>
                                    <div class="small fw-semibold text-dark">[<?= htmlspecialchars($px['idcom']); ?>] <?= htmlspecialchars($px['descom']); ?></div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border"><?= htmlspecialchars($px['nomval'] ?? 'Competencia'); ?></span>
                                </td>
                                <td class="text-center fw-bold text-dark"><?= $px['horcom']; ?>h</td>
                                <td class="text-center">
                                    <?php if ($esActiva) { ?>
                                        <span class="badge-status-active"><i class="fa-solid fa-circle-check"></i> Activo (<?= $ficVigentes; ?>)</span>
                                    <?php } else { ?>
                                        <span class="badge-status-inactive"><i class="fa-solid fa-clock-rotate-left"></i> Histórico</span>
                                    <?php } ?>
                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-1">
                                        <a href="home.php?pg=1510&codpro=<?= $px['codpro']; ?>" class="btn-action-icon" title="Ver en Matriz">
                                            <i class="fa-solid fa-folder-open text-success"></i>
                                        </a>
                                        <button type="button" class="btn-action-icon del" title="Desvincular"
                                                onclick="confirmarDesvincularMalla('<?= $px['codpro']; ?>', '<?= $px['idcom']; ?>', '<?= htmlspecialchars(addslashes(preg_replace('/[\r\n]+/', ' ', substr($px['descom'] ?? '', 0, 70)))); ?>', '<?= htmlspecialchars(addslashes(preg_replace('/[\r\n]+/', ' ', substr($px['nompro'] ?? '', 0, 70)))); ?>')">
                                            <i class="fa-solid fa-unlink"></i>
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
<!-- MODAL VINCULAR COMPETENCIA A PROGRAMA                      -->
<!-- ========================================================== -->
<div class="modal fade" id="modalVincularPrxcm" tabindex="-1" aria-labelledby="modalVincularPrxcmLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form action="home.php?pg=<?= $pg; ?>" method="POST" id="formVincularPrxcm">
                <div class="modal-header bg-light border-0 py-3">
                    <h5 class="modal-title fw-bold text-dark" id="modalVincularPrxcmLabel">
                        <i class="fa-solid fa-link text-success me-2"></i>Asignar Competencia a Malla
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" name="ope" value="save">

                    <div class="mb-3">
                        <label for="vinc_codpro" class="form-label small fw-bold">1. Programa de Formación Destino <span class="text-danger">*</span></label>
                        <select name="codpro" id="vinc_codpro" class="form-select" required>
                            <option value="">-- Selecciona el Programa --</option>
                            <?php if (!empty($dtProg)) { foreach ($dtProg as $p) { ?>
                                <option value="<?= $p['codpro']; ?>">
                                    [<?= $p['codpro']; ?>] <?= htmlspecialchars($p['nompro']); ?> (v<?= $p['verpro']; ?>)
                                </option>
                            <?php }} ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="vinc_idcom" class="form-label small fw-bold">2. Competencia Curricular <span class="text-danger">*</span></label>
                        <select name="idcom" id="vinc_idcom" class="form-select" required>
                            <option value="">-- Selecciona la Competencia --</option>
                            <?php if (!empty($dtComp)) { foreach ($dtComp as $c) { ?>
                                <option value="<?= $c['idcom']; ?>">
                                    [<?= $c['idcom']; ?>] <?= htmlspecialchars(substr($c['descom'], 0, 70)); ?>... (<?= $c['nomval']; ?> - <?= $c['horcom']; ?>h)
                                </option>
                            <?php }} ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0 py-3">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-sena-green">
                        <i class="fa-solid fa-check me-1"></i> Vincular a Malla
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================================== -->
<!-- SCRIPTS DINÁMICOS, PAGINADO Y CONTROL DE TARJETAS          -->
<!-- ========================================================== -->
<script>
let estadoFiltroPrxcmActual = 'todos';
let paginaActualCardsPrxcm = 1;
let itemsPorPaginaCardsPrxcm = 12;
let tablaPrxcmDT = null;

$(document).ready(function() {
    // 1. Inicializar DataTable para la vista alternativa de Tabla
    if ($.fn.DataTable.isDataTable('#tablaPrxcm')) {
        $('#tablaPrxcm').DataTable().destroy();
    }

    tablaPrxcmDT = $('#tablaPrxcm').DataTable({
        language: {
            processing: "Procesando...",
            search: "_INPUT_",
            searchPlaceholder: "Buscar vinculaciones...",
            lengthMenu: "Mostrar _MENU_ registros",
            info: "Mostrando _START_ a _END_ de _TOTAL_ vinculaciones",
            infoEmpty: "Mostrando 0 vinculaciones",
            infoFiltered: "(filtrado de _MAX_ totales)",
            zeroRecords: "No se encontraron vinculaciones con los filtros aplicados",
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
    $('#btnVistaCardsPrxcm').on('click', function() {
        $(this).addClass('active');
        $('#btnVistaTablaPrxcm').removeClass('active');
        $('#contenedorCardsPrxcmWrapper').show();
        $('#contenedorTablaPrxcm').hide();
    });

    $('#btnVistaTablaPrxcm').on('click', function() {
        $(this).addClass('active');
        $('#btnVistaCardsPrxcm').removeClass('active');
        $('#contenedorCardsPrxcmWrapper').hide();
        $('#contenedorTablaPrxcm').show();
        if (tablaPrxcmDT) {
            tablaPrxcmDT.columns.adjust().responsive.recalc();
        }
    });

    // 3. Buscador en Vivo
    $('#customSearchPrxcm').on('input keyup', function() {
        paginaActualCardsPrxcm = 1;
        ejecutarFiltroCardsPrxcm();
        if (tablaPrxcmDT) {
            tablaPrxcmDT.search(this.value).draw();
        }
    });

    // 4. Filtros desplegables
    $('#filtroProgPrxcm, #filtroTipoPrxcm').on('change', function() {
        paginaActualCardsPrxcm = 1;
        ejecutarFiltroCardsPrxcm();
        if (tablaPrxcmDT) {
            const prog = $('#filtroProgPrxcm').val();
            const tipo = $('#filtroTipoPrxcm').val();
            tablaPrxcmDT.column(0).search(prog ? prog : '', false, false);
            tablaPrxcmDT.column(2).search(tipo ? tipo : '', true, false);
            tablaPrxcmDT.draw();
        }
    });

    // Inicializar paginación de tarjetas
    ejecutarFiltroCardsPrxcm();

    // 5. Notificaciones SweetAlert2
    const urlParams = new URLSearchParams(window.location.search);
    const msg = urlParams.get('msg');
    if (msg) {
        let titulo = '¡Éxito!';
        let texto = 'Operación realizada correctamente.';
        let icono = 'success';

        switch (msg) {
            case 'vinculado': texto = 'Competencia asignada a la malla del programa.'; break;
            case 'desvinculado': texto = 'Competencia desvinculada exitosamente de la malla.'; break;
            case 'duplicado': titulo = 'Ya existe'; texto = 'Esta competencia ya está asignada a este programa.'; icono = 'warning'; break;
            case 'incompleto': titulo = 'Incompleto'; texto = 'Debes seleccionar el programa y la competencia.'; icono = 'warning'; break;
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

function filtrarCardsPrxcmPorEstado(estado) {
    estadoFiltroPrxcmActual = estado;
    paginaActualCardsPrxcm = 1;

    $('.pill-filter').removeClass('active');
    $('.kpi-card').removeClass('active');

    if (estado === 'activo') {
        $('#pillPrxcmActivas').addClass('active');
        $('#kpiPrxcmActivas').addClass('active');
        if (tablaPrxcmDT) { tablaPrxcmDT.column(4).search('Activo').draw(); }
    } else if (estado === 'inactivo') {
        $('#pillPrxcmInactivas').addClass('active');
        $('#kpiPrxcmInactivas').addClass('active');
        if (tablaPrxcmDT) { tablaPrxcmDT.column(4).search('Histórico', true, false).draw(); }
    } else {
        $('#pillPrxcmTodos').addClass('active');
        $('#kpiPrxcmTotal').addClass('active');
        if (tablaPrxcmDT) { tablaPrxcmDT.column(4).search('').draw(); }
    }

    ejecutarFiltroCardsPrxcm();
}

function cambiarItemsPorPaginaCardsPrxcm(val) {
    itemsPorPaginaCardsPrxcm = (val === 'todos') ? 99999 : parseInt(val);
    paginaActualCardsPrxcm = 1;
    ejecutarFiltroCardsPrxcm();
}

function ejecutarFiltroCardsPrxcm() {
    const busqueda = $('#customSearchPrxcm').val().toLowerCase().trim();
    const progSel = $('#filtroProgPrxcm').val().toLowerCase().trim();
    const tipoSel = $('#filtroTipoPrxcm').val().toLowerCase().trim();

    const coincidentes = [];
    $('.item-card-prxcm').each(function() {
        const card = $(this);
        const cardEstado = card.data('estado');
        const cardProg = String(card.data('prog') || '');
        const cardTipo = String(card.data('tipo') || '');
        const cardSearchText = String(card.data('search') || '');

        const coincideEstado = (estadoFiltroPrxcmActual === 'todos') || (cardEstado === estadoFiltroPrxcmActual);
        const coincideProg = (progSel === '') || (cardProg.indexOf(progSel) > -1);
        const coincideTipo = (tipoSel === '') || (cardTipo.indexOf(tipoSel) > -1);
        const coincideBusqueda = (busqueda === '') || (cardSearchText.indexOf(busqueda) > -1);

        if (coincideEstado && coincideProg && coincideTipo && coincideBusqueda) {
            coincidentes.push(card);
        } else {
            card.hide();
        }
    });

    const totalCoincidentes = coincidentes.length;

    if (totalCoincidentes === 0) {
        $('#emptyStateCardsPrxcm').show();
        $('#contenedorCardsPrxcm').hide();
        $('#barraPaginacionCardsPrxcm').hide();
        return;
    }

    $('#emptyStateCardsPrxcm').hide();
    if (!$('#btnVistaTablaPrxcm').hasClass('active')) {
        $('#contenedorCardsPrxcm').show();
    }
    $('#barraPaginacionCardsPrxcm').show();

    const totalPaginas = Math.ceil(totalCoincidentes / itemsPorPaginaCardsPrxcm) || 1;
    if (paginaActualCardsPrxcm > totalPaginas) paginaActualCardsPrxcm = totalPaginas;
    if (paginaActualCardsPrxcm < 1) paginaActualCardsPrxcm = 1;

    const startIdx = (paginaActualCardsPrxcm - 1) * itemsPorPaginaCardsPrxcm;
    const endIdx = Math.min(startIdx + itemsPorPaginaCardsPrxcm, totalCoincidentes);

    coincidentes.forEach((card, index) => {
        if (index >= startIdx && index < endIdx) {
            card.show();
        } else {
            card.hide();
        }
    });

    $('#pagCardStartPrxcm').text(totalCoincidentes > 0 ? (startIdx + 1) : 0);
    $('#pagCardEndPrxcm').text(endIdx);
    $('#pagCardTotalPrxcm').text(totalCoincidentes);

    renderizarPaginadorCardsPrxcm(totalPaginas);
}

function irAPaginaCardsPrxcm(pag) {
    paginaActualCardsPrxcm = pag;
    ejecutarFiltroCardsPrxcm();
    $('html, body').animate({ scrollTop: $('#contenedorCardsPrxcmWrapper').offset().top - 80 }, 200);
}

function renderizarPaginadorCardsPrxcm(totalPaginas) {
    const ul = $('#paginadorCardsUlPrxcm');
    ul.empty();

    if (totalPaginas <= 1) return;

    const prevDisabled = (paginaActualCardsPrxcm === 1) ? 'disabled' : '';
    ul.append(`<li class="page-item ${prevDisabled}">
        <a class="page-link" href="javascript:void(0)" onclick="irAPaginaCardsPrxcm(${paginaActualCardsPrxcm - 1})">
            &laquo; Anterior
        </a>
    </li>`);

    let startPage = Math.max(1, paginaActualCardsPrxcm - 2);
    let endPage = Math.min(totalPaginas, startPage + 4);
    if (endPage - startPage < 4) {
        startPage = Math.max(1, endPage - 4);
    }

    if (startPage > 1) {
        ul.append(`<li class="page-item"><a class="page-link" href="javascript:void(0)" onclick="irAPaginaCardsPrxcm(1)">1</a></li>`);
        if (startPage > 2) {
            ul.append(`<li class="page-item disabled"><span class="page-link">...</span></li>`);
        }
    }

    for (let i = startPage; i <= endPage; i++) {
        const activeClass = (i === paginaActualCardsPrxcm) ? 'active bg-success border-success text-white' : '';
        ul.append(`<li class="page-item ${i === paginaActualCardsPrxcm ? 'active' : ''}">
            <a class="page-link ${activeClass}" href="javascript:void(0)" onclick="irAPaginaCardsPrxcm(${i})">${i}</a>
        </li>`);
    }

    if (endPage < totalPaginas) {
        if (endPage < totalPaginas - 1) {
            ul.append(`<li class="page-item disabled"><span class="page-link">...</span></li>`);
        }
        ul.append(`<li class="page-item"><a class="page-link" href="javascript:void(0)" onclick="irAPaginaCardsPrxcm(${totalPaginas})">${totalPaginas}</a></li>`);
    }

    const nextDisabled = (paginaActualCardsPrxcm === totalPaginas) ? 'disabled' : '';
    ul.append(`<li class="page-item ${nextDisabled}">
        <a class="page-link" href="javascript:void(0)" onclick="irAPaginaCardsPrxcm(${paginaActualCardsPrxcm + 1})">
            Siguiente &raquo;
        </a>
    </li>`);
}

function resetearFiltrosCardsPrxcm() {
    $('#customSearchPrxcm').val('');
    $('#filtroProgPrxcm').val('');
    $('#filtroTipoPrxcm').val('');
    filtrarCardsPrxcmPorEstado('todos');
}

function abrirModalVincularPrxcm() {
    $('#formVincularPrxcm')[0].reset();
    const modal = new bootstrap.Modal(document.getElementById('modalVincularPrxcm'));
    modal.show();
}

function confirmarDesvincularMalla(codpro, idcom, descom, nompro) {
    Swal.fire({
        title: '¿Desvincular de la Malla?',
        html: `¿Estás seguro de desvincular la competencia <strong>"${descom.substring(0, 50)}..."</strong> del programa <strong>"${nompro}"</strong>?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, desvincular',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `home.php?pg=<?= $pg; ?>&codpro=${codpro}&idcom=${idcom}&ope=del`;
        }
    });
}
</script>
