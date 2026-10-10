<?php
require_once 'conexion.php';

class Hormrstri {
    private $conexion;
    private $idfic;

    public function __construct() {
        $modelo = new Conexion();
        $this->conexion = $modelo->get_conexion();
        $this->actualizarRutasPagina();
    }

    /**
     * Sincronizar automáticamente en la tabla pagina de la BD el nuevo nombre de las vistas
     */
    private function actualizarRutasPagina() {
        try {
            $this->conexion->query("UPDATE pagina SET rutpag = 'views/horvrslins.php' WHERE rutpag = 'views/vrsins.php' OR idpag = 1513");
            $this->conexion->query("UPDATE pagina SET rutpag = 'views/horvrstri.php' WHERE rutpag = 'views/vrpt.php' OR idpag = 1548");
        } catch (Exception $e) {
            // Silencioso si ya está actualizado
        }
    }

    public function getIdfic() {
        return $this->idfic;
    }

    public function setIdfic($idfic) {
        $this->idfic = $idfic;
    }

    /**
     * Obtener listado de todas las fichas disponibles en el sistema
     */
    public function getFichas() {
        try {
            $sql = "SELECT f.idfic, f.nomfic, p.codpro, p.nompro, f.finific, f.ffinfic, v.nomval AS jornada
                    FROM ficha f
                    LEFT JOIN programa p ON f.codpro = p.codpro
                    LEFT JOIN valor v ON f.jornada = v.idval
                    WHERE f.idfic > 1000 OR f.idfic REGEXP '^[0-9]+'
                    ORDER BY f.idfic DESC";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Hormrstri::getFichas: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Traer información completa de la ficha y su programa (encabezado oficial)
     */
    public function getFichaPrograma($idfic = null) {
        $fic = $idfic ?? $this->idfic;
        if (!$fic) return [];

        try {
            $sql = "SELECT f.idfic, f.nomfic, p.codpro, p.nompro, p.verpro, 
                           p.horlpro, p.horppro, p.crelpro, p.creppro, p.redcon,
                           a.nomare, v.nomval
                    FROM ficha f
                    LEFT JOIN programa p ON f.codpro = p.codpro
                    LEFT JOIN area a ON p.idare = a.idare
                    LEFT JOIN valor v ON f.jornada = v.idval
                    WHERE f.idfic = :idfic";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindValue(':idfic', $fic);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Hormrstri::getFichaPrograma: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Traer fechas de inicio y fin de la ficha
     */
    public function getFechasFicha($idfic = null) {
        $fic = $idfic ?? $this->idfic;
        if (!$fic) return null;

        try {
            $sql = "SELECT finific, ffinfic 
                    FROM ficha 
                    WHERE idfic = :idfic";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindValue(':idfic', $fic);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Hormrstri::getFechasFicha: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Calcular los trimestres de la ficha con sus rangos de fechas
     */
    public function getTrimestresCalculados($idfic = null) {
        $fechas = $this->getFechasFicha($idfic);
        $trimestres = [];

        $mesesES = [
            1 => 'Ene', 2 => 'Feb', 3 => 'Mar', 4 => 'Abr',
            5 => 'May', 6 => 'Jun', 7 => 'Jul', 8 => 'Ago',
            9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dic'
        ];

        if (!empty($fechas) && !empty($fechas['finific']) && !empty($fechas['ffinfic']) && $fechas['finific'] != '0000-00-00') {
            try {
                $inicio = new DateTime($fechas['finific']);
                $fin = new DateTime($fechas['ffinfic']);
                $diff = $inicio->diff($fin);
                $meses = ($diff->y * 12) + $diff->m;
                $numTrimestres = max(1, ceil($meses / 3));

                for ($i = 1; $i <= $numTrimestres; $i++) {
                    $inicioTrim = clone $inicio;
                    $inicioTrim->modify('+' . (($i - 1) * 3) . ' months');
                    $finTrim = clone $inicioTrim;
                    $finTrim->modify('+2 months')->modify('last day of this month');

                    $mesInicio = $mesesES[(int)$inicioTrim->format('n')] . ' ' . $inicioTrim->format('Y');
                    $mesFin    = $mesesES[(int)$finTrim->format('n')] . ' ' . $finTrim->format('Y');

                    $trimestres[] = [
                        'numero' => $i,
                        'nombre' => "Trimestre $i ($mesInicio - $mesFin)",
                        'fch_inicio' => $inicioTrim->format('Y-m-d'),
                        'fch_fin' => $finTrim->format('Y-m-d')
                    ];
                }
            } catch (Exception $e) {
                // Fallback a 6 trimestres estándar
                for ($i = 1; $i <= 6; $i++) {
                    $trimestres[] = [
                        'numero' => $i,
                        'nombre' => "Trimestre $i",
                        'fch_inicio' => null,
                        'fch_fin' => null
                    ];
                }
            }
        } else {
            // Trimestres estándar por defecto si la ficha no tiene fechas registradas
            for ($i = 1; $i <= 6; $i++) {
                $trimestres[] = [
                    'numero' => $i,
                    'nombre' => "Trimestre $i",
                    'fch_inicio' => null,
                    'fch_fin' => null
                ];
            }
        }

        return $trimestres;
    }

    /**
     * Traer competencias, resultados e instructores de la ficha y trimestre
     * Regla estricta: NO omitir resultados ni competencias
     */
    public function getCompetenciasResultados($trimestre = null) {
        if (!$this->idfic) return [];

        try {
            // 1. Obtener los rangos de fecha del trimestre si fue seleccionado
            $inicioTrim = null;
            $finTrim = null;
            if (!empty($trimestre)) {
                $trimestres = $this->getTrimestresCalculados($this->idfic);
                foreach ($trimestres as $t) {
                    if ($t['numero'] == $trimestre) {
                        $inicioTrim = $t['fch_inicio'];
                        $finTrim = $t['fch_fin'];
                        break;
                    }
                }
            }

            // 2. Traer las competencias y resultados asociados en agenda (con el instructor asignado)
            $sqlAgenda = "SELECT DISTINCT 
                            c.idcom, c.descom, 
                            r.idres, r.nomres,
                            a.idage, a.fas, a.actpro, a.horcom,
                            COALESCE(u.nomusu, u2.nomusu, 'Sin asignar') AS instructor,
                            COALESCE(u.ndocusu, u2.ndocusu, '') AS doc_instructor,
                            COALESCE(a.fchinc, a.fecini) AS fch_inicio,
                            COALESCE(a.fchfnl, a.fecfin) AS fch_fin
                          FROM agenda a
                          INNER JOIN ficha f ON a.idfic = f.idfic
                          LEFT JOIN competencia c ON a.idcom = c.idcom
                          LEFT JOIN resultado r ON a.idres = r.idres
                          LEFT JOIN usuario u ON a.idins = u.idusu
                          LEFT JOIN usuario u2 ON a.idusu = u2.idusu
                          WHERE a.idfic = :idfic
                            AND c.idcom IS NOT NULL";

            if ($inicioTrim && $finTrim) {
                $sqlAgenda .= " AND (
                                  (a.fchinc <= :fin1 AND a.fchfnl >= :ini1)
                                  OR (a.fecini <= :fin2 AND a.fecfin >= :ini2)
                                  OR (a.fchinc BETWEEN :ini3 AND :fin3)
                                  OR (a.fecini BETWEEN :ini4 AND :fin4)
                                )";
            }

            $sqlAgenda .= " ORDER BY c.idcom, r.idres";

            $stmt = $this->conexion->prepare($sqlAgenda);
            $stmt->bindValue(':idfic', $this->idfic);
            if ($inicioTrim && $finTrim) {
                $stmt->bindValue(':ini1', $inicioTrim);
                $stmt->bindValue(':fin1', $finTrim);
                $stmt->bindValue(':ini2', $inicioTrim);
                $stmt->bindValue(':fin2', $finTrim);
                $stmt->bindValue(':ini3', $inicioTrim);
                $stmt->bindValue(':fin3', $finTrim);
                $stmt->bindValue(':ini4', $inicioTrim);
                $stmt->bindValue(':fin4', $finTrim);
            }
            $stmt->execute();
            $datosAgenda = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Si hay datos en agenda para ese filtro, retornarlos
            if (!empty($datosAgenda)) {
                return $datosAgenda;
            }

            // 3. Fallback institucional: si no hay agenda registrada para este trimestre específico,
            // no omitir ninguna competencia ni resultado del programa de la ficha
            $sqlPrograma = "SELECT DISTINCT 
                                c.idcom, c.descom, 
                                r.idres, r.nomres,
                                NULL as idage,
                                'LECTIVA' as fas,
                                'Actividad de proyecto' as actpro,
                                c.horcom,
                                'Por asignar' as instructor,
                                '' as doc_instructor,
                                f.finific as fch_inicio,
                                f.ffinfic as fch_fin
                            FROM ficha f
                            INNER JOIN programa p ON f.codpro = p.codpro
                            INNER JOIN proxcom pc ON p.codpro = pc.codpro
                            INNER JOIN competencia c ON pc.idcom = c.idcom
                            INNER JOIN resultado r ON c.idcom = r.idcom
                            WHERE f.idfic = :idfic
                            ORDER BY c.idcom, r.idres";
            $stmtProg = $this->conexion->prepare($sqlPrograma);
            $stmtProg->bindValue(':idfic', $this->idfic);
            $stmtProg->execute();
            return $stmtProg->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Error en Hormrstri::getCompetenciasResultados: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Métricas estadísticas para el dashboard superior
     */
    public function getEstadisticas($idfic = null, $trimestre = null) {
        $fic = $idfic ?? $this->idfic;
        $kpi = [
            'total_competencias' => 0,
            'total_resultados' => 0,
            'total_instructores' => 0,
            'total_horas' => 0,
            'total_trimestres' => 0,
            'trimestre_actual' => $trimestre ?: 'Todos'
        ];

        if (!$fic) {
            return $kpi;
        }

        try {
            $trimestres = $this->getTrimestresCalculados($fic);
            $kpi['total_trimestres'] = count($trimestres);

            $registros = $this->getCompetenciasResultados($trimestre);
            $comps = [];
            $res = [];
            $insts = [];
            $horas = 0;

            foreach ($registros as $row) {
                if (!empty($row['idcom'])) $comps[$row['idcom']] = true;
                if (!empty($row['idres'])) $res[$row['idres']] = true;
                if (!empty($row['instructor']) && $row['instructor'] != 'Por asignar' && $row['instructor'] != 'Sin asignar') {
                    $insts[$row['instructor']] = true;
                }
                if (!empty($row['horcom']) && is_numeric($row['horcom'])) {
                    $horas += (int)$row['horcom'];
                }
            }

            $kpi['total_competencias'] = count($comps);
            $kpi['total_resultados'] = count($res);
            $kpi['total_instructores'] = count($insts);
            $kpi['total_horas'] = $horas;

        } catch (Exception $e) {
            error_log("Error en Hormrstri::getEstadisticas: " . $e->getMessage());
        }

        return $kpi;
    }
}
?>
