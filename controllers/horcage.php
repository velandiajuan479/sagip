<?php
if (!file_exists('models/hormage.php') && file_exists(dirname(__DIR__) . '/models/hormage.php')) {
    chdir(dirname(__DIR__));
}
require_once('models/hormage.php');

$hormage = new Hormage();

@ini_set('session.cache_limiter', 'private_no_expire');
if (function_exists('session_cache_limiter')) {
    @session_cache_limiter('private_no_expire');
}
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
@header_remove("Pragma");
@header("Pragma: private");
@header("Cache-Control: private, max-age=10800, pre-check=10800");

// Recordar la ficha seleccionada desde Horarios o navegación como predeterminada
if (isset($_REQUEST['idfic'])) {
    if ($_REQUEST['idfic'] !== '' && $_REQUEST['idfic'] !== 'all') {
        $_SESSION['agenda_default_idfic'] = trim($_REQUEST['idfic']);
    } elseif ($_REQUEST['idfic'] === 'all') {
        $_SESSION['agenda_default_idfic'] = 'all';
    }
}

// Obtener parámetros de filtro
$idfic    = $_REQUEST['idfic'] ?? ($_SESSION['agenda_default_idfic'] ?? '');
$fas      = $_REQUEST['fas'] ?? '';
$idins    = $_REQUEST['idins'] ?? '';
$jornada  = $_REQUEST['jornada'] ?? '';
$fecini   = $_REQUEST['fecini'] ?? '';
$fecfin   = $_REQUEST['fecfin'] ?? '';
$buscar   = $_REQUEST['buscar'] ?? '';
$export   = $_REQUEST['export'] ?? '';
$ajax     = $_REQUEST['ajax'] ?? '';

// Control de Roles y Permisos en Agenda
$idper_actual = (int)($_SESSION['idper'] ?? ($_SESSION['idpergen'] ?? 0));
$idusu_actual = (int)($_SESSION['idusu'] ?? 0);
$esAdmin = in_array($idper_actual, [1, 2, 21, 28]);
$esInstructor = in_array($idper_actual, [7, 32, 42]);
$esAprendiz = in_array($idper_actual, [4, 8, 22, 23, 39, 41]);

// Alcance por rol (módulo HyA) para la cinta de alcance de la vista.
require_once 'models/hormacceso.php';
$hacceso = new HormAcceso();
$scopeCtx = $hacceso->getContexto();
$scopeFichas = $hacceso->getFichasPermitidas($scopeCtx);
$scopeRol = $hacceso->etiquetaRol($scopeCtx);
$scopeN = count($scopeFichas);
$scopeTotal = !empty($scopeCtx['is_admin']);

// Catálogos para selectores con restricción por perfil
$dtFichas       = $hormage->getAllFichas($idusu_actual, $esInstructor, $esAprendiz);
$dtFases        = $hormage->getDistinctFases();
$dtInstructores = $hormage->getDistinctInstructores();
$dtJornadas     = $hormage->getDistinctJornadas();

// Si el usuario es instructor, forzamos o limitamos el selector de instructores a sí mismo
if ($esInstructor) {
    $idins = $idusu_actual;
}

// Si no se ha seleccionado ficha pero hay fichas disponibles y no se pidió "all"
if ($idfic === '' && !empty($dtFichas)) {
    // Tomar la primera ficha por defecto para mostrar su programación inicial
    $idfic = $dtFichas[0]['idfic'];
    $_SESSION['agenda_default_idfic'] = $idfic;
}

// Si es aprendiz, verificar que la ficha solicitada corresponda a una de sus fichas asignadas
if ($esAprendiz && !empty($dtFichas)) {
    $fichasPermitidas = array_column($dtFichas, 'idfic');
    if (!in_array($idfic, $fichasPermitidas)) {
        $idfic = $fichasPermitidas[0];
        $_SESSION['agenda_default_idfic'] = $idfic;
    }
}

// Armar arreglo de filtros
$filters = [
    'idfic'   => $idfic,
    'fas'     => $fas,
    'idins'   => $idins,
    'jornada' => $jornada,
    'fecini'  => $fecini,
    'fecfin'  => $fecfin,
    'buscar'  => $buscar
];

// Bloqueos de seguridad a nivel de consulta
if ($esInstructor) {
    $filters['instructor_locked'] = $idusu_actual;
}
if ($esAprendiz && !empty($dtFichas)) {
    $filters['ficha_locked'] = array_column($dtFichas, 'idfic');
}

// Consultar registros
$dtAge = $hormage->getAgendas($filters);

// Mapa de horas asignadas en Horario por ficha y día (iddia valor 1042-1048)
// para pintarlas en las celdas L/M/X/J/V/S de la tabla.
$mapHorario = [];
$mapInstr = [];
try {
    require_once 'models/hormhrper.php';
    $idsFichasTabla = [];
    if (!empty($dtAge)) {
        foreach ($dtAge as $filaAge) {
            if (!empty($filaAge['idfic'])) {
                $idsFichasTabla[$filaAge['idfic']] = true;
            }
        }
    }
    if (!empty($idsFichasTabla)) {
        $mHoras = new Hormhrper();
        foreach ($mHoras->getHorasPorFichasDias(array_keys($idsFichasTabla)) as $hh) {
            $mapHorario[(string)$hh['idfic']][(string)$hh['iddia']][] = substr($hh['hinihor'], 0, 5) . '-' . substr($hh['hfinhor'], 0, 5);
            if (!empty($hh['nomusu'])) {
                $mapInstr[(string)$hh['idfic']][$hh['nomusu']] = true;
            }
        }
    }
} catch (Throwable $e) {
    $mapHorario = [];
    $mapInstr = [];
}

// Horarios asignados a la ficha (tabla horario): lo cargado en Horario
// también se visualiza en Agenda aunque no tenga planeación registrada.
$horariosFicha = [];
if (!empty($idfic) && $idfic !== 'all') {
    require_once 'models/hormhrper.php';
    $mHorFicha = new Hormhrper();
    $horariosFicha = $mHorFicha->getHorariosFiltrados(null, $idfic, null, null);
    $horariosFicha = $hacceso->filtrarPorFicha($horariosFicha, 'idfic', $scopeCtx);
}

$totalRegistros = count($dtAge);

// Detalle de la ficha seleccionada
$fichaDetalle = (!empty($idfic) && $idfic !== 'all') ? $hormage->getFichaDetalle($idfic) : null;

// Cálculo de Métricas KPI para el Dashboard de Agenda
$totalHorasAgenda = 0;
$competenciasAgenda = [];
$instructoresAgenda = [];

if (!empty($dtAge)) {
    foreach ($dtAge as $agItem) {
        if (isset($agItem['horcom']) && is_numeric($agItem['horcom'])) {
            $totalHorasAgenda += floatval($agItem['horcom']);
        }
        if (!empty($agItem['idcom'])) {
            $competenciasAgenda[$agItem['idcom']] = true;
        } elseif (!empty($agItem['descom'])) {
            $competenciasAgenda[$agItem['descom']] = true;
        }
        if (!empty($agItem['idins'])) {
            $instructoresAgenda[$agItem['idins']] = true;
        } elseif (!empty($agItem['nomusu'])) {
            $instructoresAgenda[$agItem['nomusu']] = true;
        }
    }
}

$kpiAgenda = [
    'idfic'               => $idfic,
    'nomfic'              => !empty($fichaDetalle['nompro']) ? $fichaDetalle['nompro'] : (!empty($fichaDetalle['nomfic']) ? $fichaDetalle['nomfic'] : 'Todas las Fichas'),
    'total_agendas'       => count($dtAge),
    'total_horas'         => $totalHorasAgenda,
    'total_competencias'  => count($competenciasAgenda),
    'total_instructores'  => count($instructoresAgenda)
];

// Compatibilidad con vistas legadas (vrpt.php, vrsins.php)
$dtPrg = null;
if ($fichaDetalle && !empty($fichaDetalle['codpro'])) {
    $dtPrg = [[
        'idfic'   => $fichaDetalle['idfic'] ?? $idfic,
        'nomfic'  => $fichaDetalle['nomfic'] ?? '',
        'codpro'  => $fichaDetalle['codpro'],
        'nompro'  => $fichaDetalle['nompro'],
        'verpro'  => $fichaDetalle['verpro'] ?? '1',
        'horlpro' => $fichaDetalle['horlpro'] ?? 0,
        'crelpro' => $fichaDetalle['crelpro'] ?? 0,
        'horppro' => $fichaDetalle['horppro'] ?? 0,
        'creppro' => $fichaDetalle['creppro'] ?? 0,
        'nomval'  => $fichaDetalle['nomjornada'] ?? 'Diurna',
        'redcon'  => 'Tecnologías de la Información y las Comunicaciones',
        'nomare'  => 'Sistemas y Desarrollo de Software'
    ]];
}

// ==========================================
// RESPUESTA AJAX
// ==========================================
if ($ajax == '1') {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'status' => 'success',
        'total'  => $totalRegistros,
        'ficha'  => $fichaDetalle,
        'agendas' => $dtAge
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// ==========================================
// EXPORTACIÓN A EXCEL
// ==========================================
if ($export === 'excel') {
    $filename = "Reporte_Agenda_Formacion_" . (!empty($idfic) ? "Ficha_" . $idfic . "_" : "") . date('Ymd_His') . ".xls";
    header("Content-Type: application/vnd.ms-excel; charset=utf-8");
    header("Content-Disposition: attachment; filename=\"$filename\"");
    header("Pragma: no-cache");
    header("Expires: 0");

    echo "\xEF\xBB\xBF"; // UTF-8 BOM
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <style>
            body { font-family: Arial, sans-serif; font-size: 11px; }
            .header-sena { background-color: #008F39; color: #ffffff; font-weight: bold; font-size: 16px; text-align: center; }
            .sub-header { background-color: #e8f5e9; color: #006828; font-weight: bold; font-size: 12px; }
            th { background-color: #008F39; color: #ffffff; font-weight: bold; border: 1px solid #006828; text-align: center; padding: 6px; }
            td { border: 1px solid #dcdcdc; padding: 5px; vertical-align: middle; }
            .center { text-align: center; }
            .check { color: #008F39; font-weight: bold; font-size: 13px; text-align: center; }
            .dash { color: #999999; text-align: center; }
            .badge-fase { font-weight: bold; color: #006828; text-align: center; }
        </style>
    </head>
    <body>
        <table>
            <tr>
                <td colspan="17" class="header-sena" height="40">SERVICIO NACIONAL DE APRENDIZAJE - SENA</td>
            </tr>
            <tr>
                <td colspan="17" class="sub-header" height="25">REPORTE DE PROGRAMACIÓN DE FORMACIÓN (AGENDA)</td>
            </tr>
            <?php if ($fichaDetalle): ?>
            <tr>
                <td colspan="3" style="font-weight: bold;">FICHA:</td>
                <td colspan="4"><?= htmlspecialchars($fichaDetalle['idfic']); ?></td>
                <td colspan="3" style="font-weight: bold;">PROGRAMA:</td>
                <td colspan="7"><?= htmlspecialchars(sena_utf8_clean($fichaDetalle['nompro'])); ?></td>
            </tr>
            <tr>
                <td colspan="3" style="font-weight: bold;">JORNADA:</td>
                <td colspan="14"><?= htmlspecialchars(sena_utf8_clean($fichaDetalle['nomjornada'])); ?></td>
            </tr>
            <?php endif; ?>
            <tr>
                <td colspan="17" style="color: #666666;">Generado el: <?= date('d/m/Y H:i:s'); ?> | Total registros: <?= $totalRegistros; ?></td>
            </tr>
            <tr><td colspan="17"></td></tr>
            <thead>
                <tr>
                    <th style="width: 80px;">FICHA</th>
                    <th style="width: 180px;">PROGRAMA</th>
                    <th style="width: 100px;">FASE</th>
                    <th style="width: 250px;">ACTIVIDAD PROYECTO</th>
                    <th style="width: 220px;">COMPETENCIA</th>
                    <th style="width: 250px;">RESULTADO APRENDIZAJE</th>
                    <th style="width: 90px;">HORAS DE LA COMPETENCIA</th>
                    <th style="width: 180px;">INSTRUCTOR</th>
                    <th style="width: 110px;">HORARIO</th>
                    <th style="width: 30px;">L</th>
                    <th style="width: 30px;">M</th>
                    <th style="width: 30px;">M</th>
                    <th style="width: 30px;">J</th>
                    <th style="width: 30px;">V</th>
                    <th style="width: 30px;">S</th>
                    <th style="width: 90px;">FECHA INICIO</th>
                    <th style="width: 90px;">FECHA FINAL</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($dtAge)): ?>
                    <?php foreach ($dtAge as $dta): ?>
                        <tr>
                            <td class="center"><strong><?= htmlspecialchars($dta['idfic'] ?? '-'); ?></strong></td>
                            <td><?= htmlspecialchars(sena_utf8_clean($dta['nompro'] ?? '-')); ?></td>
                            <td class="badge-fase"><?= htmlspecialchars($dta['fas'] ?? 'General'); ?></td>
                            <td><?= htmlspecialchars($dta['actpro'] ?? '-'); ?></td>
                            <td><?= htmlspecialchars($dta['descom'] ?? '-'); ?></td>
                            <td><?= htmlspecialchars($dta['nomres'] ?? '-'); ?></td>
                            <td class="center"><?= htmlspecialchars(($dta['horcom'] ?? 0) . ' hrs'); ?></td>
                            <td><?= htmlspecialchars($dta['nomusu'] ?? 'Sin asignar'); ?></td>
                            <td class="center"><?= htmlspecialchars($dta['hor'] ?? '-'); ?></td>
                            <td class="<?= !empty($dta['lun']) ? 'check' : 'dash'; ?>"><?= !empty($dta['lun']) ? '✓' : '-'; ?></td>
                            <td class="<?= !empty($dta['mar']) ? 'check' : 'dash'; ?>"><?= !empty($dta['mar']) ? '✓' : '-'; ?></td>
                            <td class="<?= !empty($dta['mie']) ? 'check' : 'dash'; ?>"><?= !empty($dta['mie']) ? '✓' : '-'; ?></td>
                            <td class="<?= !empty($dta['jue']) ? 'check' : 'dash'; ?>"><?= !empty($dta['jue']) ? '✓' : '-'; ?></td>
                            <td class="<?= !empty($dta['vie']) ? 'check' : 'dash'; ?>"><?= !empty($dta['vie']) ? '✓' : '-'; ?></td>
                            <td class="<?= !empty($dta['sab']) ? 'check' : 'dash'; ?>"><?= !empty($dta['sab']) ? '✓' : '-'; ?></td>
                            <td class="center"><?= !empty($dta['fecini']) ? date('d/m/Y', strtotime($dta['fecini'])) : '-'; ?></td>
                            <td class="center"><?= !empty($dta['fecfin']) ? date('d/m/Y', strtotime($dta['fecfin'])) : '-'; ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="17" class="center">No hay registros para los criterios seleccionados.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </body>
    </html>
    <?php
    exit;
}

// ==========================================
// EXPORTACIÓN A PDF / VISTA IMPRIMIBLE
// ==========================================
if ($export === 'pdf') {
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Reporte de Formación SENA - Agenda</title>
        <style>
            @page { size: letter landscape; margin: 10mm; }
            body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size: 10px; color: #333; margin: 0; padding: 15px; background: #fff; }
            .header-box { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #008F39; padding-bottom: 10px; margin-bottom: 15px; }
            .title-box h1 { margin: 0; color: #008F39; font-size: 20px; text-transform: uppercase; }
            .title-box p { margin: 3px 0 0; color: #666; font-size: 11px; }
            .badge-box { background: #e8f5e9; border: 1px solid #c8e6c9; border-radius: 6px; padding: 6px 12px; font-size: 11px; text-align: right; color: #006828; }
            .info-grid { display: table; width: 100%; margin-bottom: 15px; background: #fafafa; border: 1px solid #e0e0e0; border-radius: 6px; padding: 8px; }
            .info-row { display: table-row; }
            .info-cell { display: table-cell; padding: 4px 8px; font-size: 11px; }
            .info-cell strong { color: #008F39; }
            table.data-table { width: 100%; border-collapse: collapse; font-size: 9.5px; }
            table.data-table th { background-color: #008F39; color: #ffffff; font-weight: bold; text-align: left; padding: 6px 4px; border: 1px solid #006828; text-transform: uppercase; }
            table.data-table td { border: 1px solid #e0e0e0; padding: 5px 4px; vertical-align: middle; }
            table.data-table tr:nth-child(even) { background-color: #f9fbf9; }
            .text-center { text-align: center; }
            .check { color: #008F39; font-weight: bold; font-size: 12px; text-align: center; }
            .dash { color: #bbb; text-align: center; }
            .badge-pill { display: inline-block; padding: 2px 6px; border-radius: 12px; font-size: 9px; font-weight: bold; text-transform: uppercase; }
            .pill-blue { background: #e3f2fd; color: #1976d2; }
            .pill-green { background: #e8f5e9; color: #2e7d32; }
            .pill-amber { background: #fff8e1; color: #f57f17; }
            .pill-purple { background: #f3e5f5; color: #7b1fa2; }
            .pill-gray { background: #f5f5f5; color: #616161; }
            .no-print { margin-bottom: 15px; }
            .btn-print { background: #008F39; color: white; border: none; padding: 8px 16px; font-weight: bold; border-radius: 4px; cursor: pointer; font-size: 12px; }
            @media print { .no-print { display: none; } }
        </style>
    </head>
    <body>
        <div class="no-print">
            <button class="btn-print" onclick="window.print();">🖨️ Imprimir / Guardar como PDF</button>
            <button class="btn-print" style="background:#555; margin-left: 8px;" onclick="window.close();">Cerrar</button>
        </div>

        <div class="header-box">
            <div class="title-box">
                <h1>Reporte de Agenda de Formación</h1>
                <p>Servicio Nacional de Aprendizaje - SENA | Dirección de Formación Profesional</p>
            </div>
            <div class="badge-box">
                <strong>Fecha Emisión:</strong> <?= date('d/m/Y H:i:s'); ?><br>
                <strong>Total Actividades:</strong> <?= $totalRegistros; ?>
            </div>
        </div>

        <?php if ($fichaDetalle): ?>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-cell"><strong>Ficha:</strong> <?= htmlspecialchars($fichaDetalle['idfic']); ?></div>
                <div class="info-cell"><strong>Programa:</strong> <?= htmlspecialchars(sena_utf8_clean($fichaDetalle['nompro'])); ?></div>
            </div>
            <div class="info-row">
                <div class="info-cell"><strong>Jornada:</strong> <?= htmlspecialchars(sena_utf8_clean($fichaDetalle['nomjornada'])); ?></div>
            </div>
        </div>
        <?php endif; ?>

        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 55px;" class="text-center">Ficha</th>
                    <th style="width: 140px;">Programa</th>
                    <th style="width: 75px;">Fase</th>
                    <th style="width: 180px;">Actividad Proyecto</th>
                    <th style="width: 150px;">Competencia</th>
                    <th>Resultado Aprendizaje</th>
                    <th style="width: 65px;" class="text-center">Horas de la Competencia</th>
                    <th style="width: 120px;">Instructor</th>
                    <th style="width: 75px;" class="text-center">Horario</th>
                    <th style="width: 18px;" class="text-center">L</th>
                    <th style="width: 18px;" class="text-center">M</th>
                    <th style="width: 18px;" class="text-center">M</th>
                    <th style="width: 18px;" class="text-center">J</th>
                    <th style="width: 18px;" class="text-center">V</th>
                    <th style="width: 18px;" class="text-center">S</th>
                    <th style="width: 65px;" class="text-center">Inicio</th>
                    <th style="width: 65px;" class="text-center">Fin</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($dtAge)): ?>
                    <?php foreach ($dtAge as $dta): 
                        $fasName = trim($dta['fas'] ?? 'General');
                        $fasLower = mb_strtolower($fasName, 'UTF-8');
                        $pillClass = 'pill-gray';
                        if (strpos($fasLower, 'plan') !== false) $pillClass = 'pill-blue';
                        elseif (strpos($fasLower, 'ejec') !== false) $pillClass = 'pill-green';
                        elseif (strpos($fasLower, 'eval') !== false) $pillClass = 'pill-amber';
                        elseif (strpos($fasLower, 'an') !== false) $pillClass = 'pill-purple';
                    ?>
                        <tr>
                            <td class="text-center"><strong><?= htmlspecialchars($dta['idfic'] ?? '-'); ?></strong></td>
                            <td><?= htmlspecialchars(sena_utf8_clean($dta['nompro'] ?? '-')); ?></td>
                            <td><span class="badge-pill <?= $pillClass; ?>"><?= htmlspecialchars($fasName); ?></span></td>
                            <td><strong><?= htmlspecialchars($dta['actpro'] ?? '-'); ?></strong></td>
                            <td><?= htmlspecialchars($dta['descom'] ?? '-'); ?></td>
                            <td><?= htmlspecialchars($dta['nomres'] ?? '-'); ?></td>
                            <td class="text-center"><strong><?= htmlspecialchars(($dta['horcom'] ?? 0) . ' hrs'); ?></strong></td>
                            <td><?= htmlspecialchars($dta['nomusu'] ?? 'Sin asignar'); ?></td>
                            <td class="text-center"><?= htmlspecialchars($dta['hor'] ?? '-'); ?></td>
                            <td class="<?= !empty($dta['lun']) ? 'check' : 'dash'; ?>"><?= !empty($dta['lun']) ? '✓' : '-'; ?></td>
                            <td class="<?= !empty($dta['mar']) ? 'check' : 'dash'; ?>"><?= !empty($dta['mar']) ? '✓' : '-'; ?></td>
                            <td class="<?= !empty($dta['mie']) ? 'check' : 'dash'; ?>"><?= !empty($dta['mie']) ? '✓' : '-'; ?></td>
                            <td class="<?= !empty($dta['jue']) ? 'check' : 'dash'; ?>"><?= !empty($dta['jue']) ? '✓' : '-'; ?></td>
                            <td class="<?= !empty($dta['vie']) ? 'check' : 'dash'; ?>"><?= !empty($dta['vie']) ? '✓' : '-'; ?></td>
                            <td class="<?= !empty($dta['sab']) ? 'check' : 'dash'; ?>"><?= !empty($dta['sab']) ? '✓' : '-'; ?></td>
                            <td class="text-center"><?= !empty($dta['fecini']) ? date('d/m/Y', strtotime($dta['fecini'])) : '-'; ?></td>
                            <td class="text-center"><?= !empty($dta['fecfin']) ? date('d/m/Y', strtotime($dta['fecfin'])) : '-'; ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="17" class="text-center" style="padding: 20px;">No se encontraron registros de programación con los filtros aplicados.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <script>
            // Disparar diálogo de impresión automáticamente
            window.addEventListener('load', () => {
                setTimeout(() => { window.print(); }, 400);
            });
        </script>
    </body>
    </html>
    <?php
    exit;
}
?>
