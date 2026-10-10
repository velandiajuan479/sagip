<?php
require_once("conexion.php");

class Edtmsedt {
    private $idnorad;
    private $codpro;
    private $idemp;
    private $codproesp;
    private $codslem;
    private $feclini;
    private $feclin;
    private $cupo;
    private $jornada;
    private $idfic;
    private $convht;
    private $idusu;

    // Nuevos campos según Formato Oficial EDT SENA
    private $codsena;
    private $denominacion;
    private $ocupacion;
    private $sector_econ;
    private $gremio;
    private $duracion;
    private $tipo_edt;
    private $objetivos;
    private $temas;
    private $poblacion;
    private $metodologia;
    private $resultados;
    private $perfil_orientador;
    private $observaciones;
    private $sector_programa;
    private $tipo_evento;
    private $subtipo_evento;
    private $horario_propuesto;
    private $lugar_edt;
    private $responsable;
    private $nitemp;
    private $modalidad_prog;
    private $tipo_convenio;

    public function __construct() {
        $this->ensureSchema();
    }

    private function ensureSchema() {
        try {
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            
            $columns = [
                'codsena' => "VARCHAR(100) DEFAULT NULL",
                'denominacion' => "TEXT DEFAULT NULL",
                'ocupacion' => "VARCHAR(255) DEFAULT NULL",
                'sector_econ' => "VARCHAR(255) DEFAULT NULL",
                'gremio' => "VARCHAR(255) DEFAULT NULL",
                'duracion' => "INT(11) DEFAULT NULL",
                'tipo_edt' => "VARCHAR(255) DEFAULT NULL",
                'objetivos' => "TEXT DEFAULT NULL",
                'temas' => "TEXT DEFAULT NULL",
                'poblacion' => "TEXT DEFAULT NULL",
                'metodologia' => "TEXT DEFAULT NULL",
                'resultados' => "TEXT DEFAULT NULL",
                'perfil_orientador' => "TEXT DEFAULT NULL",
                'observaciones' => "TEXT DEFAULT NULL",
                'sector_programa' => "VARCHAR(255) DEFAULT NULL",
                'tipo_evento' => "VARCHAR(255) DEFAULT NULL",
                'subtipo_evento' => "VARCHAR(255) DEFAULT NULL",
                'horario_propuesto' => "TEXT DEFAULT NULL",
                'lugar_edt' => "VARCHAR(255) DEFAULT NULL",
                'responsable' => "VARCHAR(255) DEFAULT NULL",
                'nitemp' => "VARCHAR(100) DEFAULT NULL",
                'modalidad_prog' => "VARCHAR(255) DEFAULT NULL",
                'tipo_convenio' => "VARCHAR(255) DEFAULT NULL"
            ];

            foreach ($columns as $col => $type) {
                $chk = $conexion->query("SHOW COLUMNS FROM hojatra LIKE '$col'");
                if ($chk && $chk->rowCount() == 0) {
                    $conexion->exec("ALTER TABLE hojatra ADD COLUMN `$col` $type");
                }
            }
        } catch (Exception $e) {
            // Silencioso si falla permisos o esquema ya modificado
        }
    }

    // Getters
    public function getIdnorad() { return $this->idnorad; }
    public function getCodpro() { return $this->codpro; }
    public function getIdemp() { return $this->idemp; }
    public function getCodproesp() { return $this->codproesp; }
    public function getCodslem() { return $this->codslem; }
    public function getFeclini() { return $this->feclini; }
    public function getFeclin() { return $this->feclin; }
    public function getCupo() { return $this->cupo; }
    public function getJornada() { return $this->jornada; }
    public function getIdfic() { return $this->idfic; }
    public function getConvht() { return $this->convht; }
    public function getIdusu() { return $this->idusu; }

    public function getCodsena() { return $this->codsena; }
    public function getDenominacion() { return $this->denominacion; }
    public function getOcupacion() { return $this->ocupacion; }
    public function getSector_econ() { return $this->sector_econ; }
    public function getGremio() { return $this->gremio; }
    public function getDuracion() { return $this->duracion; }
    public function getTipo_edt() { return $this->tipo_edt; }
    public function getObjetivos() { return $this->objetivos; }
    public function getTemas() { return $this->temas; }
    public function getPoblacion() { return $this->poblacion; }
    public function getMetodologia() { return $this->metodologia; }
    public function getResultados() { return $this->resultados; }
    public function getPerfil_orientador() { return $this->perfil_orientador; }
    public function getObservaciones() { return $this->observaciones; }
    public function getSector_programa() { return $this->sector_programa; }
    public function getTipo_evento() { return $this->tipo_evento; }
    public function getSubtipo_evento() { return $this->subtipo_evento; }
    public function getHorario_propuesto() { return $this->horario_propuesto; }
    public function getLugar_edt() { return $this->lugar_edt; }
    public function getResponsable() { return $this->responsable; }
    public function getNitemp() { return $this->nitemp; }
    public function getModalidad_prog() { return $this->modalidad_prog; }
    public function getTipo_convenio() { return $this->tipo_convenio; }

    // Setters
    public function setIdnorad($idnorad) { $this->idnorad = $idnorad; }
    public function setCodpro($codpro) { $this->codpro = $codpro; }
    public function setIdemp($idemp) { $this->idemp = $idemp; }
    public function setCodproesp($codproesp) { $this->codproesp = $codproesp; }
    public function setCodslem($codslem) { $this->codslem = $codslem; }
    public function setFeclini($feclini) { $this->feclini = $feclini; }
    public function setFeclin($feclin) { $this->feclin = $feclin; }
    public function setCupo($cupo) { $this->cupo = $cupo; }
    public function setJornada($jornada) { $this->jornada = $jornada; }
    public function setIdfic($idfic) { $this->idfic = $idfic; }
    public function setConvht($convht) { $this->convht = $convht; }
    public function setIdusu($idusu) { $this->idusu = $idusu; }

    public function setCodsena($codsena) { $this->codsena = $codsena; }
    public function setDenominacion($denominacion) { $this->denominacion = $denominacion; }
    public function setOcupacion($ocupacion) { $this->ocupacion = $ocupacion; }
    public function setSector_econ($sector_econ) { $this->sector_econ = $sector_econ; }
    public function setGremio($gremio) { $this->gremio = $gremio; }
    public function setDuracion($duracion) { $this->duracion = $duracion; }
    public function setTipo_edt($tipo_edt) { $this->tipo_edt = $tipo_edt; }
    public function setObjetivos($objetivos) { $this->objetivos = $objetivos; }
    public function setTemas($temas) { $this->temas = $temas; }
    public function setPoblacion($poblacion) { $this->poblacion = $poblacion; }
    public function setMetodologia($metodologia) { $this->metodologia = $metodologia; }
    public function setResultados($resultados) { $this->resultados = $resultados; }
    public function setPerfil_orientador($perfil_orientador) { $this->perfil_orientador = $perfil_orientador; }
    public function setObservaciones($observaciones) { $this->observaciones = $observaciones; }
    public function setSector_programa($sector_programa) { $this->sector_programa = $sector_programa; }
    public function setTipo_evento($tipo_evento) { $this->tipo_evento = $tipo_evento; }
    public function setSubtipo_evento($subtipo_evento) { $this->subtipo_evento = $subtipo_evento; }
    public function setHorario_propuesto($horario_propuesto) { $this->horario_propuesto = $horario_propuesto; }
    public function setLugar_edt($lugar_edt) { $this->lugar_edt = $lugar_edt; }
    public function setResponsable($responsable) { $this->responsable = $responsable; }
    public function setNitemp($nitemp) { $this->nitemp = $nitemp; }
    public function setModalidad_prog($modalidad_prog) { $this->modalidad_prog = $modalidad_prog; }
    public function setTipo_convenio($tipo_convenio) { $this->tipo_convenio = $tipo_convenio; }

    public function getAll(){
        $sql = "SELECT h.*, p.nompro, e.nomemp, v1.nomval AS nom_proesp, 
                v2.nomval AS nom_jornada, u.idusu, u.nomusu 
                FROM hojatra AS h
                LEFT JOIN programa AS p ON h.codpro = p.codpro
                LEFT JOIN empresa AS e ON h.idemp = e.idemp
                LEFT JOIN valor AS v1 ON h.codproesp = v1.idval
                LEFT JOIN valor AS v2 ON h.jornada = v2.idval
                LEFT JOIN hdtxusu AS hu ON h.idnorad = hu.idnorad
                LEFT JOIN usuario AS u ON hu.idusu = u.idusu
                ORDER BY h.idnorad DESC";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->execute();
        $res = $result->fetchAll(PDO::FETCH_ASSOC);
        return $res;
    }

    public function getOne(){
        $sql = "SELECT h.*, p.nompro, e.nomemp, v1.nomval AS nom_proesp, 
                v2.nomval AS nom_jornada, u.idusu, u.nomusu 
                FROM hojatra AS h
                LEFT JOIN programa AS p ON h.codpro = p.codpro
                LEFT JOIN empresa AS e ON h.idemp = e.idemp
                LEFT JOIN valor AS v1 ON h.codproesp = v1.idval
                LEFT JOIN valor AS v2 ON h.jornada = v2.idval
                LEFT JOIN hdtxusu AS hu ON h.idnorad = hu.idnorad
                LEFT JOIN usuario AS u ON hu.idusu = u.idusu
                WHERE h.idnorad = :idnorad";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $idnorad = $this->getIdnorad();
        $result->bindParam(":idnorad", $idnorad);
        $result->execute();
        $res = $result->fetchAll(PDO::FETCH_ASSOC);
        return $res;
    }

    public function getPro(){
        $sql = "SELECT codpro, nompro FROM programa ORDER BY nompro ASC";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->execute();
        $res = $result->fetchAll(PDO::FETCH_ASSOC);
        return $res;
    }

    public function getEmp(){
        $sql = "SELECT idemp, nomemp, numdocemp FROM empresa ORDER BY nomemp ASC";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->execute();
        $res = $result->fetchAll(PDO::FETCH_ASSOC);
        return $res;
    }

    public function getValDom($iddom){
        $sql = "SELECT idval, nomval FROM valor WHERE iddom = :iddom AND act = 1 ORDER BY nomval ASC";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->bindParam(":iddom", $iddom);
        $result->execute();
        $res = $result->fetchAll(PDO::FETCH_ASSOC);
        return $res;
    }

    public function save(){
        $sql = "INSERT INTO hojatra (
                    codpro, idemp, codproesp, codslem, feclini, feclin, cupo, jornada, convht,
                    codsena, denominacion, ocupacion, sector_econ, gremio, duracion, tipo_edt,
                    objetivos, temas, poblacion, metodologia, resultados, perfil_orientador,
                    observaciones, sector_programa, tipo_evento, subtipo_evento, horario_propuesto,
                    lugar_edt, responsable, nitemp, modalidad_prog, tipo_convenio
                ) VALUES (
                    :codpro, :idemp, :codproesp, :codslem, :feclini, :feclin, :cupo, :jornada, :convht,
                    :codsena, :denominacion, :ocupacion, :sector_econ, :gremio, :duracion, :tipo_edt,
                    :objetivos, :temas, :poblacion, :metodologia, :resultados, :perfil_orientador,
                    :observaciones, :sector_programa, :tipo_evento, :subtipo_evento, :horario_propuesto,
                    :lugar_edt, :responsable, :nitemp, :modalidad_prog, :tipo_convenio
                )";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $stmt = $conexion->prepare($sql);

        $stmt->bindParam(":codpro", $this->codpro);
        $stmt->bindParam(":idemp", $this->idemp);
        $stmt->bindParam(":codproesp", $this->codproesp);
        $stmt->bindParam(":codslem", $this->codslem);
        $stmt->bindParam(":feclini", $this->feclini);
        $stmt->bindParam(":feclin", $this->feclin);
        $stmt->bindParam(":cupo", $this->cupo);
        $stmt->bindParam(":jornada", $this->jornada);
        $stmt->bindParam(":convht", $this->convht);

        $stmt->bindParam(":codsena", $this->codsena);
        $stmt->bindParam(":denominacion", $this->denominacion);
        $stmt->bindParam(":ocupacion", $this->ocupacion);
        $stmt->bindParam(":sector_econ", $this->sector_econ);
        $stmt->bindParam(":gremio", $this->gremio);
        $stmt->bindParam(":duracion", $this->duracion);
        $stmt->bindParam(":tipo_edt", $this->tipo_edt);
        $stmt->bindParam(":objetivos", $this->objetivos);
        $stmt->bindParam(":temas", $this->temas);
        $stmt->bindParam(":poblacion", $this->poblacion);
        $stmt->bindParam(":metodologia", $this->metodologia);
        $stmt->bindParam(":resultados", $this->resultados);
        $stmt->bindParam(":perfil_orientador", $this->perfil_orientador);
        $stmt->bindParam(":observaciones", $this->observaciones);
        $stmt->bindParam(":sector_programa", $this->sector_programa);
        $stmt->bindParam(":tipo_evento", $this->tipo_evento);
        $stmt->bindParam(":subtipo_evento", $this->subtipo_evento);
        $stmt->bindParam(":horario_propuesto", $this->horario_propuesto);
        $stmt->bindParam(":lugar_edt", $this->lugar_edt);
        $stmt->bindParam(":responsable", $this->responsable);
        $stmt->bindParam(":nitemp", $this->nitemp);
        $stmt->bindParam(":modalidad_prog", $this->modalidad_prog);
        $stmt->bindParam(":tipo_convenio", $this->tipo_convenio);

        $stmt->execute();
        return $conexion->lastInsertId();
    }

    public function saveHxU(){
        $sql = "INSERT INTO hdtxusu (idnorad, idusu) VALUES (:idnorad, :idusu)";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(":idnorad", $this->idnorad);
        $stmt->bindParam(":idusu", $this->idusu);
        $stmt->execute();
    }

    public function edit(){
        $sql = "UPDATE hojatra SET 
                codpro = :codpro, 
                idemp = :idemp, 
                codproesp = :codproesp, 
                codslem = :codslem, 
                feclini = :feclini, 
                feclin = :feclin, 
                cupo = :cupo, 
                jornada = :jornada,
                codsena = :codsena,
                denominacion = :denominacion,
                ocupacion = :ocupacion,
                sector_econ = :sector_econ,
                gremio = :gremio,
                duracion = :duracion,
                tipo_edt = :tipo_edt,
                objetivos = :objetivos,
                temas = :temas,
                poblacion = :poblacion,
                metodologia = :metodologia,
                resultados = :resultados,
                perfil_orientador = :perfil_orientador,
                observaciones = :observaciones,
                sector_programa = :sector_programa,
                tipo_evento = :tipo_evento,
                subtipo_evento = :subtipo_evento,
                horario_propuesto = :horario_propuesto,
                lugar_edt = :lugar_edt,
                responsable = :responsable,
                nitemp = :nitemp,
                modalidad_prog = :modalidad_prog,
                tipo_convenio = :tipo_convenio
                WHERE idnorad = :idnorad";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $stmt = $conexion->prepare($sql);

        $stmt->bindParam(":codpro", $this->codpro);
        $stmt->bindParam(":idemp", $this->idemp);
        $stmt->bindParam(":codproesp", $this->codproesp);
        $stmt->bindParam(":codslem", $this->codslem);
        $stmt->bindParam(":feclini", $this->feclini);
        $stmt->bindParam(":feclin", $this->feclin);
        $stmt->bindParam(":cupo", $this->cupo);
        $stmt->bindParam(":jornada", $this->jornada);

        $stmt->bindParam(":codsena", $this->codsena);
        $stmt->bindParam(":denominacion", $this->denominacion);
        $stmt->bindParam(":ocupacion", $this->ocupacion);
        $stmt->bindParam(":sector_econ", $this->sector_econ);
        $stmt->bindParam(":gremio", $this->gremio);
        $stmt->bindParam(":duracion", $this->duracion);
        $stmt->bindParam(":tipo_edt", $this->tipo_edt);
        $stmt->bindParam(":objetivos", $this->objetivos);
        $stmt->bindParam(":temas", $this->temas);
        $stmt->bindParam(":poblacion", $this->poblacion);
        $stmt->bindParam(":metodologia", $this->metodologia);
        $stmt->bindParam(":resultados", $this->resultados);
        $stmt->bindParam(":perfil_orientador", $this->perfil_orientador);
        $stmt->bindParam(":observaciones", $this->observaciones);
        $stmt->bindParam(":sector_programa", $this->sector_programa);
        $stmt->bindParam(":tipo_evento", $this->tipo_evento);
        $stmt->bindParam(":subtipo_evento", $this->subtipo_evento);
        $stmt->bindParam(":horario_propuesto", $this->horario_propuesto);
        $stmt->bindParam(":lugar_edt", $this->lugar_edt);
        $stmt->bindParam(":responsable", $this->responsable);
        $stmt->bindParam(":nitemp", $this->nitemp);
        $stmt->bindParam(":modalidad_prog", $this->modalidad_prog);
        $stmt->bindParam(":tipo_convenio", $this->tipo_convenio);

        $stmt->bindParam(":idnorad", $this->idnorad);
        $stmt->execute();
    }

    public function updateEstado(){
        $sql = "UPDATE hojatra SET convht = :convht WHERE idnorad = :idnorad";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(":convht", $this->convht);
        $stmt->bindParam(":idnorad", $this->idnorad);
        $stmt->execute();
    }

    public function updateEstadoConObs($convht, $idnorad, $obs = null){
        $sql = "UPDATE hojatra SET convht = :convht";
        if ($obs !== null && trim($obs) !== '') {
            $sql .= ", observaciones = CONCAT(IFNULL(observaciones, ''), '\n[', NOW(), ' - ', :convht, ']: ', :obs)";
        }
        $sql .= " WHERE idnorad = :idnorad";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(":convht", $convht);
        $stmt->bindParam(":idnorad", $idnorad);
        if ($obs !== null && trim($obs) !== '') {
            $stmt->bindParam(":obs", $obs);
        }
        return $stmt->execute();
    }

    public function matricular(){
        $sql = "UPDATE hojatra SET idfic = :idfic, convht = 'Matriculado' WHERE idnorad = :idnorad";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(":idfic", $this->idfic);
        $stmt->bindParam(":idnorad", $this->idnorad);
        $stmt->execute();
    }

    public function del(){
        $sql1 = "DELETE FROM hdtxusu WHERE idnorad = :idnorad";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $stmt1 = $conexion->prepare($sql1);
        $idnorad = $this->getIdnorad();
        $stmt1->bindParam(":idnorad", $idnorad);
        $stmt1->execute();

        $sql2 = "DELETE FROM hojatra WHERE idnorad = :idnorad";
        $stmt2 = $conexion->prepare($sql2);
        $stmt2->bindParam(":idnorad", $idnorad);
        $stmt2->execute();
    }
}
?>

