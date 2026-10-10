<?php

class Mrphau
{
    //atributosidhor
    private $idhor;
    private $idfic;
    private $idaul;
    private $idusu;
    private $iddia;

    private $jornada;

    //metodos get
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
    public function getJornada()
    {
        return $this->jornada;
    }
    //metodos SET
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
    public function setJornada($jornada)
    {
        $this->jornada = $jornada;
    }
    //metodos publicos
    public function getAll()
    {
        $res = null;
        $sql = "SELECT h.idhor, f.idfic, f.jornada, a.idaul AS ai, u.idusu AS ui, u.nomusu AS un, h.iddia
		FROM horario AS h
		INNER JOIN ficha AS f ON h.idfic=f.idfic
		INNER JOIN aula AS a ON h.idaul=a.idaul
		INNER JOIN usuario AS u ON h.idusu=u.idusu";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->execute();
        $res = $result->fetchall(PDO::FETCH_ASSOC);
        return $res;
    }

    public function getAllaul()
    {
        $res = null;
        $sql = "SELECT * FROM aula";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->execute();
        $res = $result->fetchall(PDO::FETCH_ASSOC);
        return $res;
    }

    /**
     * Cabecera del aula: nombre, municipio, piso, bloque.
     * Usa LEFT JOIN para no fallar si el aula no tiene horarios aún.
     */
    public function getOne($idaul = null)
    {
        if ($idaul !== null) {
            $this->setIdaul($idaul);
        }
        $idaul = $this->getIdaul();
        $sql = "SELECT a.idaul AS ai, a.nomaul, a.piso, a.bloqau, a.taula,
                       a.codubi, ub.nomubi
                FROM aula AS a
                LEFT JOIN ubica AS ub ON a.codubi = ub.codubi
                WHERE a.idaul = :idaul";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->bindParam(":idaul", $idaul);
        $result->execute();
        return $result->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener el rango de fechas real registrado en horario para un aula.
     * $anio: si se pasa (ej. 2026), filtra SOLO ese año para no mezclar datos de años anteriores.
     * Sirve para detectar el período cuando no hay trimestre en calendario_academico.
     */
    public function getRangoFechasAula($idaul, $anio = null)
    {
        try {
            $params = [':idaul' => $idaul];

            // Columna de referencia de fecha efectiva
            $colFecha = "COALESCE(NULLIF(h.fecha_especifica,'0000-00-00'), NULLIF(h.fecha_inicio,'0000-00-00'))";

            $sql = "SELECT 
                        MIN($colFecha) AS fecha_min,
                        MAX(COALESCE(NULLIF(h.fecha_especifica,'0000-00-00'), NULLIF(h.fecha_fin,'0000-00-00'))) AS fecha_max
                    FROM horario h
                    WHERE h.idaul = :idaul";

            if ($anio !== null) {
                $sql .= " AND YEAR($colFecha) = :anio";
                $params[':anio'] = (int)$anio;
            }

            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->execute($params);
            return $result->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('Mrphau::getRangoFechasAula error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtener horarios de un aula dentro de un rango de fechas.
     * La jornada se calcula a partir de hinihor/hfinhor cuando están disponibles;
     * si no, se usa f.jornada. Si f.jornada no es 1, 2 ó 3 se asigna 1 (Mañana)
     * para que no quede invisible en la tabla.
     */
    public function getHorariosAulaEnRango($idaul, $fechaInicio, $fechaFin)
    {
        try {
            $sql = "SELECT 
                        h.idhor,
                        h.iddia,
                        h.idfic,
                        f.nomfic AS nf,
                        CASE 
                            WHEN h.hinihor IS NOT NULL AND h.hinihor != '00:00:00' THEN
                                CASE 
                                    WHEN h.hinihor >= '06:00:00' AND h.hinihor < '13:00:00' THEN 1
                                    WHEN h.hinihor >= '13:00:00' AND h.hinihor < '18:00:00' THEN 2
                                    WHEN h.hinihor >= '18:00:00' THEN 3
                                    ELSE 1
                                END
                            ELSE
                                CASE WHEN COALESCE(f.jornada,1) IN (1,2,3) THEN COALESCE(f.jornada,1) ELSE 1 END
                        END AS jornada,
                        u.nomusu AS un,
                        h.es_otros,
                        h.actividad,
                        h.horas_otros,
                        h.fecha_especifica,
                        h.fecha_inicio,
                        h.fecha_fin,
                        h.hinihor,
                        h.hfinhor
                    FROM horario h
                    LEFT JOIN ficha f ON h.idfic = f.idfic
                    LEFT JOIN usuario u ON h.idusu = u.idusu
                    WHERE h.idaul = :idaul";

            $params = [':idaul' => $idaul];

            if (!empty($fechaInicio) && !empty($fechaFin)) {
                $sql .= " AND (
                            (h.fecha_especifica IS NOT NULL AND h.fecha_especifica != '0000-00-00'
                             AND h.fecha_especifica != '' AND h.fecha_especifica BETWEEN :ini AND :fin)
                         OR (
                            (h.fecha_especifica IS NULL OR h.fecha_especifica = '0000-00-00' OR h.fecha_especifica = '')
                            AND h.fecha_inicio IS NOT NULL AND h.fecha_fin IS NOT NULL
                            AND h.fecha_inicio != '0000-00-00' AND h.fecha_fin != '0000-00-00'
                            AND h.fecha_inicio <= :fin AND h.fecha_fin >= :ini
                         )
                      )";
                $params[':ini'] = $fechaInicio;
                $params[':fin'] = $fechaFin;
            }

            $sql .= " ORDER BY h.iddia ASC, h.fecha_especifica ASC";

            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->execute($params);
            return $result->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('Mrphau::getHorariosAulaEnRango error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener pisos disponibles
     */
    public function getAllPiso()
    {
        try {
            $sql = "SELECT DISTINCT piso 
                    FROM aula 
                    WHERE piso IS NOT NULL AND piso != '' 
                    ORDER BY CAST(piso AS UNSIGNED) ASC, piso ASC";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->execute();
            return $result->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('Mrphau::getAllPiso error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener todas las aulas con información (piso, bloque, municipio)
     */
    public function getAllAulasConInfo($piso = null, $bloque = null)
    {
        try {
            $sql = "SELECT a.idaul, a.nomaul, a.piso, a.bloqau, a.taula, a.codubi, ub.nomubi
                    FROM aula AS a
                    LEFT JOIN ubica AS ub ON a.codubi = ub.codubi
                    WHERE 1=1";
            $params = [];
            if ($piso !== null && $piso !== '') {
                $sql .= " AND a.piso = :piso";
                $params[':piso'] = $piso;
            }
            if ($bloque !== null && $bloque !== '') {
                $sql .= " AND a.bloqau = :bloque";
                $params[':bloque'] = $bloque;
            }
            $sql .= " ORDER BY CAST(a.piso AS UNSIGNED) ASC, CAST(a.bloqau AS UNSIGNED) ASC, a.idaul ASC";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->execute($params);
            return $result->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('Mrphau::getAllAulasConInfo error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Consultar qué aulas tienen horarios registrados
     */
    public function getAulasConHorarios()
    {
        try {
            $sql = "SELECT DISTINCT idaul 
                    FROM horario 
                    WHERE idaul IS NOT NULL AND idaul > 0 
                    ORDER BY idaul ASC";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->execute();
            return $result->fetchAll(PDO::FETCH_COLUMN);
        } catch (Exception $e) {
            error_log('Mrphau::getAulasConHorarios error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener todos los trimestres del calendario académico
     */
    public function getAllTrimestres()
    {
        try {
            $sql = "SELECT id, titulo, descripcion, fecha_inicio, fecha_fin, tipo_evento, anio, trimestre 
                    FROM calendario_academico 
                    WHERE tipo_evento = 'trimestre'
                    ORDER BY anio DESC, fecha_inicio DESC";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->execute();
            return $result->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('Mrphau::getAllTrimestres error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener todas las aulas ordenadas
     */
    public function getAula()
    {
        try {
            $sql = "SELECT idaul, nomaul, piso, bloqau, codubi 
                    FROM aula 
                    ORDER BY CAST(piso AS UNSIGNED) ASC, bloqau ASC, idaul ASC";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->execute();
            return $result->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('Mrphau::getAula error: ' . $e->getMessage());
            return [];
        }
    }
}