<?php
/**
 * Vista de Criterios de Evaluación.
 * Consume exclusivamente los datos preparados por controllers/horccrev.php.
 */

require_once __DIR__ . '/../controllers/horccrev.php';

$escapar = static function ($valor) {
    return htmlspecialchars((string)$valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
};

$modoEdicion = $editar !== null;
$idinsSeleccionado = $modoEdicion ? (string)$editar['idins'] : (string)$formIdins;
$idficSeleccionada = $modoEdicion ? (string)($editar['idfic'] ?? '') : (string)$formIdfic;
$instrumentoSeleccionado = null;
$pesoTotalInstrumento = 0;

foreach ($instrumentos as $instrumento) {
    if ((string)$instrumento['idins'] === $idinsSeleccionado) {
        $instrumentoSeleccionado = $instrumento;
        $pesoTotalInstrumento = (int)$instrumento['peso_total'];
        break;
    }
}

$pesoAlEditar = $modoEdicion ? (int)$editar['vscum'] : 0;
$pesoBaseFormulario = $modoEdicion
    ? max(0, $pesoTotalInstrumento - $pesoAlEditar)
    : $pesoTotalInstrumento;
?>

<style>
    .criterio-card {
        border: 0;
        border-radius: 12px;
    }

    .criterio-kpi {
        min-height: 110px;
        border: 1px solid #e9ecef;
        border-radius: 12px;
        background: #fff;
    }

    .criterio-kpi .icono {
        width: 48px;
        height: 48px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        font-size: 1.25rem;
    }

    .badge-cumple {
        color: #0f5132;
        background: #d1e7dd;
        border: 1px solid #badbcc;
    }

    .badge-parcial {
        color: #664d03;
        background: #fff3cd;
        border: 1px solid #ffecb5;
    }

    .badge-no-cumple {
        color: #842029;
        background: #f8d7da;
        border: 1px solid #f5c2c7;
    }

    .fila-criterio {
        border-left: 4px solid #00af00;
        background-color: #f8f9fa;
    }

    #totalVscumFormulario {
        min-width: 3.5rem;
    }
</style>

<div class="container-fluid px-3 py-2">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h2 class="h4 mb-1 text-dark">
                <i class="fa-solid fa-list-check text-success me-2"></i>
                Criterios de Evaluación
            </h2>
            <small class="text-muted">
                Catálogo persistente de criterios, con actualización y eliminación por su idcri real.
            </small>
        </div>
        <?php if ($modoEdicion): ?>
            <a class="btn btn-outline-secondary" href="home.php?pg=<?= $escapar($pg) ?>">
                <i class="fa-solid fa-arrow-left me-1"></i> Volver a todos los criterios
            </a>
        <?php endif; ?>
    </div>

    <?php if ($mensaje !== null && $mensaje !== ''): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i><?= $escapar($mensaje) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    <?php endif; ?>

    <?php if ($error !== null && $error !== ''): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <i class="fa-solid fa-triangle-exclamation me-2"></i><?= $escapar($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    <?php endif; ?>

    <?php if (!$puedeGestionar): ?>
        <div class="alert alert-warning">
            Su perfil puede consultar el módulo, pero no tiene permiso para crear, modificar o eliminar criterios.
        </div>
    <?php endif; ?>

    <?php if ($modoEdicion): ?>
        <div class="alert alert-info d-flex justify-content-between align-items-center gap-3">
            <div>
                <i class="fa-solid fa-eye me-2"></i>
                Se seleccionó el criterio <strong>#<?= $escapar($editar['idcri']) ?></strong>.
                El formulario y la tabla muestran únicamente su información.
            </div>
        </div>
    <?php endif; ?>

    <?php if (!$modoEdicion): ?>
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-xl-3">
                <div class="criterio-kpi p-3 d-flex align-items-center gap-3">
                    <span class="icono bg-success-subtle text-success"><i class="fa-solid fa-clipboard-list"></i></span>
                    <div>
                        <div class="small text-muted fw-semibold">CRITERIOS EN SISTEMA</div>
                        <div class="fs-3 fw-bold text-dark"><?= (int)$dashboard['total_criterios'] ?></div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="criterio-kpi p-3 d-flex align-items-center gap-3">
                    <span class="icono bg-primary-subtle text-primary"><i class="fa-solid fa-screwdriver-wrench"></i></span>
                    <div>
                        <div class="small text-muted fw-semibold">INSTRUMENTOS VINCULADOS</div>
                        <div class="fs-3 fw-bold text-dark"><?= (int)$dashboard['instrumentos_con_criterios'] ?></div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="criterio-kpi p-3 d-flex align-items-center gap-3">
                    <span class="icono bg-warning-subtle text-warning"><i class="fa-solid fa-graduation-cap"></i></span>
                    <div>
                        <div class="small text-muted fw-semibold">FICHAS CON CRITERIOS</div>
                        <div class="fs-3 fw-bold text-dark"><?= (int)$dashboard['fichas_con_criterios'] ?></div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="criterio-kpi p-3 d-flex align-items-center gap-3">
                    <span class="icono bg-info-subtle text-info"><i class="fa-solid fa-check-double"></i></span>
                    <div>
                        <div class="small text-muted fw-semibold">CALIFICACIONES ASOCIADAS</div>
                        <div class="fs-3 fw-bold text-dark"><?= (int)$dashboard['calificaciones_asociadas'] ?></div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($puedeGestionar): ?>
        <div class="card criterio-card shadow-sm mb-4">
            <div class="card-header bg-white border-bottom">
                <h5 class="mb-0 text-dark">
                    <i class="fa-solid <?= $modoEdicion ? 'fa-pen-to-square text-warning' : 'fa-circle-plus text-success' ?> me-2"></i>
                    <?= $modoEdicion
                        ? 'Modificar criterio #' . $escapar($editar['idcri'])
                        : 'Registrar criterio(s) de evaluación' ?>
                </h5>
            </div>
            <div class="card-body p-4">
                <?php if (empty($instrumentos)): ?>
                    <div class="alert alert-warning mb-0">
                        No hay instrumentos registrados. Primero cree o seleccione un instrumento en el módulo de Instrumentos.
                    </div>
                <?php else: ?>
                    <form method="post"
                          action="home.php?pg=<?= $escapar($pg) ?>"
                          id="formCriterio"
                          data-modo-edicion="<?= $modoEdicion ? '1' : '0' ?>"
                          data-vscum-editado="<?= $pesoAlEditar ?>">
                        <input type="hidden" name="ope" value="save_criterio">
                        <input type="hidden" name="csrf_token" value="<?= $escapar($csrfToken) ?>">
                        <?php if ($modoEdicion): ?>
                            <input type="hidden" name="idcri" value="<?= (int)$editar['idcri'] ?>">
                        <?php else: ?>
                            <input type="hidden" name="create_guard" id="createGuard" value="<?= $escapar($formGuard) ?>">
                        <?php endif; ?>

                        <div class="row g-3 mb-3">
                            <div class="col-md-5">
                                <label for="idfic" class="form-label fw-semibold">Ficha de formación</label>
                                <select name="idfic" id="idfic" class="form-select">
                                    <option value="">-- Todas las fichas / General --</option>
                                    <?php foreach ($fichas as $ficha): ?>
                                        <option value="<?= $escapar($ficha['idfic']) ?>"
                                            <?= $idficSeleccionada === (string)$ficha['idfic'] ? 'selected' : '' ?>>
                                            <?= $escapar($ficha['idfic']) ?> - <?= $escapar($ficha['nomfic']) ?>
                                            (<?= (int)($ficha['total_instrumentos'] ?? 0) ?> instrumento(s))
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text">
                                    La ficha no se agrega a <code>criterio</code>; se obtiene mediante
                                    <code>criterio.idins → inseva.idfic</code>.
                                </div>
                            </div>

                            <div class="col-md-7">
                                <label for="idins" class="form-label fw-semibold">
                                    Instrumento de evaluación <span class="text-danger">*</span>
                                </label>
                                <select name="idins" id="idins" class="form-select" required
                                        <?= $modoEdicion ? 'disabled' : '' ?>>
                                    <option value="">-- Seleccione un instrumento --</option>
                                    <?php foreach ($instrumentos as $instrumento): ?>
                                        <option value="<?= $escapar($instrumento['idins']) ?>"
                                                data-idfic="<?= $escapar($instrumento['idfic'] ?? '') ?>"
                                                data-peso-total="<?= (int)$instrumento['peso_total'] ?>"
                                                <?= $idinsSeleccionado === (string)$instrumento['idins'] ? 'selected' : '' ?>>
                                            #<?= $escapar($instrumento['idins']) ?> -
                                            <?= $escapar($instrumento['nomins']) ?>
                                            (<?= $escapar($instrumento['idfic'] ?? 'Sin ficha') ?>,
                                            peso actual: <?= (int)$instrumento['peso_total'] ?>/100)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text">
                                    Este valor corresponde a <code>inseva.idins</code>. El usuario instructor que está
                                    conectado se obtiene de la sesión y se guarda en <code>criterio.idusu</code> (#<?= (int)$idusuActual ?>).
                                </div>
                                <div class="alert alert-warning py-2 px-3 mt-2 mb-0 small" id="avisoSinInstrumentos" style="display:none;">
                                    <i class="fa-solid fa-triangle-exclamation me-1"></i>
                                    La ficha seleccionada no tiene instrumentos. Elija "Todas las fichas" o registre el instrumento en el módulo de Instrumentos.
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-light border small mb-3">
                            La suma de <strong>Si cumple (vscum)</strong> del instrumento debe quedar en
                            <strong>100</strong>. Puede registrar varios criterios en un solo envío; la operación es transaccional.
                        </div>

                        <div id="contenedorCriterios" class="d-grid gap-3">
                            <?php foreach ($formRows as $indiceFila => $fila): ?>
                                <div class="fila-criterio border rounded p-3">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <strong class="text-dark">
                                            Criterio <?= (int)$indiceFila + 1 ?>
                                        </strong>
                                        <?php if (!$modoEdicion && count($formRows) > 1): ?>
                                            <button type="button" class="btn btn-sm btn-outline-danger quitar-fila">
                                                <i class="fa-solid fa-trash-can me-1"></i> Quitar
                                            </button>
                                        <?php endif; ?>
                                    </div>

                                    <div class="row g-3 align-items-end">
                                        <div class="col-md-5">
                                            <label class="form-label fw-semibold">
                                                Nombre del criterio <span class="text-danger">*</span>
                                            </label>
                                            <input type="text"
                                                   name="nomcri[]"
                                                   class="form-control"
                                                   maxlength="255"
                                                   required
                                                   placeholder="Ej: Criterio de conocimientos"
                                                   value="<?= $escapar($fila['nomcri'] ?? '') ?>">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label fw-semibold text-success">Si cumple</label>
                                            <input type="number"
                                                   name="vscum[]"
                                                   class="form-control text-center fw-bold"
                                                   min="0"
                                                   max="100"
                                                   step="1"
                                                   required
                                                   value="<?= $escapar($fila['vscum'] ?? '100') ?>">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label fw-semibold text-warning">Parcial</label>
                                            <input type="number"
                                                   name="vpar[]"
                                                   class="form-control text-center fw-bold"
                                                   min="0"
                                                   max="100"
                                                   step="1"
                                                   required
                                                   value="<?= $escapar($fila['vpar'] ?? '50') ?>">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label fw-semibold text-danger">No cumple</label>
                                            <input type="number"
                                                   name="vncum[]"
                                                   class="form-control text-center fw-bold"
                                                   min="0"
                                                   max="100"
                                                   step="1"
                                                   required
                                                   value="<?= $escapar($fila['vncum'] ?? '0') ?>">
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3">
                            <div>
                                <?php if (!$modoEdicion): ?>
                                    <button type="button" id="agregarCriterio" class="btn btn-outline-success">
                                        <i class="fa-solid fa-plus me-1"></i> Agregar otro criterio
                                    </button>
                                <?php else: ?>
                                    <a href="home.php?pg=<?= $escapar($pg) ?>" class="btn btn-outline-secondary">
                                        <i class="fa-solid fa-xmark me-1"></i> Cancelar edición
                                    </a>
                                <?php endif; ?>
                            </div>

                            <div class="d-flex align-items-center gap-3">
                                <span class="text-muted">
                                    Suma Si cumple:
                                    <strong id="totalVscumFormulario" class="text-danger"><?= (int)$pesoBaseFormulario ?></strong>
                                    / 100
                                </span>
                                <button type="submit" class="btn <?= $modoEdicion ? 'btn-warning' : 'btn-success' ?> fw-semibold">
                                    <i class="fa-solid <?= $modoEdicion ? 'fa-floppy-disk' : 'fa-paper-plane' ?> me-1"></i>
                                    <?= $modoEdicion ? 'Actualizar criterio' : 'Enviar y guardar' ?>
                                </button>
                            </div>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!$modoEdicion): ?>
        <div class="card shadow-sm mb-3">
            <div class="card-body py-2">
                <form method="get" action="home.php" class="row g-2 align-items-center">
                    <input type="hidden" name="pg" value="<?= $escapar($pg) ?>">

                    <div class="col-md-4">
                        <select name="filtro_idfic" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">-- Todas las fichas --</option>
                            <?php foreach ($fichas as $ficha): ?>
                                <option value="<?= $escapar($ficha['idfic']) ?>"
                                    <?= $filtroIdfic === (string)$ficha['idfic'] ? 'selected' : '' ?>>
                                    <?= $escapar($ficha['idfic']) ?> - <?= $escapar($ficha['nomfic']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <select name="filtro_idins" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">-- Todos los instrumentos --</option>
                            <?php foreach ($instrumentos as $instrumento): ?>
                                <option value="<?= $escapar($instrumento['idins']) ?>"
                                    <?= $filtroIdins === (string)$instrumento['idins'] ? 'selected' : '' ?>>
                                    #<?= $escapar($instrumento['idins']) ?> - <?= $escapar($instrumento['nomins']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-4 d-flex gap-2">
                        <button type="submit" class="btn btn-sm btn-outline-success w-50">
                            <i class="fa-solid fa-filter me-1"></i> Filtrar
                        </button>
                        <a href="home.php?pg=<?= $escapar($pg) ?>" class="btn btn-sm btn-outline-secondary w-50">
                            <i class="fa-solid fa-arrows-rotate me-1"></i> Ver todos
                        </a>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
            <div>
                <i class="fa-solid fa-table-list me-2"></i>
                <strong><?= $modoEdicion ? 'Información del criterio seleccionado' : 'Criterios registrados' ?></strong>
            </div>
            <span class="badge bg-light text-dark">
                <?= count($criterios) ?> <?= count($criterios) === 1 ? 'registro' : 'registros' ?>
            </span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="tablaMaestroCriterios" class="table table-striped table-hover table-bordered align-middle w-100">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 70px;">ID</th>
                            <th>Criterio</th>
                            <th>Instrumento</th>
                            <th>Ficha</th>
                            <th class="text-center">Cumple</th>
                            <th class="text-center">Parcial</th>
                            <th class="text-center">No cumple</th>
                            <th>Registrado por</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($criterios)): ?>
                            <?php foreach ($criterios as $criterio): ?>
                                <tr>
                                    <td class="text-center fw-bold">
                                        <code>#<?= (int)$criterio['idcri'] ?></code>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark"><?= $escapar($criterio['nomcri']) ?></div>
                                        <small class="text-muted">
                                            <?= $escapar($criterio['nomres'] ?? 'Sin resultado de aprendizaje') ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?= $escapar($criterio['nomins'] ?? ('Instrumento #' . ($criterio['idins'] ?? ''))) ?>
                                        <small class="text-muted d-block">
                                            idins: <?= (int)$criterio['idins'] ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?php if (($criterio['idfic'] ?? '') !== ''): ?>
                                            <span class="badge bg-secondary mb-1">
                                                Ficha: <?= $escapar($criterio['idfic']) ?>
                                            </span>
                                            <small class="d-block text-muted"><?= $escapar($criterio['nomfic']) ?></small>
                                        <?php else: ?>
                                            <span class="text-muted fst-italic">Sin ficha</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-cumple"><?= (int)$criterio['vscum'] ?>%</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-parcial"><?= (int)$criterio['vpar'] ?>%</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-no-cumple"><?= (int)$criterio['vncum'] ?>%</span>
                                    </td>
                                    <td>
                                        <?= $escapar($criterio['nomusu_registro'] ?? 'Usuario no identificado') ?>
                                        <small class="text-muted d-block">idusu: <?= (int)$criterio['idusu'] ?></small>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm" role="group" aria-label="Acciones del criterio">
                                            <a href="home.php?pg=<?= $escapar($pg) ?>&ope=edi_criterio&idcri=<?= (int)$criterio['idcri'] ?>"
                                               class="btn btn-outline-warning"
                                               title="Seleccionar y modificar">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </a>

                                            <?php if ($puedeGestionar): ?>
                                                <form method="post"
                                                      action="home.php?pg=<?= $escapar($pg) ?>"
                                                      class="d-inline"
                                                      onsubmit="return confirm('¿Está seguro de eliminar el criterio #<?= (int)$criterio['idcri'] ?>? Esta acción no se puede deshacer.');">
                                                    <input type="hidden" name="ope" value="del_criterio">
                                                    <input type="hidden" name="csrf_token" value="<?= $escapar($csrfToken) ?>">
                                                    <input type="hidden" name="idcri" value="<?= (int)$criterio['idcri'] ?>">
                                                    <input type="hidden" name="filtro_idins" value="<?= $escapar($filtroIdins) ?>">
                                                    <input type="hidden" name="filtro_idfic" value="<?= $escapar($filtroIdfic) ?>">
                                                    <button type="submit" class="btn btn-outline-danger" title="Eliminar criterio">
                                                        <i class="fa-solid fa-trash-can"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center py-4 text-muted">
                                    <i class="fa-solid fa-clipboard-question fa-2x mb-2 d-block text-secondary"></i>
                                    No hay criterios registrados para el contexto seleccionado.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    'use strict';

    const formulario = document.getElementById('formCriterio');
    const contenedor = document.getElementById('contenedorCriterios');
    const agregar = document.getElementById('agregarCriterio');
    const ficha = document.getElementById('idfic');
    const instrumento = document.getElementById('idins');
    const indicadorTotal = document.getElementById('totalVscumFormulario');

    function actualizarTotal() {
        if (!formulario || !indicadorTotal) return;

        const opcion = instrumento && instrumento.selectedIndex >= 0
            ? instrumento.options[instrumento.selectedIndex]
            : null;
        let base = opcion ? parseInt(opcion.dataset.pesoTotal || '0', 10) : 0;

        if (formulario.dataset.modoEdicion === '1') {
            base -= parseInt(formulario.dataset.vscumEditado || '0', 10);
        }
        base = Math.max(0, base);

        let enviado = 0;
        contenedor.querySelectorAll('input[name="vscum[]"]').forEach(input => {
            enviado += Number(input.value || 0);
        });

        const total = base + enviado;
        indicadorTotal.textContent = String(total);
        indicadorTotal.classList.toggle('text-success', total === 100);
        indicadorTotal.classList.toggle('text-danger', total !== 100);
    }

    function conectarControlesFila(fila) {
        const quitar = fila.querySelector('.quitar-fila');
        if (quitar) {
            quitar.addEventListener('click', () => {
                const filas = contenedor.querySelectorAll('.fila-criterio');
                if (filas.length > 1) {
                    fila.remove();
                    actualizarTotal();
                }
            });
        }
        fila.querySelectorAll('input[name="vscum[]"]').forEach(input => {
            input.addEventListener('input', actualizarTotal);
        });
    }

    function filtrarInstrumentosPorFicha() {
        if (!ficha || !instrumento || formulario.dataset.modoEdicion === '1') return;

        let primeroDisponible = '';
        let visibles = 0;
        Array.from(instrumento.options).forEach(opcion => {
            if (!opcion.value) return;
            const coincide = ficha.value === '' || opcion.dataset.idfic === ficha.value;
            opcion.disabled = !coincide;
            if (coincide) {
                visibles++;
                if (!primeroDisponible) primeroDisponible = opcion.value;
            }
        });

        const seleccionada = instrumento.selectedIndex >= 0
            ? instrumento.options[instrumento.selectedIndex]
            : null;
        if (!seleccionada || seleccionada.disabled || !seleccionada.value) {
            instrumento.value = primeroDisponible;
        }
        const aviso = document.getElementById('avisoSinInstrumentos');
        if (aviso) aviso.style.display = (visibles === 0) ? '' : 'none';
        actualizarTotal();
    }

    if (formulario && contenedor) {
        contenedor.querySelectorAll('.fila-criterio').forEach(conectarControlesFila);

        if (agregar) {
            agregar.addEventListener('click', () => {
                const filas = contenedor.querySelectorAll('.fila-criterio');
                const origen = filas[filas.length - 1];
                const nuevaFila = origen.cloneNode(true);

                nuevaFila.querySelectorAll('input').forEach(input => {
                    if (input.name === 'nomcri[]' || input.name === 'vscum[]' || input.name === 'vpar[]') {
                        input.value = '';
                    } else if (input.name === 'vncum[]') {
                        input.value = '0';
                    }
                });

                const tituloFila = nuevaFila.querySelector('strong');
                if (tituloFila) tituloFila.textContent = 'Criterio ' + (filas.length + 1);

                let quitar = nuevaFila.querySelector('.quitar-fila');
                if (!quitar) {
                    quitar = document.createElement('button');
                    quitar.type = 'button';
                    quitar.className = 'btn btn-sm btn-outline-danger quitar-fila';
                    quitar.innerHTML = '<i class="fa-solid fa-trash-can me-1"></i> Quitar';
                    nuevaFila.querySelector('.d-flex.justify-content-between')?.appendChild(quitar);
                }
                quitar.classList.remove('d-none');

                contenedor.appendChild(nuevaFila);
                conectarControlesFila(nuevaFila);
                actualizarTotal();
                nuevaFila.querySelector('input[name="nomcri[]"]')?.focus();
            });
        }

        formulario.addEventListener('submit', function (event) {
            if (!formulario.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
                formulario.classList.add('was-validated');
                return;
            }

            const opcion = instrumento && instrumento.selectedIndex >= 0
                ? instrumento.options[instrumento.selectedIndex]
                : null;
            let base = opcion ? parseInt(opcion.dataset.pesoTotal || '0', 10) : 0;
            if (formulario.dataset.modoEdicion === '1') {
                base -= parseInt(formulario.dataset.vscumEditado || '0', 10);
            }
            base = Math.max(0, base);

            let enviado = 0;
            contenedor.querySelectorAll('input[name="vscum[]"]').forEach(input => {
                enviado += Number(input.value || 0);
            });

            if (base + enviado !== 100) {
                event.preventDefault();
                alert('La suma de "Si cumple" del instrumento debe quedar exactamente en 100. Total calculado: ' + (base + enviado) + '.');
            }
        });
    }

    if (ficha) {
        ficha.addEventListener('change', filtrarInstrumentosPorFicha);
    }
    if (instrumento) {
        instrumento.addEventListener('change', actualizarTotal);
    }

    document.addEventListener('DOMContentLoaded', function () {
        filtrarInstrumentosPorFicha();
        actualizarTotal();
    });

    if (window.jQuery && $.fn.DataTable) {
        $('#tablaMaestroCriterios').DataTable({
            language: {
                emptyTable: 'No hay criterios para mostrar.',
                info: 'Mostrando _START_ a _END_ de _TOTAL_ criterios',
                infoEmpty: 'No hay criterios para mostrar',
                infoFiltered: '(filtrados de _MAX_ criterios)',
                lengthMenu: 'Mostrar _MENU_ criterios por página',
                search: 'Buscar criterio, ficha o instrumento:',
                zeroRecords: 'No se encontraron criterios coincidentes',
                paginate: { first: 'Primero', last: 'Último', next: 'Siguiente', previous: 'Anterior' }
            },
            order: [[0, 'desc']],
            pageLength: -1,
            lengthChange: true,
            dom: 'Bfrtip',
            buttons: [
                { extend: 'copyHtml5', className: 'btn btn-sm btn-outline-secondary', text: '<i class="fa-solid fa-copy me-1"></i> Copiar' },
                { extend: 'excelHtml5', className: 'btn btn-sm btn-outline-success', text: '<i class="fa-solid fa-file-excel me-1"></i> Excel' },
                { extend: 'pdfHtml5', className: 'btn btn-sm btn-outline-danger', text: '<i class="fa-solid fa-file-pdf me-1"></i> PDF' },
                { extend: 'print', className: 'btn btn-sm btn-outline-primary', text: '<i class="fa-solid fa-print me-1"></i> Imprimir' }
            ]
        });
    }
})();
</script>
