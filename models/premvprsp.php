<?php
$connPath = file_exists('models/conexion.php') ? 'models/conexion.php' : (file_exists('conexion.php') ? 'conexion.php' : '../models/conexion.php');
require_once $connPath;

class Premvprsp {
  private $idpresp;
  private $idaul;
  private $idusu;
  private $apresp;
  private $whoapr;
  private $fecini;
  private $fecfin;
  private $horini;
  private $horfin;
  private $incpre;
  private $ndocusu;

  function getIdpresp() { return $this->idpresp; }
  function getIdaul() { return $this->idaul; }
  function getIdusu() { return $this->idusu; }
  function getApresp() { return $this->apresp; }
  function getWhoapr() { return $this->whoapr; }
  function getFecini() { return $this->fecini; }
  function getFecfin() { return $this->fecfin; }
  function getHorini() { return $this->horini; }
  function getHorfin() { return $this->horfin; }
  function getIncpre() { return $this->incpre; }
  function getNdocusu() { return $this->ndocusu; }

  function setIdpresp($idpresp) { $this->idpresp = $idpresp; }
  function setIdaul($idaul) { $this->idaul = $idaul; }
  function setIdusu($idusu) { $this->idusu = $idusu; }
  function setApresp($apresp) { $this->apresp = $apresp; }
  function setWhoapr($whoapr) { $this->whoapr = $whoapr; }
  function setFecini($fecini) { $this->fecini = $fecini; }
  function setFecfin($fecfin) { $this->fecfin = $fecfin; }
  function setHorini($horini) { $this->horini = $horini; }
  function setHorfin($horfin) { $this->horfin = $horfin; }
  function setIncpre($incpre) { $this->incpre = $incpre; }
  function setNdocusu($ndocusu) { $this->ndocusu = $ndocusu; }

  public function getConteos() {
    try {
      $res = NULL;
      $modelo = new Conexion();
      $conexion = $modelo->get_conexion();
      $idcen = isset($_SESSION['idcen']) ? " AND a.idcen=".(int)$_SESSION['idcen'] : "";
      $sql = "SELECT
                (SELECT COUNT(*) FROM aula AS a WHERE a.tipaul=2".$idcen.") AS aulas,
                (SELECT COUNT(*) FROM presesp WHERE apresp=2) AS pendientes,
                (SELECT COUNT(*) FROM presesp WHERE apresp=1) AS aprobadas,
                (SELECT COUNT(*) FROM presesp WHERE apresp=0) AS rechazadas";
      $result = $conexion->prepare($sql);
      $result->execute();
      $res = $result->fetchAll(PDO::FETCH_ASSOC);
      return $res;
    } catch (Exception $e) {
      echo "Error: ".$e;
    }
  }

  public function getUsuarioByDoc() {
    try {
      $res = NULL;
      $modelo = new Conexion();
      $conexion = $modelo->get_conexion();
      $sql = "SELECT idusu, nomusu, ndocusu, idper FROM usuario WHERE ndocusu=:ndocusu AND actusu=1 LIMIT 1";
      $result = $conexion->prepare($sql);
      $ndocusu = $this->getNdocusu();
      $result->bindParam(":ndocusu", $ndocusu);
      $result->execute();
      $res = $result->fetchAll(PDO::FETCH_ASSOC);
      return $res;
    } catch (Exception $e) {
      echo "Error: ".$e;
    }
  }

  public function getAll() {
    try {
      $res = NULL;
      $modelo = new Conexion();
      $conexion = $modelo->get_conexion();
      $sql = "SELECT pr.idpresp, pr.idaul, a.nomaul, a.piso, a.bloqau, a.desaula, pr.idusu, u.ndocusu, u.nomusu, u.idper, pr.apresp, pr.whoapr, uw.ndocusu, uw.nomusu, uw.idper, pr.fecini, pr.fecfin, pr.horini, pr.horfin, pr.incpre FROM presesp AS pr INNER JOIN usuario AS u ON pr.idusu=u.idusu LEFT JOIN usuario AS uw ON pr.whoapr=uw.idusu INNER JOIN aula AS a ON pr.idaul=a.idaul";
      $result = $conexion->prepare($sql);
      $result->execute();
      $res = $result->fetchAll(PDO::FETCH_ASSOC);
      return $res;
    } catch (Exception $e) {
      echo "Error: ".$e;
    }
  }

  public function getOne() {
    try {
      $res = NULL;
      $modelo = new Conexion();
      $conexion = $modelo->get_conexion();
      $sql = "SELECT pr.idpresp, pr.idaul, a.nomaul, a.piso, a.bloqau, a.desaula, pr.idusu, u.ndocusu, u.nomusu, u.idper, pr.apresp, pr.whoapr, uw.ndocusu, uw.nomusu, uw.idper, pr.fecini, pr.fecfin, pr.horini, pr.horfin, pr.incpre FROM presesp AS pr INNER JOIN usuario AS u ON pr.idusu=u.idusu LEFT JOIN usuario AS uw ON pr.whoapr=uw.idusu INNER JOIN aula AS a ON pr.idaul=a.idaul WHERE pr.idpresp=:idpresp";
      $result = $conexion->prepare($sql);
      $idpresp = $this->getIdpresp();
      $result->bindParam(':idpresp', $idpresp);
      $result->execute();
      $res = $result->fetchAll(PDO::FETCH_ASSOC);
      return $res;
    } catch (Exception $e) {
      echo "Error: ".$e;
    }
  }

  public function getAulEsp() {
    try {
      $res = NULL;
      $modelo = new Conexion();
      $conexion = $modelo->get_conexion();
      $idcen = (!empty($_SESSION['idcen'])) ? " WHERE idcen=" . (int)$_SESSION['idcen'] . " AND tipaul=2" : " WHERE tipaul=2";
      $sql = "SELECT DISTINCT idaul, nomaul, piso, bloqau, taula, esaula, desaula, tipaul FROM aula" . $idcen;
      $result = $conexion->prepare($sql);
      $result->execute();
      $res = $result->fetchAll(PDO::FETCH_ASSOC);
      return $res;
    } catch (Exception $e) {
      echo "Error: ".$e;
    }
  }

  public function getSpaceSolicited() {
    try {
      $res = NULL;
      $modelo = new Conexion();
      $conexion = $modelo->get_conexion();
      $dateCurrent = new DateTime();
      $dateCurrent = $dateCurrent->format('Y-m-d');
      $sql = "SELECT pr.idpresp, pr.idaul, pr.idusu, u.nomusu, u.ndocusu, pr.fecfin, pr.horini, pr.horfin, u.idper, p.nomper, pr.apresp FROM presesp AS pr INNER JOIN usuario AS u ON pr.idusu=u.idusu INNER JOIN perfil AS p ON u.idper=p.idper WHERE pr.idaul=:idaul AND pr.fecfin >= :dateCurrent AND pr.apresp=2";
      $result = $conexion->prepare($sql);
      $idaul = $this->getIdaul();
      $result->bindParam(':idaul', $idaul);
      $result->bindParam(':dateCurrent', $dateCurrent);
      $result->execute();
      $res = $result->fetchAll(PDO::FETCH_ASSOC);
      return $res;
    } catch (Exception $e) {
      echo 'Error: '.$e;
    }
  }

  public function usuSolicited() {
    try {
      $res = NULL;
      $modelo = new Conexion();
      $conexion = $modelo->get_conexion();
      $dateCurrent = new DateTime();
      $dateCurrent = $dateCurrent->format('Y-m-d');
      $sql = "SELECT idpresp, apresp, idusu, apresp, fecfin, horini, horfin FROM presesp WHERE idaul=:idaul AND idusu=:idusu AND fecfin >= :dateCurrent AND apresp!=0";
      $result = $conexion->prepare($sql);
      $idaul = $this->getIdaul();
      $result->bindParam(':idaul', $idaul);
      $idusu = $this->getIdusu();
      $result->bindParam(':idusu', $idusu);
      $result->bindParam(':dateCurrent', $dateCurrent);
      $result->execute();
      $res = $result->fetchAll(PDO::FETCH_ASSOC);
      return $res;
    } catch (Exception $e) {
      echo 'Error: '.$e;
    }
  }

  public function eventOccuped() {
    try {
      $res = NULL;
      $modelo = new Conexion();
      $conexion = $modelo->get_conexion();
      $sql = "SELECT pr.idpresp, pr.idusu, u.nomusu AS unomusu, u.ndocusu AS undocusu, pr.idaul, a.nomaul, a.piso, a.bloqau, pr.fecini, pr.fecfin, pr.horini, pr.horfin, pr.apresp, pr.whoapr, w.nomusu, w.ndocusu FROM presesp AS pr INNER JOIN usuario AS u ON pr.idusu=u.idusu INNER JOIN aula AS a ON pr.idaul=a.idaul INNER JOIN usuario AS w ON pr.whoapr=w.idusu WHERE pr.apresp=1 AND pr.fecfin=:fecfin AND pr.idaul=:idaul ORDER BY pr.horini";
      $result = $conexion->prepare($sql);
      $fecfin = $this->getFecfin();
      $result->bindParam(':fecfin', $fecfin);
      $idaul = $this->getIdaul();
      $result->bindParam(':idaul', $idaul);
      $result->execute();
      $res = $result->fetchAll(PDO::FETCH_ASSOC);
      return $res;
    } catch (Exception $e) {
      echo 'Error: '.$e;
    }
  }

  public function getSolsAul() {
    try {
      $res = NULL;
      $modelo = new Conexion();
      $conexion = $modelo->get_conexion();
      $sql = "SELECT pr.idpresp, pr.idaul, a.nomaul, a.piso, a.bloqau, a.desaula, pr.idusu, u.ndocusu AS undocusu, u.nomusu AS unomusu, u.idper, pr.apresp, pr.whoapr, uw.ndocusu, uw.nomusu, uw.idper, pr.fecini, pr.fecfin, pr.horini, pr.horfin, pr.incpre FROM presesp AS pr INNER JOIN usuario AS u ON pr.idusu=u.idusu LEFT JOIN usuario AS uw ON pr.whoapr=uw.idusu INNER JOIN aula AS a ON pr.idaul=a.idaul WHERE pr.idaul=:idaul AND pr.apresp=1 AND pr.incpre=1";
      $result = $conexion->prepare($sql);
      $idaul = $this->getIdaul();
      $result->bindParam(':idaul', $idaul);
      $result->execute();
      $res = $result->fetchAll(PDO::FETCH_ASSOC);
      return $res;
    } catch (Exception $e) {
      echo "Error: ".$e;
    }
  }

  public function verOtherSols() {
    try {
      $res = NULL;
      $modelo = new Conexion();
      $conexion = $modelo->get_conexion();
      $sql = "SELECT idpresp, idusu FROM presesp WHERE fecfin=:fecfin AND horini BETWEEN :horini AND :horfin AND apresp=1 AND idaul=:idaul";
      $result = $conexion->prepare($sql);
      $fecfin = $this->getFecfin();
      $result->bindParam(':fecfin', $fecfin);
      $horini = $this->getHorini();
      $result->bindParam(':horini', $horini);
      $horfin = $this->getHorfin();
      $result->bindParam(':horfin', $horfin);
      $idaul = $this->getIdaul();
      $result->bindParam(':idaul', $idaul);
      $result->execute();
      $res = $result->fetchAll(PDO::FETCH_ASSOC);
      return $res;
    } catch (Exception $e) {
      echo "Error: ".$e;
    }
  }

  public function searchOthersSols() {
    try {
      $res = NULL;
      $modelo = new Conexion();
      $conexion = $modelo->get_conexion();
      $sql = "SELECT idpresp, idusu FROM presesp WHERE fecfin=:fecfin AND horini BETWEEN :horini AND :horfin AND apresp=2 AND idaul=:idaul AND idusu!=:idusu";
      $result = $conexion->prepare($sql);
      $fecfin = $this->getFecfin();
      $result->bindParam(':fecfin', $fecfin);
      $horini = $this->getHorini();
      $result->bindParam(':horini', $horini);
      $horfin = $this->getHorfin();
      $result->bindParam(':horfin', $horfin);
      $idaul = $this->getIdaul();
      $result->bindParam(':idaul', $idaul);
      $idusu = $this->getIdusu();
      $result->bindParam(':idusu', $idusu);
      $result->execute();
      $res = $result->fetchAll(PDO::FETCH_ASSOC);
      return $res;
    } catch (Exception $e) {
      echo "Error: ".$e;
    }
  }

  public function save() {
    try {
      $modelo = new Conexion();
      $conexion = $modelo->get_conexion();
      $sql = "INSERT INTO presesp(idpresp, idaul, idusu, apresp, fecini, fecfin, horini, horfin, incpre) VALUES ((SELECT COALESCE(MAX(idpresp),0)+1 FROM (SELECT idpresp FROM presesp) AS ndp), :idaul, :idusu, 2, :fecini, :fecfin, :horini, :horfin, :incpre)";
      $result = $conexion->prepare($sql);
      $idaul = $this->getIdaul();
      $result->bindParam(':idaul', $idaul);
      $idusu = $this->getIdusu();
      $result->bindParam(':idusu', $idusu);
      $fecini = $this->getFecini();
      $result->bindParam(':fecini', $fecini);
      $fecfin = $this->getFecfin();
      $result->bindParam(':fecfin', $fecfin);
      $horini = $this->getHorini();
      $result->bindParam(':horini', $horini);
      $horfin = $this->getHorfin();
      $result->bindParam(':horfin', $horfin);
      $incpre = $this->getIncpre();
      $result->bindParam(':incpre', $incpre);
      $result->execute();
    } catch (Exception $e) {
      echo "Error: ".$e;
    }
  }

  public function editApro() {
    try {
      $modelo = new Conexion();
      $conexion = $modelo->get_conexion();
      $sql = "UPDATE presesp SET apresp=:apresp, whoapr=:whoapr WHERE idusu=:idusu AND idaul=:idaul AND fecfin=:fecfin";
      $result = $conexion->prepare($sql);
      $idusu = $this->getIdusu();
      $result->bindParam(':idusu', $idusu);
      $idaul = $this->getIdaul();
      $result->bindParam(':idaul', $idaul);
      $fecfin = $this->getFecfin();
      $result->bindParam(':fecfin', $fecfin);
      $whoapr = $this->getWhoapr();
      $result->bindParam(':whoapr', $whoapr);
      $apresp = $this->getApresp();
      $result->bindParam(':apresp', $apresp);
      $result->execute();
    } catch (Exception $e) {
      echo "Error: ".$e;
    }
  }

  public function editAprEvents() {
    try {
      $modelo = new Conexion();
      $conexion = $modelo->get_conexion();
      $sql = "UPDATE presesp SET apresp=:apresp, whoapr=:whoapr WHERE idaul=:idaul AND idusu!=:idusu AND fecfin=:fecfin AND horini BETWEEN :horini AND :horfin";
      $result = $conexion->prepare($sql);
      $apresp = $this->getApresp();
      $result->bindParam(':apresp', $apresp);
      $whoapr = $this->getWhoapr();
      $result->bindParam(':whoapr', $whoapr);
      $idaul = $this->getIdaul();
      $result->bindParam(':idaul', $idaul);
      $idusu = $this->getIdusu();
      $result->bindParam(':idusu', $idusu);
      $fecfin = $this->getFecfin();
      $result->bindParam(':fecfin', $fecfin);
      $horini = $this->getHorini();
      $result->bindParam(':horini', $horini);
      $horfin = $this->getHorfin();
      $result->bindParam(':horfin', $horfin);
      $result->execute();
    } catch (Exception $e) {
      echo "Error: ".$e;
    }
  }

  public function editIncpre() {
    try {
      $modelo = new Conexion();
      $conexion = $modelo->get_conexion();
      $sql = "UPDATE presesp SET incpre=:incpre, apresp=:apresp, whoapr=:whoapr WHERE idpresp=:idpresp";
      $result = $conexion->prepare($sql);
      $idpresp = $this->getIdpresp();
      $result->bindParam(':idpresp', $idpresp);
      $incpre = $this->getIncpre();
      $result->bindParam(':incpre', $incpre);
      $apresp = $this->getApresp();
      $result->bindParam(':apresp', $apresp);
      $whoapr = $this->getWhoapr();
      $result->bindParam(':whoapr', $whoapr);
      $result->execute();
    } catch (Exception $e) {
      echo "Error: ".$e;
    }
  }

  public function edit() {
    try {
      $modelo = new Conexion();
      $conexion = $modelo->get_conexion();
      $sql = "UPDATE presesp SET fecfin=:fecfin, horini=:horini, horfin=:horfin WHERE idpresp=:idpresp";
      $result = $conexion->prepare($sql);
      $idpresp = $this->getIdpresp();
      $result->bindParam(':idpresp', $idpresp);
      $fecfin = $this->getFecfin();
      $result->bindParam(':fecfin', $fecfin);
      $horini = $this->getHorini();
      $result->bindParam(':horini', $horini);
      $horfin = $this->getHorfin();
      $result->bindParam(':horfin', $horfin);
      $result->execute();
    } catch (Exception $e) {
      echo "Error: ".$e;
    }
  }

  public function delSol() {
    try {
      $modelo = new Conexion();
      $conexion = $modelo->get_conexion();
      $sql = "DELETE FROM presesp WHERE idusu=:idusu AND idaul=:idaul";
      $result = $conexion->prepare($sql);
      $idaul = $this->getIdaul();
      $result->bindParam(':idaul', $idaul);
      $idusu = $this->getIdusu();
      $result->bindParam(':idusu', $idusu);
      $result->execute();
    } catch (Exception $e) {
      echo "Error: ".$e;
    }
  }

  public function del() {
    try {
      $modelo = new Conexion();
      $conexion = $modelo->get_conexion();
      $sql = "DELETE FROM presesp WHERE idpresp=:idpresp";
      $result = $conexion->prepare($sql);
      $idpresp = $this->getIdpresp();
      $result->bindParam(':idpresp', $idpresp);
      $result->execute();
    } catch (Exception $e) {
      echo "Error: ".$e;
    }
  }
}
?>