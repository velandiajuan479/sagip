<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    require_once(__DIR__ . '/../models/conexion.php');
    require_once(__DIR__ . '/../models/usumfic.php');

    $mfic = new Usumfic();
    $idfic = isset($_REQUEST['idfic']) ?$_REQUEST['idfic']:NULL;
    $nomfic = isset($_POST['nomfic']) ?$_POST['nomfic']:NULL;
    $jornada = isset($_POST['jornada']) ?$_POST['jornada']:NULL;
    $mun = isset($_POST['mun']) ?$_POST['mun']:NULL;
    $idcen = isset($_POST['idcen']) ?$_POST['idcen']:NULL;
    $finific= isset($_POST['finific']) ?$_POST['finific']:NULL;
    $ffinfic= isset($_POST['ffinfic']) ?$_POST['ffinfic']:NULL;
    $opera = isset($_REQUEST['opera']) ?$_REQUEST['opera']:NULL;
    $idusu = isset($_REQUEST['idusu']) ? $_REQUEST['idusu'] : (isset($_SESSION["idusu"]) ? $_SESSION["idusu"] : NULL);
    $datOne = NULL;
    $pg = 1210;
    
    $idfic = trim(str_replace(" ","",$idfic));
    
    if (isset($_REQUEST['ajax_dt']) && $_REQUEST['ajax_dt'] == 1) {
        header('Content-Type: application/json; charset=utf-8');
        $draw = isset($_POST['draw']) ? (int)$_POST['draw'] : 1;
        $start = isset($_POST['start']) ? (int)$_POST['start'] : 0;
        $length = isset($_POST['length']) ? (int)$_POST['length'] : 50;
        $searchValue = isset($_POST['search']['value']) ? trim($_POST['search']['value']) : '';
        $tipo = isset($_REQUEST['tipo']) ? $_REQUEST['tipo'] : 'all';

        $searchResult = $mfic->getFichasServerSide($tipo, $searchValue, $start, $length);

        $rows = [];
        foreach ($searchResult['data'] as $d) {
            $col0 = '<strong>' . htmlspecialchars($d['idfic']) . ' - ' . htmlspecialchars($d['nomfic']) . '</strong><br>';
            $col0 .= '<small><strong>Jornada: </strong>' . htmlspecialchars($d['nomval']);
            if ($d['mun']) {
                $col0 .= ' <strong>Municipio: </strong>' . htmlspecialchars($d['mun']);
            }
            $col0 .= '<br><strong>Centro: </strong>' . htmlspecialchars($d['idcen']) . ' ' . htmlspecialchars($d['nomcen']);
            $col0 .= '<br><strong>Inicio: </strong>' . htmlspecialchars($d['finific']) . ' - <strong>Fin: </strong>' . htmlspecialchars($d['ffinfic']) . '</small>';

            $dtnApr = $mfic->selNoApr($d['idfic']);
            $canApr = isset($dtnApr[0]['can']) ? $dtnApr[0]['can'] : 0;
            $col1 = '<div style="text-align: center;"><span style="font-size: 30px; font-weight: bold;">' . $canApr . '</span></div>';

            $col2 = '<div style="text-align: right;">';
            $actas = $mfic->getAllActasFic($d['idfic']);
            if ($actas && count($actas) > 0) {
                foreach ($actas as $acta) {
                    $col2 .= '<a href="views/pdffic.php?idactdc=' . $acta['idactdc'] . '&idfic=' . $d['idfic'] . '" target="_blank" title="Imprimir Acta #' . $acta['numact'] . '"><i class="fa-solid fa-print fa-2x"></i></a> ';
                }
            }
            $col2 .= '<i class="fa-solid fa-file-circle-plus fa-2x" title="Crear Actas" data-bs-toggle="modal" data-bs-target="#CrearActModal' . $d['idfic'] . '"></i> ';
            $col2 .= modActCierreModal($d['idfic'], $d['nomfic'], $d['finific'], $d['ffinfic'] . " " . $d['nomval'], $pg);
            $col2 .= ' <a href="views/vrfic.php?fic=' . $d['idfic'] . '" title="Imprimir" target="_blank"><i class="fa-solid fa-print fa-2x"></i></a> ';

            if (isset($_SESSION["idper"]) && ($_SESSION["idper"] == 1 || $_SESSION["idper"] == 18)) {
                $col2 .= '<a href="home.php?pg=' . $pg . '&idfic=' . $d['idfic'] . '&ope=edi" title="Actualizar"><i class="fa-solid fa-pen-to-square fa-2x"></i></a> ';
            }

            $res = $mfic->selNoApr($d['idfic']);
            $totalAprendices = isset($res[0]['can']) ? (int)$res[0]['can'] : 0;
            if ($totalAprendices === 0) {
                $col2 .= '<a href="home.php?pg=' . $pg . '&idfic=' . $d['idfic'] . '&opera=Eliminar" title="Eliminar" onclick="return eli(this);"><i class="fa-solid fa-trash-can fa-2x"></i></a>';
            }
            $col2 .= '</div>';

            $rows[] = [$col0, $col1, $col2];
        }

        echo json_encode([
            "draw" => $draw,
            "recordsTotal" => $searchResult['recordsTotal'],
            "recordsFiltered" => $searchResult['recordsFiltered'],
            "data" => $rows
        ]);
        exit;
    }
    
$mfic->setIdfic($idfic);
    // Insertar 
    if($opera=="Insertar"){
        if($idfic AND $nomfic AND $jornada AND $idcen){
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
    // actualizar
    if($opera=="Actualizar"){
        if($idfic AND $nomfic AND $jornada AND $idcen){
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
    if($opera=="Eliminar"){
        if("$idfic"){
            $mfic->del();
        }
    }

    //mostrar datos
    $dat = $mfic->selUltimas50();
    $datTitulada = $mfic->selTitulada50();
    $datComplementaria = $mfic->selComplementarias50();
    $dce = $mfic->getCen();
    $djo = $mfic->getJor();
    $dubi = $mfic->getUbica();
    if($idfic){
        $datOne = $mfic->selOne();
    }

    //DASHBOARD
    $dash = $mfic->getDashboard();

    //BUSCADOR DE FICHAS (por nombre o número de ficha)
    $busqueda = isset($_REQUEST['busqueda']) ? trim($_REQUEST['busqueda']) : NULL;
    $datBusqueda = NULL;
    if($busqueda){
        $datBusqueda = $mfic->selBusqueda($busqueda);
    }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ope']) && $_POST['ope'] === 'InAct') {
    $idfic = $_POST['idfic'];
    $idusu = $_POST['coorusu'];
    $numact = $_POST['numacta'];

    $idactdc = $mfic->insertarActaCierre($idusu, $idfic, $numact);
}

function modActCierreModal($fic, $nomfic, $feini, $fefin, $pg) {
    $html = '';
    $html .= '<div class="modal" id="CrearActModal' . $fic . '" tabindex="-1" role="dialog">';
    $html .= '<div class="modal-dialog">';
    $html .= '<form action="home.php?pg=' . $pg . '" method="POST">';
    $html .= '<div class="modal-content">';
    $html .= '<div class="modal-header">';
    $html .= '<h3>Acta de Cierre</h3>';
    $html .= '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>';
    $html .= '</div>';
    $html .= '<div class="modal-body">';
    $html .= '<h5>Ficha: ' . $fic . " " . $nomfic . '</h5><br>';
    $html .= '<h5>Fechas: ' . $feini . " " . $fefin . '</h5><br>';

    $html .= '<div class="row">';
    $html .= '<div class="form-group col-md-12">';
    $html .= '<label for="conact">Conclusiones</label>';
    $html .= '<textarea name="conact" id="conact" class="form-control"></textarea>';
    $html .= '</div>';

    $html .= '<div class="form-group col-md-12">';
    $html .= '<label for="coorusu">Coordinador</label>';
    $html .= '<select name="coorusu" id="coorusu" class="form-select">';
    $html .= '<option value="1">Robin</option>';
    $html .= '</select>';
    $html .= '</div>';

    // === Actas por trimestres ===
    $inicio = new DateTime(explode(" ", $feini)[0]); 
    $fin = new DateTime(explode(" ", $fefin)[0]);
    $hoy = new DateTime();

    $html .= '<div class="form-group col-md-12">';
    $html .= '<label for="numacta">Seleccione Acta</label>';
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

    $html .= '<div class="modal-footer">';
    $html .= '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>';
    $html .= '<input type="submit" class="btn btn-primary" value="Guardar">';
    $html .= '<input type="hidden" name="idfic" value="' . $fic . '">';
    $html .= '<input type="hidden" name="ope" value="InAct">';
    $html .= '</div>';

    $html .= '</div>'; 
    $html .= '</form>';
    $html .= '</div>'; 
    $html .= '</div>'; 

    return $html;
}




?>