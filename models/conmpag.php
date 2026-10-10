<?php
class conMpag{
    private $idpag;
    private $nompag;
    private $rutpag;
    private $mospas;
    private $ordpag;
    private $icopag;
    private $idmod;
    private $despag;

    // GETTERS
    public function getIdpag(){ return $this->idpag; }
    public function getNompag(){ return $this->nompag; }
    public function getRutpag(){ return $this->rutpag; }
    public function getMospas(){ return $this->mospas; }
    public function getOrdpag(){ return $this->ordpag; }
    public function getIcopag(){ return $this->icopag; }
    public function getIdmod(){ return $this->idmod; }
    public function getDespag(){ return $this->despag; }

    // SETTERS
    public function setIdpag($idpag){ $this->idpag=$idpag; }
    public function setNompag($nompag){ $this->nompag=$nompag; }
    public function setRutpag($rutpag){ $this->rutpag=$rutpag; }
    public function setMospas($mospas){ $this->mospas=$mospas; }
    public function setOrdpag($ordpag){ $this->ordpag=$ordpag; }
    public function setIcopag($icopag){ $this->icopag=$icopag; }
    public function setIdmod($idmod){ $this->idmod=$idmod; }
    public function setDespag($despag){ $this->despag=$despag; }

    function getAll(){
        try{
            $sql="SELECT p.idpag, p.nompag, p.rutpag, p.mospas, p.ordpag, p.icopag, p.idmod, p.despag, m.nommod FROM pagina AS p LEFT JOIN modulo AS m ON p.idmod=m.idmod ORDER BY p.ordpag ASC";
            $modelo=new conexion();
            $conexion=$modelo->get_conexion();
            $result=$conexion->prepare($sql);
            $result->execute();
            return $result->fetchAll(PDO::FETCH_ASSOC);
        }catch(Exception $e){
            ManejoError($e);
        }
    }

    function getOne(){
        try{
            $sql = "SELECT p.idpag, p.nompag, p.rutpag, p.mospas, p.ordpag, p.icopag, p.idmod, p.despag, m.nommod FROM pagina AS p LEFT JOIN modulo AS m ON p.idmod=m.idmod WHERE p.idpag=:idpag";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $idpag = $this->getIdpag();
            $result->bindParam(":idpag",$idpag);
            $result->execute();
            return $result->fetchAll(PDO::FETCH_ASSOC);
        }catch(Exception $e){
            ManejoError($e);
        }
    }

    function save(){
        try{
            $sql="INSERT INTO pagina(nompag, rutpag, mospas, ordpag, icopag, idmod, despag) VALUES (:nompag, :rutpag, :mospas, :ordpag, :icopag, :idmod, :despag)";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();    
            $result = $conexion->prepare($sql);
            $nompag = $this->getNompag();
            $result->bindParam(":nompag",$nompag);
            $rutpag = $this->getRutpag();
            $result->bindParam(":rutpag",$rutpag);
            $mospas = $this->getMospas();
            $result->bindParam(":mospas",$mospas);
            $ordpag = $this->getOrdpag();
            $result->bindParam(":ordpag",$ordpag);
            $icopag = $this->getIcopag();
            $result->bindParam(":icopag",$icopag);
            $idmod = $this->getIdmod();
            $result->bindParam(":idmod",$idmod);
            $despag = $this->getDespag();
            $result->bindParam(":despag",$despag);
            $result->execute();
        }catch(Exception $e){
            ManejoError($e);
        }
    }   

    function edit(){
        try{
            $sql="UPDATE pagina SET nompag=:nompag, rutpag=:rutpag, mospas=:mospas, ordpag=:ordpag, icopag=:icopag, idmod=:idmod, despag=:despag WHERE idpag=:idpag";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();    
            $result = $conexion->prepare($sql);
            $idpag = $this->getIdpag();
            $result->bindParam(":idpag", $idpag);
            $nompag = $this->getNompag();
            $result->bindParam(":nompag",$nompag);
            $rutpag = $this->getRutpag();
            $result->bindParam(":rutpag",$rutpag);
            $mospas = $this->getMospas();
            $result->bindParam(":mospas",$mospas);
            $ordpag = $this->getOrdpag();
            $result->bindParam(":ordpag",$ordpag);
            $icopag = $this->getIcopag();
            $result->bindParam(":icopag",$icopag);
            $idmod = $this->getIdmod();
            $result->bindParam(":idmod",$idmod);
            $despag = $this->getDespag();
            $result->bindParam(":despag",$despag);
            $result->execute();
        }catch(Exception $e){
            ManejoError($e);
        }
    }

    function editMospas(){
        try{
            $sql = "UPDATE pagina SET mospas=:mospas WHERE idpag=:idpag";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $idpag = $this->getIdpag();
            $result->bindParam(":idpag",$idpag);
            $mospas = $this->getMospas();
            $result->bindParam(":mospas",$mospas);
            $result->execute();
        }catch(Exception $e){
            ManejoError($e);
        }
    }

    function del(){
        try{
            $sql="DELETE FROM pagina WHERE idpag=:idpag";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();    
            $result = $conexion->prepare($sql);
            $idpag = $this->getIdpag();
            $result->bindParam(":idpag", $idpag);
            $result->execute();
        }catch(Exception $e){
            ManejoError($e);
        }
    }

    function getAllMod(){
        try{
            $sql ="SELECT idmod, nommod FROM modulo ORDER BY nommod ASC";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->execute();
            return $result->fetchAll(PDO::FETCH_ASSOC);
        }catch(Exception $e){
            ManejoError($e);
        }
    }

    function getPdfPag(){
        try{
            $sql = "SELECT p.idpag, p.nompag, p.mospas, p.idmod, m.nommod FROM pagina AS p INNER JOIN modulo AS m ON m.idmod=p.idmod ORDER BY m.nommod DESC";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->execute();
            return $result->fetchAll(PDO::FETCH_ASSOC);
        }catch(Exception $e){
            ManejoError($e);
        }
    }
}
?>