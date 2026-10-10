<?php
require_once "models/conexion.php";

class Votmrvo{

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

    // Obtiene centros de formacion para el filtro
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

    // Obtiene jornadas para el filtro
    public function getJor(){
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
    }

    // Selecciona todos los candidatos a representantes
    public function selAll($jornada, $idcen){
        $sql = "SELECT u.idusu, u.ndocusu, u.nomusu, f.idfic, f.nomfic, c.nomcen, u.actusu, u.fotcan, v.nomval, u.emausu, u.noca 
                FROM usuario AS u 
                LEFT JOIN usupef AS up ON u.idusu=up.idusu 
                INNER JOIN usufic AS uf ON u.idusu=uf.idusu 
                INNER JOIN ficha AS f ON uf.idfic=f.idfic 
                INNER JOIN centro AS c ON f.idcen=c.idcen 
                INNER JOIN valor AS v ON f.jornada=v.idval 
                WHERE f.jornada=:jornada AND c.idcen=:idcen AND up.idper=3;";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->bindParam(":jornada", $jornada);
        $result->bindParam(":idcen", $idcen);
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

    // Obtener numero de votos por candidato
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
