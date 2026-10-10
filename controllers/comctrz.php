<?php
if (session_status() == PHP_SESSION_NONE && !headers_sent()) {
	session_start();
}

require_once __DIR__ . '/../models/commtrz.php';

$commtrz = new Commtrz();
$icono = isset($icono) ? $icono : 'fa-solid fa-route';

// Captura y saneamiento de parámetros
$opera = htmlspecialchars($_POST['opera'] ?? ($_GET['opera'] ?? ''), ENT_QUOTES, 'UTF-8');
$idnorad = !empty($_POST['idnorad']) ? (int)$_POST['idnorad'] : (!empty($_GET['idnorad']) ? (int)$_GET['idnorad'] : null);
$esDemo = isset($_GET['demo']) ? ((int)$_GET['demo'] === 1) : $commtrz->esHojatraVacia();

$idUsuario = $_SESSION['idusu'] ?? null;
$nomUsuario = $_SESSION['nomusu'] ?? 'Usuario';

/** Estado textual de cada paso del flujo. */
$ESTADOS_PASO = [
	1 => 'En Revisión (Instructor)',
	2 => 'En Revisión (Coordinación)',
	3 => 'En Revisión (Administración Educativa)',
	4 => 'En Revisión (Relaciones Corporativas)'
];
$DEPENDENCIA_PASO = [
	1 => 'Instructor',
	2 => 'Coordinación',
	3 => 'Administración Educativa',
	4 => 'Relaciones Corporativas'
];
/** Rol simulado del paso (modo demo: quién está llamado a actuar). */
$ROL_DEMO_PASO = [
	1 => 'Comple. Instructor',
	2 => 'Comple. Coordinador',
	3 => 'Comple. Admon. Educativa',
	4 => 'Comple. Relaciones Corporativas'
];

/**
 * Emite alerta y redirección (evita repetir las llamadas de salida).
 */
function mtrz_redirect($tipo, $mensaje, $id) {
	$cls = ($tipo === 'error') ? 'alert-danger' : 'alert-success';
	echo "<script>Swal.fire({icon: '" . ($tipo === 'error' ? 'error' : 'success') . "', title: '" . htmlspecialchars($mensaje, ENT_QUOTES) . "', timer: 3500, showConfirmButton: false}).then(function(){ window.location='home.php?pg=2016&idnorad={$id}'; });</script>";
	exit;
}

// Procesamiento de acción de flujo de trazabilidad
if ($opera === 'flujo_accion') {
	$accion = strtolower(trim($_POST['accion'] ?? ''));
	$observacion = trim($_POST['observacion'] ?? '');

	if (!$idnorad) {
		mtrz_redirect('error', 'Debe seleccionar una Hoja de Trabajo válida.', '');
	}

	if ($commtrz->esDemo($idnorad) && !$esDemo) {
		$esDemo = true;
	}

	$hoja = $commtrz->getHojaById($idnorad);
	if (!$hoja) {
		mtrz_redirect('error', "La Hoja de Trabajo #{$idnorad} no fue encontrada.", '');
	}

	$pasoActual = (int)$hoja['paso_actual'];
	$estadoActual = $hoja['estado'];
	$devueltoDe = !empty($hoja['devuelto_de']) ? (int)$hoja['devuelto_de'] : null;
	$esFinalizado = ($pasoActual === 4 && stripos($estadoActual, 'Aprobado Final') !== false);

	// 1) Whitelist de acciones permitidas por paso
	$accionesPermitidas = [
		1 => ['enviar'],
		2 => ['aprobar', 'devolver', 'rechazar'],
		3 => ['aprobar', 'devolver', 'rechazar'],
		4 => ['aprobar', 'devolver', 'rechazar']
	];
	if (!in_array($accion, $accionesPermitidas[$pasoActual] ?? [], true)) {
		mtrz_redirect('error', "Acción no permitida para el paso {$pasoActual}.", $idnorad);
	}

	// 2) No se puede devolver una hoja ya finalizada
	if ($esFinalizado && in_array($accion, ['devolver', 'rechazar'], true)) {
		mtrz_redirect('error', 'Esta Hoja de Trabajo ya fue aprobada y cerrada; no se puede devolver.', $idnorad);
	}

	// 3) Devolver / rechazar exige observación
	if (in_array($accion, ['devolver', 'rechazar'], true) && empty($observacion)) {
		mtrz_redirect('error', 'Debe ingresar una observación o motivo detallado para devolver o rechazar la solicitud.', $idnorad);
	}

	// 4) Control de perfil (solo aplica a flujo real; el demo queda libre para probar)
	if (!$esDemo && !$commtrz->puedeActuarEnPaso($idUsuario, $pasoActual)) {
		mtrz_redirect('error', 'Su perfil actual no tiene autorización para actuar en el Paso ' . $pasoActual . ' (' . $DEPENDENCIA_PASO[$pasoActual] . ').', $idnorad);
	}

	$nuevoPaso = $pasoActual;
	$nuevoEstado = $estadoActual;
	$nuevoDevueltoDe = $devueltoDe;
	$accionRegistrada = '';

	// Lógica de transición de estados y pasos
	switch ($pasoActual) {
		case 1: // Paso 1: Creación / Corrección (Instructor)
			if ($devueltoDe) {
				// Regla de Negocio: la hoja corregida regresa DIRECTO a la dependencia remitente
				$nuevoPaso = $devueltoDe;
				$nuevoEstado = $ESTADOS_PASO[$devueltoDe];
				$nuevoDevueltoDe = null;
				$accionRegistrada = 'Reenviado a ' . $DEPENDENCIA_PASO[$devueltoDe];
			} else {
				// Hoja nueva: envío estándar a Coordinación
				$nuevoPaso = 2;
				$nuevoEstado = $ESTADOS_PASO[2];
				$nuevoDevueltoDe = null;
				$accionRegistrada = 'Enviado a Coordinación';
			}
			break;

		case 2: // Paso 2: Revisión (Coordinación)
			if ($accion === 'aprobar') {
				$nuevoPaso = 3;
				$nuevoEstado = $ESTADOS_PASO[3];
				$nuevoDevueltoDe = null;
				$accionRegistrada = 'Aprobado por Coordinación';
			} else {
				$nuevoPaso = 1;
				$nuevoEstado = 'Devuelto por Coordinación';
				$nuevoDevueltoDe = 2;
				$accionRegistrada = 'Devuelto por Coordinación';
			}
			break;

		case 3: // Paso 3: Viabilidad y Registro (Administración Educativa)
			if ($accion === 'aprobar') {
				$nuevoPaso = 4;
				$nuevoEstado = $ESTADOS_PASO[4];
				$nuevoDevueltoDe = null;
				$accionRegistrada = 'Aprobado por Administración Educativa';
			} else {
				$nuevoPaso = 1;
				$nuevoEstado = 'Devuelto por Admin. Educativa';
				$nuevoDevueltoDe = 3;
				$accionRegistrada = 'Devuelto por Admin. Educativa';
			}
			break;

		case 4: // Paso 4: Aprobación Final (Relaciones Corporativas)
			if ($accion === 'aprobar') {
				$nuevoPaso = 4;
				$nuevoEstado = 'Aprobado Final / Cerrado';
				$nuevoDevueltoDe = null;
				$accionRegistrada = 'Aprobación Final Concedida';
			} else {
				$nuevoPaso = 1;
				$nuevoEstado = 'Devuelto por Relaciones Corporativas';
				$nuevoDevueltoDe = 4;
				$accionRegistrada = 'Devuelto por Relaciones Corporativas';
			}
			break;

		default:
			$nuevoPaso = 1;
			$nuevoEstado = $ESTADOS_PASO[1];
			$nuevoDevueltoDe = null;
			$accionRegistrada = 'Reinicio de Flujo';
			break;
	}

	if ($esDemo) {
		$movDemo = [
			'paso_flujo' => $pasoActual,
			'id_usuario' => $idUsuario,
			'rol_usuario' => $ROL_DEMO_PASO[$pasoActual] ?? 'Funcionario SENA',
			'accion' => $accionRegistrada,
			'observacion' => $observacion ?: 'Acción procesada en el flujo de aprobación.',
			'nomusu' => $nomUsuario
		];
		$ok = $commtrz->procesarFlujoDemo($idnorad, $nuevoPaso, $nuevoEstado, $nuevoDevueltoDe, $movDemo);
	} else {
		$movReal = [
			'paso_flujo' => $pasoActual,
			'id_usuario' => $idUsuario,
			'rol_usuario' => $commtrz->resolverRolUsuario($idUsuario, $pasoActual),
			'accion' => $accionRegistrada,
			'observacion' => $observacion ?: 'Acción procesada en el flujo de aprobación.'
		];
		$ok = $commtrz->procesarFlujo($idnorad, $nuevoPaso, $nuevoEstado, $nuevoDevueltoDe, $movReal);
	}

	if (!$ok) {
		mtrz_redirect('error', 'No se pudo registrar la trazabilidad. Intente nuevamente.', $idnorad);
	}

	$msg = $esDemo
		? 'EJEMPLO (memoria): acción "' . $accionRegistrada . '" aplicada a la hoja de demostración. No se guardó en la BD.'
		: 'TRAZABILIDAD REGISTRADA: Acción "' . $accionRegistrada . '" ejecutada con éxito.';
	mtrz_redirect('ok', $msg, $idnorad);
}

// Carga de datos para la vista
$datHoj = $commtrz->getHojas($esDemo);
$hojaSele = $idnorad ? (int)$idnorad : (!empty($datHoj) ? (int)$datHoj[0]['idnorad'] : null);
$hojaDetalle = $hojaSele ? $commtrz->getHojaById($hojaSele) : null;
$historial = $hojaSele ? $commtrz->obtenerHistorialTrazabilidad($hojaSele) : [];
$cnt = $commtrz->getConteos($esDemo);
$puedeActuar = $hojaDetalle ? $commtrz->puedeActuarEnPaso($idUsuario, (int)$hojaDetalle['paso_actual']) : true;
?>