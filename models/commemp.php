<?php
require_once("conexion.php");

class Commemp{
	//Atributos
	private $idemp;
	private $nurunt;
	private $rutrunt;
	private $numdocemp;
	private $nomemp;
	private $tipoemp;
	private $diremp;
	private $codubi;
	private $nomconemp;
	private $telemp;
	private $coremp;
	private $webbemp;
	private $reprleg;
	private $estado;
	private $tipcur;
	private $desemp;
	private $idare;
	private $cupemp;
	private $modalidad;
	private $duraemp;
	private $infcon;

	//METODOS GET
	function getIdemp(){
		return $this->idemp;
	}
	function getNurunt(){
		return $this->nurunt;
	}
	function getRutrunt(){
		return $this->rutrunt;
	}
	function getNumdocemp(){
		return $this->numdocemp;
	}
	function getNomemp(){
		return $this->nomemp;
	}
	function getTipoemp(){
		return $this->tipoemp;
	}
	function getDiremp(){
		return $this->diremp;
	}
	function getCodubi(){
		return $this->codubi;
	}
	function getNomconemp(){
		return $this->nomconemp;
	}
	function getTelemp(){
		return $this->telemp;
	}
	function getCoremp(){
		return $this->coremp;
	}
	function getWebbemp(){
		return $this->webbemp;
	}
	function getReprleg(){
		return $this->reprleg;
	}
	function getEstado(){
		return $this->estado;
	}
	function getTipcur(){
		return $this->tipcur;
	}
	function getDesemp(){
		return $this->desemp;
	}
	function getIdare(){
		return $this->idare;
	}
	function getCupemp(){
		return $this->cupemp;
	}
	function getModalidad(){
		return $this->modalidad;
	}
	function getDuraemp(){
		return $this->duraemp;
	}
	function getInfcon(){
		return $this->infcon;
	}

	//METODOS SET
	function setIdemp($idemp){
		$this->idemp = $idemp;
	}
	function setNurunt($nurunt){
		$this->nurunt = $nurunt;
	}
	function setRutrunt($rutrunt){
		$this->rutrunt = $rutrunt;
	}
	function setNumdocemp($numdocemp){
		$this->numdocemp = $numdocemp;
	}
	function setNomemp($nomemp){
		$this->nomemp = $nomemp;
	}
	function setTipoemp($tipoemp){
		$this->tipoemp = $tipoemp;
	}
	function setDiremp($diremp){
		$this->diremp = $diremp;
	}
	function setCodubi($codubi){
		$this->codubi = $codubi;
	}
	function setNomconemp($nomconemp){
		$this->nomconemp = $nomconemp;
	}
	function setTelemp($telemp){
		$this->telemp = $telemp;
	}
	function setCoremp($coremp){
		$this->coremp = $coremp;
	}
	function setWebbemp($webbemp){
		$this->webbemp = $webbemp;
	}
	function setReprleg($reprleg){
		$this->reprleg = $reprleg;
	}
	function setEstado($estado){
		$this->estado = $estado;
	}
	function setTipcur($tipcur){
		$this->tipcur = $tipcur;
	}
	function setDesemp($desemp){
		$this->desemp = $desemp;
	}
	function setIdare($idare){
		$this->idare = $idare;
	}
	function setCupemp($cupemp){
		$this->cupemp = $cupemp;
	}
	function setModalidad($modalidad){
		$this->modalidad = $modalidad;
	}
	function setDuraemp($duraemp){
		$this->duraemp = $duraemp;
	}
	function setInfcon($infcon){
		$this->infcon = $infcon;
	}

	//METODOS

	public function getAll(){
		try{
			$sql = "SELECT e.idemp, e.nurunt, e.rutrunt, e.numdocemp, e.nomemp, e.tipoemp, e.diremp, e.codubi, e.nomconemp, e.telemp, e.coremp, e.webbemp, e.reprleg, e.estado, e.tipcur, e.desemp, e.idare, e.cupemp, e.modalidad, e.duraemp, e.infcon, e.feccre,
					u.nomubi AS nommun, d.nomubi AS nomdep, v.nomval AS nommodal, a.nomare AS nomare
				FROM empresa AS e
				LEFT JOIN ubica AS u ON e.codubi = u.codubi
				LEFT JOIN ubica AS d ON u.depubi = d.codubi
				LEFT JOIN valor AS v ON e.modalidad = v.idval
				LEFT JOIN area AS a ON e.idare = a.idare
				ORDER BY e.idemp DESC";
			$modelo = new conexion();
			$conexion = $modelo->get_conexion();
			$result = $conexion->prepare($sql);
			$result->execute();
			$res = $result->fetchAll(PDO::FETCH_ASSOC);
			return $res;
		}catch(Exception $e){
			ManejoError($e);
		}
	}

	public function getOne(){
		try{
			$sql = "SELECT e.idemp, e.nurunt, e.rutrunt, e.numdocemp, e.nomemp, e.tipoemp, e.diremp, e.codubi, e.nomconemp, e.telemp, e.coremp, e.webbemp, e.reprleg, e.estado, e.tipcur, e.desemp, e.idare, e.cupemp, e.modalidad, e.duraemp, e.infcon, e.feccre,
					u.nomubi AS nommun, u.depubi, d.nomubi AS nomdep, v.nomval AS nommodal, a.nomare AS nomare
				FROM empresa AS e
				LEFT JOIN ubica AS u ON e.codubi = u.codubi
				LEFT JOIN ubica AS d ON u.depubi = d.codubi
				LEFT JOIN valor AS v ON e.modalidad = v.idval
				LEFT JOIN area AS a ON e.idare = a.idare
				WHERE e.idemp = :idemp";
			$modelo = new conexion();
			$conexion = $modelo->get_conexion();
			$result = $conexion->prepare($sql);
			$idemp = $this->getIdemp();
			$result->bindParam(':idemp',$idemp);
			$result->execute();
			$res = $result->fetchAll(PDO::FETCH_ASSOC);
			return $res;
		}catch(Exception $e){
			ManejoError($e);
		}
	}

	public function save(){
		try{
			$sql = "INSERT INTO empresa (nurunt, rutrunt, numdocemp, nomemp, tipoemp, diremp, codubi, nomconemp, telemp, coremp, webbemp, reprleg, estado, tipcur, desemp, idare, cupemp, modalidad, duraemp, infcon, usucre, feccre)
				VALUES (:nurunt, :rutrunt, :numdocemp, :nomemp, :tipoemp, :diremp, :codubi, :nomconemp, :telemp, :coremp, :webbemp, :reprleg, :estado, :tipcur, :desemp, :idare, :cupemp, :modalidad, :duraemp, :infcon, :usucre, :feccre)";
			$modelo = new conexion();
			$conexion = $modelo->get_conexion();
			$result = $conexion->prepare($sql);
			$result->bindParam(':nurunt',$this->nurunt);
			$result->bindParam(':rutrunt',$this->rutrunt);
			$result->bindParam(':numdocemp',$this->numdocemp);
			$result->bindParam(':nomemp',$this->nomemp);
			$result->bindParam(':tipoemp',$this->tipoemp);
			$result->bindParam(':diremp',$this->diremp);
			$result->bindParam(':codubi',$this->codubi);
			$result->bindParam(':nomconemp',$this->nomconemp);
			$result->bindParam(':telemp',$this->telemp);
			$result->bindParam(':coremp',$this->coremp);
			$result->bindParam(':webbemp',$this->webbemp);
			$result->bindParam(':reprleg',$this->reprleg);
			$result->bindParam(':estado',$this->estado);
			$result->bindParam(':tipcur',$this->tipcur);
			$result->bindParam(':desemp',$this->desemp);
			$result->bindParam(':idare',$this->idare);
			$result->bindParam(':cupemp',$this->cupemp);
			$result->bindParam(':modalidad',$this->modalidad);
			$result->bindParam(':duraemp',$this->duraemp);
			$result->bindParam(':infcon',$this->infcon);
			$usucre = isset($_SESSION['idusu']) ? $_SESSION['idusu']:NULL;
			$result->bindParam(':usucre',$usucre);
			$feccre = date('Y-m-d H:i:s');
			$result->bindParam(':feccre',$feccre);
			$result->execute();
			$newId = $conexion->lastInsertId();
			return $newId ? $newId : true;
		}catch(Exception $e){
			ManejoError($e);
			return false;
		}
	}

	public function edit(){
		try{
			$sql = "UPDATE empresa SET nurunt=:nurunt, rutrunt=:rutrunt, numdocemp=:numdocemp, nomemp=:nomemp, tipoemp=:tipoemp, diremp=:diremp, codubi=:codubi, nomconemp=:nomconemp, telemp=:telemp, coremp=:coremp, webbemp=:webbemp, reprleg=:reprleg, estado=:estado, tipcur=:tipcur, desemp=:desemp, idare=:idare, cupemp=:cupemp, modalidad=:modalidad, duraemp=:duraemp, infcon=:infcon
				WHERE idemp=:idemp";
			$modelo = new conexion();
			$conexion = $modelo->get_conexion();
			$result = $conexion->prepare($sql);
			$result->bindParam(':idemp',$this->idemp);
			$result->bindParam(':nurunt',$this->nurunt);
			$result->bindParam(':rutrunt',$this->rutrunt);
			$result->bindParam(':numdocemp',$this->numdocemp);
			$result->bindParam(':nomemp',$this->nomemp);
			$result->bindParam(':tipoemp',$this->tipoemp);
			$result->bindParam(':diremp',$this->diremp);
			$result->bindParam(':codubi',$this->codubi);
			$result->bindParam(':nomconemp',$this->nomconemp);
			$result->bindParam(':telemp',$this->telemp);
			$result->bindParam(':coremp',$this->coremp);
			$result->bindParam(':webbemp',$this->webbemp);
			$result->bindParam(':reprleg',$this->reprleg);
			$result->bindParam(':estado',$this->estado);
			$result->bindParam(':tipcur',$this->tipcur);
			$result->bindParam(':desemp',$this->desemp);
			$result->bindParam(':idare',$this->idare);
			$result->bindParam(':cupemp',$this->cupemp);
			$result->bindParam(':modalidad',$this->modalidad);
			$result->bindParam(':duraemp',$this->duraemp);
			$result->bindParam(':infcon',$this->infcon);
			$result->execute();
			return true;
		}catch(Exception $e){
			ManejoError($e);
			return false;
		}
	}

	public function eliminar(){
		try{
			$sql = "DELETE FROM empresa WHERE idemp=:idemp";
			$modelo = new conexion();
			$conexion = $modelo->get_conexion();
			$result = $conexion->prepare($sql);
			$result->bindParam(':idemp',$this->idemp);
			$result->execute();
			return true;
		}catch(Exception $e){
			ManejoError($e);
			return false;
		}
	}

	public function getConteos(){
		try{
			$sql = "SELECT
					(SELECT COUNT(*) FROM empresa) AS total,
					(SELECT COUNT(*) FROM empresa WHERE estado = 'Activa') AS activas,
					(SELECT COUNT(*) FROM empresa WHERE estado = 'Inactiva') AS inactivas,
					(SELECT COUNT(*) FROM empresa WHERE estado = 'Pendiente') AS pendientes";
			$modelo = new conexion();
			$conexion = $modelo->get_conexion();
			$result = $conexion->prepare($sql);
			$result->execute();
			$res = $result->fetchAll(PDO::FETCH_ASSOC);
			return $res;
		}catch(Exception $e){
			ManejoError($e);
		}
	}

	public function getAllDep(){
		try{
			$sql = "SELECT codubi, nomubi FROM ubica WHERE depubi = 0 ORDER BY nomubi";
			$modelo = new conexion();
			$conexion = $modelo->get_conexion();
			$result = $conexion->prepare($sql);
			$result->execute();
			$res = $result->fetchAll(PDO::FETCH_ASSOC);
			return $res;
		}catch(Exception $e){
			ManejoError($e);
		}
	}

	public function getAllMun($depubi){
		try{
			$sql = "SELECT codubi, nomubi FROM ubica WHERE depubi = :depubi ORDER BY nomubi";
			$modelo = new conexion();
			$conexion = $modelo->get_conexion();
			$result = $conexion->prepare($sql);
			$result->bindParam(':depubi',$depubi);
			$result->execute();
			$res = $result->fetchAll(PDO::FETCH_ASSOC);
			return $res;
		}catch(Exception $e){
			ManejoError($e);
		}
	}

	public function getVal($iddom){
		try{
			$sql = "SELECT idval, nomval FROM valor WHERE iddom=:iddom ORDER BY nomval";
			$modelo = new conexion();
			$conexion = $modelo->get_conexion();
			$result = $conexion->prepare($sql);
			$result->bindParam(':iddom',$iddom);
			$result->execute();
			$res = $result->fetchAll(PDO::FETCH_ASSOC);
			return $res;
		}catch(Exception $e){
			ManejoError($e);
		}
	}

	public function getAllAreas(){
		try{
			$sql = "SELECT idare, nomare FROM area WHERE idare <> 0 ORDER BY nomare";
			$modelo = new conexion();
			$conexion = $modelo->get_conexion();
			$result = $conexion->prepare($sql);
			$result->execute();
			$res = $result->fetchAll(PDO::FETCH_ASSOC);
			return $res;
		}catch(Exception $e){
			ManejoError($e);
		}
	}
}
?>