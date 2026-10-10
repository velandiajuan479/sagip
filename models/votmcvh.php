<?php
class VotMcvh{

    private $idusu;
    private $ndocusu;
    private $nomusu;
    private $idper;
    private $idfic;
    private $pasusu;
    private $idcen;
    private $actusu;

    //Metodo GET
    public function getIdusu(){
        return $this->idusu;
    }
    public function getNdocusu(){
        return $this->ndocusu;
    }
    public function getNomusu(){
        return $this->nomusu;
    }
    public function getIdper(){
        return $this->idper;
    }
    public function getIdfic(){
        return $this->idfic;
    }
    public function getPasusu(){
        return $this->pasusu;
    }
    public function getIdcen(){
        return $this->idcen;
    }
    public function getActusu(){
        return $this->actusu;
    }

    //Metodo SET
    public function setIdusu($idusu){
        $this->idusu=$idusu;
    }
    public function setNdocusu($ndocusu){
        $this->ndocusu=$ndocusu;
    }
    public function setNomusu($nomusu){
        $this->nomusu=$nomusu;
    }
    public function setIdper($idper){
        $this->idper=$idper;
    }
    public function setIdfic($idfic){
        $this->idfic=$idfic;
    }
    public function setPasusu($pasusu){
        $this->pasusu=$pasusu;
    }
    public function setIdcen($idcen){
        $this->idcen=$idcen;
    }
    public function setActusu($actusu){
        $this->actusu=$actusu;
    }

    //Funciones generales
    public function selAll(){
        $sql = "SELECT u.idusu, u.ndocusu, u.nomusu, u.idper, p.nomper, f.idfic, f.nomfic, v.nomval, u.pasusu, u.emausu, u.idcen, c.nomcen, u.actusu, u.fotcan, u.telcan FROM usuario AS u INNER JOIN perfil AS p ON u.idper=p.idper INNER JOIN centro AS c ON u.idcen=c.idcen LEFT JOIN usufic AS uf ON u.idusu=uf.idusu LEFT JOIN ficha AS f ON uf.idfic=f.idfic LEFT JOIN valor AS v ON f.jornada=v.idval;";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->execute();
        $res = $result->fetchAll(PDO::FETCH_ASSOC);
        return $res;
    }
    public function selOne(){
        $sql = "SELECT u.idusu, u.ndocusu, u.nomusu, u.idper, p.nomper, f.idfic, f.nomfic, v.nomval, u.pasusu, u.emausu, u.idcen, c.nomcen, c.fivotcen, c.ffvotcen, u.actusu, u.fotcan, u.telcan FROM usuario AS u INNER JOIN perfil AS p ON u.idper=p.idper INNER JOIN centro AS c ON u.idcen=c.idcen LEFT JOIN usufic AS uf ON u.idusu=uf.idusu LEFT JOIN ficha AS f ON uf.idfic=f.idfic LEFT JOIN valor AS v ON f.jornada=v.idval WHERE u.idusu=:idusu";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $idusu = $this->getIdusu();
        $result->bindParam(":idusu",$idusu);
        $result->execute();
        $res = $result->fetchAll(PDO::FETCH_ASSOC);
        return $res;
    }

    public function ins(){
        try {
            $sql = "INSERT INTO usuario(ndocusu, nomusu, idper, pasusu, idcen, actusu) VALUES (:ndocusu, :nomusu, :idper, :pasusu, :idcen, :actusu)";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $ndocusu = $this->getNdocusu();
            $result->bindParam(":ndocusu",$ndocusu);
            $nomusu = $this->getNomusu();
            $result->bindParam(":nomusu",$nomusu);
            $idper = $this->getIdper();
            $result->bindParam(":idper",$idper);
            $pasusu =$this->getPasusu();
            $result->bindParam(":pasusu",$pasusu);
            $idcen = $this->getIdcen();
            $result->bindParam(":idcen",$idcen);
            $actusu = $this->getActusu();
            $result->bindParam(":actusu",$actusu);
            $result->execute();
        } catch (Exception $e) {
            ManejoError($e);
        }
    }
    public function upd(){
        try {
            $pasusu=$this->getPasusu();
            $sql = "UPDATE usuario SET ndocusu=:ndocusu,nomusu=:nomusu,idper=:idper,idfic=:idfic,";
            if($pasusu) $sql .= "pasusu=:pasusu,";
            $sql .= "idcen=:idcen,actusu=:actusu WHERE idusu=:idusu";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $idusu = $this->getIdusu();
            $result->bindParam(":idusu",$idusu);
            $ndocusu = $this->getNdocusu();
            $result->bindParam(":ndocusu",$ndocusu);
            $nomusu = $this->getNomusu();
            $result->bindParam(":nomusu",$nomusu);
            $idper = $this->getIdper();
            $result->bindParam(":idper",$idper);
            if($pasusu){
                $pasusu = sha1(md5($pasusu));
                $result->bindParam(":pasusu",$pasusu);
            }
            $idcen = $this->getIdcen();
            $result->bindParam(":idcen",$idcen);
            $actusu = $this->getActusu();
            $result->bindParam(":actusu",$actusu);

            $result->execute();
        } catch (Exception $e) {
            ManejoError($e);
        }
    }
    //en principio la funcion del no deberia existir, pero en caso de necesitarse 
    public function del(){
        try {
            $sql = "DELETE FROM usuario WHERE idusu=:idusu";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $idusu = $this->getIdusu();
            $result->bindParam(":idusu",$idusu);
            $result->execute();
        } catch (Exception $e) {
            ManejoError($e);
        }
    }

    public function getCentro(){
        $sql = "SELECT idcen, nomcen FROM centro";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->execute();
        $res = $result->fetchall(PDO::FETCH_ASSOC);
        return $res;
    }

    public function getPerfil(){
        $sql = "SELECT idper, nomper FROM perfil";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->execute();
        $res = $result->fetchall(PDO::FETCH_ASSOC);
        return $res;
    }

    public function getFicha(){
        $sql = "SELECT f.idfic, f.nomfic, v.nomval FROM ficha AS f INNER JOIN valor AS v ON f.jornada=v.idval";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->execute();
        $res = $result->fetchall(PDO::FETCH_ASSOC);
        return $res;
    }
}
?>