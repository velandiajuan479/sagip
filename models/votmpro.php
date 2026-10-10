<?php
class Votmpro{
	//atributos
	private $npro;
	private $idusu;
	private $texpro;
	private $idval;

	//Métodos Get

	public function getNpro(){
        return $this->npro;
    }
    public function getIdusu(){
        return $this->idusu;
    }
    public function getTexpro(){
        return $this->texpro;
    }
    public function getIdval(){
        return $this->idval;
    }
	//Métodos Set
	public function setNpro($npro){
        $this->npro = $npro;
    }
    public function setIdusu($idusu){
        $this->idusu = $idusu;
    }
    public function setTexpro($texpro){
        $this->texpro = $texpro;
    }
    public function setIdval($idval){
        $this->idval = $idval;
    }

	public function getAll(){
		$sql = "SELECT p.npro, p.idusu, p.texpro, p.idval,v.iddom, v.nomval FROM propuesta AS p INNER JOIN valor AS v ON p.idval=v.idval";
		$modelo = new conexion();
		$conexion = $modelo->get_conexion();
		$result = $conexion->prepare($sql);
		$result->execute();
		$res = $result->fetchAll(PDO::FETCH_ASSOC);
		return $res;
	}
	public function getOne($iddom){
		$sql = "SELECT p.npro, p.idusu, p.texpro AS texpro, p.idval, v.iddom, v.nomval FROM propuesta AS p INNER JOIN valor AS v ON p.idval=v.idval WHERE p.idusu=:idusu AND v.iddom=:iddom ORDER BY v.idval";
		$modelo = new conexion();
		$conexion = $modelo->get_conexion();
		$result = $conexion->prepare($sql);
		$idusu = $this->getIdusu();
		$result->bindParam(":idusu",$idusu);
		$result->bindParam(":iddom",$iddom);
		$result->execute();
		$res = $result->fetchAll(PDO::FETCH_ASSOC);
		return $res;
	}
	public function save(){
		$npro = $this->getNpro();
		if ($npro) {
			$sql = "INSERT INTO propuesta(npro, idusu, texpro, idval) VALUES (:npro,:idusu,:texpro,:idval)";
		} else {
			$sql = "INSERT INTO propuesta(idusu, texpro, idval) VALUES (:idusu,:texpro,:idval)";
		}
		$modelo = new conexion();
		$conexion = $modelo->get_conexion();
		$result = $conexion->prepare($sql);
		$idusu = $this->getIdusu();
		$result->bindParam(":idusu",$idusu);
		$texpro = $this->getTexpro();
		$result->bindParam(":texpro",$texpro);
		$idval = $this->getIdval();
		$result->bindParam(":idval",$idval);
		if ($npro) {
			$result->bindParam(":npro",$npro);
		}
		$result->execute();
	}
	public function maxId(){
		$sql = "SELECT COALESCE(MAX(npro),0) AS maxn FROM propuesta";
		$modelo = new conexion();
		$conexion = $modelo->get_conexion();
		$result = $conexion->prepare($sql);
		$result->execute();
		$res = $result->fetch(PDO::FETCH_ASSOC);
		return (int)$res['maxn'];
	}
	public function edit(){
		$sql = "UPDATE propuesta SET idusu=:idusu, texpro=:texpro, idval=:idval WHERE npro=:npro";
		$modelo = new conexion();
		$conexion = $modelo->get_conexion();
		$result = $conexion->prepare($sql);
		$npro = $this->getNpro();
		$result->bindParam(":npro",$npro);
		$idusu = $this->getIdusu();
		$result->bindParam(":idusu",$idusu);
		$texpro = $this->getTexpro();
		$result->bindParam(":texpro",$texpro);
		$idval = $this->getIdval();
		$result->bindParam(":idval",$idval);
		$result->execute();
	}
	public function del(){
		$sql = "DELETE FROM propuesta WHERE idusu=:idusu";
		$modelo = new conexion();
		$conexion = $modelo->get_conexion();
		$result = $conexion->prepare($sql);
		$idusu = $this->getIdusu();
		$result->bindParam(":idusu",$idusu);
		$result->execute();

	}

	public function getUsu(){
		$sql = "SELECT u.idusu, u.ndocusu, u.nomusu, u.idper, p.nomper, uf.idfic, f.nomfic, 
		v.nomval, u.pasusu, u.emausu, u.idcen, c.nomcen, u.actusu, u.fotcan, u.telcan, u.noca, 
		f.jornada FROM usuario AS u INNER JOIN perfil AS p ON u.idper=p.idper INNER JOIN centro 
		AS c ON u.idcen=c.idcen LEFT JOIN usufic AS uf ON u.idusu=uf.idusu LEFT JOIN ficha AS 
		f ON uf.idfic=f.idfic LEFT JOIN valor AS v ON f.jornada=v.idval WHERE u.idusu=:idusu";
		$modelo = new conexion();
		$conexion = $modelo->get_conexion();
		$result = $conexion->prepare($sql);
		$result->bindParam(":idusu",$idusu);
		$idusu = $this->getIdusu();
		$result->execute();
		$res = $result->fetchAll(PDO::FETCH_ASSOC);
		return $res;
	}

	public function getVal($iddom){
		$sql = "SELECT idval, nomval, parval, act FROM valor WHERE iddom=:iddom ORDER BY idval";
		$modelo = new conexion();
		$conexion = $modelo->get_conexion();
		$result = $conexion->prepare($sql);
		$result->bindParam(":iddom",$iddom);
		$result->execute();
		$res = $result->fetchAll(PDO::FETCH_ASSOC);
		return $res;
	}

	public function getVideo(){
		$sql = "SELECT idvid, nomvid, rutvid, feccar FROM video WHERE idusu=:idusu AND actvid=1 ORDER BY idvid DESC LIMIT 1";
		$modelo = new conexion();
		$conexion = $modelo->get_conexion();
		$result = $conexion->prepare($sql);
		$idusu = $this->getIdusu();
		$result->bindParam(":idusu",$idusu);
		$result->execute();
		$res = $result->fetchAll(PDO::FETCH_ASSOC);
		return $res;
	}
	public function maxIdVid(){
		$sql = "SELECT COALESCE(MAX(idvid),0) AS maxv FROM video";
		$modelo = new conexion();
		$conexion = $modelo->get_conexion();
		$result = $conexion->prepare($sql);
		$result->execute();
		$res = $result->fetch(PDO::FETCH_ASSOC);
		return (int)$res['maxv'];
	}
	public function seeVideo(){
		$sql = "UPDATE video SET actvid=0 WHERE idusu=:idusu AND actvid=1 ORDER BY idvid DESC LIMIT 1";
		$modelo = new conexion();
		$conexion = $modelo->get_conexion();
		$result = $conexion->prepare($sql);
		$idusu = $this->getIdusu();
		$result->bindParam(":idusu",$idusu);
		$result->execute();
	}
	public function saveVideo($nomvid, $rutvid){
		$sql = "INSERT INTO video(idvid, nomvid, rutvid, ordvid, feccar, fecini, fecfin, actvid, idusu) VALUES (:idvid, :nomvid, :rutvid, 1, NOW(), CURDATE(), NULL, 1, :idusu)";
		$modelo = new conexion();
		$conexion = $modelo->get_conexion();
		$result = $conexion->prepare($sql);
		$idvid = $this->maxIdVid() + 1;
		$result->bindParam(":idvid",$idvid);
		$result->bindParam(":nomvid",$nomvid);
		$result->bindParam(":rutvid",$rutvid);
		$idusu = $this->getIdusu();
		$result->bindParam(":idusu",$idusu);
		$result->execute();
	}

}
if (!class_exists('Mpro')) {
    class_alias('Votmpro', 'Mpro');
}
?>