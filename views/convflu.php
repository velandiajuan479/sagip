<?php
require_once("controllers/concflu.php");
?>

<div class="conte">
    <?php echo titulo2("<i class='".$icono."'></i> Flujos de Proceso", 1); ?>

    <!-- 1. KPI Cards -->
    <div class="row my-4 g-4">

        <!-- Total en Sistema -->
        <div class="col-12 col-md-4">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold small text-secondary">Total en Sistema</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold fs-2 lh-1 text-dark"><?= $totFlu ?? (count($datAct ?? []) + count($datInact ?? [])); ?></span>
                            <span class="small text-secondary">flujos</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box bg-primary-subtle text-primary">
                        <i class="fa-solid fa-layer-group fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="fw-semibold d-inline-flex align-items-center gap-1 small text-primary">
                        <i class="fa-solid fa-arrow-trend-up"></i> Registrados
                    </span>
                    <span class="small text-secondary">Pasos: <?= $metrics['total_pasos'] ?? 0; ?></span>
                </div>
            </div>
        </div>

        <!-- Flujos Activos -->
        <div class="col-12 col-md-4">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold small text-success">Flujos Activos</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold fs-2 lh-1 text-success"><?= $totAct ?? count($datAct ?? []); ?></span>
                            <span class="small text-success opacity-75">en operación</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box bg-success-subtle text-success">
                        <i class="fa-solid fa-diagram-project fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="fw-semibold d-inline-flex align-items-center gap-2 small text-success">
                        <span class="pulse-dot"></span> Flujos habilitados
                    </span>
                    <span class="small text-secondary">Activos: 100% OK</span>
                </div>
            </div>
        </div>

        <!-- Flujos Inactivos -->
        <div class="col-12 col-md-4">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold small text-danger">Flujos Inactivos</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold fs-2 lh-1 text-dark"><?= $totInact ?? count($datInact ?? []); ?></span>
                            <span class="small text-secondary">archivados</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box bg-danger-subtle text-danger">
                        <i class="fa-solid fa-circle-pause fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="d-inline-flex align-items-center gap-1 small text-secondary">
                        <i class="fa-solid fa-clock-rotate-left"></i> Fuera de operación
                    </span>
                    <span class="fw-semibold small text-primary">Pausados</span>
                </div>
            </div>
        </div>

    </div>

    <!-- 2. Últimos Flujos Creados -->
    <?php if (!empty($metrics['recientes'])): ?>
    <div class="kpi-card mb-4">
        <h6 class="fw-bold mb-3 small text-secondary">
            <i class="fa-solid fa-clock-rotate-left me-2"></i> Últimos Flujos Creados
        </h6>
        <div class="row g-3">
            <?php foreach ($metrics['recientes'] as $rec):
                $esActivo = intval($rec['fluacti']) === 1;
                $pasos    = intval($rec['cant_pasos']);
            ?>
            <div class="col-12 col-sm-6 col-lg-4">
                <a href="home.php?pg=2009&idflu=<?= $rec['idflu']; ?>" class="text-decoration-none" title="Ver pasos">
                    <div class="card h-100 border-0 shadow-sm vid-card">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-secondary rounded-pill">#<?= $rec['idflu']; ?></span>
                                    <span class="badge <?= $esActivo ? 'bg-success' : 'bg-danger'; ?>">
                                        <i class="fa-solid <?= $esActivo ? 'fa-circle-check' : 'fa-circle-xmark'; ?> me-1"></i>
                                        <?= $esActivo ? 'Activo' : 'Inactivo'; ?>
                                    </span>
                                </div>
                                <span class="badge bg-light text-dark border">
                                    <i class="fa-solid fa-list-ol me-1"></i><?= $pasos; ?> <?= $pasos === 1 ? 'paso' : 'pasos'; ?>
                                </span>
                            </div>
                            <div class="fw-bold text-dark text-truncate"><?= htmlspecialchars($rec['nomflu']); ?></div>
                        </div>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- 3. Formulario de Registro / Edición -->
    <div class="inser">
        <form id="frmins" class="filter-toolbar-container" action="home.php?pg=<?= $pg; ?>" method="POST">
            <h2><i class="fa-solid fa-file-circle-plus"></i> <?= $dtOn ? 'Editar flujo de proceso' : 'Nuevo flujo de proceso'; ?></h2>
            <div class="row">
                <div class="form-group col-md-4">
                    <label for="nomflu">Nombre</label>
                    <input type="text" class="form-control" name="nomflu" id="nomflu"
                           value="<?php if($dtOn) echo htmlspecialchars($dtOn[0]['nomflu']); ?>" required>
                </div>

                <div class="form-group col-md-4">
                    <label for="descflu">Descripción</label>
                    <input type="text" class="form-control" name="descflu" id="descflu"
                           value="<?php if($dtOn) echo htmlspecialchars($dtOn[0]['descflu']); ?>">
                </div>

                <div class="form-group col-md-2">
                    <label for="fluacti">Activo</label>
                    <select class="form-control form-select" name="fluacti" id="fluacti">
                        <option value="1" <?php if(!$dtOn || ($dtOn && $dtOn[0]['fluacti']=="1")) echo 'selected'; ?>>Sí</option>
                        <option value="2" <?php if($dtOn && $dtOn[0]['fluacti']!="1") echo 'selected'; ?>>No</option>
                    </select>
                </div>

                <div class="form-group col-md-2">
                    <br>
                    <input type="hidden" name="idflu" value="<?php if($dtOn) echo $dtOn[0]['idflu']; ?>">
                    <input type="hidden" name="ope" value="save">
                    <input type="hidden" name="opera" value="save">
                    <input class="btn btn-success w-100" type="submit" value="<?= $dtOn ? 'Guardar' : 'Enviar'; ?>">
                    <br><br>
                </div>

                <?php if($dtOn): ?>
                <div class="col-12 mt-2">
                    <a href="home.php?pg=<?= $pg; ?>" class="btn btn-secondary btn-sm">
                        <i class="fa-solid fa-xmark me-1"></i> Cancelar edición
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Toolbar de Control y Filtros (Estructura referenciada de vvidgen.php) -->
<div class="filter-toolbar-container">
    <div class="toolbar-left-group">
        <div class="filter-pills-group" id="tabFlujos" role="tablist">
            <!-- Píldora Activos (Activa por defecto al ingresar, como en vvidgen) -->
            <button class="btn-filter-pill active" id="activos-tab" data-bs-toggle="tab" data-bs-target="#activos"
                    type="button" role="tab" aria-controls="activos" aria-selected="true" onclick="filterByTab('activos')">
                <span class="pill-dot bg-success"></span>
                <span>Activos</span>
                <span class="pill-count"><?= count($datAct ?? []); ?></span>
            </button>

            <!-- Píldora Inactivos -->
            <button class="btn-filter-pill" id="inactivos-tab" data-bs-toggle="tab" data-bs-target="#inactivos"
                    type="button" role="tab" aria-controls="inactivos" aria-selected="false" onclick="filterByTab('inactivos')">
                <span class="pill-dot bg-danger"></span>
                <span>Inactivos</span>
                <span class="pill-count"><?= count($datInact ?? []); ?></span>
            </button>

            <!-- Píldora Todos -->
            <button class="btn-filter-pill" id="todos-tab" data-bs-toggle="tab" data-bs-target="#todos"
                    type="button" role="tab" aria-controls="todos" aria-selected="false" onclick="filterByTab('todos')">
                <span class="pill-dot bg-primary"></span>
                <span>Todos</span>
                <span class="pill-count"><?= $totFlu ?? (count($datAct ?? []) + count($datInact ?? [])); ?></span>
            </button>
        </div>

        <div class="toolbar-divider d-none d-sm-block"></div>

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

    <div class="toolbar-search-sort-group">
        <div class="toolbar-search-box">
            <i class="fa-solid fa-magnifying-glass icoldiz"></i>
            <input type="text" id="customVideoSearch" placeholder="Buscar" onkeyup="syncCustomSearch(this.value)">
        </div>

        <div class="toolbar-secondary-controls">
            <select class="toolbar-select-sort" id="customVideoSort" onchange="syncCustomSort(this.value)">
                <option value="asc">Ascendente</option>
                <option value="desc">Descendente</option>
            </select>

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

<!-- Tabs de Contenido -->
<div class="tab-content" id="tabFluContent">

    <!-- Pestaña 1: Flujos Activos (Activa por defecto al ingresar, como en vvidgen) -->
    <div class="tab-pane fade show active" id="activos" role="tabpanel" aria-labelledby="activos-tab">
        <table id="example" class="table table-striped table-vid-cards w-100">
            <thead><tr><th>Flujos Activos</th></tr></thead>
            <tbody>
                <?php if($datAct){ foreach($datAct as $f){ ?>
                <tr data-order="<?= intval($f['idflu']); ?>">
                    <td data-search="<?= $f['idflu']; ?> <?= htmlspecialchars($f['nomflu']); ?> <?= htmlspecialchars($f['descflu'] ?? ''); ?> Activo">
                        <div class="card h-100 shadow-sm border-0 vid-card">
                            <div class="card-body p-3 d-flex flex-column justify-content-between">
                                <div>
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge bg-secondary rounded-pill">#<?= $f['idflu']; ?></span>
                                            <span class="badge bg-success shadow-sm px-2 py-1">
                                                <i class="fa-solid <?= $esActivo ? 'fa-circle-check' : 'fa-circle-xmark'; ?> me-1"></i>
                                                <?= $esActivo ? 'Activo' : 'Inactivo'; ?>
                                            </span>
                                        </div>
                                        <a href="home.php?pg=2009&idflu=<?= $f['idflu']; ?>"
                                           class="badge bg-primary text-decoration-none px-2 py-1 shadow-sm">
                                            <i class="fa-solid fa-shoe-prints me-1"></i> <?= $f['total_pasos']; ?> <?= $f['total_pasos'] == 1 ? 'Paso' : 'Pasos'; ?>
                                        </a>
                                    </div>
                                    <h6 class="fw-bold text-dark mb-2 text-truncate fs-6" title="<?= htmlspecialchars($f['nomflu']); ?>">
                                        <span class="text-primary">#<?= $f['idflu']; ?></span> - <?= htmlspecialchars($f['nomflu']); ?>
                                    </h6>
                                    <?php if(!empty($f['descflu'])): ?>
                                        <p class="text-muted mb-2 small"><?= htmlspecialchars($f['descflu']); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="card-footer bg-white border-0 pt-0 pb-3 px-3">
                                <hr class="mt-0 mb-3 text-muted opacity-25">
                                <div class="vid-actions-bar">
                                    <a href="home.php?pg=2009&idflu=<?= $f['idflu']; ?>"
                                       class="btn btn-outline-primary shadow-sm" title="Ver Pasos">
                                        <i class="fa-solid fa-shoe-prints"></i> <span class="d-none d-sm-inline">Pasos</span>
                                    </a>
                                    <a href="home.php?pg=<?= $pg; ?>&idflu=<?= $f['idflu']; ?>&ope=edi"
                                       class="btn btn-outline-warning text-dark shadow-sm" title="Editar">
                                        <i class="fa-solid fa-pen-to-square"></i> <span class="d-none d-sm-inline">Editar</span>
                                    </a>
                                    <form action="home.php?pg=<?= $pg; ?>" method="POST" class="m-0 p-0 w-100">
                                        <input type="hidden" name="idflu" value="<?= $f['idflu']; ?>">
                                        <input type="hidden" name="fluacti" value="2">
                                        <input type="hidden" name="act" value="2">
                                        <input type="hidden" name="ope" value="act">
                                        <button type="submit" class="btn btn-outline-secondary w-100 shadow-sm" title="Desactivar Flujo">
                                            <i class="fa-solid fa-toggle-on text-primary"></i> <span class="d-none d-sm-inline">Desact.</span>
                                        </button>
                                    </form>
                                    <a href="home.php?pg=<?= $pg; ?>&idflu=<?= $f['idflu']; ?>&ope=eli"
                                       onclick="return eli(this);" class="btn btn-outline-danger shadow-sm" title="Eliminar">
                                        <i class="fa-solid fa-trash-can"></i> <span class="d-none d-sm-inline">Elim.</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                <?php }} ?>
            </tbody>
            <tfoot><tr><th>Flujos Activos</th></tr></tfoot>
        </table>
    </div>

    <!-- Pestaña 2: Flujos Inactivos -->
    <div class="tab-pane fade" id="inactivos" role="tabpanel" aria-labelledby="inactivos-tab">
        <table id="myt" class="table table-striped table-vid-cards w-100">
            <thead><tr><th>Flujos Inactivos</th></tr></thead>
            <tbody>
                <?php if($datInact){ foreach($datInact as $f){ ?>
                <tr data-order="<?= intval($f['idflu']); ?>">
                    <td data-search="<?= $f['idflu']; ?> <?= htmlspecialchars($f['nomflu']); ?> <?= htmlspecialchars($f['descflu'] ?? ''); ?> Inactivo">
                        <div class="card h-100 shadow-sm border-0 vid-card">
                            <div class="card-body p-3 d-flex flex-column justify-content-between">
                                <div>
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge bg-secondary rounded-pill">#<?= $f['idflu']; ?></span>
                                            <span class="badge bg-danger shadow-sm px-2 py-1">
                                                <i class="fa-solid fa-circle-xmark me-1"></i> Inactivo
                                            </span>
                                        </div>
                                        <a href="home.php?pg=2009&idflu=<?= $f['idflu']; ?>"
                                           class="badge bg-primary text-decoration-none px-2 py-1 shadow-sm">
                                            <i class="fa-solid fa-shoe-prints me-1"></i> <?= $f['total_pasos']; ?> <?= $f['total_pasos'] == 1 ? 'Paso' : 'Pasos'; ?>
                                        </a>
                                    </div>
                                    <h6 class="fw-bold text-dark mb-2 text-truncate fs-6" title="<?= htmlspecialchars($f['nomflu']); ?>">
                                        <span class="text-danger">#<?= $f['idflu']; ?></span> - <?= htmlspecialchars($f['nomflu']); ?>
                                    </h6>
                                    <?php if(!empty($f['descflu'])): ?>
                                        <p class="text-muted mb-2 small"><?= htmlspecialchars($f['descflu']); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="card-footer bg-white border-0 pt-0 pb-3 px-3">
                                <hr class="mt-0 mb-3 text-muted opacity-25">
                                <div class="vid-actions-bar">
                                    <a href="home.php?pg=2009&idflu=<?= $f['idflu']; ?>"
                                       class="btn btn-outline-primary shadow-sm" title="Ver Pasos">
                                        <i class="fa-solid fa-shoe-prints"></i> <span class="d-none d-sm-inline">Pasos</span>
                                    </a>
                                    <a href="home.php?pg=<?= $pg; ?>&idflu=<?= $f['idflu']; ?>&ope=edi"
                                       class="btn btn-outline-warning text-dark shadow-sm" title="Editar">
                                        <i class="fa-solid fa-pen-to-square"></i> <span class="d-none d-sm-inline">Editar</span>
                                    </a>
                                    <form action="home.php?pg=<?= $pg; ?>" method="POST" class="m-0 p-0 w-100">
                                        <input type="hidden" name="idflu" value="<?= $f['idflu']; ?>">
                                        <input type="hidden" name="fluacti" value="1">
                                        <input type="hidden" name="act" value="1">
                                        <input type="hidden" name="ope" value="act">
                                        <button type="submit" class="btn btn-outline-success w-100 shadow-sm" title="Activar Flujo">
                                            <i class="fa-solid fa-toggle-off text-success"></i> <span class="d-none d-sm-inline">Activar</span>
                                        </button>
                                    </form>
                                    <a href="home.php?pg=<?= $pg; ?>&idflu=<?= $f['idflu']; ?>&ope=eli"
                                       onclick="return eli(this);" class="btn btn-outline-danger shadow-sm" title="Eliminar">
                                        <i class="fa-solid fa-trash-can"></i> <span class="d-none d-sm-inline">Elim.</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                <?php }} ?>
            </tbody>
            <tfoot><tr><th>Flujos Inactivos</th></tr></tfoot>
        </table>
    </div>

    <!-- Pestaña 3: Todos los Flujos -->
    <div class="tab-pane fade" id="todos" role="tabpanel" aria-labelledby="todos-tab">
        <table id="tblTodos" class="table table-striped table-vid-cards w-100">
            <thead><tr><th>Todos los Flujos</th></tr></thead>
            <tbody>
                <?php 
                $datTodos = array_merge($datAct ?? [], $datInact ?? []);
                if (empty($datTodos) && !empty($datAll)) {
                    $datTodos = $datAll;
                }
                if($datTodos){ foreach($datTodos as $f){
                    $esActivo = intval($f['fluacti']) === 1;
                ?>
                <tr data-order="<?= intval($f['idflu']); ?>">
                    <td data-search="<?= $f['idflu']; ?> <?= htmlspecialchars($f['nomflu']); ?> <?= htmlspecialchars($f['descflu'] ?? ''); ?> <?= $esActivo ? 'Activo' : 'Inactivo'; ?>">
                        <div class="card h-100 shadow-sm border-0 vid-card">
                            <div class="card-body p-3 d-flex flex-column justify-content-between">
                                <div>
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge bg-secondary rounded-pill">#<?= $f['idflu']; ?></span>
                                            <span class="badge <?= $esActivo ? 'bg-success' : 'bg-danger'; ?> shadow-sm px-2 py-1">
                                                <i class="fa-solid <?= $esActivo ? 'fa-circle-check' : 'fa-circle-xmark'; ?> me-1"></i>
                                                <?= $esActivo ? 'Activo' : 'Inactivo'; ?>
                                            </span>
                                        </div>
                                        <a href="home.php?pg=2009&idflu=<?= $f['idflu']; ?>"
                                           class="badge bg-primary text-decoration-none px-2 py-1 shadow-sm">
                                            <i class="fa-solid fa-shoe-prints me-1"></i> <?= $f['total_pasos']; ?> <?= $f['total_pasos'] == 1 ? 'Paso' : 'Pasos'; ?>
                                        </a>
                                    </div>
                                    <h6 class="fw-bold text-dark mb-2 text-truncate fs-6" title="<?= htmlspecialchars($f['nomflu']); ?>">
                                        <span class="<?= $esActivo ? 'text-primary' : 'text-danger'; ?>">#<?= $f['idflu']; ?></span> - <?= htmlspecialchars($f['nomflu']); ?>
                                    </h6>
                                    <?php if(!empty($f['descflu'])): ?>
                                        <p class="text-muted mb-2 small"><?= htmlspecialchars($f['descflu']); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="card-footer bg-white border-0 pt-0 pb-3 px-3">
                                <hr class="mt-0 mb-3 text-muted opacity-25">
                                <div class="vid-actions-bar">
                                    <a href="home.php?pg=2009&idflu=<?= $f['idflu']; ?>"
                                       class="btn btn-outline-primary shadow-sm" title="Ver Pasos">
                                        <i class="fa-solid fa-shoe-prints"></i> <span class="d-none d-sm-inline">Pasos</span>
                                    </a>
                                    <a href="home.php?pg=<?= $pg; ?>&idflu=<?= $f['idflu']; ?>&ope=edi"
                                       class="btn btn-outline-warning text-dark shadow-sm" title="Editar">
                                        <i class="fa-solid fa-pen-to-square"></i> <span class="d-none d-sm-inline">Editar</span>
                                    </a>
                                    <?php if($esActivo): ?>
                                    <form action="home.php?pg=<?= $pg; ?>" method="POST" class="m-0 p-0 w-100">
                                        <input type="hidden" name="idflu" value="<?= $f['idflu']; ?>">
                                        <input type="hidden" name="fluacti" value="2">
                                        <input type="hidden" name="act" value="2">
                                        <input type="hidden" name="ope" value="act">
                                        <button type="submit" class="btn btn-outline-secondary w-100 shadow-sm" title="Desactivar Flujo">
                                            <i class="fa-solid fa-toggle-on text-primary"></i> <span class="d-none d-sm-inline">Desact.</span>
                                        </button>
                                    </form>
                                    <?php else: ?>
                                    <form action="home.php?pg=<?= $pg; ?>" method="POST" class="m-0 p-0 w-100">
                                        <input type="hidden" name="idflu" value="<?= $f['idflu']; ?>">
                                        <input type="hidden" name="fluacti" value="1">
                                        <input type="hidden" name="act" value="1">
                                        <input type="hidden" name="ope" value="act">
                                        <button type="submit" class="btn btn-outline-success w-100 shadow-sm" title="Activar Flujo">
                                            <i class="fa-solid fa-toggle-off text-success"></i> <span class="d-none d-sm-inline">Activar</span>
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                    <a href="home.php?pg=<?= $pg; ?>&idflu=<?= $f['idflu']; ?>&ope=eli"
                                       onclick="return eli(this);" class="btn btn-outline-danger shadow-sm" title="Eliminar">
                                        <i class="fa-solid fa-trash-can"></i> <span class="d-none d-sm-inline">Elim.</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                <?php }} ?>
            </tbody>
            <tfoot><tr><th>Todos los Flujos</th></tr></tfoot>
        </table>
    </div>

</div><!-- /tab-content -->