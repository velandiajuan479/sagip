<?php 
require_once 'controllers/horccmp.php'; 

// Datos del programa seleccionado (si aplica)
$prog = ($dtPrg && count($dtPrg) > 0) ? $dtPrg[0] : null;

// Cálculo de porcentaje de cobertura de horas si hay programa seleccionado
$horasLectivas = ($prog && !empty($prog['horlpro'])) ? (float)$prog['horlpro'] : 0;
$horasCompetencias = (float)($resumen['tot_horas'] ?? 0);
$porcentajeHoras = ($horasLectivas > 0) ? round(($horasCompetencias / $horasLectivas) * 100, 1) : 0;
$colorProgreso = ($porcentajeHoras > 100) ? '#dc3545' : (($porcentajeHoras >= 100) ? '#39A900' : (($porcentajeHoras >= 50) ? '#2d8500' : '#ffc107'));

// Renderizar únicamente el título oficial de SAGISENA encima del dashboard
echo titulo2("<i class='".($icono ?? 'fa-solid fa-folder-open')."'></i> Ver Competencias", 0);
?>

<!-- Estilos optimizados y consistentes del módulo de Competencias -->
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

    .cmp-dashboard {
        font-family: inherit;
        color: #111827;
        padding-bottom: 40px;
    }

    /* Balance de Horas del Programa */
    .program-balance-bar {
        background: #ffffff;
        border-radius: var(--card-radius);
        padding: 14px 18px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 2px 8px rgba(0,0,0,0.03);
        margin-bottom: 18px;
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

    /* GRID DE TARJETAS INTELIGENTES DE COMPETENCIAS */
    .cards-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
        gap: 18px;
        margin-bottom: 24px;
    }

    .cmp-card {
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

    .cmp-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 24px rgba(0, 0, 0, 0.08);
        border-color: #cbd5e1;
    }

    .cmp-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 4px;
        background: #cbd5e1;
    }

    .cmp-card.is-active::before {
        background: var(--sena-green);
    }

    .cmp-card-header {
        padding: 16px 18px 12px 18px;
        border-bottom: 1px solid #f1f5f9;
    }

    .cmp-card-body {
        padding: 14px 18px;
        flex-grow: 1;
    }

    .cmp-card-title {
        font-size: 0.95rem;
        font-weight: 700;
        color: #0f172a;
        line-height: 1.45;
        margin-bottom: 8px;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
        min-height: 3.8rem;
    }

    .cmp-metrics-box {
        background: #f8fafc;
        border-radius: 10px;
        padding: 10px 12px;
        border: 1px solid #edf2f7;
        margin-top: 10px;
    }

    .cmp-card-footer {
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

<div class="cmp-dashboard">

    <!-- 1. SELECTOR DE CONTEXTO DE PROGRAMA -->
    <div class="card shadow-sm border-0 mb-3 rounded-4" style="background: #ffffff;">
        <div class="card-body p-3 d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-2 flex-grow-1" style="max-width: 620px;">
                <label for="selectorProgramaContexto" class="small fw-bold text-dark text-nowrap">
                    <i class="fa-solid fa-filter text-success me-1"></i> Ver Competencias de:
                </label>
                <select id="selectorProgramaContexto" class="form-select form-select-sm" onchange="cambiarProgramaContexto(this.value)">
                    <option value="todos" <?= ($codpro == 'todos' || empty($codpro)) ? 'selected' : ''; ?>>
                        🏛️ Catálogo General (Todas las Competencias del Centro)
                    </option>
                    <optgroup label="Programas de Formación en Sede:">
                        <?php if (!empty($dtPrgList)) { foreach ($dtPrgList as $p) { ?>
                            <option value="<?= $p['codpro']; ?>" <?= ($codpro == $p['codpro']) ? 'selected' : ''; ?>>
                                [<?= $p['codpro']; ?>] <?= htmlspecialchars($p['nompro']); ?> (v<?= $p['verpro']; ?>)
                            </option>
                        <?php }} ?>
                    </optgroup>
                </select>
            </div>

            <div class="d-flex align-items-center gap-2">
                <?php if ($prog) { ?>
                    <button type="button" class="btn btn-sm btn-outline-dark" onclick="abrirModalVincularCompetencia()">
                        <i class="fa-solid fa-link text-success me-1"></i> Asignar a Malla
                    </button>
                <?php } ?>
                <button type="button" class="btn btn-sm btn-sena-green" onclick="abrirModalCompetenciaNueva()">
                    <i class="fa-solid fa-plus me-1"></i> Nueva Competencia
                </button>
            </div>
        </div>
    </div>

    <!-- 2. BARRA DE BALANCE DE HORAS (SI HAY PROGRAMA SELECCIONADO) -->
    <?php if ($prog) { ?>
        <div class="program-balance-bar">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-2">
                <div>
                    <span class="badge bg-dark text-white px-2 py-1 font-monospace me-1"><?= $prog['codpro']; ?></span>
                    <strong class="text-dark"><?= htmlspecialchars($prog['nompro']); ?></strong>
                    <span class="text-muted small ms-2">(Lectiva: <?= number_format($horasLectivas); ?>h &bull; Práctica: <?= $prog['horppro']; ?>h)</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="small text-muted">Horas Asignadas en Competencias:</span>
                    <strong style="color: <?= $colorProgreso; ?>; font-size: 1.05rem;">
                        <?= number_format($horasCompetencias); ?> / <?= number_format($horasLectivas); ?> hrs (<?= $porcentajeHoras; ?>%)
                    </strong>
                </div>
            </div>
            <div class="progress" style="height: 8px; border-radius: 10px;">
                <div class="progress-bar" role="progressbar" style="width: <?= min(100, $porcentajeHoras); ?>%; background-color: <?= $colorProgreso; ?>;" aria-valuenow="<?= $porcentajeHoras; ?>" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
        </div>
    <?php } ?>

    <!-- 3. DASHBOARD DE INFORMACIÓN Y KPIS -->
    <div class="kpi-grid">
        <div class="kpi-card border-green active" onclick="filtrarCardsCmpPorEstado('todos')" id="kpiCmpTotal" title="Ver todas las competencias">
            <div class="kpi-info">
                <div class="kpi-num"><?= $resumen['total_competencias'] ?? count($dat ?? []); ?></div>
                <div class="kpi-title">Total Competencias</div>
                <div class="kpi-sub"><?= $prog ? 'En malla de este programa' : 'En catálogo institucional'; ?></div>
            </div>
            <div class="kpi-icon-wrap icon-green">
                <i class="fa-solid fa-folder-open"></i>
            </div>
        </div>

        <div class="kpi-card border-green" onclick="filtrarCardsCmpPorEstado('activo')" id="kpiCmpActivas" title="Competencias con fichas en formación">
            <div class="kpi-info">
                <div class="kpi-num text-success"><?= $resumen['competencias_activas'] ?? 0; ?></div>
                <div class="kpi-title">Activas en Formación</div>
                <div class="kpi-sub">Con fichas en curso</div>
            </div>
            <div class="kpi-icon-wrap icon-green">
                <i class="fa-solid fa-circle-check text-success"></i>
            </div>
        </div>

        <div class="kpi-card border-dark" onclick="filtrarCardsCmpPorEstado('inactivo')" id="kpiCmpInactivas" title="Competencias sin fichas actuales">
            <div class="kpi-info">
                <div class="kpi-num text-secondary"><?= $resumen['competencias_inactivas'] ?? 0; ?></div>
                <div class="kpi-title">Históricas / Reserva</div>
                <div class="kpi-sub">Sin fichas en este momento</div>
            </div>
            <div class="kpi-icon-wrap icon-dark">
                <i class="fa-solid fa-clock-rotate-left"></i>
            </div>
        </div>

        <div class="kpi-card border-orange" title="Suma total de horas formativas asignadas">
            <div class="kpi-info">
                <div class="kpi-num text-dark"><?= number_format($resumen['tot_horas'] ?? 0); ?> <small style="font-size: 0.95rem; color: #64748b;">hrs</small></div>
                <div class="kpi-title">Horas Formativas</div>
                <div class="kpi-sub">Promedio: <?= $resumen['promedio_horas'] ?? 0; ?> hrs / comp</div>
            </div>
            <div class="kpi-icon-wrap icon-orange">
                <i class="fa-solid fa-clock text-warning"></i>
            </div>
        </div>

        <div class="kpi-card border-purple" title="Resultados de aprendizaje articulados">
            <div class="kpi-info">
                <div class="kpi-num text-dark"><?= $resumen['tot_resultados'] ?? 0; ?></div>
                <div class="kpi-title">Resultados (RAPs)</div>
                <div class="kpi-sub">Logros pedagógicos</div>
            </div>
            <div class="kpi-icon-wrap icon-purple">
                <i class="fa-solid fa-list-check"></i>
            </div>
        </div>
    </div>

    <!-- ENLACES DIRECTOS A LAS OTRAS VISTAS (DEBAJO DEL DASHBOARD) -->
    <div class="nav-curricular-pills">
        <a href="home.php?pg=1527" class="btn-nav-pill">
            <i class="fa-solid fa-graduation-cap"></i> Programas de Formación
        </a>
        <a href="home.php?pg=1510<?= (!empty($codpro) && $codpro != 'todos') ? '&codpro='.$codpro : ''; ?>" class="btn-nav-pill active">
            <i class="fa-solid fa-folder-open"></i> Ver Competencias
        </a>
        <a href="home.php?pg=1529<?= (!empty($codpro) && $codpro != 'todos') ? '&codpro='.$codpro : ''; ?>" class="btn-nav-pill">
            <i class="fa-solid fa-network-wired"></i> Programa por Competencia
        </a>
        <a href="home.php?pg=1528" class="btn-nav-pill">
            <i class="fa-solid fa-list-check"></i> Resultados de Aprendizaje
        </a>
    </div>

    <!-- 4. BARRA DE CONTROL INTELIGENTE (Píldoras + Buscador + Switcher) -->
    <div class="smart-toolbar">
        <div class="d-flex align-items-center gap-1.5 flex-wrap">
            <span class="small text-muted fw-bold text-uppercase me-1" style="font-size: 0.72rem;">Filtrar:</span>
            <button type="button" class="pill-filter active" id="pillCmpTodos" onclick="filtrarCardsCmpPorEstado('todos')">
                <i class="fa-solid fa-layer-group"></i> Todas <span class="count-chip"><?= count($dat ?? []); ?></span>
            </button>
            <button type="button" class="pill-filter" id="pillCmpActivas" onclick="filtrarCardsCmpPorEstado('activo')">
                <i class="fa-solid fa-circle-check text-success"></i> Activas en Formación <span class="count-chip"><?= $resumen['competencias_activas'] ?? 0; ?></span>
            </button>
            <button type="button" class="pill-filter" id="pillCmpInactivas" onclick="filtrarCardsCmpPorEstado('inactivo')">
                <i class="fa-solid fa-clock-rotate-left text-muted"></i> Históricas <span class="count-chip"><?= $resumen['competencias_inactivas'] ?? 0; ?></span>
            </button>

            <!-- Filtro Tipología -->
            <select id="filtroTipoCmp" class="form-select form-select-sm ms-2" style="width: auto;">
                <option value="">Todas las Tipologías</option>
                <option value="técnica">Técnica</option>
                <option value="transversal">Transversal</option>
                <option value="básica">Básica / Clave</option>
            </select>
        </div>

        <div class="d-flex align-items-center gap-2 flex-wrap">
            <div class="input-group input-group-sm" style="min-width: 240px;">
                <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                <input type="text" id="customSearchCmp" class="form-control border-start-0" placeholder="Buscar por código, norma o nombre...">
            </div>

            <!-- Selector de Vista (Tarjetas por defecto) -->
            <div class="btn-group btn-group-sm" role="group">
                <button type="button" class="btn btn-outline-secondary active" id="btnVistaCardsCmp" title="Vista Cuadrícula de Tarjetas">
                    <i class="fa-solid fa-grip me-1"></i> Tarjetas
                </button>
                <button type="button" class="btn btn-outline-secondary" id="btnVistaTablaCmp" title="Vista Tabla">
                    <i class="fa-solid fa-table-list me-1"></i> Tabla
                </button>
            </div>
        </div>
    </div>

    <!-- 5. CONTENEDOR 1: GRID DE TARJETAS INTELIGENTES DE COMPETENCIAS -->
    <div id="contenedorCardsCompetenciasWrapper">
        <div class="cards-grid" id="contenedorCardsCompetencias">
            <?php if (!empty($dat)) {
                foreach ($dat as $c) { 
                    $ficVigentes = (int)($c['fic_activas'] ?? ($c['fic_vigentes'] ?? 0));
                    $esActiva = ($ficVigentes > 0);
                    $estadoStr = $esActiva ? 'activo' : 'inactivo';
                    $cantRes = (int)($c['cant_res'] ?? 0);
                    $cantProgs = (int)($c['cant_progs'] ?? 0);
                    $tipoNormalizado = strtolower($c['nomval'] ?? '');
                ?>
                    <div class="cmp-card item-card-cmp <?= $esActiva ? 'is-active' : ''; ?>"
                         data-estado="<?= $estadoStr; ?>"
                         data-tipo="<?= $tipoNormalizado; ?>"
                         data-search="<?= strtolower($c['idcom'] . ' ' . $c['descom'] . ' ' . ($c['nomval'] ?? '') . ' ' . ($c['vercom'] ?? '')); ?>">

                        <div class="cmp-card-header">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="badge bg-dark bg-opacity-10 text-dark border border-dark border-opacity-25 px-2 py-1 font-monospace">
                                    <i class="fa-solid fa-hashtag me-0.5 text-muted"></i><?= htmlspecialchars($c['idcom']); ?>
                                </span>

                                <?php if ($esActiva) { ?>
                                    <span class="badge-status-active" title="Hay fichas activas recibiendo esta competencia">
                                        <i class="fa-solid fa-circle-check"></i> Activa (<?= $ficVigentes; ?>)
                                    </span>
                                <?php } else { ?>
                                    <span class="badge-status-inactive" title="Sin fichas activas en este momento">
                                        <i class="fa-solid fa-clock-rotate-left"></i> Histórica
                                    </span>
                                <?php } ?>
                            </div>

                            <div class="d-flex align-items-center gap-1.5 flex-wrap mt-2">
                                <span class="badge bg-light text-dark border px-2 py-0.5 small">
                                    <?= htmlspecialchars($c['nomval'] ?? 'Competencia'); ?>
                                </span>
                                <span class="badge bg-light text-secondary border px-2 py-0.5 small font-monospace">
                                    Norma: <?= htmlspecialchars($c['vercom'] ?? 'V1'); ?>
                                </span>
                            </div>
                        </div>

                        <div class="cmp-card-body">
                            <div class="cmp-card-title" title="<?= htmlspecialchars($c['descom']); ?>">
                                <?= htmlspecialchars($c['descom']); ?>
                            </div>

                            <div class="cmp-metrics-box">
                                <div class="row g-2">
                                    <div class="col-6 prg-metric-item">
                                        <i class="fa-regular fa-clock text-success me-1"></i> Horas: <strong><?= $c['horcom']; ?>h</strong>
                                        <div class="text-muted small" style="font-size: 0.7rem;">Carga curricular</div>
                                    </div>
                                    <div class="col-6 prg-metric-item">
                                        <i class="fa-solid fa-list-check text-primary me-1"></i> RAPs: 
                                        <a href="javascript:void(0)" class="fw-bold text-decoration-none text-primary" onclick="verRAPsModal('<?= $c['idcom']; ?>', '<?= htmlspecialchars(addslashes($c['descom'])); ?>')">
                                            <?= $cantRes; ?> logros &raquo;
                                        </a>
                                        <div class="text-muted small" style="font-size: 0.7rem;">
                                            <?= ($cantProgs > 0) ? "En {$cantProgs} programa(s)" : 'General'; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="cmp-card-footer">
                            <!-- ACCIÓN DESTACADA: VER RESULTADOS DE APRENDIZAJE -->
                            <button type="button" class="btn-action-primary" onclick="verRAPsModal('<?= $c['idcom']; ?>', '<?= htmlspecialchars(addslashes(preg_replace('/[\r\n]+/', ' ', substr($c['descom'] ?? '', 0, 75)))); ?>')">
                                <i class="fa-solid fa-list-check"></i> Ver resultados (RAPs)
                            </button>

                            <div class="d-flex align-items-center gap-1">
                                <button type="button" class="btn-action-icon" title="Editar Competencia" 
                                        onclick='abrirModalEditarCompetencia(<?= htmlspecialchars(json_encode($c), ENT_QUOTES, 'UTF-8'); ?>)'>
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                
                                <?php if ($prog) { ?>
                                    <button type="button" class="btn-action-icon del" title="Desvincular de este programa" 
                                            onclick="confirmarDesvincularCompetencia('<?= $prog['codpro']; ?>', '<?= $c['idcom']; ?>', '<?= htmlspecialchars(addslashes(preg_replace('/[\r\n]+/', ' ', substr($c['descom'] ?? '', 0, 75)))); ?>')">
                                        <i class="fa-solid fa-unlink"></i>
                                    </button>
                                <?php } else { ?>
                                    <button type="button" class="btn-action-icon del" title="Eliminar del catálogo" 
                                            onclick="confirmarEliminarCompetencia('<?= $c['idcom']; ?>', '<?= htmlspecialchars(addslashes(preg_replace('/[\r\n]+/', ' ', substr($c['descom'] ?? '', 0, 75)))); ?>')">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                <?php } 
            } ?>
        </div>

        <!-- PAGINADO E INFORMACIÓN DE LA PÁGINA (TARJETAS INTELIGENTES) -->
        <div class="cards-pagination-bar d-flex flex-wrap justify-content-between align-items-center gap-2" id="barraPaginacionCardsCmp">
            <div class="small text-muted" id="infoPaginacionCardsCmp">
                Mostrando <strong id="pagCardStartCmp">1</strong> a <strong id="pagCardEndCmp">12</strong> de <strong id="pagCardTotalCmp"><?= count($dat ?? []); ?></strong> competencias
            </div>
            <div class="d-flex align-items-center gap-2">
                <select id="selItemsPorPaginaCardsCmp" class="form-select form-select-sm" style="width: auto;" onchange="cambiarItemsPorPaginaCardsCmp(this.value)">
                    <option value="9">9 por página</option>
                    <option value="12" selected>12 por página</option>
                    <option value="24">24 por página</option>
                    <option value="todos">Todas</option>
                </select>
                <nav aria-label="Navegación de tarjetas">
                    <ul class="pagination pagination-sm mb-0" id="paginadorCardsUlCmp">
                        <!-- Botones de paginación generados por JS -->
                    </ul>
                </nav>
            </div>
        </div>

        <!-- ESTADO VACÍO EN CASO DE FILTRADO SIN COINCIDENCIAS -->
        <div id="emptyStateCardsCmp" class="card shadow-sm border-0 rounded-4 text-center p-5 my-4" style="display: none;">
            <div class="p-3 rounded-circle bg-light d-inline-flex mx-auto mb-3 text-muted fs-2" style="width: 64px; height: 64px; align-items: center; justify-content: center;">
                <i class="fa-solid fa-folder-open"></i>
            </div>
            <h5 class="fw-bold text-dark">No se encontraron competencias coincidentes</h5>
            <p class="text-muted small">Intenta cambiar los términos de búsqueda o los filtros de tipología y estado.</p>
            <div>
                <button type="button" class="btn btn-outline-success btn-sm px-3" onclick="resetearFiltrosCardsCmp()">
                    <i class="fa-solid fa-arrow-rotate-left me-1"></i> Restablecer filtros
                </button>
            </div>
        </div>
    </div>

    <!-- 6. CONTENEDOR 2: VISTA TABLA (OPCIÓN ALTERNATIVA) -->
    <div class="card shadow-sm border-0 rounded-4 overflow-hidden" id="contenedorTablaCompetencias" style="display: none;">
        <div class="table-responsive p-3">
            <table id="tablaCompetencias" class="table table-hover align-middle mb-0" style="width:100%">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width: 80px;">Código</th>
                        <th style="width: 90px;">Norma / Versión</th>
                        <th style="width: 110px;">Tipología</th>
                        <th>Denominación de la Competencia</th>
                        <th class="text-center" style="width: 80px;">Horas</th>
                        <th class="text-center" style="width: 100px;">Resultados</th>
                        <th class="text-center" style="width: 110px;">Estado Sede</th>
                        <th class="text-end" style="width: 120px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($dat)) {
                        foreach ($dat as $c) { 
                            $ficVigentes = (int)($c['fic_activas'] ?? ($c['fic_vigentes'] ?? 0));
                            $esActiva = ($ficVigentes > 0);
                            $cantRes = (int)($c['cant_res'] ?? 0);
                            $cantProgs = (int)($c['cant_progs'] ?? 0);
                        ?>
                            <tr>
                                <td class="text-center fw-bold font-monospace text-dark"><?= htmlspecialchars($c['idcom']); ?></td>
                                <td><span class="badge bg-light text-muted border font-monospace"><?= htmlspecialchars($c['vercom'] ?? 'V1'); ?></span></td>
                                <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($c['nomval'] ?? 'Competencia'); ?></span></td>
                                <td><div class="fw-semibold text-dark"><?= htmlspecialchars($c['descom']); ?></div></td>
                                <td class="text-center fw-bold text-dark"><?= $c['horcom']; ?>h</td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-outline-success px-2 py-0.5" onclick="verRAPsModal('<?= $c['idcom']; ?>', '<?= htmlspecialchars(addslashes(preg_replace('/[\r\n]+/', ' ', substr($c['descom'] ?? '', 0, 75)))); ?>')">
                                        <i class="fa-solid fa-list-check me-1"></i> <?= $cantRes; ?> RAPs
                                    </button>
                                </td>
                                <td class="text-center">
                                    <?php if ($esActiva) { ?>
                                        <span class="badge-status-active"><i class="fa-solid fa-circle-check"></i> Activa (<?= $ficVigentes; ?>)</span>
                                    <?php } else { ?>
                                        <span class="badge-status-inactive"><i class="fa-solid fa-clock-rotate-left"></i> Histórica</span>
                                    <?php } ?>
                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-1">
                                        <button type="button" class="btn-action-icon" title="Editar" onclick='abrirModalEditarCompetencia(<?= htmlspecialchars(json_encode($c), ENT_QUOTES, 'UTF-8'); ?>)'>
                                             <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                        <?php if ($prog) { ?>
                                            <button type="button" class="btn-action-icon del" title="Desvincular" onclick="confirmarDesvincularCompetencia('<?= $prog['codpro']; ?>', '<?= $c['idcom']; ?>', '<?= htmlspecialchars(addslashes(preg_replace('/[\r\n]+/', ' ', substr($c['descom'] ?? '', 0, 75)))); ?>')">
                                                <i class="fa-solid fa-unlink"></i>
                                            </button>
                                        <?php } else { ?>
                                            <button type="button" class="btn-action-icon del" title="Eliminar" onclick="confirmarEliminarCompetencia('<?= $c['idcom']; ?>', '<?= htmlspecialchars(addslashes(preg_replace('/[\r\n]+/', ' ', substr($c['descom'] ?? '', 0, 75)))); ?>')">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        <?php } ?>
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
<!-- MODALES DEL MÓDULO DE COMPETENCIAS                         -->
<!-- ========================================================== -->
<!-- Modal 1: Crear / Editar Competencia -->
<div class="modal fade" id="modalCompetencia" tabindex="-1" aria-labelledby="modalCompetenciaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form action="home.php?pg=<?= $pg; ?>" method="POST" id="formCompetencia">
                <div class="modal-header bg-light border-0 py-3">
                    <h5 class="modal-title fw-bold text-dark" id="modalCompetenciaLabel">
                        <i class="fa-solid fa-folder-open text-success me-2"></i><span id="lblTituloModalComp">Registrar Nueva Competencia</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" name="ope" value="save">
                    <input type="hidden" name="ope2" id="comp_ope2" value="">
                    <input type="hidden" name="codpro" value="<?= htmlspecialchars($codpro); ?>">

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="comp_idcom" class="form-label small fw-bold">Código Competencia <span class="text-danger">*</span></label>
                            <input type="number" name="idcom" id="comp_idcom" class="form-control" placeholder="Ej: 220501" required>
                        </div>
                        <div class="col-md-4">
                            <label for="comp_vercom" class="form-label small fw-bold">Versión de Norma</label>
                            <input type="text" name="vercom" id="comp_vercom" class="form-control" placeholder="Ej: V1 o 2024">
                        </div>
                        <div class="col-md-4">
                            <label for="comp_horcom" class="form-label small fw-bold">Horas Formativas <span class="text-danger">*</span></label>
                            <input type="number" name="horcom" id="comp_horcom" class="form-control" min="1" value="48" required>
                        </div>
                        <div class="col-md-6">
                            <label for="comp_idval" class="form-label small fw-bold">Tipología / Tipo <span class="text-danger">*</span></label>
                            <select name="idval" id="comp_idval" class="form-select" required>
                                <option value="">-- Selecciona Tipo --</option>
                                <?php if (!empty($dtTipcom)) { foreach ($dtTipcom as $tc) { ?>
                                    <option value="<?= $tc['idval']; ?>"><?= htmlspecialchars($tc['nomval']); ?></option>
                                <?php }} ?>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label for="comp_descom" class="form-label small fw-bold">Denominación Oficial de la Norma <span class="text-danger">*</span></label>
                            <textarea name="descom" id="comp_descom" class="form-control" rows="3" placeholder="Enunciado de la competencia laboral o resultado formativo..." required></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0 py-3">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-sena-green">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Guardar Competencia
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal 2: Visualizar RAPs de la Competencia -->
<div class="modal fade" id="modalVerRAPs" tabindex="-1" aria-labelledby="modalVerRAPsLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 bg-light py-3">
                <div>
                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2.5 py-1 mb-1">Resultados de Aprendizaje</span>
                    <h5 class="modal-title fw-bold text-dark" id="rap_comp_preview">-</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" id="contenedorListaRAPs">
                <!-- Se llena dinámicamente con JavaScript -->
            </div>
            <div class="modal-footer bg-light border-0 py-3 d-flex justify-content-between">
                <button type="button" class="btn btn-sena-green" id="btnAgregarRapDesdeModal">
                    <i class="fa-solid fa-plus me-1"></i> Nuevo Resultado para esta Competencia
                </button>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal 3: Vincular Competencia a Programa -->
<?php if ($prog) { ?>
<div class="modal fade" id="modalVincularComp" tabindex="-1" aria-labelledby="modalVincularCompLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form action="home.php?pg=<?= $pg; ?>" method="POST">
                <div class="modal-header bg-light border-0 py-3">
                    <h5 class="modal-title fw-bold text-dark" id="modalVincularCompLabel">
                        <i class="fa-solid fa-link text-success me-2"></i>Asignar a Malla Curricular
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" name="ope" value="vincular">
                    <input type="hidden" name="codpro" value="<?= $prog['codpro']; ?>">

                    <div class="alert alert-light border mb-3">
                        <small class="text-muted d-block fw-bold">Programa Destino:</small>
                        <strong class="text-dark">[<?= $prog['codpro']; ?>] <?= htmlspecialchars($prog['nompro']); ?></strong>
                    </div>

                    <div class="mb-3">
                        <label for="sel_idcom_vincular" class="form-label small fw-bold">Selecciona la Competencia a Vincular <span class="text-danger">*</span></label>
                        <select name="idcom" id="sel_idcom_vincular" class="form-select" required>
                            <option value="">-- Buscar en catálogo institucional --</option>
                            <?php if (!empty($dtDisponibles)) { foreach ($dtDisponibles as $d) { ?>
                                <option value="<?= $d['idcom']; ?>">
                                    [<?= $d['idcom']; ?>] <?= htmlspecialchars(substr($d['descom'], 0, 70)); ?>... (<?= $d['nomval']; ?> - <?= $d['horcom']; ?>h)
                                </option>
                            <?php }} ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0 py-3">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-sena-green">
                        <i class="fa-solid fa-check me-1"></i> Vincular a Programa
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php } ?>

<!-- Modal 4: Registrar Nuevo RAP -->
<div class="modal fade" id="modalResultado" tabindex="-1" aria-labelledby="modalResultadoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form action="home.php?pg=<?= $pg; ?>" method="POST" id="formResultado">
                <div class="modal-header bg-light border-0 py-3">
                    <h5 class="modal-title fw-bold text-dark" id="modalResultadoLabel">
                        <i class="fa-solid fa-list-check text-success me-2"></i><span id="lblTituloModalRs">Nuevo RAP</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" name="ope" value="saveRs">
                    <input type="hidden" name="ope2" id="rs_ope2" value="">
                    <input type="hidden" name="codpro" value="<?= htmlspecialchars($codpro); ?>">
                    <input type="hidden" name="idcom" id="rs_idcom" value="">

                    <div class="mb-3 p-2 bg-light rounded-3 border">
                        <small class="text-muted d-block fw-bold">Competencia:</small>
                        <span id="rs_nomcomp_preview" class="text-dark small">-</span>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="rs_idres" class="form-label small fw-bold">Código Resultado <span class="text-danger">*</span></label>
                            <input type="number" name="idres" id="rs_idres" class="form-control" placeholder="Ej: 590801" required>
                        </div>
                        <div class="col-md-6">
                            <label for="rs_ndeses" class="form-label small fw-bold">No. Sesiones <span class="text-danger">*</span></label>
                            <input type="number" name="ndeses" id="rs_ndeses" class="form-control" min="1" max="999" value="1" required>
                        </div>
                        <div class="col-md-12">
                            <label for="rs_nomres" class="form-label small fw-bold">Enunciado del Resultado de Aprendizaje <span class="text-danger">*</span></label>
                            <textarea name="nomres" id="rs_nomres" class="form-control" rows="3" placeholder="Redacta el logro de aprendizaje que alcanzará el aprendiz..." required></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0 py-3">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-sena-green">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Guardar RAP
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
const allResultsProg = <?= json_encode($rsProg ?? []); ?>;
let estadoFiltroCmpActual = 'todos';
let paginaActualCardsCmp = 1;
let itemsPorPaginaCardsCmp = 12;
let tablaCmpDT = null;

$(document).ready(function() {
    // 1. Inicializar DataTable para la vista alternativa de Tabla
    if ($.fn.DataTable.isDataTable('#tablaCompetencias')) {
        $('#tablaCompetencias').DataTable().destroy();
    }

    tablaCmpDT = $('#tablaCompetencias').DataTable({
        language: {
            processing: "Procesando...",
            search: "_INPUT_",
            searchPlaceholder: "Buscar competencias...",
            lengthMenu: "Mostrar _MENU_ registros",
            info: "Mostrando _START_ a _END_ de _TOTAL_ competencias",
            infoEmpty: "Mostrando 0 competencias",
            infoFiltered: "(filtrado de _MAX_ totales)",
            zeroRecords: "No se encontraron competencias con los filtros aplicados",
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
    $('#btnVistaCardsCmp').on('click', function() {
        $(this).addClass('active');
        $('#btnVistaTablaCmp').removeClass('active');
        $('#contenedorCardsCompetenciasWrapper').show();
        $('#contenedorTablaCompetencias').hide();
    });

    $('#btnVistaTablaCmp').on('click', function() {
        $(this).addClass('active');
        $('#btnVistaCardsCmp').removeClass('active');
        $('#contenedorCardsCompetenciasWrapper').hide();
        $('#contenedorTablaCompetencias').show();
        if (tablaCmpDT) {
            tablaCmpDT.columns.adjust().responsive.recalc();
        }
    });

    // 3. Buscador en Vivo
    $('#customSearchCmp').on('input keyup', function() {
        paginaActualCardsCmp = 1;
        ejecutarFiltroCardsCompetencias();
        if (tablaCmpDT) {
            tablaCmpDT.search(this.value).draw();
        }
    });

    // 4. Filtro por Tipología
    $('#filtroTipoCmp').on('change', function() {
        paginaActualCardsCmp = 1;
        ejecutarFiltroCardsCompetencias();
        if (tablaCmpDT) {
            const val = $(this).val();
            tablaCmpDT.column(2).search(val ? val : '', true, false).draw();
        }
    });

    // Inicializar paginación de tarjetas
    ejecutarFiltroCardsCompetencias();

    // 5. Cargar edición por GET si aplica
    <?php if (!empty($datOne) && !empty($datOne[0]['idcom'])) { ?>
        abrirModalEditarCompetencia(<?= json_encode($datOne[0]); ?>);
    <?php } ?>

    // 6. SweetAlert2 notificaciones
    const urlParams = new URLSearchParams(window.location.search);
    const msg = urlParams.get('msg');
    if (msg) {
        let titulo = '¡Éxito!';
        let texto = 'Operación realizada correctamente.';
        let icono = 'success';

        switch (msg) {
            case 'guardado': texto = 'Competencia guardada exitosamente.'; break;
            case 'editado': texto = 'Competencia actualizada correctamente.'; break;
            case 'eliminado': texto = 'Competencia eliminada.'; break;
            case 'vinculado': texto = 'Competencia asignada a la malla del programa.'; break;
            case 'desvinculado': texto = 'Competencia desvinculada del programa.'; break;
            case 'duplicado': titulo = 'Código Duplicado'; texto = 'El código ya existe para otra competencia.'; icono = 'error'; break;
            case 'rs_guardado': texto = 'Resultado de aprendizaje registrado correctamente.'; break;
            case 'rs_editado': texto = 'Resultado de aprendizaje actualizado correctamente.'; break;
            case 'rs_eliminado': texto = 'Resultado de aprendizaje eliminado.'; break;
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

function cambiarProgramaContexto(codpro) {
    window.location.href = `home.php?pg=<?= $pg; ?>&codpro=${codpro}`;
}

function filtrarCardsCmpPorEstado(estado) {
    estadoFiltroCmpActual = estado;
    paginaActualCardsCmp = 1;

    $('.pill-filter').removeClass('active');
    $('.kpi-card').removeClass('active');

    if (estado === 'activo') {
        $('#pillCmpActivas').addClass('active');
        $('#kpiCmpActivas').addClass('active');
        if (tablaCmpDT) { tablaCmpDT.column(6).search('Activa').draw(); }
    } else if (estado === 'inactivo') {
        $('#pillCmpInactivas').addClass('active');
        $('#kpiCmpInactivas').addClass('active');
        if (tablaCmpDT) { tablaCmpDT.column(6).search('Histórica|Reserva', true, false).draw(); }
    } else {
        $('#pillCmpTodos').addClass('active');
        $('#kpiCmpTotal').addClass('active');
        if (tablaCmpDT) { tablaCmpDT.column(6).search('').draw(); }
    }

    ejecutarFiltroCardsCompetencias();
}

function cambiarItemsPorPaginaCardsCmp(val) {
    itemsPorPaginaCardsCmp = (val === 'todos') ? 99999 : parseInt(val);
    paginaActualCardsCmp = 1;
    ejecutarFiltroCardsCompetencias();
}

function ejecutarFiltroCardsCompetencias() {
    const busqueda = $('#customSearchCmp').val().toLowerCase().trim();
    const tipoSel = $('#filtroTipoCmp').val().toLowerCase().trim();

    const coincidentes = [];
    $('.item-card-cmp').each(function() {
        const card = $(this);
        const cardEstado = card.data('estado');
        const cardTipo = String(card.data('tipo') || '');
        const cardSearchText = String(card.data('search') || '');

        const coincideEstado = (estadoFiltroCmpActual === 'todos') || (cardEstado === estadoFiltroCmpActual);
        const coincideTipo = (tipoSel === '') || (cardTipo.indexOf(tipoSel) > -1);
        const coincideBusqueda = (busqueda === '') || (cardSearchText.indexOf(busqueda) > -1);

        if (coincideEstado && coincideTipo && coincideBusqueda) {
            coincidentes.push(card);
        } else {
            card.hide();
        }
    });

    const totalCoincidentes = coincidentes.length;

    if (totalCoincidentes === 0) {
        $('#emptyStateCardsCmp').show();
        $('#contenedorCardsCompetencias').hide();
        $('#barraPaginacionCardsCmp').hide();
        return;
    }

    $('#emptyStateCardsCmp').hide();
    if (!$('#btnVistaTablaCmp').hasClass('active')) {
        $('#contenedorCardsCompetencias').show();
    }
    $('#barraPaginacionCardsCmp').show();

    const totalPaginas = Math.ceil(totalCoincidentes / itemsPorPaginaCardsCmp) || 1;
    if (paginaActualCardsCmp > totalPaginas) paginaActualCardsCmp = totalPaginas;
    if (paginaActualCardsCmp < 1) paginaActualCardsCmp = 1;

    const startIdx = (paginaActualCardsCmp - 1) * itemsPorPaginaCardsCmp;
    const endIdx = Math.min(startIdx + itemsPorPaginaCardsCmp, totalCoincidentes);

    coincidentes.forEach((card, index) => {
        if (index >= startIdx && index < endIdx) {
            card.show();
        } else {
            card.hide();
        }
    });

    $('#pagCardStartCmp').text(totalCoincidentes > 0 ? (startIdx + 1) : 0);
    $('#pagCardEndCmp').text(endIdx);
    $('#pagCardTotalCmp').text(totalCoincidentes);

    renderizarPaginadorCardsCmp(totalPaginas);
}

function irAPaginaCardsCmp(pag) {
    paginaActualCardsCmp = pag;
    ejecutarFiltroCardsCompetencias();
    $('html, body').animate({ scrollTop: $('#contenedorCardsCompetenciasWrapper').offset().top - 80 }, 200);
}

function renderizarPaginadorCardsCmp(totalPaginas) {
    const ul = $('#paginadorCardsUlCmp');
    ul.empty();

    if (totalPaginas <= 1) return;

    const prevDisabled = (paginaActualCardsCmp === 1) ? 'disabled' : '';
    ul.append(`<li class="page-item ${prevDisabled}">
        <a class="page-link" href="javascript:void(0)" onclick="irAPaginaCardsCmp(${paginaActualCardsCmp - 1})">
            &laquo; Anterior
        </a>
    </li>`);

    let startPage = Math.max(1, paginaActualCardsCmp - 2);
    let endPage = Math.min(totalPaginas, startPage + 4);
    if (endPage - startPage < 4) {
        startPage = Math.max(1, endPage - 4);
    }

    if (startPage > 1) {
        ul.append(`<li class="page-item"><a class="page-link" href="javascript:void(0)" onclick="irAPaginaCardsCmp(1)">1</a></li>`);
        if (startPage > 2) {
            ul.append(`<li class="page-item disabled"><span class="page-link">...</span></li>`);
        }
    }

    for (let i = startPage; i <= endPage; i++) {
        const activeClass = (i === paginaActualCardsCmp) ? 'active bg-success border-success text-white' : '';
        ul.append(`<li class="page-item ${i === paginaActualCardsCmp ? 'active' : ''}">
            <a class="page-link ${activeClass}" href="javascript:void(0)" onclick="irAPaginaCardsCmp(${i})">${i}</a>
        </li>`);
    }

    if (endPage < totalPaginas) {
        if (endPage < totalPaginas - 1) {
            ul.append(`<li class="page-item disabled"><span class="page-link">...</span></li>`);
        }
        ul.append(`<li class="page-item"><a class="page-link" href="javascript:void(0)" onclick="irAPaginaCardsCmp(${totalPaginas})">${totalPaginas}</a></li>`);
    }

    const nextDisabled = (paginaActualCardsCmp === totalPaginas) ? 'disabled' : '';
    ul.append(`<li class="page-item ${nextDisabled}">
        <a class="page-link" href="javascript:void(0)" onclick="irAPaginaCardsCmp(${paginaActualCardsCmp + 1})">
            Siguiente &raquo;
        </a>
    </li>`);
}

function resetearFiltrosCardsCmp() {
    $('#customSearchCmp').val('');
    $('#filtroTipoCmp').val('');
    filtrarCardsCmpPorEstado('todos');
}

// Modales
function abrirModalCompetenciaNueva() {
    $('#formCompetencia')[0].reset();
    $('#lblTituloModalComp').text('Registrar Nueva Competencia');
    $('#comp_ope2').val('');
    $('#comp_idcom').prop('readonly', false);
    const modal = new bootstrap.Modal(document.getElementById('modalCompetencia'));
    modal.show();
}

function abrirModalEditarCompetencia(comp) {
    $('#formCompetencia')[0].reset();
    $('#lblTituloModalComp').text('Editar Competencia');
    $('#comp_ope2').val('edit');
    $('#comp_idcom').val(comp.idcom).prop('readonly', true);
    $('#comp_descom').val(comp.descom);
    $('#comp_vercom').val(comp.vercom);
    $('#comp_horcom').val(comp.horcom);
    $('#comp_idval').val(comp.idval);
    const modal = new bootstrap.Modal(document.getElementById('modalCompetencia'));
    modal.show();
}

function abrirModalVincularCompetencia() {
    const modal = new bootstrap.Modal(document.getElementById('modalVincularComp'));
    modal.show();
}

// Modal para ver y gestionar RAPs de una competencia
window.verRAPsModal = function(idcom, descom) {
    $('#rap_comp_preview').text('[' + idcom + '] ' + descom);
    $('#btnAgregarRapDesdeModal').off('click').on('click', function() {
        const modalRAPs = bootstrap.Modal.getInstance(document.getElementById('modalVerRAPs'));
        modalRAPs?.hide();
        abrirModalNuevoResultado(idcom, descom);
    });

    const lista = allResultsProg[idcom] || [];
    let html = '';

    if (lista.length > 0) {
        html = `<table class="table table-sm align-middle">
            <thead class="table-light">
                <tr>
                    <th style="width: 100px;">Código RAP</th>
                    <th>Descripción</th>
                    <th class="text-center" style="width: 90px;">Sesiones</th>
                </tr>
            </thead>
            <tbody>`;
        lista.forEach(r => {
            html += `<tr>
                <td class="fw-bold font-monospace text-dark">${r.idres}</td>
                <td class="small text-dark">${r.nomres}</td>
                <td class="text-center"><span class="badge bg-light text-dark border">${r.ndeses || 1} ses.</span></td>
            </tr>`;
        });
        html += `</tbody></table>`;
    } else {
        html = `<div class="text-center text-muted p-4">
            <i class="fa-solid fa-folder-open fs-2 mb-2 d-block"></i>
            No hay resultados de aprendizaje (RAPs) registrados aún para esta competencia.
        </div>`;
    }

    $('#contenedorListaRAPs').html(html);
    const modal = new bootstrap.Modal(document.getElementById('modalVerRAPs'));
    modal.show();
};

function abrirModalNuevoResultado(idcom, descom) {
    $('#formResultado')[0].reset();
    $('#lblTituloModalRs').text('Nuevo Resultado de Aprendizaje');
    $('#rs_ope2').val('');
    $('#rs_idcom').val(idcom);
    $('#rs_nomcomp_preview').text('[' + idcom + '] ' + descom);
    $('#rs_idres').prop('readonly', false);
    const modal = new bootstrap.Modal(document.getElementById('modalResultado'));
    modal.show();
}

function confirmarDesvincularCompetencia(codpro, idcom, descom) {
    Swal.fire({
        title: '¿Desvincular Competencia?',
        html: `¿Deseas desvincular la competencia <strong>"${descom}"</strong> (${idcom}) de este programa?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ea580c',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, desvincular',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `home.php?pg=<?= $pg; ?>&codpro=${codpro}&idcom=${idcom}&ope=desvincular`;
        }
    });
}

function confirmarEliminarCompetencia(idcom, descom) {
    Swal.fire({
        title: '¿Eliminar Competencia?',
        html: `¿Deseas eliminar definitivamente la competencia <strong>"${descom}"</strong> (${idcom}) y sus resultados? Esta acción no se puede deshacer.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `home.php?pg=<?= $pg; ?>&idcom=${idcom}&ope=del`;
        }
    });
}
</script>
