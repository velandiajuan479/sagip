<?php require_once 'controllers/votcctele.php'; ?>

<?php
if (!function_exists('inicialesNombres')) {
    function inicialesNombres($nombre) {
        $partes = preg_split('/\s+/', trim((string)$nombre));
        $ini = '';
        $cont = 0;
        foreach ($partes as $p) {
            if ($p === '' || $p === '') continue;
            $ini .= strtoupper(substr($p, 0, 1));
            $cont++;
            if ($cont >= 2) break;
        }
        return $ini !== '' ? $ini : '?';
    }
}
?>

<?php echo titulo2("<i class='" . $icono . "'></i> Cartón Electoral", 2); ?>

<!-- Barra de acciones: imprimir -->
<div class="max-w-[1200px] mx-auto mb-[22px] flex items-center justify-end flex-wrap gap-3 px-5 py-[14px] rounded-2xl bg-[linear-gradient(135deg,#0a3d1a_0%,#117f09_55%,#00af00_100%)] shadow-[0_10px_28px_rgba(10,61,26,0.18)] border-l-[6px] border-[#ffc800] relative overflow-hidden print:hidden">
    <span class="inline-flex items-center gap-2 text-white text-[13.5px] font-semibold bg-white/15 border border-white/35 rounded-full px-[15px] py-[7px]">
        <i class="fa-solid <?= $periodoVotacion ? 'fa-door-open' : 'fa-lock'; ?>"></i>
        Periodo de votación: <strong><?= $periodoVotacion ? 'Abierto' : 'Cerrado'; ?></strong>
    </span>
    <button type="button" class="inline-flex items-center gap-[9px] border-0 rounded-full px-6 py-[11px] bg-[#ffc800] text-[#17321a] font-extrabold text-sm uppercase tracking-wider shadow-[0_4px_14px_rgba(0,0,0,0.25)] cursor-pointer transition duration-200 hover:brightness-105 hover:-translate-y-0.5" onclick="window.print();">
        <i class="fa-solid fa-print"></i> Imprimir Cartón Electoral
    </button>
</div>

<!-- Contenido imprimible: el cartón electoral -->
<div class="max-w-[1200px] mx-auto mb-[30px] print:max-w-full print:my-0" id="carton-electoral">

    <!-- ========== CARTÓN REPRESENTANTES ========== -->
    <div class="isolate relative overflow-hidden mx-auto mb-[30px] max-w-[1040px] rounded-[18px] py-[22px] px-7 text-white bg-[linear-gradient(135deg,#0a3d1a_0%,#117f09_55%,#00af00_100%)] shadow-[0_10px_28px_rgba(10,61,26,0.35)] flex items-center justify-between flex-wrap gap-[14px] border-l-[6px] border-[#ffc800] print:max-w-full print:mb-3 print:[print-color-adjust:exact]">
        <div>
            <h4 class="m-0 mb-[5px] text-[1.45rem] font-extrabold text-white tracking-[0.3px] print:text-[1.2rem]">
                <i class="fa-solid fa-user-tie"></i> Candidatos a Representante de Aprendices
            </h4>
            <p class="m-0 text-base text-[#e8f7e6]">Cartón informativo de la votación para elegir representante.</p>
        </div>
        <div class="inline-flex items-center gap-2 bg-white/15 border border-white/40 rounded-full px-[18px] py-[9px] text-base font-bold">
            <i class="fa-solid fa-users"></i> <?= $datRepTar ? count($datRepTar) : 0; ?> Opciones
        </div>
        <i class="fa-solid fa-users absolute -right-5 -bottom-9 text-[150px] text-white/5 -z-10 pointer-events-none"></i>
    </div>

    <div class="flex flex-wrap justify-center items-stretch gap-[26px] pt-[6px] pb-[26px] max-w-[1200px] mx-auto print:gap-3 print:py-0">
        <?php
        if (!empty($datRepTar)) {
            foreach ($datRepTar as $d) {
                $esBlanco = !empty($d['esblanco']);
                $nombre   = isset($d['nomusu']) ? $d['nomusu'] : 'SIN NOMBRE';
                $numCard  = $esBlanco ? 'BLANCO' : (isset($d['noca']) ? htmlspecialchars($d['noca']) : '???');
                $foto     = isset($d['fotcan']) && !empty($d['fotcan']) ? htmlspecialchars($d['fotcan']) : '';
                $ficha    = isset($d['idfic']) && !empty($d['idfic']) ? htmlspecialchars($d['idfic']) : '';
                $programa = isset($d['nomfic']) && !empty($d['nomfic']) ? htmlspecialchars($d['nomfic']) : '';
                $lema     = isset($d['lema']) && !empty($d['lema']) ? $d['lema'] : ($esBlanco ? 'No hay inclinación por ningún candidato.' : 'Compromiso y liderazgo al servicio de los aprendices.');

                $cardCls  = 'w-[262px] max-md:w-full max-md:max-w-[300px] bg-white rounded-[18px] overflow-hidden text-center shadow-[0_4px_12px_rgba(0,0,0,0.06)] transition-transform duration-200 relative flex flex-col print:w-[245px] print:shadow-none print:hover:translate-y-0 print:hover:shadow-none';
                $cardCls .= $esBlanco
                    ? ' border-2 border-dashed border-[#b9c2b9] bg-[#fafbfa] hover:border-[#117f09] hover:shadow-[0_18px_40px_rgba(17,127,9,0.14)]'
                    : ' border border-[#e3e8e3] hover:-translate-y-1 hover:border-[#cfe8cd] hover:shadow-[0_18px_40px_rgba(17,127,9,0.22)]';
                $fotoCls  = 'relative shrink-0 h-[210px] max-md:h-[180px] print:h-[170px] bg-[#f2f5f2]';
                $fotoPlace = $esBlanco
                    ? 'flex items-center justify-center bg-[linear-gradient(160deg,#ffffff,#edf1ed)] text-[#a9b4a9]'
                    : 'flex items-center justify-center bg-[linear-gradient(160deg,#e8f4e8,#d6e8d6)] text-[#83a983]';
                $nomCls   = $esBlanco ? 'text-[#5b645b]' : 'text-[#17321a]';
                $lemaCls  = $esBlanco ? 'border-l-[#a9b4a9]' : 'border-l-[#117f09]';
                $chipCls  = $esBlanco ? 'text-[#4c5a4c] border-solid' : 'text-[#6b756b]';
                $avatarCls = 'w-[104px] h-[104px] rounded-full bg-[linear-gradient(135deg,#117f09,#00af00)] text-white flex items-center justify-center text-[40px] font-extrabold tracking-wide shadow-[0_10px_22px_rgba(17,127,9,0.35)] border-4 border-white [text-shadow:0_2px_5px_rgba(0,0,0,0.25)] print:[print-color-adjust:exact]';
                $numCls   = 'absolute top-3 left-3 z-[2] inline-flex items-center bg-[rgba(15,70,8,0.88)] text-white font-extrabold text-[13px] px-[13px] py-[6px] rounded-full tracking-[0.6px] shadow-[0_2px_8px_rgba(0,0,0,0.28)] print:[print-color-adjust:exact]';
                ?>
                <div class="<?= $cardCls; ?>">

                    <?php if ($esBlanco) { ?>
                        <div class="<?= $fotoCls . ' ' . $fotoPlace; ?>">
                            <div class="w-[84px] h-[84px] rounded-full bg-white text-[#a9b4a9] flex items-center justify-center text-[32px] shadow-[0_8px_18px_rgba(0,0,0,0.08)] border-2 border-dashed border-[#cdd5cd]">
                                <i class="fa-solid fa-ban"></i>
                            </div>
                        </div>
                    <?php } elseif ($foto && $foto != 'image/usuario.png') { ?>
                        <div class="<?= $fotoCls; ?>">
                            <span class="<?= $numCls; ?>"><i class="fa-solid fa-id-card mr-[6px]"></i><?= $numCard; ?></span>
                            <img src="<?= $foto; ?>" alt="<?= htmlspecialchars($nombre); ?>" class="block w-full h-full object-cover"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div class="hidden <?= $avatarCls; ?>"><?= inicialesNombres($nombre); ?></div>
                        </div>
                    <?php } else { ?>
                        <div class="<?= $fotoCls . ' ' . $fotoPlace; ?>">
                            <span class="<?= $numCls; ?>"><i class="fa-solid fa-id-card mr-[6px]"></i><?= $numCard; ?></span>
                            <div class="<?= $avatarCls; ?>"><?= inicialesNombres($nombre); ?></div>
                        </div>
                    <?php } ?>

                    <div class="px-4 pt-4 pb-[18px] flex flex-col grow">
                        <div class="<?= $nomCls; ?> text-[15px] font-extrabold leading-[1.35] min-h-[42px] tracking-[0.2px] mb-[5px]"><?= strtoupper(htmlspecialchars($nombre)); ?></div>
                        <div class="text-[#6b756b] text-xs mb-[10px]">
                            <?php if ($esBlanco) { ?>
                                <i class="fa-solid fa-file-circle-minus text-[#117f09] mr-[5px]"></i> Opción democrática oficial
                            <?php } else { ?>
                                <?php if ($ficha) { ?><i class="fa-solid fa-hashtag text-[#117f09] mr-[5px]"></i>Ficha <?= $ficha; ?><?php } ?>
                                <?php if ($programa) { ?>&nbsp;·&nbsp;<i class="fa-solid fa-graduation-cap text-[#117f09] mr-[5px]"></i><?= $programa; ?><?php } ?>
                            <?php } ?>
                        </div>
                        <div class="<?= $lemaCls; ?> bg-[#f6faf6] border-l-4 rounded-r-[8px] px-[11px] py-2 text-xs italic text-[#4c5a4c] text-left leading-[1.45] mb-[14px] grow">
                            <i class="fa-solid fa-quote-left text-[#117f09] mr-1"></i> <?= htmlspecialchars($lema); ?>
                        </div>
                        <div class="<?= $chipCls; ?> w-full border-2 border-[#cdd5cd] rounded-[10px] p-[11px] bg-[#fafbfa] font-bold text-xs tracking-[0.8px] uppercase inline-flex items-center justify-center gap-[7px] print:[print-color-adjust:exact]">
                            <i class="fa-solid fa-eye text-[#117f09]"></i> Consulta informativa
                        </div>
                    </div>
                </div>
                <?php
            }
        } else {
            ?>
            <div class="alert alert-warning text-center w-full">
                <i class="fa-solid fa-triangle-exclamation"></i> No hay candidatos a representante para mostrar.
            </div>
            <?php
        }
        ?>
    </div>

    <!-- ========== CARTÓN VOCEROS ========== -->
    <?php if (!empty($datVocTar)): ?>
    <div class="print:hidden">
    <div class="text-center mx-auto mb-[30px] max-w-[1200px]">
        <span class="inline-block bg-white border border-[#e3e8e3] rounded-[40px] px-[26px] py-[10px] font-extrabold text-[15px] tracking-[0.8px] uppercase text-[#123a1f] shadow-[0_4px_12px_rgba(0,0,0,0.06)]">
            <i class="fa-solid fa-layer-group text-[#117f09] mr-2"></i> Segunda elección
        </span>
    </div>

    <div class="isolate relative overflow-hidden mx-auto mb-[30px] max-w-[1040px] rounded-[18px] py-[22px] px-7 text-white bg-[linear-gradient(135deg,#083a5e_0%,#0f5c8c_55%,#1b83bd_100%)] shadow-[0_10px_28px_rgba(8,58,94,0.35)] flex items-center justify-between flex-wrap gap-[14px] border-l-[6px] border-[#ffdd59] print:[print-color-adjust:exact]">
        <div>
            <h4 class="m-0 mb-[5px] text-[1.45rem] font-extrabold text-white tracking-[0.3px]">
                <i class="fa-solid fa-bullhorn"></i> Candidatos a Vocero de Ficha
            </h4>
            <p class="m-0 text-base text-[#dceefb]">Cartón informativo de la votación para elegir vocero.</p>
        </div>
        <div class="inline-flex items-center gap-2 bg-white/15 border border-white/40 rounded-full px-[18px] py-[9px] text-base font-bold">
            <i class="fa-solid fa-users"></i> <?= count($datVocTar); ?> Opciones
        </div>
        <i class="fa-solid fa-bullhorn absolute -right-5 -bottom-9 text-[150px] text-white/5 -z-10 pointer-events-none"></i>
    </div>

    <div class="flex flex-wrap justify-center items-stretch gap-[26px] pt-[6px] pb-[26px] max-w-[1200px] mx-auto">
        <?php
        foreach ($datVocTar as $v) {
            $esBlanco = !empty($v['esblanco']);
            $nombre   = isset($v['nomusu']) ? $v['nomusu'] : 'SIN NOMBRE';
            $numCard  = $esBlanco ? 'BLANCO' : (isset($v['noca']) ? htmlspecialchars($v['noca']) : '???');
            $foto     = isset($v['fotcan']) && !empty($v['fotcan']) ? htmlspecialchars($v['fotcan']) : '';
            $ficha    = isset($v['idfic']) && !empty($v['idfic']) ? htmlspecialchars($v['idfic']) : '';
            $programa = isset($v['nomfic']) && !empty($v['nomfic']) ? htmlspecialchars($v['nomfic']) : '';
            $lema     = isset($v['lema']) && !empty($v['lema']) ? $v['lema'] : ($esBlanco ? 'No hay inclinación por ningún candidato.' : 'Compromiso y liderazgo al servicio de los aprendices.');

            $cardCls  = 'w-[262px] max-md:w-full max-md:max-w-[300px] bg-white rounded-[18px] overflow-hidden text-center shadow-[0_4px_12px_rgba(0,0,0,0.06)] transition-transform duration-200 relative flex flex-col';
            $cardCls .= $esBlanco
                ? ' border-2 border-dashed border-[#b9c2b9] bg-[#fafbfa] hover:border-[#117f09] hover:shadow-[0_18px_40px_rgba(17,127,9,0.14)]'
                : ' border border-[#e3e8e3] hover:-translate-y-1 hover:border-[#cfe8cd] hover:shadow-[0_18px_40px_rgba(17,127,9,0.22)]';
            $fotoCls  = 'relative shrink-0 h-[210px] max-md:h-[180px] bg-[#f2f5f2]';
            $fotoPlace = $esBlanco
                ? 'flex items-center justify-center bg-[linear-gradient(160deg,#ffffff,#edf1ed)] text-[#a9b4a9]'
                : 'flex items-center justify-center bg-[linear-gradient(160deg,#e8f4e8,#d6e8d6)] text-[#83a983]';
            $nomCls   = $esBlanco ? 'text-[#5b645b]' : 'text-[#17321a]';
            $lemaCls  = $esBlanco ? 'border-l-[#a9b4a9]' : 'border-l-[#117f09]';
            $chipCls  = $esBlanco ? 'text-[#4c5a4c] border-solid' : 'text-[#6b756b]';
            $avatarCls = 'w-[104px] h-[104px] rounded-full bg-[linear-gradient(135deg,#117f09,#00af00)] text-white flex items-center justify-center text-[40px] font-extrabold tracking-wide shadow-[0_10px_22px_rgba(17,127,9,0.35)] border-4 border-white [text-shadow:0_2px_5px_rgba(0,0,0,0.25)]';
            $numCls   = 'absolute top-3 left-3 z-[2] inline-flex items-center bg-[rgba(15,70,8,0.88)] text-white font-extrabold text-[13px] px-[13px] py-[6px] rounded-full tracking-[0.6px] shadow-[0_2px_8px_rgba(0,0,0,0.28)]';
            ?>
            <div class="<?= $cardCls; ?>">

                <?php if ($esBlanco) { ?>
                    <div class="<?= $fotoCls . ' ' . $fotoPlace; ?>">
                        <div class="w-[84px] h-[84px] rounded-full bg-white text-[#a9b4a9] flex items-center justify-center text-[32px] shadow-[0_8px_18px_rgba(0,0,0,0.08)] border-2 border-dashed border-[#cdd5cd]">
                            <i class="fa-solid fa-ban"></i>
                        </div>
                    </div>
                <?php } elseif ($foto && $foto != 'image/usuario.png') { ?>
                    <div class="<?= $fotoCls; ?>">
                        <span class="<?= $numCls; ?>"><i class="fa-solid fa-id-card mr-[6px]"></i><?= $numCard; ?></span>
                        <img src="<?= $foto; ?>" alt="<?= htmlspecialchars($nombre); ?>" class="block w-full h-full object-cover"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="hidden <?= $avatarCls; ?>"><?= inicialesNombres($nombre); ?></div>
                    </div>
                <?php } else { ?>
                    <div class="<?= $fotoCls . ' ' . $fotoPlace; ?>">
                        <span class="<?= $numCls; ?>"><i class="fa-solid fa-id-card mr-[6px]"></i><?= $numCard; ?></span>
                        <div class="<?= $avatarCls; ?>"><?= inicialesNombres($nombre); ?></div>
                    </div>
                <?php } ?>

                <div class="px-4 pt-4 pb-[18px] flex flex-col grow">
                    <div class="<?= $nomCls; ?> text-[15px] font-extrabold leading-[1.35] min-h-[42px] tracking-[0.2px] mb-[5px]"><?= strtoupper(htmlspecialchars($nombre)); ?></div>
                    <div class="text-[#6b756b] text-xs mb-[10px]">
                        <?php if ($esBlanco) { ?>
                            <i class="fa-solid fa-file-circle-minus text-[#117f09] mr-[5px]"></i> Opción democrática oficial
                        <?php } else { ?>
                            <?php if ($ficha) { ?><i class="fa-solid fa-hashtag text-[#117f09] mr-[5px]"></i>Ficha <?= $ficha; ?><?php } ?>
                            <?php if ($programa) { ?>&nbsp;·&nbsp;<i class="fa-solid fa-graduation-cap text-[#117f09] mr-[5px]"></i><?= $programa; ?><?php } ?>
                        <?php } ?>
                    </div>
                    <div class="<?= $lemaCls; ?> bg-[#f6faf6] border-l-4 rounded-r-[8px] px-[11px] py-2 text-xs italic text-[#4c5a4c] text-left leading-[1.45] mb-[14px] grow">
                        <i class="fa-solid fa-quote-left text-[#117f09] mr-1"></i> <?= htmlspecialchars($lema); ?>
                    </div>
                    <div class="<?= $chipCls; ?> w-full border-2 border-[#cdd5cd] rounded-[10px] p-[11px] bg-[#fafbfa] font-bold text-xs tracking-[0.8px] uppercase inline-flex items-center justify-center gap-[7px]">
                        <i class="fa-solid fa-eye text-[#117f09]"></i> Consulta informativa
                    </div>
                </div>
            </div>
            <?php
        }
        ?>
    </div>
    </div>
    <?php endif; ?>
</div>

<div class="text-center max-w-[760px] mx-auto mb-[30px] print:hidden">
    <p class="text-[#5b635b] text-[14.5px] leading-[1.65]">
        <i class="fa-solid fa-circle-info text-[#117f09] mr-[6px]"></i>
        Esta es una vista <strong class="text-[#117f09]">informativa</strong> del cartón electoral. El ejercicio del voto se realiza
        desde las opciones habilitadas en cada proceso de votación.
    </p>
</div>

<p class="text-center text-[#8a938a] text-[13px] my-[6px] mb-[18px] print:hidden">
    <i class="fa-solid fa-shield-halved text-[#117f09] mr-[6px]"></i>
    El voto es personal, secreto e irreversible.
</p>