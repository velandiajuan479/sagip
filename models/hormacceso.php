<?php
require_once(__DIR__ . "/conexion.php");

/**
 * Modelo HormAcceso - control de acceso por ficha para Horarios y Agendas.
 * Centraliza: contexto de sesión, fichas permitidas, aprendices, actividades
 * y permisos de calificación. Todo retorna valores seguros (vacío/false).
 */
class HormAcceso {

    public function getContexto() {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        $idusu = isset($_SESSION['idusu']) ? $_SESSION['idusu'] : null;
        $idper = isset($_SESSION['idper']) ? (int)$_SESSION['idper'] : 0;
        $idpergen = isset($_SESSION['idpergen']) ? (int)$_SESSION['idpergen'] : 0;
        $aut = isset($_SESSION['aut']) ? $_SESSION['aut'] : null;
        $authenticated = ($idusu && $aut === "jY238Jn&5Hhass.??44aa@@fg(80");
        return [
            'authenticated' => $authenticated,
            'idusu' => $idusu,
            'idper' => $idper,
            'idpergen' => $idpergen,
            'idper_sesion' => $idper ?: $idpergen,
            'nomusu' => isset($_SESSION['nomusu']) ? $_SESSION['nomusu'] : null,
            'is_admin' => in_array($idpergen, [1, 2, 21, 28], true),
            'is_learner' => in_array($idpergen, [4, 8, 22, 23, 39, 41], true),
            'is_instructor' => in_array($idpergen, [7, 32, 42], true),
            'can_write_grades' => $authenticated && (in_array($idpergen, [1, 2, 7, 21, 28, 32, 42], true)),
        ];
    }

    public function getFichasPermitidas($contexto = null, $idfic = null) {
        try {
            $ctx = $contexto ?: $this->getContexto();
            if (empty($ctx['authenticated'])) return [];
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            if (!empty($ctx['is_admin'])) {
                $sql = "SELECT idfic, nomfic FROM ficha ORDER BY idfic";
                $result = $conexion->prepare($sql);
                $result->execute();
            } elseif (!empty($ctx['is_instructor'])) {
                $sql = "(SELECT idfic, nomfic FROM ficha WHERE idusu=:a) UNION (SELECT f.idfic, f.nomfic FROM usufic AS uf INNER JOIN ficha AS f ON uf.idfic=f.idfic WHERE uf.idusu=:b AND uf.actfic=1) ORDER BY idfic";
                $result = $conexion->prepare($sql);
                $result->bindParam(':a', $ctx['idusu']);
                $result->bindParam(':b', $ctx['idusu']);
                $result->execute();
            } else {
                $sql = "SELECT f.idfic, f.nomfic FROM usufic AS uf INNER JOIN ficha AS f ON uf.idfic=f.idfic WHERE uf.idusu=:idusu AND uf.actfic=1 ORDER BY f.idfic";
                $result = $conexion->prepare($sql);
                $result->bindParam(':idusu', $ctx['idusu']);
                $result->execute();
            }
            $res = $result->fetchAll(PDO::FETCH_ASSOC);
            if ($idfic !== null && $idfic !== '') {
                $res = array_values(array_filter($res, function ($f) use ($idfic) { return (string)$f['idfic'] === (string)$idfic; }));
            }
            return $res;
        } catch (Exception $e) {
            return [];
        }
    }

    public function puedeVerFicha($idfic, $contexto = null) {
        if ($idfic === null || $idfic === '') return false;
        $ctx = $contexto ?: $this->getContexto();
        if (!empty($ctx['is_admin'])) return true;
        $fichas = $this->getFichasPermitidas($ctx);
        foreach ($fichas as $f) {
            if ((string)$f['idfic'] === (string)$idfic) return true;
        }
        return false;
    }

    public function getAprendicesPermitidos($idfic, $contexto = null) {
        try {
            $ctx = $contexto ?: $this->getContexto();
            if (!$this->puedeVerFicha($idfic, $ctx)) return [];
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $sql = "SELECT u.idusu, u.ndocusu, u.nomusu, u.idper FROM usufic AS uf INNER JOIN usuario AS u ON uf.idusu=u.idusu WHERE uf.idfic=:idfic AND uf.actfic=1 AND u.idper IN (3,4,8,12,13,22,23,39,41) ORDER BY u.nomusu";
            $result = $conexion->prepare($sql);
            $result->bindParam(':idfic', $idfic);
            $result->execute();
            return $result->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    public function getActividadesPermitidas($contexto = null, $idfic = null, $idact = null) {
        try {
            $ctx = $contexto ?: $this->getContexto();
            if (empty($ctx['authenticated'])) return [];
            if ($idfic !== null && $idfic !== '' && !$this->puedeVerFicha($idfic, $ctx)) return [];
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $sql = "SELECT a.idact, a.nomact, a.desact, a.duract, ag.idfic, ag.idcom, c.descom AS nomcom, p.nompro FROM actividad AS a INNER JOIN sesion AS s ON a.idses=s.idses INNER JOIN agenda AS ag ON s.idage=ag.idage LEFT JOIN competencia AS c ON ag.idcom=c.idcom LEFT JOIN ficha AS f ON ag.idfic=f.idfic LEFT JOIN programa AS p ON f.codpro=p.codpro WHERE 1=1";
            if ($idfic !== null && $idfic !== '') $sql .= " AND ag.idfic=:idfic";
            if ($idact !== null && $idact !== '') $sql .= " AND a.idact=:idact";
            $sql .= " ORDER BY a.idact";
            $result = $conexion->prepare($sql);
            if ($idfic !== null && $idfic !== '') $result->bindParam(':idfic', $idfic);
            if ($idact !== null && $idact !== '') $result->bindParam(':idact', $idact);
            $result->execute();
            return $result->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    public function puedeVerActividad($idfic, $idact, $contexto = null) {
        $ctx = $contexto ?: $this->getContexto();
        $acts = $this->getActividadesPermitidas($ctx, $idfic, $idact);
        return !empty($acts);
    }

    public function puedeCalificar($idfic, $contexto = null) {
        $ctx = $contexto ?: $this->getContexto();
        if (empty($ctx['can_write_grades'])) return false;
        return $this->puedeVerFicha($idfic, $ctx);
    }

    public function puedeAccederPaginaModulo($idpag, $contexto = null) {
        $ctx = $contexto ?: $this->getContexto();
        return !empty($ctx['authenticated']);
    }

    /**
     * Filtra filas por ficha permitida al usuario (coordinación/admin ve todo).
     * Las filas sin el campo indicado pasan intactas para no romper reportes.
     */
    public function filtrarPorFicha($filas, $campo = 'idfic', $contexto = null) {
        if (empty($filas) || !is_array($filas)) return $filas;
        $ctx = $contexto ?: $this->getContexto();
        if (!empty($ctx['is_admin'])) return $filas;
        $permitidas = [];
        foreach ($this->getFichasPermitidas($ctx) as $f) {
            $permitidas[(string)$f['idfic']] = true;
        }
        $out = [];
        foreach ($filas as $fila) {
            if (!is_array($fila) || !isset($fila[$campo])) {
                $out[] = $fila;
                continue;
            }
            if (isset($permitidas[(string)$fila[$campo]])) $out[] = $fila;
        }
        return $out;
    }

    /**
     * Etiqueta de rol para la cinta de alcance de las vistas del módulo.
     */
    public function etiquetaRol($contexto = null) {
        $ctx = $contexto ?: $this->getContexto();
        if (!empty($ctx['is_admin'])) return 'Coordinación/Administración';
        if (!empty($ctx['is_instructor'])) return 'Instructor';
        if (!empty($ctx['is_learner'])) return 'Aprendiz';
        return 'Usuario';
    }
}
?>
