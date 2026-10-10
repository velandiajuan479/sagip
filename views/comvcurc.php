<?php require_once("controllers/comccurc.php"); ?>

<div class="conte">
    <?php echo titulo2("<i class='".$icono."'></i> Cursos Complementarios",1); ?>

    <?php if(!empty($msgccm)): ?>
        <div class="alert alert-<?= $tipoccm ?> alert-dismissible fade show">
            <?= htmlspecialchars($msgccm) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    <?php endif; ?>

    <div class="row my-4 g-4">
        <!-- Total registrados -->
        <div class="col-12 col-md-4">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold small text-secondary">Total en Sistema</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold fs-2 lh-1"><?= $dash['registrados']; ?></span>
                            <span class="small text-secondary">cursos</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box bg-primary bg-opacity-10 text-primary">
                        <i class="fa-solid fa-layer-group fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="fw-semibold d-inline-flex align-items-center gap-1 small text-primary">
                        <i class="fa-solid fa-arrow-trend-up"></i> Registrados
                    </span>
                    <span class="small text-secondary">Cupos: <?= $dash['cupos']; ?></span>
                </div>
            </div>
        </div>

        <!-- Utilizados -->
        <div class="col-12 col-md-4">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold small text-success">Cursos Utilizados</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold fs-2 lh-1 text-success"><?= $dash['utilizados']; ?></span>
                            <span class="small text-success">en hojas</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box bg-success bg-opacity-10 text-success">
                        <i class="fa-solid fa-circle-check fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="fw-semibold d-inline-flex align-items-center gap-2 small text-success">
                        <span class="pulse-dot"></span> Aprovechamiento <?= $aprove; ?>%
                    </span>
                    <span class="small text-secondary"><?= $dash['hojas']; ?> hoja(s)</span>
                </div>
            </div>
        </div>

        <!-- Sin utilizar -->
        <div class="col-12 col-md-4">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold small text-danger">Sin Utilizar</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold fs-2 lh-1"><?= $sinUsar; ?></span>
                            <span class="small text-secondary">cursos</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box bg-danger bg-opacity-10 text-danger">
                        <i class="fa-solid fa-circle-pause fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="d-inline-flex align-items-center gap-1 small text-secondary">
                        <i class="fa-solid fa-clock-rotate-left"></i> Nunca programados
                    </span>
                    <span class="fw-semibold small text-primary">Activos año: <?= $dash['activos']; ?></span>
                </div>
            </div>
        </div>
    </div>

    <div class="inser">
        <form id="frmins" action="home.php?pg=<?=$pg;?>" method="POST">
            <h2><i class="fa-solid fa-file-circle-plus"></i> <?= $datOne ? 'Editar curso' : 'Nuevo curso complementario' ?></h2>
            <div class="row">
                <div class="form-group col-md-3">
                    <label for="codpro">Código</label>
                    <input type="text" class="form-control" name="codpro" id="codpro"
                    value="<?php if($datOne) echo $datOne[0]['codpro']; ?>" <?= $datOne ? 'readonly' : '' ?> required>
                </div>
                <div class="form-group col-md-6">
                    <label for="nompro">Nombre del curso</label>
                    <input type="text" class="form-control" name="nompro" id="nompro"
                    value="<?php if($datOne) echo htmlspecialchars($datOne[0]['nompro']); ?>" required>
                </div>
                <div class="form-group col-md-3">
                    <label for="horlpro">Duración (horas)</label>
                    <input type="number" class="form-control" name="horlpro" id="horlpro" min="1"
                    value="<?php if($datOne) echo $datOne[0]['horlpro']; ?>" required>
                </div>
                <div class="form-group col-md-3">
                    <label for="verpro">Versión</label>
                    <input type="number" class="form-control" name="verpro" id="verpro" min="1"
                    value="<?php echo $datOne ? $datOne[0]['verpro'] : '1'; ?>" required>
                </div>
                <div class="form-group col-md-7">
                    <label for="despro">Descripción</label>
                    <input type="text" class="form-control" name="despro" id="despro"
                    value="<?php if($datOne) echo htmlspecialchars($datOne[0]['despro']); ?>">
                </div>
                <div class="form-group col-md-2">
                    <br>
                    <input type="hidden" name="tippro" value="1083">
                    <input type="hidden" name="opera" value="<?= $datOne ? 'edit' : 'save' ?>">
                    <input class="btn btn-success w-100" type="submit"
                           value="<?= $datOne ? 'Guardar' : 'Enviar' ?>">
                    <br><br>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Toolbar de Control y Filtros -->
<div class="filter-toolbar-container">
    <div class="toolbar-left-group">
        <div class="filter-pills-group" id="tabCursos" role="tablist">
            <button class="btn-filter-pill active" id="todos-tab" data-bs-toggle="tab" data-bs-target="#todos" type="button" role="tab" aria-controls="todos" aria-selected="true" onclick="filterByTab('todos')">
                <span class="pill-dot bg-primary"></span>
                <span>Todos</span>
                <span class="pill-count"><?= $dash['registrados']; ?></span>
            </button>

            <button class="btn-filter-pill" id="usados-tab" data-bs-toggle="tab" data-bs-target="#usados" type="button" role="tab" aria-controls="usados" aria-selected="false" onclick="filterByTab('usados')">
                <span class="pill-dot bg-success"></span>
                <span>Utilizados</span>
                <span class="pill-count"><?= $dash['utilizados']; ?></span>
            </button>

            <button class="btn-filter-pill" id="libres-tab" data-bs-toggle="tab" data-bs-target="#libres" type="button" role="tab" aria-controls="libres" aria-selected="false" onclick="filterByTab('libres')">
                <span class="pill-dot bg-danger"></span>
                <span>Sin utilizar</span>
                <span class="pill-count"><?= $sinUsar; ?></span>
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

<div class="tab-content" id="tabCursosContent">

    <!-- Pestaña 1: Todos -->
    <div class="tab-pane fade show active" id="todos" role="tabpanel" aria-labelledby="todos-tab">
        <table id="example" class="table table-striped table-vid-cards w-100">
            <thead>
                <tr><th>Todos los cursos</th></tr>
            </thead>
            <tbody>
                <?php if($datAll){ foreach($datAll as $dta){
                    $usos = isset($dta['usos']) ? (int)$dta['usos'] : 0;
                ?>
                <tr data-order="<?= $dta['codpro']; ?>">
                    <td data-search="<?= $dta['codpro']; ?> <?= $dta['nompro']; ?> <?= $dta['verpro']; ?> <?= $dta['horlpro']; ?>">
                        <div class="card h-100 shadow-sm border-0 vid-card">
                            <div class="card-body p-3 d-flex flex-column justify-content-between">
                                <div>
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <?php if($usos > 0): ?>
                                            <span class="badge bg-success px-2 py-1">
                                                <i class="fa-solid fa-circle-check me-1"></i> <?= $usos ?> hoja(s)
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary px-2 py-1">
                                                <i class="fa-solid fa-circle-pause me-1"></i> Sin usar
                                            </span>
                                        <?php endif; ?>
                                        <span class="badge bg-light text-dark px-2 py-1">
                                            <i class="fa-regular fa-clock me-1 text-warning"></i> <?= htmlspecialchars($dta['horlpro']); ?> h
                                        </span>
                                    </div>

                                    <h6 class="fw-bold text-dark mb-2" title="<?= htmlspecialchars($dta['nompro']); ?>">
                                        <span class="text-primary">#<?= htmlspecialchars($dta['codpro']); ?></span> - <?= htmlspecialchars($dta['nompro']); ?>
                                    </h6>

                                    <div class="p-2 mb-2 rounded-3 bg-light border border-light-subtle d-flex justify-content-between align-items-center small">
                                        <div>
                                            <span class="text-muted d-block small">Versión</span>
                                            <span class="fw-bold text-success"><?= htmlspecialchars($dta['verpro']); ?></span>
                                        </div>
                                        <div class="text-end">
                                            <span class="text-muted d-block small">Tipo</span>
                                            <span class="fw-bold text-secondary">Complementario</span>
                                        </div>
                                    </div>

                                    <?php if(!empty($dta['despro'])): ?>
                                        <p class="text-muted mb-0 small"><?= htmlspecialchars($dta['despro']); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="card-footer bg-white border-0 pt-0 pb-3 px-3">
                                <hr class="mt-0 mb-3 text-muted opacity-25">
                                <div class="vid-actions-bar">
                                    <a href="home.php?pg=<?=$pg;?>&codpro=<?= urlencode($dta['codpro']); ?>&opera=edi" class="btn btn-outline-warning text-dark shadow-sm" title="Editar">
                                        <i class="fa-solid fa-pen-to-square"></i> <span class="d-none d-sm-inline">Editar</span>
                                    </a>
                                    <?php if($usos == 0): ?>
                                        <a href="home.php?pg=<?=$pg;?>&codpro=<?= urlencode($dta['codpro']); ?>&opera=eli" class="btn btn-outline-danger shadow-sm" title="Eliminar" onclick="return eli(this);">
                                            <i class="fa-solid fa-trash-can"></i> <span class="d-none d-sm-inline">Elim.</span>
                                        </a>
                                    <?php else: ?>
                                        <button type="button" class="btn btn-outline-secondary shadow-sm" disabled title="El curso está en uso">
                                            <i class="fa-solid fa-lock"></i> <span class="d-none d-sm-inline">En uso</span>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                <?php }} ?>
            </tbody>
            <tfoot>
                <tr><th>Todos los cursos</th></tr>
            </tfoot>
        </table>
    </div>

    <!-- Pestaña 2: Utilizados -->
    <div class="tab-pane fade" id="usados" role="tabpanel" aria-labelledby="usados-tab">
        <table id="myt" class="table table-striped table-vid-cards w-100">
            <thead>
                <tr><th>Cursos utilizados</th></tr>
            </thead>
            <tbody>
                <?php if($datAll){ foreach($datAll as $dta){
                    $usos = isset($dta['usos']) ? (int)$dta['usos'] : 0;
                    if($usos == 0) continue;
                ?>
                <tr data-order="<?= $dta['codpro']; ?>">
                    <td data-search="<?= $dta['codpro']; ?> <?= $dta['nompro']; ?> <?= $dta['verpro']; ?> <?= $dta['horlpro']; ?>">
                        <div class="card h-100 shadow-sm border-0 vid-card">
                            <div class="card-body p-3 d-flex flex-column justify-content-between">
                                <div>
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <span class="badge bg-success px-2 py-1">
                                            <i class="fa-solid fa-circle-check me-1"></i> <?= $usos ?> hoja(s)
                                        </span>
                                        <span class="badge bg-light text-dark px-2 py-1">
                                            <i class="fa-regular fa-clock me-1 text-warning"></i> <?= htmlspecialchars($dta['horlpro']); ?> h
                                        </span>
                                    </div>
                                    <h6 class="fw-bold text-dark mb-2">
                                        <span class="text-primary">#<?= htmlspecialchars($dta['codpro']); ?></span> - <?= htmlspecialchars($dta['nompro']); ?>
                                    </h6>
                                    <div class="p-2 mb-2 rounded-3 bg-light border border-light-subtle d-flex justify-content-between align-items-center small">
                                        <div>
                                            <span class="text-muted d-block small">Versión</span>
                                            <span class="fw-bold text-success"><?= htmlspecialchars($dta['verpro']); ?></span>
                                        </div>
                                        <div class="text-end">
                                            <span class="text-muted d-block small">Hojas generadas</span>
                                            <span class="fw-bold text-success"><?= $usos ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer bg-white border-0 pt-0 pb-3 px-3">
                                <hr class="mt-0 mb-3 text-muted opacity-25">
                                <div class="vid-actions-bar">
                                    <a href="home.php?pg=<?=$pg;?>&codpro=<?= urlencode($dta['codpro']); ?>&opera=edi" class="btn btn-outline-warning text-dark shadow-sm" title="Editar">
                                        <i class="fa-solid fa-pen-to-square"></i> <span class="d-none d-sm-inline">Editar</span>
                                    </a>
                                    <button type="button" class="btn btn-outline-secondary shadow-sm" disabled title="El curso está en uso">
                                        <i class="fa-solid fa-lock"></i> <span class="d-none d-sm-inline">En uso</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                <?php }} ?>
            </tbody>
            <tfoot>
                <tr><th>Cursos utilizados</th></tr>
            </tfoot>
        </table>
    </div>

    <!-- Pestaña 3: Sin utilizar -->
    <div class="tab-pane fade" id="libres" role="tabpanel" aria-labelledby="libres-tab">
        <table id="tblTodos" class="table table-striped table-vid-cards w-100">
            <thead>
                <tr><th>Cursos sin utilizar</th></tr>
            </thead>
            <tbody>
                <?php if($datAll){ foreach($datAll as $dta){
                    $usos = isset($dta['usos']) ? (int)$dta['usos'] : 0;
                    if($usos > 0) continue;
                ?>
                <tr data-order="<?= $dta['codpro']; ?>">
                    <td data-search="<?= $dta['codpro']; ?> <?= $dta['nompro']; ?> <?= $dta['verpro']; ?> <?= $dta['horlpro']; ?>">
                        <div class="card h-100 shadow-sm border-0 vid-card">
                            <div class="card-body p-3 d-flex flex-column justify-content-between">
                                <div>
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <span class="badge bg-danger px-2 py-1">
                                            <i class="fa-solid fa-circle-xmark me-1"></i> Sin usar
                                        </span>
                                        <span class="badge bg-light text-dark px-2 py-1">
                                            <i class="fa-regular fa-clock me-1 text-warning"></i> <?= htmlspecialchars($dta['horlpro']); ?> h
                                        </span>
                                    </div>
                                    <h6 class="fw-bold text-dark mb-2">
                                        <span class="text-danger">#<?= htmlspecialchars($dta['codpro']); ?></span> - <?= htmlspecialchars($dta['nompro']); ?>
                                    </h6>
                                    <div class="p-2 mb-2 rounded-3 bg-light border border-light-subtle d-flex justify-content-between align-items-center small">
                                        <div>
                                            <span class="text-muted d-block small">Versión</span>
                                            <span class="fw-bold text-secondary"><?= htmlspecialchars($dta['verpro']); ?></span>
                                        </div>
                                        <div class="text-end">
                                            <span class="text-muted d-block small">Estado</span>
                                            <span class="fw-bold text-danger">Nunca programado</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer bg-white border-0 pt-0 pb-3 px-3">
                                <hr class="mt-0 mb-3 text-muted opacity-25">
                                <div class="vid-actions-bar">
                                    <a href="home.php?pg=<?=$pg;?>&codpro=<?= urlencode($dta['codpro']); ?>&opera=edi" class="btn btn-outline-warning text-dark shadow-sm" title="Editar">
                                        <i class="fa-solid fa-pen-to-square"></i> <span class="d-none d-sm-inline">Editar</span>
                                    </a>
                                    <a href="home.php?pg=<?=$pg;?>&codpro=<?= urlencode($dta['codpro']); ?>&opera=eli" class="btn btn-outline-danger shadow-sm" title="Eliminar" onclick="return eli(this);">
                                        <i class="fa-solid fa-trash-can"></i> <span class="d-none d-sm-inline">Elim.</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                <?php }} ?>
            </tbody>
            <tfoot>
                <tr><th>Cursos sin utilizar</th></tr>
            </tfoot>
        </table>
    </div>
</div>