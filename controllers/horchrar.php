<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../models/conexion.php';
require_once __DIR__ . '/../models/hormhrar.php';

date_default_timezone_set('America/Bogota');

$mrphau = new Mrphau();
$aulasConHorario = $mrphau->getAulasConHorarios();
$listAulas       = $mrphau->getAllAulasConInfo();
$todosTrimestres = $mrphau->getAllTrimestres();

// 1. Selección de Aula
$idaul = $_REQUEST['idaul'] ?? $_REQUEST['aul'] ?? null;

if (empty($idaul)) {
    if (!empty($aulasConHorario)) {
        $idaul = $aulasConHorario[0];
    } elseif (!empty($listAulas)) {
        $idaul = $listAulas[0]['idaul'];
    } else {
        $idaul = null;
    }
}

$mrphau->setIdaul($idaul);
$dtOneHeader = $mrphau->getOne();

// 2. Detección de Período:
//    a) Fechas manuales enviadas por el usuario
//    b) Trimestre seleccionado del calendario_academico
//    c) Trimestre activo del año actual en calendario_academico
//    d) Rango real de fechas registradas en horario para esta aula (NUEVO)
//    e) Mes actual como último fallback

$fechaIniReq    = $_REQUEST['fecha_inicio'] ?? $_REQUEST['ini'] ?? null;
$fechaFinReq    = $_REQUEST['fecha_fin']    ?? $_REQUEST['fin'] ?? null;
$idTrimestre    = $_REQUEST['id_trimestre'] ?? $_REQUEST['id_tri'] ?? null;
$__trActual     = null;

// a) Rango manual
if (!empty($fechaIniReq) && !empty($fechaFinReq)) {
    $__trActual = [
        'id'           => null,
        'nombre'       => 'Rango personalizado',
        'año'          => (int)substr($fechaIniReq, 0, 4),
        'fecha_inicio' => $fechaIniReq,
        'fecha_fin'    => $fechaFinReq
    ];
}

// b) Trimestre seleccionado manualmente
if (!$__trActual && !empty($idTrimestre)) {
    foreach ($todosTrimestres as $tr) {
        if ($tr['id'] == $idTrimestre) {
            $__trActual = [
                'id'           => $tr['id'],
                'nombre'       => $tr['trimestre'] ?: ($tr['titulo'] ?: 'Trimestre'),
                'año'          => (int)$tr['anio'],
                'fecha_inicio' => $tr['fecha_inicio'],
                'fecha_fin'    => $tr['fecha_fin']
            ];
            break;
        }
    }
}

// c) Trimestre activo del año actual en calendario_academico
if (!$__trActual) {
    $anioActual = (int)date('Y');
    $hoy = new DateTime('now', new DateTimeZone('America/Bogota'));

    foreach ($todosTrimestres as $tr) {
        if ((int)$tr['anio'] === $anioActual && !empty($tr['fecha_inicio']) && !empty($tr['fecha_fin'])) {
            $ini = new DateTime($tr['fecha_inicio']);
            $fin = new DateTime($tr['fecha_fin']);
            if ($hoy >= $ini && $hoy <= $fin) {
                $__trActual = [
                    'id'           => $tr['id'],
                    'nombre'       => $tr['trimestre'] ?: ($tr['titulo'] ?: 'Trimestre'),
                    'año'          => (int)$tr['anio'],
                    'fecha_inicio' => $tr['fecha_inicio'],
                    'fecha_fin'    => $tr['fecha_fin']
                ];
                break;
            }
        }
    }
}

// d) Si no hay trimestre activo para el año actual, usar el rango real del año actual en horario.
//    Se pasa $anioActual para NO incluir datos de años anteriores (ej. 2025).
//    Si hay registros en 2026, el período será 1 ene - 31 dic del año actual (año completo),
//    de esa forma el reporte muestra TODO lo registrado en el año sin importar el trimestre.
if (!$__trActual && !empty($idaul)) {
    $anioActual = (int)date('Y');
    $rangoAula = $mrphau->getRangoFechasAula($idaul, $anioActual);
    if (!empty($rangoAula['fecha_min']) && !empty($rangoAula['fecha_max'])) {
        $__trActual = [
            'id'           => null,
            'nombre'       => 'Vigencia ' . $anioActual,
            'año'          => $anioActual,
            'fecha_inicio' => $anioActual . '-01-01',
            'fecha_fin'    => $anioActual . '-12-31'
        ];
    }
}

// e) Fallback final: mes actual
if (!$__trActual) {
    $anioActual = (int)date('Y');
    $__trActual = [
        'id'           => null,
        'nombre'       => 'Trimestre',
        'año'          => $anioActual,
        'fecha_inicio' => date('Y-m-01'),
        'fecha_fin'    => date('Y-m-t')
    ];
}

$__iniTr = $__trActual['fecha_inicio'];
$__finTr = $__trActual['fecha_fin'];

// 3. Horarios del aula en el rango
$dtOne = $mrphau->getHorariosAulaEnRango($idaul, $__iniTr, $__finTr);

// 4. Vista
require __DIR__ . '/../views/horrhrar.php';