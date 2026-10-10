<?php
require_once(__DIR__ . '/../models/desmacfh.php');

// Cargador mínimo de dompdf (compatible con PHP 8.0 de XAMPP). Se evita
// vendor/autoload.php porque su platform_check exige PHP >= 8.1 (de otra dependencia).
spl_autoload_register(function ($class) {
    $raiz = __DIR__ . '/../vendor';
    if ($class === 'Dompdf\\Cpdf') {
        $archivo = $raiz . '/dompdf/dompdf/lib/Cpdf.php';
        if (file_exists($archivo)) { require $archivo; }
        return;
    }
    $prefijos = [
        'Dompdf\\' => '/dompdf/dompdf/src/',
        'FontLib\\' => '/phenx/php-font-lib/src/FontLib/',
        'Svg\\' => '/phenx/php-svg-lib/src/Svg/',
    ];
    foreach ($prefijos as $p => $dir) {
        if (strncmp($class, $p, strlen($p)) === 0) {
            $archivo = $raiz . $dir . str_replace('\\', '/', substr($class, strlen($p))) . '.php';
            if (file_exists($archivo)) { require $archivo; }
            return;
        }
    }
});

use Dompdf\Dompdf;
use Dompdf\Options;

$desmacfh = new Desmacfh();

$idactdc = isset($_GET['idactdc']) ? (int)$_GET['idactdc'] : 0;
$idfic = isset($_GET['idfic']) ? trim($_GET['idfic']) : '';

$acta = $desmacfh->getOneActa($idactdc);
if (!$acta || ($idfic !== '' && (string)$acta['idfic'] !== (string)$idfic)) {
    die("Acta de cierre no encontrada.");
}

$ficha = $desmacfh->getFicha($acta['idfic']);
$aprendices = $desmacfh->getAprendicesFicha($acta['idfic']);

// Conclusiones y coordinador responsable (acta/actdec)
$conclusiones = $desmacfh->getConclusionesActa($acta['numact'], $acta['idusu']);
$coordNombre = null;
if (!empty($conclusiones)) {
    $coordNombre = $desmacfh->getNomUsu($conclusiones[0]['coorusu']);
}
if (!$coordNombre) {
    $coordNombre = $desmacfh->getNomUsu($acta['idusu']);
}

// Rango del trimestre según el número de acta
$rango = '';
if ($ficha && !empty($ficha['finific']) && !empty($ficha['ffinfic'])) {
    foreach ($desmacfh->calcularActasPorFecha($ficha['finific'], $ficha['ffinfic'], $ficha['idfic']) as $ac) {
        if ((string)$ac['numact'] === (string)$acta['numact']) {
            $rango = $ac['rango'];
            break;
        }
    }
}

$tituloActa = ($acta['numact'] == 999) ? 'Acta de Cierre Final' : 'Acta Trimestral #' . $acta['numact'];
$fechaAct = date('d/m/Y', strtotime($acta['fecregdec']));
$nomCentro = $ficha['nomcen'] ?? 'Centro de Desarrollo Agroempresarial CDA Chía';
$nomJornada = $ficha['nomval'] ?? 'Jornada no especificada';

$html = '
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>' . htmlspecialchars($tituloActa) . '</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: DejaVu Sans, sans-serif; color: #1e293b; font-size: 11px; line-height: 1.5; }
        .header { text-align: center; border-bottom: 3px solid #39a900; padding-bottom: 12px; margin-bottom: 18px; }
        .header h1 { font-size: 17px; color: #0f172a; margin-bottom: 4px; }
        .header h2 { font-size: 14px; color: #39a900; margin-top: 2px; }
        .header p { font-size: 10px; color: #64748b; }
        .datos { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
        .datos td { border: 1px solid #cbd5e1; padding: 6px 8px; }
        .datos .lbl { width: 32%; font-weight: bold; background: #f8fafc; }
        table.aprendices { width: 100%; border-collapse: collapse; margin: 10px 0 18px; }
        table.aprendices th { background: #39a900; color: #ffffff; padding: 6px 8px; text-align: left; font-size: 10px; }
        table.aprendices td { border: 1px solid #cbd5e1; padding: 5px 8px; }
        h3 { font-size: 13px; color: #0f172a; margin: 14px 0 6px; padding-left: 6px; border-left: 4px solid #39a900; }
        ul.concl { padding-left: 22px; }
        ul.concl li { margin-bottom: 4px; }
        .firmas { width: 100%; margin-top: 46px; }
        .firmas td { width: 50%; text-align: center; }
        .firmas .linea { border-top: 1px solid #333; padding-top: 6px; font-weight: bold; }
        .firmas .rol { color: #64748b; font-size: 9px; }
        .footer { text-align: center; color: #94a3b8; font-size: 8px; margin-top: 30px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>SAGI - Sistema de Gestión de la Formación</h1>
        <h2>' . htmlspecialchars($tituloActa) . '</h2>
        <p>SENA Centro de Desarrollo Agroempresarial CDA Chía</p>
    </div>

    <table class="datos">
        <tr>
            <td class="lbl">Ficha:</td>
            <td>' . htmlspecialchars($ficha['idfic'] ?? $acta['idfic']) . '</td>
            <td class="lbl">Fecha de Acta:</td>
            <td>' . $fechaAct . '</td>
        </tr>
        <tr>
            <td class="lbl">Programa:</td>
            <td colspan="3">' . htmlspecialchars($ficha['nomfic'] ?? '') . '</td>
        </tr>
        <tr>
            <td class="lbl">Jornada:</td>
            <td>' . htmlspecialchars($nomJornada) . '</td>
            <td class="lbl">Centro:</td>
            <td>' . htmlspecialchars($nomCentro) . '</td>
        </tr>
        <tr>
            <td class="lbl">Período Evaluado:</td>
            <td colspan="3">' . htmlspecialchars($rango) . '</td>
        </tr>
        <tr>
            <td class="lbl">Coordinador Responsable:</td>
            <td colspan="3">' . htmlspecialchars($coordNombre ?? 'Coordinación') . '</td>
        </tr>
    </table>

    <h3>1. Aprendices de la Ficha</h3>
    <table class="aprendices">
        <thead>
            <tr>
                <th style="width: 8%;">N°</th>
                <th style="width: 25%;">Identificación</th>
                <th>Nombre Completo</th>
            </tr>
        </thead>
        <tbody>';

if (!empty($aprendices)) {
    $i = 1;
    foreach ($aprendices as $ap) {
        $html .= '
        <tr>
            <td>' . $i++ . '</td>
            <td>' . htmlspecialchars($ap['ndocusu']) . '</td>
            <td>' . htmlspecialchars($ap['nomusu']) . '</td>
        </tr>';
    }
} else {
    $html .= '<tr><td colspan="3" style="text-align:center; color:#64748b;">No hay aprendices activos registrados en la ficha.</td></tr>';
}

$html .= '
        </tbody>
    </table>

    <h3>2. Conclusiones y Compromisos</h3>';

if (!empty($conclusiones)) {
    $html .= '<ul class="concl">';
    foreach ($conclusiones as $c) {
        $html .= '<li>' . htmlspecialchars($c['desacde']) . ' <em style="color:#64748b;">(' . date('d/m/Y', strtotime($c['fecacde'])) . ')</em></li>';
    }
    $html .= '</ul>';
} else {
    $html .= '<p style="color:#64748b;">Sin observaciones registradas para este período.</p>';
}

$html .= '
    <table class="firmas">
        <tr>
            <td>
                <div class="linea">' . htmlspecialchars($coordNombre ?? 'Coordinación') . '</div>
                <div class="rol">Coordinador Responsable</div>
            </td>
            <td>
                <div class="linea"></div>
                <div class="rol">Instructor / Enlace</div>
            </td>
        </tr>
    </table>

    <div class="footer">Documento generado por SAGI - SENA CDA Chía el ' . date('d/m/Y H:i') . '</div>
</body>
</html>';

$opciones = new Options();
$opciones->set('isRemoteEnabled', false);
$dompdf = new Dompdf($opciones);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream('acta_' . $acta['idfic'] . '_' . $acta['numact'] . '.pdf', ['Attachment' => false]);