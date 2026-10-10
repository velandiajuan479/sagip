<?php
require_once dirname(__DIR__) . '/models/commuasp.php';

$mensaje = "";
$datOne = [];
$modelo = new commuasp();

// Definir la URL de acción para mantener el parámetro 'pg' si existe en la plantilla principal
$pg = $_GET['pg'] ?? null;
$actionUrl = isset($pg) ? "home.php?pg=" . $pg : "vuasp.php";
$sep = (strpos($actionUrl, '?') !== false) ? '&' : '?';

$redirigir = static function ($url) {
    if (!headers_sent()) {
        header("Location: " . $url);
    } else {
        echo '<script>window.location.replace(' . json_encode($url) . ');</script>';
    }
    exit();
};

if (isset($_POST['opera']) && $_POST['opera'] === 'save') {
    $idRegistro = $_POST['id'] ?? null;
    
    if (empty($idRegistro)) {
        $todosActuales = $modelo->getAll();
        if (count($todosActuales) >= 30) {
            $mensaje = '<div><strong>Acción Denegada:</strong> Se ha alcanzado el límite máximo de 30 aspirantes registrados.</div>';
        }
    }

    if (empty($mensaje)) {
        $modelo->setId($idRegistro);
        $modelo->setNombre($_POST['nombre'] ?? '');
        $modelo->setTipoId($_POST['tipoId'] ?? '');
        $modelo->setDocumento($_POST['documento'] ?? '');
        $modelo->setEmail($_POST['email'] ?? '');

        if (!empty($modelo->getNombre()) && !empty($modelo->getDocumento())) {
            $resultado = $modelo->save();
            if ($resultado) {
                $redirigir($actionUrl);
            } else {
                $mensaje = '<div>Error al procesar la información o cupo máximo de 30 registros alcanzado.</div>';
            }
        } else {
            $mensaje = '<div>Por favor complete los campos obligatorios.</div>';
        }
    }
}

if (isset($_GET['opera']) && $_GET['opera'] === 'edi' && isset($_GET['id'])) {
    $datOne = $modelo->edi($_GET['id']);
}

if (isset($_GET['opera']) && $_GET['opera'] === 'eli' && isset($_GET['id'])) {
    $modelo->eliminarAspirante($_GET['id']);
    $redirigir($actionUrl);
}

$datAll = $modelo->getAll();