<?php
require_once(__DIR__ . "/conexion.php");

/**
 * Modelo de Acta de Deserción y Gestión de Justificaciones (MVC)
 * Proyecto SAGI - SENA
 */
class Desmadsc
{
    private $conexion;

    public function __construct()
    {
        $modelo = new conexion();
        $this->conexion = $modelo->get_conexion();
        $this->initTableJustificaciones();
    }

    /**
     * Asegura la existencia de la tabla para el sistema de justificación de ausencias
     */
    private function initTableJustificaciones()
    {
        try {
            $sql = "CREATE TABLE IF NOT EXISTS `des_docjust` (
                `idjust` BIGINT(20) NOT NULL AUTO_INCREMENT,
                `idusu` BIGINT(20) NOT NULL,
                `idfic` VARCHAR(20) NOT NULL,
                `tipodoc` ENUM('Cita médica','Incapacidad','Muerte de un familiar de primer grado de consanguinidad') NOT NULL,
                `nomarc` VARCHAR(255) NOT NULL,
                `rutarc` VARCHAR(255) NOT NULL,
                `fecjust` DATE NOT NULL,
                `fecreg` DATETIME NOT NULL,
                `obsjust` TEXT DEFAULT NULL,
                `estado` ENUM('Pendiente','Aprobado','Rechazado/Inválido') DEFAULT 'Pendiente',
                `obs_instructor` TEXT DEFAULT NULL,
                `idusu_instructor` BIGINT(20) DEFAULT NULL,
                `fec_revision` DATETIME DEFAULT NULL,
                `falta_agregada` TINYINT(1) DEFAULT 0,
                PRIMARY KEY (`idjust`),
                KEY `idx_just_usu` (`idusu`),
                KEY `idx_just_fic` (`idfic`),
                KEY `idx_just_est` (`estado`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
            $this->conexion->exec($sql);
        } catch (PDOException $e) {
            error_log("Error inicializando tabla des_docjust: " . $e->getMessage());
        }
    }

    /**
     * Tipos válidos autorizados para documentos de justificación
     */
    public static function getTiposValidos(): array
    {
        return [
            'Cita médica',
            'Incapacidad',
            'Muerte de un familiar de primer grado de consanguinidad'
        ];
    }

    /**
     * Dashboard general consolidado
     */
    public function getDashboardResumen(): array
    {
        try {
            // Total fichas monitoreadas
            $qFic = $this->conexion->query("SELECT COUNT(*) AS total FROM ficha WHERE idfic > 1000");
            $totalFichas = (int) $qFic->fetchColumn();

            // Total aprendices desertados
            $qDes = $this->conexion->query("SELECT COUNT(DISTINCT u.idusu) AS total 
                                            FROM usuario u 
                                            WHERE u.actusu = 3 
                                               OR u.idusu IN (SELECT idusu FROM desercion)");
            $totalDesertados = (int) $qDes->fetchColumn();

            // Total horas de inasistencia en el sistema
            $qHor = $this->conexion->query("SELECT COALESCE(SUM(horasina), 0) AS total FROM inasistencia");
            $totalHoras = (int) $qHor->fetchColumn();

            // Conteo de justificaciones
            $qJus = $this->conexion->query("SELECT 
                COUNT(*) AS total,
                COALESCE(SUM(CASE WHEN estado = 'Pendiente' THEN 1 ELSE 0 END), 0) AS pendientes,
                COALESCE(SUM(CASE WHEN estado = 'Aprobado' THEN 1 ELSE 0 END), 0) AS aprobadas,
                COALESCE(SUM(CASE WHEN estado = 'Rechazado/Inválido' THEN 1 ELSE 0 END), 0) AS invalidas,
                COALESCE(SUM(CASE WHEN falta_agregada = 1 THEN 1 ELSE 0 END), 0) AS faltas_aplicadas
                FROM des_docjust");
            $resJus = $qJus->fetch(PDO::FETCH_ASSOC) ?: [
                'total' => 0,
                'pendientes' => 0,
                'aprobadas' => 0,
                'invalidas' => 0,
                'faltas_aplicadas' => 0
            ];

            return [
                'total_fichas' => $totalFichas,
                'total_desertados' => $totalDesertados,
                'total_horas_ina' => $totalHoras,
                'just_total' => (int) $resJus['total'],
                'just_pendientes' => (int) $resJus['pendientes'],
                'just_aprobadas' => (int) $resJus['aprobadas'],
                'just_invalidas' => (int) $resJus['invalidas'],
                'faltas_aplicadas' => (int) $resJus['faltas_aplicadas']
            ];
        } catch (PDOException $e) {
            error_log("Error en getDashboardResumen: " . $e->getMessage());
            return [
                'total_fichas' => 0,
                'total_desertados' => 0,
                'total_horas_ina' => 0,
                'just_total' => 0,
                'just_pendientes' => 0,
                'just_aprobadas' => 0,
                'just_invalidas' => 0,
                'faltas_aplicadas' => 0
            ];
        }
    }

    /**
     * Dashboard: Presenta por ficha cuántos estudiantes han desertado dinámicamente
     */
    public function getFichasDesercionStats(): array
    {
        try {
            $sql = "SELECT 
                        f.idfic,
                        f.nomfic,
                        COALESCE(v.nomval, 'No asignada') AS jornada,
                        COALESCE(p.nompro, 'Sin programa') AS nompro,
                        COUNT(DISTINCT uf.idusu) AS total_aprendices,
                        COUNT(DISTINCT CASE 
                            WHEN u.actusu = 3 OR u.idusu IN (SELECT idusu FROM desercion) 
                            THEN u.idusu 
                        END) AS desertados,
                        COUNT(DISTINCT CASE WHEN u.actusu = 1 THEN u.idusu END) AS activos,
                        COALESCE((
                            SELECT SUM(i.horasina) 
                            FROM inasistencia i 
                            INNER JOIN usufic ufx ON i.idusu = ufx.idusu 
                            WHERE ufx.idfic = f.idfic
                        ), 0) AS total_horas_inasistencia,
                        COALESCE((
                            SELECT COUNT(*) 
                            FROM des_docjust dj 
                            WHERE dj.idfic = f.idfic AND dj.estado = 'Pendiente'
                        ), 0) AS just_pendientes
                    FROM ficha AS f
                    LEFT JOIN valor AS v ON f.jornada = v.idval
                    LEFT JOIN programa AS p ON f.codpro = p.codpro
                    LEFT JOIN usufic AS uf ON f.idfic = uf.idfic
                    LEFT JOIN usuario AS u ON uf.idusu = u.idusu
                    WHERE f.idfic > 1000
                    GROUP BY f.idfic, f.nomfic, v.nomval, p.nompro
                    ORDER BY desertados DESC, total_horas_inasistencia DESC, f.idfic ASC;";

            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            $fichas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Calcular porcentajes de deserción
            foreach ($fichas as &$fic) {
                $total = (int) $fic['total_aprendices'];
                $des = (int) $fic['desertados'];
                $fic['tasa_desercion'] = ($total > 0) ? round(($des / $total) * 100, 1) : 0;
            }
            unset($fic);

            return $fichas;
        } catch (PDOException $e) {
            error_log("Error en getFichasDesercionStats: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Listado de aprendices de una ficha con desglose de inasistencias y deserción
     */
    public function getAprendicesByFicha($idfic): array
    {
        try {
            $sql = "SELECT 
                        u.idusu,
                        u.ndocusu,
                        u.nomusu,
                        u.actusu,
                        u.emausu,
                        COALESCE(u.telcan, '') AS telcan,
                        COALESCE(v.nomval, 'Sin estado') AS nomest,
                        COALESCE((SELECT SUM(horasina) FROM inasistencia i WHERE i.idusu = u.idusu AND i.idfic = :idfic), 0) AS horasina,
                        COALESCE((SELECT COUNT(*) FROM llamado l WHERE l.idusu = u.idusu AND l.tipllam = 1) 
                                      + (SELECT COUNT(*) FROM des_llamados dl WHERE dl.idusu = u.idusu AND dl.tipllam = 1), 0) AS llamver,
                        COALESCE((SELECT COUNT(*) FROM llamado l WHERE l.idusu = u.idusu AND l.tipllam = 2) 
                                      + (SELECT COUNT(*) FROM des_llamados dl WHERE dl.idusu = u.idusu AND dl.tipllam = 2), 0) AS llamesc,
                        (SELECT MAX(fecllam) FROM llamado l WHERE l.idusu = u.idusu) AS fecllam,
                        CASE 
                            WHEN u.actusu = 3 OR u.idusu IN (SELECT idusu FROM desercion WHERE idfic = :idfic2) 
                            THEN 1 ELSE 0 
                        END AS es_desertado,
                        COALESCE((
                            SELECT COUNT(*) 
                            FROM des_docjust dj 
                            WHERE dj.idusu = u.idusu AND dj.estado = 'Pendiente'
                        ), 0) AS just_pendientes
                    FROM usuario AS u
                    INNER JOIN usufic AS uf ON u.idusu = uf.idusu
                    LEFT JOIN valor AS v ON u.actusu = v.parval AND v.iddom = 10
                    WHERE uf.idfic = :idfic3
                    ORDER BY es_desertado DESC, horasina DESC, u.nomusu ASC;";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindValue(':idfic', $idfic);
            $stmt->bindValue(':idfic2', $idfic);
            $stmt->bindValue(':idfic3', $idfic);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en getAprendicesByFicha: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene todos los detalles de un estudiante para el Acta de Deserción:
     * - Datos del aprendiz y ficha
     * - Inasistencias detalladas (horas totales y fechas exactas de faltas)
     * - Resultados no aprobados
     * - Resultados atrasados (claramente marcados)
     */
    public function getDetalleActaEstudiante($idusu, $idfic): ?array
    {
        try {
            // 1. Datos básicos del estudiante y ficha
            $sqlEst = "SELECT 
                            u.idusu,
                            u.ndocusu,
                            u.nomusu,
                            u.actusu,
                            u.emausu,
                            COALESCE(u.telcan, '') AS telcan,
                            COALESCE(td.nomval, 'CC') AS tipdoc,
                            COALESCE(est.nomval, 'Sin estado') AS nomest,
                            f.idfic,
                            f.nomfic,
                            f.finific,
                            f.ffinfic,
                            COALESCE(jor.nomval, 'Diurna') AS jornada,
                            COALESCE(p.nompro, 'Sin programa') AS nompro
                       FROM usuario AS u
                       INNER JOIN usufic AS uf ON u.idusu = uf.idusu
                       INNER JOIN ficha AS f ON uf.idfic = f.idfic
                       LEFT JOIN valor AS td ON u.tdousu = td.idval
                       LEFT JOIN valor AS est ON u.actusu = est.parval AND est.iddom = 10
                       LEFT JOIN valor AS jor ON f.jornada = jor.idval
                       LEFT JOIN programa AS p ON f.codpro = p.codpro
                       WHERE u.idusu = :idusu AND f.idfic = :idfic
                       LIMIT 1;";
            $stmtEst = $this->conexion->prepare($sqlEst);
            $stmtEst->bindValue(':idusu', $idusu, PDO::PARAM_INT);
            $stmtEst->bindValue(':idfic', $idfic);
            $stmtEst->execute();
            $estudiante = $stmtEst->fetch(PDO::FETCH_ASSOC);

            if (!$estudiante) {
                return null;
            }

            // 2. Inasistencias detalladas (horas y fechas de faltas)
            $sqlIna = "SELECT 
                            idina,
                            fecina,
                            fecreg,
                            horasina,
                            comina
                       FROM inasistencia
                       WHERE idusu = :idusu AND idfic = :idfic
                       ORDER BY fecina DESC, idina DESC;";
            $stmtIna = $this->conexion->prepare($sqlIna);
            $stmtIna->bindValue(':idusu', $idusu, PDO::PARAM_INT);
            $stmtIna->bindValue(':idfic', $idfic);
            $stmtIna->execute();
            $inasistencias = $stmtIna->fetchAll(PDO::FETCH_ASSOC);

            $totalHorasFaltadas = 0;
            $fechasFaltas = [];
            foreach ($inasistencias as $ina) {
                $totalHorasFaltadas += (int) $ina['horasina'];
                $fechasFaltas[] = [
                    'fecha' => $ina['fecina'],
                    'horas' => (int) $ina['horasina'],
                    'comentario' => $ina['comina'],
                    'fecreg' => $ina['fecreg']
                ];
            }

            // 3. Resultados de Aprendizaje: No Aprobados y Atrasados
            // Extraer de juicio, resultado, competencia y planses
            $sqlRes = "SELECT 
                            r.idres,
                            r.nomres,
                            c.idcom,
                            COALESCE(c.descom, 'Competencia de Formación') AS descom,
                            COALESCE(j.caljui, '0.00') AS caljui,
                            j.fecjuieva,
                            ps.fecha_programada,
                            COALESCE(ar.estado, 'pendiente') AS estado_aprobacion,
                            CASE 
                                -- Se considera no aprobado si la calificación no es aprobatoria
                                WHEN j.caljui IS NULL 
                                  OR j.caljui IN ('0.00', '0', 'No Aprobado', 'D', 'Deficiente', '') 
                                  OR ar.estado = 'no aprobado'
                                THEN 1 ELSE 0 
                            END AS no_aprobado,
                            CASE 
                                -- Se marca como atrasado si no está aprobado Y su fecha ya venció
                                WHEN (
                                    j.caljui IS NULL 
                                    OR j.caljui IN ('0.00', '0', 'No Aprobado', 'D', 'Deficiente', '') 
                                    OR ar.estado != 'aprobado'
                                ) AND (
                                    (j.fecjuieva IS NOT NULL AND j.fecjuieva < CURDATE())
                                    OR (ps.fecha_programada IS NOT NULL AND ps.fecha_programada < CURDATE())
                                    OR (f.ffinfic IS NOT NULL AND f.ffinfic < CURDATE())
                                )
                                THEN 1 ELSE 0 
                            END AS atrasado
                       FROM resultado AS r
                       INNER JOIN competencia AS c ON r.idcom = c.idcom
                       LEFT JOIN ficha AS f ON f.idfic = :idfic1
                       LEFT JOIN juicio AS j ON (j.idres = r.idres AND (j.idusu = :idusu1 OR j.idfic = :idfic2))
                       LEFT JOIN planses AS ps ON (ps.idres = r.idres AND ps.idfic = :idfic3)
                       LEFT JOIN aprobacion_resultado AS ar ON (ar.idres = r.idres AND ar.idfic = :idfic4)
                       WHERE (
                           -- Mostrar resultados reprobados, pendientes o atrasados
                           j.caljui IS NULL 
                           OR j.caljui IN ('0.00', '0', 'No Aprobado', 'D', 'Deficiente', '')
                           OR ar.estado != 'aprobado'
                           OR ps.fecha_programada < CURDATE()
                       )
                       GROUP BY r.idres, r.nomres, c.idcom, c.descom, j.caljui, j.fecjuieva, ps.fecha_programada, ar.estado
                       ORDER BY atrasado DESC, no_aprobado DESC, r.idres ASC
                       LIMIT 50;";

            $stmtRes = $this->conexion->prepare($sqlRes);
            $stmtRes->bindValue(':idfic1', $idfic);
            $stmtRes->bindValue(':idusu1', $idusu, PDO::PARAM_INT);
            $stmtRes->bindValue(':idfic2', $idfic);
            $stmtRes->bindValue(':idfic3', $idfic);
            $stmtRes->bindValue(':idfic4', $idfic);
            $stmtRes->execute();
            $resultados = $stmtRes->fetchAll(PDO::FETCH_ASSOC);

            // 4. Acta formal existente (si ya se generó)
            $sqlAct = "SELECT d.iddec, d.idact, d.fecregdec, d.obsdec, a.conact, a.fecact, a.coorusu
                       FROM desercion AS d
                       INNER JOIN acta AS a ON d.idact = a.idact
                       WHERE d.idusu = :idusu AND d.idfic = :idfic
                       ORDER BY d.fecregdec DESC
                       LIMIT 1;";
            $stmtAct = $this->conexion->prepare($sqlAct);
            $stmtAct->bindValue(':idusu', $idusu, PDO::PARAM_INT);
            $stmtAct->bindValue(':idfic', $idfic);
            $stmtAct->execute();
            $actaExistente = $stmtAct->fetch(PDO::FETCH_ASSOC) ?: null;

            return [
                'estudiante' => $estudiante,
                'total_horas_faltadas' => $totalHorasFaltadas,
                'fechas_faltas' => $fechasFaltas,
                'inasistencias' => $inasistencias,
                'resultados' => $resultados,
                'acta_existente' => $actaExistente
            ];
        } catch (PDOException $e) {
            error_log("Error en getDetalleActaEstudiante: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Sistema de Justificación: Consulta documentos subidos
     */
    public function getJustificaciones($idusu = null, $idfic = null, $estado = null): array
    {
        try {
            $sql = "SELECT 
                        dj.idjust,
                        dj.idusu,
                        dj.idfic,
                        dj.tipodoc,
                        dj.nomarc,
                        dj.rutarc,
                        dj.fecjust,
                        dj.fecreg,
                        dj.obsjust,
                        dj.estado,
                        dj.obs_instructor,
                        dj.fec_revision,
                        dj.falta_agregada,
                        u.nomusu,
                        u.ndocusu,
                        f.nomfic,
                        COALESCE(rev.nomusu, 'Sin revisar') AS revisor
                    FROM des_docjust AS dj
                    INNER JOIN usuario AS u ON dj.idusu = u.idusu
                    INNER JOIN ficha AS f ON dj.idfic = f.idfic
                    LEFT JOIN usuario AS rev ON dj.idusu_instructor = rev.idusu
                    WHERE 1 = 1";

            $params = [];
            if (!empty($idusu)) {
                $sql .= " AND dj.idusu = :idusu";
                $params[':idusu'] = $idusu;
            }
            if (!empty($idfic)) {
                $sql .= " AND dj.idfic = :idfic";
                $params[':idfic'] = $idfic;
            }
            if (!empty($estado)) {
                $sql .= " AND dj.estado = :estado";
                $params[':estado'] = $estado;
            }

            $sql .= " ORDER BY dj.fecreg DESC;";
            $stmt = $this->conexion->prepare($sql);
            foreach ($params as $key => $val) {
                $stmt->bindValue($key, $val);
            }
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en getJustificaciones: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Guarda un nuevo documento de justificación con validación estricta de tipo
     */
    public function guardarJustificacion($idusu, $idfic, $tipodoc, $nomarc, $rutarc, $fecjust, $obsjust): array
    {
        // Validación obligatoria de lista blanca de tipos válidos
        if (!in_array($tipodoc, self::getTiposValidos(), true)) {
            return [
                'success' => false,
                'message' => 'Tipo de documento no autorizado. Solo se permiten: Cita médica, Incapacidad, Muerte de un familiar de primer grado de consanguinidad.'
            ];
        }

        try {
            date_default_timezone_set('America/Bogota');
            $fecreg = date('Y-m-d H:i:s');

            $sql = "INSERT INTO des_docjust (idusu, idfic, tipodoc, nomarc, rutarc, fecjust, fecreg, obsjust, estado)
                    VALUES (:idusu, :idfic, :tipodoc, :nomarc, :rutarc, :fecjust, :fecreg, :obsjust, 'Pendiente');";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindValue(':idusu', $idusu, PDO::PARAM_INT);
            $stmt->bindValue(':idfic', $idfic);
            $stmt->bindValue(':tipodoc', $tipodoc);
            $stmt->bindValue(':nomarc', $nomarc);
            $stmt->bindValue(':rutarc', $rutarc);
            $stmt->bindValue(':fecjust', $fecjust);
            $stmt->bindValue(':fecreg', $fecreg);
            $stmt->bindValue(':obsjust', substr(trim($obsjust), 0, 1000));
            $stmt->execute();

            return [
                'success' => true,
                'idjust' => $this->conexion->lastInsertId(),
                'message' => 'Documento de justificación subido con éxito y remitido a revisión del instructor.'
            ];
        } catch (PDOException $e) {
            error_log("Error guardando justificación: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error en base de datos al guardar la justificación: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Autoridad del Instructor:
     * Revisa un documento y, si determina que es falso o inválido, añade la falta directamente al acta de deserción
     */
    public function procesarRevisionInstructor(
        $idjust,
        $decision,
        $obsInstructor,
        $idusuInstructor,
        $agregarFalta = false,
        $horasFalta = 0,
        $fechaFalta = null,
        $motivoFalta = ''
    ): array {
        try {
            $this->conexion->beginTransaction();

            // 1. Obtener la justificación actual
            $stmtGet = $this->conexion->prepare("SELECT * FROM des_docjust WHERE idjust = :idjust FOR UPDATE");
            $stmtGet->bindValue(':idjust', $idjust, PDO::PARAM_INT);
            $stmtGet->execute();
            $just = $stmtGet->fetch(PDO::FETCH_ASSOC);

            if (!$just) {
                $this->conexion->rollBack();
                return ['success' => false, 'message' => 'Documento no encontrado.'];
            }

            date_default_timezone_set('America/Bogota');
            $fecRevision = date('Y-m-d H:i:s');
            $nuevoEstado = ($decision === 'aprobar') ? 'Aprobado' : 'Rechazado/Inválido';
            $faltaAplicada = 0;

            // 2. Si es rechazado/inválido y se solicita añadir la falta directamente
            if ($nuevoEstado === 'Rechazado/Inválido' && $agregarFalta) {
                $horas = (int) $horasFalta > 0 ? (int) $horasFalta : 6;
                $fecIna = !empty($fechaFalta) ? $fechaFalta : $just['fecjust'];
                $comentario = "FALTA APLICADA POR INSTRUCTOR - Documento inválido/falso (Soporte #" . $idjust . " - " . $just['tipodoc'] . "). Motivo: " . trim($motivoFalta);

                $resFalta = $this->insInasistenciaDirecta(
                    $just['idusu'],
                    $just['idfic'],
                    $fecIna,
                    $horas,
                    $comentario
                );

                if (!$resFalta['success']) {
                    $this->conexion->rollBack();
                    return ['success' => false, 'message' => 'No se pudo registrar la falta: ' . $resFalta['message']];
                }
                $faltaAplicada = 1;
            }

            // 3. Actualizar estado de la justificación
            $sqlUpd = "UPDATE des_docjust 
                       SET estado = :estado,
                           obs_instructor = :obs,
                           idusu_instructor = :idins,
                           fec_revision = :fecrev,
                           falta_agregada = :falta
                       WHERE idjust = :idjust;";
            $stmtUpd = $this->conexion->prepare($sqlUpd);
            $stmtUpd->bindValue(':estado', $nuevoEstado);
            $stmtUpd->bindValue(':obs', substr(trim($obsInstructor), 0, 1000));
            $stmtUpd->bindValue(':idins', $idusuInstructor, PDO::PARAM_INT);
            $stmtUpd->bindValue(':fecrev', $fecRevision);
            $stmtUpd->bindValue(':falta', $faltaAplicada, PDO::PARAM_INT);
            $stmtUpd->bindValue(':idjust', $idjust, PDO::PARAM_INT);
            $stmtUpd->execute();

            $this->conexion->commit();

            $msg = ($nuevoEstado === 'Aprobado')
                ? 'Documento validado y aprobado exitosamente.'
                : 'Documento calificado como Rechazado/Inválido.';

            if ($faltaAplicada === 1) {
                $msg .= ' Se ha agregado la falta disciplinaria directamente al acta de deserción e historial del aprendiz.';
            }

            return [
                'success' => true,
                'message' => $msg,
                'idusu' => (int) $just['idusu'],
                'idfic' => $just['idfic']
            ];
        } catch (PDOException $e) {
            if ($this->conexion->inTransaction()) {
                $this->conexion->rollBack();
            }
            error_log("Error en procesarRevisionInstructor: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al procesar la revisión: ' . $e->getMessage()];
        }
    }

    /**
     * Añade una falta directamente a la tabla inasistencia con ID autoincremental calculado
     */
    public function insInasistenciaDirecta($idusu, $idfic, $fecina, $horasina, $comina): array
    {
        try {
            date_default_timezone_set('America/Bogota');
            $fecreg = date('Y-m-d H:i:s');

            // Calcular siguiente ID primario seguro
            $stmtId = $this->conexion->query("SELECT COALESCE(MAX(idina), 0) + 1 AS next_id FROM inasistencia");
            $nextId = (int) $stmtId->fetchColumn();

            $sql = "INSERT INTO inasistencia (idina, idusu, fecina, fecreg, horasina, idfic, comina)
                    VALUES (:idina, :idusu, :fecina, :fecreg, :horasina, :idfic, :comina);";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindValue(':idina', $nextId, PDO::PARAM_INT);
            $stmt->bindValue(':idusu', $idusu, PDO::PARAM_INT);
            $stmt->bindValue(':fecina', $fecina);
            $stmt->bindValue(':fecreg', $fecreg);
            $stmt->bindValue(':horasina', (int) $horasina, PDO::PARAM_INT);
            $stmt->bindValue(':idfic', $idfic);
            $stmt->bindValue(':comina', substr(trim($comina), 0, 500));
            $stmt->execute();

            return [
                'success' => true,
                'idina' => $nextId,
                'message' => 'Falta registrada exitosamente en el acta de inasistencias.'
            ];
        } catch (PDOException $e) {
            error_log("Error en insInasistenciaDirecta: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Genera y guarda formalmente el Acta de Deserción
     */
    public function guardarActaOficial($idusu, $idfic, $conact, $coorusu, $idusuInstructor): array
    {
        try {
            $this->conexion->beginTransaction();
            date_default_timezone_set('America/Bogota');
            $fechor = date('Y-m-d H:i:s');

            // 1. Obtener siguiente idact
            $qAct = $this->conexion->query("SELECT COALESCE(MAX(idact), 0) + 1 AS next_act FROM acta");
            $nextAct = (int) $qAct->fetchColumn();

            // 2. Insertar en acta (tipact 201 = Acta Deserción)
            $sqlAct = "INSERT INTO acta (idact, nac, tipact, fecact, conact, idusu, coorusu)
                       VALUES (:idact, 1, 201, :fecact, :conact, :idusu, :coorusu);";
            $stmtAct = $this->conexion->prepare($sqlAct);
            $stmtAct->bindValue(':idact', $nextAct, PDO::PARAM_INT);
            $stmtAct->bindValue(':fecact', $fechor);
            $stmtAct->bindValue(':conact', $conact);
            $stmtAct->bindValue(':idusu', $idusuInstructor, PDO::PARAM_INT);
            $stmtAct->bindValue(':coorusu', $coorusu, PDO::PARAM_INT);
            $stmtAct->execute();

            // 3. Obtener siguiente iddec
            $qDec = $this->conexion->query("SELECT COALESCE(MAX(iddec), 0) + 1 AS next_dec FROM desercion");
            $nextDec = (int) $qDec->fetchColumn();

            // 4. Insertar en desercion
            $sqlDec = "INSERT INTO desercion (iddec, idact, idusu, idfic, fecregdec, obsdec)
                       VALUES (:iddec, :idact, :idusu, :idfic, :fecreg, :obsdec);";
            $stmtDec = $this->conexion->prepare($sqlDec);
            $stmtDec->bindValue(':iddec', $nextDec, PDO::PARAM_INT);
            $stmtDec->bindValue(':idact', $nextAct, PDO::PARAM_INT);
            $stmtDec->bindValue(':idusu', $idusu, PDO::PARAM_INT);
            $stmtDec->bindValue(':idfic', $idfic);
            $stmtDec->bindValue(':fecreg', $fechor);
            $stmtDec->bindValue(':obsdec', 'Acta formal de deserción generada por instructor.');
            $stmtDec->execute();

            // 5. Actualizar estado del aprendiz a Deserción (actusu = 3)
            $sqlUsu = "UPDATE usuario SET actusu = 3 WHERE idusu = :idusu;";
            $stmtUsu = $this->conexion->prepare($sqlUsu);
            $stmtUsu->bindValue(':idusu', $idusu, PDO::PARAM_INT);
            $stmtUsu->execute();

            $this->conexion->commit();

            return [
                'success' => true,
                'idact' => $nextAct,
                'message' => 'Acta de deserción formalizada y guardada exitosamente.'
            ];
        } catch (PDOException $e) {
            if ($this->conexion->inTransaction()) {
                $this->conexion->rollBack();
            }
            error_log("Error en guardarActaOficial: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Verifica si un aprendiz supera el umbral de 30 horas y genera automáticamente el acta de deserción.
     *
     * @param int $idusu Id del aprendiz
     * @param int $idfic Id de la ficha
     * @return array ['generated' => bool, 'idact' => ?int, 'message' => string]
     */
    public function verificarYGenerarActaDesercion(int $idusu, int $idfic): array
    {
        // 1. Calcular total horas de inasistencia del aprendiz en la ficha
        $sqlHoras = "SELECT COALESCE(SUM(horasina),0) AS total_horas
                     FROM inasistencia
                     WHERE idusu = :idusu AND idfic = :idfic";
        $stmtHoras = $this->conexion->prepare($sqlHoras);
        $stmtHoras->bindValue(':idusu', $idusu, PDO::PARAM_INT);
        $stmtHoras->bindValue(':idfic', $idfic);
        $stmtHoras->execute();
        $totalHoras = (int) $stmtHoras->fetchColumn();

        // 2. Si supera el umbral de 30 horas y aún no existe un acta
        if ($totalHoras >= 30) {
            // Verificar si ya existe un acta registrada
            $sqlActExist = "SELECT a.idact
                            FROM desercion d
                            INNER JOIN acta a ON d.idact = a.idact
                            WHERE d.idusu = :idusu AND d.idfic = :idfic
                            LIMIT 1";
            $stmtExist = $this->conexion->prepare($sqlActExist);
            $stmtExist->bindValue(':idusu', $idusu, PDO::PARAM_INT);
            $stmtExist->bindValue(':idfic', $idfic);
            $stmtExist->execute();
            $actaExistente = $stmtExist->fetch(PDO::FETCH_ASSOC);

            if ($actaExistente) {
                return [
                    'generated' => false,
                    'idact' => (int) $actaExistente['idact'],
                    'message' => 'Acta ya existente.'
                ];
            }

            // 3. Generar contenido del acta (simple plantilla)
            $detalle = $this->getDetalleActaEstudiante($idusu, $idfic);
            if (!$detalle || empty($detalle['estudiante'])) {
                return [
                    'generated' => false,
                    'idact' => null,
                    'message' => 'No se encontró la información del aprendiz para generar el acta automáticamente.'
                ];
            }
            $conact = "<h2>Acta de Deserción</h2>";
            $conact .= "<p>Aprendiz: {$detalle['estudiante']['nomusu']} (ID {$idusu})</p>";
            $conact .= "<p>Ficha: {$detalle['estudiante']['nomfic']}</p>";
            $conact .= "<p>Total horas de inasistencia: {$totalHoras}</p>";
            $conact .= "<p>Generada automáticamente al superar el umbral de 30 horas.</p>";

            // 4. Utilizar método existente para guardar el acta
            $resultado = $this->guardarActaOficial(
                $idusu,
                $idfic,
                $conact,
                $detalle['estudiante']['idusu'],
                $detalle['estudiante']['idusu']
            );
            return [
                'generated' => $resultado['success'],
                'idact' => $resultado['success'] ? $resultado['idact'] : null,
                'message' => $resultado['message']
            ];
        }

        return [
            'generated' => false,
            'idact' => null,
            'message' => 'Horas insuficientes para generar acta.'
        ];
    }

    /**
     * Construye la URL pública para visualizar un acta de deserción.
     *
     * @param int $idact Id del acta
     * @return string URL absoluta
     */
    public function getActaUrl(int $idact): string
    {
        return "views/desvadsc.php?idact=" . $idact;
    }

    /**
     * Resuelve el aprendiz y la ficha asociados a un acta de deserción (deep-link por ?idact=)
     */
    public function consultaIdact(int $idact): ?array
    {
        try {
            $sql = "SELECT d.idusu, d.idfic
                    FROM desercion AS d
                    WHERE d.idact = :idact
                    LIMIT 1;";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindValue(':idact', $idact, PDO::PARAM_INT);
            $stmt->execute();
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            return $res ?: null;
        } catch (PDOException $e) {
            error_log("Error en consultaIdact: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Lista de coordinadores para asignación en el acta
     */
    public function getCoordinadores(): array
    {
        try {
            $sql = "SELECT idusu, nomusu FROM usuario WHERE idper IN (7, 21, 24) ORDER BY nomusu ASC";
            $stmt = $this->conexion->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Lista de fichas disponibles para filtros
     */
    public function getFichasDisponibles(): array
    {
        try {
            $sql = "SELECT f.idfic, f.nomfic, COALESCE(v.nomval, '') AS jornada 
                    FROM ficha AS f 
                    LEFT JOIN valor AS v ON f.jornada = v.idval 
                    WHERE f.idfic > 1000 
                    ORDER BY f.idfic ASC;";
            $stmt = $this->conexion->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Estadísticas de deserción por ficha con paginación para DataTables (server-side).
     *
     * @param int    $start    Registro de inicio (OFFSET)
     * @param int    $length   Cantidad de registros (LIMIT)
     * @param string $search   Texto de búsqueda global
     * @param int    $orderCol Índice de columna a ordenar (0-8)
     * @param string $orderDir Dirección del orden (asc|desc)
     */
    public function getFichasDesercionStatsPage(int $start, int $length, string $search = '', int $orderCol = 4, string $orderDir = 'desc'): array
    {
        try {
            $where = "WHERE f.idfic > 1000";
            $params = [];

            // Búsqueda global de DataTables
            if ($search !== '') {
                $where .= " AND (CAST(f.idfic AS CHAR) LIKE :s_idfic
                              OR f.nomfic LIKE :s_nomfic
                              OR COALESCE(v.nomval, '') LIKE :s_jornada
                              OR COALESCE(p.nompro, '') LIKE :s_nompro)";
                $like = '%' . $search . '%';
                $params[':s_idfic'] = $like;
                $params[':s_nomfic'] = $like;
                $params[':s_jornada'] = $like;
                $params[':s_nompro'] = $like;
            }

            // Total de fichas sin filtros
            $stmtT = $this->conexion->query("SELECT COUNT(*) AS n FROM ficha WHERE idfic > 1000");
            $total = (int) $stmtT->fetchColumn();

            // Total filtrado
            $sqlCount = "SELECT COUNT(DISTINCT f.idfic) AS n
                         FROM ficha AS f
                         LEFT JOIN valor AS v ON f.jornada = v.idval
                         LEFT JOIN programa AS p ON f.codpro = p.codpro
                         $where";
            $stmtC = $this->conexion->prepare($sqlCount);
            foreach ($params as $k => $v) {
                $stmtC->bindValue($k, $v);
            }
            $stmtC->execute();
            $filtered = (int) $stmtC->fetchColumn();

            // Orden por columna (solo valores permitidos para evitar inyección)
            $colsOrder = [
                0 => 'f.idfic',
                1 => 'f.nomfic',
                2 => 'COALESCE(v.nomval, \'\')',
                3 => 'total_aprendices',
                4 => 'desertados',
                5 => 'activos',
                6 => 'total_horas_inasistencia',
                7 => 'desertados',
                8 => 'f.idfic',
            ];
            $dir = (strtoupper($orderDir) === 'ASC') ? 'ASC' : 'DESC';
            $orderSql = isset($colsOrder[$orderCol])
                ? ' ORDER BY ' . $colsOrder[$orderCol] . ' ' . $dir . ', f.idfic ASC'
                : ' ORDER BY desertados DESC, total_horas_inasistencia DESC, f.idfic ASC';

            $sql = "SELECT 
                        f.idfic,
                        f.nomfic,
                        COALESCE(v.nomval, 'No asignada') AS jornada,
                        COALESCE(p.nompro, 'Sin programa') AS nompro,
                        COUNT(DISTINCT uf.idusu) AS total_aprendices,
                        COUNT(DISTINCT CASE 
                            WHEN u.actusu = 3 OR u.idusu IN (SELECT idusu FROM desercion) 
                            THEN u.idusu 
                        END) AS desertados,
                        COUNT(DISTINCT CASE WHEN u.actusu = 1 THEN u.idusu END) AS activos,
                        COALESCE((
                            SELECT SUM(i.horasina) 
                            FROM inasistencia i 
                            INNER JOIN usufic ufx ON i.idusu = ufx.idusu 
                            WHERE ufx.idfic = f.idfic
                        ), 0) AS total_horas_inasistencia
                    FROM ficha AS f
                    LEFT JOIN valor AS v ON f.jornada = v.idval
                    LEFT JOIN programa AS p ON f.codpro = p.codpro
                    LEFT JOIN usufic AS uf ON f.idfic = uf.idfic
                    LEFT JOIN usuario AS u ON uf.idusu = u.idusu
                    $where
                    GROUP BY f.idfic, f.nomfic, v.nomval, p.nompro
                    $orderSql
                    LIMIT :lim OFFSET :off";

            $stmt = $this->conexion->prepare($sql);
            foreach ($params as $k => $v) {
                $stmt->bindValue($k, $v);
            }
            $stmt->bindValue(':lim', $length, PDO::PARAM_INT);
            $stmt->bindValue(':off', $start, PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($rows as &$fic) {
                $totalA = (int) $fic['total_aprendices'];
                $des = (int) $fic['desertados'];
                $fic['tasa_desercion'] = ($totalA > 0) ? round(($des / $totalA) * 100, 1) : 0;
            }
            unset($fic);

            return ['total' => $total, 'filtered' => $filtered, 'rows' => $rows];
        } catch (PDOException $e) {
            error_log("Error en getFichasDesercionStatsPage: " . $e->getMessage());
            return ['total' => 0, 'filtered' => 0, 'rows' => []];
        }
    }
}
?>

<?php
// -------------------------------------------------------------
// API JSON para DataTables server-side (consumida por desvadsc.php)
// Uso: models/desmadsc.php?action=fichas_dt&draw=1&start=0&length=10&search[value]=...
// Nota: solo responde cuando desmadsc.php es el script solicitado directamente;
// cuando se incluye desde otros modelos (p. ej. chequeoAutoActa en desmins.php)
// no debe interceptar la petición ajena.
// -------------------------------------------------------------
$esScriptEntrada = (basename(($_SERVER['SCRIPT_NAME'] ?? '')) === 'desmadsc.php');
if ($esScriptEntrada && (isset($_REQUEST['action']) || isset($_GET['action']) || isset($_POST['action']))) {
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-cache, must-revalidate');

    try {
        $modeloApi = new Desmadsc();
        switch (trim((string) $_REQUEST['action'])) {
            case 'fichas_dt':
                $draw = isset($_REQUEST['draw']) ? (int) $_REQUEST['draw'] : 1;
                $start = isset($_REQUEST['start']) ? max(0, (int) $_REQUEST['start']) : 0;
                $length = isset($_REQUEST['length']) ? min(100, max(1, (int) $_REQUEST['length'])) : 10;
                $search = isset($_REQUEST['search']['value']) ? trim((string) $_REQUEST['search']['value']) : '';
                $orderCol = isset($_REQUEST['order'][0]['column']) ? (int) $_REQUEST['order'][0]['column'] : 4;
                $orderDir = isset($_REQUEST['order'][0]['dir']) ? trim((string) $_REQUEST['order'][0]['dir']) : 'desc';

                $datos = $modeloApi->getFichasDesercionStatsPage($start, $length, $search, $orderCol, $orderDir);

                echo json_encode([
                    'draw' => $draw,
                    'recordsTotal' => $datos['total'],
                    'recordsFiltered' => $datos['filtered'],
                    'data' => $datos['rows']
                ], JSON_UNESCAPED_UNICODE);
                break;

            default:
                echo json_encode(['success' => false, 'message' => 'Acción no reconocida'], JSON_UNESCAPED_UNICODE);
        }
    } catch (Throwable $e) {
        error_log("Error en API desmadsc: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
    }
    exit;
}