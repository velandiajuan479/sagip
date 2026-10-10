<?php 
class Hormpgcm{
    // Atributos proxcom
    private $codpro;
    private $idcom;

    // Métodos GET
    public function getCodpro(){
        return $this->codpro;
    }
    public function getIdcom(){
        return $this->idcom;
    }

    // Métodos SET
    public function setCodpro($codpro){
        $this->codpro = $codpro;
    }
    public function setIdcom($idcom){
        $this->idcom = $idcom;
    }

    // Métodos públicos CRUD
    public function getAll(){
        $res = NULL;
        $sql = "SELECT px.codpro, px.idcom, 
                       p.nompro, p.verpro, p.horlpro,
                       c.descom, c.vercom, c.horcom, COALESCE(v.nomval, 'Competencia') AS nomval,
                       (SELECT COUNT(r.idres) FROM resultado r WHERE r.idcom = c.idcom) AS cant_res,
                       (SELECT COUNT(DISTINCT f.idfic) FROM ficha f WHERE f.codpro = px.codpro AND f.ffinfic >= CURDATE()) AS fic_vigentes,
                       (SELECT COUNT(DISTINCT f.idfic) FROM ficha f WHERE f.codpro = px.codpro) AS tot_fic
                FROM proxcom px
                INNER JOIN programa p ON px.codpro = p.codpro
                INNER JOIN competencia c ON px.idcom = c.idcom
                LEFT JOIN valor v ON c.idval = v.idval
                ORDER BY p.nompro ASC, c.descom ASC";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->execute();
        $res = $result->fetchAll(PDO::FETCH_ASSOC);
        return $res;
    }

    public function getByPrograma($codpro){
        $res = NULL;
        $sql = "SELECT px.codpro, px.idcom, 
                       p.nompro, p.verpro, p.horlpro,
                       c.descom, c.vercom, c.horcom, COALESCE(v.nomval, 'Competencia') AS nomval,
                       (SELECT COUNT(r.idres) FROM resultado r WHERE r.idcom = c.idcom) AS cant_res,
                       (SELECT COUNT(DISTINCT f.idfic) FROM ficha f WHERE f.codpro = px.codpro AND f.ffinfic >= CURDATE()) AS fic_vigentes,
                       (SELECT COUNT(DISTINCT f.idfic) FROM ficha f WHERE f.codpro = px.codpro) AS tot_fic
                FROM proxcom px
                INNER JOIN programa p ON px.codpro = p.codpro
                INNER JOIN competencia c ON px.idcom = c.idcom
                LEFT JOIN valor v ON c.idval = v.idval
                WHERE px.codpro = :codpro
                ORDER BY c.descom ASC";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->bindParam(":codpro", $codpro);
        $result->execute();
        $res = $result->fetchAll(PDO::FETCH_ASSOC);
        return $res;
    }

    public function save(){
        try {
            $codpro = $this->getCodpro();
            $idcom = $this->getIdcom();
            if (!$codpro || !is_numeric($codpro) || !$idcom || !is_numeric($idcom)) {
                return false;
            }
            $sql = "INSERT IGNORE INTO proxcom (codpro, idcom) VALUES (:codpro, :idcom)";
            $modelo = new conexion();
            return $modelo->executeWrite($sql, [':codpro' => $codpro, ':idcom' => $idcom]);
        } catch (Exception $e) {
            error_log("Error en Hormpgcm::save(): " . $e->getMessage());
            return false;
        }
    }

    public function del(){
        try {
            $codpro = $this->getCodpro();
            $idcom = $this->getIdcom();
            $sql = "DELETE FROM proxcom WHERE codpro = :codpro AND idcom = :idcom";
            $modelo = new conexion();
            return $modelo->executeWrite($sql, [':codpro' => $codpro, ':idcom' => $idcom]);
        } catch (Exception $e) {
            error_log("Error en Hormpgcm::del(): " . $e->getMessage());
            return false;
        }
    }

    // Listados de apoyo
    public function getProgramasList(){
        $sql = "SELECT p.codpro, p.nompro, p.verpro, 
                       COUNT(px.idcom) AS total_comp,
                       COALESCE(SUM(c.horcom), 0) AS total_horas,
                       (SELECT COUNT(DISTINCT f.idfic) FROM ficha f WHERE f.codpro = p.codpro AND f.ffinfic >= CURDATE()) AS fic_vigentes
                FROM programa p
                LEFT JOIN proxcom px ON p.codpro = px.codpro
                LEFT JOIN competencia c ON px.idcom = c.idcom
                GROUP BY p.codpro, p.nompro, p.verpro
                ORDER BY p.nompro ASC";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $stmt = $conexion->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCompetenciasList(){
        $sql = "SELECT c.idcom, c.descom, c.vercom, c.horcom, v.nomval 
                FROM competencia c 
                INNER JOIN valor v ON c.idval = v.idval 
                ORDER BY c.descom ASC";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $stmt = $conexion->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Métricas para el Dashboard
    public function getResumenDashboard(){
        $res = [
            'total_vinculaciones' => 0,
            'vinculaciones_activas' => 0,
            'vinculaciones_inactivas' => 0,
            'programas_vinculados' => 0,
            'programas_activos' => 0,
            'programas_inactivos' => 0,
            'competencias_asignadas' => 0,
            'promedio_comp' => 0,
            'competencias_sin_vincular' => 0,
            'total_horas_malla' => 0,
            'total_raps' => 0,
            'raps_sin_programa' => 0
        ];
        $sql = "SELECT COUNT(*) AS total_vinculaciones, 
                       COUNT(DISTINCT px.codpro) AS programas_vinculados, 
                       COUNT(DISTINCT px.idcom) AS competencias_asignadas,
                       COUNT(DISTINCT CASE WHEN f.ffinfic >= CURDATE() THEN px.codpro END) AS programas_activos,
                       COUNT(CASE WHEN f.ffinfic >= CURDATE() THEN px.codpro END) AS vinculaciones_activas
                FROM proxcom px
                LEFT JOIN ficha f ON px.codpro = f.codpro";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $stmt = $conexion->prepare($sql);
        $stmt->execute();
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($r) {
            $res['total_vinculaciones'] = (int)$r['total_vinculaciones'];
            $res['programas_vinculados'] = (int)$r['programas_vinculados'];
            $res['programas_activos'] = (int)$r['programas_activos'];
            $res['programas_inactivos'] = max(0, $res['programas_vinculados'] - $res['programas_activos']);
            $res['vinculaciones_activas'] = (int)$r['vinculaciones_activas'];
            $res['vinculaciones_inactivas'] = max(0, $res['total_vinculaciones'] - $res['vinculaciones_activas']);
            $res['competencias_asignadas'] = (int)$r['competencias_asignadas'];
            $res['promedio_comp'] = ($res['programas_vinculados'] > 0) 
                ? round($res['total_vinculaciones'] / $res['programas_vinculados'], 1) 
                : 0;
        }

        // Competencias sin vincular a ningún programa
        $sql2 = "SELECT COUNT(*) AS sin_vincular 
                 FROM competencia c 
                 WHERE c.idcom NOT IN (SELECT idcom FROM proxcom)";
        $stmt2 = $conexion->prepare($sql2);
        $stmt2->execute();
        $r2 = $stmt2->fetch(PDO::FETCH_ASSOC);
        if ($r2) {
            $res['competencias_sin_vincular'] = (int)$r2['sin_vincular'];
        }

        // Horas de la malla curricular (sin duplicar competencias compartidas)
        $sql3 = "SELECT COALESCE(SUM(c.horcom), 0) AS horas_malla
                 FROM competencia c
                 WHERE c.idcom IN (SELECT idcom FROM proxcom)";
        $stmt3 = $conexion->prepare($sql3);
        $stmt3->execute();
        $r3 = $stmt3->fetch(PDO::FETCH_ASSOC);
        if ($r3) {
            $res['total_horas_malla'] = (int)$r3['horas_malla'];
        }

        // Resultados asociados y huérfanos
        $sql4 = "SELECT (SELECT COUNT(idres) FROM resultado WHERE idcom IN (SELECT idcom FROM proxcom)) AS en_malla,
                        (SELECT COUNT(idres) FROM resultado WHERE idcom NOT IN (SELECT idcom FROM proxcom)) AS sin_programa";
        $stmt4 = $conexion->prepare($sql4);
        $stmt4->execute();
        $r4 = $stmt4->fetch(PDO::FETCH_ASSOC);
        if ($r4) {
            $res['total_raps'] = (int)$r4['en_malla'] + (int)$r4['sin_programa'];
            $res['raps_sin_programa'] = (int)$r4['sin_programa'];
        }

        return $res;
    }

    public function getDistTipos(){
        $sql = "SELECT v.nomval, COUNT(px.idcom) AS total, COALESCE(SUM(c.horcom), 0) AS total_horas
                FROM proxcom px
                INNER JOIN competencia c ON px.idcom = c.idcom
                INNER JOIN valor v ON c.idval = v.idval
                GROUP BY v.idval, v.nomval
                ORDER BY total DESC";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $stmt = $conexion->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function existeVinculacion($codpro, $idcom){
        $sql = "SELECT codpro FROM proxcom WHERE codpro = :codpro AND idcom = :idcom";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(":codpro", $codpro);
        $stmt->bindParam(":idcom", $idcom);
        $stmt->execute();
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        return !empty($r);
    }
}
class_alias('Hormpgcm', 'Mprxcm');
?>
