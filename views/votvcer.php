<?php require_once 'controllers/votccer.php'; ?>
<?php echo titulo2("<i class= '". $icono . "'></i> Certificado Representante", 2); ?>
<br><br>
<a href="views/pdfcr.php?idusu=<?=$_SESSION['idusu'];?>" target="_blank">
	<i class="fas fa-print fa-2x" title="Imprimir"></i>
</a>
	&nbsp;&nbsp;&nbsp;
<a href="views/pdfcr.php?pdf=ok&idusu=<?=$_SESSION['idusu'];?>" target="_blank">
	<i class="fas fa-file-pdf fa-2x" title="Generar PDF"></i>
</a>

<?php
date_default_timezone_set('America/Bogota');
$mes = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
$fecha = date('d')." de ".$mes[date('m')-1]." de ".date('Y');
?>
<table width="100%">
	<tr>
		<td style="text-align: center;">
			<br><br>
		</td>
	</tr>
</table>
<table width="100%" cellpadding="5px" cellspacing="0">
	
		<th style="text-align: center;border: 1px solid #000;" colspan="3">
			CERTIFICACIÓN DE VOTACIÓN DE REPRESENTANTES DE APRENDICES SENA
		</th>
	</tr>
	<tr>
		<td style="border: 1px solid #000;">
			<strong>CIUDAD y FECHA</strong><br>
			Chía, <?=$fecha?>
		</td>
		<td style="border: 1px solid #000;">
			<strong>HORA INICIO</strong><br>
			<?php if($datOne) echo substr($datOne[0]['fivotcen'], 11, 5)." Hrs"; ?>
		</td>
		<td style="border: 1px solid #000;">
			<strong>HORA FIN</strong><br>
			<?php if($datOne) echo substr($datOne[0]['ffvotcen'], 11, 5)." Hrs"; ?>
		</td>
	</tr>
	<tr>
		<td style="border: 1px solid #000;">
			<strong>LUGAR Y/O ENLACE</strong><br>
			Bienestar
		</td>
		<td colspan="2" style="border: 1px solid #000;">
			<strong>DIRECCIÓN GENERAL / Regional / Centro</strong><br>
			Regional Cundinamarca, <?php if($datOne) echo $datOne[0]['nomcen']; ?>
		</td>
	</tr>
	<tr>
		<td colspan="3" style="border: 1px solid #000;">
			<strong>Datos</strong><br>

			<?php if ($datOne): ?>
				<div class="form-group col-md-6">
					<label for="ndocusu">No. Documento:</label>
					<?= $datOne[0]['ndocusu']; ?>
				</div>
				<div class="form-group col-md-6">
					<label for="nomusu">Nombre:</label>
					<?= $datOne[0]['nomusu']; ?>
				</div>
				<div class="form-group col-md-6">
					<label for="nomcen">Centro de Formación:</label>
					<?= $datOne[0]['nomcen']; ?>
				</div>
				<?php if (!empty($datOne[0]['idfic'])): ?>
					<div class="form-group col-md-6">
						<label for="idfic">Ficha:</label>
						<?= $datOne[0]['idfic'] . " " . $datOne[0]['nomfic'] . " " . $datOne[0]['nomval']; ?>
					</div>
				<?php endif; ?>
			<?php endif; ?>
		</td>
	</tr>

</table>
<br><br><br><br><br>
