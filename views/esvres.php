<?php
require_once 'controllers/escres.php';
?>

<?php if($usuVen): ?>
<div id="overlayVencido" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 9999; display: flex; align-items: center; justify-content: center; pointer-events: none;">
    <h1 style="margin: 0; font-size: 250px; font-weight: bold; color: #dc3545; text-shadow: 5px 5px 15px rgba(0,0,0,0.5); opacity: 0.8; text-align: center; line-height: 0.9;">VENCIDO</h1>
</div>
<?php endif; ?>

<div class="conte">
	<?php echo titulo2("<i class='".$icono."'></i> Registro de Asistencia", 0); ?>

<?php if(!$ndocusu){ ?>
	<!-- PANTALLA 1: INGRESO / ESCANEO DEL DOCUMENTO -->
	<form class="my-4 px-2 px-sm-3" action="<?=$direc;?>" method="POST" enctype="multipart/form-data">
		<div class="row justify-content-center">
			<div class="col-12 col-sm-10 col-md-8 col-lg-6 col-xl-5 mx-auto">
				<!-- Tarjeta institucional estilo vvidgen.php -->
				<div class="card shadow-sm border-0 rounded-4 overflow-hidden bg-white">
					
					<!-- Cabecera de la Tarjeta limpia institucional -->
					<div class="card-header bg-white py-3 px-4 d-flex align-items-center justify-content-between border-bottom">
						<div class="d-flex align-items-center">
							<div class="kpi-icon-box" style="width: 38px; height: 38px; border-radius: 10px; background-color: #e8f5e9; color: #006b29;">
								<i class="fa-solid fa-address-card fs-6"></i>
							</div>
						</div>
						<span class="badge rounded-pill px-3 py-1 fw-semibold shadow-xs" style="background: #e8f5e9; color: #006b29; border: 1px solid #c8e6c9;">Portería</span>
					</div>

					<div class="card-body p-4 p-md-5 text-center bg-white">
						<h3 class="fw-bold mb-4 text-dark fs-4 fs-md-3">
							Control de Ingreso y Salida
						</h3>

						<!-- Recuadro del Input con borde sutil y fondo estilizado -->
						<div class="p-3 p-sm-4 rounded-4 mb-3 bg-light border border-light-subtle">
							<label for="ndocusu" class="form-label fs-5 text-dark fw-bold mb-3 d-flex align-items-center justify-content-center gap-2">
								<i class="fa-solid fa-id-card" style="color: #006b29;"></i> Número de documento
							</label>
							<div class="input-group input-group-lg shadow-xs rounded-3 overflow-hidden border border-success">
								<span class="input-group-text bg-white border-0 pe-2">
									<i class="fa-solid fa-barcode fa-lg" style="color: #006b29;"></i>
								</span>
								<input type="number" 
									class="form-control border-0 fs-3 text-center fw-bold text-dark" 
									id="ndocusu" 
									maxlength="100" 
									name="ndocusu" 
									required 
									onchange="this.form.submit();" 
									placeholder="Ej. 10000000" 
									autofocus 
									style="letter-spacing: 1.5px;" />
							</div>
							<input type="hidden" name="ope" value="save">
							
							<!-- Comentario de ayuda con tono legible, acorde a vvidgen.php -->
							<div class="mt-3 py-2 px-3 rounded-pill d-inline-flex align-items-center justify-content-center bg-success-subtle text-success-emphasis border border-success-subtle">
								<i class="fa-solid fa-circle-info me-2 text-success" style="font-size: 1.05rem;"></i>
								<span style="font-weight: 600; font-size: 0.875rem;">
									Presione <kbd class="bg-dark text-white px-2 py-1 rounded" style="font-size: 0.8rem;">Enter</kbd> o utilice el lector de código de barras
								</span>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</form>
<?php }elseif($ndocusu){ ?>
	<!-- PANTALLA 2: CONFIRMACIÓN DE INGRESO / SALIDA / NO REGISTRADO (DISEÑO UNIFICADO) -->
	<?php
	if (!$dtUds) {
		if (!$rdtT) {
			// ESTADO: INGRESO REGISTRADO
			$estadoTitulo = "INGRESO REGISTRADO";
			$estadoSub = "Estado de Asistencia";
			$estadoBadge = "Ingreso Exitoso";
			$estadoIcono = "fa-circle-check";
			$colorBgIcono = "#7afd88";
			$colorTxtIcono = "#006b24";
			$colorTema = "#006b29";
			$badgeBg = "#e8f5e9";
			$badgeColor = "#006b29";
			$badgeBorder = "#c8e6c9";
			$badgeExtra = '<span class="pulse-dot me-1"></span>';
			$fotoBadgeTxt = "Ingreso";
			$fotoBadgeIcon = "fa-arrow-right";
			$fotoBadgeBg = "#006b29";
		} else {
			// ESTADO: SALIDA REGISTRADA
			$estadoTitulo = "SALIDA REGISTRADA";
			$estadoSub = "Estado de Asistencia";
			$estadoBadge = "Salida Exitosa";
			$estadoIcono = "fa-arrow-right-from-bracket";
			$colorBgIcono = "#ffdad6";
			$colorTxtIcono = "#ba1a1a";
			$colorTema = "#ba1a1a";
			$badgeBg = "#ffebee";
			$badgeColor = "#ba1a1a";
			$badgeBorder = "#ffcdd2";
			$badgeExtra = '<i class="fa-solid fa-clock-rotate-left me-1"></i>';
			$fotoBadgeTxt = "Salida";
			$fotoBadgeIcon = "fa-arrow-left";
			$fotoBadgeBg = "#ba1a1a";
		}
	} else {
		// ESTADO: USUARIO NO ENCONTRADO
		$estadoTitulo = "USUARIO NO ENCONTRADO";
		$estadoSub = "Estado de Asistencia";
		$estadoBadge = "No Registrado";
		$estadoIcono = "fa-user-xmark";
		$colorBgIcono = "#fee2e2";
		$colorTxtIcono = "#dc2626";
		$colorTema = "#dc2626";
		$badgeBg = "#ffebee";
		$badgeColor = "#dc2626";
		$badgeBorder = "#fecaca";
		$badgeExtra = '<i class="fa-solid fa-triangle-exclamation me-1"></i>';
		$fotoBadgeTxt = "Sin Registro";
		$fotoBadgeIcon = "fa-ban";
		$fotoBadgeBg = "#dc2626";
	}
	?>
	<div class="row justify-content-center my-4 px-2 px-sm-3">
		<div class="col-12 col-md-11 col-lg-10 col-xl-9 mx-auto">
			<!-- Tarjeta institucional con diseño uniforme para los 3 estados -->
			<div class="card shadow-sm border-0 rounded-4 overflow-hidden bg-white">
				
				<!-- CABECERA DEL ESTADO CLARA Y DESTACADA -->
				<div class="card-header bg-white py-3 px-4 d-flex flex-wrap align-items-center justify-content-between gap-2 border-bottom">
					<div class="d-flex align-items-center gap-3">
						<div class="kpi-icon-box" style="width: 44px; height: 44px; border-radius: 12px; background-color: <?=$colorBgIcono;?>; color: <?=$colorTxtIcono;?>;">
							<i class="fa-solid <?=$estadoIcono;?> fs-4"></i>
						</div>
						<div>
							<span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: <?=$colorTema;?>;"><?=$estadoSub;?></span>
							<h3 class="fw-bold mb-0" style="color: <?=$colorTema;?>; font-size: 1.35rem; line-height: 1.2;"><?=$estadoTitulo;?></h3>
						</div>
					</div>
					<div class="d-flex align-items-center gap-2">
						<span class="badge rounded-pill px-3 py-2 fw-semibold shadow-xs" style="background: <?=$badgeBg;?>; color: <?=$badgeColor;?>; border: 1px solid <?=$badgeBorder;?>; font-size: 0.85rem;">
							<?=$badgeExtra;?><?=$estadoBadge;?>
						</span>
					</div>
				</div>

				<div class="card-body p-4 p-md-5 bg-white">
					<!-- Mensaje de error si existe -->
					<?php if($msjerr): ?>
						<div class="alert alert-danger text-center fw-bold parpadea mb-4 fs-5 rounded-3 border border-danger-subtle" role="alert">
							<i class="fa-solid fa-circle-exclamation me-2"></i><?=$msjerr;?>
						</div>
					<?php endif; ?>

					<form name="myForm" action="<?=$direc;?>" method="POST" enctype="multipart/form-data">
						
						<!-- ESTRUCTURA UNIFICADA PARA LOS 3 ESTADOS -->
						<div class="row align-items-center g-4 mb-4">
							
							<!-- FOTO DEL USUARIO -->
							<div class="col-12 col-md-4 text-center">
								<div class="position-relative d-inline-block p-2 rounded-4 shadow-xs bg-light border border-light-subtle">
									<?php if(!$dtUds && $val && !empty($val[0]['fotcan']) && file_exists($val[0]['fotcan'])): ?>
										<img src="<?=$val[0]['fotcan'];?>" class="img-fluid rounded-3 shadow-xs" style="width: 190px; height: 230px; object-fit: cover;">
									<?php else: ?>
										<img src="img/user.jpg" class="img-fluid rounded-3 shadow-xs" style="width: 190px; height: 230px; object-fit: cover;">
									<?php endif; ?>
									<span class="position-absolute bottom-0 start-50 translate-middle-x mb-2 badge px-3 py-2 rounded-pill shadow-xs" style="background: <?=$fotoBadgeBg;?>; font-size: 0.85rem;">
										<i class="fa-solid <?=$fotoBadgeIcon;?> me-1"></i> <?=$fotoBadgeTxt;?>
									</span>
								</div>
							</div>

							<!-- DATOS PRINCIPALES CON EXCELENTE ESPACIADO -->
							<div class="col-12 col-md-8">
								<!-- Nombre del Usuario / Estado en bloque destacado -->
								<div class="p-3 mb-3 rounded-3 bg-white border border-light-subtle shadow-xs">
									<small class="text-uppercase fw-bold text-muted d-block mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px;">
										<i class="fa-solid <?=($dtUds ? 'fa-user-xmark' : 'fa-user');?> me-1" style="color: <?=$colorTema;?>;"></i>
										<?=($dtUds ? 'Estado del Registro' : 'Nombre del Usuario');?>
									</small>
									<?php if(!$dtUds): ?>
										<h2 class="fw-bold mb-0 text-dark" style="font-size: clamp(1.25rem, 3.5vw, 1.85rem); line-height: 1.25;">
											<?=$val[0]['nomusu'];?>
										</h2>
										<input type="hidden" name="idusu" value="<?=$val[0]['idusu'];?>">
									<?php else: ?>
										<h2 class="fw-bold mb-0" style="color: #dc2626; font-size: clamp(1.25rem, 3.5vw, 1.85rem); line-height: 1.25;">
											Usuario no registrado en el sistema
										</h2>
									<?php endif; ?>
								</div>

								<!-- Documento y Fecha/Hora en tarjetas paralelas -->
								<div class="row g-3">
									<!-- Documento -->
									<div class="col-12 col-sm-6">
										<div class="p-3 rounded-3 h-100 bg-light border border-light-subtle">
											<small class="text-uppercase fw-bold text-muted d-block mb-1" style="font-size: 0.75rem;">
												<i class="fa-solid fa-id-card me-1" style="color: <?=$colorTema;?>;"></i> Documento
											</small>
											<span class="fs-4 fw-bold <?=($dtUds ? 'text-danger' : 'text-dark');?> d-block">
												C.C. <?php if($ndocusu) echo number_format($ndocusu, 0, ',', '.'); ?>
											</span>
										</div>
									</div>

									<!-- Fecha y Hora -->
									<div class="col-12 col-sm-6">
										<div class="p-3 rounded-3 h-100 bg-light border border-light-subtle">
											<small class="text-uppercase fw-bold text-muted d-block mb-1" style="font-size: 0.75rem;">
												<i class="fa-solid fa-clock me-1" style="color: <?=$colorTema;?>;"></i> Fecha y Hora
											</small>
											<span class="fs-5 fw-bold text-dark d-block">
												<?=$fechos;?>
											</span>
											<?php if(!$dtUds): ?>
												<input type="hidden" name="ope" value="edi">
											<?php else: ?>
												<input type="hidden" name="dtUds" value="<?=$dtUds;?>">
											<?php endif; ?>
										</div>
									</div>
								</div>

								<!-- Aviso y acción para Usuario No Registrado -->
								<?php if($dtUds): ?>
									<div class="p-3 mt-3 rounded-3 bg-light border border-light-subtle d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-3 shadow-xs">
										<div class="d-flex align-items-center gap-2">
											<i class="fa-solid fa-circle-exclamation text-warning fs-5"></i>
											<span class="text-dark fw-semibold" style="font-size: 0.875rem;">
												Por favor acercarse a la biblioteca para crear el usuario. Gracias
											</span>
										</div>
										<a href="home.php?pg=1325" class="btn btn-outline-danger btn-sm px-3 py-1.5 rounded-pill fw-bold shadow-xs d-inline-flex align-items-center gap-1 text-nowrap">
											<i class="fa-solid fa-arrow-rotate-left"></i> Volver Sin Registro
										</a>
									</div>
								<?php endif; ?>
							</div>
						</div>

						<!-- Elementos y Dispositivos Asociados (si existen y es usuario registrado) -->
						<?php if(!$dtUds && $datEle): ?>
							<div class="mt-4 pt-4 border-top">
								<div class="d-flex align-items-center gap-2 mb-3">
									<div class="kpi-icon-box" style="width: 32px; height: 32px; border-radius: 8px; background-color: #e8f5e9; color: #006b29;">
										<i class="fa-solid fa-laptop fs-6"></i>
									</div>
									<h6 class="fw-bold mb-0 text-dark" style="font-size: 0.95rem;">Elementos y Dispositivos Asociados</h6>
								</div>
								<div class="row g-3">
									<?php foreach($datEle as $dEl): ?>
										<div class="col-12 col-md-4 col-lg-3">
											<div class="card h-100 border border-light-subtle text-center p-3 shadow-xs bg-white rounded-3">
												<div class="mb-2">
													<?php if(!empty($dEl['rutfot']) && file_exists($dEl['rutfot'])): ?>
														<img src="<?=$dEl['rutfot'];?>" class="img-fluid rounded border" style="max-height: 100px; object-fit: contain;">
													<?php else: ?>
														<img src="img/user.jpg" class="img-fluid rounded border" style="max-height: 100px; object-fit: contain;">
													<?php endif; ?>
												</div>
												<div class="form-check d-flex justify-content-center align-items-center gap-2 mb-1">
													<input class="form-check-input" type="checkbox" name="ideles[]" checked value="<?=$dEl['idele'];?>" id="ele_<?=$dEl['idele'];?>">
													<label class="form-check-label fw-bold text-dark" for="ele_<?=$dEl['idele'];?>">
														<?=$dEl['nomele'];?>
													</label>
												</div>
												<small class="text-muted d-block"><?=$dEl['nidele'];?></small>
											</div>
										</div>
									<?php endforeach; ?>
								</div>
							</div>
						<?php endif; ?>

					</form>
				</div>

			</div>
		</div>
	</div>

	<!-- TEMPORIZADOR AUTOMÁTICO TRAS REGISTRO (1s para registrado, 4s para no registrado) -->
	<?php if(!$dtUds): ?>
		<script type="text/javascript">
			setTimeout(function() {
				const form = document.forms['myForm'];
				if (form) {
					form.submit();
				}
			}, 1000);
		</script>
	<?php else: ?>
		<script type="text/javascript">
			setTimeout(function() {
				window.location.href = "home.php?pg=1325";
			}, 4000);
		</script>
	<?php endif; ?>
<?php } ?>

</div>