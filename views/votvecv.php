<?php require_once 'controllers/votcecv.php'; ?>

<div class="conte">
    <?php echo titulo2("<i class='" . $icono . "'></i> Selección de Voceros", 2); ?>

    <?php if (isset($_REQUEST['idficfil']) && !empty($_REQUEST['idficfil'])): ?>
        <div class="alert alert-info py-2 px-3 d-flex align-items-center mb-3 rounded-3 shadow-sm border-0 bg-info-subtle text-info-emphasis">
            <i class="fas fa-circle-info fa-lg me-2 text-info"></i>
            <div>
                <strong>Regla de postulación:</strong> Puedes seleccionar un máximo de <strong>4 candidatos</strong> a vocero por ficha.
            </div>
        </div>
    <?php endif; ?>

    <!-- Dashboard Estadístico -->
    <div class="row g-3 mb-4 mt-1">
        <!-- Card 1: Cantidad de Fichas -->
        <div class="col-12 col-md-6">
            <div class="card border shadow-sm h-100 rounded-3">
                <div class="card-body d-flex align-items-center p-3">
                    <div class="rounded-3 bg-success-subtle text-success me-3 d-flex align-items-center justify-content-center w-[52px] h-[52px] min-w-[52px]">
                        <i class="fas fa-layer-group fa-2x"></i>
                    </div>
                    <div>
                        <span class="text-muted small text-uppercase fw-semibold d-block">Cantidad de Fichas</span>
                        <h3 class="mb-0 fw-bold text-dark"><?= number_format($totalFichas, 0, ',', '.'); ?></h3>
                        <small class="text-success"><i class="fas fa-check-circle me-1"></i>Fichas registradas en el sistema</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 2: Cantidad de Candidatos Vocero -->
        <div class="col-12 col-md-6">
            <div class="card border shadow-sm h-100 rounded-3">
                <div class="card-body d-flex align-items-center p-3">
                    <div class="rounded-3 bg-warning-subtle text-warning-emphasis me-3 d-flex align-items-center justify-content-center w-[52px] h-[52px] min-w-[52px]">
                        <i class="fas fa-check-to-slot fa-2x text-warning"></i>
                    </div>
                    <div>
                        <span class="text-muted small text-uppercase fw-semibold d-block">Candidatos a Vocero</span>
                        <?php if (isset($_REQUEST['idficfil']) && !empty($_REQUEST['idficfil'])): ?>
                            <h3 class="mb-0 fw-bold text-dark">
                                <?= (int)$conteoCandidatos; ?> <span class="fs-6 text-muted fw-normal">/ 4 en ficha <?= htmlspecialchars($_REQUEST['idficfil']); ?></span>
                            </h3>
                            <small class="<?= $conteoCandidatos >= 4 ? 'text-danger' : 'text-success'; ?> fw-semibold">
                                <i class="fas <?= $conteoCandidatos >= 4 ? 'fa-ban' : 'fa-user-check'; ?> me-1"></i>
                                <?= $conteoCandidatos >= 4 ? 'Límite alcanzado (4/4)' : (4 - $conteoCandidatos) . ' cupo(s) disponible(s)'; ?> 
                                <span class="text-muted fw-normal">(Total sistema: <?= number_format($totalCandidatosVocero, 0, ',', '.'); ?>)</span>
                            </small>
                        <?php else: ?>
                            <h3 class="mb-0 fw-bold text-dark"><?= number_format($totalCandidatosVocero, 0, ',', '.'); ?></h3>
                            <small class="text-muted"><i class="fas fa-user-check me-1 text-warning"></i>Candidatos registrados en todo el sistema</small>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filtro de Ficha con Búsqueda -->
<div class="card border shadow-sm rounded-3 mb-4">
    <div class="card-body p-3 p-md-4">
        <form id="formFicha" name="frm1" action="home.php?pg=<?= $pg; ?>" method="POST">
            <input type="hidden" name="pg" value="<?= $pg; ?>">
            <input type="hidden" name="idficfil" id="idficfil-hidden" value="<?= isset($_REQUEST['idficfil']) ? htmlspecialchars($_REQUEST['idficfil'], ENT_QUOTES, 'UTF-8') : ''; ?>">
            <div class="position-relative">
                <label for="buscar-ficha-input" class="form-label fw-semibold text-secondary mb-2 d-flex align-items-center small text-uppercase">
                    <i class="fas fa-search text-success me-2"></i> Buscar Ficha de Formación
                </label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="fas fa-id-card text-muted"></i>
                    </span>
                    <input type="text"
                           id="buscar-ficha-input"
                           class="form-control border-start-0"
                           placeholder="Escriba número de ficha o nombre del programa (ej: ADSO, 269...)..."
                           autocomplete="off">
                    <button class="btn btn-outline-secondary" type="button" id="btn-limpiar-busqueda" title="Limpiar búsqueda">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <!-- Menú flotante con resultados -->
                <div id="dropdown-fichas-resultados"
                     class="list-group position-absolute w-100 shadow mt-1 overflow-auto rounded-3 border top-100 start-0 hidden z-[1030] max-h-[280px]"></div>
            </div>
        </form>
    </div>
</div>

<?php if (isset($_REQUEST['idficfil']) && !empty($_REQUEST['idficfil'])): ?>
    <div class="card border shadow-sm rounded-3 mb-4">
        <div class="card-header bg-white py-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div class="d-flex align-items-center">
                <div class="rounded-circle bg-success-subtle text-success p-2 me-2 d-flex align-items-center justify-content-center w-[38px] h-[38px]">
                    <i class="fas fa-users"></i>
                </div>
                <div>
                    <h5 class="mb-0 fw-bold text-dark">Aprendices de la Ficha <?= htmlspecialchars($idficfil); ?></h5>
                    <small class="text-muted">Total: <?= count($dat ?? []); ?> aprendiz(ces) registrados</small>
                </div>
            </div>
            <div>
                <span class="badge <?= $conteoCandidatos >= 4 ? 'bg-danger' : 'bg-success'; ?> px-3 py-2 fs-6">
                    <i class="fas <?= $conteoCandidatos >= 4 ? 'fa-lock' : 'fa-user-check'; ?> me-1"></i>
                    Candidatos: <?= $conteoCandidatos; ?> / 4
                </span>
            </div>
        </div>
        <div class="card-body p-0">
            <?php if ($dat && count($dat) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-secondary">
                            <tr>
                                <th class="text-center w-[140px]">Acción</th>
                                <th class="text-center w-[100px]">Estado</th>
                                <th class="text-center w-[80px]">Foto</th>
                                <th>Aprendiz</th>
                                <th>No. Documento</th>
                                <th>Perfil / Cargo</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($dat as $d): 
                                $esCandidato = ($d['es_candidato'] > 0 || $d['idper'] == 13);
                            ?>
                                <tr class="<?= $esCandidato ? 'table-success bg-opacity-25' : ''; ?>">
                                    <td class="text-center align-middle position-static">
                                        <?php if ($esCandidato): ?>
                                            <a href="javascript:void(0)" role="button" 
                                               class="btn btn-sm btn-outline-danger d-inline-flex align-items-center justify-content-center position-static float-none my-0 mx-auto align-middle text-decoration-none"
                                               onclick="confirmarEliminarVocero(<?= $d['idusu']; ?>, '<?= addslashes(htmlspecialchars($d['nomusu'])); ?>')">
                                                <i class="fas fa-user-minus me-1"></i> Quitar
                                            </a>
                                        <?php else: ?>
                                            <?php if ($conteoCandidatos < 4): ?>
                                                <a href="javascript:void(0)" role="button" 
                                                   class="btn btn-sm btn-success d-inline-flex align-items-center justify-content-center text-white position-static float-none my-0 mx-auto align-middle text-decoration-none"
                                                   onclick="confirmarVocero(<?= $d['idusu']; ?>, '<?= addslashes(htmlspecialchars($d['nomusu'])); ?>')">
                                                    <i class="fas fa-user-plus me-1"></i> Seleccionar
                                                </a>
                                            <?php else: ?>
                                                <button type="button" 
                                                    class="btn btn-sm btn-secondary d-inline-flex align-items-center justify-content-center position-static float-none my-0 mx-auto align-middle" 
                                                    disabled title="Límite máximo de 4 candidatos alcanzado">
                                                    <i class="fas fa-ban me-1"></i> Cupo lleno
                                                </button>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center align-middle">
                                        <?php if ($d['actusu'] == 1): ?>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                                <i class="fas fa-circle-check me-1"></i>Activo
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                                                <i class="fas fa-circle-xmark me-1"></i>Inactivo
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center align-middle">
                                        <?php 
                                        $fotoSrc = (!empty($d['fotcan']) && file_exists($d['fotcan'])) ? $d['fotcan'] : 'img/user.jpg';
                                        ?>
                                        <img src="<?= $fotoSrc; ?>" alt="Foto" width="45" height="45" class="rounded-circle border shadow-sm object-cover">
                                    </td>
                                    <td class="align-middle">
                                        <span class="fw-bold text-dark d-block"><?= htmlspecialchars($d['nomusu']); ?></span>
                                        <?php if ($esCandidato): ?>
                                            <small class="text-success fw-semibold"><i class="fas fa-award me-1"></i>Postulado a vocero</small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="align-middle">
                                        <span class="badge bg-light text-dark border font-monospace px-2 py-1 text-[1.4rem] font-semibold tracking-[0.5px]">
                                            <?= htmlspecialchars($d['ndocusu']); ?>
                                        </span>
                                    </td>
                                    <td class="align-middle">
                                        <?php if ($esCandidato): ?>
                                            <span class="badge bg-success px-2 py-1">
                                                <i class="fas fa-star me-1"></i>Candidato a Vocero
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary-subtle text-secondary border px-2 py-1">
                                                <?= htmlspecialchars($d['nomper']); ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="p-4 text-center text-muted">
                    <i class="fas fa-info-circle fa-2x text-secondary mb-2 d-block"></i>
                    No se encontraron aprendices registrados en esta ficha.
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>