<?php
class Desmdsra {
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
    return $result->fetchAll(PDO::FETCH_ASSOC);
  }

  public function getDeserciones($idfic) {
    try {
      $sql = "SELECT d.iddec, d.idact, d.idusu, d.idfic, d.fecregdec, d.obsdec,
                     u.ndocusu, u.nomusu, u.actusu,
                     COALESCE(v.nomval,'Sin estado') AS nomest,
                     f.nomfic, vj.nomval AS nomval,
                     a.nac, a.fecact, a.conact,
                     COALESCE((SELECT SUM(i.horasina) FROM inasistencia i WHERE i.idusu=u.idusu AND i.idfic=d.idfic),0) AS horasina,
                     COALESCE((SELECT COUNT(*) FROM llamado l WHERE l.idusu=u.idusu AND l.tipllam=1),0) AS llamver,
                     COALESCE((SELECT COUNT(*) FROM llamado l WHERE l.idusu=u.idusu AND l.tipllam=2),0) AS llamesc
              FROM desercion AS d
              INNER JOIN usuario AS u ON u.idusu = d.idusu
              INNER JOIN ficha AS f ON f.idfic = d.idfic
              LEFT JOIN valor AS v ON u.actusu = v.parval AND v.iddom = 10
              LEFT JOIN valor AS vj ON vj.idval = f.jornada
              LEFT JOIN acta AS a ON a.idact = d.idact
              WHERE 1=1";
      if ($idfic && $idfic != 0) $sql .= " AND d.idfic = :idfic";
      $sql .= " ORDER BY d.fecregdec DESC, u.nomusu;";
      $modelo = new conexion();
      $conexion = $modelo->get_conexion();
      $result = $conexion->prepare($sql);
      if ($idfic && $idfic != 0) $result->bindParam(':idfic', $idfic);
      $result->execute();
      return $result->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
      echo "Error: $e";
    }
  }

  public function getConteo($idfic) {
    try {
      $sql = "SELECT COUNT(*) AS total FROM desercion AS d WHERE 1=1";
      if ($idfic && $idfic != 0) $sql .= " AND d.idfic = :idfic";
      $modelo = new conexion();
      $conexion = $modelo->get_conexion();
      $result = $conexion->prepare($sql);
      if ($idfic && $idfic != 0) $result->bindParam(':idfic', $idfic);
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
}