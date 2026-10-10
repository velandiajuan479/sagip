<?php require_once "controllers/cmod.php"; ?>

<section class="space-y-5" style="margin-top: 20px;" data-purpose="system-modules">
	<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-200 pb-3">
	    <div>
	    	<h2 class="text-lg font-bold text-slate-900 tracking-tight flex items-center gap-2">
	        	<i class="fa-solid fa-grip"></i>
	            Módulos del Sistema
	    	</h2>
			<p class="text-xs text-slate-500">A continuación puede ver una breve descripción de los módulos que encontrará en esta aplicación.</p>
		</div>
		<div class="flex items-center gap-2">
			<span class="text-xs font-semibold text-slate-500 bg-slate-100 px-3 py-1 rounded-md">11 Módulos Activos</span>
		</div>
	</div>

	<div class="slider-container">
	    <div class="slider-track">
			<?php if($datMos){ foreach ($datMos as $dt){ ?>
				<div class="module-card">
					<article class="module-card bg-white rounded-2xl border border-slate-200 overflow-hidden flex flex-col justify-between shadow-2xs hover:border-sena-500/40">
						<div>
							<div class="px-5 py-4 bg-gradient-to-r from-sena-600 to-sena-700 text-white flex items-center justify-between">
								<div class="flex items-center gap-3">
									<div class="p-2 bg-white/15 rounded-lg">
										<i class="<?=$dt['imgmod'];?>"></i>
									</div>
									<div>
										<h3 class="text-sm font-bold tracking-wide"><?= $dt['nommod']; ?></h3>
										<span class="text-[11px] text-emerald-100 block"><?=substr($dt['desmod'],0,50); ?>...</span>
									</div>
								</div>
							</div>
							<div class="p-5">
								<p class="text-xs text-slate-500 leading-relaxed mb-4">
									<?php if($dt['desmod']){?>
										<p><?=substr($dt['desmod'],0,238); ?></p>
									<?php } else {
										echo "<p class='default-des'>Sin descripción añadida</p>";
									}?>
								</p>
							</div>
						</div>
					</article>
				</div>
			<?php }} ?>
		</div>
	</div>
</section>