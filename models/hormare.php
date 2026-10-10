<?php
/**
 * Modelo de Áreas  ->  tabla: area (idare, nomare, idusu)
 * -----------------------------------------------------------------------------
 * Se conservan INTACTOS los métodos y firmas originales:
 *   getIdare/getNomare/getIdusu, setIdare/setNomare/setIdusu,
 *   getAll, getOne, save, edit, del, getAllusu, getTotales, getAreAsig, getAreSin.
 *
 * Se agrega la lógica de negocio del dashboard "Áreas y Usuarios Asignados":
 *   getTotUsuResumen()  -> indicadores de usuarios responsables / candidatos
 *   getUsuAreas()       -> usuarios con las áreas que tienen a cargo
 *   getUsuSinAreas()    -> usuarios operativos activos sin área (candidatos)
 *
 * Reglas de negocio (R):
 *   R1. Área ASIGNADA     = area.idusu con valor (tiene responsable).
 *       Área SIN ASIGNAR  = area.idusu IS NULL.
 *   R2. CARGA del área    = nº de programas de formación que dependen de ella
 *                           (programa.idare). 0 = área sin oferta.
 *   R3. USUARIO OPERATIVO = usuario activo (actusu = 1) cuyo perfil NO sea de
 *                           aprendiz / candidato / nulo (esos perfiles no
 *                           pueden recibir un área a cargo).
 *   R4. USUARIO ASIGNADO  = usuario con al menos un área a cargo (responsable).
 *   R5. RESPONSABLE INACTIVO = área asignada a un usuario con actusu = 0 (alerta).
 */
class Mare
{
    private $idare;
    private $nomare;
    private $idusu;

    /* Patrones de nombre de perfil que NO pueden ser responsables de un área.
       Se comparan con LIKE (la colación de la BD no distingue mayúsculas). */
    private $perfilNoResp = array('%Aprendiz%', '%Candidato%', '%Nulo%', '%Sin perfil%');

    public function getidare()  { return $this->idare; }
    public function getNomare() { return $this->nomare; }
    public function getIdusu()  { return $this->idusu; }

    public function setidare($idare)   { $this->idare = $idare; }
    public function setNomare($nomare) { $this->nomare = $nomare; }
    public function setIdusu($idusu)   { $this->idusu = $idusu; }

    // -------------------------------------------------------------------------
    // Ayudante privado: condición SQL de "perfil operativo" (R3).
    // Se arma desde $perfilNoResp para no repetir los patrones en cada consulta.
    // -------------------------------------------------------------------------
    private function condPerfilOperativo($alias = 'p')
    {
        $cond = array();
        foreach ($this->perfilNoResp as $patron) {
            $cond[] = $alias . ".nomper NOT LIKE '" . $patron . "'";
        }
        return "(" . $alias . ".nomper IS NOT NULL AND " . implode(" AND ", $cond) . ")";
    }

    // -------------------------------------------------------------------------
    // CONSULTAS ORIGINALES (mismas columnas + 2 columnas agregadas de solo
    // lectura: u.actusu (R5) y programas (R2). No rompen a ningún llamador.
    // -------------------------------------------------------------------------
    public function getAll()
    {
        try {
            $sql = "SELECT a.idare, a.nomare, a.idusu, u.nomusu, u.actusu, p.nomper,
                           (SELECT COUNT(*) FROM programa AS pr WHERE pr.idare = a.idare) AS programas
                    FROM area AS a
                    LEFT JOIN usuario AS u ON a.idusu = u.idusu
                    LEFT JOIN perfil AS p ON u.idper = p.idper
                    ORDER BY a.nomare ASC";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->execute();
            return $result->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            ManejoError($e);
            return [];
        }
    }

    public function getOne()
    {
        try {
            $sql = "SELECT a.idare, a.nomare, a.idusu, u.nomusu, u.actusu, p.nomper,
                           (SELECT COUNT(*) FROM programa AS pr WHERE pr.idare = a.idare) AS programas
                    FROM area AS a
                    LEFT JOIN usuario AS u ON a.idusu = u.idusu
                    LEFT JOIN perfil AS p ON u.idper = p.idper
                    WHERE a.idare = :idare
                    LIMIT 1";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->bindValue(":idare", (int)$this->getidare(), PDO::PARAM_INT);
            $result->execute();
            $fila = $result->fetch(PDO::FETCH_ASSOC);
            return $fila ? $fila : null;
        } catch (Exception $e) {
            ManejoError($e);
            return null;
        }
    }

    public function save()
    {
        try {
            $sql = "INSERT INTO area (nomare, idusu) VALUES (:nomare, :idusu)";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);

            $nomare = $this->getNomare();
            $idusu  = $this->getIdusu();

            if ($nomare === null || $nomare === "") {
                $result->bindValue(":nomare", null, PDO::PARAM_NULL);
            } else {
                $result->bindValue(":nomare", $nomare, PDO::PARAM_STR);
            }

            if ($idusu !== null && $idusu !== "") {
                $result->bindValue(":idusu", (int)$idusu, PDO::PARAM_INT);
            } else {
                $result->bindValue(":idusu", null, PDO::PARAM_NULL);
            }

            return $result->execute();
        } catch (Exception $e) {
            ManejoError($e);
            return false;
        }
    }

    public function edit()
    {
        try {
            $sql = "UPDATE area SET nomare = :nomare, idusu = :idusu WHERE idare = :idare";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);

            $nomare = $this->getNomare();
            $idusu  = $this->getIdusu();

            $result->bindValue(":idare", (int)$this->getidare(), PDO::PARAM_INT);

            if ($nomare === null || $nomare === "") {
                $result->bindValue(":nomare", null, PDO::PARAM_NULL);
            } else {
                $result->bindValue(":nomare", $nomare, PDO::PARAM_STR);
            }

            if ($idusu !== null && $idusu !== "") {
                $result->bindValue(":idusu", (int)$idusu, PDO::PARAM_INT);
            } else {
                $result->bindValue(":idusu", null, PDO::PARAM_NULL);
            }

            return $result->execute();
        } catch (Exception $e) {
            ManejoError($e);
            return false;
        }
    }

    public function del()
    {
        try {
            $sql = "DELETE FROM area WHERE idare = :idare";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->bindValue(":idare", (int)$this->getidare(), PDO::PARAM_INT);
            $result->execute();
            return ($result->rowCount() > 0);
        } catch (Exception $e) {
            ManejoError($e);
            return false;
        }
    }

    public function getAllusu()
    {
        try {
            $sql = "SELECT u.idusu, u.nomusu, u.idper, p.nomper
                    FROM usuario AS u
                    LEFT JOIN perfil AS p ON u.idper = p.idper
                    WHERE u.actusu = 1
                    ORDER BY ISNULL(p.nomper), p.nomper ASC, u.nomusu ASC";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->execute();
            return $result->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            ManejoError($e);
            return [];
        }
    }

    public function getTotales()
    {
        try {
            $sql = "SELECT COUNT(*) AS total, SUM(CASE WHEN idusu IS NOT NULL THEN 1 ELSE 0 END) AS asignadas, SUM(CASE WHEN idusu IS NULL THEN 1 ELSE 0 END) AS sin FROM area";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->execute();
            return $result->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            ManejoError($e);
            return [];
        }
    }

    public function getAreAsig()
    {
        try {
            $sql = "SELECT a.idare, a.nomare, a.idusu, u.nomusu, u.actusu, p.nomper,
                           (SELECT COUNT(*) FROM programa AS pr WHERE pr.idare = a.idare) AS programas
                    FROM area AS a
                    LEFT JOIN usuario AS u ON a.idusu = u.idusu
                    LEFT JOIN perfil AS p ON u.idper = p.idper
                    WHERE a.idusu IS NOT NULL
                    ORDER BY a.nomare ASC";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->execute();
            return $result->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            ManejoError($e);
            return [];
        }
    }

    public function getAreSin()
    {
        try {
            $sql = "SELECT a.idare, a.nomare, a.idusu, u.nomusu, u.actusu, p.nomper,
                           (SELECT COUNT(*) FROM programa AS pr WHERE pr.idare = a.idare) AS programas
                    FROM area AS a
                    LEFT JOIN usuario AS u ON a.idusu = u.idusu
                    LEFT JOIN perfil AS p ON u.idper = p.idper
                    WHERE a.idusu IS NULL
                    ORDER BY a.nomare ASC";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->execute();
            return $result->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            ManejoError($e);
            return [];
        }
    }

    // =========================================================================
    // LÓGICA DE NEGOCIO DEL DASHBOARD: ÁREAS Y USUARIOS ASIGNADOS
    // =========================================================================

    /**
     * Indicadores generales de usuarios y oferta (una sola consulta).
     * Devuelve: usuarios_con_area, responsables_inactivos, usuarios_operativos,
     *           usuarios_sin_area, programas.
     */
    public function getTotUsuResumen()
    {
        try {
            $filtro = $this->condPerfilOperativo('p');

            $sql = "SELECT
                        (SELECT COUNT(DISTINCT a.idusu) FROM area AS a WHERE a.idusu IS NOT NULL) AS usuarios_con_area,
                        (SELECT COUNT(*) FROM area AS a
                            INNER JOIN usuario AS u ON u.idusu = a.idusu
                            WHERE u.actusu = 0) AS responsables_inactivos,
                        (SELECT COUNT(*) FROM usuario AS u
                            LEFT JOIN perfil AS p ON u.idper = p.idper
                            WHERE u.actusu = 1 AND $filtro) AS usuarios_operativos,
                        (SELECT COUNT(*) FROM usuario AS u
                            LEFT JOIN perfil AS p ON u.idper = p.idper
                            WHERE u.actusu = 1 AND $filtro
                              AND u.idusu NOT IN (SELECT a2.idusu FROM area AS a2 WHERE a2.idusu IS NOT NULL)) AS usuarios_sin_area,
                        (SELECT COUNT(*) FROM programa) AS programas";

            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->execute();
            $fila = $result->fetch(PDO::FETCH_ASSOC);
            return $fila ? $fila : array();
        } catch (Exception $e) {
            ManejoError($e);
            return array();
        }
    }

    /**
     * Usuarios con área asignada (R4) junto con cada área a su cargo.
     * El controlador agrupa estas filas por usuario para pintar las tarjetas.
     */
    public function getUsuAreas()
    {
        try {
            $sql = "SELECT a.idare, a.nomare,
                           (SELECT COUNT(*) FROM programa AS pr WHERE pr.idare = a.idare) AS programas,
                           u.idusu, u.nomusu, u.emausu, u.actusu,
                           p.nomper, c.nomcen
                    FROM area AS a
                    INNER JOIN usuario AS u ON a.idusu = u.idusu
                    LEFT JOIN perfil AS p ON u.idper = p.idper
                    LEFT JOIN centro AS c ON u.idcen = c.idcen
                    WHERE a.idusu IS NOT NULL
                    ORDER BY u.nomusu ASC, a.nomare ASC";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->execute();
            return $result->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            ManejoError($e);
            return [];
        }
    }

    /**
     * Usuarios operativos activos (R3) que aún NO tienen área asignada.
     * $limite evita listados gigantes en el dashboard; el total real se obtiene
     * con getTotUsuResumen()['usuarios_sin_area'].
     */
    public function getUsuSinAreas($limite = 200)
    {
        try {
            $limite = (int)$limite;
            if ($limite <= 0) { $limite = 200; }

            $filtro = $this->condPerfilOperativo('p');

            $sql = "SELECT u.idusu, u.nomusu, u.emausu, u.actusu, p.nomper, c.nomcen
                    FROM usuario AS u
                    LEFT JOIN perfil AS p ON u.idper = p.idper
                    LEFT JOIN centro AS c ON u.idcen = c.idcen
                    WHERE u.actusu = 1
                      AND $filtro
                      AND u.idusu NOT IN (SELECT a.idusu FROM area AS a WHERE a.idusu IS NOT NULL)
                    ORDER BY p.nomper ASC, u.nomusu ASC
                    LIMIT $limite";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->execute();
            return $result->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            ManejoError($e);
            return [];
        }
    }
}
?>
