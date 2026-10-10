<?php
require_once __DIR__ . '/conexion.php';

class Admmadep {

    /**
     * Obtiene la lista completa de dependencias con el conteo de empleados asociados.
     */
    public function obtenerDependencias() {
        try {
            $sql = "SELECT d.id_dependencia, d.nombre, d.descripcion, 
                           COUNT(DISTINCT ud.idusu) AS total_empleados,
                           GROUP_CONCAT(CONCAT_WS(' ', u.nomusu, u.ndocusu) SEPARATOR ' ') AS empleados_search
                    FROM dependencias d 
                    LEFT JOIN usu_dep ud ON d.id_dependencia = ud.id_dependencia 
                    LEFT JOIN usuario u ON ud.idusu = u.idusu
                    GROUP BY d.id_dependencia, d.nombre, d.descripcion 
                    ORDER BY d.id_dependencia ASC";

            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $stmt = $conexion->prepare($sql);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception("Error al obtener dependencias: " . $e->getMessage(), 500);
        }
    }

    /**
     * Busca dependencias por nombre, descripción o por nombre/número de documento de sus usuarios vinculados.
     */
    public function buscarDependenciasOUsuarios($criterio) {
        try {
            $criterioTermino = "%" . trim($criterio) . "%";
            $sql = "SELECT d.id_dependencia, d.nombre, d.descripcion, 
                           COUNT(DISTINCT ud.idusu) AS total_empleados,
                           GROUP_CONCAT(CONCAT_WS(' ', u.nomusu, u.ndocusu) SEPARATOR ' ') AS empleados_search
                    FROM dependencias d 
                    LEFT JOIN usu_dep ud ON d.id_dependencia = ud.id_dependencia 
                    LEFT JOIN usuario u ON ud.idusu = u.idusu
                    WHERE d.nombre LIKE :crit 
                       OR d.descripcion LIKE :crit 
                       OR u.nomusu LIKE :crit 
                       OR u.ndocusu LIKE :crit
                    GROUP BY d.id_dependencia, d.nombre, d.descripcion 
                    ORDER BY d.id_dependencia ASC";

            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(':crit', $criterioTermino, PDO::PARAM_STR);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception("Error al buscar dependencias u usuarios: " . $e->getMessage(), 500);
        }
    }

    /**
     * Obtiene los datos de una dependencia por su ID.
     */
    public function obtenerDependenciaPorId($id_dependencia) {
        try {
            $sql = "SELECT id_dependencia, nombre, descripcion 
                    FROM dependencias 
                    WHERE id_dependencia = :id_dependencia";

            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(':id_dependencia', $id_dependencia, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception("Error al obtener la dependencia: " . $e->getMessage(), 500);
        }
    }

    /**
     * Crea una nueva dependencia.
     */
    public function crearDependencia($nombre, $descripcion) {
        try {
            $sql = "INSERT INTO dependencias (nombre, descripcion) VALUES (:nombre, :descripcion)";

            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(':nombre', $nombre, PDO::PARAM_STR);
            $stmt->bindParam(':descripcion', $descripcion, PDO::PARAM_STR);
            
            return $stmt->execute();
        } catch (Exception $e) {
            throw new Exception("Error al registrar la dependencia: " . $e->getMessage(), 500);
        }
    }

    /**
     * Actualiza los datos de una dependencia existente.
     */
    public function actualizarDependencia($id_dependencia, $nombre, $descripcion) {
        try {
            $sql = "UPDATE dependencias 
                    SET nombre = :nombre, descripcion = :descripcion 
                    WHERE id_dependencia = :id_dependencia";

            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(':id_dependencia', $id_dependencia, PDO::PARAM_INT);
            $stmt->bindParam(':nombre', $nombre, PDO::PARAM_STR);
            $stmt->bindParam(':descripcion', $descripcion, PDO::PARAM_STR);

            return $stmt->execute();
        } catch (Exception $e) {
            throw new Exception("Error al actualizar la dependencia: " . $e->getMessage(), 500);
        }
    }

    /**
     * Elimina una dependencia y desvincula sus usuarios en usu_dep.
     */
    public function eliminarDependencia($id_dependencia) {
        try {
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            
            // Iniciar transacción
            $conexion->beginTransaction();

            // Eliminar asignaciones previas en usu_dep
            $sqlUd = "DELETE FROM usu_dep WHERE id_dependencia = :id_dependencia";
            $stmtUd = $conexion->prepare($sqlUd);
            $stmtUd->bindParam(':id_dependencia', $id_dependencia, PDO::PARAM_INT);
            $stmtUd->execute();

            // Eliminar dependencia
            $sqlDep = "DELETE FROM dependencias WHERE id_dependencia = :id_dependencia";
            $stmtDep = $conexion->prepare($sqlDep);
            $stmtDep->bindParam(':id_dependencia', $id_dependencia, PDO::PARAM_INT);
            $stmtDep->execute();

            $conexion->commit();
            return true;
        } catch (Exception $e) {
            if (isset($conexion) && $conexion->inTransaction()) {
                $conexion->rollBack();
            }
            throw new Exception("Error al eliminar la dependencia: " . $e->getMessage(), 500);
        }
    }

    /**
     * Obtiene los empleados/usuarios/contratistas asignados a una dependencia.
     */
    public function obtenerEmpleadosPorDependencia($id_dependencia) {
        try {
            $sql = "SELECT DISTINCT u.idusu, u.ndocusu, u.nomusu AS nombre, 
                           u.emausu AS email, COALESCE(ud.activo, 1) AS activo, u.telcan AS telefono, u.idper,
                           u.fecini, u.fecfin, ud.numero_contrato, ud.fecha_contrato,
                           p.nomper, ud.id_dependencia,
                           CASE 
                               WHEN (ud.numero_contrato IS NOT NULL AND ud.numero_contrato != '') 
                                 OR (u.fecini IS NOT NULL AND u.fecini != '' AND u.fecini != '0000-00-00') 
                                 OR (p.nomper IS NOT NULL AND LOWER(p.nomper) LIKE '%contratista%')
                               THEN 'Contratista'
                               ELSE 'Planta'
                           END AS tipo_contrato
                    FROM usu_dep ud
                    INNER JOIN usuario u ON ud.idusu = u.idusu
                    LEFT JOIN perfil p ON u.idper = p.idper
                    WHERE ud.id_dependencia = :id_dependencia
                    ORDER BY u.nomusu ASC";

            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(':id_dependencia', $id_dependencia, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception("Error al obtener empleados de la dependencia: " . $e->getMessage(), 500);
        }
    }

    /**
     * Obtiene todos los empleados/usuarios/contratistas del sistema.
     */
    public function obtenerTodosLosEmpleados() {
        try {
            $sql = "SELECT DISTINCT u.idusu, u.ndocusu, u.nomusu AS nombre, 
                           u.emausu AS email, COALESCE(ud.activo, 1) AS activo, u.telcan AS telefono, u.idper,
                           u.fecini, u.fecfin, ud.numero_contrato, ud.fecha_contrato,
                           p.nomper, ud.id_dependencia, d.nombre AS nombre_dependencia,
                           CASE 
                               WHEN (ud.numero_contrato IS NOT NULL AND ud.numero_contrato != '') 
                                 OR (u.fecini IS NOT NULL AND u.fecini != '' AND u.fecini != '0000-00-00') 
                                 OR (p.nomper IS NOT NULL AND LOWER(p.nomper) LIKE '%contratista%')
                               THEN 'Contratista'
                               ELSE 'Planta'
                           END AS tipo_contrato
                    FROM usuario u
                    LEFT JOIN usu_dep ud ON u.idusu = ud.idusu
                    LEFT JOIN dependencias d ON ud.id_dependencia = d.id_dependencia
                    LEFT JOIN perfil p ON u.idper = p.idper
                    ORDER BY u.nomusu ASC";

            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $stmt = $conexion->prepare($sql);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception("Error al obtener todos los empleados: " . $e->getMessage(), 500);
        }
    }

    /**
     * Obtiene todos los usuarios disponibles para asignar.
     */
    public function obtenerUsuariosDisponibles() {
        try {
            $sql = "SELECT u.idusu, u.ndocusu, u.nomusu AS nombre, u.emausu AS email 
                    FROM usuario u 
                    ORDER BY u.nomusu ASC 
                    LIMIT 200";

            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $stmt = $conexion->prepare($sql);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception("Error al obtener lista de usuarios: " . $e->getMessage(), 500);
        }
    }

    /**
     * Asigna un usuario a una dependencia.
     */
    public function asignarUsuarioDependencia($idusu, $id_dependencia) {
        try {
            $sql = "INSERT INTO usu_dep (idusu, id_dependencia, activo) 
                    VALUES (:idusu, :id_dependencia, 1) 
                    ON DUPLICATE KEY UPDATE activo = 1";

            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(':idusu', $idusu, PDO::PARAM_INT);
            $stmt->bindParam(':id_dependencia', $id_dependencia, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (Exception $e) {
            throw new Exception("Error al asignar el usuario a la dependencia: " . $e->getMessage(), 500);
        }
    }

    /**
     * Remueve la asignación de un usuario de una dependencia.
     */
    public function removerUsuarioDependencia($idusu, $id_dependencia) {
        try {
            $sql = "DELETE FROM usu_dep WHERE idusu = :idusu AND id_dependencia = :id_dependencia";

            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(':idusu', $idusu, PDO::PARAM_INT);
            $stmt->bindParam(':id_dependencia', $id_dependencia, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (Exception $e) {
            throw new Exception("Error al desvincular usuario: " . $e->getMessage(), 500);
        }
    }

    /**
     * Habilita (1) o deshabilita (0) la asignación de un usuario a una dependencia sin borrar la vinculación.
     */
    public function cambiarEstadoUsuarioDependencia($idusu, $id_dependencia, $activo) {
        try {
            $sql = "UPDATE usu_dep SET activo = :activo WHERE idusu = :idusu AND id_dependencia = :id_dependencia";

            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(':activo', $activo, PDO::PARAM_INT);
            $stmt->bindParam(':idusu', $idusu, PDO::PARAM_INT);
            $stmt->bindParam(':id_dependencia', $id_dependencia, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (Exception $e) {
            throw new Exception("Error al cambiar estado del usuario en la dependencia: " . $e->getMessage(), 500);
        }
    }

    /**
     * Retorna el número total de dependencias registradas.
     */
    public function contarTotalDependencias() {
        try {
            $sql = "SELECT COUNT(*) AS total FROM dependencias";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $stmt = $conexion->query($sql);
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            return $res ? (int)$res['total'] : 0;
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Retorna el número total de empleados vinculados a dependencias.
     */
    public function contarTotalEmpleados() {
        try {
            $sql = "SELECT COUNT(DISTINCT idusu) AS total FROM usu_dep";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $stmt = $conexion->query($sql);
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            return $res ? (int)$res['total'] : 0;
        } catch (Exception $e) {
            return 0;
        }
    }
}
?>
