<?php

	require_once('models/conexion.php');

	$valor = $_REQUEST["valor"];
	$data=selmuni($valor);
	//echo $sql;
	$i=0;
	foreach ($data as $res){
		$mmun[$i]["value"]=$res["codubi"];
		$mmun[$i]["nombre"]=$res["nomubi"];
		$i++;
	}
	$html='<div id="reloadMun"><select name="ubiest" id="id_estado" class="form-control form-select">';
	$html.='<option value="">Seleccione municipio</option>';
	
	foreach ($mmun as $res){
			$html.='<option value="'.$res["value"].'">'.$res["nombre"].'</option>';
		}

	$html.='</select></div>';
	echo $html;	


	function selmuni($valor){
		$resultado=null;
		$modelo=new conexion();
		$conexion=$modelo->get_conexion();
		$sql="SELECT codubi, nomubi, depubi FROM ubica WHERE depubi=:valor";
		//echo $sql;
		$result=$conexion->prepare($sql);

		$result->bindParam(':valor', $valor);
		$result->execute();

		while($f=$result->fetch()){
			$resultado[]=$f;
		}
		return $resultado;
	}
?>