<?php 
$ctrlPath = file_exists('controllers/precpra.php') ? 'controllers/precpra.php' : '../controllers/precpra.php';
require_once $ctrlPath;
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!isset($pg)) {
    $pg = isset($_GET['pg']) ? $_GET['pg'] : 1532;
}
if (!function_exists('titulo2')) {
    $optPath = file_exists('controllers/optimg.php') ? 'controllers/optimg.php' : '../controllers/optimg.php';
    if (file_exists($optPath)) require_once $optPath;
}
?>

<div class="conte">
  <?php echo titulo2("<i class='".($icono ?? 'fa fa-solid fa-building-circle-check')."'></i> Préstamo de Aula", 2); ?>

  <?php if (isset($cnt) && $cnt): ?>
  <!-- Dashboard de Métricas (Tarjetas KPI Estilo vvidgen.php) -->
  <div class="row my-4 g-4">
    <!-- Total de Aulas -->
    <div class="col-12 col-sm-6 col-xl-3">
      <div class="kpi-card h-100 d-flex flex-column justify-content-between">
        <div class="d-flex align-items-center justify-content-between">
          <div>
            <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #3c627f;">Total Aulas</span>
            <div class="d-flex align-items-baseline gap-2 mt-2">
              <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #0b1c30;"><?= $cnt['total']; ?></span>
              <span style="font-size: 13px; color: #3e4a3d;">ambientes</span>
            </div>
          </div>
          <div class="kpi-icon-box" style="background-color: #b5dcfe; color: #3c627f;">
            <i class="fa-solid fa-door-open fs-4"></i>
          </div>
        </div>
        <div class="kpi-footer-strip">
          <span class="fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 11px; color: #3c627f;">
            <i class="fa-solid fa-building" style="font-size: 12px;"></i> Capacidad Sede
          </span>
          <span style="font-size: 11px; color: #3e4a3d;">Registradas</span>
        </div>
      </div>
    </div>

    <!-- Aulas Ocupadas -->
    <div class="col-12 col-sm-6 col-xl-3">
      <div class="kpi-card h-100 d-flex flex-column justify-content-between">
        <div class="d-flex align-items-center justify-content-between">
          <div>
            <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #8a6d00;">Aulas Ocupadas</span>
            <div class="d-flex align-items-baseline gap-2 mt-2">
              <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #0b1c30;"><?= $cnt['ocupadas']; ?></span>
              <span style="font-size: 13px; color: #3e4a3d;">en sesión</span>
            </div>
          </div>
          <div class="kpi-icon-box" style="background-color: #ffe082; color: #8a6d00;">
            <i class="fa-solid fa-calendar-day fs-4"></i>
          </div>
        </div>
        <div class="kpi-footer-strip">
          <span class="fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 11px; color: #8a6d00;">
            <i class="fa-solid fa-clock" style="font-size: 12px;"></i> En formación
          </span>
          <span style="font-size: 11px; color: #3e4a3d;">Activas</span>
        </div>
      </div>
    </div>

    <!-- Aulas Liberadas -->
    <div class="col-12 col-sm-6 col-xl-3">
      <div class="kpi-card h-100 d-flex flex-column justify-content-between">
        <div class="d-flex align-items-center justify-content-between">
          <div>
            <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #006b29;">Aulas Liberadas</span>
            <div class="d-flex align-items-baseline gap-2 mt-2">
              <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #006b29;"><?= $cnt['liberadas']; ?></span>
              <span style="font-size: 13px; color: rgba(0, 107, 41, 0.8);">disponibles</span>
            </div>
          </div>
          <div class="kpi-icon-box" style="background-color: #7afd88; color: #006b24;">
            <i class="fa-solid fa-circle-check fs-4"></i>
          </div>
        </div>
        <div class="kpi-footer-strip">
          <span class="fw-semibold d-inline-flex align-items-center gap-2" style="font-size: 11px; color: #006b29;">
            <span class="pulse-dot"></span> Listas para uso
          </span>
          <span style="font-size: 11px; color: #3e4a3d;">Desocupadas</span>
        </div>
      </div>
    </div>

    <!-- Ocupaciones de Hoy -->
    <div class="col-12 col-sm-6 col-xl-3">
      <div class="kpi-card h-100 d-flex flex-column justify-content-between">
        <div class="d-flex align-items-center justify-content-between">
          <div>
            <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #ba1a1a;">Ocupaciones Hoy</span>
            <div class="d-flex align-items-baseline gap-2 mt-2">
              <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #0b1c30;"><?= $cnt['hoy']; ?></span>
              <span style="font-size: 13px; color: #3e4a3d;">movimientos</span>
            </div>
          </div>
          <div class="kpi-icon-box" style="background-color: #ffdad6; color: #ba1a1a;">
            <i class="fa-solid fa-arrows-rotate fs-4"></i>
          </div>
        </div>
        <div class="kpi-footer-strip">
          <span class="d-inline-flex align-items-center gap-1" style="font-size: 11px; color: #ba1a1a;">
            <i class="fa-solid fa-calendar-check" style="font-size: 12px;"></i> Jornada actual
          </span>
          <span class="fw-semibold" style="font-size: 11px; color: #3c627f;">Turnos</span>
        </div>
      </div>
    </div>
  </div>
  <?php endif; ?>

<?php
// Recopilar aulas de todos los pisos y bloques para alimentar las vistas DataTables
$aulasDisp = [];
$aulasOcup = [];
$aulasTodas = [];

if ($dtPiso) {
    foreach ($dtPiso as $dtp) {
        $maula->setPiso($dtp['piso']);
        $dtBloque = $maula->getAllBloque();
        if ($dtBloque) {
            foreach ($dtBloque as $dbq) {
                $maula->setBloqau($dbq['bloqau']);
                $dtaula = $maula->getAllAula();
                if ($dtaula) {
                    foreach ($dtaula as $dtau) {
                        $mpraul->setIdaul($dtau['idaul']);
                        $dtAuOc = $mpraul->getAulOcu();
                        $itemAula = [
                            'idaul' => $dtau['idaul'],
                            'nomaul' => $dtau['nomaul'],
                            'piso' => $dtp['piso'],
                            'bloqau' => $dbq['bloqau'],
                            'taula' => $dtau['taula'],
                            'ocupada' => !empty($dtAuOc),
                            'dtAuOc' => $dtAuOc
                        ];
                        $aulasTodas[] = $itemAula;
                        if (!empty($dtAuOc)) {
                            $aulasOcup[] = $itemAula;
                        } else {
                            $aulasDisp[] = $itemAula;
                        }
                    }
                }
            }
        }
    }
}

// Extraer pisos y bloques únicos para los filtros de la toolbar
$pisosUnicos = [];
$bloquesUnicos = []; // [piso => [bloques...]]
foreach ($aulasTodas as $item) {
    $p = $item['piso'];
    $b = $item['bloqau'];
    if (!in_array($p, $pisosUnicos)) $pisosUnicos[] = $p;
    if (!isset($bloquesUnicos[$p])) $bloquesUnicos[$p] = [];
    if (!in_array($b, $bloquesUnicos[$p])) $bloquesUnicos[$p][] = $b;
}
sort($pisosUnicos);
foreach ($bloquesUnicos as &$blist) sort($blist);
unset($blist);

// Helper para renderizar cada aula como tarjeta en cuadrícula DataTable
if (!function_exists('renderAulaCard')) {
function renderAulaCard($item, $pg) {
    $idaul = $item['idaul'];
    $nomaul = $item['nomaul'];
    $piso = $item['piso'];
    $bloqau = $item['bloqau'];
    $ocupada = $item['ocupada'];
    $dtAuOc = $item['dtAuOc'];
    $dataModal = (!$ocupada) ? 'onclick="abrirSol('.$idaul.', \''.htmlspecialchars(addslashes($nomaul)).'\')"' : '';
?>
    <div class="card h-100 shadow-sm border-0 vid-card">

        <!-- Cuerpo de la Tarjeta -->
        <div class="card-body p-3 d-flex flex-column justify-content-between">
            <div>
                <!-- Título del aula -->
                <h6 class="fw-bold text-dark mb-2 text-truncate" title="#<?=$idaul;?> - <?=htmlspecialchars($nomaul);?>" style="font-size: 1.05rem;">
                    <span class="<?= !$ocupada ? 'text-success' : 'text-warning'; ?>">#<?=$idaul;?></span> - <?=htmlspecialchars($nomaul);?>
                </h6>

                <!-- Caja de Ubicación y Estado -->
                <div class="p-2 mb-3 rounded-3 bg-light border border-light-subtle d-flex justify-content-between align-items-center" style="font-size: 0.8rem;">
                    <div>
                        <span class="text-muted d-block" style="font-size: 0.72rem;">Ubicación</span>
                        <span class="fw-bold text-dark">Piso <?=$piso;?> &bull; Bloque <?=$bloqau;?></span>
                    </div>
                    <div class="text-end">
                        <span class="text-muted d-block" style="font-size: 0.72rem;">Estado Actual</span>
                        <span class="fw-bold <?= !$ocupada ? 'text-success' : 'text-danger'; ?>">
                            <?= !$ocupada ? 'Habilitada' : 'En Sesión'; ?>
                        </span>
                    </div>
                </div>

                <!-- Datos de Ocupación si aplica -->
                <?php if ($ocupada) { 
                    $noms = explode(" ", $dtAuOc[0]['nomusu'] ?? '');
                    if (isset($noms[0]) && isset($noms[1]) && isset($noms[2])) $nomCorto = $noms[0]." ".$noms[2];
                    elseif (isset($noms[0]) && isset($noms[1])) $nomCorto = $noms[0]." ".$noms[1];
                    else $nomCorto = $dtAuOc[0]['nomusu'] ?? 'Usuario';
                ?>
                    <div class="text-muted small mb-1">
                        <i class="fa-solid fa-user-check text-primary me-1"></i><strong>Instructor:</strong> <?= htmlspecialchars($nomCorto); ?>
                    </div>
                <?php } else { ?>
                    <div class="text-success small mb-1">
                        <i class="fa-solid fa-circle-check me-1"></i>Disponible para asignación inmediata.
                    </div>
                <?php } ?>
            </div>

            <!-- Botón Novedad (solo si aula ocupada) -->
            <?php if ($ocupada && !empty($dtAuOc[0]['idpres'])) { ?>
            <div class="mt-3">
                <button type="button" class="btn btn-outline-primary shadow-sm w-100 d-flex align-items-center justify-content-center gap-2"
                    data-bs-toggle="modal" data-bs-target="#libaul<?=$dtAuOc[0]['idpres'];?>" title="Registrar Novedad">
                    <i class="fa-solid fa-file-export"></i> <span>Novedad</span>
                </button>
            </div>
            <?php } ?>

            <!-- Botón Agendar (solo si aula disponible) -->
            <?php if (!$ocupada) { ?>
            <div class="mt-3">
                <button type="button" class="btn btn-outline-success shadow-sm w-100 d-flex align-items-center justify-content-center gap-2"
                    <?=$dataModal;?> title="Agendar Préstamo de Aula">
                    <i class="fa-solid fa-calendar-plus"></i> <span>Agendar</span>
                </button>
            </div>
            <?php } ?>
        </div>
    </div>
<?php
}
}
?>

<!-- Toolbar de Control y Filtros (Estilo Institucional vvidgen.php) -->
<div class="filter-toolbar-container">
    <!-- Izquierda: Píldoras de Filtro de Estado y Exportación -->
    <div class="toolbar-left-group">
        <div class="filter-pills-group" id="tabAulas" role="tablist">
            <!-- Píldora Disponibles (Activa por defecto) -->
            <button class="btn-filter-pill active" id="activos-tab" data-bs-toggle="tab" data-bs-target="#activos" type="button" role="tab" aria-controls="activos" aria-selected="true" onclick="filterByTab('activos')">
                <span class="pill-dot" style="background-color: #006b29;"></span>
                <span>Disponibles</span>
                <span class="pill-count"><?= count($aulasDisp); ?></span>
            </button>

            <!-- Píldora Ocupadas -->
            <button class="btn-filter-pill" id="inactivos-tab" data-bs-toggle="tab" data-bs-target="#inactivos" type="button" role="tab" aria-controls="inactivos" aria-selected="false" onclick="filterByTab('inactivos')">
                <span class="pill-dot" style="background-color: #ba1a1a;"></span>
                <span>Ocupadas</span>
                <span class="pill-count"><?= count($aulasOcup); ?></span>
            </button>

            <!-- Píldora Todas -->
            <button class="btn-filter-pill" id="todos-tab" data-bs-toggle="tab" data-bs-target="#todos" type="button" role="tab" aria-controls="todos" aria-selected="false" onclick="filterByTab('todos')">
                <span class="pill-dot" style="background-color: #3c627f;"></span>
                <span>Todas</span>
                <span class="pill-count"><?= count($aulasTodas); ?></span>
            </button>
        </div>

        <!-- Separador -->
        <div class="toolbar-divider d-none d-sm-block"></div>

        <!-- Botones de Exportación integrados -->
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

    <!-- Derecha: Búsqueda, Filtros de Piso/Bloque, Ordenamiento y Vista Grid/Lista -->
    <div class="toolbar-search-sort-group" style="flex-wrap:wrap; gap:6px;">
        <!-- Input de búsqueda sincronizada con DataTables -->
        <div class="toolbar-search-box">
            <i class="fa-solid fa-magnifying-glass icoldiz"></i>
            <input type="text" id="customVideoSearch" placeholder="Buscar aula, piso, bloque..." onkeyup="syncCustomSearch(this.value)">
        </div>

        <!-- Selector de Piso -->
        <select class="toolbar-select-sort" id="filterPiso" onchange="onPisoChange()" title="Filtrar por Piso" style="min-width:110px;">
            <option value="">Todos los Pisos</option>
            <?php foreach ($pisosUnicos as $p): ?>
            <option value="<?= $p ?>"><?= 'Piso ' . $p ?></option>
            <?php endforeach; ?>
        </select>

        <!-- Selector de Bloque (se actualiza dinámicamente) -->
        <select class="toolbar-select-sort" id="filterBloque" onchange="redrawAllTables()" title="Filtrar por Bloque" style="min-width:120px;">
            <option value="">Todos los Bloques</option>
            <?php foreach ($bloquesUnicos as $pisoKey => $bloqList): foreach ($bloqList as $b): ?>
            <option value="<?= $b ?>" data-piso="<?= $pisoKey ?>"><?= 'Bloque ' . $b ?></option>
            <?php endforeach; endforeach; ?>
        </select>

        <!-- Controles Secundarios: Selector de Orden y Conmutador de Vistas -->
        <div class="toolbar-secondary-controls">
            <!-- Selector de ordenamiento -->
            <select class="toolbar-select-sort" id="customVideoSort" onchange="syncCustomSort(this.value)">
                <option value="asc">Ascendente</option>
                <option value="desc">Descendente</option>
            </select>

            <!-- Conmutador de cuadrícula / lista -->
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

<!-- Contenedor de Pestañas con Tablas en Formato Cuadrícula / Tarjetas -->
<div class="tab-content" id="tabAulasContent">
    <!-- Pestaña 1: Aulas Disponibles -->
    <div class="tab-pane fade show active" id="activos" role="tabpanel" aria-labelledby="activos-tab">
        <table id="example" class="table table-striped table-vid-cards" style="width:100%">
            <thead>
                <tr>
                    <th>Aulas Disponibles</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($aulasDisp) { foreach ($aulasDisp as $item) { ?>
                <tr data-order="<?=$item['idaul'];?>">
                    <td data-search="<?=$item['idaul'];?> <?=$item['nomaul'];?> Piso <?=$item['piso'];?> Bloque <?=$item['bloqau'];?>">
                        <?php renderAulaCard($item, $pg); ?>
                    </td>
                </tr>
                <?php }} ?>
            </tbody>
            <tfoot>
                <tr>
                    <th>Aulas Disponibles</th>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Pestaña 2: Aulas Ocupadas -->
    <div class="tab-pane fade" id="inactivos" role="tabpanel" aria-labelledby="inactivos-tab">
        <table id="myt" class="table table-striped table-vid-cards" style="width:100%">
            <thead>
                <tr>
                    <th>Aulas Ocupadas</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($aulasOcup) { foreach ($aulasOcup as $item) { ?>
                <tr data-order="<?=$item['idaul'];?>">
                    <td data-search="<?=$item['idaul'];?> <?=$item['nomaul'];?> Piso <?=$item['piso'];?> Bloque <?=$item['bloqau'];?> <?=($item['dtAuOc'][0]['nomusu'] ?? '');?>">
                        <?php renderAulaCard($item, $pg); ?>
                    </td>
                </tr>
                <?php }} ?>
            </tbody>
            <tfoot>
                <tr>
                    <th>Aulas Ocupadas</th>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Pestaña 3: Todas las Aulas -->
    <div class="tab-pane fade" id="todos" role="tabpanel" aria-labelledby="todos-tab">
        <table id="tblTodos" class="table table-striped table-vid-cards" style="width:100%">
            <thead>
                <tr>
                    <th>Todas las Aulas</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($aulasTodas) { foreach ($aulasTodas as $item) { ?>
                <tr data-order="<?=$item['idaul'];?>">
                    <td data-search="<?=$item['idaul'];?> <?=$item['nomaul'];?> Piso <?=$item['piso'];?> Bloque <?=$item['bloqau'];?> <?=($item['dtAuOc'][0]['nomusu'] ?? '');?>">
                        <?php renderAulaCard($item, $pg); ?>
                    </td>
                </tr>
                <?php }} ?>
            </tbody>
            <tfoot>
                <tr>
                    <th>Todas las Aulas</th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<!-- Modales de Novedad: renderizados UNA SOLA VEZ fuera de DataTables -->
<!-- (si se ponen dentro de <td>, Bootstrap los duplica en cada draw() y parpadean) -->
<?php foreach ($aulasOcup as $mitem): ?>
    <?php if (!empty($mitem['dtAuOc'][0]['idpres'])): ?>
        <?= modalLibCom($mitem['dtAuOc'][0]['idpres'], $mitem['nomaul'], $pg); ?>
    <?php endif; ?>
<?php endforeach; ?>

<!-- Modal de Préstamo de Aula -->
<div class="modal fade" id="solDateModal" tabindex="-1" aria-labelledby="solDateModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content shadow-lg border-0 rounded-3 overflow-hidden">
      <div class="modal-header bg-dark text-white border-0 px-4 py-3">
        <h5 class="modal-title fs-5 d-flex align-items-center gap-2" id="solDateModalLabel">
          <i class="fa-solid fa-door-open text-success"></i> Préstamo de Aula
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <form method="POST" action="home.php?pg=<?=$pg;?>">
          <div class="row g-3">
            <div class="form-group col-12">
              <label for="solIdent" class="form-label fw-bold small text-muted">Número de identificación del solicitante *</label>
              <input type="text" id="solIdent" name="ndocusu" class="form-control" required placeholder="Ej. 1065487523">
            </div>
            <div class="form-group col-12">
              <label for="solFecha" class="form-label fw-bold small text-muted">Día *</label>
              <input type="date" id="solFecha" name="fecfin" class="form-control" required>
            </div>
            <div class="form-group col-md-6">
              <label for="solHorini" class="form-label fw-bold small text-muted">Hora inicio *</label>
              <input type="time" id="solHorini" name="horini" class="form-control" required>
            </div>
            <div class="form-group col-md-6">
              <label for="solHorfin" class="form-label fw-bold small text-muted">Hora fin *</label>
              <input type="time" id="solHorfin" name="horfin" class="form-control" required>
            </div>
          </div>
          <input type="hidden" id="solIdaul" name="idaul">
          <input type="hidden" name="ope" value="save">
          <div class="modal-footer bg-light border-0 px-0 pb-0 mt-4 d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-success d-inline-flex align-items-center gap-2">
              <i class="fa-solid fa-check"></i> Confirmar Préstamo
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
function abrirSol(idaul, nomaul){
  document.getElementById('solIdaul').value = idaul;
  document.getElementById('solIdent').value = '';
  document.getElementById('solFecha').value = '';
  document.getElementById('solHorini').value = '';
  document.getElementById('solHorfin').value = '';
  document.getElementById('solDateModalLabel').innerHTML = '<i class="fa-solid fa-door-open text-success me-2"></i> Préstamo de Aula ' + nomaul;
  new bootstrap.Modal(document.getElementById('solDateModal')).show();
}

// Mapa de bloques por piso generado desde PHP
var bloquesPorPiso = <?php
  $map = [];
  foreach ($bloquesUnicos as $pisoKey => $bloqList) {
    $map[(string)$pisoKey] = array_values($bloqList);
  }
  echo json_encode($map);
?>;

// Registrar filtro externo nativo de DataTables para Piso y Bloque
// Se ejecuta en cada draw() — devuelve true si la fila pasa el filtro
$.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
  var pisoSel = (document.getElementById('filterPiso')  || {}).value || '';
  var bloqSel = (document.getElementById('filterBloque') || {}).value || '';
  if (!pisoSel && !bloqSel) return true; // sin filtros activos

  // Leer data-search del <tr> ó del <td> según la estructura
  var tr = settings.aoData[dataIndex] ? settings.aoData[dataIndex].nTr : null;
  if (!tr) return true;
  var td = tr.querySelector('td[data-search]');
  var searchVal = td ? (td.getAttribute('data-search') || '').toLowerCase() : '';

  if (pisoSel && searchVal.indexOf('piso ' + pisoSel.toLowerCase()) === -1) return false;
  if (bloqSel && searchVal.indexOf('bloque ' + bloqSel.toLowerCase()) === -1) return false;
  return true;
});

/**
 * Cuando cambia el piso: actualiza opciones del bloque y redibuja.
 */
function onPisoChange() {
  var pisoSel = document.getElementById('filterPiso').value;
  var bloqSel = document.getElementById('filterBloque');
  var prevVal  = bloqSel.value;

  bloqSel.innerHTML = '<option value="">Todos los Bloques</option>';
  var lista = pisoSel === '' ? Object.values(bloquesPorPiso).flat() : (bloquesPorPiso[pisoSel] || []);
  lista = [...new Set(lista)].sort(function(a,b){ return a - b; });
  lista.forEach(function(b) {
    var opt = document.createElement('option');
    opt.value = b;
    opt.textContent = 'Bloque ' + b;
    if (String(b) === prevVal) opt.selected = true;
    bloqSel.appendChild(opt);
  });

  redrawAllTables();
}


/**
 * Redibuja todas las instancias DataTable .table-vid-cards
 * para que el filtro externo se aplique.
 */
function redrawAllTables() {
  if (typeof $.fn.dataTable !== 'undefined') {
    $('.table-vid-cards').each(function() {
      if ($.fn.DataTable.isDataTable(this)) {
        $(this).DataTable().draw();
      }
    });
  }
}
</script>
</div>