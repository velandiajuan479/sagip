<?php

class Etamgssol {
    
    // Método principal para ser usado por coordinador
    public function getAllSolicitudesDetalladas() {
        return $this->getAllSolicitudesDetalladasConPrograma();
    }

    // Obtener solicitudes con información detallada del programa
    public function getAllSolicitudesDetalladasConPrograma() {
        try {
            // Consulta base garantizando siempre 's.id as idsolicitud'
            $sqlSimple = "SELECT 
                            s.id as idsolicitud, 
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
                            u.nomusu, 
                            u.ndocusu, 
                            COALESCE(u.emausu, 'N/A') as emausu, 
                            COALESCE(u.telcan, 'N/A') as telcan,
                            'Sin programa asignado' as nombre_programa,
                            s.idfic as codigo_ficha,
                            'N/A' as tipo_programa,
                            0 as duracion_meses,
                            0 as duracion_trimestres,
                            NULL as fecha_inicio_programa,
                            0 as trimestre_actual,
                            0 as porcentaje_avance,
                            0 as total_bitacoras,
                            0 as bitacoras_completas,
                            CASE 
                                WHEN s.estado = 'pendiente' THEN 0
                                WHEN s.estado = 'aprobada_coordinacion' THEN 1  
                                WHEN s.estado = 'completada' THEN 2
                                WHEN s.estado = 'rechazada' THEN 3
                                ELSE 4
                            END as orden_estado
                        FROM solicicerti s
                        INNER JOIN usuario u ON s.idusu = u.idusu
                        ORDER BY orden_estado ASC, s.fecha_solicitud DESC";
            
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $stmt = $conexion->prepare($sqlSimple);
            $stmt->execute();
            $resultadoSimple = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (!empty($resultadoSimple)) {
                foreach ($resultadoSimple as &$solicitud) {
                    // Preservar explícitamente el ID original
                    if (!isset($solicitud['idsolicitud']) || empty($solicitud['idsolicitud'])) {
                        $solicitud['idsolicitud'] = $solicitud['id'] ?? 0;
                    }

                    try {
                        $sqlPrograma = "SELECT 
                                            f.nomfic, 
                                            f.finific,
                                            f.ffinfic,
                                            p.nompro,
                                            p.tippro,
                                            p.horlpro,
                                            p.horppro
                                        FROM ficha f
                                        LEFT JOIN programa p ON f.codpro = p.codpro
                                        WHERE f.idfic = :idfic";
                        
                        $stmtProg = $conexion->prepare($sqlPrograma);
                        $stmtProg->bindParam(':idfic', $solicitud['idfic']);
                        $stmtProg->execute();
                        $programa = $stmtProg->fetch(PDO::FETCH_ASSOC);
                        
                        if ($programa) {
                            $solicitud['nombre_programa'] = $programa['nomfic'] ?: $programa['nompro'] ?: 'Sin nombre';
                            $solicitud['fecha_inicio_programa'] = $programa['finific'];
                            
                            $nombreUpper = strtoupper($programa['nompro'] ?: $programa['nomfic'] ?: '');
                            if (strpos($nombreUpper, 'TECNOLOGO') !== false || strpos($nombreUpper, 'ANALISIS') !== false) {
                                $solicitud['tipo_programa'] = 'TECNOLOGO';
                                $solicitud['duracion_trimestres'] = 8;
                                $solicitud['duracion_meses'] = 24;
                            } elseif (strpos($nombreUpper, 'TECNICO') !== false) {
                                $solicitud['tipo_programa'] = 'TECNICO';
                                $solicitud['duracion_trimestres'] = 6;
                                $solicitud['duracion_meses'] = 18;
                            } elseif (strpos($nombreUpper, 'CURSO') !== false || strpos($nombreUpper, 'COMPLEMENTARIA') !== false) {
                                $solicitud['tipo_programa'] = 'CURSO';
                                $solicitud['duracion_trimestres'] = 2;
                                $solicitud['duracion_meses'] = 6;
                            } else {
                                $solicitud['tipo_programa'] = 'PROGRAMA';
                                $solicitud['duracion_trimestres'] = 4;
                                $solicitud['duracion_meses'] = 12;
                            }
                            
                            if ($programa['finific']) {
                                $mesesCursados = max(0, floor((time() - strtotime($programa['finific'])) / (30 * 24 * 3600)));
                                $solicitud['porcentaje_avance'] = min(100, round(($mesesCursados * 100.0) / $solicitud['duracion_meses'], 1));
                                $solicitud['trimestre_actual'] = min($solicitud['duracion_trimestres'], 
                                                                floor($mesesCursados / ($solicitud['duracion_meses'] / $solicitud['duracion_trimestres'])) + 1);
                            }
                        }
                        
                        $sqlBitacoras = "SELECT 
                                            COUNT(*) as total,
                                            SUM(CASE WHEN estado = 'Aprobado' THEN 1 ELSE 0 END) as completas
                                        FROM bitacora 
                                        WHERE idaprendiz = :idusu";
                        
                        $stmtBit = $conexion->prepare($sqlBitacoras);
                        $stmtBit->bindParam(':idusu', $solicitud['idusu']);
                        $stmtBit->execute();
                        $bitacoras = $stmtBit->fetch(PDO::FETCH_ASSOC);
                        
                        if ($bitacoras) {
                            $solicitud['total_bitacoras'] = $bitacoras['total'] ?: 0;
                            $solicitud['bitacoras_completas'] = $bitacoras['completas'] ?: 0;
                        }
                        
                    } catch (Exception $eProg) {
                        error_log("Error al obtener datos del programa para solicitud {$solicitud['idsolicitud']}: " . $eProg->getMessage());
                    }
                }
                
                return $resultadoSimple;
            }
            
            return [];
            
        } catch (Exception $e) {
            error_log("Error en getAllSolicitudesDetalladasConPrograma: " . $e->getMessage());
            
            try {
                $sqlBasico = "SELECT 
                                s.id as idsolicitud,
                                s.tipo_certificacion,
                                s.estado,
                                s.fecha_solicitud,
                                u.nomusu,
                                u.ndocusu,
                                s.idfic as codigo_ficha,
                                'Sin programa' as nombre_programa,
                                'N/A' as tipo_programa,
                                0 as trimestre_actual,
                                0 as duracion_trimestres,
                                0 as porcentaje_avance,
                                0 as total_bitacoras,
                                0 as bitacoras_completas,
                                'N/A' as emausu,
                                'N/A' as telcan,
                                s.motivo,
                                s.respuesta_coordinacion,
                                s.fecha_respuesta_coordinacion,
                                s.firma_subdirector,
                                s.fecha_firma_subdirector,
                                NULL as fecha_inicio_programa
                            FROM solicicerti s
                            INNER JOIN usuario u ON s.idusu = u.idusu
                            ORDER BY s.fecha_solicitud DESC";
                
                $modelo = new conexion();
                $conexion = $modelo->get_conexion();
                $stmt = $conexion->prepare($sqlBasico);
                $stmt->execute();
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
                
            } catch (Exception $eBasico) {
                error_log("ERROR CRÍTICO - Falló también la consulta básica: " . $eBasico->getMessage());
                return [];
            }
        }
    }

    public function validarSolicitudAutomaticaConPrograma($idsolicitud) {
        try {
            $solicitud = $this->getDetalleSolicitudConPrograma($idsolicitud);
            
            if (!$solicitud) {
                return ['valida' => false, 'motivo' => 'Solicitud no encontrada'];
            }

            $tipo = $solicitud['tipo_certificacion'];
            $tipoPrograma = $solicitud['tipo_programa'];
            $trimestreActual = $solicitud['trimestre_actual'];
            $duracionTrimestres = $solicitud['duracion_trimestres'];
            $porcentajeAvance = $solicitud['porcentaje_avance'];
            $bitacorasCompletas = $solicitud['bitacoras_completas'];
            $totalBitacoras = $solicitud['total_bitacoras'];
            $diasFormacion = $solicitud['fecha_inicio_programa'] ? 
                               round((time() - strtotime($solicitud['fecha_inicio_programa'])) / 86400) : 0;

            $validacion = ['valida' => false, 'motivo' => '', 'recomendacion' => 'rechazar'];
            $tipoPrograma = $this->determinarTipoProgramaCorregido($tipoPrograma, $solicitud['nombre_programa']);

            switch ($tipo) {
                case 'certificacion_inicio':
                    if ($diasFormacion >= 30) {
                        $validacion = [
                            'valida' => true, 
                            'motivo' => "Estudiante con {$diasFormacion} días de formación en {$tipoPrograma}", 
                            'recomendacion' => 'aprobar'
                        ];
                    } else {
                        $validacion['motivo'] = "Requiere mínimo 30 días de formación (actual: {$diasFormacion} días)";
                    }
                    break;

                case 'certificacion_parcial':
                    $trimestreMinimo = ($tipoPrograma == 'TECNICO') ? 2 : 3;
                    if ($trimestreActual >= $trimestreMinimo) {
                        $validacion = [
                            'valida' => true, 
                            'motivo' => "{$tipoPrograma} en trimestre {$trimestreActual} con {$porcentajeAvance}% de avance", 
                            'recomendacion' => 'aprobar'
                        ];
                    } else {
                        $validacion['motivo'] = "Requiere mínimo {$trimestreMinimo} trimestres para certificación parcial";
                    }
                    break;

                case 'certificacion_etapa_productiva':
                    if ($trimestreActual >= ($duracionTrimestres - 1) && $bitacorasCompletas > 0) {
                        $validacion = [
                            'valida' => true, 
                            'motivo' => "Etapa productiva con {$bitacorasCompletas}/{$totalBitacoras} bitácoras", 
                            'recomendacion' => 'aprobar'
                        ];
                    } else {
                        $validacion['motivo'] = "No ha iniciado etapa productiva o faltan bitácoras";
                    }
                    break;

                case 'certificacion_tecnica_completa':
                case 'certificacion_tecnologica_completa':
                    if ($trimestreActual >= $duracionTrimestres && 
                        ($totalBitacoras == 0 || $bitacorasCompletas == $totalBitacoras)) {
                        $validacion = [
                            'valida' => true, 
                            'motivo' => "Programa {$tipoPrograma} completado exitosamente", 
                            'recomendacion' => 'aprobar'
                        ];
                    } else {
                        $validacion['motivo'] = "Programa incompleto o bitácoras pendientes";
                    }
                    break;

                case 'certificacion_curso_inicio':
                case 'certificacion_curso_intermedio':
                case 'certificacion_curso_completo':
                    $avanceMinimo = ($tipo == 'certificacion_curso_inicio') ? 10 : 
                                   (($tipo == 'certificacion_curso_intermedio') ? 50 : 100);
                    
                    if ($porcentajeAvance >= $avanceMinimo) {
                        $validacion = [
                            'valida' => true, 
                            'motivo' => "Curso con {$porcentajeAvance}% de avance", 
                            'recomendacion' => 'aprobar'
                        ];
                    } else {
                        $validacion['motivo'] = "Requiere {$avanceMinimo}% de avance mínimo";
                    }
                    break;

                case 'constancia_estudiante_activo':
                    if ($diasFormacion >= 1) {
                        $validacion = [
                            'valida' => true, 
                            'motivo' => "Estudiante activo en formación", 
                            'recomendacion' => 'aprobar'
                        ];
                    }
                    break;
            }

            $validacion['analisis'] = [
                'tipo_programa' => $tipoPrograma,
                'trimestre_actual' => $trimestreActual,
                'duracion_total' => $duracionTrimestres,
                'porcentaje_avance' => $porcentajeAvance,
                'bitacoras_completas' => $bitacorasCompletas,
                'total_bitacoras' => $totalBitacoras,
                'dias_formacion' => $diasFormacion,
                'programa' => $solicitud['nombre_programa']
            ];

            return $validacion;

        } catch (Exception $e) {
            error_log("Error en validación automática con programa: " . $e->getMessage());
            return ['valida' => false, 'motivo' => 'Error en validación: ' . $e->getMessage()];
        }
    }
    
    private function determinarTipoProgramaCorregido($tipfic, $nombrePrograma = '') {
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
        
        switch ($tipfic) {
            case 'TECNICO': return 'TECNICO';
            case 'TECNOLOGO': return 'TECNOLOGO';  
            case 'CURSO': return 'CURSO';
            case 'COMPLEMENTARIA': return 'COMPLEMENTARIA';
            case 'ESPECIALIZACION': return 'ESPECIALIZACION';
            default: 
                if (strpos($nombreUpper, 'ANALISIS') !== false || strpos($nombreUpper, 'DESARROLLO') !== false) {
                    return 'TECNOLOGO';
                }
                return 'PROGRAMA';
        }
    }
    
    public function getDetalleSolicitudConPrograma($idsolicitud) {
        try {
            $sql = "SELECT 
                        s.id as idsolicitud,
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
                        u.nomusu, 
                        u.ndocusu, 
                        COALESCE(u.emausu, 'N/A') as emausu, 
                        COALESCE(u.telcan, 'N/A') as telcan
                    FROM solicicerti s
                    INNER JOIN usuario u ON s.idusu = u.idusu
                    WHERE s.id = :idsolicitud";
            
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(':idsolicitud', $idsolicitud, PDO::PARAM_INT);
            $stmt->execute();
            $solicitud = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$solicitud) {
                return null;
            }
            
            if ($solicitud['idfic']) {
                try {
                    $sqlPrograma = "SELECT 
                                        f.nomfic, 
                                        f.finific as fecha_inicio_programa,
                                        f.ffinfic as fecha_fin_estimada,
                                        p.nompro,
                                        p.tippro
                                    FROM ficha f
                                    LEFT JOIN programa p ON f.codpro = p.codpro
                                    WHERE f.idfic = :idfic";
                    
                    $stmtProg = $conexion->prepare($sqlPrograma);
                    $stmtProg->bindParam(':idfic', $solicitud['idfic']);
                    $stmtProg->execute();
                    $programa = $stmtProg->fetch(PDO::FETCH_ASSOC);
                    
                    if ($programa) {
                        $solicitud['nombre_programa'] = $programa['nomfic'] ?: $programa['nompro'] ?: 'Sin nombre';
                        $solicitud['fecha_inicio_programa'] = $programa['fecha_inicio_programa'];
                        $solicitud['codigo_ficha'] = $solicitud['idfic'];
                        
                        $nombreUpper = strtoupper($programa['nompro'] ?: $programa['nomfic'] ?: '');
                        if (strpos($nombreUpper, 'TECNOLOGO') !== false || strpos($nombreUpper, 'ANALISIS') !== false) {
                            $solicitud['tipo_programa'] = 'TECNOLOGO';
                            $solicitud['duracion_trimestres'] = 8;
                            $solicitud['duracion_meses'] = 24;
                        } elseif (strpos($nombreUpper, 'TECNICO') !== false) {
                            $solicitud['tipo_programa'] = 'TECNICO';
                            $solicitud['duracion_trimestres'] = 6;
                            $solicitud['duracion_meses'] = 18;
                        } elseif (strpos($nombreUpper, 'CURSO') !== false || strpos($nombreUpper, 'COMPLEMENTARIA') !== false) {
                            $solicitud['tipo_programa'] = 'CURSO';
                            $solicitud['duracion_trimestres'] = 2;
                            $solicitud['duracion_meses'] = 6;
                        } else {
                            $solicitud['tipo_programa'] = 'PROGRAMA';
                            $solicitud['duracion_trimestres'] = 4;
                            $solicitud['duracion_meses'] = 12;
                        }
                        
                        if ($programa['fecha_inicio_programa']) {
                            $fechaInicio = strtotime($programa['fecha_inicio_programa']);
                            $ahora = time();
                            $mesesCursados = max(0, floor(($ahora - $fechaInicio) / (30 * 24 * 3600)));
                            $solicitud['porcentaje_avance'] = min(100, round(($mesesCursados * 100.0) / $solicitud['duracion_meses'], 1));
                            $solicitud['trimestre_actual'] = min($solicitud['duracion_trimestres'], 
                                                                floor($mesesCursados / ($solicitud['duracion_meses'] / $solicitud['duracion_trimestres'])) + 1);
                        } else {
                            $solicitud['porcentaje_avance'] = 0;
                            $solicitud['trimestre_actual'] = 0;
                        }
                    } else {
                        $solicitud['nombre_programa'] = 'Sin programa asignado';
                        $solicitud['tipo_programa'] = 'N/A';
                        $solicitud['codigo_ficha'] = $solicitud['idfic'];
                        $solicitud['fecha_inicio_programa'] = null;
                        $solicitud['duracion_trimestres'] = 0;
                        $solicitud['duracion_meses'] = 0;
                        $solicitud['porcentaje_avance'] = 0;
                        $solicitud['trimestre_actual'] = 0;
                    }
                } catch (Exception $eProg) {
                    error_log("Error al obtener datos del programa: " . $eProg->getMessage());
                    $solicitud['nombre_programa'] = 'Error al cargar programa';
                    $solicitud['tipo_programa'] = 'N/A';
                    $solicitud['codigo_ficha'] = $solicitud['idfic'];
                    $solicitud['fecha_inicio_programa'] = null;
                    $solicitud['duracion_trimestres'] = 0;
                    $solicitud['duracion_meses'] = 0;
                    $solicitud['porcentaje_avance'] = 0;
                    $solicitud['trimestre_actual'] = 0;
                }
            } else {
                $solicitud['nombre_programa'] = 'Sin ficha asignada';
                $solicitud['tipo_programa'] = 'N/A';
                $solicitud['codigo_ficha'] = 'N/A';
                $solicitud['fecha_inicio_programa'] = null;
                $solicitud['duracion_trimestres'] = 0;
                $solicitud['duracion_meses'] = 0;
                $solicitud['porcentaje_avance'] = 0;
                $solicitud['trimestre_actual'] = 0;
            }
            
            try {
                $sqlBitacoras = "SELECT 
                                    COUNT(*) as total,
                                    SUM(CASE WHEN estado = 'Aprobado' THEN 1 ELSE 0 END) as completas
                                FROM bitacora 
                                WHERE idaprendiz = :idusu";
                
                $stmtBit = $conexion->prepare($sqlBitacoras);
                $stmtBit->bindParam(':idusu', $solicitud['idusu']);
                $stmtBit->execute();
                $bitacoras = $stmtBit->fetch(PDO::FETCH_ASSOC);
                
                if ($bitacoras) {
                    $solicitud['total_bitacoras'] = $bitacoras['total'] ?: 0;
                    $solicitud['bitacoras_completas'] = $bitacoras['completas'] ?: 0;
                } else {
                    $solicitud['total_bitacoras'] = 0;
                    $solicitud['bitacoras_completas'] = 0;
                }
                
            } catch (Exception $eBit) {
                error_log("Error al obtener bitácoras: " . $eBit->getMessage());
                $solicitud['total_bitacoras'] = 0;
                $solicitud['bitacoras_completas'] = 0;
            }
            
            return $solicitud;
            
        } catch (Exception $e) {
            error_log("Error en getDetalleSolicitudConPrograma: " . $e->getMessage());
            return null;
        }
    }

    public function procesarSolicitudesMasivas($solicitudes, $accion, $observacionesGenerales = '') {
        try {
            $resultados = [];
            $exitosos = 0;
            $errores = 0;

            foreach ($solicitudes as $idsolicitud) {
                $resultado = $this->procesarSolicitudConIA($idsolicitud, $accion, $observacionesGenerales);
                
                if ($resultado['success']) {
                    $exitosos++;
                } else {
                    $errores++;
                }
                
                $resultados[] = [
                    'idsolicitud' => $idsolicitud,
                    'resultado' => $resultado
                ];
            }

            return [
                'success' => true,
                'total' => count($solicitudes),
                'exitosos' => $exitosos,
                'errores' => $errores,
                'detalles' => $resultados
            ];

        } catch (Exception $e) {
            error_log("Error en procesamiento masivo: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error en procesamiento masivo: ' . $e->getMessage()
            ];
        }
    }

    public function procesarSolicitudConIA($idsolicitud, $accion, $observacionesManual = '') {
        try {
            $validacion = $this->validarSolicitudAutomaticaConPrograma($idsolicitud);
            $solicitud = $this->getDetalleSolicitudConPrograma($idsolicitud);
            
            if (!$solicitud) {
                return ['success' => false, 'message' => 'Solicitud no encontrada'];
            }

            $observaciones = '';
            $nuevoEstado = '';

            if ($accion === 'auto_aprobar' && $validacion['valida'] && $validacion['recomendacion'] === 'aprobar') {
                $nuevoEstado = 'aprobada_coordinacion';
                $observaciones = "APROBACIÓN AUTOMÁTICA - PROGRAMA: {$solicitud['nombre_programa']} ({$solicitud['tipo_programa']})\n";
                $observaciones .= "ANÁLISIS: {$validacion['motivo']}\n";
                
                if (!empty($observacionesManual)) {
                    $observaciones .= "OBSERVACIONES DEL COORDINADOR: " . $observacionesManual;
                }

            } elseif ($accion === 'auto_rechazar' && (!$validacion['valida'] || $validacion['recomendacion'] === 'rechazar')) {
                $nuevoEstado = 'rechazada';
                $observaciones = "RECHAZO AUTOMÁTICO - PROGRAMA: {$solicitud['nombre_programa']} ({$solicitud['tipo_programa']})\n";
                $observaciones .= "MOTIVO: {$validacion['motivo']}\n";
                
                if (!empty($observacionesManual)) {
                    $observaciones .= "OBSERVACIONES DEL COORDINADOR: " . $observacionesManual;
                }

            } elseif ($accion === 'aprobar_manual') {
                $nuevoEstado = 'aprobada_coordinacion';
                $observaciones = "APROBACIÓN MANUAL - PROGRAMA: {$solicitud['nombre_programa']}\n";
                $observaciones .= (!empty($observacionesManual) ? $observacionesManual : 'Aprobado por decisión del coordinador');

            } elseif ($accion === 'rechazar_manual') {
                $nuevoEstado = 'rechazada';
                $observaciones = "RECHAZO MANUAL - PROGRAMA: {$solicitud['nombre_programa']}\n";
                $observaciones .= (!empty($observacionesManual) ? $observacionesManual : 'Rechazado por decisión del coordinador');
                
            } else {
                return ['success' => false, 'message' => 'Acción no válida o requisitos no cumplidos'];
            }

            $resultado = $this->actualizarEstadoSolicitudCorregido($idsolicitud, $nuevoEstado, $observaciones);
            
            return [
                'success' => $resultado,
                'message' => $resultado ? 'Solicitud procesada correctamente' : 'Error al procesar solicitud',
                'tipo_proceso' => $accion,
                'nuevo_estado' => $nuevoEstado
            ];

        } catch (Exception $e) {
            error_log("Error al procesar solicitud con IA: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error del sistema: ' . $e->getMessage()];
        }
    }

    public function getEstadisticasPorPrograma() {
        try {
            $sql = "SELECT 
                        f.nomfic as nombre_programa,
                        pf.tipfic as tipo_programa,
                        f.idfic as codigo_ficha,
                        COUNT(*) as total_solicitudes,
                        SUM(CASE WHEN s.estado = 'pendiente' THEN 1 ELSE 0 END) as pendientes,
                        SUM(CASE WHEN s.estado = 'aprobada_coordinacion' THEN 1 ELSE 0 END) as aprobadas,
                        SUM(CASE WHEN s.estado = 'completada' THEN 1 ELSE 0 END) as completadas,
                        SUM(CASE WHEN s.estado = 'rechazada' THEN 1 ELSE 0 END) as rechazadas,
                        AVG(CASE WHEN s.fecha_respuesta_coordinacion IS NOT NULL 
                            THEN TIMESTAMPDIFF(HOUR, s.fecha_solicitud, s.fecha_respuesta_coordinacion) 
                            ELSE NULL END) as tiempo_promedio_respuesta_horas
                    FROM solicicerti s
                    LEFT JOIN ficha f ON s.idfic = f.idfic
                    LEFT JOIN programa pf ON f.codpro = pf.codpro
                    GROUP BY f.idfic, f.nomfic, pf.tipfic
                    ORDER BY total_solicitudes DESC";
            
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $stmt = $conexion->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Error al obtener estadísticas por programa: " . $e->getMessage());
            return [];
        }
    }

    public function getSolicitudesUrgentes() {
        try {
            $sql = "SELECT 
                        s.id as idsolicitud,
                        s.tipo_certificacion,
                        s.fecha_solicitud,
                        u.nomusu,
                        u.ndocusu,
                        f.nomfic as nombre_programa,
                        TIMESTAMPDIFF(HOUR, s.fecha_solicitud, NOW()) as horas_pendiente,
                        CASE 
                            WHEN s.tipo_certificacion LIKE '%etapa_productiva%' THEN 'alta'
                            WHEN s.tipo_certificacion LIKE '%completa%' THEN 'alta'
                            WHEN TIMESTAMPDIFF(HOUR, s.fecha_solicitud, NOW()) > 72 THEN 'alta'
                            WHEN TIMESTAMPDIFF(HOUR, s.fecha_solicitud, NOW()) > 48 THEN 'media'
                            ELSE 'baja'
                        END as prioridad
                    FROM solicicerti s
                    INNER JOIN usuario u ON s.idusu = u.idusu
                    LEFT JOIN ficha f ON s.idfic = f.idfic
                    WHERE s.estado = 'pendiente'
                    ORDER BY 
                        CASE prioridad 
                            WHEN 'alta' THEN 1 
                            WHEN 'media' THEN 2 
                            ELSE 3 
                        END ASC,
                        s.fecha_solicitud ASC";
            
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $stmt = $conexion->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Error al obtener solicitudes urgentes: " . $e->getMessage());
            return [];
        }
    }

    // Estadísticas ajustadas considerando respuesta_coordinacion y motivo para 'automaticas'
    public function getEstadisticasSolicitudes() {
        try {
            $sql = "SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN estado = 'pendiente' THEN 1 ELSE 0 END) as pendientes,
                        SUM(CASE WHEN estado = 'aprobada_coordinacion' THEN 1 ELSE 0 END) as aprobadas,
                        SUM(CASE WHEN estado = 'completada' THEN 1 ELSE 0 END) as completadas,
                        SUM(CASE WHEN estado = 'rechazada' THEN 1 ELSE 0 END) as rechazadas,
                        SUM(CASE 
                            WHEN respuesta_coordinacion LIKE '%Sistema Automático%' 
                              OR respuesta_coordinacion LIKE '%automát%' 
                              OR motivo LIKE '%Sistema Automático%' 
                            THEN 1 
                            ELSE 0 
                        END) as automaticas
                    FROM solicicerti";
            
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $stmt = $conexion->prepare($sql);
            $stmt->execute();
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return [
                'total' => (int)($resultado['total'] ?? 0),
                'pendientes' => (int)($resultado['pendientes'] ?? 0),
                'aprobadas' => (int)($resultado['aprobadas'] ?? 0),
                'completadas' => (int)($resultado['completadas'] ?? 0),
                'rechazadas' => (int)($resultado['rechazadas'] ?? 0),
                'automaticas' => (int)($resultado['automaticas'] ?? 0)
            ];
            
        } catch (Exception $e) {
            error_log("Error al obtener estadísticas: " . $e->getMessage());
            return [
                'total' => 0,
                'pendientes' => 0,
                'aprobadas' => 0,
                'completadas' => 0,
                'rechazadas' => 0,
                'automaticas' => 0
            ];
        }
    }
    
    // Método estricto protegidos por idsolicitud y estado='pendiente'
    public function actualizarEstadoSolicitudCorregido($idsolicitud, $nuevoEstado, $observaciones = '') {
        try {
            $sql = "UPDATE solicicerti SET 
                        estado = :estado,
                        respuesta_coordinacion = :observaciones,
                        fecha_respuesta_coordinacion = NOW()
                    WHERE id = :idsolicitud AND estado = 'pendiente'";
            
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            
            if (!$conexion) {
                return false;
            }
            
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(':estado', $nuevoEstado, PDO::PARAM_STR);
            $stmt->bindParam(':observaciones', $observaciones, PDO::PARAM_STR);
            $stmt->bindParam(':idsolicitud', $idsolicitud, PDO::PARAM_INT);
            
            $resultado = $stmt->execute();
            $filasAfectadas = $stmt->rowCount();
            
            return ($resultado && $filasAfectadas > 0);
            
        } catch (PDOException $e) {
            error_log("Error PDO al actualizar solicitud: " . $e->getMessage());
            return false;
        } catch (Exception $e) {
            error_log("Error general al actualizar solicitud: " . $e->getMessage());
            return false;
        }
    }

    public function registrarCertificacion($datosRequisitos) {
        try {
            $sql = "INSERT INTO certificaciones (
                        idaprendiz, 
                        nombre_certificacion, 
                        descripcion, 
                        fecha_obtencion, 
                        entidad_emisora, 
                        estado, 
                        tipo_certificacion
                    ) VALUES (
                        :idaprendiz, 
                        :nombre_certificacion, 
                        :descripcion, 
                        NOW(), 
                        :entidad_emisora, 
                        'activo', 
                        :tipo_certificacion
                    )";
            
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $stmt = $conexion->prepare($sql);
            
            $stmt->bindParam(':idaprendiz', $datosRequisitos['idaprendiz']);
            $stmt->bindParam(':nombre_certificacion', $datosRequisitos['nombre_certificacion']);
            $stmt->bindParam(':descripcion', $datosRequisitos['descripcion']);
            $stmt->bindParam(':entidad_emisora', $datosRequisitos['entidad_emisora']);
            $stmt->bindParam(':tipo_certificacion', $datosRequisitos['tipo_certificacion']);
            
            return $stmt->execute();
            
        } catch (Exception $e) {
            error_log("Error al registrar certificación: " . $e->getMessage());
            return false;
        }
    }

    public function verificarRequisitosEstudianteConPrograma($idusu, $tipoCertificacion, $idfic) {
        try {
            $sqlPrograma = "SELECT 
                                u.*, f.nomfic, f.idfic, pf.tipfic,
                                f.finific as fecini,
                                pf.durtri as duracion_trimestres,
                                pf.durmeses as duracion_meses
                           FROM usuario u 
                           LEFT JOIN ficha f ON f.idfic = :idfic
                           LEFT JOIN programa pf ON f.codpro = pf.codpro
                           WHERE u.idusu = :idusu";
            
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $stmt = $conexion->prepare($sqlPrograma);
            $stmt->bindParam(':idusu', $idusu);
            $stmt->bindParam(':idfic', $idfic);
            $stmt->execute();
            $datosPrograma = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$datosPrograma || !$datosPrograma['idfic']) {
                return ['cumple' => false, 'motivo' => 'Estudiante no inscrito en este programa'];
            }
            
            if (in_array($tipoCertificacion, ['certificacion_etapa_productiva', 'certificacion_tecnica_completa', 'certificacion_tecnologica_completa'])) {
                $sqlBitacoras = "SELECT COUNT(*) as total,
                                SUM(CASE WHEN estado = 'Aprobado' THEN 1 ELSE 0 END) as completadas
                                FROM bitacora 
                                WHERE idaprendiz = :idusu";
                
                $stmt = $conexion->prepare($sqlBitacoras);
                $stmt->bindParam(':idusu', $idusu);
                $stmt->execute();
                $bitacoras = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($bitacoras['total'] > 0 && $bitacoras['completadas'] < $bitacoras['total']) {
                    return ['cumple' => false, 'motivo' => 'Tiene bitácoras pendientes en este programa'];
                }
            }
            
            return [
                'cumple' => true,
                'motivo' => 'Cumple con todos los requisitos para este programa',
                'detalles' => [
                    'programa' => $datosPrograma['nomfic'],
                    'tipo_programa' => $datosPrograma['tipfic'],
                    'ficha' => $datosPrograma['idfic']
                ]
            ];
            
        } catch (Exception $e) {
            error_log("Error al verificar requisitos con programa: " . $e->getMessage());
            return ['cumple' => false, 'motivo' => 'Error en la verificación: ' . $e->getMessage()];
        }
    }
    
    public function getTiposCertificacionSolicitudes() {
        try {
            $sql = "SELECT DISTINCT tipo_certificacion FROM solicicerti ORDER BY tipo_certificacion";
            
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $stmt = $conexion->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Error al obtener tipos de certificación: " . $e->getMessage());
            return [];
        }
    }

    public function getHistorialRespuestas($coordinadorId = null) {
        try {
            $sql = "SELECT 
                        s.id as idsolicitud,
                        s.tipo_certificacion,
                        s.estado,
                        s.fecha_solicitud,
                        s.fecha_respuesta_coordinacion as fecha_respuesta,
                        s.respuesta_coordinacion as respuesta,
                        u.nomusu,
                        u.ndocusu,
                        COALESCE(f.nomfic, 'Sin programa asignado') as nombre_programa,
                        CASE 
                            WHEN s.respuesta_coordinacion LIKE '%APROBACIÓN AUTOMÁTICA%' THEN 'automatico'
                            WHEN s.respuesta_coordinacion LIKE '%RECHAZO AUTOMÁTICO%' THEN 'automatico'
                            ELSE 'manual'
                        END as tipo_proceso
                    FROM solicicerti s
                    INNER JOIN usuario u ON s.idusu = u.idusu
                    LEFT JOIN ficha f ON s.idfic = f.idfic
                    WHERE s.estado != 'pendiente'
                    ORDER BY s.fecha_respuesta_coordinacion DESC LIMIT 50";
            
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $stmt = $conexion->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Error al obtener historial: " . $e->getMessage());
            return [];
        }
    }
    
    public function getEstadisticasSistemaAutomatico() {
        try {
            $sql = "SELECT 
                        COUNT(*) as total_solicitudes,
                        SUM(CASE WHEN respuesta_coordinacion LIKE '%APROBACIÓN AUTOMÁTICA%' THEN 1 ELSE 0 END) as aprobaciones_automaticas,
                        SUM(CASE WHEN respuesta_coordinacion LIKE '%RECHAZO AUTOMÁTICO%' THEN 1 ELSE 0 END) as rechazos_automaticos,
                        SUM(CASE WHEN respuesta_coordinacion LIKE '%MANUAL%' THEN 1 ELSE 0 END) as procesos_manuales,
                        ROUND(
                            (SUM(CASE WHEN respuesta_coordinacion LIKE '%APROBACIÓN AUTOMÁTICA%' OR respuesta_coordinacion LIKE '%RECHAZO AUTOMÁTICO%' THEN 1 ELSE 0 END) * 100.0 / COUNT(*)), 
                            1
                        ) as porcentaje_automatizacion
                    FROM solicicerti 
                    WHERE estado != 'pendiente' 
                    AND fecha_respuesta_coordinacion >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
            
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $stmt = $conexion->prepare($sql);
            $stmt->execute();
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return [
                'total_solicitudes' => (int)($resultado['total_solicitudes'] ?? 0),
                'aprobaciones_automaticas' => (int)($resultado['aprobaciones_automaticas'] ?? 0),
                'rechazos_automaticos' => (int)($resultado['rechazos_automaticos'] ?? 0),
                'procesos_manuales' => (int)($resultado['procesos_manuales'] ?? 0),
                'porcentaje_automatizacion' => (float)($resultado['porcentaje_automatizacion'] ?? 0)
            ];
            
        } catch (Exception $e) {
            error_log("Error al obtener estadísticas automático: " . $e->getMessage());
            return [
                'total_solicitudes' => 0,
                'aprobaciones_automaticas' => 0,
                'rechazos_automaticos' => 0,
                'procesos_manuales' => 0,
                'porcentaje_automatizacion' => 0
            ];
        }
    }
    
    public function exportarSolicitudesCSV() {
        $solicitudes = $this->getAllSolicitudesDetalladas();
        
        if (empty($solicitudes)) {
            return false;
        }
        
        $filename = 'solicitudes_certificacion_' . date('Y-m-d_H-i-s') . '.csv';
        $filepath = 'exports/' . $filename;
        
        if (!is_dir('exports')) {
            mkdir('exports', 0755, true);
        }
        
        $handle = fopen($filepath, 'w');
        
        fputcsv($handle, [
            'ID Solicitud',
            'Estudiante',
            'Documento',
            'Programa',
            'Tipo Programa',
            'Trimestre Actual',
            'Tipo Certificación',
            'Estado',
            'Fecha Solicitud',
            'Fecha Respuesta',
            'Motivo Sistema',
            'Observaciones Coordinador',
            'Bitácoras Completas',
            'Total Bitácoras'
        ]);
        
        foreach ($solicitudes as $solicitud) {
            fputcsv($handle, [
                $solicitud['idsolicitud'],
                $solicitud['nomusu'],
                $solicitud['ndocusu'],
                $solicitud['nombre_programa'],
                $solicitud['tipo_programa'],
                $solicitud['trimestre_actual'],
                $solicitud['tipo_certificacion'],
                $solicitud['estado'],
                $solicitud['fecha_solicitud'],
                $solicitud['fecha_respuesta_coordinacion'] ?? 'Pendiente',
                $solicitud['motivo'],
                $solicitud['respuesta_coordinacion'] ?? '',
                $solicitud['bitacoras_completas'],
                $solicitud['total_bitacoras']
            ]);
        }
        
        fclose($handle);
        return $filepath;
    }
}