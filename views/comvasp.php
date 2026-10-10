
<?php require_once 'controllers/comcasp.php'; ?>
<?php
// Clasificación de datos por estado para contadores y pestañas
$dat = isset($dat) ? $dat : array();
$datPend = array();
$datAprob = array();
$datInsc = array();
$datRech = array();

foreach ($dat as $d) {
    $est = isset($d['estado']) ? $d['estado'] : '';
    if ($est == 'Pendiente') {
        $datPend[] = $d;
    } elseif ($est == 'Aprobado') {
        $datAprob[] = $d;
    } elseif ($est == 'Inscrito') {
        $datInsc[] = $d;
    } elseif ($est == 'Rechazado') {
        $datRech[] = $d;
    }
}

$totAsp = count($dat);
$totPend = count($datPend);
$totAprob = count($datAprob);
$totInsc = count($datInsc);
$totRech = count($datRech);
?>

<div class="conte">
    <?php echo titulo2("<i class='fa fa-solid fa-users'></i> Aspirantes de Complementarias"); ?>

    <!-- Tarjetas KPI de Métricas Globales -->
    <div class="row my-4 g-4">
        <!-- Total Aspirantes -->
        <div class="col-12 col-md">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold small text-muted">Total Aspirantes</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold fs-2 lh-1 text-dark"><?= $totAsp; ?></span>
                            <span class="small text-muted">registrados</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box bg-primary-subtle text-primary">
                        <i class="fa-solid fa-users fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="fw-semibold d-inline-flex align-items-center gap-1 small text-primary">
                        <i class="fa-solid fa-layer-group"></i> Solicitudes
                    </span>
                    <span class="small text-muted">Base Total</span>
                </div>
            </div>
        </div>

        <!-- Aspirantes Aprobados -->
        <div class="col-12 col-md">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold small text-success">Aprobados</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold fs-2 lh-1 text-success"><?= $totAprob; ?></span>
                            <span class="small text-success opacity-75">con aprobación</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box bg-success-subtle text-success">
                        <i class="fa-solid fa-circle-check fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="fw-semibold d-inline-flex align-items-center gap-1 small text-success">
                        <span class="pulse-dot"></span> Habilitados
                    </span>
                    <span class="small text-muted">Validados</span>
                </div>
            </div>
        </div>

        <!-- Aspirantes Inscritos -->
        <div class="col-12 col-md">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold small text-info">Inscritos</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold fs-2 lh-1 text-info"><?= $totInsc; ?></span>
                            <span class="small text-info opacity-75">a ficha</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box bg-info-subtle text-info">
                        <i class="fa-solid fa-user-check fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="fw-semibold d-inline-flex align-items-center gap-1 small text-info">
                        <i class="fa-solid fa-graduation-cap"></i> Matriculados
                    </span>
                    <span class="small text-muted">En programa</span>
                </div>
            </div>
        </div>

        <!-- Aspirantes Pendientes -->
        <div class="col-12 col-md">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold small text-warning">Pendientes</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold fs-2 lh-1 text-warning"><?= $totPend; ?></span>
                            <span class="small text-warning">en validación</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box bg-warning-subtle text-warning">
                        <i class="fa-solid fa-clock fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="fw-semibold d-inline-flex align-items-center gap-1 small text-warning">
                        <i class="fa-solid fa-file-signature"></i> Por revisar
                    </span>
                    <span class="small text-muted">Sin tramitar</span>
                </div>
            </div>
        </div>

        <!-- Aspirantes Rechazados -->
        <div class="col-12 col-md">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold small text-danger">Rechazados</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold fs-2 lh-1 text-danger"><?= $totRech; ?></span>
                            <span class="small text-danger">no aceptados</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box bg-danger-subtle text-danger">
                        <i class="fa-solid fa-ban fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="d-inline-flex align-items-center gap-1 small text-danger">
                        <i class="fa-solid fa-circle-xmark"></i> Sin cupo
                    </span>
                    <span class="fw-semibold small text-muted">Negados</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Formulario de Inserción / Edición -->
    <div class="inser">
        <form id="frmins" class="filter-toolbar-container mb-4" action="home.php?pg=<?=$pg;?>" method="POST">
            <h2><i class="fa-solid fa-user-plus"></i> <?= ($datOne) ? 'Editar' : 'Nuevo'; ?> Aspirante</h2>
            <input type="hidden" name="idasp" id="idasp" value="<?php if($datOne) echo htmlspecialchars($datOne[0]['idasp'] ?? ''); ?>">
            <input type="hidden" name="fecreg" id="fecreg" value="<?php if($datOne && isset($datOne[0]['fecreg'])) echo htmlspecialchars($datOne[0]['fecreg']); ?>">

            <div class="row g-3">
                <div class="form-group col-md-3">
                    <label for="ndocusu" class="form-label fw-semibold">Documento del aspirante *</label>
                    <div class="input-group">
                        <input type="text" name="ndocusu" id="ndocusu" class="form-control" value="<?php if($datOne) echo htmlspecialchars($datOne[0]['ndocusu'] ?? ''); ?>" placeholder="Ej: 1055962023" required autocomplete="off">
                        <button type="button" class="btn btn-outline-secondary" id="btnBuscarDoc" title="Buscar documento">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </button>
                    </div>
                    <div id="doc_feedback" class="mt-1 small"></div>
                </div>
                <div class="form-group col-md-4">
                    <label for="nomusu" class="form-label fw-semibold">Nombre del aspirante *</label>
                    <input type="text" name="nomusu" id="nomusu" class="form-control" value="<?php if($datOne) echo htmlspecialchars($datOne[0]['nomusu'] ?? ''); ?>" placeholder="Nombre completo del aspirante" required>
                </div>
                <div class="form-group col-md-3">
                    <label for="codpro" class="form-label fw-semibold">Complementaria</label>
                    <select name="codpro" id="codpro" class="form-select">
                        <option value="">Seleccione la complementaria</option>
                        <?php if($dcm){
                            foreach ($dcm as $dc) { ?>
                                <option value="<?=$dc['codpro'];?>" <?php if($datOne && $datOne[0]['codpro']==$dc['codpro']) echo " selected "; ?>><?=$dc['nompro'];?></option>
                        <?php }} ?>
                    </select>
                </div>
                <div class="form-group col-md-2">
                    <label for="estado" class="form-label fw-semibold">Estado</label>
                    <select name="estado" id="estado" class="form-select">
                        <option value="Pendiente" <?php if($datOne && $datOne[0]['estado']=='Pendiente') echo " selected "; ?>>Pendiente</option>
                        <option value="Aprobado" <?php if($datOne && $datOne[0]['estado']=='Aprobado') echo " selected "; ?>>Aprobado</option>
                        <option value="Inscrito" <?php if($datOne && $datOne[0]['estado']=='Inscrito') echo " selected "; ?>>Inscrito</option>
                        <option value="Rechazado" <?php if($datOne && $datOne[0]['estado']=='Rechazado') echo " selected "; ?>>Rechazado</option>
                    </select>
                </div>
                <div class="col-12 text-end mt-1">
                    <button type="submit" class="btn btn-success px-4 me-2" name="opera" value="<?php echo ($datOne) ? 'edi' : 'save'; ?>">
                        <i class="fa-solid fa-floppy-disk me-1"></i> <?php echo ($datOne) ? 'Actualizar' : 'Guardar'; ?>
                    </button>
                    <?php if($datOne){ ?>
                        <a href="home.php?pg=<?=$pg;?>&ope=new" class="btn btn-secondary px-4">
                            <i class="fa-solid fa-xmark me-1"></i> Cancelar
                        </a>
                    <?php } ?>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Toolbar de Control y Filtros -->
<div class="filter-toolbar-container">
    <div class="toolbar-left-group">
        <div class="filter-pills-group" id="tabAspirantes" role="tablist">
            <!-- Píldora Pendientes (Activa al ingresar) -->
            <button class="btn-filter-pill active" id="pendientes-tab" data-bs-toggle="tab" data-bs-target="#pendientes" type="button" role="tab" aria-controls="pendientes" aria-selected="true" onclick="filterByTab('pendientes')">
                <span class="pill-dot bg-warning"></span>
                <span>Pendientes</span>
                <span class="pill-count"><?= $totPend; ?></span>
            </button>

            <!-- Píldora Aprobados -->
            <button class="btn-filter-pill" id="aprobados-tab" data-bs-toggle="tab" data-bs-target="#aprobados" type="button" role="tab" aria-controls="aprobados" aria-selected="false" onclick="filterByTab('aprobados')">
                <span class="pill-dot bg-success"></span>
                <span>Aprobados</span>
                <span class="pill-count"><?= $totAprob; ?></span>
            </button>

            <!-- Píldora Inscritos -->
            <button class="btn-filter-pill" id="inscritos-tab" data-bs-toggle="tab" data-bs-target="#inscritos" type="button" role="tab" aria-controls="inscritos" aria-selected="false" onclick="filterByTab('inscritos')">
                <span class="pill-dot bg-info"></span>
                <span>Inscritos</span>
                <span class="pill-count"><?= $totInsc; ?></span>
            </button>

            <!-- Píldora Rechazados -->
            <button class="btn-filter-pill" id="rechazados-tab" data-bs-toggle="tab" data-bs-target="#rechazados" type="button" role="tab" aria-controls="rechazados" aria-selected="false" onclick="filterByTab('rechazados')">
                <span class="pill-dot bg-danger"></span>
                <span>Rechazados</span>
                <span class="pill-count"><?= $totRech; ?></span>
            </button>

            <!-- Píldora Todos -->
            <button class="btn-filter-pill" id="todos-tab" data-bs-toggle="tab" data-bs-target="#todos" type="button" role="tab" aria-controls="todos" aria-selected="false" onclick="filterByTab('todos')">
                <span class="pill-dot bg-primary"></span>
                <span>Todos</span>
                <span class="pill-count"><?= $totAsp; ?></span>
            </button>
        </div>

        <div class="toolbar-divider d-none d-sm-block"></div>

        <!-- Botones de Exportación -->
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
            <input type="text" id="customVideoSearch" placeholder="Buscar aspirante..." onkeyup="syncCustomSearch(this.value)">
        </div>

        <div class="toolbar-secondary-controls">
            <select class="toolbar-select-sort" id="customVideoSort" onchange="syncCustomSort(this.value)">
                <option value="desc">Descendente</option>
                <option value="asc">Ascendente</option>
            </select>

            <div class="toolbar-view-toggle">
                <button type="button" class="toolbar-view-btn active" id="btnViewGrid" title="Vista Cuadrícula" onclick="switchCardView('grid')">
                    <i class="fa-solid fa-grip"></i>
                </button>
                <button type="button" class="toolbar-view-btn" id="btnViewList" title="Vista Tabla/Lista" onclick="switchCardView('list')">
                    <i class="fa-solid fa-bars"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Tab Content -->
<div class="tab-content" id="tabAspirantesContent">
    <!-- Pestaña 1: Pendientes -->
    <div class="tab-pane fade show active" id="pendientes" role="tabpanel" aria-labelledby="pendientes-tab">
        <?php renderTablaAspirantes($datPend, $pg); ?>
    </div>

    <!-- Pestaña 2: Aprobados -->
    <div class="tab-pane fade" id="aprobados" role="tabpanel" aria-labelledby="aprobados-tab">
        <?php renderTablaAspirantes($datAprob, $pg); ?>
    </div>

    <!-- Pestaña 3: Inscritos -->
    <div class="tab-pane fade" id="inscritos" role="tabpanel" aria-labelledby="inscritos-tab">
        <?php renderTablaAspirantes($datInsc, $pg); ?>
    </div>

    <!-- Pestaña 4: Rechazados -->
    <div class="tab-pane fade" id="rechazados" role="tabpanel" aria-labelledby="rechazados-tab">
        <?php renderTablaAspirantes($datRech, $pg); ?>
    </div>

    <!-- Pestaña 5: Todos -->
    <div class="tab-pane fade" id="todos" role="tabpanel" aria-labelledby="todos-tab">
        <?php renderTablaAspirantes($dat, $pg); ?>
    </div>
</div>

<?php
// Función renderizadora para tabla y tarjetas de aspirantes
function renderTablaAspirantes($lista, $pg) {
?>
    <table class="table table-striped table-vid-cards w-100">
        <thead>
            <tr>
                <th>Aspirantes</th>
            </tr>
        </thead>
        <tbody>
            <?php if($lista && count($lista) > 0){ foreach ($lista as $d) {
                $est = $d['estado'];
                $badgeClass = 'bg-success';
                if($est == 'Pendiente') $badgeClass = 'bg-warning text-dark';
                elseif($est == 'Inscrito') $badgeClass = 'bg-info text-dark';
                elseif($est == 'Rechazado') $badgeClass = 'bg-danger';
            ?>
            <tr data-order="<?=$d['idasp'];?>">
                <td data-search="<?=$d['idasp'];?> <?=$d['ndocusu'];?> <?=$d['nomusu'];?> <?=$d['idfic'];?> <?=$d['nomfic'];?> <?=$d['nompro'];?> <?=$d['estado'];?> <?=$d['fecreg'];?>">
                    <div class="card h-100 shadow-sm border-0 vid-card p-3">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="badge bg-secondary">#<?=$d['idasp'];?></span>
                            <span class="badge <?=$badgeClass;?>"><?=htmlspecialchars($est ?? '');?></span>
                        </div>

                        <h5 class="fw-bold text-dark mb-1 text-truncate" title="<?=htmlspecialchars($d['nomusu'] ?? '');?>">
                            <?=htmlspecialchars($d['nomusu'] ?? 'Sin nombre');?>
                        </h5>

                        <div class="text-primary small mb-2 fw-semibold">
                            <i class="fa-solid fa-id-card me-1"></i> Documento: <?=htmlspecialchars((string)($d['ndocusu'] ?? ''));?>
                        </div>

                        <div class="p-2 mb-2 rounded bg-light border small">
                            <div class="d-flex justify-content-between text-muted mb-1">
                                <span><i class="fa-solid fa-layer-group me-1 text-info"></i> <?=htmlspecialchars($d['nompro'] ?? 'Sin complementaria');?></span>
                            </div>
                            <div class="d-flex justify-content-between text-muted">
                                <span><i class="fa-solid fa-hashtag me-1 text-secondary"></i> Ficha: <?= !empty($d['idfic']) ? htmlspecialchars((string)$d['idfic']) : 'N/A'; ?></span>
                                <span><i class="fa-solid fa-book-open me-1 text-success"></i> <?= !empty($d['nomfic']) ? htmlspecialchars($d['nomfic']) : 'Sin ficha'; ?></span>
                            </div>
                        </div>

                        <div class="text-muted small mb-2">
                            <i class="fa-solid fa-calendar-plus me-1"></i> Registro: <?= !empty($d['fecreg']) ? htmlspecialchars($d['fecreg']) : 'No registrada'; ?>
                        </div>

                        <div class="vid-actions-bar mt-2">
                            <a href="home.php?pg=<?=$pg;?>&idasp=<?=$d['idasp'];?>&ope=edi" class="btn btn-outline-warning text-dark shadow-sm" title="Editar">
                                <i class="fa-solid fa-pen-to-square"></i> <span class="d-none d-sm-inline">Editar</span>
                            </a>
                            <a href="home.php?pg=2003&idasp=<?=$d['idasp'];?>&ope=pdf" target="_blank" class="btn btn-outline-danger shadow-sm" title="Hoja imprimible (PDF)">
                                <i class="fa-solid fa-file-pdf"></i> <span class="d-none d-sm-inline">PDF</span>
                            </a>
                            <a href="home.php?pg=<?=$pg;?>&idasp=<?=$d['idasp'];?>&ope=eliminar" class="btn btn-outline-secondary shadow-sm" onclick="return confirm('¿Seguro de eliminar este aspirante?');" title="Eliminar">
                                <i class="fa-solid fa-trash"></i> <span class="d-none d-sm-inline">Elim.</span>
                            </a>
                        </div>
                    </div>
                </td>
            </tr>
            <?php }} ?>
        </tbody>
        <tfoot>
            <tr>
                <th>Aspirantes</th>
            </tr>
        </tfoot>
    </table>
<?php } ?>

<script>
document.addEventListener('DOMContentLoaded', function(){
    const inputDoc = document.getElementById('ndocusu');
    const inputNom = document.getElementById('nomusu');
    const feedback = document.getElementById('doc_feedback');
    const btnBuscar = document.getElementById('btnBuscarDoc');

    function buscarDoc() {
        if (!inputDoc) return;
        const val = inputDoc.value.trim();
        if (val.length < 3) {
            if (feedback) feedback.innerHTML = '';
            return;
        }
        if (feedback) {
            feedback.innerHTML = '<span class="text-muted"><i class="fa-solid fa-spinner fa-spin"></i> Consultando...</span>';
        }

        fetch('controllers/comcasp.php?ajax_user=1&ndoc=' + encodeURIComponent(val))
            .then(r => r.json())
            .then(data => {
                if (data.success && data.exists) {
                    if (inputNom && !inputNom.value) {
                        inputNom.value = data.nomusu;
                    }
                    if (feedback) {
                        feedback.innerHTML = '<span class="text-success fw-semibold"><i class="fa-solid fa-circle-check"></i> ' + data.nomusu + '</span>';
                    }
                } else {
                    if (feedback) {
                        feedback.innerHTML = '<span class="text-primary fw-semibold"><i class="fa-solid fa-user-plus"></i> Usuario nuevo: ingrese el nombre para registrarlo</span>';
                    }
                }
            })
            .catch(() => {
                if (feedback) feedback.innerHTML = '';
            });
    }

    if (inputDoc) {
        inputDoc.addEventListener('blur', buscarDoc);
        inputDoc.addEventListener('change', buscarDoc);
    }
    if (btnBuscar) {
        btnBuscar.addEventListener('click', buscarDoc);
    }
});
</script>