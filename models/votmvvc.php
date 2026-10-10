<?php
require_once('models/conexion.php');

class Votmvvc {
    private $idusu;
    private $canusu;
    private $dtvot;

    // Métodos Get
    public function getIdusu() {
        return $this->idusu;
    }

    public function getCanusu() {
        return $this->canusu;
    }

    public function getDtvot() {
        return $this->dtvot;
    }

    // Métodos Set
    public function setIdusu($idusu) {
        $this->idusu = $idusu;
    }

    public function setCanusu($canusu) {
        $this->canusu = $canusu;
    }

    public function setDtvot($dtvot) {
        $this->dtvot = $dtvot;
    }

    // Método para obtener la ficha del usuario actual
    public function getFichaUsuario($idusu) {
        if (!$idusu) {
            return null;
        }
        try {
            $sql = "SELECT uf.idfic FROM usufic uf WHERE uf.idusu = :idusu AND uf.actfic = '1' LIMIT 1";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->bindParam(":idusu", $idusu, PDO::PARAM_INT);
            $result->execute();
            $res = $result->fetch(PDO::FETCH_ASSOC);
            return $res ? $res['idfic'] : null;
        } catch (PDOException $e) {
            error_log("Error en getFichaUsuario: " . $e->getMessage());
            return null;
        }
    }

    // Método para obtener los voceros postulados de la misma ficha
    public function getVocerosMismaFicha($idfic, $idusu_actual = null) {
        if (!$idfic) {
            return [];
        }
        try {
            // Excluir al usuario actual solo cuando existe; así el parámetro
            // nombrado :idusu_apar solamente se usa una vez en la consulta.
            $excluye = ($idusu_actual !== null && $idusu_actual !== '')
                ? "AND u.idusu <> :idusu_apar"
                : "";

            $sql = "SELECT DISTINCT u.idusu, u.nomusu, u.fotcan, u.noca, f.nomfic, uf.idfic
                    FROM usuario u
                    INNER JOIN usupef up ON u.idusu = up.idusu
                    LEFT JOIN usufic uf ON u.idusu = uf.idusu
                    LEFT JOIN ficha f ON uf.idfic = f.idfic
                    WHERE up.idper = 13
                    AND u.actusu = '1'
                    AND uf.idfic = :idfic
                    $excluye
                    ORDER BY CAST(NULLIF(u.noca, '') AS UNSIGNED) ASC, u.nomusu ASC";
            
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $stmt = $conexion->prepare($sql);
            $stmt->bindValue(":idfic", $idfic);
            if ($excluye !== "") {
                $stmt->bindValue(":idusu_apar", $idusu_actual, PDO::PARAM_INT);
            }
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en getVocerosMismaFicha: " . $e->getMessage());
            return [];
        }
    }

    // Método para consultar si el usuario ya votó por vocero
    public function getOne($tipo = 'vocero') {
        try {
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $idusu = $this->getIdusu();

            if (!$idusu) {
                return false;
            }

            // Intentar consulta con columna tipo_voto
            try {
                $sql = "SELECT COUNT(*) AS co FROM voto WHERE idusu = :idusu AND tipo_voto = :tipo";
                $result = $conexion->prepare($sql);
                $result->bindParam(":idusu", $idusu, PDO::PARAM_INT);
                $result->bindParam(":tipo", $tipo, PDO::PARAM_STR);
                $result->execute();
                $res = $result->fetch(PDO::FETCH_ASSOC);
                return ($res && $res['co'] > 0);
            } catch (PDOException $ex) {
                // Fallback si no existe columna tipo_voto
                $sql = "SELECT COUNT(*) AS co FROM voto WHERE idusu = :idusu";
                $result = $conexion->prepare($sql);
                $result->bindParam(":idusu", $idusu, PDO::PARAM_INT);
                $result->execute();
                $res = $result->fetch(PDO::FETCH_ASSOC);
                return ($res && $res['co'] > 0);
            }
        } catch (PDOException $e) {
            error_log("Error en getOne (vocero): " . $e->getMessage());
            return false;
        }
    }

    // Método para registrar el voto
    public function save($tipo = 'vocero') {
        try {
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            
            if ($this->getOne($tipo)) {
                error_log("Usuario ya votó por $tipo: " . $this->idusu);
                return false;
            }
            
            // Intentar inserción con tipo_voto
            try {
                $sql = "INSERT INTO voto (id, idusu, canusu, dtvot, tipo_voto) 
                        VALUES (:id, :idusu, :canusu, :dtvot, :tipo)";
                $result = $conexion->prepare($sql);
                // La tabla voto no tiene id autoincremental: se calcula el siguiente
                $maxid = $conexion->query("SELECT IFNULL(MAX(id),0)+1 AS sig FROM voto")->fetch(PDO::FETCH_ASSOC);
                $id = (int)$maxid['sig'];
                $result->bindParam(":id", $id, PDO::PARAM_INT);
                $result->bindParam(":idusu", $this->idusu, PDO::PARAM_INT);
                $result->bindParam(":canusu", $this->canusu, PDO::PARAM_INT);
                $result->bindParam(":dtvot", $this->dtvot);
                $result->bindParam(":tipo", $tipo, PDO::PARAM_STR);
                return $result->execute();
            } catch (PDOException $ex) {
                // Fallback si la tabla voto no tiene la columna tipo_voto
                $sql = "INSERT INTO voto (id, idusu, canusu, dtvot) 
                        VALUES (:id, :idusu, :canusu, :dtvot)";
                $result = $conexion->prepare($sql);
                $maxid = $conexion->query("SELECT IFNULL(MAX(id),0)+1 AS sig FROM voto")->fetch(PDO::FETCH_ASSOC);
                $id = (int)$maxid['sig'];
                $result->bindParam(":id", $id, PDO::PARAM_INT);
                $result->bindParam(":idusu", $this->idusu, PDO::PARAM_INT);
                $result->bindParam(":canusu", $this->canusu, PDO::PARAM_INT);
                $result->bindParam(":dtvot", $this->dtvot);
                return $result->execute();
            }
            
        } catch (PDOException $e) {
            error_log("Error en save voto $tipo: " . $e->getMessage());
            return false;
        }
    }

    // Obtener información del voto realizado por el usuario
    public function getVotoUsuario($tipo = 'vocero') {
        try {
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $idusu = $this->getIdusu();
            
            if (!$idusu) {
                return null;
            }

            try {
                $sql = "SELECT v.*, u.nomusu, u.fotcan, f.nomfic 
                        FROM voto v 
                        LEFT JOIN usuario u ON v.canusu = u.idusu 
                        LEFT JOIN usufic uf ON u.idusu = uf.idusu 
                        LEFT JOIN ficha f ON uf.idfic = f.idfic 
                        WHERE v.idusu = :idusu AND v.tipo_voto = :tipo
                        ORDER BY v.dtvot DESC LIMIT 1";
                
                $result = $conexion->prepare($sql);
                $result->bindParam(":idusu", $idusu, PDO::PARAM_INT);
                $result->bindParam(":tipo", $tipo, PDO::PARAM_STR);
                $result->execute();
                return $result->fetch(PDO::FETCH_ASSOC);
            } catch (PDOException $ex) {
                $sql = "SELECT v.*, u.nomusu, u.fotcan, f.nomfic 
                        FROM voto v 
                        LEFT JOIN usuario u ON v.canusu = u.idusu 
                        LEFT JOIN usufic uf ON u.idusu = uf.idusu 
                        LEFT JOIN ficha f ON uf.idfic = f.idfic 
                        WHERE v.idusu = :idusu 
                        ORDER BY v.dtvot DESC LIMIT 1";
                
                $result = $conexion->prepare($sql);
                $result->bindParam(":idusu", $idusu, PDO::PARAM_INT);
                $result->execute();
                return $result->fetch(PDO::FETCH_ASSOC);
            }
        } catch (PDOException $e) {
            error_log("Error en getVotoUsuario: " . $e->getMessage());
            return null;
        }
    }

    // Obtener la jornada de la ficha del aprendiz
    public function getOneJor() {
        try {
            $sql = "SELECT f.jornada FROM usufic AS u INNER JOIN ficha AS f ON u.idfic=f.idfic WHERE u.actfic ='1' AND u.idusu=:idusu LIMIT 1;";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $idusu = $this->getIdusu();
            $result->bindParam(":idusu", $idusu, PDO::PARAM_INT);
            $result->execute();
            return $result->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en getOneJor: " . $e->getMessage());
            return [];
        }
    }
}

// Alias de clase para compatibilidad total
if (!class_exists('Mvvc')) {
    class Mvvc extends Votmvvc {}
}
?>