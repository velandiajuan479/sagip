<?php 
$ctrlPath = file_exists('controllers/preccele.php') ? 'controllers/preccele.php' : '../controllers/preccele.php';
require_once $ctrlPath;
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!isset($pg)) {
    $pg = isset($_GET['pg']) ? $_GET['pg'] : 1504;
}
if (!function_exists('titulo2')) {
    $optPath = file_exists('controllers/optimg.php') ? 'controllers/optimg.php' : '../controllers/optimg.php';
    if (file_exists($optPath)) require_once $optPath;
}
?>

<div class="conte">
  <?php echo titulo2("<i class='" . ($icono ?? 'fa-solid fa-cloud-arrow-up') . "'></i> Carga Masiva de Elementos", 1); ?>

  <div class="alert alert-info py-3 mb-4 border-0 shadow-sm rounded-3" role="alert">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
      <div class="d-flex align-items-start gap-3">
        <i class="fa-solid fa-circle-info fs-3 text-primary mt-1"></i>
        <div>
          <h5 class="alert-heading mb-1 fw-bold text-dark" style="font-size: 1.05rem;">
            Importación Masiva de Activos e Inventario SENA
          </h5>
          <p class="mb-0 text-muted">
            Utilice este módulo para ingresar de forma ágil lotes de bienes institucionales (computadores, proyectores, balones, herramientas). 
            Descargue la plantilla oficial, complete las columnas respetando el documento del cuentadante y cargue el archivo Excel.
          </p>
        </div>
      </div>
      <div class="d-flex align-items-center gap-2 flex-wrap">
        <button type="button" onclick="descargarPlantillaExcel()" class="btn btn-success d-inline-flex align-items-center gap-2 text-nowrap">
          <i class="fa-solid fa-file-excel fs-5"></i> Plantilla Excel (.xls)
        </button>
        <button type="button" onclick="descargarPlantillaCSV()" class="btn btn-outline-success d-inline-flex align-items-center gap-2 text-nowrap bg-white">
          <i class="fa-solid fa-file-csv fs-5"></i> Plantilla CSV (.csv)
        </button>
      </div>
    </div>
  </div>

  <div class="inser">
    <form name="frmCarga" id="frmins" class="filter-toolbar-container mx-auto" style="max-width: 850px;" action="home.php?pg=<?=$pg;?>" method="POST" enctype="multipart/form-data">
      <div class="w-100 mb-2">
        <h2>
          <i class="fa-solid fa-cloud-arrow-up"></i> Subir Archivo de Elementos
        </h2>
      </div>
      <div class="w-100">
        <div class="mb-3">
          <label for="arch" class="form-label fw-bold text-muted small">Seleccione el archivo diligenciado (.xlsx, .xls o .csv):</label>
          <input type="file" name="arch" id="arch" class="form-control form-control-lg" accept=".xlsx, .xls, .csv" required>
          <div class="form-text text-muted">El archivo debe contener los encabezados obligatorios de la plantilla oficial.</div>
        </div>
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 pt-2 border-top">
          <button type="submit" class="btn btn-success d-inline-flex align-items-center gap-2">
            <i class="fa-solid fa-upload"></i> Iniciar Procesamiento de Carga
          </button>
          <a href="home.php?pg=1406" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2">
            <i class="fa-solid fa-arrow-left"></i> Ir a Gestión de Elementos
          </a>
        </div>
      </div>
    </form>
  </div>

  <?php if (!empty($resumenCarga)) { ?>
    <div class="mt-4">
      <div class="mb-3">
        <h2>
          <i class="fa-solid fa-square-poll-vertical text-success me-2"></i> Resultado de la Carga Masiva
        </h2>
      </div>
      
      <!-- Dashboard de Métricas de Carga (Tarjetas KPI Estilo vvidgen.php) -->
      <div class="row my-4 g-4">
        <!-- Total Leídos -->
        <div class="col-12 col-sm-6 col-xl-3">
          <div class="kpi-card h-100 d-flex flex-column justify-content-between">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #3c627f;">Total Leídos</span>
                <div class="d-flex align-items-baseline gap-2 mt-2">
                  <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #0b1c30;"><?= $resumenCarga['total']; ?></span>
                  <span style="font-size: 13px; color: #3e4a3d;">filas</span>
                </div>
              </div>
              <div class="kpi-icon-box" style="background-color: #b5dcfe; color: #3c627f;">
                <i class="fa-solid fa-file-lines fs-4"></i>
              </div>
            </div>
            <div class="kpi-footer-strip">
              <span class="fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 11px; color: #3c627f;">
                <i class="fa-solid fa-table" style="font-size: 12px;"></i> Procesadas
              </span>
              <span style="font-size: 11px; color: #3e4a3d;">En archivo</span>
            </div>
          </div>
        </div>

        <!-- Nuevos Creados -->
        <div class="col-12 col-sm-6 col-xl-3">
          <div class="kpi-card h-100 d-flex flex-column justify-content-between">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #006b29;">Nuevos Creados</span>
                <div class="d-flex align-items-baseline gap-2 mt-2">
                  <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #006b29;"><?= $resumenCarga['insertados']; ?></span>
                  <span style="font-size: 13px; color: rgba(0, 107, 41, 0.8);">insertados</span>
                </div>
              </div>
              <div class="kpi-icon-box" style="background-color: #7afd88; color: #006b24;">
                <i class="fa-solid fa-circle-plus fs-4"></i>
              </div>
            </div>
            <div class="kpi-footer-strip">
              <span class="fw-semibold d-inline-flex align-items-center gap-2" style="font-size: 11px; color: #006b29;">
                <span class="pulse-dot"></span> Éxito en BD
              </span>
              <span style="font-size: 11px; color: #3e4a3d;">Nuevos</span>
            </div>
          </div>
        </div>

        <!-- Actualizados (Serial) -->
        <div class="col-12 col-sm-6 col-xl-3">
          <div class="kpi-card h-100 d-flex flex-column justify-content-between">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #8a6d00;">Actualizados</span>
                <div class="d-flex align-items-baseline gap-2 mt-2">
                  <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #0b1c30;"><?= $resumenCarga['actualizados']; ?></span>
                  <span style="font-size: 13px; color: #3e4a3d;">existentes</span>
                </div>
              </div>
              <div class="kpi-icon-box" style="background-color: #ffe082; color: #8a6d00;">
                <i class="fa-solid fa-arrows-rotate fs-4"></i>
              </div>
            </div>
            <div class="kpi-footer-strip">
              <span class="fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 11px; color: #8a6d00;">
                <i class="fa-solid fa-pen" style="font-size: 12px;"></i> Coincidencia Serial
              </span>
              <span style="font-size: 11px; color: #3e4a3d;">Modificados</span>
            </div>
          </div>
        </div>

        <!-- Omitidos / Errores -->
        <div class="col-12 col-sm-6 col-xl-3">
          <div class="kpi-card h-100 d-flex flex-column justify-content-between">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #ba1a1a;">Inconsistencias</span>
                <div class="d-flex align-items-baseline gap-2 mt-2">
                  <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #ba1a1a;"><?= count($resumenCarga['errores']); ?></span>
                  <span style="font-size: 13px; color: #3e4a3d;">omitidos</span>
                </div>
              </div>
              <div class="kpi-icon-box" style="background-color: #ffdad6; color: #ba1a1a;">
                <i class="fa-solid fa-triangle-exclamation fs-4"></i>
              </div>
            </div>
            <div class="kpi-footer-strip">
              <span class="d-inline-flex align-items-center gap-1" style="font-size: 11px; color: #ba1a1a;">
                <i class="fa-solid fa-circle-xmark" style="font-size: 12px;"></i> No importados
              </span>
              <span class="fw-semibold" style="font-size: 11px; color: #ba1a1a;">Revisar</span>
            </div>
          </div>
        </div>
      </div>

      <?php if (!empty($resumenCarga['errores'])) { ?>
        <!-- Toolbar de Control para la Tabla de Inconsistencias -->
        <div class="filter-toolbar-container">
          <div class="toolbar-left-group">
            <div class="filter-pills-group" role="tablist">
              <button class="btn-filter-pill active" type="button">
                <span class="pill-dot" style="background-color: #ba1a1a;"></span>
                <span>Filas Omitidas</span>
                <span class="pill-count"><?= count($resumenCarga['errores']); ?></span>
              </button>
            </div>
            <div class="toolbar-divider d-none d-sm-block"></div>
            <div class="toolbar-export-buttons">
              <button type="button" class="toolbar-export-btn" title="Copiar al portapapeles" onclick="triggerDTExport('copy')">
                <i class="fa-solid fa-copy"></i>
              </button>
              <button type="button" class="toolbar-export-btn" title="Descargar CSV" onclick="triggerDTExport('csv')">
                <i class="fa-solid fa-file-csv"></i>
              </button>
              <button type="button" class="toolbar-export-btn" title="Exportar a Excel" onclick="triggerDTExport('excel')">
                <i class="fa-solid fa-file-excel"></i>
              </button>
            </div>
          </div>
          <div class="toolbar-search-sort-group">
            <div class="toolbar-search-box">
              <i class="fa-solid fa-magnifying-glass icoldiz"></i>
              <input type="text" id="customVideoSearch" placeholder="Buscar en inconsistencias..." onkeyup="syncCustomSearch(this.value)">
            </div>
            <div class="toolbar-secondary-controls">
              <select class="toolbar-select-sort" id="customVideoSort" onchange="syncCustomSort(this.value)">
                <option value="asc">Ascendente</option>
                <option value="desc">Descendente</option>
              </select>
              <div class="toolbar-view-toggle">
                <button type="button" class="toolbar-view-btn active" id="btnViewGrid" title="Vista Cuadrícula" onclick="switchCardView('grid')">
                  <i class="fa-solid fa-grip"></i>
                </button>
                <button type="button" class="toolbar-view-btn" id="btnViewList" title="Vista Lista" onclick="switchCardView('list')">
                  <i class="fa-solid fa-bars"></i>
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- DataTable en Formato Tarjetas para Inconsistencias -->
        <table id="example" class="table table-striped table-vid-cards" style="width:100%">
          <thead>
            <tr>
              <th>Filas Omitidas</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($resumenCarga['errores'] as $err) { ?>
            <tr data-order="<?=$err['fila'];?>">
              <td data-search="<?=$err['fila'];?> <?=$err['documento'];?> <?=$err['serial'];?> <?=$err['elemento'];?> <?=$err['motivo'];?>">
                <div class="card h-100 shadow-sm border-0 vid-card">
                  <!-- Preview Box -->
                  <div class="vid-preview-box">
                    <div class="d-flex flex-column align-items-center justify-content-center text-white-50">
                      <i class="fa-solid fa-file-circle-xmark fa-3x mb-2 text-danger opacity-80"></i>
                      <small class="text-uppercase fw-semibold" style="font-size: 0.72rem; letter-spacing: 0.5px;">Rechazado en Validación</small>
                    </div>

                    <!-- Badges Superiores -->
                    <div class="vid-badges-top">
                      <span class="badge bg-danger shadow-sm px-2 py-1">
                        <i class="fa-solid fa-triangle-exclamation me-1"></i> Fila <?=$err['fila'];?>
                      </span>
                      <span class="badge vid-badge-blur px-2 py-1 shadow-sm">
                        <i class="fa-solid fa-id-card me-1 text-info"></i> <?=$err['documento'];?>
                      </span>
                    </div>

                    <!-- Badges Inferiores -->
                    <div class="vid-badges-bottom">
                      <span class="badge vid-badge-blur px-2 py-1">
                        <i class="fa-solid fa-barcode me-1 text-warning"></i> Serial: <?=$err['serial'];?>
                      </span>
                    </div>
                  </div>

                  <!-- Cuerpo de la Tarjeta -->
                  <div class="card-body p-3 d-flex flex-column justify-content-between">
                    <div>
                      <h6 class="fw-bold text-dark mb-2 text-truncate" title="<?=htmlspecialchars($err['elemento']);?>" style="font-size: 1.05rem;">
                        <span class="text-danger">#<?=$err['fila'];?></span> - <?=htmlspecialchars($err['elemento']);?>
                      </h6>

                      <div class="alert alert-danger py-2 px-3 mb-0 border-0" style="font-size: 0.8rem;">
                        <i class="fa-solid fa-circle-xmark text-danger me-1"></i>
                        <strong>Motivo:</strong> <?= htmlspecialchars($err['motivo']); ?>
                      </div>
                    </div>
                  </div>

                  <!-- Footer -->
                  <div class="card-footer bg-white border-0 pt-0 pb-3 px-3">
                    <hr class="mt-0 mb-3 text-muted opacity-25">
                    <div class="d-flex justify-content-between align-items-center text-muted small">
                      <span><i class="fa-solid fa-file-excel text-success me-1"></i>Archivo Excel</span>
                      <span class="badge bg-light text-danger border border-danger-subtle">No Importado</span>
                    </div>
                  </div>
                </div>
              </td>
            </tr>
          <?php } ?>
          </tbody>
          <tfoot>
            <tr>
              <th>Filas Omitidas</th>
            </tr>
          </tfoot>
        </table>
      <?php } else { ?>
        <div class="alert alert-success d-flex align-items-center gap-2 border-0 shadow-sm mb-4" role="alert">
          <i class="fa-solid fa-circle-check fs-4 text-success"></i>
          <div>¡Excelente! Todos los registros del archivo fueron importados exitosamente sin inconsistencias.</div>
        </div>
      <?php } ?>

      <div class="text-center mt-4 mb-4">
        <a href="home.php?pg=1406" class="btn btn-success btn-lg d-inline-flex align-items-center gap-2 shadow-sm">
          <i class="fa-solid fa-boxes-stacked"></i> Consultar Inventario de Elementos (Vista 1406)
        </a>
      </div>
    </div>
  <?php } ?>
</div>

<script>
function descargarPlantillaExcel() {
  const contenido = `<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <!--[if gte mso 9]>
  <xml>
    <x:ExcelWorkbook>
      <x:ExcelWorksheets>
        <x:ExcelWorksheet>
          <x:Name>Carga Elementos SENA</x:Name>
          <x:WorksheetOptions>
            <x:DisplayGridlines/>
          </x:WorksheetOptions>
        </x:ExcelWorksheet>
      </x:ExcelWorksheets>
    </x:ExcelWorkbook>
  </xml>
  <![endif]-->
  <style>
    th { background-color: #117f09; color: #ffffff; font-family: Arial, sans-serif; font-size: 11pt; font-weight: bold; text-align: center; height: 32px; border: 1px solid #0d5f07; }
    td { font-family: Arial, sans-serif; font-size: 10pt; height: 26px; border: 1px solid #dcdcdc; vertical-align: middle; }
    .num { mso-number-format:"\\@"; }
  </style>
</head>
<body>
  <table border="1">
    <thead>
      <tr>
        <th>Documento Cuentadante *</th>
        <th>Nombre Cuentadante</th>
        <th>Nombre Elemento *</th>
        <th>Serial o Placa *</th>
        <th>Placa SENA (Almacen)</th>
        <th>Marca *</th>
        <th>Tipo de Elemento *</th>
        <th>Descripcion Detallada *</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td class="num">85555555</td>
        <td>ROBINSON ENRIQUE RINCON RAMIREZ</td>
        <td>Portatil Lenovo ThinkPad L14</td>
        <td class="num">PF-39X8K01</td>
        <td class="num">1092837</td>
        <td>Lenovo</td>
        <td>Portatil Sena</td>
        <td>Core i5, 16GB RAM, SSD 512GB, incluye cargador original</td>
      </tr>
      <tr>
        <td class="num">85555555</td>
        <td>ROBINSON ENRIQUE RINCON RAMIREZ</td>
        <td>Balon de Futbol Golty No. 5</td>
        <td class="num">BAL-FUT-02</td>
        <td class="num">2093847</td>
        <td>Golty</td>
        <td>Otro Sena</td>
        <td>Balon oficial microfutbol para prestamo bienestar</td>
      </tr>
      <tr>
        <td class="num">85555555</td>
        <td>ROBINSON ENRIQUE RINCON RAMIREZ</td>
        <td>Proyector Videobeam Epson</td>
        <td class="num">VB-EPS-99</td>
        <td class="num">3094857</td>
        <td>Epson</td>
        <td>Otro Sena</td>
        <td>Proyector HDMI 3600 lumenes con control remoto y cable de poder</td>
      </tr>
    </tbody>
  </table>
</body>
</html>`;

  const blob = new Blob(['\ufeff', contenido], { type: 'application/vnd.ms-excel;charset=utf-8;' });
  const url = URL.createObjectURL(blob);
  const link = document.createElement('a');
  link.href = url;
  link.download = 'Plantilla_Carga_Masiva_Elementos_SENA.xls';
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
  URL.revokeObjectURL(url);
}

function descargarPlantillaCSV() {
  const lineas = [
    ["Documento Cuentadante *", "Nombre Cuentadante", "Nombre Elemento *", "Serial o Placa *", "Placa SENA (Almacen)", "Marca *", "Tipo de Elemento *", "Descripcion Detallada *"],
    ["85555555", "ROBINSON ENRIQUE RINCON RAMIREZ", "Portatil Lenovo ThinkPad L14", "PF-39X8K01", "1092837", "Lenovo", "Portatil Sena", "Core i5, 16GB RAM, SSD 512GB, incluye cargador original"],
    ["85555555", "ROBINSON ENRIQUE RINCON RAMIREZ", "Balon de Futbol Golty No. 5", "BAL-FUT-02", "2093847", "Golty", "Otro Sena", "Balon oficial microfutbol para prestamo bienestar"],
    ["85555555", "ROBINSON ENRIQUE RINCON RAMIREZ", "Proyector Videobeam Epson", "VB-EPS-99", "3094857", "Epson", "Otro Sena", "Proyector HDMI 3600 lumenes con control remoto y cable de poder"]
  ];
  let csvContenido = "\uFEFF";
  lineas.forEach(row => {
    csvContenido += row.map(v => '"' + String(v).replace(/"/g, '""') + '"').join(";") + "\r\n";
  });
  const blob = new Blob([csvContenido], { type: 'text/csv;charset=utf-8;' });
  const url = URL.createObjectURL(blob);
  const link = document.createElement('a');
  link.href = url;
  link.download = 'Plantilla_Carga_Masiva_Elementos_SENA.csv';
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
  URL.revokeObjectURL(url);
}
</script>