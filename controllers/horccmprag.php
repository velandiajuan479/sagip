<?php
require_once(__DIR__ . "/../models/hormcmprag.php");
require_once(__DIR__ . "/../models/hormacceso.php");
require_once(__DIR__ . "/../vendor/autoload.php");

use PhpOffice\PhpSpreadsheet\IOFactory;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$horAccesoCarga = new HormAcceso();
$contextoCarga = $horAccesoCarga->getContexto();
// Lectura: cualquier usuario autenticado con acceso a esta página (el permiso por
// ficha se aplica con getFichasPermitidas). La subida masiva sigue siendo admin.
if (!$contextoCarga['authenticated'] || !$horAccesoCarga->puedeAccederPaginaModulo(1507, $contextoCarga)) {
    http_response_code(403);
    echo 'No tiene permisos para acceder a esta página.';
    exit;
}
$puedeSubirCarga = !empty($contextoCarga['is_admin']);
$idficCargaSolicitado = $_POST['idfic'] ?? $_GET['idfic'] ?? '';
$fichaDestinoCarga = is_scalar($idficCargaSolicitado) ? trim((string)$idficCargaSolicitado) : '';

$mcmas = new Mcmas();

// Garantizar que $pg y $pag estén siempre definidos para el enrutamiento correcto
$pg = isset($_REQUEST["pg"]) ? $_REQUEST["pg"] : (isset($pag) ? $pag : 1507);
$pag = $pg;

$mensajeCarga = null;
$tipoMensajeCarga = 'info';
$resumenCarga = null;
$erroresCarga = [];

// -------------------------------------------------------------------------
// PROCESAMIENTO PRINCIPAL DE CARGA MASIVA (AGENDAS / PLANEACIÓN UNIFICADOS)
// Un solo formulario (btnDocumento/EXCEL_DOC); se conservan btnCmas/btnPlaneacion
// por compatibilidad. El procesamiento es el mismo: el tipo se detecta por contenido.
// Solo administradores pueden subir.
// -------------------------------------------------------------------------
$haySubida = (isset($_POST['btnDocumento']) && isset($_FILES['EXCEL_DOC']) && $_FILES['EXCEL_DOC']['error'] === UPLOAD_ERR_OK)
    || (isset($_POST['btnCmas']) && isset($_FILES['EXCEL_CMAS']) && $_FILES['EXCEL_CMAS']['error'] === UPLOAD_ERR_OK)
    || (isset($_POST['btnPlaneacion']) && isset($_FILES['EXCEL_PLANE']) && $_FILES['EXCEL_PLANE']['error'] === UPLOAD_ERR_OK);

if (!$haySubida) {
    foreach (['EXCEL_DOC', 'EXCEL_CMAS', 'EXCEL_PLANE'] as $campoArchivo) {
        if (!isset($_FILES[$campoArchivo]) || !is_array($_FILES[$campoArchivo]) || !isset($_FILES[$campoArchivo]['error'])) {
            continue;
        }
        $errorArchivo = (int)$_FILES[$campoArchivo]['error'];
        if ($errorArchivo === UPLOAD_ERR_OK) {
            continue;
        }
        $mensajesErrorArchivo = [
            UPLOAD_ERR_INI_SIZE => 'El archivo supera el tamaño permitido por el servidor.',
            UPLOAD_ERR_FORM_SIZE => 'El archivo supera el tamaño permitido por el formulario.',
            UPLOAD_ERR_PARTIAL => 'El archivo se subió incompleto. Vuelva a intentarlo.',
            UPLOAD_ERR_NO_FILE => 'Seleccione un archivo antes de continuar.',
            UPLOAD_ERR_NO_TMP_DIR => 'El servidor no tiene una carpeta temporal disponible.',
            UPLOAD_ERR_CANT_WRITE => 'El servidor no pudo escribir el archivo temporal.',
            UPLOAD_ERR_EXTENSION => 'Una extensión de PHP impidió procesar el archivo.',
        ];
        $tipoMensajeCarga = 'danger';
        $mensajeCarga = $mensajesErrorArchivo[$errorArchivo] ?? 'No se pudo cargar el archivo. Vuelva a intentarlo.';
        break;
    }
}

if ($haySubida) {
    if (!$puedeSubirCarga) {
        http_response_code(403);
        $tipoMensajeCarga = 'danger';
        $mensajeCarga = 'No tiene permisos para realizar carga masiva.';
    } else {

    // Aumentar límites temporales para procesar archivos grandes sin bloqueos
    @set_time_limit(180);
    @ini_set('memory_limit', '512M');

    if (isset($_POST['btnDocumento'])) {
        $fileInputName = 'EXCEL_DOC';
    } elseif (isset($_POST['btnCmas'])) {
        $fileInputName = 'EXCEL_CMAS';
    } else {
        $fileInputName = 'EXCEL_PLANE';
    }
    $tmpName = $_FILES[$fileInputName]['tmp_name'];
    $originalName = $_FILES[$fileInputName]['name'];
    $originalNameHtml = htmlspecialchars((string)$originalName, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

    // Archivo temporal con extensión real para evitar que PhpSpreadsheet falle al identificar
    $tempFileWithExt = $tmpName . '.' . $ext;
    if (!file_exists($tempFileWithExt)) {
        @copy($tmpName, $tempFileWithExt);
    }
    $fileToRead = file_exists($tempFileWithExt) ? $tempFileWithExt : $tmpName;

    try {
        if (!in_array($ext, ['xls', 'xlsx', 'csv', 'ods'])) {
            throw new Exception("El archivo '{$originalNameHtml}' no es un formato de hoja de cálculo válido (.xls, .xlsx). Para procesar datos masivos en la base de datos se requiere un archivo Excel.");
        }

        $objPHPExcel = null;
        $readerErrors = [];

        // 1. Intentar cargar usando lectores explícitos según la extensión
        $candidateReaders = [];
        if ($ext === 'xlsx') {
            $candidateReaders = ['Xlsx', 'Xls', 'Csv', 'Ods', 'Xml'];
        } elseif ($ext === 'xls') {
            $candidateReaders = ['Xls', 'Xlsx', 'Csv', 'Ods', 'Xml'];
        } elseif ($ext === 'csv') {
            $candidateReaders = ['Csv', 'Xls', 'Xlsx'];
        } else {
            $candidateReaders = ['Xlsx', 'Xls', 'Csv', 'Ods', 'Xml'];
        }

        foreach ($candidateReaders as $rName) {
            try {
                $readerClass = "\\PhpOffice\\PhpSpreadsheet\\Reader\\" . $rName;
                if (class_exists($readerClass)) {
                    $reader = new $readerClass();
                    $reader->setReadDataOnly(true);
                    $objPHPExcel = $reader->load($fileToRead);
                    if ($objPHPExcel) {
                        break;
                    }
                }
            } catch (\Throwable $re) {
                $readerErrors[] = $rName . ": " . $re->getMessage();
            }
        }

        // 2. Si fallaron los lectores específicos, intentar con IOFactory::identify como último recurso
        if (!$objPHPExcel) {
            try {
                $inputFileType = IOFactory::identify($fileToRead);
                $objReader = IOFactory::createReader($inputFileType);
                $objReader->setReadDataOnly(true);
                $objPHPExcel = $objReader->load($fileToRead);
            } catch (\Throwable $ie) {
                $readerErrors[] = "IOFactory: " . $ie->getMessage();
            }
        }

        if (!$objPHPExcel) {
            throw new Exception("No se pudo leer el archivo '{$originalNameHtml}'. Asegúrese de que sea un archivo de Excel válido y no esté dañado.");
        }

        // Limpiar archivo temporal si se creó
        if (file_exists($tempFileWithExt) && $tempFileWithExt !== $tmpName) {
            @unlink($tempFileWithExt);
        }

        // Seleccionar inteligentemente la hoja que realmente contiene datos
        $selectedSheet = null;
        $sheetTitle = '';
        $maxScore = -1;
        $bestRowsData = [];

        foreach ($objPHPExcel->getAllSheets() as $sh) {
            $rData = $sh->toArray(null, true, true, true);
            $nonEmptyCount = 0;
            $hasKeywords = false;
            foreach ($rData as $r) {
                $joined = mb_strtoupper(implode(' ', array_filter($r)));
                if (!empty(trim($joined))) {
                    $nonEmptyCount++;
                    if (stripos($joined, 'COMPETENCIA') !== false || stripos($joined, 'RESULTADO') !== false || stripos($joined, 'FASE') !== false || stripos($joined, 'ACTIVIDAD') !== false || stripos($joined, 'PROGRAMA') !== false) {
                        $hasKeywords = true;
                    }
                }
            }

            $score = $nonEmptyCount + ($hasKeywords ? 500 : 0);
            if ($score > $maxScore) {
                $maxScore = $score;
                $selectedSheet = $sh;
                $sheetTitle = $sh->getTitle();
                $bestRowsData = $rData;
            }
        }

        $hojaUnificada = $objPHPExcel->getSheetByName('UNIFICADO');
        if ($hojaUnificada !== null) {
            $selectedSheet = $hojaUnificada;
            $sheetTitle = $selectedSheet->getTitle();
            $bestRowsData = $selectedSheet->toArray(null, true, true, true);
        }

        if (!$selectedSheet || empty($bestRowsData)) {
            $selectedSheet = $objPHPExcel->getSheet(0);
            $sheetTitle = $selectedSheet->getTitle();
            $bestRowsData = $selectedSheet->toArray(null, true, true, true);
        }

        $allRows = $bestRowsData;
        $sheetTitleHtml = htmlspecialchars((string)$sheetTitle, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        // La plantilla institucional UNIFICADO usa encabezados en una fila
        // distinta y más columnas que la plantilla simple. Se detecta el
        // encabezado por nombres de columna y se leen los datos posteriores.
        $normalizarCarga = static function ($valor) {
            $valor = trim(preg_replace('/\\s+/u', ' ', (string)$valor));
            $valor = strtr($valor, [
                'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U', 'Ü' => 'U',
                'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u',
            ]);
            return strtoupper($valor);
        };
        $headerMapaCarga = [];
        $headerIndiceCarga = null;
        $headerPuntajeCarga = 0;
        foreach ($allRows as $indiceFila => $filaCarga) {
            $mapa = [];
            foreach ($filaCarga as $columna => $valor) {
                $etiqueta = $normalizarCarga($valor);
                if ($etiqueta === '') {
                    continue;
                }
                if (strpos($etiqueta, 'FASE') !== false) {
                    $mapa['fase'] = $columna;
                }
                if (strpos($etiqueta, 'ACTIVIDAD') !== false && strpos($etiqueta, 'APRENDIZAJE') === false) {
                    $mapa['actividad'] = $columna;
                }
                if (strpos($etiqueta, 'COMPETENCIA') !== false) {
                    $mapa['competencia'] = $columna;
                }
                if (strpos($etiqueta, 'RESULTADO') !== false) {
                    $mapa['resultado'] = $columna;
                }
                if (strpos($etiqueta, 'DURACION') !== false || strpos($etiqueta, 'HORAS POR RAP') !== false) {
                    $mapa['duracion'] = $columna;
                }
                if (strpos($etiqueta, 'FECHA INICIO') !== false) {
                    $mapa['fecha_inicio'] = $columna;
                }
                if (strpos($etiqueta, 'FECHA FIN') !== false) {
                    $mapa['fecha_fin'] = $columna;
                }
                if (strpos($etiqueta, 'JORNADA') !== false) {
                    $mapa['jornada'] = $columna;
                }
                if (strpos($etiqueta, 'TIPO') !== false) {
                    $mapa['tipo'] = $columna;
                }
                if (strpos($etiqueta, 'SABERES DE CONCEPTOS') !== false) {
                    $mapa['saberes_conceptos'] = $columna;
                }
                if (strpos($etiqueta, 'SABERES DE PROCESO') !== false) {
                    $mapa['saberes_proceso'] = $columna;
                }
                if (strpos($etiqueta, 'CRITERIOS DE EVALUACI') !== false) {
                    $mapa['criterios'] = $columna;
                }
                if (strpos($etiqueta, 'ACTIVIDADES DE APRENDIZAJE') !== false) {
                    $mapa['actividades'] = $columna;
                }
                if (strpos($etiqueta, 'HORAS TRABAJO DIRECTO') !== false) {
                    $mapa['horas_directo'] = $columna;
                }
                if (strpos($etiqueta, 'HORAS TRABAJO INDEPENDIENTE') !== false) {
                    $mapa['horas_independiente'] = $columna;
                }
                if (strpos($etiqueta, 'DESCRIPCION DE LA EVIDENCIA') !== false) {
                    $mapa['evidencia'] = $columna;
                }
                if (strpos($etiqueta, 'ESTRATEGIAS DIDACTICAS') !== false) {
                    $mapa['estrategias'] = $columna;
                }
                if (strpos($etiqueta, 'AMBIENTE') !== false) {
                    $mapa['ambiente'] = $columna;
                }
                if (strpos($etiqueta, 'MATERIALES DE FORMACION') !== false) {
                    $mapa['materiales'] = $columna;
                }
                if (strpos($etiqueta, 'INSTRUCTORES RESPONSABLES') !== false) {
                    $mapa['instructores'] = $columna;
                }
                if (strpos($etiqueta, 'OBSERVACIONES') !== false) {
                    $mapa['observaciones'] = $columna;
                }
                if (preg_match('/^COL([1-9]|1[0-6])$/', $etiqueta, $coincidenciaColumna)) {
                    $mapa['col' . (int)$coincidenciaColumna[1]] = $columna;
                }
            }
            $puntaje = 0;
            foreach (['fase', 'actividad', 'competencia', 'resultado'] as $claveObligatoria) {
                if (isset($mapa[$claveObligatoria])) {
                    $puntaje++;
                }
            }
            if ($puntaje >= 3 && $puntaje > $headerPuntajeCarga) {
                $headerPuntajeCarga = $puntaje;
                $headerMapaCarga = $mapa;
                $headerIndiceCarga = (int)$indiceFila;
            }
        }

        $fichaDetectada = false;
        $fichaEnArchivo = '';
        $programaDetectado = '';
        $codproDetectado = '';
        $proyectoDetectado = '';
        $filasPrevias = [];
        foreach ($allRows as $indiceMeta => $filaMeta) {
            if ($headerIndiceCarga !== null && (int)$indiceMeta >= (int)$headerIndiceCarga) {
                break;
            }
            $filasPrevias[] = $filaMeta;
        }
        foreach ($filasPrevias as $filaMeta) {
            $valoresMeta = [];
            foreach ($filaMeta as $columna => $valor) {
                $texto = trim((string)$valor);
                if ($texto !== '') {
                    $valoresMeta[] = [$normalizarCarga($texto), $texto];
                }
            }
            $joinedMeta = implode(' ', array_column($valoresMeta, 1));
            if (preg_match('/FICHA[^0-9]{0,12}([0-9]{6,8}(?:\s+[A-Z0-9]{1,2})?)/i', $joinedMeta, $coincidenciaFichaFila)) {
                $fichaEnArchivo = strtoupper(preg_replace('/\s+/', '', $coincidenciaFichaFila[1]));
                $fichaDetectada = true;
            }
            foreach ($valoresMeta as $posicion => $meta) {
                $etiqueta = $meta[0];
                if (preg_match('/FICHA[^0-9]{0,12}([0-9]{6,8}(?:\s+[A-Z0-9]{1,2})?)/i', $meta[1], $coincidencia)) {
                    $fichaEnArchivo = strtoupper(preg_replace('/\s+/', '', $coincidencia[1]));
                    $fichaDetectada = true;
                }
                if (strpos($etiqueta, 'PROGRAMA') !== false) {
                    for ($siguiente = $posicion + 1; $siguiente < count($valoresMeta); $siguiente++) {
                        $valorPrograma = trim($valoresMeta[$siguiente][1]);
                        if ($valorPrograma !== '' && stripos($valorPrograma, 'PROGRAMA') === false) {
                            if (preg_match('/^\s*(\d{4,8})(?:\s*[- ]?\s*V(?:ERSION)?\s*\d+(?:\.\d+)?)?\s*$/i', $valorPrograma, $coincidenciaPrograma)) {
                                $codproDetectado = $coincidenciaPrograma[1];
                            }
                            if ($programaDetectado === '' && !preg_match('/^\\d{4,8}(?:\\s+V\\s*\\d+(?:\\.\\d+)?)?$/i', $valorPrograma)) {
                                $programaDetectado = $valorPrograma;
                            }
                            break;
                        }
                    }
                }
                if (strpos($etiqueta, 'PROYECTO') !== false && strpos($etiqueta, 'FORMATO') === false) {
                    for ($siguiente = $posicion + 1; $siguiente < count($valoresMeta); $siguiente++) {
                        $valorProyecto = trim($valoresMeta[$siguiente][1]);
                        if ($valorProyecto !== '' && stripos($valorProyecto, 'PROYECTO') === false) {
                            $proyectoDetectado = $valorProyecto;
                            break;
                        }
                    }
                }
            }
        }

        $filasValidas = 0;
        $filasReconocidas = 0;
        $filasConError = 0;
        $competenciasProcesadas = 0;
        $resultadosProcesados = 0;
        $agendasCreadas = 0;
        $planeacionesGuardadas = 0;
        $programasProcesados = [];
        $proyectosProcesados = [];

        $faseActual = 'FASE 1: ANÁLISIS';
        $actividadActual = 'Actividad de Proyecto';
        $competenciaActual = '';
        $programaActual = $programaDetectado !== '' ? $programaDetectado : 'ANALISIS Y DESARROLLO DE SOFTWARE';
        $codproActual = $codproDetectado !== '' ? $codproDetectado : '228118';
        $proyectoActual = $proyectoDetectado !== '' ? $proyectoDetectado : 'SISTEMA INTEGRADO DE GESTION SAGISENA';
        $fichaSolicitada = strtoupper($fichaDestinoCarga);
        $fichaSolicitadaValida = $fichaSolicitada !== '' && preg_match('/^[0-9]{6,8}[A-Z0-9]{0,2}$/i', $fichaSolicitada);
        $fichaActual = $fichaEnArchivo !== '' ? $fichaEnArchivo : ($fichaSolicitadaValida ? $fichaSolicitada : '');
        $fichaResuelta = $fichaActual;
        $fichaDisponible = $fichaResuelta !== '';
        $fichaNoPermitida = $fichaDisponible && !$horAccesoCarga->puedeVerFicha($fichaResuelta, $contextoCarga);
        if ($fichaNoPermitida) {
            $fichaDisponible = false;
        }
        $fichaDetectada = $fichaEnArchivo !== '';
        $filasLeidas = 0;
        $valorCabecera = static function ($row, $clave) use ($headerMapaCarga) {
            $columna = $headerMapaCarga[$clave] ?? '';
            return $columna !== '' && isset($row[$columna]) ? trim((string)$row[$columna]) : '';
        };

        // Escanear solo las filas de datos posteriores al encabezado detectado.
        foreach ($allRows as $rowNum => $row) {
            if (!$fichaDisponible) {
                break;
            }
            if ($fichaDestinoCarga !== '' && !$fichaSolicitadaValida) {
                break;
            }
            if ($headerIndiceCarga === null || (int)$rowNum <= (int)$headerIndiceCarga) {
                continue;
            }
            $rowValues = array_values(array_filter(array_map('trim', $row)));
            if (empty($rowValues)) {
                continue;
            }
            $filasLeidas++;

            $colA = $valorCabecera($row, 'fase');
            $colB = $valorCabecera($row, 'actividad');
            $colC = $valorCabecera($row, 'competencia');
            $resultado = $valorCabecera($row, 'resultado');
            $duracionTexto = $valorCabecera($row, 'duracion');
            $fecIniTexto = $valorCabecera($row, 'fecha_inicio');
            $fecFinTexto = $valorCabecera($row, 'fecha_fin');
            $jorDoc = Mcmas::normalizarJornada($valorCabecera($row, 'jornada'));

            // Actualizar el contexto de la fila; las filas de continuación sin
            // actividad se ignoran para no duplicar agendas.
            if ($colA !== '') {
                $faseActual = $colA;
            }
            if ($colB !== '') {
                $actividadActual = $colB;
            }
            if ($colC !== '') {
                $competenciaActual = $colC;
            }

            // Validación mínima: una fila necesita actividad y al menos
            // competencia o resultado asociado.
            if ($colB === '' || ($competenciaActual === '' && $resultado === '')) {
                continue;
            }
            $filasReconocidas++;

            // Guardar la fila cruda en col1..col16 y cargar el modelo de planeacion_p.
            $datosPlaneacion = [
                'col1' => $colC,
                'col2' => $resultado,
                'col3' => $valorCabecera($row, 'tipo'),
                'col4' => $valorCabecera($row, 'saberes_conceptos'),
                'col5' => $valorCabecera($row, 'saberes_proceso'),
                'col6' => $valorCabecera($row, 'criterios'),
                'col7' => $valorCabecera($row, 'actividades'),
                'col8' => $duracionTexto,
                'col9' => $valorCabecera($row, 'horas_directo'),
                'col10' => $valorCabecera($row, 'horas_independiente'),
                'col11' => $valorCabecera($row, 'evidencia'),
                'col12' => $valorCabecera($row, 'estrategias'),
                'col13' => $valorCabecera($row, 'ambiente'),
                'col14' => $valorCabecera($row, 'materiales'),
                'col15' => $valorCabecera($row, 'instructores'),
                'col16' => $valorCabecera($row, 'observaciones'),
            ];
            // Si el archivo usa encabezados literalmente col1..col16, se respetan.
            for ($columnaPlaneacion = 1; $columnaPlaneacion <= 16; $columnaPlaneacion++) {
                $valorColumnaPlaneacion = $valorCabecera($row, 'col' . $columnaPlaneacion);
                if ($valorColumnaPlaneacion !== '') {
                    $datosPlaneacion['col' . $columnaPlaneacion] = $valorColumnaPlaneacion;
                }
            }
            $idPlaneacion = $mcmas->guardarCarguePlaneacion($datosPlaneacion);
            if ($idPlaneacion === null) {
                $filasConError++;
                $erroresCarga[] = 'Fila ' . ((int)$rowNum + 1) . ': no se pudo guardar en cargueplaneacion/planeacion_p.';
                continue;
            }
            $planeacionesGuardadas++;

            // 1. Validar y crear / relacionar PROGRAMA (evitando duplicados)
            $codpro = $mcmas->buscarOCrearPrograma($codproActual, $programaActual);
            if ($codpro) {
                $programasProcesados[$codpro] = $programaActual;
            } else {
                $filasConError++;
                $erroresCarga[] = 'Fila ' . ((int)$rowNum + 1) . ': no se pudo validar o crear el programa.';
                continue;
            }

            // 2. Relacionar Programa → PROYECTO y FICHA
            $mcmas->relacionarProgramaProyectoFicha($codpro, $proyectoActual, 'PRY-001', $fichaActual);
            $proyectosProcesados['PRY-001'] = $proyectoActual;

            // 3. Relacionar Programa → COMPETENCIA
            $idcom = $mcmas->buscarOCrearCompetencia($competenciaActual ?: 'Competencia Técnica de Formación', $codpro);
            if ($idcom) {
                $competenciasProcesadas++;
            } else {
                $filasConError++;
                $erroresCarga[] = 'Fila ' . ((int)$rowNum + 1) . ': no se pudo validar o crear la competencia.';
                continue;
            }

            // 4. Relacionar Competencia → RESULTADO DE APRENDIZAJE
            $duracion = $duracionTexto !== '' && is_numeric(str_replace(',', '.', $duracionTexto))
                ? (int)round((float)str_replace(',', '.', $duracionTexto))
                : 4;
            $numSesiones = max(1, (int)ceil($duracion / 4));

            if (empty($resultado)) {
                $resultado = "Resultado de Aprendizaje - " . substr($competenciaActual, 0, 80);
            }

            $idres = $mcmas->buscarOCrearResultado($resultado, $idcom, $numSesiones);
            if ($idres) {
                $resultadosProcesados++;
            } else {
                $filasConError++;
                $erroresCarga[] = 'Fila ' . ((int)$rowNum + 1) . ': no se pudo validar o crear el resultado de aprendizaje.';
                continue;
            }

            // 5. Normalizar y validar FECHAS
            $fecIni = Mcmas::normalizarFecha($fecIniTexto) ?: date('Y-m-d');
            $diasPorDefecto = max(1, $numSesiones - 1);
            $fecFin = Mcmas::normalizarFecha($fecFinTexto);
            if ($fecFin === null) {
                $fecFin = date('Y-m-d', strtotime($fecIni . ' +' . $diasPorDefecto . ' days'));
            }

            if (strtotime($fecIni) > strtotime($fecFin)) {
                $temp = $fecIni;
                $fecIni = $fecFin;
                $fecFin = $temp;
            }

            // 6. Guardar en AGENDA, SESIONES y ACTIVIDADES
            if ($idcom && $idres) {
                $resAge = $mcmas->guardarAgendaCompleta([
                    'idfic' => $fichaActual,
                    'codpro' => $codpro,
                    'fas' => $faseActual,
                    'actpro' => $actividadActual,
                    'idcom' => $idcom,
                    'idres' => $idres,
                    'fecini' => $fecIni,
                    'fecfin' => $fecFin,
                    'nomact' => $actividadActual,
                    'duract' => $duracion,
                    'numses' => $numSesiones,
                    'jor' => $jorDoc
                ]);

                if ($resAge) {
                    $agendasCreadas++;
                    $filasValidas++;
                } else {
                    $filasConError++;
                    $erroresCarga[] = 'Fila ' . ((int)$rowNum + 1) . ': se reconocieron los datos, pero no se pudo guardar agenda, sesión o actividad.';
                }
            }
        }

        $resumenCarga = [
            'nombre_archivo' => $originalName,
            'hoja_procesada' => $sheetTitle,
            'filas_leidas' => $filasLeidas,
            'filas_reconocidas' => $filasReconocidas,
            'filas_validas' => $filasValidas,
            'filas_errores' => $filasConError,
            'ficha_detectada' => $fichaEnArchivo !== '' ? $fichaEnArchivo : ($fichaSolicitadaValida ? $fichaSolicitada : null),
            'header_fila' => $headerIndiceCarga !== null ? ((int)$headerIndiceCarga + 1) : null,
            'programas' => count($programasProcesados),
            'competencias' => $competenciasProcesadas,
            'resultados' => $resultadosProcesados,
            'agendas' => $agendasCreadas,
            'planeaciones' => $planeacionesGuardadas,
            'errores_detalle' => $erroresCarga
        ];

        // Revisión del diligenciamiento: mensaje claro con lo que se cargó y avisos.
        if ($fichaDestinoCarga !== '' && !$fichaSolicitadaValida) {
            $tipoMensajeCarga = 'danger';
            $mensajeCarga = 'La ficha de destino no es válida. Use 6 a 8 dígitos y, si aplica, un sufijo alfanumérico breve.';
        } elseif ($fichaNoPermitida) {
            $tipoMensajeCarga = 'danger';
            $mensajeCarga = 'La ficha indicada no existe o no está disponible para este usuario. Seleccione una ficha existente antes de cargar.';
        } elseif (!$fichaDisponible) {
            $tipoMensajeCarga = 'danger';
            $mensajeCarga = 'No se encontró una FICHA en el archivo. Indique la ficha de destino en el formulario de carga antes de subirlo.';
        } elseif ($headerIndiceCarga === null) {
            $tipoMensajeCarga = 'danger';
            $mensajeCarga = "No se encontró un encabezado de actividades en la hoja <strong>{$sheetTitleHtml}</strong> del archivo <strong>{$originalNameHtml}</strong>. "
                . 'Use la hoja UNIFICADO o la plantilla de carga y conserve sus encabezados.';
        } elseif ($filasValidas === 0 && $filasReconocidas > 0) {
            $tipoMensajeCarga = 'danger';
            $detalleErrores = htmlspecialchars(implode('; ', array_slice($erroresCarga, 0, 3)), ENT_QUOTES, 'UTF-8');
            $mensajeCarga = "Se reconocieron {$filasReconocidas} registro(s) válido(s) en la hoja <strong>{$sheetTitleHtml}</strong>, pero no se pudieron guardar. "
                . 'Revise las relaciones de agenda, sesión y actividad en la base de datos.';
            if ($detalleErrores !== '') {
                $mensajeCarga .= ' <strong>Detalle:</strong> ' . $detalleErrores;
            }
        } elseif ($filasValidas === 0) {
            $tipoMensajeCarga = 'danger';
            $lineaEncabezado = (int)$headerIndiceCarga + 1;
            $mensajeCarga = "La hoja <strong>{$sheetTitleHtml}</strong> tiene el encabezado en la fila {$lineaEncabezado}, pero no contiene filas con actividad, competencia o resultado. "
                . 'Verifique que las filas de datos estén debajo del encabezado y que la ficha de destino sea correcta.';
        } else {
            $tipoMensajeCarga = ($filasConError === 0) ? 'success' : 'warning';
            $mensajeCarga = "Archivo <strong>{$originalNameHtml}</strong> procesado: {$filasValidas} registro(s) válido(s), "
                . "{$agendasCreadas} agenda(s), {$competenciasProcesadas} competencia(s) y {$resultadosProcesados} resultado(s).";
        }

    } catch (\Throwable $e) {
        $tipoMensajeCarga = 'danger';
        $mensajeCarga = "Error al procesar el archivo: " . $e->getMessage();
    }
    } // fin permiso admin para subida
}

// Programas del usuario (cada programa una sola vez) con sus fichas autorizadas.
// El permiso por ficha ya viene aplicado en getFichasPermitidas().
$fichasPermitidas = $horAccesoCarga->getFichasPermitidas($contextoCarga);

// Por defecto solo fichas vigentes (ffinfic >= hoy) para no cargar cientos de
// fichas terminadas; con ?ver=todas se muestran todas las permitidas.
$verTodasCarga = (isset($_GET['ver']) && $_GET['ver'] === 'todas');
$hoyCarga = date('Y-m-d');
$totalFichasCarga = count($fichasPermitidas);
$fichasVigentesCarga = array_values(array_filter($fichasPermitidas, function ($f) use ($hoyCarga) {
    $fin = trim((string)($f['ffinfic'] ?? ''));
    return $fin === '' || $fin === '0000-00-00' || $fin >= $hoyCarga;
}));
$fichasVistaCarga = $verTodasCarga ? $fichasPermitidas : $fichasVigentesCarga;

// Filtros sin JavaScript: texto, vigencia y tipo de instructor.
$qCarga = trim((string)($_GET['q'] ?? ''));
$tipoCarga = mb_strtolower(trim((string)($_GET['tipo'] ?? 'todos')));
$tiposCargaValidos = ['todos', 'planta', 'contratista', 'sin_clasificar', 'sin_asignar'];
if (!in_array($tipoCarga, $tiposCargaValidos, true)) {
    $tipoCarga = 'todos';
}

if ($qCarga !== '') {
    $idsInstrFiltroCarga = $mcmas->getIdsInstructoresPorFicha(array_column($fichasVistaCarga, 'idfic'));
    $todosIdsFiltroCarga = [];
    foreach ($idsInstrFiltroCarga as $listaIdsFiltro) {
        foreach ((array)$listaIdsFiltro as $idusuFiltro) {
            $todosIdsFiltroCarga[(int)$idusuFiltro] = true;
        }
    }
    $nombresFiltroCarga = $mcmas->getNombresInstructores(array_keys($todosIdsFiltroCarga));
    $normalizarBusqueda = static function ($valor) {
        $valor = mb_strtolower(trim((string)$valor));
        $normalizado = @iconv('UTF-8', 'ASCII//TRANSLIT', $valor);
        return $normalizado === false ? $valor : $normalizado;
    };
    $normQCarga = $normalizarBusqueda($qCarga);
    $qEsPendientes = (
        mb_strpos($normQCarga, 'sin instructor') !== false
        || mb_strpos($normQCarga, 'pendiente') !== false
        || mb_strpos($normQCarga, 'sin asignar') !== false
    );

    $fichasVistaCarga = array_values(array_filter($fichasVistaCarga, static function ($f) use ($idsInstrFiltroCarga, $nombresFiltroCarga, $normalizarBusqueda, $normQCarga, $qEsPendientes) {
        $textos = [
            $f['idfic'] ?? '',
            $f['nomfic'] ?? '',
            $f['nompro'] ?? '',
            $f['codpro'] ?? '',
            Mcmas::nombreJornada($f['jornada'] ?? 0),
        ];
        foreach ($textos as $texto) {
            $textoNormalizado = $normalizarBusqueda($texto);
            if ($textoNormalizado !== '' && mb_strpos($textoNormalizado, $normQCarga) !== false) {
                return true;
            }
        }

        $idfic = (string)($f['idfic'] ?? '');
        $idus = $idsInstrFiltroCarga[$idfic] ?? [];
        if (empty($idus)) {
            return $qEsPendientes;
        }
        foreach ($idus as $idusu) {
            $nombreNormalizado = $normalizarBusqueda($nombresFiltroCarga[(int)$idusu] ?? '');
            if ($nombreNormalizado !== '' && mb_strpos($nombreNormalizado, $normQCarga) !== false) {
                return true;
            }
        }
        return false;
    }));
}

// Enriquece las fichas con relaciones de programa, responsables, agendas,
// sesiones, actividades y documentos antes de construir las tarjetas.
$fichasRelacionadas = $mcmas->getFichasRelacionadas(array_column($fichasVistaCarga, 'idfic'));
$fichaRelacionadaPorId = [];
foreach ($fichasRelacionadas as $fichaRelacionada) {
    $fichaRelacionadaPorId[(string)($fichaRelacionada['idfic'] ?? '')] = $fichaRelacionada;
}
$fichasVistaCarga = array_values(array_map(static function ($ficha) use ($fichaRelacionadaPorId) {
    $idfic = (string)($ficha['idfic'] ?? '');
    return array_replace($ficha, $fichaRelacionadaPorId[$idfic] ?? []);
}, $fichasVistaCarga));

$programasFichas = $mcmas->agruparFichasPorPrograma($fichasVistaCarga);
$conteosFicha = [];
$instructoresFicha = [];
$docsPorFicha = [];
foreach ($fichasVistaCarga as $fichaRelacionada) {
    $idfic = (string)($fichaRelacionada['idfic'] ?? '');
    if ($idfic === '') {
        continue;
    }
    $conteosFicha[$idfic] = $fichaRelacionada['conteos'] ?? ['agendas' => 0, 'sesiones' => 0, 'actividades' => 0];
    $instructoresFicha[$idfic] = implode(', ', array_column($fichaRelacionada['instructores'] ?? [], 'nombre'));
    $docsPorFicha[$idfic] = $fichaRelacionada['documentos'] ?? [];
}

// Fichas compañeras: otras fichas permitidas a cargo del mismo instructor.
// Sirve para mostrar "también está a cargo de" dentro de cada ficha.
$idsInstrPorFicha = $mcmas->getIdsInstructoresPorFicha(array_column($fichasPermitidas, 'idfic'));
$fichasPorInstr = [];
foreach ($idsInstrPorFicha as $idficInstr => $idusInstr) {
    foreach ($idusInstr as $idusuInstr) {
        $fichasPorInstr[$idusuInstr][] = (string)$idficInstr;
    }
}
$etiquetasFicha = [];
foreach ($fichasPermitidas as $fp) {
    $etiquetasFicha[(string)($fp['idfic'] ?? '')] = $fp;
}
$fichasColegas = [];
foreach ($idsInstrPorFicha as $idficBase => $idusBase) {
    $colegas = [];
    foreach ($idusBase as $idusuBase) {
        foreach ($fichasPorInstr[$idusuBase] ?? [] as $otraFicha) {
            if ($otraFicha !== (string)$idficBase && isset($etiquetasFicha[$otraFicha]) && !isset($colegas[$otraFicha])) {
                $colegas[$otraFicha] = $etiquetasFicha[$otraFicha];
            }
        }
    }
    $fichasColegas[$idficBase] = array_slice(array_values($colegas), 0, 8);
}

// Los documentos ya fueron relacionados en getFichasRelacionadas().

// Para cada agenda, otras fichas permitidas con el mismo contenido.
$fichasPorAgenda = $mcmas->getFichasPorAgenda(array_column($fichasPermitidas, 'idfic'));

// Jornadas con agenda por ficha (Mañana/Tarde/Noche...), en orden canónico.
$ordenJornadas = ['Mañana', 'Tarde', 'Noche', 'Fin de semana', 'Virtual'];
$jorAgendasPorFicha = [];
foreach ($docsPorFicha as $idficDocs => $docsFicha) {
    $vistas = [];
    foreach ($docsFicha as $doc) {
        $jorNorm = Mcmas::normalizarJornada($doc['hor'] ?? '');
        if ($jorNorm !== null) {
            $vistas[$jorNorm] = true;
        }
    }
    $jorAgendasPorFicha[$idficDocs] = array_values(array_filter($ordenJornadas, function ($j) use ($vistas) {
        return isset($vistas[$j]);
    }));
}

// Carga por instructor: clasifica responsables y permite filtrar planta/contratista.
$datosInstructores = $mcmas->agruparFichasPorInstructor($fichasVistaCarga, $idsInstrPorFicha);
$gruposTodos = $datosInstructores['instructores'];
$fichasPendientes = $datosInstructores['pendientes'];
$gruposPorTipo = [
    'planta' => [],
    'contratista' => [],
    'sin_clasificar' => [],
    'sin_asignar' => [],
];
$conteoTipos = array_fill_keys(array_keys($gruposPorTipo), 0);

foreach ($gruposTodos as &$grupoInstructor) {
    $tipoGrupo = $grupoInstructor['tipo'] ?? 'sin_clasificar';
    if (!isset($gruposPorTipo[$tipoGrupo])) {
        $tipoGrupo = 'sin_clasificar';
    }
    usort($grupoInstructor['fichas'], static function ($a, $b) {
        $nomA = mb_strtolower(trim((string)($a['nomfic'] ?? '')));
        $nomB = mb_strtolower(trim((string)($b['nomfic'] ?? '')));
        return $nomA === $nomB
            ? strcmp((string)($a['idfic'] ?? ''), (string)($b['idfic'] ?? ''))
            : strcmp($nomA, $nomB);
    });
    $grupoInstructor['resumen'] = ['fichas' => 0, 'agendas' => 0, 'sesiones' => 0, 'actividades' => 0, 'aprendices' => 0];
    $jornadasGrupo = [];
    foreach ($grupoInstructor['fichas'] as $fichaGrupo) {
        $idficGrupo = (string)($fichaGrupo['idfic'] ?? '');
        $conteoGrupo = $conteosFicha[$idficGrupo] ?? ['agendas' => 0, 'sesiones' => 0, 'actividades' => 0];
        $grupoInstructor['resumen']['fichas']++;
        $grupoInstructor['resumen']['agendas'] += (int)($conteoGrupo['agendas'] ?? 0);
        $grupoInstructor['resumen']['sesiones'] += (int)($conteoGrupo['sesiones'] ?? 0);
        $grupoInstructor['resumen']['actividades'] += (int)($conteoGrupo['actividades'] ?? 0);
        $grupoInstructor['resumen']['aprendices'] += (int)($fichaGrupo['total_aprendices'] ?? 0);
        $jornadaGrupo = Mcmas::nombreJornada($fichaGrupo['jornada'] ?? 0);
        $jornadasGrupo[$jornadaGrupo] = true;
    }
    $grupoInstructor['jornadas'] = array_keys($jornadasGrupo);
    $gruposPorTipo[$tipoGrupo][] = $grupoInstructor;
    $conteoTipos[$tipoGrupo]++;
}
unset($grupoInstructor);

if (!empty($fichasPendientes)) {
    $gruposPorTipo['sin_asignar'][] = [
        'idusu' => 0,
        'nombre' => 'Sin instructor asignado',
        'tipo' => 'sin_asignar',
        'etiqueta_tipo' => 'Pendiente de asignación',
        'fichas' => $fichasPendientes,
        'resumen' => ['fichas' => count($fichasPendientes), 'agendas' => 0, 'sesiones' => 0, 'actividades' => 0, 'aprendices' => 0],
        'jornadas' => [],
    ];
    $conteoTipos['sin_asignar'] = 1;
}

$seccionesMeta = [
    'planta' => [
        'titulo' => 'Instructores de planta',
        'descripcion' => 'Responsables con vínculo permanente o perfil de instructor.',
        'icono' => 'fa-user-tie',
        'clase' => 'carga-seccion-planta',
    ],
    'contratista' => [
        'titulo' => 'Instructores contratistas',
        'descripcion' => 'Responsables con perfil de contratista.',
        'icono' => 'fa-id-badge',
        'clase' => 'carga-seccion-contratista',
    ],
    'sin_clasificar' => [
        'titulo' => 'Otros responsables',
        'descripcion' => 'Registros que requieren validar su clasificación.',
        'icono' => 'fa-user-slash',
        'clase' => 'carga-seccion-otros',
    ],
    'sin_asignar' => [
        'titulo' => 'Sin instructor asignado',
        'descripcion' => 'Fichas pendientes por asignar un responsable.',
        'icono' => 'fa-triangle-exclamation',
        'clase' => 'carga-seccion-pendiente',
    ],
];
$seccionesInstructores = [];
foreach ($seccionesMeta as $clave => $meta) {
    if ($tipoCarga !== 'todos' && $tipoCarga !== $clave) {
        continue;
    }
    if (empty($gruposPorTipo[$clave])) {
        continue;
    }
    $seccionesInstructores[] = array_merge($meta, [
        'clave' => $clave,
        'grupos' => $gruposPorTipo[$clave],
        'total_instructores' => count($gruposPorTipo[$clave]),
    ]);
}

$idsFichasVisibles = [];
foreach ($seccionesInstructores as $seccion) {
    foreach ($seccion['grupos'] as $grupo) {
        foreach ($grupo['fichas'] as $ficha) {
            $idficVisible = (string)($ficha['idfic'] ?? '');
            if ($idficVisible !== '') {
                $idsFichasVisibles[$idficVisible] = true;
            }
        }
    }
}
$idsFichasVisibles = array_keys($idsFichasVisibles);
sort($idsFichasVisibles, SORT_NATURAL);
$programasVisibles = [];
foreach ($idsFichasVisibles as $idficVisible) {
    $codproVisible = trim((string)($etiquetasFicha[$idficVisible]['codpro'] ?? ''));
    if ($codproVisible !== '') {
        $programasVisibles[$codproVisible] = true;
    }
}

$fichasSinInstructor = ($tipoCarga === 'todos' || $tipoCarga === 'sin_asignar') ? count($fichasPendientes) : 0;
$sesionesSinInstructor = $mcmas->getSesionesSinAsignar($idsFichasVisibles);
$resumenGeneral = [
    'programas' => count($programasVisibles),
    'fichas' => count($idsFichasVisibles),
    'fichas_sin_instructor' => $fichasSinInstructor,
    'sesiones_sin_instructor' => $sesionesSinInstructor,
    'pendientes' => $fichasSinInstructor,
    'sesiones_sin' => $sesionesSinInstructor,
    'agendas' => 0,
    'sesiones' => 0,
    'actividades' => 0,
];
foreach ($idsFichasVisibles as $idficVisible) {
    $conteoVisible = $conteosFicha[$idficVisible] ?? ['agendas' => 0, 'sesiones' => 0, 'actividades' => 0];
    $resumenGeneral['agendas'] += (int)($conteoVisible['agendas'] ?? 0);
    $resumenGeneral['sesiones'] += (int)($conteoVisible['sesiones'] ?? 0);
    $resumenGeneral['actividades'] += (int)($conteoVisible['actividades'] ?? 0);
}

$baseFiltrosCarga = ['pg' => (int)$pg];
if ($qCarga !== '') {
    $baseFiltrosCarga['q'] = $qCarga;
}
if ($tipoCarga !== 'todos') {
    $baseFiltrosCarga['tipo'] = $tipoCarga;
}
if (!empty($verTodasCarga)) {
    $baseFiltrosCarga['ver'] = 'todas';
}
$paramsVigentesCarga = $baseFiltrosCarga;
unset($paramsVigentesCarga['ver']);
$urlVigenteCarga = 'home.php?' . http_build_query($paramsVigentesCarga);
$paramsTodasCarga = $baseFiltrosCarga;
$paramsTodasCarga['ver'] = 'todas';
$urlTodasCarga = 'home.php?' . http_build_query($paramsTodasCarga);
$urlLimpiarCarga = 'home.php?pg=' . (int)$pg;
$urlPorTipoCarga = [];
foreach ($tiposCargaValidos as $tipoUrl) {
    $paramsTipo = $baseFiltrosCarga;
    if ($tipoUrl === 'todos') {
        unset($paramsTipo['tipo']);
    } else {
        $paramsTipo['tipo'] = $tipoUrl;
    }
    $urlPorTipoCarga[$tipoUrl] = 'home.php?' . http_build_query($paramsTipo);
}

// Paginado de tarjetas de responsables: las secciones conservan su orden y
// los filtrosvigencia/tipo/texto se mantienen al cambiar de página.
$porPaginaCarga = 8;
$totalResponsablesCarga = 0;
foreach ($seccionesInstructores as $seccion) {
    $totalResponsablesCarga += (int)$seccion['total_instructores'];
}
$totalPaginasCarga = max(1, (int)ceil($totalResponsablesCarga / $porPaginaCarga));
$paginaCarga = max(1, (int)($_GET['pagina'] ?? 1));
$paginaCarga = min($paginaCarga, $totalPaginasCarga);
$offsetCarga = ($paginaCarga - 1) * $porPaginaCarga;
$seccionesPaginadasCarga = [];
$restantesOffset = $offsetCarga;
$tarjetasEnPagina = 0;
foreach ($seccionesInstructores as $seccion) {
    if ($tarjetasEnPagina >= $porPaginaCarga) {
        break;
    }
    $cantidadSeccion = count($seccion['grupos']);
    $saltarSeccion = min($restantesOffset, $cantidadSeccion);
    $restantesOffset -= $saltarSeccion;
    $tomarSeccion = min($cantidadSeccion - $saltarSeccion, $porPaginaCarga - $tarjetasEnPagina);
    if ($tomarSeccion > 0) {
        $seccionPaginada = $seccion;
        $seccionPaginada['grupos'] = array_slice($seccion['grupos'], $saltarSeccion, $tomarSeccion);
        $seccionesPaginadasCarga[] = $seccionPaginada;
        $tarjetasEnPagina += $tomarSeccion;
    }
}
$inicioCarga = $totalResponsablesCarga > 0 ? $offsetCarga + 1 : 0;
$finCarga = min($offsetCarga + $porPaginaCarga, $totalResponsablesCarga);
$urlPaginaCarga = [];
for ($numeroPagina = 1; $numeroPagina <= $totalPaginasCarga; $numeroPagina++) {
    $paramsPagina = $baseFiltrosCarga;
    $paramsPagina['pagina'] = $numeroPagina;
    $urlPaginaCarga[$numeroPagina] = 'home.php?' . http_build_query($paramsPagina);
}
?>

