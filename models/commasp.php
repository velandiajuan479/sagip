<?php
require_once("conexion.php");

class Commasp{
	//Atributos
	private $idasp;
	private $idusu;
	private $codpro;
	private $fecreg;
	private $estado;
	private $ndocusu;
	private $nomasp;

	//METODOS GET
	function getIdasp(){
		return $this->idasp;
	}
	function getIdusu(){
		return $this->idusu;
	}
	function getCodpro(){
		return $this->codpro;
	}
	function getFecreg(){
		return $this->fecreg;
	}
	function getEstado(){
		return $this->estado;
	}
	function getNdocusu(){
		return $this->ndocusu;
	}
	function getNomasp(){
		return $this->nomasp;
	}

	//METODOS SET
	function setIdasp($idasp){
		$this->idasp = $idasp;
	}
	function setIdusu($idusu){
		$this->idusu = $idusu;
	}
	function setCodpro($codpro){
		$this->codpro = $codpro;
	}
	function setFecreg($fecreg){
		$this->fecreg = $fecreg;
	}
	function setEstado($estado){
		$this->estado = $estado;
	}
	function setNdocusu($ndocusu){
		$this->ndocusu = $ndocusu;
	}
	function setNomasp($nomasp){
		$this->nomasp = $nomasp;
	}

	//METODOS

	public function getAll(){
		try{
			$sql = "SELECT a.idasp, a.idusu, a.codpro, a.fecreg, a.estado,
					COALESCE(u.nomusu, a.nomasp) AS nomusu,
					COALESCE(u.ndocusu, a.ndocusu) AS ndocusu,
					p.nompro, f.idfic, f.nomfic
				FROM aspirante AS a
				LEFT JOIN usuario AS u ON a.idusu = u.idusu
				LEFT JOIN programa AS p ON a.codpro = p.codpro
				LEFT JOIN usufic AS uf ON uf.idusu = a.idusu AND uf.idfic = (SELECT uf2.idfic FROM usufic AS uf2 WHERE uf2.idusu = a.idusu AND uf2.actfic = 1 ORDER BY uf2.idfic DESC LIMIT 1)
				LEFT JOIN ficha AS f ON uf.idfic = f.idfic
				ORDER BY a.idasp DESC";
			$modelo = new conexion();
			$conexion = $modelo->get_conexion();
			$result = $conexion->prepare($sql);
			$result->execute();
			$res = $result->fetchAll(PDO::FETCH_ASSOC);
			return $res;
		}catch(Exception $e){
			ManejoError($e);
			return [];
		}
	}

	public function getOne(){
		try{
			$sql = "SELECT a.idasp, a.idusu, a.codpro, a.fecreg, a.estado,
					COALESCE(u.nomusu, a.nomasp) AS nomusu,
					COALESCE(u.ndocusu, a.ndocusu) AS ndocusu,
					p.nompro, f.idfic, f.nomfic
				FROM aspirante AS a
				LEFT JOIN usuario AS u ON a.idusu = u.idusu
				LEFT JOIN programa AS p ON a.codpro = p.codpro
				LEFT JOIN usufic AS uf ON uf.idusu = a.idusu AND uf.idfic = (SELECT uf2.idfic FROM usufic AS uf2 WHERE uf2.idusu = a.idusu AND uf2.actfic = 1 ORDER BY uf2.idfic DESC LIMIT 1)
				LEFT JOIN ficha AS f ON uf.idfic = f.idfic
				WHERE a.idasp = :idasp";
			$modelo = new conexion();
			$conexion = $modelo->get_conexion();
			$result = $conexion->prepare($sql);
			$idasp = $this->getIdasp();
			$result->bindParam(':idasp',$idasp);
			$result->execute();
			$res = $result->fetchAll(PDO::FETCH_ASSOC);
			return $res;
		}catch(Exception $e){
			ManejoError($e);
			return [];
		}
	}

	public function save(){
		try{
			$sql = "INSERT INTO aspirante (idusu, codpro, fecreg, estado, ndocusu, nomasp) 
					VALUES(:idusu, :codpro, :fecreg, :estado, :ndocusu, :nomasp)";
			$modelo = new conexion();
			$conexion = $modelo->get_conexion();
			$result = $conexion->prepare($sql);
			$result->bindParam(':idusu',$this->idusu);
			$result->bindParam(':codpro',$this->codpro);
			$result->bindParam(':fecreg',$this->fecreg);
			$result->bindParam(':estado',$this->estado);
			$result->bindParam(':ndocusu',$this->ndocusu);
			$result->bindParam(':nomasp',$this->nomasp);
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
			$sql = "UPDATE aspirante SET idusu=:idusu, codpro=:codpro, fecreg=:fecreg, estado=:estado, ndocusu=:ndocusu, nomasp=:nomasp WHERE idasp=:idasp";
			$modelo = new conexion();
			$conexion = $modelo->get_conexion();
			$result = $conexion->prepare($sql);
			$result->bindParam(':idasp',$this->idasp);
			$result->bindParam(':idusu',$this->idusu);
			$result->bindParam(':codpro',$this->codpro);
			$result->bindParam(':fecreg',$this->fecreg);
			$result->bindParam(':estado',$this->estado);
			$result->bindParam(':ndocusu',$this->ndocusu);
			$result->bindParam(':nomasp',$this->nomasp);
			$result->execute();
			return true;
		}catch(Exception $e){
			ManejoError($e);
			return false;
		}
	}

	public function eliminar(){
		try{
			$sql = "DELETE FROM aspirante WHERE idasp=:idasp";
			$modelo = new conexion();
			$conexion = $modelo->get_conexion();
			$result = $conexion->prepare($sql);
			$result->bindParam(':idasp',$this->idasp);
			$result->execute();
			return true;
		}catch(Exception $e){
			ManejoError($e);
			return false;
		}
	}

	public function getUsuByDoc($ndocusu){
		try{
			$sql = "SELECT u.idusu, u.nomusu, u.ndocusu, u.emausu, u.telcan
				FROM usuario AS u
				WHERE u.ndocusu = :ndocusu
				LIMIT 1";
			$modelo = new conexion();
			$conexion = $modelo->get_conexion();
			$result = $conexion->prepare($sql);
			$result->bindParam(':ndocusu',$ndocusu);
			$result->execute();
			$res = $result->fetchAll(PDO::FETCH_ASSOC);
			return $res;
		}catch(Exception $e){
			ManejoError($e);
			return [];
		}
	}

	public function createUser($ndocusu, $nomusu){
		try{
			$sql = "INSERT INTO usuario (ndocusu, nomusu, idper, pasusu, idcen, actusu, colfon, coltex, bloqkey, rhusu, tdousu, genusu)
					VALUES (:ndocusu, :nomusu, 2, SHA1(:ndocusu), 1, 1, 'ffffff', '000000', 0, 0, 1001, 1004)";
			$modelo = new conexion();
			$conexion = $modelo->get_conexion();
			$result = $conexion->prepare($sql);
			$result->bindParam(':ndocusu', $ndocusu);
			$result->bindParam(':nomusu', $nomusu);
			$result->execute();
			return $conexion->lastInsertId();
		}catch(Exception $e){
			ManejoError($e);
			return null;
		}
	}

	public function getAllComplementarias(){
		try{
			$sql = "SELECT p.codpro, p.nompro, p.horlpro
				FROM programa AS p
				WHERE p.tippro = 1083 OR p.tippro IS NULL
				ORDER BY p.nompro";
			$modelo = new conexion();
			$conexion = $modelo->get_conexion();
			$result = $conexion->prepare($sql);
			$result->execute();
			$res = $result->fetchAll(PDO::FETCH_ASSOC);
			return $res;
		}catch(Exception $e){
			ManejoError($e);
			return [];
		}
	}
}
?>