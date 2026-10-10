<?php
class Mmcon{
//Atributos
	private $nummin;
	private $idusu;
	private $orimin;
	private $fechos;
	private $idrut;
	private $tipmin;
	private $fotusu;
	private $maq;

	private $fhlle;

	private $ndocusu;

// Métodos Get devuelven datos
	function getNummin(){
		return $this->nummin;
	}
	function getIdusu(){
		return $this->idusu;
	}
	function getOrimin(){
		return $this->orimin;
	}
	function getFechos(){
		return $this->fechos;
	}
	function getIdrut(){
		return $this->idrut;
	}
	function getTipmin(){
		return $this->tipmin;
	}
	function getFotusu(){
		return $this->fotusu;
	}
	function getFhlle(){
		return $this->fhlle;
	}
	function getMaq(){
		return $this->maq;
	}

	function getNdocusu(){
		return $this->ndocusu;
	}
//Métodos Set guardan datos
	function setNummin($nummin){
		$this->nummin = $nummin;
	}
	function setIdusu($idusu){
		$this->idusu = $idusu;
	}
	function setOrimin($orimin){
		$this->orimin = $orimin;
	}
	function setFechos($fechos){
		$this->fechos = $fechos;
	}
	function setIdrut($idrut){
		$this->idrut = $idrut;
	}
	function setTipmin($tipmin){
		$this->tipmin = $tipmin;
	}
	function setFotusu($fotusu){
		$this->fotusu = $fotusu;
	}
	function setFhlle($fhlle){
		$this->fhlle = $fhlle;
	}
	function setMaq($maq){
		$this->maq = $maq;
	}

	function setNdocusu($ndocusu){
		$this->ndocusu = $ndocusu;
	}

//Métodos CRUD
	function getAll(){
		$sql = "SELECT m.nummin, m.idusu, u.ndocusu, u.nomusu, u.idfic, f.nomfic, v.nomval, m.fechos, m.fhlle, u.fotcan, m.obs FROM minuta AS m LEFT JOIN usuario AS u ON m.idusu=u.idusu INNER JOIN ficha AS f ON u.idfic=f.idfic INNER JOIN valor AS v ON f.jornada=v.idval";
		$modelo = new conexion();
		$conexion = $modelo->get_conexion();
		$result = $conexion->prepare($sql);
		$result->execute();
		$res = NULL;
		while($f=$result->fetch())
			$res[]=$f;
		return $res;
	}
	function getOne(){
		$sql = "SELECT m.nummin, m.idusu, u.ndocusu, u.nomusu, u.idfic, f.nomfic, v.nomval, m.fechos, m.fhlle, u.fotcan, m.obs FROM minuta AS m LEFT JOIN usuario AS u ON m.idusu=u.idusu INNER JOIN ficha AS f ON u.idfic=f.idfic INNER JOIN valor AS v ON f.jornada=v.idval WHERE m.nummin=:nummin";
		$modelo = new conexion();
		$conexion = $modelo->get_conexion();
		$result = $conexion->prepare($sql);
		$nummin = $this->getNummin();
		$result->bindParam(':nummin',$nummin);
		$result->execute();
		$res = NULL;
		while($f=$result->fetch())
			$res[]=$f;
		return $res;
	}
	function save(){
		$sql = "INSERT INTO minuta(nummin, idusu, tipmin, fechos) VALUES (:nummin, :idusu, :tipmin, :fechos)";
		$modelo = new conexion();
		$conexion = $modelo->get_conexion();
		$result = $conexion->prepare($sql);
		$nummin=$this->getNummin();
		$result->bindParam(':nummin',$nummin);
		$idusu=$this->getIdusu();
		$result->bindParam(':idusu',$idusu);
		$tipmin=$this->getTipmin();
		$result->bindParam(':tipmin',$tipmin);
		$fechos=$this->getFechos();
		$result->bindParam(':fechos',$fechos);
		$result->execute();
		$res = $conexion->lastInsertId();
		return $res;
	}

	function del(){
		$sql = "DELETE FROM usuario WHERE idusu=:idusu";
		$modelo = new conexion();
		$conexion = $modelo->get_conexion();
		$result = $conexion->prepare($sql);
		$idusu = $this->getIdusu();
		$result->bindParam(':idusu',$idusu);
		//echo $sql."<br>";
		//echo $idusu."<br>";
		$result->execute();
		$res = NULL;
		while($f=$result->fetch())
			$res[]=$f;
		return $res;
	}


	function getUsuario(){
		$sql = "SELECT u.idusu, u.ndocusu, u.nomusu, u.fotcan, u.idfic, f.nomfic, v.nomval FROM usuario AS u INNER JOIN ficha AS f ON u.idfic=f.idfic INNER JOIN valor AS v ON f.jornada=v.idval";

		$modelo = new conexion();
		$conexion = $modelo->get_conexion();
		$result = $conexion->prepare($sql);
		$ndocusu = $this->getNdocusu();
		$result->bindParam(':ndocusu',$ndocusu);
	// echo "<br>".$sql."<br>'".$ndocusu."'<br>";
	// die();	
		$result->execute();
		$res = NULL;
		while($f=$result->fetch())
			$res[]=$f;
		return $res;
	}

	function getRepetido(){
		$sql = "SELECT COUNT(nummin) AS cot FROM minuta WHERE idusu=:idusu AND tipmin=:tipmin AND hij=0";
		$modelo = new conexion();
		$conexion = $modelo->get_conexion();
		$result = $conexion->prepare($sql);
		$idusu = $this->getIdusu();
		$result->bindParam(':idusu',$idusu);
		$tipmin = 'I';//$this->getTipmin();
		$result->bindParam(':tipmin',$tipmin);
		//echo "<br>".$sql."<br>'".$idusu."' '".$tipmin."'<br>";
		$result->execute();
		$res = NULL;
		while($f=$result->fetch())
			$res[]=$f;
		return $res;
	}

	function updHij($fhlle,$nummin){
		$sql = "UPDATE minuta SET hij=1, tipmin='S', fhlle=:fhlle WHERE nummin=:nummin;";
		$modelo = new conexion();
		$conexion = $modelo->get_conexion();
		$result = $conexion->prepare($sql);
		$result->bindParam(':nummin',$nummin);
		$result->bindParam(':fhlle',$fhlle);
		//echo $sql."<br>";
		//echo $idusu."<br>";
		$result->execute();
	}

	function getVuelta(){
		$sql = "SELECT COUNT(nummin) AS can FROM minuta WHERE idusu=:idusu AND tipmin='I' AND fhlle IS NULL;";
		$modelo = new conexion();
		$conexion = $modelo->get_conexion();
		$result = $conexion->prepare($sql);
		$idusu = $this->getIdusu();
		$result->bindParam(':idusu',$idusu);
		// echo "<br>".$sql."<br>'".$idusu."'<br>";
		// die();
		$result->execute();
		$res = NULL;
		while($f=$result->fetch())
			$res[]=$f;
		return $res;
	}

	function getExiste($idusu){
		$sql = "SELECT nummin, idusu, fechos, tipmin, hij, fhlle FROM minuta WHERE idusu='$idusu' AND tipmin='I' AND fhlle IS NULL;";
		$modelo = new conexion();
		$conexion = $modelo->get_conexion();
		$result = $conexion->prepare($sql);
		$result->execute();
		$res = NULL;
		while($f=$result->fetch())
			$res[]=$f;
		return $res;
	}

	function updFecAnt(){
		$sql = "UPDATE minuta SET fhlle=concat(DATE(fechos),' 23:59:59'),tipmin='S',obs='Sistema.' WHERE fhlle IS NULL AND fechos<CURDATE();";
		$modelo = new conexion();
		$conexion = $modelo->get_conexion();
		$result = $conexion->prepare($sql);
		$result->execute();
	}

	function getDelFot(){
		$sql = "SELECT * FROM minuta WHERE fechos<DATE_SUB(NOW(),INTERVAL '1' MONTH) AND foteli=1;";
		$modelo = new conexion();
		$conexion = $modelo->get_conexion();
		$result = $conexion->prepare($sql);
		$result->execute();
		$res = NULL;
		while($f=$result->fetch())
			$res[]=$f;
		return $res;
	}

	function updFecEli(){
		$sql = "UPDATE minuta SET foteli=2 WHERE fechos<DATE_SUB(NOW(),INTERVAL '1' MONTH) AND foteli=1";
		$modelo = new conexion();
		$conexion = $modelo->get_conexion();
		$result = $conexion->prepare($sql);
		$result->execute();
	}
}
?>