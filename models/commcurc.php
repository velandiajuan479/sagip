<?php
class Mccm {
    // Atributos
    private $codpro;
    private $nompro;
    private $despro; 
    private $verpro;
    private $horlpro;
    private $tippro;

    // Getters
    function getCodpro() {
        return $this->codpro;
    }
    function getNompro() {
        return $this->nompro;
    }
    function getDespro() {
        return $this->despro;
    }
    function getVerpro() {
        return $this->verpro;
    }
    function getHorlpro() {
        return $this->horlpro;
    }
    function getTippro() {
        return $this->tippro;
    }

    // Setters
    function setCodpro($codpro) {
        $this->codpro = $codpro;
    }
    function setNompro($nompro) {
        $this->nompro = $nompro;
    }
    function setDespro($despro) {
        $this->despro = $despro;
    }
    function setVerpro($verpro) {
        $this->verpro = $verpro;
    }
    function setHorlpro($horlpro) {
        $this->horlpro = $horlpro;
    }
    function setTippro($tippro) {
        $this->tippro = $tippro;
    }

    // Solo cursos complementarios (tippro 1083)
    function getAll(){
        try{
            $sql = "SELECT p.codpro, p.nompro, p.despro, p.verpro, p.horlpro, p.tippro,
                           (SELECT COUNT(*) FROM hojatra h WHERE h.codpro = p.codpro) AS usos
                    FROM programa AS p
                    WHERE p.tippro = 1083
                    ORDER BY p.nompro ASC";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->execute();
            $res = $result->fetchAll(PDO::FETCH_ASSOC);
            return $res;
        }catch(Exception $e){
            ManejoError($e);
            return [];
        }    
    }

    function getOne(){
        try{
            $sql = "SELECT codpro, nompro, despro, verpro, horlpro, tippro FROM programa WHERE codpro = :codpro";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $codpro = $this->getCodpro();
            $result->bindParam(":codpro", $codpro);
            $result->execute();
            $res = $result->fetchAll(PDO::FETCH_ASSOC);
            return $res;
        }catch(Exception $e){
            ManejoError($e);
            return [];
        }    
    }

    // Evita insertar un código que ya existe
    function existe($codpro){
        try{
            $sql = "SELECT codpro FROM programa WHERE codpro = :codpro LIMIT 1";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->bindParam(":codpro", $codpro);
            $result->execute();
            $res = $result->fetch(PDO::FETCH_ASSOC);
            return $res ? true : false;
        }catch(Exception $e){
            return false;
        }
    }

    // Cuántas hojas de trabajo usan este curso
    function vecesUsado($codpro){
        try{
            $sql = "SELECT COUNT(*) AS total FROM hojatra WHERE codpro = :codpro";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->bindParam(":codpro", $codpro);
            $result->execute();
            $res = $result->fetch(PDO::FETCH_ASSOC);
            return $res ? (int)$res['total'] : 0;
        }catch(Exception $e){
            return 0;
        }
    }

    // Métricas para el tablero de cursos complementarios
    function getDashboard(){
        try{
            $sql = "SELECT
                        (SELECT COUNT(*) FROM programa WHERE tippro = 1083) AS registrados,
                        (SELECT COUNT(DISTINCT h.codpro) FROM hojatra h
                          INNER JOIN programa p ON h.codpro = p.codpro
                          WHERE p.tippro = 1083) AS utilizados,
                        (SELECT COUNT(*) FROM hojatra h
                          INNER JOIN programa p ON h.codpro = p.codpro
                          WHERE p.tippro = 1083) AS hojas,
                        (SELECT IFNULL(SUM(h.cupo),0) FROM hojatra h
                          INNER JOIN programa p ON h.codpro = p.codpro
                          WHERE p.tippro = 1083) AS cupos,
                        (SELECT COUNT(DISTINCT h.codpro) FROM hojatra h
                          INNER JOIN programa p ON h.codpro = p.codpro
                          WHERE p.tippro = 1083
                            AND h.feclini >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)) AS activos";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->execute();
            $res = $result->fetch(PDO::FETCH_ASSOC);
            return $res ? $res : array('registrados'=>0,'utilizados'=>0,'hojas'=>0,'cupos'=>0,'activos'=>0);
        }catch(Exception $e){
            return array('registrados'=>0,'utilizados'=>0,'hojas'=>0,'cupos'=>0,'activos'=>0);
        }
    }

    // Cursos complementarios con más hojas de trabajo generadas
    function getTopUsados($limite = 5){
        try{
            $sql = "SELECT p.codpro, p.nompro, COUNT(h.idnorad) AS usos,
                           IFNULL(SUM(h.cupo),0) AS cupos
                    FROM programa AS p
                    INNER JOIN hojatra AS h ON h.codpro = p.codpro
                    WHERE p.tippro = 1083
                    GROUP BY p.codpro, p.nompro
                    ORDER BY usos DESC, p.nompro ASC
                    LIMIT " . (int)$limite;
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->execute();
            $res = $result->fetchAll(PDO::FETCH_ASSOC);
            return $res;
        }catch(Exception $e){
            return [];
        }
    }

    function save(){
        try{
            $sql = "INSERT INTO programa (codpro, nompro, despro, verpro, horlpro, horppro, tippro, idare) 
                    VALUES (:codpro, :nompro, :despro, :verpro, :horlpro, :horlpro2, :tippro, 1)";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            
            $codpro = $this->getCodpro();
            $nompro = $this->getNompro();
            $despro = $this->getDespro();
            $verpro = $this->getVerpro();
            $horlpro = $this->getHorlpro();
            $tippro = $this->getTippro();

            $result->bindParam(":codpro", $codpro);
            $result->bindParam(":nompro", $nompro);
            $result->bindParam(":despro", $despro);
            $result->bindParam(":verpro", $verpro);
            $result->bindParam(":horlpro", $horlpro);
            $result->bindParam(":horlpro2", $horlpro);
            $result->bindParam(":tippro", $tippro);
            
            $result->execute();
        }catch(Exception $e){
            ManejoError($e);
        }    
    }

    function edit(){
        try{
            $sql = "UPDATE programa SET nompro = :nompro, despro = :despro, verpro = :verpro, horlpro = :horlpro, tippro = :tippro
                    WHERE codpro = :codpro";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            
            $codpro = $this->getCodpro();
            $nompro = $this->getNompro();
            $despro = $this->getDespro();
            $verpro = $this->getVerpro();
            $horlpro = $this->getHorlpro();
            $tippro = $this->getTippro();
            
            $result->bindParam(":codpro", $codpro);
            $result->bindParam(":nompro", $nompro);
            $result->bindParam(":despro", $despro);
            $result->bindParam(":verpro", $verpro);
            $result->bindParam(":horlpro", $horlpro);
            $result->bindParam(":tippro", $tippro);
            
            $result->execute();
        }catch(Exception $e){
            ManejoError($e);
        }    
    }

    function del(){
        try{
            $sql = "DELETE FROM programa WHERE codpro = :codpro";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            
            $codpro = $this->getCodpro();
            $result->bindParam(":codpro", $codpro);
            $result->execute();
        }catch(Exception $e){
            ManejoError($e);
        }    
    }
}
?>