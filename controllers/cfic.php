<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once(__DIR__ . '/../models/mfic.php');

$mfic = new Mfic();
$idfic = isset($_REQUEST['idfic']) ? $_REQUEST['idfic'] : NULL;
$nomfic = isset($_POST['nomfic']) ? $_POST['nomfic'] : NULL;
$jornada = isset($_POST['jornada']) ? $_POST['jornada'] : NULL;
$mun = isset($_POST['mun']) ? $_POST['mun'] : NULL;
$idcen = isset($_POST['idcen']) ? $_POST['idcen'] : NULL;
$finific = isset($_POST['finific']) ? $_POST['finific'] : NULL;
$ffinfic = isset($_POST['ffinfic']) ? $_POST['ffinfic'] : NULL;
$opera = isset($_REQUEST['opera']) ? $_REQUEST['opera'] : NULL;
$idusu = isset($_REQUEST['idusu']) ? $_REQUEST['idusu'] : ($_SESSION["idusu"] ?? null);
$datOne = NULL;
$pg = 1210;

$idfic = !empty($idfic) ? trim(str_replace(" ", "", $idfic)) : null;

$mfic->setIdfic($idfic);

// Insertar 
if ($opera == "Insertar") {
    if (!empty($idfic) && !empty($nomfic) && !empty($jornada) && !empty($idcen)) {
        $mfic->setIdfic($idfic);
        $mfic->setNomfic($nomfic);
        $mfic->setJornada($jornada);
        $mfic->setIdcen($idcen);
        $mfic->setMun($mun);
        $mfic->setFinific($finific);
        $mfic->setFfinfic($ffinfic);
        $mfic->ins();
        $idfic = NULL;
    }
}

// Actualizar
if ($opera == "Actualizar") {
    if (!empty($idfic) && !empty($nomfic) && !empty($jornada) && !empty($idcen)) {
        $mfic->setIdfic($idfic);
        $mfic->setNomfic($nomfic);
        $mfic->setJornada($jornada);
        $mfic->setIdcen($idcen);
        $mfic->setMun($mun);
        $mfic->setFinific($finific);
        $mfic->setFfinfic($ffinfic);
        $mfic->upd();
        $idfic = NULL;
    }
}

// Eliminar
if ($opera == "Eliminar") {
    if (!empty($idfic)) {
        $mfic->del();
    }
}

// Mostrar datos
$dat = $mfic->selAll();
$dce = $mfic->getCen();
$djo = $mfic->getJor();
$dubi = $mfic->getUbica();
if ($idfic) {
    $datOne = $mfic->selOne();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ope']) && $_POST['ope'] === 'InAct') {
    $idfic = $_POST['idfic'] ?? null;
    $idusu = $_POST['coorusu'] ?? null;
    $numact = $_POST['numacta'] ?? null;

    if ($idfic && $idusu && $numact) {
        $idactdc = $mfic->insertarActaCierre($idusu, $idfic, $numact);
    }
}

function modActCierreModal($fic, $nomfic, $feini, $fefin, $pg) {
    $html = '';
    $html .= '<div class="modal fade" id="CrearActModal' . $fic . '" tabindex="-1" role="dialog" aria-hidden="true">';
    $html .= '<div class="modal-dialog modal-dialog-centered">';
    $html .= '<form action="home.php?pg=' . $pg . '" method="POST">';
    $html .= '<div class="modal-content shadow border-0 rounded-3">';
    $html .= '<div class="modal-header bg-success text-white">';
    $html .= '<h5 class="modal-title fw-bold"><i class="fa-solid fa-file-contract me-2"></i>Acta de Cierre</h5>';
    $html .= '<button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>';
    $html .= '</div>';
    $html .= '<div class="modal-body p-4">';
    $html .= '<div class="p-3 mb-3 bg-light rounded-3 border-start border-4 border-success">';
    $html .= '<div class="fw-bold text-dark fs-6">Ficha: ' . htmlspecialchars($fic) . ' - ' . htmlspecialchars($nomfic) . '</div>';
    $html .= '<small class="text-muted"><i class="fa-regular fa-calendar-days me-1"></i>Vigencia: ' . htmlspecialchars($feini) . ' a ' . htmlspecialchars($fefin) . '</small>';
    $html .= '</div>';

    $html .= '<div class="row g-3">';
    $html .= '<div class="col-12">';
    $html .= '<label for="conact" class="form-label fw-semibold text-dark small mb-1">Conclusiones</label>';
    $html .= '<textarea name="conact" id="conact" class="form-control" rows="3" placeholder="Ingrese las conclusiones del acta..."></textarea>';
    $html .= '</div>';

    $html .= '<div class="col-12">';
    $html .= '<label for="coorusu" class="form-label fw-semibold text-dark small mb-1">Coordinador</label>';
    $html .= '<select name="coorusu" id="coorusu" class="form-select">';
    $html .= '<option value="1">Robin</option>';
    $html .= '</select>';
    $html .= '</div>';

    // === Actas por trimestres ===
    $feiniClean = explode(" ", $feini ?? '')[0];
    $fefinClean = explode(" ", $fefin ?? '')[0];
    $inicio = !empty($feiniClean) ? new DateTime($feiniClean) : new DateTime(); 
    $fin = !empty($fefinClean) ? new DateTime($fefinClean) : (new DateTime())->modify('+1 year');
    $hoy = new DateTime();

    $html .= '<div class="col-12">';
    $html .= '<label for="numacta" class="form-label fw-semibold text-dark small mb-1">Seleccione Acta</label>';
    $html .= '<select name="numacta" id="numacta" class="form-select">';

    $actaNum = 1;
    $temp = clone $inicio;

    while ($temp < $fin) {
        $limite = (clone $temp)->add(new DateInterval('P3M'));

        // Detectar si es el último trimestre
        if ($limite >= $fin) {
            $texto = "Acta de Cierre (" . $temp->format("Y-m-d") . " a " . $fin->format("Y-m-d") . ")";

            // Solo disponible si ya pasó la fecha de fin de la ficha
            if ($hoy < $fin) {
                $html .= '<option value="cierre" disabled>' . $texto . ' (No disponible)</option>';
            } else {
                $html .= '<option value="cierre">' . $texto . '</option>';
            }
        } else {
            $texto = "Acta $actaNum (" . $temp->format("Y-m-d") . " a " . $limite->format("Y-m-d") . ")";

            // Solo disponible si ya pasó el trimestre
            if ($hoy < $limite) {
                $html .= '<option value="' . $actaNum . '" disabled>' . $texto . ' (No disponible)</option>';
            } else {
                $html .= '<option value="' . $actaNum . '">' . $texto . '</option>';
            }
        }

        $temp = $limite; 
        $actaNum++;
    }

    $html .= '</select>';
    $html .= '</div>'; // cierre select

    $html .= '</div>'; // cierre row
    $html .= '</div>'; // cierre body

    $html .= '<div class="modal-footer bg-light px-4 py-3">';
    $html .= '<button type="button" class="btn btn-secondary px-4 rounded-pill" data-bs-dismiss="modal">Cerrar</button>';
    $html .= '<button type="submit" class="btn btn-success px-4 rounded-pill fw-bold">Guardar</button>';
    $html .= '<input type="hidden" name="idfic" value="' . htmlspecialchars($fic) . '">';
    $html .= '<input type="hidden" name="ope" value="InAct">';
    $html .= '</div>';

    $html .= '</div>'; 
    $html .= '</form>';
    $html .= '</div>'; 
    $html .= '</div>'; 

    return $html;
}
?>