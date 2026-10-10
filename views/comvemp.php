<?php require_once 'controllers/comcemp.php'; ?>
<?php $icono = isset($icono) ? $icono : 'fa-solid fa-industry'; ?>
<?php
// Clasificación de datos por estado para contadores y pestañas
$datAct = array();
$datInact = array();
$datPend = array();

if (isset($dat) && is_array($dat)) {
    foreach ($dat as $d) {
        $est = isset($d['estado']) ? $d['estado'] : '';
        if ($est == 'Activa') {
            $datAct[] = $d;
        } elseif ($est == 'Inactiva') {
            $datInact[] = $d;
        } elseif ($est == 'Pendiente') {
            $datPend[] = $d;
        }
    }
}

$totEmp = count($dat ?? []);
$totAct = count($datAct);
$totInact = count($datInact);
$totPend = count($datPend);
?>

<div class="conte">
    <?php echo titulo2("<i class='".$icono."'></i> Empresas de Complementarias", 1); ?>

    <!-- Tarjetas KPI de Métricas Globables -->
    <div class="row my-4 g-4">
        <!-- Total Empresas -->
        <div class="col-12 col-md-3">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold small text-muted">Total Empresas</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold fs-2 lh-1 text-dark"><?= $totEmp; ?></span>
                            <span class="small text-muted">registradas</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box bg-primary-subtle text-primary">
                        <i class="fa-solid fa-building fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="fw-semibold d-inline-flex align-items-center gap-1 small text-primary">
                        <i class="fa-solid fa-layer-group"></i> Directorio
                    </span>
                    <span class="small text-muted">Base Total</span>
                </div>
            </div>
        </div>

        <!-- Empresas Activas -->
        <div class="col-12 col-md-3">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold small text-success">Empresas Activas</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold fs-2 lh-1 text-success"><?= $totAct; ?></span>
                            <span class="small text-success opacity-75">operativas</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box bg-success-subtle text-success">
                        <i class="fa-solid fa-circle-check fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="fw-semibold d-inline-flex align-items-center gap-1 small text-success">
                        <span class="pulse-dot"></span> Habilitadas
                    </span>
                    <span class="small text-muted">Convenio activo</span>
                </div>
            </div>
        </div>

        <!-- Empresas Inactivas -->
        <div class="col-12 col-md-3">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold small text-danger">Empresas Inactivas</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold fs-2 lh-1 text-danger"><?= $totInact; ?></span>
                            <span class="small text-danger">suspendidas</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box bg-danger-subtle text-danger">
                        <i class="fa-solid fa-circle-minus fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="d-inline-flex align-items-center gap-1 small text-danger">
                        <i class="fa-solid fa-pause"></i> Sin servicio
                    </span>
                    <span class="fw-semibold small text-muted">Pausadas</span>
                </div>
            </div>
        </div>

        <!-- Empresas Pendientes -->
        <div class="col-12 col-md-3">
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
                        <i class="fa-solid fa-file-signature"></i> Por verificar
                    </span>
                    <span class="small text-muted">En proceso</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Formulario de Inserción / Edición -->
    <div class="inser">
        <form id="frmins" class="filter-toolbar-container mb-4" action="home.php?pg=<?=$pg;?>" method="POST" enctype="multipart/form-data">
            <h2><i class="fa-solid fa-building-circle-check"></i> <?= ($datOne) ? 'Editar' : 'Nueva'; ?> Empresa</h2>
            <input type="hidden" name="idemp" id="idemp" value="<?php if($datOne) echo htmlspecialchars($datOne[0]['idemp'] ?? ''); ?>">
            <input type="hidden" name="rutrunt" id="rutrunt" value="<?php if($datOne && isset($datOne[0]['rutrunt'])) echo htmlspecialchars($datOne[0]['rutrunt']); ?>">

            <div class="sec-tit d-flex align-items-center gap-2 mt-2 mb-3 pb-2 border-bottom fw-bold text-success">
                <i class="fa-solid fa-building"></i> <span>1. Información de la Empresa</span>
            </div>

            <div class="row g-3">
                <div class="form-group col-md-3">
                    <label for="arch_runt" class="form-label fw-semibold">Documento RUNT (PDF)</label>
                    <input type="file" name="arch_runt" id="arch_runt" class="form-control" accept=".pdf,application/pdf">
                    <?php if($datOne && $datOne[0]['rutrunt']){ ?>
                        <small class="mt-1 d-block"><a href="<?= $datOne[0]['rutrunt']; ?>" target="_blank" title="Ver documento RUNT" class="text-danger fw-semibold"><i class="fa-solid fa-file-pdf"></i> Ver archivo subido</a></small>
                    <?php } ?>
                </div>

                <div class="form-group col-md-3">
                    <label for="numdocemp" class="form-label fw-semibold">NIT *</label>
                    <input type="text" name="numdocemp" id="numdocemp" maxlength="20" class="form-control" value="<?php if($datOne) echo $datOne[0]['numdocemp']; ?>" placeholder="Ej: 900123456-1" required>
                </div>

                <div class="form-group col-md-6">
                    <label for="nomemp" class="form-label fw-semibold">Nombre o Razón social *</label>
                    <input type="text" name="nomemp" id="nomemp" maxlength="50" class="form-control" value="<?php if($datOne) echo $datOne[0]['nomemp']; ?>" placeholder="Nombre legal de la empresa" required>
                </div>

                <div class="form-group col-md-4">
                    <label for="tipoemp" class="form-label fw-semibold">Tipo de empresa</label>
                    <select name="tipoemp" id="tipoemp" class="form-select">
                        <option value="">Seleccione el tipo de empresa</option>
                        <?php $tipos = array('S.A.S.','S.A.','Ltda.','Unipersonal / Persona natural','Cooperativa','ONG / Fundación','Entidad pública','Otro');
                        foreach($tipos as $tip){ ?>
                            <option value="<?= $tip; ?>" <?php if($datOne && $datOne[0]['tipoemp']==$tip) echo " selected "; ?>><?= $tip; ?></option>
                        <?php } ?>
                    </select>
                </div>

                <div class="form-group col-md-8">
                    <label for="diremp" class="form-label fw-semibold">Dirección</label>
                    <input type="text" name="diremp" id="diremp" maxlength="50" class="form-control" value="<?php if($datOne) echo $datOne[0]['diremp']; ?>" placeholder="Dirección sede principal">
                </div>

                <div class="form-group col-md-3">
                    <label for="mun" class="form-label fw-semibold">Departamento</label>
                    <select class="form-control form-select" onchange="recCiudad(this.value);">
                        <option value="0">Seleccione un departamento</option>
                        <?php if($ddep){ foreach($ddep AS $ub){ ?>
                            <option value="<?=$ub['codubi'];?>" <?php if($datOne && isset($datOne[0]['depubi']) && $datOne[0]['depubi']==$ub['codubi']) echo " selected "; ?>><?=$ub['nomubi'];?></option>
                        <?php }} ?>
                    </select>
                </div>

                <div class="form-group col-md-3">
                    <label for="ubiest" class="form-label fw-semibold">Ciudad / Municipio</label>
                    <div id="reloadMun">
                        <?php if($datOne && $datOne[0]['codubi']){ ?>
                            <select name="ubiest" id="id_estado" class="form-control form-select">
                                <option value="">Seleccione municipio</option>
                                <?php if($dmunc){ foreach($dmunc AS $mu){ ?>
                                    <option value="<?=$mu['codubi'];?>" <?php if($datOne[0]['codubi']==$mu['codubi']) echo " selected "; ?>><?=$mu['nomubi'];?></option>
                                <?php }} ?>
                            </select>
                        <?php }else{ ?>
                            <input type="text" class="form-control" disabled value="Primero seleccione departamento">
                            <input type="hidden" name="ubiest" id="ubiest" value="">
                        <?php } ?>
                    </div>
                </div>

                <div class="form-group col-md-3">
                    <label for="telemp" class="form-label fw-semibold">Teléfono</label>
                    <input type="text" name="telemp" id="telemp" maxlength="15" class="form-control" value="<?php if($datOne) echo $datOne[0]['telemp']; ?>" placeholder="Teléfono de contacto">
                </div>

                <div class="form-group col-md-3">
                    <label for="coremp" class="form-label fw-semibold">Correo electrónico</label>
                    <input type="email" name="coremp" id="coremp" maxlength="70" class="form-control" value="<?php if($datOne) echo $datOne[0]['coremp']; ?>" placeholder="correo@empresa.com">
                </div>

                <div class="form-group col-md-6">
                    <label for="webbemp" class="form-label fw-semibold">Página web</label>
                    <input type="text" name="webbemp" id="webbemp" maxlength="255" class="form-control" value="<?php if($datOne) echo $datOne[0]['webbemp']; ?>" placeholder="https://www.empresa.com">
                </div>

                <div class="form-group col-md-3">
                    <label for="reprleg" class="form-label fw-semibold">Representante legal</label>
                    <input type="text" name="reprleg" id="reprleg" maxlength="70" class="form-control" value="<?php if($datOne) echo $datOne[0]['reprleg']; ?>" placeholder="Nombre representante">
                </div>

                <div class="form-group col-md-3">
                    <label for="estado" class="form-label fw-semibold">Estado de la empresa</label>
                    <select name="estado" id="estado" class="form-select">
                        <option value="Activa" <?php if($datOne && $datOne[0]['estado']=='Activa') echo " selected "; ?>>Activa</option>
                        <option value="Inactiva" <?php if($datOne && $datOne[0]['estado']=='Inactiva') echo " selected "; ?>>Inactiva</option>
                        <option value="Pendiente" <?php if($datOne && $datOne[0]['estado']=='Pendiente') echo " selected "; ?>>Pendiente</option>
                    </select>
                </div>
            </div>

            <div class="sec-tit d-flex align-items-center gap-2 mt-4 mb-3 pb-2 border-bottom fw-bold text-success">
                <i class="fa-solid fa-graduation-cap"></i> <span>2. Información de Complementarias y Oferta</span>
            </div>

            <div class="row g-3">
                <div class="form-group col-md-4">
                    <label for="tipcur" class="form-label fw-semibold">Cursos o complementarias</label>
                    <input type="text" name="tipcur" id="tipcur" maxlength="255" class="form-control" value="<?php if($datOne) echo $datOne[0]['tipcur']; ?>" placeholder="Tipos de cursos ofrecidos">
                </div>

                <div class="form-group col-md-3">
                    <label for="idare" class="form-label fw-semibold">Área de formación</label>
                    <select name="idare" id="idare" class="form-select">
                        <option value="">Seleccione el área</option>
                        <?php if($dare){ foreach($dare AS $ar){ ?>
                            <option value="<?=$ar['idare'];?>" <?php if($datOne && $datOne[0]['idare']==$ar['idare']) echo " selected "; ?>><?=$ar['nomare'];?></option>
                        <?php }} ?>
                    </select>
                </div>

                <div class="form-group col-md-3">
                    <label for="modalidad" class="form-label fw-semibold">Modalidad</label>
                    <select name="modalidad" id="modalidad" class="form-select">
                        <option value="">Seleccione la modalidad</option>
                        <?php if($dmod){ foreach($dmod AS $mv){ ?>
                            <option value="<?=$mv['idval'];?>" <?php if($datOne && $datOne[0]['modalidad']==$mv['idval']) echo " selected "; ?>><?=$mv['nomval'];?></option>
                        <?php }} ?>
                    </select>
                </div>

                <div class="form-group col-md-2">
                    <label for="cupemp" class="form-label fw-semibold">Cupos disponibles</label>
                    <input type="number" name="cupemp" id="cupemp" min="1" max="999999999" class="form-control" value="<?php if($datOne) echo $datOne[0]['cupemp']; ?>" placeholder="Cupos">
                </div>

                <div class="form-group col-md-3">
                    <label for="duraemp" class="form-label fw-semibold">Duración</label>
                    <input type="text" name="duraemp" id="duraemp" maxlength="60" class="form-control" value="<?php if($datOne) echo $datOne[0]['duraemp']; ?>" placeholder="Ej: 40 Horas">
                </div>

                <div class="form-group col-md-4">
                    <label for="nomconemp" class="form-label fw-semibold">Persona de contacto</label>
                    <input type="text" name="nomconemp" id="nomconemp" maxlength="50" class="form-control" value="<?php if($datOne) echo $datOne[0]['nomconemp']; ?>" placeholder="Nombre de contacto">
                </div>

                <div class="form-group col-md-5">
                    <label for="infcon" class="form-label fw-semibold">Información de contacto</label>
                    <input type="text" name="infcon" id="infcon" maxlength="255" class="form-control" value="<?php if($datOne) echo $datOne[0]['infcon']; ?>" placeholder="Ej: 3101234567 - contacto@empresa.com">
                </div>

                <div class="form-group col-md-12">
                    <label for="desemp" class="form-label fw-semibold">Descripción u oferta</label>
                    <textarea name="desemp" id="desemp" rows="2" class="form-control" placeholder="Oferta de cursos, requisitos, cobertura, etc."><?php if($datOne) echo $datOne[0]['desemp']; ?></textarea>
                </div>

                <div class="col-12 text-end mt-4">
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
        <div class="filter-pills-group" id="tabEmpresas" role="tablist">
            <!-- Píldora Activas (Activa por defecto) -->
            <button class="btn-filter-pill active" id="activas-tab" data-bs-toggle="tab" data-bs-target="#activas" type="button" role="tab" aria-controls="activas" aria-selected="true" onclick="filterByTab('activas')">
                <span class="pill-dot bg-success"></span>
                <span>Activas</span>
                <span class="pill-count"><?= $totAct; ?></span>
            </button>

            <!-- Píldora Inactivas -->
            <button class="btn-filter-pill" id="inactivas-tab" data-bs-toggle="tab" data-bs-target="#inactivas" type="button" role="tab" aria-controls="inactivas" aria-selected="false" onclick="filterByTab('inactivas')">
                <span class="pill-dot bg-danger"></span>
                <span>Inactivas</span>
                <span class="pill-count"><?= $totInact; ?></span>
            </button>

            <!-- Píldora Pendientes -->
            <button class="btn-filter-pill" id="pendientes-tab" data-bs-toggle="tab" data-bs-target="#pendientes" type="button" role="tab" aria-controls="pendientes" aria-selected="false" onclick="filterByTab('pendientes')">
                <span class="pill-dot bg-warning"></span>
                <span>Pendientes</span>
                <span class="pill-count"><?= $totPend; ?></span>
            </button>

            <!-- Píldora Todas -->
            <button class="btn-filter-pill" id="todos-tab" data-bs-toggle="tab" data-bs-target="#todos" type="button" role="tab" aria-controls="todos" aria-selected="false" onclick="filterByTab('todos')">
                <span class="pill-dot bg-primary"></span>
                <span>Todas</span>
                <span class="pill-count"><?= $totEmp; ?></span>
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
            <input type="text" id="customVideoSearch" placeholder="Buscar empresa..." onkeyup="syncCustomSearch(this.value)">
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
<div class="tab-content" id="tabEmpresasContent">
    <!-- Pestaña 1: Activas -->
    <div class="tab-pane fade show active" id="activas" role="tabpanel" aria-labelledby="activas-tab">
        <?php renderTablaEmpresas($datAct, $pg); ?>
    </div>

    <!-- Pestaña 2: Inactivas -->
    <div class="tab-pane fade" id="inactivas" role="tabpanel" aria-labelledby="inactivas-tab">
        <?php renderTablaEmpresas($datInact, $pg); ?>
    </div>

    <!-- Pestaña 3: Pendientes -->
    <div class="tab-pane fade" id="pendientes" role="tabpanel" aria-labelledby="pendientes-tab">
        <?php renderTablaEmpresas($datPend, $pg); ?>
    </div>

    <!-- Pestaña 4: Todas -->
    <div class="tab-pane fade" id="todos" role="tabpanel" aria-labelledby="todos-tab">
        <?php renderTablaEmpresas($dat ?? [], $pg); ?>
    </div>
</div>

<?php
// Función renderizadora para tabla y tarjetas de empresas
function renderTablaEmpresas($lista, $pg) {
?>
    <table class="table table-striped table-vid-cards w-100">
        <thead>
            <tr>
                <th>Empresas</th>
            </tr>
        </thead>
        <tbody>
            <?php if($lista && count($lista) > 0){ foreach ($lista as $d) {
                $est = $d['estado'];
                $badgeClass = 'bg-success';
                if($est == 'Inactiva') $badgeClass = 'bg-danger';
                elseif($est == 'Pendiente') $badgeClass = 'bg-warning text-dark';
            ?>
            <tr data-order="<?=$d['idemp'];?>">
                <td data-search="<?=$d['idemp'];?> <?=$d['numdocemp'];?> <?=$d['nomemp'];?> <?=$d['tipoemp'];?> <?=$d['nommun'];?> <?=$d['nomare'];?> <?=$d['estado'];?>">
                    <div class="card h-100 shadow-sm border-0 vid-card p-3">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="badge bg-secondary">#<?=$d['idemp'];?></span>
                            <div class="d-flex gap-1">
                                <?php if(!empty($d['rutrunt'])): ?>
                                    <a href="<?=$d['rutrunt'];?>" target="_blank" class="badge bg-danger text-decoration-none" title="Documento RUNT">
                                        <i class="fa-solid fa-file-pdf"></i> RUNT
                                    </a>
                                <?php endif; ?>
                                <span class="badge <?=$badgeClass;?>"><?=$est;?></span>
                            </div>
                        </div>

                        <h5 class="fw-bold text-dark mb-1 text-truncate" title="<?=htmlspecialchars($d['nomemp'] ?? '');?>">
                            <?=htmlspecialchars($d['nomemp'] ?? '');?>
                        </h5>

                        <div class="text-primary small mb-2 fw-semibold">
                            <i class="fa-solid fa-id-card me-1"></i> NIT: <?=htmlspecialchars($d['numdocemp'] ?? '');?>
                            <?php if(!empty($d['tipoemp'])): ?>
                                <span class="text-muted"> (<?=htmlspecialchars($d['tipoemp']);?>)</span>
                            <?php endif; ?>
                        </div>

                        <div class="p-2 mb-2 rounded bg-light border small">
                            <div class="d-flex justify-content-between text-muted mb-1">
                                <span><i class="fa-solid fa-location-dot me-1 text-danger"></i> <?= !empty($d['nommun']) ? htmlspecialchars($d['nommun']) : 'N/D'; ?></span>
                                <span><i class="fa-solid fa-layer-group me-1 text-info"></i> <?= !empty($d['nomare']) ? htmlspecialchars($d['nomare']) : 'N/D'; ?></span>
                            </div>
                            <?php if(!empty($d['nommodal']) || !empty($d['cupemp'])): ?>
                            <div class="d-flex justify-content-between text-muted">
                                <span><i class="fa-solid fa-laptop-house me-1 text-success"></i> <?= !empty($d['nommodal']) ? htmlspecialchars($d['nommodal']) : 'N/D'; ?></span>
                                <span><i class="fa-solid fa-users-between-lines me-1 text-warning"></i> Cupos: <?= !empty($d['cupemp']) ? htmlspecialchars((string)$d['cupemp']) : '0'; ?></span>
                            </div>
                            <?php endif; ?>
                        </div>

                        <?php if(!empty($d['telemp']) || !empty($d['coremp'])): ?>
                        <div class="text-muted small mb-2 text-truncate">
                            <i class="fa-solid fa-envelope me-1"></i> <?= !empty($d['coremp']) ? htmlspecialchars($d['coremp']) : 'No registrado'; ?>
                            <?php if(!empty($d['telemp'])): ?>
                                | <i class="fa-solid fa-phone me-1"></i> <?=htmlspecialchars($d['telemp']);?>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>

                        <div class="vid-actions-bar mt-2">
                            <a href="home.php?pg=<?=$pg;?>&idemp=<?=$d['idemp'];?>&ope=edi" class="btn btn-outline-warning text-dark shadow-sm" title="Editar">
                                <i class="fa-solid fa-pen-to-square"></i> <span class="d-none d-sm-inline">Editar</span>
                            </a>
                            <?php if(!empty($d['rutrunt'])): ?>
                                <a href="<?=$d['rutrunt'];?>" target="_blank" class="btn btn-outline-danger shadow-sm" title="Ver RUNT PDF">
                                    <i class="fa-solid fa-file-pdf"></i> <span class="d-none d-sm-inline">RUNT</span>
                                </a>
                            <?php endif; ?>
                            <a href="home.php?pg=<?=$pg;?>&idemp=<?=$d['idemp'];?>&ope=eliminar" class="btn btn-outline-secondary shadow-sm" onclick="return confirm('¿Seguro de eliminar esta empresa?');" title="Eliminar">
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
                <th>Empresas</th>
            </tr>
        </tfoot>
    </table>
<?php } ?>