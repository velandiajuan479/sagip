<?php
require_once("models/commhcm.php");
require_once("models/commhtr.php");
require_once("models/commemp.php");
require_once("models/commcurc.php");

$idnorad = isset($_REQUEST["idnorad"]) ? $_REQUEST["idnorad"] : NULL;
$opera = isset($_REQUEST['opera']) ? $_REQUEST['opera'] : NULL;
$pg = isset($_GET['pg']) ? $_GET['pg'] : '2000';

$mhorc = new Mhorc();
$mhdt = new Mhdt();
$memp = new Commemp();
$mccm = new Mccm();

$datOne = [];
$datOneEmp = [];
$datOneHt = [];
$idemp_actual = null;

// Inicialización de datos básicos
$mhdt->setIdnorad($idnorad);
$dthdt = $mhdt->getOne();
if ($dthdt) {
    $mccm->setCodpro($dthdt[0]["codpro"]);
    $dtpro = $mccm->getOne();
}

// Lógica para cambiar empresa
if (isset($_POST['cambiar_empresa']) && !empty($_POST['idemp'])) {
    $idemp_actual = $_POST['idemp'];
    if ($idnorad) {
        $mhdt->setIdnorad($idnorad);
        $datos_actuales = $mhdt->getOne();
        if (!empty($datos_actuales[0])) {
            $datos_guardar = $datos_actuales[0];
            $mhdt->setIdemp($idemp_actual);
            $mhdt->setFeclini($datos_guardar['feclini'] ?? null);
            $mhdt->setFeclin($datos_guardar['feclin'] ?? null);
            $mhdt->setCupo($datos_guardar['cupo'] ?? null);
            $mhdt->setJornada($datos_guardar['jornada'] ?? null);
            $mhdt->edit();

            $datOneHt = $mhdt->getOne();
            if (!empty($datOneHt[0])) {
                $datOneHt[0] = array_merge($datOneHt[0], $datos_guardar);
                $datOneHt[0]['idemp'] = $idemp_actual;
                $datOneHt[0]['feclini'] = $datos_guardar['feclini'];
                $datOneHt[0]['feclin'] = $datos_guardar['feclin'];
            } else {
                $datOneHt = $datos_actuales;
                $datOneHt[0]['idemp'] = $idemp_actual;
            }
        }
    }
    $memp->setIdemp($idemp_actual);
    $datOneEmp = $memp->getOne();
} else if ($idnorad) {
    $mhdt->setIdnorad($idnorad);
    $datOneHt = $mhdt->getOne();
    if (!empty($datOneHt[0]['idemp'])) {
        $memp->setIdemp($datOneHt[0]['idemp']);
        $datOneEmp = $memp->getOne();
    }
}

if (!empty($datOneHt) && !empty($datOneHt[0])) {
    $datOne = $datOneHt;
    if (isset($datOne[0]['feclini']) && isset($datOne[0]['feclin'])) {
        $feclini = new DateTime($datOne[0]['feclini']);
        $feclin = new DateTime($datOne[0]['feclin']);
        $datOne[0]['feclini'] = $feclini->format('Y-m-d');
        $datOne[0]['feclin'] = $feclin->format('Y-m-d');
    }
}

// Datos base de la hoja (fechas) consultados directamente, por si el JOIN
// completo de getOne() falla y deja el horario sin poder generarse
$htBase = [];
if ($idnorad) {
    $mhdt->setIdnorad($idnorad);
    $htBase = $mhdt->getFechas();
}

// Horarios ya guardados de la hoja, para prellenar la grilla al re-renderizar
$horarioGuardado = [];
$horarioPorFecha = [];
if ($idnorad) {
    $mhorc->setIdnorad($idnorad);
    $horarioGuardado = $mhorc->getAll();
    foreach ($horarioGuardado as $hr) {
        if (!empty($hr['fecha_especifica'])) {
            $horarioPorFecha[$hr['fecha_especifica']] = $hr;
        }
    }
}

if (empty($idnorad) && !empty($_POST['idemp']) && empty($datOneEmp)) {
    $memp->setIdemp($_POST['idemp']);
    $datOneEmp = $memp->getOne();
}

// ---------------------------------------------------------
// LOGICA DE OPERACIONES (GUARDAR HORARIO E INSTRUCTORES)
// ---------------------------------------------------------

// 1. Guardar Horario

if (($opera == "save_horario" || $opera == "guardar_hrs") && $idnorad) {
    $fecha = isset($_POST['fecha']) ? (array)$_POST['fecha'] : [];
    $hora_inicio = isset($_POST['hora_inicio']) ? (array)$_POST['hora_inicio'] : [];
    $hora_fin = isset($_POST['hora_fin']) ? (array)$_POST['hora_fin'] : [];

    $guardados = 0;
    for ($i = 0; $i < count($fecha); $i++) {
        if (!empty($hora_inicio[$i]) && !empty($hora_fin[$i])) {
            // Reemplaza el horario previo de esa fecha (evita duplicados al re-guardar)
            $mhorc->setIddia($fecha[$i]);
            $mhorc->setIdnorad($idnorad);
            $mhorc->delPorFecha();

            $mhorc->setHinihor($hora_inicio[$i]);
            $mhorc->setHfinhor($hora_fin[$i]);
            if ($mhorc->save()) {
                $guardados++;
            }
        }
    }

    if ($guardados > 0) {
        echo '<script>alert("Se guardaron ' . $guardados . ' registros de horario exitosamente"); window.location.href="home.php?pg=' . $pg . '&opera=edi&idnorad=' . $idnorad . '";</script>';
    } else {
        echo '<script>alert("No se detectaron horarios para guardar. Por favor ingrese al menos una hora de inicio y fin."); window.history.back();</script>';
    }
    exit;
}

// 1.1 Guardar horario individual con instructor asignado
if ($opera == "add_horario" && $idnorad) {
    $fec = isset($_POST['fecha_nueva']) ? $_POST['fecha_nueva'] : null;
    $hi = isset($_POST['hora_inicio_nueva']) ? $_POST['hora_inicio_nueva'] : null;
    $hf = isset($_POST['hora_fin_nueva']) ? $_POST['hora_fin_nueva'] : null;
    $idh = isset($_POST['idusu_hor']) ? $_POST['idusu_hor'] : null;

    if (!$fec || !$hi || !$hf || !$idh) {
        echo '<script>alert("Debe completar la fecha, las horas y el instructor del horario"); window.history.back();</script>';
        exit;
    }

    $mhdt->setIdnorad($idnorad);
    $hojaFechas = $mhdt->getFechas();
    $fIni = !empty($hojaFechas[0]['feclini']) ? date('Y-m-d', strtotime($hojaFechas[0]['feclini'])) : null;
    $fFin = !empty($hojaFechas[0]['feclin']) ? date('Y-m-d', strtotime($hojaFechas[0]['feclin'])) : null;
    if ($fIni && $fFin && ($fec < $fIni || $fec > $fFin)) {
        echo '<script>alert("La fecha debe estar entre la fecha inicial (' . $fIni . ') y la fecha final (' . $fFin . ') de la hoja de trabajo"); window.history.back();</script>';
        exit;
    }

    if ($hf <= $hi) {
        echo '<script>alert("La hora de terminación debe ser mayor que la hora de inicio"); window.history.back();</script>';
        exit;
    }

    $cruce = $mhorc->hayCruceHorario($idnorad, $fec, $idh, $hi, $hf);
    if ($cruce == 2) {
        echo '<script>alert("Este instructor ya tiene ocupado ese horario en esa fecha (en esta u otra ficha)"); window.history.back();</script>';
        exit;
    }
    if ($cruce == 1) {
        echo '<script>alert("Ese horario ya está asignado a otro instructor en esa fecha dentro de esta hoja de trabajo"); window.history.back();</script>';
        exit;
    }

    $mhorc->setIddia($fec);
    $mhorc->setHinihor($hi);
    $mhorc->setHfinhor($hf);
    $mhorc->setIdusu($idh);
    $mhorc->setIdnorad($idnorad);
    if ($mhorc->save()) {
        echo '<script>alert("Horario guardado exitosamente"); window.location.href="home.php?pg=' . $pg . '&opera=edi&idnorad=' . $idnorad . '";</script>';
    } else {
        echo '<script>alert("Error al guardar el horario"); window.history.back();</script>';
    }
    exit;
}

// 2. Agregar Instructor (Esta parte estaba inalcanzable antes)
if ($opera == "add_instructor" && $idnorad) {
    $idusu = isset($_POST['idusu']) ? $_POST['idusu'] : null;
    if ($idusu) {
        if ($mhorc->saveInstructorToNorad($idnorad, $idusu)) {
            echo '<script>alert("Instructor agregado correctamente"); window.location.href="home.php?pg=' . $pg . '&opera=edi&idnorad=' . $idnorad . '";</script>';
        } else {
            echo '<script>alert("Error al agregar instructor"); window.history.back();</script>';
        }
    }
    exit;
}

// 3. Eliminar Instructor
if ($opera == "del_instructor" && $idnorad) {
    $idusu = isset($_GET['idusu']) ? $_GET['idusu'] : null;

    if ($idusu) {
        if ($mhorc->deleteInstructorFromNorad($idusu, $idnorad)) {
            echo '<script>alert("Instructor eliminado correctamente"); window.location.href="home.php?pg=' . $pg . '&opera=edi&idnorad=' . $idnorad . '";</script>';
        } else {
            echo '<script>alert("Error al eliminar instructor"); window.history.back();</script>';
        }
    }
    exit;
}

// ---------------------------------------------------------
// OBTENER DATOS PARA LA VISTA (Solo si no se ejecutó un exit arriba)
// ---------------------------------------------------------

// Obtener listas para el modal de instructores
$listaInstructores = $mhorc->getAllInstructores();
$instructoresAsignados = [];
if ($idnorad) {
    $instructoresAsignados = $mhorc->getInstructoresByNorad($idnorad);
}

// ---------------------------------------------------------
// Dashboard de horas: totales, asignadas y sin asignar
// ---------------------------------------------------------
$horasTotales = 0;
if (!empty($dtpro) && isset($dtpro[0]['horlpro'])) {
    $horasTotales = (float) $dtpro[0]['horlpro'];
} elseif (!empty($dthdt) && !empty($dthdt[0]['codpro'])) {
    // Fallback por si el JOIN de getOne() no trajo el programa
    try {
        $mccmTmp = new Mccm();
        $mccmTmp->setCodpro($dthdt[0]['codpro']);
        $tmpPro = $mccmTmp->getOne();
        if (!empty($tmpPro[0]['horlpro'])) {
            $horasTotales = (float) $tmpPro[0]['horlpro'];
            if (empty($dtpro)) $dtpro = $tmpPro;
        }
    } catch (Exception $e) {
        $horasTotales = 0;
    }
}

$horasAsignadas = 0;
if (!empty($horarioGuardado) && is_array($horarioGuardado)) {
    foreach ($horarioGuardado as $hr) {
        if (!empty($hr['hinihor']) && !empty($hr['hfinhor'])) {
            $tIni = strtotime($hr['hinihor']);
            $tFin = strtotime($hr['hfinhor']);
            if ($tIni !== false && $tFin !== false && $tFin > $tIni) {
                $horasAsignadas += ($tFin - $tIni) / 3600;
            }
        }
    }
}
$horasAsignadas = round($horasAsignadas, 2);
$horasSinAsignar = round(max(0, $horasTotales - $horasAsignadas), 2);
$porcAsignado = $horasTotales > 0 ? round(($horasAsignadas / $horasTotales) * 100, 1) : 0;

$horariosUsu = [];
foreach ($mhorc->getFechasPorInstructor() as $fh) {
    if (!empty($fh['fecha_especifica']) && !empty($fh['idusu'])) {
        $horariosUsu[(string) $fh['idusu']][] = [
            'f' => date('Y-m-d', strtotime($fh['fecha_especifica'])),
            'ini' => !empty($fh['hinihor']) ? substr($fh['hinihor'], 0, 5) : '',
            'fin' => !empty($fh['hfinhor']) ? substr($fh['hfinhor'], 0, 5) : ''
        ];
    }
}
