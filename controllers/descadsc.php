<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('America/Bogota');
require_once(__DIR__ . "/../models/desmadsc.php");

// Instanciar Modelo
$modeloAdsc = new Desmadsc();

// Variables de sesión y contexto
$idusuSesion = isset($_SESSION['idusu']) ? (int) $_SESSION['idusu'] : 1;
$pefidSesion = isset($_SESSION['pefid']) ? (int) $_SESSION['pefid'] : 1;
$nomusuSesion = isset($_SESSION['nomusu']) ? $_SESSION['nomusu'] : 'Usuario del Sistema';

// Parámetros de navegación
$pg = isset($_REQUEST['pg']) ? (int) $_REQUEST['pg'] : 2455;
$tabActiva = isset($_REQUEST['tab']) ? trim($_REQUEST['tab']) : 'dashboard';

// Manejo de Ficha seleccionada
if (!empty($_POST['idfic_sel'])) {
    $_SESSION['adsc_idfic'] = trim($_POST['idfic_sel']);
} elseif (!empty($_GET['idfic'])) {
    $_SESSION['adsc_idfic'] = trim($_GET['idfic']);
}
$idficActiva = isset($_SESSION['adsc_idfic']) ? $_SESSION['adsc_idfic'] : null;

// Aprendiz seleccionado para ver Acta detallada
$idusuSel = isset($_REQUEST['idusu_sel']) ? (int) $_REQUEST['idusu_sel'] : null;

// Deep-link: si se accede con ?idact=N (p. ej. desde el reporte de inasistencias) abrir directamente el acta del aprendiz
$idactLink = isset($_REQUEST['idact']) ? (int) $_REQUEST['idact'] : 0;
if ($idactLink > 0 && ($tabActiva === 'dashboard' || $tabActiva === 'acta')) {
    $infoAct = $modeloAdsc->consultaIdact($idactLink);
    if ($infoAct) {
        $idusuSel = (int) $infoAct['idusu'];
        $idficActiva = $infoAct['idfic'];
        $_SESSION['adsc_idfic'] = $idficActiva;
        $tabActiva = 'acta';
    }
}

// Mensaje de alerta flash
$alerta = null;
if (isset($_SESSION['adsc_alerta'])) {
    $alerta = $_SESSION['adsc_alerta'];
    unset($_SESSION['adsc_alerta']);
}

// -------------------------------------------------------------
// PROCESAMIENTO DE ACCIONES (POST)
// -------------------------------------------------------------
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
    $accion = trim($_POST['accion']);

    // 1. CARGA DE DOCUMENTO DE JUSTIFICACIÓN (APRENDIZ / USUARIO)
    if ($accion === 'subir_justificacion') {
        $idusuJust = isset($_POST['idusu_just']) ? (int) $_POST['idusu_just'] : 0;
        $idficJust = isset($_POST['idfic_just']) ? trim($_POST['idfic_just']) : '';
        $tipoDoc = isset($_POST['tipodoc']) ? trim($_POST['tipodoc']) : '';
        $fecJust = isset($_POST['fecjust']) ? trim($_POST['fecjust']) : '';
        $obsJust = isset($_POST['obsjust']) ? trim($_POST['obsjust']) : '';

        // Validación de tipos autorizados estrictos
        $tiposPermitidos = Desmadsc::getTiposValidos();
        if (!in_array($tipoDoc, $tiposPermitidos, true)) {
            $_SESSION['adsc_alerta'] = [
                'tipo' => 'danger',
                'mensaje' => 'Error de validación: El tipo de documento "' . htmlspecialchars($tipoDoc) . '" no es válido. Solo se admiten: Cita médica, Incapacidad o Muerte de un familiar de primer grado de consanguinidad.'
            ];
            header("Location: home.php?pg=" . $pg . "&tab=justificaciones" . ($idficActiva ? "&idfic=" . urlencode($idficActiva) : ""));
            exit;
        }

        // Validación de datos obligatorios
        if ($idusuJust <= 0 || empty($idficJust) || empty($fecJust)) {
            $_SESSION['adsc_alerta'] = [
                'tipo' => 'danger',
                'mensaje' => 'Faltan campos obligatorios para registrar la justificación.'
            ];
            header("Location: home.php?pg=" . $pg . "&tab=justificaciones");
            exit;
        }

        // Validación de archivo subido
        if (!isset($_FILES['archivo_just']) || $_FILES['archivo_just']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['adsc_alerta'] = [
                'tipo' => 'danger',
                'mensaje' => 'Debe adjuntar un archivo válido para justificar la inasistencia.'
            ];
            header("Location: home.php?pg=" . $pg . "&tab=justificaciones");
            exit;
        }

        $archivo = $_FILES['archivo_just'];
        $maxSize = 5 * 1024 * 1024; // 5 MB
        if ($archivo['size'] > $maxSize) {
            $_SESSION['adsc_alerta'] = [
                'tipo' => 'danger',
                'mensaje' => 'El archivo supera el límite permitido de 5 MB.'
            ];
            header("Location: home.php?pg=" . $pg . "&tab=justificaciones");
            exit;
        }

        // Extensiones y MIME types permitidos
        $ext = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        $extPermitidas = ['pdf', 'jpg', 'jpeg', 'png'];
        $mimesPermitidos = ['application/pdf', 'image/jpeg', 'image/png', 'image/pjpeg'];

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeReal = finfo_file($finfo, $archivo['tmp_name']);
        finfo_close($finfo);

        if (!in_array($ext, $extPermitidas, true) || !in_array($mimeReal, $mimesPermitidos, true)) {
            $_SESSION['adsc_alerta'] = [
                'tipo' => 'danger',
                'mensaje' => 'Formato no admitido. Únicamente se aceptan documentos en PDF, JPG, JPEG o PNG.'
            ];
            header("Location: home.php?pg=" . $pg . "&tab=justificaciones");
            exit;
        }

        // Asegurar directorio destino seguro
        $dirDestino = __DIR__ . "/../doc/justificaciones/";
        if (!file_exists($dirDestino)) {
            mkdir($dirDestino, 0755, true);
        }

        // Nombre de archivo seguro e inyectable
        $nombreGuardado = sprintf(
            "just_%d_%s_%s.%s",
            $idusuJust,
            date('Ymd_His'),
            bin2hex(random_bytes(4)),
            $ext
        );
        $rutaCompleta = $dirDestino . $nombreGuardado;
        $rutaRelativa = "doc/justificaciones/" . $nombreGuardado;

        if (move_uploaded_file($archivo['tmp_name'], $rutaCompleta)) {
            $resp = $modeloAdsc->guardarJustificacion(
                $idusuJust,
                $idficJust,
                $tipoDoc,
                $archivo['name'],
                $rutaRelativa,
                $fecJust,
                $obsJust
            );

            $_SESSION['adsc_alerta'] = [
                'tipo' => $resp['success'] ? 'success' : 'danger',
                'mensaje' => $resp['message']
            ];
        } else {
            $_SESSION['adsc_alerta'] = [
                'tipo' => 'danger',
                'mensaje' => 'Error al transferir el archivo al servidor de almacenamiento.'
            ];
        }

        header("Location: home.php?pg=" . $pg . "&tab=justificaciones" . ($idficActiva ? "&idfic=" . urlencode($idficActiva) : ""));
        exit;
    }

    // 2. REVISIÓN DE DOCUMENTO POR INSTRUCTOR (AUTORIDAD DEL INSTRUCTOR)
    if ($accion === 'revisar_documento') {
        $idjust = isset($_POST['idjust']) ? (int) $_POST['idjust'] : 0;
        $decision = isset($_POST['decision']) ? trim($_POST['decision']) : '';
        $obsInstructor = isset($_POST['obs_instructor']) ? trim($_POST['obs_instructor']) : '';
        $agregarFalta = isset($_POST['agregar_falta']) && $_POST['agregar_falta'] == '1';
        $horasFalta = isset($_POST['horas_falta']) ? (int) $_POST['horas_falta'] : 6;
        $fechaFalta = isset($_POST['fecha_falta']) ? trim($_POST['fecha_falta']) : null;
        $motivoFalta = isset($_POST['motivo_falta']) ? trim($_POST['motivo_falta']) : $obsInstructor;

        if ($idjust <= 0 || !in_array($decision, ['aprobar', 'rechazar'], true)) {
            $_SESSION['adsc_alerta'] = [
                'tipo' => 'danger',
                'mensaje' => 'Parámetros de revisión no válidos.'
            ];
            header("Location: home.php?pg=" . $pg . "&tab=justificaciones");
            exit;
        }

        $resRev = $modeloAdsc->procesarRevisionInstructor(
            $idjust,
            $decision,
            $obsInstructor,
            $idusuSesion,
            $agregarFalta,
            $horasFalta,
            $fechaFalta,
            $motivoFalta
        );

        $_SESSION['adsc_alerta'] = [
            'tipo' => $resRev['success'] ? 'success' : 'danger',
            'mensaje' => $resRev['message']
        ];

        // AUTO-GENERACIÓN: si la falta aplicada (documento rechazado) hace que el
        // aprendiz supere el umbral de 30 horas, se emite el Acta de Deserción.
        if ($resRev['success'] && $agregarFalta && $decision === 'rechazar') {
            $resAut = $modeloAdsc->verificarYGenerarActaDesercion((int) $resRev['idusu'], $resRev['idfic']);
            if ($resAut['generated']) {
                $_SESSION['adsc_alerta']['mensaje'] .= ' El aprendiz supera el umbral de 30 horas de inasistencia: Acta de Deserción N° ' . (int) $resAut['idact'] . ' generada automáticamente.';
            }
        }

        header("Location: home.php?pg=" . $pg . "&tab=justificaciones" . ($idficActiva ? "&idfic=" . urlencode($idficActiva) : ""));
        exit;
    }

    // 3. AÑADIR FALTA DIRECTAMENTE AL ACTA DE DESERCIÓN
    if ($accion === 'agregar_falta_manual') {
        $idusuFalta = isset($_POST['idusu_falta']) ? (int) $_POST['idusu_falta'] : 0;
        $idficFalta = isset($_POST['idfic_falta']) ? trim($_POST['idfic_falta']) : '';
        $fecina = isset($_POST['fecina']) ? trim($_POST['fecina']) : '';
        $horasina = isset($_POST['horasina']) ? (int) $_POST['horasina'] : 0;
        $comina = isset($_POST['comina']) ? trim($_POST['comina']) : '';

        if ($idusuFalta <= 0 || empty($idficFalta) || empty($fecina) || $horasina <= 0) {
            $_SESSION['adsc_alerta'] = [
                'tipo' => 'danger',
                'mensaje' => 'Datos incompletos para añadir la falta. Verifique horas y fecha.'
            ];
        } else {
            $resFalta = $modeloAdsc->insInasistenciaDirecta(
                $idusuFalta,
                $idficFalta,
                $fecina,
                $horasina,
                "FALTA REGISTRADA DIRECTAMENTE EN ACTA: " . $comina
            );

            if ($resFalta['success']) {
                $msgFalta = 'Falta disciplinaria de ' . $horasina . ' horas agregada con éxito al acta de deserción.';

                // AUTO-GENERACIÓN: si el aprendiz supera el umbral de 30 horas,
                // se emite automáticamente su Acta de Deserción.
                $resAut2 = $modeloAdsc->verificarYGenerarActaDesercion($idusuFalta, $idficFalta);
                if ($resAut2['generated']) {
                    $msgFalta .= ' El aprendiz supera el umbral de 30 horas: Acta de Deserción N° ' . (int) $resAut2['idact'] . ' generada automáticamente.';
                }

                $_SESSION['adsc_alerta'] = [
                    'tipo' => 'success',
                    'mensaje' => $msgFalta
                ];
            } else {
                $_SESSION['adsc_alerta'] = [
                    'tipo' => 'danger',
                    'mensaje' => $resFalta['message']
                ];
            }
        }

        header("Location: home.php?pg=" . $pg . "&tab=acta&idusu_sel=" . $idusuFalta . "&idfic=" . urlencode($idficFalta));
        exit;
    }

    // 4. FORMALIZAR ACTA OFICIAL DE DESERCIÓN
    if ($accion === 'formalizar_acta') {
        $idusuActa = isset($_POST['idusu_acta']) ? (int) $_POST['idusu_acta'] : 0;
        $idficActa = isset($_POST['idfic_acta']) ? trim($_POST['idfic_acta']) : '';
        $conact = isset($_POST['conact']) ? trim($_POST['conact']) : '';
        $coorusu = isset($_POST['coorusu']) ? (int) $_POST['coorusu'] : 1;

        if ($idusuActa <= 0 || empty($idficActa) || empty($conact)) {
            $_SESSION['adsc_alerta'] = [
                'tipo' => 'danger',
                'mensaje' => 'Debe ingresar las consideraciones y motivos del acta de deserción.'
            ];
        } else {
            $resActa = $modeloAdsc->guardarActaOficial($idusuActa, $idficActa, $conact, $coorusu, $idusuSesion);
            $_SESSION['adsc_alerta'] = [
                'tipo' => $resActa['success'] ? 'success' : 'danger',
                'mensaje' => $resActa['success']
                    ? 'Acta oficial de deserción N° ' . $resActa['idact'] . ' formalizada con éxito. El estado del aprendiz ha sido actualizado a Deserción.'
                    : $resActa['message']
            ];
        }

        header("Location: home.php?pg=" . $pg . "&tab=acta&idusu_sel=" . $idusuActa . "&idfic=" . urlencode($idficActa));
        exit;
    }
}

// -------------------------------------------------------------
// CONSULTA DE DATOS PARA LA VISTA
// -------------------------------------------------------------
// 1. Métricas del Dashboard inicial
$dashboardResumen = $modeloAdsc->getDashboardResumen();

// 2. Fichas con estadísticas dinámicas de deserción (se renderizan como tarjetas estilo Videos TV)
$fichasDesercion = $modeloAdsc->getFichasDesercionStats();

// 3. Fichas disponibles para selector
$fichasDisponibles = $modeloAdsc->getFichasDisponibles();

// Si no hay ficha seleccionada en sesión pero hay fichas disponibles, preseleccionar la que tenga desertados
if (!$idficActiva && !empty($fichasDesercion)) {
    $idficActiva = $fichasDesercion[0]['idfic'];
    $_SESSION['adsc_idfic'] = $idficActiva;
}

// 4. Aprendices de la ficha activa
$aprendicesFicha = ($idficActiva) ? $modeloAdsc->getAprendicesByFicha($idficActiva) : [];

// Si se pidió tab acta sin aprendiz seleccionado, seleccionar el primero con más inasistencias o desertado
if ($tabActiva === 'acta' && !$idusuSel && !empty($aprendicesFicha)) {
    $idusuSel = (int) $aprendicesFicha[0]['idusu'];
}

// 5. Detalle del Acta para el estudiante seleccionado
$detalleActa = ($idusuSel && $idficActiva)
    ? $modeloAdsc->getDetalleActaEstudiante($idusuSel, $idficActiva)
    : null;

// 6. Justificaciones
$justificaciones = $modeloAdsc->getJustificaciones(null, $idficActiva);

// 7. Coordinadores
$coordinadores = $modeloAdsc->getCoordinadores();

// 8. Tipos de documentos válidos
$tiposDocValidos = Desmadsc::getTiposValidos();
?>