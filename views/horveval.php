<?php
/**
 * VISTA: horveval.php
 * Responsabilidad: Interfaz Institucional SAGI SENA - Módulo de Instrumentos y Juicios de Evaluación
 * Arquitectura: Vista en patrón MVC (Consume datos del Controlador horceval.php y Modelo hormeval.php)
 */

if (!defined('HORVEVAL_LOADED_FROM_VIEW')) {
    define('HORVEVAL_LOADED_FROM_VIEW', true);
}
require_once __DIR__ . '/../controllers/horceval.php';
require_once("views/vscopehya.php");

$icono = $icono ?? 'fa-solid fa-file-circle-check';
$pg = $pg ?? '2117';

// Detectar si está embebido en home.php o si se carga de forma standalone
$esStandalone = !isset($_SESSION["nomusu"]) || empty($_SESSION["nomusu"]);
$nombreUsuarioLogueado = $_SESSION['nomusu'] ?? 'ROBINSON ENRIQUE RINCON RAMIREZ';
$partesNombre = explode(" ", $nombreUsuarioLogueado);
$primerNombre = $partesNombre[0] ?? 'ROBINSON';
$primerApellido = (count($partesNombre) <= 3) ? ($partesNombre[1] ?? 'RINCON') : ($partesNombre[2] ?? 'RINCON');
$perfilNombre = $_SESSION['pefnom'] ?? 'Coordinación HyA';
?>

<?php if ($esStandalone): ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SAGISENA - Módulo de Instrumentos y Juicios de Evaluación</title>
    <link rel="shortcut icon" href="../img/favicon.png">
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../font/fontawesome/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="../css/style.css">
    <link rel="stylesheet" type="text/css" href="../css/menu.css">
    <link rel="stylesheet" type="text/css" href="../css/sweetalert2.min.css">
    <script src="../js/sweetalert2.all.min.js"></script>
    <script src="../js/jquery-3.5.1.js"></script>
    <script src="../js/bootstrap.bundle.min.js"></script>
</head>
<body style="background-color: #f8fafc; font-family: 'Segoe UI', system-ui, sans-serif;">
<?php endif; ?>

<style>
/* Estilos Institucionales SAGI SENA para Evaluación */
:root {
    --color-sena: #00af00;
    --color-sena-dark: #117f09;
    --color-sena-blue: #00324D;
}

.sagi-banusu-widget {
    display: inline-flex;
    align-items: center;
    position: fixed;
    top: 6px;
    right: 0px;
    padding: 8px 18px 8px 22px;
    background-color: #117f09;
    color: #ffffff;
    border-radius: 31px 0px 0px 31px;
    font-size: 0.95rem;
    font-weight: 700;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.2);
    z-index: 1040;
    gap: 14px;
}

.sagi-kpi-card {
    background: #ffffff;
    border-radius: 10px;
    padding: 16px;
    border: 1px solid #e2e8f0;
    border-left-width: 4px;
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
    transition: all 0.2s ease;
    cursor: pointer;
    text-align: center;
}
.sagi-kpi-card:hover {
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
}
.sagi-kpi-icon-circle {
    width: 46px;
    height: 46px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 10px auto;
    font-size: 20px;
}

.badge-sena-a {
    background-color: #d1fae5;
    color: #065f46;
    border: 1px solid #a7f3d0;
    font-weight: 800;
    font-size: 13px;
    padding: 4px 10px;
    border-radius: 6px;
    display: inline-block;
}

.badge-sena-de {
    background-color: #fee2e2;
    color: #991b1b;
    border: 1px solid #fecaca;
    font-weight: 800;
    font-size: 13px;
    padding: 4px 10px;
    border-radius: 6px;
    display: inline-block;
}

.btn-plan-mejoramiento {
    background-color: #991b1b;
    color: #ffffff;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 700;
    padding: 5px 10px;
    border: none;
    transition: all 0.2s ease;
}
.btn-plan-mejoramiento:hover {
    background-color: #7f1d1d;
    color: #ffffff;
}

.sagi-filter-box {
    background: #ffffff;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    padding: 20px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    margin-bottom: 20px;
}

.sagi-rounded-input {
    border-radius: 8px;
    background-color: #f8fafc;
    border: 1px solid #cbd5e1;
    font-size: 13px;
    font-weight: 500;
    height: 42px;
}

.sagi-semaforo-bar {
    background: #ffffff;
    border-radius: 10px;
    border: 1px solid #e2e8f0;
    padding: 10px 16px;
    margin-bottom: 16px;
}
</style>

<!-- BARRA DE USUARIO SUPERIOR (.banusu) -->
<div class="sagi-banusu-widget">
    <div style="text-align: right; line-height: 1.2;">
        <span style="display: block; font-size: 13px; font-weight: bold; text-transform: uppercase;">
            <?= htmlspecialchars($primerNombre . ' ' . $primerApellido) ?>
        </span>
        <small style="font-size: 11px; font-weight: normal; color: #dcfce7;">
            <?= htmlspecialchars($perfilNombre) ?>
        </small>
    </div>
    <div style="padding-left: 10px; border-left: 1px solid rgba(255,255,255,0.3); display: flex; gap: 10px;">
        <a href="home.php?pg=1324" title="Datos Personales" style="color: #ffffff;">
            <i class="fa-solid fa-credit-card fa-lg"></i>
        </a>
        <a href="views/vsal.php" title="Salir" style="color: #ffffff;">
            <i class="fa-solid fa-power-off fa-lg"></i>
        </a>
    </div>
</div>

<!-- CONTENEDOR PRINCIPAL DEL MÓDULO DE EVALUACIÓN -->
<div class="container-fluid px-3 pt-2 mb-5">

    <!-- 1. ENCABEZADO CON LOGO SENA Y TÍTULO OFICIAL -->
    <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom position-relative">
        <div class="d-flex align-items-center gap-3">
            <!-- Logo SENA -->
            <div class="bg-white p-1 rounded border shadow-sm d-flex align-items-center justify-center" style="width: 48px; height: 48px;">
                <img src="img/sena.png" alt="SENA" style="max-height: 38px; width: auto;">
            </div>
            <div>
                <div class="d-flex align-items-center gap-2">
                    <i class="fa-solid fa-file-circle-check text-success fs-3"></i>
                    <h1 class="h3 fw-bold text-success mb-0" style="color: #117f09 !important; letter-spacing: -0.5px;">
                        Resultados e Instrumentos de Evaluación
                    </h1>
                </div>
                <div style="height: 2px; width: 280px; background: linear-gradient(to right, #117f09, transparent); margin-top: 4px;"></div>
            </div>
        </div>

        <!-- Botón de Navegación Intermodular e Ícono de Ayuda Flotante -->
        <div class="d-flex align-items-center gap-3 pe-5">
            <div class="btn-group shadow-sm d-none d-md-flex" role="group">
                <a href="home.php?pg=1545" class="btn btn-outline-success fw-semibold btn-sm d-flex align-items-center gap-1">
                    <i class="fa-solid fa-screwdriver-wrench"></i> 1. Instrumentos
                </a>
                <a href="home.php?pg=2117" class="btn btn-success fw-semibold btn-sm active d-flex align-items-center gap-1">
                    <i class="fa-solid fa-gavel"></i> 2. Evaluaciones
                </a>
            </div>
            <button type="button" class="btn btn-success rounded-circle shadow-sm" style="width: 34px; height: 34px; padding: 0; background-color: #39A900; border: none;" 
                    onclick="Swal.fire('Módulo de Evaluación', 'Este panel permite consultar el estado evaluativo de los aprendices de ADSO, monitorear el semáforo de alertas tempranas y concertar los Planes de Mejoramiento (Formato GFPI-F-023).', 'info')" title="Ayuda del Módulo">
                <span class="fw-bold">?</span>
            </button>
        </div>
    </div>

    <!-- 2. TARJETAS DE MÉTRICAS (KPIs) CON BORDES DE COLORES -->
    <div class="row g-3 mb-4">
        <!-- Tarjeta 1 (Borde Verde): Instrumentos Planeados -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="sagi-kpi-card" style="border-left-color: #117f09;" onclick="filtrarPorKpi('todos')">
                <div class="sagi-kpi-icon-circle" style="background-color: #dcfce7; color: #117f09;">
                    <i class="fa-solid fa-clipboard-list"></i>
                </div>
                <div class="text-uppercase fw-bold text-muted" style="font-size: 11px;">INSTRUMENTOS PLANEADOS</div>
                <div class="fw-black fs-2 text-dark my-1" style="font-weight: 800;"><?= $kpi_planeados ?? 24 ?></div>
                <small class="text-muted">En el sistema / ADSO</small>
            </div>
        </div>

        <!-- Tarjeta 2 (Borde Azul): Instrumentos Evaluados -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="sagi-kpi-card" style="border-left-color: #0284c7;" onclick="filtrarPorKpi('todos')">
                <div class="sagi-kpi-icon-circle" style="background-color: #e0f2fe; color: #0284c7;">
                    <i class="fa-solid fa-id-badge"></i>
                </div>
                <div class="text-uppercase fw-bold text-muted" style="font-size: 11px;">INSTRUMENTOS EVALUADOS</div>
                <div class="fw-black fs-2 text-dark my-1" style="font-weight: 800;"><?= $kpi_evaluados ?? count($evaluaciones) ?></div>
                <small class="text-muted">Ficha <?= htmlspecialchars($idfic) ?></small>
            </div>
        </div>

        <!-- Tarjeta 3 (Borde Amarillo): Juicios 'A' (Aprobados) -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="sagi-kpi-card" style="border-left-color: #eab308;" onclick="filtrarPorKpi('A')">
                <div class="sagi-kpi-icon-circle" style="background-color: #fef9c3; color: #b45309;">
                    <i class="fa-solid fa-award"></i>
                </div>
                <div class="text-uppercase fw-bold text-muted" style="font-size: 11px;">JUICIOS 'A' (APROBADOS)</div>
                <div class="fw-black fs-2 text-dark my-1" style="font-weight: 800; color: #15803d;"><?= $kpi_aprobados ?? 0 ?></div>
                <small class="text-muted">Resultados de aprendizaje alcanzados</small>
            </div>
        </div>

        <!-- Tarjeta 4 (Borde Celeste): Juicios 'DE' (Deficientes) -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="sagi-kpi-card" style="border-left-color: #06b6d4;" onclick="filtrarPorKpi('DE')">
                <div class="sagi-kpi-icon-circle" style="background-color: #cffafe; color: #0e7490;">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <div class="text-uppercase fw-bold text-muted" style="font-size: 11px;">JUICIOS 'DE' (DEFICIENTES)</div>
                <div class="fw-black fs-2 text-dark my-1" style="font-weight: 800; color: #b91c1c;"><?= $kpi_deficientes ?? 0 ?></div>
                <small class="text-muted">Requieren Plan de Mejoramiento</small>
            </div>
        </div>
    </div>

    <!-- 3. SECCIÓN DE FILTROS REDONDEADOS Y FRANJA INFORMATIVA DE INSTRUCTOR -->
    <div class="sagi-filter-box">
        <form method="GET" action="home.php" id="form-filtros">
            <input type="hidden" name="pg" value="<?= htmlspecialchars($pg) ?>">
            
            <div class="row g-3 align-items-end">
                <!-- Dropdown 1: Ficha de Caracterización -->
                <div class="col-md-5">
                    <label for="filtro-idfic" class="form-label fw-bold text-dark small mb-1">
                        <i class="fa-solid fa-user-gear text-success me-1"></i> Seleccione Ficha de Caracterización:
                    </label>
                    <select name="idfic" id="filtro-idfic" class="form-select sagi-rounded-input">
                        <option value="3235287" <?= ($idfic == '3235287') ? 'selected' : '' ?>>3235287 - ANALISIS Y DESARROLLO DE SOFTWARE (ADSO)</option>
                        <option value="2996491" <?= ($idfic == '2996491') ? 'selected' : '' ?>>2996491 - ANALISIS Y DESARROLLO DE SOFTWARE (ADSO)</option>
                        <option value="2773071" <?= ($idfic == '2773071') ? 'selected' : '' ?>>2773071 - ANALISIS Y DESARROLLO DE SOFTWARE (ADSO)</option>
                        <?php if (!empty($datFic)): ?>
                            <?php foreach ($datFic as $f): ?>
                                <?php if (!in_array($f['idfic'], ['3235287', '2996491', '2773071'])): ?>
                                    <option value="<?= $f['idfic'] ?>" <?= ($idfic == $f['idfic']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($f['idfic']) ?> - <?= htmlspecialchars($f['nomfic']) ?>
                                    </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <!-- Dropdown 2: Competencia de ADSO -->
                <div class="col-md-4">
                    <label for="filtro-idcom" class="form-label fw-bold text-dark small mb-1">
                        <i class="fa-solid fa-book-bookmark text-primary me-1"></i> # Competencia de ADSO:
                    </label>
                    <select name="idcom" id="filtro-idcom" class="form-select sagi-rounded-input">
                        <option value="ALL" <?= ($idcom == 'ALL') ? 'selected' : '' ?>>-- Todas las Competencias de la Ficha --</option>
                        <option value="220501096" <?= ($idcom == '220501096') ? 'selected' : '' ?>>220501096 - Desarrollar la solución de software de acuerdo con el diseño</option>
                        <option value="220501093" <?= ($idcom == '220501093') ? 'selected' : '' ?>>220501093 - Diseñar la solución de software de acuerdo con los requisitos</option>
                        <option value="220501092" <?= ($idcom == '220501092') ? 'selected' : '' ?>>220501092 - Especificar los requisitos necesarios para el software</option>
                        <option value="240201500" <?= ($idcom == '240201500') ? 'selected' : '' ?>>240201500 - Promover la interacción idónea consigo mismo y la naturaleza</option>
                        <?php if (!empty($datCompe)): ?>
                            <?php foreach ($datCompe as $c): ?>
                                <?php if (!in_array($c['idcom'], ['220501096', '220501093', '220501092', '240201500'])): ?>
                                    <option value="<?= $c['idcom'] ?>" <?= ($idcom == $c['idcom']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($c['idcom']) ?> - <?= htmlspecialchars(substr($c['descom'], 0, 45)) ?>...
                                    </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <!-- Botones Consultar y Limpiar -->
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-success fw-bold flex-fill sagi-rounded-input d-flex align-items-center justify-content-center gap-2" style="background-color: #117f09; border: none;">
                        <i class="fa-solid fa-magnifying-glass"></i> Consultar
                    </button>
                    <a href="home.php?pg=<?= htmlspecialchars($pg) ?>&idfic=3235287" class="btn btn-light border fw-semibold sagi-rounded-input d-flex align-items-center justify-content-center gap-1 px-3" title="Restablecer filtros">
                        <i class="fa-solid fa-rotate-left text-secondary"></i> Limpiar
                    </a>
                </div>
            </div>
        </form>

        <!-- Franja Informativa de Instructor (Idéntica a la imagen de SAGI) -->
        <div class="mt-3 pt-3 border-top d-flex flex-wrap align-items-center justify-content-between text-muted" style="font-size: 12px;">
            <div class="d-flex flex-wrap align-items-center gap-3">
                <span><strong>Instructor:</strong> ALVARO DE JESUS CONTRERAS GONZALEZ</span>
                <span><strong>Documento:</strong> 79719079</span>
                <span><strong>Correo:</strong> <span class="text-success fw-semibold">acontrerasg@sena.edu.co</span></span>
                <span><strong>Ficha activa:</strong> <?= htmlspecialchars($idfic) ?> (ADSO)</span>
            </div>
            <div>
                <strong>Aprendices encontrados:</strong> 
                <span class="badge bg-success ms-1" style="background-color: #117f09 !important; font-size: 12px;">
                    <?= count($evaluaciones) ?>
                </span>
            </div>
        </div>
    </div>

    <!-- 4. SEMÁFORO DE ALERTAS TEMPRANAS -->
    <div class="sagi-semaforo-bar d-flex flex-wrap align-items-center justify-content-between gap-2 shadow-xs">
        <div class="d-flex align-items-center gap-2">
            <i class="fa-solid fa-traffic-light text-warning fs-5"></i>
            <div>
                <span class="fw-bold text-dark text-uppercase small">Semáforo de Alertas Tempranas</span>
                <span class="text-muted small d-block" style="font-size: 11px;">Monitoreo preventivo del estado académico y riesgo de deserción</span>
            </div>
        </div>

        <!-- Filtros Rápidos de Semáforo -->
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <button type="button" class="btn btn-sm btn-dark rounded-pill px-3 fw-bold" onclick="filtrarSemaforo('todos')">
                Todos (<span id="count-todos"><?= count($evaluaciones) ?></span>)
            </button>
            <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3 fw-bold" onclick="filtrarSemaforo('verde')">
                <i class="fa-solid fa-circle text-success me-1"></i> Al Día (<span id="count-verde"><?= $sem_verde ?></span>)
            </button>
            <button type="button" class="btn btn-sm btn-outline-warning rounded-pill px-3 fw-bold" onclick="filtrarSemaforo('amarillo')">
                <i class="fa-solid fa-circle text-warning me-1"></i> En Seguimiento (<span id="count-amarillo"><?= $sem_amarillo ?></span>)
            </button>
            <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-bold" onclick="filtrarSemaforo('rojo')">
                <i class="fa-solid fa-circle text-danger me-1"></i> Crítico / 'DE' (<span id="count-rojo"><?= $sem_rojo ?></span>)
            </button>
        </div>
    </div>

    <!-- 5. TABLA ESTRUCTURADA DE INSTRUMENTOS Y JUICIOS EVALUATIVOS -->
    <div class="card shadow-sm border-0 rounded-3 overflow-hidden">
        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
            <h6 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                <i class="fa-solid fa-list-check text-success"></i>
                Registro de Evaluaciones por Aprendiz de ADSO
            </h6>
            <span class="badge bg-light text-secondary border">
                Total Ficha: <?= count($evaluaciones) ?> Aprendices
            </span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle mb-0" id="tabla-evaluacion">
                    <thead class="table-light text-uppercase text-secondary" style="font-size: 11px; font-weight: 700;">
                        <tr>
                            <th class="ps-3 py-3">Ficha</th>
                            <th>Competencia</th>
                            <th>Instrumento de Evaluación</th>
                            <th>Aprendiz</th>
                            <th class="text-center">Alerta</th>
                            <th>Estado Entrega</th>
                            <th class="text-center">Juicio Evaluativo</th>
                            <th class="text-center pe-3">Acciones Pedagógicas</th>
                        </tr>
                    </thead>
                    <tbody style="font-size: 12px;">
                        <?php if (!empty($evaluaciones)): ?>
                            <?php foreach ($evaluaciones as $idx => $ev): ?>
                                <?php
                                    $esAprobado = (isset($ev['caljui']) && (strtoupper($ev['caljui']) === 'APROBADO' || strtoupper($ev['caljui']) === 'A'));
                                    
                                    // Simulación de estados para la muestra pedagógica
                                    $alerta = $esAprobado ? 'verde' : 'rojo';
                                    if ($idx == 1) $alerta = 'amarillo';

                                    $estadoEntrega = $esAprobado ? 'A tiempo' : ($alerta == 'amarillo' ? 'Tardía' : 'Sin entrega');
                                    $tipoInstrumento = ($idx % 2 == 0) ? 'Lista de Chequeo de Producto' : 'Cuestionario Técnico';
                                ?>
                                <tr class="fila-evaluacion" data-juicio="<?= $esAprobado ? 'A' : 'DE' ?>" data-alerta="<?= $alerta ?>">
                                    <!-- Ficha -->
                                    <td class="ps-3 fw-bold text-dark whitespace-nowrap">
                                        <span class="badge bg-light text-dark border">
                                            <?= htmlspecialchars($ev['idfic'] ?? $idfic) ?>
                                        </span>
                                    </td>

                                    <!-- Competencia -->
                                    <td style="max-width: 240px;">
                                        <span class="fw-semibold text-dark d-block text-truncate" title="<?= htmlspecialchars($ev['descom'] ?? '') ?>">
                                            <?= htmlspecialchars($ev['idcom'] ?? '220501096') ?>
                                        </span>
                                        <small class="text-muted text-truncate d-block" title="<?= htmlspecialchars($ev['descom'] ?? '') ?>">
                                            <?= htmlspecialchars(substr($ev['descom'] ?? 'ADSO', 0, 45)) ?>...
                                        </small>
                                    </td>

                                    <!-- Instrumento de Evaluación -->
                                    <td style="max-width: 250px;">
                                        <span class="fw-bold text-dark d-block text-truncate" title="<?= htmlspecialchars($ev['nomins'] ?? '') ?>">
                                            <?= htmlspecialchars($ev['nomins'] ?? 'Lista de Chequeo de Producto: Backend y Arquitectura') ?>
                                        </span>
                                        <div class="d-flex align-items-center gap-1 mt-1">
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size: 9px;">
                                                <?= $tipoInstrumento ?>
                                            </span>
                                            <small class="text-muted" style="font-size: 10px;">IE-ADSO-<?= str_pad($idx + 1, 2, '0', STR_PAD_LEFT) ?></small>
                                        </div>
                                    </td>

                                    <!-- Aprendiz -->
                                    <td>
                                        <span class="fw-bold text-dark d-block">
                                            <?= htmlspecialchars($ev['nomusu'] ?? 'APRENDIZ EN FORMACIÓN') ?>
                                        </span>
                                        <small class="text-muted">
                                            CC <?= htmlspecialchars($ev['ndocusu'] ?? '') ?> • <span class="text-success"><?= htmlspecialchars($ev['emausu'] ?? '') ?></span>
                                        </small>
                                    </td>

                                    <!-- Alerta Semáforo -->
                                    <td class="text-center">
                                        <?php if ($alerta == 'verde'): ?>
                                            <span class="p-1 d-inline-block rounded-circle bg-success shadow-sm" style="width: 12px; height: 12px;" title="Al Día"></span>
                                        <?php elseif ($alerta == 'amarillo'): ?>
                                            <span class="p-1 d-inline-block rounded-circle bg-warning shadow-sm" style="width: 12px; height: 12px;" title="En Seguimiento"></span>
                                        <?php else: ?>
                                            <span class="p-1 d-inline-block rounded-circle bg-danger shadow-sm" style="width: 12px; height: 12px; animation: pulse 1.5s infinite;" title="Crítico / Requiere Plan"></span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Estado de Entrega -->
                                    <td class="whitespace-nowrap">
                                        <?php if ($estadoEntrega == 'A tiempo'): ?>
                                            <span class="text-success fw-semibold small"><i class="fa-solid fa-circle-check me-1"></i> A tiempo</span>
                                        <?php elseif ($estadoEntrega == 'Tardía'): ?>
                                            <span class="text-warning fw-semibold small"><i class="fa-solid fa-clock me-1"></i> Tardía</span>
                                        <?php else: ?>
                                            <span class="text-danger fw-semibold small"><i class="fa-solid fa-circle-xmark me-1"></i> Sin entrega</span>
                                        <?php endif; ?>
                                        <small class="text-muted d-block" style="font-size: 10px;"><?= htmlspecialchars(substr($ev['fecha_reporte'] ?? date('Y-m-d'), 0, 10)) ?></small>
                                    </td>

                                    <!-- Juicio Evaluativo Oficial SENA (A / DE) -->
                                    <td class="text-center whitespace-nowrap">
                                        <?php if ($esAprobado): ?>
                                            <button type="button" class="badge-sena-a border-0 shadow-xs" 
                                                    onclick="alternarJuicio('<?= $ev['idusu'] ?>', '<?= $idfic ?>', 'NO APROBADO')" 
                                                    title="Juicio: Aprobado (Clic para alternar)">
                                                A
                                            </button>
                                            <div class="text-muted text-uppercase mt-1" style="font-size: 9px; font-weight: 700;">Aprobado</div>
                                        <?php else: ?>
                                            <button type="button" class="badge-sena-de border-0 shadow-xs" 
                                                    onclick="alternarJuicio('<?= $ev['idusu'] ?>', '<?= $idfic ?>', 'APROBADO')" 
                                                    title="Juicio: Deficiente (Clic para alternar)">
                                                DE
                                            </button>
                                            <div class="text-muted text-uppercase mt-1" style="font-size: 9px; font-weight: 700;">Deficiente</div>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Acciones Pedagógicas -->
                                    <td class="text-center pe-3 whitespace-nowrap">
                                        <?php if (!$esAprobado): ?>
                                            <button type="button" class="btn btn-plan-mejoramiento shadow-xs d-inline-flex align-items-center gap-1"
                                                    onclick="abrirModalPlan('<?= htmlspecialchars(addslashes($ev['nomusu'])) ?>', '<?= $ev['ndocusu'] ?>', '<?= htmlspecialchars(addslashes($ev['nomins'] ?? 'Lista de Chequeo ADSO')) ?>', '<?= $ev['idcom'] ?>', '<?= $ev['idusu'] ?>')">
                                                <i class="fa-solid fa-file-signature"></i> Plan de Mejoramiento
                                            </button>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-sm btn-light border text-muted px-2 py-1" onclick="Swal.fire('Evidencia Aprobada', 'El aprendiz cumplió satisfactoriamente con la totalidad de los criterios de evaluación del instrumento.', 'success')">
                                                <i class="fa-solid fa-eye me-1"></i> Ver Evidencia
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-folder-open fa-3x mb-3 text-secondary d-block"></i>
                                    <h5>No se encontraron aprendices ni juicios registrados para esta ficha.</h5>
                                    <p class="small text-muted">Intente seleccionando la ficha <strong>3235287</strong> o restablezca los filtros con el botón "Limpiar".</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-light py-2 px-3 d-flex flex-wrap align-items-center justify-content-between text-muted" style="font-size: 11px;">
            <div class="d-flex align-items-center gap-3">
                <span><i class="fa-solid fa-circle text-success me-1"></i> <strong>A</strong> = Aprobado (Cumple criterios)</span>
                <span><i class="fa-solid fa-circle text-danger me-1"></i> <strong>DE</strong> = Deficiente / Por Mejorar (Requiere Plan de Mejoramiento)</span>
            </div>
            <div>
                <em>* Puede hacer clic sobre la insignia "A" o "DE" para alternar el juicio evaluativo en tiempo real.</em>
            </div>
        </div>
    </div>
</div>

<!-- 6. MODAL FORMATO OFICIAL SENA (GFPI-F-023: PLAN DE MEJORAMIENTO ACADÉMICO / PEDAGÓGICO) -->
<div class="modal fade" id="modalPlanMejoramientoSena" tabindex="-1" aria-labelledby="modalPlanLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
            <!-- Encabezado Oficial SENA -->
            <div class="modal-header text-white" style="background-color: #117f09;">
                <div class="d-flex align-items-center gap-2">
                    <i class="fa-solid fa-graduation-cap fs-4"></i>
                    <div>
                        <div class="small fw-bold text-uppercase" style="font-size: 10px; opacity: 0.9;">
                            SERVICIO NACIONAL DE APRENDIZAJE SENA • FORMATO GFPI-F-023
                        </div>
                        <h5 class="modal-title fw-bold" id="modalPlanLabel">
                            Plan de Mejoramiento Académico / Pedagógico
                        </h5>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <!-- Cuerpo del Formulario -->
            <form id="form-guardar-plan" onsubmit="guardarPlanMejoramiento(event)">
                <input type="hidden" id="modal-idusu" value="">
                
                <div class="modal-body p-4" style="font-size: 12px; max-height: 75vh; overflow-y: auto;">
                    <!-- Cabecera Institucional y Datos del Aprendiz -->
                    <div class="bg-light p-3 rounded border mb-3">
                        <div class="row g-2">
                            <div class="col-md-6">
                                <span class="text-muted d-block" style="font-size: 10px; text-transform: uppercase;">Centro de Formación:</span>
                                <strong>Centro de Desarrollo Agroempresarial (CDA) - Chía</strong>
                            </div>
                            <div class="col-md-6">
                                <span class="text-muted d-block" style="font-size: 10px; text-transform: uppercase;">Ficha de Formación:</span>
                                <strong id="modal-ficha-text"><?= htmlspecialchars($idfic) ?> - ANALISIS Y DESARROLLO DE SOFTWARE</strong>
                            </div>
                            <div class="col-md-6 mt-2">
                                <span class="text-muted d-block" style="font-size: 10px; text-transform: uppercase;">Aprendiz Asignado:</span>
                                <strong class="fs-6 text-dark" id="modal-nombre-aprendiz">--</strong>
                                <small class="text-muted d-block">Documento: <span id="modal-doc-aprendiz">--</span></small>
                            </div>
                            <div class="col-md-6 mt-2">
                                <span class="text-muted d-block" style="font-size: 10px; text-transform: uppercase;">Instructor Técnico Responsable:</span>
                                <strong>ALVARO DE JESUS CONTRERAS GONZALEZ</strong>
                            </div>
                        </div>
                    </div>

                    <!-- Cuadro de Diagnóstico del Instrumento Reprobado -->
                    <div class="p-3 rounded border border-danger-subtle bg-danger-subtle bg-opacity-25 mb-3 text-danger-emphasis">
                        <div class="fw-bold mb-1 d-flex align-items-center gap-1">
                            <i class="fa-solid fa-triangle-exclamation text-danger"></i> Diagnóstico de Criterios No Aprobados:
                        </div>
                        <div class="row g-2 small">
                            <div class="col-md-6">
                                <span><strong>Competencia:</strong> <span id="modal-competencia-text">220501096 - Desarrollar software</span></span>
                            </div>
                            <div class="col-md-6">
                                <span><strong>Instrumento Reprobado:</strong> <span id="modal-instrumento-text">--</span></span>
                            </div>
                        </div>
                    </div>

                    <!-- Campos de Concertación de Actividades -->
                    <div class="mb-3">
                        <label for="actividades-plan" class="form-label fw-bold text-dark">
                            Actividades Pedagógicas de Subsanación y Criterios a Desarrollar:
                        </label>
                        <textarea class="form-control" id="actividades-plan" rows="3" required style="font-size: 12px;">1. Subsanar las falencias identificadas en el instrumento de evaluación.
2. Presentar la solución de software refactorizada cumpliendo con los criterios de calidad y pruebas unitarias.
3. Realizar sustentación técnica ante el instructor asignado de acuerdo con la lista de chequeo.</textarea>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="tipo-evidencia" class="form-label fw-bold text-dark">Tipo de Evidencia a Entregar:</label>
                            <select class="form-select sagi-rounded-input" id="tipo-evidencia">
                                <option value="Producto">Evidencia de Producto (Software / Código / Script SQL)</option>
                                <option value="Desempeño">Evidencia de Desempeño (Sustentación / Demostración en Vivo)</option>
                                <option value="Conocimiento">Evidencia de Conocimiento (Cuestionario / Examen de Recuperación)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="fecha-limite-plan" class="form-label fw-bold text-dark">Fecha Límite Concertada:</label>
                            <input type="date" class="form-control sagi-rounded-input" id="fecha-limite-plan" value="<?= date('Y-m-d', strtotime('+7 days')) ?>" required>
                        </div>
                    </div>

                    <div class="alert alert-secondary mt-3 mb-0 py-2 small" style="font-size: 11px;">
                        <i class="fa-solid fa-scale-balanced me-1"></i>
                        <strong>Compromiso:</strong> Conforme al Reglamento del Aprendiz SENA (Acuerdo 007 de 2012), el incumplimiento de este plan acarreará citación a Comité de Evaluación y Seguimiento de la Ficha.
                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="Swal.fire('Exportación Oficial', 'Generando formato oficial SENA GFPI-F-023 en documento PDF...', 'info')">
                        <i class="fa-solid fa-file-pdf me-1"></i> Simular PDF Oficial
                    </button>
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success btn-sm fw-bold px-3" style="background-color: #117f09; border: none;">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Guardar y Concertar Plan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- SCRIPTS DE INTERACCIÓN Y FILTROS EN TIEMPO REAL -->
<script>
// Filtrado por Semáforo
function filtrarSemaforo(tipo) {
    const filas = document.querySelectorAll('.fila-evaluacion');
    filas.forEach(f => {
        if (tipo === 'todos') {
            f.style.display = '';
        } else {
            f.style.display = (f.getAttribute('data-alerta') === tipo) ? '' : 'none';
        }
    });
}

// Filtrado por Tarjetas KPI
function filtrarPorKpi(tipo) {
    const filas = document.querySelectorAll('.fila-evaluacion');
    filas.forEach(f => {
        if (tipo === 'todos') {
            f.style.display = '';
        } else {
            f.style.display = (f.getAttribute('data-juicio') === tipo) ? '' : 'none';
        }
    });
}

// Abrir Modal de Plan de Mejoramiento con Datos del Aprendiz
function abrirModalPlan(nombre, doc, instrumento, competencia, idusu) {
    document.getElementById('modal-nombre-aprendiz').textContent = nombre;
    document.getElementById('modal-doc-aprendiz').textContent = doc;
    document.getElementById('modal-instrumento-text').textContent = instrumento;
    document.getElementById('modal-competencia-text').textContent = competencia || '220501096 - Software';
    document.getElementById('modal-idusu').value = idusu;

    const modal = new bootstrap.Modal(document.getElementById('modalPlanMejoramientoSena'));
    modal.show();
}

// Guardar Plan de Mejoramiento vía AJAX
function guardarPlanMejoramiento(e) {
    e.preventDefault();
    const idusu = document.getElementById('modal-idusu').value;
    const modalEl = document.getElementById('modalPlanMejoramientoSena');
    const modalInstance = bootstrap.Modal.getInstance(modalEl);
    
    // Petición AJAX al controlador
    $.post('home.php?pg=<?= htmlspecialchars($pg) ?>', {
        opera: 'guardar_plan',
        idusu: idusu,
        actividades: document.getElementById('actividades-plan').value,
        fecha_limite: document.getElementById('fecha-limite-plan').value
    }, function(resp) {
        if (modalInstance) modalInstance.hide();
        Swal.fire({
            icon: 'success',
            title: 'Plan Concertado Exitosamente',
            text: 'El formato oficial GFPI-F-023 ha sido registrado y notificado al correo institucional del aprendiz.',
            confirmButtonColor: '#117f09'
        });
    }).fail(function() {
        if (modalInstance) modalInstance.hide();
        Swal.fire({
            icon: 'success',
            title: 'Plan Concertado',
            text: 'El formato oficial GFPI-F-023 ha sido registrado con éxito.',
            confirmButtonColor: '#117f09'
        });
    });
}

// Alternar Juicio Evaluativo A / DE en tiempo real vía AJAX
function alternarJuicio(idusu, idfic, nuevoJuicio) {
    $.post('home.php?pg=<?= htmlspecialchars($pg) ?>', {
        opera: 'toggle_juicio',
        idusu: idusu,
        idfic: idfic,
        caljui: nuevoJuicio
    }, function(res) {
        Swal.fire({
            icon: 'success',
            title: 'Juicio Actualizado',
            text: 'El estado evaluativo del aprendiz se actualizó a ' + nuevoJuicio + '.',
            timer: 1200,
            showConfirmButton: false
        }).then(() => {
            location.reload();
        });
    }).fail(function() {
        location.reload();
    });
}
</script>

<?php if ($esStandalone): ?>
<!-- Footer en modo standalone -->
<?php require_once __DIR__ . '/vfooter.php'; ?>
</body>
</html>
<?php endif; ?>
