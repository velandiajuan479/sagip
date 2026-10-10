<?php
$rootPath = file_exists('models/premcele.php') ? '' : '../';
require_once $rootPath . 'models/premcele.php';

$pg = 1504;
$mele = new Mele();

$ope = isset($_REQUEST['ope']) ? $_REQUEST['ope'] : NULL;

// Helper para extraer archivos de un archivo XLSX (ZIP) sin depender de la clase ZipArchive
if (!function_exists('extraerXlsxSinZipArchive')) {
function extraerXlsxSinZipArchive($content) {
    $files = [];
    $length = strlen($content);

    // Estrategia 1: End of Central Directory (EOCD: PK\x05\x06)
    $eocdPos = strrpos($content, "PK\x05\x06");
    if ($eocdPos !== false && $eocdPos + 22 <= $length) {
        $cdOffset = unpack('V', substr($content, $eocdPos + 16, 4))[1];
        $cdEntries = unpack('v', substr($content, $eocdPos + 8, 2))[1];
        $pos = $cdOffset;

        for ($i = 0; $i < $cdEntries && $pos + 46 <= $length; $i++) {
            if (substr($content, $pos, 4) !== "PK\x01\x02") break;
            $compMethod = unpack('v', substr($content, $pos + 10, 2))[1];
            $compSize = unpack('V', substr($content, $pos + 20, 4))[1];
            $fnLen = unpack('v', substr($content, $pos + 28, 2))[1];
            $extraLen = unpack('v', substr($content, $pos + 30, 2))[1];
            $commentLen = unpack('v', substr($content, $pos + 32, 2))[1];
            $localHeaderOffset = unpack('V', substr($content, $pos + 42, 4))[1];
            $fn = substr($content, $pos + 46, $fnLen);

            if ($localHeaderOffset + 30 <= $length && substr($content, $localHeaderOffset, 4) === "PK\x03\x04") {
                $localFnLen = unpack('v', substr($content, $localHeaderOffset + 26, 2))[1];
                $localExtraLen = unpack('v', substr($content, $localHeaderOffset + 28, 2))[1];
                $dataStart = $localHeaderOffset + 30 + $localFnLen + $localExtraLen;
                $compData = substr($content, $dataStart, $compSize);

                if ($compMethod === 0) {
                    $files[$fn] = $compData;
                } elseif ($compMethod === 8 && function_exists('gzinflate')) {
                    $uncomp = @gzinflate($compData);
                    if ($uncomp !== false) {
                        $files[$fn] = $uncomp;
                    }
                }
            }
            $pos += 46 + $fnLen + $extraLen + $commentLen;
        }
    }

    // Estrategia 2: Local File Headers (PK\x03\x04)
    if (empty($files['xl/worksheets/sheet1.xml'])) {
        $offset = 0;
        while ($offset + 30 <= $length) {
            if (substr($content, $offset, 4) !== "PK\x03\x04") break;
            $compMethod = unpack('v', substr($content, $offset + 8, 2))[1];
            $compSize = unpack('V', substr($content, $offset + 18, 4))[1];
            $fnLen = unpack('v', substr($content, $offset + 26, 2))[1];
            $extraLen = unpack('v', substr($content, $offset + 28, 2))[1];
            $fn = substr($content, $offset + 30, $fnLen);
            $dataStart = $offset + 30 + $fnLen + $extraLen;

            if ($compSize > 0 && $dataStart + $compSize <= $length) {
                $compData = substr($content, $dataStart, $compSize);
                if ($compMethod === 0) {
                    $files[$fn] = $compData;
                } elseif ($compMethod === 8 && function_exists('gzinflate')) {
                    $uncomp = @gzinflate($compData);
                    if ($uncomp !== false) {
                        $files[$fn] = $uncomp;
                    }
                }
                $offset = $dataStart + $compSize;
            } else {
                $offset = $dataStart;
            }
        }
    }

    if (empty($files)) {
        return null;
    }

    // Buscar sharedStrings.xml
    $sharedXmlContent = null;
    if (isset($files['xl/sharedStrings.xml'])) {
        $sharedXmlContent = $files['xl/sharedStrings.xml'];
    } else {
        foreach ($files as $fPath => $fData) {
            if (stripos($fPath, 'sharedStrings.xml') !== false) {
                $sharedXmlContent = $fData;
                break;
            }
        }
    }

    $shared = [];
    if (!empty($sharedXmlContent)) {
        $xmlShared = @simplexml_load_string($sharedXmlContent);
        if ($xmlShared) {
            foreach ($xmlShared->si as $si) {
                if (isset($si->t)) {
                    $shared[] = (string)$si->t;
                } elseif (isset($si->r)) {
                    $str = '';
                    foreach ($si->r as $r) {
                        $str .= (string)$r->t;
                    }
                    $shared[] = $str;
                } else {
                    $shared[] = '';
                }
            }
        }
    }

    // Buscar la primera hoja de trabajo en xl/worksheets/
    $sheetXmlContent = null;
    if (isset($files['xl/worksheets/sheet1.xml'])) {
        $sheetXmlContent = $files['xl/worksheets/sheet1.xml'];
    } else {
        foreach ($files as $fPath => $fData) {
            if (stripos($fPath, 'xl/worksheets/') !== false && stripos($fPath, '.xml') !== false) {
                $sheetXmlContent = $fData;
                break;
            }
        }
    }

    if (empty($sheetXmlContent)) {
        return null;
    }

    $xmlSheet = @simplexml_load_string($sheetXmlContent);
    if (!$xmlSheet || !isset($xmlSheet->sheetData)) {
        return null;
    }

    $rows = [];
    foreach ($xmlSheet->sheetData->row as $rNode) {
        $cells = [];
        foreach ($rNode->c as $cNode) {
            $ref = (string)$cNode['r'];
            $colLetters = preg_replace('/[0-9]/', '', $ref);
            $colIdx = 0;
            for ($l = 0; $l < strlen($colLetters); $l++) {
                $colIdx = $colIdx * 26 + (ord(strtoupper($colLetters[$l])) - ord('A') + 1);
            }
            $colIdx -= 1;

            $val = '';
            $type = (string)$cNode['t'];
            if ($type === 's') {
                $sIdx = (int)$cNode->v;
                $val = $shared[$sIdx] ?? '';
            } elseif ($type === 'inlineStr' && isset($cNode->is->t)) {
                $val = (string)$cNode->is->t;
            } else {
                $val = isset($cNode->v) ? (string)$cNode->v : '';
            }
            $cells[$colIdx] = $val;
        }

        if (!empty($cells)) {
            $maxCol = max(array_keys($cells));
            $rowArr = [];
            for ($c = 0; $c <= $maxCol; $c++) {
                $rowArr[$c] = $cells[$c] ?? '';
            }
            $rows[] = $rowArr;
        }
    }

    return $rows;
}
}

// Helper universal de lectura multiformato (.xls HTML, .csv, .xlsx, .xls BIFF8)
if (!function_exists('leerFilasDesdeArchivo')) {
function leerFilasDesdeArchivo($fileTmp, $fileExt, $rootPath) {
    $content = file_get_contents($fileTmp);
    $magic = substr($content, 0, 4);

    // 1. Archivo HTML (tabla exportada o plantilla descargada como .xls)
    if (stripos($content, '<table') !== false || stripos($content, '<html') !== false) {
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8" ?>' . $content);
        libxml_clear_errors();
        $trList = $dom->getElementsByTagName('tr');
        $filas = [];
        foreach ($trList as $tr) {
            $celdas = [];
            foreach ($tr->childNodes as $node) {
                if ($node->nodeName === 'td' || $node->nodeName === 'th') {
                    $celdas[] = trim($node->textContent);
                }
            }
            if (!empty($celdas)) {
                $filas[] = $celdas;
            }
        }
        if (!empty($filas)) {
            return $filas;
        }
    }

    // 2. Archivo ZIP / XLSX (inicia con PK\x03\x04)
    if ($magic === "PK\x03\x04") {
        if (class_exists('ZipArchive')) {
            require_once $rootPath . 'vendor/autoload.php';
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            $spreadsheet = $reader->load($fileTmp);
            return $spreadsheet->getActiveSheet()->toArray(null, true, true, false);
        } else {
            // Descompresión nativa de XLSX en PHP puro sin requerir ZipArchive
            $filasXlsx = extraerXlsxSinZipArchive($content);
            if (!empty($filasXlsx)) {
                return $filasXlsx;
            }
            throw new Exception("El archivo es un formato comprimido .xlsx y la extensión ZipArchive de PHP no está instalada en su servidor Apache/XAMPP. Por favor descargue la plantilla oficial o guarde su archivo como .xls o .csv para procesarlo sin inconvenientes.");
        }
    }

    // 3. Archivo binario BIFF8 .xls (inicia con OLE \xD0\xCF\x11\xE0)
    if ($magic === "\xD0\xCF\x11\xE0") {
        require_once $rootPath . 'vendor/autoload.php';
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
        $spreadsheet = $reader->load($fileTmp);
        return $spreadsheet->getActiveSheet()->toArray(null, true, true, false);
    }

    // 4. Archivo CSV o texto delimitado
    $handle = fopen($fileTmp, 'r');
    if ($handle) {
        $firstLine = fgets($handle);
        rewind($handle);
        $delimiter = ',';
        if (substr_count($firstLine, ';') > substr_count($firstLine, ',')) {
            $delimiter = ';';
        } elseif (substr_count($firstLine, "\t") > substr_count($firstLine, ',')) {
            $delimiter = "\t";
        }
        $filas = [];
        while (($data = fgetcsv($handle, 0, $delimiter)) !== false) {
            if (isset($data[0])) {
                $data[0] = preg_replace('/^\xEF\xBB\xBF/', '', $data[0]); // Limpiar UTF-8 BOM
            }
            $filas[] = $data;
        }
        fclose($handle);
        if (!empty($filas)) {
            return $filas;
        }
    }

    throw new Exception("No fue posible reconocer el formato del archivo. Asegúrese de usar la plantilla descargable (.xls), .xlsx o .csv.");
}
}

// 1. Procesamiento de archivo subido
$resumenCarga = NULL;

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') == 'POST' && isset($_FILES['arch']) && !empty($_FILES['arch']['tmp_name'])) {
  $fileTmp = $_FILES['arch']['tmp_name'];
  $fileName = $_FILES['arch']['name'];
  $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

  if (!in_array($fileExt, ['xlsx', 'xls', 'csv'])) {
    echo "<script>
      Swal.fire({
        icon: 'error',
        title: 'Formato no compatible',
        text: 'Por favor suba un archivo en formato Excel (.xlsx, .xls) o .csv.'
      }).then(function() { window.location.href='home.php?pg=" . $pg . "'; });
    </script>";
    return;
  }

  try {
    $filas = leerFilasDesdeArchivo($fileTmp, $fileExt, $rootPath);
    $totalFilas = count($filas);

    $tiposDb = $mele->getTiposValidos();
    $mapTipos = [];
    foreach ($tiposDb as $t) {
      $mapTipos[strtolower(trim($t['nomval']))] = $t['idval'];
      $mapTipos[$t['idval']] = $t['idval'];
    }

    $total = 0;
    $insertados = 0;
    $actualizados = 0;
    $errores = [];

    // Fila 0 es el encabezado, los datos van de la fila 1 en adelante
    for ($i = 1; $i < $totalFilas; $i++) {
      $rowCells = $filas[$i];
      $row = $i + 1; // Número de fila visual de Excel (base 1)

      $ndocusu = isset($rowCells[0]) ? trim((string)$rowCells[0]) : '';
      $nomusu_excel = isset($rowCells[1]) ? trim((string)$rowCells[1]) : '';
      $nomele = isset($rowCells[2]) ? trim((string)$rowCells[2]) : '';
      $nidele = isset($rowCells[3]) ? trim((string)$rowCells[3]) : '';
      $noplasena = isset($rowCells[4]) ? trim((string)$rowCells[4]) : '';
      $marele = isset($rowCells[5]) ? trim((string)$rowCells[5]) : '';
      $tipeleRaw = isset($rowCells[6]) ? trim((string)$rowCells[6]) : '';
      $desele = isset($rowCells[7]) ? trim((string)$rowCells[7]) : '';

      // Si la fila está completamente vacía, se ignora
      if (empty($ndocusu) && empty($nomele) && empty($nidele)) {
        continue;
      }

      $total++;

      // Validar datos obligatorios
      if (empty($ndocusu) || empty($nomele) || empty($nidele)) {
        $errores[] = [
          'fila' => $row,
          'documento' => $ndocusu ?: 'Vacío',
          'serial' => $nidele ?: 'Vacío',
          'elemento' => $nomele ?: 'Vacío',
          'motivo' => 'Faltan campos obligatorios (Documento cuentadante, Nombre o Serial/Placa).'
        ];
        continue;
      }

      // Validar existencia del usuario cuentadante
      $userRow = $mele->getUsuByDoc($ndocusu);
      if (empty($userRow)) {
        $errores[] = [
          'fila' => $row,
          'documento' => $ndocusu,
          'serial' => $nidele,
          'elemento' => $nomele,
          'motivo' => "El cuentadante con documento '$ndocusu' no existe en la base de datos de usuarios."
        ];
        continue;
      }
      $idusu = $userRow[0]['idusu'];

      // Mapear Tipo de Elemento
      $tipeleId = 80; // Default: 'Otro Sena'
      $tipeleKey = strtolower($tipeleRaw);
      if (isset($mapTipos[$tipeleKey])) {
        $tipeleId = $mapTipos[$tipeleKey];
      } elseif (!empty($tipeleRaw) && is_numeric($tipeleRaw)) {
        $tipeleId = (int)$tipeleRaw;
      }

      if (empty($marele)) $marele = 'SENA';
      if (empty($desele)) $desele = 'Elemento cargado por importación masiva';
      $noplasenaNum = (!empty($noplasena) && is_numeric($noplasena)) ? (int)$noplasena : NULL;

      // Verificar si ya existe por serial (nidele)
      $mele->setNidele($nidele);
      $existData = $mele->selectEle();

      if (!empty($existData) && $existData[0]['sum'] > 0) {
        // Actualizar registro existente
        $mele->setIdusu($idusu);
        $mele->setNomele($nomele);
        $mele->setMarele($marele);
        $mele->setTipele($tipeleId);
        $mele->setNoplasena($noplasenaNum);
        $mele->setDesele($desele);
        $mele->editBySerial();
        $actualizados++;
      } else {
        // Insertar nuevo registro
        $mele->setIdusu($idusu);
        $mele->setNomele($nomele);
        $mele->setNidele($nidele);
        $mele->setMarele($marele);
        $mele->setTipele($tipeleId);
        $mele->setNoplasena($noplasenaNum);
        $mele->setDesele($desele);
        $mele->setPreele(1);
        $nuevoId = $mele->save();

        if ($nuevoId) {
          $mele->setIdele($nuevoId);
          $mele->setRutfot('img/no-found.jpg');
          $mele->saveFoto('img/no-found.jpg');
        }
        $insertados++;
      }
    }

    $resumenCarga = [
      'total' => $total,
      'insertados' => $insertados,
      'actualizados' => $actualizados,
      'errores' => $errores
    ];
  } catch (Exception $e) {
    echo "<script>
      Swal.fire({
        icon: 'error',
        title: 'Error al procesar archivo',
        text: 'Ocurrió un error durante la lectura del archivo: " . addslashes($e->getMessage()) . "'
      });
    </script>";
  }
}
?>