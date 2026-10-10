<?php
require_once("conexion.php");

class Commdoc{
	//Atributos
	private $idasp;
	private $idusu;
	private $tdousu;
	private $ndocusu;
	private $nomasp;
	private $apeasp;
	private $fecnac;
	private $genusu;
	private $telasp;
	private $emasp;
	private $diresp;
	private $muncod;
	private $codpro;
	private $idfic;
	private $motasp;
	private $dispoasp;
	private $fecreg;
	private $estado;

	//METODOS GET
	function getIdasp(){
		return $this->idasp;
	}
	function getIdusu(){
		return $this->idusu;
	}
	function getTdousu(){
		return $this->tdousu;
	}
	function getNdocusu(){
		return $this->ndocusu;
	}
	function getNomasp(){
		return $this->nomasp;
	}
	function getApeasp(){
		return $this->apeasp;
	}
	function getFecnac(){
		return $this->fecnac;
	}
	function getGenusu(){
		return $this->genusu;
	}
	function getTelasp(){
		return $this->telasp;
	}
	function getEmasp(){
		return $this->emasp;
	}
	function getDiresp(){
		return $this->diresp;
	}
	function getMuncod(){
		return $this->muncod;
	}
	function getCodpro(){
		return $this->codpro;
	}
	function getIdfic(){
		return $this->idfic;
	}
	function getMotasp(){
		return $this->motasp;
	}
	function getDispoasp(){
		return $this->dispoasp;
	}
	function getFecreg(){
		return $this->fecreg;
	}
	function getEstado(){
		return $this->estado;
	}

	//METODOS SET
	function setIdasp($idasp){
		$this->idasp = $idasp;
	}
	function setIdusu($idusu){
		$this->idusu = $idusu;
	}
	function setTdousu($tdousu){
		$this->tdousu = $tdousu;
	}
	function setNdocusu($ndocusu){
		$this->ndocusu = $ndocusu;
	}
	function setNomasp($nomasp){
		$this->nomasp = $nomasp;
	}
	function setApeasp($apeasp){
		$this->apeasp = $apeasp;
	}
	function setFecnac($fecnac){
		$this->fecnac = $fecnac;
	}
	function setGenusu($genusu){
		$this->genusu = $genusu;
	}
	function setTelasp($telasp){
		$this->telasp = $telasp;
	}
	function setEmasp($emasp){
		$this->emasp = $emasp;
	}
	function setDiresp($diresp){
		$this->diresp = $diresp;
	}
	function setMuncod($muncod){
		$this->muncod = $muncod;
	}
	function setCodpro($codpro){
		$this->codpro = $codpro;
	}
	function setIdfic($idfic){
		$this->idfic = $idfic;
	}
	function setMotasp($motasp){
		$this->motasp = $motasp;
	}
	function setDispoasp($dispoasp){
		$this->dispoasp = $dispoasp;
	}
	function setFecreg($fecreg){
		$this->fecreg = $fecreg;
	}
	function setEstado($estado){
		$this->estado = $estado;
	}

	//METODOS

	public function getAll(){
		try{
			$sql = "SELECT a.idasp, a.tdousu, a.ndocusu, a.nomasp, a.apeasp, a.fecnac, a.genusu, a.telasp, a.emasp, a.diresp, a.muncod, a.codpro, a.idfic, a.motasp, a.dispoasp, a.fecreg, a.estado,
					vtd.nomval AS nomtdo, vg.nomval AS nomgen, mu.nomubi AS ncommun, pc.nompro AS nomcomp,
					f.nomfic, f.codpro AS codproest, pp.nompro AS nomproest, pp.verpro AS verproest, c.nomcen AS nomcen, vj.nomval AS nomjornada
				FROM aspirante AS a
				LEFT JOIN valor AS vtd ON a.tdousu = vtd.idval
				LEFT JOIN valor AS vg ON a.genusu = vg.idval
				LEFT JOIN ubica AS mu ON a.muncod = mu.codubi
				LEFT JOIN programa AS pc ON a.codpro = pc.codpro
				LEFT JOIN ficha AS f ON a.idfic = f.idfic
				LEFT JOIN programa AS pp ON f.codpro = pp.codpro
				LEFT JOIN centro AS c ON f.idcen = c.idcen
				LEFT JOIN valor AS vj ON f.jornada = vj.idval
				ORDER BY a.idasp DESC";
			$modelo = new conexion();
			$conexion = $modelo->get_conexion();
			$result = $conexion->prepare($sql);
			$result->execute();
			$res = $result->fetchAll(PDO::FETCH_ASSOC);
			return $res;
		}catch(Exception $e){
			ManejoError($e);
		}
	}

	public function getOne(){
		try{
			$sql = "SELECT a.idasp,
					COALESCE(a.tdousu, u.tdousu) AS tdousu,
					COALESCE(a.ndocusu, u.ndocusu) AS ndocusu,
					COALESCE(a.nomasp, u.nomusu) AS nomasp,
					a.apeasp, a.fecnac,
					COALESCE(a.genusu, u.genusu) AS genusu,
					COALESCE(a.telasp, u.telcan) AS telasp,
					COALESCE(a.emasp, u.emausu) AS emasp,
					a.diresp, a.muncod, a.codpro, a.idfic, a.motasp, a.dispoasp, a.fecreg, a.estado,
					vtd.nomval AS nomtdo, vg.nomval AS nomgen, mu.nomubi AS ncommun, pc.nompro AS nomcomp,
					f.nomfic, f.codpro AS codproest, pp.nompro AS nomproest, pp.verpro AS verproest, c.nomcen AS nomcen, vj.nomval AS nomjornada
				FROM aspirante AS a
				LEFT JOIN usuario AS u ON a.idusu = u.idusu
				LEFT JOIN valor AS vtd ON COALESCE(a.tdousu, u.tdousu) = vtd.idval
				LEFT JOIN valor AS vg ON COALESCE(a.genusu, u.genusu) = vg.idval
				LEFT JOIN ubica AS mu ON a.muncod = mu.codubi
				LEFT JOIN programa AS pc ON a.codpro = pc.codpro
				LEFT JOIN ficha AS f ON a.idfic = f.idfic
				LEFT JOIN programa AS pp ON f.codpro = pp.codpro
				LEFT JOIN centro AS c ON f.idcen = c.idcen
				LEFT JOIN valor AS vj ON f.jornada = vj.idval
				WHERE a.idasp = :idasp";
			$modelo = new conexion();
			$conexion = $modelo->get_conexion();
			$result = $conexion->prepare($sql);
			$idasp = $this->getIdasp();
			$result->bindParam(':idasp',$idasp);
			$result->execute();
			$res = $result->fetchAll(PDO::FETCH_ASSOC);
			return $res;
		}catch(Exception $e){
			ManejoError($e);
			return [];
		}
	}

	public function save(){
		try{
			$sql = "INSERT INTO aspirante (idusu, tdousu, ndocusu, nomasp, apeasp, fecnac, genusu, telasp, emasp, diresp, muncod, codpro, idfic, motasp, dispoasp, fecreg, estado)
				VALUES (:idusu, :tdousu, :ndocusu, :nomasp, :apeasp, :fecnac, :genusu, :telasp, :emasp, :diresp, :muncod, :codpro, :idfic, :motasp, :dispoasp, :fecreg, :estado)";
			$modelo = new conexion();
			$conexion = $modelo->get_conexion();
			$result = $conexion->prepare($sql);
			$result->bindParam(':idusu',$this->idusu);
			$result->bindParam(':tdousu',$this->tdousu);
			$result->bindParam(':ndocusu',$this->ndocusu);
			$result->bindParam(':nomasp',$this->nomasp);
			$result->bindParam(':apeasp',$this->apeasp);
			$result->bindParam(':fecnac',$this->fecnac);
			$result->bindParam(':genusu',$this->genusu);
			$result->bindParam(':telasp',$this->telasp);
			$result->bindParam(':emasp',$this->emasp);
			$result->bindParam(':diresp',$this->diresp);
			$result->bindParam(':muncod',$this->muncod);
			$result->bindParam(':codpro',$this->codpro);
			$result->bindParam(':idfic',$this->idfic);
			$result->bindParam(':motasp',$this->motasp);
			$result->bindParam(':dispoasp',$this->dispoasp);
			$result->bindParam(':fecreg',$this->fecreg);
			$result->bindParam(':estado',$this->estado);
			$result->execute();
		}catch(Exception $e){
			ManejoError($e);
		}
	}

	public function edit(){
		try{
			$sql = "UPDATE aspirante SET idusu=:idusu, tdousu=:tdousu, ndocusu=:ndocusu, nomasp=:nomasp, apeasp=:apeasp, fecnac=:fecnac, genusu=:genusu, telasp=:telasp, emasp=:emasp, diresp=:diresp, muncod=:muncod, codpro=:codpro, idfic=:idfic, motasp=:motasp, dispoasp=:dispoasp, fecreg=:fecreg, estado=:estado
				WHERE idasp=:idasp";
			$modelo = new conexion();
			$conexion = $modelo->get_conexion();
			$result = $conexion->prepare($sql);
			$result->bindParam(':idasp',$this->idasp);
			$result->bindParam(':idusu',$this->idusu);
			$result->bindParam(':tdousu',$this->tdousu);
			$result->bindParam(':ndocusu',$this->ndocusu);
			$result->bindParam(':nomasp',$this->nomasp);
			$result->bindParam(':apeasp',$this->apeasp);
			$result->bindParam(':fecnac',$this->fecnac);
			$result->bindParam(':genusu',$this->genusu);
			$result->bindParam(':telasp',$this->telasp);
			$result->bindParam(':emasp',$this->emasp);
			$result->bindParam(':diresp',$this->diresp);
			$result->bindParam(':muncod',$this->muncod);
			$result->bindParam(':codpro',$this->codpro);
			$result->bindParam(':idfic',$this->idfic);
			$result->bindParam(':motasp',$this->motasp);
			$result->bindParam(':dispoasp',$this->dispoasp);
			$result->bindParam(':fecreg',$this->fecreg);
			$result->bindParam(':estado',$this->estado);
			$result->execute();
		}catch(Exception $e){
			ManejoError($e);
		}
	}

	public function eliminar(){
		try{
			$sql = "DELETE FROM aspirante WHERE idasp=:idasp";
			$modelo = new conexion();
			$conexion = $modelo->get_conexion();
			$result = $conexion->prepare($sql);
			$result->bindParam(':idasp',$this->idasp);
			$result->execute();
		}catch(Exception $e){
			ManejoError($e);
		}
	}

	public function getConteos(){
		try{
			$sql = "SELECT
					(SELECT COUNT(*) FROM aspirante) AS total,
					(SELECT COUNT(*) FROM aspirante WHERE estado = 'Pendiente') AS pendientes,
					(SELECT COUNT(*) FROM aspirante WHERE estado = 'Aprobado') AS aprobadas,
					(SELECT COUNT(*) FROM aspirante WHERE estado = 'Inscrito') AS inscritas,
					(SELECT COUNT(*) FROM aspirante WHERE estado = 'Rechazado') AS rechazadas,
					(SELECT COUNT(*) FROM programa WHERE tippro = 1083) AS comp";
			$modelo = new conexion();
			$conexion = $modelo->get_conexion();
			$result = $conexion->prepare($sql);
			$result->execute();
			$res = $result->fetchAll(PDO::FETCH_ASSOC);
			return $res;
		}catch(Exception $e){
			ManejoError($e);
		}
	}

	public function getUsuCompleto($ndocusu){
		try{
			$sql = "SELECT u.idusu, u.tdousu, u.ndocusu, u.nomusu, u.genusu, u.telcan, u.emausu,
					uf.idfic, f.nomfic, f.codpro AS codproest, p.nompro AS nomproest, f.idcen, c.nomcen,
					f.jornada, j.nomval AS nomjornada, f.mun AS muncod, mu.nomubi AS ncommun
				FROM usuario AS u
				LEFT JOIN usufic AS uf ON uf.idusu = u.idusu AND uf.idfic = (SELECT uf2.idfic FROM usufic AS uf2 WHERE uf2.idusu = u.idusu AND uf2.actfic = 1 ORDER BY uf2.idfic DESC LIMIT 1)
				LEFT JOIN ficha AS f ON uf.idfic = f.idfic
				LEFT JOIN programa AS p ON f.codpro = p.codpro
				LEFT JOIN centro AS c ON f.idcen = c.idcen
				LEFT JOIN valor AS j ON f.jornada = j.idval
				LEFT JOIN ubica AS mu ON f.mun = mu.codubi
				WHERE u.ndocusu = :ndocusu AND u.actusu = 1
				LIMIT 1";
			$modelo = new conexion();
			$conexion = $modelo->get_conexion();
			$result = $conexion->prepare($sql);
			$result->bindParam(':ndocusu',$ndocusu);
			$result->execute();
			$res = $result->fetchAll(PDO::FETCH_ASSOC);
			return $res;
		}catch(Exception $e){
			ManejoError($e);
		}
	}

	public function getVal($iddom){
		try{
			$sql = "SELECT idval, nomval FROM valor WHERE iddom=:iddom ORDER BY nomval";
			$modelo = new conexion();
			$conexion = $modelo->get_conexion();
			$result = $conexion->prepare($sql);
			$result->bindParam(':iddom',$iddom);
			$result->execute();
			$res = $result->fetchAll(PDO::FETCH_ASSOC);
			return $res;
		}catch(Exception $e){
			ManejoError($e);
		}
	}

	public function getAllMunicipios(){
		try{
			$sql = "SELECT codubi, nomubi FROM ubica WHERE depubi <> 0 ORDER BY nomubi";
			$modelo = new conexion();
			$conexion = $modelo->get_conexion();
			$result = $conexion->prepare($sql);
			$result->execute();
			$res = $result->fetchAll(PDO::FETCH_ASSOC);
			return $res;
		}catch(Exception $e){
			ManejoError($e);
		}
	}

	public function getAllFichas(){
		try{
			$sql = "SELECT f.idfic, f.nomfic, f.codpro, p.nompro, f.idcen, c.nomcen, f.jornada, j.nomval AS nomjornada
				FROM ficha AS f
				LEFT JOIN programa AS p ON f.codpro = p.codpro
				LEFT JOIN centro AS c ON f.idcen = c.idcen
				LEFT JOIN valor AS j ON f.jornada = j.idval
				ORDER BY f.idfic DESC";
			$modelo = new conexion();
			$conexion = $modelo->get_conexion();
			$result = $conexion->prepare($sql);
			$result->execute();
			$res = $result->fetchAll(PDO::FETCH_ASSOC);
			return $res;
		}catch(Exception $e){
			ManejoError($e);
		}
	}

	public function getAllComplementarias(){
		try{
			$sql = "SELECT p.codpro, p.nompro, p.horlpro
				FROM programa AS p
				WHERE p.tippro = 1083
				ORDER BY p.nompro";
			$modelo = new conexion();
			$conexion = $modelo->get_conexion();
			$result = $conexion->prepare($sql);
			$result->execute();
			$res = $result->fetchAll(PDO::FETCH_ASSOC);
			return $res;
		}catch(Exception $e){
			ManejoError($e);
		}
	}
}
?>