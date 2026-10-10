<?php 
class Hormcmp{
	// Atributos competencia
	private $idcom;
	private $descom;
	private $vercom;
	private $horcom;
	private $idval;

	// Atributos proxcom
	private $codpro;

	// Atributos resultado
	private $idres;
	private $nomres;
	private $ndeses;

	// Métodos GET
	public function getIdcom(){
		return $this->idcom;
	}
	public function getDescom(){
		return $this->descom;
	}
	public function getVercom(){
		return $this->vercom;
	}
	public function getHorcom(){
		return $this->horcom;
	}
	public function getIdval(){
		return $this->idval;
	}

	public function getCodpro(){
		return $this->codpro;
	}

	public function getIdres(){
		return $this->idres;
	}
	public function getNomres(){
		return $this->nomres;
	}
	public function getNdeses(){
		return $this->ndeses;
	}

	// Métodos SET
	public function setIdcom($idcom){
		$this->idcom = $idcom;
	}
	public function setDescom($descom){
		$this->descom = $descom;
	}
	public function setVercom($vercom){
		$this->vercom = $vercom;
	}
	public function setHorcom($horcom){
		$this->horcom = $horcom;
	}
	public function setIdval($idval){
		$this->idval = $idval;
	}

	public function setCodpro($codpro){
		$this->codpro = $codpro;
	}

	public function setIdres($idres){
		$this->idres = $idres;
	}
	public function setNomres($nomres){
		$this->nomres = $nomres;
	}
	public function setNdeses($ndeses){
		$this->ndeses = $ndeses;
	}

	// Métodos públicos Competencia
	public function getAll(){
		$res = NULL;
		$sql = "SELECT c.idcom, c.descom, c.vercom, c.horcom, c.idval, v.nomval 
		        FROM competencia AS c 
		        INNER JOIN valor as v ON c.idval=v.idval 
		        INNER JOIN proxcom AS p ON c.idcom=p.idcom 
		        WHERE p.codpro=:codpro 
		        ORDER BY c.descom ASC";
		$modelo = new Conexion();
		$conexion = $modelo->get_conexion();
		$result = $conexion->prepare($sql);
		$codpro = $this->getCodpro();
		$result->bindParam(":codpro", $codpro);
		$result->execute();
		$res = $result->fetchall(PDO::FETCH_ASSOC);
		return $res;
	}

	// Obtiene competencias del programa o globales con conteo de resultados, programas y fichas activas
	public function getAllWithStats($codproFiltro = null){
		$codpro = $codproFiltro !== null ? $codproFiltro : $this->getCodpro();
		$modelo = new Conexion();
		$conexion = $modelo->get_conexion();

		if ($codpro && $codpro !== 'todos') {
			$sql = "SELECT c.idcom, c.descom, c.vercom, c.horcom, c.idval, COALESCE(v.nomval, 'Competencia Laboral') AS nomval,
			               COUNT(DISTINCT r.idres) AS cant_res,
			               COALESCE(SUM(r.ndeses), 0) AS cant_ses,
			               1 AS cant_progs,
			               (SELECT COUNT(DISTINCT f.idfic) FROM ficha f WHERE f.codpro = :codpro AND f.ffinfic >= CURDATE()) AS fic_activas,
			               p.nompro AS prog_nombre
			        FROM competencia AS c 
			        LEFT JOIN valor as v ON c.idval=v.idval 
			        INNER JOIN proxcom AS px ON c.idcom=px.idcom 
			        INNER JOIN programa AS p ON px.codpro=p.codpro
			        LEFT JOIN resultado AS r ON c.idcom=r.idcom
			        WHERE px.codpro=:codpro
			        GROUP BY c.idcom, c.descom, c.vercom, c.horcom, c.idval, v.nomval, p.nompro
			        ORDER BY v.nomval ASC, c.descom ASC";
			$result = $conexion->prepare($sql);
			$result->bindParam(":codpro", $codpro);
			$result->execute();
			return $result->fetchAll(PDO::FETCH_ASSOC);
		} else {
			$sql = "SELECT c.idcom, c.descom, c.vercom, c.horcom, c.idval, COALESCE(v.nomval, 'Competencia Laboral') AS nomval,
			               COUNT(DISTINCT r.idres) AS cant_res,
			               COALESCE(SUM(r.ndeses), 0) AS cant_ses,
			               COUNT(DISTINCT px.codpro) AS cant_progs,
			               COUNT(DISTINCT CASE WHEN f.ffinfic >= CURDATE() THEN f.idfic END) AS fic_activas,
			               GROUP_CONCAT(DISTINCT p.nompro ORDER BY p.nompro ASC SEPARATOR ' | ') AS progs_nombres
			        FROM competencia AS c 
			        LEFT JOIN valor as v ON c.idval=v.idval 
			        LEFT JOIN proxcom AS px ON c.idcom=px.idcom 
			        LEFT JOIN programa AS p ON px.codpro=p.codpro
			        LEFT JOIN ficha AS f ON p.codpro=f.codpro
			        LEFT JOIN resultado AS r ON c.idcom=r.idcom
			        GROUP BY c.idcom, c.descom, c.vercom, c.horcom, c.idval, v.nomval
			        ORDER BY v.nomval ASC, c.descom ASC";
			$result = $conexion->prepare($sql);
			$result->execute();
			return $result->fetchAll(PDO::FETCH_ASSOC);
		}
	}

	public function getOne(){
		$res = NULL;
		$sql = "SELECT c.idcom, c.descom, c.vercom, c.horcom, c.idval, COALESCE(v.nomval, 'Competencia Laboral') AS nomval 
		        FROM competencia AS c 
		        LEFT JOIN valor as v ON c.idval=v.idval 
		        WHERE c.idcom=:idcom";
		$modelo = new Conexion();
		$conexion = $modelo->get_conexion();
		$result = $conexion->prepare($sql);
		$idcom = $this->getIdcom();
		$result->bindParam(":idcom", $idcom);
		$result->execute();
		$res = $result->fetchall(PDO::FETCH_ASSOC);
		return $res;
	}

	public function getTipComp(){
		$sql = "SELECT idval, nomval, parval FROM valor WHERE act=1 AND iddom=15 ORDER BY parval";
		$modelo = new Conexion();
		$conexion = $modelo->get_conexion();
		$result = $conexion->prepare($sql);
		$result->execute();
		$res = $result->fetchall(PDO::FETCH_ASSOC);
		return $res;
	}

	public function save(){
		try {
			$sql = "INSERT INTO competencia (idcom, descom, vercom, horcom, idval) 
			        VALUES (:idcom, :descom, :vercom, :horcom, :idval)";
			$modelo = new Conexion();
			$idcom = $this->getIdcom();
			$descom = $this->getDescom();
			$vercom = (int)preg_replace('/[^0-9]/', '', (string)$this->getVercom()) ?: 1;
			$horcom = (int)($this->getHorcom() ?: 40);
			$idval = (int)$this->getIdval();
			if (!$idval || $idval === 1) {
				$idval = 1049; // Tipología estándar técnica
			}

			$params = [
				':idcom'  => $idcom,
				':descom' => $descom,
				':vercom' => $vercom,
				':horcom' => $horcom,
				':idval'  => $idval
			];

			return $modelo->executeWrite($sql, $params);
		} catch (Exception $e) {
			if (session_status() === PHP_SESSION_ACTIVE) {
				$_SESSION['last_crud_error'] = $e->getMessage();
			}
			error_log("Error en Hormcmp::save(): " . $e->getMessage());
			return false;
		}
	}

	public function edit(){
		try {
			$sql = "UPDATE competencia SET descom=:descom, vercom=:vercom, horcom=:horcom, idval=:idval WHERE idcom=:idcom";
			$modelo = new Conexion();
			$idcom = $this->getIdcom();
			$descom = $this->getDescom();
			$vercom = (int)preg_replace('/[^0-9]/', '', (string)$this->getVercom()) ?: 1;
			$horcom = (int)($this->getHorcom() ?: 40);
			$idval = (int)$this->getIdval();
			if (!$idval || $idval === 1) {
				$idval = 1049;
			}

			$params = [
				':idcom'  => $idcom,
				':descom' => $descom,
				':vercom' => $vercom,
				':horcom' => $horcom,
				':idval'  => $idval
			];

			return $modelo->executeWrite($sql, $params);
		} catch (Exception $e) {
			if (session_status() === PHP_SESSION_ACTIVE) {
				$_SESSION['last_crud_error'] = $e->getMessage();
			}
			error_log("Error en Hormcmp::edit(): " . $e->getMessage());
			return false;
		}
	}

	public function del(){
		try {
			$modelo = new Conexion();
			$idcom = $this->getIdcom();

			$sqlRes = "DELETE FROM resultado WHERE idcom=:idcom";
			$modelo->executeWrite($sqlRes, [':idcom' => $idcom]);

			$sql = "DELETE FROM competencia WHERE idcom=:idcom";
			return $modelo->executeWrite($sql, [':idcom' => $idcom]);
		} catch (Exception $e) {
			error_log("Error en Hormcmp::del(): " . $e->getMessage());
			return false;
		}
	}

	// Métodos públicos ProxCom
	public function savePxC(){
		$codpro = $this->getCodpro();
		$idcom = $this->getIdcom();
		if (!$codpro || !is_numeric($codpro) || !$idcom || !is_numeric($idcom)) {
			return false;
		}
		try {
			$sql = "INSERT IGNORE INTO proxcom(codpro, idcom) VALUES (:codpro, :idcom)";
			$modelo = new Conexion();
			return $modelo->executeWrite($sql, [':codpro' => $codpro, ':idcom' => $idcom]);
		} catch (Exception $e) {
			error_log("Error en Hormcmp::savePxC(): " . $e->getMessage());
			return false;
		}
	}

	public function delPxC(){
		$codpro = $this->getCodpro();
		$idcom = $this->getIdcom();
		if (!$codpro || !is_numeric($codpro) || !$idcom || !is_numeric($idcom)) {
			return false;
		}
		try {
			$sql = "DELETE FROM proxcom WHERE codpro=:codpro AND idcom=:idcom";
			$modelo = new Conexion();
			return $modelo->executeWrite($sql, [':codpro' => $codpro, ':idcom' => $idcom]);
		} catch (Exception $e) {
			error_log("Error en Hormcmp::delPxC(): " . $e->getMessage());
			return false;
		}
	}

	// Métodos públicos Resultados
	public function getAllRs(){
		$res = NULL;
		$sql = "SELECT idres, nomres, idcom, ndeses FROM resultado WHERE idcom=:idcom ORDER BY idres ASC";
		$modelo = new Conexion();
		$conexion = $modelo->get_conexion();
		$result = $conexion->prepare($sql);
		$idcom = $this->getIdcom();
		$result->bindParam(":idcom", $idcom);
		$result->execute();
		$res = $result->fetchall(PDO::FETCH_ASSOC);
		return $res;
	}

	public function getAllRsProg(){
		$codpro = $this->getCodpro();
		if(!$codpro) return [];
		$sql = "SELECT r.idres, r.nomres, r.idcom, r.ndeses 
		        FROM resultado r
		        INNER JOIN proxcom p ON r.idcom = p.idcom
		        WHERE p.codpro = :codpro
		        ORDER BY r.idcom ASC, r.idres ASC";
		$modelo = new Conexion();
		$conexion = $modelo->get_conexion();
		$stmt = $conexion->prepare($sql);
		$stmt->bindParam(":codpro", $codpro);
		$stmt->execute();
		$res = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$grouped = [];
		foreach($res as $row){
			$grouped[$row['idcom']][] = $row;
		}
		return $grouped;
	}

	public function getOneRs(){
		$res = NULL;
		$sql = "SELECT idres, nomres, idcom, ndeses FROM resultado WHERE idres=:idres";
		$modelo = new Conexion();
		$conexion = $modelo->get_conexion();
		$result = $conexion->prepare($sql);
		$idres = $this->getIdres();
		$result->bindParam(":idres", $idres);
		$result->execute();
		$res = $result->fetchall(PDO::FETCH_ASSOC);
		return $res;
	}

	public function saveRs(){
		try {
			$sql = "INSERT INTO resultado (idres, nomres, idcom, ndeses) 
			        VALUES (:idres, :nomres, :idcom, :ndeses)";
			$modelo = new Conexion();
			$idres = $this->getIdres();
			$nomres = $this->getNomres();
			$idcom = $this->getIdcom();
			$ndeses = (int)($this->getNdeses() ?: 1);

			$params = [
				':idres'  => $idres,
				':nomres' => $nomres,
				':idcom'  => $idcom,
				':ndeses' => $ndeses
			];

			return $modelo->executeWrite($sql, $params);
		} catch (Exception $e) {
			if (session_status() === PHP_SESSION_ACTIVE) {
				$_SESSION['last_crud_error'] = $e->getMessage();
			}
			error_log("Error en Hormcmp::saveRs(): " . $e->getMessage());
			return false;
		}
	}

	public function editRs(){
		try {
			$sql = "UPDATE resultado SET nomres=:nomres, idcom=:idcom, ndeses=:ndeses WHERE idres=:idres";
			$modelo = new Conexion();
			$idres = $this->getIdres();
			$nomres = $this->getNomres();
			$idcom = $this->getIdcom();
			$ndeses = (int)($this->getNdeses() ?: 1);

			$params = [
				':idres'  => $idres,
				':nomres' => $nomres,
				':idcom'  => $idcom,
				':ndeses' => $ndeses
			];

			return $modelo->executeWrite($sql, $params);
		} catch (Exception $e) {
			if (session_status() === PHP_SESSION_ACTIVE) {
				$_SESSION['last_crud_error'] = $e->getMessage();
			}
			error_log("Error en Hormcmp::editRs(): " . $e->getMessage());
			return false;
		}
	}

	public function delRs(){
		try {
			$sql = "DELETE FROM resultado WHERE idres=:idres";
			$modelo = new Conexion();
			$idres = $this->getIdres();
			return $modelo->executeWrite($sql, [':idres' => $idres]);
		} catch (Exception $e) {
			error_log("Error en Hormcmp::delRs(): " . $e->getMessage());
			return false;
		}
	}

	// Métodos para el Dashboard y Sistematización de Competencias
	public function getResumenProg($codproFiltro = null){
		$codpro = $codproFiltro !== null ? $codproFiltro : $this->getCodpro();
		$res = [
			'tot_comp' => 0,
			'comp_activas' => 0,
			'comp_inactivas' => 0,
			'comp_sin_vincular' => 0,
			'tot_horas' => 0,
			'tot_res' => 0,
			'tot_ses' => 0,
			'prom_horas_comp' => 0,
			'prom_raps_comp' => 0,
			'prom_ses' => 0
		];

		$modelo = new Conexion();
		$conexion = $modelo->get_conexion();

		if ($codpro && $codpro !== 'todos') {
			// Métricas para un programa específico
			$sql1 = "SELECT COUNT(DISTINCT c.idcom) AS tot_comp, 
			                COALESCE(SUM(c.horcom), 0) AS tot_horas,
			                COUNT(DISTINCT r.idres) AS tot_res, 
			                COALESCE(SUM(r.ndeses), 0) AS tot_ses,
			                (SELECT COUNT(DISTINCT f.idfic) FROM ficha f WHERE f.codpro = :codpro AND f.ffinfic >= CURDATE()) AS fic_activas
			         FROM competencia c
			         INNER JOIN proxcom p ON c.idcom = p.idcom
			         LEFT JOIN resultado r ON c.idcom = r.idcom
			         WHERE p.codpro = :codpro";
			$stmt1 = $conexion->prepare($sql1);
			$stmt1->bindParam(":codpro", $codpro);
			$stmt1->execute();
			$r1 = $stmt1->fetch(PDO::FETCH_ASSOC);
			if ($r1) {
				$res['tot_comp'] = (int)$r1['tot_comp'];
				$res['tot_horas'] = (int)$r1['tot_horas'];
				$res['tot_res'] = (int)$r1['tot_res'];
				$res['tot_ses'] = (int)$r1['tot_ses'];
				$esActivo = ((int)$r1['fic_activas'] > 0);
				$res['comp_activas'] = $esActivo ? $res['tot_comp'] : 0;
				$res['comp_inactivas'] = $esActivo ? 0 : $res['tot_comp'];
			}
		} else {
			// Métricas globales de todo el banco de competencias
			$sqlGlobal = "SELECT 
			    COUNT(DISTINCT c.idcom) AS tot_comp,
			    COALESCE(SUM(c.horcom), 0) AS tot_horas,
			    (SELECT COUNT(DISTINCT idres) FROM resultado) AS tot_res,
			    (SELECT COALESCE(SUM(ndeses), 0) FROM resultado) AS tot_ses,
			    COUNT(DISTINCT CASE WHEN f.ffinfic >= CURDATE() THEN c.idcom END) AS comp_activas,
			    COUNT(DISTINCT CASE WHEN px.idcom IS NULL THEN c.idcom END) AS comp_sin_vincular
			FROM competencia c
			LEFT JOIN proxcom px ON c.idcom = px.idcom
			LEFT JOIN ficha f ON px.codpro = f.codpro";
			$stmtG = $conexion->prepare($sqlGlobal);
			$stmtG->execute();
			$rg = $stmtG->fetch(PDO::FETCH_ASSOC);
			if ($rg) {
				$res['tot_comp'] = (int)$rg['tot_comp'];
				$res['comp_activas'] = (int)$rg['comp_activas'];
				$res['comp_sin_vincular'] = (int)$rg['comp_sin_vincular'];
				$res['comp_inactivas'] = max(0, $res['tot_comp'] - $res['comp_activas']);
				$res['tot_horas'] = (int)$rg['tot_horas'];
				$res['tot_res'] = (int)$rg['tot_res'];
				$res['tot_ses'] = (int)$rg['tot_ses'];
			}
		}

		$res['prom_horas_comp'] = ($res['tot_comp'] > 0) ? round($res['tot_horas'] / $res['tot_comp']) : 0;
		$res['prom_raps_comp'] = ($res['tot_comp'] > 0) ? round($res['tot_res'] / $res['tot_comp'], 1) : 0;
		$res['prom_ses'] = ($res['tot_res'] > 0) ? round($res['tot_ses'] / $res['tot_res'], 1) : 0;

		return $res;
	}

	public function getDistTipoComp($codproFiltro = null){
		$codpro = $codproFiltro !== null ? $codproFiltro : $this->getCodpro();
		$modelo = new Conexion();
		$conexion = $modelo->get_conexion();

		if ($codpro && $codpro !== 'todos') {
			$sql = "SELECT v.nomval, COUNT(c.idcom) AS cantidad, COALESCE(SUM(c.horcom), 0) AS horas
			        FROM competencia c
			        INNER JOIN valor v ON c.idval = v.idval
			        INNER JOIN proxcom p ON c.idcom = p.idcom
			        WHERE p.codpro = :codpro
			        GROUP BY v.idval, v.nomval
			        ORDER BY horas DESC";
			$stmt = $conexion->prepare($sql);
			$stmt->bindParam(":codpro", $codpro);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} else {
			$sql = "SELECT v.nomval, COUNT(c.idcom) AS cantidad, COALESCE(SUM(c.horcom), 0) AS horas
			        FROM competencia c
			        INNER JOIN valor v ON c.idval = v.idval
			        GROUP BY v.idval, v.nomval
			        ORDER BY horas DESC";
			$stmt = $conexion->prepare($sql);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		}
	}

	public function getCompetenciasDisponibles(){
		$codpro = $this->getCodpro();
		if(!$codpro) return [];
		$sql = "SELECT c.idcom, c.descom, c.vercom, c.horcom, v.nomval
		        FROM competencia c
		        INNER JOIN valor v ON c.idval = v.idval
		        WHERE c.idcom NOT IN (SELECT idcom FROM proxcom WHERE codpro = :codpro)
		        ORDER BY c.descom ASC";
		$modelo = new Conexion();
		$conexion = $modelo->get_conexion();
		$stmt = $conexion->prepare($sql);
		$stmt->bindParam(":codpro", $codpro);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	public function existeCompetencia($idcom){
		$sql = "SELECT idcom FROM competencia WHERE idcom = :idcom";
		$modelo = new Conexion();
		$conexion = $modelo->get_conexion();
		$stmt = $conexion->prepare($sql);
		$stmt->bindParam(":idcom", $idcom);
		$stmt->execute();
		$r = $stmt->fetch(PDO::FETCH_ASSOC);
		return !empty($r);
	}

	public function existeResultado($idres){
		$sql = "SELECT idres FROM resultado WHERE idres = :idres";
		$modelo = new Conexion();
		$conexion = $modelo->get_conexion();
		$stmt = $conexion->prepare($sql);
		$stmt->bindParam(":idres", $idres);
		$stmt->execute();
		$r = $stmt->fetch(PDO::FETCH_ASSOC);
		return !empty($r);
	}
}
class_alias('Hormcmp', 'Mcom');
?>
