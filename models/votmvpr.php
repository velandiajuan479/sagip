<?php
class Mvvpr{
	private $idusu;
	private $texpro;
	private $idval;

	public function getIdusu(){
		return $this->idusu;
	}
	public function getTexpro(){
		return $this->texpro;
	}
	public function getIdval(){
		return $this->idval;
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

	/** Personas que han registrado propuesta en la tabla propuesta. */
	public function getCand(){
		$sql = "SELECT DISTINCT u.idusu, u.noca, u.nomusu, u.fotcan, u.idcen, uf.idfic, f.nomfic, f.jornada, c.nomcen, v.nomval AS nomjor
			FROM usuario AS u
			INNER JOIN propuesta AS pr ON pr.idusu = u.idusu
			LEFT JOIN centro AS c ON u.idcen = c.idcen
			LEFT JOIN usufic AS uf ON u.idusu = uf.idusu
			LEFT JOIN ficha AS f ON uf.idfic = f.idfic
			LEFT JOIN valor AS v ON f.jornada = v.idval
			WHERE (u.noca IS NULL OR u.noca <> '999')
			ORDER BY u.noca IS NULL, u.noca, u.nomusu";
		$modelo = new conexion();
		$conexion = $modelo->get_conexion();
		$result = $conexion->prepare($sql);
		$result->execute();
		$res = $result->fetchAll(PDO::FETCH_ASSOC);
		return $res;
	}

	/** Definiciones de campos por dominio (2 condiciones, 3 propuesta, 4 manifiesto). */
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

	/** Todas las propuestas registradas de un candidato. */
	public function getProp(){
		$sql = "SELECT p.idusu, p.texpro, v.idval, v.nomval, v.iddom, v.parval
			FROM propuesta AS p INNER JOIN valor AS v ON p.idval = v.idval
			WHERE p.idusu = :idusu
			ORDER BY v.iddom, v.idval";
		$modelo = new conexion();
		$conexion = $modelo->get_conexion();
		$result = $conexion->prepare($sql);
		$idusu = $this->getIdusu();
		$result->bindParam(":idusu",$idusu);
		$result->execute();
		$res = $result->fetchAll(PDO::FETCH_ASSOC);
		return $res;
	}

	/** Video activo de la propuesta de un candidato. */
	public function getVideo(){
		$sql = "SELECT idvid, nomvid, rutvid FROM video WHERE idusu=:idusu AND actvid=1 ORDER BY idvid DESC LIMIT 1";
		$modelo = new conexion();
		$conexion = $modelo->get_conexion();
		$result = $conexion->prepare($sql);
		$idusu = $this->getIdusu();
		$result->bindParam(":idusu",$idusu);
		$result->execute();
		$res = $result->fetchAll(PDO::FETCH_ASSOC);
		return $res;
	}
}
?>