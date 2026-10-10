<?php require_once('controllers/conccpr.php'); ?>
<?php
$tituloVista = trim($nomDocSel);
if($tituloVista === '') $tituloVista = 'MI COMPROMISO COMO APRENDIZ SENA';
echo titulo2("<i class='fa-solid fa-file-signature'></i> ".htmlspecialchars($tituloVista, ENT_QUOTES), 0);
?>

<div class="card shadow-sm border-0 mb-4 rounded-3">
	<div class="card-body p-3">
		<div class="row align-items-center g-3">
			<div class="col-12 col-md-5 d-flex align-items-center gap-2">
				<label for="selcom" class="form-label fw-bold text-secondary mb-0">Documento:</label>
				<select id="selcom" data-pg="<?php echo (int)$pg; ?>" class="form-select border-success" onchange="window.location='home.php?pg=' + this.getAttribute('data-pg') + '&for_dcpr=' + encodeURIComponent(this.value) + '<?php echo ($idusu ? '&idusu=' . (int)$idusu : ''); ?>';">
					<?php if(!empty($datCom)): ?>
						<?php foreach($datCom as $c): 
							$nomc = isset($c['nom_dcpr']) && trim($c['nom_dcpr']) !== '' ? $c['nom_dcpr'] : ($c['p1com'] ? $c['p1com'] : $c['p2com']); ?>
							<option value="<?php echo htmlspecialchars($nomc, ENT_QUOTES); ?>" <?php echo ($selCom && $nomDocSel === trim($nomc)) ? 'selected' : ''; ?>>
								<?php echo htmlspecialchars($nomc, ENT_QUOTES); ?>
							</option>
						<?php endforeach; ?>
					<?php endif; ?>
				</select>
			</div>
			<div class="col-12 col-md-7">
				<?php if($selCom && (int)$selCom['idcom'] > 0): ?>
					<div class="d-flex align-items-center gap-2 justify-content-md-end">
						<form method="post" action="home.php?pg=<?php echo (int)$pg; ?>" class="d-inline">
							<input type="hidden" name="ope" value="guardar">
							<input type="hidden" name="idcom" value="<?php echo (int)$selCom['idcom']; ?>">
							<button type="submit" class="btn btn-success"><i class="fa-solid fa-floppy-disk me-1"></i> Guardar fecha de diligenciamiento</button>
						</form>
						<a class="btn btn-outline-danger" target="_blank"
						   href="views/conpdfdcpr.php?&idcom=<?php echo (int)$selCom['idcom']; ?>&idusu=<?php echo (int)$idusu; ?>&for_dcpr=<?php echo urlencode($nomDocSel); ?>&pdf=ok">
							<i class="fa-solid fa-file-pdf me-1"></i> DESCARGAR PDF
						</a>
					</div>
				<?php else: ?>
					<div class="alert alert-light border shadow-sm mb-0 py-2 px-3 small text-muted d-flex align-items-center gap-2">
						<i class="fa-solid fa-circle-info text-success"></i>
						<span>Formato estándar SENA. La fecha de diligenciamiento se registra desde el detalle de documentación.</span>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>

<?php if(true): ?>
	<?php
	$fecha = array('dia' => date('d'), 'mes' => date('m'), 'anio' => date('Y'));
	$logoSenaPath = 'img/sena.png';
	if (!file_exists('img/sena.png') && file_exists('img/favicon.png')) {
		$logoSenaPath = 'img/favicon.png';
	}
	$imgv = array('sena' => $logoSenaPath);

	$datHoja = array();
	if(!empty($datUsu)) $datHoja = $datUsu[0];
	$datHoja['nomcom'] = $nomDocSel;
	if($detDcpr){
		if(!empty($detDcpr['fec_dili']))  $datHoja['fec_dili'] = $detDcpr['fec_dili'];
		if(!empty($detDcpr['nom_acu']))   $datHoja['nom_acu'] = $detDcpr['nom_acu'];
		if(!empty($detDcpr['doc_acu']))   $datHoja['doc_acu'] = $detDcpr['doc_acu'];
		if(!empty($detDcpr['parentesco']))$datHoja['parentesco'] = $detDcpr['parentesco'];
		if(!empty($detDcpr['clausulas'])) $datHoja['clausulas'] = $detDcpr['clausulas'];
		if(!empty($detDcpr['compromiso_esp'])) $datHoja['compromiso_esp'] = $detDcpr['compromiso_esp'];
	}

	$hojaHtml = $mdcpr->armarCompromiso($esMenor, $fecha, $imgv, $datHoja, $nomDocSel);

	// Asegurar que el logo verde del SENA se muestre en la celda correspondiente
	if (strpos($hojaHtml, '<img') === false || strpos($hojaHtml, 'alt="Logo SENA"') === false) {
		$hojaHtml = preg_replace(
			'/(<td[^>]*class="[^"]*dcpr-celda-logo[^"]*"[^>]*>)(.*?)(<\/td>)/is',
			'$1<img class="dcpr-logo-img" src="' . $logoSenaPath . '" alt="Logo SENA">$3',
			$hojaHtml
		);
	}

	$searchClasses = array(
		'border="0"',
		'class="dcpr-tabla dcpr-tabla-mt"',
		'class="dcpr-tabla"',
		'class="dcpr-celda-logo"',
		'class="dcpr-celda-centro"',
		'class="dcpr-tit-proceso"',
		'class="dcpr-box"',
		'class="dcpr-yo"',
		'class="dcpr-nombre-inline"',
		'class="dcpr-label-top"',
		'class="dcpr-ident-sub"',
		'class="dcpr-chk"',
		'class="dcpr-opc-cell"',
		'class="dcpr-opc-td"',
		'class="dcpr-label"',
		'class="dcpr-just"',
		'class="dcpr-just2"',
		'class="dcpr-num-doc"',
		'class="dcpr-lista"',
		'class="dcpr-fir-celda-full"',
		'class="dcpr-fir-celda"',
		'class="dcpr-fir-titulo"',
		'class="dcpr-fir-linea"',
		'class="dcpr-fir-nombre"',
		'class="dcpr-fir-doc"',
		'class="dcpr-fecha-cell"',
		'class="dcpr-fecha-campo"',
		'class="dcpr-nota"',
		'class="dcpr-nota-menor"',
		'class="dcpr-footer"',
		'class="dcpr-logo-img"'
	);

	$replaceClasses = array(
		'border="1"',
		'class="table table-bordered border-dark align-middle mb-0 mt-2"',
		'class="table table-bordered border-dark align-middle mb-0"',
		'class="p-2 text-center align-middle border border-dark w-25"',
		'class="p-2 text-center align-middle border border-dark"',
		'class="d-block fw-bold text-uppercase fs-6 mb-1"',
		'class="p-2 border border-dark align-middle"',
		'class="d-inline-block fw-bold me-2"',
		'class="d-inline-block fw-semibold"',
		'class="p-2 border border-dark align-middle fw-semibold w-25"',
		'class="small text-muted fw-normal"',
		'class="border border-dark text-center fw-bold p-1"',
		'class="border border-dark p-1 small align-middle"',
		'class="border border-dark p-2 align-middle small"',
		'class="p-2 border border-dark align-middle fw-semibold w-25"',
		'class="p-3 border border-dark text-start"',
		'class="p-3 border border-dark text-start"',
		'class="fw-semibold"',
		'class="ps-4 mb-0"',
		'class="p-4 border border-dark text-center"',
		'class="p-4 border border-dark text-center w-50"',
		'class="fw-bold small mb-4"',
		'class="border-bottom border-dark my-4 w-50 mx-auto"',
		'class="fw-bold text-uppercase mt-2"',
		'class="small text-muted"',
		'class="p-2 border border-dark align-middle"',
		'class="fw-bold px-2 border-bottom border-dark"',
		'class="p-2 border border-dark small text-muted fst-italic"',
		'class="p-2 border border-dark small text-muted fst-italic"',
		'class="text-end small text-muted mt-2 fw-semibold"',
		'class="img-fluid" style="max-height: 70px;"'
	);

	$hojaHtml = str_replace($searchClasses, $replaceClasses, $hojaHtml);
	?>

	<div class="card shadow-sm border border-secondary p-3 p-md-5 my-4 bg-white mx-auto" style="max-width: 950px;">
		<?php echo $hojaHtml; ?>
	</div>
<?php endif; ?>

<?php if(isset($_SESSION['pefid']) && $_SESSION['pefid'] == 1): ?>
	<div class="card shadow-sm border-0 mb-4 rounded-3">
		<div class="card-body p-4">
			<h4 class="fw-bold text-dark mb-2"><i class="fa-solid fa-wand-magic-sparkles text-success me-2"></i> Generador de documentos</h4>
			<p class="text-secondary small mb-3">Cree un documento nuevo: escriba el título y el texto; el sistema arma la hoja del compromiso con el encabezado oficial y la tabla del aprendiz.</p>
			<form method="post" action="home.php?pg=<?php echo (int)$pg; ?>">
				<input type="hidden" name="ope" value="crear">
				<div class="mb-3">
					<label class="form-label fw-bold text-secondary">Título del documento:</label>
					<input type="text" name="titulo" class="form-control" placeholder="Ej: MI COMPROMISO COMO APRENDIZ SENA" required>
				</div>
				<div class="mb-3">
					<label class="form-label fw-bold text-secondary">Texto del documento (compromisos):</label>
					<textarea name="texto" rows="5" class="form-control" required></textarea>
				</div>
				<button type="submit" class="btn btn-success px-4">
					<i class="fa-solid fa-plus me-1"></i> Crear documento
				</button>
			</form>
		</div>
	</div>
<?php endif; ?>

<?php if(!empty($datHist)): ?>
	<div class="card shadow-sm border-0 mb-4 rounded-3">
		<div class="card-body p-4">
			<h4 class="fw-bold text-dark mb-3"><i class="fa-solid fa-clock-rotate-left text-success me-2"></i> Historial de diligenciamiento</h4>
			<div class="table-responsive">
				<table class="table table-hover table-striped table-bordered align-middle mb-0">
					<thead class="table-success">
						<tr>
							<th class="text-center" style="width: 50px;">#</th>
							<th>Documento</th>
							<th>Fecha de diligenciamiento</th>
						</tr>
					</thead>
					<tbody>
						<?php $i = 1; foreach($datHist as $h): 
							$nomh = $h['p1com'] ? $h['p1com'] : $h['p2com']; ?>
							<tr>
								<td class="text-center fw-bold"><?php echo $i++; ?></td>
								<td><?php echo htmlspecialchars((string)$nomh, ENT_QUOTES); ?></td>
								<td><?php echo date('d/m/Y H:i:s', strtotime($h['fecuxc'])); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
<?php endif; ?>