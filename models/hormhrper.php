<?php 
class hormhrper{
	// Atributos
	private $idhor;
	private $idfic;
	private $idaul;
	private $idusu;
	private $iddia;

	private $hinihor;
	private $hfinhor;
	private $es_otros;
	private $actividad;
	private $horas_otros;
	private $fecha_inicio;
	private $fecha_fin;
	private $fecha_especifica;
	private $es_formacion_directa;

	// Helper para obtener conexión configurada en UTF-8
	private function getConexionBD(){
		$modelo = new Conexion();
		$conexion = $modelo->get_conexion();
		$conexion->exec("SET NAMES utf8mb4");
		return $conexion;
	}

	// Métodos GET
	public function getIdhor(){
		return $this->idhor;
	}
	public function getIdfic(){
		return $this->idfic;
	}
	public function getIdaul(){
		return $this->idaul;
	}
	public function getIdusu(){
		return $this->idusu;
	}
	public function getIddia(){
		return $this->iddia;
	}
	public function getHinihor(){
		return $this->hinihor;
	}
	public function getHfinhor(){
		return $this->hfinhor;
	}
	public function getEsOtros(){
		return $this->es_otros;
	}
	public function getActividad(){
		return $this->actividad;
	}
	public function getHorasOtros(){
		return $this->horas_otros;
	}
	public function getFechaInicio(){
		return $this->fecha_inicio;
	}
	public function getFechaFin(){
		return $this->fecha_fin;
	}
	public function getFechaEspecifica(){
		return $this->fecha_especifica;
	}
	public function getEsFormacionDirecta(){
		return $this->es_formacion_directa;
	}

	// Métodos SET
	public function setIdhor($idhor){
		$this->idhor = $idhor;
	}
	public function setIdfic($idfic){
		$this->idfic = $idfic;
	}
	public function setIdaul($idaul){
		$this->idaul = $idaul;
	}
	public function setIdusu($idusu){
		$this->idusu = $idusu;
	}
	public function setIddia($iddia){
		$this->iddia = $iddia;
	}
	public function setHinihor($hinihor){
		$this->hinihor = $hinihor;
	}
	public function setHfinhor($hfinhor){
		$this->hfinhor = $hfinhor;
	}
	public function setEsOtros($es_otros){
		$this->es_otros = $es_otros;
	}
	public function setActividad($actividad){
		$this->actividad = $actividad;
	}
	public function setHorasOtros($horas_otros){
		$this->horas_otros = $horas_otros;
	}
	public function setFechaInicio($fecha_inicio){
		$this->fecha_inicio = $fecha_inicio;
	}
	public function setFechaFin($fecha_fin){
		$this->fecha_fin = $fecha_fin;
	}
	public function setFechaEspecifica($fecha_especifica){
		$this->fecha_especifica = $fecha_especifica;
	}
	public function setEsFormacionDirecta($es_formacion_directa){
		$this->es_formacion_directa = $es_formacion_directa;
	}

	// Obtener datos del usuario por ID desde la BD real
	public function getUsuarioById($idusu){
		if (empty($idusu)) return NULL;
		$conexion = $this->getConexionBD();
		$sql = "SELECT idusu, ndocusu, nomusu, emausu, actusu FROM usuario WHERE idusu = :idusu LIMIT 1";
		$result = $conexion->prepare($sql);
		$result->bindParam(":idusu", $idusu);
		$result->execute();
		return $result->fetch(PDO::FETCH_ASSOC);
	}

	// Obtener instructores asociados a una ficha específica
	public function getInstructoresPorFicha($idfic){
		if (empty($idfic)) return array();
		$conexion = $this->getConexionBD();
		$sql = "SELECT DISTINCT p.idusu, p.ndocusu, p.nomusu, p.emausu 
				FROM usuario AS p 
				INNER JOIN usupef AS f ON p.idusu = f.idusu 
				WHERE f.idper IN (6,7,12) 
				  AND (
				    p.idusu IN (SELECT DISTINCT idusu FROM horario WHERE idfic = :idfic)
				    OR p.idusu IN (SELECT DISTINCT idusu FROM usufic WHERE idfic = :idfic)
				  )
				ORDER BY p.nomusu";
		$result = $conexion->prepare($sql);
		$result->bindParam(":idfic", $idfic);
		$result->execute();
		return $result->fetchAll(PDO::FETCH_ASSOC);
	}

	// Consulta general preservando compatibilidad
	public function getAll(){
		$conexion = $this->getConexionBD();
		$sql = "SELECT h.idhor, h.idfic, h.idaul, a.nomaul, a.codubi, a.idcen, 
				       h.idusu, u.ndocusu, u.nomusu, h.iddia,
				       h.hinihor, h.hfinhor, h.es_otros, h.actividad, h.horas_otros, 
				       h.fecha_inicio, h.fecha_fin, h.es_formacion_directa
				FROM horario AS h 
				INNER JOIN usuario AS u ON h.idusu=u.idusu 
				LEFT JOIN aula AS a ON h.idaul=a.idaul 
				WHERE 1=1";
		
		$params = array();
		if(!empty($this->idfic)){
			$sql .= " AND h.idfic = :idfic";
			$params[':idfic'] = $this->idfic;
		}
		if(!empty($this->iddia)){
			$sql .= " AND h.iddia = :iddia";
			$params[':iddia'] = $this->iddia;
		}
		if(!empty($this->idusu)){
			$sql .= " AND h.idusu = :idusu";
			$params[':idusu'] = $this->idusu;
		}
		if(!empty($this->idaul)){
			$sql .= " AND h.idaul = :idaul";
			$params[':idaul'] = $this->idaul;
		}

		$sql .= " ORDER BY h.iddia, h.hinihor, h.idhor";
		
		$result = $conexion->prepare($sql);
		foreach($params as $key => $val){
			$result->bindValue($key, $val);
		}
		$result->execute();
		return $result->fetchAll(PDO::FETCH_ASSOC);
	}

	// Obtener listado detallado de horarios reales de la BD
	public function getHorariosFiltrados($idusu = null, $idfic = null, $iddia = null, $idaul = null){
		if (empty($idusu) && empty($idfic) && empty($iddia) && empty($idaul)) {
			return array();
		}

		$conexion = $this->getConexionBD();
		$sql = "SELECT h.idhor, h.idfic, h.idaul, h.idusu, h.iddia, h.idnorad,
				       h.hinihor, h.hfinhor, h.es_otros, h.actividad, h.horas_otros,
				       h.fecha_inicio, h.fecha_fin, h.es_transversal, h.nombre_transversal,
				       h.fecha_especifica, h.es_formacion_directa,
				       a.nomaul, a.codubi, a.idcen,
				       u.ndocusu, u.nomusu, u.emausu,
				       f.nomfic, f.codpro, f.jornada,
				       v.nomval AS nomjor, v.nhora AS horajor,
				       p.nompro, ar.nomare,
				       vd.nomval AS nomdia
				FROM horario AS h 
				INNER JOIN usuario AS u ON h.idusu = u.idusu 
				LEFT JOIN aula AS a ON h.idaul = a.idaul 
				LEFT JOIN ficha AS f ON h.idfic = f.idfic 
				LEFT JOIN valor AS v ON f.jornada = v.idval 
				LEFT JOIN programa AS p ON f.codpro = p.codpro 
				LEFT JOIN area AS ar ON p.idare = ar.idare 
				LEFT JOIN valor AS vd ON h.iddia = vd.idval 
				WHERE 1=1";

		$params = array();
		if(!empty($idusu)){
			$sql .= " AND h.idusu = :idusu";
			$params[':idusu'] = $idusu;
		}
		if(!empty($idfic)){
			$sql .= " AND h.idfic = :idfic";
			$params[':idfic'] = $idfic;
		}
		if(!empty($iddia)){
			$sql .= " AND h.iddia = :iddia";
			$params[':iddia'] = $iddia;
		}
		if(!empty($idaul)){
			$sql .= " AND h.idaul = :idaul";
			$params[':idaul'] = $idaul;
		}

		$sql .= " ORDER BY h.iddia ASC, h.hinihor ASC, h.idhor ASC";

		$result = $conexion->prepare($sql);
		foreach($params as $key => $val){
			$result->bindValue($key, $val);
		}
		$result->execute();
		return $result->fetchAll(PDO::FETCH_ASSOC);
	}

	// Obtener un único horario por idhor
	public function getOne(){
		$conexion = $this->getConexionBD();
		$sql = "SELECT h.*, a.nomaul, u.nomusu, u.ndocusu, f.nomfic, 
				       v.nomval AS nomjor, vd.nomval AS nomdia 
				FROM horario AS h 
				LEFT JOIN usuario AS u ON h.idusu = u.idusu 
				LEFT JOIN aula AS a ON h.idaul = a.idaul 
				LEFT JOIN ficha AS f ON h.idfic = f.idfic 
				LEFT JOIN valor AS v ON f.jornada = v.idval 
				LEFT JOIN valor AS vd ON h.iddia = vd.idval 
				WHERE h.idhor = :idhor";
		$result = $conexion->prepare($sql);
		$idhor = $this->getIdhor();
		$result->bindParam(":idhor", $idhor);
		$result->execute();
		return $result->fetchAll(PDO::FETCH_ASSOC);
	}
	
	// Fichas asociadas al instructor en la BD real
	public function getFicha(){
		if (empty($this->idusu)) return array();
		$conexion = $this->getConexionBD();
		$sql = "SELECT DISTINCT f.idfic, f.nomfic, v.nomval, v.nhora, f.codpro, a.nomare 
				FROM ficha AS f 
				LEFT JOIN valor AS v ON f.jornada = v.idval 
				LEFT JOIN programa AS p ON f.codpro = p.codpro 
				LEFT JOIN area AS a ON p.idare = a.idare 
				WHERE (
				    f.idfic IN (SELECT DISTINCT idfic FROM horario WHERE idusu = :idusu)
				    OR f.idfic IN (SELECT DISTINCT idfic FROM usufic WHERE idusu = :idusu)
				)
				ORDER BY f.idfic, f.nomfic";
		$result = $conexion->prepare($sql);
		$idusu = $this->getIdusu();
		$result->bindParam(":idusu", $idusu);
		$result->execute();
		return $result->fetchAll(PDO::FETCH_ASSOC);
	}

	// Todas las fichas activas reales
	public function getAllFichas(){
		$conexion = $this->getConexionBD();
		$sql = "SELECT DISTINCT f.idfic, f.nomfic, v.nomval, v.nhora, f.codpro, a.nomare 
				FROM ficha AS f 
				LEFT JOIN valor AS v ON f.jornada=v.idval 
				LEFT JOIN programa AS p ON f.codpro=p.codpro 
				LEFT JOIN area AS a ON p.idare=a.idare 
				ORDER BY f.idfic, f.nomfic";
		$result = $conexion->prepare($sql);
		$result->execute();
		return $result->fetchAll(PDO::FETCH_ASSOC);
	}

	// Días de la semana reales (iddom=14)
	public function getDia(){
		$conexion = $this->getConexionBD();
		$sql = "SELECT idval, nomval, parval FROM valor WHERE act=1 AND iddom=14 ORDER BY CAST(parval AS UNSIGNED) ASC";
		$result = $conexion->prepare($sql);
		$result->execute();
		return $result->fetchAll(PDO::FETCH_ASSOC);
	}

	// Instructores reales en la BD (perfiles 6, 7, 12)
	public function getInst(){
		$conexion = $this->getConexionBD();
		$sql = "SELECT DISTINCT p.idusu, p.ndocusu, p.nomusu, p.idper, p.emausu, p.actusu 
				FROM usuario AS p 
				INNER JOIN usupef AS f ON p.idusu=f.idusu 
				WHERE f.idper IN (6,7,12) 
				ORDER BY p.nomusu";
		$result = $conexion->prepare($sql);
		$result->execute();
		return $result->fetchAll(PDO::FETCH_ASSOC);
	}

	// Aulas reales en la BD
	public function getAula(){
		$conexion = $this->getConexionBD();
		$sql = "SELECT idaul, nomaul FROM aula ORDER BY idaul";
		$result = $conexion->prepare($sql);
		$result->execute();
		return $result->fetchAll(PDO::FETCH_ASSOC);
	}

	// Verifica si ya existe un horario idéntico (mismo instructor, día y franja horaria)
	public function verificarHorarioDuplicado($idusu, $iddia, $hinihor, $hfinhor, $idhor = NULL){
		if (empty($idusu) || empty($iddia)) return NULL;
		$conexion = $this->getConexionBD();
		$sql = "SELECT idhor, idfic, idusu, iddia, hinihor, hfinhor, es_otros 
				FROM horario 
				WHERE idusu = :idusu AND iddia = :iddia 
				  AND hinihor = :hinihor AND hfinhor = :hfinhor";
		if (!empty($idhor)) {
			$sql .= " AND idhor <> :idhor";
		}
		$sql .= " LIMIT 1";
		$result = $conexion->prepare($sql);
		$result->bindParam(":idusu", $idusu);
		$result->bindParam(":iddia", $iddia);
		$result->bindParam(":hinihor", $hinihor);
		$result->bindParam(":hfinhor", $hfinhor);
		if (!empty($idhor)) {
			$result->bindParam(":idhor", $idhor);
		}
		$result->execute();
		return $result->fetch(PDO::FETCH_ASSOC);
	}

	// Verifica cruce de franjas del mismo instructor en el mismo día (ignora registros de jornada completa tipo 00:00:00)
	public function verificarCruceHorario($idusu, $iddia, $hinihor, $hfinhor, $idhor = NULL){
		if (empty($idusu) || empty($iddia) || empty($hinihor) || empty($hfinhor) 
			|| $hinihor === '00:00:00' || $hfinhor === '00:00:00') return NULL;
		$conexion = $this->getConexionBD();
		$sql = "SELECT idhor, idfic, idusu, iddia, hinihor, hfinhor, es_otros 
				FROM horario 
				WHERE idusu = :idusu AND iddia = :iddia 
				  AND hinihor <> '00:00:00' AND hfinhor <> '00:00:00' 
				  AND :hinihor < hfinhor AND hinihor < :hfinhor";
		if (!empty($idhor)) {
			$sql .= " AND idhor <> :idhor";
		}
		$sql .= " ORDER BY hinihor ASC LIMIT 1";
		$result = $conexion->prepare($sql);
		$result->bindParam(":idusu", $idusu);
		$result->bindParam(":iddia", $iddia);
		$result->bindParam(":hinihor", $hinihor);
		$result->bindParam(":hfinhor", $hfinhor);
		if (!empty($idhor)) {
			$result->bindParam(":idhor", $idhor);
		}
		$result->execute();
		return $result->fetch(PDO::FETCH_ASSOC);
	}

	// Operaciones CRUD
	public function insert(){
		$conexion = $this->getConexionBD();
		$sql = "INSERT INTO horario (idfic, idaul, idusu, iddia, idnorad, hinihor, hfinhor, es_otros, actividad, horas_otros, fecha_inicio, fecha_fin, es_transversal, nombre_transversal, fecha_especifica, es_formacion_directa) 
				VALUES (:idfic, :idaul, :idusu, :iddia, 0, :hinihor, :hfinhor, :es_otros, :actividad, :horas_otros, :fecha_inicio, :fecha_fin, 0, '', :fecha_especifica, :es_formacion_directa)";
		$result = $conexion->prepare($sql);
		
		$idfic = $this->getIdfic();
		$idaul = $this->getIdaul() ? $this->getIdaul() : NULL;
		$idusu = $this->getIdusu();
		$iddia = $this->getIddia();
		$hinihor = $this->getHinihor() ? $this->getHinihor() : '00:00:00';
		$hfinhor = $this->getHfinhor() ? $this->getHfinhor() : '00:00:00';
		$es_otros = $this->getEsOtros() ? 1 : 0;
		$actividad = $this->getActividad() ? $this->getActividad() : '';
		$horas_otros = $this->getHorasOtros() ? $this->getHorasOtros() : 0;
		$fecha_inicio = $this->getFechaInicio() ? $this->getFechaInicio() : NULL;
		$fecha_fin = $this->getFechaFin() ? $this->getFechaFin() : NULL;
		$fecha_especifica = $this->getFechaEspecifica() ? $this->getFechaEspecifica() : NULL;
		$es_formacion_directa = $this->getEsFormacionDirecta() !== NULL ? $this->getEsFormacionDirecta() : 1;

		$result->bindParam(":idfic", $idfic);
		$result->bindParam(":idaul", $idaul);
		$result->bindParam(":idusu", $idusu);
		$result->bindParam(":iddia", $iddia);
		$result->bindParam(":hinihor", $hinihor);
		$result->bindParam(":hfinhor", $hfinhor);
		$result->bindParam(":es_otros", $es_otros);
		$result->bindParam(":actividad", $actividad);
		$result->bindParam(":horas_otros", $horas_otros);
		$result->bindParam(":fecha_inicio", $fecha_inicio);
		$result->bindParam(":fecha_fin", $fecha_fin);
		$result->bindParam(":fecha_especifica", $fecha_especifica);
		$result->bindParam(":es_formacion_directa", $es_formacion_directa);
		
		return $result->execute();
	}

	public function update(){
		$conexion = $this->getConexionBD();
		$sql = "UPDATE horario SET 
				idfic = :idfic, 
				idaul = :idaul, 
				idusu = :idusu, 
				iddia = :iddia, 
				hinihor = :hinihor, 
				hfinhor = :hfinhor, 
				es_otros = :es_otros, 
				actividad = :actividad, 
				horas_otros = :horas_otros, 
				fecha_inicio = :fecha_inicio, 
				fecha_fin = :fecha_fin, 
				es_formacion_directa = :es_formacion_directa 
				WHERE idhor = :idhor";
		$result = $conexion->prepare($sql);
		
		$idhor = $this->getIdhor();
		$idfic = $this->getIdfic();
		$idaul = $this->getIdaul() ? $this->getIdaul() : NULL;
		$idusu = $this->getIdusu();
		$iddia = $this->getIddia();
		$hinihor = $this->getHinihor() ? $this->getHinihor() : '00:00:00';
		$hfinhor = $this->getHfinhor() ? $this->getHfinhor() : '00:00:00';
		$es_otros = $this->getEsOtros() ? 1 : 0;
		$actividad = $this->getActividad() ? $this->getActividad() : '';
		$horas_otros = $this->getHorasOtros() ? $this->getHorasOtros() : 0;
		$fecha_inicio = $this->getFechaInicio() ? $this->getFechaInicio() : NULL;
		$fecha_fin = $this->getFechaFin() ? $this->getFechaFin() : NULL;
		$es_formacion_directa = $this->getEsFormacionDirecta() !== NULL ? $this->getEsFormacionDirecta() : 1;

		$result->bindParam(":idhor", $idhor);
		$result->bindParam(":idfic", $idfic);
		$result->bindParam(":idaul", $idaul);
		$result->bindParam(":idusu", $idusu);
		$result->bindParam(":iddia", $iddia);
		$result->bindParam(":hinihor", $hinihor);
		$result->bindParam(":hfinhor", $hfinhor);
		$result->bindParam(":es_otros", $es_otros);
		$result->bindParam(":actividad", $actividad);
		$result->bindParam(":horas_otros", $horas_otros);
		$result->bindParam(":fecha_inicio", $fecha_inicio);
		$result->bindParam(":fecha_fin", $fecha_fin);
		$result->bindParam(":es_formacion_directa", $es_formacion_directa);
		
		return $result->execute();
	}

	public function delete(){
		$conexion = $this->getConexionBD();
		$sql = "DELETE FROM horario WHERE idhor = :idhor";
		$result = $conexion->prepare($sql);
		$idhor = $this->getIdhor();
		$result->bindParam(":idhor", $idhor);
		return $result->execute();
	}

	// Horas asignadas por ficha y día (para pintar en la tabla de Agenda).
	// Vacío = todas. Retorna filas (idfic, iddia, hinihor, hfinhor, idusu, nomusu).
	public function getHorasPorFichasDias($idfics = []){
		try{
			$conexion = $this->getConexionBD();
			$sql = "SELECT h.idfic, h.iddia, h.hinihor, h.hfinhor, h.idusu, u.nomusu FROM horario AS h LEFT JOIN usuario AS u ON h.idusu=u.idusu WHERE h.hinihor IS NOT NULL AND h.hinihor <> '' AND h.hinihor <> '00:00:00' AND h.hfinhor IS NOT NULL AND h.hfinhor <> '' AND h.hfinhor <> '00:00:00'";
			$params = [];
			if (!empty($idfics)) {
				$ph = [];
				foreach (array_values($idfics) as $i => $idf) {
					$k = ':f' . $i;
					$ph[] = $k;
					$params[$k] = $idf;
				}
				$sql .= " AND idfic IN (" . implode(',', $ph) . ")";
			}
			$sql .= " ORDER BY idfic, iddia, hinihor";
			$result = $conexion->prepare($sql);
			$result->execute($params);
			return $result->fetchAll(PDO::FETCH_ASSOC);
		}catch(Exception $e){
			return [];
		}
	}
}
?>