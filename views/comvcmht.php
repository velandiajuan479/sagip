<?php
require_once("controllers/comccmht.php");

$rutaHojaTrabajo = "home.php?pg=2004";

$cargar = new cchdt();
$cargar->procesar();
$mensaje     = $cargar->mensaje;
$tipoMensaje = $cargar->tipoMensaje;
$datos       = $cargar->datos;
$validacion  = $cargar->validacion;
$resumen     = $cargar->resumen;
$registrado  = $cargar->registrado;
$puedeReg    = $cargar->puedeReg;
$dash        = $cargar->dash;

function estCampo($validacion, $clave) {
    return isset($validacion[$clave]) ? $validacion[$clave] : array('estado'=>'ok','nota'=>'');
}

function esVacio($valor) {
    return trim((string)$valor) === '';
}

// Conteo de estados del archivo cargado
$nOk = 0; $nAviso = 0; $nError = 0;
foreach ($validacion as $v) {
    if ($v['estado'] == 'error') $nError++;
    elseif ($v['estado'] == 'aviso') $nAviso++;
    else $nOk++;
}
?>

<div class="conte">
    <?php echo titulo2("<i class='".$icono."'></i> Cargar Hoja de Trabajo",1); ?>

    <?php if (!empty($mensaje)): ?>
        <div class="alert alert-<?= $tipoMensaje ?> alert-dismissible fade show">
            <?= htmlspecialchars($mensaje) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    <?php endif; ?>

    <div class="row my-4 g-4">
        <!-- Total registradas -->
        <div class="col-12 col-md-4">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold small text-secondary">Hojas Registradas</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold fs-2 lh-1"><?= $dash['total']; ?></span>
                            <span class="small text-secondary">en total</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box bg-primary bg-opacity-10 text-primary">
                        <i class="fa-solid fa-layer-group fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="fw-semibold d-inline-flex align-items-center gap-1 small text-primary">
                        <i class="fa-solid fa-arrow-trend-up"></i> <?= $dash['anio']; ?> este año
                    </span>
                    <span class="small text-secondary">Empresas: <?= $dash['empresas']; ?></span>
                </div>
            </div>
        </div>

        <!-- Cupos -->
        <div class="col-12 col-md-4">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold small text-success">Cupos Ofertados</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold fs-2 lh-1 text-success"><?= $dash['cupos']; ?></span>
                            <span class="small text-success">aprendices</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box bg-success bg-opacity-10 text-success">
                        <i class="fa-solid fa-users fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="fw-semibold d-inline-flex align-items-center gap-2 small text-success">
                        <span class="pulse-dot"></span> Formación complementaria
                    </span>
                    <span class="small text-secondary">Acumulado</span>
                </div>
            </div>
        </div>

        <!-- Pendientes de ficha -->
        <div class="col-12 col-md-4">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold small text-danger">Sin Ficha Asignada</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold fs-2 lh-1"><?= $dash['sinficha']; ?></span>
                            <span class="small text-secondary">hojas</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box bg-danger bg-opacity-10 text-danger">
                        <i class="fa-solid fa-clock-rotate-left fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="d-inline-flex align-items-center gap-1 small text-secondary">
                        <i class="fa-solid fa-hourglass-half"></i> En trámite SOFIA Plus
                    </span>
                    <span class="fw-semibold small text-primary">Por asignar</span>
                </div>
            </div>
        </div>
    </div>

    <div class="cargahdt">
        <form id="frmins" method="POST" enctype="multipart/form-data">
            <h2><i class="fa-solid fa-file-circle-plus"></i> Cargar hoja de trabajo</h2>
            <div class="row">
                <div class="form-group col-md-5">
                    <label for="archivo">Archivo Excel</label>
                    <input type="file" class="form-control" name="archivo" id="archivo" accept=".xls,.xlsx" required>
                    <small class="text-muted">
                        Un archivo por hoja de trabajo &middot; Máximo 5 MB
                        <?php if (!class_exists('ZipArchive')): ?>
                            <br><span class="text-warning">Extensión ZIP deshabilitada: utilice formato .xls</span>
                        <?php endif; ?>
                    </small>
                </div>
                <div class="form-group col-md-4">
                    <label>Formato oficial</label><br>
                    <a href="img/excel/1%20HOJA%20DE%20TRABAJO%20COMPLEMENTARIA%20G1.XLS"
                       class="btn btn-outline-primary" download="1 HOJA DE TRABAJO COMPLEMENTARIA G1.XLS">
                        <i class="fa-solid fa-download"></i> Descargar formato
                    </a>
                    <br><small class="text-muted">Diligéncielo sin modificar su estructura.</small>
                </div>
                <div class="form-group col-md-3">
                    <br>
                    <input type="hidden" name="accion" value="cargar">
                    <input class="btn btn-success w-100" type="submit" value="Subir archivo">
                    <br><br>
                </div>
            </div>
        </form>
    </div>
</div>

<?php if ($registrado): ?>
<div class="conte">
    <div class="alert alert-success">
        <h5 class="mb-2"><i class="fa-solid fa-circle-check me-2"></i>Registro completado</h5>
        <p class="mb-2">Radicado asignado: <strong><?= htmlspecialchars($resumen['idnorad']) ?></strong></p>
        <?php if (!empty($resumen['empresa'])): ?>
            <p class="mb-2 small">
                Se creó automáticamente la empresa <strong><?= htmlspecialchars($resumen['empresa']) ?></strong>.
                Complete sus datos en el módulo Empresa.
            </p>
        <?php endif; ?>
        <a href="<?= $rutaHojaTrabajo ?>" class="btn btn-success btn-sm">
            <i class="fa-solid fa-arrow-right me-1"></i> Ir a Hoja de Trabajo
        </a>
    </div>
</div>
<?php endif; ?>

<?php if (!empty($datos)): ?>

<div class="filter-toolbar-container">
    <div class="toolbar-left-group">
        <div class="filter-pills-group">
            <span class="btn-filter-pill active" onclick="filtrarHdtEstado('todos', this)">
                <span class="pill-dot bg-secondary"></span>
                <span>Todos</span>
                <span class="pill-count"><?= ($nOk + $nAviso + $nError); ?></span>
            </span>
            <span class="btn-filter-pill" onclick="filtrarHdtEstado('ok', this)">
                <span class="pill-dot bg-success"></span>
                <span>Correctos</span>
                <span class="pill-count"><?= $nOk; ?></span>
            </span>
            <span class="btn-filter-pill" onclick="filtrarHdtEstado('aviso', this)">
                <span class="pill-dot bg-warning"></span>
                <span>Avisos</span>
                <span class="pill-count"><?= $nAviso; ?></span>
            </span>
            <span class="btn-filter-pill" onclick="filtrarHdtEstado('error', this)">
                <span class="pill-dot bg-danger"></span>
                <span>Errores</span>
                <span class="pill-count"><?= $nError; ?></span>
            </span>
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
            <input type="text" id="customVideoSearch" placeholder="Buscar campo" onkeyup="syncCustomSearch(this.value)">
        </div>
    </div>
</div>

<table id="example" class="table table-striped table-vid-cards w-100">
    <thead>
        <tr>
            <th>Campo</th>
            <th>Valor</th>
            <th>Observación</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><strong>Código del programa</strong></td>
            <td><?= esVacio($datos['codpro']) ? '<em class="text-muted">Sin diligenciar</em>' : htmlspecialchars($datos['codpro']) ?></td>
            <?php $e = estCampo($validacion,'codpro'); ?>
            <td>
                <?php if($e['nota']): ?>
                    <span class="badge <?= $e['estado']=='error' ? 'bg-danger' : 'bg-warning text-dark' ?>"><?= htmlspecialchars($e['nota']) ?></span>
                <?php else: ?>
                    <span class="badge bg-success"><i class="fa-solid fa-check"></i> Correcto</span>
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <td><strong>Nombre del programa</strong></td>
            <td><?= esVacio($datos['nompro']) ? '<em class="text-muted">Sin diligenciar</em>' : htmlspecialchars($datos['nompro']) ?></td>
            <td></td>
        </tr>
        <tr>
            <td><strong>Duración máxima</strong></td>
            <td><?= htmlspecialchars($datos['duracion']) ?> horas</td>
            <td></td>
        </tr>
        <tr>
            <td><strong>Código solicitud empresa</strong></td>
            <td><?= esVacio($datos['codslem']) ? '<em class="text-muted">Sin diligenciar</em>' : htmlspecialchars($datos['codslem']) ?></td>
            <td></td>
        </tr>
        <tr>
            <td><strong>Programa especial</strong></td>
            <td><?= htmlspecialchars($datos['progesp']) ?></td>
            <?php $e = estCampo($validacion,'progesp'); ?>
            <td>
                <?php if($e['nota']): ?>
                    <span class="badge <?= $e['estado']=='error' ? 'bg-danger' : 'bg-warning text-dark' ?>"><?= htmlspecialchars($e['nota']) ?></span>
                <?php else: ?>
                    <span class="badge bg-success"><i class="fa-solid fa-check"></i> Correcto</span>
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <td><strong>Convenio</strong></td>
            <td><?= esVacio($datos['convenio']) ? '<em class="text-muted">No aplica</em>' : htmlspecialchars($datos['convenio']) ?></td>
            <td></td>
        </tr>
        <tr>
            <td><strong>Empresa</strong></td>
            <td><?= esVacio($datos['empresa']) ? '<em class="text-muted">Sin diligenciar</em>' : htmlspecialchars($datos['empresa']) ?></td>
            <?php $e = estCampo($validacion,'empresa'); ?>
            <td>
                <?php if($e['nota']): ?>
                    <span class="badge <?= $e['estado']=='error' ? 'bg-danger' : 'bg-warning text-dark' ?>"><?= htmlspecialchars($e['nota']) ?></span>
                <?php else: ?>
                    <span class="badge bg-success"><i class="fa-solid fa-check"></i> Correcto</span>
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <td><strong>NIT</strong></td>
            <td><?= esVacio($datos['nit']) ? '<em class="text-muted">Sin diligenciar</em>' : htmlspecialchars($datos['nit']) ?></td>
            <td></td>
        </tr>
        <tr>
            <td><strong>Municipio</strong></td>
            <td><?= esVacio($datos['municipio']) ? '<em class="text-muted">Sin diligenciar</em>' : htmlspecialchars($datos['municipio']) ?></td>
            <td></td>
        </tr>
        <tr>
            <td><strong>Fecha de inicio</strong></td>
            <td><?= esVacio($datos['feclini']) ? '<em class="text-muted">Sin diligenciar</em>' : htmlspecialchars($datos['feclini']) ?></td>
            <td></td>
        </tr>
        <tr>
            <td><strong>Fecha de terminación</strong></td>
            <td><?= esVacio($datos['feclin']) ? '<em class="text-muted">Sin diligenciar</em>' : htmlspecialchars($datos['feclin']) ?></td>
            <?php $e = estCampo($validacion,'fechas'); ?>
            <td>
                <?php if($e['nota']): ?>
                    <span class="badge <?= $e['estado']=='error' ? 'bg-danger' : 'bg-warning text-dark' ?>"><?= htmlspecialchars($e['nota']) ?></span>
                <?php else: ?>
                    <span class="badge bg-success"><i class="fa-solid fa-check"></i> Correcto</span>
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <td><strong>Cupo</strong></td>
            <td><?= htmlspecialchars($datos['cupo']) ?></td>
            <?php $e = estCampo($validacion,'cupo'); ?>
            <td>
                <?php if($e['nota']): ?>
                    <span class="badge <?= $e['estado']=='error' ? 'bg-danger' : 'bg-warning text-dark' ?>"><?= htmlspecialchars($e['nota']) ?></span>
                <?php else: ?>
                    <span class="badge bg-success"><i class="fa-solid fa-check"></i> Correcto</span>
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <td><strong>Jornada</strong></td>
            <td><?= esVacio($datos['jornada']) ? '<em class="text-muted">Sin diligenciar</em>' : htmlspecialchars($datos['jornada']) ?></td>
            <?php $e = estCampo($validacion,'jornada'); ?>
            <td>
                <?php if($e['nota']): ?>
                    <span class="badge <?= $e['estado']=='error' ? 'bg-danger' : 'bg-warning text-dark' ?>"><?= htmlspecialchars($e['nota']) ?></span>
                <?php else: ?>
                    <span class="badge bg-success"><i class="fa-solid fa-check"></i> Correcto</span>
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <td><strong>Código de ficha</strong></td>
            <td><?= esVacio($datos['idfic']) ? '<em class="text-muted">Sin asignar</em>' : htmlspecialchars($datos['idfic']) ?></td>
            <?php $e = estCampo($validacion,'idfic'); ?>
            <td>
                <?php if($e['nota']): ?>
                    <span class="badge <?= $e['estado']=='error' ? 'bg-danger' : 'bg-warning text-dark' ?>"><?= htmlspecialchars($e['nota']) ?></span>
                <?php else: ?>
                    <span class="badge bg-success"><i class="fa-solid fa-check"></i> Correcto</span>
                <?php endif; ?>
            </td>
        </tr>
    </tbody>
    <tfoot>
        <tr>
            <th>Campo</th>
            <th>Valor</th>
            <th>Observación</th>
        </tr>
    </tfoot>
</table>

<div class="conte">
    <p class="text-muted small">
        <i class="fa-solid fa-circle-info me-1"></i>
        <strong>Datos leídos que no se almacenan:</strong> dirección del lugar de formación,
        contacto, teléfono, días y horarios de formación, y datos del instructor.
        La tabla de hojas de trabajo no cuenta con campos para esta información.
    </p>

    <?php if ($puedeReg): ?>
        <form method="POST">
            <input type="hidden" name="accion" value="registrar">
            <button type="submit" class="btn btn-success">
                <i class="fa-solid fa-database me-1"></i> Registrar hoja de trabajo
            </button>
        </form>
    <?php else: ?>
        <div class="alert alert-danger">
            <i class="fa-solid fa-triangle-exclamation me-1"></i>
            Corrija los datos marcados en rojo dentro del archivo y cárguelo de nuevo.
        </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<script>
function filtrarHdtEstado(tipo, el) {
    if (typeof document.querySelectorAll === 'function') {
        document.querySelectorAll('.filter-pills-group .btn-filter-pill').forEach(function(b) {
            b.classList.remove('active');
        });
    }
    if (el) el.classList.add('active');
    
    if (typeof $.fn.DataTable !== 'undefined' && $.fn.DataTable.isDataTable('#example')) {
        var dt = $('#example').DataTable();
        if (tipo === 'todos') {
            dt.column(2).search('').draw();
        } else if (tipo === 'ok') {
            dt.column(2).search('Correcto').draw();
        } else if (tipo === 'aviso') {
            dt.column(2).search('revisión|aviso|creará|asignó|generó|existe|guardará', true, false).draw();
        } else if (tipo === 'error') {
            dt.column(2).search('obligatorio|no está|error|anterior|válida|registrado', true, false).draw();
        }
    }
}
</script>