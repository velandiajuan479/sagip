<?php
require_once(__DIR__ . '/conexion.php');

/**
 * Modelo de la vista "Carga de Juicios Evaluativos"
 *
 * Se encarga del Reporte de Juicios de Evaluación generado desde SENA Sofía Plus
 * (Administrar Ruta de Aprendizaje -> Reportes). El archivo .xls / .xlsx trae:
 *
 *   C2  Fecha del Reporte
 *   C3  Ficha de Caracterización
 *   C4  Cógigo (código del programa)
 *   C6  Denominación
 *   C8  Fecha Inicio   (serial de Excel)
 *   C9  Fecha Fin      (serial de Excel)
 *   F13 Cabecera de columnas de datos y a partir de la fila 14:
 *       B Número de Documento | E Estado | F Competencia | G Resultado de Aprendizaje
 *       H Juicio de Evaluación | J Fecha y Hora del Juicio | K Funcionario que registró
 */
class Desmcmje {
    private $idjui;
    private $idusu;
    private $idfic;
    private $idres;
    private $idcom;
    private $caljui;
    private $ndocins;
    private $fecha_reporte;
    private $fecjuieva;

    // Getters y Setters
    public function getIdjui() { return $this->idjui; }
    public function setIdjui($idjui) { $this->idjui = $idjui; }

    public function getIdusu() { return $this->idusu; }
    public function setIdusu($idusu) { $this->idusu = $idusu; }

    public function getIdfic() { return $this->idfic; }
    public function setIdfic($idfic) { $this->idfic = $idfic; }

    public function getIdres() { return $this->idres; }
    public function setIdres($idres) { $this->idres = $idres; }

    public function getIdcom() { return $this->idcom; }
    public function setIdcom($idcom) { $this->idcom = $idcom; }

    public function getCaljui() { return $this->caljui; }
    public function setCaljui($caljui) { $this->caljui = $caljui; }

    public function getNdocins() { return $this->ndocins; }
    public function setNdocins($ndocins) { $this->ndocins = $ndocins; }

    public function getFechaReporte() { return $this->fecha_reporte; }
    public function setFechaReporte($fecha_reporte) { $this->fecha_reporte = $fecha_reporte; }

    public function getFecjuieva() { return $this->fecjuieva; }
    public function setFecjuieva($fecjuieva) { $this->fecjuieva = $fecjuieva; }

    /**
     * Resumen de juicios importados por ficha
     */
    public function getResumenPorFicha() {
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $sql = "SELECT j.idfic, f.nomfic AS nomfic, COUNT(*) AS total,
                       SUM(CASE WHEN j.caljui IN ('APROBADO','NO APROBADO') THEN 1 ELSE 0 END) AS evaluados,
                       SUM(CASE WHEN j.caljui = 'POR EVALUAR' OR j.caljui IS NULL OR j.caljui = '' THEN 1 ELSE 0 END) AS por_evaluar,
                       MAX(j.fecha_importacion) AS ultima_importacion,
                       MAX(j.fecha_reporte) AS ultimo_reportado
                FROM juicio AS j
                LEFT JOIN ficha AS f ON j.idfic = f.idfic
                GROUP BY j.idfic, f.nomfic
                ORDER BY ultima_importacion DESC, j.idfic DESC";
        $stmt = $conexion->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Contadores exactos por estado de juicio (para la ficha seleccionada o todas)
     */
    public function getContadoresJuicios($idfic = null) {
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $sql = "SELECT COUNT(*) AS total,
                       SUM(CASE WHEN UPPER(TRIM(j.caljui)) = 'APROBADO' THEN 1 ELSE 0 END) AS aprobados,
                       SUM(CASE WHEN UPPER(TRIM(j.caljui)) = 'NO APROBADO' THEN 1 ELSE 0 END) AS no_aprobados,
                       SUM(CASE WHEN UPPER(TRIM(j.caljui)) = 'POR EVALUAR' OR j.caljui IS NULL OR j.caljui = '' THEN 1 ELSE 0 END) AS por_evaluar
                FROM juicio AS j";
        $params = [];
        if ($idfic) {
            $sql .= " WHERE j.idfic = :idfic";
            $params[':idfic'] = $idfic;
        }
        $stmt = $conexion->prepare($sql);
        $stmt->execute($params);
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);
        return [
            'total' => (int)($fila['total'] ?? 0),
            'aprobados' => (int)($fila['aprobados'] ?? 0),
            'no_aprobados' => (int)($fila['no_aprobados'] ?? 0),
            'por_evaluar' => (int)($fila['por_evaluar'] ?? 0)
        ];
    }

    /**
     * Listar juicios con aprendiz, competencia y resultado
     */
    public function getJuicios($idfic = null, $limite = 300, $caljui = null) {
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $sql = "SELECT j.idjui, j.idfic, j.caljui, j.fecjuieva, j.ndocins, j.fecha_reporte,
                       j.archivo_correlativo, j.fecha_importacion,
                       u.idusu, u.ndocusu, u.nomusu,
                       c.idcom, c.descom,
                       r.idres, r.nomres AS nomres,
                       ins.nomusu AS nombre_instructor
                FROM juicio AS j
                LEFT JOIN usuario AS u ON j.idusu = u.idusu
                LEFT JOIN resultado AS r ON j.idres = r.idres
                LEFT JOIN competencia AS c ON j.idcom = c.idcom
                LEFT JOIN usuario AS ins ON ins.ndocusu = j.ndocins";
        $where = [];
        $params = [];
        if ($idfic) {
            $where[] = "j.idfic = :idfic";
            $params[':idfic'] = $idfic;
        }
        if ($caljui) {
            $caljui = strtoupper(trim($caljui));
            if ($caljui === 'POR EVALUAR') {
                $where[] = "(UPPER(TRIM(j.caljui)) = 'POR EVALUAR' OR j.caljui IS NULL OR j.caljui = '')";
            } else {
                $where[] = "UPPER(TRIM(j.caljui)) = :caljui";
                $params[':caljui'] = $caljui;
            }
        }
        if ($where) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        if ((int)$limite > 0) {
            $sql .= " ORDER BY j.fecha_importacion DESC, j.idjui DESC LIMIT " . (int)$limite;
        } else {
            $sql .= " ORDER BY j.fecha_importacion DESC, j.idjui DESC";
        }
        $stmt = $conexion->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Agregados globales para el panel (dashboard) de juicios evaluativos
     */
    public function getDashboard() {
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();

        $fila = $conexion->query("SELECT COUNT(*) AS total,
                        COALESCE(SUM(CASE WHEN UPPER(TRIM(caljui)) = 'APROBADO' THEN 1 ELSE 0 END), 0) AS aprobados,
                        COALESCE(SUM(CASE WHEN UPPER(TRIM(caljui)) = 'NO APROBADO' THEN 1 ELSE 0 END), 0) AS no_aprobados,
                        COALESCE(SUM(CASE WHEN UPPER(TRIM(caljui)) = 'POR EVALUAR' OR caljui IS NULL OR caljui = '' THEN 1 ELSE 0 END), 0) AS por_evaluar,
                        COUNT(DISTINCT idfic) AS fichas
                 FROM juicio")->fetch(PDO::FETCH_ASSOC);
        $aprob = (int)($fila['aprobados'] ?? 0);
        $noApr = (int)($fila['no_aprobados'] ?? 0);
        $valid = $aprob + $noApr;
        $r['kpis'] = [
            'fichas' => (int)($fila['fichas'] ?? 0),
            'total' => (int)($fila['total'] ?? 0),
            'aprobados' => $aprob,
            'no_aprobados' => $noApr,
            'por_evaluar' => (int)($fila['por_evaluar'] ?? 0),
            'tasa' => $valid ? round($aprob * 100 / $valid) : 0
        ];

        return $r;
    }

    /**
     * Registrar el autoloader mínimo de PhpSpreadsheet (sin vendor/autoload.php
     * que exige PHP >= 8.1 por dependencias de Illuminate). Mismo patrón de dompdf.
     */
    private function registrarAutoloadPhpSpreadsheet() {
        if (class_exists('PhpOffice\PhpSpreadsheet\IOFactory', false)) {
            return;
        }
        $map = [
            'PhpOffice\PhpSpreadsheet\\' => dirname(__DIR__) . '/vendor/phpoffice/phpspreadsheet/src/PhpSpreadsheet',
            'Psr\SimpleCache\\'           => dirname(__DIR__) . '/vendor/psr/simple-cache/src',
            'Psr\Http\Message\\'          => dirname(__DIR__) . '/vendor/psr/http-message/src',
            'Psr\Http\Client\\'           => dirname(__DIR__) . '/vendor/psr/http-client/src',
            'Complex\\'                   => dirname(__DIR__) . '/vendor/markbaker/complex/src',
            'Matrix\\'                    => dirname(__DIR__) . '/vendor/markbaker/matrix/src',
            'ZipStream\\'                 => dirname(__DIR__) . '/vendor/maennchen/zipstream-php/src',
            'MyCLabs\Enum\\'              => dirname(__DIR__) . '/vendor/myclabs/php-enum/src',
        ];
        spl_autoload_register(function ($clase) use ($map) {
            foreach ($map as $prefijo => $dir) {
                if (strpos($clase, $prefijo) === 0) {
                    $rel = substr($clase, strlen($prefijo));
                    $archivo = $dir . '/' . str_replace('\\', '/', $rel) . '.php';
                    if (is_file($archivo)) {
                        require_once $archivo;
                    }
                    return;
                }
            }
        });
    }

    /**
     * Normalizar texto (mayúsculas y sin tildes) para comparar encabezados/estados
     */
    private function normalizar($texto) {
        $texto = strtr((string)$texto, [
            'á' => 'A', 'é' => 'E', 'í' => 'I', 'ó' => 'O', 'ú' => 'U', 'ü' => 'U', 'ñ' => 'N',
            'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U', 'Ü' => 'U', 'Ñ' => 'N'
        ]);
        $texto = strtoupper($texto);
        return trim(preg_replace('/\s+/u', ' ', $texto));
    }

    /**
     * Convertir un valor de celda a fecha Y-m-d (soporta serial de Excel o texto dd/mm/aa / yyyy-mm-dd)
     */
    private function valorAFecha($valor) {
        if ($valor === null || trim((string)$valor) === '') {
            return null;
        }
        if (is_numeric($valor)) {
            try {
                $obj = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float)$valor);
                return $obj->format('Y-m-d');
            } catch (Exception $e) {
                return null;
            }
        }
        $t = trim((string)$valor);
        if (preg_match('/^\d{4}-\d{2}-\d{2}/', $t, $m)) {
            return $m[0];
        }
        $partes = preg_split('/[\/\-]/', $t);
        if (count($partes) === 3) {
            $d = (int)$partes[0]; $mo = (int)$partes[1]; $a = (int)$partes[2];
            if ($a < 100) { $a += 2000; }
            if ($d >= 1 && $d <= 31 && $mo >= 1 && $mo <= 12) {
                return sprintf('%04d-%02d-%02d', $a, $mo, $d);
            }
        }
        return null;
    }

    /**
     * Importar el Reporte de Juicios de Evaluación de SENA Sofía Plus.
     * Reemplaza los juicios ya cargados de la ficha.
     *
     * @param string $archivo     Ruta temporal del archivo subido
     * @param string $nombre      Nombre original del archivo (archivo_correlativo)
     * @param string $fechaReporte Fecha del reporte (YYYY-MM-DD) o null para usar C2 del archivo
     * @return array Resumen de la importación
     */
    public function importarReporte($archivo, $nombre = '', $fechaReporte = null) {
        $resumen = [
            'exito' => false,
            'mensaje' => '',
            'idfic' => null,
            'denominacion' => '',
            'codpro' => null,
            'finific' => null,
            'ffinfic' => null,
            'fecha_reporte' => $fechaReporte,
            'archivo' => $nombre,
            'filas_leidas' => 0,
            'importados' => 0,
            'omitidos_estado' => 0,
            'estados_actualizados' => 0,
            'docs_estado_actualizado' => [],
            'no_encontrados' => [],
            'errores' => [],
        ];

        if (empty($archivo) || !is_file($archivo)) {
            $resumen['mensaje'] = "El archivo no existe o no fue recibido.";
            return $resumen;
        }

        $extension = strtolower(pathinfo($nombre ?: $archivo, PATHINFO_EXTENSION));
        if (!in_array($extension, ['xls', 'xlsx'])) {
            $resumen['mensaje'] = "Formato no permitido. Solo se admite .xls y .xlsx.";
            return $resumen;
        }

        ini_set('memory_limit', '512M');
        set_time_limit(300);

        try {
            $this->registrarAutoloadPhpSpreadsheet();

            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($archivo);
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($archivo);
            $hoja = $spreadsheet->getActiveSheet();

            // --- Metadatos del reporte ---
            $idfic = trim((string)$hoja->getCell('C3')->getValue());
            if ($idfic === '' && $fechaReporte) {
                // Sin ficha en C3: abandonar
            }
            if ($idfic === '') {
                $resumen['mensaje'] = "No se pudo leer el número de ficha (celda C3) del archivo.";
                return $resumen;
            }
            $resumen['idfic'] = $idfic;

            $fechaC2 = $this->valorAFecha($hoja->getCell('C2')->getValue());
            if (!$resumen['fecha_reporte'] && $fechaC2) {
                $resumen['fecha_reporte'] = $fechaC2;
            }
            if (!$resumen['fecha_reporte']) {
                $resumen['fecha_reporte'] = date('Y-m-d');
            }

            $resumen['denominacion'] = trim((string)$hoja->getCell('C6')->getValue());
            $resumen['codpro'] = (int)preg_replace('/\D+/', '', (string)$hoja->getCell('C4')->getValue());
            $resumen['finific']   = $this->valorAFecha($hoja->getCell('C8')->getValue());
            $resumen['ffinfic']   = $this->valorAFecha($hoja->getCell('C9')->getValue());
            $finificOk = $resumen['finific'] ?: '2022-07-18';
            $ffinficOk = $resumen['ffinfic'] ?: '2024-10-17';
            $codproOk  = $resumen['codpro'] ?: 228118;

            // --- Detectar fila de cabecera (a partir de la fila 13 normalmente) ---
            $filaCabecera = null;
            $colMap = [];
            $ultimaFila = min($hoja->getHighestRow(), 40);
            for ($fila = 1; $fila <= $ultimaFila; $fila++) {
                $filaMap = [];
                $clavesEncontradas = 0;
                foreach ($hoja->getRowIterator($fila, $fila) as $itFila) {
                    foreach ($itFila->getCellIterator() as $celda) {
                        $texto = $this->normalizar($celda->getValue());
                        $col = $celda->getColumn();
                        if ($texto === '') { continue; }

                        $clave = null;
                        if (preg_match('/^NUMERO\s+(DE\s+)?DOCUMENTO$/', $texto) || $texto === 'CEDULA' || $texto === 'C.C') { $clave = 'doc'; }
                        elseif (strpos($texto, 'TIPO DE DOCUMENTO') === 0) { $clave = 'tipodoc'; }
                        elseif ($texto === 'ESTADO') { $clave = 'estado'; }
                        elseif ($texto === 'COMPETENCIA') { $clave = 'competencia'; }
                        elseif (strpos($texto, 'RESULTADO') === 0) { $clave = 'resultado'; }
                        elseif (strpos($texto, 'JUICIO') === 0) { $clave = 'juicio'; }
                        elseif (strpos($texto, 'FECHA Y HORA DEL JUICIO') === 0 || $texto === 'FECHA DEL JUICIO') { $clave = 'fechajui'; }
                        elseif (strpos($texto, 'FUNCIONARIO') === 0) { $clave = 'funcionario'; }

                        if ($clave && !isset($filaMap[$clave])) {
                            $filaMap[$clave] = $col;
                            $clavesEncontradas++;
                        }
                    }
                }
                if (isset($filaMap['doc']) && isset($filaMap['resultado']) && isset($filaMap['juicio'])) {
                    $filaCabecera = $fila;
                    $colMap = $filaMap;
                    break;
                }
            }

            if (!$filaCabecera) {
                $resumen['mensaje'] = "No se encontró la fila de cabecera (Número de Documento, Resultado, Juicio...). Verifique el archivo.";
                return $resumen;
            }

            // --- Mapeo de estados del aprendiz a usuario.actusu (valor iddom=10) ---
            $mapaEstado = [
                'EN FORMACION'    => 1,
                'DESACTIVADO'     => 2,
                'DESERCION'       => 3,
                'CANCELADO'       => 4,
                'TRASLADO'        => 5,
                'RETIRO VOLUNTARIO' => 6,
            ];

            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $conexion->beginTransaction();

            // 1. Ficha: crear si no existe; si existe se sincronizan los datos del reporte
            $sqlF = $conexion->prepare("SELECT idfic FROM ficha WHERE idfic = :idfic");
            $sqlF->execute([':idfic' => $idfic]);
            if (!$sqlF->fetchColumn()) {
                $stmtInsF = $conexion->prepare(
                    "INSERT INTO ficha (idfic, nomfic, codpro, idusu, jornada, idcen, mun, finific, ffinfic)
                     VALUES (:idfic, :nomfic, :codpro, NULL, 1, 951310, '', :finific, :ffinfic)"
                );
                $stmtInsF->execute([
                    ':idfic' => $idfic,
                    ':nomfic' => $resumen['denominacion'] ?: 'Ficha ' . $idfic,
                    ':codpro' => $codproOk,
                    ':finific' => $finificOk,
                    ':ffinfic' => $ffinficOk,
                ]);
                $resumen['ficha_nueva'] = true;
            } else {
                $stmtUpdF = $conexion->prepare(
                    "UPDATE ficha SET nomfic = :nomfic, codpro = :codpro, finific = :finific, ffinfic = :ffinfic
                     WHERE idfic = :idfic"
                );
                $stmtUpdF->execute([
                    ':nomfic' => $resumen['denominacion'] ?: 'Ficha ' . $idfic,
                    ':codpro' => $codproOk,
                    ':finific' => $finificOk,
                    ':ffinfic' => $ffinficOk,
                    ':idfic' => $idfic,
                ]);
                $resumen['ficha_nueva'] = false;
            }

            // 2. Reemplazo: se eliminan los juicios previos de la ficha
            $stmtDel = $conexion->prepare("DELETE FROM juicio WHERE idfic = :idfic");
            $stmtDel->execute([':idfic' => $idfic]);
            $resumen['eliminados'] = $stmtDel->rowCount();

            $stmtUser  = $conexion->prepare("SELECT idusu, actusu FROM usuario WHERE ndocusu = :ndocusu LIMIT 1");
            $stmtUser2 = $conexion->prepare("SELECT idusu, actusu FROM usuario WHERE idusu = :idusu LIMIT 1");
            $stmtCom   = $conexion->prepare("SELECT idcom FROM competencia WHERE idcom = :idcom LIMIT 1");
            $stmtRes   = $conexion->prepare("SELECT idres FROM resultado WHERE idres = :idres LIMIT 1");
            $stmtInsCom = $conexion->prepare(
                "INSERT INTO competencia (idcom, descom, vercom, horcom, idval) VALUES (:idcom, :descom, 1, 40, 1)"
            );
            $stmtInsRes = $conexion->prepare(
                "INSERT INTO resultado (idres, nomres, idcom, ndeses) VALUES (:idres, :nomres, :idcom, 1)"
            );
            $stmtUpdEst = $conexion->prepare("UPDATE usuario SET actusu = :actusu WHERE idusu = :idusu");
            $stmtInsJui = $conexion->prepare(
                "INSERT INTO juicio (idusu, idfic, idres, caljui, ndocins, fecha_reporte, fecha_importacion, archivo_correlativo, fecjuieva, idcom)
                 VALUES (:idusu, :idfic, :idres, :caljui, :ndocins, :fecha_reporte, NOW(), :archivo, :fecjuieva, :idcom)"
            );

            $archivoCorrelativo = $nombre ?: basename($archivo);
            if (strlen($archivoCorrelativo) > 50) {
                $archivoCorrelativo = substr($archivoCorrelativo, 0, 50);
            }

            $estadosActualizadosPorDoc = [];

            for ($fila = $filaCabecera + 1; $fila <= $hoja->getHighestRow(); $fila++) {
                $numDoc = $colMap['doc'] ?? null;
                if (!$numDoc) { continue; }
                $docRaw = trim((string)$hoja->getCell($numDoc . $fila)->getValue());
                $doc = preg_replace('/\D+/', '', $docRaw);
                if ($doc === '') { continue; }
                $resumen['filas_leidas']++;

                // Competencia / Resultado
                $idcom = null; $nombreCom = null;
                $idres = null;
                $compRaw = (string)$hoja->getCell(($colMap['competencia'] ?? 'F') . $fila)->getValue();
                if (preg_match('/^\s*(\d+)\s*(?:-|:)\s*(.*)$/s', $compRaw, $m)) {
                    $idcom = (int)$m[1];
                    $nombreCom = trim($m[2]) ?: $compRaw;
                } elseif (preg_match('/^\s*(\d+)\s*$/s', $compRaw, $m)) {
                    $idcom = (int)$m[1];
                }

                $resRaw = (string)$hoja->getCell(($colMap['resultado'] ?? 'G') . $fila)->getValue();
                if (preg_match('/^\s*(\d+)\s*(?:-|:)\s*(.*)$/s', $resRaw, $m)) {
                    $idres = (int)$m[1];
                } elseif (preg_match('/^\s*(\d+)\s*$/s', $resRaw, $m)) {
                    $idres = (int)$m[1];
                }

                if (!$idcom || !$idres) {
                    continue;
                }

                // Estado del aprendiz (columna E). Si no existe el usuario, se omite y se cuenta.
                $stmtUser->execute([':ndocusu' => $doc]);
                $usuario = $stmtUser->fetch(PDO::FETCH_ASSOC);

                if (!$usuario) {
                    $resumen['no_encontrados'][] = $doc;
                    continue;
                }
                $idUsu = (int)$usuario['idusu'];

                $estadoRaw = $this->normalizar((string)$hoja->getCell(($colMap['estado'] ?? 'E') . $fila)->getValue());
                $actusu = isset($mapaEstado[$estadoRaw]) ? $mapaEstado[$estadoRaw] : 0;

                if ($actusu !== 1) {
                    // Retiro voluntario, cancelado, deserción, traslado, desactivado:
                    // se actualiza el estado del aprendiz y NO se importan sus juicios.
                    if ($actusu > 0 && (int)$usuario['actusu'] != $actusu) {
                        $stmtUpdEst->execute([':actusu' => $actusu, ':idusu' => $idUsu]);
                        if (!in_array($doc, $estadosActualizadosPorDoc)) {
                            $estadosActualizadosPorDoc[] = $doc;
                        }
                    }
                    $resumen['omitidos_estado']++;
                    continue;
                }

                // Upsert de competencia / resultado
                $stmtCom->execute([':idcom' => $idcom]);
                if (!$stmtCom->fetchColumn()) {
                    $stmtInsCom->execute([':idcom' => $idcom, ':descom' => $nombreCom]);
                }
                $stmtRes->execute([':idres' => $idres]);
                if (!$stmtRes->fetchColumn()) {
                    $stmtInsRes->execute([':idres' => $idres, ':nomres' => preg_replace('/^\d+\s*(?:-|:)\s*/', '', $resRaw), ':idcom' => $idcom]);
                }

                // Juicio de evaluación (H), fecha (J) y funcionario (K)
                $caljui = strtoupper(trim((string)$hoja->getCell(($colMap['juicio'] ?? 'H') . $fila)->getValue()));

                $fecjuieva = null;
                if ($caljui !== 'POR EVALUAR' && ($colMap['fechajui'] ?? null)) {
                    $fecjuieva = $this->valorAFecha($hoja->getCell($colMap['fechajui'] . $fila)->getValue());
                }

                $ndocins = '';
                if (isset($colMap['funcionario'])) {
                    $funcRaw = trim((string)$hoja->getCell($colMap['funcionario'] . $fila)->getValue());
                    if ($funcRaw !== '' && $funcRaw !== '-') {
                        $token = $funcRaw;
                        $posGuion = strpos($funcRaw, '-');
                        if ($posGuion !== false) {
                            $token = trim(substr($funcRaw, 0, $posGuion));
                        }
                        if (preg_match('/(\d+)/', $token, $mDocIns)) {
                            $ndocins = $mDocIns[1];
                        }
                    }
                }

                $stmtInsJui->execute([
                    ':idusu' => $idUsu,
                    ':idfic' => $idfic,
                    ':idres' => $idres,
                    ':caljui' => $caljui,
                    ':ndocins' => $ndocins,
                    ':fecha_reporte' => $resumen['fecha_reporte'],
                    ':archivo' => $archivoCorrelativo,
                    ':fecjuieva' => $fecjuieva,
                    ':idcom' => $idcom,
                ]);
                $resumen['importados']++;
            }

            $resumen['estados_actualizados'] = count($estadosActualizadosPorDoc);
            $resumen['docs_estado_actualizado'] = $estadosActualizadosPorDoc;

            $conexion->commit();
            $resumen['exito'] = true;

            if (!$resumen['filas_leidas']) {
                $resumen['mensaje'] = "El archivo fue leído pero no contenía filas de datos.";
            } elseif (!$resumen['importados']) {
                $resumen['mensaje'] = "El archivo se procesó, pero ningún juicio pudo importarse (revise estados y documentos pendientes).";
            } else {
                $resumen['mensaje'] = "Importación completada para la ficha {$idfic}.";
            }
        } catch (\Throwable $e) {
            if (isset($conexion) && $conexion->inTransaction()) {
                try { $conexion->rollBack(); } catch (Exception $ign) {}
            }
            $resumen['mensaje'] = "Error al importar el archivo: " . $e->getMessage();
            $resumen['errores'][] = $e->getMessage();
        }

        return $resumen;
    }
}