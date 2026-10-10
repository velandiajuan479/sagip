<?php require_once 'controllers/mescsspo.php'; ?>

<div class="conte">
    <?php echo titulo2("<i class='" . $icono . "'></i> Seguimiento Soporte", 0); ?>

    <!-- Botón de retorno limpio posicionado a la izquierda -->
    <div class="mb-3">
        <a href="home.php?pg=2202" class="btn btn-outline-secondary px-3 py-1" title="Volver a la bandeja general de soportes">
            <i class="fa-solid fa-arrow-left me-1"></i>Volver a la bandeja
        </a>
    </div>

    <?php if ($datOne): ?>
        <?php
            $estadoId = (int) ($datOne['ultimo_estado_id'] ?? 0);
            $badgeClass = 'bg-secondary';
            if ($estadoId === 1073) { $badgeClass = 'bg-info text-dark'; }
            elseif ($estadoId === 1075) { $badgeClass = 'bg-warning text-dark'; }
            elseif ($estadoId === 1074) { $badgeClass = 'bg-success'; }
        ?>
 
        <!-- Grid de 2 Columnas principales -->
        <div class="row g-4 mb-4">
            <!-- Columna Izquierda: Información Completa del Ticket -->
            <div class="col-12 col-lg-6">
                <div class="card bg-white border shadow-sm h-100">
                    <div class="card-header bg-light py-3 d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold text-dark">
                            <i class="fa-solid fa-info-circle me-2 text-primary"></i>Ficha del Ticket #<?= htmlspecialchars($idsop); ?>
                        </h6>
                        <span class="badge <?= $badgeClass; ?> px-2 py-1">
                            <?= htmlspecialchars($datOne['nom_ultimo_estado'] ?: 'Sin registro'); ?>
                        </span>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-12 col-sm-6">
                                <label class="text-secondary small fw-semibold d-block">Solicitante</label>
                                <span class="fw-bold text-dark fs-6 d-block"><?= htmlspecialchars($datOne['nomper'] ?: $datOne['nom_solicitante']); ?></span>
                                <small class="text-muted"><?= htmlspecialchars($datOne['nom_carper'] ?: 'Sin cargo'); ?></small>
                            </div>
                            <div class="col-12 col-sm-6">
                                <label class="text-secondary small fw-semibold d-block">Técnico Asignado</label>
                                <span id="spanNombreTecnico" class="fw-bold text-dark fs-6 d-block">
                                    <?php if (!empty($datOne['nom_tecnico'])): ?>
                                        <i class="fa-solid fa-user me-1 text-secondary"></i><?= htmlspecialchars($datOne['nom_tecnico']); ?>
                                    <?php else: ?>
                                        <span class="badge bg-light text-muted border">Sin asignar</span>
                                    <?php endif; ?>
                                </span>
                            </div>

                            <div class="col-12">
                                <hr class="my-1 text-muted opacity-25">
                            </div>

                            <div class="col-12 col-sm-6">
                                <label class="text-secondary small fw-semibold d-block">Falla Reportada</label>
                                <span class="text-dark fw-semibold"><?= htmlspecialchars($datOne['nom_falrep'] ?: 'N/A'); ?></span>
                            </div>
                            <div class="col-12 col-sm-6">
                                <label class="text-secondary small fw-semibold d-block">Fecha de Inicio</label>
                                <span class="text-muted small"><?= htmlspecialchars($datOne['fecserini']); ?></span>
                            </div>

                            <?php if (!empty($datOne['fecserfin'])): ?>
                                <div class="col-12 col-sm-6">
                                    <label class="text-secondary small fw-semibold d-block">Fecha de Cierre</label>
                                    <span class="text-success small fw-semibold"><?= htmlspecialchars($datOne['fecserfin']); ?></span>
                                </div>
                            <?php endif; ?>

                            <div class="col-12">
                                <label class="text-secondary small fw-semibold d-block">Descripción del Problema</label>
                                <div class="p-3 bg-light rounded border text-dark">
                                    <?= nl2br(htmlspecialchars($datOne['desser'])); ?>
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="text-secondary small fw-semibold d-block mb-2">Evidencia Inicial Adjunta</label>
                                <?php
                                    $fotosIniciales = [];
                                    if (!empty($datOne['evisop'])) {
                                        $rutasIni = explode(',', (string)$datOne['evisop']);
                                        foreach ($rutasIni as $rIni) {
                                            $rIni = trim($rIni);
                                            if ($rIni !== '') {
                                                $baseIni = basename($rIni);
                                                $rFoto = null;
                                                if (file_exists('img/fevi/' . $baseIni)) {
                                                    $rFoto = 'img/fevi/' . $baseIni;
                                                } elseif (file_exists($rIni)) {
                                                    $rFoto = $rIni;
                                                }
                                                if ($rFoto && !in_array($rFoto, $fotosIniciales)) {
                                                    $fotosIniciales[] = $rFoto;
                                                }
                                            }
                                        }
                                    }
                                    if (class_exists('conexion') && !empty($idsop)) {
                                        try {
                                            $cnxSpo = (new conexion())->get_conexion();
                                            $stmtSpoEvi = $cnxSpo->prepare("SELECT ruta FROM soporte_evidencia WHERE idsop = :idsop AND ruta IS NOT NULL AND ruta != '' ORDER BY idevi ASC");
                                            $stmtSpoEvi->bindValue(':idsop', (int)$idsop, PDO::PARAM_INT);
                                            $stmtSpoEvi->execute();
                                            while ($rSpo = $stmtSpoEvi->fetch(PDO::FETCH_ASSOC)) {
                                                $rPath = trim((string)$rSpo['ruta']);
                                                if ($rPath !== '') {
                                                    $basePath = basename($rPath);
                                                    $rFoto = null;
                                                    if (file_exists('img/fevi/' . $basePath)) {
                                                        $rFoto = 'img/fevi/' . $basePath;
                                                    } elseif (file_exists($rPath)) {
                                                        $rFoto = $rPath;
                                                    }
                                                    if ($rFoto && !in_array($rFoto, $fotosIniciales)) {
                                                        $fotosIniciales[] = $rFoto;
                                                    }
                                                }
                                            }
                                        } catch (Exception $e) {}
                                    }
                                    $totalFotosIni = count($fotosIniciales);
                                ?>
                                <?php if ($totalFotosIni === 0): ?>
                                    <span class="badge bg-light text-muted border p-2">Sin evidencia fotográfica inicial</span>
                                <?php elseif ($totalFotosIni === 1): ?>
                                    <div class="border rounded p-2 bg-light d-inline-block">
                                        <img src="<?= htmlspecialchars($fotosIniciales[0]); ?>" 
                                             alt="Evidencia inicial" 
                                             class="img-thumbnail rounded-3 shadow-sm mes-trigger-modal" 
                                             style="max-height: 120px; max-width: 180px; object-fit: cover; cursor: pointer;"
                                             data-src="<?= htmlspecialchars($fotosIniciales[0]); ?>"
                                             title="Clic para ampliar foto de evidencia">
                                    </div>
                                <?php else: ?>
                                    <div class="border rounded p-2 bg-light d-inline-block">
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-warning text-dark border-warning-subtle bg-white d-inline-flex flex-column align-items-center justify-content-center p-2 rounded-3 shadow-sm mes-trigger-album" 
                                                style="min-width: 100px; height: 60px;"
                                                data-fotos='<?= htmlspecialchars(json_encode(array_values($fotosIniciales)), ENT_QUOTES, 'UTF-8'); ?>'
                                                data-idsop="<?= htmlspecialchars((string)$idsop, ENT_QUOTES, 'UTF-8'); ?>"
                                                title="Ver carpeta de <?= $totalFotosIni; ?> evidencias">
                                            <i class="fa-solid fa-folder-open text-warning fs-4 mb-1"></i>
                                            <span class="badge bg-dark text-white rounded-pill px-2 py-0" style="font-size: 0.7rem;">
                                                <i class="fa-solid fa-images me-1"></i><?= $totalFotosIni; ?> fotos
                                            </span>
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Columna Derecha: Panel Operativo Unificado -->
            <div class="col-12 col-lg-6">
                <div class="card bg-white border shadow-sm h-100">
                    <div class="card-header bg-light py-3">
                        <h6 class="mb-0 fw-bold text-dark">
                            <i class="fa-solid fa-cogs me-2 text-primary"></i>Panel Operativo y Asignación
                        </h6>
                    </div>
                    <div class="card-body p-4">
                        <!-- Sección de Asignación / Autoasignación -->
                        <div class="card bg-light border mb-4">
                            <div class="card-body p-3">
                                <label class="fw-semibold text-secondary small mb-2 d-block">
                                    <i class="fa-solid fa-user-check me-1"></i>Gestión de Asignación de Técnico
                                </label>
                                <?php if (isset($_SESSION['idper']) && (int)$_SESSION['idper'] === 38): ?>
                                    <!-- Vista Administrador: Selector de técnicos -->
                                    <div class="input-group">
                                        <label class="input-group-text bg-white" for="selectTecnicoAdmin">Asignar a:</label>
                                        <select id="selectTecnicoAdmin" class="form-select bg-white">
                                            <option value="">-- Seleccionar Técnico --</option>
                                            <?php foreach ($dtSelsop as $sp): ?>
                                                <option value="<?= htmlspecialchars($sp['idusu']); ?>" <?= ($datOne['idusu2'] == $sp['idusu']) ? 'selected' : ''; ?>>
                                                    <?= htmlspecialchars($sp['nomusu']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="form-text small text-muted mt-1">Al cambiar la opción se guardará la asignación automáticamente.</div>
                                <?php elseif (isset($_SESSION['idper']) && (int)$_SESSION['idper'] === 27): ?>
                                    <!-- Vista Técnico: Autoasignación -->
                                    <div class="d-flex align-items-center justify-content-between">
                                        <span class="small text-dark">
                                            <?= ($datOne['idusu2'] == $_SESSION['idusu']) ? '<span class="text-success fw-bold"><i class="fa-solid fa-check me-1"></i>Estás asignado a este caso</span>' : 'Caso pendiente de asignación.'; ?>
                                        </span>
                                        <button type="button" id="btnAutoasignar" class="btn btn-outline-primary px-3 py-1">
                                            <i class="fa-solid fa-user-plus me-1"></i>Autoasignarme este caso
                                        </button>
                                    </div>
                                <?php else: ?>
                                    <div class="small text-muted">No dispone de permisos para modificar la asignación.</div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Sección Formulario de Seguimiento -->
                        <?php if (!$estadoFinalizado && isset($_SESSION['idper']) && in_array((int)$_SESSION['idper'], [27, 38])): ?>
                            <form action="home.php?pg=2204&idsop=<?= htmlspecialchars($idsop); ?>" method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="ope" value="save">
                                <input type="hidden" name="idsop" value="<?= htmlspecialchars($idsop); ?>">
                                <input type="hidden" name="idusu" value="<?= htmlspecialchars($_SESSION['idusu']); ?>">

                                <div class="row g-3">
                                    <div class="col-12">
                                        <label for="detest" class="form-label fw-semibold text-secondary small">Nuevo Estado</label>
                                        <select name="detest" id="detest" class="form-select" required>
                                            <?php foreach ($dtEst as $st): ?>
                                                <option value="<?= htmlspecialchars($st['idval']); ?>" <?= ($datOne['ultimo_estado_id'] == $st['idval']) ? 'selected' : ''; ?>>
                                                    <?= htmlspecialchars($st['nomval']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="col-12">
                                        <label for="detcom" class="form-label fw-semibold text-secondary small">Comentario / Avance</label>
                                        <textarea name="detcom" id="detcom" rows="4" class="form-control" placeholder="Escriba los detalles de las acciones realizadas..." required></textarea>
                                    </div>

                                    <div class="col-12">
                                        <label for="foto" class="form-label fw-semibold text-secondary small">Foto Evidencia (Opcional)</label>
                                        <input type="file" name="foto" id="foto" class="form-control" accept="image/png, image/jpeg">
                                    </div>

                                    <div class="col-12 pt-2">
                                        <button type="submit" class="btn btn-primary w-100 fw-semibold py-2">
                                            <i class="fa-solid fa-paper-plane me-1"></i>Registrar Seguimiento
                                        </button>
                                    </div>
                                </div>
                            </form>
                        <?php elseif ($estadoFinalizado): ?>
                            <div class="alert alert-secondary border mb-0" role="alert">
                                <i class="fa-solid fa-lock me-2"></i>Este soporte ha sido <strong>Finalizado</strong>. Ya no se permiten nuevos seguimientos.
                            </div>
                        <?php else: ?>
                            <div class="alert alert-light border mb-0 text-muted small" role="alert">
                                Solo los técnicos y administradores pueden agregar notas de seguimiento.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla / Bitácora Histórica de Seguimientos -->
        <div class="card bg-white border shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold text-secondary">
                    <i class="fa-solid fa-history me-2"></i>Historial de Seguimientos y Avances
                </h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 w-100">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Fecha</th>
                                <th>Usuario / Técnico</th>
                                <th>Estado Registrado</th>
                                <th>Comentarios</th>
                                <th class="text-center">Evidencia</th>
                                <?php if (isset($_SESSION['idper']) && (int)$_SESSION['idper'] === 38): ?>
                                    <th class="text-center pe-3">Acción</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($dat)): ?>
                                <?php foreach ($dat as $dt): ?>
                                    <?php
                                        $stId = (int) $dt['detest'];
                                        $stBadge = 'bg-secondary';
                                        if ($stId === 1073) { $stBadge = 'bg-info text-dark'; }
                                        elseif ($stId === 1075) { $stBadge = 'bg-warning text-dark'; }
                                        elseif ($stId === 1074) { $stBadge = 'bg-success'; }
                                    ?>
                                    <tr>
                                        <td class="ps-3 text-muted small whitespace-nowrap">
                                            <?= htmlspecialchars($dt['fecseg']); ?>
                                        </td>
                                        <td>
                                            <?php
                                                $nomTecnicoSeg = !empty($datOne['nom_tecnico']) 
                                                    ? $datOne['nom_tecnico'] 
                                                    : (!empty($dt['nom_tecnico']) ? $dt['nom_tecnico'] : 'Sin asignar');
                                            ?>
                                            <span class="fw-semibold text-dark"><?= htmlspecialchars($nomTecnicoSeg); ?></span>
                                        </td>
                                        <td>
                                            <span class="badge <?= $stBadge; ?> px-2 py-1">
                                                <?= htmlspecialchars($dt['nom_detest']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="text-dark small"><?= nl2br(htmlspecialchars($dt['detcom'])); ?></span>
                                        </td>
                                        <td class="text-center align-middle">
                                            <?php
                                                $fotosDet = [];
                                                if (!empty($dt['detevi'])) {
                                                    $rArr = explode(',', (string)$dt['detevi']);
                                                    foreach ($rArr as $rItem) {
                                                        $rItem = trim($rItem);
                                                        if ($rItem !== '') {
                                                            $baseDet = basename($rItem);
                                                            $rFoto = null;
                                                            if (file_exists('img/fevi/' . $baseDet)) {
                                                                $rFoto = 'img/fevi/' . $baseDet;
                                                            } elseif (file_exists($rItem)) {
                                                                $rFoto = $rItem;
                                                            }
                                                            if ($rFoto && !in_array($rFoto, $fotosDet)) {
                                                                $fotosDet[] = $rFoto;
                                                            }
                                                        }
                                                    }
                                                }
                                                $cntDet = count($fotosDet);
                                            ?>
                                            <?php if ($cntDet === 0): ?>
                                                <span class="text-muted small">-</span>
                                            <?php elseif ($cntDet === 1): ?>
                                                <img src="<?= htmlspecialchars($fotosDet[0]); ?>" 
                                                     alt="Evidencia avance" 
                                                     class="img-thumbnail rounded-3 shadow-sm mes-trigger-modal"
                                                     style="width: 75px; height: 50px; object-fit: cover; cursor: pointer;"
                                                     data-src="<?= htmlspecialchars($fotosDet[0]); ?>"
                                                     title="Clic para ampliar foto de evidencia">
                                            <?php else: ?>
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-warning text-dark border-warning-subtle bg-light d-inline-flex flex-column align-items-center justify-content-center p-1 rounded-3 shadow-sm mes-trigger-album position-relative" 
                                                        style="width: 75px; height: 50px;"
                                                        data-fotos='<?= htmlspecialchars(json_encode(array_values($fotosDet)), ENT_QUOTES, 'UTF-8'); ?>'
                                                        data-idsop="<?= htmlspecialchars((string)$idsop, ENT_QUOTES, 'UTF-8'); ?>"
                                                        title="Ver carpeta de <?= $cntDet; ?> evidencias">
                                                    <i class="fa-solid fa-folder-open text-warning fs-5"></i>
                                                    <span class="badge bg-dark text-white rounded-pill px-2 py-0 mt-1" style="font-size: 0.65rem;">
                                                        <i class="fa-solid fa-images me-1"></i><?= $cntDet; ?> fotos
                                                    </span>
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                        <?php if (isset($_SESSION['idper']) && (int)$_SESSION['idper'] === 38): ?>
                                            <td class="text-center pe-3 align-middle">
                                                <a href="home.php?pg=2204&idsop=<?= htmlspecialchars($idsop); ?>&iddet=<?= htmlspecialchars($dt['iddet']); ?>&ope=del" class="btn btn-sm btn-outline-danger shadow-sm" title="Eliminar registro" onclick="return confirm('¿Desea eliminar este avance de seguimiento?');">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </a>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="<?= (isset($_SESSION['idper']) && (int)$_SESSION['idper'] === 38) ? '6' : '5'; ?>" class="text-center py-4 text-muted">
                                        No hay registros de seguimiento para este soporte.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    <?php else: ?>
        <div class="alert alert-warning border" role="alert">
            <i class="fa-solid fa-exclamation-triangle me-2"></i>El ticket de soporte especificado no fue encontrado o no existe.
        </div>
    <?php endif; ?>
</div>

<!-- Modal Nativo de Bootstrap para Previsualización de Evidencias (Individual y Carpeta de Fotos) -->
<div class="modal fade" id="mesModalEvidencia" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-dark border-0 shadow-lg text-white">
            <div class="modal-header border-0 pb-1 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <h6 class="modal-title text-white mb-0">
                        <i class="fa-solid fa-image me-2 text-primary" id="mesModalIcono"></i>
                        <span id="mesModalTitulo">Foto Evidencia de Seguimiento</span>
                    </h6>
                    <span id="mesModalContador" class="badge bg-secondary rounded-pill px-2 py-1 small" style="display: none;">1 / 1</span>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-2 p-md-3 text-center position-relative">
                <button type="button" id="mesBtnPrevFoto" 
                        class="btn btn-dark bg-opacity-75 text-white position-absolute start-0 top-50 translate-middle-y ms-2 ms-md-4 rounded-circle border-0 shadow d-inline-flex align-items-center justify-content-center" 
                        style="width: 44px; height: 44px; z-index: 10; display: none;" 
                        title="Foto anterior">
                    <i class="fa-solid fa-chevron-left fs-5"></i>
                </button>
                <div class="d-flex align-items-center justify-content-center" style="min-height: 250px; max-height: 65vh;">
                    <img id="mesModalImg" src="" alt="Evidencia Ampliada" class="img-fluid rounded-3 shadow" style="max-height: 65vh; max-width: 100%; object-fit: contain;">
                </div>
                <button type="button" id="mesBtnNextFoto" 
                        class="btn btn-dark bg-opacity-75 text-white position-absolute end-0 top-50 translate-middle-y me-2 me-md-4 rounded-circle border-0 shadow d-inline-flex align-items-center justify-content-center" 
                        style="width: 44px; height: 44px; z-index: 10; display: none;" 
                        title="Siguiente foto">
                    <i class="fa-solid fa-chevron-right fs-5"></i>
                </button>
                <div id="mesModalThumbnails" class="d-flex justify-content-center align-items-center gap-2 mt-3 p-2 bg-black bg-opacity-50 rounded-3 overflow-auto" style="display: none; max-width: 100%;"></div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const btnAutoasignar = document.getElementById("btnAutoasignar");
    const selectTecnicoAdmin = document.getElementById("selectTecnicoAdmin");

    function processAsignacion(idusu2Val) {
        const formData = new FormData();
        formData.append("ajax", "asignar_tecnico");
        formData.append("idsop", "<?= (int)$idsop; ?>");
        formData.append("idusu2", idusu2Val);

        fetch("controllers/mescsspo.php", {
            method: "POST",
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Asignación actualizada',
                        text: 'Técnico asignado: ' + data.nomtecnico,
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    alert('Técnico asignado: ' + data.nomtecnico);
                    location.reload();
                }
            } else {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'No se pudo actualizar la asignación.'
                    });
                } else {
                    alert(data.message || 'No se pudo actualizar la asignación.');
                }
            }
        })
        .catch(err => {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Error de comunicación',
                    text: 'Ocurrió un problema al procesar la asignación.'
                });
            } else {
                alert('Ocurrió un problema al procesar la asignación.');
            }
        });
    }

    if (btnAutoasignar) {
        btnAutoasignar.addEventListener("click", function () {
            processAsignacion("<?= (int)$_SESSION['idusu']; ?>");
        });
    }

    if (selectTecnicoAdmin) {
        selectTecnicoAdmin.addEventListener("change", function () {
            processAsignacion(this.value);
        });
    }

    const modalElement = document.getElementById('mesModalEvidencia');
    const modalImg = document.getElementById('mesModalImg');
    const modalTitulo = document.getElementById('mesModalTitulo');
    const modalIcono = document.getElementById('mesModalIcono');
    const modalContador = document.getElementById('mesModalContador');
    const btnPrev = document.getElementById('mesBtnPrevFoto');
    const btnNext = document.getElementById('mesBtnNextFoto');
    const thumbnailsContainer = document.getElementById('mesModalThumbnails');

    let fotosActuales = [];
    let fotoIndexActual = 0;

    function abrirModal() {
        if (modalElement && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
            modal.show();
        } else if (window.jQuery && $('#mesModalEvidencia').modal) {
            $('#mesModalEvidencia').modal('show');
        }
    }

    function renderFotoActual() {
        if (!fotosActuales.length) return;
        const src = fotosActuales[fotoIndexActual];
        if (modalImg) modalImg.src = src;

        if (fotosActuales.length > 1) {
            if (modalContador) {
                modalContador.textContent = (fotoIndexActual + 1) + ' / ' + fotosActuales.length;
                modalContador.style.display = 'inline-block';
            }
            if (btnPrev) btnPrev.style.display = 'inline-flex';
            if (btnNext) btnNext.style.display = 'inline-flex';
            if (thumbnailsContainer) {
                thumbnailsContainer.style.display = 'flex';
                const thumbs = thumbnailsContainer.querySelectorAll('img');
                thumbs.forEach((th, idx) => {
                    if (idx === fotoIndexActual) {
                        th.classList.add('border-warning');
                        th.style.borderWidth = '2px';
                        th.style.opacity = '1';
                    } else {
                        th.classList.remove('border-warning');
                        th.style.borderWidth = '1px';
                        th.style.opacity = '0.55';
                    }
                });
            }
        } else {
            if (modalContador) modalContador.style.display = 'none';
            if (btnPrev) btnPrev.style.display = 'none';
            if (btnNext) btnNext.style.display = 'none';
            if (thumbnailsContainer) {
                thumbnailsContainer.innerHTML = '';
                thumbnailsContainer.style.display = 'none';
            }
        }
    }

    function construirMiniaturas() {
        if (!thumbnailsContainer) return;
        thumbnailsContainer.innerHTML = '';
        if (fotosActuales.length <= 1) return;

        fotosActuales.forEach((fotoSrc, idx) => {
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
                fotoIndexActual = idx;
                renderFotoActual();
            });

            thumbnailsContainer.appendChild(thumb);
        });
    }

    // Clic en foto individual
    document.querySelectorAll('.mes-trigger-modal').forEach(img => {
        img.addEventListener('click', function () {
            const src = this.getAttribute('data-src') || this.src;
            fotosActuales = [src];
            fotoIndexActual = 0;

            if (modalTitulo) modalTitulo.textContent = 'Foto Evidencia de Seguimiento';
            if (modalIcono) modalIcono.className = 'fa-solid fa-image me-2 text-primary';

            renderFotoActual();
            abrirModal();
        });
    });

    // Clic en carpetica con múltiples fotos
    document.querySelectorAll('.mes-trigger-album').forEach(btn => {
        btn.addEventListener('click', function () {
            try {
                const fotosData = this.getAttribute('data-fotos');
                fotosActuales = JSON.parse(fotosData) || [];
            } catch (err) {
                fotosActuales = [];
            }

            if (!fotosActuales.length) return;

            const idSop = this.getAttribute('data-idsop') || '';
            fotoIndexActual = 0;

            if (modalTitulo) modalTitulo.textContent = 'Carpeta de Evidencias - Caso #' + idSop;
            if (modalIcono) modalIcono.className = 'fa-solid fa-folder-open me-2 text-warning';

            construirMiniaturas();
            renderFotoActual();
            abrirModal();
        });
    });

    if (btnPrev) {
        btnPrev.addEventListener('click', function (e) {
            e.stopPropagation();
            if (fotosActuales.length > 1) {
                fotoIndexActual = (fotoIndexActual - 1 + fotosActuales.length) % fotosActuales.length;
                renderFotoActual();
            }
        });
    }

    if (btnNext) {
        btnNext.addEventListener('click', function (e) {
            e.stopPropagation();
            if (fotosActuales.length > 1) {
                fotoIndexActual = (fotoIndexActual + 1) % fotosActuales.length;
                renderFotoActual();
            }
        });
    }

    document.addEventListener('keydown', function (e) {
        if (!modalElement || !modalElement.classList.contains('show') || fotosActuales.length <= 1) return;
        if (e.key === 'ArrowLeft') {
            fotoIndexActual = (fotoIndexActual - 1 + fotosActuales.length) % fotosActuales.length;
            renderFotoActual();
        } else if (e.key === 'ArrowRight') {
            fotoIndexActual = (fotoIndexActual + 1) % fotosActuales.length;
            renderFotoActual();
        }
    });
});
</script>