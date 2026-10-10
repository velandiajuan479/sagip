<?php
/**
 * Modelo de Criterios de Evaluación.
 *
 * Tablas reales:
 * - criterio: datos del criterio.
 * - inseva: instrumento relacionado mediante criterio.idins.
 * - ficha: ficha del instrumento mediante inseva.idfic.
 * - usuario: usuario que registra mediante criterio.idusu.
 * - calcri: calificaciones que impiden borrar un criterio en uso.
 */

require_once __DIR__ . '/conexion.php';

class Hormcrev
{
    private $idcri;
    private $nomcri;
    private $vscum = 100;
    private $vpar = 50;
    private $vncum = 0;
    private $idins;
    private $idusu;

    public function getIdcri()
    {
        return $this->idcri;
    }

    public function getNomcri()
    {
        return $this->nomcri;
    }

    public function getVscum()
    {
        return $this->vscum;
    }

    public function getVpar()
    {
        return $this->vpar;
    }

    public function getVncum()
    {
        return $this->vncum;
    }

    public function getIdins()
    {
        return $this->idins;
    }

    public function getIdusu()
    {
        return $this->idusu;
    }

    public function setIdcri($value)
    {
        $this->idcri = ($value === '' || $value === null) ? null : (int)$value;
    }

    public function setNomcri($value)
    {
        $this->nomcri = trim((string)$value);
    }

    public function setVscum($value)
    {
        $this->vscum = (int)$value;
    }

    public function setVpar($value)
    {
        $this->vpar = (int)$value;
    }

    public function setVncum($value)
    {
        $this->vncum = (int)$value;
    }

    public function setIdins($value)
    {
        $this->idins = ($value === '' || $value === null) ? null : (int)$value;
    }

    public function setIdusu($value)
    {
        $this->idusu = ($value === '' || $value === null) ? null : (int)$value;
    }

    /**
     * Conexión estricta: cualquier error SQL debe llegar al controlador.
     */
    private function conexion()
    {
        $db = (new Conexion())->get_conexion();
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        return $db;
    }

    /**
     * Lista todos los criterios que correspondan al contexto solicitado.
     * No usa LIMIT ni trunca resultados en PHP.
     */
    public function getAllCriterios(array $filtros = [])
    {
        $db = $this->conexion();
        $sql = "SELECT
                    c.idcri,
                    c.nomcri,
                    c.vscum,
                    c.vpar,
                    c.vncum,
                    c.idins,
                    c.idusu,
                    COALESCE(NULLIF(TRIM(i.nomins), ''), CONCAT('Instrumento #', c.idins)) AS nomins,
                    i.idfic,
                    i.idres,
                    COALESCE(NULLIF(TRIM(f.nomfic), ''), IF(i.idfic IS NOT NULL, CONCAT('Ficha ', i.idfic), 'Sin ficha asignada')) AS nomfic,
                    COALESCE(NULLIF(TRIM(r.nomres), ''), 'Sin resultado de aprendizaje') AS nomres,
                    COALESCE(NULLIF(TRIM(au.nomusu), ''), 'Usuario no identificado') AS nomusu_registro,
                    (SELECT COUNT(*)
                       FROM calcri cal
                      WHERE cal.idcri = c.idcri) AS total_calificaciones
                FROM criterio c
                LEFT JOIN inseva i ON i.idins = c.idins
                LEFT JOIN ficha f ON f.idfic = i.idfic
                LEFT JOIN resultado r ON r.idres = i.idres
                LEFT JOIN usuario au ON au.idusu = c.idusu
                WHERE 1=1";

        $params = [];

        if (!empty($filtros['idcri'])) {
            $sql .= " AND c.idcri = :idcri";
            $params[':idcri'] = (int)$filtros['idcri'];
        }

        if (isset($filtros['idins']) && $filtros['idins'] !== '' && $filtros['idins'] !== null) {
            $sql .= " AND c.idins = :idins";
            $params[':idins'] = (int)$filtros['idins'];
        }

        if (isset($filtros['idfic']) && $filtros['idfic'] !== '' && $filtros['idfic'] !== null) {
            $sql .= " AND i.idfic = :idfic";
            $params[':idfic'] = (string)$filtros['idfic'];
        }

        if (!empty($filtros['buscar'])) {
            $sql .= " AND (c.nomcri LIKE :buscar OR i.nomins LIKE :buscar2 OR f.nomfic LIKE :buscar3 OR i.idfic LIKE :buscar4)";
            $params[':buscar'] = '%' . $filtros['buscar'] . '%';
            $params[':buscar2'] = $params[':buscar'];
            $params[':buscar3'] = $params[':buscar'];
            $params[':buscar4'] = $params[':buscar'];
        }

        $sql .= " ORDER BY c.idcri DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene un criterio por su clave primaria real.
     */
    public function getCriterioById($idcri)
    {
        $idcri = (int)$idcri;
        if ($idcri <= 0) {
            return null;
        }

        $db = $this->conexion();
        $stmt = $db->prepare(
            "SELECT c.idcri,
                    c.nomcri,
                    c.vscum,
                    c.vpar,
                    c.vncum,
                    c.idins,
                    c.idusu,
                    i.idfic,
                    i.idres,
                    COALESCE(NULLIF(TRIM(i.nomins), ''), CONCAT('Instrumento #', c.idins)) AS nomins,
                    COALESCE(NULLIF(TRIM(f.nomfic), ''), IF(i.idfic IS NOT NULL, CONCAT('Ficha ', i.idfic), 'Sin ficha asignada')) AS nomfic
               FROM criterio c
               LEFT JOIN inseva i ON i.idins = c.idins
               LEFT JOIN ficha f ON f.idfic = i.idfic
              WHERE c.idcri = :idcri"
        );
        $stmt->execute([':idcri' => $idcri]);
        $criterio = $stmt->fetch(PDO::FETCH_ASSOC);
        return $criterio ?: null;
    }

    /**
     * Catálogo completo de fichas; no se trunca el resultado.
     */
    public function getFichas()
    {
        $db = $this->conexion();
        $sql = "SELECT f.idfic,
                       f.nomfic,
                       (SELECT COUNT(*) FROM inseva i WHERE i.idfic = f.idfic) AS total_instrumentos
                  FROM ficha f
                 ORDER BY f.idfic DESC";
        return $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Catálogo completo de instrumentos y su peso actual.
     */
    public function getInstrumentos($idfic = null)
    {
        $db = $this->conexion();
        $sql = "SELECT i.idins,
                       i.nomins,
                       i.idres,
                       i.idfic,
                       i.idusu,
                       f.nomfic,
                       (SELECT COUNT(*) FROM criterio c WHERE c.idins = i.idins) AS total_criterios,
                       (SELECT COALESCE(SUM(c.vscum), 0) FROM criterio c WHERE c.idins = i.idins) AS peso_total
                  FROM inseva i
                  LEFT JOIN ficha f ON f.idfic = i.idfic";

        $params = [];
        if ($idfic !== null && $idfic !== '') {
            $sql .= " WHERE i.idfic = :idfic";
            $params[':idfic'] = (string)$idfic;
        }

        $sql .= " ORDER BY i.idins DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getInstrumentoById($idins)
    {
        $idins = (int)$idins;
        if ($idins <= 0) {
            return null;
        }

        $db = $this->conexion();
        $stmt = $db->prepare(
            "SELECT i.idins, i.nomins, i.idres, i.idfic, f.nomfic
               FROM inseva i
               LEFT JOIN ficha f ON f.idfic = i.idfic
              WHERE i.idins = :idins"
        );
        $stmt->execute([':idins' => $idins]);
        $instrumento = $stmt->fetch(PDO::FETCH_ASSOC);
        return $instrumento ?: null;
    }

    /**
     * Suma de vscum de un instrumento, opcionalmente excluyendo el criterio editado.
     */
    public function getTotalVscum($idins, $idcriExcluir = null)
    {
        $db = $this->conexion();
        $sql = "SELECT COALESCE(SUM(vscum), 0)
                  FROM criterio
                 WHERE idins = :idins";
        $params = [':idins' => (int)$idins];

        if ($idcriExcluir !== null) {
            $sql .= " AND idcri <> :idcri";
            $params[':idcri'] = (int)$idcriExcluir;
        }

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Inserta uno o varios criterios en una sola transacción.
     * idcri no se envía: lo genera AUTO_INCREMENT de MariaDB/MySQL.
     */
    public function saveBatch(array $criterios, $idins, $idusu)
    {
        $idins = (int)$idins;
        $idusu = (int)$idusu;

        if ($idins <= 0) {
            throw new DomainException('Seleccione un instrumento válido.');
        }
        if ($idusu <= 0) {
            throw new DomainException('No se pudo obtener el usuario de la sesión.');
        }
        if (empty($criterios)) {
            throw new DomainException('Debe registrar al menos un criterio.');
        }

        $filas = [];
        $sumaNueva = 0;

        foreach ($criterios as $criterio) {
            $nomcri = trim((string)($criterio['nomcri'] ?? ''));
            $vscum = filter_var($criterio['vscum'] ?? null, FILTER_VALIDATE_INT, [
                'options' => ['min_range' => 0, 'max_range' => 100]
            ]);
            $vpar = filter_var($criterio['vpar'] ?? null, FILTER_VALIDATE_INT, [
                'options' => ['min_range' => 0, 'max_range' => 100]
            ]);
            $vncum = filter_var($criterio['vncum'] ?? null, FILTER_VALIDATE_INT, [
                'options' => ['min_range' => 0, 'max_range' => 100]
            ]);
            $longitudNombre = function_exists('mb_strlen')
                ? mb_strlen($nomcri, 'UTF-8')
                : strlen($nomcri);

            if ($nomcri === '' || $longitudNombre > 255) {
                throw new DomainException('El nombre del criterio es obligatorio y admite máximo 255 caracteres.');
            }
            if ($vscum === false || $vpar === false || $vncum === false) {
                throw new DomainException('Los valores de evaluación deben ser números enteros entre 0 y 100.');
            }

            $filas[] = [
                'nomcri' => $nomcri,
                'vscum' => (int)$vscum,
                'vpar' => (int)$vpar,
                'vncum' => (int)$vncum
            ];
            $sumaNueva += (int)$vscum;
        }

        $db = $this->conexion();

        try {
            $db->beginTransaction();

            // Serializa los guardados de un mismo instrumento.
            $stmtInstrumento = $db->prepare("SELECT idins FROM inseva WHERE idins = :idins FOR UPDATE");
            $stmtInstrumento->execute([':idins' => $idins]);
            if (!$stmtInstrumento->fetchColumn()) {
                throw new DomainException('El instrumento seleccionado no existe en la base de datos.');
            }

            $stmtTotal = $db->prepare("SELECT COALESCE(SUM(vscum), 0) FROM criterio WHERE idins = :idins");
            $stmtTotal->execute([':idins' => $idins]);
            $sumaActual = (int)$stmtTotal->fetchColumn();
            $sumaFinal = $sumaActual + $sumaNueva;

            if ($sumaFinal !== 100) {
                throw new DomainException(
                    "La suma de vscum del instrumento debe ser exactamente 100. " .
                    "Valor actual: {$sumaActual}; valor enviado: {$sumaNueva}; total: {$sumaFinal}."
                );
            }

            $stmtInsert = $db->prepare(
                "INSERT INTO criterio (nomcri, vscum, vpar, vncum, idins, idusu)
                 VALUES (:nomcri, :vscum, :vpar, :vncum, :idins, :idusu)"
            );

            $idsGenerados = [];
            foreach ($filas as $fila) {
                $stmtInsert->execute([
                    ':nomcri' => $fila['nomcri'],
                    ':vscum' => $fila['vscum'],
                    ':vpar' => $fila['vpar'],
                    ':vncum' => $fila['vncum'],
                    ':idins' => $idins,
                    ':idusu' => $idusu
                ]);
                $idsGenerados[] = (int)$db->lastInsertId();
            }

            $db->commit();
            return $idsGenerados;
        } catch (Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Actualiza el registro existente identificado por idcri.
     * Nunca inserta un idcri nuevo y nunca cambia idins durante una edición.
     */
    public function edit()
    {
        if ($this->idcri === null || (int)$this->idcri <= 0) {
            throw new DomainException('El criterio seleccionado no tiene un idcri válido.');
        }

        $nomcri = trim((string)$this->nomcri);
        $longitudNombre = function_exists('mb_strlen')
            ? mb_strlen($nomcri, 'UTF-8')
            : strlen($nomcri);
        $vscum = filter_var($this->vscum, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 0, 'max_range' => 100]
        ]);
        $vpar = filter_var($this->vpar, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 0, 'max_range' => 100]
        ]);
        $vncum = filter_var($this->vncum, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 0, 'max_range' => 100]
        ]);

        if ($nomcri === '' || $longitudNombre > 255) {
            throw new DomainException('El nombre del criterio es obligatorio y admite máximo 255 caracteres.');
        }
        if ($vscum === false || $vpar === false || $vncum === false) {
            throw new DomainException('Los valores de evaluación deben ser números enteros entre 0 y 100.');
        }

        $db = $this->conexion();

        try {
            $stmtPreliminar = $db->prepare("SELECT idins FROM criterio WHERE idcri = :idcri");
            $stmtPreliminar->execute([':idcri' => (int)$this->idcri]);
            $idins = $stmtPreliminar->fetchColumn();

            if ($idins === false) {
                throw new DomainException('No se encontró el criterio solicitado para modificar.');
            }

            $db->beginTransaction();

            $stmtInstrumento = $db->prepare("SELECT idins FROM inseva WHERE idins = :idins FOR UPDATE");
            $stmtInstrumento->execute([':idins' => (int)$idins]);
            if (!$stmtInstrumento->fetchColumn()) {
                throw new DomainException('El instrumento relacionado con el criterio no existe.');
            }

            $stmtCriterio = $db->prepare("SELECT idcri FROM criterio WHERE idcri = :idcri FOR UPDATE");
            $stmtCriterio->execute([':idcri' => (int)$this->idcri]);
            if (!$stmtCriterio->fetchColumn()) {
                throw new DomainException('No se encontró el criterio solicitado para modificar.');
            }

            $stmtTotal = $db->prepare(
                "SELECT COALESCE(SUM(vscum), 0)
                   FROM criterio
                  WHERE idins = :idins
                    AND idcri <> :idcri"
            );
            $stmtTotal->execute([
                ':idins' => (int)$idins,
                ':idcri' => (int)$this->idcri
            ]);
            $sumaRestante = (int)$stmtTotal->fetchColumn();
            $sumaFinal = $sumaRestante + (int)$vscum;

            if ($sumaFinal !== 100) {
                throw new DomainException(
                    "La suma de vscum del instrumento debe ser exactamente 100. " .
                    "El valor final sería {$sumaFinal}."
                );
            }

            $stmtUpdate = $db->prepare(
                "UPDATE criterio
                    SET nomcri = :nomcri,
                        vscum = :vscum,
                        vpar = :vpar,
                        vncum = :vncum
                  WHERE idcri = :idcri"
            );
            $stmtUpdate->execute([
                ':nomcri' => $nomcri,
                ':vscum' => (int)$vscum,
                ':vpar' => (int)$vpar,
                ':vncum' => (int)$vncum,
                ':idcri' => (int)$this->idcri
            ]);

            $db->commit();
            return true;
        } catch (Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Elimina únicamente el criterio indicado.
     * Si ya tiene calificaciones, no borra información de calificaciones.
     */
    public function delete()
    {
        if ($this->idcri === null || (int)$this->idcri <= 0) {
            throw new DomainException('El criterio seleccionado no tiene un idcri válido.');
        }

        $db = $this->conexion();

        try {
            $db->beginTransaction();

            $stmtCriterio = $db->prepare("SELECT idcri FROM criterio WHERE idcri = :idcri FOR UPDATE");
            $stmtCriterio->execute([':idcri' => (int)$this->idcri]);
            if (!$stmtCriterio->fetchColumn()) {
                throw new DomainException('No se encontró el criterio solicitado para eliminar.');
            }

            $stmtCalificaciones = $db->prepare("SELECT COUNT(*) FROM calcri WHERE idcri = :idcri");
            $stmtCalificaciones->execute([':idcri' => (int)$this->idcri]);
            $totalCalificaciones = (int)$stmtCalificaciones->fetchColumn();

            if ($totalCalificaciones > 0) {
                throw new DomainException(
                    'El criterio tiene calificaciones asociadas y no puede eliminarse para no destruir información de calificaciones.'
                );
            }

            $stmtDelete = $db->prepare("DELETE FROM criterio WHERE idcri = :idcri");
            $stmtDelete->execute([':idcri' => (int)$this->idcri]);

            if ($stmtDelete->rowCount() !== 1) {
                throw new RuntimeException('La base de datos no eliminó el criterio seleccionado.');
            }

            $db->commit();
            return true;
        } catch (Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Métricas reales del módulo, sin valores ficticios.
     */
    public function getDashboardMetrics()
    {
        $db = $this->conexion();

        $totalCriterios = (int)$db->query("SELECT COUNT(*) FROM criterio")->fetchColumn();
        $instrumentosConCriterios = (int)$db->query(
            "SELECT COUNT(DISTINCT idins)
               FROM criterio
              WHERE idins IS NOT NULL"
        )->fetchColumn();
        $fichasConCriterios = (int)$db->query(
            "SELECT COUNT(DISTINCT i.idfic)
               FROM criterio c
               INNER JOIN inseva i ON i.idins = c.idins
              WHERE i.idfic IS NOT NULL
                AND i.idfic <> ''"
        )->fetchColumn();
        $calificacionesAsociadas = (int)$db->query("SELECT COUNT(*) FROM calcri")->fetchColumn();

        return [
            'total_criterios' => $totalCriterios,
            'instrumentos_con_criterios' => $instrumentosConCriterios,
            'fichas_con_criterios' => $fichasConCriterios,
            'calificaciones_asociadas' => $calificacionesAsociadas
        ];
    }
}
