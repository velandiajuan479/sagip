<?php include('controllers/descdsra.php'); ?>
<style>
  .acc-ico{ font-size:20px; color:#117f09; margin: 0 4px; cursor:pointer; }
  .acc-ico:hover{ color:#0b5c06; }
</style>

<div class="conte">
  <?php echo titulo2("<i class='".$icono."'></i> Deserciones por aprendiz", 2); ?>

  <form name="frm1" action="home.php?pg=<?=$pg;?>" method="POST">
    <div class="row">
      <div class="form-group col-md-6">
        <label for="combobox">Seleccione Ficha</label>
        <select name="idfic2" id="combobox" class="form-select" onchange="this.form.submit();">
          <option value="0"></option>
          <?php if ($dfi) { foreach ($dfi as $de) { ?>
            <option value="<?= $de['idfic']; ?>" <?php if ($idfic==$de['idfic']) echo " selected "; ?>><?= $de['idfic']; ?> - <?= $de['nomfic']; ?> <?= $de['nomval']; ?></option>
          <?php }} ?>
        </select>
      </div>
      <div class="form-group col-md-3">
        <br>
        <button type="submit" name="limpfic" value="1" class="btn btn-secondary">Limpiar</button>
      </div>
    </div>
  </form>

  <br>

  <?php if ($datAll) { ?>
  <div class="row">
    <div class="col-md-3">
      <div class="alert alert-danger">
        <strong>Total deserciones:</strong> <?= $cnt['total']; ?>
      </div>
    </div>
  </div>

  <div class="tbl">
    <table id="example" class="table table-striped" style="width:100%">
      <thead>
        <tr>
          <th>Aprendiz</th>
          <th>Documento</th>
          <th>Ficha</th>
          <th>Estado</th>
          <th>Horas Inasistencia</th>
          <th>Ll. Verbales</th>
          <th>Ll. Escritos</th>
          <th>Acta</th>
          <th>Registro Deserción</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($datAll as $dt) {
        $hist = $mdsra->getHist($dt['idusu']);
        $llam = $mdsra->getLlam($dt['idusu']); ?>
        <tr>
          <td><?= $dt['nomusu']; ?></td>
          <td><?= $dt['ndocusu']; ?></td>
          <td><?= $dt['idfic']; ?> <?= $dt['nomfic']; ?> <?= $dt['nomval']; ?></td>
          <td><?= $dt['nomest']; ?></td>
          <td><?= $dt['horasina']; ?></td>
          <td><?= $dt['llamver']; ?></td>
          <td><?= $dt['llamesc']; ?></td>
          <td><?= $dt['nac'] ? 'N° '.$dt['nac'].' ('.date("d/m/Y", strtotime($dt['fecact'])).')' : 'Sin acta'; ?></td>
          <td><?= date("d/m/Y H:i", strtotime($dt['fecregdec'])); ?></td>
          <td class="text-center" style="white-space:nowrap;">
            <i class="fa-solid fa-clock-rotate-left acc-ico" data-bs-toggle="modal" data-bs-target="#modhisdes<?= $dt['idusu']; ?>" title="Historial"></i>
            <?php if ($dt['idact']) { ?>
              <a href="views/pdfdes.php?idact=<?= $dt['idact']; ?>" target="_blank" title="Imprimir acta"><i class="fa-solid fa-print acc-ico"></i></a>
            <?php } ?>
          </td>
        </tr>
        <?= modalHistoria($dt['idusu'], $dt['nomusu'], $dt['idfic'], $dt['nomfic'].' '.$dt['nomval'], $pg, $hist, $llam); ?>
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
  <?php } else if ($idfic) { ?>
    <div class="alert alert-warning">No se encontraron deserciones registradas para esta ficha.</div>
  <?php } else { ?>
    <div class="alert alert-info">Seleccione una ficha para ver las deserciones por aprendiz.</div>
  <?php } ?>
</div>