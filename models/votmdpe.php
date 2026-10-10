<?php
require_once __DIR__ . '/conexion.php';

class Mdpe{
	private $idusu;
    private $nomusu;
	private $idper;
    private $nomdom;
	private $nomper;
    private $idfic;
    private $nomfic;
    private $nomval;
    private $pasusu;
    private $emausu;
    private $idcen;
    private $nomcen;
    private $actusu;
    private $fotcan;
    private $telcan;
    private $ndocusu;
///////////////////Metodos get
function getIdusu(){
	return $this->idusu;
}
function getNomusu(){
	return $this->nomusu;
}
function getIdper(){
	return $this->idper;
}
function getNomdom(){
	return $this->nomdom;
}
function getNomper(){
	return $this->nomper;
}
function getIdfic(){
	return $this->idfic;
}
function getNomfic(){
	return $this->nomfic;
}
function getNomval(){
	return $this->nomval;
}
function getPasusu(){
	return $this->pasusu;
}
function getEmausu(){
	return $this->emausu;
}
function getIdcen(){
	return $this->idcen;
}
function getNomcen(){
	return $this->nomcen;
}
function getActusu(){
	return $this->actusu;
}
function getFotcan(){
	return $this->fotcan;
}
function getTelcan(){
	return $this->telcan;
}
function getNdocusu(){
	return $this->ndocusu;
}
///////////////////Metodos set
function setIdusu($idusu){
	$this->idusu = $idusu;
}
function setNomusu($nomusu){
	$this->nomusu = $nomusu;
}
function setIdper($idper){
	$this->idper = $idper;
}
function setNomdom($nomdom){
	$this->nomdom = $nomdom;
}
function setNomper($nomper){
	$this->nomper = $nomper;
}
function setIdfic($idfic){
	$this->idfic = $idfic;
}
function setNomfic($nomfic){
	$this->nomfic = $nomfic;
}
function setNomval($nomval){
	$this->nomval = $nomval;
}
function setPasusu($pasusu){
	$this->pasusu = $pasusu;
}
function setEmausu($emausu){
 	$this->emausu = $emausu;
}
function setIdcen($idcen){
	$this->idcen = $idcen;
}
function setNomcen($nomcen){
	$this->nomcen = $nomcen;
}
function setActusu($actusu){
	$this->actusu = $actusu;
}
function setFotcan($fotcan){
	$this->fotcan = $fotcan;
}
function setTelcan($telcan){
	$this->telcan = $telcan;
}
function setNdocusu($ndocusu){
	$this->ndocusu = $ndocusu;
}

	//metodo para verificar el voto a  representante
	public function yaVotoRepresentante($idusu) {
		$sql = "SELECT COUNT(*) FROM voto WHERE idusu = :idusu AND tipo_voto = 'representante'";
		$modelo = new conexion();
		$conexion = $modelo->get_conexion();
		$result = $conexion->prepare($sql);
		$result->bindParam(":idusu", $idusu);
		$result->execute();
		return $result->fetchColumn() > 0;
	}

	//metodo para verificar el voto a vocero
	public function yaVotoVocero($idusu) {
		$sql = "SELECT COUNT(*) FROM voto WHERE idusu = :idusu AND tipo_voto = 'vocero'";
		$modelo = new conexion();
		$conexion = $modelo->get_conexion();
		$result = $conexion->prepare($sql);
		$result->bindParam(":idusu", $idusu);
		$result->execute();
		return $result->fetchColumn() > 0;
	}

	function selAll(){
		$sql = "SELECT u.idusu, u.ndocusu, u.nomusu, u.idper, p.nomper, f.idfic, f.nomfic, v.nomval, u.pasusu, u.emausu, u.idcen, c.nomcen, u.actusu, u.fotcan, u.telcan FROM usuario AS u LEFT JOIN perfil AS p ON u.idper=p.idper LEFT JOIN centro AS c ON u.idcen=c.idcen LEFT JOIN usufic AS uf ON u.idusu=uf.idusu LEFT JOIN ficha AS f ON uf.idfic=f.idfic LEFT JOIN valor AS v ON f.jornada=v.idval;";
		$modelo = new conexion();
		$conexion = $modelo->get_conexion();
		$result = $conexion->prepare($sql);
		$result->execute();
		$res = $result-> fetchall(PDO::FETCH_ASSOC);
		return $res;
	}
	function selOne(){
		$sql = "SELECT u.idusu, u.ndocusu, u.nomusu, u.idper, p.nomper, f.idfic, f.nomfic, pr.nompro, v.idval, v.nomval, u.pasusu, u.emausu, u.idcen, c.nomcen, u.actusu, u.fotcan, u.telcan FROM usuario AS u LEFT JOIN perfil AS p ON u.idper=p.idper LEFT JOIN centro AS c ON u.idcen=c.idcen LEFT JOIN usufic AS uf ON u.idusu=uf.idusu LEFT JOIN ficha AS f ON uf.idfic=f.idfic LEFT JOIN programa AS pr ON f.codpro=pr.codpro LEFT JOIN valor AS v ON f.jornada=v.idval WHERE u.idusu=:idusu";
		$modelo = new conexion();
		$conexion = $modelo->get_conexion();
		$result = $conexion->prepare($sql);
		$idusu = $this->getIdusu();
		$result->bindParam(':idusu',$idusu);
		$result->execute();
		$res = $result-> fetchall(PDO::FETCH_ASSOC);
		return $res;
	}
	function ins(){
		$sql = "INSERT INTO usuario(ndocusu, nomusu, idper, idfic, pasusu, idcen, actusu) VALUES (:ndocusu, :nomusu, :idper, :idfic, :pasusu, :idcen, :actusu)";
		$modelo = new conexion();
		$conexion = $modelo->get_conexion();
		$result = $conexion->prepare($sql);
		$ndocusu = $this->getNdocusu();
		$result->bindParam(":ndocusu",$ndocusu);
		$nomusu = $this->getNomusu();
		$result->bindParam(":nomusu",$nomusu);
		$idper = $this->getIdper();
		$result->bindParam(":idper",$idper);
		$idper = $this->getIdfic();
		$result->bindParam(":idfic",$idfic);
		$pasusu = $this->getPasusu();
		$result->bindParam(":pasusu",$pasusu);
		$idcen = $this->getIdcen();	
		$result->bindParam(":idcen",$idcen);
		$actusu = $this->getActusu();		
		$result->bindParam(":actusu",$actusu);
		$result->execute();
	}
	 function upd(){
		$pasusu = $this->getPasusu();
		$sql = "UPDATE usuario SET ndocusu=:ndocusu,nomusu=:nomusu,idper=:idper,idfic=:idfic,";
		$modelo = new conexion();
		$conexion = $modelo->get_conexion();
		$result = $conexion->prepare($sql);
		if($pasusu) $sql .= "pasusu=:pasusu,";
		$sql .= "idcen=:idcen,actusu=:actusu WHERE idusu=:idusu";
		$idusu = $this->getIdusu();
		$result->bindParam(":idusu",$idusu);
		$ndocusu = $this->getNdocusu();
		$result->bindParam(":ndocusu",$ndocusu);
		$nomusu = $this->getNomusu();
		$result->bindParam(":nomusu",$nomusu);
		$idper = $this->getIdper();
		$result->bindParam(":idper",$idper);
		$idfic = $this->getIdfic();
		$result->bindParam(":idfic",$idfic);
		if($pasusu) $result->bindParam(":pasusu",$pasusu);
		$idcen = $this->getIdcen();
		$result->bindParam(":idcen",$idcen);
		$actusu = $this->getActusu();
		$result->bindParam(":actusu",$actusu);
		$result->execute();
	}
	function getCentro(){
        $sql = "SELECT idcen, nomcen FROM centro";
        $modelo =new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->execute();
        $res = $result-> fetchall(PDO::FETCH_ASSOC);
        return $res;
    }
	function getPerfil(){
		$sql = "SELECT idper, nomper FROM perfil";
		$modelo = new conexion();
		$conexion = $modelo->get_conexion();
		$result = $conexion->prepare($sql);
		$result->execute();
        $res = $result-> fetchall(PDO::FETCH_ASSOC);
        return $res;
	}
	
	function getFicha(){
		$sql = "SELECT f.idfic, f.nomfic, v.nomval FROM ficha AS f INNER JOIN valor AS v ON f.jornada=v.idval";
		$modelo = new conexion();
		$conexion = $modelo->get_conexion();
		$result = $conexion->prepare($sql);
		$result->execute();
        $res = $result-> fetchall(PDO::FETCH_ASSOC);
        return $res;
	}
	
	function selVot(){
		$sql = "SELECT count(idusu) As No FROM voto WHERE idusu=:idusu";
		$modelo = new conexion();
		$conexion = $modelo->get_conexion();
		$result = $conexion->prepare($sql);
		$idusu = $this->getIdusu();
		$result->bindParam(":idusu",$idusu);
		$result->execute();
        $res = $result-> fetchall(PDO::FETCH_ASSOC);
        return $res;
	}
	function del(){
	   $sql = "DELETE FROM usuario WHERE idusu=:idusu";
	   $modelo = new conexion();
	   $conexion = $modelo->get_conexion();
	   $result = $conexion->prepare($sql);
	   $idusu = $this->getIdusu();
	   $result->bindParam(":idusu",$idusu);
	   $result->execute();
	
	}
}

