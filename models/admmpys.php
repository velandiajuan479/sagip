<?php
require_once 'conexion.php';
class Mpys {

    public function obtenerDatosEmpleadoPorDocumento($documento) {
    try {
        
        $sql = "SELECT u.idusu, u.ndocusu, u.nomusu AS nombre, 
                u.emausu AS email, u.telcan AS telefono, u.idper,
                p.nomper AS perfil, d.nombre AS dependencia,
                ud.activo, ud.fecha_contrato, ud.numero_contrato
                FROM usuario u
                LEFT JOIN perfil p ON u.idper = p.idper
                LEFT JOIN usu_dep ud ON u.idusu = ud.idusu
                LEFT JOIN dependencias d ON ud.id_dependencia = d.id_dependencia
                WHERE u.ndocusu = :documento";

            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->bindParam(':documento', $documento, PDO::PARAM_STR);
            $result->execute();
            
            $empleado = $result->fetch(PDO::FETCH_ASSOC);
            
            if (!$empleado) {
                return false;
            }
            
            // Obtener historial solo si es necesario
            if (!isset($_GET['simple'])) { // Bandera para consultas simples
                $empleado['historial'] = $this->obtenerHistorialDetallesPorUsuario($empleado['idusu']);
                $empleado['ultima_observacion'] = !empty($empleado['historial']) ? $empleado['historial'][0] : null;
            }
            
            return $empleado;
            
        } catch(Exception $e) {
            error_log("Error en obtenerDatosEmpleadoPorDocumento: " . $e->getMessage());
            return false;
        }
    }

    public function obtenerDatosEmpleado($id_empleado) {
    try {
        $sql = "SELECT u.idusu, u.ndocusu, u.nomusu AS nombre, 
                u.emausu AS email, u.telcan AS telefono, 
                p.nomper AS perfil, d.nombre AS dependencia,
                ud.fecha_contrato, ud.numero_contrato
                FROM usuario u
                LEFT JOIN perfil p ON u.idper = p.idper
                LEFT JOIN usu_dep ud ON u.idusu = ud.idusu
                LEFT JOIN dependencias d ON ud.id_dependencia = d.id_dependencia
                WHERE u.idusu = :id_empleado";
                    
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            
            if (!$conexion) {
                throw new Exception("Error de conexión a la base de datos");
            }
            
            $result = $conexion->prepare($sql);
            $result->bindParam(':id_empleado', $id_empleado, PDO::PARAM_INT);
            
            if (!$result->execute()) {
                $errorInfo = $result->errorInfo();
                throw new Exception("Error al ejecutar consulta: " . $errorInfo[2]);
            }
            
            return $result->fetchAll(PDO::FETCH_ASSOC);
            
        } catch(Exception $e) {
            ManejoError($e);
        }
    }

    public function obtenerDependencias() {
        try {
            $sql = "SELECT id_dependencia, nombre, descripcion FROM dependencias ORDER BY nombre";

            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            
            if (!$conexion) {
                throw new Exception("Error de conexión a la base de datos");
            }
            
            $result = $conexion->prepare($sql);
            
            if (!$result->execute()) {
                $errorInfo = $result->errorInfo();
                throw new Exception("Error al ejecutar consulta: " . $errorInfo[2]);
            }
            
            return $result->fetchAll(PDO::FETCH_ASSOC);
            
        } catch(Exception $e) {
            ManejoError($e);
        }
    }

    public function obtenerEmpleadosPorDependencia($id_dependencia) {
    try {
        $sql = "SELECT u.idusu, u.ndocusu, u.nomusu AS nombre, 
                u.emausu AS email, ud.activo, u.telcan AS telefono, u.idper,
                p.nomper, ud.fecha_contrato, ud.numero_contrato
                FROM usu_dep ud
                INNER JOIN usuario u ON ud.idusu = u.idusu
                LEFT JOIN perfil p ON u.idper = p.idper
                WHERE ud.id_dependencia = :id_dependencia
                ORDER BY u.nomusu";
                    
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            
            if (!$conexion) {
                throw new Exception("Error de conexión a la base de datos");
            }
            
            $result = $conexion->prepare($sql);
            $result->bindParam(':id_dependencia', $id_dependencia, PDO::PARAM_INT);
            
            if (!$result->execute()) {
                $errorInfo = $result->errorInfo();
                throw new Exception("Error al ejecutar consulta: " . $errorInfo[2]);
            }
            
            return $result->fetchAll(PDO::FETCH_ASSOC);
            
        } catch(Exception $e) {
            ManejoError($e);
        }
    }



    public function gestionarPazSalvo($idusu, $idpaz, $iddetalle, $observacion, $calificacion) {
        try {
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            
            if (!$conexion) {
                throw new Exception("Error de conexión a la base de datos");
            }
            
            // Iniciar transacción
            $conexion->beginTransaction();
            
            // Gestionar el paz y salvo principal
            if ($idpaz) {
                // Verificar que el paz y salvo pertenece al usuario
                $sql = "SELECT idusu FROM pazysalvo WHERE idpaz = :idpaz";
                $result = $conexion->prepare($sql);
                $result->bindParam(':idpaz', $idpaz, PDO::PARAM_INT);
                $result->execute();
                $res = $result->fetch(PDO::FETCH_ASSOC);
                
                if (!$res || $res['idusu'] != $idusu) {
                    throw new Exception("El paz y salvo no pertenece al usuario especificado", 400);
                }
            } else {
                // Crear nuevo paz y salvo
                $sql = "INSERT INTO pazysalvo (idusu, fecha) VALUES (:idusu, NOW())";
                $result = $conexion->prepare($sql);
                $result->bindParam(':idusu', $idusu, PDO::PARAM_INT);
                
                if (!$result->execute()) {
                    $errorInfo = $result->errorInfo();
                    throw new Exception("Error al crear paz y salvo: " . $errorInfo[2]);
                }
                
                $idpaz = $conexion->lastInsertId();
            }
            
            // Gestionar los detalles
            $sql = "INSERT INTO detallesps (idpaz, idusu, calificacion, observacion, fechayhora) 
                    VALUES (:idpaz, :idusu, :calificacion, :observacion, NOW())";
            
            $result = $conexion->prepare($sql);
            $result->bindParam(':idpaz', $idpaz, PDO::PARAM_INT);
            $result->bindParam(':idusu', $idusu, PDO::PARAM_INT);
            $result->bindParam(':calificacion', $calificacion, PDO::PARAM_STR);
            $result->bindParam(':observacion', $observacion, PDO::PARAM_STR);
            
            if (!$result->execute()) {
                $errorInfo = $result->errorInfo();
                throw new Exception("Error al insertar detalles: " . $errorInfo[2]);
            }
            
            $iddetalle = $conexion->lastInsertId();
            
            // Confirmar transacción
            $conexion->commit();
            
            return [
                'idpaz' => $idpaz,
                'iddetalle' => $iddetalle,
                'fechayhora' => date('Y-m-d H:i:s')
            ];
            
        } catch(Exception $e) {
            // Revertir transacción en caso de error
            if (isset($conexion) && $conexion->inTransaction()) {
                $conexion->rollBack();
            }
            ManejoError($e);
        }
    }
    
    public function obtenerHistorialDetallesPorUsuario($idusu) {
        try {
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            
            $sql = "SELECT 
                    d.iddetalle, d.idpaz, d.fechayhora, 
                    d.observacion, d.calificacion,
                    p.fecha as fecha_paz
                    FROM detallesps d
                    INNER JOIN pazysalvo p ON d.idpaz = p.idpaz
                    WHERE d.idusu = :idusu
                    ORDER BY d.fechayhora DESC";
            
            $result = $conexion->prepare($sql);
            $result->bindParam(':idusu', $idusu, PDO::PARAM_INT);
            $result->execute();
            
            return $result->fetchAll(PDO::FETCH_ASSOC);
            
        } catch(Exception $e) {
            error_log("Error en obtenerHistorialDetallesPorUsuario: " . $e->getMessage());
            return [];
        }
    }
   

    public function obtenerListaPazYSalvos($id_dependencia) {
        try {
            $sql = "SELECT u.idusu, u.ndocusu, u.nomusu as nombre, u.telcan as telefono,
                    MAX(d.fechayhora) as ultima_fecha,
                    (SELECT d2.observacion FROM detallesps d2 
                    WHERE d2.idusu = u.idusu 
                    ORDER BY d2.fechayhora DESC LIMIT 1) as observacion,
                    (SELECT d2.calificacion FROM detallesps d2 
                    WHERE d2.idusu = u.idusu 
                    ORDER BY d2.fechayhora DESC LIMIT 1) as calificacion,
                    COUNT(DISTINCT p.idpaz) as total_paz_salvos,
                    ud.fecha_contrato, ud.numero_contrato
                    FROM usu_dep ud
                    JOIN usuario u ON ud.idusu = u.idusu
                    LEFT JOIN pazysalvo p ON u.idusu = p.idusu
                    LEFT JOIN detallesps d ON p.idpaz = d.idpaz
                    WHERE ud.id_dependencia = :id_dependencia
                    AND u.idper != 34  -- Excluir empleados de planta
                    GROUP BY u.idusu
                    ORDER BY u.nomusu";
                    
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->bindParam(':id_dependencia', $id_dependencia);
            $result->execute();
            
            return $result->fetchAll(PDO::FETCH_ASSOC);
            
        } catch(PDOException $e) {
            error_log("Error en obtenerListaPazYSalvos: " . $e->getMessage());
            return [];
        }
    }

    public function obtenerDatosCompletosEmpleado($documento) {
        try {            
            $sql = "SELECT u.*, p.nomper as perfil, d.nombre as dependencia,
                    ud.fecha_contrato, ud.numero_contrato, u.telcan as telefono
                    FROM usuario u
                    LEFT JOIN perfil p ON u.idper = p.idper
                    LEFT JOIN usu_dep ud ON u.idusu = ud.idusu
                    LEFT JOIN dependencias d ON ud.id_dependencia = d.id_dependencia
                    WHERE u.ndocusu = :documento";
            
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->bindParam(':documento', $documento);
            $result->execute();
            
            $empleado = $result->fetch(PDO::FETCH_ASSOC);
            
            if($empleado) {
                // Solo obtener historial si no es de planta
                if ($empleado['idper'] != 34) {
                    $empleado['historial'] = $this->obtenerHistorialPazSalvo($empleado['idusu']);
                    $empleado['ultima_observacion'] = !empty($empleado['historial']) ? $empleado['historial'][0] : null;
                }
                
                // Asegurar que los campos existan
                $empleado['telefono'] = $empleado['telefono'] ?? 'N/A';
                $empleado['fecha_contrato'] = $empleado['fecha_contrato'] ?? 'No registrada';
                $empleado['numero_contrato'] = $empleado['numero_contrato'] ?? 'No registrado';
            }
            
            return $empleado;
            
        } catch(PDOException $e) {
            error_log("Error en obtenerDatosCompletosEmpleado: " . $e->getMessage());
            return false;
        }
    }

    public function obtenerHistorialPazSalvo($idusu) {
        try {
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            
            $sql = "SELECT d.*, p.fecha as fecha_paz
                    FROM detallesps d
                    INNER JOIN pazysalvo p ON d.idpaz = p.idpaz
                    WHERE d.idusu = :idusu
                    ORDER BY d.fechayhora DESC";
            
            $result = $conexion->prepare($sql);
            $result->bindParam(':idusu', $idusu);
            $result->execute();
            
            return $result->fetchAll(PDO::FETCH_ASSOC);
            
        } catch(PDOException $e) {
            error_log("Error en obtenerHistorialPazSalvo: " . $e->getMessage());
            return [];
        }
    }
    
}
?>