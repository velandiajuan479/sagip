<?php 
require_once ("models/mcar.php");
$ndocusu = isset($_REQUEST['id']) ? $_REQUEST['id']:NULL;

$ndocusu = str_replace(".","",$ndocusu);
$ndocusu = str_replace(",","",$ndocusu);

$mcar = new Mcar();
$mcar->setNdocusu($ndocusu);
$datOne = $mcar->getOne();
$nom = "USO";
$ape = "EXCLUSIVO";

if($datOne){
	$nomc = explode(" ", $datOne[0]['nomusu']);
	if($nomc){
		switch (count($nomc)) {
			case '1':
				$nom = $nomc[0];
				break;
			case '2':
				$nom = $nomc[0];
				$ape = $nomc[1];
				break;
			case '3':
				$nom = $nomc[0];
				$ape = $nomc[1]." ".$nomc[2];
				break;
			default:
				$nom = $nomc[0]." ".$nomc[1];
				$ape = $nomc[2]." ".$nomc[3];
				break;
		}
	}

	if ($datOne[0]['idper'] == 4 or $datOne[0]['idper'] == 8) {

	    $text = "Este carné es personal e instransferible; identifica al portador Aprediz del Servicio Nacional de
				Aprendizaje SENA.<br>
				El SENA es una entidad que imparte una formación técnica profesional y tecnológica que forma parte
				de la educación superior; se solicita a las autoridades públicas, civiles y militares prestarle al
				portador toda la colaboración para la realización de las actividades de su aprendizaje.<br>
				Por disposición de las Leyes 418 de 1997, 548 de 1999, 642 de 2001 y 110 de 2006, los estudiantes de
				educación superior no serán incorporados en la presentación del servicio militar.<br>
				FIRMA AUTORIZADA<br>
				<center>German Raimundo Robayo Cruz</center>";
	} else {

	    $text = "Este carné identifica a quien lo porta, únicamente para el cumplimiento de sus funciones y para la		obtención de los servicios que el SENA presta a sus funcionarios y/o contratistas.<br>
		Se solicita a las autoridades civiles y militares prestarle toda la colaboración para su desempeño.<br><br> 
		FIRMA AUTORIZADA<br>
		<center>German Raimundo Robayo Cruz</center<br>
		<hr>";
	}
}

$text = isset($text) ? $text:"<strong>Usuario no registrado.</strong><br><br>Para acceder a nuestros servicios, por favor, diríjase a la biblioteca del Centro de Desarrollo Agroempresarial de Chía, Cundinamarca, para completar su registro.";
?>