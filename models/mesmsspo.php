<?php
require_once __DIR__ . '/conexion.php';

class Mesmsspo
{
    private $iddet;
    private $idsop;
    private $idusu;
    private $detest;
    private $detcom;
    private $detevi;

    public function getIddet() { return $this->iddet; }
    public function getIdsop() { return $this->idsop; }
    public function getIdusu() { return $this->idusu; }
    public function getDetest() { return $this->detest; }
    public function getDetcom() { return $this->detcom; }
    public function getDetevi() { return $this->detevi; }

    public function setIddet($iddet) { $this->iddet = $iddet; }
    public function setIdsop($idsop) { $this->idsop = $idsop; }
    public function setIdusu($idusu) { $this->idusu = $idusu; }
    public function setDetest($detest) { $this->detest = $detest; }
    public function setDetcom($detcom) { $this->detcom = $detcom; }
    public function setDetevi($detevi) { $this->detevi = $detevi; }

    private function conexion()
    {
        $modelo = new conexion();
        return $modelo->get_conexion();
    }

    public function getAll()
    {
        $sql = "SELECT 
                    d.iddet, 
                    d.idsop, 
                    d.idusu, 
                    u.nomusu, 
                    d.detevi, 
                    d.detcom, 
                    d.fecseg, 
                    d.detest, 
                    v3.nomval AS nom_detest 
                FROM detalle_soporte AS d
                INNER JOIN usuario AS u ON d.idusu = u.idusu 
                INNER JOIN valor AS v3 ON d.detest = v3.idval 
                WHERE d.idsop = :idsop
                ORDER BY d.iddet ASC";

        $stmt = $this->conexion()->prepare($sql);
        $idsop = (int) $this->getIdsop();
        $stmt->bindParam(":idsop", $idsop, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getOne()
    {
        $sql = "SELECT 
                    s.idsop, 
                    s.idusu, 
                    s.idusu2, 
                    u.nomusu AS nom_solicitante, 
                    u2.nomusu AS nom_tecnico, 
                    s.falrep, 
                    v1.nomval AS nom_falrep, 
                    s.carper, 
                    v2.nomval AS nom_carper, 
                    s.fecserini, 
                    s.fecserfin, 
                    s.nomper, 
                    s.desser, 
                    s.evisop,
                    ds.detest AS ultimo_estado_id,
                    v3.nomval AS nom_ultimo_estado
                FROM soporte AS s 
                LEFT JOIN usuario AS u ON s.idusu = u.idusu 
                LEFT JOIN usuario AS u2 ON s.idusu2 = u2.idusu 
                LEFT JOIN valor AS v1 ON s.falrep = v1.idval 
                LEFT JOIN valor AS v2 ON s.carper = v2.idval
                LEFT JOIN (
                    SELECT d1.idsop, d1.detest
                    FROM detalle_soporte AS d1
                    INNER JOIN (
                        SELECT idsop, MAX(iddet) AS max_iddet
                        FROM detalle_soporte
                        GROUP BY idsop
                    ) AS d2 ON d1.iddet = d2.max_iddet
                ) AS ds ON s.idsop = ds.idsop
                LEFT JOIN valor AS v3 ON ds.detest = v3.idval
                WHERE s.idsop = :idsop";

        $stmt = $this->conexion()->prepare($sql);
        $idsop = (int) $this->getIdsop();
        $stmt->bindParam(":idsop", $idsop, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function asignarTecnico($idusu2)
    {
        $sql = "UPDATE soporte SET idusu2 = :idusu2 WHERE idsop = :idsop";
        $stmt = $this->conexion()->prepare($sql);
        $idsop = (int) $this->getIdsop();
        $idusu2Val = $idusu2 ? (int) $idusu2 : null;
        $stmt->bindValue(":idusu2", $idusu2Val, $idusu2Val ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $stmt->bindValue(":idsop", $idsop, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function getSelSop($idcen = null)
    {
        $sql = "SELECT idusu, nomusu FROM usuario WHERE idper IN (27, 38)";
        if ($idcen !== null) {
            $sql .= " AND idcen = :idcen";
        }
        $sql .= " ORDER BY nomusu ASC";

        $stmt = $this->conexion()->prepare($sql);
        if ($idcen !== null) {
            $stmt->bindValue(":idcen", (int) $idcen, PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function save()
    {
        $conexion = $this->conexion();

        // Obtener el siguiente iddet para evitar duplicados en la clave primaria
        $stmtId = $conexion->query("SELECT COALESCE(MAX(iddet), 0) + 1 AS next_id FROM detalle_soporte");
        $rowId = $stmtId->fetch(PDO::FETCH_ASSOC);
        $nextIddet = (int) $rowId['next_id'];

        $sql = "INSERT INTO detalle_soporte (iddet, idsop, idusu, detest, detcom, detevi, fecseg) 
                VALUES (:iddet, :idsop, :idusu, :detest, :detcom, :detevi, NOW())";
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(":iddet", $nextIddet, PDO::PARAM_INT);
        $stmt->bindValue(":idsop", (int) $this->idsop, PDO::PARAM_INT);
        $stmt->bindValue(":idusu", (int) $this->idusu, PDO::PARAM_INT);
        $stmt->bindValue(":detest", (int) $this->detest, PDO::PARAM_INT);
        $stmt->bindValue(":detcom", $this->detcom, PDO::PARAM_STR);
        $stmt->bindValue(":detevi", $this->detevi, PDO::PARAM_STR);
        $stmt->execute();

        return $nextIddet;
    }

    public function updateDetevi()
    {
        $sql = "UPDATE detalle_soporte SET detevi = :detevi WHERE iddet = :iddet";
        $stmt = $this->conexion()->prepare($sql);
        $stmt->bindValue(":detevi", $this->getDetevi(), PDO::PARAM_STR);
        $stmt->bindValue(":iddet", (int) $this->getIddet(), PDO::PARAM_INT);
        $stmt->execute();
    }

    public function getEstado()
    {
        $sql = "SELECT idval, nomval, act FROM valor WHERE iddom = 9 ORDER BY idval ASC";
        $stmt = $this->conexion()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUltimoEstado()
    {
        $sql = "SELECT detest FROM detalle_soporte WHERE idsop = :idsop ORDER BY iddet DESC LIMIT 1";
        $stmt = $this->conexion()->prepare($sql);
        $stmt->bindValue(":idsop", (int) $this->idsop, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function finalizarSoporte()
    {
        $sql = "UPDATE soporte SET fecserfin = NOW() WHERE idsop = :idsop";
        $stmt = $this->conexion()->prepare($sql);
        $stmt->bindValue(":idsop", (int) $this->idsop, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function del()
    {
        $sql = "DELETE FROM detalle_soporte WHERE iddet = :iddet";
        $stmt = $this->conexion()->prepare($sql);
        $stmt->bindValue(":iddet", (int) $this->getIddet(), PDO::PARAM_INT);
        $stmt->execute();
    }
}