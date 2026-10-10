<?php require_once 'controllers/votcdpe.php'; ?>

<?php
$icono = isset($icono) ? $icono : 'fa fa-solid fa-user';
if (function_exists('titulo2')) {
    echo titulo2("<i class='" . $icono . "'></i> Datos personales", 2);
} else {
    echo "<h2 class='title-page'><i class='" . $icono . "'></i> Datos personales</h2>";
}

// Determinar la foto del perfil o aprendiz
$fotoPerfil = 'img/user.jpg';
if (!empty($datOne) && !empty($datOne[0]['fotcan']) && file_exists($datOne[0]['fotcan'])) {
    $fotoPerfil = $datOne[0]['fotcan'];
} elseif (file_exists('img/user.jpg')) {
    $fotoPerfil = 'img/user.jpg';
}

// Determinar programa de formación y ficha
$programaFormacion = 'No asignado';
if (!empty($datOne)) {
    if (!empty($datOne[0]['nompro']) && $datOne[0]['nompro'] !== 'Sin programa') {
        $programaFormacion = $datOne[0]['nompro'];
    } elseif (!empty($datOne[0]['nomfic'])) {
        $programaFormacion = $datOne[0]['nomfic'];
    }
}
$numeroFicha = (!empty($datOne) && !empty($datOne[0]['idfic'])) ? $datOne[0]['idfic'] : 'Sin ficha';
$jornada = (!empty($datOne) && !empty($datOne[0]['nomval'])) ? $datOne[0]['nomval'] : 'No asignada';
?>

<div class="mx-auto my-3" style="max-width: 950px;">
    <!-- 1. Indicador general del estado de votación -->
    <div class="alert <?= $votacionAbierta ? 'alert-success border-success-subtle bg-success-subtle text-success-emphasis' : 'alert-danger border-danger-subtle bg-danger-subtle text-danger-emphasis' ?> d-flex align-items-center justify-content-between flex-wrap gap-2 rounded-3 shadow-sm py-2 px-3 mb-4">
        <div class="d-flex align-items-center gap-2">
            <i class="fa-solid <?= $votacionAbierta ? 'fa-door-open text-success' : 'fa-lock text-danger' ?> fa-lg"></i>
            <span class="fw-semibold">
                <?= $votacionAbierta ? 'Proceso de Votación Habilitado' : 'Proceso de Votación Cerrado' ?>
            </span>
        </div>
        <span class="badge <?= $votacionAbierta ? 'bg-success text-white' : 'bg-danger text-white' ?> px-3 py-2">
            <?= $votacionAbierta ? 'Abierto' : 'Cerrado' ?>
        </span>
    </div>

    <!-- 2. Tarjeta de Perfil e Identidad Visual del Aprendiz -->
    <div class="card border shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <div class="d-flex flex-column align-items-center justify-content-center text-center">
                <div class="position-relative d-inline-block" style="width: 120px; height: 120px;">
                    <img src="<?= htmlspecialchars($fotoPerfil) ?>" alt="Foto del Aprendiz" 
                         class="rounded-circle border border-3 border-success shadow-sm" 
                         style="width: 120px; height: 120px; object-fit: cover; background-color: #f8f9fa;">
                    <div class="position-absolute bottom-0 end-0 bg-white rounded-circle border border-2 border-success d-flex align-items-center justify-content-center shadow-sm" 
                         style="width: 38px; height: 38px;" title="SENA">
                        <img src="image/sena.png" alt="SENA" style="width: 22px; height: auto;">
                    </div>
                </div>
                <div class="mt-3">
                    <h4 class="fw-bold text-dark mb-1">
                        <?= (!empty($datOne) && !empty($datOne[0]['nomusu'])) ? htmlspecialchars($datOne[0]['nomusu']) : 'Usuario' ?>
                    </h4>
                    <div class="d-flex justify-content-center gap-2 flex-wrap mt-2">
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                            <i class="fa-solid fa-id-badge me-1"></i>
                            <?= (!empty($datOne) && !empty($datOne[0]['nomper'])) ? htmlspecialchars($datOne[0]['nomper']) : 'Aprendiz' ?>
                        </span>
                        <span class="badge bg-secondary-subtle text-secondary border px-2 py-1">
                            <i class="fa-solid fa-hashtag me-1"></i> Ficha: <?= htmlspecialchars($numeroFicha) ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- 3. Información Académica y de la Ficha -->
            <div class="mt-4 pt-3 border-top">
                <h5 class="fw-bold text-dark mb-3">
                    <i class="fa-solid fa-graduation-cap text-success me-2"></i> Información Académica y Personal
                </h5>

                <div class="row g-3">
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="p-3 bg-light rounded-3 border d-flex align-items-center h-100">
                            <div class="rounded-3 bg-success-subtle text-success d-flex align-items-center justify-content-center me-3 flex-shrink-0" style="width: 44px; height: 44px; font-size: 1.2rem;">
                                <i class="fa-solid fa-address-card"></i>
                            </div>
                            <div class="overflow-hidden">
                                <span class="text-muted small text-uppercase fw-semibold d-block">No. Documento</span>
                                <span class="fw-bold text-dark text-break">
                                    <?= (!empty($datOne) && !empty($datOne[0]['ndocusu'])) ? htmlspecialchars($datOne[0]['ndocusu']) : '---' ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="p-3 bg-light rounded-3 border d-flex align-items-center h-100">
                            <div class="rounded-3 bg-success-subtle text-success d-flex align-items-center justify-content-center me-3 flex-shrink-0" style="width: 44px; height: 44px; font-size: 1.2rem;">
                                <i class="fa-solid fa-building-columns"></i>
                            </div>
                            <div class="overflow-hidden">
                                <span class="text-muted small text-uppercase fw-semibold d-block">Centro de Formación</span>
                                <span class="fw-bold text-dark text-break">
                                    <?= (!empty($datOne) && !empty($datOne[0]['nomcen'])) ? htmlspecialchars($datOne[0]['nomcen']) : '---' ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="p-3 bg-light rounded-3 border d-flex align-items-center h-100">
                            <div class="rounded-3 bg-success-subtle text-success d-flex align-items-center justify-content-center me-3 flex-shrink-0" style="width: 44px; height: 44px; font-size: 1.2rem;">
                                <i class="fa-solid fa-hashtag"></i>
                            </div>
                            <div class="overflow-hidden">
                                <span class="text-muted small text-uppercase fw-semibold d-block">Número de Ficha</span>
                                <span class="fw-bold text-success text-break">
                                    <?= htmlspecialchars($numeroFicha) ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="p-3 bg-light rounded-3 border d-flex align-items-center h-100">
                            <div class="rounded-3 bg-success-subtle text-success d-flex align-items-center justify-content-center me-3 flex-shrink-0" style="width: 44px; height: 44px; font-size: 1.2rem;">
                                <i class="fa-solid fa-book-open"></i>
                            </div>
                            <div class="overflow-hidden">
                                <span class="text-muted small text-uppercase fw-semibold d-block">Programa de Formación</span>
                                <span class="fw-bold text-dark text-break">
                                    <?= htmlspecialchars($programaFormacion) ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="p-3 bg-light rounded-3 border d-flex align-items-center h-100">
                            <div class="rounded-3 bg-success-subtle text-success d-flex align-items-center justify-content-center me-3 flex-shrink-0" style="width: 44px; height: 44px; font-size: 1.2rem;">
                                <i class="fa-solid fa-clock"></i>
                            </div>
                            <div class="overflow-hidden">
                                <span class="text-muted small text-uppercase fw-semibold d-block">Jornada</span>
                                <span class="fw-bold text-dark text-break">
                                    <?= htmlspecialchars($jornada) ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="p-3 bg-light rounded-3 border d-flex align-items-center h-100">
                            <div class="rounded-3 bg-success-subtle text-success d-flex align-items-center justify-content-center me-3 flex-shrink-0" style="width: 44px; height: 44px; font-size: 1.2rem;">
                                <i class="fa-solid fa-user-check"></i>
                            </div>
                            <div class="overflow-hidden">
                                <span class="text-muted small text-uppercase fw-semibold d-block">Estado de Usuario</span>
                                <span class="fw-bold text-dark">
                                    <span class="badge <?= (!empty($datOne) && $datOne[0]['actusu'] == 1) ? 'bg-success' : 'bg-secondary' ?>">
                                        <?= (!empty($datOne) && $datOne[0]['actusu'] == 1) ? 'Activo' : 'Inactivo' ?>
                                    </span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4. Indicadores de Estado de Voto y Botones de Acción -->
            <div class="mt-4 pt-3 border-top">
                <h5 class="fw-bold text-dark mb-3">
                    <i class="fa-solid fa-check-to-slot text-success me-2"></i> Estado de Votaciones
                </h5>

                <div class="row g-3">
                    <!-- Elección Representante -->
                    <div class="col-12">
                        <div class="card h-100 border rounded-3 shadow-sm <?= $yaVotoRepresentante ? 'border-success-subtle bg-success-subtle bg-opacity-10' : ($votacionAbierta ? 'border-warning-subtle bg-warning-subtle bg-opacity-10' : 'bg-light') ?>">
                            <div class="card-body d-flex flex-column justify-content-between p-3">
                                <div>
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="rounded-3 bg-success text-white d-flex align-items-center justify-content-center flex-shrink-0" style="width: 36px; height: 36px;">
                                                <i class="fa-solid fa-user-tie"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0 fw-bold text-dark">Representante</h6>
                                                <small class="text-muted">Aprendices SENA</small>
                                            </div>
                                        </div>
                                        <?php if ($yaVotoRepresentante): ?>
                                            <span class="badge bg-success">
                                                <i class="fa-solid fa-circle-check me-1"></i> Ya votó
                                            </span>
                                        <?php elseif ($votacionAbierta): ?>
                                            <span class="badge bg-warning text-dark">
                                                <i class="fa-solid fa-clock me-1"></i> Pendiente
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">
                                                <i class="fa-solid fa-lock me-1"></i> Cerrada
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <p class="text-secondary small mb-3">
                                        <?php if ($yaVotoRepresentante): ?>
                                            Su voto para la elección de Representante ha sido registrado exitosamente.
                                        <?php elseif ($votacionAbierta): ?>
                                            La votación para Representante está abierta. Haga clic en el botón para ejercer su voto.
                                        <?php else: ?>
                                            El periodo de votación para Representante se encuentra actualmente cerrado.
                                        <?php endif; ?>
                                    </p>
                                </div>

                                <div>
                                    <?php if (!$yaVotoRepresentante && $votacionAbierta): ?>
                                        <a href="home.php?pg=1203" class="btn btn-success w-100 py-2 fw-semibold shadow-sm">
                                            <i class="fa-solid fa-check-to-slot me-1"></i> Votar Representante
                                        </a>
                                    <?php elseif ($yaVotoRepresentante): ?>
                                        <button class="btn btn-outline-success w-100 py-2" disabled>
                                            <i class="fa-solid fa-check-double me-1"></i> Voto Registrado
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-secondary w-100 py-2" disabled>
                                            <i class="fa-solid fa-lock me-1"></i> Votación Cerrada
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>