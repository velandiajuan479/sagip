<?php

class Mcep {

    function getInstructorById($idusu) {
        $sql = "SELECT idusu, ndocusu, nomusu, emausu, telcan FROM usuario WHERE idusu = :idusu";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':idusu', $idusu, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    function getFichaInfo($idficha) {
        $sql = "SELECT idfic, nomfic FROM ficha WHERE idfic = :idfic";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':idfic', $idficha);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    function getAprendicesResumenPorFicha($idficha) {
        $sql = "
            SELECT
                u.idusu, u.ndocusu, u.nomusu, u.telcan, u.emausu,
                COALESCE(SUM(CASE WHEN b.estado = 'Aprobado' THEN 1 ELSE 0 END), 0) AS aprobado,
                COALESCE(SUM(CASE WHEN b.estado = 'Rechazado' THEN 1 ELSE 0 END), 0) AS rechazado,
                COALESCE(SUM(CASE WHEN b.estado = 'Pendiente' THEN 1 ELSE 0 END), 0) AS pendiente,
                GROUP_CONCAT(DISTINCT b.numero_bitacora ORDER BY b.numero_bitacora ASC) AS bitacoras_entregadas
            FROM usufic uf
            INNER JOIN usuario u ON uf.idusu = u.idusu
            LEFT JOIN bitacora b ON b.idaprendiz = u.idusu
            WHERE uf.idfic = :idficha
            GROUP BY u.idusu, u.ndocusu, u.nomusu, u.telcan, u.emausu
            ORDER BY u.nomusu ASC
        ";

        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':idficha', $idficha, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
