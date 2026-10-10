<?php
require_once __DIR__ . "/conexion.php";

class Mpse {
    private $idact;
    private $idses;
    private $nomact;
    private $desact;
    private $duract;
    private $tipact;
    private $ordact;
    private $fecses;
    private $idfic;
    private $idres;
    private $idcom;
    private $idplan;
    private $plsOk = null;

    // Getters
    public function getIdact() { return $this->idact; }
    public function getIdses() { return $this->idses; }
    public function getNomact() { return $this->nomact; }
    public function getDesact() { return $this->desact; }
    public function getDuract() { return $this->duract; }
    public function getTipact() { return $this->tipact; }
    public function getOrdact() { return $this->ordact; }
    public function getFecses() { return $this->fecses; }
    public function getIdfic() { return $this->idfic; }
    public function getIdres() { return $this->idres; }
    public function getIdcom() { return $this->idcom; }
    public function getIdplan() { return $this->idplan; }

    // Setters
    public function setIdact($idact) { $this->idact = $idact; }
    public function setIdses($idses) { $this->idses = $idses; }
    public function setNomact($nomact) { $this->nomact = $nomact; }
    public function setDesact($desact) { $this->desact = $desact; }
    public function setDuract($duract) { $this->duract = $duract; }
    public function setTipact($tipact) { $this->tipact = $tipact; }
    public function setOrdact($ordact) { $this->ordact = $ordact; }
    public function setFecses($fecses) { $this->fecses = $fecses; }
    public function setIdfic($idfic) { $this->idfic = $idfic; }
    public function setIdres($idres) { $this->idres = $idres; }
    public function setIdcom($idcom) { $this->idcom = $idcom; }
    public function setIdplan($idplan) { $this->idplan = $idplan; }

    /**
     * Obtiene todas las actividades/planes de sesión basados en la cadena relacional del sistema:
     * agenda -> sesion -> actividad -> ficha/resultado/competencia/usuario/valor
     */
    public function getAll($idfic = null) {
        $res = [];
        try {
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $pls = $this->plansesDisponible();
            $plsCols = $pls
                ? "COALESCE(NULLIF(p.fas, ''), NULLIF(ag.fas, '')) AS fas,
                   p.titulo_sesion, p.cont, p.matfor, p.estado, p.fecha_programada, p.numses, p.tmp,"
                : "COALESCE(NULLIF(ag.fas, ''), '') AS fas,
                   NULL AS titulo_sesion, NULL AS cont, NULL AS matfor, NULL AS estado, NULL AS fecha_programada, NULL AS numses, NULL AS tmp,";
            $plsJoin = $pls ? "LEFT JOIN planses AS p ON p.idfic = ag.idfic AND p.idres = ag.idres AND p.numses = s.idses" : "";
            $sql = "SELECT 
                        COALESCE(a.idact, 0) AS idact,
                        COALESCE(a.nomact, ag.actpro, 'Actividad de Sesión') AS nomact,
                        COALESCE(a.desact, r.nomres, 'Sin descripción') AS desact,
                        COALESCE(a.duract, ag.horcom, 0) AS duract,
                        COALESCE(a.tipact, 1) AS tipact,
                        COALESCE(a.ordact, 1) AS ordact,
                        COALESCE(s.idses, 0) AS idses,
                        COALESCE(s.fecses, ag.fchinc, ag.fecini, CURDATE()) AS fecses,
                        ag.idage,
                        ag.actpro,
                        COALESCE(ag.fchinc, ag.fecini, '') AS fchinc,
                        COALESCE(ag.fchfnl, ag.fecfin, '') AS fchfnl,
                        COALESCE(f.idfic, ag.idfic, '') AS idfic,
                        COALESCE(f.nomfic, 'Programa de Formación') AS nomfic,
                        COALESCE(f.codpro, 0) AS codpro,
                        COALESCE(pr.nompro, '') AS nompro,
                        COALESCE(u.idusu, 0) AS idusu,
                        COALESCE(u.nomusu, 'Instructor Asignado') AS nomusu,
                        COALESCE(u.ndocusu, '') AS ndocusu,
                        COALESCE(c.idcom, ag.idcom, 0) AS idcom,
                        COALESCE(c.descom, 'Competencia de Formación') AS descom,
                        COALESCE(r.idres, ag.idres, 0) AS idres,
                        COALESCE(r.nomres, 'Resultado de Aprendizaje') AS nomres,
                        COALESCE(r.ndeses, 1) AS ndeses,
                        COALESCE(v.nomval, 'Presencial') AS nomval,
                        $plsCols
                        'Ambiente 1' AS idaul,
                        'Ambiente de Formación' AS nomaul,
                        'Desarrollo pedagógico según planeación' AS observaciones
                    FROM agenda AS ag
                    LEFT JOIN sesion AS s ON s.idage = ag.idage
                    LEFT JOIN actividad AS a ON a.idses = s.idses
                    LEFT JOIN ficha AS f ON ag.idfic = f.idfic
                    LEFT JOIN programa AS pr ON f.codpro = pr.codpro
                    LEFT JOIN resultado AS r ON ag.idres = r.idres
                    LEFT JOIN competencia AS c ON (r.idcom = c.idcom OR ag.idcom = c.idcom)
                    LEFT JOIN usuario AS u ON COALESCE(ag.idins, ag.idusu) = u.idusu
                    LEFT JOIN valor AS v ON a.tipact = v.idval
                    $plsJoin
                    WHERE ag.idfic IS NOT NULL AND TRIM(ag.idfic) != ''";

            if (!empty($idfic)) {
                $sql .= " AND ag.idfic = :idfic";
            }

            $sql .= " ORDER BY fecses DESC, ag.idage DESC, a.ordact ASC, a.idact ASC";

            $result = $conexion->prepare($sql);
            if (!empty($idfic)) {
                $result->bindValue(':idfic', $idfic);
            }
            $result->execute();
            $res = $result->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en Mpse::getAll(): " . $e->getMessage());
        }
        return $res;
    }

    /**
     * Obtiene un registro individual por ID de actividad
     */
    public function getOne() {
        $res = null;
        try {
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $pls = $this->plansesDisponible();
            $plsCols = $pls
                ? "COALESCE(NULLIF(p.fas, ''), NULLIF(ag.fas, '')) AS fas, p.titulo_sesion, p.cont, p.matfor, p.estado, p.fecha_programada, p.numses, p.tmp,"
                : "COALESCE(NULLIF(ag.fas, ''), '') AS fas, NULL AS titulo_sesion, NULL AS cont, NULL AS matfor, NULL AS estado, NULL AS fecha_programada, NULL AS numses, NULL AS tmp,";
            $plsJoin = $pls ? "LEFT JOIN planses AS p ON p.idfic = ag.idfic AND p.idres = ag.idres AND p.numses = s.idses" : "";
            $sql = "SELECT 
                        a.idact,
                        a.nomact,
                        a.desact,
                        a.duract,
                        a.tipact,
                        a.ordact,
                        s.idses,
                        s.fecses,
                        ag.idage,
                        ag.fchinc,
                        ag.fchfnl,
                        f.idfic,
                        f.nomfic,
                        f.codpro,
                        pr.nompro,
                        u.idusu,
                        u.nomusu,
                        u.ndocusu,
                        c.idcom,
                        c.descom,
                        r.idres,
                        r.nomres,
                        r.ndeses,
                        v.nomval,
                        $plsCols
                        ag.idusu AS idusu_agenda,
                        ag.idins AS idins_agenda
                    FROM actividad AS a
                    INNER JOIN sesion AS s ON a.idses = s.idses
                    INNER JOIN agenda AS ag ON s.idage = ag.idage
                    LEFT JOIN ficha AS f ON ag.idfic = f.idfic
                    LEFT JOIN programa AS pr ON f.codpro = pr.codpro
                    LEFT JOIN resultado AS r ON ag.idres = r.idres
                    LEFT JOIN competencia AS c ON r.idcom = c.idcom
                    LEFT JOIN usuario AS u ON COALESCE(ag.idins, ag.idusu) = u.idusu
                    LEFT JOIN valor AS v ON a.tipact = v.idval
                    $plsJoin
                    WHERE a.idact = :idact";
            $result = $conexion->prepare($sql);
            $result->bindValue(':idact', $this->idact, PDO::PARAM_INT);
            $result->execute();
            $res = $result->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en Mpse::getOne(): " . $e->getMessage());
        }
        return $res;
    }

    /**
     * Estadísticas completas para el Dashboard de Plan de Sesión
     */
    public function getEstadisticas() {
        $stats = [
            'total_actividades' => 0,
            'total_sesiones' => 0,
            'total_duracion_minutos' => 0,
            'total_duracion_horas' => 0,
            'total_fichas' => 0,
            'total_competencias' => 0,
            'total_resultados' => 0,
            'total_planes_tabla' => 0
        ];
        try {
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();

            // Total de actividades y duración
            $sql = "SELECT COUNT(*) as total_act, COALESCE(SUM(horcom), 0) as total_min FROM agenda WHERE idfic IS NOT NULL";
            $stmt = $conexion->prepare($sql);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                $stats['total_actividades'] = (int)$row['total_act'];
                $stats['total_duracion_minutos'] = (int)$row['total_min'];
                $stats['total_duracion_horas'] = round($row['total_min'] / 60, 1);
            }

            // Total de sesiones
            $sql = "SELECT COUNT(DISTINCT idses) as total_ses FROM sesion";
            $stmt = $conexion->prepare($sql);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $totalSes = (int)($row['total_ses'] ?? 0);
            $stats['total_sesiones'] = $totalSes > 0 ? $totalSes : $stats['total_actividades'];

            // Total de planes en tabla planses
            $sql = "SELECT COUNT(*) as total_p FROM planses";
            $stmt = $conexion->prepare($sql);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['total_planes_tabla'] = (int)($row['total_p'] ?? 0);

            // Fichas involucradas en agendas
            $sql = "SELECT COUNT(DISTINCT idfic) as total_f FROM agenda WHERE idfic IS NOT NULL";
            $stmt = $conexion->prepare($sql);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['total_fichas'] = (int)($row['total_f'] ?? 0);

            // Competencias
            $sql = "SELECT COUNT(DISTINCT idcom) as total_c FROM agenda WHERE idcom IS NOT NULL";
            $stmt = $conexion->prepare($sql);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['total_competencias'] = (int)($row['total_c'] ?? 0);

            // Resultados
            $sql = "SELECT COUNT(DISTINCT idres) as total_r FROM agenda WHERE idres IS NOT NULL";
            $stmt = $conexion->prepare($sql);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['total_resultados'] = (int)($row['total_r'] ?? 0);
        } catch (Exception $e) {
            error_log("Error en Mpse::getEstadisticas(): " . $e->getMessage());
        }
        return $stats;
    }

    /**
     * Resumen de actividades agrupadas por Ficha
     */
    public function getActividadesPorFicha() {
        $res = [];
        try {
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $sql = "SELECT 
                        f.idfic, 
                        f.nomfic, 
                        COUNT(a.idact) AS total_act,
                        COALESCE(SUM(a.duract), 0) AS total_min
                    FROM actividad AS a
                    INNER JOIN sesion AS s ON a.idses = s.idses
                    INNER JOIN agenda AS ag ON s.idage = ag.idage
                    INNER JOIN ficha AS f ON ag.idfic = f.idfic
                    GROUP BY f.idfic, f.nomfic
                    ORDER BY total_act DESC
                    LIMIT 5";
            $stmt = $conexion->prepare($sql);
            $stmt->execute();
            $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en Mpse::getActividadesPorFicha(): " . $e->getMessage());
        }
        return $res;
    }

    /**
     * Resumen de actividades por competencia
     */
    public function getActividadesPorCompetencia() {
        $res = [];
        try {
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $sql = "SELECT 
                        c.idcom,
                        c.descom,
                        COUNT(a.idact) AS total_act,
                        COALESCE(SUM(a.duract), 0) AS total_min
                    FROM actividad AS a
                    INNER JOIN sesion AS s ON a.idses = s.idses
                    INNER JOIN agenda AS ag ON s.idage = ag.idage
                    INNER JOIN resultado AS r ON ag.idres = r.idres
                    INNER JOIN competencia AS c ON r.idcom = c.idcom
                    GROUP BY c.idcom, c.descom
                    ORDER BY total_act DESC
                    LIMIT 5";
            $stmt = $conexion->prepare($sql);
            $stmt->execute();
            $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en Mpse::getActividadesPorCompetencia(): " . $e->getMessage());
        }
        return $res;
    }

    /**
     * Lista de fichas disponibles
     */
    public function getFichas() {
        $res = [];
        try {
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $sql = "SELECT idfic, nomfic, codpro FROM ficha ORDER BY idfic ASC";
            $stmt = $conexion->prepare($sql);
            $stmt->execute();
            $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en Mpse::getFichas(): " . $e->getMessage());
        }
        return $res;
    }

    /**
     * Obtiene los datos del Centro de Formación para el encabezado del reporte institucional
     */
    /**
     * Detalle de una ficha con su programa y jornada (uso exclusivo de la vista del módulo).
     * Método ADITIVO: no altera getFichas() ni ningún método existente.
     */
    public function getFichaDetalle($idfic) {
        $res = [];
        if (empty($idfic)) { return $res; }
        try {
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $sql = "SELECT f.idfic, f.nomfic, f.finific, f.ffinfic, f.codpro,
                           p.nompro, p.verpro, p.horlpro, p.horppro, v.nomval AS jornada
                    FROM ficha AS f
                    LEFT JOIN programa AS p ON f.codpro = p.codpro
                    LEFT JOIN valor AS v ON f.jornada = v.idval
                    WHERE f.idfic = :idfic
                    LIMIT 1";
            $stmt = $conexion->prepare($sql);
            $stmt->bindValue(':idfic', $idfic);
            $stmt->execute();
            $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en Mpse::getFichaDetalle(): " . $e->getMessage());
        }
        return $res;
    }

    public function getCen() {
        $res = [];
        try {
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $sql = "SELECT idcen, nomcen, dircen, telcen, imgcen FROM centro LIMIT 1";
            $stmt = $conexion->prepare($sql);
            $stmt->execute();
            $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en Mpse::getCen(): " . $e->getMessage());
        }
        return $res;
    }

    /**
     * Obtiene lista de instructores asignados a planes/agendas
     */
    public function getInstructores() {
        $res = [];
        try {
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $sql = "SELECT DISTINCT u.idusu, u.nomusu, u.ndocusu 
                    FROM usuario AS u 
                    INNER JOIN agenda AS ag ON (ag.idusu = u.idusu OR ag.idins = u.idusu) 
                    ORDER BY u.nomusu ASC";
            $stmt = $conexion->prepare($sql);
            $stmt->execute();
            $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en Mpse::getInstructores(): " . $e->getMessage());
        }
        return $res;
    }

    /**
     * Indica si la tabla planses existe y puede unirse al listado.
     * Los campos fase/estado/contenidos/materiales solo se muestran si hay soporte real.
     */
    public function plansesDisponible() {
        if ($this->plsOk !== null) { return $this->plsOk; }
        try {
            $db = (new conexion())->get_conexion();
            $db->query("SELECT 1 FROM planses LIMIT 1");
            $this->plsOk = true;
        } catch (Exception $e) {
            $this->plsOk = false;
        }
        return $this->plsOk;
    }

    /**
     * Listado paginado y filtrado del plan de sesión (cadena agenda -> sesion -> actividad).
     * Mantiene exactamente las mismas columnas que getAll() para no romper contratos.
     */
    public function getActividadesFiltradas($filtros = [], $page = 1, $limite = 10) {
        $res = ['data' => [], 'total' => 0, 'total_paginas' => 1, 'pagina' => max(1, (int)$page)];
        try {
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $pls = $this->plansesDisponible();
            $plsCols = $pls
                ? "COALESCE(NULLIF(p.fas, ''), NULLIF(ag.fas, '')) AS fas, p.titulo_sesion, p.cont, p.matfor, p.estado, p.fecha_programada, p.numses, p.tmp,"
                : "COALESCE(NULLIF(ag.fas, ''), '') AS fas, NULL AS titulo_sesion, NULL AS cont, NULL AS matfor, NULL AS estado, NULL AS fecha_programada, NULL AS numses, NULL AS tmp,";
            $plsJoin = $pls ? "LEFT JOIN planses AS p ON p.idfic = ag.idfic AND p.idres = ag.idres AND p.numses = s.idses" : "";

            $where = ["ag.idfic IS NOT NULL", "TRIM(ag.idfic) != ''"];
            $params = [];
            if (!empty($filtros['idfic'])) { $where[] = "ag.idfic = :idfic"; $params[':idfic'] = $filtros['idfic']; }
            if (!empty($filtros['idcom'])) { $where[] = "(ag.idcom = :idcom OR r.idcom = :idcom)"; $params[':idcom'] = (int)$filtros['idcom']; }
            if (!empty($filtros['idres'])) { $where[] = "(ag.idres = :idres OR r.idres = :idres)"; $params[':idres'] = (int)$filtros['idres']; }
            if (!empty($filtros['idusu'])) { $where[] = "(ag.idins = :idusu OR ag.idusu = :idusu)"; $params[':idusu'] = (int)$filtros['idusu']; }
            if (!empty($filtros['desde'])) { $where[] = "COALESCE(s.fecses, ag.fchinc, ag.fecini, CURDATE()) >= :desde"; $params[':desde'] = $filtros['desde']; }
            if (!empty($filtros['hasta'])) { $where[] = "COALESCE(s.fecses, ag.fchinc, ag.fecini, CURDATE()) <= :hasta"; $params[':hasta'] = $filtros['hasta']; }
            if (!empty($filtros['busqueda'])) {
                $b = '%' . trim($filtros['busqueda']) . '%';
                $where[] = "(a.nomact LIKE :b1 OR a.desact LIKE :b2 OR ag.actpro LIKE :b3 OR c.descom LIKE :b4 OR r.nomres LIKE :b5 OR u.nomusu LIKE :b6 OR CAST(u.ndocusu AS CHAR) LIKE :b7 OR ag.idfic LIKE :b8 OR f.nomfic LIKE :b9 OR pr.nompro LIKE :b10)";
                for ($i = 1; $i <= 10; $i++) { $params[':b' . $i] = $b; }
            }

            $whereSql = "WHERE " . implode(" AND ", $where);
            $fromSql = "FROM agenda AS ag
                    LEFT JOIN sesion AS s ON s.idage = ag.idage
                    LEFT JOIN actividad AS a ON a.idses = s.idses
                    LEFT JOIN ficha AS f ON ag.idfic = f.idfic
                    LEFT JOIN programa AS pr ON f.codpro = pr.codpro
                    LEFT JOIN resultado AS r ON ag.idres = r.idres
                    LEFT JOIN competencia AS c ON (r.idcom = c.idcom OR ag.idcom = c.idcom)
                    LEFT JOIN usuario AS u ON COALESCE(ag.idins, ag.idusu) = u.idusu
                    LEFT JOIN valor AS v ON a.tipact = v.idval
                    $plsJoin";

            $countSql = "SELECT COUNT(*) $fromSql $whereSql";
            $stmt = $conexion->prepare($countSql);
            $stmt->execute($params);
            $res['total'] = (int)$stmt->fetchColumn();
            $res['total_paginas'] = max(1, (int)ceil($res['total'] / $limite));
            $offset = max(0, ($res['pagina'] - 1) * $limite);

            $select = "SELECT
                        COALESCE(a.idact, 0) AS idact,
                        COALESCE(a.nomact, ag.actpro, 'Actividad de Sesión') AS nomact,
                        COALESCE(a.desact, r.nomres, 'Sin descripción') AS desact,
                        COALESCE(a.duract, ag.horcom, 0) AS duract,
                        COALESCE(a.tipact, 1) AS tipact,
                        COALESCE(a.ordact, 1) AS ordact,
                        COALESCE(s.idses, 0) AS idses,
                        COALESCE(s.fecses, ag.fchinc, ag.fecini, CURDATE()) AS fecses,
                        ag.idage,
                        ag.actpro,
                        COALESCE(ag.fchinc, ag.fecini, '') AS fchinc,
                        COALESCE(ag.fchfnl, ag.fecfin, '') AS fchfnl,
                        COALESCE(f.idfic, ag.idfic, '') AS idfic,
                        COALESCE(f.nomfic, 'Programa de Formación') AS nomfic,
                        COALESCE(f.codpro, 0) AS codpro,
                        COALESCE(pr.nompro, '') AS nompro,
                        COALESCE(u.idusu, 0) AS idusu,
                        COALESCE(u.nomusu, 'Instructor Asignado') AS nomusu,
                        COALESCE(u.ndocusu, '') AS ndocusu,
                        COALESCE(c.idcom, ag.idcom, 0) AS idcom,
                        COALESCE(c.descom, 'Competencia de Formación') AS descom,
                        COALESCE(r.idres, ag.idres, 0) AS idres,
                        COALESCE(r.nomres, 'Resultado de Aprendizaje') AS nomres,
                        COALESCE(r.ndeses, 1) AS ndeses,
                        COALESCE(v.nomval, 'Presencial') AS nomval,
                        $plsCols
                        'Ambiente 1' AS idaul,
                        'Ambiente de Formación' AS nomaul,
                        'Desarrollo pedagógico según planeación' AS observaciones
                    $fromSql
                    $whereSql
                    ORDER BY fecses DESC, ag.idage DESC, a.ordact ASC, a.idact ASC
                    LIMIT " . (int)$limite . " OFFSET " . (int)$offset;
            $stmt = $conexion->prepare($select);
            $stmt->execute($params);
            $res['data'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en Mpse::getActividadesFiltradas(): " . $e->getMessage());
        }
        return $res;
    }

    /**
     * Registro completo de una actividad por su idact (cadena agenda -> sesion -> actividad).
     */
    public function getActividad($idact) {
        $res = null;
        try {
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $pls = $this->plansesDisponible();
            $plsCols = $pls
                ? "COALESCE(NULLIF(p.fas, ''), NULLIF(ag.fas, '')) AS fas, p.titulo_sesion, p.cont, p.matfor, p.estado,"
                : "COALESCE(NULLIF(ag.fas, ''), '') AS fas, NULL AS titulo_sesion, NULL AS cont, NULL AS matfor, NULL AS estado,";
            $plsJoin = $pls ? "LEFT JOIN planses AS p ON p.idfic = ag.idfic AND p.idres = ag.idres AND p.numses = s.idses" : "";
            $sql = "SELECT a.idact, a.nomact, a.desact, a.duract, a.tipact, a.ordact, s.idses, s.fecses,
                           ag.idage, ag.actpro, ag.idcom, ag.idres, ag.idfic, f.nomfic, f.codpro, pr.nompro,
                           u.idusu, u.nomusu, u.ndocusu, c.descom, r.nomres, $plsCols v.nomval
                    FROM actividad AS a
                    INNER JOIN sesion AS s ON a.idses = s.idses
                    INNER JOIN agenda AS ag ON s.idage = ag.idage
                    LEFT JOIN ficha AS f ON ag.idfic = f.idfic
                    LEFT JOIN programa AS pr ON f.codpro = pr.codpro
                    LEFT JOIN resultado AS r ON ag.idres = r.idres
                    LEFT JOIN competencia AS c ON (r.idcom = c.idcom OR ag.idcom = c.idcom)
                    LEFT JOIN usuario AS u ON COALESCE(ag.idins, ag.idusu) = u.idusu
                    LEFT JOIN valor AS v ON a.tipact = v.idval
                    $plsJoin
                    WHERE a.idact = :idact";
            $result = $conexion->prepare($sql);
            $result->bindValue(':idact', (int)$idact, PDO::PARAM_INT);
            $result->execute();
            $res = $result->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en Mpse::getActividad(): " . $e->getMessage());
        }
        return $res;
    }

    /**
     * Sesiones (con su agenda y ficha) disponibles para la ficha indicada.
     */
    public function getSesionesPorFicha($idfic) {
        $res = [];
        try {
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $sql = "SELECT s.idses, s.idage, s.fecses, ag.idfic, ag.actpro, ag.fas, f.nomfic
                    FROM sesion AS s
                    INNER JOIN agenda AS ag ON s.idage = ag.idage
                    LEFT JOIN ficha AS f ON ag.idfic = f.idfic
                    WHERE ag.idfic = :idfic
                    ORDER BY s.fecses DESC, s.idses ASC";
            $result = $conexion->prepare($sql);
            $result->bindValue(':idfic', $idfic);
            $result->execute();
            $res = $result->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en Mpse::getSesionesPorFicha(): " . $e->getMessage());
        }
        return $res;
    }

    /**
     * Agendas de la ficha (para crear una nueva sesión de la agenda).
     */
    public function getAgendasPorFicha($idfic) {
        $res = [];
        try {
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $sql = "SELECT ag.idage, ag.idfic, ag.actpro, ag.fas, ag.fchinc, ag.fchfnl, f.nomfic
                    FROM agenda AS ag
                    LEFT JOIN ficha AS f ON ag.idfic = f.idfic
                    WHERE ag.idfic = :idfic
                    ORDER BY COALESCE(ag.fchinc, ag.fecini) DESC, ag.idage DESC";
            $result = $conexion->prepare($sql);
            $result->bindValue(':idfic', $idfic);
            $result->execute();
            $res = $result->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en Mpse::getAgendasPorFicha(): " . $e->getMessage());
        }
        return $res;
    }

    /**
     * Tipos de actividad existentes (referencia tipact -> valor) para el formulario.
     */
    public function getTiposActividad() {
        $res = [];
        try {
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $sql = "SELECT DISTINCT a.tipact, COALESCE(v.nomval, 'General') AS nomval
                    FROM actividad AS a
                    LEFT JOIN valor AS v ON a.tipact = v.idval
                    ORDER BY a.tipact ASC";
            $stmt = $conexion->prepare($sql);
            $stmt->execute();
            $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en Mpse::getTiposActividad(): " . $e->getMessage());
        }
        return $res;
    }

    /**
     * Competencias presentes en las agendas (filtro del listado).
     */
    public function getCompetenciasFiltro() {
        $res = [];
        try {
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $sql = "SELECT DISTINCT COALESCE(c.idcom, ag.idcom) AS idcom, COALESCE(c.descom, 'Sin competencia') AS descom
                    FROM agenda AS ag
                    LEFT JOIN resultado AS r ON ag.idres = r.idres
                    LEFT JOIN competencia AS c ON (r.idcom = c.idcom OR ag.idcom = c.idcom)
                    WHERE ag.idfic IS NOT NULL AND (c.idcom IS NOT NULL OR ag.idcom IS NOT NULL)
                    ORDER BY descom ASC";
            $stmt = $conexion->prepare($sql);
            $stmt->execute();
            $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en Mpse::getCompetenciasFiltro(): " . $e->getMessage());
        }
        return $res;
    }

    /**
     * Resultados de aprendizaje presentes en las agendas (filtro del listado).
     */
    public function getResultadosFiltro() {
        $res = [];
        try {
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $sql = "SELECT DISTINCT COALESCE(r.idres, ag.idres) AS idres, COALESCE(r.nomres, 'Sin resultado') AS nomres
                    FROM agenda AS ag
                    LEFT JOIN resultado AS r ON ag.idres = r.idres
                    WHERE ag.idfic IS NOT NULL AND (r.idres IS NOT NULL OR ag.idres IS NOT NULL)
                    ORDER BY nomres ASC";
            $stmt = $conexion->prepare($sql);
            $stmt->execute();
            $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en Mpse::getResultadosFiltro(): " . $e->getMessage());
        }
        return $res;
    }

    /**
     * Crea una actividad dentro de una sesión existente (tabla actividad, PK manual idact).
     */
    public function save() {
        try {
            $db = (new conexion())->get_conexion();
            $idact = $this->nextIdActividad();
            $stmt = $db->prepare("INSERT INTO actividad (idact, idses, nomact, desact, duract, tipact, ordact)
                                  VALUES (?, ?, ?, ?, ?, ?, ?)");
            return $stmt->execute([
                $idact,
                (int)$this->idses,
                trim((string)$this->nomact),
                trim((string)$this->desact),
                (int)$this->duract,
                (int)$this->tipact,
                (int)$this->ordact
            ]) ? $idact : false;
        } catch (Exception $e) {
            error_log("Error en Mpse::save(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualiza una actividad existente por idact.
     */
    public function edit() {
        try {
            $db = (new conexion())->get_conexion();
            $stmt = $db->prepare("UPDATE actividad SET
                    idses = ?, nomact = ?, desact = ?, duract = ?, tipact = ?, ordact = ?
                WHERE idact = ?");
            return $stmt->execute([
                (int)$this->idses,
                trim((string)$this->nomact),
                trim((string)$this->desact),
                (int)$this->duract,
                (int)$this->tipact,
                (int)$this->ordact,
                (int)$this->idact
            ]);
        } catch (Exception $e) {
            error_log("Error en Mpse::edit(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Elimina una actividad por idact (parámetro opcional; si no se pasa usa $this->idact).
     */
    public function del($idact = null) {
        try {
            $db = (new conexion())->get_conexion();
            $id = $idact !== null ? (int)$idact : (int)$this->idact;
            if ($id <= 0) { return false; }
            $stmt = $db->prepare("DELETE FROM actividad WHERE idact = ?");
            return $stmt->execute([$id]);
        } catch (Exception $e) {
            error_log("Error en Mpse::del(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Crea una sesión para una agenda existente. Devuelve el nuevo idses o false.
     */
    public function saveSesion($idage, $fecses) {
        try {
            $db = (new conexion())->get_conexion();
            $stmt = $db->prepare("INSERT INTO sesion (idage, fecses) VALUES (?, ?)");
            $ok = $stmt->execute([(int)$idage, $fecses]);
            return $ok ? (int)$db->lastInsertId() : false;
        } catch (Exception $e) {
            error_log("Error en Mpse::saveSesion(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Datos básicos de una sesión.
     */
    public function getSesion($idses) {
        try {
            $db = (new conexion())->get_conexion();
            $stmt = $db->prepare("SELECT idses, idage, fecses FROM sesion WHERE idses = ? LIMIT 1");
            $stmt->execute([(int)$idses]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (Exception $e) {
            error_log("Error en Mpse::getSesion(): " . $e->getMessage());
            return null;
        }
    }

    /**
     * Siguiente idact manual (la tabla actividad no usa auto_increment).
     */
    private function nextIdActividad() {
        try {
            $db = (new conexion())->get_conexion();
            $stmt = $db->query("SELECT COALESCE(MAX(idact), 0) + 1 FROM actividad");
            return (int)$stmt->fetchColumn();
        } catch (Exception $e) {
            return (int)time();
        }
    }

    /**
     * Obtiene las sesiones estructuradas con sus actividades para el Reporte Plan de Sesión
     * Agrupa datos por sesión/agenda y anida la secuencia didáctica
     */
    public function getReporteSesiones($idfic = null) {
        $raw = $this->getAll($idfic);
        $sesiones = [];

        foreach ($raw as $r) {
            $key = ($r['idage'] ?? 0) . '_' . ($r['idses'] ?? 0) . '_' . ($r['fecses'] ?? '');
            if (!isset($sesiones[$key])) {
                $sesiones[$key] = [
                    'idage' => $r['idage'] ?? 0,
                    'idses' => $r['idses'] ?? 0,
                    'fecses' => $r['fecses'] ?? date('Y-m-d'),
                    'ndeses' => !empty($r['ndeses']) ? $r['ndeses'] : 1,
                    'idfic' => $r['idfic'] ?? '',
                    'nomfic' => $r['nomfic'] ?? 'Programa de Formación',
                    'codpro' => $r['codpro'] ?? '',
                    'idusu' => $r['idusu'] ?? 0,
                    'nomusu' => $r['nomusu'] ?? 'Instructor Asignado',
                    'ndocusu' => $r['ndocusu'] ?? '',
                    'descom' => $r['descom'] ?? 'Competencia de Formación',
                    'nomres' => $r['nomres'] ?? 'Resultado de Aprendizaje',
                    'actpro' => !empty($r['actpro']) ? $r['actpro'] : 'Actividades de Proyecto de Formación',
                    'objetivo' => !empty($r['desact']) ? $r['desact'] : 'Desarrollo de competencias y resultados de aprendizaje',
                    'total_duracion' => 0,
                    'actividades' => []
                ];
            }

            $sesiones[$key]['total_duracion'] += (int)($r['duract'] ?? 0);
            $sesiones[$key]['actividades'][] = [
                'idact' => $r['idact'] ?? 0,
                'nomact' => $r['nomact'] ?? 'Actividad',
                'desact' => $r['desact'] ?? 'Sin descripción',
                'duract' => (int)($r['duract'] ?? 0),
                'tipact' => $r['tipact'] ?? 1,
                'nomval' => $r['nomval'] ?? 'General',
                'ordact' => $r['ordact'] ?? 1,
                'fecses' => $r['fecses'] ?? date('Y-m-d')
            ];
        }

        return array_values($sesiones);
    }
}
?>
