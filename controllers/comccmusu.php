<?php
require_once __DIR__ . '/../models/commcmusu.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

class comccmusu {
    private $model;

    public function __construct() {
        $this->model = new commcmusu();
    }

    public function gestionarCarga() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        $accion = isset($_GET['accion']) ? $_GET['accion'] : '';

        if ($accion == 'procesar') {
            $this->cargarMasivo();
        } elseif ($accion == 'guardar') {
            $this->guardarMasivo();
        } else {
            $this->mostrarFormulario();
        }
    }

    public function mostrarFormulario($mensaje = '', $exito = false) {
        $icono = "fa-solid fa-file-excel";
        $pg = isset($_GET['pg']) ? $_GET['pg'] : 'carga_aspirantes';
        
        include __DIR__ . '/../views/comvcmusu.php';
    }

    public function cargarMasivo() {
        $mensaje = '';
        $exito = false;

        if (isset($_FILES['excel_file']) && $_FILES['excel_file']['error'] === UPLOAD_ERR_OK) {
            try {
                $filePath = $_FILES['excel_file']['tmp_name'];
                $spreadsheet = IOFactory::load($filePath);
                $worksheet = $spreadsheet->getActiveSheet();
                $rows = $worksheet->toArray();

                if (!empty($rows)) {
                    array_shift($rows);
                }

                $rows = array_values(array_filter($rows, function ($row) {
                    return is_array($row) && (trim((string)($row[1] ?? '')) !== '' || trim((string)($row[2] ?? '')) !== '');
                }));

                if (empty($rows)) {
                    $mensaje = "El archivo Excel está vacío o no contiene registros válidos.";
                    $this->mostrarFormulario($mensaje, false);
                    return;
                }

                if (session_status() == PHP_SESSION_NONE) {
                    session_start();
                }
                $_SESSION['excel_data'] = $rows;
                $total = count($rows);

                $mensaje = "Archivo leido correctamente. Se encontraron <strong>$total</strong> registros listos para validar e insertar.";
                $exito = true;

            } catch (Exception $e) {
                $mensaje = "Error al procesar el archivo Excel: " . $e->getMessage();
            }
        } else {
            $mensaje = "Debe seleccionar un archivo Excel valido para realizar la carga.";
        }

        $this->mostrarFormulario($mensaje, $exito);
    }

    public function guardarMasivo() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        $mensaje = '';
        $exito = false;

        if (isset($_SESSION['excel_data']) && !empty($_SESSION['excel_data'])) {
            $rows = $_SESSION['excel_data'];
            unset($_SESSION['excel_data']);

            $registrosExitosos = 0;
            $registrosFallidos = 0;

            foreach ($rows as $row) {
                if (empty($row[1]) || empty($row[2])) {
                    $registrosFallidos++;
                    continue;
                }

                $tipo_ident     = trim($row[1]);
                $numero_ident   = trim($row[2]);
                $codigo_ficha   = trim($row[3]);
                $tipo_poblacion = isset($row[4]) ? trim($row[4]) : '';
                $tipo_org       = isset($row[5]) ? trim($row[5]) : '';
                $codigo_emp     = isset($row[6]) ? trim($row[6]) : '';

                $resultado = $this->model->insertarAspiranteMasivo($tipo_ident, $numero_ident, $codigo_ficha, $tipo_poblacion, $tipo_org, $codigo_emp);
                
                if ($resultado) {
                    $registrosExitosos++;
                } else {
                    $registrosFallidos++;
                }
            }

            $mensaje = "Proceso completado. Registros insertados/actualizados con éxito: <strong>$registrosExitosos</strong>.";
            if ($registrosFallidos > 0) {
                $mensaje .= " Hubo $registrosFallidos registros que no pudieron procesarse por datos incompletos.";
            }
            $exito = true;

        } else {
            $mensaje = "No hay datos pendientes en sesión para guardar.";
        }

        $this->mostrarFormulario($mensaje, $exito);
    }
}

$controladorMasivo = new comccmusu();
$controladorMasivo->gestionarCarga();
?>