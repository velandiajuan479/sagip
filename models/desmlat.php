<?php
class Desmlat {
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

  public function getUser($documentNumber) {
    $sql = "SELECT idusu, nomusu, ndocusu FROM usuario WHERE ndocusu=:ndocusu";
    $modelo = new conexion();
    $conexion = $modelo->get_conexion();
    $result = $conexion->prepare($sql);
    $result->bindParam(':ndocusu', $documentNumber);
    $result->execute();
    return $result->fetchAll(PDO::FETCH_ASSOC);
  }

  public function getLlamados($fin, $ffi, $idusu, $idfic) {
    $sql = "SELECT l.idllam, u.idusu, u.ndocusu, u.nomusu, f.idfic, f.nomfic,
                   l.tipllam, l.obsllam, DATE_FORMAT(l.fecllam, '%d/%m/%Y') AS fecllam
            FROM llamado AS l
            INNER JOIN usuario AS u ON u.idusu = l.idusu
            LEFT JOIN usufic AS uf ON uf.idusu = u.idusu
            LEFT JOIN ficha AS f ON f.idfic = uf.idfic
            WHERE 1=1";
    if ($fin) $sql .= " AND DATE(l.fecllam) >= :fin";
    if ($ffi) $sql .= " AND DATE(l.fecllam) <= :ffi";
    if ($idusu) $sql .= " AND l.idusu = :idusu";
    if ($idfic && $idfic != 0) $sql .= " AND uf.idfic = :idfic";
    $sql .= " ORDER BY l.fecllam DESC, u.nomusu;";
    $modelo = new conexion();
    $conexion = $modelo->get_conexion();
    $result = $conexion->prepare($sql);
    if ($fin) $result->bindParam(':fin', $fin);
    if ($ffi) $result->bindParam(':ffi', $ffi);
    if ($idusu) $result->bindParam(':idusu', $idusu);
    if ($idfic && $idfic != 0) $result->bindParam(':idfic', $idfic);
    $result->execute();
    return $result->fetchAll(PDO::FETCH_ASSOC);
  }

  public function getTotales($fin, $ffi, $idusu, $idfic) {
    $sql = "SELECT COUNT(DISTINCT l.idusu) AS aprendices,
                   COUNT(*) AS total,
                   COALESCE(SUM(CASE WHEN l.tipllam = 1 THEN 1 ELSE 0 END), 0) AS verbales,
                   COALESCE(SUM(CASE WHEN l.tipllam = 2 THEN 1 ELSE 0 END), 0) AS escritos
            FROM llamado AS l
            INNER JOIN usuario AS u ON u.idusu = l.idusu
            LEFT JOIN usufic AS uf ON uf.idusu = u.idusu
            WHERE 1=1";
    if ($fin) $sql .= " AND DATE(l.fecllam) >= :fin";
    if ($ffi) $sql .= " AND DATE(l.fecllam) <= :ffi";
    if ($idusu) $sql .= " AND l.idusu = :idusu";
    if ($idfic && $idfic != 0) $sql .= " AND uf.idfic = :idfic";
    $modelo = new conexion();
    $conexion = $modelo->get_conexion();
    $result = $conexion->prepare($sql);
    if ($fin) $result->bindParam(':fin', $fin);
    if ($ffi) $result->bindParam(':ffi', $ffi);
    if ($idusu) $result->bindParam(':idusu', $idusu);
    if ($idfic && $idfic != 0) $result->bindParam(':idfic', $idfic);
    $result->execute();
    return $result->fetch(PDO::FETCH_ASSOC);
  }
}