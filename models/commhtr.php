<?php
require_once("conexion.php");

class Mhdt{
	//Atributos
	private $idnorad;
	private $codpro;
	private $idemp;
	private $codproesp;
	private $codslem;
	private $feclini;
	private $feclin;
	private $cupo;
	private $jornada;
	private $idfic;
	private $convht;
	private $act;

	public function __construct($con = null) {
		
	}

//Getters
	function getIdnorad(){
		return $this->idnorad;
	}
	function getCodpro(){
		return $this->codpro;
	}
	function getIdemp(){
		return $this->idemp;
	}
	function getCodproesp(){
		return $this->codproesp;
	}
	function getCodslem(){
		return $this->codslem;
	}
	function getFeclini(){
		return $this->feclini;
	}
	function getFeclin(){
		return $this->feclin;
	}
	function getCupo(){
		return $this->cupo;
	}
	function getJornada(){
		return $this->jornada;
	}
	function getIdfic(){
		return $this->idfic;
	}
	public function getConvht() {
        return $this->convht;
    }
	function getAct(){
		return $this->act;
	}

//Setters
	function setIdnorad($idnorad){
		$this->idnorad = $idnorad;
	}
	function setCodpro($codpro){
		$this->codpro = $codpro;
	}
	function setIdemp($idemp){
		$this->idemp = $idemp;
	}
	function setCodproesp($codproesp){
		$this->codproesp = $codproesp;
	}
	function setCodslem($codslem){
		$this->codslem = $codslem;
	}
	function setFeclini($feclini){
		$this->feclini = $feclini;
	}
	function setFeclin($feclin){
		$this->feclin = $feclin;
	}
	function setCupo($cupo){
		$this->cupo = $cupo;
	}
	function setJornada($jornada){
		$this->jornada = $jornada;
	}
	function setIdfic($idfic){
		$this->idfic = $idfic;
	}
	public function setConvht($convht) {
        $this->convht = $convht;
        return $this; 
    }
	function setAct($act){
		$this->act = $act;
	}

//Métodos
	public function getAll(){
	    try {
	        $sql = "SELECT h.idnorad, h.codpro, p.nompro, h.idemp, e.nomemp, h.codproesp, s.nomval AS proesp, h.codslem, h.feclini, h.feclin, h.cupo, h.jornada, j.nomval AS jorn, h.idfic, h.convht, h.act 
			        FROM hojatra AS h  
					INNER JOIN programa AS p ON h.codpro=p.codpro  
					INNER JOIN empresa AS e ON h.idemp=e.idemp 
					INNER JOIN valor AS s ON h.codproesp=s.idval 
					INNER JOIN valor AS j ON h.jornada=j.idval 
					ORDER BY h.idnorad DESC";        
	        $modelo = new conexion();
	        $conexion = $modelo->get_conexion();
	        $result = $conexion->prepare($sql);
	        $result->execute();
	        $res = $result->fetchall(PDO::FETCH_ASSOC);
	        return $res;
	    } catch (Exception $e) {
	        if(function_exists('ManejoError')) {
	            ManejoError($e);
	        } else {
	            echo '<script>alert("Error al obtener datos");</script>';
	        }
	        return [];
	    }
	}

	public function getOne(){
		try {
	    	$sql = "SELECT h.idnorad, h.codpro, p.nompro, h.idemp, e.nomemp, h.codproesp, s.nomval AS proesp, h.codslem, h.feclini, h.feclin, h.cupo, h.jornada, j.nomval AS jorn, h.idfic, h.convht, h.act 
			        FROM hojatra AS h  
					INNER JOIN programa AS p ON h.codpro=p.codpro  
					INNER JOIN empresa AS e ON h.idemp=e.idemp 
					INNER JOIN valor AS s ON h.codproesp=s.idval 
					INNER JOIN valor AS j ON h.jornada=j.idval 
					WHERE h.idnorad=:idnorad";
			$modelo = new conexion();
			$conexion = $modelo->get_conexion();
			$result = $conexion->prepare($sql);
			$idnorad = $this->getIdnorad();
			$result->bindParam(':idnorad',$idnorad);
			$result->execute();
			$res = $result->fetchall(PDO::FETCH_ASSOC);
			return $res;
		} catch (Exception $e) {
	        if(function_exists('ManejoError')) {
	            ManejoError($e);
	        } else {
	            echo '<script>alert("Error al obtener registro");</script>';
	        }
	        return [];
	    }
	}

	public function getFechas(){
		try {
	    	$sql = "SELECT idnorad, feclini, feclin FROM hojatra WHERE idnorad=:idnorad";
			$modelo = new conexion();
			$conexion = $modelo->get_conexion();
			$result = $conexion->prepare($sql);
			$idnorad = $this->getIdnorad();
			$result->bindParam(':idnorad',$idnorad);
			$result->execute();
			$res = $result->fetchall(PDO::FETCH_ASSOC);
			return $res;
		} catch (Exception $e) {
	        if(function_exists('ManejoError')) {
	            ManejoError($e);
	        }
	        return [];
	    }
	}

	public function getOneLast(){
		try {
	    	$sql = "SELECT idnorad FROM hojatra WHERE codpro=:codpro AND 
            idemp=:idemp AND codproesp=:codproesp AND feclini=:feclini AND feclin=:feclin AND cupo=:cupo AND jornada=:jornada ORDER BY idnorad DESC LIMIT 1";
			$modelo = new conexion();
			$conexion = $modelo->get_conexion();
			$result = $conexion->prepare($sql);
			$codpro = $this->getCodpro();
			$result->bindParam(':codpro',$codpro);
			$idemp = $this->getIdemp();
			$result->bindParam(':idemp',$idemp);
			$codproesp = $this->getCodproesp();
			$result->bindParam(':codproesp',$codproesp);
			$feclini = $this->getFeclini();
			$result->bindParam(':feclini',$feclini);
			$feclin = $this->getFeclin();
			$result->bindParam(':feclin',$feclin);
			$cupo = $this->getCupo();
			$result->bindParam(':cupo',$cupo);
			$jornada = $this->getJornada();
			$result->bindParam(':jornada',$jornada);
			$result->execute();
			$res = $result->fetchall(PDO::FETCH_ASSOC);
			return $res;
		} catch (Exception $e) {
	        if(function_exists('ManejoError')) {
	            ManejoError($e);
	        } else {
	            echo '<script>alert("Error al obtener último registro");</script>';
	        }
	        return [];
	    }
	}

public function save(){
    try {
        $sql = "INSERT INTO hojatra (codpro, idemp, codproesp, codslem, feclini, feclin, cupo, jornada, idfic, convht, act)
        VALUES (:codpro, :idemp, :codproesp, :codslem, :feclini, :feclin, :cupo, :jornada, :idfic, :convht, :act)";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        
        $codpro = $this->getCodpro();
        $result->bindParam(':codpro',$codpro);
        $idemp = $this->getIdemp();
        $result->bindParam(':idemp',$idemp);
        $codproesp = $this->getCodproesp();
        $result->bindParam(':codproesp',$codproesp);
        $codslem = $this->getCodslem();
        $result->bindParam(':codslem',$codslem);
        $feclini = $this->getFeclini();
        $result->bindParam(':feclini',$feclini);
        $feclin = $this->getFeclin();
        $result->bindParam(':feclin',$feclin);
        $cupo = $this->getCupo();
        $result->bindParam(':cupo',$cupo);
        $jornada = $this->getJornada();
        $result->bindParam(':jornada',$jornada);
        $idfic = $this->getIdfic();
        $result->bindParam(':idfic',$idfic);
        $convht = $this->getConvht();
        $result->bindParam(':convht',$convht);
        $act = $this->getAct() ? $this->getAct() : 1;
        $result->bindParam(':act',$act);
        
        $result->execute();
        
        // Get the last inserted ID
        $lastId = $conexion->lastInsertId();
        
        return $lastId;
    } catch (Exception $e) {
        if(function_exists('ManejoError')) {
            ManejoError($e);
        } else {
            echo '<script>alert("Error al guardar registro: ' . addslashes($e->getMessage()) . '");</script>';
        }
        return false;
    }
}

	public function saveHxU(){
		try {
			$idusu = isset($_SESSION["idusu"]) ? $_SESSION["idusu"] : null;
			if(!$idusu) {
				return;
			}
			$sql = "INSERT INTO hdtxusu (idnorad, idusu) 
	            VALUES (:idnorad, :idusu)";
			$modelo = new conexion();
			$conexion = $modelo->get_conexion();
			$result = $conexion->prepare($sql);
			$idnorad = $this->getIdnorad();
			$result->bindParam(':idnorad',$idnorad);
			$result->bindParam(':idusu',$idusu);
	    	$result->execute();
	    } catch (Exception $e) {
	        if(function_exists('ManejoError')) {
	            ManejoError($e);
	        }
	    }
	}

	public function edit(){
		try {
			$sql = "UPDATE hojatra SET 
	            codpro=:codpro, 
	            idemp=:idemp, 
	            codproesp=:codproesp, 
	            codslem=:codslem, 
	            feclini=:feclini, 
	            feclin=:feclin, 
	            cupo=:cupo, 
	            jornada=:jornada, 
	            idfic=:idfic, 
	            convht=:convht 
	            WHERE idnorad=:idnorad";
			$modelo = new conexion();
			$conexion = $modelo->get_conexion();
			$result = $conexion->prepare($sql);
			$idnorad = $this->getIdnorad();
			$result->bindParam(':idnorad',$idnorad);
			$codpro = $this->getCodpro();
			$result->bindParam(':codpro',$codpro);
			$idemp = $this->getIdemp();
			$result->bindParam(':idemp',$idemp);
			$codproesp = $this->getCodproesp();
			$result->bindParam(':codproesp',$codproesp);
			$codslem = $this->getCodslem();
			$result->bindParam(':codslem',$codslem);
			$feclini = $this->getFeclini();
			$result->bindParam(':feclini',$feclini);
			$feclin = $this->getFeclin();
			$result->bindParam(':feclin',$feclin);
			$cupo = $this->getCupo();
			$result->bindParam(':cupo',$cupo);
			$jornada = $this->getJornada();
			$result->bindParam(':jornada',$jornada);
			$idfic = $this->getIdfic();
			$result->bindParam(':idfic',$idfic);
			$convht = $this->getConvht();
	    	$result->bindParam(':convht', $convht);
			$result->execute();
		} catch (Exception $e) {
	        if(function_exists('ManejoError')) {
	            ManejoError($e);
	        } else {
	            echo '<script>alert("Error al actualizar registro");</script>';
	        }
	    }
	}

	public function editAct(){
		try {
			$sql = "UPDATE hojatra SET act=:act WHERE idnorad=:idnorad";
			$modelo = new conexion();
			$conexion = $modelo->get_conexion();
			$result = $conexion->prepare($sql);
			$idnorad = $this->getIdnorad();
			$result->bindParam(':idnorad',$idnorad);
			$act = $this->getAct();
			$result->bindParam(':act',$act);
			$result->execute();
		} catch (Exception $e) {
	        if(function_exists('ManejoError')) {
	            ManejoError($e);
	        }
	    }
	}

	public function del(){
		try {
			$modelo = new conexion();
			$conexion = $modelo->get_conexion();
			$idnorad = $this->getIdnorad();

			// 1. Eliminar horarios asociados
			$sqlHor = "DELETE FROM horario WHERE idnorad=:idnorad";
			$stmtHor = $conexion->prepare($sqlHor);
			$stmtHor->bindParam(':idnorad',$idnorad);
			$stmtHor->execute();

			// 2. Eliminar asociación usuario en hdtxusu
			$sqlUs = "DELETE FROM hdtxusu WHERE idnorad=:idnorad";
			$stmtUs = $conexion->prepare($sqlUs);
			$stmtUs->bindParam(':idnorad',$idnorad);
			$stmtUs->execute();
			
			// 3. Eliminar la hoja de trabajo
			$sqlHt = "DELETE FROM hojatra WHERE idnorad=:idnorad";
			$stmtHt = $conexion->prepare($sqlHt);
			$stmtHt->bindParam(':idnorad',$idnorad);
			$stmtHt->execute();
		} catch (Exception $e) {
	        if(function_exists('ManejoError')) {
	            ManejoError($e);
	        } else {
	            echo '<script>alert("Error al eliminar registro");</script>';
	        }
	    }
	}

	public function getAllPro(){
		try {
			$sql = "SELECT * FROM programa WHERE tippro = 1083 ORDER BY codpro ASC, nompro ASC";
			$modelo = new conexion();
			$conexion = $modelo->get_conexion();
			$result = $conexion->prepare($sql);
			$result->execute();
			$res = $result->fetchall(PDO::FETCH_ASSOC);
			return $res;
		} catch (Exception $e) {
	        if(function_exists('ManejoError')) {
	            ManejoError($e);
	        } else {
	            echo '<script>alert("Error al obtener programas");</script>';
	        }
	        return [];
	    }
	}

	public function getAllFic(){
		try {
			$sql = "SELECT * FROM ficha ORDER BY idfic DESC";
			$modelo = new conexion();
			$conexion = $modelo->get_conexion();
			$result = $conexion->prepare($sql);
			$result->execute();
			$res = $result->fetchall(PDO::FETCH_ASSOC);
			return $res;
		} catch (Exception $e) {
	        if(function_exists('ManejoError')) {
	            ManejoError($e);
	        } else {
	            echo '<script>alert("Error al obtener fichas");</script>';
	        }
	        return [];
	    }
	}

	public function getAllEmp(){
		try {
			$sql = "SELECT idemp, nomemp FROM empresa ORDER BY nomemp ASC";
			$modelo = new conexion();
			$conexion = $modelo->get_conexion();
			$result = $conexion->prepare($sql);
			$result->execute();
			$res = $result->fetchall(PDO::FETCH_ASSOC);
			return $res;
		} catch (Exception $e) {
	        if(function_exists('ManejoError')) {
	            ManejoError($e);
	        } else {
	            echo '<script>alert("Error al obtener empresas");</script>';
	        }
	        return [];
	    }
	}

	public function getAllVal($iddom){
		try {
			$sql = "SELECT idval, nomval FROM valor WHERE act=1 AND iddom=:iddom
				ORDER BY nomval";
			$modelo = new conexion();
			$conexion = $modelo->get_conexion();
			$result = $conexion->prepare($sql);
			$result->bindParam(':iddom',$iddom);
			$result->execute();
			$res = $result->fetchall(PDO::FETCH_ASSOC);
			return $res;
		} catch (Exception $e) {
	        if(function_exists('ManejoError')) {
	            ManejoError($e);
	        } else {
	            echo '<script>alert("Error al obtener valores");</script>';
	        }
	        return [];
	    }
	}

	private $tieneAct = null;

	// Detecta si hojatra tiene la columna "act" (no existe en el dump; sí en algunas BD)
	private function tieneAct(){
		if ($this->tieneAct === null) {
			$this->tieneAct = 0;
			try {
				$modelo   = new conexion();
				$conexion = $modelo->get_conexion();
				$q = $conexion->query("SHOW COLUMNS FROM hojatra");
				if ($q !== false) {
					$cols = $q->fetchAll(PDO::FETCH_COLUMN);
					$this->tieneAct = in_array('act', $cols) ? 1 : 0;
				}
			} catch (Exception $e) {
				$this->tieneAct = 0;
			}
		}
		return $this->tieneAct === 1;
	}

	// SELECT base con todos los campos que consume la vista/exportación (LEFT JOIN para no descartar filas)
	private function qBase(){
		$act = $this->tieneAct() ? 'h.act, ' : '';
		return "SELECT {$act}
				h.idnorad, h.codpro, p.nompro, p.horlpro,
				h.idemp, e.nomemp, e.numdocemp, e.diremp, e.nomconemp, e.telemp, u.nomubi AS nommun,
				h.codproesp, s.nomval AS proesp,
				h.codslem, h.feclini, h.feclin, h.cupo,
				h.jornada, j.nomval AS jorn,
				h.idfic, f.nomfic,
				h.convht
				FROM hojatra AS h
				LEFT JOIN programa AS p ON h.codpro = p.codpro
				LEFT JOIN empresa AS e ON h.idemp = e.idemp
				LEFT JOIN ubica   AS u ON e.codubi = u.codubi
				LEFT JOIN valor   AS s ON h.codproesp = s.idval
				LEFT JOIN valor   AS j ON h.jornada = j.idval
				LEFT JOIN ficha   AS f ON h.idfic = f.idfic";
	}

	// ─────────────────────────────────────────────────────────────────────────
	// Horarios del día por hoja de trabajo -> matriz semanal keyed por día
	// (valor.iddom=14 tiene Lunes..Domingo en idval 1042..1048, parval 1..7)
	// ─────────────────────────────────────────────────────────────────────────
	public function getDiasHorario($idnorad){
		$dias = [];
		try {
			if (!$idnorad) return $dias;

			$orden = ['LUNES', 'MARTES', 'MIÉRCOLES', 'JUEVES', 'VIERNES', 'SÁBADO', 'DOMINGO'];

			$sql = "SELECT v.parval AS par, h.hinihor, h.hfinhor
					FROM horario AS h
					LEFT JOIN valor AS v ON h.iddia = v.idval
					WHERE h.idnorad = :idnorad
					  AND h.hinihor IS NOT NULL AND h.hfinhor IS NOT NULL
					ORDER BY v.parval ASC";
			$modelo = new conexion();
			$conexion = $modelo->get_conexion();
			$result = $conexion->prepare($sql);
			$result->bindParam(':idnorad',$idnorad);
			$result->execute();
			$res = $result->fetchall(PDO::FETCH_ASSOC);

			$tmp = [];
			foreach ($res as $r) {
				$hi = trim($r['hinihor'] ?? '');
				$hf = trim($r['hfinhor'] ?? '');
				if ($hi === '' || $hf === '' || ($hi === '00:00:00' && $hf === '00:00:00')) continue;

				// Mapeo por parval (1..7) -> Lunes..Domingo (evita problemas de codificación de nomval)
				$idx = (int)($r['par'] ?? 0) - 1;
				if ($idx < 0 || $idx > 6) continue;

				$tmp[$orden[$idx]][] = ['inicio' => substr($hi,0,5), 'fin' => substr($hf,0,5)];
			}

			foreach ($tmp as $k => $sesiones) {
				$ini = $sesiones[0]['inicio'];
				$fin = $sesiones[0]['fin'];
				foreach ($sesiones as $s) {
					$ini = ($s['inicio'] < $ini) ? $s['inicio'] : $ini;
					$fin = ($s['fin'] > $fin)   ? $s['fin']   : $fin;
				}
				$dias[$k] = ['inicio' => $ini, 'fin' => $fin];
			}
		} catch (Exception $e) {
	        if(function_exists('ManejoError')) {
	            ManejoError($e);
	        } else {
	            echo '<script>alert("Error al obtener horarios");</script>';
	        }
	    }
		return $dias;
	}

	// ─────────────────────────────────────────────────────────────────────────
	// Instructor asignado: el usuario con más registros de horario en la hoja
	// ─────────────────────────────────────────────────────────────────────────
	public function getInstructor($idnorad){
		try {
			if (!$idnorad) return [];

			$sql = "SELECT u.idusu, u.nomusu, u.ndocusu, u.telcan, u.emausu, COUNT(*) AS n
					FROM horario AS h
					INNER JOIN usuario AS u ON h.idusu = u.idusu
					WHERE h.idnorad = :idnorad
					GROUP BY u.idusu, u.nomusu, u.ndocusu, u.telcan, u.emausu
					ORDER BY n DESC, u.idusu ASC
					LIMIT 1";
			$modelo = new conexion();
			$conexion = $modelo->get_conexion();
			$result = $conexion->prepare($sql);
			$result->bindParam(':idnorad',$idnorad);
			$result->execute();
			$res = $result->fetchall(PDO::FETCH_ASSOC);
			return !empty($res) ? $res[0] : [];
		} catch (Exception $e) {
	        if(function_exists('ManejoError')) {
	            ManejoError($e);
	        }
	        return [];
	    }
	}

	// ─────────────────────────────────────────────────────────────────────────
	// Reporte completo para la vista/exportación: mapea la fila de getOne()
	// + instructor + matriz de horarios.
	// ─────────────────────────────────────────────────────────────────────────
	public function reporte($idnorad){
		$out = ['datos' => [], 'dias' => []];
		$id = (int)$idnorad;
		if ($id <= 0) return $out;

		$this->setIdnorad($id);
		$raw = $this->getOne();
		if (empty($raw)) {
			// Modo demostración: los ids reservados 900001..900006 no existen en BD
			if ($this->esDemo($id)) {
				return $this->datosDemoUno($id);
			}
			return $out;
		}

		$res = $raw[0];
		$datos = [
			'idnorad'      => $res['idnorad'] ?? null,
			'codpro'       => $res['codpro'] ?? '',
			'idemp'        => $res['idemp'] ?? '',
			'codproesp'    => !empty($res['proesp']) ? $res['proesp'] : ($res['codproesp'] ?? ''),
			'codslem'      => $res['codslem'] ?? '',
			'feclini'      => $res['feclini'] ?? '',
			'feclin'       => $res['feclin'] ?? '',
			'fecha_inicio' => $res['feclini'] ?? '',
			'fecha_fin'    => $res['feclin'] ?? '',
			'cupo'         => $res['cupo'] ?? 0,
			'jornada'      => !empty($res['jorn']) ? $res['jorn'] : ($res['jornada'] ?? ''),
			'codigo_ficha' => $res['idfic'] ?? '',
			'idfic'        => $res['idfic'] ?? '',
			'nomfic'       => $res['nomfic'] ?? '',
			'nompro'       => $res['nompro'] ?? '',
			'nomemp'       => $res['nomemp'] ?? '',
			'numdocemp'    => $res['numdocemp'] ?? '',
			'convenio'     => $res['convht'] ?? '',
			'convht'       => $res['convht'] ?? '',
			'duracion'     => !empty($res['horlpro']) ? ($res['horlpro'] . ' horas') : '',
			'direccion'    => $res['diremp'] ?? '',
			'municipio'    => $res['nommun'] ?? '',
			'contacto'     => $res['nomconemp'] ?? '',
			'telefono'     => $res['telemp'] ?? '',
		];

		// Instructor asignado (desde horario → usuario)
		$inst = $this->getInstructor($id);
		if (!empty($inst)) {
			$datos['nombre_instructor']    = $inst['nomusu']  ?? '';
			$datos['documento_instructor'] = $inst['ndocusu'] ?? '';
			$datos['cel_instructor']       = $inst['telcan']  ?? '';
			$datos['correo_instructor']    = $inst['emausu']  ?? '';
		}

		$out['datos'] = $datos;
		$out['dias']  = $this->getDiasHorario($id);
		return $out;
	}

	// ─────────────────────────────────────────────────────────────────────────
	// MODO DEMOSTRACIÓN (sin tocar la BD)
	// Cuando la tabla hojatra está vacía, la vista puede ofrecer hojas de
	// trabajo de ejemplo (ids 900001..900006) para probar 'Cargar reporte',
	// la impresión en PDF/Excel y el dashboard. Esos ids no existen en la BD
	// y reporte() los resuelve en memoria, por lo que el endpoint de
	// exportación (comvhtr.php) los genera sin cambios.
	// ─────────────────────────────────────────────────────────────────────────
	public function esDemo($id){
		$id = (int)$id;
		return ($id >= 900001 && $id <= 900006);
	}

	public function esHojatraVacia(){
		try {
			$modelo = new conexion();
			$c = $modelo->get_conexion();
			$q = $c->query("SELECT COUNT(*) AS n FROM hojatra");
			$r = $q->fetch(PDO::FETCH_ASSOC);
			return (((int)($r['n'] ?? 1)) === 0);
		} catch (Exception $e) {
			return false;
		}
	}

	// Base de datos de ejemplo: datos realistas referenciando programas reales,
	// jornadas reales (valor dom 1: Mañana/Tarde/Noche; 1083 Complementaria) y
	// días reales (valor dom 14: 1042 Lunes .. 1048 Domingo).
	private function demoDatos(){
		$hoy = time();
		$f   = function($m, $d = 0) use ($hoy) {
			return date('Y-m-d', strtotime(($m >= 0 ? "+{$m} months" : "{$m} months") . " {$d} days", $hoy));
		};

		$base = [
			900001 => [
				'codpro'=>121523, 'nompro'=>'COORDINACION DE PROCESOS LOGISTICOS', 'horlpro'=>3120,
				'idemp'=>1, 'nomemp'=>'Agroindustrias Chía SAS', 'numdocemp'=>'900301245-1',
				'diremp'=>'Km 3 Vía Chía – Cajicá, Zona Industrial', 'nommun'=>'Chía',
				'nomconemp'=>'Diana Forero', 'telemp'=>'3115550101',
				'codproesp'=>'', 'proesp'=>'', 'codslem'=>'SD-2026-001',
				'feclini'=>$f(-2), 'feclin'=>$f(4), 'cupo'=>30, 'jornada'=>1, 'jorn'=>'Mañana',
				'idfic'=>'90671001', 'nomfic'=>'COORDINADOR DE PROCESOS LOGISTICOS (90671001)',
				'convht'=>'CONV-CDA-2026-001',
				'instructor'=>['nombre_instructor'=>'Carlos Andrés Pardo','documento_instructor'=>'1023945678','cel_instructor'=>'3105550101','correo_instructor'=>'capardo@sena.edu.co'],
				'dias'=>['LUNES'=>['06:00','12:00'],'MIÉRCOLES'=>['06:00','12:00'],'VIERNES'=>['06:00','12:00']],
			],
			900002 => [
				'codpro'=>228118, 'nompro'=>'ANALISIS Y DESARROLLO DE SOFTWARE', 'horlpro'=>3120,
				'idemp'=>1, 'nomemp'=>'Tecnología Aplicada S.A.S.', 'numdocemp'=>'900554871-2',
				'diremp'=>'Cll 26 # 12-34, Bogotá D.C.', 'nommun'=>'Bogotá D.C.',
				'nomconemp'=>'Fabián Rueda', 'telemp'=>'3205552020',
				'codproesp'=>'', 'proesp'=>'', 'codslem'=>'SD-2026-002',
				'feclini'=>$f(-5), 'feclin'=>$f(-1), 'cupo'=>25, 'jornada'=>3, 'jorn'=>'Noche',
				'idfic'=>'90671002', 'nomfic'=>'ANALISIS Y DESARROLLO DE SOFTWARE (90671002)',
				'convht'=>'CONV-CDA-2025-018',
				'instructor'=>['nombre_instructor'=>'Lina Marcela Torres','documento_instructor'=>'1018462176','cel_instructor'=>'3005552020','correo_instructor'=>'lmtorres@sena.edu.co'],
				'dias'=>['LUNES'=>['18:00','22:00'],'MARTES'=>['18:00','22:00'],'JUEVES'=>['18:00','22:00']],
			],
			900003 => [
				'codpro'=>13530036, 'nompro'=>'MANEJO BÁSICO DE HERRAMIENTAS OFIMATICAS I', 'horlpro'=>50,
				'idemp'=>1, 'nomemp'=>'Frutas del Campo LTDA', 'numdocemp'=>'900123456-5',
				'diremp'=>'Vereda Centro, Cajicá', 'nommun'=>'Cajicá',
				'nomconemp'=>'Hernán Villalba', 'telemp'=>'3125553030',
				'codproesp'=>'', 'proesp'=>'', 'codslem'=>'SD-2026-003',
				'feclini'=>$f(0, 3), 'feclin'=>$f(2), 'cupo'=>40, 'jornada'=>1083, 'jorn'=>'Complementaria',
				'idfic'=>'90671003', 'nomfic'=>'COMPLEMENTARIA OFIMATICA I (90671003)',
				'convht'=>'',
				'instructor'=>['nombre_instructor'=>'Martha Lucía Gómez','documento_instructor'=>'51738921','cel_instructor'=>'3155553030','correo_instructor'=>'mlgomez@sena.edu.co'],
				'dias'=>['SÁBADO'=>['06:00','14:00'],'DOMINGO'=>['06:00','12:00']],
			],
			900004 => [
				'codpro'=>121523, 'nompro'=>'COORDINACION DE PROCESOS LOGISTICOS', 'horlpro'=>3120,
				'idemp'=>1, 'nomemp'=>'Servicios Turísticos Cundinamarca', 'numdocemp'=>'901234567-4',
				'diremp'=>'Carrera 7 # 5-20, Zipaquirá', 'nommun'=>'Zipaquirá',
				'nomconemp'=>'Paola Montaño', 'telemp'=>'3225554040',
				'codproesp'=>'', 'proesp'=>'', 'codslem'=>'SD-2026-004',
				'feclini'=>$f(-1), 'feclin'=>$f(3), 'cupo'=>35, 'jornada'=>2, 'jorn'=>'Tarde',
				'idfic'=>'90671004', 'nomfic'=>'COORDINACION PROCESOS LOGISTICOS TARDE (90671004)',
				'convht'=>'CONV-CDA-2026-002',
				'instructor'=>['nombre_instructor'=>'Julián David Cárdenas','documento_instructor'=>'1019756011','cel_instructor'=>'3015554040','correo_instructor'=>'jdcardenas@sena.edu.co'],
				'dias'=>['MARTES'=>['06:00','10:00'],'JUEVES'=>['06:00','10:00'],'SÁBADO'=>['08:00','12:00']],
			],
			900005 => [
				'codpro'=>13530036, 'nompro'=>'MANEJO BÁSICO DE HERRAMIENTAS OFIMATICAS I', 'horlpro'=>50,
				'idemp'=>1, 'nomemp'=>'Transformados Agrícolas del Norte', 'numdocemp'=>'900987654-3',
				'diremp'=>'Vía Tenjo – Subachoque km 7', 'nommun'=>'Cota',
				'nomconemp'=>'Rosalba Pineda', 'telemp'=>'3195555050',
				'codproesp'=>'', 'proesp'=>'', 'codslem'=>'SD-2025-011',
				'feclini'=>$f(-4), 'feclin'=>$f(-2), 'cupo'=>28, 'jornada'=>1, 'jorn'=>'Mañana',
				'idfic'=>'90671005', 'nomfic'=>'COMPLEMENTARIA OFIMATICA I COTA (90671005)',
				'convht'=>'',
				'instructor'=>['nombre_instructor'=>'Oscar Iván Suárez','documento_instructor'=>'934431122','cel_instructor'=>'3135555050','correo_instructor'=>'oisuarez@sena.edu.co'],
				'dias'=>['LUNES'=>['06:00','12:00'],'MIÉRCOLES'=>['06:00','12:00'],'VIERNES'=>['06:00','12:00']],
			],
			900006 => [
				'codpro'=>228118, 'nompro'=>'ANALISIS Y DESARROLLO DE SOFTWARE', 'horlpro'=>3120,
				'idemp'=>1, 'nomemp'=>'Logística Colombiana SAS', 'numdocemp'=>'902345678-6',
				'diremp'=>'Zona Franca Occidente, Tenjo', 'nommun'=>'Tenjo',
				'nomconemp'=>'Carmenza Duarte', 'telemp'=>'3165556060',
				'codproesp'=>'', 'proesp'=>'', 'codslem'=>'SD-2024-016',
				'feclini'=>$f(-6), 'feclin'=>$f(6), 'cupo'=>45, 'jornada'=>2, 'jorn'=>'Tarde',
				'idfic'=>'90671006', 'nomfic'=>'DESARROLLO DE SOFTWARE VESPERTINO (90671006)',
				'convht'=>'CONV-CDA-2024-009',
				'instructor'=>['nombre_instructor'=>'Mary Luz Espitia','documento_instructor'=>'1025514887','cel_instructor'=>'3025556060','correo_instructor'=>'mespitia@sena.edu.co'],
				'dias'=>['LUNES'=>['06:00','12:00'],'MARTES'=>['13:00','17:00'],'JUEVES'=>['06:00','12:00'],'VIERNES'=>['13:00','17:00']],
			],
		];
		return $base;
	}

	// Listado demo con el mismo formato de fila que getAll()
	public function getAllDemo(){
		$filas = [];
		foreach ($this->demoDatos() as $id => $r) {
			$filas[] = [
				'idnorad'   => $id,     'codpro'    => $r['codpro'], 'nompro' => $r['nompro'],
				'horlpro'   => $r['horlpro'],
				'idemp'     => $r['idemp'], 'nomemp'  => $r['nomemp'], 'numdocemp' => $r['numdocemp'],
				'diremp'    => $r['diremp'], 'nomconemp' => $r['nomconemp'], 'telemp' => $r['telemp'],
				'nommun'    => $r['nommun'],
				'codproesp' => $r['codproesp'], 'proesp' => $r['proesp'],
				'codslem'   => $r['codslem'],
				'feclini'   => $r['feclini'], 'feclin' => $r['feclin'], 'cupo' => $r['cupo'],
				'jornada'   => $r['jornada'], 'jorn'  => $r['jorn'],
				'idfic'     => $r['idfic'], 'nomfic' => $r['nomfic'],
				'convht'    => $r['convht'],
			];
		}
		return $filas;
	}

	// Reporte completo de una hoja demo (mismo esquema que reporte())
	public function datosDemoUno($id){
		$base = $this->demoDatos();
		$id   = (int)$id;
		if (!isset($base[$id])) return ['datos' => [], 'dias' => []];

		$r = $base[$id];
		$datos = [
			'idnorad'      => $id,
			'codpro'       => $r['codpro'],
			'idemp'        => $r['idemp'],
			'codproesp'    => !empty($r['proesp']) ? $r['proesp'] : $r['codproesp'],
			'codslem'      => $r['codslem'],
			'feclini'      => $r['feclini'], 'feclin' => $r['feclin'],
			'fecha_inicio' => $r['feclini'], 'fecha_fin' => $r['feclin'],
			'cupo'         => $r['cupo'],
			'jornada'      => $r['jorn'],
			'codigo_ficha' => $r['idfic'], 'idfic' => $r['idfic'], 'nomfic' => $r['nomfic'],
			'nompro'       => $r['nompro'],
			'nomemp'       => $r['nomemp'], 'numdocemp' => $r['numdocemp'],
			'convenio'     => !empty($r['convht']) ? $r['convht'] : 'Sin convenio específico',
			'convht'       => $r['convht'],
			'duracion'     => $r['horlpro'] . ' horas',
			'direccion'    => $r['diremp'],
			'municipio'    => $r['nommun'],
			'contacto'     => $r['nomconemp'],
			'telefono'     => $r['telemp'],
		];
		foreach (['nombre_instructor', 'documento_instructor', 'cel_instructor', 'correo_instructor'] as $k) {
			$datos[$k] = $r['instructor'][$k] ?? '';
		}

		$dias = [];
		foreach ($r['dias'] as $dia => $hrs) {
			$dias[$dia] = ['inicio' => $hrs[0], 'fin' => $hrs[1]];
		}
		return ['datos' => $datos, 'dias' => $dias];
	}

	public function diasDemo($id){
		$base = $this->demoDatos();
		$id   = (int)$id;
		if (!isset($base[$id])) return [];
		$dias = [];
		foreach ($base[$id]['dias'] as $dia => $hrs) {
			$dias[$dia] = ['inicio' => $hrs[0], 'fin' => $hrs[1]];
		}
		return $dias;
	}

	// ─────────────────────────────────────────────────────────────────────────
	// Estadísticas/dashboard sobre las hojas de trabajo (reales o demo)
	// ─────────────────────────────────────────────────────────────────────────
	public function estadisticas($dat){
		$out = [
			'total'         => 0,
			'totalCupo'     => 0,
			'vigentes'      => 0,
			'proximasVencer'=> 0,
			'porJornada'    => [],
			'porMes'        => [],
			'topEmpresas'   => [],
			'topProgramas'  => [],
		];
		if (empty($dat) || !is_array($dat)) return $out;

		$hoy = date('Y-m-d');
		$h30 = date('Y-m-d', strtotime('+30 days'));
		$jornadas = []; $meses = []; $empresas = []; $programas = [];

		foreach ($dat as $r) {
			$cupo = (int)($r['cupo'] ?? 0);
			$out['total']++;
			$out['totalCupo'] += $cupo;

			$ini = $r['feclini'] ?? '';
			$fin = $r['feclin'] ?? '';

			if ($ini !== '' && $ini <= $hoy && $fin !== '' && $fin >= $hoy) $out['vigentes']++;
			if ($fin !== '' && $fin >= $hoy && $fin <= $h30) $out['proximasVencer']++;

			$jorn = !empty($r['jorn']) ? $r['jorn'] : (!empty($r['jornada']) ? '#' . $r['jornada'] : 'Sin definir');
			$jornadas[$jorn] = ($jornadas[$jorn] ?? 0) + 1;

			if ($ini !== '') {
				$mk = substr($ini, 0, 7);
				$meses[$mk] = ($meses[$mk] ?? 0) + 1;
			}

			$emp = !empty($r['nomemp']) ? $r['nomemp'] : 'Sin empresa';
			$empresas[$emp] = ($empresas[$emp] ?? 0) + 1;

			$prog = !empty($r['nompro']) ? $r['nompro'] : 'Sin programa';
			$programas[$prog] = ($programas[$prog] ?? 0) + $cupo;
		}

		ksort($meses);
		arsort($empresas);
		arsort($programas);

		$out['porJornada']   = $jornadas;
		$out['porMes']       = $meses;
		$out['topEmpresas']  = array_slice($empresas, 0, 5, true);
		$out['topProgramas'] = array_slice($programas, 0, 5, true);

		return $out;
	}

	// ─────────────────────────────────────────────────────────────────────────
	// Exportar Hoja de Trabajo como PDF usando dompdf
	// ─────────────────────────────────────────────────────────────────────────
	public function exportarPDF($datos, $diasHorario = []) {
		// Cargar Composer autoloader
		$autoload = __DIR__ . '/../vendor/autoload.php';
		if (!file_exists($autoload)) { die('vendor/autoload.php no encontrado.'); }
		require_once $autoload;

		// La vista es incluida por home.php tras pintar la plantilla: limpiar el buffer
		if (ob_get_level()) { ob_clean(); }
		if (headers_sent()) {
			echo '<script>alert("No fue posible generar el PDF. Recargue la página e intente de nuevo.");window.history.back();</script>';
			exit;
		}

		$radicado   = str_pad($datos['idnorad'] ?? '0', 5, '0', STR_PAD_LEFT);
		$timestamp  = date('Ymd');
		$nombreBase = 'HojaTrabajo_' . $radicado . '_' . $timestamp;

		$diasSemana = ['LUNES', 'MARTES', 'MIÉRCOLES', 'JUEVES', 'VIERNES', 'SÁBADO', 'DOMINGO'];

		// Logo institucional en Base64 (requiere GD; si no está disponible se usa el texto SENA)
		$logoHtml = '<strong style="color:#0d701c;font-size:22px;">SENA</strong>';
		$tieneGD = function_exists('imagecreatefrompng');
		foreach ([__DIR__ . '/../img/sena.png', __DIR__ . '/../img/senab.png', __DIR__ . '/img/sena.png'] as $ruta) {
			if ($tieneGD && file_exists($ruta)) {
				$bin = @file_get_contents($ruta);
				if ($bin !== false) {
					$b64 = 'data:image/png;base64,' . base64_encode($bin);
					$logoHtml = "<img src=\"{$b64}\" style=\"max-width:80px;max-height:70px;\">";
					break;
				}
			}
		}

		// Helper: escape HTML
		$e = function($v, $def = '---') {
			return htmlspecialchars(!empty($v) ? $v : $def);
		};

		// Fecha formateada
		$fmt = function($f) {
			if (empty($f)) return '---';
			$ts = strtotime($f);
			return $ts ? date('d/m/Y', $ts) : $f;
		};

		// Filas de horario
		$filasHor = '';
		foreach ($diasSemana as $dia) {
			$hI  = !empty($diasHorario[$dia]['inicio']) ? htmlspecialchars($diasHorario[$dia]['inicio']) : '---';
			$hF  = !empty($diasHorario[$dia]['fin'])    ? htmlspecialchars($diasHorario[$dia]['fin'])    : '---';
			$prg = ($hI !== '---' && $hF !== '---') ? 'Sesión programada' : 'No programado';
			$bg  = ($hI !== '---' && $hF !== '---') ? '#d1fae5' : '#f8fafc';
			$filasHor .= "<tr style=\"background:{$bg};\">
				<td style=\"padding:5px 8px;border:1px solid #cbd5e1;\">".htmlspecialchars($dia)."</td>
				<td style=\"padding:5px 8px;border:1px solid #cbd5e1;text-align:center;\">{$hI}</td>
				<td style=\"padding:5px 8px;border:1px solid #cbd5e1;text-align:center;\">{$hF}</td>
				<td style=\"padding:5px 8px;border:1px solid #cbd5e1;\">{$prg}</td>
			</tr>";
		}

		$html = '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8">
		<style>
			* { box-sizing:border-box; margin:0; padding:0; }
			body { font-family: DejaVu Sans, sans-serif; font-size:10px; color:#1e293b; padding:18px; }
			.htbl { width:100%; border-collapse:collapse; border-bottom:2px solid #000; margin-bottom:14px; }
			.htbl td { border:none; vertical-align:middle; padding:4px 8px; }
			.itit { font-weight:bold; text-transform:uppercase; font-size:11px; text-align:center; line-height:1.3; }
			.isub { font-weight:bold; text-transform:uppercase; font-size:9px; color:#0d701c; margin-top:2px; text-align:center; }
			.idoc { font-weight:bold; text-transform:uppercase; font-size:10px; margin-top:3px; text-align:center; }
			.rbox { border:2px solid #000; background:#f8fafc; padding:7px 10px; text-align:center; border-radius:4px; }
			.rbox .rl { font-size:8px; text-transform:uppercase; color:#64748b; }
			.rbox .rn { font-size:16px; font-weight:bold; color:#0d701c; margin:2px 0; }
			.rbox .rd { font-size:9px; color:#64748b; font-weight:bold; border-top:1px dashed #cbd5e1; padding-top:3px; margin-top:3px; }
			.stit { background:#e8f5e9; border-left:4px solid #0d701c; padding:5px 10px; font-weight:bold; font-size:10px; text-transform:uppercase; margin:12px 0 6px 0; }
			.dtbl { width:100%; border-collapse:collapse; margin-bottom:6px; }
			.dtbl td { padding:5px 8px; border:1px solid #e2e8f0; font-size:9.5px; }
			.dtbl .lb { font-weight:bold; background:#f8fafc; color:#475569; width:28%; }
			.hrtbl { width:100%; border-collapse:collapse; margin-bottom:6px; }
			.hrtbl th { background:#0d701c; color:#fff; padding:5px 8px; font-size:9px; text-align:left; border:1px solid #0d701c; }
			.obsbox { background:#f8fafc; border:1px solid #e2e8f0; padding:7px 10px; font-size:9px; color:#475569; margin-bottom:10px; }
			.ftbl { width:100%; border-collapse:collapse; margin-top:16px; }
			.ftbl td { border-top:1px solid #000; text-align:center; padding:4px 8px; font-size:8.5px; width:33.33%; }
			.foot { text-align:center; font-size:8px; color:#94a3b8; margin-top:10px; }
		</style></head><body>
		<table class="htbl"><tr>
			<td style="width:90px;text-align:center;">' . $logoHtml . '</td>
			<td>
				<div class="itit">SERVICIO NACIONAL DE APRENDIZAJE – SENA | CENTRO DE DESARROLLO AGROEMPRESARIAL</div>
				<div class="isub">REGIONAL CUNDINAMARCA — SEDE CHÍA / SEDES ADSCRITAS</div>
				<div class="idoc">HOJA DE TRABAJO — SOLICITUD DE CURSOS DE FORMACIÓN COMPLEMENTARIA - TITULADA</div>
			</td>
			<td style="width:160px;text-align:right;">
				<div class="rbox">
					<div class="rl">Radicado Oficial</div>
					<div class="rn">#' . $e($radicado) . '</div>
					<div class="rd">Expedición: ' . date('d/m/Y') . '</div>
				</div>
			</td>
		</tr></table>

		<div class="stit">1. Información General del Programa</div>
		<table class="dtbl">
			<tr><td class="lb">Radicado Oficial</td><td>#' . $e($radicado) . '</td><td class="lb">Código del Programa</td><td>' . $e($datos['codpro'] ?? '') . '</td></tr>
			<tr><td class="lb">Nombre del Programa</td><td colspan="3">' . $e($datos['nompro'] ?? '') . '</td></tr>
			<tr><td class="lb">Duración Estimada</td><td>' . $e($datos['duracion'] ?? '') . '</td><td class="lb">Programa Especial</td><td>' . $e($datos['codproesp'] ?? '', 'Regular / Ninguno') . '</td></tr>
			<tr><td class="lb">Jornada de Formación</td><td>' . $e($datos['jornada'] ?? '') . '</td><td class="lb">Cód. Solicitud Empresa</td><td>' . $e($datos['codslem'] ?? '') . '</td></tr>
			<tr><td class="lb">Convenio Asociado</td><td colspan="3">' . $e($datos['convenio'] ?? '', 'No aplica / Sin convenio específico') . '</td></tr>
		</table>

		<div class="stit">2. Datos de la Entidad Solicitante</div>
		<table class="dtbl">
			<tr><td class="lb">Razón Social / Empresa</td><td colspan="3">' . $e($datos['nomemp'] ?? '') . '</td></tr>
			<tr><td class="lb">NIT / Identificación</td><td>' . $e($datos['numdocemp'] ?? '') . '</td><td class="lb">Municipio de Formación</td><td>' . $e($datos['municipio'] ?? '') . '</td></tr>
			<tr><td class="lb">Dirección del Lugar</td><td>' . $e($datos['direccion'] ?? '', 'Sede asignada') . '</td><td class="lb">Persona de Contacto</td><td>' . $e($datos['contacto'] ?? '') . '</td></tr>
			<tr><td class="lb">Teléfono / Celular</td><td colspan="3">' . $e($datos['telefono'] ?? '') . '</td></tr>
		</table>

		<div class="stit">3. Programación y Parámetros de la Ficha</div>
		<table class="dtbl">
			<tr><td class="lb">Código Ficha (SOFIA Plus)</td><td>' . $e($datos['codigo_ficha'] ?? '', 'Pendiente por vincular') . '</td><td class="lb">Cupo de Aprendices</td><td>' . $e((!empty($datos['cupo']) ? $datos['cupo'] . ' cupos' : '0 cupos')) . '</td></tr>
			<tr><td class="lb">Fecha de Inicio</td><td>' . $fmt($datos['fecha_inicio'] ?? '') . '</td><td class="lb">Fecha de Terminación</td><td>' . $fmt($datos['fecha_fin'] ?? '') . '</td></tr>
			<tr><td class="lb">Jornada Programada</td><td>' . $e($datos['jornada'] ?? '') . '</td><td class="lb">Estado de la Ficha</td><td>Registrada</td></tr>
			' . (!empty($datos['nomfic']) ? '<tr><td class="lb">Nombre de la Ficha</td><td colspan="3">' . $e($datos['nomfic']) . '</td></tr>' : '') . '
		</table>

		<div class="stit">4. Horarios y Días de Formación</div>
		<table class="hrtbl">
			<thead><tr><th>Día</th><th>Hora Inicio</th><th>Hora Fin</th><th>Programación</th></tr></thead>
			<tbody>' . $filasHor . '</tbody>
		</table>

		<div class="stit">5. Datos del Instructor Asignado</div>
		<table class="dtbl">
			<tr><td class="lb">Nombres y Apellidos</td><td colspan="3">' . $e($datos['nombre_instructor'] ?? '', 'Pendiente por asignar') . '</td></tr>
			<tr><td class="lb">Documento de Identidad</td><td>' . $e($datos['documento_instructor'] ?? '') . '</td><td class="lb">Teléfono / Celular</td><td>' . $e($datos['cel_instructor'] ?? '') . '</td></tr>
			<tr><td class="lb">Correo Institucional</td><td colspan="3">' . $e($datos['correo_instructor'] ?? '') . '</td></tr>
		</table>

		<div class="stit">6. Observaciones y Seguimiento</div>
		<div class="obsbox">La presente Hoja de Trabajo se genera bajo la normatividad académica e institucional vigente para formación complementaria y titulada del Centro de Desarrollo Agroempresarial (SENA Regional Cundinamarca). La ejecución efectiva de la acción formativa se encuentra sujeta al cumplimiento estricto del cupo mínimo de aprendices formalmente matriculados en la plataforma SOFIA Plus.</div>

		<div class="stit">7. Firmas y Control Institucional</div>
		<table class="ftbl"><tr>
			<td><div style="height:30px;"></div><strong>Responsable de Formación</strong><br>Instructor / Gestor de Solicitud</td>
			<td><div style="height:30px;"></div><strong>Revisión Académica CEDA</strong><br>Coordinación de Formación</td>
			<td><div style="height:30px;"></div><strong>Aprobación Final</strong><br>Subdirección de Centro / Apoyo</td>
		</tr></table>
		<div class="foot">Documento oficial — SENA CEDA — Generado el ' . date('d/m/Y H:i:s') . '</div>
		</body></html>';

		$options = new \Dompdf\Options();
		$options->set('isHtml5ParserEnabled', true);
		$options->set('isRemoteEnabled', false);
		$options->set('defaultFont', 'DejaVu Sans');

		try {
			$dompdf = new \Dompdf\Dompdf($options);
			$dompdf->loadHtml($html);
			$dompdf->setPaper('A4', 'portrait');
			$dompdf->render();
			$outPDF = $dompdf->output();
		} catch (\Throwable $ex) {
			if (ob_get_level()) { ob_end_clean(); }
			echo '<script>alert("No fue posible generar el PDF ('.htmlspecialchars($ex->getMessage()).'). Recargue la página e intente de nuevo.");window.history.back();</script>';
			exit;
		}

		// Descartar salida previa (p.ej. notices de dompdf) para no corromper el binario
		while (ob_get_level()) { ob_end_clean(); }

		header('Content-Type: application/pdf');
		header('Content-Disposition: attachment; filename="' . $nombreBase . '.pdf"');
		header('Cache-Control: private, max-age=0, must-revalidate');
		header('Pragma: public');
		echo $outPDF;
		exit;
	}

	// ─────────────────────────────────────────────────────────────────────────
	// Exportar Hoja de Trabajo como Excel usando PhpSpreadsheet
	// ─────────────────────────────────────────────────────────────────────────
	public function exportarExcel($datos, $diasHorario = []) {
		// Cargar Composer autoloader
		$autoload = __DIR__ . '/../vendor/autoload.php';
		if (!file_exists($autoload)) { die('vendor/autoload.php no encontrado.'); }
		require_once $autoload;

		// Corrección de autoload de Composer: ZipStream (usado por el Writer\Xlsx de PhpSpreadsheet)
		// depende de MyCLabs\Enum, pero ese paquete no quedó registrado en el autoload de Composer
		// (instalación incompleta desde su día). Se registra su PSR-4 en tiempo de ejecución.
		if (!class_exists('MyCLabs\\Enum\\Enum', false)) {
			$myclabs = __DIR__ . '/../vendor/myclabs/php-enum/src';
			if (is_dir($myclabs)) {
				foreach (get_declared_classes() as $cls) {
					if (strpos($cls, 'ComposerAutoloaderInit') === 0 && method_exists($cls, 'getLoader')) {
						$cls::getLoader()->addPsr4('MyCLabs\\Enum\\', $myclabs);
						break;
					}
				}
			}
		}

		// La vista es incluida por home.php tras pintar la plantilla: limpiar el buffer
		if (ob_get_level()) { ob_clean(); }
		if (headers_sent()) {
			echo '<script>alert("No fue posible generar el Excel. Recargue la página e intente de nuevo.");window.history.back();</script>';
			exit;
		}

		$radicado   = str_pad($datos['idnorad'] ?? '0', 5, '0', STR_PAD_LEFT);
		$timestamp  = date('Ymd');
		$nombreBase = 'HojaTrabajo_' . $radicado . '_' . $timestamp;

		$diasSemana = ['LUNES', 'MARTES', 'MIÉRCOLES', 'JUEVES', 'VIERNES', 'SÁBADO', 'DOMINGO'];

		$fmt = function($f) {
			if (empty($f)) return '---';
			$ts = strtotime($f);
			return $ts ? date('d/m/Y', $ts) : $f;
		};

		$spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
		$sheet       = $spreadsheet->getActiveSheet();
		$sheet->setTitle('Hoja de Trabajo');

		$fila = 1;
		$VERDE   = '0d701c';
		$VERDECL = 'BBF7D0';
		$GRIS    = 'F1F5F9';
		$BLANCO  = 'FFFFFF';

		// Función local para encabezado de sección
		$sec = function($titulo) use ($sheet, &$fila, $VERDE) {
			$sheet->mergeCells("A{$fila}:D{$fila}");
			$sheet->setCellValue("A{$fila}", $titulo);
			$sheet->getStyle("A{$fila}:D{$fila}")->applyFromArray([
				'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
				'fill'      => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => $VERDE]],
				'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT],
			]);
			$fila++;
		};

		$fil1 = function($lbl, $val) use ($sheet, &$fila, $GRIS) {
			$sheet->setCellValue("A{$fila}", $lbl);
			$sheet->mergeCells("B{$fila}:D{$fila}");
			$sheet->setCellValue("B{$fila}", $val);
			$sheet->getStyle("A{$fila}")->applyFromArray(['font' => ['bold' => true], 'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => $GRIS]]]);
			$fila++;
		};

		$fil2 = function($l1, $v1, $l2, $v2) use ($sheet, &$fila, $GRIS) {
			$sheet->setCellValue("A{$fila}", $l1);
			$sheet->setCellValue("B{$fila}", $v1);
			$sheet->setCellValue("C{$fila}", $l2);
			$sheet->setCellValue("D{$fila}", $v2);
			$sheet->getStyle("A{$fila}")->applyFromArray(['font' => ['bold' => true], 'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => $GRIS]]]);
			$sheet->getStyle("C{$fila}")->applyFromArray(['font' => ['bold' => true], 'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => $GRIS]]]);
			$fila++;
		};

		// Encabezado
		$sheet->mergeCells("A{$fila}:D{$fila}");
		$sheet->setCellValue("A{$fila}", 'SERVICIO NACIONAL DE APRENDIZAJE – SENA | CENTRO DE DESARROLLO AGROEMPRESARIAL');
		$sheet->getStyle("A{$fila}:D{$fila}")->applyFromArray(['font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => $VERDE]], 'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]]);
		$fila++;

		$sheet->mergeCells("A{$fila}:D{$fila}");
		$sheet->setCellValue("A{$fila}", 'HOJA DE TRABAJO — SOLICITUD DE CURSOS DE FORMACIÓN COMPLEMENTARIA - TITULADA');
		$sheet->getStyle("A{$fila}:D{$fila}")->applyFromArray(['font' => ['bold' => true, 'size' => 10], 'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]]);
		$fila++;

		$sheet->mergeCells("A{$fila}:D{$fila}");
		$sheet->setCellValue("A{$fila}", 'Radicado Oficial: #' . $radicado . '   |   Generado: ' . date('d/m/Y H:i'));
		$sheet->getStyle("A{$fila}:D{$fila}")->applyFromArray(['font' => ['italic' => true, 'size' => 9, 'color' => ['rgb' => '64748B']], 'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]]);
		$fila += 2;

		// Sección 1
		$sec('1. INFORMACIÓN GENERAL DEL PROGRAMA');
		$fil2('Radicado Oficial', '#' . $radicado, 'Código del Programa', $datos['codpro'] ?? '---');
		$fil1('Nombre del Programa', $datos['nompro'] ?? '---');
		$fil2('Duración Estimada', $datos['duracion'] ?? '---', 'Programa Especial', $datos['codproesp'] ?? 'Regular / Ninguno');
		$fil2('Jornada de Formación', $datos['jornada'] ?? '---', 'Cód. Solicitud Empresa', $datos['codslem'] ?? '---');
		$fil1('Convenio Asociado', $datos['convenio'] ?? 'No aplica / Sin convenio específico');
		$fila++;

		// Sección 2
		$sec('2. DATOS DE LA ENTIDAD SOLICITANTE');
		$fil1('Razón Social / Empresa', $datos['nomemp'] ?? '---');
		$fil2('NIT / Identificación', $datos['numdocemp'] ?? '---', 'Municipio de Formación', $datos['municipio'] ?? '---');
		$fil2('Dirección del Lugar', $datos['direccion'] ?? 'Sede asignada', 'Persona de Contacto', $datos['contacto'] ?? '---');
		$fil1('Teléfono / Celular', $datos['telefono'] ?? '---');
		$fila++;

		// Sección 3
		$sec('3. PROGRAMACIÓN Y PARÁMETROS DE LA FICHA');
		$fil2('Código Ficha (SOFIA Plus)', $datos['codigo_ficha'] ?? 'Pendiente por vincular', 'Cupo de Aprendices', (!empty($datos['cupo']) ? $datos['cupo'] . ' cupos' : '0 cupos'));
		$fil2('Fecha de Inicio', $fmt($datos['fecha_inicio'] ?? ''), 'Fecha de Terminación', $fmt($datos['fecha_fin'] ?? ''));
		$fil2('Jornada Programada', $datos['jornada'] ?? '---', 'Estado de la Ficha', 'Registrada');
		if (!empty($datos['nomfic'])) { $fil1('Nombre de la Ficha', $datos['nomfic']); }
		$fila++;

		// Sección 4 — Horarios
		$sec('4. HORARIOS Y DÍAS DE FORMACIÓN');
		foreach (['A' => 'Día de la Semana', 'B' => 'Hora Inicio', 'C' => 'Hora Fin', 'D' => 'Programación'] as $col => $titulo) {
			$sheet->setCellValue("{$col}{$fila}", $titulo);
		}
		$sheet->getStyle("A{$fila}:D{$fila}")->applyFromArray([
			'font' => ['bold' => true],
			'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => $VERDECL]],
			'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']]],
		]);
		$fila++;
		foreach ($diasSemana as $dia) {
			$hI  = !empty($diasHorario[$dia]['inicio']) ? $diasHorario[$dia]['inicio'] : '---';
			$hF  = !empty($diasHorario[$dia]['fin'])    ? $diasHorario[$dia]['fin']    : '---';
			$prg = ($hI !== '---' && $hF !== '---') ? 'Sesión programada' : 'No programado';
			$bgD = ($hI !== '---' && $hF !== '---') ? 'D1FAE5' : $BLANCO;
			$sheet->setCellValue("A{$fila}", $dia);
			$sheet->setCellValue("B{$fila}", $hI);
			$sheet->setCellValue("C{$fila}", $hF);
			$sheet->setCellValue("D{$fila}", $prg);
			$sheet->getStyle("A{$fila}:D{$fila}")->applyFromArray([
				'fill'    => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => $bgD]],
				'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']]],
			]);
			$fila++;
		}
		$fila++;

		// Sección 5
		$sec('5. DATOS DEL INSTRUCTOR ASIGNADO');
		$fil1('Nombres y Apellidos', $datos['nombre_instructor'] ?? 'Pendiente por asignar');
		$fil2('Documento de Identidad', $datos['documento_instructor'] ?? '---', 'Teléfono / Celular', $datos['cel_instructor'] ?? '---');
		$fil1('Correo Institucional', $datos['correo_instructor'] ?? '---');
		$fila++;

		// Sección 6
		$sec('6. OBSERVACIONES Y SEGUIMIENTO');
		$sheet->mergeCells("A{$fila}:D{$fila}");
		$sheet->setCellValue("A{$fila}", 'La presente Hoja de Trabajo se genera bajo la normatividad académica e institucional vigente para formación complementaria y titulada del Centro de Desarrollo Agroempresarial (SENA Regional Cundinamarca). La ejecución efectiva de la acción formativa se encuentra sujeta al cumplimiento estricto del cupo mínimo de aprendices formalmente matriculados en la plataforma SOFIA Plus.');
		$sheet->getStyle("A{$fila}:D{$fila}")->getAlignment()->setWrapText(true);
		$sheet->getRowDimension($fila)->setRowHeight(45);
		$fila += 2;

		// Sección 7
		$sec('7. FIRMAS Y CONTROL INSTITUCIONAL');
		foreach (['A' => 'Responsable de Formación', 'B' => 'Revisión Académica CEDA', 'C' => 'Aprobación Final'] as $col => $tit) {
			$sheet->setCellValue("{$col}{$fila}", $tit);
			$sheet->getStyle("{$col}{$fila}")->applyFromArray(['font' => ['bold' => true], 'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]]);
		}
		$fila++;
		foreach (['A' => 'Instructor / Gestor de Solicitud', 'B' => 'Coordinación de Formación', 'C' => 'Subdirección de Centro / Apoyo'] as $col => $sub) {
			$sheet->setCellValue("{$col}{$fila}", $sub);
			$sheet->getStyle("{$col}{$fila}")->applyFromArray([
				'font'      => ['italic' => true, 'size' => 8, 'color' => ['rgb' => '64748B']],
				'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
				'borders'   => ['top' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM]],
			]);
		}
		$fila += 2;

		// Pie de página
		$sheet->mergeCells("A{$fila}:D{$fila}");
		$sheet->setCellValue("A{$fila}", 'Documento oficial — SENA CEDA — Generado el ' . date('d/m/Y H:i:s'));
		$sheet->getStyle("A{$fila}:D{$fila}")->applyFromArray(['font' => ['italic' => true, 'size' => 8, 'color' => ['rgb' => '94A3B8']], 'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]]);

		// Anchos de columna
		foreach (['A' => 30, 'B' => 32, 'C' => 28, 'D' => 28] as $col => $w) {
			$sheet->getColumnDimension($col)->setWidth($w);
		}

		// Borde exterior
		$sheet->getStyle('A1:D' . $fila)->applyFromArray([
			'borders' => ['outline' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM, 'color' => ['rgb' => $VERDE]]],
		]);

		// Descartar salida previa para no corromper el binario (Idem PDF)
		while (ob_get_level()) { ob_end_clean(); }

		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment; filename="' . $nombreBase . '.xlsx"');
		header('Cache-Control: max-age=0');

		$writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
		$writer->save('php://output');
		exit;
	}
}
?>