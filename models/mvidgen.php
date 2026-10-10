<?php
class mVidgen{
    private $idvid;
    private $nomvid;
    private $rutvid;
    private $ordvid;
    private $feccar;
    private $fecini;
    private $fecfin;
    private $pesvid;
    private $durvid;
    private $actvid;
    private $idusu;
    
    function getIdvid(){ return $this->idvid; }
    function getNomvid(){ return $this->nomvid; }
    function getRutvid(){ return $this->rutvid; }
    function getOrdvid(){ return $this->ordvid; }
    function getFeccar(){ return $this->feccar; }
    function getFecini(){ return $this->fecini; }
    function getFecfin(){ return $this->fecfin; }
    function getPesvid(){ return $this->pesvid; }
    function getDurvid(){ return $this->durvid; }
    function getActvid(){ return $this->actvid; }
    function getIdusu(){ return $this->idusu; }

    function setIdvid($idvid){ $this->idvid = $idvid; }
    function setNomvid($nomvid){ $this->nomvid = $nomvid; }
    function setRutvid($rutvid){ $this->rutvid = $rutvid; }
    function setOrdvid($ordvid){ $this->ordvid = $ordvid; }
    function setFeccar($feccar){ $this->feccar = $feccar; }
    function setFecini($fecini){ $this->fecini = $fecini; }
    function setFecfin($fecfin){ $this->fecfin = $fecfin; }
    function setPesvid($pesvid){ $this->pesvid = $pesvid; }
    function setDurvid($durvid){ $this->durvid = $durvid; }
    function setActvid($actvid){ $this->actvid = $actvid; }
    function setIdusu($idusu){ $this->idusu = $idusu; }

    function getAll(){
        try{
            $sql = "SELECT idvid, nomvid, rutvid, ordvid, feccar, fecini, fecfin, pesvid, durvid, actvid, idusu FROM video ORDER BY ordvid";
            $modelo = new Conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->execute();
            return $result->fetchAll(PDO::FETCH_ASSOC);
        }catch(Exception $e){
            echo "Error: Comuníquese con su administrador.<br><br>".$e;
        }
    }
    
    function getVidAct(){
        try{
            $sql = "SELECT idvid, nomvid, rutvid, ordvid, feccar, fecini, fecfin, pesvid, durvid, actvid, idusu FROM video WHERE actvid=1 ORDER BY ordvid";
            $modelo = new Conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->execute();
            return $result->fetchAll(PDO::FETCH_ASSOC);
        }catch(Exception $e){
            echo "Error: Comuníquese con su administrador.<br><br>".$e;
        }
    }

    function getVidInact(){
        try{
            $sql = "SELECT idvid, nomvid, rutvid, ordvid, feccar, fecini, fecfin, pesvid, durvid, actvid, idusu FROM video WHERE actvid <> 1 OR actvid IS NULL ORDER BY ordvid";
            $modelo = new Conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->execute();
            return $result->fetchAll(PDO::FETCH_ASSOC);
        }catch(Exception $e){
            echo "Error: Comuníquese con su administrador.<br><br>".$e;
        }
    }

    function getTotales(){
        try{
            $sql = "SELECT 
                        COUNT(*) AS total,
                        COALESCE(SUM(CASE WHEN actvid = 1 THEN 1 ELSE 0 END), 0) AS activos,
                        COALESCE(SUM(CASE WHEN actvid <> 1 OR actvid IS NULL THEN 1 ELSE 0 END), 0) AS inactivos
                    FROM video";
            $modelo = new Conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->execute();
            return $result->fetchAll(PDO::FETCH_ASSOC);
        }catch(Exception $e){
            echo "Error: Comuníquese con su administrador.<br><br>".$e;
        }
    }

    function getOneVis(){
        try{
            $hoy = DATE("Y-m-d");
            $sql = "SELECT idvid, nomvid, rutvid, ordvid, feccar, fecini, fecfin, pesvid, durvid, actvid, idusu FROM video WHERE actvid=1 AND '".$hoy."' BETWEEN fecini AND fecfin ORDER BY ordvid";
            $modelo = new Conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->execute();
            return $result->fetchAll(PDO::FETCH_ASSOC);
        }catch(Exception $e){
            echo "Error: Comuníquese con su administrador.<br><br>".$e;
        }
    }

    function getOne(){
        try{
            $sql = "SELECT idvid, nomvid, rutvid, ordvid, feccar, fecini, fecfin, pesvid, durvid, actvid, idusu FROM video WHERE idvid=:idvid";
            $modelo = new Conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $idvid = $this->getIdvid();
            $result->bindParam(":idvid",$idvid);
            $result->execute();
            return $result->fetchAll(PDO::FETCH_ASSOC);
        }catch(Exception $e){
            echo "Error: Comuníquese con su administrador.<br><br>".$e;
        }
    }

    function save(){
        try{
            $sql = "INSERT INTO video (nomvid, rutvid, ordvid, feccar, fecini, fecfin, pesvid, durvid, actvid, idusu) VALUES (:nomvid, :rutvid, :ordvid, :feccar, :fecini, :fecfin, :pesvid, :durvid, :actvid, :idusu)";
            $modelo = new Conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $nomvid = $this->getNomvid();
            $result->bindParam(":nomvid", $nomvid);
            $rutvid = $this->getRutvid();
            $result->bindParam(":rutvid", $rutvid);
            $ordvid = $this->getOrdvid();
            $result->bindParam(":ordvid", $ordvid);
            $feccar = $this->getFeccar();
            $result->bindParam(":feccar", $feccar);
            $fecini = $this->getFecini();
            $result->bindParam(":fecini", $fecini);
            $fecfin = $this->getFecfin();
            $result->bindParam(":fecfin", $fecfin);
            $pesvid = $this->getPesvid();
            $result->bindParam(":pesvid", $pesvid);
            $durvid = $this->getDurvid();
            $result->bindParam(":durvid", $durvid);
            $actvid = $this->getActvid();
            $result->bindParam(":actvid", $actvid);
            $idusu = $this->getIdusu();
            $result->bindParam(":idusu", $idusu);
            $result->execute();
            return $result->fetchAll(PDO::FETCH_ASSOC);
        }catch(Exception $e){
            echo "Error: Comuníquese con su administrador.<br><br>".$e;
        }
    }

    function upd(){
        try{
            $sql = "UPDATE video SET nomvid=:nomvid, rutvid=:rutvid, ordvid=:ordvid, feccar=:feccar, fecini=:fecini, fecfin=:fecfin, pesvid=:pesvid, durvid=:durvid, actvid=:actvid, idusu=:idusu WHERE idvid=:idvid";
            $modelo = new Conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $idvid = $this->getIdvid();
            $result->bindParam(":idvid",$idvid);
            $nomvid = $this->getNomvid();
            $result->bindParam(":nomvid", $nomvid);
            $rutvid = $this->getRutvid();
            $result->bindParam(":rutvid", $rutvid);
            $ordvid = $this->getOrdvid();
            $result->bindParam(":ordvid", $ordvid);
            $feccar = $this->getFeccar();
            $result->bindParam(":feccar", $feccar);
            $fecini = $this->getFecini();
            $result->bindParam(":fecini", $fecini);
            $fecfin = $this->getFecfin();
            $result->bindParam(":fecfin", $fecfin);
            $pesvid = $this->getPesvid();
            $result->bindParam(":pesvid", $pesvid);
            $durvid = $this->getDurvid();
            $result->bindParam(":durvid", $durvid);
            $actvid = $this->getActvid();
            $result->bindParam(":actvid", $actvid);
            $idusu = $this->getIdusu();
            $result->bindParam(":idusu", $idusu);
            $result->execute();
            return $result->fetchAll(PDO::FETCH_ASSOC);
        }catch(Exception $e){
            echo "Error: Comuníquese con su administrador.<br><br>".$e;
        }
    }

    function updAct($actvid){
        try{
            $sql = "UPDATE video SET actvid=:actvid WHERE idvid=:idvid";
            $modelo = new Conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $idvid = $this->getIdvid();
            $result->bindParam(":idvid",$idvid);
            $result->bindParam(":actvid", $actvid);
            $result->execute();
            return $result->fetchAll(PDO::FETCH_ASSOC);
        }catch(Exception $e){
            echo "Error: Comuníquese con su administrador.<br><br>".$e;
        }
    }

    function del(){
        try{
            $sql = "DELETE FROM video WHERE idvid=:idvid";
            $modelo = new Conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $idvid = $this->getIdvid();
            $result->bindParam(":idvid",$idvid);
            $result->execute();
            return $result->fetchAll(PDO::FETCH_ASSOC);
        }catch(Exception $e){
            echo "Error: Comuníquese con su administrador.<br><br>".$e;
        }
    }
}
?>