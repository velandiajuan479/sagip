<?php require_once('controllers/ccar.php'); ?>
<link rel="stylesheet" type="text/css" href="css/carne.css">
<script src="js/JsBarcode.all.min.js"></script>

<section class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-sena-900 via-sena-800 to-slate-900 text-white p-6 sm:p-8 shadow-xl" data-purpose="hero-banner">
	<div class="absolute -right-20 -bottom-20 w-80 h-80 bg-sena-500/20 rounded-full blur-3xl pointer-events-none"></div>
	<div class="absolute top-0 right-1/4 w-40 h-40 bg-emerald-400/10 rounded-full blur-2xl pointer-events-none"></div>
	<div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
		<div class="space-y-2 max-w-2xl">
			<div class="flex flex-wrap items-center gap-2">
				<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-500/20 text-emerald-200 border border-emerald-400/20">
              		Sede Principal - Chía
            	</span>
			</div>
			<h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white">Generación de Carnet Digital</h1>
		</div>
	</div>

	<form class="form-carnet" action="index.php?pg=101" method="POST" enctype="multipart/form-data">
		<div class="relative z-10 flex flex-col md:flex-row md:items-center justify-center gap-6">
			<div class="space-y-2 w-full max-w-none flex flex-col items-center">
		<!-- Inicio Carnet -->
				<div class="carnet-contenedor">
					<div class="text-end volver-carnet">
						<a href="index.php" class="btn btn-sm">
							<i class="fa-solid fa-arrow-left"></i> Volver
						</a>
					</div>
					<div class="bx-carnet-dt">
						<section class="carnet-contenido">
							<div class=" borcar">
								<div class="logo">
									<img src="img/sena.png">
								</div>
								<div class="fot">
									<?php if ($datOne && file_exists($datOne[0]['fotcan'])) { ?>
										<img src="<?= $datOne[0]['fotcan']; ?>">
									<?php } else { ?>
										<img src="img/user.jpg">
									<?php } ?>
								</div>
								<div class="tipct">
									<?php if ($datOne)
										echo $datOne[0]['nomper'];
									else
										echo "Visitante" ?>
									</div>
									<hr>
									<div class="txtnom">
									<?= $nom; ?>
									<br>
									<?= $ape; ?>
								</div>
								<div class="txt">
							    	<?= ($datOne && isset($datOne[0]['tipdoc']) ? $datOne[0]['tipdoc'] : 'N/A'); ?>
				    				<?= number_format($ndocusu, 0, ',', '.'); ?>
								</div>
								<div class="txt">
									<?php echo "RH: " . ($datOne && isset($datOne[0]['rh']) ? $datOne[0]['rh'] : "N/A"); ?>
								</div>
								<?php if($datOne && isset($datOne[0]['tipdoc'])){ ?>
									<div class="coba">
										<svg id="c1"></svg>
									</div>
								<?php } ?>
								<hr class="tbar">
								<div class="txt" style="padding-top: 2px;font-size: 12px;">
									<span class="txt1">Regional Cundinamarca</span>
									<br>
									<span class="txt2">Centro de Desarrollo Agroempresarial</span>
								</div>
							</div>

							<div class="borcar ajubc">
								<div class="txtint">
									<?php echo $text; ?>
								</div>
								<?php if ($datOne): ?>
									<?php $perfil = $datOne[0]['idper']; ?>

									<?php if ($perfil == 4 || $perfil == 8): ?>
										<strong>Ficha:</strong> <?= $datOne[0]['idfic'] . " " . $datOne[0]['nomfic'] . " " . $datOne[0]['nomval'] ?><br>
										<strong>Fecha inicial: </strong><?= $datOne[0]['finific'] ?><br>
										<strong>Fecha Finalización: </strong><?= $datOne[0]['ffinfic'] ?><br>

									<?php elseif ($perfil == 12): ?>
										<strong>Fecha inicial: </strong><?= $datOne[0]['fecini'] ?><br>
										<strong>Fecha Finalización: </strong><?= $datOne[0]['fecfin'] ?><br>

									<?php else :?>
										<br>
										<strong style="padding-top: 5px;">Cargo: </strong><?= $datOne[0]['nomper'] ?><br>
									<?php endif; ?>
								<?php endif; ?>


								<script type="text/javascript">
									JsBarcode("#c1", "<?= $ndocusu; ?>", {
									format: "codabar",
									lineColor: "#000",
									width: 2,
									height: 30,
									displayValue: false
									});
								</script>
							</div>
						</section>
					</div>
				</div>
			</div>

		<!-- Fin Carnet -->
			<div class="bg-white/10 backdrop-blur-md p-4 rounded-xl border border-white/15 w-full md:w-auto min-w-[280px]" data-purpose="quick-carnet-card">
				<label class="text-xs font-semibold text-slate-200 uppercase tracking-wider block mb-2" for="carnet-id-input">
	            	Verificación Rápida de Identidad
	          	</label>
				<div class="flex items-center gap-2">
					<div class="relative flex-1">
						<input name="id" class="w-full pl-9 pr-3 py-2 text-xs bg-slate-900/60 border border-white/20 rounded-lg text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-sena-500" id="carnet-id-input" placeholder="No. Documento..." type="text" onchange="this.form.submit();" autofocus="">
						<i class="fa-solid fa-fingerprint iconCarnet"></i>
					</div>
					<button class="px-3.5 py-2 bg-sena-600 hover:bg-sena-500 text-white rounded-lg text-xs font-semibold shadow transition flex items-center gap-1.5" type="submit">
						<span class="">Consultar</span>
						<i class="fa-solid fa-arrow-right"></i>
					</button>
					<input type="hidden" name="ope" value="save">
				</div>
			</div>
		</div>
	</form>
</section>



<script>
	let timer;
	const inputCarnet = document.getElementById('inp-num_doc');
	const form = document.querySelector('.form-carnet');

	if (inputCarnet) {
		inputCarnet.addEventListener('input', () => {
			clearTimeout(timer); // reinicia el temporizador
			timer = setTimeout(() => {
				if (inputCarnet.value.trim() !== "") {
					form.submit();
				}
			}, 2000);
		});
	}
</script>