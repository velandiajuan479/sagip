<?php
$connPath = file_exists('models/conexion.php') ? 'models/conexion.php' : (file_exists('conexion.php') ? 'conexion.php' : '../models/conexion.php');
require_once $connPath;

if (!function_exists('ManejoError')) {
  function ManejoError($e) {
    error_log("Error BD: " . $e->getMessage());
  }
}

Class Prempam{
  private $idusu;
  private $idval;
  private $nomusu;
  private $parval;
  private $nomval; 
  private $fhlle;
  private $fechos;
  private $act;
  private $ndocusu;

  function getIdusu() { return $this->idusu; }
  function getIdval() { return $this->idval; }
  function getNomusu() { return $this->nomusu; }
  function getParval() { return $this->parval; }
  function getNomval() { return $this->nomval; }
  function getFhlle() { return $this->fhlle; }
  function getFechos() { return $this->fechos; }
  function getAct() { return $this->act; }
  function getNdocusu() { return $this->ndocusu; }

  function setIdusu($idusu) { $this->idusu = $idusu; }
  function setIdval($idval) { $this->idval = $idval; }
  function setNomusu($nomusu) { $this->nomusu = $nomusu; }
  function setParval($parval) { $this->parval = $parval; }
  function setNomval($nomval) { $this->nomval = $nomval; }
  function setFhlle($fhlle) { $this->fhlle = $fhlle; }
  function setFechos($fechos) { $this->fechos = $fechos; }
  function setAct($act) { $this->act = $act; }
  function setNdocusu($ndocusu) { $this->ndocusu = $ndocusu; }

  public function getTodoA() {
    try {
      $res = NULL;
      $modelo = new conexion();
      $conexion = $modelo->get_conexion();
      $sql = "SELECT m.nummin, m.idusu, m.fechos, m.tipmin, m.hij, m.fhlle, m.obs, m.ideles, v.iddom, v.parval, v.nomval, v.novam, u.nomusu, u.ndocusu FROM minuta AS m INNER JOIN valor AS v ON v.idval=m.ideles INNER JOIN usuario AS u ON u.idusu=m.idusu WHERE v.iddom=13;";
      $result = $conexion->prepare($sql);
      $result->execute();
      $res = $result->fetchAll(PDO::FETCH_ASSOC);
      return $res;
    } catch (Exception $e) {
      echo 'Error: '.$e->getMessage();
    }
}

  public function getOne() {
    try {
      $res = NULL;
      $modelo = new conexion();
      $conexion = $modelo->get_conexion();
      $sql = "SELECT idval, nomval, iddom, parval, act, nhora, novam FROM valor WHERE idval=:idval";
      $result = $conexion->prepare($sql);
      $idval = $this->getIdval();
      $result->bindParam(":idval", $idval);
      $result->execute();
      $res = $result->fetchAll(PDO::FETCH_ASSOC);
      return $res;
    } catch (Exception $e) {
      echo 'Error: '.$e->getMessage();
    }
  }

  public function selOneAmb($idval) {
    try {
      $res = NULL;
      $modelo = new conexion();
      $conexion = $modelo->get_conexion();
      $sql = "SELECT idval, nomval, iddom, parval, act, nhora, novam FROM valor WHERE idval=:idval";
      $result = $conexion->prepare($sql);
      $result->bindParam(":idval", $idval);
      $result->execute();
      $res = $result->fetchAll(PDO::FETCH_ASSOC);
      return $res;
    } catch (Exception $e) {
      echo 'Error: '.$e->getMessage();
    }
  }

  public function getOneDom() {
    try {
      $res = NULL;
      $modelo = new conexion();
      $conexion = $modelo->get_conexion();
      $sql = "SELECT idval, nomval, iddom, parval, act, nhora, novam FROM valor WHERE iddom=13 AND act=1";
      $result = $conexion->prepare($sql);
      $result->execute();
      $res = $result->fetchAll(PDO::FETCH_ASSOC);
      return $res;
    } catch (Exception $e) {
      echo 'Error: '.$e->getMessage();
    }
  }

  public function getConteos() {
    try {
      $res = NULL;
      $modelo = new conexion();
      $conexion = $modelo->get_conexion();
      $sql = "SELECT
                (SELECT COUNT(*) FROM valor WHERE iddom=13) AS total,
                (SELECT COUNT(*) FROM valor WHERE iddom=13 AND act=1) AS disponibles,
                (SELECT COUNT(*) FROM valor WHERE iddom=13 AND act=2) AS apartados,
                (SELECT COUNT(*) FROM minuta AS m INNER JOIN valor AS v ON v.idval=m.ideles WHERE v.iddom=13) AS movimientos";
      $result = $conexion->prepare($sql);
      $result->execute();
      $res = $result->fetchAll(PDO::FETCH_ASSOC);
      return $res;
    } catch (Exception $e) {
      echo 'Error: '.$e->getMessage();
    }
  }

  public function getUsuarioByDoc() {
    try {
      $res = NULL;
      $modelo = new conexion();
      $conexion = $modelo->get_conexion();
      $sql = "SELECT idusu, nomusu, ndocusu FROM usuario WHERE ndocusu=:ndocusu AND actusu=1 LIMIT 1";
      $result = $conexion->prepare($sql);
      $ndocusu = $this->getNdocusu();
      $result->bindParam(":ndocusu", $ndocusu);
      $result->execute();
      $res = $result->fetchAll(PDO::FETCH_ASSOC);
      return $res;
    } catch (Exception $e) {
      echo 'Error: '.$e->getMessage();
    }
  }

  public function getNextNummin() {
    try {
      $res = NULL;
      $modelo = new conexion();
      $conexion = $modelo->get_conexion();
      $sql = "SELECT COALESCE(MAX(nummin),0)+1 AS sig FROM minuta";
      $result = $conexion->prepare($sql);
      $result->execute();
      $res = $result->fetch(PDO::FETCH_ASSOC);
      return $res['sig'];
    } catch (Exception $e) {
      echo 'Error: '.$e->getMessage();
    }
  }

  public function save() {
    try{
      $sql = "UPDATE valor SET act=:act WHERE idval=:idval";
      $modelo = new conexion();
      $conexion = $modelo->get_conexion();    
      $result = $conexion->prepare($sql);
      $idval = $this->getIdval();
      $result->bindParam(":idval", $idval);
      $act = $this->getAct();
      $result->bindParam(":act", $act);
      $result->execute();
    } catch(Exception $e) {
      ManejoError($e);
    }
  }

  public function saveMinuta($idusu, $idval, $fechos, $tipmin = 'N') {
    try {
      $modelo = new conexion();
      $conexion = $modelo->get_conexion();
      $nummin = $this->getNextNummin();
      $sql = "INSERT INTO minuta (nummin, idusu, fechos, tipmin, hij, fhlle, obs, ideles) 
              VALUES (:nummin, :idusu, :fechos, :tipmin, 0, NULL, NULL, :ideles)";
      $stmt = $conexion->prepare($sql);
      $stmt->bindParam(':nummin', $nummin);
      $stmt->bindParam(':idusu', $idusu);
      $stmt->bindParam(':fechos', $fechos);
      $stmt->bindParam(':tipmin', $tipmin);
      $stmt->bindParam(':ideles', $idval);
      $stmt->execute();
      return $nummin;
    } catch (Exception $e) {
      echo 'Error: ' . $e->getMessage();
    }
  }

  public function updMinuta($nummin, $fhlle, $tipmin = 'A') {
    try {
      $modelo = new conexion();
      $conexion = $modelo->get_conexion();
      $sql = "UPDATE minuta SET tipmin=:tipmin, fhlle=:fhlle, hij=1 WHERE nummin=:nummin";
      $stmt = $conexion->prepare($sql);
      $stmt->bindParam(':tipmin', $tipmin);
      $stmt->bindParam(':fhlle', $fhlle);
      $stmt->bindParam(':nummin', $nummin);
      $stmt->execute();
    } catch (Exception $e) {
      echo 'Error: ' . $e->getMessage();
    }
  }

  public function edit() {
    try {
      $modelo = new conexion();
      $conexion = $modelo->get_conexion();
      $sql = "UPDATE valor SET nomval=:nomval WHERE idval=:idval";
      $stmt = $conexion->prepare($sql);
      $nomval = $this->getNomval();
      $stmt->bindParam(':nomval', $nomval);
      $idval = $this->getIdval();
      $stmt->bindParam(':idval', $idval);
      $stmt->execute();
    } catch (Exception $e) {
      echo 'Error: ' . $e->getMessage();
    }
  }
}

if (!class_exists('Mmin')) {
  class Mmin extends Prempam {}
}

if (!class_exists('Mval')) {
  class Mval extends Prempam {}
}
?>