<?php
require_once __DIR__ . '/conexion.php';

class Mesmdsop
{
    private $idsop;
    private $idusu;
    private $idusu2;
    private $falrep;
    private $carper;
    private $fecser;
    private $nomper;
    private $desser;
    private $evisop;

    public function getIdsop() { return $this->idsop; }
    public function getIdusu() { return $this->idusu; }
    public function getIdusu2() { return $this->idusu2; }
    public function getFalrep() { return $this->falrep; }
    public function getCarper() { return $this->carper; }
    public function getFecser() { return $this->fecser; }
    public function getNomper() { return $this->nomper; }
    public function getDesser() { return $this->desser; }
    public function getEvisop() { return $this->evisop; }

    public function setIdsop($value) { $this->idsop = $value; }
    public function setIdusu($value) { $this->idusu = $value; }
    public function setIdusu2($value) { $this->idusu2 = $value; }
    public function setFalrep($value) { $this->falrep = $value; }
    public function setCarper($value) { $this->carper = $value; }
    public function setFecser($value) { $this->fecser = $value; }
    public function setNomper($value) { $this->nomper = $value; }
    public function setDesser($value) { $this->desser = $value; }
    public function setEvisop($value) { $this->evisop = $value; }

    private function conexion()
    {
        $modelo = new conexion();
        return $modelo->get_conexion();
    }

    private function estadoActualSql()
    {
        return "LEFT JOIN (
                    SELECT d1.idsop, d1.detest
                    FROM detalle_soporte AS d1
                    INNER JOIN (
                        SELECT idsop, MAX(iddet) AS max_iddet
                        FROM detalle_soporte
                        GROUP BY idsop
                    ) AS d2 ON d1.iddet = d2.max_iddet
                ) AS ds ON s.idsop = ds.idsop
                LEFT JOIN valor AS v3 ON ds.detest = v3.idval";
    }

    public function getAll($idper = null, $idusu = null, $mes = null, $anio = null, $fecha_filtro = null)
    {
        $sql = "SELECT
                    s.idsop,
                    s.idusu,
                    s.idusu2,
                    u.nomusu AS nomusu,
                    u2.nomusu AS nomtecnico,
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
                " . $this->estadoActualSql() . "
                WHERE 1 = 1";

        $params = [];

        if ((int) $idper === 27 && $idusu !== null) {
            $sql .= " AND (s.idusu = :idusu OR s.idusu2 = :idusu)";
            $params[':idusu'] = [(int) $idusu, PDO::PARAM_INT];
        }

        if ($mes !== null) {
            $sql .= " AND MONTH(s.fecserini) = :mes";
            $params[':mes'] = [(int) $mes, PDO::PARAM_INT];
        }

        if ($anio !== null) {
            $sql .= " AND YEAR(s.fecserini) = :anio";
            $params[':anio'] = [(int) $anio, PDO::PARAM_INT];
        }

        if ($fecha_filtro !== null) {
            $sql .= " AND DATE(s.fecserini) = :fecha_filtro";
            $params[':fecha_filtro'] = [$fecha_filtro, PDO::PARAM_STR];
        }

        $sql .= " ORDER BY s.idsop DESC";

        $stmt = $this->conexion()->prepare($sql);

        foreach ($params as $name => $value) {
            $stmt->bindValue($name, $value[0], $value[1]);
        }

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getOne()
    {
        $sql = "SELECT
                    s.idsop,
                    s.idusu,
                    s.idusu2,
                    u.nomusu,
                    u2.nomusu AS nomtecnico,
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
                " . $this->estadoActualSql() . "
                WHERE s.idsop = :idsop";

        $stmt = $this->conexion()->prepare($sql);
        $stmt->bindValue(':idsop', (int) $this->getIdsop(), PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getMetricasDashboard($fechaFiltro = null, $mes = null, $anio = null)
    {
        $fecha = DateTime::createFromFormat('Y-m-d', (string) $fechaFiltro);
        if (!$fecha || $fecha->format('Y-m-d') !== $fechaFiltro) {
            $fechaFiltro = date('Y-m-d');
        }

        $periodoMes = $mes !== null ? (int) $mes : (int) date('n');
        $periodoAnio = $anio !== null ? (int) $anio : (int) date('Y');

        $sql = "SELECT
                    (
                        SELECT COUNT(*)
                        FROM soporte
                        WHERE MONTH(fecserini) = :mes_at
                          AND YEAR(fecserini) = :anio_at
                    ) AS atendidos_mes,

                    (
                        SELECT COUNT(*)
                        FROM soporte AS s
                        INNER JOIN (
                            SELECT d1.idsop, d1.detest
                            FROM detalle_soporte AS d1
                            INNER JOIN (
                                SELECT idsop, MAX(iddet) AS max_iddet
                                FROM detalle_soporte
                                GROUP BY idsop
                            ) AS d2 ON d1.iddet = d2.max_iddet
                        ) AS ds ON s.idsop = ds.idsop
                        WHERE ds.detest = 1074
                          AND MONTH(s.fecserfin) = :mes_res
                          AND YEAR(s.fecserfin) = :anio_res
                    ) AS resueltos_mes,

                    (
                        SELECT COUNT(*)
                        FROM soporte AS s
                        INNER JOIN (
                            SELECT d1.idsop, d1.detest
                            FROM detalle_soporte AS d1
                            INNER JOIN (
                                SELECT idsop, MAX(iddet) AS max_iddet
                                FROM detalle_soporte
                                GROUP BY idsop
                            ) AS d2 ON d1.iddet = d2.max_iddet
                        ) AS ds ON s.idsop = ds.idsop
                        WHERE ds.detest = 1074
                          AND YEAR(s.fecserfin) = :anio_anual
                    ) AS resueltos_anual,

                    (
                        SELECT COUNT(*)
                        FROM soporte AS s
                        INNER JOIN (
                            SELECT d1.idsop, d1.detest
                            FROM detalle_soporte AS d1
                            INNER JOIN (
                                SELECT idsop, MAX(iddet) AS max_iddet
                                FROM detalle_soporte
                                GROUP BY idsop
                            ) AS d2 ON d1.iddet = d2.max_iddet
                        ) AS ds ON s.idsop = ds.idsop
                        WHERE ds.detest = 1074
                          AND DATE(s.fecserfin) = :fecha_filtro
                    ) AS resueltos_fecha";

        $stmt = $this->conexion()->prepare($sql);
        $stmt->bindValue(':mes_at', $periodoMes, PDO::PARAM_INT);
        $stmt->bindValue(':anio_at', $periodoAnio, PDO::PARAM_INT);
        $stmt->bindValue(':mes_res', $periodoMes, PDO::PARAM_INT);
        $stmt->bindValue(':anio_res', $periodoAnio, PDO::PARAM_INT);
        $stmt->bindValue(':anio_anual', $periodoAnio, PDO::PARAM_INT);
        $stmt->bindValue(':fecha_filtro', $fechaFiltro, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [
            'atendidos_mes' => 0,
            'resueltos_mes' => 0,
            'resueltos_anual' => 0,
            'resueltos_fecha' => 0
        ];
    }

    public function getCargoPer()
    {
        $stmt = $this->conexion()->prepare("SELECT idval, nomval, act FROM valor WHERE iddom = 7 ORDER BY nomval ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getFallaRep()
    {
        $stmt = $this->conexion()->prepare("SELECT idval, nomval, act FROM valor WHERE iddom = 6 ORDER BY nomval ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
            $stmt->bindValue(':idcen', (int) $idcen, PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function save()
    {
        $conexion = $this->conexion();
        $stmtId = $conexion->query("SELECT COALESCE(MAX(idsop), 0) + 1 AS next_idsop FROM soporte");
        $rowId = $stmtId->fetch(PDO::FETCH_ASSOC);
        $nextIdsop = (int) $rowId['next_idsop'];

        $stmt = $conexion->prepare(
            "INSERT INTO soporte (idsop, idusu, idusu2, falrep, carper, fecserini, nomper, desser, evisop)
             VALUES (:idsop, :idusu, :idusu2, :falrep, :carper, NOW(), :nomper, :desser, :evisop)"
        );
        $stmt->bindValue(':idsop', $nextIdsop, PDO::PARAM_INT);
        $stmt->bindValue(':idusu', $this->getIdusu() ? (int) $this->getIdusu() : null, $this->getIdusu() ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $stmt->bindValue(':idusu2', $this->getIdusu2() !== null ? (int) $this->getIdusu2() : null, $this->getIdusu2() !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $stmt->bindValue(':falrep', (int) $this->getFalrep(), PDO::PARAM_INT);
        $stmt->bindValue(':carper', (int) $this->getCarper(), PDO::PARAM_INT);
        $stmt->bindValue(':nomper', $this->getNomper(), PDO::PARAM_STR);
        $stmt->bindValue(':desser', $this->getDesser(), PDO::PARAM_STR);
        $stmt->bindValue(':evisop', $this->getEvisop(), PDO::PARAM_STR);
        $stmt->execute();

        return $nextIdsop;
    }

    public function updateEvisop()
    {
        $stmt = $this->conexion()->prepare("UPDATE soporte SET evisop = :evisop WHERE idsop = :idsop");
        $stmt->bindValue(':evisop', $this->getEvisop(), PDO::PARAM_STR);
        $stmt->bindValue(':idsop', (int) $this->getIdsop(), PDO::PARAM_INT);
        $stmt->execute();
    }

    public function edit()
    {
        $stmt = $this->conexion()->prepare(
            "UPDATE soporte SET idusu = :idusu, idusu2 = :idusu2, falrep = :falrep, carper = :carper, 
             nomper = :nomper, desser = :desser, evisop = :evisop WHERE idsop = :idsop"
        );
        $stmt->bindValue(':idsop', (int) $this->getIdsop(), PDO::PARAM_INT);
        $stmt->bindValue(':idusu', $this->getIdusu() ? (int) $this->getIdusu() : null, $this->getIdusu() ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $stmt->bindValue(':idusu2', $this->getIdusu2() !== null ? (int) $this->getIdusu2() : null, $this->getIdusu2() !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $stmt->bindValue(':falrep', (int) $this->getFalrep(), PDO::PARAM_INT);
        $stmt->bindValue(':carper', (int) $this->getCarper(), PDO::PARAM_INT);
        $stmt->bindValue(':nomper', $this->getNomper(), PDO::PARAM_STR);
        $stmt->bindValue(':desser', $this->getDesser(), PDO::PARAM_STR);
        $stmt->bindValue(':evisop', $this->getEvisop(), PDO::PARAM_STR);
        $stmt->execute();
    }

    public function del()
    {
        $conexion = $this->conexion();
        $conexion->beginTransaction();
        try {
            $stmt = $conexion->prepare("DELETE FROM detalle_soporte WHERE idsop = :idsop");
            $stmt->bindValue(':idsop', (int) $this->getIdsop(), PDO::PARAM_INT);
            $stmt->execute();

            $stmt = $conexion->prepare("DELETE FROM soporte WHERE idsop = :idsop");
            $stmt->bindValue(':idsop', (int) $this->getIdsop(), PDO::PARAM_INT);
            $stmt->execute();

            $conexion->commit();
        } catch (Throwable $e) {
            if ($conexion->inTransaction()) {
                $conexion->rollBack();
            }
            throw $e;
        }
    }
}