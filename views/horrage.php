<?php
require_once __DIR__ . '/../models/conexion.php';
require_once __DIR__ . '/../models/mrpage.php';
require_once __DIR__ . '/../models/hormacceso.php';

date_default_timezone_set('America/Bogota');

// Alcance por rol: la ficha solicitada debe estar permitida al usuario;
// si no, se usa su primera ficha asignada (admin ve todas).
$hacceso = new HormAcceso();
$scopeCtx = $hacceso->getContexto();
$scopeFichas = $hacceso->getFichasPermitidas($scopeCtx);

// Controlador
$idFicha = isset($_REQUEST['fic']) ? $_REQUEST['fic'] : NULL;
if (!$hacceso->puedeVerFicha($idFicha, $scopeCtx)) {
    $idFicha = !empty($scopeFichas) ? $scopeFichas[0]['idfic'] : NULL;
}
$mrpage = new Mrpage();

$datcen = $mrpage->getCent();
$datfic = $mrpage->getFic($idFicha);
$datres = $mrpage->getRes($idFicha);
$datage = $mrpage->getAge($idFicha);
$ddi = $mrpage->getDia();

$dtNapr = 0;
$dtfh = NULL;
if ($datfic) {
    $dtfh = $datfic[0];
    $idFic = $dtfh['idfic'];
    $dtNapr = $mrpage->getNoApr($idFic);
    $dtNapr = isset($dtNapr[0]['can']) ? $dtNapr[0]['can'] : 0;
}

$html = "";

// Estructura HTML
$html .= '<!DOCTYPE html>';
$html .= '<html lang="en">';
$html .= '<head>';
$html .= '<meta charset="UTF-8">';
$html .= '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
$html .= '<title>REPORTE DE AGENDA</title>';
$html .= '<style>';
$html .= '* { margin: 0; padding: 5px; text-align: center; font-size: 10px; font-family: Arial, Helvetica, sans-serif; }';
$html .= 'table { width: 100%; margin: auto; }';
$html .= 'table, th, td { border: 1px solid; border-collapse: collapse; }';
$html .= 'img { height: 50px; width: 50px; }';
$html .= '.version, .gpf { max-width: 30px; }';
$html .= '.pri { text-align: left; height: 10px; }';
$html .= '.dato { height: 20px; }';
$html .= 'input { width: 100%; }';
$html .= '</style>';
$html .= '</head>';
$html .= '<body>';

$html .= '<table>';
$html .= '<tr>';
$html .= '<td>';
$html .= '<img src="img/sena.png" alt="logoSena">';
if ($datcen) {
    foreach ($datcen as $dtct) {
        $html .= '<h1>' . $dtct['nomcen'] . '</h1>';
        $html .= '<h5>' . $dtct['dircen'] . '</h5>';
    }
}
$html .= '</td>';
$html .= '</tr>';
$html .= '</table>';

$html .= '<table>';
$html .= '<tr><td colspan="3">AGENDA PROGRAMACIÓN DE ACCIONES DE FORMACIÓN</td></tr>';
$html .= '<tr>';
$html .= '<td>';
if ($datfic) {
    $html .= 'Ciudad y fecha:<br>' . $dtfh['mun'] . ', ' . date('d/m/Y') . '<br>';
}
$html .= '</td>';
$html .= '<td>Hora de impresión<br>' . date('H:i') . '</td>';
$html .= '</tr>';
$html .= '</table>';

// Información de la ficha
$html .= '<table>';
$html .= '<tr>';
if ($datfic) {
    $html .= '<th colspan="3">FICHA:</th>';
    $html .= '<td colspan="3">' . $dtfh['idfic'] . '</td>';
    $html .= '<th colspan="3">PROGRAMA:</th>';
    $html .= '<td colspan="4">' . $dtfh['nomfic'] . '</td>';
    $html .= '<th colspan="3">MUNICIPIO:</th>';
    $html .= '<td colspan="2">' . $dtfh['mun'] . '</td>';
    $html .= '<th colspan="3">JORNADA</th>';
    $html .= '<td colspan="2">' . $dtfh['nomval'] . '</td>';
    $html .= '<th colspan="3">No. APRENDICES</th>';
    $html .= '<td colspan="1">' . $dtNapr . '</td>';
}
$html .= '</tr>';
$html .= '</table>';

// Encabezado de la tabla de resultados
$html .= '<table>';
$html .= '<tr>';
$html .= '<th colspan="3">FASE</th>';
$html .= '<th colspan="3">COMPETENCIA</th>';
$html .= '<th colspan="4">RESULTADO DE APRENDIZAJE</th>';
$html .= '<th colspan="1">HORAS COMPETENCIA</th>';
$html .= '<th colspan="1">HORAS RAP</th>';
$html .= '<th colspan="3">INSTRUCTOR:</th>';
$html .= '<th colspan="1">Lunes</th>';
$html .= '<th colspan="1">Martes</th>';
$html .= '<th colspan="1">Miercoles</th>';
$html .= '<th colspan="1">Jueves</th>';
$html .= '<th colspan="1">Viernes</th>';
$html .= '<th colspan="1">Sabado</th>';
$html .= '<th colspan="1">Domingo</th>';
$html .= '<th colspan="2">INICIO</th>';
$html .= '<th colspan="2">FIN</th>';
$html .= '</tr>';

/// Filas de resultados de aprendizaje
if ($datres) {
    foreach ($datres as $dtres) {
        $html .= '<tr>';
        $html .= '<td colspan="3">EJECUCIÓN</td>';
        $html .= '<td colspan="3">' . $dtres['descom'] . '</td>';
        $html .= '<td colspan="4">' . $dtres['nomres'] . '</td>';
        $html .= '<td>' . $dtres['horcom'] . '</td>';
        $html .= '<td>30</td>';

        // Asumimos que solo hay un instructor por cada resultado de aprendizaje
        if ($datage) {
            $dtusu = $datage[0]; // Tomar el primer instructor (ajustar según lógica de negocio)
            $html .= '<td colspan="3">' . $dtusu['nomusu'] . '</td>';
        }

        // Días de la semana
        if ($ddi) {
            foreach ($ddi as $d) {
                $html .= '<td>';
                $mrpage->setIddia($d["idval"]);
                $mrpage->setIdfic($dtfh["idfic"]);
                $dat = $mrpage->getAll();
                if ($dat) {
                    foreach ($dat as $dti) {
                        $html .= '<b>X</b>';
                    }
                }
                $html .= '</td>';
            }
        }

        // Fechas de inicio y fin
        if (!empty($datage)) {
            $fchinc = '';
            $fchfnl = '';
            foreach ($datage as $dtage) {
                if ($dtage['idfic'] === $dtfh['idfic']) {
                    $fchinc = $dtage['fchinc'];
                    $fchfnl = $dtage['fchfnl'];
                    break; // Break after finding the first match to avoid duplicates
                }
            }
            $html .= '<td colspan="2">' . $fchinc . '</td>';
            $html .= '<td colspan="2">' . $fchfnl . '</td>';
        } else {
            $html .= '<td colspan="2"></td>';
            $html .= '<td colspan="2"></td>';
        }

        $html .= '</tr>';
    }
}

if ($datres) {
    foreach ($datres as $dtres) {
        $html .= '<tr>';
        $html .= '<td colspan="3">EVALUACIÓN</td>';
        $html .= '<td colspan="3">' . $dtres['descom'] . '</td>';
        $html .= '<td colspan="4">' . $dtres['nomres'] . '</td>';
        $html .= '<td>' . $dtres['horcom'] . '</td>';
        $html .= '<td>10</td>';

        // Asumimos que solo hay un instructor por cada resultado de aprendizaje
        if ($datage) {
            $dtusu = $datage[0]; // Tomar el primer instructor (ajustar según lógica de negocio)
            $html .= '<td colspan="3">' . $dtusu['nomusu'] . '</td>';
        }

        // Días de la semana
        if ($ddi) {
            foreach ($ddi as $d) {
                $html .= '<td>';
                $mrpage->setIddia($d["idval"]);
                $mrpage->setIdfic($dtfh["idfic"]);
                $dat = $mrpage->getAll();
                if ($dat) {
                    foreach ($dat as $dti) {
                        $html .= '<b>X</b>';
                    }
                }
                $html .= '</td>';
            }
        }

        // Fechas de inicio y fin
        if (!empty($datage)) {
            $fchinc = '';
            $fchfnl = '';
            foreach ($datage as $dtage) {
                if ($dtage['idfic'] === $dtfh['idfic']) {
                    $fchinc = $dtage['fchinc'];
                    $fchfnl = $dtage['fchfnl'];
                    break; // Break after finding the first match to avoid duplicates
                }
            }
            $html .= '<td colspan="2">' . $fchinc . '</td>';
            $html .= '<td colspan="2">' . $fchfnl . '</td>';
        } else {
            $html .= '<td colspan="2"></td>';
            $html .= '<td colspan="2"></td>';
        }

        $html .= '</tr>';
    }
}
$html .= '</table>';
$html .= '</body>';
$html .= '</html>';

echo $html;
echo "<script>window.print();</script>";
?>