<?php 
class Usumuxfi{

    private $idusu;
    private $idfic;

    public function getIdusu(){
        return $this->idusu;
    }
    public function getIdfic(){
        return $this->idfic;
    }
    public function setIdusu($idusu){
        $this->idusu = $idusu;
    }
    public function setIdfic($idfic){
        $this->idfic = $idfic;
    }

    function getAll(){
        $sql = "SELECT uf.idusu, u.ndocusu, u.nomusu, uf.idfic, f.nomfic, f.finific,
                    v.nomval AS jornada, c.nomcen, uf.actfic
                FROM usufic AS uf
                INNER JOIN usuario AS u ON uf.idusu = u.idusu
                INNER JOIN ficha AS f ON uf.idfic = f.idfic
                LEFT JOIN valor AS v ON f.jornada = v.idval
                LEFT JOIN centro AS c ON f.idcen = c.idcen
                ORDER BY u.nomusu ASC";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->execute();
        $res = $result->fetchAll(PDO::FETCH_ASSOC);
        return $res;
    }

    function selUsuarioDoc($ndocusu){
        $sql = "SELECT idusu, ndocusu, nomusu FROM usuario WHERE ndocusu=:ndocusu LIMIT 1";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->bindParam(":ndocusu", $ndocusu);
        $result->execute();
        $res = $result->fetchAll(PDO::FETCH_ASSOC);
        return $res;
    }

    function getFichas(){
        $sql = "SELECT idfic, nomfic FROM ficha ORDER BY CAST(idfic AS UNSIGNED) DESC";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->execute();
        $res = $result->fetchAll(PDO::FETCH_ASSOC);
        return $res;
    }

    function exists(){
        $sql = "SELECT COUNT(*) AS can FROM usufic WHERE idusu=:idusu AND idfic=:idfic";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $idusu = $this->getIdusu();
        $result->bindParam(":idusu", $idusu);
        $idfic = $this->getIdfic();
        $result->bindParam(":idfic", $idfic);
        $result->execute();
        $res = $result->fetchAll(PDO::FETCH_ASSOC);
        return $res;
    }

    function save(){
        try{
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $idusu = $this->getIdusu();
            $idfic = $this->getIdfic();
            $ex = $this->exists();
            if($ex[0]['can'] > 0){
                $sqlUp = "UPDATE usufic SET actfic=1 WHERE idusu=:idusu AND idfic=:idfic";
                $resultUp = $conexion->prepare($sqlUp);
                $resultUp->bindParam(":idusu", $idusu);
                $resultUp->bindParam(":idfic", $idfic);
                $resultUp->execute();
            }else{
                $sql = "UPDATE usufic SET actfic=2 WHERE idusu=:idusu";
                $result = $conexion->prepare($sql);
                $result->bindParam(":idusu", $idusu);
                $result->execute();
                $sqlIns = "INSERT INTO usufic(idusu, idfic, actfic) VALUES (:idusu, :idfic, 1)";
                $resultIns = $conexion->prepare($sqlIns);
                $resultIns->bindParam(":idusu", $idusu);
                $resultIns->bindParam(":idfic", $idfic);
                $resultIns->execute();
            }
        }catch(Exception $e){
            ManejoError($e);
        }
    }

    function activar(){
        try{
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $idusu = $this->getIdusu();
            $idfic = $this->getIdfic();
            $sql = "UPDATE usufic SET actfic=2 WHERE idusu=:idusu";
            $result = $conexion->prepare($sql);
            $result->bindParam(":idusu", $idusu);
            $result->execute();
            $sqlUp = "UPDATE usufic SET actfic=1 WHERE idusu=:idusu AND idfic=:idfic";
            $resultUp = $conexion->prepare($sqlUp);
            $resultUp->bindParam(":idusu", $idusu);
            $resultUp->bindParam(":idfic", $idfic);
            $resultUp->execute();
        }catch(Exception $e){
            ManejoError($e);
        }
    }

    function del(){
        $sql = "DELETE FROM usufic WHERE idusu=:idusu AND idfic=:idfic";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $idusu = $this->getIdusu();
        $result->bindParam(":idusu", $idusu);
        $idfic = $this->getIdfic();
        $result->bindParam(":idfic", $idfic);
        $result->execute();
    }

    function getDashboard(){
        try{
            $sql = "SELECT
                    (SELECT COUNT(*) FROM usufic) AS tot,
                    (SELECT COUNT(DISTINCT idusu) FROM usufic) AS usuarios,
                    (SELECT COUNT(DISTINCT idfic) FROM usufic) AS fichas,
                    (SELECT COUNT(*) FROM usufic WHERE actfic=1) AS activas";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->execute();
            $res = $result->fetchAll(PDO::FETCH_ASSOC);
            return $res;
        }catch(Exception $e){
            ManejoError($e);
        }
    }
}
?>