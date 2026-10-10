<?php 
    class Usumpef{

    private $idper;
    private $nomper;
    private $idmod;
    private $idpag;

// GETT
    public function getIdper(){
        return $this->idper;
    }
    public function getNomper(){
        return $this->nomper;
    }
    public function getIdmod(){
        return $this->idmod;
    }
    public function getIdpag(){
        return $this->idpag;
    }
    // SETTT
    public function setIdper($idper){
        $this->idper = $idper;
    }
    public function setNomper($nomper){
        $this->nomper = $nomper;
    }    
    public function setIdmod($idmod){
        $this->idmod = $idmod;
    }
    public function setIdpag($idpag){
        $this->idpag = $idpag;
    }

        function getAll(){
            $sql = "SELECT p.idper, p.nomper, p.idpag, g.nompag, p.idmod, m.nommod FROM perfil AS p LEFT JOIN pagina AS g ON p.idpag=g.idpag INNER JOIN modulo AS m ON p.idmod=m.idmod ORDER BY p.idper ASC";
            //$sql = "SELECT idper, nomper, idpag, idmod FROM perfil";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->execute();
            $res = $result->fetchall(PDO::FETCH_ASSOC);
            return $res;
        }
        function getOne(){
            $sql = "SELECT p.idper, p.nomper, p.idpag, p.idmod FROM perfil AS p INNER JOIN pagina AS g ON g.idpag=p.idpag INNER JOIN modulo AS m ON m.idmod = p.idmod WHERE p.idper=:idper";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $idper= $this->getIdper();
            $result->bindParam(":idper",$idper);
            $result->execute();
            $res = $result->fetchall(PDO::FETCH_ASSOC);
            return $res;
        }
        function selBusqueda($termino){
            $sql = "SELECT p.idper, p.nomper, p.idpag, g.nompag, p.idmod, m.nommod FROM perfil AS p LEFT JOIN pagina AS g ON p.idpag=g.idpag INNER JOIN modulo AS m ON p.idmod=m.idmod WHERE p.nomper LIKE :busqueda ORDER BY p.idper ASC";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $busqueda = '%'.$termino.'%';
            $result->bindParam(":busqueda",$busqueda);
            $result->execute();
            $res = $result->fetchall(PDO::FETCH_ASSOC);
            return $res;
        }
        function save(){
            $sql = "INSERT INTO perfil(nomper, idpag, idmod) VALUES (:nomper, :idpag, :idmod)";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $nomper= $this->getNomper();
            $result->bindParam(":nomper",$nomper);
            $idpag= $this->getIdpag();
            $result->bindParam(":idpag",$idpag);
            $idmod= $this->getIdmod();
            $result->bindParam(":idmod",$idmod);
            $result->execute();                        
        }
        function edit(){
            $sql = "UPDATE perfil SET nomper=:nomper, idpag=:idpag, idmod=:idmod WHERE idper=:idper";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $idper= $this->getIdper();
            $result->bindParam(":idper",$idper);
            $nomper= $this->getNomper();
            $result->bindParam(":nomper",$nomper);
            $idpag= $this->getIdpag();
            $result->bindParam(":idpag",$idpag);
            $idmod= $this->getIdmod();
            $result->bindParam(":idmod",$idmod);
            $result->execute();
        }
        function del(){
            $sql = "DELETE FROM perfil WHERE idper=:idper";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $idper= $this->getIdper();
            $result->bindParam(":idper",$idper);
            $result->execute();
        }
        function getPag(){
            $sql = "SELECT idpag, nompag, icopag, idmod FROM pagina";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);            
            $result->execute();
            $res = $result->fetchall(PDO::FETCH_ASSOC);
            return $res;
        }

        function getPagMod(){
            $sql = "SELECT idpag, nompag, icopag FROM pagina WHERE idmod=$this->idmod";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->execute();
            $res = $result->fetchall(PDO::FETCH_ASSOC);
            return $res;
        }
        
        function getMod(){
            $sql = "SELECT idmod, nommod, imgmod, actmod, idper FROM modulo";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->execute();
            $res = $result->fetchall(PDO::FETCH_ASSOC);
            return $res;
        }
        function getPxP(){
            $sql = "SELECT idpag FROM pagper WHERE idper=:idper";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $idper= $this->getIdper();
            $result->bindParam(":idper",$idper);            
            $result->execute();
            $res = $result->fetchall(PDO::FETCH_ASSOC);
            return $res;
            
        } 
        function delPxP(){
            $sql = "DELETE FROM pagper WHERE idper=:idper";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $idper= $this->getIdper();
            $result->bindParam(":idper",$idper);
            $result->execute();
        }
        function savePxP(){
            $sql = "INSERT INTO pagper (idpag,idper) VALUES (:idpag,:idper);";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $idper= $this->getIdper();
            $result->bindParam(":idper",$idper);
            $idpag= $this->getIdpag();
            $result->bindParam(":idpag",$idpag);
            $result->execute();
        }

        function getDashboard(){
            try{
                $sql = "SELECT (SELECT COUNT(*) FROM perfil) AS totalperf";
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