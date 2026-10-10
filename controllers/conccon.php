<?php
require_once('models/conmcon.php');

// Guarda el logo subido en la carpeta img/ con el nombre fijo indicado,
// convirtiendo cualquier imagen (PNG, JPG, GIF, WEBP) a PNG,
// de modo que el cambio se refleje automáticamente en toda la aplicación.
function guardarLogo($pict, $nomfijo){
    $nombre = '';
    if(!$pict || !isset($pict['tmp_name']) || !is_uploaded_file($pict['tmp_name'])) return $nombre;
    $info = @getimagesize($pict['tmp_name']);
    if(!$info) return $nombre;

    $tipos = array(
        IMAGETYPE_JPEG => 'jpeg',
        IMAGETYPE_PNG  => 'png',
        IMAGETYPE_GIF  => 'gif',
        IMAGETYPE_WEBP => 'webp'
    );
    if(!isset($tipos[$info[2]])) return $nombre;

    $origen = @imagecreatefromstring(@file_get_contents($pict['tmp_name']));
    if(!$origen) return $nombre;

    $ancho = imagesx($origen);
    $alto  = imagesy($origen);
    $dest = imagecreatetruecolor($ancho, $alto);
    imagesavealpha($dest, true);
    $trans = imagecolorallocatealpha($dest, 0, 0, 0, 127);
    imagefill($dest, 0, 0, $trans);
    imagecopy($dest, $origen, 0, 0, 0, 0, $ancho, $alto);

    if(!imagepng($dest, 'img/' . $nomfijo)){
        imagedestroy($dest);
        imagedestroy($origen);
        return $nombre;
    }
    imagedestroy($dest);
    imagedestroy($origen);
    $nombre = $nomfijo;
    return $nombre;
}

$mcon = new Mcon();

$idcof  = isset($_REQUEST['idcof']) ? $_REQUEST['idcof'] : 1;
$ope    = isset($_REQUEST['ope']) ? $_REQUEST['ope'] : NULL;
$opera  = isset($_REQUEST['opera']) ? $_REQUEST['opera'] : NULL;

// La pagina de Configuracion siempre debe abrir en modo edicion,
// de lo contrario el formulario queda oculto (patron ocul por mospas=1).
if(($ope === NULL || $ope === '') && ($opera === NULL || $opera === '') && ($_SERVER['REQUEST_METHOD'] === 'GET')){
    echo '<script>window.location="home.php?pg=1114&ope=edi";</script>';
    exit;
}

$titcof = isset($_POST['titcof']) ? trim($_POST['titcof']) : NULL;
$descof = isset($_POST['descof']) ? trim($_POST['descof']) : NULL;
$foocof = isset($_POST['foocof']) ? trim($_POST['foocof']) : NULL;
$nacta  = isset($_POST['nacta']) ? trim($_POST['nacta']) : NULL;
$veracta = isset($_POST['veracta']) ? trim($_POST['veracta']) : NULL;

function sanearConstante($valor){
    return str_replace(array('\\', "'"), array('\\\\', "\\'"), $valor);
}

function guardarConstantesActa($archivo, $nacta, $veracta){
    $contenido = @file_get_contents($archivo);
    if($contenido === false) return false;
    $nacta = sanearConstante($nacta);
    $veracta = sanearConstante($veracta);
    $contenido = preg_replace("/const\s+NACTA\s*=\s*'[^']*';/", "const NACTA = '".$nacta."';", $contenido, 1, $c1);
    $contenido = preg_replace("/const\s+VERACTA\s*=\s*'[^']*';/", "const VERACTA = '".$veracta."';", $contenido, 1, $c2);
    if($c1 !== 1 || $c2 !== 1) return false;
    return file_put_contents($archivo, $contenido) !== false;
}

$datOne = $mcon->getOne();

// Nombres fijos de archivo para cada logo:
// A color: senan.png
// En negro: logoSenaB.png
// En blanco: logoSenaW.png
$FOTO  = 'senan.png';
$FOTOB = 'logoSenaB.png';
$FOTOW = 'logoSenaW.png';

if($ope == "save"){
    if(!$titcof || !$nacta || !$veracta){
        echo '<div class="d-none" data-notify="error" data-msg="Todos los datos son obligatorios."></div>';
    }else{
        // Subir logos si vienen en el formulario
        $hayFoto  = isset($_FILES['foto']['name'])  && $_FILES['foto']['name'];
        $hayFotob = isset($_FILES['fotob']['name']) && $_FILES['fotob']['name'];
        $hayFotow = isset($_FILES['fotow']['name']) && $_FILES['fotow']['name'];

        $logcof  = $hayFoto  ? guardarLogo($_FILES['foto'],  $FOTO)  : NULL;
        $logbcof = $hayFotob ? guardarLogo($_FILES['fotob'], $FOTOB) : NULL;
        $logwcof = $hayFotow ? guardarLogo($_FILES['fotow'], $FOTOW) : NULL;

        if($hayFoto  && !$logcof)  echo '<div class="d-none" data-notify="error" data-msg="El logo a color debe ser una imagen valida (PNG, JPG, GIF o WEBP)."></div>';
        if($hayFotob && !$logbcof) echo '<div class="d-none" data-notify="error" data-msg="El logo en negro debe ser una imagen valida (PNG, JPG, GIF o WEBP)."></div>';
        if($hayFotow && !$logwcof) echo '<div class="d-none" data-notify="error" data-msg="El logo en blanco debe ser una imagen valida (PNG, JPG, GIF o WEBP)."></div>';

        if(!$logcof)  $logcof  = file_exists('img/'.$FOTO)  ? $FOTO  : (isset($datOne[0]['logcof'])  ? $datOne[0]['logcof']  : '');
        if(!$logbcof) $logbcof = file_exists('img/'.$FOTOB) ? $FOTOB : (isset($datOne[0]['logbcof']) ? $datOne[0]['logbcof'] : '');
        if(!$logwcof) $logwcof = file_exists('img/'.$FOTOW) ? $FOTOW : (isset($datOne[0]['logwcof']) ? $datOne[0]['logwcof'] : '');

        // Semántica fija:
        // - A color: para el sistema (menú)
        // - En negro: para documentos impresos
        // - En blanco: para documentos con fondo oscuro
        $logsis = $logcof;
        $logdoc = $logbcof;

        $mcon->setIdcof($idcof);
        $mcon->setTitcof($titcof);
        $mcon->setDescof($descof);
        $mcon->setLogcof($logcof);
        $mcon->setLogbcof($logbcof);
        $mcon->setLogwcof($logwcof);
        $mcon->setLogsis($logsis);
        $mcon->setLogdoc($logdoc);
        $mcon->setFoocof($foocof);
        $mcon->edit();

        guardarConstantesActa(dirname(__DIR__).'/models/conmcon.php', $nacta, $veracta);

        echo '<script>window.location="home.php?pg=1114&ope=edi";</script>';
    }
}

$datOne = $mcon->getOne();
?>