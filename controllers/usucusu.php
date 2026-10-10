<?php
if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

// Rutas absolutas basadas en la ubicación real del controlador.
// Funcionan tanto cuando el controlador es incluido desde home.php como
// cuando recibe peticiones AJAX directamente.
require_once dirname(__DIR__) . '/models/conexion.php';
require_once dirname(__DIR__) . '/models/usumusu.php';

$musu = new Usumusu();
$idusu = isset($_REQUEST['idusu']) ? $_REQUEST['idusu'] : NULL;
$ndocusu = isset($_POST['ndocusu']) ? $_POST['ndocusu'] : NULL;
$nomusu = isset($_POST['nomusu']) ? $_POST['nomusu'] : NULL;
$idper = isset($_POST['idper']) ? $_POST['idper'] : NULL;
$idfic = isset($_REQUEST['idfic']) ? $_REQUEST['idfic'] : NULL;
$pasusu = isset($_POST['pasusu']) ? $_POST['pasusu'] : NULL;
$optimgPath = __DIR__ . '/optimg.php';
if (file_exists($optimgPath)) {
	require_once $optimgPath;
}

$idcen = isset($_POST['idcen']) ? $_POST['idcen'] : NULL;
$actusu = isset($_POST['actusu']) ? $_POST['actusu'] : NULL;
$emausu = isset($_POST['emausu']) ? trim($_POST['emausu']) : NULL;

// Mejora 4.1: Validación estricta y restricción en campo de teléfono (solo números, máximo 10 dígitos)
$telcan = NULL;
if (isset($_POST['telcan'])) {
	$telLimpio = preg_replace('/\D/', '', $_POST['telcan']);
	$telcan = !empty($telLimpio) ? substr($telLimpio, 0, 10) : NULL;
}

$noca = isset($_POST['noca']) ? $_POST['noca'] : NULL;
$fotcan = isset($_POST['fotcan']) ? $_POST['fotcan'] : NULL;
$rhusu = isset($_POST['rhusu']) ? $_POST['rhusu'] : NULL;
$tdousu = isset($_POST['tdousu']) ? $_POST['tdousu'] : NULL;
$genusu = isset($_POST['genusu']) ? $_POST['genusu'] : 2009;

$idficfil = (!empty($_REQUEST['idficfil']) && $_REQUEST['idficfil'] != '0') ? trim($_REQUEST['idficfil']) : NULL;
$nodocfil = !empty($_REQUEST['nodocfil']) ? trim($_REQUEST['nodocfil']) : NULL;

$foto = isset($_FILES['foto']['name']) ? $_FILES['foto']['name'] : NULL;
$opera = isset($_REQUEST['opera']) ? $_REQUEST['opera'] : NULL;

$ips = isset($_POST['inico']) ? $_POST['inico'] : "";
$fps = isset($_POST['finco']) ? $_POST['finco'] : "";

$datOne = NULL;

$isDatosPersonales = (isset($pg) && ($pg == 1102 || $pg == 1324)) || !empty($_POST['is_datos_personales']);
if ($isDatosPersonales) {
	if (!isset($pg) || ($pg != 1102 && $pg != 1324)) {
		$pg = 1324;
	}
	$idusu = $_SESSION["idusu"] ?? ($idusu ?? null);
} else {
	$pg = 1113;
}

$musu->setIdusu($idusu);

// Mejora 2 y 3: Procesamiento dinámico de fotografía
$msgErrorFoto = null;

// Regla específica para Datos Personales: solo se puede cambiar la foto de perfil una sola vez
$bloquearCambioDatosPersonales = false;
if ($isDatosPersonales && $idusu) {
	$datPrevia = $musu->getOne();
	$fotoExistente = trim($datPrevia[0]['fotcan'] ?? '');
	$yaTieneFoto = (!empty($fotoExistente) && $fotoExistente !== 'fcan/' && $fotoExistente !== 'img/user.jpg' && $fotoExistente !== 'image/user-default.webp');
	if ($yaTieneFoto) {
		$fotcan = $fotoExistente;
		$bloquearCambioDatosPersonales = true;
		if (!empty($_FILES['foto']['name'])) {
			$msgErrorFoto = "aviso_biblioteca";
		}
	}
}

if (!$bloquearCambioDatosPersonales && !empty($_FILES['foto']['name']) && isset($_FILES['foto']['error']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
	$fileUpload = $_FILES['foto'];
	$ext = strtolower(pathinfo($fileUpload['name'], PATHINFO_EXTENSION));
	$formatosValidos = ['png', 'jpg', 'jpeg', 'webp', 'jfif', 'gif'];

	if (in_array($ext, $formatosValidos)) {
		if (!file_exists('fcan')) {
			@mkdir('fcan', 0777, true);
		}

		$identificador = $idusu ? $idusu : (!empty($ndocusu) ? preg_replace('/\D/', '', $ndocusu) : ('u_' . time()));
		$nombreArchivo = $identificador . "_" . time() . "." . $ext;
		$rutaDestino = "fcan/" . $nombreArchivo;

		$guardado = false;
		// Solo invocar opti si la librería GD de PHP está habilitada con soporte de funciones de imagen
		$tieneGd = extension_loaded('gd') && function_exists('imagecreatefromjpeg') && function_exists('imagecreatetruecolor');
		if ($tieneGd && in_array($ext, ['png', 'jpg', 'jpeg', 'jfif']) && function_exists('opti')) {
			try {
				$nombreOpti = opti($fileUpload, $identificador, 'fcan', (string)time());
				if ($nombreOpti && file_exists('fcan/' . $nombreOpti)) {
					$fotcan = "fcan/" . $nombreOpti;
					$guardado = true;
				}
			} catch (Throwable $e) {
				$guardado = false;
			}
		}

		// Si GD no está habilitado en el servidor o opti no procesó la imagen, guardar directamente con move_uploaded_file
		if (!$guardado) {
			if (move_uploaded_file($fileUpload['tmp_name'], $rutaDestino)) {
				$fotcan = $rutaDestino;
				$guardado = true;
			}
		}

		if ($guardado) {
			// Si el usuario tenía foto previa en fcan, limpiar el archivo anterior
			if ($idusu) {
				$datPrevia = $musu->getOne();
				$fotoPrevia = trim($datPrevia[0]['fotcan'] ?? '');
				if (!empty($fotoPrevia) && strpos($fotoPrevia, 'fcan/') === 0 && file_exists($fotoPrevia) && $fotoPrevia !== $fotcan) {
					@unlink($fotoPrevia);
				}
			}

			if ($idusu && isset($_SESSION['idusu']) && $_SESSION['idusu'] == $idusu) {
				$_SESSION['fotcan'] = $fotcan;
			}
		}
	} else {
		// Extensión no válida: no procesar y registrar advertencia
		$msgErrorFoto = "ext_invalida";
	}
} elseif ($idusu && empty($fotcan)) {
	// Si no se cargó una nueva imagen ni se envió fotcan, conservar la fotografía previa registrada en BD
	$datPrevia = $musu->getOne();
	if (!empty($datPrevia[0]['fotcan'])) {
		$fotcan = $datPrevia[0]['fotcan'];
	}
}

// URL para preservar filtros en redirecciones
$urlFiltro = "home.php?pg=$pg" . ($nodocfil ? "&nodocfil=" . urlencode($nodocfil) : "") . ($idficfil ? "&idficfil=" . urlencode($idficfil) : "");

// Acción dinámica: Alternar estado Activo / Inactivo
if($opera == "toggle_act" && $idusu){
	$newStatus = $musu->toggleActivo($idusu);
	$isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') || isset($_REQUEST['ajax']);
	if($isAjax){
		while (ob_get_level()) {
			ob_end_clean();
		}
		header('Content-Type: application/json; charset=utf-8');
		echo json_encode([
			'success' => true,
			'idusu' => $idusu,
			'actusu' => $newStatus,
			'status_text' => ($newStatus == 1 ? 'Activo' : 'Inactivo')
		]);
		exit;
	}
	echo "<script>window.location.href='$urlFiltro&msg=estado_ok';</script>";
	exit;
}

// Acción dinámica: Cambiar estado de usuario (1: Activo, 2: Inactivo, 3: Desertó)
if($opera == "cambiar_estado" && $idusu){
	$actusu_val = isset($_REQUEST['actusu_val']) ? (int)$_REQUEST['actusu_val'] : 1;
	$newStatus = $musu->updateEstado($idusu, $actusu_val);
	$statusText = 'Inactivo';
	if($newStatus == 1) $statusText = 'Activo';
	else if($newStatus == 3) $statusText = 'Desertó';

	$isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') || isset($_REQUEST['ajax']);
	if($isAjax){
		while (ob_get_level()) {
			ob_end_clean();
		}
		header('Content-Type: application/json; charset=utf-8');
		echo json_encode([
			'success' => true,
			'idusu' => $idusu,
			'actusu' => $newStatus,
			'status_text' => $statusText
		]);
		exit;
	}
	echo "<script>window.location.href='$urlFiltro&msg=estado_ok';</script>";
	exit;
}

// Insertar / Actualizar
if($opera == "save"){
	$musu->setNdocusu($ndocusu);
	$musu->setNomusu($nomusu);
	$musu->setIdper($idper);
	$musu->setPasusu($pasusu);
	$musu->setEmausu($emausu);
	$musu->setIdcen($idcen);
	$musu->setActusu($actusu);
	$musu->setFotcan($fotcan);
	$musu->setTelcan($telcan);
	$musu->setNoca($noca);
	$musu->setRhusu($rhusu);
	$musu->setTdousu($tdousu);
	$musu->setGenusu($genusu);
	
	if(!$idusu){
		$musu->save();
		$msg = "guardado";
	}else{
		$musu->edi();
		$msg = "actualizado";
		if(isset($_SESSION['idusu']) && $_SESSION['idusu'] == $idusu){
			if(!empty($nomusu)) $_SESSION['nomusu'] = $nomusu;
			if(!empty($fotcan)) $_SESSION['fotcan'] = $fotcan;
		}
	}
	$extraParam = !empty($msgErrorFoto) ? "&err=$msgErrorFoto" : "";
	echo "<script>window.location.href='$urlFiltro&msg=$msg$extraParam';</script>";
	exit;
}

// Cargar para edición
if($opera == "edit" || $opera == "edi"){
	$datOne = $musu->getOne();
}

// Actualización masiva de contraseñas
if($opera == "CamCon"){
	$cant = $musu->updPascParam($ips, $fps);
	$idusu = "";
	echo "<script>window.location.href='$urlFiltro&msg=pass_ok&cant=' + " . (int)$cant . ";</script>";
	exit;
}

// =========================================================================
// MEJORA 3.1 & 3.2: ACTUALIZACIÓN DE PERFILES Y CONTROL RBAC EN TIEMPO REAL
// =========================================================================

// Sincronización automática de permisos en tiempo real al cargar el controlador
if (isset($_SESSION['idusu'])) {
	$musu->sincronizarPermisosSesion((int)$_SESSION['idusu']);
}

// Endpoint AJAX: Sincronización forzada de permisos RBAC en tiempo real
if ($opera == "sync_permisos" && isset($_SESSION['idusu'])) {
	$syncData = $musu->sincronizarPermisosSesion((int)$_SESSION['idusu']);
	while (ob_get_level()) { ob_end_clean(); }
	header('Content-Type: application/json; charset=utf-8');
	echo json_encode([
		'success' => true,
		'data' => $syncData,
		'message' => 'Permisos sincronizados en tiempo real.'
	]);
	exit;
}

// Asignación Masiva de Perfiles en Lote (Mejora 3.1 & 3.2)
if ($opera == "InUP_masivo") {
	$usuariosRaw = isset($_POST['usuarios_ids']) ? $_POST['usuarios_ids'] : (isset($_REQUEST['usuarios_ids']) ? $_REQUEST['usuarios_ids'] : []);
	if (is_string($usuariosRaw)) {
		$usuariosArr = json_decode($usuariosRaw, true);
		if (!is_array($usuariosArr)) {
			$usuariosArr = array_filter(array_map('trim', explode(',', $usuariosRaw)));
		}
	} else {
		$usuariosArr = (array)$usuariosRaw;
	}

	$usuariosArr = array_values(array_unique(array_filter(array_map('intval', $usuariosArr))));
	$perfilesArr = isset($_POST['idper']) ? (array)$_POST['idper'] : [];
	$perfilPrincipal = !empty($_POST['perfil_principal']) ? (int)$_POST['perfil_principal'] : null;

	$isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') || isset($_REQUEST['ajax']);

	if (empty($usuariosArr)) {
		if ($isAjax) {
			while (ob_get_level()) { ob_end_clean(); }
			header('Content-Type: application/json; charset=utf-8');
			echo json_encode(['success' => false, 'error' => 'No se seleccionaron usuarios para la asignación masiva.']);
			exit;
		}
		echo "<script>alert('No se seleccionaron usuarios'); window.location.href='$urlFiltro';</script>";
		exit;
	}

	$resMasivo = $musu->asignarPerfilesLote($usuariosArr, $perfilesArr, $perfilPrincipal);

	// Si el usuario en sesión actual fue modificado, sincronizar su sesión de inmediato
	if (isset($_SESSION['idusu']) && in_array((int)$_SESSION['idusu'], $usuariosArr)) {
		$musu->sincronizarPermisosSesion((int)$_SESSION['idusu']);
	}

	if ($isAjax) {
		while (ob_get_level()) { ob_end_clean(); }
		header('Content-Type: application/json; charset=utf-8');
		echo json_encode([
			'success' => $resMasivo['success'],
			'procesados' => $resMasivo['procesados'] ?? 0,
			'usuarios' => $resMasivo['usuarios'] ?? [],
			'message' => $resMasivo['mensaje'] ?? 'Asignación masiva finalizada con éxito.',
			'permisos_actualizados' => true
		]);
		exit;
	}

	echo "<script>window.location.href='$urlFiltro&msg=perfil_masivo_ok&cant=' + " . (int)($resMasivo['procesados'] ?? 0) . ";</script>";
	exit;
}

// Actualización individual de perfiles (Mejora 3.1 & 3.2 con soporte AJAX y sincronización RBAC)
if ($opera == "InUP") {
	$isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') || isset($_REQUEST['ajax']);
	$perfilPrincipal = !empty($_POST['perfil_principal']) ? (int)$_POST['perfil_principal'] : null;

	if (empty($idusu)) {
		if ($isAjax) {
			while (ob_get_level()) { ob_end_clean(); }
			header('Content-Type: application/json; charset=utf-8');
			echo json_encode(['success' => false, 'error' => 'Identificador de usuario no válido o no especificado.']);
			exit;
		}
		echo "<script>window.location.href='$urlFiltro';</script>";
		exit;
	}

	if ($idusu) {
		// 1. Limpiar perfiles secundarios previos
		$musu->setIdusu($idusu);
		$musu->delUxP();

		// 2. Insertar perfiles seleccionados por módulo
		if (!empty($idper)) {
			$idperArr = is_array($idper) ? $idper : [$idper];
			$idperArr = array_unique(array_filter(array_map('intval', $idperArr)));
			foreach ($idperArr as $idx) {
				if ($idx > 0) {
					$musu->setIdper($idx);
					$musu->insUxP();
				}
			}
		}

		// 3. Actualizar perfil principal si se indicó
		if ($perfilPrincipal && $perfilPrincipal > 0) {
			$musu->setIdper($perfilPrincipal);
			// Actualización directa de idper en tabla usuario
			$modConex = new conexion();
			$dbCon = $modConex->get_conexion();
			$stmtUpP = $dbCon->prepare("UPDATE usuario SET idper = :idper WHERE idusu = :idusu");
			$stmtUpP->bindParam(':idper', $perfilPrincipal, PDO::PARAM_INT);
			$stmtUpP->bindParam(':idusu', $idusu, PDO::PARAM_INT);
			$stmtUpP->execute();
		}

		// 4. MEJORA 3.2: Sincronizar permisos en tiempo real
		$syncData = $musu->sincronizarPermisosSesion((int)$idusu);

		// Obtener nombre del perfil principal actualizado para la respuesta
		$nomPerfilNuevo = '';
		if ($perfilPrincipal) {
			$modConex = new conexion();
			$dbCon = $modConex->get_conexion();
			$stmtNomP = $dbCon->prepare("SELECT nomper FROM perfil WHERE idper = :idper");
			$stmtNomP->bindParam(':idper', $perfilPrincipal, PDO::PARAM_INT);
			$stmtNomP->execute();
			$rowNomP = $stmtNomP->fetch(PDO::FETCH_ASSOC);
			$nomPerfilNuevo = $rowNomP['nomper'] ?? '';
		}

		if ($isAjax) {
			while (ob_get_level()) { ob_end_clean(); }
			header('Content-Type: application/json; charset=utf-8');
			echo json_encode([
				'success' => true,
				'idusu' => (int)$idusu,
				'idper_principal' => $perfilPrincipal,
				'nomper_principal' => $nomPerfilNuevo,
				'message' => 'Perfiles de usuario actualizados correctamente en tiempo real.',
				'permisos_sincronizados' => true
			]);
			exit;
		}
	}

	echo "<script>window.location.href='$urlFiltro&msg=perfil_ok';</script>";
	exit;
}

// Asignar Ficha
if($opera == "InUF"){
	if($idfic && $idusu){
		// Si viene con formato "2670123 - PROGRAMA", extraer únicamente el número de ficha
		if(preg_match('/^(\d+)/', trim($idfic), $matchesFic)){
			$idfic = $matchesFic[1];
		}
		if($musu->existeFicha($idfic)){
			$musu->ediUxF();
			$dtuxf = $musu->getFicUsuSec($idusu, $idfic);
			if(!$dtuxf) {
				$musu->insUxF($idusu, $idfic);
				$msg = "ficha_ok";
			} else {
				$msg = "ficha_duplicada";
			}
		} else {
			$msg = "ficha_no_existe";
		}
	} else {
		$msg = "ficha_invalida";
	}
	echo "<script>window.location.href='$urlFiltro&msg=$msg';</script>";
	exit;
}

// Eliminar seguro
if($opera == "Eliminar" && $idusu){
	$resDel = $musu->delSafe($idusu);
	if($resDel['success']){
		$msg = "eliminado";
	} else if(isset($resDel['error']) && $resDel['error'] == 'fk'){
		$msg = "error_fk";
	} else {
		$msg = "error_del";
	}
	echo "<script>window.location.href='$urlFiltro&msg=$msg';</script>";
    exit;
}

// Quitar Ficha
if($opera == "duxf"){
	if($idfic && $idusu) {
		$musu->delUxPF($idusu, $idfic);
	}
	echo "<script>window.location.href='$urlFiltro&msg=ficha_del';</script>";
	exit;
}

// =========================================================================
// MEJORA 1: Búsqueda inteligente y protección contra sobrecarga (>100 usuarios)
// Evita errores y congelamiento al seleccionar fichas con muchos usuarios (ej. Contratistas)
// =========================================================================
$dat = NULL;
$fichaSobrecargada = false;
$totalUsuariosFicha = 0;
$infoFichaSobrecargada = null;
$busquedaDemasiadoAmplia = false;

if($idficfil) {
	// Verificar en tiempo real la cantidad de usuarios vinculados a esta ficha/programa
	$totalUsuariosFicha = $musu->countUsuariosFicha($idficfil);
	$infoFichaSobrecargada = $musu->getFichaInfo($idficfil);

	if($totalUsuariosFicha > 100 && empty($nodocfil)) {
		// Supera los 100 usuarios y no se ha especificado una persona concreta:
		// No cargar la lista completa para evitar sobrecarga y error de memoria/timeout.
		$fichaSobrecargada = true;
		$dat = NULL;
	} else {
		// La ficha tiene <= 100 usuarios O el usuario proporcionó un criterio para filtrar en ella
		$dat = $musu->getAllSearch($nodocfil, $idficfil);
		if($dat && count($dat) > 100) {
			// Si la búsqueda dentro del curso aún arroja más de 100 resultados, solicitar más especificidad
			$fichaSobrecargada = true;
			$busquedaDemasiadoAmplia = true;
			$dat = NULL;
		}
	}
} else if($nodocfil) {
	// Búsqueda global por documento, nombre o correo
	$dat = $musu->getAllSearch($nodocfil, null);
	if($dat && count($dat) > 100) {
		$busquedaDemasiadoAmplia = true;
		$totalUsuariosFicha = count($dat);
		$dat = NULL;
	}
}

// Obtener datos auxiliares para formularios y filtros
$dce = $musu->getCentro();
$dfi = $musu->getFicha();
$dpe = $musu->getPerfil();
$rhs = $musu->getVal(28);
$tdos = $musu->getVal(27);
$gen = $musu->getVal(29);
$tfichas = $musu->getTficha();

// Métricas para Dashboard KPI en tiempo real
$kpis = $musu->getDashboardStats();

if($idusu && empty($datOne)){
	$datOne = $musu->getOne();
}

// Estadísticas de perfil para el Dashboard en tiempo real de Datos Personales (datvdp.php)
$userStats = NULL;
if($idusu && $isDatosPersonales){
	$userStats = $musu->getUserProfileStats($idusu);
}


function modmulsel($id, $nom, $pg, $nodocfil = null, $idficfil = null){
	static $datmdCache = null;
	static $todosPerfilesCache = null;
	static $pefusCache = [];

	$musu = new Usumusu();
	if ($datmdCache === null) {
		$datmdCache = $musu->getMod();
	}
	if ($todosPerfilesCache === null) {
		$todosPerfilesCache = $musu->getPerfil();
	}
	$datmd = $datmdCache;
	$todosPerfiles = $todosPerfilesCache;
	
	// Obtener perfil principal actual del usuario
	$musu->setIdusu($id);
	$datUsu = $musu->getOne();
	$perfilPrincipalActual = $datUsu ? ($datUsu[0]['idper'] ?? null) : null;
	$nomPerfilPrincipalActual = $datUsu ? ($datUsu[0]['nomper'] ?? 'Sin asignar') : 'Sin asignar';
	
	$html = '';
	$html .= '<div class="modal fade modal-perfiles-usu" id="muxp'.$id.'" tabindex="-1" aria-labelledby="lblMuxp'.$id.'" aria-hidden="true">';
		$html .= '<div class="modal-dialog modal-dialog-centered modal-lg">';
			$html .= '<form id="frmPerfilesUsu_'.$id.'" action="home.php?pg='.$pg.'" method="POST" onsubmit="guardarPerfilesIndividual(event, this, '.$id.');">';
				$html .= '<div class="modal-content shadow border-0 rounded-3 overflow-hidden">';
					$html .= '<div class="modal-header bg-success text-white">';
						$html .= '<div class="d-flex align-items-center gap-2">';
							$html .= '<i class="fa-solid fa-id-badge fa-lg"></i>';
							$html .= '<h5 class="modal-title mb-0 fw-bold" id="lblMuxp'.$id.'">Gestionar Perfiles de Usuario</h5>';
						$html .= '</div>';
						$html .= '<button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>';
					$html .= '</div>';
					$html .= '<div class="modal-body p-4">';
						// Banner de usuario seleccionado
						$html .= '<div class="p-3 mb-3 rounded-3 d-flex align-items-center justify-content-between flex-wrap gap-2 bg-light border-start border-4 border-success">';
							$html .= '<div class="d-flex align-items-center gap-3">';
								$html .= '<i class="fa-solid fa-circle-user fa-2x text-success"></i>';
								$html .= '<div>';
									$html .= '<small class="text-muted text-uppercase fw-bold" style="font-size: 0.72rem;">Usuario Seleccionado</small>';
									$html .= '<h6 class="mb-0 fw-bold text-dark fs-6">'.htmlspecialchars($nom).'</h6>';
								$html .= '</div>';
							$html .= '</div>';
							$html .= '<div>';
								$html .= '<span class="badge bg-white text-success border border-success px-3 py-2 fw-semibold" id="badge-modal-perfil-'.$id.'">';
									$html .= '<i class="fa-solid fa-shield-halved me-1"></i> Rol Principal: <strong>'.htmlspecialchars($nomPerfilPrincipalActual).'</strong>';
								$html .= '</span>';
							$html .= '</div>';
						$html .= '</div>';

						// Alerta de sincronización en tiempo real
						$html .= '<div class="alert alert-light border border-success border-opacity-25 rounded-3 py-2 px-3 mb-3 d-flex align-items-center gap-2 small text-muted">';
							$html .= '<i class="fa-solid fa-bolt text-success"></i>';
							$html .= '<span><strong>Actualización en tiempo real:</strong> Los permisos se sincronizan inmediatamente en el control de acceso (RBAC) sin requerir que el usuario cierre sesión.</span>';
						$html .= '</div>';
						
						// Selector de Perfil Principal
						$html .= '<div class="card border border-success border-opacity-25 p-3 mb-4 bg-light rounded-3 shadow-sm">';
							$html .= '<label class="form-label fw-bold text-dark mb-1 small d-flex align-items-center justify-content-between" for="perfil_principal_'.$id.'">';
								$html .= '<span><i class="fa-solid fa-user-shield me-1 text-success"></i> Perfil Principal del Sistema <span class="text-danger">*</span></span>';
								$html .= '<small class="text-muted fw-normal">Define el rol primario del usuario en el sistema</small>';
							$html .= '</label>';
							$html .= '<select name="perfil_principal" id="perfil_principal_'.$id.'" class="form-select form-select-sm fw-semibold" required>';
								if($todosPerfiles){ foreach($todosPerfiles as $tp){
									$isPrinSel = ($perfilPrincipalActual == $tp['idper']) ? 'selected' : '';
									$html .= '<option value="'.$tp['idper'].'" '.$isPrinSel.'>'.htmlspecialchars($tp['nomper']).'</option>';
								}}
							$html .= '</select>';
						$html .= '</div>';

						// Perfiles por Módulo del Sistema
						$html .= '<div class="d-flex align-items-center justify-content-between mb-2">';
							$html .= '<h6 class="fw-bold text-dark mb-0 fs-7">';
								$html .= '<i class="fa-solid fa-cubes-stacked me-1 text-success"></i> Perfiles Secundarios por Módulo';
							$html .= '</h6>';
							$html .= '<small class="text-muted">Seleccione los roles correspondientes a cada módulo</small>';
						$html .= '</div>';

						$html .= '<div class="row g-3">';
							if($datmd){ foreach($datmd AS $dmd){
								if (!isset($pefusCache[$dmd['idmod']])) {
									$pefusCache[$dmd['idmod']] = $musu->getPefus($dmd['idmod']);
								}
								$datpu = $pefusCache[$dmd['idmod']];
								$datup = $musu->getUsPe($dmd['idmod'], $id);
								$html .= '<div class="col-md-6">';
									$html .= '<div class="card border h-100 p-2 bg-white shadow-sm rounded-3 card-modulo-perfil">';
										$html .= '<label class="form-label fw-bold small text-dark mb-1" for="idper_'.$id.'_'.$dmd['idmod'].'">';
											$html .= '<i class="fa-solid fa-cube me-1 text-success"></i>'.htmlspecialchars($dmd['nommod']);
										$html .= '</label>';
										$html .= '<select name="idper[]" id="idper_'.$id.'_'.$dmd['idmod'].'" class="form-select form-select-sm">';
											$html .= '<option value="0">-- Sin perfil asignado --</option>';
											if($datpu){ foreach($datpu AS $dp){
												$isSelected = ($datup && $datup[0]['idper'] == $dp['idper']) ? 'selected' : '';
												$html .= '<option value="'.$dp['idper'].'" '.$isSelected.'>'.htmlspecialchars($dp['nomper']).'</option>';
											}}
										$html .= '</select>';
									$html .= '</div>';
								$html .= '</div>';
							}}
						$html .= '</div>';
					$html .= '</div>';
					$html .= '<div class="modal-footer bg-light px-4 py-3 d-flex justify-content-between">';
						$html .= '<button type="button" class="btn btn-secondary px-3 rounded-pill" data-bs-dismiss="modal">';
							$html .= '<i class="fa-solid fa-xmark me-1"></i> Cancelar';
						$html .= '</button>';
						$html .= '<button type="submit" id="btnGuardarPef_'.$id.'" class="btn btn-success px-4 rounded-pill fw-bold">';
							$html .= '<i class="fa-solid fa-floppy-disk me-1"></i> Guardar Cambios';
						$html .= '</button>';
						$html .= '<input type="hidden" name="idusu" value="'.$id.'">';
						$html .= '<input type="hidden" name="opera" value="InUP">';
						if($nodocfil) $html .= '<input type="hidden" name="nodocfil" value="'.htmlspecialchars($nodocfil).'">';
						if($idficfil) $html .= '<input type="hidden" name="idficfil" value="'.htmlspecialchars($idficfil).'">';
					$html .= '</div>';
				$html .= '</div>';
			$html .= '</form>';
		$html .= '</div>';
	$html .= '</div>';
	return $html;
}

function modsimsel($id, $nom, $pg, $nodocfil = null, $idficfil = null){
	static $optionsFichasCache = null;
	static $datalistFichasCache = null;

	$musu = new Usumusu();
	if ($optionsFichasCache === null || $datalistFichasCache === null) {
		$datpu = $musu->getFicha();
		$optionsFichasCache = '';
		$datalistFichasCache = '';
		if($datpu){
			foreach($datpu AS $dp){
				$textoFicha = $dp['idfic'] . ' - ' . htmlspecialchars($dp['nomfic']);
				$optionsFichasCache .= '<option value="'.$dp['idfic'].'">'.$textoFicha.'</option>';
				$datalistFichasCache .= '<option value="'.$dp['idfic'].'">'.$textoFicha.(!empty($dp['nomval']) ? ' ('.htmlspecialchars($dp['nomval']).')' : '').'</option>';
			}
		}
	}
	$optionsFichas = $optionsFichasCache;
	$datalistFichas = $datalistFichasCache;
	$datfr = $musu->getFicUsu($id);

	$html = '';
	$html .= '<div class="modal fade" id="muxf'.$id.'" tabindex="-1" aria-labelledby="lblMuxf'.$id.'" aria-hidden="true">';
		$html .= '<div class="modal-dialog modal-dialog-centered modal-lg">';
			$html .= '<form action="home.php?pg='.$pg.'" method="POST">';
				$html .= '<div class="modal-content shadow border-0 rounded-3">';
					$html .= '<div class="modal-header bg-success text-white">';
						$html .= '<div class="d-flex align-items-center gap-2">';
							$html .= '<i class="fa-solid fa-graduation-cap fa-lg"></i>';
							$html .= '<h5 class="modal-title mb-0 fw-bold" id="lblMuxf'.$id.'">Fichas Vinculadas al Usuario</h5>';
						$html .= '</div>';
						$html .= '<button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>';
					$html .= '</div>';
					$html .= '<div class="modal-body p-4">';
						$html .= '<div class="p-3 mb-4 rounded-3 d-flex align-items-center gap-3 bg-light border-start border-4 border-success">';
							$html .= '<i class="fa-solid fa-circle-user fa-2x text-success"></i>';
							$html .= '<div>';
								$html .= '<small class="text-muted text-uppercase fw-bold">Usuario Seleccionado</small>';
								$html .= '<h6 class="mb-0 fw-bold text-dark fs-6">'.htmlspecialchars($nom).'</h6>';
							$html .= '</div>';
						$html .= '</div>';
						
						$html .= '<div class="card border-0 bg-light p-3 mb-4 shadow-sm rounded-3">';
							$html .= '<label class="form-label fw-bold text-dark mb-2">';
								$html .= '<i class="fa-solid fa-plus-circle me-1 text-success"></i> Vincular Nueva Ficha ';
								$html .= '<small class="text-muted fw-normal">(Puede escribir el número de la ficha o seleccionarla de la lista)</small>';
							$html .= '</label>';
							$html .= '<div class="row g-2 align-items-center">';
								$html .= '<div class="col-md-5">';
									$html .= '<div class="input-group">';
										$html .= '<span class="input-group-text bg-white"><i class="fa-solid fa-hashtag text-muted"></i></span>';
										$html .= '<input type="text" name="idfic" id="txt_idfic_'.$id.'" list="dl_fichas_modal_'.$id.'" class="form-control" placeholder="No. ficha o nombre..." autocomplete="off" oninput="syncModalFichaInput(this, \'sel_idfic_'.$id.'\')">';
									$html .= '</div>';
								$html .= '</div>';
								$html .= '<div class="col-md-5">';
									$html .= '<select id="sel_idfic_'.$id.'" class="form-select" onchange="syncModalFichaSelect(this, \'txt_idfic_'.$id.'\')">';
										$html .= '<option value="">-- O seleccionar de lista desplegable --</option>';
										$html .= $optionsFichas;
									$html .= '</select>';
								$html .= '</div>';
								$html .= '<div class="col-md-2">';
									$html .= '<button type="submit" class="btn btn-success w-100 fw-bold"><i class="fa-solid fa-link me-1"></i> Vincular</button>';
								$html .= '</div>';
							$html .= '</div>';
							$html .= '<datalist id="dl_fichas_modal_'.$id.'">';
								$html .= $datalistFichas;
							$html .= '</datalist>';
						$html .= '</div>';

						$html .= '<h6 class="fw-bold text-dark mb-2"><i class="fa-solid fa-list-check me-1 text-success"></i> Fichas Actualmente Registradas</h6>';
						$html .= '<div class="table-responsive">';
							$html .= '<table class="table table-bordered table-hover align-middle mb-0">';
								$html .= '<thead class="table-light">';
									$html .= '<tr>';
										$html .= '<th style="width: 25%;">No. Ficha</th>';
										$html .= '<th>Nombre del Programa</th>';
										$html .= '<th class="text-center" style="width: 15%;">Principal</th>';
										$html .= '<th class="text-center" style="width: 15%;">Acción</th>';
									$html .= '</tr>';
								$html .= '</thead>';
								$html .= '<tbody>';
								if($datfr){ foreach($datfr AS $dp){
									$esPrincipal = ($dp['actfic'] == 1);
									$html .= '<tr>';
										$html .= '<td><span class="badge bg-secondary fs-7">'.htmlspecialchars($dp['idfic']).'</span></td>';
										$html .= '<td class="fw-semibold">'.htmlspecialchars($dp['nomfic']).'</td>';
										$html .= '<td class="text-center">';
											if($esPrincipal){
												$html .= '<span class="badge bg-warning text-dark"><i class="fa-solid fa-star me-1"></i> Principal</span>';
											} else {
												$html .= '<span class="badge bg-light text-muted border">Secundaria</span>';
											}
										$html .= '</td>';
										$html .= '<td class="text-center">';
											$html .= '<a href="home.php?pg='.$pg.'&idfic='.$dp['idfic'].'&idusu='.$id.'&opera=duxf'.($nodocfil ? '&nodocfil='.urlencode($nodocfil) : '').($idficfil ? '&idficfil='.urlencode($idficfil) : '').'" class="btn btn-outline-danger btn-sm" title="Desvincular Ficha" onclick="return confirm(\'¿Confirma que desea desvincular la ficha '.$dp['idfic'].' de este usuario?\');">';
												$html .= '<i class="fa-solid fa-trash-can"></i>';
											$html .= '</a>';
										$html .= '</td>';
									$html .= '</tr>';
								}} else {
									$html .= '<tr><td colspan="4" class="text-center text-muted py-3"><i class="fa-solid fa-inbox fa-2x mb-2 d-block text-secondary opacity-50"></i>No tiene fichas registradas actualmente.</td></tr>';
								}
								$html .= '</tbody>';
							$html .= '</table>';
						$html .= '</div>';
					$html .= '</div>';
					$html .= '<div class="modal-footer bg-light px-4 py-3">';
						$html .= '<button type="button" class="btn btn-secondary px-4 rounded-pill" data-bs-dismiss="modal"><i class="fa-solid fa-xmark me-1"></i> Cerrar</button>';
						$html .= '<input type="hidden" name="idusu" value="'.$id.'">';
						$html .= '<input type="hidden" name="opera" value="InUF">';
						if($nodocfil) $html .= '<input type="hidden" name="nodocfil" value="'.htmlspecialchars($nodocfil).'">';
						if($idficfil) $html .= '<input type="hidden" name="idficfil" value="'.htmlspecialchars($idficfil).'">';
					$html .= '</div>';
				$html .= '</div>';
			$html .= '</form>';
		$html .= '</div>';
	$html .= '</div>';
	return $html;
}

?>