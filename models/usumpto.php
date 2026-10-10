<?php
require_once(__DIR__ . '/conexion.php');

class usumpto extends conexion
{
    /**
     * Obtiene los IDs de perfiles que corresponden a aprendices y candidatos
     */
    public function obtenerIdsPerfilesAprendiz()
    {
        $sql = "SELECT idper FROM perfil WHERE LOWER(nomper) LIKE '%aprendiz%' OR LOWER(nomper) LIKE '%candidato%'";
        $stmt = $this->get_conexion()->query($sql);
        $ids = $stmt ? $stmt->fetchAll(PDO::FETCH_COLUMN) : [];
        if (empty($ids)) {
            $ids = [3, 4, 8, 13, 22, 23, 39, 41];
        }
        return $ids;
    }

    /**
     * Total de aprendices activos en el sistema.
     */
    public function totalAprendices()
    {
        $ids = $this->obtenerIdsPerfilesAprendiz();
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sql = "SELECT COUNT(DISTINCT idusu) AS total
                FROM usuario
                WHERE idper IN ($placeholders) OR idusu IN (SELECT DISTINCT idusu FROM patrocinio)";
        $stmt = $this->get_conexion()->prepare($sql);
        $stmt->execute($ids);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) ($row['total'] ?? 0);
    }

    /**
     * Total de aprendices con un patrocinio activo (estpat = 1).
     */
    public function totalConPatrocinio()
    {
        $sql = "SELECT COUNT(DISTINCT p.idusu) AS total
                FROM patrocinio p
                WHERE p.estpat = 1";
        $stmt = $this->get_conexion()->query($sql);
        $row = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : null;
        return (int) ($row['total'] ?? 0);
    }

    /**
     * Total de empresas con aprendices activos vinculados.
     */
    public function totalEmpresasActivas()
    {
        $sql = "SELECT COUNT(DISTINCT p.idemp) AS total
                FROM patrocinio p
                WHERE p.estpat = 1";
        $stmt = $this->get_conexion()->query($sql);
        $row = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : null;
        return (int) ($row['total'] ?? 0);
    }

    /**
     * Datos para las tarjetas del dashboard.
     */
    public function dashboard()
    {
        $total = $this->totalAprendices();
        $con   = $this->totalConPatrocinio();
        $emp   = $this->totalEmpresasActivas();
        return [
            'total'     => $total,
            'con'       => $con,
            'sin'       => max(0, $total - $con),
            'empresas'  => $emp,
        ];
    }

    /**
     * Obtiene el patrocinio del aprendiz especificado (el más reciente).
     * Toda la información del jefe y empresa proviene de la tabla `empresa`.
     */
    public function obtenerPatrocinioUsuario($idusu)
    {
        $sql = "SELECT p.idpat,
                       p.idusu,
                       p.idemp,
                       p.estpat,
                       p.usucre,
                       p.feccre,
                       p.feccre AS fecinipat,
                       e.numdocemp,
                       e.nomemp,
                       e.diremp,
                       e.codubi,
                       e.nomconemp,
                       e.telemp,
                       e.telemp AS telempresa,
                       e.nomconemp AS nomjefpat,
                       e.telemp AS teljefpat
                FROM patrocinio p
                INNER JOIN empresa e ON e.idemp = p.idemp
                WHERE p.idusu = :idusu
                ORDER BY p.idpat DESC
                LIMIT 1";
        $stmt = $this->get_conexion()->prepare($sql);
        $stmt->execute([':idusu' => $idusu]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Listado general para DataTables.
     */
    public function listarAprendices()
    {
        $ids = $this->obtenerIdsPerfilesAprendiz();
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sql = "SELECT
                    u.idusu,
                    u.ndocusu,
                    u.nomusu,
                    u.emausu,
                    e.idemp,
                    e.nomemp,
                    e.numdocemp,
                    e.nomconemp,
                    e.telemp,
                    p.idpat,
                    p.estpat,
                    p.feccre,
                    p.feccre AS fecinipat,
                    CASE 
                        WHEN p.idpat IS NULL THEN 0 
                        WHEN p.estpat = 1 THEN 1 
                        WHEN p.estpat = 2 OR p.estpat = 0 THEN 2 
                        WHEN p.estpat = 3 THEN 3 
                        ELSE 0 
                    END AS estado_codigo
                FROM usuario u
                LEFT JOIN (
                    SELECT idusu, MAX(idpat) AS max_idpat
                    FROM patrocinio
                    GROUP BY idusu
                ) ult ON ult.idusu = u.idusu
                LEFT JOIN patrocinio p ON p.idpat = ult.max_idpat
                LEFT JOIN empresa e ON e.idemp = p.idemp
                WHERE u.idper IN ($placeholders) OR u.idusu IN (SELECT DISTINCT idusu FROM patrocinio)
                ORDER BY u.nomusu ASC";
        $stmt = $this->get_conexion()->prepare($sql);
        $stmt->execute($ids);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Aprendices que NO tienen un patrocinio activo (estpat = 1).
     */
    public function listarAprendicesSinPatrocinio()
    {
        $ids = $this->obtenerIdsPerfilesAprendiz();
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sql = "SELECT u.idusu, u.ndocusu, u.nomusu
                FROM usuario u
                LEFT JOIN patrocinio p ON p.idusu = u.idusu AND p.estpat = 1
                WHERE (u.idper IN ($placeholders) OR u.idusu IN (SELECT DISTINCT idusu FROM patrocinio))
                  AND p.idpat IS NULL
                ORDER BY u.nomusu ASC";
        $stmt = $this->get_conexion()->prepare($sql);
        $stmt->execute($ids);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Listar todas las empresas con sus campos de contacto desde `empresa`.
     */
    public function listarEmpresas()
    {
        $sql = "SELECT idemp, numdocemp, nomemp, diremp, codubi, nomconemp, telemp 
                FROM empresa 
                ORDER BY nomemp ASC";
        $stmt = $this->get_conexion()->query($sql);
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    /**
     * Listado de empresas que tienen aprendices con patrocinio,
     * ordenadas de MAYOR a MENOR cantidad de aprendices patrocinados.
     */
    public function listarEmpresasRanking()
    {
        $sql = "SELECT 
                    e.idemp, 
                    e.numdocemp, 
                    e.nomemp, 
                    e.diremp, 
                    e.nomconemp, 
                    e.telemp,
                    COUNT(p.idpat) AS total_aprendices,
                    SUM(CASE WHEN p.estpat = 1 THEN 1 ELSE 0 END) AS aprendices_activos,
                    SUM(CASE WHEN p.estpat = 2 OR p.estpat = 0 THEN 1 ELSE 0 END) AS aprendices_cancelados
                FROM empresa e
                INNER JOIN patrocinio p ON p.idemp = e.idemp
                GROUP BY e.idemp, e.numdocemp, e.nomemp, e.diremp, e.nomconemp, e.telemp
                ORDER BY total_aprendices DESC, e.nomemp ASC";
        $stmt = $this->get_conexion()->query($sql);
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    /**
     * Obtiene los aprendices patrocinados por una empresa en específico,
     * calculando inasistencias, llamados de atención y estado académico.
     */
    public function listarAprendicesPorEmpresa($idemp)
    {
        $db = $this->get_conexion();
        $sql = "SELECT 
                    p.idpat,
                    p.idusu,
                    p.idemp,
                    p.estpat,
                    p.feccre,
                    p.feccre AS fecinipat,
                    e.nomemp,
                    e.numdocemp,
                    e.nomconemp,
                    e.telemp,
                    u.ndocusu,
                    u.nomusu,
                    u.emausu,
                    u.telcan
                FROM patrocinio p
                INNER JOIN usuario u ON u.idusu = p.idusu
                INNER JOIN empresa e ON e.idemp = p.idemp
                WHERE p.idemp = :idemp
                ORDER BY p.estpat DESC, u.nomusu ASC";
        $stmt = $db->prepare($sql);
        $stmt->execute([':idemp' => $idemp]);
        $aprendices = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $resultado = [];
        foreach ($aprendices as $a) {
            $idusu = $a['idusu'];

            // 1. Horas de inasistencia desde `inasistencia`
            $sqlIna = "SELECT COALESCE(SUM(horasina), 0) AS total_horas FROM inasistencia WHERE idusu = :idusu";
            $stmtIna = $db->prepare($sqlIna);
            $stmtIna->execute([':idusu' => $idusu]);
            $horasIna = (int) ($stmtIna->fetch(PDO::FETCH_ASSOC)['total_horas'] ?? 0);

            // 2. Llamados de atención desde `llamado`
            $sqlLlam = "SELECT COUNT(*) AS total_llamados FROM llamado WHERE idusu = :idusu";
            $stmtLlam = $db->prepare($sqlLlam);
            $stmtLlam->execute([':idusu' => $idusu]);
            $llamados = (int) ($stmtLlam->fetch(PDO::FETCH_ASSOC)['total_llamados'] ?? 0);

            // 3. Juicios evaluados / aprobados desde `juicio`
            $sqlJui = "SELECT 
                        COUNT(*) AS total_juicios,
                        SUM(CASE WHEN UPPER(caljui) LIKE '%APROB%' AND UPPER(caljui) NOT LIKE '%NO%' THEN 1 ELSE 0 END) AS aprobados,
                        SUM(CASE WHEN UPPER(caljui) LIKE '%NO%APROB%' OR UPPER(caljui) LIKE '%DEFIC%' THEN 1 ELSE 0 END) AS no_aprobados,
                        SUM(CASE WHEN UPPER(caljui) LIKE '%EVALUAR%' OR caljui IS NULL OR caljui = '' THEN 1 ELSE 0 END) AS por_evaluar
                       FROM juicio 
                       WHERE idusu = :idusu";
            $stmtJui = $db->prepare($sqlJui);
            $stmtJui->execute([':idusu' => $idusu]);
            $juiData = $stmtJui->fetch(PDO::FETCH_ASSOC);

            $totalJuicios = (int) ($juiData['total_juicios'] ?? 0);
            $aprobados    = (int) ($juiData['aprobados'] ?? 0);
            $noAprobados  = (int) ($juiData['no_aprobados'] ?? 0);
            $porEvaluar   = (int) ($juiData['por_evaluar'] ?? 0);

            $porcentajeAvance = $totalJuicios > 0 ? round(($aprobados / $totalJuicios) * 100) : 0;

            // Determinación del estado académico y riesgo de deserción
            $estadoAcademico = 'Va aprobando';
            $riesgoDesercion = false;
            $motivoAlerta    = [];

            if ($horasIna >= 100) {
                $riesgoDesercion = true;
                $motivoAlerta[] = "Inasistencias críticas ({$horasIna} hrs)";
            } elseif ($horasIna >= 40) {
                $motivoAlerta[] = "Inasistencias acumuladas ({$horasIna} hrs)";
            }

            if ($llamados >= 2) {
                $riesgoDesercion = true;
                $motivoAlerta[] = "{$llamados} llamados de atención";
            } elseif ($llamados === 1) {
                $motivoAlerta[] = "1 llamado de atención";
            }

            if ($totalJuicios > 0) {
                if ($porcentajeAvance < 60 || $noAprobados > 0) {
                    $estadoAcademico = 'Bajo rendimiento académico';
                    $motivoAlerta[] = "Avance académico al {$porcentajeAvance}% ({$aprobados}/{$totalJuicios})";
                    if ($porcentajeAvance < 50) {
                        $riesgoDesercion = true;
                    }
                }
            } else {
                $estadoAcademico = 'En formación (Sin juicios)';
            }

            $a['horas_inasistencia'] = $horasIna;
            $a['total_llamados']     = $llamados;
            $a['total_juicios']      = $totalJuicios;
            $a['juicios_aprobados']  = $aprobados;
            $a['juicios_no_aprob']   = $noAprobados;
            $a['juicios_por_evaluar']= $porEvaluar;
            $a['porcentaje_avance']  = $porcentajeAvance;
            $a['estado_academico']   = $estadoAcademico;
            $a['riesgo_desercion']   = $riesgoDesercion;
            $a['motivo_alerta']      = implode(', ', $motivoAlerta);

            $resultado[] = $a;
        }

        return $resultado;
    }

    /**
     * Detecta la empresa asociada a un usuario (si es empresario).
     */
    public function obtenerEmpresaDeUsuario($idusu, $ndocusu = null)
    {
        $db = $this->get_conexion();
        if ($ndocusu) {
            $sql = "SELECT * FROM empresa WHERE numdocemp = :ndoc LIMIT 1";
            $stmt = $db->prepare($sql);
            $stmt->execute([':ndoc' => $ndocusu]);
            $emp = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($emp) return $emp;
        }

        $sql = "SELECT * FROM empresa WHERE usucre = :idusu LIMIT 1";
        $stmt = $db->prepare($sql);
        $stmt->execute([':idusu' => $idusu]);
        $emp = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($emp) return $emp;

        $sql = "SELECT * FROM empresa ORDER BY idemp ASC LIMIT 1";
        return $db->query($sql)->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Registra el patrocinio usando las tablas originales:
     * - Guarda los datos del jefe y teléfono directamente en la tabla `empresa` (nomconemp, telemp).
     * - Inserta el registro en `patrocinio` con su fecha de inicio en `feccre`.
     */
    public function registrarPatrocinio($idusu, $idemp, $fecinipat, $nomconemp, $telemp, $usucre)
    {
        try {
            $db = $this->get_conexion();

            // 1. Verificar si el aprendiz ya tiene un patrocinio activo
            $sqlChk = "SELECT idpat FROM patrocinio WHERE idusu = :idusu AND estpat = 1";
            $stmtChk = $db->prepare($sqlChk);
            $stmtChk->execute([':idusu' => $idusu]);
            if ($stmtChk->fetch()) {
                return ['ok' => false, 'msg' => 'Este aprendiz ya cuenta con un patrocinio registrado y activo.'];
            }

            // 2. Si se proporciona nombre de contacto o teléfono, actualizar la tabla `empresa`
            if (!empty($nomconemp) || !empty($telemp)) {
                $sqlEmp = "UPDATE empresa SET nomconemp = :nomconemp, telemp = :telemp WHERE idemp = :idemp";
                $stmtEmp = $db->prepare($sqlEmp);
                $stmtEmp->execute([
                    ':nomconemp' => $nomconemp,
                    ':telemp'    => $telemp,
                    ':idemp'     => $idemp
                ]);
            }

            // 3. Fecha de inicio del patrocinio en feccre
            $fechaGuardar = !empty($fecinipat) ? date('Y-m-d H:i:s', strtotime($fecinipat . ' ' . date('H:i:s'))) : date('Y-m-d H:i:s');

            // 4. Insertar en tabla original `patrocinio`
            $sql = "INSERT INTO patrocinio (idusu, idemp, estpat, usucre, feccre)
                    VALUES (:idusu, :idemp, 1, :usucre, :feccre)";
            $stmt = $db->prepare($sql);
            $ok = $stmt->execute([
                ':idusu'  => $idusu,
                ':idemp'  => $idemp,
                ':usucre' => $usucre,
                ':feccre' => $fechaGuardar,
            ]);

            return [
                'ok'  => (bool) $ok,
                'msg' => $ok ? 'Patrocinio registrado exitosamente.' : 'No fue posible registrar el patrocinio.',
                'idpat' => $ok ? $db->lastInsertId() : null
            ];
        } catch (Exception $e) {
            return ['ok' => false, 'msg' => 'Error al registrar patrocinio: ' . $e->getMessage()];
        }
    }

    /**
     * Cancela un patrocinio actualizando `estpat = 0` (o 2) en la tabla `patrocinio`.
     */
    public function cancelarPatrocinio($idpat, $motivo = null)
    {
        try {
            $db = $this->get_conexion();

            $sql = "UPDATE patrocinio SET estpat = 0 WHERE idpat = :idpat";
            $stmt = $db->prepare($sql);
            $ok = $stmt->execute([':idpat' => $idpat]);

            return [
                'ok'  => (bool) $ok,
                'msg' => $ok ? 'El patrocinio ha sido cancelado correctamente.' : 'No se pudo cancelar el patrocinio.',
            ];
        } catch (Exception $e) {
            return ['ok' => false, 'msg' => 'Error al cancelar: ' . $e->getMessage()];
        }
    }

    /**
     * Obtiene los datos detallados para el Acta de Cancelación.
     */
    public function obtenerDatosActaCancelacion($idpat, $motivoTexto = null)
    {
        $sql = "SELECT p.idpat,
                       p.idusu,
                       p.idemp,
                       p.estpat,
                       p.feccre,
                       p.feccre AS fecinipat,
                       u.nomusu,
                       u.ndocusu,
                       u.emausu,
                       u.telcan,
                       e.nomemp,
                       e.numdocemp,
                       e.diremp,
                       e.telemp,
                       e.nomconemp
                FROM patrocinio p
                INNER JOIN usuario u ON u.idusu = p.idusu
                INNER JOIN empresa e ON e.idemp = p.idemp
                WHERE p.idpat = :idpat";
        $stmt = $this->get_conexion()->prepare($sql);
        $stmt->execute([':idpat' => $idpat]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $row['motcanpat'] = !empty($motivoTexto) ? $motivoTexto : 'Cancelación de patrocinio por inasistencias reiteradas o bajo rendimiento académico.';
            $row['feccanpat'] = date('Y-m-d H:i:s');
        }
        return $row ?: null;
    }

    /**
     * Obtiene los datos para el Acta de Seguimiento de una empresa.
     */
    public function obtenerDatosActaSeguimiento($idemp)
    {
        $db = $this->get_conexion();
        $sqlEmp = "SELECT * FROM empresa WHERE idemp = :idemp";
        $stmtEmp = $db->prepare($sqlEmp);
        $stmtEmp->execute([':idemp' => $idemp]);
        $empresa = $stmtEmp->fetch(PDO::FETCH_ASSOC);

        if (!$empresa) return null;

        $aprendices = $this->listarAprendicesPorEmpresa($idemp);

        return [
            'empresa'    => $empresa,
            'aprendices' => $aprendices,
            'fecha_acta' => date('Y-m-d H:i:s')
        ];
    }
}
