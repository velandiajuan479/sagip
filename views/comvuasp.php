<?php
require_once dirname(__DIR__) . '/controllers/comcuasp.php';

$actionUrl = isset($pg) ? "home.php?pg=" . $pg : "vuasp.php";
$sep = (strpos($actionUrl, '?') !== false) ? '&' : '?';
$isStandalone = !isset($pg);

$limiteMaximo = 30;
$totalRegistrados = !empty($datAll) ? count($datAll) : 0;
$porcentajeOcupacion = ($limiteMaximo > 0) ? min(100, round(($totalRegistrados / $limiteMaximo) * 100)) : 0;
?>

<?php if ($isStandalone): ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuarios Aspirantes - SAGISENA</title>
</head>
<body>
<div>
<?php endif; ?>

<div class="conte">
    <?php 
    if (function_exists('titulo2')) {
        echo titulo2("<i class='fa-solid fa-users'></i> Usuarios Aspirantes (Individual)", 1);
    } else {
        echo '<h2>Usuarios Aspirantes (Individual)</h2>';
    }
    ?>

    <?= $mensaje ?? ''; ?>

    <!-- Panel de Indicadores / Ocupación -->
    <div class="row my-4 g-4">
        <!-- Total Registrados -->
        <div class="col-12 col-md-4">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #3e4a3d;">Total Registrados</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #0b1c30;"><?= $totalRegistrados; ?></span>
                            <span style="font-size: 13px; color: #3e4a3d;">aspirantes</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box" style="background-color: #b5dcfe; color: #3c627f;">
                        <i class="fa-solid fa-users fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 11px; color: #3c627f;">
                        <i class="fa-solid fa-arrow-trend-up" style="font-size: 12px;"></i> Registrados
                    </span>
                    <span style="font-size: 11px; color: #3e4a3d;">Capacidad: 100%</span>
                </div>
            </div>
        </div>

        <!-- Límite de Aspirantes -->
        <div class="col-12 col-md-4">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #006b29;">Límite de Cupos</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #006b29;"><?= $limiteMaximo; ?></span>
                            <span style="font-size: 13px; color: rgba(0, 107, 41, 0.8);">máximo</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box" style="background-color: #7afd88; color: #006b24;">
                        <i class="fa-solid fa-flag-checkered fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="fw-semibold d-inline-flex align-items-center gap-2" style="font-size: 11px; color: #006b29;">
                        <span class="pulse-dot"></span> Cupos Libres: <?= max(0, $limiteMaximo - $totalRegistrados); ?>
                    </span>
                    <span style="font-size: 11px; color: #3e4a3d;">Sistema OK</span>
                </div>
            </div>
        </div>

        <!-- Estado de Ocupación -->
        <div class="col-12 col-md-4">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #ba1a1a;">Ocupación</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #0b1c30;"><?= $porcentajeOcupacion; ?>%</span>
                            <span style="font-size: 13px; color: #3e4a3d;">utilizado</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box" style="background-color: #ffdad6; color: #ba1a1a;">
                        <i class="fa-solid fa-chart-pie fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="d-inline-flex align-items-center gap-1" style="font-size: 11px; color: #3e4a3d;">
                        <i class="fa-solid fa-clock-rotate-left" style="font-size: 12px;"></i> Estado actual
                    </span>
                    <span class="fw-semibold" style="font-size: 11px; color: #3c627f;"><?= ($totalRegistrados >= $limiteMaximo) ? 'Cupo lleno' : 'Disponible'; ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Formulario de Inserción / Edición -->
    <div class="inser">
        <form id="frmins" class="filter-toolbar-container" action="<?= $actionUrl; ?>" method="POST">
            <h2><i class="fa-solid fa-user-plus"></i> <?= !empty($datOne) ? 'Modificar Aspirante' : 'Nuevo Aspirante'; ?></h2>
            
            <?php if (!empty($datOne)): ?>
                <div class="alert alert-info py-2 mb-3">
                    <strong>Modificando datos del aspirante:</strong> <?= htmlspecialchars($datOne[0]['nombre'] ?? ''); ?>
                    <a href="<?= $actionUrl; ?>" class="float-end text-decoration-none">[X] Cancelar</a>
                </div>
            <?php endif; ?>

            <?php if (empty($datOne) && $totalRegistrados >= $limiteMaximo): ?>
                <div class="alert alert-warning py-3">
                    <i class="fa-solid fa-triangle-exclamation"></i> <strong>Límite de registros alcanzado:</strong> Se ha llegado al cupo máximo de <strong><?= $limiteMaximo; ?> aspirantes</strong> permitidos[cite: 1]. No es posible realizar nuevos registros en este momento, a menos que elimine algún registro existente[cite: 1].
                </div>
            <?php else: ?>
                <div class="row">
                    <div class="form-group col-md-6">
                        <label for="nombre">Nombre Completo</label>
                        <input type="text" class="form-control" name="nombre" id="nombre" required
                               placeholder="Ingrese nombre y apellidos"
                               value="<?= isset($datOne[0]['nombre']) ? htmlspecialchars($datOne[0]['nombre']) : ''; ?>">
                    </div>

                    <div class="form-group col-md-6">
                        <label for="tipoId">Tipo de Identificación</label>
                        <select class="form-control form-select" name="tipoId" id="tipoId" required>
                            <option value="">Seleccione...</option>
                            <?php 
                            $tipos = ['CC' => 'Cédula de Ciudadanía', 'TI' => 'Tarjeta de Identidad', 'CE' => 'Cédula de Extranjería', 'PA' => 'Pasaporte'];
                            $selTipo = $datOne[0]['tipoId'] ?? '';
                            foreach ($tipos as $val => $lbl): 
                            ?>
                                <option value="<?= $val; ?>" <?= ($selTipo === $val) ? 'selected' : ''; ?>><?= $lbl; ?> (<?= $val; ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group col-md-6">
                        <label for="documento">Número de Documento</label>
                        <input type="text" class="form-control" name="documento" id="documento" required
                               placeholder="Ej: 1020304050"
                               value="<?= isset($datOne[0]['documento']) ? htmlspecialchars($datOne[0]['documento']) : ''; ?>">
                    </div>

                    <div class="form-group col-md-6">
                        <label for="email">Correo Electrónico</label>
                        <input type="email" class="form-control" name="email" id="email" required
                               placeholder="correo@ejemplo.com"
                               value="<?= isset($datOne[0]['email']) ? htmlspecialchars($datOne[0]['email']) : ''; ?>">
                    </div>

                    <div class="form-group col-md-12">
                        <br>
                        <input type="hidden" name="opera" value="save">
                        <input type="hidden" name="id" value="<?= isset($datOne[0]['idasp']) ? $datOne[0]['idasp'] : ''; ?>">
                        
                        <button type="submit" class="btn btn-success" style="width: 100%;">
                            <?= !empty($datOne) ? 'Actualizar Aspirante' : 'Registrar Aspirante'; ?>
                        </button>
                        <div class="text-center mt-2">
                            <a href="<?= $actionUrl; ?>">Cancelar / Nuevo</a>
                        </div>
                        <br><br>
                    </div>
                </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Toolbar de Control y Filtros -->
<div class="filter-toolbar-container">
    <div class="toolbar-left-group">
        <div class="filter-pills-group" id="tabAspirantes" role="tablist">
            <button class="btn-filter-pill active" id="todos-tab" data-bs-toggle="tab" data-bs-target="#todos" type="button" role="tab" aria-controls="todos" aria-selected="true">
                <span class="pill-dot" style="background-color: #006b29;"></span>
                <span>Aspirantes Registrados</span>
                <span class="pill-count"><?= $totalRegistrados; ?></span>
            </button>
        </div>

        <div class="toolbar-divider d-none d-sm-block"></div>

        <div class="toolbar-export-buttons">
            <button type="button" class="toolbar-export-btn" title="Copiar al portapapeles" onclick="triggerDTExport('copy')">
                <i class="fa-solid fa-copy"></i>
            </button>
            <button type="button" class="toolbar-export-btn" title="Descargar CSV" onclick="triggerDTExport('csv')">
                <i class="fa-solid fa-file-csv"></i>
            </button>
            <button type="button" class="toolbar-export-btn" title="Exportar a Excel" onclick="triggerDTExport('excel')">
                <i class="fa-solid fa-file-excel"></i>
            </button>
        </div>
    </div>

    <div class="toolbar-search-sort-group">
        <div class="toolbar-search-box">
            <i class="fa-solid fa-magnifying-glass icoldiz"></i>
            <input type="text" id="customVideoSearch" placeholder="Buscar aspirante" onkeyup="syncCustomSearch(this.value)">
        </div>

        <div class="toolbar-secondary-controls">
            <select class="toolbar-select-sort" id="customVideoSort" onchange="syncCustomSort(this.value)">
                <option value="asc">Ascendente</option>
                <option value="desc">Descendente</option>
            </select>

            <div class="toolbar-view-toggle">
                <button type="button" class="toolbar-view-btn active" id="btnViewGrid" title="Vista Cuadrícula" onclick="switchCardView('grid')">
                    <i class="fa-solid fa-grip"></i>
                </button>
                <button type="button" class="toolbar-view-btn" id="btnViewList" title="Vista Lista" onclick="switchCardView('list')">
                    <i class="fa-solid fa-bars"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<div class="tab-content" id="tabAspirantesContent">
    <div class="tab-pane fade show active" id="todos" role="tabpanel" aria-labelledby="todos-tab">
        <table id="example" class="table table-striped table-vid-cards" style="width:100%">
            <thead>
                <tr>
                    <th>Aspirantes Registrados</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($datAll) && is_array($datAll)): ?>
                    <?php 
                    $cont = 1;
                    foreach ($datAll as $asp): 
                        $idAsp = $asp['idasp'] ?? '';
                        $tipo = $asp['tipoId'] ?? 'CC';
                        $doc = $asp['documento'] ?? '';
                        $nom = $asp['nombre'] ?? '';
                        $emailVal = $asp['email'] ?? '';
                    ?>
                    <tr data-order="<?= $cont; ?>">
                        <td data-search="<?= $idAsp; ?> <?= $tipo; ?> <?= $doc; ?> <?= $nom; ?> <?= $emailVal; ?>">
                            
                            <!-- Tarjeta de Aspirante (Misma estructura visual de vvidgen) -->
                            <div class="card h-100 shadow-sm border-0 vid-card mb-3">
                                <div class="vid-badges-top p-3 pb-0 d-flex justify-content-between align-items-center">
                                    <span class="badge bg-success shadow-sm px-2 py-1">
                                        <i class="fa-solid fa-user-check me-1"></i> #<?= $cont++; ?>
                                    </span>
                                    <span class="badge vid-badge-blur px-2 py-1 shadow-sm text-dark bg-light border">
                                        <i class="fa-solid fa-id-card me-1 text-info"></i> Tipo: <?= htmlspecialchars($tipo); ?>
                                    </span>
                                </div>

                                <div class="card-body p-3 d-flex flex-column justify-content-between">
                                    <div>
                                        <h6 class="fw-bold text-dark mb-2 text-truncate" title="<?= htmlspecialchars($nom); ?>" style="font-size: 1.05rem;">
                                            <span class="text-primary">Doc: <?= htmlspecialchars($doc); ?></span>
                                        </h6>
                                        <h5 class="fw-bold text-dark mb-3 text-truncate">
                                            <?= htmlspecialchars($nom); ?>
                                        </h5>

                                        <div class="p-2 mb-3 rounded-3 bg-light border border-light-subtle d-flex align-items-center gap-2" style="font-size: 0.8rem;">
                                            <i class="fa-solid fa-envelope text-muted"></i>
                                            <?php if (!empty($emailVal)): ?>
                                                <a href="mailto:<?= htmlspecialchars($emailVal); ?>" class="text-decoration-none"><?= htmlspecialchars($emailVal); ?></a>
                                            <?php else: ?>
                                                <em class="text-muted">Sin correo electrónico</em>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="card-footer bg-white border-0 pt-0 pb-3 px-3">
                                    <hr class="mt-0 mb-3 text-muted opacity-25">
                                    <div class="vid-actions-bar">
                                        <!-- Botón Editar -->
                                        <a href="<?= $actionUrl . $sep; ?>id=<?= $idAsp; ?>&opera=edi" class="btn btn-outline-warning text-dark shadow-sm" title="Editar">
                                            <i class="fa-solid fa-user-pen"></i> <span class="d-none d-sm-inline">Editar</span>
                                        </a>

                                        <!-- Botón Eliminar -->
                                        <a href="<?= $actionUrl . $sep; ?>id=<?= $idAsp; ?>&opera=eli" class="btn btn-outline-danger shadow-sm" title="Eliminar" onclick="return confirm('¿Está seguro de eliminar este aspirante?');">
                                            <i class="fa-solid fa-trash-can"></i> <span class="d-none d-sm-inline">Elim.</span>
                                        </a>
                                    </div>
                                </div>
                            </div>

                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td>
                            <div class="card shadow-sm border-0 p-4 text-center text-muted">
                                <p class="mb-0">No hay aspirantes registrados actualmente.</p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th>Aspirantes Registrados</th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<?php if ($isStandalone): ?>
</div>
</body>
</html>
<?php endif; ?>