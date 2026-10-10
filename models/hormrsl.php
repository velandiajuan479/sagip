<?php 
require_once __DIR__ . "/conexion.php";

class Hormrsl{
	// Atributos
	private $idres;
	private $nomres;
	private $idcom;
	private $ndeses;
	private $codpro;

	// Métodos GET
	public function getIdres(){
		return $this->idres;
	}
	public function getNomres(){
		return $this->nomres;
	}
	public function getIdcom(){
		return $this->idcom;
	}
	public function getNdeses(){
		return $this->ndeses;
	}
	public function getCodpro(){
		return $this->codpro;
	}

	// Métodos SET
	public function setIdres($idres){
		$this->idres = $idres;
	}
	public function setNomres($nomres){
		$this->nomres = $nomres;
	}
	public function setIdcom($idcom){
		$this->idcom = $idcom;
	}
	public function setNdeses($ndeses){
		$this->ndeses = $ndeses;
	}
	public function setCodpro($codpro){
		$this->codpro = $codpro;
	}

	// Métodos públicos CRUD
	public function getAll(){
		$res = NULL;
		$sql = "SELECT r.idres, r.nomres, r.idcom, r.ndeses, 
		               COALESCE(c.descom, 'Sin competencia asignada') AS descom, c.vercom, c.horcom, COALESCE(v.nomval, 'General') AS nomval
		        FROM resultado AS r
		        LEFT JOIN competencia AS c ON r.idcom = c.idcom
		        LEFT JOIN valor AS v ON c.idval = v.idval
		        ORDER BY c.descom ASC, r.idres ASC";
		$modelo = new conexion();
		$conexion = $modelo->get_conexion();
		$result = $conexion->prepare($sql);
		$result->execute();
		$res = $result->fetchAll(PDO::FETCH_ASSOC);
		return $res;
	}

	// Resultados de aprendizaje vinculados a programas formativos con estado activo/inactivo
	public function getAllWithProgramas(){
		$res = NULL;
		$sql = "SELECT r.idres, r.nomres, r.idcom, r.ndeses, 
		               COALESCE(c.descom, 'Sin competencia asignada') AS descom, 
		               c.vercom, c.horcom, 
		               COALESCE(v.nomval, 'General') AS nomval,
		               px_agg.codpro,
		               px_agg.nompro,
		               COALESCE(px_agg.cant_progs, 0) AS cant_progs,
		               COALESCE(px_agg.fic_vigentes, 0) AS fic_vigentes
		        FROM resultado AS r
		        LEFT JOIN competencia AS c ON r.idcom = c.idcom
		        LEFT JOIN valor AS v ON c.idval = v.idval
		        LEFT JOIN (
		            SELECT px.idcom,
		                   MIN(p.codpro) AS codpro,
		                   GROUP_CONCAT(DISTINCT p.nompro ORDER BY p.nompro ASC SEPARATOR ' | ') AS nompro,
		                   COUNT(DISTINCT px.codpro) AS cant_progs,
		                   COUNT(DISTINCT f.idfic) AS fic_vigentes
		            FROM proxcom px
		            INNER JOIN programa p ON px.codpro = p.codpro
		            LEFT JOIN ficha f ON p.codpro = f.codpro AND f.ffinfic >= CURDATE()
		            GROUP BY px.idcom
		        ) AS px_agg ON r.idcom = px_agg.idcom
		        ORDER BY c.descom ASC, r.idres ASC";
		$modelo = new conexion();
		$conexion = $modelo->get_conexion();
		$result = $conexion->prepare($sql);
		$result->execute();
		$res = $result->fetchAll(PDO::FETCH_ASSOC);
		return $res;
	}

	public function getOne(){
		$res = NULL;
		$sql = "SELECT r.idres, r.nomres, r.idcom, r.ndeses, c.descom, c.vercom, c.horcom, v.nomval 
		        FROM resultado AS r 
		        LEFT JOIN competencia AS c ON r.idcom = c.idcom 
		        LEFT JOIN valor AS v ON c.idval = v.idval
		        WHERE r.idres = :idres";
		$modelo = new conexion();
		$conexion = $modelo->get_conexion();
		$result = $conexion->prepare($sql);
		$idres = $this->getIdres();
		$result->bindParam(":idres", $idres);
		$result->execute();
		$res = $result->fetchAll(PDO::FETCH_ASSOC);
		return $res;
	}

	public function save(){
		try {
			$sql = "INSERT INTO resultado (idres, nomres, idcom, ndeses) 
			        VALUES (:idres, :nomres, :idcom, :ndeses)";
			$modelo = new conexion(); 
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
			error_log("Error en Hormrsl::save(): " . $e->getMessage());
			return false;
		}
	}

	public function edit(){
		try {
			$sql = "UPDATE resultado 
			        SET nomres=:nomres, idcom=:idcom, ndeses=:ndeses 
			        WHERE idres=:idres";
			$modelo = new conexion();
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
			error_log("Error en Hormrsl::edit(): " . $e->getMessage());
			return false;
		}
	}

	public function del(){
		try {
			$sql = "DELETE FROM resultado WHERE idres=:idres";
			$modelo = new conexion();
			$idres = $this->getIdres();
			return $modelo->executeWrite($sql, [':idres' => $idres]);
		} catch (Exception $e) {
			error_log("Error en Hormrsl::del(): " . $e->getMessage());
			return false;
		}
	}

	// Listado de competencias para el selector del formulario
	public function getCompetenciasList(){
		$sql = "SELECT c.idcom, c.descom, c.vercom, c.horcom, COALESCE(v.nomval, 'Competencia') AS nomval 
		        FROM competencia c 
		        LEFT JOIN valor v ON c.idval = v.idval 
		        ORDER BY c.descom ASC";
		$modelo = new conexion();
		$conexion = $modelo->get_conexion();
		$stmt = $conexion->prepare($sql);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	// Listado de programas para filtros
	public function getProgramasList(){
		$sql = "SELECT codpro, nompro, verpro FROM programa ORDER BY nompro ASC";
		$modelo = new conexion();
		$conexion = $modelo->get_conexion();
		$stmt = $conexion->prepare($sql);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	// Métricas del Dashboard de Resultados (cálculo ultrarrápido en memoria o fallback directo)
	public function getResumenDashboard($datList = null){
		$res = [
			'total_resultados' => 0,
			'resultados_activos' => 0,
			'resultados_inactivos' => 0,
			'raps_sin_programa' => 0,
			'total_sesiones' => 0,
			'competencias_cubiertas' => 0,
			'promedio_sesiones' => 0,
			'total_competencias' => 0,
			'programas_cubiertos' => 0
		];

		if ($datList !== null && is_array($datList)) {
			$compMap = [];
			$progMap = [];
			$totRes = count($datList);
			$totSes = 0;
			$activos = 0;
			$huerfanos = 0;

			foreach ($datList as $r) {
				$totSes += (int)($r['ndeses'] ?? 0);
				if (!empty($r['idcom'])) {
					$compMap[$r['idcom']] = true;
				}
				if (!empty($r['codpro'])) {
					$progMap[$r['codpro']] = true;
				}

				$esHuerfano = empty($r['cant_progs']) || empty($r['codpro']);
				$esActivo = !empty($r['fic_vigentes']) && ($r['fic_vigentes'] > 0);

				if ($esHuerfano) {
					$huerfanos++;
				} elseif ($esActivo) {
					$activos++;
				}
			}

			$res['total_resultados'] = $totRes;
			$res['total_sesiones'] = $totSes;
			$res['competencias_cubiertas'] = count($compMap);
			$res['promedio_sesiones'] = $totRes > 0 ? round($totSes / $totRes, 1) : 0;
			$res['resultados_activos'] = $activos;
			$res['raps_sin_programa'] = $huerfanos;
			$res['resultados_inactivos'] = max(0, $totRes - $activos - $huerfanos);
			$res['total_competencias'] = count($compMap);
			$res['programas_cubiertos'] = count($progMap);
			return $res;
		}

		try {
			$sql = "SELECT 
			    (SELECT COUNT(*) FROM resultado) AS total_resultados,
			    (SELECT COALESCE(SUM(ndeses), 0) FROM resultado) AS total_sesiones,
			    (SELECT COUNT(DISTINCT idcom) FROM resultado) AS competencias_cubiertas,
			    (SELECT COALESCE(ROUND(AVG(ndeses), 1), 0) FROM resultado) AS promedio_sesiones,
			    (SELECT COUNT(*) FROM competencia) AS total_competencias,
			    (SELECT COUNT(DISTINCT codpro) FROM proxcom) AS programas_cubiertos";
			$modelo = new conexion();
			$conexion = $modelo->get_conexion();
			$stmt = $conexion->prepare($sql);
			$stmt->execute();
			$r = $stmt->fetch(PDO::FETCH_ASSOC);
			if ($r) {
				$res['total_resultados'] = (int)$r['total_resultados'];
				$res['total_sesiones'] = (int)$r['total_sesiones'];
				$res['competencias_cubiertas'] = (int)$r['competencias_cubiertas'];
				$res['promedio_sesiones'] = (float)$r['promedio_sesiones'];
				$res['total_competencias'] = (int)$r['total_competencias'];
				$res['programas_cubiertos'] = (int)$r['programas_cubiertos'];
			}
		} catch (\Throwable $e) {}

		return $res;
	}

	// Distribución de resultados según la tipología de la competencia
	public function getDistTipos(){
		$sql = "SELECT v.nomval, COUNT(r.idres) AS total_res, COALESCE(SUM(r.ndeses), 0) AS total_ses 
		        FROM resultado r 
		        INNER JOIN competencia c ON r.idcom = c.idcom 
		        INNER JOIN valor v ON c.idval = v.idval 
		        GROUP BY v.idval, v.nomval 
		        ORDER BY total_res DESC";
		$modelo = new conexion();
		$conexion = $modelo->get_conexion();
		$stmt = $conexion->prepare($sql);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	public function existeResultado($idres){
		$sql = "SELECT idres FROM resultado WHERE idres = :idres";
		$modelo = new conexion();
		$conexion = $modelo->get_conexion();
		$stmt = $conexion->prepare($sql);
		$stmt->bindParam(":idres", $idres);
		$stmt->execute();
		$r = $stmt->fetch(PDO::FETCH_ASSOC);
		return !empty($r);
	}
}
class_alias('Hormrsl', 'Mrsl');
?>
