<?php
$connPath = file_exists('models/conexion.php') ? 'models/conexion.php' : (file_exists('conexion.php') ? 'conexion.php' : '../models/conexion.php');
require_once $connPath;

if (!function_exists('ManejoError')) {
  function ManejoError($e) {
    error_log("Error BD: " . $e->getMessage());
  }
}
class Mele {
  private $idele;
  private $idusu;
  private $nomele;
  private $nidele;
  private $marele;
  private $tipele;
  private $noplasena;
  private $desele;
  private $preele;
  private $idfot;
  private $rutfot;

  function getIdele() { return $this->idele; }
  function getIdusu() { return $this->idusu; }
  function getNomele() { return $this->nomele; }
  function getNidele() { return $this->nidele; }
  function getMarele() { return $this->marele; }
  function getTipele() { return $this->tipele; }
  function getNoplasena() { return $this->noplasena; }
  function getDesele() { return $this->desele; }
  function getPreele() { return $this->preele; }
  function getIdfot() { return $this->idfot; }
  function getRutfot() { return $this->rutfot; }
  // METODOS SET
  function setIdele($idele) { $this->idele = $idele; }
  function setIdusu($idusu) { $this->idusu = $idusu; }
  function setNomele($nomele) { $this->nomele = $nomele; }
  function setNidele($nidele) { $this->nidele = $nidele; }
  function setMarele($marele) { $this->marele = $marele; }
  function setTipele($tipele) { $this->tipele = $tipele; }
  function setNoplasena($noplasena) { $this->noplasena = $noplasena; }
  function setDesele($desele) { $this->desele = $desele; }
  function setPreele($preele) { $this->preele = $preele; }
  function setIdfot($idfot) { $this->idfot = $idfot; }
  function setRutfot($rutfot) { $this->rutfot = $rutfot; }

  public function getAll($filtro_origen = NULL, $filtro_preele = NULL, $filtro_tipele = NULL, $filtro_texto = NULL) {
    try {
      $modelo = new conexion();
      $conexion = $modelo->get_conexion();
      $sql = "SELECT e.idele, e.idusu, e.nomele, e.nidele, e.marele, e.tipele, v.nomval, e.noplasena, e.desele, e.preele, f.rutfot, u.idusu, u.nomusu, u.ndocusu 
              FROM elemento AS e 
              INNER JOIN valor AS v ON e.tipele=v.idval 
              INNER JOIN usuario AS u ON e.idusu=u.idusu 
              LEFT JOIN foto AS f ON e.idele=f.idele 
              WHERE 1";
      $rolesAdmin = [1, 2, 6, 9, 29];
      if (!isset($_SESSION["idper"]) || !in_array($_SESSION["idper"], $rolesAdmin)) {
        $sql .= " AND e.idusu='" . $_SESSION["idusu"] . "'";
      }
      if ($filtro_origen === 'sena') {
        $sql .= " AND (e.noplasena IS NOT NULL AND e.noplasena != '' AND e.noplasena != '0')";
      } else if ($filtro_origen === 'personal') {
        $sql .= " AND (e.noplasena IS NULL OR e.noplasena = '' OR e.noplasena = '0')";
      }
      if (!empty($filtro_preele)) {
        if ($filtro_preele == '1') {
          $sql .= " AND e.preele = 1";
        } else if ($filtro_preele == '2') {
          $sql .= " AND (e.preele = 2 OR e.preele = 0 OR e.preele IS NULL)";
        }
      }
      if (!empty($filtro_tipele)) {
        $sql .= " AND e.tipele = :tipele";
      }
      if (!empty($filtro_texto)) {
        $sql .= " AND (e.nomele LIKE :txt OR e.nidele LIKE :txt OR e.marele LIKE :txt OR e.noplasena LIKE :txt OR u.nomusu LIKE :txt OR u.ndocusu LIKE :txt)";
      }
      $sql .= " ORDER BY e.idele DESC";
      $result = $conexion->prepare($sql);
      if (!empty($filtro_tipele)) {
        $result->bindParam(':tipele', $filtro_tipele);
      }
      if (!empty($filtro_texto)) {
        $txt = '%' . $filtro_texto . '%';
        $result->bindParam(':txt', $txt);
      }
      $result->execute();
      $res = $result->fetchAll(PDO::FETCH_ASSOC);
      return $res;
    } catch (Exception $e) {
      ManejoError($e);
    }
  }

  public function getEstadisticasElementos() {
    try {
      $modelo = new conexion();
      $conexion = $modelo->get_conexion();
      $rolesAdmin = [1, 2, 6, 9, 29];
      $whereUsu = "";
      if (!isset($_SESSION["idper"]) || !in_array($_SESSION["idper"], $rolesAdmin)) {
        $whereUsu = " WHERE e.idusu = '" . $_SESSION["idusu"] . "'";
      }

      $sql = "SELECT 
          COUNT(*) AS total,
          SUM(CASE WHEN e.preele = 1 THEN 1 ELSE 0 END) AS activos,
          SUM(CASE WHEN e.preele = 2 OR e.preele = 0 OR e.preele IS NULL THEN 1 ELSE 0 END) AS inactivos,
          SUM(CASE WHEN e.noplasena IS NOT NULL AND e.noplasena != '' AND e.noplasena != '0' THEN 1 ELSE 0 END) AS institucionales,
          SUM(CASE WHEN e.noplasena IS NULL OR e.noplasena = '' OR e.noplasena = '0' THEN 1 ELSE 0 END) AS personales
      FROM elemento AS e" . $whereUsu;
      
      $stmt = $conexion->prepare($sql);
      $stmt->execute();
      $row = $stmt->fetch(PDO::FETCH_ASSOC);

      // Conteo de elementos actualmente en préstamo activo
      $wherePreUsu = "";
      if (!isset($_SESSION["idper"]) || !in_array($_SESSION["idper"], $rolesAdmin)) {
        $wherePreUsu = " AND p.idusu = '" . $_SESSION["idusu"] . "'";
      }
      $sqlPrestados = "SELECT COUNT(DISTINCT p.idele) FROM presele AS p WHERE p.estpre = 1" . $wherePreUsu;
      $stmtPre = $conexion->prepare($sqlPrestados);
      $stmtPre->execute();
      $prestados = (int)$stmtPre->fetchColumn();

      return [
        'total' => (int)($row['total'] ?? 0),
        'activos' => (int)($row['activos'] ?? 0),
        'inactivos' => (int)($row['inactivos'] ?? 0),
        'institucionales' => (int)($row['institucionales'] ?? 0),
        'personales' => (int)($row['personales'] ?? 0),
        'prestados' => $prestados
      ];
    } catch (Exception $e) {
      return ['total' => 0, 'activos' => 0, 'inactivos' => 0, 'institucionales' => 0, 'personales' => 0, 'prestados' => 0];
    }
  }

  public function getOne() {
    $modelo = new conexion();
    $conexion = $modelo->get_conexion();
    $sql = "SELECT e.idele, e.idusu, e.nomele, e.nidele, e.marele, e.tipele, v.nomval, e.noplasena, e.desele, e.preele, f.rutfot FROM elemento AS e INNER JOIN valor AS v ON e.tipele=v.idval LEFT JOIN foto AS f ON e.idele=f.idele WHERE e.idele=:idele";
    $result = $conexion->prepare($sql);
    $idele = $this->getIdele();
    $result->bindParam(":idele", $idele);
    $result->execute();
    $res = $result->fetchAll(PDO::FETCH_ASSOC);
    return $res;
  }

  public function getElem() {
    $modelo = new conexion();
    $conexion = $modelo->get_conexion();
    $sql = "SELECT idele, idusu, nomele, nidele, marele, tipele, noplasena, desele, preele FROM elemento WHERE idele=:idele";
    $result = $conexion->prepare($sql);
    $idele = $this->getIdele();
    $result->bindParam(":idele", $idele);
    $result->execute();
    $res = $result->fetchAll(PDO::FETCH_ASSOC);
    return $res;
  }

  public function getEle() {
    try {
      $modelo = new conexion();
      $conexion = $modelo->get_conexion();
      $sql = "SELECT idval, nomval, iddom, parval, act FROM valor WHERE iddom=12";
      $result = $conexion->prepare($sql);
      $result->execute();
      $res = $result->fetchall(PDO::FETCH_ASSOC);
      return $res;
    } catch (Exception $e) {
      ManejoError($e);
    }
  }

  public function getOneId() {
    try {
      $modelo = new conexion();
      $conexion = $modelo->get_conexion();
      $sql = "SELECT idele FROM elemento WHERE idusu=:idusu and nomele=:nomele and nidele=:nidele and marele=:marele and tipele=:tipele";
      $result = $conexion->prepare($sql);
      $idusu = $this->getIdusu();
      $result->bindParam(":idusu", $idusu);
      $nomele = $this->getNomele();
      $result->bindParam(":nomele", $nomele);
      $nidele = $this->getNidele();
      $result->bindParam(":nidele", $nidele);
      $marele = $this->getMarele();
      $result->bindParam(":marele", $marele);
      $tipele = $this->getTipele();
      $result->bindParam(":tipele", $tipele);
      $result->execute();
    } catch (Exception $e) {
      ManejoError($e);
    }
  }

  public function selectEle() {
    $modelo = new conexion();
    $conexion = $modelo->get_conexion();
    $sql = "SELECT COUNT(*) as sum FROM elemento WHERE nidele=:nidele";
    $result = $conexion->prepare($sql);
    $nidele = $this->getNidele();
    $result->bindParam(":nidele", $nidele);
    $result->execute();
    $res = $result->fetchAll(PDO::FETCH_ASSOC);
    return $res;
  }

  public function savePre() {
    try {
      $modelo = new conexion();
      $conexion = $modelo->get_conexion();
      $sql = "INSERT INTO elemento(idusu, nomele, nidele, marele, tipele, noplasena, desele, preele) VALUES (:idusu, :nomele, :nidele, :marele, :tipele, :noplasena, :desele, :preele)";
      $result = $conexion->prepare($sql);
      $idusu = $this->getIdusu();
      $result->bindParam(":idusu", $idusu);
      $nomele = $this->getNomele();
      $result->bindParam(":nomele", $nomele);
      $nidele = $this->getNidele();
      $result->bindParam(":nidele", $nidele);
      $marele = $this->getMarele();
      $result->bindParam(":marele", $marele);
      $tipele = $this->getTipele();
      $result->bindParam(":tipele", $tipele);
      $noplasena = $this->getNoplasena();
      $result->bindParam(":noplasena", $noplasena);
      $desele = $this->getDesele();
      $result->bindParam(":desele", $desele);
      $preele = $this->getPreele();
      $result->bindParam(":preele", $preele);
      $result->execute();
    } catch (Exception $e) {
      ManejoError($e);
    }
  }

  public function save() {
    try {
      $modelo = new conexion();
      $conexion = $modelo->get_conexion();
      $sqlId = "SELECT COALESCE(MAX(idele), 0) + 1 AS nextId FROM elemento";
      $resId = $conexion->query($sqlId)->fetch(PDO::FETCH_ASSOC);
      $nextId = $resId ? $resId['nextId'] : 1;

      $sql = "INSERT INTO elemento(idele, idusu, nomele, nidele, marele, tipele, noplasena, desele, preele) VALUES (:idele, :idusu, :nomele, :nidele, :marele, :tipele, :noplasena, :desele, :preele)";
      $result = $conexion->prepare($sql);
      $result->bindParam(":idele", $nextId);
      $idusu = $this->getIdusu();
      $result->bindParam(":idusu", $idusu);
      $nomele = $this->getNomele();
      $result->bindParam(":nomele", $nomele);
      $nidele = $this->getNidele();
      $result->bindParam(":nidele", $nidele);
      $marele = $this->getMarele();
      $result->bindParam(":marele", $marele);
      $tipele = $this->getTipele();
      $result->bindParam(":tipele", $tipele);
      $noplasena = $this->getNoplasena();
      $result->bindParam(":noplasena", $noplasena);
      $desele = $this->getDesele();
      $result->bindParam(":desele", $desele);
      $preele = $this->getPreele() ? $this->getPreele() : 1;
      $result->bindParam(":preele", $preele);
      $result->execute();
      return $nextId;
    } catch (Exception $e) {
      ManejoError($e);
    }
  }

  public function editPre() {
    try {
      $modelo = new conexion();
      $conexion = $modelo->get_conexion();
      $sql = "UPDATE elemento SET preele=:preele WHERE idele=:idele";
      $result = $conexion->prepare($sql);
      $idele = $this->getIdele();
      $result->bindParam(":idele", $idele);
      $preele = $this->getPreele();
      $result->bindParam(":preele", $preele);
      $result->execute();
    } catch (Exception $e) {
      ManejoError($e);
    }
  }

  public function edit() {
    try {
      $modelo = new conexion();
      $conexion = $modelo->get_conexion();
      $sql = "UPDATE elemento SET nomele=:nomele, nidele=:nidele, marele=:marele, tipele=:tipele, noplasena=:noplasena, desele=:desele WHERE idele=:idele";
      $result = $conexion->prepare($sql);
      $idele = $this->getIdele();
      $result->bindParam(":idele", $idele);
      $nomele = $this->getNomele();
      $result->bindParam(":nomele", $nomele);
      $nidele = $this->getNidele();
      $result->bindParam(":nidele", $nidele);
      $marele = $this->getMarele();
      $result->bindParam(":marele", $marele);
      $tipele = $this->getTipele();
      $result->bindParam(":tipele", $tipele);
      $noplasena = $this->getNoplasena();
      $result->bindParam(":noplasena", $noplasena);
      $desele = $this->getDesele();
      $result->bindParam(":desele", $desele);
      $result->execute();
    } catch (Exception $e) {
      ManejoError($e);
    }
  }

  public function del() {
    try {
      $modelo = new conexion();
      $conexion = $modelo->get_conexion();
      $sql = "DELETE FROM elemento WHERE idele=:idele";
      $result = $conexion->prepare($sql);
      $idele = $this->getIdele();
      $result->bindParam(":idele", $idele);
      $result->execute();
    } catch (Exception $e) {
      ManejoError($e);
    }
  }

  public function getUsuByDoc($ndocusu) {
    try {
      $modelo = new conexion();
      $conexion = $modelo->get_conexion();
      $sql = "SELECT idusu, ndocusu, nomusu FROM usuario WHERE ndocusu=:ndocusu";
      $result = $conexion->prepare($sql);
      $result->bindParam(":ndocusu", $ndocusu);
      $result->execute();
      return $result->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
      return [];
    }
  }

  public function getTiposValidos() {
    try {
      $modelo = new conexion();
      $conexion = $modelo->get_conexion();
      $sql = "SELECT idval, nomval FROM valor WHERE iddom=12";
      $result = $conexion->prepare($sql);
      $result->execute();
      return $result->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
      return [];
    }
  }

  public function editBySerial() {
    try {
      $modelo = new conexion();
      $conexion = $modelo->get_conexion();
      $sql = "UPDATE elemento SET idusu=:idusu, nomele=:nomele, marele=:marele, tipele=:tipele, noplasena=:noplasena, desele=:desele WHERE nidele=:nidele";
      $result = $conexion->prepare($sql);
      $nidele = $this->getNidele();
      $result->bindParam(":nidele", $nidele);
      $idusu = $this->getIdusu();
      $result->bindParam(":idusu", $idusu);
      $nomele = $this->getNomele();
      $result->bindParam(":nomele", $nomele);
      $marele = $this->getMarele();
      $result->bindParam(":marele", $marele);
      $tipele = $this->getTipele();
      $result->bindParam(":tipele", $tipele);
      $noplasena = $this->getNoplasena();
      $result->bindParam(":noplasena", $noplasena);
      $desele = $this->getDesele();
      $result->bindParam(":desele", $desele);
      $result->execute();
    } catch (Exception $e) {
      ManejoError($e);
    }
  }

  public function saveFoto($rutfot = null) {
    try {
      $modelo = new conexion();
      $conexion = $modelo->get_conexion();
      $sqlId = "SELECT COALESCE(MAX(idfot), 0) + 1 AS nextId FROM foto";
      $resId = $conexion->query($sqlId)->fetch(PDO::FETCH_ASSOC);
      $nextId = $resId ? $resId['nextId'] : 1;

      $rut = !empty($rutfot) ? $rutfot : $this->rutfot;
      $idele = $this->idele;
      $sql = "INSERT INTO foto(idfot, idele, rutfot) VALUES (:idfot, :idele, :rutfot)";
      $stmt = $conexion->prepare($sql);
      $stmt->bindParam(':idfot', $nextId);
      $stmt->bindParam(':idele', $idele);
      $stmt->bindParam(':rutfot', $rut);
      $stmt->execute();
      return $nextId;
    } catch (Exception $e) {
      ManejoError($e);
    }
  }

  public function editFoto($rutfot = null) {
    try {
      $modelo = new conexion();
      $conexion = $modelo->get_conexion();
      $rut = !empty($rutfot) ? $rutfot : $this->rutfot;
      $idele = $this->idele;
      $check = $conexion->prepare("SELECT idfot FROM foto WHERE idele=:idele");
      $check->bindParam(':idele', $idele);
      $check->execute();
      if ($check->rowCount() > 0) {
        $stmt = $conexion->prepare("UPDATE foto SET rutfot=:rutfot WHERE idele=:idele");
      } else {
        $sqlId = "SELECT COALESCE(MAX(idfot), 0) + 1 AS nextId FROM foto";
        $resId = $conexion->query($sqlId)->fetch(PDO::FETCH_ASSOC);
        $nextId = $resId ? $resId['nextId'] : 1;
        $stmt = $conexion->prepare("INSERT INTO foto(idfot, idele, rutfot) VALUES (:idfot, :idele, :rutfot)");
        $stmt->bindParam(':idfot', $nextId);
      }
      $stmt->bindParam(':idele', $idele);
      $stmt->bindParam(':rutfot', $rut);
      $stmt->execute();
    } catch (Exception $e) {
      ManejoError($e);
    }
  }

  public function delFot() {
    try {
      $modelo = new conexion();
      $conexion = $modelo->get_conexion();
      $stmt = $conexion->prepare("DELETE FROM foto WHERE idele=:idele");
      $idele = $this->idele;
      $stmt->bindParam(':idele', $idele);
      $stmt->execute();
    } catch (Exception $e) {
      ManejoError($e);
    }
  }
}

if (!class_exists('Mfot')) {
  class Mfot extends Mele {}
}

if (!class_exists('Premvele')) {
  class Premvele extends Mele {}
}


