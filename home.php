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
	<script src="https://unpkg.com/lucide@latest"></script>

	<!-- Alerts Mensajes Pantalla -->
	<script src="js/sweetalert2.all.min.js"></script>
	<link rel="stylesheet" type="text/css" href="css/sweetalert2.min.css">
	<!-- jQuery primero -->
	<script src="js/jquery-3.5.1.js"></script>
	<!-- jQuery UI CSS -->
	<link rel="stylesheet" href="./css/jquery-ui.css">
	<!-- jQuery UI JS -->
	<script src="./js/jquery-ui.min.js"></script>
	<!-- Chart -->
	<script src="js/chart.min.js"></script>
	<!-- Chart JS Datatables -->
	<script src="js/chartjs-plugin-datalabels.min.js"></script>
	<!-- Bootstrap 5 CSS -->
	<link href="css/bootstrap.min.css" rel="stylesheet">
	<!-- DataTables CSS (con Bootstrap 5) -->
	<link href="css/dataTables.bootstrap5.min.css" rel="stylesheet">
	<!-- DataTables JS -->
	<script src="js/jquery.dataTables.min.js"></script>
	<script src="js/dataTables.bootstrap5.min.js"></script>
	<!-- Bootstrap 5 JS -->
	<script src="js/bootstrap.bundle.min.js"></script>
	<!-- DataTables exportación -->
	<script src="js/dataTables.buttons.min.js"></script>
	<script src="js/jszip.min.js"></script>
	<script src="js/pdfmake.min.js"></script>
	<script src="js/vfs_fonts.js"></script>
	<script src="js/buttons.html5.min.js"></script>
	<link rel="stylesheet" href="css/buttons.bootstrap5.min.css">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>


	<script src="js/java.js"></script>

	<link rel="stylesheet" type="text/css" href="css/style.css">
	<link rel="stylesheet" type="text/css" href="css/menu.css">
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
			$pefid = isset($_SESSION["pefid"]) ? $_SESSION["pefid"] : NULL;
			$ope = isset($_GET['ope']) ? $_GET['ope'] : false;
			$opera = isset($_GET['opera']) ? $_GET['opera'] : false;

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
			echo '<div id="err"></div>';
		?>
    </header>
    <?php require_once("views/vmenu.php"); ?>
	<section class="contnr">
		<?php
		$mos = 0;
		$est = 0;
		$rut = validar($pg);
		if ($rut) {
			$mos = $rut[0]['mospas'];
			if ($opera == "edi" or $ope == "edi")
				$est = 1;
			echo ayuda($pg);
			echo "<script>err();</script>";
			$icono = substr($rut[0]['icopag'], 3);
			require_once($rut[0]['rutpag']);
		} else
			echo "<script>window.location='home.php?pg=1102';</script>";
		?>
	</section>
	<?php require_once "views/footer.php"; ?>
</body>
<script type="text/javascript" src="js/valida.js"></script>
<script type="text/javascript">ocul(<?= $mos; ?>, <?= $est; ?>);</script>
</html>