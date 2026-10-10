<?php
	require_once('conexion.php');
	date_default_timezone_set('America/Bogota');
	$usu = isset($_POST['usu']) ? $_POST['usu']:NULL;
	$con = isset($_POST['con']) ? $_POST['con']:NULL;
	if ($usu and $con) 
		validar($usu,$con);
	else
			echo '<script>window.location="../index.php?error=ok&login=open";</script>';

	
	function validar($usu,$con){
		$res = verdat($usu,$con);
		$res = isset($res) ? $res:NULL;

		if($res){
			$fec = date("Y-m-d H:i:s");
			session_start();
			$_SESSION["idusu"] = $res[0]["idusu"];
			$_SESSION["nomusu"] = $res[0]["nomusu"];
			$_SESSION["idcen"] = $res[0]["idcen"];
			$_SESSION["codubi"] = 1;
			$_SESSION["idper"] = NULL;
			$_SESSION["idpergen"] = $res[0]["idper"];
			$_SESSION["pefnom"] = "Módulos";
			$_SESSION["aut"] = "jY238Jn&5Hhass.??44aa@@fg(80";
			//die();
			//echo '<script>window.location="https://websolution.com.co/sagi/pmod.php";</script>';
			echo '<script>window.location="../mod.php";</script>';
		}else{
			//echo "Salio con OK";
			//die();
			echo '<script>window.location="../index.php?error=ok";</script>';
		}
	}

	function verdat($usu,$con){
		$res = NULL;
		$pas = sha1(md5($con));
		$sql = "SELECT u.idusu, u.nomusu, u.idcen, u.idper, c.fiicancen, c.fficancen, c.fiprocen, c.ffprocen, c.fivotcen, c.ffvotcen 
		FROM usuario AS u INNER JOIN centro AS c ON u.idcen = c.idcen LEFT JOIN usupef AS p ON u.idusu = p.idusu 
		WHERE u.ndocusu = :usu AND u.pasusu = :con AND u.actusu = 1";
		 //echo "<br><br><br><br><br><br>".$sql."<br>'".$usu."','".$pas."'<br>";
		 //die();
		$modelo=new conexion();
		$conexion = $modelo->get_conexion();
		$result = $conexion->prepare($sql);
		//echo $sql."<br>'".$usu."','".$pas."<br>";
		//echo $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION). "<br><br>";
		$result->bindParam(':usu', $usu);
		$result->bindParam(':con', $pas);
		$result->execute();
		$res = $result->fetchall(PDO::FETCH_ASSOC);
		return $res;
	}
?>