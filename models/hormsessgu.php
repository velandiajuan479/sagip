<?php
require_once(__DIR__ . '/conexion.php');

class Msseg {
    private $idses;
    private $idage;
    private $fecses;
    private $ideva;
    private $idusu;
    private $comeva;
    private $valeva;
    private $idfic;
    private $idcom;
    private $idres;
    private $descom;

    // Perfiles considerados Administrador (ven todo y gestionan)
    private $rolesAdministrador = [3, 4, 5, 8];

    // Perfiles considerados Coordinador (ven TODAS las fichas)
    private $rolesCoordinador = [1, 2];

    // Perfiles considerados Instructor/Formador (ven solo sus fichas)
    private $rolesInstructor = [6, 7, 9, 10, 21, 28, 43];

    public function __construct() {
        $this->crearTablasSiNoExisten();
    }

    // Métodos Get y Set
    public function getIdses() { return $this->idses; }
    public function setIdses($idses) { $this->idses = $idses; }

    public function getIdage() { return $this->idage; }
    public function setIdage($idage) { $this->idage = $idage; }

    public function getFecses() { return $this->fecses; }
    public function setFecses($fecses) { $this->fecses = $fecses; }

    public function getIdeva() { return $this->ideva; }
    public function setIdeva($ideva) { $this->ideva = $ideva; }

    public function getIdusu() { return $this->idusu; }
    public function setIdusu($idusu) { $this->idusu = $idusu; }

    public function getComeva() { return $this->comeva; }
    public function setComeva($comeva) { $this->comeva = $comeva; }

    public function getValeva() { return $this->valeva; }
    public function setValeva($valeva) { $this->valeva = $valeva; }

    public function getIdfic() { return $this->idfic; }
    public function setIdfic($idfic) { $this->idfic = $idfic; }

    public function getIdcom() { return $this->idcom; }
    public function setIdcom($idcom) { $this->idcom = $idcom; }

    public function getIdres() { return $this->idres; }
    public function setIdres($idres) { $this->idres = $idres; }

    public function getDescom() { return $this->descom; }
    public function setDescom($descom) { $this->descom = $descom; }

    /**
     * Verifica si el usuario es Administrador (acceso total)
     */
    public function esAdministrador($idusu, $idper = null) {
        if ($idper && in_array((int)$idper, $this->rolesAdministrador, true)) {
            return true;
        }
        if ($idusu) {
            try {
                $modelo = new conexion();
                $conexion = $modelo->get_conexion();
                $sql = "SELECT idper FROM usuario WHERE idusu = :idusu
                        UNION
                        SELECT idper FROM usupef WHERE idusu = :idusu";
                $stmt = $conexion->prepare($sql);
                $stmt->bindParam(':idusu', $idusu);
                $stmt->execute();
                $roles = $stmt->fetchAll(PDO::FETCH_COLUMN);
                foreach ($roles as $r) {
                    if (in_array((int)$r, $this->rolesAdministrador, true)) return true;
                }
            } catch (Exception $e) {
                error_log("Error en esAdministrador: " . $e->getMessage());
            }
        }
        return false;
    }

    /**
     * Verifica si el usuario es Coordinador (ve todas las fichas y sesiones)
     */
    public function esCoordinador($idusu, $idper = null) {
        if ($idper && in_array((int)$idper, $this->rolesCoordinador, true)) {
            return true;
        }
        if ($idusu) {
            try {
                $modelo = new conexion();
                $conexion = $modelo->get_conexion();
                $sql = "SELECT idper FROM usuario WHERE idusu = :idusu
                        UNION
                        SELECT idper FROM usupef WHERE idusu = :idusu";
                $stmt = $conexion->prepare($sql);
                $stmt->bindParam(':idusu', $idusu);
                $stmt->execute();
                $roles = $stmt->fetchAll(PDO::FETCH_COLUMN);
                foreach ($roles as $r) {
                    if (in_array((int)$r, $this->rolesCoordinador, true)) return true;
                }
            } catch (Exception $e) {
                error_log("Error en esCoordinador: " . $e->getMessage());
            }
        }
        return false;
    }

    /**
     * Verifica si el usuario es Instructor/Formador
     */
    public function esInstructor($idusu, $idper = null) {
        if ($idper && in_array((int)$idper, $this->rolesInstructor, true)) {
            return true;
        }
        if ($idusu) {
            try {
                $modelo = new conexion();
                $conexion = $modelo->get_conexion();
                $sql = "SELECT idper FROM usuario WHERE idusu = :idusu
                        UNION
                        SELECT idper FROM usupef WHERE idusu = :idusu";
                $stmt = $conexion->prepare($sql);
                $stmt->bindParam(':idusu', $idusu);
                $stmt->execute();
                $roles = $stmt->fetchAll(PDO::FETCH_COLUMN);
                foreach ($roles as $r) {
                    if (in_array((int)$r, $this->rolesInstructor, true)) return true;
                }
            } catch (Exception $e) {
                error_log("Error en esInstructor: " . $e->getMessage());
            }
        }
        return false;
    }

    /**
     * Valida si el usuario activo tiene rol docente / instructor / administrativo
     * (Mantiene compatibilidad: retorna true si es Coordinador O Instructor)
     */
    public function esDocenteOAdmin($idusu, $idper = null) {
        return $this->esCoordinador($idusu, $idper) || $this->esInstructor($idusu, $idper);
    }

    /**
     * Asegura que las tablas necesarias existan en la base de datos
     */
    public function crearTablasSiNoExisten() {
        try {
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();

            $sqlSes = "CREATE TABLE IF NOT EXISTS ses_seguimiento (
                idses BIGINT(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                idfic VARCHAR(20) NOT NULL,
                idcom BIGINT(20) NOT NULL,
                idres BIGINT(20) NOT NULL,
                idusu BIGINT(20) DEFAULT NULL,
                numses INT(11) NOT NULL,
                fecses DATE NOT NULL,
                horini VARCHAR(20) DEFAULT NULL,
                horfin VARCHAR(20) DEFAULT NULL,
                mom_bienvenida TEXT DEFAULT NULL,
                mom_teoria TEXT DEFAULT NULL,
                mom_practica TEXT DEFAULT NULL,
                mom_cierre TEXT DEFAULT NULL,
                fec_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_fic_com (idfic, idcom),
                INDEX idx_fic_res (idfic, idres),
                INDEX idx_com (idcom)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            $conexion->exec($sqlSes);

            $sqlEva = "CREATE TABLE IF NOT EXISTS eva_seguimiento (
                ideva BIGINT(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                idses BIGINT(20) NOT NULL,
                idusu BIGINT(20) NOT NULL,
                valeva INT(11) NOT NULL COMMENT '1: Se impartió, 2: Parcialmente, 3: No se impartió',
                comeva TEXT DEFAULT NULL,
                fec_evaluacion DATETIME DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_ses_usu (idses, idusu)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            $conexion->exec($sqlEva);

        } catch (Exception $e) {
            error_log("Error creando tablas en Msseg: " . $e->getMessage());
        }
    }

    /**
     * Obtiene el listado de TODAS las fichas activas (para Coordinador)
     */
    public function getFic() {
        try {
            $sql = "SELECT f.idfic, f.nomfic, v.nomval, f.codpro, a.nomare 
                    FROM ficha AS f 
                    INNER JOIN valor AS v ON f.jornada = v.idval 
                    LEFT JOIN programa AS p ON f.codpro = p.codpro 
                    LEFT JOIN area AS a ON p.idare = a.idare 
                    WHERE f.idfic > 1000 
                    ORDER BY f.idfic ASC, f.nomfic ASC";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->execute();
            return $result->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en getFic: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene las fichas donde un Instructor tiene clases asignadas
     */
    public function getFichasByInstructor($idusu) {
        if (!$idusu) return [];
        try {
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            // Se consulta la tabla horario que vincula instructor (idusu) con ficha (idfic)
            $sql = "SELECT DISTINCT f.idfic, f.nomfic, v.nomval, f.codpro, a.nomare
                    FROM horario h
                    INNER JOIN ficha f ON h.idfic = f.idfic
                    INNER JOIN valor v ON f.jornada = v.idval
                    LEFT JOIN programa p ON f.codpro = p.codpro
                    LEFT JOIN area a ON p.idare = a.idare
                    WHERE h.idusu = :idusu AND f.idfic > 1000
                    ORDER BY f.idfic ASC";
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(':idusu', $idusu);
            $stmt->execute();
            $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
            // Si no hay horarios asignados, devolver todas las fichas como fallback
            if (empty($res)) {
                return $this->getFic();
            }
            return $res;
        } catch (Exception $e) {
            error_log("Error en getFichasByInstructor: " . $e->getMessage());
            return $this->getFic();
        }
    }

    /**
     * Obtiene la(s) ficha(s) a la(s) que pertenece un Aprendiz
     */
    public function getFichaByAprendiz($idusu) {
        if (!$idusu) return [];
        try {
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $sql = "SELECT DISTINCT f.idfic, f.nomfic, v.nomval, f.codpro, a.nomare
                    FROM aprendiz ap
                    INNER JOIN ficha f ON ap.idfic = f.idfic
                    INNER JOIN valor v ON f.jornada = v.idval
                    LEFT JOIN programa p ON f.codpro = p.codpro
                    LEFT JOIN area a ON p.idare = a.idare
                    WHERE ap.idusu = :idusu AND f.idfic > 1000
                    ORDER BY f.idfic ASC";
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(':idusu', $idusu);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en getFichaByAprendiz: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene las competencias asociadas a una ficha según su programa formativo
     */
    public function getCompetenciasByFicha($idfic) {
        if (!$idfic) return [];
        try {
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();

            $sql = "SELECT DISTINCT c.idcom, c.descom, c.vercom, c.horcom 
                    FROM proxcom px 
                    INNER JOIN competencia c ON px.idcom = c.idcom 
                    INNER JOIN ficha f ON px.codpro = f.codpro 
                    WHERE f.idfic = :idfic 
                    ORDER BY c.descom ASC";
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(':idfic', $idfic);
            $stmt->execute();
            $res = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Fallback: todas las competencias con resultados
            if (empty($res)) {
                $sqlFallback = "SELECT DISTINCT c.idcom, c.descom, c.vercom, c.horcom 
                                FROM competencia c 
                                INNER JOIN resultado r ON c.idcom = r.idcom 
                                ORDER BY c.descom ASC";
                $stmtFb = $conexion->query($sqlFallback);
                $res = $stmtFb->fetchAll(PDO::FETCH_ASSOC);
            }

            return $res;
        } catch (Exception $e) {
            error_log("Error en getCompetenciasByFicha: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene todas las competencias que poseen resultados de aprendizaje
     */
    public function getAllCompetenciasConResultados() {
        try {
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $sql = "SELECT DISTINCT c.idcom, c.descom, c.vercom, c.horcom 
                    FROM competencia c 
                    INNER JOIN resultado r ON c.idcom = r.idcom 
                    ORDER BY c.descom ASC";
            $stmt = $conexion->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Obtiene los resultados de aprendizaje vinculados a una competencia
     */
    public function getResultadosByCompetencia($idcom) {
        if (!$idcom) return [];
        try {
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();

            $sql = "SELECT r.idres, r.nomres, COALESCE(r.ndeses, 0) AS ndeses, r.idcom 
                    FROM resultado r 
                    WHERE r.idcom = :idcom 
                    ORDER BY r.idres ASC";
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(':idcom', $idcom);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en getResultadosByCompetencia: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene los datos detallados de un resultado de aprendizaje específico
     */
    public function getResultadoInfo($idres) {
        if (!$idres) return null;
        try {
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();

            $sql = "SELECT r.idres, r.nomres, COALESCE(r.ndeses, 0) AS ndeses, r.idcom, c.descom 
                    FROM resultado r 
                    LEFT JOIN competencia c ON r.idcom = c.idcom 
                    WHERE r.idres = :idres 
                    LIMIT 1";
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(':idres', $idres);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en getResultadoInfo: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Calcula el siguiente número de sesión para una ficha y competencia
     */
    public function getNextNumSesion($idfic, $idcom) {
        try {
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();

            $sql = "SELECT COALESCE(MAX(numses), 0) + 1 AS next_num 
                    FROM ses_seguimiento 
                    WHERE idfic = :idfic AND idcom = :idcom";
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(':idfic', $idfic);
            $stmt->bindParam(':idcom', $idcom);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)($row['next_num'] ?? 1);
        } catch (Exception $e) {
            return 1;
        }
    }

    /**
     * Obtiene el total de sesiones para una ficha y competencia (para calcular páginas)
     */
    public function getTotalSesionesByComp($idfic, $idcom) {
        if (!$idfic || !$idcom) return 0;
        try {
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $sql = "SELECT COUNT(*) AS total FROM ses_seguimiento WHERE idfic = :idfic AND idcom = :idcom";
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(':idfic', $idfic);
            $stmt->bindParam(':idcom', $idcom);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)($row['total'] ?? 0);
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Obtiene el ID de la última sesión creada (mayor numses) para ficha y competencia.
     * Esta es la única que el aprendiz puede calificar.
     */
    public function getIdUltimaSesion($idfic, $idcom) {
        if (!$idfic || !$idcom) return null;
        try {
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $sql = "SELECT idses FROM ses_seguimiento 
                    WHERE idfic = :idfic AND idcom = :idcom 
                    ORDER BY numses DESC LIMIT 1";
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(':idfic', $idfic);
            $stmt->bindParam(':idcom', $idcom);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ? (int)$row['idses'] : null;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Obtiene las sesiones paginadas para una ficha y competencia,
     * ordenadas por numses DESC (más recientes primero).
     * $page comienza en 1. $limit es el máximo por página (3).
     */
    public function getSesionesByCompetencia($idfic, $idcom, $page = 1, $limit = 3, $idusuAprendiz = null) {
        if (!$idfic || !$idcom) return [];
        try {
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();

            $page   = max(1, (int)$page);
            $limit  = max(1, (int)$limit);
            $offset = ($page - 1) * $limit;

            $sql = "SELECT s.*, 
                           u.nomusu AS nom_instructor,
                           c.descom,
                           r.nomres,
                           (SELECT COUNT(*) FROM eva_seguimiento e WHERE e.idses = s.idses AND e.valeva = 1) AS tot_impartida,
                           (SELECT COUNT(*) FROM eva_seguimiento e WHERE e.idses = s.idses AND e.valeva = 2) AS tot_parcial,
                           (SELECT COUNT(*) FROM eva_seguimiento e WHERE e.idses = s.idses AND e.valeva = 3) AS tot_no_impartida,
                           (SELECT COUNT(*) FROM eva_seguimiento e WHERE e.idses = s.idses) AS tot_evaluaciones
                    FROM ses_seguimiento s 
                    LEFT JOIN usuario u ON s.idusu = u.idusu
                    LEFT JOIN competencia c ON s.idcom = c.idcom
                    LEFT JOIN resultado r ON s.idres = r.idres
                    WHERE s.idfic = :idfic AND s.idcom = :idcom 
                    ORDER BY s.numses DESC
                    LIMIT :limit OFFSET :offset";

            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(':idfic', $idfic);
            $stmt->bindParam(':idcom', $idcom);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            $sesiones = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Si hay un aprendiz en sesión, traer su evaluación individual
            if ($idusuAprendiz) {
                foreach ($sesiones as &$ses) {
                    $ses['eval_aprendiz'] = $this->getEvaluacionAprendiz($ses['idses'], $idusuAprendiz);
                }
            }

            return $sesiones;
        } catch (Exception $e) {
            error_log("Error en getSesionesByCompetencia: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene las sesiones registradas para un resultado y ficha (compatibilidad legacy)
     */
    public function getSesionesByResultado($idfic, $idres, $idusuAprendiz = null) {
        if (!$idfic || !$idres) return [];
        try {
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();

            $sql = "SELECT s.*, 
                           u.nomusu AS nom_instructor,
                           (SELECT COUNT(*) FROM eva_seguimiento e WHERE e.idses = s.idses AND e.valeva = 1) AS tot_impartida,
                           (SELECT COUNT(*) FROM eva_seguimiento e WHERE e.idses = s.idses AND e.valeva = 2) AS tot_parcial,
                           (SELECT COUNT(*) FROM eva_seguimiento e WHERE e.idses = s.idses AND e.valeva = 3) AS tot_no_impartida,
                           (SELECT COUNT(*) FROM eva_seguimiento e WHERE e.idses = s.idses) AS tot_evaluaciones
                    FROM ses_seguimiento s 
                    LEFT JOIN usuario u ON s.idusu = u.idusu 
                    WHERE s.idfic = :idfic AND s.idres = :idres 
                    ORDER BY s.numses ASC, s.fecses ASC";
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(':idfic', $idfic);
            $stmt->bindParam(':idres', $idres);
            $stmt->execute();
            $sesiones = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($idusuAprendiz) {
                foreach ($sesiones as &$ses) {
                    $ses['eval_aprendiz'] = $this->getEvaluacionAprendiz($ses['idses'], $idusuAprendiz);
                }
            }

            return $sesiones;
        } catch (Exception $e) {
            error_log("Error en getSesionesByResultado: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene una sesión individual por su ID
     */
    public function getSesionById($idses) {
        if (!$idses) return null;
        try {
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();

            $sql = "SELECT s.*, u.nomusu AS nom_instructor 
                    FROM ses_seguimiento s 
                    LEFT JOIN usuario u ON s.idusu = u.idusu 
                    WHERE s.idses = :idses";
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(':idses', $idses);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en getSesionById: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Registra o actualiza una sesión de seguimiento
     */
    public function guardarSesion($datos) {
        try {
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();

            $idses          = !empty($datos['idses']) ? (int)$datos['idses'] : null;
            $idfic          = trim($datos['idfic'] ?? '');
            $idcom          = (int)($datos['idcom'] ?? 0);
            $idres          = (int)($datos['idres'] ?? 0);
            $idusu          = !empty($datos['idusu']) ? (int)$datos['idusu'] : null;
            $numses         = (int)($datos['numses'] ?? 1);
            $fecses         = trim($datos['fecses'] ?? date('Y-m-d'));
            $horini         = trim($datos['horini'] ?? '');
            $horfin         = trim($datos['horfin'] ?? '');
            $mom_bienvenida = trim($datos['mom_bienvenida'] ?? '');
            $mom_teoria     = trim($datos['mom_teoria'] ?? '');
            $mom_practica   = trim($datos['mom_practica'] ?? '');
            $mom_cierre     = trim($datos['mom_cierre'] ?? '');

            if ($idses) {
                $sql = "UPDATE ses_seguimiento SET 
                            numses = :numses,
                            fecses = :fecses,
                            horini = :horini,
                            horfin = :horfin,
                            mom_bienvenida = :mom_bienvenida,
                            mom_teoria = :mom_teoria,
                            mom_practica = :mom_practica,
                            mom_cierre = :mom_cierre
                        WHERE idses = :idses";
                $stmt = $conexion->prepare($sql);
                $stmt->bindParam(':numses', $numses);
                $stmt->bindParam(':fecses', $fecses);
                $stmt->bindParam(':horini', $horini);
                $stmt->bindParam(':horfin', $horfin);
                $stmt->bindParam(':mom_bienvenida', $mom_bienvenida);
                $stmt->bindParam(':mom_teoria', $mom_teoria);
                $stmt->bindParam(':mom_practica', $mom_practica);
                $stmt->bindParam(':mom_cierre', $mom_cierre);
                $stmt->bindParam(':idses', $idses);
                $stmt->execute();
                return ['status' => true, 'idses' => $idses, 'msg' => 'Sesión actualizada correctamente'];
            } else {
                $sql = "INSERT INTO ses_seguimiento 
                        (idfic, idcom, idres, idusu, numses, fecses, horini, horfin, mom_bienvenida, mom_teoria, mom_practica, mom_cierre) 
                        VALUES 
                        (:idfic, :idcom, :idres, :idusu, :numses, :fecses, :horini, :horfin, :mom_bienvenida, :mom_teoria, :mom_practica, :mom_cierre)";
                $stmt = $conexion->prepare($sql);
                $stmt->bindParam(':idfic', $idfic);
                $stmt->bindParam(':idcom', $idcom);
                $stmt->bindParam(':idres', $idres);
                $stmt->bindParam(':idusu', $idusu);
                $stmt->bindParam(':numses', $numses);
                $stmt->bindParam(':fecses', $fecses);
                $stmt->bindParam(':horini', $horini);
                $stmt->bindParam(':horfin', $horfin);
                $stmt->bindParam(':mom_bienvenida', $mom_bienvenida);
                $stmt->bindParam(':mom_teoria', $mom_teoria);
                $stmt->bindParam(':mom_practica', $mom_practica);
                $stmt->bindParam(':mom_cierre', $mom_cierre);
                $stmt->execute();
                $nuevoId = $conexion->lastInsertId();
                return ['status' => true, 'idses' => $nuevoId, 'msg' => 'Sesión registrada correctamente'];
            }
        } catch (Exception $e) {
            error_log("Error en guardarSesion: " . $e->getMessage());
            return ['status' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Elimina una sesión y sus evaluaciones
     */
    public function eliminarSesion($idses) {
        if (!$idses) return false;
        try {
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();

            $sqlEva = "DELETE FROM eva_seguimiento WHERE idses = :idses";
            $stmtEva = $conexion->prepare($sqlEva);
            $stmtEva->bindParam(':idses', $idses);
            $stmtEva->execute();

            $sqlSes = "DELETE FROM ses_seguimiento WHERE idses = :idses";
            $stmtSes = $conexion->prepare($sqlSes);
            $stmtSes->bindParam(':idses', $idses);
            $stmtSes->execute();

            return true;
        } catch (Exception $e) {
            error_log("Error en eliminarSesion: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Guarda o actualiza la evaluación de un aprendiz para una sesión
     */
    public function guardarEvaluacion($idses, $idusu, $valeva, $comeva) {
        if (!$idses || !$idusu || !$valeva) {
            return ['status' => false, 'error' => 'Faltan parámetros de evaluación'];
        }
        try {
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();

            $sqlCheck = "SELECT ideva FROM eva_seguimiento WHERE idses = :idses AND idusu = :idusu LIMIT 1";
            $stmtCheck = $conexion->prepare($sqlCheck);
            $stmtCheck->bindParam(':idses', $idses);
            $stmtCheck->bindParam(':idusu', $idusu);
            $stmtCheck->execute();
            $existente = $stmtCheck->fetch(PDO::FETCH_ASSOC);

            if ($existente) {
                $sqlUpd = "UPDATE eva_seguimiento SET 
                                valeva = :valeva, 
                                comeva = :comeva, 
                                fec_evaluacion = CURRENT_TIMESTAMP 
                           WHERE ideva = :ideva";
                $stmtUpd = $conexion->prepare($sqlUpd);
                $stmtUpd->bindParam(':valeva', $valeva);
                $stmtUpd->bindParam(':comeva', $comeva);
                $stmtUpd->bindParam(':ideva', $existente['ideva']);
                $stmtUpd->execute();
                return ['status' => true, 'ideva' => $existente['ideva'], 'msg' => 'Evaluación actualizada con éxito'];
            } else {
                $sqlIns = "INSERT INTO eva_seguimiento (idses, idusu, valeva, comeva) 
                           VALUES (:idses, :idusu, :valeva, :comeva)";
                $stmtIns = $conexion->prepare($sqlIns);
                $stmtIns->bindParam(':idses', $idses);
                $stmtIns->bindParam(':idusu', $idusu);
                $stmtIns->bindParam(':valeva', $valeva);
                $stmtIns->bindParam(':comeva', $comeva);
                $stmtIns->execute();
                $nuevoId = $conexion->lastInsertId();
                return ['status' => true, 'ideva' => $nuevoId, 'msg' => 'Evaluación registrada con éxito'];
            }
        } catch (Exception $e) {
            error_log("Error en guardarEvaluacion: " . $e->getMessage());
            return ['status' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Obtiene la evaluación individual de un aprendiz para una sesión específica
     */
    public function getEvaluacionAprendiz($idses, $idusu) {
        if (!$idses || !$idusu) return null;
        try {
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();

            $sql = "SELECT * FROM eva_seguimiento WHERE idses = :idses AND idusu = :idusu LIMIT 1";
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(':idses', $idses);
            $stmt->bindParam(':idusu', $idusu);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Calcula métricas y estadísticas de avance para la tarjeta de resumen
     * Ahora opera a nivel de ficha + competencia
     */
    public function getEstadisticasByCompetencia($idfic, $idcom) {
        $resultado = [
            'titulo_com'         => 'Competencia',
            'total_sesiones'     => 0,
            'cant_impartidas'    => 0,
            'cant_parciales'     => 0,
            'cant_no_impartidas' => 0,
            'total_evaluaciones' => 0
        ];

        if (!$idfic || !$idcom) return $resultado;

        try {
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();

            // Nombre de la competencia
            $sqlCom = "SELECT descom FROM competencia WHERE idcom = :idcom LIMIT 1";
            $stmtCom = $conexion->prepare($sqlCom);
            $stmtCom->bindParam(':idcom', $idcom);
            $stmtCom->execute();
            $rowCom = $stmtCom->fetch(PDO::FETCH_ASSOC);
            if ($rowCom) $resultado['titulo_com'] = $rowCom['descom'];

            // Total de sesiones registradas para esta ficha+competencia
            $sqlPlan = "SELECT COUNT(*) AS total FROM ses_seguimiento WHERE idfic = :idfic AND idcom = :idcom";
            $stmtPlan = $conexion->prepare($sqlPlan);
            $stmtPlan->bindParam(':idfic', $idfic);
            $stmtPlan->bindParam(':idcom', $idcom);
            $stmtPlan->execute();
            $rowPlan = $stmtPlan->fetch(PDO::FETCH_ASSOC);
            $resultado['total_sesiones'] = (int)($rowPlan['total'] ?? 0);

            // Conteo de evaluaciones
            $sqlEva = "SELECT 
                        SUM(CASE WHEN e.valeva = 1 THEN 1 ELSE 0 END) AS cant_impartidas,
                        SUM(CASE WHEN e.valeva = 2 THEN 1 ELSE 0 END) AS cant_parciales,
                        SUM(CASE WHEN e.valeva = 3 THEN 1 ELSE 0 END) AS cant_no_impartidas,
                        COUNT(e.ideva) AS total_evaluaciones
                       FROM eva_seguimiento e 
                       INNER JOIN ses_seguimiento s ON e.idses = s.idses 
                       WHERE s.idfic = :idfic AND s.idcom = :idcom";
            $stmtEva = $conexion->prepare($sqlEva);
            $stmtEva->bindParam(':idfic', $idfic);
            $stmtEva->bindParam(':idcom', $idcom);
            $stmtEva->execute();
            $rowEva = $stmtEva->fetch(PDO::FETCH_ASSOC);

            if ($rowEva) {
                $resultado['cant_impartidas']    = (int)($rowEva['cant_impartidas'] ?? 0);
                $resultado['cant_parciales']     = (int)($rowEva['cant_parciales'] ?? 0);
                $resultado['cant_no_impartidas'] = (int)($rowEva['cant_no_impartidas'] ?? 0);
                $resultado['total_evaluaciones'] = (int)($rowEva['total_evaluaciones'] ?? 0);
            }

            return $resultado;
        } catch (Exception $e) {
            error_log("Error en getEstadisticasByCompetencia: " . $e->getMessage());
            return $resultado;
        }
    }

    /**
     * Mantiene compatibilidad con código antiguo que usaba getEstadisticasResultado
     */
    public function getEstadisticasResultado($idfic, $idres) {
        $resultado = [
            'titulo_res'      => 'Resultado de Aprendizaje',
            'total_planeadas' => 0,
            'total_estimadas' => 0,
            'porcentaje'      => 0,
            'cant_impartidas' => 0,
            'cant_parciales'  => 0,
            'cant_no_impartidas' => 0,
            'total_evaluaciones' => 0
        ];
        if (!$idfic || !$idres) return $resultado;
        try {
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $infoRes = $this->getResultadoInfo($idres);
            if ($infoRes) {
                $resultado['titulo_res']      = $infoRes['nomres'];
                $resultado['total_estimadas'] = (int)$infoRes['ndeses'];
            }
            $sqlPlan = "SELECT COUNT(*) AS total_plan FROM ses_seguimiento WHERE idfic = :idfic AND idres = :idres";
            $stmtPlan = $conexion->prepare($sqlPlan);
            $stmtPlan->bindParam(':idfic', $idfic);
            $stmtPlan->bindParam(':idres', $idres);
            $stmtPlan->execute();
            $rowPlan = $stmtPlan->fetch(PDO::FETCH_ASSOC);
            $resultado['total_planeadas'] = (int)($rowPlan['total_plan'] ?? 0);
            if ($resultado['total_estimadas'] > 0) {
                $pct = round(($resultado['total_planeadas'] / $resultado['total_estimadas']) * 100);
                $resultado['porcentaje'] = min(100, max(0, $pct));
            } else {
                $resultado['porcentaje'] = $resultado['total_planeadas'] > 0 ? 100 : 0;
            }
            $sqlEva = "SELECT 
                        SUM(CASE WHEN e.valeva = 1 THEN 1 ELSE 0 END) AS cant_impartidas,
                        SUM(CASE WHEN e.valeva = 2 THEN 1 ELSE 0 END) AS cant_parciales,
                        SUM(CASE WHEN e.valeva = 3 THEN 1 ELSE 0 END) AS cant_no_impartidas,
                        COUNT(e.ideva) AS total_evaluaciones
                       FROM eva_seguimiento e 
                       INNER JOIN ses_seguimiento s ON e.idses = s.idses 
                       WHERE s.idfic = :idfic AND s.idres = :idres";
            $stmtEva = $conexion->prepare($sqlEva);
            $stmtEva->bindParam(':idfic', $idfic);
            $stmtEva->bindParam(':idres', $idres);
            $stmtEva->execute();
            $rowEva = $stmtEva->fetch(PDO::FETCH_ASSOC);
            if ($rowEva) {
                $resultado['cant_impartidas']    = (int)($rowEva['cant_impartidas'] ?? 0);
                $resultado['cant_parciales']     = (int)($rowEva['cant_parciales'] ?? 0);
                $resultado['cant_no_impartidas'] = (int)($rowEva['cant_no_impartidas'] ?? 0);
                $resultado['total_evaluaciones'] = (int)($rowEva['total_evaluaciones'] ?? 0);
            }
            return $resultado;
        } catch (Exception $e) {
            error_log("Error en getEstadisticasResultado: " . $e->getMessage());
            return $resultado;
        }
    }
}
?>