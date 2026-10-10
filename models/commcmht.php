<?php
require_once("conexion.php");

class mchdt {

    public $error = "";

    private $codpro;
    private $idemp;
    private $codproesp;
    private $codslem;
    private $feclini;
    private $feclin;
    private $cupo;
    private $jornada;
    private $idfic;
    private $convht;
    private $idnorad;

//Setters
    function setCodpro($codpro){       $this->codpro = $codpro; }
    function setIdemp($idemp){         $this->idemp = $idemp; }
    function setCodproesp($codproesp){ $this->codproesp = $codproesp; }
    function setCodslem($codslem){     $this->codslem = $codslem; }
    function setFeclini($feclini){     $this->feclini = $feclini; }
    function setFeclin($feclin){       $this->feclin = $feclin; }
    function setCupo($cupo){           $this->cupo = $cupo; }
    function setJornada($jornada){     $this->jornada = $jornada; }
    function setIdfic($idfic){         $this->idfic = $idfic; }
    function setConvht($convht){       $this->convht = $convht; }

//Getters
    function getIdnorad(){ return $this->idnorad; }

//Manejo del archivo subido

    // Valida la subida antes de mover el archivo
    public function validarSubida($archivo) {

        if (!isset($archivo) || !is_array($archivo)) {
            $this->error = "No se recibió ningún archivo.";
            return false;
        }

        switch ($archivo['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_NO_FILE:
                $this->error = "No seleccionó ningún archivo.";
                return false;
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $this->error = "El archivo supera el tamaño permitido por el servidor.";
                return false;
            case UPLOAD_ERR_PARTIAL:
                $this->error = "El archivo se subió incompleto. Intente de nuevo.";
                return false;
            case UPLOAD_ERR_NO_TMP_DIR:
            case UPLOAD_ERR_CANT_WRITE:
                $this->error = "Error del servidor al guardar el archivo temporal.";
                return false;
            default:
                $this->error = "Error desconocido al subir el archivo.";
                return false;
        }

        if ($archivo['size'] <= 0) {
            $this->error = "El archivo está vacío.";
            return false;
        }

        if ($archivo['size'] > 5242880) { // 5 MB
            $this->error = "El archivo supera los 5 MB permitidos.";
            return false;
        }

        $ext = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, array('xls', 'xlsx'))) {
            $this->error = "Solo se permiten archivos de Excel (.xls o .xlsx).";
            return false;
        }

        return true;
    }

    // Guarda con nombre único para que un archivo no pise al anterior
    public function guardarArchivo($rutaTemporal, $nombreArchivo) {
        $carpetaDestino = 'uploads/';
        if (!is_dir($carpetaDestino)) {
            if (!mkdir($carpetaDestino, 0777, true)) {
                $this->error = "No se pudo crear la carpeta de destino.";
                return false;
            }
        }

        $ext = strtolower(pathinfo($nombreArchivo, PATHINFO_EXTENSION));
        $nombreUnico = 'hdt_' . date('Ymd_His') . '_' . uniqid() . '.' . $ext;
        $rutaDestino = $carpetaDestino . $nombreUnico;

        if (move_uploaded_file($rutaTemporal, $rutaDestino)) {
            return $rutaDestino;
        }

        $this->error = "No se pudo guardar el archivo en el servidor.";
        return false;
    }

    // Borra el archivo temporal después de leerlo
    public function borrarArchivo($ruta) {
        if (!empty($ruta) && is_file($ruta)) {
            @unlink($ruta);
        }
    }

//Consultas de validación contra la base de datos

    // idnorad no es autoincremental: se calcula el siguiente
    public function getNuevoIdnorad(){
        try {
            $sql = "SELECT IFNULL(MAX(idnorad),0)+1 AS nuevo FROM hojatra";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->execute();
            $res = $result->fetch(PDO::FETCH_ASSOC);
            return $res['nuevo'];
        } catch (Exception $e) {
            return false;
        }
    }

    public function buscarEmpresa($nombre){
        try {
            $sql = "SELECT idemp FROM empresa WHERE UPPER(TRIM(nomemp)) = UPPER(TRIM(:nombre)) LIMIT 1";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->bindParam(':nombre',$nombre);
            $result->execute();
            $res = $result->fetch(PDO::FETCH_ASSOC);
            return $res ? $res['idemp'] : false;
        } catch (Exception $e) {
            return false;
        }
    }

    // Crea la empresa cuando no existe y devuelve su id
    public function crearEmpresa($nombre){
        try {
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();

            $sql = "SELECT IFNULL(MAX(idemp),0)+1 AS nuevo FROM empresa";
            $result = $conexion->prepare($sql);
            $result->execute();
            $res = $result->fetch(PDO::FETCH_ASSOC);
            $idemp = $res['nuevo'];

            $sql = "INSERT INTO empresa (idemp, nomemp, usucre, feccre)
                    VALUES (:idemp, :nomemp, :usucre, NOW())";
            $result = $conexion->prepare($sql);
            $result->bindParam(':idemp',$idemp);
            $result->bindParam(':nomemp',$nombre);
            $usucre = isset($_SESSION["idusu"]) ? $_SESSION["idusu"] : null;
            $result->bindParam(':usucre',$usucre);
            $result->execute();

            return $idemp;
        } catch (Exception $e) {
            return false;
        }
    }

    public function buscarPrograma($codpro){
        try {
            $sql = "SELECT codpro, nompro FROM programa WHERE codpro = :codpro LIMIT 1";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->bindParam(':codpro',$codpro);
            $result->execute();
            $res = $result->fetch(PDO::FETCH_ASSOC);
            return $res ? $res : false;
        } catch (Exception $e) {
            return false;
        }
    }

    public function buscarProgramaPorNombre($nompro){
        $nompro = trim((string)$nompro);
        if ($nompro === '') { return false; }
        try {
            $sql = "SELECT codpro, nompro FROM programa WHERE UPPER(TRIM(nompro)) = UPPER(TRIM(:nompro)) LIMIT 1";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->bindParam(':nompro',$nompro);
            $result->execute();
            $res = $result->fetch(PDO::FETCH_ASSOC);
            return $res ? $res : false;
        } catch (Exception $e) {
            return false;
        }
    }

    public function getNuevoCodpro(){
        try {
            $sql = "SELECT IFNULL(MAX(codpro), 9000000) + 1 AS nuevo FROM programa";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->execute();
            $res = $result->fetch(PDO::FETCH_ASSOC);
            return ($res && !empty($res['nuevo'])) ? $res['nuevo'] : rand(9000001, 9999999);
        } catch (Exception $e) {
            return rand(9000001, 9999999);
        }
    }

    public function crearPrograma($codpro, $nompro, $duracion = null){
        try {
            // SI YA EXISTA un código en la tabla programa, NO SE MODIFIQUE EN ABSOLUTAMENTE NADA
            if ($this->buscarPrograma($codpro)) {
                return true;
            }
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $sql = "INSERT INTO programa (codpro, nompro, horlpro, idare) VALUES (:codpro, :nompro, :horlpro, 1)";
            $result = $conexion->prepare($sql);
            $result->bindParam(':codpro', $codpro);
            $nomproClean = trim((string)$nompro) !== '' ? trim((string)$nompro) : 'PROGRAMA NUEVO EN REVISIÓN';
            $result->bindParam(':nompro', $nomproClean);
            $dur = (is_numeric($duracion) && $duracion > 0) ? (int)$duracion : 0;
            $result->bindParam(':horlpro', $dur);
            return $result->execute();
        } catch (Exception $e) {
            return false;
        }
    }

    // Traduce el texto del Excel al idval correspondiente
    public function buscarValor($nombre, $iddom){
        try {
            $sql = "SELECT idval, nomval FROM valor WHERE iddom = :iddom AND act = 1";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->bindParam(':iddom',$iddom);
            $result->execute();
            $lista = $result->fetchAll(PDO::FETCH_ASSOC);

            $buscado = $this->limpiar($nombre);
            if ($buscado === '') { return false; }

            foreach ($lista as $v) {
                if ($this->limpiar($v['nomval']) === $buscado) {
                    return $v['idval'];
                }
            }
            return false;
        } catch (Exception $e) {
            return false;
        }
    }

    // Compara ignorando tildes, ñ y problemas de codificación
    private function limpiar($texto){
        $texto = mb_strtoupper(trim((string)$texto), 'UTF-8');
        $texto = preg_replace('/[^A-Z0-9 ]/', '', $texto);
        $texto = preg_replace('/\s+/', ' ', $texto);
        return trim($texto);
    }

    public function existeFicha($idfic){
        try {
            $sql = "SELECT idfic FROM ficha WHERE idfic = :idfic LIMIT 1";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->bindParam(':idfic',$idfic);
            $result->execute();
            $res = $result->fetch(PDO::FETCH_ASSOC);
            return $res ? true : false;
        } catch (Exception $e) {
            return false;
        }
    }

//Tablero de indicadores

    // Métricas para el tablero de hojas de trabajo
    public function getDashboard(){
        try {
            $sql = "SELECT
                        (SELECT COUNT(*) FROM hojatra) AS total,
                        (SELECT IFNULL(SUM(cupo),0) FROM hojatra) AS cupos,
                        (SELECT COUNT(DISTINCT idemp) FROM hojatra) AS empresas,
                        (SELECT COUNT(*) FROM hojatra
                          WHERE YEAR(feclini) = YEAR(CURDATE())) AS anio,
                        (SELECT COUNT(*) FROM hojatra
                          WHERE idfic IS NULL OR idfic = '') AS sinficha";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->execute();
            $res = $result->fetch(PDO::FETCH_ASSOC);
            return $res ? $res : array('total'=>0,'cupos'=>0,'empresas'=>0,'anio'=>0,'sinficha'=>0);
        } catch (Exception $e) {
            return array('total'=>0,'cupos'=>0,'empresas'=>0,'anio'=>0,'sinficha'=>0);
        }
    }

//Registro en la base de datos

    // Inserta la hoja de trabajo con el id calculado
    public function saveCompleto(){
        try {
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();

            $idnorad = $this->getNuevoIdnorad();
            if (!$idnorad) { return false; }

            $sql = "INSERT INTO hojatra
                    (idnorad, codpro, idemp, codproesp, codslem, feclini, feclin, cupo, jornada, idfic, convht)
                    VALUES
                    (:idnorad, :codpro, :idemp, :codproesp, :codslem, :feclini, :feclin, :cupo, :jornada, :idfic, :convht)";
            $result = $conexion->prepare($sql);
            $result->bindParam(':idnorad',$idnorad);
            $result->bindParam(':codpro',$this->codpro);
            $result->bindParam(':idemp',$this->idemp);
            $result->bindParam(':codproesp',$this->codproesp);
            $result->bindParam(':codslem',$this->codslem);
            $result->bindParam(':feclini',$this->feclini);
            $result->bindParam(':feclin',$this->feclin);
            $result->bindParam(':cupo',$this->cupo);
            $result->bindParam(':jornada',$this->jornada);
            $result->bindParam(':idfic',$this->idfic);
            $result->bindParam(':convht',$this->convht);
            $result->execute();

            $this->idnorad = $idnorad;
            return $idnorad;
        } catch (Exception $e) {
            return false;
        }
    }

    // Asocia la hoja al usuario que la registró
    public function saveHxU(){
        try {
            $sql = "INSERT INTO hdtxusu (idnorad, idusu) VALUES (:idnorad, :idusu)";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->bindParam(':idnorad',$this->idnorad);
            $idusu = isset($_SESSION["idusu"]) ? $_SESSION["idusu"] : null;
            $result->bindParam(':idusu',$idusu);
            $result->execute();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
?>