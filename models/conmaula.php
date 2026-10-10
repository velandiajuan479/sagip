<?php
class Maula {
  private $idaul;
  private $nomaul;
  private $piso;
  private $bloqau;
  private $taula;
  private $esaula;
  private $desaula;
  private $tipaul;
  private $idcen;
  private $codubi;

  function getIdaul() { return $this->idaul; }
  function getNomaul() { return $this->nomaul; }
  function getPiso() { return $this->piso; }
  function getBloqau() { return $this->bloqau; }
  function getTaula() { return $this->taula; }
  function getEsaula() { return $this->esaula; }
  function getDesaula() { return $this->desaula; }
  function getTipaul() { return $this->tipaul; }
  function getIdcen() { return $this->idcen; }
  function getCodubi() { return $this->codubi; }

  function setIdaul($idaul) { $this->idaul = $idaul; }
  function setNomaul($nomaul) { $this->nomaul = $nomaul; }
  function setPiso($piso) { $this->piso = $piso; }
  function setBloqau($bloqau) { $this->bloqau = $bloqau; }
  function setTaula($taula) { $this->taula = $taula; }
  function setEsaula($esaula) { $this->esaula = $esaula; }
  function setDesaula($desaula) { $this->desaula = $desaula; }
  function setTipaul($tipaul) { $this->tipaul = $tipaul; }
  function setIdcen($idcen) { $this->idcen = $idcen; }
  function setCodubi($codubi) { $this->codubi = $codubi; }

  private function getSesionCenUbi() {
    $idcen  = isset($_SESSION['idcen'])  ? $_SESSION['idcen']  : '';
    $codubi = isset($_SESSION['codubi']) ? $_SESSION['codubi'] : '';
    return array($idcen, $codubi);
  }

  public function getAllPiso() {
    try {
      list($idcen, $codubi) = $this->getSesionCenUbi();
      $sql = "SELECT DISTINCT piso FROM aula WHERE idcen=:idcen AND codubi=:codubi ORDER BY piso";
      $modelo = new conexion();
      $conexion = $modelo->get_conexion();
      $result = $conexion->prepare($sql);
      $result->bindParam(":idcen", $idcen);
      $result->bindParam(":codubi", $codubi);
      $result->execute();
      return $result->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
      return array();
    }
  }

  public function getPisosByBloque() {
    try {
      list($idcen, $codubi) = $this->getSesionCenUbi();
      $sql = "SELECT DISTINCT piso FROM aula WHERE idcen=:idcen AND codubi=:codubi AND bloqau=:bloqau ORDER BY piso";
      $modelo = new conexion();
      $conexion = $modelo->get_conexion();
      $result = $conexion->prepare($sql);
      $bloqau = $this->getBloqau();
      $result->bindParam(":idcen", $idcen);
      $result->bindParam(":codubi", $codubi);
      $result->bindParam(":bloqau", $bloqau);
      $result->execute();
      return $result->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
      return array();
    }
  }

  public function getAllBloque() {
    try {
      list($idcen, $codubi) = $this->getSesionCenUbi();
      $modelo = new conexion();
      $conexion = $modelo->get_conexion();
      $piso = $this->getPiso();
      if ($piso !== null && $piso !== '') {
        $sql = "SELECT DISTINCT bloqau FROM aula WHERE idcen=:idcen AND codubi=:codubi AND piso=:piso AND bloqau>0 ORDER BY bloqau";
        $result = $conexion->prepare($sql);
        $result->bindParam(":idcen", $idcen);
        $result->bindParam(":codubi", $codubi);
        $result->bindParam(":piso", $piso);
      } else {
        $sql = "SELECT DISTINCT bloqau FROM aula WHERE idcen=:idcen AND codubi=:codubi ORDER BY bloqau";
        $result = $conexion->prepare($sql);
        $result->bindParam(":idcen", $idcen);
        $result->bindParam(":codubi", $codubi);
      }
      $result->execute();
      return $result->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
      return array();
    }
  }

  private function ordenAulas() {
    $bloqau = $this->getBloqau();
    $orden = "";
    if ($bloqau == 6) {
      $orden .= "CASE nomaul WHEN '124' THEN 1 WHEN 'Enfermeria' THEN 2 ELSE 3 END, ";
    }
    $orden .= "CAST(nomaul AS UNSIGNED) ASC, nomaul ASC";
    return $orden;
  }

  public function getAllAula() {
    try {
      list($idcen, $codubi) = $this->getSesionCenUbi();
      $sql = "SELECT DISTINCT idaul, nomaul, piso, bloqau, taula, tipaul, esaula, desaula FROM aula WHERE idcen=:idcen AND codubi=:codubi AND piso=:piso AND bloqau=:bloqau ORDER BY ".$this->ordenAulas();
      $modelo = new conexion();
      $conexion = $modelo->get_conexion();
      $result = $conexion->prepare($sql);
      $piso = $this->getPiso();
      $bloqau = $this->getBloqau();
      $result->bindParam(":idcen", $idcen);
      $result->bindParam(":codubi", $codubi);
      $result->bindParam(":piso", $piso);
      $result->bindParam(":bloqau", $bloqau);
      $result->execute();
      return $result->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
      return array();
    }
  }

  public function getAulasByBloque() {
    try {
      list($idcen, $codubi) = $this->getSesionCenUbi();
      $sql = "SELECT DISTINCT idaul, nomaul, piso, bloqau, taula, tipaul, esaula, desaula FROM aula WHERE idcen=:idcen AND codubi=:codubi AND bloqau=:bloqau ORDER BY piso ASC, ".$this->ordenAulas();
      $modelo = new conexion();
      $conexion = $modelo->get_conexion();
      $result = $conexion->prepare($sql);
      $bloqau = $this->getBloqau();
      $result->bindParam(":idcen", $idcen);
      $result->bindParam(":codubi", $codubi);
      $result->bindParam(":bloqau", $bloqau);
      $result->execute();
      return $result->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
      return array();
    }
  }

  public function getStats() {
    try {
      list($idcen, $codubi) = $this->getSesionCenUbi();
      $sql = "SELECT
              (SELECT COUNT(*) FROM aula WHERE idcen=:idcen AND codubi=:codubi AND bloqau>0) AS totaus,
              (SELECT COUNT(*) FROM aula WHERE idcen=:idcen AND codubi=:codubi AND bloqau>0 AND tipaul=2) AS espeus,
              (SELECT COUNT(*) FROM aula WHERE idcen=:idcen AND codubi=:codubi AND bloqau>0 AND tipaul=1) AS convaus,
              (SELECT COUNT(DISTINCT a.idaul) FROM presaula p INNER JOIN aula a ON p.idaul=a.idaul WHERE a.idcen=:idcen AND a.codubi=:codubi AND p.estado='Ocupado') AS presaus";
      $modelo = new conexion();
      $conexion = $modelo->get_conexion();
      $result = $conexion->prepare($sql);
      $result->bindParam(":idcen", $idcen);
      $result->bindParam(":codubi", $codubi);
      $result->execute();
      return $result->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
      return array();
    }
  }

  public function getRecents() {
    try {
      list($idcen, $codubi) = $this->getSesionCenUbi();
      $sql = "SELECT idaul, nomaul, piso, bloqau, taula, tipaul, COALESCE(esaula,'') AS esaula, COALESCE(desaula,'') AS desaula FROM aula WHERE idcen=:idcen AND codubi=:codubi ORDER BY idaul DESC LIMIT 5";
      $modelo = new conexion();
      $conexion = $modelo->get_conexion();
      $result = $conexion->prepare($sql);
      $result->bindParam(":idcen", $idcen);
      $result->bindParam(":codubi", $codubi);
      $result->execute();
      return $result->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
      return array();
    }
  }

  public function getAreas() {
    try {
      $idcen = isset($_SESSION['idcen']) ? $_SESSION['idcen'] : '';
      $sql = "SELECT idaul, nomaul, piso, bloqau, taula, tipaul, COALESCE(esaula,'') AS esaula, COALESCE(desaula,'') AS desaula FROM aula WHERE idcen=:idcen AND bloqau=0 ORDER BY FIELD(nomaul, 'Auditorio', 'Multiproposito', 'Parrilla'), nomaul ASC";
      $modelo = new conexion();
      $conexion = $modelo->get_conexion();
      $result = $conexion->prepare($sql);
      $result->bindParam(":idcen", $idcen);
      $result->execute();
      return $result->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
      return array();
    }
  }

  public function getOne() {
    try {
      $sql = "SELECT idaul, nomaul, piso, bloqau, taula, esaula, desaula, tipaul, idcen, codubi FROM aula WHERE idaul=:idaul";
      $modelo = new conexion();
      $conexion = $modelo->get_conexion();
      $result = $conexion->prepare($sql);
      $idaul = $this->getIdaul();
      $result->bindParam(":idaul", $idaul);
      $result->execute();
      return $result->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
      return array();
    }
  }

  public function save() {
    try {
      $sql = "INSERT INTO aula (nomaul, piso, bloqau, taula, esaula, desaula, tipaul, idcen, codubi) VALUES (:nomaul, :piso, :bloqau, :taula, :esaula, :desaula, :tipaul, :idcen, :codubi)";
      $modelo = new conexion();
      $conexion = $modelo->get_conexion();
      $result = $conexion->prepare($sql);
      $nomaul = $this->getNomaul();
      $result->bindParam(":nomaul", $nomaul);
      $piso = $this->getPiso();
      $result->bindParam(":piso", $piso);
      $bloqau = $this->getBloqau();
      $result->bindParam(":bloqau", $bloqau); 
      $taula = $this->getTaula();
      $result->bindParam(":taula", $taula);
      $esaula = $this->getEsaula();
      $result->bindParam(":esaula", $esaula);
      $desaula = $this->getDesaula();
      $result->bindParam(":desaula", $desaula);
      $tipaul = $this->getTipaul();
      $result->bindParam(":tipaul", $tipaul);
      $idcen = $this->getIdcen();
      $result->bindParam(":idcen", $idcen);
      $codubi = $this->getCodubi();
      $result->bindParam(":codubi", $codubi);
      $result->execute();
      return true;
    } catch (Throwable $e) {
      return false;
    }
  }

  public function edit() {
    try {
      $sql = "UPDATE aula SET nomaul=:nomaul, piso=:piso, bloqau=:bloqau, taula=:taula, esaula=:esaula, desaula=:desaula, tipaul=:tipaul, idcen=:idcen, codubi=:codubi WHERE idaul=:idaul";
      $modelo = new conexion();
      $conexion = $modelo->get_conexion();
      $result = $conexion->prepare($sql);
      $nomaul = $this->getNomaul();
      $result->bindParam(":nomaul", $nomaul);
      $piso = $this->getPiso();
      $result->bindParam(":piso", $piso);
      $bloqau = $this->getBloqau();
      $result->bindParam(":bloqau", $bloqau); 
      $taula = $this->getTaula();
      $result->bindParam(":taula", $taula);
      $esaula = $this->getEsaula();
      $result->bindParam(":esaula", $esaula);
      $desaula = $this->getDesaula();
      $result->bindParam(":desaula", $desaula);
      $tipaul = $this->getTipaul();
      $result->bindParam(":tipaul", $tipaul);
      $idcen = $this->getIdcen();
      $result->bindParam(":idcen", $idcen);
      $codubi = $this->getCodubi();
      $result->bindParam(":codubi", $codubi);
      $idaul = $this->getIdaul();
      $result->bindParam(":idaul", $idaul); 
      $result->execute();
      return true;
    } catch (Throwable $e) {
      return false;
    }
  }

  public function del() {
    try {
      $sql = "DELETE FROM aula WHERE idaul = :idaul";
      $modelo = new conexion();
      $conexion = $modelo->get_conexion();
      $result = $conexion->prepare($sql);
      $idaul = $this->getIdaul();
      $result->bindParam(":idaul", $idaul);
      $result->execute();
      return true;
    } catch (Throwable $e) {
      return false;
    }
  }
}