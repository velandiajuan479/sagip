<?php 
require_once 'controllers/concdocg.php'; 
$pg = isset($pg) ? $pg : (isset($_REQUEST['pg']) ? $_REQUEST['pg'] : 2419);
?>

<div class="conte container-fluid px-2 px-md-4 py-3">
    <?php echo titulo2("<i class='fa-solid fa-file-contract'></i> Gestión de Documentos por Aspirante"); ?>


    <!-- Estadísticas del Header: Aspirantes y Cobertura Documental (solo perfiles administrativos) -->
    <?php if (!empty($esAdminAsp)): ?>
    <div class="card shadow-sm border-0 mb-4 rounded-3">
        <div class="card-body p-3 p-md-4">
            <div class="row g-3 text-center">
                <div class="col-12 col-sm-6 col-lg-4">
                    <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                        <div class="small text-secondary fw-bold text-uppercase"><i class="fa-solid fa-users me-1"></i> Aspirantes Registrados</div>
                        <div class="fs-2 fw-bold text-success" id="st-asp-total"><?= (int)$statsAsp['total'] ?></div>
                        <div class="small text-muted">Con perfil Candidato / Aprendiz</div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-lg-4">
                    <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                        <div class="small text-secondary fw-bold text-uppercase"><i class="fa-solid fa-file-circle-check me-1"></i> Documentos por Aspirante</div>
                        <div class="fs-2 fw-bold text-primary" id="st-asp-prom"><?= $statsAsp['promedio'] ?></div>
                        <div class="small text-muted">Promedio de documentos subidos por aspirante</div>
                    </div>
                </div>
                <div class="col-12 col-lg-4">
                    <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                        <div class="small text-secondary fw-bold text-uppercase"><i class="fa-solid fa-check-double me-1"></i> Documentación Completa</div>
                        <div class="fs-2 fw-bold <?= ($statsAsp['faltan'] > 0 ? 'text-warning' : 'text-success') ?>" id="st-asp-comp"><?= (int)$statsAsp['completos'] ?> de <?= (int)$statsAsp['total'] ?></div>
                        <div class="small text-muted">Cumplen el mínimo requerido (Extranjeros: 8 · Nacionales: 6)</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Formulario de Búsqueda -->
    <div class="card shadow-sm border-0 mb-4 rounded-3">
        <div class="card-body p-3 p-md-4">
            <form id="frmbuscar" action="home.php?pg=<?=$pg;?>" method="POST">
                <div class="row align-items-end">
                    <div class="form-group col-12 col-md-8 col-lg-6 mb-2 mb-md-0">
                        <label for="ndocasp" class="form-label fw-bold text-secondary">
                            <i class="fa-solid fa-id-card text-success"></i> Buscar por Número de Documento de Identidad
                        </label>
                        <div class="d-flex flex-column flex-sm-row gap-2">
                            <input type="text" name="ndocasp" id="ndocasp" class="form-control form-control-lg border-success w-100" placeholder="Ingrese el número de documento" value="<?=htmlspecialchars($ndocasp ?? '')?>" required autocomplete="off">
                            <button type="submit" name="opera" value="buscar" class="btn btn-success btn-lg px-4 flex-fill flex-sm-grow-0 text-nowrap">
                                <i class="fa-solid fa-magnifying-glass"></i> <span class="d-none d-sm-inline">Buscar</span>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Mensajes del Sistema -->
    <?php if ($msg): ?>
        <div class="alert alert-<?=$msg_type?> alert-dismissible fade show shadow-sm mb-4" role="alert">
            <i class="fa-solid <?=($msg_type == 'success' ? 'fa-circle-check' : ($msg_type == 'warning' ? 'fa-triangle-exclamation' : 'fa-circle-info'))?> me-2"></i>
            <?=$msg?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Información del Aspirante / Usuario Registrado -->
    <?php if ($usuario): ?>
    <div class="card shadow-sm border-0 mb-4 rounded-3 border-start border-4 border-success">
        <div class="card-header bg-light border-0 py-3">
            <h5 class="mb-0 text-success fw-bold">
                <i class="fa-solid fa-user-check me-2"></i> Información del Aspirante / Usuario
            </h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12 col-sm-6 col-md-4">
                    <span class="text-muted d-block small">Nombre completo</span>
                    <strong class="fs-6 text-dark"><?=htmlspecialchars($usuario['nomusu'])?></strong>
                </div>
                <div class="col-12 col-sm-6 col-md-4">
                    <span class="text-muted d-block small">Nº Documento</span>
                    <span class="badge bg-secondary fs-6"><?=htmlspecialchars($usuario['ndocusu'])?></span>
                </div>
                <div class="col-12 col-sm-6 col-md-4">
                    <span class="text-muted d-block small">Perfil</span>
                    <span class="badge bg-success fs-6"><?=htmlspecialchars($usuario['nomper'] ?? 'Aspirante')?></span>
                </div>
                <?php if (!empty($usuario['emausu'])): ?>
                <div class="col-12 col-sm-6 col-md-4">
                    <span class="text-muted d-block small">Correo electrónico</span>
                    <span class="text-dark"><?=htmlspecialchars($usuario['emausu'])?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Documentos Cargados -->
    <?php if ($usuario && $dat && count($dat) > 0): ?>
    <div class="card shadow-sm border-0 mb-4 rounded-3">
        <div class="card-header bg-white border-bottom py-3">
            <h5 class="mb-0 fw-bold text-dark">
                <i class="fa-solid fa-folder-open text-warning me-2"></i> Documentos Cargados (<?=count($dat)?>)
            </h5>
        </div>
        <div class="card-body p-0 p-md-3">
            
            <!-- VISTA ESCRITORIO Y TABLETS (d-none d-md-block) -->
            <div class="table-responsive d-none d-md-block">
                <table class="table table-hover align-middle mb-0" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th>Tipo de Documento</th>
                            <th>Archivo Original</th>
                            <th>Fecha de Carga</th>
                            <th>Estado</th>
                            <th>Observaciones</th>
                            <th class="text-center" style="min-width: 150px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($dat as $d): ?>
                        <tr>
                            <td>
                                <strong><?=htmlspecialchars(getTipoNombre($tiposDoc, $d['tipodoc']))?></strong>
                            </td>
                            <td>
                                <?php if ($d['rutarchivo'] && file_exists($d['rutarchivo'])): ?>
                                    <a href="<?=$d['rutarchivo']?>" target="_blank" class="text-decoration-none fw-bold text-primary">
                                        <i class="fa-solid fa-file-pdf text-danger me-1"></i> <?=htmlspecialchars($d['nomarchivo'])?>
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted"><i class="fa-solid fa-file me-1"></i> <?=htmlspecialchars($d['nomarchivo'])?> (archivo no hallado)</span>
                                <?php endif; ?>
                            </td>
                            <td><small class="text-muted"><?=date('d/m/Y H:i', strtotime($d['feccar']))?></small></td>
                            <td>
                                <?php if ($d['estdoc'] == 1): ?>
                                    <span class="badge bg-success"><i class="fa-solid fa-check me-1"></i> Vigente</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary"><i class="fa-solid fa-xmark me-1"></i> Inactivo</span>
                                <?php endif; ?>
                            </td>
                            <td><small class="text-secondary"><?=htmlspecialchars($d['obsdoc'] ?? '-')?></small></td>
                            <td class="text-center">
                                <div class="d-inline-flex gap-1">
                                    <?php if ($d['rutarchivo'] && file_exists($d['rutarchivo'])): ?>
                                        <a href="<?=$d['rutarchivo']?>" target="_blank" class="btn btn-sm btn-outline-primary" title="Ver / Descargar archivo">
                                            <i class="fa-solid fa-eye me-1"></i> Ver
                                        </a>
                                    <?php endif; ?>
                                    <a href="home.php?pg=<?=$pg?>&iddocg=<?=$d['iddocg']?>&ndocasp=<?=urlencode($ndocasp)?>&opera=eliminar" 
                                       class="btn btn-sm btn-danger text-white" 
                                       title="Eliminar este documento"
                                       onclick="return confirm('¿Está seguro de que desea eliminar este documento? Esta acción eliminará el archivo subido.');">
                                        <i class="fa-solid fa-trash-can me-1"></i> Borrar
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- VISTA MÓVIL PARA CELULARES (d-block d-md-none) -->
            <div class="d-block d-md-none p-2">
                <?php foreach ($dat as $d): ?>
                <div class="card mb-3 shadow-sm border border-light rounded-3">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h6 class="fw-bold text-success mb-0 me-2">
                                <?=htmlspecialchars(getTipoNombre($tiposDoc, $d['tipodoc']))?>
                            </h6>
                            <?php if ($d['estdoc'] == 1): ?>
                                <span class="badge bg-success"><i class="fa-solid fa-check me-1"></i> Vigente</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Inactivo</span>
                            <?php endif; ?>
                        </div>

                        <div class="mb-2">
                            <span class="text-muted small d-block">Archivo:</span>
                            <?php if ($d['rutarchivo'] && file_exists($d['rutarchivo'])): ?>
                                <a href="<?=$d['rutarchivo']?>" target="_blank" class="text-break fw-bold text-primary small">
                                    <i class="fa-solid fa-file-pdf text-danger me-1"></i> <?=htmlspecialchars($d['nomarchivo'])?>
                                </a>
                            <?php else: ?>
                                <span class="text-muted small"><?=htmlspecialchars($d['nomarchivo'])?> (no hallado)</span>
                            <?php endif; ?>
                        </div>

                        <div class="row g-1 mb-2 small text-muted">
                            <div class="col-12">
                                <i class="fa-regular fa-clock me-1"></i> <?=date('d/m/Y H:i', strtotime($d['feccar']))?>
                            </div>
                            <?php if (!empty($d['obsdoc'])): ?>
                            <div class="col-12">
                                <i class="fa-regular fa-comment me-1"></i> <?=htmlspecialchars($d['obsdoc'])?>
                            </div>
                            <?php endif; ?>
                        </div>

                        <div class="d-flex gap-2 justify-content-end mt-3 pt-2 border-top">
                            <?php if ($d['rutarchivo'] && file_exists($d['rutarchivo'])): ?>
                                <a href="<?=$d['rutarchivo']?>" target="_blank" class="btn btn-sm btn-outline-primary flex-fill text-center">
                                    <i class="fa-solid fa-eye me-1"></i> Ver
                                </a>
                            <?php endif; ?>
                            <a href="home.php?pg=<?=$pg?>&iddocg=<?=$d['iddocg']?>&ndocasp=<?=urlencode($ndocasp)?>&opera=eliminar" 
                               class="btn btn-sm btn-danger text-white flex-fill text-center" 
                               onclick="return confirm('¿Está seguro de que desea eliminar este documento? Esta acción eliminará el archivo subido.');">
                                <i class="fa-solid fa-trash-can me-1"></i> Borrar
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

        </div>
    </div>
    <?php elseif ($usuario && $ndocasp && $opera == "buscar"): ?>
        <div class="alert alert-info shadow-sm mb-4">
            <i class="fa-solid fa-info-circle me-2"></i> No se encontraron documentos cargados para el usuario: <strong><?=htmlspecialchars($usuario['nomusu'])?></strong> (Doc: <?=htmlspecialchars($ndocasp)?>).
        </div>
    <?php endif; ?>

    <!-- Dashboard de Documentos por Aspirante -->
    <?php if ($usuario): 
        // Indexar documentos cargados por tipo de documento
        $docsCargadosMap = [];
        if (!empty($dat)) {
            foreach ($dat as $docItem) {
                $docsCargadosMap[$docItem['tipodoc']] = $docItem;
            }
        }
        $totalDisponibles = count($tiposDocFlat);
        $totalSubidos = count($docsCargadosMap);
        $porcentaje = ($totalDisponibles > 0) ? round(($totalSubidos / $totalDisponibles) * 100) : 0;
    ?>
    <div class="card shadow-sm border-0 rounded-3 mb-4">
        <div class="card-header bg-success text-white p-3 p-md-4 d-flex flex-column flex-md-row justify-content-between align-items-stretch align-items-md-center gap-3">
            <div class="d-flex align-items-center gap-2">
                <i class="fa-solid fa-folder-tree fs-4 text-white-50"></i>
                <h5 class="mb-0 fw-bold fs-5 fs-md-4">Dashboard de Documentos del Aspirante</h5>
            </div>
            <div class="d-flex flex-column flex-sm-row align-items-stretch align-items-sm-center gap-2 w-100 w-md-auto">
                <div class="bg-white text-dark rounded-pill px-3 py-2 shadow-sm d-flex align-items-center justify-content-center gap-2 text-center text-sm-start flex-wrap flex-sm-nowrap" id="badge-contador" style="font-size: 0.9rem;">
                    <i class="fa-solid fa-clipboard-check text-success fs-6"></i>
                    <span class="fw-semibold">
                        <strong id="contador-subidos"><?=$totalSubidos?></strong> de <strong id="contador-total"><?=$totalDisponibles?></strong> documentos subidos
                    </span>
                    <span class="badge bg-success text-white rounded-pill px-2 py-1" id="contador-porcentaje"><?=$porcentaje?>%</span>
                </div>
                <a href="home.php?pg=<?=$pg?>" class="btn btn-outline-light px-3 py-2 rounded-pill d-flex align-items-center justify-content-center text-nowrap" title="Limpiar búsqueda">
                    <i class="fa-solid fa-rotate-left me-1"></i> Limpiar
                </a>
            </div>
        </div>

        <div class="card-body p-3 p-md-4">
            <!-- Barra de Progreso Global -->
            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="small fw-bold text-secondary">
                        <i class="fa-solid fa-bars-progress me-1"></i> Progreso General de Documentación
                    </span>
                    <span class="small fw-bold text-success" id="progreso-texto"><?=$totalSubidos?> / <?=$totalDisponibles?> Completados</span>
                </div>
                <div class="progress" style="height: 12px; background-color: #e9ecef; border-radius: 6px;">
                    <div id="progreso-barra" class="progress-bar progress-bar-striped progress-bar-animated bg-success" 
                         role="progressbar" style="width: <?=$porcentaje?>%;" 
                         aria-valuenow="<?=$porcentaje?>" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
            </div>

            <!-- Listado de Categorías y Documentos -->
            <div class="accordion" id="accordionDocDashboard">
                <?php 
                $catIndex = 0;
                foreach ($tiposDoc as $catKey => $cat): 
                    if (empty($cat['tipos'])) continue;
                    $catIndex++;
                    $catId = "cat_" . $catIndex;
                    
                    // Conteo por categoría
                    $catTotal = count($cat['tipos']);
                    $catSubidos = 0;
                    foreach ($cat['tipos'] as $t) {
                        if (isset($docsCargadosMap[$t['idval']])) {
                            $catSubidos++;
                        }
                    }
                ?>
                <details class="accordion-item border rounded-3 mb-3 shadow-sm overflow-hidden bg-white" <?=($catIndex == 1 ? 'open' : '')?>>
                    <summary class="accordion-header bg-light fw-bold text-dark p-3 border-0" style="cursor: pointer; list-style: none;">
                        <div class="d-flex w-100 justify-content-between align-items-center pe-2 flex-wrap gap-2">
                            <span>
                                <i class="fa-solid fa-folder me-2 text-warning"></i> <?=htmlspecialchars($cat['nombre'])?>
                            </span>
                            <span class="badge <?=($catSubidos == $catTotal ? 'bg-success' : ($catSubidos > 0 ? 'bg-warning text-dark' : 'bg-secondary'))?> rounded-pill" id="badge-cat-<?=$catKey?>">
                                <span class="cat-subidos"><?=$catSubidos?></span> / <?=$catTotal?> completos
                            </span>
                        </div>
                    </summary>
                    <div id="collapse_<?=$catId?>" class="accordion-body p-2 p-md-3 bg-white border-top">
                        <div class="row g-3">
                            <?php foreach ($cat['tipos'] as $tipo): 
                                $idVal = $tipo['idval'];
                                $isUploaded = isset($docsCargadosMap[$idVal]);
                                $docData = $isUploaded ? $docsCargadosMap[$idVal] : null;
                            ?>
                            <div class="col-12" id="doc-row-<?=$idVal?>">
                                <div class="card border <?=($isUploaded ? 'border-success bg-light' : 'border-light bg-white')?> shadow-sm rounded-3">
                                    <div class="card-body p-3">
                                        <div class="row align-items-center g-3">
                                            
                                            <!-- Estado e Icono -->
                                            <div class="col-auto">
                                                <span class="status-indicator d-inline-flex align-items-center justify-content-center rounded-circle <?=($isUploaded ? 'bg-success text-white' : 'bg-light text-muted border')?>" style="width: 44px; height: 44px; font-size: 1.25rem;">
                                                    <i class="fa-solid <?=($isUploaded ? 'fa-check' : 'fa-arrow-up-from-bracket')?>"></i>
                                                </span>
                                            </div>

                                            <!-- Info del Documento -->
                                            <div class="col">
                                                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                                    <h6 class="mb-0 fw-bold text-dark"><?=htmlspecialchars($tipo['nomval'])?></h6>
                                                    <span class="badge <?=($isUploaded ? 'bg-success' : 'bg-secondary')?> status-badge">
                                                        <?=($isUploaded ? '<i class="fa-solid fa-circle-check me-1"></i> Subido' : '<i class="fa-solid fa-clock me-1"></i> Pendiente')?>
                                                    </span>
                                                </div>

                                                <!-- Información si ya está cargado -->
                                                <div class="doc-meta small text-muted <?=($isUploaded ? '' : 'd-none')?>">
                                                    <span class="d-block d-lg-inline-block me-lg-3 mb-1 mb-lg-0 text-break">
                                                        <i class="fa-solid fa-file-pdf text-danger me-1"></i>
                                                        <a href="<?=($isUploaded && !empty($docData['rutarchivo']) ? htmlspecialchars($docData['rutarchivo']) : '#')?>" target="_blank" class="doc-link fw-bold text-primary text-decoration-none">
                                                            <span class="doc-filename"><?=($isUploaded && !empty($docData['nomarchivo']) ? htmlspecialchars($docData['nomarchivo']) : '')?></span>
                                                        </a>
                                                    </span>
                                                    <span class="d-block d-lg-inline-block me-lg-3 mb-1 mb-lg-0">
                                                        <i class="fa-regular fa-calendar-check me-1"></i>
                                                        <span class="doc-date"><?=($isUploaded && !empty($docData['feccar']) ? date('d/m/Y H:i', strtotime($docData['feccar'])) : '')?></span>
                                                    </span>
                                                    <span class="doc-obs-wrapper d-block d-lg-inline-block mb-1 mb-lg-0 <?=($isUploaded && !empty($docData['obsdoc']) ? '' : 'd-none')?>">
                                                        <i class="fa-regular fa-comment-dots me-1"></i>
                                                        <em class="doc-obs"><?=($isUploaded && !empty($docData['obsdoc']) ? htmlspecialchars($docData['obsdoc']) : '')?></em>
                                                    </span>
                                                </div>

                                                <!-- Aviso si está pendiente -->
                                                <div class="doc-empty-hint small text-secondary <?=($isUploaded ? 'd-none' : '')?>">
                                                    <i class="fa-solid fa-circle-exclamation text-warning me-1"></i> No se ha cargado este documento todavía.
                                                </div>

                                                <?php if ($idVal == 2106 || $idVal == 2107): ?>
                                                <div class="mt-2">
                                                    <a href="home.php?pg=2112<?=($usuario ? '&idusu='.(int)$usuario['idusu'] : '')?>" target="_blank" class="btn btn-sm btn-outline-success border-success text-success fw-bold text-decoration-none w-100 w-sm-auto">
                                                        <i class="fa-solid fa-wand-magic-sparkles me-1"></i> Generar / Consultar en Generador de Documentos
                                                    </a>
                                                </div>
                                                <?php endif; ?>

                                                <!-- Mensaje de subida/error en vivo -->
                                                <div class="upload-feedback small mt-1 d-none"></div>
                                            </div>

                                            <!-- Controles de Carga Directa -->
                                            <div class="col-12 col-lg-5">
                                                <form class="frm-doc-upload d-flex flex-column gap-2" method="POST" enctype="multipart/form-data">
                                                    <input type="hidden" name="tipodoc" value="<?=$idVal?>">
                                                    <input type="hidden" name="ndocasp" value="<?=htmlspecialchars($ndocasp)?>">
                                                    <input type="hidden" name="opera" value="guardar">

                                                    <div class="d-flex flex-column flex-sm-row gap-2">
                                                        <input type="file" name="archivo" class="form-control form-control-sm file-input flex-fill" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx" required>
                                                        <button type="submit" class="btn btn-success btn-upload btn-sm px-3 flex-fill flex-sm-grow-0 text-nowrap" title="<?=($isUploaded ? 'Actualizar / Reemplazar archivo' : 'Cargar archivo')?>">
                                                            <i class="fa-solid <?=($isUploaded ? 'fa-arrows-rotate' : 'fa-cloud-arrow-up')?> me-1"></i>
                                                            <span class="btn-text"><?=($isUploaded ? 'Reemplazar' : 'Subir')?></span>
                                                        </button>
                                                    </div>

                                                    <div class="input-group input-group-sm">
                                                        <span class="input-group-text bg-white text-muted"><i class="fa-solid fa-pencil"></i></span>
                                                        <input type="text" name="obsdoc" class="form-control obs-input" placeholder="Observaciones opcionales..." value="<?=($isUploaded && !empty($docData['obsdoc']) ? htmlspecialchars($docData['obsdoc']) : '')?>">
                                                    </div>
                                                </form>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </details>
                <?php endforeach; ?>
            </div>

        </div>
    </div>
    <?php else: ?>
    <div class="card shadow-sm border-0 rounded-3 text-center py-5">
        <div class="card-body">
            <i class="fa-solid fa-id-card-clip fa-4x text-success mb-3 opacity-75"></i>
            <h4 class="fw-bold text-dark">Buscar Aspirante por Documento</h4>
            <p class="text-muted max-w-500 mx-auto">Ingrese un número de documento de identidad previamente registrado en la tabla de usuarios para consultar, subir o eliminar sus documentos de aspirante.</p>
        </div>
    </div>
    <?php endif; ?>
</div>