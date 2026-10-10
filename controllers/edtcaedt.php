<?php
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    session_start();
}

require_once 'models/edtmaedt.php';

$edtmaedt = new edtmaedt();

$idnorad = isset($_REQUEST['idnorad']) ? $_REQUEST['idnorad'] : NULL;
$idfic = isset($_POST['idfic']) ? $_POST['idfic'] : NULL;
$convht = isset($_POST['convht']) ? $_POST['convht'] : NULL;
$ope = isset($_REQUEST['ope']) ? $_REQUEST['ope'] : NULL;
$comentario = isset($_POST['comentario']) ? trim($_POST['comentario']) : '';
$archivo_listado = isset($_FILES['listado']['name']) ? $_FILES['listado']['name'] : NULL;

if ($idnorad) {
    $edtmaedt->setIdnorad($idnorad);
}
if ($idfic) {
    $edtmaedt->setIdfic($idfic);
}
if ($convht) {
    $edtmaedt->setConvht($convht);
}

// Operaciones de aprobación de EDT
if ($ope == "aprobar_coordinador" && $idnorad) {
    if ($comentario !== '') {
        $edtmaedt->updateEstadoConObs('Aprobado Coordinador', $idnorad, $comentario);
    } else {
        $edtmaedt->setConvht('Aprobado Coordinador');
        $edtmaedt->updateEstado();
    }
}

if ($ope == "rechazar_coordinador" && $idnorad) {
    $edtmaedt->updateEstadoConObs('Rechazado Coordinador', $idnorad, $comentario);
}

if ($ope == "aprobar_admin" && $idnorad) {
    if ($comentario !== '') {
        $edtmaedt->updateEstadoConObs('Aprobado Admin Educativa', $idnorad, $comentario);
    } else {
        $edtmaedt->setConvht('Aprobado Admin Educativa');
        $edtmaedt->updateEstado();
    }
}

if ($ope == "rechazar_admin" && $idnorad) {
    $edtmaedt->updateEstadoConObs('Rechazado Admin Educativa', $idnorad, $comentario);
}

if ($ope == "subir_listado" && $idnorad) {
    if ($archivo_listado) {
        $dir = "fdetevi/";
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $filename = "listado_edt_" . $idnorad . "_" . time() . "_" . basename($_FILES['listado']['name']);
        $ruta = $dir . $filename;
        if (move_uploaded_file($_FILES['listado']['tmp_name'], $ruta)) {
            $edtmaedt->setConvht('Listado Cargado:' . $ruta);
        } else {
            $edtmaedt->setConvht('Listado Cargado');
        }
    } else {
        $edtmaedt->setConvht('Listado Cargado');
    }
    $edtmaedt->updateEstado();
}

if ($ope == "matricular" && $idnorad && $idfic) {
    $edtmaedt->matricular($idnorad, $idfic);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
    $pg = isset($_GET['pg']) ? $_GET['pg'] : (isset($_REQUEST['pg']) ? $_REQUEST['pg'] : '1601');
    echo ("<script>window.location.href = 'home.php?pg=" . $pg . "'</script>");
}

// Cargar todas las solicitudes para alimentar la vista
$datAll = $edtmaedt->getAll();

class edtcaedt{
    private $model;

    public function __construct() {
        $this->model = new edtmaedt();
    }

    public function getUsuario($idusu){ return $this->model->getUsuario($idusu); }
    public function listarPendientes($idcen = null){ return $this->model->listarPendientes($idcen); }
    public function historial($idedt){ return $this->model->historial($idedt); }
    public function decidir($idedt, $rol, $idusu, $decision, $comentario){ return $this->model->decidir($idedt, $rol, $idusu, $decision, $comentario); }
    public function getAll(){ return $this->model->getAll(); }
    public function updateEstado($convht = null, $idnorad = null){ return $this->model->updateEstado($convht, $idnorad); }
    public function updateEstadoConObs($convht, $idnorad, $obs = null){ return $this->model->updateEstadoConObs($convht, $idnorad, $obs); }
    public function matricular($idnorad, $idfic){ return $this->model->matricular($idnorad, $idfic); }
}
?>