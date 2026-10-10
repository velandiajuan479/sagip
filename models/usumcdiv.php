<?php
class Usumcdiv
{
    private $idCind;
    private $idUsu;
    private $idTipDoc;
    private $rutPdf;
    private $fecSub;
    private $aprEst;
    private $aprObs;
    private $fecApr;
    private $idUsuApr;
    private $db;

    public function __construct()
    {
        require_once(__DIR__ . '/conexion.php');
        $cnx = new conexion();
        $this->db = $cnx->get_conexion();
    }

    public function getIdCind(){return $this->idCind;}
    public function setIdCind($v){$this->idCind=$v;}
    public function getIdUsu(){return $this->idUsu;}
    public function setIdUsu($v){$this->idUsu=$v;}
    public function getIdTipDoc(){return $this->idTipDoc;}
    public function setIdTipDoc($v){$this->idTipDoc=$v;}
    public function getRutPdf(){return $this->rutPdf;}
    public function setRutPdf($v){$this->rutPdf=$v;}
    public function getFecSub(){return $this->fecSub;}
    public function setFecSub($v){$this->fecSub=$v;}
    public function getAprEst(){return $this->aprEst;}
    public function setAprEst($v){$this->aprEst=$v;}
    public function getAprObs(){return $this->aprObs;}
    public function setAprObs($v){$this->aprObs=$v;}
    public function getFecApr(){return $this->fecApr;}
    public function setFecApr($v){$this->fecApr=$v;}
    public function getIdUsuApr(){return $this->idUsuApr;}
    public function setIdUsuApr($v){$this->idUsuApr=$v;}

    public function getDocTipos()
    {
        try {
            $sql = "SELECT idval AS idTipDoc, nomval AS nomTipDoc FROM valor WHERE iddom = 27 AND act = 1 ORDER BY nomval ASC";
            return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getDocTipos usumcdiv: ".$e->getMessage());
            return null;
        }
    }

    public function getAll($idUsu = null, $idCen = null)
    {
        try {
            $sql = "SELECT c.idCind,c.idUsu,c.idTipDoc,c.rutPdf,c.fecSub,c.aprEst,c.aprObs,c.fecApr,c.idUsuApr,v.nomval AS nomTipDoc,u.nomusu FROM carga_individual c INNER JOIN valor v ON v.idval=c.idTipDoc INNER JOIN usuario u ON u.idusu=c.idUsu WHERE 1=1";
            $params=[];
            if ($idUsu !== null){$sql.=" AND c.idUsu=:idUsu";$params[':idUsu']=$idUsu;}
            if ($idCen !== null){$sql.=" AND u.idcen=:idCen";$params[':idCen']=$idCen;}
            $sql.=" ORDER BY c.idCind DESC";
            $st=$this->db->prepare($sql);$st->execute($params);return $st->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {error_log("Error getAll usumcdiv: ".$e->getMessage());return null;}
    }

    public function getOne()
    {
        try {
            $sql="SELECT c.idCind,c.idUsu,c.idTipDoc,c.rutPdf,c.fecSub,c.aprEst,c.aprObs,c.fecApr,c.idUsuApr,v.nomval AS nomTipDoc,u.nomusu FROM carga_individual c INNER JOIN valor v ON v.idval=c.idTipDoc INNER JOIN usuario u ON u.idusu=c.idUsu WHERE c.idCind=:idCind";
            $st=$this->db->prepare($sql);$st->bindParam(':idCind',$this->idCind);$st->execute();return $st->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {error_log("Error getOne usumcdiv: ".$e->getMessage());return null;}
    }

    public function getOneByUsuarioTipo($idUsu,$idTipDoc)
    {
        try {
            $sql="SELECT * FROM carga_individual WHERE idUsu=:idUsu AND idTipDoc=:idTipDoc ORDER BY idCind DESC";
            $st=$this->db->prepare($sql);$st->bindParam(':idUsu',$idUsu);$st->bindParam(':idTipDoc',$idTipDoc);$st->execute();return $st->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {error_log("Error getOneByUsuarioTipo usumcdiv: ".$e->getMessage());return null;}
    }

    public function save()
    {
        try {
            $sql="INSERT INTO carga_individual (idUsu,idTipDoc,rutPdf,fecSub,aprEst) VALUES (:idUsu,:idTipDoc,:rutPdf,NOW(),0)";
            $st=$this->db->prepare($sql);$st->bindParam(':idUsu',$this->idUsu);$st->bindParam(':idTipDoc',$this->idTipDoc);$st->bindParam(':rutPdf',$this->rutPdf);return $st->execute();
        } catch (Exception $e) {error_log("Error save usumcdiv: ".$e->getMessage());return false;}
    }

    public function edit()
    {
        try {
            $sql="UPDATE carga_individual SET idTipDoc=:idTipDoc,rutPdf=:rutPdf WHERE idCind=:idCind";
            $st=$this->db->prepare($sql);$st->bindParam(':idTipDoc',$this->idTipDoc);$st->bindParam(':rutPdf',$this->rutPdf);$st->bindParam(':idCind',$this->idCind);return $st->execute();
        } catch (Exception $e) {error_log("Error edit usumcdiv: ".$e->getMessage());return false;}
    }

    public function cambiarEstado($estado,$obs=null,$idUsuApr=null)
    {
        try {
            $sql="UPDATE carga_individual SET aprEst=:aprEst,aprObs=:aprObs,fecApr=NOW(),idUsuApr=:idUsuApr WHERE idCind=:idCind";
            $st=$this->db->prepare($sql);$st->bindParam(':aprEst',$estado);$st->bindParam(':aprObs',$obs);$st->bindParam(':idUsuApr',$idUsuApr);$st->bindParam(':idCind',$this->idCind);return $st->execute();
        } catch (Exception $e) {error_log("Error cambiarEstado usumcdiv: ".$e->getMessage());return false;}
    }

    public function del()
    {
        try {$st=$this->db->prepare("DELETE FROM carga_individual WHERE idCind=:idCind");$st->bindParam(':idCind',$this->idCind);return $st->execute();} catch (Exception $e) {error_log("Error del usumcdiv: ".$e->getMessage());return false;}
    }

    public function getDashboardMetrics($idUsu=null,$idCen=null)
    {
        try {
            $sql="SELECT COUNT(*) AS total_docs,SUM(CASE WHEN aprEst=1 THEN 1 ELSE 0 END) AS total_aprobados,SUM(CASE WHEN aprEst=0 THEN 1 ELSE 0 END) AS total_pendientes,SUM(CASE WHEN aprEst=2 THEN 1 ELSE 0 END) AS total_rechazados FROM carga_individual c INNER JOIN usuario u ON u.idusu=c.idUsu WHERE 1=1";
            $params=[];if($idUsu!==null){$sql.=" AND c.idUsu=:idUsu";$params[':idUsu']=$idUsu;}if($idCen!==null){$sql.=" AND u.idcen=:idCen";$params[':idCen']=$idCen;}
            $st=$this->db->prepare($sql);$st->execute($params);return $st->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {error_log("Error getDashboardMetrics usumcdiv: ".$e->getMessage());return null;}
    }
}
