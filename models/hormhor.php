<?php

class Hormhor
{
    public function __construct() {
        // Configurar zona horaria de Colombia para toda la clase
        date_default_timezone_set('America/Bogota');
    }

    // Atributos
    private $idhor;
    private $idfic;
    private $idaul;
    private $idusu;
    private $iddia;
    private $idare;

    // Nuevos atributos para actividades "Otros"
    private $es_otros;
    private $actividad;
    private $horas_otros;
    private $es_formacion_directa; // 1=otros, 2=formación directa

    // Nuevos atributos para horarios "Transversales"
    private $es_transversal;
    private $nombre_transversal;

    // Categoria de hora (formacion|proyecto|lider|otra). Ver bd/migracion_categoria_hora.sql.
    private $categoria_hora;

    // Nuevos atributos para fechas de lapsos de tiempo
    private $fecha_inicio;
    private $fecha_fin;
    private $fecha_especifica;
    private $horas_mes_actual;

    // Nuevos atributos para la hora de inicio y fin del horario
    private $hinihor;
    private $hfinhor;

    // Métodos GET
    public function getIdhor()
    {
        return $this->idhor;
    }
    public function getIdfic()
    {
        return $this->idfic;
    }
    public function getIdaul()
    {
        return $this->idaul;
    }
    public function getIdusu()
    {
        return $this->idusu;
    }
    public function getIddia()
    {
        return $this->iddia;
    }
    public function getIdare()
    {
        return $this->idare;
    }

    // Nuevos métodos GET para actividades "Otros"
    public function getEsOtros()
    {
        return $this->es_otros;
    }

    public function getActividad()
    {
        return $this->actividad;
    }

    public function getHorasOtros()
    {
        return $this->horas_otros;
    }

    public function getEsFormacionDirecta()
    {
        return $this->es_formacion_directa;
    }

    // Nuevos métodos GET para horarios "Transversales"
    public function getEsTransversal()
    {
        return $this->es_transversal;
    }

    public function getNombreTransversal()
    {
        return $this->nombre_transversal;
    }

    public function getCategoriaHora()
    {
        return $this->categoria_hora;
    }

    /**
     * Deriva categoria_hora con el mismo mapeo del backfill historico:
     * actividades especiales o transversales -> 'otra', resto -> 'formacion'.
     * 'proyecto' y 'lider' quedan disponibles para clasificacion manual futura.
     */
    public function derivarCategoriaHora($es_otros = null, $es_transversal = null)
    {
        if ($es_otros === null) {
            $es_otros = $this->es_otros;
        }
        if ($es_transversal === null) {
            $es_transversal = $this->es_transversal;
        }
        return (!empty($es_otros) || !empty($es_transversal)) ? 'otra' : 'formacion';
    }

    // Nuevos métodos GET para fechas de lapsos de tiempo
    public function getFechaInicio()
    {
        return $this->fecha_inicio;
    }

    public function getFechaFin()
    {
        return $this->fecha_fin;
    }

    public function getFechaEspecifica()
    {
        return $this->fecha_especifica;
    }

    public function getHorasMesActual()
    {
        return $this->horas_mes_actual;
    }

    public function getHinihor()
    {
        return $this->hinihor;
    }

    public function getHfinhor()
    {
        return $this->hfinhor;
    }

    // Métodos SET
    public function setIdhor($idhor)
    {
        $this->idhor = $idhor;
    }
    public function setIdfic($idfic)
    {
        $this->idfic = $idfic;
    }
    public function setIdaul($idaul)
    {
        $this->idaul = $idaul;
    }
    public function setIdusu($idusu)
    {
        $this->idusu = $idusu;
    }
    public function setIddia($iddia)
    {
        $this->iddia = $iddia;
    }
    public function setIdare($idare)
    {
        $this->idare = $idare;
    }

    // Nuevos métodos SET para actividades "Otros"
    public function setEsOtros($es_otros)
    {
        $this->es_otros = $es_otros;
    }

    public function setActividad($actividad)
    {
        $this->actividad = $actividad;
    }

    public function setHorasOtros($horas_otros)
    {
        $this->horas_otros = $horas_otros;
    }

    public function setEsFormacionDirecta($es_formacion_directa)
    {
        $this->es_formacion_directa = $es_formacion_directa;
    }

    // Nuevos métodos SET para horarios "Transversales"
    public function setEsTransversal($es_transversal)
    {
        $this->es_transversal = $es_transversal;
    }

    public function setNombreTransversal($nombre_transversal)
    {
        $this->nombre_transversal = $nombre_transversal;
    }

    public function setCategoriaHora($categoria_hora)
    {
        $this->categoria_hora = $categoria_hora;
    }

    // Nuevos métodos SET para fechas de lapsos de tiempo
    public function setFechaInicio($fecha_inicio)
    {
        $this->fecha_inicio = $fecha_inicio;
    }

    public function setFechaFin($fecha_fin)
    {
        $this->fecha_fin = $fecha_fin;
    }

    public function setFechaEspecifica($fecha_especifica)
    {
        $this->fecha_especifica = $fecha_especifica;
    }

    public function setHorasMesActual($horas_mes_actual)
    {
        $this->horas_mes_actual = $horas_mes_actual;
    }

    public function setHinihor($hinihor)
    {
        $this->hinihor = $hinihor;
    }

    public function setHfinhor($hfinhor)
    {
        $this->hfinhor = $hfinhor;
    }

    /**
     * Obtiene el inicio de la semana actual (lunes) o de una semana específica
     */
    public function getInicioSemana($semanaSelector = null)
    {
        if ($semanaSelector) {
            return $this->getInicioSemanaEspecifica($semanaSelector);
        }

        $zonaColombia = new DateTimeZone('America/Bogota');
        $hoy = new DateTime('now', $zonaColombia);
        $diaSemana = $hoy->format('N');

        if ($diaSemana == 1) {
            $inicioSemana = clone $hoy;
        } else {
            $diasRetroceder = $diaSemana - 1;
            $inicioSemana = clone $hoy;
            $inicioSemana->sub(new DateInterval("P{$diasRetroceder}D"));
        }

        return $inicioSemana->format('Y-m-d');
    }

    /**
     * Obtiene el fin de la semana actual (domingo)
     */
    public function getFinSemana($semanaSelector = null)
    {
        if ($semanaSelector) {
            $inicio = $this->getInicioSemanaEspecifica($semanaSelector);
            $fechaInicio = new DateTime($inicio);
            $fechaInicio->add(new DateInterval('P6D'));
            return $fechaInicio->format('Y-m-d');
        }

        $zonaColombia = new DateTimeZone('America/Bogota');
        $hoy = new DateTime('now', $zonaColombia);
        $diaSemana = $hoy->format('N');

        if ($diaSemana == 7) {
            $finSemana = clone $hoy;
        } else {
            $diasAvanzar = 7 - $diaSemana;
            $finSemana = clone $hoy;
            $finSemana->add(new DateInterval("P{$diasAvanzar}D"));
        }

        return $finSemana->format('Y-m-d');
    }

    /**
     * Obtiene el inicio de una semana específica basada en el selector
     */
    private function getInicioSemanaEspecifica($semanaSelector)
    {
        try {
            if (preg_match('/^(\d{4})-W?(\d{2,3})$/', $semanaSelector, $matches)) {
                $año = (int)$matches[1];
                $semana = (int)$matches[2];

                $zonaColombia = new DateTimeZone('America/Bogota');
                $cuatroEnero = new DateTime("$año-01-04", $zonaColombia);
                $diaSemanaCuatroEnero = (int)$cuatroEnero->format('N');

                $primerDiaSemana1 = clone $cuatroEnero;
                $diasHastaLunes = $diaSemanaCuatroEnero - 1;
                $primerDiaSemana1->sub(new DateInterval("P{$diasHastaLunes}D"));

                $lunesSemana = clone $primerDiaSemana1;
                $lunesSemana->add(new DateInterval('P' . (($semana - 1) * 7) . 'D'));

                return $lunesSemana->format('Y-m-d');
            }
        } catch (Exception $e) {
            error_log("Error en getInicioSemanaEspecifica: " . $e->getMessage());
            throw $e;
        }

        return $this->getInicioSemana();
    }

    // Métodos públicos
    public function getAll($semanaSelector = null)
    {
        $sql = 'SELECT h.idhor, h.idfic, h.idaul, a.nomaul, a.codubi, a.idcen, h.idusu, 
                       u.ndocusu, u.nomusu, u.colfon, u.coltex, h.iddia,
                       h.es_otros, h.actividad, h.horas_otros,
                       h.es_formacion_directa,
                       h.es_transversal, h.nombre_transversal,
                       h.fecha_inicio, h.fecha_fin,
                       h.fecha_especifica, h.hinihor, h.hfinhor
                FROM horario AS h 
                LEFT JOIN usuario AS u ON h.idusu = u.idusu 
                LEFT JOIN aula AS a ON h.idaul = a.idaul 
                WHERE h.idfic = :idfic 
                AND h.iddia = :iddia
                AND h.fecha_especifica IS NOT NULL
                AND h.fecha_especifica BETWEEN :semana_inicio AND :semana_fin';

        $modelo   = new Conexion();
        $conexion = $modelo->get_conexion();
        $result   = $conexion->prepare($sql);

        $idfic = $this->getIdfic();
        $result->bindParam(':idfic', $idfic);

        $iddia = $this->getIddia();
        $result->bindParam(':iddia', $iddia);

        $semanaInicio = $this->getInicioSemana($semanaSelector);
        $semanaFin = $this->getFinSemana($semanaSelector);

        $result->bindParam(':semana_inicio', $semanaInicio);
        $result->bindParam(':semana_fin', $semanaFin);

        $result->execute();
        return $result->fetchAll(PDO::FETCH_ASSOC);
    }

    public function validarHorarioActivoEnSemana($horario, $semanaInicio, $semanaFin)
    {
        try {
            if (empty($horario['fecha_inicio']) || empty($horario['fecha_fin']) || empty($horario['fecha_especifica'])) {
                return false;
            }

            $fechaInicio = new DateTime($horario['fecha_inicio']);
            $fechaFin = new DateTime($horario['fecha_fin']);
            $fechaEspecifica = new DateTime($horario['fecha_especifica']);
            $semanaInicioDT = new DateTime($semanaInicio);
            $semanaFinDT = new DateTime($semanaFin);

            if ($fechaEspecifica < $semanaInicioDT || $fechaEspecifica > $semanaFinDT) {
                return false;
            }

            if ($fechaInicio > $semanaFinDT || $fechaFin < $semanaInicioDT) {
                return false;
            }

            return true;
        } catch (Exception $e) {
            error_log("Error en validarHorarioActivoEnSemana: " . $e->getMessage());
            return false;
        }
    }

    public function getAllValidados($semanaSelector = null)
    {
        $horarios = $this->getAll($semanaSelector);

        if (empty($horarios)) {
            return [];
        }

        $semanaInicio = $this->getInicioSemana($semanaSelector);
        $semanaFin = $this->getFinSemana($semanaSelector);

        $horariosValidados = [];
        foreach ($horarios as $horario) {
            if ($this->validarHorarioActivoEnSemana($horario, $semanaInicio, $semanaFin)) {
                $horariosValidados[] = $horario;
            }
        }

        return $horariosValidados;
    }

    public function getAllUltraEstricto($semanaSelector = null)
    {
        $sql = 'SELECT h.idhor, h.idfic, h.idaul, a.nomaul, a.codubi, a.idcen, h.idusu, 
                       u.ndocusu, u.nomusu, u.colfon, u.coltex, h.iddia,
                       h.es_otros, h.actividad, h.horas_otros,
                       h.es_formacion_directa,
                       h.es_transversal, h.nombre_transversal,
                       h.fecha_inicio, h.fecha_fin,
                       h.fecha_especifica, h.hinihor, h.hfinhor
                FROM horario AS h 
                LEFT JOIN usuario AS u ON h.idusu = u.idusu 
                LEFT JOIN aula AS a ON h.idaul = a.idaul 
                WHERE h.idfic = :idfic 
                AND h.iddia = :iddia
                AND h.fecha_especifica IS NOT NULL
                AND h.fecha_especifica BETWEEN :semana_inicio AND :semana_fin
                AND (
                    (h.fecha_inicio IS NOT NULL AND h.fecha_fin IS NOT NULL) OR
                    (h.fecha_inicio IS NULL AND h.fecha_fin IS NULL)
                )';

        $modelo   = new Conexion();
        $conexion = $modelo->get_conexion();
        $result   = $conexion->prepare($sql);

        $idfic = $this->getIdfic();
        $result->bindParam(':idfic', $idfic);

        $iddia = $this->getIddia();
        $result->bindParam(':iddia', $iddia);

        $semanaInicio = $this->getInicioSemana($semanaSelector);
        $semanaFin = $this->getFinSemana($semanaSelector);

        $result->bindParam(':semana_inicio', $semanaInicio);
        $result->bindParam(':semana_fin', $semanaFin);

        $result->execute();
        return $result->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene TODOS los horarios de una semana (para todas las fichas) en una sola consulta.
     * Se usa para la tabla principal de horarios (server-side processing): evita lanzar
     * una consulta por ficha y por día al cargar la página.
     * Mantiene exactamente las mismas condiciones que getAllUltraEstricto().
     */
    /**
     * Cuenta horarios con fecha específica en una semana (para elegir semana inicial con datos).
     */
    public function contarHorariosSemana($semanaSelector = null)
    {
        try {
            $sql = 'SELECT COUNT(*) FROM horario
                    WHERE fecha_especifica IS NOT NULL
                    AND fecha_especifica BETWEEN :semana_inicio AND :semana_fin';
            $modelo = new Conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $semanaInicio = $this->getInicioSemana($semanaSelector);
            $semanaFin = $this->getFinSemana($semanaSelector);
            $result->bindParam(':semana_inicio', $semanaInicio);
            $result->bindParam(':semana_fin', $semanaFin);
            $result->execute();
            return (int)$result->fetchColumn();
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Última semana con horarios, en formato YYYY-WW (igual que calcularSemanaISO).
     */
    public function getUltimaSemanaConHorarios()
    {
        try {
            $modelo = new Conexion();
            $conexion = $modelo->get_conexion();
            $max = $conexion->query("SELECT MAX(fecha_especifica) FROM horario WHERE fecha_especifica IS NOT NULL")->fetchColumn();
            if (!$max) {
                return null;
            }
            $dt = new DateTime($max, new DateTimeZone('America/Bogota'));
            return $dt->format('o-\WW');
        } catch (Exception $e) {
            return null;
        }
    }

    public function getHorariosSemanaTodas($semanaSelector = null)
    {        try {
            $sql = 'SELECT h.idhor, h.idfic, h.idaul, a.nomaul, a.codubi, a.idcen, h.idusu,
                           u.ndocusu, u.nomusu, u.colfon, u.coltex, h.iddia,
                           h.es_otros, h.actividad, h.horas_otros,
                           h.es_formacion_directa,
                           h.es_transversal, h.nombre_transversal,
                           h.fecha_inicio, h.fecha_fin,
                           h.fecha_especifica, h.hinihor, h.hfinhor
                    FROM horario AS h
                    LEFT JOIN usuario AS u ON h.idusu = u.idusu
                    LEFT JOIN aula AS a ON h.idaul = a.idaul
                    WHERE h.fecha_especifica IS NOT NULL
                    AND h.fecha_especifica BETWEEN :semana_inicio AND :semana_fin
                    AND (
                        (h.fecha_inicio IS NOT NULL AND h.fecha_fin IS NOT NULL) OR
                        (h.fecha_inicio IS NULL AND h.fecha_fin IS NULL)
                    )
                    ORDER BY h.idfic ASC, h.iddia ASC, h.hinihor ASC';

            $modelo   = new Conexion();
            $conexion = $modelo->get_conexion();
            $result   = $conexion->prepare($sql);

            $semanaInicio = $this->getInicioSemana($semanaSelector);
            $semanaFin    = $this->getFinSemana($semanaSelector);

            $result->bindParam(':semana_inicio', $semanaInicio);
            $result->bindParam(':semana_fin', $semanaFin);

            $result->execute();
            return $result->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en getHorariosSemanaTodas: " . $e->getMessage());
            return [];
        }
    }

    public function probarFiltro($semanaSelector = null)
    {
        $resultado = [
            'semana_selector' => $semanaSelector,
            'fechas_semana' => [],
            'ficha' => $this->getIdfic(),
            'dia' => $this->getIddia(),
            'horarios_encontrados' => [],
            'resumen' => []
        ];

        try {
            $semanaInicio = $this->getInicioSemana($semanaSelector);
            $semanaFin = $this->getFinSemana($semanaSelector);
            $resultado['fechas_semana'] = [
                'inicio' => $semanaInicio,
                'fin' => $semanaFin
            ];

            $horarios = $this->getAll($semanaSelector);
            $resultado['horarios_encontrados'] = $horarios;

            foreach ($horarios as $horario) {
                $fechaEspecifica = $horario['fecha_especifica'];
                $fechaInicio = $horario['fecha_inicio'];
                $fechaFin = $horario['fecha_fin'];

                $enSemana = ($fechaEspecifica >= $semanaInicio && $fechaEspecifica <= $semanaFin);

                $fechaDT = new DateTime($fechaEspecifica);
                $diaSemana = $fechaDT->format('N');

                $mapeoDias = [
                    1042 => 1, // Lunes
                    1043 => 2, // Martes
                    1044 => 3, // Miércoles
                    1045 => 4, // Jueves
                    1046 => 5, // Viernes
                    1047 => 6, // Sábado
                    1048 => 7  // Domingo
                ];

                $diaEsperado = $mapeoDias[$this->getIddia()] ?? 1;
                $diaCoincide = ($diaSemana == $diaEsperado);

                $resultado['resumen'][] = [
                    'id_horario' => $horario['idhor'],
                    'fecha_especifica' => $fechaEspecifica,
                    'fecha_inicio' => $fechaInicio,
                    'fecha_fin' => $fechaFin,
                    'dia_semana' => $diaSemana,
                    'dia_esperado' => $diaEsperado,
                    'en_semana' => $enSemana,
                    'dia_coincide' => $diaCoincide,
                    'valido' => $enSemana && $diaCoincide
                ];
            }
        } catch (Exception $e) {
            error_log("Error en probarFiltro: " . $e->getMessage());
            $resultado['error'] = $e->getMessage();
        }

        return $resultado;
    }

    public function getHorariosPorInstructor($idInstructor)
    {
        try {
            $sql = 'SELECT h.idhor, h.idfic, h.idaul, h.idusu, h.iddia, h.fecha_inicio, h.fecha_fin, h.fecha_especifica,
                           h.es_otros, h.actividad, h.horas_otros, h.es_transversal, h.nombre_transversal, h.es_formacion_directa,
                           f.nomfic, f.jornada, vj.nomval as nombre_jornada, vd.nomval as nombre_dia, a.nomaul, u.ndocusu, u.nomusu
                    FROM horario AS h 
                    LEFT JOIN ficha AS f ON h.idfic = f.idfic
                    LEFT JOIN valor AS vj ON f.jornada = vj.idval
                    LEFT JOIN valor AS vd ON h.iddia = vd.idval
                    LEFT JOIN aula AS a ON h.idaul = a.idaul
                    LEFT JOIN usuario AS u ON h.idusu = u.idusu
                    WHERE h.idusu = :idInstructor
                    AND h.fecha_especifica IS NOT NULL
                    ORDER BY h.fecha_especifica ASC';

            $modelo = new Conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->bindParam(':idInstructor', $idInstructor);
            $result->execute();

            return $result->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en getHorariosPorInstructor: " . $e->getMessage());
            return [];
        }
    }

    public function getOne()
    {
        $sql      = 'SELECT * FROM horario WHERE idhor=:idhor';
        $modelo   = new Conexion();
        $conexion = $modelo->get_conexion();
        $result   = $conexion->prepare($sql);
        $idhor    = $this->getIdhor();
        $result->bindParam(':idhor', $idhor);
        $result->execute();

        return $result->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getFicha()
    {
        $sql      = 'SELECT f.idfic, f.nomfic, v.nomval, f.codpro, a.nomare FROM ficha AS f INNER JOIN valor AS v ON f.jornada=v.idval LEFT JOIN programa AS p ON f.codpro=p.codpro LEFT JOIN area AS a ON p.idare=a.idare WHERE f.idfic>1000';
        $modelo   = new Conexion();
        $conexion = $modelo->get_conexion();
        $result   = $conexion->prepare($sql);
        $result->execute();

        return $result->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getDia()
    {
        $sql      = 'SELECT idval, nomval, parval FROM valor WHERE act=1 AND iddom=14 ORDER BY parval';
        $modelo   = new Conexion();
        $conexion = $modelo->get_conexion();
        $result   = $conexion->prepare($sql);
        $result->execute();

        return $result->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getInst()
    {
        $sql      = 'SELECT p.idusu, p.ndocusu, p.nomusu, p.idper, p.emausu, p.actusu FROM usuario AS p INNER JOIN usupef AS f ON p.idusu=f.idusu WHERE f.idper IN (7,32,42) ORDER BY p.nomusu';
        $modelo   = new Conexion();
        $conexion = $modelo->get_conexion();
        $result   = $conexion->prepare($sql);
        $result->execute();

        return $result->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAula()
    {
        $sql      = 'SELECT idaul, nomaul FROM aula ORDER BY idaul';
        $modelo   = new Conexion();
        $conexion = $modelo->get_conexion();
        $result   = $conexion->prepare($sql);
        $result->execute();

        return $result->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getArea()
    {
        $sql      = 'SELECT idare, nomare, idusu FROM area';
        $modelo   = new Conexion();
        $conexion = $modelo->get_conexion();
        $result   = $conexion->prepare($sql);
        $result->execute();

        return $result->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getFiltro($fec = null)
    {
        $sql = 'SELECT f.idfic, f.nomfic, v.nomval, f.codpro, a.idare, a.nomare FROM ficha AS f INNER JOIN valor AS v ON f.jornada=v.idval 
                LEFT JOIN programa AS p ON f.codpro=p.codpro LEFT JOIN area AS a ON p.idare=a.idare WHERE f.idfic>1000 AND a.idare=:idare';

        $idfic_especifica = $this->getIdfic();
        if ($idfic_especifica && $idfic_especifica > 1000) {
            $sql .= " AND f.idfic = :idfic_especifica";
        }

        if ($fec) {
            $sql .= " AND '".$fec."'<=f.ffinfic";
        }

        $modelo   = new Conexion();
        $conexion = $modelo->get_conexion();
        $result   = $conexion->prepare($sql);
        $idare    = $this->getIdare();
        $result->bindParam(':idare', $idare);

        if ($idfic_especifica && $idfic_especifica > 1000) {
            $result->bindParam(':idfic_especifica', $idfic_especifica);
        }

        $result->execute();
        return $result->fetchAll(PDO::FETCH_ASSOC);
    }

    public function save()
    {
        $sql = 'INSERT INTO horario (idfic, idaul, idusu, iddia, es_otros, actividad, horas_otros, es_formacion_directa, es_transversal, nombre_transversal, fecha_inicio, fecha_fin, fecha_especifica, hinihor, hfinhor, categoria_hora) 
                VALUES (:idfic, :idaul, :idusu, :iddia, :es_otros, :actividad, :horas_otros, :es_formacion_directa, :es_transversal, :nombre_transversal, :fecha_inicio, :fecha_fin, :fecha_especifica, :hinihor, :hfinhor, :categoria_hora)';

        $modelo   = new Conexion();
        $conexion = $modelo->get_conexion();
        $result   = $conexion->prepare($sql);

        $idfic = $this->getIdfic();
        $result->bindParam(':idfic', $idfic);

        $idaul = $this->getIdaul();
        $result->bindParam(':idaul', $idaul, PDO::PARAM_INT);

        $iddia = $this->getIddia();
        $result->bindParam(':iddia', $iddia);

        $idusu = $this->getIdusu();
        $result->bindParam(':idusu', $idusu);

        $es_otros = $this->getEsOtros() ? 1 : 0;
        $result->bindParam(':es_otros', $es_otros);

        $actividad = $this->getActividad();
        $result->bindParam(':actividad', $actividad);

        $horas_otros = $this->getHorasOtros();
        $result->bindParam(':horas_otros', $horas_otros);

        $es_formacion_directa = $this->getEsFormacionDirecta();
        if ($es_formacion_directa === null || $es_formacion_directa === '') {
            $es_formacion_directa = 1;
        }
        $result->bindParam(':es_formacion_directa', $es_formacion_directa);

        $es_transversal = $this->getEsTransversal() ? 1 : 0;
        $result->bindParam(':es_transversal', $es_transversal);

        $nombre_transversal = $this->getNombreTransversal();
        $result->bindParam(':nombre_transversal', $nombre_transversal);

        $fecha_inicio = $this->getFechaInicio();
        $result->bindParam(':fecha_inicio', $fecha_inicio);

        $fecha_fin = $this->getFechaFin();
        $result->bindParam(':fecha_fin', $fecha_fin);

        $fecha_especifica = $this->getFechaEspecifica();
        $result->bindParam(':fecha_especifica', $fecha_especifica);

        $hinihor = $this->getHinihor();
        if (empty($hinihor)) {
            $hinihor = '00:00:00';
        }
        $result->bindParam(':hinihor', $hinihor);

        $hfinhor = $this->getHfinhor();
        if (empty($hfinhor)) {
            $hfinhor = '00:00:00';
        }
        $result->bindParam(':hfinhor', $hfinhor);

        $categoria_hora = $this->getCategoriaHora();
        if (empty($categoria_hora)) {
            $categoria_hora = $this->derivarCategoriaHora();
        }
        $result->bindParam(':categoria_hora', $categoria_hora);

        $result->execute();
    }

    public function edit()
    {
        $sql = 'UPDATE horario SET idfic=:idfic, idaul=:idaul, idusu=:idusu, iddia=:iddia, 
                es_otros=:es_otros, actividad=:actividad, horas_otros=:horas_otros, es_formacion_directa=:es_formacion_directa,
                es_transversal=:es_transversal, nombre_transversal=:nombre_transversal,
                fecha_inicio=:fecha_inicio, fecha_fin=:fecha_fin, fecha_especifica=:fecha_especifica,
                hinihor=:hinihor, hfinhor=:hfinhor, categoria_hora=:categoria_hora
                WHERE idhor=:idhor';

        $modelo   = new Conexion();
        $conexion = $modelo->get_conexion();
        $result   = $conexion->prepare($sql);

        $idhor = $this->getIdhor();
        $result->bindParam(':idhor', $idhor);

        $idfic = $this->getIdfic();
        $result->bindParam(':idfic', $idfic);

        $idaul = $this->getIdaul();
        $result->bindParam(':idaul', $idaul, PDO::PARAM_INT);

        $idusu = $this->getIdusu();
        $result->bindParam(':idusu', $idusu);

        $iddia = $this->getIddia();
        $result->bindParam(':iddia', $iddia);

        $es_otros = $this->getEsOtros() ? 1 : 0;
        $result->bindParam(':es_otros', $es_otros);

        $actividad = $this->getActividad();
        $result->bindParam(':actividad', $actividad);

        $horas_otros = $this->getHorasOtros();
        $result->bindParam(':horas_otros', $horas_otros);

        $es_formacion_directa = $this->getEsFormacionDirecta();
        if ($es_formacion_directa === null || $es_formacion_directa === '') {
            $es_formacion_directa = 1;
        }
        $result->bindParam(':es_formacion_directa', $es_formacion_directa);

        $es_transversal = $this->getEsTransversal() ? 1 : 0;
        $result->bindParam(':es_transversal', $es_transversal);

        $nombre_transversal = $this->getNombreTransversal();
        $result->bindParam(':nombre_transversal', $nombre_transversal);

        $fecha_inicio = $this->getFechaInicio();
        $result->bindParam(':fecha_inicio', $fecha_inicio);

        $fecha_fin = $this->getFechaFin();
        $result->bindParam(':fecha_fin', $fecha_fin);

        $fecha_especifica = $this->getFechaEspecifica();
        $result->bindParam(':fecha_especifica', $fecha_especifica);

        $hinihor = $this->getHinihor();
        if (empty($hinihor)) {
            $hinihor = '00:00:00';
        }
        $result->bindParam(':hinihor', $hinihor);

        $hfinhor = $this->getHfinhor();
        if (empty($hfinhor)) {
            $hfinhor = '00:00:00';
        }
        $result->bindParam(':hfinhor', $hfinhor);

        $categoria_hora = $this->getCategoriaHora();
        if (empty($categoria_hora)) {
            $categoria_hora = $this->derivarCategoriaHora();
        }
        $result->bindParam(':categoria_hora', $categoria_hora);

        $result->execute();
    }

    public function del()
    {
        $sql      = 'DELETE FROM horario WHERE idhor=:idhor AND iddia=:iddia';
        $modelo   = new Conexion();
        $conexion = $modelo->get_conexion();
        $result   = $conexion->prepare($sql);
        $idhor    = $this->getIdhor();
        $result->bindParam(':idhor', $idhor);
        $iddia = $this->getIddia();
        $result->bindParam(':iddia', $iddia);
        $result->execute();
    }

    /**
     * Elimina un grupo completo de horarios (misma ficha, día, fechas, aula e instructor)
     */
    public function delGrupo($idfic, $iddia, $idusu, $idaul, $fecha_inicio, $fecha_fin)
    {
        $sql = 'DELETE FROM horario WHERE idfic=:idfic AND iddia=:iddia AND fecha_inicio=:fecha_inicio AND fecha_fin=:fecha_fin';
        
        if ($idusu === null || $idusu === '') {
            $sql .= ' AND (idusu IS NULL OR idusu = "")';
        } else {
            $sql .= ' AND idusu=:idusu';
        }

        if ($idaul === null || $idaul === '') {
            $sql .= ' AND (idaul IS NULL OR idaul = "")';
        } else {
            $sql .= ' AND idaul=:idaul';
        }

        $modelo   = new Conexion();
        $conexion = $modelo->get_conexion();
        $result   = $conexion->prepare($sql);

        $result->bindParam(':idfic', $idfic);
        $result->bindParam(':iddia', $iddia);
        $result->bindParam(':fecha_inicio', $fecha_inicio);
        $result->bindParam(':fecha_fin', $fecha_fin);

        if ($idusu !== null && $idusu !== '') {
            $result->bindParam(':idusu', $idusu);
        }

        if ($idaul !== null && $idaul !== '') {
            $result->bindParam(':idaul', $idaul, PDO::PARAM_INT);
        }

        $result->execute();
    }

    public function obtenerNombreDia($dia)
    {
        $dias = [
            1042 => 'Lunes',
            1043 => 'Martes',
            1044 => 'Miércoles',
            1045 => 'Jueves',
            1046 => 'Viernes',
            1047 => 'Sábado',
            1048 => 'Domingo'
        ];
        if (isset($dias[$dia])) {
            return $dias[$dia];
        }

        try {
            $sql = 'SELECT nomval FROM valor WHERE idval = :dia LIMIT 1';
            $modelo = new Conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->bindParam(':dia', $dia);
            $result->execute();
            $row = $result->fetch(PDO::FETCH_ASSOC);
            return $row ? $row['nomval'] : 'Día';
        } catch (Exception $e) {
            return 'Día';
        }
    }

    public function verificarHorarioExistente($idfic, $dia, $fecha = null)
    {
        try {
            $modelo = new Conexion();
            $conexion = $modelo->get_conexion();
            
            if ($fecha) {
                $sql = 'SELECT idhor FROM horario 
                        WHERE idfic = :idfic 
                        AND iddia = :dia 
                        AND (fecha_especifica = :fecha OR (:fecha BETWEEN fecha_inicio AND fecha_fin)) 
                        LIMIT 1';
                $result = $conexion->prepare($sql);
                $result->bindParam(':idfic', $idfic);
                $result->bindParam(':dia', $dia);
                $result->bindParam(':fecha', $fecha);
            } else {
                $sql = 'SELECT idhor FROM horario WHERE idfic = :idfic AND iddia = :dia LIMIT 1';
                $result = $conexion->prepare($sql);
                $result->bindParam(':idfic', $idfic);
                $result->bindParam(':dia', $dia);
            }
            
            $result->execute();
            $row = $result->fetch(PDO::FETCH_ASSOC);
            return $row ? $row['idhor'] : false;
        } catch (Exception $e) {
            return false;
        }
    }

    public function verificarConflictoInstructor($idusu, $idfic, $dia, $fecha = null)
    {
        if (empty($idusu)) return false;
        try {
            $modelo = new Conexion();
            $conexion = $modelo->get_conexion();
            
            if ($fecha) {
                $sql = 'SELECT idhor FROM horario 
                        WHERE idusu = :idusu 
                        AND idfic != :idfic 
                        AND iddia = :dia 
                        AND (fecha_especifica = :fecha OR (:fecha BETWEEN fecha_inicio AND fecha_fin)) 
                        LIMIT 1';
                $result = $conexion->prepare($sql);
                $result->bindParam(':idusu', $idusu);
                $result->bindParam(':idfic', $idfic);
                $result->bindParam(':dia', $dia);
                $result->bindParam(':fecha', $fecha);
            } else {
                $sql = 'SELECT idhor FROM horario 
                        WHERE idusu = :idusu 
                        AND idfic != :idfic 
                        AND iddia = :dia 
                        LIMIT 1';
                $result = $conexion->prepare($sql);
                $result->bindParam(':idusu', $idusu);
                $result->bindParam(':idfic', $idfic);
                $result->bindParam(':dia', $dia);
            }
            
            $result->execute();
            $row = $result->fetch(PDO::FETCH_ASSOC);
            return $row ? true : false;
        } catch (Exception $e) {
            return false;
        }
    }

    public function verificarConflictoAula($idaul, $idfic, $dia, $fecha = null)
    {
        if (empty($idaul)) return false;
        try {
            $modelo = new Conexion();
            $conexion = $modelo->get_conexion();
            
            if ($fecha) {
                $sql = 'SELECT idhor FROM horario 
                        WHERE idaul = :idaul 
                        AND idfic != :idfic 
                        AND iddia = :dia 
                        AND (fecha_especifica = :fecha OR (:fecha BETWEEN fecha_inicio AND fecha_fin)) 
                        LIMIT 1';
                $result = $conexion->prepare($sql);
                $result->bindParam(':idaul', $idaul, PDO::PARAM_INT);
                $result->bindParam(':idfic', $idfic);
                $result->bindParam(':dia', $dia);
                $result->bindParam(':fecha', $fecha);
            } else {
                $sql = 'SELECT idhor FROM horario 
                        WHERE idaul = :idaul 
                        AND idfic != :idfic 
                        AND iddia = :dia 
                        LIMIT 1';
                $result = $conexion->prepare($sql);
                $result->bindParam(':idaul', $idaul, PDO::PARAM_INT);
                $result->bindParam(':idfic', $idfic);
                $result->bindParam(':dia', $dia);
            }
            
            $result->execute();
            $row = $result->fetch(PDO::FETCH_ASSOC);
            return $row ? true : false;
        } catch (Exception $e) {
            return false;
        }
    }

    public function contarHorariosGrupo($idfic, $iddia, $idusu = null, $idaul = null, $fecha_inicio = null, $fecha_fin = null)
    {
        try {
            $modelo = new Conexion();
            $conexion = $modelo->get_conexion();
            $sql = 'SELECT COUNT(*) as total FROM horario WHERE idfic = :idfic AND iddia = :iddia';
            
            if ($fecha_inicio && $fecha_fin) {
                $sql .= ' AND fecha_inicio = :fecha_inicio AND fecha_fin = :fecha_fin';
            }
            if ($idusu !== null && $idusu !== '') {
                $sql .= ' AND idusu = :idusu';
            }
            if ($idaul !== null && $idaul !== '') {
                $sql .= ' AND idaul = :idaul';
            }
            
            $result = $conexion->prepare($sql);
            $result->bindParam(':idfic', $idfic);
            $result->bindParam(':iddia', $iddia);
            if ($fecha_inicio && $fecha_fin) {
                $result->bindParam(':fecha_inicio', $fecha_inicio);
                $result->bindParam(':fecha_fin', $fecha_fin);
            }
            if ($idusu !== null && $idusu !== '') {
                $result->bindParam(':idusu', $idusu);
            }
            if ($idaul !== null && $idaul !== '') {
                $result->bindParam(':idaul', $idaul);
            }
            
            $result->execute();
            $row = $result->fetch(PDO::FETCH_ASSOC);
            return $row ? (int)$row['total'] : 0;
        } catch (Exception $e) {
            return 0;
        }
    }

    public function getEventosPorTipo($tipoEvento, $anio = null)
    {
        try {
            $modelo = new Conexion();
            $conexion = $modelo->get_conexion();
            $sql = 'SELECT * FROM calacade WHERE tipo_evento = :tipo';
            if ($anio) {
                $sql .= ' AND YEAR(fecha_inicio) = :anio';
            }
            $sql .= ' ORDER BY fecha_inicio ASC';
            
            $result = $conexion->prepare($sql);
            $result->bindParam(':tipo', $tipoEvento);
            if ($anio) {
                $result->bindParam(':anio', $anio);
            }
            $result->execute();
            return $result->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    public function getTiposEventos()
    {
        try {
            $modelo = new Conexion();
            $conexion = $modelo->get_conexion();
            $sql = 'SELECT DISTINCT tipo_evento FROM calacade WHERE tipo_evento IS NOT NULL AND tipo_evento != "" ORDER BY tipo_evento';
            $result = $conexion->prepare($sql);
            $result->execute();
            $filas = $result->fetchAll(PDO::FETCH_ASSOC);
            $tipos = [];
            foreach ($filas as $f) {
                $tipos[] = $f['tipo_evento'];
            }
            return $tipos;
        } catch (Exception $e) {
            return [];
        }
    }

    public function debugFiltroHorarios($semana_selector = null)
    {
        return $this->probarFiltro($semana_selector);
    }

    /**
     * Obtiene las áreas asignadas al coordinador autenticado
     * Si el perfil es Administrador de Centro (idper = 2) o Superadmin, retorna todas las áreas
     */
    public function getAreasCoordinador($idusu, $idper = null)
    {
        try {
            $modelo = new Conexion();
            $conexion = $modelo->get_conexion();

            // Si es administrador general / centro, tiene acceso a todas las áreas
            if ($idper == 2) {
                $sql = 'SELECT idare, nomare, idusu FROM area ORDER BY nomare ASC';
                $stmt = $conexion->prepare($sql);
                $stmt->execute();
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }

            // Para coordinadores (idper = 21 u otros): solo áreas asignadas en filhor o como responsable en area
            $sql = 'SELECT DISTINCT a.idare, a.nomare, a.idusu 
                    FROM area a 
                    LEFT JOIN filhor fh ON a.idare = fh.idare 
                    WHERE (a.idusu = :idusu OR fh.idusu = :idusu)
                    ORDER BY a.nomare ASC';
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(':idusu', $idusu);
            $stmt->execute();
            $areas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Si no tiene áreas asignadas directamente pero es coordinador/admin, traer áreas disponibles
            if (empty($areas) && ($idper == 21 || $idper == 1)) {
                $sqlFallback = 'SELECT idare, nomare, idusu FROM area ORDER BY nomare ASC';
                $stmtFallback = $conexion->prepare($sqlFallback);
                $stmtFallback->execute();
                $areas = $stmtFallback->fetchAll(PDO::FETCH_ASSOC);
            }

            return $areas;
        } catch (Exception $e) {
            error_log("Error en getAreasCoordinador: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene las fichas filtradas por las áreas permitidas del coordinador
     */
    public function getFichasCoordinador($idusu, $idare = null, $idper = null)
    {
        try {
            $modelo = new Conexion();
            $conexion = $modelo->get_conexion();

            $areasPermitidas = $this->getAreasCoordinador($idusu, $idper);
            $idsAreasPermitidas = array_column($areasPermitidas, 'idare');

            $sql = 'SELECT f.idfic, f.nomfic, v.nomval, f.codpro, p.nompro, a.idare, a.nomare 
                    FROM ficha AS f 
                    INNER JOIN valor AS v ON f.jornada = v.idval 
                    LEFT JOIN programa AS p ON f.codpro = p.codpro 
                    LEFT JOIN area AS a ON p.idare = a.idare 
                    WHERE f.idfic > 1000';

            $params = [];

            if (!empty($idare) && $idare !== 'todas') {
                // Verificar que el área solicitada esté entre las permitidas si no es admin
                if ($idper != 2 && !empty($idsAreasPermitidas) && !in_array($idare, $idsAreasPermitidas)) {
                    // Forzar a sus áreas permitidas o fichas con horarios registrados
                    $inPlaceholders = implode(',', array_map('intval', $idsAreasPermitidas));
                    $sql .= " AND (a.idare IN ($inPlaceholders) OR a.idare IS NULL OR a.idare = 0 OR f.idfic IN (SELECT DISTINCT idfic FROM horario))";
                } else {
                    $sql .= ' AND a.idare = :idare';
                    $params[':idare'] = $idare;
                }
            } elseif ($idper != 2 && !empty($idsAreasPermitidas)) {
                // Restricción por defecto para coordinador sin filtro de área específico
                $inPlaceholders = implode(',', array_map('intval', $idsAreasPermitidas));
                $sql .= " AND (a.idare IN ($inPlaceholders) OR a.idare IS NULL OR a.idare = 0 OR f.idfic IN (SELECT DISTINCT idfic FROM horario))";
            }

            $sql .= ' ORDER BY f.idfic ASC';

            $stmt = $conexion->prepare($sql);
            foreach ($params as $param => $val) {
                $stmt->bindValue($param, $val);
            }
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en getFichasCoordinador: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene los instructores relacionados con las áreas y fichas del coordinador
     */
    public function getInstructoresCoordinador($idusu_coord = null, $idare = null, $idfic = null, $idper = null)
    {
        try {
            $modelo = new Conexion();
            $conexion = $modelo->get_conexion();

            $sql = 'SELECT DISTINCT u.idusu, u.ndocusu, u.nomusu, u.emausu, u.actusu 
                    FROM usuario u 
                    INNER JOIN usupef up ON u.idusu = up.idusu 
                    WHERE up.idper IN (7, 32, 42) AND u.actusu = 1 
                    ORDER BY u.nomusu ASC';

            $stmt = $conexion->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en getInstructoresCoordinador: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Construye la consulta SQL (y parámetros) base para la carga horaria de instructores.
     * Compartida por getCargaHorariaInstructores y la versión paginada (server-side DataTables).
     *
     * @return array [string $sql, array $params]
     */
    private function prepararConsultaCargaHoraria($filtros = [])
    {
        $idusu_coord = $filtros['idusu_coord'] ?? null;
        $idper = $filtros['idper'] ?? null;
        $idare = $filtros['idare'] ?? null;
        $idfic = $filtros['idfic'] ?? null;
        $idusu_inst = $filtros['idusu_inst'] ?? null;
        $fecha_inicio = $filtros['fecha_inicio'] ?? null;
        $fecha_fin = $filtros['fecha_fin'] ?? null;
        $mes = $filtros['mes'] ?? null;
        $anio = $filtros['anio'] ?? null;

        // Obtener áreas permitidas
        $areasPermitidas = $this->getAreasCoordinador($idusu_coord, $idper);
        $idsAreasPermitidas = array_column($areasPermitidas, 'idare');

        $sql = 'SELECT h.idhor, h.idfic, h.idaul, f.nomfic, p.nompro, a.idare, a.nomare,
                       h.idusu, u.nomusu, u.ndocusu, u.colfon, u.coltex,
                       h.iddia, vd.nomval AS nomdia,
                       h.hinihor, h.hfinhor,
                       h.es_otros, h.actividad, h.horas_otros,
                       h.es_transversal, h.nombre_transversal,
                       h.es_formacion_directa,
                       h.fecha_inicio, h.fecha_fin, h.fecha_especifica,
                       f.jornada, vj.nomval AS nomjornada, vj.nhora AS horas_jornada,
                       au.nomaul
                FROM horario h
                LEFT JOIN ficha f ON h.idfic = f.idfic
                LEFT JOIN programa p ON f.codpro = p.codpro
                LEFT JOIN area a ON p.idare = a.idare
                LEFT JOIN usuario u ON h.idusu = u.idusu
                LEFT JOIN valor vd ON h.iddia = vd.idval
                LEFT JOIN valor vj ON f.jornada = vj.idval
                LEFT JOIN aula au ON h.idaul = au.idaul
                WHERE h.fecha_especifica IS NOT NULL';

        $params = [];

        // Restricción por área
        if (!empty($idare) && $idare !== 'todas') {
            if ($idper != 2 && !empty($idsAreasPermitidas) && !in_array($idare, $idsAreasPermitidas)) {
                $inPlaceholders = implode(',', array_map('intval', $idsAreasPermitidas));
                $sql .= " AND (a.idare IN ($inPlaceholders) OR a.idare IS NULL OR a.idare = 0)";
            } else {
                $sql .= ' AND a.idare = :idare';
                $params[':idare'] = $idare;
            }
        } elseif ($idper != 2 && !empty($idsAreasPermitidas)) {
            $inPlaceholders = implode(',', array_map('intval', $idsAreasPermitidas));
            $sql .= " AND (a.idare IN ($inPlaceholders) OR a.idare IS NULL OR a.idare = 0)";
        }

        // Restricción por ficha
        if (!empty($idfic)) {
            $sql .= ' AND h.idfic = :idfic';
            $params[':idfic'] = $idfic;
        }

        // Restricción por instructor
        if (!empty($idusu_inst)) {
            $sql .= ' AND h.idusu = :idusu_inst';
            $params[':idusu_inst'] = $idusu_inst;
        }

        // Filtro por rango de fechas
        if (!empty($fecha_inicio) && !empty($fecha_fin)) {
            $sql .= ' AND h.fecha_especifica BETWEEN :fecha_inicio AND :fecha_fin';
            $params[':fecha_inicio'] = $fecha_inicio;
            $params[':fecha_fin'] = $fecha_fin;
        } elseif (!empty($mes) && !empty($anio)) {
            $sql .= ' AND MONTH(h.fecha_especifica) = :mes AND YEAR(h.fecha_especifica) = :anio';
            $params[':mes'] = (int)$mes;
            $params[':anio'] = (int)$anio;
        }

        $sql .= ' ORDER BY h.fecha_especifica ASC';

        return [$sql, $params];
    }

    /**
     * Calcula la duración real de un horario y su clasificación (planta/transversal/otros).
     *
     * @return array [float $duracion, string $tipoHora]
     */
    private function calcularDuracionClasificacion($h)
    {
        $duracion = 0.0;
        if (!empty($h['hinihor']) && !empty($h['hfinhor']) && $h['hinihor'] !== '00:00:00' && $h['hfinhor'] !== '00:00:00') {
            $t1 = strtotime($h['hinihor']);
            $t2 = strtotime($h['hfinhor']);
            if ($t2 > $t1) {
                $duracion = round(($t2 - $t1) / 3600, 2);
            }
        }

        if ($duracion <= 0) {
            if (!empty($h['es_otros']) && !empty($h['horas_otros']) && (float)$h['horas_otros'] > 0) {
                $duracion = (float)$h['horas_otros'];
            } elseif (!empty($h['horas_jornada'])) {
                $duracion = (float)$h['horas_jornada'];
            } else {
                $duracion = 6.0;
            }
        }

        $tipoHora = 'Horas de planta / Formación';
        if (!empty($h['es_transversal'])) {
            $tipoHora = 'Horas transversales';
        } elseif (!empty($h['es_otros']) && ($h['es_formacion_directa'] != 2)) {
            $tipoHora = 'Otras horas / Actividad especial';
        }

        return [$duracion, $tipoHora];
    }

    /**
     * Construye el detalle (item) de un horario para un instructor.
     */
    private function construirItemDetalleHorario($h, $duracion, $tipoHora)
    {
        $horaIniLabel = (!empty($h['hinihor']) && $h['hinihor'] !== '00:00:00') ? substr($h['hinihor'], 0, 5) : '';
        $horaFinLabel = (!empty($h['hfinhor']) && $h['hfinhor'] !== '00:00:00') ? substr($h['hfinhor'], 0, 5) : '';
        $jornadaLabel = $h['nomjornada'] ?: '';
        if ($horaIniLabel && $horaFinLabel) {
            $jornadaLabel = $horaIniLabel . ' - ' . $horaFinLabel;
        }

        return [
            'idhor' => $h['idhor'],
            'idfic' => $h['idfic'],
            'nomfic' => $h['nomfic'],
            'ficha' => $h['nomfic'] ?: '',
            'codpro' => $h['nompro'] ? $h['nompro'] : $h['nomfic'],
            'programa' => $h['nompro'] ?: 'No asignado',
            'area' => $h['nomare'] ?: 'General',
            'actividad' => $h['actividad'] ?: ($h['nombre_transversal'] ?: 'Formación Regular'),
            'tipo_hora' => $tipoHora,
            'tipo_horas' => $tipoHora === 'Otras horas / Actividad especial' ? 'otros' : ($tipoHora === 'Horas transversales' ? 'transversal' : 'formacion'),
            'iddia' => $h['iddia'],
            'dia' => $h['nomdia'] ?: 'Día',
            'fecha' => $h['fecha_especifica'],
            'fecha_especifica' => $h['fecha_especifica'],
            'fecha_inicio' => $h['fecha_inicio'] ?: '',
            'fecha_fin' => $h['fecha_fin'] ?: '',
            'hinihor' => (!empty($h['hinihor']) && $h['hinihor'] !== '00:00:00') ? substr($h['hinihor'], 0, 5) : '',
            'hfinhor' => (!empty($h['hfinhor']) && $h['hfinhor'] !== '00:00:00') ? substr($h['hfinhor'], 0, 5) : '',
            'jornada' => $jornadaLabel,
            'nomjornada' => $h['nomjornada'] ?: '',
            'horas' => $duracion,
            'aula' => $h['nomaul'] ?: 'Sin aula',
            'idusu' => $h['idusu'],
            'idaul' => $h['idaul'],
            'nomusu' => $h['nomusu'] ?: 'Sin instructor',
            'ndocusu' => $h['ndocusu'] ?: '',
            'es_otros' => (int)($h['es_otros'] ?? 0),
            'es_transversal' => (int)($h['es_transversal'] ?? 0),
            'es_formacion_directa' => (int)($h['es_formacion_directa'] ?? 1),
            'nombre_transversal' => $h['nombre_transversal'] ?: '',
            'horas_otros' => $h['horas_otros'] ?: ''
        ];
    }

    /**
     * Inicializa el acumulador de carga de un instructor.
     */
    private function inicializarInstructorCarga($key, $h)
    {
        $idusu = $h['idusu'] ? (int)$h['idusu'] : 0;
        $nomusu = $h['nomusu'] ?: ($h['es_transversal'] ? 'Transversal (Sin instructor)' : 'Sin instructor asignado');
        
        // Determinar tipo de vinculación (planta 187h / contratista 160h)
        $maxHoras = 160;
        $tipoContrato = 'contratista';
        if (function_exists('obtenerTipoContratoYMaxHoras') && $idusu > 0) {
            $infoContrato = obtenerTipoContratoYMaxHoras($idusu);
            $tipoContrato = $infoContrato['tipo_contrato'] ?? 'contratista';
            $maxHoras = (int)($infoContrato['max_horas'] ?? 160);
        }

        return [
            'id_instructor' => $key,
            'idusu' => $idusu,
            'nombre' => $nomusu,
            'documento' => $h['ndocusu'] ?: '',
            'area' => $h['nomare'] ?: 'General',
            'tipo_contrato' => $tipoContrato,
            'horas_planta' => 0.0,
            'horas_transversales' => 0.0,
            'otras_horas' => 0.0,
            'total_horas' => 0.0,
            'horas' => 0.0,
            'max_horas' => $maxHoras,
            'referencia_planta' => 187,
            'referencia_contratista' => 160,
            'estado' => 'Déficit',
            'estado_badge' => 'warning',
            'fichas' => [],
            'detalle' => []
        ];
    }

    /**
     * Acumula la duración y clasificación de un horario en la carga del instructor.
     */
    private function acumularCargaInstructor(&$inst, $h, $duracion, $tipoHora)
    {
        if ($tipoHora === 'Horas transversales') {
            $inst['horas_transversales'] += $duracion;
        } elseif ($tipoHora === 'Otras horas / Actividad especial') {
            $inst['otras_horas'] += $duracion;
        } else {
            $inst['horas_planta'] += $duracion;
        }

        $inst['total_horas'] += $duracion;
        $inst['horas'] = $inst['total_horas'];

        if (!empty($h['idfic']) && !in_array($h['idfic'], $inst['fichas'])) {
            $inst['fichas'][] = $h['idfic'];
        }
    }

    /**
     * Calcula el estado final (semáforo) según el total de horas.
     * Reglas de negocio:
     * - Déficit: < 160 horas
     * - Cumple: 160 a 187 horas
     * - Sobre Carga: > 187 horas
     */
    private function calcularEstadoCarga(&$inst)
    {
        $total = $inst['total_horas'];
        if ($total < 160) {
            $inst['estado'] = 'Déficit';
            $inst['estado_badge'] = 'warning';
        } elseif ($total <= 187) {
            $inst['estado'] = 'Cumple';
            $inst['estado_badge'] = 'success';
        } else {
            $inst['estado'] = 'Sobre Carga';
            $inst['estado_badge'] = 'danger';
        }
    }

    /**
     * Calcula la carga horaria consolidada de los instructores
     * Devuelve horas de planta, transversales, otras horas, total, estado y desglose
     */
    public function getCargaHorariaInstructores($filtros = [])
    {
        try {
            $modelo = new Conexion();
            $conexion = $modelo->get_conexion();

            list($sql, $params) = $this->prepararConsultaCargaHoraria($filtros);

            $stmt = $conexion->prepare($sql);
            foreach ($params as $param => $val) {
                $stmt->bindValue($param, $val);
            }
            $stmt->execute();
            $horarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Agrupar y calcular por instructor
            $instructoresMap = [];

            foreach ($horarios as $h) {
                $idusu = $h['idusu'] ? (int)$h['idusu'] : 0;
                $key = $idusu > 0 ? (string)$idusu : 'transversal_' . $h['idhor'];

                if (!isset($instructoresMap[$key])) {
                    $instructoresMap[$key] = $this->inicializarInstructorCarga($key, $h);
                }

                list($duracion, $tipoHora) = $this->calcularDuracionClasificacion($h);
                $this->acumularCargaInstructor($instructoresMap[$key], $h, $duracion, $tipoHora);
                $instructoresMap[$key]['detalle'][] = $this->construirItemDetalleHorario($h, $duracion, $tipoHora);
            }

            // Calcular estados finales de cada instructor
            foreach ($instructoresMap as &$inst) {
                $this->calcularEstadoCarga($inst);
            }
            unset($inst);

            // Ordenar inicialmente de MENOR a MAYOR carga horaria (ASC)
            $resultado = array_values($instructoresMap);
            usort($resultado, function($a, $b) {
                if ($a['total_horas'] == $b['total_horas']) {
                    return strcmp($a['nombre'], $b['nombre']);
                }
                return ($a['total_horas'] < $b['total_horas']) ? -1 : 1;
            });

            return $resultado;
        } catch (Exception $e) {
            error_log("Error en getCargaHorariaInstructores: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Versión paginada (server-side DataTables) de la carga horaria de instructores.
     * Aplica los mismos filtros/permisos que getCargaHorariaInstructores, más la
     * búsqueda y el ordenamiento de DataTables, y devuelve solo la página solicitada.
     *
     * @param array $filtros  Filtros base (idare, idfic, idusu_inst, fechas, mes, anio...)
     * @param array $opciones ['busqueda'=>string, 'orden_columna'=>int, 'orden_dir'=>'asc'|'desc', 'start'=>int, 'length'=>int]
     * @return array ['recordsTotal'=>int, 'recordsFiltered'=>int, 'data'=>array]
     */
    public function getCargaHorariaInstructoresPaginado($filtros = [], $opciones = [])
    {
        try {
            $modelo = new Conexion();
            $conexion = $modelo->get_conexion();

            list($sql, $params) = $this->prepararConsultaCargaHoraria($filtros);

            $stmt = $conexion->prepare($sql);
            foreach ($params as $param => $val) {
                $stmt->bindValue($param, $val);
            }
            $stmt->execute();
            $horarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Acumular carga por instructor (sin construir detalle todavía) y conservar sus filas
            $instructoresMap = [];
            $filasPorInstructor = [];

            foreach ($horarios as $h) {
                $idusu = $h['idusu'] ? (int)$h['idusu'] : 0;
                $key = $idusu > 0 ? (string)$idusu : 'transversal_' . $h['idhor'];

                if (!isset($instructoresMap[$key])) {
                    $instructoresMap[$key] = $this->inicializarInstructorCarga($key, $h);
                }

                list($duracion, $tipoHora) = $this->calcularDuracionClasificacion($h);
                $this->acumularCargaInstructor($instructoresMap[$key], $h, $duracion, $tipoHora);
                $filasPorInstructor[$key][] = ['h' => $h, 'duracion' => $duracion, 'tipoHora' => $tipoHora];
            }

            foreach ($instructoresMap as &$inst) {
                $this->calcularEstadoCarga($inst);
            }
            unset($inst);

            // recordsTotal: instructores tras aplicar permisos + filtros del servidor (sin búsqueda de DataTables)
            $recordsTotal = count($instructoresMap);

            // Búsqueda de DataTables (instructor, documento, área)
            $busqueda = trim((string)($opciones['busqueda'] ?? ''));
            if ($busqueda !== '') {
                $needle = function_exists('mb_strtolower') ? mb_strtolower($busqueda, 'UTF-8') : strtolower($busqueda);
                $instructoresMap = array_filter($instructoresMap, function ($inst) use ($needle) {
                    $campos = ($inst['nombre'] ?? '') . ' ' . ($inst['documento'] ?? '') . ' ' . ($inst['area'] ?? '');
                    $campo = function_exists('mb_strtolower') ? mb_strtolower($campos, 'UTF-8') : strtolower($campos);
                    return strpos($campo, $needle) !== false;
                });
            }

            // recordsFiltered: instructores tras la búsqueda
            $recordsFiltered = count($instructoresMap);

            // Ordenamiento (columnas de DataTables)
            $columna = (int)($opciones['orden_columna'] ?? 5);
            $dir = (($opciones['orden_dir'] ?? 'asc') === 'desc') ? 'desc' : 'asc';
            $mapaOrden = [
                0 => 'nombre',
                1 => 'area',
                2 => 'horas_planta',
                3 => 'horas_transversales',
                4 => 'otras_horas',
                5 => 'total_horas',
                6 => 'total_horas'
            ];
            $campoOrden = $mapaOrden[$columna] ?? 'total_horas';

            $resultado = array_values($instructoresMap);
            usort($resultado, function ($a, $b) use ($campoOrden, $dir) {
                $va = $a[$campoOrden] ?? null;
                $vb = $b[$campoOrden] ?? null;
                if (is_numeric($va) && is_numeric($vb)) {
                    $cmp = ((float)$va < (float)$vb) ? -1 : (((float)$va > (float)$vb) ? 1 : 0);
                } else {
                    $cmp = strcmp((string)$va, (string)$vb);
                }
                if ($cmp === 0) {
                    $cmp = strcmp((string)($a['nombre'] ?? ''), (string)($b['nombre'] ?? ''));
                }
                return ($dir === 'desc') ? -$cmp : $cmp;
            });

            // Paginación: construir el detalle solo para la página solicitada
            $start = max(0, (int)($opciones['start'] ?? 0));
            $length = (int)($opciones['length'] ?? 20);
            if ($length < 0) {
                $length = $recordsFiltered;
            }

            $pagina = array_slice($resultado, $start, $length);

            foreach ($pagina as &$inst) {
                $key = $inst['id_instructor'];
                if (!empty($filasPorInstructor[$key])) {
                    foreach ($filasPorInstructor[$key] as $fila) {
                        $inst['detalle'][] = $this->construirItemDetalleHorario($fila['h'], $fila['duracion'], $fila['tipoHora']);
                    }
                }
            }
            unset($inst);

            return [
                'recordsTotal' => $recordsTotal,
                'recordsFiltered' => $recordsFiltered,
                'data' => $pagina
            ];
        } catch (Exception $e) {
            error_log("Error en getCargaHorariaInstructoresPaginado: " . $e->getMessage());
            return ['recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => []];
        }
    }

    /**
     * Obtiene el detalle completo de horas de un instructor
     */
    public function getDetalleHorasInstructor($idInstructor, $filtros = [])
    {
        $filtros['idusu_inst'] = $idInstructor;
        $carga = $this->getCargaHorariaInstructores($filtros);
        return !empty($carga[0]['detalle']) ? $carga[0]['detalle'] : [];
    }

    /**
     * Genera el reporte mensual de horas por instructor
     */
    public function getReporteMensualHoras($anio, $mes, $filtros = [])
    {
        $filtros['anio'] = $anio;
        $filtros['mes'] = $mes;
        unset($filtros['fecha_inicio'], $filtros['fecha_fin']);
        return $this->getCargaHorariaInstructores($filtros);
    }

    /**
     * Genera la matriz para el reporte anual de horas por instructor (12 meses)
     */
    public function getReporteAnualHoras($anio, $filtros = [])
    {
        try {
            $modelo = new Conexion();
            $conexion = $modelo->get_conexion();

            $idusu_coord = $filtros['idusu_coord'] ?? null;
            $idper = $filtros['idper'] ?? null;
            $idare = $filtros['idare'] ?? null;
            $idusu_inst = $filtros['idusu_inst'] ?? null;

            $areasPermitidas = $this->getAreasCoordinador($idusu_coord, $idper);
            $idsAreasPermitidas = array_column($areasPermitidas, 'idare');

            $sql = 'SELECT h.idhor, h.idusu, u.nomusu, u.ndocusu, a.nomare,
                           h.fecha_especifica, MONTH(h.fecha_especifica) AS mes_num,
                           h.hinihor, h.hfinhor, h.es_otros, h.horas_otros,
                           h.es_transversal, h.es_formacion_directa,
                           vj.nhora AS horas_jornada
                    FROM horario h
                    INNER JOIN ficha f ON h.idfic = f.idfic
                    LEFT JOIN programa p ON f.codpro = p.codpro
                    LEFT JOIN area a ON p.idare = a.idare
                    LEFT JOIN usuario u ON h.idusu = u.idusu
                    LEFT JOIN valor vj ON f.jornada = vj.idval
                    WHERE h.fecha_especifica IS NOT NULL
                    AND YEAR(h.fecha_especifica) = :anio';

            $params = [':anio' => (int)$anio];

            if (!empty($idare) && $idare !== 'todas') {
                if ($idper != 2 && !empty($idsAreasPermitidas) && !in_array($idare, $idsAreasPermitidas)) {
                    $inPlaceholders = implode(',', array_map('intval', $idsAreasPermitidas));
                    $sql .= " AND a.idare IN ($inPlaceholders)";
                } else {
                    $sql .= ' AND a.idare = :idare';
                    $params[':idare'] = $idare;
                }
            } elseif ($idper != 2 && !empty($idsAreasPermitidas)) {
                $inPlaceholders = implode(',', array_map('intval', $idsAreasPermitidas));
                $sql .= " AND (a.idare IN ($inPlaceholders) OR a.idare IS NULL)";
            }

            if (!empty($idusu_inst)) {
                $sql .= ' AND h.idusu = :idusu_inst';
                $params[':idusu_inst'] = $idusu_inst;
            }

            $stmt = $conexion->prepare($sql);
            foreach ($params as $param => $val) {
                $stmt->bindValue($param, $val);
            }
            $stmt->execute();
            $horarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $matriz = [];
            foreach ($horarios as $h) {
                $idusu = $h['idusu'] ? (int)$h['idusu'] : 0;
                $nomusu = $h['nomusu'] ?: 'Sin Instructor';
                $mesNum = (int)$h['mes_num'];

                if (!isset($matriz[$idusu])) {
                    $matriz[$idusu] = [
                        'idusu' => $idusu,
                        'nombre' => $nomusu,
                        'documento' => $h['ndocusu'] ?: '',
                        'area' => $h['nomare'] ?: 'General',
                        'meses' => array_fill(1, 12, 0.0),
                        'total_anual' => 0.0
                    ];
                }

                $duracion = 0.0;
                if (!empty($h['hinihor']) && !empty($h['hfinhor']) && $h['hinihor'] !== '00:00:00' && $h['hfinhor'] !== '00:00:00') {
                    $t1 = strtotime($h['hinihor']);
                    $t2 = strtotime($h['hfinhor']);
                    if ($t2 > $t1) {
                        $duracion = round(($t2 - $t1) / 3600, 2);
                    }
                }

                if ($duracion <= 0) {
                    if (!empty($h['es_otros']) && !empty($h['horas_otros']) && (float)$h['horas_otros'] > 0) {
                        $duracion = (float)$h['horas_otros'];
                    } elseif (!empty($h['horas_jornada'])) {
                        $duracion = (float)$h['horas_jornada'];
                    } else {
                        $duracion = 6.0;
                    }
                }

                if ($mesNum >= 1 && $mesNum <= 12) {
                    $matriz[$idusu]['meses'][$mesNum] += $duracion;
                    $matriz[$idusu]['total_anual'] += $duracion;
                }
            }

            $resultado = array_values($matriz);
            usort($resultado, function($a, $b) {
                return strcmp($a['nombre'], $b['nombre']);
            });

            return $resultado;
        } catch (Exception $e) {
            error_log("Error en getReporteAnualHoras: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene métricas consolidadas 100% reales para el Dashboard de Horarios y Agendas
     */
    public function getMetricasDashboard($filtros = [])
    {
        try {
            $carga = $this->getCargaHorariaInstructores($filtros);

            $totalHoras = 0.0;
            $totalPlanta = 0.0;
            $totalTransversales = 0.0;
            $totalOtros = 0.0;
            $cumplen = 0;
            $deficit = 0;
            $sobre = 0;
            $instructoresSet = [];
            $fichasSet = [];
            $aulasSet = [];

            foreach ($carga as $inst) {
                $totalHoras += (float)$inst['total_horas'];
                $totalPlanta += (float)$inst['horas_planta'];
                $totalTransversales += (float)$inst['horas_transversales'];
                $totalOtros += (float)$inst['otras_horas'];

                if ($inst['estado'] === 'Cumple' || $inst['estado'] === 'Cumple horas') {
                    $cumplen++;
                } elseif ($inst['estado'] === 'Sobre Carga' || $inst['estado'] === 'Sobre horas') {
                    $sobre++;
                } else {
                    $deficit++;
                }

                if (!empty($inst['idusu'])) {
                    $instructoresSet[$inst['idusu']] = true;
                }

                if (!empty($inst['detalle'])) {
                    foreach ($inst['detalle'] as $det) {
                        if (!empty($det['idfic'])) {
                            $fichasSet[$det['idfic']] = true;
                        }
                        if (!empty($det['aula']) && $det['aula'] !== 'Sin aula') {
                            $aulasSet[$det['aula']] = true;
                        }
                    }
                }
            }

            $totalInstructores = count($instructoresSet);
            $porcentajeCumplimiento = ($totalInstructores > 0) ? round(($cumplen / $totalInstructores) * 100, 1) : 0;

            return [
                'total_horas' => round($totalHoras, 1),
                'horas_planta' => round($totalPlanta, 1),
                'horas_transversales' => round($totalTransversales, 1),
                'horas_otros' => round($totalOtros, 1),
                'total_instructores' => $totalInstructores,
                'instructores_cumplen' => $cumplen,
                'instructores_deficit' => $deficit,
                'instructores_sobre' => $sobre,
                'porcentaje_cumplimiento' => $porcentajeCumplimiento,
                'total_fichas' => count($fichasSet),
                'total_aulas' => count($aulasSet)
            ];
        } catch (Exception $e) {
            error_log("Error en getMetricasDashboard: " . $e->getMessage());
            return [
                'total_horas' => 0,
                'horas_planta' => 0,
                'horas_transversales' => 0,
                'horas_otros' => 0,
                'total_instructores' => 0,
                'instructores_cumplen' => 0,
                'instructores_deficit' => 0,
                'instructores_sobre' => 0,
                'porcentaje_cumplimiento' => 0,
                'total_fichas' => 0,
                'total_aulas' => 0
            ];
        }
    }

    /**
     * Totales globales para el dashboard del coordinador:
     * areas registradas e instructores activos registrados (idper 7,32,42).
     * Se comparan en la vista contra areas/instructores asignados.
     */
    public function getTotalesAsignacion()
    {
        try {
            $modelo = new Conexion();
            $conexion = $modelo->get_conexion();
            $totalAreas = (int)$conexion->query('SELECT COUNT(*) FROM area')->fetchColumn();
            $stmt = $conexion->prepare('SELECT COUNT(DISTINCT u.idusu) FROM usuario u INNER JOIN usupef up ON u.idusu = up.idusu WHERE up.idper IN (7,32,42) AND u.actusu = 1');
            $stmt->execute();
            $totalInstructores = (int)$stmt->fetchColumn();
            return ['total_areas' => $totalAreas, 'total_instructores' => $totalInstructores];
        } catch (Exception $e) {
            error_log("Error en getTotalesAsignacion: " . $e->getMessage());
            return ['total_areas' => 0, 'total_instructores' => 0];
        }
    }

    /**
     * Información adicional del dashboard: fichas/programas activos y semanas/días asignados
     * para el mes actual, sin restricciones de área/ficha/instructor.
     */
    public function getInfoDashboardAdicional($mes, $anio)
    {
        try {
            $modelo = new Conexion();
            $conexion = $modelo->get_conexion();

            // Fichas con programa activo en el mes actual
            $sqlFichas = 'SELECT DISTINCT f.idfic, f.nomfic, p.codpro, p.nompro, ar.nomare
                          FROM horario h
                          INNER JOIN ficha f ON h.idfic = f.idfic
                          INNER JOIN programa p ON f.codpro = p.codpro
                          LEFT JOIN area ar ON p.idare = ar.idare
                          WHERE h.fecha_especifica IS NOT NULL
                            AND MONTH(h.fecha_especifica) = :mes
                            AND YEAR(h.fecha_especifica) = :anio
                          ORDER BY p.nompro, f.nomfic';
            $stmt = $conexion->prepare($sqlFichas);
            $stmt->bindValue(':mes', (int)$mes, PDO::PARAM_INT);
            $stmt->bindValue(':anio', (int)$anio, PDO::PARAM_INT);
            $stmt->execute();
            $fichas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Sumar semanas y días asignados en el mes actual
            $sqlSemanas = 'SELECT COUNT(DISTINCT WEEK(fecha_especifica, 1)) AS total_semanas,
                                  COUNT(DISTINCT iddia) AS total_dias
                           FROM horario
                           WHERE fecha_especifica IS NOT NULL
                             AND MONTH(fecha_especifica) = :mes
                             AND YEAR(fecha_especifica) = :anio';
            $stmt2 = $conexion->prepare($sqlSemanas);
            $stmt2->bindValue(':mes', (int)$mes, PDO::PARAM_INT);
            $stmt2->bindValue(':anio', (int)$anio, PDO::PARAM_INT);
            $stmt2->execute();
            $semanas = $stmt2->fetch(PDO::FETCH_ASSOC);

            // Total de horas del mes actual (suma de duración de todos los horarios)
            $sqlHoras = 'SELECT COALESCE(SUM(
                              CASE
                                  WHEN h.hinihor != "" AND h.hfinhor != "" 
                                       AND h.hinihor != "00:00:00" AND h.hfinhor != "00:00:00" 
                                       AND TIME(h.hfinhor) > TIME(h.hinihor)
                                  THEN TIMESTAMPDIFF(SECOND, h.hinihor, h.hfinhor) / 3600.0
                                  WHEN h.es_otros = 1 AND h.horas_otros > 0 THEN h.horas_otros
                                  WHEN h.horas_jornada > 0 THEN h.horas_jornada
                                  ELSE 6.0
                              END
                          ), 0) AS total_horas_mes
                       FROM horario h
                       WHERE h.fecha_especifica IS NOT NULL
                         AND MONTH(h.fecha_especifica) = :mes
                         AND YEAR(h.fecha_especifica) = :anio';
            $stmt3 = $conexion->prepare($sqlHoras);
            $stmt3->bindValue(':mes', (int)$mes, PDO::PARAM_INT);
            $stmt3->bindValue(':anio', (int)$anio, PDO::PARAM_INT);
            $stmt3->execute();
            $horasResult = $stmt3->fetch(PDO::FETCH_ASSOC);
            $totalHorasMes = round((float)($horasResult['total_horas_mes'] ?? 0), 1);

            // Horas por instructor en el mes actual
            $sqlHorasPorInst = 'SELECT h.idusu, u.nomusu,
                                       COALESCE(SUM(
                                           CASE
                                               WHEN h.hinihor != "" AND h.hfinhor != "" 
                                                    AND h.hinihor != "00:00:00" AND h.hfinhor != "00:00:00" 
                                                    AND TIME(h.hfinhor) > TIME(h.hinihor)
                                               THEN TIMESTAMPDIFF(SECOND, h.hinihor, h.hfinhor) / 3600.0
                                               WHEN h.es_otros = 1 AND h.horas_otros > 0 THEN h.horas_otros
                                               WHEN h.horas_jornada > 0 THEN h.horas_jornada
                                               ELSE 6.0
                                           END
                                       ), 0) AS total_horas
                                FROM horario h
                                INNER JOIN usuario u ON h.idusu = u.idusu
                                WHERE h.fecha_especifica IS NOT NULL
                                  AND MONTH(h.fecha_especifica) = :mes
                                  AND YEAR(h.fecha_especifica) = :anio
                                  AND u.actusu = 1
                                GROUP BY h.idusu, u.nomusu
                                ORDER BY total_horas DESC';
            $stmt4 = $conexion->prepare($sqlHorasPorInst);
            $stmt4->bindValue(':mes', (int)$mes, PDO::PARAM_INT);
            $stmt4->bindValue(':anio', (int)$anio, PDO::PARAM_INT);
            $stmt4->execute();
            $horasPorInst = $stmt4->fetchAll(PDO::FETCH_ASSOC);

            return [
                'fichas' => $fichas,
                'total_fichas' => count($fichas),
                'total_semanas' => (int)($semanas['total_semanas'] ?? 0),
                'total_dias' => (int)($semanas['total_dias'] ?? 0),
                'programas' => array_unique(array_column($fichas, 'nompro')),
                'total_horas_mes' => $totalHorasMes,
                'horas_por_instructor' => $horasPorInst,
                'total_instructores_con_horas' => count($horasPorInst),
            ];
        } catch (Exception $e) {
            error_log("Error en getInfoDashboardAdicional: " . $e->getMessage());
            return ['fichas' => [], 'total_fichas' => 0, 'total_semanas' => 0, 'total_dias' => 0, 'programas' => [], 'total_horas_mes' => 0, 'horas_por_instructor' => [], 'total_instructores_con_horas' => 0];
        }
    }

    /**
     * Obtiene la información detallada de una ficha, incluyendo su Programa
     * y su Proyecto Formativo (identificado desde la agenda o programa existente).
     */
    public function getInfoFicha($idfic)
    {
        try {
            $modelo = new Conexion();
            $conexion = $modelo->get_conexion();
            $sql = 'SELECT f.idfic, f.nomfic, v.nomval AS nomjornada, f.codpro, p.nompro, a.idare, a.nomare,
                           (SELECT ag.actpro FROM agenda ag WHERE ag.idfic = f.idfic AND ag.actpro IS NOT NULL AND ag.actpro != "" ORDER BY ag.idage ASC LIMIT 1) AS actpro_agenda,
                           (SELECT ag.fas FROM agenda ag WHERE ag.idfic = f.idfic AND ag.fas IS NOT NULL AND ag.fas != "" ORDER BY ag.idage ASC LIMIT 1) AS fase_agenda
                    FROM ficha AS f 
                    INNER JOIN valor AS v ON f.jornada = v.idval 
                    LEFT JOIN programa AS p ON f.codpro = p.codpro 
                    LEFT JOIN area AS a ON p.idare = a.idare 
                    WHERE f.idfic = :idfic LIMIT 1';
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(':idfic', $idfic);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                $row['proyecto_formativo'] = !empty($row['actpro_agenda']) 
                    ? $row['actpro_agenda'] 
                    : (!empty($row['nompro']) ? 'Proyecto Formativo - ' . $row['nompro'] : 'Proyecto Formativo Asignado');
            }
            return $row ?: null;
        } catch (Exception $e) {
            error_log("Error en getInfoFicha: " . $e->getMessage());
            return null;
        }
    }
}