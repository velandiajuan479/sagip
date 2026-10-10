<?php 
class Hormprg{
    // Atributos
    private $codpro;
    private $nompro;
    private $despro;
    private $verpro;
    private $horlpro;
    private $horppro;
    private $crelpro;
    private $creppro;
    private $tippro;
    private $just;
    private $redcon;
    private $reqing;
    private $reqcer;
    private $idare;

    // Métodos GET
    public function getCodpro(){
        return $this->codpro;
    }
    public function getNompro(){
        return $this->nompro;
    }
    public function getDespro(){
        return $this->despro;
    }
    public function getVerpro(){
        return $this->verpro;
    }
    public function getHorlpro(){
        return $this->horlpro;
    }
    public function getHorppro(){
        return $this->horppro;
    }
    public function getCrelpro(){
        return $this->crelpro;
    }
    public function getCreppro(){
        return $this->creppro;
    }
    public function getTippro(){
        return $this->tippro;
    }
    public function getJust(){
        return $this->just;
    }
    public function getRedcon(){
        return $this->redcon;
    }
    public function getReqing(){
        return $this->reqing;
    }
    public function getReqcer(){
        return $this->reqcer;
    }
    public function getIdare(){
        return $this->idare;
    }

    // Métodos SET
    public function setCodpro($codpro){
        $this->codpro = $codpro;
    }
    public function setNompro($nompro){
        $this->nompro = $nompro;
    }
    public function setDespro($despro){
        $this->despro = $despro;
    }
    public function setVerpro($verpro){
        $this->verpro = $verpro;
    }
    public function setHorlpro($horlpro){
        $this->horlpro = $horlpro;
    }
    public function setHorppro($horppro){
        $this->horppro = $horppro;
    }
    public function setCrelpro($crelpro){
        $this->crelpro = $crelpro;
    }
    public function setCreppro($creppro){
        $this->creppro = $creppro;
    }
    public function setTippro($tippro){
        $this->tippro = $tippro;
    }
    public function setJust($just){
        $this->just = $just;
    }
    public function setRedcon($redcon){
        $this->redcon = $redcon;
    }
    public function setReqing($reqing){
        $this->reqing = $reqing;
    }
    public function setReqcer($reqcer){
        $this->reqcer = $reqcer;
    }
    public function setIdare($idare){
        $this->idare = $idare;
    }    // Métodos públicos existentes
    public function getAll(){
        $res = NULL;
        $sql = "SELECT p.codpro, p.nompro, p.despro, p.verpro, p.horlpro, p.horppro, p.crelpro, p.creppro, 
                       p.tippro, COALESCE(v.nomval, 'No especificado') AS nomval, p.just, p.redcon, p.reqing, p.reqcer, p.idare, a.nomare, a.idusu 
                FROM programa AS p 
                LEFT JOIN valor AS v ON p.tippro=v.idval 
                LEFT JOIN area AS a ON p.idare=a.idare 
                ORDER BY p.nompro ASC";
        $modelo = new Conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->execute();
        $res = $result->fetchall(PDO::FETCH_ASSOC);
        return $res;
    }

    // Obtener programas junto con conteo de competencias asociadas
    public function getAllWithCounts(){
        $res = NULL;
        $sql = "SELECT p.codpro, p.nompro, p.despro, p.verpro, p.horlpro, p.horppro, p.crelpro, p.creppro, 
                       p.tippro, COALESCE(v.nomval, 'No especificado') AS nomval, p.just, p.redcon, p.reqing, p.reqcer, p.idare, a.nomare, a.idusu,
                       COUNT(DISTINCT px.idcom) AS cant_comp,
                       COUNT(DISTINCT r.idres) AS cant_res,
                       COUNT(DISTINCT f.idfic) AS tot_fic,
                       COUNT(DISTINCT CASE WHEN f.ffinfic >= CURDATE() THEN f.idfic END) AS fic_vigentes
                FROM programa AS p 
                LEFT JOIN valor AS v ON p.tippro=v.idval 
                LEFT JOIN area AS a ON p.idare=a.idare
                LEFT JOIN proxcom AS px ON p.codpro = px.codpro
                LEFT JOIN resultado AS r ON px.idcom = r.idcom
                LEFT JOIN ficha AS f ON p.codpro = f.codpro
                GROUP BY p.codpro, p.nompro, p.despro, p.verpro, p.horlpro, p.horppro, p.crelpro, p.creppro, 
                         p.tippro, v.nomval, p.just, p.redcon, p.reqing, p.reqcer, p.idare, a.nomare, a.idusu
                ORDER BY p.nompro ASC";
        $modelo = new Conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->execute();
        $res = $result->fetchall(PDO::FETCH_ASSOC);
        return $res;
    }

    public function getOne(){
        $res = NULL;
        $sql = "SELECT p.codpro, p.nompro, p.despro, p.verpro, p.horlpro, p.horppro, p.crelpro, p.creppro, 
                       p.tippro, COALESCE(v.nomval, 'No especificado') AS nomval, p.just, p.redcon, p.reqing, p.reqcer, p.idare, a.nomare, a.idusu 
                FROM programa AS p 
                LEFT JOIN valor AS v ON p.tippro=v.idval 
                LEFT JOIN area AS a ON p.idare=a.idare 
                WHERE codpro=:codpro";
        $modelo = new Conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $codpro = $this->getCodpro();
        $result->bindParam(":codpro", $codpro);
        $result->execute();
        $res = $result->fetchall(PDO::FETCH_ASSOC);
        return $res;
    }

    public function getArea(){
        $res = NULL;
        $sql = "SELECT idare, nomare, idusu FROM area ORDER BY nomare ASC";
        $modelo = new Conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->execute();
        $res = $result->fetchall(PDO::FETCH_ASSOC);
        return $res;
    }

    public function getTipPr(){
        $res = NULL;
        $sql = "SELECT idval, nomval, act FROM valor WHERE iddom IN (16, 24) ORDER BY iddom ASC, nomval ASC";
        $modelo = new Conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->execute();
        $res = $result->fetchall(PDO::FETCH_ASSOC);
        return $res;
    }

    public function save(){
        try {
            $sql = "INSERT INTO programa (codpro, nompro, despro, verpro, horlpro, horppro, crelpro, creppro, tippro, just, redcon, reqing, reqcer, idare) 
                    VALUES (:codpro, :nompro, :despro, :verpro, :horlpro, :horppro, :crelpro, :creppro, :tippro, :just, :redcon, :reqing, :reqcer, :idare)";
            $modelo = new Conexion();

            $codpro = $this->getCodpro();
            $nompro = $this->getNompro();
            $despro = $this->getDespro();
            $verpro = (int)($this->getVerpro() ?: 1);
            $horlpro = (int)($this->getHorlpro() ?: 0);
            $horppro = (int)($this->getHorppro() ?: 0);
            $crelpro = (int)($this->getCrelpro() ?: 0);
            $creppro = (int)($this->getCreppro() ?: 0);
            $tippro = !empty($this->getTippro()) ? (int)$this->getTippro() : null;
            $just = $this->getJust();
            $redcon = $this->getRedcon();
            $reqing = $this->getReqing();
            $reqcer = $this->getReqcer();
            $idare = (int)($this->getIdare() ?: 0);

            $params = [
                ':codpro'  => $codpro,
                ':nompro'  => $nompro,
                ':despro'  => !empty($despro) ? $despro : null,
                ':verpro'  => $verpro,
                ':horlpro' => $horlpro,
                ':horppro' => $horppro,
                ':crelpro' => $crelpro,
                ':creppro' => $creppro,
                ':tippro'  => $tippro,
                ':just'    => !empty($just) ? $just : null,
                ':redcon'  => !empty($redcon) ? $redcon : null,
                ':reqing'  => !empty($reqing) ? $reqing : null,
                ':reqcer'  => !empty($reqcer) ? $reqcer : null,
                ':idare'   => $idare
            ];

            return $modelo->executeWrite($sql, $params);
        } catch (Exception $e) {
            if (session_status() === PHP_SESSION_ACTIVE) {
                $_SESSION['last_crud_error'] = $e->getMessage();
            }
            error_log("Error en Hormprg::save(): " . $e->getMessage());
            return false;
        }
    }

    public function edit(){
        try {
            $sql = "UPDATE programa SET nompro=:nompro, despro=:despro, verpro=:verpro, horlpro=:horlpro, 
                           horppro=:horppro, crelpro=:crelpro, creppro=:creppro, tippro=:tippro, 
                           just=:just, redcon=:redcon, reqing=:reqing, reqcer=:reqcer, idare=:idare 
                    WHERE codpro=:codpro";
            $modelo = new Conexion();

            $codpro = $this->getCodpro();
            $nompro = $this->getNompro();
            $despro = $this->getDespro();
            $verpro = (int)($this->getVerpro() ?: 1);
            $horlpro = (int)($this->getHorlpro() ?: 0);
            $horppro = (int)($this->getHorppro() ?: 0);
            $crelpro = (int)($this->getCrelpro() ?: 0);
            $creppro = (int)($this->getCreppro() ?: 0);
            $tippro = !empty($this->getTippro()) ? (int)$this->getTippro() : null;
            $just = $this->getJust();
            $redcon = $this->getRedcon();
            $reqing = $this->getReqing();
            $reqcer = $this->getReqcer();
            $idare = (int)($this->getIdare() ?: 0);

            $params = [
                ':codpro'  => $codpro,
                ':nompro'  => $nompro,
                ':despro'  => !empty($despro) ? $despro : null,
                ':verpro'  => $verpro,
                ':horlpro' => $horlpro,
                ':horppro' => $horppro,
                ':crelpro' => $crelpro,
                ':creppro' => $creppro,
                ':tippro'  => $tippro,
                ':just'    => !empty($just) ? $just : null,
                ':redcon'  => !empty($redcon) ? $redcon : null,
                ':reqing'  => !empty($reqing) ? $reqing : null,
                ':reqcer'  => !empty($reqcer) ? $reqcer : null,
                ':idare'   => $idare
            ];

            return $modelo->executeWrite($sql, $params);
        } catch (Exception $e) {
            if (session_status() === PHP_SESSION_ACTIVE) {
                $_SESSION['last_crud_error'] = $e->getMessage();
            }
            error_log("Error en Hormprg::edit(): " . $e->getMessage());
            return false;
        }
    }

    public function del(){
        try {
            $sql = "DELETE FROM programa WHERE codpro=:codpro";
            $modelo = new Conexion();
            $codpro = $this->getCodpro();
            return $modelo->executeWrite($sql, [':codpro' => $codpro]);
        } catch (Exception $e) {
            error_log("Error en Hormprg::del(): " . $e->getMessage());
            return false;
        }
    }

    // Métodos para el Dashboard de Programas
    public function getResumenDashboard(){
        $res = [
            'total_programas' => 0,
            'programas_activos' => 0,
            'programas_inactivos' => 0,
            'total_resultados' => 0,
            'prom_horas_lectivas' => 0,
            'prom_horas_productivas' => 0,
            'total_areas' => 0,
            'total_competencias' => 0,
            'fichas_totales' => 0,
            'fichas_vigentes' => 0
        ];
        $sql = "SELECT COUNT(DISTINCT p.codpro) AS total_programas, 
                       ROUND(AVG(p.horlpro)) AS prom_horas_lectivas, 
                       ROUND(AVG(p.horppro)) AS prom_horas_productivas, 
                       COUNT(DISTINCT p.idare) AS total_areas,
                       COUNT(DISTINCT r.idres) AS total_resultados,
                       COUNT(DISTINCT c.idcom) AS total_competencias,
                       COUNT(DISTINCT f.idfic) AS fichas_totales,
                       COUNT(DISTINCT CASE WHEN f.ffinfic >= CURDATE() THEN f.idfic END) AS fichas_vigentes,
                       COUNT(DISTINCT CASE WHEN f.ffinfic >= CURDATE() THEN p.codpro END) AS programas_activos
                FROM programa p
                LEFT JOIN proxcom px ON p.codpro = px.codpro
                LEFT JOIN competencia c ON px.idcom = c.idcom
                LEFT JOIN resultado r ON px.idcom = r.idcom
                LEFT JOIN ficha f ON p.codpro = f.codpro";
        $modelo = new Conexion();
        $conexion = $modelo->get_conexion();
        $stmt = $conexion->prepare($sql);
        $stmt->execute();
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($r) {
            $res['total_programas'] = (int)$r['total_programas'];
            $res['programas_activos'] = (int)$r['programas_activos'];
            $res['programas_inactivos'] = max(0, $res['total_programas'] - $res['programas_activos']);
            $res['total_resultados'] = (int)$r['total_resultados'];
            $res['prom_horas_lectivas'] = (int)$r['prom_horas_lectivas'];
            $res['prom_horas_productivas'] = (int)$r['prom_horas_productivas'];
            $res['total_areas'] = (int)$r['total_areas'];
            $res['total_competencias'] = (int)$r['total_competencias'];
            $res['fichas_totales'] = (int)$r['fichas_totales'];
            $res['fichas_vigentes'] = (int)$r['fichas_vigentes'];
        }
        return $res;
    }

    public function getDistTipos(){
        $sql = "SELECT COALESCE(v.nomval, 'Sin nivel asignado') AS nomval, COUNT(p.codpro) AS total 
                FROM programa p 
                LEFT JOIN valor v ON p.tippro = v.idval 
                GROUP BY v.idval, v.nomval 
                ORDER BY total DESC";
        $modelo = new Conexion();
        $conexion = $modelo->get_conexion();
        $stmt = $conexion->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getDistJornadas(){
        $sql = "SELECT COALESCE(v.nomval, CONCAT('Jornada ', f.jornada)) AS nomval, 
                       COUNT(f.idfic) AS total_fichas 
                FROM ficha f 
                LEFT JOIN valor v ON f.jornada = v.idval 
                GROUP BY v.idval, v.nomval, f.jornada 
                ORDER BY total_fichas DESC";
        $modelo = new Conexion();
        $conexion = $modelo->get_conexion();
        $stmt = $conexion->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getDistAreas(){
        $sql = "SELECT COALESCE(a.nomare, 'Sin área asignada') AS nomare, 
                       COUNT(p.codpro) AS total,
                       COALESCE(SUM(COALESCE(p.horlpro, 0) + COALESCE(p.horppro, 0)), 0) AS total_horas,
                       COALESCE(SUM(COALESCE(p.horlpro, 0)), 0) AS total_lectiva,
                       COALESCE(SUM(COALESCE(p.horppro, 0)), 0) AS total_productiva
                FROM programa p 
                LEFT JOIN area a ON p.idare = a.idare 
                GROUP BY a.idare, a.nomare 
                ORDER BY total DESC";
        $modelo = new Conexion();
        $conexion = $modelo->get_conexion();
        $stmt = $conexion->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function existePrograma($codpro){
        $sql = "SELECT codpro FROM programa WHERE codpro = :codpro";
        $modelo = new Conexion();
        $conexion = $modelo->get_conexion();
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(":codpro", $codpro);
        $stmt->execute();
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        return !empty($r);
    }
}
class_alias('Hormprg', 'Mprg');
?>
