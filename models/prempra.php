<?php
$connPath = file_exists('models/conexion.php') ? 'models/conexion.php' : (file_exists('conexion.php') ? 'conexion.php' : '../models/conexion.php');
require_once $connPath;

class Prempra {
  private $idpres;
  private $idaul;
  private $idusu;
  private $jornada;
  private $estado;
  private $fechin;
  private $fechfin;
  private $piso;
  private $bloqau;

  function getIdpres() { return $this->idpres; }
  function getIdaul() { return $this->idaul; }
  function getIdusu() { return $this->idusu; }
  function getJornada() { return $this->jornada; }
  function getEstado() { return $this->estado; }
  function getFechin() { return $this->fechin; }
  function getFechfin() { return $this->fechfin; }
  function getPiso() { return $this->piso; }
  function getBloqau() { return $this->bloqau; }

  function setIdpres($idpres) { $this->idpres = $idpres; }
  function setIdaul($idaul) { $this->idaul = $idaul; }
  function setIdusu($idusu) { $this->idusu = $idusu; }
  function setJornada($jornada) { $this->jornada = $jornada; }
  function setEstado($estado) { $this->estado = $estado; }
  function setFechin($fechin) { $this->fechin = $fechin; }
  function setFechfin($fechfin) { $this->fechfin = $fechfin; }
  function setPiso($piso) { $this->piso = $piso; }
  function setBloqau($bloqau) { $this->bloqau = $bloqau; }

  public function getAll($fechin, $fechfin, $idusu, $idaul) {
    try {
      $res = NULL;
      $modelo = new conexion();
      $conexion = $modelo->get_conexion();
      $sql = "SELECT p.idpres, p.idaul, a.nomaul, p.idusu, u.ndocusu, u.nomusu, p.jornada, p.estado, p.fechin, p.fechfin FROM presaula AS p INNER JOIN usuario AS u ON p.idusu = u.idusu INNER JOIN aula AS a ON p.idaul=a.idaul WHERE 1";
      if ($fechin && $fechfin) $sql .= " AND p.fechin BETWEEN :fin AND :ffi";
      else if ($fechin) $sql .= " AND p.fechin>=:fin";
      else if ($fechfin) $sql .= " AND p.fechfin<=:ffi";
      if ($idusu) $sql .= " AND u.idusu=:idusu";
      if ($idaul) $sql .= " AND p.idaul=:idaul";
      $result = $conexion->prepare($sql);
      if ($fechin) {
        $fin = $fechin.' 00:00:00';
        $result->bindParam(':fin', $fin);
      }
      if ($fechfin) {
        $ffi = $fechfin.' 23:59:00';
        $result->bindParam(':ffi', $ffi);
      }
      if ($idusu) $result->bindParam(':idusu', $idusu[0]['idusu']);
      if ($idaul) $result->bindParam(':idaul', $idaul);
      $result->execute();
      $res = $result->fetchAll(PDO::FETCH_ASSOC);
      return $res;
    } catch (Exception $e) {
      echo "Error: $e";
    }
  }

  public function getOne() {
    try {
      $res = NULL;
      $modelo = new Conexion();
      $conexion = $modelo->get_conexion();
      $sql = "SELECT m.idpres, m.idusu, m.idaul, m.jornada, m.fechin, m.fechfin FROM presaula AS m INNER JOIN usuario AS u ON m.idusu=u.idusu WHERE m.idpres=:idpres";
      $result = $conexion->prepare($sql);
      $idpres = $this->getIdpres();
      $result->bindParam(':idpres', $idpres);
      $result->execute();
      $res = $result->fetchall(PDO::FETCH_ASSOC);
      return $res;
    } catch (Exception $e) {
      echo "Error: $e";
    }
  }

  public function getUser($documentNumber) {
    try {
      $res = NULL;
      $modelo = new Conexion();
      $conexion = $modelo->get_conexion();
      $sql = "SELECT idusu, nomusu, ndocusu FROM usuario WHERE ndocusu=:ndocusu";
      $result = $conexion->prepare($sql);
      $result->bindParam(':ndocusu', $documentNumber);
      $result->execute();
      $res = $result->fetchall(PDO::FETCH_ASSOC);
      return $res;
    } catch (Exception $e) {
      echo "Error: $e";
    }
  }

  public function getConteos() {
    try {
      $modelo = new Conexion();
      $conexion = $modelo->get_conexion();
      $filter = "";
      if (!empty($_SESSION['idcen'])) {
        $filter .= " AND idcen=" . (int)$_SESSION['idcen'];
      }
      if (!empty($_SESSION['codubi'])) {
        $filter .= " AND codubi=" . (int)$_SESSION['codubi'];
      }
      $sql = "SELECT
                (SELECT COUNT(*) FROM aula WHERE 1" . $filter . ") AS total,
                (SELECT COUNT(*) FROM presaula p INNER JOIN aula a ON p.idaul=a.idaul WHERE p.estado='Ocupado'" . str_replace(" AND ", " AND a.", $filter) . ") AS ocupadas,
                (SELECT COUNT(*) FROM presaula p INNER JOIN aula a ON p.idaul=a.idaul WHERE p.estado<>'Ocupado'" . str_replace(" AND ", " AND a.", $filter) . ") AS liberadas,
                (SELECT COUNT(*) FROM presaula p INNER JOIN aula a ON p.idaul=a.idaul WHERE DATE(p.fechin)=CURDATE()" . str_replace(" AND ", " AND a.", $filter) . ") AS hoy";
      $result = $conexion->prepare($sql);
      $result->execute();
      $res = $result->fetch(PDO::FETCH_ASSOC);
      return $res ?: ['total' => 0, 'ocupadas' => 0, 'liberadas' => 0, 'hoy' => 0];
    } catch (Exception $e) {
      return ['total' => 0, 'ocupadas' => 0, 'liberadas' => 0, 'hoy' => 0];
    }
  }

  public function getAllPiso() {
    try {
      $modelo = new Conexion();
      $conexion = $modelo->get_conexion();
      $sql = "SELECT DISTINCT piso FROM aula WHERE tipaul=1 AND piso IS NOT NULL AND piso != '' ORDER BY piso ASC";
      $result = $conexion->prepare($sql);
      $result->execute();
      return $result->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
      return [];
    }
  }

  public function getAllBloque() {
    try {
      $modelo = new Conexion();
      $conexion = $modelo->get_conexion();
      $sql = "SELECT DISTINCT bloqau FROM aula WHERE tipaul=1 AND bloqau IS NOT NULL";
      if ($this->piso !== null) {
        $sql .= " AND piso=:piso";
      }
      $sql .= " ORDER BY bloqau ASC";
      $result = $conexion->prepare($sql);
      if ($this->piso !== null) {
        $piso = $this->getPiso();
        $result->bindParam(':piso', $piso);
      }
      $result->execute();
      return $result->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
      return [];
    }
  }

  public function getAllAula() {
    try {
      $modelo = new Conexion();
      $conexion = $modelo->get_conexion();
      $sql = "SELECT idaul, nomaul, piso, bloqau, taula FROM aula WHERE tipaul=1";
      if ($this->piso !== null) {
        $sql .= " AND piso=:piso";
      }
      if ($this->bloqau !== null) {
        $sql .= " AND bloqau=:bloqau";
      }
      $sql .= " ORDER BY nomaul ASC";
      $result = $conexion->prepare($sql);
      if ($this->piso !== null) {
        $piso = $this->getPiso();
        $result->bindParam(':piso', $piso);
      }
      if ($this->bloqau !== null) {
        $bloqau = $this->getBloqau();
        $result->bindParam(':bloqau', $bloqau);
      }
      $result->execute();
      return $result->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
      return [];
    }
  }

  public function getAulas() {
    try {
      $res = NULL;
      $modelo = new Conexion();
      $conexion = $modelo->get_conexion();
      $sql = "SELECT idaul, nomaul FROM aula";
      $result = $conexion->prepare($sql);
      $result->execute();
      return $result->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
      echo "Error: $e";
    }
  }

  public function getAulOcu() {
    try {
      $res = NULL;
      $modelo = new conexion();
      $conexion = $modelo->get_conexion();
      $sql = "SELECT p.idpres, p.idaul, p.idusu, u.ndocusu, u.nomusu, u.emausu, u.fotcan, u.telcan, p.jornada, p.estado, p.fechin, p.fechfin FROM presaula AS p INNER JOIN usuario AS u ON p.idusu=u.idusu WHERE p.estado='Ocupado' AND p.idaul=:idaul";
      $result = $conexion->prepare($sql);
      $idaul = $this->getIdaul();
      $result->bindParam(':idaul', $idaul);
      $result->execute();
      $res = $result->fetchall(PDO::FETCH_ASSOC);
      return $res;
    } catch (Exception $e) {
      echo "Error: $e";
    }
  }

  public function save() {
    try {
      $modelo = new Conexion();
      $conexion = $modelo->get_conexion();
      $sql = "INSERT INTO presaula(idpres, idaul, idusu, jornada, estado, fechin, fechfin) VALUES ((SELECT COALESCE(MAX(idpres),0)+1 FROM (SELECT idpres FROM presaula) AS tmp), :idaul, :idusu, :jornada, :estado, :fechin, :fechfin)";
      $result = $conexion->prepare($sql);
      $idusu = $this->getIdusu();
      $result->bindParam(":idusu", $idusu);
      $idaul = $this->getIdaul();
      $result->bindParam(":idaul", $idaul);
      $jornada = $this->getJornada() ? $this->getJornada() : '1';
      $result->bindParam(":jornada", $jornada);
      $estado = $this->getEstado() ? $this->getEstado() : 'Ocupado';
      $result->bindParam(":estado", $estado);
      $fechin = $this->getFechin();
      $result->bindParam(":fechin", $fechin);
      $fechfin = $this->getFechfin();
      if (!$fechfin) $fechfin = '0000-00-00 00:00:00';
      $result->bindParam(":fechfin", $fechfin);
      $result->execute();
    } catch (Exception $e) {
      echo "Error: $e";
    }
  }

  public function edit() {
    try {
      $modelo = new Conexion();
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
      echo "Error: $e";
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
      echo "Error: $e";
    }
  }

  public function del() {
    try {
      $modelo = new conexion();
      $conexion = $modelo->get_conexion();
      $sql = "DELETE FROM presaula WHERE idpres=:idpres";
      $result = $conexion->prepare($sql);
      $idpres=$this->getIdpres();
      $result->bindParam(":idpres",$idpres);
      $result->execute();
    } catch (Exception $e) {
      echo "Error: $e";
    }
  }
}

if (!class_exists('Maula')) {
  class Maula extends Prempra {}
}

if (!class_exists('Musu')) {
  class Musu extends Prempra {}
}
?>