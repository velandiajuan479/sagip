<?php require_once('controllers/crct.php'); ?>
<section class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 sm:p-6 transition hover:border-sena-500/40" data-purpose="quick-login-section" style="box-shadow: rgba(0, 143, 57, 0.18) 0px 10px 25px -3px, rgba(0, 143, 57, 0.12) 0px 4px 12px -2px;margin-top: 20px;">
	<div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
    	<div class="space-y-1">
    		<div class="flex items-center gap-2">
        		<span class="w-8 h-8 rounded-lg bg-sena-50 text-sena-700 flex items-center justify-center">
        			<i class="fa-solid fa-lock"></i>
        		</span>
        		<h2 class="text-base font-bold text-slate-900 tracking-tight">
        			<?php if($pg==102){ ?>Recuperación de contraseña
        			<?php }elseif($pg==183){ ?>Cambiar contraseña
        			<?php }else{ ?>Acceso Rápido al Sistema SAGI<?php } ?>
        		</h2>
        		<span class="hidden sm:inline-flex items-center gap-1 text-[11px] font-medium text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-full">
        			<i class="fa-solid fa-shield-halved"></i> Conexión institucional segura
        		</span>
      		</div>
      		<p class="text-xs text-slate-500 pl-10">
				<?php if($pg==102){ ?>Ingresa con tu correo electrónico registrado en SAGI para solicitar tu cambio de clave.
        			<?php }elseif($pg==183){ ?>Haz solicitado el cambio de contraseña, <strong><?=$emausu;?></strong> por favor registre su nueva contraseña y confirmela para poder acceder al sitio.
        			<?php }else{ ?>Ingresa con tus credenciales de SAGI para gestionar servicios.<?php } ?>
      		</p>
    	</div>
    	<?php if($pg==183){ ?>
    		<?php
    		if($act==true){ ?>
	    	<form class="flex-1 max-w-2xl" action="index.php?pg=183" method="POST">
	    		<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-center">
	        		<div class="relative lg:col-span-4">
	        			<div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
	        				<i class="fa-solid fa-key"></i>
	          			</div>
	          			<input type="password" id="pas1" maxlength="100" name="pas1" placeholder="Nueva Contraseña" class="w-full pl-9 pr-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl text-slate-800 placeholder-slate-400 focus:bg-white focus:border-sena-600 focus:ring-2 focus:ring-sena-600/15 outline-none transition">
	        		</div>
			        <div class="relative lg:col-span-4">
						<div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
							<i class="fa-solid fa-key"></i>
						</div>
						<input type="password" id="pas2" maxlength="100" name="pas2" placeholder="Repetir Contraseña" class="w-full pl-9 pr-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl text-slate-800 placeholder-slate-400 focus:bg-white focus:border-sena-600 focus:ring-2 focus:ring-sena-600/15 outline-none transition">
			        </div>
			        <div class="lg:col-span-4 flex sm:flex-col lg:flex-row gap-2">
			        	<input type="hidden" name="idusu" value="<?=$datAll['idusu'];?>">
        	    		<input type="hidden" name="ope" value="AcTuAl">
						<button type="submit" class="w-full inline-flex items-center justify-center gap-1.5 px-3.5 py-2 bg-sena-600 hover:bg-sena-700 text-white rounded-xl text-xs font-semibold shadow-sm transition" onclick="return validadat();">
			            	<span class="">Cambiar</span>
			            	<i class="fa-solid fa-key"></i>
						</button>
			        </div>
	    		</div>
	    		<div class="flex items-center justify-between mt-2 pt-1 text-[11px] text-slate-500">
	    			<div id="mensaje-error" class="error-login"></div>
	        		<span class="sm:hidden flex items-center gap-1 text-emerald-700 font-medium">
	        			<i class="fa-solid fa-shield"></i> Segura SENA
	        		</span>
	        		<a href="index.php" class="text-xs font-medium text-sena-700 hover:text-sena-900 hover:underline inline-flex items-center gap-1 transition ml-auto">
	        			<i class="fa-regular fa-circle-question"></i> Ingresa contraseña
	        		</a>
				</div>
	    	</form>
	    	<?php } ?>
	    <?php }elseif($pg==102){ ?>
	    	<form class="flex-1 max-w-2xl" action="controllers/colv.php" method="POST">
	    		<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-center">
	        		<div class="relative lg:col-span-4">
	        		</div>
	        		<div class="relative lg:col-span-5">
						<div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
	        				<i class="fa-solid fa-envelope me-1"></i>
	          			</div>
	          			<input type="email" name="emausu" placeholder="ejemplo@sena.edu.co" class="w-full pl-9 pr-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl text-slate-800 placeholder-slate-400 focus:bg-white focus:border-sena-600 focus:ring-2 focus:ring-sena-600/15 outline-none transition">

			        </div>
			        <div class="lg:col-span-3 flex sm:flex-col lg:flex-row gap-2">
						<button type="submit" class="w-full inline-flex items-center justify-center gap-1.5 px-3.5 py-2 bg-sena-600 hover:bg-sena-700 text-white rounded-xl text-xs font-semibold shadow-sm transition">
			            	<span class="">Enviar Correo</span>
			            	<i class="fa-solid fa-share"></i>
						</button>
			        </div>
	    		</div>
	    		<div class="flex items-center justify-between mt-2 pt-1 text-[11px] text-slate-500">
	    			<div id="mensaje-error" class="error-login"></div>
	        		<span class="sm:hidden flex items-center gap-1 text-emerald-700 font-medium">
	        			<i class="fa-solid fa-shield"></i> Segura SENA
	        		</span>
	        		<a href="index.php" class="text-xs font-medium text-sena-700 hover:text-sena-900 hover:underline inline-flex items-center gap-1 transition ml-auto">
	        			<i class="fa-regular fa-circle-question"></i> Ingresa contraseña
	        		</a>
				</div>
			</form>
		<?php }else{ ?>
	    	<form class="flex-1 max-w-2xl" action="models/control.php" method="POST">
	    		<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-center">
	        		<div class="relative lg:col-span-5">
	        			<div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
	        				<i class="fa-solid fa-user"></i>
	          			</div>
	          			<input type="number" name="usu" placeholder="No. documento" class="w-full pl-9 pr-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl text-slate-800 placeholder-slate-400 focus:bg-white focus:border-sena-600 focus:ring-2 focus:ring-sena-600/15 outline-none transition">
	        		</div>
			        <div class="relative lg:col-span-4">
						<div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
							<i class="fa-solid fa-key"></i>
						</div>
						<input type="password" name="con" placeholder="Contraseña..." class="w-full pl-9 pr-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl text-slate-800 placeholder-slate-400 focus:bg-white focus:border-sena-600 focus:ring-2 focus:ring-sena-600/15 outline-none transition">
			        </div>
			        <div class="lg:col-span-3 flex sm:flex-col lg:flex-row gap-2">
						<button type="submit" class="w-full inline-flex items-center justify-center gap-1.5 px-3.5 py-2 bg-sena-600 hover:bg-sena-700 text-white rounded-xl text-xs font-semibold shadow-sm transition">
			            	<span class="">Ingresar</span>
			            	<i class="fa-solid fa-right-to-bracket"></i>
						</button>
			        </div>
	    		</div>
	    		<div class="flex items-center justify-between mt-2 pt-1 text-[11px] text-slate-500">
	    			<div id="mensaje-error" class="error-login"></div>
	        		<span class="sm:hidden flex items-center gap-1 text-emerald-700 font-medium">
	        			<i class="fa-solid fa-shield"></i> Segura SENA
	        		</span>
	        		<a href="index.php?pg=102" class="text-xs font-medium text-sena-700 hover:text-sena-900 hover:underline inline-flex items-center gap-1 transition ml-auto">
	        			<i class="fa-regular fa-circle-question"></i> ¿Olvidó su contraseña?
	        		</a>
				</div>
				<?php
					$error = isset($_GET['error']) ? $_GET['error'] : NULL;
					if ($error == "ok") {
						echo "<script>
							document.addEventListener('DOMContentLoaded', function () {
								if (typeof ingreso === 'function') {
									ingreso(1);
								}
								const errorDiv = document.getElementById('mensaje-error');
								if (errorDiv) {
									errorDiv.textContent = 'Datos inválidos. Por favor verifique sus credenciales.';
									errorDiv.style.display = 'block';
								}
							});
						</script>";
					}
				?>
	    	</form>
		<?php } ?>
	</div>
</section>



