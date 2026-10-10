<?php
class Mpas {

    private $idpas;
    private $idflu;
    private $numpas;
    private $descpas;
    private $idper;
    private $created_at;
    private $updated_at;

    // Getters
    function getIdpas() {
        return $this->idpas;
    }
    function getIdflu() {
        return $this->idflu;
    }
    function getNumpas() {
        return $this->numpas;
    }
    function getDescpas() {
        return $this->descpas;
    }
    function getIdper() {
        return $this->idper;
    }
    function getCreatedAt() {
        return $this->created_at;
    }
    function getUpdatedAt() {
        return $this->updated_at;
    }

    // Setters
    function setIdpas($idpas) {
        $this->idpas = $idpas;
    }
    function setIdflu($idflu) {
        $this->idflu = $idflu;
    }
    function setNumpas($numpas) {
        $this->numpas = $numpas;
    }
    function setDescpas($descpas) {
        $this->descpas = $descpas;
    }
    function setIdper($idper) {
        $this->idper = $idper;
    }
    function setCreatedAt($created_at) {
        $this->created_at = $created_at;
    }
    function setUpdatedAt($updated_at) {
        $this->updated_at = $updated_at;
    }

    /**
     * Verifica de forma segura y en modo lectura si una columna existe en la tabla
     */
    private function hasColumn($table, $column) {
        static $columnCache = [];
        $key = $table . "." . $column;
        if (isset($columnCache[$key])) {
            return $columnCache[$key];
        }
        try {
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $stmt = $conexion->prepare("SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table AND COLUMN_NAME = :col LIMIT 1");
            $stmt->execute([':table' => $table, ':col' => $column]);
            $columnCache[$key] = ($stmt->fetch() !== false);
        } catch (Exception $e) {
            try {
                $modelo2 = new conexion();
                $conexion2 = $modelo2->get_conexion();
                $stmt2 = $conexion2->query("SHOW COLUMNS FROM `" . $table . "` LIKE '" . addslashes($column) . "'");
                $columnCache[$key] = ($stmt2 && $stmt2->fetch() !== false);
            } catch (Exception $e2) {
                $columnCache[$key] = false;
            }
        }
        return $columnCache[$key];
    }

    /**
     * Obtiene el siguiente número de paso correlativo para un flujo específico
     */
    public function getNextStepNumber($idflu) {
        try {
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();

            if ($this->hasColumn('paso', 'numpas')) {
                $sql = "SELECT COALESCE(MAX(numpas), 0) + 1 AS next_num FROM paso WHERE idflu = :idflu";
            } else {
                $sql = "SELECT COUNT(*) + 1 AS next_num FROM paso WHERE idflu = :idflu";
            }

            $result = $conexion->prepare($sql);
            $result->bindParam(":idflu", $idflu, PDO::PARAM_INT);
            $result->execute();
            $row = $result->fetch(PDO::FETCH_ASSOC);
            return $row ? intval($row['next_num']) : 1;
        } catch (Exception $e) {
            return 1;
        }
    }

    /**
     * Obtiene todos los pasos registrados en el sistema con información de su flujo y perfil
     */
    public function getAll() {
        try {
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $hasNum = $this->hasColumn('paso', 'numpas');

            if ($hasNum) {
                $sql = "SELECT p.idpas, p.idflu, 
                               COALESCE(p.numpas, 1) AS numpas, 
                               p.descpas, p.idper, 
                               f.nomflu, f.fluacti, 
                               COALESCE(j.nomper, 'Sin Perfil') AS nomper,
                               COALESCE(fc.cant_pasos, 0) AS cant_pasos
                        FROM paso AS p 
                        INNER JOIN flujo AS f ON p.idflu = f.idflu 
                        LEFT JOIN perfil AS j ON p.idper = j.idper
                        LEFT JOIN (
                            SELECT idflu, COUNT(*) AS cant_pasos FROM paso GROUP BY idflu
                        ) AS fc ON p.idflu = fc.idflu
                        ORDER BY f.nomflu ASC, p.numpas ASC, p.idpas ASC";
                $result = $conexion->prepare($sql);
                $result->execute();
                return $result->fetchAll(PDO::FETCH_ASSOC);
            } else {
                $sql = "SELECT p.idpas, p.idflu, 
                               p.descpas, p.idper, 
                               f.nomflu, f.fluacti, 
                               COALESCE(j.nomper, 'Sin Perfil') AS nomper,
                               COALESCE(fc.cant_pasos, 0) AS cant_pasos
                        FROM paso AS p 
                        INNER JOIN flujo AS f ON p.idflu = f.idflu 
                        LEFT JOIN perfil AS j ON p.idper = j.idper
                        LEFT JOIN (
                            SELECT idflu, COUNT(*) AS cant_pasos FROM paso GROUP BY idflu
                        ) AS fc ON p.idflu = fc.idflu
                        ORDER BY f.nomflu ASC, p.idpas ASC";
                $result = $conexion->prepare($sql);
                $result->execute();
                $rows = $result->fetchAll(PDO::FETCH_ASSOC);

                // Asignar numeración secuencial dinámica en memoria si no existe la columna
                $counts = [];
                foreach ($rows as &$r) {
                    $fId = $r['idflu'];
                    if (!isset($counts[$fId])) {
                        $counts[$fId] = 0;
                    }
                    $counts[$fId]++;
                    $r['numpas'] = $counts[$fId];
                }
                return $rows;
            }
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage() . "<br><br>Comuníquese con su administrador.";
            return [];
        }
    }

    /**
     * Obtiene los pasos filtrados de un flujo específico ordenados secuencialmente
     */
    public function getByFlujo($idflu) {
        try {
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $hasNum = $this->hasColumn('paso', 'numpas');

            if ($hasNum) {
                $sql = "SELECT p.idpas, p.idflu, 
                               COALESCE(p.numpas, 1) AS numpas, 
                               p.descpas, p.idper, 
                               f.nomflu, f.fluacti, 
                               COALESCE(j.nomper, 'Sin Perfil') AS nomper,
                               COALESCE(fc.cant_pasos, 0) AS cant_pasos
                        FROM paso AS p 
                        INNER JOIN flujo AS f ON p.idflu = f.idflu 
                        LEFT JOIN perfil AS j ON p.idper = j.idper 
                        LEFT JOIN (
                            SELECT idflu, COUNT(*) AS cant_pasos FROM paso GROUP BY idflu
                        ) AS fc ON p.idflu = fc.idflu
                        WHERE p.idflu = :idflu
                        ORDER BY p.numpas ASC, p.idpas ASC";
                $result = $conexion->prepare($sql);
                $result->bindParam(":idflu", $idflu, PDO::PARAM_INT);
                $result->execute();
                return $result->fetchAll(PDO::FETCH_ASSOC);
            } else {
                $sql = "SELECT p.idpas, p.idflu, 
                               p.descpas, p.idper, 
                               f.nomflu, f.fluacti, 
                               COALESCE(j.nomper, 'Sin Perfil') AS nomper,
                               COALESCE(fc.cant_pasos, 0) AS cant_pasos
                        FROM paso AS p 
                        INNER JOIN flujo AS f ON p.idflu = f.idflu 
                        LEFT JOIN perfil AS j ON p.idper = j.idper 
                        LEFT JOIN (
                            SELECT idflu, COUNT(*) AS cant_pasos FROM paso GROUP BY idflu
                        ) AS fc ON p.idflu = fc.idflu
                        WHERE p.idflu = :idflu
                        ORDER BY p.idpas ASC";
                $result = $conexion->prepare($sql);
                $result->bindParam(":idflu", $idflu, PDO::PARAM_INT);
                $result->execute();
                $rows = $result->fetchAll(PDO::FETCH_ASSOC);

                $seq = 1;
                foreach ($rows as &$r) {
                    $r['numpas'] = $seq++;
                }
                return $rows;
            }
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
            return [];
        }
    }

    /**
     * Obtiene un paso específico por su ID
     */
    public function getOne() {
        try {
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $hasNum = $this->hasColumn('paso', 'numpas');
            $colNum = $hasNum ? "COALESCE(p.numpas, 1) AS numpas," : "1 AS numpas,";

            $sql = "SELECT p.idpas, p.idflu, " . $colNum . " 
                           p.descpas, p.idper, 
                           f.nomflu, f.fluacti, 
                           COALESCE(j.nomper, 'Sin Perfil') AS nomper 
                    FROM paso AS p 
                    INNER JOIN flujo AS f ON p.idflu = f.idflu 
                    LEFT JOIN perfil AS j ON p.idper = j.idper 
                    WHERE p.idpas = :idpas";

            $result = $conexion->prepare($sql);
            $idpas = $this->getIdpas();
            $result->bindParam(":idpas", $idpas, PDO::PARAM_INT);
            $result->execute();
            return $result->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage() . "<br><br>Comuníquese con su administrador.";
            return [];
        }
    }

    /**
     * Guarda un nuevo paso asignándole secuencialmente su número de paso
     */
    public function save() {
        try {
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();

            $idflu = $this->getIdflu();
            $descpas = $this->getDescpas();
            $idper = $this->getIdper();
            $hasNum = $this->hasColumn('paso', 'numpas');

            $numpas = $this->getNumpas();
            if (!$numpas || intval($numpas) <= 0) {
                $numpas = $this->getNextStepNumber($idflu);
            }

            // Calcular siguiente idpas para evitar fallo si no tiene AUTO_INCREMENT
            $stmtMax = $conexion->query("SELECT COALESCE(MAX(idpas), 0) + 1 AS next_id FROM paso");
            $rowMax = $stmtMax ? $stmtMax->fetch(PDO::FETCH_ASSOC) : null;
            $nextId = ($rowMax && isset($rowMax['next_id'])) ? intval($rowMax['next_id']) : 1;
            if ($nextId <= 0) { $nextId = 1; }

            // Intento 1: Con idpas explícito
            try {
                if ($hasNum) {
                    $sql = "INSERT INTO paso (idpas, idflu, numpas, descpas, idper) VALUES (:idpas, :idflu, :numpas, :descpas, :idper)";
                    $result = $conexion->prepare($sql);
                    $result->bindParam(":idpas", $nextId, PDO::PARAM_INT);
                    $result->bindParam(":idflu", $idflu, PDO::PARAM_INT);
                    $result->bindParam(":numpas", $numpas, PDO::PARAM_INT);
                    $result->bindParam(":descpas", $descpas);
                    $result->bindParam(":idper", $idper);
                } else {
                    $sql = "INSERT INTO paso (idpas, idflu, descpas, idper) VALUES (:idpas, :idflu, :descpas, :idper)";
                    $result = $conexion->prepare($sql);
                    $result->bindParam(":idpas", $nextId, PDO::PARAM_INT);
                    $result->bindParam(":idflu", $idflu, PDO::PARAM_INT);
                    $result->bindParam(":descpas", $descpas);
                    $result->bindParam(":idper", $idper);
                }
                $result->execute();
                return $nextId;
            } catch (Exception $e1) {
                // Intento 2: Inserción estándar sin idpas si tiene AUTO_INCREMENT
                if ($hasNum) {
                    $sql = "INSERT INTO paso (idflu, numpas, descpas, idper) VALUES (:idflu, :numpas, :descpas, :idper)";
                    $result = $conexion->prepare($sql);
                    $result->bindParam(":idflu", $idflu, PDO::PARAM_INT);
                    $result->bindParam(":numpas", $numpas, PDO::PARAM_INT);
                    $result->bindParam(":descpas", $descpas);
                    $result->bindParam(":idper", $idper);
                } else {
                    $sql = "INSERT INTO paso (idflu, descpas, idper) VALUES (:idflu, :descpas, :idper)";
                    $result = $conexion->prepare($sql);
                    $result->bindParam(":idflu", $idflu, PDO::PARAM_INT);
                    $result->bindParam(":descpas", $descpas);
                    $result->bindParam(":idper", $idper);
                }
                $result->execute();
                $last = $conexion->lastInsertId();
                return $last ? $last : $nextId;
            }
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage() . "<br><br>Comuníquese con su administrador.";
            return false;
        }
    }

    /**
     * Actualiza la información de un paso
     */
    public function edit() {
        try {
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();

            $idpas = $this->getIdpas();
            $idflu = $this->getIdflu();
            $descpas = $this->getDescpas();
            $idper = $this->getIdper();
            $hasNum = $this->hasColumn('paso', 'numpas');

            if ($hasNum) {
                $numpas = $this->getNumpas();
                $sql = "UPDATE paso SET idflu = :idflu, numpas = :numpas, descpas = :descpas, idper = :idper WHERE idpas = :idpas";
                $result = $conexion->prepare($sql);
                $result->bindParam(":numpas", $numpas, PDO::PARAM_INT);
            } else {
                $sql = "UPDATE paso SET idflu = :idflu, descpas = :descpas, idper = :idper WHERE idpas = :idpas";
                $result = $conexion->prepare($sql);
            }

            $result->bindParam(":idpas", $idpas, PDO::PARAM_INT);
            $result->bindParam(":idflu", $idflu, PDO::PARAM_INT);
            $result->bindParam(":descpas", $descpas);
            $result->bindParam(":idper", $idper);
            return $result->execute();
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage() . "<br><br>Comuníquese con su administrador.";
            return false;
        }
    }

    /**
     * Elimina un paso y re-compacta la secuencia numérica de los pasos restantes
     */
    public function del() {
        try {
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();

            $idpas = $this->getIdpas();

            // 1. Obtener el idflu de este paso antes de eliminar
            $stmtFlu = $conexion->prepare("SELECT idflu FROM paso WHERE idpas = :idpas");
            $stmtFlu->bindParam(":idpas", $idpas, PDO::PARAM_INT);
            $stmtFlu->execute();
            $row = $stmtFlu->fetch(PDO::FETCH_ASSOC);
            $idflu = $row ? $row['idflu'] : null;

            // 2. Eliminar el paso (DML estándar)
            $sql = "DELETE FROM paso WHERE idpas = :idpas";
            $result = $conexion->prepare($sql);
            $result->bindParam(":idpas", $idpas, PDO::PARAM_INT);
            $result->execute();

            // 3. Re-compactar la secuencia de los pasos restantes si la columna numpas existe
            if ($idflu && $this->hasColumn('paso', 'numpas')) {
                $this->recompactSequence($conexion, $idflu);
            }

            return true;
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage() . "<br><br>Comuníquese con su administrador.";
            return false;
        }
    }

    /**
     * Re-enumera secuencialmente sin huecos los pasos de un flujo
     */
    private function recompactSequence($conexion, $idflu) {
        try {
            if (!$this->hasColumn('paso', 'numpas')) return;

            $stmt = $conexion->prepare("SELECT idpas FROM paso WHERE idflu = :idflu ORDER BY COALESCE(numpas, idpas) ASC, idpas ASC");
            $stmt->bindParam(":idflu", $idflu, PDO::PARAM_INT);
            $stmt->execute();
            $pasos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $seq = 1;
            $upd = $conexion->prepare("UPDATE paso SET numpas = :numpas WHERE idpas = :idpas");
            foreach ($pasos as $p) {
                $upd->execute([':numpas' => $seq, ':idpas' => $p['idpas']]);
                $seq++;
            }
        } catch (Exception $e) {
            // Silencioso
        }
    }

    /**
     * Permite subir ('up') o bajar ('down') el orden de un paso intercambiando su secuencia
     */
    public function moveStep($idpas, $direction) {
        try {
            if (!$this->hasColumn('paso', 'numpas')) return true;

            $modelo = new conexion();
            $conexion = $modelo->get_conexion();

            // Obtener datos del paso actual
            $stmt = $conexion->prepare("SELECT idpas, idflu, COALESCE(numpas, 1) AS numpas FROM paso WHERE idpas = :idpas");
            $stmt->bindParam(":idpas", $idpas, PDO::PARAM_INT);
            $stmt->execute();
            $curr = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$curr) return false;

            $idflu = $curr['idflu'];
            $currNum = intval($curr['numpas']);

            if ($direction === 'up') {
                $stmtAdj = $conexion->prepare("SELECT idpas, numpas FROM paso WHERE idflu = :idflu AND numpas < :currNum ORDER BY numpas DESC LIMIT 1");
            } else {
                $stmtAdj = $conexion->prepare("SELECT idpas, numpas FROM paso WHERE idflu = :idflu AND numpas > :currNum ORDER BY numpas ASC LIMIT 1");
            }

            $stmtAdj->bindParam(":idflu", $idflu, PDO::PARAM_INT);
            $stmtAdj->bindParam(":currNum", $currNum, PDO::PARAM_INT);
            $stmtAdj->execute();
            $adj = $stmtAdj->fetch(PDO::FETCH_ASSOC);

            if ($adj) {
                $adjId = $adj['idpas'];
                $adjNum = intval($adj['numpas']);

                $upd = $conexion->prepare("UPDATE paso SET numpas = :numpas WHERE idpas = :idpas");
                $upd->execute([':numpas' => $adjNum, ':idpas' => $idpas]);
                $upd->execute([':numpas' => $currNum, ':idpas' => $adjId]);
                return true;
            }

            return false;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Retorna todos los flujos registrados para listados y selectores
     */
    public function getAllFlu(){
        try {
            $sql = "SELECT f.idflu, f.nomflu, f.fluacti, COALESCE(fc.cant_pasos, 0) AS cant_pasos
                    FROM flujo AS f
                    LEFT JOIN (
                        SELECT idflu, COUNT(*) AS cant_pasos FROM paso GROUP BY idflu
                    ) AS fc ON f.idflu = fc.idflu
                    ORDER BY f.fluacti ASC, f.nomflu ASC";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->execute();
            return $result->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage() . "<br><br>Comuníquese con su administrador.";
            return [];
        }
    }

    /**
     * Retorna los perfiles/roles disponibles
     */
    public function getAllPef(){
        try {
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            
            $sql = "SELECT idper, nomper FROM perfil WHERE idmod IN (8,9) ORDER BY nomper ASC";
            $result = $conexion->prepare($sql);
            $result->execute();
            $res = $result->fetchAll(PDO::FETCH_ASSOC);

            if (empty($res)) {
                $sqlAll = "SELECT idper, nomper FROM perfil ORDER BY nomper ASC";
                $resultAll = $conexion->prepare($sqlAll);
                $resultAll->execute();
                $res = $resultAll->fetchAll(PDO::FETCH_ASSOC);
            }

            return $res;
        } catch (Exception $e) {
            try {
                $modelo2 = new conexion();
                $conexion2 = $modelo2->get_conexion();
                $sqlAll = "SELECT idper, nomper FROM perfil ORDER BY nomper ASC";
                $resultAll = $conexion2->prepare($sqlAll);
                $resultAll->execute();
                return $resultAll->fetchAll(PDO::FETCH_ASSOC);
            } catch (Exception $ex) {
                echo "Error: " . $ex->getMessage() . "<br><br>Comuníquese con su administrador.";
                return [];
            }
        }
    }

    /**
     * Métricas consolidadas para el Dashboard de Pasos
     */
    public function getMetrics($idflu = null) {
        $m = [
            'total_pasos'      => 0,
            'flujos_con_pasos' => 0,
            'perfiles_unicos'  => 0,
            'flujo_top_nombre' => '—',
            'flujo_top_pasos'  => 0,
            'nom_flujo_actual' => '',
            'pasos_flujo_actual' => 0,
        ];
        try {
            $modelo = new conexion();
            $cx = $modelo->get_conexion();

            // Totales globales
            $row = $cx->query("SELECT COUNT(*) AS t FROM paso")->fetch(PDO::FETCH_ASSOC);
            $m['total_pasos'] = $row ? intval($row['t']) : 0;

            $row2 = $cx->query("SELECT COUNT(DISTINCT idflu) AS t FROM paso")->fetch(PDO::FETCH_ASSOC);
            $m['flujos_con_pasos'] = $row2 ? intval($row2['t']) : 0;

            $row3 = $cx->query("SELECT COUNT(DISTINCT idper) AS t FROM paso")->fetch(PDO::FETCH_ASSOC);
            $m['perfiles_unicos'] = $row3 ? intval($row3['t']) : 0;

            // Flujo con más pasos
            $sqlTop = "SELECT f.nomflu, COUNT(p.idpas) AS cnt
                       FROM paso p
                       INNER JOIN flujo f ON p.idflu = f.idflu
                       GROUP BY p.idflu, f.nomflu
                       ORDER BY cnt DESC
                       LIMIT 1";
            $rowTop = $cx->query($sqlTop)->fetch(PDO::FETCH_ASSOC);
            if ($rowTop) {
                $m['flujo_top_nombre'] = $rowTop['nomflu'];
                $m['flujo_top_pasos']  = intval($rowTop['cnt']);
            }

            // Si hay un flujo filtrado, datos específicos de ese flujo
            if ($idflu) {
                $stmtF = $cx->prepare("SELECT nomflu FROM flujo WHERE idflu = :id");
                $stmtF->bindParam(':id', $idflu, PDO::PARAM_INT);
                $stmtF->execute();
                $rowF = $stmtF->fetch(PDO::FETCH_ASSOC);
                $m['nom_flujo_actual'] = $rowF ? $rowF['nomflu'] : '';

                $stmtC = $cx->prepare("SELECT COUNT(*) AS cnt FROM paso WHERE idflu = :id");
                $stmtC->bindParam(':id', $idflu, PDO::PARAM_INT);
                $stmtC->execute();
                $rowC = $stmtC->fetch(PDO::FETCH_ASSOC);
                $m['pasos_flujo_actual'] = $rowC ? intval($rowC['cnt']) : 0;

                // Perfiles únicos involucrados en este flujo específico
                $stmtP = $cx->prepare("SELECT COUNT(DISTINCT idper) AS cnt FROM paso WHERE idflu = :id");
                $stmtP->bindParam(':id', $idflu, PDO::PARAM_INT);
                $stmtP->execute();
                $rowP = $stmtP->fetch(PDO::FETCH_ASSOC);
                $m['perfiles_flujo_actual'] = $rowP ? intval($rowP['cnt']) : 0;
            }

        } catch (Exception $e) {
            // Métricas seguras en contingencia
        }
        return $m;
    }

    /**
     * Obtiene pasos de flujos activos (Estandarización arquitectónica estilo vidgen)
     */
    public function getPasAct($idflu = null) {
        try {
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $hasNum = $this->hasColumn('paso', 'numpas');
            $colNum = $hasNum ? "COALESCE(p.numpas, 1) AS numpas," : "1 AS numpas,";

            $sql = "SELECT p.idpas, p.idflu, " . $colNum . " 
                           p.descpas, p.idper, 
                           f.nomflu, f.fluacti, 
                           COALESCE(j.nomper, 'Sin Perfil') AS nomper,
                           COALESCE(fc.cant_pasos, 0) AS cant_pasos
                    FROM paso AS p 
                    INNER JOIN flujo AS f ON p.idflu = f.idflu 
                    LEFT JOIN perfil AS j ON p.idper = j.idper
                    LEFT JOIN (
                        SELECT idflu, COUNT(*) AS cant_pasos FROM paso GROUP BY idflu
                    ) AS fc ON p.idflu = fc.idflu
                    WHERE f.fluacti = 1 " . ($idflu ? "AND p.idflu = :idflu " : "") . "
                    ORDER BY " . ($hasNum ? "p.numpas ASC, " : "") . "p.idpas ASC";

            $result = $conexion->prepare($sql);
            if ($idflu) {
                $result->bindParam(":idflu", $idflu, PDO::PARAM_INT);
            }
            $result->execute();
            return $result->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Obtiene pasos de flujos inactivos (Estandarización arquitectónica estilo vidgen)
     */
    public function getPasInact($idflu = null) {
        try {
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $hasNum = $this->hasColumn('paso', 'numpas');
            $colNum = $hasNum ? "COALESCE(p.numpas, 1) AS numpas," : "1 AS numpas,";

            $sql = "SELECT p.idpas, p.idflu, " . $colNum . " 
                           p.descpas, p.idper, 
                           f.nomflu, f.fluacti, 
                           COALESCE(j.nomper, 'Sin Perfil') AS nomper,
                           COALESCE(fc.cant_pasos, 0) AS cant_pasos
                    FROM paso AS p 
                    INNER JOIN flujo AS f ON p.idflu = f.idflu 
                    LEFT JOIN perfil AS j ON p.idper = j.idper
                    LEFT JOIN (
                        SELECT idflu, COUNT(*) AS cant_pasos FROM paso GROUP BY idflu
                    ) AS fc ON p.idflu = fc.idflu
                    WHERE (f.fluacti <> 1 OR f.fluacti IS NULL) " . ($idflu ? "AND p.idflu = :idflu " : "") . "
                    ORDER BY " . ($hasNum ? "p.numpas ASC, " : "") . "p.idpas ASC";

            $result = $conexion->prepare($sql);
            if ($idflu) {
                $result->bindParam(":idflu", $idflu, PDO::PARAM_INT);
            }
            $result->execute();
            return $result->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Obtiene totales de pasos para tarjetas KPI (Estandarización vidgen)
     */
    public function getTotales($idflu = null) {
        try {
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $sql = "SELECT 
                        COUNT(*) AS total,
                        COALESCE(SUM(CASE WHEN f.fluacti = 1 THEN 1 ELSE 0 END), 0) AS activos,
                        COALESCE(SUM(CASE WHEN f.fluacti <> 1 OR f.fluacti IS NULL THEN 1 ELSE 0 END), 0) AS inactivos
                    FROM paso AS p
                    INNER JOIN flujo AS f ON p.idflu = f.idflu" . ($idflu ? " WHERE p.idflu = :idflu" : "");
            $result = $conexion->prepare($sql);
            if ($idflu) {
                $result->bindParam(":idflu", $idflu, PDO::PARAM_INT);
            }
            $result->execute();
            return $result->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [['total' => 0, 'activos' => 0, 'inactivos' => 0]];
        }
    }

    /**
     * Método upd estandarizado equivalente a edit()
     */
    public function upd() {
        return $this->edit();
    }
}
?>