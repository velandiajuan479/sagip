<?php require_once 'controllers/votcpro.php';
$vis = isset($_REQUEST['vis']) ? $_REQUEST['vis'] : NULL;
$reado = ($vis == "OK");

$vid    = isset($vid) ? $vid : NULL;
$vAct   = ($vid && isset($vid[0]['rutvid']) && $vid[0]['rutvid']) ? $vid[0]['rutvid'] : '';
$vPath  = $vAct ? ('videos/' . $vAct) : '';
$vMime  = $vAct ? vidmime($vAct) : 'video/mp4';
?>

<div class="conte">
	<?php echo titulo2("<i class='" . $icono . "'></i> Propuesta", 2); ?>
</div>

<div class="mx-auto w-100 max-w-screen-xl">
	<?php if ($dus) { ?>
	<!-- Información del candidato -->
	<header class="bg-white border rounded-4 shadow-sm p-4 d-flex flex-wrap align-items-center gap-4 mb-4 mt-4">
		<?php if (!empty($dus[0]['fotcan']) && file_exists($dus[0]['fotcan'])) { ?>
		<img class="w-44 h-44 rounded-circle object-fit-cover bg-light border-4 border-white shadow-sm flex-shrink-0" src="<?= htmlspecialchars($dus[0]['fotcan']); ?>" alt="Foto del candidato">
		<?php } else { ?>
		<img class="w-44 h-44 rounded-circle object-fit-cover bg-light border-4 border-white shadow-sm flex-shrink-0" src="img/user.jpg" alt="Foto del candidato">
		<?php } ?>
		<div class="flex-grow-1">
			<span class="fs-1 fw-bolder text-success lh-1 d-block"><?= txlbl($dus[0]['noca']); ?></span>
			<span class="fs-4 fw-semibold text-dark d-block mb-2"><?= txlbl($dus[0]['nomusu']); ?></span>
			<ul class="list-unstyled d-flex flex-wrap gap-x-4 gap-y-1 mb-0 text-muted">
				<li><i class="fa-solid fa-building-columns text-success me-1"></i><?= txlbl($dus[0]['nomcen']); ?></li>
				<?php if ($esEst) { ?>
				<li><i class="fa-solid fa-hashtag text-success me-1"></i>Ficha <?= (int)$dus[0]['idfic']; ?> - <?= txlbl($dus[0]['nomfic']); ?></li>
				<li><i class="fa-solid fa-clock text-success me-1"></i>Jornada: <?= txlbl($dus[0]['nomval']); ?></li>
				<?php } ?>
			</ul>
		</div>
	</header>

	<form name="frm1" action="home.php?pg=<?= (int)$pg; ?>" method="POST" class="mb-1">
		<?php if ($dvpr) { ?>
		<!-- Sección Propuesta (slogan y estrategias) -->
		<section class="bg-white border rounded-4 shadow-sm mb-4 overflow-hidden">
			<header class="d-flex align-items-center gap-3 px-4 py-3 bg-success-subtle border-bottom">
				<span class="w-8 h-8 rounded-circle bg-success text-white d-inline-flex align-items-center justify-content-center flex-shrink-0"><i class="fa-solid fa-file-lines"></i></span>
				<h2 class="fs-5 fw-bold mb-0 pb-0">Propuesta</h2>
			</header>
			<div class="p-4">
				<?php
				$n = 0;
				foreach ($dvpr as $dv) {
					if (!$dv['parval']) {
						$valor = ($datOne && isset($datOne[$n]['texpro'])) ? $datOne[$n]['texpro'] : '';
						$npro  = ($datOne && isset($datOne[$n]['npro'])) ? (int)$datOne[$n]['npro'] : '';
						?>
						<div class="form-group mb-4">
							<label class="form-label fw-bold fs-6" for="texpro_<?= $n; ?>"><?= txlbl($dv['nomval']); ?></label>
							<textarea name="texpro[]" id="texpro_<?= $n; ?>" class="form-control" rows="3" required <?= $reado ? 'readonly' : ''; ?>><?= htmlspecialchars($valor); ?></textarea>
							<input type="hidden" name="idval[]" value="<?= (int)$dv['idval']; ?>">
							<input type="hidden" name="npro[]" value="<?= $npro; ?>">
						</div>
						<?php
						$n++;
					} else {
						?>
						<h6 class="fw-bold text-success border-bottom border-success-subtle pb-2 mb-3"><i class="fa-solid fa-square fa-xs text-success me-2"></i><?= txlbl($dv['nomval']); ?></h6>
						<?php
						$nr = explode(";", $dv['parval']);
						for ($o = 0; $o < count($nr); $o++) {
							$valor = ($datOne && isset($datOne[$n]['texpro'])) ? $datOne[$n]['texpro'] : '';
							$npro  = ($datOne && isset($datOne[$n]['npro'])) ? (int)$datOne[$n]['npro'] : '';
							?>
							<div class="form-group mb-4">
								<label class="form-label fw-bold fs-6" for="texpro_<?= $n; ?>"><?= txlbl($nr[$o]); ?></label>
								<textarea name="texpro[]" id="texpro_<?= $n; ?>" class="form-control" rows="8" required <?= $reado ? 'readonly' : ''; ?>><?= htmlspecialchars($valor); ?></textarea>
								<input type="hidden" name="idval[]" value="<?= (int)$dv['idval']; ?>">
								<input type="hidden" name="npro[]" value="<?= $npro; ?>">
							</div>
							<?php
							$n++;
						}
					}
				}
				?>
			</div>
		</section>

		<!-- Sección Video: debajo de Estrategias -->
		<section class="bg-white border rounded-4 shadow-sm mb-4 overflow-hidden">
			<header class="d-flex align-items-center gap-3 px-4 py-3 bg-success-subtle border-bottom">
				<span class="w-8 h-8 rounded-circle bg-success text-white d-inline-flex align-items-center justify-content-center flex-shrink-0"><i class="fa-solid fa-video"></i></span>
				<h2 class="fs-5 fw-bold mb-0 pb-0">Video</h2>
			</header>
			<div class="p-4">
				<p class="small text-muted d-flex align-items-start gap-2 p-3 bg-success-subtle border border-success-subtle rounded-4 mb-4">
					<i class="fa-solid fa-circle-info text-success mt-1"></i>
					<span>Sube un video donde describas tu propuesta de manera gráfica (máximo 97Mb &middot; mp4, webm, mov, avi, m4v, ogv).</span>
				</p>
				<div class="d-flex align-items-center flex-wrap gap-3">
					<?php if (!$reado) { ?>
					<input type="file" name="vidpro" id="vidpro" accept="video/*" class="d-none" onchange="subirVideo(this);">
					<button type="button" class="btn btn-success rounded-3 fw-bold shadow-sm" onclick="document.getElementById('vidpro').click();">
						<i class="fa-solid fa-upload me-1"></i> Subir video
					</button>
					<?php } ?>
					<?php if ($vAct && file_exists($vPath)) { ?>
					<button type="button" class="btn btn-success rounded-3 fw-bold shadow-sm" onclick="verVideo();">
						<i class="fa-solid fa-circle-play me-1"></i> Ver video
					</button>
					<span class="badge bg-light text-muted text-break rounded-pill border"><i class="fa-solid fa-file-video text-success me-1"></i> <?= htmlspecialchars($vAct); ?></span>
					<?php } ?>
				</div>
			</div>
		</section>
		<?php } ?>

		<?php
		echo dbche("Condiciones", $dcon, $datC, $reado);
		echo dbche("Manifiesto", $dman, $datM, $reado);
		?>

		<?php if ($dvpr || $dcon || $dman) { ?>
		<div class="d-flex align-items-center gap-3 flex-wrap pt-2 pb-5">
			<button type="submit" class="btn btn-success btn-lg rounded-4 fw-bold shadow-sm px-4" <?= $reado ? 'disabled' : ''; ?>><i class="fa-solid fa-floppy-disk me-1"></i> <?= $datOne ? "Actualizar" : "Registrar"; ?></button>
			<input type="hidden" name="opera" value="Insertar">
			<input type="hidden" name="idusu" value="<?= (int)($dus[0]['idusu']); ?>">
		</div>
		<?php } else { ?>
		<div class="bg-white border border-dashed rounded-4 shadow-sm text-center p-5 mb-5">
			<i class="fa-solid fa-circle-info text-success fs-1 d-block mb-3"></i>
			<span class="text-muted">No hay campos configurados para esta propuesta.</span>
		</div>
		<?php } ?>
	</form>

	<!-- Modal Ver video de la propuesta -->
	<div class="modal fade" id="vidModal" tabindex="-1" aria-hidden="true">
		<div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
			<div class="modal-content shadow-lg border-0 rounded-4 overflow-hidden">
				<header class="modal-header bg-sena-900 text-white border-0 px-4 py-3">
					<h5 class="modal-title fs-5 d-flex align-items-center gap-2">
						<i class="fa-solid fa-circle-play text-white"></i>
						<span>Video de la propuesta</span>
					</h5>
					<button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
				</header>
				<div class="modal-body bg-black p-0 text-center">
					<video id="vidPlayer" controls preload="metadata" class="w-100">
						<source id="vidSource" src="" type="">
						Su navegador no soporta la visualización de video.
					</video>
				</div>
				<footer class="modal-footer bg-light border-0 px-4 py-3">
					<button type="button" class="btn btn-success" data-bs-dismiss="modal"><i class="fa-solid fa-xmark me-1"></i> Cerrar</button>
				</footer>
			</div>
		</div>
	</div>
	<?php } else { ?>
	<div class="bg-white border border-dashed rounded-5 shadow-sm text-center p-5 mb-5">
		<i class="fa-solid fa-user-slash text-success fs-1 d-block mb-3"></i>
		<span class="text-muted">No se encontró el candidato solicitado.</span>
	</div>
	<?php } ?>
</div>

<?php if ($okVid) { ?>
<div class="d-none" data-vid-ok="1"></div>
<?php } ?>
<?php if ($vidErr) { ?>
<div class="d-none" data-vid-err="<?= htmlspecialchars($vidErr); ?>"></div>
<?php } ?>

<?php if ($ok) { ?>
<script>
	document.addEventListener('DOMContentLoaded', function () {
		Swal.fire({
			icon: 'success',
			title: '¡Propuesta guardada!',
			text: 'Los cambios se registraron correctamente.',
			confirmButtonColor: '#117f09'
		});
	});
</script>
<?php } ?>

<?php if ($okVid) { ?>
<script>
	document.addEventListener('DOMContentLoaded', function () {
		Swal.fire({
			icon: 'success',
			title: '¡Video subido!',
			text: 'El video de tu propuesta se guardó correctamente.',
			confirmButtonColor: '#117f09'
		});
	});
</script>
<?php } ?>

<?php if ($vidErr) { ?>
<script>
	document.addEventListener('DOMContentLoaded', function () {
		Swal.fire({
			icon: 'warning',
			title: 'Video no guardado',
			text: <?= json_encode($vidErr); ?>,
			confirmButtonColor: '#117f09'
		});
	});
</script>
<?php } ?>

<?php if ($dus) { ?>
<script>
var vidModal = null;

function subirVideo(input) {
	if (!input.files || !input.files.length) return;
	var fd = new FormData();
	fd.append('idusu', <?= (int)($dus[0]['idusu']); ?>);
	fd.append('vidpro', input.files[0]);
	Swal.fire({
		title: 'Subiendo video...',
		text: 'Por favor espera mientras se carga tu video.',
		allowOutsideClick: false,
		didOpen: function () { Swal.showLoading(); }
	});
	$.ajax({
		url: 'home.php?pg=<?= (int)$pg; ?>',
		type: 'POST',
		data: fd,
		processData: false,
		contentType: false,
		success: function (html) {
			var doc = new DOMParser().parseFromString(html, 'text/html');
			var errBox = doc.querySelector('[data-vid-err]');
			if (errBox) {
				Swal.fire({
					icon: 'warning',
					title: 'Video no guardado',
					text: errBox.getAttribute('data-vid-err'),
					confirmButtonColor: '#117f09'
				});
				return;
			}
			if (!doc.querySelector('[data-vid-ok]')) {
				Swal.fire({
					icon: 'error',
					title: 'Error',
					text: 'No se pudo subir el video. Inténtalo nuevamente.',
					confirmButtonColor: '#117f09'
				});
				return;
			}
			Swal.fire({
				icon: 'success',
				title: '¡Video subido!',
				text: 'El video de tu propuesta se guardó correctamente.',
				confirmButtonColor: '#117f09'
			}).then(function () { location.reload(); });
		},
		error: function () {
			Swal.fire({
				icon: 'error',
				title: 'Error',
				text: 'No se pudo subir el video. Inténtalo nuevamente.',
				confirmButtonColor: '#117f09'
			});
		}
	});
}

function verVideo() {
	var s = document.getElementById('vidSource');
	s.src = <?= json_encode($vPath); ?>;
	s.type = <?= json_encode($vMime); ?>;
	var p = document.getElementById('vidPlayer');
	p.load();
	if (!vidModal) vidModal = new bootstrap.Modal(document.getElementById('vidModal'));
	vidModal.show();
	p.play();
}

document.addEventListener('DOMContentLoaded', function () {
	var m = document.getElementById('vidModal');
	if (m) {
		m.addEventListener('hidden.bs.modal', function () {
			var p = document.getElementById('vidPlayer');
			p.pause();
			p.removeAttribute('src');
			p.load();
		});
	}
});
</script>
<?php } ?>