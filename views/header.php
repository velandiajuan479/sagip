<?php
	$pg = isset($_REQUEST["pg"]) ? $_REQUEST["pg"] : NULL;
	if ((!$pg or $pg == 1324) && $sesion) {
		$_SESSION["idper"] = 5;
		$_SESSION["pefnom"] = "Datos Básicos";
	}elseif (!$pg or $pg==183) {
		$_SESSION["pefnom"] = "ADSO";
	}else{
        $_SESSION["pefnom"] = "ADSO";
    }

	$nomcompleto = isset($_SESSION['nomusu']) ? $_SESSION['nomusu']:'Versión 2';
	$partes = explode(" ", trim($nomcompleto));
	$prinom = isset($partes[0]) ? $partes[0] : '';		
	if (count($partes) <= 3 && isset($partes[1])) $priape = $partes[1];
	elseif (isset($partes[2]))$priape = $partes[2];
	else $priape = '';
?>

<div class="w-full px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between h-20 gap-4">
        <div class="flex items-center gap-3.5 flex-shrink-0 -translate-x-[15px]">
            <div class="w-12 h-12 bg-sena-600 rounded-xl flex items-center justify-center shadow-md shadow-sena-600/20 text-white font-bold text-2xl tracking-tighter ring-2 ring-sena-500/30">
                <img src="img/senab.png" width="70%">
            </div>
            <div>
                <div class="flex items-center gap-1.5">
                    <span class="text-2xl font-black tracking-tight text-slate-900 leading-none">SAGI</span>
                    <span class="text-xs font-bold px-1.5 py-0.5 rounded bg-sena-100 text-sena-800 tracking-wide uppercase">CDA</span>
                </div>
                <p class="text-xs text-slate-500 font-medium tracking-tight mt-0.5 txtocu">Centro de Desarrollo Agroempresarial</p>
            </div>
        </div>
        <div class="hidden md:flex flex-1 max-w-lg mx-6" data-purpose="quick-search">
            <div class="relative w-full">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400"></div>
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                </div>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <div class="flex items-center pl-2 sm:pl-3 border-l border-slate-200 gap-3">
                <div class="text-right hidden sm:block">
                    <p class="text-sm font-bold text-slate-800 leading-tight"><?=htmlspecialchars($prinom . " " . $priape);?></p>
                    <div class="flex items-center justify-end gap-1.5 mt-0.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span class="text-xs text-slate-500 font-medium"><?=isset($_SESSION['pefnom']) ? htmlspecialchars($_SESSION['pefnom']):''; ?></span>
                    </div>
                </div>
                <div class="relative">
                <?php if($_SESSION["pefnom"] AND $_SESSION["pefnom"]!="2.0"){ ?>
                	<a href="home.php?pg=1324" title="Datos Personales" class="btn-user-action">
                <?php } ?>
	                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-sena-800 to-sena-600 flex items-center justify-center text-white font-bold text-sm shadow-sm ring-2 ring-white">
	                        <?=htmlspecialchars(strtoupper(substr($prinom,0,1)).strtoupper(substr($priape,0,1)));?>
	                    </div>
	            <?php if($_SESSION["pefnom"] AND $_SESSION["pefnom"]!="2.0"){ ?>
	                </a>
	            <?php } ?>
                    <span class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-emerald-500 border-2 border-white rounded-full"></span>
                </div>

                <?php if($sesion){ ?>
	                <a href="views/vsal.php" title="Cerrar Sesión" class="btn-user-action btn-logout">
		                <button aria-label="Cerrar sesión" class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition duration-150" title="Cerrar Sesión" type="button">
		                    <i class="fa-solid fa-power-off"></i>
		                </button>
		            </a>
		        <?php } ?>
            </div>
        </div>
    </div>
</div>