<?php
class Mcar{
//Atributos
	private $ndocusu;

// Métodos Get devuelven datos
	function getNdocusu(){
		return $this->ndocusu;
	}

//Métodos Set guardan datos
	function setNdocusu($ndocusu){
		$this->ndocusu = $ndocusu;
	}
//Métodos CRUD
	function getOne(){
		$sql = "SELECT vtd.nomval AS tipdoc, u.idusu, u.ndocusu, u.nomusu, u.idper, vrh.nomval AS rh ,u.fecini, u.fecfin,  p.nomper, f.nomfic, f.finific, f.ffinfic, v.nomval, uf.idfic, f.nomfic, u.fotcan 
		FROM usuario AS u 
		LEFT JOIN usufic AS uf ON u.idusu=uf.idusu 
		LEFT JOIN perfil AS p ON u.idper=p.idper 
		LEFT JOIN ficha AS f ON uf.idfic=f.idfic 
		LEFT JOIN valor AS v ON f.jornada=v.idval 
		LEFT JOIN valor AS vrh ON u.rhusu = vrh.idval
		LEFT JOIN valor    AS vtd  ON u.tdousu  = vtd.idval
		WHERE u.ndocusu=:ndocusu";
		$modelo = new conexion();
		$conexion = $modelo->get_conexion();
		$result = $conexion->prepare($sql);
		$ndocusu = $this->getNdocusu();
		$result->bindParam(':ndocusu',$ndocusu);
		//echo "<br>".$sql."<br>";
		$result->execute();
		$res = $result->fetchAll(PDO::FETCH_ASSOC);
		return $res;
	}

}
?>