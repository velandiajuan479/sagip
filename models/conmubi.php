<?php
class Mubi{
	private $codubi;
	private $nomubi;
	private $depubi;

	function getCodubi(){
		return $this->codubi;
	}
	function getNomubi(){
		return $this->nomubi;
	}
	function getDepubi(){
		return $this->depubi;
	}

	function setCodubi($codubi){
		$this->codubi = $codubi;
	}
	function setNomubi($nomubi){
		$this->nomubi = $nomubi;
	}
	function setDepubi($depubi){
		$this->depubi = $depubi;
	}

	public function getAll(){
		try {
			$sql = "SELECT d.codubi AS cd, d.nomubi AS nd
			, m.codubi AS cm,m.nomubi AS nm FROM
			 ubica AS m LEFT JOIN ubica AS d ON m.depubi
			 =d.codubi";
			$modelo = new conexion();
			$conexion = $modelo->get_conexion();
			$result = $conexion->prepare($sql);
			$result->execute();
			return $result->fetchall(PDO::FETCH_ASSOC);
		} catch (Throwable $e) {
			return array();
		}
	}
	public function getOne(){
		try {
			$sql = "SELECT * FROM ubica WHERE codubi=:codubi";
			$modelo = new conexion();
			$conexion = $modelo->get_conexion();
			$result = $conexion->prepare($sql);
			$codubi = $this->getCodubi();
			$result->bindParam(":codubi", $codubi);
			$result->execute();
			return $result->fetchall(PDO::FETCH_ASSOC);
		} catch (Throwable $e) {
			return array();
		}
	}
	public function save(){
		try {
			$sql = "INSERT INTO ubica (codubi, nomubi, depubi) VALUES (:codubi, :nomubi, :depubi)";
			$modelo = new conexion();
			$conexion = $modelo->get_conexion();
			$result = $conexion->prepare($sql);
			$codubi = $this->getCodubi();
			$result->bindParam(":codubi", $codubi);
			$nomubi = $this->getNomubi();
			$result->bindParam(":nomubi", $nomubi);
			$depubi = $this->getDepubi();
			$result->bindParam(":depubi", $depubi);
			$result->execute();
			return true;
		} catch (Throwable $e) {
			return false;
		}
	}
	public function edit(){
		try {
			$sql = "UPDATE ubica SET nomubi=:nomubi, depubi=:depubi WHERE codubi=:codubi";
			$modelo = new conexion();
			$conexion = $modelo->get_conexion();
			$result = $conexion->prepare($sql);
			$codubi = $this->getCodubi();
			$result->bindParam(":codubi", $codubi);
			$nomubi = $this->getNomubi();
			$result->bindParam(":nomubi", $nomubi);
			$depubi = $this->getDepubi();
			$result->bindParam(":depubi", $depubi);
			$result->execute();
			return true;
		} catch (Throwable $e) {
			return false;
		}
	}

	public function del(){
		try {
			$sql = "DELETE FROM ubica WHERE codubi=:codubi";
			$modelo = new conexion();
			$conexion = $modelo->get_conexion();
			$result = $conexion->prepare($sql);
			$codubi = $this->getCodubi();
			$result->bindParam(":codubi", $codubi);
			$result->execute();
			return true;
		} catch (Throwable $e) {
			return false;
		}
	}

	public function getStats(){
		try {
			$sql = "SELECT
				(SELECT COUNT(*) FROM ubica) AS total,
				(SELECT COUNT(*) FROM ubica WHERE depubi=0) AS deps,
				(SELECT COUNT(DISTINCT a.codubi) FROM aula a INNER JOIN ubica u ON a.codubi=u.codubi) AS activas";
			$modelo = new conexion();
			$conexion = $modelo->get_conexion();
			$result = $conexion->prepare($sql);
			$result->execute();
			return $result->fetch(PDO::FETCH_ASSOC);
		} catch (Throwable $e) {
			return array('total'=>0,'deps'=>0,'activas'=>0);
		}
	}

	public function getDep($depubi){
		try {
			$modelo = new conexion();
			$conexion = $modelo->get_conexion();
			$sql="SELECT * FROM ubica WHERE depubi=:depubi
				ORDER BY nomubi";
			$result = $conexion->prepare($sql);
			$result->bindParam(":depubi", $depubi);
			$result->execute();
			return $result->fetchall(PDO::FETCH_ASSOC);
		} catch (Throwable $e) {
			return array();
		}
	}
}
?>