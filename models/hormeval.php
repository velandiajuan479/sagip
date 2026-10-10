<?php
// CACHE BUSTER - FORZAR RECARGA - 2025-09-17 00:06 - IDCOM FIX
require_once(__DIR__ . "/conexion.php");

/**
 * Función para manejar errores de base de datos
 * Solo se define si no existe ya
 */
if (!function_exists('ManejoError')) {
    function ManejoError($e) {
        error_log("Error en Mjui: " . $e->getMessage());
        // No imprimir nada para evitar romper respuestas JSON
        // if (defined('DEBUG_MODE') && DEBUG_MODE) {
        //     echo "Error: " . $e->getMessage();
        // }
    }
}

class Mjui{
	private $idjui;
	private $idusu;
	private $idfic;
	private $idres;
    private $caljui;
    
    // Propiedades adicionales para el formulario (solo las que se usan)
    private $ndoce;
    private $nomes;
    private $apees;
    private $compe;
    private $resapr;
    private $tidoc;

    // GETTERS
    function getIdjui() {
        return $this->idjui;
    }
    function getIdusu() {
        return $this->idusu;
    }
    function getIdfic() {
        return $this->idfic;
    }
    function getIdres() {
        return $this->idres;
    }
    function getCaljui() {
        return $this->caljui;
    }
    function getNdoce() {
        return $this->ndoce;
    }
    function getNomes() {
        return $this->nomes;
    }
    function getApees() {
        return $this->apees;
    }
    function getEstes() {
        return $this->estes;
    }
    function getCompe() {
        return $this->compe;
    }
    function getResapr() {
        return $this->resapr;
    }
    function getTidoc() {
        return $this->tidoc;
    }

    // SETTERS
    function setIdjui($idjui) {
        $this->idjui = $idjui;
    }
    function setIdusu($idusu) {
        $this->idusu = $idusu;
    }
    function setIdfic($idfic) {
        $this->idfic = $idfic;
    }
    function setIdres($idres) {
        $this->idres = $idres;
    }
    function setCaljui($caljui) {
        $this->caljui = $caljui;
    }
    function setNdoce($ndoce) {
        $this->ndoce = $ndoce;
    }
    function setNomes($nomes) {
        $this->nomes = $nomes;
    }
    function setApees($apees) {
        $this->apees = $apees;
    }
    function setEstes($estes) {
        $this->estes = $estes;
    }
    function setCompe($compe) {
        $this->compe = $compe;
    }
    function setResapr($resapr) {
        $this->resapr = $resapr;
    }
    function setTidoc($tidoc) {
        $this->tidoc = $tidoc;
    }

	// Métodos
	function getAll(){
		try{
                // Verificar primero si las nuevas columnas existen
                $modelo = new conexion();
                $conexion = $modelo->get_conexion();
                
                $stmt = $conexion->query("SHOW COLUMNS FROM juicio LIKE 'fecha_reporte'");
                $tiene_fecha_reporte = $stmt->rowCount() > 0;
                
                $stmt = $conexion->query("SHOW COLUMNS FROM juicio LIKE 'fecha_importacion'");
                $tiene_fecha_importacion = $stmt->rowCount() > 0;
                
                $stmt = $conexion->query("SHOW COLUMNS FROM juicio LIKE 'archivo_correlativo'");
                $tiene_archivo_correlativo = $stmt->rowCount() > 0;

                // Construir la consulta dinámicamente según las columnas disponibles
			$sql = "SELECT 
                    j.idjui, j.idusu, j.idfic, j.idres, j.caljui,
                    -- Datos del estudiante (tabla usuario)
                    COALESCE(u.ndocusu, '') as ndoce, 
                    COALESCE(SUBSTRING_INDEX(u.nomusu, ' ', 2), 'Sin nombre') as nomes, 
                    COALESCE(SUBSTRING_INDEX(u.nomusu, ' ', -2), 'Sin apellidos') as apees, 
                    COALESCE(u.tdousu, '') as tidoc,
                    'EN FORMACION' as estes,
                    -- Datos de la ficha
                    COALESCE(f.nomfic, 'Sin ficha') as nomfic, 
                    f.mun, f.finific, f.ffinfic,
                    -- Datos del resultado
                    COALESCE(r.nomres, 'Sin resultado') as resapr, 
                    r.ndeses,
                    -- Datos de la competencia
                    c.idcom, 
                    COALESCE(c.descom, 'Sin competencia') as compe,
                    -- Datos de aprobacion_resultado
                    ar.idaprob, ar.idinstructor, 
                    COALESCE(ar.estado, 'pendiente') as estado, 
                    ar.fecha_aprobacion as fecha_juicio,
                    -- Datos del funcionario instructor (buscado por ndocins de juicio)
                    j.ndocins,
                    COALESCE(func.nomusu, 'Sin instructor') as funre";

                // Agregar campos de metadatos solo si las columnas existen
                if ($tiene_fecha_reporte) {
                    $sql .= ", COALESCE(j.fecha_reporte, '') as fecre";
                } else {
                    $sql .= ", '' as fecre";
                }
                
                if ($tiene_fecha_importacion) {
                    $sql .= ", COALESCE(j.fecha_importacion, '') as fecim";
                } else {
                    $sql .= ", '' as fecim";
                }
                
                if ($tiene_archivo_correlativo) {
                    $sql .= ", COALESCE(j.archivo_correlativo, '') as arcor";
                } else {
                    $sql .= ", '' as arcor";
                }

                // Verificar si existe la columna fecjuieva
                $stmt = $conexion->query("SHOW COLUMNS FROM juicio LIKE 'fecjuieva'");
                $tiene_fecjuieva = $stmt->rowCount() > 0;
                
                if ($tiene_fecjuieva) {
                    $sql .= ", COALESCE(j.fecjuieva, '') as fecjuieva";
                } else {
                    $sql .= ", '' as fecjuieva";
                }

                $sql .= ", ar.fecha_aprobacion as fecju
                    FROM juicio j
                    LEFT JOIN usuario u ON j.idusu = u.idusu
                    LEFT JOIN ficha f ON j.idfic = f.idfic
                    LEFT JOIN resultado r ON j.idres = r.idres
                    LEFT JOIN competencia c ON r.idcom = c.idcom
                    LEFT JOIN aprobacion_resultado ar ON (j.idfic = ar.idfic AND j.idres = ar.idres)
                    LEFT JOIN usuario func ON j.ndocins = func.ndocusu
                    ORDER BY j.idjui DESC
                    LIMIT 50";
			$result = $conexion->prepare($sql);
			$result->execute();
			$res = $result->fetchAll(PDO::FETCH_ASSOC);
			return $res;
		}catch(Exception $e){
			ManejoError($e);
			return [];
		}
	}

	function getOne(){
		try{
		$sql = "SELECT
			j.idjui, j.idusu, j.idfic, j.idres, j.caljui,
			-- Datos del estudiante (tabla usuario)
			COALESCE(u.ndocusu, '') as ndoce, 
			COALESCE(SUBSTRING_INDEX(u.nomusu, ' ', 2), 'Sin nombre') as nomes, 
			COALESCE(SUBSTRING_INDEX(u.nomusu, ' ', -2), 'Sin apellidos') as apees, 
			COALESCE(u.tdousu, '') as tidoc,
			COALESCE(u.estusu, 'EN FORMACION') as estes,
			-- Datos de la ficha
			COALESCE(f.nomfic, 'Sin ficha') as nomfic, 
			f.mun, f.finific, f.ffinfic,
			-- Datos del resultado
			COALESCE(r.nomres, 'Sin resultado') as resapr, 
			r.ndeses,
			-- Datos de la competencia
			c.idcom, 
			COALESCE(c.descom, 'Sin competencia') as compe,
			-- Datos de aprobacion_resultado
			ar.idaprob, ar.idinstructor, 
			COALESCE(ar.estado, 'pendiente') as estado, 
			ar.fecha_aprobacion as fecha_juicio,
			-- Datos del funcionario instructor (buscado por ndocins de juicio)
			j.ndocins,
			COALESCE(func.nomusu, 'Sin instructor') as funre,
			-- Nuevos campos de metadatos con alias para JavaScript
			j.fecha_reporte as fecre,
			j.fecha_importacion as fecim,
			j.archivo_correlativo as arcor,
			ar.fecha_aprobacion as fecju
		FROM juicio j
		LEFT JOIN usuario u ON j.idusu = u.idusu
		LEFT JOIN ficha f ON j.idfic = f.idfic
		LEFT JOIN resultado r ON j.idres = r.idres
		LEFT JOIN competencia c ON r.idcom = c.idcom
		LEFT JOIN aprobacion_resultado ar ON (j.idfic = ar.idfic AND j.idres = ar.idres)
		LEFT JOIN usuario func ON j.ndocins = func.ndocusu
		WHERE j.idjui = :idjui";
			$modelo = new conexion();
			$conexion = $modelo->get_conexion();
			$result = $conexion->prepare($sql);
			$idjui = $this->getIdjui();
			$result->bindParam(":idjui",$idjui);
			$result->execute();
			$res = $result->fetchAll(PDO::FETCH_ASSOC);
			return $res;
		}catch(Exception $e){
			ManejoError($e);
			return [];
		}
	}

	function edit(){
		try{
			$sql = "UPDATE juicio SET idusu=:idusu, idfic=:idfic, idres=:idres, caljui=:caljui WHERE idjui=:idjui";
			$modelo = new conexion();
			$conexion = $modelo->get_conexion();
			$result = $conexion->prepare($sql);
			$idjui = $this->getIdjui();
			$result->bindParam(":idjui",$idjui);
			$idusu = $this->getIdusu();
			$result->bindParam(":idusu",$idusu);
			$idfic = $this->getIdfic();
			$result->bindParam(":idfic",$idfic);
			$caljui = $this->getCaljui();
			$result->bindParam(":caljui",$caljui);
			$result->execute();
		}catch(Exception $e){
			ManejoError($e);
		}
	}

	function del(){
		try{
			$sql = "DELETE FROM juicio WHERE idjui=:idjui";
			$modelo = new conexion();
			$conexion = $modelo->get_conexion();
			$result = $conexion->prepare($sql);
			$idjui = $this->getIdjui();
			$result->bindParam(":idjui",$idjui);
			$result->execute();
		}catch(Exception $e){
			ManejoError($e);
		}
	}

	function getAllUsu(){
		try{
			$sql = "SELECT idusu, ndocusu, nomusu FROM usuario";
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

	function getAllfic(){
		try{
			$sql = "SELECT idfic, nomfic FROM ficha";
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

	function getAllRes(){
		try{
			$sql = "SELECT idres, nomres FROM resultado";
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

	/**
	 * Busca el ID del usuario por número de documento
	 */
	function buscarUsuarioPorDocumento($ndoce){
		try{
			$sql = "SELECT idusu FROM usuario WHERE ndocusu = :ndoce LIMIT 1";
			$modelo = new conexion();
			$conexion = $modelo->get_conexion();
			$result = $conexion->prepare($sql);
			$result->bindParam(":ndoce", $ndoce);
			$result->execute();

			$usuario = $result->fetch(PDO::FETCH_ASSOC);
			return $usuario ? $usuario['idusu'] : null;

		}catch(Exception $e){
			ManejoError($e);
			return null;
		}
	}

	/**
	 * Buscar ficha por denominación en la tabla ficha
	 */
	function buscarFichaPorDenominacion($denominacion) {
		try {
			$modelo = new conexion();
			$conexion = $modelo->get_conexion();
			
			$sql = "SELECT idfic FROM ficha WHERE nomfic LIKE :nomfic LIMIT 1";
			$stmt = $conexion->prepare($sql);
			$param_nomfic = '%' . trim($denominacion) . '%';
			$stmt->bindParam(':nomfic', $param_nomfic);
			$stmt->execute();
			
			$resultado = $stmt->fetch(PDO::FETCH_ASSOC);
			return $resultado ? $resultado['idfic'] : null;
		} catch (Exception $e) {
			ManejoError($e);
			return null;
		}
	}

	/**
	 * Buscar ficha por ID con variantes (A, B, etc.)
	 */
	function buscarFichaPorIdConVariantes($idfic) {
		try {
			$modelo = new conexion();
			$conexion = $modelo->get_conexion();
			
			// Primero intentar buscar exactamente
			$sql = "SELECT idfic FROM ficha WHERE idfic = :idfic LIMIT 1";
			$stmt = $conexion->prepare($sql);
			$stmt->bindParam(':idfic', $idfic);
			$stmt->execute();
			
			$resultado = $stmt->fetch(PDO::FETCH_ASSOC);
			if ($resultado) {
				return $resultado['idfic'];
			}
			
			// Si no se encuentra, buscar con variantes (A, B, etc.)
			$sql = "SELECT idfic FROM ficha WHERE idfic LIKE :idfic ORDER BY idfic LIMIT 1";
			$stmt = $conexion->prepare($sql);
			$param_idfic = trim($idfic) . '%';
			$stmt->bindParam(':idfic', $param_idfic);
			$stmt->execute();
			
			$resultado = $stmt->fetch(PDO::FETCH_ASSOC);
			return $resultado ? $resultado['idfic'] : null;
		} catch (Exception $e) {
			ManejoError($e);
			return null;
		}
	}

	/**
	 * Busca el ID del resultado por competencia
	 */
	function buscarResultadoPorCompetencia($compe){
		try{
			$sql = "SELECT idres FROM resultado WHERE nomres LIKE :compe LIMIT 1";
			$modelo = new conexion();
			$conexion = $modelo->get_conexion();
			$result = $conexion->prepare($sql);
			
			// Buscar con comodín para mejor coincidencia
			$busqueda = "%" . trim($compe) . "%";
			$result->bindParam(":compe", $busqueda);
			$result->execute();
			
			$resultado = $result->fetch(PDO::FETCH_ASSOC);
			return $resultado ? $resultado['idres'] : null;
			
		}catch(Exception $e){
			ManejoError($e);
			return null;
		}
	}

	/**
	 * Actualiza automáticamente las relaciones idusu e idres para una ficha
	 */
	function actualizarRelacionesAutomaticamente($idfic){
		try{
			$modelo = new conexion();
			$conexion = $modelo->get_conexion();
			
			// 1. Obtener todos los juicios de la ficha que no tengan idusu o idres
			$sql = "SELECT idjui, ndoce, compe FROM juicio 
					WHERE idfic = :idfic 
					AND (idusu IS NULL OR idusu = '' OR idres IS NULL OR idres = '')";
			
			$stmt = $conexion->prepare($sql);
			$stmt->bindParam(":idfic", $idfic);
			$stmt->execute();
			$juicios = $stmt->fetchAll(PDO::FETCH_ASSOC);
			
			$estadisticas = [
				'total' => count($juicios),
				'usuarios_actualizados' => 0,
				'resultados_actualizados' => 0,
				'usuarios_no_encontrados' => 0,
				'resultados_no_encontrados' => 0
			];
			
			// 2. Procesar cada juicio
			foreach($juicios as $juicio){
				$actualizaciones = [];
				$valores = [];
				
				// Buscar usuario
				if(empty($juicio['idusu']) || $juicio['idusu'] == '') {
					$idusu = $this->buscarUsuarioPorDocumento($juicio['ndoce']);
					if($idusu) {
						$actualizaciones[] = "idusu = :idusu";
						$valores[':idusu'] = $idusu;
						$estadisticas['usuarios_actualizados']++;
					} else {
						$estadisticas['usuarios_no_encontrados']++;
					}
				}
				
				// Buscar resultado
				if(empty($juicio['idres']) || $juicio['idres'] == '') {
					$idres = $this->buscarResultadoPorCompetencia($juicio['compe']);
					if($idres) {
						$actualizaciones[] = "idres = :idres";
						$valores[':idres'] = $idres;
						$estadisticas['resultados_actualizados']++;
					} else {
						$estadisticas['resultados_no_encontrados']++;
					}
				}
				
				// 3. Actualizar si hay cambios
				if(!empty($actualizaciones)) {
					$sqlUpdate = "UPDATE juicio SET " . implode(", ", $actualizaciones) . " WHERE idjui = :idjui";
					$stmtUpdate = $conexion->prepare($sqlUpdate);
					
					// Bind de todos los valores
					foreach($valores as $param => $valor) {
						$stmtUpdate->bindParam($param, $valor);
					}
					$stmtUpdate->bindParam(":idjui", $juicio['idjui']);
					
					$stmtUpdate->execute();
				}
			}
			
			return $estadisticas;
			
		}catch(Exception $e){
			ManejoError($e);
			return false;
		}
	}

	/**
	 * Extraer metadatos del programa desde los datos del Excel
	 */
	function extraerMetadatosDelPrograma($excelData) {
		$metadatos = [];
		
		// Buscar metadatos en las primeras filas del CSV
		for ($i = 0; $i < min(20, count($excelData)); $i++) {
			$row = $excelData[$i];
			
			// Buscar por etiquetas específicas del CSV
			if (is_array($row)) {
				// Fecha del Reporte
				if (in_array('Fecha del Reporte:', $row)) {
					$fechaIndex = array_search('Fecha del Reporte:', $row);
					if (isset($row[$fechaIndex + 2])) {
						$metadatos['fecha_reporte'] = trim($row[$fechaIndex + 2]);
					}
				}
				
				// Ficha de Caracterización
				if (in_array('Ficha de Caracterización:', $row)) {
					$fichaIndex = array_search('Ficha de Caracterización:', $row);
					if (isset($row[$fichaIndex + 2])) {
						$metadatos['ficha_caracterizacion'] = trim($row[$fichaIndex + 2]);
					}
				}
				
				// Código
				if (in_array('Código:', $row)) {
					$codigoIndex = array_search('Código:', $row);
					if (isset($row[$codigoIndex + 2])) {
						$metadatos['codigo_reporte'] = trim($row[$codigoIndex + 2]);
					}
				}
				
				// Denominación
				if (in_array('Denominación:', $row)) {
					$denominacionIndex = array_search('Denominación:', $row);
					if (isset($row[$denominacionIndex + 2])) {
						$metadatos['denominacion'] = trim($row[$denominacionIndex + 2]);
					}
				}
				
				// Estado de la Ficha
				if (in_array('Estado de la Ficha de Caracterización:', $row)) {
					$estadoIndex = array_search('Estado de la Ficha de Caracterización:', $row);
					if (isset($row[$estadoIndex + 2])) {
						$metadatos['estado_ficha'] = trim($row[$estadoIndex + 2]);
					}
				}
				
				// Fecha Inicio
				if (in_array('Fecha Inicio:', $row)) {
					$inicioIndex = array_search('Fecha Inicio:', $row);
					if (isset($row[$inicioIndex + 2])) {
						$metadatos['fecha_inicio'] = trim($row[$inicioIndex + 2]);
					}
				}
				
				// Fecha Fin
				if (in_array('Fecha Fin:', $row)) {
					$finIndex = array_search('Fecha Fin:', $row);
					if (isset($row[$finIndex + 2])) {
						$metadatos['fecha_fin'] = trim($row[$finIndex + 2]);
					}
				}
				
				// Modalidad de Formación
				if (in_array('Modalidad de Formación:', $row)) {
					$modalidadIndex = array_search('Modalidad de Formación:', $row);
					if (isset($row[$modalidadIndex + 2])) {
						$metadatos['modalidad'] = trim($row[$modalidadIndex + 2]);
					}
				}
				
				// Regional
				if (in_array('Regional:', $row)) {
					$regionalIndex = array_search('Regional:', $row);
					if (isset($row[$regionalIndex + 2])) {
						$metadatos['regional'] = trim($row[$regionalIndex + 2]);
					}
				}
				
				// Centro de Formación
				if (in_array('Centro de Formación:', $row)) {
					$centroIndex = array_search('Centro de Formación:', $row);
					if (isset($row[$centroIndex + 2])) {
						$metadatos['centro'] = trim($row[$centroIndex + 2]);
					}
				}
			}
		}
		
		return $metadatos;
	}

	/**
	 * Extraer datos de juicio de una fila del Excel
	 */
	function extractJuicioDataMejorado($row, $idfic_import, $metadatos = [], $db = null) {
		// Verificar que la fila tenga suficientes columnas
		if (count($row) < 10) {
			return null;
		}
		
		// Mapear columnas según el Excel real
		$data = [
			'idfic' => $idfic_import,
			'ndoce' => $row[1] ?? '', // Número de Documento
			'nomes' => $row[2] ?? '', // Nombre
			'apees' => $row[3] ?? '', // Apellidos
			'estes' => $row[4] ?? '', // Estado
			'compe' => $row[5] ?? '', // Competencia
			'idres' => null, // Default a null
			'resapr' => $row[6] ?? '', // Resultado de Aprendizaje (texto completo)
			'caljui' => $row[7] ?? '', // Juicio de Evaluación
			'fecju' => $row[9] ?? '', // Fecha y Hora del Juicio Evaluativo (CORREGIDO: era $row[8])
			'funre' => $row[10] ?? '', // Funcionario que registra el juicio evaluativo (CORREGIDO: era $row[9])
			'fecre' => $metadatos['fecha_reporte'] ?? '',
			'fecim' => date('Y-m-d H:i:s'),
			'tidoc' => $row[0] ?? '', // Tipo de Documento
			'ficca' => $metadatos['ficha_caracterizacion'] ?? '',
			'codre' => $metadatos['codigo_reporte'] ?? '',
			'denpr' => $metadatos['denominacion'] ?? '',
			'estfi' => $metadatos['estado_ficha'] ?? '',
			'fecin' => $metadatos['fecha_inicio'] ?? '',
			'fecfi' => $metadatos['fecha_fin'] ?? '',
			'modfo' => $metadatos['modalidad'] ?? '',
			'regfo' => $metadatos['regional'] ?? '',
			'cenfo' => $metadatos['centro'] ?? ''
		];
		
		// Buscar idfic en la tabla ficha si no se proporcionó
		if (empty($data['idfic']) && !empty($data['denpr']) && $db) {
			$data['idfic'] = $this->buscarFichaPorDenominacion($data['denpr']);
		}
		
		// Buscar idusu en la tabla usuario por número de documento
		if (!empty($data['ndoce']) && $db) {
			$data['idusu'] = $this->buscarUsuarioPorDocumento($data['ndoce']);
		}
		
		// Convertir fechas del CSV a formato MySQL
		if (!empty($data['fecre'])) {
			$data['fecre'] = $this->convertirFechaExcel($data['fecre']);
		}
		if (!empty($data['fecin'])) {
			$data['fecin'] = $this->convertirFechaExcel($data['fecin']);
		}
		if (!empty($data['fecfi'])) {
			$data['fecfi'] = $this->convertirFechaExcel($data['fecfi']);
		}
		
		// Convertir fechas si es necesario
		if (!empty($data['fecju'])) {
			$data['fecju'] = $this->convertirFechaExcel($data['fecju']);
		}
		
		// Buscar idres en la tabla resultado
		if (!empty($data['resapr']) && $db) {
			try {
				// Extraer solo el texto después del número (ej: "ESTABLECER RELACIONES...")
				$textoResultado = preg_replace('/^\d+\s*-\s*\d+\s*/', '', $data['resapr']);
				$textoResultado = trim($textoResultado);
				
				// Buscar en la tabla resultado por nombre
				$sql = "SELECT idres FROM resultado WHERE nomres LIKE :nomres";
				$stmt = $db->prepare($sql);
				$param_nomres = '%' . $textoResultado . '%';
				$stmt->bindParam(':nomres', $param_nomres);
				$stmt->execute();
				$resultado = $stmt->fetch(PDO::FETCH_ASSOC);
				
				if ($resultado) {
					$data['idres'] = $resultado['idres'];
				}
			} catch (Exception $e) {
				// Si hay error, mantener idres como null
			}
		}
		
		return $data;
	}

	/**
	 * Convertir fecha de Excel a formato MySQL
	 */
	function convertirFechaExcel($fecha) {
		// Si es un número (formato Excel), convertirlo
		if (is_numeric($fecha)) {
			$timestamp = ($fecha - 25569) * 86400;
			return date('Y-m-d H:i:s', $timestamp);
		}
		
		// Si es string en formato dd/mm/yyyy HH.MM a, convertirlo
		if (preg_match('/(\d{1,2})\/(\d{1,2})\/(\d{4})\s+(\d{1,2})\.(\d{2})\s+([ap])/', $fecha, $matches)) {
			$dia = $matches[1];
			$mes = $matches[2];
			$anio = $matches[3];
			$hora = $matches[4];
			$minuto = $matches[5];
			$periodo = $matches[6];
			
			// Convertir a formato 24 horas
			if ($periodo === 'p' && $hora != 12) {
				$hora += 12;
			} elseif ($periodo === 'a' && $hora == 12) {
				$hora = 0;
			}
			
			return sprintf("%04d-%02d-%02d %02d:%02d:00", $anio, $mes, $dia, $hora, $minuto);
		}
		
		// Si es string en formato dd/mm/yyyy, convertirlo
		if (preg_match('/(\d{1,2})\/(\d{1,2})\/(\d{4})/', $fecha, $matches)) {
			$dia = $matches[1];
			$mes = $matches[2];
			$anio = $matches[3];
			return "$anio-$mes-$dia 00:00:00";
		}
		
		// Si no se puede convertir, devolver la fecha original
		return $fecha;
	}

	/**
	 * Procesar importación de Excel (versión sin HTML)
	 */
	function procesarImportacionExcel($excelFile, $idfic_import) {
		try {
					// Verificar si PhpSpreadsheet está disponible
		if (!file_exists('vendor/autoload.php')) {
			throw new Exception("PhpSpreadsheet no está disponible");
		}
		
		require_once 'vendor/autoload.php';
			
			// Cargar el archivo Excel
			$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($excelFile['tmp_name']);
			$worksheet = $spreadsheet->getActiveSheet();
			
			// Obtener dimensiones
			$highestRow = $worksheet->getHighestRow();
			$highestColumn = $worksheet->getHighestColumn();
			$highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);
			
			// Extraer datos fila por fila
			$csvData = [];
			for ($row = 1; $row <= $highestRow; $row++) {
				$rowData = [];
				for ($col = 1; $col <= $highestColumnIndex; $col++) {
					$cellValue = $worksheet->getCellByColumnAndRow($col, $row)->getValue();
					$rowData[] = $cellValue ?? '';
				}
				$csvData[] = $rowData;
			}
			
			// Extraer ficha de caracterización del archivo (línea 3 del CSV)
			$ficha_extraida = null;
			if (count($csvData) >= 3 && isset($csvData[2][2])) {
				$ficha_raw = trim($csvData[2][2]); // Línea 3, columna 3 (índice 2,2)
				// Buscar la ficha con variantes (A, B, etc.)
				$ficha_extraida = $this->buscarFichaPorIdConVariantes($ficha_raw);
				if (!$ficha_extraida) {
					error_log("⚠️ No se encontró ficha para: $ficha_raw");
					$ficha_extraida = $ficha_raw; // Usar el valor original si no se encuentra
				} else {
					error_log("✅ Ficha encontrada: $ficha_raw → $ficha_extraida");
				}
			}
			
			// Guardar datos en sesión para la vista
			$_SESSION['excel_data'] = $csvData;
			$_SESSION['file_name'] = $excelFile['name'];
			$_SESSION['idfic_import'] = $ficha_extraida; // Usar la ficha extraída del archivo
			
			return [
				'success' => true,
				'data' => $csvData,
				'total_rows' => count($csvData),
				'dimensions' => [
					'rows' => $highestRow,
					'columns' => $highestColumnIndex
				]
			];
			
		} catch (Exception $e) {
			return [
				'success' => false,
				'error' => $e->getMessage()
			];
		}
	}

	/**
	 * Cargar información automática de la ficha
	 */
	function cargarInfoFicha($idfic) {
		try {
			$sql = "SELECT f.*, p.nompro, p.despro, p.verpro, p.horlpro, p.horppro, p.crelpro, p.creppro, p.tippro
					FROM ficha f
					LEFT JOIN programa p ON f.codpro = p.codpro
					WHERE f.idfic = :idfic";
			
			$modelo = new conexion();
			$conexion = $modelo->get_conexion();
			$result = $conexion->prepare($sql);
			$result->bindParam(':idfic', $idfic);
			$result->execute();
			
			return $result->fetch(PDO::FETCH_ASSOC);
		} catch (Exception $e) {
			ManejoError($e);
			return null;
		}
	}

	/**
	 * Obtener competencias usadas por un instructor específico
	 */
	function getCompetenciasPorInstructor($idusu) {
		try {
			error_log("DEBUG getCompetenciasPorInstructor: Buscando competencias para instructor ID: " . $idusu);
			
			// Obtener número de documento del instructor
			$sqlDoc = "SELECT ndocusu FROM usuario WHERE idusu = :idusu";
			$modelo = new conexion();
			$conexion = $modelo->get_conexion();
			$resultDoc = $conexion->prepare($sqlDoc);
			$resultDoc->bindParam(':idusu', $idusu);
			$resultDoc->execute();
			$usuario = $resultDoc->fetch(PDO::FETCH_ASSOC);
			
			if (!$usuario) {
				error_log("DEBUG getCompetenciasPorInstructor: No se encontró el usuario con ID: " . $idusu);
				return [];
			}
			
			$ndocInstructor = $usuario['ndocusu'];
			error_log("DEBUG getCompetenciasPorInstructor: Documento del instructor: " . $ndocInstructor);
			
			// Buscar competencias a través de resultado -> competencia usando ndocins
			$sql = "SELECT DISTINCT 
						c.idcom, 
						c.descom as compe
					FROM juicio j
					LEFT JOIN resultado r ON j.idres = r.idres
					LEFT JOIN competencia c ON r.idcom = c.idcom
					WHERE j.ndocins = :ndocInstructor
					AND c.idcom IS NOT NULL
					ORDER BY c.descom";
			
			error_log("DEBUG getCompetenciasPorInstructor: SQL (por ndocins): " . $sql);
			
			$result = $conexion->prepare($sql);
			$result->bindParam(':ndocInstructor', $ndocInstructor);
			$result->execute();
			
			$competencias = $result->fetchAll(PDO::FETCH_ASSOC);
			error_log("DEBUG getCompetenciasPorInstructor: Resultado (por ndocins): " . count($competencias) . " competencias encontradas");
			
			// Si no encuentra nada, buscar todas las competencias disponibles (fallback)
			if (count($competencias) == 0) {
				error_log("DEBUG getCompetenciasPorInstructor: No se encontraron por ndocins, buscando todas las competencias...");
				
				$sqlFallback = "SELECT DISTINCT 
									c.idcom, 
									c.descom as compe
								FROM competencia c
								WHERE c.idcom IS NOT NULL
								ORDER BY c.descom
								LIMIT 10";
				
				error_log("DEBUG getCompetenciasPorInstructor: SQL (fallback): " . $sqlFallback);
				
				$resultFallback = $conexion->prepare($sqlFallback);
				$resultFallback->execute();
				
				$competencias = $resultFallback->fetchAll(PDO::FETCH_ASSOC);
				error_log("DEBUG getCompetenciasPorInstructor: Resultado (fallback): " . count($competencias) . " competencias encontradas");
			}
			
			if ($competencias) {
				error_log("DEBUG getCompetenciasPorInstructor: Datos finales: " . print_r($competencias, true));
			}
			
			return $competencias;
		} catch (Exception $e) {
			error_log("ERROR getCompetenciasPorInstructor: " . $e->getMessage());
			return [];
		}
	}

	/**
	 * Obtener resultados de aprendizaje usados por un instructor específico
	 */
	function getResultadosPorInstructor($idusu) {
		try {
			error_log("DEBUG getResultadosPorInstructor: Buscando resultados para instructor ID: " . $idusu);
			
			// Obtener número de documento del instructor
			$sqlDoc = "SELECT ndocusu FROM usuario WHERE idusu = :idusu";
			$modelo = new conexion();
			$conexion = $modelo->get_conexion();
			$resultDoc = $conexion->prepare($sqlDoc);
			$resultDoc->bindParam(':idusu', $idusu);
			$resultDoc->execute();
			$usuario = $resultDoc->fetch(PDO::FETCH_ASSOC);
			
			if (!$usuario) {
				error_log("DEBUG getResultadosPorInstructor: No se encontró el usuario con ID: " . $idusu);
				return [];
			}
			
			$ndocInstructor = $usuario['ndocusu'];
			error_log("DEBUG getResultadosPorInstructor: Documento del instructor: " . $ndocInstructor);
			
			// Buscar resultados directamente en juicio usando ndocins
			$sql = "SELECT DISTINCT 
						r.idres, 
						r.nomres as resapr
					FROM juicio j
					LEFT JOIN resultado r ON j.idres = r.idres
					WHERE j.ndocins = :ndocInstructor
					AND r.idres IS NOT NULL
					ORDER BY r.nomres";
			
			error_log("DEBUG getResultadosPorInstructor: SQL (por ndocins): " . $sql);
			
			$result = $conexion->prepare($sql);
			$result->bindParam(':ndocInstructor', $ndocInstructor);
			$result->execute();
			
			$resultados = $result->fetchAll(PDO::FETCH_ASSOC);
			error_log("DEBUG getResultadosPorInstructor: Resultado (por ndocins): " . count($resultados) . " resultados encontrados");
			
			// Si no encuentra nada, buscar todos los resultados disponibles (fallback)
			if (count($resultados) == 0) {
				error_log("DEBUG getResultadosPorInstructor: No se encontraron por ndocins, buscando todos los resultados...");
				
				$sqlFallback = "SELECT DISTINCT 
									r.idres, 
									r.nomres as resapr
								FROM resultado r
								WHERE r.idres IS NOT NULL
								ORDER BY r.nomres
								LIMIT 10";
				
				error_log("DEBUG getResultadosPorInstructor: SQL (fallback): " . $sqlFallback);
				
				$resultFallback = $conexion->prepare($sqlFallback);
				$resultFallback->execute();
				
				$resultados = $resultFallback->fetchAll(PDO::FETCH_ASSOC);
				error_log("DEBUG getResultadosPorInstructor: Resultado (fallback): " . count($resultados) . " resultados encontrados");
			}
			
			if ($resultados) {
				error_log("DEBUG getResultadosPorInstructor: Datos finales: " . print_r($resultados, true));
			}
			
			return $resultados;
		} catch (Exception $e) {
			error_log("ERROR getResultadosPorInstructor: " . $e->getMessage());
			return [];
		}
	}

	/**
	 * Cargar información automática del usuario
	 */
	function cargarInfoUsuario($idusu) {
		try {
			error_log("DEBUG cargarInfoUsuario: Buscando usuario con ID: " . $idusu);
			$sql = "SELECT idusu, ndocusu, nomusu, idper FROM usuario WHERE idusu = :idusu";
			error_log("DEBUG cargarInfoUsuario: SQL: " . $sql);
			
			$modelo = new conexion();
			$conexion = $modelo->get_conexion();
			$result = $conexion->prepare($sql);
			$result->bindParam(':idusu', $idusu);
			$result->execute();
			
			$usuario = $result->fetch(PDO::FETCH_ASSOC);
			error_log("DEBUG cargarInfoUsuario: Resultado: " . ($usuario ? 'ENCONTRADO' : 'NO ENCONTRADO'));
			if ($usuario) {
				error_log("DEBUG cargarInfoUsuario: Datos: " . print_r($usuario, true));
			}
			
			return $usuario;
		} catch (Exception $e) {
			ManejoError($e);
			return null;
		}
	}

	/**
	 * Obtiene competencias que han sido usadas en juicios (para el formulario)
	 */
	function getCompetenciasUsadas() {
		try {
			$sql = "SELECT DISTINCT c.idcom, c.descom 
					FROM competencia c
					INNER JOIN resultado r ON c.idcom = r.idcom
					INNER JOIN juicio j ON r.idres = j.idres
					ORDER BY c.idcom";
			$db = (new conexion())->get_conexion();
			$st = $db->prepare($sql);
			$st->execute();
			return $st->fetchAll(PDO::FETCH_ASSOC);
		} catch (Exception $e) {
			ManejoError($e);
			return [];
		}
	}

	/**
	 * Obtiene resultados de aprendizaje que han sido usados en juicios (para el formulario)
	 */
	function getResultadosUsados() {
		try {
			$sql = "SELECT DISTINCT r.idres, r.nomres 
					FROM resultado r
					INNER JOIN juicio j ON r.idres = j.idres
					ORDER BY r.idres";
			$db = (new conexion())->get_conexion();
			$st = $db->prepare($sql);
			$st->execute();
			return $st->fetchAll(PDO::FETCH_ASSOC);
		} catch (Exception $e) {
			ManejoError($e);
			return [];
		}
	}

	/**
	 * Busca datos básicos del alumno por documento (para precargar nombres/apellidos/ficha)
	 */
	function cargarInfoUsuarioPorDocumento($ndoce) {
		try {
		$sql = "SELECT idusu, 
				SUBSTRING_INDEX(nomusu, ' ', 2) as nomes, 
				SUBSTRING_INDEX(nomusu, ' ', -2) as apees, 
				ndocusu
				FROM usuario
				WHERE ndocusu = :ndoce
				LIMIT 1";
			$db = (new conexion())->get_conexion();
			$st = $db->prepare($sql);
			$st->bindParam(':ndoce', $ndoce);
			$st->execute();
			return $st->fetch(PDO::FETCH_ASSOC) ?: null;
		} catch (Exception $e) {
			ManejoError($e);
			return null;
	}
}

	/**
	 * Valida que un resultado pertenezca a una competencia específica
	 * @param string $idcom - ID de la competencia
	 * @param string $idres - ID del resultado
	 * @return bool - true si la relación es válida
	 */
	public function validarRelacionCompetenciaResultado($idcom, $idres) {
		try {
			$sql = "SELECT COUNT(*) FROM resultado WHERE idres = ? AND idcom = ?";
			$db = (new conexion())->get_conexion();
			$stmt = $db->prepare($sql);
			$stmt->execute([$idres, $idcom]);
			return $stmt->fetchColumn() > 0;
		} catch (Exception $e) {
			ManejoError($e);
			return false;
		}
	}

	/**
	 * Obtiene resultados de una competencia específica
	 * @param string $idcom - ID de la competencia
	 * @return array - Lista de resultados de la competencia
	 */
	public function getResultadosPorCompetencia($idcom) {
		try {
			$sql = "SELECT idres, nomres FROM resultado WHERE idcom = ? ORDER BY nomres";
			$db = (new conexion())->get_conexion();
			$stmt = $db->prepare($sql);
			$stmt->execute([$idcom]);
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch (Exception $e) {
			ManejoError($e);
			return [];
		}
	}

	/**
	 * Carga la ficha del instructor desde la tabla usufic
	 */
	function cargarFichaInstructor($idusu) {
		try {
			error_log("DEBUG cargarFichaInstructor: Buscando ficha para usuario ID: " . $idusu);
			$sql = "SELECT f.*, p.nompro, p.despro, p.verpro, p.horlpro, p.horppro, p.crelpro, p.creppro, p.tippro
					FROM usufic uf
					LEFT JOIN ficha f ON uf.idfic = f.idfic
					LEFT JOIN programa p ON f.codpro = p.codpro
					WHERE uf.idusu = :idusu AND uf.actfic = 1
					LIMIT 1";
			error_log("DEBUG cargarFichaInstructor: SQL: " . $sql);
			
			$db = (new conexion())->get_conexion();
			$st = $db->prepare($sql);
			$st->bindParam(':idusu', $idusu);
			$st->execute();
			
			$ficha = $st->fetch(PDO::FETCH_ASSOC);
			error_log("DEBUG cargarFichaInstructor: Resultado: " . ($ficha ? 'ENCONTRADO' : 'NO ENCONTRADO'));
			if ($ficha) {
				error_log("DEBUG cargarFichaInstructor: Datos: " . print_r($ficha, true));
			} else {
				error_log("DEBUG cargarFichaInstructor: No se encontró ficha para usuario: " . $idusu);
				
				// Verificar si hay registros en usufic para este usuario
				$sqlCheck = "SELECT * FROM usufic WHERE idusu = :idusu";
				$stCheck = $db->prepare($sqlCheck);
				$stCheck->bindParam(':idusu', $idusu);
				$stCheck->execute();
				$registros = $stCheck->fetchAll(PDO::FETCH_ASSOC);
				error_log("DEBUG cargarFichaInstructor: Registros en usufic para usuario " . $idusu . ": " . count($registros));
				if (count($registros) > 0) {
					error_log("DEBUG cargarFichaInstructor: Registros encontrados: " . print_r($registros, true));
				}
			}
			
			return $ficha ?: null;
		} catch (Exception $e) {
			ManejoError($e);
			return null;
		}
	}

	/**
	 * Procesa un registro individual sin transacción (para procesamiento en lote)
	 * VERSIÓN ACTUALIZADA CON IDCOM - 2025-09-17 00:06
	 */
	public function procesarRegistroEnLote($fila, $idfic, $archivoOriginal, $fechaReporte = null, $db = null) {
		// LLAMAR AL MÉTODO ACTUALIZADO
		return $this->procesarRegistroEnLoteV2($fila, $idfic, $archivoOriginal, $fechaReporte, $db);
	}

	/**
	 * VERSIÓN ACTUALIZADA DEL PROCESAMIENTO EN LOTE CON IDCOM
	 * 2025-09-17 00:06 - INCLUYE IDCOM EN INSERT/UPDATE
	 * VERSIÓN CORREGIDA - 2025-09-17 00:08
	 */
	public function procesarRegistroEnLoteV2($fila, $idfic, $archivoOriginal, $fechaReporte = null, $db = null) {
		if (!$db) {
			$db = (new conexion())->get_conexion();
		}
		
		// === 1. PROCESAR COMPETENCIA ===
		$competencia = trim($fila['Competencia']);
		$partesCompetencia = explode(" - ", $competencia, 2);
		$idcom = trim($partesCompetencia[0]);
		$descom = isset($partesCompetencia[1]) ? trim($partesCompetencia[1]) : '';
		
		// Verificar si la competencia ya existe
		$stmt = $db->prepare("SELECT idcom FROM competencia WHERE idcom = ?");
		$stmt->execute([$idcom]);
		if ($stmt->rowCount() == 0) {
			// Crear nueva competencia
			$stmt = $db->prepare("INSERT INTO competencia (idcom, descom, vercom, horcom, idval) VALUES (?, ?, 1, 40, 1)");
			$stmt->execute([$idcom, $descom]);
		}

		// === 2. PROCESAR RESULTADO ===
		$resultado = trim($fila['Resultado de Aprendizaje']);
		$partesResultado = explode(" - ", $resultado, 2);
		$idres = trim($partesResultado[0]);
		$nomres = isset($partesResultado[1]) ? trim($partesResultado[1]) : '';
			
		// Verificar si el resultado ya existe
		$stmt = $db->prepare("SELECT idres FROM resultado WHERE idres = ?");
		$stmt->execute([$idres]);
		if ($stmt->rowCount() == 0) {
			// Crear nuevo resultado
			$stmt = $db->prepare("INSERT INTO resultado (idres, nomres, idcom, ndeses) VALUES (?, ?, ?, 1)");
			$stmt->execute([$idres, $nomres, $idcom]);
		}

		// === 3. OBTENER ID DEL USUARIO ESTUDIANTE ===
		$ndoce = trim($fila['Número de Documento']);
		
		// Validar que el documento no esté vacío
		if (empty($ndoce)) {
			throw new Exception("Número de documento vacío");
		}
		
		$stmt = $db->prepare("SELECT idusu FROM usuario WHERE ndocusu = ?");
		$stmt->execute([$ndoce]);
		$idusu = $stmt->fetchColumn();

		if (!$idusu) {
			throw new Exception("Usuario con documento $ndoce no encontrado");
		}

		// === 4. PROCESAR FUNCIONARIO INSTRUCTOR ===
		$funcionario = trim($fila['Funcionario que registro el juicio evaluativo']);
		$ndocins = null;
		
		// Solo extraer ndocins si el funcionario no está vacío o es solo espacios/guiones
		if (!empty($funcionario) && !preg_match('/^[\s\-]+$/', $funcionario)) {
			preg_match('/(\d+)/', $funcionario, $matches);
			$ndocins = isset($matches[1]) ? $matches[1] : null;
		}

		// === 5. VALIDAR DUPLICADOS ===
		// Verificar si ya existe un juicio para este usuario con el mismo resultado y competencia
		$stmt = $db->prepare("SELECT j.idjui FROM juicio j 
							  WHERE j.idusu = ? AND j.idres = ? AND j.idcom = ?");
		$stmt->execute([$idusu, $idres, $idcom]);
		
		$juicioExistente = $stmt->fetch(PDO::FETCH_ASSOC);
		$esActualizacion = ($juicioExistente !== false);

		// === 6. CREAR JUICIO ===
		$caljui = trim($fila['Juicio de Evaluación']);
		
		// Procesar fecha de reporte
		$fechaReporteSQL = null;
		if ($fechaReporte) {
			// Convertir formato DD/MM/YYYY a YYYY-MM-DD
			$fecha = DateTime::createFromFormat('d/m/Y', $fechaReporte);
			if ($fecha !== false) {
				$fechaReporteSQL = $fecha->format('Y-m-d');
			}
		}
		
		// Fecha de importación (ahora)
		$fechaImportacionSQL = date('Y-m-d H:i:s');
		
		// Archivo correlativo
		$archivoCorrelativoFinal = basename($archivoOriginal);
		
		// Fecha del juicio evaluativo (procesar fecha del CSV)
		$fechaJuicioSQL = null;
		if (isset($fila['Fecha y Hora del Juicio Evaluativo']) && !empty(trim($fila['Fecha y Hora del Juicio Evaluativo']))) {
			$fechaStr = trim($fila['Fecha y Hora del Juicio Evaluativo']);
			// Solo procesar si no es solo espacios o guiones
			if (!preg_match('/^[\s\-]+$/', $fechaStr)) {
				$fechaJuicioSQL = $this->convertirFechaExcel($fechaStr);
			}
		}
		
		if ($esActualizacion) {
			// ACTUALIZAR juicio existente
			$stmt = $db->prepare("UPDATE juicio SET caljui = ?, ndocins = ?, fecha_reporte = ?, fecha_importacion = ?, archivo_correlativo = ?, fecjuieva = ?, idcom = ? WHERE idjui = ?");
			$stmt->execute([$caljui, $ndocins, $fechaReporteSQL, $fechaImportacionSQL, $archivoCorrelativoFinal, $fechaJuicioSQL, $idcom, $juicioExistente['idjui']]);
			$idjui = $juicioExistente['idjui'];
		} else {
			// INSERTAR nuevo juicio
			$stmt = $db->prepare("INSERT INTO juicio (idusu, idfic, idres, caljui, ndocins, fecha_reporte, fecha_importacion, archivo_correlativo, fecjuieva, idcom) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
			$stmt->execute([$idusu, $idfic, $idres, $caljui, $ndocins, $fechaReporteSQL, $fechaImportacionSQL, $archivoCorrelativoFinal, $fechaJuicioSQL, $idcom]);
			$idjui = $db->lastInsertId();
		}

		// === 7. PROCESAR APROBACION_RESULTADO ===
		// Buscar idinstructor usando el ndocins ya extraído
		$idinstructor = null;
		if ($ndocins) {
			$stmt = $db->prepare("SELECT idusu FROM usuario WHERE ndocusu = ?");
			$stmt->execute([$ndocins]);
			$idinstructor = $stmt->fetchColumn();
		}

		// Determinar estado basado en el juicio
		$estado = 'pendiente';
		if (strtoupper($caljui) === 'APROBADO') {
			$estado = 'aprobado';
		} elseif (strtoupper($caljui) === 'NO APROBADO') {
			$estado = 'no aprobado';
		}

		// Verificar si ya existe aprobación para esta ficha y resultado
		$stmt = $db->prepare("SELECT idaprob FROM aprobacion_resultado WHERE idfic = ? AND idres = ?");
		$stmt->execute([$idfic, $idres]);
		if ($stmt->rowCount() == 0 && $idinstructor) {
			// Crear nueva aprobación con ndocins
			$stmt = $db->prepare("INSERT INTO aprobacion_resultado (idfic, idres, idinstructor, ndocins, estado, fecha_aprobacion) VALUES (?, ?, ?, ?, ?, ?)");
			$stmt->execute([$idfic, $idres, $idinstructor, $ndocins, $estado, $fechaJuicioSQL]);
		}

		return [
			'success' => true,
			'idjui' => $idjui,
			'accion' => $esActualizacion ? 'actualizado' : 'insertado',
			'competencia' => ['idcom' => $idcom, 'descom' => $descom],
			'resultado' => ['idres' => $idres, 'nomres' => $nomres],
			'usuario' => ['idusu' => $idusu, 'ndoce' => $ndoce],
			'aprobacion' => ['idinstructor' => $idinstructor, 'ndocins' => $ndocins, 'estado' => $estado, 'fecha' => $fechaJuicioSQL]
		];
	}

	public function importarDesdeCSV($fila, $idfic, $archivoOriginal, $fechaReporte = null, $archivoCorrelativo = null) {
		$db = (new conexion())->get_conexion();
		$db->beginTransaction();
		
		try {
			// === 1. PROCESAR COMPETENCIA ===
			// Extraer ID y descripción de la competencia
			$competencia = trim($fila['Competencia']);
			$partesCompetencia = explode(" - ", $competencia, 2);
			$idcom = trim($partesCompetencia[0]);
			$descom = isset($partesCompetencia[1]) ? trim($partesCompetencia[1]) : '';
			
			// Verificar si la competencia ya existe
    $stmt = $db->prepare("SELECT idcom FROM competencia WHERE idcom = ?");
    $stmt->execute([$idcom]);
    if ($stmt->rowCount() == 0) {
				// Crear nueva competencia
        $stmt = $db->prepare("INSERT INTO competencia (idcom, descom, vercom, horcom, idval) VALUES (?, ?, 1, 40, 1)");
        $stmt->execute([$idcom, $descom]);
    }

		// === 2. PROCESAR RESULTADO ===
		// Extraer ID y nombre del resultado
		$resultado = trim($fila['Resultado de Aprendizaje']);
		$partesResultado = explode(" - ", $resultado, 2);
		$idres = trim($partesResultado[0]); // Solo el número: 593151
		$nomres = isset($partesResultado[1]) ? trim($partesResultado[1]) : ''; // Todo después del primer -
			
			// Verificar si el resultado ya existe
    $stmt = $db->prepare("SELECT idres FROM resultado WHERE idres = ?");
    $stmt->execute([$idres]);
    if ($stmt->rowCount() == 0) {
				// Crear nuevo resultado
        $stmt = $db->prepare("INSERT INTO resultado (idres, nomres, idcom, ndeses) VALUES (?, ?, ?, 1)");
        $stmt->execute([$idres, $nomres, $idcom]);
    }

			// === 3. OBTENER ID DEL USUARIO ESTUDIANTE ===
			$ndoce = trim($fila['Número de Documento']);
			
			// Validar que el documento no esté vacío
			if (empty($ndoce)) {
				throw new Exception("Número de documento vacío");
			}
			
			$stmt = $db->prepare("SELECT idusu FROM usuario WHERE ndocusu = ?");
			$stmt->execute([$ndoce]);
			$idusu = $stmt->fetchColumn();

			if (!$idusu) {
				throw new Exception("Usuario con documento $ndoce no encontrado");
			}

			// === 4. PROCESAR FUNCIONARIO INSTRUCTOR ===
			// Extraer número de documento del funcionario
			$funcionario = trim($fila['Funcionario que registro el juicio evaluativo']);
			$ndocins = null;
			
			// Solo extraer ndocins si el funcionario no está vacío o es solo espacios/guiones
			if (!empty($funcionario) && !preg_match('/^[\s\-]+$/', $funcionario)) {
				preg_match('/(\d+)/', $funcionario, $matches);
				$ndocins = isset($matches[1]) ? $matches[1] : null;
			}

			// === 5. CREAR JUICIO ===
			$caljui = trim($fila['Juicio de Evaluación']);
			
			// Procesar fecha de reporte
			$fechaReporteSQL = null;
			if ($fechaReporte) {
				// Convertir formato DD/MM/YYYY a YYYY-MM-DD
				$fecha = DateTime::createFromFormat('d/m/Y', $fechaReporte);
				if ($fecha !== false) {
					$fechaReporteSQL = $fecha->format('Y-m-d');
				}
			}
			
			// Fecha de importación (ahora)
			$fechaImportacionSQL = date('Y-m-d H:i:s');
			
			// Archivo correlativo
			$archivoCorrelativoFinal = $archivoCorrelativo ?: basename($archivoOriginal);
			
			// Fecha del juicio evaluativo (procesar fecha del CSV)
			$fechaJuicioSQL = null;
			if (isset($fila['Fecha y Hora del Juicio Evaluativo']) && !empty(trim($fila['Fecha y Hora del Juicio Evaluativo']))) {
				$fechaStr = trim($fila['Fecha y Hora del Juicio Evaluativo']);
				// Solo procesar si no es solo espacios o guiones
				if (!preg_match('/^[\s\-]+$/', $fechaStr)) {
					$fechaJuicioSQL = $this->convertirFechaExcel($fechaStr);
				}
			}
			
			$stmt = $db->prepare("INSERT INTO juicio (idusu, idfic, idres, caljui, ndocins, fecha_reporte, fecha_importacion, archivo_correlativo, fecjuieva) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
			$stmt->execute([$idusu, $idfic, $idres, $caljui, $ndocins, $fechaReporteSQL, $fechaImportacionSQL, $archivoCorrelativoFinal, $fechaJuicioSQL]);
			$idjui = $db->lastInsertId();

			// === 6. PROCESAR APROBACION_RESULTADO ===
			// Buscar idinstructor usando el ndocins ya extraído
			$idinstructor = null;
			if ($ndocins) {
				$stmt = $db->prepare("SELECT idusu FROM usuario WHERE ndocusu = ?");
				$stmt->execute([$ndocins]);
				$idinstructor = $stmt->fetchColumn();
			}

			// Procesar fecha del juicio evaluativo
			$fechaStr = trim($fila['Fecha y Hora del Juicio Evaluativo']);
			$fechaJuicioSQL = null;
			if ($fechaStr && $fechaStr !== '') {
				// Intentar diferentes formatos de fecha
				$formatos = ['d/m/Y H.i a', 'd/m/Y H:i a', 'Y-m-d H:i:s', 'd/m/Y'];
				foreach ($formatos as $formato) {
					$fecha = DateTime::createFromFormat($formato, $fechaStr);
					if ($fecha !== false) {
						$fechaJuicioSQL = $fecha->format('Y-m-d H:i:s');
						break;
					}
				}
			}

			// Determinar estado basado en el juicio
			$estado = 'pendiente';
			if (strtoupper($caljui) === 'APROBADO') {
				$estado = 'aprobado';
			} elseif (strtoupper($caljui) === 'NO APROBADO') {
				$estado = 'no aprobado';
			}

			// Verificar si ya existe aprobación para esta ficha y resultado
    $stmt = $db->prepare("SELECT idaprob FROM aprobacion_resultado WHERE idfic = ? AND idres = ?");
    $stmt->execute([$idfic, $idres]);
			if ($stmt->rowCount() == 0 && $idinstructor) {
				// Crear nueva aprobación con ndocins
        $stmt = $db->prepare("INSERT INTO aprobacion_resultado (idfic, idres, idinstructor, ndocins, estado, fecha_aprobacion) VALUES (?, ?, ?, ?, ?, ?)");
				$stmt->execute([$idfic, $idres, $idinstructor, $ndocins, $estado, $fechaJuicioSQL]);
			}

			$db->commit();
			return [
				'success' => true,
				'idjui' => $idjui,
				'competencia' => ['idcom' => $idcom, 'descom' => $descom],
				'resultado' => ['idres' => $idres, 'nomres' => $nomres],
				'usuario' => ['idusu' => $idusu, 'ndoce' => $ndoce],
				'aprobacion' => ['idinstructor' => $idinstructor, 'ndocins' => $ndocins, 'estado' => $estado, 'fecha' => $fechaJuicioSQL]
			];

		} catch (Exception $e) {
			$db->rollback();
			throw new Exception("Error al importar registro: " . $e->getMessage());
		}
	}

	// Normalizador simple
	public function normalizar_utf8($v) {
    if ($v === null) return '';
    return trim(mb_convert_encoding($v, 'UTF-8', 'UTF-8,ISO-8859-1'));
}

/**
	 * Procesa la importación desde los datos en sesión
 */
public function procesarImportacionBD_desdeSession($idfic, $nombreArchivo) {
		if (!isset($_SESSION['excel_data'])) {
			throw new Exception('No hay datos de Excel en sesión');
		}
    $excelData = $_SESSION['excel_data'];
    return $this->procesarImportacionBD($excelData, $idfic, $nombreArchivo);
}

/**
	 * Procesa un archivo CSV y extrae los datos de juicios
	 */
	public function procesarCSVJuicios($archivoCSV, $idfic) {
		$resultado = [
			'total_filas' => 0,
			'procesadas' => 0,
			'error' => 0,
			'errores' => [],
			'datos_procesados' => []
		];

		if (!file_exists($archivoCSV)) {
			throw new Exception("El archivo CSV no existe: $archivoCSV");
		}

		$handle = fopen($archivoCSV, 'r');
		if (!$handle) {
			throw new Exception("No se pudo abrir el archivo CSV");
		}

		$encabezados = null;
		$numeroFila = 0;

		while (($fila = fgetcsv($handle, 0, ',')) !== FALSE) {
			$numeroFila++;
			
			// Buscar la fila de encabezados
			if ($encabezados === null && isset($fila[0]) && $fila[0] === 'Tipo de Documento') {
				$encabezados = $fila;
				continue;
			}
			
			// Si no tenemos encabezados, continuar
			if ($encabezados === null) {
				continue;
			}

			// Saltar filas vacías
			if (empty(array_filter($fila))) {
				continue;
			}

			$resultado['total_filas']++;

			try {
				// Crear array asociativo con los datos
				$datosFila = [];
				for ($i = 0; $i < count($encabezados); $i++) {
					$datosFila[$encabezados[$i]] = isset($fila[$i]) ? trim($fila[$i], '"') : '';
				}

				// Validar datos mínimos
				if (empty($datosFila['Número de Documento']) || empty($datosFila['Competencia']) || empty($datosFila['Resultado de Aprendizaje'])) {
					$resultado['error']++;
					$resultado['errores'][] = [
						'fila' => $numeroFila,
						'datos' => $datosFila,
						'error' => 'Faltan datos obligatorios: Número de Documento, Competencia o Resultado de Aprendizaje'
					];
					continue;
				}

				// Procesar la fila
				$resultadoImportacion = $this->importarDesdeCSV($datosFila, $idfic, $archivoCSV);
				$resultado['procesadas']++;
				$resultado['datos_procesados'][] = [
					'fila' => $numeroFila,
					'datos' => $datosFila,
					'resultado' => $resultadoImportacion
				];

			} catch (Exception $e) {
				$resultado['error']++;
				$resultado['errores'][] = [
					'fila' => $numeroFila,
					'datos' => isset($datosFila) ? $datosFila : $fila,
					'error' => $e->getMessage()
				];
			}
		}

		fclose($handle);

		// Guardar resultado en sesión
		$_SESSION['resultado_importacion'] = $resultado;

		return $resultado;
	}

	/**
	 * Procesa la importación completa desde Excel a BD
	 */
	public function procesarImportacionBD($excelData, $idfic, $nombreArchivo) {
		// Si no se proporciona idfic, extraerlo de los metadatos del archivo
		if (empty($idfic) && count($excelData) >= 3) {
			$idfic_raw = isset($excelData[2][2]) ? trim($excelData[2][2]) : null;
			// Buscar la ficha con variantes (A, B, etc.)
			$idfic = $this->buscarFichaPorIdConVariantes($idfic_raw);
			if (!$idfic) {
				error_log("⚠️ No se encontró ficha para: $idfic_raw");
				$idfic = $idfic_raw; // Usar el valor original si no se encuentra
			} else {
				error_log("✅ Ficha encontrada: $idfic_raw → $idfic");
			}
		}
		$resultado = [
			'total_filas' => count($excelData),
			'procesadas' => 0,
			'error' => 0,
			'insertados' => 0,
			'actualizados' => 0,
			'omitidos' => 0,
			'errores' => []
		];

		// Si no hay datos, retornar resultado vacío
		if (empty($excelData) || count($excelData) < 14) {
			$_SESSION['resultado_importacion'] = $resultado;
			return $resultado;
		}

		// === EXTRAER FECHA DE REPORTE DE LOS METADATOS ===
		$fechaReporte = null;
		if (isset($excelData[1]) && is_array($excelData[1])) {
			// Línea 2: ["Fecha del Reporte:", "", "29/07/2025", "", "", ...]
			if (isset($excelData[1][2]) && !empty(trim($excelData[1][2]))) {
				$fechaReporte = trim($excelData[1][2]);
			}
		}

		// Saltar las primeras 12 líneas (metadatos del reporte)
		$dataSinMetadatos = array_slice($excelData, 12);
		
		// La línea 13 (índice 0 del array recortado) son los encabezados
		$header = $dataSinMetadatos[0];
		$header = array_map('trim', $header);
		
		// Buscar posiciones de columnas importantes
		$find = function($label) use ($header){
			$i = array_search($label, $header);
			return ($i === false ? null : $i);
		};

		$col_TD   = $find('Tipo de Documento');
		$col_NDOC = $find('Número de Documento');
		$col_NOM  = $find('Nombre');
		$col_APE  = $find('Apellidos');
		$col_EST  = $find('Estado');
		$col_COMP = $find('Competencia');
		$col_RES  = $find('Resultado de Aprendizaje');
		$col_JUI  = $find('Juicio de Evaluación');
		$col_FEJ  = $find('Fecha y Hora del Juicio Evaluativo');
		$col_FUN  = $find('Funcionario que registro el juicio evaluativo');

		// Procesar todas las líneas de datos (desde la línea 14 en adelante)
		$datosReales = array_slice($dataSinMetadatos, 1); // Saltar solo encabezados, procesar todas las líneas restantes
		
		// === PROCESAMIENTO EN LOTE CON UNA SOLA TRANSACCIÓN ===
		$db = (new conexion())->get_conexion();
		$db->beginTransaction();
		
		try {
			foreach ($datosReales as $index => $row) {
				try {
					// Limpiar datos de la fila
					$row = array_map('trim', $row);
					
					// Obtener datos importantes
					$doc   = isset($col_NDOC) && $col_NDOC !== null ? ($row[$col_NDOC] ?? '') : '';
					$comp  = isset($col_COMP) && $col_COMP !== null ? ($row[$col_COMP] ?? '') : '';
					$res   = isset($col_RES)  && $col_RES  !== null ? ($row[$col_RES]  ?? '') : '';

					// Saltar filas vacías
					if ($doc === '' && $comp === '' && $res === '') {
						continue;
					}

					// Convertir fila numérica a asociativa (formato que espera importarDesdeCSV)
					$fila = [
						'Tipo de Documento'                           => ($col_TD !== null ? ($row[$col_TD] ?? '') : ''),
						'Número de Documento'                         => $doc,
						'Nombre'                                      => ($col_NOM !== null ? ($row[$col_NOM] ?? '') : ''),
						'Apellidos'                                   => ($col_APE !== null ? ($row[$col_APE] ?? '') : ''),
						'Estado'                                      => ($col_EST !== null ? ($row[$col_EST] ?? '') : ''),
						'Competencia'                                 => $comp,
						'Resultado de Aprendizaje'                    => $res,
						'Juicio de Evaluación'                        => ($col_JUI !== null ? ($row[$col_JUI] ?? '') : ''),
						'Fecha y Hora del Juicio Evaluativo'          => ($col_FEJ !== null ? ($row[$col_FEJ] ?? '') : ''),
						'Funcionario que registro el juicio evaluativo'=> ($col_FUN !== null ? ($row[$col_FUN] ?? '') : ''),
					];

					// Procesar registro sin transacción individual
					$resultadoRegistro = $this->procesarRegistroEnLote($fila, $idfic, $nombreArchivo, $fechaReporte, $db);
					$resultado['procesadas']++;
					
					// Contar según la acción realizada
					if ($resultadoRegistro['accion'] === 'insertado') {
						$resultado['insertados']++;
					} else {
						$resultado['actualizados'] = ($resultado['actualizados'] ?? 0) + 1;
					}
					
				} catch (Exception $e) {
					$resultado['error']++;
					$resultado['omitidos']++;
					$resultado['errores'][] = [
						'fila_numero' => $index + 14, // +14 porque empezamos desde la línea 14
						'fila' => $row,
						'error' => $e->getMessage()
					];
					// Continuar procesando otros registros
				}
			}
			
			// Commit de toda la transacción
			$db->commit();
			
		} catch (Exception $e) {
			// Rollback en caso de error general
			$db->rollback();
			throw new Exception("Error en procesamiento masivo: " . $e->getMessage());
		}

		// Guardar resultado en sesión para la vista
		$_SESSION['resultado_importacion'] = $resultado;

		return $resultado;
	}

	public function obtenerResultadoImportacion() {
		if (!isset($_SESSION['resultado_importacion'])) {
			return null;
		}

		return $_SESSION['resultado_importacion'];
	}

	public function buscarJuicioPorDocumento($ndoce) {
		try {
		$sql = "SELECT 
			j.idjui, j.idusu, j.idfic, j.idres, j.caljui,
			u.ndocusu as ndoce, 
			SUBSTRING_INDEX(u.nomusu, ' ', 2) as nomes, 
			SUBSTRING_INDEX(u.nomusu, ' ', -2) as apees, 
			u.tdousu,
			f.nomfic, f.mun, f.finific, f.ffinfic,
			r.nomres as resapr, r.ndeses,
			c.idcom, c.descom as compe,
			ar.idaprob, ar.idinstructor, ar.estado, ar.fecha_aprobacion,
			ins.nomusu as funre
		FROM juicio j
		LEFT JOIN usuario u ON j.idusu = u.idusu
		LEFT JOIN ficha f ON j.idfic = f.idfic  
		LEFT JOIN resultado r ON j.idres = r.idres
		LEFT JOIN competencia c ON r.idcom = c.idcom
		LEFT JOIN aprobacion_resultado ar ON (j.idfic = ar.idfic AND j.idres = ar.idres)
		LEFT JOIN usuario ins ON ar.idinstructor = ins.idusu
		WHERE u.ndocusu = :ndoce
		ORDER BY j.fecjuieva DESC, j.idjui DESC
		LIMIT 1";
			$db = (new conexion())->get_conexion();
			$st = $db->prepare($sql);
			$st->bindParam(':ndoce', $ndoce);
			$st->execute();
			return $st->fetch(PDO::FETCH_ASSOC) ?: null;
		} catch (Exception $e) {
			ManejoError($e);
			return null;
		}
	}

	// ============================================================================
	// MÉTODOS AUXILIARES PARA LA NUEVA LÓGICA DE 4 TABLAS
	// ============================================================================

	/**
	 * Verifica si existe una competencia, si no existe la crea
	 * @param string $idcom - ID de la competencia
	 * @param string $descom - Descripción de la competencia
	 * @return bool - true si existe o se creó correctamente
	 */
	public function verificarOCrearCompetencia($idcom, $descom) {
		try {
			$db = (new conexion())->get_conexion();
			
			// Verificar si existe
			$stmt = $db->prepare("SELECT idcom FROM competencia WHERE idcom = ?");
			$stmt->execute([$idcom]);
			
			if ($stmt->rowCount() == 0) {
				// No existe, crearla
				$stmt = $db->prepare("INSERT INTO competencia (idcom, descom, vercom, horcom, idval) VALUES (?, ?, 1, 40, 1)");
				$stmt->execute([$idcom, $descom]);
			}
			
			return true;
		} catch (Exception $e) {
			ManejoError($e);
			return false;
		}
	}

	/**
	 * Verifica si existe un resultado, si no existe lo crea
	 * @param string $idres - ID del resultado
	 * @param string $nomres - Nombre del resultado
	 * @param string $idcom - ID de la competencia relacionada
	 * @return bool - true si existe o se creó correctamente
	 */
	public function verificarOCrearResultado($idres, $nomres, $idcom) {
		try {
			$db = (new conexion())->get_conexion();
			
			// Verificar si existe
			$stmt = $db->prepare("SELECT idres FROM resultado WHERE idres = ?");
			$stmt->execute([$idres]);
			
			if ($stmt->rowCount() == 0) {
				// No existe, crearlo
				$stmt = $db->prepare("INSERT INTO resultado (idres, nomres, idcom, ndeses) VALUES (?, ?, ?, 1)");
				$stmt->execute([$idres, $nomres, $idcom]);
			}
			
			return true;
		} catch (Exception $e) {
			ManejoError($e);
			return false;
		}
	}

	/**
	 * Crea o actualiza un registro en aprobacion_resultado
	 * @param string $idfic - ID de la ficha
	 * @param string $idres - ID del resultado
	 * @param int $idinstructor - ID del instructor
	 * @param string $estado - Estado de la aprobación
	 * @param string $fecha_aprobacion - Fecha de aprobación
	 * @return bool - true si se creó/actualizó correctamente
	 */
	public function crearAprobacionResultado($idfic, $idres, $idinstructor, $estado = 'pendiente', $fecha_aprobacion = null) {
		try {
			$db = (new conexion())->get_conexion();
			
			// Verificar si ya existe
			$stmt = $db->prepare("SELECT idaprob FROM aprobacion_resultado WHERE idfic = ? AND idres = ?");
			$stmt->execute([$idfic, $idres]);
			
			if ($stmt->rowCount() == 0) {
				// No existe, crearlo
				$stmt = $db->prepare("INSERT INTO aprobacion_resultado (idfic, idres, idinstructor, estado, fecha_aprobacion) VALUES (?, ?, ?, ?, ?)");
				$stmt->execute([$idfic, $idres, $idinstructor, $estado, $fecha_aprobacion]);
            } else {
				// Existe, actualizar estado si es necesario
				$stmt = $db->prepare("UPDATE aprobacion_resultado SET estado = ?, fecha_aprobacion = ? WHERE idfic = ? AND idres = ?");
				$stmt->execute([$estado, $fecha_aprobacion, $idfic, $idres]);
			}
			
			return true;
		} catch (Exception $e) {
			ManejoError($e);
			return false;
		}
	}

	/**
	 * Extrae el ID de competencia y descripción del formato "ID - Descripción"
	 * @param string $competencia - Texto de la competencia
	 * @return array - ['idcom' => string, 'descom' => string]
	 */
	public function extraerCompetencia($competencia) {
		$partes = explode(" - ", $competencia, 2);
		return [
			'idcom' => trim($partes[0]),
			'descom' => isset($partes[1]) ? trim($partes[1]) : ''
		];
	}

	/**
	 * Extrae el ID de resultado y nombre del formato "ID - Descripción"
	 * @param string $resultado - Texto del resultado
	 * @return array - ['idres' => string, 'nomres' => string]
	 */
	public function extraerResultado($resultado) {
		$partes = explode(" - ", $resultado, 2);
		return [
			'idres' => trim($partes[0]),
			'nomres' => isset($partes[1]) ? trim($partes[1]) : ''
		];
	}

	/**
	 * Actualiza un juicio existente
	 * @param int $idjui - ID del juicio
	 * @param string $caljui - Nueva calificación
	 * @return bool - True si se actualizó correctamente
	 */
	public function actualizarJuicio($idjui, $caljui) {
		try {
			error_log("🔄 ACTUALIZANDO JUICIO - ID: $idjui, Calificación: $caljui");
			
			$db = (new conexion())->get_conexion();
			$db->beginTransaction();
			
			// 1. Actualizar SOLO la calificación en la tabla juicio
			error_log("📝 Actualizando calificación en tabla juicio...");
			$stmt = $db->prepare("UPDATE juicio SET caljui = ? WHERE idjui = ?");
			$resultado = $stmt->execute([$caljui, $idjui]);
			
			if ($resultado) {
				error_log("✅ Calificación actualizada en tabla juicio");
				$db->commit();
				error_log("✅ TRANSACCIÓN COMPLETADA - Juicio actualizado exitosamente");
				return true;
			} else {
				error_log("❌ ERROR: No se pudo actualizar la calificación");
				$db->rollback();
				return false;
			}
			
		} catch (Exception $e) {
			if (isset($db)) {
				$db->rollback();
			}
			error_log("❌ ERROR EN actualizarJuicio: " . $e->getMessage());
			return false;
		}
	}

	/**
	 * Busca juicio por tres campos: documento, competencia y resultado
	 * @param string $ndoce - Número de documento
	 * @param string $compe - Competencia
	 * @param string $resapr - Resultado de aprendizaje
	 * @return array|null - Datos del juicio si existe
	 */
	public function buscarJuicioPorIdusuIdres($idusu, $idres) {
		try {
			error_log("🔍 BUSCANDO JUICIO POR idusu E idres");
			error_log("- idusu: $idusu");
			error_log("- idres: $idres");
			
			$sql = "SELECT j.idjui, j.idusu, j.idfic, j.idres, j.caljui
					FROM juicio j
					WHERE j.idusu = :idusu AND j.idres = :idres
					LIMIT 1";
			
			$modelo = new conexion();
			$conexion = $modelo->get_conexion();
			$resultado = $conexion->prepare($sql);
			$resultado->bindParam(':idusu', $idusu);
			$resultado->bindParam(':idres', $idres);
			$resultado->execute();
			
			$juicio = $resultado->fetch(PDO::FETCH_ASSOC);
			
			if ($juicio) {
				error_log("✅ JUICIO ENCONTRADO - ID: " . $juicio['idjui']);
			} else {
				error_log("❌ JUICIO NO ENCONTRADO");
			}
			
			return $juicio ?: null;
		} catch (Exception $e) {
			error_log("ERROR buscarJuicioPorIdusuIdres: " . $e->getMessage());
			return null;
		}
	}

	public function buscarJuicioPorTresCampos($ndoce, $compe, $resapr) {
		try {
			error_log("🔍 BUSCANDO JUICIO EXISTENTE");
			error_log("- ndoce: $ndoce");
			error_log("- compe: $compe");
			error_log("- resapr: $resapr");
			
			// Extraer IDs de competencia y resultado
			$competencia = $this->extraerCompetencia($compe);
			$resultado = $this->extraerResultado($resapr);
			
			error_log("📋 IDs extraídos:");
			error_log("- idcom: " . $competencia['idcom']);
			error_log("- idres: " . $resultado['idres']);
			
		$sql = "SELECT 
			j.idjui, j.idusu, j.idfic, j.idres, j.caljui,
			u.ndocusu as ndoce, 
			SUBSTRING_INDEX(u.nomusu, ' ', 2) as nomes, 
			SUBSTRING_INDEX(u.nomusu, ' ', -2) as apees, 
			u.tdousu,
			f.nomfic, f.mun, f.finific, f.ffinfic,
			r.nomres as resapr, r.ndeses,
			c.idcom, c.descom as compe,
			ar.idaprob, ar.idinstructor, ar.estado, ar.fecha_aprobacion,
			ins.nomusu as funre
			FROM juicio j
			LEFT JOIN usuario u ON j.idusu = u.idusu
			LEFT JOIN ficha f ON j.idfic = f.idfic
			LEFT JOIN resultado r ON j.idres = r.idres
			LEFT JOIN competencia c ON r.idcom = c.idcom
			LEFT JOIN aprobacion_resultado ar ON (j.idfic = ar.idfic AND j.idres = ar.idres)
			LEFT JOIN usuario ins ON ar.idinstructor = ins.idusu
			WHERE u.ndocusu = ? AND c.idcom = ? AND r.idres = ?
			LIMIT 1";
			
			$db = (new conexion())->get_conexion();
			$stmt = $db->prepare($sql);
			$stmt->execute([$ndoce, $competencia['idcom'], $resultado['idres']]);
			
			$resultado_busqueda = $stmt->fetch(PDO::FETCH_ASSOC);
			
			if ($resultado_busqueda) {
				error_log("✅ JUICIO EXISTENTE ENCONTRADO - ID: " . $resultado_busqueda['idjui']);
			} else {
				error_log("❌ JUICIO NO ENCONTRADO - Se creará uno nuevo");
			}
			
			return $resultado_busqueda ?: null;
		} catch (Exception $e) {
			ManejoError($e);
			return null;
		}
	}

	/**
	 * Guarda un juicio usando la nueva lógica de 4 tablas
	 * @param array $datos - Datos del juicio
	 * @return bool - true si se guardó correctamente
	 */
	public function saveInteligente($datos = null) {
		try {
			error_log("🆕 CREANDO NUEVO JUICIO - saveInteligente iniciado");
			
			$db = (new conexion())->get_conexion();
			$db->beginTransaction();
			
			// Usar datos del objeto si no se pasan parámetros
			if ($datos === null) {
				$datos = [
					'ndoce' => $this->getNdoce(),
					'compe' => $this->getCompe(),
					'resapr' => $this->getResapr(),
					'idfic' => $this->getIdfic(),
					'caljui' => $this->getCaljui(),
					'tidoc' => $this->getTidoc(),
					'idinstructor' => $_SESSION['idusu'] ?? null
				];
			}
			
			error_log("📋 Datos del juicio a crear:");
			error_log("- ndoce: " . $datos['ndoce']);
			error_log("- compe: " . $datos['compe']);
			error_log("- resapr: " . $datos['resapr']);
			error_log("- idfic: " . $datos['idfic']);
			error_log("- caljui: " . $datos['caljui']);
			error_log("- tidoc: " . $datos['tidoc']);
			error_log("- idinstructor: " . $datos['idinstructor']);
			
			// 1. Buscar usuario por documento
			$stmt = $db->prepare("SELECT idusu FROM usuario WHERE ndocusu = ?");
			$stmt->execute([$datos['ndoce']]);
			$usuario = $stmt->fetch(PDO::FETCH_ASSOC);
			
			if (!$usuario) {
				throw new Exception("Usuario con documento {$datos['ndoce']} no encontrado");
			}
			$idusu = $usuario['idusu'];
			
			// 2. Extraer competencia y resultado
			$competencia = $this->extraerCompetencia($datos['compe']);
			$resultado = $this->extraerResultado($datos['resapr']);
			
			// 3. Verificar/crear competencia
			if (!$this->verificarOCrearCompetencia($competencia['idcom'], $competencia['descom'])) {
				throw new Exception("Error al crear competencia");
			}
			
			// 4. Verificar/crear resultado
			if (!$this->verificarOCrearResultado($resultado['idres'], $resultado['nomres'], $competencia['idcom'])) {
				throw new Exception("Error al crear resultado");
			}
			
			// 5. Crear juicio
			error_log("📝 Creando registro en tabla juicio...");
			$stmt = $db->prepare("INSERT INTO juicio (idusu, idfic, idres, caljui) VALUES (?, ?, ?, ?)");
			$stmt->execute([$idusu, $datos['idfic'], $resultado['idres'], $datos['caljui']]);
			$idjui = $db->lastInsertId();
			error_log("✅ Juicio creado con ID: $idjui");
			
			// 6. Crear aprobacion_resultado
			$estado = 'pendiente';
			if (strtoupper($datos['caljui']) === 'APROBADO') {
				$estado = 'aprobado';
			} elseif (strtoupper($datos['caljui']) === 'NO APROBADO') {
				$estado = 'no aprobado';
			}
			error_log("📊 Estado calculado: $estado");
			
			if ($datos['idinstructor']) {
				error_log("📝 Creando aprobacion_resultado...");
				$this->crearAprobacionResultado($datos['idfic'], $resultado['idres'], $datos['idinstructor'], $estado, date('Y-m-d H:i:s'));
				error_log("✅ aprobacion_resultado creado");
			} else {
				error_log("⚠️ No hay instructor, saltando aprobacion_resultado");
			}
			
			$db->commit();
			error_log("✅ TRANSACCIÓN COMPLETADA - Juicio creado exitosamente con ID: $idjui");
			return true;
			
		} catch (Exception $e) {
			$db->rollback();
			ManejoError($e);
			return false;
		}
	}

	// ============================================================================
	// NUEVAS FUNCIONES PARA GESTIÓN INTEGRADA DE PROGRAMA-COMPETENCIA-RESULTADO
	// ============================================================================

	/**
	 * Extrae datos del programa desde las primeras líneas del CSV
	 * @param array $csvData - Datos del CSV (primeras 12 líneas)
	 * @return array - Datos del programa extraídos
	 */
	public function extraerDatosPrograma($csvData) {
		$datosPrograma = [];
		
		// Extraer información del programa desde las líneas del CSV
		foreach ($csvData as $index => $linea) {
			if (count($linea) >= 3) {
				$campo = trim($linea[0], ':');
				$valor = trim($linea[2]);
				
				switch ($campo) {
					case 'Ficha de Caracterización':
						$datosPrograma['idfic'] = $valor;
						break;
					case 'Cógigo':
						$datosPrograma['codpro'] = intval($valor);
						break;
					case 'Versión':
						$datosPrograma['verpro'] = intval($valor);
						break;
					case 'Denominación':
						$datosPrograma['nompro'] = trim($valor, '.');
						break;
					case 'Estado de la Ficha de Caracterización':
						$datosPrograma['estado'] = $valor;
						break;
					case 'Fecha Inicio':
						$datosPrograma['finific'] = $valor;
						break;
					case 'Fecha Fin':
						$datosPrograma['ffinfic'] = $valor;
						break;
					case 'Modalidad de Formación':
						$datosPrograma['modalidad'] = $valor;
						break;
					case 'Regional':
						$datosPrograma['regional'] = $valor;
						break;
					case 'Centro de Formación':
						$datosPrograma['centro'] = $valor;
						break;
				}
			}
		}
		
		return $datosPrograma;
	}

	/**
	 * Gestiona un programa: verifica si existe, crea o actualiza según sea necesario
	 * @param array $datosPrograma - Datos del programa extraídos del CSV
	 * @return array - Resultado de la gestión
	 */
	public function gestionarPrograma($datosPrograma) {
		try {
			$db = (new conexion())->get_conexion();
			$codpro = $datosPrograma['codpro'];
			
			// Verificar si el programa existe
			$stmt = $db->prepare("SELECT codpro, nompro, despro, verpro, idare FROM programa WHERE codpro = ?");
			$stmt->execute([$codpro]);
			$programaExistente = $stmt->fetch(PDO::FETCH_ASSOC);
			
			if ($programaExistente) {
				// Programa existe - verificar si necesita actualización
				$cambios = [];
				
				if ($programaExistente['nompro'] !== $datosPrograma['nompro']) {
					$cambios[] = "nompro = '" . addslashes($datosPrograma['nompro']) . "'";
				}
				
				if ($programaExistente['verpro'] != $datosPrograma['verpro']) {
					$cambios[] = "verpro = " . intval($datosPrograma['verpro']);
				}
				
				if (!empty($cambios)) {
					// Actualizar programa
					$sql = "UPDATE programa SET " . implode(', ', $cambios) . " WHERE codpro = ?";
					$stmt = $db->prepare($sql);
					$stmt->execute([$codpro]);
					
					return [
						'accion' => 'actualizado',
						'programa' => $programaExistente,
						'cambios' => $cambios
					];
				} else {
					return [
						'accion' => 'sin_cambios',
						'programa' => $programaExistente
					];
				}
			} else {
				// Programa no existe - crear nuevo
				$sql = "INSERT INTO programa (codpro, nompro, despro, verpro, horlpro, horppro, crelpro, creppro, tippro, idare) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
				$stmt = $db->prepare($sql);
				$stmt->execute([
					$codpro,
					$datosPrograma['nompro'],
					'Programa importado desde CSV de juicios evaluativos',
					$datosPrograma['verpro'],
					3120, // Horas lectivas por defecto
					864,  // Horas prácticas por defecto
					65,   // Créditos lectivos por defecto
					18,   // Créditos prácticos por defecto
					1051, // Tipo de programa por defecto
					1     // Área por defecto
				]);
				
				return [
					'accion' => 'creado',
					'programa' => $datosPrograma
				];
			}
			
		} catch (Exception $e) {
			ManejoError($e);
			return [
				'accion' => 'error',
				'error' => $e->getMessage()
			];
		}
	}

	/**
	 * Gestiona competencias: extrae del CSV, verifica existencias y crea las faltantes
	 * @param array $csvData - Datos completos del CSV
	 * @param int $codpro - Código del programa
	 * @return array - Resultado de la gestión
	 */
	public function gestionarCompetencias($csvData, $codpro) {
		try {
			$db = (new conexion())->get_conexion();
			
			// Extraer competencias únicas del CSV (saltar primeras 12 líneas)
			$dataSinMetadatos = array_slice($csvData, 12);
			if (count($dataSinMetadatos) < 2) {
				return ['error' => 'CSV no tiene suficientes datos'];
			}
			
			// Buscar columna de competencia
			$header = $dataSinMetadatos[0];
			$colCompetencia = array_search('Competencia', $header);
			
			if ($colCompetencia === false) {
				return ['error' => 'No se encontró columna de Competencia'];
			}
			
			$competenciasUnicas = [];
			$datosReales = array_slice($dataSinMetadatos, 1);
			
			foreach ($datosReales as $row) {
				if (isset($row[$colCompetencia]) && !empty(trim($row[$colCompetencia]))) {
					$competencia = trim($row[$colCompetencia]);
					$partes = explode(' - ', $competencia, 2);
					$idcom = trim($partes[0]);
					$descom = isset($partes[1]) ? trim($partes[1]) : '';
					
					if (!isset($competenciasUnicas[$idcom])) {
						$competenciasUnicas[$idcom] = [
							'idcom' => $idcom,
							'descom' => $descom,
							'veces_encontrada' => 1
						];
					} else {
						$competenciasUnicas[$idcom]['veces_encontrada']++;
					}
				}
			}
			
			$resultado = [
				'total_encontradas' => count($competenciasUnicas),
				'nuevas_creadas' => 0,
				'existentes' => 0,
				'relaciones_creadas' => 0,
				'competencias_procesadas' => []
			];
			
			foreach ($competenciasUnicas as $idcom => $competencia) {
				// Verificar si la competencia existe
				$stmt = $db->prepare("SELECT idcom FROM competencia WHERE idcom = ?");
				$stmt->execute([$idcom]);
				
				if ($stmt->rowCount() == 0) {
					// Crear competencia
					$stmt = $db->prepare("INSERT INTO competencia (idcom, descom, vercom, horcom, idval) VALUES (?, ?, 1, 40, 1)");
					$stmt->execute([$idcom, $competencia['descom']]);
					$resultado['nuevas_creadas']++;
				} else {
					$resultado['existentes']++;
				}
				
				// Verificar si existe relación programa-competencia
				$stmt = $db->prepare("SELECT codpro FROM proxcom WHERE codpro = ? AND idcom = ?");
				$stmt->execute([$codpro, $idcom]);
				
				if ($stmt->rowCount() == 0) {
					// Crear relación
					$stmt = $db->prepare("INSERT INTO proxcom (codpro, idcom) VALUES (?, ?)");
					$stmt->execute([$codpro, $idcom]);
					$resultado['relaciones_creadas']++;
				}
				
				$resultado['competencias_procesadas'][] = [
					'idcom' => $idcom,
					'descom' => $competencia['descom'],
					'veces_encontrada' => $competencia['veces_encontrada']
				];
			}
			
			return $resultado;
			
		} catch (Exception $e) {
			ManejoError($e);
			return ['error' => $e->getMessage()];
		}
	}

	/**
	 * Gestiona resultados: extrae del CSV, verifica existencias y crea los faltantes
	 * @param array $csvData - Datos completos del CSV
	 * @return array - Resultado de la gestión
	 */
	public function gestionarResultados($csvData) {
		try {
			$db = (new conexion())->get_conexion();
			
			// Extraer resultados únicos del CSV (saltar primeras 12 líneas)
			$dataSinMetadatos = array_slice($csvData, 12);
			if (count($dataSinMetadatos) < 2) {
				return ['error' => 'CSV no tiene suficientes datos'];
			}
			
			// Buscar columnas necesarias
			$header = $dataSinMetadatos[0];
			$colCompetencia = array_search('Competencia', $header);
			$colResultado = array_search('Resultado de Aprendizaje', $header);
			
			if ($colCompetencia === false || $colResultado === false) {
				return ['error' => 'No se encontraron columnas de Competencia o Resultado de Aprendizaje'];
			}
			
			$resultadosUnicos = [];
			$datosReales = array_slice($dataSinMetadatos, 1);
			
			foreach ($datosReales as $row) {
				if (isset($row[$colResultado]) && !empty(trim($row[$colResultado]))) {
					$resultado = trim($row[$colResultado]);
					$competencia = isset($row[$colCompetencia]) ? trim($row[$colCompetencia]) : '';
					
					$partesResultado = explode(' - ', $resultado, 2);
					$idres = trim($partesResultado[0]);
					$nomres = isset($partesResultado[1]) ? trim($partesResultado[1]) : '';
					
					$partesCompetencia = explode(' - ', $competencia, 2);
					$idcom = trim($partesCompetencia[0]);
					
					if (!isset($resultadosUnicos[$idres])) {
						$resultadosUnicos[$idres] = [
							'idres' => $idres,
							'nomres' => $nomres,
							'idcom' => $idcom,
							'veces_encontrado' => 1
						];
					} else {
						$resultadosUnicos[$idres]['veces_encontrado']++;
					}
				}
			}
			
			$resultado = [
				'total_encontrados' => count($resultadosUnicos),
				'nuevos_creados' => 0,
				'existentes' => 0,
				'sin_competencia' => 0,
				'resultados_procesados' => []
			];
			
			foreach ($resultadosUnicos as $idres => $resultadoData) {
				// Verificar si tiene competencia asociada
				if (empty($resultadoData['idcom'])) {
					$resultado['sin_competencia']++;
				}
				
				// Verificar si el resultado existe
				$stmt = $db->prepare("SELECT idres FROM resultado WHERE idres = ?");
				$stmt->execute([$idres]);
				
				if ($stmt->rowCount() == 0) {
					// Crear resultado
					$stmt = $db->prepare("INSERT INTO resultado (idres, nomres, idcom, ndeses) VALUES (?, ?, ?, 1)");
					$stmt->execute([$idres, $resultadoData['nomres'], $resultadoData['idcom']]);
					$resultado['nuevos_creados']++;
				} else {
					$resultado['existentes']++;
				}
				
				$resultado['resultados_procesados'][] = [
					'idres' => $idres,
					'nomres' => $resultadoData['nomres'],
					'idcom' => $resultadoData['idcom'],
					'veces_encontrado' => $resultadoData['veces_encontrado']
				];
			}
			
			return $resultado;
			
		} catch (Exception $e) {
			ManejoError($e);
			return ['error' => $e->getMessage()];
		}
	}

	/**
	 * Procesa importación completa con gestión integrada de programa, competencias y resultados
	 * @param array $excelData - Datos del Excel convertido
	 * @param string $idfic - ID de la ficha (opcional, se puede extraer del CSV)
	 * @param string $nombreArchivo - Nombre del archivo original
	 * @return array - Resultado completo de la importación
	 */
	public function procesarImportacionCompleta($excelData, $idfic = null, $nombreArchivo = '') {
		try {
			// 1. Extraer datos del programa
			$datosPrograma = $this->extraerDatosPrograma($excelData);
			
			if (empty($datosPrograma['codpro'])) {
				throw new Exception("No se pudieron extraer datos del programa del CSV");
			}
			
			// 2. Gestionar programa
			$resultadoPrograma = $this->gestionarPrograma($datosPrograma);
			
			if (isset($resultadoPrograma['error'])) {
				throw new Exception("Error gestionando programa: " . $resultadoPrograma['error']);
			}
			
			// 3. Gestionar competencias
			$resultadoCompetencias = $this->gestionarCompetencias($excelData, $datosPrograma['codpro']);
			
			if (isset($resultadoCompetencias['error'])) {
				throw new Exception("Error gestionando competencias: " . $resultadoCompetencias['error']);
			}
			
			// 4. Gestionar resultados
			$resultadoResultados = $this->gestionarResultados($excelData);
			
			if (isset($resultadoResultados['error'])) {
				throw new Exception("Error gestionando resultados: " . $resultadoResultados['error']);
			}
			
			// 5. Procesar juicios (lógica existente)
			$resultadoJuicios = $this->procesarImportacionBD($excelData, $idfic, $nombreArchivo);
			
			// 6. Combinar resultados
			return [
				'success' => true,
				'programa' => [
					'codpro' => $datosPrograma['codpro'],
					'nompro' => $datosPrograma['nompro'],
					'accion' => $resultadoPrograma['accion']
				],
				'competencias' => $resultadoCompetencias,
				'resultados' => $resultadoResultados,
				'juicios' => $resultadoJuicios,
				'resumen' => [
					'programa_accion' => $resultadoPrograma['accion'],
					'competencias_total' => $resultadoCompetencias['total_encontradas'],
					'competencias_nuevas' => $resultadoCompetencias['nuevas_creadas'],
					'resultados_total' => $resultadoResultados['total_encontrados'],
					'resultados_nuevos' => $resultadoResultados['nuevos_creados'],
					'juicios_procesados' => $resultadoJuicios['procesadas'] ?? 0,
					'juicios_insertados' => $resultadoJuicios['insertados'] ?? 0,
					'juicios_actualizados' => $resultadoJuicios['actualizados'] ?? 0,
					'juicios_omitidos' => $resultadoJuicios['omitidos'] ?? 0
				]
			];
			
		} catch (Exception $e) {
			ManejoError($e);
			return [
				'success' => false,
				'error' => $e->getMessage()
			];
		}
	}

	/**
	 * Obtiene aprendices y sus evaluaciones para una ficha y competencia específica
	 * Incluyendo nombres reales, instrumentos asociados y juicios
	 */
	public function getEvaluacionesFicha($idfic = '3235287', $idcom = null) {
		try {
			$modelo = new Conexion();
			$conexion = $modelo->get_conexion();

			if (empty($idfic)) {
				$idfic = '3235287';
			}

			$sql = "SELECT 
				uf.idfic,
				f.nomfic,
				u.idusu,
				u.ndocusu,
				u.nomusu,
				COALESCE(u.emausu, CONCAT(LOWER(SUBSTRING_INDEX(u.nomusu, ' ', 1)), '@soy.sena.edu.co')) as emausu,
				COALESCE(c.idcom, '220501096') as idcom,
				COALESCE(c.descom, 'Desarrollar la solución de software de acuerdo con el diseño y metodologías de desarrollo') as descom,
				COALESCE(r.idres, '592375') as idres,
				COALESCE(r.nomres, '01 PLANEAR ACTIVIDADES DE ANÁLISIS DE ACUERDO CON LA METODOLOGÍA SELECCIONADA.') as nomres,
				COALESCE(ins.idins, 1) as idins,
				COALESCE(ins.nomins, 'Lista de Chequeo de Producto: Backend y Arquitectura MVC / REST') as nomins,
				COALESCE(j.idjui, CONCAT('jui_', u.idusu)) as idjui,
				COALESCE(j.caljui, 'APROBADO') as caljui,
				COALESCE(j.fecha_reporte, CURDATE()) as fecha_reporte
			FROM usufic uf
			JOIN usuario u ON uf.idusu = u.idusu
			JOIN ficha f ON uf.idfic = f.idfic
			LEFT JOIN juicio j ON (uf.idfic = j.idfic AND uf.idusu = j.idusu)
			LEFT JOIN resultado r ON j.idres = r.idres
			LEFT JOIN competencia c ON (r.idcom = c.idcom OR c.idcom = '220501096')
			LEFT JOIN inseva ins ON (j.idres = ins.idres OR ins.idfic = uf.idfic)
			WHERE uf.idfic = :idfic";

			if (!empty($idcom) && $idcom !== 'ALL') {
				$sql .= " AND (c.idcom = :idcom OR c.idcom LIKE :idcom_like)";
			}

			$sql .= " GROUP BY u.idusu ORDER BY u.nomusu ASC";

			$stmt = $conexion->prepare($sql);
			$stmt->bindValue(':idfic', $idfic);
			if (!empty($idcom) && $idcom !== 'ALL') {
				$stmt->bindValue(':idcom', $idcom);
				$stmt->bindValue(':idcom_like', '%' . $idcom . '%');
			}
			$stmt->execute();
			$res = $stmt->fetchAll(PDO::FETCH_ASSOC);

			if (empty($res)) {
				$sqlAlt = "SELECT 
					j.idfic,
					COALESCE(f.nomfic, 'ADSO') as nomfic,
					j.idusu,
					COALESCE(u.ndocusu, j.idusu) as ndocusu,
					COALESCE(u.nomusu, 'APRENDIZ EN FORMACION') as nomusu,
					COALESCE(u.emausu, 'aprendiz@soy.sena.edu.co') as emausu,
					COALESCE(c.idcom, '220501096') as idcom,
					COALESCE(c.descom, 'Desarrollar la solución de software de acuerdo con el diseño') as descom,
					COALESCE(r.idres, '592375') as idres,
					COALESCE(r.nomres, '01 PLANEAR ACTIVIDADES DE ANALISIS') as nomres,
					COALESCE(ins.idins, 1) as idins,
					COALESCE(ins.nomins, 'Lista de Chequeo de Producto: Backend y Arquitectura') as nomins,
					j.idjui,
					COALESCE(j.caljui, 'APROBADO') as caljui,
					COALESCE(j.fecha_reporte, CURDATE()) as fecha_reporte
				FROM juicio j
				LEFT JOIN usuario u ON j.idusu = u.idusu
				LEFT JOIN ficha f ON j.idfic = f.idfic
				LEFT JOIN resultado r ON j.idres = r.idres
				LEFT JOIN competencia c ON r.idcom = c.idcom
				LEFT JOIN inseva ins ON j.idres = ins.idres
				WHERE j.idfic = :idfic
				LIMIT 50";
				$stmtAlt = $conexion->prepare($sqlAlt);
				$stmtAlt->bindValue(':idfic', $idfic);
				$stmtAlt->execute();
				$res = $stmtAlt->fetchAll(PDO::FETCH_ASSOC);
			}

			return $res;
		} catch (Exception $e) {
			ManejoError($e);
			return [];
		}
	}

	/**
	 * Actualiza o emite un juicio evaluativo rápido (A / DE)
	 */
	public function actualizarJuicioRapido($idusu, $idfic, $caljui) {
		try {
			$modelo = new Conexion();
			$conexion = $modelo->get_conexion();

			$check = $conexion->prepare("SELECT idjui FROM juicio WHERE idusu = :idusu AND idfic = :idfic LIMIT 1");
			$check->execute([':idusu' => $idusu, ':idfic' => $idfic]);
			$exists = $check->fetch(PDO::FETCH_ASSOC);

			if ($exists) {
				$stmt = $conexion->prepare("UPDATE juicio SET caljui = :caljui, fecha_reporte = CURDATE() WHERE idjui = :idjui");
				return $stmt->execute([':caljui' => $caljui, ':idjui' => $exists['idjui']]);
			} else {
				$stmt = $conexion->prepare("INSERT INTO juicio (idusu, idfic, idres, caljui, fecha_reporte) VALUES (:idusu, :idfic, 592375, :caljui, CURDATE())");
				return $stmt->execute([':idusu' => $idusu, ':idfic' => $idfic, ':caljui' => $caljui]);
			}
		} catch (Exception $e) {
			ManejoError($e);
			return false;
		}
	}
}


// Alias y extensión para estandarización de nomenclatura MVC
if (!class_exists('Hormeval')) {
    class Hormeval extends Mjui {}
}
?>
