<?php
class Mtots {
    private $totfic;
    private $totusu;
    private $ficusu;
    private $totasig;
    private $idfic;
    private $usuforfic;

    //METODOS GET
    public function getTotfic(){
        return $this->totfic;
    }

    public function getTotusu(){
        return $this->totusu;
    }

    public function getFicusu(){
        return $this->ficusu;
    }

    public function getTotasig(){
        return $this->totasig;
    }

    public function getIdfic(){
        return $this->idfic;
    }

    public function getUsuforfic(){
        return $this->usuforfic;
    }

    //METODOS SET
    public function setTotfic($totfic){
        $this->totfic = $totfic;
    }

    public function setTotusu($totusu){
        $this->totusu = $totusu;
    }

    public function setFicusu($ficusu){
        $this->ficusu = $ficusu;
    }

    public function setTotasig($totasig){
        $this->totasig = $totasig;
    }

    public function setIdfic($idfic){
        $this->idfic = $idfic;
    }

    public function setUsuforfic($usuforfic){
        $this->usuforfic = $usuforfic;
    }


    //FUNCIONES 

    public function getTotfics(){
        $sql = "SELECT COUNT(*) FROM ficha";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result-> execute();
        $res = $result->fetch(PDO::FETCH_ASSOC);
        return $res;
    }

    public function getTotusus(){
        $sql = "SELECT COUNT(*) FROM usuario";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result-> execute();
        $res = $result->fetch(PDO::FETCH_ASSOC);
        return $res;
    }

    public function getTotUsuForFic(){
        $sql = "SELECT f.idfic, COUNT(DISTINCT uf.idusu) AS usuarios_por_ficha
            FROM ficha f
            LEFT JOIN usufic uf ON f.idfic = uf.idfic
            GROUP BY f.idfic";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->execute();
        $res = $result->fetchAll(PDO::FETCH_ASSOC);
        return $res;
    }

    public function getTotUsuForFicId($idUsuario){
        $sql = "SELECT f.idfic, COUNT(DISTINCT uf.idusu) AS usuarios_por_ficha
                FROM ficha f
                LEFT JOIN usufic uf ON f.idfic = uf.idfic
                WHERE f.idusu = :idUsuario
                GROUP BY f.idfic";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->bindParam(':idUsuario', $idUsuario, PDO::PARAM_INT);
        $result->execute();
        $res = $result->fetchAll(PDO::FETCH_ASSOC);
        return $res;
    }

    public function getCantUsuFic($idusu) {
        $sql = "SELECT COUNT(DISTINCT idusu) AS cantidad_usuarios_ficha
                FROM usufic
                WHERE idfic IN (
                    SELECT idfic FROM usufic WHERE idusu = :idusu
                )";

        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->bindParam(":idusu", $idusu);
        $result->execute();
        $res = $result->fetch(PDO::FETCH_ASSOC);
        
        return $res ? $res['cantidad_usuarios_ficha'] : 0;
    }

    
    public function getTotInst() {
        $sql = "SELECT COUNT(*) AS instructores FROM usuario WHERE idper = 7";
    
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->execute();
        $res = $result->fetch(PDO::FETCH_ASSOC);
    
        return $res ? $res['instructores'] : 0;
    }

    public function getTotEle() {
    $sql = "SELECT COUNT(*) AS total_elemento FROM elemento";

    $modelo = new conexion();
    $conexion = $modelo->get_conexion();
    $result = $conexion->prepare($sql);
    $result->execute();
    $res = $result->fetch(PDO::FETCH_ASSOC);

    return $res ? $res['total_elemento'] : 0;
    }

    public function getTotPresEle() {
    $sql = "SELECT COUNT(*) AS total_presele FROM presele";

    $modelo = new conexion();
    $conexion = $modelo->get_conexion();
    $result = $conexion->prepare($sql);
    $result->execute();
    $res = $result->fetch(PDO::FETCH_ASSOC);

    return $res ? $res['total_presele'] : 0;
    }


    public function getCantUsuPer() {
    $sql = "SELECT COUNT(*) AS cantidad_usuarios
            FROM usuario u
            JOIN perfil p ON u.idper = p.idper
            WHERE p.idper = 3";

    $modelo = new conexion();
    $conexion = $modelo->get_conexion();
    $result = $conexion->prepare($sql);
    $result->execute();
    $res = $result->fetch(PDO::FETCH_ASSOC);

    return $res ? $res['cantidad_usuarios'] : 0;
    }

    public function getCantUsuPerJor() {
    $sql = "SELECT f.jornada, COUNT(u.idusu) AS cantidad_usuarios
            FROM usuario u
            JOIN usufic uf ON u.idusu = uf.idusu
            JOIN ficha f ON uf.idfic = f.idfic
            WHERE u.idper = 3
            GROUP BY f.jornada";

    $modelo = new conexion();
    $conexion = $modelo->get_conexion();
    $result = $conexion->prepare($sql);
    $result->execute();
    $res = $result->fetchAll(PDO::FETCH_ASSOC);

    return $res ? $res : [];
    }

    public function getVotTot($idusu) {
        $sql = "WITH usuario_jornada AS (
            SELECT f.jornada
            FROM usufic uf
            JOIN ficha f ON uf.idfic = f.idfic
            WHERE uf.idusu = :idusu
            LIMIT 1
        ),
        votos_jornada AS (
            SELECT
                v.canusu,
                COUNT(*) AS total_votos
            FROM voto v
            JOIN usufic uf ON v.canusu = uf.idusu
            JOIN ficha f ON uf.idfic = f.idfic
            JOIN usuario_jornada uj ON f.jornada = uj.jornada
            GROUP BY v.canusu
        ),
        mas_votado AS (
            SELECT
                vj.canusu,
                u.nomusu,
                vj.total_votos
            FROM votos_jornada vj
            JOIN usuario u ON vj.canusu = u.idusu
            ORDER BY vj.total_votos DESC
            LIMIT 1
        )
    
        SELECT
            mv.nomusu AS nombre_mas_votado,
            mv.total_votos,
            NULL AS total_candidatos,
            uj.jornada
        FROM mas_votado mv
        JOIN usuario_jornada uj ON 1=1
        WHERE EXISTS (
            SELECT 1 FROM voto WHERE idusu = :idusu
        )
    
        UNION
    
        SELECT
            NULL AS nombre_mas_votado,
            NULL AS total_votos,
            COUNT(DISTINCT v.canusu) AS total_candidatos,
            uj.jornada
        FROM voto v
        JOIN usufic uf ON v.canusu = uf.idusu
        JOIN ficha f ON uf.idfic = f.idfic
        JOIN usuario_jornada uj ON f.jornada = uj.jornada
        WHERE NOT EXISTS (
            SELECT 1 FROM voto WHERE idusu = :idusu
        );";
    
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->bindParam(':idusu', $idusu, PDO::PARAM_INT);
        $result->execute();
        $res = $result->fetchAll(PDO::FETCH_ASSOC);
    
        return $res ? $res : [];
    }
    
    public function getCantIna() {
        $sql = "SELECT COUNT(*) AS total_inasistencias FROM inasistencia";
    
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->execute();
        $res = $result->fetch(PDO::FETCH_ASSOC);
    
        return $res ? $res['total_inasistencias'] : 0;
    }

    public function getCantBi() {
        $sql = "SELECT COUNT(*) AS total_bitacoras FROM bitacora";
        
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->execute();
        $res = $result->fetch(PDO::FETCH_ASSOC);
        
        return $res ? $res['total_bitacoras'] : 0;
    }
    
    public function getTotalApro() {
        $sql = "SELECT COUNT(*) AS cantidad FROM aprobacion_resultado";
        
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->execute();
        $res = $result->fetch(PDO::FETCH_ASSOC);
        
        return $res ? $res['cantidad'] : 0;
    }
    

    public function getTotAproApro() {
        $sql = "SELECT COUNT(*) AS total_aprobados FROM aprobacion_resultado WHERE estado = 'aprobado'";
        
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->execute();
        $res = $result->fetch(PDO::FETCH_ASSOC);
        
        return $res ? $res['total_aprobados'] : 0;
    }
    

    public function getTotalAspirantes() {
        try {
            $sql = "SELECT COUNT(*) AS total_aspirantes 
                    FROM usuario 
                    WHERE idper IN (3, 4)";
        
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->execute();
            $res = $result->fetch(PDO::FETCH_ASSOC);
        
            return $res ? $res['total_aspirantes'] : 0;
        } catch (Exception $e) {
            error_log("Error en getTotalAspirantes: " . $e->getMessage());
            return 0;
        }
    }

    
    public function getPersonasDocumentosCompletos() {
        try {
            $sql = "SELECT COUNT(*) AS personas_documentos_completos
                    FROM usuario u
                    WHERE u.idper IN (3,4)
                    AND NOT EXISTS (
                        SELECT 1
                        FROM docped d
                        WHERE d.act = 1
                        AND NOT EXISTS (
                            SELECT 1
                            FROM docmat dm
                            WHERE dm.iduxf = u.idusu
                            AND dm.iddocp = d.iddocp
                            AND dm.aprdcma = 1
                        )
                    )";
    
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->execute();
            $res = $result->fetch(PDO::FETCH_ASSOC);
    
            return $res ? $res['personas_documentos_completos'] : 0;
        } catch (Exception $e) {
            error_log("Error en getPersonasDocumentosCompletos: " . $e->getMessage());
            return 0;
        }
    }

    
    public function getPersonasFaltanDocumentos() {
        try {
            $sql = "SELECT COUNT(*) AS personas_faltan_documentos
                    FROM usuario u
                    WHERE u.idper IN (3,4)
                    AND EXISTS (
                        SELECT 1
                        FROM docped d
                        WHERE d.act = 1
                        AND NOT EXISTS (
                            SELECT 1
                            FROM docmat dm
                            WHERE dm.iduxf = u.idusu
                            AND dm.iddocp = d.iddocp
                            AND dm.aprdcma = 1
                        )
                    )";
    
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->execute();
            $res = $result->fetch(PDO::FETCH_ASSOC);
    
            return $res ? $res['personas_faltan_documentos'] : 0;
        } catch (Exception $e) {
            error_log("Error en getPersonasFaltanDocumentos: " . $e->getMessage());
            return 0;
        }
    }

    public function getTotalSoportesSolicitados() {
        try {
            $sql = "SELECT COUNT(*) AS total_solicitados FROM soporte";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->execute();
            $res = $result->fetch(PDO::FETCH_ASSOC);
            return $res ? $res['total_solicitados'] : 0;
        } catch (Exception $e) {
            error_log("Error en getTotalSoportesSolicitados: " . $e->getMessage());
            return 0;
        }
    }


    public function getTotalSoportesSolucionados() {
        try {
            $sql = "SELECT COUNT(*) AS total_solucionados
                    FROM soporte s
                    INNER JOIN (
                        SELECT idsop, MAX(fecseg) AS max_fecha
                        FROM detalle_soporte
                        GROUP BY idsop
                    ) ds_max ON s.idsop = ds_max.idsop
                    INNER JOIN detalle_soporte ds ON ds.idsop = ds_max.idsop AND ds.fecseg = ds_max.max_fecha
                    WHERE ds.detest = 1074";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->execute();
            $res = $result->fetch(PDO::FETCH_ASSOC);
            return $res ? $res['total_solucionados'] : 0;
        } catch (Exception $e) {
            error_log("Error en getTotalSoportesSolucionados: " . $e->getMessage());
            return 0;
        }
    }
}
?>