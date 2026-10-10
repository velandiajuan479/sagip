<?php 
/**
 * MODELO: hormactag.php
 * Responsabilidad: Gestión de actividades de sesiones (actividad / sesion / agenda)
 * Arquitectura: Modelo en patrón MVC
 */
require_once __DIR__ . "/conexion.php";

class Hormactag { 
    // Atributos 
    private $idact; 
    private $idses; 
    private $nomact; 
    private $desact; 
    private $duract; 
    private $tipact; 
    private $ordact; 
     
    // Métodos GET 
    public function getIdact()  { return $this->idact; } 
    public function getIdses()  { return $this->idses; } 
    public function getNomact() { return $this->nomact; } 
    public function getDesact() { return $this->desact; } 
    public function getDuract() { return $this->duract; }   
    public function getTipact() { return $this->tipact; } 
    public function getOrdact() { return $this->ordact; } 

    // Métodos SET 
    public function setIdact($idact)   { $this->idact = $idact; } 
    public function setIdses($idses)   { $this->idses = $idses; } 
    public function setNomact($nomact) { $this->nomact = $nomact; } 
    public function setDesact($desact) { $this->desact = $desact; } 
    public function setDuract($duract) { $this->duract = $duract; } 
    public function setTipact($tipact) { $this->tipact = $tipact; } 
    public function setOrdact($ordact) { $this->ordact = $ordact; } 

    // Métodos públicos 
    public function getAll($idusu = null, $idfic = null) { 
        $modelo = new Conexion(); 
        $conexion = $modelo->get_conexion(); 

        $sql = "SELECT a.idact, a.idses, a.nomact, a.desact, a.duract, a.tipact, a.ordact,
                       s.fecses, s.idage,
                       ag.idfic, ag.actpro,
                       f.nomfic,
                       COALESCE(u.nomusu, 'Instructor') AS instructor_nombre,
                       v.nomval AS tipo_actividad
                FROM actividad a
                LEFT JOIN sesion s ON a.idses = s.idses
                LEFT JOIN agenda ag ON s.idage = ag.idage
                LEFT JOIN ficha f ON ag.idfic = f.idfic
                LEFT JOIN usuario u ON COALESCE(ag.idins, ag.idusu) = u.idusu
                LEFT JOIN valor v ON a.tipact = v.idval
                WHERE 1=1"; 

        $params = [];
        if (!empty($idfic)) {
            $sql .= " AND ag.idfic = :idfic";
            $params[':idfic'] = $idfic;
        }
        if (!empty($idusu)) {
            $sql .= " AND (ag.idins = :idusu OR ag.idusu = :idusu)";
            $params[':idusu'] = $idusu;
        }

        $sql .= " ORDER BY a.ordact ASC, a.idact DESC";

        $result = $conexion->prepare($sql); 
        $result->execute($params); 
        return $result->fetchAll(PDO::FETCH_ASSOC); 
    } 

    public function getOne() { 
        $sql = "SELECT a.idact, a.idses, a.nomact, a.desact, a.duract, a.tipact, a.ordact,
                       s.fecses, ag.idfic
                FROM actividad a
                LEFT JOIN sesion s ON a.idses = s.idses
                LEFT JOIN agenda ag ON s.idage = ag.idage
                WHERE a.idact = :idact"; 
        $modelo = new Conexion(); 
        $conexion = $modelo->get_conexion(); 
        $result = $conexion->prepare($sql); 
        $idact = $this->getIdact(); 
        $result->bindParam(":idact", $idact, PDO::PARAM_INT); 
        $result->execute();  
        return $result->fetchAll(PDO::FETCH_ASSOC); 
    } 

    public function getSesion($idusu = null) { 
        $modelo = new Conexion(); 
        $conexion = $modelo->get_conexion(); 

        $sql = "SELECT s.idses, s.fecses, ag.idfic, f.nomfic, ag.actpro 
                FROM sesion s
                LEFT JOIN agenda ag ON s.idage = ag.idage
                LEFT JOIN ficha f ON ag.idfic = f.idfic
                WHERE 1=1"; 

        $params = [];
        if (!empty($idusu)) {
            $sql .= " AND (ag.idins = :idusu OR ag.idusu = :idusu)";
            $params[':idusu'] = $idusu;
        }

        $sql .= " ORDER BY s.fecses DESC, s.idses DESC LIMIT 50";

        $result = $conexion->prepare($sql); 
        $result->execute($params); 
        return $result->fetchAll(PDO::FETCH_ASSOC); 
    } 

    public function save() { 
        $sql = "INSERT INTO actividad (nomact, idses, desact, duract, tipact, ordact) 
                VALUES (:nomact, :idses, :desact, :duract, :tipact, :ordact)"; 
        $modelo = new Conexion(); 
        $conexion = $modelo->get_conexion();  
        $result = $conexion->prepare($sql); 

        $nomact = $this->getNomact(); 
        $idses  = $this->getIdses(); 
        $desact = $this->getDesact(); 
        $duract = $this->getDuract(); 
        $tipact = $this->getTipact(); 
        $ordact = $this->getOrdact() ?: 1; 

        $result->bindParam(":nomact", $nomact); 
        $result->bindParam(":idses", $idses); 
        $result->bindParam(":desact", $desact); 
        $result->bindParam(":duract", $duract); 
        $result->bindParam(":tipact", $tipact); 
        $result->bindParam(":ordact", $ordact); 

        return $result->execute(); 
    } 

    public function edit() { 
        $sql = "UPDATE actividad 
                SET nomact = :nomact, 
                    idses = :idses, 
                    desact = :desact, 
                    duract = :duract, 
                    tipact = :tipact, 
                    ordact = :ordact 
                WHERE idact = :idact"; 
        $modelo = new Conexion(); 
        $conexion = $modelo->get_conexion(); 
        $result = $conexion->prepare($sql); 

        $idact  = $this->getIdact(); 
        $nomact = $this->getNomact(); 
        $idses  = $this->getIdses(); 
        $desact = $this->getDesact(); 
        $duract = $this->getDuract(); 
        $tipact = $this->getTipact(); 
        $ordact = $this->getOrdact() ?: 1; 

        $result->bindParam(":idact", $idact, PDO::PARAM_INT); 
        $result->bindParam(":nomact", $nomact); 
        $result->bindParam(":idses", $idses); 
        $result->bindParam(":desact", $desact); 
        $result->bindParam(":duract", $duract); 
        $result->bindParam(":tipact", $tipact); 
        $result->bindParam(":ordact", $ordact); 

        return $result->execute(); 
    } 
 
    public function del() { 
        $sql = "DELETE FROM actividad WHERE idact = :idact"; 
        $modelo = new Conexion(); 
        $conexion = $modelo->get_conexion(); 
        $result = $conexion->prepare($sql); 
        $idact = $this->getIdact(); 
        $result->bindParam(":idact", $idact, PDO::PARAM_INT); 
        return $result->execute(); 
    } 
} 
?>