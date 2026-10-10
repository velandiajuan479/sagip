<section class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-sena-900 via-sena-800 to-slate-900 text-white p-6 sm:p-8 shadow-xl" data-purpose="hero-banner">
	<form class="m-tb-40 form-carnet" action="index.php?pg=101" method="POST" enctype="multipart/form-data">
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
				<p class="text-slate-300 text-sm leading-relaxed">
	            	Plataforma integral de gestión de aprendices, control de acceso perimetral y seguimiento académico del Centro de Desarrollo Agroempresarial.
	        	</p>
			</div>
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