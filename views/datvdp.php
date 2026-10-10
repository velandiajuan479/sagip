<?php 
require_once('controllers/usucusu.php'); 

// Resolver datos de usuario e imagen de perfil
$fotoDb = trim($datOne[0]['fotcan'] ?? '');
$tieneFoto = (!empty($fotoDb) && $fotoDb !== 'fcan/' && $fotoDb !== 'img/user.jpg' && $fotoDb !== 'image/user-default.webp');
$fotoPerfil = ($tieneFoto && (file_exists($fotoDb) || file_exists('../' . $fotoDb))) ? $fotoDb : 'image/user-default.webp';
if (!file_exists($fotoPerfil) && file_exists('img/user.jpg')) {
	$fotoPerfil = 'img/user.jpg';
}

$nomUsuario = $datOne[0]['nomusu'] ?? 'Usuario';
$nomPerfil = $userStats['perfil_principal'] ?? ($datOne[0]['nomper'] ?? 'Usuario');
$nomCentro = $datOne[0]['nomcen'] ?? 'Centro de Formación';
$idFicha = $userStats['ficha_principal']['idfic'] ?? ($datOne[0]['idfic'] ?? null);
$nomFicha = $userStats['ficha_principal']['nomfic'] ?? ($datOne[0]['nomfic'] ?? 'Sin ficha asignada');
$telUsuario = $datOne[0]['telcan'] ?? '';
$completitud = $userStats['completitud'] ?? 75;
?>

<style>
/* Estilos para fotografía de perfil ampliada, visible y con interactividad */
.profile-avatar-wrapper {
	width: 195px;
	height: 195px;
	position: relative;
	border-radius: 50%;
	overflow: hidden;
	cursor: pointer;
	box-shadow: 0 10px 25px rgba(0,0,0,0.12);
	border: 4px solid #ffffff;
	transition: transform 0.25s ease, box-shadow 0.25s ease;
	background-color: #f8fafc;
}
.profile-avatar-wrapper:hover {
	transform: scale(1.02);
	box-shadow: 0 14px 30px rgba(0,0,0,0.18);
}
.profile-avatar-wrapper:hover .avatar-hover-overlay {
	opacity: 1;
}
.avatar-hover-overlay {
	position: absolute;
	top: 0;
	left: 0;
	width: 100%;
	height: 100%;
	background: rgba(11, 28, 48, 0.45);
	opacity: 0;
	display: flex;
	flex-direction: column;
	align-items: center;
	justify-content: center;
	gap: 4px;
	transition: opacity 0.25s ease;
	color: #ffffff;
	z-index: 2;
}
</style>

<div class="conte">
	<!-- Título Principal de la Vista (Sin iconos de + y -) -->
	<?php echo titulo2("<i class='" . $icono . "'></i> Datos Personales", 0); ?>

	<!-- ========================================================================= -->
	<!-- MEJORA 4.1: DASHBOARD EN TIEMPO REAL INMEDIATAMENTE DEBAJO DEL TÍTULO    -->
	<!-- ========================================================================= -->
	<div class="row my-4 g-4">
		<!-- KPI: Rol / Perfil en el Sistema -->
		<div class="col-12 col-md-4">
			<div class="kpi-card h-100 d-flex flex-column justify-content-between">
				<div class="d-flex align-items-center justify-content-between">
					<div>
						<span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #3e4a3d;">Rol Institucional</span>
						<div class="d-flex align-items-baseline gap-2 mt-2">
							<span class="fw-bold text-truncate" style="font-size: 20px; line-height: 1.2; color: #0b1c30; max-width: 170px;" title="<?= htmlspecialchars($nomPerfil); ?>"><?= htmlspecialchars($nomPerfil); ?></span>
						</div>
					</div>
					<div class="kpi-icon-box" style="background-color: #b5dcfe; color: #3c627f;">
						<i class="fa-solid fa-user-shield fs-4"></i>
					</div>
				</div>
				<div class="kpi-footer-strip">
					<span class="fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 11px; color: #3c627f;">
						<i class="fa-solid fa-id-badge" style="font-size: 12px;"></i> Perfil Asignado
					</span>
					<span style="font-size: 11px; color: #3e4a3d;">Rol Primario</span>
				</div>
			</div>
		</div>

		<!-- KPI: Estado del Usuario -->
		<div class="col-12 col-md-4">
			<div class="kpi-card h-100 d-flex flex-column justify-content-between">
				<div class="d-flex align-items-center justify-content-between">
					<div>
						<span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #006b29;">Estado de Cuenta</span>
						<div class="d-flex align-items-baseline gap-2 mt-2">
							<span class="fw-bold" style="font-size: 26px; line-height: 1; color: #006b29;"><?= ($datOne && $datOne[0]['actusu'] == 1) ? 'Activo' : 'Inactivo'; ?></span>
						</div>
					</div>
					<div class="kpi-icon-box" style="background-color: #7afd88; color: #006b24;">
						<i class="fa-solid fa-user-check fs-4"></i>
					</div>
				</div>
				<div class="kpi-footer-strip">
					<span class="fw-semibold d-inline-flex align-items-center gap-2" style="font-size: 11px; color: #006b29;">
						<span class="pulse-dot"></span> Acceso Habilitado
					</span>
					<span style="font-size: 11px; color: #3e4a3d;">En línea</span>
				</div>
			</div>
		</div>

		<!-- KPI: Ficha de Formación -->
		<div class="col-12 col-md-4">
			<div class="kpi-card h-100 d-flex flex-column justify-content-between">
				<div class="d-flex align-items-center justify-content-between">
					<div>
						<span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #3c627f;">Ficha de Formación</span>
						<div class="d-flex align-items-baseline gap-2 mt-2">
							<span class="fw-bold" style="font-size: 24px; line-height: 1; color: #0b1c30;"><?= $idFicha ? htmlspecialchars($idFicha) : 'No vinculada'; ?></span>
						</div>
					</div>
					<div class="kpi-icon-box" style="background-color: #e5eeff; color: #3c627f;">
						<i class="fa-solid fa-graduation-cap fs-4"></i>
					</div>
				</div>
				<div class="kpi-footer-strip">
					<span class="fw-semibold d-inline-flex align-items-center gap-1 text-truncate" style="font-size: 11px; color: #3c627f; max-width: 160px;" title="<?= htmlspecialchars($nomFicha); ?>">
						<i class="fa-solid fa-book-bookmark" style="font-size: 12px;"></i> <?= htmlspecialchars($nomFicha); ?>
					</span>
					<span style="font-size: 11px; color: #3e4a3d;">Programa</span>
				</div>
			</div>
		</div>

	</div>

	<!-- ========================================================================= -->
	<!-- MEJORA 4.2: SECCIÓN PRINCIPAL CON PERFIL PROFESIONAL Y FORMULARIO MEJORADO -->
	<!-- ========================================================================= -->
	<form id="frmDatosPersonales" name="frm1" action="home.php?pg=<?= $pg; ?>" method="POST" enctype="multipart/form-data">
		<div class="row g-4">
			
			<!-- COLUMNA IZQUIERDA: TARJETA DE PERFIL Y FOTO -->
			<div class="col-lg-4 col-xl-4">
				<div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
					<div class="card-header bg-success bg-gradient text-white text-center py-4 px-3 rounded-top-4 border-0">
						<span class="badge bg-white text-success px-3 py-1 rounded-pill fw-bold shadow-sm mb-2" style="font-size: 0.85rem;">
							<i class="fa-solid fa-id-badge me-1"></i><?= htmlspecialchars($nomPerfil); ?>
						</span>
						<h5 class="fw-bold mb-1 text-white text-truncate" title="<?= htmlspecialchars($nomUsuario); ?>">
							<?= htmlspecialchars($nomUsuario); ?>
						</h5>
						<small class="text-white text-opacity-75 d-block text-truncate" title="<?= htmlspecialchars($nomCentro); ?>">
							<i class="fa-solid fa-building-columns me-1"></i><?= htmlspecialchars($nomCentro); ?>
						</small>
					</div>

					<div class="card-body text-center p-4 d-flex flex-column align-items-center">
						<!-- MEJORA 3.3: Contenedor del Avatar / Foto de Perfil Más Visible y Ampliable -->
						<div class="my-2 position-relative d-inline-block">
							<div class="profile-avatar-wrapper shadow-lg" onclick="abrirModalVerFoto();" title="Haga clic para ver imagen ampliada">
								<img src="<?= htmlspecialchars($fotoPerfil); ?>" alt="Foto de perfil" class="w-100 h-100" style="object-fit: cover;" id="profileImage" onerror="this.src='img/user.jpg';">
								<div class="avatar-hover-overlay">
									<i class="fa-solid fa-magnifying-glass-plus fa-2x"></i>
									<span class="small fw-semibold mt-1">Ver foto</span>
								</div>
							</div>

							<?php if (!$tieneFoto) { ?>
								<!-- Botón flotante para subir la foto por única vez -->
								<label for="fotoInput" class="btn btn-success rounded-circle shadow position-absolute bottom-0 end-0 d-flex align-items-center justify-content-center p-0" style="width: 44px; height: 44px; cursor: pointer; z-index: 5;"
									data-bs-toggle="tooltip" data-bs-placement="bottom"
									title="Subir fotografía de perfil (solo se permite una única vez). Formatos admitidos: PNG, JPG, JPEG, WEBP.">
									<i class="fa-solid fa-camera"></i>
								</label>
								<input type="file" id="fotoInput" name="foto" accept="image/png, image/jpeg, image/jpg, image/webp" class="d-none" onchange="validarYPrevisualizarFoto(event)">
							<?php } else { ?>
								<!-- Botón indicador que informa dirigirse a biblioteca -->
								<button type="button" class="btn btn-success rounded-circle shadow position-absolute bottom-0 end-0 d-flex align-items-center justify-content-center p-0" style="width: 44px; height: 44px; cursor: pointer; z-index: 5;"
									onclick="mostrarAvisoBiblioteca();"
									title="Diríjase a biblioteca para cambiar su foto de perfil">
									<i class="fa-solid fa-camera"></i>
								</button>
							<?php } ?>
						</div>

						<!-- Botones de Acción para Fotografía -->
						<div class="d-flex gap-2 justify-content-center mt-3 flex-wrap">
							<?php if (!$tieneFoto) { ?>
								<button type="button" class="btn btn-outline-success btn-sm rounded-pill px-3 shadow-sm fw-bold" onclick="document.getElementById('fotoInput').click();">
									<i class="fa-solid fa-camera me-1"></i> Cargar Foto
								</button>
							<?php } else { ?>
								<button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-sm fw-semibold" onclick="mostrarAvisoBiblioteca();" title="Diríjase a biblioteca para cambiar su foto de perfil">
									<i class="fa-solid fa-camera me-1"></i> Cambiar Foto
								</button>
							<?php } ?>
							<button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-sm" onclick="abrirModalVerFoto();">
								<i class="fa-solid fa-eye me-1"></i> Ver en Grande
							</button>
						</div>

						<!-- Estados y Avisos de Fotografía -->
						<div class="mt-3 text-center w-100">
							<div id="previewBadge" class="d-none alert alert-success py-2 px-3 small mb-2 rounded-3 shadow-sm border border-success border-opacity-25 text-start">
								<i class="fa-solid fa-circle-check text-success me-1"></i>
								<strong>Imagen lista:</strong> <span id="previewNombreArchivo"></span>. Haga clic en <em>Guardar y Actualizar Datos</em> para aplicarla.
							</div>

							<?php if ($tieneFoto) { ?>
								<div class="alert alert-info border-0 shadow-sm rounded-3 text-start p-3 mb-0" role="alert" style="background-color: #e8f4fd; border-left: 4px solid #0284c7 !important;">
									<div class="d-flex align-items-start gap-2">
										<i class="fa-solid fa-circle-info fa-lg text-primary mt-1"></i>
										<div>
											<strong class="text-primary d-block mb-1">Fotografía Registrada:</strong>
											<p class="m-0 small text-dark" style="line-height: 1.4;">
												Diríjase a biblioteca para cambiar su foto de perfil.
											</p>
										</div>
									</div>
								</div>
							<?php } else { ?>
								<div class="alert alert-warning border-0 shadow-sm rounded-3 text-start p-3 mb-0" role="alert" style="background-color: #fffbeb; border-left: 4px solid #f59e0b !important;">
									<div class="d-flex align-items-start gap-2">
										<i class="fa-solid fa-clock text-warning fa-lg mt-1"></i>
										<div>
											<strong class="text-dark d-block mb-1">Foto pendiente por registrar:</strong>
											<p class="m-0 small text-muted" style="line-height: 1.4;">
												Solo puede cambiar una sola vez la foto de perfil. Formatos: PNG, JPG, JPEG o WEBP.
											</p>
										</div>
									</div>
								</div>
							<?php } ?>
						</div>

						<!-- Resumen adicional del perfil -->
						<div class="w-100 border-top pt-3 mt-3 text-start">
							<h6 class="fw-bold text-dark mb-2 small text-uppercase" style="letter-spacing: 0.5px;">
								<i class="fa-solid fa-circle-user me-1 text-success"></i>Detalles de la Cuenta
							</h6>
							<ul class="list-unstyled mb-0 small text-muted">
								<li class="mb-2 d-flex justify-content-between">
									<span><i class="fa-solid fa-hashtag me-1"></i>Documento:</span>
									<span class="fw-semibold text-dark"><?= htmlspecialchars($datOne[0]['ndocusu'] ?? 'N/A'); ?></span>
								</li>
								<li class="mb-2 d-flex justify-content-between">
									<span><i class="fa-solid fa-droplet me-1 text-danger"></i>Tipo de Sangre / RH:</span>
									<span class="fw-semibold text-dark"><?= htmlspecialchars($datOne[0]['rh'] ?? 'N/A'); ?></span>
								</li>
								<li class="mb-2 d-flex justify-content-between">
									<span><i class="fa-solid fa-venus-mars me-1 text-info"></i>Género:</span>
									<span class="fw-semibold text-dark"><?= htmlspecialchars($datOne[0]['gen'] ?? 'N/A'); ?></span>
								</li>
								<?php if($idFicha){ ?>
									<li class="d-flex justify-content-between">
										<span><i class="fa-solid fa-bookmark me-1 text-primary"></i>No. Ficha:</span>
										<span class="badge bg-primary fs-8"><?= htmlspecialchars($idFicha); ?></span>
									</li>
								<?php } ?>
							</ul>
						</div>

					</div>
				</div>
			</div>

			<!-- COLUMNA DERECHA: FORMULARIO DE EDICIÓN DE DATOS -->
			<div class="col-lg-8 col-xl-8">
				<div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
					<div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
						<h5 class="fw-bold text-success mb-0 fs-5">
							<i class="fa-solid fa-user-pen me-2"></i>Actualizar Información Personal
						</h5>
						<span class="text-muted small">
							<i class="fa-solid fa-lock text-secondary me-1"></i>Conexión Segura
						</span>
					</div>

					<div class="row g-3">
						<!-- Nombre Completo -->
						<div class="form-group col-md-12">
							<label for="nomusu" class="form-label fw-semibold text-dark mb-1">
								<i class="fa-solid fa-user me-1 text-success"></i>Nombre Completo <span class="text-danger">*</span>
							</label>
							<input type="text" name="nomusu" id="nomusu" maxlength="70" class="form-control form--input-default" required value="<?php if ($datOne) echo htmlspecialchars($datOne[0]['nomusu']); ?>" placeholder="Nombres y apellidos completos">
						</div>

						<!-- Tipo de Documento -->
						<div class="form-group col-md-6">
							<label for="tdousu" class="form-label fw-semibold text-dark mb-1">
								<i class="fa-solid fa-address-card me-1 text-success"></i>Tipo de Documento <span class="text-danger">*</span>
							</label>
							<select name="tdousu" id="tdousu" class="form-select form--input-default" required>
								<?php
								if ($tdos) {
									foreach ($tdos as $de) {
										$sel = ($datOne && $datOne[0]['tdousu'] == $de['idval']) ? " selected " : "";
										echo '<option value="' . $de['idval'] . '"' . $sel . '>' . htmlspecialchars($de['nomval']) . '</option>';
									}
								} ?>
							</select>
						</div>

						<!-- No. Documento (Readonly por integridad) -->
						<div class="form-group col-md-6">
							<label for="ndocusu" class="form-label fw-semibold text-dark mb-1">
								<i class="fa-solid fa-id-card me-1 text-success"></i>No. Documento
							</label>
							<div class="input-group">
								<input type="text" name="ndocusu" id="ndocusu" class="form-control form--input-default bg-light" required value="<?php if ($datOne) echo htmlspecialchars($datOne[0]['ndocusu']); ?>" readonly title="El número de documento no puede ser modificado por seguridad">
								<span class="input-group-text bg-light text-muted border-start-0" title="Campo protegido"><i class="fa-solid fa-lock"></i></span>
							</div>
							<small class="text-muted" style="font-size: 0.75rem;">Para cambios en documento, acérquese a Administración.</small>
						</div>

						<!-- RH -->
						<div class="form-group col-md-6">
							<label for="rhusu" class="form-label fw-semibold text-dark mb-1">
								<i class="fa-solid fa-droplet me-1 text-danger"></i>Grupo Sanguíneo y RH <span class="text-danger">*</span>
							</label>
							<select name="rhusu" id="rhusu" class="form-select form--input-default" required>
								<?php
								if ($rhs) {
									foreach ($rhs as $de) {
										$sel = ($datOne && $datOne[0]['rhusu'] == $de['idval']) ? " selected " : "";
										echo '<option value="' . $de['idval'] . '"' . $sel . '>' . htmlspecialchars($de['nomval']) . '</option>';
									}
								} ?>
							</select>
						</div>

						<!-- Género -->
						<div class="form-group col-md-6">
							<label for="genusu" class="form-label fw-semibold text-dark mb-1">
								<i class="fa-solid fa-venus-mars me-1 text-info"></i>Género <span class="text-danger">*</span>
							</label>
							<select name="genusu" id="genusu" class="form-select form--input-default" required>
								<?php
								if ($gen) {
									foreach ($gen as $de) {
										$sel = ($datOne && $datOne[0]['genusu'] == $de['idval']) ? " selected " : "";
										echo '<option value="' . $de['idval'] . '"' . $sel . '>' . htmlspecialchars($de['nomval']) . '</option>';
									}
								} ?>
							</select>
						</div>

						<!-- Email -->
						<div class="form-group col-md-12">
							<label for="emausu" class="form-label fw-semibold text-dark mb-1">
								<i class="fa-solid fa-envelope me-1 text-success"></i>Correo Electrónico <span class="text-danger">*</span>
							</label>
							<input type="email" name="emausu" id="emausu" maxlength="70" class="form-control form--input-default" required value="<?php if ($datOne) echo htmlspecialchars($datOne[0]['emausu']); ?>" placeholder="ejemplo@misena.edu.co">
						</div>

						<!-- MEJORA 4.1: Teléfono con validación estricta numérica y límite de dígitos -->
						<div class="form-group col-md-6">
							<label for="telcan" class="form-label fw-semibold text-dark mb-1 d-flex justify-content-between align-items-center">
								<span><i class="fa-solid fa-phone me-1 text-success"></i>Teléfono de Contacto <span class="text-danger">*</span></span>
								<small id="telCountBadge" class="badge bg-light text-muted border fw-normal" style="font-size: 0.72rem;">0/10 dígitos</small>
							</label>
							<div class="input-group has-validation">
								<span class="input-group-text bg-white text-muted"><i class="fa-solid fa-mobile-screen-button"></i></span>
								<input type="text" 
									   inputmode="numeric" 
									   name="telcan" 
									   id="telcan" 
									   maxlength="10"
									   pattern="[0-9]{7,10}" 
									   class="form-control form--input-default" 
									   value="<?php if ($datOne) echo htmlspecialchars($telUsuario); ?>" 
									   placeholder="Ej: 3201234567" 
									   autocomplete="tel"
									   required
									   oninput="validarTelefonoInput(this)" 
									   onkeypress="return soloPermitirDigitos(event)"
									   onpaste="manejarPegadoTelefono(event)">
								<div id="telErrorFeedback" class="invalid-feedback">
									Ingrese un número de teléfono válido (entre 7 y 10 dígitos numéricos).
								</div>
							</div>
							<small id="telHelp" class="text-muted d-block mt-1" style="font-size: 0.76rem;">
								<i class="fa-solid fa-circle-check text-success me-1"></i>Solo números, entre 7 y 10 dígitos (sin espacios ni guiones).
							</small>
						</div>

						<!-- Contraseña con botón mostrar/ocultar -->
						<div class="form-group col-md-6">
							<label for="pasusu" class="form-label fw-semibold text-dark mb-1">
								<i class="fa-solid fa-key me-1 text-success"></i>Contraseña de Acceso
							</label>
							<div class="input-group">
								<input type="password" name="pasusu" id="pasusu" maxlength="70" class="form-control form--input-default" placeholder="Nueva contraseña (opcional)" <?php if (!$datOne) echo "required"; ?>>
								<button type="button" class="btn btn-outline-secondary btn-toggle-pass" id="btnTogglePass" onclick="alternarVisibilidadPassword()" title="Mostrar u ocultar contraseña">
									<i class="fa-solid fa-eye" id="iconTogglePass"></i>
								</button>
							</div>
							<small class="text-muted d-block mt-1" style="font-size: 0.75rem;">
								Deje este campo vacío si no desea modificar su contraseña actual.
							</small>
						</div>

						<!-- Botón de Envío e Inputs Ocultos -->
						<div class="form-group col-12 mt-4 pt-3 border-top d-flex justify-content-end align-items-center gap-3">
							<input type="hidden" name="idusu" value="<?php if ($datOne) echo $datOne[0]['idusu']; ?>">
							<input type="hidden" name="opera" value="save">
							<input type="hidden" name="is_datos_personales" value="1">
							<input type="hidden" name="noca" value="<?php if ($datOne) echo $datOne[0]['noca']; ?>">
							<input type="hidden" name="actusu" value="1">
							<input type="hidden" name="idcen" value="<?php if ($datOne) echo $datOne[0]['idcen']; ?>">
							<input type="hidden" name="idper" value="<?php if ($datOne) echo $datOne[0]['idper']; ?>">
							
							<button type="submit" class="btn btn-success px-4 py-2 fw-bold rounded-pill shadow-sm">
								<i class="fa-solid fa-floppy-disk me-2"></i>Guardar y Actualizar Datos
							</button>
						</div>

					</div>
				</div>
			</div>

		</div>
	</form>
</div>

<!-- ========================================================================= -->
<!-- JAVASCRIPT: VALIDACIONES ESTRICTAS Y PREVISUALIZACIÓN                      -->
<!-- ========================================================================= -->
<script>
document.addEventListener("DOMContentLoaded", function () {
	// Inicializar contador de dígitos del teléfono al cargar la vista
	let telInput = document.getElementById('telcan');
	if (telInput) {
		validarTelefonoInput(telInput);
	}

	// Activar tooltips de Bootstrap si están disponibles
	if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
		let tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
		tooltipTriggerList.map(function (tooltipTriggerEl) {
			return new bootstrap.Tooltip(tooltipTriggerEl);
		});
	}
});

// MEJORA 4.1: Validación estricta de teléfono en frontend
function soloPermitirDigitos(event) {
	// Permitir solo teclas numéricas (0-9) y teclas de control como backspace, enter, tab
	const charCode = (event.which) ? event.which : event.keyCode;
	if (charCode > 31 && (charCode < 48 || charCode > 57)) {
		event.preventDefault();
		return false;
	}
	return true;
}

function manejarPegadoTelefono(event) {
	event.preventDefault();
	let clipboardData = event.clipboardData || window.clipboardData;
	let pastedText = clipboardData.getData('text');
	// Filtrar solo dígitos y limitar a 10
	let cleanDigits = pastedText.replace(/\D/g, '').slice(0, 10);
	document.execCommand('insertText', false, cleanDigits);
}

function validarTelefonoInput(input) {
	// Remover cualquier caracter que no sea dígito
	let valorLimpio = input.value.replace(/\D/g, '');
	if (valorLimpio.length > 10) {
		valorLimpio = valorLimpio.slice(0, 10);
	}
	input.value = valorLimpio;

	let count = valorLimpio.length;
	let badge = document.getElementById('telCountBadge');
	let errorFeedback = document.getElementById('telErrorFeedback');

	if (badge) {
		badge.textContent = count + '/10 dígitos';
		if (count >= 7 && count <= 10) {
			badge.className = 'badge bg-success bg-opacity-10 text-success border border-success fw-normal';
		} else if (count > 0) {
			badge.className = 'badge bg-warning bg-opacity-10 text-warning border border-warning fw-normal';
		} else {
			badge.className = 'badge bg-light text-muted border fw-normal';
		}
	}

	if (count > 0 && (count < 7 || count > 10)) {
		input.classList.add('is-invalid');
		input.classList.remove('is-valid');
		if (errorFeedback) errorFeedback.style.display = 'block';
	} else if (count >= 7 && count <= 10) {
		input.classList.remove('is-invalid');
		input.classList.add('is-valid');
		if (errorFeedback) errorFeedback.style.display = 'none';
	} else {
		input.classList.remove('is-invalid');
		input.classList.remove('is-valid');
		if (errorFeedback) errorFeedback.style.display = 'none';
	}
}

// MEJORA 3.2: Validación y previsualización robusta de fotografía sin errores en extensiones válidas
function validarYPrevisualizarFoto(event) {
	const fileInput = event.target;
	const file = fileInput.files[0];

	if (!file) return;

	// Validar extensión admitida de imagen (png, jpg, jpeg, webp, jfif, gif)
	const ext = file.name.split('.').pop().toLowerCase();
	const allowedExtensions = ['png', 'jpg', 'jpeg', 'webp', 'jfif', 'gif'];
	const allowedMimes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp', 'image/jfif', 'image/gif', 'image/pjpeg', 'image/x-png'];

	if (!allowedExtensions.includes(ext) && !allowedMimes.includes(file.type)) {
		if (typeof Swal !== 'undefined') {
			Swal.fire({
				icon: 'warning',
				title: 'Extensión no válida',
				text: 'El archivo seleccionado no tiene una extensión de imagen permitida. Seleccione un archivo válido (.png, .jpg, .jpeg o .webp).',
				confirmButtonColor: '#00af00'
			});
		} else {
			alert('Extensión no válida. Seleccione un archivo de imagen (.png, .jpg, .jpeg o .webp).');
		}
		fileInput.value = '';
		return;
	}

	// Validar peso máximo (5MB = 5242880 bytes)
	const maxSize = 5 * 1024 * 1024;
	if (file.size > maxSize) {
		if (typeof Swal !== 'undefined') {
			Swal.fire({
				icon: 'warning',
				title: 'Imagen muy pesada',
				text: 'La fotografía no debe superar los 5MB de peso.',
				confirmButtonColor: '#00af00'
			});
		} else {
			alert('La fotografía no debe superar los 5MB de peso.');
		}
		fileInput.value = '';
		return;
	}

	// Previsualización instantánea con FileReader
	const reader = new FileReader();
	reader.onload = function (e) {
		const profileImg = document.getElementById('profileImage');
		if (profileImg) {
			profileImg.src = e.target.result;
		}
		const modalImg = document.getElementById('modalFotoSrc');
		if (modalImg) {
			modalImg.src = e.target.result;
		}
		const previewBadge = document.getElementById('previewBadge');
		const txtBadge = document.getElementById('previewNombreArchivo');
		if (txtBadge) {
			txtBadge.textContent = file.name;
		}
		if (previewBadge) {
			previewBadge.classList.remove('d-none');
		}
	};
	reader.readAsDataURL(file);
}

// MEJORA 3.3: Abrir modal con la fotografía de perfil ampliada
function abrirModalVerFoto() {
	const modalEl = document.getElementById('modalVerFotoPerfil');
	if (modalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
		const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
		modal.show();
	}
}

// Aviso cuando el usuario ya tiene fotografía y debe dirigirse a biblioteca
function mostrarAvisoBiblioteca() {
	if (typeof Swal !== 'undefined') {
		Swal.fire({
			icon: 'info',
			title: 'Fotografía Registrada',
			text: 'Diríjase a biblioteca para cambiar su foto de perfil.',
			confirmButtonColor: '#00af00'
		});
	} else {
		alert('Diríjase a biblioteca para cambiar su foto de perfil.');
	}
}

// Alternar visibilidad de contraseña
function alternarVisibilidadPassword() {
	const passInput = document.getElementById('pasusu');
	const icon = document.getElementById('iconTogglePass');
	if (passInput && icon) {
		if (passInput.type === 'password') {
			passInput.type = 'text';
			icon.classList.remove('fa-eye');
			icon.classList.add('fa-eye-slash');
		} else {
			passInput.type = 'password';
			icon.classList.remove('fa-eye-slash');
			icon.classList.add('fa-eye');
		}
	}
}

// Validación antes de enviar formulario
document.getElementById('frmDatosPersonales')?.addEventListener('submit', function (e) {
	const telInput = document.getElementById('telcan');
	if (telInput) {
		const telVal = telInput.value.trim();
		if (telVal.length < 7 || telVal.length > 10) {
			e.preventDefault();
			if (typeof Swal !== 'undefined') {
				Swal.fire({
					icon: 'error',
					title: 'Número telefónico inválido',
					text: 'El campo teléfono debe contener entre 7 y 10 dígitos numéricos.',
					confirmButtonColor: '#00af00'
				});
			} else {
				alert('El campo teléfono debe contener entre 7 y 10 dígitos numéricos.');
			}
			telInput.focus();
			return false;
		}
	}
});
</script>

<!-- MEJORA 3.3: MODAL PARA VER FOTOGRAFÍA DE PERFIL EN ALTA RESOLUCIÓN -->
<div class="modal fade" id="modalVerFotoPerfil" tabindex="-1" aria-labelledby="lblModalVerFoto" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
			<div class="modal-header text-white" style="background: linear-gradient(135deg, #117f09 0%, #00af00 100%);">
				<div class="d-flex align-items-center gap-2">
					<i class="fa-solid fa-image fa-lg"></i>
					<div>
						<h5 class="modal-title mb-0 fw-bold fs-6" id="lblModalVerFoto">Fotografía de Perfil</h5>
						<small class="text-white text-opacity-75"><?= htmlspecialchars($nomUsuario); ?></small>
					</div>
				</div>
				<button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body p-4 text-center bg-light">
				<div class="p-2 bg-white rounded-4 shadow-sm border d-inline-block">
					<img src="<?= htmlspecialchars($fotoPerfil); ?>" id="modalFotoSrc" alt="Fotografía en alta resolución" class="img-fluid rounded-3" style="max-height: 420px; max-width: 100%; object-fit: contain;" onerror="this.src='img/user.jpg';">
				</div>
				<h6 class="fw-bold text-dark mt-3 mb-1"><?= htmlspecialchars($nomUsuario); ?></h6>
				<span class="badge bg-success bg-opacity-10 text-success border border-success px-3 py-1 rounded-pill small">
					<?= htmlspecialchars($nomPerfil); ?>
				</span>
			</div>
			<div class="modal-footer py-2 justify-content-between bg-white">
				<a href="<?= htmlspecialchars($fotoPerfil); ?>" id="btnDescargarFotoPerfil" target="_blank" download class="btn btn-outline-success btn-sm rounded-pill px-3">
					<i class="fa-solid fa-download me-1"></i> Descargar Foto
				</a>
				<button type="button" class="btn btn-secondary btn-sm rounded-pill px-4" data-bs-dismiss="modal">Cerrar</button>
			</div>
		</div>
	</div>
</div>

<!-- Notificaciones SweetAlert2 según retorno de URL -->
<?php if(isset($_GET['msg']) || isset($_GET['err'])){ 
	$msg = $_GET['msg'] ?? '';
	$err = $_GET['err'] ?? '';
?>
<script>
document.addEventListener('DOMContentLoaded', function() {
	if (typeof Swal !== 'undefined') {
		<?php if($err === 'aviso_biblioteca'){ ?>
			Swal.fire({
				icon: 'info',
				title: 'Fotografía Registrada',
				text: 'Diríjase a biblioteca para cambiar su foto de perfil.',
				confirmButtonColor: '#00af00'
			});
		<?php } else if($err === 'ext_invalida'){ ?>
			Swal.fire({
				icon: 'warning',
				title: 'Extensión no permitida',
				text: 'Sus datos se actualizaron correctamente, pero la fotografía no fue modificada debido a que su formato no es válido (solo se admiten .png, .jpg, .jpeg, .webp).',
				confirmButtonColor: '#00af00'
			});
		<?php } else if($msg == 'actualizado' || $msg == 'guardado'){ ?>
			Swal.fire({
				icon: 'success',
				title: '¡Datos Actualizados!',
				text: 'Su información personal ha sido actualizada exitosamente.',
				confirmButtonColor: '#00af00'
			});
		<?php } else if($msg == 'foto_error'){ ?>
			Swal.fire({
				icon: 'error',
				title: 'Error al cargar imagen',
				text: 'No fue posible procesar la fotografía. Verifique el formato y peso del archivo.',
				confirmButtonColor: '#00af00'
			});
		<?php } ?>
	}
});
</script>
<?php } ?>