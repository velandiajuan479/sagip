<?php
require_once('controllers/votcvvc.php');

echo titulo2("<i class='fa-solid fa-check-to-slot'></i> Votación de Voceros de Ficha", 2);
?>

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
            <p class="mb-0">Ya has ejercido tu derecho al voto para <strong>Vocero de Ficha</strong>. A continuación puedes ver los candidatos y la opción por la que votaste. <strong>Tu voto es definitivo y no se puede modificar.</strong></p>
        </div>
    </div>
    <span class="badge bg-white text-success border border-success rounded-pill px-3 py-2 text-nowrap">
        <i class="fa-solid fa-shield-halved"></i> Voto Consignado
    </span>
</div>

<div class="row g-4">
    <?php foreach ($dat as $i => $d):
        $candId     = htmlspecialchars($d['idusu']);
        $isBlanco   = !empty($d['is_blanco']) && $d['is_blanco'] === true;
        $candNum    = $isBlanco ? 'EN BLANCO' : (!empty($d['noca']) ? str_pad($d['noca'], 2, '0', STR_PAD_LEFT) : str_pad(($i + 1), 2, '0', STR_PAD_LEFT));
        $candNom    = htmlspecialchars($d['nomusu']);
        $candFic    = !empty($d['idfic']) ? htmlspecialchars($d['idfic']) : '';
        $candNomFic = !empty($d['nomfic']) ? htmlspecialchars($d['nomfic']) : 'Formación Titulada';
        $candLema   = !empty($d['lema']) ? htmlspecialchars($d['lema']) : 'Compromiso y liderazgo con la comunidad aprendiz.';
        $fueVotado  = ($candVotadoId && $candId == $candVotadoId);
    ?>
    <div class="col-12 col-sm-6 col-lg-3 d-flex">
        <div class="card h-100 w-100 rounded-4 overflow-hidden <?= $fueVotado ? 'border-success border-3 shadow' : 'shadow-sm opacity-50'; ?>">
            <div class="<?= $isBlanco ? 'bg-secondary' : 'bg-success'; ?> bg-gradient text-white text-center fw-bold py-2">
                <?php if ($isBlanco): ?>
                    <i class="fa-solid fa-inbox"></i> VOTO EN BLANCO
                <?php else: ?>
                    <i class="fa-solid fa-check-to-slot"></i> N° <?= $candNum; ?> · TARJETÓN
                <?php endif; ?>
            </div>
            <div class="card-body p-4 text-center d-flex flex-column">
                <?php if ($fueVotado): ?>
                <span class="badge bg-success rounded-pill px-3 py-2 mb-3 align-self-center">
                    <i class="fa-solid fa-circle-check"></i> Tu Voto Registrado
                </span>
                <?php endif; ?>

                <?php if ($isBlanco): ?>
                <div class="ratio ratio-1x1 w-50 mx-auto mb-3 rounded-circle bg-light border border-2 border-success">
                    <div class="d-flex align-items-center justify-content-center">
                        <i class="fa-solid fa-box-archive text-success display-4"></i>
                    </div>
                </div>
                <?php else: ?>
                <div class="ratio ratio-1x1 w-50 mx-auto mb-3 rounded-circle overflow-hidden bg-light border">
                    <img src="img/user.jpg" alt="<?= $candNom; ?>" class="w-100 h-100 object-fit-cover">
                </div>
                <?php endif; ?>

                <h5 class="fw-bold text-uppercase mb-1"><?= strtoupper($candNom); ?></h5>

                <div class="mb-2">
                    <?php if (!empty($candFic)): ?>
                    <span class="badge bg-light text-success border border-success rounded-pill px-2 py-1">
                        <i class="fa-solid fa-hashtag"></i> Ficha: <?= $candFic; ?>
                    </span>
                    <?php endif; ?>
                </div>

                <div class="text-muted small mb-2">
                    <i class="fa-solid fa-<?= $isBlanco ? 'scale-balanced' : 'graduation-cap'; ?>"></i> <?= $candNomFic; ?>
                </div>

                <p class="text-muted small fst-italic mb-4">
                    <i class="fa-solid fa-quote-left text-success"></i> <?= $candLema; ?>
                </p>

                <button type="button" class="btn <?= $fueVotado ? 'btn-success' : 'btn-outline-secondary'; ?> w-100 fw-bold rounded-pill py-2 mt-auto" disabled>
                    <i class="fa-solid <?= $fueVotado ? 'fa-circle-check' : 'fa-lock'; ?>"></i>
                    <?= $fueVotado ? 'Votaste por esta opción' : 'No seleccionado'; ?>
                </button>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php else: ?>

<div class="alert alert-success d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 rounded-4 shadow-sm p-4 mb-3" role="alert">
    <div class="d-flex align-items-start gap-3">
        <i class="fa-solid fa-vote-yea fa-2x text-success mt-1"></i>
        <div>
            <h4 class="alert-heading fw-bold mb-1">Elección de Voceros de Ficha</h4>
            <p class="mb-0">Selecciona la tarjeta de tu candidato de preferencia o el <strong>Voto en Blanco</strong> y haz clic en <strong>Confirmar Mi Voto</strong>.</p>
        </div>
    </div>
    <span class="badge bg-success text-white rounded-pill px-3 py-2 text-nowrap">
        <i class="fa-solid fa-users"></i> <?= count($dat); ?> Opciones Disponibles
    </span>
</div>

<p class="text-muted mb-4">
    <i class="fa-solid fa-circle-info text-success"></i>
    Recuerda que tu voto es personal, secreto e <strong>irreversible</strong>. Solo podrás votar <strong>una única vez</strong> por el vocero de tu ficha.
</p>

<form id="frmVotacion" name="frmVotacion" action="home.php?pg=<?= htmlspecialchars($pg); ?>" method="POST">
    <input type="hidden" name="opera" value="save">

    <div class="row g-4">
        <?php foreach ($dat as $i => $d):
            $candId     = htmlspecialchars($d['idusu']);
            $isBlanco   = !empty($d['is_blanco']) && $d['is_blanco'] === true;
            $candNum    = $isBlanco ? 'EN BLANCO' : (!empty($d['noca']) ? str_pad($d['noca'], 2, '0', STR_PAD_LEFT) : str_pad(($i + 1), 2, '0', STR_PAD_LEFT));
            $candNom    = htmlspecialchars($d['nomusu']);
            $candFic    = !empty($d['idfic']) ? htmlspecialchars($d['idfic']) : '';
            $candNomFic = !empty($d['nomfic']) ? htmlspecialchars($d['nomfic']) : 'Formación Titulada';
            $candLema   = !empty($d['lema']) ? htmlspecialchars($d['lema']) : 'Compromiso y liderazgo con la comunidad aprendiz.';
        ?>
        <div class="col-12 col-sm-6 col-lg-3 d-flex">
            <div class="card h-100 w-100 border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="<?= $isBlanco ? 'bg-secondary' : 'bg-success'; ?> bg-gradient text-white text-center fw-bold py-2">
                    <?php if ($isBlanco): ?>
                        <i class="fa-solid fa-inbox"></i> VOTO EN BLANCO
                    <?php else: ?>
                        <i class="fa-solid fa-check-to-slot"></i> N° <?= $candNum; ?> · TARJETÓN
                    <?php endif; ?>
                </div>
                <div class="card-body p-4 text-center d-flex flex-column">
                    <?php if ($isBlanco): ?>
                    <div class="ratio ratio-1x1 w-50 mx-auto mb-3 rounded-circle bg-light border border-2 border-success">
                        <div class="d-flex align-items-center justify-content-center">
                            <i class="fa-solid fa-box-archive text-success display-4"></i>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="ratio ratio-1x1 w-50 mx-auto mb-3 rounded-circle overflow-hidden bg-light border">
                        <img src="img/user.jpg" alt="<?= $candNom; ?>" class="w-100 h-100 object-fit-cover">
                    </div>
                    <?php endif; ?>

                    <h5 class="fw-bold text-uppercase mb-1"><?= strtoupper($candNom); ?></h5>

                    <div class="mb-2">
                        <?php if (!empty($candFic)): ?>
                        <span class="badge bg-light text-success border border-success rounded-pill px-2 py-1">
                            <i class="fa-solid fa-hashtag"></i> Ficha: <?= $candFic; ?>
                        </span>
                        <?php endif; ?>
                    </div>

                    <div class="text-muted small mb-2">
                        <i class="fa-solid fa-<?= $isBlanco ? 'scale-balanced' : 'graduation-cap'; ?>"></i> <?= $candNomFic; ?>
                    </div>

                    <p class="text-muted small fst-italic mb-4">
                        <i class="fa-solid fa-quote-left text-success"></i> <?= $candLema; ?>
                    </p>

                    <div class="mt-auto">
                        <input class="btn-check" type="radio" name="canusu" id="voto<?= $i; ?>" value="<?= $candId; ?>" autocomplete="off" required>
                        <label class="btn <?= $isBlanco ? 'btn-outline-secondary' : 'btn-outline-success'; ?> w-100 rounded-pill fw-bold" for="voto<?= $i; ?>">
                            <i class="fa-solid <?= $isBlanco ? 'fa-inbox' : 'fa-check'; ?>"></i>
                            <?= $isBlanco ? 'Votar en Blanco' : 'Elegir Candidato'; ?>
                        </label>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="alert alert-success d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 rounded-4 shadow-sm p-4 mt-4 mb-0" role="alert">
        <div class="d-flex align-items-center gap-3">
            <i class="fa-solid fa-hand-pointer fa-2x text-success"></i>
            <div>
                <span class="fw-bold d-block">Marca la opción de tu preferencia</span>
                <span class="small">Elige el tarjetón de tu candidato o el <strong>Voto en Blanco</strong> y confirma para emitir tu voto.</span>
            </div>
        </div>
        <button type="submit" class="btn btn-success btn-lg fw-bold rounded-pill px-4 text-nowrap">
            <i class="fa-solid fa-envelope-open-text"></i> Confirmar Mi Voto
        </button>
    </div>
</form>

<?php endif; ?>
