<?php 
require_once 'controllers/votcnvot.php'; 
echo titulo2("<i class='fa " . $icono . "'></i> No Votantes", 2); 
?>

<!-- Dashboard Estadístico -->
<div class="row g-3 mb-4 mt-1">
    <!-- Votaron -->
    <div class="col-12 col-md-4">
        <div class="card border shadow-sm h-100 rounded-3">
            <div class="card-body d-flex align-items-center p-3">
                <div class="rounded-3 bg-success-subtle text-success me-3 d-flex align-items-center justify-content-center w-[52px] h-[52px] min-w-[52px]">
                    <i class="fas fa-check-circle fa-2x"></i>
                </div>
                <div>
                    <span class="text-muted small text-uppercase fw-semibold d-block">Votaron</span>
                    <h3 class="mb-0 fw-bold text-success"><?= number_format($votaron, 0, ',', '.'); ?></h3>
                    <small class="text-muted"><i class="fas fa-percent me-1"></i><?= $porc_votaron; ?>% de participación</small>
                </div>
            </div>
        </div>
    </div>

    <!-- No votaron -->
    <div class="col-12 col-md-4">
        <div class="card border shadow-sm h-100 rounded-3">
            <div class="card-body d-flex align-items-center p-3">
                <div class="rounded-3 bg-danger-subtle text-danger me-3 d-flex align-items-center justify-content-center w-[52px] h-[52px] min-w-[52px]">
                    <i class="fas fa-user-times fa-2x"></i>
                </div>
                <div>
                    <span class="text-muted small text-uppercase fw-semibold d-block">No votaron</span>
                    <h3 class="mb-0 fw-bold text-danger"><?= number_format($no_votaron, 0, ',', '.'); ?></h3>
                    <small class="text-danger fw-semibold"><i class="fas fa-exclamation-circle me-1"></i><?= $porc_no_votaron; ?>% sin votar</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Votos en blanco -->
    <div class="col-12 col-md-4">
        <div class="card border shadow-sm h-100 rounded-3">
            <div class="card-body d-flex align-items-center p-3">
                <div class="rounded-3 bg-secondary-subtle text-secondary me-3 d-flex align-items-center justify-content-center w-[52px] h-[52px] min-w-[52px]">
                    <i class="fas fa-file fa-2x"></i>
                </div>
                <div>
                    <span class="text-muted small text-uppercase fw-semibold d-block">Votos en blanco</span>
                    <h3 class="mb-0 fw-bold text-dark"><?= number_format($votos_blanco, 0, ',', '.'); ?></h3>
                    <small class="text-muted"><?= $porc_blanco; ?>% del total</small>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$totalSeguro = max((int)$total_personas, 1);
$gradVotaron   = round(((int)$votaron / $totalSeguro) * 360);
$gradNoVotaron = round(((int)$no_votaron / $totalSeguro) * 360);
$gradBlanco    = max(360 - $gradVotaron - $gradNoVotaron, 0);
?>

<!-- Participación y Resumen -->
<div class="row g-4 mb-4 align-items-stretch">
    <!-- Donut de Participación -->
    <div class="col-12 col-lg-5">
        <div class="card border-0 shadow-sm rounded-3 h-100">
            <div class="card-header bg-white border-0 pt-3 pb-0">
                <h6 class="fw-bold text-secondary mb-0">
                    <i class="fas fa-chart-pie text-success me-2"></i> Participación de Votación
                </h6>
            </div>
            <div class="card-body p-3 p-md-4 d-flex flex-column align-items-center justify-content-center">
                <div class="position-relative d-inline-flex align-items-center justify-content-center w-[190px] h-[190px] rounded-full bg-[conic-gradient(#198754_0deg_<?= $gradVotaron ?>deg,#dc3545_<?= $gradVotaron ?>deg_<?= $gradVotaron + $gradNoVotaron ?>deg,#6c757d_<?= $gradVotaron + $gradNoVotaron ?>deg_360deg)] shadow-[0_4px_12px_rgba(0,0,0,0.08)]">
                    <div class="bg-white rounded-circle d-flex flex-column align-items-center justify-content-center shadow-sm w-[130px] h-[130px]">
                        <span class="fw-bold text-dark fs-3 leading-none"><?= $porc_votaron; ?>%</span>
                        <span class="text-muted small text-[11px]">Participación</span>
                    </div>
                </div>
                <div class="d-flex flex-wrap justify-content-center gap-3 mt-3">
                    <span class="small d-inline-flex align-items-center text-secondary">
                        <span class="d-inline-block rounded-circle me-1 w-[10px] h-[10px] bg-[#198754]"></span> Votaron
                    </span>
                    <span class="small d-inline-flex align-items-center text-secondary">
                        <span class="d-inline-block rounded-circle me-1 w-[10px] h-[10px] bg-[#dc3545]"></span> No votaron
                    </span>
                    <span class="small d-inline-flex align-items-center text-secondary">
                        <span class="d-inline-block rounded-circle me-1 w-[10px] h-[10px] bg-[#6c757d]"></span> En blanco
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Resumen de Votación -->
    <div class="col-12 col-lg-7">
        <div class="card border-0 shadow-sm rounded-3 h-100">
            <div class="card-header bg-white border-0 pt-3 pb-0">
                <h6 class="fw-bold text-secondary mb-0">
                    <i class="fas fa-chart-bar text-success me-2"></i> Resumen de Votación
                </h6>
            </div>
            <div class="card-body p-3 p-md-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-secondary small text-uppercase py-2">Métrica</th>
                                <th class="text-center text-secondary small text-uppercase py-2">Cantidad</th>
                                <th class="text-end text-secondary small text-uppercase py-2">Porcentaje</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="py-2">
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    <span class="text-success fw-semibold">Votaron</span>
                                </td>
                                <td class="text-center fw-bold text-success py-2"><?= number_format($votaron, 0, ',', '.'); ?></td>
                                <td class="text-end fw-semibold text-success py-2"><?= $porc_votaron; ?>%</td>
                            </tr>
                            <tr>
                                <td class="py-2">
                                    <i class="fas fa-user-times text-danger me-2"></i>
                                    <span class="text-danger fw-semibold">No votaron</span>
                                </td>
                                <td class="text-center fw-bold text-danger py-2"><?= number_format($no_votaron, 0, ',', '.'); ?></td>
                                <td class="text-end fw-semibold text-danger py-2"><?= $porc_no_votaron; ?>%</td>
                            </tr>
                            <tr>
                                <td class="py-2">
                                    <i class="fas fa-file text-secondary me-2"></i>
                                    <span class="text-muted fw-semibold">Votos en blanco</span>
                                </td>
                                <td class="text-center fw-bold text-muted py-2"><?= number_format($votos_blanco, 0, ',', '.'); ?></td>
                                <td class="text-end fw-semibold text-muted py-2"><?= $porc_blanco; ?>%</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$totalReg   = is_array($dat) ? count($dat) : 0;
$novotoReg  = 0;
$votoReg    = 0;
foreach (($dat ?? []) as $d) {
    if (!empty($d['votado'])) $votoReg++; else $novotoReg++;
}

$badgeJornada = function ($nomval) {
    $j = ($nomval ?? '');
    if (strpos($j, 'Ã') !== false) $j = @utf8_decode($j);
    $j = str_replace(['MaÃ±ana', 'maÃ±ana'], ['Mañana', 'mañana'], $j);
    $jn = strtolower($j);
    $jn = str_replace(['ñ', 'á', 'é', 'í', 'ó', 'ú'], ['n', 'a', 'e', 'i', 'o', 'u'], $jn);
    if (strpos($jn, 'manana') !== false) return ['bg-warning-subtle text-dark border border-warning', 'fa-sun', $j];
    if (strpos($jn, 'tarde') !== false) return ['bg-info-subtle text-dark border border-info', 'fa-cloud-sun', $j];
    if (strpos($jn, 'virtual') !== false) return ['bg-primary-subtle text-primary border border-primary', 'fa-laptop', $j];
    if (strpos($jn, 'noche') !== false || strpos($jn, 'nocturna') !== false) return ['bg-dark text-white', 'fa-moon', $j];
    if (strpos($jn, 'sabado') !== false || strpos($jn, 'domingo') !== false || strpos($jn, 'fin de semana') !== false) return ['bg-secondary text-white', 'fa-calendar-week', $j];
    return ['bg-light text-dark border', 'fa-clock', $j];
};
?>

<!-- Listado Detallado con DataTables -->
<div class="card border shadow-sm rounded-3 mb-4">
    <div class="card-header bg-white border-bottom py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h5 class="card-title mb-0 fw-bold text-secondary">
            <i class="fa-solid fa-users me-2 text-success"></i> Listado General de Votantes
        </h5>
        <span class="badge bg-secondary rounded-pill">
            <?= number_format($totalReg, 0, ',', '.'); ?> registros
        </span>
    </div>
    <div class="card-body p-3">

        <!-- Filtro por estado -->
        <div class="btn-group mb-3" role="group" aria-label="Filtrar por estado de voto">
            <button type="button" class="btn btn-sm btn-outline-secondary btn-filtro-estado active" data-estado-filtro="todos">
                <i class="fa-solid fa-list-ul me-1"></i> Todos <span class="ms-1">(<?= $totalReg; ?>)</span>
            </button>
            <button type="button" class="btn btn-sm btn-outline-danger btn-filtro-estado" data-estado-filtro="novoto">
                <i class="fa-solid fa-user-times me-1"></i> No votaron <span class="ms-1">(<?= $novotoReg; ?>)</span>
            </button>
            <button type="button" class="btn btn-sm btn-outline-success btn-filtro-estado" data-estado-filtro="voto">
                <i class="fa-solid fa-check me-1"></i> Votaron <span class="ms-1">(<?= $votoReg; ?>)</span>
            </button>
        </div>

        <div class="table-responsive">
            <table id="example" class="table table-striped table-hover align-middle w-100">
                <thead class="table-light">
                    <tr>
                        <th class="text-center w-[60px]">Foto</th>
                        <th>Usuario</th>
                        <th class="w-[14%]">Perfil</th>
                        <th class="w-[24%]">Ficha / Jornada</th>
                        <th class="w-[22%]">Contacto</th>
                        <th class="text-center w-[14%]">Estado Voto</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($dat)): ?>
                        <?php foreach ($dat as $d):
                            $votado = isset($d['votado']) ? (bool)$d['votado'] : $mnvot->getVotU($d['idusu']);
                            list($jClase, $jIcono, $jTexto) = $badgeJornada($d['nomval'] ?? '');
                            $fotoSrc = (!empty($d['fotcan']) && file_exists($d['fotcan'])) ? $d['fotcan'] : 'img/user.jpg';
                        ?>
                            <tr data-estado="<?= $votado ? '1' : '0'; ?>">
                                <td class="text-center align-middle">
                                    <img src="<?= htmlspecialchars($fotoSrc, ENT_QUOTES, 'UTF-8'); ?>" alt="Foto" width="42" height="42" class="rounded-circle border shadow-sm object-cover">
                                </td>
                                <td class="align-middle">
                                    <div class="fw-bold text-uppercase text-dark"><?= htmlspecialchars($d['nomusu']); ?></div>
                                    <div class="small text-muted mt-1">
                                        <span><strong>Doc:</strong> <?= htmlspecialchars($d['ndocusu']); ?></span>
                                        <?php if (!empty($d['idcen']) && !empty($d['nomcen'])): ?>
                                            <span class="ms-2">| <i class="fa-solid fa-building me-1"></i><?= htmlspecialchars($d['nomcen']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="align-middle">
                                    <span class="badge bg-light text-dark border px-2 py-1"><?= htmlspecialchars($d['nomper']); ?></span>
                                </td>
                                <td class="align-middle">
                                    <?php if (!empty($d['idfic'])): ?>
                                        <div class="fw-semibold text-dark">
                                            <?= htmlspecialchars($d['idfic']); ?> - <?= htmlspecialchars($d['nomfic']); ?>
                                        </div>
                                        <?php if (!empty($jTexto)): ?>
                                            <span class="badge <?= $jClase; ?> px-2 py-1 mt-1">
                                                <i class="fas <?= $jIcono; ?> me-1"></i><?= htmlspecialchars($jTexto, ENT_QUOTES, 'UTF-8'); ?>
                                            </span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="align-middle">
                                    <div class="small">
                                        <?php if (!empty($d['telcan'])): ?>
                                            <div><a href="tel:<?= htmlspecialchars($d['telcan'], ENT_QUOTES, 'UTF-8'); ?>" class="text-decoration-none"><i class="fa-solid fa-phone me-1 text-success"></i><?= htmlspecialchars($d['telcan']); ?></a></div>
                                        <?php endif; ?>
                                        <?php if (!empty($d['emausu'])): ?>
                                            <div><a href="mailto:<?= htmlspecialchars($d['emausu'], ENT_QUOTES, 'UTF-8'); ?>" class="text-decoration-none text-truncate d-inline-block max-w-[220px] align-bottom"><i class="fa-solid fa-envelope me-1 text-secondary"></i><?= htmlspecialchars($d['emausu']); ?></a></div>
                                        <?php endif; ?>
                                        <?php if (empty($d['telcan']) && empty($d['emausu'])): ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="text-center align-middle">
                                    <a href="home.php?pg=<?= $pg; ?>&idusu=<?= $d['idusu']; ?>" class="text-decoration-none" title="<?= $votado ? 'Votó' : 'No votó'; ?>">
                                        <?php if ($votado): ?>
                                            <span class="badge bg-success px-3 py-2 rounded-pill">
                                                <i class="fa-solid fa-thumbs-up me-1"></i> Votó
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-danger px-3 py-2 rounded-pill">
                                                <i class="fa-solid fa-thumbs-down me-1"></i> No votó
                                            </span>
                                        <?php endif; ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>