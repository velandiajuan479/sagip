<?php include('controllers/desclat.php'); ?>
<div class="conte">
  <?php echo titulo2("<i class='".$icono."'></i> Reporte Llamados de atención", 2); ?>

  <form name="frm1" action="home.php?pg=<?=$pg;?>" method="POST">
    <div class="row">
      <div class="form-group col-md-3">
        <label for="fin">Fecha inicial</label>
        <input type="date" name="fin" id="fin" class="form-control" value="<?= $fin; ?>">
      </div>
      <div class="form-group col-md-3">
        <label for="ffi">Fecha final</label>
        <input type="date" name="ffi" id="ffi" class="form-control" value="<?= $ffi; ?>">
      </div>
      <div class="form-group col-md-3">
        <label for="ndocusu">No. Documento</label>
        <input type="text" name="ndocusu" id="ndocusu" maxlength="15" class="form-control" value="<?= $ndocusu; ?>">
      </div>
      <div class="form-group col-md-3">
        <label for="idfic">Ficha</label>
        <select name="idfic" id="idfic" class="form-select">
          <option value="">Todas</option>
          <?php if ($dfi) { foreach ($dfi as $de) { ?>
            <option value="<?= $de['idfic']; ?>" <?php if ($idfic == $de['idfic']) echo " selected "; ?>><?= $de['idfic']; ?> - <?= $de['nomfic']; ?> <?= $de['nomval']; ?></option>
          <?php }} ?>
        </select>
      </div>
    </div>
    <br>
    <div class="row">
      <div class="form-group col-md-12">
        <input type="submit" class="btn btn-primary" value="Buscar">
        <?php echo (($fin || $ffi || $ndocusu || $idfic) ? reportLlam($fin, $ffi, $ndocusu, $idfic) : ''); ?>
      </div>
    </div>
  </form>

  <br>

  <?php if ($datLlam) { ?>
  <div class="row">
    <div class="col-md-3">
      <div class="alert alert-info">
        <strong>Aprendices:</strong> <?= $totLlam['aprendices']; ?>
      </div>
    </div>
    <div class="col-md-3">
      <div class="alert alert-warning">
        <strong>Verbales:</strong> <?= $totLlam['verbales']; ?>
      </div>
    </div>
    <div class="col-md-3">
      <div class="alert alert-danger">
        <strong>Escritos:</strong> <?= $totLlam['escritos']; ?>
      </div>
    </div>
    <div class="col-md-3">
      <div class="alert alert-success">
        <strong>Total:</strong> <?= $totLlam['total']; ?>
      </div>
    </div>
  </div>

  <div class="tbl">
    <table id="example" class="table table-striped" style="width:100%">
      <thead>
        <tr>
          <th>#</th>
          <th>Aprendiz</th>
          <th>Ficha</th>
          <th>Tipo</th>
          <th>Fecha</th>
          <th>Observación</th>
        </tr>
      </thead>
      <tbody>
      <?php $i = 1; foreach ($datLlam as $d) { ?>
        <tr>
          <td><?= $i++; ?></td>
          <td><?= $d['ndocusu']; ?> - <?= $d['nomusu']; ?></td>
          <td><?= $d['idfic']; ?> <?= $d['nomfic']; ?></td>
          <td><?= ($d['tipllam'] == 1) ? 'Verbal' : 'Escrito'; ?></td>
          <td><?= $d['fecllam']; ?></td>
          <td><?= $d['obsllam']; ?></td>
        </tr>
      <?php } ?>
      </tbody>
    </table>
  </div>

  <script>
    $(document).ready(function () {
      $('#example').DataTable({
        "language": { "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json" }
      });
    });
  </script>
  <?php } else if ($fin || $ffi || $ndocusu || $idfic) { ?>
    <div class="alert alert-warning">No se encontraron resultados con los filtros seleccionados.</div>
  <?php } ?>
</div>