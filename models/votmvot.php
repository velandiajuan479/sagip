<?php
class Mvot{
    private $idusu;
    private $canusu;
    private $dtvot;

    // Métodos Get
    public function getIdusu(){
        return $this->idusu;
    }
    public function getCanusu(){
        return $this->canusu;
    }
    public function getDtvot(){
        return $this->dtvot;
    }

    // Métodos Set
    public function setIdusu($idusu){
        $this->idusu=$idusu;
    }
    public function setCanusu($canusu){
        $this->canusu=$canusu;
    }
    public function setDtvot($dtvot){
        $this->dtvot=$dtvot;
    }
    // selAll
    public function getAll($idval=1){
        $sql="SELECT u.idusu, u.ndocusu, u.nomusu, u.idper, u.noca, p.nomper, uf.idfic, f.nomfic, v.nomval, u.pasusu, u.emausu, u.idcen, c.nomcen, u.actusu, u.fotcan, u.telcan, f.jornada
              FROM usuario AS u
              INNER JOIN usupef AS up ON u.idusu=up.idusu
              INNER JOIN perfil AS p ON p.idper=up.idper
              INNER JOIN centro AS c ON u.idcen=c.idcen
              LEFT JOIN usufic AS uf ON u.idusu=uf.idusu
              LEFT JOIN ficha AS f ON uf.idfic=f.idfic
              LEFT JOIN valor AS v ON v.idval=f.jornada
              WHERE up.idper=3 AND u.actusu=1 AND u.noca<>'999' AND f.jornada=:jor
              GROUP BY u.idusu
              ORDER BY u.noca, u.nomusu";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->bindValue(":jor", $idval);
        $result->execute();
        $res=$result->fetchAll(PDO::FETCH_ASSOC);
        return $res;
    } 

    // Devuelve la tarjeta "Voto en blanco" de la jornada, creandola si hace falta.
    public function getVotoBlanco($jornada=1){
        $sql="SELECT u.idusu, u.ndocusu, u.nomusu, u.idper, u.noca, uf.idfic, f.nomfic, f.jornada, u.actusu, u.fotcan
              FROM usuario AS u
              INNER JOIN usufic AS uf ON u.idusu=uf.idusu AND uf.actfic='1'
              INNER JOIN ficha AS f ON uf.idfic=f.idfic
              WHERE u.noca='999' AND u.nomusu='VOTO EN BLANCO' AND f.jornada=:jornada
              LIMIT 1";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $jornada = (int)$jornada;
        $result->bindParam(":jornada", $jornada, PDO::PARAM_INT);
        $result->execute();
        $res = $result->fetch(PDO::FETCH_ASSOC);
        if(!$res){
            $this->crearVotoBlanco($jornada);
            $result = $conexion->prepare($sql);
            $result->bindParam(":jornada", $jornada, PDO::PARAM_INT);
            $result->execute();
            $res = $result->fetch(PDO::FETCH_ASSOC);
        }
        return $res;
    }

    // Calcula el siguiente idusu disponible (la tabla usuario no usa AUTO_INCREMENT).
    private function nextIdUsu($conexion){
        $stmt = $conexion->query("SELECT IFNULL(MAX(idusu),0)+1 AS sig FROM usuario");
        return (int)$stmt->fetch(PDO::FETCH_ASSOC)['sig'];
    }

    // Crea el usuario "VOTO EN BLANCO" de la jornada si no existe.
    private function crearVotoBlanco($jornada){
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();

        $stmt = $conexion->prepare("SELECT idfic FROM ficha WHERE jornada=:jor AND nomfic LIKE 'Voto%' LIMIT 1");
        $stmt->bindParam(":jor", $jornada, PDO::PARAM_INT);
        $stmt->execute();
        $idfic = $stmt->fetchColumn();
        if(!$idfic){
            $idfic = 'V'.str_pad($jornada, 3, '0', STR_PAD_LEFT);
            $stmt = $conexion->prepare("INSERT IGNORE INTO ficha (idfic, nomfic, codpro, jornada, idcen, finific, ffinfic)
                                        VALUES (:idfic, 'Voto blanco CDA', 1, :jor, 951310, NOW(), NOW())");
            $stmt->bindParam(":idfic", $idfic);
            $stmt->bindParam(":jor", $jornada, PDO::PARAM_INT);
            $stmt->execute();
        }

        $stmt = $conexion->prepare("SELECT COUNT(*) AS c FROM usuario AS u
                                    INNER JOIN usufic AS uf ON u.idusu=uf.idusu
                                    INNER JOIN ficha AS f ON uf.idfic=f.idfic
                                    WHERE u.noca='999' AND u.nomusu='VOTO EN BLANCO' AND f.jornada=:jor");
        $stmt->bindParam(":jor", $jornada, PDO::PARAM_INT);
        $stmt->execute();
        if($stmt->fetch(PDO::FETCH_ASSOC)['c'] > 0){ return false; }

        $sql = "INSERT INTO usuario (idusu, ndocusu, nomusu, idper, pasusu, emausu, idcen, actusu, fotcan, telcan, noca)
                VALUES (:idusu, :ndocusu, 'VOTO EN BLANCO', 3, :pasusu, NULL, 951310, 2, NULL, '', '999')";
        $result = $conexion->prepare($sql);
        $ndocusu = mt_rand(200000000, 299999999);
        $idusu = $this->nextIdUsu($conexion);
        $pasusu = sha1($ndocusu);
        $result->bindParam(":idusu", $idusu);
        $result->bindParam(":ndocusu", $ndocusu);
        $result->bindParam(":pasusu", $pasusu);
        $result->execute();

        $sql = "INSERT INTO usupef (idusu, idper) VALUES (:idusu, 3)";
        $result = $conexion->prepare($sql);
        $result->bindParam(":idusu", $idusu);
        $result->execute();

        $sql = "INSERT INTO usufic (idusu, idfic, actfic) VALUES (:idusu, :idfic, 1)";
        $result = $conexion->prepare($sql);
        $result->bindParam(":idusu", $idusu);
        $result->bindParam(":idfic", $idfic);
        $result->execute();
        return $idusu;
    }

    public function getOne() {
        $sql = "SELECT COUNT(*) AS co FROM voto 
                WHERE idusu = :idusu AND tipo_voto = 'representante'";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $idusu = $this->getIdusu();
        $result->bindParam(":idusu", $idusu, PDO::PARAM_INT);
        $result->execute();
        $res = $result->fetch(PDO::FETCH_ASSOC);
        return $res['co'] > 0;
    }

    public function save() {
        try {
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            
            if ($this->getOne()) {
                error_log("Usuario ya votó por representante: ".$this->idusu);
                return false;
            }
            
            $sql = "INSERT INTO voto (id, idusu, canusu, dtvot, tipo_voto) 
                    VALUES (:id, :idusu, :canusu, :dtvot, 'representante')";
            
            $result = $conexion->prepare($sql);
            $maxid = $conexion->query("SELECT IFNULL(MAX(id),0)+1 AS sig FROM voto")->fetch(PDO::FETCH_ASSOC);
            $id = (int)$maxid['sig'];
            $result->bindParam(":id", $id, PDO::PARAM_INT);
            $result->bindParam(":idusu", $this->idusu, PDO::PARAM_INT);
            $result->bindParam(":canusu", $this->canusu, PDO::PARAM_INT);
            $result->bindParam(":dtvot", $this->dtvot);
            
            return $result->execute();
            
        } catch (PDOException $e) {
            error_log("Error en voto representante: ".$e->getMessage());
            return false;
        }
    }

    public function getOneJor(){
        $sql = "SELECT f.jornada FROM usufic AS u INNER JOIN ficha AS f ON u.idfic=f.idfic WHERE u.actfic ='1' AND u.idusu=:idusu;";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $idusu= $this->getIdusu();
        $result->bindParam(":idusu",$idusu);
        $result->execute();
        $res=$result->fetchAll(PDO::FETCH_ASSOC);
        return $res;
    }

    public function getVotoUsuario() {
        try {
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $idusu = $this->getIdusu();
            if (!$idusu) {
                return null;
            }
            $sql = "SELECT canusu, dtvot FROM voto 
                    WHERE idusu = :idusu AND tipo_voto = 'representante' 
                    ORDER BY dtvot DESC LIMIT 1";
            $result = $conexion->prepare($sql);
            $result->bindParam(":idusu", $idusu, PDO::PARAM_INT);
            $result->execute();
            return $result->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en getVotoUsuario representante: " . $e->getMessage());
            return null;
        }
    }

    public function getCandidatoById($idcand) {
        try {
            $sql = "SELECT u.idusu, u.ndocusu, u.nomusu, u.idper, u.noca, p.nomper, uf.idfic, f.nomfic, v.nomval, u.pasusu, u.emausu, u.idcen, c.nomcen, u.actusu, u.fotcan, u.telcan, f.jornada
                    FROM usuario AS u
                    LEFT JOIN usupef AS up ON u.idusu=up.idusu
                    LEFT JOIN perfil AS p ON p.idper=up.idper
                    LEFT JOIN centro AS c ON u.idcen=c.idcen
                    LEFT JOIN usufic AS uf ON u.idusu=uf.idusu AND uf.actfic='1'
                    LEFT JOIN ficha AS f ON uf.idfic=f.idfic
                    LEFT JOIN valor AS v ON v.idval=f.jornada
                    WHERE u.idusu = :idcand
                    LIMIT 1";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->bindParam(":idcand", $idcand, PDO::PARAM_INT);
            $result->execute();
            return $result->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en getCandidatoById: " . $e->getMessage());
            return null;
        }
    }

}
?>