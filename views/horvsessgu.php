<?php
require_once 'controllers/horcsessgu.php';
?>

<div class="conte">
  <!-- Título igual al de agenda -->
  <?php echo titulo2("<i class='fa-solid fa-chalkboard-user'></i> Seguimiento de Sesiones", 0); ?>
  <?php require_once("views/vscopehya.php"); ?>

  <!-- Botones Superiores Rápidos -->
  <div class="d-flex flex-wrap align-items-center gap-2 mt-4 mb-3">
    <a href="home.php?pg=1509<?= !empty($idfic) ? '&idfic=' . urlencode($idfic) : ''; ?>" class="btn btn-outline-secondary shadow-sm btn-sm fw-semibold" title="Volver a Agenda">
      <i class="fa-solid fa-arrow-left me-1"></i> Volver a Agenda
    </a>
    <?php if ($puedeCrearSesion && $idfic && ($idcom || $idres)): ?>
    <button type="button" class="btn btn-success shadow-sm btn-sm fw-semibold" style="background-color: #006b29; border-color: #006b29;" onclick="abrirModalCrearSesion()">
      <i class="fa-solid fa-plus me-1"></i> Crear Nueva Sesión
    </button>
    <?php endif; ?>
  </div>

  <?php if ($idfic && ($idcom || $idres)): ?>
    
  <!-- Información Prominente de la Ficha Seleccionada (estilo agenda) -->
  <div class="card border-0 shadow-sm rounded-3 p-3 mb-4" style="background: #ffffff; border-left: 5px solid #006b29 !important;">
    <div class="row align-items-center g-3 text-start">
      <div class="col-12">
        <span class="text-uppercase fw-bold d-block text-muted" style="font-size: 10.5px; letter-spacing: 0.5px;">
          <i class="fa-solid fa-id-badge text-success me-1"></i> Ficha de Caracterización
        </span>
        <span class="fw-bold" style="color: #006b29; font-size: 1.35rem;"><?= htmlspecialchars($idfic); ?></span>
      </div>
    </div>
  </div>

  <!-- Dashboard KPI (Métricas principales) -->
  <div class="row my-4 g-3">
    <!-- Total Sesiones -->
    <div class="col-12 col-sm-6 col-md-4">
      <div class="kpi-card h-100 d-flex flex-column justify-content-between">
        <div class="d-flex align-items-center justify-content-between">
          <div>
            <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #006b29;">Total Sesiones</span>
            <div class="d-flex align-items-baseline gap-2 mt-2">
              <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #006b29;"><?= (int)($resumen['total_sesiones'] ?? $resumen['total_planeadas'] ?? 0); ?></span>
              <span style="font-size: 13px; color: rgba(0, 107, 41, 0.8);">registradas</span>
            </div>
          </div>
          <div class="kpi-icon-box" style="background-color: #7afd88; color: #006b24;">
            <i class="fa-solid fa-calendar-check fs-4"></i>
          </div>
        </div>
        <div class="kpi-footer-strip">
          <span class="fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 11px; color: #006b29;">
            <i class="fa-solid fa-list-check"></i> Activas
          </span>
        </div>
      </div>
    </div>

    <!-- Impartidas -->
    <div class="col-12 col-sm-6 col-md-4">
      <div class="kpi-card h-100 d-flex flex-column justify-content-between">
        <div class="d-flex align-items-center justify-content-between">
          <div>
            <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #3c627f;">Impartidas</span>
            <div class="d-flex align-items-baseline gap-2 mt-2">
              <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #0b1c30;"><?= (int)($resumen['cant_impartidas'] ?? 0); ?></span>
              <span style="font-size: 13px; color: #3e4a3d;">sesiones</span>
            </div>
          </div>
          <div class="kpi-icon-box" style="background-color: #b5dcfe; color: #3c627f;">
            <i class="fa-solid fa-check fs-4"></i>
          </div>
        </div>
        <div class="kpi-footer-strip">
          <span class="fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 11px; color: #3c627f;">
            <i class="fa-solid fa-clipboard-check"></i> Verificadas
          </span>
        </div>
      </div>
    </div>

    <!-- Novedades -->
    <div class="col-12 col-sm-6 col-md-4">
      <div class="kpi-card h-100 d-flex flex-column justify-content-between">
        <div class="d-flex align-items-center justify-content-between">
          <div>
            <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #b45309;">Novedades</span>
            <div class="d-flex align-items-baseline gap-2 mt-2">
              <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #b45309;">
                <?= ((int)($resumen['cant_parciales'] ?? 0) + (int)($resumen['cant_no_impartidas'] ?? 0)); ?>
              </span>
              <span style="font-size: 13px; color: #b45309;">alertas</span>
            </div>
          </div>
          <div class="kpi-icon-box" style="background-color: #fffbeb; color: #b45309;">
            <i class="fa-solid fa-triangle-exclamation fs-4"></i>
          </div>
        </div>
        <div class="kpi-footer-strip">
          <span class="fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 11px; color: #b45309;">
            <i class="fa-solid fa-circle-exclamation"></i> Con justificación
          </span>
        </div>
      </div>
    </div>
  </div>

  <!-- Lista de Sesiones (Grid visual) -->
  <div class="card border-0 shadow-sm p-4 mb-4" style="background: #ffffff; border-radius: 0.75rem;">
    <h5 class="fw-bold mb-4" style="color: #0b1c30;"><i class="fa-solid fa-table-list text-success me-2"></i> Registro de Clases y Momentos</h5>
    
    <div class="row g-4">
        <?php if (!empty($sesiones)): ?>
            <?php foreach ($sesiones as $ses): ?>
                <?php
                $idses   = (int)$ses['idses'];
                $numses  = (int)$ses['numses'];
                $fecses  = htmlspecialchars($ses['fecses']);
                $horario = trim(($ses['horini'] ?? '') . ' - ' . ($ses['horfin'] ?? ''));
                if ($horario === '-') $horario = 'Horario asignado';
                ?>
                <div class="col-12 col-lg-6">
                    <div class="card h-100 shadow-sm rounded border-0" style="background-color: #f8f9fa;">
                        <div class="card-header d-flex justify-content-between align-items-center py-3" style="background-color: #ffffff; border-bottom: 2px solid #e9ecef; border-top: 3px solid #006b29;">
                            <div>
                                <span class="badge" style="background-color: #006b29; font-size: 0.85rem;">Sesión <?= $numses; ?></span>
                                <span class="text-muted ms-2 small fw-semibold"><i class="fa-regular fa-calendar me-1"></i><?= $fecses; ?></span>
                            </div>
                            <span class="badge bg-white text-dark border shadow-sm px-2 py-1">
                                <i class="fa-regular fa-clock text-primary me-1"></i><?= htmlspecialchars($horario); ?>
                            </span>
                        </div>
                        <div class="card-body p-3">
                            <div class="bg-white p-2 mb-2 rounded border-start border-3 border-primary shadow-sm">
                                <span class="fw-bold small text-dark"><i class="fa-solid fa-door-open text-primary me-1"></i> 1. Bienvenida e Introducción</span>
                                <p class="mb-0 small text-muted mt-1 ps-3"><?= htmlspecialchars($ses['mom_bienvenida'] ?? 'No especificado.'); ?></p>
                            </div>
                            <div class="bg-white p-2 mb-2 rounded border-start border-3 border-info shadow-sm">
                                <span class="fw-bold small text-dark"><i class="fa-solid fa-lightbulb text-info me-1"></i> 2. Conceptualización / Teoría</span>
                                <p class="mb-0 small text-muted mt-1 ps-3"><?= htmlspecialchars($ses['mom_teoria'] ?? 'No especificado.'); ?></p>
                            </div>
                            <div class="bg-white p-2 mb-2 rounded border-start border-3 border-success shadow-sm">
                                <span class="fw-bold small text-dark"><i class="fa-solid fa-laptop-code text-success me-1"></i> 3. Desarrollo Práctico / Taller</span>
                                <p class="mb-0 small text-muted mt-1 ps-3"><?= htmlspecialchars($ses['mom_practica'] ?? 'No especificado.'); ?></p>
                            </div>
                            <div class="bg-white p-2 mb-2 rounded border-start border-3 border-secondary shadow-sm">
                                <span class="fw-bold small text-dark"><i class="fa-solid fa-flag-checkered text-secondary me-1"></i> 4. Cierre y Conclusiones</span>
                                <p class="mb-0 small text-muted mt-1 ps-3"><?= htmlspecialchars($ses['mom_cierre'] ?? 'No especificado.'); ?></p>
                            </div>
                        </div>
                        <div class="card-footer bg-white border-top py-3 d-flex justify-content-between align-items-center">
                            <div class="small text-muted fw-semibold">
                                <i class="fa-solid fa-user-tie text-success me-1"></i> Instructor: <?= htmlspecialchars($ses['nom_instructor'] ?? 'Asignado'); ?>
                            </div>
                            <?php if ($puedeCrearSesion): ?>
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-primary shadow-sm" onclick="editarSesion(<?= htmlspecialchars(json_encode($ses)); ?>)" title="Editar">
                                    <i class="fa-solid fa-pencil"></i>
                                </button>
                                <button type="button" class="btn btn-outline-danger shadow-sm" onclick="eliminarSesion(<?= $idses; ?>)" title="Eliminar">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="text-center p-5 text-muted border rounded" style="background-color: #f8f9fa; border-style: dashed !important;">
                    <i class="fa-solid fa-calendar-xmark fs-2 mb-3 d-block text-secondary"></i>
                    <h6 class="fw-bold text-dark">No hay sesiones de seguimiento registradas</h6>
                    <p class="small mb-0">Haga clic en 'Crear Nueva Sesión' para registrar los momentos de formación.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
  </div>

  <?php else: ?>
    <!-- Estado Inicial / No hay IDFIC seleccionado (Diseño limpio) -->
    <div class="card border-0 shadow-sm p-5 text-center mt-4" style="background: #ffffff; border-radius: 0.75rem;">
        <i class="fa-solid fa-arrow-pointer fs-1 text-success mb-3 opacity-50"></i>
        <h4 class="fw-bold" style="color: #0b1c30;">Información no disponible</h4>
        <p class="text-muted mx-auto mb-0" style="max-width: 500px;">
            Por favor, asegúrese de ingresar al seguimiento de clases a través de la opción correspondiente en la <strong>Agenda</strong>, seleccionando un resultado o competencia específico.
        </p>
        <div class="mt-4">
            <a href="home.php?pg=1509" class="btn btn-success px-4" style="background-color: #006b29; border-color: #006b29;">
                <i class="fa-solid fa-calendar-days me-2"></i>Ir a la Agenda
            </a>
        </div>
    </div>
  <?php endif; ?>
</div>

<!-- ============================================================== -->
<!-- MODAL: FORMULARIO DE SESIÓN -->
<!-- ============================================================== -->
<?php if ($puedeCrearSesion): ?>
<div class="modal fade" id="modalCrearSesion" tabindex="-1" aria-labelledby="modalCrearSesionLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content shadow-lg border-0" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header bg-dark text-white border-0 px-4 py-3">
                <h5 class="modal-title fs-5 fw-bold d-flex align-items-center gap-2" id="modalTituloTexto">
                    <i class="fa-solid fa-calendar-plus text-success"></i> Crear Sesión
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="frmSesionPlaneacion" onsubmit="guardarSesionAjax(event)">
                <div class="modal-body p-4 bg-light">
                    <input type="hidden" name="idses" id="m_idses" value="">
                    <input type="hidden" name="idfic" id="m_idfic" value="<?= htmlspecialchars($idfic ?? ''); ?>">
                    <input type="hidden" name="idcom" id="m_idcom" value="<?= htmlspecialchars($idcom ?? ''); ?>">
                    <input type="hidden" name="idres" id="m_idres" value="<?= htmlspecialchars($idres ?? ''); ?>">

                    <!-- Datos Básicos -->
                    <div class="p-3 bg-white rounded border shadow-sm mb-3">
                        <label class="form-label fw-bold text-uppercase text-secondary small mb-3">Programación</label>
                        <div class="row g-3">
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-bold small text-dark mb-1">Número de Sesión</label>
                                <input type="number" class="form-control form-control-sm" name="numses" id="m_numses" min="1" value="<?= $siguienteNumSes ?? 1; ?>" required>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-bold small text-dark mb-1">Fecha</label>
                                <input type="date" class="form-control form-control-sm" name="fecses" id="m_fecses" value="<?= date('Y-m-d'); ?>" required>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-bold small text-dark mb-1">Horario</label>
                                <div class="input-group input-group-sm">
                                    <input type="time" class="form-control" name="horini" id="m_horini" value="07:00" required>
                                    <span class="input-group-text bg-light border-start-0 border-end-0">a</span>
                                    <input type="time" class="form-control" name="horfin" id="m_horfin" value="13:00" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Momentos -->
                    <div class="p-3 bg-white rounded border shadow-sm">
                        <label class="form-label fw-bold text-uppercase text-secondary small mb-3">Desarrollo de los 4 Momentos</label>
                        
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-dark d-flex align-items-center gap-1">
                                <i class="fa-solid fa-door-open text-primary"></i> 1. Bienvenida e Introducción
                            </label>
                            <textarea class="form-control form-control-sm" name="mom_bienvenida" id="m_mom_bienvenida" rows="2" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-dark d-flex align-items-center gap-1">
                                <i class="fa-solid fa-lightbulb text-info"></i> 2. Conceptualización / Teoría
                            </label>
                            <textarea class="form-control form-control-sm" name="mom_teoria" id="m_mom_teoria" rows="2" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-dark d-flex align-items-center gap-1">
                                <i class="fa-solid fa-laptop-code text-success"></i> 3. Desarrollo Práctico / Taller
                            </label>
                            <textarea class="form-control form-control-sm" name="mom_practica" id="m_mom_practica" rows="2" required></textarea>
                        </div>
                        <div class="mb-0">
                            <label class="form-label small fw-bold text-dark d-flex align-items-center gap-1">
                                <i class="fa-solid fa-flag-checkered text-secondary"></i> 4. Cierre y Conclusiones
                            </label>
                            <textarea class="form-control form-control-sm" name="mom_cierre" id="m_mom_cierre" rows="2" required></textarea>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer bg-light px-4 py-3 border-top">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success btn-sm px-4 fw-bold shadow-sm" style="background-color: #006b29; border-color: #006b29;">
                        <i class="fa-solid fa-save me-1"></i> Guardar Sesión
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- ============================================================== -->
<!-- SCRIPT JS -->
<!-- ============================================================== -->
<script>
const CONTROLLER_URL = 'controllers/horcsessgu.php';

function abrirModalCrearSesion() {
    document.getElementById('frmSesionPlaneacion').reset();
    document.getElementById('m_idses').value = '';
    
    // Titulo con icono
    document.getElementById('modalTituloTexto').innerHTML = '<i class="fa-solid fa-calendar-plus text-success"></i> Crear Sesión';
    
    // Restaurar los valores ocultos
    document.getElementById('m_idfic').value = '<?= htmlspecialchars($idfic ?? ''); ?>';
    document.getElementById('m_idcom').value = '<?= htmlspecialchars($idcom ?? ''); ?>';
    document.getElementById('m_idres').value = '<?= htmlspecialchars($idres ?? ''); ?>';
    document.getElementById('m_numses').value = '<?= $siguienteNumSes ?? 1; ?>';
    
    let modal = new bootstrap.Modal(document.getElementById('modalCrearSesion'));
    modal.show();
}

function editarSesion(sesion) {
    document.getElementById('m_idses').value = sesion.idses;
    document.getElementById('m_idfic').value = sesion.idfic;
    document.getElementById('m_idcom').value = sesion.idcom || '<?= htmlspecialchars($idcom ?? ''); ?>';
    document.getElementById('m_idres').value = sesion.idres || '<?= htmlspecialchars($idres ?? ''); ?>';
    
    document.getElementById('m_numses').value = sesion.numses;
    document.getElementById('m_fecses').value = sesion.fecses;
    document.getElementById('m_horini').value = sesion.horini;
    document.getElementById('m_horfin').value = sesion.horfin;
    
    document.getElementById('m_mom_bienvenida').value = sesion.mom_bienvenida;
    document.getElementById('m_mom_teoria').value = sesion.mom_teoria;
    document.getElementById('m_mom_practica').value = sesion.mom_practica;
    document.getElementById('m_mom_cierre').value = sesion.mom_cierre;
    
    document.getElementById('modalTituloTexto').innerHTML = '<i class="fa-solid fa-pencil text-warning"></i> Editar Sesión N° ' + sesion.numses;
    
    let modal = new bootstrap.Modal(document.getElementById('modalCrearSesion'));
    modal.show();
}

function guardarSesionAjax(event) {
    event.preventDefault();
    const formData = new FormData(document.getElementById('frmSesionPlaneacion'));
    formData.append('action', 'guardar_sesion');
    
    fetch(CONTROLLER_URL, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status) {
            Swal.fire({
                icon: 'success',
                title: 'Éxito',
                text: data.msg,
                timer: 2000,
                showConfirmButton: false
            }).then(() => {
                location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.error
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Ocurrió un error en la solicitud.'
        });
    });
}

function eliminarSesion(idses) {
    Swal.fire({
        title: '¿Está seguro?',
        text: "Se eliminará esta sesión y sus evaluaciones. Esta acción no se puede deshacer.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('action', 'eliminar_sesion');
            formData.append('idses', idses);
            
            fetch(CONTROLLER_URL, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Eliminada',
                        text: 'La sesión ha sido eliminada.',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.error || 'No se pudo eliminar la sesión.'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }
    });
}
</script>
