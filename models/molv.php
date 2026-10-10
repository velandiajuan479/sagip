<?php
class Molv{
    private $emausu;
    private $fecsol;
    private $keyolv;
    private $idusu;
    private $pas;
    
    function getEmausu(){
        return $this->emausu;
    }
    function getFecsol(){
        return $this->fecsol;
    }
    function getKeyolv(){
        return $this->keyolv;
    }
    function getIdusu(){
        return $this->idusu;
    }
    function getPas(){
        return $this->pas;
    }
    
    function setEmausu($emausu){
        $this->emausu = $emausu;
    }
    function setFecsol($fecsol){
        $this->fecsol = $fecsol;
    }
    function setKeyolv($keyolv){
        $this->keyolv = $keyolv;
    }
    function setIdusu($idusu){
        $this->idusu = $idusu;
    }
    function setPas($pas){
        $this->pas = $pas;
    }
    
    public function getOneEma(){
        $res = NULL;
        $sql = "SELECT idusu, nomusu, emausu FROM usuario WHERE emausu=:emausu";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $emausu = $this->getEmausu();
        $result->bindParam(":emausu",$emausu);
        $result->execute();
        $res = $result->fetch(PDO::FETCH_ASSOC);
        return $res;
    }
    
    public function updUsu(){
        $res = NULL;
        $sql = "UPDATE usuario SET fecsol=:fecsol, keyolv=:keyolv, bloqkey=2 WHERE idusu=:idusu";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $fecsol = $this->getFecsol();
        $result->bindParam(":fecsol",$fecsol);
        $keyolv = $this->getKeyolv();
        $result->bindParam(":keyolv",$keyolv);
        $idusu = $this->getIdusu();
        $result->bindParam(":idusu",$idusu);
        $result->execute();
    }
    
    public function updPas(){
        $res = NULL;
        $sql = "UPDATE usuario SET pasusu=:pasusu, bloqkey=1 WHERE idusu=:idusu";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $pasusu = $this->getPas();
        $result->bindParam(":pasusu",$pasusu);
        $idusu = $this->getIdusu();
        $result->bindParam(":idusu",$idusu);
        $result->execute();
    }
    
    public function getOneKO(){
        $res = NULL;
        $sql = "SELECT idusu, nomusu, emausu FROM usuario WHERE emausu=:emausu AND keyolv=:keyolv AND bloqkey=2 AND :fecsol BETWEEN fecsol AND ADDTIME(fecsol, '01:00:00')";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $emausu = $this->getEmausu();
        $result->bindParam(":emausu",$emausu);
        $keyolv = $this->getKeyolv();
        $result->bindParam(":keyolv",$keyolv);
        $fecsol = $this->getFecsol();
        $result->bindParam(":fecsol",$fecsol);
        $result->execute();
        $res = $result->fetch(PDO::FETCH_ASSOC);
        return $res;
    }
}
?>