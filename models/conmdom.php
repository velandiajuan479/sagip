<?php
class conMdom{
    private $iddom;
    private $nomdom;

    public function getIddom(){
        return $this->iddom;
    }
    public function getNomdom(){
        return $this->nomdom;
    }

    // Métodos SET
    public function setIddom($iddom){
        $this->iddom=$iddom;
    }
    public function setNomdom($nomdom){
        $this->nomdom=$nomdom;
    }

    function getAll(){
        $sql = "SELECT iddom, nomdom FROM dominio ORDER BY iddom DESC";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->execute();
        $res = $result->fetchAll(PDO::FETCH_ASSOC);
        return $res;
    }

    function getOne(){
        $sql = "SELECT nomdom, iddom FROM dominio WHERE iddom=:iddom";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $iddom = $this->getIddom();
        $result->bindParam(":iddom",$iddom);
        $result->execute();
        $res = $result->fetchAll(PDO::FETCH_ASSOC);
        return $res;
    }

    public function save(){
        try{
            $sql = "INSERT INTO dominio(nomdom) VALUES (:nomdom)";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $nomdom = $this->getNomdom();
            $result->bindParam(":nomdom",$nomdom);
            $result->execute();
        }catch(Exception $e){
            ManejoError($e);
        }
    }

    public function edit(){
        try{
            $sql = "UPDATE dominio SET nomdom=:nomdom WHERE iddom=:iddom";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $iddom = $this->getIddom();
            $result->bindParam(":iddom",$iddom);
            $nomdom = $this->getNomdom();
            $result->bindParam(":nomdom",$nomdom);
            $result->execute();
        }catch(Exception $e){
            ManejoError($e);
        }
    }

    function del(){
        try{
            $sql = "DELETE FROM dominio WHERE iddom=:iddom";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $iddom = $this->getIddom();
            $result->bindParam(":iddom",$iddom);
            $result->execute();
        }catch(Exception $e){
            ManejoError($e);
        }
    }

    function getDxV($iddom){
        $res = null;
        $modelo = new conexion();
        $sql = "SELECT COUNT(iddom) AS can FROM valor WHERE iddom=:iddom";
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->bindParam(':iddom',$iddom);
        $result->execute();
        $res = $result->fetchAll(PDO::FETCH_ASSOC);
        return $res;
    }

    // MÉTODOS AÑADIDOS PARA EL DASHBOARD
    public function getStats(){
        try {
            $sql = "SELECT 
                        (SELECT COUNT(*) FROM dominio) as total_dominios,
                        (SELECT COUNT(*) FROM valor) as total_valores,
                        (SELECT COUNT(DISTINCT iddom) FROM valor) as dominios_con_valores,
                        (SELECT COUNT(*) FROM dominio d WHERE NOT EXISTS (SELECT 1 FROM valor v WHERE v.iddom = d.iddom)) as dominios_vacios";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $stmt = $conexion->prepare($sql);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(Exception $e) {
            ManejoError($e);
            return ['total_dominios' => 0, 'total_valores' => 0, 'dominios_con_valores' => 0, 'dominios_vacios' => 0];
        }
    }

    public function getTopDominiosConValores(){
        try {
            $sql = "SELECT d.nomdom, COUNT(v.idval) as cantidad 
                    FROM dominio d 
                    LEFT JOIN valor v ON d.iddom = v.iddom 
                    GROUP BY d.iddom, d.nomdom 
                    ORDER BY cantidad DESC 
                    LIMIT 5";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $stmt = $conexion->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(Exception $e) {
            ManejoError($e);
            return [];
        }
    }
}
?>