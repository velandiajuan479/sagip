<?php
require_once('models/votmvvc.php');

// Obtener parámetros de sesión y navegación con seguridad
$idusu = isset($_SESSION['idusu']) ? $_SESSION['idusu'] : (isset($_SESSION['id']) ? $_SESSION['id'] : NULL);
$idper = isset($_SESSION['idper']) ? $_SESSION['idper'] : (isset($_SESSION['pefid']) ? $_SESSION['pefid'] : NULL);
$pg = isset($_REQUEST["pg"]) ? $_REQUEST["pg"] : 1202;

// Instanciar el modelo de votación de vocero
$votmvvc = new Votmvvc();
$mvvc = $votmvvc; // Compatibilidad

if ($idusu) {
    $votmvvc->setIdusu($idusu);
}

// Validación de sesión si no existe
if (!$idusu && session_status() === PHP_SESSION_ACTIVE && !isset($_SESSION['nomusu']) && !headers_sent()) {
    // Si no hay sesión válida, redirigir
    header("Location: index.php?error=sesion");
    exit();
}

// Consultar si el usuario ya votó
$yaVoto = $idusu ? $votmvvc->getOne('vocero') : false;
$votoInfo = ($idusu && $yaVoto) ? $votmvvc->getVotoUsuario('vocero') : null;
$candVotadoId = ($votoInfo && isset($votoInfo['canusu'])) ? $votoInfo['canusu'] : null;

// Procesar el registro del voto
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['opera']) && $_POST['opera'] === 'save') {
    $canusu = isset($_POST['canusu']) ? $_POST['canusu'] : NULL;

    if ($yaVoto) {
        $_SESSION['votmsg'] = array(
            'tipo'  => 'warning',
            'texto' => 'Ya has ejercido tu derecho al voto para vocero de ficha. No es posible modificar tu voto.'
        );
    } elseif (empty($canusu)) {
        $_SESSION['votmsg'] = array(
            'tipo'  => 'danger',
            'texto' => 'Debes seleccionar una opción de voto antes de confirmar.'
        );
    } else {
        $votmvvc->setCanusu($canusu);
        $votmvvc->setDtvot(date("Y-m-d H:i:s"));

        if ($votmvvc->save('vocero')) {
            $_SESSION['votmsg'] = array(
                'tipo'  => 'success',
                'texto' => 'Tu voto para vocero de ficha ha sido registrado correctamente.'
            );
        } else {
            $_SESSION['votmsg'] = array(
                'tipo'  => 'danger',
                'texto' => 'Hubo un inconveniente al procesar tu solicitud o ya votaste previamente.'
            );
        }

        // Refrescar el estado del voto para mostrar la vista en modo solo lectura
        $yaVoto = $idusu ? $votmvvc->getOne('vocero') : false;
        $votoInfo = ($idusu && $yaVoto) ? $votmvvc->getVotoUsuario('vocero') : null;
        $candVotadoId = ($votoInfo && isset($votoInfo['canusu'])) ? $votoInfo['canusu'] : null;
    }

    if (!headers_sent()) {
        header("Location: home.php?pg=1202");
        exit();
    }
}

// Obtener ficha del aprendiz
$idfic = $idusu ? $votmvvc->getFichaUsuario($idusu) : null;

// Consultar candidatos desde base de datos
$candidatosBD = ($idfic && $idusu) ? $votmvvc->getVocerosMismaFicha($idfic, $idusu) : [];

$votoBlanco = [
    'idusu' => 999,
    'nomusu' => 'Voto en Blanco',
    'noca' => 99,
    'idfic' => '',
    'nomfic' => 'Opción Democrática Institucional',
    'fotcan' => 'img/user.jpg',
    'lema' => 'Opción legítima para manifestar inconformidad o no afinidad con las candidaturas.',
    'is_blanco' => true
];

// Solo aparecen los voceros registrados en la vista Candidato Vocero (1211)
$dat = [];

// Voceros reales registrados en 1211 de la misma ficha en la base de datos
foreach ($candidatosBD as $idx => $cand) {
    $dat[] = [
        'idusu' => $cand['idusu'],
        'nomusu' => $cand['nomusu'],
        'noca' => !empty($cand['noca']) ? $cand['noca'] : ($idx + 1),
        'idfic' => !empty($cand['idfic']) ? $cand['idfic'] : $idfic,
        'nomfic' => !empty($cand['nomfic']) ? $cand['nomfic'] : 'Formación Titulada',
        'fotcan' => 'img/user.jpg',
        'lema' => 'Compromiso, dedicación y vocería activa en representación de la ficha.',
        'is_blanco' => false
    ];
}

// Opción de voto en blanco
$dat[] = $votoBlanco;

// Si el usuario ya votó, asegurarse de que la opción votada esté presente en $dat
if ($yaVoto && $candVotadoId) {
    $encontrado = false;
    foreach ($dat as $cExistente) {
        if ($cExistente['idusu'] == $candVotadoId) {
            $encontrado = true;
            break;
        }
    }
    if (!$encontrado && $votoInfo) {
        $dat[] = [
            'idusu' => $candVotadoId,
            'nomusu' => !empty($votoInfo['nomusu']) ? $votoInfo['nomusu'] : 'Candidato Seleccionado',
            'noca' => 'OK',
            'idfic' => !empty($votoInfo['idfic']) ? $votoInfo['idfic'] : $idfic,
            'nomfic' => !empty($votoInfo['nomfic']) ? $votoInfo['nomfic'] : 'Formación Titulada',
            'fotcan' => 'img/user.jpg',
            'lema' => 'Opción registrada en tu votación de vocero.',
            'is_blanco' => false
        ];
    }
}
?>