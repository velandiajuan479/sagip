<?php
class Esmmin {
    private $idusu;
    private $nummin;
    private $fechos;
    private $tipmin;
    private $hij;
    private $fhlle;
    private $obs;
    private $ideles;

    function getIdusu() { return $this->idusu; }
    function getNummin() { return $this->nummin; }
    function getFechos() { return $this->fechos; }
    function getTipmin() { return $this->tipmin; }
    function getHij() { return $this->hij; }
    function getObs() { return $this->obs; }
    function getFhlle() { return $this->fhlle; }
    function getIdeles() { return $this->ideles; }

    function setIdusu($idusu) { $this->idusu = $idusu; }
    function setNummin($nummin) { $this->nummin = $nummin; }
    function setFechos($fechos) { $this->fechos = $fechos; }
    function setTipmin($tipmin) { $this->tipmin = $tipmin; }
    function setHij($hij) { $this->hij = $hij; }
    function setFhlle($fhlle) { $this->fhlle = $fhlle; }
    function setObs($obs) { $this->obs = $obs; }
    function setIdeles($ideles) { $this->ideles = $ideles; }

    public function getTodo($datemin, $datemax) {
        $res = NULL;
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $idusu = $this->getIdusu();
        $sql = "SELECT m.nummin, COALESCE(u.nomusu, 'Invitado') AS nomusu, COALESCE(u.ndocusu, m.idusu) AS ndocusu, COALESCE(p.nomper, 'Sin Perfil') AS nomper, uf.idfic, f.nomfic, v.nomval, m.fechos, m.fhlle, m.obs, m.tipmin, m.hij, m.ideles, TIMEDIFF(m.fhlle, m.fechos) as tiempo FROM minuta AS m LEFT JOIN usuario AS u ON m.idusu=u.idusu LEFT JOIN perfil AS p ON u.idper=p.idper LEFT JOIN usufic AS uf ON u.idusu=uf.idusu LEFT JOIN ficha AS f ON uf.idfic=f.idfic LEFT JOIN valor AS v ON f.jornada=v.idval WHERE DATE(m.fechos)>=:datemin";
        if($datemax) $sql .= " AND DATE(m.fechos)<=:datemax AND tipmin IN ('F','S','I')";
        if($idusu) $sql .= " AND u.ndocusu LIKE CONCAT('%', :idusu, '%')";
        $sql .= " ORDER BY m.fechos DESC;";
        $result = $conexion->prepare($sql);
        $result->bindParam(':datemin',$datemin);
        if($datemax) $result->bindParam(':datemax',$datemax);
        if($idusu) $result->bindParam(":idusu",$idusu);
        $result->execute();
        $res = $result->fetchAll(PDO::FETCH_ASSOC);
        return $res;
    }

    public function getTodoP() {
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $sql = "SELECT m.nummin, m.idusu, m.fechos, m.tipmin, m.hij, m.fhlle, m.obs, m.ideles, u.nomusu, u.ndocusu, u.idper, u.telcan, p.nomper, e.nomele, f.idfic, f.nomfic, v.nomval FROM minuta AS m INNER JOIN usuario AS u ON u.idusu=m.idusu INNER JOIN perfil AS p ON p.idper=u.idper INNER JOIN elemento AS e ON e.idele=m.ideles LEFT JOIN usufic AS uf ON uf.idusu=u.idusu LEFT JOIN ficha AS f ON f.idfic=uf.idfic LEFT JOIN valor AS v ON v.idval=f.jornada WHERE tipmin IN ('A')";
        $result = $conexion->prepare($sql);
        $result->execute();
        $res = $result->fetchAll(PDO::FETCH_ASSOC);
        return $res;
    }

    public function getTodoA() {
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $sql = "SELECT m.nummin, m.idusu, m.fechos, m.tipmin, m.hij, m.fhlle, m.obs, m.ideles, u.nomusu, u.ndocusu, u.idper, u.telcan, p.nomper, v.nomval, v.parval FROM minuta AS m INNER JOIN usuario AS u ON u.idusu=m.idusu INNER JOIN perfil AS p ON p.idper=u.idper INNER JOIN valor AS v ON v.idval=m.ideles WHERE tipmin IN ('N')";
        $result = $conexion->prepare($sql);
        $result->execute();
        $res = $result->fetchAll(PDO::FETCH_ASSOC);
        return $res;
    }

    public function selOneEle($idele) {
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $sql = "SELECT e.idele, e.idusu, e.nomele, e.nidele, e.marele, e.tipele, v.nomval, e.noplasena, e.desele, e.preele, f.rutfot FROM elemento AS e INNER JOIN valor AS v ON e.tipele=v.idval LEFT JOIN foto AS f ON e.idele=f.idele WHERE e.idele=:idele";
        $result = $conexion->prepare($sql);
        $result->bindParam(":idele", $idele);
        $result->execute();
        $res = $result->fetchAll(PDO::FETCH_ASSOC);
        return $res;
    }

    public function getTopIngresosMes($mes, $anio, $limit = 5) {
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $limit = (int)$limit;
        $sql = "SELECT
            COALESCE(u.ndocusu, m.idusu) AS ndocusu,
            COALESCE(u.nomusu, 'Invitado') AS nomusu,
            COALESCE(p.nomper, 'Sin Perfil') AS nomper,
            COUNT(m.nummin) AS total_ingresos
        FROM minuta AS m
        LEFT JOIN usuario AS u ON m.idusu = u.idusu
        LEFT JOIN perfil AS p ON u.idper = p.idper
        WHERE MONTH(m.fechos) = :mes AND YEAR(m.fechos) = :anio
        GROUP BY COALESCE(u.ndocusu, m.idusu), u.nomusu, p.nomper
        ORDER BY total_ingresos DESC
        LIMIT $limit;";
        $result = $conexion->prepare($sql);
        $result->bindParam(':mes', $mes, PDO::PARAM_INT);
        $result->bindParam(':anio', $anio, PDO::PARAM_INT);
        $result->execute();
        return $result->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTopPermanenciaMes($mes, $anio, $order = 'DESC', $limit = 5) {
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $orderSql = (strtoupper($order) === 'ASC') ? 'ASC' : 'DESC';
        $limit = (int)$limit;
        $sql = "SELECT
            COALESCE(u.ndocusu, m.idusu) AS ndocusu,
            COALESCE(u.nomusu, 'Invitado') AS nomusu,
            COALESCE(p.nomper, 'Sin Perfil') AS nomper,
            SUM(CASE WHEN m.fhlle IS NOT NULL AND m.fhlle > m.fechos THEN TIMESTAMPDIFF(SECOND, m.fechos, m.fhlle) ELSE 0 END) AS total_segundos,
            COUNT(m.nummin) AS total_ingresos
        FROM minuta AS m
        LEFT JOIN usuario AS u ON m.idusu = u.idusu
        LEFT JOIN perfil AS p ON u.idper = p.idper
        WHERE MONTH(m.fechos) = :mes AND YEAR(m.fechos) = :anio
        GROUP BY COALESCE(u.ndocusu, m.idusu), u.nomusu, p.nomper
        HAVING total_segundos > 0
        ORDER BY total_segundos $orderSql, total_ingresos $orderSql
        LIMIT $limit;";
        $result = $conexion->prepare($sql);
        $result->bindParam(':mes', $mes, PDO::PARAM_INT);
        $result->bindParam(':anio', $anio, PDO::PARAM_INT);
        $result->execute();
        return $result->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getMetricasDashboard($fechos, $fhlle, $idJornada = null) {
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();

        // Determinar la jornada automática según la hora actual
        $horaActual = (int)date("H");
        if ($horaActual >= 6 && $horaActual < 12) {
            $idJornadaAuto = 1;
            $nomJornadaAuto = 'Mañana';
        } elseif ($horaActual >= 12 && $horaActual < 18) {
            $idJornadaAuto = 2;
            $nomJornadaAuto = 'Tarde';
        } else {
            $idJornadaAuto = 3;
            $nomJornadaAuto = 'Noche';
        }

        if ($idJornada === null || $idJornada === '' || $idJornada === 0) {
            $idJornada = $idJornadaAuto;
            $nomJornadaActual = $nomJornadaAuto;
        } else {
            $idJornada = (int)$idJornada;
            $jornadasMap = [1 => 'Mañana', 2 => 'Tarde', 3 => 'Noche', 4 => 'Fin de Semana', 5 => 'Virtual'];
            $nomJornadaActual = $jornadasMap[$idJornada] ?? $nomJornadaAuto;
        }

        if ($idJornada == 1) { 
            $horaInicio = 6; 
            $horaFin = 11; 
        } elseif ($idJornada == 2) { 
            $horaInicio = 12; 
            $horaFin = 17; 
        } elseif ($idJornada == 3) { 
            $horaInicio = 18; 
            $horaFin = 23; 
        } else { 
            $horaInicio = 0; 
            $horaFin = 23; 
        }

        // 1. Aprendices en la jornada:
        // Registrados con ingreso en el rango de fechas para esa jornada o rango horario (solo aprendices)
        $sqlRegAprendices = "SELECT COUNT(DISTINCT m.idusu) AS reg 
                             FROM minuta AS m 
                             INNER JOIN usuario AS us ON m.idusu = us.idusu 
                             INNER JOIN perfil AS pp ON us.idper = pp.idper AND pp.nomper LIKE '%Aprendiz%'
                             LEFT JOIN usufic AS uf ON m.idusu = uf.idusu AND uf.actfic = 1
                             LEFT JOIN ficha AS f ON uf.idfic = f.idfic 
                             LEFT JOIN valor AS v ON f.jornada = v.idval 
                             WHERE DATE(m.fechos) >= :fechos AND DATE(m.fechos) <= :fhlle 
                               AND (f.jornada = :idJornada OR v.nomval LIKE CONCAT('%', :nomJornada, '%') OR HOUR(m.fechos) BETWEEN :horaInicio AND :horaFin)";
        $st = $conexion->prepare($sqlRegAprendices);
        $st->bindParam(':fechos', $fechos);
        $st->bindParam(':fhlle', $fhlle);
        $st->bindParam(':idJornada', $idJornada);
        $st->bindParam(':nomJornada', $nomJornadaActual);
        $st->bindParam(':horaInicio', $horaInicio);
        $st->bindParam(':horaFin', $horaFin);
        $st->execute();
        $row = $st->fetch(PDO::FETCH_ASSOC);
        $regAprendices = (int)($row['reg'] ?? 0);

        // Total Aprendices de la Jornada según las fichas activas (solo perfiles Aprendiz)
        $sqlTotAprendices = "SELECT COUNT(DISTINCT uf.idusu) AS tot 
                             FROM usufic AS uf 
                             INNER JOIN ficha AS f ON uf.idfic = f.idfic 
                             LEFT JOIN valor AS v ON f.jornada = v.idval 
                             INNER JOIN usuario AS us ON uf.idusu = us.idusu AND us.actusu = 1
                             INNER JOIN perfil AS pp ON us.idper = pp.idper AND pp.nomper LIKE '%Aprendiz%'
                             WHERE uf.actfic = 1 AND (f.jornada = :idJornada OR v.nomval LIKE CONCAT('%', :nomJornada, '%'))";
        $st = $conexion->prepare($sqlTotAprendices);
        $st->bindParam(':idJornada', $idJornada);
        $st->bindParam(':nomJornada', $nomJornadaActual);
        $st->execute();
        $row = $st->fetch(PDO::FETCH_ASSOC);
        $totAprendices = (int)($row['tot'] ?? 0);

        // Intento 2: aprendices de la jornada sin restringir al estado activo de ficha
        if ($totAprendices === 0) {
            $sqlTotAprendices2 = "SELECT COUNT(DISTINCT uf.idusu) AS tot 
                                  FROM usufic AS uf 
                                  INNER JOIN ficha AS f ON uf.idfic = f.idfic 
                                  LEFT JOIN valor AS v ON f.jornada = v.idval 
                                  INNER JOIN usuario AS us ON uf.idusu = us.idusu AND us.actusu = 1
                                  INNER JOIN perfil AS pp ON us.idper = pp.idper AND pp.nomper LIKE '%Aprendiz%'
                                  WHERE (f.jornada = :idJornada OR v.nomval LIKE CONCAT('%', :nomJornada, '%'))";
            $st2 = $conexion->prepare($sqlTotAprendices2);
            $st2->bindParam(':idJornada', $idJornada);
            $st2->bindParam(':nomJornada', $nomJornadaActual);
            $st2->execute();
            $row2 = $st2->fetch(PDO::FETCH_ASSOC);
            $totAprendices = (int)($row2['tot'] ?? 0);
        }

        // Intento 3: total general de aprendices activos del centro como fallback
        if ($totAprendices === 0) {
            $sqlTotAprendicesAlt = "SELECT COUNT(DISTINCT u.idusu) AS tot 
                                    FROM usuario AS u 
                                    INNER JOIN perfil AS p ON u.idper = p.idper 
                                    WHERE u.actusu = 1 AND p.nomper LIKE '%Aprendiz%'";
            $st = $conexion->prepare($sqlTotAprendicesAlt);
            $st->execute();
            $rowAlt = $st->fetch(PDO::FETCH_ASSOC);
            $totAprendices = (int)($rowAlt['tot'] ?? 0);
        }

        // 2. Fichas por Jornada:
        // Fichas con movimiento registrado en el período para esa jornada
        $sqlRegFichas = "SELECT COUNT(DISTINCT f.idfic) AS reg 
                         FROM minuta AS m 
                         INNER JOIN usufic AS uf ON m.idusu = uf.idusu 
                         INNER JOIN ficha AS f ON uf.idfic = f.idfic 
                         LEFT JOIN valor AS v ON f.jornada = v.idval 
                         WHERE DATE(m.fechos) >= :fechos AND DATE(m.fechos) <= :fhlle 
                           AND (f.jornada = :idJornada OR v.nomval LIKE CONCAT('%', :nomJornada, '%') OR HOUR(m.fechos) BETWEEN :horaInicio AND :horaFin)";
        $st = $conexion->prepare($sqlRegFichas);
        $st->bindParam(':fechos', $fechos);
        $st->bindParam(':fhlle', $fhlle);
        $st->bindParam(':idJornada', $idJornada);
        $st->bindParam(':nomJornada', $nomJornadaActual);
        $st->bindParam(':horaInicio', $horaInicio);
        $st->bindParam(':horaFin', $horaFin);
        $st->execute();
        $row = $st->fetch(PDO::FETCH_ASSOC);
        $regFichas = (int)($row['reg'] ?? 0);

        // Total Fichas de la Jornada
        $sqlTotFichas = "SELECT COUNT(*) AS tot 
                         FROM ficha AS f 
                         LEFT JOIN valor AS v ON f.jornada = v.idval 
                         WHERE (f.jornada = :idJornada OR v.nomval LIKE CONCAT('%', :nomJornada, '%'))";
        $st = $conexion->prepare($sqlTotFichas);
        $st->bindParam(':idJornada', $idJornada);
        $st->bindParam(':nomJornada', $nomJornadaActual);
        $st->execute();
        $row = $st->fetch(PDO::FETCH_ASSOC);
        $totFichas = (int)($row['tot'] ?? 0);

        if ($totFichas === 0) {
            $sqlFallbackTotFic = "SELECT COUNT(*) AS tot FROM ficha";
            $st = $conexion->prepare($sqlFallbackTotFic);
            $st->execute();
            $rowFallback = $st->fetch(PDO::FETCH_ASSOC);
            $totFichas = (int)($rowFallback['tot'] ?? 0);
        }

        // 3. Entradas (según la jornada)
        $sqlRegEntradas = "SELECT COUNT(*) AS reg 
                           FROM minuta AS m 
                           LEFT JOIN usufic AS uf ON m.idusu = uf.idusu AND uf.actfic = 1
                           LEFT JOIN ficha AS f ON uf.idfic = f.idfic 
                           LEFT JOIN valor AS v ON f.jornada = v.idval 
                           WHERE DATE(m.fechos) >= :fechos AND DATE(m.fechos) <= :fhlle 
                             AND m.tipmin = 'I'
                             AND (f.jornada = :idJornada OR v.nomval LIKE CONCAT('%', :nomJornada, '%') OR HOUR(m.fechos) BETWEEN :horaInicio AND :horaFin)";
        $st = $conexion->prepare($sqlRegEntradas);
        $st->bindParam(':fechos', $fechos);
        $st->bindParam(':fhlle', $fhlle);
        $st->bindParam(':idJornada', $idJornada);
        $st->bindParam(':nomJornada', $nomJornadaActual);
        $st->bindParam(':horaInicio', $horaInicio);
        $st->bindParam(':horaFin', $horaFin);
        $st->execute();
        $row = $st->fetch(PDO::FETCH_ASSOC);
        $regEntradas = (int)($row['reg'] ?? 0);

        // 4. Salidas (según la jornada)
        $sqlRegSalidas = "SELECT COUNT(*) AS reg 
                          FROM minuta AS m 
                          LEFT JOIN usufic AS uf ON m.idusu = uf.idusu AND uf.actfic = 1
                          LEFT JOIN ficha AS f ON uf.idfic = f.idfic 
                          LEFT JOIN valor AS v ON f.jornada = v.idval 
                          WHERE DATE(m.fechos) >= :fechos AND DATE(m.fechos) <= :fhlle 
                            AND (m.fhlle IS NOT NULL OR m.tipmin IN ('F', 'S'))
                            AND (f.jornada = :idJornada OR v.nomval LIKE CONCAT('%', :nomJornada, '%') OR HOUR(COALESCE(m.fhlle, m.fechos)) BETWEEN :horaInicio AND :horaFin)";
        $st = $conexion->prepare($sqlRegSalidas);
        $st->bindParam(':fechos', $fechos);
        $st->bindParam(':fhlle', $fhlle);
        $st->bindParam(':idJornada', $idJornada);
        $st->bindParam(':nomJornada', $nomJornadaActual);
        $st->bindParam(':horaInicio', $horaInicio);
        $st->bindParam(':horaFin', $horaFin);
        $st->execute();
        $row = $st->fetch(PDO::FETCH_ASSOC);
        $regSalidas = (int)($row['reg'] ?? 0);

        // 5. Elementos (traídos según la jornada)
        $sqlRegElementos = "SELECT COUNT(DISTINCT TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(m.ideles, ';', numbers.n), ';', -1))) AS reg
                            FROM minuta m
                            JOIN (
                                SELECT 1 n UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5
                            ) numbers ON CHAR_LENGTH(m.ideles) - CHAR_LENGTH(REPLACE(m.ideles, ';', '')) >= numbers.n - 1
                            LEFT JOIN usufic AS uf ON m.idusu = uf.idusu AND uf.actfic = 1
                            LEFT JOIN ficha AS f ON uf.idfic = f.idfic 
                            LEFT JOIN valor AS v ON f.jornada = v.idval 
                            WHERE DATE(m.fechos) >= :fechos AND DATE(m.fechos) <= :fhlle 
                              AND m.ideles IS NOT NULL AND m.ideles != ''
                              AND (f.jornada = :idJornada OR v.nomval LIKE CONCAT('%', :nomJornada, '%') OR HOUR(m.fechos) BETWEEN :horaInicio AND :horaFin)";
        $st = $conexion->prepare($sqlRegElementos);
        $st->bindParam(':fechos', $fechos);
        $st->bindParam(':fhlle', $fhlle);
        $st->bindParam(':idJornada', $idJornada);
        $st->bindParam(':nomJornada', $nomJornadaActual);
        $st->bindParam(':horaInicio', $horaInicio);
        $st->bindParam(':horaFin', $horaFin);
        $st->execute();
        $row = $st->fetch(PDO::FETCH_ASSOC);
        $regElementos = (int)($row['reg'] ?? 0);

        $sqlTotElementos = "SELECT COUNT(*) AS tot FROM elemento";
        $st = $conexion->prepare($sqlTotElementos);
        $st->execute();
        $row = $st->fetch(PDO::FETCH_ASSOC);
        $totElementos = (int)($row['tot'] ?? 0);

        return [
            'regAprendices'    => $regAprendices, 
            'totAprendices'    => $totAprendices,
            'regFichas'        => $regFichas,     
            'totFichas'        => $totFichas,
            'regEntradas'      => $regEntradas,
            'regSalidas'       => $regSalidas,
            'regElementos'     => $regElementos,  
            'totElementos'     => $totElementos,
            'nomJornadaActual' => $nomJornadaActual,
            'idJornadaActual'  => $idJornada,
            'nomJornadaAuto'   => $nomJornadaAuto,
            'idJornadaAuto'    => $idJornadaAuto
        ];
    }

    // Total de todos los estudiantes registrados en el centro (solo perfiles Aprendiz)
    public function getTotAprendices() {
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $sql = "SELECT COUNT(DISTINCT u.idusu) AS tot
                FROM usuario AS u
                INNER JOIN perfil AS p ON u.idper = p.idper
                WHERE u.actusu = 1 AND p.nomper LIKE '%Aprendiz%'";
        $st = $conexion->prepare($sql);
        $st->execute();
        return (int)($st->fetch(PDO::FETCH_ASSOC)['tot'] ?? 0);
    }

    // Días hábiles (lunes a viernes) con actividad de aprendices en la minuta durante el mes
    public function getDiasActivosMes($mes, $anio) {
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $sql = "SELECT COUNT(DISTINCT DATE(m.fechos)) AS dias
                FROM minuta AS m
                INNER JOIN usuario AS u ON m.idusu = u.idusu
                INNER JOIN perfil AS p ON u.idper = p.idper
                WHERE p.nomper LIKE '%Aprendiz%' AND u.actusu = 1
                  AND MONTH(m.fechos) = :mes AND YEAR(m.fechos) = :anio AND WEEKDAY(m.fechos) < 5";
        $st = $conexion->prepare($sql);
        $st->bindParam(':mes', $mes, PDO::PARAM_INT);
        $st->bindParam(':anio', $anio, PDO::PARAM_INT);
        $st->execute();
        $dias = (int)($st->fetch(PDO::FETCH_ASSOC)['dias'] ?? 0);
        if ($dias === 0) {
            $sqlAlt = "SELECT COUNT(DISTINCT DATE(m.fechos)) AS dias
                       FROM minuta AS m
                       WHERE MONTH(m.fechos) = :mes AND YEAR(m.fechos) = :anio AND WEEKDAY(m.fechos) < 5";
            $stAlt = $conexion->prepare($sqlAlt);
            $stAlt->bindParam(':mes', $mes, PDO::PARAM_INT);
            $stAlt->bindParam(':anio', $anio, PDO::PARAM_INT);
            $stAlt->execute();
            $dias = (int)($stAlt->fetch(PDO::FETCH_ASSOC)['dias'] ?? 0);
        }
        return $dias;
    }

    // Asistencia diaria de aprendices (días hábiles) durante el mes
    public function getAsistenciaDiariaMes($mes, $anio) {
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $sql = "SELECT DATE(m.fechos) AS dia, COUNT(DISTINCT m.idusu) AS asistentes
                FROM minuta AS m
                INNER JOIN usuario AS u ON m.idusu = u.idusu
                INNER JOIN perfil AS p ON u.idper = p.idper
                WHERE p.nomper LIKE '%Aprendiz%' AND u.actusu = 1
                  AND MONTH(m.fechos) = :mes AND YEAR(m.fechos) = :anio AND WEEKDAY(m.fechos) < 5
                GROUP BY DATE(m.fechos)
                ORDER BY DATE(m.fechos) ASC";
        $st = $conexion->prepare($sql);
        $st->bindParam(':mes', $mes, PDO::PARAM_INT);
        $st->bindParam(':anio', $anio, PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    // Días asistidos por cada aprendiz durante el mes
    public function getAsistenciaPorAprendizMes($mes, $anio) {
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $sql = "SELECT m.idusu, COUNT(DISTINCT DATE(m.fechos)) AS atendidos
                FROM minuta AS m
                INNER JOIN usuario AS u ON m.idusu = u.idusu
                INNER JOIN perfil AS p ON u.idper = p.idper
                WHERE p.nomper LIKE '%Aprendiz%' AND u.actusu = 1
                  AND MONTH(m.fechos) = :mes AND YEAR(m.fechos) = :anio AND WEEKDAY(m.fechos) < 5
                GROUP BY m.idusu";
        $st = $conexion->prepare($sql);
        $st->bindParam(':mes', $mes, PDO::PARAM_INT);
        $st->bindParam(':anio', $anio, PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    // Listado de todos los estudiantes registrados en el centro (solo perfiles Aprendiz)
    public function getAprendices() {
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $sql = "SELECT DISTINCT u.idusu, u.ndocusu, u.nomusu,
                       (SELECT MIN(uf2.idfic) FROM usufic AS uf2 WHERE uf2.idusu = u.idusu AND uf2.actfic = 1) AS idfic,
                       (SELECT MIN(f2.nomfic) FROM usufic AS uf2 INNER JOIN ficha AS f2 ON uf2.idfic = f2.idfic WHERE uf2.idusu = u.idusu AND uf2.actfic = 1) AS nomfic,
                       (SELECT MIN(v2.nomval) FROM usufic AS uf2 INNER JOIN ficha AS f2 ON uf2.idfic = f2.idfic INNER JOIN valor AS v2 ON f2.jornada = v2.idval WHERE uf2.idusu = u.idusu AND uf2.actfic = 1) AS nomjornada
                FROM usuario AS u
                INNER JOIN perfil AS p ON u.idper = p.idper
                WHERE u.actusu = 1 AND p.nomper LIKE '%Aprendiz%'
                ORDER BY u.nomusu ASC";
        $st = $conexion->prepare($sql);
        $st->execute();
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>