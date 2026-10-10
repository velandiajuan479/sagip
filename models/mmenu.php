<?php
class Mmenu{
//Atributos
	private $idpag;
	private $idper;
// Metodos GET devuelven el dato
	function getIdpag(){
		return $this->idpag;
	}
	function getIdper(){
		return $this->idper;
	}
// Metodos SET guardar el dato
	function setIdper($idper){
		$this->idper = $idper;
	}
	function setIdpag($idpag){
		$this->idpag = $idpag;
	}
	

	public function getMen(){
		$sql = "SELECT p.idpag, p.nompag, p.rutpag, p.icopag FROM pagina AS p INNER JOIN pagper AS j ON p.idpag=j.idpag WHERE p.mospas=1 AND j.idper=:idper ORDER BY p.ordpag;";
		$modelo = new conexion();
		$conexion = $modelo->get_conexion();
		$result = $conexion->prepare($sql);
		$idper = $this->getIdper();
		$result->bindParam(':idper',$idper);
		$result->execute();
		$res = $result ->fetchAll(PDO::FETCH_ASSOC);
		return $res;
	}

	public function getVal(){
		$sql = "SELECT p.idpag, p.nompag, p.rutpag, p.icopag, p.mospas FROM pagina AS p INNER JOIN pagper AS g ON p.idpag=g.idpag WHERE p.idpag=:idpag AND g.idper=:idper";
		$modelo = new conexion();
		$conexion = $modelo->get_conexion();
		$result = $conexion->prepare($sql);
		$idper = $this->getIdper();
		$idpag = $this->getIdpag();
		$result->bindParam(':idper',$idper);
		$result->bindParam(':idpag',$idpag);
		$result->execute();
		$res = $res = $result ->fetchAll(PDO::FETCH_ASSOC);
		return $res;
	}
}
?>