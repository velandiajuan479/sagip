<?php
class Mclac
{
    // ⚠️ Si tu tabla se llama distinto (ej: "calendario"), cambia SOLO esta línea.
    private $tabla = "calendario_academico";

    private $id;
    private $titulo;
    private $descripcion;
    private $fecha_inicio;
    private $fecha_fin;
    private $tipo_evento;
    private $anio;
    private $trimestre;

    public function getId()          { return $this->id; }
    public function getTitulo()      { return $this->titulo; }
    public function getDescripcion() { return $this->descripcion; }
    public function getFechaInicio() { return $this->fecha_inicio; }
    public function getFechaFin()    { return $this->fecha_fin; }
    public function getTipoEvento()  { return $this->tipo_evento; }
    public function getAnio()        { return $this->anio; }
    public function getTrimestre()   { return $this->trimestre; }

    public function setId($id)                   { $this->id = $id; }
    public function setTitulo($titulo)           { $this->titulo = $titulo; }
    public function setDescripcion($descripcion) { $this->descripcion = $descripcion; }
    public function setFechaInicio($fecha_inicio){ $this->fecha_inicio = $fecha_inicio; }
    public function setFechaFin($fecha_fin)      { $this->fecha_fin = $fecha_fin; }
    public function setTipoEvento($tipo_evento)  { $this->tipo_evento = $tipo_evento; }
    public function setAnio($anio)               { $this->anio = $anio; }
    public function setTrimestre($trimestre)     { $this->trimestre = $trimestre; }

    // Ayudante: si el dato viene vacío, guarda NULL en vez de texto vacío
    private function atar($result, $marca, $valor, $tipo = PDO::PARAM_STR)
    {
        if ($valor === null || $valor === "") {
            $result->bindValue($marca, null, PDO::PARAM_NULL);
        } else {
            $result->bindValue($marca, $valor, $tipo);
        }
    }

    public function getAll()
    {
        try {
            $sql = "SELECT * FROM " . $this->tabla . " ORDER BY fecha_inicio ASC";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->execute();
            return $result->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            ManejoError($e);
            return [];
        }
    }

    public function getOne()
    {
        try {
            $sql = "SELECT * FROM " . $this->tabla . " WHERE id = :id LIMIT 1";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->bindValue(":id", (int)$this->getId(), PDO::PARAM_INT);
            $result->execute();
            $fila = $result->fetch(PDO::FETCH_ASSOC);
            return $fila ? $fila : null;
        } catch (Exception $e) {
            ManejoError($e);
            return null;
        }
    }

    public function save()
    {
        try {
            $sql = "INSERT INTO " . $this->tabla . "
                    (titulo, descripcion, fecha_inicio, fecha_fin, tipo_evento, anio, trimestre)
                    VALUES (:titulo, :descripcion, :fecha_inicio, :fecha_fin, :tipo_evento, :anio, :trimestre)";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);

            $this->atar($result, ":titulo",       $this->getTitulo());
            $this->atar($result, ":descripcion",  $this->getDescripcion());
            $this->atar($result, ":fecha_inicio", $this->getFechaInicio());
            $this->atar($result, ":fecha_fin",    $this->getFechaFin());
            $this->atar($result, ":tipo_evento",  $this->getTipoEvento());
            $this->atar($result, ":anio",         $this->getAnio(), PDO::PARAM_INT);
            $this->atar($result, ":trimestre",    $this->getTrimestre());

            return $result->execute();
        } catch (Exception $e) {
            ManejoError($e);
            return false;
        }
    }

    public function edit()
    {
        try {
            $sql = "UPDATE " . $this->tabla . " SET
                    titulo = :titulo,
                    descripcion = :descripcion,
                    fecha_inicio = :fecha_inicio,
                    fecha_fin = :fecha_fin,
                    tipo_evento = :tipo_evento,
                    anio = :anio,
                    trimestre = :trimestre
                    WHERE id = :id";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);

            $result->bindValue(":id", (int)$this->getId(), PDO::PARAM_INT);
            $this->atar($result, ":titulo",       $this->getTitulo());
            $this->atar($result, ":descripcion",  $this->getDescripcion());
            $this->atar($result, ":fecha_inicio", $this->getFechaInicio());
            $this->atar($result, ":fecha_fin",    $this->getFechaFin());
            $this->atar($result, ":tipo_evento",  $this->getTipoEvento());
            $this->atar($result, ":anio",         $this->getAnio(), PDO::PARAM_INT);
            $this->atar($result, ":trimestre",    $this->getTrimestre());

            return $result->execute();
        } catch (Exception $e) {
            ManejoError($e);
            return false;
        }
    }

    public function del()
    {
        try {
            $sql = "DELETE FROM " . $this->tabla . " WHERE id = :id";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->bindValue(":id", (int)$this->getId(), PDO::PARAM_INT);
            $result->execute();
            return ($result->rowCount() > 0);
        } catch (Exception $e) {
            ManejoError($e);
            return false;
        }
    }

    public function getTotales()
    {
        try {
            $sql = "SELECT COUNT(*) AS total, SUM(CASE WHEN fecha_fin >= CURDATE() THEN 1 ELSE 0 END) AS vigentes, SUM(CASE WHEN fecha_fin < CURDATE() THEN 1 ELSE 0 END) AS pasados FROM " . $this->tabla;
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->execute();
            return $result->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            ManejoError($e);
            return [];
        }
    }

    public function getCalAct()
    {
        try {
            $sql = "SELECT * FROM " . $this->tabla . " WHERE fecha_fin >= CURDATE() ORDER BY fecha_inicio ASC, id DESC";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->execute();
            return $result->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            ManejoError($e);
            return [];
        }
    }

    public function getCalInact()
    {
        try {
            $sql = "SELECT * FROM " . $this->tabla . " WHERE fecha_fin < CURDATE() ORDER BY fecha_fin DESC, id DESC";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->execute();
            return $result->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            ManejoError($e);
            return [];
        }
    }
}
?>