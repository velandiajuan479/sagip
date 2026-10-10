<?php
require_once 'conexion.php';

class Votmctele {
    private $idusu;
    private $idfic;
    private $idcen;
    private $tipo_voto;

    public function getIdusu() { return $this->idusu; }
    public function getIdfic() { return $this->idfic; }
    public function getIdcen() { return $this->idcen; }
    public function getTipoVoto() { return $this->tipo_voto; }

    public function setIdusu($idusu) { $this->idusu = $idusu; }
    public function setIdfic($idfic) { $this->idfic = $idfic; }
    public function setIdcen($idcen) { $this->idcen = $idcen; }
    public function setTipoVoto($tipo_voto) { $this->tipo_voto = $tipo_voto; }

    public function getCandidatosRepresentante($idfic = null, $idcen = null) {
        $sql = "SELECT u.idusu, u.ndocusu, u.nomusu, u.noca, u.fotcan, u.emausu, u.telcan, 
                       f.idfic, f.nomfic, v.nomval as jornada, c.nomcen
                FROM usuario u
                INNER JOIN usupef up ON u.idusu = up.idusu
                INNER JOIN perfil p ON up.idper = p.idper
                INNER JOIN centro c ON u.idcen = c.idcen
                LEFT JOIN usufic uf ON u.idusu = uf.idusu AND uf.actfic = 1
                LEFT JOIN ficha f ON uf.idfic = f.idfic
                LEFT JOIN valor v ON f.jornada = v.idval
                WHERE up.idper = 3
                AND u.actusu = 1";
        
        $params = [];
        if ($idfic) {
            $sql .= " AND uf.idfic = :idfic";
            $params[':idfic'] = $idfic;
        } elseif ($idcen) {
            $sql .= " AND u.idcen = :idcen";
            $params[':idcen'] = $idcen;
        }
        $sql .= " GROUP BY u.idusu ORDER BY u.noca";

        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        foreach ($params as $key => $val) {
            $result->bindValue($key, $val);
        }
        $result->execute();
        return $result->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCandidatosVocero($idfic = null, $idcen = null) {
        $sql = "SELECT u.idusu, u.ndocusu, u.nomusu, u.noca, u.fotcan, u.emausu, u.telcan, 
                       f.idfic, f.nomfic, v.nomval as jornada, c.nomcen
                FROM usuario u
                INNER JOIN usupef up ON u.idusu = up.idusu
                INNER JOIN perfil p ON up.idper = p.idper
                INNER JOIN centro c ON u.idcen = c.idcen
                LEFT JOIN usufic uf ON u.idusu = uf.idusu AND uf.actfic = 1
                LEFT JOIN ficha f ON uf.idfic = f.idfic
                LEFT JOIN valor v ON f.jornada = v.idval
                WHERE up.idper = 13
                AND u.actusu = 1";
        
        $params = [];
        if ($idfic) {
            $sql .= " AND uf.idfic = :idfic";
            $params[':idfic'] = $idfic;
        } elseif ($idcen) {
            $sql .= " AND u.idcen = :idcen";
            $params[':idcen'] = $idcen;
        }
        $sql .= " GROUP BY u.idusu ORDER BY u.noca";

        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        foreach ($params as $key => $val) {
            $result->bindValue($key, $val);
        }
        $result->execute();
        return $result->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getFichasByCentro($idcen) {
        $sql = "SELECT f.idfic, f.nomfic, v.nomval 
                FROM ficha AS f 
                INNER JOIN valor AS v ON f.jornada = v.idval 
                WHERE f.idcen = :idcen 
                ORDER BY f.idfic";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->bindParam(":idcen", $idcen);
        $result->execute();
        return $result->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCentros() {
        $sql = "SELECT idcen, nomcen FROM centro ORDER BY nomcen";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->execute();
        return $result->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getFichaUsuario($idusu) {
        $sql = "SELECT uf.idfic FROM usufic uf WHERE uf.idusu = :idusu AND uf.actfic = 1 LIMIT 1";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->bindParam(":idusu", $idusu);
        $result->execute();
        $res = $result->fetch(PDO::FETCH_ASSOC);
        return $res ? $res['idfic'] : null;
    }

    public function getVotoUsuario($idusu, $tipo_voto) {
        $sql = "SELECT v.*, u.nomusu as candidato_nombre, u.noca as candidato_numero, u.fotcan as candidato_foto
                FROM voto v
                LEFT JOIN usuario u ON v.canusu = u.idusu
                WHERE v.idusu = :idusu AND v.tipo_voto = :tipo_voto
                ORDER BY v.dtvot DESC LIMIT 1";
        
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->bindParam(":idusu", $idusu, PDO::PARAM_INT);
        $result->bindParam(":tipo_voto", $tipo_voto);
        $result->execute();
        return $result->fetch(PDO::FETCH_ASSOC);
    }

    public function getResultadosGenerales($tipo_voto, $idcen = null) {
        $sql = "SELECT u.idusu, u.nomusu, u.noca, u.fotcan, f.nomfic, c.nomcen, COUNT(v.id) as total_votos
                FROM usuario u
                INNER JOIN usupef up ON u.idusu = up.idusu
                INNER JOIN centro c ON u.idcen = c.idcen
                LEFT JOIN usufic uf ON u.idusu = uf.idusu
                LEFT JOIN ficha f ON uf.idfic = f.idfic
                LEFT JOIN voto v ON v.canusu = u.idusu AND v.tipo_voto = :tipo_voto
                WHERE up.idper = :idper
                AND u.actusu = 1";
        
        $params = [
            ':tipo_voto' => $tipo_voto,
            ':idper' => $tipo_voto === 'representante' ? 3 : 13
        ];
        
        if ($idcen) {
            $sql .= " AND u.idcen = :idcen";
            $params[':idcen'] = $idcen;
        }
        
        $sql .= " GROUP BY u.idusu, u.nomusu, u.noca, u.fotcan, f.nomfic, c.nomcen
                  ORDER BY total_votos DESC, u.noca";

        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        foreach ($params as $key => $val) {
            $result->bindValue($key, $val);
        }
        $result->execute();
        return $result->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getConfiguracionCentro($idcen) {
        $sql = "SELECT fiicancen, fficancen, fiprocen, ffprocen, fivotcen, ffvotcen 
                FROM centro WHERE idcen = :idcen";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->bindParam(":idcen", $idcen, PDO::PARAM_INT);
        $result->execute();
        return $result->fetch(PDO::FETCH_ASSOC);
    }

    public function getVotosByCandidato($idcandidato) {
        $sql = "SELECT COUNT(*) as total FROM voto WHERE canusu = :canusu AND tipo_voto IN ('representante', 'vocero')";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->bindParam(":canusu", $idcandidato, PDO::PARAM_INT);
        $result->execute();
        $res = $result->fetch(PDO::FETCH_ASSOC);
        return $res['total'] ?? 0;
    }

    public function verificarPeriodoVotacion($idcen, $tipo = 'votacion') {
        $config = $this->getConfiguracionCentro($idcen);
        if (!$config) return false;
        
        $ahora = new DateTime();
        $inicio = $tipo === 'votacion' ? new DateTime($config['fivotcen']) : 
                  ($tipo === 'candidatos' ? new DateTime($config['fiicancen']) : 
                  ($tipo === 'propuestas' ? new DateTime($config['fiprocen']) : $ahora));
        $fin = $tipo === 'votacion' ? new DateTime($config['ffvotcen']) : 
               ($tipo === 'candidatos' ? new DateTime($config['fficancen']) : 
               ($tipo === 'propuestas' ? new DateTime($config['ffprocen']) : $ahora));
        
        return $ahora >= $inicio && $ahora <= $fin;
    }
}