<?php 
/**
 * MODELO: hormins.php
 * Responsabilidad exclusiva: Lógica de acceso a datos para Instrumentos de Evaluación (inseva)
 * Arquitectura: Modelo en patrón MVC
 */
require_once __DIR__ . "/conexion.php";

class Mcins {
    private $idins;
    private $nomins;    
    private $idres; 
    private $idfic;

    // Getters
    public function getIdfic() { return $this->idfic; }
    public function getIdins() { return $this->idins; }
    public function getNomins() { return $this->nomins; }
    public function getIdres() { return $this->idres; }

    // Setters
    public function setIdfic($idfic) { $this->idfic = $idfic; }
    public function setIdins($idins) { $this->idins = $idins; }
    public function setNomins($nomins) { $this->nomins = $nomins; }
    public function setIdres($idres) { $this->idres = $idres; }

    public function getAll() {
        $sql = "SELECT i.idins, i.nomins, i.idres, i.idfic, f.nomfic, r.nomres 
                FROM inseva i
                LEFT JOIN ficha f ON i.idfic = f.idfic
                LEFT JOIN resultado r ON i.idres = r.idres
                ORDER BY i.idins DESC";
        $modelo = new Conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->execute();
        return $result->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getOne() {
        $sql = "SELECT idins, nomins, idres, idfic FROM inseva WHERE idins = :idins";
        $modelo = new Conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->bindParam(":idins", $this->idins);
        $result->execute();
        return $result->fetch(PDO::FETCH_ASSOC);
    }

    public function save(){
        $sql = "INSERT INTO inseva (nomins, idres, idfic) VALUES (:nomins, :idres, :idfic)";
        $modelo = new Conexion();
        $conexion = $modelo->get_conexion(); 
        $result = $conexion->prepare($sql);
        $nomins = $this->getNomins();
        $idres = $this->getIdres(); 
        $idfic = $this->getIdfic();
        $result->bindParam(":nomins", $nomins);
        $result->bindParam(":idres", $idres);
        $result->bindParam(":idfic", $idfic);
        $result->execute();
        return $conexion->lastInsertId();
    }

    public function edit() {
        $sql = "UPDATE inseva SET nomins = :nomins, idres = :idres, idfic = :idfic WHERE idins = :idins";
        $modelo = new Conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $idins = $this->getIdins();
        $nomins = $this->getNomins();
        $idres = $this->getIdres();
        $idfic = $this->getIdfic();
        $result->bindParam(":idins", $idins);
        $result->bindParam(":nomins", $nomins);
        $result->bindParam(":idres", $idres);
        $result->bindParam(":idfic", $idfic);
        $result->execute();
        return $result->rowCount();
    }

    public function del() {
        $modelo = new Conexion();
        $conexion = $modelo->get_conexion();

        // Eliminar primero los criterios relacionados en cascada controlada
        $sqlCriterios = "DELETE FROM criterio WHERE idins = :idins";
        $stmt1 = $conexion->prepare($sqlCriterios);
        $stmt1->bindParam(":idins", $this->idins);
        $stmt1->execute();

        // Luego eliminar el instrumento
        $sql = "DELETE FROM inseva WHERE idins = :idins";
        $stmt2 = $conexion->prepare($sql);
        $stmt2->bindParam(":idins", $this->idins);
        return $stmt2->execute();
    }

    public function getByResultado() {
        $sql = "SELECT idins, nomins, idres, idfic FROM inseva WHERE idres = :idres";
        $modelo = new Conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->bindParam(":idres", $this->idres);
        $result->execute();
        return $result->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByFicha($idfic) {
        $sql = "SELECT i.idins, i.nomins, i.idres, i.idfic, f.nomfic, r.nomres 
                FROM inseva i 
                LEFT JOIN ficha f ON i.idfic = f.idfic 
                LEFT JOIN resultado r ON i.idres = r.idres 
                WHERE i.idfic = :idfic
                ORDER BY i.idins DESC";
        $modelo = new Conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->bindParam(":idfic", $idfic);
        $result->execute();
        return $result->fetchAll(PDO::FETCH_ASSOC);
    }

    public function existsResultado() {
        $sql = "SELECT COUNT(*) FROM resultado WHERE idres = :idres";
        $modelo = new Conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->bindParam(":idres", $this->idres);
        $result->execute();
        return $result->fetchColumn() > 0;
    }

    public function getNombreFicha($idfic) {
        if (!$idfic) return null;
        $sql = "SELECT nomfic FROM ficha WHERE idfic = :idfic";
        $modelo = new Conexion();
        $conexion = $modelo->get_conexion();
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(":idfic", $idfic, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public function getAllFichas() {
        $sql = "SELECT idfic, nomfic FROM ficha ORDER BY idfic DESC LIMIT 30";
        $modelo = new Conexion();
        $conexion = $modelo->get_conexion();
        $stmt = $conexion->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllResultados() {
        $sql = "SELECT r.idres, r.nomres, c.descom 
                FROM resultado r 
                LEFT JOIN competencia c ON r.idcom = c.idcom 
                ORDER BY r.idres ASC LIMIT 50";
        $modelo = new Conexion();
        $conexion = $modelo->get_conexion();
        $stmt = $conexion->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ---- Criterios de evaluación (tabla criterio, vista horvcrev 1546) ----
    public function saveCriterio($nomcri, $vscum, $vpar, $vncum, $idins, $idusu){
        try{
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $sql = "INSERT INTO criterio (nomcri, vscum, vpar, vncum, idins, idusu) VALUES (:nomcri, :vscum, :vpar, :vncum, :idins, :idusu)";
            $result = $conexion->prepare($sql);
            $result->bindParam(':nomcri', $nomcri);
            $result->bindParam(':vscum', $vscum);
            $result->bindParam(':vpar', $vpar);
            $result->bindParam(':vncum', $vncum);
            $result->bindParam(':idins', $idins);
            $result->bindParam(':idusu', $idusu);
            return $result->execute();
        }catch(Exception $e){
            return false;
        }
    }

    public function editCriterio($idcri, $nomcri, $vscum, $vpar, $vncum, $idins){
        try{
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $sql = "UPDATE criterio SET nomcri=:nomcri, vscum=:vscum, vpar=:vpar, vncum=:vncum, idins=:idins WHERE idcri=:idcri";
            $result = $conexion->prepare($sql);
            $result->bindParam(':idcri', $idcri);
            $result->bindParam(':nomcri', $nomcri);
            $result->bindParam(':vscum', $vscum);
            $result->bindParam(':vpar', $vpar);
            $result->bindParam(':vncum', $vncum);
            $result->bindParam(':idins', $idins);
            return $result->execute();
        }catch(Exception $e){
            return false;
        }
    }

    public function delCriterio($idcri){
        try{
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $sql = "DELETE FROM criterio WHERE idcri=:idcri";
            $result = $conexion->prepare($sql);
            $result->bindParam(':idcri', $idcri);
            return $result->execute();
        }catch(Exception $e){
            return false;
        }
    }

    public function getCriterioById($idcri){
        try{
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $sql = "SELECT * FROM criterio WHERE idcri=:idcri LIMIT 1";
            $result = $conexion->prepare($sql);
            $result->bindParam(':idcri', $idcri);
            $result->execute();
            $res = $result->fetchAll(PDO::FETCH_ASSOC);
            return isset($res[0]) ? $res[0] : null;
        }catch(Exception $e){
            return null;
        }
    }

    public function getCriteriosMaestros($filtro_idfic = '', $filtro_idins = ''){
        try{
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $sql = "SELECT c.idcri, c.nomcri, c.vscum, c.vpar, c.vncum, c.idins, i.nomins, r.nomres, i.idfic, f.nomfic, (SELECT COUNT(*) FROM evaluacion_actividad e WHERE e.idcri=c.idcri) AS total_calificaciones, (SELECT COUNT(*) FROM agenda ag WHERE ag.idfic=i.idfic) AS actividades_agenda FROM criterio AS c LEFT JOIN inseva AS i ON c.idins=i.idins LEFT JOIN resultado AS r ON i.idres=r.idres LEFT JOIN ficha AS f ON i.idfic=f.idfic WHERE 1=1";
            if ($filtro_idfic !== null && $filtro_idfic !== '') $sql .= " AND i.idfic=:idfic";
            if ($filtro_idins !== null && $filtro_idins !== '' && (int)$filtro_idins > 0) $sql .= " AND c.idins=:idins";
            $sql .= " ORDER BY c.idcri DESC";
            $result = $conexion->prepare($sql);
            if ($filtro_idfic !== null && $filtro_idfic !== '') $result->bindParam(':idfic', $filtro_idfic);
            if ($filtro_idins !== null && $filtro_idins !== '' && (int)$filtro_idins > 0) { $iid = (int)$filtro_idins; $result->bindParam(':idins', $iid); }
            $result->execute();
            return $result->fetchAll(PDO::FETCH_ASSOC);
        }catch(Exception $e){
            return [];
        }
    }

    public function getDashboardCriterios(){
        try{
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $sql = "SELECT (SELECT COUNT(*) FROM criterio) AS total_criterios, (SELECT COUNT(DISTINCT idins) FROM criterio WHERE idins IS NOT NULL) AS instrumentos_con_criterios, (SELECT COUNT(DISTINCT i.idfic) FROM criterio c INNER JOIN inseva i ON c.idins=i.idins WHERE i.idfic IS NOT NULL) AS fichas_con_criterios, (SELECT COUNT(*) FROM evaluacion_actividad) AS calificaciones_asociadas";
            $result = $conexion->prepare($sql);
            $result->execute();
            $res = $result->fetchAll(PDO::FETCH_ASSOC);
            return isset($res[0]) ? $res[0] : ['total_criterios' => 0, 'instrumentos_con_criterios' => 0, 'fichas_con_criterios' => 0, 'calificaciones_asociadas' => 0];
        }catch(Exception $e){
            return ['total_criterios' => 0, 'instrumentos_con_criterios' => 0, 'fichas_con_criterios' => 0, 'calificaciones_asociadas' => 0];
        }
    }
}

// Alias de clase para estandarizar nomenclatura Hormins en el patrón MVC
if (!class_exists('Hormins')) {
    class Hormins extends Mcins {}
}
?>
