<?php
require_once "models/conexion.php";

class Votmrcv{

    private function fixUtf8($str) {
        if (!is_string($str)) return $str;
        if (strpos($str, 'Ã') !== false) {
            $decoded = utf8_decode($str);
            if (mb_check_encoding($decoded, 'UTF-8')) {
                return $decoded;
            }
        }
        return mb_check_encoding($str, 'UTF-8') ? $str : utf8_encode($str);
    }

    // Obtiene jornadas para el filtro
    public function getJor(){
        $sql = "SELECT idval, nomval FROM valor WHERE iddom=1";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->execute();
        $res = $result->fetchall(PDO::FETCH_ASSOC);
        foreach ($res as &$row) {
            $row['nomval'] = utf8_decode($row['nomval']);
        }
        return $res;
    }

    // Metodo para obtener todas las fichas disponibles, opcionalmente filtradas por jornada
    public function getFichas($jornada = null) {
        $sql = "SELECT DISTINCT f.idfic, f.nomfic, v.nomval 
                FROM ficha f 
                JOIN valor v ON f.jornada = v.idval ";
        if ($jornada) {
            $sql .= " WHERE f.jornada = :jornada ";
        }
        $sql .= " ORDER BY f.idfic";
        
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        if ($jornada) {
            $result->bindParam(":jornada", $jornada);
        }
        $result->execute();
        $res = $result->fetchall(PDO::FETCH_ASSOC);
        
        foreach ($res as &$row) {
            $row['nomval'] = $this->fixUtf8($row['nomval']);
            $row['nomfic'] = $this->fixUtf8($row['nomfic']);
        }
        return $res;
    }

    // Metodo para obtener candidatos voceros por ficha con datos completos de contacto y tipo de documento
    public function getVocerosElectosPorFicha($idfic) {
        $sql = "SELECT u.idusu, u.ndocusu, u.nomusu, u.fotcan, u.emausu, u.telcan, u.tdousu,
                    COALESCE(vtd.nomval, 'CC') AS tipodoc,
                    f.idfic, f.nomfic, v.nomval AS nomjor,
                    f.idusu AS idins, ins.nomusu AS nomins, ins.emausu AS emains, ins.ndocusu AS ndocins,
                    COUNT(vo.idusu) AS total_votos
                FROM usuario AS u 
                INNER JOIN usufic AS uf ON u.idusu = uf.idusu
                INNER JOIN ficha AS f ON uf.idfic = f.idfic
                INNER JOIN valor AS v ON f.jornada = v.idval
                INNER JOIN usupef AS up ON u.idusu = up.idusu
                LEFT JOIN valor AS vtd ON u.tdousu = vtd.idval
                LEFT JOIN usuario AS ins ON f.idusu = ins.idusu
                LEFT JOIN voto AS vo ON u.idusu = vo.canusu
                WHERE uf.idfic = :idfic
                AND uf.actfic = 1
                AND up.idper IN (3, 13)
                GROUP BY u.idusu, u.ndocusu, u.nomusu, u.fotcan, u.emausu, u.telcan, u.tdousu, vtd.nomval, f.idfic, f.nomfic, v.nomval, f.idusu, ins.nomusu, ins.emausu, ins.ndocusu
                ORDER BY total_votos DESC, u.nomusu ASC";
        
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->bindParam(":idfic", $idfic);
        $result->execute();
        $res = $result->fetchall(PDO::FETCH_ASSOC);

        foreach ($res as &$row) {
            foreach ($row as $k => $v) {
                if (is_string($v)) {
                    $row[$k] = $this->fixUtf8($v);
                }
            }
        }
        return $res;
    }

    // Metodo para obtener detalle de una ficha e instructor lider
    public function getFichaDetalle($idfic) {
        $sql = "SELECT f.idfic, f.nomfic, f.jornada, v.nomval AS nomjor,
                       f.idusu AS idins, ins.nomusu AS nomins, ins.emausu AS emains, ins.ndocusu AS ndocins
                FROM ficha f
                INNER JOIN valor v ON f.jornada = v.idval
                LEFT JOIN usuario ins ON f.idusu = ins.idusu
                WHERE f.idfic = :idfic";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(":idfic", $idfic);
        $stmt->execute();
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($r) {
            foreach ($r as $k => $v) {
                if (is_string($v)) {
                    $r[$k] = $this->fixUtf8($v);
                }
            }
        }
        return $r;
    }

    // Metodo para obtener aprendices inscritos en la ficha (para formato de asistencia/participacion GFPI-F-121)
    public function getAprendicesPorFicha($idfic) {
        $sql = "SELECT u.idusu, u.ndocusu, u.nomusu, u.emausu, u.telcan, u.tdousu,
                       COALESCE(vtd.nomval, 'CC') AS tipodoc,
                       COALESCE(vg.nomval, 'N/A') AS genero,
                       f.idfic, f.nomfic, vj.nomval AS jornada
                FROM usuario u
                INNER JOIN usufic uf ON u.idusu = uf.idusu
                INNER JOIN ficha f ON uf.idfic = f.idfic
                INNER JOIN valor vj ON f.jornada = vj.idval
                LEFT JOIN valor vtd ON u.tdousu = vtd.idval
                LEFT JOIN valor vg ON u.genusu = vg.idval
                WHERE uf.idfic = :idfic
                AND uf.actfic = 1
                ORDER BY u.nomusu ASC";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(":idfic", $idfic);
        $stmt->execute();
        $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($res as &$row) {
            foreach ($row as $k => $v) {
                if (is_string($v)) {
                    $row[$k] = $this->fixUtf8($v);
                }
            }
        }
        return $res;
    }

    // Obtener numero de votos por candidato individual (Legacy fallback)
    public function nvoCan($canusu){
        $sql = "SELECT count(idusu) AS nvo FROM voto WHERE canusu=:canusu";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->bindParam(":canusu", $canusu);
        $result->execute();
        $res = $result->fetchall(PDO::FETCH_ASSOC);
        return $res;
    }
}
