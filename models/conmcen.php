<?php
class Mcen{
private $idcen;
private $nomcen;
private $dircen;
private $telcen;
private $imgcen;
private $logocen;
private $descen;
private $fiicancen;
private $fficancen;
private $fiprocen;
private $ffprocen;
private $fivotcen;
private $ffvotcen;
// Metodo Get
public function getIdcen(){
    return $this->idcen;
}
public function getNomcen(){
    return $this->nomcen;
}
public function getDircen(){
    return $this->dircen;
}
public function getTelcen(){
    return $this->telcen;
}
public function getImgcen(){
    return $this->imgcen;
}
public function getLogocen(){
    return $this->logocen;
}
public function getDescen(){
    return $this->descen;
}
public function getFiicancen(){
    return $this->fiicancen;
}
public function getFficancen(){
    return $this->fficancen;
}
public function getFiprocen(){
    return $this->fiprocen;
}
public function getFfprocen(){
    return $this->ffprocen;
}
public function getFivotcen(){
    return $this->fivotcen;
}
public function getFfvotcen(){
    return $this->ffvotcen;
}

    // Metodos SET
public function setIdcen($idcen){
    $this->idcen=$idcen;
}
public function setNomcen($nomcen){
    $this->nomcen=$nomcen;
}
public function setDircen($dircen){
    $this->dircen=$dircen;
}
public function setTelcen($telcen){
    $this->telcen=$telcen;
}
public function setImgcen($imgcen){
    $this->imgcen=$imgcen;
}
public function setLogocen($logocen){
    $this->logocen=$logocen;
}
public function setDescen($descen){
    $this->descen=$descen;
}
public function setFiicancen($fiicancen){
    $this->fiicancen=$fiicancen;
}
public function setFficancen($fficancen){
    $this->fficancen=$fficancen;
}
public function setFiprocen($fiprocen){
    $this->fiprocen=$fiprocen;
}
public function setFfprocen($ffprocen){
    $this->ffprocen=$ffprocen;
}
public function setFivotcen($fivotcen){
    $this->fivotcen=$fivotcen;
}
public function setFfvotcen($ffvotcen){
    $this->ffvotcen=$ffvotcen;
}

function getAll(){
    try {
        $sql = "SELECT idcen, nomcen, dircen, telcen, imgcen, logocen, descen, fiicancen, fficancen, fiprocen, ffprocen, fivotcen, ffvotcen FROM centro ORDER BY nomcen";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->execute();
        return $result->fetchall(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        return array();
    }
}

function getOne(){
    try {
        $sql = "SELECT idcen, nomcen, dircen, telcen, imgcen, logocen, descen, fiicancen, fficancen, fiprocen, ffprocen, fivotcen, ffvotcen FROM centro WHERE idcen=:idcen";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $idcen = $this->getIdcen();
        $result->bindParam(":idcen",$idcen);
        $result->execute(); 
        return $result->fetchall(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        return array();
    }
}

function save(){
    try {
        $sql = "INSERT INTO centro(idcen, nomcen, dircen, telcen, imgcen, logocen, descen, fiicancen, fficancen, fiprocen, ffprocen, fivotcen, ffvotcen)VALUES(:idcen, :nomcen, :dircen, :telcen, :imgcen, :logocen, :descen, :fiicancen, :fficancen, :fiprocen, :ffprocen, :fivotcen, :ffvotcen)";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);

        $idcen = $this->getIdcen();
        $result->bindParam(":idcen",$idcen);

        $nomcen = $this->getNomcen();
        $result->bindParam(":nomcen",$nomcen);

        $dircen = $this->getDircen();
        $result->bindParam(":dircen",$dircen);

        $telcen = $this->getTelcen();
        $result->bindParam(":telcen",$telcen);

        $imgcen = $this->getImgcen();
        $result->bindParam(":imgcen",$imgcen);

        $logocen = $this->getLogocen();
        $result->bindParam(":logocen",$logocen);

        $descen = $this->getDescen();
        $result->bindParam(":descen",$descen);

        $fiicancen = $this->getFiicancen();
        $result->bindParam(":fiicancen",$fiicancen);

        $fficancen = $this->getFficancen();
        $result->bindParam(":fficancen",$fficancen);

        $fiprocen = $this->getFiprocen();
        $result->bindParam(":fiprocen",$fiprocen);

        $ffprocen = $this->getFfprocen();
        $result->bindParam(":ffprocen",$ffprocen);

        $fivotcen = $this->getFivotcen();
        $result->bindParam(":fivotcen",$fivotcen);

        $ffvotcen = $this->getFfvotcen();
        $result->bindParam(":ffvotcen",$ffvotcen);

        $result->execute();
        return true;
    } catch (Throwable $e) {
        return false;
    }
}

function edit(){
    try {
        $imgcen = $this->getImgcen();
        $logocen = $this->getLogocen();
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $sql = "UPDATE centro SET nomcen=:nomcen, dircen=:dircen, telcen=:telcen, ";
        if($imgcen){
            $sql .= "imgcen=:imgcen, ";
        }
        if($logocen){
            $sql .= "logocen=:logocen, ";
        }
        $sql .= "descen=:descen, fiicancen=:fiicancen, fficancen=:fficancen, fiprocen=:fiprocen, ffprocen=:ffprocen, fivotcen=:fivotcen, ffvotcen=:ffvotcen WHERE idcen=:idcen";
        $result = $conexion->prepare($sql);
        $idcen = $this->getIdcen();
        $result->bindParam(":idcen",$idcen);
        $nomcen = $this->getNomcen();
        $result->bindParam(":nomcen",$nomcen);
        $dircen = $this->getDircen();
        $result->bindParam(":dircen",$dircen);
        $telcen = $this->getTelcen();
        $result->bindParam(":telcen",$telcen);
        if($imgcen){
            $result->bindParam(":imgcen",$imgcen);
        }
        if($logocen){
            $result->bindParam(":logocen",$logocen);
        }
        $descen = $this->getDescen();
        $result->bindParam(":descen",$descen);
        $fiicancen = $this->getFiicancen();
        $result->bindParam(":fiicancen",$fiicancen);
        $fficancen = $this->getFficancen();
        $result->bindParam(":fficancen",$fficancen);
        $fiprocen = $this->getFiprocen();
        $result->bindParam(":fiprocen",$fiprocen);
        $ffprocen = $this->getFfprocen();
        $result->bindParam(":ffprocen",$ffprocen);
        $fivotcen = $this->getFivotcen();
        $result->bindParam(":fivotcen",$fivotcen);
        $ffvotcen = $this->getFfvotcen();
        $result->bindParam(":ffvotcen",$ffvotcen);
        $result->execute();
        return true;
    } catch (Throwable $e) {
        return false;
    }
}
function del(){
    try {
        $sql = "DELETE FROM centro WHERE idcen=:idcen";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $idcen = $this->getIdcen();
        $result->bindParam(":idcen",$idcen);
        $result->execute();
        return true;
    } catch (Throwable $e) {
        return false;
    }
}

function getCrl(){
    try {
        $sql = "SELECT COUNT(*) AS con FROM (SELECT idcen FROM usuario WHERE idcen=:idcen UNION ALL SELECT idcen FROM ficha WHERE idcen=:idcen UNION ALL SELECT idcen FROM aula WHERE idcen=:idcen) AS rel";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $idcen = $this->getIdcen();
        $result->bindParam(":idcen",$idcen);
        $result->execute();
        return $result->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        return array();
    }
}

// Conteos de asociación por centro (personas, fichas y candidatos)
function getInfo(){
    try {
        $sql = "SELECT
                (SELECT COUNT(*) FROM usuario WHERE idcen=:idcen) AS total_usu,
                (SELECT COUNT(*) FROM ficha WHERE idcen=:idcen) AS total_fic,
                (SELECT COUNT(*) FROM usupef up INNER JOIN usuario u ON up.idusu=u.idusu WHERE u.idcen=:idcen AND up.idper IN (3,13)) AS total_can,
                (SELECT COUNT(*) FROM aula WHERE idcen=:idcen) AS total_aul";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $idcen = $this->getIdcen();
        $result->bindParam(":idcen",$idcen);
        $result->execute();
        return $result->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        return array();
    }
}

// Candidatos (representante idper=3 y vocero idper=13) asociados a un centro
function getCand(){
    try {
        $sql = "SELECT u.idusu, u.ndocusu, u.nomusu, u.telcan, p.idper, p.nomper,
                (SELECT COUNT(*) FROM voto WHERE canusu = u.idusu) AS votos
                FROM usupef up
                INNER JOIN perfil p ON up.idper = p.idper
                INNER JOIN usuario u ON u.idusu = up.idusu
                WHERE u.idcen = :idcen AND up.idper IN (3, 13)
                ORDER BY u.nomusu";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $idcen = $this->getIdcen();
        $result->bindParam(":idcen",$idcen);
        $result->execute();
        return $result->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        return array();
    }
}

// Totales globales para el dashboard (centros, asociados, fichas, aulas y candidatos)
function getTotales(){
    try {
        $sql = "SELECT
                (SELECT COUNT(*) FROM centro) AS total_cen,
                (SELECT COUNT(*) FROM usuario) AS total_usu,
                (SELECT COUNT(*) FROM ficha) AS total_fic,
                (SELECT COUNT(*) FROM aula) AS total_aul,
                (SELECT COUNT(*) FROM usupef up INNER JOIN usuario u ON up.idusu=u.idusu WHERE up.idper IN (3,13)) AS total_can";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->execute();
        return $result->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        return array(array('total_cen' => 0, 'total_usu' => 0, 'total_fic' => 0, 'total_aul' => 0, 'total_can' => 0));
    }
}

}
?>