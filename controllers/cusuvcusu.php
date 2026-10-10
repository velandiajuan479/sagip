<?php
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    session_start();
}

// Asegurar directorio raíz para inclusiones uniformes
chdir(__DIR__ . '/..');

require_once 'models/conexion.php';
require_once 'models/mfic.php';
require_once 'models/musuvusu.php';
require_once 'vendor/autoload.php';

if (!class_exists('MyCLabs\Enum\Enum')) {
    $enumFile = __DIR__ . '/../vendor/myclabs/php-enum/src/Enum.php';
    if (file_exists($enumFile)) {
        require_once $enumFile;
    }
}

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

$pag = isset($pg) ? $pg : 1219;
$idcenSession = $_SESSION["idcen"] ?? 951310;

$mfic = new Mfic();
$musu = new Musu();

// =========================================================================
// MEJORA 1.2: DESCARGA DIRECTA DE LA PLANTILLA OFICIAL DE CARGA MASIVA
// =========================================================================
if (isset($_GET['action']) && $_GET['action'] === 'descargar_plantilla') {
    while (ob_get_level()) {
        ob_end_clean();
    }

    $spreadsheet = new Spreadsheet();

    // -------------------------------------------------------------
    // HOJA 1: PLANTILLA DE CARGA
    // -------------------------------------------------------------
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Plantilla_Carga_Masiva');

    // Títulos y banner superior
    $sheet->setCellValue('A1', 'SERVICIO NACIONAL DE APRENDIZAJE - SENA');
    $sheet->setCellValue('A2', 'SISTEMA INTEGRADO DE GESTIÓN Y ADMINISTRACIÓN (SAGI)');
    $sheet->setCellValue('A3', 'PLANTILLA OFICIAL DE CARGA MASIVA DE USUARIOS Y FICHAS');
    $sheet->setCellValue('A4', 'Instrucciones: Inicie el registro de datos desde la fila 7. Puede asociar aprendices a múltiples fichas diferentes en este mismo archivo.');

    $sheet->mergeCells('A1:H1');
    $sheet->mergeCells('A2:H2');
    $sheet->mergeCells('A3:H3');
    $sheet->mergeCells('A4:H4');

    $sheet->getStyle('A1:A3')->getFont()->setBold(true)->setName('Segoe UI');
    $sheet->getStyle('A1')->getFont()->setSize(14)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('00843D'));
    $sheet->getStyle('A2')->getFont()->setSize(12)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('333333'));
    $sheet->getStyle('A3')->getFont()->setSize(11)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('117F09'));
    $sheet->getStyle('A4')->getFont()->setItalic(true)->setSize(9)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('555555'));
    $sheet->getStyle('A1:H4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    // Cabeceras de columna (Fila 6)
    $headers = [
        'A' => 'Tipo y Documento (*)',
        'B' => 'Nombre Completo (*)',
        'C' => 'Teléfono',
        'D' => 'Código Ficha (*)',
        'E' => 'Nombre del Programa / Ficha (*)',
        'F' => 'Correo Electrónico',
        'G' => 'Jornada (*)',
        'H' => 'Municipio'
    ];

    foreach ($headers as $col => $title) {
        $sheet->setCellValue($col . '6', $title);
    }

    $headerStyle = [
        'font' => [
            'bold' => true,
            'color' => ['rgb' => 'FFFFFF'],
            'size' => 10,
            'name' => 'Segoe UI'
        ],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => '00843D']
        ],
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
            'vertical' => Alignment::VERTICAL_CENTER,
            'wrapText' => true
        ],
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['rgb' => 'FFFFFF']
            ]
        ]
    ];
    $sheet->getStyle('A6:H6')->applyFromArray($headerStyle);
    $sheet->getRowDimension(6)->setRowHeight(30);

    // Filas de ejemplo con diferentes fichas para demostrar carga multificha
    $sampleData = [
        [
            'doc' => 'CC - 1001234567',
            'nom' => 'JUAN CARLOS PÉREZ GÓMEZ',
            'tel' => '3101112222',
            'fic' => '2825101',
            'nomfic' => 'ANÁLISIS Y DESARROLLO DE SOFTWARE',
            'ema' => 'jperez@soysena.edu.co',
            'jor' => 'Mañana',
            'mun' => '25175'
        ],
        [
            'doc' => 'TI - 1002345678',
            'nom' => 'MARÍA ALEJANDRA RODRÍGUEZ LÓPEZ',
            'tel' => '3112223333',
            'fic' => '2825101',
            'nomfic' => 'ANÁLISIS Y DESARROLLO DE SOFTWARE',
            'ema' => 'mrodriguez@soysena.edu.co',
            'jor' => 'Mañana',
            'mun' => '25175'
        ],
        [
            'doc' => 'CC - 1003456789',
            'nom' => 'CARLOS ALBERTO MARTÍNEZ SÁNCHEZ',
            'tel' => '3123334444',
            'fic' => '2825102',
            'nomfic' => 'GESTIÓN CONTABLE Y FINANCIERA',
            'ema' => 'cmartinez@soysena.edu.co',
            'jor' => 'Tarde',
            'mun' => '25899'
        ],
        [
            'doc' => 'CC - 1004567890',
            'nom' => 'ANA SOFÍA TORRES RAMÍREZ',
            'tel' => '3134445555',
            'fic' => '2825103',
            'nomfic' => 'REDES DE COMUNICACIÓN',
            'ema' => 'atorres@soysena.edu.co',
            'jor' => 'Noche',
            'mun' => '25175'
        ]
    ];

    $rowIdx = 7;
    foreach ($sampleData as $item) {
        $sheet->setCellValueExplicit('A' . $rowIdx, $item['doc'], DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('B' . $rowIdx, $item['nom'], DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('C' . $rowIdx, $item['tel'], DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('D' . $rowIdx, $item['fic'], DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('E' . $rowIdx, $item['nomfic'], DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('F' . $rowIdx, $item['ema'], DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('G' . $rowIdx, $item['jor'], DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('H' . $rowIdx, $item['mun'], DataType::TYPE_STRING);

        $sheet->getStyle("A{$rowIdx}:H{$rowIdx}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('E0E0E0');
        $sheet->getRowDimension($rowIdx)->setRowHeight(22);
        $rowIdx++;
    }

    // Autoajuste de ancho de columnas
    foreach (range('A', 'H') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    // -------------------------------------------------------------
    // HOJA 2: FICHAS DISPONIBLES EN EL SISTEMA (Hoja de Referencia)
    // -------------------------------------------------------------
    $sheetFic = $spreadsheet->createSheet();
    $sheetFic->setTitle('Fichas_Disponibles');

    $sheetFic->setCellValue('A1', 'Código Ficha');
    $sheetFic->setCellValue('B1', 'Nombre Programa / Ficha');
    $sheetFic->setCellValue('C1', 'Jornada');
    $sheetFic->setCellValue('D1', 'Municipio');

    $sheetFic->getStyle('A1:D1')->applyFromArray([
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '117F09']],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
    ]);

    $fichasExistentes = $musu->getFichasDisponibles($idcenSession);
    $rf = 2;
    if (!empty($fichasExistentes)) {
        foreach ($fichasExistentes as $f) {
            $sheetFic->setCellValueExplicit('A' . $rf, $f['idfic'], DataType::TYPE_STRING);
            $sheetFic->setCellValue('B' . $rf, $f['nomfic']);
            $sheetFic->setCellValue('C' . $rf, $f['jornada'] ?? 'Mañana');
            $sheetFic->setCellValue('D' . $rf, $f['mun'] ?? '');
            $rf++;
        }
    }
    foreach (range('A', 'D') as $col) {
        $sheetFic->getColumnDimension($col)->setAutoSize(true);
    }

    // -------------------------------------------------------------
    // HOJA 3: JORNADAS VÁLIDAS (Referencia)
    // -------------------------------------------------------------
    $sheetJor = $spreadsheet->createSheet();
    $sheetJor->setTitle('Jornadas_Validas');
    $sheetJor->setCellValue('A1', 'Jornada Aceptada');
    $sheetJor->setCellValue('B1', 'Código Interno');
    $sheetJor->setCellValue('C1', 'Descripción');

    $sheetJor->getStyle('A1:C1')->applyFromArray([
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '00843D']]
    ]);

    $jornadas = [
        ['Mañana', '1', 'Jornada Diurna Matutina'],
        ['Tarde', '2', 'Jornada Diurna Vespertina'],
        ['Noche', '3', 'Jornada Nocturna'],
        ['Fin de Semana', '4', 'Jornada Sábados y Domingos'],
        ['Virtual', '5', 'Formación 100% Virtual']
    ];
    $rj = 2;
    foreach ($jornadas as $j) {
        $sheetJor->setCellValue('A' . $rj, $j[0]);
        $sheetJor->setCellValue('B' . $rj, $j[1]);
        $sheetJor->setCellValue('C' . $rj, $j[2]);
        $rj++;
    }
    foreach (range('A', 'C') as $col) {
        $sheetJor->getColumnDimension($col)->setAutoSize(true);
    }

    // Volver a la primera hoja activa
    $spreadsheet->setActiveSheetIndex(0);

    // Forzar descarga de la plantilla Excel oficial (Xlsx o Xls según entorno)
    if (class_exists('ZipArchive')) {
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="Plantilla_Carga_Masiva_Usuarios_Fichas.xlsx"');
        header('Cache-Control: max-age=0');
        header('Pragma: public');
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
    } else {
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="Plantilla_Carga_Masiva_Usuarios_Fichas.xls"');
        header('Cache-Control: max-age=0');
        header('Pragma: public');
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xls($spreadsheet);
        $writer->save('php://output');
    }
    exit;
}

// =========================================================================
// MEJORA 1.1: PROCESAMIENTO Y VALIDACIÓN ESTRUCTURADA DE ERRORES
// =========================================================================
$resultadoCarga = [
    'procesado' => false,
    'total_filas' => 0,
    'exitosos' => 0,
    'errores' => 0,
    'omitidos' => 0,
    'usuarios_creados' => 0,
    'usuarios_actualizados' => 0,
    'fichas_procesadas' => [],
    'fichas_nuevas' => [],
    'fichas_existentes' => [],
    'lista_errores' => [],
    'lista_exitos' => [],
    'mensaje' => '',
    'tipo' => 'info'
];

$inic = isset($_POST["inic"]) ? trim($_POST["inic"]) : '';
$finc = isset($_POST["finc"]) ? trim($_POST["finc"]) : '';
$fileUpload = $_FILES["arch"] ?? null;

if ($fileUpload && !empty($fileUpload['name'])) {
    $resultadoCarga['procesado'] = true;
    $nombreOriginal = $fileUpload['name'];
    $extension = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));

    if (!in_array($extension, ['xls', 'xlsx'])) {
        $resultadoCarga['errores']++;
        $resultadoCarga['lista_errores'][] = [
            'fila' => '-',
            'columna' => 'Archivo',
            'valor' => $nombreOriginal,
            'motivo' => "Extensión de archivo no permitida (.$extension). Solamente se admiten archivos Excel (.xlsx, .xls).",
            'solucion' => 'Guarde su archivo en formato Libro de Excel (.xlsx) y vuelva a intentarlo.'
        ];
    } elseif ($fileUpload['error'] !== UPLOAD_ERR_OK) {
        $resultadoCarga['errores']++;
        $resultadoCarga['lista_errores'][] = [
            'fila' => '-',
            'columna' => 'Archivo',
            'valor' => $nombreOriginal,
            'motivo' => 'Error durante la transferencia del archivo (código de error: ' . $fileUpload['error'] . ').',
            'solucion' => 'Verifique el tamaño del archivo o su conexión e inténtelo nuevamente.'
        ];
    } else {
        try {
            $tmpPath = $fileUpload['tmp_name'];
            $inputFileType = IOFactory::identify($tmpPath);
            $objReader = IOFactory::createReader($inputFileType);
            $objReader->setReadDataOnly(true);
            $objPHPExcel = $objReader->load($tmpPath);
            $sheet = $objPHPExcel->getSheet(0);

            $highestRow = $sheet->getHighestRow();
            $highestColumn = $sheet->getHighestColumn();

            // Determinar la fila de inicio: la plantilla estándar tiene datos desde fila 7
            $startRow = 7;
            // Si la fila 7 está vacía pero la fila 2 tiene datos (plantilla simple), detectar
            $valTest7 = trim((string)$sheet->getCell("A7")->getValue());
            $valTest2 = trim((string)$sheet->getCell("A2")->getValue());
            if (empty($valTest7) && !empty($valTest2) && !preg_match('/sena/i', $valTest2)) {
                $startRow = 2;
            }

            // Memoria caché para fichas vistas durante la lectura del lote actual
            $fichasCache = [];

            for ($row = $startRow; $row <= $highestRow; $row++) {
                // Obtener valores crudos de la fila
                $rawDoc = trim((string)$sheet->getCell("A" . $row)->getValue());
                $rawNom = trim((string)$sheet->getCell("B" . $row)->getValue());
                $rawTel = trim((string)$sheet->getCell("C" . $row)->getValue());
                $rawFic = trim((string)$sheet->getCell("D" . $row)->getValue());
                $rawNomFic = trim((string)$sheet->getCell("E" . $row)->getValue());
                $rawEma = trim((string)$sheet->getCell("F" . $row)->getValue());
                $rawJor = trim((string)$sheet->getCell("G" . $row)->getValue());
                $rawMun = trim((string)$sheet->getCell("H" . $row)->getValue());

                // Detectar si la fila está completamente en blanco
                if ($rawDoc === '' && $rawNom === '' && $rawFic === '' && $rawNomFic === '') {
                    $resultadoCarga['omitidos']++;
                    continue;
                }

                $resultadoCarga['total_filas']++;
                $erroresFila = [];

                // -------------------------------------------------------------
                // Validación 1: Columna A (Documento de Identificación)
                // -------------------------------------------------------------
                $ndocusu = '';
                if ($rawDoc === '') {
                    $erroresFila[] = [
                        'fila' => $row,
                        'columna' => 'Columna A (Documento)',
                        'valor' => '[Vacío]',
                        'motivo' => 'El número de identificación del usuario es obligatorio y no puede estar vacío.',
                        'solucion' => 'Diligencie el tipo y número de documento (ej: CC - 1001234567 o 1001234567).'
                    ];
                } else {
                    // Si viene en formato "TIPO - NUMERO", extraer el número
                    if (strpos($rawDoc, "-") !== false) {
                        $partesDoc = explode("-", $rawDoc);
                        $ndocusu = trim(end($partesDoc));
                    } else {
                        $ndocusu = $rawDoc;
                    }
                    // Limpiar puntos, comas o espacios
                    $ndocusu = preg_replace('/[^0-9]/', '', $ndocusu);

                    if (empty($ndocusu) || strlen($ndocusu) < 4) {
                        $erroresFila[] = [
                            'fila' => $row,
                            'columna' => 'Columna A (Documento)',
                            'valor' => $rawDoc,
                            'motivo' => "El documento contiene un valor no numérico o inválido ('$rawDoc'). Se requiere un número de al menos 4 dígitos.",
                            'solucion' => 'Verifique que la identificación contenga únicamente dígitos numéricos válidos.'
                        ];
                    }
                }

                // -------------------------------------------------------------
                // Validación 2: Columna B (Nombre Completo)
                // -------------------------------------------------------------
                $nomusu = $rawNom;
                if ($nomusu === '' || strlen($nomusu) < 3) {
                    $erroresFila[] = [
                        'fila' => $row,
                        'columna' => 'Columna B (Nombre Completo)',
                        'valor' => $rawNom === '' ? '[Vacío]' : $rawNom,
                        'motivo' => 'El nombre del usuario/aprendiz es obligatorio y debe tener al menos 3 caracteres.',
                        'solucion' => 'Ingrese los nombres y apellidos completos del aprendiz.'
                    ];
                }

                // -------------------------------------------------------------
                // Validación 3: Columna D (Código de Ficha)
                // -------------------------------------------------------------
                $idfic = preg_replace('/\s+/', '', $rawFic);
                if ($idfic === '') {
                    $erroresFila[] = [
                        'fila' => $row,
                        'columna' => 'Columna D (Código Ficha)',
                        'valor' => '[Vacío]',
                        'motivo' => 'El código de ficha es obligatorio para asociar al usuario a un programa de formación.',
                        'solucion' => 'Indique el código numérico de la ficha (ej: 2825101).'
                    ];
                } elseif (!is_numeric($idfic) || strlen($idfic) < 3) {
                    $erroresFila[] = [
                        'fila' => $row,
                        'columna' => 'Columna D (Código Ficha)',
                        'valor' => $rawFic,
                        'motivo' => "El código de ficha '$rawFic' debe ser un valor numérico válido de mínimo 3 dígitos.",
                        'solucion' => 'Consulte las fichas en la hoja de referencia y verifique el código.'
                    ];
                }

                // -------------------------------------------------------------
                // Validación 4 & Gestión Inteligente: Columna E (Nombre Programa / Ficha)
                // MEJORA 2: Soporte para 1 ficha nueva para 30 usuarios o múltiples fichas nuevas
                // -------------------------------------------------------------
                $nomfic = trim($rawNomFic);

                // Si no se proporcionó nombre en esta fila, pero ya se vio el nombre de esta misma ficha en una fila previa del archivo
                if ($nomfic === '' && isset($fichasCache[$idfic]['nomfic']) && $fichasCache[$idfic]['nomfic'] !== '') {
                    $nomfic = $fichasCache[$idfic]['nomfic'];
                }

                // Verificar si la ficha existe en BD o fue creada en este lote
                if (!isset($fichasCache[$idfic])) {
                    $existeEnBd = $idfic !== '' ? $mfic->existeFicha($idfic) : false;
                    $fichasCache[$idfic] = [
                        'existe_bd' => $existeEnBd,
                        'es_nueva' => !$existeEnBd,
                        'creada' => false,
                        'nomfic' => $nomfic
                    ];
                } else {
                    if ($nomfic !== '' && empty($fichasCache[$idfic]['nomfic'])) {
                        $fichasCache[$idfic]['nomfic'] = $nomfic;
                    }
                }

                $fichaExiste = $fichasCache[$idfic]['existe_bd'];

                // Si la ficha no existe previamente y no viene nombre del programa, asignar nombre descriptivo por defecto
                if (!$fichaExiste && empty($nomfic)) {
                    $nomfic = "PROGRAMA DE FORMACIÓN " . $idfic;
                    $fichasCache[$idfic]['nomfic'] = $nomfic;
                }

                // -------------------------------------------------------------
                // Validación 5: Columna G (Jornada)
                // -------------------------------------------------------------
                $jornadaTexto = mb_strtolower($rawJor, 'UTF-8');
                $jornada = '1';

                if (empty($rawJor)) {
                    $jornada = '1'; // Default: Mañana
                } elseif (strpos($jornadaTexto, 'mañ') !== false || strpos($jornadaTexto, 'man') !== false || $jornadaTexto === '1') {
                    $jornada = '1';
                } elseif (strpos($jornadaTexto, 'tar') !== false || $jornadaTexto === '2') {
                    $jornada = '2';
                } elseif (strpos($jornadaTexto, 'noc') !== false || $jornadaTexto === '3') {
                    $jornada = '3';
                } elseif (strpos($jornadaTexto, 'fin') !== false || strpos($jornadaTexto, 'sem') !== false || $jornadaTexto === '4') {
                    $jornada = '4';
                } elseif (strpos($jornadaTexto, 'vir') !== false || $jornadaTexto === '5') {
                    $jornada = '5';
                } else {
                    $erroresFila[] = [
                        'fila' => $row,
                        'columna' => 'Columna G (Jornada)',
                        'valor' => $rawJor,
                        'motivo' => "Jornada '$rawJor' no reconocida. Solo se admiten: Mañana, Tarde, Noche, Fin de Semana o Virtual.",
                        'solucion' => 'Seleccione una jornada válida de las especificadas en la hoja Jornadas_Validas.'
                    ];
                }

                // -------------------------------------------------------------
                // Si la fila tiene errores: registrar y pasar a la siguiente
                // -------------------------------------------------------------
                if (!empty($erroresFila)) {
                    $resultadoCarga['errores']++;
                    foreach ($erroresFila as $err) {
                        $resultadoCarga['lista_errores'][] = $err;
                    }
                    continue;
                }

                // -------------------------------------------------------------
                // PROCESAMIENTO SEGURO DE LA FILA VÁLIDA
                // -------------------------------------------------------------
                $telcan = !empty($rawTel) ? preg_replace('/[^0-9]/', '', $rawTel) : '3101112222';
                $emausu = !empty($rawEma) && filter_var($rawEma, FILTER_VALIDATE_EMAIL) ? $rawEma : ($ndocusu . '@soysena.edu.co');
                $mun = !empty($rawMun) ? $rawMun : '25175';

                // 1. Gestión de la Ficha (creación automática en BD si es nueva)
                if (!$fichaExiste) {
                    if (!$fichasCache[$idfic]['creada']) {
                        $mfic->crearFichaRapida($idfic, $nomfic, $jornada, $idcenSession, $mun);
                        $fichasCache[$idfic]['creada'] = true;
                        $fichasCache[$idfic]['existe_bd'] = true;
                    }
                }

                // Registrar estadísticas y feedback de la ficha
                if (!isset($resultadoCarga['fichas_procesadas'][$idfic])) {
                    $esNueva = $fichasCache[$idfic]['es_nueva'];
                    $resultadoCarga['fichas_procesadas'][$idfic] = [
                        'idfic' => $idfic,
                        'nomfic' => $nomfic ?: "Ficha $idfic",
                        'es_nueva' => $esNueva,
                        'jornada_texto' => $rawJor ?: 'Mañana',
                        'total_aprendices' => 0
                    ];
                    if ($esNueva) {
                        $resultadoCarga['fichas_nuevas'][$idfic] = [
                            'idfic' => $idfic,
                            'nomfic' => $nomfic ?: "Ficha $idfic",
                            'jornada' => $rawJor ?: 'Mañana'
                        ];
                    } else {
                        $resultadoCarga['fichas_existentes'][$idfic] = [
                            'idfic' => $idfic,
                            'nomfic' => $nomfic ?: "Ficha $idfic",
                            'jornada' => $rawJor ?: 'Mañana'
                        ];
                    }
                }
                $resultadoCarga['fichas_procesadas'][$idfic]['total_aprendices']++;

                // 2. Gestión del Usuario (Aprendiz)
                $tdousu = 1801; // CC por defecto
                if (stripos($rawDoc, 'TI') === 0 || stripos($rawDoc, 'TI -') !== false) {
                    $tdousu = 1802; // Tarjeta de Identidad
                } elseif (stripos($rawDoc, 'CE') === 0 || stripos($rawDoc, 'CE -') !== false) {
                    $tdousu = 1803; // Cédula de Extranjería
                } elseif (stripos($rawDoc, 'PEP') !== false) {
                    $tdousu = 1804; // Permiso Especial de Permanencia
                }

                $musu->setIdper(4); // Perfil 4 = Aprendiz
                $musu->setNdocusu($ndocusu);
                $musu->setNomusu(mb_strtoupper($nomusu, 'UTF-8'));
                $musu->setPasusu($inic . $ndocusu . $finc);
                $musu->setActusu(1);
                $musu->setIdcen($idcenSession);
                $musu->setEmausu($emausu);
                $musu->setTelcan($telcan);
                $musu->setNoca('');
                $musu->setFotcan('img/user.jpg');
                $musu->setTdousu($tdousu);
                $musu->setRhusu(2007);
                $musu->setGenusu(2009);

                $existingData = $musu->selectUsu();
                if (!empty($existingData) && ($existingData[0]['sum'] ?? 0) > 0) {
                    $idusu = (int)$existingData[0]['idusu'];
                    $musu->setIdusu($idusu);
                    $musu->edi();
                    $resultadoCarga['usuarios_actualizados']++;
                } else {
                    $idusu = $musu->save(3);
                    $resultadoCarga['usuarios_creados']++;
                    if ($idusu <= 0) {
                        $freshData = $musu->selectUsu();
                        $idusu = (int)($freshData[0]['idusu'] ?? 0);
                    }
                }

                // 3. Asignación de Permisos Predeterminados para Aprendiz en usupef
                if ($idusu > 0) {
                    $musu->asignarPerfilesPredeterminados($idusu, 4);
                }

                // 4. Asociación en usufic (Múltiples Fichas)
                if ($idusu > 0 && $idfic !== '') {
                    $musu->setIdusu($idusu);
                    $dtuxf = $musu->getFicUsuSec($idusu, $idfic);
                    if (!$dtuxf) {
                        $musu->insUxF($idusu, $idfic);
                    }
                }

                $resultadoCarga['exitosos']++;
                $resultadoCarga['lista_exitos'][] = [
                    'fila' => $row,
                    'documento' => $ndocusu,
                    'nombre' => $nomusu,
                    'ficha' => $idfic
                ];
            }

            $cantFichasNuevas = count($resultadoCarga['fichas_nuevas']);
            $cantFichasExistentes = count($resultadoCarga['fichas_existentes']);
            $msgFichas = "en " . count($resultadoCarga['fichas_procesadas']) . " ficha(s) distinta(s)";
            if ($cantFichasNuevas > 0 && $cantFichasExistentes > 0) {
                $msgFichas .= " ({$cantFichasNuevas} nueva(s) creada(s) en BD y {$cantFichasExistentes} previamente existente(s))";
            } elseif ($cantFichasNuevas > 0) {
                $msgFichas .= " ({$cantFichasNuevas} nueva(s) creada(s) exitosamente en BD)";
            } elseif ($cantFichasExistentes > 0) {
                $msgFichas .= " ({$cantFichasExistentes} ficha(s) existente(s) en el sistema)";
            }

            if ($resultadoCarga['errores'] > 0 && $resultadoCarga['exitosos'] > 0) {
                $resultadoCarga['mensaje'] = "Carga procesada parcialmente: {$resultadoCarga['exitosos']} aprendiz(ces) procesados ({$resultadoCarga['usuarios_creados']} nuevos, {$resultadoCarga['usuarios_actualizados']} actualizados) {$msgFichas}, con {$resultadoCarga['errores']} inconsistencia(s) detectadas.";
                $resultadoCarga['tipo'] = 'warning';
            } elseif ($resultadoCarga['errores'] > 0) {
                $resultadoCarga['mensaje'] = "Se encontraron {$resultadoCarga['errores']} inconsistencia(s) en el archivo. Revise el detalle a continuación para corregirlas.";
                $resultadoCarga['tipo'] = 'danger';
            } else {
                $resultadoCarga['mensaje'] = "¡Carga masiva completada con éxito! {$resultadoCarga['exitosos']} aprendiz(ces) procesados ({$resultadoCarga['usuarios_creados']} nuevos, {$resultadoCarga['usuarios_actualizados']} actualizados) {$msgFichas}.";
                $resultadoCarga['tipo'] = 'success';
            }

        } catch (Exception $e) {
            $resultadoCarga['errores']++;
            $resultadoCarga['lista_errores'][] = [
                'fila' => '-',
                'columna' => 'General',
                'valor' => '-',
                'motivo' => 'Excepción durante la lectura del archivo: ' . $e->getMessage(),
                'solucion' => 'Asegúrese de que el archivo no esté protegido con contraseña ni dañado.'
            ];
            $resultadoCarga['mensaje'] = "Error al procesar el archivo: " . $e->getMessage();
            $resultadoCarga['tipo'] = 'danger';
        }
    }
}

// =========================================================================
// DATOS EN TIEMPO REAL PARA EL DASHBOARD SUPERIOR
// =========================================================================
$dashboardStats = [
    'total_aprendices' => $musu->getCountAprendices($idcenSession),
    'total_fichas' => $mfic->getCountFichas($idcenSession),
    'total_asociaciones' => $musu->getCountTotalAsociaciones($idcenSession),
    'aprendices_activos' => $musu->getCountAprendicesActivos($idcenSession)
];
?>