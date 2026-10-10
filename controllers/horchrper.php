<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once("models/hormhrper.php");
require_once("models/hormacceso.php");

// Alcance por rol (módulo HyA) para la cinta de alcance de la vista.
// (El controlador ya centra a no-coordinadores en su propio usuario.)
$haccesoHrp = new HormAcceso();
$scopeCtx = $haccesoHrp->getContexto();
$scopeFichas = $haccesoHrp->getFichasPermitidas($scopeCtx);
$scopeRol = $haccesoHrp->etiquetaRol($scopeCtx);
$scopeN = count($scopeFichas);
$scopeTotal = !empty($scopeCtx['is_admin']);

// Parámetros institucionales del módulo de Horarios (misma fuente que controllers/horchor.php)
if (!defined('FACTOR_FORMACION')) define('FACTOR_FORMACION', 6.4); // horas base diarias de formación directa
if (!defined('FACTOR_OTROS')) define('FACTOR_OTROS', 2.1);          // horas base diarias de alistamiento / otros
if (!defined('DIAS_LABORABLES')) define('DIAS_LABORABLES', 5);      // lunes a viernes

$capacidadDia       = FACTOR_FORMACION + FACTOR_OTROS;              // 8.5 h por día
$totalHorasSemanaBase = round(FACTOR_FORMACION * DIAS_LABORABLES + FACTOR_OTROS * DIAS_LABORABLES, 2); // 42.5 h semanales
$alistamientoSemana = round(FACTOR_OTROS * DIAS_LABORABLES, 1);     // 10.5 h semanales de alistamiento

// 1. Recepción y sanitización de parámetros
$idhor = isset($_REQUEST['idhor']) ? trim($_REQUEST['idhor']) : NULL;  
$idfic = isset($_REQUEST['idfic']) ? trim($_REQUEST['idfic']) : NULL; 
$idaul = isset($_REQUEST['idaul']) ? trim($_REQUEST['idaul']) : NULL; 
$iddia = isset($_REQUEST['iddia']) ? trim($_REQUEST['iddia']) : NULL; 
$hinihor = isset($_REQUEST['hinihor']) ? trim($_REQUEST['hinihor']) : NULL;
$hfinhor = isset($_REQUEST['hfinhor']) ? trim($_REQUEST['hfinhor']) : NULL;
$es_otros = isset($_REQUEST['es_otros']) ? trim($_REQUEST['es_otros']) : 0;
$actividad = isset($_REQUEST['actividad']) ? trim($_REQUEST['actividad']) : NULL;
$horas_otros = isset($_REQUEST['horas_otros']) ? trim($_REQUEST['horas_otros']) : NULL;
$fecha_inicio = isset($_REQUEST['fecha_inicio']) ? trim($_REQUEST['fecha_inicio']) : NULL;
$fecha_fin = isset($_REQUEST['fecha_fin']) ? trim($_REQUEST['fecha_fin']) : NULL;
$fecha_especifica = isset($_REQUEST['fecha_especifica']) ? trim($_REQUEST['fecha_especifica']) : NULL;
$es_formacion_directa = isset($_REQUEST['es_formacion_directa']) ? trim($_REQUEST['es_formacion_directa']) : 1;
$ope = isset($_REQUEST['ope']) ? trim($_REQUEST['ope']) : NULL; 

// Filtros de búsqueda
$idfic_filter = isset($_REQUEST['idfic_filter']) ? trim($_REQUEST['idfic_filter']) : NULL;
$iddia_filter = isset($_REQUEST['iddia_filter']) ? trim($_REQUEST['iddia_filter']) : NULL;
$idaul_filter = isset($_REQUEST['idaul_filter']) ? trim($_REQUEST['idaul_filter']) : NULL;

// 2. Validación de permisos por perfil (sesión real del sistema: $_SESSION['idper'])
//    La página 1517 solo es accesible para el perfil 21 (pagper). Se conserva la semántica:
//    - Perfil 21 puede seleccionar cualquier instructor.
//    - Perfil 4 (solo lectura) no puede editar.
$idper = isset($_SESSION['idper']) ? $_SESSION['idper'] 
        : (isset($_SESSION['pefid']) ? $_SESSION['pefid'] 
        : (isset($_SESSION['idpef']) ? $_SESSION['idpef'] : 0));
$canSelectInstructor = ($idper == 21); // Perfil 21 (Coordinación HyA)
$canEdit = ($idper != 4); // El perfil 4 es de solo lectura en el sistema

$hormhrper = new hormhrper();

// Carga de listas maestras desde la BD real (fichas limitadas al alcance del usuario)
$din = $hormhrper->getInst();
$allFichas = $haccesoHrp->filtrarPorFicha($hormhrper->getAllFichas(), 'idfic', $scopeCtx);
$ddi = $hormhrper->getDia();
$dau = $hormhrper->getAula();

// Captura de usuario desde request o sesión
$req_usu = isset($_REQUEST['idusu']) && !empty($_REQUEST['idusu']) ? trim($_REQUEST['idusu']) : NULL;
$session_usu = isset($_SESSION['idusu']) ? $_SESSION['idusu'] : NULL;

$idusu = NULL;
$instructoresFicha = array();

// Si se selecciona una Ficha, consultar automáticamente el Instructor asociado en la BD
if (!empty($idfic_filter)) {
    $instructoresFicha = $hormhrper->getInstructoresPorFicha($idfic_filter);
}

if (!empty($req_usu)) {
    $idusu = $req_usu;
} else if (!empty($idfic_filter) && !empty($instructoresFicha)) {
    // Tomar automáticamente el instructor asociado a la ficha en la BD
    $idusu = $instructoresFicha[0]['idusu'];
} else if (!empty($session_usu) && !$canSelectInstructor) {
    $idusu = $session_usu;
} else if ($canSelectInstructor && !empty($session_usu) && empty($idfic_filter)) {
    $idusu = $session_usu;
}

// Mapeo si el parámetro recibido es número de documento
if (!empty($idusu) && $din) {
    foreach ($din as $inst) {
        if ($inst['ndocusu'] == $idusu) {
            $idusu = $inst['idusu'];
            break;
        }
    }
}

// Validación de los valores de los filtros contra listas maestras reales (evita valores inventados)
function validarValorMaestro($valor, $lista, $campo){
    if (empty($valor)) return NULL;
    foreach ($lista as $item) {
        if ((string)$item[$campo] === (string)$valor) {
            return $valor;
        }
    }
    return NULL;
}
$idfic_filter = validarValorMaestro($idfic_filter, $allFichas, 'idfic');
$iddia_filter = validarValorMaestro($iddia_filter, $ddi, 'idval');
$idaul_filter = validarValorMaestro($idaul_filter, $dau, 'idaul');

// Consultar datos reales del instructor desde la BD
$selectedInstructorName = "Sin instructor asignado";
$selectedInstructorDoc = "";

if (!empty($idusu)) {
    $userData = $hormhrper->getUsuarioById($idusu);
    if ($userData) {
        $selectedInstructorName = $userData['nomusu'];
        $selectedInstructorDoc = $userData['ndocusu'];
    } else {
        $idusu = NULL;
    }
}

$dtOne = NULL;
$msj_tipo = NULL;
$msj_texto = NULL;

// 3. Orquestación de Operaciones CRUD
if ($ope === 'save') {
    if (!$canEdit) {
        $msj_tipo = 'warning';
        $msj_texto = 'Su perfil no tiene permisos para registrar o modificar horarios.';
    } else {
        $es_otros_int = ($es_otros === '1' || (int)$es_otros === 1) ? 1 : 0;

        // Validación de existencia contra datos reales
        $fichaValida = false;
        if (!empty($idfic)) {
            foreach ($allFichas as $f) {
                if ((string)$f['idfic'] === (string)$idfic) { $fichaValida = true; break; }
            }
        }
        $diaValido = false;
        if (!empty($iddia)) {
            foreach ($ddi as $d) {
                if ((string)$d['idval'] === (string)$iddia) { $diaValido = true; break; }
            }
        }
        $aulaValida = true;
        if (!empty($idaul)) {
            $aulaValida = false;
            foreach ($dau as $a) {
                if ((string)$a['idaul'] === (string)$idaul) { $aulaValida = true; break; }
            }
        }
        $idsInstructores = array();
        if ($din) {
            foreach ($din as $inst) { $idsInstructores[] = $inst['idusu']; }
        }
        $userValido = !empty($idusu) && (!empty($session_usu) && $idusu == $session_usu || in_array($idusu, $idsInstructores));

        if (empty($idfic) || empty($iddia) || empty($idusu)) {
            $msj_tipo = 'warning';
            $msj_texto = 'Debe diligenciar los campos obligatorios (Instructor, Ficha y Día).';
        } else if (!$fichaValida || !$diaValido || !$userValido || !$aulaValida) {
            $msj_tipo = 'warning';
            $msj_texto = 'Debe seleccionar un Instructor, Ficha, Día y Aula válidos de la lista.';
        } else if ($es_otros_int === 1 && (empty($actividad) || empty($horas_otros) || !is_numeric($horas_otros) || (float)$horas_otros <= 0)) {
            $msj_tipo = 'warning';
            $msj_texto = 'Para una Actividad Especial debe indicar el nombre de la actividad y las horas asignadas (mayor a 0).';
        } else {
            // Normalización de franja horaria
            $tIni = (!empty($hinihor) && $hinihor !== '00:00:00') ? (strlen($hinihor) === 5 ? $hinihor . ':00' : $hinihor) : NULL;
            $tFin = (!empty($hfinhor) && $hfinhor !== '00:00:00') ? (strlen($hfinhor) === 5 ? $hfinhor . ':00' : $hfinhor) : NULL;

            if ((empty($tIni) && !empty($tFin)) || (!empty($tIni) && empty($tFin))) {
                $msj_tipo = 'warning';
                $msj_texto = 'Debe indicar la hora de inicio y la hora de fin.';
            } else if (!empty($tIni) && !empty($tFin) && strtotime($tIni) >= strtotime($tFin)) {
                $msj_tipo = 'warning';
                $msj_texto = 'La hora de inicio debe ser menor que la hora de fin.';
            } else {
                // Si no es actividad especial, no se guardan campos de "otros"
                if ($es_otros_int === 0) {
                    $actividad = NULL;
                    $horas_otros = NULL;
                    $es_formacion_directa = NULL;
                    $fecha_inicio = NULL;
                    $fecha_fin = NULL;
                    $fecha_especifica = NULL;
                } else if ($es_formacion_directa !== '1' && $es_formacion_directa !== '2') {
                    $es_formacion_directa = '1';
                }

                $hormhrper->setIdhor($idhor);
                $hormhrper->setIdfic($idfic);
                $hormhrper->setIdaul($idaul);
                $hormhrper->setIdusu($idusu);
                $hormhrper->setIddia($iddia);
                $hormhrper->setHinihor($tIni);
                $hormhrper->setHfinhor($tFin);
                $hormhrper->setEsOtros($es_otros_int);
                $hormhrper->setActividad($actividad);
                $hormhrper->setHorasOtros($horas_otros);
                $hormhrper->setFechaInicio($fecha_inicio);
                $hormhrper->setFechaFin($fecha_fin);
                $hormhrper->setFechaEspecifica($fecha_especifica);
                $hormhrper->setEsFormacionDirecta($es_formacion_directa);

                // Validación de duplicados y cruces de horarios (solo franjas explícitas)
                $conflictoTipo = NULL;
                $conflictoData = NULL;
                if (!empty($tIni) && !empty($tFin)) {
                    $duplicado = $hormhrper->verificarHorarioDuplicado($idusu, $iddia, $tIni, $tFin, $idhor);
                    if ($duplicado) {
                        $conflictoTipo = 'duplicado';
                        $conflictoData = $duplicado;
                    } else {
                        $cruce = $hormhrper->verificarCruceHorario($idusu, $iddia, $tIni, $tFin, $idhor);
                        if ($cruce) {
                            $conflictoTipo = 'cruce';
                            $conflictoData = $cruce;
                        }
                    }
                }

                if ($conflictoTipo === 'duplicado') {
                    $msj_tipo = 'warning';
                    $msj_texto = 'Ya existe un horario duplicado del instructor en la misma franja (' . $tIni . ' - ' . $tFin . '). Revise la información.';
                } else if ($conflictoTipo === 'cruce') {
                    $msj_tipo = 'warning';
                    $msj_texto = 'La franja (' . $tIni . ' - ' . $tFin . ') se cruza con el registro #' . $conflictoData['idhor'] . ' (' . $conflictoData['hinihor'] . ' - ' . $conflictoData['hfinhor'] . ') del mismo día.';
                } else {
                    if (!empty($idhor)) {
                        $resOk = $hormhrper->update();
                        if ($resOk) {
                            $msj_tipo = 'success';
                            $msj_texto = 'Horario actualizado correctamente en la BD.';
                        } else {
                            $msj_tipo = 'danger';
                            $msj_texto = 'Error al actualizar el horario.';
                        }
                    } else {
                        $resOk = $hormhrper->insert();
                        if ($resOk) {
                            $msj_tipo = 'success';
                            $msj_texto = 'Horario registrado correctamente en la BD.';
                        } else {
                            $msj_tipo = 'danger';
                            $msj_texto = 'Error al registrar el horario.';
                        }
                    }
                }
            }
        }
    }
}

if ($ope === 'edi' && !empty($idhor)) {
    $hormhrper->setIdhor($idhor);
    $dtOne = $hormhrper->getOne();
}

if ($ope === 'del' && !empty($idhor)) {
    if (!$canEdit) {
        $msj_tipo = 'warning';
        $msj_texto = 'Su perfil no tiene permisos para eliminar horarios.';
    } else {
        $hormhrper->setIdhor($idhor);
        $resOk = $hormhrper->delete();
        if ($resOk) {
            $msj_tipo = 'success';
            $msj_texto = 'Horario eliminado correctamente de la BD.';
        } else {
            $msj_tipo = 'danger';
            $msj_texto = 'Error al eliminar el horario.';
        }
    }
}

// 4. Carga de fichas del instructor desde la BD
$hormhrper->setIdusu($idusu);
$dfi = $hormhrper->getFicha();

// 5. Consulta principal de horarios filtrados desde la BD real
$allHorarios = array();
if (!empty($idusu) || !empty($idfic_filter) || !empty($iddia_filter) || !empty($idaul_filter)) {
    $allHorarios = $hormhrper->getHorariosFiltrados($idusu, $idfic_filter, $iddia_filter, $idaul_filter);
}

// 6. Cálculo y Control de Horas basado en los registros reales y los parámetros institucionales.
//    Clasificación: horario normal => formación directa; es_otros con es_formacion_directa == 2 => formación;
//    es_otros sin formación directa => otros/alistamiento (igual que el reporte institucional vrphor.php).
$cntDs = 0.0;
$cntOtros = 0.0;
$distribucionDias = array();
$distribucionFichas = array();

if ($ddi) {
    foreach ($ddi as $d) {
        $distribucionDias[$d['idval']] = array(
            'idval' => $d['idval'],
            'parval' => $d['parval'],
            'nomval' => $d['nomval'],
            'horas' => 0.0,
            'cantidad' => 0,
            'horarios' => array()
        );
    }
}

if ($allHorarios) {
    foreach ($allHorarios as $h) {
        $horasSlot = 0.0;

        if (!empty($h['hinihor']) && !empty($h['hfinhor']) && $h['hinihor'] !== '00:00:00' && $h['hfinhor'] !== '00:00:00') {
            $ini = strtotime($h['hinihor']);
            $fin = strtotime($h['hfinhor']);
            if ($fin > $ini) {
                $horasSlot = round(($fin - $ini) / 3600, 2);
            }
        }
        
        if ($horasSlot == 0.0) {
            if ($h['es_otros'] == 1 && !empty($h['horas_otros']) && $h['horas_otros'] > 0) {
                $horasSlot = (float)$h['horas_otros'];
            } else if (!empty($h['horajor']) && $h['horajor'] > 0) {
                $horasSlot = (float)$h['horajor'];
            } else {
                $horasSlot = FACTOR_FORMACION; // Jornada mañana por defecto (sin configuración de horas)
            }
        }

        $esFormacion = ($h['es_otros'] == 1) ? ((int)$h['es_formacion_directa'] === 2 ? 1 : 0) : 1;
        if ($esFormacion) {
            $cntDs += $horasSlot;
        } else {
            $cntOtros += $horasSlot;
        }

        $diaId = $h['iddia'];
        $mappedKey = NULL;
        if (isset($distribucionDias[$diaId])) {
            $mappedKey = $diaId;
        } else {
            foreach ($distribucionDias as $kVal => $vVal) {
                if ($vVal['parval'] == $diaId) {
                    $mappedKey = $kVal;
                    break;
                }
            }
        }

        if ($mappedKey !== NULL) {
            $distribucionDias[$mappedKey]['horas'] += $horasSlot;
            $distribucionDias[$mappedKey]['cantidad'] += 1;
            $distribucionDias[$mappedKey]['horarios'][] = $h;
        }

        $ficKey = !empty($h['idfic']) ? $h['idfic'] : 'Sin Ficha';
        $ficNom = !empty($h['nomfic']) ? $h['nomfic'] : ($h['es_otros'] == 1 ? $h['actividad'] : 'Actividad Especial');
        if (!isset($distribucionFichas[$ficKey])) {
            $distribucionFichas[$ficKey] = array(
                'idfic' => $ficKey,
                'nomfic' => $ficNom,
                'horas' => 0.0,
                'cantidad' => 0
            );
        }
        $distribucionFichas[$ficKey]['horas'] += $horasSlot;
        $distribucionFichas[$ficKey]['cantidad'] += 1;
    }
}

// Resumen de horas: TOTAL = Formación + Alistamiento/Otros + Disponibles
$halis = !empty($idusu) ? max($alistamientoSemana, $cntOtros) : 0.0;
$totalHorasSemana = !empty($idusu) ? $totalHorasSemanaBase : 0.0;
$totalProgramadas = round($cntDs + $cntOtros, 2);
$hdispo = !empty($idusu) ? round(max(0, $totalHorasSemana - $cntDs - $halis), 2) : 0.0;

foreach ($distribucionDias as $key => $dData) {
    $hrsD = $dData['horas'];
    if ($hrsD > 7.0) {
        $distribucionDias[$key]['carga_label'] = 'Carga Alta';
        $distribucionDias[$key]['carga_badge'] = 'badge bg-danger';
        $distribucionDias[$key]['disponible_dia'] = 0.0;
    } else if ($hrsD >= 4.0) {
        $distribucionDias[$key]['carga_label'] = 'Carga Media';
        $distribucionDias[$key]['carga_badge'] = 'badge bg-warning text-dark';
        $distribucionDias[$key]['disponible_dia'] = round($capacidadDia - $hrsD, 1);
    } else {
        $distribucionDias[$key]['carga_label'] = 'Disponible';
        $distribucionDias[$key]['carga_badge'] = 'badge bg-success';
        $distribucionDias[$key]['disponible_dia'] = !empty($idusu) ? round($capacidadDia - $hrsD, 1) : 0.0;
    }
}

function pintit($ddi){ 
    if($ddi){ foreach ($ddi as $d) { 
      echo "<th>".$d['nomval']."</th>"; 
    }} 
}
?>