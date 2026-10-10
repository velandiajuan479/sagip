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

$idllam = isset($_GET['idllam']) ? (int)$_GET['idllam'] : 0;
$dato = $desmacfh->getOneLlamado($idllam);
if (!$dato) {
    die("Llamado de atención no encontrado.");
}

$tipoLlam = ($dato['tipllam'] == 1) ? 'VERBAL' : 'ESCRITO';
$colorTipo = ($dato['tipllam'] == 1) ? '#b45309' : '#b91c1c';
$fechaLlam = date('d/m/Y', strtotime($dato['fecllam']));

$html = '
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Llamado de Atención ' . $tipoLlam . '</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: DejaVu Sans, sans-serif; color: #1e293b; font-size: 11px; line-height: 1.5; }
        .header { text-align: center; border-bottom: 3px solid ' . $colorTipo . '; padding-bottom: 12px; margin-bottom: 18px; }
        .header h1 { font-size: 17px; color: #0f172a; margin-bottom: 4px; }
        .header h2 { font-size: 14px; color: ' . $colorTipo . '; margin-top: 2px; text-transform: uppercase; letter-spacing: 2px; }
        .header p { font-size: 10px; color: #64748b; }
        .tipo-badge { display: inline-block; background: ' . $colorTipo . '; color: #ffffff; padding: 3px 14px; border-radius: 4px; font-size: 12px; font-weight: bold; margin-bottom: 4px; }
        table.datos { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
        table.datos td { border: 1px solid #cbd5e1; padding: 7px 9px; }
        table.datos .lbl { width: 32%; font-weight: bold; background: #f8fafc; }
        .obs { border: 1px solid #cbd5e1; border-left: 4px solid ' . $colorTipo . '; padding: 10px 12px; margin: 10px 0 20px; }
        .obs h3 { font-size: 12px; margin-bottom: 6px; color: #0f172a; }
        .firmas { width: 100%; margin-top: 48px; }
        .firmas td { width: 33%; text-align: center; }
        .firmas .linea { border-top: 1px solid #333; padding-top: 6px; font-weight: bold; }
        .firmas .rol { color: #64748b; font-size: 9px; }
        .footer { text-align: center; color: #94a3b8; font-size: 8px; margin-top: 30px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>SAGI - Sistema de Gestión de la Formación</h1>
        <span class="tipo-badge">LLAMADO DE ATENCIÓN ' . $tipoLlam . '</span>
        <h2>Documento de Llamado de Atención</h2>
        <p>SENA Centro de Desarrollo Agroempresarial CDA Chía</p>
    </div>

    <table class="datos">
        <tr>
            <td class="lbl">Aprendiz:</td>
            <td>' . htmlspecialchars($dato['aprendiz'] ?? '') . '</td>
            <td class="lbl">Documento:</td>
            <td>' . htmlspecialchars($dato['ndocusu'] ?? '') . '</td>
        </tr>
        <tr>
            <td class="lbl">Correo Institucional:</td>
            <td>' . htmlspecialchars($dato['emausu'] ?? '') . '</td>
            <td class="lbl">Fecha del Llamado:</td>
            <td>' . $fechaLlam . '</td>
        </tr>
        <tr>
            <td class="lbl">Tipo de Llamado:</td>
            <td><strong>' . $tipoLlam . '</strong></td>
            <td class="lbl">Instructor / Registra:</td>
            <td>' . htmlspecialchars($dato['instructor'] ?? 'Instructor') . '</td>
        </tr>
    </table>

    <div class="obs">
        <h3>Observaciones del Llamado:</h3>
        <p>' . nl2br(htmlspecialchars($dato['obsllam'] ?? '')) . '</p>
    </div>

    <table class="firmas">
        <tr>
            <td>
                <div class="linea"></div>
                <div class="rol">Aprendiz</div>
            </td>
            <td>
                <div class="linea"></div>
                <div class="rol">Instructor / Enlace</div>
            </td>
            <td>
                <div class="linea"></div>
                <div class="rol">Coordinación</div>
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
$dompdf->stream('llamado_atencion_' . $idllam . '_' . $tipoLlam . '.pdf', ['Attachment' => false]);