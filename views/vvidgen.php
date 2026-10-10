<?php
require_once("controllers/cvidgen.php");
?>

<!-- Dashboard -->
<div class="conte">
    <?php echo titulo2("<i class='".$icono."'></i> Videos en TV",1); ?> 

    <div class="row my-4 g-4">
        <!-- Total Videos -->
        <div class="col-12 col-md-4">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #3e4a3d;">Total en Sistema</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #0b1c30;"><?= $totVid; ?></span>
                            <span style="font-size: 13px; color: #3e4a3d;">videos</span>
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

        <!-- Active Videos -->
        <div class="col-12 col-md-4">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #006b29;">Videos Activos</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #006b29;"><?= $totAct; ?></span>
                            <span style="font-size: 13px; color: rgba(0, 107, 41, 0.8);">en carrusel</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box" style="background-color: #7afd88; color: #006b24;">
                        <i class="fa-solid fa-circle-play fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="fw-semibold d-inline-flex align-items-center gap-2" style="font-size: 11px; color: #006b29;">
                        <span class="pulse-dot"></span> Emisión Activa (100% OK)
                    </span>
                    <span style="font-size: 11px; color: #3e4a3d;">En pantalla TV</span>
                </div>
            </div>
        </div>

        <!-- Inactive Videos -->
        <div class="col-12 col-md-4">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #ba1a1a;">Videos Inactivos</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #0b1c30;"><?= $totInact; ?></span>
                            <span style="font-size: 13px; color: #3e4a3d;">archivados</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box" style="background-color: #ffdad6; color: #ba1a1a;">
                        <i class="fa-solid fa-circle-pause fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="d-inline-flex align-items-center gap-1" style="font-size: 11px; color: #3e4a3d;">
                        <i class="fa-solid fa-clock-rotate-left" style="font-size: 12px;"></i> Fuera de cronograma
                    </span>
                    <span class="fw-semibold" style="font-size: 11px; color: #3c627f;">Pausados</span>
                </div>
            </div>
        </div>
    </div>

	<div class="inser">
		<form id="frmins" class="filter-toolbar-container" action="home.php?pg=<?=$pg;?>" method="POST" enctype="multipart/form-data">
            <h2><i class="fa-solid fa-file-circle-plus"></i> Nuevo video</h2>
		    <div class="row">
    			<div class="form-group col-md-6">
    				<label for="nomvid">Nombre</label>
    				<input type="text" class="form-control" name="nomvid" id="nomvid" value="<?php if($dtOn) echo $dtOn[0]["nomvid"]; ?>" required>
    			</div>
    			<div class="form-group col-md-6">
    				<label for="rutvid">Ruta</label>
    				<input type="file" class="form-control" name="rutvid" id="rutvid" accept="video/mp4" value="<?php if($dtOn) echo $dtOn[0]["rutvid"]; ?>" required>
    			</div>
    			<div class="form-group col-md-6">
    				<label for="ordvid">Orden</label>
    				<input type="number" class="form-control" name="ordvid" id="ordvid" value="<?php if($dtOn) echo $dtOn[0]["ordvid"]; else echo $ctn; ?>" required>
    			</div>
    			<div class="form-group col-md-6">
    				<label for="fecini">Fecha inicio</label>
    				<input type="date" class="form-control" name="fecini" id="fecini" value="<?php if($dtOn) echo $dtOn[0]["fecini"]; else echo $fechini; ?>" required>
    			</div>
    			<div class="form-group col-md-6">
    				<label for="fecfin">Fecha fin</label>
    				<input type="date" class="form-control" name="fecfin" id="fecfin" value="<?php if($dtOn) echo $dtOn[0]["fecfin"]; else echo $fechfin; ?>" required>
    			</div>
    			<div class="form-group col-md-4">
    				<label for="actvid">Activo</label>
    				<select class="form-control form-select" name="actvid" id="actvid">
    					<option value="1" <?php if($dtOn && $dtOn[0]["actvid"]=="1") echo "selected"; ?>>Sí</option>
    					<option value="2" <?php if($dtOn && $dtOn[0]["actvid"]!="1") echo "selected"; ?>>No</option>
    				</select>
    			</div>
    			<div class="form-group col-md-2">
    				<br>
    				<input type="hidden" class="form-control" name="idvid" value="<?php if($dtOn) echo $dtOn[0]["idvid"]; ?>">
    				<input type="hidden" name="pesvid" id="pesvid" value="<?php if($dtOn && isset($dtOn[0]["pesvid"])) echo $dtOn[0]["pesvid"]; ?>">
    				<input type="hidden" name="durvid" id="durvid" value="<?php if($dtOn && isset($dtOn[0]["durvid"])) echo $dtOn[0]["durvid"]; ?>">
    				<input type="hidden" name="ope" value="save">
    				<input class="btn btn-success" style="width: 100%;" type="submit" value="Enviar">
    				<br><br>
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
            <!-- Píldora Activos (Activa por defecto al ingresar) -->
            <button class="btn-filter-pill active" id="activos-tab" data-bs-toggle="tab" data-bs-target="#activos" type="button" role="tab" aria-controls="activos" aria-selected="true" onclick="filterByTab('activos')">
                <span class="pill-dot" style="background-color: #006b29;"></span>
                <span>Activos</span>
                <span class="pill-count"><?= count($datAct ?? []); ?></span>
            </button>

            <!-- Píldora Inactivos -->
            <button class="btn-filter-pill" id="inactivos-tab" data-bs-toggle="tab" data-bs-target="#inactivos" type="button" role="tab" aria-controls="inactivos" aria-selected="false" onclick="filterByTab('inactivos')">
                <span class="pill-dot" style="background-color: #ba1a1a;"></span>
                <span>Inactivos</span>
                <span class="pill-count"><?= count($datInact ?? []); ?></span>
            </button>

            <!-- Píldora Todos -->
            <button class="btn-filter-pill" id="todos-tab" data-bs-toggle="tab" data-bs-target="#todos" type="button" role="tab" aria-controls="todos" aria-selected="false" onclick="filterByTab('todos')">
                <span class="pill-dot" style="background-color: #3c627f;"></span>
                <span>Todos</span>
                <span class="pill-count"><?= $totVid; ?></span>
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
    <!-- Pestaña 1: Videos Activos (Activa por defecto al ingresar) -->
    <div class="tab-pane fade show active" id="activos" role="tabpanel" aria-labelledby="activos-tab">
        <table id="example" class="table table-striped table-vid-cards" style="width:100%">
            <thead>
                <tr>
                    <th>V. Activos</th>
                </tr>
            </thead>
            <tbody>
                <?php if($datAct){ foreach ($datAct as $dt) {?>
                <tr data-order="<?=$dt['idvid'];?>">
                    <td data-search="<?=$dt["idvid"];?> <?=$dt["nomvid"];?> <?=$dt["feccar"];?> <?=$dt["fecini"];?> <?=$dt["fecfin"];?> <?=$dt["idusu"];?> <?=$dt["durvid"];?> <?=$dt["ordvid"];?>">
                        
                        <!-- Tarjeta de Video -->
                        <div class="card h-100 shadow-sm border-0 vid-card">
                            
                            <!-- Video Thumbnail / Preview -->
                            <div class="vid-preview-box">
                                <video preload="metadata" muted playsinline>
                                    <source src="../vid/vis/<?=$dt["rutvid"];?>#t=0.5" type="video/mp4">
                                </video>

                                <!-- Badges Superiores -->
                                <div class="vid-badges-top">
                                    <span class="badge bg-success shadow-sm px-2 py-1">
                                        <i class="fa-solid fa-circle-check me-1"></i> Activo
                                    </span>
                                    <span class="badge vid-badge-blur px-2 py-1 shadow-sm">
                                        <i class="fa-solid fa-arrow-down-1-9 me-1 text-info"></i> Orden: <?=$dt["ordvid"];?>
                                    </span>
                                </div>

                                <!-- Botón central para ver / reproducir video -->
                                <div class="vid-play-btn" data-bs-toggle="modal" data-bs-target="#mod<?=$dt["idvid"];?>" title="Reproducir Video" role="button">
                                    <i class="fa-solid fa-play"></i>
                                </div>

                                <!-- Badges Inferiores (Duración y Peso) -->
                                <div class="vid-badges-bottom">
                                    <?php if(!empty($dt["durvid"])): ?>
                                        <span class="badge vid-badge-blur px-2 py-1">
                                            <i class="fa-regular fa-clock me-1 text-warning"></i> <?=$dt["durvid"];?>
                                        </span>
                                    <?php else: ?>
                                        <span></span>
                                    <?php endif; ?>

                                    <?php if(!empty($dt["pesvid"]) && $dt["pesvid"] > 0): ?>
                                        <span class="badge vid-badge-blur px-2 py-1">
                                            <i class="fa-solid fa-file-video me-1 text-primary"></i> <?=number_format($dt["pesvid"] / (1024 * 1024), 2);?> MB
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Cuerpo de la Tarjeta -->
                            <div class="card-body p-3 d-flex flex-column justify-content-between">
                                <div>
                                    <!-- Título del video con ID -->
                                    <h6 class="fw-bold text-dark mb-2 text-truncate" title="<?=$dt["idvid"];?> - <?=htmlspecialchars($dt["nomvid"]);?>" style="font-size: 1.05rem;">
                                        <span class="text-primary">#<?=$dt["idvid"];?></span> - <?=$dt["nomvid"];?>
                                    </h6>

                                    <!-- Caja de Vigencia (Fechas) -->
                                    <div class="p-2 mb-3 rounded-3 bg-light border border-light-subtle d-flex justify-content-between align-items-center" style="font-size: 0.8rem;">
                                        <div>
                                            <span class="text-muted d-block" style="font-size: 0.72rem;">Publicación</span>
                                            <span class="fw-bold text-success">
                                                <i class="fa-regular fa-calendar-check me-1"></i><?=$dt["fecini"];?>
                                            </span>
                                        </div>
                                        <div class="text-muted"><i class="fa-solid fa-arrow-right-long mx-1"></i></div>
                                        <div class="text-end">
                                            <span class="text-muted d-block" style="font-size: 0.72rem;">Cierre</span>
                                            <span class="fw-bold text-danger">
                                                <i class="fa-regular fa-calendar-xmark me-1"></i><?=$dt["fecfin"];?>
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Metadatos de Carga y Registro -->
                                    <div class="d-flex justify-content-between text-muted mb-1" style="font-size: 0.78rem;">
                                        <span><i class="fa-solid fa-cloud-arrow-up text-primary me-1"></i> <strong>Carga:</strong> <?=$dt["feccar"];?></span>
                                    </div>
                                    <div class="d-flex justify-content-between text-muted" style="font-size: 0.78rem;">
                                        <span><i class="fa-solid fa-user-pen text-secondary me-1"></i> <strong>Registró:</strong> <?=$dt["idusu"];?></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Footer con Botones de Acción -->
                            <div class="card-footer bg-white border-0 pt-0 pb-3 px-3">
                                <hr class="mt-0 mb-3 text-muted opacity-25">
                                <div class="vid-actions-bar">
                                    <!-- Botón Ver Video -->
                                    <button type="button" class="btn btn-outline-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#mod<?=$dt["idvid"];?>" title="Ver Video">
                                        <i class="fa-solid fa-eye"></i> <span class="d-none d-sm-inline">Ver</span>
                                    </button>

                                    <!-- Botón Editar -->
                                    <a href="home.php?pg=<?=$pg;?>&idvid=<?=$dt["idvid"];?>&ope=edi" class="btn btn-outline-warning text-dark shadow-sm" title="Editar">
                                        <i class="fa-solid fa-pen-to-square"></i> <span class="d-none d-sm-inline">Editar</span>
                                    </a>

                                    <!-- Botón Desactivar -->
                                    <form action="home.php?pg=<?=$pg;?>" method="POST" class="m-0 p-0 w-100">
                                        <input type="hidden" name="idvid" value="<?=$dt["idvid"];?>">
                                        <input type="hidden" name="actvid" value="2">
                                        <input type="hidden" name="ope" value="act">
                                        <button type="submit" class="btn btn-outline-secondary w-100 shadow-sm" title="Desactivar Video">
                                            <i class="fa-solid fa-toggle-on text-primary"></i> <span class="d-none d-sm-inline">Desact.</span>
                                        </button>
                                    </form>

                                    <!-- Botón Eliminar -->
                                    <a href="home.php?pg=<?=$pg;?>&idvid=<?=$dt["idvid"];?>&ope=eli" class="btn btn-outline-danger shadow-sm" title="Eliminar" onclick="return eli(this);">
                                        <i class="fa-solid fa-trash-can"></i> <span class="d-none d-sm-inline">Elim.</span>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Modal Reproductor de Video -->
                        <div class="modal fade" id="mod<?=$dt["idvid"];?>" tabindex="-1" aria-labelledby="modLabel<?=$dt["idvid"];?>" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                <div class="modal-content shadow-lg border-0" style="border-radius: 16px; overflow: hidden;">
                                    <div class="modal-header bg-dark text-white border-0 px-4 py-3">
                                        <h5 class="modal-title fs-5 d-flex align-items-center gap-2" id="modLabel<?=$dt["idvid"];?>">
                                            <i class="fa-solid fa-circle-play text-primary"></i>
                                            <span class="text-truncate" style="max-width: 650px;"><?=$dt["nomvid"];?></span>
                                        </h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body bg-black p-0 text-center">
                                        <video width="100%" controls preload="auto" style="max-height: 70vh; outline: none;">
                                            <source src="../vid/vis/<?=$dt["rutvid"];?>" type="video/mp4">
                                            Tu navegador no soporta reproducción de este video.
                                        </video>
                                    </div>
                                    <div class="modal-footer bg-light border-0 px-4 py-3 d-flex justify-content-between align-items-center">
                                        <div class="text-muted small">
                                            <i class="fa-regular fa-clock me-1"></i><?= !empty($dt["durvid"]) ? $dt["durvid"] : 'N/D'; ?> | 
                                            <i class="fa-solid fa-arrow-down-1-9 me-1"></i>Orden: <?=$dt["ordvid"];?>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <a href="home.php?pg=<?=$pg;?>&idvid=<?=$dt["idvid"];?>&ope=edi" class="btn btn-warning btn-sm">
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
            <tfoot>
                <tr>
                    <th>V. Activos</th>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Pestaña 2: V. Inactivos (Vista en Tarjetas / Cards con mismo diseño) -->
    <div class="tab-pane fade" id="inactivos" role="tabpanel" aria-labelledby="inactivos-tab">
        <table id="myt" class="table table-striped table-vid-cards" style="width:100%">
            <thead>
                <tr>
                    <th>V. Inactivos</th>
                </tr>
            </thead>
            <tbody>
                <?php if($datInact){ foreach ($datInact as $dt) {?>
                <tr data-order="<?=$dt['idvid'];?>">
                    <td data-search="<?=$dt["idvid"];?> <?=$dt["nomvid"];?> <?=$dt["feccar"];?> <?=$dt["fecini"];?> <?=$dt["fecfin"];?> <?=$dt["idusu"];?> <?=$dt["durvid"];?> <?=$dt["ordvid"];?>">
                        
                        <!-- Tarjeta de Video Inactivo -->
                        <div class="card h-100 shadow-sm border-0 vid-card">
                            
                            <!-- Video Thumbnail / Preview -->
                            <div class="vid-preview-box" style="filter: grayscale(0.25);">
                                <video preload="metadata" muted playsinline>
                                    <source src="../vid/vis/<?=$dt["rutvid"];?>#t=0.5" type="video/mp4">
                                </video>

                                <!-- Badges Superiores -->
                                <div class="vid-badges-top">
                                    <span class="badge bg-danger shadow-sm px-2 py-1">
                                        <i class="fa-solid fa-circle-xmark me-1"></i> Inactivo
                                    </span>
                                    <span class="badge vid-badge-blur px-2 py-1 shadow-sm">
                                        <i class="fa-solid fa-arrow-down-1-9 me-1 text-info"></i> Orden: <?=$dt["ordvid"];?>
                                    </span>
                                </div>

                                <!-- Botón central para ver / reproducir video -->
                                <div class="vid-play-btn" data-bs-toggle="modal" data-bs-target="#mod<?=$dt["idvid"];?>" title="Reproducir Video" role="button">
                                    <i class="fa-solid fa-play"></i>
                                </div>

                                <!-- Badges Inferiores (Duración y Peso) -->
                                <div class="vid-badges-bottom">
                                    <?php if(!empty($dt["durvid"])): ?>
                                        <span class="badge vid-badge-blur px-2 py-1">
                                            <i class="fa-regular fa-clock me-1 text-warning"></i> <?=$dt["durvid"];?>
                                        </span>
                                    <?php else: ?>
                                        <span></span>
                                    <?php endif; ?>

                                    <?php if(!empty($dt["pesvid"]) && $dt["pesvid"] > 0): ?>
                                        <span class="badge vid-badge-blur px-2 py-1">
                                            <i class="fa-solid fa-file-video me-1 text-primary"></i> <?=number_format($dt["pesvid"] / (1024 * 1024), 2);?> MB
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Cuerpo de la Tarjeta -->
                            <div class="card-body p-3 d-flex flex-column justify-content-between">
                                <div>
                                    <!-- Título del video con ID -->
                                    <h6 class="fw-bold text-dark mb-2 text-truncate" title="<?=$dt["idvid"];?> - <?=htmlspecialchars($dt["nomvid"]);?>" style="font-size: 1.05rem;">
                                        <span class="text-danger">#<?=$dt["idvid"];?></span> - <?=$dt["nomvid"];?>
                                    </h6>

                                    <!-- Caja de Vigencia (Fechas) -->
                                    <div class="p-2 mb-3 rounded-3 bg-light border border-light-subtle d-flex justify-content-between align-items-center" style="font-size: 0.8rem;">
                                        <div>
                                            <span class="text-muted d-block" style="font-size: 0.72rem;">Publicación</span>
                                            <span class="fw-bold text-secondary">
                                                <i class="fa-regular fa-calendar-check me-1"></i><?=$dt["fecini"];?>
                                            </span>
                                        </div>
                                        <div class="text-muted"><i class="fa-solid fa-arrow-right-long mx-1"></i></div>
                                        <div class="text-end">
                                            <span class="text-muted d-block" style="font-size: 0.72rem;">Cierre</span>
                                            <span class="fw-bold text-danger">
                                                <i class="fa-regular fa-calendar-xmark me-1"></i><?=$dt["fecfin"];?>
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Metadatos de Carga y Registro -->
                                    <div class="d-flex justify-content-between text-muted mb-1" style="font-size: 0.78rem;">
                                        <span><i class="fa-solid fa-cloud-arrow-up text-primary me-1"></i> <strong>Carga:</strong> <?=$dt["feccar"];?></span>
                                    </div>
                                    <div class="d-flex justify-content-between text-muted" style="font-size: 0.78rem;">
                                        <span><i class="fa-solid fa-user-pen text-secondary me-1"></i> <strong>Registró:</strong> <?=$dt["idusu"];?></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Footer con Botones de Acción -->
                            <div class="card-footer bg-white border-0 pt-0 pb-3 px-3">
                                <hr class="mt-0 mb-3 text-muted opacity-25">
                                <div class="vid-actions-bar">
                                    <!-- Botón Ver Video -->
                                    <button type="button" class="btn btn-outline-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#mod<?=$dt["idvid"];?>" title="Ver Video">
                                        <i class="fa-solid fa-eye"></i> <span class="d-none d-sm-inline">Ver</span>
                                    </button>

                                    <!-- Botón Editar -->
                                    <a href="home.php?pg=<?=$pg;?>&idvid=<?=$dt["idvid"];?>&ope=edi" class="btn btn-outline-warning text-dark shadow-sm" title="Editar">
                                        <i class="fa-solid fa-pen-to-square"></i> <span class="d-none d-sm-inline">Editar</span>
                                    </a>

                                    <!-- Botón Activar Video -->
                                    <form action="home.php?pg=<?=$pg;?>" method="POST" class="m-0 p-0 w-100">
                                        <input type="hidden" name="idvid" value="<?=$dt["idvid"];?>">
                                        <input type="hidden" name="actvid" value="1">
                                        <input type="hidden" name="ope" value="act">
                                        <button type="submit" class="btn btn-outline-success w-100 shadow-sm" title="Activar Video en TV">
                                            <i class="fa-solid fa-toggle-off text-success"></i> <span class="d-none d-sm-inline">Activar</span>
                                        </button>
                                    </form>

                                    <!-- Botón Eliminar -->
                                    <a href="home.php?pg=<?=$pg;?>&idvid=<?=$dt["idvid"];?>&ope=eli" class="btn btn-outline-danger shadow-sm" title="Eliminar" onclick="return eli(this);">
                                        <i class="fa-solid fa-trash-can"></i> <span class="d-none d-sm-inline">Elim.</span>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Modal Reproductor de Video -->
                        <div class="modal fade" id="mod<?=$dt["idvid"];?>" tabindex="-1" aria-labelledby="modLabel<?=$dt["idvid"];?>" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                <div class="modal-content shadow-lg border-0" style="border-radius: 16px; overflow: hidden;">
                                    <div class="modal-header bg-dark text-white border-0 px-4 py-3">
                                        <h5 class="modal-title fs-5 d-flex align-items-center gap-2" id="modLabel<?=$dt["idvid"];?>">
                                            <i class="fa-solid fa-circle-play text-primary"></i>
                                            <span class="text-truncate" style="max-width: 650px;"><?=$dt["nomvid"];?></span>
                                        </h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body bg-black p-0 text-center">
                                        <video width="100%" controls preload="auto" style="max-height: 70vh; outline: none;">
                                            <source src="../vid/vis/<?=$dt["rutvid"];?>" type="video/mp4">
                                            Tu navegador no soporta reproducción de este video.
                                        </video>
                                    </div>
                                    <div class="modal-footer bg-light border-0 px-4 py-3 d-flex justify-content-between align-items-center">
                                        <div class="text-muted small">
                                            <i class="fa-regular fa-clock me-1"></i><?= !empty($dt["durvid"]) ? $dt["durvid"] : 'N/D'; ?> | 
                                            <i class="fa-solid fa-arrow-down-1-9 me-1"></i>Orden: <?=$dt["ordvid"];?>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <a href="home.php?pg=<?=$pg;?>&idvid=<?=$dt["idvid"];?>&ope=edi" class="btn btn-warning btn-sm">
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
            <tfoot>
                <tr>
                    <th>V. Inactivos</th>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Pestaña 3: Todos los Videos (Se visualiza al hacer clic en 'Todos') -->
    <div class="tab-pane fade" id="todos" role="tabpanel" aria-labelledby="todos-tab">
        <table id="tblTodos" class="table table-striped table-vid-cards" style="width:100%">
            <thead>
                <tr>
                    <th>Todos los Videos</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $datTodos = array_merge($datAct ?? [], $datInact ?? []);
                if($datTodos){ foreach ($datTodos as $dt) {
                    $esActivo = ($dt["actvid"] == "1");
                ?>
                <tr data-order="<?=$dt['idvid'];?>">
                    <td data-search="<?=$dt["idvid"];?> <?=$dt["nomvid"];?> <?=$dt["feccar"];?> <?=$dt["fecini"];?> <?=$dt["fecfin"];?> <?=$dt["idusu"];?> <?=$dt["durvid"];?> <?=$dt["ordvid"];?>">
                        
                        <!-- Tarjeta de Video -->
                        <div class="card h-100 shadow-sm border-0 vid-card">
                            
                            <!-- Video Thumbnail / Preview -->
                            <div class="vid-preview-box" <?= !$esActivo ? 'style="filter: grayscale(0.25);"' : ''; ?>>
                                <video preload="metadata" muted playsinline>
                                    <source src="../vid/vis/<?=$dt["rutvid"];?>#t=0.5" type="video/mp4">
                                </video>

                                <!-- Badges Superiores -->
                                <div class="vid-badges-top">
                                    <?php if($esActivo): ?>
                                        <span class="badge bg-success shadow-sm px-2 py-1">
                                            <i class="fa-solid fa-circle-check me-1"></i> Activo
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-danger shadow-sm px-2 py-1">
                                            <i class="fa-solid fa-circle-xmark me-1"></i> Inactivo
                                        </span>
                                    <?php endif; ?>
                                    <span class="badge vid-badge-blur px-2 py-1 shadow-sm">
                                        <i class="fa-solid fa-arrow-down-1-9 me-1 text-info"></i> Orden: <?=$dt["ordvid"];?>
                                    </span>
                                </div>

                                <!-- Botón central para ver / reproducir video -->
                                <div class="vid-play-btn" data-bs-toggle="modal" data-bs-target="#modTodos<?=$dt["idvid"];?>" title="Reproducir Video" role="button">
                                    <i class="fa-solid fa-play"></i>
                                </div>

                                <!-- Badges Inferiores (Duración y Peso) -->
                                <div class="vid-badges-bottom">
                                    <?php if(!empty($dt["durvid"])): ?>
                                        <span class="badge vid-badge-blur px-2 py-1">
                                            <i class="fa-regular fa-clock me-1 text-warning"></i> <?=$dt["durvid"];?>
                                        </span>
                                    <?php else: ?>
                                        <span></span>
                                    <?php endif; ?>

                                    <?php if(!empty($dt["pesvid"]) && $dt["pesvid"] > 0): ?>
                                        <span class="badge vid-badge-blur px-2 py-1">
                                            <i class="fa-solid fa-file-video me-1 text-primary"></i> <?=number_format($dt["pesvid"] / (1024 * 1024), 2);?> MB
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Cuerpo de la Tarjeta -->
                            <div class="card-body p-3 d-flex flex-column justify-content-between">
                                <div>
                                    <!-- Título del video con ID -->
                                    <h6 class="fw-bold text-dark mb-2 text-truncate" title="<?=$dt["idvid"];?> - <?=htmlspecialchars($dt["nomvid"]);?>" style="font-size: 1.05rem;">
                                        <span class="<?= $esActivo ? 'text-primary' : 'text-danger'; ?>">#<?=$dt["idvid"];?></span> - <?=$dt["nomvid"];?>
                                    </h6>

                                    <!-- Caja de Vigencia (Fechas) -->
                                    <div class="p-2 mb-3 rounded-3 bg-light border border-light-subtle d-flex justify-content-between align-items-center" style="font-size: 0.8rem;">
                                        <div>
                                            <span class="text-muted d-block" style="font-size: 0.72rem;">Publicación</span>
                                            <span class="fw-bold <?= $esActivo ? 'text-success' : 'text-secondary'; ?>">
                                                <i class="fa-regular fa-calendar-check me-1"></i><?=$dt["fecini"];?>
                                            </span>
                                        </div>
                                        <div class="text-muted"><i class="fa-solid fa-arrow-right-long mx-1"></i></div>
                                        <div class="text-end">
                                            <span class="text-muted d-block" style="font-size: 0.72rem;">Cierre</span>
                                            <span class="fw-bold text-danger">
                                                <i class="fa-regular fa-calendar-xmark me-1"></i><?=$dt["fecfin"];?>
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Metadatos de Carga y Registro -->
                                    <div class="d-flex justify-content-between text-muted mb-1" style="font-size: 0.78rem;">
                                        <span><i class="fa-solid fa-cloud-arrow-up text-primary me-1"></i> <strong>Carga:</strong> <?=$dt["feccar"];?></span>
                                    </div>
                                    <div class="d-flex justify-content-between text-muted" style="font-size: 0.78rem;">
                                        <span><i class="fa-solid fa-user-pen text-secondary me-1"></i> <strong>Registró:</strong> <?=$dt["idusu"];?></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Footer con Botones de Acción -->
                            <div class="card-footer bg-white border-0 pt-0 pb-3 px-3">
                                <hr class="mt-0 mb-3 text-muted opacity-25">
                                <div class="vid-actions-bar">
                                    <!-- Botón Ver Video -->
                                    <button type="button" class="btn btn-outline-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modTodos<?=$dt["idvid"];?>" title="Ver Video">
                                        <i class="fa-solid fa-eye"></i> <span class="d-none d-sm-inline">Ver</span>
                                    </button>

                                    <!-- Botón Editar -->
                                    <a href="home.php?pg=<?=$pg;?>&idvid=<?=$dt["idvid"];?>&ope=edi" class="btn btn-outline-warning text-dark shadow-sm" title="Editar">
                                        <i class="fa-solid fa-pen-to-square"></i> <span class="d-none d-sm-inline">Editar</span>
                                    </a>

                                    <?php if($esActivo): ?>
                                        <!-- Botón Desactivar -->
                                        <form action="home.php?pg=<?=$pg;?>" method="POST" class="m-0 p-0 w-100">
                                            <input type="hidden" name="idvid" value="<?=$dt["idvid"];?>">
                                            <input type="hidden" name="actvid" value="2">
                                            <input type="hidden" name="ope" value="act">
                                            <button type="submit" class="btn btn-outline-secondary w-100 shadow-sm" title="Desactivar Video">
                                                <i class="fa-solid fa-toggle-on text-primary"></i> <span class="d-none d-sm-inline">Desact.</span>
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <!-- Botón Activar -->
                                        <form action="home.php?pg=<?=$pg;?>" method="POST" class="m-0 p-0 w-100">
                                            <input type="hidden" name="idvid" value="<?=$dt["idvid"];?>">
                                            <input type="hidden" name="actvid" value="1">
                                            <input type="hidden" name="ope" value="act">
                                            <button type="submit" class="btn btn-outline-success w-100 shadow-sm" title="Activar Video en TV">
                                                <i class="fa-solid fa-toggle-off text-success"></i> <span class="d-none d-sm-inline">Activar</span>
                                            </button>
                                        </form>
                                    <?php endif; ?>

                                    <!-- Botón Eliminar -->
                                    <a href="home.php?pg=<?=$pg;?>&idvid=<?=$dt["idvid"];?>&ope=eli" class="btn btn-outline-danger shadow-sm" title="Eliminar" onclick="return eli(this);">
                                        <i class="fa-solid fa-trash-can"></i> <span class="d-none d-sm-inline">Elim.</span>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Modal Reproductor de Video -->
                        <div class="modal fade" id="modTodos<?=$dt["idvid"];?>" tabindex="-1" aria-labelledby="modTodosLabel<?=$dt["idvid"];?>" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                <div class="modal-content shadow-lg border-0" style="border-radius: 16px; overflow: hidden;">
                                    <div class="modal-header bg-dark text-white border-0 px-4 py-3">
                                        <h5 class="modal-title fs-5 d-flex align-items-center gap-2" id="modTodosLabel<?=$dt["idvid"];?>">
                                            <i class="fa-solid fa-circle-play <?= $esActivo ? 'text-primary' : 'text-danger'; ?>"></i>
                                            <span class="text-truncate" style="max-width: 650px;"><?=$dt["nomvid"];?></span>
                                        </h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body bg-black p-0 text-center">
                                        <video width="100%" controls preload="auto" style="max-height: 70vh; outline: none;">
                                            <source src="../vid/vis/<?=$dt["rutvid"];?>" type="video/mp4">
                                            Tu navegador no soporta reproducción de este video.
                                        </video>
                                    </div>
                                    <div class="modal-footer bg-light border-0 px-4 py-3 d-flex justify-content-between align-items-center">
                                        <div class="text-muted small">
                                            <i class="fa-regular fa-clock me-1"></i><?= !empty($dt["durvid"]) ? $dt["durvid"] : 'N/D'; ?> | 
                                            <i class="fa-solid fa-arrow-down-1-9 me-1"></i>Orden: <?=$dt["ordvid"];?>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <a href="home.php?pg=<?=$pg;?>&idvid=<?=$dt["idvid"];?>&ope=edi" class="btn btn-warning btn-sm">
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
            <tfoot>
                <tr>
                    <th>Todos los Videos</th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

