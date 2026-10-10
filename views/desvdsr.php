<?php include('controllers/descdsr.php'); ?>

<div class="conte">
	<?php echo titulo2("<i class='".$icono."'></i> Deserción y Llamados", 2); ?>

	<!-- Métricas (KPI) -->
	<div class="row my-4 g-4">
		<!-- Aprendices -->
		<div class="col-12 col-md-6 col-xl-4">
			<div class="kpi-card h-100 d-flex flex-column justify-content-between">
				<div class="d-flex align-items-center justify-content-between">
					<div>
						<span class="text-uppercase fw-bold kpi-label tx-plomo">Aprendices</span>
						<div class="d-flex align-items-baseline gap-2 mt-2">
							<span class="fw-bold kpi-number kpi-tx-total"><?= $cnt['total']; ?></span>
							<span class="kpi-sub">en sistema</span>
						</div>
					</div>
					<div class="kpi-icon-box kpi-icon-total"><i class="fa-solid fa-user-graduate fs-4"></i></div>
				</div>
				<div class="kpi-footer-strip">
					<span class="fw-semibold d-inline-flex align-items-center gap-1 kpi-foot tx-azul">
						<i class="fa-solid fa-users"></i> Registrados
					</span>
					<span class="kpi-foot tx-plomo">Mostrados</span>
				</div>
			</div>
		</div>

		<!-- En formación -->
		<div class="col-12 col-md-6 col-xl-4">
			<div class="kpi-card h-100 d-flex flex-column justify-content-between">
				<div class="d-flex align-items-center justify-content-between">
					<div>
						<span class="text-uppercase fw-bold kpi-label tx-plomo">En Formación</span>
						<div class="d-flex align-items-baseline gap-2 mt-2">
							<span class="fw-bold kpi-number kpi-tx-vigente"><?= $cnt['vigentes']; ?></span>
							<span class="kpi-sub">vigentes</span>
						</div>
					</div>
					<div class="kpi-icon-box kpi-icon-vigente"><i class="fa-solid fa-circle-check fs-4"></i></div>
				</div>
				<div class="kpi-footer-strip kpi-footer-vigente">
					<span class="fw-semibold d-inline-flex align-items-center gap-2 kpi-foot tx-plomo">
						<span class="pulse-dot"></span> Activos (100% OK)
					</span>
					<span class="kpi-foot tx-plomo">Sin novedad</span>
				</div>
			</div>
		</div>

		<!-- Con llamados -->
		<div class="col-12 col-md-6 col-xl-4">
			<div class="kpi-card h-100 d-flex flex-column justify-content-between">
				<div class="d-flex align-items-center justify-content-between">
					<div>
						<span class="text-uppercase fw-bold kpi-label tx-plomo">Con Llamados</span>
						<div class="d-flex align-items-baseline gap-2 mt-2">
							<span class="fw-bold kpi-number kpi-tx-llamado"><?= $cnt['llamados']; ?></span>
							<span class="kpi-sub">aprendices</span>
						</div>
					</div>
					<div class="kpi-icon-box kpi-icon-llamado"><i class="fa-solid fa-comment-dots fs-4"></i></div>
				</div>
				<div class="kpi-footer-strip kpi-footer-llamado">
					<span class="fw-semibold d-inline-flex align-items-center gap-1 kpi-foot tx-plomo">
						<i class="fa-solid fa-arrow-trend-up"></i> Verbales + Escritos
					</span>
					<span class="kpi-foot tx-plomo">Detectados</span>
				</div>
			</div>
		</div>

		<!-- Horas de inasistencia -->
		<div class="col-12 col-md-6 col-xl-4">
			<div class="kpi-card h-100 d-flex flex-column justify-content-between">
				<div class="d-flex align-items-center justify-content-between">
					<div>
						<span class="text-uppercase fw-bold kpi-label tx-plomo">Horas Inasistencia</span>
						<div class="d-flex align-items-baseline gap-2 mt-2">
							<span class="fw-bold kpi-number kpi-tx-horas"><?= $cnt['horas']; ?></span>
							<span class="kpi-sub">acumuladas</span>
						</div>
					</div>
					<div class="kpi-icon-box kpi-icon-horas"><i class="fa-solid fa-clock fs-4"></i></div>
				</div>
				<div class="kpi-footer-strip kpi-footer-horas">
					<span class="fw-semibold d-inline-flex align-items-center gap-1 kpi-foot tx-azul">
						<i class="fa-solid fa-triangle-exclamation"></i> Total registrado
					</span>
					<span class="kpi-foot tx-plomo">Suma global</span>
				</div>
			</div>
		</div>

		<!-- Novedades / Deserciones -->
		<div class="col-12 col-md-6 col-xl-4">
			<div class="kpi-card h-100 d-flex flex-column justify-content-between">
				<div class="d-flex align-items-center justify-content-between">
					<div>
						<span class="text-uppercase fw-bold kpi-label tx-plomo">Novedades / Deserciones</span>
						<div class="d-flex align-items-baseline gap-2 mt-2">
							<span class="fw-bold kpi-number kpi-tx-nove"><?= $cnt['novedades']; ?></span>
							<span class="kpi-sub">casos</span>
						</div>
					</div>
					<div class="kpi-icon-box kpi-icon-nove"><i class="fa-solid fa-ban fs-4"></i></div>
				</div>
				<div class="kpi-footer-strip kpi-footer-nove">
					<span class="fw-semibold d-inline-flex align-items-center gap-1 kpi-foot tx-azul">
						<i class="fa-solid fa-users-slash"></i> Deserción / Cancelado
					</span>
					<span class="kpi-foot tx-plomo">Registrados</span>
				</div>
			</div>
		</div>

		<!-- Más de 30 horas -->
		<div class="col-12 col-md-6 col-xl-4">
			<div class="kpi-card h-100 d-flex flex-column justify-content-between">
				<div class="d-flex align-items-center justify-content-between">
					<div>
						<span class="text-uppercase fw-bold kpi-label tx-plomo">Más de 30 Horas</span>
						<div class="d-flex align-items-baseline gap-2 mt-2">
							<span class="fw-bold kpi-number kpi-tx-treinta"><?= $cnt['treinta']; ?></span>
							<span class="kpi-sub">en riesgo</span>
						</div>
					</div>
					<div class="kpi-icon-box kpi-icon-treinta"><i class="fa-solid fa-exclamation-triangle fs-4"></i></div>
				</div>
				<div class="kpi-footer-strip kpi-footer-treinta">
					<span class="fw-semibold d-inline-flex align-items-center gap-1 kpi-foot tx-azul">
						<i class="fa-solid fa-circle-exclamation"></i> Riesgo
					</span>
					<span class="kpi-foot tx-plomo">Prioridad</span>
				</div>
			</div>
		</div>
	</div>

	<!-- Toolbar de Control y Filtros -->
	<div class="filter-toolbar-container">
		<!-- Izquierda: Píldoras y exportación -->
		<div class="toolbar-left-group">
			<!-- Píldoras de estado -->
			<div class="filter-pills-group" id="tabDes" role="tablist">
				<button class="btn-filter-pill active" id="todos-tab" data-bs-toggle="tab" data-bs-target="#todos" type="button" role="tab" aria-controls="todos" aria-selected="true" onclick="filterByTab('todos')">
					<span class="pill-dot pill-dot-todos"></span><span>Todos</span><span class="pill-count"><?= $cnt['total']; ?></span>
				</button>
				<button class="btn-filter-pill" id="envform-tab" data-bs-toggle="tab" data-bs-target="#envform" type="button" role="tab" aria-controls="envform" aria-selected="false" onclick="filterByTab('envform')">
					<span class="pill-dot pill-dot-form"></span><span>En formación</span><span class="pill-count"><?= count($datForm); ?></span>
				</button>
				<button class="btn-filter-pill" id="conllam-tab" data-bs-toggle="tab" data-bs-target="#conllam" type="button" role="tab" aria-controls="conllam" aria-selected="false" onclick="filterByTab('conllam')">
					<span class="pill-dot pill-dot-llam"></span><span>Con llamados</span><span class="pill-count"><?= count($datLlam); ?></span>
				</button>
				<button class="btn-filter-pill" id="mas30-tab" data-bs-toggle="tab" data-bs-target="#mas30" type="button" role="tab" aria-controls="mas30" aria-selected="false" onclick="filterByTab('mas30')">
					<span class="pill-dot pill-dot-30"></span><span>+30 horas</span><span class="pill-count"><?= count($dat30); ?></span>
				</button>
			</div>

			<div class="toolbar-divider d-none d-sm-block"></div>

			<!-- Botones de exportación -->
			<div class="toolbar-export-buttons">
				<button type="button" class="toolbar-export-btn" title="Copiar al portapapeles" onclick="triggerDTExport('copy')"><i class="fa-solid fa-copy"></i></button>
				<button type="button" class="toolbar-export-btn" title="Descargar CSV" onclick="triggerDTExport('csv')"><i class="fa-solid fa-file-csv"></i></button>
				<button type="button" class="toolbar-export-btn" title="Exportar a Excel" onclick="triggerDTExport('excel')"><i class="fa-solid fa-file-excel"></i></button>
			</div>
		</div>

		<!-- Derecha: Búsqueda por documento / ficha, orden y vista -->
		<div class="toolbar-search-sort-group">
			<div class="toolbar-search-box">
				<i class="fa-solid fa-id-card"></i>
				<input type="text" id="busqDoc" placeholder="No. documento">
			</div>
			<div class="toolbar-search-box">
				<i class="fa-solid fa-users"></i>
				<input type="text" id="busqFic" placeholder="Ficha">
			</div>
			<div class="toolbar-secondary-controls">
				<select class="toolbar-select-sort" id="customSortDes" onchange="syncCustomSort(this.value)">
					<option value="asc">Ascendente</option>
					<option value="desc">Descendente</option>
				</select>
				<div class="toolbar-view-toggle">
					<button type="button" class="toolbar-view-btn active" id="btnViewGrid" title="Vista Cuadrícula" onclick="switchCardView('grid')"><i class="fa-solid fa-grip"></i></button>
					<button type="button" class="toolbar-view-btn" id="btnViewList" title="Vista Lista" onclick="switchCardView('list')"><i class="fa-solid fa-bars"></i></button>
				</div>
			</div>
		</div>
	</div>

	<!-- Pestañas de estado -->
	<div class="tab-content" id="tabDesContent">
		<div class="tab-pane fade show active" id="todos" role="tabpanel" aria-labelledby="todos-tab">
			<table id="tblDesTodos" class="table table-striped table-vid-cards">
				<thead><tr><th>Todos los Aprendices</th></tr></thead>
				<tbody>
					<?php if ($datTodos) { foreach ($datTodos as $dt) echo filaAprendiz($dt, $pg); } ?>
				</tbody>
				<tfoot><tr><th>Todos los Aprendices</th></tr></tfoot>
			</table>
		</div>

		<div class="tab-pane fade" id="envform" role="tabpanel" aria-labelledby="envform-tab">
			<table id="tblDesForm" class="table table-striped table-vid-cards">
				<thead><tr><th>En Formación</th></tr></thead>
				<tbody>
					<?php if ($datForm) { foreach ($datForm as $dt) echo filaAprendiz($dt, $pg); } ?>
				</tbody>
				<tfoot><tr><th>En Formación</th></tr></tfoot>
			</table>
		</div>

		<div class="tab-pane fade" id="conllam" role="tabpanel" aria-labelledby="conllam-tab">
			<table id="tblDesLlam" class="table table-striped table-vid-cards">
				<thead><tr><th>Con Llamados</th></tr></thead>
				<tbody>
					<?php if ($datLlam) { foreach ($datLlam as $dt) echo filaAprendiz($dt, $pg); } ?>
				</tbody>
				<tfoot><tr><th>Con Llamados</th></tr></tfoot>
			</table>
		</div>

		<div class="tab-pane fade" id="mas30" role="tabpanel" aria-labelledby="mas30-tab">
			<table id="tblDes30" class="table table-striped table-vid-cards">
				<thead><tr><th>Más de 30 Horas</th></tr></thead>
				<tbody>
					<?php if ($dat30) { foreach ($dat30 as $dt) echo filaAprendiz($dt, $pg); } ?>
				</tbody>
				<tfoot><tr><th>Más de 30 Horas</th></tr></tfoot>
			</table>
		</div>
	</div>

	<!-- Modales (una sola vez por aprendiz) -->
	<?php
	$vistos = array();
	if ($datTodos) { foreach ($datTodos as $dt) {
		$id = (int)$dt['idusu'];
		if (isset($vistos[$id])) continue;
		$vistos[$id] = true;
		$hist = $mdsr->getHist($id);
		$llam = $mdsr->getLlam($id);
		echo modalLlamado($id, $dt['nomusu'], $dt['idfic'], $dt['nomfic'], $pg);
		echo modalHistorial($id, $dt['nomusu'], $dt['idfic'], $dt['nomfic'], $pg, $hist, $llam);
		echo modalNovedad($id, $dt['nomusu'], $dt['idfic'], $dt['nomfic'], $pg);
		echo modalActa($id, $dt['nomusu'], $dt['idfic'], $dt['nomfic'], $pg, $dcor);
		echo modalCarImg($id, $dt['nomusu'], $pg);
	} }
	?>
</div>