<?php
/**
 * MODELO: hormevas.php
 * Responsabilidad: Acceso a datos para Evaluación de Sesiones (evases / sesion)
 * Arquitectura: Modelo en patrón MVC
 */
require_once __DIR__ . "/conexion.php";

class Mevs {
    private $ideva;
    private $idses;
    private $idusu;
    private $comeva;
    private $valeva;
    private $fecses;
    private $idage;

    // Getters
    public function getIdeva()  { return $this->ideva; }
    public function getIdses()  { return $this->idses; }
    public function getIdusu()  { return $this->idusu; }
    public function getComeva() { return $this->comeva; }
    public function getValeva() { return $this->valeva; }
    public function getFecses() { return $this->fecses; }
    public function getIdage()  { return $this->idage; }

    // Setters
    public function setIdeva($ideva)   { $this->ideva = $ideva; }
    public function setIdses($idses)   { $this->idses = $idses; }
    public function setIdusu($idusu)   { $this->idusu = $idusu; }
    public function setComeva($comeva) { $this->comeva = $comeva; }
    public function setValeva($valeva) { $this->valeva = $valeva; }
    public function setFecses($fecses) { $this->fecses = $fecses; }
    public function setIdage($idage)   { $this->idage = $idage; }

    /**
     * Obtiene todas las evaluaciones de sesiones con joins reales a sesion, usuario, agenda y ficha
     */
    public function getAll($idusu = null, $idfic = null) {
        $modelo = new Conexion();
        $conexion = $modelo->get_conexion();

        $sql = "SELECT e.ideva, e.idses, e.idusu, e.comeva, e.valeva, 
                       s.fecses, s.idage, 
                       u.nomusu, u.ndocusu,
                       ag.idfic, ag.actpro,
                       f.nomfic
                FROM evases e
                INNER JOIN sesion s ON e.idses = s.idses
                INNER JOIN usuario u ON e.idusu = u.idusu
                LEFT JOIN agenda ag ON s.idage = ag.idage
                LEFT JOIN ficha f ON ag.idfic = f.idfic
                WHERE 1=1";

        $params = [];
        if (!empty($idusu)) {
            $sql .= " AND e.idusu = :idusu";
            $params[':idusu'] = $idusu;
        }
        if (!empty($idfic)) {
            $sql .= " AND ag.idfic = :idfic";
            $params[':idfic'] = $idfic;
        }

        $sql .= " ORDER BY s.fecses DESC, e.ideva DESC";

        $stmt = $conexion->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene una evaluación específica por ideva o idses
     */
    public function getOne() {
        $modelo = new Conexion();
        $conexion = $modelo->get_conexion();

        if ($this->ideva) {
            $sql = "SELECT e.ideva, e.idses, e.idusu, e.comeva, e.valeva, s.fecses, s.idage
                    FROM evases e
                    INNER JOIN sesion s ON e.idses = s.idses
                    WHERE e.ideva = :ideva";
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(":ideva", $this->ideva, PDO::PARAM_INT);
        } else {
            $sql = "SELECT e.ideva, e.idses, e.idusu, e.comeva, e.valeva, s.fecses, s.idage
                    FROM evases e
                    INNER JOIN sesion s ON e.idses = s.idses
                    WHERE e.idses = :idses";
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(":idses", $this->idses, PDO::PARAM_INT);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Guarda una evaluación de sesión en la tabla real `evases`
     */
    public function save() {
        $modelo = new Conexion();
        $conexion = $modelo->get_conexion();

        $sql = "INSERT INTO evases (idses, idusu, comeva, valeva) 
                VALUES (:idses, :idusu, :comeva, :valeva)";
        $stmt = $conexion->prepare($sql);

        $stmt->bindParam(":idses", $this->idses, PDO::PARAM_INT);
        $stmt->bindParam(":idusu", $this->idusu, PDO::PARAM_INT);
        $stmt->bindParam(":comeva", $this->comeva, PDO::PARAM_STR);
        $stmt->bindParam(":valeva", $this->valeva, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Actualiza una evaluación existente
     */
    public function edit() {
        $modelo = new Conexion();
        $conexion = $modelo->get_conexion();

        $sql = "UPDATE evases 
                SET comeva = :comeva, valeva = :valeva 
                WHERE ideva = :ideva";
        $stmt = $conexion->prepare($sql);

        $stmt->bindParam(":ideva", $this->ideva, PDO::PARAM_INT);
        $stmt->bindParam(":comeva", $this->comeva, PDO::PARAM_STR);
        $stmt->bindParam(":valeva", $this->valeva, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Elimina una evaluación
     */
    public function del() {
        $modelo = new Conexion();
        $conexion = $modelo->get_conexion();

        $sql = "DELETE FROM evases WHERE ideva = :ideva";
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(":ideva", $this->ideva, PDO::PARAM_INT);
        return $stmt->execute();
    }
}

if (!class_exists('Hormevas')) {
    class Hormevas extends Mevs {}
}
?>