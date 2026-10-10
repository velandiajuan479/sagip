<?php
require_once(__DIR__ . '/conexion.php');

class Desmacfh {
    private $idactdc;
    private $idusu;
    private $idfic;
    private $numact;
    private $fecregdec;
    private $conact;
    private $coorusu;

    // Getters y Setters
    public function getIdactdc() { return $this->idactdc; }
    public function setIdactdc($idactdc) { $this->idactdc = $idactdc; }

    public function getIdusu() { return $this->idusu; }
    public function setIdusu($idusu) { $this->idusu = $idusu; }

    public function getIdfic() { return $this->idfic; }
    public function setIdfic($idfic) { $this->idfic = $idfic; }

    public function getNumact() { return $this->numact; }
    public function setNumact($numact) { $this->numact = $numact; }

    public function getFecregdec() { return $this->fecregdec; }
    public function setFecregdec($fecregdec) { $this->fecregdec = $fecregdec; }

    public function getConact() { return $this->conact; }
    public function setConact($conact) { $this->conact = $conact; }

    public function getCoorusu() { return $this->coorusu; }
    public function setCoorusu($coorusu) { $this->coorusu = $coorusu; }

    /**
     * Obtener información de una ficha específica
     */
    public function getFicha($idfic) {
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $sql = "SELECT f.idfic, f.nomfic, f.jornada, f.mun, v.nomval, f.idcen, c.nomcen, f.finific, f.ffinfic 
                FROM ficha AS f 
                LEFT JOIN valor AS v ON f.jornada = v.idval 
                LEFT JOIN centro AS c ON f.idcen = c.idcen 
                WHERE f.idfic = :idfic LIMIT 1";
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':idfic', $idfic);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener todas las fichas para selector rápido
     */
    public function getAllFichas() {
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $sql = "SELECT f.idfic, f.nomfic, f.finific, f.ffinfic, v.nomval 
                FROM ficha AS f 
                LEFT JOIN valor AS v ON f.jornada = v.idval 
                ORDER BY f.idfic DESC";
        $stmt = $conexion->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Calcular los trimestres y acta de cierre según las fechas de la ficha y la fecha actual
     */
    public function calcularActasPorFecha($feini, $fefin, $idfic = null) {
        $actas = [];
        if (empty($feini) || empty($fefin)) {
            return $actas;
        }

        $inicio = new DateTime(explode(" ", $feini)[0]);
        $fin = new DateTime(explode(" ", $fefin)[0]);
        $hoy = new DateTime();

        // Obtener actas ya creadas para esta ficha si se provee $idfic
        $actasRegistradas = [];
        if ($idfic) {
            $reg = $this->getAllActasFic($idfic);
            if ($reg) {
                foreach ($reg as $r) {
                    $actasRegistradas[(string)$r['numact']] = $r;
                }
            }
        }

        $actaNum = 1;
        $temp = clone $inicio;

        while ($temp < $fin) {
            $limite = (clone $temp)->add(new DateInterval('P3M'));
            $esCierre = false;

            if ($limite >= $fin) {
                $esCierre = true;
                $limite = clone $fin;
            }

            $numIdentificador = $esCierre ? 999 : $actaNum; // 999 representa acta de cierre final numéricamente
            $titulo = $esCierre ? "Acta de Cierre Final" : "Acta Trimestral #$actaNum";
            $rango = $temp->format("Y-m-d") . " a " . $limite->format("Y-m-d");

            $disponible = ($hoy >= $limite);
            $yaRegistrada = isset($actasRegistradas[(string)$numIdentificador]) || ($esCierre && isset($actasRegistradas['999']));
            $idactdc = $yaRegistrada ? ($actasRegistradas[(string)$numIdentificador]['idactdc'] ?? $actasRegistradas['999']['idactdc'] ?? null) : null;

            $actas[] = [
                'numact' => $numIdentificador,
                'titulo' => $titulo,
                'es_cierre' => $esCierre,
                'trimestre_num' => $actaNum,
                'fecha_desde' => $temp->format("Y-m-d"),
                'fecha_hasta' => $limite->format("Y-m-d"),
                'rango' => $rango,
                'disponible' => $disponible,
                'ya_registrada' => $yaRegistrada,
                'idactdc' => $idactdc,
                'fecha_disponible' => $limite->format("Y-m-d")
            ];

            if ($esCierre) {
                break;
            }

            $temp = $limite;
            $actaNum++;
        }

        return $actas;
    }

    /**
     * Insertar nueva Acta de Cierre / Seguimiento trimestral
     */
    public function insertarActaCierre($idusu, $idfic, $numact, $conact = '', $coorusu = null) {
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();

        // Verificar si ya existe
        $sqlCheck = "SELECT idactdc FROM acta_cierre WHERE idfic = :idfic AND numact = :numact LIMIT 1";
        $stmtCheck = $conexion->prepare($sqlCheck);
        $stmtCheck->execute([
            ':idfic' => $idfic,
            ':numact' => $numact
        ]);

        if ($stmtCheck->rowCount() > 0) {
            $row = $stmtCheck->fetch(PDO::FETCH_ASSOC);
            return $row['idactdc'];
        }

        // Insertar acta
        $sql = "INSERT INTO acta_cierre (idusu, idfic, numact, fecregdec) 
                VALUES (:idusu, :idfic, :numact, NOW())";
        $stmt = $conexion->prepare($sql);
        $res = $stmt->execute([
            ':idusu' => $idusu,
            ':idfic' => $idfic,
            ':numact' => $numact
        ]);

        if ($res) {
            $idactdc = $conexion->lastInsertId();

            // Si hay coordinador responsable o conclusiones, se crea el acta soporte
            // (tabla acta) y su detalle (actdec) para mantener la integridad referencial
            if ($coorusu || !empty($conact)) {
                $coor = $coorusu ? $coorusu : $idusu;
                $sqlActa = "INSERT INTO acta (nac, tipact, fecact, conact, idusu, coorusu) 
                            VALUES (:nac, 1, NOW(), :conact, :idusu, :coorusu)";
                $stmtActa = $conexion->prepare($sqlActa);
                $resActa = $stmtActa->execute([
                    ':nac' => $numact,
                    ':conact' => $conact,
                    ':idusu' => $idusu,
                    ':coorusu' => $coor
                ]);
                if ($resActa) {
                    $idact = $conexion->lastInsertId();
                    $sqlDec = "INSERT INTO actdec (idact, desacde, fecacde, idusu) 
                               VALUES (:idact, :desacde, CURDATE(), :idusu)";
                    $stmtDec = $conexion->prepare($sqlDec);
                    $stmtDec->execute([
                        ':idact' => $idact,
                        ':desacde' => $conact,
                        ':idusu' => $coor
                    ]);
                }
            }

            return $idactdc;
        }

        return false;
    }

    /**
     * Obtener todas las actas de una ficha
     */
    public function getAllActasFic($idfic) {
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $sql = "SELECT a.idactdc, a.idfic, a.numact, a.fecregdec, a.idusu, u.nomusu 
                FROM acta_cierre AS a 
                LEFT JOIN usuario AS u ON a.idusu = u.idusu 
                WHERE a.idfic = :idfic 
                ORDER BY a.numact ASC";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([':idfic' => $idfic]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener un acta específica
     */
    public function getOneActa($idactdc) {
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $sql = "SELECT a.*, u.nomusu 
                FROM acta_cierre AS a 
                LEFT JOIN usuario AS u ON a.idusu = u.idusu 
                WHERE a.idactdc = :idactdc LIMIT 1";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([':idactdc' => $idactdc]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Reporte Informativo de Asistencia de la Ficha (por mes/año)
     * Total de aprendices, novedades, asistentes regulares (conteo) y
     * críticos del mes (> $minhoras horas de inasistencia).
     */
    public function getReporteInformativo($idfic, $mes = null, $anio = null, $minhoras = 30) {
        if (!$mes || $mes < 1 || $mes > 12) { $mes = (int)date('n'); }
        if (!$anio || $anio < 2000) { $anio = (int)date('Y'); }

        $modelo = new conexion();
        $conexion = $modelo->get_conexion();

        // 1. Total de aprendices matriculados de la ficha
        $sqlTotal = "SELECT COUNT(DISTINCT u.idusu) AS total 
                     FROM usuario AS u 
                     INNER JOIN usufic AS uf ON u.idusu = uf.idusu 
                     WHERE uf.idfic = :idfic";
        $stmtTotal = $conexion->prepare($sqlTotal);
        $stmtTotal->execute([':idfic' => $idfic]);
        $totalAprendices = (int)($stmtTotal->fetchColumn() ?? 0);

        // 2. Aprendices con novedades (actusu != 1 o registros en desercion)
        $sqlNov = "SELECT u.idusu, u.ndocusu, u.nomusu, u.actusu,
                          CASE 
                              WHEN u.actusu = 0 THEN 'Inactivo'
                              WHEN u.actusu = 2 THEN 'Condicionado'
                              WHEN u.actusu = 3 THEN 'Deserción'
                              WHEN u.actusu = 4 THEN 'Cancelado'
                              WHEN u.actusu = 5 THEN 'Traslado'
                              WHEN u.actusu = 6 THEN 'Retiro Voluntario'
                              ELSE 'Novedad'
                          END AS tipo_novedad,
                          d.obsdec AS motivo_desercion,
                          d.fecregdec AS fecha_desercion
                   FROM usuario AS u
                   INNER JOIN usufic AS uf ON u.idusu = uf.idusu
                   LEFT JOIN desercion AS d ON (u.idusu = d.idusu AND d.idfic = :idfic)
                   WHERE uf.idfic = :idfic 
                     AND (u.actusu IN (0, 2, 3, 4, 5, 6) OR d.idusu IS NOT NULL)
                   GROUP BY u.idusu, u.ndocusu, u.nomusu, u.actusu, d.obsdec, d.fecregdec
                   ORDER BY u.nomusu ASC";
        $stmtNov = $conexion->prepare($sqlNov);
        $stmtNov->execute([':idfic' => $idfic]);
        $aprendicesNovedad = $stmtNov->fetchAll(PDO::FETCH_ASSOC);
        $conteoNovedades = count($aprendicesNovedad);

        // 3. Críticos del mes: aprendices activos sin deserción con más de $minhoras horas en el mes
        $sqlCrit = "SELECT u.idusu, u.ndocusu, u.nomusu, u.emausu, u.telcan, u.actusu,
                           COALESCE(SUM(i.horasina), 0) AS horasina,
                           (SELECT COUNT(*) FROM llamado AS l WHERE l.idusu = u.idusu AND l.tipllam = 1) AS llamados_verbales,
                           (SELECT COUNT(*) FROM llamado AS l WHERE l.idusu = u.idusu AND l.tipllam = 2) AS llamados_escritos
                    FROM usuario AS u
                    INNER JOIN usufic AS uf ON u.idusu = uf.idusu
                    LEFT JOIN inasistencia AS i ON (u.idusu = i.idusu AND i.idfic = :idfic AND MONTH(i.fecina) = :mes AND YEAR(i.fecina) = :anio)
                    WHERE uf.idfic = :idfic
                      AND u.actusu = 1
                      AND NOT EXISTS (SELECT 1 FROM desercion AS d WHERE d.idusu = u.idusu AND d.idfic = :idfic)
                    GROUP BY u.idusu, u.ndocusu, u.nomusu, u.emausu, u.telcan, u.actusu
                    HAVING horasina > :minhoras
                    ORDER BY horasina DESC, u.nomusu ASC";
        $stmtCrit = $conexion->prepare($sqlCrit);
        $stmtCrit->execute([
            ':idfic' => $idfic,
            ':mes' => $mes,
            ':anio' => $anio,
            ':minhoras' => $minhoras
        ]);
        $aprendicesCriticos = $stmtCrit->fetchAll(PDO::FETCH_ASSOC);

        // 4. Asistentes regulares (solo conteo): total menos novedades y menos críticos del mes
        $asistentesRegulares = max(0, $totalAprendices - $conteoNovedades - count($aprendicesCriticos));

        return [
            'total_aprendices' => $totalAprendices,
            'conteo_novedades' => $conteoNovedades,
            'aprendices_novedad' => $aprendicesNovedad,
            'asistentes_regulares' => $asistentesRegulares,
            'aprendices_criticos' => $aprendicesCriticos,
            'mes' => $mes,
            'anio' => $anio,
            'minhoras' => $minhoras
        ];
    }

    /**
     * Meses/años con registros de inasistencia de la ficha (para el selector del informe)
     */
    public function getMesesDisponibles($idfic) {
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $meses = [];

        if (!empty($idfic)) {
            $sql = "SELECT finific, ffinfic FROM ficha WHERE idfic = :idfic";
            $stmt = $conexion->prepare($sql);
            $stmt->execute([':idfic' => $idfic]);
            $ficha = $stmt->fetch(PDO::FETCH_ASSOC);

            $feIni = isset($ficha['finific']) ? explode(' ', (string)$ficha['finific'])[0] : '';
            $feFin = isset($ficha['ffinfic']) ? explode(' ', (string)$ficha['ffinfic'])[0] : '';

            if ($feIni && $feIni !== '0000-00-00' && (int)substr($feIni, 0, 4) >= 1900) {
                $inicio = new DateTime($feIni);
                $inicio->modify('first day of this month');

                $hoy = new DateTime();
                $hoy->modify('first day of this month');
                $fin = $hoy;

                if ($feFin && $feFin !== '0000-00-00') {
                    $finFicha = new DateTime($feFin);
                    $finFicha->modify('first day of this month');
                    if ($finFicha < $fin) {
                        $fin = $finFicha;
                    }
                }

                while ($inicio <= $fin) {
                    $meses[] = ['anio' => (int)$inicio->format('Y'), 'mes' => (int)$inicio->format('n')];
                    $inicio->modify('first day of next month');
                }
            }
        }

        // Garantizar que el mes/año del calendario actual siempre esté presente
        $mesActual = (int)date('n');
        $anioActual = (int)date('Y');
        $existe = false;
        foreach ($meses as $m) {
            if ((int)$m['mes'] === $mesActual && (int)$m['anio'] === $anioActual) {
                $existe = true;
                break;
            }
        }
        if (!$existe) {
            array_unshift($meses, ['anio' => $anioActual, 'mes' => $mesActual]);
        }

        // Más reciente primero (como el calendario)
        $meses = array_reverse($meses);
        return $meses;
    }

    /**
     * Obtener un llamado de atención específico con el aprendiz y el instructor
     */
    public function getOneLlamado($idllam) {
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $sql = "SELECT l.idllam, l.idusu, l.tipllam, l.obsllam, l.fecllam, l.firma, l.idinst,
                       u.nomusu AS aprendiz, u.ndocusu, u.emausu,
                       ins.nomusu AS instructor
                FROM llamado AS l
                LEFT JOIN usuario AS u ON l.idusu = u.idusu
                LEFT JOIN usuario AS ins ON l.idinst = ins.idusu
                WHERE l.idllam = :idllam LIMIT 1";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([':idllam' => $idllam]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Listado de aprendices activos de una ficha (para el acta de cierre en PDF)
     */
    public function getAprendicesFicha($idfic) {
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $sql = "SELECT u.idusu, u.ndocusu, u.nomusu, u.emausu
                FROM usuario AS u
                INNER JOIN usufic AS uf ON u.idusu = uf.idusu
                WHERE uf.idfic = :idfic AND u.actusu = 1
                ORDER BY u.nomusu ASC";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([':idfic' => $idfic]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Conclusiones de un acta de cierre (busca en acta/actdec por número de acta y usuario creador)
     */
    public function getConclusionesActa($numact, $idusu) {
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $sql = "SELECT a.nac, d.desacde, d.fecacde, d.idusu AS coorusu
                FROM acta AS a
                INNER JOIN actdec AS d ON d.idact = a.idact
                WHERE a.nac = :numact AND a.idusu = :idusu
                ORDER BY d.fecacde ASC";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([':numact' => $numact, ':idusu' => $idusu]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Nombre de un usuario (utilizado en los documentos PDF)
     */
    public function getNomUsu($idusu) {
        if (!$idusu) { return null; }
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $sql = "SELECT nomusu FROM usuario WHERE idusu = :idusu LIMIT 1";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([':idusu' => $idusu]);
        return $stmt->fetchColumn();
    }

    /**
     * Obtener coordinadores reales del sistema para los desplegables
     */
    public function getCoordinadores() {
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        
        $sql = "SELECT u.idusu, u.nomusu, u.ndocusu, COALESCE(p.nomper, 'Coordinador') AS nomper 
                FROM usuario AS u 
                LEFT JOIN perfil AS p ON u.idper = p.idper 
                WHERE LOWER(COALESCE(p.nomper, '')) LIKE '%coord%' 
                   OR LOWER(u.nomusu) LIKE '%coord%' 
                   OR u.idper IN (1, 18, 7, 8) 
                ORDER BY u.nomusu ASC";
        $stmt = $conexion->prepare($sql);
        $stmt->execute();
        $res = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($res)) {
            // Fallback: usuarios administradores o instructores principales
            $sqlFallback = "SELECT idusu, nomusu, ndocusu, 'Coordinación' AS nomper 
                            FROM usuario 
                            WHERE idper IN (1, 7, 18) OR actusu = 1 
                            LIMIT 10";
            $stmtFallback = $conexion->prepare($sqlFallback);
            $stmtFallback->execute();
            $res = $stmtFallback->fetchAll(PDO::FETCH_ASSOC);
        }

        return $res;
    }

    /**
     * Registrar Llamado de Atención automático (Audio 2)
     * Determina por debajo verbal o escrito (1 a 3 verbales, luego escrito)
     */
    public function registrarLlamado($idusu, $idfic, $obsllam, $idinst = null) {
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();

        if (!$idinst) {
            $idinst = $_SESSION['idusu'] ?? 1;
        }

        // Consultar cuántos llamados verbales previos tiene el aprendiz
        $sqlCount = "SELECT COUNT(*) FROM llamado WHERE idusu = :idusu AND tipllam = 1";
        $stmtCount = $conexion->prepare($sqlCount);
        $stmtCount->execute([':idusu' => $idusu]);
        $verbalesPrevios = (int)$stmtCount->fetchColumn();

        // Regla: si tiene menos de 3 llamados verbales, se asigna Verbal (1). De lo contrario, Escrito (2).
        $tipllam = ($verbalesPrevios < 3) ? 1 : 2;

        $sql = "INSERT INTO llamado (idusu, tipllam, obsllam, fecllam, firma, idinst) 
                VALUES (:idusu, :tipllam, :obsllam, NOW(), 0, :idinst)";
        $stmt = $conexion->prepare($sql);
        $res = $stmt->execute([
            ':idusu' => $idusu,
            ':tipllam' => $tipllam,
            ':obsllam' => $obsllam,
            ':idinst' => $idinst
        ]);

        return [
            'exito' => $res,
            'tipo' => ($tipllam == 1) ? 'Verbal' : 'Escrito',
            'tipllam' => $tipllam,
            'idllam' => $res ? $conexion->lastInsertId() : null
        ];
    }

    /**
     * Conteo de llamados de un aprendiz
     */
    public function getConteoLlamados($idusu) {
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();

        $sql = "SELECT 
                    SUM(CASE WHEN tipllam = 1 THEN 1 ELSE 0 END) AS verbales,
                    SUM(CASE WHEN tipllam = 2 THEN 1 ELSE 0 END) AS escritos,
                    COUNT(*) AS total
                FROM llamado 
                WHERE idusu = :idusu";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([':idusu' => $idusu]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'verbales' => (int)($res['verbales'] ?? 0),
            'escritos' => (int)($res['escritos'] ?? 0),
            'total' => (int)($res['total'] ?? 0)
        ];
    }

    /**
     * Historial de llamados de atención de un aprendiz
     */
    public function getHistorialLlamados($idusu) {
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();

        $sql = "SELECT l.idllam, l.idusu, l.tipllam, l.obsllam, l.fecllam, l.firma, u.nomusu AS instructor 
                FROM llamado AS l 
                LEFT JOIN usuario AS u ON l.idinst = u.idusu 
                WHERE l.idusu = :idusu 
                ORDER BY l.fecllam DESC";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([':idusu' => $idusu]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Registrar Deserción con coordinador y pantallazo de SENA Sofia Plus (Audio 2)
     */
    public function registrarDesercion($idusu, $idfic, $obsdec, $coorusu = null, $nomimg = null) {
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();

        // 1. Crear acta soporte (requerida por la FK desercion.idact -> acta.idact)
        $sqlActa = "INSERT INTO acta (nac, tipact, fecact, conact, idusu, coorusu) 
                    VALUES (1, 1, NOW(), :conact, :idusu, :coorusu)";
        $stmtActa = $conexion->prepare($sqlActa);
        $resActa = $stmtActa->execute([
            ':conact' => $obsdec,
            ':idusu' => $_SESSION['idusu'] ?? 1,
            ':coorusu' => $coorusu ? $coorusu : ($_SESSION['idusu'] ?? 1)
        ]);
        $idact = $resActa ? $conexion->lastInsertId() : null;

        // 2. Insertar en tabla desercion
        $sqlDes = "INSERT INTO desercion (idact, idusu, idfic, fecregdec, obsdec) 
                   VALUES (:idact, :idusu, :idfic, NOW(), :obsdec)";
        $stmtDes = $conexion->prepare($sqlDes);
        $resDes = $stmtDes->execute([
            ':idact' => $idact,
            ':idusu' => $idusu,
            ':idfic' => $idfic,
            ':obsdec' => $obsdec
        ]);

        // 3. Actualizar estado del aprendiz a Deserción (actusu = 3)
        $sqlUpd = "UPDATE usuario SET actusu = 3 WHERE idusu = :idusu";
        $stmtUpd = $conexion->prepare($sqlUpd);
        $stmtUpd->execute([':idusu' => $idusu]);

        // 4. Guardar la imagen del pantallazo de SENA Sofía Plus si fue procesada por opti()
        if ($nomimg) {
            try {
                $sqlImg = "INSERT INTO imagen_usuario (idusu, nomimg, fecha) 
                           VALUES (:idusu, :nomimg, NOW())";
                $stmtImg = $conexion->prepare($sqlImg);
                $stmtImg->execute([
                    ':idusu' => $idusu,
                    ':nomimg' => $nomimg
                ]);
            } catch (Exception $e) {
                // Continuar si ya está guardada
            }
        }

        return [
            'exito' => $resDes && $idact,
            'imagen' => $nomimg
        ];
    }
}
?>
