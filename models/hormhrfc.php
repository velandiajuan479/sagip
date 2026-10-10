<?php 
class Hormhrfc{
	//atributosidhor
	private $idhor;
	private $idfic;
	private $idaul;
	private $idusu;
	private $iddia;
    private $jornada;

	//metodos get
	function getIdhor(){return $this->idhor;}
	function getIdfic(){return $this->idfic;}
	function getIdaul(){return $this->idaul;}
	function getIdusu(){return $this->idusu;}
	function getIddia(){return $this->iddia;}
	
	//metodos SET
	function setIdhor($idhor){$this->idhor =$idhor;}
	function setIdfic($idfic){$this->idfic =$idfic;}
	function setIdaul($idaul){$this->idaul =$idaul;}
	function setIdusu($idusu){$this->idusu =$idusu;}
	function setIddia($iddia){$this->iddia =$iddia;}
    function setJornada($jornada){$this->jornada =$jornada;}
	//metodos publicos
	public function getAll(){
		$res = NULL;
		$sql = "SELECT h.idhor, f.idfic, f.jornada, a.idaul AS ai, u.idusu AS ui, u.nomusu AS un, h.iddia
		FROM horario AS h
		INNER JOIN ficha AS f ON h.idfic=f.idfic
		INNER JOIN aula AS a ON h.idaul=a.idaul
		INNER JOIN usuario AS u ON h.idusu=u.idusu";
		$modelo = new conexion();
		$conexion = $modelo->get_conexion();
		$result = $conexion->prepare($sql);
		$result->execute();
		$res = $result->fetchall(PDO::FETCH_ASSOC);
		return $res;
	}

    public function getAllaul(){
		$res = NULL;
		$sql = "SELECT * FROM aula";
		$modelo = new conexion();
		$conexion = $modelo->get_conexion();
		$result = $conexion->prepare($sql);
		$result->execute();
		$res = $result->fetchall(PDO::FETCH_ASSOC);
		return $res;
	}

	public function getOne(){
		$res = NULL;
		$sql = "SELECT h.idhor, f.idfic, f.nomfic AS nf, f.jornada, v.nomval, a.idaul AS ai, a.nomaul AS an, ub.codubi, ub.nomubi, u.idusu AS ui, u.nomusu AS un, h.iddia, h.es_otros, h.actividad, h.horas_otros
		FROM horario AS h
		INNER JOIN ficha AS f ON h.idfic=f.idfic
        INNER JOIN valor AS v ON f.jornada=v.idval
		INNER JOIN aula AS a ON h.idaul=a.idaul
		INNER JOIN ubica AS ub ON a.codubi=ub.codubi
		INNER JOIN usuario AS u ON h.idusu=u.idusu
		WHERE h.idfic=:idfic";
		$modelo = new conexion();
		$conexion = $modelo->get_conexion();
		$result = $conexion->prepare($sql);
		$idfic = $this->getIdfic();
		$result->bindParam(":idfic", $idfic);
		$result->execute();
		$res= $result->fetchall(PDO::FETCH_ASSOC);
		return $res;
	}

	/**
	 * Obtener municipio (columna mun) desde la tabla ficha por idfic
	 */
	public function getMunicipioPorFicha($idfic)
	{
		try {
			$sql = "SELECT mun FROM ficha WHERE idfic = :idfic LIMIT 1";
			$modelo = new conexion();
			$conexion = $modelo->get_conexion();
			$result = $conexion->prepare($sql);
			$result->bindParam(":idfic", $idfic);
			$result->execute();
			$row = $result->fetch(PDO::FETCH_ASSOC);
		} catch (Exception $e) {
			error_log('Mrphfc::getMunicipioPorFicha error: ' . $e->getMessage());
			return '';
		}
	}

	/**
	 * Obtener horarios de una ficha dentro de un rango de fechas
	 */
	public function getHorariosFichaEnRango($idfic, $fechaInicio, $fechaFin)
	{
		try {
			$sql = "SELECT 
						h.iddia,
						u.nomusu AS un,
						a.idaul AS ai,
						a.nomaul AS an,
						h.es_otros,
						h.actividad,
						h.horas_otros,
						h.fecha_especifica,
						h.fecha_inicio,
						h.fecha_fin
				FROM horario h
				LEFT JOIN usuario u ON h.idusu = u.idusu
				LEFT JOIN aula a ON h.idaul = a.idaul
				WHERE h.idfic = :idfic
				  AND (
						(h.fecha_especifica IS NOT NULL AND h.fecha_especifica BETWEEN :ini AND :fin)
					 OR (
						h.fecha_especifica IS NULL 
						AND h.fecha_inicio IS NOT NULL AND h.fecha_fin IS NOT NULL
						AND h.fecha_inicio <= :fin AND h.fecha_fin >= :ini
					 )
				  )
				ORDER BY h.iddia ASC, h.fecha_especifica ASC";
			$modelo = new conexion();
			$conexion = $modelo->get_conexion();
			$result = $conexion->prepare($sql);
			$result->bindParam(":idfic", $idfic);
			$result->bindParam(":ini", $fechaInicio);
			$result->bindParam(":fin", $fechaFin);
			$result->execute();
			return $result->fetchAll(PDO::FETCH_ASSOC);
		} catch (Exception $e) {
			error_log('Mrphfc::getHorariosFichaEnRango error: ' . $e->getMessage());
			return [];
		}
	}
}
?>