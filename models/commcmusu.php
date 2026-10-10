<?php
require_once __DIR__ . '/conexion.php';

class commcmusu {
    private $db;

    public function __construct() {
        $conexion = new conexion();
        $this->db = $conexion->get_conexion();
    }

    public function insertarAspiranteMasivo($tipoIdent, $numeroIdent, $codigoFicha, $tipoPoblacion, $tipoOrg, $codigoEmpresa) {
        try {
            $consulta = $this->db->prepare('SELECT idasp FROM aspirante WHERE ndocusu = ? LIMIT 1');
            $consulta->execute([$numeroIdent]);
            $existente = $consulta->fetch(PDO::FETCH_ASSOC);

            if ($existente) {
                $sql = 'UPDATE aspirante SET tdousu = ?, idfic = ?, estado = ? WHERE idasp = ?';
                $parametros = [$tipoIdent, $codigoFicha, 'Pendiente', $existente['idasp']];
            } else {
                $sql = 'INSERT INTO aspirante (tdousu, ndocusu, idfic, fecreg, estado) VALUES (?, ?, ?, NOW(), ?)';
                $parametros = [$tipoIdent, $numeroIdent, $codigoFicha, 'Pendiente'];
            }

            $sentencia = $this->db->prepare($sql);
            return $sentencia->execute($parametros);
        } catch (PDOException $e) {
            error_log('Error en commcmusu::insertarAspiranteMasivo: ' . $e->getMessage());
            return false;
        }
    }
}
