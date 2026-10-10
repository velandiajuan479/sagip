<?php
require_once('conexion.php');
class Mcon{
	const NACTA = 'GFPI-F-015';
	const VERACTA = 'V.3';

	private $idcof;
	private $logcof;
	private $logbcof;
	private $logwcof;
	private $logsis;
	private $logdoc;
	private $titcof;
	private $descof;
	private $foocof;

	function getIdcof(){
		return $this->idcof;
	}
	function getLogcof(){
		return $this->logcof;
	}
	function getLogbcof(){
		return $this->logbcof;
	}
	function getLogwcof(){
		return $this->logwcof;
	}
	function getLogsis(){
		return $this->logsis;
	}
	function getLogdoc(){
		return $this->logdoc;
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
	function setLogbcof($logbcof){
		$this->logbcof = $logbcof;
	}
	function setLogwcof($logwcof){
		$this->logwcof = $logwcof;
	}
	function setLogsis($logsis){
		$this->logsis = $logsis;
	}
	function setLogdoc($logdoc){
		$this->logdoc = $logdoc;
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
	    try {
		    $sql = "SELECT idcof, logcof, logbcof, logwcof, logsis, logdoc, titcof, descof, foocof FROM configuracion WHERE idcof=1";
		    $modelo = new conexion();
		    $conexion = $modelo->get_conexion();
		    $result = $conexion->prepare($sql);
		    $result->execute(); 
		    return $result->fetchall(PDO::FETCH_ASSOC);
	    } catch (Throwable $e) {
	        return array();
	    }
	}

	function borrarLogo($campo){
	    $permitidos = array('logcof','logbcof');
	    if(!in_array($campo, $permitidos)) return true;
	    try {
		    $modelo = new conexion();
		    $conexion = $modelo->get_conexion();
		    $sql = "UPDATE configuracion SET ".$campo."='' WHERE idcof=1";
		    $result = $conexion->prepare($sql);
		    $result->execute();
		    return true;
	    } catch (Throwable $e) {
	        return false;
	    }
	}

	function actualizaPuntero($campo, $valor){
	    $permitidos = array('logsis','logdoc');
	    if(!in_array($campo, $permitidos)) return true;
	    try {
		    $modelo = new conexion();
		    $conexion = $modelo->get_conexion();
		    $sql = "UPDATE configuracion SET ".$campo."=:valor WHERE idcof=1";
		    $result = $conexion->prepare($sql);
		    $result->bindValue(':valor', $valor);
		    $result->execute();
		    return true;
	    } catch (Throwable $e) {
	        return false;
	    }
	}

	function edit(){
	    try {
		    $modelo = new conexion();
		    $conexion = $modelo->get_conexion();
		    $sql = "UPDATE configuracion SET logcof=:logcof, logbcof=:logbcof, logwcof=:logwcof, logsis=:logsis, logdoc=:logdoc, titcof=:titcof, descof=:descof, foocof=:foocof WHERE idcof=:idcof";
		    $result = $conexion->prepare($sql);
		    $idcof = $this->getIdcof();
		    $result->bindParam(":idcof",$idcof);
		    $logcof = $this->getLogcof();
		    $result->bindParam(":logcof",$logcof);
		    $logbcof = $this->getLogbcof();
		    $result->bindParam(":logbcof",$logbcof);
		    $logwcof = $this->getLogwcof();
		    $result->bindParam(":logwcof",$logwcof);
		    $logsis = $this->getLogsis();
		    $result->bindParam(":logsis",$logsis);
		    $logdoc = $this->getLogdoc();
		    $result->bindParam(":logdoc",$logdoc);
		    $titcof = $this->getTitcof();
		    $result->bindParam(":titcof",$titcof);
		    $descof = $this->getDescof();
		    $result->bindParam(":descof",$descof);
		    $foocof = $this->getFoocof();
		    $result->bindParam(":foocof",$foocof);
		    $result->execute();
		    return true;
	    } catch (Throwable $e) {
	        return false;
	    }
	}
}
?>











