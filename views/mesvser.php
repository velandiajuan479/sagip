<?php
require_once 'controllers/mescser.php';

// Safe variable initialization
$dat = $dat ?? [];
$evidenciasPorSoporte = $evidenciasPorSoporte ?? [];
$dtFalla = $dtFalla ?? [];
$dtCargo = $dtCargo ?? [];
$dtAmbientes = $dtAmbientes ?? [];
$dtOne = $dtOne ?? [];
$pg = $pg ?? '2201';
$icono = $icono ?? 'fa-solid fa-headset';

$nomUsuarioSesion = $_SESSION['nomusu'] ?? '';
$usuarioActualNom = !empty($usuarioActual['nomusu']) ? $usuarioActual['nomusu'] : $nomUsuarioSesion;

$totalSoportes = is_array($dat) ? count($dat) : 0;
$soportesFinalizados = 0;
$soportesConEvidencia = 0;
$ultimoSoporte = null;

if (!empty($dat)) {
    foreach ($dat as $soporte) {
        if (!empty($soporte['fecserfin'])) {
            $soportesFinalizados++;
        }
        $evidenciasSoporte = $evidenciasPorSoporte[$soporte['idsop']] ?? [];
        if (!empty($soporte['evisop']) || !empty($evidenciasSoporte)) {
            $soportesConEvidencia++;
        }
        if ($ultimoSoporte === null || ($soporte['idsop'] ?? 0) > ($ultimoSoporte['idsop'] ?? 0)) {
            $ultimoSoporte = $soporte;
        }
    }
}

$soportesEnProceso = $totalSoportes - $soportesFinalizados;

// Parse initial edit data
$ambienteEditar = '';
$desserEditar = !empty($dtOne[0]['desser']) ? $dtOne[0]['desser'] : '';
if (!empty($desserEditar) && preg_match('/\[Ambiente:\s*([^\]]+)\]/i', $desserEditar, $mEdit)) {
    $ambienteEditar = trim($mEdit[1]);
    $desserEditar = trim(str_replace($mEdit[0], '', $desserEditar));
}
?>

<div class="conte">
    <!-- Header Title -->
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-2">
        <div>
            <?= titulo2("<i class='" . htmlspecialchars($icono, ENT_QUOTES, 'UTF-8') . " me-2 text-primary'></i> Servicio de Soporte Mesa de Ayuda", 1); ?>
            <p class="text-muted small mb-0 mt-1">Registra tus solicitudes de soporte técnico y gestiona el seguimiento de tus requerimientos.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill">
                <i class="fa-solid fa-user me-1"></i> Solicitante: <strong><?= htmlspecialchars($usuarioActualNom, ENT_QUOTES, 'UTF-8'); ?></strong>
            </span>

        </div>
    </div>

    <!-- Summary KPIs Section -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold text-muted small">Total Soportes</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold fs-2 text-dark"><?= $totalSoportes; ?></span>
                            <span class="small text-muted">registros</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box bg-primary text-white">
                        <i class="fa-solid fa-ticket fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="fw-semibold text-primary d-inline-flex align-items-center gap-1 small">
                        <i class="fa-solid fa-layer-group"></i> Total General
                    </span>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold text-warning small">En Proceso</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold fs-2 text-dark"><?= $soportesEnProceso; ?></span>
                            <span class="small text-muted">pendientes</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box bg-warning text-dark">
                        <i class="fa-solid fa-clock-rotate-left fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="fw-semibold text-warning d-inline-flex align-items-center gap-2 small">
                        <span class="pulse-dot"></span> En Atención
                    </span>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold text-success small">Finalizados</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold fs-2 text-dark"><?= $soportesFinalizados; ?></span>
                            <span class="small text-muted">resueltos</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box bg-success text-white">
                        <i class="fa-solid fa-circle-check fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="fw-semibold text-success d-inline-flex align-items-center gap-2 small">
                        <span class="pulse-dot"></span> Solucionados
                    </span>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold text-info small">Con Evidencia</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold fs-2 text-dark"><?= $soportesConEvidencia; ?></span>
                            <span class="small text-muted">adjuntos</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box bg-info text-white">
                        <i class="fa-solid fa-camera fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="fw-semibold text-info d-inline-flex align-items-center gap-1 small">
                        <i class="fa-solid fa-paperclip"></i> Con Imagen/PDF
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Form Section: Submit Support Request -->
    <div class="inser mb-4" id="formulario-soporte">
        <h2>
            <i class="fa-solid fa-pen-to-square me-2"></i><?= !empty($dtOne) ? "Editar Solicitud # " . htmlspecialchars((string)($dtOne[0]['idsop'] ?? ''), ENT_QUOTES, 'UTF-8') : "Formulario de Solicitud Mesa de Ayuda"; ?>
        </h2>
        <div class="p-3">
            <form id="frmins" action="home.php?pg=<?= htmlspecialchars((string)$pg, ENT_QUOTES, 'UTF-8'); ?>" method="POST" enctype="multipart/form-data">
                <div class="row g-3 g-md-4">
                    <!-- Evidence Photo Upload Field -->
                    <div class="col-12 col-md-4">
                        <label for="fotos-soporte" class="form-label fw-semibold text-secondary small text-uppercase">
                            <i class="fa-solid fa-camera me-1 text-primary"></i> Foto Evidencia:
                        </label>
                        <div class="border border-2 border-dashed rounded-4 p-3 text-center bg-light h-100 d-flex flex-column justify-content-center align-items-center">
                            <i class="fa-solid fa-cloud-arrow-up text-primary fs-1 mb-2"></i>
                            <p class="small text-muted mb-2">Adjunte una o varias imágenes de evidencia (JPG, PNG, WebP, PDF):</p>
                            <input type="file" class="form-control form-control-sm shadow-none" name="fotos[]" id="fotos-soporte" multiple accept="image/png, image/jpeg, image/jpg, image/webp, application/pdf">
                            <div id="previewContainer" class="d-flex flex-wrap gap-2 mt-2 justify-content-center"></div>
                        </div>
                    </div>

                    <!-- Fields Section -->
                    <div class="col-12 col-md-8">
                        <div class="row g-3">
                            <!-- Requester Name -->
                            <div class="col-12 col-md-6">
                                <label for="nomper" class="form-label fw-semibold text-secondary small text-uppercase">
                                    <i class="fa-solid fa-user me-1 text-primary"></i> Nombre de quien solicita:
                                </label>
                                <input type="text" name="nomper" id="nomper" maxlength="70" class="form-control shadow-none"
                                    value="<?= !empty($dtOne[0]['nomper']) ? htmlspecialchars($dtOne[0]['nomper'], ENT_QUOTES, 'UTF-8') : htmlspecialchars($usuarioActualNom, ENT_QUOTES, 'UTF-8'); ?>"
                                    placeholder="Nombre del usuario solicitante" autocomplete="off" required>
                                <div id="autocomplete-error" class="text-danger small d-none mt-1"></div>
                            </div>

                            <!-- Ambiente / Ubicación del Soporte -->
                            <div class="col-12 col-md-6">
                                <label for="ambiente" class="form-label fw-semibold text-secondary small text-uppercase">
                                    <i class="fa-solid fa-location-dot me-1 text-primary"></i> Ambiente / Ubicación del soporte:
                                </label>
                                <input list="list-ambientes" type="text" name="ambiente" id="ambiente" class="form-control shadow-none"
                                    value="<?= htmlspecialchars($ambienteEditar, ENT_QUOTES, 'UTF-8'); ?>"
                                    placeholder="Ej. Ambiente 204, Lab Sistemas, Piso 2..." autocomplete="off" required>
                                <datalist id="list-ambientes">
                                    <?php if (!empty($dtAmbientes)): ?>
                                        <?php foreach ($dtAmbientes as $amb): ?>
                                            <option value="<?= htmlspecialchars($amb['nomaul'] . (!empty($amb['piso']) ? ' (Piso ' . $amb['piso'] . ')' : ''), ENT_QUOTES, 'UTF-8'); ?>"></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </datalist>
                            </div>

                            <!-- Fault Type -->
                            <div class="col-12 col-md-6">
                                <label for="falrep" class="form-label fw-semibold text-secondary small text-uppercase">
                                    <i class="fa-solid fa-triangle-exclamation me-1 text-warning"></i> Falla reportada:
                                </label>
                                <select name="falrep" id="falrep" class="form-select shadow-none" required>
                                    <option value="" disabled <?= empty($dtOne[0]['falrep']) ? 'selected' : ''; ?>>Seleccione tipo de falla...</option>
                                    <?php if (!empty($dtFalla)): ?>
                                        <?php foreach ($dtFalla as $dtFll): ?>
                                            <option value="<?= htmlspecialchars((string)$dtFll['idval'], ENT_QUOTES, 'UTF-8'); ?>" <?= (!empty($dtOne) && isset($dtOne[0]['falrep']) && $dtOne[0]['falrep'] == $dtFll['idval']) ? 'selected' : ''; ?>>
                                                <?= htmlspecialchars($dtFll['nomval'], ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <!-- Cargo -->
                            <div class="col-12 col-md-6">
                                <label for="carper" class="form-label fw-semibold text-secondary small text-uppercase">
                                    <i class="fa-solid fa-briefcase me-1 text-primary"></i> Cargo / Unidad:
                                </label>
                                <select name="carper" id="carper" class="form-select shadow-none">
                                    <option value="" disabled <?= (empty($dtOne[0]['carper']) && empty($cargoActualId)) ? 'selected' : ''; ?>>Seleccione cargo de la persona...</option>
                                    <?php if (!empty($dtCargo)): ?>
                                        <?php foreach ($dtCargo as $dtCr): ?>
                                            <?php
                                                $selectedCargo = false;
                                                if (!empty($dtOne) && isset($dtOne[0]['carper']) && $dtOne[0]['carper'] == $dtCr['idval']) {
                                                    $selectedCargo = true;
                                                } elseif (empty($dtOne) && !empty($cargoActualId) && $cargoActualId == $dtCr['idval']) {
                                                    $selectedCargo = true;
                                                }
                                            ?>
                                            <option value="<?= htmlspecialchars((string)$dtCr['idval'], ENT_QUOTES, 'UTF-8'); ?>" <?= $selectedCargo ? 'selected' : ''; ?>>
                                                <?= htmlspecialchars($dtCr['nomval'], ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <!-- Description -->
                            <div class="col-12">
                                <label for="desser" class="form-label fw-semibold text-secondary small text-uppercase">
                                    <i class="fa-solid fa-align-left me-1 text-primary"></i> Descripción del problema o servicio:
                                </label>
                                <textarea name="desser" id="desser" rows="3" class="form-control shadow-none" placeholder="Describa el inconveniente técnico o solicitud requerida..." required><?= htmlspecialchars($desserEditar, ENT_QUOTES, 'UTF-8'); ?></textarea>
                            </div>

                            <!-- Form Actions -->
                            <div class="col-12 d-flex align-items-center gap-2 mt-3">
                                <button class="btn btn-success shadow-sm px-4" type="submit">
                                    <i class="fa-solid fa-paper-plane me-1"></i> <?= !empty($dtOne) ? 'Actualizar Solicitud' : 'Enviar Solicitud'; ?>
                                </button>
                                <?php if (!empty($dtOne)): ?>
                                    <a href="home.php?pg=<?= htmlspecialchars((string)$pg, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-outline-secondary shadow-sm">
                                        <i class="fa-solid fa-xmark me-1"></i> Cancelar
                                    </a>
                                <?php endif; ?>
                                <input type="hidden" name="ope" value="save">
                                <input type="hidden" name="idsop" id="idsop" value="<?= (!empty($dtOne) && !empty($dtOne[0]['idsop'])) ? htmlspecialchars((string)$dtOne[0]['idsop'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- History / Control Table of User's Support Requests -->
    <div class="card shadow-sm border-0 rounded-4 mb-4">
        <div class="card-header bg-transparent border-0 pt-3 px-3 px-md-4 pb-0 d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <h5 class="fw-bold text-dark mb-0">
                    <i class="fa-solid fa-list-check me-2 text-primary"></i>Control de Soportes Realizados
                </h5>
                <p class="text-muted small mb-0 mt-1">Historial y estado de tus solicitudes de soporte registradas.</p>
            </div>
            <?php if ($ultimoSoporte): ?>
                <span class="badge bg-light text-dark border px-3 py-2">
                    Último registro: #<?= htmlspecialchars((string)($ultimoSoporte['idsop'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                </span>
            <?php endif; ?>
        </div>

        <div class="card-body p-2 p-md-4">
            <div class="table-responsive w-100 rounded-3">
                <table id="example" class="table table-hover align-middle w-100 mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="py-3 px-3">Servicio / Solicitante</th>
                            <th class="py-3 px-2">Detalles del Soporte</th>
                            <th class="py-3 px-2 text-center">Foto Evidencia</th>
                            <th class="py-3 px-2 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($dat)): ?>
                            <?php foreach ($dat as $dt): ?>
                                <?php 
                                    $estadoNombre = $dt["nom_ultimo_estado"] ?? '';
                                    $isFinalizado = (!empty($dt['fecserfin']) || mb_stripos($estadoNombre, 'solucion') !== false || mb_stripos($estadoNombre, 'finaliz') !== false);

                                    // Parse Ambiente from desser if formatted
                                    $descRaw = (string)($dt['desser'] ?? '');
                                    $ambienteFila = '';
                                    if (preg_match('/\[Ambiente:\s*([^\]]+)\]/i', $descRaw, $matchAmb)) {
                                        $ambienteFila = trim($matchAmb[1]);
                                        $descRaw = trim(str_replace($matchAmb[0], '', $descRaw));
                                    }
                                ?>
                                <tr>
                                    <!-- Service / Requester Info -->
                                    <td class="px-3 py-3">
                                        <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                            <span class="badge bg-dark">#<?= htmlspecialchars((string)($dt["idsop"] ?? ''), ENT_QUOTES, 'UTF-8'); ?></span>
                                            <strong class="text-dark fs-6">
                                                <i class="fa-solid fa-user me-1 text-primary"></i><?= htmlspecialchars((string)(!empty($dt['nomper']) ? $dt['nomper'] : ($dt['nomusu'] ?? $usuarioActualNom)), ENT_QUOTES, 'UTF-8'); ?>
                                            </strong>
                                        </div>

                                        <?php if (!empty($dt['nom_tecnico'])): ?>
                                            <div class="small text-secondary mb-1">
                                                <i class="fa-solid fa-headset me-1 text-info"></i><strong>Técnico:</strong> <?= htmlspecialchars((string)$dt['nom_tecnico'], ENT_QUOTES, 'UTF-8'); ?>
                                            </div>
                                        <?php endif; ?>

                                        <div class="small text-muted mb-2">
                                            <i class="fa-regular fa-calendar-days me-1 text-secondary"></i>Inicio: <?= htmlspecialchars((string)($dt['fecserini'] ?? ''), ENT_QUOTES, 'UTF-8'); ?><br>
                                            <i class="fa-solid fa-flag-checkered me-1 text-secondary"></i>Fin: <?= !empty($dt['fecserfin']) ? htmlspecialchars((string)$dt['fecserfin'], ENT_QUOTES, 'UTF-8') : '<span class="badge bg-warning text-dark">En proceso</span>'; ?>
                                        </div>

                                        <div>
                                            <span class="badge <?= $isFinalizado ? 'bg-success' : 'bg-info text-dark'; ?> px-2 py-1">
                                                <i class="<?= $isFinalizado ? 'fa-solid fa-check me-1' : 'fa-solid fa-spinner fa-spin-pulse me-1'; ?>"></i>
                                                <?= htmlspecialchars((string)($dt["nom_ultimo_estado"] ?? 'En proceso'), ENT_QUOTES, 'UTF-8'); ?>
                                            </span>
                                        </div>
                                    </td>

                                    <!-- Support Details -->
                                    <td class="px-2 py-3">
                                        <span class="badge bg-success bg-opacity-10 text-success mb-1 me-1">
                                            <i class="fa-solid fa-triangle-exclamation me-1"></i>Falla: <?= htmlspecialchars((string)($dt['nom_falrep'] ?? 'General'), ENT_QUOTES, 'UTF-8'); ?>
                                        </span>

                                        <?php if (!empty($ambienteFila)): ?>
                                            <span class="badge bg-primary bg-opacity-10 text-primary mb-1 me-1">
                                                <i class="fa-solid fa-location-dot me-1"></i>Ambiente: <?= htmlspecialchars($ambienteFila, ENT_QUOTES, 'UTF-8'); ?>
                                            </span>
                                        <?php endif; ?>

                                        <?php if (!empty($dt['nom_carper'])): ?>
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary mb-1">
                                                <i class="fa-solid fa-briefcase me-1"></i>Cargo: <?= htmlspecialchars((string)$dt['nom_carper'], ENT_QUOTES, 'UTF-8'); ?>
                                            </span>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($descRaw)): ?>
                                            <div class="text-secondary small text-break mt-1 p-2 bg-light rounded-2 border-start border-3 border-primary">
                                                <?= nl2br(htmlspecialchars($descRaw, ENT_QUOTES, 'UTF-8')); ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Photo Evidence -->
                                    <td class="text-center align-middle px-2 py-3">
                                        <?php 
                                            $resolverRuta = function ($rOrig) {
                                                $r = trim((string)$rOrig);
                                                if ($r === '') return null;
                                                $base = basename($r);
                                                if (file_exists('img/fevi/' . $base)) return 'img/fevi/' . $base;
                                                if (file_exists($r)) return $r;
                                                return null;
                                            };
                                            $fotosSoporte = [];
                                            // 1. Evidencias de soporte_evidencia
                                            $evidenciasActuales = $evidenciasPorSoporte[$dt['idsop']] ?? [];
                                            if (!empty($evidenciasActuales)) {
                                                foreach ($evidenciasActuales as $evidencia) {
                                                    $resR = $resolverRuta($evidencia['ruta'] ?? '');
                                                    if ($resR && !in_array($resR, $fotosSoporte)) {
                                                        $fotosSoporte[] = $resR;
                                                    }
                                                }
                                            }
                                            // 2. Evidencia inicial directa
                                            if (!empty($dt['evisop'])) {
                                                $rutasIni = explode(',', (string)$dt['evisop']);
                                                foreach ($rutasIni as $rIni) {
                                                    $resR = $resolverRuta($rIni);
                                                    if ($resR && !in_array($resR, $fotosSoporte)) {
                                                        $fotosSoporte[] = $resR;
                                                    }
                                                }
                                            }
                                            $totalFotos = count($fotosSoporte);
                                        ?>
                                        <?php if ($totalFotos === 0): ?>
                                            <span class="text-muted small d-block">
                                                <i class="fa-solid fa-image-slash me-1 opacity-50"></i>Sin foto
                                            </span>
                                        <?php elseif ($totalFotos === 1): ?>
                                            <img src="<?= htmlspecialchars($fotosSoporte[0], ENT_QUOTES, 'UTF-8'); ?>" 
                                                 alt="Evidencia" 
                                                 class="img-thumbnail rounded-3 shadow-sm mes-trigger-modal" 
                                                 style="width: 75px; height: 50px; object-fit: cover; cursor: pointer;"
                                                 data-src="<?= htmlspecialchars($fotosSoporte[0], ENT_QUOTES, 'UTF-8'); ?>"
                                                 title="Clic para ampliar foto de evidencia">
                                        <?php else: ?>
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-warning text-dark border-warning-subtle bg-light d-inline-flex flex-column align-items-center justify-content-center p-1 rounded-3 shadow-sm mes-trigger-album position-relative" 
                                                    style="width: 75px; height: 50px;"
                                                    data-fotos='<?= htmlspecialchars(json_encode(array_values($fotosSoporte)), ENT_QUOTES, 'UTF-8'); ?>'
                                                    data-idsop="<?= htmlspecialchars((string)($dt['idsop'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                                                    title="Ver carpeta de <?= $totalFotos; ?> evidencias">
                                                <i class="fa-solid fa-folder-open text-warning fs-5"></i>
                                                <span class="badge bg-dark text-white rounded-pill px-2 py-0 mt-1" style="font-size: 0.65rem;">
                                                    <i class="fa-solid fa-images me-1"></i><?= $totalFotos; ?> fotos
                                                </span>
                                            </button>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Action Buttons -->
                                    <td class="text-center text-nowrap align-middle px-2 py-3">
                                        <a href="home.php?pg=2202&idsop=<?= htmlspecialchars((string)($dt['idsop'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" 
                                           class="btn btn-sm btn-outline-primary shadow-sm me-1" 
                                           title="Ver Seguimiento">
                                            <i class="fa-solid fa-magnifying-glass"></i>
                                        </a>

                                        <?php if (isset($_SESSION['idper']) && ($_SESSION['idper'] == 38)): ?>
                                            <a href="home.php?pg=<?= htmlspecialchars((string)$pg, ENT_QUOTES, 'UTF-8'); ?>&idsop=<?= htmlspecialchars((string)($dt['idsop'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>&ope=del" 
                                               class="btn btn-sm btn-outline-danger shadow-sm" 
                                               title="Eliminar" 
                                               onclick="return eli(event, this);">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">
                                    <i class="fa-solid fa-circle-info me-1 text-primary"></i> No hay registros de soporte de mesa de ayuda disponibles.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Nativo de Bootstrap para Previsualización de Foto Evidencia -->
<div class="modal fade" id="mesModalEvidencia" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-dark border-0 shadow-lg text-white">
            <div class="modal-header border-0 pb-1 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <h6 class="modal-title text-white mb-0">
                        <i class="fa-solid fa-image me-2 text-primary" id="mesModalIcono"></i>
                        <span id="mesModalTitulo">Foto Evidencia de Soporte</span>
                    </h6>
                    <span id="mesModalCounter" class="badge bg-secondary rounded-pill ms-2 small d-none"></span>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-2 p-md-3 text-center position-relative">
                <button type="button" id="btnPrevEvidencia" class="btn btn-dark bg-opacity-75 text-white position-absolute start-0 top-50 translate-middle-y ms-2 ms-md-4 rounded-circle border-0 shadow d-inline-flex align-items-center justify-content-center d-none" style="width: 44px; height: 44px; z-index: 10;" title="Foto anterior">
                    <i class="fa-solid fa-chevron-left fs-5"></i>
                </button>
                <div class="d-flex align-items-center justify-content-center" style="min-height: 250px; max-height: 65vh;">
                    <img id="mesModalImg" src="" alt="Evidencia Ampliada" class="img-fluid rounded-3 shadow" style="max-height: 65vh; max-width: 100%; object-fit: contain;">
                </div>
                <button type="button" id="btnNextEvidencia" class="btn btn-dark bg-opacity-75 text-white position-absolute end-0 top-50 translate-middle-y me-2 me-md-4 rounded-circle border-0 shadow d-inline-flex align-items-center justify-content-center d-none" style="width: 44px; height: 44px; z-index: 10;" title="Siguiente foto">
                    <i class="fa-solid fa-chevron-right fs-5"></i>
                </button>
                <div id="mesModalThumbnails" class="d-flex justify-content-center align-items-center gap-2 mt-3 p-2 bg-black bg-opacity-50 rounded-3 overflow-auto" style="display: none; max-width: 100%;"></div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function () {
    // Autocompletado del nombre del solicitante
    if ($('#nomper').length > 0 && typeof $.fn.autocomplete !== 'undefined') {
        $('#nomper').autocomplete({
            source: function (request, response) {
                $.ajax({
                    url: 'controllers/mescser.php',
                    data: { _autocomplete: 1, term: request.term },
                    dataType: 'json',
                    type: 'GET',
                    beforeSend: function () {
                        $('#nomper').addClass('ui-autocomplete-loading');
                        $('#autocomplete-error').addClass('d-none').text('');
                    },
                    complete: function () {
                        $('#nomper').removeClass('ui-autocomplete-loading');
                    },
                    success: function (data) {
                        if (data && (data.status === 'success' || data.success === true)) {
                            var items = $.map(data.data, function (item) {
                                return {
                                    label: item.nomusu,
                                    value: item.nomusu,
                                    idusu: item.idusu,
                                    idcargo: item.idcargo
                                };
                            });
                            response(items.length > 0 ? items : [{ label: 'No se encontraron coincidencias', value: '', disabled: true }]);
                        } else {
                            $('#autocomplete-error').removeClass('d-none').text('Error en la búsqueda');
                            response([]);
                        }
                    },
                    error: function () {
                        $('#autocomplete-error').removeClass('d-none').text('Error al cargar sugerencias');
                        response([]);
                    }
                });
            },
            minLength: 2,
            delay: 300,
            select: function (event, ui) {
                if (!ui.item.disabled) {
                    $('#nomper').val(ui.item.value);
                    if (ui.item.idcargo && $('#carper option[value="' + ui.item.idcargo + '"]').length > 0) {
                        $('#carper').val(ui.item.idcargo);
                    }
                }
                return false;
            }
        });
    }

    // Modal para previsualizar evidencia en la tabla
    let currentGallery = [];
    let currentIndex = 0;

    const modalElement = document.getElementById('mesModalEvidencia');
    const modalImg = document.getElementById('mesModalImg');
    const modalCounter = document.getElementById('mesModalCounter');
    const modalTitulo = document.getElementById('mesModalTitulo');
    const modalIcono = document.getElementById('mesModalIcono');
    const btnPrev = document.getElementById('btnPrevEvidencia');
    const btnNext = document.getElementById('btnNextEvidencia');
    const thumbnailsContainer = document.getElementById('mesModalThumbnails');

    function updateModalView() {
        if (!currentGallery.length) return;
        modalImg.src = currentGallery[currentIndex];
        if (modalCounter) {
            if (currentGallery.length > 1) {
                modalCounter.textContent = (currentIndex + 1) + ' / ' + currentGallery.length;
                modalCounter.classList.remove('d-none');
            } else {
                modalCounter.classList.add('d-none');
            }
        }
        if (btnPrev && btnNext) {
            if (currentGallery.length > 1) {
                btnPrev.classList.remove('d-none');
                btnNext.classList.remove('d-none');
            } else {
                btnPrev.classList.add('d-none');
                btnNext.classList.add('d-none');
            }
        }
        if (thumbnailsContainer) {
            if (currentGallery.length > 1) {
                thumbnailsContainer.style.display = 'flex';
                const thumbs = thumbnailsContainer.querySelectorAll('img');
                thumbs.forEach((th, idx) => {
                    if (idx === currentIndex) {
                        th.classList.add('border-warning');
                        th.style.borderWidth = '2px';
                        th.style.opacity = '1';
                    } else {
                        th.classList.remove('border-warning');
                        th.style.borderWidth = '1px';
                        th.style.opacity = '0.55';
                    }
                });
            } else {
                thumbnailsContainer.innerHTML = '';
                thumbnailsContainer.style.display = 'none';
            }
        }
    }

    function buildThumbnails() {
        if (!thumbnailsContainer) return;
        thumbnailsContainer.innerHTML = '';
        if (currentGallery.length <= 1) return;

        currentGallery.forEach((fotoSrc, idx) => {
            const thumb = document.createElement('img');
            thumb.src = fotoSrc;
            thumb.className = 'img-thumbnail rounded border';
            thumb.style.width = '55px';
            thumb.style.height = '40px';
            thumb.style.objectFit = 'cover';
            thumb.style.cursor = 'pointer';
            thumb.style.transition = 'all 0.2s ease';
            thumb.title = 'Ver foto ' + (idx + 1);

            thumb.addEventListener('click', function () {
                currentIndex = idx;
                updateModalView();
            });

            thumbnailsContainer.appendChild(thumb);
        });
    }

    function openModal() {
        if (modalElement && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
            modal.show();
        } else if (window.jQuery && $('#mesModalEvidencia').modal) {
            $('#mesModalEvidencia').modal('show');
        }
    }

    if (btnPrev) {
        btnPrev.addEventListener('click', function(e) {
            e.stopPropagation();
            if (currentGallery.length > 1) {
                currentIndex = (currentIndex - 1 + currentGallery.length) % currentGallery.length;
                updateModalView();
            }
        });
    }

    if (btnNext) {
        btnNext.addEventListener('click', function(e) {
            e.stopPropagation();
            if (currentGallery.length > 1) {
                currentIndex = (currentIndex + 1) % currentGallery.length;
                updateModalView();
            }
        });
    }

    // Clic en foto individual
    document.querySelectorAll('.mes-trigger-modal').forEach(img => {
        img.addEventListener('click', function () {
            const src = this.getAttribute('data-src') || this.src;
            currentGallery = [src];
            currentIndex = 0;

            if (modalTitulo) modalTitulo.textContent = 'Foto Evidencia de Soporte';
            if (modalIcono) modalIcono.className = 'fa-solid fa-image me-2 text-primary';

            updateModalView();
            openModal();
        });
    });

    // Clic en carpetica con múltiples fotos
    document.querySelectorAll('.mes-trigger-album').forEach(btn => {
        btn.addEventListener('click', function () {
            try {
                const fotosData = this.getAttribute('data-fotos');
                currentGallery = JSON.parse(fotosData) || [];
            } catch (err) {
                currentGallery = [];
            }

            if (!currentGallery.length) return;

            const idSop = this.getAttribute('data-idsop') || '';
            currentIndex = 0;

            if (modalTitulo) modalTitulo.textContent = 'Carpeta de Evidencias - Caso #' + idSop;
            if (modalIcono) modalIcono.className = 'fa-solid fa-folder-open me-2 text-warning';

            buildThumbnails();
            updateModalView();
            openModal();
        });
    });

    document.addEventListener('keydown', function(e) {
        if (modalElement && modalElement.classList.contains('show') && currentGallery.length > 1) {
            if (e.key === 'ArrowLeft') {
                currentIndex = (currentIndex - 1 + currentGallery.length) % currentGallery.length;
                updateModalView();
            } else if (e.key === 'ArrowRight') {
                currentIndex = (currentIndex + 1) % currentGallery.length;
                updateModalView();
            }
        }
    });

    // Función global de confirmación para eliminar
    window.eli = function (event, link) {
        event.preventDefault();
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: '¿Está seguro que desea eliminar este registro?',
                text: 'Verifique antes de continuar',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                confirmButtonColor: '#d33',
                cancelButtonText: 'Cancelar'
            }).then(function (result) {
                if (result.isConfirmed) {
                    window.location.href = link.href;
                }
            });
        } else {
            if (confirm('¿Está seguro de que desea eliminar este registro de soporte?')) {
                window.location.href = link.href;
            }
        }
        return false;
    };
});
</script>