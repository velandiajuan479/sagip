<?php
require_once('controllers/votcvot.php');
?>
<?php echo titulo2("<i class='" . $icono . "'></i> Votación de Representantes", 2); ?>

<?php if (!empty($_SESSION['votmsg'])):
    $votmsg = $_SESSION['votmsg'];
    unset($_SESSION['votmsg']);
?>
<div class="alert alert-<?= htmlspecialchars($votmsg['tipo']); ?> rounded-4 shadow-sm w-100 mb-3" role="alert">
    <i class="fa-solid fa-circle-info"></i> <?= htmlspecialchars($votmsg['texto']); ?>
</div>
<?php endif; ?>

<?php if ($yaVoto): ?>
<div class="alert alert-success d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 rounded-4 shadow-sm p-4 mb-3" role="alert">
    <div class="d-flex align-items-start gap-3">
        <i class="fa-solid fa-circle-check fa-2x text-success mt-1"></i>
        <div>
            <h4 class="alert-heading fw-bold mb-1">Tu voto ya ha sido registrado</h4>
            <p class="mb-0">Ya has ejercido tu derecho al voto para <strong>Representante de Aprendices</strong>. A continuación puedes ver los candidatos y la opción por la que votaste. <strong>Tu voto es definitivo y no se puede modificar.</strong></p>
        </div>
    </div>
    <span class="badge bg-white text-success border border-success rounded-pill px-3 py-2 text-nowrap">
        <i class="fa-solid fa-shield-halved"></i> Voto Consignado
    </span>
</div>
<?php else: ?>
<div class="alert alert-success d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 rounded-4 shadow-sm p-4 mb-3" role="alert">
    <div class="d-flex align-items-start gap-3">
        <i class="fa-solid fa-square-check fa-2x text-success mt-1"></i>
        <div>
            <h4 class="alert-heading fw-bold mb-1">Elección de Representante de Aprendices</h4>
            <p class="mb-0">Selecciona la tarjeta de tu candidato de preferencia o elige el <strong>Voto en Blanco</strong> y haz clic en el botón <strong>Votar</strong>.</p>
        </div>
    </div>
    <span class="badge bg-success text-white rounded-pill px-3 py-2 text-nowrap">
        <i class="fa-solid fa-users"></i> <?= count($dat); ?> Opciones
    </span>
</div>
<p class="text-muted mb-4">
    <i class="fa-solid fa-circle-info text-success"></i>
    Recuerda que tu voto es personal, secreto e <strong>irreversible</strong>. Solo podrás votar <strong>una única vez</strong> por tu representante.
</p>
<?php endif; ?>

<div class="row g-4">
    <?php
    if (!empty($dat)) {
        $nCand = 0;
        foreach ($dat as $d) {
            $esBlanco  = !empty($d['esblanco']);
            $nombre    = isset($d['nomusu']) ? $d['nomusu'] : 'SIN NOMBRE';
            $ficha     = isset($d['idfic']) && !empty($d['idfic']) ? htmlspecialchars($d['idfic']) : '';
            $programa  = isset($d['nomfic']) && !empty($d['nomfic']) ? htmlspecialchars($d['nomfic']) : '';
            $lema      = isset($d['lema']) && !empty($d['lema']) ? $d['lema'] : ($esBlanco ? 'No hay inclinación por ningún candidato.' : 'Compromiso y liderazgo al servicio de los aprendices.');
            $fueVotado = ($yaVoto && $candVotadoId && $d['idusu'] == $candVotadoId);

            if (!$esBlanco) {
                $nCand++;
            }
            $numCard = $esBlanco
                ? 'EN BLANCO'
                : (!empty($d['noca']) ? str_pad($d['noca'], 2, '0', STR_PAD_LEFT) : str_pad($nCand, 2, '0', STR_PAD_LEFT));
            ?>
            <div class="col-12 col-sm-6 col-lg-4 d-flex">
                <?php if ($yaVoto): ?>
                <!-- Tarjeta de solo lectura después de haber votado -->
                <div class="card h-100 w-100 rounded-4 overflow-hidden <?= $fueVotado ? 'border-success border-3 shadow' : 'shadow-sm opacity-50'; ?>">
                    <div class="<?= $esBlanco ? 'bg-secondary' : 'bg-success'; ?> bg-gradient text-white text-center fw-bold py-2">
                        <?php if ($esBlanco): ?>
                            <i class="fa-solid fa-inbox"></i> VOTO EN BLANCO
                        <?php else: ?>
                            <i class="fa-solid fa-square-check"></i> N° <?= $numCard; ?> · TARJETÓN
                        <?php endif; ?>
                    </div>
                    <div class="card-body p-4 text-center d-flex flex-column">
                        <?php if ($fueVotado): ?>
                        <span class="badge bg-success rounded-pill px-3 py-2 mb-3 align-self-center">
                            <i class="fa-solid fa-circle-check"></i> Tu Voto Registrado
                        </span>
                        <?php endif; ?>

                        <?php if ($esBlanco): ?>
                        <div class="ratio ratio-1x1 w-50 mx-auto mb-3 rounded-circle bg-light border border-2 border-success">
                            <div class="d-flex align-items-center justify-content-center">
                                <i class="fa-solid fa-box-archive text-success display-4"></i>
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="ratio ratio-1x1 w-50 mx-auto mb-3 rounded-circle overflow-hidden bg-light border">
                            <img src="img/user.jpg" alt="<?= htmlspecialchars($nombre); ?>" class="w-100 h-100 object-fit-cover">
                        </div>
                        <?php endif; ?>

                        <h5 class="fw-bold text-uppercase mb-1"><?= strtoupper(htmlspecialchars($nombre)); ?></h5>

                        <div class="mb-3">
                            <?php if ($esBlanco): ?>
                                <span class="badge bg-light text-success border border-success rounded-pill px-3 py-2">
                                    <i class="fa-solid fa-file-circle-minus"></i> Opción democrática oficial
                                </span>
                            <?php else: ?>
                                <?php if ($ficha): ?>
                                <span class="badge bg-light text-success border border-success rounded-pill px-2 py-1">
                                    <i class="fa-solid fa-hashtag"></i> Ficha <?= $ficha; ?>
                                </span>
                                <?php endif; ?>
                                <?php if ($programa): ?>
                                <span class="badge bg-light text-success border border-success rounded-pill px-2 py-1">
                                    <i class="fa-solid fa-graduation-cap"></i> <?= $programa; ?>
                                </span>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>

                        <p class="text-muted small fst-italic mb-4">
                            <i class="fa-solid fa-quote-left text-success"></i> <?= htmlspecialchars($lema); ?>
                        </p>

                        <button type="button" class="btn <?= $fueVotado ? 'btn-success' : 'btn-outline-secondary'; ?> w-100 fw-bold rounded-pill py-2 mt-auto" disabled>
                            <i class="fa-solid <?= $fueVotado ? 'fa-circle-check' : 'fa-lock'; ?>"></i>
                            <?= $fueVotado ? 'Votaste por esta opción' : 'No seleccionado'; ?>
                        </button>
                    </div>
                </div>
                <?php else: ?>
                <!-- Tarjeta interactiva para emitir el voto -->
                <form class="card h-100 w-100 border-0 shadow-sm rounded-4 overflow-hidden" action="home.php?pg=<?= htmlspecialchars($pg); ?>" method="POST">
                    <div class="<?= $esBlanco ? 'bg-secondary' : 'bg-success'; ?> bg-gradient text-white text-center fw-bold py-2">
                        <?php if ($esBlanco): ?>
                            <i class="fa-solid fa-inbox"></i> VOTO EN BLANCO
                        <?php else: ?>
                            <i class="fa-solid fa-square-check"></i> N° <?= $numCard; ?> · TARJETÓN
                        <?php endif; ?>
                    </div>
                    <div class="card-body p-4 text-center d-flex flex-column">
                        <?php if ($esBlanco): ?>
                        <div class="ratio ratio-1x1 w-50 mx-auto mb-3 rounded-circle bg-light border border-2 border-success">
                            <div class="d-flex align-items-center justify-content-center">
                                <i class="fa-solid fa-box-archive text-success display-4"></i>
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="ratio ratio-1x1 w-50 mx-auto mb-3 rounded-circle overflow-hidden bg-light border">
                            <img src="img/user.jpg" alt="<?= htmlspecialchars($nombre); ?>" class="w-100 h-100 object-fit-cover">
                        </div>
                        <?php endif; ?>

                        <h5 class="fw-bold text-uppercase mb-1"><?= strtoupper(htmlspecialchars($nombre)); ?></h5>

                        <div class="mb-3">
                            <?php if ($esBlanco): ?>
                                <span class="badge bg-light text-success border border-success rounded-pill px-3 py-2">
                                    <i class="fa-solid fa-file-circle-minus"></i> Opción democrática oficial
                                </span>
                            <?php else: ?>
                                <?php if ($ficha): ?>
                                <span class="badge bg-light text-success border border-success rounded-pill px-2 py-1">
                                    <i class="fa-solid fa-hashtag"></i> Ficha <?= $ficha; ?>
                                </span>
                                <?php endif; ?>
                                <?php if ($programa): ?>
                                <span class="badge bg-light text-success border border-success rounded-pill px-2 py-1">
                                    <i class="fa-solid fa-graduation-cap"></i> <?= $programa; ?>
                                </span>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>

                        <p class="text-muted small fst-italic mb-4">
                            <i class="fa-solid fa-quote-left text-success"></i> <?= htmlspecialchars($lema); ?>
                        </p>

                        <input type="hidden" name="canusu" value="<?= $d['idusu']; ?>">
                        <input type="hidden" name="opera" value="save">
                        <button type="submit" class="btn <?= $esBlanco ? 'btn-outline-secondary' : 'btn-success'; ?> w-100 fw-bold rounded-pill py-2 mt-auto">
                            <i class="fa-solid <?= $esBlanco ? 'fa-inbox' : 'fa-square-check'; ?>"></i>
                            <?= $esBlanco ? 'Votar en Blanco' : 'Votar por esta opción'; ?>
                        </button>
                    </div>
                </form>
                <?php endif; ?>
            </div>
            <?php
        }
    } else {
        ?>
        <div class="col-12">
            <div class="alert alert-warning text-center rounded-4 shadow-sm mb-0">
                <i class="fa-solid fa-triangle-exclamation"></i> No hay candidatos disponibles para votación.
            </div>
        </div>
        <?php
    }
    ?>
</div>

<p class="text-center text-muted small mt-4 mb-0">
    <i class="fa-solid fa-shield-halved text-success"></i>
    Tu voto está protegido y será registrado de forma confidencial.
</p>
