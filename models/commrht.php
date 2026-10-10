<?php
require_once __DIR__ . "/conexion.php";

class Commhtr{

	private $idnorad;

	function setIdnorad($idnorad){
		$this->idnorad = $idnorad;
	}
	function getIdnorad(){
		return $this->idnorad;
	}

	public function getAll(){
		try{
			$sql = "SELECT h.idnorad, h.codpro, p.nompro, h.idemp, e.nomemp, h.codproesp, s.nomval AS proesp, 
						   h.codslem, h.feclini, h.feclin, h.cupo, h.jornada, j.nomval AS nomjornada, 
						   h.idfic, f.nomfic, h.convht
				FROM hojatra AS h
				LEFT JOIN programa AS p ON h.codpro = p.codpro
				LEFT JOIN empresa AS e ON h.idemp = e.idemp
				LEFT JOIN valor AS s ON h.codproesp = s.idval
				LEFT JOIN valor AS j ON h.jornada = j.idval
				LEFT JOIN ficha AS f ON h.idfic = f.idfic
				ORDER BY h.idnorad DESC";
			$modelo = new conexion();
			$conexion = $modelo->get_conexion();
			$result = $conexion->prepare($sql);
			$result->execute();
			$res = $result->fetchAll(PDO::FETCH_ASSOC);
			return $res;
		}catch(Exception $e){
			if(function_exists('ManejoError')) ManejoError($e);
			return [];
		}
	}

	public function getOne(){
		try{
			$sql = "SELECT h.*, 
						   p.nompro, p.horlpro, 
						   e.nomemp, e.numdocemp, e.diremp, e.telemp, e.nomconemp, e.coremp,
						   u.nomubi AS nommun, 
						   s.nomval AS proesp, 
						   j.nomval AS nomjornada, 
						   f.nomfic,
						   uinst.nomusu AS nombre_instructor,
						   uinst.ndocusu AS documento_instructor,
						   uinst.telcan AS cel_instructor,
						   uinst.emausu AS correo_instructor
				FROM hojatra AS h
				LEFT JOIN programa AS p ON h.codpro = p.codpro
				LEFT JOIN empresa AS e ON h.idemp = e.idemp
				LEFT JOIN ubica AS u ON e.codubi = u.codubi
				LEFT JOIN valor AS s ON h.codproesp = s.idval
				LEFT JOIN valor AS j ON h.jornada = j.idval
				LEFT JOIN ficha AS f ON h.idfic = f.idfic
				LEFT JOIN usuario AS uinst ON f.idusu = uinst.idusu
				WHERE h.idnorad = :idnorad";
			$modelo = new conexion();
			$conexion = $modelo->get_conexion();
			$result = $conexion->prepare($sql);
			$idnorad = $this->getIdnorad();
			$result->bindParam(':idnorad', $idnorad);
			$result->execute();
			$res = $result->fetchAll(PDO::FETCH_ASSOC);
			return $res;
		}catch(Exception $e){
			if(function_exists('ManejoError')) ManejoError($e);
			return [];
		}
	}

	public function getConteos(){
		try{
			$sql = "SELECT
					(SELECT COUNT(*) FROM hojatra) AS total,
					(SELECT COALESCE(SUM(cupo),0) FROM hojatra) AS cupo,
					(SELECT COUNT(DISTINCT idfic) FROM hojatra WHERE idfic IS NOT NULL AND idfic <> '') AS fichas,
					(SELECT COUNT(DISTINCT codpro) FROM hojatra) AS programas";
			$modelo = new conexion();
			$conexion = $modelo->get_conexion();
			$result = $conexion->prepare($sql);
			$result->execute();
			$res = $result->fetchAll(PDO::FETCH_ASSOC);
			return $res;
		}catch(Exception $e){
			if(function_exists('ManejoError')) ManejoError($e);
			return [];
		}
	}

	public function getNextIdnorad() {
		try {
			$modelo = new conexion();
			$conexion = $modelo->get_conexion();
			$sql = "SELECT COALESCE(MAX(idnorad), 0) + 1 AS sig FROM hojatra";
			$stmt = $conexion->prepare($sql);
			$stmt->execute();
			$res = $stmt->fetch(PDO::FETCH_ASSOC);
			return $res ? (int)$res['sig'] : 1;
		} catch (Exception $e) {
			if(function_exists('ManejoError')) ManejoError($e);
			return 1;
		}
	}

	public function save($d) {
		try {
			$modelo = new conexion();
			$conexion = $modelo->get_conexion();
			$sql = "INSERT INTO hojatra (idnorad, codpro, idemp, codproesp, codslem, feclini, feclin, cupo, jornada, idfic, convht)
					VALUES (:idnorad, :codpro, :idemp, :codproesp, :codslem, :feclini, :feclin, :cupo, :jornada, :idfic, :convht)";
			$stmt = $conexion->prepare($sql);
			$stmt->bindParam(':idnorad', $d['idnorad'], PDO::PARAM_INT);
			$stmt->bindParam(':codpro', $d['codpro']);
			$stmt->bindParam(':idemp', $d['idemp'], PDO::PARAM_INT);
			$stmt->bindParam(':codproesp', $d['codproesp'], PDO::PARAM_INT);
			$stmt->bindParam(':codslem', $d['codslem']);
			$stmt->bindParam(':feclini', $d['feclini']);
			$stmt->bindParam(':feclin', $d['feclin']);
			$stmt->bindParam(':cupo', $d['cupo'], PDO::PARAM_INT);
			$stmt->bindParam(':jornada', $d['jornada']);
			$stmt->bindParam(':idfic', $d['idfic']);
			$stmt->bindParam(':convht', $d['convht']);
			return $stmt->execute();
		} catch (Exception $e) {
			if(function_exists('ManejoError')) ManejoError($e);
			return false;
		}
	}

	public function saveHxU($idnorad, $idusu) {
		try {
			$modelo = new conexion();
			$conexion = $modelo->get_conexion();
			$sql = "INSERT INTO hdtxusu (idnorad, idusu) VALUES (:idnorad, :idusu)";
			$stmt = $conexion->prepare($sql);
			$stmt->bindParam(':idnorad', $idnorad, PDO::PARAM_INT);
			$stmt->bindParam(':idusu', $idusu, PDO::PARAM_INT);
			return $stmt->execute();
		} catch (Exception $e) {
			if(function_exists('ManejoError')) ManejoError($e);
			return false;
		}
	}

	public function getProgramas() {
		try {
			$modelo = new conexion();
			$conexion = $modelo->get_conexion();
			$sql = "SELECT codpro, nompro FROM programa ORDER BY nompro ASC";
			$stmt = $conexion->prepare($sql);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch (Exception $e) {
			if(function_exists('ManejoError')) ManejoError($e);
			return [];
		}
	}

	public function getEmpresas() {
		try {
			$modelo = new conexion();
			$conexion = $modelo->get_conexion();
			$sql = "SELECT idemp, nomemp FROM empresa ORDER BY nomemp ASC";
			$stmt = $conexion->prepare($sql);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch (Exception $e) {
			if(function_exists('ManejoError')) ManejoError($e);
			return [];
		}
	}

	public function getProgramasEspeciales() {
		try {
			$modelo = new conexion();
			$conexion = $modelo->get_conexion();
			$sql = "SELECT idval, nomval FROM valor WHERE iddom=25 AND act=1 ORDER BY nomval ASC";
			$stmt = $conexion->prepare($sql);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch (Exception $e) {
			if(function_exists('ManejoError')) ManejoError($e);
			return [];
		}
	}

	public function getJornadas() {
		try {
			$modelo = new conexion();
			$conexion = $modelo->get_conexion();
			$sql = "SELECT idval, nomval FROM valor WHERE iddom=1 AND act=1 ORDER BY idval ASC";
			$stmt = $conexion->prepare($sql);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch (Exception $e) {
			if(function_exists('ManejoError')) ManejoError($e);
			return [];
		}
	}

	public function getFichas() {
		try {
			$modelo = new conexion();
			$conexion = $modelo->get_conexion();
			$sql = "SELECT idfic, nomfic FROM ficha ORDER BY idfic DESC";
			$stmt = $conexion->prepare($sql);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch (Exception $e) {
			if(function_exists('ManejoError')) ManejoError($e);
			return [];
		}
	}
}
?>