<?php
require_once("models/seguridad.php");
date_default_timezone_set('America/Bogota');
$snomcc = isset($_SESSION["nomcc"]) ? $_SESSION["nomcc"] : NULL;
$ano = date("Y");
?>
<!DOCTYPE html>
<html class="h-full bg-slate-50" lang="es" style="">
<head>
    <?php
        header('Content-Type: text/html; charset=UTF-8');
    ?>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta content="width=device-width, initial-scale=1.0" name="viewport">
	<title>SAGI-CDA Chía | SENA</title>
	<link rel="shortcut icon" href="img/favicon.png">
	<link rel="stylesheet" href="vendor/fontawesome/css/all.min.css">
	<script src="vendor/fontawesome/js/all.min.js"></script>
	<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
	<script src="js/java.js"></script>
	<script src="https://unpkg.com/lucide@latest"></script>
	<link rel="stylesheet" type="text/css" href="css/style.css">
</head>

<body class="h-full flex flex-col font-sans text-slate-800 antialiased selection:bg-sena-600 selection:text-white">
	<header class="sticky top-0 z-50 bg-white/95 glass-header border-b border-slate-200/80 shadow-sm" data-purpose="site-navigation-header">
		<?php
			require_once('models/conexion.php');
			require_once('controllers/optimg.php');
			require_once('controllers/ccof.php');
			$pg = isset($_GET['pg']) ? $_GET['pg'] : NULL;
			$id = isset($_GET['id']) ? $_GET['id'] : NULL;
			$sesion = isset($_SESSION['idusu']) ? $_SESSION['idusu']:NULL;
			$nu = 2;
			$alto = "0px";
			require_once 'controllers/titulo.php';
			$dmd = new conexion();
			$datmd = $dmd->getOneModV();

			/*if (isset($_SESSION['idusu']) && $pg !== "205") {
				header("Location: mod.php");
				exit;
			}*/
			require_once('views/header.php');
		?>
    </header>
	<section class="contni">
		<?php require_once('views/vpmod.php'); ?>
	</section>
	<?php require_once "views/footer.php"; ?>
</body>
<script type="text/javascript" src="js/valida.js"></script>
</html>