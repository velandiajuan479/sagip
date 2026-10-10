<?php
if (isset($_REQUEST['ope']) && !isset($_REQUEST['opera'])) {
    $_REQUEST['opera'] = $_REQUEST['ope'];
    $_GET['opera']     = $_REQUEST['ope'];
    $_POST['opera']    = $_REQUEST['ope'];
}
require_once("controllers/concpas.php");
$dtOn = $datOne;
?>

<div class="conte">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <?php echo titulo2("<i class='".$icono."'></i> Pasos del Proceso", 1); ?>
        <a href="home.php?pg=2008" class="btn btn-outline-secondary btn-sm px-3 shadow-sm">
            <i class="fa-solid fa-arrow-left me-1"></i> Ir a Flujos
        </a>
    </div>

    <!-- 1. KPI Cards -->
    <div class="row my-4 g-4">

        <!-- Total Pasos -->
        <div class="col-12 col-md-4">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold small text-secondary">
                            <?= $idflu ? 'Pasos de este Flujo' : 'Total en Sistema'; ?>
                        </span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold fs-2 lh-1 text-dark">
                                <?= $idflu ? ($metricsPas['pasos_flujo_actual'] ?? count($datAll ?? [])) : ($metricsPas['total_pasos'] ?? count($datAll ?? [])); ?>
                            </span>
                            <span class="small text-secondary">pasos</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box bg-primary-subtle text-primary">
                        <i class="fa-solid fa-list-ol fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="fw-semibold d-inline-flex align-items-center gap-1 small text-primary">
                        <i class="fa-solid fa-arrow-trend-up"></i> Registrados
                    </span>
                    <span class="small text-secondary"><?= $idflu ? 'En este proceso' : 'Disponibles: 100%'; ?></span>
                </div>
            </div>
        </div>

        <!-- Flujos con Pasos -->
        <div class="col-12 col-md-4">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold small text-success">Flujos con Pasos</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold fs-2 lh-1 text-success"><?= $metricsPas['flujos_con_pasos'] ?? 0; ?></span>
                            <span class="small text-success opacity-75">procesos</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box bg-success-subtle text-success">
                        <i class="fa-solid fa-diagram-project fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="fw-semibold d-inline-flex align-items-center gap-2 small text-success">
                        <span class="pulse-dot"></span> Procesos activos
                    </span>
                    <span class="small text-secondary">Con etapas</span>
                </div>
            </div>
        </div>

        <!-- Perfiles Responsables -->
        <div class="col-12 col-md-4">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold small text-danger">
                            <?= $idflu ? 'Perfiles en este Flujo' : 'Perfiles Involucrados'; ?>
                        </span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold fs-2 lh-1 text-dark">
                                <?= $idflu ? ($metricsPas['perfiles_flujo_actual'] ?? 0) : ($metricsPas['perfiles_unicos'] ?? 0); ?>
                            </span>
                            <span class="small text-secondary">roles</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box bg-danger-subtle text-danger">
                        <i class="fa-solid fa-users-gear fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="d-inline-flex align-items-center gap-1 small text-secondary">
                        <i class="fa-solid fa-user-tag"></i>
                        <?= $idflu ? 'En este proceso' : 'Roles responsables'; ?>
                    </span>
                    <span class="fw-semibold small text-primary">Activos</span>
                </div>
            </div>
        </div>

    </div>

    <!-- 2. Formulario de Registro / Edición -->
    <div class="inser">
        <form id="frmins" class="filter-toolbar-container" action="home.php?pg=<?= $pg; ?><?= $idflu ? '&idflu=' . $idflu : ''; ?>" method="POST">
            <h2><i class="fa-solid fa-file-circle-plus"></i> <?= $dtOn ? 'Editar paso de proceso' : 'Nuevo paso de proceso'; ?></h2>
            <div class="row">
                <div class="form-group col-md-4">
                    <label for="idflu">Flujo</label>
                    <select name="idflu" id="idflu" class="form-control form-select" required
                            onchange="if(this.value && !<?= $dtOn ? 'true' : 'false'; ?>){ location.href='home.php?pg=<?=$pg;?>&idflu='+encodeURIComponent(this.value); }">
                        <option value="">Seleccione...</option>
                        <?php if($datFlu){ foreach($datFlu as $dt){ ?>
                            <option value="<?= $dt['idflu']; ?>"
                                <?= (($dtOn && $dtOn[0]['idflu'] == $dt['idflu']) || (!$dtOn && $idflu && $idflu == $dt['idflu'])) ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($dt['nomflu']); ?> (<?= $dt['cant_pasos']; ?> <?= $dt['cant_pasos'] == 1 ? 'paso' : 'pasos'; ?>)<?= (isset($dt['fluacti']) && $dt['fluacti'] != 1) ? ' (Inactivo)' : ''; ?>
                            </option>
                        <?php }} ?>
                    </select>
                </div>

                <div class="form-group col-md-4">
                    <label for="descpas">Descripción</label>
                    <input type="text" class="form-control" name="descpas" id="descpas"
                           value="<?php if ($dtOn) echo htmlspecialchars($dtOn[0]['descpas']); ?>" required>
                </div>

                <div class="form-group col-md-2">
                    <label for="idper">Responsable</label>
                    <select name="idper" id="idper" class="form-control form-select" required>
                        <option value="">Seleccione...</option>
                        <?php if($datPef){ foreach($datPef as $dt){ ?>
                            <option value="<?= $dt['idper']; ?>" <?= ($dtOn && $dtOn[0]['idper'] == $dt['idper']) ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($dt['nomper']); ?>
                            </option>
                        <?php }} ?>
                    </select>
                </div>

                <div class="form-group col-md-2">
                    <br>
                    <input type="hidden" name="idpas" value="<?php if ($dtOn) echo $dtOn[0]['idpas']; ?>">
                    <input type="hidden" name="numpas" id="numpas" value="<?= $dtOn ? $dtOn[0]['numpas'] : $nextNum; ?>">
                    <?php if($dtOn): ?><input type="hidden" name="es_edicion" value="1"><?php endif; ?>
                    <input type="hidden" name="ope" value="save">
                    <input type="hidden" name="opera" value="save">
                    <input class="btn btn-success w-100" type="submit" value="<?= $dtOn ? 'Guardar' : 'Enviar'; ?>">
                    <br><br>
                </div>

                <?php if($dtOn): ?>
                <div class="col-12 mt-2">
                    <small class="text-muted me-3">
                        <i class="fa-solid fa-list-ol me-1"></i> Secuencia: <strong>#<?= $dtOn[0]['numpas']; ?></strong>
                    </small>
                    <a href="home.php?pg=<?= $pg; ?><?= $idflu ? '&idflu=' . $idflu : ''; ?>" class="btn btn-secondary btn-sm">
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
        <div class="filter-pills-group" id="tabPasos" role="tablist">
            <button class="btn-filter-pill active" id="todos-tab" data-bs-toggle="tab"
                    data-bs-target="#todos" type="button" role="tab" aria-selected="true" onclick="filterByTab('todos')">
                <span class="pill-dot bg-primary"></span>
                <span><?= $idflu ? 'Pasos' : 'Todos'; ?></span>
                <span class="pill-count"><?= count($datAll ?? []); ?></span>
            </button>
        </div>

        <div class="toolbar-divider d-none d-sm-block"></div>

        <!-- Selector de flujo integrado en toolbar -->
        <div class="d-flex align-items-center gap-2">
            <select id="filtroFlujo" class="toolbar-select-sort w-auto"
                    onchange="location.href=this.value ? 'home.php?pg=<?=$pg;?>&idflu='+encodeURIComponent(this.value) : 'home.php?pg=<?=$pg;?>'">
                <option value="">Todos los flujos</option>
                <?php if($datFlu){ foreach($datFlu as $fl){ ?>
                    <option value="<?= $fl['idflu']; ?>" <?= ($idflu && $idflu == $fl['idflu']) ? 'selected' : ''; ?>>
                        <?= htmlspecialchars($fl['nomflu']); ?> (<?= $fl['cant_pasos']; ?>)
                    </option>
                <?php }} ?>
            </select>
            <?php if($idflu && !empty($nomFluActual)): ?>
                <a href="home.php?pg=<?= $pg; ?>" class="toolbar-export-btn text-danger" title="Quitar filtro de flujo">
                    <i class="fa-solid fa-xmark"></i>
                </a>
            <?php endif; ?>
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

<!-- Tabla de Pasos con DataTables y diseño cards -->
<div class="tab-content" id="tabPasosContent">
    <div class="tab-pane fade show active" id="todos" role="tabpanel" aria-labelledby="todos-tab">
        <table id="example" class="table table-striped table-vid-cards w-100">
            <thead>
                <tr><th><?= $idflu ? 'Pasos de: ' . htmlspecialchars($nomFluActual ?? '') : 'Pasos Registrados'; ?></th></tr>
            </thead>
            <tbody>
                <?php if ($datAll) { foreach ($datAll as $p) { ?>
                <tr data-order="<?= intval($p['numpas']); ?>">
                    <td data-search="<?= $p['numpas']; ?> <?= htmlspecialchars($p['descpas']); ?> <?= htmlspecialchars($p['nomflu']); ?> <?= htmlspecialchars($p['nomper']); ?>">
                        <div class="card h-100 shadow-sm border-0 vid-card">
                            <div class="card-body p-3 d-flex flex-column justify-content-between">
                                <div>
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <span class="badge bg-primary rounded-pill px-3 py-1 shadow-sm">
                                            <i class="fa-solid fa-shoe-prints me-1"></i> Secuencia #<?= $p['numpas']; ?>
                                        </span>
                                        <a href="home.php?pg=<?= $pg; ?>&idflu=<?= $p['idflu']; ?>"
                                           class="badge bg-light text-dark border text-decoration-none shadow-sm">
                                            <i class="fa-solid fa-diagram-project me-1 text-primary"></i> <?= htmlspecialchars($p['nomflu']); ?>
                                        </a>
                                    </div>
                                    <h6 class="fw-bold text-dark mb-2 text-truncate fs-6" title="<?= htmlspecialchars($p['descpas']); ?>">
                                        <?= htmlspecialchars($p['descpas']); ?>
                                    </h6>
                                    <div class="d-flex justify-content-between align-items-center mt-2">
                                        <span class="badge bg-light text-secondary border px-2 py-1">
                                            <i class="fa-solid fa-hashtag me-1"></i>ID: #<?= $p['idpas']; ?>
                                        </span>
                                        <span class="badge bg-light text-dark border px-2 py-1">
                                            <i class="fa-solid fa-user-gear me-1 text-success"></i> <?= htmlspecialchars($p['nomper']); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer bg-white border-0 pt-0 pb-3 px-3">
                                <hr class="mt-0 mb-3 text-muted opacity-25">
                                <div class="vid-actions-bar">
                                    <a href="home.php?pg=<?= $pg; ?>&idpas=<?= $p['idpas']; ?><?= $idflu ? '&idflu=' . $idflu : ''; ?>&ope=edi"
                                       class="btn btn-outline-warning text-dark shadow-sm" title="Editar">
                                        <i class="fa-solid fa-pen-to-square"></i> <span class="d-none d-sm-inline">Editar</span>
                                    </a>
                                    <a href="home.php?pg=<?= $pg; ?>&idpas=<?= $p['idpas']; ?><?= $idflu ? '&idflu=' . $idflu : ''; ?>&ope=eli"
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
            <tfoot>
                <tr><th><?= $idflu ? 'Pasos del Flujo' : 'Pasos Registrados'; ?></th></tr>
            </tfoot>
        </table>
    </div>
</div>
