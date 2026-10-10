<?php
class Mnvot{
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
        $this->idfic= $idfic;
    }
	public function setIdusu($idusu){
        $this->idusu= $idusu;
    }
	public function setActusu($actusu){
        $this->actusu= $actusu;
    }

		

    public function getAll(){
        $sql = "SELECT u.idusu, u.ndocusu, u.nomusu, u.idper, p.nomper, f.idfic,
        f.nomfic, v.nomval, u.pasusu, u.emausu, u.idcen, c.nomcen, u.actusu, u.fotcan, 
        u.telcan, u.noca,
        CASE WHEN vo.idusu IS NOT NULL THEN 1 ELSE 0 END AS votado
        FROM usuario AS u 
        INNER JOIN perfil AS p ON u.idper = p.idper 
        LEFT JOIN centro AS c ON u.idcen = c.idcen 
        LEFT JOIN ficha AS f ON u.idusu = f.idfic 
        LEFT JOIN valor AS v ON f.jornada = v.idval 
        LEFT JOIN (SELECT DISTINCT idusu FROM voto) AS vo ON u.idusu = vo.idusu
        WHERE (u.idper = 4 OR u.idper = 3 OR u.idper = 13)
        AND (u.noca <> '999' OR u.noca IS NULL)
        AND UPPER(u.nomusu) NOT LIKE '%BLANCO%'
        ORDER BY u.nomusu ASC";

        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->execute();
        $res = $result->fetchall(PDO::FETCH_ASSOC);
        return $res;
    }

	function editAct(){
        $sql = "UPDATE usuario SET actusu= :actusu WHERE idusu= :idusu";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $idusu = $this->getIdusu();
        $result->bindParam(":idusu",$idusu);
        $actusu = $this->getActusu();
        $result->bindParam(":actusu",$actusu);
        $result->execute();
    }

	function edit(){
        try{
            $sql = "UPDATE usuario SET actusu = :actusu WHERE idusu = :idusu";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $actusu = $this->getActusu();
            $result->bindParam(":actusu",$actusu);
			$idusu = $this->getIdusu();
            $result->bindParam(":idusu",$idusu);
            $result->execute();

            $idfic = $this->getIdfic();
            if ($idfic) {
                $sqlFic = "UPDATE usufic SET idfic = :idfic WHERE idusu = :idusu AND actfic = '1'";
                $stmtFic = $conexion->prepare($sqlFic);
                $stmtFic->bindParam(":idfic", $idfic);
                $stmtFic->bindParam(":idusu", $idusu);
                $stmtFic->execute();
            }
        }catch(Exception $e){
            ManejoError($e);
        }
    }
	function save(){
        try {
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $idusu = $this->getIdusu();
            $check = $conexion->prepare("SELECT COUNT(*) FROM voto WHERE idusu = :idusu AND tipo_voto = 'representante'");
            $check->bindParam(":idusu", $idusu);
            $check->execute();
            if ($check->fetchColumn() > 0) {
                return false;
            }
            $sql = "INSERT INTO voto(idusu, canusu, dtvot, tipo_voto) VALUES(:idusu, 0, NOW(), 'representante')";
            $result = $conexion->prepare($sql);
            $result->bindParam(":idusu", $idusu);        
            $result->execute();
            return true;
        } catch(Exception $e) {
            error_log("Error al guardar voto: " . $e->getMessage());
            return false;
        }
    }
    public function getGraphic(){
        $sql = "SELECT 
            SUM(CASE WHEN vo.idusu IS NULL THEN 1 ELSE 0 END) AS no_votaron,
            SUM(CASE WHEN vo.idusu IS NOT NULL AND (can.noca = '999' OR can.noca = 999 OR UPPER(can.nomusu) LIKE '%BLANCO%' OR vo.canusu = 0 OR vo.canusu IS NULL) THEN 1 ELSE 0 END) AS votos_blanco,
            SUM(CASE WHEN vo.idusu IS NOT NULL AND NOT (can.noca = '999' OR can.noca = 999 OR UPPER(can.nomusu) LIKE '%BLANCO%' OR vo.canusu = 0 OR vo.canusu IS NULL) THEN 1 ELSE 0 END) AS votaron,
            COUNT(*) AS total_personas
            FROM usuario AS u 
            LEFT JOIN (
                SELECT v.idusu, 
                       COALESCE(
                           MAX(CASE WHEN (can_v.noca <> '999' AND can_v.noca <> 999 AND UPPER(can_v.nomusu) NOT LIKE '%BLANCO%' AND v.canusu > 0) THEN v.canusu END),
                           MAX(v.canusu)
                       ) AS canusu,
                       MAX(v.dtvot) AS dtvot 
                FROM voto AS v
                LEFT JOIN usuario AS can_v ON v.canusu = can_v.idusu
                GROUP BY v.idusu
            ) AS vo ON u.idusu = vo.idusu
            LEFT JOIN usuario AS can ON vo.canusu = can.idusu
            WHERE (u.idper = 4 OR u.idper = 3 OR u.idper = 13)
            AND (u.noca <> '999' OR u.noca IS NULL)
            AND UPPER(u.nomusu) NOT LIKE '%BLANCO%'";
        
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->execute();
        $res = $result->fetchall(PDO::FETCH_ASSOC);

        if ($res && isset($res[0])) {
            $res[0]['no_votaron'] = isset($res[0]['no_votaron']) ? (int)$res[0]['no_votaron'] : 0;
            $res[0]['votos_blanco'] = isset($res[0]['votos_blanco']) ? (int)$res[0]['votos_blanco'] : 0;
            $res[0]['blanco'] = $res[0]['votos_blanco'];
            $res[0]['votaron'] = isset($res[0]['votaron']) ? (int)$res[0]['votaron'] : 0;
            $res[0]['total_personas'] = isset($res[0]['total_personas']) ? (int)$res[0]['total_personas'] : 0;
        } else {
            $res = [[
                'no_votaron' => 0,
                'votos_blanco' => 0,
                'blanco' => 0,
                'votaron' => 0,
                'total_personas' => 0
            ]];
        }

        return $res;
    }

    public function getVotosBlanco() {
        $sql = "SELECT COUNT(*) AS total_blanco
                FROM voto AS v
                LEFT JOIN usuario AS can ON v.canusu = can.idusu
                WHERE (can.noca = '999' OR can.noca = 999 OR UPPER(can.nomusu) LIKE '%BLANCO%' OR v.canusu = 0 OR v.canusu IS NULL)";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->execute();
        $res = $result->fetch(PDO::FETCH_ASSOC);
        return isset($res['total_blanco']) ? (int)$res['total_blanco'] : 0;
    }
	
    public function getVotU($idusu) {
        $sql = "SELECT idusu FROM voto WHERE idusu=:idusu";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->bindParam(":idusu", $idusu);
        $result->execute();
        $res = $result->fetch(PDO::FETCH_ASSOC);
        // Si el usuario ha votado, devolvemos true, de lo contrario devolvemos false
        return $res ? true : false;
    }
    
}
?>