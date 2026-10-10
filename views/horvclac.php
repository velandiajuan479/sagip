<?php require_once("controllers/horcclac.php"); ?>
<?php require_once("views/vscopehya.php"); ?>

<div class="conte">
    <?php echo titulo2("<i class='" . ($icono ?? '') . "'></i> Calendario Académico", $isAdmin ? 1 : 0); ?>

    <div class="row my-4 g-4">
        <!-- Total Eventos -->
        <div class="col-12 col-md-4">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #3e4a3d;">Total en Sistema</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #0b1c30;"><?= $totCal; ?></span>
                            <span style="font-size: 13px; color: #3e4a3d;">eventos</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box" style="background-color: #b5dcfe; color: #3c627f;">
                        <i class="fa-solid fa-layer-group fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 11px; color: #3c627f;">
                        <i class="fa-solid fa-arrow-trend-up" style="font-size: 12px;"></i> Registrados
                    </span>
                    <span style="font-size: 11px; color: #3e4a3d;">Disponibles: 100%</span>
                </div>
            </div>
        </div>

        <!-- Vigentes -->
        <div class="col-12 col-md-4">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #006b29;">Eventos Vigentes</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #006b29;"><?= $totVig; ?></span>
                            <span style="font-size: 13px; color: rgba(0, 107, 41, 0.8);">vigentes</span>
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
                    <span style="font-size: 11px; color: #3e4a3d;">fecha_fin >= hoy</span>
                </div>
            </div>
        </div>

        <!-- Pasados -->
        <div class="col-12 col-md-4">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #ba1a1a;">Eventos Pasados</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #0b1c30;"><?= $totPas; ?></span>
                            <span style="font-size: 13px; color: #3e4a3d;">archivados</span>
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
                    <span class="fw-semibold" style="font-size: 11px; color: #3c627f;">Pasados</span>
                </div>
            </div>
        </div>
    </div>

    <?php if ($isAdmin): ?>
        <div class="inser">
            <form id="frmins" class="filter-toolbar-container" action="home.php?pg=<?= $pg; ?>" method="POST">
                <h2><i class="fa-solid fa-calendar-plus"></i> <?= $datOne ? 'Actualizar evento' : 'Nuevo evento'; ?></h2>
                <div class="row">
                    <div class="form-group col-md-6">
                        <label for="titulo"><strong>Título</strong></label>
                        <input type="text" name="titulo" id="titulo" class="form-control" required value="<?php if (!empty($datOne['titulo'])) echo htmlspecialchars($datOne['titulo']); ?>" placeholder="Título del evento">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="tipo_evento"><strong>Tipo</strong></label>
                        <select name="tipo_evento" id="tipo_evento" class="form-control form-select">
                            <option value="evento" <?php if (($datOne['tipo_evento'] ?? '')=='evento') echo 'selected'; ?>>evento</option>
                            <option value="inicio_clases" <?php if (($datOne['tipo_evento'] ?? '')=='inicio_clases') echo 'selected'; ?>>inicio_clases</option>
                            <option value="receso" <?php if (($datOne['tipo_evento'] ?? '')=='receso') echo 'selected'; ?>>receso</option>
                            <option value="examen" <?php if (($datOne['tipo_evento'] ?? '')=='examen') echo 'selected'; ?>>examen</option>
                            <option value="festivo" <?php if (($datOne['tipo_evento'] ?? '')=='festivo') echo 'selected'; ?>>festivo</option>
                            <option value="trimestre" <?php if (($datOne['tipo_evento'] ?? '')=='trimestre') echo 'selected'; ?>>trimestre</option>
                            <option value="alistamiento" <?php if (($datOne['tipo_evento'] ?? '')=='alistamiento') echo 'selected'; ?>>alistamiento</option>
                            <option value="balance" <?php if (($datOne['tipo_evento'] ?? '')=='balance') echo 'selected'; ?>>balance</option>
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <label for="anio"><strong>Año</strong></label>
                        <input type="number" name="anio" id="anio" class="form-control" value="<?php if (!empty($datOne['anio'])) echo htmlspecialchars($datOne['anio']); else echo date('Y'); ?>">
                    </div>
                    <div class="form-group col-md-6">
                        <label for="descripcion"><strong>Descripción</strong></label>
                        <textarea name="descripcion" id="descripcion" class="form-control" rows="2" placeholder="Descripción"><?php if (!empty($datOne['descripcion'])) echo htmlspecialchars($datOne['descripcion']); ?></textarea>
                    </div>
                    <div class="form-group col-md-2">
                        <label for="fecha_inicio"><strong>Fecha inicio</strong></label>
                        <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control" required value="<?php if (!empty($datOne['fecha_inicio'])) echo htmlspecialchars($datOne['fecha_inicio']); ?>">
                    </div>
                    <div class="form-group col-md-2">
                        <label for="fecha_fin"><strong>Fecha fin</strong></label>
                        <input type="date" name="fecha_fin" id="fecha_fin" class="form-control" required value="<?php if (!empty($datOne['fecha_fin'])) echo htmlspecialchars($datOne['fecha_fin']); ?>">
                    </div>
                    <div class="form-group col-md-2">
                        <label for="trimestre"><strong>Trimestre</strong></label>
                        <input type="text" name="trimestre" id="trimestre" class="form-control" value="<?php if (!empty($datOne['trimestre'])) echo htmlspecialchars($datOne['trimestre']); ?>" placeholder="Ej: Primer Trimestre">
                    </div>
                    <div class="form-group col-md-12" style="text-align:right;margin-top:10px;">
                        <input type="submit" value="<?= $datOne ? 'Actualizar' : 'Guardar'; ?>" class="btn btn-success">
                        <?php if ($datOne): ?>
                            <a href="home.php?pg=<?= $pg; ?>" class="btn btn-secondary">Cancelar</a>
                        <?php endif; ?>
                        <input type="hidden" name="opera" value="save">
                        <input type="hidden" name="idcla" value="<?= htmlspecialchars($idcla ?? ''); ?>">
                    </div>
                </div>
            </form>
        </div>
    <?php endif; ?>
</div>

<!-- Toolbar de Control y Filtros -->
<style>
/* Institucional HyA: estas tarjetas NO son videos (sin preview oscuro ni botón play) */
.vid-preview-box{background:linear-gradient(135deg,#00af00 0%,#008700 100%)!important;}
.vid-preview-box>div:first-child{background:linear-gradient(135deg,#00af00 0%,#008700 100%)!important;}
.vid-play-btn{display:none!important;}
</style>
<div class="filter-toolbar-container">
    <div class="toolbar-left-group">
        <div class="filter-pills-group" id="tabVideos" role="tablist">
            <button class="btn-filter-pill active" id="activos-tab" data-bs-toggle="tab" data-bs-target="#activos" type="button" role="tab" aria-controls="activos" aria-selected="true" onclick="filterByTab('activos')">
                <span class="pill-dot" style="background-color: #006b29;"></span>
                <span>Vigentes</span>
                <span class="pill-count"><?= count($datAct ?? []); ?></span>
            </button>
            <button class="btn-filter-pill" id="inactivos-tab" data-bs-toggle="tab" data-bs-target="#inactivos" type="button" role="tab" aria-controls="inactivos" aria-selected="false" onclick="filterByTab('inactivos')">
                <span class="pill-dot" style="background-color: #ba1a1a;"></span>
                <span>Pasados</span>
                <span class="pill-count"><?= count($datInact ?? []); ?></span>
            </button>
            <button class="btn-filter-pill" id="todos-tab" data-bs-toggle="tab" data-bs-target="#todos" type="button" role="tab" aria-controls="todos" aria-selected="false" onclick="filterByTab('todos')">
                <span class="pill-dot" style="background-color: #3c627f;"></span>
                <span>Todos</span>
                <span class="pill-count"><?= $totCal; ?></span>
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

<div class="tab-content" id="tabVideosContent">
    <!-- Vigentes -->
    <div class="tab-pane fade show active" id="activos" role="tabpanel" aria-labelledby="activos-tab">
        <table id="example" class="table table-striped table-vid-cards" style="width:100%">
            <thead><tr><th>Vigentes</th></tr></thead>
            <tbody>
                <?php if($datAct){ foreach ($datAct as $dt) {?>
                <tr data-order="<?=$dt['id'];?>">
                    <td data-search="<?=$dt["id"];?> <?=htmlspecialchars($dt["titulo"] ?? '');?> <?=htmlspecialchars($dt["descripcion"] ?? '');?> <?=$dt["fecha_inicio"];?> <?=$dt["fecha_fin"];?> <?=htmlspecialchars($dt["tipo_evento"] ?? '');?> <?=$dt["anio"];?> <?=htmlspecialchars($dt["trimestre"] ?? '');?>">
                        <div class="card h-100 shadow-sm border-0 vid-card">
                            <div class="vid-preview-box">
                                <div style="display:flex;align-items:center;justify-content:center;width:100%;height:100%;background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);color:#fff;">
                                    <i class="fa-solid fa-calendar-days" style="font-size:48px;opacity:0.9;"></i>
                                </div>
                                <div class="vid-badges-top">
                                    <span class="badge bg-success shadow-sm px-2 py-1">
                                        <i class="fa-solid fa-circle-check me-1"></i> Vigente
                                    </span>
                                    <span class="badge vid-badge-blur px-2 py-1 shadow-sm">
                                        <i class="fa-solid fa-tag me-1 text-info"></i> <?=htmlspecialchars($dt["tipo_evento"]);?>
                                    </span>
                                </div>
                                <div class="vid-play-btn" data-bs-toggle="modal" data-bs-target="#mod<?=$dt["id"];?>" title="Ver detalle" role="button">
                                    <i class="fa-solid fa-eye"></i>
                                </div>
                                <div class="vid-badges-bottom">
                                    <span class="badge vid-badge-blur px-2 py-1">
                                        <i class="fa-regular fa-calendar me-1 text-warning"></i> <?=$dt["fecha_inicio"];?>
                                    </span>
                                    <span class="badge vid-badge-blur px-2 py-1">
                                        <i class="fa-solid fa-calendar-week me-1 text-primary"></i> <?=$dt["anio"];?>
                                    </span>
                                </div>
                            </div>
                            <div class="card-body p-3 d-flex flex-column justify-content-between">
                                <div>
                                    <h6 class="fw-bold text-dark mb-2 text-truncate" title="<?=htmlspecialchars($dt["titulo"]);?>" style="font-size: 1.05rem;">
                                        <span class="text-primary">#<?=$dt["id"];?></span> - <?=htmlspecialchars($dt["titulo"]);?>
                                    </h6>
                                    <div class="p-2 mb-3 rounded-3 bg-light border border-light-subtle d-flex justify-content-between align-items-center" style="font-size: 0.8rem;">
                                        <div>
                                            <span class="text-muted d-block" style="font-size: 0.72rem;">Inicio</span>
                                            <span class="fw-bold text-success">
                                                <i class="fa-regular fa-calendar-check me-1"></i><?=$dt["fecha_inicio"];?>
                                            </span>
                                        </div>
                                        <div class="text-muted"><i class="fa-solid fa-arrow-right-long mx-1"></i></div>
                                        <div class="text-end">
                                            <span class="text-muted d-block" style="font-size: 0.72rem;">Fin</span>
                                            <span class="fw-bold text-danger">
                                                <i class="fa-regular fa-calendar-xmark me-1"></i><?=$dt["fecha_fin"];?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between text-muted mb-1" style="font-size: 0.78rem;">
                                        <span><i class="fa-solid fa-align-left me-1 text-primary"></i> <strong>Tipo:</strong> <?=htmlspecialchars($dt["tipo_evento"]);?> | <?=htmlspecialchars($dt["trimestre"] ?? ''); ?></span>
                                    </div>
                                    <div class="text-muted" style="font-size: 0.78rem; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                        <?=htmlspecialchars(substr($dt["descripcion"] ?? '',0,120));?><?= strlen($dt["descripcion"] ?? '')>120 ? '...' : '';?>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer bg-white border-0 pt-0 pb-3 px-3">
                                <hr class="mt-0 mb-3 text-muted opacity-25">
                                <div class="vid-actions-bar">
                                    <button type="button" class="btn btn-outline-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#mod<?=$dt["id"];?>" title="Ver">
                                        <i class="fa-solid fa-eye"></i> <span class="d-none d-sm-inline">Ver</span>
                                    </button>
                                    <?php if ($isAdmin): ?>
                                    <a href="home.php?pg=<?=$pg;?>&idcla=<?=$dt["id"];?>&opera=edi" class="btn btn-outline-warning text-dark shadow-sm" title="Editar">
                                        <i class="fa-solid fa-pen-to-square"></i> <span class="d-none d-sm-inline">Editar</span>
                                    </a>
                                    <a href="home.php?pg=<?=$pg;?>&idcla=<?=$dt["id"];?>&opera=eli" class="btn btn-outline-danger shadow-sm w-100" title="Eliminar" onclick="return eli(this);">
                                        <i class="fa-solid fa-trash-can"></i> <span class="d-none d-sm-inline">Elim.</span>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="modal fade" id="mod<?=$dt["id"];?>" tabindex="-1" aria-labelledby="modLabel<?=$dt["id"];?>" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                <div class="modal-content shadow-lg border-0" style="border-radius: 16px; overflow: hidden;">
                                    <div class="modal-header bg-dark text-white border-0 px-4 py-3">
                                        <h5 class="modal-title fs-5 d-flex align-items-center gap-2" id="modLabel<?=$dt["id"];?>">
                                            <i class="fa-solid fa-calendar-days text-success"></i>
                                            <span class="text-truncate" style="max-width: 650px;"><?=htmlspecialchars($dt["titulo"]);?></span>
                                        </h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <strong>Título:</strong> <?=htmlspecialchars($dt["titulo"]);?><br>
                                                <strong>Tipo:</strong> <?=htmlspecialchars($dt["tipo_evento"]);?><br>
                                                <strong>Trimestre:</strong> <?=htmlspecialchars($dt["trimestre"] ?? 'N/A');?><br>
                                                <strong>Año:</strong> <?=$dt["anio"];?>
                                            </div>
                                            <div class="col-md-6">
                                                <strong>Inicio:</strong> <?=$dt["fecha_inicio"];?><br>
                                                <strong>Fin:</strong> <?=$dt["fecha_fin"];?><br>
                                                <strong>ID:</strong> <?=$dt["id"];?>
                                            </div>
                                            <div class="col-12">
                                                <strong>Descripción:</strong><br><?=nl2br(htmlspecialchars($dt["descripcion"] ?? 'Sin descripción'));?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer bg-light border-0 px-4 py-3 d-flex justify-content-between align-items-center">
                                        <div class="text-muted small">
                                            <i class="fa-regular fa-calendar me-1"></i><?=$dt["fecha_inicio"];?> -> <?=$dt["fecha_fin"];?>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <?php if ($isAdmin): ?>
                                            <a href="home.php?pg=<?=$pg;?>&idcla=<?=$dt["id"];?>&opera=edi" class="btn btn-warning btn-sm">
                                                <i class="fa-solid fa-pen-to-square me-1"></i> Editar
                                            </a>
                                            <?php endif; ?>
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
            <tfoot><tr><th>Vigentes</th></tr></tfoot>
        </table>
    </div>

    <!-- Pasados -->
    <div class="tab-pane fade" id="inactivos" role="tabpanel" aria-labelledby="inactivos-tab">
        <table id="myt" class="table table-striped table-vid-cards" style="width:100%">
            <thead><tr><th>Pasados</th></tr></thead>
            <tbody>
                <?php if($datInact){ foreach ($datInact as $dt) {?>
                <tr data-order="<?=$dt['id'];?>">
                    <td data-search="<?=$dt["id"];?> <?=htmlspecialchars($dt["titulo"] ?? '');?> <?=htmlspecialchars($dt["descripcion"] ?? '');?> <?=$dt["fecha_inicio"];?> <?=$dt["fecha_fin"];?> <?=htmlspecialchars($dt["tipo_evento"] ?? '');?> <?=$dt["anio"];?>">
                        <div class="card h-100 shadow-sm border-0 vid-card">
                            <div class="vid-preview-box" style="filter: grayscale(0.25);">
                                <div style="display:flex;align-items:center;justify-content:center;width:100%;height:100%;background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);color:#fff;">
                                    <i class="fa-solid fa-calendar-xmark" style="font-size:48px;opacity:0.6;"></i>
                                </div>
                                <div class="vid-badges-top">
                                    <span class="badge bg-danger shadow-sm px-2 py-1">
                                        <i class="fa-solid fa-circle-xmark me-1"></i> Pasado
                                    </span>
                                    <span class="badge vid-badge-blur px-2 py-1 shadow-sm">
                                        <i class="fa-solid fa-tag me-1 text-info"></i> <?=htmlspecialchars($dt["tipo_evento"]);?>
                                    </span>
                                </div>
                                <div class="vid-play-btn" data-bs-toggle="modal" data-bs-target="#mod<?=$dt["id"];?>" title="Ver detalle" role="button">
                                    <i class="fa-solid fa-eye"></i>
                                </div>
                                <div class="vid-badges-bottom">
                                    <span class="badge vid-badge-blur px-2 py-1">
                                        <i class="fa-regular fa-calendar me-1 text-warning"></i> <?=$dt["fecha_inicio"];?>
                                    </span>
                                    <span class="badge vid-badge-blur px-2 py-1">
                                        <i class="fa-solid fa-calendar-week me-1 text-primary"></i> <?=$dt["anio"];?>
                                    </span>
                                </div>
                            </div>
                            <div class="card-body p-3 d-flex flex-column justify-content-between">
                                <div>
                                    <h6 class="fw-bold text-dark mb-2 text-truncate" title="<?=htmlspecialchars($dt["titulo"]);?>" style="font-size: 1.05rem;">
                                        <span class="text-danger">#<?=$dt["id"];?></span> - <?=htmlspecialchars($dt["titulo"]);?>
                                    </h6>
                                    <div class="p-2 mb-3 rounded-3 bg-light border border-light-subtle d-flex justify-content-between align-items-center" style="font-size: 0.8rem;">
                                        <div>
                                            <span class="text-muted d-block" style="font-size: 0.72rem;">Inicio</span>
                                            <span class="fw-bold text-secondary">
                                                <i class="fa-regular fa-calendar-check me-1"></i><?=$dt["fecha_inicio"];?>
                                            </span>
                                        </div>
                                        <div class="text-muted"><i class="fa-solid fa-arrow-right-long mx-1"></i></div>
                                        <div class="text-end">
                                            <span class="text-muted d-block" style="font-size: 0.72rem;">Fin</span>
                                            <span class="fw-bold text-danger">
                                                <i class="fa-regular fa-calendar-xmark me-1"></i><?=$dt["fecha_fin"];?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="text-muted" style="font-size: 0.78rem; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                        <?=htmlspecialchars(substr($dt["descripcion"] ?? '',0,120));?>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer bg-white border-0 pt-0 pb-3 px-3">
                                <hr class="mt-0 mb-3 text-muted opacity-25">
                                <div class="vid-actions-bar">
                                    <button type="button" class="btn btn-outline-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#mod<?=$dt["id"];?>" title="Ver">
                                        <i class="fa-solid fa-eye"></i> <span class="d-none d-sm-inline">Ver</span>
                                    </button>
                                    <?php if ($isAdmin): ?>
                                    <a href="home.php?pg=<?=$pg;?>&idcla=<?=$dt["id"];?>&opera=edi" class="btn btn-outline-warning text-dark shadow-sm" title="Editar">
                                        <i class="fa-solid fa-pen-to-square"></i> <span class="d-none d-sm-inline">Editar</span>
                                    </a>
                                    <a href="home.php?pg=<?=$pg;?>&idcla=<?=$dt["id"];?>&opera=eli" class="btn btn-outline-danger shadow-sm w-100" title="Eliminar" onclick="return eli(this);">
                                        <i class="fa-solid fa-trash-can"></i> <span class="d-none d-sm-inline">Elim.</span>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="modal fade" id="mod<?=$dt["id"];?>" tabindex="-1" aria-labelledby="modLabel<?=$dt["id"];?>" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                <div class="modal-content shadow-lg border-0" style="border-radius: 16px; overflow: hidden;">
                                    <div class="modal-header bg-dark text-white border-0 px-4 py-3">
                                        <h5 class="modal-title fs-5 d-flex align-items-center gap-2" id="modLabel<?=$dt["id"];?>">
                                            <i class="fa-solid fa-calendar-xmark text-danger"></i>
                                            <span class="text-truncate" style="max-width: 650px;"><?=htmlspecialchars($dt["titulo"]);?></span>
                                        </h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <strong>Descripción:</strong><br><?=nl2br(htmlspecialchars($dt["descripcion"] ?? 'Sin descripción'));?>
                                    </div>
                                    <div class="modal-footer bg-light border-0 px-4 py-3 d-flex justify-content-between align-items-center">
                                        <div class="text-muted small">
                                            <?=$dt["fecha_inicio"];?> -> <?=$dt["fecha_fin"];?>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <?php if ($isAdmin): ?>
                                            <a href="home.php?pg=<?=$pg;?>&idcla=<?=$dt["id"];?>&opera=edi" class="btn btn-warning btn-sm">Editar</a>
                                            <?php endif; ?>
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
            <tfoot><tr><th>Pasados</th></tr></tfoot>
        </table>
    </div>

    <!-- Todas -->
    <div class="tab-pane fade" id="todos" role="tabpanel" aria-labelledby="todos-tab">
        <table id="tblTodos" class="table table-striped table-vid-cards" style="width:100%">
            <thead><tr><th>Todas</th></tr></thead>
            <tbody>
                <?php 
                $datTodos = $datAll ?? [];
                if($datTodos){ foreach ($datTodos as $dt) {
                    $esVigente = ($dt["fecha_fin"] >= date("Y-m-d"));
                ?>
                <tr data-order="<?=$dt['id'];?>">
                    <td data-search="<?=$dt["id"];?> <?=htmlspecialchars($dt["titulo"] ?? '');?> <?=htmlspecialchars($dt["descripcion"] ?? '');?> <?=$dt["fecha_inicio"];?> <?=$dt["fecha_fin"];?> <?=htmlspecialchars($dt["tipo_evento"] ?? '');?> <?=$dt["anio"];?>">
                        <div class="card h-100 shadow-sm border-0 vid-card">
                            <div class="vid-preview-box" <?= !$esVigente ? 'style="filter: grayscale(0.25);"' : ''; ?>>
                                <div style="display:flex;align-items:center;justify-content:center;width:100%;height:100%;background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);color:#fff;">
                                    <i class="fa-solid <?= $esVigente ? 'fa-calendar-check' : 'fa-calendar-xmark'; ?>" style="font-size:48px;opacity:0.9;"></i>
                                </div>
                                <div class="vid-badges-top">
                                    <?php if($esVigente): ?>
                                        <span class="badge bg-success shadow-sm px-2 py-1">
                                            <i class="fa-solid fa-circle-check me-1"></i> Vigente
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-danger shadow-sm px-2 py-1">
                                            <i class="fa-solid fa-circle-xmark me-1"></i> Pasado
                                        </span>
                                    <?php endif; ?>
                                    <span class="badge vid-badge-blur px-2 py-1 shadow-sm">
                                        <i class="fa-solid fa-tag me-1 text-info"></i> <?=htmlspecialchars($dt["tipo_evento"]);?>
                                    </span>
                                </div>
                                <div class="vid-play-btn" data-bs-toggle="modal" data-bs-target="#modTodos<?=$dt["id"];?>" title="Ver detalle" role="button">
                                    <i class="fa-solid fa-eye"></i>
                                </div>
                                <div class="vid-badges-bottom">
                                    <span class="badge vid-badge-blur px-2 py-1">
                                        <i class="fa-regular fa-calendar me-1 text-warning"></i> <?=$dt["fecha_inicio"];?>
                                    </span>
                                    <span class="badge vid-badge-blur px-2 py-1">
                                        <i class="fa-solid fa-file-lines me-1 text-primary"></i> #<?=$dt["id"];?>
                                    </span>
                                </div>
                            </div>
                            <div class="card-body p-3 d-flex flex-column justify-content-between">
                                <div>
                                    <h6 class="fw-bold text-dark mb-2 text-truncate" title="<?=htmlspecialchars($dt["titulo"]);?>" style="font-size: 1.05rem;">
                                        <span class="<?= $esVigente ? 'text-primary' : 'text-danger'; ?>">#<?=$dt["id"];?></span> - <?=htmlspecialchars($dt["titulo"]);?>
                                    </h6>
                                    <div class="p-2 mb-3 rounded-3 bg-light border border-light-subtle d-flex justify-content-between align-items-center" style="font-size: 0.8rem;">
                                        <div>
                                            <span class="text-muted d-block" style="font-size: 0.72rem;">Inicio</span>
                                            <span class="fw-bold <?= $esVigente ? 'text-success' : 'text-secondary'; ?>">
                                                <i class="fa-regular fa-calendar-check me-1"></i><?=$dt["fecha_inicio"];?>
                                            </span>
                                        </div>
                                        <div class="text-muted"><i class="fa-solid fa-arrow-right-long mx-1"></i></div>
                                        <div class="text-end">
                                            <span class="text-muted d-block" style="font-size: 0.72rem;">Fin</span>
                                            <span class="fw-bold text-danger">
                                                <i class="fa-regular fa-calendar-xmark me-1"></i><?=$dt["fecha_fin"];?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="text-muted" style="font-size: 0.78rem; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                        <?=htmlspecialchars(substr($dt["descripcion"] ?? '',0,120));?>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer bg-white border-0 pt-0 pb-3 px-3">
                                <hr class="mt-0 mb-3 text-muted opacity-25">
                                <div class="vid-actions-bar">
                                    <button type="button" class="btn btn-outline-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modTodos<?=$dt["id"];?>" title="Ver">
                                        <i class="fa-solid fa-eye"></i> <span class="d-none d-sm-inline">Ver</span>
                                    </button>
                                    <?php if ($isAdmin): ?>
                                    <a href="home.php?pg=<?=$pg;?>&idcla=<?=$dt["id"];?>&opera=edi" class="btn btn-outline-warning text-dark shadow-sm" title="Editar">
                                        <i class="fa-solid fa-pen-to-square"></i> <span class="d-none d-sm-inline">Editar</span>
                                    </a>
                                    <a href="home.php?pg=<?=$pg;?>&idcla=<?=$dt["id"];?>&opera=eli" class="btn btn-outline-danger shadow-sm w-100" title="Eliminar" onclick="return eli(this);">
                                        <i class="fa-solid fa-trash-can"></i> <span class="d-none d-sm-inline">Elim.</span>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="modal fade" id="modTodos<?=$dt["id"];?>" tabindex="-1" aria-labelledby="modTodosLabel<?=$dt["id"];?>" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                <div class="modal-content shadow-lg border-0" style="border-radius: 16px; overflow: hidden;">
                                    <div class="modal-header bg-dark text-white border-0 px-4 py-3">
                                        <h5 class="modal-title fs-5 d-flex align-items-center gap-2" id="modTodosLabel<?=$dt["id"];?>">
                                            <i class="fa-solid <?= $esVigente ? 'fa-calendar-check text-success' : 'fa-calendar-xmark text-danger'; ?>"></i>
                                            <span class="text-truncate" style="max-width: 650px;"><?=htmlspecialchars($dt["titulo"]);?></span>
                                        </h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <strong>Descripción:</strong><br><?=nl2br(htmlspecialchars($dt["descripcion"] ?? 'Sin descripción'));?>
                                    </div>
                                    <div class="modal-footer bg-light border-0 px-4 py-3 d-flex justify-content-between align-items-center">
                                        <div class="text-muted small">
                                            <?=$dt["fecha_inicio"];?> -> <?=$dt["fecha_fin"];?> | <?=htmlspecialchars($dt["tipo_evento"]);?>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <?php if ($isAdmin): ?>
                                            <a href="home.php?pg=<?=$pg;?>&idcla=<?=$dt["id"];?>&opera=edi" class="btn btn-warning btn-sm">Editar</a>
                                            <?php endif; ?>
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
            <tfoot><tr><th>Todas</th></tr></tfoot>
        </table>
    </div>
</div>
