<?php

class Etamebit { 

    private $conexion;

    // Conexión única reutilizable para no saturar la base de datos
    public function __construct() {
        $modelo = new conexion();
        $this->conexion = $modelo->get_conexion();
    }

    // 1. Obtener fichas asignadas al instructor
    public function getFichasBitacoras($idusu) {
        $sql = "SELECT idficha FROM insseg WHERE idusu = :idusu";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':idusu', $idusu);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);   
    }

    // 2. Obtener aprendices con bitácoras pendientes por ficha
    public function getAprendicesByFicha($idficha) {
        $sql = "
            SELECT u.idusu, u.ndocusu, u.nomusu, b.idbitacora, b.numero_bitacora, 
                b.nombre_empresa, b.nit, b.fecha_inicio, b.fecha_fin
            FROM usufic uf
            INNER JOIN usuario u ON uf.idusu = u.idusu
            INNER JOIN bitacora b ON b.idaprendiz = u.idusu
            WHERE uf.idfic = :idfic AND b.estado = 'Pendiente'
        ";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':idfic', $idficha);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 3. Obtener aprendices con bitácoras aprobadas por ficha
    public function getAprendicesAprobadosByFicha($idficha) {
        $sql = "
            SELECT u.idusu, u.ndocusu, u.nomusu, b.idbitacora, b.numero_bitacora, 
                b.nombre_empresa, b.nit, b.fecha_inicio, b.fecha_fin
            FROM usufic uf
            INNER JOIN usuario u ON uf.idusu = u.idusu
            INNER JOIN bitacora b ON b.idaprendiz = u.idusu
            WHERE uf.idfic = :idfic AND b.estado = 'Aprobado'
        ";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':idfic', $idficha);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 4. Obtener aprendices con bitácoras rechazadas/devueltas por ficha
    public function getAprendicesRechazadosByFicha($idficha) {
        $sql = "
            SELECT u.idusu, u.ndocusu, u.nomusu, b.idbitacora, b.numero_bitacora, 
                b.nombre_empresa, b.nit, b.fecha_inicio, b.fecha_fin
            FROM usufic uf
            INNER JOIN usuario u ON uf.idusu = u.idusu
            INNER JOIN bitacora b ON b.idaprendiz = u.idusu
            WHERE uf.idfic = :idfic AND b.estado = 'Rechazado'
        ";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':idfic', $idficha);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 5. Métrica consolidada de estado de bitácoras por ficha
    public function getEstadisticasPorFicha($idficha) {
        $sql = "SELECT 
                    (SELECT COUNT(DISTINCT uf.idusu) FROM usufic uf WHERE uf.idfic = :idfic) as total_aprendices,
                    (SELECT COUNT(b.idbitacora) FROM bitacora b INNER JOIN usufic uf ON b.idaprendiz = uf.idusu WHERE uf.idfic = :idfic AND b.estado = 'Pendiente') as pendientes,
                    (SELECT COUNT(b.idbitacora) FROM bitacora b INNER JOIN usufic uf ON b.idaprendiz = uf.idusu WHERE uf.idfic = :idfic AND b.estado = 'Aprobado') as aprobadas,
                    (SELECT COUNT(b.idbitacora) FROM bitacora b INNER JOIN usufic uf ON b.idaprendiz = uf.idusu WHERE uf.idfic = :idfic AND b.estado = 'Rechazado') as rechazadas";
        
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':idfic', $idficha);
        $stmt->execute();
        $res = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'total_aprendices' => (int)($res['total_aprendices'] ?? 0),
            'pendientes' => (int)($res['pendientes'] ?? 0),
            'aprobadas' => (int)($res['aprobadas'] ?? 0),
            'rechazadas' => (int)($res['rechazadas'] ?? 0)
        ];
    }

    // 6. Obtener bitácoras pendientes asociadas a un usuario aprendiz
    public function getBitacorasPendientesByUsuario($idusu) {
        $sql = "SELECT 
                    b.*, 
                    u_jefe.nomusu AS nombre_jefe, 
                    u_instructor.nomusu AS nombre_instructor,
                    u_instructor.emausu AS email_instructor
                FROM bitacora b
                LEFT JOIN usuario u_jefe ON b.idjefe = u_jefe.idusu
                LEFT JOIN usuario u_instructor ON b.idinstructor = u_instructor.idusu
                WHERE b.idaprendiz = :idusu AND b.estado = 'Pendiente'";
        
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':idusu', $idusu);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 7. Consultar datos de usuario por ID
    public function getOneUsu($idusu) {
        $sql = "SELECT idusu, nomusu, ndocusu, telcan, emausu FROM usuario WHERE idusu=:idusu";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':idusu', $idusu);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 8. Consultar la ficha a la que pertenece un usuario
    public function getFichaUsuario($idusu) {
        $sql = "SELECT uf.idfic FROM usufic uf INNER JOIN usuario u ON uf.idusu = u.idusu WHERE u.idusu = :idusu";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':idusu', $idusu);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 9. Consultar nombre del programa/ficha de un usuario
    public function getNombrePrograma($idusu) {
        $sql = "SELECT f.nomfic FROM ficha f INNER JOIN usufic uf ON f.idfic = uf.idfic WHERE uf.idusu = :idusu";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':idusu', $idusu);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 10. Consultar alternativa y subalternativa de etapa productiva
    public function getAlternativaAndSubalternativa($idbitacora) {
        $sql = "
            SELECT 
                d.nomdom AS alternativa,
                v.nomval AS subalternativa
            FROM bitacora b
            LEFT JOIN dominio d ON b.idaltep = d.iddom
            LEFT JOIN valor v ON b.idsubaltep = v.idval
            WHERE b.idbitacora = :idbitacora
        ";

        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':idbitacora', $idbitacora, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // 11. Total acumulado de bitácoras aprobadas por ficha
    public function getTotalBitacorasAprobadasPorFicha($idficha) {
        $sql = "SELECT COUNT(*) as total_aprobadas
                FROM bitacora b
                JOIN usufic uf ON b.idaprendiz = uf.idusu
                WHERE uf.idfic = :idficha AND b.estado = 'Aprobado'";

        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':idficha', $idficha);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total_aprobadas'] ?? 0;
    }

    // 12. Obtener lista de actividades vinculadas a una bitácora
    public function getActividadesByBitacora($idbitacora) {
        $sql = "SELECT * FROM actividades WHERE idbitacora = :idbitacora";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':idbitacora', $idbitacora);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 13. Actualizar el estado de la bitácora (Acción directa)
    public function actualizarEstadoBitacoraMasiva($idbitacora, $estado) {
        $sql = "UPDATE bitacora SET estado = :estado WHERE idbitacora = :idbitacora";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':estado', $estado);
        $stmt->bindParam(':idbitacora', $idbitacora);
        return $stmt->execute();
    }

    // 14. Guardar observación general de la bitácora
    public function saverObservationMasiva($idbitacora, $observacion) {
        $sql = "UPDATE bitacora SET `observación` = :observacion WHERE idbitacora = :idbitacora";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':observacion', $observacion);
        $stmt->bindParam(':idbitacora', $idbitacora);
        return $stmt->execute();
    }

    // 15. Evaluación segura verificando la asignación del instructor a la ficha
    public function evaluarBitacoraSeguro($idbitacora, $estado, $observacion, $idinstructor, $idficha) {
        $sql = "UPDATE bitacora b
                INNER JOIN usufic uf ON b.idaprendiz = uf.idusu
                INNER JOIN insseg ins ON uf.idfic = ins.idficha
                SET b.estado = :estado, 
                    b.`observación` = :observacion,
                    b.idinstructor = :idinstructor
                WHERE b.idbitacora = :idbitacora
                  AND b.estado = 'Pendiente'
                  AND uf.idfic = :idficha
                  AND ins.idficha = :idficha
                  AND ins.idusu = :idinstructor";

        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':estado', $estado);
        $stmt->bindParam(':observacion', $observacion);
        $stmt->bindParam(':idinstructor', $idinstructor);
        $stmt->bindParam(':idbitacora', $idbitacora);
        $stmt->bindParam(':idficha', $idficha);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

}
?>