<?php

if (session_status() == PHP_SESSION_NONE && !headers_sent()) {
	session_start();
}

if (defined('COMCHTR_RUN')) return;
define('COMCHTR_RUN', true);

require_once __DIR__ . '/../models/commhtr.php';

$commhtr = new Commhtr();
$icono = isset($icono) ? $icono : 'fa-solid fa-file-contract';

// Manejo de petición AJAX para guardar nueva hoja de trabajo
$opera = isset($_REQUEST['opera']) ? $_REQUEST['opera'] : '';
if ($opera === 'save_ajax' || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' && isset($_POST['codpro']))) {
	while (ob_get_level() > 0) {
		ob_end_clean();
	}
	header('Content-Type: application/json; charset=utf-8');
	try {
		$idnorad = !empty($_POST['idnorad']) ? (int)$_POST['idnorad'] : $commhtr->getNextIdnorad();
		$codpro = !empty($_POST['codpro']) ? $_POST['codpro'] : null;
		$idemp = !empty($_POST['idemp']) ? (int)$_POST['idemp'] : null;
		$codproesp = !empty($_POST['codproesp']) ? (int)$_POST['codproesp'] : null;
		$codslem = !empty($_POST['codslem']) ? trim($_POST['codslem']) : null;
		$feclini = !empty($_POST['feclini']) ? $_POST['feclini'] : null;
		$feclin = !empty($_POST['feclin']) ? $_POST['feclin'] : null;
		$cupo = isset($_POST['cupo']) && $_POST['cupo'] !== '' ? (int)$_POST['cupo'] : null;
		$jornada = !empty($_POST['jornada']) ? $_POST['jornada'] : null;
		$idfic = !empty($_POST['idfic']) ? trim($_POST['idfic']) : null;
		$convht = !empty($_POST['convht']) ? trim($_POST['convht']) : null;

		if (!$codpro || !$idemp || !$codproesp || !$feclini || !$feclin || $cupo === null || !$jornada) {
			echo json_encode([
				'success' => false,
				'message' => 'Por favor complete todos los campos obligatorios (*).'
			]);
			exit;
		}

		if ($cupo <= 0) {
			echo json_encode([
				'success' => false,
				'message' => 'El cupo debe ser mayor a 0.'
			]);
			exit;
		}

		if (strtotime($feclin) < strtotime($feclini)) {
			echo json_encode([
				'success' => false,
				'message' => 'La fecha de terminación no puede ser anterior a la fecha de inicio.'
			]);
			exit;
		}

		$commhtr->setIdnorad($idnorad);
		$existe = $commhtr->getOne();
		if (!empty($existe)) {
			echo json_encode([
				'success' => false,
				'message' => "El número de radicado #$idnorad ya se encuentra registrado. Ingrese otro o use el sugerido."
			]);
			exit;
		}

		$guardado = $commhtr->save([
			'idnorad' => $idnorad,
			'codpro' => $codpro,
			'idemp' => $idemp,
			'codproesp' => $codproesp,
			'codslem' => $codslem,
			'feclini' => $feclini,
			'feclin' => $feclin,
			'cupo' => $cupo,
			'jornada' => $jornada,
			'idfic' => $idfic,
			'convht' => $convht
		]);

		if ($guardado) {
			if (isset($_SESSION['idusu']) && !empty($_SESSION['idusu'])) {
				$commhtr->saveHxU($idnorad, $_SESSION['idusu']);
			}

			$commhtr->setIdnorad($idnorad);
			$nuevo = $commhtr->getOne();
			$datosNuevo = !empty($nuevo) ? $nuevo[0] : [
				'idnorad' => $idnorad,
				'codpro' => $codpro,
				'idemp' => $idemp,
				'feclini' => $feclini,
				'feclin' => $feclin,
				'cupo' => $cupo,
				'jornada' => $jornada,
				'idfic' => $idfic
			];

			$conteos = $commhtr->getConteos();

			echo json_encode([
				'success' => true,
				'message' => 'Hoja de trabajo registrada exitosamente.',
				'data' => $datosNuevo,
				'stats' => !empty($conteos) ? $conteos[0] : null,
				'nextId' => $commhtr->getNextIdnorad()
			]);
		} else {
			echo json_encode([
				'success' => false,
				'message' => 'No se pudo registrar la hoja de trabajo en la base de datos.'
			]);
		}
	} catch (Throwable $e) {
		while (ob_get_level() > 0) {
			ob_end_clean();
		}
		header('Content-Type: application/json; charset=utf-8');
		echo json_encode([
			'success' => false,
			'message' => 'Error en el servidor: ' . $e->getMessage()
		]);
	}
	exit;
}

$idnorad = isset($_REQUEST['id']) ? $_REQUEST['id'] : NULL;

$datos = NULL;
$modo = 'pagina';

if ($idnorad !== NULL && $idnorad !== '') {
	$commhtr->setIdnorad($idnorad);
	$datOne = $commhtr->getOne();
	if (!empty($datOne)) {
		$res = $datOne[0];
		$modo = 'reporte';
		$datos = [
			'idnorad' => $res['idnorad'],
			'codpro' => $res['codpro'],
			'idemp' => $res['idemp'],
			'codproesp' => !empty($res['proesp']) ? $res['proesp'] : $res['codproesp'],
			'codslem' => !empty($res['codslem']) ? $res['codslem'] : '',
			'feclini' => !empty($res['feclini']) ? $res['feclini'] : '',
			'feclin' => !empty($res['feclin']) ? $res['feclin'] : '',
			'fecha_inicio' => !empty($res['feclini']) ? $res['feclini'] : '',
			'fecha_fin' => !empty($res['feclin']) ? $res['feclin'] : '',
			'cupo' => $res['cupo'],
			'jornada' => !empty($res['nomjornada']) ? $res['nomjornada'] : $res['jornada'],
			'codigo_ficha' => !empty($res['idfic']) ? $res['idfic'] : '',
			'idfic' => !empty($res['idfic']) ? $res['idfic'] : '',
			'nomfic' => !empty($res['nomfic']) ? $res['nomfic'] : '',
			'nompro' => !empty($res['nompro']) ? $res['nompro'] : '',
			'nomemp' => !empty($res['nomemp']) ? $res['nomemp'] : '',
			'numdocemp' => !empty($res['numdocemp']) ? $res['numdocemp'] : '',
			'convenio' => !empty($res['convht']) ? $res['convht'] : '',
			'convht' => !empty($res['convht']) ? $res['convht'] : '',
			'duracion' => !empty($res['horlpro']) ? ($res['horlpro'] . ' horas') : '',
			'caracterizacion' => '',
			'programa_ser' => '',
			'aula_movil' => '',
			'bilingüismo' => '',
			'atencion_instituciones' => '',
			'fortalecimiento' => '',
			'mipymes' => '',
			'direccion' => !empty($res['diremp']) ? $res['diremp'] : '',
			'municipio' => !empty($res['nommun']) ? $res['nommun'] : '',
			'contacto' => !empty($res['nomconemp']) ? $res['nomconemp'] : '',
			'telefono' => !empty($res['telemp']) ? $res['telemp'] : '',
			'correo_empresa' => !empty($res['coremp']) ? $res['coremp'] : '',
			'dias_formacion_json' => '',
			'nombre_instructor' => !empty($res['nombre_instructor']) ? $res['nombre_instructor'] : '',
			'documento_instructor' => !empty($res['documento_instructor']) ? $res['documento_instructor'] : '',
			'cel_instructor' => !empty($res['cel_instructor']) ? $res['cel_instructor'] : '',
			'correo_instructor' => !empty($res['correo_instructor']) ? $res['correo_instructor'] : '',
			'control_apoyo' => '',
			'control_complementaria' => ''
		];
	}
}

$dat = $commhtr->getAll();
$cnt = $commhtr->getConteos();

// Catálogos para el modal de nueva hoja de trabajo
$datPr = $commhtr->getProgramas();
$datEm = $commhtr->getEmpresas();
$datPe = $commhtr->getProgramasEspeciales();
$datJo = $commhtr->getJornadas();
$datFi = $commhtr->getFichas();
$siguienteRadicado = $commhtr->getNextIdnorad();

if ($modo === 'reporte' && !defined('COMVHTR_RUN')) {
	include 'views/comvhtr.php';
}
?>