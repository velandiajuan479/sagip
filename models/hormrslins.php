<?php
require_once 'conexion.php';

class Hormrslins {
    private $conexion;

    public function __construct() {
        $modelo = new Conexion();
        $this->conexion = $modelo->get_conexion();
        $this->actualizarRutasPagina();
    }

    /**
     * Sincronizar automáticamente en la tabla pagina de la BD el nuevo nombre de las vistas
     */
    private function actualizarRutasPagina() {
        try {
            $this->conexion->query("UPDATE pagina SET rutpag = 'views/horvrslins.php' WHERE rutpag = 'views/vrsins.php' OR idpag = 1513");
            $this->conexion->query("UPDATE pagina SET rutpag = 'views/horvrstri.php' WHERE rutpag = 'views/vrpt.php' OR idpag = 1548");
        } catch (Exception $e) {
            // Silencioso si ya está actualizado
        }
    }

    /**
     * Obtener listado de instructores reales en el sistema
     */
    public function getInstructores() {
        try {
            $sql = "SELECT DISTINCT u.idusu, u.nomusu, u.ndocusu, u.emausu
                    FROM usuario u
                    LEFT JOIN usupef up ON u.idusu = up.idusu
                    WHERE up.idper IN (7, 6, 12, 21, 32, 42)
                       OR u.idper IN (7, 6, 12, 21, 32, 42)
                       OR u.idusu IN (SELECT DISTINCT idins FROM agenda WHERE idins IS NOT NULL AND idins > 0)
                       OR u.idusu IN (SELECT DISTINCT idusu FROM agenda WHERE idusu IS NOT NULL AND idusu > 0)
                       OR u.idusu IN (SELECT DISTINCT idusu FROM horario WHERE idusu IS NOT NULL AND idusu > 0)
                    ORDER BY u.nomusu ASC";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Hormrslins::getInstructores: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener información básica de un instructor
     */
    public function getInstructorInfo($idusu) {
        if (!$idusu) return null;
        try {
            $sql = "SELECT idusu, nomusu, ndocusu, emausu, telcan
                    FROM usuario 
                    WHERE idusu = :idusu";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindValue(':idusu', $idusu, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * Obtener las fichas asignadas a un instructor
     */
    public function getFichasPorInstructor($idusu) {
        if (!$idusu) return [];
        try {
            $sql = "SELECT DISTINCT f.idfic, f.nomfic, p.codpro, p.nompro, v.nomval AS jornada
                    FROM ficha f
                    LEFT JOIN programa p ON f.codpro = p.codpro
                    LEFT JOIN valor v ON f.jornada = v.idval
                    WHERE f.idfic IN (
                        SELECT idfic FROM agenda WHERE (idins = :idusu1 OR idusu = :idusu2) AND idfic IS NOT NULL AND idfic <> ''
                        UNION
                        SELECT idfic FROM horario WHERE idusu = :idusu3 AND idfic IS NOT NULL AND idfic <> ''
                        UNION
                        SELECT idfic FROM usufic WHERE idusu = :idusu4 AND idfic IS NOT NULL AND idfic <> ''
                    )
                    ORDER BY f.idfic DESC";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindValue(':idusu1', $idusu, PDO::PARAM_INT);
            $stmt->bindValue(':idusu2', $idusu, PDO::PARAM_INT);
            $stmt->bindValue(':idusu3', $idusu, PDO::PARAM_INT);
            $stmt->bindValue(':idusu4', $idusu, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Hormrslins::getFichasPorInstructor: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener datos del programa y ficha para el encabezado estándar
     */
    public function getFichaPrograma($idfic) {
        if (!$idfic) return [];
        try {
            $sql = "SELECT f.idfic, f.nomfic, p.codpro, p.nompro, p.verpro,
                           p.horlpro, p.horppro, p.crelpro, p.creppro, p.redcon,
                           a.nomare, v.nomval
                    FROM ficha f
                    LEFT JOIN programa p ON f.codpro = p.codpro
                    LEFT JOIN area a ON p.idare = a.idare
                    LEFT JOIN valor v ON f.jornada = v.idval
                    WHERE f.idfic = :idfic";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindValue(':idfic', $idfic);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Obtener competencias y resultados asociados al instructor (y ficha opcional)
     */
    public function getResultadosPorInstructorId($idusu, $idfic = null) {
        if (!$idusu) return [];
        try {
            // 1. Intentar obtener desde agenda directamente
            $sql = "SELECT DISTINCT 
                        c.idcom, c.descom, 
                        r.idres, r.nomres, 
                        f.idfic, f.nomfic,
                        p.codpro, p.nompro,
                        a.fas, a.actpro, a.horcom, a.fecini, a.fecfin
                    FROM agenda a
                    INNER JOIN ficha f ON a.idfic = f.idfic
                    LEFT JOIN programa p ON f.codpro = p.codpro
                    LEFT JOIN competencia c ON a.idcom = c.idcom
                    LEFT JOIN resultado r ON a.idres = r.idres
                    WHERE (a.idins = :idusu1 OR a.idusu = :idusu2)
                      AND (:idfic IS NULL OR :idfic_vacio = '' OR f.idfic = :idfic_val)
                      AND c.idcom IS NOT NULL
                    ORDER BY f.idfic, c.idcom, r.idres";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindValue(':idusu1', $idusu, PDO::PARAM_INT);
            $stmt->bindValue(':idusu2', $idusu, PDO::PARAM_INT);
            $stmt->bindValue(':idfic', $idfic);
            $stmt->bindValue(':idfic_vacio', $idfic);
            $stmt->bindValue(':idfic_val', $idfic);
            $stmt->execute();
            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // 2. Si no hay registros en agenda pero el instructor tiene ficha asignada, traer el programa
            if (empty($resultados)) {
                $fichasInst = $this->getFichasPorInstructor($idusu);
                if (!empty($fichasInst)) {
                    $targetFic = $idfic ? $idfic : $fichasInst[0]['idfic'];
                    $sqlFallback = "SELECT DISTINCT 
                                        c.idcom, c.descom, 
                                        r.idres, r.nomres, 
                                        f.idfic, f.nomfic,
                                        p.codpro, p.nompro,
                                        'ETAPA LECTIVA' as fas,
                                        'Formación asignada' as actpro,
                                        c.horcom,
                                        f.finific as fecini,
                                        f.ffinfic as fecfin
                                    FROM ficha f
                                    INNER JOIN programa p ON f.codpro = p.codpro
                                    INNER JOIN proxcom pc ON p.codpro = pc.codpro
                                    INNER JOIN competencia c ON pc.idcom = c.idcom
                                    INNER JOIN resultado r ON c.idcom = r.idcom
                                    WHERE f.idfic = :targetFic
                                    ORDER BY c.idcom, r.idres";
                    $stmtFb = $this->conexion->prepare($sqlFallback);
                    $stmtFb->bindValue(':targetFic', $targetFic);
                    $stmtFb->execute();
                    $resultados = $stmtFb->fetchAll(PDO::FETCH_ASSOC);
                }
            }

            return $resultados;
        } catch (PDOException $e) {
            error_log("Error en Hormrslins::getResultadosPorInstructorId: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener estadísticas KPI para el dashboard superior
     */
    public function getEstadisticas($idusu = null, $idfic = null) {
        $kpi = [
            'total_instructores' => 0,
            'total_fichas' => 0,
            'total_competencias' => 0,
            'total_resultados' => 0,
            'total_horas' => 0
        ];

        try {
            // Total instructores registrados
            $sqlInst = "SELECT COUNT(DISTINCT u.idusu) as cant
                        FROM usuario u
                        LEFT JOIN usupef up ON u.idusu = up.idusu
                        WHERE up.idper IN (7, 6, 12, 21, 32, 42)
                           OR u.idper IN (7, 6, 12, 21, 32, 42)";
            $stmt = $this->conexion->query($sqlInst);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $kpi['total_instructores'] = $row ? (int)$row['cant'] : 0;

            if ($idusu) {
                // Fichas del instructor
                $fichas = $this->getFichasPorInstructor($idusu);
                $kpi['total_fichas'] = count($fichas);

                // Competencias y resultados del instructor
                $comRes = $this->getResultadosPorInstructorId($idusu, $idfic);
                $comps = [];
                $res = [];
                $horas = 0;
                foreach ($comRes as $cr) {
                    $comps[$cr['idcom']] = true;
                    $res[$cr['idres']] = true;
                    if (!empty($cr['horcom']) && is_numeric($cr['horcom'])) {
                        $horas += (int)$cr['horcom'];
                    }
                }
                $kpi['total_competencias'] = count($comps);
                $kpi['total_resultados'] = count($res);
                $kpi['total_horas'] = $horas;
            } else {
                // Totales globales en el módulo de agendas
                $sqlGlobal = "SELECT 
                                COUNT(DISTINCT a.idfic) as cant_fichas,
                                COUNT(DISTINCT a.idcom) as cant_comp,
                                COUNT(DISTINCT a.idres) as cant_res,
                                COALESCE(SUM(a.horcom), 0) as cant_horas
                              FROM agenda a";
                $stmt = $this->conexion->query($sqlGlobal);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($row) {
                    $kpi['total_fichas'] = (int)$row['cant_fichas'];
                    $kpi['total_competencias'] = (int)$row['cant_comp'];
                    $kpi['total_resultados'] = (int)$row['cant_res'];
                    $kpi['total_horas'] = (int)$row['cant_horas'];
                }
            }
        } catch (PDOException $e) {
            error_log("Error en Hormrslins::getEstadisticas: " . $e->getMessage());
        }

        return $kpi;
    }
}
?>
