<?php require_once 'controllers/cpmod.php';
include('controllers/ccanusu.php');
include('controllers/ctots.php');
$votacion = getVotTot(); ?>


<section class="space-y-5" style="margin-top: 20px;" data-purpose="system-modules">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-200 pb-3">
        <div>
            <h2 class="text-lg font-bold text-slate-900 tracking-tight flex items-center gap-2">
                <i class="fa-solid fa-grip"></i>
                Módulos del Sistema
            </h2>
            <p class="text-xs text-slate-500">Seleccione un módulo operativo para gestionar registros e información</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-xs font-semibold text-slate-500 bg-slate-100 px-3 py-1 rounded-md"><?=$dtCtn[0]["ctn"];?> Módulos Activos</span>
        </div>
    </div>
    <!-- Modules Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php 
        $delay = 0.2;
        $step = 0.2;
        if($datAll){ foreach ($datAll as $dt){
            if ($delay < 1) {
                // Ej: 0.2 -> '02'
                $delayNum = str_pad(intval($delay * 10), 2, '0', STR_PAD_LEFT);
                $delayClass = "animate__delay-{$delayNum}s";
            } else {
                // Ej: 1.2 -> '12', 1.4 -> '14', etc.
                $delayNum = str_replace('.', '', number_format($delay, 1));
                $delayClass = "animate__delay-{$delayNum}s";
            }
            $delay += $step;
            $key = array_search($dt['idmod'], $datMod);
            if ($key) {
                $mmod->setIdmod($datMd[$key - 1]["idmod"]);
                $datMus = $mmod->getAllModUsu();
                $mmod->setIdper($datMus[0]["idper"]);
                $datPer = $mmod->getAllPer();
                $pagsXMod = getPagsXMod($dt['idmod'], $_SESSION['idusu'], $_SESSION['idpergen']);
        ?>
            <form id="form-<?= $dt['idmod']; ?>" action="mod.php" method="POST"class="formu animate__animated animate__fadeIn animate__delay-02s">
                <div class="row btnmod bx-mod-main" onclick="this.closest('form').submit();" style="cursor:pointer;">
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
                        <div class="px-5 py-3 bg-slate-50/80 border-t border-slate-100 flex items-center justify-between">
                            <div class="flex flex-wrap gap-2">
                                <?php if ($dt['idmod'] == 1 && $datMus[0]['idper'] == 2) { ?>
                                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold">
                                        <i class="fa-solid fa-user-check"></i>
                                        <span class=""><?=getCantUsuPer(); ?></span>
                                    </div>
                                <?php } ?>

                                <?php if ($dt['idmod'] == 1 && $datMus[0]['idper'] == 4) { ?>
                                    <?php if (!empty($votacion)) { $datos = $votacion[0]; ?>
                                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold">
                                        <i class="fa-solid fa-vote-yea"></i>
                                        <span class="">
                                            <?php if ($datos['nombre_mas_votado']) { ?>
                                                <?= $datos['nombre_mas_votado'] ?> (<?= $datos['total_votos'] ?>)
                                            <?php } else { ?>
                                                <?= $datos['total_candidatos'] ?>
                                            <?php } ?>
                                        </span>
                                    </div>
                                <?php }} ?>

                                <?php if ($dt['idmod'] == 2 && $datMus[0]['idper'] == 6) { ?>
                                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold">
                                        <i class="fa-solid fa-user"></i>
                                        <span class=""><?= $S[0]['ctn']; ?></span>
                                    </div>
                                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-blue-50 border border-blue-200 text-blue-800 text-xs font-semibold">
                                        <i class="fa-solid fa-user"></i>
                                        <span class=""><?= $F[0]['ctn']; ?></span>
                                    </div>
                                <?php } ?>

                                <?php if ($dt['idmod'] == 2 && $datMus[0]['idper'] == 7) { ?>
                                    <?php
                                        $total_entradas = 0; $total_salidas = 0;
                                        if (!empty($movimientos)) { foreach ($movimientos as $mov) {
                                            if ($mov['tipmin'] == 'I') $total_entradas++;
                                            elseif ($mov['tipmin'] == 'F') $total_salidas++;
                                        }}
                                    ?>
                                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold">
                                        <i class="fa-solid fa-user"></i>
                                        <span class=""><?= $total_entradas; ?></span>
                                    </div>
                                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-blue-50 border border-blue-200 text-blue-800 text-xs font-semibold">
                                        <i class="fa-solid fa-user-times"></i>
                                        <span class=""><?= $total_salidas; ?></span>
                                    </div>
                                <?php } ?>

                                <?php if ($dt['idmod'] == 5 && $datMus[0]['idper'] == 24) { ?>
                                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold">
                                        <i class="fa-solid fa-landmark"></i>
                                        <span class=""><?= totFics(); ?></span>
                                    </div>
                                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-blue-50 border border-blue-200 text-blue-800 text-xs font-semibold">
                                        <i class="fa-solid fa-calendar-xmark"></i>
                                        <span class=""><?= getCantIna(); ?></span>
                                    </div>
                                <?php } ?>

                                <?php if ($dt['idmod'] == 6 && $datMus[0]['idper'] == 29) { ?>
                                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold">
                                        <i class="fa-solid fa-box"></i>
                                        <span class=""><?= totEle(); ?></span>
                                    </div>
                                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-blue-50 border border-blue-200 text-blue-800 text-xs font-semibold">
                                        <i class="fa-solid fa-list-check"></i>
                                        <span class=""><?= totPresEle(); ?></span>
                                    </div>
                                <?php } ?>

                                <?php if ($dt['idmod'] == 7 && $datMus[0]['idper'] == 28) { ?>
                                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold">
                                        <i class="fa-solid fa-person"></i>
                                        <span class=""><?= totInst(); ?></span>
                                    </div>
                                <?php } ?>

                                <?php if ($dt['idmod'] == 3 && $datMus[0]['idper'] == 17) { ?>
                                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold">
                                        <i class="fa-solid fa-user"></i>
                                        <span class=""><?= cantUsuFichaById($_SESSION['idusu']); ?></span>
                                    </div>
                                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-blue-50 border border-blue-200 text-blue-800 text-xs font-semibold">
                                        <i class="fa-solid fa-user"></i>
                                        <span class=""><?= usuFalt($_SESSION['idusu']); ?></span>
                                    </div>
                                <?php } ?>

                                <?php if ($dt['idmod'] == 12 && $datMus[0]['idper'] == 21) { ?>
                                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold">
                                        <i class="fa-solid fa-landmark"></i>
                                        <span class=""><?= totFics(); ?></span>
                                    </div>
                                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-blue-50 border border-blue-200 text-blue-800 text-xs font-semibold">
                                        <i class="fa-solid fa-person"></i>
                                        <span class=""><?= totInst(); ?></span>
                                    </div>
                                <?php } ?>

                                <?php if ($dt['idmod'] == 13 && $datMus[0]['idper'] == 31) { ?>
                                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold">
                                        <i class="fa-solid fa-book"></i>
                                        <span class=""><?= totFics(); ?></span>
                                    </div>
                                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-blue-50 border border-blue-200 text-blue-800 text-xs font-semibold">
                                        <i class="fa-solid fa-person"></i>
                                        <span class=""><?= getCantBit(); ?></span>
                                    </div>
                                <?php } ?>

                            </div>
                            <a class="text-xs font-bold text-sena-600 hover:text-sena-800 flex items-center gap-1 transition" href="#">Ingresar <i class="fa-solid fa-chevron-right"></i></a>
                        </div>
                    </article>

                    <input type="hidden" name="idmod" value="<?= $dt['idmod']; ?>">
                    <input type="hidden" name="pg" id="pg-<?= $dt['idmod']; ?>" value="<?= $datPer[0]['idpag']; ?>">
                    <input type="hidden" name="idper" value="<?= $datPer[0]['idper']; ?>">
                    <input type="hidden" name="nomper" value="<?= $datPer[0]['nomper']; ?>">
                    <input type="hidden" name="ope" value="dircc">
                </div>
            </form>
            <?php } ?>
        <?php }} ?>
    </div>
</section>