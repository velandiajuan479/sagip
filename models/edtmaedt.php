<?php
if (!class_exists("conexion")) {
    require_once(__DIR__ . "/conexion.php");
}

class edtmaedt {
    private $conexion;
    private $idnorad;
    private $convht;
    private $idfic;

    public function setConexion($conn) {
        $this->conexion = $conn;
    }

    public function get_conexion(){
        if ($this->conexion !== null) {
            return $this->conexion;
        }
        if (class_exists("conexion")) {
            $modelo = new conexion();
            $this->conexion = $modelo->get_conexion();
            return $this->conexion;
        }
        include(__DIR__ . "/configuracion.php");
        $this->conexion = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
        return $this->conexion;
    }

    // Getters y Setters
    public function getIdnorad() { return $this->idnorad; }
    public function setIdnorad($idnorad) { $this->idnorad = $idnorad; }
    public function getConvht() { return $this->convht; }
    public function setConvht($convht) { $this->convht = $convht; }
    public function getIdfic() { return $this->idfic; }
    public function setIdfic($idfic) { $this->idfic = $idfic; }

    // Obtener todas las solicitudes EDT para seguimiento y aprobación
    public function getAll() {
        $sql = "SELECT h.*, p.nompro, e.nomemp, v1.nomval AS nom_proesp, 
                v2.nomval AS nom_jornada, u.idusu, u.nomusu 
                FROM hojatra AS h
                LEFT JOIN programa AS p ON h.codpro = p.codpro
                LEFT JOIN empresa AS e ON h.idemp = e.idemp
                LEFT JOIN valor AS v1 ON h.codproesp = v1.idval
                LEFT JOIN valor AS v2 ON h.jornada = v2.idval
                LEFT JOIN hdtxusu AS hu ON h.idnorad = hu.idnorad
                LEFT JOIN usuario AS u ON hu.idusu = u.idusu
                ORDER BY h.idnorad DESC";
        $conexion = $this->get_conexion();
        $result = $conexion->prepare($sql);
        $result->execute();
        return $result->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener una solicitud EDT por idnorad
    public function getOne($idnorad = null) {
        $id = $idnorad !== null ? $idnorad : $this->idnorad;
        $sql = "SELECT h.*, p.nompro, e.nomemp, v1.nomval AS nom_proesp, 
                v2.nomval AS nom_jornada, u.idusu, u.nomusu 
                FROM hojatra AS h
                LEFT JOIN programa AS p ON h.codpro = p.codpro
                LEFT JOIN empresa AS e ON h.idemp = e.idemp
                LEFT JOIN valor AS v1 ON h.codproesp = v1.idval
                LEFT JOIN valor AS v2 ON h.jornada = v2.idval
                LEFT JOIN hdtxusu AS hu ON h.idnorad = hu.idnorad
                LEFT JOIN usuario AS u ON hu.idusu = u.idusu
                WHERE h.idnorad = :idnorad";
        $conexion = $this->get_conexion();
        $result = $conexion->prepare($sql);
        $result->bindParam(":idnorad", $id);
        $result->execute();
        return $result->fetchAll(PDO::FETCH_ASSOC);
    }

    // Actualizar estado general
    public function updateEstado($convht = null, $idnorad = null) {
        $conv = $convht !== null ? $convht : $this->convht;
        $id = $idnorad !== null ? $idnorad : $this->idnorad;
        $sql = "UPDATE hojatra SET convht = :convht WHERE idnorad = :idnorad";
        $conexion = $this->get_conexion();
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(":convht", $conv);
        $stmt->bindParam(":idnorad", $id);
        return $stmt->execute();
    }

    // Actualizar estado y concatenar observaciones
    public function updateEstadoConObs($convht, $idnorad, $obs = null) {
        $sql = "UPDATE hojatra SET convht = :convht";
        if ($obs !== null && trim($obs) !== '') {
            $sql .= ", observaciones = CONCAT(IFNULL(observaciones, ''), '\n[', NOW(), ' - ', :convht, ']: ', :obs)";
        }
        $sql .= " WHERE idnorad = :idnorad";
        $conexion = $this->get_conexion();
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(":convht", $convht);
        $stmt->bindParam(":idnorad", $idnorad);
        if ($obs !== null && trim($obs) !== '') {
            $stmt->bindParam(":obs", $obs);
        }
        return $stmt->execute();
    }

    // Matricular asignando ficha SOFIA Plus
    public function matricular($idnorad = null, $idfic = null) {
        $id = $idnorad !== null ? $idnorad : $this->idnorad;
        $fic = $idfic !== null ? $idfic : $this->idfic;
        $sql = "UPDATE hojatra SET idfic = :idfic, convht = 'Matriculado' WHERE idnorad = :idnorad";
        $conexion = $this->get_conexion();
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(":idfic", $fic);
        $stmt->bindParam(":idnorad", $id);
        return $stmt->execute();
    }

    // Métodos preexistentes preservados para compatibilidad
    public function getUsuario($idusu){
        $sql = "SELECT idusu, nomusu, idper, idcen FROM usuario WHERE idusu = :idusu";
        $conexion = $this->get_conexion();
        $st = $conexion->prepare($sql);
        $st->execute([':idusu' => $idusu]);
        return $st->fetch(PDO::FETCH_ASSOC);
    }

    public function listarPendientes($idcen = null){
        $sql = "SELECT e.idedt, e.titedt, e.descedt, e.fececr, e.estedt, u.nomusu AS instructor
                FROM edt e
                INNER JOIN usuario u ON e.idusu = u.idusu
                WHERE e.estedt IN ('pendiente_coordinador','pendiente_administracion')";
        $params = [];
        if ($idcen !== null){
            $sql .= " AND e.idcen = :idcen";
            $params[':idcen'] = $idcen;
        }
        $sql .= " ORDER BY e.fececr ASC";
        $conexion = $this->get_conexion();
        $st = $conexion->prepare($sql);
        $st->execute($params);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    public function historial($idedt){
        $sql = "SELECT a.*, u.nomusu FROM edtaprob a 
                LEFT JOIN usuario u ON a.idusu = u.idusu 
                WHERE a.idedt = :idedt ORDER BY a.idaprob ASC";
        $conexion = $this->get_conexion();
        $st = $conexion->prepare($sql);
        $st->execute([':idedt' => $idedt]);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    public function decidir($idedt, $rol, $idusu, $decision, $comentario){
        $conexion = $this->get_conexion();
        $conexion->beginTransaction();
        try {
            $conexion->prepare("UPDATE edtaprob SET idusu=:idusu, decaprob=:dec, comaprob=:com, fecdec=NOW() 
                                 WHERE idedt=:idedt AND rolaprob=:rol AND decaprob='pendiente'")
                      ->execute([':idusu'=>$idusu, ':dec'=>$decision, ':com'=>$comentario, ':idedt'=>$idedt, ':rol'=>$rol]);

            if ($decision == 'rechazado'){
                $conexion->prepare("UPDATE edt SET estedt='rechazado' WHERE idedt=:idedt")->execute([':idedt'=>$idedt]);
            } else {
                if ($rol == 'coordinador'){
                    $conexion->prepare("UPDATE edt SET estedt='pendiente_administracion' WHERE idedt=:idedt")->execute([':idedt'=>$idedt]);
                    $conexion->prepare("INSERT INTO edtaprob (idedt, rolaprob, decaprob) VALUES (:idedt, 'administracion', 'pendiente')")->execute([':idedt'=>$idedt]);
                } else if ($rol == 'administracion'){
                    $conexion->prepare("UPDATE edt SET estedt='aprobado' WHERE idedt=:idedt")->execute([':idedt'=>$idedt]);
                }
            }
            $conexion->commit();
            return true;
        } catch (Exception $e){
            $conexion->rollBack();
            return false;
        }
    }
}
?>