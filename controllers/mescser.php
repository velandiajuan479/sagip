<?php
if (isset($_GET['_autocomplete'])) {
    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    ini_set('display_errors', 0);
    ini_set('html_errors', 0);

    if (file_exists(__DIR__ . '/../models/conexion.php')) {
        require_once __DIR__ . '/../models/conexion.php';
    }
    require_once __DIR__ . '/../models/mesmser.php';

    header_remove();
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-cache, must-revalidate');
    header('X-Content-Type-Options: nosniff');

    try {
        $mesmser = new Mesmser();
        $msersop = $mesmser;
        $term = filter_input(INPUT_GET, 'term', FILTER_SANITIZE_STRING);
        $results = $mesmser->buscarUsuarios($term ?: '');

        echo json_encode([
            'success' => true,
            'status' => 'success',
            'data' => is_array($results) ? $results : [],
            'timestamp' => time(),
        ], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'status' => 'error',
            'error' => 'Error en el servidor',
            'message' => $e->getMessage(),
        ]);
    }

    exit(0);
}

require_once __DIR__ . '/../models/mesmser.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$mesmser = new Mesmser();
$msersop = $mesmser;
$idcen = isset($_SESSION['idcen']) ? $_SESSION['idcen'] : null;

$idsop = isset($_REQUEST['idsop']) ? $_REQUEST['idsop'] : null;
$idusu = isset($_POST['idusu']) ? $_POST['idusu'] : null;
$idusu2 = isset($_POST['idusu2']) ? $_POST['idusu2'] : null;
$falrep = isset($_POST['falrep']) ? $_POST['falrep'] : null;
$carper = isset($_POST['carper']) ? $_POST['carper'] : null;
$fecser = isset($_POST['fecser']) ? $_POST['fecser'] : null;
$nomper = isset($_POST['nomper']) ? $_POST['nomper'] : null;
$desser = isset($_POST['desser']) ? $_POST['desser'] : null;
$ambiente = isset($_POST['ambiente']) ? trim($_POST['ambiente']) : null;
$ope = isset($_REQUEST['ope']) ? $_REQUEST['ope'] : null;

$dtOne = null;
$mesmser->setIdsop($idsop);

if ($ope == 'save') {
    $idusu = $idusu ?: (isset($_SESSION['idusu']) ? $_SESSION['idusu'] : null);
    if ($idusu) {
        $mesmser->setIdusu($idusu);
    }

    $carper = $carper ?: $mesmser->getCargoActualPorUsuario($idusu);
    if ($carper === null && isset($_SESSION['idper'])) {
        $carper = $_SESSION['idper'];
    }

    $usuarioActual = $idusu ? $mesmser->getUsuarioActual($idusu) : [];
    $nomper = $nomper ?: ($usuarioActual['nomusu'] ?? ($_SESSION['nomusu'] ?? ''));

    $idusu2 = $idusu2 ?: $mesmser->getRandomTecnicoId($idusu);

    if (!empty($ambiente) && strpos((string)$desser, '[Ambiente:') === false) {
        $desser = '[Ambiente: ' . $ambiente . '] ' . $desser;
    }

    $mesmser->setIdusu2($idusu2);
    $mesmser->setFalrep($falrep);
    $mesmser->setCarper($carper);
    $mesmser->setFecser($fecser ?: date('Y-m-d H:i:s'));
    $mesmser->setNomper($nomper);
    $mesmser->setDesser($desser);

    if (!$idsop) {
        $mesmser->setEvisop(null);
        $idsop = $mesmser->save();
        $mesmser->setIdsop($idsop);
        $evidencias = $mesmser->guardarEvidenciasDesdeUpload($idsop, $_FILES['fotos'] ?? []);
        if (!empty($evidencias)) {
            $mesmser->setEvisop($evidencias[0]);
            $mesmser->updateEvisop();
        }
    } else {
        $evidencias = $mesmser->guardarEvidenciasDesdeUpload($idsop, $_FILES['fotos'] ?? []);
        if (!empty($evidencias)) {
            $mesmser->setEvisop($evidencias[0]);
        }
        $mesmser->edit();
    }
}

if ($ope == 'del' && $idsop) {
    $mesmser->del();
    $pg = isset($_GET['pg']) ? $_GET['pg'] : '';
    echo "<script>window.location.href = 'home.php?pg=" . urlencode($pg) . "';</script>";
    exit();
}

if ($ope == 'edit' && $idsop) {
    $dtOne = $mesmser->getOne();
} else {
    $dtOne = null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pg = isset($_GET['pg']) ? $_GET['pg'] : '';
    echo "<script>window.location.href = 'home.php?pg={$pg}';</script>";
    exit();
}

$idper = isset($_SESSION['idper']) ? $_SESSION['idper'] : null;
$idusu_session = isset($_SESSION['idusu']) ? $_SESSION['idusu'] : null;

$dat = $mesmser->getAll($idper, $idusu_session);
$evidenciasPorSoporte = [];
if (is_array($dat)) {
    foreach ($dat as $soporte) {
        $idsopActual = $soporte['idsop'] ?? null;
        if ($idsopActual) {
            $evidenciasPorSoporte[$idsopActual] = $mesmser->getEvidenciasPorSoporte($idsopActual);
        }
    }
}

$dtSelsop = $mesmser->getSelSop($idcen);
$dtCargo = $mesmser->getCargoPer();
$dtFalla = $mesmser->getFallaRep();
$dtAmbientes = $mesmser->getAmbientes();
$usuarioActual = $idusu_session ? $mesmser->getUsuarioActual($idusu_session) : [];
$cargoActual = $usuarioActual['nomcargo'] ?? '';
$cargoActualId = $usuarioActual['idcargo'] ?? ($idper ?? '');
?>