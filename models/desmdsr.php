<?php
class Desmdsr {
  private $idusu;
  private $idfic;

  function getIdusu() { return $this->idusu; }
  function getIdfic() { return $this->idfic; }

  function setIdusu($idusu) { $this->idusu = $idusu; }
  function setIdfic($idfic) { $this->idfic = $idfic; }

  public function getFicha() {
    $sql = "SELECT f.idfic, f.nomfic, v.nomval FROM ficha AS f INNER JOIN valor AS v ON f.jornada=v.idval WHERE f.idfic>1000 ORDER BY f.idfic, f.nomfic";
    $modelo = new conexion();
    $conexion = $modelo->get_conexion();
    $result = $conexion->prepare($sql);
    $result->execute();
    $res = $result->fetchAll(PDO::FETCH_ASSOC);
    return $res;
  }

  public function getAprendicesByFicha($idfic) {
    try {
      $sql = "SELECT u.idusu, u.ndocusu, u.nomusu, u.actusu, COALESCE(v.nomval,'Sin estado') AS nomest,
                     COALESCE((SELECT SUM(horasina) FROM inasistencia i WHERE i.idusu=u.idusu AND i.idfic=:idfic),0) AS horasina,
                     COALESCE((SELECT COUNT(*) FROM llamado l WHERE l.idusu=u.idusu AND l.tipllam=1),0) AS llamver,
                     COALESCE((SELECT COUNT(*) FROM llamado l WHERE l.idusu=u.idusu AND l.tipllam=2),0) AS llamesc,
                     (SELECT MAX(fecllam) FROM llamado l WHERE l.idusu=u.idusu) AS fecllam
              FROM usuario AS u
              INNER JOIN usufic AS f ON u.idusu = f.idusu
              LEFT JOIN valor AS v ON u.actusu = v.parval AND v.iddom = 10
              WHERE f.idfic = :idfic
              ORDER BY horasina DESC, u.nomusu";
      $modelo = new conexion();
      $conexion = $modelo->get_conexion();
      $result = $conexion->prepare($sql);
      $result->bindParam(':idfic', $idfic);
      $result->execute();
      return $result->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
      echo "Error: $e";
    }
  }

  public function getConteo($idfic) {
    try {
      $sql = "SELECT
                (SELECT COUNT(*) FROM usuario u INNER JOIN usufic f ON u.idusu=f.idusu WHERE f.idfic=:idfic AND u.actusu=1) AS vigentes,
                (SELECT COUNT(*) FROM llamado l INNER JOIN usuario u ON l.idusu=u.idusu INNER JOIN usufic f ON u.idusu=f.idusu WHERE f.idfic=:idfic) AS llamados,
                (SELECT COALESCE(SUM(horasina),0) FROM inasistencia i INNER JOIN usufic f ON i.idusu=f.idusu WHERE f.idfic=:idfic) AS horas,
                (SELECT COUNT(*) FROM usuario u INNER JOIN usufic f ON u.idusu=f.idusu WHERE f.idfic=:idfic AND u.actusu IN (3,4,5,6)) AS novedades,
                (SELECT COUNT(*) FROM (SELECT i.idusu, SUM(i.horasina) AS hh FROM inasistencia i INNER JOIN usufic f ON i.idusu=f.idusu WHERE f.idfic=:idfic GROUP BY i.idusu HAVING hh > 30) AS t) AS treinta";
      $modelo = new conexion();
      $conexion = $modelo->get_conexion();
      $result = $conexion->prepare($sql);
      $result->bindParam(':idfic', $idfic);
      $result->execute();
      return $result->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
      echo "Error: $e";
    }
  }

  public function getHist($idusu) {
    $sql = "SELECT horasina, fecina, comina FROM inasistencia WHERE idusu = :idusu ORDER BY fecina";
    $modelo = new conexion();
    $conexion = $modelo->get_conexion();
    $result = $conexion->prepare($sql);
    $result->bindParam(':idusu', $idusu);
    $result->execute();
    return $result->fetchAll(PDO::FETCH_ASSOC);
  }

  public function getLlam($idusu) {
    $sql = "SELECT tipllam, obsllam, fecllam FROM llamado WHERE idusu = :idusu ORDER BY fecllam";
    $modelo = new conexion();
    $conexion = $modelo->get_conexion();
    $result = $conexion->prepare($sql);
    $result->bindParam(':idusu', $idusu);
    $result->execute();
    return $result->fetchAll(PDO::FETCH_ASSOC);
  }

  public function guardarLlamado($idusu, $tipllam, $obsllam, $fecllam) {
    $sql = "INSERT INTO llamado (idusu, tipllam, obsllam, fecllam) VALUES (:idusu, :tipllam, :obsllam, :fecllam)";
    $modelo = new conexion();
    $conexion = $modelo->get_conexion();
    $stmt = $conexion->prepare($sql);
    $stmt->bindParam(':idusu', $idusu);
    $stmt->bindParam(':tipllam', $tipllam);
    $stmt->bindParam(':obsllam', $obsllam);
    $stmt->bindParam(':fecllam', $fecllam);
    $stmt->execute();
  }

  public function camactusu($idusu, $actusu) {
    try {
      $sql = "UPDATE usuario SET actusu = :actusu WHERE idusu = :idusu";
      $modelo = new conexion();
      $conexion = $modelo->get_conexion();
      $result = $conexion->prepare($sql);
      $result->bindParam(':actusu', $actusu, PDO::PARAM_INT);
      $result->bindParam(':idusu', $idusu, PDO::PARAM_INT);
      $result->execute();
      return $result->rowCount() > 0;
    } catch (Exception $e) {
      echo '<script>alert("Error al cambiar el estado del usuario: ' . $e->getMessage() . '");</script>';
      return false;
    }
  }

  public function getCoordinadores() {
    $sql = "SELECT idusu, nomusu FROM usuario WHERE idper IN (7, 21, 24) ORDER BY nomusu";
    $modelo = new conexion();
    $conexion = $modelo->get_conexion();
    $result = $conexion->prepare($sql);
    $result->execute();
    return $result->fetchAll(PDO::FETCH_ASSOC);
  }

  public function getAprendices($buscar = null) {
    try {
      $sql = "SELECT u.idusu, u.ndocusu, u.nomusu, u.actusu,
                     COALESCE(v.nomval, 'Sin estado') AS nomest,
                     UF.idfic, F.nomfic, V2.nomval AS nomjor,
                     COALESCE((SELECT SUM(i.horasina) FROM inasistencia i WHERE i.idusu = u.idusu AND i.idfic = UF.idfic), 0) AS horasina,
                     COALESCE((SELECT COUNT(*) FROM llamado l WHERE l.idusu = u.idusu AND l.tipllam = 1), 0) AS llamver,
                     COALESCE((SELECT COUNT(*) FROM llamado l WHERE l.idusu = u.idusu AND l.tipllam = 2), 0) AS llamesc,
                     (SELECT MAX(l.fecllam) FROM llamado l WHERE l.idusu = u.idusu) AS fecllam
              FROM usuario AS u
              INNER JOIN usufic AS UF ON u.idusu = UF.idusu AND UF.actfic = 1
              INNER JOIN ficha AS F ON UF.idfic = F.idfic
              LEFT JOIN valor AS v ON u.actusu = v.parval AND v.iddom = 10
              LEFT JOIN valor AS V2 ON V2.idval = F.jornada
              WHERE (:buscar IS NULL OR :buscar = '' OR u.ndocusu = :buscar OR UF.idfic = :buscar)
              ORDER BY horasina DESC, u.nomusu";
      $modelo = new conexion();
      $conexion = $modelo->get_conexion();
      $result = $conexion->prepare($sql);
      $result->bindParam(':buscar', $buscar);
      $result->execute();
      return $result->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
      echo "Error: $e";
    }
  }

  public function getUser($documentNumber) {
    $sql = "SELECT idusu, nomusu, ndocusu FROM usuario WHERE ndocusu=:ndocusu";
    $modelo = new Conexion();
    $conexion = $modelo->get_conexion();
    $result = $conexion->prepare($sql);
    $result->bindParam(':ndocusu', $documentNumber);
    $result->execute();
    return $result->fetchAll(PDO::FETCH_ASSOC);
  }
}