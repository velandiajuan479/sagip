<?php
require_once "models/conexion.php";

class Votmact{

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

    // Centros de formación para filtros
    public function getCen(){
        $sql = "SELECT idcen, nomcen FROM centro";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->execute();
        $res = $result->fetchall(PDO::FETCH_ASSOC);
        foreach ($res as &$row) {
            if (isset($row['nomcen'])) {
                $row['nomcen'] = $this->fixUtf8($row['nomcen']);
            }
        }
        return $res;
    }

    // Jornadas para filtros
    public function getJor(){
        try {
            $sql = "SELECT idval, nomval FROM valor WHERE iddom=1";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->execute();
            $res = $result->fetchall(PDO::FETCH_ASSOC);
            foreach ($res as &$row) {
                if (isset($row['nomval'])) {
                    $row['nomval'] = $this->fixUtf8($row['nomval']);
                }
            }
            return $res;
        } catch(Exception $e) {
            return [];
        }
    }

    // Fichas filtradas opcionalmente por jornada
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

    // Obtener candidatos a representantes ordenados por votos
    public function selAll($fidcen = null, $fidjor = null){
        try{
            $sql = "SELECT u.idusu, u.ndocusu, u.nomusu, up.idper, f.jornada, u.emausu, u.idcen, c.nomcen, u.actusu, u.fotcan, u.telcan, u.noca 
                    FROM usuario AS u 
                    LEFT JOIN usupef AS up ON u.idusu=up.idusu 
                    LEFT JOIN usufic AS uf ON u.idusu=uf.idusu 
                    LEFT JOIN ficha as f ON f.idfic=uf.idfic 
                    LEFT JOIN centro AS c ON u.idcen=c.idcen 
                    LEFT JOIN valor AS v ON f.jornada=v.idval 
                    WHERE up.idper=3";

            if ($fidcen) {
                $sql .= " AND u.idcen = :fidcen";
            }
            if ($fidjor) {
                $sql .= " AND f.jornada = :fidjor";
            }

            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            
            if ($fidcen) {
                $result->bindParam(':fidcen', $fidcen);
            }
            if ($fidjor) {
                $result->bindParam(':fidjor', $fidjor);
            }

            $result->execute();
            $res = $result->fetchall(PDO::FETCH_ASSOC);
            
            foreach ($res as $key => $row) {
                $votos = $this->nvoCan($row['idusu']);
                $res[$key]['total_votos'] = isset($votos[0]['nvo']) ? (int)$votos[0]['nvo'] : 0;
                $res[$key]['nomusu'] = $this->fixUtf8($row['nomusu']);
            }
            
            usort($res, function($a, $b) {
                return $b['total_votos'] <=> $a['total_votos'];
            });

            return $res;
        } catch(Exception $e){
            return [];
        }
    }

    // Detalle de ficha e instructor para acta de voceros
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

    // Voceros electos por ficha con votos consolidados
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

    // Lista de aprendices por ficha (incluye todos los matriculados en la ficha con datos completos para GFPI-F-121)
    public function getAprendicesPorFicha($idfic) {
        $sql = "SELECT u.idusu, u.ndocusu, u.nomusu, u.emausu, u.telcan, u.tdousu,
                       COALESCE(vtd.nomval, 'CC') AS tipodoc,
                       COALESCE(vg.nomval, 'M') AS genero,
                       f.idfic, f.nomfic,
                       COALESCE(vj.nomval, 'N/A') AS jornada,
                       COALESCE(vniv.nomval, 'Tecnólogo') AS nivel_formacion,
                       'Presencial' AS modalidad,
                       COALESCE(c.nomcen, 'Centro de Desarrollo Agroempresarial') AS nomcen
                FROM usuario u
                INNER JOIN usufic uf ON u.idusu = uf.idusu
                INNER JOIN ficha f ON uf.idfic = f.idfic
                LEFT JOIN programa p ON f.codpro = p.codpro
                LEFT JOIN valor vniv ON p.tippro = vniv.idval
                LEFT JOIN centro c ON f.idcen = c.idcen
                LEFT JOIN valor vj ON f.jornada = vj.idval
                LEFT JOIN valor vtd ON u.tdousu = vtd.idval
                LEFT JOIN valor vg ON u.genusu = vg.idval
                WHERE uf.idfic = :idfic
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

    // Votos por candidato
    public function nvoCan($canusu){
        try {
            $sql = "SELECT count(idusu) AS nvo FROM voto WHERE canusu=:canusu";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->bindParam(":canusu", $canusu);
            $result->execute();
            $res = $result->fetchall(PDO::FETCH_ASSOC);
            return $res;
        } catch(Exception $e){
            return [['nvo' => 0]];
        }
    }
}
?>
