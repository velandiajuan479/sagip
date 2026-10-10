<?php
$connPath = file_exists('models/conexion.php') ? 'models/conexion.php' : (file_exists('conexion.php') ? 'conexion.php' : '../models/conexion.php');
require_once $connPath;

if (!class_exists('Mhaul')) {
  class Mhaul {
    private $idpres;
    private $idaul;
    private $idusu;
    private $jornada;
    private $estado;
    private $fechin;
    private $fechfin;

    function getIdpres() { return $this->idpres; }
    function getIdaul() { return $this->idaul; }
    function getIdusu() { return $this->idusu; }
    function getJornada() { return $this->jornada; }
    function getEstado() { return $this->estado; }
    function getFechin() { return $this->fechin; }
    function getFechfin() { return $this->fechfin; }

    function setIdpres($idpres) { $this->idpres = $idpres; }
    function setIdaul($idaul) { $this->idaul = $idaul; }
    function setIdusu($idusu) { $this->idusu = $idusu; }
    function setJornada($jornada) { $this->jornada = $jornada; }
    function setEstado($estado) { $this->estado = $estado; }
    function setFechin($fechin) { $this->fechin = $fechin; }
    function setFechfin($fechfin) { $this->fechfin = $fechfin; }

    public function getAll($fechin = NULL, $fechfin = NULL, $idusu = NULL, $idaul = NULL, $estado = NULL) {
      try {
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $sql = "SELECT p.idpres, p.idaul, a.nomaul, a.piso, a.bloqau, p.idusu, u.ndocusu, u.nomusu, p.jornada, v.nomval AS nomjor, p.estado, p.fechin, p.fechfin 
                FROM presaula AS p 
                LEFT JOIN usuario AS u ON p.idusu = u.idusu 
                LEFT JOIN aula AS a ON p.idaul = a.idaul 
                LEFT JOIN valor AS v ON p.jornada = v.idval 
                WHERE 1";
        if (!empty($fechin) && !empty($fechfin)) {
          $sql .= " AND p.fechin BETWEEN :fin AND :ffi";
        } else if (!empty($fechin)) {
          $sql .= " AND p.fechin >= :fin";
        } else if (!empty($fechfin)) {
          $sql .= " AND p.fechin <= :ffi";
        }
        if (!empty($idusu)) {
          $sql .= " AND p.idusu = :idusu";
        }
        if (!empty($idaul)) {
          $sql .= " AND p.idaul = :idaul";
        }
        if (!empty($estado)) {
          if ($estado === 'novedad') {
            $sql .= " AND (p.estado LIKE '%llave%' OR p.estado LIKE '%novedad%')";
          } else {
            $sql .= " AND p.estado = :estado";
          }
        }
        $sql .= " ORDER BY p.fechin DESC, p.idpres DESC";

        $result = $conexion->prepare($sql);
        if (!empty($fechin)) {
          $fin = $fechin.' 00:00:00';
          $result->bindParam(':fin', $fin);
        }
        if (!empty($fechfin)) {
          $ffi = $fechfin.' 23:59:59';
          $result->bindParam(':ffi', $ffi);
        }
        if (!empty($idusu)) {
          $userId = is_array($idusu) ? ($idusu[0]['idusu'] ?? $idusu['idusu'] ?? 0) : $idusu;
          $result->bindParam(':idusu', $userId);
        }
        if (!empty($idaul)) {
          $result->bindParam(':idaul', $idaul);
        }
        if (!empty($estado) && $estado !== 'novedad') {
          $result->bindParam(':estado', $estado);
        }
        $result->execute();
        return $result->fetchAll(PDO::FETCH_ASSOC);
      } catch (Exception $e) {
        error_log("Error en Mhaul::getAll: " . $e->getMessage());
        return [];
      }
    }

    public function getEstadisticasAulas() {
      try {
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $sql = "SELECT 
          COUNT(*) AS total,
          COUNT(DISTINCT p.idaul) AS aulas_utilizadas,
          SUM(CASE WHEN p.estado = 'Ocupado' OR p.fechfin IS NULL OR p.fechfin = '0000-00-00 00:00:00' THEN 1 ELSE 0 END) AS ocupadas,
          SUM(CASE WHEN p.estado = 'Liberado' THEN 1 ELSE 0 END) AS liberadas,
          SUM(CASE WHEN p.estado LIKE '%llave%' OR p.estado LIKE '%novedad%' THEN 1 ELSE 0 END) AS novedades
        FROM presaula AS p";
        $stmt = $conexion->prepare($sql);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return [
          'total' => (int)($row['total'] ?? 0),
          'aulas_utilizadas' => (int)($row['aulas_utilizadas'] ?? 0),
          'ocupadas' => (int)($row['ocupadas'] ?? 0),
          'liberadas' => (int)($row['liberadas'] ?? 0),
          'novedades' => (int)($row['novedades'] ?? 0)
        ];
      } catch (Exception $e) {
        return ['total' => 0, 'aulas_utilizadas' => 0, 'ocupadas' => 0, 'liberadas' => 0, 'novedades' => 0];
      }
    }

    public function getOne() {
      try {
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $sql = "SELECT m.idpres, m.idusu, m.idaul, m.jornada, m.fechin, m.fechfin FROM presaula AS m INNER JOIN usuario AS u ON m.idusu=u.idusu WHERE m.idpres=:idpres";
        $result = $conexion->prepare($sql);
        $idpres = $this->getIdpres();
        $result->bindParam(':idpres', $idpres);
        $result->execute();
        return $result->fetchAll(PDO::FETCH_ASSOC);
      } catch (Exception $e) {
        return [];
      }
    }

    public function getUser($documentNumber) {
      try {
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $sql = "SELECT idusu, nomusu, ndocusu FROM usuario WHERE ndocusu=:ndocusu";
        $result = $conexion->prepare($sql);
        $result->bindParam(':ndocusu', $documentNumber);
        $result->execute();
        return $result->fetchAll(PDO::FETCH_ASSOC);
      } catch (Exception $e) {
        return [];
      }
    }

    public function getAllAula() {
      try {
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $sql = "SELECT idaul, nomaul FROM aula WHERE tipaul=1";
        $result = $conexion->prepare($sql);
        $result->execute();
        return $result->fetchAll(PDO::FETCH_ASSOC);
      } catch (Exception $e) {
        return [];
      }
    }

    public function getAulas() {
      try {
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $sql = "SELECT idaul, nomaul FROM aula";
        $result = $conexion->prepare($sql);
        $result->execute();
        return $result->fetchAll(PDO::FETCH_ASSOC);
      } catch (Exception $e) {
        return [];
      }
    }

    public function getAulOcu() {
      try {
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $sql = "SELECT p.idpres, p.idaul, p.idusu, u.ndocusu, u.nomusu, u.emausu, u.fotcan, u.telcan, p.jornada, p.estado, p.fechin, p.fechfin FROM presaula AS p INNER JOIN usuario AS u ON p.idusu=u.idusu WHERE p.estado='Ocupado' AND p.idaul=:idaul";
        $result = $conexion->prepare($sql);
        $idaul = $this->getIdaul();
        $result->bindParam(':idaul', $idaul);
        $result->execute();
        return $result->fetchAll(PDO::FETCH_ASSOC);
      } catch (Exception $e) {
        return [];
      }
    }

    public function save() {
      try {
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $sql = "INSERT INTO presaula(idaul, idusu, jornada, estado, fechin) VALUES (:idaul, :idusu, '1', 'Ocupado', :fechin)";
        $result = $conexion->prepare($sql);
        $idusu = $this->getIdusu();
        $result->bindParam(":idusu", $idusu);
        $idaul = $this->getIdaul();
        $result->bindParam(":idaul", $idaul);
        $fechin = $this->getFechin();
        $result->bindParam(":fechin", $fechin);
        $result->execute();
      } catch (Exception $e) {
        error_log("Error en Mhaul::save: " . $e->getMessage());
      }
    }

    public function edit() {
      try {
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $sql = "UPDATE presaula SET estado=:estado, fechfin=:fechfin WHERE idpres=:idpres";
        $result = $conexion->prepare($sql);
        $idpres = $this->getIdpres();
        $result->bindParam(":idpres", $idpres);
        $estado = $this->getEstado();
        $result->bindParam(":estado", $estado);
        $fechfin = $this->getFechfin();
        $result->bindParam(":fechfin", $fechfin);
        $result->execute();
      } catch (Exception $e) {
        error_log("Error en Mhaul::edit: " . $e->getMessage());
      }
    }

    public function delSol() {
      try {
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $sql = "DELETE FROM presaula WHERE idaul=:idaul AND idusu=:idusu";
        $result = $conexion->prepare($sql);
        $idaul = $this->getIdaul();
        $result->bindParam(":idaul", $idaul);
        $idusu = $this->getIdusu();
        $result->bindParam(":idusu", $idusu);
        $result->execute();
      } catch (Exception $e) {
        error_log("Error en Mhaul::delSol: " . $e->getMessage());
      }
    }

    public function del() {
      try {
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $sql = "DELETE FROM presaula WHERE idpres=:idpres";
        $result = $conexion->prepare($sql);
        $idpres = $this->getIdpres();
        $result->bindParam(":idpres", $idpres);
        $result->execute();
      } catch (Exception $e) {
        error_log("Error en Mhaul::del: " . $e->getMessage());
      }
    }
  }
}

if (!class_exists('Mpraul')) {
  class Mpraul extends Mhaul {}
}

if (!class_exists('Premhaul')) {
  class Premhaul extends Mhaul {}
}