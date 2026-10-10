<?php
$connPath = file_exists('models/conexion.php') ? 'models/conexion.php' : (file_exists('conexion.php') ? 'conexion.php' : '../models/conexion.php');
require_once $connPath;
class Mprele {
  private $idprele;
  private $idele;
  private $idusu;
  private $fhpre;
  private $fhent;
  private $estpre;
  private $whopre;

  function getIdprele() { return $this->idprele; }
  function getIdele() { return $this->idele; }
  function getIdusu() { return $this->idusu; }
  function getFhpre() { return $this->fhpre; }
  function getFhent() { return $this->fhent; }
  function getEstpre() { return $this->estpre; }
  function getWhopre() { return $this->whopre; }

  function setIdprele($idprele) { $this->idprele = $idprele; }
  function setIdele($idele) { $this->idele = $idele; }
  function setIdusu($idusu) { $this->idusu = $idusu; }
  function setFhpre($fhpre) { $this->fhpre = $fhpre; }
  function setFhent($fhent) { $this->fhent = $fhent; }
  function setEstpre($estpre) { $this->estpre = $estpre; }
  function setWhopre($whopre) { $this->whopre = $whopre; }

  public function getAll($idper, $idusu, $filtro_estado = NULL, $filtro_fin = NULL, $filtro_ffi = NULL, $filtro_idele = NULL, $filtro_texto = NULL) {
    try {
      $res = NULL;
      $modelo = new conexion();
      $conexion = $modelo->get_conexion();
      $sql = "SELECT pr.idprele, pr.idele, ft.rutfot, e.nomele, e.nidele, e.marele, e.tipele, e.noplasena, e.desele, e.preele, 
                     pr.idusu, u.ndocusu AS docu, u.nomusu AS nomu, p.nomper AS peru, 
                     pr.fhpre, pr.estpre, pr.fhent, pr.whopre, 
                     w.ndocusu AS docw, w.nomusu AS nomw, wp.nomper AS perw, 
                     uf.idfic, f.nomfic, v.nomval AS jornada 
              FROM presele AS pr 
              LEFT JOIN elemento AS e ON pr.idele=e.idele 
              LEFT JOIN foto AS ft ON e.idele=ft.idele 
              LEFT JOIN usuario AS u ON pr.idusu=u.idusu 
              LEFT JOIN usuario AS w ON pr.whopre=w.idusu 
              LEFT JOIN perfil AS wp ON w.idper=wp.idper 
              LEFT JOIN usufic AS uf ON pr.idusu=uf.idusu 
              LEFT JOIN ficha AS f ON uf.idfic=f.idfic 
              LEFT JOIN perfil AS p ON u.idper=p.idper 
              LEFT JOIN valor AS v ON f.jornada=v.idval
              WHERE 1";
      if (!in_array($idper, [1, 2, 6, 9, 29])) {
        $sql .= " AND pr.idusu=:idusu";
      }

      if (!empty($filtro_estado)) {
        if ($filtro_estado === 'activo') {
          $sql .= " AND pr.estpre = 1 AND (pr.fhpre >= DATE_SUB(NOW(), INTERVAL 24 HOUR) AND DATE(pr.fhpre) = CURDATE())";
        } else if ($filtro_estado === 'mora') {
          $sql .= " AND pr.estpre = 1 AND (pr.fhpre < DATE_SUB(NOW(), INTERVAL 24 HOUR) OR DATE(pr.fhpre) < CURDATE())";
        } else if ($filtro_estado === 'activos_todos') {
          $sql .= " AND pr.estpre = 1";
        } else if ($filtro_estado === 'devuelto' || $filtro_estado === '2') {
          $sql .= " AND pr.estpre = 2";
        }
      }

      if (!empty($filtro_fin)) {
        $sql .= " AND DATE(pr.fhpre) >= :fin";
      }
      if (!empty($filtro_ffi)) {
        $sql .= " AND DATE(pr.fhpre) <= :ffi";
      }
      if (!empty($filtro_idele)) {
        $sql .= " AND pr.idele = :idele";
      }
      if (!empty($filtro_texto)) {
        $sql .= " AND (e.nomele LIKE :txt OR e.nidele LIKE :txt OR e.noplasena LIKE :txt OR u.nomusu LIKE :txt OR u.ndocusu LIKE :txt OR w.nomusu LIKE :txt)";
      }

      $sql .= " ORDER BY pr.estpre ASC, pr.fhpre DESC, pr.idprele DESC";
      $result = $conexion->prepare($sql);

      if (!in_array($idper, [1, 2, 6, 9, 29])) {
        $result->bindParam(':idusu', $idusu);
      }
      if (!empty($filtro_fin)) {
        $result->bindParam(':fin', $filtro_fin);
      }
      if (!empty($filtro_ffi)) {
        $result->bindParam(':ffi', $filtro_ffi);
      }
      if (!empty($filtro_idele)) {
        $result->bindParam(':idele', $filtro_idele);
      }
      if (!empty($filtro_texto)) {
        $txt = '%' . $filtro_texto . '%';
        $result->bindParam(':txt', $txt);
      }

      $result->execute();
      $res = $result->fetchAll(PDO::FETCH_ASSOC);
      return $res;
    } catch (Exception $e) {
      echo "Error: ".$e->getMessage();
    }
  }

  public function getEstadisticasPrestamos($idper, $idusu) {
    try {
      $modelo = new conexion();
      $conexion = $modelo->get_conexion();
      
      // 1. Elementos institucionales disponibles (no prestados actualmente)
      $sqlDisp = "SELECT COUNT(*) FROM elemento AS e 
                  WHERE (e.noplasena IS NOT NULL AND e.noplasena != '' AND e.noplasena != '0') 
                    AND e.idele NOT IN (SELECT idele FROM presele WHERE estpre = 1)";
      $stmtDisp = $conexion->prepare($sqlDisp);
      $stmtDisp->execute();
      $disponibles = (int)$stmtDisp->fetchColumn();

      // Roles admin
      $rolesAdmin = [1, 2, 6, 9, 29];
      $whereUsu = "";
      if (!in_array($idper, $rolesAdmin)) {
        $whereUsu = " AND pr.idusu = " . (int)$idusu;
      }

      // 2. Prestamos activos, devueltos y morosos
      $sqlStats = "SELECT 
          SUM(CASE WHEN pr.estpre = 1 THEN 1 ELSE 0 END) AS activos,
          SUM(CASE WHEN pr.estpre = 2 THEN 1 ELSE 0 END) AS devueltos,
          SUM(CASE WHEN pr.estpre = 1 AND (pr.fhpre < DATE_SUB(NOW(), INTERVAL 24 HOUR) OR DATE(pr.fhpre) < CURDATE()) THEN 1 ELSE 0 END) AS mora
        FROM presele AS pr 
        WHERE 1" . $whereUsu;
      $stmtStats = $conexion->prepare($sqlStats);
      $stmtStats->execute();
      $row = $stmtStats->fetch(PDO::FETCH_ASSOC);

      return [
        'disponibles' => $disponibles,
        'activos' => (int)($row['activos'] ?? 0),
        'devueltos' => (int)($row['devueltos'] ?? 0),
        'mora' => (int)($row['mora'] ?? 0)
      ];
    } catch (Exception $e) {
      return ['disponibles' => 0, 'activos' => 0, 'devueltos' => 0, 'mora' => 0];
    }
  }

  public function getElementosFiltro() {
    try {
      $modelo = new conexion();
      $conexion = $modelo->get_conexion();
      $sql = "SELECT e.idele, e.nomele, e.noplasena, e.marele 
              FROM elemento AS e 
              WHERE (e.noplasena IS NOT NULL AND e.noplasena != '' AND e.noplasena != '0') 
              ORDER BY e.nomele ASC";
      $result = $conexion->prepare($sql);
      $result->execute();
      return $result->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
      return [];
    }
  }
  
  public function getOne() {
    try {
      $res = NULL;
      $modelo = new conexion();
      $conexion = $modelo->get_conexion();
      $sql = "SELECT pr.idprele, pr.idele, ft.rutfot, e.nomele, e.nidele, e.marele, e.tipele, e.noplasena, e.desele, e.preele, 
                     pr.idusu, u.ndocusu AS docu, u.nomusu AS nomu, p.nomper AS peru, 
                     pr.fhpre, pr.estpre, pr.fhent, pr.whopre, 
                     w.ndocusu AS docw, w.nomusu AS nomw, wp.nomper AS perw, 
                     uf.idfic, f.nomfic, v.nomval AS jornada 
              FROM presele AS pr 
              LEFT JOIN elemento AS e ON pr.idele=e.idele 
              LEFT JOIN foto AS ft ON e.idele=ft.idele 
              LEFT JOIN usuario AS u ON pr.idusu=u.idusu 
              LEFT JOIN usuario AS w ON pr.whopre=w.idusu 
              LEFT JOIN perfil AS wp ON w.idper=wp.idper 
              LEFT JOIN usufic AS uf ON pr.idusu=uf.idusu 
              LEFT JOIN ficha AS f ON uf.idfic=f.idfic 
              LEFT JOIN perfil AS p ON u.idper=p.idper 
              LEFT JOIN valor AS v ON f.jornada=v.idval 
              WHERE pr.idprele=:idprele";
      $result = $conexion->prepare($sql);
      $idprele = $this->getIdprele();
      $result->bindParam(':idprele', $idprele);
      $result->execute();
      $res = $result->fetchAll(PDO::FETCH_ASSOC);
      return $res;
    } catch (Exception $e) {
      echo "Error: ".$e->getMessage();
    }
  }

  public function getUsu($ndocusu) {
    try {
      $res = NULL;
      $modelo = new conexion();
      $conexion = $modelo->get_conexion();
      $sql = "SELECT idusu, ndocusu, nomusu FROM usuario WHERE ndocusu=:ndocusu";
      $result = $conexion->prepare($sql);
      $result->bindParam(":ndocusu", $ndocusu);
      $result->execute();
      $res = $result->fetchAll(PDO::FETCH_ASSOC);
      return $res;
    } catch (Exception $e) {
      echo "Error: ".$e->getMessage();
    }
  }

  public function getMin() {
    try {
      $res = NULL;
      $modelo = new conexion();
      $conexion = $modelo->get_conexion();
      $sql = "SELECT nummin, obs FROM minuta WHERE ideles=:idele AND idusu=:idusu";
      $result = $conexion->prepare($sql);
      $idele = $this->getIdele();
      $result->bindParam(':idele', $idele);
      $idusu = $this->getIdusu();
      $result->bindParam(':idusu', $idusu);
      $result->execute();
      $res = $result->fetchAll(PDO::FETCH_ASSOC);
      return $res;
    } catch (Exception $e) {
      echo "Error: ".$e->getMessage();
    }
  }

  public function getEles() {
    try {
      $res = NULL;
      $modelo = new conexion();
      $conexion = $modelo->get_conexion();
      $sql = "SELECT e.idele, e.nomele, e.noplasena, e.marele, e.desele 
              FROM elemento AS e 
              WHERE (e.noplasena IS NOT NULL AND e.noplasena != 0) 
                AND e.idele NOT IN (SELECT idele FROM presele WHERE estpre = 1)
              ORDER BY e.nomele ASC";
      $result = $conexion->prepare($sql);
      $result->execute();
      $res = $result->fetchall(PDO::FETCH_ASSOC);
      return $res;
    } catch (Exception $e) {
      echo "Error: $e";
    }
  }

  public function getPrestamosPendientes($idusu) {
    try {
      $modelo = new conexion();
      $conexion = $modelo->get_conexion();
      $sql = "SELECT pr.idprele, pr.idele, e.nomele, e.noplasena, e.marele, pr.fhpre, 
                     TIMESTAMPDIFF(HOUR, pr.fhpre, NOW()) AS horas_transcurridas 
              FROM presele AS pr 
              LEFT JOIN elemento AS e ON pr.idele=e.idele 
              WHERE pr.idusu=:idusu AND pr.estpre=1 
              ORDER BY pr.fhpre ASC";
      $result = $conexion->prepare($sql);
      $result->bindParam(':idusu', $idusu);
      $result->execute();
      return $result->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
      return [];
    }
  }

  public function getResumenMorosos() {
    try {
      $modelo = new conexion();
      $conexion = $modelo->get_conexion();
      $sql = "SELECT COUNT(*) AS total_activos, 
                     SUM(CASE WHEN pr.fhpre < DATE_SUB(NOW(), INTERVAL 24 HOUR) THEN 1 ELSE 0 END) AS total_morosos
              FROM presele AS pr 
              WHERE pr.estpre=1";
      $result = $conexion->prepare($sql);
      $result->execute();
      $res = $result->fetch(PDO::FETCH_ASSOC);
      return $res ? $res : ['total_activos' => 0, 'total_morosos' => 0];
    } catch (Exception $e) {
      return ['total_activos' => 0, 'total_morosos' => 0];
    }
  }

  public function getElem() {
    try {
      $res = NULL;
      $modelo = new conexion();
      $conexion = $modelo->get_conexion();
      $sql = "SELECT desele FROM elemento WHERE idele=:idele";
      $result = $conexion->prepare($sql);
      $idele = $this->getIdele();
      $result->bindParam(':idele', $idele);
      $result->execute();
      $res = $result->fetchall(PDO::FETCH_ASSOC);
      return $res;
    } catch (Exception $e) {
      echo "Error: $e";
    }
  }
  
  public function save() {
    try {
      $modelo = new conexion();
      $conexion = $modelo->get_conexion();
      $sqlId = "SELECT COALESCE(MAX(idprele), 0) + 1 AS nextId FROM presele";
      $resId = $conexion->query($sqlId)->fetch(PDO::FETCH_ASSOC);
      $nextId = $resId ? $resId['nextId'] : 1;

      $sql = "INSERT INTO presele(idprele, idele, idusu, fhpre, estpre, whopre) VALUES(:idprele, :idele, :idusu, :fhpre, :estpre, :whopre)";
      $result = $conexion->prepare($sql);
      $result->bindParam(':idprele', $nextId);
      $idele = $this->getIdele();
      $result->bindParam(':idele', $idele);
      $idusu = $this->getIdusu();
      $result->bindParam(':idusu', $idusu);
      $fhpre = $this->getFhpre();
      $result->bindParam(':fhpre', $fhpre);
      $estpre = $this->getEstpre();
      $result->bindParam(':estpre', $estpre);
      $whopre = $this->getWhopre();
      $result->bindParam(':whopre', $whopre);
      $result->execute();
      return $nextId;
    } catch (Exception $e) {
      echo "Error: ".$e->getMessage();
    }
  }

  public function entEle() {
    try {
      $modelo = new conexion();
      $conexion = $modelo->get_conexion();
      $sql = "UPDATE presele SET fhent=:fhent, estpre=:estpre WHERE idprele=:idprele";
      $result = $conexion->prepare($sql);
      $idprele = $this->getIdprele();
      $result->bindParam(':idprele', $idprele);
      $fhent = $this->getFhent();
      $result->bindParam(':fhent', $fhent);
      $estpre = $this->getEstpre();
      $result->bindParam(':estpre', $estpre);
      $result->execute();
    } catch (Exception $e) {
      echo "Error: ".$e->getMessage();
    }
  }
  
  public function edit() {
    try {
      $modelo = new conexion();
      $conexion = $modelo->get_conexion();
      $sql = "UPDATE presele SET idele=:idele, idusu=:idusu, fhpre=:fhpre, fhent=:fhent, estpre=:estpre, whopre=:whopre WHERE idprele=:idprele";
      $result = $conexion->prepare($sql);
      $idprele = $this->getIdprele();
      $result->bindParam(':idprele', $idprele);
      $idele = $this->getIdele();
      $result->bindParam(':idele', $idele);
      $idusu = $this->getIdusu();
      $result->bindParam(':idusu', $idusu);
      $fhpre = $this->getFhpre();
      $result->bindParam(':fhpre', $fhpre);
      $fhent = $this->getFhent();
      $result->bindParam(':fhent', $fhent);
      $estpre = $this->getEstpre();
      $result->bindParam(':estpre', $estpre);
      $whopre = $this->getWhopre();
      $result->bindParam(':whopre', $whopre);
      $result->execute();
    } catch (Exception $e) {
      echo "Error: ".$e->getMessage();
    }
  }

  public function del() {
    try {
      $modelo = new conexion();
      $conexion = $modelo->get_conexion();
      $sql = "DELETE FROM presele WHERE idprele=:idprele";
      $result = $conexion->prepare($sql);
      $idprele = $this->getIdprele();
      $result->bindParam(':idprele', $idprele);
      $result->execute();
    } catch (Exception $e) {
      echo "Error: ".$e->getMessage();
    }
  }

  public function saveMinuta($idusu, $idele, $fechos, $obs = 1, $tipmin = 'A') {
    try {
      $modelo = new conexion();
      $conexion = $modelo->get_conexion();
      $sqlNum = "SELECT COALESCE(MAX(nummin), 0) + 1 AS sig FROM minuta";
      $rowNum = $conexion->query($sqlNum)->fetch(PDO::FETCH_ASSOC);
      $nummin = $rowNum ? $rowNum['sig'] : 1;

      $sql = "INSERT INTO minuta (nummin, idusu, fechos, tipmin, hij, fhlle, obs, ideles) 
              VALUES (:nummin, :idusu, :fechos, :tipmin, 0, NULL, :obs, :ideles)";
      $stmt = $conexion->prepare($sql);
      $stmt->bindParam(':nummin', $nummin);
      $stmt->bindParam(':idusu', $idusu);
      $stmt->bindParam(':fechos', $fechos);
      $stmt->bindParam(':tipmin', $tipmin);
      $stmt->bindParam(':obs', $obs);
      $stmt->bindParam(':ideles', $idele);
      $stmt->execute();
      return $nummin;
    } catch (Exception $e) {
      echo "Error: " . $e->getMessage();
    }
  }

  public function updMinuta($nummin, $fhlle, $tipmin = 'E') {
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
      echo "Error: " . $e->getMessage();
    }
  }
}

if (!class_exists('Premprele')) {
  class Premprele extends Mprele {}
}

if (!class_exists('Mres')) {
  class Mres extends Mprele {}
}
?>