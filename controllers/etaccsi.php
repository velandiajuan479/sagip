<?php
require_once 'models/etamcsi.php';

$mcsi = new Mcsi();

// Sanitización de parámetros iniciales de navegación y estado
$pg           = $_REQUEST['pg'] ?? 1535;
$opera        = $_REQUEST['opera'] ?? null;
$idcon        = $_REQUEST['idcon'] ?? null;
$idficha      = $_REQUEST['idficha'] ?? null;
$tipo_formato = $_REQUEST['tipo_formato'] ?? 301;
$idaprendiz   = $_REQUEST['idaprendiz'] ?? null;

// Obtención del usuario en sesión actual (Instructor de Seguimiento)
$idusu_sesion = $_SESSION['idusu'] ?? null;

// Cargar listas de dominios, fichas e instructores
$formatosList     = $mcsi->getFormatosEtapaProductiva();
$fichasAsignadas = $mcsi->getFichasInstructor($idusu_sesion);
$instructoresList = $mcsi->getInstructores();

// ------------------------------------------------------------------------------
// OPERACIÓN 1: ALTERNAR FIRMA (Aprendiz / Empresa)
// ------------------------------------------------------------------------------
if ($opera === 'alternar_firma' && !empty($idcon)) {
    $rolFirma = $_REQUEST['rol'] ?? 'aprendiz';
    $resFirma = $mcsi->alternarFirma($idcon, $rolFirma);
    
    $urlRedir = "home.php?pg=" . urlencode($pg) 
              . ($idficha ? "&idficha=" . urlencode($idficha) : "") 
              . "&tipo_formato=" . urlencode($tipo_formato);
              
    echo "<script>window.location = '$urlRedir';</script>";
    exit;
}

// ------------------------------------------------------------------------------
// OPERACIÓN 2: GUARDAR NUEVO CONSERTAMIENTO (GFPI-F-023)
// ------------------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $opera === 'guardar') {
    $datos = [
        'idaprendiz'        => $_POST['idaprendiz'] ?? null,
        'idinstructor'      => $_POST['idinstructor'] ?? $idusu_sesion,
        'idficha'           => $_POST['idficha'] ?? $idficha,
        'tipo_formato'      => $_POST['tipo_formato'] ?? $tipo_formato,
        'nombre_empresa'    => trim($_POST['nombre_empresa'] ?? ''),
        'nit_empresa'       => trim($_POST['nit_empresa'] ?? ''),
        'direccion_empresa' => trim($_POST['direccion_empresa'] ?? ''),
        'nombre_jefe'       => trim($_POST['nombre_jefe'] ?? ''),
        'cargo_jefe'        => trim($_POST['cargo_jefe'] ?? ''),
        'correo_jefe'       => trim($_POST['correo_empresa'] ?? $_POST['correo_jefe'] ?? ''),
        'telefono_jefe'     => trim($_POST['telefono_jefe'] ?? ''),
        'nombre_arl'        => trim($_POST['nombre_arl'] ?? 'SURA'),
        'nivel_riesgo_arl'  => $_POST['nivel_riesgo_arl'] ?? 'I',
        'modalidad'         => $_POST['modalidad'] ?? $_POST['modalidad_reunion'] ?? 'Presencial',
        'fecha_inicio'      => $_POST['fecha_inicio'] ?? date('Y-m-d'),
        'fecha_fin'         => $_POST['fecha_fin'] ?? date('Y-m-d', strtotime('+6 months')),
        'competencias'      => trim($_POST['competencias'] ?? ''),
        'raps'              => trim($_POST['raps'] ?? ''),
        'actividades_lista' => trim($_POST['actividades_lista'] ?? ''),
        'tareas_acordadas'  => trim($_POST['tareas_acordadas'] ?? ''),
        'fechas_entrega'    => trim($_POST['fechas_entrega'] ?? ''),
        'obs_momento1'      => trim($_POST['obs_momento1'] ?? ''),
        'estado'            => $_POST['estado'] ?? 'Aprobado',
        'firma_aprendiz'    => $_POST['firma_aprendiz'] ?? 'Pendiente',
        'firma_empresa'     => $_POST['firma_empresa'] ?? 'Pendiente',
        'firma_instructor'  => 'Firmado'
    ];

    $ficDest = $datos['idficha'];
    $tipDest = $datos['tipo_formato'];

    if (!empty($datos['idaprendiz']) && !empty($datos['idficha']) && !empty($datos['nombre_empresa'])) {
        // PRESERVAR EL MISMO ID: Verificar si viene idcon o si ya existe registro para este aprendiz y ficha
        $idconTarget = !empty($_POST['idcon']) ? (int)$_POST['idcon'] : null;
        if (empty($idconTarget)) {
            $conExistente = $mcsi->getConsentimientoByAprendizFicha($datos['idaprendiz'], $datos['idficha']);
            if (!empty($conExistente['idcon'])) {
                $idconTarget = (int)$conExistente['idcon'];
            }
        }

        if (!empty($idconTarget)) {
            // Actualiza manteniendo exactamente el mismo idcon sin aumentarlo
            $res = $mcsi->edit($idconTarget, $datos);
        } else {
            // Registra por primera vez con nuevo idcon
            $res = $mcsi->save($datos);
        }

        if ($res) {
            echo "<script>
                Swal.fire({
                    icon: 'success',
                    title: '¡Guardado con Éxito!',
                    text: 'El formato de concertación y evaluación se registró correctamente.'
                }).then(() => {
                    window.location = 'home.php?pg=$pg&idficha=$ficDest&tipo_formato=$tipDest';
                });
            </script>";
            exit;
        } else {
            echo "<script>
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo registrar la concertación. Por favor verifique los datos e intente nuevamente.'
                });
            </script>";
        }
    }
}

// ------------------------------------------------------------------------------
// OPERACIÓN 3: ACTUALIZAR CONSERTAMIENTO EXISTENTE
// ------------------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $opera === 'editar' && !empty($idcon)) {
    $datos = [
        'idaprendiz'        => $_POST['idaprendiz'] ?? null,
        'idinstructor'      => $_POST['idinstructor'] ?? $idusu_sesion,
        'idficha'           => $_POST['idficha'] ?? $idficha,
        'tipo_formato'      => $_POST['tipo_formato'] ?? $tipo_formato,
        'nombre_empresa'    => trim($_POST['nombre_empresa'] ?? ''),
        'nit_empresa'       => trim($_POST['nit_empresa'] ?? ''),
        'direccion_empresa' => trim($_POST['direccion_empresa'] ?? ''),
        'nombre_jefe'       => trim($_POST['nombre_jefe'] ?? ''),
        'cargo_jefe'        => trim($_POST['cargo_jefe'] ?? ''),
        'correo_jefe'       => trim($_POST['correo_empresa'] ?? $_POST['correo_jefe'] ?? ''),
        'telefono_jefe'     => trim($_POST['telefono_jefe'] ?? ''),
        'nombre_arl'        => trim($_POST['nombre_arl'] ?? 'SURA'),
        'nivel_riesgo_arl'  => $_POST['nivel_riesgo_arl'] ?? 'I',
        'modalidad'         => $_POST['modalidad'] ?? $_POST['modalidad_reunion'] ?? 'Presencial',
        'fecha_inicio'      => $_POST['fecha_inicio'] ?? date('Y-m-d'),
        'fecha_fin'         => $_POST['fecha_fin'] ?? date('Y-m-d', strtotime('+6 months')),
        'competencias'      => trim($_POST['competencias'] ?? ''),
        'raps'              => trim($_POST['raps'] ?? ''),
        'actividades_lista' => trim($_POST['actividades_lista'] ?? ''),
        'tareas_acordadas'  => trim($_POST['tareas_acordadas'] ?? ''),
        'fechas_entrega'    => trim($_POST['fechas_entrega'] ?? ''),
        'obs_momento1'      => trim($_POST['obs_momento1'] ?? ''),
        'estado'            => $_POST['estado'] ?? 'Aprobado',
        'firma_aprendiz'    => $_POST['firma_aprendiz'] ?? 'Pendiente',
        'firma_empresa'     => $_POST['firma_empresa'] ?? 'Pendiente',
        'firma_instructor'  => $_POST['firma_instructor'] ?? 'Firmado'
    ];

    $ficDest = $datos['idficha'];
    $tipDest = $datos['tipo_formato'];

    $res = $mcsi->edit($idcon, $datos);
    if ($res) {
        echo "<script>
            Swal.fire({
                icon: 'success',
                title: '¡Actualizado!',
                text: 'El formato oficial de concertación fue actualizado exitosamente.'
            }).then(() => {
                window.location = 'home.php?pg=$pg&idficha=$ficDest&tipo_formato=$tipDest';
            });
        </script>";
        exit;
    }
}

// ------------------------------------------------------------------------------
// ------------------------------------------------------------------------------
// OPERACIÓN 4: ELIMINAR CONSERTAMIENTO
// ------------------------------------------------------------------------------
if ($opera === 'eliminar' && !empty($idcon)) {
    $res = $mcsi->delete($idcon);
    $ficDest = $idficha ? "&idficha=$idficha" : "";
    echo "<script>
        Swal.fire({
            icon: 'success',
            title: 'Eliminado',
            text: 'El registro de concertación fue eliminado.'
        }).then(() => {
            window.location = 'home.php?pg=$pg$ficDest&tipo_formato=$tipo_formato';
        });
    </script>";
    exit;
}

// ------------------------------------------------------------------------------
// OPERACIÓN 5: GUARDAR SEGUIMIENTO MOMENTO 2 (GFPI-F-023)
// ------------------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $opera === 'guardar_m2') {
    $idcon = $_POST['idcon'] ?? null;
    $ficDest = $_POST['idficha'] ?? $idficha;
    $tipDest = $_POST['tipo_formato'] ?? 302;

    if (!empty($idcon)) {
        $datosM2 = [
            'fecha_momento2_real' => $_POST['fecha_momento2_real'] ?? date('Y-m-d'),
            'modalidad'           => $_POST['modalidad'] ?? 'Presencial',
            'enlace_grabacion'    => $_POST['enlace_grabacion'] ?? null,
            'obs_momento2'        => $_POST['obs_momento2'] ?? null,
        ];
        $variables = $_POST['variables'] ?? [];

        $res = $mcsi->guardarSeguimientoM2($idcon, $datosM2, $variables);
        if ($res) {
            echo "<script>
                Swal.fire({
                    icon: 'success',
                    title: '¡Evaluación Parcial Guardada!',
                    text: 'El Seguimiento Momento 2 y la evaluación de factores se registraron exitosamente.'
                }).then(() => {
                    window.location = 'home.php?pg=$pg&idficha=$ficDest&tipo_formato=$tipDest';
                });
            </script>";
            exit;
        } else {
            echo "<script>
                Swal.fire({
                    icon: 'error',
                    title: 'Error al Guardar',
                    text: 'No se pudo registrar la evaluación del Momento 2. Intente nuevamente.'
                });
            </script>";
        }
    } else {
        echo "<script>
            Swal.fire({
                icon: 'warning',
                title: 'Atención',
                text: 'No se encontró un registro de concertación previo para asociar el Momento 2.'
            });
        </script>";
    }
}

// ------------------------------------------------------------------------------
// OPERACIÓN 6: GUARDAR EVALUACIÓN FINAL MOMENTO 3 (GFPI-F-023)
// ------------------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $opera === 'guardar_m3') {
    $idcon = $_POST['idcon'] ?? null;
    $ficDest = $_POST['idficha'] ?? $idficha;
    $tipDest = $_POST['tipo_formato'] ?? 303;

    if (empty($idcon) && !empty($_POST['idaprendiz']) && !empty($ficDest)) {
        $conPrev = $mcsi->getConsentimientoByAprendizFicha($_POST['idaprendiz'], $ficDest);
        if (!empty($conPrev['idcon'])) {
            $idcon = (int)$conPrev['idcon'];
        }
    }

    if (!empty($idcon)) {
        // Empaquetar la retroalimentación de los 3 actores solicitada
        $retroData = [
            'coformador_proceso'      => trim($_POST['retro_coformador_proceso'] ?? ''),
            'coformador_competencias' => trim($_POST['retro_coformador_competencias'] ?? ''),
            'instructor_proceso'      => trim($_POST['retro_instructor_proceso'] ?? ''),
            'instructor_competencias' => trim($_POST['retro_instructor_competencias'] ?? ''),
            'aprendiz_proceso'        => trim($_POST['retro_aprendiz_proceso'] ?? ''),
            'aprendiz_competencias'   => trim($_POST['retro_aprendiz_competencias'] ?? ''),
            'reconocimientos'         => trim($_POST['reconocimientos_especiales'] ?? '')
        ];

        $datosM3 = [
            'num_visitas'         => (int)($_POST['num_visitas'] ?? 3),
            'fecha_fin'            => $_POST['fecha_fin'] ?? date('Y-m-d'),
            'fecha_momento3_real'  => $_POST['fecha_momento3_real'] ?? date('Y-m-d'),
            'modalidad'            => $_POST['modalidad'] ?? 'Presencial',
            'enlace_grabacion'     => $_POST['enlace_grabacion'] ?? null,
            'obs_momento3'         => json_encode($retroData, JSON_UNESCAPED_UNICODE),
            'juicio_evaluativo'    => ($_POST['juicio_evaluativo'] ?? 'APROBADO') === 'NO APROBADO' ? 'NO APROBADO' : 'APROBADO'
        ];
        $variables = $_POST['variables'] ?? [];

        $res = $mcsi->guardarEvaluacionFinalM3($idcon, $datosM3, $variables);
        if ($res) {
            $juicioTxt = $datosM3['juicio_evaluativo'];
            echo "<script>
                Swal.fire({
                    icon: 'success',
                    title: '¡Evaluación Final Guardada!',
                    text: 'El Momento 3 y el Juicio Evaluativo ($juicioTxt) fueron registrados exitosamente.'
                }).then(() => {
                    window.location = 'home.php?pg=$pg&idficha=$ficDest&tipo_formato=$tipDest';
                });
            </script>";
            exit;
        } else {
            echo "<script>
                Swal.fire({
                    icon: 'error',
                    title: 'Error al Guardar',
                    text: 'No se pudo registrar la evaluación final del Momento 3. Intente nuevamente.'
                });
            </script>";
        }
    } else {
        echo "<script>
            Swal.fire({
                icon: 'warning',
                title: 'Atención',
                text: 'No se encontró un registro de concertación previo para asociar la Evaluación Final.'
            });
        </script>";
    }
}

// ------------------------------------------------------------------------------
// PRE-CARGA DE DATOS EN LA MISMA PÁGINA PARA DILIGENCIAR O EDITAR
// ------------------------------------------------------------------------------
$conEdit = null;
if (!empty($idcon)) {
    $conEdit = $mcsi->getOneConsentimiento($idcon);
} elseif (!empty($idaprendiz) && !empty($idficha)) {
    // Si viene para diligenciar por aprendiz y ficha, buscar si ya existe consertamiento
    $conExistente = $mcsi->getConsentimientoByAprendizFicha($idaprendiz, $idficha);
    if ($conExistente) {
        $conEdit = $conExistente;
        $idcon = $conExistente['idcon'];
    }
}

$aprendizPreload = null;
if ($opera === 'diligenciar' && !empty($idaprendiz) && empty($conEdit)) {
    $aprendizPreload = $mcsi->getAprendizById($idaprendiz);
}

// Deserializar retroalimentación de Momento 3 si existe
$retroM3 = [];
if (!empty($conEdit['obs_momento3'])) {
    $decoded = json_decode($conEdit['obs_momento3'], true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
        $retroM3 = $decoded;
    }
}

// Catálogo de variables de evaluación (Dominios 31 y 32)
$variablesEvaluacion = $mcsi->getVariablesEvaluacion();

// Detalles evaluados en caso de edición o revisión.
// Se filtran por el momento activo (pg / tipo_formato): el M2 solo lee momento=2 y
// el M3 solo momento=3, para que el formulario no herede los checks del M2.
$momentoDetalles = (($pg == 1537 || $tipo_formato == 302) ? 2 : (($pg == 1538 || $tipo_formato == 303) ? 3 : null));
$detallesM2 = [];
if (!empty($conEdit['idcon'])) {
    $detallesM2 = $mcsi->getDetallesConsertamiento($conEdit['idcon'], $momentoDetalles);
}

// Si no se eligió idficha manualmente en la URL, tomar la primera asignada por defecto
if (empty($idficha) && !empty($fichasAsignadas)) {
    $idficha = $fichasAsignadas[0]['idfic'] ?? $fichasAsignadas[0]['idficha'] ?? null;
}

// ------------------------------------------------------------------------------
// CARGA DE MÉTRICAS GLOBALES Y LISTADO DE APRENDICES DE LA FICHA
// ------------------------------------------------------------------------------
$resumenGlobal = $mcsi->getResumenGlobalInstructor($idusu_sesion);
$resumenFicha = null;
$aprendicesFicha = [];

if (!empty($idficha)) {
    $resumenFicha = $mcsi->getResumenFichaConcertacion($idficha, $tipo_formato);
    $aprendicesFicha = $mcsi->getAprendicesConcertacionFicha($idficha, $tipo_formato);
}
?>