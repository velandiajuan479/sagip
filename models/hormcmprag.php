<?php
require_once(__DIR__ . "/conexion.php");

/**
 * Modelo Mcmas - Módulo de Carga Masiva
 * Valida, estructura y relaciona Programas, Proyectos, Competencias, Resultados de Aprendizaje, Agendas y Sesiones.
 */
class Mcmas {

    private $conexion;

    public function __construct() {
        $modelo = new conexion();
        $this->conexion = $modelo->get_conexion();
    }

    /**
     * Valida que un programa exista o lo crea evitando duplicados
     */
    public function buscarOCrearPrograma($codpro, $nompro, $verpro = 1) {
        try {
            $codpro = trim((string)$codpro);
            $nompro = trim((string)$nompro);

            if (empty($codpro) && empty($nompro)) {
                return null;
            }

            // 1. Buscar por código exacto
            if (!empty($codpro)) {
                $sql = "SELECT codpro, nompro FROM programa WHERE codpro = :codpro LIMIT 1";
                $stmt = $this->conexion->prepare($sql);
                $stmt->bindParam(':codpro', $codpro);
                $stmt->execute();
                $prog = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($prog) {
                    // Si el nombre existente era genérico y ahora viene completo, actualizar
                    if (!empty($nompro) && (empty($prog['nompro']) || $prog['nompro'] === 'Sin programa')) {
                        $sqlUpdate = "UPDATE programa SET nompro = :nompro WHERE codpro = :codpro";
                        $stmtUp = $this->conexion->prepare($sqlUpdate);
                        $stmtUp->bindParam(':nompro', $nompro);
                        $stmtUp->bindParam(':codpro', $codpro);
                        $stmtUp->execute();
                    }
                    return $codpro;
                }
            }

            // 2. Buscar por nombre exacto si no tenía código
            if (!empty($nompro)) {
                $sqlNom = "SELECT codpro FROM programa WHERE nompro = :nompro LIMIT 1";
                $stmtNom = $this->conexion->prepare($sqlNom);
                $stmtNom->bindParam(':nompro', $nompro);
                $stmtNom->execute();
                $progNom = $stmtNom->fetch(PDO::FETCH_ASSOC);
                if ($progNom) {
                    return $progNom['codpro'];
                }
            }

            // 3. Si no existe, crear nuevo programa
            if (empty($codpro)) {
                // Generar código numérico si no viene
                $codpro = time();
            }

            $sqlInsert = "INSERT INTO programa (codpro, nompro, verpro, idare) VALUES (:codpro, :nompro, :verpro, 8)";
            $stmtIns = $this->conexion->prepare($sqlInsert);
            $stmtIns->bindParam(':codpro', $codpro);
            $stmtIns->bindParam(':nompro', $nompro);
            $stmtIns->bindParam(':verpro', $verpro);
            $stmtIns->execute();

            return $codpro;
        } catch (Exception $e) {
            error_log("Error en Mcmas::buscarOCrearPrograma: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Normaliza descripciones para encontrar la misma competencia aunque el
     * archivo cambie mayúsculas, tildes, espacios o signos de puntuación.
     */
    private function normalizarCompetencia($valor)
    {
        $texto = trim(preg_replace('/\\s+/u', ' ', (string)$valor));
        $texto = strtr($texto, [
            'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U', 'Ü' => 'U', 'Ñ' => 'N',
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n',
        ]);
        $texto = function_exists('mb_strtolower') ? mb_strtolower($texto, 'UTF-8') : strtolower($texto);
        $texto = preg_replace('/[^\\p{L}\\p{N}]+/u', ' ', $texto);
        return trim(preg_replace('/\\s+/u', ' ', (string)$texto));
    }

    /**
     * Las competencias nuevas heredan la clasificación técnica del programa
     * cuando existe; 1049 es el valor de Técnica en la tabla valor.
     */
    private function obtenerIdvalCompetencia($codpro)
    {
        $idval = 1049;
        if (empty($codpro)) {
            return $idval;
        }

        try {
            $sql = "SELECT c.idval
                    FROM proxcom px
                    INNER JOIN competencia c ON c.idcom = px.idcom
                    WHERE px.codpro = :codpro
                      AND c.idval IN (1049, 1050)
                    ORDER BY CASE WHEN c.idval = 1049 THEN 0 ELSE 1 END, c.idcom
                    LIMIT 1";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':codpro', $codpro);
            $stmt->execute();
            $valor = $stmt->fetchColumn();
            return ($valor !== false && (int)$valor > 0) ? (int)$valor : $idval;
        } catch (Exception $e) {
            error_log("Error obteniendo clasificación de competencia: " . $e->getMessage());
            return $idval;
        }
    }

    /**
     * Busca o crea una competencia y la asocia al programa en proxcom.
     * La tabla competencia no tiene AUTO_INCREMENT y exige idcom e idval;
     * por eso se deben proporcionar ambos valores al insertar.
     */
    public function buscarOCrearCompetencia($descom, $codpro = null, $horcom = 0) {
        try {
            $descom = trim((string)$descom);
            if (empty($descom)) return null;

            $normalizado = $this->normalizarCompetencia($descom);

            // 1. Buscar coincidencia exacta.
            $sql = "SELECT idcom FROM competencia WHERE descom = :descom LIMIT 1";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':descom', $descom);
            $stmt->execute();
            $comp = $stmt->fetch(PDO::FETCH_ASSOC);

            // 2. Buscar la misma competencia con diferencias de formato.
            if (!$comp && $normalizado !== '') {
                $sql = "SELECT idcom, descom FROM competencia ORDER BY idcom";
                $stmt = $this->conexion->prepare($sql);
                $stmt->execute();
                foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $filaCompetencia) {
                    if ($this->normalizarCompetencia($filaCompetencia['descom'] ?? '') === $normalizado) {
                        $comp = $filaCompetencia;
                        break;
                    }
                }
            }

            $idcom = null;
            if ($comp) {
                $idcom = (int)$comp['idcom'];
            } else {
                // 3. Crear una competencia nueva con las columnas obligatorias
                // del esquema legado (idcom no es AUTO_INCREMENT).
                $sqlSiguiente = "SELECT COALESCE(MAX(idcom), 0) + 1 FROM competencia";
                $idcom = (int)$this->conexion->query($sqlSiguiente)->fetchColumn();
                $idval = $this->obtenerIdvalCompetencia($codpro);
                $sqlIns = "INSERT INTO competencia (idcom, descom, vercom, horcom, idval)
                           VALUES (:idcom, :descom, :vercom, :horcom, :idval)";
                $stmtIns = $this->conexion->prepare($sqlIns);
                $vercom = 1;
                $horcomValor = (int)$horcom;
                $stmtIns->bindParam(':idcom', $idcom);
                $stmtIns->bindParam(':descom', $descom);
                $stmtIns->bindParam(':vercom', $vercom);
                $stmtIns->bindParam(':horcom', $horcomValor);
                $stmtIns->bindParam(':idval', $idval);
                $stmtIns->execute();
            }

            // 4. Relacionar Programa → Competencia en proxcom.
            if ($idcom > 0 && !empty($codpro) && !$this->relacionarProgramaCompetencia($codpro, $idcom)) {
                return null;
            }

            return $idcom;
        } catch (Exception $e) {
            error_log("Error en Mcmas::buscarOCrearCompetencia: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Relaciona Programa con Competencia en tabla proxcom evitando duplicados
     */
    public function relacionarProgramaCompetencia($codpro, $idcom) {
        try {
            $sqlCheck = "SELECT 1 FROM proxcom WHERE codpro = :codpro AND idcom = :idcom LIMIT 1";
            $stmtCheck = $this->conexion->prepare($sqlCheck);
            $stmtCheck->bindParam(':codpro', $codpro);
            $stmtCheck->bindParam(':idcom', $idcom);
            $stmtCheck->execute();

            if (!$stmtCheck->fetch()) {
                $sqlIns = "INSERT INTO proxcom (codpro, idcom) VALUES (:codpro, :idcom)";
                $stmtIns = $this->conexion->prepare($sqlIns);
                $stmtIns->bindParam(':codpro', $codpro);
                $stmtIns->bindParam(':idcom', $idcom);
                $stmtIns->execute();
            }
            return true;
        } catch (Exception $e) {
            error_log("Error en Mcmas::relacionarProgramaCompetencia: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Busca o crea un resultado de aprendizaje y lo relaciona a su competencia.
     * La tabla resultado no tiene AUTO_INCREMENT; idres debe enviarse explícitamente.
     */
    public function buscarOCrearResultado($nomres, $idcom, $ndeses = 1) {
        try {
            $nomres = trim((string)$nomres);
            $idcom = (int)$idcom;
            if (empty($nomres) || $idcom <= 0) return null;

            $normalizado = $this->normalizarCompetencia($nomres);

            // 1. Buscar coincidencia exacta para la competencia.
            $sql = "SELECT idres FROM resultado WHERE nomres = :nomres AND idcom = :idcom LIMIT 1";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':nomres', $nomres);
            $stmt->bindParam(':idcom', $idcom);
            $stmt->execute();
            $res = $stmt->fetch(PDO::FETCH_ASSOC);

            // 2. Evitar duplicados por diferencias de mayúsculas, tildes o
            // puntuación dentro de la misma competencia.
            if (!$res && $normalizado !== '') {
                $sql = "SELECT idres, nomres FROM resultado WHERE idcom = :idcom ORDER BY idres";
                $stmt = $this->conexion->prepare($sql);
                $stmt->bindParam(':idcom', $idcom);
                $stmt->execute();
                foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $filaResultado) {
                    if ($this->normalizarCompetencia($filaResultado['nomres'] ?? '') === $normalizado) {
                        $res = $filaResultado;
                        break;
                    }
                }
            }

            if ($res) {
                return (int)$res['idres'];
            }

            // 3. Crear el resultado con un idres explícito.
            $sqlSiguiente = "SELECT COALESCE(MAX(idres), 0) + 1 FROM resultado";
            $idres = (int)$this->conexion->query($sqlSiguiente)->fetchColumn();
            $ndesesValor = max(1, (int)$ndeses);
            $sqlIns = "INSERT INTO resultado (idres, nomres, idcom, ndeses)
                       VALUES (:idres, :nomres, :idcom, :ndeses)";
            $stmtIns = $this->conexion->prepare($sqlIns);
            $stmtIns->bindParam(':idres', $idres);
            $stmtIns->bindParam(':nomres', $nomres);
            $stmtIns->bindParam(':idcom', $idcom);
            $stmtIns->bindParam(':ndeses', $ndesesValor);
            $stmtIns->execute();

            return $idres;
        } catch (Exception $e) {
            error_log("Error en Mcmas::buscarOCrearResultado: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Guarda una fila de planeación en cargueplaneacion (col1..col16) y la
     * misma información en planeacion_p. La correspondencia es:
     * col1=comp, col2=ra, ..., col16=obs.
     */
    public function guardarCarguePlaneacion($datos)
    {
        $transaccionPropia = false;
        try {
            $texto = [];
            $carga = [];
            for ($i = 1; $i <= 16; $i++) {
                $valor = trim((string)($datos['col' . $i] ?? ''));
                $texto[$i] = $valor;
                $carga[$i] = function_exists('mb_substr') ? mb_substr($valor, 0, 255) : substr($valor, 0, 255);
            }

            if (!$this->conexion->inTransaction()) {
                $this->conexion->beginTransaction();
                $transaccionPropia = true;
            }

            $columnas = [];
            $placeholders = [];
            for ($i = 1; $i <= 16; $i++) {
                $columnas[] = 'col' . $i;
                $placeholders[] = ':col' . $i;
            }
            $sqlCargue = "INSERT INTO cargueplaneacion (" . implode(',', $columnas) . ")
                          VALUES (" . implode(',', $placeholders) . ")";
            $stmtCargue = $this->conexion->prepare($sqlCargue);
            for ($i = 1; $i <= 16; $i++) {
                $stmtCargue->bindValue(':col' . $i, $carga[$i], PDO::PARAM_STR);
            }
            $stmtCargue->execute();

            $idPlaneacion = (int)$this->conexion->query("SELECT COALESCE(MAX(id), 0) + 1 FROM planeacion_p")->fetchColumn();
            $horas = static function ($valor) {
                $valor = trim((string)$valor);
                if ($valor === '') {
                    return null;
                }
                $valor = str_replace(',', '.', $valor);
                return is_numeric($valor) ? (int)round((float)$valor) : null;
            };
            $tipo = function_exists('mb_substr') ? mb_substr($texto[3], 0, 50) : substr($texto[3], 0, 50);

            $sqlPlaneacion = "INSERT INTO planeacion_p
                (id, comp, ra, tip, scp, sp, ce, aa, hr, htd, hti, dea, eda, amb, mf, ir, obs)
                VALUES
                (:id, :comp, :ra, :tip, :scp, :sp, :ce, :aa, :hr, :htd, :hti, :dea, :eda, :amb, :mf, :ir, :obs)";
            $stmtPlaneacion = $this->conexion->prepare($sqlPlaneacion);
            $stmtPlaneacion->bindValue(':id', $idPlaneacion, PDO::PARAM_INT);
            $stmtPlaneacion->bindValue(':comp', $texto[1], PDO::PARAM_STR);
            $stmtPlaneacion->bindValue(':ra', $texto[2], PDO::PARAM_STR);
            $stmtPlaneacion->bindValue(':tip', $tipo, PDO::PARAM_STR);
            $stmtPlaneacion->bindValue(':scp', $texto[4], PDO::PARAM_STR);
            $stmtPlaneacion->bindValue(':sp', $texto[5], PDO::PARAM_STR);
            $stmtPlaneacion->bindValue(':ce', $texto[6], PDO::PARAM_STR);
            $stmtPlaneacion->bindValue(':aa', $texto[7], PDO::PARAM_STR);
            $stmtPlaneacion->bindValue(':hr', $horas($texto[8]), $horas($texto[8]) === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $stmtPlaneacion->bindValue(':htd', $horas($texto[9]), $horas($texto[9]) === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $stmtPlaneacion->bindValue(':hti', $horas($texto[10]), $horas($texto[10]) === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $stmtPlaneacion->bindValue(':dea', $texto[11], PDO::PARAM_STR);
            $stmtPlaneacion->bindValue(':eda', $texto[12], PDO::PARAM_STR);
            $stmtPlaneacion->bindValue(':amb', $texto[13], PDO::PARAM_STR);
            $stmtPlaneacion->bindValue(':mf', $texto[14], PDO::PARAM_STR);
            $stmtPlaneacion->bindValue(':ir', $texto[15], PDO::PARAM_STR);
            $stmtPlaneacion->bindValue(':obs', $texto[16], PDO::PARAM_STR);
            $stmtPlaneacion->execute();

            if ($transaccionPropia) {
                $this->conexion->commit();
            }
            return $idPlaneacion;
        } catch (\Throwable $e) {
            if ($transaccionPropia && $this->conexion->inTransaction()) {
                $this->conexion->rollBack();
            }
            error_log("Error en Mcmas::guardarCarguePlaneacion: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Busca o crea una ficha vinculada al programa para asegurar integridad referencial
     */
    public function buscarOCrearFicha($idfic, $codpro = '1') {
        try {
            if (empty($idfic)) return '100001';
            $idfic = trim((string)$idfic);

            $sql = "SELECT idfic FROM ficha WHERE idfic = :idfic LIMIT 1";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':idfic', $idfic);
            $stmt->execute();
            if ($stmt->fetch()) {
                if (!empty($codpro)) {
                    $sqlUp = "UPDATE ficha SET codpro = :codpro WHERE idfic = :idfic";
                    $stmtUp = $this->conexion->prepare($sqlUp);
                    $stmtUp->bindParam(':codpro', $codpro);
                    $stmtUp->bindParam(':idfic', $idfic);
                    $stmtUp->execute();
                }
                return $idfic;
            }

            $sqlIns = "INSERT INTO ficha (idfic, codpro) VALUES (:idfic, :codpro)";
            $stmtIns = $this->conexion->prepare($sqlIns);
            $stmtIns->bindParam(':idfic', $idfic);
            $stmtIns->bindParam(':codpro', $codpro);
            $stmtIns->execute();
            return $idfic;
        } catch (Exception $e) {
            return $idfic;
        }
    }

    /**
     * Relaciona Programa con Proyecto y Ficha
     */
    public function relacionarProgramaProyectoFicha($codpro, $nompy = '', $codpy = '', $idfic = null) {
        try {
            if (empty($codpro)) return false;

            // Si viene ficha, asegurar que existe y asociarla al programa
            if (!empty($idfic)) {
                $this->buscarOCrearFicha($idfic, $codpro);
            }

            return true;
        } catch (Exception $e) {
            error_log("Error en Mcmas::relacionarProgramaProyectoFicha: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Guarda la Agenda, Sesiones y Actividades correspondientes.
     * Las tablas legacy no tienen AUTO_INCREMENT en sus identificadores;
     * se generan y persisten explícitamente dentro de una transacción.
     */
    public function guardarAgendaCompleta($datos) {
        $transaccionPropia = false;
        try {
            $idfic = !empty($datos['idfic']) ? $datos['idfic'] : '100001';
            $this->buscarOCrearFicha($idfic, $datos['codpro'] ?? '1');

            $fas = $datos['fas'] ?? 'FASE INICIAL';
            $actpro = $datos['actpro'] ?? $datos['actividad'] ?? 'Actividad de Proyecto';
            $idcom = (int)($datos['idcom'] ?? 0);
            $idres = (int)($datos['idres'] ?? 0);
            $fecini = $datos['fecini'] ?? date('Y-m-d');
            $fecfin = $datos['fecfin'] ?? date('Y-m-d');
            $nomact = $datos['nomact'] ?? $actpro;
            $actiAgenda = function_exists('mb_substr') ? mb_substr((string)$nomact, 0, 50) : substr((string)$nomact, 0, 50);
            $fas = function_exists('mb_substr') ? mb_substr((string)$fas, 0, 255) : substr((string)$fas, 0, 255);
            $actpro = function_exists('mb_substr') ? mb_substr((string)$actpro, 0, 255) : substr((string)$actpro, 0, 255);
            $duract = !empty($datos['duract']) ? (int)$datos['duract'] : 4;
            $numses = !empty($datos['numses']) ? max(1, (int)$datos['numses']) : 1;

            // Jornada de la agenda (editable en la plantilla, columna JORNADA).
            // Si no viene, se hereda la jornada de la ficha.
            $jorAge = trim((string)($datos['jor'] ?? ''));
            if ($jorAge === '') {
                try {
                    $sqlJor = "SELECT jornada FROM ficha WHERE idfic = :idfic LIMIT 1";
                    $stmtJor = $this->conexion->prepare($sqlJor);
                    $stmtJor->bindParam(':idfic', $idfic);
                    $stmtJor->execute();
                    $jorFic = $stmtJor->fetchColumn();
                    if ($jorFic !== false) {
                        $jorAge = self::nombreJornada($jorFic);
                    }
                } catch (Exception $e) {
                    $jorAge = '';
                }
            }

            if ($idcom <= 0 || $idres <= 0) {
                return false;
            }

            $inicioTs = strtotime($fecini);
            $finTs = strtotime($fecfin);
            if ($inicioTs === false || $finTs === false) {
                return false;
            }
            if ($finTs < $inicioTs) {
                $temp = $inicioTs;
                $inicioTs = $finTs;
                $finTs = $temp;
                $fechaTemporal = $fecini;
                $fecini = $fecfin;
                $fecfin = $fechaTemporal;
            }

            if (!$this->conexion->inTransaction()) {
                $this->conexion->beginTransaction();
                $transaccionPropia = true;
            }

            // 1. Guardar agenda con idage explícito.
            $sqlSiguienteAge = "SELECT COALESCE(MAX(idage), 0) + 1 FROM agenda";
            $idage = (int)$this->conexion->query($sqlSiguienteAge)->fetchColumn();
            $sqlAge = "INSERT INTO agenda (idage, idfic, fas, actpro, idcom, idres, fecini, fecfin, fchinc, fchfnl, acti, hor)
                       VALUES (:idage, :idfic, :fas, :actpro, :idcom, :idres, :fecini, :fecfin, :fchinc, :fchfnl, :acti, :hor)";
            $stmtAge = $this->conexion->prepare($sqlAge);
            $stmtAge->bindParam(':idage', $idage);
            $stmtAge->bindParam(':idfic', $idfic);
            $stmtAge->bindParam(':fas', $fas);
            $stmtAge->bindParam(':actpro', $actpro);
            $stmtAge->bindParam(':idcom', $idcom);
            $stmtAge->bindParam(':idres', $idres);
            $stmtAge->bindParam(':fecini', $fecini);
            $stmtAge->bindParam(':fecfin', $fecfin);
            $stmtAge->bindParam(':fchinc', $fecini);
            $stmtAge->bindParam(':fchfnl', $fecfin);
            $stmtAge->bindParam(':acti', $actiAgenda);
            $stmtAge->bindParam(':hor', $jorAge);
            $stmtAge->execute();

            // 2. Crear las sesiones correspondientes con sus fechas.
            $diasTotales = max(1, round(($finTs - $inicioTs) / 86400));
            $intervaloDias = ($numses > 1) ? floor($diasTotales / ($numses - 1)) : 0;

            for ($s = 1; $s <= $numses; $s++) {
                $fechaSesion = date('Y-m-d', $inicioTs + (($s - 1) * $intervaloDias * 86400));
                if (strtotime($fechaSesion) > $finTs) {
                    $fechaSesion = $fecfin;
                }

                // 3. Crear sesión con idses explícito.
                $sqlSiguienteSes = "SELECT COALESCE(MAX(idses), 0) + 1 FROM sesion";
                $idses = (int)$this->conexion->query($sqlSiguienteSes)->fetchColumn();
                $sqlSes = "INSERT INTO sesion (idses, idage, fecses)
                           VALUES (:idses, :idage, :fecses)";
                $stmtSes = $this->conexion->prepare($sqlSes);
                $stmtSes->bindParam(':idses', $idses);
                $stmtSes->bindParam(':idage', $idage);
                $stmtSes->bindParam(':fecses', $fechaSesion);
                $stmtSes->execute();

                // 4. Crear actividad vinculada con idact explícito.
                $sqlSiguienteAct = "SELECT COALESCE(MAX(idact), 0) + 1 FROM actividad";
                $idact = (int)$this->conexion->query($sqlSiguienteAct)->fetchColumn();
                $sqlAct = "INSERT INTO actividad (idact, idses, nomact, desact, duract, tipact, ordact)
                           VALUES (:idact, :idses, :nomact, :desact, :duract, 1, :ordact)";
                $stmtAct = $this->conexion->prepare($sqlAct);
                $stmtAct->bindParam(':idact', $idact);
                $stmtAct->bindParam(':idses', $idses);
                $stmtAct->bindParam(':nomact', $nomact);
                $stmtAct->bindParam(':desact', $actpro);
                $stmtAct->bindParam(':duract', $duract);
                $stmtAct->bindParam(':ordact', $s);
                $stmtAct->execute();
            }

            if ($transaccionPropia) {
                $this->conexion->commit();
            }
            return $idage;
        } catch (\Throwable $e) {
            if ($transaccionPropia && $this->conexion->inTransaction()) {
                $this->conexion->rollBack();
            }
            error_log("Error en Mcmas::guardarAgendaCompleta: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Valida una fecha en formato Y-m-d o dd/mm/aaaa
     */
    public static function normalizarFecha($valorFecha) {
        if (empty($valorFecha)) return null;

        $valorStr = trim((string)$valorFecha);

        // Si es número serial de Excel (ej: 44927)
        if (is_numeric($valorStr) && $valorStr > 20000 && $valorStr < 60000) {
            $unixTime = ($valorStr - 25569) * 86400;
            return gmdate('Y-m-d', (int)$unixTime);
        }

        // Formato dd/mm/yyyy o dd-mm-yyyy
        if (preg_match('/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})$/', $valorStr, $m)) {
            return sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]);
        }

        // Formato yyyy-mm-dd
        if (preg_match('/^(\d{4})[\/\-](\d{1,2})[\/\-](\d{1,2})$/', $valorStr, $m)) {
            return sprintf('%04d-%02d-%02d', $m[1], $m[2], $m[3]);
        }

        $time = strtotime($valorStr);
        return ($time !== false) ? date('Y-m-d', $time) : null;
    }

    /**
     * Obtiene todos los programas registrados
     */
    public function getAllProgramas() {
        try {
            $sql = "SELECT p.codpro, p.nompro, p.verpro, COUNT(px.idcom) as total_competencias 
                    FROM programa p 
                    LEFT JOIN proxcom px ON p.codpro = px.codpro 
                    GROUP BY p.codpro 
                    ORDER BY p.codpro DESC";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Obtiene estadísticas para el Dashboard de Carga Masiva
     */
    public function getEstadisticasDashboard() {
        try {
            // 1. Total de archivos/cargues subidos (agendas registradas)
            $sqlArchivos = "SELECT COUNT(DISTINCT idage) as total_agendas FROM agenda";
            $stmtArchivos = $this->conexion->query($sqlArchivos);
            $totalArchivos = $stmtArchivos ? (int)$stmtArchivos->fetchColumn() : 0;

            // 2. Total de programas
            $sqlProg = "SELECT COUNT(*) as total FROM programa";
            $stmtProg = $this->conexion->query($sqlProg);
            $totalProgramas = $stmtProg ? (int)$stmtProg->fetchColumn() : 0;

            // 3. Total de competencias
            $sqlComp = "SELECT COUNT(*) as total FROM competencia";
            $stmtComp = $this->conexion->query($sqlComp);
            $totalCompetencias = $stmtComp ? (int)$stmtComp->fetchColumn() : 0;

            // 4. Alertas de documentos / fichas que no se han subido
            $sqlPendientes = "SELECT COUNT(*) as total FROM ficha f WHERE f.idfic NOT IN (SELECT DISTINCT idfic FROM agenda WHERE idfic IS NOT NULL AND idfic != '')";
            $stmtPendientes = $this->conexion->query($sqlPendientes);
            $totalPendientes = $stmtPendientes ? (int)$stmtPendientes->fetchColumn() : 0;

            return [
                'archivos_subidos' => $totalArchivos,
                'alertas_pendientes' => $totalPendientes,
                'competencias' => $totalCompetencias,
                'programas' => $totalProgramas
            ];
        } catch (Exception $e) {
            error_log("Error en Mcmas::getEstadisticasDashboard: " . $e->getMessage());
            return [
                'archivos_subidos' => 0,
                'alertas_pendientes' => 0,
                'competencias' => 0,
                'programas' => 0
            ];
        }
    }

    /**
     * Obtiene el listado de archivos / registros de agendas y planeación subidos
     * agrupados por ficha, programa y fecha para mostrar en la vista previa organizada.
     */
    public function getArchivosSubidosResumen() {
        try {
            $sql = "SELECT 
                        MAX(a.idage) as idage,
                        a.idfic,
                        COALESCE(p.codpro, '') as codpro,
                        COALESCE(p.nompro, 'Programa de Formación') as nompro,
                        MIN(a.fecini) as fecini,
                        MAX(a.fecfin) as fecfin,
                        COUNT(DISTINCT a.idage) as total_agendas,
                        (SELECT COUNT(*) FROM sesion s INNER JOIN agenda ag ON s.idage = ag.idage WHERE ag.idfic = a.idfic) as total_sesiones,
                        CASE 
                            WHEN a.fas LIKE '%PLANEACI%' OR a.actpro LIKE '%PLANEACI%' THEN 'Planeación Pedagógica'
                            ELSE 'Agenda de Formación'
                        END as tipo_documento
                    FROM agenda a
                    LEFT JOIN ficha f ON a.idfic = f.idfic
                    LEFT JOIN programa p ON f.codpro = p.codpro
                    GROUP BY a.idfic, p.nompro, tipo_documento
                    ORDER BY idage DESC
                    LIMIT 20";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en Mcmas::getArchivosSubidosResumen: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Devuelve la ficha con sus relaciones de programa, responsables y documentos.
     * Las fichas de entrada ya vienen filtradas por HormAcceso; este método solo
     * completa la vista con datos relacionados de agenda, sesión, actividad y usuario.
     */
    public function getFichasRelacionadas(array $idfics)
    {
        $idfics = array_values(array_unique(array_filter(array_map('strval', $idfics))));
        if (empty($idfics)) {
            return [];
        }

        $filas = [];
        try {
            $placeholders = implode(',', array_fill(0, count($idfics), '?'));
            $sql = "SELECT f.idfic, f.nomfic, f.codpro, f.jornada, f.finific, f.ffinfic,
                           f.idusu AS ficha_idusu,
                           COALESCE(p.nompro, f.nomfic) AS nompro,
                           (SELECT COUNT(DISTINCT uf.idusu)
                            FROM usufic uf
                            WHERE uf.idfic = f.idfic AND uf.actfic = 1) AS total_aprendices
                    FROM ficha f
                    LEFT JOIN programa p ON p.codpro = f.codpro
                    WHERE f.idfic IN ($placeholders)
                    ORDER BY f.idfic DESC";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute($idfics);
            $filas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('Error consultando fichas relacionadas: ' . $e->getMessage());
            return [];
        }

        $idsPorFicha = $this->getIdsInstructoresPorFicha($idfics);
        $idsUsuarios = [];
        foreach ($idsPorFicha as $listaIds) {
            foreach ((array)$listaIds as $idusu) {
                $idsUsuarios[(int)$idusu] = true;
            }
        }
        $nombres = $this->getNombresInstructores(array_keys($idsUsuarios));
        $conteos = $this->getConteosPorFicha($idfics);
        $documentos = $this->getDocumentosPorFichas($idfics);

        foreach ($filas as &$fila) {
            $idfic = (string)($fila['idfic'] ?? '');
            $ids = array_values(array_unique(array_map('intval', $idsPorFicha[$idfic] ?? [])));
            $fila['ids_instructores'] = $ids;
            $fila['instructores'] = [];
            foreach ($ids as $idusu) {
                $fila['instructores'][] = [
                    'idusu' => $idusu,
                    'nombre' => $nombres[$idusu] ?? ('Usuario ' . $idusu),
                ];
            }
            $fila['conteos'] = $conteos[$idfic] ?? ['agendas' => 0, 'sesiones' => 0, 'actividades' => 0];
            $fila['documentos'] = $documentos[$idfic] ?? [];
        }
        unset($fila);

        return $filas;
    }

    /**
     * Agrupa fichas ya autorizadas por programa: cada programa aparece una sola vez
     * con todas sus fichas debajo. Entrada: filas de HormAcceso::getFichasPermitidas().
     */
    public function agruparFichasPorPrograma(array $fichas) {
        $grupos = [];
        foreach ($fichas as $f) {
            $codpro = isset($f['codpro']) ? (string)$f['codpro'] : '';
            if (!isset($grupos[$codpro])) {
                $nompro = trim((string)($f['nompro'] ?? ''));
                $grupos[$codpro] = [
                    'codpro' => $codpro,
                    'nompro' => $nompro !== '' ? $nompro : 'Programa de Formación',
                    'fichas' => []
                ];
            }
            $grupos[$codpro]['fichas'][] = $f;
        }
        return array_values($grupos);
    }

    /**
     * Conteos por ficha: agendas (agenda), planeación (sesiones) y guías (actividades).
     * Retorna [idfic => ['agendas' => n, 'sesiones' => n, 'actividades' => n]].
     */
    public function getConteosPorFicha(array $idfics) {
        $conteos = [];
        $idfics = array_values(array_unique(array_filter(array_map('strval', $idfics))));
        if (empty($idfics)) {
            return $conteos;
        }
        try {
            $placeholders = implode(',', array_fill(0, count($idfics), '?'));
            $sql = "SELECT a.idfic,
                           COUNT(DISTINCT a.idage) as agendas,
                           COUNT(DISTINCT s.idses) as sesiones,
                           COUNT(DISTINCT ac.idact) as actividades
                    FROM agenda a
                    LEFT JOIN sesion s ON s.idage = a.idage
                    LEFT JOIN actividad ac ON ac.idses = s.idses
                    WHERE a.idfic IN ($placeholders)
                    GROUP BY a.idfic";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute($idfics);
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $conteos[(string)$row['idfic']] = [
                    'agendas' => (int)$row['agendas'],
                    'sesiones' => (int)$row['sesiones'],
                    'actividades' => (int)$row['actividades']
                ];
            }
        } catch (Exception $e) {
            error_log("Error en Mcmas::getConteosPorFicha: " . $e->getMessage());
        }
        return $conteos;
    }

    /**
     * Nombre de la jornada de una ficha (1=Mañana, 2=Tarde, 3=Noche, 4=Fin de semana, 5=Virtual).
     */
    public static function nombreJornada($jornada) {
        $map = [1 => 'Mañana', 2 => 'Tarde', 3 => 'Noche', 4 => 'Fin de semana', 5 => 'Virtual'];
        $jornada = (int)$jornada;
        return $map[$jornada] ?? ('Jornada ' . $jornada);
    }

    /**
     * Normaliza una jornada escrita en la plantilla (1-5, M/T/N/F/V o nombre)
     * al valor canónico. Retorna null si no se reconoce.
     */
    public static function normalizarJornada($valor) {
        $v = mb_strtolower(trim((string)$valor));
        if ($v === '') {
            return null;
        }
        $map = [
            '1' => 'Mañana', 'm' => 'Mañana', 'mañana' => 'Mañana', 'manana' => 'Mañana',
            '2' => 'Tarde', 't' => 'Tarde', 'tarde' => 'Tarde',
            '3' => 'Noche', 'n' => 'Noche', 'noche' => 'Noche', 'nocturna' => 'Noche', 'nocturno' => 'Noche',
            '4' => 'Fin de semana', 'f' => 'Fin de semana', 'fin de semana' => 'Fin de semana', 'fds' => 'Fin de semana',
            '5' => 'Virtual', 'v' => 'Virtual', 'virtual' => 'Virtual',
        ];
        return $map[$v] ?? null;
    }

    /**
     * Documentos (agendas/planeación unificados) por ficha, con conteos de
     * sesiones y guías. Retorna [idfic => [filas...]] (máx. $limite por ficha).
     */
    public function getDocumentosPorFichas(array $idfics, $limite = 12) {
        $docs = [];
        $idfics = array_values(array_unique(array_filter(array_map('strval', $idfics))));
        if (empty($idfics)) {
            return $docs;
        }
        try {
            $placeholders = implode(',', array_fill(0, count($idfics), '?'));
            $sql = "SELECT a.idfic, a.idage, a.fas, a.actpro, a.fecini, a.fecfin, a.hor,
                           COUNT(DISTINCT s.idses) as sesiones,
                           COUNT(DISTINCT ac.idact) as actividades
                    FROM agenda a
                    LEFT JOIN sesion s ON s.idage = a.idage
                    LEFT JOIN actividad ac ON ac.idses = s.idses
                    WHERE a.idfic IN ($placeholders)
                    GROUP BY a.idage
                    ORDER BY a.fecini DESC, a.idage DESC";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute($idfics);
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $idfic = (string)$row['idfic'];
                if (!isset($docs[$idfic])) {
                    $docs[$idfic] = [];
                }
                if (count($docs[$idfic]) < (int)$limite) {
                    $docs[$idfic][] = $row;
                }
            }
        } catch (Exception $e) {
            error_log("Error en Mcmas::getDocumentosPorFichas: " . $e->getMessage());
        }
        return $docs;
    }

    /**
     * Instructor(es) a cargo por ficha: desde horario, con respaldo en
     * agenda (idins/idusu) y ficha (idusu). Retorna [idfic => 'Nombre1, Nombre2'].
     */
    public function getInstructoresPorFicha(array $idfics) {
        $nombres = [];
        $idfics = array_values(array_unique(array_filter(array_map('strval', $idfics))));
        if (empty($idfics)) {
            return $nombres;
        }
        $agregar = function ($idfic, $nom) use (&$nombres) {
            $idfic = (string)$idfic;
            $nom = trim((string)$nom);
            if ($nom === '') {
                return;
            }
            if (!isset($nombres[$idfic])) {
                $nombres[$idfic] = [];
            }
            if (!in_array($nom, $nombres[$idfic], true) && count($nombres[$idfic]) < 3) {
                $nombres[$idfic][] = $nom;
            }
        };
        try {
            $placeholders = implode(',', array_fill(0, count($idfics), '?'));
            // 1. Horario de la ficha
            $sql = "SELECT h.idfic, u.nomusu FROM horario h
                    INNER JOIN usuario u ON u.idusu = h.idusu
                    WHERE h.idfic IN ($placeholders)";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute($idfics);
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $agregar($row['idfic'], $row['nomusu']);
            }
            // 2. Respaldo: agenda (instructor o usuario que cargó)
            $faltan = array_values(array_filter($idfics, function ($id) use ($nombres) {
                return !isset($nombres[(string)$id]);
            }));
            if (!empty($faltan)) {
                $ph = implode(',', array_fill(0, count($faltan), '?'));
                $sql = "SELECT DISTINCT a.idfic, u.nomusu FROM agenda a
                        INNER JOIN usuario u ON u.idusu = COALESCE(NULLIF(a.idins, 0), NULLIF(a.idusu, 0))
                        WHERE a.idfic IN ($ph)";
                $stmt = $this->conexion->prepare($sql);
                $stmt->execute($faltan);
                foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                    $agregar($row['idfic'], $row['nomusu']);
                }
            }
            // 3. Respaldo: responsable registrado en la ficha
            $faltan = array_values(array_filter($idfics, function ($id) use ($nombres) {
                return !isset($nombres[(string)$id]);
            }));
            if (!empty($faltan)) {
                $ph = implode(',', array_fill(0, count($faltan), '?'));
                $sql = "SELECT f.idfic, u.nomusu FROM ficha f
                        INNER JOIN usuario u ON u.idusu = f.idusu
                        WHERE f.idfic IN ($ph)";
                $stmt = $this->conexion->prepare($sql);
                $stmt->execute($faltan);
                foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                    $agregar($row['idfic'], $row['nomusu']);
                }
            }
        } catch (Exception $e) {
            error_log("Error en Mcmas::getInstructoresPorFicha: " . $e->getMessage());
        }
        $salida = [];
        foreach ($nombres as $idfic => $lista) {
            $salida[$idfic] = implode(', ', $lista);
        }
        return $salida;
    }

    /**
     * IDs de instructores por ficha (horario, agenda, ficha). Retorna [idfic => [idus...]].
     */
    public function getIdsInstructoresPorFicha(array $idfics) {
        $mapa = [];
        $idfics = array_values(array_unique(array_filter(array_map('strval', $idfics))));
        if (empty($idfics)) {
            return $mapa;
        }
        $agregar = function ($idfic, $idusu) use (&$mapa) {
            $idfic = (string)$idfic;
            $idusu = (int)$idusu;
            if ($idusu <= 0) {
                return;
            }
            if (!isset($mapa[$idfic])) {
                $mapa[$idfic] = [];
            }
            if (!in_array($idusu, $mapa[$idfic], true)) {
                $mapa[$idfic][] = $idusu;
            }
        };
        try {
            $placeholders = implode(',', array_fill(0, count($idfics), '?'));
            $stmt = $this->conexion->prepare("SELECT idfic, idusu FROM horario WHERE idfic IN ($placeholders)");
            $stmt->execute($idfics);
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $agregar($row['idfic'], $row['idusu']);
            }
            $stmt = $this->conexion->prepare(
                "SELECT idfic, idins AS idusu FROM agenda
                 WHERE idfic IN ($placeholders) AND COALESCE(idins, 0) > 0
                 UNION
                 SELECT idfic, idusu AS idusu FROM agenda
                 WHERE idfic IN ($placeholders) AND COALESCE(idusu, 0) > 0"
            );
            $stmt->execute(array_merge($idfics, $idfics));
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $agregar($row['idfic'], $row['idusu']);
            }
            $stmt = $this->conexion->prepare("SELECT idfic, idusu FROM ficha WHERE idfic IN ($placeholders)");
            $stmt->execute($idfics);
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $agregar($row['idfic'], $row['idusu']);
            }
        } catch (Exception $e) {
            error_log("Error en Mcmas::getIdsInstructoresPorFicha: " . $e->getMessage());
        }
        return $mapa;
    }

    /**
     * Fichas con la misma agenda (mismo contenido) para cada agenda.
     * Retorna [idage => [idfics...]] solo con fichas del conjunto dado
     * (los idfic basura de cargas viejas quedan excluidos solos).
     */
    public function getFichasPorAgenda(array $idfics) {
        $porAgenda = [];
        $idfics = array_values(array_unique(array_filter(array_map('strval', $idfics))));
        if (empty($idfics)) {
            return $porAgenda;
        }
        try {
            $placeholders = implode(',', array_fill(0, count($idfics), '?'));
            $sql = "SELECT idage, idfic, actpro FROM agenda
                    WHERE idfic IN ($placeholders)
                      AND actpro IS NOT NULL AND CHAR_LENGTH(actpro) > 15
                      AND actpro NOT IN ('Actividad de Proyecto', 'ACTIVIDAD DEL PROYECTO')";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute($idfics);
            $grupos = [];
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $clave = mb_strtolower(trim((string)$row['actpro']));
                $grupos[$clave][(int)$row['idage']] = (string)$row['idfic'];
            }
            $permitidos = array_fill_keys(array_map('strval', $idfics), true);
            foreach ($grupos as $agendas) {
                $fichasGrupo = array_values(array_unique(array_values($agendas)));
                if (count($fichasGrupo) < 2) {
                    continue;
                }
                foreach ($agendas as $idage => $idfic) {
                    $otras = array_values(array_filter($fichasGrupo, function ($f) use ($idfic, $permitidos) {
                        return $f !== (string)$idfic && isset($permitidos[(string)$f]);
                    }));
                    if (!empty($otras)) {
                        $porAgenda[$idage] = array_slice($otras, 0, 6);
                    }
                }
            }
        } catch (Exception $e) {
            error_log("Error en Mcmas::getFichasPorAgenda: " . $e->getMessage());
        }
        return $porAgenda;
    }

    /**
     * Nombres de usuarios por id. Retorna [idusu => nomusu].
     */
    public function getNombresInstructores(array $idus) {
        $nombres = [];
        $idus = array_values(array_unique(array_filter(array_map('intval', $idus))));
        if (empty($idus)) {
            return $nombres;
        }
        try {
            $placeholders = implode(',', array_fill(0, count($idus), '?'));
            $sql = "SELECT idusu, nomusu FROM usuario WHERE idusu IN ($placeholders)";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute($idus);
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $nombres[(int)$row['idusu']] = trim((string)$row['nomusu']);
            }
        } catch (Exception $e) {
            error_log("Error en Mcmas::getNombresInstructores: " . $e->getMessage());
        }
        return $nombres;
    }

    /**
     * Clasifica a los responsables como instructor de planta o contratista.
     * El perfil base de usuario tiene prioridad; usuperfil solo completa la
     * clasificación cuando el usuario tiene roles adicionales.
     */
    public function getClasificacionInstructores(array $idus)
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $idus), static function ($id) {
            return $id > 0;
        })));
        if (empty($ids)) {
            return [];
        }

        $resultado = [];
        try {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $sqlBase = "SELECT u.idusu, u.idper, p.nomper
                       FROM usuario u
                       LEFT JOIN perfil p ON p.idper = u.idper
                       WHERE u.idusu IN ($placeholders)";
            $sqlPerfiles = "SELECT up.idusu, up.idper, p.nomper
                            FROM usupef up
                            LEFT JOIN perfil p ON p.idper = up.idper
                            WHERE up.idusu IN ($placeholders)";

            $stmt = $this->conexion->prepare($sqlBase);
            $stmt->execute($ids);
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $idusu = (int)$row['idusu'];
                $resultado[$idusu]['base_id'] = (int)$row['idper'];
                $resultado[$idusu]['base_nombre'] = trim((string)($row['nomper'] ?? ''));
                $resultado[$idusu]['perfiles'][(int)$row['idper']] = trim((string)($row['nomper'] ?? ''));
            }

            $stmt = $this->conexion->prepare($sqlPerfiles);
            $stmt->execute($ids);
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $idusu = (int)$row['idusu'];
                $idper = (int)$row['idper'];
                $resultado[$idusu]['perfiles'][$idper] = trim((string)($row['nomper'] ?? ''));
            }
        } catch (Exception $e) {
            error_log('Error clasificando instructores: ' . $e->getMessage());
        }

        $clasificados = [];
        foreach ($resultado as $idusu => $datos) {
            $baseId = (int)($datos['base_id'] ?? 0);
            $baseNombre = mb_strtolower((string)($datos['base_nombre'] ?? ''));
            $tieneContratista = $baseId === 12 || strpos($baseNombre, 'contratista') !== false;
            $tienePlanta = $baseId === 7 || strpos($baseNombre, 'instructor') !== false || strpos($baseNombre, 'docente') !== false || strpos($baseNombre, 'planta') !== false;

            foreach (($datos['perfiles'] ?? []) as $idper => $nombrePerfil) {
                $idper = (int)$idper;
                $nombrePerfil = mb_strtolower((string)$nombrePerfil);
                if ($idper === 12 || strpos($nombrePerfil, 'contratista') !== false) {
                    $tieneContratista = true;
                }
                if ($idper === 7 || strpos($nombrePerfil, 'instructor') !== false || strpos($nombrePerfil, 'docente') !== false) {
                    $tienePlanta = true;
                }
            }

            if ($baseId === 12 || ($baseId !== 7 && $tieneContratista && !$tienePlanta)) {
                $tipo = 'contratista';
            } elseif ($baseId === 7 || ($tienePlanta && !$tieneContratista)) {
                $tipo = 'planta';
            } elseif ($tienePlanta && $tieneContratista) {
                $tipo = $baseId === 12 ? 'contratista' : ($baseId === 7 ? 'planta' : 'sin_clasificar');
            } else {
                $tipo = 'sin_clasificar';
            }

            $etiquetas = [
                'planta' => 'Instructor de planta',
                'contratista' => 'Instructor contratista',
                'sin_clasificar' => 'Instructor sin clasificar',
            ];
            $clasificados[(int)$idusu] = [
                'tipo' => $tipo,
                'etiqueta' => $etiquetas[$tipo],
            ];
        }

        return $clasificados;
    }

    /**
     * Agrupa fichas por instructor a partir de [idfic => [idus...]].
     * Retorna ['instructores' => [['idusu', 'nombre', 'tipo', 'fichas']], 'pendientes' => [fichas sin instructor]].
     */
    public function agruparFichasPorInstructor(array $fichas, array $idsInstrPorFicha) {
        $grupos = [];
        $pendientes = [];
        $todosIds = [];
        foreach ($idsInstrPorFicha as $listaIds) {
            foreach ((array)$listaIds as $idusu) {
                $todosIds[(int)$idusu] = true;
            }
        }
        $nombres = $this->getNombresInstructores(array_keys($todosIds));
        $clasificaciones = $this->getClasificacionInstructores(array_keys($todosIds));
        foreach ($fichas as $fic) {
            $idfic = (string)($fic['idfic'] ?? '');
            $idus = $idsInstrPorFicha[$idfic] ?? [];
            if (empty($idus)) {
                $pendientes[] = $fic;
                continue;
            }
            foreach ($idus as $idusu) {
                $idusu = (int)$idusu;
                if (!isset($grupos[$idusu])) {
                    $grupos[$idusu] = [
                        'idusu' => $idusu,
                        'nombre' => ($nombres[$idusu] ?? ('Instructor ' . $idusu)),
                        'tipo' => $clasificaciones[$idusu]['tipo'] ?? 'sin_clasificar',
                        'etiqueta_tipo' => $clasificaciones[$idusu]['etiqueta'] ?? 'Instructor sin clasificar',
                        'fichas' => []
                    ];
                }
                $grupos[$idusu]['fichas'][] = $fic;
            }
        }
        $ordenTipos = ['planta' => 0, 'contratista' => 1, 'sin_clasificar' => 2];
        uasort($grupos, function ($a, $b) use ($ordenTipos) {
            $ordenA = $ordenTipos[$a['tipo']] ?? 9;
            $ordenB = $ordenTipos[$b['tipo']] ?? 9;
            return $ordenA === $ordenB
                ? strcmp((string)$a['nombre'], (string)$b['nombre'])
                : $ordenA <=> $ordenB;
        });
        return ['instructores' => array_values($grupos), 'pendientes' => $pendientes];
    }

    /**
     * Sesiones de las fichas dadas cuyas agendas no tienen instructor asignado.
     */
    public function getSesionesSinAsignar(array $idfics) {
        $idfics = array_values(array_unique(array_filter(array_map('strval', $idfics))));
        if (empty($idfics)) {
            return 0;
        }
        try {
            $placeholders = implode(',', array_fill(0, count($idfics), '?'));
            $sql = "SELECT COUNT(DISTINCT s.idses) FROM sesion s
                    INNER JOIN agenda a ON a.idage = s.idage
                    WHERE a.idfic IN ($placeholders)
                      AND COALESCE(a.idins, 0) = 0
                      AND COALESCE(a.idusu, 0) = 0";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute($idfics);
            return (int)$stmt->fetchColumn();
        } catch (Exception $e) {
            error_log("Error en Mcmas::getSesionesSinAsignar: " . $e->getMessage());
            return 0;
        }
    }
}


