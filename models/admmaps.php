<?php
require_once 'conexion.php';

class ADMMaps {

    public function obtenerDependencias() {
        try {
            $conexion = $this->db();
            $stmt = $conexion->prepare("SELECT id_dependencia, nombre, descripcion FROM dependencias ORDER BY nombre");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("ADMMaps::obtenerDependencias - " . $e->getMessage());
            return [];
        }
    }

    private function db() {
        $modelo = new conexion();
        return $modelo->get_conexion();
    }

    // Contratistas activos de la dependencia que aun no tienen paz y salvo aprobado
    private function condicionPendientes($busqueda = '') {
        $sql = " FROM usu_dep ud
                 INNER JOIN usuario u ON ud.idusu = u.idusu
                 LEFT JOIN perfil p ON u.idper = p.idper
                 LEFT JOIN (
                     SELECT d.idusu, d.calificacion
                     FROM detallesps d
                     INNER JOIN (
                         SELECT idusu, MAX(fechayhora) AS ultima_fecha
                         FROM detallesps
                         GROUP BY idusu
                     ) ult ON d.idusu = ult.idusu AND d.fechayhora = ult.ultima_fecha
                 ) ps ON u.idusu = ps.idusu
                 WHERE ud.id_dependencia = :id_dependencia
                   AND ud.activo = 1
                   AND u.idper IN (12, 35)
                   AND (ps.calificacion IS NULL OR ps.calificacion = 0)";
        if ($busqueda !== '') {
            $sql .= " AND (u.ndocusu LIKE :busqueda OR u.nomusu LIKE :busqueda)";
        }
        return $sql;
    }

    public function contarContratistas($id_dependencia, $busqueda = '') {
        try {
            $conexion = $this->db();
            $stmt = $conexion->prepare("SELECT COUNT(*) AS total" . $this->condicionPendientes($busqueda));
            $stmt->bindValue(':id_dependencia', $id_dependencia, PDO::PARAM_INT);
            if ($busqueda !== '') {
                $stmt->bindValue(':busqueda', '%' . $busqueda . '%', PDO::PARAM_STR);
            }
            $stmt->execute();
            $fila = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)($fila['total'] ?? 0);
        } catch (Exception $e) {
            error_log("ADMMaps::contarContratistas - " . $e->getMessage());
            return 0;
        }
    }

    public function obtenerContratistas($id_dependencia, $busqueda = '', $inicio = 0, $limite = 50) {
        try {
            $conexion = $this->db();
            $sql = "SELECT u.idusu, u.ndocusu, u.nomusu AS nombre, u.emausu AS email,
                           u.telcan AS telefono, u.idper, p.nomper AS perfil"
                  . $this->condicionPendientes($busqueda)
                  . " ORDER BY u.nomusu ASC LIMIT :inicio, :limite";
            $stmt = $conexion->prepare($sql);
            $stmt->bindValue(':id_dependencia', $id_dependencia, PDO::PARAM_INT);
            if ($busqueda !== '') {
                $stmt->bindValue(':busqueda', '%' . $busqueda . '%', PDO::PARAM_STR);
            }
            $stmt->bindValue(':inicio', (int)$inicio, PDO::PARAM_INT);
            $stmt->bindValue(':limite', (int)$limite, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("ADMMaps::obtenerContratistas - " . $e->getMessage());
            return [];
        }
    }

    public function obtenerIdsContratistas($id_dependencia) {
        try {
            $conexion = $this->db();
            $stmt = $conexion->prepare("SELECT u.idusu" . $this->condicionPendientes(''));
            $stmt->bindValue(':id_dependencia', $id_dependencia, PDO::PARAM_INT);
            $stmt->execute();
            $filas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return array_map('intval', array_column($filas, 'idusu'));
        } catch (Exception $e) {
            error_log("ADMMaps::obtenerIdsContratistas - " . $e->getMessage());
            return [];
        }
    }

    // Guarda de una vez: aprueba los marcados y rechaza (con motivo) los que informaron observacion
    public function guardarAprobacionMasiva($id_dependencia, $aprobados, $rechazados) {
        try {
            $conexion = $this->db();
            $conexion->beginTransaction();

            $aprobados = array_values(array_unique(array_filter(array_map('intval', (array)$aprobados))));

            $rechazos = [];
            foreach ((array)$rechazados as $r) {
                if (!is_array($r)) continue;
                $idusu = (int)($r['idusu'] ?? 0);
                $obs = trim((string)($r['observacion'] ?? ''));
                if ($idusu > 0 && $obs !== '' && !in_array($idusu, $aprobados, true)) {
                    $rechazos[] = ['idusu' => $idusu, 'obs' => $obs];
                }
            }

            $contA = 0;
            $contR = 0;

            foreach ($aprobados as $idusu) {
                if (!$this->perteneceDependencia($conexion, $idusu, $id_dependencia)) continue;
                $this->registrarCalificacion($conexion, $idusu, 1, '');
                $contA++;
            }

            foreach ($rechazos as $r) {
                if (!$this->perteneceDependencia($conexion, $r['idusu'], $id_dependencia)) continue;
                $this->registrarCalificacion($conexion, $r['idusu'], 0, $r['obs']);
                $contR++;
            }

            $conexion->commit();

            return [
                'success'    => true,
                'aprobados'  => $contA,
                'rechazados' => $contR
            ];
        } catch (Exception $e) {
            if (isset($conexion) && $conexion->inTransaction()) {
                $conexion->rollBack();
            }
            error_log("ADMMaps::guardarAprobacionMasiva - " . $e->getMessage());
            return ['success' => false, 'error' => 'Error al guardar la aprobación: ' . $e->getMessage()];
        }
    }

    private function perteneceDependencia($conexion, $idusu, $id_dependencia) {
        $stmt = $conexion->prepare("SELECT COUNT(*) FROM usu_dep
                                    WHERE idusu = :idusu AND id_dependencia = :dep AND activo = 1");
        $stmt->bindValue(':idusu', $idusu, PDO::PARAM_INT);
        $stmt->bindValue(':dep', $id_dependencia, PDO::PARAM_INT);
        $stmt->execute();
        return (int)$stmt->fetchColumn() > 0;
    }

    private function registrarCalificacion($conexion, $idusu, $calificacion, $observacion) {
        $stmt = $conexion->prepare("SELECT idpaz FROM pazysalvo WHERE idusu = :idusu ORDER BY idpaz DESC LIMIT 1");
        $stmt->bindValue(':idusu', $idusu, PDO::PARAM_INT);
        $stmt->execute();
        $idpaz = $stmt->fetchColumn();

        if (!$idpaz) {
            $stmt = $conexion->prepare("INSERT INTO pazysalvo (idusu) VALUES (:idusu)");
            $stmt->bindValue(':idusu', $idusu, PDO::PARAM_INT);
            $stmt->execute();
            $idpaz = (int)$conexion->lastInsertId();
        }

        $stmt = $conexion->prepare("INSERT INTO detallesps (idpaz, idusu, calificacion, observacion, fechayhora)
                                    VALUES (:idpaz, :idusu, :calificacion, :observacion, NOW())");
        $stmt->bindValue(':idpaz', $idpaz, PDO::PARAM_INT);
        $stmt->bindValue(':idusu', $idusu, PDO::PARAM_INT);
        $stmt->bindValue(':calificacion', $calificacion, PDO::PARAM_INT);
        $stmt->bindValue(':observacion', $observacion === '' ? null : $observacion, PDO::PARAM_STR);
        $stmt->execute();
    }
}
?>