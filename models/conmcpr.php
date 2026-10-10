<?php
require_once(dirname(__DIR__) . '/models/conexion.php');

class Mdcpr {

	private $idusu;
	private $idcom;
	private $fecuxc;

	// Metodos GET
	function getIdusu(){ return $this->idusu; }
	function getIdcom(){ return $this->idcom; }
	function getFecuxc(){ return $this->fecuxc; }

	// Metodos SET
	function setIdusu($idusu){ $this->idusu = $idusu; }
	function setIdcom($idcom){ $this->idcom = $idcom; }
	function setFecuxc($fecuxc){ $this->fecuxc = $fecuxc; }

	// Búsqueda del aprendiz por número de documento o por idusu
	public function buscarUsuario($ndocusu = '', $idusu = 0){
		try {
			$sql = "SELECT u.idusu, u.nomusu, u.ndocusu, t.nomval AS tdoc, uf.idfic, IFNULL(f.nomfic,'') AS programa, c.nomcen
					FROM usuario AS u
					INNER JOIN centro AS c ON u.idcen = c.idcen
					LEFT JOIN valor AS t ON u.tdousu = t.idval
					LEFT JOIN usufic AS uf ON u.idusu = uf.idusu
					LEFT JOIN ficha AS f ON uf.idfic = f.idfic
					WHERE 1=1 ";
			$params = array();
			if((string)$ndocusu !== ''){
				$sql .= "AND u.ndocusu = :ndoc ";
				$params[':ndoc'] = (string)$ndocusu;
			}
			if((int)$idusu > 0){
				$sql .= "AND u.idusu = :idusu ";
				$params[':idusu'] = (int)$idusu;
			}
			$sql .= "ORDER BY uf.actfic DESC LIMIT 1";
			$modelo = new conexion();
			$conexion = $modelo->get_conexion();
			$result = $conexion->prepare($sql);
			foreach($params as $k => $v){ $result->bindValue($k, $v); }
			$result->execute();
			$res = $result->fetchAll(PDO::FETCH_ASSOC);
			return isset($res[0]) ? $res[0] : null;
		} catch (Throwable $e) {
			return null;
		}
	}

	// Búsqueda del aprendiz por número de documento o por ficha
	public function buscarAprendiz($busqueda){
		$busqueda = trim((string)$busqueda);
		if($busqueda === '') return null;

		try {
			$sql = "SELECT u.idusu, u.nomusu, u.ndocusu, t.nomval AS tdoc, u.tdousu,
						   uf.idfic, IFNULL(f.nomfic,'') AS programa, c.nomcen
					FROM usuario AS u
					INNER JOIN centro AS c ON u.idcen = c.idcen
					LEFT JOIN valor AS t ON u.tdousu = t.idval
					LEFT JOIN usufic AS uf ON u.idusu = uf.idusu
					LEFT JOIN ficha AS f ON uf.idfic = f.idfic
					WHERE (u.ndocusu = :ndoc OR uf.idfic = :fic)
					ORDER BY uf.actfic DESC
					LIMIT 1";
			$modelo = new conexion();
			$conexion = $modelo->get_conexion();
			$result = $conexion->prepare($sql);
			$result->bindParam(':ndoc', $busqueda);
			$result->bindParam(':fic', $busqueda);
			$result->execute();
			$res = $result->fetchAll(PDO::FETCH_ASSOC);
			return isset($res[0]) ? $res[0] : null;
		} catch (Throwable $e) {
			return null;
		}
	}

	// Cláusulas normativas según el formato seleccionado
	public function getClausulas($formato = 'MI COMPROMISO COMO APRENDIZ SENA'){
		$fijos = $this->getCompromisosFijos();
		$base = array(
			array('id' => 'c1', 'txt' => $fijos[1][0]),
			array('id' => 'c2', 'txt' => $fijos[1][1]),
			array('id' => 'c3', 'txt' => $fijos[1][2]),
			array('id' => 'c4', 'txt' => $fijos[1][3]),
			array('id' => 'c5', 'txt' => $fijos[1][4]),
			array('id' => 'c6', 'txt' => $fijos[1][5]),
			array('id' => 'c7', 'txt' => $fijos[1][6]),
			array('id' => 'c8', 'txt' => $fijos[1][7]),
			array('id' => 'c9', 'txt' => $fijos[1][8])
		);

		if($formato == 'FORMATO DE SEGUIMIENTO ETAPA PRODUCTIVA'){
			return array(
				array('id' => 's1', 'txt' => 'Asistir puntualmente a las actividades y compromisos pactados con la empresa o el instructor de la etapa productiva.'),
				array('id' => 's2', 'txt' => 'Utilizar correctamente la bit&aacute;cora y los formatos establecidos por la Coordinaci&oacute;n de Etapa Productiva.'),
				array('id' => 's3', 'txt' => 'Reportar oportunamente las novedades, cambios de jornada o ausencias durante la ejecuci&oacute;n de la etapa productiva.'),
				array('id' => 's4', 'txt' => 'Cumplir con el horario, las normas de seguridad industrial y el reglamento interno de la empresa o escenario de pr&aacute;ctica.'),
				array('id' => 's5', 'txt' => 'Mantener comunicaci&oacute;n permanente con el instructor y el centro de formaci&oacute;n durante toda la etapa productiva.')
			);
		}

		if($formato == 'PLAN DE MEJORAMIENTO ACADÉMICO'){
			return array(
				array('id' => 'm1', 'txt' => 'Asistir y participar activamente en las jornadas de refuerzo y nivelaci&oacute;n programadas por el instructor o la coordinaci&oacute;n acad&eacute;mica.'),
				array('id' => 'm2', 'txt' => 'Entregar las evidencias y trabajos pendientes en las fechas establecidas dentro del plan de mejoramiento.'),
				array('id' => 'm3', 'txt' => 'Realizar seguimiento continuo a los logros de aprendizaje que generaron la oportunidad de mejora.'),
				array('id' => 'm4', 'txt' => 'Cumplir las acciones pedag&oacute;gicas y formativas pactadas con el instructor durante la vigencia del plan.'),
				array('id' => 'm5', 'txt' => 'Conservar una actitud comprometida, respetuosa y diligente frente al proceso formativo y sus evaluaciones.')
			);
		}

		if($formato == 'CONSENTIMIENTO INFORMADO Y TRATAMIENTO DE DATOS'){
			return array(
				array('id' => 'k1', 'txt' => 'Autorizar libre, previa, expresa e informadamente al Servicio Nacional de Aprendizaje - SENA para el tratamiento de los datos personales, conforme a lo establecido en la Ley 1581 de 2012 y normas concordantes.'),
				array('id' => 'k2', 'txt' => 'Conocer los derechos de consulta, actualizaci&oacute;n, rectificaci&oacute;n, supresi&oacute;n e impugnaci&oacute;n frente al tratamiento de los datos personales.'),
				array('id' => 'k3', 'txt' => 'Declarar que la informaci&oacute;n suministrada es veraz y comprometerme a mantenerla actualizada durante el proceso de formaci&oacute;n.'),
				array('id' => 'k4', 'txt' => 'Autorizar el uso de los datos personales exclusivamente para los fines acad&eacute;micos, administrativos y de bienestar propios del proceso formativo SENA.')
			);
		}

		return $base;
	}

	// Guardado (o actualización) del detalle de documentación
	public function guardarDetalle($dat){
		try {
			$modelo = new conexion();
			$conexion = $modelo->get_conexion();

			$sql = "INSERT INTO dcpr_detalle
						(for_dcpr, id_usu, ndocumento, nom_apre, tdoc_apre, programa, centro, idfic,
						 es_menor, nom_acu, doc_acu, parentesco, consentimiento, clausulas, compromiso_esp, fec_dili)
					VALUES
						(:for_dcpr, :id_usu, :ndocumento, :nom_apre, :tdoc_apre, :programa, :centro, :idfic,
						 :es_menor, :nom_acu, :doc_acu, :parentesco, :consentimiento, :clausulas, :compromiso_esp, :fec_dili)
					ON DUPLICATE KEY UPDATE
						ndocumento = VALUES(ndocumento),
						nom_apre = VALUES(nom_apre),
						tdoc_apre = VALUES(tdoc_apre),
						programa = VALUES(programa),
						centro = VALUES(centro),
						idfic = VALUES(idfic),
						es_menor = VALUES(es_menor),
						nom_acu = VALUES(nom_acu),
						doc_acu = VALUES(doc_acu),
						parentesco = VALUES(parentesco),
						consentimiento = VALUES(consentimiento),
						clausulas = VALUES(clausulas),
						compromiso_esp = VALUES(compromiso_esp),
						fec_dili = VALUES(fec_dili)";

			$result = $conexion->prepare($sql);
			$result->bindParam(':for_dcpr', $dat['for_dcpr']);
			$result->bindParam(':id_usu', $dat['id_usu']);
			$result->bindParam(':ndocumento', $dat['ndocumento']);
			$result->bindParam(':nom_apre', $dat['nom_apre']);
			$result->bindParam(':tdoc_apre', $dat['tdoc_apre']);
			$result->bindParam(':programa', $dat['programa']);
			$result->bindParam(':centro', $dat['centro']);
			$result->bindParam(':idfic', $dat['idfic']);
			$result->bindParam(':es_menor', $dat['es_menor']);
			$result->bindParam(':nom_acu', $dat['nom_acu']);
			$result->bindParam(':doc_acu', $dat['doc_acu']);
			$result->bindParam(':parentesco', $dat['parentesco']);
			$result->bindParam(':consentimiento', $dat['consentimiento']);
			$result->bindParam(':clausulas', $dat['clausulas']);
			$result->bindParam(':compromiso_esp', $dat['compromiso_esp']);
			$result->bindParam(':fec_dili', $dat['fec_dili']);
			$result->execute();
			return true;
		} catch (Throwable $e) {
			return false;
		}
	}

	// Último detalle registrado de un aprendiz para un formato dado
	public function getDetalle($id_usu, $for_dcpr = ''){
		try {
			$sql = "SELECT * FROM dcpr_detalle WHERE id_usu = :id_usu ";
			if($for_dcpr !== ''){
				$sql .= "AND for_dcpr = :for_dcpr ";
			}
			$sql .= "ORDER BY iddcpr DESC LIMIT 1";
			$modelo = new conexion();
			$conexion = $modelo->get_conexion();
			$result = $conexion->prepare($sql);
			$result->bindParam(':id_usu', $id_usu);
			if($for_dcpr !== ''){
				$result->bindParam(':for_dcpr', $for_dcpr);
			}
			$result->execute();
			$res = $result->fetchAll(PDO::FETCH_ASSOC);
			return isset($res[0]) ? $res[0] : null;
		} catch (Throwable $e) {
			return null;
		}
	}

	// Texto fijo del compromiso (9 compromisos)
	public function getCompromisosFijos(){
		return array(
			'Me comprometo con el Servicio Nacional de Aprendizaje - SENA, en mi calidad de Aprendiz, y como persona responsable de mis actos, a:',
			array(
				'Cumplir y promover las disposiciones contempladas en el Reglamento del Aprendiz SENA, publicado en la p&aacute;gina web del SENA y en el blog de cada centro de formaci&oacute;n, del cual hago constar que he le&iacute;do y entendido, por lo que acepto las responsabilidades, derechos y obligaciones establecidas; as&iacute; como acatar las Normas y los Acuerdos de Convivencia Institucional de conformidad con el contexto geogr&aacute;fico y social del Centro de Formaci&oacute;n.',
				'Participar en todo el proceso de inducci&oacute;n para iniciar el programa de formaci&oacute;n, de acuerdo con la programaci&oacute;n del Centro de Formaci&oacute;n.',
				'Portar en todo momento el carn&eacute; de identificaci&oacute;n institucional en sitio visible.',
				'Proyectar la imagen corporativa del SENA dentro y fuera de la Entidad asumiendo una actitud &eacute;tica, con principios y valores sociales en cada una de mis actuaciones.',
				'Respetar la orientaci&oacute;n sexual, identidad de g&eacute;nero, edad, etnia, culto, religi&oacute;n, ideolog&iacute;a, procedencia y ocupaci&oacute;n, de todos los integrantes de la comunidad educativa.',
				'Al finalizar la formaci&oacute;n dar cumplimiento oportuno a todos los tr&aacute;mites acad&eacute;micos y administrativos para lograr la certificaci&oacute;n dentro del t&eacute;rmino que establece el reglamento.',
				'Si soy seleccionado como beneficiario para recibir apoyo de sostenimiento, alimentaci&oacute;n, transporte u otro, por parte de la entidad, me comprometo a realizar de forma adecuada todos los tr&aacute;mites administrativos y acad&eacute;micos correspondientes reglamentados por el SENA.',
				'Registrar y mantener actualizados mis datos personales y de contacto en los aplicativos inform&aacute;ticos que el SENA determine y actuar como veedor del registro oportuno de las situaciones acad&eacute;micas y administrativas que se presenten. Cualquier dato registrado por el aprendiz que no corresponda con la informaci&oacute;n real, ser&aacute; sujeto a lo establecido en la ley de delitos inform&aacute;ticos y dem&aacute;s normatividad vigente sobre uso de plataformas p&uacute;blicas.',
				'Con la firma del presente compromiso autorizo al SENA para que me notifique a trav&eacute;s de mi correo electr&oacute;nico registrado en el aplicativo Sofia Plus, todos los actos acad&eacute;micos y administrativos, as&iacute; como tambi&eacute;n los procedimientos y tr&aacute;mites en general que profiera, de acuerdo con las pol&iacute;ticas de uso y confidencialidad.'
			)
		);
	}

	// Datos del aprendiz para el documento (usuario logueado)
	public function getDatosUsuario($idusu){
		try {
			$sql = "SELECT u.idusu, u.nomusu, u.ndocusu, t.nomval AS tdoc, uf.idfic, f.nomfic AS programa, c.nomcen
					FROM usuario AS u
					INNER JOIN centro AS c ON u.idcen = c.idcen
					LEFT JOIN valor AS t ON u.tdousu = t.idval
					LEFT JOIN usufic AS uf ON u.idusu = uf.idusu
					LEFT JOIN ficha AS f ON uf.idfic = f.idfic
					WHERE u.idusu = :idusu LIMIT 1";
			$modelo = new conexion();
			$conexion = $modelo->get_conexion();
			$result = $conexion->prepare($sql);
			$result->bindParam(':idusu', $idusu);
			$result->execute();
			return $result->fetchAll(PDO::FETCH_ASSOC);
		} catch (Throwable $e) {
			return array();
		}
	}

	// Formatos estándar visibles siempre (generador de documentos)
	public function getFormatosFijosGenerador(){
		return array(
			'MI COMPROMISO COMO APRENDIZ SENA',
			'FORMATO DE SEGUIMIENTO ETAPA PRODUCTIVA',
			'CONSENTIMIENTO INFORMADO Y TRATAMIENTO DE DATOS',
			'PLAN DE MEJORAMIENTO ACADÉMICO'
		);
	}

	// Formatos estándar visibles siempre (detalle de documentación)
	public function getFormatosFijosDetalle(){
		return array(
			'MI COMPROMISO COMO APRENDIZ SENA',
			'FORMATO DE SEGUIMIENTO ETAPA PRODUCTIVA',
			'PLAN DE MEJORAMIENTO ACADÉMICO'
		);
	}

	// Compromisos / documentos disponibles (generador y detalle)
	// Combina los registros de la BD con los formatos estándar (idcom 0)
	public function getCompromisos(){
		try {
			$sql = "SELECT idcom, p1com, p2com, (CASE WHEN COALESCE(p1com,'') <> '' THEN p1com ELSE p2com END) AS nom_dcpr FROM compromiso ORDER BY idcom";
			$modelo = new conexion();
			$conexion = $modelo->get_conexion();
			$result = $conexion->prepare($sql);
			$result->execute();
			$res = $result->fetchAll(PDO::FETCH_ASSOC);
		} catch (Throwable $e) {
			return array();
		}

		$nombres = array();
		foreach($res as $r){ $nombres[trim((string)$r['nom_dcpr'])] = true; }
		foreach($this->getFormatosFijosGenerador() as $nom){
			$key = trim((string)$nom);
			if($key === '' || isset($nombres[$key])) continue;
			$res[] = array('idcom' => 0, 'p1com' => $nom, 'p2com' => '', 'nom_dcpr' => $nom);
			$nombres[$key] = true;
		}
		return $res;
	}

	// Busca un documento por su nombre (el mismo que usa el detalle para el form)
	public function buscarPorNombre($nombre = ''){
		$nombre = trim((string)$nombre);
		if($nombre === '') return null;
		foreach($this->getCompromisos() as $c){
			if($c['nom_dcpr'] === $nombre) return $c;
		}
		return null;
	}

	public function getCompromiso($idcom){
		try {
			$sql = "SELECT idcom, p1com, p2com FROM compromiso WHERE idcom = :idcom";
			$modelo = new conexion();
			$conexion = $modelo->get_conexion();
			$result = $conexion->prepare($sql);
			$result->bindParam(':idcom', $idcom);
			$result->execute();
			$res = $result->fetchAll(PDO::FETCH_ASSOC);
			return isset($res[0]) ? $res[0] : null;
		} catch (Throwable $e) {
			return null;
		}
	}

	// Registro de diligenciamiento del aprendiz
	public function getDiligenciamientos($idusu){
		try {
			$sql = "SELECT uc.idusu, uc.idcom, uc.fecuxc, c.p1com, c.p2com
					FROM usucom AS uc
					LEFT JOIN compromiso AS c ON uc.idcom = c.idcom
					WHERE uc.idusu = :idusu
					ORDER BY uc.fecuxc DESC";
			$modelo = new conexion();
			$conexion = $modelo->get_conexion();
			$result = $conexion->prepare($sql);
			$result->bindParam(':idusu', $idusu);
			$result->execute();
			return $result->fetchAll(PDO::FETCH_ASSOC);
		} catch (Throwable $e) {
			return array();
		}
	}

	public function getDiligenciamiento($idusu, $idcom){
		try {
			$sql = "SELECT uc.idusu, uc.idcom, uc.fecuxc
					FROM usucom AS uc
					WHERE uc.idusu = :idusu AND uc.idcom = :idcom LIMIT 1";
			$modelo = new conexion();
			$conexion = $modelo->get_conexion();
			$result = $conexion->prepare($sql);
			$result->bindParam(':idusu', $idusu);
			$result->bindParam(':idcom', $idcom);
			$result->execute();
			$res = $result->fetchAll(PDO::FETCH_ASSOC);
			return isset($res[0]) ? $res[0] : null;
		} catch (Throwable $e) {
			return null;
		}
	}

	public function guardarDiligenciamiento($idusu, $idcom){
		try {
			$modelo = new conexion();
			$conexion = $modelo->get_conexion();

			$sql = "DELETE FROM usucom WHERE idusu = :idusu AND idcom = :idcom";
			$result = $conexion->prepare($sql);
			$result->bindParam(':idusu', $idusu);
			$result->bindParam(':idcom', $idcom);
			$result->execute();

			$fecuxc = date('Y-m-d H:i:s');
			$sql = "INSERT INTO usucom (idusu, idcom, fecuxc) VALUES (:idusu, :idcom, :fecuxc)";
			$result = $conexion->prepare($sql);
			$result->bindParam(':idusu', $idusu);
			$result->bindParam(':idcom', $idcom);
			$result->bindParam(':fecuxc', $fecuxc);
			$result->execute();
			return $fecuxc;
		} catch (Throwable $e) {
			return null;
		}
	}

	// Generador: crea un documento nuevo con los textos indicados
	public function crearCompromiso($titulo, $desdtc){
		try {
			$modelo = new conexion();
			$conexion = $modelo->get_conexion();

			$sql = "SELECT COALESCE(MAX(idcom),0)+1 AS nxt FROM compromiso";
			$result = $conexion->prepare($sql);
			$result->execute();
			$f = $result->fetch(PDO::FETCH_ASSOC);
			$idcom = (int)$f['nxt'];

			$sql = "INSERT INTO compromiso (idcom, idval, p1com, p2com) VALUES (:idcom, 1, :titulo, NULL)";
			$result = $conexion->prepare($sql);
			$result->bindParam(':idcom', $idcom);
			$result->bindParam(':titulo', $titulo);
			$result->execute();

			$sql = "SELECT COALESCE(MAX(iddtc),0)+1 AS nxt FROM detcom";
			$result = $conexion->prepare($sql);
			$result->execute();
			$f = $result->fetch(PDO::FETCH_ASSOC);
			$iddtc = (int)$f['nxt'];

			$detfech = date('Y-m-d');
			$sql = "INSERT INTO detcom (iddtc, idcom, desdtc, detfech) VALUES (:iddtc, :idcom, :desdtc, :detfech)";
			$result = $conexion->prepare($sql);
			$result->bindParam(':iddtc', $iddtc);
			$result->bindParam(':idcom', $idcom);
			$result->bindParam(':desdtc', $desdtc);
			$result->bindParam(':detfech', $detfech);
			$result->execute();

			return $idcom;
		} catch (Throwable $e) {
			return null;
		}
	}

	// Mapea el tipo de documento del usuario a una de las casillas del formato
	private function tipoDoc($nomval){
		$nomval = strtoupper(trim((string)$nomval));
		switch($nomval){
			case 'TI': return array('tipo' => 'Tarjeta de Identidad',       'idx' => 0, 'otro' => '');
			case 'CC': return array('tipo' => 'Cédula de Ciudadanía',       'idx' => 1, 'otro' => '');
			case 'CE': return array('tipo' => 'Cédula de Extranjería',      'idx' => 2, 'otro' => '');
			default:   return array('tipo' => 'Otro',                       'idx' => 3, 'otro' => htmlspecialchars($nomval, ENT_QUOTES));
		}
	}

	// Genera la hoja del documento del aprendizaje (GFPI-F-015 V.3).
	// $esMenor: si true incluye el párrafo Ley 1581 redactado para tutor y el bloque de firmas del acudiente.
	// $fecha: array dia/mes/anio.
	// $img: array con rutas o data URIs de los logos 'sena' y 'siga'.
	// $dat: array opcional con datos del aprendiz para pre-llenar la hoja
	//       (nomusu, ndocusu, tdoc, idfic, programa, nomcen, fec_dili,
	//        nom_acu, doc_acu, parentesco).
	// $titulo: nombre del formato/documento (se muestra en el encabezado).
	public function armarCompromiso($esMenor = false, $fecha = null, $img = array(), $dat = array(), $titulo = ''){
		if(!$fecha) $fecha = array('dia' => date('d'), 'mes' => date('m'), 'anio' => date('Y'));

		if(!empty($dat) && !empty($dat['fec_dili']) && strtotime((string)$dat['fec_dili'])){
			$dt = strtotime((string)$dat['fec_dili']);
			$fecha = array('dia' => date('d', $dt), 'mes' => date('m', $dt), 'anio' => date('Y', $dt));
		}

		$logoSena = isset($img['sena']) ? $img['sena'] : 'img/logoSenaB.png';
		$logoSiga = isset($img['siga']) ? $img['siga'] : 'image/logocom.png';

		$intro = $this->getCompromisosFijos();

		// Lista de "compromisos / cláusulas" a renderizar en la hoja.
		// Si se generó desde el detalle, se respetan las cláusulas elegidas
		// y el compromiso especial guardado; si no, el compromiso fijo.
		$listaItems = array();
		$listaHtml  = '';
		$tituloLista = 'Me comprometo con el Servicio Nacional de Aprendizaje - SENA, en mi calidad de Aprendiz, y como persona responsable de mis actos, a:';

		$clausDet  = isset($dat['clausulas']) ? trim((string)$dat['clausulas']) : '';
		$espDet    = isset($dat['compromiso_esp']) ? trim((string)$dat['compromiso_esp']) : '';
		$formatoHoja = trim((string)(isset($dat['nomcom']) ? $dat['nomcom'] : $titulo));

		if($clausDet !== '' || $espDet !== ''){
			$selIds = array();
			foreach(explode(',', $clausDet) as $cid){
				$cid = trim((string)$cid);
				if($cid !== '') $selIds[] = $cid;
			}
			$clausDisp = $this->getClausulas($formatoHoja);
			$mapClaus = array();
			foreach($clausDisp as $cl){ $mapClaus[(string)$cl['id']] = $cl['txt']; }
			foreach($selIds as $cid){
				if(isset($mapClaus[$cid]) && $mapClaus[$cid] !== '') $listaItems[] = $mapClaus[$cid];
			}
			if($espDet !== ''){
				$listaItems[] = html_entity_decode($espDet, ENT_QUOTES, 'UTF-8');
			}
			if($formatoHoja === 'FORMATO DE SEGUIMIENTO ETAPA PRODUCTIVA' || $formatoHoja === 'PLAN DE MEJORAMIENTO ACADÉMICO'){
				$tituloLista = 'Me comprometo a dar cumplimiento estricto a las siguientes cláusulas del formato:';
			}elseif($formatoHoja === 'CONSENTIMIENTO INFORMADO Y TRATAMIENTO DE DATOS'){
				$tituloLista = 'Autorizo de manera libre, previa y expresa:';
			}
		}

		if(empty($listaItems)){
			$clausFormato = $this->getClausulas($formatoHoja);
			if(in_array($formatoHoja, array('FORMATO DE SEGUIMIENTO ETAPA PRODUCTIVA','PLAN DE MEJORAMIENTO ACADÉMICO','CONSENTIMIENTO INFORMADO Y TRATAMIENTO DE DATOS'), true)){
				foreach($clausFormato as $cl){
					$listaItems[] = $cl['txt'];
				}
				if($formatoHoja === 'CONSENTIMIENTO INFORMADO Y TRATAMIENTO DE DATOS'){
					$tituloLista = 'Autorizo de manera libre, previa y expresa:';
				}else{
					$tituloLista = 'Me comprometo a dar cumplimiento estricto a las siguientes cláusulas del formato:';
				}
			}else{
				foreach($intro[1] as $cm){
					$listaItems[] = $cm;
				}
				$tituloLista = 'Me comprometo con el Servicio Nacional de Aprendizaje - SENA, en mi calidad de Aprendiz, y como persona responsable de mis actos, a:';
			}
		}

		$lista = '<ol class="dcpr-lista">';
		foreach($listaItems as $cm){
			$lista .= '<li>' . $cm . '</li>';
		}
		$lista .= '</ol>';

		// ── Datos del aprendiz (solo lectura, se pintan como texto plano) ──
		$nomApre   = !empty($dat['nomusu'])   ? htmlspecialchars((string)$dat['nomusu'], ENT_QUOTES) : '';
		$docApre   = !empty($dat['ndocusu'])  ? htmlspecialchars((string)$dat['ndocusu'], ENT_QUOTES) : '';
		$progApre  = !empty($dat['programa']) ? htmlspecialchars((string)$dat['programa'], ENT_QUOTES) : '';
		$ficApre   = !empty($dat['idfic'])    ? htmlspecialchars((string)$dat['idfic'], ENT_QUOTES) : '';
		$cenApre   = !empty($dat['nomcen'])   ? htmlspecialchars((string)$dat['nomcen'], ENT_QUOTES) : '';
		$nomAcu    = !empty($dat['nom_acu'])  ? htmlspecialchars((string)$dat['nom_acu'], ENT_QUOTES) : '';
		$docAcu    = !empty($dat['doc_acu'])  ? htmlspecialchars((string)$dat['doc_acu'], ENT_QUOTES) : '';
		$parAcu    = !empty($dat['parentesco']) ? htmlspecialchars((string)$dat['parentesco'], ENT_QUOTES) : '';
		$tipoDoc   = $this->tipoDoc(isset($dat['tdoc']) ? $dat['tdoc'] : '');

		// Nombre del documento para el encabezado (se detecta si no viene explícito)
		$tituloDet = trim((string)$titulo);
		if($tituloDet === ''){
			if(!empty($dat['nomcom']))      $tituloDet = trim((string)$dat['nomcom']);
			elseif(!empty($dat['p1com']))   $tituloDet = trim((string)$dat['p1com']);
			elseif(!empty($dat['p2com']))   $tituloDet = trim((string)$dat['p2com']);
		}
		if($tituloDet === '') $tituloDet = 'MI COMPROMISO COMO APRENDIZ SENA';

		// Etiquetas de texto plano (NO editables)
		$txNom = '<div class="dcpr-txt">'.($nomApre !== '' ? $nomApre : '&nbsp;').'</div>';
		$txPro = '<div class="dcpr-txt">'.($progApre !== '' ? $progApre : '&nbsp;').'</div>';
		$txFic = '<div class="dcpr-txt">'.($ficApre !== '' ? $ficApre : '&nbsp;').'</div>';
		$txCen = '<div class="dcpr-txt">'.($cenApre !== '' ? $cenApre : '&nbsp;').'</div>';
		$txCual = '<div class="dcpr-txt">'.($tipoDoc['tipo'] === 'Otro' ? $tipoDoc['otro'] : '&nbsp;').'</div>';
		$txDoc = '<div class="dcpr-txt">'.($docApre !== '' ? $docApre : '&nbsp;').'</div>';
		$v = '<div class="dcpr-x-sel">X</div>';
		$b = '<div class="dcpr-x">&nbsp;</div>';
		$chkTI = $tipoDoc['idx'] === 0 ? $v : $b;
		$chkCC = $tipoDoc['idx'] === 1 ? $v : $b;
		$chkCE = $tipoDoc['idx'] === 2 ? $v : $b;
		$chkOT = $tipoDoc['idx'] === 3 ? $v : $b;

		// ── Párrafo legal Ley 1581 de 2012 (inteligente: adulto en 1a persona / menor vía tutor) ──
		$ley1581 = '';
		if(!empty($dat['ley1581'])){
			$ley1581 = htmlspecialchars((string)$dat['ley1581'], ENT_QUOTES);
		}elseif($esMenor){
			$representa = $nomAcu !== '' ? $nomAcu : 'EL ACUDIENTE DEL APRENDIZ';
			$docRepr    = $docAcu !== '' ? $docAcu : '____________________';
			$ley1581 = 'Yo, '.$representa.', identificado(a) con el documento No. '.$docRepr.', en representación legal del menor '
				. ($nomApre !== '' ? $nomApre : '____________________') . ', autorizo libre, previa, expresa e inequívocamente al SERVICIO '
				. 'NACIONAL DE APRENDIZAJE - SENA para el tratamiento de los datos personales e imagen corporativa del menor y los míos '
				. 'relacionados con el proceso de formación, conforme a lo establecido en la Ley 1581 de 2012, su Decreto reglamentario 1377 '
				. 'de 2013 y demás normas concordantes. Declaro que la información suministrada es veraz y conozco los derechos de ley, '
				. 'consulta, actualización, rectificación, supresión e impugnación frente al titular de la información.';
		}else{
			$ley1581 = 'Yo, '.($nomApre !== '' ? $nomApre : '____________________').', identificado(a) con el documento '
				. $tipoDoc['tipo'].' No. '.($docApre !== '' ? $docApre : '____________________').', autorizo libre, previa, expresa e '
				. 'inequívocamente al SERVICIO NACIONAL DE APRENDIZAJE - SENA para el tratamiento de mis datos personales e imagen '
				. 'corporativa relacionados con el proceso de formación, conforme a lo establecido en la Ley 1581 de 2012, su Decreto '
				. 'reglamentario 1377 de 2013 y demás normas concordantes. Declaro que la información suministrada es veraz y conozco los '
				. 'derechos de ley, consulta, actualización, rectificación, supresión e impugnación frente al titular de la información.';
		}

		$hoja = '';

		// ── ENCABEZADO ──
		$hoja .= '<table class="dcpr-tabla" border="0" cellpadding="0" cellspacing="0">';
		$hoja .= '<tr><td class="dcpr-celda-logo">';
		if($logoSena && (strpos($logoSena, 'data:') === 0 || file_exists($logoSena))) $hoja .= '<img class="dcpr-logo-img" src="'.$logoSena.'" alt="Logo SENA">';
		$hoja .= '</td>';
		$hoja .= '<td class="dcpr-celda-centro">';
		$hoja .= '<strong class="dcpr-tit-proceso">PROCESO GESTIÓN DE FORMACIÓN PROFESIONAL INTEGRAL</strong><br>';
		$hoja .= '<strong>'.htmlspecialchars($tituloDet, ENT_QUOTES).'</strong>';
		$hoja .= '</td>';
		$hoja .= '</tr>';
		$hoja .= '</table>';

		// ── TABLA PRINCIPAL ──
		$hoja .= '<table class="dcpr-tabla dcpr-tabla-mt" border="0" cellpadding="0" cellspacing="0">';

		// Yo
		$hoja .= '<tr><td colspan="2" class="dcpr-box">';
		$hoja .= '<div class="dcpr-yo">Yo,</div>';
		$hoja .= '<div class="dcpr-nombre-inline">'.$txNom.'</div>';
		$hoja .= '</td></tr>';

		// Documento de identidad
		$hoja .= '<tr><td class="dcpr-label-top">Con documento de identidad:<br><span class="dcpr-ident-sub">(marcar con una X)</span></td>';
		$hoja .= '<td class="dcpr-box">';
		$hoja .= '<table class="dcpr-tabla" border="0" cellpadding="0" cellspacing="0">';
		$hoja .= '<tr>';
		$hoja .= '<td class="dcpr-chk">'.$chkTI.'</td><td class="dcpr-opc-cell"><strong>Tarjeta de Identidad</strong></td>';
		$hoja .= '<td class="dcpr-chk">'.$chkCC.'</td><td class="dcpr-opc-cell"><strong>Cédula de Ciudadanía</strong></td>';
		$hoja .= '<td class="dcpr-chk">'.$chkCE.'</td><td class="dcpr-opc-cell"><strong>Cédula de Extranjería</strong></td>';
		$hoja .= '<td class="dcpr-chk">'.$chkOT.'</td><td class="dcpr-opc-cell"><strong>Otro</strong></td>';
		$hoja .= '</tr><tr>';
		$hoja .= '<td colspan="4" class="dcpr-opc-td"><strong>Cual:</strong> '.$txCual.'</td>';
		$hoja .= '<td colspan="4" class="dcpr-opc-td"><strong>No.</strong> '.$txDoc.'</td>';
		$hoja .= '</tr>';
		$hoja .= '</table>';
		$hoja .= '</td></tr>';

		// Programa
		$hoja .= '<tr><td class="dcpr-label">Matriculado en el programa de formación:</td><td class="dcpr-box">'.$txPro.'</td></tr>';

		// Ficha
		$hoja .= '<tr><td class="dcpr-label">Ficha de Caracterización No.</td><td class="dcpr-box">'.$txFic.'</td></tr>';

		// Centro
		$hoja .= '<tr><td class="dcpr-label">Del Centro de Formación:</td><td class="dcpr-box">'.$txCen.'</td></tr>';

		// Compromisos
		$hoja .= '<tr><td colspan="2" class="dcpr-just">';
		$hoja .= '<span class="dcpr-num-doc">'.htmlspecialchars($tituloLista, ENT_QUOTES).'</span><br><br>';
		$hoja .= $lista;
		$hoja .= '</td></tr>';

		// ── Párrafo legal Ley 1581 (antes de las firmas) ──
		if($ley1581 !== ''){
			$hoja .= '<tr><td colspan="2" class="dcpr-just2">';
			$hoja .= '<strong>PARÁGRAFO – LEY 1581 DE 2012 (PROTECCIÓN DE DATOS PERSONALES):</strong><br><br>';
			$hoja .= $ley1581;
			$hoja .= '</td></tr>';
		}

		// ── Bloque de firmas adaptativo ──
		$firApre = '<div class="dcpr-fir-linea"></div>'
			. '<div class="dcpr-fir-nombre">'.($nomApre !== '' ? $nomApre : '&nbsp;').'</div>'
			. '<div class="dcpr-fir-doc">CC: '.($docApre !== '' ? $docApre : '________').'</div>';
		if($esMenor){
			// Dos columnas: firma del aprendiz (izquierda) y firma del padre/acudiente (derecha)
			$hoja .= '<tr>';
			$hoja .= '<td class="dcpr-fir-celda">'
				. '<div class="dcpr-fir-titulo">FIRMA DEL APRENDIZ</div>'.$firApre.'</td>';
			$datoAcu = '<div class="dcpr-fir-nombre">'.($nomAcu !== '' ? $nomAcu : '&nbsp;').'</div>'
				. '<div class="dcpr-fir-doc">'.($parAcu !== '' ? $parAcu : 'Acudiente').': '
				. ($docAcu !== '' ? $docAcu : '________').'</div>';
			$hoja .= '<td class="dcpr-fir-celda">'
				. '<div class="dcpr-fir-titulo">FIRMA DEL PADRE O ACUDIENTE LEGAL</div>'
				. '<div class="dcpr-fir-linea"></div>'.$datoAcu.'</td>';
			$hoja .= '</tr>';
			$hoja .= '<tr><td colspan="2" class="dcpr-nota-menor">';
			$hoja .= '<em>(Por tratarse de un aprendiz menor de edad, se exige la firma del padre, la madre o el acudiente legal y copia del documento oficial que acredite tal condición)</em>';
			$hoja .= '</td></tr>';
		}else{
			// Una celda: solo la firma del aprendiz
			$hoja .= '<tr><td colspan="2" class="dcpr-fir-celda-full">'
				. '<div class="dcpr-fir-titulo">FIRMA DEL APRENDIZ</div>'.$firApre.'</td></tr>';
		}

		// Fecha de diligenciamiento (pre-llenada y de solo lectura)
		$fecDia = $fecha['dia'] !== '' ? preg_replace('/^0+/', '', $fecha['dia']) : '';
		$fecMes = $fecha['mes'] !== '' ? preg_replace('/^0+/', '', $fecha['mes']) : '';
		$fecAnio = $fecha['anio'];
		$hoja .= '<tr><td class="dcpr-label">FECHA DE DILIGENCIAMIENTO:</td><td class="dcpr-fecha-cell">';
		$hoja .= 'DÍA: <span class="dcpr-fecha-campo">'.htmlspecialchars($fecDia, ENT_QUOTES).'</span>'
			. ' &nbsp;&nbsp;&nbsp; MES: <span class="dcpr-fecha-campo">'.htmlspecialchars($fecMes, ENT_QUOTES).'</span>'
			. ' &nbsp;&nbsp;&nbsp; AÑO: <span class="dcpr-fecha-campo">'.htmlspecialchars($fecAnio, ENT_QUOTES).'</span>';
		$hoja .= '</td></tr>';

		// Nota final
		$hoja .= '<tr><td colspan="2" class="dcpr-nota">';
		$hoja .= '<em>Este documento forma parte de la ficha académica del aprendiz y es prueba del compromiso que adquiere con el SENA de cumplir el Reglamento de Aprendices SENA, el cual es firmado durante el proceso de matrícula en un programa de formación en el SENA.</em>';
		$hoja .= '</td></tr>';

		$hoja .= '</table>';

		// Código del formato (parte inferior derecha)
		require_once(dirname(__DIR__) . '/models/conmcon.php');
		$hoja .= '<div class="dcpr-footer">'.htmlspecialchars(Mcon::NACTA.' '.Mcon::VERACTA, ENT_QUOTES).'</div>';

		return $hoja;
	}
}
?>