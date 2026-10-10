<?php
class Mflu {
    
    private $idflu;
    private $nomflu;
    private $descflu;
    private $fluacti;
    private $created_at;
    private $updated_at;

    // Getters
    function getIdflu() {
        return $this->idflu;
    }
    function getNomflu() {
        return $this->nomflu;
    }
    function getDescflu() {
        return $this->descflu;
    }
    function getFluacti() {
        return $this->fluacti;
    }
    function getCreatedAt() {
        return $this->created_at;
    }
    function getUpdatedAt() {
        return $this->updated_at;
    }

    // Setters
    function setIdflu($idflu) {
        $this->idflu = $idflu;
    }
    function setNomflu($nomflu) {
        $this->nomflu = $nomflu;
    }
    function setDescflu($descflu) {
        $this->descflu = $descflu;
    }
    function setFluacti($fluacti) {
        $this->fluacti = $fluacti;
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
     * Obtiene todos los flujos junto con el conteo de pasos asociados
     */
    public function getAll() {
        try {
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();

            $hasDesc = $this->hasColumn('flujo', 'descflu');
            $colDesc = $hasDesc ? "COALESCE(f.descflu, '') AS descflu," : "'' AS descflu,";

            // Se utiliza una subconsulta agrupada para total_pasos para evitar incompatibilidades con ONLY_FULL_GROUP_BY
            $sql = "SELECT f.idflu, f.nomflu, " . $colDesc . " f.fluacti, 
                           COALESCE(p.total_pasos, 0) AS total_pasos
                    FROM flujo AS f
                    LEFT JOIN (
                        SELECT idflu, COUNT(*) AS total_pasos 
                        FROM paso 
                        GROUP BY idflu
                    ) AS p ON f.idflu = p.idflu
                    ORDER BY f.idflu DESC";

            $result = $conexion->prepare($sql);
            $result->execute();
            return $result->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            try {
                $modelo2 = new conexion();
                $conexion2 = $modelo2->get_conexion();
                $sqlFallback = "SELECT idflu, nomflu, '' AS descflu, fluacti, 0 AS total_pasos FROM flujo ORDER BY idflu DESC";
                $result = $conexion2->prepare($sqlFallback);
                $result->execute();
                return $result->fetchAll(PDO::FETCH_ASSOC);
            } catch (Exception $ex) {
                echo "Error: " . $ex->getMessage() . "<br><br>Comuníquese con su administrador.";
                return [];
            }
        }
    }

    /**
     * Obtiene únicamente los flujos activos (para selectores y formularios)
     */
    public function getAllActive() {
        try {
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();

            $hasDesc = $this->hasColumn('flujo', 'descflu');
            $colDesc = $hasDesc ? "COALESCE(descflu, '') AS descflu," : "'' AS descflu,";

            $sql = "SELECT idflu, nomflu, " . $colDesc . " fluacti 
                    FROM flujo 
                    WHERE fluacti = 1 
                    ORDER BY nomflu ASC";
            $result = $conexion->prepare($sql);
            $result->execute();
            return $result->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
            return [];
        }
    }

    /**
     * Obtiene el detalle de un flujo por su ID
     */
    public function getOne() {
        try {
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();

            $hasDesc = $this->hasColumn('flujo', 'descflu');
            $colDesc = $hasDesc ? "COALESCE(f.descflu, '') AS descflu," : "'' AS descflu,";

            $sql = "SELECT f.idflu, f.nomflu, " . $colDesc . " f.fluacti, 
                           COALESCE(p.total_pasos, 0) AS total_pasos
                    FROM flujo AS f
                    LEFT JOIN (
                        SELECT idflu, COUNT(*) AS total_pasos 
                        FROM paso 
                        GROUP BY idflu
                    ) AS p ON f.idflu = p.idflu
                    WHERE f.idflu = :idflu";

            $result = $conexion->prepare($sql);
            $idflu = $this->getIdflu();
            $result->bindParam(":idflu", $idflu, PDO::PARAM_INT);
            $result->execute();
            return $result->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage() . "<br><br>Comuníquese con su administrador.";
            return [];
        }
    }

    /**
     * Registra un nuevo flujo asegurando generación adecuada de ID si la tabla no es AUTO_INCREMENT
     */
    public function save() {
        try {
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();

            $hasDesc = $this->hasColumn('flujo', 'descflu');
            $nomflu = $this->getNomflu();
            $descflu = $this->getDescflu();
            $fluacti = $this->getFluacti() ? $this->getFluacti() : 1;

            // Obtener siguiente ID correlativo para evitar colisiones con idflu = 0
            $stmtMax = $conexion->query("SELECT COALESCE(MAX(idflu), 0) + 1 AS next_id FROM flujo");
            $rowMax = $stmtMax ? $stmtMax->fetch(PDO::FETCH_ASSOC) : null;
            $nextId = ($rowMax && isset($rowMax['next_id'])) ? intval($rowMax['next_id']) : 1;
            if ($nextId <= 0) { $nextId = 1; }

            // Intento 1: Con id explícito (resuelve tablas sin AUTO_INCREMENT)
            try {
                if ($hasDesc) {
                    $sql = "INSERT INTO flujo (idflu, nomflu, descflu, fluacti) VALUES (:idflu, :nomflu, :descflu, :fluacti)";
                    $result = $conexion->prepare($sql);
                    $result->bindParam(":idflu", $nextId, PDO::PARAM_INT);
                    $result->bindParam(":nomflu", $nomflu);
                    $result->bindParam(":descflu", $descflu);
                    $result->bindParam(":fluacti", $fluacti, PDO::PARAM_INT);
                } else {
                    $sql = "INSERT INTO flujo (idflu, nomflu, fluacti) VALUES (:idflu, :nomflu, :fluacti)";
                    $result = $conexion->prepare($sql);
                    $result->bindParam(":idflu", $nextId, PDO::PARAM_INT);
                    $result->bindParam(":nomflu", $nomflu);
                    $result->bindParam(":fluacti", $fluacti, PDO::PARAM_INT);
                }
                $result->execute();
                return $nextId;
            } catch (Exception $e1) {
                // Intento 2: Inserción estándar sin id si el motor maneja AUTO_INCREMENT estricto
                if ($hasDesc) {
                    $sql = "INSERT INTO flujo (nomflu, descflu, fluacti) VALUES (:nomflu, :descflu, :fluacti)";
                    $result = $conexion->prepare($sql);
                    $result->bindParam(":nomflu", $nomflu);
                    $result->bindParam(":descflu", $descflu);
                    $result->bindParam(":fluacti", $fluacti, PDO::PARAM_INT);
                } else {
                    $sql = "INSERT INTO flujo (nomflu, fluacti) VALUES (:nomflu, :fluacti)";
                    $result = $conexion->prepare($sql);
                    $result->bindParam(":nomflu", $nomflu);
                    $result->bindParam(":fluacti", $fluacti, PDO::PARAM_INT);
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
     * Modifica los datos de un flujo existente
     */
    public function edit() {
        try {
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();

            $hasDesc = $this->hasColumn('flujo', 'descflu');
            $idflu = $this->getIdflu();
            $nomflu = $this->getNomflu();
            $descflu = $this->getDescflu();
            $fluacti = $this->getFluacti();

            if ($hasDesc) {
                $sql = "UPDATE flujo SET nomflu = :nomflu, descflu = :descflu, fluacti = :fluacti WHERE idflu = :idflu";
                $result = $conexion->prepare($sql);
                $result->bindParam(":descflu", $descflu);
            } else {
                $sql = "UPDATE flujo SET nomflu = :nomflu, fluacti = :fluacti WHERE idflu = :idflu";
                $result = $conexion->prepare($sql);
            }

            $result->bindParam(":idflu", $idflu, PDO::PARAM_INT);
            $result->bindParam(":nomflu", $nomflu);
            $result->bindParam(":fluacti", $fluacti, PDO::PARAM_INT);
            return $result->execute();
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage() . "<br><br>Comuníquese con su administrador.";
            return false;
        }
    }

    /**
     * Modifica únicamente el estado (Activo / Inactivo)
     */
    public function editAct(){
        try {
            $sql = "UPDATE flujo SET fluacti = :fluacti WHERE idflu = :idflu";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $idflu = $this->getIdflu();
            $fluacti = $this->getFluacti();
            $result->bindParam(":idflu", $idflu, PDO::PARAM_INT);
            $result->bindParam(":fluacti", $fluacti, PDO::PARAM_INT);
            return $result->execute();
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage() . "<br><br>Comuníquese con su administrador.";
        }
    }

    /**
     * Eliminación de flujo con borrado en cascada de sus pasos asociados (DML estándar)
     */
    public function del() {
        try {
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $idflu = $this->getIdflu();

            $conexion->beginTransaction();

            // 1. Eliminar pasos asociados al flujo para evitar registros huérfanos
            $sqlPas = "DELETE FROM paso WHERE idflu = :idflu";
            $resPas = $conexion->prepare($sqlPas);
            $resPas->bindParam(":idflu", $idflu, PDO::PARAM_INT);
            $resPas->execute();

            // 2. Eliminar el flujo
            $sqlFlu = "DELETE FROM flujo WHERE idflu = :idflu";
            $resFlu = $conexion->prepare($sqlFlu);
            $resFlu->bindParam(":idflu", $idflu, PDO::PARAM_INT);
            $resFlu->execute();

            $conexion->commit();
            return true;
        } catch (Exception $e) {
            if (isset($conexion) && $conexion->inTransaction()) {
                $conexion->rollBack();
            }
            echo "Error: " . $e->getMessage() . "<br><br>Comuníquese con su administrador.";
            return false;
        }
    }

    /**
     * Métricas consolidadas para el Dashboard Integrado (Flujos y Pasos)
     */
    public function getMetrics() {
        $metrics = [
            'total_flujos' => 0,
            'flujos_activos' => 0,
            'flujos_inactivos' => 0,
            'total_pasos' => 0,
            'promedio_pasos' => 0,
            'top_flujos' => [],
            'recientes' => []
        ];

        try {
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();

            // Conteo de flujos y estado
            $sqlF = "SELECT 
                        COUNT(*) AS total,
                        SUM(CASE WHEN fluacti = 1 THEN 1 ELSE 0 END) AS activos,
                        SUM(CASE WHEN fluacti != 1 THEN 1 ELSE 0 END) AS inactivos
                     FROM flujo";
            $stmtF = $conexion->query($sqlF);
            if ($rowF = $stmtF->fetch(PDO::FETCH_ASSOC)) {
                $metrics['total_flujos'] = intval($rowF['total']);
                $metrics['flujos_activos'] = intval($rowF['activos']);
                $metrics['flujos_inactivos'] = intval($rowF['inactivos']);
            }

            // Conteo de pasos totales
            $sqlP = "SELECT COUNT(*) AS total_pasos FROM paso";
            $stmtP = $conexion->query($sqlP);
            if ($rowP = $stmtP->fetch(PDO::FETCH_ASSOC)) {
                $metrics['total_pasos'] = intval($rowP['total_pasos']);
            }

            // Promedio de pasos por flujo
            if ($metrics['total_flujos'] > 0) {
                $metrics['promedio_pasos'] = round($metrics['total_pasos'] / $metrics['total_flujos'], 1);
            }

            // Flujos con conteo de pasos para gráfica y resumen (sin límite rígido)
            $sqlTop = "SELECT f.idflu, f.nomflu, COALESCE(p.cant_pasos, 0) AS cant_pasos
                       FROM flujo AS f
                       LEFT JOIN (
                           SELECT idflu, COUNT(*) AS cant_pasos FROM paso GROUP BY idflu
                       ) AS p ON f.idflu = p.idflu
                       ORDER BY cant_pasos DESC, f.nomflu ASC";
            $stmtTop = $conexion->query($sqlTop);
            $metrics['top_flujos'] = $stmtTop ? $stmtTop->fetchAll(PDO::FETCH_ASSOC) : [];

            // Flujos recientes con conteo de pasos (últimos 6 creados)
            $sqlRec = "SELECT f.idflu, f.nomflu, f.fluacti, COALESCE(p.cant_pasos, 0) AS cant_pasos
                       FROM flujo AS f
                       LEFT JOIN (
                           SELECT idflu, COUNT(*) AS cant_pasos FROM paso GROUP BY idflu
                       ) AS p ON f.idflu = p.idflu
                       ORDER BY f.idflu DESC
                       LIMIT 6";
            $stmtRec = $conexion->query($sqlRec);
            $metrics['recientes'] = $stmtRec ? $stmtRec->fetchAll(PDO::FETCH_ASSOC) : [];

        } catch (Exception $e) {
            // Valores seguros en caso de contingencia
        }

        return $metrics;
    }

    /**
     * Obtiene flujos activos (Estandarización arquitectónica estilo vidgen)
     */
    public function getFluAct() {
        try {
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $hasDesc = $this->hasColumn('flujo', 'descflu');
            $colDesc = $hasDesc ? "COALESCE(f.descflu, '') AS descflu," : "'' AS descflu,";

            $sql = "SELECT f.idflu, f.nomflu, " . $colDesc . " f.fluacti, 
                           COALESCE(p.total_pasos, 0) AS total_pasos
                    FROM flujo AS f
                    LEFT JOIN (
                        SELECT idflu, COUNT(*) AS total_pasos 
                        FROM paso 
                        GROUP BY idflu
                    ) AS p ON f.idflu = p.idflu
                    WHERE f.fluacti = 1
                    ORDER BY f.idflu DESC";

            $result = $conexion->prepare($sql);
            $result->execute();
            return $result->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Obtiene flujos inactivos (Estandarización arquitectónica estilo vidgen)
     */
    public function getFluInact() {
        try {
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $hasDesc = $this->hasColumn('flujo', 'descflu');
            $colDesc = $hasDesc ? "COALESCE(f.descflu, '') AS descflu," : "'' AS descflu,";

            $sql = "SELECT f.idflu, f.nomflu, " . $colDesc . " f.fluacti, 
                           COALESCE(p.total_pasos, 0) AS total_pasos
                    FROM flujo AS f
                    LEFT JOIN (
                        SELECT idflu, COUNT(*) AS total_pasos 
                        FROM paso 
                        GROUP BY idflu
                    ) AS p ON f.idflu = p.idflu
                    WHERE f.fluacti <> 1 OR f.fluacti IS NULL
                    ORDER BY f.idflu DESC";

            $result = $conexion->prepare($sql);
            $result->execute();
            return $result->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Obtiene totales de flujos para tarjetas KPI (Estandarización vidgen)
     */
    public function getTotales() {
        try {
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $sql = "SELECT 
                        COUNT(*) AS total,
                        COALESCE(SUM(CASE WHEN fluacti = 1 THEN 1 ELSE 0 END), 0) AS activos,
                        COALESCE(SUM(CASE WHEN fluacti <> 1 OR fluacti IS NULL THEN 1 ELSE 0 END), 0) AS inactivos
                    FROM flujo";
            $result = $conexion->prepare($sql);
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

    /**
     * Método updAct estandarizado equivalente a editAct()
     */
    public function updAct($fluacti) {
        $this->setFluacti($fluacti);
        return $this->editAct();
    }
}
?>



