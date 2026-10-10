models
<?php
    class Hormhrgn{ 
    //Atributos
        private $idhor;
        private $idfic;
        private $nomfic;
        private $jornada;
        private $idaul;
        private $nomaul;
        private $idcen;
        private $nomcen;
        private $dircen;
        private $idusu;
        private $nomusu;
        private $iddia;
        private $idare;

    // Métodos Get devuelven datos
        function getIdhor(){return $this->idhor;}
        function getIdfic(){return $this->idfic;}
        function getNomfic(){return $this->nomfic;}
        function getJornada(){return $this->jornada;}
        function getIdaul(){return $this->idaul;}
        function getNomaul(){return $this->nomaul;}
        function getIdcen(){return $this->idcen;}
        function getNomcen(){return $this->nomcen;}
        function getDircen(){return $this->dircen;}
        function getIdusu(){return $this->idusu;}
        function getNomusu(){return $this->nomusu;}    
        function getIddia(){return $this->iddia;}
        function getIdare(){return $this->idare;}
        
    //Métodos Set guardan datos
        function setIdhor($idhor){$this->idhor = $idhor;}
        function setIdfic($idfic){$this->idfic = $idfic;}
        function setNomfic($nomfic){$this->nomfic = $nomfic;}
        function setJornada($jornada){$this->jornada = $jornada;}
        function setIdaul($idaul){$this->idaul = $idaul;}
        function setNomaul($nomaul){$this->nomaul = $nomaul;}
        function setIdcen($idcen){$this->idcen = $idcen;}
        function setNomcen($nomcen){$this->nomcen = $nomcen;}
        function setDircen($dircen){$this->dircen = $dircen;}
        function setIdusu($idusu){$this->idusu = $idusu;}
        function setNomusu($nomusu){$this->nomusu = $nomusu;}
        function setIddia($iddia){ $this->iddia = $iddia;}
        function setIdare($idare){$this->idare = $idare;}

        public function getAll(){
            $sql = "SELECT f.idfic, f.nomfic, v.nomval, f.codpro, a.idare, a.nomare, f.finific, f.ffinfic
            FROM ficha AS f INNER JOIN valor AS v ON f.jornada=v.idval INNER JOIN programa AS p ON f.codpro=p.codpro INNER JOIN area AS a ON p.idare=a.idare WHERE f.idfic>1000";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->execute();
            $res = $result-> fetchall(PDO::FETCH_ASSOC);
            return $res;
        }
        public function getIns(){
            $res = NULL;
            $sql = "SELECT DISTINCT h.idfic, h.idaul, a.nomaul, a.codubi, a.idcen, h.idusu, u.ndocusu, u.nomusu, h.iddia FROM horario AS h INNER JOIN usuario AS u ON h.idusu=u.idusu INNER JOIN aula AS a ON h.idaul=a.idaul WHERE h.idfic=:idfic AND h.iddia=:iddia";
            $modelo = new Conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $idfic = $this->getIdfic();
            $result->bindParam(":idfic", $idfic);
            $iddia = $this->getIddia();
            $result->bindParam(":iddia", $iddia);
            $result->execute();
            $res= $result->fetchall(PDO::FETCH_ASSOC);
            return $res;
        }
        
        function getFic(){
            $res = NULL;
            $sql = "SELECT f.idfic, f.nomfic, v.nomval, f.codpro, a.nomare FROM ficha AS f INNER JOIN valor AS v ON f.jornada=v.idval LEFT JOIN programa AS p ON f.codpro=p.codpro LEFT JOIN area AS a ON p.idare=a.idare WHERE f.idfic>1000 ORDER BY f.idfic, f.nomfic";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->execute();
            $res = $result->fetchAll(PDO::FETCH_ASSOC);
            return $res; 
        }

        public function getDia(){
            $sql = "SELECT idval, nomval, parval FROM valor WHERE act=1 AND iddom=14 ORDER BY parval";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->execute();
            $res = $result-> fetchall(PDO::FETCH_ASSOC);
            return $res;
        }

        function getcent(){
            $res = NULL;
            $sql = "SELECT c.idcen, c.nomcen, c.dircen FROM centro AS c";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->execute();
            $res = $result->fetchAll(PDO::FETCH_ASSOC);
            return $res; 
        }
}
?>