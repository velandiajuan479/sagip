<?php
include "controllers/concubi.php";
?>
<div class="conte"> 
    <?php echo titulo2("<i class='" . $icono . "'></i> Ubicación", 1); ?>

    <!-- Dashboard de ubicaciones -->
    <?php
    $stot = (int)(isset($stats['total'])   ? $stats['total']   : 0);
    $sdps = (int)(isset($stats['deps'])    ? $stats['deps']    : 0);
    $sact = (int)(isset($stats['activas']) ? $stats['activas'] : 0);
    ?>
    <div class="row my-4 g-4">
        <div class="col-12 col-md-4">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold small text-primary">Municipios / Sedes Registradas</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold fs-2 lh-1 text-dark"><?= number_format($stot, 0, ',', '.'); ?></span>
                            <span class="small text-secondary">registros</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box bg-primary-subtle text-primary">
                        <i class="fa-solid fa-map-location-dot fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="fw-semibold d-inline-flex align-items-center gap-1 small text-primary">
                        <i class="fa-solid fa-location-dot"></i> Cobertura nacional
                    </span>
                    <span class="small text-secondary">Municipios: <?= $stot - $sdps; ?></span>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold small text-success">Departamentos con Cobertura</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold fs-2 lh-1 text-success"><?= $sdps; ?></span>
                            <span class="small text-success opacity-75">departamentos</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box bg-success-subtle text-success">
                        <i class="fa-solid fa-earth-americas fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="fw-semibold d-inline-flex align-items-center gap-1 text-success">
                        <i class="fa-solid fa-flag"></i> Agrupan las ubicaciones
                    </span>
                    <span class="small text-secondary">Municipios por dpto.</span>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold small text-danger">Ubicaciones Activas / Operativas</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold fs-2 lh-1 text-dark"><?= $sact; ?></span>
                            <span class="small text-secondary">en funcionamiento</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box bg-danger-subtle text-danger">
                        <i class="fa-solid fa-building-circle-check fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="fw-semibold small text-danger">
                        <i class="fa-solid fa-chalkboard-user"></i> Con actividades / aulas
                    </span>
                    <span class="small text-secondary">% del total: <?= $stot ? round($sact*100/$stot,1).'%' : '0%'; ?></span>
                </div>
            </div>
        </div>
    </div> 

<div id="frmins" class="inser oculto">
<form name="frm1" action="home.php?pg=<?=$pg;?>" method="POST">
	<div class="row">
		<div class="form-group col-md-4">
			<label for="nomubi">Nombre</label>
			<input type="text" class="form-control form-control" name="nomubi" id="nomubi" value="<?php if($dtOne && $dtOne[0]['nomubi']) echo $dtOne[0]['nomubi']; ?>" required>
		</div>
		<div class="form-group col-md-4">
			<label for="codubi">Codigo</label>
			<input type="number" class="form-control form-control" name="codubi" id="codubi" value="<?php if($dtOne && $dtOne[0]['codubi']) echo $dtOne[0]['codubi']; ?>" required <?php if($dtOne && $dtOne[0]['codubi']) echo "readonly"; ?>>
		</div>
		
		<div class="form-group col-md-4">
			<label for="depubi">Departamento</label>
			<select class="form-control form-select" id="depubi" name="depubi">
				<option value="0">Nuevo Departamento</option>
				<?php if($dtDtp){ foreach($dtDtp as $dtD){ ?>
				<option value="<?=$dtD['codubi'];?>" <?php if($dtOne && $dtOne[0]['depubi']==$dtD['codubi']) echo "selected"; ?>><?=$dtD['nomubi'];?></option>
				<?php }} ?>
			</select>
		</div>

		<div class="form-group col-md-4">
			<br>
			<input type="hidden" name="ope" value="save">
			<?php if($dtOne && $dtOne[0]['codubi']){
				echo '<input type="hidden" name="codubi" value="'.$dtOne[0]['codubi'].'">';
				echo '<input type="hidden" name="ope2" value="edit">';
			} ?>
			<input type="submit" class="btn btn-primary" value="Enviar">
		</div>
	</div>
</form>
</div>

<table id="example" class="table table-striped tabla-100">
    <thead>
        <tr>
			<th>Código</th>
			<th>Municipio - Dpto.</th>
			<th></th>
		</tr>
    </thead>
	<tbody>
		<?php if($dat){ foreach ($dat as $dt) { ?>
			<tr>
				<td><?=$dt["cm"];?></td>
				<td><?=$dt["nm"]." ".$dt["nd"] ;?></td>
				<td>
					<a href="home.php?pg=<?=$pg;?>&ope=del&codubi=<?=$dt['cm'];?>" data-eli title="Eliminar"><i class="fa-solid fa-trash-can fa-2x"></i></a>
					<a href="home.php?pg=<?=$pg;?>&ope=edi&codubi=<?=$dt['cm'];?>" title="Editar"><i class="fa-solid fa-pen-to-square fa-2x"></i></a>
				</td>
			</tr>
		<?php }} ?>
	</tbody>

    <tfoot>
        <tr>
			<th>Código</th>
			<th>Ubicación</th>
			<th></th>
		</tr>
    </tfoot>
</table>
</div>