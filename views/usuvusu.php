<?php require_once 'controllers/usucusu.php'; ?>

<!-- Estilos para Botones de Exportación DataTables y Previsualización Dinámica de Foto -->
<style>
#example_wrapper .dt-buttons,
.dt-buttons-usu,
.dt-buttons-usu .dt-buttons {
	display: inline-flex !important;
	gap: 0.4rem !important;
	flex-wrap: wrap !important;
	margin-bottom: 0.5rem !important;
}
#example_wrapper .dataTables_filter {
	display: block !important;
}
.btn-export-dt {
	font-size: 0.82rem;
	font-weight: 600;
	transition: all 0.2s ease;
}
.avatar-preview-box {
	transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.avatar-preview-box:hover {
	transform: scale(1.03);
}
</style>

<div class="conte">
	<!-- Título Principal de la Vista -->
	<?php echo titulo2("<i class='".$icono."'></i> Usuarios", 1); ?>

	<!-- ========================================================================= -->
	<!-- MEJORA 2.1: DASHBOARD EN TIEMPO REAL INMEDIATAMENTE DEBAJO DEL TÍTULO    -->
	<!-- ========================================================================= -->
	<div class="row my-4 g-4">
		<!-- KPI: Usuarios Activos -->
		<div class="col-12 col-md-4">
			<div class="kpi-card h-100 d-flex flex-column justify-content-between">
				<div class="d-flex align-items-center justify-content-between">
					<div>
						<span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #006b29;">Usuarios Activos</span>
						<div class="d-flex align-items-baseline gap-2 mt-2">
							<span class="fw-bold" style="font-size: 32px; line-height: 1; color: #006b29;"><?= number_format($kpis['activos'] ?? 0); ?></span>
							<span style="font-size: 13px; color: rgba(0, 107, 41, 0.8);">habilitados</span>
						</div>
					</div>
					<div class="kpi-icon-box" style="background-color: #7afd88; color: #006b24;">
						<i class="fa-solid fa-user-check fs-4"></i>
					</div>
				</div>
				<div class="kpi-footer-strip">
					<span class="fw-semibold d-inline-flex align-items-center gap-2" style="font-size: 11px; color: #006b29;">
						<span class="pulse-dot"></span> <?= $kpis['porc_activos'] ?? 0; ?>% del total
					</span>
					<span style="font-size: 11px; color: #3e4a3d;">Acceso activo</span>
				</div>
			</div>
		</div>

		<!-- KPI: Usuarios Inactivos -->
		<div class="col-12 col-md-4">
			<div class="kpi-card h-100 d-flex flex-column justify-content-between">
				<div class="d-flex align-items-center justify-content-between">
					<div>
						<span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #ba1a1a;">Usuarios Inactivos</span>
						<div class="d-flex align-items-baseline gap-2 mt-2">
							<span class="fw-bold" style="font-size: 32px; line-height: 1; color: #0b1c30;"><?= number_format($kpis['inactivos'] ?? 0); ?></span>
							<span style="font-size: 13px; color: #3e4a3d;">bloqueados</span>
						</div>
					</div>
					<div class="kpi-icon-box" style="background-color: #ffdad6; color: #ba1a1a;">
						<i class="fa-solid fa-user-xmark fs-4"></i>
					</div>
				</div>
				<div class="kpi-footer-strip">
					<span class="d-inline-flex align-items-center gap-1" style="font-size: 11px; color: #ba1a1a;">
						<i class="fa-solid fa-lock" style="font-size: 12px;"></i> Inactivos / Desertores
					</span>
					<span class="fw-semibold" style="font-size: 11px; color: #3c627f;">Restringidos</span>
				</div>
			</div>
		</div>

		<!-- KPI: Usuarios con Ficha -->
		<div class="col-12 col-md-4">
			<div class="kpi-card h-100 d-flex flex-column justify-content-between">
				<div class="d-flex align-items-center justify-content-between">
					<div>
						<span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #3c627f;">Con Ficha Asignada</span>
						<div class="d-flex align-items-baseline gap-2 mt-2">
							<span class="fw-bold" style="font-size: 32px; line-height: 1; color: #0b1c30;"><?= number_format($kpis['con_ficha'] ?? 0); ?></span>
							<span style="font-size: 13px; color: #3e4a3d;">vinculados</span>
						</div>
					</div>
					<div class="kpi-icon-box" style="background-color: #e5eeff; color: #3c627f;">
						<i class="fa-solid fa-graduation-cap fs-4"></i>
					</div>
				</div>
				<div class="kpi-footer-strip">
					<span class="fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 11px; color: #3c627f;">
						<i class="fa-solid fa-id-card-clip" style="font-size: 12px;"></i> Programas Formativos
					</span>
					<span style="font-size: 11px; color: #3e4a3d;">Matriculados</span>
				</div>
			</div>
		</div>
	</div>

	<!-- ========================================================================= -->
	<!-- MEJORA 2: FORMULARIO DE USUARIO (REDISEÑO VISUAL ATRACTIVO Y RESPONSIVE)  -->
	<!-- Despliegue agradable al presionar '+' y edición 100% responsive           -->
	<!-- ========================================================================= -->
	<div class="inser mb-4">
		<form id="frmins" action="home.php?pg=<?=$pg;?>" method="POST" enctype="multipart/form-data" class="card border-0 shadow rounded-4 p-3 p-md-4 bg-white border-top border-4 border-success form-registro-usu form-registro-animado" style="<?= ($datOne ? 'display: block;' : 'display: none;'); ?>">
			<!-- Cabecera Institucional del Formulario -->
			<div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom flex-wrap gap-2">
				<div class="d-flex align-items-center gap-3">
					<div class="p-2 rounded-3 bg-success bg-opacity-10 text-success d-flex align-items-center justify-content-center" style="width: 46px; height: 46px;">
						<i class="fa-solid <?= ($datOne ? 'fa-user-pen' : 'fa-user-plus'); ?> fa-xl"></i>
					</div>
					<div>
						<h4 class="fw-bold text-dark mb-0 fs-5">
							<?= ($datOne ? 'Actualizar Información de Usuario' : 'Registrar Nuevo Usuario'); ?>
						</h4>
						<small class="text-muted">
							<?= ($datOne ? 'Modifique los campos necesarios para el usuario seleccionado' : 'Complete el formulario para incorporar un nuevo usuario al sistema'); ?>
						</small>
					</div>
				</div>
				<div>
					<?php if($datOne){ ?>
						<div class="d-flex align-items-center gap-2">
							<span class="badge bg-success bg-opacity-10 text-success border border-success px-3 py-2 fw-semibold">
								<i class="fa-solid fa-user-check me-1"></i> <?= htmlspecialchars($datOne[0]['nomusu']); ?>
							</span>
							<a href="home.php?pg=<?=$pg;?><?= ($nodocfil ? '&nodocfil='.urlencode($nodocfil) : ''); ?><?= ($idficfil ? '&idficfil='.urlencode($idficfil) : ''); ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3" title="Cancelar Edición">
								<i class="fa-solid fa-xmark me-1"></i> Cancelar
							</a>
						</div>
					<?php } else { ?>
						<button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" onclick="ocul(1, 0);" title="Ocultar Formulario">
							<i class="fa-solid fa-minus me-1"></i> Ocultar
						</button>
					<?php } ?>
				</div>
			</div>

			<div class="row g-3">
				<!-- Sección 1: Datos Personales -->
				<div class="col-12 mt-2">
					<div class="d-flex align-items-center gap-2 mb-1">
						<span class="badge rounded-circle bg-success text-white d-inline-flex align-items-center justify-content-center" style="width: 24px; height: 24px; font-size: 0.75rem;">1</span>
						<h6 class="text-dark fw-bold mb-0 fs-6">Información Personal y Documental</h6>
					</div>
					<hr class="mt-1 mb-2 text-muted opacity-25">
				</div>

				<div class="col-12 col-sm-6 col-lg-3">
					<label for="tdousu" class="form-label fw-semibold small text-dark mb-1">
						Tipo de Documento <span class="text-danger">*</span>
					</label>
					<select name="tdousu" id="tdousu" class="form-select form-select-sm" required>
						<?php if($tdos){ foreach ($tdos as $de) { ?>
							<option value="<?=$de['idval'];?>" <?= ($datOne && $datOne[0]['tdousu'] == $de['idval']) ? 'selected' : ''; ?>>
								<?=$de['nomval'];?>
							</option>
						<?php }} ?>
					</select>
				</div>

				<div class="col-12 col-sm-6 col-lg-3">
					<label for="ndocusu" class="form-label fw-semibold small text-dark mb-1">
						No. Documento <span class="text-danger">*</span>
					</label>
					<div class="input-group input-group-sm">
						<span class="input-group-text bg-light text-muted"><i class="fa-solid fa-id-card"></i></span>
						<input type="number" name="ndocusu" id="ndocusu" class="form-control form-control-sm" required value="<?=$datOne[0]['ndocusu'] ?? '';?>" placeholder="Ej: 1003456789">
					</div>
				</div>

				<div class="col-12 col-lg-6">
					<label for="nomusu" class="form-label fw-semibold small text-dark mb-1">
						Nombre Completo <span class="text-danger">*</span>
					</label>
					<div class="input-group input-group-sm">
						<span class="input-group-text bg-light text-muted"><i class="fa-solid fa-user"></i></span>
						<input type="text" name="nomusu" id="nomusu" maxlength="70" class="form-control form-control-sm text-uppercase" required value="<?=$datOne[0]['nomusu'] ?? '';?>" placeholder="NOMBRES Y APELLIDOS COMPLETOS">
					</div>
				</div>

				<div class="col-12 col-sm-6 col-lg-3">
					<label for="rhusu" class="form-label fw-semibold small text-dark mb-1">Grupo Sanguíneo (RH)</label>
					<select name="rhusu" id="rhusu" class="form-select form-select-sm">
						<?php if($rhs){ foreach ($rhs as $de) { ?>
							<option value="<?=$de['idval'];?>" <?= ($datOne && $datOne[0]['rhusu'] == $de['idval']) ? 'selected' : ''; ?>>
								<?=$de['nomval'];?>
							</option>
						<?php }} ?>
					</select>
				</div>

				<div class="col-12 col-sm-6 col-lg-3">
					<label for="genusu" class="form-label fw-semibold small text-dark mb-1">Género</label>
					<select name="genusu" id="genusu" class="form-select form-select-sm">
						<?php if($gen){ foreach ($gen as $de) { ?>
							<option value="<?=$de['idval'];?>" <?= ($datOne && $datOne[0]['genusu'] == $de['idval']) ? 'selected' : ''; ?>>
								<?=$de['nomval'];?>
							</option>
						<?php }} ?>
					</select>
				</div>

				<!-- MEJORA 2.2: FOTOGRAFÍA GRANDE, VISIBLE Y DINÁMICA PARA EDITAR EN CUALQUIER MOMENTO -->
				<?php 
					$fotoPreviewUsu = (!empty($datOne[0]['fotcan']) && file_exists($datOne[0]['fotcan'])) ? $datOne[0]['fotcan'] : 'img/user.jpg';
				?>
				<div class="col-12 col-lg-6">
					<div class="card border border-success border-opacity-25 rounded-4 p-3 bg-light shadow-sm h-100">
						<div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom">
							<span class="fw-bold small text-dark d-flex align-items-center gap-1">
								<i class="fa-solid fa-camera text-success"></i> Fotografía de Perfil
							</span>
							<span class="badge bg-light text-muted border small">PNG, JPG, JPEG, WEBP</span>
						</div>
						<div class="d-flex align-items-center gap-3 flex-wrap flex-sm-nowrap">
							<div class="position-relative flex-shrink-0 text-center">
								<div class="rounded-circle shadow border border-3 border-white overflow-hidden" style="width: 100px; height: 100px; background-color: #e2e8f0;">
									<img src="<?=$fotoPreviewUsu;?>" id="imgPreviewUsuForm" alt="Foto del Usuario" class="w-100 h-100" style="object-fit: cover; cursor: pointer;" onclick="abrirVisorFotoUsuario(this.src, '<?=htmlspecialchars(addslashes($datOne[0]['nomusu'] ?? 'Usuario'));?>');" title="Clic para ampliar fotografía" onerror="this.src='img/user.jpg';">
								</div>
								<button type="button" class="btn btn-sm btn-success position-absolute bottom-0 end-0 rounded-circle shadow p-0 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" onclick="document.getElementById('fotcan').click();" title="Cambiar fotografía">
									<i class="fa-solid fa-camera text-white fs-7"></i>
								</button>
							</div>
							<div class="flex-grow-1">
								<div class="small fw-semibold text-dark mb-1">
									<?= ($datOne ? 'Actualizar fotografía del usuario' : 'Subir foto del usuario'); ?>
								</div>
								<p class="text-muted small mb-2" style="font-size: 0.78rem;">
									Puede cambiar la imagen en cualquier momento con formatos válidos.
								</p>
								<div class="d-flex gap-2 flex-wrap">
									<label for="fotcan" class="btn btn-outline-success btn-sm rounded-pill px-3 fw-bold" style="cursor: pointer;">
										<i class="fa-solid fa-upload me-1"></i> Cambiar Imagen
									</label>
									<button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" onclick="abrirVisorFotoUsuario(document.getElementById('imgPreviewUsuForm').src, '<?=htmlspecialchars(addslashes($datOne[0]['nomusu'] ?? 'Usuario'));?>');" title="Ver foto ampliada">
										<i class="fa-solid fa-magnifying-glass-plus me-1"></i> Ver
									</button>
								</div>
								<input type="file" name="foto" id="fotcan" class="d-none" accept="image/png, image/jpeg, image/jpg, image/webp" onchange="previsualizarFotoUsuario(event)">
								<?php if($datOne && !empty($datOne[0]['fotcan'])){ ?>
									<input type="hidden" name="fotcan" value="<?=$datOne[0]['fotcan'];?>">
								<?php } ?>
								<div id="badgeFotoSeleccionada" class="d-none mt-2 alert alert-success py-1 px-2 small mb-0">
									<i class="fa-solid fa-check me-1"></i> <span id="txtNombreArchivoFoto"></span>
								</div>
							</div>
						</div>
					</div>
				</div>

				<!-- Sección 2: Contacto y Seguridad -->
				<div class="col-12 mt-4">
					<div class="d-flex align-items-center gap-2 mb-1">
						<span class="badge rounded-circle bg-success text-white d-inline-flex align-items-center justify-content-center" style="width: 24px; height: 24px; font-size: 0.75rem;">2</span>
						<h6 class="text-dark fw-bold mb-0 fs-6">Contacto y Credenciales de Acceso</h6>
					</div>
					<hr class="mt-1 mb-2 text-muted opacity-25">
				</div>

				<div class="col-12 col-sm-6 col-lg-4">
					<label for="emausu" class="form-label fw-semibold small text-dark mb-1">Correo Electrónico</label>
					<div class="input-group input-group-sm">
						<span class="input-group-text bg-light text-muted"><i class="fa-solid fa-envelope"></i></span>
						<input type="email" name="emausu" id="emausu" maxlength="70" class="form-control form-control-sm" value="<?=$datOne[0]['emausu'] ?? '';?>" placeholder="ejemplo@misena.edu.co">
					</div>
				</div>

				<div class="col-12 col-sm-6 col-lg-4">
					<label for="telcan" class="form-label fw-semibold small text-dark mb-1">No. Telefónico</label>
					<div class="input-group input-group-sm">
						<span class="input-group-text bg-light text-muted"><i class="fa-solid fa-phone"></i></span>
						<input type="number" name="telcan" id="telcan" max="9999999999999" class="form-control form-control-sm" value="<?=$datOne[0]['telcan'] ?? '';?>" placeholder="Ej: 3123456789">
					</div>
				</div>

				<div class="col-12 col-lg-4">
					<label for="pasusu" class="form-label fw-semibold small text-dark mb-1 d-flex justify-content-between">
						<span>Contraseña <?= ($datOne ? '<small class="text-muted fw-normal">(Dejar en blanco para conservar)</small>' : '<span class="text-danger">*</span>'); ?></span>
					</label>
					<div class="input-group input-group-sm">
						<span class="input-group-text bg-light text-muted"><i class="fa-solid fa-lock"></i></span>
						<input type="password" name="pasusu" id="pasusu" maxlength="70" class="form-control form-control-sm" <?= ($datOne ? '' : 'required'); ?> placeholder="<?= ($datOne ? '••••••••' : 'Ingrese contraseña'); ?>">
						<button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('pasusu', this);" title="Mostrar/Ocultar contraseña">
							<i class="fa-solid fa-eye"></i>
						</button>
					</div>
				</div>

				<!-- Sección 3: Rol, Centro y Estado -->
				<div class="col-12 mt-4">
					<div class="d-flex align-items-center gap-2 mb-1">
						<span class="badge rounded-circle bg-success text-white d-inline-flex align-items-center justify-content-center" style="width: 24px; height: 24px; font-size: 0.75rem;">3</span>
						<h6 class="text-dark fw-bold mb-0 fs-6">Perfil en el Sistema y Centro de Formación</h6>
					</div>
					<hr class="mt-1 mb-2 text-muted opacity-25">
				</div>

				<div class="col-12 col-sm-6 col-lg-4">
					<label for="idper" class="form-label fw-semibold small text-dark mb-1">
						Perfil Principal <span class="text-danger">*</span>
					</label>
					<select name="idper" id="idper" class="form-select form-select-sm" required>
						<?php if($dpe){ foreach ($dpe as $de) { ?>
							<option value="<?=$de['idper'];?>" <?= ($datOne && $datOne[0]['idper'] == $de['idper']) ? 'selected' : ''; ?>>
								<?=$de['nomper'];?>
							</option>
						<?php }} ?>
					</select>
				</div>

				<div class="col-12 col-sm-6 col-lg-4">
					<label for="idcen" class="form-label fw-semibold small text-dark mb-1">
						Centro de Formación <span class="text-danger">*</span>
					</label>
					<select name="idcen" id="idcen" class="form-select form-select-sm" required>
						<?php if($dce){ foreach ($dce as $de) { ?>
							<option value="<?=$de['idcen'];?>" <?= ($datOne && $datOne[0]['idcen'] == $de['idcen']) ? 'selected' : ''; ?>>
								<?=$de['nomcen'];?>
							</option>
						<?php }} ?>
					</select>
				</div>

				<div class="col-12 col-sm-6 col-lg-2">
					<label for="noca" class="form-label fw-semibold small text-dark mb-1">No. Candidato</label>
					<input type="text" name="noca" id="noca" maxlength="3" class="form-control form-control-sm" value="<?=$datOne[0]['noca'] ?? '';?>" placeholder="000">
				</div>

				<div class="col-12 col-sm-6 col-lg-2">
					<label for="actusu" class="form-label fw-semibold small text-dark mb-1">
						Estado <span class="text-danger">*</span>
					</label>
					<select name="actusu" id="actusu" class="form-select form-select-sm" required>
						<option value="1" <?= ($datOne && $datOne[0]['actusu'] == 1) ? 'selected' : ''; ?>>Activo</option>
						<option value="2" <?= ($datOne && $datOne[0]['actusu'] == 2) ? 'selected' : ''; ?>>Inactivo</option>
						<option value="3" <?= ($datOne && $datOne[0]['actusu'] == 3) ? 'selected' : ''; ?>>Desertó</option>
					</select>
				</div>

				<!-- Botones de Acción Responsivos -->
				<div class="col-12 mt-4 pt-2 border-top d-flex flex-column flex-sm-row justify-content-end align-items-center gap-2">
					<?php if($datOne){ ?>
						<a href="home.php?pg=<?=$pg;?><?= ($nodocfil ? '&nodocfil='.urlencode($nodocfil) : ''); ?><?= ($idficfil ? '&idficfil='.urlencode($idficfil) : ''); ?>" class="btn btn-secondary px-4 rounded-pill w-100 w-sm-auto">
							<i class="fa-solid fa-arrow-rotate-left me-1"></i> Cancelar Edición
						</a>
					<?php } else { ?>
						<button type="button" class="btn btn-outline-secondary px-4 rounded-pill w-100 w-sm-auto" onclick="ocul(1, 0);">
							<i class="fa-solid fa-xmark me-1"></i> Cancelar
						</button>
					<?php } ?>
					<button type="submit" class="btn btn-success px-4 rounded-pill fw-bold shadow-sm w-100 w-sm-auto" style="background-color: #00af00; border-color: #00af00;">
						<i class="fa-solid <?= ($datOne ? 'fa-floppy-disk' : 'fa-user-check'); ?> me-1"></i>
						<?= ($datOne ? 'Guardar Cambios' : 'Registrar Usuario'); ?>
					</button>
					<input type="hidden" name="opera" value="save">
					<input type="hidden" name="idusu" value="<?=$datOne[0]['idusu'] ?? '';?>">
					<?php if($nodocfil){ ?><input type="hidden" name="nodocfil" value="<?=htmlspecialchars($nodocfil);?>"><?php } ?>
					<?php if($idficfil){ ?><input type="hidden" name="idficfil" value="<?=htmlspecialchars($idficfil);?>"><?php } ?>
				</div>
			</div>
		</form>
	</div>
</div>

<!-- ========================================================================= -->
<!-- MEJORA 2.1 & 2.2: BUSCADOR AVANZADO Y FILTRADO MULTICRITERIO RESPONSIVE   -->
<!-- ========================================================================= -->
<div class="card border-0 shadow-sm rounded-4 p-3 p-md-4 mb-4 bg-white">
	<div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
		<h3 class="fs-5 fw-bold text-success mb-0">
			<i class="fa-solid fa-magnifying-glass-chart me-2"></i> Búsqueda de Usuarios
		</h3>
		<span class="badge bg-light text-muted border px-2 py-1 small">
			<i class="fa-solid fa-circle-info me-1 text-primary"></i> Busque por documento, nombre, correo o ficha
		</span>
	</div>

	<form name="frm1" id="frmBuscarUsu" action="home.php?pg=<?=$pg;?>" method="POST">
		<div class="row g-3 align-items-end">
			<div class="col-12 col-lg-5">
				<label for="txtBuscador" class="form-label fw-semibold small text-dark mb-1">
					<i class="fa-solid fa-user-tag me-1 text-success"></i> Documento, Nombre, Apellido o Correo
				</label>
				<div class="input-group">
					<span class="input-group-text bg-light text-muted"><i class="fa-solid fa-keyboard"></i></span>
					<input type="text" name="nodocfil" id="txtBuscador" class="form-control" value="<?=htmlspecialchars($nodocfil ?? '');?>" placeholder="Ej: 1003456789, Robinson Rincon, @misena...">
				</div>
			</div>
			
			<div class="col-12 col-lg-5">
				<label for="txtFichaFiltro" class="form-label fw-semibold small text-dark d-flex justify-content-between mb-1">
					<span><i class="fa-solid fa-graduation-cap me-1 text-success"></i> Ficha o Programa</span>
					<small class="text-muted fw-normal"><i class="fa-solid fa-i-cursor me-1"></i>Escriba o elija</small>
				</label>
				<div class="input-group ficha-filtro-group">
					<span class="input-group-text bg-light text-muted"><i class="fa-solid fa-hashtag"></i></span>
					<input type="text" name="idficfil" id="txtFichaFiltro" list="dl_fichas_filtro" class="form-control" value="<?=htmlspecialchars($idficfil ?? '');?>" placeholder="No. de ficha o nombre..." autocomplete="off" oninput="sincronizarFichaInputASelect(this.value)">
					<select id="comboboxFicha" class="form-select select-ficha-responsive" onchange="sincronizarFichaSelectAInput(this)">
						<option value="0">-- Elegir lista --</option>
						<?php if($dfi){ foreach ($dfi as $de) { 
							$txtDisplay = $de['idfic'] . ' - ' . $de['nomfic'];
							$isSel = (isset($idficfil) && ($idficfil == $de['idfic'] || $idficfil == $txtDisplay || strpos($idficfil, (string)$de['idfic']) === 0));
						?>
							<option value="<?=$de['idfic'];?>" data-full="<?=$txtDisplay;?>" <?= $isSel ? 'selected' : ''; ?>>
								<?=$de['idfic'];?> - <?=$de['nomfic'];?> (<?=$de['nomval'];?>)
							</option>
						<?php }} ?>
					</select>
				</div>
				<datalist id="dl_fichas_filtro">
					<?php if($dfi){ foreach ($dfi as $de) { ?>
						<option value="<?=$de['idfic'];?> - <?=$de['nomfic'];?>"><?=$de['idfic'];?> - <?=$de['nomfic'];?> (<?=$de['nomval'];?>)</option>
					<?php }} ?>
				</datalist>
			</div>

			<div class="col-12 col-lg-2 d-flex gap-2">
				<button type="submit" class="btn btn-success flex-grow-1 fw-bold" style="background-color: #00af00; border-color: #00af00;">
					<i class="fa-solid fa-magnifying-glass me-1"></i> Buscar
				</button>
				<?php if($nodocfil || $idficfil){ ?>
					<a href="home.php?pg=<?=$pg;?>" class="btn btn-outline-secondary" title="Limpiar Filtros">
						<i class="fa-solid fa-rotate-left"></i>
					</a>
				<?php } ?>
			</div>
		</div>
	</form>
</div>

<!-- ========================================================================= -->
<!-- MEJORA 1: CONTROL DE CARGA INTELIGENTE Y LISTADO DE USUARIOS              -->
<!-- ========================================================================= -->
<?php
$modalesHtml = '';

if ($fichaSobrecargada) {
?>
	<!-- Panel para programas o fichas que superan los 100 usuarios (ej. Contratistas con 448 usuarios) -->
	<div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 mb-4 bg-white panel-curso-masivo position-relative overflow-hidden" style="border-left: 5px solid #f59e0b !important;">
		<div class="d-flex align-items-start gap-3 flex-column flex-sm-row mb-3">
			<div class="icon-curso-masivo bg-warning bg-opacity-10 text-warning p-3 rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 60px; height: 60px;">
				<i class="fa-solid fa-users-viewfinder fa-2x"></i>
			</div>
			<div class="flex-grow-1">
				<div class="d-flex align-items-center gap-2 flex-wrap mb-1">
					<span class="badge bg-warning text-dark px-3 py-1 fw-bold rounded-pill">
						<i class="fa-solid fa-triangle-exclamation me-1"></i> Programa con alta densidad de usuarios
					</span>
					<span class="badge bg-light text-muted border px-2 py-1 small">
						<i class="fa-solid fa-users me-1 text-primary"></i> <?= number_format($totalUsuariosFicha); ?> usuarios vinculados
					</span>
				</div>
				<h4 class="fw-bold text-dark mb-1 fs-5">
					<?= htmlspecialchars($infoFichaSobrecargada['nomfic'] ?? $idficfil); ?>
					<?php if(!empty($infoFichaSobrecargada['idfic'])){ ?>
						<span class="text-success fw-bold">(Ficha: <?= htmlspecialchars($infoFichaSobrecargada['idfic']); ?>)</span>
					<?php } ?>
				</h4>
				<p class="text-muted small mb-0">
					<?php if($busquedaDemasiadoAmplia){ ?>
						La búsqueda arrojó <strong>más de 100 usuarios</strong>. Para evitar errores en la carga masiva y optimizar el rendimiento, por favor especifique el documento o nombre específico de la persona.
					<?php } else { ?>
						Este programa o ficha cuenta con <strong><?= number_format($totalUsuariosFicha); ?> usuarios registrados</strong> (superior al límite de 100 para listado directo). Para garantizar una respuesta instantánea y evitar errores de sobrecarga, utilice el siguiente buscador para localizar a una persona específica de este curso o programa.
					<?php } ?>
				</p>
			</div>
		</div>

		<!-- Formulario específico de búsqueda dentro de este curso/programa -->
		<div class="bg-light p-3 p-md-4 rounded-4 border border-warning border-opacity-25 mt-2">
			<form action="home.php?pg=<?=$pg;?>" method="POST" class="w-100">
				<input type="hidden" name="idficfil" value="<?= htmlspecialchars($idficfil); ?>">
				<label for="txtDocFiltroEspecifico" class="form-label fw-bold text-dark small mb-2 d-flex align-items-center justify-content-between">
					<span><i class="fa-solid fa-user-magnifying-glass text-success me-1"></i> Buscar persona específica dentro de este programa</span>
					<span class="badge bg-white text-muted border small"><i class="fa-solid fa-filter text-warning me-1"></i> Filtro enfocado</span>
				</label>
				<div class="input-group input-group-lg shadow-sm rounded-pill overflow-hidden border">
					<span class="input-group-text bg-white border-0 ps-3 text-muted">
						<i class="fa-solid fa-keyboard text-success"></i>
					</span>
					<input type="text" name="nodocfil" id="txtDocFiltroEspecifico" class="form-control border-0 px-2 fs-6" placeholder="Ingrese número de documento, nombres o correo electrónico..." autofocus required value="<?= htmlspecialchars($nodocfil ?? ''); ?>">
					<button type="submit" class="btn btn-success px-4 fw-bold" style="background-color: #00af00; border-color: #00af00;">
						<i class="fa-solid fa-magnifying-glass me-1"></i> Buscar en este Curso
					</button>
				</div>
				<div class="d-flex justify-content-between align-items-center mt-3 px-2 flex-wrap gap-2">
					<small class="text-muted">
						<i class="fa-solid fa-circle-check text-success me-1"></i> La búsqueda consultará de forma segura entre los <?= number_format($totalUsuariosFicha); ?> usuarios de este programa.
					</small>
					<a href="home.php?pg=<?=$pg;?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
						<i class="fa-solid fa-arrow-rotate-left me-1"></i> Cambiar de ficha o programa
					</a>
				</div>
			</form>
		</div>
	</div>
<?php
} elseif ($dat && count($dat) > 0) {
?>
	<!-- ========================================================================= -->
	<!-- MEJORA 3.1: BARRA RESPONSIVE DE ASIGNACIÓN MASIVA DE PERFILES            -->
	<!-- ========================================================================= -->
	<div id="panelAccionesMasivas" class="card border-0 shadow-sm rounded-4 p-3 mb-3 bg-white d-none panel-masivo-animado" style="border-left: 5px solid #00af00 !important; background: linear-gradient(135deg, #f0fdf4 0%, #ffffff 100%) !important;">
		<div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
			<div class="d-flex align-items-center gap-3">
				<span class="badge rounded-pill bg-success px-3 py-2 fs-7 shadow-sm" id="txtContadorSeleccionados">
					<i class="fa-solid fa-users-gear me-1"></i> <strong id="numSeleccionados">0</strong> usuario(s) seleccionado(s)
				</span>
				<span class="small text-muted d-none d-md-inline">
					<i class="fa-solid fa-circle-check text-success me-1"></i> Seleccione los usuarios para asignar roles/perfiles en lote con feedback en tiempo real
				</span>
			</div>
			<div class="d-flex gap-2">
				<button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" onclick="deseleccionarTodosUsuarios();">
					<i class="fa-solid fa-xmark me-1"></i> Limpiar selección
				</button>
				<button type="button" class="btn btn-success btn-sm rounded-pill px-4 fw-bold shadow-sm" style="background-color: #00af00; border-color: #00af00;" onclick="abrirModalAsignacionMasiva();">
					<i class="fa-solid fa-id-card-clip me-1"></i> Asignar Perfiles en Lote
				</button>
			</div>
		</div>
	</div>

	<div class="card border-0 shadow-sm rounded-4 p-3 bg-white mb-5">
		<div class="d-flex justify-content-between align-items-center mb-3 px-2 flex-wrap gap-2">
			<div>
				<h5 class="fw-bold mb-0 text-dark fs-6">
					<i class="fa-solid fa-table-list text-success me-2"></i> Usuarios Encontrados
				</h5>
				<small class="text-muted">Se encontraron <strong><?= count($dat); ?></strong> usuario(s) que coinciden con su búsqueda.</small>
			</div>
			<div class="d-flex align-items-center gap-2 flex-wrap">
				<!-- MEJORA 2.1: BOTONES DEL DATATABLE (COPIAR, CSV Y EXCEL) -->
				<div class="btn-group btn-group-sm shadow-sm" role="group" aria-label="Exportar datos de usuarios">
					<button type="button" class="btn btn-outline-secondary px-3 fw-semibold btn-export-dt" onclick="exportarUsuariosDT('copy');" title="Copiar registros al portapapeles">
						<i class="fa-solid fa-copy me-1"></i> Copiar
					</button>
					<button type="button" class="btn btn-outline-primary px-3 fw-semibold btn-export-dt" onclick="exportarUsuariosDT('csv');" title="Descargar archivo en formato CSV">
						<i class="fa-solid fa-file-csv me-1 text-primary"></i> CSV
					</button>
					<button type="button" class="btn btn-outline-success px-3 fw-semibold btn-export-dt" onclick="exportarUsuariosDT('excel');" title="Descargar archivo en formato Excel (.xlsx)">
						<i class="fa-solid fa-file-excel me-1 text-success"></i> Excel
					</button>
				</div>

				<button type="button" class="btn btn-outline-success btn-sm rounded-pill px-3" onclick="seleccionarTodosVisible(true);" title="Marcar todos los usuarios de la lista">
					<i class="fa-solid fa-check-double me-1"></i> Seleccionar Todos
				</button>
				<?php if($idficfil){ ?>
					<span class="badge bg-success bg-opacity-10 text-success border border-success px-3 py-2">
						<i class="fa-solid fa-filter me-1"></i> Ficha: <?= htmlspecialchars($idficfil); ?>
					</span>
				<?php } ?>
			</div>
		</div>

		<div class="table-responsive table-responsive-usu">
			<table id="example" class="table table-striped table-hover align-middle w-100 table-usu-custom" style="width:100%">
				<thead class="table-light">
					<tr>
						<th style="width: 4%;" class="text-center" data-orderable="false">
							<input type="checkbox" id="chkSelectAllUsu" class="form-check-input" title="Seleccionar/Deseleccionar todos" onchange="toggleSelectAllUsuarios(this);">
						</th>
						<th style="width: 13%;">Perfil</th>
						<th style="width: 8%;" class="text-center">Foto</th>
						<th style="width: 30%;">Datos de Usuario</th>
						<th style="width: 20%;">Contacto</th>
						<th style="width: 10%;" class="text-center">Estado</th>
						<th style="width: 15%; text-align: center;">Acciones</th>
					</tr>
				</thead>
				<tbody>
				<?php
					foreach($dat as $d){
						// Recolectar modales para renderizarlos FUERA de la tabla (evita rotura de DataTable)
						$modalesHtml .= modmulsel($d['idusu'], $d['nomusu'], $pg, $nodocfil, $idficfil);
						$modalesHtml .= modsimsel($d['idusu'], $d['nomusu'], $pg, $nodocfil, $idficfil);

						$esActivo = ($d['actusu'] == 1);
						$foto = (!empty($d['fotcan']) && file_exists($d['fotcan'])) ? $d['fotcan'] : 'img/user.jpg';
				?>
					<tr id="row-usu-<?=$d['idusu'];?>">
						<!-- Checkbox Selección Masiva (Mejora 3.1) -->
						<td class="text-center">
							<input type="checkbox" class="form-check-input chk-usu-select" value="<?=$d['idusu'];?>" data-nombre="<?=htmlspecialchars($d['nomusu']);?>" data-perfil="<?=htmlspecialchars($d['nomper']);?>" onchange="actualizarSeleccionUsuarios();">
						</td>

						<!-- Perfil Principal -->
						<td>
							<span class="badge bg-light text-dark border px-2 py-1 fw-semibold badge-perfil-usu-<?=$d['idusu'];?> text-wrap text-start d-inline-block">
								<i class="fa-solid fa-user-shield me-1 text-success"></i><span class="lbl-perfil-<?=$d['idusu'];?>"><?=htmlspecialchars($d['nomper']);?></span>
							</span>
						</td>

						<!-- Foto -->
						<td class="text-center">
							<img src="<?=$foto;?>" width="46" height="46" style="object-fit:cover; border-radius:50%; border:2px solid #e0e0e0;" alt="Foto" loading="lazy" onerror="this.src='img/user.jpg';" />
						</td>

						<!-- Datos de Usuario -->
						<td>
							<div class="fw-bold text-dark fs-6 user-name-cell"><?=htmlspecialchars($d['nomusu']);?></div>
							<div class="small text-muted mt-1">
								<span class="me-2"><strong class="text-secondary">Doc:</strong> <?=htmlspecialchars($d['ndocusu']);?></span>
								<?php if(!empty($d['tdoc'])){ ?>
									<span class="badge bg-secondary bg-opacity-10 text-secondary border fs-8"><?=htmlspecialchars($d['tdoc']);?></span>
								<?php } ?>
							</div>
							
							<!-- Fichas Asociadas -->
							<div class="mt-2 user-fichas-cell">
								<?php 
								if(!empty($d['fichas_info'])){
									$fichasArr = explode('||', $d['fichas_info']);
									foreach($fichasArr as $ficItem){
										echo '<span class="badge bg-success bg-opacity-10 text-success border border-success me-1 mb-1 text-wrap text-start d-inline-block" style="font-size: 0.75rem; word-break: break-word;"><i class="fa-solid fa-graduation-cap me-1"></i>' . htmlspecialchars($ficItem) . '</span> ';
									}
								} else {
									echo '<span class="text-muted small fst-italic"><i class="fa-solid fa-circle-exclamation me-1"></i>Sin ficha asociada</span>';
								}
								?>
							</div>

							<?php if(!empty($d['nomcen'])){ ?>
								<div class="small text-muted mt-1 user-center-cell">
									<i class="fa-solid fa-building-columns me-1 text-secondary"></i><?=htmlspecialchars($d['nomcen']);?>
								</div>
							<?php } ?>
						</td>

						<!-- Contacto -->
						<td>
							<div class="small mb-1 text-break user-email-cell" style="word-break: break-all;">
								<?php if(!empty($d['emausu'])){ ?>
									<a href="mailto:<?=htmlspecialchars($d['emausu']);?>" class="text-decoration-none text-dark">
										<i class="fa-solid fa-envelope me-1 text-success"></i><?=htmlspecialchars($d['emausu']);?>
									</a>
								<?php } else { ?>
									<span class="text-muted fst-italic">Sin correo</span>
								<?php } ?>
							</div>
							<div class="small">
								<?php if(!empty($d['telcan'])){ ?>
									<i class="fa-solid fa-phone me-1 text-secondary"></i><?=htmlspecialchars($d['telcan']);?>
								<?php } else { ?>
									<span class="text-muted fst-italic">Sin teléfono</span>
								<?php } ?>
							</div>
						</td>

						<!-- ESTADO DEL USUARIO: SELECT CON ICONOS (ACTIVO, INACTIVO, DESERTÓ) -->
						<td class="text-center" style="min-width: 140px;">
							<div class="d-inline-flex align-items-center position-relative estado-select-wrapper">
								<span class="position-absolute start-0 ps-2 icon-estado-indicador" id="icon-estado-<?=$d['idusu'];?>">
									<?php if($d['actusu'] == 1){ ?>
										<i class="fa-solid fa-circle-check text-success"></i>
									<?php } else if($d['actusu'] == 3){ ?>
										<i class="fa-solid fa-person-walking-arrow-right text-warning"></i>
									<?php } else { ?>
										<i class="fa-solid fa-circle-xmark text-danger"></i>
									<?php } ?>
								</span>
								<?php
									$selectClass = 'select-estado-activo';
									if($d['actusu'] == 2) $selectClass = 'select-estado-inactivo';
									else if($d['actusu'] == 3) $selectClass = 'select-estado-deserto';
								?>
								<select class="form-select form-select-sm select-estado-usuario fw-bold rounded-pill ps-4 pe-3 shadow-sm <?=$selectClass;?>" 
										id="sel-estado-<?=$d['idusu'];?>"
										data-prev="<?=$d['actusu'];?>"
										onchange="cambiarEstadoUsuario(<?=$d['idusu'];?>, this.value, this);"
										title="Cambiar estado del usuario">
									<option value="1" <?= ($d['actusu'] == 1 ? 'selected' : ''); ?>>Activo</option>
									<option value="2" <?= ($d['actusu'] == 2 ? 'selected' : ''); ?>>Inactivo</option>
									<option value="3" <?= ($d['actusu'] == 3 ? 'selected' : ''); ?>>Desertó</option>
								</select>
							</div>
						</td>

						<!-- MEJORA 2.4: ACCIONES MEJORADAS (EDITAR, ELIMINAR, PERFILES, FICHAS) -->
						<td style="text-align:center;">
							<div class="btn-group btn-group-sm flex-wrap justify-content-center" role="group">
								<!-- Modal Perfiles -->
								<button type="button" class="btn btn-outline-success btn-action-usu" data-bs-toggle="modal" data-bs-target="#muxp<?=$d['idusu'];?>" title="Gestionar Perfiles de Usuario">
									<i class="fa-solid fa-id-badge fa-lg"></i>
								</button>
								
								<!-- Modal Fichas -->
								<button type="button" class="btn btn-outline-success btn-action-usu" data-bs-toggle="modal" data-bs-target="#muxf<?=$d['idusu'];?>" title="Gestionar Fichas de Usuario">
									<i class="fa-solid fa-graduation-cap fa-lg"></i>
								</button>
								
								<!-- Editar -->
								<a href="home.php?pg=<?=$pg;?>&idusu=<?=$d['idusu'];?>&opera=edi<?= ($nodocfil ? '&nodocfil='.urlencode($nodocfil) : ''); ?><?= ($idficfil ? '&idficfil='.urlencode($idficfil) : ''); ?>" class="btn btn-outline-primary btn-action-usu" title="Editar Información">
									<i class="fa-solid fa-pen-to-square fa-lg"></i>
								</a>

								<!-- Eliminar Seguro -->
								<button type="button" class="btn btn-outline-danger btn-action-usu" onclick="eliminarUsuario(<?=$d['idusu'];?>, '<?=htmlspecialchars(addslashes($d['nomusu']));?>');" title="Eliminar Usuario">
									<i class="fa-solid fa-trash-can fa-lg"></i>
								</button>
							</div>
						</td>
					</tr>
				<?php } ?>
				</tbody>
				<tfoot>
					<tr>
						<th class="text-center"><i class="fa-solid fa-check-double text-muted"></i></th>
						<th>Perfil</th>
						<th class="text-center">Foto</th>
						<th>Datos de Usuario</th>
						<th>Contacto</th>
						<th class="text-center">Estado</th>
						<th style="text-align: center;">Acciones</th>
					</tr>
				</tfoot>
			</table>
		</div>
	</div>

	<!-- Renderizar los modales individuales fuera de la tabla para proteger el DOM de DataTables -->
	<?= $modalesHtml; ?>

	<!-- Modal para Visualización de Fotografía de Usuario Ampliada (Mejora 2.2) -->
	<div class="modal fade" id="modalVisorFotoUsuario" tabindex="-1" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
				<div class="modal-header text-white" style="background: linear-gradient(135deg, #117f09 0%, #00af00 100%);">
					<div class="d-flex align-items-center gap-2">
						<i class="fa-solid fa-image fa-lg"></i>
						<h5 class="modal-title mb-0 fw-bold fs-6">Fotografía de Perfil</h5>
					</div>
					<button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body p-4 text-center bg-light">
					<div class="p-2 bg-white rounded-4 shadow-sm border d-inline-block">
						<img src="" id="imgVisorFotoSrc" alt="Fotografía del usuario" class="img-fluid rounded-3" style="max-height: 420px; max-width: 100%; object-fit: contain;">
					</div>
					<h6 class="fw-bold text-dark mt-3 mb-1" id="nomVisorFotoUsuario"></h6>
					<small class="text-muted d-block">Fotografía registrada en el sistema SAGI</small>
				</div>
				<div class="modal-footer py-2 justify-content-between bg-white">
					<a href="#" id="btnDescargarFotoVisor" target="_blank" download class="btn btn-outline-success btn-sm rounded-pill px-3">
						<i class="fa-solid fa-download me-1"></i> Descargar Imagen
					</a>
					<button type="button" class="btn btn-secondary btn-sm rounded-pill px-4" data-bs-dismiss="modal">Cerrar</button>
				</div>
			</div>
		</div>
	</div>

	<!-- ========================================================================= -->
	<!-- MEJORA 3.1 & 3.2: MODAL DE ASIGNACIÓN MASIVA DE PERFILES EN LOTE          -->
	<!-- ========================================================================= -->
	<div class="modal fade" id="modalAsignacionMasiva" tabindex="-1" aria-labelledby="lblModalAsignacionMasiva" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
			<div class="modal-content shadow-lg border-0 rounded-4 overflow-hidden">
				<div class="modal-header text-white" style="background: linear-gradient(135deg, #117f09 0%, #00af00 100%);">
					<div class="d-flex align-items-center gap-2">
						<i class="fa-solid fa-users-gear fa-xl"></i>
						<div>
							<h4 class="modal-title mb-0 fw-bold fs-5" id="lblModalAsignacionMasiva">Asignación Masiva de Perfiles en Lote</h4>
							<small class="text-white text-opacity-75" style="font-size: 0.75rem;">Actualización de roles y permisos RBAC en tiempo real</small>
						</div>
					</div>
					<button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body p-4">
					<!-- Banner de usuarios seleccionados -->
					<div class="p-3 mb-3 rounded-3" style="background: #f0fdf4; border-left: 4px solid #00af00;">
						<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
							<div class="d-flex align-items-center gap-2">
								<i class="fa-solid fa-users text-success fa-lg"></i>
								<strong class="text-dark">Usuarios a procesar:</strong>
								<span class="badge bg-success rounded-pill px-3 py-1 fs-7" id="badgeModalTotalUsuarios">0 seleccionados</span>
							</div>
							<small class="text-muted"><i class="fa-solid fa-bolt text-warning me-1"></i>Sincronización en vivo sin logout</small>
						</div>
						<!-- Lista compacta de chips de usuarios seleccionados -->
						<div id="contenedorChipsUsuarios" class="d-flex flex-wrap gap-1 p-2 bg-white rounded-3 border" style="max-height: 95px; overflow-y: auto;">
							<!-- Generado dinámicamente con JS -->
						</div>
					</div>

					<form id="frmAsignacionMasiva" onsubmit="ejecutarAsignacionMasiva(event);">
						<!-- Alerta de seguridad y confirmación -->
						<div class="alert alert-warning border-0 rounded-3 py-2 px-3 mb-3 d-flex align-items-start gap-2 small">
							<i class="fa-solid fa-triangle-exclamation text-warning fa-lg mt-1"></i>
							<div>
								<strong>Atención:</strong> Esta acción modificará los perfiles y permisos de todos los usuarios seleccionados simultáneamente. Los permisos actualizados surtirán efecto de inmediato en el sistema.
							</div>
						</div>

						<!-- Configuración del Perfil Principal -->
						<div class="card border border-success border-opacity-25 p-3 mb-3 bg-light rounded-3 shadow-sm">
							<label for="masivo_perfil_principal" class="form-label fw-bold text-dark mb-1 small d-flex justify-content-between">
								<span><i class="fa-solid fa-shield-halved me-1 text-success"></i> Perfil Principal del Sistema</span>
								<small class="text-muted fw-normal">Seleccione una opción para actualizar o conservar</small>
							</label>
							<select name="perfil_principal" id="masivo_perfil_principal" class="form-select form-select-sm fw-semibold">
								<option value="0">-- Mantener perfiles principales actuales de cada usuario --</option>
								<?php if($dpe){ foreach ($dpe as $pe) { ?>
									<option value="<?=$pe['idper'];?>"><?=$pe['nomper'];?></option>
								<?php }} ?>
							</select>
						</div>

						<!-- Perfiles por Módulo -->
						<div class="mb-2">
							<div class="d-flex justify-content-between align-items-center mb-2">
								<h6 class="fw-bold text-dark mb-0 fs-7">
									<i class="fa-solid fa-cubes-stacked me-1 text-success"></i> Perfiles a Asignar por Módulo
								</h6>
								<small class="text-muted">Elija los roles correspondientes a cada módulo</small>
							</div>
							<div class="row g-3">
								<?php
									$modulosDisponibles = $musu->getMod();
									if($modulosDisponibles){ 
										foreach($modulosDisponibles as $mItem){
											$perfilesDelMod = $musu->getPefus($mItem['idmod']);
								?>
									<div class="col-md-6">
										<div class="card border h-100 p-2 bg-white shadow-sm rounded-3">
											<label class="form-label fw-bold small text-dark mb-1" for="masivo_mod_<?=$mItem['idmod'];?>">
												<i class="fa-solid fa-cube me-1 text-success"></i><?=htmlspecialchars($mItem['nommod']);?>
											</label>
											<select name="idper[]" id="masivo_mod_<?=$mItem['idmod'];?>" class="form-select form-select-sm">
												<option value="0">-- Sin perfil en este módulo --</option>
												<?php if($perfilesDelMod){ foreach($perfilesDelMod as $pdm){ ?>
													<option value="<?=$pdm['idper'];?>"><?=htmlspecialchars($pdm['nomper']);?></option>
												<?php }} ?>
											</select>
										</div>
									</div>
								<?php 
										}
									} 
								?>
							</div>
						</div>
					</form>
				</div>
				<div class="modal-footer bg-light rounded-bottom-4 px-4 py-3 d-flex justify-content-between">
					<button type="button" class="btn btn-secondary px-3 rounded-pill" data-bs-dismiss="modal">
						<i class="fa-solid fa-xmark me-1"></i> Cancelar
					</button>
					<button type="button" id="btnEjecutarMasivo" class="btn btn-success px-4 rounded-pill fw-bold shadow-sm" style="background-color: #00af00; border-color: #00af00;" onclick="ejecutarAsignacionMasiva();">
						<i class="fa-solid fa-bolt me-1"></i> Asignar Perfiles en Lote
					</button>
				</div>
			</div>
		</div>
	</div>

	<!-- ========================================================================= -->
	<!-- MEJORA 3.1: INDICADOR DE CARGA VISUAL (SPINNER + BARRA DE PROGRESO)       -->
	<!-- Evita que la pantalla se congele durante procesos asíncronos en lote      -->
	<!-- ========================================================================= -->
	<div class="modal fade" id="modalLoadingProgreso" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
		<div class="modal-dialog modal-dialog-centered" style="max-width: 440px;">
			<div class="modal-content border-0 shadow-lg rounded-4 p-4 text-center bg-white">
				<div class="modal-body p-2">
					<!-- Spinner institucional animado -->
					<div class="mb-3 position-relative d-inline-flex align-items-center justify-content-center">
						<div class="spinner-border text-success" role="status" style="width: 4.5rem; height: 4.5rem; border-width: 0.35rem;">
							<span class="visually-hidden">Procesando...</span>
						</div>
						<div class="position-absolute text-success">
							<i class="fa-solid fa-users-gear fa-xl"></i>
						</div>
					</div>

					<h4 class="fw-bold text-dark fs-5 mb-1" id="txtProgresoTitulo">Asignando Perfiles</h4>
					<p class="text-muted small mb-3" id="txtProgresoEstado">Iniciando proceso en lote...</p>

					<!-- Barra de progreso fluida -->
					<div class="progress mb-2 rounded-pill" style="height: 14px; background-color: #e9ecef; box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);">
						<div id="barraProgresoAsignacion" class="progress-bar progress-bar-striped progress-bar-animated bg-success rounded-pill" role="progressbar" style="width: 0%; transition: width 0.35s ease;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
					</div>

					<div class="d-flex justify-content-between align-items-center text-muted small px-1">
						<span id="txtProgresoUsuarios">Preparando usuarios...</span>
						<strong class="text-success" id="txtProgresoPorcentaje">0%</strong>
					</div>

					<div class="mt-3 pt-2 border-top text-muted fs-8">
						<i class="fa-solid fa-lock text-success me-1"></i> Por favor espere mientras se sincronizan los permisos RBAC en tiempo real.
					</div>
				</div>
			</div>
		</div>
	</div>

<?php 
} elseif ($nodocfil || $idficfil) { 
?>
	<!-- Mensaje de Búsqueda sin Resultados -->
	<div class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white mb-4">
		<div class="mb-3">
			<i class="fa-solid fa-user-slash fa-4x text-muted opacity-50"></i>
		</div>
		<h4 class="fw-bold text-dark fs-5">No se encontraron usuarios</h4>
		<p class="text-muted mb-3">No hubo coincidencias con los criterios ingresados: <strong><?=htmlspecialchars($nodocfil ?: $idficfil);?></strong></p>
		<div>
			<a href="home.php?pg=<?=$pg;?>" class="btn btn-outline-success rounded-pill px-4">
				<i class="fa-solid fa-arrow-rotate-left me-1"></i> Restablecer Filtros
			</a>
		</div>
	</div>
<?php 
} else { 
?>
	<!-- MEJORA 2.1: Estado inicial sin usuarios hasta que se busque -->
	<div class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white mb-4 placeholder-busqueda-usu">
		<div class="mb-3">
			<div class="d-inline-flex align-items-center justify-content-center p-4 rounded-circle bg-success bg-opacity-10 text-success">
				<i class="fa-solid fa-magnifying-glass fa-3x"></i>
			</div>
		</div>
		<h4 class="fw-bold text-dark fs-5">Consulta de Usuarios en el Sistema</h4>
		<p class="text-muted mx-auto" style="max-width: 550px;">
			Para visualizar los registros, utilice el panel de búsqueda superior ingresando el <strong>número de documento</strong>, <strong>nombre</strong>, <strong>apellido</strong>, <strong>correo electrónico</strong> o seleccionando una <strong>ficha de formación</strong>.
		</p>
		<div class="d-flex justify-content-center gap-2 mt-2">
			<span class="badge bg-light text-dark border px-3 py-2"><i class="fa-solid fa-bolt text-warning me-1"></i> Búsqueda en tiempo real</span>
			<span class="badge bg-light text-dark border px-3 py-2"><i class="fa-solid fa-shield-halved text-success me-1"></i> Gestión segura</span>
		</div>
	</div>
<?php 
} 
?>

<!-- ========================================================================= -->
<!-- MEJORA 2.5: CAMBIO MASIVO DE CONTRASEÑAS (DISEÑO INTUITIVO Y CONFIRMACIÓN) -->
<!-- ========================================================================= -->
<div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-5">
	<div class="d-flex align-items-center mb-3">
		<div class="p-2 rounded-3 bg-warning bg-opacity-10 text-warning me-3">
			<i class="fa-solid fa-key fa-2x"></i>
		</div>
		<div>
			<h3 class="fs-5 fw-bold text-dark mb-0">Cambio Masivo de Contraseñas</h3>
			<small class="text-muted">Actualice de forma masiva las credenciales para los usuarios del sistema</small>
		</div>
	</div>

	<div class="alert alert-warning border-0 rounded-3 d-flex align-items-start gap-3 py-3 px-3 mb-4">
		<i class="fa-solid fa-triangle-exclamation fa-lg text-warning mt-1"></i>
		<div class="small">
			<strong>Aviso importante:</strong> Esta operación modificará la contraseña de todos los usuarios registrados (excepto el Superadministrador). La contraseña final se generará bajo la estructura: 
			<code>[Inicio] + [No. Documento] + [Fin]</code> (Ejemplo: si inicio es <em>Sen</em> y fin es <em>*</em>, para el doc. <em>1003456789</em> la contraseña será <em>Sen1003456789*</em>).
		</div>
	</div>

	<form id="frmCamCon" action="home.php?pg=<?=$pg;?>" method="POST">
		<div class="row g-3 align-items-end">
			<div class="col-md-4">
				<label for="inico" class="form-label fw-semibold small text-dark">Inicio de Contraseña (Prefijo)</label>
				<input type="text" name="inico" id="inico" maxlength="10" class="form-control" placeholder="Ej: Sena, Sg, Clv...">
			</div>
			<div class="col-md-4">
				<label for="finco" class="form-label fw-semibold small text-dark">Fin de Contraseña (Sufijo)</label>
				<input type="text" name="finco" id="finco" maxlength="10" class="form-control" placeholder="Ej: 2026*, #, $...">
			</div>
			<div class="col-md-4">
				<button type="button" class="btn btn-warning w-100 fw-bold py-2 shadow-sm text-dark" onclick="confirmarCambioContrasenas();">
					<i class="fa-solid fa-arrows-rotate me-1"></i> Cambiar Contraseñas Masivas
				</button>
				<input type="hidden" name="opera" value="CamCon">
				<?php if($nodocfil){ ?><input type="hidden" name="nodocfil" value="<?=htmlspecialchars($nodocfil);?>"><?php } ?>
				<?php if($idficfil){ ?><input type="hidden" name="idficfil" value="<?=htmlspecialchars($idficfil);?>"><?php } ?>
			</div>
		</div>
	</form>
</div>



<!-- ========================================================================= -->
<!-- JAVASCRIPT: INTERACTIVIDAD, AJAX Y ALERTAS SWEETALERT2                   -->
<!-- ========================================================================= -->
<script>
// Toggle de visibilidad para el campo contraseña
function togglePasswordVisibility(inputId, btnElement) {
	var inp = document.getElementById(inputId);
	if (!inp) return;
	if (inp.type === 'password') {
		inp.type = 'text';
		btnElement.innerHTML = '<i class="fa-solid fa-eye-slash text-success"></i>';
	} else {
		inp.type = 'password';
		btnElement.innerHTML = '<i class="fa-solid fa-eye"></i>';
	}
}

// Auto-abrir formulario al editar si viene cargado $datOne
document.addEventListener('DOMContentLoaded', function() {
	<?php if(!empty($datOne)){ ?>
		var frm = document.getElementById("frmins");
		var mas = document.getElementById("mas");
		var menos = document.getElementById("menos");
		if (frm) {
			frm.style.display = "block";
			frm.classList.add("form-registro-animado");
		}
		if (mas) mas.style.display = "none";
		if (menos) menos.style.display = "inline-block";
		if (frm) frm.scrollIntoView({ behavior: 'smooth', block: 'start' });
	<?php } ?>

	// Evento click mejorado para el icono "+" en el título (Mejora 2)
	var btnMas = document.getElementById("mas");
	if (btnMas) {
		btnMas.addEventListener('click', function() {
			var frm = document.getElementById("frmins");
			if (frm) {
				frm.style.display = "block";
				frm.classList.remove("form-registro-animado");
				// Forzar reflow para reiniciar animación suave
				void frm.offsetWidth;
				frm.classList.add("form-registro-animado");
				frm.scrollIntoView({ behavior: 'smooth', block: 'start' });
				setTimeout(function() {
					var firstInput = document.getElementById("ndocusu") || document.getElementById("nomusu");
					if (firstInput) firstInput.focus();
				}, 250);
			}
		});
	}

	// MEJORA 2.1: Inicialización robusta de DataTables con Botones de Exportación (Copiar, CSV, Excel)
	function inicializarDataTableUsuarios() {
		if (window.jQuery && $.fn && $.fn.DataTable && $('#example').length) {
			if ($.fn.DataTable.isDataTable('#example')) {
				$('#example').DataTable().destroy();
			}
			var table = $('#example').DataTable({
				dom: "<'row mb-3 align-items-center g-2'<'col-12 col-md-6 d-flex align-items-center flex-wrap gap-2 dt-buttons-usu'B><'col-12 col-md-6 d-flex justify-content-md-end'f>>" +
					 "<'row'<'col-12'tr>>" +
					 "<'row mt-3 align-items-center g-2'<'col-12 col-md-5'i><'col-12 col-md-7 d-flex justify-content-md-end'p>>",
				buttons: [
					{
						extend: 'copyHtml5',
						text: '<i class="fa-solid fa-copy me-1"></i> Copiar',
						className: 'btn btn-sm btn-outline-secondary rounded-pill px-3 shadow-sm btn-export-dt',
						exportOptions: {
							columns: [1, 3, 4, 5],
							format: {
								body: function(data) {
									var tmp = document.createElement("div");
									tmp.innerHTML = data;
									return (tmp.textContent || tmp.innerText || "").trim().replace(/\s+/g, ' ');
								}
							}
						}
					},
					{
						extend: 'csvHtml5',
						text: '<i class="fa-solid fa-file-csv me-1 text-primary"></i> CSV',
						className: 'btn btn-sm btn-outline-primary rounded-pill px-3 shadow-sm btn-export-dt',
						title: 'Listado_Usuarios_SAGI',
						exportOptions: {
							columns: [1, 3, 4, 5],
							format: {
								body: function(data) {
									var tmp = document.createElement("div");
									tmp.innerHTML = data;
									return (tmp.textContent || tmp.innerText || "").trim().replace(/\s+/g, ' ');
								}
							}
						}
					},
					{
						extend: 'excelHtml5',
						text: '<i class="fa-solid fa-file-excel me-1 text-success"></i> Excel',
						className: 'btn btn-sm btn-outline-success rounded-pill px-3 shadow-sm btn-export-dt',
						title: 'Listado_Usuarios_SAGI',
						exportOptions: {
							columns: [1, 3, 4, 5],
							format: {
								body: function(data) {
									var tmp = document.createElement("div");
									tmp.innerHTML = data;
									return (tmp.textContent || tmp.innerText || "").trim().replace(/\s+/g, ' ');
								}
							}
						}
					}
				],
				order: [[3, 'asc']],
				pageLength: 25,
				lengthMenu: [10, 25, 50, 100],
				language: {
					lengthMenu: "Mostrar _MENU_ usuarios",
					zeroRecords: "No se encontraron usuarios coincidentes",
					info: "Mostrando _START_ a _END_ de _TOTAL_ usuarios",
					infoEmpty: "0 usuarios disponibles",
					infoFiltered: "(filtrado de _MAX_ usuarios en total)",
					search: "Filtrar en tabla:",
					paginate: {
						first: "Primero",
						last: "Último",
						next: "Siguiente",
						previous: "Anterior"
					}
				},
				columnDefs: [
					{ orderable: false, targets: [0, 2, 6] }
				],
				responsive: true
			});
		}
	}

	inicializarDataTableUsuarios();

	// Ajustar DataTables ante redimensionamiento de pantalla
	window.addEventListener('resize', function() {
		if (window.jQuery && $.fn && $.fn.DataTable && $.fn.DataTable.isDataTable('#example')) {
			$('#example').DataTable().columns.adjust();
		}
	});

	// Notificaciones emergentes según retorno del controlador
	<?php if(isset($_GET['err']) && $_GET['err'] === 'ext_invalida'){ ?>
		if (typeof Swal !== 'undefined') {
			Swal.fire({
				icon: 'warning',
				title: 'Fotografía no actualizada',
				text: 'Los datos fueron procesados pero la imagen no pudo ser guardada debido a que su formato no es válido (solo se admiten PNG, JPG, JPEG, WEBP).',
				confirmButtonColor: '#00af00'
			});
		}
	<?php } ?>
});

// Función de activación para botones de exportación externos
function exportarUsuariosDT(tipo) {
	if (window.jQuery && $.fn && $.fn.DataTable && $.fn.DataTable.isDataTable('#example')) {
		var dt = $('#example').DataTable();
		if (tipo === 'copy') {
			dt.button('.buttons-copy').trigger();
		} else if (tipo === 'csv') {
			dt.button('.buttons-csv').trigger();
		} else if (tipo === 'excel') {
			dt.button('.buttons-excel').trigger();
		}
	}
}

// Previsualización y validación dinámica de foto en formulario de usuario
function previsualizarFotoUsuario(event) {
	var input = event.target;
	if (!input.files || !input.files[0]) return;
	var file = input.files[0];
	var ext = file.name.split('.').pop().toLowerCase();
	var validExts = ['png', 'jpg', 'jpeg', 'webp', 'jfif', 'gif'];
	if (!validExts.includes(ext)) {
		if (typeof Swal !== 'undefined') {
			Swal.fire({
				icon: 'warning',
				title: 'Formato no válido',
				text: 'Por favor seleccione una imagen en formato PNG, JPG, JPEG o WEBP.',
				confirmButtonColor: '#00af00'
			});
		} else {
			alert('Formato no válido. Seleccione PNG, JPG, JPEG o WEBP.');
		}
		input.value = '';
		return;
	}
	var reader = new FileReader();
	reader.onload = function(e) {
		var preview = document.getElementById('imgPreviewUsuForm');
		if (preview) preview.src = e.target.result;
		var badge = document.getElementById('badgeFotoSeleccionada');
		var txt = document.getElementById('txtNombreArchivoFoto');
		if (badge && txt) {
			txt.textContent = file.name;
			badge.classList.remove('d-none');
		}
	};
	reader.readAsDataURL(file);
}

// Abrir visor de foto ampliada en modal
function abrirVisorFotoUsuario(src, nombre) {
	var modalEl = document.getElementById('modalVisorFotoUsuario');
	var imgEl = document.getElementById('imgVisorFotoSrc');
	var nomEl = document.getElementById('nomVisorFotoUsuario');
	var btnDown = document.getElementById('btnDescargarFotoVisor');
	if (imgEl) imgEl.src = src;
	if (nomEl) nomEl.textContent = nombre || 'Usuario';
	if (btnDown) {
		btnDown.href = src;
		btnDown.download = (nombre ? nombre.toLowerCase().replace(/\s+/g, '_') : 'foto_perfil') + '.jpg';
	}
	if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
		var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
		modal.show();
	}
}

// =========================================================================
// MEJORA 3.1 & 3.2: GESTIÓN MASIVA DE PERFILES CON FEEDBACK VISUAL
// =========================================================================

// Marcar o desmarcar todos los checkboxes de usuarios visibles
function toggleSelectAllUsuarios(masterCheckbox) {
	var checkboxes = document.querySelectorAll('.chk-usu-select');
	checkboxes.forEach(function(chk) {
		chk.checked = masterCheckbox.checked;
	});
	actualizarSeleccionUsuarios();
}

function seleccionarTodosVisible(marcar) {
	var checkboxes = document.querySelectorAll('.chk-usu-select');
	checkboxes.forEach(function(chk) {
		chk.checked = marcar;
	});
	var master = document.getElementById('chkSelectAllUsu');
	if (master) master.checked = marcar;
	actualizarSeleccionUsuarios();
}

function deseleccionarTodosUsuarios() {
	seleccionarTodosVisible(false);
}

// Actualizar contador y visibilidad del panel de acciones en lote
function actualizarSeleccionUsuarios() {
	var checkboxes = document.querySelectorAll('.chk-usu-select:checked');
	var totalCheckboxes = document.querySelectorAll('.chk-usu-select');
	var panel = document.getElementById('panelAccionesMasivas');
	var numSpan = document.getElementById('numSeleccionados');
	var master = document.getElementById('chkSelectAllUsu');

	var count = checkboxes.length;
	if (numSpan) numSpan.textContent = count;

	if (panel) {
		if (count > 0) {
			panel.classList.remove('d-none');
		} else {
			panel.classList.add('d-none');
		}
	}

	if (master && totalCheckboxes.length > 0) {
		master.checked = (count === totalCheckboxes.length);
		master.indeterminate = (count > 0 && count < totalCheckboxes.length);
	}
}

// Abrir modal de asignación masiva poblando la lista de usuarios seleccionados
function abrirModalAsignacionMasiva() {
	var checkboxes = document.querySelectorAll('.chk-usu-select:checked');
	if (checkboxes.length === 0) {
		if (typeof Swal !== 'undefined') {
			Swal.fire('Atención', 'Por favor seleccione al menos un usuario de la lista.', 'warning');
		} else {
			alert('Seleccione al menos un usuario.');
		}
		return;
	}

	var badgeTotal = document.getElementById('badgeModalTotalUsuarios');
	if (badgeTotal) badgeTotal.textContent = checkboxes.length + ' seleccionado(s)';

	var contenedorChips = document.getElementById('contenedorChipsUsuarios');
	if (contenedorChips) {
		contenedorChips.innerHTML = '';
		var maxVisualizar = 60;
		checkboxes.forEach(function(chk, idx) {
			if (idx < maxVisualizar) {
				var nombre = chk.getAttribute('data-nombre') || 'Usuario #' + chk.value;
				var chip = document.createElement('span');
				chip.className = 'chip-usuario-seleccionado';
				chip.innerHTML = '<i class="fa-solid fa-user-check me-1"></i> ' + escapeHtml(nombre);
				contenedorChips.appendChild(chip);
			}
		});
		if (checkboxes.length > maxVisualizar) {
			var extra = document.createElement('span');
			extra.className = 'badge bg-secondary rounded-pill align-self-center';
			extra.textContent = '+' + (checkboxes.length - maxVisualizar) + ' más';
			contenedorChips.appendChild(extra);
		}
	}

	var modalElem = document.getElementById('modalAsignacionMasiva');
	if (modalElem) {
		var bsModal = bootstrap.Modal.getInstance(modalElem) || new bootstrap.Modal(modalElem);
		bsModal.show();
	}
}

// Ejecución asíncrona de asignación masiva con barra de progreso y feedback visual continuo (Mejora 3.1)
function ejecutarAsignacionMasiva(event) {
	if (event) event.preventDefault();

	var checkboxes = document.querySelectorAll('.chk-usu-select:checked');
	var usuariosIds = [];
	checkboxes.forEach(function(chk) {
		usuariosIds.push(chk.value);
	});

	if (usuariosIds.length === 0) {
		if (typeof Swal !== 'undefined') {
			Swal.fire('Atención', 'No hay usuarios seleccionados.', 'warning');
		}
		return;
	}

	// Cerrar modal de formulario masivo
	var modalFormElem = document.getElementById('modalAsignacionMasiva');
	if (modalFormElem) {
		cerrarModalSeguro(modalFormElem);
	}

	// Abrir modal de progreso visual
	var modalLoadingElem = document.getElementById('modalLoadingProgreso');
	var bsModalLoading = bootstrap.Modal.getInstance(modalLoadingElem) || new bootstrap.Modal(modalLoadingElem);
	
	var barraProgreso = document.getElementById('barraProgresoAsignacion');
	var txtEstado = document.getElementById('txtProgresoEstado');
	var txtPorcentaje = document.getElementById('txtProgresoPorcentaje');
	var txtUsuarios = document.getElementById('txtProgresoUsuarios');

	if (barraProgreso) barraProgreso.style.width = '10%';
	if (txtPorcentaje) txtPorcentaje.textContent = '10%';
	if (txtEstado) txtEstado.textContent = 'Preparando lote de ' + usuariosIds.length + ' usuario(s)...';
	if (txtUsuarios) txtUsuarios.textContent = usuariosIds.length + ' usuario(s) en proceso';

	bsModalLoading.show();

	// Animación de avance escalonado de la barra de progreso
	var progresoActual = 10;
	var intervaloProgreso = setInterval(function() {
		if (progresoActual < 85) {
			progresoActual += Math.floor(Math.random() * 12) + 5;
			if (progresoActual > 85) progresoActual = 85;
			if (barraProgreso) barraProgreso.style.width = progresoActual + '%';
			if (txtPorcentaje) txtPorcentaje.textContent = progresoActual + '%';
			
			if (progresoActual > 30 && progresoActual < 60) {
				if (txtEstado) txtEstado.textContent = 'Aplicando perfiles y roles por módulo...';
			} else if (progresoActual >= 60) {
				if (txtEstado) txtEstado.textContent = 'Sincronizando permisos RBAC en tiempo real...';
			}
		}
	}, 180);

	// Recopilar datos del formulario
	var frm = document.getElementById('frmAsignacionMasiva');
	var formData = new FormData(frm);
	formData.append('opera', 'InUP_masivo');
	formData.append('ajax', '1');
	formData.append('usuarios_ids', JSON.stringify(usuariosIds));

	// Petición asíncrona (AJAX)
	fetch('controllers/usucusu.php', {
		method: 'POST',
		headers: {
			'X-Requested-With': 'XMLHttpRequest',
			'Accept': 'application/json'
		},
		body: formData
	})
	.then(function(response) {
		if (!response.ok) {
			throw new Error('HTTP ' + response.status);
		}
		return response.json();
	})
	.then(function(data) {
		clearInterval(intervaloProgreso);

		if (data.success) {
			// Completar barra al 100%
			if (barraProgreso) barraProgreso.style.width = '100%';
			if (txtPorcentaje) txtPorcentaje.textContent = '100%';
			if (txtEstado) txtEstado.textContent = '¡Permisos actualizados en tiempo real!';

			// Leer perfil principal seleccionado para refrescar visualmente la tabla
			var selectPrin = document.getElementById('masivo_perfil_principal');
			var nuevoNomPerfil = (selectPrin && selectPrin.value !== '0') ? selectPrin.options[selectPrin.selectedIndex].text : null;

			setTimeout(function() {
				if (modalLoadingElem) {
					cerrarModalSeguro(modalLoadingElem);
				} else {
					bsModalLoading.hide();
				}

				// Actualizar visualmente badges de perfiles en la tabla
				if (nuevoNomPerfil && data.usuarios) {
					data.usuarios.forEach(function(uId) {
						var lbl = document.querySelector('.lbl-perfil-' + uId);
						if (lbl) lbl.textContent = nuevoNomPerfil;
					});
				}

				// Limpiar selección de usuarios
				deseleccionarTodosUsuarios();

				// Alerta modal clara de éxito con SweetAlert2 (Mejora 3.1 & 3.2)
				if (typeof Swal !== 'undefined') {
					Swal.fire({
						icon: 'success',
						title: '¡Asignación Masiva Exitosa!',
						html: '<p class="mb-2">Se han actualizado los perfiles de <strong>' + data.procesados + ' usuario(s)</strong> en lote con éxito.</p>' +
						      '<div class="alert alert-success bg-success bg-opacity-10 border-0 rounded-3 p-2 small mt-2">' +
						      '<i class="fa-solid fa-bolt me-1 text-success"></i> <strong>Control de Acceso (RBAC):</strong> Los permisos entraron en vigencia en tiempo real sin requerir que los usuarios cierren sesión.' +
						      '</div>',
						confirmButtonColor: '#00af00',
						confirmButtonText: '<i class="fa-solid fa-check me-1"></i> Entendido'
					});
				} else {
					alert(data.message || 'Asignación masiva completada con éxito.');
				}
			}, 450);
		} else {
			throw new Error(data.error || 'No se pudo completar la asignación masiva.');
		}
	})
	.catch(function(err) {
		clearInterval(intervaloProgreso);
		if (modalLoadingElem) {
			cerrarModalSeguro(modalLoadingElem);
		} else {
			bsModalLoading.hide();
		}
		console.error(err);
		if (typeof Swal !== 'undefined') {
			Swal.fire('Error en la Asignación', err.message || 'Ocurrió un inconveniente al procesar los usuarios.', 'error');
		} else {
			alert('Error: ' + err.message);
		}
	});
}

// Función robusta para cerrar cualquier modal y garantizar que la pantalla no quede congelada
function cerrarModalSeguro(modalElem) {
	if (!modalElem) return;

	// 1. Intentar cerrar con Bootstrap 5
	try {
		if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
			var bsModal = bootstrap.Modal.getInstance(modalElem) || (bootstrap.Modal.getOrCreateInstance ? bootstrap.Modal.getOrCreateInstance(modalElem) : null);
			if (bsModal && typeof bsModal.hide === 'function') {
				bsModal.hide();
			}
		}
	} catch (e) {
		console.warn('Advertencia cerrando modal con Bootstrap:', e);
	}

	// 2. Intentar cerrar con jQuery si está presente
	try {
		if (window.jQuery && typeof jQuery(modalElem).modal === 'function') {
			jQuery(modalElem).modal('hide');
		}
	} catch (e) {
		console.warn('Advertencia cerrando modal con jQuery:', e);
	}

	// 3. Simular clic en el botón de cerrar nativo del modal si aún está visible
	try {
		var btnCerrar = modalElem.querySelector('[data-bs-dismiss="modal"], .btn-close');
		if (btnCerrar) {
			btnCerrar.click();
		}
	} catch (e) {
		console.warn('Advertencia disparando click en botón de cierre:', e);
	}

	// 4. Limpieza forzada e integral del DOM para eliminar cualquier backdrop residual y restaurar interactividad
	var limpiarBackdropYBloqueos = function() {
		modalElem.classList.remove('show');
		modalElem.style.display = 'none';
		modalElem.setAttribute('aria-hidden', 'true');
		modalElem.removeAttribute('aria-modal');

		// Eliminar cualquier backdrop remanente en el DOM
		var backdrops = document.querySelectorAll('.modal-backdrop');
		backdrops.forEach(function(b) {
			b.remove();
		});

		// Desbloquear el scroll y eventos del body
		document.body.classList.remove('modal-open');
		document.body.style.overflow = '';
		document.body.style.paddingRight = '';
	};

	// Ejecución inmediata y escalonada para abarcar el final de transiciones CSS
	limpiarBackdropYBloqueos();
	setTimeout(limpiarBackdropYBloqueos, 180);
	setTimeout(limpiarBackdropYBloqueos, 350);
}

// Guardado asíncrono individual en el modal "Gestionar Perfiles de Usuario" (Mejora 1, 3.1 & 3.2)
function guardarPerfilesIndividual(event, form, idusu) {
	if (event) event.preventDefault();

	var btnSubmit = document.getElementById('btnGuardarPef_' + idusu);
	var originalHtml = btnSubmit ? btnSubmit.innerHTML : 'Guardar Cambios';

	if (btnSubmit) {
		btnSubmit.disabled = true;
		btnSubmit.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Guardando...';
	}

	var formData = new FormData(form);
	formData.append('ajax', '1');

	fetch('controllers/usucusu.php', {
		method: 'POST',
		headers: {
			'X-Requested-With': 'XMLHttpRequest',
			'Accept': 'application/json'
		},
		body: formData
	})
	.then(function(response) {
		if (!response.ok) {
			throw new Error('HTTP ' + response.status);
		}
		return response.json();
	})
	.then(function(data) {
		if (btnSubmit) {
			btnSubmit.disabled = false;
			btnSubmit.innerHTML = originalHtml;
		}

		if (data.success) {
			// Cerrar el modal individual y desbloquear inmediatamente la pantalla
			var modalElem = document.getElementById('muxp' + idusu);
			if (modalElem) {
				cerrarModalSeguro(modalElem);
			}

			// Actualizar badge de perfil principal en la fila de la tabla
			if (data.nomper_principal) {
				var lbl = document.querySelector('.lbl-perfil-' + idusu);
				if (lbl) lbl.textContent = data.nomper_principal;
				var badgeModal = document.getElementById('badge-modal-perfil-' + idusu);
				if (badgeModal) {
					badgeModal.innerHTML = '<i class="fa-solid fa-shield-halved me-1"></i> Rol Principal: <strong>' + escapeHtml(data.nomper_principal) + '</strong>';
				}
			}

			// Feedback visual instantáneo con SweetAlert2 Toast (sin bloquear la interacción del usuario)
			if (typeof Swal !== 'undefined') {
				const Toast = Swal.mixin({
					toast: true,
					position: 'top-end',
					showConfirmButton: false,
					timer: 3000,
					timerProgressBar: true
				});
				Toast.fire({
					icon: 'success',
					title: 'Perfil principal actualizado correctamente.'
				});
			}
		} else {
			if (typeof Swal !== 'undefined') {
				Swal.fire('Error', data.error || 'No se pudieron actualizar los perfiles.', 'error');
			} else {
				alert(data.error || 'No se pudieron actualizar los perfiles.');
			}
		}
	})
	.catch(function(err) {
		if (btnSubmit) {
			btnSubmit.disabled = false;
			btnSubmit.innerHTML = originalHtml;
		}
		console.error(err);
		if (typeof Swal !== 'undefined') {
			Swal.fire('Error de Conexión', 'No fue posible comunicar con el servidor.', 'error');
		} else {
			alert('Error de conexión con el servidor.');
		}
	});
}

function escapeHtml(text) {
	var map = {
		'&': '&amp;',
		'<': '&lt;',
		'>': '&gt;',
		'"': '&quot;',
		"'": '&#039;'
	};
	return String(text).replace(/[&<>"']/g, function(m) { return map[m]; });
}

// Cambiar Estado del Usuario (Activo, Inactivo, Desertó) con AJAX y Feedback Inmediato
function cambiarEstadoUsuario(idusu, nuevoEstado, selElement) {
	if (!idusu) return;

	var iconSpan = document.getElementById('icon-estado-' + idusu);
	var prevVal = selElement.getAttribute('data-prev') || selElement.value;
	selElement.disabled = true;

	if (iconSpan) {
		iconSpan.innerHTML = '<i class="fa-solid fa-spinner fa-spin text-muted"></i>';
	}

	var url = 'controllers/usucusu.php?opera=cambiar_estado&ajax=1&idusu=' + idusu + '&actusu_val=' + nuevoEstado;

	fetch(url, {
		method: 'GET',
		headers: { 
			'X-Requested-With': 'XMLHttpRequest',
			'Accept': 'application/json'
		}
	})
	.then(function(response) {
		if (!response.ok) {
			throw new Error('HTTP ' + response.status);
		}
		return response.json();
	})
	.then(function(data) {
		selElement.disabled = false;
		if (data.success) {
			selElement.setAttribute('data-prev', nuevoEstado);
			selElement.classList.remove('select-estado-activo', 'select-estado-inactivo', 'select-estado-deserto');
			
			if (nuevoEstado == 1) {
				selElement.classList.add('select-estado-activo');
				if (iconSpan) iconSpan.innerHTML = '<i class="fa-solid fa-circle-check text-success"></i>';
			} else if (nuevoEstado == 3) {
				selElement.classList.add('select-estado-deserto');
				if (iconSpan) iconSpan.innerHTML = '<i class="fa-solid fa-person-walking-arrow-right text-warning"></i>';
			} else {
				selElement.classList.add('select-estado-inactivo');
				if (iconSpan) iconSpan.innerHTML = '<i class="fa-solid fa-circle-xmark text-danger"></i>';
			}

			if (typeof Swal !== 'undefined') {
				const Toast = Swal.mixin({
					toast: true,
					position: 'top-end',
					showConfirmButton: false,
					timer: 2500,
					timerProgressBar: true
				});
				Toast.fire({
					icon: nuevoEstado == 1 ? 'success' : (nuevoEstado == 3 ? 'warning' : 'info'),
					title: 'Estado de usuario actualizado: ' + data.status_text
				});
			}
		} else {
			selElement.value = prevVal;
			if (typeof Swal !== 'undefined') {
				Swal.fire('Error', 'No se pudo actualizar el estado.', 'error');
			}
		}
	})
	.catch(function(err) {
		selElement.disabled = false;
		selElement.value = prevVal;
		console.error(err);
		if (typeof Swal !== 'undefined') {
			Swal.fire('Error de Conexión', 'Hubo un problema al procesar la solicitud.', 'error');
		}
	});
}

// Sincronización entre input de búsqueda y select de ficha
function sincronizarFichaSelectAInput(selectElem) {
	var txt = document.getElementById('txtFichaFiltro');
	if (!txt) return;
	if (selectElem.value && selectElem.value !== '0') {
		var opt = selectElem.options[selectElem.selectedIndex];
		txt.value = opt.getAttribute('data-full') || opt.text;
	} else {
		txt.value = '';
	}
}

function sincronizarFichaInputASelect(valor) {
	var sel = document.getElementById('comboboxFicha');
	if (!sel) return;
	valor = (valor || '').trim().toLowerCase();
	if (!valor) {
		sel.value = '0';
		return;
	}
	var matched = false;
	for (var i = 0; i < sel.options.length; i++) {
		var optVal = (sel.options[i].value || '').toLowerCase();
		var optText = (sel.options[i].text || '').toLowerCase();
		if (optVal && (optVal === valor || optText.indexOf(valor) !== -1 || valor.indexOf(optVal) === 0)) {
			sel.selectedIndex = i;
			matched = true;
			break;
		}
	}
	if (!matched) {
		sel.value = '0';
	}
}

// Sincronización en el modal de vinculación de fichas
function syncModalFichaSelect(selElem, inputId) {
	var inp = document.getElementById(inputId);
	if (!inp) return;
	if (selElem.value && selElem.value !== '') {
		inp.value = selElem.value;
	} else {
		inp.value = '';
	}
}

function syncModalFichaInput(inpElem, selectId) {
	var sel = document.getElementById(selectId);
	if (!sel) return;
	var val = (inpElem.value || '').trim();
	var m = val.match(/^(\d+)/);
	var num = m ? m[1] : val;
	var matched = false;
	for (var i = 0; i < sel.options.length; i++) {
		if (sel.options[i].value === num || sel.options[i].value === val) {
			sel.selectedIndex = i;
			matched = true;
			break;
		}
	}
	if (!matched) {
		sel.value = '';
	}
}

// Alternar Estado Dinámicamente (Activo / Inactivo) con AJAX (Compatibilidad)
function toggleEstadoUsuario(idusu, btnElement) {
	if (!idusu) return;

	var originalHtml = btnElement.innerHTML;
	btnElement.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i>...';
	btnElement.disabled = true;

	var url = 'controllers/usucusu.php?opera=toggle_act&ajax=1&idusu=' + idusu;

	fetch(url, {
		method: 'GET',
		headers: { 
			'X-Requested-With': 'XMLHttpRequest',
			'Accept': 'application/json'
		}
	})
	.then(function(response) {
		if (!response.ok) {
			throw new Error('HTTP ' + response.status);
		}
		return response.json();
	})
	.then(function(data) {
		btnElement.disabled = false;
		if (data.success) {
			if (data.actusu == 1) {
				btnElement.className = 'btn btn-sm btn-estado-dinamico rounded-pill px-3 py-1 shadow-sm btn-outline-success';
				btnElement.innerHTML = '<i class="fa-solid fa-circle-check text-success me-1"></i><span class="fw-bold status-label">Activo</span>';
				btnElement.title = 'Haga clic para desactivar el usuario';
			} else {
				btnElement.className = 'btn btn-sm btn-estado-dinamico rounded-pill px-3 py-1 shadow-sm btn-outline-danger';
				btnElement.innerHTML = '<i class="fa-solid fa-circle-xmark text-danger me-1"></i><span class="fw-bold status-label">Inactivo</span>';
				btnElement.title = 'Haga clic para activar el usuario';
			}

			if (typeof Swal !== 'undefined') {
				const Toast = Swal.mixin({
					toast: true,
					position: 'top-end',
					showConfirmButton: false,
					timer: 2000,
					timerProgressBar: true
				});
				Toast.fire({
					icon: 'success',
					title: 'Estado actualizado a: ' + data.status_text
				});
			}
		} else {
			btnElement.innerHTML = originalHtml;
			if (typeof Swal !== 'undefined') {
				Swal.fire('Error', 'No se pudo cambiar el estado.', 'error');
			}
		}
	})
	.catch(function(err) {
		btnElement.disabled = false;
		btnElement.innerHTML = originalHtml;
		console.error(err);
	});
}

// Confirmación de Eliminación con SweetAlert2
function eliminarUsuario(idusu, nombre) {
	if (typeof Swal !== 'undefined') {
		Swal.fire({
			title: '¿Eliminar Usuario?',
			text: '¿Está seguro de eliminar al usuario "' + nombre + '"? Esta acción no se puede deshacer.',
			icon: 'warning',
			showCancelButton: true,
			confirmButtonColor: '#d33',
			cancelButtonColor: '#6c757d',
			confirmButtonText: '<i class="fa-solid fa-trash-can me-1"></i> Sí, eliminar',
			cancelButtonText: 'Cancelar'
		}).then(function(result) {
			if (result.isConfirmed) {
				window.location.href = 'home.php?pg=<?=$pg;?>&idusu=' + idusu + '&opera=Eliminar<?= ($nodocfil ? '&nodocfil='.urlencode($nodocfil) : ''); ?><?= ($idficfil ? '&idficfil='.urlencode($idficfil) : ''); ?>';
			}
		});
	} else {
		if (confirm('¿Confirma que desea eliminar este usuario?')) {
			window.location.href = 'home.php?pg=<?=$pg;?>&idusu=' + idusu + '&opera=Eliminar<?= ($nodocfil ? '&nodocfil='.urlencode($nodocfil) : ''); ?><?= ($idficfil ? '&idficfil='.urlencode($idficfil) : ''); ?>';
		}
	}
}

// Confirmación de Cambio Masivo de Contraseñas
function confirmarCambioContrasenas() {
	var inico = document.getElementById("inico").value.trim();
	var finco = document.getElementById("finco").value.trim();

	if (inico === '' && finco === '') {
		if (typeof Swal !== 'undefined') {
			Swal.fire('Atención', 'Ingrese al menos un prefijo o sufijo para definir la estructura de la contraseña.', 'warning');
		} else {
			alert('Ingrese al menos un prefijo o sufijo.');
		}
		return;
	}

	if (typeof Swal !== 'undefined') {
		Swal.fire({
			title: '¿Confirmar cambio masivo?',
			text: 'Esta acción actualizará la contraseña de TODOS los usuarios registrados según el patrón definido. ¿Desea continuar?',
			icon: 'warning',
			showCancelButton: true,
			confirmButtonColor: '#ffc107',
			cancelButtonColor: '#6c757d',
			confirmButtonText: '<span class="text-dark fw-bold"><i class="fa-solid fa-arrows-rotate me-1"></i> Sí, cambiar contraseñas</span>',
			cancelButtonText: 'Cancelar'
		}).then(function(result) {
			if (result.isConfirmed) {
				document.getElementById("frmCamCon").submit();
			}
		});
	} else {
		if (confirm('¿Está seguro de cambiar las contraseñas masivamente a todos los usuarios?')) {
			document.getElementById("frmCamCon").submit();
		}
	}
}
</script>

<!-- Notificaciones SweetAlert2 según parámetro de URL -->
<?php if(isset($_GET['msg'])){ 
	$msg = $_GET['msg'];
	$cant = isset($_GET['cant']) ? (int)$_GET['cant'] : 0;
?>
<script>
document.addEventListener('DOMContentLoaded', function() {
	if (typeof Swal !== 'undefined') {
		<?php if($msg == 'guardado'){ ?>
			Swal.fire('¡Registrado!', 'El usuario se ha registrado exitosamente en el sistema.', 'success');
		<?php } else if($msg == 'actualizado'){ ?>
			Swal.fire('¡Actualizado!', 'Los datos del usuario fueron actualizados correctamente.', 'success');
		<?php } else if($msg == 'eliminado'){ ?>
			Swal.fire('¡Eliminado!', 'El usuario se ha eliminado correctamente.', 'success');
		<?php } else if($msg == 'error_fk'){ ?>
			Swal.fire('No se puede eliminar', 'El usuario tiene registros asociados (asistencias, bitácoras, etc.). Se recomienda desactivarlo utilizando el botón de estado.', 'warning');
		<?php } else if($msg == 'pass_ok'){ ?>
			Swal.fire('¡Contraseñas Actualizadas!', 'Se han cambiado las contraseñas masivamente con éxito (<?=$cant;?> usuarios actualizados).', 'success');
		<?php } else if($msg == 'perfil_ok'){ ?>
			Swal.fire('¡Perfiles Guardados!', 'Los perfiles del usuario fueron actualizados correctamente en tiempo real.', 'success');
		<?php } else if($msg == 'perfil_masivo_ok'){ ?>
			Swal.fire('¡Asignación Masiva Exitosa!', 'Se han asignado los perfiles y actualizado los permisos en tiempo real para <?=$cant;?> usuario(s).', 'success');
		<?php } else if($msg == 'ficha_ok'){ ?>
			Swal.fire('¡Ficha Asociada!', 'La ficha ha sido asociada al usuario exitosamente.', 'success');
		<?php } else if($msg == 'ficha_del'){ ?>
			Swal.fire('¡Ficha Desvinculada!', 'La ficha fue desvinculada del usuario.', 'info');
		<?php } else if($msg == 'ficha_duplicada'){ ?>
			Swal.fire('Ficha ya vinculada', 'El usuario ya cuenta con la ficha seleccionada previamente.', 'info');
		<?php } else if($msg == 'ficha_no_existe'){ ?>
			Swal.fire('Ficha no encontrada', 'El número de ficha ingresado no existe en la base de datos del sistema.', 'error');
		<?php } else if($msg == 'ficha_invalida'){ ?>
			Swal.fire('Ficha requerida', 'Por favor digite o seleccione un número de ficha válido para vincular.', 'warning');
		<?php } else if($msg == 'estado_ok'){ ?>
			Swal.fire('¡Estado Actualizado!', 'El estado del usuario se actualizó correctamente.', 'success');
		<?php } ?>
	}
});
</script>
<?php } ?>
