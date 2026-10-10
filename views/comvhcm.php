<?php

require_once("controllers/comchcm.php");
require_once("controllers/comchtr.php");
require_once("controllers/comcemp.php");
?>
<div class="conte">
    <?php echo titulo2("<i class='fas fa-calendar-alt'></i> Programación de Horarios", 2); ?>
    <?php
    $ht = [];
    if (!empty($datOneHt) && !empty($datOneHt[0])) {
        $ht = $datOneHt[0];
    } elseif (!empty($datOne) && !empty($datOne[0])) {
        $ht = $datOne[0];
    }
    ?>
    <?php if (!empty($ht)): ?>
        <h2>Hoja de Trabajo No. <?= htmlspecialchars($ht['idnorad'] ?? ''); ?></h2>
    <?php endif; ?>
    <!-- DASHBOARD DE HORAS -->
    <div class="row my-1 g-4">
        <!-- Horas totales -->
        <div class="col-12 col-md-4">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold small text-secondary">Horas Totales</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold fs-2 lh-1"><?= htmlspecialchars(number_format((float)($horasTotales ?? 0), 0)); ?></span>
                            <span class="small text-secondary">horas</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box bg-primary bg-opacity-10 text-primary">
                        <i class="fa-solid fa-clock fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="fw-semibold d-inline-flex align-items-center gap-1 small text-primary">
                        <i class="fa-solid fa-book-open"></i> Duración programa
                    </span>
                    <span class="small text-secondary"><?= htmlspecialchars(trim(($ht['codpro'] ?? '') . ' - ' . ($ht['nompro'] ?? ''), ' -')) ?: 'N/A'; ?></span>
                </div>
            </div>
        </div>

        <!-- Horas asignadas -->
        <div class="col-12 col-md-4">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold small text-success">Horas Asignadas</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold fs-2 lh-1 text-success"><?= htmlspecialchars(number_format((float)($horasAsignadas ?? 0), 1)); ?></span>
                            <span class="small text-success">horas</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box bg-success bg-opacity-10 text-success">
                        <i class="fa-solid fa-circle-check fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="fw-semibold d-inline-flex align-items-center gap-2 small text-success">
                        <span class="pulse-dot"></span> <?= htmlspecialchars($porcAsignado ?? 0); ?>% programado
                    </span>
                    <span class="small text-secondary"><?= count($horarioGuardado ?? []); ?> sesiones</span>
                </div>
            </div>
        </div>

        <!-- Horas sin asignar -->
        <div class="col-12 col-md-4">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold small text-danger">Horas Sin Asignar</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold fs-2 lh-1"><?= htmlspecialchars(number_format((float)($horasSinAsignar ?? 0), 1)); ?></span>
                            <span class="small text-secondary">horas</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box bg-danger bg-opacity-10 text-danger">
                        <i class="fa-solid fa-hourglass-half fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="d-inline-flex align-items-center gap-1 small text-secondary">
                        <i class="fa-solid fa-triangle-exclamation"></i> Pendientes por programar
                    </span>
                    <span class="fw-semibold small text-primary">Por asignar</span>
                </div>
            </div>
        </div>
    </div>
    <?php if (($horasTotales ?? 0) > 0): ?>
        <div class="progress mt-3" style="height: 10px;" title="<?= htmlspecialchars($porcAsignado ?? 0); ?>% asignado">
            <div class="progress-bar bg-success" role="progressbar" style="width: <?= htmlspecialchars($porcAsignado ?? 0); ?>%;" aria-valuenow="<?= htmlspecialchars($porcAsignado ?? 0); ?>" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
    <?php endif; ?>
    <br>
    <div class="row g-3 mb-3">
        <div class="col-12 col-lg-6">
            <div class="card h-100 shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fa-solid fa-file-lines me-2 text-success"></i>1. Datos de la Hoja de Trabajo</h5>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($ht)): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-sm mb-0">
                                <tbody>
                                    <tr>
                                        <th scope="row" class="table-light">Programa</th>
                                        <td colspan="3"><strong><?= htmlspecialchars(trim(($ht['codpro'] ?? '') . ' - ' . ($ht['nompro'] ?? ''), ' -')) ?: '---'; ?></strong></td>
                                    </tr>
                                    <tr>
                                        <th scope="row" class="table-light">Empresa</th>
                                        <td><?= htmlspecialchars($ht['nomemp'] ?? '') ?: '---'; ?></td>
                                        <th scope="row" class="table-light">Fecha Inicial</th>
                                        <td><?= htmlspecialchars($ht['feclini'] ?? '') ?: '---'; ?></td>
                                    </tr>
                                    <tr>
                                        <th scope="row" class="table-light">Programa Especial</th>
                                        <td><?= htmlspecialchars($ht['proesp'] ?? '') ?: '---'; ?></td>
                                        <th scope="row" class="table-light">Fecha Final</th>
                                        <td><?= htmlspecialchars($ht['feclin'] ?? '') ?: '---'; ?></td>
                                    </tr>
                                    <tr>
                                        <th scope="row" class="table-light">Cupo</th>
                                        <td><strong><?= htmlspecialchars(!empty($ht['cupo']) ? $ht['cupo'] : '---'); ?></strong></td>
                                        <th scope="row" class="table-light">Jornada</th>
                                        <td><?= htmlspecialchars($ht['jorn'] ?? '') ?: '---'; ?></td>
                                    </tr>
                                    <tr>
                                        <th scope="row" class="table-light">Cod. Ficha</th>
                                        <td><?= htmlspecialchars($ht['idfic'] ?? '') ?: '---'; ?></td>
                                        <th scope="row" class="table-light">Cod. Solicitud Empresa</th>
                                        <td><?= htmlspecialchars($ht['codslem'] ?? '') ?: '---'; ?></td>
                                    </tr>
                                    <tr>
                                        <th scope="row" class="table-light">Convenio</th>
                                        <td colspan="3"><?= htmlspecialchars($ht['convht'] ?? '') ?: 'No aplica / Sin convenio específico'; ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="p-3">
                            <div class="alert alert-info mb-0">
                                <i class="fa-solid fa-circle-info me-2"></i> No hay datos de hoja de trabajo para mostrar.
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <!-- DATOS DE LA EMPRESA -->
        <div class="col-12 col-lg-6">
            <div class="card h-100 shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fa-solid fa-briefcase me-2 text-primary"></i>2. Datos de la Empresa</h5>
                </div>
                <div class="card-body p-0">
                    <?php
                    if (!empty($datOneEmp) && is_array($datOneEmp) && !empty($datOneEmp[0])):
                        $empresa = $datOneEmp[0];
                        $dirEmp = trim(htmlspecialchars($empresa['diremp'] ?? '') . ' ' . htmlspecialchars($empresa['nommun'] ?? '') . ' ' . htmlspecialchars($empresa['nomdep'] ?? ''));
                    ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-sm mb-0">
                                <tbody>
                                    <tr>
                                        <th scope="row" class="table-light">Empresa</th>
                                        <td colspan="3"><strong><?= htmlspecialchars($empresa['nomemp'] ?? '') ?: '---'; ?></strong></td>
                                    </tr>
                                    <tr>
                                        <th scope="row" class="table-light">NIT</th>
                                        <td><?= htmlspecialchars($empresa['numdocemp'] ?? '') ?: '---'; ?></td>
                                        <th scope="row" class="table-light">Teléfono</th>
                                        <td><?= htmlspecialchars($empresa['telemp'] ?? '') ?: '---'; ?></td>
                                    </tr>
                                    <tr>
                                        <th scope="row" class="table-light">Dirección</th>
                                        <td colspan="3"><?= $dirEmp !== '' ? $dirEmp : '---'; ?></td>
                                    </tr>
                                    <tr>
                                        <th scope="row" class="table-light">Persona de Contacto</th>
                                        <td colspan="3"><?= htmlspecialchars($empresa['nomconemp'] ?? '') ?: '---'; ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="p-3">
                            <div class="alert alert-info mb-0">
                                <i class="fa-solid fa-circle-info me-2"></i> No hay datos de la empresa para mostrar.
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<br>
<!-- AGREGAR HORARIO -->
<div class="card">
    <div class="card-body">
        <h4 class="card-title">Agregar Horario</h4>
        <br>
        <?php
        $fIniH = '';
        $fFinH = '';
        if (!empty($ht['feclini'])) {
            $fIniH = date('Y-m-d', strtotime($ht['feclini']));
        }
        if (!empty($ht['feclin'])) {
            $fFinH = date('Y-m-d', strtotime($ht['feclin']));
        }
        $horariosHojaJson = [];
        if (!empty($horarioGuardado)) {
            foreach ($horarioGuardado as $hg) {
                if (!empty($hg['fecha_especifica'])) {
                    $horariosHojaJson[] = [
                        'f' => date('Y-m-d', strtotime($hg['fecha_especifica'])),
                        'u' => (string) ($hg['idusu'] ?? ''),
                        'ini' => !empty($hg['hinihor']) ? substr($hg['hinihor'], 0, 5) : '',
                        'fin' => !empty($hg['hfinhor']) ? substr($hg['hfinhor'], 0, 5) : ''
                    ];
                }
            }
        }
        ?>
        <?php if (!empty($instructoresAsignados)): ?>
            <form method="post" id="form_add_horario" action="home.php?pg=<?= htmlspecialchars($pg ?? '2000'); ?>&opera=add_horario&idnorad=<?= htmlspecialchars($idnorad ?? '') ?>">
                <div class="row">
                    <div class="form-group col-md-3">
                        <label for="fecha_nueva">Fecha</label>
                        <input type="date" name="fecha_nueva" id="fecha_nueva" class="form-control"
                            <?php if ($fIniH) echo 'min="' . $fIniH . '"'; ?>
                            <?php if ($fFinH) echo 'max="' . $fFinH . '"'; ?> required>
                        <small id="msg_fecha_hor" class="text-danger d-block mt-1"></small>
                    </div>
                    <div class="form-group col-md-3">
                        <label for="hora_inicio_nueva">Hora Inicio</label>
                        <input type="time" name="hora_inicio_nueva" id="hora_inicio_nueva" class="form-control" step="60" required>
                    </div>
                    <div class="form-group col-md-3">
                        <label for="hora_fin_nueva">Hora Terminación</label>
                        <input type="time" name="hora_fin_nueva" id="hora_fin_nueva" class="form-control" step="60" required>
                    </div>
                    <div class="form-group col-md-3">
                        <label for="idusu_hor">Instructor</label>
                        <select name="idusu_hor" id="idusu_hor" class="form-control form-select" required>
                            <option value="" selected disabled>-- Seleccione un instructor --</option>
                            <?php foreach ($instructoresAsignados as $insHor): ?>
                                <option value="<?= htmlspecialchars($insHor['idusu']); ?>"><?= htmlspecialchars($insHor['nomusu']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="text-center mt-3">
                    <button type="submit" id="btn_guardar_hor" class="btn btn-primary">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Guardar Horario
                    </button>
                </div>
            </form>
            <script>
                var horariosHoja = <?php echo json_encode($horariosHojaJson); ?>;
                var horariosInstructor = <?php echo json_encode(!empty($horariosUsu) ? $horariosUsu : new stdClass()); ?>;

                function seCruzan(a1, a2, b1, b2) {
                    return a1 < b2 && b1 < a2;
                }

                function validarFechaHorario() {
                    var instructor = document.getElementById('idusu_hor').value;
                    var fecha = document.getElementById('fecha_nueva').value;
                    var hIni = document.getElementById('hora_inicio_nueva').value;
                    var hFin = document.getElementById('hora_fin_nueva').value;
                    var btn = document.getElementById('btn_guardar_hor');
                    var msg = document.getElementById('msg_fecha_hor');
                    msg.textContent = '';
                    btn.disabled = false;
                    if (!instructor || !fecha || !hIni || !hFin) return true;

                    if (hFin <= hIni) {
                        msg.textContent = 'La hora de terminación debe ser mayor que la hora de inicio.';
                        btn.disabled = true;
                        return false;
                    }

                    var cruzaPropio = (horariosInstructor[instructor] || []).some(function (h) {
                        return h.f === fecha && h.ini && h.fin && seCruzan(hIni, hFin, h.ini, h.fin);
                    });
                    if (cruzaPropio) {
                        msg.textContent = 'Este instructor ya tiene ocupado ese horario en esa fecha (en esta u otra ficha).';
                        btn.disabled = true;
                        return false;
                    }

                    var cruzaOtro = horariosHoja.some(function (h) {
                        return h.f === fecha && h.u !== instructor && h.ini && h.fin && seCruzan(hIni, hFin, h.ini, h.fin);
                    });
                    if (cruzaOtro) {
                        msg.textContent = 'Ese horario ya está asignado a otro instructor en esa fecha dentro de esta hoja de trabajo.';
                        btn.disabled = true;
                        return false;
                    }
                    return true;
                }

                document.getElementById('idusu_hor').addEventListener('change', validarFechaHorario);
                document.getElementById('fecha_nueva').addEventListener('change', validarFechaHorario);
                document.getElementById('hora_inicio_nueva').addEventListener('change', validarFechaHorario);
                document.getElementById('hora_fin_nueva').addEventListener('change', validarFechaHorario);
                document.getElementById('form_add_horario').addEventListener('submit', function (e) {
                    if (!validarFechaHorario()) {
                        e.preventDefault();
                    }
                });
            </script>
        <?php else: ?>
            <div class="alert alert-warning mb-0">
                <i class="fa-solid fa-triangle-exclamation me-2"></i>
                Para agregar horarios primero debe asignar al menos un instructor a esta hoja de trabajo.
            </div>
        <?php endif; ?>
    </div>
</div>

<br>
<!-- HORARIO -->
<div class="card">
    <div class="card-body">
        <h4 class="card-title">Horario de la Hoja de Trabajo</h4>
        <br>
        <div class="row">
            <div class="form-group col-md-6 col-lg-4">
                <label for="sel_instr_view" class="fw-bold">Instructor</label>
                <select id="sel_instr_view" class="form-control form-select">
                    <option value="" selected disabled>-- Seleccione un instructor --</option>
                    <?php if (!empty($instructoresAsignados)): ?>
                        <?php foreach ($instructoresAsignados as $insV): ?>
                            <option value="<?= htmlspecialchars($insV['idusu']); ?>"><?= htmlspecialchars($insV['nomusu']); ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
        </div>
        <br>
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-success">
                    <tr>
                        <th>Fecha</th>
                        <th>Día</th>
                        <th>Hora Inicio</th>
                        <th>Hora Terminación</th>
                    </tr>
                </thead>
                <tbody id="horario_view_body">
                    <?php if (!empty($horarioGuardado)): ?>
                        <?php
                        $dias_hor = ['Monday' => 'Lunes', 'Tuesday' => 'Martes', 'Wednesday' => 'Miércoles', 'Thursday' => 'Jueves', 'Friday' => 'Viernes', 'Saturday' => 'Sábado', 'Sunday' => 'Domingo'];
                        foreach ($horarioGuardado as $hor):
                            $fecTxt = '';
                            $diaTxt = '';
                            if (!empty($hor['fecha_especifica'])) {
                                try {
                                    $objFec = new DateTime($hor['fecha_especifica']);
                                    $fecTxt = $objFec->format('d/m/Y');
                                    $diaTxt = $dias_hor[$objFec->format('l')] ?? '';
                                } catch (Exception $e) {
                                    $fecTxt = htmlspecialchars($hor['fecha_especifica']);
                                }
                            }
                            ?>
                            <tr data-idusu="<?= htmlspecialchars($hor['idusu'] ?? ''); ?>">
                                <td><?= $fecTxt; ?></td>
                                <td><?= $diaTxt; ?></td>
                                <td><?= !empty($hor['hinihor']) ? htmlspecialchars(substr($hor['hinihor'], 0, 5)) : ''; ?></td>
                                <td><?= !empty($hor['hfinhor']) ? htmlspecialchars(substr($hor['hfinhor'], 0, 5)) : ''; ?></td>
                            </tr>
                        <?php
                        endforeach;
                        ?>
                    <?php endif; ?>
                    <tr id="horario_msg">
                        <td colspan="4" class="text-center text-muted">Seleccione un instructor para ver sus horarios.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
<script>
    document.getElementById('sel_instr_view').addEventListener('change', function () {
        var idusu = this.value;
        var vistos = 0;
        document.querySelectorAll('#horario_view_body tr[data-idusu]').forEach(function (tr) {
            if (tr.getAttribute('data-idusu') === idusu) {
                tr.style.display = '';
                vistos++;
            } else {
                tr.style.display = 'none';
            }
        });
        var msg = document.getElementById('horario_msg');
        if (vistos > 0) {
            msg.style.display = 'none';
        } else {
            msg.style.display = '';
            msg.cells[0].textContent = 'Este instructor no tiene horarios asignados.';
        }
    });
</script>

<br>
<!-- SECCIÓN DE INSTRUCTORES CORREGIDA -->
<div class="d-flex justify-content-end gap-2 mb-3">
    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#AgreUsuIns">
        <i class="fa-solid fa-user-plus"></i> Asignar Instructor
    </button>

    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#CargaAsp">
        <i class="fa-solid fa-upload"></i> Cargar Aspirantes
    </button>
</div>

<!-- TABLA DE INSTRUCTORES ASIGNADOS -->
<?php if (!empty($instructoresAsignados)): ?>
    <div class="card shadow-sm">
        <div class="card-header bg-white border-bottom">
            <h5 class="mb-0 text-primary"><i class="fa-solid fa-users-gear"></i> Instructores Asignados a esta Hoja</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Nombre del Instructor</th>
                            <th scope="col" class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $contador = 1;
                        foreach ($instructoresAsignados as $ins): ?>
                            <tr>
                                <td class="text-center fw-bold"><?= $contador++; ?></td>
                                <td>
                                    <i class="fa-solid fa-user-tie text-secondary me-2"></i>
                                    <?= htmlspecialchars($ins['nomusu']); ?>
                                </td>
                                <td class="text-center">
                                    <a href="home.php?pg=<?= $pg; ?>&opera=del_instructor&idnorad=<?= $idnorad; ?>&idusu=<?= $ins['idusu']; ?>"
                                        class="btn btn-sm btn-outline-danger"
                                        title="Eliminar instructor"
                                        onclick="return confirm('¿Está seguro de eliminar a este instructor de la hoja de trabajo?');">
                                        <i class="fa-solid fa-trash-can"></i> Eliminar
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="alert alert-info d-flex align-items-center" role="alert">
        <i class="fa-solid fa-circle-info me-2"></i>
        <div>No hay instructores asignados actualmente a esta hoja de trabajo.</div>
    </div>
<?php endif; ?>

<!-- MODAL AGREGAR INSTRUCTOR CORREGIDO -->
<div class="modal fade" id="AgreUsuIns" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <!-- CAMBIO AQUÍ: opera=add_instructor en lugar de guardar_hrs -->
            <form method="POST" action="home.php?pg=<?= htmlspecialchars($pg ?? '2000'); ?>&opera=add_instructor&idnorad=<?= htmlspecialchars($idnorad ?? '') ?>">
                <div class="modal-header bg-success text-white">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">Asignar Nuevo Instructor</h1>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="idusu_ins" class="form-label fw-bold">Seleccione el Instructor:</label>
                        <select name="idusu" id="idusu_ins" class="form-select" required>
                            <option value="" selected disabled>-- Seleccione un usuario --</option>
                            <?php if (!empty($listaInstructores)): ?>
                                <?php foreach ($listaInstructores as $usuario): ?>
                                    <?php
                                    // Filtrar para no mostrar los que ya están asignados
                                    $yaAsignado = false;
                                    if (!empty($instructoresAsignados)) {
                                        foreach ($instructoresAsignados as $asignado) {
                                            if ($asignado['idusu'] == $usuario['idusu']) $yaAsignado = true;
                                        }
                                    }
                                    if (!$yaAsignado):
                                    ?>
                                        <option value="<?= $usuario['idusu']; ?>"><?= htmlspecialchars($usuario['nomusu']); ?></option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Guardar Asignación</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL CARGA ASPIRANTES -->
<div class="modal fade" id="CargaAsp" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h1 class="modal-title fs-5" id="exampleModalLabel">Cargar Aspirantes</h1>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Seleccione el archivo Excel o CSV con la lista de aspirantes.</p>
                <input type="file" class="form-control">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary">Cargar</button>
            </div>
        </div>
    </div>
</div>

