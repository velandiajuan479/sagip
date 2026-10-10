<?php
require_once(__DIR__ . '/conexion.php');

if (!function_exists('sena_utf8_clean')) {
    function sena_utf8_clean($str) {
        if ($str === null || $str === '') return '';
        if (!is_string($str)) return $str;

        $mojibakeMap = [
            'MaÃ±ana' => 'Mañana',
            'maÃ±ana' => 'mañana',
            'Ã¡' => 'á',
            'Ã©' => 'é',
            'Ã­' => 'í',
            'Ã³' => 'ó',
            'Ãº' => 'ú',
            'Ã±' => 'ñ',
            'Ã‘' => 'Ñ',
            'Ã' => 'Á',
            'Ã‰' => 'É',
            'Ã' => 'Í',
            'Ã“' => 'Ó',
            'Ãš' => 'Ú',
            'Ã¼' => 'ü',
            'Ãœ' => 'Ü',
            'Â¿' => '¿',
            'Â¡' => '¡',
            'Â°' => '°',
            'â€“' => '–',
            'â€”' => '—',
            'â€œ' => '“',
            'â€' => '”',
            'â€˜' => '‘',
            'â€™' => '’',
            'AnÃ¡lisis' => 'Análisis',
            'anÃ¡lisis' => 'análisis',
            'PlaneaciÃ³n' => 'Planeación',
            'planeaciÃ³n' => 'planeación',
            'EjecuciÃ³n' => 'Ejecución',
            'ejecuciÃ³n' => 'ejecución',
            'EvaluaciÃ³n' => 'Evaluación',
            'evaluaciÃ³n' => 'evaluación',
            'FormaciÃ³n' => 'Formación',
            'formaciÃ³n' => 'formación',
            'GestiÃ³n' => 'Gestión',
            'gestiÃ³n' => 'gestión',
            'ProducciÃ³n' => 'Producción',
            'producciÃ³n' => 'producción',
            'TecnolÃ³gico' => 'Tecnológico',
            'tecnolÃ³gico' => 'tecnológico',
            'BÃ¡sica' => 'Básica',
            'bÃ¡sica' => 'básica',
            'TÃ©cnico' => 'Técnico',
            'tÃ©cnico' => 'técnico'
        ];

        $cleaned = strtr($str, $mojibakeMap);

        if (preg_match('/[\xC3][\x80-\xBF]/', $cleaned)) {
            $utf8Decoded = @utf8_decode($cleaned);
            if (mb_check_encoding($utf8Decoded, 'UTF-8')) {
                $cleaned = $utf8Decoded;
            }
        }

        return $cleaned;
    }
}

if (!function_exists('sena_utf8_clean_array')) {
    function sena_utf8_clean_array($data) {
        if (!is_array($data)) {
            return is_string($data) ? sena_utf8_clean($data) : $data;
        }
        $result = [];
        foreach ($data as $key => $val) {
            $cleanKey = is_string($key) ? sena_utf8_clean($key) : $key;
            $result[$cleanKey] = is_array($val) ? sena_utf8_clean_array($val) : (is_string($val) ? sena_utf8_clean($val) : $val);
        }
        return $result;
    }
}

if (!function_exists('sena_clean_fase')) {
    function sena_clean_fase($fas) {
        $f = trim((string)$fas);
        if ($f === '1' || stripos($f, 'an') !== false) return 'Análisis';
        if ($f === '2' || stripos($f, 'plan') !== false) return 'Planeación';
        if ($f === '3' || stripos($f, 'ejec') !== false) return 'Ejecución';
        if ($f === '4' || stripos($f, 'eval') !== false) return 'Evaluación';
        return preg_replace('/^\d+\.\s*/', '', $f);
    }
}

class Hormage {
    private $idage;
    private $fas;
    private $actpro;
    private $idcom;
    private $idfic;
    private $fecini;
    private $fecfin;
    private $idusu;
    private $idres;
    private $horcom;
    private $idins;
    private $hor;
    private $lun;
    private $mar;
    private $mie;
    private $jue;
    private $vie;
    private $sab;
    private $fchinc;
    private $fchfnl;
    private $acti;

    // Getters y Setters
    public function getIdage() { return $this->idage; }
    public function setIdage($idage) { $this->idage = $idage; }

    public function getFas() { return $this->fas; }
    public function setFas($fas) { $this->fas = $fas; }

    public function getActpro() { return $this->actpro; }
    public function setActpro($actpro) { $this->actpro = $actpro; }

    public function getIdcom() { return $this->idcom; }
    public function setIdcom($idcom) { $this->idcom = $idcom; }

    public function getIdfic() { return $this->idfic; }
    public function setIdfic($idfic) { $this->idfic = $idfic; }

    public function getFecini() { return $this->fecini; }
    public function setFecini($fecini) { $this->fecini = $fecini; }

    public function getFecfin() { return $this->fecfin; }
    public function setFecfin($fecfin) { $this->fecfin = $fecfin; }

    public function getIdusu() { return $this->idusu; }
    public function setIdusu($idusu) { $this->idusu = $idusu; }

    public function getIdres() { return $this->idres; }
    public function setIdres($idres) { $this->idres = $idres; }

    public function getHorcom() { return $this->horcom; }
    public function setHorcom($horcom) { $this->horcom = $horcom; }

    public function getIdins() { return $this->idins; }
    public function setIdins($idins) { $this->idins = $idins; }

    public function getHor() { return $this->hor; }
    public function setHor($hor) { $this->hor = $hor; }

    public function getLun() { return $this->lun; }
    public function setLun($lun) { $this->lun = $lun; }

    public function getMar() { return $this->mar; }
    public function setMar($mar) { $this->mar = $mar; }

    public function getMie() { return $this->mie; }
    public function setMie($mie) { $this->mie = $mie; }

    public function getJue() { return $this->jue; }
    public function setJue($jue) { $this->jue = $jue; }

    public function getVie() { return $this->vie; }
    public function setVie($vie) { $this->vie = $vie; }

    public function getSab() { return $this->sab; }
    public function setSab($sab) { $this->sab = $sab; }

    public function getFchinc() { return $this->fchinc; }
    public function setFchinc($fchinc) { $this->fchinc = $fchinc; }

    public function getFchfnl() { return $this->fchfnl; }
    public function setFchfnl($fchfnl) { $this->fchfnl = $fchfnl; }

    public function getActi() { return $this->acti; }
    public function setActi($acti) { $this->acti = $acti; }

    public function getGuia() { return $this->guia; }
    public function setGuia($guia) { $this->guia = $guia; }

    /**
     * Obtener registros de agenda con filtros dinámicos y relaciones
     */
    public function getAgendas($filters = []) {
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();

        // Verificar si existe la columna guia sin modificar la estructura de la base de datos
        $selectGuia = "NULL AS guia";
        try {
            $check = $conexion->query("SHOW COLUMNS FROM actividad LIKE 'guia'");
            if ($check && $check->rowCount() > 0) {
                $selectGuia = "act.guia AS guia";
            }
        } catch (Exception $e) {
            $selectGuia = "NULL AS guia";
        }

        $sql = "SELECT 
                    a.idage, 
                    a.fas, 
                    a.actpro, 
                    a.idcom, 
                    a.idfic, 
                    a.fecini, 
                    a.fecfin, 
                    a.idusu, 
                    a.idres, 
                    COALESCE(NULLIF(c.horcom, 0), NULLIF(a.horcom, 0), c.horcom, a.horcom, 0) AS horcom, 
                    a.idins, 
                    a.hor, 
                    a.lun, 
                    a.mar, 
                    a.mie, 
                    a.jue, 
                    a.vie, 
                    a.sab, 
                    a.fchinc, 
                    a.fchfnl, 
                    a.acti,
                    act.idact AS idact_ref,
                    " . $selectGuia . ",
                    COALESCE(c.descom, 'Competencia general') AS descom,
                    c.vercom,
                    COALESCE(r.nomres, 'Resultado de aprendizaje') AS nomres,
                    r.ndeses,
                    COALESCE(u_ins.nomusu, u_usu.nomusu, 'Sin asignar') AS nomusu,
                    f.nomfic,
                    f.codpro,
                    f.jornada,
                    v_jor.nomval AS nomjornada,
                    COALESCE(p.nompro, f.nomfic, 'Programa de Formación') AS nompro,
                    p.verpro,
                    ub.nomubi AS nommunicipio
                FROM agenda AS a
                LEFT JOIN ficha AS f ON a.idfic = f.idfic
                LEFT JOIN competencia AS c ON a.idcom = c.idcom
                LEFT JOIN resultado AS r ON a.idres = r.idres
                LEFT JOIN usuario AS u_ins ON a.idins = u_ins.idusu
                LEFT JOIN usuario AS u_usu ON a.idusu = u_usu.idusu
                LEFT JOIN valor AS v_jor ON f.jornada = v_jor.idval
                LEFT JOIN programa AS p ON f.codpro = p.codpro
                LEFT JOIN ubica AS ub ON f.mun = ub.codubi
                LEFT JOIN actividad AS act ON act.idact = (
                    SELECT a2.idact FROM actividad AS a2
                    WHERE a.acti = a2.idact
                       OR (a.actpro IS NOT NULL AND a.actpro != '' AND (a.actpro = a2.desact OR a.actpro = a2.nomact))
                    ORDER BY CASE WHEN a.acti = a2.idact THEN 0 ELSE 1 END, a2.idact
                    LIMIT 1
                )
                WHERE 1 = 1";

        $params = [];

        // Filtro por ficha
        if (!empty($filters['idfic']) && $filters['idfic'] !== 'all') {
            $sql .= " AND a.idfic = :idfic";
            $params[':idfic'] = $filters['idfic'];
        }

        // Filtro por fase (1. Análisis, 2. Planeación, 3. Ejecución, 4. Evaluación)
        if (!empty($filters['fas']) && $filters['fas'] !== 'all') {
            $f = trim($filters['fas']);
            if ($f === '1' || stripos($f, 'an') !== false) {
                $sql .= " AND (a.fas = '1' OR LOWER(a.fas) LIKE '%an%')";
            } elseif ($f === '2' || stripos($f, 'plan') !== false) {
                $sql .= " AND (a.fas = '2' OR LOWER(a.fas) LIKE '%plan%')";
            } elseif ($f === '3' || stripos($f, 'ejec') !== false) {
                $sql .= " AND (a.fas = '3' OR LOWER(a.fas) LIKE '%ejec%')";
            } elseif ($f === '4' || stripos($f, 'eval') !== false) {
                $sql .= " AND (a.fas = '4' OR LOWER(a.fas) LIKE '%eval%')";
            } else {
                $sql .= " AND LOWER(TRIM(a.fas)) = LOWER(TRIM(:fas))";
                $params[':fas'] = $f;
            }
        }

        // Aislamiento de seguridad por rol: Instructor o Aprendiz bloqueado
        if (!empty($filters['instructor_locked'])) {
            $sql .= " AND (a.idins = :inst_locked OR a.idusu = :inst_locked)";
            $params[':inst_locked'] = $filters['instructor_locked'];
        }
        if (!empty($filters['ficha_locked'])) {
            $lista = is_array($filters['ficha_locked']) ? array_values($filters['ficha_locked']) : [$filters['ficha_locked']];
            $lista = array_values(array_filter(array_map('strval', $lista), function ($v) { return $v !== ''; }));
            if (empty($lista)) {
                $sql .= " AND 1 = 0";
            } else {
                $ph = [];
                foreach ($lista as $i => $idf) {
                    $k = ':fic_locked_' . $i;
                    $ph[] = $k;
                    $params[$k] = $idf;
                }
                $sql .= " AND a.idfic IN (" . implode(',', $ph) . ")";
            }
        }

        // Filtro por instructor (puede estar en idins o idusu)
        if (!empty($filters['idins']) && $filters['idins'] !== 'all') {
            $sql .= " AND (a.idins = :idins OR a.idusu = :idins)";
            $params[':idins'] = $filters['idins'];
        }

        // Filtro por jornada
        if (!empty($filters['jornada']) && $filters['jornada'] !== 'all') {
            $sql .= " AND (f.jornada = :jornada OR LOWER(v_jor.nomval) LIKE LOWER(:jornada_txt))";
            $params[':jornada'] = $filters['jornada'];
            $params[':jornada_txt'] = '%' . $filters['jornada'] . '%';
        }

        // Filtro por rango de fechas (fecini o fecfin)
        if (!empty($filters['fecini'])) {
            $sql .= " AND (a.fecini >= :fecini OR a.fecfin >= :fecini)";
            $params[':fecini'] = $filters['fecini'];
        }
        if (!empty($filters['fecfin'])) {
            $sql .= " AND (a.fecfin <= :fecfin OR a.fecini <= :fecfin)";
            $params[':fecfin'] = $filters['fecfin'];
        }

        // Filtro por texto de búsqueda libre
        if (!empty($filters['buscar'])) {
            $sql .= " AND (
                LOWER(a.actpro) LIKE LOWER(:b1) OR 
                LOWER(c.descom) LIKE LOWER(:b2) OR 
                LOWER(r.nomres) LIKE LOWER(:b3) OR 
                LOWER(u_ins.nomusu) LIKE LOWER(:b4) OR 
                LOWER(u_usu.nomusu) LIKE LOWER(:b5) OR 
                LOWER(a.fas) LIKE LOWER(:b6)
            )";
            $bTerm = '%' . $filters['buscar'] . '%';
            $params[':b1'] = $bTerm;
            $params[':b2'] = $bTerm;
            $params[':b3'] = $bTerm;
            $params[':b4'] = $bTerm;
            $params[':b5'] = $bTerm;
            $params[':b6'] = $bTerm;
        }

        // Ordenamiento por fecha de inicio, fase y actividad
        $sql .= " ORDER BY a.fecini ASC, a.fas ASC, a.actpro ASC, a.idage ASC";

        $stmt = $conexion->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->execute();
        $agendas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($agendas as &$item) {
            $item['fas'] = sena_clean_fase($item['fas'] ?? '');
        }
        unset($item);
        return sena_utf8_clean_array($agendas);
    }

    /**
     * Obtener listado de todas las fichas disponibles, con soporte para filtrado por rol
     */
    public function getAllFichas($idusu = null, $isInstructor = false, $isAprendiz = false) {
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();

        if ($isAprendiz && !empty($idusu)) {
            $sql = "SELECT DISTINCT f.idfic, f.nomfic, f.codpro, p.nompro
                    FROM ficha AS f
                    INNER JOIN usufic AS uf ON f.idfic = uf.idfic
                    LEFT JOIN programa AS p ON f.codpro = p.codpro
                    WHERE uf.idusu = :idusu
                    ORDER BY f.idfic ASC";
            $stmt = $conexion->prepare($sql);
            $stmt->bindValue(':idusu', $idusu, PDO::PARAM_INT);
            $stmt->execute();
            $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return sena_utf8_clean_array($res);
        }

        if ($isInstructor && !empty($idusu)) {
            $sql = "SELECT DISTINCT f.idfic, f.nomfic, f.codpro, p.nompro
                    FROM ficha AS f
                    LEFT JOIN programa AS p ON f.codpro = p.codpro
                    WHERE f.idfic IN (
                        SELECT DISTINCT idfic FROM agenda WHERE (idins = :idusu OR idusu = :idusu) AND idfic IS NOT NULL
                        UNION
                        SELECT DISTINCT idfic FROM horario WHERE idusu = :idusu AND idfic IS NOT NULL
                    )
                    ORDER BY f.idfic ASC";
            $stmt = $conexion->prepare($sql);
            $stmt->bindValue(':idusu', $idusu, PDO::PARAM_INT);
            $stmt->execute();
            $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
            // Instructor sin fichas vinculadas: lista vacía (no caer al
            // catálogo global para no romper el aislamiento por rol).
            return sena_utf8_clean_array(is_array($res) ? $res : []);
        }

        $sql = "SELECT DISTINCT f.idfic, f.nomfic, f.codpro, p.nompro
                FROM ficha AS f
                LEFT JOIN programa AS p ON f.codpro = p.codpro
                ORDER BY f.idfic ASC";
        $stmt = $conexion->prepare($sql);
        $stmt->execute();
        $res = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Si la tabla ficha está vacía o no tiene todas las de agenda, asegurar traer las de agenda
        if (empty($res)) {
            $sql2 = "SELECT DISTINCT a.idfic, CONCAT('Ficha ', a.idfic) AS nomfic, NULL AS codpro, '' AS nompro
                     FROM agenda AS a
                     WHERE a.idfic IS NOT NULL AND a.idfic != ''
                     ORDER BY a.idfic ASC";
            $stmt2 = $conexion->prepare($sql2);
            $stmt2->execute();
            $res = $stmt2->fetchAll(PDO::FETCH_ASSOC);
        }
        return sena_utf8_clean_array($res);
    }


    /**
     * Obtener el detalle informativo de una ficha específica
     */
    public function getFichaDetalle($idfic) {
        if (empty($idfic)) return null;

        $sql = "SELECT 
                    f.idfic, 
                    f.nomfic, 
                    f.jornada, 
                    f.mun, 
                    f.finific, 
                    f.ffinfic,
                    COALESCE(v.nomval, 'No especificada') AS nomjornada,
                    p.codpro, 
                    COALESCE(p.nompro, 'Programa de Formación') AS nompro, 
                    COALESCE(p.verpro, '1') AS verpro, 
                    COALESCE(p.horlpro, 0) AS horlpro, 
                    COALESCE(p.horppro, 0) AS horppro, 
                    COALESCE(p.crelpro, 0) AS crelpro, 
                    COALESCE(p.creppro, 0) AS creppro,
                    COALESCE(u.nomubi, 'Centro') AS nommunicipio
                FROM ficha AS f
                LEFT JOIN valor AS v ON f.jornada = v.idval
                LEFT JOIN programa AS p ON f.codpro = p.codpro
                LEFT JOIN ubica AS u ON f.mun = u.codubi
                WHERE f.idfic = :idfic";

        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':idfic', $idfic);
        $stmt->execute();
        $detalle = $stmt->fetch(PDO::FETCH_ASSOC);

        // Si no está en ficha pero sí en agenda, generar objeto básico
        if (!$detalle) {
            $detalle = [
                'idfic' => $idfic,
                'nomfic' => 'Ficha ' . $idfic,
                'jornada' => '',
                'nomjornada' => 'No especificada',
                'codpro' => '',
                'nompro' => 'Programa de Formación',
                'verpro' => '1',
                'horlpro' => 0,
                'horppro' => 0,
                'crelpro' => 0,
                'creppro' => 0,
                'nommunicipio' => 'SENA Centro'
            ];
        }

        return sena_utf8_clean_array($detalle);
    }

    /**
     * Obtener las 4 fases de formación SENA
     */
    public function getDistinctFases() {
        return ['Análisis', 'Planeación', 'Ejecución', 'Evaluación'];
    }

    /**
     * Obtener lista de instructores que participan en agendas o usuarios con rol instructor
     */
    public function getDistinctInstructores() {
        $sql = "SELECT DISTINCT u.idusu, u.nomusu 
                FROM usuario AS u
                WHERE u.idusu IN (
                    SELECT DISTINCT idins FROM agenda WHERE idins IS NOT NULL AND idins > 0
                    UNION
                    SELECT DISTINCT idusu FROM agenda WHERE idusu IS NOT NULL AND idusu > 0
                )
                ORDER BY u.nomusu ASC";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $stmt = $conexion->prepare($sql);
        $stmt->execute();
        $res = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Si la lista está vacía, traer usuarios activos
        if (empty($res)) {
            $sql2 = "SELECT idusu, nomusu FROM usuario WHERE nomusu IS NOT NULL ORDER BY nomusu ASC LIMIT 30";
            $stmt2 = $conexion->prepare($sql2);
            $stmt2->execute();
            $res = $stmt2->fetchAll(PDO::FETCH_ASSOC);
        }
        return sena_utf8_clean_array($res);
    }

    /**
     * Obtener catálogo de jornadas registradas en la base de datos
     */
    public function getDistinctJornadas() {
        $sql = "SELECT idval, nomval FROM valor WHERE iddom = 1 ORDER BY nomval ASC";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $stmt = $conexion->prepare($sql);
        $stmt->execute();
        $res = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($res)) {
            $res = [
                ['idval' => 'Mañana', 'nomval' => 'Mañana (6:00 a.m. - 1:00 p.m.)'],
                ['idval' => 'Tarde', 'nomval' => 'Tarde (12:00 p.m. - 6:00 p.m.)'],
                ['idval' => 'Diurna', 'nomval' => 'Diurna (6:00 a.m. - 6:00 p.m.)'],
                ['idval' => 'Nocturna', 'nomval' => 'Nocturna (6:00 p.m. - 10:00 p.m.)'],
                ['idval' => 'Mixta', 'nomval' => 'Mixta'],
                ['idval' => 'Virtual', 'nomval' => 'Virtual']
            ];
        }
        return sena_utf8_clean_array($res);
    }

    /**
     * Métodos CRUD básicos para compatibilidad
     */
    public function getAll() {
        return $this->getAgendas();
    }

    public function getOne() {
        $sql = "SELECT * FROM agenda WHERE idage = :idage";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(":idage", $this->idage);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function save() {
        $sql = "INSERT INTO agenda (fas, actpro, idcom, idfic, fecini, fecfin, idusu, idres, horcom, idins, hor, lun, mar, mie, jue, vie, sab, fchinc, fchfnl, acti) 
                VALUES (:fas, :actpro, :idcom, :idfic, :fecini, :fecfin, :idusu, :idres, :horcom, :idins, :hor, :lun, :mar, :mie, :jue, :vie, :sab, :fchinc, :fchfnl, :acti)";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(":fas", $this->fas);
        $stmt->bindParam(":actpro", $this->actpro);
        $stmt->bindParam(":idcom", $this->idcom);
        $stmt->bindParam(":idfic", $this->idfic);
        $stmt->bindParam(":fecini", $this->fecini);
        $stmt->bindParam(":fecfin", $this->fecfin);
        $stmt->bindParam(":idusu", $this->idusu);
        $stmt->bindParam(":idres", $this->idres);
        $stmt->bindParam(":horcom", $this->horcom);
        $stmt->bindParam(":idins", $this->idins);
        $stmt->bindParam(":hor", $this->hor);
        $stmt->bindParam(":lun", $this->lun);
        $stmt->bindParam(":mar", $this->mar);
        $stmt->bindParam(":mie", $this->mie);
        $stmt->bindParam(":jue", $this->jue);
        $stmt->bindParam(":vie", $this->vie);
        $stmt->bindParam(":sab", $this->sab);
        $stmt->bindParam(":fchinc", $this->fchinc);
        $stmt->bindParam(":fchfnl", $this->fchfnl);
        $stmt->bindParam(":acti", $this->acti);
        $stmt->execute();
    }

    public function edit() {
        $sql = "UPDATE agenda SET 
                    fas = :fas, 
                    actpro = :actpro, 
                    idcom = :idcom, 
                    idfic = :idfic, 
                    fecini = :fecini, 
                    fecfin = :fecfin, 
                    idusu = :idusu, 
                    idres = :idres, 
                    horcom = :horcom, 
                    idins = :idins, 
                    hor = :hor, 
                    lun = :lun, 
                    mar = :mar, 
                    mie = :mie, 
                    jue = :jue, 
                    vie = :vie, 
                    sab = :sab, 
                    fchinc = :fchinc, 
                    fchfnl = :fchfnl, 
                    acti = :acti 
                WHERE idage = :idage";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(":idage", $this->idage);
        $stmt->bindParam(":fas", $this->fas);
        $stmt->bindParam(":actpro", $this->actpro);
        $stmt->bindParam(":idcom", $this->idcom);
        $stmt->bindParam(":idfic", $this->idfic);
        $stmt->bindParam(":fecini", $this->fecini);
        $stmt->bindParam(":fecfin", $this->fecfin);
        $stmt->bindParam(":idusu", $this->idusu);
        $stmt->bindParam(":idres", $this->idres);
        $stmt->bindParam(":horcom", $this->horcom);
        $stmt->bindParam(":idins", $this->idins);
        $stmt->bindParam(":hor", $this->hor);
        $stmt->bindParam(":lun", $this->lun);
        $stmt->bindParam(":mar", $this->mar);
        $stmt->bindParam(":mie", $this->mie);
        $stmt->bindParam(":jue", $this->jue);
        $stmt->bindParam(":vie", $this->vie);
        $stmt->bindParam(":sab", $this->sab);
        $stmt->bindParam(":fchinc", $this->fchinc);
        $stmt->bindParam(":fchfnl", $this->fchfnl);
        $stmt->bindParam(":acti", $this->acti);
        $stmt->execute();
    }

    /**
     * Métodos legados para mantener compatibilidad con cualquier vista existente
     */
    public function getAllAge() {
        $filters = [];
        if (!empty($this->idfic)) {
            $filters['idfic'] = $this->idfic;
        }
        return $this->getAgendas($filters);
    }

    public function getInstrumentosByResultado($idres) {
        $sql = "SELECT idins, nomins FROM inseva WHERE idres = :idres";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':idres', $idres);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
