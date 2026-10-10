<?php
require_once(__DIR__ . '/../controllers/desccmje.php');

$isStandalone = !isset($snomcc) && !isset($rut);
$currentPg = isset($pg) ? $pg : 2458;
$actionUrl = $isStandalone ? "desvcmje.php" : "home.php?pg=" . $currentPg;
$debLink = (strpos($actionUrl, '?') === false) ? ($actionUrl . '?') : ($actionUrl . '&');

$totalJui = $contadores['total'] ?? 0;
$aprobJui = $contadores['aprobados'] ?? 0;
$porJui = $contadores['por_evaluar'] ?? 0;
$noAprJui = $contadores['no_aprobados'] ?? 0;

if ($isStandalone):
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carga de Juicios Evaluativos - SAGI</title>
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../font/fontawesome/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="../css/style.css">
    <link rel="stylesheet" type="text/css" href="../css/menu.css">
    <script src="../js/jquery-3.5.1.js"></script>
    <script src="../js/bootstrap.bundle.min.js"></script>
</head>
<body style="background-color: #f8fafc; padding: 20px;">
<?php endif; ?>

<style>
    /* Scope estricto para evitar herencia de reglas conflictivas de style.css */
    .ajc-wrapper {
        font-family: 'Roboto', -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        color: #1e293b;
        font-size: 1.4rem;
        line-height: 1.5;
        margin-bottom: 3rem;
    }

    .ajc-wrapper .ajc-btn-green {
        background-color: #39a900 !important;
        border: 1px solid #39a900 !important;
        color: #ffffff !important;
        padding: 0.75rem 1.6rem !important;
        font-size: 1.4rem !important;
        border-radius: 8px !important;
    }

    .ajc-wrapper .ajc-btn-green:hover {
        background-color: #2e8600 !important;
        border-color: #2e8600 !important;
        color: #ffffff !important;
        box-shadow: 0 4px 10px rgba(57, 169, 0, 0.3) !important;
    }

    .ajc-wrapper .ajc-card {
        background-color: #ffffff;
        border-radius: 14px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 4px rgba(15, 23, 42, 0.06);
        padding: 1.6rem;
    }

    .ajc-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.35rem 0.95rem;
        border-radius: 999px;
        font-size: 1.25rem;
        font-weight: 600;
        white-space: nowrap;
    }

    .ajc-badge-green { background-color: #dcfce7; color: #15803d; }
    .ajc-badge-amber { background-color: #fef3c7; color: #b45309; }
    .ajc-badge-gray { background-color: #f1f5f9; color: #475569; }
    .ajc-badge-red { background-color: #fee2e2; color: #b91c1c; }

    .ajc-kpi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 1.2rem;
        margin-bottom: 1.8rem;
    }

    .ajc-kpi-links .ajc-kpi-card { text-decoration: none; color: inherit; display: flex; }
    .ajc-kpi-links .ajc-kpi-card:hover { border-color: #39a900; box-shadow: 0 4px 12px rgba(57, 169, 0, 0.15); }

    .ajc-file-hint { font-size: 1.25rem; color: #64748b; }
        background-color: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 1.3rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.8rem;
        box-shadow: 0 1px 4px rgba(15, 23, 42, 0.06);
    }

    .ajc-kpi-title { font-size: 1.3rem; color: #64748b; font-weight: 600; }
    .ajc-kpi-value { font-size: 2.3rem; font-weight: 800; line-height: 1.1; }
    .ajc-kpi-sub { font-size: 1.15rem; color: #94a3b8; }

    .ajc-kpi-iconbox {
        width: 46px;
        height: 46px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        flex-shrink: 0;
    }

    .ajc-iconbox-green { background-color: #dcfce7; color: #16a34a; }
    .ajc-iconbox-blue { background-color: #dbeafe; color: #2563eb; }
    .ajc-iconbox-amber { background-color: #fef3c7; color: #d97706; }
    .ajc-iconbox-red { background-color: #fee2e2; color: #dc2626; }

    .ajc-table {
        width: 100%;
        font-size: 1.3rem;
        margin-bottom: 0;
    }

    .ajc-table thead th {
        background-color: #f8fafc;
        color: #475569;
        font-size: 1.2rem;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        padding: 0.7rem 0.8rem;
        white-space: nowrap;
    }

    .ajc-table tbody td {
        padding: 0.65rem 0.8rem;
        vertical-align: middle;
        border-color: #eef2f7;
    }

    .ajc-cell-limit {
        max-width: 240px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .ajc-file-hint { font-size: 1.25rem; color: #64748b; }

    /* Chips de selección de ficha */
    .ajc-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.4rem 1rem;
        border-radius: 999px;
        font-size: 1.25rem;
        font-weight: 600;
        text-decoration: none;
        border: 1px solid #cbd5e1;
        color: #475569;
        background-color: #ffffff;
        transition: all 0.15s ease;
    }

    .ajc-chip:hover { border-color: #39a900; color: #2e8600; }
    .ajc-chip.active { background-color: #39a900; border-color: #39a900; color: #ffffff; }
    .ajc-chip small { font-weight: 700; opacity: 0.85; }

    /* Tabla interactiva */
    th.ajc-sortable { cursor: pointer; user-select: none; }
    th.ajc-sortable:after { content: "\2195"; margin-left: 5px; opacity: 0.3; font-size: 0.95em; }
    th.ajc-sorted-asc:after { content: "\25B2"; opacity: 1; color: #39a900; }
    th.ajc-sorted-desc:after { content: "\25BC"; opacity: 1; color: #39a900; }

    .ajc-busca { position: relative; }
    .ajc-busca i {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 1.25rem;
    }
    .ajc-busca input { padding-left: 38px; font-size: 1.3rem; }

    .ajc-paginacion .page-link {
        font-size: 1.3rem;
        color: #1e293b;
        padding: 0.35rem 0.8rem;
    }
    .ajc-paginacion .page-item.active .page-link { background-color: #39a900; border-color: #39a900; }
    .ajc-paginacion .page-item.disabled .page-link { color: #cbd5e1; }

    /* Detalle del aprendiz */
    .ajc-aprendiz-link { color: #1d4ed8; cursor: pointer; border-bottom: 1px dotted #93c5fd; }
    .ajc-aprendiz-link:hover { color: #1e40af; }

    .ajc-modal-apr .modal-content { border-radius: 14px; border: 0; box-shadow: 0 10px 40px rgba(15, 23, 42, 0.2); }
    .ajc-modal-apr .modal-header { border-bottom: 0; padding-bottom: 0; }
    .ajc-modal-apr .modal-footer { border-top: 0; }

    .ajc-progress {
        height: 10px;
        background-color: #eef2f7;
        border-radius: 999px;
        overflow: hidden;
    }
    .ajc-progress > div { height: 100%; border-radius: 999px; background-color: #39a900; }
</style>

<div class="conte ajc-wrapper">
    <!-- Encabezado Institucional SAGI -->
    <?php
    if (function_exists('titulo2')) {
        echo titulo2("<i class='" . ($icono ?? 'fa fa-solid fa-clipboard-check') . "'></i> Carga de Juicios Evaluativos", 0);
    } else {
    ?>
        <div class="tit mb-3">
            <h1 class="title-page"><i class="fa fa-solid fa-clipboard-check"></i> Carga de Juicios Evaluativos</h1>
            <hr class="lintit">
        </div>
    <?php } ?>

    <!-- Alertas y Mensajes de Retroalimentación -->
    <?php if (!empty($mensajeExito)): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-4" role="alert" style="border-left: 5px solid #39a900 !important; background-color: #f0fdf4;">
            <div class="d-flex align-items-center">
                <i class="fa-solid fa-circle-check fs-4 text-success me-3"></i>
                <div>
                    <strong class="text-success" style="font-size: 1.4rem;">Operaci&oacute;n Exitosa</strong>
                    <div class="text-dark" style="font-size: 1.3rem;"><?= htmlspecialchars($mensajeExito); ?></div>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($mensajeError)): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 mb-4" role="alert" style="border-left: 5px solid #dc2626 !important; background-color: #fef2f2;">
            <div class="d-flex align-items-center">
                <i class="fa-solid fa-triangle-exclamation fs-4 text-danger me-3"></i>
                <div>
                    <strong class="text-danger" style="font-size: 1.4rem;">Atenci&oacute;n</strong>
                    <div class="text-dark" style="font-size: 1.3rem;"><?= htmlspecialchars($mensajeError); ?></div>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($resumenImport) && $resumenImport['exito']): ?>
        <!-- Resumen de la última importación -->
        <div class="ajc-kpi-grid mb-4">
            <div class="ajc-kpi-card">
                <div>
                    <div class="ajc-kpi-title">Filas Le&iacute;das</div>
                    <div class="ajc-kpi-value text-dark"><?= (int)($resumenImport['filas_leidas'] ?? 0); ?></div>
                    <div class="ajc-kpi-sub">Reporte Sof&iacute;a Plus</div>
                </div>
                <div class="ajc-kpi-iconbox ajc-iconbox-blue"><i class="fa-solid fa-file-excel"></i></div>
            </div>

            <div class="ajc-kpi-card">
                <div>
                    <div class="ajc-kpi-title">Juicios Importados</div>
                    <div class="ajc-kpi-value" style="color: #15803d;"><?= (int)($resumenImport['importados'] ?? 0); ?></div>
                    <div class="ajc-kpi-sub">Registrados</div>
                </div>
                <div class="ajc-kpi-iconbox ajc-iconbox-green"><i class="fa-solid fa-check-double"></i></div>
            </div>

            <div class="ajc-kpi-card">
                <div>
                    <div class="ajc-kpi-title">Omitidos</div>
                    <div class="ajc-kpi-value text-dark"><?= (int)($resumenImport['omitidos_estado'] ?? 0); ?></div>
                    <div class="ajc-kpi-sub">Retiro / Cancelado / Deserci&oacute;n</div>
                </div>
                <div class="ajc-kpi-iconbox ajc-iconbox-amber"><i class="fa-solid fa-user-slash"></i></div>
            </div>

            <div class="ajc-kpi-card">
                <div>
                    <div class="ajc-kpi-title">Estados Actualizados</div>
                    <div class="ajc-kpi-value text-dark"><?= (int)($resumenImport['estados_actualizados'] ?? 0); ?></div>
                    <div class="ajc-kpi-sub">Aprendices</div>
                </div>
                <div class="ajc-kpi-iconbox ajc-iconbox-red"><i class="fa-solid fa-id-badge"></i></div>
            </div>
        </div>

        <div class="ajc-card mb-4">
            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                <span class="ajc-badge ajc-badge-green"><i class="fa-solid fa-hashtag"></i> Ficha <?= htmlspecialchars($resumenImport['idfic'] ?? ''); ?></span>
                <span class="ajc-badge ajc-badge-gray"><i class="fa-solid fa-graduation-cap"></i> <?= htmlspecialchars($resumenImport['denominacion'] ?? ''); ?></span>
                <span class="ajc-badge ajc-badge-gray"><i class="fa-regular fa-calendar"></i> <?= htmlspecialchars($resumenImport['finific'] ?? 'N/A'); ?> &rarr; <?= htmlspecialchars($resumenImport['ffinfic'] ?? 'N/A'); ?></span>
                <?php if (!empty($resumenImport['eliminados'])): ?>
                    <span class="ajc-badge ajc-badge-amber"><i class="fa-solid fa-rotate"></i> <?= (int)$resumenImport['eliminados']; ?> juicios reemplazados</span>
                <?php endif; ?>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <small class="text-muted" style="font-size: 1.2rem;">
                    <i class="fa-regular fa-calendar-check me-1"></i> Reporte: <strong><?= htmlspecialchars($resumenImport['fecha_reporte'] ?? date('Y-m-d')); ?></strong> &middot; Archivo: <strong><?= htmlspecialchars($resumenImport['archivo'] ?? ''); ?></strong>
                </small>
                <?php if (!empty($resumenImport['no_encontrados'])): ?>
                    <small class="text-danger" style="font-size: 1.2rem;">
                        <i class="fa-solid fa-circle-exclamation me-1"></i> <?= count($resumenImport['no_encontrados']); ?> documentos sin usuario: <?= htmlspecialchars(implode(', ', array_slice($resumenImport['no_encontrados'], 0, 20))); ?><?= count($resumenImport['no_encontrados']) > 20 ? ' &hellip;' : ''; ?>
                    </small>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- 0. Panel (Dashboard) de Juicios Evaluativos -->
    <?php if (($dashboard['kpis']['total'] ?? 0) > 0): ?>
    <div class="ajc-card mb-4">
        <div class="d-flex align-items-center gap-2 mb-3">
            <span class="ajc-badge ajc-badge-green"><i class="fa-solid fa-gauge-high"></i> Panel de Juicios Evaluativos</span>
        </div>

        <div class="ajc-kpi-grid ajc-kpi-links">
            <a href="<?= $actionUrl; ?>" class="ajc-kpi-card">
                <div>
                    <div class="ajc-kpi-title">Fichas</div>
                    <div class="ajc-kpi-value text-dark"><?= (int)$dashboard['kpis']['fichas']; ?></div>
                    <div class="ajc-kpi-sub">con juicios cargados</div>
                </div>
                <div class="ajc-kpi-iconbox ajc-iconbox-blue"><i class="fa-solid fa-layer-group"></i></div>
            </a>

            <a href="<?= $actionUrl; ?>" class="ajc-kpi-card">
                <div>
                    <div class="ajc-kpi-title">Total de Juicios</div>
                    <div class="ajc-kpi-value text-dark"><?= number_format((int)$dashboard['kpis']['total']); ?></div>
                    <div class="ajc-kpi-sub">registrados</div>
                </div>
                <div class="ajc-kpi-iconbox ajc-iconbox-amber"><i class="fa-solid fa-list-check"></i></div>
            </a>

            <a href="<?= $debLink; ?>estado=APROBADO" class="ajc-kpi-card">
                <div>
                    <div class="ajc-kpi-title">Aprobaci&oacute;n</div>
                    <div class="ajc-kpi-value" style="color: #15803d;"><?= (int)$dashboard['kpis']['tasa']; ?>%</div>
                    <div class="ajc-kpi-sub">sobre juicios evaluados</div>
                </div>
                <div class="ajc-kpi-iconbox ajc-iconbox-green"><i class="fa-solid fa-circle-check"></i></div>
            </a>

            <a href="<?= $debLink; ?>estado=POR EVALUAR" class="ajc-kpi-card">
                <div>
                    <div class="ajc-kpi-title">Por Evaluar</div>
                    <div class="ajc-kpi-value text-dark"><?= number_format((int)$dashboard['kpis']['por_evaluar']); ?></div>
                    <div class="ajc-kpi-sub">pendientes de evaluaci&oacute;n</div>
                </div>
                <div class="ajc-kpi-iconbox ajc-iconbox-red"><i class="fa-regular fa-clock"></i></div>
            </a>
        </div>
    </div>
    <?php endif; ?>

    <!-- 1. Formulario de Carga del Reporte -->
    <div class="ajc-card mb-4">
        <div class="d-flex align-items-center gap-2 mb-3">
            <span class="ajc-badge ajc-badge-green"><i class="fa-solid fa-cloud-arrow-up"></i> Cargar Reporte</span>
            <small class="text-muted" style="font-size: 1.2rem;">
                <i class="fa-solid fa-circle-info me-1"></i> Reporte de Juicios de Evaluaci&oacute;n de SENA Sof&iacute;a Plus.
            </small>
        </div>
        <form action="<?= $actionUrl; ?>" method="POST" enctype="multipart/form-data" class="row g-3 align-items-end">
            <?php if (!$isStandalone): ?>
                <input type="hidden" name="pg" value="<?= $currentPg; ?>">
            <?php endif; ?>
            <input type="hidden" name="ope" value="cargarReporte">

            <div class="col-12 col-md-5 col-lg-6">
                <label for="archivo">
                    <i class="fa-solid fa-file-excel text-success me-1"></i> Archivo del Reporte (Excel):
                </label>
                <input type="file" name="archivo" id="archivo" class="form-control form-control-lg" accept=".xls,.xlsx" required>
                <small class="ajc-file-hint">Formatos .xls y .xlsx, m&aacute;ximo 20 MB.</small>
            </div>

            <div class="col-6 col-md-3 col-lg-2">
                <label for="fecha_reporte">
                    <i class="fa-regular fa-calendar-check text-success me-1"></i> Fecha del Reporte:
                </label>
                <input type="date" name="fecha_reporte" id="fecha_reporte" class="form-control form-control-lg" value="<?= date('Y-m-d'); ?>">
                <small class="ajc-file-hint">Vac&iacute;o: se toma del archivo.</small>
            </div>

            <div class="col-6 col-md-4 col-lg-2">
                <button type="submit" class="btn ajc-btn-green w-100">
                    <i class="fa-solid fa-upload"></i> Importar
                </button>
            </div>

            <div class="col-12">
                <small class="text-muted" style="font-size: 1.2rem;">
                    <i class="fa-solid fa-exclamation-triangle text-warning me-1"></i>
                    Reemplaza los juicios previos de la ficha. Los aprendices no activos se omiten y su estado se actualiza.
                </small>
            </div>
        </form>
    </div>

    <!-- 2. Historial de Juicios Evaluativos -->
    <div class="ajc-card">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
            <div class="d-flex align-items-center gap-2">
                <span class="ajc-badge ajc-badge-gray"><i class="fa-solid fa-list-check"></i> Historial de Juicios Evaluativos</span>
            </div>
        </div>

        <?php if (!empty($resumenPorFicha)): ?>
            <div class="d-flex flex-wrap gap-2 mb-3">
                <a href="<?= $actionUrl; ?>" class="ajc-chip <?= ($idfic === '' || $idfic === null) ? 'active' : ''; ?>">
                    <i class="fa-solid fa-table-cells"></i> Todas
                </a>
                <?php foreach ($resumenPorFicha as $rp): ?>
                    <a href="<?= $debLink; ?>idfic=<?= urlencode($rp['idfic']); ?>" class="ajc-chip <?= ((string)$idfic === (string)$rp['idfic']) ? 'active' : ''; ?>">
                        <?= htmlspecialchars($rp['idfic']); ?> <small><?= number_format((int)$rp['total']); ?></small>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($juicios)): ?>
            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                <span class="ajc-badge ajc-badge-gray"><i class="fa-solid fa-list"></i> <?= number_format($totalJui); ?> registro<?= $totalJui === 1 ? '' : 's'; ?></span>
                <?php if ($estado): ?>
                    <a href="<?= $debLink; ?>idfic=<?= urlencode((string)$idfic); ?>" class="ajc-chip active" title="Quitar filtro de estado">
                        <i class="fa-solid fa-filter"></i> <?= htmlspecialchars($estado); ?> <i class="fa-solid fa-xmark ms-1"></i>
                    </a>
                <?php endif; ?>
                <?php if ($aprobJui): ?><span class="ajc-badge ajc-badge-green"><i class="fa-solid fa-circle-check"></i> <?= number_format($aprobJui); ?> aprobados</span><?php endif; ?>
                <?php if ($porJui): ?><span class="ajc-badge ajc-badge-amber"><i class="fa-regular fa-clock"></i> <?= number_format($porJui); ?> por evaluar</span><?php endif; ?>
                <?php if ($noAprJui): ?><span class="ajc-badge ajc-badge-red"><i class="fa-solid fa-circle-xmark"></i> <?= number_format($noAprJui); ?> no aprobados</span><?php endif; ?>
            </div>

            <div class="row g-2 align-items-center mb-3">
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="ajc-busca">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" id="buscaJuicio" class="form-control" placeholder="Buscar aprendiz, documento, competencia..." autocomplete="off">
                    </div>
                    <small class="ajc-file-hint mt-1 d-block"><i class="fa-solid fa-id-card me-1"></i> Escribe el n&uacute;mero de documento completo para abrir el detalle del aprendiz.</small>
                </div>
                <div class="col-12 col-md-6 col-lg-8 text-md-end">
                    <small class="text-muted" style="font-size: 1.2rem;">
                        Mostrando <b><span id="desdeJuicio">0</span>&ndash;<span id="hastaJuicio">0</span></b> de <b><span id="cntJuicios">0</span></b> registros
                    </small>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle ajc-table" id="tblJuicios">
                    <thead>
                        <tr>
                            <th class="ajc-sortable" data-tipo="num">Ficha</th>
                            <th class="ajc-sortable" data-tipo="txt">Aprendiz</th>
                            <th class="ajc-sortable" data-tipo="txt">Competencia</th>
                            <th class="ajc-sortable" data-tipo="txt">Resultado</th>
                            <th class="ajc-sortable text-center" data-tipo="txt">Juicio</th>
                            <th class="ajc-sortable text-center" data-tipo="fecha">Fec. Evaluaci&oacute;n</th>
                            <th class="ajc-sortable" data-tipo="txt">Instructor</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($juicios as $j): ?>
                            <?php
                                $badgeJuicio = 'ajc-badge-gray';
                                $iconoJuicio = 'fa-solid fa-hourglass-half';
                                $calTxt = strtoupper(trim((string)($j['caljui'] ?? '')));
                                if ($calTxt === 'APROBADO') { $badgeJuicio = 'ajc-badge-green'; $iconoJuicio = 'fa-solid fa-circle-check'; }
                                elseif ($calTxt === 'NO APROBADO') { $badgeJuicio = 'ajc-badge-red'; $iconoJuicio = 'fa-solid fa-circle-xmark'; }
                                elseif ($calTxt === 'POR EVALUAR') { $badgeJuicio = 'ajc-badge-amber'; $iconoJuicio = 'fa-regular fa-clock'; }
                            ?>
                            <tr data-doc="<?= htmlspecialchars($j['ndocusu'] ?? ''); ?>" data-caljui="<?= htmlspecialchars($calTxt); ?>">
                                <td data-sort="<?= (int)$j['idfic']; ?>"><strong><?= htmlspecialchars($j['idfic']); ?></strong></td>
                                <td>
                                    <div class="ajc-aprendiz-link fw-bold text-truncate" title="Ver detalle del aprendiz" style="max-width: 190px;"><?= htmlspecialchars($j['nomusu'] ?? 'Usuario no encontrado'); ?></div>
                                    <div class="text-muted" style="font-size: 1.15rem;"><?= htmlspecialchars($j['ndocusu'] ?? '-'); ?></div>
                                </td>
                                <td class="ajc-cell-limit" title="<?= htmlspecialchars($j['descom'] ?? ''); ?>">
                                    <small>[<?= htmlspecialchars($j['idcom'] ?? ''); ?>]</small> <?= htmlspecialchars($j['descom'] ?? '-'); ?>
                                </td>
                                <td class="ajc-cell-limit" title="<?= htmlspecialchars($j['nomres'] ?? ''); ?>">
                                    <small>[<?= htmlspecialchars($j['idres'] ?? ''); ?>]</small> <?= htmlspecialchars($j['nomres'] ?? '-'); ?>
                                </td>
                                <td class="text-center">
                                    <span class="ajc-badge <?= $badgeJuicio; ?>"><i class="<?= $iconoJuicio; ?> me-1"></i> <?= htmlspecialchars($calTxt ?: 'POR EVALUAR'); ?></span>
                                </td>
                                <td class="text-center" data-sort="<?= htmlspecialchars($j['fecjuieva'] ?? ''); ?>"><?= htmlspecialchars($j['fecjuieva'] ?? '-'); ?></td>
                                <td>
                                    <div class="fw-bold text-truncate" style="max-width: 200px;"><?= htmlspecialchars($j['nombre_instructor'] ?? 'Instructor'); ?></div>
                                    <div class="text-muted" style="font-size: 1.15rem;"><?= htmlspecialchars($j['ndocins'] ?? '-'); ?></div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div id="sinJuicios" class="text-center text-muted py-4" style="font-size: 1.4rem; display: none;">
                <i class="fa-regular fa-face-smile me-2"></i> Sin resultados para la b&uacute;squeda.
            </div>

            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2 mt-3">
                <div class="d-flex align-items-center gap-2">
                    <small class="text-muted" style="font-size: 1.15rem;">Registros por p&aacute;gina:</small>
                    <select id="perPagJuicios" class="form-select form-select-sm" style="width: auto;">
                        <option value="15" selected>15</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
                <nav>
                    <ul class="pagination pagination-sm ajc-paginacion mb-0" id="pagJuicios"></ul>
                </nav>
            </div>
        <?php else: ?>
            <div class="text-center text-muted py-4" style="font-size: 1.4rem;">
                <i class="fa-regular fa-file-lines me-2"></i> No hay juicios registrados<?= $idfic ? ' para esta ficha' : ''; ?>.
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal: Detalle del Aprendiz -->
<div class="modal fade ajc-modal-apr" id="modalAprendiz" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modTitulo"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body" id="modCuerpo"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fa-solid fa-xmark me-1"></i> Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    "use strict";

    var ultimoDocAbierto = null;
    var rowsGlobal = [];

    function abrirDetalle(doc) {
        if (!doc) return;
        var det = rowsGlobal.filter(function (r) { return $(r).attr('data-doc') === doc; });
        if (!det.length) return;

        var total = det.length, apro = 0, porEv = 0, noApr = 0;
        det.forEach(function (r) {
            var c = $(r).attr('data-caljui');
            if (c === 'APROBADO') apro++;
            else if (c === 'NO APROBADO') noApr++;
            else porEv++;
        });

        var nombre = $.trim($(det[0]).find('td').eq(1).find('.ajc-aprendiz-link').text());
        var documento = $(det[0]).attr('data-doc');
        var fichas = {};
        det.forEach(function (r) { fichas[$.trim($(r).find('td').eq(0).text())] = 1; });
        var listaFichas = Object.keys(fichas).join(' · ');

        var validados = apro + noApr;
        var pct = validados ? Math.round((apro / validados) * 100) : 0;

        var $wrap = $('<div>');

        var $badges = $('<div class="d-flex flex-wrap align-items-center gap-2 mb-3"></div>');
        $badges.append('<span class="ajc-badge ajc-badge-gray"><i class="fa-solid fa-list"></i> ' + total + ' juicios</span>');
        if (apro) $badges.append('<span class="ajc-badge ajc-badge-green"><i class="fa-solid fa-circle-check"></i> ' + apro + ' aprobados</span>');
        if (porEv) $badges.append('<span class="ajc-badge ajc-badge-amber"><i class="fa-regular fa-clock"></i> ' + porEv + ' por evaluar</span>');
        if (noApr) $badges.append('<span class="ajc-badge ajc-badge-red"><i class="fa-solid fa-circle-xmark"></i> ' + noApr + ' no aprobados</span>');
        $wrap.append($badges);

        $wrap.append('<div class="mb-4">' +
            '<div class="d-flex justify-content-between mb-1"><small class="text-muted">Tasa de aprobaci&oacute;n (sobre juicios evaluados)</small>' +
            '<strong>' + pct + '%</strong></div>' +
            '<div class="ajc-progress"><div style="width:' + pct + '%;' + (noApr > apro ? 'background-color:#dc2626;' : '') + '"></div></div></div>');

        var $tbl = $('<div class="table-responsive"><table class="table table-striped align-middle ajc-table mb-0"><thead><tr>' +
            '<th>Competencia</th><th>Resultado de Aprendizaje</th><th class="text-center">Juicio</th>' +
            '<th class="text-center">Fec. Evaluaci&oacute;n</th><th>Instructor</th>' +
            '</tr></thead><tbody></tbody></table></div>');
        det.slice().sort(function (a, b) {
            return $(a).find('td').eq(2).text().localeCompare($(b).find('td').eq(2).text());
        }).forEach(function (r) {
            $tbl.find('tbody').append($('<tr>').append($(r).find('td').slice(2).clone()));
        });
        $wrap.append($tbl);

        $('#modTitulo').html('<i class="fa-solid fa-user-graduate text-success me-2"></i>' +
            $('<div>').text(nombre).html() +
            ' <small class="text-muted fs-6"><i class="fa-solid fa-id-card ms-1 me-1"></i>' + documento +
            (listaFichas ? ' &nbsp;&middot;&nbsp; <i class="fa-solid fa-hashtag me-1"></i>Ficha ' + $('<div>').text(listaFichas).html() : '') + '</small>');

        $('#modCuerpo').empty().append($wrap);
        $('#modalAprendiz').modal('show');
    }

    function initHistorial() {
        var $tbl = $('#tblJuicios');
        if (!$tbl.length) return;

        var $tbody = $tbl.find('tbody');
        var rows = $tbody.find('tr').toArray();
        rowsGlobal = rows;
        var perPage = 15, page = 0, sortCol = -1, sortAsc = true;

        function valor(r, c) {
            var $td = $(r).find('td').eq(c);
            var s = $td.attr('data-sort');
            if (s !== undefined && s !== "") return s;
            return $.trim($td.text()).toLowerCase();
        }

        function visibles() {
            var q = $.trim($('#buscaJuicio').val()).toLowerCase();
            return rows.filter(function (r) {
                return !q || $(r).text().toLowerCase().indexOf(q) !== -1;
            });
        }

        function numPages(vis) {
            return Math.max(1, Math.ceil(vis.length / perPage));
        }

        function render() {
            var vis = visibles();

            if (sortCol >= 0) {
                var tipo = $tbl.find('thead th').eq(sortCol).attr('data-tipo');
                vis.sort(function (a, b) {
                    var va = valor(a, sortCol), vb = valor(b, sortCol);
                    var r;
                    if (tipo === 'num') {
                        var na = parseFloat(va.replace(/[^\d.\-]/g, '')) || 0;
                        var nb = parseFloat(vb.replace(/[^\d.\-]/g, '')) || 0;
                        r = na - nb;
                    } else {
                        r = va.localeCompare(vb, 'es', { numeric: true, sensitivity: 'base' });
                    }
                    return sortAsc ? r : -r;
                });
            }

            var pages = numPages(vis);
            if (page >= pages) page = 0;

            $tbody.find('tr').hide();
            var slice = vis.slice(page * perPage, page * perPage + perPage);
            slice.forEach(function (r) { $(r).show(); });

            $('#cntJuicios').text(vis.length);
            $('#desdeJuicio').text(vis.length ? page * perPage + 1 : 0);
            $('#hastaJuicio').text(vis.length ? page * perPage + slice.length : 0);
            $('#sinJuicios').toggle(vis.length === 0);

            // Abrir detalle automáticamente si se escribió un documento completo y hay coincidencia única
            var q = $.trim($('#buscaJuicio').val());
            var qDig = q.replace(/[^\d]/g, '');
            if (qDig.length >= 6 && vis.length > 0) {
                var docs = {};
                vis.forEach(function (r) { docs[$(r).attr('data-doc')] = 1; });
                var keys = Object.keys(docs);
                if (keys.length === 1 && keys[0] === qDig) {
                    if (ultimoDocAbierto !== keys[0]) {
                        ultimoDocAbierto = keys[0];
                        abrirDetalle(keys[0]);
                    }
                } else if (ultimoDocAbierto) {
                    ultimoDocAbierto = null;
                }
            } else if (ultimoDocAbierto) {
                ultimoDocAbierto = null;
            }

            var $nav = $('#pagJuicios').empty();
            if (pages > 1) {
                $('<li class="page-item' + (page === 0 ? ' disabled' : '') + '" data-p="' + (page - 1) + '"><a class="page-link" href="#">&laquo;</a></li>').appendTo($nav);
                var desde = Math.max(0, Math.min(page - 2, pages - 5));
                var hasta = Math.min(pages, desde + 5);
                for (var i = desde; i < hasta; i++) {
                    $('<li class="page-item' + (i === page ? ' active' : '') + '" data-p="' + i + '"><a class="page-link" href="#">' + (i + 1) + '</a></li>').appendTo($nav);
                }
                $('<li class="page-item' + (page === pages - 1 ? ' disabled' : '') + '" data-p="' + (page + 1) + '"><a class="page-link" href="#">&raquo;</a></li>').appendTo($nav);
            }
        }

        $('#buscaJuicio').on('input', function () { page = 0; render(); });
        $('#perPagJuicios').on('change', function () { perPage = parseInt(this.value, 10) || 15; page = 0; render(); });
        $('#pagJuicios').on('click', 'li[data-p]', function (e) {
            e.preventDefault();
            var p = parseInt($(this).attr('data-p'), 10);
            if (p < 0 || p >= numPages(visibles())) return;
            page = p;
            render();
        });
        $tbl.on('click', '.ajc-aprendiz-link', function () {
            abrirDetalle($(this).closest('tr').attr('data-doc'));
        });
        $('#modalAprendiz').on('hidden.bs.modal', function () { ultimoDocAbierto = null; });
        $tbl.find('thead th.ajc-sortable').on('click', function () {
            var c = $(this).index();
            if (sortCol === c) {
                sortAsc = !sortAsc;
            } else {
                sortCol = c;
                sortAsc = true;
            }
            $tbl.find('thead th').removeClass('ajc-sorted-asc ajc-sorted-desc');
            $(this).addClass(sortAsc ? 'ajc-sorted-asc' : 'ajc-sorted-desc');
            page = 0;
            render();
        });

        render();
    }

    $(document).ready(initHistorial);
    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        setTimeout(initHistorial, 0);
    }
})();
</script>
<?php if ($isStandalone): ?>
</body>
</html>
<?php endif; ?>