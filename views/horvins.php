<?php
/**
 * VISTA: horvins.php
 * Responsabilidad: Exclusivamente presentación visual del Módulo de Instrumentos
 * Arquitectura: Vista en patrón MVC (Consume datos del Controlador horcins.php)
 */

if (!defined('HORVINS_LOADED_FROM_VIEW')) {
    define('HORVINS_LOADED_FROM_VIEW', true);
}
require_once __DIR__ . '/../controllers/horcins.php';

$icono = $icono ?? 'fa-solid fa-screwdriver-wrench';
?>

<!-- ENCABEZADO Y DASHBOARD UNIFICADO SENA -->
<div class="container-fluid px-0 mb-4">
    <!-- BARRA DE NAVEGACIÓN SUPERIOR DEL DASHBOARD INSTITUCIONAL -->
    <div class="card shadow-sm border-0 mb-3 bg-white">
        <div class="card-body p-3 d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-success text-white p-2 rounded-3 text-center" style="width: 44px; height: 44px;">
                    <i class="fa-solid fa-graduation-cap fs-5"></i>
                </div>
                <div>
                    <h5 class="mb-0 fw-bold text-dark">SAGISENA - Suite Académica</h5>
                    <small class="text-muted">Centro de Biotecnología Agropecuaria • Regional Cundinamarca</small>
                </div>
            </div>
            
            <!-- NAVEGACIÓN INTERMODULAR ENTRE INSTRUMENTOS Y EVALUACIONES -->
            <div class="btn-group shadow-sm" role="group" aria-label="Navegación de módulos">
                <a href="home.php?pg=1545" class="btn btn-success fw-semibold d-flex align-items-center gap-2 active">
                    <i class="fa-solid fa-list-check"></i>
                    <span>1. Instrumentos</span>
                </a>
                <a href="home.php?pg=2117" class="btn btn-outline-success fw-semibold d-flex align-items-center gap-2">
                    <i class="fa-solid fa-gavel"></i>
                    <span>2. Evaluaciones</span>
                </a>
            </div>
        </div>
    </div>

    <!-- BREADCRUMBS & CONTEXTO ACADÉMICO -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small">
                    <li class="breadcrumb-item"><a href="home.php" class="text-decoration-none text-muted">Inicio</a></li>
                    <li class="breadcrumb-item"><a href="#" class="text-decoration-none text-muted">Gestión Formación</a></li>
                    <li class="breadcrumb-item active text-success fw-bold" aria-current="page">Instrumentos de Evaluación</li>
                </ol>
            </nav>
            <h2 class="h3 fw-bold text-dark mb-0">
                <i class="<?= $icono ?> text-success me-2"></i>Catálogo de Instrumentos de Evaluación
            </h2>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-secondary-subtle text-secondary px-3 py-2 border rounded-pill">
                <i class="fa-solid fa-id-card me-1"></i> Ficha: <?= htmlspecialchars($idfic ?? 'Sin selección') ?> - <?= htmlspecialchars($nomfic ?? '') ?>
            </span>
        </div>
    </div>
    <?php require_once("views/vscopehya.php"); ?>

    <!-- NOTIFICACIONES PROVENIENTES DEL CONTROLADOR (MVC FEEDBACK) -->
    <?php if (!empty($mensajeExito)): ?>
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 shadow-sm" role="alert">
            <i class="fa-solid fa-circle-check fs-5"></i>
            <div><strong>Éxito:</strong> <?= htmlspecialchars($mensajeExito) ?></div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 shadow-sm" role="alert">
            <i class="fa-solid fa-triangle-exclamation fs-5"></i>
            <div><strong>Atención:</strong> <?= htmlspecialchars($error_message) ?></div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    <?php endif; ?>

    <!-- TARJETAS DE MÉTRICAS (KPIs) PARA INSTRUMENTOS -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card shadow-sm border-0 border-start border-4 border-success p-3 text-center bg-white">
                <div class="rounded-circle bg-success-subtle text-success mx-auto d-flex align-items-center justify-content-center mb-2" style="width: 44px; height: 44px; font-size: 18px;">
                    <i class="fa-solid fa-clipboard-list"></i>
                </div>
                <div class="text-uppercase fw-bold text-muted" style="font-size: 11px;">INSTRUMENTOS ACTIVOS</div>
                <div class="fw-bold fs-3 text-dark"><?= !empty($dat) ? count($dat) : 3 ?></div>
                <small class="text-muted">En el banco institucional</small>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card shadow-sm border-0 border-start border-4 border-primary p-3 text-center bg-white">
                <div class="rounded-circle bg-primary-subtle text-primary mx-auto d-flex align-items-center justify-content-center mb-2" style="width: 44px; height: 44px; font-size: 18px;">
                    <i class="fa-solid fa-file-excel"></i>
                </div>
                <div class="text-uppercase fw-bold text-muted" style="font-size: 11px;">MATRICES EXCEL</div>
                <div class="fw-bold fs-3 text-dark">100%</div>
                <small class="text-muted">Ponderación integrada</small>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card shadow-sm border-0 border-start border-4 border-warning p-3 text-center bg-white">
                <div class="rounded-circle bg-warning-subtle text-warning mx-auto d-flex align-items-center justify-content-center mb-2" style="width: 44px; height: 44px; font-size: 18px;">
                    <i class="fa-solid fa-check-double"></i>
                </div>
                <div class="text-uppercase fw-bold text-muted" style="font-size: 11px;">CRITERIOS DE CALIDAD</div>
                <div class="fw-bold fs-3 text-dark">28</div>
                <small class="text-muted">Criterios de evaluación</small>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card shadow-sm border-0 border-start border-4 border-info p-3 text-center bg-white">
                <div class="rounded-circle bg-info-subtle text-info mx-auto d-flex align-items-center justify-content-center mb-2" style="width: 44px; height: 44px; font-size: 18px;">
                    <i class="fa-solid fa-graduation-cap"></i>
                </div>
                <div class="text-uppercase fw-bold text-muted" style="font-size: 11px;">FICHAS ADSO</div>
                <div class="fw-bold fs-3 text-dark">3235287</div>
                <small class="text-muted">CDA Chía - Presencial</small>
            </div>
        </div>
    </div>

    <!-- TARJETA FORMULARIO: REGISTRO / EDICIÓN DE INSTRUMENTO -->
    <!-- DECISIÓN ARQUITECTÓNICA: Formulario HTML desacoplado que envía por POST al endpoint del Controlador -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
            <h6 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                <i class="fa-solid fa-pen-to-square text-success"></i>
                <?= isset($dtOne['idins']) ? 'Editar Instrumento Registrado' : 'Registrar Nuevo Instrumento de Evaluación' ?>
            </h6>
            <span class="badge bg-light text-muted border">Acción: POST ope=save</span>
        </div>
        <div class="card-body p-4">
            <form id="form-instrumento" action="home.php?pg=<?= htmlspecialchars($pg) ?>" method="POST" class="needs-validation" novalidate>
                <input type="hidden" name="ope" value="save">
                <input type="hidden" name="idins" value="<?= isset($dtOne['idins']) ? htmlspecialchars($dtOne['idins']) : '' ?>">

                <div class="row g-3">
                    <!-- Nombre del Instrumento -->
                    <div class="col-md-5">
                        <label for="nomins" class="form-label fw-semibold text-secondary">
                            Nombre del Instrumento <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" name="nomins" id="nomins"
                            placeholder="Ej: Lista de Chequeo - Modelado Base de Datos"
                            value="<?= isset($dtOne['nomins']) ? htmlspecialchars($dtOne['nomins']) : '' ?>" 
                            required minlength="4" maxlength="150">
                        <div class="invalid-feedback">
                            Por favor ingrese un nombre descriptivo válido (mínimo 4 caracteres).
                        </div>
                    </div>

                    <!-- Selección de Ficha -->
                    <div class="col-md-3">
                        <label for="idfic" class="form-label fw-semibold text-secondary">
                            Ficha de Formación <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" name="idfic" id="idfic" required>
                            <option value="" disabled <?= empty($idfic) ? 'selected' : '' ?>>Seleccione Ficha...</option>
                            <?php if (!empty($listFichas)): ?>
                                <?php foreach ($listFichas as $f): ?>
                                    <option value="<?= $f['idfic'] ?>" <?= ($idfic == $f['idfic'] || (isset($dtOne['idfic']) && $dtOne['idfic'] == $f['idfic'])) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($f['idfic']) ?> - <?= htmlspecialchars($f['nomfic']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="<?= htmlspecialchars($idfic ?? '2694123') ?>" selected>
                                    Ficha <?= htmlspecialchars($idfic ?? '2694123') ?>
                                </option>
                            <?php endif; ?>
                        </select>
                        <div class="invalid-feedback">Seleccione la ficha académica.</div>
                    </div>

                    <!-- Resultado de Aprendizaje (RAP) -->
                    <div class="col-md-4">
                        <label for="idres" class="form-label fw-semibold text-secondary">
                            Resultado de Aprendizaje (RAP) <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" name="idres" id="idres" required>
                            <option value="" disabled <?= empty($idres) ? 'selected' : '' ?>>Seleccione Resultado...</option>
                            <?php if (!empty($listResultados)): ?>
                                <?php foreach ($listResultados as $r): ?>
                                    <option value="<?= $r['idres'] ?>" <?= ($idres == $r['idres'] || (isset($dtOne['idres']) && $dtOne['idres'] == $r['idres'])) ? 'selected' : '' ?>>
                                        RAP <?= htmlspecialchars($r['idres']) ?>: <?= htmlspecialchars(substr($r['nomres'], 0, 45)) ?>...
                                    </option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="<?= htmlspecialchars($idres ?? '1') ?>" selected>
                                    RAP <?= htmlspecialchars($idres ?? '1') ?>
                                </option>
                            <?php endif; ?>
                        </select>
                        <div class="invalid-feedback">Debe asociar un Resultado de Aprendizaje.</div>
                    </div>
                </div>

                <!-- Botones de Acción del Formulario -->
                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                    <?php if (isset($dtOne['idins'])): ?>
                        <a href="home.php?pg=<?= htmlspecialchars($pg) ?>&idfic=<?= htmlspecialchars($idfic ?? '') ?>" class="btn btn-outline-secondary">
                            <i class="fa-solid fa-xmark me-1"></i> Cancelar Edición
                        </a>
                    <?php else: ?>
                        <button type="reset" class="btn btn-outline-secondary">
                            <i class="fa-solid fa-rotate-left me-1"></i> Limpiar
                        </button>
                    <?php endif; ?>
                    <button type="submit" class="btn btn-success px-4 fw-semibold shadow-sm">
                        <i class="fa-solid fa-floppy-disk me-1"></i> <?= isset($dtOne['idins']) ? 'Actualizar Instrumento' : 'Guardar Instrumento' ?>
                    </button>
                </div>
            </form>

            <!-- SECCIÓN OPCIONAL: IMPORTACIÓN DE MATRIZ DE CRITERIOS EN EXCEL -->
            <div class="mt-4 pt-3 border-top">
                <a class="btn btn-sm btn-outline-success" data-bs-toggle="collapse" href="#collapseExcel" role="button" aria-expanded="false" aria-controls="collapseExcel">
                    <i class="fa-solid fa-file-excel me-1"></i> Opcional: Importar Criterios desde Archivo Excel (.xlsx)
                </a>
                <div class="collapse mt-3" id="collapseExcel">
                    <form action="home.php?pg=<?= htmlspecialchars($pg) ?>&idres=<?= htmlspecialchars($idres ?? '') ?>&idfic=<?= htmlspecialchars($idfic ?? '') ?>" method="POST" enctype="multipart/form-data" class="bg-light p-3 rounded border">
                        <div class="row align-items-center g-3">
                            <div class="col-md-9">
                                <label for="EXCEL" class="form-label fw-semibold small text-muted">Seleccione archivo Excel (.xls, .xlsx) con matriz de ponderación:</label>
                                <input type="file" class="form-control" name="EXCEL" id="EXCEL" accept=".xls,.xlsx" required>
                            </div>
                            <div class="col-md-3 d-grid">
                                <label class="form-label small text-transparent d-none d-md-block">Acción</label>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa-solid fa-upload me-1"></i> Subir Matriz
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- TABLA LISTADO DE INSTRUMENTOS (RENDERIZADO DESDE EL MODELO) -->
    <!-- DECISIÓN ARQUITECTÓNICA: Colección recibida desde horcins.php sin lógica SQL embebida en la Vista -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
            <h6 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                <i class="fa-solid fa-table-list text-success"></i> Instrumentos Registrados
            </h6>
            <span class="badge bg-secondary-subtle text-secondary">
                Total: <?= !empty($dat) ? count($dat) : 0 ?> registros
            </span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" class="ps-3">ID</th>
                            <th scope="col">Nombre del Instrumento</th>
                            <th scope="col">Ficha Asociada</th>
                            <th scope="col">Resultado de Aprendizaje (RAP)</th>
                            <th scope="col" class="text-center">Criterios</th>
                            <th scope="col" class="text-end pe-3">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($dat)): ?>
                            <?php foreach ($dat as $dta): ?>
                                <tr>
                                    <td class="ps-3 fw-bold text-muted"><?= htmlspecialchars($dta['idins']) ?></td>
                                    <td>
                                        <span class="fw-semibold text-dark d-block"><?= htmlspecialchars($dta['nomins']) ?></span>
                                        <small class="text-muted">Instrumento de evaluación institucional</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            <?= htmlspecialchars($dta['idfic']) ?> <?= !empty($dta['nomfic']) ? '• ' . htmlspecialchars($dta['nomfic']) : '' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-secondary fw-semibold">
                                            RAP <?= htmlspecialchars($dta['idres']) ?>
                                            <?= !empty($dta['nomres']) ? '• ' . htmlspecialchars(substr($dta['nomres'], 0, 35)) . '...' : '' ?>
                                        </small>
                                    </td>
                                    <td class="text-center">
                                        <a href="home.php?pg=1546&idins=<?= htmlspecialchars($dta['idins']) ?>&idfic=<?= htmlspecialchars($idfic ?? $dta['idfic']) ?>" 
                                           class="btn btn-sm btn-outline-info" title="Configurar Criterios">
                                            <i class="fa-solid fa-list-check me-1"></i> Criterios
                                        </a>
                                    </td>
                                    <td class="text-end pe-3">
                                        <div class="btn-group btn-group-sm" role="group" aria-label="Acciones">
                                            <!-- Acción Editar: Envía GET al Controlador -->
                                            <a href="home.php?pg=<?= htmlspecialchars($pg) ?>&idins=<?= htmlspecialchars($dta['idins']) ?>&idres=<?= htmlspecialchars($dta['idres']) ?>&idfic=<?= htmlspecialchars($dta['idfic']) ?>&ope=edi" 
                                               class="btn btn-outline-primary" title="Editar Instrumento">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </a>
                                            <!-- Acción Eliminar: Modal de Confirmación Segura -->
                                            <button type="button" class="btn btn-outline-danger" title="Eliminar Instrumento"
                                                    data-bs-toggle="modal" data-bs-target="#modalEliminarIns"
                                                    data-id="<?= htmlspecialchars($dta['idins']) ?>"
                                                    data-nombre="<?= htmlspecialchars($dta['nomins']) ?>"
                                                    data-fic="<?= htmlspecialchars($dta['idfic']) ?>">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    <i class="fa-solid fa-folder-open fa-2x mb-2 d-block text-secondary"></i>
                                    No se encontraron instrumentos registrados para el criterio seleccionado.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- MODAL DE CONFIRMACIÓN DE ELIMINACIÓN SEGURA -->
<!-- DECISIÓN ARQUITECTÓNICA: Protege contra borrados accidentales canalizando la eliminación mediante método POST/GET hacia el controlador -->
<div class="modal fade" id="modalEliminarIns" tabindex="-1" aria-labelledby="modalEliminarInsLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="home.php?pg=<?= htmlspecialchars($pg) ?>" method="GET" id="form-eliminar-ins">
                <input type="hidden" name="pg" value="<?= htmlspecialchars($pg) ?>">
                <input type="hidden" name="ope" value="del">
                <input type="hidden" name="idins" id="modal-ins-id" value="">
                <input type="hidden" name="idfic" id="modal-ins-fic" value="<?= htmlspecialchars($idfic ?? '') ?>">
                
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="modalEliminarInsLabel">
                        <i class="fa-solid fa-triangle-exclamation me-2"></i> Confirmar Eliminación
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2">¿Está seguro de que desea eliminar el siguiente instrumento de evaluación?</p>
                    <p class="fw-bold text-danger mb-2" id="modal-ins-nombre">Instrumento</p>
                    <small class="text-muted">
                        Esta acción invocará el método del Modelo que elimina en cascada los criterios asociados al instrumento.
                    </small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger d-flex align-items-center gap-1">
                        <i class="fa-solid fa-trash-can"></i> Confirmar y Eliminar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Validación declarativa frontend estándar de Bootstrap 5
(() => {
    'use strict';
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });

    // Enlace dinámico de datos al modal de eliminación
    const modalEliminar = document.getElementById('modalEliminarIns');
    if (modalEliminar) {
        modalEliminar.addEventListener('show.bs.modal', event => {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-id');
            const nombre = button.getAttribute('data-nombre');
            const fic = button.getAttribute('data-fic');
            document.getElementById('modal-ins-id').value = id;
            document.getElementById('modal-ins-fic').value = fic || '';
            document.getElementById('modal-ins-nombre').textContent = nombre + ' (ID: ' + id + ')';
        });
    }
})();
</script>