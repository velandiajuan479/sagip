<?php include('controllers/cmenu.php'); ?>
<div class="menu--btn-spt-men" title="Menú de Navegación">
	<i class="fa-solid fa-bars"></i>
</div>
<nav class="main-menu">
	<div class="branding">
		<?php if (isset($val) && $val): ?>
			<img src="img/<?= htmlspecialchars($val[0]['logcof']); ?>" alt="Logo SENA" class="branding-logo">
			<?php
			$partes = explode(" ", $val[0]['titcof']);
			$pal1 = isset($partes[0]) ? $partes[0] : 'SAGI';
			$pal2 = isset($partes[1]) ? $partes[1] : 'CDA';
			?>
			<div class="branding-title">
				<div class="flex items-center gap-1.5">
                    <span class="text-2xl font-black tracking-tight text-slate-900 leading-none" style="color: #fff;"><?=htmlspecialchars(strtoupper($pal1)); ?></span>
                    <span class="text-xs font-bold px-1.5 py-0.5 rounded bg-sena-100 text-sena-800 tracking-wide uppercase"><?=htmlspecialchars(strtoupper($pal2)); ?></span>
                </div>
                <p class="text-xs text-slate-500 font-medium tracking-tight mt-0.5 txtocu" style="color: #fff;">Soft. Admin. Gestión Integral</p>
			</div>
		<?php else: ?>
			<img src="img/favicon.png" alt="Logo" class="branding-logo">
			<div class="branding-title">
				<span class="palabra1">SAGI</span>
				<span class="palabra2">CDA</span>
			</div>
		<?php endif; ?>
	</div>

	<div class="scrollbar" id="style-1">
		<ul>
			<li>
				<a href="mod.php" title="Módulos del Sistema">
					<i class="fa-solid fa-house"></i>&nbsp;&nbsp;
					<span class="nav-text">Módulos</span>
				</a>
			</li>
			<?php
			$currPg = isset($_REQUEST['pg']) ? $_REQUEST['pg'] : NULL;
			if (isset($dat) && $dat) {
				foreach ($dat as $dt) {
					$isActive = ($currPg == $dt['idpag']) ? 'active' : '';
					?>
					<li class="darkerli <?= $isActive; ?>">
						<a href="home.php?pg=<?= $dt['idpag']; ?>" title="<?= htmlspecialchars($dt['nompag']); ?>">
							<i class="<?= htmlspecialchars($dt['icopag']); ?>"></i>&nbsp;&nbsp;
							<span class="nav-text"><?= htmlspecialchars($dt['nompag']); ?></span>
						</a>
					</li>
					<?php
				}
			}
			?>
			<li class="logout">
				<a href="views/vsal.php" title="Cerrar Sesión">
					<i class="fa-solid fa-power-off"></i>&nbsp;&nbsp;
					<span class="nav-text">Salir</span>
				</a>
			</li>
		</ul>
	</div>
</nav>

<script>
	(function() {
		let btnMenu = document.querySelector('.menu--btn-spt-men');
		if (btnMenu) {
			btnMenu.addEventListener('click', () => {
				let mainMen = document.querySelector('.main-menu');
				if (mainMen) {
					mainMen.classList.toggle('menu-despleg');
					mainMen.classList.toggle('expanded');
					btnMenu.classList.toggle('menu-btn-style');
				}
			});
		}
	})();
</script>