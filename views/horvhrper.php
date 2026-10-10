<?php require_once 'controllers/horchrper.php'; ?> 

<?php echo titulo2("<i class='".$icono."'></i> Horarios del Usuario", 0); ?> 
<?php require_once("views/vscopehya.php"); ?>

<!-- Alertas y Notificaciones -->
<?php if (!empty($msj_texto)): ?>
    <div class="alert alert-<?=$msj_tipo;?> alert-dismissible fade show my-3" role="alert">
        <i class="fa-solid fa-circle-info me-2"></i> <?=$msj_texto;?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<!-- Panel de Filtros y Búsqueda -->
<div class="card shadow-sm mb-4 border-0">
    <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
        <h6 class="card-title mb-0 text-secondary">
            <i class="fa-solid fa-filter me-2 text-success"></i> Filtros y Selección de Usuario
        </h6>
    </div>
    <div class="card-body">
        <form action="home.php?pg=<?=$pg;?>" method="POST" id="formFiltrosHorario">
            <input type="hidden" name="pg" value="<?=$pg;?>">
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="idfic_filter" class="form-label small fw-bold mb-1">Ficha / Programa:</label>
                    <select name="idfic_filter" id="idfic_filter" class="form-select form-select-sm" onchange="this.form.submit();">
                        <option value="">-- Seleccionar Ficha --</option>
                        <?php if ($allFichas): foreach ($allFichas as $f): ?>
                            <option value="<?=$f['idfic'];?>" <?=$f['idfic'] == $idfic_filter ? 'selected' : '';?>>
                                <?=$f['idfic'];?> - <?=$f['nomfic'];?>
                            </option>
                        <?php endforeach; endif; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="idusu" class="form-label small fw-bold mb-1">Instructor / Usuario:</label>
                    <select name="idusu" id="idusu" class="form-select form-select-sm" onchange="this.form.submit();" <?=$canSelectInstructor ? '' : 'disabled';?>>
                        <option value="">-- <?=$canSelectInstructor ? 'Seleccionar Instructor' : 'Instructor Asignado'?> --</option>
                        <?php if ($din): foreach ($din as $inst): ?>
                            <option value="<?=$inst['idusu'];?>" <?=$inst['idusu'] == $idusu ? 'selected' : '';?>>
                                <?=$inst['nomusu'];?> (<?=$inst['ndocusu'];?>)
                            </option>
                        <?php endforeach; endif; ?>
                    </select>
                    <?php if (!$canSelectInstructor && !empty($idusu)): ?>
                        <input type="hidden" name="idusu" value="<?=$idusu;?>">
                    <?php endif; ?>
                </div>

                <div class="col-md-2">
                    <label for="iddia_filter" class="form-label small fw-bold mb-1">Día:</label>
                    <select name="iddia_filter" id="iddia_filter" class="form-select form-select-sm" onchange="this.form.submit();">
                        <option value="">-- Todos --</option>
                        <?php if ($ddi): foreach ($ddi as $d): ?>
                            <option value="<?=$d['idval'];?>" <?=$d['idval'] == $iddia_filter ? 'selected' : '';?>>
                                <?=$d['nomval'];?>
                            </option>
                        <?php endforeach; endif; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="idaul_filter" class="form-label small fw-bold mb-1">Aula / Ambiente:</label>
                    <select name="idaul_filter" id="idaul_filter" class="form-select form-select-sm" onchange="this.form.submit();">
                        <option value="">-- Todas --</option>
                        <?php if ($dau): foreach ($dau as $a): ?>
                            <option value="<?=$a['idaul'];?>" <?=$a['idaul'] == $idaul_filter ? 'selected' : '';?>>
                                <?=$a['nomaul'];?>
                            </option>
                        <?php endforeach; endif; ?>
                    </select>
                </div>
            </div>

            <div class="d-flex flex-wrap justify-content-between align-items-center mt-3 pt-2 border-top">
                <div class="small mb-0">
                    <?php if (!empty($idusu)): ?>
                        <i class="fa-solid fa-user-check text-success me-1"></i> Instructor: <strong><?=$selectedInstructorName;?></strong> <?=$selectedInstructorDoc ? '('.$selectedInstructorDoc.')' : '';?>
                    <?php else: ?>
                        <span class="badge bg-warning text-dark"><i class="fa-solid fa-triangle-exclamation me-1"></i> Sin instructor asignado</span>
                    <?php endif; ?>
                </div>
                <div class="d-flex gap-2">
                    <a href="home.php?pg=<?=$pg;?>" class="btn btn-outline-secondary btn-sm">
                        <i class="fa-solid fa-rotate-left me-1"></i> Limpiar Filtros
                    </a>
                    <button type="submit" class="btn btn-success btn-sm">
                        <i class="fa-solid fa-magnifying-glass me-1"></i> Buscar
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Resumen de Jornada (único bloque) -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-light py-2">
        <h6 class="card-title mb-0 text-secondary">
            <i class="fa-solid fa-gauge-high me-2 text-success"></i> Resumen de Jornada
        </h6>
    </div>
    <div class="card-body py-3">
        <div class="d-flex flex-column flex-md-row align-items-stretch text-center">
            <div class="flex-fill px-2 py-2">
                <div class="small text-uppercase text-muted fw-bold" style="letter-spacing: 0.5px;">Total Jornada</div>
                <div class="fs-3 fw-bold text-success"><?=$totalHorasSemana;?> h</div>
                <div class="small text-muted">Base semanal</div>
            </div>
            <div class="vr d-none d-md-block my-2"></div>
            <div class="flex-fill px-2 py-2">
                <div class="small text-uppercase text-muted fw-bold" style="letter-spacing: 0.5px;">Formación Directa</div>
                <div class="fs-3 fw-bold text-success"><?=$cntDs;?> h</div>
                <div class="small text-muted">Asignadas en aula</div>
            </div>
            <div class="vr d-none d-md-block my-2"></div>
            <div class="flex-fill px-2 py-2">
                <div class="small text-uppercase text-muted fw-bold" style="letter-spacing: 0.5px;">Alistamiento</div>
                <div class="fs-3 fw-bold text-secondary"><?=$halis;?> h</div>
                <div class="small text-muted">Preparación y gestión</div>
            </div>
            <div class="vr d-none d-md-block my-2"></div>
            <div class="flex-fill px-2 py-2">
                <div class="small text-uppercase text-muted fw-bold" style="letter-spacing: 0.5px;">Horas Disponibles</div>
                <div class="fs-3 fw-bold <?=$hdispo > 0 ? 'text-success' : 'text-danger';?>"><?=$hdispo;?> h</div>
                <div class="small text-muted">Cupo libre para asignación</div>
            </div>
        </div>
    </div>
</div>

<!-- Tabla Principal de Horarios -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-light py-2 d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h6 class="card-title mb-0 text-secondary">
            <i class="fa-solid fa-table-list me-2 text-success"></i> Detalle de Horarios del Instructor
        </h6>
        <div class="d-flex align-items-center gap-2">
            <?php if (!empty($idusu)): ?>
                <a href="views/vrphor.php?idusu=<?=$idusu;?>" target="_blank" class="btn btn-outline-success btn-sm rounded-pill" title="Imprimir Horario">
                    <i class="fa-solid fa-print me-1"></i> Imprimir
                </a>
            <?php endif; ?>
            <?php if ($canEdit && !empty($idusu)): ?>
                <button type="button" class="btn btn-success btn-sm rounded-pill" onclick="nuevoHorario()">
                    <i class="fa-solid fa-plus me-1"></i> Nuevo Horario
                </button>
            <?php endif; ?>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="example" class="table table-striped table-hover align-middle" style="width:100%"> 
                <thead style="background-color:#117f09;color:#fff;">
                    <tr> 
                        <th>Día</th>
                        <th>Hora Inicial</th>
                        <th>Hora Final</th>
                        <th>Duración</th>
                        <th>Ficha</th>
                        <th>Aula</th>
                        <th>Actividad</th>
                        <th>Otros</th>
                        <th class="text-center">Acciones</th>
                    </tr> 
                </thead> 
                <tbody> 
                    <?php if (!empty($allHorarios)): foreach ($allHorarios as $row): ?> 
                        <?php 
                            $rowJson = htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8'); 
                            $durH = NULL;
                            if (!empty($row['hinihor']) && !empty($row['hfinhor']) && $row['hinihor'] !== '00:00:00' && $row['hfinhor'] !== '00:00:00') {
                                $iniT = strtotime($row['hinihor']);
                                $finT = strtotime($row['hfinhor']);
                                if ($finT > $iniT) $durH = round(($finT - $iniT) / 3600, 2);
                            }
                            if ($durH === NULL) {
                                if ($row['es_otros'] == 1 && !empty($row['horas_otros']) && $row['horas_otros'] > 0) $durH = (float)$row['horas_otros'];
                                else if (!empty($row['horajor']) && $row['horajor'] > 0) $durH = (float)$row['horajor'];
                            }
                        ?>
                        <tr> 
                            <td>
                                <span class="badge bg-success text-white"><i class="fa-regular fa-calendar me-1"></i><?=!empty($row['nomdia']) ? htmlspecialchars($row['nomdia']) : 'Día '.$row['iddia'];?></span>
                            </td>
                            <td class="text-nowrap">
                                <?php if (!empty($row['hinihor']) && $row['hinihor'] !== '00:00:00'): ?>
                                    <span class="badge bg-success-subtle text-success border fw-bold"><?=$row['hinihor'];?></span>
                                <?php else: ?>
                                    <span class="badge bg-light text-muted border">Jornada</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-nowrap">
                                <?php if (!empty($row['hfinhor']) && $row['hfinhor'] !== '00:00:00'): ?>
                                    <span class="badge bg-success-subtle text-success border fw-bold"><?=$row['hfinhor'];?></span>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($durH !== NULL): ?>
                                    <span class="badge bg-light text-dark border fw-bold"><?=$durH;?> h</span>
                                <?php else: ?>
                                    <span class="text-muted">Jornada</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-success text-white fw-bold"><?=htmlspecialchars($row['idfic']);?></span>
                                    <div>
                                        <div class="fw-semibold lh-sm"><?=!empty($row['nomfic']) ? htmlspecialchars($row['nomfic']) : 'Actividad Especial';?></div>
                                        <small class="text-muted"><?=!empty($row['nomjor']) ? htmlspecialchars($row['nomjor']) : 'Jornada N/A';?></small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?php if (!empty($row['nomaul'])): ?>
                                    <i class="fa-solid fa-location-dot me-1 text-danger"></i><?=htmlspecialchars($row['nomaul']);?>
                                <?php else: ?>
                                    <span class="text-muted">Sin aula</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($row['es_otros'] == 1): ?>
                                    <span class="small"><i class="fa-solid fa-star me-1 text-warning"></i><?=!empty($row['actividad']) ? htmlspecialchars($row['actividad']) : 'Actividad especial';?></span>
                                <?php else: ?>
                                    <span class="badge bg-success">Formación Directa</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($row['es_otros'] == 1): ?>
                                    <span class="badge bg-warning text-dark">Otros</span>
                                    <?php if ((int)$row['es_formacion_directa'] === 2): ?>
                                        <div class="small text-success fw-semibold lh-sm"><i class="fa-solid fa-graduation-cap me-1"></i>Cuenta como formación</div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted">No</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm" role="group">
                                    <button type="button" class="btn btn-outline-success rounded-pill" title="Ver Detalle" onclick="verDetalle('<?=$rowJson;?>')">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>
                                    <?php if ($canEdit): ?>
                                        <button type="button" class="btn btn-outline-warning rounded-pill" title="Editar" onclick="editarHorario('<?=$rowJson;?>')">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-danger rounded-pill" title="Eliminar" onclick="confirmarEliminar('<?=$row['idhor'];?>')">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr> 
                    <?php endforeach; else: ?>
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">
                                <i class="fa-solid fa-folder-open fa-2x mb-2 d-block"></i>
                                No se encontraron horarios registrados para el usuario o criterios seleccionados.
                            </td>
                        </tr>
                    <?php endif; ?> 
                </tbody> 
            </table> 
        </div>
    </div>
</div>

<!-- Modal Formulario CRUD Horario (Crear / Editar) -->
<?php if ($canEdit): ?>
<div class="modal fade" id="modalHorarioForm" tabindex="-1" aria-labelledby="modalHorarioFormLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="home.php?pg=<?=$pg;?>" method="POST" id="formHorario">
                <input type="hidden" name="pg" value="<?=$pg;?>">
                <input type="hidden" name="ope" value="save">
                <input type="hidden" name="idhor" id="modal_idhor" value="">
                <input type="hidden" name="idusu" id="modal_idusu" value="<?=$idusu;?>">

                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="modalHorarioFormLabel">
                        <i class="fa-solid fa-calendar-plus me-2"></i> <span id="modalTitulo">Nuevo Horario</span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="modal_idfic" class="form-label fw-bold">Ficha / Programa <span class="text-danger">*</span></label>
                            <select name="idfic" id="modal_idfic" class="form-select" required>
                                <option value="">-- Seleccione Ficha --</option>
                                <?php if ($allFichas): foreach ($allFichas as $f): ?>
                                    <option value="<?=$f['idfic'];?>">
                                        <?=$f['idfic'];?> - <?=$f['nomfic'];?>
                                    </option>
                                <?php endforeach; endif; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="modal_iddia" class="form-label fw-bold">Día de la Semana <span class="text-danger">*</span></label>
                            <select name="iddia" id="modal_iddia" class="form-select" required>
                                <option value="">-- Seleccione Día --</option>
                                <?php if ($ddi): foreach ($ddi as $d): ?>
                                    <option value="<?=$d['idval'];?>"><?=$d['nomval'];?></option>
                                <?php endforeach; endif; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="modal_idaul" class="form-label fw-bold">Aula / Ambiente</label>
                            <select name="idaul" id="modal_idaul" class="form-select">
                                <option value="">-- Opcional / Sin Aula --</option>
                                <?php if ($dau): foreach ($dau as $a): ?>
                                    <option value="<?=$a['idaul'];?>"><?=$a['nomaul'];?></option>
                                <?php endforeach; endif; ?>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label for="modal_hinihor" class="form-label fw-bold">Hora Inicio</label>
                            <input type="time" name="hinihor" id="modal_hinihor" class="form-control">
                        </div>

                        <div class="col-md-3">
                            <label for="modal_hfinhor" class="form-label fw-bold">Hora Fin</label>
                            <input type="time" name="hfinhor" id="modal_hfinhor" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <div class="form-check form-switch mt-4">
                                <input class="form-check-input" type="checkbox" name="es_otros" id="modal_es_otros" value="1" onchange="toggleEsOtros()">
                                <label class="form-check-label fw-bold" for="modal_es_otros">¿Es Actividad Especial / Otros?</label>
                            </div>
                        </div>

                        <div class="col-md-6 d-none" id="group_actividad">
                            <label for="modal_actividad" class="form-label fw-bold">Nombre de la Actividad</label>
                            <input type="text" name="actividad" id="modal_actividad" class="form-control" placeholder="Ej. Preparación de clase, investigación">
                        </div>

                        <div class="col-md-6 d-none" id="group_horas_otros">
                            <label for="modal_horas_otros" class="form-label fw-bold">Horas Asignadas</label>
                            <input type="number" step="0.5" name="horas_otros" id="modal_horas_otros" class="form-control" placeholder="Ej. 2.0">
                        </div>

                        <div class="col-md-6 d-none" id="group_formacion_directa">
                            <label for="modal_es_formacion_directa" class="form-label fw-bold">¿Cuenta como Formación Directa?</label>
                            <select name="es_formacion_directa" id="modal_es_formacion_directa" class="form-select">
                                <option value="1">No (solo alistamiento / otros)</option>
                                <option value="2">Sí (formación directa)</option>
                            </select>
                            <div class="form-text">Indica si la actividad especial se suma a la formación directa del instructor.</div>
                        </div>

                        <div class="col-12 d-none" id="group_vigencia">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label for="modal_fecha_inicio" class="form-label fw-bold">Fecha Inicio</label>
                                    <input type="date" name="fecha_inicio" id="modal_fecha_inicio" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label for="modal_fecha_fin" class="form-label fw-bold">Fecha Fin</label>
                                    <input type="date" name="fecha_fin" id="modal_fecha_fin" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label for="modal_fecha_especifica" class="form-label fw-bold">Fecha Específica (evento único)</label>
                                    <input type="date" name="fecha_especifica" id="modal_fecha_especifica" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Guardar Horario
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Modal Vista Detalle -->
<div class="modal fade" id="modalDetalleHorario" tabindex="-1" aria-labelledby="modalDetalleHorarioLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="modalDetalleHorarioLabel">
                    <i class="fa-solid fa-circle-info me-2"></i> Detalle de Horario
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <table class="table table-bordered mb-0">
                    <tr>
                        <th class="bg-light" style="width: 40%;">ID Horario:</th>
                        <td id="det_idhor"></td>
                    </tr>
                    <tr>
                        <th class="bg-light">Instructor:</th>
                        <td id="det_instructor"></td>
                    </tr>
                    <tr>
                        <th class="bg-light">Ficha:</th>
                        <td id="det_ficha"></td>
                    </tr>
                    <tr>
                        <th class="bg-light">Día:</th>
                        <td id="det_dia"></td>
                    </tr>
                    <tr>
                        <th class="bg-light">Franja / Duración:</th>
                        <td id="det_horario"></td>
                    </tr>
                    <tr>
                        <th class="bg-light">Aula / Ambiente:</th>
                        <td id="det_aula"></td>
                    </tr>
                    <tr>
                        <th class="bg-light">Tipo de Actividad:</th>
                        <td id="det_tipo"></td>
                    </tr>
                </table>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() { 
    // Inicialización de DataTables en español (patrón del sistema)
    if ($.fn.DataTable) {
        if ($.fn.DataTable.isDataTable('#example')) {
            $('#example').DataTable().destroy();
        }
        $('#example').DataTable({
            "language": { "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json" },
            "pageLength": 10,
            "lengthMenu": [[5, 10, 25, 50, -1], [5, 10, 25, 50, "Todos"]],
            "order": [[0, 'asc']]
        });
    }

    // Validación del formulario del modal (crear / editar)
    $('#formHorario').on('submit', function(e) {
        if (!$('#modal_idfic').val()) {
            e.preventDefault();
            alert('Debe seleccionar la Ficha / Programa.');
            return false;
        }
        if (!$('#modal_iddia').val()) {
            e.preventDefault();
            alert('Debe seleccionar el Día de la semana.');
            return false;
        }
        var ini = $('#modal_hinihor').val();
        var fin = $('#modal_hfinhor').val();
        if ((ini && !fin) || (!ini && fin)) {
            e.preventDefault();
            alert('Debe indicar la hora de inicio y la hora de fin.');
            return false;
        }
        if (ini && fin && ini >= fin) {
            e.preventDefault();
            alert('La hora de inicio debe ser menor que la hora de fin.');
            return false;
        }
        if ($('#modal_es_otros').is(':checked')) {
            if (!$('#modal_actividad').val().trim()) {
                e.preventDefault();
                alert('Debe indicar el nombre de la actividad especial.');
                return false;
            }
            var hOtr = parseFloat($('#modal_horas_otros').val());
            if (!hOtr || hOtr <= 0) {
                e.preventDefault();
                alert('Debe indicar las horas asignadas a la actividad especial (mayor a 0).');
                return false;
            }
        }
        var fIni = $('#modal_fecha_inicio').val();
        var fFin = $('#modal_fecha_fin').val();
        if (fIni && fFin && fIni > fFin) {
            e.preventDefault();
            alert('La fecha de inicio no puede ser posterior a la fecha de fin.');
            return false;
        }
        return true;
    });
});

function parseData(jsonStr) {
    try {
        return typeof jsonStr === 'string' ? JSON.parse(jsonStr) : jsonStr;
    } catch (e) {
        return {};
    }
}

function toggleEsOtros() {
    var on = $('#modal_es_otros').is(':checked');
    $('#group_actividad').toggleClass('d-none', !on);
    $('#group_horas_otros').toggleClass('d-none', !on);
    $('#group_formacion_directa').toggleClass('d-none', !on);
    $('#group_vigencia').toggleClass('d-none', !on);
}

function nuevoHorario() {
    $('#modalTitulo').text('Nuevo Horario');
    $('#modal_idhor').val('');
    $('#modal_idfic').val('');
    $('#modal_iddia').val('');
    $('#modal_idaul').val('');
    $('#modal_hinihor').val('');
    $('#modal_hfinhor').val('');
    $('#modal_es_otros').prop('checked', false);
    $('#modal_actividad').val('');
    $('#modal_horas_otros').val('');
    $('#modal_es_formacion_directa').val('1');
    $('#modal_fecha_inicio').val('');
    $('#modal_fecha_fin').val('');
    $('#modal_fecha_especifica').val('');
    toggleEsOtros();
    
    var modalEl = document.getElementById('modalHorarioForm');
    var modal = new bootstrap.Modal(modalEl);
    modal.show();
}

function editarHorario(dataRaw) {
    var data = parseData(dataRaw);
    $('#modalTitulo').text('Editar Horario #' + data.idhor);
    $('#modal_idhor').val(data.idhor);
    $('#modal_idfic').val(data.idfic);
    $('#modal_iddia').val(data.iddia);
    $('#modal_idaul').val(data.idaul ? data.idaul : '');
    $('#modal_hinihor').val(data.hinihor && data.hinihor !== '00:00:00' ? data.hinihor : '');
    $('#modal_hfinhor').val(data.hfinhor && data.hfinhor !== '00:00:00' ? data.hfinhor : '');
    
    if (data.es_otros == 1) {
        $('#modal_es_otros').prop('checked', true);
        $('#modal_actividad').val(data.actividad ? data.actividad : '');
        $('#modal_horas_otros').val(data.horas_otros ? data.horas_otros : '');
        $('#modal_es_formacion_directa').val(data.es_formacion_directa ? data.es_formacion_directa : '1');
        $('#modal_fecha_inicio').val(data.fecha_inicio ? data.fecha_inicio : '');
        $('#modal_fecha_fin').val(data.fecha_fin ? data.fecha_fin : '');
        $('#modal_fecha_especifica').val(data.fecha_especifica ? data.fecha_especifica : '');
    } else {
        $('#modal_es_otros').prop('checked', false);
        $('#modal_actividad').val('');
        $('#modal_horas_otros').val('');
        $('#modal_es_formacion_directa').val('1');
        $('#modal_fecha_inicio').val('');
        $('#modal_fecha_fin').val('');
        $('#modal_fecha_especifica').val('');
    }
    toggleEsOtros();

    var modalEl = document.getElementById('modalHorarioForm');
    var modal = new bootstrap.Modal(modalEl);
    modal.show();
}

function verDetalle(dataRaw) {
    var data = parseData(dataRaw);
    $('#det_idhor').text('#' + data.idhor);
    $('#det_instructor').text((data.nomusu ? data.nomusu : 'Sin Instructor') + ' (' + (data.ndocusu ? data.ndocusu : '') + ')');
    $('#det_ficha').text(data.idfic + ' - ' + (data.nomfic ? data.nomfic : 'Actividad Especial'));
    $('#det_dia').text(data.nomdia ? data.nomdia : 'Día ' + data.iddia);
    
    var dur = '-';
    if (data.hinihor && data.hfinhor && data.hinihor !== '00:00:00' && data.hfinhor !== '00:00:00') {
        dur = Math.round((Date.parse('2000-01-01T' + data.hfinhor) - Date.parse('2000-01-01T' + data.hinihor)) / 3600000 * 100) / 100 + ' horas';
    } else if (data.horas_otros) {
        dur = data.horas_otros + ' horas';
    } else if (data.horajor) {
        dur = data.horajor + ' horas';
    } else {
        dur = 'Jornada completa';
    }
    $('#det_horario').text((data.hinihor && data.hinihor !== '00:00:00') ? (data.hinihor + ' a ' + data.hfinhor + ' (' + dur + ')') : dur);
    
    $('#det_aula').text(data.nomaul ? data.nomaul : 'Sin Aula Asignada');
    if (data.es_otros == 1) {
        var tipo = 'Actividad Especial: ' + (data.actividad ? data.actividad : 'Varias');
        if (data.es_formacion_directa == 2) tipo += ' (cuenta como formación directa)';
        $('#det_tipo').text(tipo);
    } else {
        $('#det_tipo').text('Formación Directa');
    }

    var modalEl = document.getElementById('modalDetalleHorario');
    var modal = new bootstrap.Modal(modalEl);
    modal.show();
}

function confirmarEliminar(idhor) {
    if (confirm('¿Está seguro de que desea eliminar el horario #' + idhor + '? Esta acción no se puede deshacer.')) {
        window.location.href = 'home.php?pg=<?=$pg;?>&ope=del&idhor=' + idhor + '&idusu=<?=$idusu;?>';
    }
}
</script>