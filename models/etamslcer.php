<?php

class Etamslcer {
    private $idcertificacion;
    private $idaprendiz;
    private $nombre_certificacion;
    private $descripcion;
    private $fecha_obtencion;
    private $fecha_vencimiento;
    private $entidad_emisora;
    private $numero_certificacion;
    private $imagen_certificacion;
    private $archivo_certificacion;
    private $estado; 
    private $tipo_certificacion; 

    // Getters
    public function getIdcertificacion() {
        return $this->idcertificacion;
    }

    public function getIdaprendiz() {
        return $this->idaprendiz;
    }

    public function getNombreCertificacion() {
        return $this->nombre_certificacion;
    }

    public function getDescripcion() {
        return $this->descripcion;
    }

    public function getFechaObtencion() {
        return $this->fecha_obtencion;
    }

    public function getFechaVencimiento() {
        return $this->fecha_vencimiento;
    }

    public function getEntidadEmisora() {
        return $this->entidad_emisora;
    }

    public function getNumeroCertificacion() {
        return $this->numero_certificacion;
    }

    public function getImagenCertificacion() {
        return $this->imagen_certificacion;
    }

    public function getArchivoCertificacion() {
        return $this->archivo_certificacion;
    }

    public function getEstado() {
        return $this->estado;
    }

    public function getTipoCertificacion() {
        return $this->tipo_certificacion;
    }

    // Setters
    public function setIdcertificacion($idcertificacion) {
        $this->idcertificacion = $idcertificacion;
    }

    public function setIdaprendiz($idaprendiz) {
        $this->idaprendiz = $idaprendiz;
    }

    public function setNombreCertificacion($nombre_certificacion) {
        $this->nombre_certificacion = $nombre_certificacion;
    }

    public function setDescripcion($descripcion) {
        $this->descripcion = $descripcion;
    }

    public function setFechaObtencion($fecha_obtencion) {
        $this->fecha_obtencion = $fecha_obtencion;
    }

    public function setFechaVencimiento($fecha_vencimiento) {
        $this->fecha_vencimiento = $fecha_vencimiento;
    }

    public function setEntidadEmisora($entidad_emisora) {
        $this->entidad_emisora = $entidad_emisora;
    }

    public function setNumeroCertificacion($numero_certificacion) {
        $this->numero_certificacion = $numero_certificacion;
    }

    public function setImagenCertificacion($imagen_certificacion) {
        $this->imagen_certificacion = $imagen_certificacion;
    }

    public function setArchivoCertificacion($archivo_certificacion) {
        $this->archivo_certificacion = $archivo_certificacion;
    }

    public function setEstado($estado) {
        $this->estado = $estado;
    }

    public function setTipoCertificacion($tipo_certificacion) {
        $this->tipo_certificacion = $tipo_certificacion;
    }

     //Obtener TODOS los programas activos del estudiante - VERSIÓN FINAL
    public function getProgramasActivosEstudiante($idusu, $ndocusu = null) {
        try {
            if (!$ndocusu) {
                $ndocusu = $idusu;
            }
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            
            // Consulta principal sin debug
            $sql = "SELECT 
                        uf.idfic,
                        uf.idusu,
                        uf.actfic as activo,
                        f.nomfic as nombre_programa,
                        f.codpro,
                        f.finific as fecha_inicio,
                        f.ffinfic as fecha_fin_estimada,
                        f.mun as municipio,
                        f.jornada,
                        p.nompro as nombre_programa_completo,
                        p.tippro as tipo_programa_codigo,
                        p.horlpro as horas_lectivas,
                        p.horppro as horas_practicas,
                        -- Calcular días y meses cursados
                        CASE 
                            WHEN f.finific IS NOT NULL THEN 
                                TIMESTAMPDIFF(DAY, f.finific, NOW())
                            ELSE 0
                        END as dias_cursados,
                        CASE 
                            WHEN f.finific IS NOT NULL THEN 
                                TIMESTAMPDIFF(MONTH, f.finific, NOW())
                            ELSE 0
                        END as meses_cursados,
                        -- Estado del programa
                        CASE 
                            WHEN f.ffinfic IS NOT NULL AND f.ffinfic < NOW() THEN 'terminado'
                            WHEN uf.actfic = 1 THEN 'activo'
                            ELSE 'inactivo'
                        END as estado_programa
                    FROM usufic uf
                    INNER JOIN ficha f ON uf.idfic = f.idfic
                    INNER JOIN programa p ON f.codpro = p.codpro
                    WHERE uf.idusu = :idusu OR uf.idusu = :ndocusu
                    ORDER BY uf.actfic DESC, f.finific DESC";
            
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(':idusu', $idusu);
            $stmt->bindParam(':ndocusu', $ndocusu);
            $stmt->execute();
            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Procesar resultados
            $programasProcesados = [];
            foreach ($resultados as $programa) {
                
                // Determinar tipo de programa (corregir la detección)
                $tipoPrograma = $this->determinarTipoProgramaCorregido($programa['tipo_programa_codigo'] ?? 1, $programa['nombre_programa']);
                
                // Calcular duración estimada basada en horas
                $horasTotal = ($programa['horas_lectivas'] ?? 0) + ($programa['horas_practicas'] ?? 0);
                $duracionMesesEstimada = $this->calcularDuracionMeses($horasTotal, $tipoPrograma);
                $duracionTrimestreEstimada = $this->calcularDuracionTrimestres($duracionMesesEstimada);
                
                // Calcular progreso
                $porcentajeAvance = 0;
                $trimestreActual = 0;
                
                if ($programa['fecha_inicio'] && $duracionMesesEstimada > 0) {
                    $mesesCursados = $programa['meses_cursados'];
                    $porcentajeAvance = min(100, round(($mesesCursados * 100.0) / $duracionMesesEstimada, 1));
                    
                    if ($duracionTrimestreEstimada > 0) {
                        $trimestreActual = min($duracionTrimestreEstimada, floor($mesesCursados / ($duracionMesesEstimada / $duracionTrimestreEstimada)) + 1);
                    }
                }
                
                // Obtener bitácoras con campos correctos
                $totalBitacoras = 0;
                $bitacorasCompletas = 0;
                
                try {
                    // Contar bitácoras totales del aprendiz
                    $sqlBitacorasTotal = "SELECT COUNT(*) as total FROM bitacora WHERE idaprendiz = :idaprendiz OR idaprendiz = :ndocusu";
                    $stmtBitTotal = $conexion->prepare($sqlBitacorasTotal);
                    $stmtBitTotal->bindParam(':idaprendiz', $idusu);
                    $stmtBitTotal->bindParam(':ndocusu', $ndocusu);
                    $stmtBitTotal->execute();
                    $bitacoraTotal = $stmtBitTotal->fetch(PDO::FETCH_ASSOC);
                    $totalBitacoras = $bitacoraTotal['total'] ?? 0;
                    
                    // Contar bitácoras aprobadas
                    $sqlBitacorasAprobadas = "SELECT COUNT(*) as aprobadas FROM bitacora WHERE (idaprendiz = :idaprendiz OR idaprendiz = :ndocusu) AND estado = 'Aprobado'";
                    $stmtBitAprobadas = $conexion->prepare($sqlBitacorasAprobadas);
                    $stmtBitAprobadas->bindParam(':idaprendiz', $idusu);
                    $stmtBitAprobadas->bindParam(':ndocusu', $ndocusu);
                    $stmtBitAprobadas->execute();
                    $bitacoraAprobadas = $stmtBitAprobadas->fetch(PDO::FETCH_ASSOC);
                    $bitacorasCompletas = $bitacoraAprobadas['aprobadas'] ?? 0;
                    
                } catch (Exception $e) {
                    // Si hay error, mantener valores en 0
                    $totalBitacoras = 0;
                    $bitacorasCompletas = 0;
                }
                
                $programasProcesados[] = [
                    'idfic' => $programa['idfic'],
                    'nombre_programa' => $programa['nombre_programa'] ?? $programa['nombre_programa_completo'] ?? 'Sin nombre',
                    'tipo_programa' => $tipoPrograma,
                    'durmeses' => $duracionMesesEstimada,
                    'duracion_trimestres' => $duracionTrimestreEstimada,
                    'fecha_inicio' => $programa['fecha_inicio'],
                    'fecha_fin_estimada' => $programa['fecha_fin_estimada'],
                    'activo' => $programa['activo'],
                    'estado_programa' => $programa['estado_programa'],
                    'dias_cursados' => max(0, $programa['dias_cursados']),
                    'meses_cursados' => max(0, $programa['meses_cursados']),
                    'porcentaje_avance' => max(0, $porcentajeAvance),
                    'trimestre_actual' => max(0, $trimestreActual),
                    'total_bitacoras' => $totalBitacoras,
                    'bitacoras_completas' => $bitacorasCompletas,
                    'municipio' => $programa['municipio'],
                    'jornada' => $programa['jornada'],
                    'horas_total' => $horasTotal
                ];
            }
            
            return $programasProcesados;
            
        } catch (Exception $e) {
            error_log("Error al obtener programas del estudiante: " . $e->getMessage());
            return [];
        }
    }

    // Método auxiliar mejorado para determinar tipo de programa
    private function determinarTipoProgramaCorregido($tippro, $nombrePrograma = '') {
        // Primero intentar por nombre del programa
        $nombreUpper = strtoupper($nombrePrograma);
        
        if (strpos($nombreUpper, 'ANALISIS') !== false && strpos($nombreUpper, 'SOFTWARE') !== false) {
            return 'TECNOLOGO';
        }
        if (strpos($nombreUpper, 'TECNOLOGO') !== false) {
            return 'TECNOLOGO';
        }
        if (strpos($nombreUpper, 'TECNICO') !== false) {
            return 'TECNICO';
        }
        if (strpos($nombreUpper, 'CURSO') !== false) {
            return 'CURSO';
        }
        if (strpos($nombreUpper, 'COMPLEMENTARIA') !== false) {
            return 'COMPLEMENTARIA';
        }
        
        // Fallback por código
        switch ($tippro) {
            case 1: return 'TECNICO';
            case 2: return 'TECNOLOGO';  
            case 3: return 'CURSO';
            case 4: return 'COMPLEMENTARIA';
            case 5: return 'ESPECIALIZACION';
            default: 
                // Para "Análisis y Desarrollo de Software" es típicamente tecnólogo
                if (strpos($nombreUpper, 'ANALISIS') !== false || strpos($nombreUpper, 'DESARROLLO') !== false) {
                    return 'TECNOLOGO';
                }
                return 'PROGRAMA';
        }
    }

    // Método auxiliar para calcular duración en meses basada en horas
    private function calcularDuracionMeses($horasTotal, $tipoPrograma) {
        if ($horasTotal <= 0) {
            // Valores por defecto según tipo
            switch (strtoupper($tipoPrograma)) {
                case 'TECNICO': return 18;
                case 'TECNOLOGO': return 24;
                case 'CURSO': case 'COMPLEMENTARIA': return 6;
                case 'ESPECIALIZACION': return 12;
                default: return 12;
            }
        }
        
        // Estimación: aproximadamente 160 horas por mes lectivo
        return max(3, ceil($horasTotal / 160));
    }

    // Método auxiliar para calcular trimestres
    private function calcularDuracionTrimestres($duracionMeses) {
        return max(1, ceil($duracionMeses / 3));
    }

   
    //Obtener información académica completa del estudiante
    public function getInformacionAcademicaEstudiante($idusu) {
        try {
            $sql = "SELECT 
                        u.idusu, u.nomusu, u.ndocusu, u.emausu, u.telcan,
                        f.idfic, f.nomfic, f.tipfic, 
                        f.durmeses, f.durtri, f.nitempresa,
                        pf.idfic as programa_id,
                        pf.nomfic as programa_nombre,
                        pf.tipfic as programa_tipo,
                        pf.durmeses as programa_duracion_meses,
                        pf.durtri as programa_duracion_trimestres,
                        uf.actfic, uf.fecini, uf.fecfin,
                        COUNT(DISTINCT b.idbita) as total_bitacoras,
                        SUM(CASE WHEN b.firma_aprendiz IS NOT NULL AND b.firma_jefe IS NOT NULL AND b.firma_instructor IS NOT NULL THEN 1 ELSE 0 END) as bitacoras_completas,
                        CASE 
                            WHEN pf.tipfic = 'TECNICO' AND uf.fecini IS NOT NULL THEN 
                                TIMESTAMPDIFF(MONTH, uf.fecini, NOW())
                            WHEN pf.tipfic = 'TECNOLOGO' AND uf.fecini IS NOT NULL THEN 
                                TIMESTAMPDIFF(MONTH, uf.fecini, NOW())
                            WHEN pf.tipfic = 'CURSO' AND uf.fecini IS NOT NULL THEN 
                                TIMESTAMPDIFF(MONTH, uf.fecini, NOW())
                            ELSE 0
                        END as meses_cursados,
                        CASE 
                            WHEN pf.tipfic = 'TECNICO' AND pf.durtri > 0 THEN 
                                FLOOR(TIMESTAMPDIFF(MONTH, uf.fecini, NOW()) / (pf.durmeses / pf.durtri))
                            WHEN pf.tipfic = 'TECNOLOGO' AND pf.durtri > 0 THEN 
                                FLOOR(TIMESTAMPDIFF(MONTH, uf.fecini, NOW()) / (pf.durmeses / pf.durtri))
                            WHEN pf.tipfic = 'CURSO' AND pf.durtri > 0 THEN 
                                FLOOR(TIMESTAMPDIFF(MONTH, uf.fecini, NOW()) / (pf.durmeses / pf.durtri))
                            ELSE 0
                        END as trimestre_actual
                    FROM usuario u
                    INNER JOIN usufic uf ON u.idusu = uf.idusu AND uf.actfic = 1
                    INNER JOIN ficha f ON uf.idfic = f.idfic
                    INNER JOIN programa pf ON f.idprograma = pf.idprograma
                    LEFT JOIN bitacora b ON u.idusu = b.idusu
                    WHERE u.idusu = :idusu
                    GROUP BY u.idusu, f.idfic, pf.idprograma";
            
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(':idusu', $idusu);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Error al obtener información académica: " . $e->getMessage());
            return null;
        }
    }

    // Determinar certificaciones disponibles por programa específico - VERSIÓN FINAL
    public function determinarCertificacionesDisponiblesPorPrograma($idusu, $idfic = null, $ndocusu = null) {
        try {
            if (!$ndocusu) {
                $ndocusu = $idusu;
            }
            $programas = [];
            
            if ($idfic) {
                // Obtener información de un programa específico
                $sql = "SELECT 
                            uf.idfic,
                            uf.actfic as activo,
                            f.nomfic as nombre_programa,
                            f.finific as fecha_inicio,
                            f.ffinfic as fecha_fin_estimada,
                            p.nompro as nombre_programa_completo,
                            p.tippro as tipo_programa_codigo,
                            p.horlpro as horas_lectivas,
                            p.horppro as horas_practicas,
                            CASE 
                                WHEN f.finific IS NOT NULL THEN 
                                    TIMESTAMPDIFF(DAY, f.finific, NOW())
                                ELSE 0
                            END as dias_cursados,
                            CASE 
                                WHEN f.finific IS NOT NULL THEN 
                                    TIMESTAMPDIFF(MONTH, f.finific, NOW())
                                ELSE 0
                            END as meses_cursados
                        FROM usufic uf
                        INNER JOIN ficha f ON uf.idfic = f.idfic
                        INNER JOIN programa p ON f.codpro = p.codpro
                        WHERE (uf.idusu = :idusu OR uf.idusu = :ndocusu) AND uf.idfic = :idfic";
                
                $modelo = new conexion();
                $conexion = $modelo->get_conexion();
                $stmt = $conexion->prepare($sql);
                $stmt->bindParam(':idusu', $idusu);
                $stmt->bindParam(':ndocusu', $ndocusu);
                $stmt->bindParam(':idfic', $idfic);
                $stmt->execute();
                $programa = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($programa) {
                    // Procesar programa con cálculos
                    $tipoPrograma = $this->determinarTipoProgramaCorregido($programa['tipo_programa_codigo'] ?? 1, $programa['nombre_programa']);
                    $horasTotal = ($programa['horas_lectivas'] ?? 0) + ($programa['horas_practicas'] ?? 0);
                    $duracionMeses = $this->calcularDuracionMeses($horasTotal, $tipoPrograma);
                    $duracionTrimestres = $this->calcularDuracionTrimestres($duracionMeses);
                    
                    $porcentajeAvance = 0;
                    $trimestreActual = 0;
                    if ($programa['fecha_inicio'] && $duracionMeses > 0) {
                        $porcentajeAvance = min(100, round(($programa['meses_cursados'] * 100.0) / $duracionMeses, 1));
                        if ($duracionTrimestres > 0) {
                            $trimestreActual = min($duracionTrimestres, floor($programa['meses_cursados'] / ($duracionMeses / $duracionTrimestres)) + 1);
                        }
                    }
                    
                    $programas = [[
                        'idfic' => $programa['idfic'],
                        'nombre_programa' => $programa['nombre_programa'] ?? $programa['nombre_programa_completo'] ?? 'Sin nombre',
                        'tipo_programa' => $tipoPrograma,
                        'duracion_trimestres' => $duracionTrimestres,
                        'fecha_inicio' => $programa['fecha_inicio'],
                        'activo' => $programa['activo'],
                        'dias_cursados' => max(0, $programa['dias_cursados']),
                        'trimestre_actual' => max(0, $trimestreActual),
                        'porcentaje_avance' => max(0, $porcentajeAvance),
                        'total_bitacoras' => 0, // Calcular después
                        'bitacoras_completas' => 0 // Calcular después
                    ]];
                }
            } else {
                // Obtener todos los programas del estudiante
                $programas = $this->getProgramasActivosEstudiante($idusu, $ndocusu);
            }

            $resultado = [];

            foreach ($programas as $programa) {
                $certificacionesDisponibles = [];
                $tipoPrograma = $programa['tipo_programa'];
                $trimestreActual = $programa['trimestre_actual'];
                $duracionTrimestres = $programa['duracion_trimestres'];
                $porcentajeAvance = $programa['porcentaje_avance'];
                $diasCursados = $programa['dias_cursados'] ?? 0;

                // Obtener bitácoras reales para este programa con campos correctos
                $modelo = new conexion();
                $conexion = $modelo->get_conexion();
                
                $totalBitacoras = 0;
                $bitacorasCompletas = 0;
                
                try {
                    // Contar bitácoras totales del aprendiz
                    $sqlBitacorasTotal = "SELECT COUNT(*) as total FROM bitacora WHERE idaprendiz = :idaprendiz OR idaprendiz = :ndocusu";
                    $stmtBitTotal = $conexion->prepare($sqlBitacorasTotal);
                    $stmtBitTotal->bindParam(':idaprendiz', $idusu);
                    $stmtBitTotal->bindParam(':ndocusu', $ndocusu);
                    $stmtBitTotal->execute();
                    $bitacoraTotal = $stmtBitTotal->fetch(PDO::FETCH_ASSOC);
                    $totalBitacoras = $bitacoraTotal['total'] ?? 0;
                    
                    // Contar bitácoras aprobadas
                    $sqlBitacorasAprobadas = "SELECT COUNT(*) as aprobadas FROM bitacora WHERE (idaprendiz = :idaprendiz OR idaprendiz = :ndocusu) AND estado = 'Aprobado'";
                    $stmtBitAprobadas = $conexion->prepare($sqlBitacorasAprobadas);
                    $stmtBitAprobadas->bindParam(':idaprendiz', $idusu);
                    $stmtBitAprobadas->bindParam(':ndocusu', $ndocusu);
                    $stmtBitAprobadas->execute();
                    $bitacoraAprobadas = $stmtBitAprobadas->fetch(PDO::FETCH_ASSOC);
                    $bitacorasCompletas = $bitacoraAprobadas['aprobadas'] ?? 0;
                    
                } catch (Exception $e) {
                    $totalBitacoras = 0;
                    $bitacorasCompletas = 0;
                }

                // Certificaciones según tipo de programa y progreso
                switch (strtoupper($tipoPrograma)) {
                    case 'TECNICO':
                        // Certificación de inicio (después de 1 mes)
                        if ($diasCursados >= 30) {
                            $certificacionesDisponibles[] = [
                                'tipo' => 'certificacion_inicio',
                                'nombre' => 'Certificación de Inicio de Formación Técnica',
                                'descripcion' => "Certifica que está cursando el programa técnico {$programa['nombre_programa']} desde " . date('d/m/Y', strtotime($programa['fecha_inicio'])),
                                'requisitos_cumplidos' => true,
                                'idfic' => $programa['idfic'],
                                'motivo_auto' => "Estudiante de programa técnico con {$diasCursados} días de formación"
                            ];
                        }

                        // Certificación parcial (después de 2 trimestres o 33% de avance)
                        if ($trimestreActual >= 2 || $porcentajeAvance >= 33) {
                            $certificacionesDisponibles[] = [
                                'tipo' => 'certificacion_parcial',
                                'nombre' => 'Certificación de Competencias Parciales Técnicas',
                                'descripcion' => "Certifica competencias adquiridas en {$trimestreActual} trimestres del programa {$programa['nombre_programa']} con {$porcentajeAvance}% de avance",
                                'requisitos_cumplidos' => true,
                                'idfic' => $programa['idfic'],
                                'motivo_auto' => "Técnico en trimestre {$trimestreActual} de {$duracionTrimestres}, avance: {$porcentajeAvance}%"
                            ];
                        }

                        // Certificación de etapa productiva (último trimestre o 80% de avance)
                        if ($trimestreActual >= ($duracionTrimestres - 1) || $porcentajeAvance >= 80) {
                            $certificacionesDisponibles[] = [
                                'tipo' => 'certificacion_etapa_productiva',
                                'nombre' => 'Certificación de Etapa Productiva Técnica',
                                'descripcion' => "Certifica participación en etapa productiva del programa técnico {$programa['nombre_programa']}",
                                'requisitos_cumplidos' => true,
                                'idfic' => $programa['idfic'],
                                'motivo_auto' => "Etapa productiva técnica con {$bitacorasCompletas}/{$totalBitacoras} bitácoras aprobadas"
                            ];
                        }

                        // Certificación completa (programa terminado)
                        if ($porcentajeAvance >= 100 || $trimestreActual >= $duracionTrimestres) {
                            $certificacionesDisponibles[] = [
                                'tipo' => 'certificacion_tecnica_completa',
                                'nombre' => 'Certificación Técnica Completa',
                                'descripcion' => "Certifica la culminación exitosa del programa técnico {$programa['nombre_programa']}",
                                'requisitos_cumplidos' => true,
                                'idfic' => $programa['idfic'],
                                'motivo_auto' => "Programa técnico completado exitosamente con todas las competencias"
                            ];
                        }
                        break;

                    case 'TECNOLOGO':
                        // Certificación de inicio (después de 1 mes)
                        if ($diasCursados >= 30) {
                            $certificacionesDisponibles[] = [
                                'tipo' => 'certificacion_inicio',
                                'nombre' => 'Certificación de Inicio de Formación Tecnológica',
                                'descripcion' => "Certifica que está cursando el programa tecnológico {$programa['nombre_programa']} desde " . date('d/m/Y', strtotime($programa['fecha_inicio'])),
                                'requisitos_cumplidos' => true,
                                'idfic' => $programa['idfic'],
                                'motivo_auto' => "Estudiante de programa tecnológico con {$diasCursados} días de formación"
                            ];
                        }

                        // Certificación parcial (después de 3 trimestres o 40% de avance)
                        if ($trimestreActual >= 3 || $porcentajeAvance >= 40) {
                            $certificacionesDisponibles[] = [
                                'tipo' => 'certificacion_parcial',
                                'nombre' => 'Certificación de Competencias Parciales Tecnológicas',
                                'descripcion' => "Certifica competencias tecnológicas adquiridas en {$trimestreActual} trimestres con {$porcentajeAvance}% de avance",
                                'requisitos_cumplidos' => true,
                                'idfic' => $programa['idfic'],
                                'motivo_auto' => "Tecnólogo en trimestre {$trimestreActual} de {$duracionTrimestres}, avance: {$porcentajeAvance}%"
                            ];
                        }

                        // Certificación de etapa productiva (último trimestre o 85% de avance)
                        if ($trimestreActual >= ($duracionTrimestres - 1) || $porcentajeAvance >= 85) {
                            $certificacionesDisponibles[] = [
                                'tipo' => 'certificacion_etapa_productiva',
                                'nombre' => 'Certificación de Etapa Productiva Tecnológica',
                                'descripcion' => "Certifica participación en etapa productiva del programa tecnológico {$programa['nombre_programa']}",
                                'requisitos_cumplidos' => true,
                                'idfic' => $programa['idfic'],
                                'motivo_auto' => "Etapa productiva tecnológica con {$bitacorasCompletas}/{$totalBitacoras} bitácoras aprobadas"
                            ];
                        }

                        // Certificación completa (programa terminado)
                        if ($porcentajeAvance >= 100) {
                            $certificacionesDisponibles[] = [
                                'tipo' => 'certificacion_tecnologica_completa',
                                'nombre' => 'Certificación Tecnológica Completa',
                                'descripcion' => "Certifica la culminación exitosa del programa tecnológico {$programa['nombre_programa']}",
                                'requisitos_cumplidos' => true,
                                'idfic' => $programa['idfic'],
                                'motivo_auto' => "Programa tecnológico completado exitosamente"
                            ];
                        }
                        break;

                    case 'CURSO':
                    case 'COMPLEMENTARIA':
                        // Para cursos complementarios
                        if ($diasCursados >= 15) {
                            $certificacionesDisponibles[] = [
                                'tipo' => 'certificacion_curso_inicio',
                                'nombre' => 'Certificación de Participación en Curso',
                                'descripcion' => "Certifica participación activa en el curso {$programa['nombre_programa']} desde " . date('d/m/Y', strtotime($programa['fecha_inicio'])),
                                'requisitos_cumplidos' => true,
                                'idfic' => $programa['idfic'],
                                'motivo_auto' => "Participación en curso complementario por {$diasCursados} días"
                            ];
                        }

                        if ($porcentajeAvance >= 50) {
                            $certificacionesDisponibles[] = [
                                'tipo' => 'certificacion_curso_intermedio',
                                'nombre' => 'Certificación de Progreso en Curso',
                                'descripcion' => "Certifica {$porcentajeAvance}% de avance en el curso {$programa['nombre_programa']}",
                                'requisitos_cumplidos' => true,
                                'idfic' => $programa['idfic'],
                                'motivo_auto' => "Curso con {$porcentajeAvance}% de avance completado"
                            ];
                        }

                        if ($porcentajeAvance >= 100) {
                            $certificacionesDisponibles[] = [
                                'tipo' => 'certificacion_curso_completo',
                                'nombre' => 'Certificación de Finalización de Curso',
                                'descripcion' => "Certifica la finalización exitosa del curso {$programa['nombre_programa']}",
                                'requisitos_cumplidos' => true,
                                'idfic' => $programa['idfic'],
                                'motivo_auto' => "Curso complementario finalizado exitosamente"
                            ];
                        }
                        break;
                        
                    default: // Para cualquier otro tipo de programa
                        // Siempre disponible para estudiantes activos
                        if ($diasCursados >= 30) {
                            $certificacionesDisponibles[] = [
                                'tipo' => 'certificacion_inicio',
                                'nombre' => 'Certificación de Inicio de Formación',
                                'descripcion' => "Certifica que está cursando {$programa['nombre_programa']} desde " . date('d/m/Y', strtotime($programa['fecha_inicio'])),
                                'requisitos_cumplidos' => true,
                                'idfic' => $programa['idfic'],
                                'motivo_auto' => "Estudiante con {$diasCursados} días de formación"
                            ];
                        }

                        if ($porcentajeAvance >= 50) {
                            $certificacionesDisponibles[] = [
                                'tipo' => 'certificacion_parcial',
                                'nombre' => 'Certificación de Competencias Parciales',
                                'descripcion' => "Certifica competencias adquiridas con {$porcentajeAvance}% de avance en {$programa['nombre_programa']}",
                                'requisitos_cumplidos' => true,
                                'idfic' => $programa['idfic'],
                                'motivo_auto' => "Avance del {$porcentajeAvance}% en el programa"
                            ];
                        }

                        if ($porcentajeAvance >= 100) {
                            $certificacionesDisponibles[] = [
                                'tipo' => 'certificacion_completa',
                                'nombre' => 'Certificación de Finalización',
                                'descripcion' => "Certifica la culminación exitosa de {$programa['nombre_programa']}",
                                'requisitos_cumplidos' => true,
                                'idfic' => $programa['idfic'],
                                'motivo_auto' => "Programa completado exitosamente"
                            ];
                        }
                        break;
                }

                // Siempre disponible: Constancia de estudiante activo
                if ($programa['activo'] == 1) {
                    $certificacionesDisponibles[] = [
                        'tipo' => 'constancia_estudiante_activo',
                        'nombre' => 'Constancia de Estudiante Activo',
                        'descripcion' => "Constancia que certifica participación activa en {$programa['nombre_programa']}",
                        'requisitos_cumplidos' => true,
                        'idfic' => $programa['idfic'],
                        'motivo_auto' => "Estudiante activo en el SENA"
                    ];
                }

                $resultado[] = [
                    'programa' => $programa,
                    'certificaciones_disponibles' => $certificacionesDisponibles
                ];
            }

            return $resultado;

        } catch (Exception $e) {
            error_log("Error al determinar certificaciones por programa: " . $e->getMessage());
            return [];
        }
    }
    
    //Guardar solicitud mejorada con información del programa
    public function saveSolicitudCertificacionConPrograma($idusu, $tipoCertificacion, $idfic) {
        try {
            // Obtener certificaciones disponibles para el programa específico
            $datosDisponibles = $this->determinarCertificacionesDisponiblesPorPrograma($idusu, $idfic);
            
            if (empty($datosDisponibles)) {
                return ['success' => false, 'message' => 'No se encontró información del programa'];
            }

            $programa = $datosDisponibles[0]['programa'];
            $certificacionesDisponibles = $datosDisponibles[0]['certificaciones_disponibles'];

            // Buscar la certificación solicitada
            $certificacionSolicitada = null;
            foreach ($certificacionesDisponibles as $cert) {
                if ($cert['tipo'] === $tipoCertificacion && $cert['idfic'] == $idfic) {
                    $certificacionSolicitada = $cert;
                    break;
                }
            }

            if (!$certificacionSolicitada) {
                return ['success' => false, 'message' => 'Certificación no disponible para tu progreso actual en este programa'];
            }

            if (!$certificacionSolicitada['requisitos_cumplidos']) {
                return ['success' => false, 'message' => 'No cumples los requisitos para esta certificación'];
            }

            // Verificar solicitudes duplicadas
            $sqlVerificar = "SELECT COUNT(*) as total FROM solicicerti 
                           WHERE idusu = :idusu AND tipo_certificacion = :tipo AND idfic = :idfic
                           AND estado IN ('pendiente', 'aprobada_coordinacion')";
            
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $stmt = $conexion->prepare($sqlVerificar);
            $stmt->bindParam(":idusu", $idusu);
            $stmt->bindParam(":tipo", $tipoCertificacion);
            $stmt->bindParam(":idfic", $idfic);
            $stmt->execute();
            $existente = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existente['total'] > 0) {
                return ['success' => false, 'message' => 'Ya tienes una solicitud pendiente de este tipo para este programa.'];
            }
            
            // Insertar nueva solicitud
            $sql = "INSERT INTO solicicerti
                    (idusu, tipo_certificacion, idfic, motivo, fecha_solicitud, estado) 
                    VALUES (:idusu, :tipo, :idfic, :motivo, NOW(), 'pendiente')";
            
            $result = $conexion->prepare($sql);
            $result->bindParam(":idusu", $idusu);
            $result->bindParam(":tipo", $tipoCertificacion);
            $result->bindParam(":idfic", $idfic);
            $result->bindParam(":motivo", $certificacionSolicitada['motivo_auto']);
            
            if ($result->execute()) {
                $solicitudId = $conexion->lastInsertId();
                return [
                    'success' => true, 
                    'message' => 'Solicitud enviada correctamente para el programa: ' . $programa['nombre_programa'], 
                    'id' => $solicitudId,
                    'certificacion_info' => $certificacionSolicitada,
                    'programa_info' => $programa
                ];
            } else {
                return ['success' => false, 'message' => 'Error al enviar la solicitud.'];
            }
            
        } catch (Exception $e) {
            error_log("Error en solicitud certificación con programa: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error del sistema. Intenta más tarde.'];
        }
    }


    // Método para guardar certificación
    public function save($dt = 1) {
        try {
            $sql = "INSERT INTO certificaciones (
                idaprendiz, nombre_certificacion, descripcion, fecha_obtencion, 
                fecha_vencimiento, entidad_emisora, numero_certificacion, 
                imagen_certificacion, archivo_certificacion, estado, tipo_certificacion
            ) VALUES (
                :idaprendiz, :nombre_certificacion, :descripcion, :fecha_obtencion,
                :fecha_vencimiento, :entidad_emisora, :numero_certificacion,
                :imagen_certificacion, :archivo_certificacion, :estado, :tipo_certificacion
            )";

            $modelo = new conexion(); 
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            
            $result->bindParam(":idaprendiz", $this->idaprendiz);
            $result->bindParam(":nombre_certificacion", $this->nombre_certificacion);
            $result->bindParam(":descripcion", $this->descripcion);
            $result->bindParam(":fecha_obtencion", $this->fecha_obtencion);
            $result->bindParam(":fecha_vencimiento", $this->fecha_vencimiento);
            $result->bindParam(":entidad_emisora", $this->entidad_emisora);
            $result->bindParam(":numero_certificacion", $this->numero_certificacion);
            $result->bindParam(":imagen_certificacion", $this->imagen_certificacion);
            $result->bindParam(":archivo_certificacion", $this->archivo_certificacion);
            $result->bindParam(":estado", $this->estado);
            $result->bindParam(":tipo_certificacion", $this->tipo_certificacion);

            $result->execute();
            return true; 
        } catch (Exception $e) {
            if ($dt == 1) ManejoError($e); 
            echo "Error al guardar certificación: " . $e->getMessage();
            return false;
        }
    }

    // Método para actualizar certificación
    public function edit($id, $dt = 1) {
        try {
            $sql = "UPDATE certificaciones SET 
                nombre_certificacion = :nombre_certificacion,
                descripcion = :descripcion,
                fecha_obtencion = :fecha_obtencion,
                fecha_vencimiento = :fecha_vencimiento,
                entidad_emisora = :entidad_emisora,
                numero_certificacion = :numero_certificacion,
                imagen_certificacion = :imagen_certificacion,
                archivo_certificacion = :archivo_certificacion,
                estado = :estado,
                tipo_certificacion = :tipo_certificacion
                WHERE idcertificacion = :idcertificacion";

            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);

            $result->bindParam(":nombre_certificacion", $this->nombre_certificacion);
            $result->bindParam(":descripcion", $this->descripcion);
            $result->bindParam(":fecha_obtencion", $this->fecha_obtencion);
            $result->bindParam(":fecha_vencimiento", $this->fecha_vencimiento);
            $result->bindParam(":entidad_emisora", $this->entidad_emisora);
            $result->bindParam(":numero_certificacion", $this->numero_certificacion);
            $result->bindParam(":imagen_certificacion", $this->imagen_certificacion);
            $result->bindParam(":archivo_certificacion", $this->archivo_certificacion);
            $result->bindParam(":estado", $this->estado);
            $result->bindParam(":tipo_certificacion", $this->tipo_certificacion);
            $result->bindParam(":idcertificacion", $id);

            $result->execute();
            return true;
        } catch (Exception $e) {
            if ($dt == 1) ManejoError($e);
            echo "Error al actualizar certificación: " . $e->getMessage();
            return false;
        }
    }

    // Método para eliminar certificación
    public function delete($idcertificacion, $dt = 1) {
        try {
            $sql = "DELETE FROM certificaciones WHERE idcertificacion = :idcertificacion";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->bindParam(":idcertificacion", $idcertificacion);
            $result->execute();
            return true; 
        } catch (Exception $e) {
            if ($dt == 1) ManejoError($e); 
            echo "Error al eliminar certificación: " . $e->getMessage();
            return false;
        }
    }

    // Obtener una certificación específica
    public function getOne($idcertificacion) {
        $sql = "SELECT * FROM certificaciones WHERE idcertificacion = :idcertificacion";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':idcertificacion', $idcertificacion);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Obtener todas las certificaciones de un usuario (tabla certificaciones y solicitudes completadas)
    public function getCertificacionesByUsuario($idusu, $ndocusu = null) {
        try {
            if (!$ndocusu) {
                $ndocusu = $idusu;
            }
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();

            // 1. Consultar tabla certificaciones
            $sql = "SELECT c.*, COALESCE(u.nomusu, '') as nomusu 
                    FROM certificaciones c
                    LEFT JOIN usuario u ON (c.idaprendiz = u.idusu OR c.idaprendiz = u.ndocusu)
                    WHERE c.idaprendiz = :idusu OR c.idaprendiz = :ndocusu
                    ORDER BY c.fecha_obtencion DESC";
            
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(':idusu', $idusu);
            $stmt->bindParam(':ndocusu', $ndocusu);
            $stmt->execute();
            $certificaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // 2. Incluir también solicitudes completadas de solicicerti como certificaciones obtenidas
            $sqlSol = "SELECT 
                        s.id, 
                        s.idusu, 
                        s.tipo_certificacion, 
                        s.idfic, 
                        s.motivo, 
                        s.fecha_solicitud, 
                        s.fecha_firma_subdirector,
                        COALESCE(f.nomfic, p.nompro, 'SENA') as nombre_programa
                    FROM solicicerti s
                    LEFT JOIN ficha f ON s.idfic = f.idfic
                    LEFT JOIN programa p ON f.codpro = p.codpro
                    WHERE (s.idusu = :idusu OR s.idusu = :ndocusu)
                      AND s.estado = 'completada'
                    ORDER BY s.fecha_solicitud DESC";
            $stmtSol = $conexion->prepare($sqlSol);
            $stmtSol->bindParam(':idusu', $idusu);
            $stmtSol->bindParam(':ndocusu', $ndocusu);
            $stmtSol->execute();
            $solCompletadas = $stmtSol->fetchAll(PDO::FETCH_ASSOC);

            foreach ($solCompletadas as $sol) {
                $yaExiste = false;
                foreach ($certificaciones as $c) {
                    if (($c['tipo_certificacion'] ?? '') === $sol['tipo_certificacion']) {
                        $yaExiste = true;
                        break;
                    }
                }

                if (!$yaExiste) {
                    $certificaciones[] = [
                        'idcertificacion' => 'SOL-' . $sol['id'],
                        'idaprendiz' => $sol['idusu'],
                        'nombre_certificacion' => 'Certificación ' . ucwords(str_replace('_', ' ', $sol['tipo_certificacion'])),
                        'descripcion' => $sol['motivo'] ?: ('Certificación completada para el programa ' . $sol['nombre_programa']),
                        'fecha_obtencion' => $sol['fecha_firma_subdirector'] ?: $sol['fecha_solicitud'],
                        'fecha_vencimiento' => null,
                        'entidad_emisora' => 'SENA',
                        'numero_certificacion' => 'SOL-' . str_pad($sol['id'], 6, '0', STR_PAD_LEFT),
                        'imagen_certificacion' => null,
                        'archivo_certificacion' => 'home.php?pg=etavslcer&opera=descargar&id=' . $sol['id'],
                        'estado' => 'activo',
                        'tipo_certificacion' => $sol['tipo_certificacion'],
                        'nomusu' => ''
                    ];
                }
            }

            return $certificaciones;
        } catch (Exception $e) {
            error_log("Error en getCertificacionesByUsuario: " . $e->getMessage());
            return [];
        }
    }

    // Obtener certificaciones por estado
    public function getCertificacionesByEstado($idusu, $estado, $ndocusu = null) {
        try {
            if (!$ndocusu) {
                $ndocusu = $idusu;
            }
            $sql = "SELECT c.*, COALESCE(u.nomusu, '') as nomusu 
                    FROM certificaciones c
                    LEFT JOIN usuario u ON (c.idaprendiz = u.idusu OR c.idaprendiz = u.ndocusu)
                    WHERE (c.idaprendiz = :idusu OR c.idaprendiz = :ndocusu) AND c.estado = :estado
                    ORDER BY c.fecha_obtencion DESC";
            
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(':idusu', $idusu);
            $stmt->bindParam(':ndocusu', $ndocusu);
            $stmt->bindParam(':estado', $estado);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en getCertificacionesByEstado: " . $e->getMessage());
            return [];
        }
    }

    // Obtener certificaciones por tipo
    public function getCertificacionesByTipo($idusu, $tipo, $ndocusu = null) {
        try {
            if (!$ndocusu) {
                $ndocusu = $idusu;
            }
            $sql = "SELECT c.*, COALESCE(u.nomusu, '') as nomusu 
                    FROM certificaciones c
                    LEFT JOIN usuario u ON (c.idaprendiz = u.idusu OR c.idaprendiz = u.ndocusu)
                    WHERE (c.idaprendiz = :idusu OR c.idaprendiz = :ndocusu) AND c.tipo_certificacion = :tipo
                    ORDER BY c.fecha_obtencion DESC";
            
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(':idusu', $idusu);
            $stmt->bindParam(':ndocusu', $ndocusu);
            $stmt->bindParam(':tipo', $tipo);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en getCertificacionesByTipo: " . $e->getMessage());
            return [];
        }
    }

    // Obtener resumen de certificaciones
    public function getResumenCertificaciones($idusu, $ndocusu = null) {
        try {
            if (!$ndocusu) {
                $ndocusu = $idusu;
            }
            $sql = "SELECT 
                        COUNT(*) as total_certificaciones,
                        SUM(CASE WHEN estado = 'activo' THEN 1 ELSE 0 END) as activas,
                        SUM(CASE WHEN estado = 'vencido' THEN 1 ELSE 0 END) as vencidas,
                        SUM(CASE WHEN estado = 'por_vencer' THEN 1 ELSE 0 END) as por_vencer
                    FROM certificaciones 
                    WHERE idaprendiz = :idusu OR idaprendiz = :ndocusu";
            
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(':idusu', $idusu);
            $stmt->bindParam(':ndocusu', $ndocusu);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en getResumenCertificaciones: " . $e->getMessage());
            return null;
        }
    }

    // Obtener tipos de certificación disponibles
    public function getTiposCertificacion() {
        try {
            $sql = "SELECT DISTINCT tipo_certificacion FROM certificaciones WHERE tipo_certificacion IS NOT NULL";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $stmt = $conexion->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en getTiposCertificacion: " . $e->getMessage());
            return [];
        }
    }

    // Actualizar estados de certificaciones (para verificar vencimientos)
    public function actualizarEstados($idusu, $ndocusu = null) {
        try {
            if (!$ndocusu) {
                $ndocusu = $idusu;
            }
            $sql = "UPDATE certificaciones SET 
                    estado = CASE 
                        WHEN fecha_vencimiento < CURDATE() THEN 'vencido'
                        WHEN fecha_vencimiento <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 'por_vencer'
                        ELSE 'activo'
                    END
                    WHERE (idaprendiz = :idusu OR idaprendiz = :ndocusu) AND fecha_vencimiento IS NOT NULL";
            
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(':idusu', $idusu);
            $stmt->bindParam(':ndocusu', $ndocusu);
            $stmt->execute();
            return true;
        } catch (Exception $e) {
            error_log("Error al actualizar estados: " . $e->getMessage());
            return false;
        }
    }

    // Obtener datos del usuario (permite buscar por idusu o ndocusu)
    public function getOneUsu($idusu) {
        try {
            $sql = "SELECT idusu, nomusu, ndocusu, telcan, emausu FROM usuario WHERE idusu = :idusu OR ndocusu = :ndocusu";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(':idusu', $idusu);
            $stmt->bindParam(':ndocusu', $idusu);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en getOneUsu: " . $e->getMessage());
            return [];
        }
    }

    // Obtener nombre del programa del estudiante
    public function getNombrePrograma($idusu, $ndocusu = null) {
        try {
            if (!$ndocusu) {
                $ndocusu = $idusu;
            }
            $sql = "SELECT f.nomfic, p.nompro 
                    FROM usufic uf
                    INNER JOIN ficha f ON uf.idfic = f.idfic
                    INNER JOIN programa p ON f.codpro = p.codpro
                    WHERE uf.idusu = :idusu OR uf.idusu = :ndocusu";
            
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(':idusu', $idusu);
            $stmt->bindParam(':ndocusu', $ndocusu);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en getNombrePrograma: " . $e->getMessage());
            return [];
        }
    }

    // Obtener solicitudes con información del programa
    public function getMisSolicitudesConPrograma($idusu, $ndocusu = null) {
        try {
            if (!$ndocusu) {
                $ndocusu = $idusu;
            }
            $sql = "SELECT 
                        s.id, 
                        s.idusu,
                        s.tipo_certificacion, 
                        s.idfic, 
                        s.motivo,  
                        s.fecha_solicitud,  
                        s.estado, 
                        s.respuesta_coordinacion, 
                        s.fecha_respuesta_coordinacion, 
                        s.firma_subdirector, 
                        s.fecha_firma_subdirector, 
                        s.observaciones_finales, 
                        COALESCE(f.nomfic, p.nompro, 'Sin programa') as nombre_programa, 
                        COALESCE(p.tippro, 1) as tipo_programa 
                    FROM solicicerti s
                    LEFT JOIN ficha f ON s.idfic = f.idfic
                    LEFT JOIN programa p ON f.codpro = p.codpro
                    WHERE s.idusu = :idusu OR s.idusu = :ndocusu 
                    ORDER BY s.fecha_solicitud DESC";
            
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(':idusu', $idusu);
            $stmt->bindParam(':ndocusu', $ndocusu);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Error al obtener solicitudes con programa: " . $e->getMessage());
            return [];
        }
    }

    // Obtener estadísticas de mis solicitudes
    public function getEstadisticasMisSolicitudes($idusu, $ndocusu = null) {
        try {
            if (!$ndocusu) {
                $ndocusu = $idusu;
            }
            $sql = "SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN estado = 'pendiente' THEN 1 ELSE 0 END) as pendientes,
                        SUM(CASE WHEN estado = 'aprobada_coordinacion' THEN 1 ELSE 0 END) as esperando_firma,
                        SUM(CASE WHEN estado = 'completada' THEN 1 ELSE 0 END) as completadas,
                        SUM(CASE WHEN estado = 'rechazada' THEN 1 ELSE 0 END) as rechazadas
                    FROM solicicerti 
                    WHERE idusu = :idusu OR idusu = :ndocusu";
            
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(':idusu', $idusu);
            $stmt->bindParam(':ndocusu', $ndocusu);
            $stmt->execute();
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            return [
                'total' => (int)($res['total'] ?? 0),
                'pendientes' => (int)($res['pendientes'] ?? 0),
                'esperando_firma' => (int)($res['esperando_firma'] ?? 0),
                'completadas' => (int)($res['completadas'] ?? 0),
                'rechazadas' => (int)($res['rechazadas'] ?? 0)
            ];
            
        } catch (Exception $e) {
            error_log("Error al obtener estadísticas: " . $e->getMessage());
            return [
                'total' => 0,
                'pendientes' => 0,
                'esperando_firma' => 0,
                'completadas' => 0,
                'rechazadas' => 0
            ];
        }
    }


}

?>