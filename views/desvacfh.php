<?php
require_once(__DIR__ . '/../controllers/descacfh.php');

$isStandalone = !isset($snomcc) && !isset($rut);
$currentPg = isset($pg) ? $pg : 2419;
$actionUrl = $isStandalone ? "desvacfh.php?idfic=" . ($idfic ?? '') : "home.php?pg=" . $currentPg . "&idfic=" . ($idfic ?? '');
$pdfUrl = $isStandalone ? "desrfic.php" : "views/desrfic.php";
$pdfLlamUrl = $isStandalone ? "desrlam.php" : "views/desrlam.php";

if ($isStandalone):
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acta de Cierre Ficha & Reporte Informativo - SAGI</title>
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
    .actcierre-wrapper {
        font-family: 'Roboto', -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        color: #1e293b;
        font-size: 1.4rem;
        line-height: 1.5;
        margin-bottom: 3rem;
    }

    /* Reset obligatorio para botones dentro de esta vista */
    .actcierre-wrapper .btn,
    .actcierre-wrapper button,
    .actcierre-wrapper a.btn,
    .actcierre-wrapper .act-btn {
        position: static !important;
        top: auto !important;
        bottom: auto !important;
        left: auto !important;
        right: auto !important;
        transform: none !important;
        margin-top: 0 !important;
        margin-bottom: 0 !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 0.5rem !important;
        font-weight: 500 !important;
        border-radius: 8px !important;
        transition: all 0.2s ease !important;
    }

    /* Botones personalizados seguros */
    .act-btn-green {
        background-color: #39a900 !important;
        border: 1px solid #39a900 !important;
        color: #ffffff !important;
        padding: 0.6rem 1.25rem !important;
        font-size: 1.35rem !important;
    }

    .act-btn-green:hover {
        background-color: #2e8600 !important;
        border-color: #2e8600 !important;
        color: #ffffff !important;
        box-shadow: 0 4px 10px rgba(57, 169, 0, 0.3) !important;
    }

    .act-btn-outline-green {
        background-color: transparent !important;
        border: 1.5px solid #39a900 !important;
        color: #39a900 !important;
        padding: 0.55rem 1rem !important;
        font-size: 1.3rem !important;
    }

    .act-btn-outline-green:hover {
        background-color: #39a900 !important;
        color: #ffffff !important;
    }

    .act-btn-disabled {
        background-color: #f1f5f9 !important;
        border: 1px solid #cbd5e1 !important;
        color: #94a3b8 !important;
        cursor: not-allowed !important;
        padding: 0.55rem 1rem !important;
        font-size: 1.3rem !important;
    }

    .act-btn-warning {
        background-color: #f59e0b !important;
        border: 1px solid #f59e0b !important;
        color: #1e293b !important;
        padding: 0.45rem 0.85rem !important;
        font-size: 1.25rem !important;
    }

    .act-btn-warning:hover {
        background-color: #d97706 !important;
        color: #ffffff !important;
    }

    .act-btn-danger {
        background-color: #ef4444 !important;
        border: 1px solid #ef4444 !important;
        color: #ffffff !important;
        padding: 0.45rem 0.85rem !important;
        font-size: 1.25rem !important;
    }

    .act-btn-danger:hover {
        background-color: #dc2626 !important;
        color: #ffffff !important;
    }

    .act-btn-secondary {
        background-color: #f8fafc !important;
        border: 1px solid #cbd5e1 !important;
        color: #475569 !important;
        padding: 0.45rem 0.85rem !important;
        font-size: 1.25rem !important;
    }

    .act-btn-secondary:hover {
        background-color: #e2e8f0 !important;
        color: #1e293b !important;
    }

    /* Buscador Card */
    .act-search-box {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-top: 4px solid #39a900;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        margin-bottom: 2rem;
    }

    .act-search-box label {
        font-size: 1.3rem;
        font-weight: 600;
        color: #475569;
        margin-bottom: 0.4rem;
        display: block;
    }

    .act-search-box .form-control,
    .act-search-box .form-select {
        font-size: 1.4rem;
        padding: 0.65rem 1rem;
        border-radius: 8px;
        border: 1.5px solid #cbd5e1;
    }

    .act-search-box .form-control:focus,
    .act-search-box .form-select:focus {
        border-color: #39a900;
        box-shadow: 0 0 0 3px rgba(57, 169, 0, 0.15);
    }

    /* Ficha Header Info Box */
    .act-ficha-box {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 1.75rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.03);
        margin-bottom: 2rem;
    }

    .act-ficha-title {
        font-size: 1.8rem;
        font-weight: 700;
        color: #0f172a;
        margin-top: 0.5rem;
        margin-bottom: 0.5rem;
    }

    .act-cronograma-card {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 1.25rem;
    }

    /* Tarjetas del Dashboard KPI */
    .act-kpi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .act-kpi-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 2px 5px rgba(0,0,0,0.04);
        position: relative;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .act-kpi-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        bottom: 0;
        width: 5px;
    }

    .act-kpi-blue::before { background: #0284c7; }
    .act-kpi-green::before { background: #39a900; }
    .act-kpi-amber::before { background: #f59e0b; }
    .act-kpi-red::before { background: #ef4444; }

    .act-kpi-title {
        font-size: 1.2rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #64748b;
        margin-bottom: 0.3rem;
    }

    .act-kpi-value {
        font-size: 2.8rem;
        font-weight: 800;
        line-height: 1.1;
        margin-bottom: 0.3rem;
    }

    .act-kpi-sub {
        font-size: 1.2rem;
        color: #94a3b8;
    }

    .act-kpi-iconbox {
        width: 52px;
        height: 52px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.2rem;
        flex-shrink: 0;
    }

    .act-iconbox-blue { background: #e0f2fe; color: #0284c7; }
    .act-iconbox-green { background: #dcfce7; color: #16a34a; }
    .act-iconbox-amber { background: #fef3c7; color: #d97706; }
    .act-iconbox-red { background: #fee2e2; color: #dc2626; }

    /* Secciones Generales */
    .act-section-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.04);
        margin-bottom: 2.5rem;
        overflow: hidden;
    }

    .act-section-header {
        background: #ffffff;
        border-bottom: 1px solid #e2e8f0;
        padding: 1.25rem 1.75rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .act-section-header h5 {
        font-size: 1.6rem;
        font-weight: 700;
        margin: 0;
        color: #0f172a;
        display: flex;
        align-items: center;
        gap: 0.6rem;
    }

    .act-section-body {
        padding: 1.75rem;
    }

    /* Grid de Actas Trimestrales */
    .act-trimestres-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1.5rem;
    }

    .act-trimestre-item {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 1.5rem;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        box-shadow: 0 1px 3px rgba(0,0,0,0.02);
        position: relative;
        min-height: 220px;
    }

    .act-trimestre-item.border-status-reg {
        border-left: 5px solid #39a900;
    }

    .act-trimestre-item.border-status-disp {
        border-left: 5px solid #0284c7;
    }

    .act-trimestre-item.border-status-wait {
        border-left: 5px solid #94a3b8;
        background: #fafafa;
    }

    .act-item-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1rem;
    }

    .act-item-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1e293b;
    }

    .act-item-title.cierre {
        color: #dc2626;
    }

    .act-item-dates {
        font-size: 1.3rem;
        color: #64748b;
        margin-bottom: 1.25rem;
    }

    .act-item-footer {
        margin-top: auto;
        padding-top: 1rem;
        border-top: 1px solid #f1f5f9;
    }

    /* Tablas Personalizadas */
    .act-table {
        width: 100%;
        border-collapse: collapse;
        margin: 0;
    }

    .act-table th {
        background-color: #f8fafc;
        color: #475569;
        font-size: 1.25rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        padding: 1rem 1.25rem;
        border-bottom: 2px solid #e2e8f0;
        text-align: left;
    }

    .act-table td {
        padding: 1rem 1.25rem;
        font-size: 1.35rem;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }

    .act-table tbody tr:hover {
        background-color: #f8fafc;
    }

    /* Badges */
    .act-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.35rem 0.75rem;
        border-radius: 20px;
        font-size: 1.2rem;
        font-weight: 600;
    }

    .act-badge-green { background: #dcfce7; color: #15803d; }
    .act-badge-blue { background: #e0f2fe; color: #0369a1; }
    .act-badge-amber { background: #fef3c7; color: #b45309; }
    .act-badge-red { background: #fee2e2; color: #b91c1c; }
    .act-badge-gray { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }

    /* Modales */
    .modal-dialog {
        max-width: 600px;
    }

    .act-modal-header {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .act-modal-header.green { background-color: #39a900; color: #ffffff; }
    .act-modal-header.amber { background-color: #f59e0b; color: #1e293b; }
    .act-modal-header.red { background-color: #ef4444; color: #ffffff; }
</style>

<div class="conte actcierre-wrapper">
    <!-- Encabezado Institucional SAGI -->
    <?php 
    if (function_exists('titulo2')) {
        echo titulo2("<i class='" . ($icono ?? 'fa fa-solid fa-file-signature') . "'></i> Acta de Cierre Ficha", 0);
    } else {
    ?>
        <div class="tit mb-3">
            <h1 class="title-page"><i class="fa fa-solid fa-file-signature"></i> Acta de Cierre Ficha</h1>
            <hr class="lintit">
        </div>
    <?php } ?>

    <!-- Alertas y Mensajes de Retroalimentación -->
    <?php if (!empty($mensajeExito)): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-4" role="alert" style="border-left: 5px solid #39a900 !important; background-color: #f0fdf4;">
            <div class="d-flex align-items-center">
                <i class="fa-solid fa-circle-check fs-4 text-success me-3"></i>
                <div>
                    <strong class="text-success" style="font-size: 1.4rem;">Operación Exitosa</strong>
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
                    <strong class="text-danger" style="font-size: 1.4rem;">Atención</strong>
                    <div class="text-dark" style="font-size: 1.3rem;"><?= htmlspecialchars($mensajeError); ?></div>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- 1. Buscador y Selector Superior de Ficha -->
    <div class="act-search-box">
        <form action="<?= $isStandalone ? 'desvacfh.php' : 'home.php'; ?>" method="GET" id="formBuscarFic">
            <?php if (!$isStandalone): ?>
                <input type="hidden" name="pg" value="<?= $currentPg; ?>">
            <?php endif; ?>

            <div class="row g-3 align-items-end">
                <div class="col-12 col-md-4 col-lg-3">
                    <label for="txtFic">
                        <i class="fa-solid fa-hashtag text-success me-1"></i> Número de Ficha:
                    </label>
                    <input type="text" name="idfic" id="txtFic" class="form-control" 
                           placeholder="Ej: 80546098 (número de ficha, 8 dígitos)" value="<?= htmlspecialchars($idfic ?? ''); ?>" autofocus
                           onkeydown="if(event.key === 'Enter'){ document.getElementById('formBuscarFic').submit(); }">
                </div>

                <div class="col-12 col-md-5 col-lg-6">
                    <label for="selFic">
                        <i class="fa-solid fa-list-check text-success me-1"></i> O seleccione de las fichas registradas:
                    </label>
                    <select id="selFic" class="form-select" 
                            onchange="if(this.value){ window.location.href='<?= $isStandalone ? 'desvacfh.php?idfic=' : 'home.php?pg=' . $currentPg . '&idfic='; ?>'+this.value; }">
                        <option value="">-- Seleccione una ficha del listado --</option>
                        <?php if (!empty($todasFichas)): ?>
                            <?php foreach ($todasFichas as $f): ?>
                                <option value="<?= $f['idfic']; ?>" <?= ($idfic == $f['idfic']) ? 'selected' : ''; ?>>
                                    Ficha <?= $f['idfic']; ?> - <?= htmlspecialchars($f['nomfic']); ?> (<?= htmlspecialchars($f['nomval'] ?? 'Jornada'); ?>)
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="col-6 col-md-3 col-lg-2">
                    <label for="selMes">
                        <i class="fa-solid fa-calendar-days text-success me-1"></i> Mes del Informe:
                    </label>
                    <select name="mes" id="selMes" class="form-select" onchange="this.form.submit();">
                        <?php
                            $nombMes = [1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 5 => 'Mayo', 6 => 'Junio',
                                        7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'];
                            $seleccion = !empty($mesesDisponibles) ? $mesesDisponibles : [['anio' => (int)date('Y'), 'mes' => (int)date('n')]];
                            foreach ($seleccion as $sm):
                                $optMes = (int)$sm['mes']; $optAnio = (int)$sm['anio'];
                        ?>
                                <option value="<?= $optMes; ?>"
                                        data-anio="<?= $optAnio; ?>"
                                        <?= ($optMes === (int)($mes ?? 0) && $optAnio === (int)($anio ?? 0)) ? 'selected' : ''; ?>>
                                    <?= $nombMes[$optMes] ?? $optMes; ?> <?= $optAnio; ?>
                                </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-6 col-md-2 col-lg-1">
                    <label for="selAnio">
                        <i class="fa-solid fa-calendar text-success me-1"></i> Año:
                    </label>
                    <select name="anio" id="selAnio" class="form-select" onchange="this.form.submit();">
                        <?php
                            $aniosSel = [];
                            foreach ($seleccion as $sa) { $aniosSel[(int)$sa['anio']] = true; }
                            $anioAct = (int)date('Y');
                            if (!isset($aniosSel[$anioAct])) { $aniosSel[$anioAct] = true; }
                            krsort($aniosSel);
                            foreach (array_keys($aniosSel) as $aa):
                        ?>
                                <option value="<?= $aa; ?>" <?= ((int)($anio ?? 0) === $aa) ? 'selected' : ''; ?>><?= $aa; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-12 col-md-2 col-lg-3 d-flex align-items-center">
                    <small class="text-muted" style="font-size: 1.25rem;">
                        <i class="fa-solid fa-circle-info me-1"></i> Presione <strong>Enter</strong> o seleccione del listado.
                    </small>
                </div>
            </div>
        </form>
    </div>

    <?php if ($datosFicha): ?>
        <!-- 2. Información Resumen de la Ficha Seleccionada -->
        <div class="act-ficha-box">
            <div class="row align-items-center g-3">
                <div class="col-lg-8">
                    <div class="d-flex align-items-center gap-2 flex-wrap mb-2">
                        <span class="act-badge act-badge-green">
                            <i class="fa-solid fa-id-card-clip"></i> Ficha: <?= htmlspecialchars($datosFicha['idfic']); ?>
                        </span>
                        <span class="act-badge act-badge-amber">
                            <i class="fa-solid fa-clock"></i> <?= htmlspecialchars($datosFicha['nomval'] ?? 'Jornada no especificada'); ?>
                        </span>
                        <span class="act-badge act-badge-gray">
                            <i class="fa-solid fa-location-dot text-danger"></i> <?= htmlspecialchars($datosFicha['mun'] ?? 'Chía'); ?>
                        </span>
                    </div>
                    <div class="act-ficha-title"><?= htmlspecialchars($datosFicha['nomfic']); ?></div>
                    <div class="text-muted" style="font-size: 1.35rem;">
                        <i class="fa-solid fa-building-columns text-success me-1"></i> <strong>Centro:</strong> <?= htmlspecialchars($datosFicha['nomcen'] ?? 'Centro de Desarrollo Agroempresarial CDA Chía'); ?>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="act-cronograma-card">
                        <div class="text-secondary fw-bold text-uppercase mb-2" style="font-size: 1.15rem; letter-spacing: 0.05em;">
                            <i class="fa-regular fa-calendar-check text-success me-1"></i> Cronograma de Ficha
                        </div>
                        <div class="d-flex justify-content-between mb-1" style="font-size: 1.3rem;">
                            <span class="text-muted">Fecha Inicio:</span>
                            <span class="fw-bold text-dark"><?= htmlspecialchars($datosFicha['finific'] ?? 'N/A'); ?></span>
                        </div>
                        <div class="d-flex justify-content-between" style="font-size: 1.3rem;">
                            <span class="text-muted">Fecha Cierre:</span>
                            <span class="fw-bold text-dark"><?= htmlspecialchars($datosFicha['ffinfic'] ?? 'N/A'); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Dashboard KPIs / Métricas Principales -->
        <div class="act-kpi-grid">
            <div class="act-kpi-card act-kpi-blue">
                <div>
                    <div class="act-kpi-title">Total Aprendices</div>
                    <div class="act-kpi-value text-dark"><?= $reporteInfo['total_aprendices'] ?? 0; ?></div>
                    <div class="act-kpi-sub"><i class="fa-solid fa-users me-1"></i> Matriculados en ficha</div>
                </div>
                <div class="act-kpi-iconbox act-iconbox-blue">
                    <i class="fa-solid fa-users"></i>
                </div>
            </div>

            <div class="act-kpi-card act-kpi-green">
                <div>
                    <div class="act-kpi-title" style="color: #15803d;">Asistentes Regulares</div>
                    <div class="act-kpi-value" style="color: #15803d;"><?= $reporteInfo['asistentes_regulares'] ?? 0; ?></div>
                    <div class="act-kpi-sub"><i class="fa-solid fa-user-check me-1"></i> Asistencia continua</div>
                </div>
                <div class="act-kpi-iconbox act-iconbox-green">
                    <i class="fa-solid fa-user-check"></i>
                </div>
            </div>

            <div class="act-kpi-card act-kpi-amber">
                <div>
                    <div class="act-kpi-title" style="color: #b45309;">Con Novedades</div>
                    <div class="act-kpi-value" style="color: #b45309;"><?= $reporteInfo['conteo_novedades'] ?? 0; ?></div>
                    <div class="act-kpi-sub"><i class="fa-solid fa-clipboard-user me-1"></i> Retiros, traslados, etc.</div>
                </div>
                <div class="act-kpi-iconbox act-iconbox-amber">
                    <i class="fa-solid fa-clipboard-user"></i>
                </div>
            </div>

            <div class="act-kpi-card act-kpi-red">
                <div>
                    <div class="act-kpi-title" style="color: #b91c1c;">Críticos del Mes</div>
                    <div class="act-kpi-value" style="color: #b91c1c;"><?= count($reporteInfo['aprendices_criticos'] ?? []); ?></div>
                    <div class="act-kpi-sub"><i class="fa-solid fa-triangle-exclamation me-1"></i> &gt; 30 Horas (Mes) <?= $nombMes[(int)($reporteInfo['mes'] ?? date('n'))] ?? $reporteInfo['mes'] ?? ''; ?></div>
                </div>
                <div class="act-kpi-iconbox act-iconbox-red">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
            </div>
        </div>

        <!-- ================================================================= -->
        <!-- SECCIÓN 1: GENERACIÓN DE ACTAS SEGÚN LA FECHA                      -->
        <!-- ================================================================= -->
        <div class="act-section-card">
            <div class="act-section-header">
                <h5>
                    <span class="act-badge act-badge-green"><i class="fa-solid fa-calendar-check"></i></span>
                    1. Actas de Cierre y Seguimiento Trimestral de la Ficha
                </h5>
                <span class="act-badge act-badge-gray">
                    <i class="fa-solid fa-clock-rotate-left"></i> Cronograma Trimestral Automático
                </span>
            </div>
            <div class="act-section-body">
                <p class="text-muted mb-4" style="font-size: 1.35rem;">
                    El sistema calcula automáticamente los trimestres (períodos de 3 meses) a partir de la fecha de inicio y fin. 
                    Cada acta se habilita formalmente cuando la fecha actual alcanza o supera el período evaluado. Al cumplirse el cronograma completo de la ficha, se habilita el <strong>Acta de Cierre Final</strong>.
                </p>

                <div class="act-trimestres-grid">
                    <?php if (!empty($actasCalculadas)): ?>
                        <?php foreach ($actasCalculadas as $acta): ?>
                            <?php 
                                $statusClass = $acta['ya_registrada'] ? 'border-status-reg' : ($acta['disponible'] ? 'border-status-disp' : 'border-status-wait');
                            ?>
                            <div class="act-trimestre-item <?= $statusClass; ?>">
                                <div class="act-item-header">
                                    <div class="act-item-title <?= $acta['es_cierre'] ? 'cierre' : ''; ?>">
                                        <?= htmlspecialchars($acta['titulo']); ?>
                                    </div>
                                    <?php if ($acta['ya_registrada']): ?>
                                        <span class="act-badge act-badge-green"><i class="fa-solid fa-check"></i> Registrada</span>
                                    <?php elseif ($acta['disponible']): ?>
                                        <span class="act-badge act-badge-blue"><i class="fa-solid fa-unlock"></i> Disponible</span>
                                    <?php else: ?>
                                        <span class="act-badge act-badge-gray"><i class="fa-solid fa-lock"></i> En Espera</span>
                                    <?php endif; ?>
                                </div>

                                <div class="act-item-dates">
                                    <div class="fw-semibold text-dark mb-1">Período evaluado:</div>
                                    <div><i class="fa-regular fa-calendar-days text-secondary me-1"></i> <?= $acta['rango']; ?></div>
                                    <div class="mt-2" style="font-size: 1.25rem;">
                                        <?php if ($acta['ya_registrada']): ?>
                                            <span class="text-success fw-semibold"><i class="fa-solid fa-circle-check me-1"></i> Acta registrada y lista para imprimir</span>
                                        <?php elseif ($acta['disponible']): ?>
                                            <span class="text-primary fw-semibold"><i class="fa-solid fa-circle-info me-1"></i> Período cumplido. Puede proceder a generarla.</span>
                                        <?php else: ?>
                                            <span class="text-muted"><i class="fa-solid fa-clock me-1"></i> Habilitación a partir de: <strong><?= $acta['fecha_disponible']; ?></strong></span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="act-item-footer">
                                    <?php if ($acta['ya_registrada']): ?>
                                        <a href="<?= $pdfUrl; ?>?idactdc=<?= $acta['idactdc']; ?>&idfic=<?= $datosFicha['idfic']; ?>" 
                                           target="_blank" class="act-btn act-btn-outline-green w-100">
                                            <i class="fa-solid fa-print"></i> Imprimir / Descargar PDF
                                        </a>
                                    <?php elseif ($acta['disponible']): ?>
                                        <button type="button" class="act-btn act-btn-green w-100"
                                                data-bs-toggle="modal" data-bs-target="#modalCrearActa_<?= $acta['numact']; ?>">
                                            <i class="fa-solid fa-file-circle-plus"></i> Generar Acta
                                        </button>
                                    <?php else: ?>
                                        <button type="button" class="act-btn act-btn-disabled w-100" disabled>
                                            <i class="fa-solid fa-lock"></i> Aún no disponible
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Modal para Generar Acta Específica -->
                            <?php if ($acta['disponible'] && !$acta['ya_registrada']): ?>
                                <div class="modal fade" id="modalCrearActa_<?= $acta['numact']; ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 shadow">
                                            <form action="<?= $actionUrl; ?>" method="POST">
                                                <input type="hidden" name="ope" value="crearActa">
                                                <input type="hidden" name="idfic" value="<?= $datosFicha['idfic']; ?>">
                                                <input type="hidden" name="numacta" value="<?= $acta['numact']; ?>">
                                                <input type="hidden" name="pg" value="<?= $currentPg; ?>">

                                                <div class="act-modal-header green">
                                                    <h5 class="modal-title fw-bold text-white mb-0" style="font-size: 1.6rem;">
                                                        <i class="fa-solid fa-file-circle-check me-2"></i> Generar <?= htmlspecialchars($acta['titulo']); ?>
                                                    </h5>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body p-4" style="font-size: 1.4rem;">
                                                    <div class="alert alert-light border mb-3">
                                                        <strong>Ficha:</strong> <?= htmlspecialchars($datosFicha['idfic']); ?> - <?= htmlspecialchars($datosFicha['nomfic']); ?><br>
                                                        <strong>Período:</strong> <?= $acta['rango']; ?>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label for="coorusu_<?= $acta['numact']; ?>" class="form-label fw-bold">Coordinador Responsable:</label>
                                                        <select name="coorusu" id="coorusu_<?= $acta['numact']; ?>" class="form-select" required>
                                                            <?php if (!empty($coordinadores)): ?>
                                                                <?php foreach ($coordinadores as $c): ?>
                                                                    <option value="<?= $c['idusu']; ?>">
                                                                        <?= htmlspecialchars($c['nomusu']); ?> (<?= htmlspecialchars($c['nomper']); ?>)
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            <?php else: ?>
                                                                <option value="<?= $_SESSION['idusu'] ?? 1; ?>"><?= htmlspecialchars($_SESSION['nomusu'] ?? 'Coordinación'); ?></option>
                                                            <?php endif; ?>
                                                        </select>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label for="conact_<?= $acta['numact']; ?>" class="form-label fw-bold">Conclusiones y Observaciones del Acta:</label>
                                                        <textarea name="conact" id="conact_<?= $acta['numact']; ?>" class="form-control" rows="4" 
                                                                  placeholder="Ingrese las conclusiones acordadas, compromisos pedagógicos y balance del período..."></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer bg-light p-3">
                                                    <button type="button" class="act-btn act-btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                    <button type="submit" class="act-btn act-btn-green">
                                                        <i class="fa-solid fa-save"></i> Guardar y Registrar Acta
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="alert alert-warning w-100 mb-0">
                            <i class="fa-solid fa-triangle-exclamation me-1"></i> No hay fechas configuradas para esta ficha (Inicio y Fin requeridos para calcular las actas).
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- ================================================================= -->
        <!-- SECCIÓN 2: REPORTE INFORMATIVO DE NOVEDADES                        -->
        <!-- ================================================================= -->
        <div class="act-section-card">
            <div class="act-section-header">
                <h5>
                    <span class="act-badge act-badge-amber"><i class="fa-solid fa-clipboard-user"></i></span>
                    2. Detalle de Aprendices con Novedades Registradas
                </h5>
                <span class="act-badge act-badge-amber">
                    Total Novedades: <?= $reporteInfo['conteo_novedades'] ?? 0; ?>
                </span>
            </div>
            <div class="table-responsive">
                <?php if (!empty($reporteInfo['aprendices_novedad'])): ?>
                    <table class="act-table">
                        <thead>
                            <tr>
                                <th style="width: 50px;">#</th>
                                <th>Identificación</th>
                                <th>Nombre Completo del Aprendiz</th>
                                <th>Tipo de Novedad</th>
                                <th>Motivo / Observación</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $cntNov = 1; foreach ($reporteInfo['aprendices_novedad'] as $an): ?>
                                <tr>
                                    <td class="text-muted fw-bold"><?= $cntNov++; ?></td>
                                    <td><span class="act-badge act-badge-gray"><?= htmlspecialchars($an['ndocusu']); ?></span></td>
                                    <td class="fw-bold text-dark"><?= htmlspecialchars($an['nomusu']); ?></td>
                                    <td>
                                        <span class="act-badge <?= ($an['tipo_novedad'] === 'Deserción') ? 'act-badge-red' : (($an['tipo_novedad'] === 'Retiro Voluntario') ? 'act-badge-gray' : 'act-badge-amber'); ?>">
                                            <?= htmlspecialchars($an['tipo_novedad']); ?>
                                        </span>
                                    </td>
                                    <td class="text-muted"><?= htmlspecialchars($an['motivo_desercion'] ?? 'Trámite registrado en el sistema'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="p-5 text-center text-muted">
                        <i class="fa-solid fa-circle-check fa-3x text-success mb-2 d-block"></i>
                        No se registran aprendices con novedades en esta ficha.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- ================================================================= -->
        <!-- SECCIÓN 3: GESTIÓN DE INASISTENCIAS CRÍTICAS Y LLAMADOS            -->
        <!-- ================================================================= -->
        <div class="act-section-card">
            <div class="act-section-header">
                <h5>
                    <span class="act-badge act-badge-red"><i class="fa-solid fa-triangle-exclamation"></i></span>
                    3. Aprendices con Inasistencias Críticas (&gt; 30 Horas) en el Mes
                </h5>
                <span class="act-badge act-badge-red">
                    <?= count($reporteInfo['aprendices_criticos'] ?? []); ?> en seguimiento (<?= $nombMes[(int)($reporteInfo['mes'] ?? date('n'))] ?? $reporteInfo['mes'] ?? ''; ?> <?= $reporteInfo['anio'] ?? ''; ?>)
                </span>
            </div>
            <div class="table-responsive">
                <?php if (!empty($reporteInfo['aprendices_criticos'])): ?>
                    <table class="act-table">
                        <thead>
                            <tr>
                                <th>Aprendiz</th>
                                <th>Identificación</th>
                                <th class="text-center">Horas Inasistencia</th>
                                <th class="text-center">Llamados Previos</th>
                                <th class="text-center" style="min-width: 280px;">Acciones de Gestión</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reporteInfo['aprendices_criticos'] as $ac): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($ac['nomusu']); ?></div>
                                        <div class="text-muted small"><i class="fa-regular fa-envelope me-1"></i><?= htmlspecialchars($ac['emausu'] ?? ''); ?></div>
                                    </td>
                                    <td>
                                        <span class="act-badge act-badge-gray"><?= htmlspecialchars($ac['ndocusu']); ?></span>
                                    </td>
                                    <td class="text-center">
                                        <span class="act-badge <?= ($ac['horasina'] >= 30) ? 'act-badge-red' : 'act-badge-amber'; ?>" style="font-size: 1.3rem;">
                                            <?= $ac['horasina']; ?> hrs
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-inline-flex gap-1">
                                            <span class="act-badge act-badge-amber" title="Llamados Verbales">
                                                <?= $ac['llamados_verbales']; ?> Verbal
                                            </span>
                                            <span class="act-badge act-badge-red" title="Llamados Escritos">
                                                <?= $ac['llamados_escritos']; ?> Escrito
                                            </span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-inline-flex gap-2">
                                            <!-- Botón Registrar Llamado de Atención -->
                                            <button type="button" class="act-btn act-btn-warning" 
                                                    data-bs-toggle="modal" data-bs-target="#modalLlamado_<?= $ac['idusu']; ?>"
                                                    title="Registrar Llamado de Atención">
                                                <i class="fa-solid fa-comment-dots"></i> Registrar llamado
                                            </button>

                                            <!-- Botón Registrar Deserción -->
                                            <button type="button" class="act-btn act-btn-danger" 
                                                    data-bs-toggle="modal" data-bs-target="#modalDesercion_<?= $ac['idusu']; ?>"
                                                    title="Registrar Deserción">
                                                <i class="fa-solid fa-user-slash"></i> Deserción
                                            </button>

                                            <!-- Botón Ver Historial -->
                                            <button type="button" class="act-btn act-btn-secondary" 
                                                    data-bs-toggle="modal" data-bs-target="#modalHistorial_<?= $ac['idusu']; ?>"
                                                    title="Ver Historial de Llamados">
                                                <i class="fa-solid fa-clock-rotate-left"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <!-- MODAL REGISTRAR LLAMADO DE ATENCIÓN -->
                                <div class="modal fade text-start" id="modalLlamado_<?= $ac['idusu']; ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 shadow">
                                            <form action="<?= $actionUrl; ?>" method="POST">
                                                <input type="hidden" name="ope" value="regLlamado">
                                                <input type="hidden" name="idusu" value="<?= $ac['idusu']; ?>">
                                                <input type="hidden" name="idfic" value="<?= $datosFicha['idfic']; ?>">
                                                <input type="hidden" name="pg" value="<?= $currentPg; ?>">

                                                <div class="act-modal-header amber">
                                                    <h5 class="modal-title fw-bold mb-0" style="font-size: 1.6rem;">
                                                        <i class="fa-solid fa-triangle-exclamation me-2"></i> Registrar Llamado de Atención
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body p-4" style="font-size: 1.4rem;">
                                                    <div class="alert alert-light border mb-3">
                                                        <strong>Aprendiz:</strong> <?= htmlspecialchars($ac['nomusu']); ?><br>
                                                        <strong>Documento:</strong> <?= htmlspecialchars($ac['ndocusu']); ?><br>
                                                        <strong>Llamados previos:</strong> <?= $ac['llamados_verbales']; ?> Verbales | <?= $ac['llamados_escritos']; ?> Escritos
                                                    </div>

                                                    <div class="mb-3">
                                                        <label for="obsllam_<?= $ac['idusu']; ?>" class="form-label fw-bold">Observaciones del Llamado:</label>
                                                        <textarea name="obsllam" id="obsllam_<?= $ac['idusu']; ?>" class="form-control" rows="4" 
                                                                  placeholder="Describa el motivo del llamado (ej: Inasistencia sin justificar a las sesiones programadas, reiterados retrasos...)" required></textarea>
                                                        <small class="text-muted mt-2 d-block" style="font-size: 1.25rem;">
                                                            <i class="fa-solid fa-circle-info text-primary me-1"></i>
                                                            El sistema determina el tipo (verbal/escrito) automáticamente según el historial acumulado y genera el documento PDF del llamado.
                                                        </small>
                                                    </div>
                                                </div>
                                                <div class="modal-footer bg-light p-3">
                                                    <button type="button" class="act-btn act-btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                    <button type="submit" class="act-btn act-btn-green">
                                                        <i class="fa-solid fa-check"></i> Registrar Llamado
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <!-- MODAL REGISTRAR DESERCIÓN -->
                                <div class="modal fade text-start" id="modalDesercion_<?= $ac['idusu']; ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 shadow">
                                            <form action="<?= $actionUrl; ?>" method="POST" enctype="multipart/form-data">
                                                <input type="hidden" name="ope" value="regDesercion">
                                                <input type="hidden" name="idusu" value="<?= $ac['idusu']; ?>">
                                                <input type="hidden" name="idfic" value="<?= $datosFicha['idfic']; ?>">
                                                <input type="hidden" name="pg" value="<?= $currentPg; ?>">

                                                <div class="act-modal-header red">
                                                    <h5 class="modal-title fw-bold text-white mb-0" style="font-size: 1.6rem;">
                                                        <i class="fa-solid fa-user-slash me-2"></i> Registrar Solicitud de Deserción
                                                    </h5>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body p-4" style="font-size: 1.4rem;">
                                                    <div class="alert alert-light border mb-3">
                                                        <strong>Aprendiz:</strong> <?= htmlspecialchars($ac['nomusu']); ?><br>
                                                        <strong>Documento:</strong> <?= htmlspecialchars($ac['ndocusu']); ?><br>
                                                        <strong>Ficha:</strong> <?= htmlspecialchars($datosFicha['idfic']); ?> - <?= htmlspecialchars($datosFicha['nomfic']); ?>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label for="coor_des_<?= $ac['idusu']; ?>" class="form-label fw-bold">Coordinador a quien se remite:</label>
                                                        <select name="coorusu" id="coor_des_<?= $ac['idusu']; ?>" class="form-select" required>
                                                            <?php if (!empty($coordinadores)): ?>
                                                                <?php foreach ($coordinadores as $c): ?>
                                                                    <option value="<?= $c['idusu']; ?>">
                                                                        <?= htmlspecialchars($c['nomusu']); ?> (<?= htmlspecialchars($c['nomper']); ?>)
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            <?php else: ?>
                                                                <option value="<?= $_SESSION['idusu'] ?? 1; ?>"><?= htmlspecialchars($_SESSION['nomusu'] ?? 'Coordinación'); ?></option>
                                                            <?php endif; ?>
                                                        </select>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label for="obsdec_<?= $ac['idusu']; ?>" class="form-label fw-bold">Motivo / Justificación de la Deserción:</label>
                                                        <textarea name="obsdec" id="obsdec_<?= $ac['idusu']; ?>" class="form-control" rows="3" 
                                                                  placeholder="Ej: El aprendiz no ha regresado a formación por más de 3 días consecutivos sin justificar en SENA Sofia Plus..." required></textarea>
                                                    </div>

                                                    <div class="mb-3 p-3 bg-light rounded border">
                                                        <label for="arc_sofia_<?= $ac['idusu']; ?>" class="form-label fw-bold text-dark mb-1">
                                                            <i class="fa-solid fa-image text-primary me-1"></i> Evidencia SENA Sofía Plus (Pantallazo de Fallas):
                                                        </label>
                                                        <input type="file" name="arc_sofia" id="arc_sofia_<?= $ac['idusu']; ?>" class="form-control" accept="image/*">
                                                        <small class="text-muted mt-1 d-block" style="font-size: 1.2rem;">
                                                            Adjunte la captura de pantalla de Sofia Plus donde se constatan las fallas reportadas.
                                                        </small>
                                                    </div>
                                                </div>
                                                <div class="modal-footer bg-light p-3">
                                                    <button type="button" class="act-btn act-btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                    <button type="submit" class="act-btn act-btn-danger">
                                                        <i class="fa-solid fa-save"></i> Guardar Deserción
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <!-- MODAL HISTORIAL DE LLAMADOS DE ATENCIÓN -->
                                <div class="modal fade text-start" id="modalHistorial_<?= $ac['idusu']; ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered modal-lg">
                                        <div class="modal-content border-0 shadow">
                                            <div class="act-modal-header bg-light">
                                                <h5 class="modal-title fw-bold text-dark mb-0" style="font-size: 1.6rem;">
                                                    <i class="fa-solid fa-clock-rotate-left text-success me-2"></i> Historial de Llamados - <?= htmlspecialchars($ac['nomusu']); ?>
                                                </h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body p-4" style="font-size: 1.4rem;">
                                                <?php
                                                    $histLlamados = $desmacfh->getHistorialLlamados($ac['idusu']);
                                                ?>
                                                <div class="mb-3 d-flex justify-content-between align-items-center">
                                                    <strong class="text-secondary">Llamados Registrados en el Sistema:</strong>
                                                    <span class="act-badge act-badge-gray"><?= count($histLlamados); ?> registros</span>
                                                </div>
                                                <?php if (!empty($histLlamados)): ?>
                                                    <div class="table-responsive">
                                                        <table class="act-table">
                                                            <thead>
                                                                <tr>
                                                                    <th>Tipo</th>
                                                                    <th>Fecha</th>
                                                                    <th>Observación</th>
                                                                    <th>Instructor</th>
                                                                    <th>Documento</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php foreach ($histLlamados as $hl): ?>
                                                                    <tr>
                                                                        <td>
                                                                            <span class="act-badge <?= ($hl['tipllam'] == 1) ? 'act-badge-amber' : 'act-badge-red'; ?>">
                                                                                <?= ($hl['tipllam'] == 1) ? 'Verbal' : 'Escrito'; ?>
                                                                            </span>
                                                                        </td>
                                                                        <td class="text-muted"><?= htmlspecialchars($hl['fecllam']); ?></td>
                                                                        <td><?= htmlspecialchars($hl['obsllam']); ?></td>
                                                                        <td class="text-muted"><?= htmlspecialchars($hl['instructor'] ?? 'Instructor'); ?></td>
                                                                        <td>
                                                                            <a href="<?= $pdfLlamUrl; ?>?idllam=<?= $hl['idllam']; ?>" target="_blank" class="act-btn act-btn-outline-green" style="font-size: 1.15rem; padding: 0.3rem 0.7rem;">
                                                                                <i class="fa-solid fa-file-pdf"></i> Ver PDF
                                                                            </a>
                                                                        </td>
                                                                    </tr>
                                                                <?php endforeach; ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="alert alert-info small mb-0">No hay llamados de atención registrados para este aprendiz.</div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="modal-footer bg-light p-3">
                                                <button type="button" class="act-btn act-btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="p-5 text-center text-muted">
                        <i class="fa-solid fa-circle-check fa-3x text-success mb-2 d-block"></i>
                        No hay aprendices con inasistencias críticas (&gt; 30 horas) en el mes seleccionado.
                    </div>
                <?php endif; ?>
            </div>
        </div>

    <?php else: ?>
        <div class="act-section-card text-center p-5">
            <div class="p-4">
                <i class="fa-solid fa-folder-open fa-4x text-muted mb-3 d-block"></i>
                <h4 class="text-secondary fw-bold mb-2" style="font-size: 1.8rem;">Por favor ingrese o seleccione una ficha</h4>
                <p class="text-muted" style="font-size: 1.4rem;">Utilice el buscador superior para cargar los datos del cronograma, actas de cierre y reporte informativo de la ficha.</p>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php if ($isStandalone): ?>
</body>
</html>
<?php endif; ?>
