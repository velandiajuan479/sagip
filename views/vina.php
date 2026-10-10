<?php require_once 'controllers/cina.php'; ?>

<?php echo titulo2("<i class='fa" . $icono . "'></i> Generar Inasistencia",0); ?>

<head>
  <style>
        
        .container {
            display: flex;
            flex-direction: row;
        }

        .chart-container {
            flex: 1;
            max-width: 100%;
        }

        .table-container {
            flex: 1;
            max-width: 600px;
            margin-top: 1%;
        }

        .highcharts-data-table table {
            font-family: Verdana, sans-serif;
            border-collapse: collapse;
            border: 1px solid #ebebeb;
            text-align: center;
            width: 100%;
            max-width: 500px;
            margin: 0 auto;
        }

        .highcharts-data-table caption {
            padding: 1em 0;
            font-size: 1.2em;
            color: #555;
        }

        .highcharts-data-table th {
            font-weight: 600;
            padding: 0.5em;
        }

        .highcharts-data-table td,
        .highcharts-data-table th,
        .highcharts-data-table caption {
            padding: 0.5em;
        }

        .highcharts-data-table thead tr,
        .highcharts-data-table tr:nth-child(even) {
            background: #f8f8f8;
        }

        .highcharts-data-table tr:hover {
            background: #f1f7ff;
        }

        @media only screen and (max-width: 600px) {
            .container {
                flex-direction: column;
            }

            .table-container {
                max-width: 100%;
                justify-content: center;
                margin-top: 10%;
            }

            .chart-container {
                display: flex;
                justify-content: center;
            }
        }
    </style>
</head>


<form name="frm1" action="home.php?pg=<?=$pg;?>" method="POST">
  <div class="row">
    <div class="form-group col-md-6">
      <label for="txt">Ingrese No. de Documento o ficha a buscar</label>
      <input type="text" name="idfic1" id="txt" class="form-control" onchange="this.form.submit();">
    </div>
    <div class="form-group col-md-6">
      <label for="combobox">Seleccione Ficha a buscar</label>
      <select name="idfic2" id="combobox" class="form-select" onchange="this.form.submit();">
        <option value="0"></option>
        <?php
        if ($dfi) {
          foreach ($dfi as $de) {
        ?>
            <option value="<?= $de['idfic']; ?>" <?php if ($dat && $idfic==$de['idfic']) echo " selected "; ?>><?= $de['idfic']; ?> - <?= $de['nomfic']; ?> <?= $de['nomval']; ?></option>
        <?php }
        } ?>
      </select>
      <input type="hidden" name="ope" value="gini">
    </div>
  </div>
</form>

<?php if (isset($_GET['updated'])): ?>
  <div class="alert alert-success">
    Datos guardados correctamente.
  </div>
<?php endif; ?>


<br>


<!-- GRAFICA -->

<?php if (isset($idfic) && $idfic != 0) { ?>
  <body>
    <div class="container">
        <div class="chart-container">
            <figure class="highcharts-figure">
                <div id="container"></div>
                <br>
            </figure>
        </div>
        <div class="table-container">
            <div class="highcharts-data-table">
                <table style="font-size: 1.5rem; border-collapse: collapse; width: 80%; margin: 20px auto;">
                    <caption id="frmins">Resumen de Aprendizes</caption>
                    <thead>
                        <tr>
                            <th>Categoría</th>
                            <th>Cantidad</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Pendientes</td>
                            <td>
                                <?= $gaf['pendientes']; ?>
                            </td>
                        </tr>
                        <tr>
                            <td>Inasistentes</td>
                            <td>
                                <?= $gaf['no_asistieron']; ?>
                            </td>
                        </tr>
                        <tr>
                            <td>Asistentes</td>
                            <td>
                                <?= $gaf['asistieron']; ?>
                            </td>
                        </tr>
                        <tr>
                            <td>Con Novedades</td>
                            <td>
                                <?= $gaf['con_novedad']; ?>
                            </td>
                        </tr>
                        <tr>
                            <td>Total</td>
                            <td>
                                <?= $gaf['total_personas']; ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
  </body>
<?php } ?>
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://code.highcharts.com/modules/exporting.js"></script>
    <script src="https://code.highcharts.com/modules/accessibility.js"></script>

    <script>

Highcharts.setOptions({
            lang: {
                decimalPoint: ',',
                thousandsSep: '.',
                loading: 'Cargando...',
                noData: 'No hay datos',
                printChart: 'Imprimir gráfico',
                downloadPNG: 'Descargar en PNG',
                downloadJPEG: 'Descargar en JPEG',
                downloadPDF: 'Descargar en PDF',
                downloadSVG: 'Descargar en SVG',
                resetZoom: 'Restablecer zoom',
                viewFullscreen: 'Ver a pantalla completa',
                exitFullscreen: 'Salir de pantalla completa'
                // Puedes agregar más traducciones según sea necesario
            }
        });
        
        Highcharts.chart('container', {
            chart: {
                type: 'pie',
                width: 370,
                height: 370
            },
            title: {
                text: 'Porcentaje de Aprendizes',
                style: {
                    fontSize: '20px'
                }
            },
            tooltip: {
                valueSuffix: '  personas'
            },
            subtitle: {
                text: 'Source: <a href="https://www.mdpi.com/2072-6643/11/3/684/htm" target="_default">MDPI</a>'
            },
            plotOptions: {
                series: {
                    allowPointSelect: true,
                    cursor: 'pointer',
                    dataLabels: [{
                        enabled: true,
                        distance: 20
                    }, {
                        enabled: true,
                        distance: -40,
                        format: '{point.percentage:.1f}%',
                        style: {
                            fontSize: '18px',
                            textOutline: 'none',
                            opacity: 0.7
                        },
                        filter: {
                            operator: '>',
                            property: 'percentage',
                            value: 10
                        }
                    }]
                }
            },
            series: [{
                name: 'Total',
                colorByPoint: true,
                data: [{
                    name: 'Pendientes',
                    y: <?= $gaf['pendientes']; ?>
                },
                {
                    name: 'Inasistentes',
                    y: <?= $gaf['no_asistieron']; ?>
                },
                {
                    name: 'Asistentes',
                    y: <?= $gaf['asistieron']; ?>
                },
                {
                    name: 'Con Novedades',
                    y: <?= $gaf['con_novedad']; ?>
                }]
            }]
        });
    </script>
</body>






<br><br><br><br>
<?php if (isset($idfic) && $idfic != 0) { ?>
  <?php echo titulo2("<i class='fa" . $icono . "'></i> Pendientes", 0); ?>
<?php } ?>

<!-- Tabla Pendientes -->




<?php if ($datAllgi) { ?>
<form name="frm1" action="home.php?pg=<?=$pg;?>" method="POST">
  <table class="table table-striped" style="width:100%">
    <thead>
      <tr>
        <th></th>
        <th>Aprendiz</th>
        <th>Total de horas</th>
        <th>
          <input type="submit" class="btn btn-primary" style="margin-top: 0px;float: right;" value="Guardar">
        </th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($datAllgi AS $d) { ?>
        <tr>
          <td></td>
          <td>
            <strong><?=strtoupper($d['nomusu']); ?></strong><br>
            <small>
              <strong>No. Documento:</strong> <?= $d['ndocusu']; ?><br>
              <?php if ($d['idfic']) { ?>
                <strong>Ficha:</strong><?= $d['idfic']; ?> <?= $d['nomfic']; ?><br>
              <?php } ?>
            </small>
          </td>
          <td style="text-align: center; font-size: 1.2rem; padding: 5px 10px; max-width: 60px;">
            <strong><big><big><?= $d['horasina']; ?></big></big></strong>
          </td>
          <td>
            <div class="d-flex flex-column">
              <div class="d-flex align-items-center mb-2">
                <input type="hidden" name="idusu[]" value="<?=$d['idusu']; ?>">
                <input type="number" name="horasina[]" step="1" min="0" max="100" class="form-control me-2" value="<?=$d['nhora']; ?>">
                <input type="checkbox" name="chkgd[]" value="<?=$d['idusu']; ?>" style="width: 30px; height: 30px;">
              </div>
                <textarea name="comina[]" class="form-control" maxlength="512" rows="2" placeholder="Comentario sobre la inasistencia..."><?=$d['comina'] ?? ''?></textarea>
            </div>
          </td>
        </tr>
      <?php } ?>
    </tbody>
    <tfoot>
      <tr>
        <th></th>
        <th>Aprendiz</th>
        <th>Total de horas</th>
        <th>
          <input type="submit" class="btn btn-primary" style="margin-top: 0px;float: right;" value="Guardar">
          <input type="hidden" name="idfic" value="<?=$idfic; ?>">
          <input type="hidden" name="ope" value="save">
        </th>
      </tr>
    </tfoot>
  </table>
</form>
<?php } ?>







<!-- Tabla Inasistencia -->

<br><br><br><br>
<?php if (isset($idfic) && $idfic != 0) { ?>
  <?php echo titulo2("<i class='fa" . $icono . "'></i> Inasistentes", 0); ?>
<?php } ?>

<?php if ($datInasi) { ?>
  <table id="mytable2" class="table table-striped" style="width:100%">
    <thead>
      <tr>
        <th></th>
        <th>Aprendiz</th>
        <th>Total de horas</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($datInasi AS $i) { ?>
        <tr>
          <td></td>
          <td>
            <strong><?=strtoupper($i['nomusu']); ?></strong><br>
            <small>
              <strong>No. Documento:</strong> <?= $i['ndocusu']; ?><br>
              <?php if ($i['idfic']) { ?>
                <strong>Ficha:</strong><?= $i['idfic']; ?> <?= $i['nomfic']; ?><br>
              <?php } ?>
            </small>
          </td>
          <td style="text-align: center; font-size: 1.2rem; padding: 5px 10px; max-width: 60px;">
            <strong><big><big><?= $i['horasina']; ?></big></big></strong>
          </td>
          <td style="text-align: center;">
            <div class="d-flex flex-row align-items-center">
              <!-- Botón Llamado de Atención -->
              <button type="button" class="btn btn-success rounded-circle d-flex align-items-center justify-content-center mx-2" 
                      style="width: 40px; height: 40px; background-color: rgb(57, 169, 0);" 
                      title="Llamado de Atención" data-bs-toggle="modal" data-bs-target="#modllamado<?=$i['idusu'];?>">
                  <i class="fa-solid fa-triangle-exclamation"></i>
              </button>

              <!-- Botón Deserción -->
               <?php
                  $macta = new Macta();
                  $ActaAct = $macta->getOneActDes($i['idusu'], $i['idfic']);
                if($ActaAct AND $ActaAct['idact']){
              ?>
              <a href="views/pdfdes.php?idact=<?=$ActaAct['idact'];?>" target="_blank">
                <i class="fa fa-solid fa-print fa-2x" style="font-size: 30px;" title="Imprimir Acta"></i>
              </a>
            <?php }else{ ?>
               <button type="button" class="btn btn-success rounded-circle d-flex align-items-center justify-content-center mx-2" 
                      style="width: 40px; height: 40px; background-color: rgb(57, 169, 0);" 
                      title="Deserción" data-bs-toggle="modal" data-bs-target="#CrearActModal<?=$i['idusu'];?>">
                  <i class="fa-solid fa-file-circle-plus"></i>
              </button>
            <?php } ?>
              

              <!-- Botón Cargar Imagen -->
                 <i class="fa-solid fa-upload fa-2x" data-bs-toggle="modal" data-bs-target="#mdcgImg<?=$i['idusu']; ?>"></i>

              <?php echo modalCarImg($i['idusu'],strtoupper($i['nomusu']),$pg); ?>


              <!-- Botón Novedad -->
              <button type="button" class="btn btn-success rounded-circle d-flex align-items-center justify-content-center mx-2" 
                      style="width: 40px; height: 40px; background-color: rgb(57, 169, 0);" 
                      title="Añadir Novedad" data-bs-toggle="modal" data-bs-target="#modnove<?=$i['idusu'];?>">
                  <i class="fa-solid fa-plus"></i>
              </button>

              <!-- Botón Historial -->
              <button type="button" class="btn btn-success rounded-circle d-flex align-items-center justify-content-center mx-2" 
                      style="width: 40px; height: 40px; background-color: rgb(57, 169, 0);" 
                      title="Ver Historial" data-bs-toggle="modal" data-bs-target="#modhisina<?=$i['idusu'];?>">
                  <i class="fa-solid fa-eye"></i>
              </button>
            </div>

          </td>
        </tr>
      <?php } ?>
    </tbody>
    <tfoot>
      <tr>
        <th></th>
        <th>Aprendiz</th>
        <th>Total de horas</th>
        <th>Acciones</th>
      </tr>
    </tfoot>
  </table>

<!-- MODALES FUERA DEL FORMULARIO PRINCIPAL -->
<?php foreach ($datInasi as $i) {
  echo modActMod($i['idusu'], $i['ndocusu']." - ".$i['nomusu'], $i['idfic'], $i['nomfic']." ".$i['nomval'], $pg);
  echo modllamado($i['idusu'], $i['ndocusu']." - ".$i['nomusu'], $i['idfic'], $i['nomfic']." ".$i['nomval'], $pg);
  echo modnove($i['idusu'], $i['ndocusu']." - ".$i['nomusu'], $i['idfic'], $i['nomfic']." ".$i['nomval'], $pg);
  $hist = $mina->getHist($i['idusu']);
  $llam = $mina->getLlam($i['idusu']);
  echo modhisina($i['idusu'], $i['ndocusu']." - ".$i['nomusu'], $i['idfic'], $i['nomfic']." ".$i['nomval'], $pg, $hist, $llam);
  echo modalCarImg($i['idusu'],strtoupper($i['nomusu']),$pg);
} ?>
<?php } ?>





<br><br><br><br>
<?php if (isset($idfic) && $idfic != 0) { ?>
  <?php echo titulo2("<i class='fa" . $icono . "'></i> Asistentes", 0); ?>
<?php } ?>


<!-- Tabla Asistentes -->


<?php if ($datAsis) { ?>
<form name="frm1" action="home.php?pg=<?=$pg;?>" method="POST">
  <table id="mytable3" class="table table-striped" style="width:100%">
    <thead>
      <tr>
        <th></th>
        <th>Aprendiz</th>
        <th>Total de horas</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($datAsis AS $a) { ?>
        <tr>
          <td></td>
          <td>
            <strong><?=strtoupper($a['nomusu']); ?></strong><br>
            <small>
              <strong>No. Documento:</strong> <?= $a['ndocusu']; ?><br>
              <?php if ($a['idfic']) { ?>
                <strong>Ficha:</strong><?= $a['idfic']; ?> <?= $a['nomfic']; ?><br>
              <?php } ?>
            </small>
          </td>
          <td style="text-align: center; font-size: 1.2rem; padding: 5px 10px; max-width: 60px;">
            <strong><big><big><?= $a['horasina']; ?></big></big></strong>
          </td>
          <td style="text-align:center;">
            <div class="d-flex flex-row align-items-center">
              <!-- Botón Llamado de Atención -->
              <button type="button" class="btn btn-success rounded-circle d-flex align-items-center justify-content-center mx-2" 
                      style="width: 40px; height: 40px; background-color: rgb(57, 169, 0);" 
                      title="Llamado de Atención" data-bs-toggle="modal" data-bs-target="#modllamado<?=$a['idusu'];?>">
                  <i class="fa-solid fa-triangle-exclamation"></i>
              </button>

              <!-- Botón Deserción -->
              <button type="button" class="btn btn-success rounded-circle d-flex align-items-center justify-content-center mx-2" 
                      style="width: 40px; height: 40px; background-color: rgb(57, 169, 0);" 
                      title="Deserción" data-bs-toggle="modal" data-bs-target="#CrearActModal<?=$i['idusu'];?>">
                  <i class="fa-solid fa-file-circle-plus"></i>
              </button>

              <!-- Botón Novedad -->
              <button type="button" class="btn btn-success rounded-circle d-flex align-items-center justify-content-center mx-2" 
                      style="width: 40px; height: 40px; background-color: rgb(57, 169, 0);" 
                      title="Añadir Novedad" data-bs-toggle="modal" data-bs-target="#modnove<?=$a['idusu'];?>">
                  <i class="fa-solid fa-plus"></i>
              </button>

              <!-- Botón Historial -->
              <button type="button" class="btn btn-success rounded-circle d-flex align-items-center justify-content-center mx-2" 
                      style="width: 40px; height: 40px; background-color: rgb(57, 169, 0);" 
                      title="Ver Historial" data-bs-toggle="modal" data-bs-target="#modhisina<?=$a['idusu'];?>">
                  <i class="fa-solid fa-eye"></i>
              </button>
            </div>
          </td>
        </tr>
      <?php } ?>
    </tbody>
    <tfoot>
      <tr>
        <th></th>
        <th>Aprendiz</th>
        <th>Total de horas</th>
        <th></th>
      </tr>
    </tfoot>
  </table>
</form>

<br><br>

<!-- MODALES FUERA DEL FORMULARIO PRINCIPAL -->
<?php foreach ($datAsis as $a) {
  echo modActMod($a['idusu'], $a['ndocusu']." - ".$a['nomusu'], $a['idfic'], $a['nomfic']." ".$a['nomval'], $pg);
  echo modllamado($a['idusu'], $a['ndocusu']." - ".$a['nomusu'], $a['idfic'], $a['nomfic']." ".$a['nomval'], $pg);
  echo modnove($a['idusu'], $a['ndocusu']." - ".$a['nomusu'], $a['idfic'], $a['nomfic']." ".$a['nomval'], $pg);
  $hist = $mina->getHist($a['idusu']);
  $llam = $mina->getLlam($a['idusu']);
  echo modhisina($a['idusu'], $a['ndocusu']." - ".$a['nomusu'], $a['idfic'], $a['nomfic']." ".$a['nomval'], $pg, $hist, $llam);
} ?>
<?php } ?>


<br><br><br><br>
<?php if (isset($idfic) && $idfic != 0) { ?>
  <?php echo titulo2("<i class='fa" . $icono . "'></i> Con Novedades", 0); ?>
<?php } ?>



<?php if ($datNove) { ?>
  <table id="mytable1" class="table table-striped" style="width:100%">
    <thead>
      <tr>
        <th></th>
        <th>Aprendiz</th>
        <th>Tipo Novedad</th>
        <th>Total de horas</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($datNove AS $n) { ?>
        <tr>
          <td></td>
          <td>
            <strong><?=strtoupper($n['nomusu']); ?></strong><br>
            <small>
              <strong>No. Documento:</strong> <?= $n['ndocusu']; ?><br>
              <?php if ($n['idfic']) { ?>
                <strong>Ficha:</strong><?= $n['idfic']; ?> <?= $n['nomfic']; ?><br>
              <?php } ?>
            </small>
          </td>
          <?php
            $estados = [
                3 => 'Deserción',
                4 => 'Cancelado',
                5 => 'Traslado',
                6 => 'Retiro Voluntario'
            ];

            $texto = isset($estados[$n['actusu']]) ? $estados[$n['actusu']] : $n['actusu'];
            ?>

          <td style="text-align: center; font-size: 1.2rem; padding: 5px 10px; max-width: 60px;">
            <strong><big><big><?= $texto ?></big></big></strong>
          </td>
          <td style="text-align: center; font-size: 1.2rem; padding: 5px 10px; max-width: 60px;">
            <strong><big><big><?= $n['horasina']; ?></big></big></strong>
          </td>
          <td style="text-align:center;">
            <div class="d-flex flex-row align-items-center"> 
                           <!-- Botón Historial -->
              <button type="button" class="btn btn-success rounded-circle d-flex align-items-center justify-content-center mx-2" 
                      style="width: 40px; height: 40px; background-color: rgb(57, 169, 0);" 
                      title="Ver Historial" data-bs-toggle="modal" data-bs-target="#modhisina<?=$n['idusu'];?>">
                  <i class="fa-solid fa-eye"></i>
              </button>

            </div>
          </td>
        </tr>
      <?php } ?>
    </tbody>
    <tfoot>
      <tr>
        <th></th>
        <th>Aprendiz</th>
        <th>Tipo Novedad</th>
        <th>Total de horas</th>
        <th></th>
      </tr>
    </tfoot>
  </table>

<?php } ?>

<?php foreach ($datNove as $n) {
  $hist = $mina->getHist($n['idusu']);
  $llam = $mina->getLlam($n['idusu']);
  echo modhisina($n['idusu'], $n['ndocusu']." - ".$n['nomusu'], $n['idfic'], $n['nomfic']." ".$n['nomval'], $pg, $hist, $llam);
} ?>
