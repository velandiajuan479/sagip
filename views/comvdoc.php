<?php require_once 'controllers/comcdoc.php'; ?>
<?php $icono = isset($icono) ? $icono : 'fa-solid fa-file-lines'; ?>
<?php
$titInst  = (isset($val) && !empty($val[0]['titcof'])) ? $val[0]['titcof'] : 'Servicio Nacional de Aprendizaje &ndash; SENA';
$fotInst  = (isset($val) && !empty($val[0]['foocof'])) ? $val[0]['foocof'] : 'SERVICIO NACIONAL DE APRENDIZAJE SENA &ndash; COLOMBIA';

function pdfFecha($f, $conHora = false){
	if(!$f) return '';
	$meses = array('enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre');
	$d = (int)date('d', strtotime($f));
	$m = $meses[(int)date('n', strtotime($f)) - 1];
	$a = date('Y', strtotime($f));
	$txt = "$d de $m de $a";
	if($conHora) $txt .= ' &bull; ' . date('h:i A', strtotime($f));
	return $txt;
}
function pdfVal($v){
	return ($v === NULL || trim($v) === '') ? '<span class="vacio">No registrado</span>' : htmlspecialchars($v);
}
function pdfEstado($estado){
	$cls = 'pend';
	if($estado == 'Aprobado') $cls = 'aprob';
	elseif($estado == 'Inscrito') $cls = 'inscr';
	elseif($estado == 'Rechazado') $cls = 'rech';
	return '<span class="badge-est '.$cls.'">'.htmlspecialchars($estado).'</span>';
}
?>
<style>
	.hoja{ max-width:820px; margin:10px auto 30px; background:#ffffff; border:1px solid #cfd8dc; border-radius:8px; box-shadow:0 3px 12px rgba(0,0,0,.14); padding:0; overflow:hidden; }
	.docs-hea{ display:flex; align-items:center; gap:18px; padding:26px 34px 18px; border-bottom:4px solid #108b22; position:relative; }
	.docs-hea::before{ content:''; position:absolute; left:0; bottom:-4px; height:4px; width:420px; background:rgba(255,255,255,.35); }
	.docs-logo img{ height:64px; width:auto; }
	.docs-tit{ flex:1; }
	.docs-inst{ font-weight:800; letter-spacing:.4px; color:#1b365d; font-size:.95rem; text-transform:uppercase; }
	.docs-cof{ color:#555; font-size:.82rem; }
	.docs-docname{ margin-top:10px; font-size:1.25rem; font-weight:800; color:#1b365d; }
	.docs-sub{ font-size:.8rem; color:#666; letter-spacing:.3px; }
	.docs-cod{ text-align:center; border:2px solid #1b365d; border-radius:6px; padding:8px 14px; background:#f4f7fa; }
	.docs-cod small{ display:block; font-size:.62rem; letter-spacing:1.5px; color:#1b365d; font-weight:700; }
	.docs-cod b{ font-size:1.05rem; color:#108b22; letter-spacing:.5px; }
	.docs-bar{ display:flex; justify-content:space-between; padding:8px 34px; background:#eef4ee; font-size:.72rem; color:#3d5c46; border-bottom:1px solid #d9e6da; }
	.docs-cuerpo{ padding:24px 34px 30px; }
	.sec-3{ margin-top:22px; }
	.sec-tit{ display:flex; align-items:center; gap:10px; margin-bottom:10px; }
	.sec-num{ width:30px; height:30px; border-radius:6px; background:#108b22; color:#fff; font-weight:800; display:flex; align-items:center; justify-content:center; font-size:.95rem; }
	.sec-nom{ font-weight:800; text-transform:uppercase; letter-spacing:.6px; color:#1b365d; font-size:.92rem; }
	.sec-line{ flex:1; border-bottom:2px solid #d9e6da; }
	.tab-dat{ width:100%; border-collapse:collapse; }
	.tab-dat th, .tab-dat td{ border:1px solid #cfd8dc; padding:9px 12px; vertical-align:top; }
	.tab-dat th{ width:220px; background:#f4f7fa; color:#1b365d; font-size:.7rem; text-transform:uppercase; letter-spacing:.5px; font-weight:700; }
	.tab-dat td{ font-size:.9rem; color:#222; }
	.tab-dat .tb2{ width:150px; }
	.badge-est{ display:inline-block; padding:3px 12px; border-radius:20px; font-size:.78rem; font-weight:700; }
	.badge-est.pend{ background:#fff3cd; color:#8a6d1a; border:1px solid #f0d78a; }
	.badge-est.aprob{ background:#d7f0d9; color:#117a20; border:1px solid #9ad6a0; }
	.badge-est.inscr{ background:#cfe6ff; color:#124d8a; border:1px solid #8fc4f0; }
	.badge-est.rech{ background:#fde1de; color:#a0201a; border:1px solid #f0a5a0; }
	.vacio{ color:#9aa7ad; font-style:italic; }
	.firmas{ display:flex; gap:60px; margin-top:46px; }
	.firma-campo{ flex:1; text-align:center; }
	.firma-linea{ border-top:1px solid #111; margin-top:64px; padding-top:7px; font-size:.78rem; font-weight:700; color:#333; }
	.docs-pie{ padding:10px 34px 14px; border-top:3px solid #1b365d; background:#fafbfc; text-align:center; font-size:.62rem; color:#6b776e; }
	.aviso{ max-width:820px; margin:30px auto; background:#fff; border:1px dashed #b4c2b6; border-radius:12px; padding:40px 30px; text-align:center; box-shadow:0 4px 12px rgba(0,0,0,0.05); }
	@media print{
		@page{ size:A4; margin:10mm 11mm; }
		.header-home, .main-menu, .menu--btn-spt-men, #err, .herr-pdf, .aviso, .filter-toolbar-container{ display:none !important; }
		.hoja{ max-width:100%; margin:0; border:none; border-radius:0; box-shadow:none; }
		.docs-hea{ padding:18px 4px 14px; }
		.docs-cuerpo{ padding:14px 4px 16px; }
		.docs-bar, .docs-pie{ padding-left:4px; padding-right:4px; }
		.tab-dat th, .tab-dat td{ padding:7px 9px; }
		body{ background:#fff !important; }
		.firmas{ margin-top:36px; }
	}
	@media (max-width:600px){
		.docs-hea{ flex-wrap:wrap; padding:18px 18px 14px; }
		.docs-cuerpo{ padding:16px 18px 20px; }
		.docs-bar, .docs-pie{ padding-left:18px; padding-right:18px; }
		.firmas{ flex-direction:column; gap:26px; }
	}
</style>

<div class="conte no-print-print">
    <?php echo titulo2("<i class='".$icono."'></i> Solicitud de Inscripción / Documento PDF", 1); ?>
</div>

<!-- Toolbar de Herramientas e Impresión -->
<div class="filter-toolbar-container no-print-print mb-4" style="max-width: 820px; margin: 0 auto 20px auto;">
    <div class="toolbar-left-group">
        <a href="home.php?pg=2001" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2">
            <i class="fa-solid fa-arrow-left"></i> <span>Volver a Aspirantes</span>
        </a>
    </div>

    <div class="toolbar-search-sort-group">
        <button type="button" class="btn btn-success d-inline-flex align-items-center gap-2 shadow-sm" onclick="window.print();" title="Imprimir o guardar como PDF">
            <i class="fa-solid fa-print"></i> <span>Imprimir / Guardar PDF</span>
        </button>
    </div>
</div>

<?php if(!isset($datOne) || !$datOne): ?>
	<div class="aviso">
		<i class="fa-solid fa-file-circle-question fa-3x mb-3" style="color:#1b365d;"></i>
		<h4 class="fw-bold text-dark">No hay una solicitud seleccionada</h4>
		<p class="text-muted">Esta hoja se genera autom&aacute;ticamente desde la lista de aspirantes.<br>
		Ubique el registro en la p&aacute;gina <b>Aspirantes</b> y presione el &iacute;cono <i class="fa-solid fa-file-pdf" style="color:#d9534f;"></i>.</p>
		<a href="home.php?pg=2001" class="btn btn-dark mt-3 px-4"><i class="fa-solid fa-users me-1"></i> Ir a Aspirantes</a>
	</div>
<?php else: $e = $datOne[0]; ?>
<div class="hoja">

	<div class="docs-hea">
		<div class="docs-logo"><img src="img/logoSena.png" alt="Logo SENA"></div>
		<div class="docs-tit">
			<div class="docs-inst"><?= $titInst; ?></div>
			<div class="docs-cof">Programas de Formaci&oacute;n Complementaria</div>
			<div class="docs-docname">SOLICITUD DE INSCRIPCI&Oacute;N</div>
			<div class="docs-sub">Registro y dedicaci&oacute;n del aspirante</div>
		</div>
		<div class="docs-cod">
			<small>Consecutivo</small>
			<b>IC-<?= str_pad($e['idasp'], 4, '0', STR_PAD_LEFT); ?></b>
		</div>
	</div>

	<div class="docs-bar">
		<span><b>Fecha de generaci&oacute;n:</b> <?= pdfFecha(date('Y-m-d H:i:s'), true); ?></span>
		<span><b>Estado de la solicitud:</b> <?= pdfEstado($e['estado']); ?></span>
	</div>

	<div class="docs-cuerpo">

		<div class="sec-3">
			<div class="sec-tit">
				<div class="sec-num">1</div>
				<div class="sec-nom">Datos personales</div>
				<div class="sec-line"></div>
			</div>
			<table class="tab-dat">
				<tr>
					<th>Tipo de documento</th>
					<td><?= pdfVal($e['nomtdo']); ?></td>
					<th class="tb2">N&uacute;mero de documento</th>
					<td><?= pdfVal($e['ndocusu']); ?></td>
				</tr>
				<tr>
					<th>Nombres</th>
					<td><?= pdfVal($e['nomasp']); ?></td>
					<th class="tb2">Apellidos</th>
					<td><?= pdfVal($e['apeasp']); ?></td>
				</tr>
				<tr>
					<th>Fecha de nacimiento</th>
					<td><?= $e['fecnac'] ? pdfFecha($e['fecnac']) : '<span class="vacio">No registrado</span>'; ?></td>
					<th class="tb2">G&eacute;nero</th>
					<td><?= pdfVal($e['nomgen']); ?></td>
				</tr>
				<tr>
					<th>Tel&eacute;fono</th>
					<td><?= pdfVal($e['telasp']); ?></td>
					<th class="tb2">Correo electr&oacute;nico</th>
					<td><?= pdfVal($e['emasp']); ?></td>
				</tr>
				<tr>
					<th>Direcci&oacute;n</th>
					<td colspan="3"><?= pdfVal($e['diresp']); ?></td>
				</tr>
				<tr>
					<th>Ciudad / Municipio</th>
					<td colspan="3"><?= pdfVal($e['ncommun']); ?></td>
				</tr>
			</table>
		</div>

		<div class="sec-3">
			<div class="sec-tit">
				<div class="sec-num">2</div>
				<div class="sec-nom">Datos acad&eacute;micos</div>
				<div class="sec-line"></div>
			</div>
			<table class="tab-dat">
				<tr>
					<th>Programa de formaci&oacute;n</th>
					<td><?= pdfVal($e['nomproest']); ?><?php if(!empty($e['verproest'])) echo ' <span class="vacio">(V. '.htmlspecialchars($e['verproest']).')</span>'; ?></td>
					<th class="tb2">Jornada</th>
					<td><?= pdfVal($e['nomjornada']); ?></td>
				</tr>
				<tr>
					<th>N&uacute;mero de ficha</th>
					<td><?= pdfVal($e['idfic']); ?></td>
					<th class="tb2">Nombre de la ficha</th>
					<td><?= pdfVal($e['nomfic']); ?></td>
				</tr>
				<tr>
					<th>Centro de formaci&oacute;n</th>
					<td colspan="3"><?= pdfVal($e['nomcen']); ?></td>
				</tr>
			</table>
		</div>

		<div class="sec-3">
			<div class="sec-tit">
				<div class="sec-num">3</div>
				<div class="sec-nom">Datos de la complementaria</div>
				<div class="sec-line"></div>
			</div>
			<table class="tab-dat">
				<tr>
					<th>Complementaria a la que se inscribe</th>
					<td><?= pdfVal($e['nomcomp']); ?></td>
					<th class="tb2">Estado de la solicitud</th>
					<td><?= pdfEstado($e['estado']); ?></td>
				</tr>
				<tr>
					<th>Disponibilidad</th>
					<td><?= pdfVal($e['dispoasp']); ?></td>
					<th class="tb2">Fecha de inscripci&oacute;n</th>
					<td><?= $e['fecreg'] ? pdfFecha($e['fecreg'], true) : '<span class="vacio">No registrado</span>'; ?></td>
				</tr>
				<tr>
					<th>Motivo o inter&eacute;s en realizar la complementaria</th>
					<td colspan="3"><?= pdfVal($e['motasp']); ?></td>
				</tr>
			</table>
		</div>

		<div class="firmas">
			<div class="firma-campo">
				<div class="firma-linea">FIRMA DEL ASPIRANTE</div>
			</div>
			<div class="firma-campo">
				<div class="firma-linea">COORDINADOR DE FORMACI&Oacute;N</div>
			</div>
		</div>

	</div>

	<div class="docs-pie">
		<?= $fotInst; ?> &bull; Hoja generada por el sistema SagiCDA el <?= pdfFecha(date('Y-m-d'), true); ?> &bull; Consecutivo IC-<?= str_pad($e['idasp'], 4, '0', STR_PAD_LEFT); ?>
	</div>

</div>
<?php endif; ?>