<?php
require_once('models/votmpro.php');

$mpro = new Votmpro();
$idusu = isset($_REQUEST['idusu']) ? $_REQUEST['idusu'] : $_SESSION['idusu'];
$opera = isset($_POST['opera']) ? $_POST['opera'] : NULL;

$pg = 1207;
$ok = false;
$okVid = false;
$vidErr = NULL;

/* Guardar / actualizar la propuesta (upsert) */
if ($opera == "Insertar" && $idusu) {
	$texpro = isset($_POST['texpro']) ? $_POST['texpro'] : array();
	$idval  = isset($_POST['idval']) ? $_POST['idval'] : array();
	$npro   = isset($_POST['npro']) ? $_POST['npro'] : array();

	if ($texpro && $idval) {
		$mpro->setIdusu($idusu);
		$next = NULL;
		for ($i = 0; $i < count($texpro); $i++) {
			$mpro->setTexpro($texpro[$i]);
			$mpro->setIdval($idval[$i]);
			$pk = isset($npro[$i]) && $npro[$i] != '' ? $npro[$i] : NULL;
			if ($pk) {
				$mpro->setNpro($pk);
				$mpro->edit();
			} else {
				if ($next === NULL) $next = $mpro->maxId() + 1;
				$mpro->setNpro($next++);
				$mpro->save();
			}
		}
		$ok = true;
	}
}

/* Subir / reemplazar el video de la propuesta del candidato */
if ($idusu && isset($_FILES['vidpro']) && $_FILES['vidpro']['name'] != '') {
	$docext = strtolower(pathinfo($_FILES['vidpro']['name'], PATHINFO_EXTENSION));
	$permit = array('mp4', 'webm', 'mov', 'avi', 'm4v', 'ogv');
	if ($_FILES['vidpro']['error'] != UPLOAD_ERR_OK) {
		if (in_array($_FILES['vidpro']['error'], array(UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE))) {
			$vidErr = "El video supera el límite del servidor (" . ini_get('upload_max_filesize') . "). Redúcelo e inténtalo de nuevo.";
		} else {
			$vidErr = "Ocurrió un error al cargar el video (código " . (int)$_FILES['vidpro']['error'] . "). Inténtalo de nuevo.";
		}
	} elseif (!in_array($docext, $permit)) {
		$vidErr = "Extensión no permitida. Solo se admiten: mp4, webm, mov, avi, m4v, ogv.";
	} elseif ($_FILES['vidpro']['size'] >= 100741824) {
		$vidErr = "El video es demasiado grande. El peso máximo permitido es de 97Mb.";
	} else {
		if (!is_dir('videos')) {
			@mkdir('videos', 0777, true);
			@chmod('videos', 0777);
		}
		$nombre = 'prop_' . $idusu . '_' . time() . '.' . $docext;
		if (move_uploaded_file($_FILES['vidpro']['tmp_name'], 'videos/' . $nombre)) {
			$mpro->setIdusu($idusu);
			$mpro->seeVideo();
			$mpro->saveVideo("Video de la propuesta", $nombre);
			$okVid = true;
		} else {
			$vidErr = "No se pudo guardar el video. Verifique los permisos de la carpeta.";
		}
	}
}

/* Tipo MIME del video según su extensión (para la etiqueta <source>) */
if (!function_exists('vidmime')) {
	function vidmime($fname) {
		$ext = strtolower(pathinfo($fname, PATHINFO_EXTENSION));
		$m = array(
			'mp4' => 'video/mp4', 'm4v' => 'video/mp4', 'webm' => 'video/webm',
			'mov' => 'video/quicktime', 'qt' => 'video/quicktime',
			'avi' => 'video/x-msvideo', 'ogv' => 'video/ogg'
		);
		return isset($m[$ext]) ? $m[$ext] : 'video/mp4';
	}
}

/* Datos para mostrar la vista */
$dcon = $mpro->getVal(2);
$dvpr = $mpro->getVal(3);
$dman = $mpro->getVal(4);

$datOne = NULL;
$datC   = NULL;
$datM   = NULL;
$dus    = NULL;

if ($idusu) {
	$mpro->setIdusu($idusu);
	$datOne = $mpro->getOne(3);
	$datC   = $mpro->getOne(2);
	$datM   = $mpro->getOne(4);
	$dus    = $mpro->getUsu();
	$vid    = $mpro->getVideo();
} else {
	$vid = NULL;
}

/* Perfiles Aprendiz: solo a un estudiante se le muestra ficha y jornada */
$perEst = array(4, 8, 22, 23, 39, 41);
$esEst  = ($dus && in_array((int)$dus[0]['idper'], $perEst)) ? true : false;

/**
 * Decodifica entidades HTML de los textos administrativos (valor/usuario)
 * para conservar la presentación de la aplicación original.
 */
if (!function_exists('txlbl')) {
	function txlbl($s) {
		return html_entity_decode($s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
	}
}

/**
 * Genera una tarjeta de selección Sí/No (Condiciones y Manifiesto).
 */
function dbche($tit, $vec, $dat, $reado = false) {
	if (!$vec) return '';
	$html = '<section class="bg-white border rounded-4 shadow-sm mb-4 overflow-hidden">';
		$html .= '<header class="d-flex align-items-center gap-3 px-4 py-3 bg-success-subtle border-bottom">'
			. '<span class="w-8 h-8 rounded-circle bg-success text-white d-inline-flex align-items-center justify-content-center flex-shrink-0"><i class="fa-solid fa-clipboard-check"></i></span>'
			. '<h2 class="fs-5 fw-bold mb-0 pb-0">' . txlbl($tit) . '</h2>'
			. '</header>';
		$html .= '<div class="p-4"><div class="row g-3">';
		$i = 0;
		foreach ($vec as $dv) {
			$val = ($dat && isset($dat[$i]['texpro'])) ? $dat[$i]['texpro'] : NULL;
			$html .= '<div class="col-md-6">';
				$html .= '<label class="form-label fw-bold fs-6" for="texpr_s_' . $i . '">' . txlbl($dv['nomval']) . '</label>';
				$html .= '<select name="texpro[]" id="texpr_s_' . $i . '" class="form-select rounded-3"' . ($reado ? ' disabled' : '') . '>';
				if (!$val) {
					$html .= '<option value="" selected>Seleccione</option>';
				}
				$html .= '<option value="No"' . (($val == "No") ? ' selected' : '') . '>No</option>';
				$html .= '<option value="Si"' . (($val == "Si") ? ' selected' : '') . '>Si</option>';
			$html .= '</select>';
				$html .= '<input type="hidden" name="idval[]" value="' . (int)$dv['idval'] . '">';
				$html .= '<input type="hidden" name="npro[]" value="' . (($val && isset($dat[$i]['npro'])) ? (int)$dat[$i]['npro'] : '') . '">';
			$html .= '</div>';
			$i++;
		}
		$html .= '</div></div>';
	$html .= '</section>';
	return $html;
}