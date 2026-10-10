<?php
// Vista: Detalle de Documentación (dcpr)
// Requiere el controller concdcpr.php ya cargado (idem convdocg.php)
require_once('controllers/concdcpr.php');
?>

<?php echo titulo2("<i class='fa-solid fa-file-circle-check'></i> Detalle de Documentación", 0); ?>

<div class="conte container-fluid px-2 px-md-4 py-3">

	<form id="frm-dcpr" method="post" action="home.php?pg=<?php echo (int)$pg; ?>">

		<!-- Datos ocultos para el guardado -->
		<input type="hidden" name="id_usu" id="id_usu" value="<?php echo ($datAprendiz ? (int)$datAprendiz['idusu'] : 0); ?>">
		<input type="hidden" name="ndocumento" id="ndocumento" value="<?php echo htmlspecialchars($datAprendiz['ndocusu'] ?? ''); ?>">

		<!-- ============================================================
		     1. BLOQUE - CONTROL SUPERIOR (Seleccionar Formato)
		============================================================ -->
		<div class="card shadow-sm border-0 mb-3 rounded-3">
			<div class="card-body p-3 p-md-4">
				<div class="row align-items-end g-3">
					<div class="col-12 col-md-6 col-lg-4">
						<label for="for_dcpr" class="form-label fw-bold text-secondary">
							<i class="fa-solid fa-file-lines text-success me-1"></i> Seleccionar Formato
						</label>
						<select name="for_dcpr" id="for_dcpr" class="form-select form-select-lg border-success">
							<?php foreach ($formatos as $fmt): ?>
								<option value="<?php echo htmlspecialchars($fmt, ENT_QUOTES); ?>" <?php echo ($forSel == $fmt ? 'selected' : ''); ?>>
									<?php echo htmlspecialchars($fmt); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="col-12 col-md-6 col-lg-8">
						<div class="alert alert-warning border-0 shadow-sm mb-0 rounded-3" id="aviso-formato" role="alert">
							<i class="fa-solid fa-circle-info me-2"></i>
							Seleccione el formato a diligenciar. Las cláusulas se ajustan automáticamente según el formato elegido.
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- ============================================================
		     2. BLOQUE - BÚSQUEDA E IDENTIFICACIÓN DEL APRENDIZ
		============================================================ -->
		<div class="card shadow-sm border-0 mb-3 rounded-3">
			<div class="card-body p-3 p-md-4">
				<div class="row align-items-end g-3 mb-3">
					<div class="col-12 col-md-8 col-lg-6">
						<label for="q" class="form-label fw-bold text-secondary">
							<i class="fa-solid fa-id-card text-success me-1"></i> Documento o Ficha del Aprendiz
						</label>
						<div class="input-group">
							<input type="text" name="q" id="q" class="form-control form-control-lg border-success"
								   placeholder="Ingrese el número de documento o la ficha" autocomplete="off"
								   value="<?php echo htmlspecialchars($busqueda); ?>">
							<button type="submit" name="ope" value="buscar" class="btn btn-success btn-lg px-4" id="btn-buscar" title="Buscar aprendiz">
								<i class="fa-solid fa-magnifying-glass"></i> <span class="d-none d-sm-inline">Buscar</span>
							</button>
						</div>
						<div id="msg-busqueda" class="small mt-2 d-none"></div>
					</div>
				</div>

				<!-- Datos del aprendiz (solo lectura) -->
				<div id="bloque-aprendiz" class="<?php echo ($datAprendiz ? '' : 'd-none'); ?>">
					<div class="row g-3">
						<div class="col-12 col-sm-6 col-lg-3">
							<label class="form-label fw-bold text-secondary"><i class="fa-solid fa-user text-success me-1"></i> Nombre Completo</label>
							<input type="text" class="form-control bg-light" readonly
								   name="nom_apre" id="nom_apre" value="<?php echo htmlspecialchars($datAprendiz['nomusu'] ?? ''); ?>">
						</div>
						<div class="col-12 col-sm-6 col-lg-3">
							<label class="form-label fw-bold text-secondary"><i class="fa-solid fa-id-badge text-success me-1"></i> Tipo de Documento</label>
							<input type="text" class="form-control bg-light" readonly
								   name="tdoc_apre" id="tdoc_apre" value="<?php echo htmlspecialchars($datAprendiz['tdoc'] ?? ''); ?>">
						</div>
						<div class="col-12 col-sm-6 col-lg-3">
							<label class="form-label fw-bold text-secondary"><i class="fa-solid fa-graduation-cap text-success me-1"></i> Programa de Formación</label>
							<input type="text" class="form-control bg-light" readonly
								   name="programa" id="programa" value="<?php echo htmlspecialchars($datAprendiz['programa'] ?? ''); ?>">
						</div>
						<div class="col-12 col-sm-6 col-lg-3">
							<label class="form-label fw-bold text-secondary"><i class="fa-solid fa-building-columns text-success me-1"></i> Centro de Formación</label>
							<input type="text" class="form-control bg-light" readonly
								   name="centro" id="centro" value="<?php echo htmlspecialchars($datAprendiz['nomcen'] ?? ''); ?>">
						</div>
						<div class="col-12">
							<input type="hidden" name="idfic" id="idfic" value="<?php echo htmlspecialchars($datAprendiz['idfic'] ?? ''); ?>">
							<span class="small text-muted"><i class="fa-solid fa-lock me-1"></i> Campos calculados automáticamente a partir de la búsqueda.</span>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- ============================================================
		     3. BLOQUE - VALIDACIÓN LEGAL Y EDAD (Ley 1581 de 2012)
		============================================================ -->
		<div class="card shadow-sm border-0 mb-3 rounded-3 <?php echo ($datAprendiz ? '' : 'd-none'); ?>" id="bloque-legal">
			<div class="card-body p-3 p-md-4">
				<h6 class="fw-bold text-dark mb-3">
					<i class="fa-solid fa-scale-balanced text-success me-2"></i> Validación de Edad y Consentimiento
					<small class="text-muted fw-normal">(Ley 1581 de 2012 · Protección de Datos Personales)</small>
				</h6>

				<div class="row g-3">
					<div class="col-12 col-md-6">
						<label class="form-label fw-bold text-secondary"><i class="fa-solid fa-user-check text-success me-1"></i> Condición del Aprendiz</label>
						<div class="d-flex flex-wrap gap-4">
							<div class="form-check form-check-inline">
								<input class="form-check-input" type="radio" name="es_menor" id="menor_no" value="0" <?php echo ($detalleCargado && !(int)$detalleCargado['es_menor'] ? 'checked' : ''); ?>>
								<label class="form-check-label" for="menor_no"><i class="fa-solid fa-user text-success me-1"></i> Mayor de Edad</label>
							</div>
							<div class="form-check form-check-inline">
								<input class="form-check-input" type="radio" name="es_menor" id="menor_si" value="1" <?php echo ($detalleCargado && (int)$detalleCargado['es_menor'] ? 'checked' : ''); ?>>
								<label class="form-check-label" for="menor_si"><i class="fa-solid fa-child text-warning me-1"></i> Menor de Edad</label>
							</div>
						</div>
						<div class="small text-muted mt-1" id="hint-edad">
							<i class="fa-solid fa-circle-info me-1"></i> Se detecta automáticamente según el tipo de documento; el funcionario puede ajustarlo.
						</div>
					</div>
				</div>

				<!-- Formulario condicional: acudiente (solo menor de edad) -->
				<div id="bloque-acudiente" class="<?php echo ($detalleCargado && (int)$detalleCargado['es_menor'] ? '' : 'd-none'); ?>">
					<hr class="my-3">
					<h6 class="fw-bold text-dark mb-3">
						<i class="fa-solid fa-hand-holding-heart text-warning me-2"></i> Datos del Acudiente / Tutor Legal
					</h6>
					<div class="row g-3">
						<div class="col-12 col-md-4">
							<label for="nom_acu" class="form-label fw-bold text-secondary"><i class="fa-solid fa-user-pen text-success me-1"></i> Nombre del Acudiente</label>
							<input type="text" name="nom_acu" id="nom_acu" class="form-control" placeholder="Nombre completo del acudiente" autocomplete="off" value="<?php echo htmlspecialchars($detalleCargado['nom_acu'] ?? ''); ?>">
						</div>
						<div class="col-12 col-md-4">
							<label for="doc_acu" class="form-label fw-bold text-secondary"><i class="fa-solid fa-id-card text-success me-1"></i> Documento de Identidad</label>
							<input type="text" name="doc_acu" id="doc_acu" class="form-control" placeholder="Número de documento" autocomplete="off" value="<?php echo htmlspecialchars($detalleCargado['doc_acu'] ?? ''); ?>">
						</div>
						<div class="col-12 col-md-4">
							<label for="parentesco" class="form-label fw-bold text-secondary"><i class="fa-solid fa-people-roof text-success me-1"></i> Parentesco</label>
							<input type="text" name="parentesco" id="parentesco" class="form-control" placeholder="Ej: Padre, Madre, Tutor" autocomplete="off" value="<?php echo htmlspecialchars($detalleCargado['parentesco'] ?? ''); ?>">
						</div>
					</div>
				</div>

				<hr class="my-3">
				<div class="form-check form-check-lg">
					<input class="form-check-input" type="checkbox" name="consentimiento" id="consentimiento" value="1" <?php echo ($detalleCargado && (int)$detalleCargado['consentimiento'] ? 'checked' : ''); ?>>
					<label class="form-check-label fw-bold text-dark" for="consentimiento">
						<i class="fa-solid fa-check-double text-success me-1"></i>
						Consentimiento de Tratamiento de Datos Personales e Imagen Corporativa (Ley 1581 de 2012)
					</label>
					<div class="small text-muted mt-1">
						Autorizo al SENA para el tratamiento de mis datos personales e imagen, conforme a la política de protección de datos institucional.
					</div>
				</div>
			</div>
		</div>

		<!-- ============================================================
		     4. BLOQUE - CUERPO DEL DETALLE (Cláusulas)
		============================================================ -->
		<div class="card shadow-sm border-0 mb-3 rounded-3 <?php echo ($datAprendiz ? '' : 'd-none'); ?>" id="bloque-clausulas">
			<div class="card-body p-3 p-md-4">
				<h6 class="fw-bold text-dark mb-1">
					<i class="fa-solid fa-list-check text-success me-2"></i> Cláusulas del Reglamento del Aprendiz
				</h6>
				<p class="small text-muted mb-3" id="txt-formato">Seleccione las cláusulas que aplican al formato vigente.</p>

				<div id="lista-clausulas" class="row g-2">
					<?php
						$clausulasSelIds = array();
						if($detalleCargado && trim((string)$detalleCargado['clausulas']) !== ''){
							$clausulasSelIds = array_map('trim', array_filter(explode(',', (string)$detalleCargado['clausulas']), function($v){ return $v !== ''; }));
						}
					?>
					<?php foreach ($clausulas as $cl): ?>
						<div class="col-12">
							<div class="form-check">
								<input class="form-check-input clausula-item" type="checkbox" name="clausulas[]"
									   value="<?php echo htmlspecialchars($cl['id'], ENT_QUOTES); ?>" id="chk_<?php echo htmlspecialchars($cl['id'], ENT_QUOTES); ?>" <?php echo (in_array((string)$cl['id'], $clausulasSelIds, true) ? 'checked' : ''); ?>>
								<label class="form-check-label small" for="chk_<?php echo htmlspecialchars($cl['id'], ENT_QUOTES); ?>">
									<?php echo $cl['txt']; ?>
								</label>
							</div>
						</div>
					<?php endforeach; ?>
				</div>

				<div class="mt-3">
					<button type="button" class="btn btn-outline-success btn-sm px-3" id="btn-agregar-clausula" title="Agregar una cláusula o compromiso especial">
						<i class="fa-solid fa-plus me-1"></i> Agregar Cláusula o Compromiso Especial
					</button>
					<div id="bloque-clausula-extra" class="d-none mt-3">
						<label for="compromiso_esp" class="form-label fw-bold text-secondary">
							<i class="fa-solid fa-pen-to-square text-success me-1"></i> Compromiso Especial
						</label>
						<textarea name="compromiso_esp" id="compromiso_esp" rows="3" class="form-control"
								  placeholder="Redacte aquí las cláusulas o compromisos especiales adicionales..."></textarea>
					</div>
				</div>
			</div>
		</div>

		<!-- ============================================================
		     5. BLOQUE - PIE DE PÁGINA (Acciones)
		============================================================ -->
		<div class="card shadow-sm border-0 mb-3 rounded-3">
			<div class="card-body p-3 p-md-4">
				<div class="row align-items-end g-3">
					<div class="col-12 col-md-4">
						<label for="fec_dili" class="form-label fw-bold text-secondary">
							<i class="fa-regular fa-calendar text-success me-1"></i> Fecha de Diligenciamiento
						</label>
						<input type="date" name="fec_dili" id="fec_dili" class="form-control"
							   value="<?php echo ($detalleCargado && $detalleCargado['fec_dili']) ? htmlspecialchars($detalleCargado['fec_dili']) : date('Y-m-d'); ?>">
					</div>
					<div class="col-12 col-md-8">
						<div class="d-flex flex-wrap justify-content-md-end gap-2">
							<button type="submit" name="ope" value="guardar" class="btn btn-success btn-lg px-4" id="btn-guardar" title="Guardar los detalles diligenciados">
								<i class="fa-solid fa-floppy-disk me-2"></i> Guardar Cambios / Registrar Detalles
							</button>
							<a href="home.php?pg=2112<?php echo ($datAprendiz ? '&idusu=' . (int)$datAprendiz['idusu'] : ''); ?>&for_dcpr=<?php echo urlencode($forSel); ?>"
							   class="btn btn-outline-primary btn-lg px-4" id="btn-preview"
							   <?php echo ($datAprendiz ? 'data-idusu="' . (int)$datAprendiz['idusu'] . '"' : ''); ?>
							   title="Generar vista previa del documento">
								<i class="fa-solid fa-eye me-2"></i> Generar Vista Previa
							</a>
						</div>
					</div>
				</div>
			</div>
		</div>

	</form>
</div>