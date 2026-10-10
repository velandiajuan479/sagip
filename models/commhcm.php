<?php

class Mhorc
{
    private $iddia;
    private $hinihor;
    private $hfinhor;
    private $idnorad;
    private $idaul;
    private $idusu;

    public function getIddia() { return $this->iddia; }
    public function getHinihor() { return $this->hinihor; }
    public function getHfinhor() { return $this->hfinhor; }
    public function getIdnorad() { return $this->idnorad; }
    public function getIdusu() { return $this->idusu; }
    public function setIddia($iddia) { $this->iddia = $iddia; }
    public function setHinihor($hinihor) { $this->hinihor = $hinihor; }
    public function setHfinhor($hfinhor) { $this->hfinhor = $hfinhor; }
    public function setIdnorad($idnorad) { $this->idnorad = $idnorad; }
    public function setIdusu($idusu) { $this->idusu = $idusu; }

    public function del()
    {
        $sql = "DELETE FROM horario WHERE id=:iddia AND hinihor=:hinihor AND hfinhor=:hfinhor";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $iddia = $this->getIddia();
        $result->bindParam(':iddia', $iddia);
        $hinihor = $this->getHinihor();
        $result->bindParam(':hinihor', $hinihor);
        $hfinhor = $this->getHfinhor();
        $result->bindParam(':hfinhor', $hfinhor);
        $result->execute();
    }

    public function delPorFecha()
    {
        try {
            $sql = "DELETE FROM horario WHERE idnorad=:idnorad AND fecha_especifica=:fecha";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $idnorad = $this->getIdnorad();
            $result->bindParam(':idnorad', $idnorad);
            $fecha = $this->getIddia();
            $result->bindParam(':fecha', $fecha);
            return $result->execute();
        } catch (Exception $e) {
            return false;
        }
    }

    public function getAllVal($iddom)
    {
        $sql = "SELECT idval, nomval FROM valor WHERE iddom=:iddom";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->bindParam(':iddom', $iddom);
        $result->execute();
        $res = $result->fetchAll(PDO::FETCH_ASSOC);
        return $res;
    }

    public function getHorario()
    {
        $sql = "SELECT * FROM horarios WHERE iddia=:iddia and hinihor=:hinihor and hfinhor=:hfinhor";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $iddia = $this->getIddia();
        $result->bindParam(':iddia', $iddia);
        $hinihor = $this->getHinihor();
        $result->bindParam(':hinihor', $hinihor);
        $hfinhor = $this->getHfinhor();
        $result->bindParam(':hfinhor', $hfinhor);
        $result->execute();
        $res = $result->fetchAll(PDO::FETCH_ASSOC);
        return $res;
    }

    public function save()
    {
        try {
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            
            $sql = "INSERT INTO horario (fecha_especifica, hinihor, hfinhor, idnorad, idfic, idusu, iddia) 
                    VALUES (:fecha, :hini, :hfin, :idnorad, '0', :idusu, 0)";
            
            $result = $conexion->prepare($sql);
            
            $fecha = $this->getIddia();
            $hini = $this->getHinihor();
            $hfin = $this->getHfinhor();
            $idnorad = $this->getIdnorad();
            $idusu = $this->getIdusu() ? $this->getIdusu() : 1;

            $result->bindParam(':fecha', $fecha);
            $result->bindParam(':hini', $hini);
            $result->bindParam(':hfin', $hfin);
            $result->bindParam(':idnorad', $idnorad);
            $result->bindParam(':idusu', $idusu);
            
            return $result->execute();
        } catch (Exception $e) {
            return false;
        }
    }

    public function edit()
    {
        $sql = "UPDATE horario SET iddia=:iddia, hinihor=:hinihor, hfinhor=:hfinhor WHERE id=:iddia";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $iddia = $this->getIddia();
        $result->bindParam(':iddia', $iddia);
        $hinihor = $this->getHinihor();
        $result->bindParam(':hinihor', $hinihor);
        $hfinhor = $this->getHfinhor();
        $result->bindParam(':hfinhor', $hfinhor);
        $result->execute();
    }

    public function getAll()
    {
        $sql = "SELECT h.*, u.nomusu FROM horario h 
                LEFT JOIN usuario u ON h.idusu = u.idusu 
                WHERE h.idnorad=:idnorad 
                ORDER BY h.fecha_especifica ASC, h.hinihor ASC";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $idnorad = $this->getIdnorad();
        $result->bindParam(':idnorad', $idnorad);
        $result->execute();
        $res = $result->fetchAll(PDO::FETCH_ASSOC);
        return $res;
    }

    public function getOne()
    {
        $sql = "SELECT * FROM horario WHERE iddia=:iddia";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $iddia = $this->getIddia();
        $result->bindParam(':iddia', $iddia);
        $result->execute();
        $res = $result->fetchAll(PDO::FETCH_ASSOC);
        return $res;
    }

    public function getAllInstructores() {
        $sql = "SELECT idusu, nomusu FROM usuario WHERE idper IN (7, 12, 27, 32, 36, 42) ORDER BY nomusu";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->execute();
        return $result->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getInstructoresByNorad($idnorad) {
        $sql = "SELECT h.idusu, u.nomusu FROM hdtxusu h INNER JOIN usuario u ON h.idusu = u.idusu WHERE h.idnorad = :idnorad";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->bindParam(':idnorad', $idnorad);
        $result->execute();
        return $result->fetchAll(PDO::FETCH_ASSOC);
    }

    public function saveInstructorToNorad($idnorad, $idusu) {
        try {
            $sqlCheck = "SELECT idusu FROM hdtxusu WHERE idnorad = :idnorad AND idusu = :idusu";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $check = $conexion->prepare($sqlCheck);
            $check->bindParam(':idnorad', $idnorad);
            $check->bindParam(':idusu', $idusu);
            $check->execute();
            
            if($check->rowCount() == 0){
                $sql = "INSERT INTO hdtxusu (idnorad, idusu) VALUES (:idnorad, :idusu)";
                $result = $conexion->prepare($sql);
                $result->bindParam(':idnorad', $idnorad);
                $result->bindParam(':idusu', $idusu);
                return $result->execute();
            }
            return true;
        } catch (Exception $e) { return false; }
    }

    public function deleteInstructorFromNorad($idusu, $idnorad) {
        try {
            $sql = "DELETE FROM hdtxusu WHERE idusu = :idusu AND idnorad = :idnorad";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->bindParam(':idusu', $idusu);
            $result->bindParam(':idnorad', $idnorad);
            return $result->execute();
        } catch (Exception $e) { return false; }
    }

    public function hayCruceHorario($idnorad, $fecha, $idusu, $hini, $hfin) {
        try {
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();

            $sql = "SELECT COUNT(*) AS c FROM horario 
                    WHERE idusu = :idusu AND fecha_especifica = :fecha 
                    AND hinihor < :hfin AND hfinhor > :hini";
            $result = $conexion->prepare($sql);
            $result->bindParam(':idusu', $idusu);
            $result->bindParam(':fecha', $fecha);
            $result->bindParam(':hini', $hini);
            $result->bindParam(':hfin', $hfin);
            $result->execute();
            $row = $result->fetch(PDO::FETCH_ASSOC);
            if (!empty($row['c'])) {
                return 2;
            }

            $sql = "SELECT COUNT(*) AS c FROM horario 
                    WHERE idnorad = :idnorad AND fecha_especifica = :fecha AND idusu <> :idusu 
                    AND hinihor < :hfin AND hfinhor > :hini";
            $result = $conexion->prepare($sql);
            $result->bindParam(':idnorad', $idnorad);
            $result->bindParam(':fecha', $fecha);
            $result->bindParam(':idusu', $idusu);
            $result->bindParam(':hini', $hini);
            $result->bindParam(':hfin', $hfin);
            $result->execute();
            $row = $result->fetch(PDO::FETCH_ASSOC);
            if (!empty($row['c'])) {
                return 1;
            }
            return 0;
        } catch (Exception $e) {
            return -1;
        }
    }

    public function getFechasPorInstructor() {
        try {
            $sql = "SELECT idusu, fecha_especifica, hinihor, hfinhor FROM horario WHERE fecha_especifica IS NOT NULL";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->execute();
            return $result->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
}