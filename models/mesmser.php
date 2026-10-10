<?php
require_once "conexion.php";

class Mesmser {
    private $idsop;
    private $idusu;
    private $idusu2;
    private $falrep;
    private $carper;
    private $fecser;
    private $nomper;
    private $desser;
    private $evisop;

    public function getIdsop(){ return $this->idsop; }
    public function getIdusu(){ return $this->idusu; }
    public function getIdusu2(){ return $this->idusu2; }
    public function getFalrep(){ return $this->falrep; }
    public function getCarper(){ return $this->carper; }
    public function getFecser(){ return $this->fecser; }
    public function getNomper(){ return $this->nomper; }
    public function getDesser(){ return $this->desser; }
    public function getEvisop(){ return $this->evisop; }

    public function setIdsop($idsop){ $this->idsop = $idsop; }
    public function setIdusu($idusu){ $this->idusu = $idusu; }
    public function setIdusu2($idusu2){ $this->idusu2 = $idusu2; }
    public function setFalrep($falrep){ $this->falrep = $falrep; }
    public function setCarper($carper){ $this->carper = $carper; }
    public function setFecser($fecser){ $this->fecser = $fecser; }
    public function setNomper($nomper){ $this->nomper = $nomper; }
    public function setDesser($desser){ $this->desser = $desser; }
    public function setEvisop($evisop){ $this->evisop = $evisop; }

    public function getAll($idper = null, $idusu = null){
        $sql = "SELECT s.idsop, s.idusu, s.idusu2, u.nomusu, u2.nomusu AS nom_tecnico, s.falrep, v1.nomval AS nom_falrep, s.carper, v2.nomval AS nom_carper, s.fecserini, s.fecserfin, COALESCE(NULLIF(s.nomper, ''), u.nomusu) AS nomper, s.desser, s.evisop,
                ds.detest AS ultimo_estado_id, v3.nomval AS nom_ultimo_estado
                FROM soporte s
                LEFT JOIN usuario u ON s.idusu = u.idusu
                LEFT JOIN usuario u2 ON s.idusu2 = u2.idusu
                LEFT JOIN valor v1 ON s.falrep = v1.idval
                LEFT JOIN valor v2 ON s.carper = v2.idval
                LEFT JOIN (
                    SELECT d1.idsop, d1.detest
                    FROM detalle_soporte d1
                    INNER JOIN (
                        SELECT idsop, MAX(fecseg) AS max_fecha
                        FROM detalle_soporte
                        GROUP BY idsop
                    ) d2 ON d1.idsop = d2.idsop AND d1.fecseg = d2.max_fecha
                ) ds ON s.idsop = ds.idsop
                LEFT JOIN valor v3 ON ds.detest = v3.idval";

        $params = [];
        if ($this->esPerfilSoporte($idper)) {
            $sql .= " WHERE s.idusu = :idusu";
            $params[':idusu'] = $idusu;
        }

        $sql .= " ORDER BY s.idsop DESC";

        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        foreach ($params as $key => $value) {
            $result->bindValue($key, $value);
        }
        $result->execute();
        return $result->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getOne(){
        $sql = "SELECT s.idsop, s.idusu, u.nomusu, u2.nomusu AS nom_tecnico, s.idusu2, s.falrep, v1.nomval AS nom_falrep, s.carper, v2.nomval AS nom_carper,
                s.fecserini, s.fecserfin, COALESCE(NULLIF(s.nomper, ''), u.nomusu) AS nomper, s.desser, s.evisop,
                ds.detest AS ultimo_estado_id, v3.nomval AS nom_ultimo_estado
                FROM soporte s
                INNER JOIN usuario u ON s.idusu = u.idusu
                LEFT JOIN usuario u2 ON s.idusu2 = u2.idusu
                LEFT JOIN valor v1 ON s.falrep = v1.idval
                LEFT JOIN valor v2 ON s.carper = v2.idval
                LEFT JOIN (
                    SELECT d1.idsop, d1.detest
                    FROM detalle_soporte d1
                    INNER JOIN (
                        SELECT idsop, MAX(fecseg) AS max_fecha
                        FROM detalle_soporte
                        GROUP BY idsop
                    ) d2 ON d1.idsop = d2.idsop AND d1.fecseg = d2.max_fecha
                ) ds ON s.idsop = ds.idsop
                LEFT JOIN valor v3 ON ds.detest = v3.idval
                WHERE s.idsop = :idsop LIMIT 1";

        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->bindValue(':idsop', $this->getIdsop());
        $result->execute();
        return $result->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUsuarioActual($idusu){
        $sql = "SELECT u.idusu, u.nomusu, u.idper, v.idval AS idcargo, v.nomval AS nomcargo
                FROM usuario u
                LEFT JOIN valor v ON v.iddom = 7 AND v.idval = u.idper
                WHERE u.idusu = :idusu LIMIT 1";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->bindValue(':idusu', $idusu);
        $result->execute();
        return $result->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    public function getCargoActualPorUsuario($idusu){
        $usuario = $this->getUsuarioActual($idusu);
        return isset($usuario['idcargo']) ? $usuario['idcargo'] : null;
    }

    public function getIdsPerfilesSoporte(){
        $sql = "SELECT DISTINCT p.idper
                FROM perfil p
                WHERE LOWER(TRIM(p.nomper)) LIKE '%soporte%'
                ORDER BY p.idper ASC";

        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->execute();

        $ids = [];
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            if (!empty($row['idper'])) {
                $ids[] = (int) $row['idper'];
            }
        }

        return $ids;
    }

    public function esPerfilSoporte($idper){
        if ($idper === null || $idper === '') {
            return false;
        }

        $idsPerfilesSoporte = $this->getIdsPerfilesSoporte();
        return !empty($idsPerfilesSoporte) && in_array((int) $idper, $idsPerfilesSoporte, true);
    }

    public function getRandomTecnicoId($idusuActual = null){
        $idsPerfilesSoporte = $this->getIdsPerfilesSoporte();

        if (empty($idsPerfilesSoporte)) {
            return null;
        }

        $placeholders = implode(',', array_fill(0, count($idsPerfilesSoporte), '?'));
        $sql = "SELECT u.idusu
                FROM usuario u
                INNER JOIN perfil p ON p.idper = u.idper
                WHERE u.actusu = 1 AND u.idper IN ($placeholders)";
        $params = $idsPerfilesSoporte;

        if ($idusuActual) {
            $sql .= " AND u.idusu != ?";
            $params[] = $idusuActual;
        }

        $sql .= " ORDER BY RAND() LIMIT 1";

        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);

        foreach ($params as $index => $value) {
            $result->bindValue($index + 1, $value, PDO::PARAM_INT);
        }

        $result->execute();
        $row = $result->fetch(PDO::FETCH_ASSOC);
        return $row['idusu'] ?? null;
    }

    public function getSelSop($idcen = null){
        $idsPerfilesSoporte = $this->getIdsPerfilesSoporte();

        if (empty($idsPerfilesSoporte)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($idsPerfilesSoporte), '?'));
        $sql = "SELECT u.idusu, u.nomusu
                FROM usuario u
                WHERE u.idper IN ($placeholders) AND u.actusu = 1";
        $params = $idsPerfilesSoporte;

        if ($idcen !== null) {
            $sql .= " AND u.idcen = ?";
            $params[] = $idcen;
        }

        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);

        foreach ($params as $index => $value) {
            $result->bindValue($index + 1, $value, PDO::PARAM_INT);
        }

        $result->execute();
        return $result->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCargoPer(){
        $sql = "SELECT idval, nomval, act FROM valor WHERE iddom = 7 ORDER BY nomval ASC";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->execute();
        return $result->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getFallaRep(){
        $sql = "SELECT idval, nomval, act FROM valor WHERE iddom = 6 ORDER BY nomval ASC";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->execute();
        return $result->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarUsuarios($term){
        try {
            $sql = "SELECT u.idusu, u.nomusu, u.idper, COALESCE(v.idval, u.idper) AS idcargo
                    FROM usuario u
                    LEFT JOIN valor v ON v.iddom = 7 AND (v.idval = u.idper OR v.idval = u.idcargo)
                    WHERE u.nomusu LIKE :term AND u.actusu = 1
                    GROUP BY u.idusu
                    ORDER BY u.nomusu ASC LIMIT 10";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $term = '%' . trim((string) $term) . '%';
            $result->bindValue(':term', $term, PDO::PARAM_STR);
            $result->execute();
            return $result->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error en buscarUsuarios: ' . $e->getMessage());
            return [];
        }
    }

    public function getNextIdsop(){
        $sql = "SELECT COALESCE(MAX(idsop), 0) + 1 AS next_id FROM soporte";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->execute();
        $row = $result->fetch(PDO::FETCH_ASSOC);
        return isset($row['next_id']) ? (int)$row['next_id'] : 1;
    }

    public function getAmbientes(){
        try {
            $sql = "SELECT idaul, nomaul, piso, bloqau FROM aula ORDER BY nomaul ASC";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->execute();
            return $result->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            return [];
        }
    }

    public function save(){
        $nextId = $this->getNextIdsop();
        $sql = "INSERT INTO soporte (idsop, idusu, idusu2, falrep, carper, fecserini, nomper, desser, evisop)
                VALUES (:idsop, :idusu, :idusu2, :falrep, :carper, :fecserini, :nomper, :desser, :evisop)";

        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);

        $result->bindValue(':idsop', $nextId, PDO::PARAM_INT);
        $result->bindValue(':idusu', $this->getIdusu());
        $result->bindValue(':idusu2', $this->getIdusu2());
        $result->bindValue(':falrep', $this->getFalrep());
        $result->bindValue(':carper', $this->getCarper());
        $result->bindValue(':fecserini', $this->getFecser() ?: date('Y-m-d H:i:s'));
        $result->bindValue(':nomper', $this->getNomper());
        $result->bindValue(':desser', $this->getDesser());
        $result->bindValue(':evisop', $this->getEvisop());
        $result->execute();

        return $nextId;
    }

    public function updateEvisop($evisop = null){
        if ($evisop !== null) {
            $this->setEvisop($evisop);
        }
        $sql = "UPDATE soporte SET evisop = :evisop WHERE idsop = :idsop";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->bindValue(':evisop', $this->getEvisop());
        $result->bindValue(':idsop', $this->getIdsop());
        $result->execute();
    }

    public function edit(){
        $sql = "UPDATE soporte SET idusu = :idusu, idusu2 = :idusu2, falrep = :falrep, carper = :carper, fecserini = :fecserini,
                nomper = :nomper, desser = :desser, evisop = :evisop WHERE idsop = :idsop";

        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->bindValue(':idsop', $this->getIdsop());
        $result->bindValue(':idusu', $this->getIdusu());
        $result->bindValue(':idusu2', $this->getIdusu2());
        $result->bindValue(':falrep', $this->getFalrep());
        $result->bindValue(':carper', $this->getCarper());
        $result->bindValue(':fecserini', $this->getFecser() ?: date('Y-m-d H:i:s'));
        $result->bindValue(':nomper', $this->getNomper());
        $result->bindValue(':desser', $this->getDesser());
        $result->bindValue(':evisop', $this->getEvisop());
        $result->execute();
    }

    public function guardarEvidenciasDesdeUpload($idsop, $files){
        if (!$idsop || empty($files) || !isset($files['name'])) {
            return [];
        }

        $resultados = [];
        $filesList = [];

        if (is_array($files['name'])) {
            $cantidad = count($files['name']);
            for ($i = 0; $i < $cantidad; $i++) {
                $filesList[] = [
                    'name' => $files['name'][$i] ?? '',
                    'type' => $files['type'][$i] ?? '',
                    'tmp_name' => $files['tmp_name'][$i] ?? '',
                    'error' => $files['error'][$i] ?? 0,
                    'size' => $files['size'][$i] ?? 0,
                ];
            }
        } elseif (is_string($files['name']) && !empty($files['name'])) {
            $filesList[] = [
                'name' => $files['name'],
                'type' => $files['type'] ?? '',
                'tmp_name' => $files['tmp_name'] ?? '',
                'error' => $files['error'] ?? 0,
                'size' => $files['size'] ?? 0,
            ];
        }

        foreach ($filesList as $archivo) {
            if (empty($archivo['name']) || empty($archivo['tmp_name'])) {
                continue;
            }

            if ($archivo['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($archivo['tmp_name'])) {
                continue;
            }

            $rutaBase = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'fevi';
            if (!is_dir($rutaBase)) {
                mkdir($rutaBase, 0777, true);
            }

            $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
            $nombreArchivo = time() . '_' . uniqid('', true) . ($extension ? '.' . $extension : '');
            $rutaRelativa = 'img/fevi/' . $nombreArchivo;
            $rutaDestino = dirname(__DIR__) . DIRECTORY_SEPARATOR . $rutaRelativa;

            if (move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
                $this->guardarEvidencia($idsop, $rutaRelativa, $archivo['name'], $archivo['type'], (int) $archivo['size']);
                $resultados[] = $rutaRelativa;
            }
        }

        return $resultados;
    }

    public function guardarEvidencia($idsop, $ruta, $nombreArchivo = null, $tipo = null, $peso = 0){
        try {
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();

            $conexion->exec("CREATE TABLE IF NOT EXISTS soporte_evidencia (
                idevi BIGINT(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                idsop BIGINT(20) NOT NULL,
                ruta VARCHAR(255) NOT NULL,
                nombre_archivo VARCHAR(255) DEFAULT NULL,
                tipo VARCHAR(100) DEFAULT NULL,
                peso BIGINT(20) DEFAULT 0,
                fecsub DATETIME DEFAULT CURRENT_TIMESTAMP,
                KEY idx_idsop (idsop)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");

            $sql = "INSERT INTO soporte_evidencia (idsop, ruta, nombre_archivo, tipo, peso, fecsub)
                    VALUES (:idsop, :ruta, :nombre_archivo, :tipo, :peso, NOW())";
            $result = $conexion->prepare($sql);
            $result->bindValue(':idsop', $idsop);
            $result->bindValue(':ruta', $ruta);
            $result->bindValue(':nombre_archivo', $nombreArchivo);
            $result->bindValue(':tipo', $tipo);
            $result->bindValue(':peso', $peso);
            $result->execute();
            return $conexion->lastInsertId();
        } catch (Exception $e) {
            return null;
        }
    }

    public function getEvidenciasPorSoporte($idsop){
        try {
            $sql = "SELECT idevi, idsop, ruta, nombre_archivo, tipo, peso, fecsub
                    FROM soporte_evidencia
                    WHERE idsop = :idsop
                    ORDER BY idevi DESC";

            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->bindValue(':idsop', $idsop);
            $result->execute();
            return $result->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    public function del(){
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $idsop = $this->getIdsop();

        try {
            $conexion->beginTransaction();

            try {
                $detalle = $conexion->prepare("DELETE FROM detalle_soporte WHERE idsop = :idsop");
                $detalle->execute([':idsop' => $idsop]);
            } catch (Exception $e) {}

            try {
                $evidencia = $conexion->prepare("DELETE FROM soporte_evidencia WHERE idsop = :idsop");
                $evidencia->execute([':idsop' => $idsop]);
            } catch (Exception $e) {}

            $soporte = $conexion->prepare("DELETE FROM soporte WHERE idsop = :idsop");
            $soporte->execute([':idsop' => $idsop]);

            $conexion->commit();
            return true;
        } catch (Throwable $e) {
            if ($conexion->inTransaction()) {
                $conexion->rollBack();
            }
            throw $e;
        }
    }
}

if (!class_exists('Msersop', false)) {
    class_alias('Mesmser', 'Msersop');
}
?>