<?php

class Etamgeins {

    // Atributos
    private $idusu;
    private $idficha;

    // Getters
    public function getIdusu(){ return $this->idusu; }
    public function getIdficha(){ return $this->idficha; }

    // Setters
    public function setIdusu($idusu){ $this->idusu = $idusu; }
    public function setIdficha($idficha){ $this->idficha = $idficha; }

    // --- MÉTODOS DE USUARIOS E INSTRUCTORES ---
    public function getInst() {
        $sql = "SELECT idusu, nomusu FROM usuario WHERE idper=7";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->execute();
        return $result->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Trae TODOS los instructores de seguimiento (idper=32) con sus datos completos.
     * Incluye a los que no tienen fichas asignadas.
     */
    public function getInstSegCompleto() {
        $sql = "SELECT idusu, nomusu, ndocusu, emausu, telcan 
                FROM usuario 
                WHERE idper = 32 
                ORDER BY nomusu";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->execute();
        return $result->fetchAll(PDO::FETCH_ASSOC);
    }

    public function convertirASeguimiento() {
        $sql = "UPDATE usuario SET idper = 32 WHERE idusu = :idusu";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $idusu = $this->getIdusu();
        $result->bindParam(':idusu', $idusu);
        return $result->execute();
    }

    // --- MÉTODOS DE FICHAS ---
    public function getFichasDisponibles() {
        $sql = "SELECT f.idfic, f.nomfic 
                FROM ficha AS f 
                WHERE f.idfic NOT IN (SELECT idficha FROM insseg)
                ORDER BY f.idfic";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->execute();
        return $result->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getFichasPorInstructor() {
        $sql = "SELECT DISTINCT f.idfic, f.nomfic 
                FROM insseg AS s 
                JOIN ficha AS f ON s.idficha = f.idfic 
                WHERE s.idusu = :idusu";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $idusu = $this->getIdusu();
        $result->bindParam(':idusu', $idusu);
        $result->execute();
        return $result->fetchAll(PDO::FETCH_ASSOC);
    }

    // --- ASIGNACIONES ---
    public function asignarFicha() {
        $sql = "INSERT INTO insseg (idusu, idficha) VALUES (:idusu, :idficha)";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $idusu = $this->getIdusu();
        $result->bindParam(':idusu', $idusu);
        $idficha = $this->getIdficha();
        $result->bindParam(':idficha', $idficha);
        return $result->execute();
    }

    public function eliminarInsSeg() {
        $idusu = $this->getIdusu();
        $idficha = $this->getIdficha();
        $sql = "DELETE FROM insseg WHERE idusu = :idusu AND idficha = :idficha";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->bindParam(':idusu', $idusu);
        $result->bindParam(':idficha', $idficha);
        return $result->execute();
    }

    /**
     * Elimina al instructor de seguimiento:
     *  1) Borra todas sus asignaciones de fichas en `insseg`.
     *  2) Revierte su perfil al de instructor normal (idper = 7).
     */
    public function eliminarInstructorSeg() {
        $idusu = $this->getIdusu();
        if (empty($idusu)) return false;

        $modelo = new conexion();
        $conexion = $modelo->get_conexion();

        // 1) Eliminar asignaciones de fichas
        $sql1 = "DELETE FROM insseg WHERE idusu = :idusu";
        $stmt1 = $conexion->prepare($sql1);
        $stmt1->bindParam(':idusu', $idusu);
        $stmt1->execute();

        // 2) Revertir perfil a instructor (7)
        $sql2 = "UPDATE usuario SET idper = 7 WHERE idusu = :idusu";
        $stmt2 = $conexion->prepare($sql2);
        $stmt2->bindParam(':idusu', $idusu);
        return $stmt2->execute();
    }

    public function existeAsignacionFicha() {
        $sql = "SELECT COUNT(*) FROM insseg WHERE idficha = :idficha";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $idficha = $this->getIdficha();
        $result->bindParam(':idficha', $idficha);
        $result->execute();
        return $result->fetchColumn() > 0;
    }

    public function getDetAsigInst() {
        $sql = "SELECT s.idusu, u.nomusu, s.idficha, f.nomfic 
                FROM insseg AS s 
                LEFT JOIN ficha AS f ON s.idficha = f.idfic 
                LEFT JOIN usuario AS u ON s.idusu = u.idusu 
                WHERE s.idusu = :idusu";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $idusu = $this->getIdusu();
        $result->bindParam(':idusu', $idusu);
        $result->execute();
        return $result->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getDetAsigInstAgrupado() {
        $idusu = $this->getIdusu();
        if (empty($idusu)) return [];

        $modelo = new conexion();
        $conexion = $modelo->get_conexion();

        $sql = "SELECT s.idusu, u.nomusu, s.idficha, f.nomfic 
                FROM insseg AS s 
                LEFT JOIN ficha AS f ON s.idficha = f.idfic 
                LEFT JOIN usuario AS u ON s.idusu = u.idusu 
                WHERE s.idusu = :idusu
                ORDER BY f.idfic";
        $result = $conexion->prepare($sql);
        $result->bindParam(':idusu', $idusu);
        $result->execute();
        $rows = $result->fetchAll(PDO::FETCH_ASSOC);

        $agrupado = [];
        foreach ($rows as $r) {
            $idficha = $r['idficha'];
            if (isset($agrupado[$idficha])) continue;

            $sqlFicha = "SELECT uf.idusu AS idapr, u.nomusu AS nomapr, u.ndocusu AS docapr 
                         FROM usufic AS uf 
                         INNER JOIN usuario AS u ON uf.idusu = u.idusu 
                         WHERE uf.idfic = :idfic 
                         ORDER BY u.nomusu";
            $stmtFicha = $conexion->prepare($sqlFicha);
            $stmtFicha->bindParam(':idfic', $idficha);
            $stmtFicha->execute();

            $agrupado[$idficha] = [
                'idusu'      => $r['idusu'],
                'nomusu'     => $r['nomusu'],
                'idficha'    => $idficha,
                'nomfic'     => $r['nomfic'],
                'aprendices' => $stmtFicha->fetchAll(PDO::FETCH_ASSOC)
            ];
        }

        return array_values($agrupado);
    }

    // --- MÉTODOS ESTADÍSTICOS ---
    public function getTotInsSeg() {
        $sql = "SELECT COUNT(*) FROM usuario WHERE idper = 32";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->execute();
        return $result->fetchColumn();
    }

    public function getTotInsNSeg() {
        $sql = "SELECT COUNT(*) FROM usuario WHERE idper = 7";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->execute();
        return $result->fetchColumn();
    }

    public function getTotFic() {
        $sql = "SELECT COUNT(DISTINCT idficha) FROM insseg";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->execute();
        return $result->fetchColumn();
    }
}