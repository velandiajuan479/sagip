<?php
require_once __DIR__ . '/conexion.php';

class commuasp {
    public $idasp;
    public $idusu;
    public $idnorad;
    public $tdousu;
    public $ndocusu;
    public $nomasp;
    public $apeasp;
    public $fecnac;
    public $genusu;
    public $telasp;
    public $emasp;
    public $diresp;
    public $muncod;
    public $idfic;
    public $motasp;
    public $dispoasp;
    public $codpro;
    public $fecreg;
    public $estado;
    
    // Propiedades auxiliares mapeadas a los inputs del formulario simplificado
    public $nombre;
    public $tipoId;
    public $documento;
    public $email;

    private $db;

    public function __construct() {
        $conexionObj = new conexion();
        $this->db = $conexionObj->get_conexion();
    }

    public function setId($id) { $this->idasp = $id; }
    public function setNombre($nombre) { 
        $this->nombre = trim($nombre); 
        $this->nomasp = trim($nombre); // Mapeo a la columna de la BD
    }
    public function setTipoId($tipoId) { 
        $this->tipoId = $tipoId; 
        $this->tdousu = $tipoId; // Mapeo a la columna de la BD
    }
    public function setDocumento($documento) { 
        $this->documento = trim($documento); 
        $this->ndocusu = trim($documento); // Mapeo a la columna de la BD
    }
    public function setEmail($email) { 
        $this->email = trim($email); 
        $this->emasp = trim($email); // Mapeo a la columna de la BD
    }

    public function getId() { return $this->idasp; }
    public function getNombre() { return $this->nombre ?? $this->nomasp; }
    public function getTipoId() { return $this->tipoId ?? $this->tdousu; }
    public function getDocumento() { return $this->documento ?? $this->ndocusu; }
    public function getEmail() { return $this->email ?? $this->emasp; }

    public function getAll() {
        try {
            $sql = "SELECT * FROM aspirante ORDER BY idasp DESC";
            $stmt = $this->db->query($sql);
            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Normalizamos las llaves para que la vista las lea fácilmente sin romper la BD
            foreach ($resultados as &$row) {
                $row['nombre'] = $row['nomasp'] ?? '';
                $row['tipoId'] = $row['tdousu'] ?? '';
                $row['documento'] = $row['ndocusu'] ?? '';
                $row['email'] = $row['emasp'] ?? '';
            }
            return $resultados;
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getOne($id) {
        try {
            $sql = "SELECT * FROM aspirante WHERE idasp = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Normalizamos las llaves para que coincidan con la vista
            foreach ($resultados as &$row) {
                $row['nombre'] = $row['nomasp'] ?? '';
                $row['tipoId'] = $row['tdousu'] ?? '';
                $row['documento'] = $row['ndocusu'] ?? '';
                $row['email'] = $row['emasp'] ?? '';
            }
            return $resultados;
        } catch (PDOException $e) {
            return [];
        }
    }

    public function edi($id) {
        $resultado = $this->getOne($id);
        if (!empty($resultado)) {
            $this->idasp = $resultado[0]['idasp'] ?? null;
            $this->nombre = $resultado[0]['nombre'] ?? '';
            $this->nomasp = $this->nombre;
            $this->tipoId = $resultado[0]['tipoId'] ?? '';
            $this->tdousu = $this->tipoId;
            $this->documento = $resultado[0]['documento'] ?? '';
            $this->ndocusu = $this->documento;
            $this->email = $resultado[0]['email'] ?? '';
            $this->emasp = $this->email;
        }
        return $resultado;
    }

    public function save() {
        try {
            if (!empty($this->idasp)) {
                // Consulta de actualización enfocada en los campos que maneja tu vista actual
                $sql = "UPDATE aspirante SET tdousu = ?, ndocusu = ?, nomasp = ?, emasp = ? WHERE idasp = ?";
                $stmt = $this->db->prepare($sql);
                return $stmt->execute([
                    $this->tdousu, 
                    $this->ndocusu, 
                    $this->nomasp, 
                    $this->emasp, 
                    $this->idasp
                ]);
            } else {
                // Consulta de inserción enfocada en los campos de tu formulario
                $sql = "INSERT INTO aspirante (tdousu, ndocusu, nomasp, emasp, fecreg) VALUES (?, ?, ?, ?, NOW())";
                $stmt = $this->db->prepare($sql);
                return $stmt->execute([
                    $this->tdousu, 
                    $this->ndocusu, 
                    $this->nomasp, 
                    $this->emasp
                ]);
            }
        } catch (PDOException $e) {
            return false;
        }
    }

    public function eliminarAspirante($id) {
        try {
            $sql = "DELETE FROM aspirante WHERE idasp = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            return false;
        }
    }
}