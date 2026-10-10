<?php
// Modelo del módulo Juicio de Evaluación - hormjuev.php
// Patrón real del sistema: conexión PDO propia por método (new conexion()->get_conexion())
require_once(__DIR__ . "/conexion.php");

if (!function_exists('ManejoError')) {
    function ManejoError($e) {
        error_log("Error en Mjui: " . $e->getMessage());
    }
}

class Mjui {
    private $idjui;
    private $idusu;
    private $idfic;
    private $idres;
    private $idcom;
    private $caljui;
    private $ndoce;
    private $nomes;
    private $apees;
    private $estes;
    private $compe;
    private $resapr;
    private $tidoc;

    // ============ GETTERS ============
    public function getIdjui() { return $this->idjui; }
    public function getIdusu() { return $this->idusu; }
    public function getIdfic() { return $this->idfic; }
    public function getIdres() { return $this->idres; }
    public function getIdcom() { return $this->idcom; }
    public function getCaljui() { return $this->caljui; }
    public function getNdoce() { return $this->ndoce; }
    public function getNomes() { return $this->nomes; }
    public function getApees() { return $this->apees; }
    public function getEstes() { return $this->estes; }
    public function getCompe() { return $this->compe; }
    public function getResapr() { return $this->resapr; }
    public function getTidoc() { return $this->tidoc; }

    // ============ SETTERS ============
    public function setIdjui($idjui) { $this->idjui = $idjui; }
    public function setIdusu($idusu) { $this->idusu = $idusu; }
    public function setIdfic($idfic) { $this->idfic = $idfic; }
    public function setIdres($idres) { $this->idres = $idres; }
    public function setIdcom($idcom) { $this->idcom = $idcom; }
    public function setCaljui($caljui) { $this->caljui = $caljui; }
    public function setNdoce($ndoce) { $this->ndoce = $ndoce; }
    public function setNomes($nomes) { $this->nomes = $nomes; }
    public function setApees($apees) { $this->apees = $apees; }
    public function setEstes($estes) { $this->estes = $estes; }
    public function setCompe($compe) { $this->compe = $compe; }
    public function setResapr($resapr) { $this->resapr = $resapr; }
    public function setTidoc($tidoc) { $this->tidoc = $tidoc; }

    // ============ CONEXIÓN ============
    private function db() {
        return (new conexion())->get_conexion();
    }

    // Punto único de entrada de la conexión: en producción siempre devuelve una
    // conexión nueva (comportamiento original); en CLI y para pruebas se puede
    // fijar una conexión externa con setConexionExterna() para que varios métodos
    // compartan la misma transacción de prueba (con ROLLBACK, sin tocar la BD).
    private static $conexionExterna = null;
    public static function setConexionExterna($db) {
        if (PHP_SAPI === 'cli' && ($db === null || $db instanceof PDO)) {
            self::$conexionExterna = $db;
        }
    }
    private function cx() {
        if (PHP_SAPI === 'cli' && self::$conexionExterna instanceof PDO) {
            return self::$conexionExterna;
        }
        return $this->db();
    }

    private function dbTx() {
        return $this->cx();
    }

    // Genera el siguiente ID para tablas con PK manual (no AUTO_INCREMENT)
    private function nextId($tabla, $campo) {
        $db = $this->db();
        $stmt = $db->query("SELECT COALESCE(MAX(`$campo`),0)+1 AS nxt FROM `$tabla`");
        return (int)$stmt->fetchColumn();
    }

    // Igual que nextId(), pero pensado para usarse DENTRO de una
    // transacción ya abierta en $db, con FOR UPDATE para bloquear la
    // fila/rango y evitar que dos inserciones concurrentes calculen el
    // mismo "próximo" ID (nextId() por sí solo tiene esa condición de
    // carrera: dos peticiones simultáneas pueden leer el mismo MAX()
    // antes de que cualquiera inserte, y una de las dos inserciones
    // falla o pisa datos).
    private function nextIdLocked($db, $tabla, $campo) {
        if ($db->inTransaction()) {
            $stmt = $db->prepare("SELECT COALESCE(MAX(`$campo`),0)+1 AS nxt FROM `$tabla` FOR UPDATE");
            $stmt->execute();
            return (int)$stmt->fetchColumn();
        }
        return $this->nextId($tabla, $campo);
    }

    // ============ CONSULTA BASE DE JUICIOS (JOINS REALES) ============
    private function sqlBaseJuicio($where = '', $limite = 300) {
        $sql = "SELECT
            j.idjui, j.idusu, j.idfic, j.idres, j.idcom, j.caljui, j.ndocins,
            j.fecha_reporte AS fecre,
            j.fecha_importacion AS fecim,
            COALESCE(j.archivo_correlativo, '') AS arcor,
            j.fecjuieva AS fecjuieva,
            COALESCE(u.ndocusu, CONCAT('DOC-', j.idusu)) AS ndoce,
            COALESCE(SUBSTRING_INDEX(u.nomusu, ' ', 2), CONCAT('Estudiante ', j.idusu)) AS nomes,
            COALESCE(SUBSTRING_INDEX(u.nomusu, ' ', -2), '') AS apees,
            COALESCE(u.nomusu, CONCAT('Estudiante ', j.idusu)) AS nomusu,
            '' AS estes,
            COALESCE(f.nomfic, 'Sin ficha') AS nomfic,
            f.mun, f.finific, f.ffinfic,
            COALESCE(r.nomres, 'Sin resultado') AS resapr,
            r.ndeses,
            COALESCE(c.descom, 'Sin competencia') AS compe,
            ar.idaprob, ar.idinstructor,
            COALESCE(ar.estado, '') AS estado,
            ar.fecha_aprobacion AS fecju,
            COALESCE(func.nomusu, '') AS funre
        FROM juicio j
        LEFT JOIN usuario u ON j.idusu = u.idusu
        LEFT JOIN ficha f ON j.idfic = f.idfic
        LEFT JOIN resultado r ON j.idres = r.idres
        LEFT JOIN competencia c ON j.idcom = c.idcom
        LEFT JOIN aprobacion_resultado ar ON (j.idfic = ar.idfic AND j.idres = ar.idres)
        LEFT JOIN usuario func ON j.ndocins = func.ndocusu";
        if ($where !== '') {
            $sql .= " WHERE " . $where;
        }
        $sql .= " ORDER BY j.idjui DESC";
        if ($limite > 0) {
            $sql .= " LIMIT " . (int)$limite;
        }
        return $sql;
    }

    /**
     * Búsqueda paginada y filtrada para la tabla de juicios
     */
    public function getJuiciosFiltrados($filtros = [], $page = 1, $limite = 10) {
        try {
            $db = $this->db();
            $where = [];
            $params = [];

            if (!empty($filtros['idfic'])) {
                $where[] = "j.idfic = :idfic";
                $params[':idfic'] = $filtros['idfic'];
            }
            if (!empty($filtros['idcom'])) {
                $where[] = "j.idcom = :idcom";
                $params[':idcom'] = (int)$filtros['idcom'];
            }
            // Filtro por calificación: 'POR EVALUAR' incluye NULL y valores que no son APROBADO/NO APROBADO
            if (!empty($filtros['caljui'])) {
                $calFiltro = strtoupper(trim($filtros['caljui']));
                if ($calFiltro === 'POR EVALUAR') {
                    $where[] = "(j.caljui IS NULL OR (UPPER(j.caljui) NOT IN ('APROBADO','NO APROBADO')))";
                } else {
                    $where[] = "UPPER(j.caljui) = :caljui";
                    $params[':caljui'] = $calFiltro;
                }
            }
            // Filtro por documento del instructor (campo exacto)
            if (!empty($filtros['ndocins'])) {
                $where[] = "j.ndocins = :ndocins";
                $params[':ndocins'] = $filtros['ndocins'];
            }
            // Búsqueda libre: busca en doc instructor, nombre instructor, resultado, competencia, ficha, idusu
            if (!empty($filtros['busqueda'])) {
                $busqRaw = trim($filtros['busqueda']);
                $busq = '%' . $busqRaw . '%';
                $where[] = "(j.ndocins LIKE :b1 OR func.nomusu LIKE :b2 OR r.nomres LIKE :b3 OR c.descom LIKE :b4 OR j.idfic LIKE :b5 OR CAST(j.idusu AS CHAR) LIKE :b6 OR u.ndocusu LIKE :b7 OR u.nomusu LIKE :b8 OR CONCAT('DOC-',j.idusu) LIKE :b9)";
                $params[':b1'] = $busq;
                $params[':b2'] = $busq;
                $params[':b3'] = $busq;
                $params[':b4'] = $busq;
                $params[':b5'] = $busq;
                $params[':b6'] = $busq;
                $params[':b7'] = $busq;
                $params[':b8'] = $busq;
                $params[':b9'] = $busq;
            }

            $whereSql = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

            // Total count
            $countSql = "SELECT COUNT(*) FROM juicio j
                         LEFT JOIN usuario u ON j.idusu = u.idusu
                         LEFT JOIN resultado r ON j.idres = r.idres
                         LEFT JOIN competencia c ON j.idcom = c.idcom
                         LEFT JOIN usuario func ON j.ndocins = func.ndocusu
                         $whereSql";
            $countStmt = $db->prepare($countSql);
            $countStmt->execute($params);
            $total = (int)$countStmt->fetchColumn();

            // Paged data
            $offset = max(0, ($page - 1) * $limite);
            $dataSql = "SELECT
                j.idjui, j.idusu, j.idfic, j.idres, j.idcom, j.caljui, j.ndocins,
                j.fecha_reporte, j.fecha_importacion, j.archivo_correlativo, j.fecjuieva,
                COALESCE(u.ndocusu, CONCAT('ID:', j.idusu)) AS ndoce,
                COALESCE(u.nomusu, CONCAT('Aprendiz-', j.idusu)) AS nomes,
                '' AS apees,
                COALESCE(u.nomusu, CONCAT('Aprendiz-', j.idusu)) AS nomusu,
                COALESCE(f.nomfic, 'Sin ficha') AS nomfic,
                COALESCE(r.nomres, 'Sin resultado') AS resapr,
                COALESCE(c.descom, 'Sin competencia') AS descom,
                COALESCE(c.descom, 'Sin competencia') AS compe,
                j.ndocins AS ndoc_instructor,
                COALESCE(func.nomusu, j.ndocins) AS nom_instructor,
                CASE
                    WHEN UPPER(j.caljui) = 'APROBADO' THEN 'APROBADO'
                    WHEN UPPER(j.caljui) = 'NO APROBADO' THEN 'NO APROBADO'
                    WHEN j.caljui IS NULL OR UPPER(j.caljui) NOT IN ('APROBADO','NO APROBADO') THEN 'POR EVALUAR'
                    ELSE j.caljui
                END AS caljui_texto
            FROM juicio j
            LEFT JOIN usuario u ON j.idusu = u.idusu
            LEFT JOIN ficha f ON j.idfic = f.idfic
            LEFT JOIN resultado r ON j.idres = r.idres
            LEFT JOIN competencia c ON j.idcom = c.idcom
            LEFT JOIN usuario func ON j.ndocins = func.ndocusu
            $whereSql
            ORDER BY j.idjui DESC
            LIMIT " . (int)$limite . " OFFSET " . (int)$offset;

            $dataStmt = $db->prepare($dataSql);
            $dataStmt->execute($params);
            $rows = $dataStmt->fetchAll(PDO::FETCH_ASSOC);

            // Normalizar caljui para mostrar
            foreach ($rows as &$row) {
                $row['caljui'] = $row['caljui_texto'];
                unset($row['caljui_texto']);
            }
            unset($row);

            return [
                'data' => $rows,
                'total' => $total,
                'total_paginas' => max(1, (int)ceil($total / $limite)),
                'pagina' => $page
            ];
        } catch (Exception $e) {
            ManejoError($e);
            return ['data' => [], 'total' => 0, 'total_paginas' => 1, 'pagina' => 1];
        }
    }


// ============ CRUD ============

    /**
     * Lista de juicios con joins. Opcionalmente filtra por ndocins (documento del instructor).
     */
    public function getAll($ndocins = null) {
        try {
            $db = $this->db();
            $where = '';
            $params = [];
            if (!empty($ndocins)) {
                $where = "j.ndocins = :ndocins";
                $params[':ndocins'] = $ndocins;
            }
            $stmt = $db->prepare($this->sqlBaseJuicio($where, 300));
            $stmt->execute($params);
            $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (empty($res) && !empty($ndocins)) {
                // Fallback: si el usuario no tiene juicios asignados a su cédula, traer los registros globales
                $stmt = $db->prepare($this->sqlBaseJuicio('', 300));
                $stmt->execute();
                $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            return $res;
        } catch (Exception $e) {
            ManejoError($e);
            return [];
        }
    }

    /**
     * Obtiene un juicio por idjui (usa setIdjui antes de llamar).
     */
    public function getOne() {
        try {
            $stmt = $this->db()->prepare($this->sqlBaseJuicio("j.idjui = :idjui", 1));
            $stmt->execute([':idjui' => $this->getIdjui()]);
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            return $res ?: null;
        } catch (Exception $e) {
            ManejoError($e);
            return null;
        }
    }

    /**
     * Inserta un juicio respetando los campos NOT NULL reales de la tabla.
     * $datos: idusu, idfic, idres, idcom, caljui, ndocins, fecha_reporte, fecjuieva, archivo_correlativo
     * NOTA: juicio.idjui es AUTO_INCREMENT (ver SHOW CREATE TABLE juicio), por lo
     * que se omite de la columna INSERT y se deja que MySQL genere el valor.
     * Esto evita la condición de carrera del cálculo MAX(idjui)+1 y respeta el flujo
     * real ficha -> resultado -> competencia sin generar IDs manuales.
     */
    public function save($datos) {
        try {
            $db = $this->db();
            $fechaReporte = !empty($datos['fecha_reporte']) ? $datos['fecha_reporte'] : date('Y-m-d');
            $fecjuieva   = !empty($datos['fecjuieva']) ? $datos['fecjuieva'] : date('Y-m-d');
            $caljui      = strtoupper(trim($datos['caljui'] ?? 'POR EVALUAR'));
            $ndocins     = (string)($datos['ndocins'] ?? '');

            $stmt = $db->prepare("INSERT INTO juicio
                (idusu, idfic, idres, idcom, caljui, ndocins, fecha_reporte, fecha_importacion, fecjuieva, archivo_correlativo)
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?)");
            $ok = $stmt->execute([
                (int)$datos['idusu'],
                $datos['idfic'],
                (int)$datos['idres'],
                (int)$datos['idcom'],
                $caljui,
                $ndocins,
                $fechaReporte,
                $fecjuieva,
                $datos['archivo_correlativo'] ?? null
            ]);
            return $ok;
        } catch (Exception $e) {
            ManejoError($e);
            return false;
        }
    }

    /**
     * Actualiza un juicio existente.
     * $datos: idusu, idfic, idres, idcom, caljui, ndocins, fecha_reporte, fecjuieva
     */
    public function edit($idjui, $datos) {
        try {
            $db = $this->db();
            $caljui   = strtoupper(trim($datos['caljui'] ?? ''));
            $ndocins  = (string)($datos['ndocins'] ?? '');
            $fecju    = !empty($datos['fecjuieva']) ? $datos['fecjuieva'] : date('Y-m-d');
            $stmt = $db->prepare("UPDATE juicio SET
                    idusu = ?, idfic = ?, idres = ?, idcom = ?, caljui = ?, ndocins = ?, fecha_reporte = ?, fecjuieva = ?
                WHERE idjui = ?");
            return $stmt->execute([
                (int)$datos['idusu'],
                $datos['idfic'],
                (int)$datos['idres'],
                (int)$datos['idcom'],
                $caljui,
                $ndocins,
                !empty($datos['fecha_reporte']) ? $datos['fecha_reporte'] : date('Y-m-d'),
                $fecju,
                (int)$idjui
            ]);
        } catch (Exception $e) {
            ManejoError($e);
            return false;
        }
    }

    /**
     * Elimina un juicio por idjui.
     */
    public function del($idjui) {
        try {
            $stmt = $this->db()->prepare("DELETE FROM juicio WHERE idjui = ?");
            return $stmt->execute([(int)$idjui]);
        } catch (Exception $e) {
            ManejoError($e);
            return false;
        }
    }

    /**
     * Edición rápida: solo cambia la calificación y sincroniza aprobacion_resultado.
     */
    public function actualizarJuicio($idjui, $caljui) {
        try {
            $db = $this->db();
            $db->beginTransaction();
            $caljui = strtoupper(trim($caljui));
            $stmt = $db->prepare("UPDATE juicio SET caljui = ? WHERE idjui = ?");
            $ok = $stmt->execute([$caljui, (int)$idjui]);
            if (!$ok) {
                $db->rollBack();
                return false;
            }
            // Sincronizar aprobacion_resultado si el juicio tiene relación.
            // INTEGRIDAD: aprobacion_resultado.idinstructor es el idusu del INSTRUCTOR
            // (juicio.ndocins -> usuario.ndocusu), nunca el idusu del aprendiz. Antes se
            // enviaba $j['idusu'] (aprendiz), por lo que la aprobación quedaba ligada al
            // aprendiz en lugar del instructor que evaluó.
            // NOTA: $j['ndocins'] se lee AQUÍ y se conserva en $ndocinsJuicio, porque
            // $j se reutiliza abajo y getIdusuInstructorTx() abre su propia consulta.
            $stmt = $db->prepare("SELECT idfic, idres, idusu, ndocins FROM juicio WHERE idjui = ?");
            $stmt->execute([(int)$idjui]);
            $j = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($j) {
                $ndocinsJuicio = trim((string)$j['ndocins']);
                $idinstructor = null;
                if ($ndocinsJuicio !== '') {
                    $stIns = $db->prepare("SELECT idusu FROM usuario WHERE ndocusu = ? LIMIT 1");
                    $stIns->execute([$ndocinsJuicio]);
                    $idIns = $stIns->fetchColumn();
                    $idinstructor = $idIns ? (int)$idIns : null;
                }
                $this->sincronizarAprobacion($db, $j['idfic'], $j['idres'], $idinstructor, $caljui);
            }
            $db->commit();
            return true;
        } catch (Exception $e) {
            if (isset($db)) { $db->rollBack(); }
            ManejoError($e);
            return false;
        }
    }
// ============ APROBACIÓN RESULTADO ============

    /**
     * Resuelve el idusu del instructor a partir del documento guardado en juicio.ndocins.
     * Devuelve null cuando el documento no corresponde a ningún usuario (en ese caso la
     * aprobación NO debe reescribir idinstructor con un id que no sea de instructor).
     */
    private function getIdusuInstructor($ndocins) {
        $ndocins = trim((string)$ndocins);
        if ($ndocins === '') { return null; }
        try {
            $stmt = $this->db()->prepare("SELECT idusu FROM usuario WHERE ndocusu = ? LIMIT 1");
            $stmt->execute([$ndocins]);
            $id = $stmt->fetchColumn();
            return $id ? (int)$id : null;
        } catch (Exception $e) {
            ManejoError($e);
            return null;
        }
    }

    /**
     * Crea o actualiza el registro de aprobacion_resultado según la calificación del juicio.
     * La tabla real no tiene columna ndocins: usamos idinstructor.
     * Si no se conoce el instructor real, se actualiza solo el estado (sin tocar idinstructor).
     */
    private function sincronizarAprobacion($db, $idfic, $idres, $idinstructor, $caljui) {
        try {
            $estado = 'pendiente';
            if (strtoupper($caljui) === 'APROBADO') {
                $estado = 'aprobado';
            } elseif (strtoupper($caljui) === 'NO APROBADO') {
                $estado = 'no aprobado';
            }
            $stmt = $db->prepare("SELECT idaprob FROM aprobacion_resultado WHERE idfic = ? AND idres = ?");
            $stmt->execute([$idfic, $idres]);
            if ($stmt->rowCount() > 0) {
                if (!empty($idinstructor)) {
                    $stmt = $db->prepare("UPDATE aprobacion_resultado SET estado = ?, idinstructor = ?, fecha_aprobacion = ? WHERE idfic = ? AND idres = ?");
                    return $stmt->execute([$estado, (int)$idinstructor, date('Y-m-d H:i:s'), $idfic, $idres]);
                }
                $stmt = $db->prepare("UPDATE aprobacion_resultado SET estado = ?, fecha_aprobacion = ? WHERE idfic = ? AND idres = ?");
                return $stmt->execute([$estado, date('Y-m-d H:i:s'), $idfic, $idres]);
            }
            if (empty($idinstructor)) {
                return true;
            }
            $stmt = $db->prepare("INSERT INTO aprobacion_resultado (idfic, idres, idinstructor, estado, fecha_aprobacion)
                                  VALUES (?, ?, ?, ?, ?)");
            return $stmt->execute([$idfic, $idres, (int)$idinstructor, $estado, date('Y-m-d H:i:s')]);
        } catch (Exception $e) {
            ManejoError($e);
            return false;
        }
    }

    /**
     * API pública: crea/actualiza la aprobación de un resultado.
     * aprobacion_resultado.idaprob es AUTO_INCREMENT, se omite del INSERT.
     */
    public function crearAprobacionResultado($idfic, $idres, $idinstructor, $estado = 'pendiente', $fecha_aprobacion = null) {
        try {
            $db = $this->db();
            $stmt = $db->prepare("SELECT idaprob FROM aprobacion_resultado WHERE idfic = ? AND idres = ?");
            $stmt->execute([$idfic, $idres]);
            if ($stmt->rowCount() > 0) {
                $stmt = $db->prepare("UPDATE aprobacion_resultado SET estado = ?, idinstructor = ?, fecha_aprobacion = ? WHERE idfic = ? AND idres = ?");
                return $stmt->execute([$estado, (int)$idinstructor, $fecha_aprobacion ?: date('Y-m-d H:i:s'), $idfic, $idres]);
            }
            if (empty($idinstructor)) {
                return true;
            }
            $stmt = $db->prepare("INSERT INTO aprobacion_resultado (idfic, idres, idinstructor, estado, fecha_aprobacion)
                                  VALUES (?, ?, ?, ?, ?)");
            return $stmt->execute([$idfic, $idres, (int)$idinstructor, $estado, $fecha_aprobacion ?: date('Y-m-d H:i:s')]);
        } catch (Exception $e) {
            ManejoError($e);
            return false;
        }
    }

    // ============ CONSULTAS DE APOYO AL FORMULARIO ============

    public function getAllUsu() {
        try {
            $stmt = $this->db()->prepare("SELECT idusu, ndocusu, nomusu FROM usuario WHERE actusu = 1 ORDER BY nomusu");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            ManejoError($e);
            return [];
        }
    }

    public function getAllfic() {
        try {
            $stmt = $this->db()->prepare("SELECT idfic, nomfic, codpro FROM ficha ORDER BY idfic");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            ManejoError($e);
            return [];
        }
    }

    public function getAllRes() {
        try {
            $stmt = $this->db()->prepare("SELECT idres, nomres FROM resultado ORDER BY idres");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            ManejoError($e);
            return [];
        }
    }
public function getCompetenciasTodas($limite = 200) {
        try {
            $stmt = $this->db()->prepare("SELECT idcom, descom FROM competencia ORDER BY idcom LIMIT " . (int)$limite);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            ManejoError($e);
            return [];
        }
    }

    public function getResultadosTodas($limite = 300) {
        try {
            $stmt = $this->db()->prepare("SELECT idres, nomres FROM resultado ORDER BY idres LIMIT " . (int)$limite);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            ManejoError($e);
            return [];
        }
    }

    public function getCompetenciasPorInstructor($idusu) {
        try {
            $db = $this->db();
            $stmt = $db->prepare("SELECT ndocusu FROM usuario WHERE idusu = ?");
            $stmt->execute([(int)$idusu]);
            $ndoc = $stmt->fetchColumn();
            if (!$ndoc) {
                return $this->getCompetenciasTodas();
            }
            $stmt = $db->prepare("SELECT DISTINCT c.idcom, c.descom AS compe
                                  FROM juicio j
                                  LEFT JOIN competencia c ON j.idcom = c.idcom
                                  WHERE j.ndocins = ? AND c.idcom IS NOT NULL
                                  ORDER BY c.descom LIMIT 200");
            $stmt->execute([$ndoc]);
            $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return !empty($res) ? $res : $this->getCompetenciasTodas();
        } catch (Exception $e) {
            ManejoError($e);
            return [];
        }
    }

    /**
     * Lista de competencias VÁLIDAMENTE relacionadas con juicios (competencia -> juicio via j.idcom).
     * No es todas las de la tabla competencia, solo las que tienen al menos un juicio.
     * Usada para el filtro de Horarios y Agendas sin mezclar idcom/idres.
     */
    public function getCompetenciasConJuicios($limite = 200) {
        try {
            $stmt = $this->db()->prepare("SELECT DISTINCT c.idcom, c.descom
                                          FROM juicio j
                                          INNER JOIN competencia c ON j.idcom = c.idcom
                                          WHERE c.idcom IS NOT NULL
                                          ORDER BY c.descom LIMIT " . (int)$limite);
            $stmt->execute();
            $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
            // Normaliza clave compe para compatibilidad con vista (compe/descom)
            foreach ($res as &$r) { $r['compe'] = $r['descom']; }
            unset($r);
            return $res;
        } catch (Exception $e) {
            ManejoError($e);
            return [];
        }
    }

    public function getResultadosPorInstructor($idusu) {
        try {
            $db = $this->db();
            $stmt = $db->prepare("SELECT ndocusu FROM usuario WHERE idusu = ?");
            $stmt->execute([(int)$idusu]);
            $ndoc = $stmt->fetchColumn();
            if (!$ndoc) {
                return $this->getResultadosTodas();
            }
            $stmt = $db->prepare("SELECT DISTINCT r.idres, r.nomres AS resapr
                                  FROM juicio j
                                  LEFT JOIN resultado r ON j.idres = r.idres
                                  WHERE j.ndocins = ? AND r.idres IS NOT NULL
                                  ORDER BY r.nomres LIMIT 300");
            $stmt->execute([$ndoc]);
            $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return !empty($res) ? $res : $this->getResultadosTodas();
        } catch (Exception $e) {
            ManejoError($e);
            return [];
        }
    }

    public function validarRelacionCompetenciaResultado($idcom, $idres) {
        try {
            $stmt = $this->db()->prepare("SELECT COUNT(*) FROM resultado WHERE idres = ? AND idcom = ?");
            $stmt->execute([$idres, $idcom]);
            return $stmt->fetchColumn() > 0;
        } catch (Exception $e) {
            ManejoError($e);
            return false;
        }
    }

    public function getResultadosPorCompetencia($idcom) {
        try {
            $stmt = $this->db()->prepare("SELECT idres, nomres FROM resultado WHERE idcom = ? ORDER BY nomres LIMIT 300");
            $stmt->execute([$idcom]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            ManejoError($e);
            return [];
        }
    }
public function extraerCompetencia($competencia) {
        $partes = explode(' - ', (string)$competencia, 2);
        return [
            'idcom'  => trim($partes[0]),
            'descom' => isset($partes[1]) ? trim($partes[1]) : ''
        ];
    }

    public function extraerResultado($resultado) {
        $partes = explode(' - ', (string)$resultado, 2);
        return [
            'idres'  => trim($partes[0]),
            'nomres' => isset($partes[1]) ? trim($partes[1]) : ''
        ];
    }

    public function getCompetenciaDeResultado($idres) {
        try {
            $stmt = $this->db()->prepare("SELECT idcom FROM resultado WHERE idres = ? LIMIT 1");
            $stmt->execute([(int)$idres]);
            return $stmt->fetchColumn() ?: null;
        } catch (Exception $e) {
            ManejoError($e);
            return null;
        }
    }

    /**
     * Indica si el resultado de aprendizaje ya existe como dato maestro.
     * Permite distinguir "resultado inexistente" de "resultado existente con idcom NULL"
     * (getCompetenciaDeResultado() devuelve null en ambos casos).
     */
    public function resultadoExiste($idres) {
        try {
            $stmt = $this->db()->prepare("SELECT 1 FROM resultado WHERE idres = ? LIMIT 1");
            $stmt->execute([(int)$idres]);
            return (bool)$stmt->fetchColumn();
        } catch (Exception $e) {
            ManejoError($e);
            return false;
        }
    }

    public function buscarJuicioPorIdusuIdres($idusu, $idres) {
        try {
            $stmt = $this->db()->prepare("SELECT j.idjui, j.idusu, j.idfic, j.idres, j.idcom, j.caljui, j.ndocins
                                          FROM juicio j WHERE j.idusu = ? AND j.idres = ? LIMIT 1");
            $stmt->execute([(int)$idusu, (int)$idres]);
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            return $res ?: null;
        } catch (Exception $e) {
            ManejoError($e);
            return null;
        }
    }

    public function buscarJuicioPorDocumento($ndoce) {
        try {
            $ndoceLimpio = trim(str_ireplace('DOC-', '', $ndoce));
            $sql = $this->sqlBaseJuicio("(u.ndocusu = :nd1 OR j.idusu = :id1 OR j.ndocins = :nd2)", 1);
            $stmt = $this->db()->prepare($sql);
            $stmt->execute([
                ':nd1' => $ndoce,
                ':id1' => is_numeric($ndoceLimpio) ? (int)$ndoceLimpio : 0,
                ':nd2' => $ndoce
            ]);
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            return $res ?: null;
        } catch (Exception $e) {
            ManejoError($e);
            return null;
        }
    }
// ============ INFORMACIÓN AUXILIAR (FICHAS, USUARIOS) ============

    public function getDocumentoUsuario($idusu) {
        try {
            $stmt = $this->db()->prepare("SELECT ndocusu FROM usuario WHERE idusu = ? LIMIT 1");
            $stmt->execute([(int)$idusu]);
            return $stmt->fetchColumn();
        } catch (Exception $e) {
            ManejoError($e);
            return null;
        }
    }

    public function buscarUsuarioPorDocumento($ndoce) {
        try {
            $stmt = $this->db()->prepare("SELECT idusu FROM usuario WHERE ndocusu = :ndoce LIMIT 1");
            $stmt->bindParam(':ndoce', $ndoce);
            $stmt->execute();
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            return $usuario ? $usuario['idusu'] : null;
        } catch (Exception $e) {
            ManejoError($e);
            return null;
        }
    }

    public function cargarInfoFicha($idfic) {
        try {
            $stmt = $this->db()->prepare("SELECT f.*, p.nompro, p.despro, p.verpro
                                          FROM ficha f
                                          LEFT JOIN programa p ON f.codpro = p.codpro
                                          WHERE f.idfic = ? LIMIT 1");
            $stmt->execute([$idfic]);
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            return $res ?: null;
        } catch (Exception $e) {
            ManejoError($e);
            return null;
        }
    }

    public function cargarInfoUsuario($idusu) {
        try {
            $stmt = $this->db()->prepare("SELECT idusu, ndocusu, nomusu, idper FROM usuario WHERE idusu = ? LIMIT 1");
            $stmt->execute([(int)$idusu]);
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            return $res ?: null;
        } catch (Exception $e) {
            ManejoError($e);
            return null;
        }
    }

    public function cargarInfoUsuarioPorDocumento($ndoce) {
        try {
            $ndoceLimpio = trim(str_ireplace('DOC-', '', $ndoce));
            // 1. Buscar en tabla usuario por ndocusu o idusu con su ficha en usufic
            $stmt = $this->db()->prepare("SELECT u.idusu,
                                          SUBSTRING_INDEX(u.nomusu, ' ', 2) AS nomes,
                                          SUBSTRING_INDEX(u.nomusu, ' ', -2) AS apees,
                                          u.nomusu,
                                          u.ndocusu,
                                          uf.idfic
                                          FROM usuario u
                                          LEFT JOIN usufic uf ON u.idusu = uf.idusu
                                          WHERE u.ndocusu = ? OR u.idusu = ? 
                                          LIMIT 1");
            $stmt->execute([$ndoce, is_numeric($ndoceLimpio) ? (int)$ndoceLimpio : 0]);
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($res) {
                return $res;
            }

            // 2. Buscar en tabla juicio por idusu o ndocins
            $stmt = $this->db()->prepare("SELECT j.idusu, j.idfic, j.ndocins,
                                          CONCAT('Estudiante ', j.idusu) AS nomes,
                                          '' AS apees,
                                          CONCAT('Estudiante ', j.idusu) AS nomusu,
                                          COALESCE(u.ndocusu, CONCAT('DOC-', j.idusu)) AS ndocusu
                                          FROM juicio j
                                          LEFT JOIN usuario u ON j.idusu = u.idusu
                                          WHERE j.idusu = ? OR j.ndocins = ?
                                          LIMIT 1");
            $stmt->execute([is_numeric($ndoceLimpio) ? (int)$ndoceLimpio : 0, $ndoce]);
            $resJui = $stmt->fetch(PDO::FETCH_ASSOC);
            return $resJui ?: null;
        } catch (Exception $e) {
            ManejoError($e);
            return null;
        }
    }

    public function cargarFichaInstructor($idusu) {
        try {
            $stmt = $this->db()->prepare("SELECT f.*, p.nompro
                                          FROM usufic uf
                                          LEFT JOIN ficha f ON uf.idfic = f.idfic
                                          LEFT JOIN programa p ON f.codpro = p.codpro
                                          WHERE uf.idusu = ? AND uf.actfic = 1
                                          LIMIT 1");
            $stmt->execute([(int)$idusu]);
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            return $res ?: null;
        } catch (Exception $e) {
            ManejoError($e);
            return null;
        }
    }

    public function buscarFichaPorDenominacion($denominacion) {
        try {
            $stmt = $this->db()->prepare("SELECT idfic FROM ficha WHERE nomfic LIKE ? LIMIT 1");
            $param = '%' . trim($denominacion) . '%';
            $stmt->execute([$param]);
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            return $res ? $res['idfic'] : null;
        } catch (Exception $e) {
            ManejoError($e);
            return null;
        }
    }

    public function buscarFichaPorIdConVariantes($idfic) {
        try {
            $stmt = $this->db()->prepare("SELECT idfic FROM ficha WHERE idfic = ? LIMIT 1");
            $stmt->execute([$idfic]);
            if ($stmt->fetchColumn()) {
                return $idfic;
            }
            // Variantes: Ficha base, Ficha-A, Ficha-B, etc.
            $patron = preg_replace('/[^0-9]/', '', $idfic);
            if ($patron !== '') {
                $stmt = $this->db()->prepare("SELECT idfic FROM ficha WHERE idfic LIKE ? ORDER BY idfic LIMIT 1");
                $stmt->execute([$patron . '%']);
                $res = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($res) {
                    return $res['idfic'];
                }
            }
            return null;
        } catch (Exception $e) {
            ManejoError($e);
            return null;
        }
    }

    public function verificarOCrearCompetencia($idcom, $descom) {
        try {
            $db = $this->db();
            $stmt = $db->prepare("SELECT idcom FROM competencia WHERE idcom = ?");
            $stmt->execute([(int)$idcom]);
            if ($stmt->rowCount() == 0) {
                $stmt = $db->prepare("INSERT INTO competencia (idcom, descom, vercom, horcom, idval) VALUES (?, ?, 1, 40, 1)");
                return $stmt->execute([(int)$idcom, $descom]);
            }
            return true;
        } catch (Exception $e) {
            ManejoError($e);
            return false;
        }
    }

    public function verificarOCrearResultado($idres, $nomres, $idcom) {
        try {
            $db = $this->db();
            $stmt = $db->prepare("SELECT idres FROM resultado WHERE idres = ?");
            $stmt->execute([(int)$idres]);
            if ($stmt->rowCount() == 0) {
                $stmt = $db->prepare("INSERT INTO resultado (idres, nomres, idcom, ndeses) VALUES (?, ?, ?, 1)");
                return $stmt->execute([(int)$idres, $nomres, (int)$idcom]);
            }
            return true;
        } catch (Exception $e) {
            ManejoError($e);
            return false;
        }
    }
// ============ IMPORTACIÓN (UN SOLO FLUJO CSV EN DOS PASOS) ============

    public function normalizar_utf8($v) {
        if ($v === null) return '';
        $v = trim($v);
        return mb_convert_encoding($v, 'UTF-8', 'UTF-8,ISO-8859-1');
    }

    public function convertirFechaExcel($fecha) {
        if (is_numeric($fecha)) {
            $timestamp = ($fecha - 25569) * 86400;
            return date('Y-m-d', $timestamp);
        }
        if (preg_match('/(\d{1,2})\/(\d{1,2})\/(\d{4})/', $fecha, $m)) {
            $hora = 0; $min = 0; $seg = 0;
            if (preg_match('/(\d{1,2})\.(\d{2})\s*([ap])/i', $fecha, $hm)) {
                $hora = (int)$hm[1];
                $min  = (int)$hm[2];
                if (strtolower($hm[3]) === 'p' && $hora != 12) { $hora += 12; }
                if (strtolower($hm[3]) === 'a' && $hora == 12) { $hora = 0; }
            }
            return sprintf("%04d-%02d-%02d %02d:%02d:%02d", $m[3], $m[2], $m[1], $hora, $min, $seg);
        }
        return $fecha;
    }

    /**
     * Lee la estructura de un CSV de Juicios (reporte SENA):
     * busca la fila de encabezados entre las primeras 30 líneas y extrae metadatos.
     * @return array{metadatos:array, header:array, header_row:int, filas:int}
     */
    public function leerEstructuraCSV($rutaCSV) {
        $required = ['Número de Documento', 'Competencia', 'Resultado de Aprendizaje', 'Juicio de Evaluación'];
        $resultado = [
            'metadatos' => ['fecha_reporte' => null, 'idfic' => null],
            'header' => [],
            'header_row' => 0,
            'filas' => 0
        ];
        $fh = fopen($rutaCSV, 'r');
        if (!$fh) {
            throw new Exception("No se pudo abrir el archivo CSV");
        }
        $filaNum = 0;
        $headerRowIdx = null;
        while (($row = fgetcsv($fh, 0, ',')) !== false) {
            $filaNum++;
            $row = array_map([$this, 'normalizar_utf8'], $row);

            foreach ($row as $i => $celda) {
                $celdaNorm = strtolower(trim($celda, " :\t"));
                if ($celdaNorm === 'fecha del reporte' && isset($row[$i + 2])) {
                    $resultado['metadatos']['fecha_reporte'] = trim($row[$i + 2]);
                }
                if ($celdaNorm === 'ficha de caracterización' && isset($row[$i + 2])) {
                    $resultado['metadatos']['idfic'] = trim($row[$i + 2]);
                }
            }

            $encontradas = 0;
            foreach ($required as $col) {
                if (in_array($col, $row)) { $encontradas++; }
            }
            if ($encontradas >= 3) {
                $headerRowIdx = $filaNum;
                $resultado['header'] = $row;
                break;
            }
            if ($filaNum >= 30) { break; }
        }

        if ($headerRowIdx === null) {
            fclose($fh);
            throw new Exception("No se encontró la fila de encabezados del CSV. Columnas requeridas: " . implode(', ', $required));
        }

        $count = 0;
        while (($row = fgetcsv($fh, 0, ',')) !== false) {
            $row = array_map([$this, 'normalizar_utf8'], $row);
            $idxNdoc = array_search('Número de Documento', $resultado['header']);
            if ($idxNdoc !== false && trim($row[$idxNdoc] ?? '') !== '') { $count++; }
        }
        fclose($fh);
        $resultado['header_row'] = $headerRowIdx;
        $resultado['filas'] = $count;
        return $resultado;
    }
/**
     * Procesa un archivo CSV completo e importa/actualiza los juicios.
     */
    public function procesarCSVJuicios($archivoCSV, $idfic, $archivoNombre = '') {
        $resultado = [
            'total_filas' => 0,
            'procesadas' => 0,
            'error' => 0,
            'insertados' => 0,
            'actualizados' => 0,
            'omitidos' => 0,
            'advertencias' => 0,
            'avisos' => [],
            'errores' => []
        ];

        if (!file_exists($archivoCSV)) {
            throw new Exception("El archivo CSV no existe");
        }

        $estructura = $this->leerEstructuraCSV($archivoCSV);
        if (empty($estructura['header'])) {
            throw new Exception("CSV sin encabezados válidos");
        }

        if (empty($idfic) && !empty($estructura['metadatos']['idfic'])) {
            $resuelta = $this->buscarFichaPorIdConVariantes($estructura['metadatos']['idfic']);
            $idfic = $resuelta ?: $estructura['metadatos']['idfic'];
        }
        if (empty($idfic)) {
            throw new Exception("Debe indicar la ficha destino (idfic) para importar");
        }

        $header = $estructura['header'];
        $idx = [];
        foreach ($header as $i => $col) {
            $idx[trim($col)] = $i;
        }

        $fh = fopen($archivoCSV, 'r');
        if (!$fh) { throw new Exception("No se pudo reabrir el CSV"); }

        $numeroFila = 0;
        while (($row = fgetcsv($fh, 0, ',')) !== false) {
            $numeroFila++;
            if ($numeroFila <= $estructura['header_row']) { continue; }
            $row = array_map([$this, 'normalizar_utf8'], $row);

            $doc  = isset($idx['Número de Documento']) ? trim($row[$idx['Número de Documento']] ?? '', '"') : '';
            $comp = isset($idx['Competencia']) ? trim($row[$idx['Competencia']] ?? '', '"') : '';
            $res  = isset($idx['Resultado de Aprendizaje']) ? trim($row[$idx['Resultado de Aprendizaje']] ?? '', '"') : '';
            if ($doc === '' && $comp === '' && $res === '') { continue; }

            $resultado['total_filas']++;
            $fila = [
                'Tipo de Documento'                             => isset($idx['Tipo de Documento']) ? trim($row[$idx['Tipo de Documento']] ?? '', "\" \t") : '',
                'Número de Documento'                           => $doc,
                'Competencia'                                   => $comp,
                'Resultado de Aprendizaje'                      => $res,
                'Juicio de Evaluación'                          => isset($idx['Juicio de Evaluación']) ? trim($row[$idx['Juicio de Evaluación']] ?? '', '"') : '',
                'Fecha y Hora del Juicio Evaluativo'            => isset($idx['Fecha y Hora del Juicio Evaluativo']) ? trim($row[$idx['Fecha y Hora del Juicio Evaluativo']] ?? '', '"') : '',
                'Funcionario que registro el juicio evaluativo' => isset($idx['Funcionario que registro el juicio evaluativo']) ? trim($row[$idx['Funcionario que registro el juicio evaluativo']] ?? '', '"') : ''
            ];

            try {
                $resImp = $this->importarDesdeCSV($fila, $idfic, $archivoNombre ?: basename($archivoCSV), $estructura['metadatos']['fecha_reporte']);
                $resultado['procesadas']++;
                if (($resImp['accion'] ?? '') === 'actualizado') {
                    $resultado['actualizados']++;
                } else {
                    $resultado['insertados']++;
                }
                if (!empty($resImp['advertencias']) && is_array($resImp['advertencias'])) {
                    foreach ($resImp['advertencias'] as $aviso) {
                        $resultado['advertencias']++;
                        $resultado['avisos'][] = ['fila' => $numeroFila, 'documento' => $doc, 'aviso' => $aviso];
                    }
                }
            } catch (Exception $e) {
                $resultado['error']++;
                $resultado['omitidos']++;
                $resultado['errores'][] = [
                    'fila' => $numeroFila,
                    'documento' => $doc,
                    'error' => $e->getMessage()
                ];
            }
        }
        fclose($fh);

        $_SESSION['resultado_importacion'] = $resultado;
        return $resultado;
    }
/**
     * Importa una fila del CSV a la BD (competencias, resultados, juicio y aprobación).
     * Respeta los campos NOT NULL reales (ndocins, fecha_reporte) y el esquema de aprobacion_resultado.
     */
    public function importarDesdeCSV($fila, $idfic, $archivoOriginal = '', $fechaReporte = null, $archivoCorrelativo = null) {
        $db = (new conexion())->get_conexion();
        $db->beginTransaction();
        try {
            // 1. Competencia
            $partesCompetencia = explode(' - ', trim($fila['Competencia']), 2);
            $idcomCsv = trim($partesCompetencia[0]);
            $descom = isset($partesCompetencia[1]) ? trim($partesCompetencia[1]) : '';
            if (empty($idcomCsv) || !is_numeric($idcomCsv)) {
                throw new Exception("Competencia inválida: {$fila['Competencia']}");
            }

            // 2. Resultado
            $partesResultado = explode(' - ', trim($fila['Resultado de Aprendizaje']), 2);
            $idres = trim($partesResultado[0]);
            $nomres = isset($partesResultado[1]) ? trim($partesResultado[1]) : '';
            if (empty($idres) || !is_numeric($idres)) {
                throw new Exception("Resultado inválido: {$fila['Resultado de Aprendizaje']}");
            }

            // INTEGRIDAD DE DATOS MAESTROS:
            // La relación real competencia -> resultado es resultado.idcom (FK) y es la que
            // usan Juicios y Plan de Sesión. Si el resultado YA existe, se respeta el maestro
            // y el juicio se liga a esa competencia.
            // Antes esta importación ejecutaba UPDATE resultado SET idcom = <id del CSV>,
            // es decir movía un resultado de aprendizaje a otra competencia (dato maestro)
            // y con ello quedaba incoherente la pareja competencia/resultado en toda la app.
            $stmt = $db->prepare("SELECT idcom FROM resultado WHERE idres = ?");
            $stmt->execute([(int)$idres]);
            $idcomActual = $stmt->fetchColumn();
            $advertencias = [];
            if (!empty($idcomActual)) {
                $idcom = (int)$idcomActual;
                if ($idcom !== (int)$idcomCsv) {
                    $advertencias[] = "El resultado $idres ya pertenece a la competencia $idcom; "
                        . "se conservó la relación del maestro (el CSV indicaba $idcomCsv).";
                }
            } else {
                // Resultado nuevo (o sin relación registrada): se crea la relación indicada.
                $idcom = (int)$idcomCsv;
                $this->verificarOCrearCompetencia($idcom, $descom);
                $this->verificarOCrearResultado($idres, $nomres, $idcom);
                $stmt = $db->prepare("SELECT idcom FROM resultado WHERE idres = ?");
                $stmt->execute([(int)$idres]);
                $idcomActual = $stmt->fetchColumn();
                if (!empty($idcomActual)) {
                    $idcom = (int)$idcomActual;
                }
            }

            // 3. Usuario (aprendiz)
            $ndoce = trim($fila['Número de Documento']);
            if ($ndoce === '') {
                throw new Exception("Número de documento vacío");
            }
            $stmt = $db->prepare("SELECT idusu FROM usuario WHERE ndocusu = ? LIMIT 1");
            $stmt->execute([$ndoce]);
            $idusu = $stmt->fetchColumn();
            if (!$idusu) {
                throw new Exception("Usuario con documento $ndoce no encontrado");
            }

            // 4. Funcionario instructor
            $ndocins = '';
            $funcionario = trim($fila['Funcionario que registro el juicio evaluativo'] ?? '');
            if (!empty($funcionario) && !preg_match('/^[\s\-]+$/', $funcionario)) {
                if (preg_match('/(\d+)/', $funcionario, $m)) {
                    $ndocins = $m[1];
                }
            }

            // 5. Datos del juicio
            $caljui = strtoupper(trim($fila['Juicio de Evaluación'] ?? ''));
            if (!in_array($caljui, ['APROBADO', 'NO APROBADO', 'POR EVALUAR'], true)) {
                $caljui = 'POR EVALUAR';
            }

            $fechaReporteSQL = null;
            if (!empty($fechaReporte)) {
                $fecha = DateTime::createFromFormat('d/m/Y', trim($fechaReporte));
                $fechaReporteSQL = $fecha ? $fecha->format('Y-m-d') : null;
            }
            if (empty($fechaReporteSQL)) {
                $fechaReporteSQL = date('Y-m-d');
            }

            $fechaJuicioSQL = null;
            $fechaStr = trim($fila['Fecha y Hora del Juicio Evaluativo'] ?? '');
            if (!empty($fechaStr) && !preg_match('/^[\s\-]+$/', $fechaStr)) {
                $fechaJuicioSQL = $this->convertirFechaExcel($fechaStr);
            }
            $fechaImportacionSQL = date('Y-m-d H:i:s');
            $archivoCorrelativoFinal = $archivoCorrelativo ?: ($archivoOriginal ? basename($archivoOriginal) : null);
// 6. Insertar o actualizar juicio
            $stmt = $db->prepare("SELECT idjui FROM juicio WHERE idusu = ? AND idres = ? AND idcom = ? LIMIT 1");
            $stmt->execute([(int)$idusu, (int)$idres, (int)$idcom]);
            $idjuiExistente = $stmt->fetchColumn();

            if ($idjuiExistente) {
                $stmt = $db->prepare("UPDATE juicio SET caljui = ?, ndocins = ?, fecha_reporte = ?, fecha_importacion = ?, archivo_correlativo = ?, fecjuieva = ? WHERE idjui = ?");
                $stmt->execute([$caljui, $ndocins, $fechaReporteSQL, $fechaImportacionSQL, $archivoCorrelativoFinal, $fechaJuicioSQL, (int)$idjuiExistente]);
                $accion = 'actualizado';
                $idjui = (int)$idjuiExistente;
            } else {
                $stmt = $db->prepare("INSERT INTO juicio (idusu, idfic, idres, idcom, caljui, ndocins, fecha_reporte, fecha_importacion, archivo_correlativo, fecjuieva)
                                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([(int)$idusu, $idfic, (int)$idres, (int)$idcom, $caljui, $ndocins, $fechaReporteSQL, $fechaImportacionSQL, $archivoCorrelativoFinal, $fechaJuicioSQL]);
                $idjui = (int)$db->lastInsertId();
                $accion = 'insertado';
            }
            return $this->finalizarImportacion($db, $idfic, $idres, $ndocins, $caljui, $fechaJuicioSQL, $accion, $idjui, $advertencias);
        } catch (Exception $e) {
            $db->rollBack();
            throw new Exception("Error al importar registro: " . $e->getMessage());
        }
    }

    private function finalizarImportacion($db, $idfic, $idres, $ndocins, $caljui, $fechaJuicioSQL, $accion, $idjui, $advertencias = []) {
        $idinstructor = null;
        if ($ndocins !== '') {
            $stmt = $db->prepare("SELECT idusu FROM usuario WHERE ndocusu = ? LIMIT 1");
            $stmt->execute([$ndocins]);
            $idinstructor = $stmt->fetchColumn();
        }
        $estado = 'pendiente';
        if ($caljui === 'APROBADO') { $estado = 'aprobado'; }
        elseif ($caljui === 'NO APROBADO') { $estado = 'no aprobado'; }

        $stmt = $db->prepare("SELECT idaprob FROM aprobacion_resultado WHERE idfic = ? AND idres = ?");
        $stmt->execute([$idfic, $idres]);
        if ($stmt->rowCount() == 0 && $idinstructor) {
            $stmt = $db->prepare("INSERT INTO aprobacion_resultado (idfic, idres, idinstructor, estado, fecha_aprobacion)
                                  VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$idfic, $idres, (int)$idinstructor, $estado, $fechaJuicioSQL ?: date('Y-m-d H:i:s')]);
        }
        $db->commit();
        return [
            'success' => true,
            'accion' => $accion,
            'idjui' => $idjui,
            'advertencias' => is_array($advertencias) ? $advertencias : []
        ];
    }
// ============ DASHBOARD (DATOS REALES) ============

    public function getEstadisticas() {
        return $this->getDashboard();
    }

    public function getFichas() {
        return $this->getAllfic();
    }

    public function getCompetencias($idusu = 0) {
        return $this->getCompetenciasPorInstructor($idusu);
    }

    public function getResultados($idusu = 0) {
        return $this->getResultadosPorInstructor($idusu);
    }

    public function getDashboard() {
        try {
            $db = $this->db();
            $stmt = $db->query("SELECT
                    COUNT(*) AS total,
                    SUM(CASE WHEN UPPER(caljui) = 'APROBADO' THEN 1 ELSE 0 END) AS aprobados,
                    SUM(CASE WHEN UPPER(caljui) = 'NO APROBADO' THEN 1 ELSE 0 END) AS no_aprobados,
                    SUM(CASE WHEN caljui IS NULL OR UPPER(caljui) NOT IN ('APROBADO','NO APROBADO') THEN 1 ELSE 0 END) AS por_evaluar,
                    COUNT(DISTINCT idfic) AS fichas,
                    COUNT(DISTINCT idcom) AS competencias,
                    COUNT(DISTINCT ndocins) AS instructores
                FROM juicio");
            $dash = $stmt->fetch(PDO::FETCH_ASSOC);

            $stmt = $db->query("SELECT COUNT(*) AS pendientes FROM aprobacion_resultado WHERE UPPER(estado) = 'PENDIENTE'");
            $dash['aprobaciones_pendientes'] = (int)$stmt->fetchColumn();

            // Normalizar a enteros
            foreach ($dash as $k => $v) {
                $dash[$k] = (int)$v;
            }
            return $dash;
        } catch (Exception $e) {
            ManejoError($e);
            return ['total' => 0, 'aprobados' => 0, 'no_aprobados' => 0, 'por_evaluar' => 0, 'fichas' => 0, 'competencias' => 0, 'instructores' => 0, 'aprobaciones_pendientes' => 0];
        }
    }

    public function getJuiciosPorFicha($limite = 6) {
        try {
            $stmt = $this->db()->prepare("SELECT j.idfic, COALESCE(f.nomfic, 'Sin ficha') AS nomfic,
                                                 COUNT(*) AS total
                                          FROM juicio j
                                          LEFT JOIN ficha f ON j.idfic = f.idfic
                                          GROUP BY j.idfic, f.nomfic
                                          ORDER BY total DESC LIMIT " . (int)$limite);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            ManejoError($e);
            return [];
        }
    }

    public function getJuiciosPorCompetencia($limite = 6) {
        try {
            $stmt = $this->db()->prepare("SELECT j.idcom, COALESCE(c.descom, 'Sin competencia') AS descom,
                                                 COUNT(*) AS total
                                          FROM juicio j
                                          LEFT JOIN competencia c ON j.idcom = c.idcom
                                          GROUP BY j.idcom, c.descom
                                          ORDER BY total DESC LIMIT " . (int)$limite);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            ManejoError($e);
            return [];
        }
    }

    public function getUltimasImportaciones($limite = 6) {
        try {
            $stmt = $this->db()->prepare("SELECT DATE(j.fecha_importacion) AS fecha,
                                                 COALESCE(j.archivo_correlativo, 'Importación directa') AS archivo,
                                                 COUNT(*) AS total
                                          FROM juicio j
                                          GROUP BY DATE(j.fecha_importacion), j.archivo_correlativo
                                          ORDER BY fecha DESC, total DESC LIMIT " . (int)$limite);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            ManejoError($e);
            return [];
        }
    }
}
?>