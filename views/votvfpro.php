<?php
// Emisión directa: impresión (?imp=1) o descarga PDF (?pdf=ok)
$esImp = (isset($_GET['imp']) && $_GET['imp'] === '1');
$esPdf = (isset($_GET['pdf']) && $_GET['pdf'] === 'ok');

if ($esImp || $esPdf) {
	if (file_exists('../models/votmfpro.php')) {
		chdir(dirname(__DIR__));
	}
	ini_set('memory_limit', '512M');
	require_once 'models/conexion.php';
}

require_once 'controllers/votcfpro.php';
$fecha = date('d/m/Y');

if (!$esImp && !$esPdf) { ?>
<div class="mx-auto w-100 max-w-screen-lg">
	<!-- Barra de acciones -->
	<div class="filter-toolbar-container d-flex flex-wrap justify-content-end gap-2">
		<a class="btn btn-outline-success" role="button" href="views/votvfpro.php?imp=1&idusu=<?= (int)$idusu; ?>" target="_blank">
			<i class="fa-solid fa-print me-1"></i> Imprimir
		</a>
		<a class="btn btn-success" role="button" href="views/votvfpro.php?pdf=ok&idusu=<?= (int)$idusu; ?>" target="_blank">
			<i class="fa-solid fa-file-pdf me-1"></i> Generar PDF
		</a>
	</div>

	<!-- Hoja del documento -->
	<article class="bg-white border rounded-4 shadow mb-4 p-4 p-md-5">
		<header class="text-center pb-4 mb-4 border-bottom border-success">
			<img class="d-block mx-auto mb-3 w-28" src="img/sena.png" alt="Logo SENA">
			<h1 class="text-uppercase fw-bold fs-3 tracking-wider mb-0">Formato de Propuesta</h1>
			<div class="text-muted mt-2 tracking-wide">Elecciones de Representante y Líder de Ficha &middot; <?= $fecha; ?></div>
		</header>

		<div class="table-responsive mb-4">
			<table class="table table-bordered align-middle mb-0">
				<tbody>
					<tr>
						<th scope="row" class="bg-light text-uppercase fw-bold small text-success w-25">Nombre del candidato</th>
						<td class="fw-semibold"><?= !empty($dus) ? txlbl($dus[0]['nomusu']) : 'No registrado'; ?></td>
					</tr>
					<tr>
						<th scope="row" class="bg-light text-uppercase fw-bold small text-success w-25">Documento de identidad</th>
						<td class="fw-semibold"><?= !empty($dus[0]['ndocusu']) ? txlbl($dus[0]['ndocusu']) : '-'; ?></td>
					</tr>
				</tbody>
			</table>
		</div>

		<dl class="row g-0 border rounded-3 mb-4">
			<div class="col-md-4 p-3 border-end border-light">
				<dt class="text-uppercase fw-bold small text-success mb-2">Ficha</dt>
				<dd class="fw-semibold mb-0"><?= $esEst && !empty($dus[0]['idfic']) ? txlbl($dus[0]['idfic'] . ' - ' . $dus[0]['nomfic']) : '-'; ?></dd>
			</div>
			<div class="col-md-4 p-3 border-end border-light">
				<dt class="text-uppercase fw-bold small text-success mb-2">Centro de formación</dt>
				<dd class="fw-semibold mb-0"><?= !empty($dus[0]['nomcen']) ? txlbl($dus[0]['nomcen']) : '-'; ?></dd>
			</div>
			<div class="col-md-4 p-3">
				<dt class="text-uppercase fw-bold small text-success mb-2">Jornada</dt>
				<dd class="fw-semibold mb-0"><?= $esEst && !empty($dus[0]['nomval']) ? txlbl($dus[0]['nomval']) : '-'; ?></dd>
			</div>
		</dl>

		<?php if ($dvpr) {
			$n = 0;
			foreach ($dvpr as $dv) {
				if (!$dv['parval']) {
					$valor = ($datOne && isset($datOne[$n]['texpro'])) ? $datOne[$n]['texpro'] : '';
					?>
					<section class="mb-4">
						<h2 class="bg-success text-white fw-bold text-uppercase small px-3 py-2 tracking-wider rounded-t-lg mb-0"><?= txlbl($dv['nomval']); ?></h2>
						<div class="border border-top-0 rounded-b-lg p-3">
							<p class="mb-0 lh-lg"><?= nl2br(htmlspecialchars($valor)); ?></p>
						</div>
					</section>
					<?php
					$n++;
				} else {
					?>
					<section class="mb-4">
						<h2 class="bg-success text-white fw-bold text-uppercase small px-3 py-2 tracking-wider rounded-t-lg mb-0"><?= txlbl($dv['nomval']); ?></h2>
						<div class="border border-top-0 rounded-b-lg p-3">
<?php
						$nr = explode(";", $dv['parval']);
						foreach ($nr as $sub) {
							$valor = ($datOne && isset($datOne[$n]['texpro'])) ? $datOne[$n]['texpro'] : '';
							?>
<h3 class="text-uppercase fw-bold small text-success mb-1 tracking-wide"><?= txlbl($sub); ?></h3>
						<p class="border-start border-3 border-success ps-3 lh-lg mb-3"><?= nl2br(htmlspecialchars($valor)); ?></p>
						<?php
						$n++;
					}
					?>
					</div>
				</section>
					<?php
				}
			}
		} ?>

		<?php if ($dcon) { ?>
		<section class="mb-4">
			<h2 class="bg-success text-white fw-bold text-uppercase small px-3 py-2 tracking-wider rounded-t-lg mb-0">Condiciones</h2>
			<div class="border border-top-0 rounded-b-lg p-3">
				<ul class="list-unstyled mb-0">
					<?php
					$i = 0;
					foreach ($dcon as $row) {
						$val = ($datC && isset($datC[$i]['texpro'])) ? $datC[$i]['texpro'] : '';
						if ($val == 'Si') { $badge = 'bg-success text-white'; $icon = 'fa-circle-check'; }
						elseif ($val == 'No') { $badge = 'bg-danger text-white'; $icon = 'fa-circle-xmark'; }
						else { $badge = 'bg-light text-muted border'; $icon = 'fa-minus'; }
						$txt = $val ? $val : 'Pendiente';
						?>
						<li class="d-flex justify-content-between align-items-center gap-2 py-2 border-bottom border-light">
							<span class="text-uppercase fw-bold small text-success"><?= txlbl($row['nomval']); ?></span>
							<span class="badge <?= $badge; ?> rounded-pill"><i class="fa-solid <?= $icon; ?> me-1"></i><?= htmlspecialchars($txt); ?></span>
						</li>
						<?php
						$i++;
					}
					?>
				</ul>
			</div>
		</section>
		<?php } ?>

		<?php if ($dman) { ?>
		<section class="mb-4">
			<h2 class="bg-success text-white fw-bold text-uppercase small px-3 py-2 tracking-wider rounded-t-lg mb-0">Manifiesto</h2>
			<div class="border border-top-0 rounded-b-lg p-3">
				<ul class="list-unstyled mb-0">
					<?php
					$i = 0;
					foreach ($dman as $row) {
						$val = ($datM && isset($datM[$i]['texpro'])) ? $datM[$i]['texpro'] : '';
						if ($val == 'Si') { $badge = 'bg-success text-white'; $icon = 'fa-circle-check'; }
						elseif ($val == 'No') { $badge = 'bg-danger text-white'; $icon = 'fa-circle-xmark'; }
						else { $badge = 'bg-light text-muted border'; $icon = 'fa-minus'; }
						$txt = $val ? $val : 'Pendiente';
						?>
						<li class="d-flex justify-content-between align-items-center gap-2 py-2 border-bottom border-light">
							<span class="text-uppercase fw-bold small text-success"><?= txlbl($row['nomval']); ?></span>
							<span class="badge <?= $badge; ?> rounded-pill"><i class="fa-solid <?= $icon; ?> me-1"></i><?= htmlspecialchars($txt); ?></span>
						</li>
						<?php
						$i++;
					}
					?>
				</ul>
			</div>
		</section>
		<?php } ?>

		<div class="d-flex justify-content-between align-items-end gap-5 mt-5">
			<div class="flex-fill pt-3 text-start border-t-2 border-gray-800">
				<span class="fw-bold text-muted">Firma del candidato</span>
			</div>
			<div class="flex-fill pt-3 text-end border-t-2 border-gray-800">
				<span class="fw-bold text-muted">Firma del responsable</span>
			</div>
		</div>
	</article>
</div>
<?php
	exit;
}

// ==================================================================================
// Emisión del formato: ?imp=1 -> imprimir | ?pdf=ok -> descargar PDF
// ==================================================================================
if (!function_exists('formato_logo')) {
	function formato_logo() {
		if (file_exists('img/sena.png')) {
			return "data:image/png;base64," . base64_encode(file_get_contents('img/sena.png'));
		}
		return '';
	}
}

ob_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Formato de Propuesta - SENA</title>
<style>
	body { font-family: 'DejaVu Sans', Arial, Helvetica, sans-serif; font-size: 11px; color: #000; line-height: 1.4; margin: 0; padding: 0; background: #fff; }
	.doc { max-width: 820px; margin: 0 auto; padding: 20px 24px; }
	.encabezado { text-align: center; margin-bottom: 14px; }
	.encabezado img { width: 58px; height: auto; }
	.titulo-doc { font-size: 16px; font-weight: bold; text-transform: uppercase; margin: 6px 0 2px; }
	.subtitulo-doc { font-size: 11px; color: #444; margin: 0; }
	.tabla-basica { width: 100%; border-collapse: collapse; margin: 12px 0; }
	.tabla-basica th, .tabla-basica td { border: 1px solid #000; padding: 6px 8px; vertical-align: top; text-align: left; }
	.tabla-basica th { background: #efefef; font-size: 9px; text-transform: uppercase; letter-spacing: .5px; width: 30%; }
	.tabla-fila3 { width: 100%; border-collapse: collapse; margin: 12px 0; }
	.tabla-fila3 td { width: 33.33%; border: 1px solid #000; padding: 6px 8px; vertical-align: top; }
	.tabla-fila3 strong { font-size: 9px; text-transform: uppercase; display: block; }
	.seccion { margin: 16px 0 0; }
	.titulo-seccion { background: #117f09; color: #fff; font-size: 10px; font-weight: bold; text-transform: uppercase; letter-spacing: .5px; padding: 6px 10px; }
	.cuerpo-seccion { border: 1px solid #000; border-top: none; padding: 10px 12px; }
	.sub-seccion { margin: 8px 0 6px; font-size: 10px; font-weight: bold; text-transform: uppercase; color: #0c6206; }
	.parrafo { margin: 0 0 8px; text-align: justify; }
	.parrafo-sub { margin: 0 0 8px; border-left: 3px solid #117f09; padding-left: 10px; text-align: justify; }
	.fila-cond { padding: 5px 0; border-bottom: 1px solid #ddd; }
	.fila-cond:last-child { border-bottom: none; }
	.fila-cond span.nombre { font-size: 10px; font-weight: bold; text-transform: uppercase; color: #0c6206; }
	.fila-cond span.estado { float: right; font-size: 9px; font-weight: bold; padding: 1px 10px; border-radius: 8px; border: 1px solid #333; }
	.estado-si { background: #117f09; color: #fff; }
	.estado-no { background: #d9534f; color: #fff; }
	.estado-na { background: #eee; color: #333; }
	.firmas { width: 100%; margin-top: 40px; border-collapse: collapse; }
	.firmas td { width: 50%; text-align: center; vertical-align: top; }
	.linea-firma { border-top: 1px solid #000; margin: 0 18px; }
	.etiqueta-firma { display: block; margin-top: 6px; font-size: 9px; font-weight: bold; text-transform: uppercase; color: #333; }
	.limpia { clear: both; }
</style>
</head>
<body>
<div class="doc">

	<div class="encabezado">
		<img src="<?= formato_logo(); ?>" alt="Logo SENA">
		<div class="titulo-doc">Formato de Propuesta</div>
		<div class="subtitulo-doc">Elecciones de Representante y L&iacute;der de Ficha &middot; <?= $fecha; ?></div>
	</div>

	<table class="tabla-basica">
		<tr>
			<th>Nombre del candidato</th>
			<td><?= !empty($dus) ? htmlspecialchars(txlbl($dus[0]['nomusu'])) : 'No registrado'; ?></td>
		</tr>
		<tr>
			<th>Documento de identidad</th>
			<td><?= !empty($dus[0]['ndocusu']) ? htmlspecialchars(txlbl($dus[0]['ndocusu'])) : '-'; ?></td>
		</tr>
	</table>

	<table class="tabla-fila3">
		<tr>
			<td><strong>Ficha</strong><?= $esEst && !empty($dus[0]['idfic']) ? htmlspecialchars(txlbl($dus[0]['idfic'] . ' - ' . $dus[0]['nomfic'])) : '-'; ?></td>
			<td><strong>Centro de formaci&oacute;n</strong><?= !empty($dus[0]['nomcen']) ? htmlspecialchars(txlbl($dus[0]['nomcen'])) : '-'; ?></td>
			<td><strong>Jornada</strong><?= $esEst && !empty($dus[0]['nomval']) ? htmlspecialchars(txlbl($dus[0]['nomval'])) : '-'; ?></td>
		</tr>
	</table>

	<?php if ($dvpr) { $n = 0; ?>
		<?php foreach ($dvpr as $dv) { ?>
			<div class="seccion">
				<div class="titulo-seccion"><?= htmlspecialchars(txlbl($dv['nomval'])); ?></div>
				<div class="cuerpo-seccion">
					<?php if (!$dv['parval']) { ?>
						<?php
						$valor = ($datOne && isset($datOne[$n]['texpro'])) ? $datOne[$n]['texpro'] : '';
						$n++;
						?>
						<p class="parrafo"><?= nl2br(htmlspecialchars($valor)); ?></p>
					<?php } else { ?>
						<?php
						$nr = explode(";", $dv['parval']);
						foreach ($nr as $sub) {
							$valor = ($datOne && isset($datOne[$n]['texpro'])) ? $datOne[$n]['texpro'] : '';
							$n++;
							?>
							<div class="sub-seccion"><?= htmlspecialchars(txlbl($sub)); ?></div>
							<p class="parrafo-sub"><?= nl2br(htmlspecialchars($valor)); ?></p>
						<?php } ?>
					<?php } ?>
				</div>
			</div>
		<?php } ?>
	<?php } ?>

	<?php
	$seccioneslista = array(
		array('titulo' => 'Condiciones', 'datos' => $dcon, 'valores' => $datC),
		array('titulo' => 'Manifiesto', 'datos' => $dman, 'valores' => $datM)
	);
	?>
	<?php foreach ($seccioneslista as $bloque) {
		if (!$bloque['datos']) continue; ?>
		<div class="seccion">
			<div class="titulo-seccion"><?= $bloque['titulo']; ?></div>
			<div class="cuerpo-seccion">
				<?php $i = 0; ?>
				<?php foreach ($bloque['datos'] as $row) {
					$val = ($bloque['valores'] && isset($bloque['valores'][$i]['texpro'])) ? $bloque['valores'][$i]['texpro'] : '';
					if ($val == 'Si') { $clase = 'estado-si'; $txt = 'Si'; }
					elseif ($val == 'No') { $clase = 'estado-no'; $txt = 'No'; }
					else { $clase = 'estado-na'; $txt = 'Pendiente'; }
					$i++;
					?>
					<div class="fila-cond">
						<span class="nombre"><?= htmlspecialchars(txlbl($row['nomval'])); ?></span>
						<span class="estado <?= $clase; ?>"><?= htmlspecialchars($txt); ?></span>
						<div class="limpia"></div>
					</div>
				<?php } ?>
			</div>
		</div>
	<?php } ?>

	<table class="firmas">
		<tr>
			<td>
				<div class="linea-firma"></div>
				<span class="etiqueta-firma">Firma del candidato</span>
			</td>
			<td>
				<div class="linea-firma"></div>
				<span class="etiqueta-firma">Firma del responsable</span>
			</td>
		</tr>
	</table>

</div>
</body>
</html>
<?php
$html = ob_get_clean();

if ($esPdf) {
	if (file_exists('vendor/autoload.php')) {
		require_once('vendor/autoload.php');
	}
	if (class_exists('Dompdf\Dompdf')) {
		$dompdf = new Dompdf\Dompdf();
		$dompdf->setPaper('letter', 'portrait');
		$dompdf->loadHtml($html);
		$dompdf->render();
		while (ob_get_level()) {
			ob_end_clean();
		}
		$dompdf->stream('Formato_Propuesta_' . date('YmdHis') . '.pdf', array('Attachment' => true));
	} else {
		echo $html;
	}
	exit;
}

echo $html;
echo "\n<script>window.addEventListener('load', function() { window.print(); });</script>";
exit;