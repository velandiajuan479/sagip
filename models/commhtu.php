<?php
class Mhtxus {
    private $id;
    private $idnorad;
    private $idusu;

    function getId(){ return $this->id; }
    function getIdnorad(){ return $this->idnorad; }
    function getIdusu(){ return $this->idusu; }

    function setId($id){ $this->id = $id; }
    function setIdnorad($idnorad){ $this->idnorad = $idnorad; }
    function setIdusu($idusu){ $this->idusu = $idusu; }

    public function getAll($busqueda = null) {
        try {
            // Tabla real: hdtxusu (idnorad, idusu). LEFT JOIN para no perder filas
            // aunque la ficha no tenga hoja de trabajo o programa relacionados.
            $sql = "SELECT hx.idnorad, hx.idusu, u.nomusu, ho.feclini, ho.feclin, ho.cupo, p.nompro
                    FROM hdtxusu AS hx
                    INNER JOIN usuario AS u ON hx.idusu = u.idusu
                    LEFT JOIN hojatra AS ho ON hx.idnorad = ho.idnorad
                    LEFT JOIN programa AS p ON ho.codpro = p.codpro
                    WHERE 1=1";

            if ($busqueda) {
                $sql .= " AND u.nomusu LIKE :busqueda";
            }

            $sql .= " ORDER BY hx.idnorad DESC";

            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);

            if ($busqueda) {
                $searchTerm = "%" . $busqueda . "%";
                $result->bindParam(':busqueda', $searchTerm, PDO::PARAM_STR);
            }

            $result->execute();
            return $result->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    public function getAllUsuarios() {
        try {
            // Solo usuarios con perfil de instructor (igual que commhcm.php getAllInstructores()).
            // 7=Instructor, 12=Contratista, 27=Soporte MA, 32=Instructor Seguimiento, 36=Coordinador EP, 42=Comple. Instructor
            $sql = "SELECT u.idusu, u.nomusu, p.nomper FROM usuario AS u LEFT JOIN perfil AS p ON u.idper = p.idper WHERE u.idper IN (7, 12, 27, 32, 36, 42) ORDER BY u.nomusu";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->execute();
            return $result->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    public function getAllHojas() {
        try {
            // Fichas reales de hojatra con su número (idnorad): sin datos de ejemplo.
            $sql = "SELECT h.idnorad, p.nompro, h.feclini FROM hojatra h INNER JOIN programa p ON h.codpro = p.codpro ORDER BY h.idnorad DESC";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->execute();
            return $result->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    public function existe() {
        try {
            $sql = "SELECT COUNT(*) AS n FROM hdtxusu WHERE idnorad = :idnorad AND idusu = :idusu";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->bindValue(':idnorad', $this->idnorad);
            $result->bindValue(':idusu', $this->idusu);
            $result->execute();
            $row = $result->fetch(PDO::FETCH_ASSOC);
            return ((int)($row['n'] ?? 0)) > 0;
        } catch (Exception $e) {
            return false;
        }
    }

    public function guardar() {
        try {
            $sql = "INSERT INTO hdtxusu (idnorad, idusu) VALUES (:idnorad, :idusu)";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->bindValue(':idnorad', $this->idnorad);
            $result->bindValue(':idusu', $this->idusu);
            return $result->execute();
        } catch (Exception $e) {
            return false;
        }
    }

    public function eliminar() {
        try {
            // hdtxusu no tiene columna id: la clave es (idnorad, idusu)
            $sql = "DELETE FROM hdtxusu WHERE idnorad = :idnorad AND idusu = :idusu";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->bindValue(':idnorad', $this->idnorad);
            $result->bindValue(':idusu', $this->idusu);
            return $result->execute();
        } catch (Exception $e) {
            return false;
        }
    }
}
?>