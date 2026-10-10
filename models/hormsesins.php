<?php
 class Mscins{
    private $idses;
    private $idage;
    private $fecses;
    private $idfic;

// METODOS GET
    function getidses(){
        return $this->idses;
    }
    function getidage(){
        return $this->idage;
    }
    function getfecses(){
        return $this->fecses;
    }
    function getIdfic(){
        return $this->idfic;
    }

//  METODOS SET
    function setidses($idses){
        $this->idses=$idses;
    }
    function setidage($idage){
        $this->idage=$idage;
    }
    function setfecses($fecses){
        $this->fecses=$fecses;
    }
    function setIdfic($idfic){
        $this->idfic=$idfic;
    }

   
    public function getAll($idfic=null, $idses=null){
        if(($idfic===null || $idfic==='' || $idfic==='all') && ($idses===null || $idses==='')) return [];
        $sql ="SELECT s.idses, s.idage, s.fecses, a.fas, a.actpro, a.idfic FROM sesion s LEFT JOIN agenda a ON s.idage=a.idage WHERE 1=1";
        if($idfic!==null && $idfic!=='' && $idfic!=='all') $sql .= " AND a.idfic=:idfic";
        if($idses!==null && $idses!=='') $sql .= " AND s.idses=:idses";
        $sql .= " ORDER BY s.fecses DESC, s.idses DESC";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        if($idfic!==null && $idfic!=='' && $idfic!=='all') $result->bindParam(":idfic",$idfic);
        if($idses!==null && $idses!=='') $result->bindParam(":idses",$idses);
        $result->execute(); 
        $res= $result->fetchAll(PDO::FETCH_ASSOC);
        return $res;
       
    }    
    public function getOne(){
        $sql = "SELECT * FROM sesion WHERE idses=:idses";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $idses = $this->getIdses();
        $result->bindParam(":idses",$idses);
        $result->execute(); 
        $res= $result->fetchAll(PDO::FETCH_ASSOC);
        return $res;
    }
    public function save(){
        $sql = "INSERT INTO sesion (idage, fecses) VALUES (:idage, :fecses)";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $idage = $this->getIdage();
        $result->bindParam(":idage",$idage);
        $fecses = $this->getFecses();
        $result->bindParam(":fecses",$fecses);

        $result->execute();
    }

	public function edit(){
		$sql = "UPDATE sesion SET idage=:idage, fecses=:fecses WHERE idses=:idses";
		$modelo = new Conexion();
		$conexion = $modelo->get_conexion();
		$result = $conexion->prepare($sql);
		$idses = $this->getIdses();
		$result->bindParam(":idses", $idses);
		$idage = $this->getIdage();
		$result->bindParam(":idage", $idage);
		$fecses = $this->getFecses();
		$result->bindParam(":fecses", $fecses);
        $result->execute();
	}   

    public function del(){
        $sql = "DELETE FROM sesion WHERE idses=:idses";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $idses = $this->getIdses();
        $result->bindParam(":idses",$idses);
        $result->execute(); 
        
    }
    public function getAge($idfic=null){
        if($idfic===null || $idfic==='' || $idfic==='all') return [];
        $sql ="SELECT idage, fas, actpro, idfic, fecini, fecfin FROM agenda WHERE idfic IS NOT NULL AND TRIM(idfic)<>'' AND idfic=:idfic";
        $sql .= " ORDER BY idage DESC";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->bindParam(":idfic",$idfic);
        $result->execute(); 
        $res= $result->fetchAll(PDO::FETCH_ASSOC);
        return $res;
    }

    public function getTotales($idfic=null, $idses=null){
        try{
            if(($idfic===null || $idfic==='' || $idfic==='all') && ($idses===null || $idses==='')) return [['total'=>0,'activos'=>0,'inactivos'=>0]];
            $sql = "SELECT 
                        COUNT(*) AS total,
                        COALESCE(SUM(CASE WHEN s.fecses >= CURDATE() THEN 1 ELSE 0 END),0) AS activos,
                        COALESCE(SUM(CASE WHEN s.fecses < CURDATE() THEN 1 ELSE 0 END),0) AS inactivos
                    FROM sesion s LEFT JOIN agenda a ON s.idage=a.idage WHERE 1=1";
            if($idfic!==null && $idfic!=='' && $idfic!=='all') $sql .= " AND a.idfic=:idfic";
            if($idses!==null && $idses!=='') $sql .= " AND s.idses=:idses";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            if($idfic!==null && $idfic!=='' && $idfic!=='all') $result->bindParam(":idfic",$idfic);
            if($idses!==null && $idses!=='') $result->bindParam(":idses",$idses);
            $result->execute();
            return $result->fetchAll(PDO::FETCH_ASSOC);
        }catch(Exception $e){
            echo "Error: Comuníquese con su administrador.<br><br>".$e;
        }
    }

    public function getSesAct($idfic=null, $idses=null){
        try{
            if(($idfic===null || $idfic==='' || $idfic==='all') && ($idses===null || $idses==='')) return [];
            $sql = "SELECT s.idses, s.idage, s.fecses, a.fas, a.actpro, a.idfic FROM sesion s LEFT JOIN agenda a ON s.idage=a.idage WHERE s.fecses >= CURDATE()";
            if($idfic!==null && $idfic!=='' && $idfic!=='all') $sql .= " AND a.idfic=:idfic";
            if($idses!==null && $idses!=='') $sql .= " AND s.idses=:idses";
            $sql .= " ORDER BY s.fecses ASC, s.idses DESC";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            if($idfic!==null && $idfic!=='' && $idfic!=='all') $result->bindParam(":idfic",$idfic);
            if($idses!==null && $idses!=='') $result->bindParam(":idses",$idses);
            $result->execute();
            return $result->fetchAll(PDO::FETCH_ASSOC);
        }catch(Exception $e){
            echo "Error: Comuníquese con su administrador.<br><br>".$e;
        }
    }

    public function getSesInact($idfic=null, $idses=null){
        try{
            if(($idfic===null || $idfic==='' || $idfic==='all') && ($idses===null || $idses==='')) return [];
            $sql = "SELECT s.idses, s.idage, s.fecses, a.fas, a.actpro, a.idfic FROM sesion s LEFT JOIN agenda a ON s.idage=a.idage WHERE s.fecses < CURDATE()";
            if($idfic!==null && $idfic!=='' && $idfic!=='all') $sql .= " AND a.idfic=:idfic";
            if($idses!==null && $idses!=='') $sql .= " AND s.idses=:idses";
            $sql .= " ORDER BY s.fecses DESC, s.idses DESC";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            if($idfic!==null && $idfic!=='' && $idfic!=='all') $result->bindParam(":idfic",$idfic);
            if($idses!==null && $idses!=='') $result->bindParam(":idses",$idses);
            $result->execute();
            return $result->fetchAll(PDO::FETCH_ASSOC);
        }catch(Exception $e){
            echo "Error: Comuníquese con su administrador.<br><br>".$e;
        }
    }

    public function getAllJoin($idfic=null, $idses=null){
        try{
            if(($idfic===null || $idfic==='' || $idfic==='all') && ($idses===null || $idses==='')) return [];
            $sql = "SELECT s.idses, s.idage, s.fecses, a.fas, a.actpro, a.idfic FROM sesion s LEFT JOIN agenda a ON s.idage=a.idage WHERE 1=1";
            if($idfic!==null && $idfic!=='' && $idfic!=='all') $sql .= " AND a.idfic=:idfic";
            if($idses!==null && $idses!=='') $sql .= " AND s.idses=:idses";
            $sql .= " ORDER BY s.fecses DESC, s.idses DESC";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            if($idfic!==null && $idfic!=='' && $idfic!=='all') $result->bindParam(":idfic",$idfic);
            if($idses!==null && $idses!=='') $result->bindParam(":idses",$idses);
            $result->execute();
            return $result->fetchAll(PDO::FETCH_ASSOC);
        }catch(Exception $e){
            echo "Error: Comuníquese con su administrador.<br><br>".$e;
        }
    }

    public function getNextId(){
        try{
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $sql = "SELECT COALESCE(MAX(idses),0)+1 AS sig FROM sesion";
            $result = $conexion->prepare($sql);
            $result->execute();
            $res = $result->fetchAll(PDO::FETCH_ASSOC);
            return isset($res[0]['sig']) ? $res[0]['sig'] : 1;
        }catch(Exception $e){
            return 1;
        }
    }

    // Sesiones de una lista de fichas (vacía = todas). Un solo query con IN.
    public function getAllIn($idfics = []){
        try{
            $sql = "SELECT s.idses, s.idage, s.fecses, a.fas, a.actpro, a.idfic FROM sesion s LEFT JOIN agenda a ON s.idage=a.idage WHERE 1=1";
            if (!empty($idfics)) {
                $ph = implode(',', array_fill(0, count($idfics), '?'));
                $sql .= " AND a.idfic IN ($ph)";
            }
            $sql .= " ORDER BY s.fecses DESC, s.idses DESC";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->execute(array_values($idfics));
            return $result->fetchAll(PDO::FETCH_ASSOC);
        }catch(Exception $e){
            return [];
        }
    }

    // Agendas de una lista de fichas (vacía = todas).
    public function getAgeIn($idfics = []){
        try{
            $sql = "SELECT idage, fas, actpro, idfic, fecini, fecfin FROM agenda WHERE idfic IS NOT NULL AND TRIM(idfic)<>''";
            if (!empty($idfics)) {
                $ph = implode(',', array_fill(0, count($idfics), '?'));
                $sql .= " AND idfic IN ($ph)";
            }
            $sql .= " ORDER BY idage DESC";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->execute(array_values($idfics));
            return $result->fetchAll(PDO::FETCH_ASSOC);
        }catch(Exception $e){
            return [];
        }
    }
 }
if (!class_exists('Hormsesins')) {
    class Hormsesins extends Mscins {}
}
?>
