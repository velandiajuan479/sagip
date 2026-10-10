<?php
require_once(__DIR__ . '/conexion.php');

class Usumfic{
    private $idactdc;
    private $idfic;
    private $nomfic;
    private $jornada;
    private $idcen;
    private $mun;
    private $finific;
    private $ffinfic;
    private $idact;

    private $tit; 
	private $nac; 
	private $ncre; 
	private $cife; 
	private $hini; 
	private $hfin; 
	private $jgen; 
	private $drcd; 
	private $agep; 
	private $desr; 
    private $objr; 
    private $aade;
    
    private $eaco;
    private $conc; //CONCLUSIONES
    
// METODOS GET
function getConc(){
		return $this->conc;
	}
    function setConc($conc){
		$this->conc = $conc;
	}
function getEaco(){
		return $this->eaco;
	}
    function setEaco($eaco){
		$this->eaco = $eaco;
	}
function getAade(){
		return $this->aade;
	}
    function setAade($aade){
		$this->aade = $aade;
	}

    public function getTit(){
		return $this->tit;
	}
	public function getNac(){
		return $this->nac;
	}
	public function getNcre(){
		return $this->ncre;
	}
	public function getCife(){
		return $this->cife;
	}
	public function getHini(){
		return $this->hini;
	}
	public function getHfin(){
		return $this->hfin;
	}
	public function getJgen(){
		return $this->jgen;
	}
	public function getDrcd(){
		return $this->drcd;
	}
	public function getAgep(){
		return $this->agep;
	}

	public function getDesr(){
		return $this->desr;
	}
    public function getIdactdc(){
		return $this->idactdc;
	}
     public function getIdact(){
        return $this->idact;
    }
    public function getIdfic(){
        return $this->idfic;
    }
    public function getNomfic(){
        return $this->nomfic;
    }
    public function getJornada(){
        return $this->jornada;
    }
    public function getIdcen(){
        return $this->idcen;
    }
    public function getMun(){
        return $this->mun;
    }
    public function getFinific(){
        return $this->finific;
    }
    public function getFfinfic(){
        return $this->ffinfic;
    }
    function getObjr(){
		return $this->objr;
	}

//  METODOS SET
    public function setTit($tit){
		$this->tit = $tit;
	}
	public function setNac($nac){
		$this->nac = $nac;
	}
	public function setNcre($ncre){
		$this->ncre = $ncre;
	}
	public function setCife($cife){
		$this->cife = $cife;
	}
	public function setHini($hini){
		$this->hini = $hini;
	}
	public function setHfin($hfin){
		$this->hfin = $hfin;
	}
	public function setJgen($jgen){
		$this->jgen = $jgen;
	}
	public function setDrcd($drcd){
		$this->drcd = $drcd;
	}
	public function setAgep($agep){
		$this->agep = $agep;
	}
	public function setDesr($desr){
		$this->desr = $desr;
	}
    public function setIdactdc($id){
		$this->idactdc = $id;
	}
    public function setIdact($idact){
        $this->idact=$idact;
    }
    public function setIdfic($idfic){
        $this->idfic=$idfic;
    }
    public function setNomfic($nomfic){
        $this->nomfic=$nomfic;
    }
    public function setJornada($jornada){
        $this->jornada=$jornada;
    }
    public function setIdcen($idcen){
        $this->idcen=$idcen;
    }

    public function setMun($mun){
        $this->mun=$mun;
    }
    public function setFinific($finific){
        $this->finific=$finific;
    }
    public function setFfinfic($ffinfic){
        $this->ffinfic=$ffinfic;
    }
    function setObjr($objr){
		$this->objr = $objr;
	}


    public function selAll($fic=NULL){
        $sql = "SELECT f.idfic, f.nomfic, f.jornada, f.mun, v.nomval, f.idcen, c.nomcen, f.finific, f.ffinfic FROM ficha AS f INNER JOIN valor AS v ON f.jornada=v.idval INNER JOIN centro AS c ON f.idcen=c.idcen";
        if($fic) {
            $sql .= " WHERE f.idfic = :fic";
        }
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        if($fic) {
            $result->bindParam(':fic', $fic);
        }
        $result->execute(); 
        return $result->fetchAll(PDO::FETCH_ASSOC);
    }

    public function selActivas(){
        $sql ="SELECT f.idfic, f.nomfic, f.jornada, f.mun, v.nomval, f.idcen,  c.nomcen, f.finific,f.ffinfic FROM ficha AS f INNER JOIN valor AS v ON f.jornada=v.idval INNER JOIN centro AS c ON f.idcen=c.idcen WHERE f.finific <= CURDATE() AND f.ffinfic >= CURDATE() ORDER BY CAST(f.idfic AS UNSIGNED) DESC";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->execute(); 
        $res= $result->fetchAll(PDO::FETCH_ASSOC);
        return $res;
       
    }

    public function selUltimas50(){
        $sql = "SELECT f.idfic, f.nomfic, f.jornada, f.mun, v.nomval, f.idcen, c.nomcen, f.finific, f.ffinfic 
                FROM ficha AS f 
                INNER JOIN valor AS v ON f.jornada=v.idval 
                INNER JOIN centro AS c ON f.idcen=c.idcen 
                ORDER BY COALESCE(f.fecregf, '1970-01-01 00:00:00') DESC, CAST(f.idfic AS UNSIGNED) DESC 
                LIMIT 50";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->execute(); 
        $res = $result->fetchAll(PDO::FETCH_ASSOC);
        return $res;
    }

    public function selTitulada50(){
        $sql = "SELECT f.idfic, f.nomfic, f.jornada, f.mun, v.nomval, f.idcen, c.nomcen, f.finific, f.ffinfic 
                FROM ficha AS f 
                INNER JOIN valor AS v ON f.jornada=v.idval 
                INNER JOIN centro AS c ON f.idcen=c.idcen 
                LEFT JOIN programa AS p ON f.codpro = p.codpro 
                LEFT JOIN valor AS vp ON p.tippro = vp.idval 
                WHERE p.tippro IN (1051, 1052, 1082) 
                   OR vp.nomval IN ('Tecnólogo', 'Técnico', 'Titulada') 
                   OR UPPER(f.nomfic) LIKE '%TECNOL%' 
                   OR UPPER(f.nomfic) LIKE '%TECNIC%' 
                   OR (UPPER(f.nomfic) NOT LIKE '%COMPLEMENTARI%' AND UPPER(f.nomfic) NOT REGEXP '\\\\bCURSOS?\\\\b' AND (p.tippro IS NULL OR p.tippro != 1083)) 
                ORDER BY COALESCE(f.fecregf, '1970-01-01 00:00:00') DESC, CAST(f.idfic AS UNSIGNED) DESC 
                LIMIT 50";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->execute(); 
        $res = $result->fetchAll(PDO::FETCH_ASSOC);
        return $res;
    }

    public function selComplementarias50(){
        $sql = "SELECT f.idfic, f.nomfic, f.jornada, f.mun, v.nomval, f.idcen, c.nomcen, f.finific, f.ffinfic 
                FROM ficha AS f 
                INNER JOIN valor AS v ON f.jornada=v.idval 
                INNER JOIN centro AS c ON f.idcen=c.idcen 
                LEFT JOIN programa AS p ON f.codpro = p.codpro 
                LEFT JOIN valor AS vp ON p.tippro = vp.idval 
                WHERE p.tippro = 1083 
                   OR vp.nomval = 'Complementaria' 
                   OR UPPER(f.nomfic) LIKE '%COMPLEMENTARI%' 
                   OR UPPER(f.nomfic) REGEXP '\\\\bCURSOS?\\\\b' 
                ORDER BY COALESCE(f.fecregf, '1970-01-01 00:00:00') DESC, CAST(f.idfic AS UNSIGNED) DESC 
                LIMIT 50";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->execute(); 
        $res = $result->fetchAll(PDO::FETCH_ASSOC);
        return $res;
    }

    public function selBusqueda($termino){
        $sql = "SELECT f.idfic, f.nomfic, f.jornada, f.mun, v.nomval, f.idcen, c.nomcen, f.finific, f.ffinfic 
                FROM ficha AS f 
                INNER JOIN valor AS v ON f.jornada=v.idval 
                INNER JOIN centro AS c ON f.idcen=c.idcen 
                WHERE f.idfic LIKE :busqueda OR f.nomfic LIKE :busqueda 
                ORDER BY COALESCE(f.fecregf, '1970-01-01 00:00:00') DESC, CAST(f.idfic AS UNSIGNED) DESC 
                LIMIT 50";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $busqueda = '%'.$termino.'%';
        $result->bindParam(':busqueda', $busqueda);
        $result->execute();
        $res = $result->fetchAll(PDO::FETCH_ASSOC);
        return $res;
    }

    public function getFichasServerSide($tipo = 'all', $search = '', $start = 0, $length = 50){
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();

        $whereClauses = [];
        $params = [];

        if ($tipo === 'titulada') {
            $whereClauses[] = "(p.tippro IN (1051, 1052, 1082) 
                OR vp.nomval IN ('Tecnólogo', 'Técnico', 'Titulada') 
                OR UPPER(f.nomfic) LIKE '%TECNOL%' 
                OR UPPER(f.nomfic) LIKE '%TECNIC%' 
                OR (UPPER(f.nomfic) NOT LIKE '%COMPLEMENTARI%' AND UPPER(f.nomfic) NOT REGEXP '\\\\bCURSOS?\\\\b' AND (p.tippro IS NULL OR p.tippro != 1083)))";
        } elseif ($tipo === 'complementaria') {
            $whereClauses[] = "(p.tippro = 1083 
                OR vp.nomval = 'Complementaria' 
                OR UPPER(f.nomfic) LIKE '%COMPLEMENTARI%' 
                OR UPPER(f.nomfic) REGEXP '\\\\bCURSOS?\\\\b')";
        }

        if ($search !== '') {
            $whereClauses[] = "(f.idfic LIKE :search 
                OR f.nomfic LIKE :search 
                OR v.nomval LIKE :search 
                OR c.nomcen LIKE :search 
                OR f.mun LIKE :search 
                OR p.nompro LIKE :search)";
            $params[':search'] = '%' . $search . '%';
        }

        $whereSql = "";
        if (count($whereClauses) > 0) {
            $whereSql = " WHERE " . implode(" AND ", $whereClauses);
        }

        $sqlTotal = "SELECT COUNT(*) as total FROM ficha f";
        if ($tipo === 'titulada') {
            $sqlTotal = "SELECT COUNT(*) as total FROM ficha f 
                         LEFT JOIN programa p ON f.codpro = p.codpro 
                         LEFT JOIN valor vp ON p.tippro = vp.idval 
                         WHERE (p.tippro IN (1051, 1052, 1082) 
                            OR vp.nomval IN ('Tecnólogo', 'Técnico', 'Titulada') 
                            OR UPPER(f.nomfic) LIKE '%TECNOL%' 
                            OR UPPER(f.nomfic) LIKE '%TECNIC%' 
                            OR (UPPER(f.nomfic) NOT LIKE '%COMPLEMENTARI%' AND UPPER(f.nomfic) NOT REGEXP '\\\\bCURSOS?\\\\b' AND (p.tippro IS NULL OR p.tippro != 1083)))";
        } elseif ($tipo === 'complementaria') {
            $sqlTotal = "SELECT COUNT(*) as total FROM ficha f 
                         LEFT JOIN programa p ON f.codpro = p.codpro 
                         LEFT JOIN valor vp ON p.tippro = vp.idval 
                         WHERE (p.tippro = 1083 
                            OR vp.nomval = 'Complementaria' 
                            OR UPPER(f.nomfic) LIKE '%COMPLEMENTARI%' 
                            OR UPPER(f.nomfic) REGEXP '\\\\bCURSOS?\\\\b')";
        }
        $stmtTotal = $conexion->prepare($sqlTotal);
        $stmtTotal->execute();
        $recordsTotal = (int)$stmtTotal->fetch(PDO::FETCH_ASSOC)['total'];

        $sqlFiltered = "SELECT COUNT(*) as total 
                        FROM ficha AS f 
                        INNER JOIN valor AS v ON f.jornada=v.idval 
                        INNER JOIN centro AS c ON f.idcen=c.idcen 
                        LEFT JOIN programa AS p ON f.codpro = p.codpro 
                        LEFT JOIN valor AS vp ON p.tippro = vp.idval" . $whereSql;
        $stmtFiltered = $conexion->prepare($sqlFiltered);
        $stmtFiltered->execute($params);
        $recordsFiltered = (int)$stmtFiltered->fetch(PDO::FETCH_ASSOC)['total'];

        $sqlData = "SELECT f.idfic, f.nomfic, f.jornada, f.mun, v.nomval, f.idcen, c.nomcen, f.finific, f.ffinfic 
                    FROM ficha AS f 
                    INNER JOIN valor AS v ON f.jornada=v.idval 
                    INNER JOIN centro AS c ON f.idcen=c.idcen 
                    LEFT JOIN programa AS p ON f.codpro = p.codpro 
                    LEFT JOIN valor AS vp ON p.tippro = vp.idval" . $whereSql . "
                    ORDER BY COALESCE(f.fecregf, '1970-01-01 00:00:00') DESC, CAST(f.idfic AS UNSIGNED) DESC 
                    LIMIT " . (int)$start . ", " . (int)$length;
        $stmtData = $conexion->prepare($sqlData);
        $stmtData->execute($params);
        $data = $stmtData->fetchAll(PDO::FETCH_ASSOC);

        return [
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data
        ];
    }

    public function selApr($fic=NULL){
        $sql = "SELECT p.idusu, vtd.nomval AS tipdoc, p.ndocusu, p.nomusu, p.idper, vgn.nomval AS genusu, p.emausu, c.idcen, c.nomcen, p.actusu, p.fotcan, p.telcan, p.noca, f.idfic, f.actfic, pr.tippro, pr.codpro, fi.nomfic, v.nomval AS jornada FROM usuario AS p JOIN usufic AS f ON p.idusu=f.idusu JOIN centro c ON p.idcen = c.idcen JOIN ficha fi ON f.idfic = fi.idfic LEFT JOIN valor AS v ON fi.jornada = v.idval LEFT JOIN valor AS vgn ON p.genusu = vgn.idval LEFT JOIN valor AS vtd ON p.tdousu = vtd.idval JOIN programa pr ON fi.codpro = pr.codpro WHERE p.actusu=1";
        if($fic) {
            $sql .= " AND f.idfic = :fic";
        }
        $sql .= " ORDER BY p.nomusu";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        if($fic) {
            $result->bindParam(':fic', $fic);
        }
        $result->execute(); 
        return $result->fetchAll(PDO::FETCH_ASSOC);
    }

    public function selOne(){
        $sql = "SELECT f.idfic, f.nomfic, f.jornada, f.mun, v.nomval, f.idcen, c.nomcen, f.finific,f.ffinfic FROM ficha AS f INNER JOIN valor AS v ON f.jornada=v.idval INNER JOIN centro AS c ON f.idcen=c.idcen WHERE f.idfic=:idfic";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $idfic = $this->getIdfic();
        $result->bindParam(":idfic",$idfic);
        $result->execute(); 
        $res= $result->fetchAll(PDO::FETCH_ASSOC);
        return $res;
    }
    public function selNoApr($idfic){
        $sql = "SELECT COUNT(idusu) AS can FROM usufic WHERE idfic=:idfic";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->bindParam(":idfic",$idfic);
        $result->execute(); 
        $res= $result->fetchAll(PDO::FETCH_ASSOC);
        return $res;
    }
    
    public function ins(){
        try{
            $idfic = trim((string)$this->getIdfic());
            $nomfic = trim((string)$this->getNomfic());
            $jornada = (int)($this->getJornada() ?: 1);
            $idcen = (int)($this->getIdcen() ?: 951310);
            $mun = trim((string)($this->getMun() ?: '25175'));
            $finific = $this->getFinific() ?: date("Y-m-d");
            $ffinfic = $this->getFfinfic() ?: date("Y-m-d", strtotime("+1 year"));

            if ($idfic === '') {
                return false;
            }

            if ($nomfic === '') {
                $nomfic = "PROGRAMA DE FORMACIÓN " . $idfic;
            }

            $modelo = new conexion();
            $conexion = $modelo->get_conexion();

            /*
             * Compatibilidad entre las dos funcionalidades:
             * - Fichas requiere conservar el registro tradicional y fecregf.
             * - Carga masiva necesita codpro cuando puede determinarse.
             *
             * Se intenta resolver codpro por el nombre del programa. Si no hay
             * coincidencia, se usa el código histórico 228118 únicamente si
             * realmente existe en la tabla programa. Si tampoco existe, se
             * conserva el comportamiento anterior de Usumfic e inserta sin codpro.
             */
            $codpro = null;

            $stmtPro = $conexion->prepare(
                "SELECT codpro
                 FROM programa
                 WHERE nompro LIKE :nomfic
                 ORDER BY codpro
                 LIMIT 1"
            );
            $likeNom = "%" . $nomfic . "%";
            $stmtPro->bindValue(':nomfic', $likeNom, PDO::PARAM_STR);
            $stmtPro->execute();
            $rowPro = $stmtPro->fetch(PDO::FETCH_ASSOC);

            if ($rowPro && !empty($rowPro['codpro'])) {
                $codpro = $rowPro['codpro'];
            } else {
                $stmtFallback = $conexion->prepare(
                    "SELECT codpro FROM programa WHERE codpro = :codpro LIMIT 1"
                );
                $stmtFallback->bindValue(':codpro', 228118, PDO::PARAM_INT);
                $stmtFallback->execute();
                $rowFallback = $stmtFallback->fetch(PDO::FETCH_ASSOC);
                if ($rowFallback && !empty($rowFallback['codpro'])) {
                    $codpro = $rowFallback['codpro'];
                }
            }

            if ($codpro !== null) {
                $sql = "INSERT INTO ficha
                        (idfic, nomfic, codpro, jornada, idcen, mun, finific, ffinfic, fecregf)
                        VALUES
                        (:idfic, :nomfic, :codpro, :jornada, :idcen, :mun, :finific, :ffinfic, NOW())";
            } else {
                $sql = "INSERT INTO ficha
                        (idfic, nomfic, jornada, idcen, mun, finific, ffinfic, fecregf)
                        VALUES
                        (:idfic, :nomfic, :jornada, :idcen, :mun, :finific, :ffinfic, NOW())";
            }

            $result = $conexion->prepare($sql);
            $result->bindValue(":idfic", $idfic);
            $result->bindValue(":nomfic", $nomfic);
            if ($codpro !== null) {
                $result->bindValue(":codpro", $codpro);
            }
            $result->bindValue(":jornada", $jornada, PDO::PARAM_INT);
            $result->bindValue(":idcen", $idcen, PDO::PARAM_INT);
            $result->bindValue(":mun", $mun);
            $result->bindValue(":finific", $finific);
            $result->bindValue(":ffinfic", $ffinfic);

            return $result->execute();
        }catch(PDOException $e){
            $mensaje = $e->getMessage();

            // Clave duplicada: la ficha ya existe.
            if ($e->getCode() === '23000' || strpos($mensaje, '1062') !== false) {
                return false;
            }

            error_log("Error en Usumfic::ins(): " . $mensaje);
            return false;
        }catch(Exception $e){
            error_log("Error en Usumfic::ins(): " . $e->getMessage());
            return false;
        }
    }

    public function upd(){
        $sql = "UPDATE ficha SET nomfic=:nomfic, jornada=:jornada, mun=:mun, idcen=:idcen, finific=:finific, ffinfic=:ffinfic WHERE idfic=:idfic";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $idfic = $this->getIdfic();
        $result->bindParam(":idfic",$idfic);
        $nomfic = $this->getNomfic();
        $result->bindParam(":nomfic",$nomfic);
        $jornada = $this->getJornada();
        $result->bindParam(":jornada",$jornada);
        $idcen = $this->getIdcen();
        $result->bindParam(":idcen",$idcen);
        $mun = $this->getMun();
        $result->bindParam(":mun",$mun);
        $finific = $this->getFinific();
        $result->bindParam(":finific",$finific);
        $ffinfic = $this->getFfinfic();
        $result->bindParam(":ffinfic",$ffinfic);
        $result->execute();
    }
    public function del(){
        $sql = "DELETE FROM ficha WHERE idfic=:idfic";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $idfic = $this->getIdfic();
        $result->bindParam(":idfic",$idfic);
        $result->execute(); 
        
    }
    public function getCen(){
        $sql ="SELECT idcen, nomcen FROM centro";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->execute(); 
        $res= $result->fetchAll(PDO::FETCH_ASSOC);
        return $res;
    }
    public function getJor(){
        $sql ="SELECT idval, nomval FROM valor WHERE iddom=1";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->execute(); 
        $res= $result->fetchAll(PDO::FETCH_ASSOC);
        return $res;
    }
    public function selectficha(){
        $sql ="SELECT count(*) as sum FROM ficha WHERE idfic=:idfic";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $idfic = $this->getIdfic();
        $result->bindParam(":idfic",$idfic);
        $result->execute(); 
        $res= $result->fetchAll(PDO::FETCH_ASSOC);
        return $res;
    }
    
    public function getUbica(){
        $sql ="SELECT codubi, nomubi, depubi FROM ubica WHERE depubi=0;";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->execute(); 
        $res= $result->fetchAll(PDO::FETCH_ASSOC);
        return $res;
    }

     public function getOneActaFic($idfic) { 
        $res = null;
        $sql = "SELECT idactdc, idfic FROM acta_cierre WHERE idfic = :idfic";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $stmt = $conexion->prepare($sql);
        $stmt->execute([':idfic' => $idfic]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res;
    }

    public function getOne() {
    $sql = "SELECT * FROM acta_cierre WHERE idactdc = ?";
    $modelo = new conexion();
    $conexion = $modelo->get_conexion();
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$this->idact]);
    $res = $stmt->fetch(PDO::FETCH_ASSOC);
    return $res;
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
public function getTotalResultados($idFicha) {  
        $sql = " SELECT COUNT(DISTINCT r.idres) AS tot FROM ficha f JOIN proxcom px ON f.codpro = px.codpro JOIN competencia c ON px.idcom = c.idcom JOIN resultado r ON c.idcom = r.idcom WHERE f.idfic = :idFicha";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':idFicha', $idFicha, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        return $data;
    }

    public function normalizarEstado($estado) {
    $estadoNormalizado = strtolower(trim($estado ?? ''));
    if ($estadoNormalizado === 'aprobado') {
        return 'aprobado';
    } elseif ($estadoNormalizado === 'no aprobado') {
        return 'no aprobado';
    } else {
        return 'por evaluar';
    }
}

public function getCompetenciasYResultados($idFicha) {
    $sql = "SELECT 
                c.descom AS competencia,
                r.nomres AS resultado,
                COALESCE(j.caljui, 'Por Evaluar') AS calificacion,
                COALESCE(u.nomusu, '-') AS instructor,
                f.idfic AS ficha
            FROM ficha f
            JOIN proxcom px ON f.codpro = px.codpro
            JOIN competencia c ON px.idcom = c.idcom
            JOIN resultado r ON c.idcom = r.idcom
            LEFT JOIN juicio j 
                   ON j.idfic COLLATE utf8mb4_unicode_ci = f.idfic COLLATE utf8mb4_unicode_ci
                  AND j.resapr COLLATE utf8mb4_unicode_ci LIKE CONCAT('%', r.nomres, '%')
            LEFT JOIN usuario u ON j.idusu = u.idusu
            WHERE f.idfic = :idFicha
            ORDER BY c.idcom, r.idres";

    $modelo = new conexion();
    $conexion = $modelo->get_conexion();
    $stmt = $conexion->prepare($sql);
    $stmt->bindParam(':idFicha', $idFicha, PDO::PARAM_STR); 
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC); 
}


public function contarEstadosJuicio($idFicha) {
    $sql = "SELECT
                SUM(CASE WHEN j.caljui IS NULL OR TRIM(j.caljui) = '' THEN 1 ELSE 0 END) AS Pendiente,
                SUM(CASE WHEN LOWER(TRIM(j.caljui)) IN ('aprobado','aprob','a') THEN 1 ELSE 0 END) AS Aprobado,
                SUM(CASE WHEN LOWER(TRIM(j.caljui)) IN ('no aprobado','noaprobado','no_aprobado','noap') THEN 1 ELSE 0 END) AS NoAprobado
            FROM ficha f
            JOIN proxcom px ON f.codpro = px.codpro
            JOIN competencia c ON px.idcom = c.idcom
            JOIN resultado r ON c.idcom = r.idcom
            LEFT JOIN juicio j ON j.idfic = f.idfic AND j.idres = r.idres
            WHERE f.idfic = :idFicha";

    $modelo = new conexion();
    $conexion = $modelo->get_conexion();
    $stmt = $conexion->prepare($sql);

    if (is_numeric($idFicha)) {
        $stmt->bindValue(':idFicha', (int)$idFicha, PDO::PARAM_INT);
    } else {
        $stmt->bindValue(':idFicha', $idFicha, PDO::PARAM_STR);
    }

    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return [
        'Aprobado'     => (int)($row['Aprobado'] ?? 0),
        'No Aprobado'  => (int)($row['NoAprobado'] ?? 0),
        'Pendiente'    => (int)($row['Pendiente'] ?? 0)
    ];
}

    public function selApr2($fic) {
    $sql = "SELECT 
                p.idusu, 
                p.ndocusu AS identificacion, 
                UPPER(p.nomusu) AS nombre,
                IF(p.actusu = 1, 'EN FORMACION', 'RETIRO VOLUNTARIO') AS estado 
            FROM usuario AS p 
            INNER JOIN usufic AS f ON p.idusu = f.idusu 
            WHERE f.idfic = :fic 
            ORDER BY p.nomusu";
    
    $modelo = new conexion();
    $conexion = $modelo->get_conexion();
    $result = $conexion->prepare($sql);
    $result->bindParam(':fic', $fic);
    $result->execute(); 
    return $result->fetchAll(PDO::FETCH_ASSOC);
}


    public function selAprInactivos() { 
        $sql = "SELECT p.ndocusu AS identificacion, UPPER(p.nomusu) AS nombre,'Inactivo' AS estado FROM usuario AS p WHERE p.idper = 2 AND p.actusu = 0 ORDER BY p.nomusu";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->execute(); 
        return $result->fetchAll(PDO::FETCH_ASSOC);
    } 

   public function insertarActaCierre($idusu, $idfic, $numact) {
    $modelo = new conexion();
    $conexion = $modelo->get_conexion();

    // Verificar si ya existe esa acta para la ficha
    $sql = "SELECT idactdc FROM acta_cierre 
            WHERE idfic = :idfic AND numact = :numact LIMIT 1";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([
        ':idfic' => $idfic,
        ':numact' => $numact
    ]);

    if ($stmt->rowCount() > 0) {
        // Ya existe esa acta para esta ficha
        return false;
    }

    // Insertar nueva acta
    $sql = "INSERT INTO acta_cierre (idusu, idfic, numact, fecregdec)
            VALUES (:idusu, :idfic, :numact, NOW())";
    $stmt = $conexion->prepare($sql);

    if ($stmt->execute([
        ':idusu' => $idusu,
        ':idfic' => $idfic,
        ':numact' => $numact
    ])) {
        return $conexion->lastInsertId();
    } else {
        return false;
    }
}


    public function getAprendicesDesercion($idfic) {
    $sql = "SELECT u.idusu, u.ndocusu AS identificacion, u.nomusu AS nombre, d.obsdec, d.fecregdec
            FROM desercion d
            INNER JOIN usuario u ON d.idusu = u.idusu
            WHERE d.idfic = :idfic";
    $modelo = new conexion();
    $conexion = $modelo->get_conexion();
    $stmt = $conexion->prepare($sql);
    $stmt->bindParam(':idfic', $idfic);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function obtenerImagenesUsuario($idusu) {
    $modelo = new conexion();
    $conexion = $modelo->get_conexion();
    $sql = "SELECT nomimg FROM imagen_usuario WHERE idusu = :idusu ORDER BY fecha DESC";
    $stmt = $conexion->prepare($sql);
    $stmt->bindParam(':idusu', $idusu);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

 public function getOneAcDe(){
		$res=NULL;
        $sql = "SELECT a.idacde, a.idact, a.desacde, a.fecacde, a.idusu, u.ndocusu, u.nomusu FROM actdec AS a INNER JOIN usuario AS u ON a.idusu=u.idusu WHERE a.idact=:idact";
        $modelo = new conexion();
        $conexion= $modelo->get_conexion();
        $result= $conexion-> prepare($sql);
        $idact= $this->getIdact();
        $result->bindParam(":idact", $idact);
        $result -> execute();
        $res = $result->fetchAll(PDO::FETCH_ASSOC);
        return $res;
	}

    public function getAllActasFic($idfic) {  
    $sql = "SELECT 
                idactdc,
                idfic,
                numact,
                fecregdec
            FROM acta_cierre  
            WHERE idfic = :idfic
            ORDER BY numact ASC";  
    
    $modelo = new conexion();
    $conexion = $modelo->get_conexion();
    $stmt = $conexion->prepare($sql);
    $stmt->execute([':idfic' => $idfic]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


    public function getActaFic() {
    $html = '<!DOCTYPE html>';
    $html .= '<html lang="es">';
    $html .= '<head>';
    $html .= '    <meta charset="utf-8">';
    $html .= '    <meta name="viewport" content="width=device-width, initial-scale=1">';
    $html .= '    <title>' . $this->getTit() . '</title>';
    
    $html .= '    <style>';
    $html .= '        body { font-family: Arial, Verdana; font-size: 11px; margin: 20px; }';
    $html .= '        table { width: 100%; border-collapse: collapse; }';
    $html .= '        th, td { border: 1px solid #000; padding: 5px; text-align: justify; vertical-align: top; }';
    $html .= '        th { background-color: #f2f2f2; text-align: center; }';
    $html .= '        .bold { font-weight: bold; }';
    $html .= '        .center { text-align: center; }';
    $html .= '        .logo { width: 80px; }';
    $html .= '        .no-border { border: none; }';
    $html .= '    </style>';
    $html .= '</head>';
    $html .= '<body>';
    $html .= '<div style="width: 60%; margin: 0 auto;">';

    
    $html .= '<header>';
    $html .= '    <table class="no-border">';
$html .= '<tr><td class="no-border" style="text-align:center;">';
$html .= '<img src="../image/logocom.png" class="logo" alt="Logo">';
$html .= '</td></tr>';
    $html .= '    </table>';
    $html .= '</header>';

    $html .= '<section>';
    $html .= '    <h2 class="center">ACTA No. ' . str_pad($this->getNac(), 3, "0", STR_PAD_LEFT) . '</h2>';
    $html .= '    <table>';
    $html .= '        <tr><td colspan="5" class="bold">NOMBRE DEL COMITÉ O DE LA REUNIÓN:<br>' . $this->getNcre() . '</td></tr>';
    $html .= '        <tr>';
    $html .= '            <td colspan="2" class="bold">CIUDAD Y FECHA:<br>' . $this->getCife() . '</td>';
    $html .= '            <td colspan="2" class="bold">HORA INICIO:<br>' . $this->getHini() . '</td>';
    $html .= '            <td class="bold">HORA FIN:<br>' . $this->getHfin() . '</td>';
    $html .= '        </tr>';
    $html .= '        <tr>';
    $html .= '            <td colspan="2" class="bold">LUGAR Y/O ENLACE:<br>' . $this->getJgen() . '</td>';
    $html .= '            <td colspan="3" class="bold">DIRECCIÓN / REGIONAL / CENTRO:<br>' . $this->getDrcd() . '</td>';
    $html .= '        </tr>';
    $html .= '        <tr><td colspan="5"><span class="bold">AGENDA O PUNTOS PARA DESARROLLAR:</span><br>' . $this->getAgep() . '</td></tr>';
    $html .= '        <tr><td colspan="5"><span class="bold">OBJETIVO(S) DE LA REUNIÓN:</span><br>' . $this->getObjr() . '</td></tr>';

    
    $html .= '        <tr><th colspan="5">DESARROLLO DE LA REUNIÓN</th></tr>';
    $html .= '        <tr><td colspan="5">' . $this->getDesr() . '</td></tr>';
    
    						$html .= '<tr><th colspan="5">CONCLUSIONES</th></tr>';
					$html .= '<tr>';
    						$html .= '<td colspan="5">' . $this->getConc() . '</td>';
					$html .= '</tr>';

    $html .= '<footer>';
    $html .= '    <table class="no-border">';
    $html .= '        <tr><td class="center">GOR-F-084V01</td></tr>';
    $html .= '    </table>';
    $html .= '</footer>';
    $html .= '</div>'; 
    $html .= '</body>';
    $html .= '</html>';

    return $html;
}

	public function getDashboard(){
		try{
			$sql = "SELECT
					(SELECT COUNT(*) FROM ficha) AS total,
					(SELECT COUNT(*) FROM ficha WHERE finific <= CURDATE() AND ffinfic >= CURDATE()) AS activas,
					(SELECT COUNT(*) FROM ficha WHERE ffinfic < CURDATE()) AS finalizadas";
			$modelo = new conexion();
			$conexion = $modelo->get_conexion();
			$result = $conexion->prepare($sql);
			$result->execute();
			$res = $result->fetchAll(PDO::FETCH_ASSOC);
			return $res;
		}catch(Exception $e){
			error_log("Error en Usumfic::getDashboard(): " . $e->getMessage());
			return [];
		}
	}

	public function getUltimaFicha(){
		try{
			$sql = "SELECT f.idfic, f.nomfic, f.finific, f.ffinfic, v.nomval AS nomjornada, c.nomcen, f.mun,
					(SELECT COUNT(*) FROM usufic AS u WHERE u.idfic = f.idfic) AS napr
				FROM ficha AS f
				INNER JOIN valor AS v ON f.jornada = v.idval
				INNER JOIN centro AS c ON f.idcen = c.idcen
				ORDER BY COALESCE(f.fecregf, '1970-01-01 00:00:00') DESC, CAST(f.idfic AS UNSIGNED) DESC LIMIT 1";
			$modelo = new conexion();
			$conexion = $modelo->get_conexion();
			$result = $conexion->prepare($sql);
			$result->execute();
			$res = $result->fetchAll(PDO::FETCH_ASSOC);
			return $res;
		}catch(Exception $e){
			error_log("Error en Usumfic::getUltimaFicha(): " . $e->getMessage());
			return [];
		}
	}



    // -----------------------------------------------------------------
    // Métodos integrados desde Mfic para soportar carga masiva y fichas
    // -----------------------------------------------------------------

    public function getCountFichas($idcen = null) {
            $sql = "SELECT COUNT(*) as total FROM ficha";
            if ($idcen) {
                $sql .= " WHERE idcen = :idcen";
            }
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $stmt = $conexion->prepare($sql);
            if ($idcen) {
                $stmt->bindParam(':idcen', $idcen);
            }
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)($row['total'] ?? 0);
        }

    public function getCountFichasConAprendices($idcen = null) {
            $sql = "SELECT COUNT(DISTINCT f.idfic) as total FROM ficha f INNER JOIN usufic uf ON f.idfic = uf.idfic";
            if ($idcen) {
                $sql .= " WHERE f.idcen = :idcen";
            }
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $stmt = $conexion->prepare($sql);
            if ($idcen) {
                $stmt->bindParam(':idcen', $idcen);
            }
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)($row['total'] ?? 0);
        }

    public function existeFicha($idfic) {
            $idfic = trim((string)$idfic);
            if ($idfic === '') return false;
            $sql = "SELECT COUNT(*) as total FROM ficha WHERE idfic = :idfic";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(':idfic', $idfic);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return ((int)($row['total'] ?? 0)) > 0;
        }

    public function crearFichaRapida($idfic, $nomfic = '', $jornada = 1, $idcen = 951310, $mun = '25175') {
            $idfic = trim((string)$idfic);
            if ($idfic === '') return ['creada' => false, 'existe' => false, 'error' => 'Código de ficha vacío'];

            if ($this->existeFicha($idfic)) {
                return ['creada' => false, 'existe' => true, 'idfic' => $idfic];
            }

            $this->setIdfic($idfic);
            $this->setNomfic($nomfic ?: ("PROGRAMA DE FORMACIÓN " . $idfic));
            $this->setJornada($jornada ?: 1);
            $this->setIdcen($idcen ?: 951310);
            $this->setMun($mun ?: '25175');
            $this->setFinific(date("Y-m-d"));
            $this->setFfinfic(date("Y-m-d", strtotime("+1 year")));

            $ok = $this->ins();
            return [
                'creada' => $ok,
                'existe' => !$ok,
                'idfic' => $idfic,
                'nomfic' => $this->getNomfic()
            ];
        }

    public function getFichaDetalle($idfic) {
            $idfic = trim((string)$idfic);
            if (empty($idfic)) return null;
            $sql = "SELECT f.idfic, f.nomfic, f.jornada, v.nomval AS nom_jornada, f.idcen, c.nomcen, f.mun 
                    FROM ficha f 
                    LEFT JOIN valor v ON f.jornada = v.idval 
                    LEFT JOIN centro c ON f.idcen = c.idcen 
                    WHERE f.idfic = :idfic";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(':idfic', $idfic);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }

}

// Alias temporal para compatibilidad con controladores antiguos que aún instancien Mfic.
if (!class_exists('Mfic', false)) {
    class_alias('Usumfic', 'Mfic');
}
