<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'models/edtmsedt.php';

$edtmsedt = new Edtmsedt;

$idnorad = isset($_REQUEST['idnorad']) ? $_REQUEST['idnorad'] : NULL;
$codpro = isset($_POST['codpro']) ? $_POST['codpro'] : NULL;
$idemp = isset($_POST['idemp']) ? $_POST['idemp'] : NULL;
$codproesp = isset($_POST['codproesp']) ? $_POST['codproesp'] : NULL;
$denominacion = isset($_POST['denominacion']) ? $_POST['denominacion'] : (isset($_POST['codslem']) ? $_POST['codslem'] : NULL);
$codslem = $denominacion;
$feclini = isset($_POST['feclini']) ? $_POST['feclini'] : NULL;
$feclin = isset($_POST['feclin']) ? $_POST['feclin'] : NULL;
$cupo = isset($_POST['cupo']) ? $_POST['cupo'] : NULL;
$jornada = (isset($_POST['jornada']) && $_POST['jornada'] !== '') ? $_POST['jornada'] : NULL;
$idfic = isset($_POST['idfic']) ? $_POST['idfic'] : NULL;
$convht = isset($_POST['convht']) ? $_POST['convht'] : NULL;
$idusu = isset($_SESSION['idusu']) ? $_SESSION['idusu'] : NULL;

$codsena = isset($_POST['codsena']) ? $_POST['codsena'] : NULL;
$ocupacion = isset($_POST['ocupacion']) ? $_POST['ocupacion'] : NULL;
$sector_econ = isset($_POST['sector_econ']) ? $_POST['sector_econ'] : NULL;
$gremio = isset($_POST['gremio']) ? $_POST['gremio'] : NULL;
$duracion = isset($_POST['duracion']) ? $_POST['duracion'] : NULL;
$tipo_edt = isset($_POST['tipo_edt']) ? $_POST['tipo_edt'] : NULL;
$objetivos = isset($_POST['objetivos']) ? $_POST['objetivos'] : NULL;
$temas = isset($_POST['temas']) ? $_POST['temas'] : NULL;
$poblacion = isset($_POST['poblacion']) ? $_POST['poblacion'] : NULL;
$metodologia = isset($_POST['metodologia']) ? $_POST['metodologia'] : NULL;
$resultados = isset($_POST['resultados']) ? $_POST['resultados'] : NULL;
$perfil_orientador = isset($_POST['perfil_orientador']) ? $_POST['perfil_orientador'] : NULL;
$observaciones = isset($_POST['observaciones']) ? $_POST['observaciones'] : NULL;
$sector_programa = isset($_POST['sector_programa']) ? $_POST['sector_programa'] : NULL;
$tipo_evento = isset($_POST['tipo_evento']) ? $_POST['tipo_evento'] : NULL;
$subtipo_evento = isset($_POST['subtipo_evento']) ? $_POST['subtipo_evento'] : NULL;
$horario_propuesto = isset($_POST['horario_propuesto']) ? $_POST['horario_propuesto'] : NULL;
$lugar_edt = isset($_POST['lugar_edt']) ? $_POST['lugar_edt'] : NULL;
$responsable = isset($_POST['responsable']) ? $_POST['responsable'] : NULL;
$nitemp = isset($_POST['nitemp']) ? $_POST['nitemp'] : NULL;
$modalidad_prog = isset($_POST['modalidad_prog']) ? $_POST['modalidad_prog'] : NULL;
$tipo_convenio = isset($_POST['tipo_convenio']) ? $_POST['tipo_convenio'] : NULL;

$archivo_listado = isset($_FILES['listado']['name']) ? $_FILES['listado']['name'] : NULL;
$ope = isset($_REQUEST['ope']) ? $_REQUEST['ope'] : NULL;

$dtOne = NULL;

if ($ope === 'quick_save_emp') {
    header('Content-Type: application/json');
    $nomemp = isset($_POST['nomemp']) ? trim($_POST['nomemp']) : '';
    $numdocemp = isset($_POST['numdocemp']) ? trim($_POST['numdocemp']) : '0';
    if ($nomemp !== '') {
        require_once 'models/conexion.php';
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $sql = "INSERT INTO empresa (nomemp, numdocemp, diremp, codubi, usucre, feccre) VALUES (:nomemp, :numdocemp, 'Sin dirección', 1, :usucre, :feccre)";
        $stmt = $conexion->prepare($sql);
        $usucre = isset($_SESSION["idusu"]) ? $_SESSION["idusu"] : NULL;
        $feccre = date('Y-m-d H:i:s');
        $stmt->bindParam(':nomemp', $nomemp);
        $stmt->bindParam(':numdocemp', $numdocemp);
        $stmt->bindParam(':usucre', $usucre);
        $stmt->bindParam(':feccre', $feccre);
        if ($stmt->execute()) {
            $newId = $conexion->lastInsertId();
            echo json_encode(['status' => 'success', 'idemp' => $newId, 'nomemp' => $nomemp, 'numdocemp' => $numdocemp]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error al guardar la empresa en la base de datos.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Nombre de empresa requerido.']);
    }
    exit();
}

if ($ope === 'quick_save_pro') {
    header('Content-Type: application/json');
    $codpro = isset($_POST['codpro']) ? trim($_POST['codpro']) : '';
    $nompro = isset($_POST['nompro']) ? trim($_POST['nompro']) : '';
    if ($codpro !== '' && $nompro !== '') {
        require_once 'models/conexion.php';
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $sql = "INSERT INTO programa (codpro, nompro, despro, verpro, redcon, idare, tippro) VALUES (:codpro, :nompro, 'Programa Especial EDT', '1', 'Red Tecnológica', 1, 1)";
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':codpro', $codpro);
        $stmt->bindParam(':nompro', $nompro);
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'codpro' => $codpro, 'nompro' => $nompro]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error al insertar programa. Puede que el código ya exista.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Código y nombre de programa son requeridos.']);
    }
    exit();
}

if ($ope === 'quick_save_proesp') {
    header('Content-Type: application/json');
    $nomval = isset($_POST['nomval']) ? trim($_POST['nomval']) : '';
    if ($nomval !== '') {
        require_once 'models/conexion.php';
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $sql = "INSERT INTO valor (iddom, nomval, act) VALUES (25, :nomval, 1)";
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':nomval', $nomval);
        if ($stmt->execute()) {
            $newId = $conexion->lastInsertId();
            echo json_encode(['status' => 'success', 'idval' => $newId, 'nomval' => $nomval]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error al guardar el programa especial.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'El nombre del programa especial es requerido.']);
    }
    exit();
}

if (!$codpro) {
    $datProList = $edtmsedt->getPro();
    $codpro = !empty($datProList) ? $datProList[0]['codpro'] : 1;
}
if (!$idemp) {
    $datEmpList = $edtmsedt->getEmp();
    $idemp = !empty($datEmpList) ? $datEmpList[0]['idemp'] : 1;
}
if (!$codproesp) {
    $datProEspList = $edtmsedt->getValDom(25);
    $codproesp = !empty($datProEspList) ? $datProEspList[0]['idval'] : 1;
}

$edtmsedt->setIdnorad($idnorad);
$edtmsedt->setCodpro($codpro);
$edtmsedt->setIdemp($idemp);
$edtmsedt->setCodproesp($codproesp);
$edtmsedt->setCodslem($codslem);
$edtmsedt->setFeclini($feclini);
$edtmsedt->setFeclin($feclin);
$edtmsedt->setCupo($cupo);
$edtmsedt->setJornada($jornada);
$edtmsedt->setIdfic($idfic);
$edtmsedt->setConvht($convht);
$edtmsedt->setIdusu($idusu);

$edtmsedt->setCodsena($codsena);
$edtmsedt->setDenominacion($denominacion);
$edtmsedt->setOcupacion($ocupacion);
$edtmsedt->setSector_econ($sector_econ);
$edtmsedt->setGremio($gremio);
$edtmsedt->setDuracion($duracion);
$edtmsedt->setTipo_edt($tipo_edt);
$edtmsedt->setObjetivos($objetivos);
$edtmsedt->setTemas($temas);
$edtmsedt->setPoblacion($poblacion);
$edtmsedt->setMetodologia($metodologia);
$edtmsedt->setResultados($resultados);
$edtmsedt->setPerfil_orientador($perfil_orientador);
$edtmsedt->setObservaciones($observaciones);
$edtmsedt->setSector_programa($sector_programa);
$edtmsedt->setTipo_evento($tipo_evento);
$edtmsedt->setSubtipo_evento($subtipo_evento);
$edtmsedt->setHorario_propuesto($horario_propuesto);
$edtmsedt->setLugar_edt($lugar_edt);
$edtmsedt->setResponsable($responsable);
$edtmsedt->setNitemp($nitemp);
$edtmsedt->setModalidad_prog($modalidad_prog);
$edtmsedt->setTipo_convenio($tipo_convenio);

if ($ope == "save") {
    if (!$idnorad) {
        $edtmsedt->setConvht('Pendiente Coordinador');
        $idnorad = $edtmsedt->save();
        $edtmsedt->setIdnorad($idnorad);
        $edtmsedt->saveHxU();
    } else {
        $edtmsedt->edit();
    }
}

$comentario = isset($_POST['comentario']) ? trim($_POST['comentario']) : '';

if ($ope == "aprobar_coordinador" && $idnorad) {
    if ($comentario !== '') {
        $edtmsedt->updateEstadoConObs('Aprobado Coordinador', $idnorad, $comentario);
    } else {
        $edtmsedt->setConvht('Aprobado Coordinador');
        $edtmsedt->updateEstado();
    }
}

if ($ope == "rechazar_coordinador" && $idnorad) {
    $edtmsedt->updateEstadoConObs('Rechazado Coordinador', $idnorad, $comentario);
}

if ($ope == "aprobar_admin" && $idnorad) {
    if ($comentario !== '') {
        $edtmsedt->updateEstadoConObs('Aprobado Admin Educativa', $idnorad, $comentario);
    } else {
        $edtmsedt->setConvht('Aprobado Admin Educativa');
        $edtmsedt->updateEstado();
    }
}

if ($ope == "rechazar_admin" && $idnorad) {
    $edtmsedt->updateEstadoConObs('Rechazado Admin Educativa', $idnorad, $comentario);
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
            $edtmsedt->setConvht('Listado Cargado:' . $ruta);
        } else {
            $edtmsedt->setConvht('Listado Cargado');
        }
    } else {
        $edtmsedt->setConvht('Listado Cargado');
    }
    $edtmsedt->updateEstado();
}

if ($ope == "matricular" && $idnorad && $idfic) {
    $edtmsedt->matricular();
}

if ($ope == "del" && $idnorad) {
    $edtmsedt->del();
}

if ($ope == "edit" && $idnorad) {
    $dtOne = $edtmsedt->getOne();
} else {
    $dtOne = NULL;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
    $pg = isset($_GET['pg']) ? $_GET['pg'] : (isset($_REQUEST['pg']) ? $_REQUEST['pg'] : 'default');
    echo ("<script>window.location.href = 'home.php?pg=" . $pg . "'</script>");
}

$datAll = $edtmsedt->getAll();
$datPro = $edtmsedt->getPro();
$datEmp = $edtmsedt->getEmp();
$datProEsp = $edtmsedt->getValDom(25);
$datJornada = $edtmsedt->getValDom(1);
?>
