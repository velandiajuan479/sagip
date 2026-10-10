<?php
require_once(__DIR__ . '/conexion.php');

class Musu{

	private $idusu;
	private $ndocusu;
	private $nomusu;
	private $idper;
	private $pasusu;
	private $emausu;
	private $idcen;
	private $actusu;
	private $fotcan;
	private $telcan;
	private $noca;
	private $idfic;
	private $finc;
	private $idval;
	private $inic;
	private $rhusu;
	private $tdousu;
	private $genusu;

	// METODOS GET-------------------------

	public function getIdusu(){
		return $this->idusu;
	}
	public function getNdocusu(){
		return $this->ndocusu;
	}
	public function getNomusu(){
		return $this->nomusu;
	}
	public function getIdper(){
		return $this->idper;
	}
	public function getPasusu(){
		return $this->pasusu;
	}
	public function getEmausu(){
		return $this->emausu;
	}
	public function getIdcen(){
		return $this->idcen;
	}
	public function getActusu(){
		return $this->actusu;
	}
	public function getFotcan(){
		return $this->fotcan;
	}
	public function getTelcan(){
		return $this->telcan;
	}
	public function getNoca(){
		return $this->noca;
	}
	public function getIdfic(){
		return $this->idfic;
	}
	public function getFinc(){
		return $this->finc;
	}
	public function getInic(){
		return $this->inic;
	}
	public function getRhusu(){
		return $this->rhusu;
	}
	public function getTdousu(){
		return $this->tdousu;
	}
	public function getGenusu(){
		return $this->genusu;
	}

	// Metodos set-----------------------

	public function setIdusu($idusu){
		$this->idusu=$idusu;
	}
	public function setNdocusu($ndocusu){
		$this->ndocusu=$ndocusu;
	}
	public function setNomusu($nomusu){
		$this->nomusu=$nomusu;
	}
	public function setIdper($idper){
		$this->idper=$idper;
	}
	public function setPasusu($pasusu){
		$this->pasusu=$pasusu;
	}
	public function setEmausu($emausu){
		$this->emausu=$emausu;
	}
	public function setIdcen($idcen){
		$this->idcen=$idcen;
	}
	public function setActusu($actusu){
		$this->actusu=$actusu;
	}
	public function setFotcan($fotcan){
		$this->fotcan=$fotcan;
	}
	public function setTelcan($telcan){
		$this->telcan=$telcan;
	}
	public function setIdfic($idfic){
		$this->idfic=$idfic;
	}
	public function setFinc($finc){
		$this->finc=$finc;
	}
	public function setInic($inic){
		$this->inic=$inic;
	}
	public function setNoca($noca){
		$this->noca=$noca;
	}
	public function setRhusu($rhusu){
		$this->rhusu=$rhusu;
	}
	public function setTdousu($tdousu){
		$this->tdousu=$tdousu;
	}
	public function setGenusu($genusu){
		$this->genusu = $genusu;
	}

	public function getDashboardStats($idcen = null){
		$modelo = new conexion();
		$conexion = $modelo->get_conexion();
		
		$sqlTot = "SELECT COUNT(*) as total FROM usuario" . ($idcen ? " WHERE idcen = :idcen" : "");
		$resTot = $conexion->prepare($sqlTot);
		if($idcen) $resTot->bindParam(":idcen", $idcen);
		$resTot->execute();
		$rowTot = $resTot->fetch(PDO::FETCH_ASSOC);
		$totalUsuarios = (int)($rowTot['total'] ?? 0);

		$sqlAct = "SELECT COUNT(*) as activos FROM usuario WHERE actusu = 1" . ($idcen ? " AND idcen = :idcen" : "");
		$resAct = $conexion->prepare($sqlAct);
		if($idcen) $resAct->bindParam(":idcen", $idcen);
		$resAct->execute();
		$rowAct = $resAct->fetch(PDO::FETCH_ASSOC);
		$usuariosActivos = (int)($rowAct['activos'] ?? 0);

		$sqlIna = "SELECT COUNT(*) as inactivos FROM usuario WHERE actusu != 1" . ($idcen ? " AND idcen = :idcen" : "");
		$resIna = $conexion->prepare($sqlIna);
		if($idcen) $resIna->bindParam(":idcen", $idcen);
		$resIna->execute();
		$rowIna = $resIna->fetch(PDO::FETCH_ASSOC);
		$usuariosInactivos = (int)($rowIna['inactivos'] ?? 0);

		$sqlFic = "SELECT COUNT(DISTINCT uf.idusu) as con_ficha FROM usufic uf INNER JOIN usuario u ON uf.idusu = u.idusu WHERE uf.actfic = 1" . ($idcen ? " AND u.idcen = :idcen" : "");
		$resFic = $conexion->prepare($sqlFic);
		if($idcen) $resFic->bindParam(":idcen", $idcen);
		$resFic->execute();
		$rowFic = $resFic->fetch(PDO::FETCH_ASSOC);
		$usuariosConFicha = (int)($rowFic['con_ficha'] ?? 0);

		return [
			'total' => $totalUsuarios,
			'activos' => $usuariosActivos,
			'inactivos' => $usuariosInactivos,
			'con_ficha' => $usuariosConFicha,
			'porc_activos' => $totalUsuarios > 0 ? round(($usuariosActivos / $totalUsuarios) * 100, 1) : 0
		];
	}

	public function countUsuariosFicha($idfic){
		if (empty($idfic) || $idfic === '0') return 0;
		$idfic = trim($idfic);
		$ficNum = null;
		if (preg_match('/^(\d+)/', $idfic, $matches)) {
			$ficNum = $matches[1];
		}

		$modelo = new conexion();
		$conexion = $modelo->get_conexion();

		if ($ficNum) {
			$sql = "SELECT COUNT(DISTINCT uf.idusu) as total FROM usufic uf INNER JOIN ficha f ON uf.idfic = f.idfic WHERE uf.idfic = :ficNum OR uf.idfic LIKE :ficLike OR f.nomfic LIKE :ficNom";
			$stmt = $conexion->prepare($sql);
			$stmt->bindValue(':ficNum', $ficNum);
			$stmt->bindValue(':ficLike', "%$ficNum%");
			$stmt->bindValue(':ficNom', "%$idfic%");
		} else {
			$sql = "SELECT COUNT(DISTINCT uf.idusu) as total FROM usufic uf INNER JOIN ficha f ON uf.idfic = f.idfic WHERE f.nomfic LIKE :ficNom OR uf.idfic LIKE :ficLike";
			$stmt = $conexion->prepare($sql);
			$stmt->bindValue(':ficNom', "%$idfic%");
			$stmt->bindValue(':ficLike', "%$idfic%");
		}
		$stmt->execute();
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		return (int)($row['total'] ?? 0);
	}

	public function getFichaInfo($idfic){
		if (empty($idfic) || $idfic === '0') return null;
		$idfic = trim($idfic);
		$ficNum = null;
		if (preg_match('/^(\d+)/', $idfic, $matches)) {
			$ficNum = $matches[1];
		}

		$modelo = new conexion();
		$conexion = $modelo->get_conexion();

		if ($ficNum) {
			$sql = "SELECT f.idfic, f.nomfic, v.nomval as jornada FROM ficha f LEFT JOIN valor v ON f.jornada = v.idval WHERE f.idfic = :ficNum OR f.idfic LIKE :ficLike OR f.nomfic LIKE :ficNom LIMIT 1";
			$stmt = $conexion->prepare($sql);
			$stmt->bindValue(':ficNum', $ficNum);
			$stmt->bindValue(':ficLike', "%$ficNum%");
			$stmt->bindValue(':ficNom', "%$idfic%");
		} else {
			$sql = "SELECT f.idfic, f.nomfic, v.nomval as jornada FROM ficha f LEFT JOIN valor v ON f.jornada = v.idval WHERE f.nomfic LIKE :ficNom OR f.idfic LIKE :ficLike LIMIT 1";
			$stmt = $conexion->prepare($sql);
			$stmt->bindValue(':ficNom', "%$idfic%");
			$stmt->bindValue(':ficLike', "%$idfic%");
		}
		$stmt->execute();
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		return $row ?: ['idfic' => $idfic, 'nomfic' => $idfic];
	}

	public function getAllSearch($criterio = null, $idfic = null){
		$criterio = trim($criterio ?? '');
		$idfic = trim($idfic ?? '');

		if (empty($criterio) && (empty($idfic) || $idfic == '0')) {
			return [];
		}

		$sql = "SELECT u.idusu, u.ndocusu, u.nomusu, u.idper, p.nomper, u.pasusu, u.emausu, u.idcen, c.nomcen, u.actusu, u.fotcan, u.telcan, u.noca, u.rhusu, r.nomval AS rh, u.tdousu, t.nomval AS tdoc, u.genusu, g.nomval AS gen, GROUP_CONCAT(DISTINCT CONCAT(f.idfic, ' - ', f.nomfic) ORDER BY f.idfic SEPARATOR '||') AS fichas_info, GROUP_CONCAT(DISTINCT f.idfic SEPARATOR ',') AS ids_fichas 
		FROM usuario AS u 
		INNER JOIN perfil AS p ON u.idper=p.idper 
		INNER JOIN centro AS c ON u.idcen=c.idcen 
		LEFT JOIN usufic AS uf ON u.idusu=uf.idusu 
		LEFT JOIN ficha AS f ON uf.idfic=f.idfic 
		LEFT JOIN valor AS r ON u.rhusu=r.idval 
		LEFT JOIN valor AS t ON u.tdousu=t.idval 
		LEFT JOIN valor AS g ON u.genusu=g.idval";

		$where = [];
		$params = [];

		if (!empty($idfic) && $idfic !== '0') {
			$ficNum = null;
			if (preg_match('/^(\d+)/', $idfic, $matches)) {
				$ficNum = $matches[1];
			}

			if ($ficNum) {
				$where[] = "u.idusu IN (SELECT DISTINCT sub_uf.idusu FROM usufic sub_uf INNER JOIN ficha sub_f ON sub_uf.idfic = sub_f.idfic WHERE sub_uf.idfic = :filtroFicNum OR sub_uf.idfic LIKE :filtroFicLike OR sub_f.nomfic LIKE :filtroFicNom)";
				$params[':filtroFicNum'] = $ficNum;
				$params[':filtroFicLike'] = "%" . $ficNum . "%";
				$params[':filtroFicNom'] = "%" . $idfic . "%";
			} else {
				$where[] = "u.idusu IN (SELECT DISTINCT sub_uf.idusu FROM usufic sub_uf INNER JOIN ficha sub_f ON sub_uf.idfic = sub_f.idfic WHERE sub_f.nomfic LIKE :filtroFicNom OR sub_uf.idfic LIKE :filtroFicLike)";
				$params[':filtroFicNom'] = "%" . $idfic . "%";
				$params[':filtroFicLike'] = "%" . $idfic . "%";
			}
		}

		if (!empty($criterio)) {
			$terms = preg_split('/\s+/', $criterio);
			$termConditions = [];
			$i = 0;
			$tieneFichaActiva = (!empty($idfic) && $idfic !== '0');
			foreach ($terms as $term) {
				$term = trim($term);
				if ($term !== '') {
					$paramName = ":term" . $i;
					if ($tieneFichaActiva) {
						// Si ya está filtrada la ficha, buscar exclusivamente en datos del usuario
						$termConditions[] = "(u.nomusu LIKE $paramName OR u.emausu LIKE $paramName OR u.ndocusu LIKE $paramName OR p.nomper LIKE $paramName)";
					} else {
						// Búsqueda general: también considerar si el término coincide con número de ficha
						$termConditions[] = "(u.nomusu LIKE $paramName OR u.emausu LIKE $paramName OR u.ndocusu LIKE $paramName OR p.nomper LIKE $paramName OR u.idusu IN (SELECT DISTINCT sub_f.idusu FROM usufic sub_f WHERE sub_f.idfic LIKE $paramName))";
					}
					$params[$paramName] = "%" . $term . "%";
					$i++;
				}
			}
			if (!empty($termConditions)) {
				$where[] = "(" . implode(" AND ", $termConditions) . ")";
			}
		}

		if (!empty($where)) {
			$sql .= " WHERE " . implode(" AND ", $where);
		}

		$sql .= " GROUP BY u.idusu ORDER BY u.nomusu ASC";

		$modelo = new conexion();
		$conexion = $modelo->get_conexion();
		$result = $conexion->prepare($sql);
		foreach ($params as $key => $val) {
			$result->bindValue($key, $val);
		}
		$result->execute();
		return $result->fetchAll(PDO::FETCH_ASSOC);
	}

	public function getAll($idfic = null){
		if (empty($idfic)) {
			return [];
		}
		return $this->getAllSearch($idfic, $idfic);
	}
	public function getTficha(){
		$sql="SELECT idusu, COUNT(*) AS Total FROM usufic  WHERE idusu=:idusu GROUP BY idusu";
		$modelo = new conexion();
		$conexion = $modelo->get_conexion();
		$result = $conexion->prepare($sql);
		$idusu=$this->getIdusu();
		$result->bindParam(":idusu",$idusu);
		$result->execute();
		$res=$result->fetchAll(PDO::FETCH_ASSOC);
		return $res;
	}
	public function getNomFics(){
		$sql = "SELECT u.idusu,u.idfic,f.nomfic FROM usufic as u INNER JOIN ficha as f ON u.idfic=f.idfic where u.idusu=:idusu ";
		$modelo = new conexion();
		$conexion = $modelo->get_conexion();
		$result = $conexion->prepare($sql);
		$idusu=$this->getIdusu();
		$result->bindParam(":idusu",$idusu);
		$result->execute();
		$res=$result->fetchAll(PDO::FETCH_ASSOC);
		return $res;
	}
	// public function selApren(){
	// 	$resultado = null;
	// 	$modelo = new conexion();
	// 	$conexion = $modelo->get_conexion();
	// 	$sql = "SELECT u.idusu, u.ndocusu, u.nomusu, u.idper FROM usuario AS u WHERE idper IN (3,4) AND noca<>999";
	// 	$result = $conexion->prepare($sql);
	// 	$result->execute();
	// 	while($f=$result->fetch()){
	// 		$resultado[]=$f;
	// 	}
	// 	return $resultado;
	// }
	public function getOne(){
		$sql = "SELECT u.idusu, u.ndocusu, u.nomusu, u.idper, p.nomper, uf.idfic, f.nomfic, v.nomval AS nomval, u.pasusu, u.emausu, u.idcen, c.nomcen, u.actusu, u.fotcan, u.telcan, u.noca, u.rhusu, r.nomval AS rh, u.tdousu, t.nomval AS tdoc, u.genusu, g.nomval AS gen FROM usuario AS u INNER JOIN perfil AS p ON u.idper=p.idper INNER JOIN centro AS c ON u.idcen=c.idcen LEFT JOIN usufic AS uf ON u.idusu=uf.idusu LEFT JOIN ficha AS f ON uf.idfic=f.idfic LEFT JOIN valor AS v ON f.jornada=v.idval LEFT JOIN valor AS r ON u.rhusu=r.idval LEFT JOIN valor AS t ON u.tdousu=t.idval LEFT JOIN valor AS g ON u.genusu=g.idval WHERE u.idusu=:idusu";
		$modelo = new conexion();
		$conexion = $modelo->get_conexion();
		$result = $conexion->prepare($sql);
		$idusu=$this->getIdusu();
		$result->bindParam(":idusu",$idusu);
		$result->execute();
		$res=$result->fetchAll(PDO::FETCH_ASSOC);
		return $res;
	}
//ins($ndocusu, $nomusu, $idper, $idfic, $pasusu, $idcen, $actusu);
	public function save($dt=1){
		try{
			$sql = "INSERT INTO usuario(idper, ndocusu, nomusu, pasusu, idcen, actusu, emausu, telcan, noca, fotcan, rhusu, tdousu, genusu) VALUES (:idper, :ndocusu, :nomusu, :pasusu, :idcen, :actusu, :emausu, :telcan, :noca, :fotcan, :rhusu, :tdousu, :genusu)";
			$modelo = new conexion();
			$conexion = $modelo->get_conexion();
			$result = $conexion->prepare($sql);

			$idper=$this->getIdper();
			$result->bindParam(":idper",$idper);

			$ndocusu=$this->getNdocusu();
			$result->bindParam(":ndocusu",$ndocusu);

			$nomusu=$this->getNomusu();
			$result->bindParam(":nomusu",$nomusu);

			$pasusu=$this->getPasusu();
			$pasusu = sha1(md5($pasusu));
			$result->bindParam(":pasusu",$pasusu);

			$idcen=$this->getIdcen();
			$result->bindParam(":idcen",$idcen);

			$actusu=$this->getActusu();
			$result->bindParam(":actusu",$actusu);

			$emausu=$this->getEmausu();
			$result->bindParam(":emausu",$emausu);

			$telcan=$this->getTelcan();
			$result->bindParam(":telcan",$telcan);

			$noca=$this->getNoca();
			$result->bindParam(":noca",$noca);
			
            $rhusu = $this->getRhusu() ?: 2007;
			$result->bindParam(":rhusu",$rhusu);
			$tdousu = $this->getTdousu() ?: 1801;
			$result->bindParam(":tdousu",$tdousu);
			$genusu = $this->getGenusu() ?: 2009;
			$result->bindParam(":genusu",$genusu);
            
			$fotcan = $this->getFotcan() ?: 'img/user.jpg';
			$result->bindParam(":fotcan",$fotcan);
			$result->execute();

			$newId = (int)$conexion->lastInsertId();
			if ($newId <= 0) {
				$stmtCheck = $conexion->prepare("SELECT idusu FROM usuario WHERE ndocusu = :ndocusu ORDER BY idusu DESC LIMIT 1");
				$stmtCheck->bindParam(':ndocusu', $ndocusu);
				$stmtCheck->execute();
				$rowChk = $stmtCheck->fetch(PDO::FETCH_ASSOC);
				$newId = (int)($rowChk['idusu'] ?? 0);
			}

			if ($newId > 0) {
				$this->setIdusu($newId);
			}
			return $newId;
		}catch(Exception $e){
			if($dt==1) ManejoError($e);
			echo $e->getMessage();
			return 0;
		}
	}

	// public function selOneIdusu($ndocusu, $nomusu, $idper, $idcen, $actusu, $emausu, $telcan, $noca){
	// 	$resultado = null;
	// 	$modelo = new conexion();
	// 	$conexion = $modelo->get_conexion();
	// 	$sql = "SELECT idusu FROM usuario WHERE ndocusu=:ndocusu AND nomusu=:nomusu AND idper=:idper AND idcen=:idcen AND actusu=:actusu AND emausu=:emausu AND telcan=:telcan AND noca=:noca";
	// 	$result = $conexion->prepare($sql);
	// 	$result->bindParam(":ndocusu",$ndocusu);
	// 	$result->bindParam(":nomusu",$nomusu);
	// 	$result->bindParam(":idper",$idper);
	// 	$result->bindParam(":idcen",$idcen);
	// 	$result->bindParam(":actusu",$actusu);
	// 	$result->bindParam(":emausu",$emausu);
	// 	$result->bindParam(":telcan",$telcan);
	// 	$result->bindParam(":noca",$noca);
	// 	$result->execute();
	// 	while($f=$result->fetch()){
	// 		$resultado[]=$f;
	// 	}
	// 	return $resultado;
	// }
	public function edi(){
		try{
		$pasusu=$this->getPasusu();
		$fotcan=$this->getFotcan();
		$sql = "UPDATE usuario SET ndocusu=:ndocusu,nomusu=:nomusu,idper=:idper,";
		if($pasusu) $sql .= "pasusu=:pasusu,";
		$sql .= "idcen=:idcen,actusu=:actusu, emausu=:emausu, telcan=:telcan, noca=:noca";
		if($fotcan && $fotcan!="fcan/")
		    $sql .= ", fotcan=:fotcan";
		$sql .= ", rhusu=:rhusu, tdousu=:tdousu, genusu=:genusu WHERE idusu=:idusu";
		$modelo = new conexion();
			$conexion = $modelo->get_conexion();
			$result = $conexion->prepare($sql);

			$idusu=$this->getIdusu();
			$result->bindParam(":idusu",$idusu);

			$ndocusu=$this->getNdocusu();
			$result->bindParam(":ndocusu",$ndocusu);

			$nomusu=$this->getNomusu();
			$result->bindParam(":nomusu",$nomusu);

			$idper=$this->getIdper();
			$result->bindParam(":idper",$idper);
			if($pasusu){
				$pasusu = sha1(md5($pasusu));
				$result->bindParam(":pasusu",$pasusu);
			}
			$idcen=$this->getIdcen();
			$result->bindParam(":idcen",$idcen);

			$actusu=$this->getActusu();
			$result->bindParam(":actusu",$actusu);

			$emausu=$this->getEmausu();
			$result->bindParam(":emausu",$emausu);

			$telcan=$this->getTelcan();
			$result->bindParam(":telcan",$telcan);

			$noca=$this->getNoca();
			$result->bindParam(":noca",$noca);

            if($fotcan && $fotcan!="fcan/")
    			$result->bindParam(":fotcan",$fotcan);
			
			$rhusu = $this->getRhusu() ?: 2007;
			$result->bindParam(":rhusu",$rhusu);
			$tdousu = $this->getTdousu() ?: 1801;
			$result->bindParam(":tdousu",$tdousu);
			$genusu = $this->getGenusu() ?: 2009;
			$result->bindParam(":genusu",$genusu);
			
			$result->execute();
		}catch(Exception $e){
            if(strpos($e->getMessage(), '1062'))
                echo '';//'<script>alert("Ficha existente");</script>';
        }
	}

	public function updPasc($ips,$fps){
		return $this->updPascParam($ips, $fps);
	}

	public function updPascParam($ips, $fps){
		$modelo = new conexion();
		$conexion = $modelo->get_conexion();
		$sql = "UPDATE usuario SET pasusu = SHA1(MD5(CONCAT(:ips, ndocusu, :fps))) WHERE idper NOT IN (1)";
		$stmt = $conexion->prepare($sql);
		$stmt->bindParam(":ips", $ips);
		$stmt->bindParam(":fps", $fps);
		$stmt->execute();
		return $stmt->rowCount();
	}

	public function updPascm($ips,$ndoc,$fps){
		$modelo = new conexion();
		$conexion = $modelo->get_conexion();
		$sql = "UPDATE usuario SET pasusu = SHA1(MD5(CONCAT(:ips, :ndoc, :fps))) WHERE idper NOT IN (1)";
		$result = $conexion->prepare($sql);
		$result->bindParam(":ips", $ips);
		$result->bindParam(":ndoc", $ndoc);
		$result->bindParam(":fps", $fps);
		$result->execute();
		return $result->rowCount();
	}

	public function toggleActivo($idusu){
		$modelo = new conexion();
		$conexion = $modelo->get_conexion();
		$sql = "UPDATE usuario SET actusu = IF(actusu = 1, 2, 1) WHERE idusu = :idusu";
		$stmt = $conexion->prepare($sql);
		$stmt->bindParam(":idusu", $idusu);
		$stmt->execute();

		$sqlStatus = "SELECT actusu FROM usuario WHERE idusu = :idusu";
		$stmtStatus = $conexion->prepare($sqlStatus);
		$stmtStatus->bindParam(":idusu", $idusu);
		$stmtStatus->execute();
		$row = $stmtStatus->fetch(PDO::FETCH_ASSOC);
		return (int)($row['actusu'] ?? 2);
	}

	public function updateEstado($idusu, $actusu){
		$modelo = new conexion();
		$conexion = $modelo->get_conexion();
		$sql = "UPDATE usuario SET actusu = :actusu WHERE idusu = :idusu";
		$stmt = $conexion->prepare($sql);
		$stmt->bindParam(":actusu", $actusu, PDO::PARAM_INT);
		$stmt->bindParam(":idusu", $idusu, PDO::PARAM_INT);
		$stmt->execute();
		return (int)$actusu;
	}

	public function del(){
		$idusu = $this->getIdusu();
		return $this->delSafe($idusu);
	}

	public function delSafe($idusu){
		$modelo = new conexion();
		$conexion = $modelo->get_conexion();
		try {
			// Borrar asignaciones secundarias en usufic y usupef
			$sqlUf = "DELETE FROM usufic WHERE idusu = :idusu";
			$stmtUf = $conexion->prepare($sqlUf);
			$stmtUf->bindParam(":idusu", $idusu);
			$stmtUf->execute();

			$sqlUp = "DELETE FROM usupef WHERE idusu = :idusu";
			$stmtUp = $conexion->prepare($sqlUp);
			$stmtUp->bindParam(":idusu", $idusu);
			$stmtUp->execute();

			$sql = "DELETE FROM usuario WHERE idusu = :idusu";
			$stmt = $conexion->prepare($sql);
			$stmt->bindParam(":idusu", $idusu);
			$stmt->execute();
			return ['success' => true];
		} catch(PDOException $e) {
			if ($e->getCode() == '23000' || strpos($e->getMessage(), '1451') !== false) {
				return [
					'success' => false,
					'error' => 'fk',
					'msg' => 'No se puede eliminar el usuario porque tiene registros asociados (asistencias, bitácoras, etc.). Se sugiere desactivarlo con el botón de estado.'
				];
			}
			return ['success' => false, 'error' => 'db', 'msg' => $e->getMessage()];
		}
	}

	public function getCentro(){
		$sql = "SELECT idcen, nomcen FROM centro";
		$modelo = new conexion();
		$conexion = $modelo->get_conexion();
		$result = $conexion->prepare($sql);
		$result->execute();
		$res=$result->fetchAll(PDO::FETCH_ASSOC);
		return $res;
	}

	public function getPerfil(){
		$sql = "SELECT idper, nomper FROM perfil";
		$modelo = new conexion();
		$conexion = $modelo->get_conexion();
		$result = $conexion->prepare($sql);
		$result->execute();
		$res=$result->fetchAll(PDO::FETCH_ASSOC);
		return $res;
	}

	public function getFicha(){
		$sql = "SELECT f.idfic, f.nomfic, v.nomval FROM ficha AS f INNER JOIN valor AS v ON f.jornada=v.idval WHERE f.idfic>1000 ORDER BY f.idfic, f.nomfic";
		$modelo = new conexion();
		$conexion = $modelo->get_conexion();
		$result = $conexion->prepare($sql);
		$result->execute();
		$res=$result->fetchAll(PDO::FETCH_ASSOC);
		return $res;
	}

	public function existeFicha($idfic){
		if(empty($idfic)) return false;
		$modelo = new conexion();
		$conexion = $modelo->get_conexion();
		$sql = "SELECT idfic FROM ficha WHERE idfic = :idfic";
		$stmt = $conexion->prepare($sql);
		$stmt->bindParam(":idfic", $idfic);
		$stmt->execute();
		return (bool)$stmt->fetch(PDO::FETCH_ASSOC);
	}

	public function getMod(){
		$sql = "SELECT idmod, nommod, idper FROM modulo";
		$modelo = new conexion();
		$conexion = $modelo->get_conexion();
		$result = $conexion->prepare($sql);
		$result->execute();
		$res=$result->fetchAll(PDO::FETCH_ASSOC);
		return $res;
	}

	public function getUsPe($idmod, $idusu){
		$sql = "SELECT s.idper FROM usupef AS s INNER JOIN perfil AS p ON s.idper=p.idper WHERE p.idmod=:idmod AND s.idusu=:idusu;";
		$modelo = new conexion();
		$conexion = $modelo->get_conexion();
		$result = $conexion->prepare($sql);
		$result->bindParam(":idmod",$idmod);
		$result->bindParam(":idusu",$idusu);
		$result->execute();
		$res=$result->fetchAll(PDO::FETCH_ASSOC);
		return $res;
	}

	
	public function getPefus($idmod){
		$sql = "SELECT idper, nomper FROM perfil WHERE idmod=:idmod";
		$modelo = new conexion();
		$conexion = $modelo->get_conexion();
		$result = $conexion->prepare($sql);
		$result->bindParam(":idmod",$idmod);
		$result->execute();
		$res=$result->fetchAll(PDO::FETCH_ASSOC);
		return $res;
	}

	public function insUxP(){
		$sql = "INSERT INTO usupef(idusu, idper) VALUES (:idusu, :idper)";
		$modelo = new conexion();
		$conexion = $modelo->get_conexion();
		$result = $conexion->prepare($sql);
		$idusu=$this->getIdusu();
		$result->bindParam(":idusu",$idusu);
		$idper=$this->getIdper();
		$result->bindParam(":idper",$idper);
		$result->execute();
	}

	public function delUxP(){
		$sql = "DELETE FROM usupef WHERE idusu=:idusu";
		$modelo = new conexion();
		$conexion = $modelo->get_conexion();
		$result = $conexion->prepare($sql);
		$idusu=$this->getIdusu();
		$result->bindParam(":idusu",$idusu);
		$result->execute();
	}

	public function insUxF($idusu, $idfic){
		$sql = "INSERT INTO usufic(idusu, idfic, actfic) VALUES (:idusu, :idfic, 1)";
		$modelo = new conexion();
		$conexion = $modelo->get_conexion();
		$result = $conexion->prepare($sql);
		$result->bindParam(":idusu",$idusu);
		$result->bindParam(":idfic",$idfic);
		$result->execute();
		$res=$result->fetchAll(PDO::FETCH_ASSOC);
		return $res;
	}
	public function ediUxF(){
		$sql = "UPDATE usufic SET actfic=2 WHERE idusu=:idusu";
		$modelo = new conexion();
		$conexion = $modelo->get_conexion();
		$result = $conexion->prepare($sql);
		$idusu=$this->getIdusu();
		$result->bindParam(":idusu",$idusu);
		$result->execute();
	}

	public function delUxPF($idusu,$idfic){
		$modelo = new conexion();
		$conexion = $modelo->get_conexion();
		$sql = "DELETE FROM usufic WHERE idusu=:idusu AND idfic=:idfic";
		$result = $conexion->prepare($sql);
		$result->bindParam(":idusu",$idusu);
		$result->bindParam(":idfic",$idfic);
		$result->execute();
	}
	
	public function getFicUsu($idusu){
		$sql = "SELECT f.idfic, f.nomfic, f.jornada, f.idcen, f.mun, u.idusu, u.actfic FROM ficha AS f INNER JOIN usufic AS u ON f.idfic=u.idfic WHERE u.idusu=:idusu";
		$modelo = new conexion();
		$conexion = $modelo->get_conexion();
		$result = $conexion->prepare($sql);
		$result->bindParam(":idusu",$idusu);
		$result->execute();
		$res=$result->fetchAll(PDO::FETCH_ASSOC);
		return $res;
	}

	public function getFicUsuSec($idusu,$idfic){
		$sql = "SELECT f.idfic FROM ficha AS f INNER JOIN usufic AS u ON f.idfic=u.idfic WHERE u.idusu=:idusu AND u.idfic=:idfic";
		$modelo = new conexion();
		$conexion = $modelo->get_conexion();
		$result = $conexion->prepare($sql);
		$result->bindParam(":idusu",$idusu);
		$result->bindParam(":idfic",$idfic);
		$result->execute();
		$res=$result->fetchAll(PDO::FETCH_ASSOC);
		return $res;
	}
	public function selectUsu(){
		$sql = "SELECT idusu, 1 as sum FROM usuario WHERE ndocusu=:ndocusu ORDER BY idusu DESC LIMIT 1";
		$modelo = new conexion();
		$conexion = $modelo->get_conexion();
		$result = $conexion->prepare($sql);
		$ndocusu=$this->getNdocusu();
		$result->bindParam(":ndocusu",$ndocusu);
		$result->execute();
		$res=$result->fetchAll(PDO::FETCH_ASSOC);
		return $res;
	}

	/**
	 * Asignar perfiles por defecto por módulo según el perfil principal
	 */
	public function asignarPerfilesPredeterminados($idusu, $idper = 4) {
		$idusu = (int)$idusu;
		$idper = (int)$idper;
		if ($idusu <= 0) return false;

		$modelo = new conexion();
		$conexion = $modelo->get_conexion();

		// Determinar perfiles secundarios por módulo
		// Si es Aprendiz (4): Módulo 1 (4 - Aprendiz), Módulo 4 (5 - Datos Básicos), Módulo 2 (8 - Aprendiz E/S)
		if ($idper == 4) {
			$perfiles = [4, 5, 8];
		} elseif ($idper == 7) {
			$perfiles = [7, 5];
		} else {
			$perfiles = [$idper, 5];
		}

		$stmtDel = $conexion->prepare("DELETE FROM usupef WHERE idusu = :idusu");
		$stmtDel->bindParam(':idusu', $idusu, PDO::PARAM_INT);
		$stmtDel->execute();

		$stmtIns = $conexion->prepare("INSERT INTO usupef (idusu, idper) VALUES (:idusu, :idper)");
		foreach ($perfiles as $pId) {
			$stmtIns->bindValue(':idusu', $idusu, PDO::PARAM_INT);
			$stmtIns->bindValue(':idper', (int)$pId, PDO::PARAM_INT);
			$stmtIns->execute();
		}
		return true;
	}

	/**
	 * Alternar o actualizar estado de una ficha asociada a un usuario (Principal: 1 / Secundaria: 2)
	 */
	public function actualizarEstadoFichaUsuario($idusu, $idfic, $actfic = 1) {
		$idusu = (int)$idusu;
		if ($idusu <= 0 || empty($idfic)) return false;

		$modelo = new conexion();
		$conexion = $modelo->get_conexion();

		if ((int)$actfic === 1) {
			$stmtClr = $conexion->prepare("UPDATE usufic SET actfic = 2 WHERE idusu = :idusu");
			$stmtClr->bindParam(':idusu', $idusu, PDO::PARAM_INT);
			$stmtClr->execute();
		}

		$stmt = $conexion->prepare("UPDATE usufic SET actfic = :actfic WHERE idusu = :idusu AND idfic = :idfic");
		$stmt->bindParam(':actfic', $actfic, PDO::PARAM_INT);
		$stmt->bindParam(':idusu', $idusu, PDO::PARAM_INT);
		$stmt->bindParam(':idfic', $idfic);
		return $stmt->execute();
	}
	public function getUsu($ndocusu){
		$sql = "SELECT idusu, nomusu, ndocusu FROM usuario WHERE ndocusu=:ndocusu";
		$modelo = new conexion();
		$conexion = $modelo->get_conexion();
		$result = $conexion->prepare($sql);
		$result->bindParam(":ndocusu",$ndocusu);
		$result->execute();
		$res=$result->fetchAll(PDO::FETCH_ASSOC);
		return $res;
	}
	
	public function getVal($iddom){
		$sql = "SELECT * FROM valor WHERE iddom=:iddom ORDER BY nomval";
		$modelo = new conexion();
		$conexion = $modelo->get_conexion();
		$result = $conexion->prepare($sql);
		$result->bindParam(":iddom",$iddom);
		$result->execute();
		$res=$result->fetchAll(PDO::FETCH_ASSOC);
		return $res;
	}

	public function getCountAprendices($idcen = null) {
		$sql = "SELECT COUNT(*) as total FROM usuario WHERE idper = 4";
		if ($idcen) {
			$sql .= " AND idcen = :idcen";
		}
		$modelo = new conexion();
		$conexion = $modelo->get_conexion();
		$stmt = $conexion->prepare($sql);
		if ($idcen) {
			$stmt->bindParam(':idcen', $idcen);
		}
		$stmt->execute();
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		return (int)($row['total'] ?? 0);
	}

	public function getCountAprendicesActivos($idcen = null) {
		$sql = "SELECT COUNT(*) as total FROM usuario WHERE idper = 4 AND actusu = 1";
		if ($idcen) {
			$sql .= " AND idcen = :idcen";
		}
		$modelo = new conexion();
		$conexion = $modelo->get_conexion();
		$stmt = $conexion->prepare($sql);
		if ($idcen) {
			$stmt->bindParam(':idcen', $idcen);
		}
		$stmt->execute();
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		return (int)($row['total'] ?? 0);
	}

	public function getCountTotalAsociaciones($idcen = null) {
		$sql = "SELECT COUNT(*) as total FROM usufic uf INNER JOIN usuario u ON uf.idusu = u.idusu WHERE uf.actfic = 1";
		if ($idcen) {
			$sql .= " AND u.idcen = :idcen";
		}
		$modelo = new conexion();
		$conexion = $modelo->get_conexion();
		$stmt = $conexion->prepare($sql);
		if ($idcen) {
			$stmt->bindParam(':idcen', $idcen);
		}
		$stmt->execute();
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		return (int)($row['total'] ?? 0);
	}

	public function getFichasDisponibles($idcen = null) {
		$sql = "SELECT f.idfic, f.nomfic, v.nomval AS jornada, f.mun FROM ficha f LEFT JOIN valor v ON f.jornada = v.idval";
		if ($idcen) {
			$sql .= " WHERE f.idcen = :idcen";
		}
		$sql .= " ORDER BY f.idfic DESC LIMIT 200";
		$modelo = new conexion();
		$conexion = $modelo->get_conexion();
		$stmt = $conexion->prepare($sql);
		if ($idcen) {
			$stmt->bindParam(':idcen', $idcen);
		}
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}
	public function getUserProfileStats($idusu) {
		if(!$idusu) return null;
		$modelo = new conexion();
		$conexion = $modelo->get_conexion();
		
		// Datos básicos del usuario y perfil
		$sqlUsu = "SELECT u.idusu, u.ndocusu, u.nomusu, u.emausu, u.telcan, u.fotcan, u.actusu, 
		                  p.idper, p.nomper, c.idcen, c.nomcen, r.nomval as rh, t.nomval as tdoc, g.nomval as genero
		           FROM usuario u
		           INNER JOIN perfil p ON u.idper = p.idper
		           INNER JOIN centro c ON u.idcen = c.idcen
		           LEFT JOIN valor r ON u.rhusu = r.idval
		           LEFT JOIN valor t ON u.tdousu = t.idval
		           LEFT JOIN valor g ON u.genusu = g.idval
		           WHERE u.idusu = :idusu";
		$stmtUsu = $conexion->prepare($sqlUsu);
		$stmtUsu->bindParam(':idusu', $idusu);
		$stmtUsu->execute();
		$user = $stmtUsu->fetch(PDO::FETCH_ASSOC);

		if(!$user) return null;

		// Fichas asociadas
		$sqlFic = "SELECT uf.idfic, f.nomfic, uf.actfic 
		           FROM usufic uf 
		           INNER JOIN ficha f ON uf.idfic = f.idfic 
		           WHERE uf.idusu = :idusu 
		           ORDER BY uf.actfic ASC, uf.idfic DESC";
		$stmtFic = $conexion->prepare($sqlFic);
		$stmtFic->bindParam(':idusu', $idusu);
		$stmtFic->execute();
		$fichas = $stmtFic->fetchAll(PDO::FETCH_ASSOC);

		// Perfiles secundarios
		$sqlPef = "SELECT p.idper, p.nomper, m.nommod 
		           FROM usupef up 
		           INNER JOIN perfil p ON up.idper = p.idper 
		           INNER JOIN modulo m ON p.idmod = m.idmod 
		           WHERE up.idusu = :idusu";
		$stmtPef = $conexion->prepare($sqlPef);
		$stmtPef->bindParam(':idusu', $idusu);
		$stmtPef->execute();
		$perfilesSec = $stmtPef->fetchAll(PDO::FETCH_ASSOC);

		// Verificación de foto
		$fotcan = trim($user['fotcan'] ?? '');
		$tieneFoto = (!empty($fotcan) && $fotcan !== 'fcan/' && $fotcan !== 'img/user.jpg' && $fotcan !== 'image/user-default.webp');
		if($tieneFoto && !file_exists($fotcan) && !file_exists('../' . $fotcan)) {
			// Si la ruta no existe físicamente, no se considera foto cargada
			$tieneFoto = false;
		}

		// Cálculo de completitud de perfil
		$campos = ['nomusu', 'ndocusu', 'emausu', 'telcan', 'rh', 'tdoc', 'genero'];
		$completados = 0;
		foreach($campos as $campo){
			if(!empty($user[$campo])) $completados++;
		}
		if($tieneFoto) $completados++;
		$porcentajePerfil = round(($completados / (count($campos) + 1)) * 100);

		return [
			'usuario' => $user,
			'perfil_principal' => $user['nomper'] ?? 'Sin perfil',
			'total_fichas' => count($fichas),
			'fichas' => $fichas,
			'ficha_principal' => !empty($fichas) ? $fichas[0] : null,
			'perfiles_secundarios' => $perfilesSec,
			'tiene_foto' => $tieneFoto,
			'completitud' => $porcentajePerfil,
			'estado_texto' => ($user['actusu'] == 1 ? 'Activo' : ($user['actusu'] == 3 ? 'Desertó' : 'Inactivo'))
		];
	}

	/**
	 * MEJORA 3.1 & 3.2: Asignación masiva de perfiles a múltiples usuarios en lote de forma transaccional
	 */
	public function asignarPerfilesLote(array $idsUsuarios, array $perfiles, $perfilPrincipal = null) {
		if (empty($idsUsuarios)) {
			return ['success' => false, 'error' => 'No se seleccionaron usuarios para la asignación.'];
		}

		$modelo = new conexion();
		$conexion = $modelo->get_conexion();
		$conexion->beginTransaction();

		try {
			$stmtDel = $conexion->prepare("DELETE FROM usupef WHERE idusu = :idusu");
			$stmtIns = $conexion->prepare("INSERT INTO usupef (idusu, idper) VALUES (:idusu, :idper)");
			$stmtUpdPrinc = null;
			if (!empty($perfilPrincipal) && (int)$perfilPrincipal > 0) {
				$stmtUpdPrinc = $conexion->prepare("UPDATE usuario SET idper = :idper WHERE idusu = :idusu");
			}

			$procesados = 0;
			$usuariosModificados = [];

			foreach ($idsUsuarios as $idU) {
				$idU = (int)$idU;
				if ($idU <= 0) continue;

				// 1. Eliminar perfiles secundarios previos en usupef
				$stmtDel->bindValue(':idusu', $idU, PDO::PARAM_INT);
				$stmtDel->execute();

				// 2. Insertar perfiles asignados por módulo
				if (!empty($perfiles)) {
					$perfilesUnicos = array_unique(array_filter(array_map('intval', $perfiles)));
					foreach ($perfilesUnicos as $idPer) {
						if ($idPer > 0) {
							$stmtIns->bindValue(':idusu', $idU, PDO::PARAM_INT);
							$stmtIns->bindValue(':idper', $idPer, PDO::PARAM_INT);
							$stmtIns->execute();
						}
					}
				}

				// 3. Actualizar perfil principal en tabla usuario si se definió
				if ($stmtUpdPrinc) {
					$stmtUpdPrinc->bindValue(':idper', (int)$perfilPrincipal, PDO::PARAM_INT);
					$stmtUpdPrinc->bindValue(':idusu', $idU, PDO::PARAM_INT);
					$stmtUpdPrinc->execute();
				}

				$procesados++;
				$usuariosModificados[] = $idU;
			}

			$conexion->commit();

			// Si el usuario actualmente logueado está entre los modificados, sincronizar su sesión de inmediato
			if (isset($_SESSION['idusu']) && in_array((int)$_SESSION['idusu'], $usuariosModificados)) {
				$this->sincronizarPermisosSesion((int)$_SESSION['idusu']);
			}

			return [
				'success' => true,
				'procesados' => $procesados,
				'usuarios' => $usuariosModificados,
				'mensaje' => "Se han asignado los perfiles correctamente a {$procesados} usuario(s)."
			];
		} catch (Exception $e) {
			if ($conexion->inTransaction()) {
				$conexion->rollBack();
			}
			return ['success' => false, 'error' => $e->getMessage()];
		}
	}

	/**
	 * MEJORA 3.2: Sincronización de permisos y control de acceso en tiempo real sin requerir re-autenticación
	 */
	public function sincronizarPermisosSesion($idusu) {
		if (!$idusu) return null;

		$modelo = new conexion();
		$conexion = $modelo->get_conexion();

		// Obtener perfil principal actual desde la BD
		$stmtUsu = $conexion->prepare("SELECT u.idusu, u.idper, p.nomper, u.actusu FROM usuario u INNER JOIN perfil p ON u.idper = p.idper WHERE u.idusu = :idusu");
		$stmtUsu->bindParam(':idusu', $idusu, PDO::PARAM_INT);
		$stmtUsu->execute();
		$rowUsu = $stmtUsu->fetch(PDO::FETCH_ASSOC);

		if (!$rowUsu) return null;

		// Obtener todos los perfiles secundarios asignados en usupef
		$stmtPef = $conexion->prepare("SELECT up.idper, p.nomper, p.idmod, m.nommod FROM usupef up INNER JOIN perfil p ON up.idper = p.idper INNER JOIN modulo m ON p.idmod = m.idmod WHERE up.idusu = :idusu");
		$stmtPef->bindParam(':idusu', $idusu, PDO::PARAM_INT);
		$stmtPef->execute();
		$perfilesAsignados = $stmtPef->fetchAll(PDO::FETCH_ASSOC);

		$perfilesIds = array_column($perfilesAsignados, 'idper');
		$perfilesIds[] = (int)$rowUsu['idper'];
		$perfilesIds = array_values(array_unique(array_map('intval', $perfilesIds)));

		// Si es el usuario conectado a la sesión PHP actual, refrescar variables de sesión
		if (isset($_SESSION['idusu']) && (int)$_SESSION['idusu'] === (int)$idusu) {
			$_SESSION['idpergen'] = (int)$rowUsu['idper'];
			$_SESSION['perfiles_activos'] = $perfilesIds;

			// Si el usuario tiene un idper activo de módulo y dicho perfil ya no le pertenece, actualizarlo
			if (!empty($_SESSION['idper']) && !in_array((int)$_SESSION['idper'], $perfilesIds)) {
				// Reasignar al perfil principal
				$_SESSION['idper'] = (int)$rowUsu['idper'];
				$_SESSION['pefnom'] = $rowUsu['nomper'];
			}
		}

		return [
			'idusu' => (int)$idusu,
			'idper_principal' => (int)$rowUsu['idper'],
			'nomper_principal' => $rowUsu['nomper'],
			'actusu' => (int)$rowUsu['actusu'],
			'perfiles_ids' => $perfilesIds,
			'perfiles_secundarios' => $perfilesAsignados,
			'sincronizado_en' => date('Y-m-d H:i:s')
		];
	}

	/**
	 * Obtener módulos y perfiles disponibles agrupados para la asignación masiva
	 */
	public function getPerfilesAgrupadosModulos() {
		$modelo = new conexion();
		$conexion = $modelo->get_conexion();
		$sql = "SELECT m.idmod, m.nommod, p.idper, p.nomper 
		        FROM modulo m 
		        LEFT JOIN perfil p ON m.idmod = p.idmod 
		        ORDER BY m.idmod ASC, p.nomper ASC";
		$stmt = $conexion->prepare($sql);
		$stmt->execute();
		$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

		$modulos = [];
		foreach ($rows as $r) {
			$idmod = $r['idmod'];
			if (!isset($modulos[$idmod])) {
				$modulos[$idmod] = [
					'idmod' => $idmod,
					'nommod' => $r['nommod'],
					'perfiles' => []
				];
			}
			if (!empty($r['idper'])) {
				$modulos[$idmod]['perfiles'][] = [
					'idper' => $r['idper'],
					'nomper' => $r['nomper']
				];
			}
		}
		return array_values($modulos);
	}

	/**
	 * Obtener los IDs de perfil asignados a un usuario específico
	 */
	public function getPerfilesUsuarioDetalle($idusu) {
		if (!$idusu) return [];
		$modelo = new conexion();
		$conexion = $modelo->get_conexion();

		$sql = "SELECT up.idper, p.idmod FROM usupef up INNER JOIN perfil p ON up.idper = p.idper WHERE up.idusu = :idusu";
		$stmt = $conexion->prepare($sql);
		$stmt->bindParam(':idusu', $idusu, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}
}

// Nombre de clase alineado con el archivo nuevo, manteniendo Musu por compatibilidad.
if (!class_exists('Usumcusu', false)) {
    class Usumcusu extends Musu {}
}
?>