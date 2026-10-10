<?php
// =========================================================================
// Módulo: 12 Horarios y Agendas
// Página: 1522 - Reporte Plan de Sesión
// Arquitectura: MV (Modelo-Vista)
// Desarrollador asignado: MIGUEL ANGEL ACOSTA ORTIZ
// Consumo directo de modelo: models/hormplss.php
// =========================================================================

require_once __DIR__ . '/../models/conexion.php';
require_once __DIR__ . '/../models/hormplss.php';
require_once __DIR__ . '/../models/hormacceso.php';

$mpse = new Mpse();

// Alcance por rol (módulo HyA): fichas visibles según el usuario autenticado.
$hacceso = new HormAcceso();
$scopeCtx = $hacceso->getContexto();
$scopeFichas = $hacceso->getFichasPermitidas($scopeCtx);
$scopeRol = $hacceso->etiquetaRol($scopeCtx);
$scopeN = count($scopeFichas);
$scopeTotal = !empty($scopeCtx['is_admin']);

// Filtro por ficha (validado contra el alcance del usuario)
$filtroFicha = isset($_REQUEST['idfic']) && !empty($_REQUEST['idfic']) ? trim($_REQUEST['idfic']) : null;
if (!$hacceso->puedeVerFicha($filtroFicha, $scopeCtx)) {
    $filtroFicha = null;
}
$pg = isset($_REQUEST['pg']) ? $_REQUEST['pg'] : '1522';

// Datos de apoyo
$datC = method_exists($mpse, 'getCen') ? $mpse->getCen() : [];
$centro = !empty($datC[0]) ? $datC[0] : [
    'nomcen' => 'CENTRO DE FORMACIÓN SENA',
    'dircen' => 'Sede Principal SENA',
    'telcen' => ''
];

$datFic = method_exists($mpse, 'getFichas') ? $hacceso->filtrarPorFicha($mpse->getFichas(), 'idfic', $scopeCtx) : [];
$sesiones = method_exists($mpse, 'getReporteSesiones') ? $hacceso->filtrarPorFicha($mpse->getReporteSesiones($filtroFicha), 'idfic', $scopeCtx) : [];
require_once("views/vscopehya.php");
?>

<style>
/* Estilos generales del reporte institucional */
.reporte-container {
    max-width: 1050px;
    margin: 0 auto 30px auto;
    background: #ffffff;
}

.reporte-card {
    border: 1px solid #1e7e34;
    margin-bottom: 30px;
    background: #fff;
    box-shadow: 0 2px 6px rgba(0,0,0,0.08);
}

.tabla-reporte {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 0;
    font-family: Arial, Helvetica, sans-serif;
    font-size: 11px;
}

.tabla-reporte th, 
.tabla-reporte td {
    border: 1px solid #333333;
    padding: 5px 8px;
    vertical-align: middle;
}

.tabla-reporte .header-cen {
    background-color: #ffffff;
    text-align: center;
    padding: 10px;
}

.tabla-reporte .header-cen img {
    max-height: 55px;
    width: auto;
}

.tabla-reporte .header-cen h2 {
    font-size: 14px;
    font-weight: bold;
    margin: 5px 0 2px 0;
    color: #000;
}

.tabla-reporte .header-cen p {
    font-size: 11px;
    margin: 0;
    color: #333;
}

.tabla-reporte .title-sec {
    background-color: #00af00;
    color: #ffffff;
    font-weight: bold;
    text-align: center;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.tabla-reporte .subtitle-sec {
    background-color: #e8f5e9;
    color: #1b5e20;
    font-weight: bold;
    text-align: center;
    font-size: 11px;
}

.tabla-reporte .lbl {
    background-color: #f8f9fa;
    font-weight: bold;
    color: #212529;
    width: 20%;
}

.tabla-reporte .val {
    background-color: #ffffff;
}

.tabla-reporte thead.secuencia-head tr th {
    background-color: #008700;
    color: #ffffff;
    text-align: center;
    font-weight: bold;
    font-size: 11px;
    border-color: #005a00;
}

.tabla-reporte tbody.secuencia-body tr td {
    font-size: 11px;
}

/* Reglas estrictas de impresión */
@media print {
    .no-print,
    header,
    nav,
    footer,
    .header-home,
    .btn,
    .menu,
    #err {
        display: none !important;
    }

    body, .contenido, .contenido2 {
        margin: 0 !important;
        padding: 0 !important;
        background: #ffffff !important;
    }

    .reporte-container {
        max-width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    .reporte-card {
        border: 1px solid #000 !important;
        box-shadow: none !important;
        page-break-after: always;
        break-after: page;
        margin-bottom: 20px !important;
    }

    .tabla-reporte {
        width: 100% !important;
        font-size: 10px !important;
    }

    .tabla-reporte th, 
    .tabla-reporte td {
        border: 1px solid #000 !important;
        padding: 4px 6px !important;
    }

    .tabla-reporte .title-sec {
        background-color: #e0e0e0 !important;
        color: #000000 !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    .tabla-reporte .subtitle-sec,
    .tabla-reporte thead.secuencia-head tr th {
        background-color: #f0f0f0 !important;
        color: #000000 !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
}
</style>

<div class="reporte-container">

    <!-- BARRA DE ACCIONES Y FILTRO (NO IMPRIMIBLE) -->
    <div class="card border-0 shadow-sm mb-4 no-print" style="background:#f4f9f4; border-left: 4px solid #00af00 !important;">
        <div class="card-body p-3">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <h5 class="text-success fw-bold mb-1">
                        <i class="fa-solid fa-file-invoice"></i> Reporte Institucional: Plan de Sesión
                    </h5>
                    <small class="text-muted">Formato estandarizado SENA para seguimiento pedagógico de sesiones</small>
                </div>
                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <form method="GET" action="home.php" class="d-flex align-items-center gap-2 m-0">
                        <input type="hidden" name="pg" value="<?= htmlspecialchars($pg); ?>">
                        <label for="filtro_idfic" class="form-label form-label-sm fw-bold mb-0 text-secondary">Ficha:</label>
                        <select name="idfic" id="filtro_idfic" class="form-select form-select-sm" style="min-width: 220px;" onchange="this.form.submit()">
                            <option value="">-- Todas las Fichas --</option>
                            <?php if (!empty($datFic)): foreach ($datFic as $f): ?>
                                <option value="<?= htmlspecialchars($f['idfic']); ?>" <?= ($filtroFicha == $f['idfic']) ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($f['idfic']); ?> - <?= htmlspecialchars(mb_substr($f['nomfic'], 0, 24)); ?>...
                                </option>
                            <?php endforeach; endif; ?>
                        </select>
                        <?php if (!empty($filtroFicha)): ?>
                            <a href="home.php?pg=<?= htmlspecialchars($pg); ?>" class="btn btn-sm btn-outline-secondary" title="Quitar filtro">
                                <i class="fa-solid fa-xmark"></i>
                            </a>
                        <?php endif; ?>
                    </form>

                    <button type="button" class="btn btn-sm btn-success fw-bold text-white" onclick="window.print();">
                        <i class="fa-solid fa-print"></i> Imprimir Reporte (PDF)
                    </button>

                    <a href="home.php?pg=1512" class="btn btn-sm btn-outline-secondary" title="Volver al Plan de Sesión">
                        <i class="fa-solid fa-calendar-days"></i> Ir a Plan de Sesión
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- CUERPO DEL REPORTE (FORMATO INSTITUCIONAL) -->
    <?php if (!empty($sesiones)): foreach ($sesiones as $idx => $s): 
        $duracionHoras = round($s['total_duracion'] / 60, 1);
    ?>
    <div class="reporte-card">
        <table class="tabla-reporte">
            <tbody>
                <!-- ENCABEZADO CENTRO Y LOGO -->
                <tr>
                    <td colspan="6" class="header-cen">
                        <div style="display: flex; align-items: center; justify-content: center; gap: 20px;">
                            <img src="img/sena.png" alt="Logo SENA">
                            <div>
                                <h2><?= htmlspecialchars($centro['nomcen'] ?? 'CENTRO DE FORMACIÓN PROFESIONAL SENA'); ?></h2>
                                <p><?= htmlspecialchars($centro['dircen'] ?? ''); ?> <?= !empty($centro['telcen']) ? ' - Tel: ' . htmlspecialchars($centro['telcen']) : ''; ?></p>
                            </div>
                        </div>
                    </td>
                </tr>

                <!-- TITULO PRINCIPAL -->
                <tr class="title-sec">
                    <td colspan="6"><b>PLAN DE SESIÓN PEDAGÓGICA</b></td>
                </tr>

                <!-- SECCIÓN: DATOS GENERALES -->
                <tr class="subtitle-sec">
                    <td colspan="6"><b>DATOS GENERALES</b></td>
                </tr>

                <tr>
                    <td class="lbl"><b>Fecha de Sesión:</b></td>
                    <td class="val" colspan="2"><?= htmlspecialchars($s['fecses']); ?></td>
                    <td class="lbl"><b>Sesión N°:</b></td>
                    <td class="val" colspan="2"><?= htmlspecialchars((string)$s['ndeses']); ?> (ID Agenda: <?= (int)$s['idage']; ?>)</td>
                </tr>

                <tr>
                    <td class="lbl"><b>Ficha de Caracterización:</b></td>
                    <td class="val" colspan="2"><strong><?= htmlspecialchars($s['idfic']); ?></strong></td>
                    <td class="lbl"><b>Programa de Formación:</b></td>
                    <td class="val" colspan="2"><?= htmlspecialchars($s['nomfic']); ?> <?= !empty($s['codpro']) ? '(' . htmlspecialchars($s['codpro']) . ')' : ''; ?></td>
                </tr>

                <tr>
                    <td class="lbl"><b>Instructor Responsable:</b></td>
                    <td class="val" colspan="5">
                        <i class="fa-solid fa-user-tie no-print text-success"></i> 
                        <strong><?= htmlspecialchars($s['nomusu']); ?></strong>
                        <?= !empty($s['ndocusu']) ? ' - Doc: ' . htmlspecialchars($s['ndocusu']) : ''; ?>
                    </td>
                </tr>

                <tr>
                    <td class="lbl"><b>Actividad de Proyecto:</b></td>
                    <td class="val" colspan="5"><?= htmlspecialchars($s['actpro']); ?></td>
                </tr>

                <tr>
                    <td class="lbl"><b>Competencia:</b></td>
                    <td class="val" colspan="2"><?= htmlspecialchars($s['descom']); ?></td>
                    <td class="lbl"><b>Resultado de Aprendizaje:</b></td>
                    <td class="val" colspan="2"><?= htmlspecialchars($s['nomres']); ?></td>
                </tr>

                <tr>
                    <td class="lbl"><b>Objetivo de la Sesión:</b></td>
                    <td class="val" colspan="5"><?= htmlspecialchars($s['objetivo']); ?></td>
                </tr>

                <!-- SECCIÓN: SECUENCIA DIDÁCTICA -->
                <tr class="subtitle-sec">
                    <td colspan="4"><b>SECUENCIA DIDÁCTICA</b></td>
                    <td class="lbl" style="text-align:right;"><b>Duración Total:</b></td>
                    <td class="val" style="text-align:center;"><b><?= $s['total_duracion']; ?> min (<?= $duracionHoras; ?> hrs)</b></td>
                </tr>
            </tbody>

            <!-- CABECERA DE ACTIVIDADES -->
            <thead class="secuencia-head">
                <tr>
                    <th style="width: 14%;">Momento</th>
                    <th style="width: 12%;">Fecha</th>
                    <th style="width: 24%;">Actividad</th>
                    <th style="width: 38%;">Descripción / Orientación</th>
                    <th colspan="2" style="width: 12%;">Tiempo</th>
                </tr>
            </thead>

            <!-- DETALLE DE ACTIVIDADES -->
            <tbody class="secuencia-body">
                <?php if (!empty($s['actividades'])): foreach ($s['actividades'] as $act): ?>
                <tr>
                    <td style="text-align: center;">
                        <span class="badge bg-light text-dark border"><?= htmlspecialchars($act['nomval']); ?></span>
                    </td>
                    <td style="text-align: center;"><?= htmlspecialchars($act['fecses']); ?></td>
                    <td><strong><?= htmlspecialchars($act['nomact']); ?></strong></td>
                    <td><?= htmlspecialchars($act['desact']); ?></td>
                    <td colspan="2" style="text-align: center;"><strong><?= (int)$act['duract']; ?> min</strong></td>
                </tr>
                <?php endforeach; else: ?>
                <tr>
                    <td colspan="6" style="text-align:center; color:#666; padding: 12px;">
                        No hay actividades detalladas registradas para esta sesión.
                    </td>
                </tr>
                <?php endif; ?>

                <!-- OBSERVACIONES DE LA SESIÓN -->
                <tr class="subtitle-sec">
                    <td colspan="6"><b>OBSERVACIONES DE LA SESIÓN</b></td>
                </tr>
                <tr>
                    <td colspan="6" style="height: 45px; vertical-align: top; color: #555;">
                        Sesión pedagógica programada para el desarrollo integral de las evidencias de formación del programa.
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <?php endforeach; else: ?>
    <div class="card border-warning p-4 text-center my-4">
        <h5 class="text-warning"><i class="fa-solid fa-triangle-exclamation"></i> No se encontraron sesiones para generar el reporte</h5>
        <p class="text-muted mb-0">Seleccione otra ficha o verifique que existan agendas y planes de sesión registrados en el sistema.</p>
    </div>
    <?php endif; ?>

</div><!-- /.reporte-container -->
