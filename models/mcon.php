<?php
class Mcon{
	private $idcof;
	private $logcof;
	private $titcof;
	private $descof;
	private $foocof;

	function getIdcof(){
		return $this->idcof;
	}
	function getLogcof(){
		return $this->logcof;
	}
	function getTitcof(){
		return $this->titcof;
	}
	function getDescof(){
		return $this->descof;
	}
	function getFoocof(){
		return $this->foocof;
	}

	function setIdcof($idcof){
		$this->idcof = $idcof;
	}
	function setLogcof($logcof){
		$this->logcof = $logcof;
	}
	function setTitcof($titcof){
		$this->titcof = $titcof;
	}
	function setDescof($descof){
		$this->descof = $descof;
	}
	function setFoocof($foocof){
		$this->foocof = $foocof;
	}

	function getOne(){
	    $sql = "SELECT idcof, logcof, titcof, descof, foocof FROM configuracion WHERE idcof=1";
	    $modelo = new conexion();
	    $conexion = $modelo->get_conexion();
	    $result = $conexion->prepare($sql);
	    $result->execute(); 
	    $res= $result->fetchall(PDO::FETCH_ASSOC);
	    return $res;
	}

	function edit(){
	    $logcof = $this->getLogcof();
	    $modelo = new conexion();
	    $conexion = $modelo->get_conexion();
	    $sql = "UPDATE configuracion SET titcof=:titcof, ";
	    if($logcof){
	        $sql .= "logcof=:logcof, ";
	    }
	    $sql .= "descof=:descof, foocof=:foocof WHERE idcof=:idcof";
	    $result = $conexion->prepare($sql);
	    $idcof = $this->getIdcof();
	    $result->bindParam(":idcof",$idcof);
	    $titcof = $this->getTitcof();
	    $result->bindParam(":titcof",$titcof);
	    $descof = $this->getDescof();
	    $result->bindParam(":descof",$descof);
	    $foocof = $this->getFoocof();
	    $result->bindParam(":foocof",$foocof);
	    if($foocof){
	        $result->bindParam(":foocof",$foocof);
	    }
	    $result->execute();
	}
}
?>