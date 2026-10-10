<?php

class Mcsi {

    /**
     * Obtiene los formatos oficiales desde la tabla valor (Dominio 30),
     * con fallback automático a los 3 momentos del GFPI-F-023 si no hay registros.
     */
    public function getFormatosEtapaProductiva() {
        try {
            $sql = "SELECT idval, nomval, parval FROM valor WHERE iddom = 30 AND act = 1 ORDER BY idval ASC";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $stmt = $conexion->prepare($sql);
            $stmt->execute();
            $formatos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($formatos)) {
                return $formatos;
            }
        } catch (Exception $e) {
            error_log("Error en getFormatosEtapaProductiva: " . $e->getMessage());
        }

        return [
            [
                'idval' => 301,
                'nomval' => 'Momento 1: GFPI-F-023 Concertación de Actividades (Planeación)',
                'parval' => 'GFPI-F-023V'
            ],
            [
                'idval' => 302,
                'nomval' => 'Momento 2: GFPI-F-023 Evaluación Parcial (Seguimiento)',
                'parval' => 'GFPI-F-023P'
            ],
            [
                'idval' => 303,
                'nomval' => 'Momento 3: GFPI-F-023 Evaluación Final (Juicio Evaluativo)',
                'parval' => 'GFPI-F-023F'
            ]
        ];
    }

    /**
     * Obtiene las fichas asignadas a un instructor según la tabla insseg[cite: 1].
     * Si no tiene fichas asignadas, retorna todas las fichas activas de la institución.
     */
    public function getFichasInstructor($idusu = null) {
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();

        if (!empty($idusu)) {
            try {
                $sql = "
                    SELECT DISTINCT f.idfic, f.nomfic 
                    FROM insseg i
                    INNER JOIN ficha f ON i.idficha = f.idfic
                    WHERE i.idusu = :idusu
                    ORDER BY f.idfic ASC
                ";
                $stmt = $conexion->prepare($sql);
                $stmt->bindParam(':idusu', $idusu, PDO::PARAM_INT);
                $stmt->execute();
                $fichas = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if (!empty($fichas)) {
                    return $fichas;
                }
            } catch (Exception $e) {
                error_log("Error en getFichasInstructor por usuario: " . $e->getMessage());
            }
        }

        try {
            $sqlAll = "SELECT idfic, nomfic FROM ficha ORDER BY idfic ASC";
            $stmtAll = $conexion->prepare($sqlAll);
            $stmtAll->execute();
            return $stmtAll->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en getFichasInstructor fallback: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Resumen Global del Instructor para el Dashboard inicial.
     */
    public function getResumenGlobalInstructor($idusu = null) {
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();

        $fichas = $this->getFichasInstructor($idusu);
        $totalFichas = count($fichas);
        $listaFichasIds = array_column($fichas, 'idfic');

        if (empty($listaFichasIds)) {
            return [
                'total_fichas'      => 0,
                'total_aprendices'  => 0,
                'total_concertados' => 0,
                'total_pendientes'  => 0,
                'total_sin_firmas'  => 0
            ];
        }

        $inClause = implode(',', array_map(function($f) { return "'" . addslashes($f) . "'"; }, $listaFichasIds));

        try {
            $sqlAp = "SELECT COUNT(DISTINCT idusu) AS total FROM usufic WHERE idfic IN ($inClause)";
            $resAp = $conexion->query($sqlAp)->fetch(PDO::FETCH_ASSOC);
            $totalAprendices = (int)($resAp['total'] ?? 0);

            $sqlCon = "
                SELECT 
                    COUNT(idcon) AS concertados,
                    SUM(CASE WHEN (COALESCE(firma_aprendiz, 'Pendiente') = 'Pendiente' OR COALESCE(firma_empresa, 'Pendiente') = 'Pendiente') THEN 1 ELSE 0 END) AS sin_firmas
                FROM consertamiento
                WHERE idficha IN ($inClause)
            ";
            $resCon = $conexion->query($sqlCon)->fetch(PDO::FETCH_ASSOC);
            $totalConcertados = (int)($resCon['concertados'] ?? 0);
            $totalSinFirmas = (int)($resCon['sin_firmas'] ?? 0);
        } catch (Exception $e) {
            error_log("Error en getResumenGlobalInstructor: " . $e->getMessage());
            $totalAprendices = 0;
            $totalConcertados = 0;
            $totalSinFirmas = 0;
        }

        $totalPendientes = max(0, $totalAprendices - $totalConcertados);

        return [
            'total_fichas'      => $totalFichas,
            'total_aprendices'  => $totalAprendices,
            'total_concertados' => $totalConcertados,
            'total_pendientes'  => $totalPendientes,
            'total_sin_firmas'  => $totalSinFirmas
        ];
    }

    /**
     * Resumen por Ficha para el Dashboard filtrado.
     */
    public function getResumenFichaConcertacion($idficha, $tipoFormato = 301) {
        $aprendices = $this->getAprendicesConcertacionFicha($idficha, $tipoFormato);

        $total = count($aprendices);
        $concertados = 0;
        $pendientesDiligenciar = 0;
        $pendientesFirmas = 0;

        foreach ($aprendices as $ap) {
            if (!empty($ap['idcon'])) {
                $concertados++;
                $fAp = $ap['firma_aprendiz'] ?? 'Pendiente';
                $fEm = $ap['firma_empresa'] ?? 'Pendiente';
                if ($fAp !== 'Firmado' || $fEm !== 'Firmado') {
                    $pendientesFirmas++;
                }
            } else {
                $pendientesDiligenciar++;
            }
        }

        return [
            'total'                  => $total,
            'concertados'            => $concertados,
            'pendientes_diligenciar' => $pendientesDiligenciar,
            'pendientes_firmas'      => $pendientesFirmas
        ];
    }

    /**
     * Listado completo de aprendices de una ficha con el estado de su concertación (consertamiento).
     */
    public function getAprendicesConcertacionFicha($idficha, $tipoFormato = 301) {
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();

        $sql = "
            SELECT 
                u.idusu AS idaprendiz,
                u.ndocusu AS doc_aprendiz,
                u.nomusu AS nom_aprendiz,
                u.emausu AS correo_aprendiz,
                u.telcan AS tel_aprendiz,
                uf.idfic AS idficha,
                f.nomfic AS nom_programa,
                c.idcon,
                c.idinstructor,
                c.tipo_formato,
                c.nombre_empresa,
                c.nit_empresa,
                c.direccion_empresa,
                c.nombre_jefe,
                c.cargo_jefe,
                c.correo_jefe,
                c.telefono_jefe,
                c.nombre_arl,
                c.nivel_riesgo_arl,
                c.modalidad,
                c.enlace_grabacion,
                c.fecha_inicio,
                c.fecha_fin,
                c.fecha_momento2_real,
                c.num_visitas,
                c.fecha_momento3_real,
                c.juicio_evaluativo,
                c.competencias,
                c.raps,
                c.actividades_lista,
                c.tareas_acordadas,
                c.fechas_entrega,
                c.obs_momento1,
                c.obs_momento2,
                c.obs_momento3,
                c.estado,
                COALESCE(c.firma_aprendiz, 'Pendiente') AS firma_aprendiz,
                COALESCE(c.firma_empresa, 'Pendiente') AS firma_empresa,
                COALESCE(c.firma_instructor, 'Firmado') AS firma_instructor,
                u_ins.nomusu AS nom_instructor,
                (SELECT COUNT(*) FROM consertamiento_detalle cd WHERE cd.idcon = c.idcon) AS total_evaluaciones
            FROM usufic uf
            INNER JOIN usuario u ON uf.idusu = u.idusu
            INNER JOIN ficha f ON uf.idfic = f.idfic
            LEFT JOIN consertamiento c ON (
                c.idaprendiz = u.idusu 
                AND c.idficha = uf.idfic
            )
            LEFT JOIN usuario u_ins ON c.idinstructor = u_ins.idusu
            WHERE uf.idfic = :idficha
            ORDER BY u.nomusu ASC
        ";

        try {
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(':idficha', $idficha);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en getAprendicesConcertacionFicha: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene un registro individual de consertamiento.
     */
    public function getOneConsentimiento($idcon) {
        try {
            $sql = "
                SELECT 
                    c.*,
                    u_ap.ndocusu AS doc_aprendiz,
                    u_ap.nomusu AS nom_aprendiz,
                    u_ap.emausu AS correo_aprendiz,
                    u_ap.telcan AS tel_aprendiz,
                    u_ins.nomusu AS nom_instructor,
                    u_ins.ndocusu AS doc_instructor,
                    f.nomfic AS nom_programa
                FROM consertamiento c
                INNER JOIN usuario u_ap ON c.idaprendiz = u_ap.idusu
                LEFT JOIN usuario u_ins ON c.idinstructor = u_ins.idusu
                LEFT JOIN ficha f ON c.idficha = f.idfic
                WHERE c.idcon = :idcon
            ";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(':idcon', $idcon, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en getOneConsentimiento: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtiene los datos del aprendiz por su ID de usuario.
     */
    public function getAprendizById($idaprendiz) {
        try {
            $sql = "
                SELECT u.idusu, u.ndocusu, u.nomusu, u.emausu, u.telcan, uf.idfic, f.nomfic AS nom_programa
                FROM usuario u
                LEFT JOIN usufic uf ON u.idusu = uf.idusu
                LEFT JOIN ficha f ON uf.idfic = f.idfic
                WHERE u.idusu = :idaprendiz
                LIMIT 1
            ";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(':idaprendiz', $idaprendiz, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en getAprendizById: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtiene el consertamiento existente de un aprendiz en una ficha específica.
     */
    public function getConsentimientoByAprendizFicha($idaprendiz, $idficha) {
        try {
            $sql = "SELECT idcon FROM consertamiento WHERE idaprendiz = :idaprendiz AND idficha = :idficha ORDER BY idcon DESC LIMIT 1";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(':idaprendiz', $idaprendiz, PDO::PARAM_INT);
            $stmt->bindParam(':idficha', $idficha);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!empty($row['idcon'])) {
                return $this->getOneConsentimiento($row['idcon']);
            }
            return null;
        } catch (Exception $e) {
            error_log("Error en getConsentimientoByAprendizFicha: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtiene el consentimiento registrado o genera una plantilla prellenada con los datos del aprendiz.
     */
    public function getOneConsentimientoOrAprendiz($idcon = null, $idaprendiz = null, $idficha = null) {
        if (!empty($idcon)) {
            $datos = $this->getOneConsentimiento($idcon);
            if (!empty($datos)) {
                return $datos;
            }
        }

        if (!empty($idaprendiz)) {
            $ap = $this->getAprendizById($idaprendiz);
            if ($ap) {
                return [
                    'idcon'             => null,
                    'idaprendiz'        => $ap['idusu'],
                    'idinstructor'      => null,
                    'idficha'           => $idficha ?: $ap['idfic'],
                    'tipo_formato'      => 301,
                    'nombre_empresa'    => 'Por definir en concertación',
                    'nit_empresa'       => 'Pendiente',
                    'direccion_empresa' => 'Pendiente',
                    'nombre_jefe'       => 'Pendiente por asignar',
                    'cargo_jefe'        => 'Tutor Empresarial',
                    'correo_jefe'       => 'pendiente@empresa.com',
                    'telefono_jefe'     => 'Pendiente',
                    'nombre_arl'        => 'Pendiente de afiliación',
                    'nivel_riesgo_arl'  => 'I',
                    'modalidad'         => 'Presencial',
                    'fecha_inicio'      => date('Y-m-d'),
                    'fecha_fin'         => date('Y-m-d', strtotime('+6 months')),
                    'competencias'      => "220501096 - Desarrollar la solución de software de acuerdo con el diseño y requerimientos.\n220501097 - Implantar la solución de software según la arquitectura y requerimientos del cliente.",
                    'raps'              => "1RAP: Construir componentes de software según especificaciones de diseño.\n2RAP: Ejecutar pruebas unitarias e integrales del sistema.",
                    'actividades_lista' => "1. Diseñar y codificar módulos web bajo estándares de calidad SENA.\n2. Elaborar documentación técnica y manuales de usuario.",
                    'tareas_acordadas'  => "*El aprendiz debe presentar una bitácora mensual como lo establece el reglamento del aprendiz y la Guía GFPI-G-040.",
                    'fechas_entrega'    => "1. Bitácora mensual de actividades de desarrollo.\n2. Informe técnico de avances de software.\n3. Repositorio de código fuente con documentación.",
                    'estado'            => 'Borrador',
                    'firma_aprendiz'    => 'Pendiente',
                    'firma_empresa'     => 'Pendiente',
                    'firma_instructor'  => 'Pendiente',
                    'doc_aprendiz'      => $ap['ndocusu'],
                    'nom_aprendiz'      => $ap['nomusu'],
                    'correo_aprendiz'   => $ap['emausu'],
                    'tel_aprendiz'      => $ap['telcan'],
                    'nom_instructor'    => 'Instructor de Seguimiento SENA',
                    'doc_instructor'    => 'SENA',
                    'nom_programa'      => $ap['nom_programa'] ?? 'Programa de Formación SENA'
                ];
            }
        }

        return null;
    }

    public function getInstructores() {
        try {
            $sql = "SELECT idusu, ndocusu, nomusu, emausu, telcan FROM usuario WHERE idper = 32 ORDER BY nomusu ASC";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $stmt = $conexion->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en getInstructores: " . $e->getMessage());
            return [];
        }
    }

    public function getFichas() {
        try {
            $sql = "SELECT idfic, nomfic FROM ficha ORDER BY idfic ASC";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $stmt = $conexion->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en getFichas: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Guarda un nuevo registro de concertación en la tabla consertamiento.
     */
    public function save($datos) {
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();

        try {
            $sql = "
                INSERT INTO consertamiento (
                    idaprendiz, idinstructor, idficha, tipo_formato, nombre_empresa, nit_empresa,
                    direccion_empresa, nombre_jefe, cargo_jefe, correo_jefe, telefono_jefe,
                    nombre_arl, nivel_riesgo_arl, modalidad, fecha_inicio, fecha_fin,
                    competencias, raps, actividades_lista, tareas_acordadas, fechas_entrega, 
                    obs_momento1, estado, firma_aprendiz, firma_empresa, firma_instructor
                ) VALUES (
                    :idaprendiz, :idinstructor, :idficha, :tipo_formato, :nombre_empresa, :nit_empresa,
                    :direccion_empresa, :nombre_jefe, :cargo_jefe, :correo_jefe, :telefono_jefe,
                    :nombre_arl, :nivel_riesgo_arl, :modalidad, :fecha_inicio, :fecha_fin,
                    :competencias, :raps, :actividades_lista, :tareas_acordadas, :fechas_entrega, 
                    :obs_momento1, :estado, :firma_aprendiz, :firma_empresa, :firma_instructor
                )
            ";
            $stmt = $conexion->prepare($sql);
            $tipoFormato = $datos['tipo_formato'] ?? 301;
            $fAp = $datos['firma_aprendiz'] ?? 'Pendiente';
            $fEm = $datos['firma_empresa'] ?? 'Pendiente';
            $fIn = $datos['firma_instructor'] ?? 'Firmado';
            $obsM1 = $datos['obs_momento1'] ?? null;

            $stmt->bindParam(':idaprendiz', $datos['idaprendiz']);
            $stmt->bindParam(':idinstructor', $datos['idinstructor']);
            $stmt->bindParam(':idficha', $datos['idficha']);
            $stmt->bindParam(':tipo_formato', $tipoFormato);
            $stmt->bindParam(':nombre_empresa', $datos['nombre_empresa']);
            $stmt->bindParam(':nit_empresa', $datos['nit_empresa']);
            $stmt->bindParam(':direccion_empresa', $datos['direccion_empresa']);
            $stmt->bindParam(':nombre_jefe', $datos['nombre_jefe']);
            $stmt->bindParam(':cargo_jefe', $datos['cargo_jefe']);
            $stmt->bindParam(':correo_jefe', $datos['correo_jefe']);
            $stmt->bindParam(':telefono_jefe', $datos['telefono_jefe']);
            $stmt->bindParam(':nombre_arl', $datos['nombre_arl']);
            $stmt->bindParam(':nivel_riesgo_arl', $datos['nivel_riesgo_arl']);
            $stmt->bindParam(':modalidad', $datos['modalidad']);
            $stmt->bindParam(':fecha_inicio', $datos['fecha_inicio']);
            $stmt->bindParam(':fecha_fin', $datos['fecha_fin']);
            $stmt->bindParam(':competencias', $datos['competencias']);
            $stmt->bindParam(':raps', $datos['raps']);
            $stmt->bindParam(':actividades_lista', $datos['actividades_lista']);
            $stmt->bindParam(':tareas_acordadas', $datos['tareas_acordadas']);
            $stmt->bindParam(':fechas_entrega', $datos['fechas_entrega']);
            $stmt->bindParam(':obs_momento1', $obsM1);
            $stmt->bindParam(':estado', $datos['estado']);
            $stmt->bindParam(':firma_aprendiz', $fAp);
            $stmt->bindParam(':firma_empresa', $fEm);
            $stmt->bindParam(':firma_instructor', $fIn);

            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error en save: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualiza un registro existente de concertación en la tabla consertamiento.
     */
    public function edit($idcon, $datos) {
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();

        try {
            $sql = "
                UPDATE consertamiento SET
                    idaprendiz = :idaprendiz,
                    idinstructor = :idinstructor,
                    idficha = :idficha,
                    tipo_formato = :tipo_formato,
                    nombre_empresa = :nombre_empresa,
                    nit_empresa = :nit_empresa,
                    direccion_empresa = :direccion_empresa,
                    nombre_jefe = :nombre_jefe,
                    cargo_jefe = :cargo_jefe,
                    correo_jefe = :correo_jefe,
                    telefono_jefe = :telefono_jefe,
                    nombre_arl = :nombre_arl,
                    nivel_riesgo_arl = :nivel_riesgo_arl,
                    modalidad = :modalidad,
                    fecha_inicio = :fecha_inicio,
                    fecha_fin = :fecha_fin,
                    competencias = :competencias,
                    raps = :raps,
                    actividades_lista = :actividades_lista,
                    tareas_acordadas = :tareas_acordadas,
                    fechas_entrega = :fechas_entrega,
                    obs_momento1 = :obs_momento1,
                    estado = :estado,
                    firma_aprendiz = :firma_aprendiz,
                    firma_empresa = :firma_empresa,
                    firma_instructor = :firma_instructor
                WHERE idcon = :idcon
            ";
            $stmt = $conexion->prepare($sql);
            $tipoFormato = $datos['tipo_formato'] ?? 301;
            $fAp = $datos['firma_aprendiz'] ?? 'Pendiente';
            $fEm = $datos['firma_empresa'] ?? 'Pendiente';
            $fIn = $datos['firma_instructor'] ?? 'Firmado';
            $obsM1 = $datos['obs_momento1'] ?? null;

            $stmt->bindParam(':idcon', $idcon, PDO::PARAM_INT);
            $stmt->bindParam(':idaprendiz', $datos['idaprendiz']);
            $stmt->bindParam(':idinstructor', $datos['idinstructor']);
            $stmt->bindParam(':idficha', $datos['idficha']);
            $stmt->bindParam(':tipo_formato', $tipoFormato);
            $stmt->bindParam(':nombre_empresa', $datos['nombre_empresa']);
            $stmt->bindParam(':nit_empresa', $datos['nit_empresa']);
            $stmt->bindParam(':direccion_empresa', $datos['direccion_empresa']);
            $stmt->bindParam(':nombre_jefe', $datos['nombre_jefe']);
            $stmt->bindParam(':cargo_jefe', $datos['cargo_jefe']);
            $stmt->bindParam(':correo_jefe', $datos['correo_jefe']);
            $stmt->bindParam(':telefono_jefe', $datos['telefono_jefe']);
            $stmt->bindParam(':nombre_arl', $datos['nombre_arl']);
            $stmt->bindParam(':nivel_riesgo_arl', $datos['nivel_riesgo_arl']);
            $stmt->bindParam(':modalidad', $datos['modalidad']);
            $stmt->bindParam(':fecha_inicio', $datos['fecha_inicio']);
            $stmt->bindParam(':fecha_fin', $datos['fecha_fin']);
            $stmt->bindParam(':competencias', $datos['competencias']);
            $stmt->bindParam(':raps', $datos['raps']);
            $stmt->bindParam(':actividades_lista', $datos['actividades_lista']);
            $stmt->bindParam(':tareas_acordadas', $datos['tareas_acordadas']);
            $stmt->bindParam(':fechas_entrega', $datos['fechas_entrega']);
            $stmt->bindParam(':obs_momento1', $obsM1);
            $stmt->bindParam(':estado', $datos['estado']);
            $stmt->bindParam(':firma_aprendiz', $fAp);
            $stmt->bindParam(':firma_empresa', $fEm);
            $stmt->bindParam(':firma_instructor', $fIn);

            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error en edit: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Alterna el estado de firma entre 'Firmado' y 'Pendiente' en la tabla consertamiento.
     */
    public function alternarFirma($idcon, $tipoFirma) {
        $columnasValidas = [
            'aprendiz'   => 'firma_aprendiz',
            'empresa'    => 'firma_empresa',
            'instructor' => 'firma_instructor'
        ];

        if (!isset($columnasValidas[$tipoFirma])) {
            return false;
        }

        $col = $columnasValidas[$tipoFirma];
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();

        try {
            $sql = "UPDATE consertamiento SET $col = CASE WHEN $col = 'Firmado' THEN 'Pendiente' ELSE 'Firmado' END WHERE idcon = :idcon";
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(':idcon', $idcon, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error en alternarFirma: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Elimina un registro de concertación por su ID.
     */
    public function delete($idcon) {
        try {
            $sql = "DELETE FROM consertamiento WHERE idcon = :idcon";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(':idcon', $idcon, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error en delete: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene el catálogo de variables de evaluación (Dominio 31: Técnicos, Dominio 32: Comportamentales).
     */
    public function getVariablesEvaluacion() {
        try {
            $sql = "
                SELECT v.idval, v.nomval, v.iddom, v.parval, d.nomdom
                FROM valor v
                INNER JOIN dominio d ON v.iddom = d.iddom
                WHERE v.iddom IN (31, 32) AND v.act = 1
                ORDER BY v.iddom ASC, v.idval ASC
            ";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $stmt = $conexion->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en getVariablesEvaluacion: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene los detalles de evaluación guardados para un consertamiento filtrado por momento.
     * $momento: 2 = Seguimiento Parcial (M2), 3 = Evaluación Final (M3), null = todos.
     * Retorna un array asociativo indexado por idval_variable.
     */
    public function getDetallesConsertamiento($idcon, $momento = null) {
        if (empty($idcon)) return [];
        try {
            $whereMomento = !is_null($momento) ? 'AND cd.momento = :momento' : '';
            $sql = "
                SELECT cd.iddetalle, cd.idcon, cd.idval_variable, cd.valoracion, cd.observacion, cd.momento,
                       v.nomval, v.iddom, v.parval
                FROM consertamiento_detalle cd
                INNER JOIN valor v ON cd.idval_variable = v.idval
                WHERE cd.idcon = :idcon $whereMomento
                ORDER BY v.iddom ASC, cd.idval_variable ASC
            ";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(':idcon', $idcon, PDO::PARAM_INT);
            if (!is_null($momento)) {
                $stmt->bindParam(':momento', $momento, PDO::PARAM_INT);
            }
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $detalles = [];
            foreach ($rows as $row) {
                $detalles[$row['idval_variable']] = $row;
            }
            return $detalles;
        } catch (Exception $e) {
            error_log("Error en getDetallesConsertamiento: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Guarda o actualiza el Seguimiento Parcial Momento 2 (GFPI-F-023).
     * Actualiza cabecera consertamiento y registros en consertamiento_detalle.
     */
    public function guardarSeguimientoM2($idcon, $datosM2, $variables = []) {
        if (empty($idcon)) return false;
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();

        try {
            $conexion->beginTransaction();

            // 1. Actualizar campos específicos del Momento 2 en consertamiento
            $sqlHeader = "
                UPDATE consertamiento SET
                    fecha_momento2_real = :fecha_momento2_real,
                    modalidad = :modalidad,
                    enlace_grabacion = :enlace_grabacion,
                    obs_momento2 = :obs_momento2
                WHERE idcon = :idcon
            ";
            $stmtH = $conexion->prepare($sqlHeader);
            $fReal = !empty($datosM2['fecha_momento2_real']) ? $datosM2['fecha_momento2_real'] : date('Y-m-d');
            $mod = !empty($datosM2['modalidad']) ? $datosM2['modalidad'] : 'Presencial';
            $grab = ($mod === 'Virtual' || $mod === 'Híbrida') ? (!empty($datosM2['enlace_grabacion']) ? trim($datosM2['enlace_grabacion']) : null) : null;
            $obsM2 = !empty($datosM2['obs_momento2']) ? trim($datosM2['obs_momento2']) : null;

            $stmtH->bindParam(':idcon', $idcon, PDO::PARAM_INT);
            $stmtH->bindParam(':fecha_momento2_real', $fReal);
            $stmtH->bindParam(':modalidad', $mod);
            $stmtH->bindParam(':enlace_grabacion', $grab);
            $stmtH->bindParam(':obs_momento2', $obsM2);
            $stmtH->execute();

            // 2. Guardar o actualizar cada variable del Momento 2 (momento=2)
            if (!empty($variables) && is_array($variables)) {
                $momentoVal = 2;
                $sqlUpsert = "
                    INSERT INTO consertamiento_detalle (idcon, idval_variable, valoracion, observacion, momento)
                    VALUES (:idcon, :idval_variable, :valoracion, :observacion, :momento)
                    ON DUPLICATE KEY UPDATE
                        valoracion = VALUES(valoracion),
                        observacion = VALUES(observacion)
                ";
                $stmtUpsert = $conexion->prepare($sqlUpsert);

                foreach ($variables as $idval => $valData) {
                    $valoracion = $valData['valoracion'] ?? 'Satisfactorio';
                    $observacion = ($valoracion === 'Por mejorar') ? trim($valData['observacion'] ?? '') : null;
                    $idvalInt = (int)$idval;

                    $stmtUpsert->bindParam(':idcon', $idcon, PDO::PARAM_INT);
                    $stmtUpsert->bindParam(':idval_variable', $idvalInt, PDO::PARAM_INT);
                    $stmtUpsert->bindParam(':valoracion', $valoracion);
                    $stmtUpsert->bindParam(':observacion', $observacion);
                    $stmtUpsert->bindParam(':momento', $momentoVal, PDO::PARAM_INT);
                    $stmtUpsert->execute();
                }
            }

            $conexion->commit();
            return true;
        } catch (Exception $e) {
            if ($conexion->inTransaction()) {
                $conexion->rollBack();
            }
            error_log("Error en guardarSeguimientoM2: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Guarda o actualiza la Evaluación Final Momento 3 (GFPI-F-023).
     * Incluye número de visitas, fecha_fin, fecha_momento3_real, modalidad,
     * retroalimentación de los 3 actores en obs_momento3 y juicio evaluativo (APROBADO/NO APROBADO).
     */
    public function guardarEvaluacionFinalM3($idcon, $datosM3, $variables = []) {
        if (empty($idcon)) return false;
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();

        try {
            $conexion->beginTransaction();

            // 1. Actualizar campos específicos del Momento 3 en consertamiento
            $sqlHeader = "
                UPDATE consertamiento SET
                    num_visitas = :num_visitas,
                    fecha_fin = :fecha_fin,
                    fecha_momento3_real = :fecha_momento3_real,
                    modalidad = :modalidad,
                    enlace_grabacion = :enlace_grabacion,
                    obs_momento3 = :obs_momento3,
                    juicio_evaluativo = :juicio_evaluativo,
                    estado = :estado
                WHERE idcon = :idcon
            ";
            $stmtH = $conexion->prepare($sqlHeader);
            $numVis = !empty($datosM3['num_visitas']) ? (int)$datosM3['num_visitas'] : 3;
            $fFin = !empty($datosM3['fecha_fin']) ? $datosM3['fecha_fin'] : date('Y-m-d');
            $fReal = !empty($datosM3['fecha_momento3_real']) ? $datosM3['fecha_momento3_real'] : date('Y-m-d');
            $mod = !empty($datosM3['modalidad']) ? $datosM3['modalidad'] : 'Presencial';
            $grab = ($mod === 'Virtual' || $mod === 'Híbrida') ? (!empty($datosM3['enlace_grabacion']) ? trim($datosM3['enlace_grabacion']) : null) : null;
            $obsM3 = !empty($datosM3['obs_momento3']) ? $datosM3['obs_momento3'] : null;
            $juicio = (!empty($datosM3['juicio_evaluativo']) && $datosM3['juicio_evaluativo'] === 'NO APROBADO') ? 'NO APROBADO' : 'APROBADO';
            $estado = ($juicio === 'APROBADO') ? 'Aprobado' : 'Rechazado';

            $stmtH->bindParam(':idcon', $idcon, PDO::PARAM_INT);
            $stmtH->bindParam(':num_visitas', $numVis, PDO::PARAM_INT);
            $stmtH->bindParam(':fecha_fin', $fFin);
            $stmtH->bindParam(':fecha_momento3_real', $fReal);
            $stmtH->bindParam(':modalidad', $mod);
            $stmtH->bindParam(':enlace_grabacion', $grab);
            $stmtH->bindParam(':obs_momento3', $obsM3);
            $stmtH->bindParam(':juicio_evaluativo', $juicio);
            $stmtH->bindParam(':estado', $estado);
            $stmtH->execute();

            // 2. Guardar o actualizar cada variable del Momento 3 (momento=3, independiente de M2)
            if (!empty($variables) && is_array($variables)) {
                $momentoVal = 3;
                $sqlUpsert = "
                    INSERT INTO consertamiento_detalle (idcon, idval_variable, valoracion, observacion, momento)
                    VALUES (:idcon, :idval_variable, :valoracion, :observacion, :momento)
                    ON DUPLICATE KEY UPDATE
                        valoracion = VALUES(valoracion),
                        observacion = VALUES(observacion)
                ";
                $stmtUpsert = $conexion->prepare($sqlUpsert);

                foreach ($variables as $idval => $valData) {
                    $valoracion = $valData['valoracion'] ?? 'Satisfactorio';
                    $observacion = ($valoracion === 'Por mejorar') ? trim($valData['observacion'] ?? '') : null;
                    $idvalInt = (int)$idval;

                    $stmtUpsert->bindParam(':idcon', $idcon, PDO::PARAM_INT);
                    $stmtUpsert->bindParam(':idval_variable', $idvalInt, PDO::PARAM_INT);
                    $stmtUpsert->bindParam(':valoracion', $valoracion);
                    $stmtUpsert->bindParam(':observacion', $observacion);
                    $stmtUpsert->bindParam(':momento', $momentoVal, PDO::PARAM_INT);
                    $stmtUpsert->execute();
                }
            }

            $conexion->commit();
            return true;
        } catch (Exception $e) {
            if ($conexion->inTransaction()) {
                $conexion->rollBack();
            }
            error_log("Error en guardarEvaluacionFinalM3: " . $e->getMessage());
            return false;
        }
    }
}
?>