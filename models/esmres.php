<?php
class Esmres {
	private $nummin;
	private $idusu;
	private $fechos;
	private $tipmin;
	private $hij;
	private $fhlle;
	private $obs;
	private $ideles;
	private $ndocusu;

	function getNummin() { return $this->nummin; }
	function getIdusu() { return $this->idusu; }
	function getObs() { return $this->obs; }
	function getIdeles() { return $this->ideles; }
	function getFechos() { return $this->fechos; }
	function getHij() { return $this->hij; }
	function getTipmin() { return $this->tipmin; }
	function getFhlle() { return $this->fhlle; }
	function getNdocusu() { return $this->ndocusu; }

	function setNummin($nummin) { $this->nummin = $nummin; }
	function setIdusu($idusu) { $this->idusu = $idusu; }
	function setObs($obs) { $this->obs = $obs; }
	function setIdeles($ideles) { $this->ideles = $ideles; }
	function setFechos($fechos) { $this->fechos = $fechos; }
	function setHij($hij) { $this->hij = $hij; }
	function setTipmin($tipmin) { $this->tipmin = $tipmin; }
	function setFhlle($fhlle) { $this->fhlle = $fhlle; }
	function setNdocusu($ndocusu) { $this->ndocusu = $ndocusu; }

	public function getAll() {
		$sql = "SELECT m.nummin, m.idusu, u.ndocusu, u.nomusu, m.obs, m.ideles, m.fechos, m.hij, m.fhlle FROM minuta AS m INNER JOIN usuario AS u ON m.idusu=u.idusu";
		$modelo = new conexion();
		$conexion = $modelo->get_conexion();
		$result = $conexion->prepare($sql);
		$result->execute();
		$res = $result->fetchall(PDO::FETCH_ASSOC);
		return $res;
	}

	public function getOne() {
		$modelo = new conexion();
		$conexion = $modelo->get_conexion();
		$sql = "SELECT m.nummin, m.idusu, u.ndocusu, u.nomusu, u.fotcan, m.obs, m.ideles, m.fechos, m.hij, m.fhlle FROM minuta AS m INNER JOIN usuario AS u ON m.idusu=u.idusu WHERE m.nummin=:nummin";
		$result = $conexion->prepare($sql);
		$nummin = $this->getNummin();
		$result->bindParam(':nummin', $nummin);
		$result->execute();
		$res = $result->fetchall(PDO::FETCH_ASSOC);
		return $res;
	}

	public function getOneP($nummin) {
		$modelo = new conexion();
		$conexion = $modelo->get_conexion();
		$sql = "SELECT m.nummin, m.idusu, m.obs, m.ideles, m.fechos, m.hij, m.fhlle FROM minuta AS m WHERE m.nummin=:nummin";
		$result = $conexion->prepare($sql);
		$result->bindParam(':nummin', $nummin);
		$result->execute();
		$res = $result->fetchall(PDO::FETCH_ASSOC);
		return $res;
	}

	public function getOrigen() {
		$modelo = new conexion();
		$conexion = $modelo->get_conexion();
		$sql = "SELECT nummin FROM minuta WHERE idusu=:idusu";
		$result = $conexion->prepare($sql);
		$idusu = $this->getIdusu();
		$result->bindParam(':idusu', $idusu);
		$result->execute();
		$res = $result->fetchall(PDO::FETCH_ASSOC);
		return $res;
	}

	public function getUsuario($fech) {
		$modelo = new conexion();
		$conexion = $modelo->get_conexion();
		$sql = "SELECT u.idusu, u.ndocusu, u.nomusu, u.fotcan, u.emausu, u.telcan ,u.fecini, u.fecfin FROM usuario AS u WHERE u.ndocusu=:ndocusu AND u.actusu=1";
		$result = $conexion->prepare($sql);
		$ndocusu = $this->getNdocusu();
		$result->bindParam(':ndocusu', $ndocusu);
		$result->execute();
		$res = $result->fetchall(PDO::FETCH_ASSOC);
		return $res;
	}

	public function getUsuRgF() {
		$modelo = new conexion();
		$conexion = $modelo->get_conexion();
		date_default_timezone_set('America/Bogota');
		$fch = date('Y-m-d');
		$sql = "SELECT m.nummin, m.idusu, u.ndocusu, m.obs, m.hij, m.ideles FROM minuta AS m INNER JOIN usuario AS u ON m.idusu=u.idusu WHERE m.fechos BETWEEN '".$fch." 00:00:00' AND '".$fch." 23:59:59' AND m.idusu=:valbus AND fechos=(SELECT MAX(fechos) AS fecMax FROM minuta WHERE fechos BETWEEN '".$fch." 00:00:00' AND '".$fch." 23:59:59' AND idusu=:valbus)";
		$result = $conexion->prepare($sql);
		$valbus = $this->getIdusu();
		$result->bindParam(':valbus', $valbus);
		$result->execute();
		$res = $result->fetchall(PDO::FETCH_ASSOC);
		return $res;
	}

	public function getUsuDsis($ndocusu) {
		$modelo = new conexion();
		$conexion = $modelo->get_conexion();
		$sql = "SELECT u.idusu, u.ndocusu, u.nomusu, u.fotcan, u.emausu, u.telcan FROM usuario AS u WHERE u.ndocusu='$ndocusu'";
		$result = $conexion->prepare($sql);
		$result->execute();
		$res = $result->fetchall(PDO::FETCH_ASSOC);
		return $res;
	}

	public function getRepetido() {
		$modelo = new conexion();
		$conexion = $modelo->get_conexion();
		$sql = "SELECT COUNT(nummin) AS cot FROM minuta WHERE idusu=:idusu AND tipmin=:tipmin AND hij=0";
		$result = $conexion->prepare($sql);
		$idusu = $this->getIdusu();
		$result->bindParam(':idusu', $idusu);
		$tipmin = 'I';
		$result->bindParam(':tipmin', $tipmin);
		$result->execute();
		$res = $result->fetchall(PDO::FETCH_ASSOC);
		return $res;
	}

	public function getVuelta() {
		$modelo = new conexion();
		$conexion = $modelo->get_conexion();
		$sql = "SELECT nummin, COUNT(nummin) AS can FROM minuta WHERE idusu=:idusu AND tipmin=:tipmin AND fhlle IS NULL";
		$result = $conexion->prepare($sql);
		$idusu = $this->getIdusu();
		$result->bindParam(':idusu', $idusu);
		$tipmin = $this->getTipmin();
		$result->bindParam(':tipmin', $tipmin);
		$result->execute();
		$res = $result->fetchall(PDO::FETCH_ASSOC);
		return $res;
	}

	public function getExiste($idusu) {
		$modelo = new conexion();
		$conexion = $modelo->get_conexion();
		$sql = "SELECT nummin, idusu, obs, ideles, fechos, tipmin, hij, fhlle FROM minuta WHERE idusu='$idusu' AND tipmin='I' AND fhlle IS NULL";
		$result = $conexion->prepare($sql);
		$result->execute();
		$res = $result->fetchall(PDO::FETCH_ASSOC);
		return $res;
	}

	public function selOneEle() {
		$modelo = new conexion();
		$conexion = $modelo->get_conexion();
		$sql = "SELECT e.idele, e.idusu, e.nomele, e.nidele, e.marele, e.tipele, v.nomval, e.noplasena, e.desele, e.preele, f.rutfot FROM elemento AS e INNER JOIN valor AS v ON e.tipele=v.idval LEFT JOIN foto AS f ON e.idele=f.idele WHERE idusu=:idusu";
		$result = $conexion->prepare($sql);
		$idusu = $this->getIdusu();
		$result->bindParam(":idusu", $idusu);
		$result->execute();
		$res = $result->fetchall(PDO::FETCH_ASSOC);
		return $res;
	}
	
	public function save() {
		try {
			$modelo = new conexion();
			$conexion = $modelo->get_conexion();
			$sql = "INSERT INTO minuta(idusu, fechos, tipmin, hij, ideles, obs) VALUES (:idusu, :fechos, :tipmin, '0', :ideles, :obs)";
			$result = $conexion->prepare($sql);
			$idusu = $this->getIdusu();
			$result->bindParam(':idusu', $idusu);
			$fechos = $this->getFechos();
			$result->bindParam(':fechos', $fechos);
			$tipmin = $this->getTipmin();
			$result->bindParam(':tipmin', $tipmin);
			$ideles = $this->getIdeles();
			$result->bindParam(':ideles', $ideles);
			$obs = $this->getObs();
			$result->bindParam(':obs', $obs);
			$result->execute();
		} catch (Exception $e) {
			ManejoError($e);
		}
	}

	public function updHij() {
		$modelo = new conexion();
		$conexion = $modelo->get_conexion();
		$sql = "UPDATE minuta SET hij=1, tipmin=:tipmin, fhlle=:fhlle WHERE nummin=:nummin";
		$result = $conexion->prepare($sql);
		$nummin = $this->getNummin();
		$result->bindParam(':nummin', $nummin);
		$tipmin = $this->getTipmin();
		$result->bindParam(':tipmin', $tipmin);
		$fhlle = $this->getFhlle();
		$result->bindParam(':fhlle', $fhlle);
		$result->execute();
	}

	public function updFecAnt() {
		$modelo = new conexion();
		$conexion = $modelo->get_conexion();
		$sql = "UPDATE minuta SET fhlle=concat(DATE(fechos),' 23:59:59'),tipmin='F',obs='Sistema.' WHERE fhlle IS NULL AND fechos<CURDATE()";
		$result = $conexion->prepare($sql);
		$result->execute();
	}

	public function getupdFecAntUsu() {
		$modelo = new conexion();
		$conexion = $modelo->get_conexion();
		$sql = "UPDATE usuario SET actusu=3 WHERE idusu IN (SELECT idusu FROM minuta WHERE fhlle IS NULL AND fechos<CURDATE())";
		$result = $conexion->prepare($sql);
		$result->execute();
		$res = $result->fetchall(PDO::FETCH_ASSOC);
		return $res;
	}

}
class Mres extends Esmres {}
?>
