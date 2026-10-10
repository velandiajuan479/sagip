<?php require_once 'controllers/horcsesins.php'; ?>

<div class="conte">
    <?php echo titulo2("<i class='" . $icono . "'></i> Sesión creación instructor", 1); ?>
    <?php require_once("views/vscopehya.php"); ?>

    <div class="row my-4 g-4">
        <!-- Total Sesiones -->
        <div class="col-12 col-md-4">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #3e4a3d;">Total en Sistema</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #0b1c30;"><?= $totSes; ?></span>
                            <span style="font-size: 13px; color: #3e4a3d;">sesiones</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box" style="background-color: #b5dcfe; color: #3c627f;">
                        <i class="fa-solid fa-layer-group fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 11px; color: #3c627f;">
                        <i class="fa-solid fa-arrow-trend-up" style="font-size: 12px;"></i> Registradas
                    </span>
                    <span style="font-size: 11px; color: #3e4a3d;">Disponibles: 100%</span>
                </div>
            </div>
        </div>

        <!-- Sesiones Vigentes -->
        <div class="col-12 col-md-4">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #006b29;">Sesiones Vigentes</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #006b29;"><?= $totAct; ?></span>
                            <span style="font-size: 13px; color: rgba(0, 107, 41, 0.8);">futuras / hoy</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box" style="background-color: #7afd88; color: #006b24;">
                        <i class="fa-solid fa-calendar-check fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="fw-semibold d-inline-flex align-items-center gap-2" style="font-size: 11px; color: #006b29;">
                        <span class="pulse-dot"></span> En vigencia
                    </span>
                    <span style="font-size: 11px; color: #3e4a3d;">Fecses >= hoy</span>
                </div>
            </div>
        </div>

        <!-- Sesiones no realizadas -->
        <div class="col-12 col-md-4">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #ba1a1a;">Sesiones no realizadas</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #0b1c30;"><?= $totInact; ?></span>
                            <span style="font-size: 13px; color: #3e4a3d;">vencidas</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box" style="background-color: #ffdad6; color: #ba1a1a;">
                        <i class="fa-solid fa-calendar-xmark fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="d-inline-flex align-items-center gap-1" style="font-size: 11px; color: #3e4a3d;">
                        <i class="fa-solid fa-clock-rotate-left" style="font-size: 12px;"></i> Fuera de vigencia
                    </span>
                    <span class="fw-semibold" style="font-size: 11px; color: #3c627f;">No realizadas</span>
                </div>
            </div>
        </div>
    </div>

    <div class="inser">
        <form id="frmins" class="filter-toolbar-container" action="home.php?pg=<?= $pg; ?>" method="POST" style="display: inherit;">
            <h2><i class="fa-solid fa-calendar-plus"></i> Nueva sesión</h2>
            <div class="row">
                <div class="form-group col-md-4">
                    <label for="idage">Agenda</label>
                    <select name="idage" id="idage" class="form-select" required>
                        <option value="">Seleccione agenda</option>
                        <?php if ($dage) { foreach ($dage as $de) {
                            $nomAgenda = trim(($de['fas'] ?? '') . ' ' . ($de['actpro'] ?? ''));
                            if($nomAgenda === '') $nomAgenda = 'Agenda';
                        ?>
                            <option value="<?= $de['idage']; ?>" <?php if ($dtOne && $dtOne[0]['idage'] == $de['idage']) echo " selected "; ?>><?= $nomAgenda; ?> <?= $de['idfic'] ? '(' . $de['idfic'] . ')' : ''; ?></option>
                        <?php } } ?>
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <label for="fecses">Fecha de sesión</label>
                    <input type="date" name="fecses" id="fecses" class="form-control" required value="<?php if ($dtOne) echo $dtOne[0]['fecses']; else echo $fechini; ?>">
                </div>
                <div class="form-group col-md-4">
                    <br>
                    <input type="hidden" name="ope" value="save">
                    <input type="hidden" name="idses" value="<?php if ($dtOne && isset($dtOne[0]['idses'])) echo $dtOne[0]['idses']; ?>">
                    <input class="btn btn-success" style="width: 100%;" type="submit" value="Enviar">
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Toolbar de Control y Filtros -->
<div class="filter-toolbar-container">
    <!-- Izquierda: Píldoras de Filtro de Estado y Exportación -->
    <div class="toolbar-left-group">
        <div class="filter-pills-group" id="tabVideos" role="tablist">
            <!-- Píldora Vigentes -->
            <button class="btn-filter-pill active" id="activos-tab" data-bs-toggle="tab" data-bs-target="#activos" type="button" role="tab" aria-controls="activos" aria-selected="true" onclick="filterByTab('activos')">
                <span class="pill-dot" style="background-color: #006b29;"></span>
                <span>Vigentes</span>
                <span class="pill-count"><?= count($datAct ?? []); ?></span>
            </button>

            <!-- Píldora No realizadas -->
            <button class="btn-filter-pill" id="inactivos-tab" data-bs-toggle="tab" data-bs-target="#inactivos" type="button" role="tab" aria-controls="inactivos" aria-selected="false" onclick="filterByTab('inactivos')">
                <span class="pill-dot" style="background-color: #ba1a1a;"></span>
                <span>No realizadas</span>
                <span class="pill-count"><?= count($datInact ?? []); ?></span>
            </button>

            <!-- Píldora Todos -->
            <button class="btn-filter-pill" id="todos-tab" data-bs-toggle="tab" data-bs-target="#todos" type="button" role="tab" aria-controls="todos" aria-selected="false" onclick="filterByTab('todos')">
                <span class="pill-dot" style="background-color: #3c627f;"></span>
                <span>Todas</span>
                <span class="pill-count"><?= $totSes; ?></span>
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
            <input type="text" id="customVideoSearch" placeholder="Buscar" onkeyup="syncCustomSearch(this.value)">
        </div>

        <!-- Controles Secundarios: Selector de Orden y Conmutador de Vistas -->
        <div class="toolbar-secondary-controls">
            <!-- Selector de ordenamiento -->
            <select class="toolbar-select-sort" id="customVideoSort" onchange="syncCustomSort(this.value)">
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

<div class="tab-content" id="tabVideosContent">
    <!-- Pestaña 1: Vigentes -->
    <div class="tab-pane fade show active" id="activos" role="tabpanel" aria-labelledby="activos-tab">
        <div class="table-responsive">
        <table id="example" class="table table-striped table-hover align-middle" style="width:100%">
            <thead class="table-light">
                <tr>
                    <th style="width:60px;">Id</th>
                    <th style="width:80px;">Agenda</th>
                    <th style="width:110px;">Ficha</th>
                    <th>Fase</th>
                    <th>Actividad</th>
                    <th style="width:110px;">Fecha</th>
                    <th style="width:160px;" class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if($datAct){ foreach ($datAct as $dt) {
                    $nomAgAct = trim(($dt["fas"] ?? '') . ' ' . ($dt["actpro"] ?? ''));
                    if($nomAgAct==='') $nomAgAct='Agenda';
                ?>
                <tr>
                    <td><?=$dt["idses"];?></td>
                    <td><?=$nomAgAct;?></td>
                    <td><?=$dt["idfic"] ?? 'N/A';?></td>
                    <td><?=$dt["fas"] ?? 'N/A';?></td>
                    <td><?=$dt["actpro"] ?? $dt["idage"];?></td>
                    <td><?=$dt["fecses"];?></td>
                    <td class="text-center">
                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#mod<?=$dt["idses"];?>" title="Ver"><i class="fa-solid fa-eye"></i></button>
                            <a href="home.php?pg=<?=$pg;?>&idses=<?=$dt["idses"];?>&ope=edit" class="btn btn-outline-warning" title="Editar"><i class="fa-solid fa-pen-to-square"></i></a>
                            <a href="home.php?pg=<?=$pg;?>&idses=<?=$dt["idses"];?>&ope=del" class="btn btn-outline-danger" title="Eliminar" onclick="return eli(this);"><i class="fa-solid fa-trash-can"></i></a>
                        </div>
                        <div class="modal fade" id="mod<?=$dt["idses"];?>" tabindex="-1" aria-labelledby="modLabel<?=$dt["idses"];?>" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                <div class="modal-content shadow-lg border-0" style="border-radius: 16px; overflow: hidden;">
                                    <div class="modal-header bg-dark text-white border-0 px-4 py-3">
                                        <h5 class="modal-title fs-5 d-flex align-items-center gap-2" id="modLabel<?=$dt["idses"];?>">
                                            <i class="fa-solid fa-calendar-days text-primary"></i>
                                            <span class="text-truncate" style="max-width: 650px;">Sesión #<?=$dt["idses"];?> - <?=$dt["fas"] ?? 'Agenda ' . $dt["idage"];?></span>
                                        </h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <strong>Agenda:</strong> <?=$dt["idage"];?><br>
                                                <strong>Fase:</strong> <?=$dt["fas"] ?? 'N/A';?><br>
                                                <strong>Actividad:</strong> <?=$dt["actpro"] ?? 'N/A';?>
                                            </div>
                                            <div class="col-md-6">
                                                <strong>Ficha:</strong> <?=$dt["idfic"] ?? 'N/A';?><br>
                                                <strong>Fecha sesión:</strong> <?=$dt["fecses"];?><br>
                                                <strong>Id Sesión:</strong> <?=$dt["idses"];?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer bg-light border-0 px-4 py-3 d-flex justify-content-between align-items-center">
                                        <div class="text-muted small">
                                            <i class="fa-regular fa-calendar me-1"></i><?=$dt["fecses"];?> | <i class="fa-solid fa-hashtag me-1"></i>Agenda: <?=$dt["idage"];?>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <a href="home.php?pg=<?=$pg;?>&idses=<?=$dt["idses"];?>&ope=edit" class="btn btn-warning btn-sm">
                                                <i class="fa-solid fa-pen-to-square me-1"></i> Editar
                                            </a>
                                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                <?php }} ?>
            </tbody>
        </table>
        </div>
    </div>

    <!-- Pestaña 2: No realizadas -->
    <div class="tab-pane fade" id="inactivos" role="tabpanel" aria-labelledby="inactivos-tab">
        <div class="table-responsive">
        <table id="myt" class="table table-striped table-hover align-middle" style="width:100%">
            <thead class="table-light">
                <tr>
                    <th style="width:60px;">Id</th>
                    <th style="width:80px;">Agenda</th>
                    <th style="width:110px;">Ficha</th>
                    <th>Fase</th>
                    <th>Actividad</th>
                    <th style="width:110px;">Fecha</th>
                    <th style="width:160px;" class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if($datInact){ foreach ($datInact as $dt) {
                    $nomAgIn = trim(($dt["fas"] ?? '') . ' ' . ($dt["actpro"] ?? ''));
                    if($nomAgIn==='') $nomAgIn='Agenda';
                ?>
                <tr>
                    <td><?=$dt["idses"];?></td>
                    <td><?=$nomAgIn;?></td>
                    <td><?=$dt["idfic"] ?? 'N/A';?></td>
                    <td><?=$dt["fas"] ?? 'N/A';?></td>
                    <td><?=$dt["actpro"] ?? $dt["idage"];?></td>
                    <td><?=$dt["fecses"];?></td>
                    <td class="text-center">
                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#mod<?=$dt["idses"];?>" title="Ver"><i class="fa-solid fa-eye"></i></button>
                            <a href="home.php?pg=<?=$pg;?>&idses=<?=$dt["idses"];?>&ope=edit" class="btn btn-outline-warning" title="Editar"><i class="fa-solid fa-pen-to-square"></i></a>
                            <a href="home.php?pg=<?=$pg;?>&idses=<?=$dt["idses"];?>&ope=del" class="btn btn-outline-danger" title="Eliminar" onclick="return eli(this);"><i class="fa-solid fa-trash-can"></i></a>
                        </div>
                        <div class="modal fade" id="mod<?=$dt["idses"];?>" tabindex="-1" aria-labelledby="modLabel<?=$dt["idses"];?>" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                <div class="modal-content shadow-lg border-0" style="border-radius: 16px; overflow: hidden;">
                                    <div class="modal-header bg-dark text-white border-0 px-4 py-3">
                                        <h5 class="modal-title fs-5 d-flex align-items-center gap-2" id="modLabel<?=$dt["idses"];?>">
                                            <i class="fa-solid fa-calendar-xmark text-danger"></i>
                                            <span class="text-truncate" style="max-width: 650px;">Sesión #<?=$dt["idses"];?> - <?=$dt["fas"] ?? 'Agenda ' . $dt["idage"];?></span>
                                        </h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <strong>Agenda:</strong> <?=$dt["idage"];?><br>
                                                <strong>Fase:</strong> <?=$dt["fas"] ?? 'N/A';?><br>
                                                <strong>Actividad:</strong> <?=$dt["actpro"] ?? 'N/A';?>
                                            </div>
                                            <div class="col-md-6">
                                                <strong>Ficha:</strong> <?=$dt["idfic"] ?? 'N/A';?><br>
                                                <strong>Fecha sesión:</strong> <?=$dt["fecses"];?><br>
                                                <strong>Id Sesión:</strong> <?=$dt["idses"];?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer bg-light border-0 px-4 py-3 d-flex justify-content-between align-items-center">
                                        <div class="text-muted small">
                                            <i class="fa-regular fa-calendar me-1"></i><?=$dt["fecses"];?> | <i class="fa-solid fa-hashtag me-1"></i>Agenda: <?=$dt["idage"];?>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <a href="home.php?pg=<?=$pg;?>&idses=<?=$dt["idses"];?>&ope=edit" class="btn btn-warning btn-sm">
                                                <i class="fa-solid fa-pen-to-square me-1"></i> Editar
                                            </a>
                                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                <?php }} ?>
            </tbody>
        </table>
        </div>
    </div>

    <!-- Pestaña 3: Todas -->
    <div class="tab-pane fade" id="todos" role="tabpanel" aria-labelledby="todos-tab">
        <div class="table-responsive">
        <table id="tblTodos" class="table table-striped table-hover align-middle" style="width:100%">
            <thead class="table-light">
                <tr>
                    <th style="width:60px;">Id</th>
                    <th style="width:80px;">Agenda</th>
                    <th style="width:110px;">Ficha</th>
                    <th>Fase</th>
                    <th>Actividad</th>
                    <th style="width:110px;">Fecha</th>
                    <th style="width:160px;" class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $datTodos = $dat ?? [];
                if($datTodos){ foreach ($datTodos as $dt) {
                    $esActivo = ($dt["fecses"] >= date("Y-m-d"));
                    $nomAgTodos = trim(($dt["fas"] ?? '') . ' ' . ($dt["actpro"] ?? ''));
                    if($nomAgTodos==='') $nomAgTodos='Agenda';
                ?>
                <tr>
                    <td><?=$dt["idses"];?></td>
                    <td><?=$nomAgTodos;?></td>
                    <td><?=$dt["idfic"] ?? 'N/A';?></td>
                    <td><?=$dt["fas"] ?? 'N/A';?></td>
                    <td><?=$dt["actpro"] ?? $dt["idage"];?></td>
                    <td><?=$dt["fecses"];?></td>
                    <td class="text-center">
                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modTodos<?=$dt["idses"];?>" title="Ver"><i class="fa-solid fa-eye"></i></button>
                            <a href="home.php?pg=<?=$pg;?>&idses=<?=$dt["idses"];?>&ope=edit" class="btn btn-outline-warning" title="Editar"><i class="fa-solid fa-pen-to-square"></i></a>
                            <a href="home.php?pg=<?=$pg;?>&idses=<?=$dt["idses"];?>&ope=del" class="btn btn-outline-danger" title="Eliminar" onclick="return eli(this);"><i class="fa-solid fa-trash-can"></i></a>
                        </div>
                        <div class="modal fade" id="modTodos<?=$dt["idses"];?>" tabindex="-1" aria-labelledby="modTodosLabel<?=$dt["idses"];?>" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                <div class="modal-content shadow-lg border-0" style="border-radius: 16px; overflow: hidden;">
                                    <div class="modal-header bg-dark text-white border-0 px-4 py-3">
                                        <h5 class="modal-title fs-5 d-flex align-items-center gap-2" id="modTodosLabel<?=$dt["idses"];?>">
                                            <i class="fa-solid <?= $esActivo ? 'fa-calendar-check text-success' : 'fa-calendar-xmark text-danger'; ?>"></i>
                                            <span class="text-truncate" style="max-width: 650px;">Sesión #<?=$dt["idses"];?> - <?=$dt["fas"] ?? 'Agenda ' . $dt["idage"];?></span>
                                        </h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <strong>Agenda:</strong> <?=$dt["idage"];?><br>
                                                <strong>Fase:</strong> <?=$dt["fas"] ?? 'N/A';?><br>
                                                <strong>Actividad:</strong> <?=$dt["actpro"] ?? 'N/A';?>
                                            </div>
                                            <div class="col-md-6">
                                                <strong>Ficha:</strong> <?=$dt["idfic"] ?? 'N/A';?><br>
                                                <strong>Fecha sesión:</strong> <?=$dt["fecses"];?><br>
                                                <strong>Id Sesión:</strong> <?=$dt["idses"];?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer bg-light border-0 px-4 py-3 d-flex justify-content-between align-items-center">
                                        <div class="text-muted small">
                                            <i class="fa-regular fa-calendar me-1"></i><?=$dt["fecses"];?> | <i class="fa-solid fa-hashtag me-1"></i>Agenda: <?=$dt["idage"];?>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <a href="home.php?pg=<?=$pg;?>&idses=<?=$dt["idses"];?>&ope=edit" class="btn btn-warning btn-sm">
                                                <i class="fa-solid fa-pen-to-square me-1"></i> Editar
                                            </a>
                                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                <?php }} ?>
            </tbody>
        </table>
        </div>
    </div>
</div>
