<?php
class Mcof{
    private $idcof;
    private $titcof;
    private $logcof;
    private $foocof;

    // METODOS GET

    public function getIdcof(){
        return $this->idcof;
    }
    public function getTitcof(){
        return $this->titcof;
    }
    public function getLogcof(){
        return $this->logcof;
    }
    public function getFoocof(){
        return $this->foocof;
    }
    // METODOD SET
    public function setIdcof($idcof){
        $this->idcof=$idcof;
    }
    public function setTitcof($titcof){
        $this->titcof=$titcof;
    }
    public function setLogcof($logcof){
        $this->logcof=$logcof;
    }
    public function setFoocof($foocof){
        $this->foocof=$foocof;
    }


    public function getAll(){
        $modelo = new conexion();
    }

    public function getOne(){
        $sql = "SELECT idcof, titcof, logcof, foocof, versoft, actsoft FROM configuracion WHERE idcof=:idcof";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $idcof=$this->getIdcof();
        $result->bindparam(":idcof",$idcof);
        $result->execute();
        $res = $result->fetchall(PDO::FETCH_ASSOC);
            return $res;
        }
    function edit(){
        try{
        $sql="UPDATE configuracion SET ";
        if($logcof){
            $sql.="logcof=:logcof,";
        }
        $sql.="titcof=:titcof,foocof=:foocof WHERE idcof=:idcof";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();    
        $result = $conexion->prepare($sql);
        $idcof=$this->getIdcof();
		$result->bindParam(":idcof",$idcof);
        if($logcof){
            $logcof=$this->getLogcof();
		    $result->bindParam(":logcof",$logcof);
        }
        $titcof=$this->getTitcof();
		$result->bindParam(":titcof",$titcof);
        $foocof=$this->getFoocof();
		$result->bindParam(":foocof",$foocof);
        $result->execute();
        $res = $result->fetchall(PDO::FETCH_ASSOC);
        }catch(Exception $e){
        ManejoError($e);
        }
        return $res;
    }
}