<?php 
// Configurar zona horaria de Colombia
date_default_timezone_set('America/Bogota');
require_once 'controllers/horchor.php'; 
// Asegurar configuración del footer (versión/actualización)
require_once __DIR__ . '/../controllers/ccof.php';
// Si $val no viene con datos válidos, intentar cargar configuración por defecto (idcof=1)
if ((!isset($val) || !is_array($val) || empty($val) || !isset($val[0]['versoft'])) ) {
    require_once __DIR__ . '/../models/mcof.php';
    $mcofFooter = new Mcof();
    $mcofFooter->setIdcof(1);
    $valFallback = $mcofFooter->getOne();
    if (is_array($valFallback) && !empty($valFallback) && isset($valFallback[0]['versoft'])) {
        $val = $valFallback;
    }
}

// ==========================================
// VALIDACIÓN DE PERMISOS POR PERFILES - INICIO
// ==========================================

// Obtener ID del usuario actual
$idusu_actual = $_SESSION["idusu"] ?? null;

// Semana inicial con datos: en carga inicial (sin semana elegida), si la
// semana actual no tiene horarios se usa la última semana con datos para
// que las cards muestren información desde el primer vistazo.
if (!isset($_POST['semana_selector']) && !isset($_REQUEST['semana_selector'])) {
    try {
        $tmpHorSem = new Hormhor();
        $semActual = function_exists('calcularSemanaISO') ? calcularSemanaISO() : null;
        if ($semActual && $tmpHorSem->contarHorariosSemana($semActual) === 0) {
            $ultSem = $tmpHorSem->getUltimaSemanaConHorarios();
            if ($ultSem) {
                $_POST['semana_selector'] = $ultSem;
                $_REQUEST['semana_selector'] = $ultSem;
            }
        }
    } catch (Throwable $e) {
        // Sin fallback: se usa la semana actual.
    }
}

// Validar que el usuario esté autenticado
if (!$idusu_actual) {
    echo '<div class="alert alert-danger">Usuario no autenticado.</div>';
    exit;
}

// Obtener perfil del usuario
$perfil_usuario = obtenerPerfilUsuario($idusu_actual);
if (!$perfil_usuario) {
    echo '<div class="alert alert-danger">No se pudo obtener el perfil del usuario.</div>';
    exit;
}

$idper_actual = $perfil_usuario['idper'] ?? ($_SESSION['idper'] ?? null);

// Obtener restricciones de interfaz
$restricciones = obtenerRestriccionesInterfaz($idusu_actual);

// ==========================================
// INICIALIZACIÓN DE MODELO Y DATOS DE HORARIOS
// ==========================================
$mhor = new hOrmhor();

// Áreas asignadas al coordinador
$dar = $mhor->getAreasCoordinador($idusu_actual, $idper_actual) ?: [];

// Captura de filtros actuales
$idare_actual = $_POST['idare'] ?? $_REQUEST['idare'] ?? null;
$idfic_actual = $_POST['idfic'] ?? $_REQUEST['idfic'] ?? null;
$idusu_filtro = $_POST['idusu_filtro'] ?? $_POST['idusu'] ?? $_REQUEST['idusu'] ?? null;
$fecha_inicio_filtro = $_POST['fecha_inicio'] ?? $_REQUEST['fecha_inicio'] ?? null;
$fecha_fin_filtro = $_POST['fecha_fin'] ?? $_REQUEST['fecha_fin'] ?? null;

// Cargar fichas permitidas para el coordinador y área seleccionada
$dfi = $mhor->getFichasCoordinador($idusu_actual, $idare_actual, $idper_actual) ?: [];

// Información de Programa y Proyecto Formativo de la ficha actual (si está seleccionada)
$infoFichaActual = !empty($idfic_actual) ? $mhor->getInfoFicha($idfic_actual) : null;

// Depurar y ordenar el listado de fichas para el buscador (evitar desorden y duplicados)
$dfiUnicas = [];
foreach ($dfi as $ficha) {
    if (isset($ficha['idfic'])) {
        $dfiUnicas[$ficha['idfic']] = $ficha;
    }
}
ksort($dfiUnicas, SORT_NUMERIC);
$dfi = array_values($dfiUnicas);
$dft = $dfi;

// Días de la semana, instructores, aulas y tipos de eventos
$ddi = $mhor->getDia() ?: [];
$din = $mhor->getInstructoresCoordinador($idusu_actual, $idare_actual, $idfic_actual, $idper_actual) ?: [];
$dau = $mhor->getAula() ?: [];
$tiposEventos = $mhor->getTiposEventos() ?: [];

// Período por defecto para el dashboard: mes/año actual (información real cargada)
$mes_dashboard = isset($_POST['mes']) && is_numeric($_POST['mes']) ? (int)$_POST['mes'] : (int)date('n');
$anio_dashboard = isset($_POST['anio']) && is_numeric($_POST['anio']) ? (int)$_POST['anio'] : (int)date('Y');

    // Cargar métricas reales para el Dashboard de cabecera
    $esPerfilAprendiz = in_array((int)($perfil_usuario['idper'] ?? 0), [4, 8, 22, 23, 39, 41]);
    if (!$esPerfilAprendiz) {
        $metricas_dashboard = $mhor->getMetricasDashboard([
            'idusu_coord' => $idusu_actual,
            'idper' => 2,
            'idare' => null,
            'idfic' => null,
            'idusu_inst' => null,
            'fecha_inicio' => null,
            'fecha_fin' => null,
            'mes' => $mes_dashboard,
            'anio' => $anio_dashboard
        ]);
        // Totales globales para "asignados / registrados" (áreas e instructores)
        $totales_asignacion = $mhor->getTotalesAsignacion();
        // Información adicional: fichas, programas, semanas y días
        $info_dashboard_adicional = $mhor->getInfoDashboardAdicional($mes_dashboard, $anio_dashboard);
    } else {
        $metricas_dashboard = null;
        $totales_asignacion = null;
        $info_dashboard_adicional = null;
    }

// Para perfiles restringidos de aprendices, obtener ficha asignada
$ficha_asignada = null;
if ($esPerfilAprendiz) {
    $ficha_asignada = obtenerFichaAsignadaUsuario($idusu_actual);

    
    if (!$ficha_asignada) {
        echo '<div class="alert alert-warning">No tienes ficha asignada. Contacta al administrador.</div>';
        exit;
    }
    
    // Filtrar datos para mostrar solo la ficha asignada
    if (isset($dft) && is_array($dft)) {
        $dft = array_filter($dft, function($ficha) use ($ficha_asignada) {
            return $ficha['idfic'] == $ficha_asignada;
        });
    }
}
// ==========================================
// VALIDACIÓN DE PERMISOS POR PERFILES - FIN
// ==========================================

// Normalizar los días para evitar Undefined variable / foreach con string.
// El controlador puede entregar un arreglo, JSON o no entregar el dato.
if (!isset($ddi)) {
    $ddi = [];
} elseif (is_string($ddi)) {
    $ddiDecodificado = json_decode($ddi, true);
    $ddi = is_array($ddiDecodificado) ? $ddiDecodificado : [];
} elseif (!is_array($ddi)) {
    $ddi = [];
}
?>
<?php
// Nota: El footer espera que $val sea un arreglo [0] con claves 'versoft', 'actsoft', etc.
// Si $val viene con otro tipo (int, null, etc.) desde algún controlador, lo limpiamos para evitar warnings.
if (isset($val) && (!is_array($val) || !isset($val[0]) || !is_array($val[0]))) {
    $val = [];
}
?>
<?php
// Asegurar que la función pintit exista aunque el controlador termine antes
if (!function_exists('pintit')) {
    function pintit($ddi)
    {
        if (!is_array($ddi)) {
            return;
        }

        foreach ($ddi as $d) {
            if (is_array($d) && isset($d['nomval'])) {
                // Encabezado abreviado L M M J V S D; nombre completo en tooltip.
                $nombre = (string)$d['nomval'];
                $letra = function_exists('mb_substr')
                    ? mb_substr($nombre, 0, 1, 'UTF-8')
                    : substr($nombre, 0, 1);
                echo '<th class="text-center" title="'.htmlspecialchars($nombre).'">'.htmlspecialchars($letra).'</th>';
            }
        }
    }
}
?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<?php
if (!function_exists('obtenerNombreMes')) {
    function obtenerNombreMes($mes) {
        $meses = [
            1 => 'Enero',
            2 => 'Febrero',
            3 => 'Marzo',
            4 => 'Abril',
            5 => 'Mayo',
            6 => 'Junio',
            7 => 'Julio',
            8 => 'Agosto',
            9 => 'Septiembre',
            10 => 'Octubre',
            11 => 'Noviembre',
            12 => 'Diciembre'
        ];

        $mes = (int)$mes;
        return $meses[$mes] ?? '';
    }
}

/**
 * Obtiene la fecha actual en zona horaria de Colombia
 * @return DateTime Fecha actual en Colombia
 */
function obtenerFechaColombia() {
    // Configurar zona horaria de Colombia
    $zonaColombia = new DateTimeZone('America/Bogota');
    $fechaColombia = new DateTime('now', $zonaColombia);
    
    return $fechaColombia;
}

/**
 * Calcula la semana ISO de una fecha específica (sincronizada con input type="week")
 * @param DateTime $fecha Fecha para calcular la semana
 * @return string Semana en formato YYYY-WW
 */
function calcularSemanaISO($fecha = null) {
    if (!$fecha) {
        $fecha = obtenerFechaColombia();
    }
    
    // Usar el método estándar de PHP que está sincronizado con input type="week"
    $timestamp = $fecha->getTimestamp();
    $semana = date('W', $timestamp);
    $año = date('Y', $timestamp);
    
    // Manejar casos especiales de cambio de año según estándar ISO 8601
    if ($semana == 1 && $fecha->format('n') == 12) {
        // Si es diciembre pero la semana es 1, pertenece al año siguiente
        $año++;
    } elseif ($semana >= 52 && $fecha->format('n') == 1) {
        // Si es enero pero la semana es 52+, pertenece al año anterior
        $año--;
    }
    
    return sprintf('%04d-%02d', $año, $semana);
}

/**
 * Formatea una semana para mostrar en formato legible
 * @param string $semanaSelector Semana en formato YYYY-WW
 * @return string Semana formateada
 */
function formatearSemanaPHP($semanaSelector) {
    if (!$semanaSelector) {
        // Si no hay selector, calcular y formatear la semana actual
        $semanaActual = calcularSemanaISO();
        return formatearSemanaPHP($semanaActual);
    }
    
    try {
        // Parsear el selector de semana (formato: YYYY-WW o YYYY-WWW)
        if (preg_match('/^(\d{4})-W?(\d{2,3})$/', $semanaSelector, $matches)) {
            $año = (int)$matches[1];
            $semana = (int)$matches[2];
            
            // Configurar zona horaria de Colombia
            $zonaColombia = new DateTimeZone('America/Bogota');
            
            // Calcular el 4 de enero del año (según estándar ISO 8601)
            $cuatroEnero = new DateTime("$año-01-04", $zonaColombia);
            $diaSemanaCuatroEnero = (int)$cuatroEnero->format('N'); // 1=Lunes, 7=Domingo
            
            // Calcular el primer día de la semana 1 (lunes)
            $primerDiaSemana1 = clone $cuatroEnero;
            $diasHastaLunes = $diaSemanaCuatroEnero - 1;
            $primerDiaSemana1->sub(new DateInterval("P{$diasHastaLunes}D"));
            
            // Calcular el lunes de la semana específica
            $lunesSemana = clone $primerDiaSemana1;
            $lunesSemana->add(new DateInterval('P' . (($semana - 1) * 7) . 'D'));
            
            // Calcular el domingo de la semana
            $domingoSemana = clone $lunesSemana;
            $domingoSemana->add(new DateInterval('P6D'));
            
            // Formatear fechas usando zona horaria de Colombia
            $inicioFormateado = $lunesSemana->format('d/m/Y');
            $finFormateado = $domingoSemana->format('d/m/Y');
            
            return "Semana $semana ($inicioFormateado - $finFormateado)";
        }
    } catch (Exception $e) {
        error_log("Error formateando semana PHP: " . $e->getMessage());
    }
    
    // Si no se puede parsear, calcular la semana actual
    $semanaActual = calcularSemanaISO();
    return formatearSemanaPHP($semanaActual);
}
?>

<!-- Estilos necesarios para los dropdowns(PASAR A CSS) -->
<!-- Inserción de horarios -->
<div class="conte">
    <?php echo titulo2("<i class='" . $icono . "'></i> Horarios", 1); ?>
    <?php
    // Alcance por rol (módulo HyA) para la cinta de alcance.
    require_once("models/hormacceso.php");
    $haccesoHvh = new HormAcceso();
    $scopeCtxHvh = $haccesoHvh->getContexto();
    $scopeRol = $haccesoHvh->etiquetaRol($scopeCtxHvh);
    $scopeFichasHvh = $haccesoHvh->getFichasPermitidas($scopeCtxHvh);
    $scopeN = count($scopeFichasHvh);
    $scopeTotal = !empty($scopeCtxHvh['is_admin']);
    require_once("views/vscopehya.php");
    ?>

    <!-- Dashboard de Horarios y Carga Académica (100% Datos Reales) -->
    <?php if (!empty($metricas_dashboard)): ?>
    <div class="row g-3 mb-4 mt-1">
        <!-- Card 1: Total en sistema -->
        <div class="col-12 col-md-4">
                <div class="card border-0 shadow-sm rounded-3 h-100">
                    <div class="card-body p-3 pb-2">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <span class="text-muted fw-semibold small text-uppercase">Horas en sistema</span>
                                <div class="d-flex align-items-baseline">
                                    <h3 class="fw-bold mb-0 text-dark"><?= number_format($metricas_dashboard['total_horas'], 1) ?></h3>
                                    <span class="ms-1 text-muted">hrs</span>
                                </div>
                            </div>
                            <span class="rounded-3 p-2 bg-primary bg-opacity-10 text-primary">
                                <i class="fa-solid fa-layer-group fa-lg"></i>
                            </span>
                        </div>
                    </div>
                    <div class="card-footer bg-light border-0 d-flex justify-content-between align-items-center small">
                        <span class="text-primary"><i class="fa-solid fa-arrow-trend-up me-1"></i>Registradas</span>
                        <span class="text-muted"><?= $metricas_dashboard['total_fichas'] ?> fichas · <?= $metricas_dashboard['total_aulas'] ?> ambientes</span>
                        <a href="home.php?pg=1517" class="text-decoration-none"><i class="fa-solid fa-user-clock me-1"></i>Horario por Persona</a>
                    </div>
                </div>
        </div>

        <!-- Card 2: Instructores al día -->
        <div class="col-12 col-md-4">
                <div class="card border-0 shadow-sm rounded-3 h-100">
                    <div class="card-body p-3 pb-2">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <span class="text-muted fw-semibold small text-uppercase">Instructores al día</span>
                                <div class="d-flex align-items-baseline">
                                    <h3 class="fw-bold mb-0 text-dark"><?= $metricas_dashboard['instructores_cumplen'] ?></h3>
                                    <span class="ms-1 text-muted">cumpliendo</span>
                                </div>
                            </div>
                            <span class="rounded-3 p-2 bg-success bg-opacity-10 text-success">
                                <i class="fa-solid fa-circle-play fa-lg"></i>
                            </span>
                        </div>
                    </div>
                    <div class="card-footer bg-light border-0 d-flex justify-content-between align-items-center small">
                        <span class="text-success"><i class="fa-solid fa-circle me-1"></i>En estándar (<?= $metricas_dashboard['porcentaje_cumplimiento'] ?>%)</span>
                        <span class="text-muted">De <?= $metricas_dashboard['total_instructores'] ?> docentes</span>
                        <a href="home.php?pg=1517" class="text-decoration-none"><i class="fa-solid fa-user-clock me-1"></i>Horario por Persona</a>
                    </div>
                </div>
        </div>

        <!-- Card 3: Instructores en déficit -->
        <div class="col-12 col-md-4">
                <div class="card border-0 shadow-sm rounded-3 h-100">
                    <div class="card-body p-3 pb-2">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <span class="text-muted fw-semibold small text-uppercase">Instructores en déficit</span>
                                <div class="d-flex align-items-baseline">
                                    <h3 class="fw-bold mb-0 text-dark"><?= $metricas_dashboard['instructores_deficit'] ?></h3>
                                    <span class="ms-1 text-muted">en déficit</span>
                                </div>
                            </div>
                            <span class="rounded-3 p-2 bg-danger bg-opacity-10 text-danger">
                                <i class="fa-solid fa-circle-pause fa-lg"></i>
                            </span>
                        </div>
                    </div>
                    <div class="card-footer bg-light border-0 d-flex justify-content-between align-items-center small">
                        <span class="text-danger"><i class="fa-solid fa-rotate-left me-1"></i>Fuera de estándar</span>
                        <span class="text-muted">Sobre carga: <?= $metricas_dashboard['instructores_sobre'] ?></span>
                        <a href="home.php?pg=1517" class="text-decoration-none"><i class="fa-solid fa-user-clock me-1"></i>Horario por Persona</a>
                    </div>
                </div>
        </div>

        <!-- Card 4: Horas por instructor (Carga Horaria) -->
        <div class="col-12 col-md-4">
                <div class="card border-0 shadow-sm rounded-3 h-100">
                    <div class="card-body p-3 pb-2">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <span class="text-muted fw-semibold small text-uppercase">Horas por instructor</span>
                                <div class="d-flex align-items-baseline">
                                    <h3 class="fw-bold mb-0 text-dark"><?= $info_dashboard_adicional['total_horas_mes'] ?></h3>
                                    <span class="ms-1 text-muted">hrs · <?= $info_dashboard_adicional['total_instructores_con_horas'] ?> instructores</span>
                                </div>
                            </div>
                            <span class="rounded-3 p-2 bg-warning bg-opacity-10 text-warning">
                                <i class="fa-solid fa-clock fa-lg"></i>
                            </span>
                        </div>
                        <div class="mt-1 small text-muted">
                            <span><i class="fa-solid fa-book me-1"></i><?= $info_dashboard_adicional['total_fichas'] ?> fichas · <?= count($info_dashboard_adicional['programas']) ?> programas</span> ·
                            <span><i class="fa-solid fa-calendar-week me-1"></i><?= $info_dashboard_adicional['total_semanas'] ?> semanas</span> ·
                            <span><i class="fa-solid fa-calendar-day me-1"></i><?= $info_dashboard_adicional['total_dias'] ?> días</span>
                        </div>
                        <div class="mt-1 small">
                            <?php if (!empty($info_dashboard_adicional['programas'])): ?>
                                <span class="badge bg-info"><?= implode(' · ', array_slice($info_dashboard_adicional['programas'], 0, 3)) ?></span>
                                <?php if (count($info_dashboard_adicional['programas']) > 3): ?>
                                    <span class="badge bg-secondary">+<?= count($info_dashboard_adicional['programas']) - 3 ?></span>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($info_dashboard_adicional['horas_por_instructor'])): ?>
                        <div class="mt-1 small">
                            <span class="text-muted fw-semibold">Detalle de horas:</span>
                            <a href="home.php?pg=1517" class="btn btn-success btn-sm float-end"><i class="fa-solid fa-arrow-right me-1"></i>Ver Horario por Persona</a>
                            <div class="clearfix"></div>
                            <?php $top = array_slice($info_dashboard_adicional['horas_por_instructor'], 0, 5); foreach ($top as $hi): ?>
                                <div class="d-block text-muted">
                                    <?php if (!empty($hi['idusu'])): ?>
                                    <a href="home.php?pg=1517&idusu=<?= urlencode($hi['idusu']); ?>" class="text-decoration-none" title="Ver horario de <?= htmlspecialchars($hi['nomusu'] ?? ''); ?>">
                                        <small><?= htmlspecialchars($hi['nomusu'] ?? 'N/A') ?></small>
                                    </a>
                                    <?php else: ?>
                                    <small><?= htmlspecialchars($hi['nomusu'] ?? 'N/A') ?></small>
                                    <?php endif; ?>
                                    <span class="float-end"><?= number_format((float)$hi['total_horas'], 1) ?> hrs</span>
                                </div>
                            <?php endforeach; ?>
                            <?php if (count($info_dashboard_adicional['horas_por_instructor']) > 5): ?>
                                <small class="text-muted">+<?= count($info_dashboard_adicional['horas_por_instructor']) - 5 ?> más</small>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                   
        </div>
    </div>
    <!-- Fin Dashboard de Horarios -->
    <?php endif; ?>

    <?php if (!empty($metricas_dashboard) && !empty($totales_asignacion)): ?>
    <!-- Asignación de la coordinación: asignados / registrados -->
    <div class="row g-3 mb-4 mt-1">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body p-3 d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted fw-semibold small text-uppercase">Áreas asignadas</span>
                        <h4 class="fw-bold mb-0 text-dark"><?= count($dar) ?> <small class="text-muted fs-6">de <?= (int)$totales_asignacion['total_areas'] ?></small></h4>
                        <a href="home.php?pg=1525" class="small text-decoration-none">Ver áreas <i class="fa-solid fa-arrow-right"></i></a>
                    </div>
                    <span class="p-2 rounded-circle">
                        <i class="fa-solid fa-layer-group fa-lg"></i>
                    </span>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body p-3 d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted fw-semibold small text-uppercase">Instructores asignados</span>
                        <h4 class="fw-bold mb-0 text-dark"><?= (int)$metricas_dashboard['total_instructores'] ?> <small class="text-muted fs-6">de <?= (int)$totales_asignacion['total_instructores'] ?></small></h4>
                        <a href="home.php?pg=1517" class="small text-decoration-none">Ver horarios por persona <i class="fa-solid fa-arrow-right"></i></a>
                    </div>
                    <span class="p-2 rounded-circle">
                        <i class="fa-solid fa-users fa-lg"></i>
                    </span>
                </div>
            </div>
        </div>
    </div>
    <!-- Fin asignación de la coordinación -->
    <?php endif; ?>

    <!-- Agregar / Asignar Horarios -->
    <?php if ($restricciones['mostrar_botones_crear']): ?>
    <div class="inser card shadow-sm mb-4 border-0">
        <div class="card-header py-2">
            <h6 class="mb-0 fw-bold text-success">
                <i class="fa-solid fa-plus-circle me-2"></i>Agregar / Asignar Horario
            </h6>
        </div>
        <form id="frmins" action="home.php?pg=<?= $pg; ?>" method="POST" class="card-body">
            <div class="row justify-content-end">
                <!-- Sección: Ficha -->
                <div class="col-12">
                    <h6 class="text-success fw-bold border-bottom pb-2 mb-1">
                        <i class="fa-solid fa-1 me-1"></i>Ficha y días
                    </h6>
                </div>
                <!-- Búsqueda de Ficha -->
                <div class="form-group col-md-6">
                    <label for="search_idfic">Buscar Ficha</label>
                    <div class="position-relative">
                        <input type="text" 
                               id="search_idfic" 
                               class="form-control search-input" 
                               placeholder="Escriba para buscar ficha..."
                               autocomplete="off">
                        <input type="hidden" name="idfic" id="idfic" value="<?= $idfic ?? '' ?>">
                        
                        <div id="dropdown_idfic" class="dropdown-menu w-100">
                            <?php if ($dfi): ?>
                                <?php foreach ($dfi as $de): ?>
                                    <div class="dropdown-item-custom dropdown-item" 
                                         data-value="<?= $de['idfic']; ?>"
                                         data-text="<?= $de['idfic']; ?> - <?= $de['nomfic']; ?> <?= $de['nomval']; ?>">
                                        <strong><?= $de['idfic']; ?></strong> - <?= $de['nomfic']; ?> 
                                        <small class="text-muted">(<?= $de['nomval']; ?>)</small>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Selección de Días -->
                <div class="form-group col-md-6">
                    <label for="iddia">Día</label><br>
                    <?php if (is_array($ddi) && !empty($ddi)): ?>
                        <?php foreach ((is_array($ddi) ? $ddi : []) as $de): ?>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       name="iddia[]" 
                                       id="dia_<?= $de['idval'] ?>"
                                       value="<?= $de['idval'] ?>"
                                       <?= (!empty($dat) && $iddia == $de['idval']) ? 'checked' : '' ?> />
                                <label class="form-check-label" for="dia_<?= $de['idval'] ?>">
                                    <?= htmlspecialchars($de['nomval']) ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Sección: Período -->
                <div class="col-12 mt-2">
                    <h6 class="text-success fw-bold border-bottom pb-2 mb-1">
                        <i class="fa-solid fa-2 me-1"></i>Período y fechas
                    </h6>
                </div>

                <!-- Checkbox para rango por evento -->
                <div class="form-group col-12">
                    <div class="form-check">
                        <input class="form-check-input" 
                               type="checkbox" 
                               name="rango_por_evento" 
                               id="rango_por_evento" 
                               value="1"
                               onchange="toggleRangoPorEvento()">
                        <label class="form-check-label" for="rango_por_evento">
                            <strong><i class="fa-solid fa-calendar-alt me-2"></i>Rango por evento del calendario académico</strong>
                        </label>
                        <small class="form-text text-muted d-block mt-1">
                            Marque esta opción para seleccionar automáticamente el rango de fechas desde el calendario académico
                        </small>
                    </div>
                </div>

                <!-- Campos de rango por evento (inicialmente ocultos) -->
                <div id="campos_rango_evento" class="col-12 d-none">
                    <div class="card mt-3">
                        <div class="card-header text-white">
                            <h6 class="mb-0">Selección de Evento del Calendario</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <!-- Tipo de Evento -->
                                <div class="form-group col-md-6">
                                    <label for="tipo_evento">Tipo de Evento</label>
                                    <select name="tipo_evento" id="tipo_evento" class="form-select" onchange="cargarEventosPorTipo()">
                                        <option value="">Seleccione el tipo de evento...</option>
                                        <?php if (!empty($tiposEventos)): ?>
                                            <?php foreach ($tiposEventos as $tipo): ?>
                                                <option value="<?= $tipo ?>"><?= ucfirst(str_replace('_', ' ', $tipo)) ?></option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                    <small class="form-text text-muted">Seleccione el tipo de evento del calendario</small>
                                </div>

                                <!-- Evento Específico -->
                                <div class="form-group col-md-6">
                                    <label for="evento_calendario">Evento del Calendario</label>
                                    <select name="evento_calendario" id="evento_calendario" class="form-select" onchange="seleccionarEvento()">
                                        <option value="">Primero seleccione el tipo de evento...</option>
                                    </select>
                                    <small class="form-text text-muted">Seleccione el evento específico</small>
                                </div>

                                <!-- Información del evento seleccionado -->
                                <div class="form-group col-12">
                                    <div id="info_evento" class="alert alert-info d-none">
                                        <small>
                                            <i class="fa-solid fa-info-circle me-1"></i>
                                            <strong>Evento seleccionado:</strong> 
                                            <span id="titulo_evento">-</span>
                                            <br>
                                            <strong>Período:</strong> 
                                            <span id="fecha_inicio_evento">-</span>
                                            <br>
                                            <strong>Trimestre:</strong> 
                                            <span id="trimestre_evento">-</span>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Fecha de Inicio -->
                <div class="form-group col-md-6" id="campo_fecha_inicio">
                    <label for="fecha_inicio">Fecha de Inicio <span class="text-danger">*</span></label>
                    <input type="date" 
                           name="fecha_inicio" 
                           id="fecha_inicio" 
                           class="form-control" 
                           value="<?= obtenerFechaColombia()->format('Y-m-d') ?>"
                           required>
                    <small class="form-text text-muted">Fecha desde cuando inicia el horario (puede ser anterior a hoy)</small>
                </div>

                <!-- Fecha de Fin -->
                <div class="form-group col-md-6" id="campo_fecha_fin">
                    <label for="fecha_fin">Fecha de Fin <span class="text-danger">*</span></label>
                    <input type="date" 
                           name="fecha_fin" 
                           id="fecha_fin" 
                           class="form-control" 
                           value="<?= obtenerFechaColombia()->modify('+1 month')->format('Y-m-d') ?>"
                           required>
                    <small class="form-text text-muted">Fecha hasta cuando es válido el horario (puede ser anterior a hoy)</small>
                </div>

                <!-- Fecha Específica para Horario Individual -->
                <div class="form-group col-md-6 d-none" id="campo_fecha_especifica">
                    <label for="fecha_especifica">Fecha Específica <span class="text-danger">*</span></label>
                    <input type="date" 
                           name="fecha_especifica" 
                           id="fecha_especifica" 
                           class="form-control">
                    <small class="form-text text-muted">Seleccione la fecha específica para el horario individual</small>
                </div>

                <!-- Modo de Horario Individual -->
                <div class="form-group col-12">
                    <div class="form-check">
                        <input type="checkbox" 
                               class="form-check-input" 
                               id="modo_individual" 
                               name="modo_individual"
                               onchange="toggleModoIndividual()">
                        <label class="form-check-label" for="modo_individual">
                            <strong>Modo Individual:</strong> Agregar horario para una fecha específica únicamente
                        </label>
                    </div>
                    <small class="form-text text-muted">Marque esta opción si desea agregar el horario solo para una fecha específica en lugar de todo el rango</small>
                </div>

                <!-- Información del lapso -->
                <div class="form-group col-12">
                    <div class="alert alert-info d-none" id="info_lapso">
                        <small>
                            <i class="fa-solid fa-info-circle me-1"></i>
                            <strong>Lapso seleccionado:</strong> 
                            <span id="formatoInicio">-</span> al <span id="formatoFin">-</span>
                            (<span id="duracion_lapso">-</span> días)
                        </small>
                    </div>
                </div>

                <!-- Sección: Recursos -->
                <div class="col-12 mt-2">
                    <h6 class="text-success fw-bold border-bottom pb-2 mb-1">
                        <i class="fa-solid fa-3 me-1"></i>Instructor y aula
                    </h6>
                </div>

                <!-- Búsqueda de Instructor -->
                <div class="form-group col-md-6">
                    <label for="search_idusu">Buscar Instructor</label>
                    <div class="position-relative">
                        <input type="text" 
                               id="search_idusu" 
                               class="form-control search-input" 
                               placeholder="Escriba para buscar instructor..."
                               autocomplete="off">
                        <input type="hidden" name="idusu" id="idusu" value="<?= $idusu ?? '' ?>">
                        
                        <div id="dropdown_idusu" class="dropdown-menu w-100">
                            <?php if ($din): ?>
                                <?php foreach ($din as $de): ?>
                                    <div class="dropdown-item-custom dropdown-item" 
                                         data-value="<?= $de['idusu']; ?>"
                                         data-text="<?= strtoupper($de['nomusu']) . ' - ' . $de['ndocusu']; ?>">
                                        <strong><?= strtoupper($de['nomusu']); ?></strong> 
                                        <small class="text-muted">(<?= $de['ndocusu']; ?>)</small>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Búsqueda de Aula -->
                <div class="form-group col-md-6">
                    <label for="search_idaul">Buscar Aula</label>
                    <div class="position-relative">
                        <input type="text" 
                               id="search_idaul" 
                               class="form-control search-input" 
                               placeholder="Escriba para buscar aula..."
                               autocomplete="off">
                        <input type="hidden" name="idaul" id="idaul" value="<?= $idaul ?? '' ?>">
                        
                        <div id="dropdown_idaul" class="dropdown-menu w-100">
                            <?php if ($dau): ?>
                                <?php foreach ($dau as $de): ?>
                                    <div class="dropdown-item-custom dropdown-item" 
                                         data-value="<?= $de['idaul']; ?>"
                                         data-text="<?= $de['idaul'] . '. ' . $de['nomaul']; ?>">
                                        <strong><?= $de['idaul']; ?></strong> - <?= $de['nomaul']; ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Rango Horario: De qué hora a qué hora -->
                <div class="form-group col-12 my-2">
                    <div class="card p-3 border-0 shadow-sm rounded-3">
                        <label class="form-label fw-bold text-dark mb-2">
                            <i class="fa-solid fa-clock me-1 text-success"></i>Horario de Asignación (¿De qué hora a qué hora?) <span class="text-danger">*</span>
                        </label>
                        <div class="row g-2 align-items-end">
                            <!-- Selector rápido de jornada -->
                            <div class="col-md-4">
                                <label for="preset_jornada" class="form-label small text-muted">Horario Sugerido / Jornada</label>
                                <select id="preset_jornada" class="form-select form-select-sm" onchange="aplicarPresetJornada(this.value)">
                                    <option value="manana" selected>Mañana (06:00 - 12:00) [6 horas]</option>
                                    <option value="tarde">Tarde (12:00 - 18:00) [6 horas]</option>
                                    <option value="noche">Noche (18:00 - 22:00) [4 horas]</option>
                                    <option value="mixta">Mixta (06:00 - 14:00) [8 horas]</option>
                                    <option value="custom">Personalizado...</option>
                                </select>
                            </div>
                            <!-- Hora Inicio -->
                            <div class="col-md-3">
                                <label for="hinihor" class="form-label small fw-bold text-dark">Hora Inicio <span class="text-danger">*</span></label>
                                <input type="time" name="hinihor" id="hinihor" class="form-control" value="06:00" required onchange="calcularHorasBloqueFormulario()">
                            </div>
                            <!-- Hora Fin -->
                            <div class="col-md-3">
                                <label for="hfinhor" class="form-label small fw-bold text-dark">Hora Fin <span class="text-danger">*</span></label>
                                <input type="time" name="hfinhor" id="hfinhor" class="form-control" value="12:00" required onchange="calcularHorasBloqueFormulario()">
                            </div>
                            <!-- Duración calculada -->
                            <div class="col-md-2 text-center">
                                <label class="form-label small text-muted">Duración</label>
                                <div>
                                    <span id="badge_duracion_bloque" class="badge bg-success fs-6 px-2 py-2 w-100">6.0 hrs/día</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sección: Tipo de horario -->
                <div class="col-12 mt-2">
                    <h6 class="text-success fw-bold border-bottom pb-2 mb-1">
                        <i class="fa-solid fa-4 me-1"></i>Tipo de horario
                    </h6>
                </div>

                <!-- Checkbox para actividades "Otros" -->
                <div class="form-group col-12">
                    <div class="form-check">
                        <input class="form-check-input" 
                               type="checkbox" 
                               name="es_otros" 
                               id="es_otros" 
                               value="1"
                               onchange="toggleOtrosFields()">
                        <label class="form-check-label" for="es_otros">
                            <strong><i class="fa-solid fa-calendar-plus me-2"></i>Actividades especiales (Otros)</strong>
                        </label>
                        <small class="form-text text-muted d-block mt-1">
                            Marque esta opción para agregar actividades como proyecto Senova, horas de alistamiento, horas de confraternidad, etc.
                        </small>
                    </div>
                </div>

                <!-- Checkbox para horarios "Transversales" -->
                <div class="form-group col-12">
                    <div class="form-check">
                        <input class="form-check-input" 
                               type="checkbox" 
                               name="es_transversal" 
                               id="es_transversal" 
                               value="1"
                               onchange="toggleTransversalFields()">
                        <label class="form-check-label" for="es_transversal">
                            <strong><i class="fa-solid fa-book-open me-2"></i>Horarios Transversales</strong>
                        </label>
                        <small class="form-text text-muted d-block mt-1">
                            Marque esta opción para agregar horarios de materias transversales como inglés, ética, etc.
                        </small>
                    </div>
                </div>

                <!-- Campos adicionales para "Otros" (inicialmente ocultos) -->
                <div id="campos_otros" class="col-12 d-none">
                    <div class="card mt-3">
                        <div class="card-header text-white">
                            <h6 class="mb-0">Configuración de Actividad Especial</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <!-- Actividad -->
                                <div class="form-group col-md-6">
                                    <label for="actividad">Actividad <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           name="actividad" 
                                           id="actividad" 
                                           class="form-control" 
                                           placeholder="Ej: Reunión de área, Capacitación docente..."
                                           maxlength="100">
                                    <small class="form-text text-muted">Descripción de la actividad especial</small>
                                </div>

                                <!-- Horas -->
                                <div class="form-group col-md-6">
                                    <label for="horas_otros">Horas de duración <span class="text-danger">*</span></label>
                                    <select name="horas_otros" id="horas_otros" class="form-select">
                                        <option value="">Seleccione las horas...</option>
                                        <option value="1">1 hora</option>
                                        <option value="2">2 horas</option>
                                        <option value="3">3 horas</option>
                                        <option value="4">4 horas</option>
                                        <option value="5">5 horas</option>
                                        <option value="6">6 horas</option>
                                        <option value="7">7 horas</option>
                                        <option value="8">8 horas</option>
                                        <option value="9">9 horas</option>
                                        <option value="10">10 horas</option>
                                        <option value="11">11 horas</option>
                                        <option value="12">12 horas</option>
                                        <option value="13">13 horas</option>
                                        <option value="14">14 horas</option>
                                        <option value="15">15 horas</option>
                                        <option value="16">16 horas</option>
                                    </select>
                                    <small class="form-text text-muted">Duración de la actividad especial</small>
                                </div>
                            </div>
                            
                            <!-- Checkbox para horas directas a formación -->
                            <div class="row mt-3">
                                <div class="form-group col-12">
                                    <!-- Campo oculto para asegurar que siempre se envíe un valor -->
                                    <input type="hidden" name="es_formacion_directa" value="1">
                                    <div class="form-check">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               name="es_formacion_directa" 
                                               id="es_formacion_directa"
                                               value="2">
                                        <label class="form-check-label" for="es_formacion_directa">
                                            <strong>Contar como horas directas a formación</strong>
                                        </label>
                                        <small class="form-text text-muted d-block mt-1">
                                            Si se marca, estas horas se sumarán como <strong>horas de formación</strong> en lugar de <strong>horas otros</strong>. 
                                            Útil para actividades especiales que contribuyen directamente a la formación de los aprendices.
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Campos adicionales para "Transversales" (inicialmente ocultos) -->
                <div id="campos_transversales" class="col-12 d-none">
                    <div class="card mt-3">
                        <div class="card-header text-white">
                            <h6 class="mb-0">Configuración de Horario Transversal</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <!-- Nombre de la Transversal -->
                                <div class="form-group col-md-12">
                                    <label for="nombre_transversal">Nombre de la Transversal <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           name="nombre_transversal" 
                                           id="nombre_transversal" 
                                           class="form-control" 
                                           placeholder="Ej: Inglés, Ética, Emprendimiento..."
                                           maxlength="100">
                                    <small class="form-text text-muted">Nombre de la materia transversal</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botón de enviar formulario -->
                <div class="form-group col-md-12 text-end">
                    <br>
                    <input type="hidden" name="ope" value="save">
                    <input type="hidden" name="id"
                        value="<?= isset($dtOne[0]['idhor']) ? $dtOne[0]['idhor'] : '' ?>">
                    
                    <!-- Campos ocultos para rango por evento -->
                    <input type="hidden" name="evento_seleccionado_id" id="evento_seleccionado_id" value="">
                    <input type="hidden" name="evento_seleccionado_titulo" id="evento_seleccionado_titulo" value="">
                    <input type="hidden" name="evento_seleccionado_fecha_inicio" id="evento_seleccionado_fecha_inicio" value="">
                    <input type="hidden" name="evento_seleccionado_fecha_fin" id="evento_seleccionado_fecha_fin" value="">
                    
                    <!-- Campos ocultos para edición -->
                    <?php if (isset($dtOne) && $dtOne): ?>
                    <input type="hidden" name="es_otros_edit" value="<?= $dtOne[0]['es_otros'] ?? '0' ?>">
                    <input type="hidden" name="actividad_edit" value="<?= htmlspecialchars($dtOne[0]['actividad'] ?? '') ?>">
                    <input type="hidden" name="horas_otros_edit" value="<?= $dtOne[0]['horas_otros'] ?? '' ?>">
                    <input type="hidden" name="es_formacion_directa_edit" value="<?= $dtOne[0]['es_formacion_directa'] ?? '1' ?>">
                    <input type="hidden" name="es_transversal_edit" value="<?= $dtOne[0]['es_transversal'] ?? '0' ?>">
                    <input type="hidden" name="nombre_transversal_edit" value="<?= htmlspecialchars($dtOne[0]['nombre_transversal'] ?? '') ?>">
                    <input type="hidden" name="fecha_inicio_edit" value="<?= $dtOne[0]['fecha_inicio'] ?? '' ?>">
                    <input type="hidden" name="fecha_fin_edit" value="<?= $dtOne[0]['fecha_fin'] ?? '' ?>">
                    <?php endif; ?>
                    
                    <input type="submit" class="btn btn-primary" value="Enviar">
                </div>
            </div>
        </form>
    </div>
    <?php endif; ?>
    <!-- Fin Agregar / Asignar Horarios -->

    <!-- Barra de filtros (área, ficha, instructor, semana) -->
    <?php $idare_filtro_actual = $_POST['idare'] ?? $_REQUEST['idare'] ?? ''; ?>
    <?php $idfic_filtro = $_POST['idfic'] ?? $_REQUEST['idfic'] ?? ''; ?>
    <?php $idusu_sel = $_POST['idusu'] ?? $_REQUEST['idusu'] ?? ''; ?>
    <form action="home.php?pg=<?= $pg; ?>" method="POST" id="formFiltros" class="card shadow-sm mb-4 border-0">
        <div class="card-header py-2 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h6 class="mb-0 fw-bold text-success">
                <i class="fa-solid fa-sliders me-2"></i>Filtros
            </h6>
            <?php if ($perfil_usuario['idper'] != 4): ?>
            <div class="d-flex flex-wrap gap-2 align-items-center">
                <a href="home.php?pg=<?= $pg; ?>" class="btn btn-outline-secondary btn-sm" title="Limpiar todos los filtros">
                    <i class="fa-solid fa-arrows-rotate me-1"></i> Limpiar Filtros
                </a>
            </div>
            <?php endif; ?>
        </div>
        <div class="card-body py-3">
            <div class="row g-3 align-items-end">
                <?php if ($perfil_usuario['idper'] != 4): ?>
                <!-- Filtro Área -->
                <div class="form-group col-md-3">
                    <label for="filtro_idare" class="form-label fw-bold small"><i class="fa-solid fa-layer-group me-1 text-success"></i>Área</label>
                    <select name="idare" id="filtro_idare" class="form-select form-select-sm" onchange="actualizarCascadaArea(this.value)">
                        <option value="todas" <?= ($idare_filtro_actual === 'todas' || $idare_filtro_actual === '' || $idare_filtro_actual === null) ? 'selected' : ''; ?>>
                            -- Todas las áreas asignadas --
                        </option>
                        <?php if (!empty($dar)): ?>
                            <?php foreach ($dar as $de): ?>
                                <option value="<?= $de['idare']; ?>" <?= ($idare_filtro_actual == $de['idare']) ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($de['nomare']); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <!-- Filtro Ficha -->
                <div class="form-group col-md-3">
                    <label for="filtro_idfic" class="form-label fw-bold small"><i class="fa-solid fa-graduation-cap me-1 text-success"></i>Ficha</label>
                    <select name="idfic" id="filtro_idfic" class="form-select form-select-sm" onchange="actualizarCascadaFicha(this.value)">
                        <option value="">-- Todas las fichas --</option>
                        <?php if (!empty($dfi)): ?>
                            <?php foreach ($dfi as $f): ?>
                                <option value="<?= $f['idfic']; ?>" <?= ($idfic_filtro == $f['idfic']) ? 'selected' : ''; ?>>
                                    <?= $f['idfic']; ?> - <?= htmlspecialchars($f['nomfic']); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <!-- Filtro Instructor -->
                <div class="form-group col-md-3">
                    <label for="filtro_idusu" class="form-label fw-bold small"><i class="fa-solid fa-user-tie me-1 text-success"></i>Instructor</label>
                    <select name="idusu" id="filtro_idusu" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">-- Todos los instructores --</option>
                        <?php if (!empty($din)): ?>
                            <?php foreach ($din as $inst): ?>
                                <option value="<?= $inst['idusu']; ?>" <?= ($idusu_sel == $inst['idusu']) ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($inst['nomusu']); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <?php endif; ?>

                <!-- Selector de Semana -->
                <div class="form-group <?= $perfil_usuario['idper'] == 4 ? 'col-md-6' : 'col-md-3'; ?>">
                    <label for="semana_selector" class="form-label fw-bold small"><i class="fa-solid fa-calendar-week me-1 text-success"></i>Semana</label>
                    <div class="input-group input-group-sm">
                        <input type="week" 
                               id="semana_selector" 
                               name="semana_selector" 
                               class="form-control" 
                               value="<?= $_POST['semana_selector'] ?? calcularSemanaISO() ?>"
                               onchange="cambiarSemana(this.value)">
                        <button type="button" class="btn btn-outline-success" onclick="semanaActual()" title="Ir a la semana actual">
                            <i class="fa-solid fa-calendar-day"></i> Hoy
                        </button>
                    </div>
                </div>
            </div>

            <!-- Información dependiente en cascada: Programa y Proyecto Formativo -->
            <div id="contenedorInfoFicha" class="mt-3 pt-2 border-top <?= empty($infoFichaActual) ? 'd-none' : ''; ?>">
                <div class="row g-2 align-items-center">
                    <div class="col-md-6">
                        <div class="p-2 rounded bg-light border d-flex align-items-center gap-2">
                            <i class="fa-solid fa-book-bookmark text-success fs-5"></i>
                            <div>
                                <small class="text-muted d-block fw-bold text-uppercase">Programa de Formación</small>
                                <span id="info_programa_texto" class="fw-semibold text-dark small"><?= !empty($infoFichaActual['nompro']) ? htmlspecialchars($infoFichaActual['nompro']) : 'Sin programa asignado'; ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-2 rounded bg-light border d-flex align-items-center gap-2">
                            <i class="fa-solid fa-diagram-project text-primary fs-5"></i>
                            <div>
                                <small class="text-muted d-block fw-bold text-uppercase">Proyecto Formativo</small>
                                <span id="info_proyecto_texto" class="fw-semibold text-dark small"><?= !empty($infoFichaActual['proyecto_formativo']) ? htmlspecialchars($infoFichaActual['proyecto_formativo']) : 'Proyecto Formativo asignado a la ficha'; ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <!-- Fin barra de filtros -->

    <!-- Sección de Reportes -->
    <?php if ($perfil_usuario['idper'] != 4): ?>
    <div class="card shadow-sm mb-4 border-0">
        <div class="card-header py-2 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h6 class="mb-0 fw-bold text-success">
                <i class="fa-solid fa-file-lines me-2"></i>Reportes
            </h6>
        </div>
        <div class="card-body py-3">
            <div class="d-flex flex-wrap gap-2 align-items-center">
                <button type="button" class="btn btn-outline-success btn-sm" onclick="abrirModalReporteMensual()">
                    <i class="fa-solid fa-calendar-days me-1"></i> Reporte Mensual
                </button>
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="abrirModalReporteAnual()">
                    <i class="fa-solid fa-chart-line me-1"></i> Reporte Anual
                </button>
                <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalReporteHorrhrgn" title="Imprimir horarios">
                    <i class="fa-solid fa-print me-1"></i> Imprimir Horarios
                </button>
                <a href="views/horrhrar.php?todas=1" class="btn btn-outline-success btn-sm" title="Imprimir los horarios de todas las aulas (un bloque por aula para colgar en cada salón)" target="_blank">
                    <i class="fa-solid fa-door-open me-1"></i> Todas las aulas
                </a>

                <!-- VENTANA MODAL CON FORMULARIO NATIVO -->
                <div class="modal fade" id="modalReporteHorrhrgn" tabindex="-1" aria-labelledby="modalReporteHorrhrgnLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <!-- FORMULARIO HTML SIN JAVASCRIPT -->
                            <form action="views/horrhrgn.php" method="GET" target="_blank">
                                
                                <div class="modal-header bg-success text-white py-2">
                                    <h5 class="modal-title fs-6" id="modalReporteHorrhrgnLabel">
                                        <i class="fa-solid fa-calendar-check me-2"></i>Generar Reporte de Horarios
                                    </h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                
                                <div class="modal-body">
                                    <!-- Parámetros ocultos traídos desde la vista principal -->
                                    <input type="hidden" name="idare" value="<?= htmlspecialchars($idare_filtro_actual, ENT_QUOTES); ?>">
                                    <input type="hidden" name="fic" value="<?= htmlspecialchars($idfic_filtro ?? '', ENT_QUOTES); ?>">

                                    <!-- Selección del Año -->
                                    <div class="mb-3">
                                        <label for="reporte_anio" class="form-label fw-bold small">Año:</label>
                                        <select name="anio" id="reporte_anio" class="form-select form-select-sm" required>
                                            <?php 
                                            $anioActual = (int)date('Y');
                                            for ($a = $anioActual; $a >= $anioActual - 5; $a--): ?>
                                                <option value="<?= $a; ?>"><?= $a; ?></option>
                                            <?php endfor; ?>
                                        </select>
                                    </div>

                                    <!-- Selección del Mes (obligatorio, por defecto mes actual) -->
                                    <div class="mb-3">
                                        <label for="reporte_mes" class="form-label fw-bold small">Mes:</label>
                                        <select name="mes" id="reporte_mes" class="form-select form-select-sm" required>
                                            <?php 
                                            $mesActual = (int)date('n');
                                            $meses = [
                                                1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
                                                5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
                                                9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
                                            ];
                                            foreach ($meses as $num => $nom): ?>
                                                <option value="<?= $num; ?>" <?= ($num === $mesActual) ? 'selected' : ''; ?>>
                                                    <?= $nom; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="modal-footer py-2">
                                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                                    <button type="submit" class="btn btn-success btn-sm">
                                        <i class="fa-solid fa-file-pdf me-1"></i> Generar Reporte
                                    </button>
                                </div>

                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    <!-- Fin sección de reportes -->
</div>

<!-- JavaScript para la funcionalidad de búsqueda -->
<script>
// Parámetros configurables sincronizados con backend
window.CONTRATISTA_MAX_HORAS = <?= defined('CONTRATISTA_MAX_HORAS') ? (int)CONTRATISTA_MAX_HORAS : 160 ?>;
window.PLANTA_MAX_HORAS = <?= defined('PLANTA_MAX_HORAS') ? (int)PLANTA_MAX_HORAS : 187 ?>;
window.FACTOR_FORMACION = <?= defined('FACTOR_FORMACION') ? json_encode(FACTOR_FORMACION) : '6.4' ?>;
window.FACTOR_OTROS = <?= defined('FACTOR_OTROS') ? json_encode(FACTOR_OTROS) : '2.1' ?>;

/**
 * Cascada AJAX: Al seleccionar Área, consulta y actualiza las Fichas correspondientes
 */
window.actualizarCascadaArea = async function(idare) {
    const selectFicha = document.getElementById('filtro_idfic');
    if (!selectFicha) {
        document.getElementById('formFiltros')?.submit();
        return;
    }

    selectFicha.innerHTML = '<option value="">-- Cargando fichas... --</option>';

    try {
        const formData = new FormData();
        formData.append('ope', 'get_fichas_por_area');
        formData.append('idare', idare);

        const resp = await fetch('controllers/horchor.php', {
            method: 'POST',
            body: formData
        });

        const data = await resp.json();
        selectFicha.innerHTML = '<option value="">-- Todas las fichas --</option>';

        if (data && data.success && Array.isArray(data.fichas)) {
            data.fichas.forEach(f => {
                const opt = document.createElement('option');
                opt.value = f.idfic;
                opt.textContent = `${f.idfic} - ${f.nomfic}`;
                selectFicha.appendChild(opt);
            });
        }
    } catch (e) {
        console.error('Error al actualizar cascada de fichas:', e);
        selectFicha.innerHTML = '<option value="">-- Todas las fichas --</option>';
    }

    document.getElementById('formFiltros').submit();
};

/**
 * Cascada AJAX: Al seleccionar Ficha, identifica Programa y Proyecto Formativo
 */
window.actualizarCascadaFicha = async function(idfic) {
    const contenedorInfo = document.getElementById('contenedorInfoFicha');
    const txtPrograma = document.getElementById('info_programa_texto');
    const txtProyecto = document.getElementById('info_proyecto_texto');

    if (!idfic) {
        if (contenedorInfo) contenedorInfo.classList.add('d-none');
        document.getElementById('formFiltros').submit();
        return;
    }

    try {
        const formData = new FormData();
        formData.append('ope', 'get_info_ficha');
        formData.append('idfic', idfic);

        const resp = await fetch('controllers/horchor.php', {
            method: 'POST',
            body: formData
        });

        const data = await resp.json();
        if (data && data.success && data.data) {
            if (txtPrograma) txtPrograma.textContent = data.data.nompro || 'Sin programa asignado';
            if (txtProyecto) txtProyecto.textContent = data.data.proyecto_formativo || 'Proyecto Formativo de la Ficha';
            if (contenedorInfo) contenedorInfo.classList.remove('d-none');
        }
    } catch (e) {
        console.error('Error al consultar info de ficha:', e);
    }

    document.getElementById('formFiltros').submit();
};

// Permisos de edición/eliminación inyectados desde el backend (misma lógica que los botones de la grilla)
window.PERMITIR_EDITAR = <?php echo (!empty($restricciones['mostrar_botones_editar']) || $perfil_usuario['idper'] != 4) ? 'true' : 'false'; ?>;
window.PERMITIR_ELIMINAR = <?php echo (!empty($restricciones['mostrar_botones_eliminar']) || $perfil_usuario['idper'] != 4) ? 'true' : 'false'; ?>;

/**
 * Codifica un valor como atributo data-* legible por JSON.parse(),
 * escapando comillas simples para no romper el atributo HTML.
 */
window.sag_JsonAttr = function (v) {
    return JSON.stringify(v == null ? '' : v).replace(/'/g, '&#39;');
};

/**
 * Construye los botones de acciones (Editar/Eliminar) para una fila de horario,
 * usando el idhor real del registro. Respeta los permisos de la vista.
 */
window.sag_BotonesHorario = function (item) {
    let html = '';
    const permitirEditar = window.PERMITIR_EDITAR === true;
    const permitirEliminar = window.PERMITIR_ELIMINAR === true;
    if (!permitirEditar && !permitirEliminar) return html;

    if (permitirEditar) {
        html += '<button type="button" class="btn btn-link p-0 text-success" title="Editar Horario" '
            + 'data-idhor="' + (item.idhor || '') + '" '
            + 'data-es-transversal="' + (item.es_transversal || 0) + '" '
            + 'data-es-otros="' + (item.es_otros || 0) + '" '
            + 'data-nombre-transversal=\'' + sag_JsonAttr(item.nombre_transversal) + '\' '
            + 'data-actividad=\'' + sag_JsonAttr(item.actividad) + '\' '
            + 'data-horas-otros="' + (item.horas_otros || '') + '" '
            + 'data-idaul="' + (item.idaul || '') + '" '
            + 'data-idusu="' + (item.idusu || '') + '" '
            + 'data-dia=\'' + sag_JsonAttr(item.dia) + '\' '
            + 'data-ficha=\'' + sag_JsonAttr(item.ficha) + '\' '
            + 'data-fecha-inicio="' + (item.fecha_inicio || '') + '" '
            + 'data-fecha-fin="' + (item.fecha_fin || '') + '" '
            + 'data-hinihor="' + (item.hinihor || '') + '" '
            + 'data-hfinhor="' + (item.hfinhor || '') + '" '
            + 'data-es-formacion-directa="' + (item.es_formacion_directa != null ? item.es_formacion_directa : 1) + '" '
            + 'onclick="onClickEditarHorario(this)">'
            + '<i class="fa-solid fa-pen-to-square"></i></button>';
    }
    if (permitirEliminar) {
        html += '<button type="button" class="btn btn-link p-0 text-danger ms-2" title="Eliminar Horario" '
            + 'data-idhor="' + (item.idhor || '') + '" '
            + 'data-iddia="' + (item.iddia || '') + '" '
            + 'data-idfic="' + (item.idfic || '') + '" '
            + 'data-idusu="' + (item.idusu || '') + '" '
            + 'data-idaul="' + (item.idaul || '') + '" '
            + 'data-nomfic=\'' + sag_JsonAttr(item.nomfic || item.ficha || '') + '\' '
            + 'data-nomusu=\'' + sag_JsonAttr(item.nomusu || 'Sin instructor') + '\' '
            + 'data-nomaul=\'' + sag_JsonAttr(item.aula || 'Sin aula') + '\' '
            + 'data-fecha-inicio="' + (item.fecha_inicio || '') + '" '
            + 'data-fecha-fin="' + (item.fecha_fin || '') + '" '
            + 'data-jornada=\'' + sag_JsonAttr(item.jornada || '') + '\' '
            + 'onclick="onClickEliminar(this)">'
            + '<i class="fa-solid fa-trash-can"></i></button>';
    }
    return html;
};

/**
 * Refresca, sin recargar la página, el detalle del instructor actualmente abierto:
 * vuelve a consultar su información (detalle/horas) y reconstruye el modal,
 * manteniéndose dentro del detalle. Devuelve true si pudo refrescar en sitio.
 */
window.sagRefrescarDetalleInstructor = async function () {
    const original = window.instructorActual;
    if (!original || !original.id_instructor) return false;
    const contenedor = document.getElementById('contenidoModal');
    const modalDetalle = document.getElementById('modalDetalle');
    const modalVisible = modalDetalle && (modalDetalle.classList.contains('show') || modalDetalle.classList.contains('visible'));
    if (!contenedor || !modalVisible) return false;

    let instructor = original;
    try {
        const formData = new FormData();
        formData.append('ope', 'get_carga_instructores');
        formData.append('id_instructor', original.id_instructor);
        const resp = await fetch('controllers/horchor.php', {
            method: 'POST',
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin',
            cache: 'no-store',
            body: formData
        });
        const data = await resp.json();
        if (data && data.success && Array.isArray(data.data)) {
            const match = data.data.find(x => String(x.id_instructor) === String(original.id_instructor))
                || (data.data.length === 1 ? data.data[0] : null);
            if (match) {
                // Conservar los campos escalares originales (tipo_contrato, max_horas, horas_senova, etc.)
                // y refrescar únicamente el detalle por registro.
                instructor = Object.assign({}, original, { detalle: match.detalle || original.detalle });
            }
        }
    } catch (e) {
        console.warn('No se pudo obtener el detalle actualizado del instructor:', e);
    }

    window.instructorActual = instructor;
    window.currentInstructorId = instructor.id_instructor;

    if (typeof mostrarDetalle !== 'function') return false;
    try {
        await mostrarDetalle(
            instructor.nombre,
            instructor.documento,
            instructor.horas,
            instructor.max_horas,
            instructor.tipo_contrato,
            instructor.detalle,
            instructor.horas_senova || 0
        );
        return true;
    } catch (e) {
        console.error('Error refrescando el detalle del instructor:', e);
        return false;
    }
};

/**
 * Tras editar/eliminar un horario: si el detalle de un instructor está abierto,
 * lo refresca en sitio; de lo contrario recarga la página.
 */
window.sagRecargarTrasCambio = function () {
    window.sagRefrescarDetalleInstructor().then(function (ok) {
        if (!ok) window.location.reload();
    }).catch(function () {
        window.location.reload();
    });
};

async function actualizarResumenMesSeleccionado(idInstructor, mes, año) {
    try {
        // Evitar condiciones de carrera si el usuario cambió de instructor mientras cargaba
        if (window.currentInstructorId && idInstructor !== window.currentInstructorId) return;
        if (!idInstructor) return;
        const fd = new FormData();
        fd.append('ope', 'get_horarios_por_mes');
        fd.append('id_instructor', idInstructor);
        fd.append('mes', mes);
        fd.append('año', año);
        // Rango completo del mes (día 2 hasta el último día) para el cálculo real por registro
        const p2m = (n) => n.toString().padStart(2, '0');
        const fechaIniMes = new Date(año, mes - 1, 2);
        const fechaFinMes = new Date(año, mes, 0);
        fd.append('fecha_inicio', fechaIniMes.getFullYear() + '-' + p2m(fechaIniMes.getMonth() + 1) + '-' + p2m(fechaIniMes.getDate()));
        fd.append('fecha_fin', fechaFinMes.getFullYear() + '-' + p2m(fechaFinMes.getMonth() + 1) + '-' + p2m(fechaFinMes.getDate()));
        const resp = await fetch('controllers/horchor.php', { method: 'POST', body: fd });
        if (!resp.ok) throw new Error('HTTP ' + resp.status);
        const data = await resp.json();
        if (data && data.success) {
            // Actualizar la tabla de detalle con los datos del mes seleccionado
            if (data.detalle && data.detalle.length > 0) {
                await actualizarTablaDetalleMes(data.detalle, mes, data.año);
            } else {
                // Si no hay datos, mostrar mensaje
                await actualizarTablaDetalleMes([], mes, data.año);
            }
        } else {
            // Limpiar tabla de detalle
            await actualizarTablaDetalleMes([], mes, año);
        }
    } catch (e) {
        console.error('Error cargando datos del mes seleccionado:', e);
        // Limpiar tabla de detalle en caso de error
        await actualizarTablaDetalleMes([], mes, año);
    }
}

// Función para actualizar la tabla de detalle con datos del mes seleccionado (por registro real)
async function actualizarTablaDetalleMes(detalle, mes, año) {
    // Actualizar el título de la tabla
    const tituloTabla = document.querySelector('#contenidoModal .card .card-header h6');
    if (tituloTabla) {
        tituloTabla.textContent = `Detalle de Horarios y Actividades - ${obtenerNombreMes(mes)} ${año}`;
    }
    
    const tablaContainer = document.querySelector('#contenidoModal .card .card-body .table-responsive');
    if (!tablaContainer) return;
    
    // Calcular resumen de horas con las horas REALES de cada registro (hinihor/hfinhor u horas_otros)
    let totalHorasFormacion = 0;
    let totalHorasOtros = 0;
    let totalGeneral = 0;
    
    if (detalle && detalle.length > 0) {
        detalle.forEach(item => {
            const horas = Number(item.horas) || 0;
            if (Number(item.es_otros)) {
                // Actividad "otros": verificar es_formacion_directa
                if (Number(item.es_formacion_directa) === 2) {
                    totalHorasFormacion += horas;
                } else {
                    totalHorasOtros += horas;
                }
            } else {
                // Horario normal o transversal: siempre suma a formación
                totalHorasFormacion += horas;
            }
        });
        
        totalGeneral = totalHorasFormacion + totalHorasOtros;
    }
    
    // Calcular horas esperadas dinámicamente para instructores de planta
    let horasEsperadasMensual = 160; // Valor por defecto para contratistas
    let horasFormacionDirecta = 0;
    let horasOtros = 0;
    
    // Calcular horas esperadas según tipo de contrato
    if (window.instructorActual && window.instructorActual.tipo_contrato === 'planta') {
        // Para instructores de planta, calcular dinámicamente
        const diasLaborales = await calcularDiasLaborales(mes, año);
        horasFormacionDirecta = diasLaborales * 6.4;
        horasOtros = diasLaborales * 2.1;
        horasEsperadasMensual = horasFormacionDirecta + horasOtros;
    } else {
        // Para contratistas, usar tope configurable
        horasEsperadasMensual = window.CONTRATISTA_MAX_HORAS || 180;
        horasFormacionDirecta = 0; // Los contratistas no tienen desglose de formación/otros
        horasOtros = 0;
    }
    
    const horasDisponibles = Math.max(0, horasEsperadasMensual - totalGeneral);
    
    // Actualizar el resumen de horas en la interfaz con formato x/y
    // Buscar explícitamente el card cuyo header sea "Resumen de Horas (...)"
    let resumenCard;
    const headers = Array.from(document.querySelectorAll('#contenidoModal h6'));
    const headerResumen = headers.find(h => h.textContent.trim().startsWith('Resumen de Horas'));
    if (headerResumen) {
        const resumenCardEl = headerResumen.closest('.card');
        resumenCard = resumenCardEl ? resumenCardEl.querySelector('.card-body') : null;
    }

    if (resumenCard) {
        const esContratista = window.instructorActual && window.instructorActual.tipo_contrato === 'contratista';
        resumenCard.innerHTML = `
            <h6>Resumen de Horas (${obtenerNombreMes(mes)})</h6>
            <p><strong>Horas directas a formación:</strong> ${esContratista ? totalHorasFormacion : `${totalHorasFormacion}/${horasFormacionDirecta.toFixed(1)}`} hrs</p>
            <p><strong>Horas otros:</strong> ${esContratista ? totalHorasOtros : `${totalHorasOtros}/${horasOtros.toFixed(1)}`} hrs</p>
            <p><strong>Total asignado:</strong> ${esContratista ? `${totalGeneral}/${(window.CONTRATISTA_MAX_HORAS||180).toFixed? (window.CONTRATISTA_MAX_HORAS||180).toFixed(1) : (window.CONTRATISTA_MAX_HORAS||180)}` : `${totalGeneral}/${horasEsperadasMensual.toFixed(1)}`} hrs</p>
            <p><strong>Horas disponibles:</strong> ${esContratista ? ((window.CONTRATISTA_MAX_HORAS||180) - totalGeneral).toFixed(1) : horasDisponibles.toFixed(1)} hrs</p>
        `;
        
    } else {
        console.error('No se encontró el card de Resumen de Horas para actualizar.');
    }
    
    // Actualizar la gráfica de barras
    const progressContainer = document.querySelector('#contenidoModal .progress.mt-3');
    if (progressContainer) {
        // Calcular porcentajes
        const porcentajeFormacion = (totalHorasFormacion / horasEsperadasMensual) * 100;
        const porcentajeOtros = (totalHorasOtros / horasEsperadasMensual) * 100;
        const porcentajeDisponibles = (horasDisponibles / horasEsperadasMensual) * 100;
        
        progressContainer.innerHTML = `
            <div class="progress-bar" title="Horas directas a formación">
                ${totalHorasFormacion} hrs
            </div>
            <div class="progress-bar bg-primary" title="Otros">
                ${totalHorasOtros} hrs
            </div>
            <div class="progress-bar bg-secondary" title="Disponibles">
                ${horasDisponibles} hrs
            </div>
        `;
        
    } else {
        console.error('No se encontró el contenedor de la gráfica');
    }
    
    // Generar contenido de la tabla por registro real
    let contenido = `
        <table class="table table-sm">
            <thead>
                <tr>
                    <th>Día</th>
                    <th>Ficha</th>
                    <th>Jornada</th>
                    <th>Aula</th>
                    <th>Horas</th>
                    <th>Tipo</th>
    `;
    if (window.PERMITIR_EDITAR === true || window.PERMITIR_ELIMINAR === true) {
        contenido += `<th>Acciones</th>`;
    }
    contenido += `
                </tr>
            </thead>
            <tbody>
    `;
    
    if (detalle && detalle.length > 0) {
        detalle.forEach(item => {
            const esOtros = Number(item.es_otros) ? true : false;
            const esTransversal = Number(item.es_transversal) ? true : false;
            const claseFila = esTransversal ? 'table-info' : (esOtros ? 'table-warning' : '');
            const horas = Number(item.horas) || 0;
            
            let tipoClase = 'badge bg-primary';
            let tipoTexto = 'Formación';
            if (item.tipo_horas === 'formacion_directa') {
                tipoClase = 'badge bg-success';
                tipoTexto = 'Otros/Formación';
            } else if (item.tipo_horas === 'otros') {
                tipoClase = 'badge bg-warning text-dark';
                tipoTexto = 'Otros';
            } else if (esTransversal) {
                tipoClase = 'badge bg-info';
                tipoTexto = 'Transversal';
            }
            
            const diaCelda = item.dia + (item.fecha || item.fecha_especifica ? ', ' + formatearFechaLocal(item.fecha || item.fecha_especifica) : '');
            const jornadaCelda = (item.hinihor && item.hfinhor) ? (item.hinihor + ' - ' + item.hfinhor) : (item.jornada || '');
            
            contenido += `
                <tr class="${claseFila}">
                    <td>${diaCelda}</td>
                    <td><strong>${item.ficha || ''}</strong></td>
                    <td>${jornadaCelda}</td>
                    <td>${item.aula || ''}</td>
                    <td>${horas.toFixed(1)} hrs</td>
                    <td><span class="${tipoClase}">${tipoTexto}</span></td>
            `;
            if (window.PERMITIR_EDITAR === true || window.PERMITIR_ELIMINAR === true) {
                contenido += `<td class="text-nowrap">${window.sag_BotonesHorario(item)}</td>`;
            }
            contenido += `</tr>`;
        });
        
        // Fila de totales
        contenido += `
            <tr class="table-dark">
                <td><strong>TOTAL GENERAL</strong></td>
                <td></td>
                <td></td>
                <td></td>
                <td><strong>${totalGeneral.toFixed(1)} hrs</strong></td>
                <td></td>
                ${(window.PERMITIR_EDITAR === true || window.PERMITIR_ELIMINAR === true) ? '<td></td>' : ''}
            </tr>
        `;
    } else {
        contenido += `
            <tr>
                <td colspan="${(window.PERMITIR_EDITAR === true || window.PERMITIR_ELIMINAR === true) ? 7 : 6}" class="text-center text-muted">
                    <i class="fa-solid fa-info-circle me-2"></i>
                    No hay horarios registrados para ${obtenerNombreMes(mes)} ${año}
                </td>
            </tr>
        `;
    }
    
    contenido += `
            </tbody>
        </table>`;
    tablaContainer.innerHTML = contenido;
}


document.addEventListener('DOMContentLoaded', function() {
    // Mostrar mensajes de resultado si existen
    <?php if (isset($mensajesResultado) && !empty($mensajesResultado)): ?>
        <?php if (isset($mensajesResultado['errores'])): ?>
            const errores = <?= json_encode($mensajesResultado['errores']) ?>;
            let mensajeCompleto = "";
            errores.forEach((error, index) => {
                mensajeCompleto += `${error}\n`;
            });
            alert(mensajeCompleto);
        <?php endif; ?>
        
        <?php if (isset($mensajesResultado['exito'])): ?>
            setTimeout(function() {
                alert("<?= $mensajesResultado['exito'] ?>");
            }, <?= isset($mensajesResultado['errores']) ? '100' : '0' ?>);
        <?php endif; ?>
    <?php endif; ?>

    // Inicializar búsquedas interactivas
    initSearchDropdown('idfic', 'Ficha');
    initSearchDropdown('idusu', 'Instructor');
    initSearchDropdown('idaul', 'Aula');

    // Inicializar el rango horario (de qué hora a qué hora se asigna al instructor)
    if (document.getElementById('preset_jornada')) {
        aplicarPresetJornada(document.getElementById('preset_jornada').value);
    }

    // Validación del formulario
    document.querySelector('#frmins').addEventListener('submit', function(e) {
        // Si es edición, verificar que se haya seleccionado transversal
        const esEdicion = document.querySelector('input[name="ope"]').value === 'edit';
        const esTransversal = document.getElementById('es_transversal').checked;
        
        if (esEdicion && !esTransversal) {
            e.preventDefault();
            alert('Para editar un transversal, debe marcar la opción "Transversales".');
            return;
        }
        
        const checked = document.querySelectorAll('input[name="iddia[]"]:checked').length;
        const fichaSeleccionada = document.getElementById('idfic').value;
        const aulaSeleccionada = document.getElementById('idaul').value;
        const instructorSeleccionado = document.getElementById('idusu').value;
        const esOtros = document.getElementById('es_otros').checked;
        const esTransversalCheck = document.getElementById('es_transversal').checked;

        if (!checked) {
            e.preventDefault();
            alert('Debes seleccionar al menos un día.');
            return;
        }

        // Validar ficha: requerida para horarios normales y transversales, opcional para "otros"
        if (!esOtros && !fichaSeleccionada) {
            e.preventDefault();
            alert('Debes seleccionar una ficha.');
            return;
        }

        // Validar instructor (requerido para horarios normales y "otros", opcional para transversales)
        if (!esTransversalCheck && !instructorSeleccionado) {
            e.preventDefault();
            alert('Debes seleccionar un instructor.');
            return;
        }

        // Validar aula (requerida para horarios normales y transversales, opcional para "otros")
        if (!esOtros && !aulaSeleccionada) {
            e.preventDefault();
            alert('Debes seleccionar un aula.');
            return;
        }

        // Validar campos específicos de "Otros"
        if (esOtros) {
            const actividad = document.getElementById('actividad').value;
            const horasOtros = document.getElementById('horas_otros').value;
            
            if (!actividad.trim()) {
                e.preventDefault();
                alert('Debes ingresar el nombre de la actividad especial.');
                return;
            }
            
            if (!horasOtros) {
                e.preventDefault();
                alert('Debes seleccionar las horas de duración de la actividad.');
                return;
            }
        }

        // Validar campos específicos de "Transversales"
        if (esTransversalCheck) {
            const nombreTransversal = document.getElementById('nombre_transversal').value;
            
            if (!nombreTransversal.trim()) {
                e.preventDefault();
                alert('Debes ingresar el nombre de la transversal.');
                return;
            }
        }

        // Validar campos de fechas
        const rangoPorEvento = document.getElementById('rango_por_evento').checked;
        let fechaInicio = document.getElementById('fecha_inicio').value;
        let fechaFin = document.getElementById('fecha_fin').value;
        
        
        // Verificar si está en modo individual
        const modoIndividual = document.getElementById('modo_individual').checked;
        
        if (modoIndividual) {
            // Modo individual: usar fecha específica
            const fechaEspecifica = document.getElementById('fecha_especifica').value;
            if (!fechaEspecifica) {
                e.preventDefault();
                alert('Debes seleccionar una fecha específica para el horario individual.');
                return;
            }
            
            // Establecer la fecha específica como fecha de inicio y fin para el procesamiento
            fechaInicio = fechaEspecifica;
            fechaFin = fechaEspecifica;
            
            // Habilitar temporalmente los campos de fecha para que se envíen en el formulario
            document.getElementById('fecha_inicio').disabled = false;
            document.getElementById('fecha_fin').disabled = false;
            document.getElementById('fecha_inicio').value = fechaEspecifica;
            document.getElementById('fecha_fin').value = fechaEspecifica;
            
        } else if (rangoPorEvento) {
            // Si es rango por evento, verificar que se haya seleccionado un evento
            const eventoSeleccionado = document.getElementById('evento_calendario').value;
            if (!eventoSeleccionado) {
                e.preventDefault();
                alert('Debes seleccionar un evento del calendario académico.');
                return;
            }
            
            // Usar las fechas del evento seleccionado
            fechaInicio = document.getElementById('evento_seleccionado_fecha_inicio').value;
            fechaFin = document.getElementById('evento_seleccionado_fecha_fin').value;
            
            // Habilitar temporalmente los campos de fecha para que se envíen en el formulario
            document.getElementById('fecha_inicio').disabled = false;
            document.getElementById('fecha_fin').disabled = false;
        }
        
        if (!fechaInicio || !fechaFin) {
            e.preventDefault();
            alert('Debes seleccionar tanto la fecha de inicio como la fecha de fin.');
            return;
        }
        
        // Solo validar que fecha de fin sea posterior a fecha de inicio si NO está en modo individual
        if (!modoIndividual && fechaInicio >= fechaFin) {
            e.preventDefault();
            alert('La fecha de fin debe ser posterior a la fecha de inicio.');
            return;
        }
        
        // Verificar que el rango de fechas no sea muy largo (máximo 1 año) solo si NO está en modo individual
        if (!modoIndividual) {
            const inicio = new Date(fechaInicio);
            const fin = new Date(fechaFin);
            const diasDiferencia = Math.ceil((fin - inicio) / (1000 * 60 * 60 * 24)) + 1; // +1 para incluir ambos días
            
            if (diasDiferencia > 365) {
                e.preventDefault();
                alert('El rango de fechas no puede ser mayor a 1 año.');
                return;
            }
        }

        // Confirmación antes de enviar
        const diasSeleccionados = document.querySelectorAll('input[name="iddia[]"]:checked');
        const nombresDias = Array.from(diasSeleccionados).map(cb => cb.nextElementSibling.textContent).join(', ');
        
        // Asegurar que las fechas estén correctamente configuradas antes del envío
        if (rangoPorEvento) {
            // Si es rango por evento, asegurar que las fechas del evento se usen
            const fechaInicioEvento = document.getElementById('evento_seleccionado_fecha_inicio').value;
            const fechaFinEvento = document.getElementById('evento_seleccionado_fecha_fin').value;
            
            // Actualizar los campos de fecha con los valores del evento
            document.getElementById('fecha_inicio').value = fechaInicioEvento;
            document.getElementById('fecha_fin').value = fechaFinEvento;
            
        }
        
        let mensajeConfirmacion;
        if (esOtros) {
            const actividad = document.getElementById('actividad').value;
            mensajeConfirmacion = `¿Estás seguro de programar la actividad "${actividad}" para los días: ${nombresDias}?`;
        } else if (esTransversalCheck) {
            const transversal = document.getElementById('nombre_transversal').value;
            if (esEdicion) {
                mensajeConfirmacion = `¿Estás seguro de actualizar la transversal "${transversal}" para los días: ${nombresDias}?`;
            } else {
                mensajeConfirmacion = `¿Estás seguro de programar la transversal "${transversal}" para los días: ${nombresDias}?`;
            }
        } else {
            mensajeConfirmacion = `¿Estás seguro de asignar este horario para los días: ${nombresDias}?`;
        }
        
        if (!confirm(mensajeConfirmacion)) {
            e.preventDefault();
        }
    });

    // Cargar valores preseleccionados
    loadPreselectedValues();
    
    // Inicializar funcionalidad de fechas
    initFechaFuncionality();
});

/**
 * Inicializa la funcionalidad de búsqueda para un dropdown específico
 */
/**
 * Inicializa la funcionalidad de búsqueda para un dropdown específico.
 * Usa clases Bootstrap existentes (dropdown-menu/show, dropdown-item/active,
 * d-none) sin estilos inline; los datos vienen del servidor (BD).
 */
function initSearchDropdown(fieldName, displayName) {
    const searchInput = document.getElementById(`search_${fieldName}`);
    const hiddenInput = document.getElementById(fieldName);
    const dropdown = document.getElementById(`dropdown_${fieldName}`);
    if (!searchInput || !hiddenInput || !dropdown) {
        return;
    }
    const items = dropdown.querySelectorAll('.dropdown-item-custom');

    function abrirDropdown() {
        dropdown.classList.add('show');
    }
    function cerrarDropdown() {
        dropdown.classList.remove('show');
    }
    
    let highlightedIndex = -1;

    // Evento de escritura en el input
    searchInput.addEventListener('input', function() {
        const query = this.value.toLowerCase().trim();
        highlightedIndex = -1;
        
        if (query === '') {
            showAllItems(items);
            abrirDropdown();
            hiddenInput.value = '';
            return;
        }

        filterItems(items, query);
        abrirDropdown();
    });

    // Navegación con teclado
    searchInput.addEventListener('keydown', function(e) {
        const visibleItems = Array.from(dropdown.querySelectorAll('.dropdown-item-custom')).filter(
            item => !item.classList.contains('d-none')
        );

        switch(e.key) {
            case 'ArrowDown':
                e.preventDefault();
                highlightedIndex = Math.min(highlightedIndex + 1, visibleItems.length - 1);
                highlightItem(visibleItems, highlightedIndex);
                break;
            case 'ArrowUp':
                e.preventDefault();
                highlightedIndex = Math.max(highlightedIndex - 1, -1);
                highlightItem(visibleItems, highlightedIndex);
                break;
            case 'Enter':
                e.preventDefault();
                if (highlightedIndex >= 0 && visibleItems[highlightedIndex]) {
                    selectItem(visibleItems[highlightedIndex], searchInput, hiddenInput, dropdown);
                }
                break;
            case 'Escape':
                cerrarDropdown();
                highlightedIndex = -1;
                break;
        }
    });

    // Click en items del dropdown
    items.forEach(item => {
        item.addEventListener('click', function() {
            selectItem(this, searchInput, hiddenInput, dropdown);
        });
    });

    // Cerrar dropdown al hacer click fuera
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !dropdown.contains(e.target)) {
            cerrarDropdown();
            highlightedIndex = -1;
        }
    });

    // Mostrar opciones al hacer click o focus (topeado para no volcar miles de nodos)
    searchInput.addEventListener('click', function() {
        showAllItems(items);
        abrirDropdown();
    });

    searchInput.addEventListener('focus', function() {
        showAllItems(items);
        abrirDropdown();
    });
}

/**
 * Tope de sugerencias visibles (sin scroll con altura fija: se pagina con texto).
 */
const HOR_MAX_SUGERENCIAS = 10;

/**
 * Muestra los primeros items (topeado) con contador del resto
 */
function showAllItems(items) {
    const dropdown = items[0]?.parentElement;
    removeNoResultsMessage(dropdown);
    let mostrados = 0;
    items.forEach(item => {
        const mostrar = mostrados < HOR_MAX_SUGERENCIAS;
        item.classList.toggle('d-none', !mostrar);
        if (mostrar) mostrados++;
    });
    if (dropdown) {
        showMoreHint(dropdown, items.length - mostrados);
    }
}

/**
 * Filtra los items basándose en la consulta de búsqueda (topeado)
 */
function filterItems(items, query) {
    const dropdown = items[0]?.parentElement;
    let coincidencias = 0;
    let mostrados = 0;

    items.forEach(item => {
        const text = item.getAttribute('data-text').toLowerCase();
        const isMatch = text.includes(query);
        if (isMatch) coincidencias++;
        const mostrar = isMatch && mostrados < HOR_MAX_SUGERENCIAS;
        item.classList.toggle('d-none', !mostrar);
        if (mostrar) mostrados++;
    });

    // Mostrar mensaje si no hay resultados
    if (coincidencias === 0 && dropdown) {
        showNoResultsMessage(dropdown, query);
    } else {
        removeNoResultsMessage(dropdown);
    }
    if (dropdown) {
        showMoreHint(dropdown, coincidencias - mostrados);
    }
}

/**
 * Resalta un item específico (clase Bootstrap 'active')
 */
function highlightItem(visibleItems, index) {
    // Remover highlight previo
    visibleItems.forEach(item => item.classList.remove('active'));

    // Agregar highlight al item actual
    if (index >= 0 && visibleItems[index]) {
        visibleItems[index].classList.add('active');
        visibleItems[index].scrollIntoView({ block: 'nearest' });
    }
}

/**
 * Selecciona un item del dropdown (fija el hidden que viaja al guardar)
 */
function selectItem(item, searchInput, hiddenInput, dropdown) {
    const value = item.getAttribute('data-value');
    const text = item.getAttribute('data-text');

    searchInput.value = text;
    hiddenInput.value = value;
    dropdown.classList.remove('show');
}

/**
 * Muestra mensaje de "no hay resultados" (clases existentes, sin inline)
 */
function showNoResultsMessage(dropdown, query) {
    removeNoResultsMessage(dropdown);

    const noResultsDiv = document.createElement('div');
    noResultsDiv.className = 'dropdown-item-custom dropdown-item text-muted fst-italic no-results-message';
    noResultsDiv.textContent = `No se encontraron resultados para "${query}"`;

    dropdown.appendChild(noResultsDiv);
}

/**
 * Aviso de resultados ocultos por el tope ("N más, siga escribiendo")
 */
function showMoreHint(dropdown, ocultos) {
    const previo = dropdown.querySelector('.dropdown-more-hint');
    if (previo) {
        previo.remove();
    }
    if (ocultos > 0) {
        const hint = document.createElement('div');
        hint.className = 'dropdown-item text-muted small dropdown-more-hint';
        hint.textContent = `+${ocultos} resultados más, siga escribiendo para filtrar`;
        dropdown.appendChild(hint);
    }
}

/**
 * Remueve el mensaje de "no hay resultados" y el aviso de tope
 */
function removeNoResultsMessage(dropdown) {
    const existingMessage = dropdown?.querySelector('.no-results-message');
    if (existingMessage) {
        existingMessage.remove();
    }
    const existingHint = dropdown?.querySelector('.dropdown-more-hint');
    if (existingHint) {
        existingHint.remove();
    }
}

/**
 * Aplica la jornada sugerida seleccionada al formulario (rellena horas de inicio y fin)
 */
function aplicarPresetJornada(valor) {
    const presets = {
        'manana': { ini: '06:00', fin: '12:00' },
        'tarde':  { ini: '12:00', fin: '18:00' },
        'noche':  { ini: '18:00', fin: '22:00' },
        'mixta':  { ini: '06:00', fin: '14:00' }
    };
    if (presets[valor]) {
        const ini = document.getElementById('hinihor');
        const fin = document.getElementById('hfinhor');
        if (ini) ini.value = presets[valor].ini;
        if (fin) fin.value = presets[valor].fin;
    }
    // 'custom' no modifica las horas, solo recalcula la duración
    if (typeof calcularHorasBloqueFormulario === 'function') {
        calcularHorasBloqueFormulario();
    }
}

/**
 * Calcula la duración del bloque (de qué hora a qué hora se asigna) y 
 * actualiza el badge del formulario
 */
function calcularHorasBloqueFormulario() {
    const iniEl = document.getElementById('hinihor');
    const finEl = document.getElementById('hfinhor');
    const badge = document.getElementById('badge_duracion_bloque');
    if (!iniEl || !finEl || !badge) return;

    const ini = iniEl.value;
    const fin = finEl.value;

    let horas = 0;
    if (ini && fin) {
        const [h1, m1] = ini.split(':').map(Number);
        const [h2, m2] = fin.split(':').map(Number);
        let totalMin = (h2 * 60 + m2) - (h1 * 60 + m1);
        if (totalMin < 0) totalMin += 24 * 60; // cruce de medianoche
        horas = Math.max(0, totalMin / 60);
    }

    badge.textContent = horas.toFixed(1) + ' hrs/día';
}

/**
 * Carga valores preseleccionados al cargar la página
 */
function loadPreselectedValues() {
    // Cargar Ficha preseleccionada
    const idficValue = document.getElementById('idfic').value;
    if (idficValue) {
        const fichaItem = document.querySelector(`#dropdown_idfic [data-value="${idficValue}"]`);
        if (fichaItem) {
            document.getElementById('search_idfic').value = fichaItem.getAttribute('data-text');
        }
    }

    // Cargar Instructor preseleccionado solo si estamos editando
    const idusuValue = document.getElementById('idusu').value;
    const isEditing = document.querySelector('input[name="ope"]') && document.querySelector('input[name="ope"]').value === 'edi';
    
    if (idusuValue && isEditing) {
        const instructorItem = document.querySelector(`#dropdown_idusu [data-value="${idusuValue}"]`);
        if (instructorItem) {
            document.getElementById('search_idusu').value = instructorItem.getAttribute('data-text');
        }
    }

    // Cargar Aula preseleccionada solo si estamos editando
    const idaulValue = document.getElementById('idaul').value;
    if (idaulValue && isEditing) {
        const aulaItem = document.querySelector(`#dropdown_idaul [data-value="${idaulValue}"]`);
        if (aulaItem) {
            document.getElementById('search_idaul').value = aulaItem.getAttribute('data-text');
        }
    }

    // Cargar campos de "Otros" si están preseleccionados
    <?php if (isset($dtOne) && $dtOne && !empty($dtOne[0]['es_otros']) && $dtOne[0]['es_otros'] == 1): ?>
    document.getElementById('es_otros').checked = true;
    toggleOtrosFields();
    document.getElementById('actividad').value = '<?= htmlspecialchars($dtOne[0]['actividad'] ?? '') ?>';
    document.getElementById('horas_otros').value = '<?= $dtOne[0]['horas_otros'] ?? '' ?>';

    // Cargar el estado del checkbox de formación directa
    <?php if (isset($dtOne[0]['es_formacion_directa']) && $dtOne[0]['es_formacion_directa'] == 2): ?>
    document.getElementById('es_formacion_directa').checked = true;
    <?php endif; ?>
    <?php endif; ?>

    // Cargar campos de "Transversales" si están preseleccionados
    <?php if (isset($dtOne) && $dtOne && !empty($dtOne[0]['es_transversal']) && $dtOne[0]['es_transversal'] == 1): ?>
    // Solo cargar si no es una edición (para evitar duplicar la carga)
    if (document.querySelector('input[name="ope"]').value !== 'edit') {
        document.getElementById('es_transversal').checked = true;
        toggleTransversalFields();
        document.getElementById('nombre_transversal').value = '<?= htmlspecialchars($dtOne[0]['nombre_transversal'] ?? '') ?>';
    }
    <?php endif; ?>

    // Cargar campos de fechas si están preseleccionados
    <?php if (isset($dtOne) && $dtOne): ?>
    if (document.querySelector('input[name="ope"]').value !== 'edit') {
        const fechaInicio = '<?= $dtOne[0]['fecha_inicio'] ?? '' ?>';
        const fechaFin = '<?= $dtOne[0]['fecha_fin'] ?? '' ?>';
        
        if (fechaInicio) {
            document.getElementById('fecha_inicio').value = fechaInicio;
        }
        if (fechaFin) {
            document.getElementById('fecha_fin').value = fechaFin;
        }
        
        // Actualizar información del lapso
        actualizarInfoLapso();
    }
    <?php endif; ?>
    
    // Verificar si hay un evento seleccionado y cargar rango por evento
    const eventoSeleccionadoId = document.getElementById('evento_seleccionado_id').value;
    if (eventoSeleccionadoId) {
        document.getElementById('rango_por_evento').checked = true;
        toggleRangoPorEvento();
    }
}

/**
 * Función de utilidad para limpiar un campo de búsqueda
 */
function clearSearchField(fieldName) {
    document.getElementById(`search_${fieldName}`).value = '';
    document.getElementById(fieldName).value = '';
    document.getElementById(`dropdown_${fieldName}`).classList.remove('show');
}

/**
 * Formatea una fecha en zona horaria local sin problemas de UTC
 */
function formatearFechaLocal(fechaString) {
    // Separar año, mes y día
    const [año, mes, dia] = fechaString.split('-');
    
    // Crear array de nombres de meses en español
    const meses = [
        'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio',
        'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'
    ];
    
    // Formatear la fecha
    const diaFormateado = parseInt(dia).toString().padStart(2, '0');
    const mesFormateado = meses[parseInt(mes) - 1];
    const añoFormateado = año;
    
    return `${diaFormateado} de ${mesFormateado} de ${añoFormateado}`;
}

/**
 * Inicializa la funcionalidad de fechas para el formulario de horarios
 * 
 * Esta función configura los event listeners para los campos de fechas:
 * - Actualiza automáticamente la fecha mínima de fin cuando cambia la fecha de inicio
 * - Valida que la fecha de fin no sea anterior a la fecha de inicio
 * - Actualiza la información del lapso en tiempo real
 * - Mantiene la consistencia entre los campos de fechas
 */
function initFechaFuncionality() {
    const fechaInicio = document.getElementById('fecha_inicio');
    const fechaFin = document.getElementById('fecha_fin');
    
    // Evento para fecha de inicio: actualiza validaciones y muestra información
    fechaInicio.addEventListener('change', function() {
        // Actualizar fecha mínima de fin para evitar fechas inválidas
        fechaFin.min = this.value;
        
        // Si la fecha de fin es anterior a la nueva fecha de inicio, actualizarla
        if (fechaFin.value && fechaFin.value < this.value) {
            fechaFin.value = this.value;
        }
        
        // Actualizar la información del lapso mostrada al usuario
        actualizarInfoLapso();
    });
    
    // Evento para fecha de fin: actualiza información del lapso
    fechaFin.addEventListener('change', function() {
        actualizarInfoLapso();
    });
    
    // Evento para fecha específica: actualiza información del lapso
    const fechaEspecifica = document.getElementById('fecha_especifica');
    if (fechaEspecifica) {
        fechaEspecifica.addEventListener('change', function() {
            actualizarInfoLapso();
        });
    }
    
    // Actualizar información del lapso al cargar la página
    actualizarInfoLapso();
}

/**
 * Alterna entre modo individual y modo rango
 */
function toggleModoIndividual() {
    const modoIndividual = document.getElementById('modo_individual').checked;
    const campoFechaEspecifica = document.getElementById('campo_fecha_especifica');
    const campoFechaInicio = document.getElementById('campo_fecha_inicio');
    const campoFechaFin = document.getElementById('campo_fecha_fin');
    
    if (modoIndividual) {
        // Mostrar campo de fecha específica y ocultar campos de rango
        campoFechaEspecifica.style.display = 'block';
        campoFechaInicio.style.display = 'none';
        campoFechaFin.style.display = 'none';
        
        // Hacer requerido el campo de fecha específica
        document.getElementById('fecha_especifica').required = true;
        document.getElementById('fecha_inicio').required = false;
        document.getElementById('fecha_fin').required = false;
        
        // Establecer la fecha específica como la fecha de inicio por defecto
        const fechaInicio = document.getElementById('fecha_inicio').value;
        if (fechaInicio) {
            document.getElementById('fecha_especifica').value = fechaInicio;
        }
        
        // Actualizar la información del lapso
        actualizarInfoLapso();
    } else {
        // Ocultar campo de fecha específica y mostrar campos de rango
        campoFechaEspecifica.style.display = 'none';
        campoFechaInicio.style.display = 'block';
        campoFechaFin.style.display = 'block';
        
        // Hacer requeridos los campos de rango
        document.getElementById('fecha_especifica').required = false;
        document.getElementById('fecha_inicio').required = true;
        document.getElementById('fecha_fin').required = true;
        
        // Actualizar la información del lapso
        actualizarInfoLapso();
    }
}

/**
 * Actualiza la información del lapso seleccionado
 */
function actualizarInfoLapso() {
    const modoIndividual = document.getElementById('modo_individual').checked;
    const infoLapso = document.getElementById('info_lapso');
    const formatoInicio = document.getElementById('formatoInicio');
    const formatoFin = document.getElementById('formatoFin');
    const duracionLapso = document.getElementById('duracion_lapso');
    
    if (modoIndividual) {
        // Modo individual: mostrar fecha específica
        const fechaEspecifica = document.getElementById('fecha_especifica').value;
        
        if (fechaEspecifica) {
            const fechaFormateada = formatearFechaLocal(fechaEspecifica);
            
            // Actualizar elementos para modo individual
            formatoInicio.textContent = fechaFormateada;
            formatoFin.textContent = fechaFormateada;
            duracionLapso.textContent = '1 día';
            
            // Mostrar información con color verde para modo individual
            infoLapso.style.display = 'block';
            infoLapso.className = 'alert alert-success';
        } else {
            // Ocultar información si no hay fecha específica
            infoLapso.style.display = 'none';
        }
    } else {
        // Modo rango: mostrar rango de fechas
        const fechaInicio = document.getElementById('fecha_inicio').value;
        const fechaFin = document.getElementById('fecha_fin').value;
        
        if (fechaInicio && fechaFin) {
            // Crear fechas usando solo la fecha (sin tiempo) para evitar problemas de UTC
            const inicio = new Date(fechaInicio);
            const fin = new Date(fechaFin);
            
            // Asegurar que las fechas se interpreten como locales
            inicio.setHours(0, 0, 0, 0);
            fin.setHours(0, 0, 0, 0);
            
            const diasDiferencia = Math.ceil((fin - inicio) / (1000 * 60 * 60 * 24)) + 1; // +1 para incluir ambos días
            
            // Formatear fechas para mostrar usando la fecha original del input
            const textoInicio = formatearFechaLocal(fechaInicio);
            const textoFin = formatearFechaLocal(fechaFin);
            
            // Actualizar el contenido de los elementos
            formatoInicio.textContent = textoInicio;
            formatoFin.textContent = textoFin;
            duracionLapso.textContent = `${diasDiferencia} días`;
            
            infoLapso.style.display = 'block';
            
            // Cambiar color según la duración
            if (diasDiferencia <= 30) {
                infoLapso.className = 'alert alert-success';
            } else if (diasDiferencia <= 90) {
                infoLapso.className = 'alert alert-info';
            } else if (diasDiferencia <= 180) {
                infoLapso.className = 'alert alert-warning';
            } else {
                infoLapso.className = 'alert alert-danger';
            }
            
        } else {
            infoLapso.style.display = 'none';
        }
    }
}

/**
 * Función para mostrar/ocultar los campos de "Otros"
 */
function toggleOtrosFields() {
    const checkbox = document.getElementById('es_otros');
    const camposOtros = document.getElementById('campos_otros');
    const camposTransversales = document.getElementById('campos_transversales');
    const searchIdfic = document.getElementById('search_idfic');
    
    if (checkbox.checked) {
        // Mostrar campos de "Otros"
        camposOtros.style.display = 'block';
        
        // Desactivar y ocultar campos de "Transversales"
        document.getElementById('es_transversal').checked = false;
        camposTransversales.style.display = 'none';
        
        // HACER EL CAMPO DE FICHA OPCIONAL PARA ACTIVIDADES ESPECIALES
        if (searchIdfic) {
            searchIdfic.placeholder = 'Opcional: Busque una ficha o deje vacío para actividad especial...';
            searchIdfic.style.borderColor = '#28a745'; // Verde para indicar que es opcional
        }
        
        // Scroll suave hacia los nuevos campos
        setTimeout(() => {
            camposOtros.scrollIntoView({ 
                behavior: 'smooth', 
                block: 'nearest' 
            });
        }, 100);
        
    } else {
        // Ocultar campos de "Otros"
        camposOtros.style.display = 'none';
        
        // RESTAURAR CAMPO DE FICHA COMO REQUERIDO
        if (searchIdfic) {
            searchIdfic.placeholder = 'Escriba para buscar ficha...';
            searchIdfic.style.borderColor = ''; // Restaurar color normal
        }
        
        // Limpiar campos de "Otros"
        document.getElementById('actividad').value = '';
        document.getElementById('horas_otros').value = '';
        document.getElementById('es_formacion_directa').checked = false;
    }
}

/**
 * Función para mostrar/ocultar los campos de "Transversales"
 */
function toggleTransversalFields() {
    const checkbox = document.getElementById('es_transversal');
    const camposTransversales = document.getElementById('campos_transversales');
    const camposOtros = document.getElementById('campos_otros');
    
    if (checkbox.checked) {
        // Mostrar campos de "Transversales"
        camposTransversales.style.display = 'block';
        
        // Desactivar y ocultar campos de "Otros"
        document.getElementById('es_otros').checked = false;
        camposOtros.style.display = 'none';
        
        // Solo hacer scroll si no es una edición
        if (document.querySelector('input[name="ope"]').value !== 'edit') {
            setTimeout(() => {
                camposTransversales.scrollIntoView({ 
                    behavior: 'smooth', 
                    block: 'nearest' 
                });
            }, 100);
        }
        
    } else {
        // Ocultar campos de "Transversales"
        camposTransversales.style.display = 'none';
        
        // Solo limpiar campos si no es una edición
        if (document.querySelector('input[name="ope"]').value !== 'edit') {
            document.getElementById('nombre_transversal').value = '';
        }
    }
}

/**
 * Función para mostrar/ocultar los campos de rango por evento
 */
function toggleRangoPorEvento() {
    const checkbox = document.getElementById('rango_por_evento');
    const camposRangoEvento = document.getElementById('campos_rango_evento');
    const campoFechaInicio = document.getElementById('campo_fecha_inicio');
    const campoFechaFin = document.getElementById('campo_fecha_fin');
    
    if (checkbox.checked) {
        // Mostrar campos de rango por evento
        camposRangoEvento.style.display = 'block';
        
        // Ocultar campos de fecha manual pero NO deshabilitarlos
        campoFechaInicio.style.display = 'none';
        campoFechaFin.style.display = 'none';
        
        // NO desactivar campos de fecha manual para que se envíen en el formulario
        // document.getElementById('fecha_inicio').disabled = true;
        // document.getElementById('fecha_fin').disabled = true;
        
        // Desactivar otros checkboxes
        document.getElementById('es_otros').checked = false;
        document.getElementById('es_transversal').checked = false;
        toggleOtrosFields();
        toggleTransversalFields();
        
        // Scroll suave hacia los nuevos campos
        setTimeout(() => {
            camposRangoEvento.scrollIntoView({ 
                behavior: 'smooth', 
                block: 'nearest' 
            });
        }, 100);
        
    } else {
        // Ocultar campos de rango por evento
        camposRangoEvento.style.display = 'none';
        
        // Mostrar campos de fecha manual
        campoFechaInicio.style.display = 'block';
        campoFechaFin.style.display = 'block';
        
        // Asegurar que los campos de fecha estén habilitados
        document.getElementById('fecha_inicio').disabled = false;
        document.getElementById('fecha_fin').disabled = false;
        
        // Limpiar campos de evento
        document.getElementById('tipo_evento').value = '';
        document.getElementById('evento_calendario').value = '';
        document.getElementById('info_evento').style.display = 'none';
        
        // Limpiar campos ocultos
        document.getElementById('evento_seleccionado_id').value = '';
        document.getElementById('evento_seleccionado_titulo').value = '';
        document.getElementById('evento_seleccionado_fecha_inicio').value = '';
        document.getElementById('evento_seleccionado_fecha_fin').value = '';
        
        // Limpiar campos de fecha
        document.getElementById('fecha_inicio').value = '';
        document.getElementById('fecha_fin').value = '';
        
        // Actualizar información del lapso
        actualizarInfoLapso();
    }
}

/**
 * Carga los eventos según el tipo seleccionado
 */
function cargarEventosPorTipo() {
    const tipoEvento = document.getElementById('tipo_evento').value;
    const selectEvento = document.getElementById('evento_calendario');
    
    if (!tipoEvento) {
        selectEvento.innerHTML = '<option value="">Primero seleccione el tipo de evento...</option>';
        return;
    }
    
    // Limpiar eventos anteriores
    selectEvento.innerHTML = '<option value="">Cargando eventos...</option>';
    
    // Crear FormData para enviar la consulta
    const formData = new FormData();
    formData.append('ope', 'get_eventos_por_tipo');
    formData.append('tipo_evento', tipoEvento);
    
    fetch('controllers/horchor.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.eventos) {
            selectEvento.innerHTML = '<option value="">Seleccione un evento...</option>';
            
            data.eventos.forEach(evento => {
                const option = document.createElement('option');
                option.value = evento.id;
                
                // Formatear fechas para mostrar mes y día (evitar problemas de UTC)
                const fechaInicio = new Date(evento.fecha_inicio + 'T00:00:00');
                const fechaFin = new Date(evento.fecha_fin + 'T00:00:00');
                
                const mesInicio = fechaInicio.toLocaleDateString('es-ES', { month: 'short' });
                const diaInicio = fechaInicio.getDate();
                const mesFin = fechaFin.toLocaleDateString('es-ES', { month: 'short' });
                const diaFin = fechaFin.getDate();
                
                // Crear texto del option con rango de fechas
                let textoOption = `${evento.titulo} (${evento.anio})`;
                
                // Si las fechas son del mismo mes, mostrar solo una vez
                if (fechaInicio.getMonth() === fechaFin.getMonth()) {
                    textoOption += ` - ${diaInicio} al ${diaFin} ${mesInicio}`;
                } else {
                    // Si son meses diferentes, mostrar ambos
                    textoOption += ` - ${diaInicio} ${mesInicio} al ${diaFin} ${mesFin}`;
                }
                
                textoOption += ` - ${evento.trimestre}`;
                
                option.textContent = textoOption;
                option.setAttribute('data-fecha-inicio', evento.fecha_inicio);
                option.setAttribute('data-fecha-fin', evento.fecha_fin);
                option.setAttribute('data-titulo', evento.titulo);
                option.setAttribute('data-trimestre', evento.trimestre);
                selectEvento.appendChild(option);
            });
        } else {
            selectEvento.innerHTML = '<option value="">No se encontraron eventos</option>';
        }
    })
    .catch(error => {
        console.error('Error cargando eventos:', error);
        selectEvento.innerHTML = '<option value="">Error al cargar eventos</option>';
    });
}

/*
 * ====================================================================================
 * (Esto es para eliminar TODOS los registros de horarios, usar solo en PRODUCCIÓN)
 * ====================================================================================
 */
/*
function confirmarEliminacionTotal() {
    const confirmacion = confirm(
        '¿Estás seguro de que quieres eliminar TODOS los horarios del sistema?\n\n' +
        'Esta acción eliminará todos los horarios y reseteará los contadores.\n' +
        'NO SE PUEDE DESHACER.\n\n' +
        '¿Continuar?'
    );
    
    if (confirmacion) {
        ejecutarEliminacionTotal();
    }
}

function ejecutarEliminacionTotal() {
    // Mostrar indicador de carga
    const boton = document.querySelector('button[onclick="confirmarEliminacionTotal()"]');
    const textoOriginal = boton.innerHTML;
    
    boton.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>PROCESANDO...';
    boton.disabled = true;
    
    // Crear FormData para enviar la solicitud
    const formData = new FormData();
    formData.append('ope', 'eliminar_todos_horarios');
    
    fetch('controllers/horchor.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✅ OPERACIÓN COMPLETADA\n\n' + data.message);
            // Recargar la página para mostrar el estado actualizado
            location.reload();
        } else {
            alert('❌ ERROR EN LA OPERACIÓN\n\n' + (data.message || 'Error desconocido'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('❌ ERROR DE CONEXIÓN\n\nNo se pudo completar la operación. Verifica la conexión e intenta nuevamente.');
    })
    .finally(() => {
        // Restaurar el botón
        boton.innerHTML = textoOriginal;
        boton.disabled = false;
    });
}
*/

/**
 * Selecciona un evento y llena automáticamente las fechas
 */
function seleccionarEvento() {
    const selectEvento = document.getElementById('evento_calendario');
    if (!selectEvento) {
        console.error('Elemento evento_calendario no encontrado');
        return;
    }
    
    const eventoId = selectEvento.value;
    
    const infoEvento = document.getElementById('info_evento');
    if (!infoEvento) {
        console.error('Elemento info_evento no encontrado');
        return;
    }
    
    if (!eventoId) {
        infoEvento.style.display = 'none';
        return;
    }
    
    const option = selectEvento.options[selectEvento.selectedIndex];
    const fechaInicio = option.getAttribute('data-fecha-inicio');
    const fechaFin = option.getAttribute('data-fecha-fin');
    const titulo = option.getAttribute('data-titulo');
    const trimestre = option.getAttribute('data-trimestre');
    
    // Llenar campos ocultos (con validación)
    const eventoSeleccionadoId = document.getElementById('evento_seleccionado_id');
    const eventoSeleccionadoTitulo = document.getElementById('evento_seleccionado_titulo');
    const eventoSeleccionadoFechaInicio = document.getElementById('evento_seleccionado_fecha_inicio');
    const eventoSeleccionadoFechaFin = document.getElementById('evento_seleccionado_fecha_fin');
    
    if (eventoSeleccionadoId) eventoSeleccionadoId.value = eventoId;
    if (eventoSeleccionadoTitulo) eventoSeleccionadoTitulo.value = titulo;
    if (eventoSeleccionadoFechaInicio) eventoSeleccionadoFechaInicio.value = fechaInicio;
    if (eventoSeleccionadoFechaFin) eventoSeleccionadoFechaFin.value = fechaFin;
    
    // ACTUALIZAR CAMPOS VISIBLES para que se muestre en el "Lapso seleccionado"
    const fechaInicioInput = document.getElementById('fecha_inicio');
    const fechaFinInput = document.getElementById('fecha_fin');
    
    if (fechaInicioInput) fechaInicioInput.value = fechaInicio;
    if (fechaFinInput) fechaFinInput.value = fechaFin;
    
    // ACTUALIZAR EL LAPSO SELECCIONADO para mostrar las fechas del evento
    if (typeof actualizarInfoLapso === 'function') {
        actualizarInfoLapso();
    }
    
    // Mostrar información del evento (con validación)
    const tituloEvento = document.getElementById('titulo_evento');
    if (tituloEvento) {
        tituloEvento.textContent = titulo;
    }
    
    // Formatear fechas para mostrar de manera más clara (evitando problemas UTC)
    // Separar las fechas manualmente para evitar problemas de zona horaria
    const [añoInicio, mesInicioNum, diaInicioNum] = fechaInicio.split('-').map(Number);
    const [añoFin, mesFinNum, diaFinNum] = fechaFin.split('-').map(Number);
    
    const meses = [
        'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio',
        'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'
    ];
    
    const mesInicio = meses[mesInicioNum - 1];
    const mesFin = meses[mesFinNum - 1];
    const diaInicio = diaInicioNum;
    const diaFin = diaFinNum;
    
    // Crear texto del rango de fechas
    let textoRango;
    if (mesInicioNum === mesFinNum) {
        // Mismo mes
        textoRango = `${diaInicio} al ${diaFin} de ${mesInicio}`;
    } else {
        // Meses diferentes
        textoRango = `${diaInicio} de ${mesInicio} al ${diaFin} de ${mesFin}`;
    }
    
    // Actualizar información del evento (con validación)
    const fechaInicioEvento = document.getElementById('fecha_inicio_evento');
    const fechaFinEvento = document.getElementById('fecha_fin_evento');
    const trimestreEvento = document.getElementById('trimestre_evento');
    
    if (fechaInicioEvento) fechaInicioEvento.textContent = textoRango;
    if (fechaFinEvento) fechaFinEvento.textContent = ''; // Ya no necesitamos mostrar fecha fin por separado
    if (trimestreEvento) trimestreEvento.textContent = trimestre;
    
    // Mostrar la información del evento
    if (infoEvento) infoEvento.style.display = 'block';
    
    // Llenar campos de fecha (aunque estén ocultos, para el envío del formulario) - con validación
    const fechaInicioField = document.getElementById('fecha_inicio');
    const fechaFinField = document.getElementById('fecha_fin');
    
    if (fechaInicioField) fechaInicioField.value = fechaInicio;
    if (fechaFinField) fechaFinField.value = fechaFin;
    
    // Asegurar que los campos estén habilitados para el envío del formulario - con validación
    if (fechaInicioField) fechaInicioField.disabled = false;
    if (fechaFinField) fechaFinField.disabled = false;
    
    // Actualizar información del lapso - con validación
    if (typeof actualizarInfoLapso === 'function') {
        actualizarInfoLapso();
    }
}



/**
 * Función para abrir el modal de edición de transversales
 */
/**
 * Carga los campos de hora inicio/fin, fechas y formación directa en el modal de edición.
 */
function cargarCamposHorarioEdicion(hinihor, hfinhor, fechaInicio, fechaFin, esFormacionDirecta) {
    const eHini = document.getElementById('hinihor_edit');
    const eHfin = document.getElementById('hfinhor_edit');
    const eFini = document.getElementById('fecha_inicio_edit');
    const eFfin = document.getElementById('fecha_fin_edit');
    const eFd = document.getElementById('es_formacion_directa_edit');
    if (eHini) eHini.value = hinihor || '';
    if (eHfin) eHfin.value = hfinhor || '';
    if (eFini) eFini.value = fechaInicio || '';
    if (eFfin) eFfin.value = fechaFin || '';
    if (eFd) eFd.value = (esFormacionDirecta || 1);
}

function abrirModalEditarTransversal(idhor, nombreTransversal, idaul, idusu, dia, ficha) {
    // Cargar datos en el modal
    document.getElementById('idhor_edit').value = idhor;
    document.getElementById('nombre_transversal_edit').value = nombreTransversal;
    document.getElementById('ficha_edit').value = ficha;
    document.getElementById('dia_edit').value = dia;
    
    // Seleccionar instructor si existe (manejar valores null/undefined)
    if (idusu && idusu !== 'null' && idusu !== 'undefined' && idusu !== '') {
        document.getElementById('instructor_edit').value = idusu;
    } else {
        document.getElementById('instructor_edit').value = '';
    }
    
    // Seleccionar aula si existe (manejar valores null/undefined)
    if (idaul && idaul !== 'null' && idaul !== 'undefined' && idaul !== '') {
        document.getElementById('aula_edit').value = idaul;
    } else {
        document.getElementById('aula_edit').value = '';
    }
    
    // Abrir el modal
    const modal = new bootstrap.Modal(document.getElementById('modalEditarTransversal'));
    modal.show();
}

/**
 * Función para guardar la edición de horarios (normales, transversales u otros)
 */
function guardarEdicionTransversal() {
    const idhorElement = document.getElementById('idhor_edit');
    if (!idhorElement || !idhorElement.value) {
        alert('Error: Elemento de edición no encontrado');
        return;
    }
    
    const esTransversal = document.getElementById('es_transversal_edit')?.value === '1';
    const esOtros = document.getElementById('es_otros_edit')?.value === '1';
    
    const nombreTransversal = document.getElementById('nombre_transversal_edit')?.value.trim() || '';
    const actividad = document.getElementById('actividad_edit')?.value.trim() || '';
    const horasOtros = document.getElementById('horas_otros_edit')?.value || '';
    const idaul = document.getElementById('aula_edit')?.value || '';
    const idusu = document.getElementById('instructor_edit')?.value || '';
    
    if (esTransversal && !nombreTransversal) {
        alert('Debe ingresar el nombre de la transversal.');
        return;
    }
    if (esOtros && !actividad) {
        alert('Debe ingresar el nombre de la actividad especial.');
        return;
    }
    
    // Verificar si es edición grupal
    const esEdicionGrupal = idhorElement.getAttribute('data-edicion-grupal') === 'true';
    const fechaInicio = idhorElement.getAttribute('data-fecha-inicio');
    const fechaFin = idhorElement.getAttribute('data-fecha-fin');
    const idaulOriginal = idhorElement.getAttribute('data-idaul-original');
    const idusuOriginal = idhorElement.getAttribute('data-idusu-original');
    
    let mensajeConfirmacion = '¿Está seguro de guardar los cambios en este horario?';
    if (esEdicionGrupal) {
        mensajeConfirmacion = `¿Está seguro de guardar los cambios en TODO el grupo de horarios transversales relacionados?\n\nEsto afectará a todos los horarios desde ${fechaInicio} hasta ${fechaFin} que mantengan la misma aula e instructor originales.\n\nHorarios que hayan sido editados individualmente NO serán afectados.`;
    }
    
    // Confirmar antes de guardar
    if (!confirm(mensajeConfirmacion)) {
        return;
    }
    
    // Crear FormData con todos los campos necesarios
    const formData = new FormData();
    const operacion = esEdicionGrupal ? 'edit_grupo_transversal' : 'edit';
    formData.append('ope', operacion);
    formData.append('idhor', idhorElement.value);
    formData.append('idaul', idaul);
    formData.append('idusu', idusu);
    
    if (esTransversal) {
        formData.append('es_transversal', '1');
        formData.append('nombre_transversal', nombreTransversal);
    } else {
        formData.append('es_transversal', '0');
    }
    
    if (esOtros) {
        formData.append('es_otros', '1');
        formData.append('actividad', actividad);
        formData.append('horas_otros', horasOtros);
    } else {
        formData.append('es_otros', '0');
    }
    
    // Si es edición grupal, agregar las fechas y valores originales
    if (esEdicionGrupal) {
        formData.append('fecha_inicio', fechaInicio);
        formData.append('fecha_fin', fechaFin);
        formData.append('idaul_original', idaulOriginal);
        formData.append('idusu_original', idusuOriginal);
    } else {
        // Edición individual: enviar hora inicio/fin, fechas y formación directa (el backend conserva si van vacías)
        formData.append('hinihor', document.getElementById('hinihor_edit')?.value || '');
        formData.append('hfinhor', document.getElementById('hfinhor_edit')?.value || '');
        formData.append('fecha_inicio', document.getElementById('fecha_inicio_edit')?.value || '');
        formData.append('fecha_fin', document.getElementById('fecha_fin_edit')?.value || '');
        formData.append('es_formacion_directa', document.getElementById('es_formacion_directa_edit')?.value || '1');
    }
    
    // Enviar datos via AJAX a horchor.php
    fetch('controllers/horchor.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        return response.text();
    })
    .then(data => {  
        let jsonData = null;
        try {
            jsonData = JSON.parse(data);
        } catch (e) {}
        
        if (jsonData && jsonData.success === false) {
            alert('Error al guardar: ' + (jsonData.message || 'Error desconocido'));
            return;
        }
        
        if (data.includes('Error') || data.includes('error')) {
            alert('Error en el servidor: ' + data);
            return;
        }
        
        // Limpiar atributos de edición grupal
        if (esEdicionGrupal) {
            limpiarAtributosEdicionGrupal();
        }
        
        // Cerrar modal
        const modalEdit = document.getElementById('modalEditarTransversal');
        const overlay = document.getElementById('overlayEliminacion');
        if (modalEdit) modalEdit.classList.remove('visible');
        if (overlay) overlay.style.display = 'none';
        
        const mensajeExito = esEdicionGrupal ? 
            'Grupo de horarios actualizado correctamente.' : 
            'Horario actualizado correctamente.';
        alert(mensajeExito);
        
        // Refrescar el detalle en sitio (o recargar si no hay detalle abierto)
        sagRecargarTrasCambio();
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al actualizar el horario. Intente nuevamente.');
    });
}
</script>





<!-- Información de la semana seleccionada -->
<?php 
// Lógica simplificada para el selector de semana
if (!isset($_POST['semana_selector'])) {
    // Carga inicial: siempre mostrar la semana actual
    $semanaSelector = calcularSemanaISO();
} else {
    // Usar la semana seleccionada manualmente
    $semanaSelector = $_POST['semana_selector'];
}

$verHoy = isset($_POST['ver_hoy']) && $_POST['ver_hoy'] === '1';

if ($verHoy) {
    // Botón "Hoy": mostrar la semana actual
    $semanaSelector = calcularSemanaISO(); // Siempre semana actual
    $semanaFormateada = formatearSemanaPHP($semanaSelector);
    $fechaHoy = date('d/m/Y');
    $mensaje = "Mostrando <strong>semana actual</strong> donde está hoy ($fechaHoy): <strong>$semanaFormateada</strong>";
    $color = "rgba(0, 175, 0, 0.1)";
    $colorTexto = "#00af00";
    $colorBorde = "rgba(0, 175, 0, 0.3)";
    $icono = "fa-calendar-week";
} else {
    // Sin botón "Hoy": mostrar la semana correspondiente
    $semanaFormateada = formatearSemanaPHP($semanaSelector);
    
    if (!isset($_POST['semana_selector'])) {
        $mensaje = "Mostrando <strong>semana actual</strong>: <strong>$semanaFormateada</strong>";
    } else {
        $mensaje = "Semana seleccionada: <strong>$semanaFormateada</strong>";
    }
    
    $color = "rgba(0, 175, 0, 0.1)";
    $colorTexto = "#00af00";
    $colorBorde = "rgba(0, 175, 0, 0.3)";
    $icono = "fa-calendar-week";
}
?>
<div class="alert alert-success bg-opacity-10 mb-3">
    <i class="fa-solid <?= $icono ?> me-2"></i>
    <?= $mensaje ?>
</div>

<!-- Horarios por ficha: tarjetas y tabla (misma fuente de datos) -->
<?php if (!empty($dft) && is_array($dft)) {?>
<div class="row g-2 align-items-center mb-3">
    <div class="col-12 col-md-4">
        <div class="input-group input-group-sm">
            <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
            <input type="search" id="buscadorCardsHorarios" class="form-control" placeholder="Buscar ficha, instructor, aula..." autocomplete="off">
        </div>
    </div>
    <div class="col-12 col-md-4">
        <select id="ordenCardsHorarios" class="form-select form-select-sm" title="Ordenar tarjetas" onchange="cambiarOrdenCardsHorarios(this.value)">
            <option value="0|desc">Orden: Ficha (Z-A)</option>
            <option value="0|asc">Orden: Ficha (A-Z)</option>
            <?php $horColDia = 2; foreach ((is_array($ddi) ? $ddi : []) as $horD): if (is_array($horD) && isset($horD['nomval'])): ?>
            <option value="<?= $horColDia; ?>|asc">Orden: <?= htmlspecialchars($horD['nomval']); ?> (hora)</option>
            <?php $horColDia++; endif; endforeach; ?>
        </select>
    </div>
    <div class="col-12 col-md-4 text-md-end">
    <div class="btn-group btn-group-sm" role="group" aria-label="Vista de horarios">
        <button type="button" id="btnVistaCards" class="btn btn-success" onclick="cambiarVistaHorarios('cards')">
            <i class="fa-solid fa-grip me-1"></i> Tarjetas
        </button>
        <button type="button" id="btnVistaTabla" class="btn btn-outline-success" onclick="cambiarVistaHorarios('tabla')">
            <i class="fa-solid fa-table me-1"></i> Tabla
        </button>
    </div>
    </div>
</div>
<div id="vistaCardsHorarios">
    <div id="gridCardsHorarios" class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-3"></div>
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3">
        <small id="infoPaginacionCards" class="text-muted"></small>
        <div class="btn-group btn-group-sm" role="group" aria-label="Paginación de tarjetas">
            <button type="button" id="btnCardsAnterior" class="btn btn-outline-success" onclick="paginarCardsHorarios(-1)">
                <i class="fa-solid fa-chevron-left me-1"></i> Anterior
            </button>
            <button type="button" id="btnCardsSiguiente" class="btn btn-outline-success" onclick="paginarCardsHorarios(1)">
                Siguiente <i class="fa-solid fa-chevron-right ms-1"></i>
            </button>
        </div>
    </div>
</div>
<div id="contenedorTablaHorarios" class="d-none">
<div class="table-responsive">
<table id='tablaHorariosPrincipal' class="table table-striped">
    <thead>
        <tr>
            <th>Ficha y Programa</th>
            <th></th>
            <?php pintit($ddi); ?>
        </tr>
    </thead>
    <tbody></tbody>

    <tfoot>
        <tr>
            <th>Ficha y Programa</th>
            <th></th>
            <?php pintit($ddi); ?>
        </tr>
    </tfoot>
</table>
</div>
</div>

<?php
// Días que realmente se imprimen como columna (misma condición que pintit)
$hor_dias_endpoint = [];
$hor_dias_nombres = [];
if (is_array($ddi)) {
    foreach ($ddi as $d) {
        if (is_array($d) && isset($d['nomval'])) {
            $hor_dias_endpoint[] = $d['idval'];
            $hor_dias_nombres[] = $d['nomval'];
        }
    }
}
$hor_idare_filtro = $idare_actual ?? '';
$hor_idfic_filtro = $idfic_actual ?? '';
$hor_idusu_filtro = $idusu_sel ?? '';
$hor_semana_filtro = $semanaSelector ?? '';
?>
<script>
/* Horarios por ficha: tarjetas (vista principal) y tabla (vista alterna).
 * Ambas consumen el MISMO endpoint server-side (ope=get_horarios_fichas_dt)
 * con los mismos filtros; solo cambia la presentación. */
var horDias = <?= json_encode($hor_dias_endpoint) ?>;
var horDiasNombres = <?= json_encode($hor_dias_nombres) ?>;
var horFiltros = {
    ope: 'get_horarios_fichas_dt',
    idare: <?= json_encode($hor_idare_filtro) ?>,
    idfic: <?= json_encode($hor_idfic_filtro) ?>,
    idusu: <?= json_encode($hor_idusu_filtro) ?>,
    semana_selector: <?= json_encode($hor_semana_filtro) ?>
};
var horCardsEstado = { pagina: 0, porPagina: 20, busqueda: '', ordenColumna: 0, ordenDir: 'desc', total: 0, draw: 0 };
var horTablaIniciada = false;

/**
 * Alterna entre tarjetas y tabla. La tabla se inicializa de forma perezosa
 * (mismo código de antes) solo cuando el usuario la pide.
 */
function cambiarVistaHorarios(vista) {
    var cards = document.getElementById('vistaCardsHorarios');
    var tabla = document.getElementById('contenedorTablaHorarios');
    var btnCards = document.getElementById('btnVistaCards');
    var btnTabla = document.getElementById('btnVistaTabla');
    if (vista === 'tabla') {
        if (cards) cards.classList.add('d-none');
        if (tabla) tabla.classList.remove('d-none');
        if (btnCards) { btnCards.classList.remove('btn-success'); btnCards.classList.add('btn-outline-success'); }
        if (btnTabla) { btnTabla.classList.remove('btn-outline-success'); btnTabla.classList.add('btn-success'); }
        iniciarTablaHorarios();
    } else {
        if (tabla) tabla.classList.add('d-none');
        if (cards) cards.classList.remove('d-none');
        if (btnTabla) { btnTabla.classList.remove('btn-success'); btnTabla.classList.add('btn-outline-success'); }
        if (btnCards) { btnCards.classList.remove('btn-outline-success'); btnCards.classList.add('btn-success'); }
        cargarCardsHorarios();
    }
}

/**
 * Inicialización original del DataTable de horarios (sin cambios de lógica).
 */
function iniciarTablaHorarios() {
    if (horTablaIniciada) {
        return;
    }
    if (!document.getElementById('tablaHorariosPrincipal')) {
        return;
    }
    if (typeof $ === 'undefined' || !$.fn.DataTable) {
        return;
    }
    horTablaIniciada = true;

    if ($.fn.DataTable.isDataTable('#tablaHorariosPrincipal')) {
        $('#tablaHorariosPrincipal').DataTable().destroy();
    }

    var horCols = [
        { data: 0, orderable: true, searchable: false },
        { data: 1, orderable: false, searchable: false }
    ];
    function horDiaCell(td, cellData) {
        var marcador = $(td).children('span[data-hor-td]');
        if (marcador.length) {
            td.className += ' position-relative';
            td.style.background = marcador.attr('data-hor-bg');
            td.style.color = marcador.attr('data-hor-fg');
            marcador.remove();
        }
    }
    for (var i = 0; i < horDias.length; i++) {
        horCols.push({ data: i + 2, orderable: true, searchable: false, createdCell: horDiaCell });
    }

    $('#tablaHorariosPrincipal').DataTable({
        serverSide: true,
        processing: true,
        searchDelay: 400,
        pageLength: 20,
        lengthMenu: [[20, 50, 100, 200], [20, 50, 100, 200]],
        order: [[0, 'desc']],
        dom: 'Bfrtip',
        buttons: [
            { extend: 'copyHtml5', text: '<i class="fa-solid fa-copy fa-2x"></i> ', titleAttr: 'Copiar', className: 'btn' },
            { extend: 'csvHtml5', text: '<i class="fa-solid fa-file-csv fa-2x"></i> ', titleAttr: 'Exportar a CSV', className: 'btn' },
            { extend: 'excelHtml5', text: '<i class="fa-solid fa-file-excel fa-2x"></i> ', titleAttr: 'Exportar a Excel', className: 'btn' }
        ],
        language: {
            lengthMenu: "Mostrar _MENU_ registros",
            zeroRecords: "No se encontraron resultados",
            emptyTable: "No hay información",
            info: "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
            infoEmpty: "Mostrando registros del 0 al 0 de un total de 0 registros",
            infoFiltered: "(filtrado de un total de _MAX_ registros)",
            sSearch: "Buscar:",
            oPaginate: { sFirst: "Primero", sLast: "Último", sNext: "Siguiente", sPrevious: "Anterior" },
            sProcessing: "Procesando..."
        },
        ajax: {
            url: 'controllers/horchor.php',
            type: 'POST',
            data: function (d) {
                return $.extend({}, d, horFiltros);
            }
        },
        columns: horCols
    });
}

/**
 * Extrae el idfic del registro desde la celda "Ficha y Programa"
 * (el backend lo expone en data-idfic del enlace existente).
 */
function horCardIdFicha(celdaFicha) {
    var cont = document.createElement('div');
    cont.innerHTML = celdaFicha || '';
    var enlace = cont.querySelector('a[data-idfic]');
    return enlace ? enlace.getAttribute('data-idfic') : '';
}

/**
 * Construye una card por registro (ficha) reutilizando el HTML de celdas
 * que entrega el endpoint: [ficha, acciones, dia1..diaN].
 */
function horCardHTML(celdas, indice) {
    var celdaFicha = celdas[0] || '';
    var celdaAcciones = celdas[1] || '';
    var idfic = horCardIdFicha(celdaFicha);
    var collapseId = 'collapseHorCard' + horCardsEstado.pagina + '_' + indice;

    // Extraer enlaces de Agenda y Reporte desde celdaAcciones (idfic ya dinámico del backend)
    var tempDiv = document.createElement('div');
    tempDiv.innerHTML = celdaAcciones;
    var linkAgenda = tempDiv.querySelector('a[href*="pg=1509"]');
    var linkReporte = tempDiv.querySelector('a[href*="horrhrfc"]');

    var btnAgenda = linkAgenda
        ? '<a class="btn btn-outline-primary btn-sm" href="' + linkAgenda.href + '" title="Agenda Pedagógica"><i class="fa-solid fa-file-lines me-1"></i>'
        : '';
    var btnReporte = linkReporte
        ? '<a class="btn btn-outline-secondary btn-sm" href="' + linkReporte.href + '" title="Reporte de ficha" target="_blank"><i class="fa-solid fa-file-pdf me-1"></i>'
        : '';

    var barraAcciones = (btnAgenda || btnReporte)
        ? '<div class="d-flex flex-wrap gap-2 mt-2 pt-2 border-top">' + btnAgenda + btnReporte + '</div>'
        : '';

    var diasConHorario = 0;
    var bloquesDias = '';
    for (var i = 0; i < horDias.length; i++) {
        var htmlDia = celdas[i + 2] || '';
        var vacio = horCardCeldaVacia(htmlDia);
        if (!vacio) {
            diasConHorario++;
        }
        var nombreDia = horDiasNombres[i] || ('Día ' + (i + 1));
        bloquesDias += '<div class="border rounded p-2 mb-2 bg-light">'
            + '<div class="fw-bold small text-uppercase text-muted mb-1">' + nombreDia + '</div>'
            + '<div>' + (vacio ? '<span class="text-muted small">Sin horario</span>' : htmlDia) + '</div>'
            + '</div>';
    }

    var resumen = diasConHorario === 0
        ? '<span class="badge bg-light text-muted border">Sin horario esta semana</span>'
        : '<span class="badge bg-success">' + diasConHorario + ' día' + (diasConHorario === 1 ? '' : 's') + ' con horario</span>';

    return '<div class="col">'
        + '<div class="card shadow-sm border-0 h-100">'
        + '<div class="card-header bg-white py-2 d-flex flex-wrap justify-content-between align-items-center gap-2" role="button" tabindex="0" '
        + 'onclick="alternarCardHorario(\'' + collapseId + '\', this)" onkeydown="if(event.key===\'Enter\'||event.key===\' \'){event.preventDefault();alternarCardHorario(\'' + collapseId + '\', this);}" '
        + 'title="Clic para ver el detalle del registro">'
        + '<div>' + celdaFicha + '</div>'
        + '<div class="d-flex align-items-center gap-2">' + resumen + '<i class="fa-solid fa-chevron-down text-muted"></i></div>'
        + '</div>'
        + '<div class="card-body pb-2">' + barraAcciones + '</div>'
        + '<div class="collapse" id="' + collapseId + '">'
        + '<div class="card-body pt-0">'
        + bloquesDias
        + '<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 pt-2 border-top">'
        + '<div class="d-flex gap-2">' + celdaAcciones + '</div>'
        + (idfic !== ''
            ? '<a class="btn btn-success btn-sm" href="home.php?pg=1517&idfic_filter=' + encodeURIComponent(idfic) + '" title="Continuar con la gestión del registro">'
              + '<i class="fa-solid fa-arrow-right me-1"></i> Gestionar horario</a>'
            : '')
        + '</div>'
        + '</div>'
        + '</div>'
        + '</div>'
        + '</div>';
}

/**
 * Una celda de día se considera vacía cuando no trae contenido visible.
 */
function horCardCeldaVacia(htmlDia) {
    var cont = document.createElement('div');
    cont.innerHTML = htmlDia || '';
    return cont.textContent.trim() === '' && cont.querySelectorAll('a,button').length === 0;
}

/**
 * Muestra/oculta el detalle de la card (misma información del registro).
 */
function alternarCardHorario(collapseId, cabecera) {
    var el = document.getElementById(collapseId);
    if (!el) {
        return;
    }
    if (window.bootstrap && bootstrap.Collapse) {
        bootstrap.Collapse.getOrCreateInstance(el, { toggle: false }).toggle();
    } else {
        el.classList.toggle('show');
    }
    // Solo la card accionada: rotar su chevron como realimentación visual.
    if (cabecera && cabecera.querySelector) {
        var ico = cabecera.querySelector('.fa-chevron-down');
        if (ico) {
            ico.style.transition = 'transform .2s';
            ico.style.transform = (ico.style.transform === 'rotate(180deg)') ? '' : 'rotate(180deg)';
        }
    }
}
// Los enlaces internos del encabezado (ficha) no deben alternar la card.
document.addEventListener('click', function (e) {
    var t = e.target && e.target.closest ? e.target.closest('#gridCardsHorarios .card-header a') : null;
    if (t) {
        e.stopPropagation();
    }
}, true);

/**
 * Orden de tarjetas (Ficha o día de la semana). Usa el ordenamiento que ya
 * implementa el endpoint server-side; solo cambia la presentación.
 */
function cambiarOrdenCardsHorarios(valor) {
    var partes = String(valor || '0|desc').split('|');
    horCardsEstado.ordenColumna = parseInt(partes[0], 10) || 0;
    horCardsEstado.ordenDir = (partes[1] === 'asc') ? 'asc' : 'desc';
    horCardsEstado.pagina = 0;
    cargarCardsHorarios();
}

/**
 * Paginación de tarjetas (mismo tamaño de página del DataTable).
 */
function paginarCardsHorarios(direccion) {
    var nueva = horCardsEstado.pagina + direccion;
    if (nueva < 0) {
        return;
    }
    var maxPag = Math.max(0, Math.ceil(horCardsEstado.total / horCardsEstado.porPagina) - 1);
    if (nueva > maxPag) {
        return;
    }
    horCardsEstado.pagina = nueva;
    cargarCardsHorarios();
}

/**
 * Carga una página de tarjetas desde el endpoint server-side de horarios.
 */
function cargarCardsHorarios() {
    var grid = document.getElementById('gridCardsHorarios');
    var info = document.getElementById('infoPaginacionCards');
    if (!grid) {
        return;
    }

    grid.innerHTML = '<div class="col"><div class="text-center py-4 text-muted">'
        + '<div class="spinner-border text-success" role="status"></div>'
        + '<p class="mt-2 small">Cargando horarios...</p></div></div>';

    horCardsEstado.draw++;
    var params = new URLSearchParams();
    params.append('ope', horFiltros.ope);
    params.append('idare', horFiltros.idare || '');
    params.append('idfic', horFiltros.idfic || '');
    params.append('idusu', horFiltros.idusu || '');
    params.append('semana_selector', horFiltros.semana_selector || '');
    params.append('draw', String(horCardsEstado.draw));
    params.append('start', String(horCardsEstado.pagina * horCardsEstado.porPagina));
    params.append('length', String(horCardsEstado.porPagina));
    params.append('search[value]', horCardsEstado.busqueda);
    params.append('order[0][column]', String(horCardsEstado.ordenColumna));
    params.append('order[0][dir]', horCardsEstado.ordenDir);

    fetch('controllers/horchor.php', { method: 'POST', body: params })
        .then(function (resp) { return resp.json(); })
        .then(function (json) {
            var filas = (json && json.data) || [];
            horCardsEstado.total = json ? (parseInt(json.recordsFiltered, 10) || 0) : 0;

            if (filas.length === 0) {
                grid.innerHTML = '<div class="col-12"><div class="alert alert-info mb-0">'
                    + '<i class="fa-solid fa-circle-info me-2"></i>No hay horarios para los filtros seleccionados.</div></div>';
            } else {
                grid.innerHTML = filas.map(function (celdas, i) { return horCardHTML(celdas, i); }).join('');
            }

            var inicio = horCardsEstado.total === 0 ? 0 : (horCardsEstado.pagina * horCardsEstado.porPagina) + 1;
            var fin = Math.min(horCardsEstado.total, (horCardsEstado.pagina + 1) * horCardsEstado.porPagina);
            if (info) {
                info.textContent = 'Mostrando ' + inicio + ' al ' + fin + ' de ' + horCardsEstado.total + ' registros';
            }
            var btnAnt = document.getElementById('btnCardsAnterior');
            var btnSig = document.getElementById('btnCardsSiguiente');
            if (btnAnt) btnAnt.disabled = horCardsEstado.pagina <= 0;
            if (btnSig) btnSig.disabled = fin >= horCardsEstado.total;
        })
        .catch(function () {
            grid.innerHTML = '<div class="col-12"><div class="alert alert-warning mb-0">'
                + '<i class="fa-solid fa-triangle-exclamation me-2"></i>No se pudieron cargar los horarios. Intente de nuevo.</div></div>';
        });
}

$(function () {
    var buscador = document.getElementById('buscadorCardsHorarios');
    var temporizador = null;
    if (buscador) {
        buscador.addEventListener('input', function () {
            if (temporizador) {
                clearTimeout(temporizador);
            }
            temporizador = setTimeout(function () {
                horCardsEstado.busqueda = buscador.value.trim();
                horCardsEstado.pagina = 0;
                // Si está en vista tabla, su propio buscador manda; solo recargar tarjetas.
                var tablaVisible = document.getElementById('contenedorTablaHorarios')
                    && !document.getElementById('contenedorTablaHorarios').classList.contains('d-none');
                if (!tablaVisible) {
                    cargarCardsHorarios();
                }
            }, 400);
        });
    }
    // Vista principal: tarjetas con los mismos datos del DataTable.
    cargarCardsHorarios();
});
</script>
<?php }?>

<!-- Modal Personalizado de Opciones de Eliminación -->
<div id="modalEliminacion" class="modal-eliminacion d-none">
    <div class="modal-eliminacion-content">
        <div class="modal-eliminacion-header">
            <h5 class="modal-eliminacion-title">
                <i class="fa-solid fa-trash-can me-2"></i>
                Opciones de Eliminación
            </h5>
            <button type="button" class="modal-eliminacion-close" onclick="cerrarModalEliminacion()">
                <i class="fa-solid fa-times"></i>
            </button>
        </div>
        
        <div class="modal-eliminacion-body">
            <div class="info-horario">
                <i class="fa-solid fa-info-circle me-2"></i>
                <strong>Horario seleccionado:</strong><br>
                <span id="infoHorarioEliminar"></span>
            </div>
            
            <div id="infoFechas" class="info-fechas d-none">
                <i class="fa-solid fa-calendar-alt me-2"></i>
                <strong>Información de fechas:</strong><br>
                <span id="detalleFechas"></span>
            </div>
            
            <div class="opciones-eliminacion">
                <div class="opcion-eliminacion">
                    <div class="opcion-icono">
                        <i class="fa-solid fa-calendar-day fa-3x"></i>
                    </div>
                    <div class="opcion-contenido">
                        <h6>Eliminar Individual</h6>
                        <p>Elimina solo este horario específico</p>
                        <button type="button" class="btn-eliminar-individual" onclick="eliminarHorarioIndividual()">
                            <i class="fa-solid fa-trash-can me-1"></i>
                            Eliminar Individual
                        </button>
                    </div>
                </div>
                
                <div class="opcion-eliminacion">
                    <div class="opcion-icono">
                        <i class="fa-solid fa-calendar-week fa-3x"></i>
                    </div>
                    <div class="opcion-contenido">
                        <h6>Eliminar Grupo</h6>
                        <p>Elimina todo el grupo de horarios relacionados</p>
                        <button type="button" class="btn-eliminar-grupo" onclick="eliminarGrupoHorarios()">
                            <i class="fa-solid fa-trash-can me-1"></i>
                            Eliminar Grupo
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="advertencia-eliminacion">
                <i class="fa-solid fa-exclamation-triangle me-2"></i>
                <strong>Advertencia:</strong> Esta acción no se puede deshacer.
            </div>
        </div>
        
        <div class="modal-eliminacion-footer">
            <button type="button" class="btn-cancelar" onclick="cerrarModalEliminacion()">
                <i class="fa-solid fa-times me-1"></i>
                Cancelar
            </button>
        </div>
    </div>
</div>

<!-- Modal Personalizado de Opciones de Edición para Transversales -->
<div id="modalEdicionTransversal" class="modal-eliminacion d-none">
    <div class="modal-eliminacion-content">
        <div class="modal-eliminacion-header">
            <h5 class="modal-eliminacion-title">
                <i class="fa-solid fa-edit me-2"></i>
                Opciones de Edición para Transversal
            </h5>
            <button type="button" class="modal-eliminacion-close" onclick="cerrarModalEdicionTransversal()">
                <i class="fa-solid fa-times"></i>
            </button>
        </div>
        
        <div class="modal-eliminacion-body">
            <div class="info-horario">
                <i class="fa-solid fa-info-circle me-2"></i>
                <strong>Transversal seleccionada:</strong><br>
                <span id="infoTransversalEditar"></span>
            </div>
            
            <div id="infoFechasEdicion" class="info-fechas d-none">
                <i class="fa-solid fa-calendar-alt me-2"></i>
                <strong>Información de fechas:</strong><br>
                <span id="detalleFechasEdicion"></span>
            </div>
            
            <div class="opciones-eliminacion">
                <div class="opcion-eliminacion">
                    <div class="opcion-icono">
                        <i class="fa-solid fa-calendar-day fa-3x"></i>
                    </div>
                    <div class="opcion-contenido">
                        <h6>Editar Individual</h6>
                        <p>Modifica solo este horario específico</p>
                        <button type="button" class="btn-eliminar-individual" onclick="editarTransversalIndividual()">
                            <i class="fa-solid fa-edit me-1"></i>
                            Editar Individual
                        </button>
                    </div>
                </div>
                
                <div class="opcion-eliminacion">
                    <div class="opcion-icono">
                        <i class="fa-solid fa-calendar-week fa-3x"></i>
                    </div>
                    <div class="opcion-contenido">
                        <h6>Editar Grupo</h6>
                        <p>Modifica todo el grupo de horarios relacionados</p>
                        <button type="button" class="btn-editar-grupo" onclick="editarTransversalGrupo()">
                            <i class="fa-solid fa-edit me-1"></i>
                            Editar Grupo
                        </button>
                    </div>
                </div>
            </div>
            
            
        </div>
        
        <!-- Sin footer: solo se permite cerrar con la X del header -->
    </div>
</div>

<!-- Overlay para el modal -->
<div id="overlayEliminacion" class="overlay-eliminacion d-none" onclick="cerrarTodoOverlay()"></div>

<!-- Estilos CSS para el Modal Personalizado -->
<!-- Inicio Horas por instructor -->
<?php if ($perfil_usuario['idper'] != 4): // Solo mostrar para perfiles que no sean restringidos ?>
<div class="card shadow-sm mt-4 mb-4 border-0">
    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center py-2">
        <h5 class="mb-0 fs-6 fw-bold">
            <i class="fa-solid fa-user-clock me-2"></i>Horas por instructor (Carga Horaria)
        </h5>
        <span class="badge bg-light text-success fw-bold" id="badgeInstructoresCount">
            0 Instructores
        </span>
    </div>
    <div class="card-body p-3">
        <div class="row g-2 align-items-center mb-3">
            <div class="col-12 col-md-5">
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="search" id="buscadorInsCards" class="form-control" placeholder="Buscar instructor, documento, área..." autocomplete="off">
                </div>
            </div>
            <div class="col-12 col-md-7">
                <select id="ordenInsCards" class="form-select form-select-sm" title="Ordenar instructores" onchange="cambiarOrdenInsCards(this.value)">
                    <option value="5|asc">Orden: menos horas primero</option>
                    <option value="5|desc">Orden: más horas primero</option>
                    <option value="0|asc">Orden: nombre (A-Z)</option>
                    <option value="1|asc">Orden: área (A-Z)</option>
                </select>
            </div>
        </div>
        <div id="gridInsCards" class="row row-cols-1 g-3"></div>
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3">
            <small id="infoPaginacionInsCards" class="text-muted"></small>
            <div class="btn-group btn-group-sm" role="group" aria-label="Paginación de instructores">
                <button type="button" id="btnInsCardsAnterior" class="btn btn-outline-success" onclick="paginarInsCards(-1)">
                    <i class="fa-solid fa-chevron-left me-1"></i> Anterior
                </button>
                <button type="button" id="btnInsCardsSiguiente" class="btn btn-outline-success" onclick="paginarInsCards(1)">
                    Siguiente <i class="fa-solid fa-chevron-right ms-1"></i>
                </button>
            </div>
        </div>
    </div>
</div>
<?php endif; // Fin de la condición para perfiles restringidos ?>
<!-- Fin Horas por instructor -->

<!-- Botón de Producción - Eliminar Todos los Horarios -->
<?php if ($_SESSION['idper'] == 21): // Solo para administradores ?>
<div class="container mt-4 mb-5 text-center">
    <!--
     * ====================================================================================
     * (Esto es para eliminar TODOS los registros de horarios, usar solo en PRODUCCIÓN)
     * ====================================================================================
     -->
    <!--
    <button type="button" 
            class="btn btn-danger btn-lg" 
            onclick="confirmarEliminacionTotal()">
        <i class="fa-solid fa-trash-can me-2"></i>
        ELIMINAR TODOS LOS HORARIOS
    </button>
    <br>
    <small class="text-muted">Solo para producción</small>
    -->
</div>
<?php endif; ?>

<!-- Modal de detalle de horarios -->
<div class="modal fade" id="modalDetalle" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalle de Horas - <span id="nombreInstructor"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Pestañas de navegación -->
                <ul class="nav nav-tabs" id="detalleTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="detalle-tab" data-bs-toggle="tab" data-bs-target="#detalle" type="button" role="tab" aria-controls="detalle" aria-selected="true">
                            <i class="fa-solid fa-calendar-day me-2"></i>Detalle Mensual
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="trimestre-tab" data-bs-toggle="tab" data-bs-target="#trimestre" type="button" role="tab" aria-controls="trimestre" aria-selected="false">
                            <i class="fa-solid fa-calendar-range me-2"></i>Por Rango
                        </button>
                    </li>
                    <!-- Pestaña: Contador de Horarios -->
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="contador-tab" data-bs-toggle="tab" data-bs-target="#contenidoContador" type="button" role="tab" aria-controls="contenidoContador" aria-selected="false">
                            <i class="fa-solid fa-calculator me-2"></i>Horas Anuales
                        </button>
                    </li>
                </ul>
                
                <!-- Contenido de las pestañas -->
                <div class="tab-content" id="detalleTabsContent">
                    <!-- Pestaña Detalle Mensual -->
                    <div class="tab-pane fade show active" id="detalle" role="tabpanel" aria-labelledby="detalle-tab">
                        <div id="contenidoModal">
                            <!-- Aquí se carga el contenido del detalle mensual -->
                        </div>
                    </div>
                    
                    <!-- Pestaña Contador de Horarios eliminada - duplicada -->
                    
                    <!-- Pestaña Por Rango -->
                    <div class="tab-pane fade" id="trimestre" role="tabpanel" aria-labelledby="trimestre-tab">
                        <div id="contenidoRango">
                            <!-- Aquí se cargará el contenido del detalle por rango -->
                        </div>
                    </div>
                    <!-- Pestaña: Contador de Horarios -->
                    <div class="tab-pane fade" id="contenidoContador" role="tabpanel" aria-labelledby="contador-tab">
                        <!-- Aquí se cargará el contenido del contador de horarios -->
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Reporte Mensual de Horas -->
<div class="modal fade" id="modalReporteMensual" tabindex="-1" aria-labelledby="modalReporteMensualLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="modalReporteMensualLabel">
                    <i class="fa-solid fa-calendar-days me-2"></i>Reporte Mensual de Horas por Instructor
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Filtros del reporte mensual -->
                <div class="card p-3 bg-light mb-3">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-2">
                            <label for="rep_men_anio" class="form-label fw-bold">Año</label>
                            <input type="number" id="rep_men_anio" class="form-control" value="<?= date('Y') ?>" min="2020" max="2030">
                        </div>
                        <div class="col-md-3">
                            <label for="rep_men_mes" class="form-label fw-bold">Mes</label>
                            <select id="rep_men_mes" class="form-select">
                                <?php for ($m = 1; $m <= 12; $m++): ?>
                                    <option value="<?= $m ?>" <?= ($m == (int)date('n')) ? 'selected' : '' ?>>
                                        <?= obtenerNombreMes($m) ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="rep_men_idare" class="form-label fw-bold">Área</label>
                            <select id="rep_men_idare" class="form-select">
                                <option value="todas">-- Todas las áreas asignadas --</option>
                                <?php if (!empty($dar)): ?>
                                    <?php foreach ($dar as $de): ?>
                                        <option value="<?= $de['idare'] ?>"><?= htmlspecialchars($de['nomare']) ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-4 d-flex gap-2">
                            <button type="button" class="btn btn-success flex-grow-1" onclick="cargarReporteMensual()">
                                <i class="fa-solid fa-filter me-1"></i> Consultar
                            </button>
                            <button type="button" class="btn btn-outline-dark" onclick="imprimirElemento('contenedorReporteMensual', 'Reporte Mensual de Horas')">
                                <i class="fa-solid fa-print"></i>
                            </button>
                        </div>
                        
                    </div>
                </div>

                <!-- Contenedor del Reporte Mensual -->
                <div id="contenedorReporteMensual">
                    <div class="text-center py-4 text-muted">
                        <i class="fa-solid fa-spinner fa-spin fa-2x mb-2 d-block"></i>
                        Cargando reporte mensual...
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Reporte Anual de Horas -->
<div class="modal fade" id="modalReporteAnual" tabindex="-1" aria-labelledby="modalReporteAnualLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen-lg-down modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalReporteAnualLabel">
                    <i class="fa-solid fa-chart-line me-2"></i>Reporte Anual de Horas por Instructor (12 Meses)
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Filtros del reporte anual -->
                <div class="card p-3 bg-light mb-3">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-3">
                            <label for="rep_anual_anio" class="form-label fw-bold">Año</label>
                            <input type="number" id="rep_anual_anio" class="form-control" value="<?= date('Y') ?>" min="2020" max="2030">
                        </div>
                        <div class="col-md-5">
                            <label for="rep_anual_idare" class="form-label fw-bold">Área</label>
                            <select id="rep_anual_idare" class="form-select">
                                <option value="todas">-- Todas las áreas asignadas --</option>
                                <?php if (!empty($dar)): ?>
                                    <?php foreach ($dar as $de): ?>
                                        <option value="<?= $de['idare'] ?>"><?= htmlspecialchars($de['nomare']) ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-4 d-flex gap-2">
                            <button type="button" class="btn btn-primary flex-grow-1" onclick="cargarReporteAnual()">
                                <i class="fa-solid fa-filter me-1"></i> Consultar
                            </button>
                            <button type="button" class="btn btn-outline-dark" onclick="imprimirElemento('contenedorReporteAnual', 'Reporte Anual de Horas')">
                                <i class="fa-solid fa-print"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Contenedor del Reporte Anual -->
                <div id="contenedorReporteAnual">
                    <div class="text-center py-4 text-muted">
                        <i class="fa-solid fa-spinner fa-spin fa-2x mb-2 d-block"></i>
                        Cargando reporte anual...
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de edición de horarios -->
<div id="modalEditarTransversal" class="modal-eliminacion d-none">
    <div class="modal-eliminacion-content">
        <div class="modal-eliminacion-header">
            <h5 class="modal-eliminacion-title" id="tituloModalEdicion">
                <i class="fa-solid fa-edit me-2"></i>
                Editar Horario
            </h5>
            <button type="button" class="modal-eliminacion-close" onclick="cerrarModalEditarTransversal()">
                <i class="fa-solid fa-times"></i>
            </button>
        </div>
        
        <div class="modal-eliminacion-body">
            <form id="frmEditarTransversal">
                <input type="hidden" name="idhor_edit" id="idhor_edit">
                <input type="hidden" name="es_transversal_edit" id="es_transversal_edit" value="0">
                <input type="hidden" name="es_otros_edit" id="es_otros_edit" value="0">
                <input type="hidden" name="ope" value="edit">
                
                <div class="row">
                    <!-- Nombre de la Transversal (visible solo si es transversal) -->
                    <div class="form-group col-md-12 mb-3 d-none" id="grupo_nombre_transversal">
                        <label for="nombre_transversal_edit">Nombre de la Transversal <span class="text-danger">*</span></label>
                        <input type="text" 
                               name="nombre_transversal" 
                               id="nombre_transversal_edit" 
                               class="form-control" 
                               placeholder="Ej: Inglés, Ética, Emprendimiento..."
                               maxlength="100">
                        <small class="form-text text-muted">Nombre de la materia transversal</small>
                    </div>

                    <!-- Actividad Especial (visible solo si es actividad otros) -->
                    <div class="form-group col-md-8 mb-3 d-none" id="grupo_actividad_otros">
                        <label for="actividad_edit">Actividad Especial <span class="text-danger">*</span></label>
                        <input type="text" 
                               name="actividad" 
                               id="actividad_edit" 
                               class="form-control" 
                               placeholder="Ej: Reunión, Preparación de clases..."
                               maxlength="150">
                    </div>
                    <div class="form-group col-md-4 mb-3 d-none" id="grupo_horas_otros">
                        <label for="horas_otros_edit">Horas <span class="text-danger">*</span></label>
                        <input type="number" 
                               name="horas_otros" 
                               id="horas_otros_edit" 
                               class="form-control" 
                               min="1" max="24" step="0.5">
                    </div>

                    <!-- Ficha -->
                    <div class="form-group col-md-6 mb-3">
                        <label for="ficha_edit">Ficha</label>
                        <input type="text" 
                               id="ficha_edit" 
                               class="form-control" 
                               readonly>
                    </div>

                    <!-- Día -->
                    <div class="form-group col-md-6 mb-3">
                        <label for="dia_edit">Día</label>
                        <input type="text" 
                               id="dia_edit" 
                               class="form-control" 
                               readonly>
                    </div>

                    <!-- Instructor -->
                    <div class="form-group col-md-6 mb-3" id="grupo_instructor_edit">
                        <label for="instructor_edit">Instructor</label>
                        <select name="idusu" id="instructor_edit" class="form-select">
                            <option value="">Sin instructor</option>
                            <?php if ($din): ?>
                                <?php foreach ($din as $de): ?>
                                    <option value="<?= $de['idusu']; ?>">
                                        <?= strtoupper($de['nomusu']) . ' - ' . $de['ndocusu']; ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <!-- Aula -->
                    <div class="form-group col-md-6 mb-3" id="grupo_aula_edit">
                        <label for="aula_edit">Aula</label>
                        <select name="idaul" id="aula_edit" class="form-select">
                            <option value="">Sin aula asignada</option>
                            <?php if ($dau): ?>
                                <?php foreach ($dau as $de): ?>
                                    <option value="<?= $de['idaul']; ?>">
                                        <?= $de['idaul'] . '. ' . $de['nomaul']; ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <!-- Hora Inicio -->
                    <div class="form-group col-md-6 mb-3">
                        <label for="hinihor_edit">Hora Inicio</label>
                        <input type="time" name="hinihor" id="hinihor_edit" class="form-control">
                    </div>

                    <!-- Hora Fin -->
                    <div class="form-group col-md-6 mb-3">
                        <label for="hfinhor_edit">Hora Fin</label>
                        <input type="time" name="hfinhor" id="hfinhor_edit" class="form-control">
                    </div>

                    <!-- Fecha Inicio -->
                    <div class="form-group col-md-6 mb-3">
                        <label for="fecha_inicio_edit">Fecha Inicio</label>
                        <input type="date" id="fecha_inicio_edit" class="form-control">
                    </div>

                    <!-- Fecha Fin -->
                    <div class="form-group col-md-6 mb-3">
                        <label for="fecha_fin_edit">Fecha Fin</label>
                        <input type="date" id="fecha_fin_edit" class="form-control">
                    </div>

                    <!-- Formación directa (para actividades "otros") -->
                    <input type="hidden" name="es_formacion_directa" id="es_formacion_directa_edit" value="1">
                </div>
            </form>
        </div>
        
        <div class="modal-eliminacion-footer">
            <button type="button" class="btn-eliminar-individual" onclick="guardarEdicionTransversal()">
                <i class="fa-solid fa-save me-1"></i>
                Guardar Cambios
            </button>
        </div>
    </div>
</div>

<script>
let festivosCache = null;

async function obtenerFestivosColombia(año) {
    // Si ya tenemos los festivos en cache para este año, los devolvemos
    if (festivosCache && festivosCache.año === año) {
        return festivosCache.festivos;
    }
    
    try {
        const response = await fetch(`https://date.nager.at/api/v3/PublicHolidays/${año}/CO`);
        const festivos = await response.json();
        
        // Convertir a formato simple de fechas
        const fechasFestivos = festivos.map(festivo => festivo.date);
        
        // Guardar en cache
        festivosCache = {
            año: año,
            festivos: fechasFestivos
        };
        
        return fechasFestivos;
    } catch (error) {
        console.warn('No se pudieron obtener los festivos de la API, usando fallback:', error);
        
        // Fallback con festivos fijos de 2024
        const festivosFallback = [
            '2024-01-01', '2024-01-08', '2024-03-25', '2024-03-28', '2024-03-29',
            '2024-05-01', '2024-05-13', '2024-06-03', '2024-06-10', '2024-07-01',
            '2024-07-20', '2024-08-07', '2024-08-19', '2024-10-14', '2024-11-04',
            '2024-11-11', '2024-12-08', '2024-12-25'
        ];
        
        return festivosFallback;
    }
}

// Abre el modal de detalle de horarios
function abrirDetalle(button) {
    try {
        const instructor = JSON.parse(button.dataset.instructor);
        // Limpiar el contenido del modal antes de cargar nuevos datos
        document.getElementById('contenidoModal').innerHTML = '';
        document.getElementById('nombreInstructor').textContent = '';
        
        // Guardar el instructor actual en variable global para acceso desde otras funciones
        window.instructorActual = instructor;
        window.currentInstructorId = instructor.id_instructor;
        
        mostrarDetalle(instructor.nombre, instructor.documento, instructor.horas, instructor.max_horas, instructor.tipo_contrato, instructor.detalle, instructor.horas_senova || 0);
    } catch (error) {
        console.error('Error al abrir detalle:', error);
        document.getElementById('contenidoModal').innerHTML = `
            <div class="alert alert-danger">
                Error al cargar los datos del instructor. Intente nuevamente.
            </div>
        `;
    }
}
// Fin Abre el modal de detalle de horarios

// Calcula los días laborales
async function calcularDiasLaborales(mes, año) {
    const festivos = await obtenerFestivosColombia(año);
    let diasLaborales = 0;
    let diasEnMes = new Date(año, mes, 0).getDate();
    
    for (let dia = 1; dia <= diasEnMes; dia++) {
        let fecha = new Date(año, mes - 1, dia);
        let diaSemana = fecha.getDay();
        let fechaStr = año + '-' + String(mes).padStart(2, '0') + '-' + String(dia).padStart(2, '0');
        
        // Si es de lunes (1) a viernes (5) y no es festivo
        if (diaSemana >= 1 && diaSemana <= 5 && !festivos.includes(fechaStr)) {
            diasLaborales++;
        }
    }
    
    return diasLaborales;
}

/**
 * Calcula los días laborales de un mes específico (versión síncrona)
 */
function calcularDiasLaboralesMes(mes, año) {
    // Usar festivos fijos para evitar problemas de async
    const festivos = [
        '01-01', '01-08', '03-25', '03-28', '03-29',
        '05-01', '05-13', '06-03', '06-10', '07-01',
        '07-20', '08-07', '08-19', '10-14', '11-04',
        '11-11', '12-08', '12-25'
    ];
    
    let diasLaborales = 0;
    let diasEnMes = new Date(año, mes, 0).getDate();
    
    for (let dia = 1; dia <= diasEnMes; dia++) {
        let fecha = new Date(año, mes - 1, dia);
        let diaSemana = fecha.getDay();
        let fechaStr = String(mes).padStart(2, '0') + '-' + String(dia).padStart(2, '0');
        
        // Si es de lunes (1) a viernes (5) y no es festivo
        if (diaSemana >= 1 && diaSemana <= 5 && !festivos.includes(fechaStr)) {
            diasLaborales++;
        }
    }
    
    return diasLaborales;
}
// Fin Calcula los días laborales

/**
 * Actualiza los cálculos dinámicos cuando se cambia el mes para instructores de planta
 */
async function actualizarCalculosDinamicos(mes, año) {
    try {
        const diasLaborales = await calcularDiasLaborales(mes, año);
        const horasFormacionDirecta = diasLaborales * 6.4;
        const horasOtros = diasLaborales * 2.1;
        const horasEsperadasMensual = horasFormacionDirecta + horasOtros;
        
        // Actualizar la información en el alert
        const alertElement = document.querySelector('.alert-success');
        if (alertElement) {
            const diasElement = alertElement.querySelector('p:nth-child(2)');
            const horasElement = alertElement.querySelector('p:nth-child(3)');
            
            if (diasElement) {
                diasElement.innerHTML = `Días laborales en ${obtenerNombreMes(mes)} ${año}: <strong>${diasLaborales} días</strong>`;
            }
            if (horasElement) {
                horasElement.innerHTML = `Horas esperadas mensual: <strong>${horasEsperadasMensual.toFixed(1)} horas</strong> (Planta: cálculo dinámico)`;
            }
        }
        
        // La sección de horas esperadas ya no es necesaria, está integrada en el resumen
        
        // Actualizar el título de la tabla de detalle
        const tablaTitulo = document.querySelector('.card-header h6');
        if (tablaTitulo) {
            tablaTitulo.textContent = `Detalle de Horarios y Actividades - ${obtenerNombreMes(mes)} ${año}`;
        }
        
        // La función actualizarTablaDetalleMes ya maneja la actualización del resumen
        // Solo necesitamos actualizar la información del alert y la tabla de detalle
        
    } catch (error) {
        console.error('Error actualizando cálculos dinámicos:', error);
    }
}

// Llenar el body del modal con el detalle de horarios
async function mostrarDetalle(nombre, documento, horas, maxHoras, tipoContrato, detalle, horasSenova = 0) {
    // Mostrar nombre del instructor
    document.getElementById('nombreInstructor').textContent = nombre;
    
    // Mostrar loading mientras carga
    document.getElementById('contenidoModal').innerHTML = `
        <div class="text-center">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
            <p class="mt-2">Calculando días laborales...</p>
        </div>
    `;

    
    let fechaActual = new Date();
    let mesActual = fechaActual.getMonth() + 1;
    let añoActual = fechaActual.getFullYear();
    
    try {
        let diasLaborales = await calcularDiasLaborales(mesActual, añoActual);
        
        
        let contenido = '';
        
        // Definir horas máximas según tipo de contrato
        const horasMaximas = tipoContrato === 'planta' ? 160 : (window.CONTRATISTA_MAX_HORAS || 180);
        
        if (tipoContrato === 'contratista') {
            // Para contratistas, las horas esperadas son fijas (180)
            const horasEsperadasContratista = (window.CONTRATISTA_MAX_HORAS || 180);
            
            contenido = `
                <div class="alert alert-info">
                    <h6>Instructor Contratista</h6>
                    <p>Máximo permitido mensual: <strong>${horasMaximas} horas</strong></p>
                </div>
                
                <!-- Selector de mes/año para contratistas -->
                <div class="row mb-3 align-items-end">
                    <div class="col-12 col-md-4">
                        <label class="form-label mb-1">Mes</label>
                        <select id="selectorMesDetalle" class="form-select">
                            ${[1,2,3,4,5,6,7,8,9,10,11,12].map(m=>`<option value="${m}" ${m===mesActual?'selected':''}>${obtenerNombreMes(m)}</option>`).join('')}
                        </select>
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label mb-1">Año</label>
                        <input id="selectorAnioDetalle" type="number" class="form-control" value="${añoActual}" min="2000" max="2100" />
                    </div>
                    <div class="col-12 col-md-5 d-grid d-md-block">
                        <button id="btnAplicarMesDetalle" class="btn w-100" type="button">
                            <i class="fa-solid fa-rotate me-2"></i>Aplicar mes seleccionado
                        </button>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h6>Información Personal</h6>
                                <p><strong>Nombre:</strong> ${nombre}</p>
                                <p><strong>Documento:</strong> ${documento}</p>
                                <p><strong>Tipo de contrato:</strong> ${tipoContrato === 'planta' ? 'Planta' : 'Contratista'}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h6>Resumen de Horas (${obtenerNombreMes(mesActual)})</h6>
                                <p><strong>Horas directas a formación:</strong> 0 hrs</p>
                                <p><strong>Horas otros:</strong> 0 hrs</p>
                                <p><strong>Total asignado:</strong> ${horas}/${(window.CONTRATISTA_MAX_HORAS||180).toFixed ? (window.CONTRATISTA_MAX_HORAS||180).toFixed(1) : (window.CONTRATISTA_MAX_HORAS||180)} hrs</p>
                                <p><strong>Horas disponibles:</strong> ${((window.CONTRATISTA_MAX_HORAS||180) - horas).toFixed(1)} hrs</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row mb-3 align-items-end">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h6>Detalle de Horarios y Actividades - ${obtenerNombreMes(mesActual)} ${añoActual}</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <!-- La tabla será renderizada con datos REALES desde backend por actualizarTablaDetalleMes() -->
                                </div>
                                <div class="progress mt-3"></div>
                                <small class="text-muted">
                                    <span class="badge">Formación</span>
                                    <span class="badge bg-success">Otros/Formación</span>
                                    <span class="badge bg-warning text-dark">Otros</span>
                                    <span class="badge text-white">SENOVA</span>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        } else {
            // Instructor de planta - cálculo dinámico basado en días laborales
            let horasFormacionDirecta = diasLaborales * 6.4;
            let horasOtros = diasLaborales * 2.1;
            let horasEsperadasMensual = horasFormacionDirecta + horasOtros;
            
            // Obtener datos reales del instructor para el mes actual desde el backend
            let totalHorasFormacion = 0;
            let totalHorasOtros = 0;
            let totalGeneral = 0;
            
            // Obtener datos reales del instructor para el mes actual
            try {
                const formData = new FormData();
                formData.append('ope', 'get_horarios_por_mes');
                formData.append('id_instructor', window.instructorActual?.id_instructor);
                formData.append('mes', mesActual);
                formData.append('año', añoActual);
                
                const response = await fetch('controllers/horchor.php', {
                    method: 'POST',
                    body: formData
                });
                
                if (response.ok) {
                    const data = await response.json();
                    
                    if (data.success && data.resumen) {
                        // Usar los datos del resumen que ya están calculados
                        totalHorasFormacion = data.resumen.horas_formacion || 0;
                        totalHorasOtros = data.resumen.horas_otros || 0;
                        totalGeneral = data.resumen.total_horas || 0;
                    }
                }
            } catch (error) {
                console.error('Error obteniendo datos del instructor:', error);
            }
            
            // Para instructores de planta, usar el cálculo dinámico como máximo
            let horasComplementarias = Math.max(0, horasEsperadasMensual - totalGeneral);
            
            // Los valores están correctos: 18, 0, 18, 169
            
            contenido = `
                <div class="alert alert-success">
                    <h6>Instructor de Planta</h6>
                    <p>Días laborales en ${obtenerNombreMes(mesActual)} ${añoActual}: <strong>${diasLaborales} días</strong></p>
                    <p>Horas esperadas mensual: <strong>${horasEsperadasMensual.toFixed(1)} horas</strong> (Planta: cálculo dinámico)</p>
                    <small class="text-muted">*Cálculo basado en festivos oficiales de Colombia</small>
                </div>

                <div class="row g-2 align-items-end mb-3">
                    <div class="col-12 col-md-4">
                        <label class="form-label mb-1">Seleccionar mes</label>
                        <select id="selectorMesDetalle" class="form-select">
                            ${[1,2,3,4,5,6,7,8,9,10,11,12].map(m=>`<option value="${m}" ${m===mesActual?'selected':''}>${obtenerNombreMes(m)}</option>`).join('')}
                        </select>
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label mb-1">Año</label>
                        <input id="selectorAnioDetalle" type="number" class="form-control" value="${añoActual}" min="2000" max="2100" />
                    </div>
                    <div class="col-12 col-md-5 d-grid d-md-block">
                        <button id="btnAplicarMesDetalle" class="btn w-100" type="button">
                            <i class="fa-solid fa-rotate me-2"></i>Aplicar mes seleccionado
                        </button>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h6>Información Personal</h6>
                                <p><strong>Nombre:</strong> ${nombre}</p>
                                <p><strong>Documento:</strong> ${documento}</p>
                                <p><strong>Tipo de contrato:</strong> ${tipoContrato === 'planta' ? 'Planta' : 'Contratista'}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h6>Resumen de Horas (${obtenerNombreMes(mesActual)})</h6>
                                <p><strong>Horas directas a formación:</strong> ${totalHorasFormacion}/${horasFormacionDirecta.toFixed(1)} hrs</p>
                                <p><strong>Horas otros:</strong> ${totalHorasOtros}/${horasOtros.toFixed(1)} hrs</p>
                                <p><strong>Total asignado:</strong> ${totalGeneral}/${horasEsperadasMensual.toFixed(1)} hrs</p>
                                <p><strong>Horas disponibles:</strong> ${horasComplementarias.toFixed(1)} hrs</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h6>Detalle de Horarios y Actividades - ${obtenerNombreMes(mesActual)} ${añoActual}</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <!-- La tabla será renderizada con datos REALES desde backend por actualizarTablaDetalleMes() -->
                                </div>
                                <div class="progress mt-3"></div>
                                <small class="text-muted">
                                    <span class="badge">Formación</span>
                                    <span class="badge bg-success">Otros/Formación</span>
                                    <span class="badge bg-warning text-dark">Otros</span>
                                    <span class="badge text-white">SENOVA</span>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }
        
        document.getElementById('contenidoModal').innerHTML = contenido;

        // Cargar y renderizar inmediatamente el detalle REAL del mes actual desde backend
        if (window.instructorActual && window.instructorActual.id_instructor) {
            await actualizarResumenMesSeleccionado(window.instructorActual.id_instructor, mesActual, añoActual);
        }

        // Enlazar selector de mes/año para actualizar resumen mensual con datos del backend
        const btnAplicar = document.getElementById('btnAplicarMesDetalle');
        if (btnAplicar) {
            btnAplicar.addEventListener('click', async () => {
                const mesSel = parseInt(document.getElementById('selectorMesDetalle').value, 10);
                let anioSel = parseInt(document.getElementById('selectorAnioDetalle').value, 10);
                
                // Validar que el año sea válido, usar año actual si no lo es
                if (isNaN(anioSel) || anioSel < 2000 || anioSel > 2100) {
                    anioSel = new Date().getFullYear();
                    document.getElementById('selectorAnioDetalle').value = anioSel;
                }
                
                // Recalcular días laborales y horas esperadas para el mes seleccionado
                if (window.instructorActual?.tipo_contrato === 'planta') {
                    await actualizarCalculosDinamicos(mesSel, anioSel);
                }
                
                await actualizarResumenMesSeleccionado(window.instructorActual?.id_instructor, mesSel, anioSel);
            });
        }
        
    } catch (error) {
        console.error('Error al calcular días laborales:', error);
        
        // Fallback simple sin API
        let diasLaborales = 22; // Días laborales promedio por mes
        
        let contenido = `
            <div class="alert alert-warning">
                <h6>Modo Offline</h6>
                <p>No se pudo conectar a la API de festivos. Usando cálculo aproximado.</p>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h6>Información Personal</h6>
                            <p><strong>Nombre:</strong> ${nombre}</p>
                            <p><strong>Documento:</strong> ${documento}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h6>Horas</h6>
                            <p><strong>Horas asignadas:</strong> ${horas}</p>
                            <p><strong>Horas máximas:</strong> ${maxHoras}</p>
                            <p><strong>Tipo:</strong> ${tipoContrato}</p>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        if (detalle && detalle.length > 0) {
            contenido += `
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h6>Horarios Asignados</h6>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Día</th>
                                            <th>Ficha</th>
                                            <th>Jornada</th>
                                            <th>Aula</th>
                                            <th>Horas</th>
                                            <th>Tipo</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
            `;
            
            detalle.forEach(horario => {
                let tipoClase, tipoTexto;
                
                // Tipos basados en la clasificación real del backend
                if (Number(horario.es_otros) && Number(horario.es_formacion_directa) === 2) {
                    tipoClase = 'bg-success';
                    tipoTexto = 'Otros/Formación';
                } else if (Number(horario.es_otros)) {
                    tipoClase = 'bg-warning text-dark';
                    tipoTexto = 'Otros';
                } else if (Number(horario.es_transversal)) {
                    tipoClase = 'bg-info';
                    tipoTexto = 'Transversal';
                } else {
                    tipoClase = 'bg-success text-white';
                    tipoTexto = 'Formación';
                }
                
                const diaOffline = (horario.dia || '') + (horario.fecha ? ', ' + formatearFechaLocal(horario.fecha) : '');
                const fichaOffline = horario.ficha || horario.nomfic || horario.actividad || '';
                const jornadaOffline = (horario.hinihor && horario.hfinhor) ? (horario.hinihor + ' - ' + horario.hfinhor) : (horario.jornada || '');
                const horasOffline = Number(horario.horas) || 0;
                
                contenido += `
                    <tr>
                        <td>${diaOffline}</td>
                        <td>${fichaOffline}</td>
                        <td>${jornadaOffline}</td>
                        <td>${horario.aula}</td>
                        <td>${horasOffline.toFixed(1)} hrs</td>
                        <td><span class="badge ${tipoClase}" ${tipoClase === 'text-white' ? '' : ''}>${tipoTexto}</span></td>
                        <td class="text-nowrap">${window.sag_BotonesHorario(horario)}</td>
                    </tr>
                `;
            });
            
            contenido += `
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }
        
        document.getElementById('contenidoModal').innerHTML = contenido;
    }
}

function obtenerNombreMes(mes) {
    const meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
                   'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
    return meses[mes - 1];
}

// Función para calcular días laborales en un mes (excluyendo fines de semana y festivos)
async function calcularDiasLaborales(mes, año) {
    // Validar parámetros
    if (!mes || !año || isNaN(mes) || isNaN(año)) {
        return 0;
    }
    
    try {
        // Lista de festivos fijos de Colombia
        const festivosFijos = [
            { mes: 1, dia: 1, nombre: 'Año Nuevo' },
            { mes: 1, dia: 6, nombre: 'Día de los Reyes Magos' },
            { mes: 3, dia: 25, nombre: 'Día de San José' },
            { mes: 4, dia: 13, nombre: 'Domingo de Ramos' }, // Variable
            { mes: 4, dia: 14, nombre: 'Lunes Santo' }, // Variable
            { mes: 4, dia: 15, nombre: 'Martes Santo' }, // Variable
            { mes: 4, dia: 16, nombre: 'Miércoles Santo' }, // Variable
            { mes: 4, dia: 17, nombre: 'Jueves Santo' }, // Variable
            { mes: 4, dia: 18, nombre: 'Viernes Santo' }, // Variable
            { mes: 4, dia: 19, nombre: 'Sábado Santo' }, // Variable
            { mes: 5, dia: 1, nombre: 'Día del Trabajo' },
            { mes: 5, dia: 12, nombre: 'Día de la Madre' },
            { mes: 5, dia: 26, nombre: 'Ascensión del Señor' }, // Variable
            { mes: 6, dia: 9, nombre: 'Corpus Christi' }, // Variable
            { mes: 6, dia: 16, nombre: 'Sagrado Corazón' }, // Variable
            { mes: 6, dia: 30, nombre: 'San Pedro y San Pablo' },
            { mes: 7, dia: 20, nombre: 'Día de la Independencia' },
            { mes: 8, dia: 7, nombre: 'Batalla de Boyacá' },
            { mes: 8, dia: 18, nombre: 'Asunción de la Virgen' },
            { mes: 10, dia: 13, nombre: 'Día de la Raza' },
            { mes: 11, dia: 3, nombre: 'Todos los Santos' },
            { mes: 11, dia: 17, nombre: 'Independencia de Cartagena' },
            { mes: 12, dia: 8, nombre: 'Inmaculada Concepción' },
            { mes: 12, dia: 25, nombre: 'Navidad' }
        ];
        
        // Filtrar festivos fijos del mes específico
        const festivosDelMesFijos = festivosFijos.filter(festivo => festivo.mes === mes);
        
        // Intentar obtener festivos de la API como respaldo
        let festivosAPI = [];
        try {
            const response = await fetch(`https://date.nager.at/api/v3/PublicHolidays/${año}/CO`);
            if (response.ok) {
                const festivos = await response.json();
                festivosAPI = festivos.filter(festivo => {
                    const fecha = new Date(festivo.date);
                    return fecha.getMonth() === (mes - 1);
                });
            }
        } catch (apiError) {
            console.warn('Error obteniendo festivos de API, usando lista fija:', apiError);
        }
        
        // Usar festivos fijos como base, API como respaldo
        const festivosDelMes = festivosDelMesFijos.length > 0 ? festivosDelMesFijos : festivosAPI;
        
        
        // Crear array de fechas festivas solo del mes
        const fechasFestivas = festivosDelMes.map(festivo => {
            // Si es de la lista fija, usar el campo 'dia'
            if (festivo.dia) {
                return festivo.dia;
            }
            // Si es de la API, extraer el día de la fecha
            const fecha = new Date(festivo.date);
            return fecha.getDate();
        });
        
        // Obtener el número de días en el mes
        const diasEnMes = new Date(año, mes, 0).getDate();
        let diasLaborales = 0;
        
        // Contar días laborales (lunes a viernes, excluyendo festivos)
        for (let dia = 1; dia <= diasEnMes; dia++) {
            const fecha = new Date(año, mes - 1, dia);
            const diaSemana = fecha.getDay(); // 0 = domingo, 1 = lunes, ..., 6 = sábado
            
            // Si es día laboral (lunes a viernes) y no es festivo
            if (diaSemana >= 1 && diaSemana <= 5 && !fechasFestivas.includes(dia)) {
                diasLaborales++;
            }
        }
        return diasLaborales;
    } catch (error) {
        console.error('Error calculando días laborales:', error);
        // Fallback: calcular días laborales sin festivos
        const diasEnMes = new Date(año, mes, 0).getDate();
        let diasLaborales = 0;
        
        for (let dia = 1; dia <= diasEnMes; dia++) {
            const fecha = new Date(año, mes - 1, dia);
            const diaSemana = fecha.getDay();
            if (diaSemana >= 1 && diaSemana <= 5) {
                diasLaborales++;
            }
        }
        
        return diasLaborales;
    }
}

/**
 * Calcula los días específicos del mes actual
 */
// Obsoleto: cálculo de días específicos en cliente (removido)

// Obsoleto: obtener días del mes por día de semana (removido)



// Ejecutar cuando se carga la página
document.addEventListener('DOMContentLoaded', function() {
    // Agregar evento para cambiar entre pestañas
    document.getElementById('reconteo-tab')?.addEventListener('click', function() {
        // Cuando se hace clic en la pestaña de contador de horarios, cargar los datos
        if (window.instructorActual) {
            cargarContadorHorarios(window.instructorActual);
        }
    });
    
    // Agregar evento para la pestaña de rango
    document.getElementById('trimestre-tab')?.addEventListener('click', function() {
        // Cuando se hace clic en la pestaña de rango, cargar los datos
        if (window.instructorActual) {
            cargarDetallePorRango(window.instructorActual);
        }
    });
});

/**
 * Carga el detalle por rango para un instructor específico
 * 
 * Esta función es el punto de entrada principal para mostrar el detalle por rango.
 * Se ejecuta cuando el usuario hace clic en la pestaña "Por Rango".
 * 
 * @param {Object} instructor - Objeto con información del instructor
 * @param {string} instructor.id_instructor - ID único del instructor
 * @param {string} instructor.nombre - Nombre completo del instructor
 * @param {string} instructor.tipo_contrato - Tipo de contrato (planta/contratista)
 */
function cargarDetallePorRango(instructor) {
    const contenidoRango = document.getElementById('contenidoRango');
    
    // Mostrar indicador de carga
    contenidoRango.innerHTML = `
        <div class="text-center">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Cargando detalle por rango...</span>
            </div>
            <p class="mt-2">Cargando opciones de rango...</p>
        </div>
    `;
    
    // Cargar la interfaz de selección de rango
    mostrarSelectorRango(instructor);
}

/**
 * Muestra el selector de rango con opciones automáticas y manuales
 */
function mostrarSelectorRango(instructor) {
    const contenidoRango = document.getElementById('contenidoRango');
    
    let html = `
        <div class="row mb-4">
            <div class="col-12">
                <div class="alert alert-info">
                    <h6><i class="fa-solid fa-calendar-range me-2"></i>Detalle por Rango</h6>
                    <p class="mb-1"><strong>Instructor:</strong> ${instructor.nombre}</p>
                    <p class="mb-0">Seleccione un rango de fechas para ver las horas asignadas en ese período específico.</p>
                </div>
            </div>
        </div>
        
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h6><i class="fa-solid fa-calendar-alt me-2"></i>Selección de Rango</h6>
                    </div>
                    <div class="card-body">
                        <!-- Checkbox para rango automático -->
                        <div class="form-check mb-3">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   name="rango_automatico" 
                                   id="rango_automatico" 
                                   value="1"
                                   onchange="toggleRangoAutomatico()">
                            <label class="form-check-label" for="rango_automatico">
                                <strong><i class="fa-solid fa-calendar-alt me-2"></i>Rango por evento del calendario académico</strong>
                            </label>
                            <small class="form-text text-muted d-block mt-1">
                                Marque esta opción para seleccionar automáticamente el rango de fechas desde el calendario académico
                            </small>
                        </div>
                        
                        <!-- Campos de rango automático (inicialmente ocultos) -->
                        <div id="campos_rango_automatico" class="d-none">
                            <div class="form-group mb-3">
                                <label for="tipo_evento_rango">Tipo de Evento</label>
                                <select id="tipo_evento_rango" class="form-select" onchange="cargarEventosPorTipoRango()">
                                    <option value="">Seleccione el tipo de evento...</option>
                                </select>
                                <small class="form-text text-muted">Seleccione el tipo de evento del calendario</small>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="evento_rango">Evento del Calendario</label>
                                <select id="evento_rango" class="form-select" onchange="seleccionarEventoRango()">
                                    <option value="">Primero seleccione el tipo de evento...</option>
                                </select>
                                <small class="form-text text-muted">Seleccione el evento específico</small>
                            </div>
                            
                            <div id="info_evento_rango" class="alert alert-success d-none">
                                <small>
                                    <i class="fa-solid fa-info-circle me-1"></i>
                                    <strong>Evento seleccionado:</strong> 
                                    <span id="titulo_evento_rango">-</span>
                                    <br>
                                    <strong>Período:</strong> 
                                    <span id="fecha_inicio_evento_rango">-</span>
                                    <br>
                                    <strong>Trimestre:</strong> 
                                    <span id="trimestre_evento_rango">-</span>
                                </small>
                            </div>
                        </div>
                        
                        <!-- Campos de rango manual -->
                        <div id="campos_rango_manual">
                            <div class="form-group mb-3">
                                <label for="fecha_inicio_rango">Fecha de Inicio <span class="text-danger">*</span></label>
                                <input type="date" 
                                       id="fecha_inicio_rango" 
                                       class="form-control" 
                                       value="${new Date().toISOString().split('T')[0]}"
                                       onchange="actualizarInfoRango()">
                                <small class="form-text text-muted">Fecha desde cuando inicia el rango</small>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="fecha_fin_rango">Fecha de Fin <span class="text-danger">*</span></label>
                                <input type="date" 
                                       id="fecha_fin_rango" 
                                       class="form-control" 
                                       value="${new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0]}"
                                       onchange="actualizarInfoRango()">
                                <small class="form-text text-muted">Fecha hasta cuando es válido el rango</small>
                            </div>
                            
                            <div id="info_rango_manual" class="alert alert-info d-none">
                                <small>
                                    <i class="fa-solid fa-info-circle me-1"></i>
                                    <strong>Rango seleccionado:</strong> 
                                    <span id="formatoInicioRango">-</span> al <span id="formatoFinRango">-</span>
                                    (<span id="duracion_rango">-</span> días)
                                </small>
                            </div>
                        </div>
                        
                        <!-- Botón para calcular -->
                        <div class="text-center mt-3">
                            <button type="button" 
                                    class="btn btn-primary" 
                                    onclick="calcularHorasRango()">
                                <i class="fa-solid fa-calculator me-2"></i>
                                Calcular Horas del Rango
                            </button>
                            

                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h6><i class="fa-solid fa-chart-bar me-2"></i>Resumen del Rango</h6>
                    </div>
                    <div class="card-body">
                        <div id="resumen_rango">
                            <p class="text-muted text-center">
                                <i class="fa-solid fa-arrow-left me-2"></i>
                                Seleccione un rango y calcule las horas
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-12">
                <div id="detalle_rango" class="d-none">
                    <!-- Aquí se mostrará el detalle detallado del rango -->
                </div>
            </div>
        </div>
    `;
    
    contenidoRango.innerHTML = html;
    
    // Cargar tipos de eventos para el rango automático
    cargarTiposEventosParaRango();
    
    // Actualizar información del rango manual
    actualizarInfoRango();
}

/**
 * Carga los tipos de eventos disponibles para el rango automático
 */
function cargarTiposEventosParaRango() {
    // Crear FormData para obtener los tipos de eventos
    const formData = new FormData();
    formData.append('ope', 'get_tipos_eventos');
    
    fetch('controllers/horchor.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.tipos) {
            const selectTipo = document.getElementById('tipo_evento_rango');
            selectTipo.innerHTML = '<option value="">Seleccione el tipo de evento...</option>';
            
            data.tipos.forEach(tipo => {
                const option = document.createElement('option');
                option.value = tipo;
                option.textContent = tipo.charAt(0).toUpperCase() + tipo.slice(1).replace('_', ' ');
                selectTipo.appendChild(option);
            });
        }
    })
    .catch(error => {
        console.error('Error cargando tipos de eventos:', error);
    });
}

/**
 * Alterna entre rango automático y manual
 */
function toggleRangoAutomatico() {
    const checkbox = document.getElementById('rango_automatico');
    const camposAutomatico = document.getElementById('campos_rango_automatico');
    const camposManual = document.getElementById('campos_rango_manual');
    
    if (checkbox.checked) {
        // Mostrar campos automáticos, ocultar manuales
        camposAutomatico.style.display = 'block';
        camposManual.style.display = 'none';
        
        // Limpiar campos manuales
        document.getElementById('fecha_inicio_rango').value = '';
        document.getElementById('fecha_fin_rango').value = '';
        document.getElementById('info_rango_manual').style.display = 'none';
        
        // Limpiar resumen
        document.getElementById('resumen_rango').innerHTML = `
            <p class="text-muted text-center">
                <i class="fa-solid fa-arrow-left me-2"></i>
                Seleccione un evento del calendario
            </p>
        `;
        
        // Ocultar detalle
        document.getElementById('detalle_rango').style.display = 'none';
        
    } else {
        // Mostrar campos manuales, ocultar automáticos
        camposAutomatico.style.display = 'none';
        camposManual.style.display = 'block';
        
        // Limpiar campos automáticos
        document.getElementById('tipo_evento_rango').value = '';
        document.getElementById('evento_rango').value = '';
        document.getElementById('info_evento_rango').style.display = 'none';
        
        // Restaurar fechas por defecto
        const hoy = new Date();
        const en30Dias = new Date(hoy.getTime() + 30 * 24 * 60 * 60 * 1000);
        
        document.getElementById('fecha_inicio_rango').value = hoy.toISOString().split('T')[0];
        document.getElementById('fecha_fin_rango').value = en30Dias.toISOString().split('T')[0];
        
        // Actualizar información del rango manual
        actualizarInfoRango();
    }
}

/**
 * Actualiza la información del rango manual
 */
function actualizarInfoRango() {
    const fechaInicio = document.getElementById('fecha_inicio_rango').value;
    const fechaFin = document.getElementById('fecha_fin_rango').value;
    const infoRango = document.getElementById('info_rango_manual');
    const formatoInicio = document.getElementById('formatoInicioRango');
    const formatoFin = document.getElementById('formatoFinRango');
    const duracionRango = document.getElementById('duracion_rango');
    
    if (fechaInicio && fechaFin) {
        // Crear fechas usando solo la fecha (sin tiempo) para evitar problemas de UTC
        const inicio = new Date(fechaInicio);
        const fin = new Date(fechaFin);
        
        // Asegurar que las fechas se interpreten como locales
        inicio.setHours(0, 0, 0, 0);
        fin.setHours(0, 0, 0, 0);
        
        const diasDiferencia = Math.ceil((fin - inicio) / (1000 * 60 * 60 * 24)) + 1; // +1 para incluir ambos días
        
        // Formatear fechas para mostrar usando la fecha original del input
        const textoInicio = formatearFechaLocal(fechaInicio);
        const textoFin = formatearFechaLocal(fechaFin);
        
        // Actualizar el contenido de los elementos
        formatoInicio.textContent = textoInicio;
        formatoFin.textContent = textoFin;
        duracionRango.textContent = `${diasDiferencia} días`;
        
        infoRango.style.display = 'block';
        
        // Cambiar color según la duración
        if (diasDiferencia <= 30) {
            infoRango.className = 'alert alert-success';
        } else if (diasDiferencia <= 90) {
            infoRango.className = 'alert alert-info';
        } else if (diasDiferencia <= 180) {
            infoRango.className = 'alert alert-warning';
        } else {
            infoRango.className = 'alert alert-danger';
        }
        
    } else {
        infoRango.style.display = 'none';
    }
}

/**
 * Carga los eventos según el tipo seleccionado para rango
 */
function cargarEventosPorTipoRango() {
    const tipoEvento = document.getElementById('tipo_evento_rango').value;
    const selectEvento = document.getElementById('evento_rango');
    
    if (!tipoEvento) {
        selectEvento.innerHTML = '<option value="">Primero seleccione el tipo de evento...</option>';
        return;
    }
    
    // Limpiar eventos anteriores
    selectEvento.innerHTML = '<option value="">Cargando eventos...</option>';
    
    // Crear FormData para enviar la consulta
    const formData = new FormData();
    formData.append('ope', 'get_eventos_por_tipo');
    formData.append('tipo_evento', tipoEvento);
    
    fetch('controllers/horchor.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.eventos) {
            selectEvento.innerHTML = '<option value="">Seleccione un evento...</option>';
            
            data.eventos.forEach(evento => {
                const option = document.createElement('option');
                option.value = evento.id;
                
                // Formatear fechas para mostrar mes y día (evitar problemas de UTC)
                const fechaInicio = new Date(evento.fecha_inicio + 'T00:00:00');
                const fechaFin = new Date(evento.fecha_fin + 'T00:00:00');
                
                const mesInicio = fechaInicio.toLocaleDateString('es-ES', { month: 'short' });
                const diaInicio = fechaInicio.getDate();
                const mesFin = fechaFin.toLocaleDateString('es-ES', { month: 'short' });
                const diaFin = fechaFin.getDate();
                
                // Crear texto del option con rango de fechas
                let textoOption = `${evento.titulo} (${evento.anio})`;
                
                // Si las fechas son del mismo mes, mostrar solo una vez
                if (fechaInicio.getMonth() === fechaFin.getMonth()) {
                    textoOption += ` - ${diaInicio} al ${diaFin} ${mesInicio}`;
                } else {
                    // Si son meses diferentes, mostrar ambos
                    textoOption += ` - ${diaInicio} ${mesInicio} al ${diaFin} ${mesFin}`;
                }
                
                textoOption += ` - ${evento.trimestre}`;
                
                option.textContent = textoOption;
                option.setAttribute('data-fecha-inicio', evento.fecha_inicio);
                option.setAttribute('data-fecha-fin', evento.fecha_fin);
                option.setAttribute('data-titulo', evento.titulo);
                option.setAttribute('data-trimestre', evento.trimestre);
                selectEvento.appendChild(option);
            });
        } else {
            selectEvento.innerHTML = '<option value="">No se encontraron eventos</option>';
        }
    })
    .catch(error => {
        console.error('Error cargando eventos:', error);
        selectEvento.innerHTML = '<option value="">Error al cargar eventos</option>';
    });
}

/**
 * Selecciona un evento y llena automáticamente las fechas
 */
function seleccionarEventoRango() {
    const selectEvento = document.getElementById('evento_rango');
    const eventoId = selectEvento.value;
    
    if (!eventoId) {
        document.getElementById('info_evento_rango').style.display = 'none';
        return;
    }
    
    const option = selectEvento.options[selectEvento.selectedIndex];
    const fechaInicio = option.getAttribute('data-fecha-inicio');
    const fechaFin = option.getAttribute('data-fecha-fin');
    const titulo = option.getAttribute('data-titulo');
    const trimestre = option.getAttribute('data-trimestre');
    
    // Mostrar información del evento
    document.getElementById('titulo_evento_rango').textContent = titulo;
    
    // Formatear fechas para mostrar de manera más clara (evitando problemas UTC)
    // Separar las fechas manualmente para evitar problemas de zona horaria
    const [añoInicio, mesInicioNum, diaInicioNum] = fechaInicio.split('-').map(Number);
    const [añoFin, mesFinNum, diaFinNum] = fechaFin.split('-').map(Number);
    
    const meses = [
        'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio',
        'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'
    ];
    
    const mesInicio = meses[mesInicioNum - 1];
    const mesFin = meses[mesFinNum - 1];
    const diaInicio = diaInicioNum;
    const diaFin = diaFinNum;
    
    // Crear texto del rango de fechas
    let textoRango;
    if (mesInicioNum === mesFinNum) {
        // Mismo mes
        textoRango = `${diaInicio} al ${diaFin} de ${mesInicio}`;
    } else {
        // Meses diferentes
        textoRango = `${diaInicio} de ${mesInicio} al ${diaFin} de ${mesFin}`;
    }
    
    document.getElementById('fecha_inicio_evento_rango').textContent = textoRango;
    document.getElementById('trimestre_evento_rango').textContent = trimestre;
    document.getElementById('info_evento_rango').style.display = 'block';
}

/**
 * Calcula las horas del instructor para el rango seleccionado
 */
function calcularHorasRango() {
    const rangoAutomatico = document.getElementById('rango_automatico').checked;
    let fechaInicio, fechaFin;
    
    if (rangoAutomatico) {
        // Obtener fechas del evento seleccionado
        const selectEvento = document.getElementById('evento_rango');
        if (!selectEvento.value) {
            alert('Debe seleccionar un evento del calendario académico.');
            return;
        }
        
        const option = selectEvento.options[selectEvento.selectedIndex];
        fechaInicio = option.getAttribute('data-fecha-inicio');
        fechaFin = option.getAttribute('data-fecha-fin');
        
    } else {
        // Obtener fechas del rango manual
        fechaInicio = document.getElementById('fecha_inicio_rango').value;
        fechaFin = document.getElementById('fecha_fin_rango').value;
        
        if (!fechaInicio || !fechaFin) {
            alert('Debe seleccionar tanto la fecha de inicio como la fecha de fin.');
            return;
        }
        
        if (fechaInicio >= fechaFin) {
            alert('La fecha de fin debe ser posterior a la fecha de inicio.');
            return;
        }
    }
    
    // Calcular y mostrar las horas del rango
    calcularHorasRangoInstructor(window.instructorActual, fechaInicio, fechaFin);
}

/**
 * Calcula las horas del instructor para el rango seleccionado
 */
function calcularHorasRangoInstructor(instructor, fechaInicio, fechaFin) {

    
    const resumenRango = document.getElementById('resumen_rango');
    const detalleRango = document.getElementById('detalle_rango');
    
    // Mostrar indicador de carga
    resumenRango.innerHTML = `
        <div class="text-center">
            <div class="spinner-border spinner-border-sm" role="status">
                <span class="visually-hidden">Calculando...</span>
            </div>
            <p class="mt-2">Calculando horas del rango...</p>
        </div>
    `;
    
    // Crear FormData para obtener las horas del rango
    const formData = new FormData();
    formData.append('ope', 'get_horas_rango');
    formData.append('id_instructor', instructor.id_instructor);
    formData.append('fecha_inicio', fechaInicio);
    formData.append('fecha_fin', fechaFin);
    
    fetch('controllers/horchor.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarResumenRango(data.resumen, fechaInicio, fechaFin);
            mostrarDetalleRango(data.detalle, fechaInicio, fechaFin);
        } else {
            resumenRango.innerHTML = `
                <div class="alert alert-warning">
                    <i class="fa-solid fa-exclamation-triangle me-2"></i>
                    ${data.message || 'No se pudieron calcular las horas del rango'}
                </div>
            `;
        }
    })
    .catch(error => {
        resumenRango.innerHTML = `
            <div class="alert alert-danger">
                <i class="fa-solid fa-exclamation-triangle me-2"></i>
                Error al calcular las horas del rango. Intente nuevamente.
                <br><small>Error: ${error.message}</small>
            </div>
        `;
    });
}

/**
 * Muestra el resumen del rango
 */
function mostrarResumenRango(resumen, fechaInicio, fechaFin) {
    const resumenRango = document.getElementById('resumen_rango');
    
    // Calcular días del período
    const inicio = new Date(fechaInicio);
    const fin = new Date(fechaFin);
    const diasPeriodo = Math.ceil((fin - inicio) / (1000 * 60 * 60 * 24)) + 1;
    
    let html = `
        <div class="row text-center">
            <div class="col-md-4">
                <div class="card text-white">
                    <div class="card-body">
                        <h5 class="card-title">${resumen.total_horas}</h5>
                        <p class="card-text">Total Horas</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h5 class="card-title">${resumen.horas_formacion}</h5>
                        <p class="card-text">Formación</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h5 class="card-title">${resumen.horas_otros}</h5>
                        <p class="card-text">Otros</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="mt-3">
            <h6><i class="fa-solid fa-calendar-range me-2"></i>Rango Seleccionado</h6>
            <p class="mb-1"><strong>Período:</strong> ${diasPeriodo} días</p>
            <p class="mb-0"><strong>Fechas:</strong> ${formatearFechaLocal(fechaInicio)} al ${formatearFechaLocal(fechaFin)}</p>
        </div>
    `;
    
    resumenRango.innerHTML = html;
}





/**
 * Muestra el detalle detallado del rango
 */
function mostrarDetalleRango(detalle, fechaInicio, fechaFin) {
    const detalleRango = document.getElementById('detalle_rango');
    const puedeAcciones = (window.PERMITIR_EDITAR === true || window.PERMITIR_ELIMINAR === true);
    
    let html = `
        <div class="card">
            <div class="card-header">
                <h6><i class="fa-solid fa-list me-2"></i>Detalle de Horarios del Rango</h6>
                <small class="text-muted">Horarios por registro (horas reales según hora inicio/fin)</small>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Día</th>
                                <th>Ficha/Actividad</th>
                                <th>Jornada</th>
                                <th>Horas</th>
                                <th>Tipo</th>
                                ${puedeAcciones ? '<th>Acciones</th>' : ''}
                            </tr>
                        </thead>
                        <tbody>
    `;
    
    if (detalle && detalle.length > 0) {
        detalle.forEach(horario => {
            let tipoClase, tipoTexto;
            
            // Usar la nueva lógica basada en tipo_horas del backend
            if (horario.tipo_horas === 'formacion_directa') {
                tipoClase = 'bg-success';
                tipoTexto = 'Otros/Formación';
            } else if (horario.tipo_horas === 'otros') {
                tipoClase = 'bg-warning text-dark';
                tipoTexto = 'Otros';
            } else if (horario.es_transversal) {
                tipoClase = 'bg-info';
                tipoTexto = 'Transversal';
            } else {
                tipoClase = 'text-white';
                tipoTexto = 'Formación';
            }
            
            // Horas reales del registro (calculadas desde hinihor/hfinhor en el backend)
            const horasRango = Number(horario.horas) || 0;
            const diaRango = (horario.dia || '') + (horario.fecha ? ', ' + formatearFechaLocal(horario.fecha) : '');
            const jornadaRango = (horario.hinihor && horario.hfinhor) ? (horario.hinihor + ' - ' + horario.hfinhor) : (horario.jornada || '');
            
            html += `
                <tr>
                    <td>${diaRango}</td>
                    <td><strong>${horario.ficha}</strong></td>
                    <td><span class="badge bg-warning text-dark">${jornadaRango}</span></td>
                    <td><strong class="text-primary">${horasRango.toFixed(1)} hrs</strong></td>
                    <td><span class="badge ${tipoClase}" ${tipoClase === 'text-white' ? '' : ''}>${tipoTexto}</span></td>
                    ${puedeAcciones ? '<td class="text-nowrap">' + window.sag_BotonesHorario(horario) + '</td>' : ''}
                </tr>
            `;
        });
    } else {
        html += `
            <tr>
                <td colspan="${puedeAcciones ? 6 : 5}" class="text-center text-muted">
                    <i class="fa-solid fa-inbox me-2"></i>
                    No se encontraron horarios en el rango seleccionado
                </td>
            </tr>
        `;
    }
    
    html += `
                        </tbody>
                    </table>
                </div>
                
            </div>
        </div>
    `;
    
    detalleRango.innerHTML = html;
    detalleRango.style.display = 'block';
}

/**
 * Calcula las horas del instructor para el trimestre seleccionado
 */
function calcularHorasTrimestre(instructor, fechaInicio, fechaFin, tituloEvento, trimestre) {
    const resumenTrimestre = document.getElementById('resumen_trimestre');
    const detalleTrimestre = document.getElementById('detalle_trimestre');
    
    // Mostrar indicador de carga
    resumenTrimestre.innerHTML = `
        <div class="text-center">
            <div class="spinner-border spinner-border-sm" role="status">
                <span class="visually-hidden">Calculando...</span>
            </div>
            <p class="mt-2">Calculando horas del trimestre...</p>
        </div>
    `;
    
    // Crear FormData para obtener las horas del trimestre
    const formData = new FormData();
    formData.append('ope', 'get_horas_trimestre');
    formData.append('id_instructor', instructor.id_instructor);
    formData.append('fecha_inicio', fechaInicio);
    formData.append('fecha_fin', fechaFin);
    
    fetch('controllers/horchor.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarResumenTrimestre(data.resumen, tituloEvento, trimestre);
            mostrarDetalleTrimestre(data.detalle, fechaInicio, fechaFin);
        } else {
            resumenTrimestre.innerHTML = `
                <div class="alert alert-warning">
                    <i class="fa-solid fa-exclamation-triangle me-2"></i>
                    ${data.message || 'No se pudieron calcular las horas del trimestre'}
                </div>
            `;
        }
    })
    .catch(error => {
        console.error('Error calculando horas del trimestre:', error);
        resumenTrimestre.innerHTML = `
            <div class="alert alert-danger">
                <i class="fa-solid fa-exclamation-triangle me-2"></i>
                Error al calcular las horas del trimestre. Intente nuevamente.
            </div>
        `;
    });
}

/**
 * Muestra el resumen del trimestre
 */
function mostrarResumenTrimestre(resumen, tituloEvento, trimestre) {
    const resumenTrimestre = document.getElementById('resumen_trimestre');
    
    let html = `
        <div class="row text-center">
            <div class="col-md-4">
                <div class="card text-white">
                    <div class="card-body">
                        <h5 class="card-title">${resumen.total_horas}</h5>
                        <p class="card-text">Total Horas</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h5 class="card-title">${resumen.horas_formacion}</h5>
                        <p class="card-text">Formación</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h5 class="card-title">${resumen.horas_otros}</h5>
                        <p class="card-text">Otros</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="mt-3">
            <h6><i class="fa-solid fa-calendar-week me-2"></i>${trimestre}</h6>
            <p class="mb-1"><strong>Evento:</strong> ${tituloEvento}</p>
            <p class="mb-0"><strong>Período:</strong> ${resumen.dias_periodo} días</p>
        </div>
    `;
    
    resumenTrimestre.innerHTML = html;
}

/**
 * Muestra el detalle detallado del trimestre
 */
function mostrarDetalleTrimestre(detalle, fechaInicio, fechaFin) {
    const detalleTrimestre = document.getElementById('detalle_trimestre');
    const puedeAcciones = (window.PERMITIR_EDITAR === true || window.PERMITIR_ELIMINAR === true);
    
    let html = `
        <div class="card">
            <div class="card-header">
                <h6><i class="fa-solid fa-list me-2"></i>Detalle de Horarios del Trimestre</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Día</th>
                                <th>Ficha/Actividad</th>
                                <th>Aula</th>
                                <th>Horas</th>
                                <th>Tipo</th>
                                ${puedeAcciones ? '<th>Acciones</th>' : ''}
                            </tr>
                        </thead>
                        <tbody>
    `;
    
    if (detalle && detalle.length > 0) {
        detalle.forEach(horario => {
            let tipoClase, tipoTexto;
            
            // Usar la nueva lógica basada en tipo_horas del backend
            if (horario.tipo_horas === 'formacion_directa') {
                tipoClase = 'bg-success';
                tipoTexto = 'Otros/Formación';
            } else if (horario.tipo_horas === 'otros') {
                tipoClase = 'bg-warning text-dark';
                tipoTexto = 'Otros';
            } else if (horario.es_transversal) {
                tipoClase = 'bg-info';
                tipoTexto = 'Transversal';
            } else {
                tipoClase = 'text-white';
                tipoTexto = 'Formación';
            }
            
            const horasTrim = Number(horario.horas) || 0;
            const diaTrim = (horario.dia || '') + (horario.fecha ? ', ' + formatearFechaLocal(horario.fecha) : '');
            
            html += `
                <tr>
                    <td>${diaTrim}</td>
                    <td><strong>${horario.ficha}</strong></td>
                    <td>${horario.aula || 'Sin aula'}</td>
                    <td><strong>${horasTrim.toFixed(1)} hrs</strong></td>
                    <td><span class="badge ${tipoClase}" ${tipoClase === 'text-white' ? '' : ''}>${tipoTexto}</span></td>
                    ${puedeAcciones ? '<td class="text-nowrap">' + window.sag_BotonesHorario(horario) + '</td>' : ''}
                </tr>
            `;
        });
    } else {
        html += `
            <tr>
                <td colspan="${puedeAcciones ? 6 : 5}" class="text-center text-muted">
                    No hay horarios asignados en este trimestre
                </td>
            </tr>
        `;
    }
    
    html += `
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    `;
    
    detalleTrimestre.innerHTML = html;
    detalleTrimestre.style.display = 'block';
}

/**
 * Carga el reconteo anual para un instructor específico
 * 
 * Esta función es el punto de entrada principal para mostrar el reconteo anual.
 * Se ejecuta cuando el usuario hace clic en la pestaña "Reconteo Anual".
 * 
 * @param {Object} instructor - Objeto con información del instructor
 * @param {string} instructor.id_instructor - ID único del instructor
 * @param {string} instructor.nombre - Nombre completo del instructor
 * @param {string} instructor.tipo_contrato - Tipo de contrato (planta/contratista)
 */
// Función cargarContadorHorarios eliminada - se creará desde cero

// Función calcularContadorHorariosLocal eliminada - se creará desde cero

// Función calcularHorasMesLocal eliminada - se creará desde cero



/**
 * Obtiene las horas de una jornada específica (misma lógica que el backend)
 */
// Función obtenerHorasJornada eliminada - se creará desde cero

// Función duplicada removida - ya existe más arriba

/**
 * Verifica si un horario está activo en un mes específico
 * Evita que las horas se dupliquen en meses incorrectos
 */
function esHorarioActivoEnMes(horario, mes, año) {
    // Si el horario tiene fecha específica, usar esa fecha (OBLIGATORIO)
    if (horario.fecha_especifica) {
        const fechaHorario = new Date(horario.fecha_especifica);
        const mesHorario = fechaHorario.getMonth() + 1;
        const añoHorario = fechaHorario.getFullYear();
        return mesHorario === mes && añoHorario === año;
    }
    
    // Si NO tiene fecha específica, NO contar en ningún mes
    // Esto evita que horarios sin fecha se sumen incorrectamente
    return false;
}

/**
 * Filtra horarios por mes específico usando fecha_especifica
 */
function filtrarHorariosPorMes(horarios, mes, año) {
    return horarios.filter(horario => {
        if (!horario.fecha_especifica) return false;
        
        const fecha = new Date(horario.fecha_especifica);
        const mesHorario = fecha.getMonth() + 1;
        const añoHorario = fecha.getFullYear();
        
        return mesHorario === mes && añoHorario === año;
    });
}

/**
 * Calcula las horas de un mes usando la lógica de "ver detalle"
 */
function calcularHorasMesHorarios(horarios, mes, año) {
    let totalHoras = 0;
    
    horarios.forEach(horario => {
        if (horario.es_transversal || horario.es_otros == 0) {
            // Horario normal o transversal: usar jornada de la ficha
            const horasJornada = obtenerHorasJornada(horario.jornada);
            totalHoras += horasJornada;
        } else {
            // Actividad "otros": usar horas_otros
            const horasOtros = horario.horas_otros || 0;
            totalHoras += horasOtros;
        }
    });
    
    return totalHoras;
}

/**
 * Verifica la sincronización de un mes específico entre reconteo anual y ver detalle
 */
async function verificarSincronizacionMes(idInstructor, mes, año) {
    try {
        // Crear FormData para enviar la consulta
        const formData = new FormData();
        formData.append('ope', 'verificar_sincronizacion');
        formData.append('id_instructor', idInstructor);
        formData.append('mes', mes);
        formData.append('año', año);
        
        const response = await fetch('controllers/horchor.php', {
            method: 'POST',
            body: formData
        });
        
        if (!response.ok) {
            throw new Error('Error en la respuesta del servidor');
        }
        
        const data = await response.json();
        
        if (data.success && data.resultado) {
            return data.resultado;
        }
        
        // Fallback si no hay respuesta
        return {
            sincronizado: false,
            diferencia: 0,
            horas_ver_detalle: 0
        };
        
    } catch (error) {
        console.warn('Error verificando sincronización del mes:', error);
        return {
            sincronizado: false,
            diferencia: 0,
            horas_ver_detalle: 0
        };
    }
}

/**
 * Verifica la sincronización general del conteo
 */
async function verificarSincronizacionConteo(idInstructor, mes, año) {
    try {
        // Crear FormData para enviar la consulta
        const formData = new FormData();
        formData.append('ope', 'verificar_sincronizacion');
        formData.append('id_instructor', idInstructor);
        formData.append('mes', mes);
        formData.append('año', año);
        
        const response = await fetch('controllers/horchor.php', {
            method: 'POST',
            body: formData
        });
        
        if (!response.ok) {
            throw new Error('Error en la respuesta del servidor');
        }
        
        const data = await response.json();
        
        if (data.success && data.resultado) {
            return data.resultado;
        }
        
        // Fallback si no hay respuesta
        return {
            sincronizado: false,
            diferencia: 0,
            horas_ver_detalle: 0
        };
        
    } catch (error) {
        console.warn('Error verificando sincronización general:', error);
        return {
            sincronizado: false,
            diferencia: 0,
            horas_ver_detalle: 0
        };
    }
}

/**
 * Función de fallback para calcular reconteo anual si falla la conexión a la BD
 * Usa el método anterior de cálculo aproximado
 */
async function calcularReconteoAnualFallback(instructor) {
    const añoActual = new Date().getFullYear();
    const mesActual = new Date().getMonth() + 1;
    
    // Horas esperadas por año según tipo de contrato
    const horasEsperadasAnual = instructor.tipo_contrato === 'contratista' ? 2160 : 1920;
    
    let horasAcumuladas = 0;
    let mesesCompletados = [];
    
    for (let mes = 1; mes <= 12; mes++) {
        try {
            const diasLaborales = await calcularDiasLaborales(mes, añoActual);
            const horasMes = calcularHorasMes(instructor, mes, añoActual, diasLaborales);
            
            mesesCompletados.push({
                mes: mes,
                nombreMes: obtenerNombreMes(mes),
                diasLaborales: diasLaborales,
                horas: horasMes,
                completado: mes < mesActual,
                futuro: mes > mesActual,
                tieneHoras: horasMes > 0
            });
            
            if (mes <= mesActual) {
                horasAcumuladas += horasMes;
            }
        } catch (error) {
            console.warn(`Error calculando mes ${mes} (fallback):`, error);
        }
    }
    
    const mesesConHoras = mesesCompletados.filter(m => m.tieneHoras && m.mes <= mesActual).length;
    const promedioMensual = mesesConHoras > 0 ? horasAcumuladas / mesesConHoras : 0;
    const proyeccionAnual = promedioMensual * 12;
    const horasRestantes = Math.max(0, horasEsperadasAnual - horasAcumuladas);
    
    return {
        instructor: instructor,
        año: añoActual,
        mesActual: mesActual,
        horasEsperadasAnual: horasEsperadasAnual,
        horasAcumuladas: horasAcumuladas,
        horasRestantes: horasRestantes,
        proyeccionAnual: proyeccionAnual,
        mesesCompletados: mesesCompletados,
        porcentajeCompletado: (horasAcumuladas / horasEsperadasAnual) * 100,
        promedioMensual: promedioMensual,
        mesesConHoras: mesesConHoras,
        modo: 'fallback'
    };
}

/**
 * Calcula las horas para un mes específico (método de fallback)
 * Esta función se usa solo cuando no se pueden obtener datos reales de la BD
 */
function calcularHorasMes(instructor, mes, año, diasLaborales) {
    // Si no hay horarios asignados al instructor, las horas son 0
    if (!instructor.horas || instructor.horas === 0) {
        return 0;
    }
    
    // Para meses completados, usar las horas reales asignadas
    // Para el mes actual, usar las horas hasta la fecha
    const mesActual = new Date().getMonth() + 1;
    
    if (mes < mesActual) {
        // Meses completados: usar horas reales
        return instructor.horas || 0;
    } else if (mes === mesActual) {
        // Mes actual: calcular proporción basada en días transcurridos
        const diaActual = new Date().getDate();
        const diasEnMes = new Date(año, mes, 0).getDate();
        const factorMes = diasLaborales / diasEnMes;
        
        return Math.round((instructor.horas || 0) * factorMes);
    } else {
        // Meses futuros: 0 horas (no asignadas aún)
        return 0;
    }
}


    /**
     * Cambia la semana seleccionada y recarga los horarios
     * @param {string} semana - Valor del selector de semana (formato: YYYY-WW)
     */
    function cambiarSemana(semana) {
        
        // Agregar el selector de semana al formulario
        const form = document.getElementById('formFiltros');
        const inputSemana = document.createElement('input');
        inputSemana.type = 'hidden';
        inputSemana.name = 'semana_selector';
        inputSemana.value = semana;
        form.appendChild(inputSemana);
        
        // Enviar el formulario para recargar con la nueva semana
        form.submit();
    }

    /**
     * Regresa a la semana actual
     */
    function semanaActual() {
        // Obtener fecha actual en zona horaria local (Colombia)
        const fechaActual = new Date();
        const semanaISO = getWeekNumber(fechaActual.toISOString().slice(0, 10));
        
        // Agregar parámetro para ver solo horarios de hoy
        const form = document.getElementById('formFiltros');
        
        // Limpiar parámetros anteriores
        const inputsExistentes = form.querySelectorAll('input[name="ver_hoy"], input[name="semana_selector"]');
        inputsExistentes.forEach(input => input.remove());
        
        // Agregar parámetro para ver hoy
        const inputVerHoy = document.createElement('input');
        inputVerHoy.type = 'hidden';
        inputVerHoy.name = 'ver_hoy';
        inputVerHoy.value = '1';
        form.appendChild(inputVerHoy);
        
        // Agregar selector de semana actual
        const inputSemana = document.createElement('input');
        inputSemana.type = 'hidden';
        inputSemana.name = 'semana_selector';
        inputSemana.value = semanaISO;
        form.appendChild(inputSemana);
        
        // Enviar el formulario
        form.submit();
    }

    /**
     * Obtiene el número de semana ISO para una fecha (sincronizado con PHP)
     * @param {string} fecha - Fecha en formato YYYY-MM-DD
     * @returns {string} Semana en formato YYYY-WW (sin la W, igual que PHP)
     */
    function getWeekNumber(fecha) {
        const date = new Date(fecha);
        date.setHours(0, 0, 0, 0);
        
        // Jueves en la semana actual
        date.setDate(date.getDate() + 3 - (date.getDay() + 6) % 7);
        
        // 4 de enero en la semana actual
        const week1 = new Date(date.getFullYear(), 0, 4);
        
        // Ajustar para que el 4 de enero esté en la semana 1
        if (date < week1) {
            return (date.getFullYear() - 1) + '-' + 
                   String(Math.ceil((((date - new Date(date.getFullYear() - 1, 0, 4)) / 86400000) + 1) / 7)).padStart(2, '0');
        } else {
            return date.getFullYear() + '-' + 
                   String(Math.ceil((((date - week1) / 86400000) + 1) / 7)).padStart(2, '0');
        }
    }

    /**
     * Formatea una semana para mostrar en formato legible
     * @param {string} semana - Semana en formato YYYY-WW
     * @returns {string} Semana formateada (ej: "Semana 34 (19-25 agosto)")
     */
    function formatearSemana(semana) {
        if (!semana) return 'Semana actual';
        
        try {
            const [año, semanaNum] = semana.split('-W');
            const fechaInicio = getDateOfISOWeek(parseInt(semanaNum), parseInt(año));
            const fechaFin = new Date(fechaInicio);
            fechaFin.setDate(fechaFin.getDate() + 6);
            
            const inicioFormateado = formatearFechaLocal(fechaInicio.toISOString().slice(0, 10));
            const finFormateado = formatearFechaLocal(fechaFin.toISOString().slice(0, 10));
            
            return `Semana ${semanaNum} (${inicioFormateado} - ${finFormateado})`;
        } catch (error) {
            return semana;
        }
    }

    /**
     * Obtiene la fecha del lunes de una semana ISO
     * @param {number} week - Número de semana
     * @param {number} year - Año
     * @returns {Date} Fecha del lunes
     */
    function getDateOfISOWeek(week, year) {
        const simple = new Date(year, 0, 1 + (week - 1) * 7);
        const dayOfWeek = simple.getDay();
        const ISOweekStart = simple;
        
        if (dayOfWeek <= 4) {
            ISOweekStart.setDate(simple.getDate() - simple.getDay() + 1);
        } else {
            ISOweekStart.setDate(simple.getDate() + 8 - simple.getDay());
        }
        
        return ISOweekStart;
    }

    /**
     * Función de prueba para verificar la conexión con el backend
     */
    async function probarConexionBackend() {
        try {
            const formData = new FormData();
            formData.append('ope', 'get_horas_mensuales');
            formData.append('id_instructor', '1'); // ID de prueba
            formData.append('año', new Date().getFullYear());
            
            const response = await fetch('controllers/horchor.php', {
                method: 'POST',
                body: formData
            });
            
            
            
            if (response.ok) {
                const data = await response.json();
                
                return true;
            } else {
                
                return false;
            }
            
        } catch (error) {
            console.error('❌ Error en prueba de conexión:', error);
            return false;
        }
    }

// Función recalcularHorasInstructor eliminada - se creará desde cero

    // Ejecutar prueba de conexión al cargar la página (desactivado)
    // document.addEventListener('DOMContentLoaded', function() {
    //     setTimeout(() => {
    //         probarConexionBackend();
    //     }, 1000);
    //     
    //     // Agregar listener para cerrar modal con Escape
    //     document.addEventListener('keydown', function(e) {
    //         if (e.key === 'Escape') {
    //             cerrarModalEliminacion();
    //         }
    //     });
    // });

    // Solo mantener el listener para Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            cerrarModalEliminacion();
        }
    });

    /**
     * Obtiene el nombre del día de la semana en español
     */
    function obtenerDiaSemana(fecha) {
        const dias = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
        return dias[fecha.getDay()];
    }

    // Endpoint AJAX del controlador (ruta absoluta para evitar envoltura del layout)
    const CHOR_ENDPOINT = 'controllers/horchor.php';

    // Variables globales para la eliminación
    let horarioSeleccionado = null;

    /**
     * Muestra el modal personalizado con opciones de eliminación
     */
    function mostrarOpcionesEliminacion(idhor, iddia, idfic, idusu, idaul, nomfic, nomusu, nomaul, fecha_inicio, fecha_fin, jornada) {
        // Guardar información del horario seleccionado
        horarioSeleccionado = {
            idhor: idhor,
            iddia: iddia,
            idfic: idfic,
            idusu: idusu,
            idaul: idaul,
            fecha_inicio: fecha_inicio,
            fecha_fin: fecha_fin
        };
        
        // Mostrar información del horario
        const infoHorario = document.getElementById('infoHorarioEliminar');
        infoHorario.innerHTML = `
            <strong>Ficha:</strong> ${horarioSeleccionado.idfic} - ${nomfic}<br>
            <strong>Jornada:</strong> ${jornada}<br>
            <strong>Instructor:</strong> ${nomusu}<br>
            <strong>Aula:</strong> ${nomaul}
        `;

        // Mostrar información de fechas si están disponibles
        const infoFechas = document.getElementById('infoFechas');
        const detalleFechas = document.getElementById('detalleFechas');
        const btnEliminarGrupo = document.querySelector('.btn-eliminar-grupo');
        
        if (fecha_inicio && fecha_fin) {
            infoFechas.style.display = 'block';
            detalleFechas.innerHTML = `
                <strong>Fecha de inicio:</strong> ${fecha_inicio}<br>
                <strong>Fecha de fin:</strong> ${fecha_fin}
            `;
            btnEliminarGrupo.disabled = false;
            btnEliminarGrupo.style.opacity = '1';
            btnEliminarGrupo.style.cursor = 'pointer';
        } else {
            infoFechas.style.display = 'none';
            btnEliminarGrupo.disabled = true;
            btnEliminarGrupo.style.opacity = '0.5';
            btnEliminarGrupo.style.cursor = 'not-allowed';
            btnEliminarGrupo.title = 'No disponible: este horario no tiene fechas de inicio y fin definidas';
        }

        // Mostrar el modal y overlay
        const modal = document.getElementById('modalEliminacion');
        const overlay = document.getElementById('overlayEliminacion');
        
        modal.classList.add('visible');
        overlay.style.display = 'block';
        
        // Agregar clase para animación de entrada
        modal.classList.remove('fade-out');
    }

    /**
     * Cierra el modal de eliminación
     */
    function cerrarModalEliminacion() {
        const modal = document.getElementById('modalEliminacion');
        const overlay = document.getElementById('overlayEliminacion');
        
        // Agregar clase para animación de salida
        modal.classList.add('fade-out');
        
        // Ocultar después de la animación
        setTimeout(() => {
            modal.classList.remove('visible');
            overlay.style.display = 'none';
            modal.classList.remove('fade-out');
        }, 300);
    }

    /**
     * Elimina solo el horario individual
     */
    async function eliminarHorarioIndividual() {
        if (!horarioSeleccionado) return;

        if (confirm('¿Está seguro de que desea eliminar solo este horario específico?')) {
            try {
                // Construir parámetros para la eliminación
                const params = new URLSearchParams();
                params.set('ope', 'del');
                params.set('idhor', horarioSeleccionado.idhor);
                params.set('iddia', horarioSeleccionado.iddia);
                
                // Construir URL absoluta al controlador con los parámetros
                const controllerUrl = new URL('controllers/horchor.php', window.location.href).toString();
                const url = `${controllerUrl}?${params.toString()}`;
                const responseWithParams = await fetch(url, {
                    method: 'GET',
                    headers: { 
                        'Accept': 'application/json',
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    credentials: 'same-origin',
                    cache: 'no-store'
                });

                const data = await responseWithParams.json();
                
                if (data.success) {
                    alert(`✅ ${data.message}\n\nSe eliminó ${data.cantidad} horario(s) exitosamente.`);
                    // Refrescar el detalle en sitio (o recargar si no hay detalle abierto)
                    sagRecargarTrasCambio();
                } else {
                    alert(`❌ Error: ${data.message || 'No se pudo eliminar el horario'}`);
                }
            } catch (error) {
                console.error('Error eliminando horario individual:', error);
                alert('❌ Error al eliminar el horario. Por favor, intente nuevamente.');
            }
        }
    }

    /**
     * Cuenta cuántos horarios coinciden con los criterios del grupo
     * @returns {Promise<number>} Cantidad de horarios que se eliminarán
     */
    async function contarHorariosGrupo() {
        if (!horarioSeleccionado) { return 0; }


        try {
            // Crear FormData para enviar la consulta
            const formData = new FormData();
            formData.append('ope', 'contar_horarios_grupo');
            formData.append('idfic', horarioSeleccionado.idfic);
            formData.append('iddia', horarioSeleccionado.iddia);
            formData.append('idusu', horarioSeleccionado.idusu);
            formData.append('idaul', horarioSeleccionado.idaul);
            formData.append('fecha_inicio', horarioSeleccionado.fecha_inicio);
            formData.append('fecha_fin', horarioSeleccionado.fecha_fin);

            // Construir URL absoluta al controlador y enviar cookies de sesión
            const controllerUrl = new URL('controllers/horchor.php', window.location.href).toString();
            const response = await fetch(controllerUrl, {
                method: 'POST',
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin',
                cache: 'no-store',
                body: formData
            });

            const textoPlano = await response.clone().text().catch(() => null);

            // Intentar parsear JSON; si falla, mostrar texto para diagnóstico
            let data;
            try {
                data = await response.json();
            } catch (e) {
                console.error('❌ No es JSON. Texto recibido:', (textoPlano || '').slice(0, 300));
                throw e;
            }
            
            
            if (data.success) {
                
                return data.cantidad || 0;
            } else {
                console.error('❌ Error del servidor:', data.message);
                return 0;
            }

        } catch (error) {
            console.error('❌ Error contando horarios del grupo:', error);
            return 0;
        }
    }

    /**
     * Elimina todo el grupo de horarios relacionados
     */
    async function eliminarGrupoHorarios() {
        if (!horarioSeleccionado) return;

        // Verificar que las fechas estén disponibles
        if (!horarioSeleccionado.fecha_inicio || !horarioSeleccionado.fecha_fin) {
            alert('No se puede eliminar en grupo: este horario no tiene fechas de inicio y fin definidas.');
            return;
        }

        // Contar cuántos horarios se van a eliminar
        const cantidadHorarios = await contarHorariosGrupo();
        
        if (confirm('¿Está seguro de que desea eliminar TODO el grupo de horarios relacionados?\n\nEste grupo eliminará ' + cantidadHorarios + ' horarios.')) {
            try {
                // Construir parámetros para la eliminación del grupo
                const params = new URLSearchParams();
                params.set('ope', 'del_grupo');
                params.set('idfic', horarioSeleccionado.idfic);
                params.set('iddia', horarioSeleccionado.iddia);
                if (horarioSeleccionado.idusu !== '') params.set('idusu', horarioSeleccionado.idusu);
                if (horarioSeleccionado.idaul !== '') params.set('idaul', horarioSeleccionado.idaul);
                params.set('fecha_inicio', horarioSeleccionado.fecha_inicio);
                params.set('fecha_fin', horarioSeleccionado.fecha_fin);
                
                // Construir URL absoluta al controlador
                const controllerUrl = new URL('controllers/horchor.php', window.location.href).toString();
                const url = `${controllerUrl}?${params.toString()}`;
                
                const response = await fetch(url, {
                    method: 'GET',
                    headers: { 
                        'Accept': 'application/json',
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    credentials: 'same-origin',
                    cache: 'no-store'
                });

                const data = await response.json();
                
                if (data.success) {
                    alert(`✅ ${data.message}\n\nSe eliminaron ${data.cantidad} horario(s) exitosamente.`);
                    // Refrescar el detalle en sitio (o recargar si no hay detalle abierto)
                    sagRecargarTrasCambio();
                } else {
                    alert(`❌ Error: ${data.message || 'No se pudo eliminar el grupo de horarios'}`);
                }
            } catch (error) {
                console.error('Error eliminando grupo de horarios:', error);
                alert('❌ Error al eliminar el grupo de horarios. Por favor, intente nuevamente.');
            }
        }
    }

    function onClickEliminar(btn) {
        try {
            const idhor = parseInt(btn.getAttribute('data-idhor'));
            const iddia = parseInt(btn.getAttribute('data-iddia'));
            const idfic = btn.getAttribute('data-idfic');
            
            // Manejar valores vacíos correctamente para idusu e idaul
            let idusu = btn.getAttribute('data-idusu');
            let idaul = btn.getAttribute('data-idaul');
            
            // Convertir a número solo si no está vacío
            if (idusu && idusu !== '') {
                idusu = parseInt(idusu);
            } else {
                idusu = ''; // Mantener como string vacío para el backend
            }
            
            if (idaul && idaul !== '') {
                idaul = parseInt(idaul);
            } else {
                idaul = ''; // Mantener como string vacío para el backend
            }
            
            const nomfic = JSON.parse(btn.getAttribute('data-nomfic') || '""');
            const nomusu = JSON.parse(btn.getAttribute('data-nomusu') || '"Sin instructor"');
            const nomaul = JSON.parse(btn.getAttribute('data-nomaul') || '"Sin aula"');
            const fecha_inicio = btn.getAttribute('data-fecha-inicio') || '';
            const fecha_fin = btn.getAttribute('data-fecha-fin') || '';
            const jornada = JSON.parse(btn.getAttribute('data-jornada') || '""');
            
            
            
            // Llamar a la función existente con parámetros seguros
            mostrarOpcionesEliminacion(idhor, iddia, idfic, idusu, idaul, nomfic, nomusu, nomaul, fecha_inicio, fecha_fin, jornada);
        } catch (e) {
            console.error('Error preparando datos para eliminar:', e);
        }
    }

    /**
     * Función unificada para manejar el clic en editar cualquier horario
     */
    function onClickEditarHorario(btn) {
        try {
            const idhor = parseInt(btn.getAttribute('data-idhor'));
            const esTransversal = btn.getAttribute('data-es-transversal') === '1' || btn.getAttribute('data-es-transversal') === 'true';
            const esOtros = btn.getAttribute('data-es-otros') === '1' || btn.getAttribute('data-es-otros') === 'true';
            const nombreTransversal = JSON.parse(btn.getAttribute('data-nombre-transversal') || '""');
            const actividad = JSON.parse(btn.getAttribute('data-actividad') || '""');
            const horasOtros = btn.getAttribute('data-horas-otros') || '';
            const idaul = btn.getAttribute('data-idaul') || '';
            const idusu = btn.getAttribute('data-idusu') || '';
            const dia = JSON.parse(btn.getAttribute('data-dia') || '""');
            const ficha = JSON.parse(btn.getAttribute('data-ficha') || '""');
            const fecha_inicio = btn.getAttribute('data-fecha-inicio') || '';
            const fecha_fin = btn.getAttribute('data-fecha-fin') || '';
            const hinihor = btn.getAttribute('data-hinihor') || '';
            const hfinhor = btn.getAttribute('data-hfinhor') || '';
            const esFormacionDirecta = btn.getAttribute('data-es-formacion-directa') || '1';
            
            if (esTransversal) {
                mostrarOpcionesEdicionTransversal(idhor, nombreTransversal, idaul, idusu, dia, ficha, fecha_inicio, fecha_fin, hinihor, hfinhor, esFormacionDirecta);
            } else {
                abrirModalEdicionHorarioDirecto(idhor, esOtros, actividad, horasOtros, idaul, idusu, dia, ficha, fecha_inicio, fecha_fin, hinihor, hfinhor, esFormacionDirecta);
            }
        } catch (e) {
            console.error('Error preparando datos para editar horario:', e);
        }
    }

    /**
     * Abre directamente el modal de edición para horarios normales o actividades especiales
     */
    function abrirModalEdicionHorarioDirecto(idhor, esOtros, actividad, horasOtros, idaul, idusu, dia, ficha, fecha_inicio, fecha_fin, hinihor, hfinhor, esFormacionDirecta) {
        const modal = document.getElementById('modalEditarTransversal');
        const overlay = document.getElementById('overlayEliminacion');
        const idhorElement = document.getElementById('idhor_edit');
        
        if (idhorElement) idhorElement.value = idhor;
        document.getElementById('es_transversal_edit').value = '0';
        document.getElementById('es_otros_edit').value = esOtros ? '1' : '0';
        
        document.getElementById('ficha_edit').value = ficha;
        document.getElementById('dia_edit').value = dia;
        document.getElementById('instructor_edit').value = idusu || '';
        document.getElementById('aula_edit').value = idaul || '';
        
        // Cargar hora inicio/fin, fechas y formación directa reales del registro
        cargarCamposHorarioEdicion(hinihor, hfinhor, fecha_inicio, fecha_fin, esFormacionDirecta);
        
        // Ocultar transversal
        const grupoTransversal = document.getElementById('grupo_nombre_transversal');
        if (grupoTransversal) grupoTransversal.style.display = 'none';
        const inputTransversal = document.getElementById('nombre_transversal_edit');
        if (inputTransversal) inputTransversal.required = false;
        
        // Mostrar u ocultar otros
        const grupoActividad = document.getElementById('grupo_actividad_otros');
        const grupoHoras = document.getElementById('grupo_horas_otros');
        const tituloModal = document.getElementById('tituloModalEdicion');
        
        if (esOtros) {
            if (grupoActividad) grupoActividad.style.display = 'block';
            if (grupoHoras) grupoHoras.style.display = 'block';
            document.getElementById('actividad_edit').value = actividad || '';
            document.getElementById('horas_otros_edit').value = horasOtros || '';
            if (tituloModal) tituloModal.innerHTML = '<i class="fa-solid fa-edit me-2"></i> Editar Actividad Especial';
        } else {
            if (grupoActividad) grupoActividad.style.display = 'none';
            if (grupoHoras) grupoHoras.style.display = 'none';
            if (tituloModal) tituloModal.innerHTML = '<i class="fa-solid fa-edit me-2"></i> Editar Horario';
        }
        
        if (modal) modal.classList.add('visible');
        if (overlay) overlay.style.display = 'block';
    }

    /**
     * Función para manejar el clic en editar transversal (retrocompatibilidad)
     */
    function onClickEditarTransversal(btn) {
        onClickEditarHorario(btn);
    }

    /**
     * Muestra el modal de opciones de edición para transversales
     */
    function mostrarOpcionesEdicionTransversal(idhor, nombreTransversal, idaul, idusu, dia, ficha, fecha_inicio, fecha_fin, hinihor, hfinhor, esFormacionDirecta) {
        // Guardar información del transversal seleccionado
        transversalSeleccionado = {
            idhor: idhor,
            nombreTransversal: nombreTransversal,
            idaul: idaul,
            idusu: idusu,
            dia: dia,
            ficha: ficha,
            fecha_inicio: fecha_inicio,
            fecha_fin: fecha_fin,
            hinihor: hinihor || '',
            hfinhor: hfinhor || '',
            esFormacionDirecta: esFormacionDirecta || 1
        };
        
        // Mostrar información del transversal
        const infoTransversal = document.getElementById('infoTransversalEditar');
        if (infoTransversal) {
            infoTransversal.innerHTML = `
                <strong>Transversal:</strong> ${nombreTransversal}<br>
                <strong>Ficha:</strong> ${ficha}<br>
                <strong>Día:</strong> ${dia}
            `;
        }
        
        // Mostrar información de fechas si están disponibles
        const infoFechas = document.getElementById('infoFechasEdicion');
        const detalleFechas = document.getElementById('detalleFechasEdicion');
        const btnEditarGrupo = document.querySelector('#modalEdicionTransversal .btn-editar-grupo');
        
        if (fecha_inicio && fecha_fin) {
            if (infoFechas) infoFechas.style.display = 'block';
            if (detalleFechas) {
                detalleFechas.innerHTML = `
                    <strong>Fecha de inicio:</strong> ${fecha_inicio}<br>
                    <strong>Fecha de fin:</strong> ${fecha_fin}
                `;
            }
            if (btnEditarGrupo) {
                btnEditarGrupo.disabled = false;
                btnEditarGrupo.style.opacity = '1';
                btnEditarGrupo.style.cursor = 'pointer';
            }
        } else {
            if (infoFechas) infoFechas.style.display = 'none';
            if (btnEditarGrupo) {
                btnEditarGrupo.disabled = true;
                btnEditarGrupo.style.opacity = '0.5';
                btnEditarGrupo.style.cursor = 'not-allowed';
            }
        }
        
        // Mostrar el modal y overlay
        const modal = document.getElementById('modalEdicionTransversal');
        const overlay = document.getElementById('overlayEliminacion');
        
        if (modal) modal.classList.add('visible');
        if (overlay) overlay.style.display = 'block';
    }

    /**
     * Cierra el modal de edición de transversales
     */
    function cerrarModalEdicionTransversal() {
        const modalOpciones = document.getElementById('modalEdicionTransversal');
        const overlay = document.getElementById('overlayEliminacion');
        
        if (modalOpciones) {
            modalOpciones.classList.remove('visible');
        }
        
        if (overlay) {
            overlay.style.display = 'none';
        }
    }

    /**
     * Edita solo el horario transversal individual
     */
    function editarTransversalIndividual() {
        if (!transversalSeleccionado) return;
        
        document.getElementById('idhor_edit').value = transversalSeleccionado.idhor;
        document.getElementById('es_transversal_edit').value = '1';
        document.getElementById('es_otros_edit').value = '0';
        document.getElementById('nombre_transversal_edit').value = transversalSeleccionado.nombreTransversal;
        document.getElementById('ficha_edit').value = transversalSeleccionado.ficha;
        document.getElementById('dia_edit').value = transversalSeleccionado.dia;
        
        const grupoTransversal = document.getElementById('grupo_nombre_transversal');
        if (grupoTransversal) grupoTransversal.style.display = 'block';
        const inputTransversal = document.getElementById('nombre_transversal_edit');
        if (inputTransversal) inputTransversal.required = true;
        
        const grupoActividad = document.getElementById('grupo_actividad_otros');
        const grupoHoras = document.getElementById('grupo_horas_otros');
        if (grupoActividad) grupoActividad.style.display = 'none';
        if (grupoHoras) grupoHoras.style.display = 'none';
        
        const tituloModal = document.getElementById('tituloModalEdicion');
        if (tituloModal) tituloModal.innerHTML = '<i class="fa-solid fa-edit me-2"></i> Editar Horario Transversal';
        
        document.getElementById('instructor_edit').value = transversalSeleccionado.idusu || '';
        document.getElementById('aula_edit').value = transversalSeleccionado.idaul || '';
        
        // Cargar hora inicio/fin, fechas y formación directa del registro individual
        cargarCamposHorarioEdicion(transversalSeleccionado.hinihor, transversalSeleccionado.hfinhor, transversalSeleccionado.fecha_inicio, transversalSeleccionado.fecha_fin, transversalSeleccionado.esFormacionDirecta);
        
        cerrarModalEdicionTransversal();
        
        const modalEdicion = document.getElementById('modalEditarTransversal');
        const overlay = document.getElementById('overlayEliminacion');
        
        if (modalEdicion) modalEdicion.classList.add('visible');
        if (overlay) overlay.style.display = 'block';
    }

    /**
     * Edita todo el grupo de horarios transversales relacionados
     */
    function editarTransversalGrupo() {
        if (!transversalSeleccionado) return;
        
        if (!transversalSeleccionado.fecha_inicio || !transversalSeleccionado.fecha_fin) {
            alert('No se puede editar en grupo: este transversal no tiene fechas de inicio y fin definidas.');
            return;
        }
        
        document.getElementById('idhor_edit').value = transversalSeleccionado.idhor;
        document.getElementById('es_transversal_edit').value = '1';
        document.getElementById('es_otros_edit').value = '0';
        document.getElementById('nombre_transversal_edit').value = transversalSeleccionado.nombreTransversal;
        document.getElementById('ficha_edit').value = transversalSeleccionado.ficha;
        document.getElementById('dia_edit').value = transversalSeleccionado.dia;
        
        const grupoTransversal = document.getElementById('grupo_nombre_transversal');
        if (grupoTransversal) grupoTransversal.style.display = 'block';
        const inputTransversal = document.getElementById('nombre_transversal_edit');
        if (inputTransversal) inputTransversal.required = true;
        
        const grupoActividad = document.getElementById('grupo_actividad_otros');
        const grupoHoras = document.getElementById('grupo_horas_otros');
        if (grupoActividad) grupoActividad.style.display = 'none';
        if (grupoHoras) grupoHoras.style.display = 'none';
        
        const tituloModal = document.getElementById('tituloModalEdicion');
        if (tituloModal) tituloModal.innerHTML = '<i class="fa-solid fa-edit me-2"></i> Editar Grupo de Horarios Transversales';
        
        document.getElementById('instructor_edit').value = transversalSeleccionado.idusu || '';
        document.getElementById('aula_edit').value = transversalSeleccionado.idaul || '';
        
        // Cargar hora inicio/fin y fechas de referencia (la edición grupal usa los atributos data-*)
        cargarCamposHorarioEdicion(transversalSeleccionado.hinihor, transversalSeleccionado.hfinhor, transversalSeleccionado.fecha_inicio, transversalSeleccionado.fecha_fin, transversalSeleccionado.esFormacionDirecta);
        
        const idhorElement = document.getElementById('idhor_edit');
        idhorElement.setAttribute('data-edicion-grupal', 'true');
        idhorElement.setAttribute('data-fecha-inicio', transversalSeleccionado.fecha_inicio);
        idhorElement.setAttribute('data-fecha-fin', transversalSeleccionado.fecha_fin);
        idhorElement.setAttribute('data-idaul-original', transversalSeleccionado.idaul || '');
        idhorElement.setAttribute('data-idusu-original', transversalSeleccionado.idusu || '');
        
        cerrarModalEdicionTransversal();
        
        const modalEdicion = document.getElementById('modalEditarTransversal');
        const overlay = document.getElementById('overlayEliminacion');
        
        if (modalEdicion) modalEdicion.classList.add('visible');
        if (overlay) overlay.style.display = 'block';
    }

    // Variable global para almacenar el transversal seleccionado
    let transversalSeleccionado = null;

    // Agregar evento al overlay para cerrar modales al hacer clic fuera
    document.addEventListener('DOMContentLoaded', function() {
        const overlay = document.getElementById('overlayEliminacion');
        if (overlay) {
            overlay.addEventListener('click', cerrarTodoOverlay);
        }

        // Cerrar como la "X" si el usuario hace clic FUERA del cuadro (fallback adicional)
        document.addEventListener('mousedown', function(e) {
            const modalMenu = document.getElementById('modalEdicionTransversal');
            const modalEdicion = document.getElementById('modalEditarTransversal');
            const modalEliminar = document.getElementById('modalEliminacion');
            const contentSelector = '.modal-eliminacion-content';

            // Si el menú de opciones está visible y el clic fue fuera de su contenido
            if (modalMenu && modalMenu.classList.contains('visible')) {
                const content = modalMenu.querySelector(contentSelector);
                if (content && !content.contains(e.target)) {
                    cerrarModalEdicionTransversal();
                    return;
                }
            }

            // Si el modal de edición está visible y el clic fue fuera de su contenido
            if (modalEdicion && modalEdicion.classList.contains('visible')) {
                const content = modalEdicion.querySelector(contentSelector);
                if (content && !content.contains(e.target)) {
                    cerrarModalEditarTransversal();
                    return;
                }
            }

            // Si el modal de eliminación está visible y el clic fue fuera de su contenido
            if (modalEliminar && modalEliminar.classList.contains('visible')) {
                const content = modalEliminar.querySelector(contentSelector);
                if (content && !content.contains(e.target)) {
                    cerrarModalEliminacion();
                    return;
                }
            }
        });
    });

    function cerrarTodoOverlay() {
        const modalMenu = document.getElementById('modalEdicionTransversal');
        const modalEdicion = document.getElementById('modalEditarTransversal');
        const modalEliminar = document.getElementById('modalEliminacion');

        if (modalMenu && modalMenu.classList.contains('visible')) {
            cerrarModalEdicionTransversal(); // mismo comportamiento que la X
            return;
        }
        if (modalEdicion && modalEdicion.classList.contains('visible')) {
            cerrarModalEditarTransversal(); // mismo comportamiento que la X
            return;
        }
        if (modalEliminar && modalEliminar.classList.contains('visible')) {
            cerrarModalEliminacion(); // mismo comportamiento que la X
            return;
        }
    }

    /**
     * Limpia los atributos de edición grupal cuando se cierra el modal
     */
    function limpiarAtributosEdicionGrupal() {
        const idhorElement = document.getElementById('idhor_edit');
        if (idhorElement) {
            const hadGrupal = idhorElement.hasAttribute('data-edicion-grupal');
            const hadInicio = idhorElement.getAttribute('data-fecha-inicio');
            const hadFin = idhorElement.getAttribute('data-fecha-fin');
            const hadAulaOriginal = idhorElement.getAttribute('data-idaul-original');
            const hadInstructorOriginal = idhorElement.getAttribute('data-idusu-original');
            
            idhorElement.removeAttribute('data-edicion-grupal');
            idhorElement.removeAttribute('data-fecha-inicio');
            idhorElement.removeAttribute('data-fecha-fin');
            idhorElement.removeAttribute('data-idaul-original');
            idhorElement.removeAttribute('data-idusu-original');
            
        } else {
            console.error('❌ Elemento idhor_edit no encontrado en limpiarAtributosEdicionGrupal');
        }
    }

    /**
     * Cierra el modal de edición de transversales
     */
    function cerrarModalEditarTransversal() {
        const modal = document.getElementById('modalEditarTransversal');
        const overlay = document.getElementById('overlayEliminacion');
        
        if (modal) {
            modal.classList.remove('visible');
        }
        
        if (overlay) {
            overlay.style.display = 'none';
        }
        
        limpiarAtributosEdicionGrupal();
    }

    /**
     * Cancela la edición de transversales
     */
    function cancelarEdicionTransversal() {
        cerrarModalEdicionTransversal();
        limpiarAtributosEdicionGrupal();
    }

    /**
     * Función de prueba para verificar que el contador de horarios esté funcionando
     * Muestra información detallada de los cálculos para debugging
     */
    async function probarContadorHorarios() {
        if (!window.instructorActual) {
            alert('No hay instructor seleccionado');
            return;
        }
        
        const instructor = window.instructorActual;
        const añoActual = new Date().getFullYear();
        const mesActual = new Date().getMonth() + 1;
        
        
        // Crear ventana de prueba
        const ventanaPrueba = window.open('', '_blank', 'width=1000,height=700');
        ventanaPrueba.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>Prueba Contador Horarios - ${instructor.nombre}</title>
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
                <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
            </head>
            <body>
                <div class="container mt-3">
                    <h4><i class="fa-solid fa-bug me-2"></i>Prueba de Horas Anuales</h4>
                    <div class="alert alert-info">
                        <strong>Instructor:</strong> ${instructor.nombre}<br>
                        <strong>ID:</strong> ${instructor.id_instructor}<br>
                        <strong>Año:</strong> ${añoActual}
                    </div>
                    <div id="resultados-prueba">
                        <div class="text-center">
                            <div class="spinner-border" role="status">
                                <span class="visually-hidden">Calculando...</span>
                            </div>
                            <p class="mt-2">Calculando horas para cada mes...</p>
                        </div>
                    </div>
                </div>
            </body>
            </html>
        `);
        
        try {
            let resultados = [];
            let totalAnual = 0;
            let debugInfo = [];
            
            // Probar cada mes del año
            for (let mes = 1; mes <= 12; mes++) {
                try {
                    // Primero hacer debugging para ver qué datos se obtienen
                    const debugFormData = new FormData();
                    debugFormData.append('ope', 'debug_horarios_instructor');
                    debugFormData.append('id_instructor', instructor.id_instructor);
                    debugFormData.append('mes', mes);
                    debugFormData.append('año', añoActual);
                    
                    const debugResponse = await fetch('controllers/horchor.php', {
                        method: 'POST',
                        body: debugFormData
                    });
                    
                    let debugData = null;
                    if (debugResponse.ok) {
                        debugData = await debugResponse.json();
                        debugInfo.push({
                            mes: mes,
                            debug: debugData
                        });
                    }
                    
                    // Luego obtener las horas calculadas
                    const horasFormData = new FormData();
                    horasFormData.append('ope', 'get_horas_mes_instructor');
                    horasFormData.append('id_instructor', instructor.id_instructor);
                    horasFormData.append('mes', mes);
                    horasFormData.append('año', añoActual);
                    
                    const horasResponse = await fetch('controllers/horchor.php', {
                        method: 'POST',
                        body: horasFormData
                    });
                    
                    if (!horasResponse.ok) {
                        throw new Error(`HTTP ${horasResponse.status}: ${horasResponse.statusText}`);
                    }
                    
                    const horasData = await horasResponse.json();
                    
                    if (horasData.success) {
                        const horas = horasData.horas || 0;
                        totalAnual += horas;
                        
                        resultados.push({
                            mes: mes,
                            nombreMes: obtenerNombreMes(mes),
                            horas: horas,
                            status: 'success',
                            message: `✅ ${horas} horas calculadas`,
                            debug: debugData
                        });
                        
                    } else {
                        resultados.push({
                            mes: mes,
                            nombreMes: obtenerNombreMes(mes),
                            horas: 0,
                            status: 'error',
                            message: `❌ ${horasData.message || 'Error desconocido'}`,
                            debug: debugData
                        });
                        
                    }
                    
                } catch (error) {
                    resultados.push({
                        mes: mes,
                        nombreMes: obtenerNombreMes(mes),
                        horas: 0,
                        status: 'error',
                        message: `❌ ${error.message}`,
                        debug: null
                    });
                }
            }
            
            // Mostrar resultados en la ventana de prueba
            const resultadosHTML = resultados.map(resultado => {
                let debugInfo = '';
                if (resultado.debug && resultado.debug.success) {
                    debugInfo = `
                        <small class="text-muted">
                            <br>📊 Horarios en BD: ${resultado.debug.total}
                            <br>🏷️ Jornadas: ${resultado.debug.jornadas ? resultado.debug.jornadas.join(', ') : 'N/A'}
                        </small>
                    `;
                }
                
                return `
                    <tr class="table-${resultado.status === 'success' ? 'success' : 'danger'}">
                        <td><strong>${resultado.nombreMes}</strong></td>
                        <td>${resultado.horas}</td>
                        <td>${resultado.message}${debugInfo}</td>
                    </tr>
                `;
            }).join('');
            
            ventanaPrueba.document.getElementById('resultados-prueba').innerHTML = `
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <h5 class="card-title">${totalAnual}</h5>
                                <p class="card-text">Total Horas Anuales</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-info text-white">
                            <div class="card-body text-center">
                                <h5 class="card-title">${Math.round(totalAnual / 12)}</h5>
                                <p class="card-text">Promedio Mensual</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Mes</th>
                                <th>Horas</th>
                                <th>Estado + Debug</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${resultadosHTML}
                        </tbody>
                    </table>
                </div>
                
                <div class="alert alert-info mt-3">
                    <h6><i class="fa-solid fa-info-circle me-2"></i>Resumen de la Prueba</h6>
                    <p class="mb-1"><strong>Total de horas calculadas:</strong> ${totalAnual}</p>
                    <p class="mb-1"><strong>Meses con éxito:</strong> ${resultados.filter(r => r.status === 'success').length}/12</p>
                    <p class="mb-0"><strong>Meses con error:</strong> ${resultados.filter(r => r.status === 'error').length}/12</p>
                </div>
                
                <div class="alert alert-warning">
                    <h6><i class="fa-solid fa-exclamation-triangle me-2"></i>Información de Debugging</h6>
                    <p class="mb-1">Esta ventana muestra información detallada de la base de datos para cada mes.</p>
                    <p class="mb-0">Si algún mes muestra 0 horas, revisa la información de debugging para identificar el problema.</p>
                </div>
            `;
            
        } catch (error) {
            ventanaPrueba.document.getElementById('resultados-prueba').innerHTML = `
                <div class="alert alert-danger">
                    <i class="fa-solid fa-exclamation-triangle me-2"></i>
                    Error ejecutando la prueba: ${error.message}
                </div>
            `;
        }
    }

    /**
     * Función de prueba para verificar la base de datos
     */
    async function probarBaseDatos() {
        if (!window.instructorActual) {
            alert('No hay instructor seleccionado');
            return;
        }
        
        const instructor = window.instructorActual;
       
        try {  
            // Mostrar información básica del instructor
            let mensaje = `Información del Instructor:\n\n`;
            mensaje += `ID: ${instructor.id_instructor}\n`;
            mensaje += `Nombre: ${instructor.nombre}\n`;
            mensaje += `Documento: ${instructor.documento}\n`;
            mensaje += `Horas: ${instructor.horas}\n`;
            mensaje += `Tipo Contrato: ${instructor.tipo_contrato}\n`;
            mensaje += `Total Horarios: ${instructor.detalle ? instructor.detalle.length : 0}\n\n`;
            
            if (instructor.detalle && instructor.detalle.length > 0) {
                mensaje += `Primeros 3 horarios del detalle:\n`;
                instructor.detalle.slice(0, 3).forEach((horario, index) => {
                    mensaje += `${index + 1}. Ficha: ${horario.ficha}, Jornada: ${horario.jornada}\n`;
                });
            } else {
                mensaje += `No hay horarios en el detalle del instructor.\n`;
            }
            
            alert(mensaje);
            
            // Ahora probar la base de datos
            const formData = new FormData();
            formData.append('ope', 'test_horarios_instructor');
            formData.append('id_instructor', instructor.id_instructor);
            
            const response = await fetch('controllers/horchor.php', {
                method: 'POST',
                body: formData
            });
            
            
            if (response.ok) {
                const data = await response.json();
                
                // Mostrar información de la base de datos
                let mensajeBD = `Base de Datos:\n\n`;
                mensajeBD += `Total horarios: ${data.estadisticas.total}\n`;
                mensajeBD += `Con fecha específica: ${data.estadisticas.con_fecha}\n`;
                mensajeBD += `Sin fecha específica: ${data.estadisticas.sin_fecha}\n\n`;
                
                if (data.ejemplos.length > 0) {
                    mensajeBD += `Ejemplos de horarios:\n`;
                    data.ejemplos.forEach((ejemplo, index) => {
                        mensajeBD += `${index + 1}. ID: ${ejemplo.idhor}, Ficha: ${ejemplo.idfic}, Fecha: ${ejemplo.fecha_especifica}\n`;
                    });
                }
                
                alert(mensajeBD);
            } else {
                alert('Error al probar la base de datos. Intente nuevamente.');
            }
        } catch (error) {
            alert('Error al probar la base de datos: ' + error.message);
        }
    }

    /**
     * Carga el contador de horarios para un instructor específico
     * Usa la misma lógica que "Por Rango" pero muestra mes a mes
     */
    async function cargarContadorHorarios(instructor) {
        const contenidoContador = document.getElementById('contenidoContador');
        
        // Mostrar indicador de carga
        contenidoContador.innerHTML = `
            <div class="text-center">
                <div class="spinner-border" role="status">
                    <span class="visually-hidden">Cargando contador de horarios...</span>
                </div>
                <p class="mt-2">Calculando contador de horarios mes a mes...</p>
            </div>
        `;
        
        try {
            // Calcular el contador mes a mes usando la misma lógica que Por Rango
            const contadorHorarios = await calcularContadorHorariosMesAMes(instructor);
            
             // Generar y mostrar el HTML del contador
             contenidoContador.innerHTML = await generarHTMLContadorHorarios(contadorHorarios);
            
        } catch (error) {
            contenidoContador.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fa-solid fa-exclamation-triangle me-2"></i>
                    Error al cargar el contador de horarios. Intente nuevamente.
                    <br><small>Error: ${error.message}</small>
                </div>
            `;
        }
    }

    /**
     * Calcula el contador de horarios mes a mes usando la misma lógica que "Por Rango"
     * Agrupa por ficha y jornada, suma horas según la lógica establecida
     */
    async function calcularContadorHorariosMesAMes(instructor) {
        const año = new Date().getFullYear();
        const resumenMensual = [];
        let totalAnual = 0;
        
        // Calcular horas para cada mes usando la misma lógica que Por Rango
        for (let mes = 1; mes <= 12; mes++) {
            // Reutilizar la lógica de "Por rango" para cada mes
            const resultadoMes = await calcularHorasMesConLogicaRango(instructor, mes, año);
            const horasMes = resultadoMes.horas || 0;
            const detalleMes = Array.isArray(resultadoMes.detalle) ? resultadoMes.detalle : [];
            const desgloseMes = resultadoMes.desglose || {};
            
            // Obtener información del mes
            const fechaMes = new Date(año, mes - 1, 1);
            const nombreMes = fechaMes.toLocaleDateString('es-ES', { month: 'long' });
            const mesActual = new Date().getMonth() + 1;
            const añoActual = new Date().getFullYear();
            
            // Preparar resumen estilo: chip solo para horas + días como texto pequeño
            // Usar horas por día (no suma), igual a Por Rango
            const detalleResumenHtml = detalleMes.map(item => {
                const diasArr = (item.dia || '')
                    .split(',')
                    .map(d => parseInt(String(d).trim(), 10))
                    .filter(n => !isNaN(n));
                const diasUnicos = [...new Set(diasArr)].sort((a, b) => a - b);
                const diasTexto = diasUnicos.join(',');
                let horasPorDia = 0;
                if (item.es_otros) {
                    const total = Number(item.horas) || 0;
                    const count = Number(item.dias_count) || Math.max(1, diasUnicos.length);
                    horasPorDia = count > 0 ? total / count : 0;
                } else {
                    // Horas reales del registro (calculadas desde hinihor/hfinhor en el backend)
                    horasPorDia = Number(item.horas) || 0;
                    if (!horasPorDia) {
                        const mapeo = { 'Mañana': 6, 'Tarde': 5, 'Noche': 4 };
                        horasPorDia = mapeo[item.jornada] || 6;
                    }
                }
                const horasHtml = `<span class=\"chip-horas\">${horasPorDia}hrs</span>`;
                const diasHtml = diasTexto? `<span class=\"dias-implicados\">(${diasTexto})</span>` : '';
                return `${horasHtml}${diasHtml}`;
            }).join(' ');

            const resumenMes = {
                mes: mes,
                nombreMes: nombreMes.charAt(0).toUpperCase() + nombreMes.slice(1),
                horas: horasMes,
                desglose: desgloseMes,
                detalleResumenHtml: detalleResumenHtml,
                completado: mes < mesActual || (mes === mesActual && año < añoActual),
                futuro: mes > mesActual || (mes === mesActual && año > añoActual)
            };
            
            resumenMensual.push(resumenMes);
            
            // Sumar TODOS los meses
            totalAnual += horasMes;
        }
        
        const contador = {
            instructor: instructor,
            año: año,
            totalAnual: totalAnual,
            resumenMensual: resumenMensual,
            totalHorarios: instructor.detalle ? instructor.detalle.length : 0
        };
        
        return contador;
    }

    /**
     * Calcula las horas de un mes reutilizando la lógica de "Por Rango"
     * Usa la misma función que se usa en "Ver detalle por rango"
     */
    async function calcularHorasMesConLogicaRango(instructor, mes, año) {
        try {
            // Calcular fechas de inicio y fin del mes
            const fechaInicio = `${año}-${mes.toString().padStart(2, '0')}-01`;
            const fechaFin = new Date(año, mes, 0).toISOString().split('T')[0]; // Último día del mes
            
            // Reutilizar la lógica de "Por rango" llamando al backend
            const formData = new FormData();
            formData.append('ope', 'get_horas_rango');
            formData.append('id_instructor', instructor.id_instructor);
            formData.append('fecha_inicio', fechaInicio);
            formData.append('fecha_fin', fechaFin);
            
            const response = await fetch('controllers/horchor.php', {
                method: 'POST',
                body: formData
            });
            
            if (!response.ok) {
                throw new Error('Error en la respuesta del servidor');
            }
            
            const data = await response.json();
            
            if (!data.success) {
                throw new Error(data.message || 'Error obteniendo horas del rango');
            }
            
            // Extraer total y detalle (mismo formato ya usado en Por Rango)
            const totalHoras = data.resumen?.total_horas || 0;
            const detalle = Array.isArray(data.detalle) ? data.detalle : [];
            
            return {
                horas: totalHoras,
                detalle: detalle,
                desglose: {}
            };
            
        } catch (error) {
            return {
                horas: 0,
                desglose: {}
            };
        }
    }

    /**
     * Calcula las horas de un mes usando la misma lógica que "Por Rango"
     * Agrupa por ficha y jornada, suma horas según la lógica establecida
     */
    async function calcularHorasMesPorRango(instructor, mes, año) {
        try {
            // Obtener TODOS los horarios del instructor desde la base de datos
            
            const formData = new FormData();
            formData.append('ope', 'get_horarios_instructor_completo');
            formData.append('id_instructor', instructor.id_instructor);
            
            const response = await fetch('controllers/horchor.php', {
                method: 'POST',
                body: formData
            });
            
            if (!response.ok) {
                throw new Error('Error en la respuesta del servidor');
            }
            
            const data = await response.json();
            
            if (!data.success) {
                throw new Error(data.message || 'Error obteniendo horarios');
            }
            
            const todosLosHorarios = data.horarios || [];
            
            if (todosLosHorarios.length === 0) {
                  return 0;
            }
            
            // Filtrar horarios por mes usando fecha_especifica
            const horariosDelMes = todosLosHorarios.filter(horario => {
                if (!horario.fecha_especifica) {
                    return false;
                }
                
                try {
                    const fechaHorario = new Date(horario.fecha_especifica);
                    const mesHorario = fechaHorario.getMonth() + 1;
                    const añoHorario = fechaHorario.getFullYear();
                    
                    const coincide = mesHorario === mes && añoHorario === año;
                    
                    return coincide;
                } catch (e) {
                    console.warn('❌ Error parseando fecha:', horario.fecha_especifica, e);
                    return false;
                }
            });
            
            if (horariosDelMes.length === 0) {
                return 0;
            }
            
            // Misma lógica que Por Rango: agrupar por ficha y jornada
            const horariosPorFichaJornada = {};
            const desgloseJornadas = {}; // Para el desglose detallado
            
            horariosDelMes.forEach(horario => {
                // Usar nombre_jornada en lugar de jornada (ID)
                const jornada = horario.nombre_jornada || 'Mañana';
                const idfic = horario.ficha || horario.idfic;
                const clave = `${idfic}_${jornada}`;
                
                if (!horariosPorFichaJornada[clave]) {
                    horariosPorFichaJornada[clave] = {
                        ficha: idfic,
                        jornada: jornada,
                        count: 0,
                        es_otros: horario.es_otros || false,
                        horas_otros: horario.horas_otros || 0,
                        es_transversal: horario.es_transversal || false,
                        es_formacion_directa: horario.es_formacion_directa || 1
                    };
                }
                
                horariosPorFichaJornada[clave].count++;
                
                // Acumular el desglose por jornada
                if (!desgloseJornadas[jornada]) {
                    desgloseJornadas[jornada] = 0;
                }
                desgloseJornadas[jornada]++;
            });

            // Calcular horas totales usando la misma lógica que "Ver detalle por rango"
            let totalHoras = 0;
            let totalHorasFormacion = 0;
            let totalHorasOtros = 0;
            
            Object.values(horariosPorFichaJornada).forEach(grupo => {
                if (grupo.es_otros) {
                    // Actividad "otros": verificar es_formacion_directa
                    const horasOtros = grupo.horas_otros || 0;
                    const horasTotal = horasOtros * grupo.count;
                    
                    if (grupo.es_formacion_directa == 2) {
                        // Actividad "otros" con formación directa: contar como formación
                        totalHorasFormacion += horasTotal;
                    } else {
                        // Actividad "otros" tradicional: contar como otros
                        totalHorasOtros += horasTotal;
                   }
                } else {
                    // Horario normal o transversal: SIEMPRE suma a formación
                    const horasJornada = obtenerHorasJornadaPorRango(grupo.jornada);
                    const horasTotal = horasJornada * grupo.count;
                    totalHorasFormacion += horasTotal;
                }
            });
            
            // Total general: formación + otros
            totalHoras = totalHorasFormacion + totalHorasOtros;
            
            // Retornar objeto con horas y desglose
            return {
                horas: totalHoras,
                desglose: desgloseJornadas
            };
            
        } catch (error) {            
            // Fallback: usar el detalle local si falla la BD
            const detalle = instructor.detalle || [];
            
            if (detalle.length === 0) {
                return 0;
            }
            
            // Filtrar horarios por mes usando fecha_especifica
            const horariosDelMes = detalle.filter(horario => {
                if (!horario.fecha_especifica) {
                    return false;
                }
                
                try {
                    const fechaHorario = new Date(horario.fecha_especifica);
                    const mesHorario = fechaHorario.getMonth() + 1;
                    const añoHorario = fechaHorario.getFullYear();
                    
                    const coincide = mesHorario === mes && añoHorario === año;
                    
                    return coincide;
                } catch (e) {
                    console.warn('❌ Error parseando fecha:', horario.fecha_especifica, e);
                    return false;
                }
            });
            
            
            if (horariosDelMes.length === 0) {
                return 0;
            }
            
            // Misma lógica que Por Rango: agrupar por ficha y jornada
            const horariosPorFichaJornada = {};
            
            horariosDelMes.forEach(horario => {
                const jornada = horario.jornada || 'Mañana';
                const idfic = horario.ficha || horario.idfic;
                const clave = `${idfic}_${jornada}`;
                
                if (!horariosPorFichaJornada[clave]) {
                    horariosPorFichaJornada[clave] = {
                        ficha: idfic,
                        jornada: jornada,
                        count: 0,
                        es_otros: horario.es_otros || false,
                        horas_otros: horario.horas_otros || 0,
                        es_transversal: horario.es_transversal || false
                    };
                }
                
                horariosPorFichaJornada[clave].count++;
            });
            
            
            // Calcular horas totales usando la misma lógica que Por Rango
            let totalHoras = 0;
            
            Object.values(horariosPorFichaJornada).forEach(grupo => {
                if (grupo.es_otros) {
                    // Actividad "otros": multiplicar horas_otros por el número de días
                    const horasOtros = grupo.horas_otros || 0;
                    const horasTotal = horasOtros * grupo.count;
                    totalHoras += horasTotal;
                } else {
                    // Horario normal o transversal: multiplicar horas de jornada por el número de días
                    const horasJornada = obtenerHorasJornadaPorRango(grupo.jornada);
                    const horasTotal = horasJornada * grupo.count;
                    totalHoras += horasTotal;
                }
            });
            // Retornar objeto con horas y desglose
            return {
                horas: totalHoras,
                desglose: desgloseJornadas
            };
        }
    }

    /**
     * Obtiene las horas de una jornada específica (misma lógica que el backend)
     * Mapeo de jornadas a horas según la lógica implementada
     */
    function obtenerHorasJornadaPorRango(jornada) {
        // Mapeo de jornadas a horas según la lógica implementada
        // Ahora funciona con nombres de jornada (como viene del backend)
        const jornadas = {
            'Mañana': 6,   // Mañana: 6 horas por día
            'Tarde': 5,    // Tarde: 5 horas por día
            'Noche': 4     // Noche: 4 horas por día
        };
        
        // También mantener compatibilidad con IDs numéricos por si acaso
        const jornadasId = {
            1042: 6,  // Mañana: 6 horas por día
            1043: 5,  // Tarde: 5 horas por día
            1044: 4   // Noche: 4 horas por día
        };
        
        // Primero intentar con nombre, luego con ID
        if (jornadas[jornada] !== undefined) {
            return jornadas[jornada];
        } else if (jornadasId[jornada] !== undefined) {
            return jornadasId[jornada];
        }
        
        // Default a mañana (6 horas) si no se reconoce
        console.warn(`⚠️ Jornada no reconocida: "${jornada}", usando default 6 horas`);
        return 6;
    }

    /**
     * Genera el HTML para mostrar el contador de horarios mes a mes
     */
     async function generarHTMLContadorHorarios(contador) {
         const { instructor, año, totalAnual, resumenMensual, totalHorarios } = contador;
        
        let html = `
            <div class="row mb-4">
                <div class="col-12">
                    <div class="alert alert-info">
                        <h6><i class="fa-solid fa-calculator me-2"></i>Horas Anuales ${año}</h6>
                        <p class="mb-1"><strong>Instructor:</strong> ${instructor.nombre}</p>
                        <p class="mb-0"><strong>Total anual (todos los meses):</strong> ${totalAnual} hrs</p>
                    </div>
                </div>
            </div>
            
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title text-success">${totalAnual}</h5>
                            <p class="card-text">Horas Anuales</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title text-info">${Math.round(totalAnual / 12)}</h5>
                            <p class="card-text">Promedio Mensual</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h6>Desglose Mensual de Horarios - Año ${año}</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                <th>Mes</th>
                                <th>Desglose Mes (horas(días))</th>
                                <th>Total Horas</th>
                                            <th>Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
        `;
        
        for (const mes of resumenMensual) {
            let estadoClase, estadoTexto, estadoIcono, estadoColor;
            
            // Calcular horas esperadas para el mes
            let horasEsperadas = (window.CONTRATISTA_MAX_HORAS || 180); // Tope configurable para contratistas
            if (window.instructorActual && window.instructorActual.tipo_contrato === 'planta') {
                // Para instructores de planta, calcular dinámicamente
                // Obtener el número del mes desde el nombre del mes
                const nombreMes = mes.nombreMes;
                const meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
                              'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
                const numeroMes = meses.indexOf(nombreMes) + 1;
                
                if (numeroMes > 0) {
                    const diasLaborales = await calcularDiasLaborales(numeroMes, año);
                    
                    if (diasLaborales > 0) {
                        const horasFormacionDirecta = diasLaborales * 6.4;
                        const horasOtros = diasLaborales * 2.1;
                        horasEsperadas = horasFormacionDirecta + horasOtros;
                    }
                }
            }
            
            if (mes.futuro) {
                // Meses futuros
                if (mes.horas === 0) {
                    // Mes futuro sin horas
                    estadoClase = 'table-light';
                    estadoTexto = 'Sin asignar';
                    estadoIcono = 'fa-calendar-plus';
                    estadoColor = 'bg-secondary';
                } else {
                    // Mes futuro con horas
                    estadoClase = 'table-success';
                    estadoTexto = 'Asignado';
                    estadoIcono = 'fa-check-circle';
                    estadoColor = 'bg-success';
                }
            } else if (mes.horas === 0) {
                // Meses sin horas asignadas
                estadoClase = 'table-warning';
                estadoTexto = 'Sin horas';
                estadoIcono = 'fa-exclamation-triangle';
                estadoColor = 'bg-warning';
            } else if (mes.completado) {
                // Meses completados con horas
                estadoClase = 'table-success';
                estadoTexto = 'Completado';
                estadoIcono = 'fa-check-circle';
                estadoColor = 'bg-success';
            } else {
                // Mes actual
                estadoClase = 'table-info';
                estadoTexto = 'En curso';
                estadoIcono = 'fa-play-circle';
                estadoColor = 'bg-info';
            }
            
            // Generar el desglose detallado de horarios
            const desgloseHorarios = (mes.detalleResumen && mes.detalleResumen.length > 0) ? mes.detalleResumen : (mes.horas > 0 ? 'Con horarios' : 'Sin horarios');
            
            html += `
                <tr class="${estadoClase}">
                    <td><strong>${mes.nombreMes}</strong></td>
                    <td><small class="chips-horas-dias">${mes.detalleResumenHtml || desgloseHorarios}</small></td>
                    <td><strong>${mes.horas}/${horasEsperadas.toFixed(1)} hrs</strong></td>
                    <td>
                        <span class="badge ${estadoColor}">
                            <i class="fa-solid ${estadoIcono} me-1"></i>${estadoTexto}
                        </span>
                    </td>
                </tr>
            `;
        }
        
        html += `
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        return html;
    }

    // Event listener para la pestaña del contador de horarios
    document.addEventListener('DOMContentLoaded', function() {
        // Agregar event listener para la pestaña del contador
        const contadorTab = document.getElementById('contador-tab');
        if (contadorTab) {
            contadorTab.addEventListener('click', function() {
                if (window.instructorActual) {
                    cargarContadorHorarios(window.instructorActual);
                }
            });
        }

        // Cards "Horas por instructor": mismo endpoint server-side
        // (ope=get_instructores_dt), misma paginación/búsqueda/orden; solo cambia la presentación.
        window.HOR_DT_FILTROS = {
            idare: <?= json_encode($idare_actual ?? '') ?>,
            idfic: <?= json_encode($idfic_actual ?? '') ?>,
            idusu_inst: <?= json_encode($idusu_filtro ?? '') ?>,
            fecha_inicio: <?= json_encode($fecha_inicio_filtro ?? '') ?>,
            fecha_fin: <?= json_encode($fecha_fin_filtro ?? '') ?>,
            mes: <?= json_encode($mes_dashboard) ?>,
            anio: <?= json_encode($anio_dashboard) ?>
        };
        window.horInsCards = { pagina: 0, porPagina: 20, busqueda: '', ordenColumna: 5, ordenDir: 'asc', total: 0, draw: 0 };

        var buscadorIns = document.getElementById('buscadorInsCards');
        var tempIns = null;
        if (buscadorIns) {
            buscadorIns.addEventListener('input', function () {
                if (tempIns) {
                    clearTimeout(tempIns);
                }
                tempIns = setTimeout(function () {
                    window.horInsCards.busqueda = buscadorIns.value.trim();
                    window.horInsCards.pagina = 0;
                    cargarInsCards();
                }, 400);
            });
        }
        cargarInsCards();
    });

/**
 * Construye una card amplia por instructor reutilizando las 9 celdas HTML
 * que entrega el endpoint: [instructor, área, planta, transv, otras, total, estado, gráfica, detalle].
 */
function horInsCardHTML(celdas) {
    var instructor = celdas[0] || '';
    var area = celdas[1] || '';
    var planta = celdas[2] || '';
    var transv = celdas[3] || '';
    var otras = celdas[4] || '';
    var total = celdas[5] || '';
    var estado = celdas[6] || '';
    var grafica = celdas[7] || '';
    var detalle = celdas[8] || '';

    return '<div class="col-12">'
        + '<div class="card shadow-sm border h-100">'
        + '<div class="card-body">'
        + '<div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-2">'
        + '<div>' + instructor + '<div class="mt-1">' + area + '</div></div>'
        + '<div>' + estado + '</div>'
        + '</div>'
        + '<div class="row g-2 text-center mb-2">'
        + '<div class="col-6 col-md-3"><small class="text-muted d-block">Planta</small><span class="fw-semibold text-primary">' + planta + '</span></div>'
        + '<div class="col-6 col-md-3"><small class="text-muted d-block">Transversales</small><span class="fw-semibold text-info">' + transv + '</span></div>'
        + '<div class="col-6 col-md-3"><small class="text-muted d-block">Otras</small><span class="fw-semibold text-secondary">' + otras + '</span></div>'
        + '<div class="col-6 col-md-3"><small class="text-muted d-block">Total</small><span class="fw-bold fs-6 text-dark">' + total + '</span></div>'
        + '</div>'
        + '<div class="mb-2">' + grafica + '</div>'
        + '<div class="d-flex justify-content-end pt-2 border-top">' + detalle + '</div>'
        + '</div>'
        + '</div>'
        + '</div>';
}

/**
 * Orden de las cards de instructores (columnas del endpoint server-side).
 */
function cambiarOrdenInsCards(valor) {
    var partes = String(valor || '5|asc').split('|');
    window.horInsCards.ordenColumna = parseInt(partes[0], 10);
    if (isNaN(window.horInsCards.ordenColumna)) {
        window.horInsCards.ordenColumna = 5;
    }
    window.horInsCards.ordenDir = (partes[1] === 'desc') ? 'desc' : 'asc';
    window.horInsCards.pagina = 0;
    cargarInsCards();
}

/**
 * Paginación de las cards de instructores.
 */
function paginarInsCards(direccion) {
    var est = window.horInsCards;
    var nueva = est.pagina + direccion;
    if (nueva < 0) {
        return;
    }
    var maxPag = Math.max(0, Math.ceil(est.total / est.porPagina) - 1);
    if (nueva > maxPag) {
        return;
    }
    est.pagina = nueva;
    cargarInsCards();
}

/**
 * Carga una página de cards desde el endpoint server-side de instructores.
 */
function cargarInsCards() {
    var grid = document.getElementById('gridInsCards');
    var info = document.getElementById('infoPaginacionInsCards');
    if (!grid) {
        return;
    }
    var est = window.horInsCards;
    var f = window.HOR_DT_FILTROS || {};

    grid.innerHTML = '<div class="col-12"><div class="text-center py-4 text-muted">'
        + '<div class="spinner-border text-success" role="status"></div>'
        + '<p class="mt-2 small">Cargando instructores...</p></div></div>';

    est.draw++;
    var params = new URLSearchParams();
    params.append('ope', 'get_instructores_dt');
    params.append('draw', String(est.draw));
    params.append('start', String(est.pagina * est.porPagina));
    params.append('length', String(est.porPagina));
    params.append('search[value]', est.busqueda);
    params.append('order[0][column]', String(est.ordenColumna));
    params.append('order[0][dir]', est.ordenDir);
    params.append('idare', f.idare || '');
    params.append('idfic', f.idfic || '');
    params.append('idusu_inst', f.idusu_inst || '');
    params.append('idusu', f.idusu_inst || '');
    params.append('fecha_inicio', f.fecha_inicio || '');
    params.append('fecha_fin', f.fecha_fin || '');
    params.append('mes', f.mes || '');
    params.append('anio', f.anio || '');

    fetch('controllers/horchor.php', { method: 'POST', body: params })
        .then(function (resp) { return resp.json(); })
        .then(function (json) {
            var filas = (json && json.data) || [];
            est.total = json ? (parseInt(json.recordsFiltered, 10) || 0) : 0;

            var badge = document.getElementById('badgeInstructoresCount');
            if (badge) {
                badge.textContent = est.total + ' Instructores';
            }

            if (filas.length === 0) {
                grid.innerHTML = '<div class="col-12"><div class="alert alert-info mb-0">'
                    + '<i class="fa-solid fa-circle-info me-2"></i>No se encontraron instructores para los filtros seleccionados.</div></div>';
            } else {
                grid.innerHTML = filas.map(function (celdas) { return horInsCardHTML(celdas); }).join('');
            }

            var inicio = est.total === 0 ? 0 : (est.pagina * est.porPagina) + 1;
            var fin = Math.min(est.total, (est.pagina + 1) * est.porPagina);
            if (info) {
                info.textContent = 'Mostrando ' + inicio + ' al ' + fin + ' de ' + est.total + ' instructores';
            }
            var btnAnt = document.getElementById('btnInsCardsAnterior');
            var btnSig = document.getElementById('btnInsCardsSiguiente');
            if (btnAnt) btnAnt.disabled = est.pagina <= 0;
            if (btnSig) btnSig.disabled = fin >= est.total;
        })
        .catch(function () {
            grid.innerHTML = '<div class="col-12"><div class="alert alert-warning mb-0">'
                + '<i class="fa-solid fa-triangle-exclamation me-2"></i>No se pudieron cargar los instructores. Intente de nuevo.</div></div>';
        });
}

    /**
     * Abre el modal de reporte mensual y lo carga
     */
    function abrirModalReporteMensual() {
        const modalElem = document.getElementById('modalReporteMensual');
        if (modalElem) {
            const modal = bootstrap.Modal.getOrCreateInstance(modalElem);
            modal.show();
            cargarReporteMensual();
        }
    }

    /**
     * Consulta y renderiza el reporte mensual de horas
     */
    async function cargarReporteMensual() {
        const anio = document.getElementById('rep_men_anio')?.value || new Date().getFullYear();
        const mes = document.getElementById('rep_men_mes')?.value || (new Date().getMonth() + 1);
        const idare = document.getElementById('rep_men_idare')?.value || 'todas';
        const contenedor = document.getElementById('contenedorReporteMensual');
        if (!contenedor) return;

        contenedor.innerHTML = `
            <div class="text-center py-4 text-muted">
                <div class="spinner-border text-success" role="status"></div>
                <p class="mt-2">Cargando reporte mensual de ${obtenerNombreMes(parseInt(mes))} ${anio}...</p>
            </div>
        `;

        try {
            const formData = new FormData();
            formData.append('ope', 'get_reporte_mensual');
            formData.append('anio', anio);
            formData.append('mes', mes);
            formData.append('idare', idare);

            const response = await fetch('controllers/horchor.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success && data.data && data.data.length > 0) {
                let totalPlanta = 0, totalTrans = 0, totalOtros = 0, totalGeneral = 0;
                let filas = data.data.map(inst => {
                    totalPlanta += parseFloat(inst.horas_planta || 0);
                    totalTrans += parseFloat(inst.horas_transversales || 0);
                    totalOtros += parseFloat(inst.otras_horas || 0);
                    totalGeneral += parseFloat(inst.total_horas || 0);

                    let badge = 'bg-warning text-dark';
                    if (inst.estado === 'Cumple horas') badge = 'bg-success';
                    else if (inst.estado === 'Sobre horas') badge = 'bg-primary';

                    return `
                        <tr>
                            <td><strong>${escapeHtml(inst.nombre)}</strong><br><small class="text-muted">${escapeHtml(inst.documento || '')}</small></td>
                            <td><span class="badge bg-light text-dark border">${escapeHtml(inst.area || 'General')}</span></td>
                            <td class="text-center text-primary fw-semibold">${parseFloat(inst.horas_planta || 0).toFixed(1)}h</td>
                            <td class="text-center text-info fw-semibold">${parseFloat(inst.horas_transversales || 0).toFixed(1)}h</td>
                            <td class="text-center text-secondary fw-semibold">${parseFloat(inst.otras_horas || 0).toFixed(1)}h</td>
                            <td class="text-center fw-bold fs-6">${parseFloat(inst.total_horas || 0).toFixed(1)}h</td>
                            <td class="text-center"><span class="badge ${badge}">${escapeHtml(inst.estado)}</span></td>
                        </tr>
                    `;
                }).join('');

                contenedor.innerHTML = `
                    <div class="mb-3 d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 text-success fw-bold">
                            <i class="fa-solid fa-calendar-check me-2"></i>Período: ${obtenerNombreMes(parseInt(mes))} de ${anio}
                        </h6>
                        <span class="badge bg-success">${data.data.length} Instructores</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Instructor</th>
                                    <th>Área</th>
                                    <th class="text-center">Horas Planta</th>
                                    <th class="text-center">Transversales</th>
                                    <th class="text-center">Otras Horas</th>
                                    <th class="text-center">Total Horas</th>
                                    <th class="text-center">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${filas}
                            </tbody>
                            <tfoot class="table-success fw-bold">
                                <tr>
                                    <td colspan="2" class="text-end">TOTALES CONSOLIDADOS:</td>
                                    <td class="text-center text-primary">${totalPlanta.toFixed(1)}h</td>
                                    <td class="text-center text-info">${totalTrans.toFixed(1)}h</td>
                                    <td class="text-center text-secondary">${totalOtros.toFixed(1)}h</td>
                                    <td class="text-center text-success fs-6">${totalGeneral.toFixed(1)}h</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                `;
            } else {
                contenedor.innerHTML = `
                    <div class="alert alert-info text-center py-4">
                        <i class="fa-solid fa-info-circle fa-2x mb-2 d-block"></i>
                        No se encontraron registros de horas para el período ${obtenerNombreMes(parseInt(mes))} ${anio}.
                    </div>
                `;
            }
        } catch (e) {
            contenedor.innerHTML = `
                <div class="alert alert-danger text-center">
                    <i class="fa-solid fa-exclamation-triangle me-2"></i>Error al consultar reporte mensual: ${e.message}
                </div>
            `;
        }
    }

    /**
     * Abre el modal de reporte anual y lo carga
     */
    function abrirModalReporteAnual() {
        const modalElem = document.getElementById('modalReporteAnual');
        if (modalElem) {
            const modal = bootstrap.Modal.getOrCreateInstance(modalElem);
            modal.show();
            cargarReporteAnual();
        }
    }

    /**
     * Consulta y renderiza la matriz del reporte anual (12 meses)
     */
    async function cargarReporteAnual() {
        const anio = document.getElementById('rep_anual_anio')?.value || new Date().getFullYear();
        const idare = document.getElementById('rep_anual_idare')?.value || 'todas';
        const contenedor = document.getElementById('contenedorReporteAnual');
        if (!contenedor) return;

        contenedor.innerHTML = `
            <div class="text-center py-4 text-muted">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="mt-2">Cargando matriz anual de ${anio} (12 meses)...</p>
            </div>
        `;

        try {
            const formData = new FormData();
            formData.append('ope', 'get_reporte_anual');
            formData.append('anio', anio);
            formData.append('idare', idare);

            const response = await fetch('controllers/horchor.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success && data.data && data.data.length > 0) {
                let totalesMeses = Array(13).fill(0);
                let totalGeneralAnual = 0;

                let filas = data.data.map(inst => {
                    totalGeneralAnual += parseFloat(inst.total_anual || 0);
                    let celdasMeses = '';
                    for (let m = 1; m <= 12; m++) {
                        let hMes = parseFloat(inst.meses[m] || 0);
                        totalesMeses[m] += hMes;
                        let textClass = hMes > 0 ? (hMes < 160 ? 'text-warning fw-semibold' : 'text-success fw-semibold') : 'text-muted';
                        celdasMeses += `<td class="text-center ${textClass}">${hMes > 0 ? hMes.toFixed(1) + 'h' : '-'}</td>`;
                    }

                    return `
                        <tr>
                            <td><strong>${escapeHtml(inst.nombre)}</strong></td>
                            <td><small class="badge bg-light text-dark border">${escapeHtml(inst.area || 'General')}</small></td>
                            ${celdasMeses}
                            <td class="text-center fw-bold bg-light fs-6 text-primary">${parseFloat(inst.total_anual || 0).toFixed(1)}h</td>
                        </tr>
                    `;
                }).join('');

                let tfootMeses = '';
                for (let m = 1; m <= 12; m++) {
                    tfootMeses += `<td class="text-center">${totalesMeses[m].toFixed(1)}h</td>`;
                }

                contenedor.innerHTML = `
                    <div class="mb-3 d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 text-primary fw-bold">
                            <i class="fa-solid fa-chart-simple me-2"></i>Matriz Anual de Carga Horaria - Año ${anio}
                        </h6>
                        <span class="badge bg-primary">${data.data.length} Instructores</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover align-middle table-sm">
                            <thead class="table-dark text-center">
                                <tr>
                                    <th class="text-start">Instructor</th>
                                    <th>Área</th>
                                    <th>Ene</th><th>Feb</th><th>Mar</th><th>Abr</th><th>May</th><th>Jun</th>
                                    <th>Jul</th><th>Ago</th><th>Sep</th><th>Oct</th><th>Nov</th><th>Dic</th>
                                    <th>Total Anual</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${filas}
                            </tbody>
                            <tfoot class="table-primary fw-bold text-center">
                                <tr>
                                    <td colspan="2" class="text-end">TOTAL MES:</td>
                                    ${tfootMeses}
                                    <td class="fs-6">${totalGeneralAnual.toFixed(1)}h</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                `;
            } else {
                contenedor.innerHTML = `
                    <div class="alert alert-info text-center py-4">
                        <i class="fa-solid fa-info-circle fa-2x mb-2 d-block"></i>
                        No se encontraron registros de horas para el año ${anio}.
                    </div>
                `;
            }
        } catch (e) {
            contenedor.innerHTML = `
                <div class="alert alert-danger text-center">
                    <i class="fa-solid fa-exclamation-triangle me-2"></i>Error al consultar reporte anual: ${e.message}
                </div>
            `;
        }
    }

    /**
     * Utilidad de impresión de reportes y elementos
     */
    function imprimirElemento(idElemento, titulo) {
        const elemento = document.getElementById(idElemento);
        if (!elemento) return;

        const ventana = window.open('', '_blank', 'width=1100,height=750');
        ventana.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="utf-8">
                <title>${titulo}</title>
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
                </head>
            <body>
                <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                    <div>
                        <h4 class="mb-0 text-success fw-bold">SagiCDA - SENA</h4>
                        <h5 class="mb-0 text-muted">${titulo}</h5>
                    </div>
                    <div class="text-end">
                        <small class="text-muted">Fecha de impresión: ${new Date().toLocaleString('es-CO')}</small>
                    </div>
                </div>
                ${elemento.innerHTML}
                <script>
                    window.onload = function() { window.print(); };
                <\/script>
            </body>
            </html>
        `);
        ventana.document.close();
    }

    /**
     * Escape HTML para evitar problemas de inyección
     */
    function escapeHtml(text) {
        if (!text) return '';
        const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
        return String(text).replace(/[&<>"']/g, m => map[m]);
    }
</script>

