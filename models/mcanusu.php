<?php
class Mcanusu{
	private $tipmin;
	private $idele;
	private $tipele;
	private $nomele; 

	//METODOS GET
	public function getTipmin(){
	    return $this->tipmin;
}
	public function getIdele(){
	    return $this->idele;
}
	public function getTipele(){
	    return $this->tipele;
}
public function getNomele(){
	return $this->nomele;
}
    //METODOS SET
	public function setTipmin($tipmin){
		$this->tipmin = $tipmin;
}
	public function setIdele($idele){
		$this->idele = $idele;
}
	public function setTipele($tipele){
		$this->tipele = $tipele;
}
public function setNomele($nomele){
	$this->tipele = $nomele;
}

function getAll(){
	$sql = "SELECT  m.tipmin e.idele e.tipele e.nomele FROM minuta AS m INNER JOIN elemento AS e INNER JOIN usuario AS u ON m.idusu = e.idusu";
	$modelo = new conexion();
	$conexion =$modelo->get_conexion();
	$result= $conexion->prepare($sql);
	$result->execute();
	$res = $result->fetchAll(PDO::FETCH_ASSOC);
	return $res;
}

	function getTotUsu($tipmin){
		$sql = "SELECT COUNT(DISTINCT idusu) AS ctn FROM minuta WHERE fechos BETWEEN concat(CURDATE(),' 00:00:00') AND concat(CURDATE(),' 23:59:59') AND tipmin=:tipmin";
		$modelo = new conexion();
		$conexion = $modelo->get_conexion();
		$result = $conexion->prepare($sql);
		//$tipmin=$this->getTipmin();
		$result->bindParam(':tipmin',$tipmin);
        $result->execute();
        $res = $result->fetchAll(PDO::FETCH_ASSOC);
        return $res;	
	}

	function getCicla(){
		$sql = "SELECT GROUP_CONCAT(ideles SEPARATOR ';') AS ciclas FROM minuta AS m LEFT JOIN elemento As e ON m.ideles = e.idele WHERE tipmin = 'I' AND fechos BETWEEN concat(CURDATE(),' 00:00:00') AND concat(CURDATE(),' 23:59:59')";
		$modelo = new conexion();
		$conexion = $modelo->get_conexion();
		$result = $conexion->prepare($sql);
		$result->execute();
		$res = NULL;
		$res = $result->fetchAll(PDO::FETCH_ASSOC);
		return $res;
	}


	public function solo($idele, $tipele){
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();    
        $sql="SELECT idele FROM elemento WHERE tipele=:tipele AND idele = :idele;";
        $result = $conexion->prepare($sql);
		
        $result->bindParam(":idele", $idele);
		
		$result->bindParam(":tipele", $tipele);
        $result->execute();
		$res = $result->fetchAll(PDO::FETCH_ASSOC);
		return $res;
    }


	function getSal(){
		$sql = "SELECT GROUP_CONCAT(ideles SEPARATOR ';') AS ciclas FROM minuta AS m LEFT JOIN elemento As e ON m.ideles = e.idele WHERE tipmin != 'I' AND fechos BETWEEN concat(CURDATE(),' 00:00:00') AND concat(CURDATE(),' 23:59:59')";
		$modelo = new conexion();
		$conexion = $modelo->get_conexion();
		$result = $conexion->prepare($sql);
		$result->execute();
		$res = $result->fetchAll(PDO::FETCH_ASSOC);
		return $res;
	}

	public function getEntrSalForFic($idusu){
		$sql = "SELECT m.idusu, m.tipmin, m.fechos
				FROM minuta AS m
				INNER JOIN usufic AS uf ON m.idusu = uf.idusu
				WHERE uf.idfic IN (
					SELECT idfic FROM usufic WHERE idusu = :idusu
				)
				AND m.fechos BETWEEN CONCAT(CURDATE(), ' 00:00:00') AND CONCAT(CURDATE(), ' 23:59:59')
				ORDER BY m.fechos ASC
				";
	
		$modelo = new conexion();
		$conexion = $modelo->get_conexion();
		$result = $conexion->prepare($sql);
		$result->bindParam(":idusu", $idusu);
		$result->execute();
		$res = $result->fetchAll(PDO::FETCH_ASSOC);
		return $res;
	}	
	
}
?>