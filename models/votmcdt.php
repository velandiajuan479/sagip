<?php
require_once 'conexion.php';

class Votmcdt {
    private $idusu;
    private $ndocusu;
    private $nomusu;
    private $idper;
    private $pasusu;
    private $emausu;
    private $idcen;
    private $actusu;
    private $fotcan;
    private $telcan;
    private $noca;
    private $canusu;
    private $dtvot;
    private $idfic;

    public function getIdusu() { return $this->idusu; }
    public function getNdocusu() { return $this->ndocusu; }
    public function getNomusu() { return $this->nomusu; }
    public function getIdper() { return $this->idper; }
    public function getPasusu() { return $this->pasusu; }
    public function getEmausu() { return $this->emausu; }
    public function getIdcen() { return $this->idcen; }
    public function getActusu() { return $this->actusu; }
    public function getFotcan() { return $this->fotcan; }
    public function getTelcan() { return $this->telcan; }
    public function getNoca() { return $this->noca; }
    public function getCanusu() { return $this->canusu; }
    public function getDtvot() { return $this->dtvot; }
    public function getIdfic() { return $this->idfic; }

    public function setIdusu($idusu) { $this->idusu = $idusu; }
    public function setNdocusu($ndocusu) { $this->ndocusu = $ndocusu; }
    public function setNomusu($nomusu) { $this->nomusu = $nomusu; }
    public function setIdper($idper) { $this->idper = $idper; }
    public function setPasusu($pasusu) { $this->pasusu = $pasusu; }
    public function setEmausu($emausu) { $this->emausu = $emausu; }
    public function setIdcen($idcen) { $this->idcen = $idcen; }
    public function setActusu($actusu) { $this->actusu = $actusu; }
    public function setFotcan($fotcan) { $this->fotcan = $fotcan; }
    public function setTelcan($telcan) { $this->telcan = $telcan; }
    public function setNoca($noca) { $this->noca = $noca; }
    public function setCanusu($canusu) { $this->canusu = $canusu; }
    public function setDtvot($dtvot) { $this->dtvot = $dtvot; }
    public function setIdfic($idfic) { $this->idfic = $idfic; }

    public function getAll($idval = 1) {
        $sql = "SELECT u.idusu, u.ndocusu, u.nomusu, u.idper, u.noca, p.nomper, uf.idfic, f.nomfic, v.nomval, u.pasusu, u.emausu, u.idcen, c.nomcen, u.actusu, u.fotcan, u.telcan 
                FROM usuario AS u 
                INNER JOIN usupef AS up ON u.idusu = up.idusu 
                INNER JOIN perfil AS p ON p.idper = up.idper 
                INNER JOIN centro AS c ON u.idcen = c.idcen 
                LEFT JOIN usufic AS uf ON u.idusu = uf.idusu 
                LEFT JOIN ficha AS f ON uf.idfic = f.idfic 
                LEFT JOIN valor AS v ON f.jornada = v.idval 
                WHERE up.idper = 3 
                AND u.actusu = 1 
                AND f.jornada = :idval 
                ORDER BY u.noca, u.nomusu";

        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->bindParam(":idval", $idval, PDO::PARAM_INT);
        $result->execute();
        return $result->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByFicha($idfic) {
        $sql = "SELECT u.idusu, u.ndocusu, u.nomusu, u.idper, u.noca, p.nomper, uf.idfic, f.nomfic, v.nomval, u.emausu, u.fotcan, u.telcan, u.actusu 
                FROM usuario AS u 
                INNER JOIN usupef AS up ON u.idusu = up.idusu 
                INNER JOIN perfil AS p ON p.idper = up.idper 
                INNER JOIN usufic AS uf ON u.idusu = uf.idusu 
                INNER JOIN ficha AS f ON uf.idfic = f.idfic 
                LEFT JOIN valor AS v ON f.jornada = v.idval 
                WHERE uf.idfic = :idfic 
                AND up.idper = 3 
                AND u.actusu = 1 
                ORDER BY u.noca";

        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->bindParam(":idfic", $idfic);
        $result->execute();
        return $result->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById() {
        $sql = "SELECT u.idusu, u.ndocusu, u.nomusu, u.idper, p.nomper, uf.idfic, f.nomfic, v.nomval, u.pasusu, u.emausu, u.idcen, c.nomcen, u.actusu, u.fotcan, u.telcan, u.noca 
                FROM usuario AS u 
                INNER JOIN perfil AS p ON u.idper = p.idper 
                INNER JOIN centro AS c ON u.idcen = c.idcen 
                INNER JOIN usufic AS uf ON u.idusu = uf.idusu 
                LEFT JOIN ficha AS f ON uf.idfic = f.idfic 
                LEFT JOIN valor AS v ON f.jornada = v.idval 
                WHERE u.idusu = :idusu";

        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $idusu = $this->getIdusu();
        $result->bindParam(":idusu", $idusu);
        $result->execute();
        return $result->fetchAll(PDO::FETCH_ASSOC);
    }

    public function save() {
        try {
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();

            $sql = "INSERT INTO usuario (ndocusu, nomusu, idper, pasusu, emausu, idcen, actusu, fotcan, telcan, noca) 
                    VALUES (:ndocusu, :nomusu, :idper, :pasusu, :emausu, :idcen, :actusu, :fotcan, :telcan, :noca)";

            $result = $conexion->prepare($sql);
            $result->bindParam(":ndocusu", $this->ndocusu);
            $result->bindParam(":nomusu", $this->nomusu);
            $result->bindParam(":idper", $this->idper, PDO::PARAM_INT);
            $result->bindParam(":pasusu", $this->pasusu);
            $result->bindParam(":emausu", $this->emausu);
            $result->bindParam(":idcen", $this->idcen, PDO::PARAM_INT);
            $result->bindParam(":actusu", $this->actusu, PDO::PARAM_INT);
            $result->bindParam(":fotcan", $this->fotcan);
            $result->bindParam(":telcan", $this->telcan);
            $result->bindParam(":noca", $this->noca);

            if ($result->execute()) {
                $this->idusu = $conexion->lastInsertId();

                $sqlU = "INSERT INTO usupef (idusu, idper) VALUES (:idusu, :idper)";
                $resU = $conexion->prepare($sqlU);
                $resU->bindParam(":idusu", $this->idusu, PDO::PARAM_INT);
                $resU->bindParam(":idper", $this->idper, PDO::PARAM_INT);
                $resU->execute();

                return true;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Error al registrar candidato: " . $e->getMessage());
            return false;
        }
    }

    public function update() {
        try {
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();

            $sql = "UPDATE usuario SET 
                    ndocusu = :ndocusu,
                    nomusu = :nomusu,
                    idper = :idper,
                    emausu = :emausu,
                    idcen = :idcen,
                    actusu = :actusu,
                    fotcan = :fotcan,
                    telcan = :telcan,
                    noca = :noca
                    WHERE idusu = :idusu";

            $result = $conexion->prepare($sql);
            $result->bindParam(":idusu", $this->idusu, PDO::PARAM_INT);
            $result->bindParam(":ndocusu", $this->ndocusu);
            $result->bindParam(":nomusu", $this->nomusu);
            $result->bindParam(":idper", $this->idper, PDO::PARAM_INT);
            $result->bindParam(":emausu", $this->emausu);
            $result->bindParam(":idcen", $this->idcen, PDO::PARAM_INT);
            $result->bindParam(":actusu", $this->actusu, PDO::PARAM_INT);
            $result->bindParam(":fotcan", $this->fotcan);
            $result->bindParam(":telcan", $this->telcan);
            $result->bindParam(":noca", $this->noca);

            return $result->execute();
        } catch (PDOException $e) {
            error_log("Error al actualizar candidato: " . $e->getMessage());
            return false;
        }
    }

    public function delete() {
        try {
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();

            $conexion->beginTransaction();

            // 1. Eliminar asignación de rol candidato representante en usupef
            $sql1 = "DELETE FROM usupef WHERE idusu = :idusu AND idper = 3";
            $stmt1 = $conexion->prepare($sql1);
            $stmt1->bindParam(":idusu", $this->idusu, PDO::PARAM_INT);
            $stmt1->execute();

            // 2. Liberar número de tarjetón, foto y teléfono sin desactivar la cuenta del aprendiz
            $sql2 = "UPDATE usuario SET noca = NULL, fotcan = NULL, telcan = NULL WHERE idusu = :idusu";
            $stmt2 = $conexion->prepare($sql2);
            $stmt2->bindParam(":idusu", $this->idusu, PDO::PARAM_INT);
            $stmt2->execute();

            // 3. Eliminar propuestas asociadas al candidato si existen
            $sql3 = "DELETE FROM propuesta WHERE idusu = :idusu";
            $stmt3 = $conexion->prepare($sql3);
            $stmt3->bindParam(":idusu", $this->idusu, PDO::PARAM_INT);
            $stmt3->execute();

            $conexion->commit();
            return true;
        } catch (PDOException $e) {
            if (isset($conexion) && $conexion->inTransaction()) {
                $conexion->rollBack();
            }
            error_log("Error al eliminar candidato: " . $e->getMessage());
            return false;
        }
    }

    public function getVotosByCandidato($idcandidato) {
        $sql = "SELECT COUNT(*) as total FROM voto WHERE canusu = :canusu AND tipo_voto = 'representante'";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->bindParam(":canusu", $idcandidato, PDO::PARAM_INT);
        $result->execute();
        $res = $result->fetch(PDO::FETCH_ASSOC);
        return $res['total'] ?? 0;
    }

    public function getFichasByCentro($idcen) {
        $sql = "SELECT f.idfic, f.nomfic, v.nomval 
                FROM ficha AS f 
                INNER JOIN valor AS v ON f.jornada = v.idval 
                WHERE f.idcen = :idcen 
                ORDER BY f.idfic";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->bindParam(":idcen", $idcen);
        $result->execute();
        return $result->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getJornadas() {
        $sql = "SELECT idval, nomval FROM valor WHERE iddom = 1 AND act = 1";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->execute();
        return $result->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCentros() {
        $sql = "SELECT idcen, nomcen FROM centro ORDER BY nomcen";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->execute();
        return $result->fetchAll(PDO::FETCH_ASSOC);
    }

    public function checkNocaExists($noca, $excludeId = null) {
        $sql = "SELECT COUNT(*) as total FROM usuario u INNER JOIN usupef up ON u.idusu = up.idusu WHERE u.noca = :noca AND up.idper = 3";
        if ($excludeId) {
            $sql .= " AND u.idusu != :excludeId";
        }
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->bindParam(":noca", $noca);
        if ($excludeId) {
            $result->bindParam(":excludeId", $excludeId, PDO::PARAM_INT);
        }
        $result->execute();
        $res = $result->fetch(PDO::FETCH_ASSOC);
        return $res['total'] > 0;
    }

    public function checkDocumentoExists($ndocusu, $excludeId = null) {
        $sql = "SELECT COUNT(*) as total FROM usuario WHERE ndocusu = :ndocusu";
        if ($excludeId) {
            $sql .= " AND idusu != :excludeId";
        }
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->bindParam(":ndocusu", $ndocusu);
        if ($excludeId) {
            $result->bindParam(":excludeId", $excludeId, PDO::PARAM_INT);
        }
        $result->execute();
        $res = $result->fetch(PDO::FETCH_ASSOC);
        return $res['total'] > 0;
    }

    public function buscarPorDocumento($ndocusu) {
        $sql = "SELECT u.idusu, u.ndocusu, u.nomusu, u.emausu, u.telcan, u.fotcan, u.noca,
                       uf.idfic, f.nomfic, v.nomval,
                       (SELECT COUNT(*) FROM usupef up WHERE up.idusu = u.idusu AND up.idper = 3) AS es_candidato
                FROM usuario AS u 
                LEFT JOIN usufic AS uf ON u.idusu = uf.idusu AND uf.actfic = 1
                LEFT JOIN ficha AS f ON uf.idfic = f.idfic
                LEFT JOIN valor AS v ON f.jornada = v.idval
                WHERE u.ndocusu = :ndocusu";

        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->bindParam(":ndocusu", $ndocusu);
        $result->execute();
        return $result->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function registrarExistente($noca, $idper, $actusu = 1, $fotcan = null) {
        try {
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();

            $sql = "UPDATE usuario SET noca = :noca, actusu = :actusu, fotcan = :fotcan WHERE idusu = :idusu";
            $result = $conexion->prepare($sql);
            $result->bindParam(":noca", $noca);
            $result->bindParam(":actusu", $actusu, PDO::PARAM_INT);
            $result->bindParam(":fotcan", $fotcan);
            $result->bindParam(":idusu", $this->idusu, PDO::PARAM_INT);
            $result->execute();

            $sqlUp = "SELECT COUNT(*) as total FROM usupef WHERE idusu = :idusu AND idper = :idper";
            $resUp = $conexion->prepare($sqlUp);
            $resUp->bindParam(":idusu", $this->idusu, PDO::PARAM_INT);
            $resUp->bindParam(":idper", $idper, PDO::PARAM_INT);
            $resUp->execute();
            $existe = $resUp->fetch(PDO::FETCH_ASSOC);

            if ($existe['total'] == 0) {
                $sqlIns = "INSERT INTO usupef (idusu, idper) VALUES (:idusu, :idper)";
                $resIns = $conexion->prepare($sqlIns);
                $resIns->bindParam(":idusu", $this->idusu, PDO::PARAM_INT);
                $resIns->bindParam(":idper", $idper, PDO::PARAM_INT);
                $resIns->execute();
            }

            return true;
        } catch (PDOException $e) {
            error_log("Error al registrar candidato existente: " . $e->getMessage());
            return false;
        }
    }

    public function actualizarCandidato($noca, $actusu, $fotcan = null) {
        try {
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();

            $sql = "UPDATE usuario SET noca = :noca, actusu = :actusu";
            $params = [":noca" => $noca, ":actusu" => $actusu, ":idusu" => $this->idusu];
            
            if ($fotcan) {
                $sql .= ", fotcan = :fotcan";
                $params[":fotcan"] = $fotcan;
            }
            
            $sql .= " WHERE idusu = :idusu";
            $result = $conexion->prepare($sql);
            $result->execute($params);
            return true;
        } catch (PDOException $e) {
            error_log("Error al actualizar candidato: " . $e->getMessage());
            return false;
        }
    }

    public function getFichaUsuario($idusu) {
        $sql = "SELECT uf.idfic FROM usufic uf WHERE uf.idusu = :idusu AND uf.actfic = 1 LIMIT 1";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->bindParam(":idusu", $idusu);
        $result->execute();
        $res = $result->fetch(PDO::FETCH_ASSOC);
        return $res ? $res['idfic'] : null;
    }

    public function asignarFicha($idusu, $idfic) {
        try {
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $sql = "SELECT COUNT(*) as total FROM usufic WHERE idusu = :idusu";
            $result = $conexion->prepare($sql);
            $result->bindParam(":idusu", $idusu, PDO::PARAM_INT);
            $result->execute();
            $res = $result->fetch(PDO::FETCH_ASSOC);

            if ($res['total'] > 0) {
                $sql = "UPDATE usufic SET idfic = :idfic, actfic = 1 WHERE idusu = :idusu";
            } else {
                $sql = "INSERT INTO usufic (idusu, idfic, actfic) VALUES (:idusu, :idfic, 1)";
            }
            $result = $conexion->prepare($sql);
            $result->bindParam(":idfic", $idfic);
            $result->bindParam(":idusu", $idusu, PDO::PARAM_INT);
            return $result->execute();
        } catch (PDOException $e) {
            error_log("Error al asignar ficha al candidato: " . $e->getMessage());
            return false;
        }
    }
}