<?php
ini_set('memory_limit', '512M');
require_once __DIR__ . '/../models/conexion.php';
require_once __DIR__ . '/../models/etamcsi.php';

if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
} elseif (file_exists(__DIR__ . '/../dompdf/autoload.inc.php')) {
    require_once __DIR__ . '/../dompdf/autoload.inc.php';
}

use Dompdf\Dompdf;
use Dompdf\Options;

$mcsi = new Mcsi();
$idcon = $_GET['idcon'] ?? null;
$idaprendiz = $_GET['idaprendiz'] ?? null;
$idficha = $_GET['idficha'] ?? null;

if (empty($idcon) && empty($idaprendiz)) {
    die("Error: Debe especificar un ID de concertación o un aprendiz.");
}

$c = $mcsi->getOneConsentimientoOrAprendiz($idcon, $idaprendiz, $idficha);
if (!$c) {
    die("Error: No se encontraron datos del aprendiz para generar el formato.");
}

// Convertir logo a base64 para que Dompdf lo renderice sin problemas de ruta
$logoPath = __DIR__ . '/../img/logoSena.png';
$logoBase64 = '';
if (file_exists($logoPath)) {
    $logoData = file_get_contents($logoPath);
    $logoBase64 = 'data:image/png;base64,' . base64_encode($logoData);
}

// Determinar etiqueta del momento evaluativo
$tipoFormato = $_GET['tipo_formato'] ?? ($c['tipo_formato'] ?? 301);
$tipoNom = 'Momento 1: Planeación y Concertación de Actividades';
$detallesPdf = [];

if ($tipoFormato == 302) {
    $tipoNom = 'Momento 2: Evaluación Parcial de Seguimiento';
    if (!empty($c['idcon'])) {
        $detallesPdf = $mcsi->getDetallesConsertamiento($c['idcon'], 2);
    }
} elseif ($tipoFormato == 303) {
    $tipoNom = 'Momento 3: Evaluación Final (Juicio Evaluativo)';
}

$html = '
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>GFPI-F-023 ' . htmlspecialchars($c['nom_aprendiz']) . '</title>
    <style>
        @page { margin: 25px 30px; }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10px;
            color: #222;
            line-height: 1.3;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        th, td {
            border: 1px solid #333;
            padding: 4px 6px;
            vertical-align: middle;
        }
        .header-table td {
            text-align: center;
            font-weight: bold;
        }
        .header-table .logo-cell {
            width: 18%;
            padding: 2px;
        }
        .header-table .title-cell {
            width: 62%;
            font-size: 10.5px;
        }
        .header-table .meta-cell {
            width: 20%;
            font-size: 9px;
            text-align: left;
        }
        .section-header {
            background-color: #39a900;
            color: #ffffff;
            font-weight: bold;
            font-size: 10.5px;
            padding: 4px 8px;
            text-transform: uppercase;
        }
        .bg-sub {
            background-color: #f1f3f4;
            font-weight: bold;
        }
        .firmas-table {
            margin-top: 20px;
        }
        .firmas-table td {
            height: 70px;
            vertical-align: bottom;
            text-align: center;
            font-size: 9px;
        }
        .badge-firmado {
            color: #198754;
            font-weight: bold;
        }
        .badge-pendiente {
            color: #dc3545;
            font-weight: bold;
        }
    </style>
</head>
<body>

    <!-- ENCABEZADO OFICIAL SENA GFPI-F-023 -->
    <table class="header-table">
        <tr>
            <td class="logo-cell" rowspan="3">';
if ($logoBase64) {
    $html .= '<img src="' . $logoBase64 . '" style="max-height: 55px; max-width: 90px;">';
} else {
    $html .= '<span style="font-size:18px; color:#39a900; font-weight:bold;">SENA</span>';
}
$html .= '  </td>
            <td class="title-cell" rowspan="3">
                SERVICIO NACIONAL DE APRENDIZAJE SENA<br>
                GESTIÓN DE FORMACIÓN PROFESIONAL INTEGRAL<br>
                PROCEDIMIENTO DESARROLLO CURRICULAR<br>
                FORMATO EVALUACIÓN Y SEGUIMIENTO ETAPA PRODUCTIVA
            </td>
            <td class="meta-cell"><strong>Código:</strong> GFPI-F-023</td>
        </tr>
        <tr>
            <td class="meta-cell"><strong>Versión:</strong> 03</td>
        </tr>
        <tr>
            <td class="meta-cell"><strong>Fecha:</strong> ' . date('d/m/Y') . '</td>
        </tr>
    </table>

    <div style="text-align:center; font-weight:bold; font-size:11px; margin-bottom:6px; color:#1e395b;">
        ' . htmlspecialchars($tipoNom) . '
    </div>

    <!-- 1. INFORMACIÓN GENERAL -->
    <table>
        <tr>
            <td colspan="4" class="section-header">1. Información General</td>
        </tr>
        <tr>
            <td class="bg-sub" style="width:20%;">Regional:</td>
            <td style="width:30%;">Distrito Capital / Cundinamarca</td>
            <td class="bg-sub" style="width:20%;">Centro de Formación:</td>
            <td style="width:30%;">Centro de Desarrollo Agroempresarial (CDA)</td>
        </tr>
        <tr>
            <td class="bg-sub">Programa de Formación:</td>
            <td>' . htmlspecialchars($c['nom_programa'] ?? 'N/A') . '</td>
            <td class="bg-sub">No. de Ficha:</td>
            <td><strong>' . htmlspecialchars($c['idficha']) . '</strong></td>
        </tr>
    </table>

    <!-- DATOS DEL APRENDIZ -->
    <table>
        <tr>
            <td colspan="4" class="bg-sub" style="background-color:#e2f0d9;">Datos del Aprendiz</td>
        </tr>
        <tr>
            <td class="bg-sub" style="width:20%;">Nombre Completo:</td>
            <td style="width:30%;"><strong>' . htmlspecialchars($c['nom_aprendiz']) . '</strong></td>
            <td class="bg-sub" style="width:20%;">No. Documento:</td>
            <td style="width:30%;">' . htmlspecialchars($c['doc_aprendiz']) . '</td>
        </tr>
        <tr>
            <td class="bg-sub">Teléfono de Contacto:</td>
            <td>' . htmlspecialchars($c['tel_aprendiz'] ?? 'N/A') . '</td>
            <td class="bg-sub">Correo Electrónico:</td>
            <td>' . htmlspecialchars($c['correo_aprendiz'] ?? 'N/A') . '</td>
        </tr>
    </table>

    <!-- DATOS DE LA EMPRESA -->
    <table>
        <tr>
            <td colspan="4" class="bg-sub" style="background-color:#e2f0d9;">Datos del Ente Coformador (Empresa)</td>
        </tr>
        <tr>
            <td class="bg-sub" style="width:20%;">Razón Social / Empresa:</td>
            <td style="width:30%;"><strong>' . htmlspecialchars($c['nombre_empresa']) . '</strong></td>
            <td class="bg-sub" style="width:20%;">NIT:</td>
            <td style="width:30%;">' . htmlspecialchars($c['nit_empresa']) . '</td>
        </tr>
        <tr>
            <td class="bg-sub">Dirección Empresa:</td>
            <td>' . htmlspecialchars($c['direccion_empresa']) . '</td>
            <td class="bg-sub">Modalidad de Práctica:</td>
            <td>' . htmlspecialchars($c['modalidad']) . '</td>
        </tr>
        <tr>
            <td class="bg-sub">Jefe Inmediato / Tutor:</td>
            <td>' . htmlspecialchars($c['nombre_jefe']) . '</td>
            <td class="bg-sub">Cargo del Tutor:</td>
            <td>' . htmlspecialchars($c['cargo_jefe']) . '</td>
        </tr>
        <tr>
            <td class="bg-sub">Teléfono del Tutor:</td>
            <td>' . htmlspecialchars($c['telefono_jefe']) . '</td>
            <td class="bg-sub">Correo del Tutor:</td>
            <td>' . htmlspecialchars($c['correo_jefe']) . '</td>
        </tr>
        <tr>
            <td class="bg-sub">Aseguradora ARL:</td>
            <td>' . htmlspecialchars($c['nombre_arl']) . '</td>
            <td class="bg-sub">Nivel de Riesgo ARL:</td>
            <td>Clase ' . htmlspecialchars($c['nivel_riesgo_arl']) . '</td>
        </tr>
        <tr>
            <td class="bg-sub">Período de Ejecución:</td>
            <td colspan="3">
                Desde: <strong>' . htmlspecialchars($c['fecha_inicio']) . '</strong> 
                Hasta: <strong>' . htmlspecialchars($c['fecha_fin']) . '</strong>
                (Estado: <strong>' . htmlspecialchars($c['estado']) . '</strong>)
            </td>
        </tr>
    </table>

    <!-- 2. SECCIÓN EVALUATIVA SEGÚN EL MOMENTO -->
';

if ($tipoFormato == 302) {
    $fechaM2Prog = date('d/m/Y', strtotime(($c['fecha_inicio'] ?? date('Y-m-d')) . ' +3 months'));
    $fechaM2Real = !empty($c['fecha_momento2_real']) ? date('d/m/Y', strtotime($c['fecha_momento2_real'])) : 'Pendiente';

    $html .= '
    <table>
        <tr>
            <td colspan="4" class="section-header">2. Seguimiento y Evaluación Parcial de Etapa Productiva (Momento 2)</td>
        </tr>
        <tr>
            <td class="bg-sub" style="width:25%;">Fecha Inicio Concertada:</td>
            <td style="width:25%;">' . date('d/m/Y', strtotime($c['fecha_inicio'] ?? date('Y-m-d'))) . '</td>
            <td class="bg-sub" style="width:25%;">Fecha Programada (3 Meses):</td>
            <td style="width:25%;"><strong>' . $fechaM2Prog . '</strong></td>
        </tr>
        <tr>
            <td class="bg-sub">Fecha Real de Sesión:</td>
            <td><strong>' . $fechaM2Real . '</strong></td>
            <td class="bg-sub">Modalidad del Seguimiento:</td>
            <td>' . htmlspecialchars($c['modalidad'] ?? 'Presencial') . '</td>
        </tr>';
    if (!empty($c['enlace_grabacion'])) {
        $html .= '
        <tr>
            <td class="bg-sub">Enlace de Grabación:</td>
            <td colspan="3" style="word-break:break-all;">' . htmlspecialchars($c['enlace_grabacion']) . '</td>
        </tr>';
    }
    $html .= '
    </table>

    <!-- TABLA DE FACTORES EVALUADOS -->
    <table>
        <tr>
            <td colspan="3" class="section-header" style="background-color:#e2f0d9; color:#385723;">Valoración de Factores Técnicos y Comportamentales</td>
        </tr>
        <tr class="bg-sub" style="text-align:center;">
            <th style="width:40%;">Factor Evaluado</th>
            <th style="width:20%;">Valoración</th>
            <th style="width:40%;">Observaciones / Compromisos</th>
        </tr>';

    if (!empty($detallesPdf)) {
        foreach ($detallesPdf as $dp) {
            $esSat = ($dp['valoracion'] === 'Satisfactorio');
            $html .= '
            <tr>
                <td><strong>' . htmlspecialchars($dp['nomval']) . '</strong><br><small style="color:#666;">' . (((int)$dp['iddom'] === 31) ? 'Factor Técnico' : 'Factor Comportamental') . '</small></td>
                <td style="text-align:center; font-weight:bold; color:' . ($esSat ? '#28a745' : '#e67e22') . ';">
                    ' . ($esSat ? '✔ Satisfactorio' : '⚠ Por mejorar') . '
                </td>
                <td>' . (!empty($dp['observacion']) ? htmlspecialchars($dp['observacion']) : '<span style="color:#888;">Sin observaciones</span>') . '</td>
            </tr>';
        }
    } else {
        $html .= '
        <tr>
            <td colspan="3" style="text-align:center; padding:10px; color:#666;">
                <em>No se registran valoraciones individuales de factores para este momento evaluativo.</em>
            </td>
        </tr>';
    }

    $html .= '
    </table>

    <!-- OBSERVACIONES GENERALES MOMENTO 2 -->
    <table>
        <tr>
            <td class="section-header" style="background-color:#e2f0d9; color:#385723;">Observaciones Generales del Seguimiento Parcial</td>
        </tr>
        <tr>
            <td style="padding:8px;">
                ' . (!empty($c['obs_momento2']) ? nl2br(htmlspecialchars($c['obs_momento2'])) : '<span style="color:#888;">Sin observaciones generales registradas.</span>') . '
            </td>
        </tr>
    </table>';
} elseif ($tipoFormato == 303) {
    // ======================== MOMENTO 3: EVALUACIÓN FINAL ========================
    $fechaM3Real = !empty($c['fecha_momento3_real']) ? date('d/m/Y', strtotime($c['fecha_momento3_real'])) : 'Pendiente';
    $fechaFin3   = !empty($c['fecha_fin']) ? date('d/m/Y', strtotime($c['fecha_fin'])) : 'N/A';
    $numVisitas  = $c['num_visitas'] ?? 3;
    $juicio      = $c['juicio_evaluativo'] ?? 'PENDIENTE';
    $juicioColor = ($juicio === 'APROBADO') ? '#28a745' : (($juicio === 'NO APROBADO') ? '#dc3545' : '#888');
    $juicioIcon  = ($juicio === 'APROBADO') ? '&#10004; APROBADO' : (($juicio === 'NO APROBADO') ? '&#10008; NO APROBADO' : '&#9203; PENDIENTE');

    // Decodificar retroalimentación (obs_momento3 es JSON)
    $retroM3Pdf = [];
    if (!empty($c['obs_momento3'])) {
        $dec = json_decode($c['obs_momento3'], true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($dec)) {
            $retroM3Pdf = $dec;
        }
    }

    // Cargar variables evaluadas para M3 (momento=3, sin mezclar con el M2)
    if (!empty($c['idcon'])) {
        $detallesPdf = $mcsi->getDetallesConsertamiento($c['idcon'], 3);
    }

    $html .= '
    <table>
        <tr>
            <td colspan="4" class="section-header">2. Par&#225;metros de la Evaluaci&#243;n Final (Momento 3)</td>
        </tr>
        <tr>
            <td class="bg-sub" style="width:25%;">N&#176; de Visitas Realizadas:</td>
            <td style="width:25%;"><strong>' . htmlspecialchars($numVisitas) . ' visitas</strong></td>
            <td class="bg-sub" style="width:25%;">Fecha Inicio Etapa Productiva:</td>
            <td style="width:25%;">' . date('d/m/Y', strtotime($c['fecha_inicio'] ?? date('Y-m-d'))) . '</td>
        </tr>
        <tr>
            <td class="bg-sub">Fecha Fin Etapa Productiva:</td>
            <td><strong>' . $fechaFin3 . '</strong></td>
            <td class="bg-sub">Fecha Real Evaluaci&#243;n Final:</td>
            <td><strong>' . $fechaM3Real . '</strong></td>
        </tr>
        <tr>
            <td class="bg-sub">Modalidad de la Sesi&#243;n:</td>
            <td>' . htmlspecialchars($c['modalidad'] ?? 'Presencial') . '</td>
            <td class="bg-sub">Juicio de Evaluaci&#243;n:</td>
            <td style="font-weight:bold; color:' . $juicioColor . '; font-size:12px;">' . $juicioIcon . '</td>
        </tr>';
    if (!empty($c['enlace_grabacion'])) {
        $html .= '
        <tr>
            <td class="bg-sub">Enlace de Grabaci&#243;n:</td>
            <td colspan="3" style="word-break:break-all;">' . htmlspecialchars($c['enlace_grabacion']) . '</td>
        </tr>';
    }
    $html .= '
    </table>

    <!-- TABLA DE FACTORES EVALUADOS M3 -->
    <table>
        <tr>
            <td colspan="3" class="section-header" style="background-color:#e2f0d9; color:#385723;">3. Valoraci&#243;n de Factores de Desempe&#241;o (T&#233;cnicos y Comportamentales)</td>
        </tr>
        <tr class="bg-sub" style="text-align:center;">
            <th style="width:40%;">Factor Evaluado</th>
            <th style="width:20%;">Valoraci&#243;n</th>
            <th style="width:40%;">Observaciones / Compromisos</th>
        </tr>';

    if (!empty($detallesPdf)) {
        foreach ($detallesPdf as $dp) {
            $esSat = ($dp['valoracion'] === 'Satisfactorio');
            $html .= '
            <tr>
                <td><strong>' . htmlspecialchars($dp['nomval']) . '</strong><br><small style="color:#666;">' . (((int)$dp['iddom'] === 31) ? 'Factor T&#233;cnico' : 'Factor Comportamental') . '</small></td>
                <td style="text-align:center; font-weight:bold; color:' . ($esSat ? '#28a745' : '#e67e22') . ';">
                    ' . ($esSat ? '&#10004; Satisfactorio' : '&#9888; Por mejorar') . '
                </td>
                <td>' . (!empty($dp['observacion']) ? htmlspecialchars($dp['observacion']) : '<span style="color:#888;">Sin observaciones</span>') . '</td>
            </tr>';
        }
    } else {
        $html .= '
        <tr>
            <td colspan="3" style="text-align:center; padding:10px; color:#666;">
                <em>No se registran valoraciones de factores.</em>
            </td>
        </tr>';
    }

    $html .= '
    </table>

    <!-- RETROALIMENTACION DE LOS 3 ACTORES -->
    <table>
        <tr>
            <td colspan="2" class="section-header" style="background-color:#e2f0d9; color:#385723;">4. Retroalimentaci&#243;n de los Actores del Proceso</td>
        </tr>
        <tr class="bg-sub">
            <td style="width:30%; font-weight:bold;">Actor</td>
            <td style="font-weight:bold;">Retroalimentaci&#243;n y Compromisos</td>
        </tr>
        <tr>
            <td class="bg-sub" style="vertical-align:top; font-weight:bold;">Coformador (Empresa)<br><small style="font-weight:normal; color:#555;">Proceso y Competencias</small></td>
            <td style="vertical-align:top; padding:6px;">
                <strong>Proceso:</strong> ' . nl2br(htmlspecialchars($retroM3Pdf['coformador_proceso'] ?? 'Sin informaci&#243;n registrada.')) . '<br>
                <strong>Competencias:</strong> ' . nl2br(htmlspecialchars($retroM3Pdf['coformador_competencias'] ?? 'Sin informaci&#243;n registrada.')) . '
            </td>
        </tr>
        <tr>
            <td class="bg-sub" style="vertical-align:top; font-weight:bold;">Instructor de Seguimiento<br><small style="font-weight:normal; color:#555;">Proceso y Competencias</small></td>
            <td style="vertical-align:top; padding:6px;">
                <strong>Proceso:</strong> ' . nl2br(htmlspecialchars($retroM3Pdf['instructor_proceso'] ?? 'Sin informaci&#243;n registrada.')) . '<br>
                <strong>Competencias:</strong> ' . nl2br(htmlspecialchars($retroM3Pdf['instructor_competencias'] ?? 'Sin informaci&#243;n registrada.')) . '
            </td>
        </tr>
        <tr>
            <td class="bg-sub" style="vertical-align:top; font-weight:bold;">Aprendiz (Autoevaluaci&#243;n)<br><small style="font-weight:normal; color:#555;">Proceso y Competencias</small></td>
            <td style="vertical-align:top; padding:6px;">
                <strong>Proceso:</strong> ' . nl2br(htmlspecialchars($retroM3Pdf['aprendiz_proceso'] ?? 'Sin informaci&#243;n registrada.')) . '<br>
                <strong>Competencias:</strong> ' . nl2br(htmlspecialchars($retroM3Pdf['aprendiz_competencias'] ?? 'Sin informaci&#243;n registrada.')) . '
            </td>
        </tr>
        <tr>
            <td class="bg-sub" style="font-weight:bold;">Reconocimientos Especiales</td>
            <td style="padding:6px;">' . nl2br(htmlspecialchars($retroM3Pdf['reconocimientos'] ?? 'Ninguno.')) . '</td>
        </tr>
    </table>

    <!-- JUICIO EVALUATIVO DESTACADO -->
    <table>
        <tr>
            <td colspan="2" class="section-header">5. Juicio de Evaluaci&#243;n de la Etapa Productiva</td>
        </tr>
        <tr>
            <td style="width:40%; text-align:center; font-size:18px; font-weight:bold; padding:18px; color:' . $juicioColor . '; border-right:2px solid #333;">
                ' . $juicioIcon . '
            </td>
            <td style="vertical-align:middle; font-size:9.5px; padding:10px;">
                El presente juicio evaluativo certifica que el aprendiz <strong>' . htmlspecialchars($c['nom_aprendiz']) . '</strong>
                ha <strong>' . ($juicio === 'APROBADO' ? 'cumplido satisfactoriamente' : 'no logrado completar') . '</strong>
                los compromisos y competencias de la Etapa Productiva seg&#250;n el formato GFPI-F-023.
            </td>
        </tr>
    </table>';
} else {
    $html .= '
    <!-- 2. PLANEACION DE ETAPA PRODUCTIVA (CONCERTACION DE ACTIVIDADES) -->
    <table>
        <tr>
            <td colspan="3" class="section-header">2. Planeaci&#243;n de Etapa Productiva (Concertaci&#243;n de Actividades)</td>
        </tr>
        <tr class="bg-sub" style="text-align:center;">
            <th style="width:50%;">Actividades a Desarrollar<br><small style="font-weight:normal;">(&#191;Qu&#233; tiene que hacer y c&#243;mo lo tiene que hacer?)</small></th>
            <th style="width:35%;">Evidencias de Aprendizaje y Cronograma<br><small style="font-weight:normal;">(&#191;Cu&#225;ndo entrega y qu&#233; evidencias?)</small></th>
            <th style="width:15%;">Lugar de Ejecuci&#243;n</th>
        </tr>
        <tr>
            <td style="padding:10px; vertical-align:top;">
                ' . nl2br(htmlspecialchars($c['tareas_acordadas'])) . '
            </td>
            <td style="padding:10px; vertical-align:top;">
                ' . nl2br(htmlspecialchars($c['fechas_entrega'])) . '
            </td>
            <td style="padding:10px; vertical-align:top; text-align:center;">
                ' . htmlspecialchars($c['direccion_empresa']) . '<br>
                <small>(' . htmlspecialchars($c['modalidad']) . ')</small>
            </td>
        </tr>
    </table>';
}

$html .= '
    <!-- 3. FIRMAS TRIPARTITAS DEL ACUERDO -->
    <table class="firmas-table">
        <tr>
            <td colspan="3" class="section-header">3. Firmas Tripartitas del Acuerdo de Concertación</td>
        </tr>
        <tr>
            <td style="width:33.3%;">
                <div style="border-bottom:1px solid #000; margin: 0 10px 5px 10px;"></div>
                <strong>' . htmlspecialchars($c['nom_aprendiz']) . '</strong><br>
                Firma del Aprendiz<br>
                Doc: ' . htmlspecialchars($c['doc_aprendiz']) . '<br>
                <span class="' . (($c['firma_aprendiz'] ?? 'Pendiente') === 'Firmado' ? 'badge-firmado' : 'badge-pendiente') . '">
                    ' . (($c['firma_aprendiz'] ?? 'Pendiente') === 'Firmado' ? '✔ Firmado Digitalmente' : '⏳ Pendiente de Firma') . '
                </span>
            </td>
            <td style="width:33.3%;">
                <div style="border-bottom:1px solid #000; margin: 0 10px 5px 10px;"></div>
                <strong>' . htmlspecialchars($c['nombre_jefe']) . '</strong><br>
                Firma del Ente Coformador (Empresa)<br>
                Cargo: ' . htmlspecialchars($c['cargo_jefe']) . '<br>
                <span class="' . (($c['firma_empresa'] ?? 'Pendiente') === 'Firmado' ? 'badge-firmado' : 'badge-pendiente') . '">
                    ' . (($c['firma_empresa'] ?? 'Pendiente') === 'Firmado' ? '✔ Firmado Digitalmente' : '⏳ Pendiente de Firma') . '
                </span>
            </td>
            <td style="width:33.3%;">
                <div style="border-bottom:1px solid #000; margin: 0 10px 5px 10px;"></div>
                <strong>' . htmlspecialchars($c['nom_instructor'] ?? 'Instructor de Seguimiento') . '</strong><br>
                Firma del Instructor SENA<br>
                Doc: ' . htmlspecialchars($c['doc_instructor'] ?? 'SENA') . '<br>
                <span class="badge-firmado">
                    ✔ Firmado Digitalmente
                </span>
            </td>
        </tr>
    </table>

</body>
</html>';

$options = new Options();
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'Arial');

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$filename = "GFPI-F-023_" . preg_replace('/[^A-Za-z0-9_]/', '', $c['idficha']) . "_" . preg_replace('/[^A-Za-z0-9_]/', '', $c['doc_aprendiz']) . ".pdf";
$dompdf->stream($filename, ["Attachment" => false]);
?>
