<?php
class Mnvv{
    private $idfic;
    private $idusu;
    private $actusu;

    public function getIdfic(){
        return $this->idfic;
    }
    
    public function getIdusu(){
        return $this->idusu;
    }
    
    public function getActusu(){
        return $this->actusu;
    }
    
    public function setIdfic($idfic){
        $this->idfic = $idfic;
    }
    
    public function setIdusu($idusu){
        $this->idusu = $idusu;
    }
    
    public function setActusu($actusu){
        $this->actusu = $actusu;
    }

    // Metodo para obtener todas las fichas disponibles (con busqueda opcional por numero o nombre)
    public function getFichas($buscar = null) {
        $sql = "SELECT DISTINCT uf.idfic, f.nomfic, v.nomval 
                FROM usufic uf
                JOIN ficha f ON uf.idfic = f.idfic
                JOIN valor v ON f.jornada = v.idval 
                WHERE uf.actfic = 1";
        
        if ($buscar !== null && trim($buscar) !== '') {
            $b = trim($buscar);
            if (ctype_digit($b) || is_numeric($b)) {
                // Cuando es número (ej: 32), que las fichas comiencen por ese número
                $sql .= " AND f.idfic LIKE :buscarNum";
            } else {
                // Cuando es nombre o texto
                $sql .= " AND (f.nomfic LIKE :buscarText OR f.idfic LIKE :buscarNum)";
            }
        }
        
        $sql .= " ORDER BY uf.idfic";
        
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        if ($buscar !== null && trim($buscar) !== '') {
            $b = trim($buscar);
            if (ctype_digit($b) || is_numeric($b)) {
                $paramNum = $b . "%"; // Que comience por ese número
                $result->bindParam(":buscarNum", $paramNum);
            } else {
                $paramText = "%" . $b . "%";
                $paramNum = $b . "%";
                $result->bindParam(":buscarText", $paramText);
                $result->bindParam(":buscarNum", $paramNum);
            }
        }
        $result->execute();
        return $result->fetchall(PDO::FETCH_ASSOC);
    }

    // Metodo para obtener aprendices por ficha especifica (con soporte de filtro y prioridad)
    public function getAprendicesPorFicha($idfic, $tipo = 'todos') {
        if ($tipo === 'candidatos') {
            return $this->getCandidatosVoceroPorFicha($idfic);
        } elseif ($tipo === 'aprendices') {
            return $this->getSoloAprendicesPorFicha($idfic);
        }

        $sql = "SELECT u.idusu, u.ndocusu, u.nomusu, u.idper, p.nomper, 
                       uf.idfic, f.nomfic, v.nomval, u.pasusu, u.emausu, 
                       u.idcen, c.nomcen, u.actusu, u.fotcan, u.telcan, u.noca,
                       CASE WHEN vo.idusu IS NOT NULL THEN 1 ELSE 0 END AS votado,
                       uf.actfic as estado_en_ficha
                FROM usufic AS uf
                INNER JOIN usuario AS u ON uf.idusu = u.idusu
                INNER JOIN perfil AS p ON u.idper = p.idper
                LEFT JOIN ficha AS f ON uf.idfic = f.idfic
                LEFT JOIN valor AS v ON f.jornada = v.idval
                LEFT JOIN centro AS c ON u.idcen = c.idcen
                LEFT JOIN voto AS vo ON u.idusu = vo.idusu
                WHERE uf.idfic = :idfic
                AND uf.actfic = 1
                AND (u.idper = 3 OR u.idper = 4 OR u.idper = 13 OR u.idper = 8)
                ORDER BY CASE 
                    WHEN (u.idper = 13 OR u.idper = 3 OR LOWER(p.nomper) LIKE '%vocero%' OR LOWER(p.nomper) LIKE '%candidato%') THEN 0 
                    ELSE 1 
                END, u.nomusu ASC";
            
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->bindParam(":idfic", $idfic);
        $result->execute();
        return $result->fetchall(PDO::FETCH_ASSOC);
    }

    // Metodo para obtener unicamente candidatos a vocero por ficha
    public function getCandidatosVoceroPorFicha($idfic) {
        $sql = "SELECT u.idusu, u.ndocusu, u.nomusu, u.idper, p.nomper, 
                       uf.idfic, f.nomfic, v.nomval, u.pasusu, u.emausu, 
                       u.idcen, c.nomcen, u.actusu, u.fotcan, u.telcan, u.noca,
                       CASE WHEN vo.idusu IS NOT NULL THEN 1 ELSE 0 END AS votado,
                       uf.actfic as estado_en_ficha
                FROM usufic AS uf
                INNER JOIN usuario AS u ON uf.idusu = u.idusu
                INNER JOIN perfil AS p ON u.idper = p.idper
                LEFT JOIN ficha AS f ON uf.idfic = f.idfic
                LEFT JOIN valor AS v ON f.jornada = v.idval
                LEFT JOIN centro AS c ON u.idcen = c.idcen
                LEFT JOIN voto AS vo ON u.idusu = vo.idusu
                WHERE uf.idfic = :idfic
                AND uf.actfic = 1
                AND (u.idper = 13 OR u.idper = 3 OR LOWER(p.nomper) LIKE '%vocero%' OR LOWER(p.nomper) LIKE '%candidato%')
                ORDER BY u.nomusu ASC";
            
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->bindParam(":idfic", $idfic);
        $result->execute();
        return $result->fetchall(PDO::FETCH_ASSOC);
    }

    // Metodo para obtener aprendices regulares de la ficha (sin candidatos a vocero)
    public function getSoloAprendicesPorFicha($idfic) {
        $sql = "SELECT u.idusu, u.ndocusu, u.nomusu, u.idper, p.nomper, 
                       uf.idfic, f.nomfic, v.nomval, u.pasusu, u.emausu, 
                       u.idcen, c.nomcen, u.actusu, u.fotcan, u.telcan, u.noca,
                       CASE WHEN vo.idusu IS NOT NULL THEN 1 ELSE 0 END AS votado,
                       uf.actfic as estado_en_ficha
                FROM usufic AS uf
                INNER JOIN usuario AS u ON uf.idusu = u.idusu
                INNER JOIN perfil AS p ON u.idper = p.idper
                LEFT JOIN ficha AS f ON uf.idfic = f.idfic
                LEFT JOIN valor AS v ON f.jornada = v.idval
                LEFT JOIN centro AS c ON u.idcen = c.idcen
                LEFT JOIN voto AS vo ON u.idusu = vo.idusu
                WHERE uf.idfic = :idfic
                AND uf.actfic = 1
                AND (u.idper = 4 OR u.idper = 8)
                AND u.idper NOT IN (13, 3)
                AND LOWER(p.nomper) NOT LIKE '%vocero%'
                AND LOWER(p.nomper) NOT LIKE '%candidato%'
                ORDER BY u.nomusu ASC";
            
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->bindParam(":idfic", $idfic);
        $result->execute();
        return $result->fetchall(PDO::FETCH_ASSOC);
    }

    // Metodo para obtener estadisticas por ficha
    public function getEstadisticasPorFicha($idfic) {
        $sql = "SELECT 
                COUNT(*) AS total_personas,
                COALESCE(SUM(CASE WHEN vo.idusu IS NOT NULL THEN 1 ELSE 0 END), 0) AS votaron,
                COALESCE(SUM(CASE WHEN vo.idusu IS NULL THEN 1 ELSE 0 END), 0) AS no_votaron
                FROM usufic AS uf
                INNER JOIN usuario AS u ON uf.idusu = u.idusu
                LEFT JOIN voto AS vo ON u.idusu = vo.idusu
                WHERE uf.idfic = :idfic
                AND uf.actfic = 1
                AND (u.idper = 3 OR u.idper = 4 OR u.idper = 13 OR u.idper = 8)";
        
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->bindParam(":idfic", $idfic);
        $result->execute();
        return $result->fetch(PDO::FETCH_ASSOC);
    }

    // Metodo para obtener estadisticas globales de aprendices que no han votado (todas las fichas)
    public function getEstadisticasGlobalesNoVotantes() {
        $sql = "SELECT COUNT(DISTINCT u.idusu) AS total_personas,
                       COUNT(DISTINCT CASE WHEN vo.idusu IS NULL THEN u.idusu END) AS no_votaron,
                       COUNT(DISTINCT CASE WHEN vo.idusu IS NOT NULL THEN u.idusu END) AS votaron
                FROM usuario AS u
                INNER JOIN usufic AS uf ON u.idusu = uf.idusu AND uf.actfic = 1
                LEFT JOIN voto AS vo ON u.idusu = vo.idusu
                WHERE (u.idper = 3 OR u.idper = 4 OR u.idper = 13 OR u.idper = 8)";

        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->execute();
        return $result->fetch(PDO::FETCH_ASSOC);
    }

    // Metodo para obtener todos los aprendices 
    public function getAll($fidfic = null) {
        $sql = "SELECT u.idusu, u.ndocusu, u.nomusu, u.idper, p.nomper, 
                       uf.idfic, f.nomfic, v.nomval, u.pasusu, u.emausu, 
                       u.idcen, c.nomcen, u.actusu, u.fotcan, u.telcan, u.noca,
                       CASE WHEN vo.idusu IS NOT NULL THEN 1 ELSE 0 END AS votado
                FROM usuario AS u 
                INNER JOIN perfil AS p ON u.idper = p.idper 
                LEFT JOIN usufic AS uf ON u.idusu = uf.idusu 
                LEFT JOIN ficha AS f ON uf.idfic = f.idfic 
                LEFT JOIN valor AS v ON f.jornada = v.idval 
                LEFT JOIN centro AS c ON u.idcen = c.idcen 
                LEFT JOIN voto AS vo ON u.idusu = vo.idusu 
                WHERE (u.idper = 3 OR u.idper = 4)";
        
        if($fidfic){
            $sql .= " AND uf.idfic = :fidfic";
        }
        
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        
        if($fidfic){
            $result->bindParam(":fidfic", $fidfic);
        }
        
        $result->execute();
        return $result->fetchall(PDO::FETCH_ASSOC);
    }

    public function editAct(){
        $sql = "UPDATE usuario SET actusu = :actusu WHERE idusu = :idusu";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $idusu = $this->getIdusu();
        $result->bindParam(":idusu", $idusu);
        $actusu = $this->getActusu();
        $result->bindParam(":actusu", $actusu);
        $result->execute();
    }
    
    public function edit(){
        try {
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $sql = "UPDATE usuario SET actusu = :actusu WHERE idusu = :idusu";
            $result = $conexion->prepare($sql);
            $result->bindParam(":actusu", $this->actusu);
            $result->bindParam(":idusu", $this->idusu);
            $result->execute();

            if ($this->idfic) {
                $sqlFic = "UPDATE usufic SET idfic = :idfic WHERE idusu = :idusu AND actfic = '1'";
                $stmtFic = $conexion->prepare($sqlFic);
                $stmtFic->bindParam(":idfic", $this->idfic);
                $stmtFic->bindParam(":idusu", $this->idusu);
                $stmtFic->execute();
            }
        } catch(Exception $e) {
            ManejoError($e);
        }
    }
    
    public function save(){
        try {
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $check = $conexion->prepare("SELECT COUNT(*) FROM voto WHERE idusu = :idusu AND tipo_voto = 'vocero'");
            $check->bindParam(":idusu", $this->idusu);
            $check->execute();
            if ($check->fetchColumn() > 0) {
                return false;
            }
            $sql = "INSERT INTO voto(idusu, canusu, dtvot, tipo_voto) VALUES(:idusu, 0, NOW(), 'vocero')";
            $result = $conexion->prepare($sql);
            $result->bindParam(":idusu", $this->idusu);
            $result->execute();
            return true;
        } catch(Exception $e) {
            error_log("Error al guardar voto: " . $e->getMessage());
            return false;
        }
    }
    
    public function getGraphic($fidfic = null){
        $sql = "SELECT 
                SUM(CASE WHEN vo.dtvot IS NOT NULL THEN 1 ELSE 0 END) AS votaron,
                SUM(CASE WHEN vo.dtvot IS NULL THEN 1 ELSE 0 END) AS no_votaron,
                COUNT(*) AS total_personas
                FROM usuario AS u 
                LEFT JOIN voto AS vo ON u.idusu = vo.idusu
                LEFT JOIN usufic AS uf ON u.idusu = uf.idusu
                WHERE (u.idper = 3 OR u.idper = 4)";
        
        if($fidfic){
            $sql .= " AND uf.idfic = :fidfic";
        }
        
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        
        if($fidfic){
            $result->bindParam(":fidfic", $fidfic);
        }
        
        $result->execute();
        return $result->fetchall(PDO::FETCH_ASSOC);
    }
    
    public function getVotU($idusu) {
        $sql = "SELECT idusu FROM voto WHERE idusu = :idusu";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->bindParam(":idusu", $idusu);
        $result->execute();
        return (bool) $result->fetch(PDO::FETCH_ASSOC);
    }
}
?>