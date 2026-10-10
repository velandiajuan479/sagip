<?php
require_once 'models/commcmht.php';
require_once 'vendor/autoload.php';

$icono = 'fa-solid fa-file-arrow-up';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as FechaExcel;

class cchdt {

    public $mensaje     = "";
    public $tipoMensaje = "success";
    public $datos       = array();
    public $validacion  = array();
    public $resumen     = array();
    public $registrado  = false;
    public $puedeReg    = false;
    public $dash        = array();

    const DOM_JORNADA = 1;
    const DOM_PROGESP = 25;

    // Ubicación de cada dato en la plantilla oficial (fila, columna)
    private $celdas = array(
        'radicado'    => array(5,  4),
        'nompro'      => array(7,  4),
        'codpro'      => array(9,  4),
        'duracion'    => array(11, 4),
        'codslem'     => array(13, 4),
        'convenio'    => array(17, 4),
        'empresa'     => array(33, 4),
        'nit'         => array(33, 11),
        'direccion'   => array(35, 4),
        'municipio'   => array(35, 11),
        'contacto'    => array(37, 4),
        'telefono'    => array(37, 11),
        'feclini'     => array(41, 4),
        'feclin'      => array(41, 11),
        'cupo'        => array(43, 4),
        'jornada'     => array(43, 11),
        'instructor'  => array(56, 4),
        'docinstruc'  => array(58, 4),
        'correo'      => array(60, 4),
        'celular'     => array(60, 11),
        'idfic'       => array(63, 11)
    );

    // Programas especiales: fila y columna de la etiqueta, columna de la marca
    private $especiales = array(
        array('fila'=>20, 'colEtq'=>4,  'colX'=>5),
        array('fila'=>20, 'colEtq'=>7,  'colX'=>8),
        array('fila'=>22, 'colEtq'=>4,  'colX'=>5),
        array('fila'=>22, 'colEtq'=>7,  'colX'=>8),
        array('fila'=>24, 'colEtq'=>4,  'colX'=>5),
        array('fila'=>26, 'colEtq'=>4,  'colX'=>5),
        array('fila'=>28, 'colEtq'=>4,  'colX'=>5)
    );

    // Enruta segun la accion del formulario
    public function procesar() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        $mhdt = new mchdt();
        $this->dash = $mhdt->getDashboard();

        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            return;
        }
        if (isset($_POST['accion']) && $_POST['accion'] == 'registrar') {
            $this->RegistrarHoja();
            $this->dash = $mhdt->getDashboard();
        } else {
            $this->CargarExcel();
        }
    }

    public function CargarExcel() {

        $extArchivo = strtolower(pathinfo($_FILES['archivo']['name'], PATHINFO_EXTENSION));
        if ($extArchivo == 'xlsx' && !class_exists('ZipArchive')) {
            $this->mensaje     = "El servidor no tiene habilitada la extensión ZIP de PHP, "
                               . "necesaria para leer archivos .xlsx. Habilite 'extension=zip' "
                               . "en php.ini y reinicie Apache, o use la plantilla en formato .xls.";
            $this->tipoMensaje = "warning";
            return;
        }

        $mchdt = new mchdt();

        if (!$mchdt->validarSubida($_FILES['archivo'])) {
            $this->mensaje     = $mchdt->error;
            $this->tipoMensaje = "danger";
            return;
        }

        $ruta = $mchdt->guardarArchivo($_FILES['archivo']['tmp_name'], $_FILES['archivo']['name']);

        if (!$ruta) {
            $this->mensaje     = $mchdt->error;
            $this->tipoMensaje = "danger";
            return;
        }

        try {
            $lector = IOFactory::createReaderForFile($ruta);
            $lector->setReadDataOnly(true);
            $libro = $lector->load($ruta);
            $hoja  = $libro->getSheet(0);

            $this->leerFormulario($hoja);

        } catch (\Throwable $e) {
            $this->tipoMensaje = "danger";
            if (strpos($e->getMessage(), 'Unable to identify a reader') !== false) {
                $this->mensaje = "El archivo seleccionado no es un archivo Excel válido.";
            } elseif (strpos($e->getMessage(), 'ZipArchive') !== false) {
                $this->mensaje = "El servidor no tiene habilitada la extensión ZIP de PHP.";
                $this->tipoMensaje = "warning";
            } else {
                $this->mensaje = "No se pudo leer el archivo: " . $e->getMessage();
            }
            $this->datos = array();
        }

        $mchdt->borrarArchivo($ruta);
    }

    // Extrae los datos de las celdas fijas de la plantilla
    private function leerFormulario($hoja) {

        // Verifica que sea la plantilla oficial
        $titulo = $this->normalizar($hoja->getCellByColumnAndRow(2, 3)->getValue());
        if (strpos($titulo, 'HOJA DE TRABAJO') === false) {
            $this->mensaje     = "El archivo no corresponde a la plantilla oficial de hoja de trabajo. "
                               . "Descargue el formato y no modifique su estructura.";
            $this->tipoMensaje = "danger";
            return;
        }

        $d = array();
        foreach ($this->celdas as $campo => $pos) {
            $valor = $hoja->getCellByColumnAndRow($pos[1], $pos[0])->getValue();
            $d[$campo] = trim((string)$valor);
        }

        $d['feclini'] = $this->normalizarFecha($d['feclini']);
        $d['feclin']  = $this->normalizarFecha($d['feclin']);
        $d['progesp'] = $this->leerProgramaEspecial($hoja);

        $this->datos = $d;
        $this->validarContraBD();
        $_SESSION['hdt_datos'] = $this->datos;

        $errores = 0;
        foreach ($this->validacion as $v) {
            if ($v['estado'] == 'error') { $errores++; }
        }

        $this->puedeReg = ($errores == 0);

        if ($errores == 0) {
            $this->mensaje     = "Hoja de trabajo leída correctamente. Revise los datos antes de registrar.";
            $this->tipoMensaje = "success";
        } else {
            $this->mensaje     = "La hoja de trabajo tiene $errores dato(s) con error. "
                               . "Corrija el archivo y vuelva a cargarlo.";
            $this->tipoMensaje = "warning";
        }
    }

    // Busca cuál programa especial quedó marcado con X
    private function leerProgramaEspecial($hoja) {
        foreach ($this->especiales as $op) {
            $marca = trim((string)$hoja->getCellByColumnAndRow($op['colX'], $op['fila'])->getValue());
            if ($marca !== '') {
                $etq = trim((string)$hoja->getCellByColumnAndRow($op['colEtq'], $op['fila'])->getValue());
                if ($etq !== '') { return $etq; }
            }
        }
        return 'Ninguno';
    }

    // Valida los datos contra las tablas de referencia
    private function validarContraBD() {

        $mhdt = new mchdt();
        $this->validacion = array();
        $d = $this->datos;

        // Código de programa
        $codproOri = isset($d['codpro']) ? trim((string)$d['codpro']) : '';
        $nomproOri = isset($d['nompro']) ? trim((string)$d['nompro']) : '';

        if ($codproOri !== '' && is_numeric($codproOri)) {
            // Se subió con código de programa
            $progExiste = $mhdt->buscarPrograma($codproOri);
            if ($progExiste) {
                // YA EXISTA un código en BD -> NO SE MODIFIQUE EN ABSOLUTAMENTE NADA
                $this->validacion['codpro'] = array('estado'=>'ok', 'nota'=>'');
            } else {
                // Viene código pero no existe en BD -> Se creará automáticamente al registrar (En revisión)
                $this->validacion['codpro'] = array(
                    'estado'=>'aviso',
                    'nota'=>'El código '.$codproOri.' no existe en BD. Se registrará automáticamente (En revisión).'
                );
            }
        } else {
            // Se subió una hoja de trabajo SIN código -> Crear o asignar código de programa
            $progPorNom = $mhdt->buscarProgramaPorNombre($nomproOri);
            if ($progPorNom) {
                $codAsignado = $progPorNom['codpro'];
                $d['codpro'] = $codAsignado;
                $this->datos['codpro'] = $codAsignado;
                $this->validacion['codpro'] = array(
                    'estado'=>'aviso',
                    'nota'=>'Hoja sin código: se asignó el código existente '.$codAsignado.' por nombre de programa (En revisión).'
                );
            } else {
                $nuevoCodpro = $mhdt->getNuevoCodpro();
                $d['codpro'] = $nuevoCodpro;
                $this->datos['codpro'] = $nuevoCodpro;
                $this->validacion['codpro'] = array(
                    'estado'=>'aviso',
                    'nota'=>'Hoja sin código: se generó automáticamente el código '.$nuevoCodpro.' (En revisión).'
                );
            }
        }

        // Empresa
        if ($d['empresa'] === '') {
            $this->validacion['empresa'] = array('estado'=>'error',
                'nota'=>'La empresa es obligatoria.');
        } elseif (!$mhdt->buscarEmpresa($d['empresa'])) {
            $this->validacion['empresa'] = array('estado'=>'aviso',
                'nota'=>'La empresa se creará automáticamente al registrar.');
        } else {
            $this->validacion['empresa'] = array('estado'=>'ok', 'nota'=>'');
        }

        // Jornada
        if (!$mhdt->buscarValor($d['jornada'], self::DOM_JORNADA)) {
            $this->validacion['jornada'] = array('estado'=>'error',
                'nota'=>'Jornada no válida. Use: Mañana, Tarde, Noche, Mixta, Fin de Semana o Virtual.');
        } else {
            $this->validacion['jornada'] = array('estado'=>'ok', 'nota'=>'');
        }

        // Programa especial
        if (!$mhdt->buscarValor($d['progesp'], self::DOM_PROGESP)) {
            $this->validacion['progesp'] = array('estado'=>'error',
                'nota'=>'El programa especial "'.$d['progesp'].'" no está registrado.');
        } else {
            $this->validacion['progesp'] = array('estado'=>'ok', 'nota'=>'');
        }

        // Fechas
        if ($d['feclini'] === '' || $d['feclin'] === '') {
            $this->validacion['fechas'] = array('estado'=>'error',
                'nota'=>'Las fechas de inicio y terminación son obligatorias.');
        } elseif ($d['feclin'] < $d['feclini']) {
            $this->validacion['fechas'] = array('estado'=>'error',
                'nota'=>'La fecha de terminación es anterior a la de inicio.');
        } else {
            $this->validacion['fechas'] = array('estado'=>'ok', 'nota'=>'');
        }

        // Cupo
        if ($d['cupo'] === '' || !is_numeric($d['cupo']) || $d['cupo'] <= 0) {
            $this->validacion['cupo'] = array('estado'=>'error',
                'nota'=>'El cupo debe ser un número mayor a cero.');
        } else {
            $this->validacion['cupo'] = array('estado'=>'ok', 'nota'=>'');
        }

        // Ficha
        if ($d['idfic'] !== '' && !$mhdt->existeFicha($d['idfic'])) {
            $this->validacion['idfic'] = array('estado'=>'aviso',
                'nota'=>'La ficha '.$d['idfic'].' no existe: se guardará sin ficha.');
        } else {
            $this->validacion['idfic'] = array('estado'=>'ok', 'nota'=>'');
        }
    }

    // Inserta la hoja de trabajo en la base de datos
    public function RegistrarHoja() {

        if (empty($_SESSION['hdt_datos'])) {
            $this->mensaje     = "No hay datos para registrar. Cargue nuevamente el archivo.";
            $this->tipoMensaje = "danger";
            return;
        }

        $this->datos = $_SESSION['hdt_datos'];
        $this->validarContraBD();

        foreach ($this->validacion as $v) {
            if ($v['estado'] == 'error') {
                $this->mensaje     = "La hoja de trabajo tiene errores y no puede registrarse.";
                $this->tipoMensaje = "danger";
                return;
            }
        }

        $mhdt = new mchdt();
        $d    = $this->datos;
        $creo = false;

        $idemp = $mhdt->buscarEmpresa($d['empresa']);
        if (!$idemp) {
            $idemp = $mhdt->crearEmpresa($d['empresa']);
            if (!$idemp) {
                $this->mensaje     = "No se pudo crear la empresa. Intente de nuevo.";
                $this->tipoMensaje = "danger";
                return;
            }
            $creo = true;
        }

        $idfic = $d['idfic'];
        if ($idfic === '' || !$mhdt->existeFicha($idfic)) {
            $idfic = null;
        }

        // Garantizar que el programa esté registrado en la BD (si ya existe, NO modifica nada)
        if (!$mhdt->buscarPrograma($d['codpro'])) {
            $dur = isset($d['duracion']) ? $d['duracion'] : 0;
            $mhdt->crearPrograma($d['codpro'], $d['nompro'], $dur);
        }

        $registro = new mchdt();
        $registro->setCodpro($d['codpro']);
        $registro->setIdemp($idemp);
        $registro->setCodproesp($mhdt->buscarValor($d['progesp'], self::DOM_PROGESP));
        $registro->setCodslem($d['codslem']);
        $registro->setFeclini($d['feclini']);
        $registro->setFeclin($d['feclin']);
        $registro->setCupo($d['cupo']);
        $registro->setJornada($mhdt->buscarValor($d['jornada'], self::DOM_JORNADA));
        $registro->setIdfic($idfic);
        $registro->setConvht($d['convenio']);

        $idnorad = $registro->saveCompleto();

        if ($idnorad) {
            $registro->saveHxU();
            unset($_SESSION['hdt_datos']);
            $this->registrado  = true;
            $this->datos       = array();
            $this->puedeReg    = false;
            $this->resumen     = array('idnorad'=>$idnorad, 'empresa'=>$creo ? $d['empresa'] : '');
            $this->mensaje     = "Hoja de trabajo registrada con el radicado $idnorad.";
            $this->tipoMensaje = "success";
        } else {
            $this->mensaje     = "Error al registrar la hoja de trabajo en la base de datos.";
            $this->tipoMensaje = "danger";
        }
    }

    // Convierte seriales de Excel y otros formatos a AAAA-MM-DD
    private function normalizarFecha($valor) {

        $valor = trim((string)$valor);
        if ($valor === '') { return ''; }

        if (is_numeric($valor) && $valor > 20000 && $valor < 80000) {
            try {
                $fecha = FechaExcel::excelToDateTimeObject((float)$valor);
                return $fecha->format('Y-m-d');
            } catch (\Throwable $e) {
                return $valor;
            }
        }

        $formatos = array('Y-m-d', 'd/m/Y', 'd-m-Y', 'Y/m/d', 'd/m/y', 'Y-m-d H:i:s');
        foreach ($formatos as $f) {
            $fecha = \DateTime::createFromFormat($f, $valor);
            if ($fecha !== false) {
                return $fecha->format('Y-m-d');
            }
        }

        return $valor;
    }

    private function normalizar($texto) {
        $texto = trim((string)$texto);
        if ($texto === '') { return ''; }
        $texto = mb_strtoupper($texto, 'UTF-8');
        $texto = strtr($texto, array(
            'Á'=>'A','É'=>'E','Í'=>'I','Ó'=>'O','Ú'=>'U',
            'Ñ'=>'N','Ü'=>'U','.'=>'','_'=>' '
        ));
        $texto = preg_replace('/\s+/', ' ', $texto);
        return trim($texto);
    }
}
?>