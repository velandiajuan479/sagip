<?php
	session_start();
	$aut = isset($_SESSION["aut"]) ? $_SESSION["aut"]:NULL;
	if(session_status()!=2 or $aut!="jY238Jn&5Hhass.??44aa@@fg(80"){
		session_destroy();
		header("Location: index.php");
		exit();
	}
?>