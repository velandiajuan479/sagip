<?php
class Mval
{
    private $idval;
    private $nomval;
    private $iddom;
    private $parval;
    private $act;

    // METODOS GET
    public function getIdval()
    {
        return $this->idval;
    }
    public function getNomval()
    {
        return $this->nomval;
    }
    public function getIddom()
    {
        return $this->iddom;
    }
    public function getParval()
    {
        return $this->parval;
    }
    public function getAct()
    {
        return $this->act;
    }

    // METODOS SET
    public function setIdval($idval)
    {
        $this->idval = $idval;
    }
    public function setNomval($nomval)
    {
        $this->nomval = $nomval;
    }
    public function setIddom($iddom)
    {
        $this->iddom = $iddom;
    }
    public function setParval($parval)
    {
        $this->parval = $parval;
    }
    public function setAct($act)
    {
        $this->act = $act;
    }

    function getAll()
    {
        $sql = "SELECT idval, nomval, iddom, parval, act FROM valor";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->execute();
        return $result->fetchall(PDO::FETCH_ASSOC);
    }

    function getOne()
    {
        $sql = "SELECT idval, nomval, iddom, parval, act FROM valor WHERE idval=:idval";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $idval = $this->getIdval();
        $result->bindParam(":idval", $idval);
        $result->execute();
        return $result->fetchall(PDO::FETCH_ASSOC);
    }

    function getByDom()
    {
        $sql = "SELECT idval, nomval FROM valor WHERE iddom=:iddom";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $iddom = $this->getIddom();
        $result->bindParam(":iddom", $iddom);
        $result->execute();
        return $result->fetchall(PDO::FETCH_ASSOC);
    }

    function save()
    {
        try {
            $sql = "INSERT INTO valor(idval, nomval, iddom, parval, act) VALUES (:idval, :nomval, :iddom, :parval, :act)";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $idval = $this->getIdval();
            $result->bindParam(":idval", $idval);
            $iddom = $this->getIddom();
            $result->bindParam(":iddom", $iddom);
            $nomval = $this->getNomval();
            $result->bindParam(":nomval", $nomval);
            $parval = $this->getParval();
            $result->bindParam(":parval", $parval);
            $act = $this->getAct();
            $result->bindParam(":act", $act);
            $result->execute();
        } catch (exception $e) {
            ManejoError($e);
        }
    }

    function editAct()
    {
        try {
            $sql = "UPDATE valor SET act=:act WHERE idval=:idval";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $idval = $this->getIdval();
            $result->bindParam(":idval", $idval);
            $act = $this->getAct();
            $result->bindParam(":act", $act);
            $result->execute();
        } catch (exception $e) {
            ManejoError($e);
        }
    }

    function edit()
    {
        try {
            $sql = "UPDATE valor SET nomval=:nomval, iddom=:iddom, parval=:parval, act=:act WHERE idval=:idval";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $idval = $this->getIdval();
            $result->bindParam(":idval", $idval);
            $nomval = $this->getNomval();
            $result->bindParam(":nomval", $nomval);
            $iddom = $this->getIddom();
            $result->bindParam(":iddom", $iddom);
            $parval = $this->getParval();
            $result->bindParam(":parval", $parval);
            $act = $this->getAct();
            $result->bindParam(":act", $act);
            $result->execute();
        } catch (exception $e) {
            ManejoError($e);
        }
    }

    function del()
    {
        try {
            $sql = "DELETE FROM valor WHERE idval=:idval";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $idval = $this->getIdval();
            $result->bindParam(":idval", $idval);
            $result->execute();
        } catch (exception $e) {
            ManejoError($e);
        }
    }

    // MÉTODOS DEL DASHBOARD
    public function getStats()
    {
        try {
            $sql = "SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN act = 1 THEN 1 ELSE 0 END) as activos,
                        SUM(CASE WHEN act = 2 THEN 1 ELSE 0 END) as inactivos,
                        COUNT(DISTINCT iddom) as dominios_usados
                    FROM valor";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->execute();
            return $result->fetch(PDO::FETCH_ASSOC);
        } catch (exception $e) {
            ManejoError($e);
            return ['total' => 0, 'activos' => 0, 'inactivos' => 0, 'dominios_usados' => 0];
        }
    }

    public function getValoresPorDominio()
    {
        try {
            $sql = "SELECT d.nomdom, COUNT(v.idval) as cantidad 
                    FROM valor v 
                    INNER JOIN dominio d ON v.iddom = d.iddom 
                    GROUP BY d.iddom, d.nomdom 
                    ORDER BY cantidad DESC 
                    LIMIT 5";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->execute();
            return $result->fetchAll(PDO::FETCH_ASSOC);
        } catch (exception $e) {
            ManejoError($e);
            return [];
        }
    }
}
?>