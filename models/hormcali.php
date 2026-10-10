<?php
require_once(__DIR__ . "/conexion.php");
require_once(__DIR__ . "/hormacceso.php");

/**
 * Modelo Hormcali - Módulo Calificar Actividades por Criterios (SAGISENA)
 * Gestiona la evaluación de aprendices por actividad y criterios de desempeño
 */
class Hormcali {

    private $acceso;
    private $contexto;

    public function __construct($contexto = null) {
        $this->acceso = new HormAcceso();
        $this->contexto = $contexto ?: $this->acceso->getContexto();
    }

    public function getContexto()
    {
        if ($this->contexto === null) {
            $this->contexto = $this->acceso->getContexto();
        }
        return $this->contexto;
    }

    public function setContexto($contexto)
    {
        $this->contexto = $contexto;
        return $this;
    }

    public function getAcceso()
    {
        return $this->acceso;
    }

    public function getFichasPermitidas($idfic = null)
    {
        return $this->acceso->getFichasPermitidas($this->getContexto(), $idfic);
    }

    public function puedeVerFicha($idfic)
    {
        return $this->acceso->puedeVerFicha($idfic, $this->getContexto());
    }

    public function getAprendicesPermitidos($idfic)
    {
        return $this->acceso->getAprendicesPermitidos($idfic, $this->getContexto());
    }

    public function getActividadesPermitidas($idfic = null, $idact = null)
    {
        return $this->acceso->getActividadesPermitidas($this->getContexto(), $idfic, $idact);
    }

    public function puedeVerActividad($idfic, $idact)
    {
        return $this->acceso->puedeVerActividad($idfic, $idact, $this->getContexto());
    }

    public function puedeCalificar($idfic)
    {
        return $this->acceso->puedeCalificar($idfic, $this->getContexto());
    }

    /**
     * Valida si el usuario tiene privilegios de Instructor, Coordinador o Administrador
     */
    public function esDocenteOAdmin($idusu = null, $idper = null) {
        $contexto = $this->getContexto();
        return !empty($contexto['can_write_grades']);
    }

    /**
     * Obtiene las fichas asociadas específicamente a un aprendiz
     */
    public function getFichasAprendiz($idusu = null) {
        $contexto = $this->getContexto();
        if (!$contexto['is_learner'] || !$contexto['authenticated']) {
            return [];
        }
        return $this->getFichasPermitidas();
    }

    /**
     * Obtiene el listado de fichas de formación disponibles en el sistema
     */
    public function getFichas($idusu = null, $idper = null) {
        return $this->getFichasPermitidas();
    }

    /**
     * Obtiene las actividades disponibles vinculadas con una ficha y sus competencias
     */
    public function getActividadesByFicha($idfic = null) {
        if ($idfic !== null && $idfic !== '' && !$this->puedeVerFicha($idfic)) {
            return [];
        }
        $actividades = $this->getAcceso()->getActividadesPermitidas($this->getContexto(), $idfic);
        $resultado = [];
        foreach ($actividades as $actividad) {
            if ($idfic !== null && $idfic !== '') {
                $actividad['idfic'] = (string)$idfic;
            }
            $actividad['idcom'] = $actividad['idcom'] ?? null;
            $actividad['nomcom'] = $actividad['nomcom'] ?? 'Competencia de Aprendizaje';
            $actividad['nompro'] = $actividad['nompro'] ?? 'Programa de Formación';
            $resultado[$actividad['idact']] = $actividad;
        }
        return array_values($resultado);
    }

    /**
     * Obtiene los datos detallados de una actividad específica para la cabecera del Instrumento de Evaluación
     */
    public function getActividadInfo($idact, $idfic = null) {
        if ($idfic === null || $idfic === '' || !$this->puedeVerActividad($idfic, $idact)) {
            return null;
        }
        try {
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();

            $sql = "SELECT 
                        a.idact, 
                        a.nomact, 
                        a.desact, 
                        COALESCE(a.duract, 2) as duract,
                        COALESCE(ag.fas, 'Planeación') as fase,
                        COALESCE(c.idcom, 493621) as idcom,
                        COALESCE(c.descom, '2. APLICAR LAS FUNCIONES DE UNA HOJA DE CÁLCULO SEGÚN LAS CARACTERÍSTICAS DE LAS OPERACIONES A DESARROLLAR.') as nomcom,
                        COALESCE(r.idres, '21010105102') as idres,
                        COALESCE(r.nomres, a.desact, 'Aplicar e identificar las funciones propuestas en clase para casos de la vida real de acuerdo a la lista de chequeo.') as nomres,
                        COALESCE(p.nompro, f.nomfic, 'TECNOLOGÍAS DE LA INFORMACIÓN Y LA COMUNICACIÓN') as nompro,
                        COALESCE(f.idfic, :idfic) as idfic,
                        COALESCE(f.nomfic, 'Ficha de Formación') as nomfic,
                        COALESCE(u.nomusu, 'Instructor Asignado') as nominstructor
                    FROM actividad a
                    LEFT JOIN sesion s ON a.idses = s.idses
                    LEFT JOIN agenda ag ON s.idage = ag.idage
                    LEFT JOIN ficha f ON f.idfic = COALESCE(NULLIF(:idfic, ''), ag.idfic)
                    LEFT JOIN programa p ON f.codpro = p.codpro
                    LEFT JOIN proxcom px ON px.codpro = f.codpro
                    LEFT JOIN competencia c ON (px.idcom = c.idcom OR ag.idcom = c.idcom OR c.idcom = 1)
                    LEFT JOIN resultado r ON (ag.idres = r.idres OR r.idcom = c.idcom)
                    LEFT JOIN usuario u ON (ag.idins = u.idusu OR ag.idusu = u.idusu OR f.idusu = u.idusu)
                    WHERE a.idact = :idact
                    LIMIT 1";

            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(':idact', $idact);
            $stmt->bindParam(':idfic', $idfic);
            $stmt->execute();
            $act = $stmt->fetch(PDO::FETCH_ASSOC);

            return $act;
        } catch (Exception $e) {
            error_log("Error en Hormcali::getActividadInfo: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtiene o inicializa los criterios / indicadores de logro para una actividad (Letras A, B, C, D...)
     */
    public function getCriteriosByActividad($idact, $idfic = null) {
        if ($idfic === null || $idfic === '' || !$this->puedeVerActividad($idfic, $idact)) {
            return [];
        }
        try {
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();

            $sql = "SELECT idcri, idact, idfic, nomcri, orden
                    FROM criterio_actividad
                    WHERE idact = :idact
                      AND (:idfic = '' OR idfic = :idfic OR idfic IS NULL)
                    ORDER BY orden ASC, idcri ASC";

            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(':idact', $idact);
            $stmt->bindParam(':idfic', $idfic);
            $stmt->execute();
            $criterios = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // No se crean criterios ni datos de configuración desde la vista.
            if (empty($criterios)) {
                return [];
            }

            // Asignar letra a cada criterio (A, B, C, D, E, F...)
            $letras = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M'];
            foreach ($criterios as $idx => &$cri) {
                $cri['letra'] = $letras[$idx] ?? ('C' . ($idx + 1));
            }

            return $criterios;
        } catch (Exception $e) {
            error_log("Error en Hormcali::getCriteriosByActividad: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Agrega un nuevo criterio a una actividad
     */
    public function agregarCriterio($idact, $nomcri, $idfic = null) {
        if (!$this->puedeCalificar($idfic) || !$this->puedeVerActividad($idfic, $idact)) {
            return false;
        }
        try {
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();

            $sqlMax = "SELECT COALESCE(MAX(orden), 0) + 1 as sig_orden FROM criterio_actividad WHERE idact = :idact";
            $stmtMax = $conexion->prepare($sqlMax);
            $stmtMax->bindParam(':idact', $idact);
            $stmtMax->execute();
            $sigOrden = $stmtMax->fetchColumn() ?: 1;

            $sql = "INSERT INTO criterio_actividad (idact, idfic, nomcri, orden) VALUES (:idact, :idfic, :nomcri, :orden)";
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(':idact', $idact);
            $stmt->bindParam(':idfic', $idfic);
            $stmt->bindParam(':nomcri', $nomcri);
            $stmt->bindParam(':orden', $sigOrden);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error en Hormcali::agregarCriterio: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Elimina un criterio de una actividad
     */
    public function eliminarCriterio($idcri) {
        try {
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $sql = "SELECT idact, idfic FROM criterio_actividad WHERE idcri = :idcri LIMIT 1";
            $stmt = $conexion->prepare($sql);
            $stmt->execute([':idcri' => (int)$idcri]);
            $criterio = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$criterio || !$this->puedeCalificar($criterio['idfic']) || !$this->puedeVerActividad($criterio['idfic'], $criterio['idact'])) {
                return false;
            }

            $sql1 = "DELETE FROM evaluacion_actividad WHERE idcri = :idcri";
            $stmt1 = $conexion->prepare($sql1);
            $stmt1->execute([':idcri' => (int)$idcri]);

            $sql2 = "DELETE FROM criterio_actividad WHERE idcri = :idcri";
            $stmt2 = $conexion->prepare($sql2);
            $stmt2->execute([':idcri' => (int)$idcri]);
            return true;
        } catch (Exception $e) {
            error_log("Error en Hormcali::eliminarCriterio: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene los aprendices de una ficha específica (Solo para uso de instructores)
     */
    public function getAprendicesByFicha($idfic) {
        return $this->getAprendicesPermitidos($idfic);
    }

    /**
     * Obtiene las evaluaciones registradas de los aprendices para una actividad y ficha
     */
    public function getEvaluacionesActividad($idact, $idfic) {
        if (!$this->puedeVerActividad($idfic, $idact)) {
            return [];
        }
        try {
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();

            $sql = "SELECT idusu, idcri, valor, estado_criterio, observacion 
                    FROM evaluacion_actividad 
                    WHERE idact = :idact AND idfic = :idfic";

            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(':idact', $idact);
            $stmt->bindParam(':idfic', $idfic);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $evaluaciones = [];
            foreach ($rows as $r) {
                $evaluaciones[$r['idusu']][$r['idcri']] = [
                    'valor' => (int)$r['valor'],
                    'estado' => $r['estado_criterio'],
                    'observacion' => $r['observacion'] ?? ''
                ];
            }

            return $evaluaciones;
        } catch (Exception $e) {
            error_log("Error en Hormcali::getEvaluacionesActividad: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene las notas finales guardadas para una actividad y ficha
     */
    public function getNotasActividad($idact, $idfic) {
        if (!$this->puedeVerActividad($idfic, $idact)) {
            return [];
        }
        try {
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();

            $sql = "SELECT idusu, nota, estado, observacion, fecha_evaluacion 
                    FROM nota_actividad 
                    WHERE idact = :idact AND idfic = :idfic";

            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(':idact', $idact);
            $stmt->bindParam(':idfic', $idfic);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $notas = [];
            foreach ($rows as $r) {
                $notas[$r['idusu']] = [
                    'nota' => (int)$r['nota'],
                    'estado' => $r['estado'],
                    'observacion' => $r['observacion'] ?? '',
                    'fecha_evaluacion' => $r['fecha_evaluacion']
                ];
            }

            return $notas;
        } catch (Exception $e) {
            error_log("Error en Hormcali::getNotasActividad: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Guarda la evaluación completa de los aprendices para una actividad y ficha
     */
    public function guardarEvaluacionCompleta($idact, $idfic, $evaluacionesPorAprendiz, $idinstructor = null) {
        $contexto = $this->getContexto();
        if (!$this->puedeCalificar($idfic) || !$this->puedeVerActividad($idfic, $idact)) {
            return [
                'status' => 'error',
                'message' => 'No tiene permisos para calificar esta actividad.'
            ];
        }

        $aprendicesPermitidos = [];
        foreach ($this->getAprendicesPermitidos($idfic) as $aprendiz) {
            $aprendicesPermitidos[(int)$aprendiz['idusu']] = true;
        }
        if (empty($aprendicesPermitidos)) {
            return [
                'status' => 'error',
                'message' => 'La ficha no tiene aprendices autorizados.'
            ];
        }

        $criteriosPermitidos = [];
        foreach ($this->getCriteriosByActividad($idact, $idfic) as $criterio) {
            $criteriosPermitidos[(int)$criterio['idcri']] = true;
        }
        if (empty($criteriosPermitidos)) {
            return [
                'status' => 'error',
                'message' => 'La actividad no tiene criterios configurados.'
            ];
        }

        $conexion = null;
        $transaccion = false;
        try {
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $conexion->beginTransaction();
            $transaccion = true;
            $fechaAhora = date('Y-m-d H:i:s');
            $idinstructorSesion = (int)$contexto['idusu'];

            $sqlEva = "INSERT INTO evaluacion_actividad (idact, idfic, idusu, idcri, valor, estado_criterio, observacion, fecha_registro)
                       VALUES (:idact, :idfic, :idusu, :idcri, :valor, :estado_criterio, :observacion, :fecha_registro)
                       ON DUPLICATE KEY UPDATE
                           valor = VALUES(valor),
                           estado_criterio = VALUES(estado_criterio),
                           observacion = VALUES(observacion),
                           fecha_registro = VALUES(fecha_registro)";
            $stmtEva = $conexion->prepare($sqlEva);

            $sqlNota = "INSERT INTO nota_actividad (idact, idfic, idusu, nota, estado, observacion, idinstructor, fecha_evaluacion)
                        VALUES (:idact, :idfic, :idusu, :nota, :estado, :observacion, :idinstructor, :fecha_evaluacion)
                        ON DUPLICATE KEY UPDATE
                            nota = VALUES(nota),
                            estado = VALUES(estado),
                            observacion = VALUES(observacion),
                            idinstructor = VALUES(idinstructor),
                            fecha_evaluacion = VALUES(fecha_evaluacion)";
            $stmtNota = $conexion->prepare($sqlNota);

            $guardados = 0;
            foreach ($evaluacionesPorAprendiz as $idusu => $data) {
                $idusu = (int)$idusu;
                if (!isset($aprendicesPermitidos[$idusu])) {
                    continue;
                }
                $criterios = array_intersect_key((array)($data['criterios'] ?? []), $criteriosPermitidos);
                if (empty($criterios)) {
                    continue;
                }

                $sumaPuntos = 0;
                $totalCriterios = 0;
                foreach ($criterios as $idcri => $valor) {
                    $valorInt = max(0, min(100, (int)$valor));
                    $sumaPuntos += $valorInt;
                    $totalCriterios++;
                    $estadoCri = $valorInt >= 80 ? 'Cumple' : ($valorInt >= 70 ? 'Cumple parcialmente' : 'No cumple');

                    $stmtEva->execute([
                        ':idact' => (int)$idact,
                        ':idfic' => (string)$idfic,
                        ':idusu' => $idusu,
                        ':idcri' => (int)$idcri,
                        ':valor' => $valorInt,
                        ':estado_criterio' => $estadoCri,
                        ':observacion' => null,
                        ':fecha_registro' => $fechaAhora,
                    ]);
                }

                $notaCalculada = isset($data['nota']) ? max(0, min(100, (int)$data['nota'])) : (int)round($sumaPuntos / $totalCriterios);
                $estadoFinal = $notaCalculada >= 80 ? 'Aprobado' : ($notaCalculada >= 70 ? 'Aprobado Parcial' : 'No Aprobado');
                $stmtNota->execute([
                    ':idact' => (int)$idact,
                    ':idfic' => (string)$idfic,
                    ':idusu' => $idusu,
                    ':nota' => $notaCalculada,
                    ':estado' => $estadoFinal,
                    ':observacion' => trim((string)($data['observacion'] ?? '')),
                    ':idinstructor' => $idinstructorSesion,
                    ':fecha_evaluacion' => $fechaAhora,
                ]);
                $guardados++;
            }

            if ($guardados === 0) {
                $conexion->rollBack();
                return [
                    'status' => 'error',
                    'message' => 'No se encontraron aprendices ni criterios válidos para guardar.'
                ];
            }
            $conexion->commit();
            return [
                'status' => 'success',
                'guardados' => $guardados,
                'message' => "Se guardaron exitosamente las calificaciones de {$guardados} aprendices."
            ];
        } catch (Exception $e) {
            if ($transaccion && $conexion && $conexion->inTransaction()) {
                $conexion->rollBack();
            }
            error_log("Error en Hormcali::guardarEvaluacionCompleta: " . $e->getMessage());
            return [
                'status' => 'error',
                'message' => "Error al guardar la evaluación: " . $e->getMessage()
            ];
        }
    }

    /**
     * Obtiene la evaluación individual de un aprendiz específico para una actividad
     */
    public function getEvaluacionAprendiz($idact, $idfic, $idusu = null) {
        $contexto = $this->getContexto();
        $idusuSesion = (int)$contexto['idusu'];
        if (!$contexto['authenticated'] || $idusuSesion !== (int)$idusu || !$this->puedeVerActividad($idfic, $idact)) {
            return null;
        }
        try {
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();

            $sqlNota = "SELECT na.nota, na.estado, na.observacion, na.fecha_evaluacion, u.nomusu AS nominstructor
                        FROM nota_actividad na
                        LEFT JOIN usuario u ON na.idinstructor = u.idusu
                        WHERE na.idact = :idact AND na.idusu = :idusu AND na.idfic = :idfic
                        LIMIT 1";
            $stmtNota = $conexion->prepare($sqlNota);
            $stmtNota->execute([
                ':idact' => (int)$idact,
                ':idusu' => $idusuSesion,
                ':idfic' => (string)$idfic,
            ]);
            $notaData = $stmtNota->fetch(PDO::FETCH_ASSOC);

            $sqlCriterios = "SELECT ca.idcri, ca.nomcri,
                                    COALESCE(ea.valor, 100) AS valor,
                                    COALESCE(ea.estado_criterio, 'Cumple') AS estado_criterio,
                                    ea.observacion
                             FROM criterio_actividad ca
                             LEFT JOIN evaluacion_actividad ea
                                ON ea.idcri = ca.idcri AND ea.idusu = :idusu AND ea.idfic = :idfic
                             WHERE ca.idact = :idact
                             ORDER BY ca.orden ASC, ca.idcri ASC";
            $stmtCri = $conexion->prepare($sqlCriterios);
            $stmtCri->execute([
                ':idact' => (int)$idact,
                ':idusu' => $idusuSesion,
                ':idfic' => (string)$idfic,
            ]);
            $criteriosEvaluados = $stmtCri->fetchAll(PDO::FETCH_ASSOC);

            return [
                'nota' => $notaData['nota'] ?? null,
                'estado' => $notaData['estado'] ?? 'Pendiente de evaluación',
                'observacion' => $notaData['observacion'] ?? '',
                'nominstructor' => $notaData['nominstructor'] ?? 'Instructor del curso',
                'fecha_evaluacion' => $notaData['fecha_evaluacion'] ?? null,
                'criterios' => $criteriosEvaluados
            ];
        } catch (Exception $e) {
            error_log("Error en Hormcali::getEvaluacionAprendiz: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtiene la lista de actividades evaluadas para un aprendiz (Historial personal)
     */
    public function getActividadesEvaluadasAprendiz($idusu = null, $idfic = null) {
        $contexto = $this->getContexto();
        if (!$contexto['authenticated'] || (int)$contexto['idusu'] !== (int)$idusu) {
            return [];
        }
        if ($idfic !== null && $idfic !== '' && !$this->puedeVerFicha($idfic)) {
            return [];
        }
        try {
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $where = 'na.idusu = :idusu';
            $params = [':idusu' => (int)$contexto['idusu']];
            if ($idfic !== null && $idfic !== '') {
                $where .= ' AND na.idfic = :idfic';
                $params[':idfic'] = (string)$idfic;
            }
            $sql = "SELECT na.idact, na.idfic, na.nota, na.estado, na.observacion,
                           na.fecha_evaluacion, a.nomact, a.desact,
                           COALESCE(c.descom, 'Competencia de Aprendizaje') AS nomcom
                    FROM nota_actividad na
                    INNER JOIN actividad a ON na.idact = a.idact
                    LEFT JOIN sesion s ON a.idses = s.idses
                    LEFT JOIN agenda ag ON s.idage = ag.idage
                    LEFT JOIN ficha f ON na.idfic = f.idfic
                    LEFT JOIN proxcom px ON px.codpro = f.codpro
                    LEFT JOIN competencia c ON px.idcom = c.idcom
                    WHERE {$where}
                    ORDER BY na.fecha_evaluacion DESC";
            $stmt = $conexion->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en Hormcali::getActividadesEvaluadasAprendiz: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Calcula métricas para el Dashboard de la vista de calificación
     */
    public function getMetricasDashboard($idact, $idfic, $totalAprendices = 0) {
        if (!$this->puedeVerActividad($idfic, $idact)) {
            return [
                'total_aprendices' => 0,
                'evaluados' => 0,
                'pendientes' => 0,
                'aprobados' => 0,
                'parciales' => 0,
                'no_aprobados' => 0,
                'promedio_general' => 0,
                'porcentaje_aprobacion' => 0
            ];
        }
        try {
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();

            if (empty($idact) || empty($idfic)) {
                return [
                    'total_aprendices' => $totalAprendices,
                    'evaluados' => 0,
                    'pendientes' => $totalAprendices,
                    'aprobados' => 0,
                    'parciales' => 0,
                    'no_aprobados' => 0,
                    'promedio_general' => 0,
                    'porcentaje_aprobacion' => 0
                ];
            }

            $sql = "SELECT 
                        COUNT(idnota) as evaluados,
                        SUM(CASE WHEN nota >= 80 THEN 1 ELSE 0 END) as aprobados,
                        SUM(CASE WHEN nota >= 70 AND nota < 80 THEN 1 ELSE 0 END) as parciales,
                        SUM(CASE WHEN nota < 70 THEN 1 ELSE 0 END) as no_aprobados,
                        AVG(nota) as promedio_general
                    FROM nota_actividad 
                    WHERE idact = :idact AND idfic = :idfic";

            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(':idact', $idact);
            $stmt->bindParam(':idfic', $idfic);
            $stmt->execute();
            $res = $stmt->fetch(PDO::FETCH_ASSOC);

            $evaluados = (int)($res['evaluados'] ?? 0);
            $aprobados = (int)($res['aprobados'] ?? 0);
            $parciales = (int)($res['parciales'] ?? 0);
            $noAprobados = (int)($res['no_aprobados'] ?? 0);
            $promedio = round((float)($res['promedio_general'] ?? 0), 1);
            $pendientes = max(0, $totalAprendices - $evaluados);

            $totalAprobadosYParciales = $aprobados + $parciales;
            $porcentajeAprobacion = ($evaluados > 0) ? round(($totalAprobadosYParciales / $evaluados) * 100, 1) : 0;

            return [
                'total_aprendices' => $totalAprendices,
                'evaluados' => $evaluados,
                'pendientes' => $pendientes,
                'aprobados' => $aprobados,
                'parciales' => $parciales,
                'no_aprobados' => $noAprobados,
                'promedio_general' => $promedio,
                'porcentaje_aprobacion' => $porcentajeAprobacion
            ];
        } catch (Exception $e) {
            error_log("Error en Hormcali::getMetricasDashboard: " . $e->getMessage());
            return [
                'total_aprendices' => $totalAprendices,
                'evaluados' => 0,
                'pendientes' => $totalAprendices,
                'aprobados' => 0,
                'parciales' => 0,
                'no_aprobados' => 0,
                'promedio_general' => 0,
                'porcentaje_aprobacion' => 0
            ];
        }
    }
}
?>
