<?php
class Votmfpro{
	private $npro;
	private $idusu;
	private $texpro;
	private $idval;
    private $nomval;
    private $iddom;
    private $parval;
    private $act;
	
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
	public function getNomval(){
		return $this->nomval;
	}
	public function getIddom(){
		return $this->iddom;
	}
	public function getParval(){
		return $this->parval;
	}
	public function getAct(){
		return $this->act;
	}



// set
	public function setNpro($npro)
	{
		$this->npro = $npro;
	}
	public function setIdusu($idusu)
	{
		$this->idusu = $idusu;
	}
	public function setTexpro($texpro)
	{
		$this->texpro = $texpro;
	}
	public function setIdval($idval)
	{
		$this->idval = $idval;
	}
	public function setNomval($nomval){
		$this->nomval=$nomval;
	}
	public function setIddom($iddom){
		$this->iddom=$iddom;
	}
	public function setParval($parval){
		$this->parval=$parval;
	}
	public function setAct($act){
		$this->act=$act;
	}

	public function selAll(){
		$sql = "SELECT p.npro, p.idusu, p.texpro, p.idval,v.iddom, v.nomval FROM propuesta AS p INNER JOIN valor AS v ON p.idval=v.idval";
		$modelo = new conexion();
		$conexion = $modelo->get_conexion();
		$result = $conexion->prepare($sql);
		$result->execute();
		$res = $result->fetchAll(PDO::FETCH_ASSOC);
		return $res;
	}
	public function selOne(){
		$sql = "SELECT p.npro, p.idusu, p.texpro AS texpro, p.idval, v.iddom, v.nomval 
		FROM propuesta AS p INNER JOIN valor AS v ON p.idval=v.idval WHERE p.idusu=:idusu 
		AND v.iddom=:iddom ORDER BY v.idval";
		$modelo = new conexion();
		$conexion = $modelo->get_conexion();
		$result = $conexion->prepare($sql);
		$idusu = $this->getIdusu();
		$result->bindParam(":idusu",$idusu);
		$iddom = $this->getIddom();
		$result->bindParam(":iddom",$iddom);
		$result->execute();
		$res = $result->fetchAll(PDO::FETCH_ASSOC);
		return $res;	
	}
	public function ins(){
		$sql = "INSERT INTO propuesta(idusu, texpro, idval) VALUES (:idusu,:texpro,:idval)";
		$modelo = new conexion();
		$conexion = $modelo->get_conexion();
		$result = $conexion->prepare($sql);
		$idusu = $this->getIdusu();
		$result->bindParam(":idusu",$idusu);
		$texpro = $this->getTexpro();
		$result->bindParam(":texpro",$texpro);
		$idval = $this->getIdval();
		$result->bindParam(":idval",$idval);
		$result->execute();
	}
	public function upd(){
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
		$modelo = new conexion();
		$conexion = $modelo->get_conexion();
		$sql = "DELETE FROM propuesta WHERE npro=:npro";
		$result = $conexion->prepare($sql);
		$npro = $this->getNpro();
		$result->bindParam(":npro",$npro);
		$result->execute();
	}

	public function getUsu(){
		$sql = "SELECT u.idusu, u.ndocusu, u.nomusu, u.idper, p.nomper, uf.idfic, f.nomfic, v.nomval, u.pasusu, u.emausu, u.idcen, c.nomcen, u.actusu, u.fotcan, u.telcan FROM usuario AS u INNER JOIN perfil AS p ON u.idper=p.idper INNER JOIN centro AS c ON u.idcen=c.idcen LEFT JOIN usufic AS uf ON u.idusu=uf.idusu LEFT JOIN ficha AS f ON uf.idfic=f.idfic LEFT JOIN valor AS v ON f.jornada=v.idval WHERE u.idusu=:idusu";
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
	function elic($tit,$vec,$idusu,$iddom){ 
		$mfpro = new mfpro();
		$mfpro->setIdusu($idusu);
		$mfpro->setIddom($iddom);
		$datOne = $mfpro->selOne();
		$html = '<tr> <td style="text-align: center;border: 1px solid #000;" colspan="3">';
			$html .= ' <strong>'.$tit.'</strong>';
		$html .= '</td></tr> ';
		if($vec){
			$i=0;
			foreach($vec AS $dv){
				$html .= '<tr><td style="border: 1px solid #000;" colspan="2">';
					$html .= $dv['nomval'];
				$html .= '</td>';
				$html .= '<td style="border: 1px solid #000;">';
						if($datOne AND $datOne[$i]['texpro']=="No"){$html .= "No";}
						else if($datOne AND $datOne[$i]['texpro']=="Si"){$html .= "Si";}
				$html .= '</td></tr>';
				$i++;
			}
		} 
		return $html;
	}
}
if (!class_exists('Mfpro')) {
    class_alias('Votmfpro', 'Mfpro');
}
?>