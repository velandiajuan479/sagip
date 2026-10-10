<?php
require_once __DIR__ . '/../controllers/admcadep.php';
?>

<!-- Estilos personalizados para el diseño SENA Institucional -->
<style>
    .sena-card-header {
        background: linear-gradient(135deg, #39A900 0%, #1e7e34 100%) !important;
        color: #ffffff !important;
        border-top-left-radius: 10px !important;
        border-top-right-radius: 10px !important;
    }

    /* Ocultar botón de ayuda/pregunta '?' y flechas flotantes superiores */
    .btnayu, .btnhelp, a[title="Ver Ayuda"], .fa-circle-question, #ayuda {
        display: none !important;
    }

    /* Fijar la barra de usuario (Robinson Rincón) en la parte superior derecha de forma permanente */
    .banusu {
        position: fixed !important;
        top: 5px !important;
        right: 0px !important;
        z-index: 99999 !important;
    }

    .sena-card-dark-header {
        background-color: #00a859 !important;
        color: #ffffff !important;
        font-weight: 600;
    }

    /* Tarjeta estilo Administrativo (Basada en la Imagen 1 del Usuario) */
    .admin-card {
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        border: none;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        background: #ffffff;
    }

    .admin-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }

    .admin-card-header {
        background: #39A900;
        color: white;
        padding: 12px 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .admin-card-header .title-box {
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 800;
        font-size: 1.1rem;
        letter-spacing: 0.5px;
    }

    .admin-card-header .icon-user-badge {
        width: 38px;
        height: 38px;
        background: rgba(255, 255, 255, 0.25);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
    }

    .admin-card-body {
        padding: 25px 15px;
        text-align: center;
        color: #6c757d;
        font-size: 0.95rem;
        min-height: 90px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .admin-card-footer {
        background: #39A900;
        padding: 10px;
        display: flex;
        justify-content: center;
    }

    .counter-pill-box {
        background: rgba(255, 255, 255, 0.2);
        border: 1.5px solid rgba(255, 255, 255, 0.7);
        border-radius: 12px;
        padding: 4px 22px;
        color: white;
        font-weight: 700;
        font-size: 1.2rem;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        box-shadow: inset 0 0 6px rgba(0,0,0,0.1);
    }

    /* Botón verde '+' (Basado en la Imagen 2 del Usuario) */
    .add-btn {
        background: none;
        border: none;
        cursor: pointer;
        padding: 0 8px;
        font-size: 1.8rem;
        font-weight: 900;
        color: #39A900;
        transition: all 0.25s ease;
        line-height: 1;
    }

    .add-btn:hover {
        color: #1e7e34;
        transform: scale(1.35);
    }

    .action-icon-btn {
        width: 34px;
        height: 34px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: none;
        transition: all 0.2s ease;
        margin: 0 2px;
    }

    .action-icon-btn:hover {
        transform: scale(1.1);
    }

    .table-hover tbody tr:hover {
        background-color: rgba(57, 169, 0, 0.04) !important;
    }

    .nombre-link {
        color: #212529;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.2s ease;
    }

    .nombre-link:hover {
        color: #39A900;
        text-decoration: underline;
    }

    /* Reglas de Responsividad (Móviles y Tablets) */
    @media (max-width: 768px) {
        .sena-card-dark-header {
            flex-direction: column !important;
            align-items: flex-start !important;
            gap: 10px;
        }

        .sena-card-dark-header button {
            width: 100%;
            justify-content: center;
        }

        .subtable-pagination-container {
            flex-direction: column !important;
            align-items: center !important;
            text-align: center;
            gap: 10px;
        }

        .subtable-pagination-buttons {
            flex-wrap: wrap;
            justify-content: center;
            width: 100%;
        }

        .action-buttons-group {
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
            justify-content: center;
        }

        .action-buttons-group .btn {
            font-size: 0.75rem;
            padding: 4px 6px;
        }

        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
    }
</style>

<div class="container-fluid py-3">

    <!-- Mensajes de Alerta -->
    <?php if (isset($_SESSION['mensaje'])): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0" role="alert">
            <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($_SESSION['mensaje']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['mensaje']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($_SESSION['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Título Principal de la Vista -->
    <!-- Título Principal de la Vista -->
    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
        <h2 class="fw-bold text-success m-0 d-flex align-items-center gap-2">
            <i class="fas fa-building text-success fs-2"></i> Administración Dependencias
        </h2>
    </div>

    <!-- Sección de Tarjetas KPI / Acceso Directo -->
    <div class="row g-4 mb-4">
        <!-- Tarjeta 1: TOTAL DEPENDENCIAS (Sin flechas ni íconos sobrantes en la esquina derecha) -->
        <div class="col-md-6 col-sm-12">
            <div class="card admin-card h-100 border-0">
                <div class="admin-card-header" style="background: linear-gradient(135deg, #13582a 0%, #1e7e34 100%);">
                    <div class="title-box">
                        <div class="icon-user-badge" style="background: rgba(255,255,255,0.2);">
                            <i class="fas fa-building"></i>
                        </div>
                        <span>DEPENDENCIAS</span>
                    </div>
                </div>
                <div class="admin-card-body flex-column">
                    <h2 class="display-5 fw-bold text-success m-0"><?= (int)$total_dependencias ?></h2>
                    <small class="text-muted">Dependencias activas institucionales</small>
                </div>
            </div>
        </div>

        <!-- Tarjeta 2: BÚSQUEDA Y FILTRADO RÁPIDO -->
        <div class="col-md-6 col-sm-12">
            <div class="card admin-card h-100 border-0">
                <div class="admin-card-header" style="background: linear-gradient(135deg, #00a859 0%, #39A900 100%);">
                    <div class="title-box">
                        <div class="icon-user-badge">
                            <i class="fas fa-search"></i>
                        </div>
                        <span>BÚSQUEDA RÁPIDA</span>
                    </div>
                    <div>
                        <i class="fas fa-filter opacity-75 fs-5"></i>
                    </div>
                </div>
                <div class="admin-card-body flex-column p-3">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" id="filtroTabla" class="form-control border-start-0" placeholder="Buscar por nombre o código de la dependencia..." onkeyup="filtrarDependencias()">
                    </div>
                    <small class="text-muted mt-2"><i class="fas fa-info-circle me-1"></i>Filtros: dependencias por nombre, descripción, número de empleado o N° de documento.</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Sección Principal: Empleados por Dependencia -->
    <div class="card shadow-sm border-0 rounded-3 mb-4">
        <div class="card-header sena-card-dark-header py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold text-white d-flex align-items-center gap-2">
                <i class="fas fa-users me-2"></i> Empleados por Dependencia
            </h5>
            <!-- Botón Agregar Dependencia colocado en la parte superior derecha del título Empleados por Dependencia (Punto 3 de la imagen) -->
            <button class="btn btn-success fw-bold text-white border-white rounded-pill px-3 shadow-sm d-flex align-items-center gap-1" onclick="abrirModalCrear()">
                <i class="fas fa-plus-circle me-1"></i> Agregar Dependencia
            </button>
        </div>

        <div class="card-body p-0">


            <?php if (!empty($dependencias)): ?>
                <!-- Tabla de dependencias con botones '+' para filtrar por dependencia -->
                <div class="table-responsive p-3 pb-0">
                    <table class="table table-striped table-hover align-middle mb-0" id="tablaDependencias">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 80px;" class="ps-4">ID</th>
                                <th style="width: 260px;">Nombre</th>
                                <th>Descripción</th>
                                <th class="text-center" style="width: 130px;">Empleados</th>
                                <th class="text-center" style="width: 200px;">Desplegar / Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($dependencias as $dep): ?>
                                <tr class="fila-dependencia" data-id="<?= htmlspecialchars($dep['id_dependencia']) ?>" data-search="<?= htmlspecialchars(mb_strtolower($dep['nombre'] . ' ' . ($dep['descripcion'] ?? '') . ' ' . ($dep['empleados_search'] ?? ''))) ?>">
                                    <td class="ps-4 fw-bold text-secondary"><?= htmlspecialchars($dep['id_dependencia']) ?></td>
                                    <td class="fw-bold text-dark"><?= htmlspecialchars($dep['nombre']) ?></td>
                                    <td class="text-secondary">
                                        <?= !empty($dep['descripcion']) ? htmlspecialchars($dep['descripcion']) : '<em class="text-muted">Sin descripción</em>' ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge rounded-pill bg-success px-3 py-2 fs-6">
                                            <i class="fas fa-users me-1"></i><?= (int)($dep['total_empleados'] ?? 0) ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex align-items-center justify-content-center gap-1">
                                            <!-- Botón '+' / '-' Desplegar y Volver a Guardar -->
                                            <button class="add-btn add-empleados-btn" data-id="<?= htmlspecialchars($dep['id_dependencia']) ?>" data-nombre="<?= htmlspecialchars(addslashes($dep['nombre'])) ?>" title="Ver / Guardar Despliegue de Empleados">
                                                +
                                            </button>
                                            
                                            <!-- Editar -->
                                            <button class="action-icon-btn bg-light text-primary" onclick="editarDependencia(<?= $dep['id_dependencia'] ?>)" title="Editar dependencia">
                                                <i class="fas fa-pen-to-square"></i>
                                            </button>
                                            
                                            <!-- Asignar Usuario -->
                                            <button class="action-icon-btn bg-light text-success" onclick="abrirModalAsignar(<?= $dep['id_dependencia'] ?>, '<?= htmlspecialchars(addslashes($dep['nombre'])) ?>')" title="Asignar Usuario">
                                                <i class="fas fa-user-plus"></i>
                                            </button>

                                            <!-- Eliminar -->
                                            <button class="action-icon-btn bg-light text-danger" onclick="eliminarDependencia(<?= $dep['id_dependencia'] ?>, '<?= htmlspecialchars(addslashes($dep['nombre'])) ?>')" title="Eliminar dependencia">
                                                <i class="fas fa-trash-can"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <!-- Footer -->
            <div class="p-3 bg-light border-top">
                <small class="text-muted fw-bold">
                    Total de dependencias: <?= count($dependencias) ?>
                </small>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Crear / Editar Dependencia -->
<div class="modal fade" id="modalDependencia" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header sena-card-header">
                <h5 class="modal-title fw-bold" id="modalDependenciaTitulo">
                    <i class="fas fa-building me-2"></i>Nueva Dependencia
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formDependencia" onsubmit="guardarDependencia(event)">
                <div class="modal-body p-4">
                    <input type="hidden" id="id_dependencia" name="id_dependencia" value="">
                    
                    <div class="mb-3">
                        <label for="nombre" class="form-label fw-bold">Nombre de la Dependencia <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-lg" id="nombre" name="nombre" placeholder="Ej: Gestión de TIC" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="descripcion" class="form-label fw-bold">Descripción</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="4" placeholder="Describa las funciones principales de la dependencia..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary fw-bold" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success fw-bold">
                        <i class="fas fa-save me-1"></i> Guardar Dependencia
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Asignar Usuario a Dependencia -->
<div class="modal fade" id="modalAsignarUsuario" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header sena-card-header">
                <h5 class="modal-title fw-bold">
                    <i class="fas fa-user-plus me-2"></i>Asignar Empleado
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formAsignarUsuario" onsubmit="guardarAsignacion(event)">
                <div class="modal-body p-4">
                    <input type="hidden" id="id_dep_asignar" name="id_dependencia" value="">
                    <p class="mb-3 fw-semibold text-secondary" id="nombreDepAsignar"></p>
                    
                    <div class="mb-3">
                        <label for="selectUsuario" class="form-label fw-bold">Seleccionar Usuario / Empleado <span class="text-danger">*</span></label>
                        <select class="form-select form-select-lg" id="selectUsuario" name="idusu" required>
                            <option value="">Cargando usuarios...</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary fw-bold" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success fw-bold">
                        <i class="fas fa-check-circle me-1"></i> Asignar Usuario
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- JavaScript de la vista (Interacciones AJAX, Filtro y SweetAlert2) -->
<script>
// ====== ESTADO GLOBAL ======
let dependenciaAbierta = null; // id de la dependencia actualmente expandida
let subtablasData = {}; // idDep -> { lista: [...], paginaActual: 1, tamanoPagina: 10 }

// ====== GENERAR TABLA INLINE DE USUARIOS CON PAGINACIÓN ======
function generarTablaUsuarios(idDep) {
    const state = subtablasData[idDep];
    if (!state || !state.lista || state.lista.length === 0) {
        return `<div class="alert alert-info m-3 mb-0 border-0 shadow-sm">
            <i class="fas fa-info-circle me-2"></i>Esta dependencia no tiene usuarios asignados.
        </div>`;
    }

    // Deduplicar por idusu
    const vistos = new Set();
    const lista = state.lista.filter(emp => {
        if (vistos.has(emp.idusu)) return false;
        vistos.add(emp.idusu);
        return true;
    });

    const tamano = state.tamanoPagina || 10;
    const totalItems = lista.length;
    const totalPaginas = Math.ceil(totalItems / tamano) || 1;
    
    if (state.paginaActual > totalPaginas) state.paginaActual = totalPaginas;
    if (state.paginaActual < 1) state.paginaActual = 1;

    const pagina = state.paginaActual;
    const inicio = (pagina - 1) * tamano;
    const fin = Math.min(inicio + tamano, totalItems);
    const itemsPagina = lista.slice(inicio, fin);

    let rows = '';
    itemsPagina.forEach(emp => {
        const esActivo = emp.activo == 1;

        const estadoBadge = esActivo
            ? `<span class="badge bg-success px-2 py-1"><i class="fas fa-check-circle me-1"></i>Activo</span>`
            : `<span class="badge bg-danger px-2 py-1"><i class="fas fa-ban me-1"></i>Inactivo</span>`;

        let perfilBadge = '';
        const esContratista = emp.tipo_contrato === 'Contratista' || (emp.numero_contrato && emp.numero_contrato !== '');
        if (esContratista) {
            perfilBadge = `<span class="badge bg-primary px-2 py-1"><i class="fas fa-file-contract me-1"></i> Contratista</span>`;
        } else if (emp.nomper) {
            perfilBadge = `<span class="badge bg-info text-dark px-2 py-1"><i class="fas fa-user-tie me-1"></i> ${emp.nomper}</span>`;
        } else {
            perfilBadge = `<span class="badge bg-secondary px-2 py-1">Sin Perfil</span>`;
        }

        const nomLimpio = (emp.nombre || '').replace(/\\/g, '\\\\').replace(/'/g, "\\'");

        // Enlace al documento para dirigir a la sección de paz y salvos (pg=2401)
        const docHtml = emp.ndocusu
            ? `<a href="home.php?pg=2401&documento=${encodeURIComponent(emp.ndocusu)}&id_dependencia=${idDep}&desde=vadm" class="fw-bold text-success text-decoration-none hover-underline" title="Ir a Paz y Salvo de ${nomLimpio}">
                <i class="fas fa-id-card me-1"></i>${emp.ndocusu} <i class="fas fa-external-link-alt ms-1 text-muted" style="font-size:0.75rem;"></i>
               </a>`
            : `<span class="fw-bold text-secondary">N/A</span>`;

        // Deshabilitar: activo únicamente si el usuario está activo
        const btnDeshabilitar = esActivo
            ? `<button class="btn btn-sm fw-bold text-white px-2 py-1 shadow-sm me-1" style="background-color:#4a5568;border-color:#4a5568;" onclick="accionEstadoUsuario(${emp.idusu},${idDep},'${nomLimpio}',0)" title="Deshabilitar"><i class="fas fa-pause me-1"></i>Deshabilitar</button>`
            : `<button class="btn btn-sm fw-bold text-white px-2 py-1 shadow-sm me-1" style="background-color:#4a5568;border-color:#4a5568;opacity:0.4;" disabled title="Ya deshabilitado"><i class="fas fa-pause me-1"></i>Deshabilitar</button>`;

        // Habilitar: activo únicamente si el usuario está inactivo
        const btnHabilitar = !esActivo
            ? `<button class="btn btn-sm fw-bold text-white px-2 py-1 shadow-sm me-1" style="background-color:#39A900;border-color:#39A900;" onclick="accionEstadoUsuario(${emp.idusu},${idDep},'${nomLimpio}',1)" title="Habilitar"><i class="fas fa-check me-1"></i>Habilitar</button>`
            : `<button class="btn btn-sm fw-bold text-white px-2 py-1 shadow-sm me-1" style="background-color:#39A900;border-color:#39A900;opacity:0.4;" disabled title="Ya activo"><i class="fas fa-check me-1"></i>Habilitar</button>`;

        const btnEliminar = `<button class="btn btn-sm fw-bold text-white px-2 py-1 shadow-sm" style="background-color:#e53e3e;border-color:#e53e3e;" onclick="accionEliminarUsuario(${emp.idusu},${idDep},'${nomLimpio}')" title="Eliminar"><i class="fas fa-trash-can me-1"></i>Eliminar</button>`;

        rows += `
        <tr style="background-color:#f8fff5;">
            <td style="padding-left:40px;">${docHtml}</td>
            <td><span class="fw-semibold text-dark">${emp.nombre || 'N/A'}</span></td>
            <td class="text-muted">${emp.email || 'N/A'}</td>
            <td class="text-muted">${emp.telefono || 'N/A'}</td>
            <td>${perfilBadge}</td>
            <td class="text-center">${estadoBadge}</td>
            <td class="text-center text-nowrap">${btnDeshabilitar}${btnHabilitar}${btnEliminar}</td>
        </tr>`;
    });

    // Paginador HTML
    let paginadorHtml = '';
    if (totalItems > 0) {
        let pagBotones = '';
        for (let p = 1; p <= totalPaginas; p++) {
            pagBotones += `<button type="button" class="btn btn-sm ${p === pagina ? 'btn-success text-white' : 'btn-outline-success'} fw-bold px-2.5 py-1 me-1" onclick="cambiarPaginaSubtabla(${idDep}, ${p})">${p}</button>`;
        }

        paginadorHtml = `
        <div class="d-flex justify-content-between align-items-center p-3 bg-light border-top flex-wrap gap-2">
            <small class="text-muted fw-bold">
                Mostrando <span class="text-success">${inicio + 1}</span> - <span class="text-success">${fin}</span> de <span class="text-success">${totalItems}</span> usuarios (Máx. 10 por página)
            </small>
            <div class="d-flex align-items-center">
                <button type="button" class="btn btn-sm btn-outline-success fw-bold me-1" ${pagina === 1 ? 'disabled' : ''} onclick="cambiarPaginaSubtabla(${idDep}, ${pagina - 1})">
                    <i class="fas fa-chevron-left me-1"></i> Anterior
                </button>
                ${pagBotones}
                <button type="button" class="btn btn-sm btn-outline-success fw-bold" ${pagina === totalPaginas ? 'disabled' : ''} onclick="cambiarPaginaSubtabla(${idDep}, ${pagina + 1})">
                    Siguiente <i class="fas fa-chevron-right ms-1"></i>
                </button>
            </div>
        </div>`;
    }

    return `<div class="table-responsive">
      <table class="table table-sm align-middle mb-0 border-start border-4 border-success">
        <thead style="background-color:#e8f5e2;">
          <tr>
            <th style="padding-left:40px;width:150px;">Documento</th>
            <th>Nombre Completo</th>
            <th>Correo Electrónico</th>
            <th>Teléfono</th>
            <th style="width:160px;">Perfil / Contrato</th>
            <th class="text-center" style="width:100px;">Estado</th>
            <th class="text-center" style="width:300px;">Acciones</th>
          </tr>
        </thead>
        <tbody>${rows}</tbody>
      </table>
    </div>
    ${paginadorHtml}`;
}

function cambiarPaginaSubtabla(idDep, nuevaPagina) {
    if (!subtablasData[idDep]) return;
    subtablasData[idDep].paginaActual = nuevaPagina;
    const filaEmp = document.getElementById(`fila-emp-${idDep}`);
    if (filaEmp) {
        const td = filaEmp.querySelector('td');
        if (td) td.innerHTML = generarTablaUsuarios(idDep);
    }
}

// ====== TOGGLE DESPLIEGUE INLINE ======
async function toggleDespliegue(btn) {
    const idDep = btn.getAttribute('data-id');
    const nomDep = btn.getAttribute('data-nombre') || 'Dependencia';

    // Si ya está abierta la misma → cerrar
    if (dependenciaAbierta === idDep) {
        cerrarDespliegue(idDep);
        return;
    }

    // Cerrar cualquier otra abierta
    if (dependenciaAbierta) {
        cerrarDespliegue(dependenciaAbierta);
    }

    dependenciaAbierta = idDep;
    btn.innerHTML = '<span class="fw-bold" style="color:#dc3545;font-size:1.4rem;line-height:1;">−</span>';
    btn.title = 'Cerrar despliegue';

    // Insertar fila de spinner debajo de la fila padre
    const filaPadre = btn.closest('tr');
    const colspan = filaPadre.cells.length;
    const filaEmp = document.createElement('tr');
    filaEmp.id = `fila-emp-${idDep}`;
    filaEmp.innerHTML = `<td colspan="${colspan}" class="p-0">
        <div class="text-center py-3">
            <div class="spinner-border spinner-border-sm text-success me-2" role="status"><span class="visually-hidden">Cargando...</span></div>
            <span class="text-muted fw-bold">Cargando usuarios de <strong>${nomDep}</strong>...</span>
        </div></td>`;
    filaPadre.insertAdjacentElement('afterend', filaEmp);

    try {
        const response = await fetch(`controllers/admcadep.php?action=get_empleados&id_dependencia=${idDep}`, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        });
        if (!response.ok) throw new Error(`Error del servidor (${response.status})`);
        const data = await response.json();
        if (!data.success) throw new Error(data.error || 'Error al obtener usuarios');

        subtablasData[idDep] = {
            lista: data.data || [],
            paginaActual: 1,
            tamanoPagina: 10
        };

        filaEmp.innerHTML = `<td colspan="${colspan}" class="p-0">${generarTablaUsuarios(idDep)}</td>`;
    } catch (err) {
        filaEmp.innerHTML = `<td colspan="${colspan}" class="p-2">
            <div class="alert alert-danger border-0 shadow-sm mb-0">
                <i class="fas fa-exclamation-triangle me-2"></i>${err.message}
            </div></td>`;
    }
}

function cerrarDespliegue(idDep) {
    const fila = document.getElementById(`fila-emp-${idDep}`);
    if (fila) fila.remove();
    const btn = document.querySelector(`.add-empleados-btn[data-id="${idDep}"]`);
    if (btn) { btn.innerHTML = '+'; btn.title = 'Ver / Guardar Despliegue de Empleados'; }
    dependenciaAbierta = null;
}

async function recargarSubTabla(idDep) {
    const filaEmp = document.getElementById(`fila-emp-${idDep}`);
    if (!filaEmp) return;
    const td = filaEmp.querySelector('td');
    td.innerHTML = `<div class="text-center py-3"><div class="spinner-border spinner-border-sm text-success me-2" role="status"></div><span class="text-muted">Actualizando...</span></div>`;
    try {
        const response = await fetch(`controllers/admcadep.php?action=get_empleados&id_dependencia=${idDep}`, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await response.json();
        if (data.success) {
            const pag = (subtablasData[idDep] && subtablasData[idDep].paginaActual) ? subtablasData[idDep].paginaActual : 1;
            subtablasData[idDep] = {
                lista: data.data || [],
                paginaActual: pag,
                tamanoPagina: 10
            };
            td.innerHTML = generarTablaUsuarios(idDep);
        }
    } catch (e) { /* silencioso */ }
}

// ====== ACCIONES DE USUARIO ======
function accionEstadoUsuario(idusu, idDep, nombre, activar) {
    const titulo = activar == 1 ? '¿Habilitar Usuario?' : '¿Deshabilitar Usuario?';
    const texto = activar == 1
        ? `¿Desea habilitar a "${nombre}" en esta dependencia?`
        : `¿Desea deshabilitar a "${nombre}"? El usuario quedará inactivo pero NO se borrará.`;

    Swal.fire({
        title: titulo, text: texto,
        icon: activar == 1 ? 'info' : 'warning',
        showCancelButton: true,
        confirmButtonColor: activar == 1 ? '#39A900' : '#ffc107',
        cancelButtonColor: '#6c757d',
        confirmButtonText: activar == 1 ? 'Sí, habilitar' : 'Sí, deshabilitar',
        cancelButtonText: 'Cancelar'
    }).then(async result => {
        if (!result.isConfirmed) return;
        try {
            const fd = new FormData();
            fd.append('action', 'cambiar_estado_usuario');
            fd.append('idusu', idusu);
            fd.append('id_dependencia', idDep);
            fd.append('activo', activar);
            const res = await fetch('controllers/admcadep.php', { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            const json = await res.json();
            if (json.success) {
                Swal.fire({ icon: 'success', title: '¡Éxito!', text: json.message, timer: 1200, showConfirmButton: false });
                if (subtablasData[idDep] && subtablasData[idDep].lista) {
                    const usr = subtablasData[idDep].lista.find(u => u.idusu == idusu);
                    if (usr) usr.activo = activar;
                    const filaEmp = document.getElementById(`fila-emp-${idDep}`);
                    if (filaEmp) {
                        const td = filaEmp.querySelector('td');
                        if (td) td.innerHTML = generarTablaUsuarios(idDep);
                    }
                } else {
                    recargarSubTabla(idDep);
                }
            } else {
                Swal.fire('Error', json.error || 'No se pudo cambiar el estado', 'error');
            }
        } catch (e) { Swal.fire('Error', 'Error de conexión al actualizar estado', 'error'); }
    });
}

function accionEliminarUsuario(idusu, idDep, nombre) {
    Swal.fire({
        title: '¿Eliminar usuario de la dependencia?',
        text: `¿Estás seguro de eliminar a "${nombre}" de esta dependencia?`,
        icon: 'error', showCancelButton: true,
        confirmButtonColor: '#e53e3e', cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, eliminar', cancelButtonText: 'Cancelar'
    }).then(async result => {
        if (!result.isConfirmed) return;
        try {
            const fd = new FormData();
            fd.append('action', 'remover_usuario');
            fd.append('idusu', idusu);
            fd.append('id_dependencia', idDep);
            const res = await fetch('controllers/admcadep.php', { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            const json = await res.json();
            if (json.success) {
                Swal.fire({ icon: 'success', title: 'Eliminado', text: json.message, timer: 1200, showConfirmButton: false });
                if (subtablasData[idDep] && subtablasData[idDep].lista) {
                    subtablasData[idDep].lista = subtablasData[idDep].lista.filter(u => u.idusu != idusu);
                    const filaEmp = document.getElementById(`fila-emp-${idDep}`);
                    if (filaEmp) {
                        const td = filaEmp.querySelector('td');
                        if (td) td.innerHTML = generarTablaUsuarios(idDep);
                    }
                } else {
                    recargarSubTabla(idDep);
                }
            } else {
                Swal.fire('Error', json.error || 'No se pudo eliminar el usuario', 'error');
            }
        } catch (e) { Swal.fire('Error', 'Error al procesar la eliminación', 'error'); }
    });
}

// ====== FILTRO TABLA PRINCIPAL ======
function filtrarDependencias() {
    const input = document.getElementById('filtroTabla').value.toLowerCase().trim();
    document.querySelectorAll('#tablaDependencias tbody tr.fila-dependencia').forEach(fila => {
        const textData = (fila.getAttribute('data-search') || fila.textContent).toLowerCase();
        fila.style.display = textData.includes(input) ? '' : 'none';
    });
}

// ====== MODAL CREAR DEPENDENCIA ======
function abrirModalCrear() {
    document.getElementById('formDependencia').reset();
    document.getElementById('id_dependencia').value = '';
    document.getElementById('modalDependenciaTitulo').innerHTML = '<i class="fas fa-building me-2"></i>Nueva Dependencia';
    new bootstrap.Modal(document.getElementById('modalDependencia')).show();
}

// ====== MODAL EDITAR DEPENDENCIA ======
async function editarDependencia(id) {
    try {
        const res = await fetch(`controllers/admcadep.php?action=get_one&id_dependencia=${id}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        const json = await res.json();
        if (json.success && json.data) {
            document.getElementById('id_dependencia').value = json.data.id_dependencia;
            document.getElementById('nombre').value = json.data.nombre;
            document.getElementById('descripcion').value = json.data.descripcion || '';
            document.getElementById('modalDependenciaTitulo').innerHTML = '<i class="fas fa-edit me-2"></i>Editar Dependencia';
            new bootstrap.Modal(document.getElementById('modalDependencia')).show();
        } else { Swal.fire('Error', json.error || 'No se pudo cargar la dependencia', 'error'); }
    } catch (e) { Swal.fire('Error', 'Error de conexión con el servidor', 'error'); }
}

// ====== GUARDAR DEPENDENCIA ======
async function guardarDependencia(e) {
    e.preventDefault();
    const fd = new FormData(document.getElementById('formDependencia'));
    fd.append('action', 'guardar');
    try {
        const res = await fetch('controllers/admcadep.php', { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        const json = await res.json();
        if (json.success) {
            Swal.fire({ icon: 'success', title: '¡Éxito!', text: json.message, timer: 1500, showConfirmButton: false }).then(() => location.reload());
        } else { Swal.fire('Error', json.error || 'No se pudo guardar', 'error'); }
    } catch (e) { Swal.fire('Error', 'Ocurrió un error inesperado al guardar', 'error'); }
}

// ====== ELIMINAR DEPENDENCIA ======
function eliminarDependencia(id, nombre) {
    Swal.fire({
        title: '¿Eliminar Dependencia?',
        text: `¿Estás seguro de eliminar la dependencia "${nombre}"?`,
        icon: 'warning', showCancelButton: true,
        confirmButtonColor: '#d33', cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, eliminar', cancelButtonText: 'Cancelar'
    }).then(async result => {
        if (!result.isConfirmed) return;
        try {
            const fd = new FormData();
            fd.append('action', 'eliminar');
            fd.append('id_dependencia', id);
            const res = await fetch('controllers/admcadep.php', { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            const json = await res.json();
            if (json.success) {
                Swal.fire('Eliminado', json.message, 'success').then(() => location.reload());
            } else { Swal.fire('Error', json.error || 'No se pudo eliminar la dependencia', 'error'); }
        } catch (e) { Swal.fire('Error', 'Error al procesar la solicitud', 'error'); }
    });
}

// ====== MODAL ASIGNAR USUARIO ======
async function abrirModalAsignar(idDep, nomDep) {
    document.getElementById('id_dep_asignar').value = idDep;
    document.getElementById('nombreDepAsignar').innerText = `Dependencia: ${nomDep}`;
    const select = document.getElementById('selectUsuario');
    select.innerHTML = '<option value="">Cargando usuarios...</option>';
    new bootstrap.Modal(document.getElementById('modalAsignarUsuario')).show();
    try {
        const res = await fetch('controllers/admcadep.php?action=get_usuarios_disponibles', { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        const json = await res.json();
        if (json.success && json.data) {
            let opts = '<option value="">-- Seleccione un Usuario --</option>';
            json.data.forEach(u => { opts += `<option value="${u.idusu}">${u.nombre} (${u.ndocusu || 'Sin Doc'}) - ${u.email || ''}</option>`; });
            select.innerHTML = opts;
        } else { select.innerHTML = '<option value="">Error al cargar usuarios</option>'; }
    } catch (e) { select.innerHTML = '<option value="">Error de conexión</option>'; }
}

// ====== GUARDAR ASIGNACIÓN ======
async function guardarAsignacion(e) {
    e.preventDefault();
    const fd = new FormData(document.getElementById('formAsignarUsuario'));
    fd.append('action', 'asignar_usuario');
    try {
        const res = await fetch('controllers/admcadep.php', { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        const json = await res.json();
        if (json.success) {
            bootstrap.Modal.getInstance(document.getElementById('modalAsignarUsuario')).hide();
            Swal.fire('Asignado', json.message, 'success').then(() => location.reload());
        } else { Swal.fire('Error', json.error || 'No se pudo asignar el usuario', 'error'); }
    } catch (e) { Swal.fire('Error', 'Ocurrió un error al asignar el usuario', 'error'); }
}

// ====== EVENTOS ======
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.add-empleados-btn').forEach(btn => {
        btn.addEventListener('click', function () { toggleDespliegue(this); });
    });
});

</script>

<!--
/*
function renderizarTablaEmpleadosSub() {
    const empleadosContent = document.getElementById('empleados-global-content');
    if (!empleadosContent) return;

    const inputFiltro = (document.getElementById('filtroEmpleadosTabla')?.value || '').toLowerCase().trim();
    
    let listaFiltrada = empleadosSubLista.filter(emp => {
        const coincideTermino = !inputFiltro || 
            (emp.nombre || '').toLowerCase().includes(inputFiltro) || 
            (emp.ndocusu || '').toLowerCase().includes(inputFiltro) || 
            (emp.email || '').toLowerCase().includes(inputFiltro) ||
            (emp.nomper || '').toLowerCase().includes(inputFiltro) ||
            (emp.tipo_contrato || '').toLowerCase().includes(inputFiltro);

        let coincideEstado = true;
        if (filtroEstadoSub === 'activo') coincideEstado = (emp.activo == 1);
        if (filtroEstadoSub === 'desactivado') coincideEstado = (emp.activo == 0);
        if (filtroEstadoSub === 'contratistas') coincideEstado = (emp.tipo_contrato === 'Contratista' || (emp.nomper && emp.nomper.toLowerCase().includes('contratista')));

        return coincideTermino && coincideEstado;
    });

    const cantTotal = empleadosSubLista.length;
    const cantActivos = empleadosSubLista.filter(e => e.activo == 1).length;
    const cantDesactivados = empleadosSubLista.filter(e => e.activo == 0).length;
    const cantContratistas = empleadosSubLista.filter(e => e.tipo_contrato === 'Contratista' || (e.nomper && e.nomper.toLowerCase().includes('contratista'))).length;

    // Actualizar badge de total
    const badgeTotal = document.getElementById('cant-total-emp');
    if (badgeTotal) badgeTotal.innerText = cantTotal;

    // Paginación: Máximo 10 elementos por página
    const totalPaginas = Math.ceil(listaFiltrada.length / tamanoPaginaSub) || 1;
    if (paginaActualSub > totalPaginas) paginaActualSub = totalPaginas;
    if (paginaActualSub < 1) paginaActualSub = 1;

    const inicio = (paginaActualSub - 1) * tamanoPaginaSub;
    const fin = inicio + tamanoPaginaSub;
    const itemsPagina = listaFiltrada.slice(inicio, fin);

    let html = `
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2 pb-2 border-bottom">
            <div>
                <h5 class="mb-1 text-success fw-bold d-flex align-items-center gap-2">
                    <i class="fas fa-users me-1"></i>${nombreDependenciaActualSub}
                </h5>
                <div class="d-flex align-items-center gap-2 mt-1 flex-wrap">
                    <span class="badge bg-success px-2 py-1 fs-6"><i class="fas fa-user-check me-1"></i>Activos: ${cantActivos}</span>
                    <span class="badge bg-warning text-dark px-2 py-1 fs-6" style="background-color: #ffc107 !important;"><i class="fas fa-file-contract me-1"></i>Contratistas: ${cantContratistas}</span>
                    <span class="badge bg-danger px-2 py-1 fs-6"><i class="fas fa-user-slash me-1"></i>Desactivados: ${cantDesactivados}</span>
                    <small class="text-muted fw-bold">| Total: ${cantTotal}</small>
                </div>
            </div>

            <div class="d-flex align-items-center gap-2 flex-wrap">
                <div class="btn-group btn-group-sm me-2" role="group">
                    <button class="btn ${filtroEstadoSub === 'todos' ? 'btn-success' : 'btn-outline-success'} fw-bold" onclick="cambiarFiltroEstadoSub('todos')">
                        Todos (${cantTotal})
                    </button>
                    <button class="btn ${filtroEstadoSub === 'contratistas' ? 'btn-warning text-dark' : 'btn-outline-warning text-dark'} fw-bold" onclick="cambiarFiltroEstadoSub('contratistas')">
                        <i class="fas fa-file-contract me-1"></i>Contratistas (${cantContratistas})
                    </button>
                    <button class="btn ${filtroEstadoSub === 'activo' ? 'btn-success' : 'btn-outline-success'} fw-bold" onclick="cambiarFiltroEstadoSub('activo')">
                        Activos (${cantActivos})
                    </button>
                    <button class="btn ${filtroEstadoSub === 'desactivado' ? 'btn-danger' : 'btn-outline-danger'} fw-bold" onclick="cambiarFiltroEstadoSub('desactivado')">
                        Desactivados (${cantDesactivados})
                    </button>
                </div>

                <div class="input-group input-group-sm" style="max-width: 250px;">
                    <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" id="filtroEmpleadosTabla" class="form-control" value="${inputFiltro}" placeholder="Buscar nombre, doc o perfil..." onkeyup="filtrarEmpleadosTabla()">
                </div>

                <button class="btn btn-sm btn-success fw-bold text-white shadow-sm" style="border-radius: 50%; width: 36px; height: 36px; display: inline-flex; align-items: center; justify-content: center;" onclick="recogerDespliegueEmpleados()" title="Guardar / Plegar Despliegue">
                    <i class="fas fa-chevron-up"></i>
                </button>
            </div>
        </div>`;

    if (listaFiltrada.length === 0) {
        html += `
            <div class="alert alert-info border-0 shadow-sm mb-0">
                <i class="fas fa-info-circle me-2 fs-5"></i> No se encontraron registros con los criterios seleccionados.
            </div>`;
    } else {
        html += `
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="tablaEmpleadosSub">
                    <thead class="table-light">
                        <tr>
                            <th>Documento</th>
                            <th>Nombre Completo</th>
                            <th>Correo Electrónico</th>
                            <th>Teléfono</th>
                            <th>Perfil / Contrato</th>
                            <th class="text-center">Estado</th>
                            <th class="text-center" style="width: 140px;">Deshabilitar</th>
                            <th class="text-center" style="width: 120px;">Habilitar</th>
                            <th class="text-center" style="width: 120px;">Eliminar</th>
                        </tr>
                    </thead>
                    <tbody>`;

        itemsPagina.forEach(emp => {
            const estadoBadge = emp.activo == 1 
                ? `<span class="badge bg-success px-2 py-1"><i class="fas fa-check-circle me-1"></i> Activo</span>`
                : `<span class="badge bg-danger px-2 py-1"><i class="fas fa-ban me-1"></i> Desactivado</span>`;

            let perfil = '';
            if (emp.nomper) {
                perfil += `<span class="badge bg-primary px-2 py-1 me-1"><i class="fas fa-user-tie me-1"></i> ${emp.nomper}</span>`;
            }
            if (emp.tipo_contrato === 'Contratista' || (emp.numero_contrato && emp.numero_contrato !== '')) {
                perfil += `<span class="badge bg-warning text-dark px-2 py-1" style="background-color: #ffc107 !important;"><i class="fas fa-file-contract me-1"></i> Contratista</span>`;
            } else if (!emp.nomper) {
                perfil = `<span class="badge bg-secondary px-2 py-1">Sin Perfil</span>`;
            }

            const nomLimpio = (emp.nombre || '').replace(/\\/g, "\\\\").replace(/'/g, "\\'").replace(/"/g, "&quot;");
            const depIdNum = emp.id_dependencia || idDependenciaActualSub || 0;

            // Botón Deshabilitar (Gris oscuro con ícono de pausa ||)
            const btnDeshabilitar = `<button class="btn btn-secondary btn-sm fw-bold text-white px-2 py-1 shadow-sm" style="background-color: #4a5568; border-color: #4a5568;" onclick="deshabilitarUsuario(${emp.idusu}, ${depIdNum}, '${nomLimpio}', 0)" title="Deshabilitar usuario">
                    <i class="fas fa-pause me-1"></i> Deshabilitar
                </button>`;

            // Botón Habilitar (Verde con ícono de check ✓)
            const btnHabilitar = `<button class="btn btn-success btn-sm fw-bold text-white px-2 py-1 shadow-sm" style="background-color: #39A900; border-color: #39A900;" onclick="deshabilitarUsuario(${emp.idusu}, ${depIdNum}, '${nomLimpio}', 1)" title="Habilitar usuario">
                    <i class="fas fa-check me-1"></i> Habilitar
                </button>`;

            // Botón Eliminar (Rojo con ícono de basura 🗑)
            const btnEliminar = `<button class="btn btn-danger btn-sm fw-bold text-white px-2 py-1 shadow-sm" style="background-color: #e53e3e; border-color: #e53e3e;" onclick="eliminarUsuarioDependencia(${emp.idusu}, ${depIdNum}, '${nomLimpio}')" title="Eliminar usuario">
                    <i class="fas fa-trash-can me-1"></i> Eliminar
                </button>`;

            html += `
            <tr>
                <td><span class="fw-bold text-dark">${emp.ndocusu || 'N/A'}</span></td>
                <td><span class="fw-semibold">${emp.nombre || 'N/A'}</span></td>
                <td>${emp.email || 'N/A'}</td>
                <td>${emp.telefono || 'N/A'}</td>
                <td>${perfil}</td>
                <td class="text-center">${estadoBadge}</td>
                <td class="text-center">${btnDeshabilitar}</td>
                <td class="text-center">${btnHabilitar}</td>
                <td class="text-center">${btnEliminar}</td>
            </tr>`;
        });

        html += `</tbody></table></div>`;

        // Paginador (Máximo 10 elementos por página)
        html += `
            <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top flex-wrap gap-2">
                <small class="text-muted fw-bold">
                    Mostrando ${inicio + 1} - ${Math.min(fin, listaFiltrada.length)} de ${listaFiltrada.length} registros (Máx. 10 por página)
                </small>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-secondary fw-bold" ${paginaActualSub === 1 ? 'disabled' : ''} onclick="cambiarPaginaSub(${paginaActualSub - 1})">
                        <i class="fas fa-chevron-left me-1"></i> Anterior
                    </button>
                    <span class="btn btn-light fw-bold disabled border">Página ${paginaActualSub} de ${totalPaginas}</span>
                    <button class="btn btn-outline-secondary fw-bold" ${paginaActualSub === totalPaginas ? 'disabled' : ''} onclick="cambiarPaginaSub(${paginaActualSub + 1})">
                        Siguiente <i class="fas fa-chevron-right ms-1"></i>
                    </button>
                </div>
            </div>`;
    }

    empleadosContent.innerHTML = html;
}

function cambiarPaginaSub(pag) {
    paginaActualSub = pag;
    renderizarTablaEmpleadosSub();
}

function cambiarFiltroEstadoSub(estado) {
    filtroEstadoSub = estado;
    paginaActualSub = 1;
    renderizarTablaEmpleadosSub();
}

function filtrarEmpleadosTabla() {
    paginaActualSub = 1;
    renderizarTablaEmpleadosSub();
}

async function cargarEmpleadosGlobales(id = 0, nomDep = 'Todas las Dependencias') {
    const empleadosContainer = document.getElementById('empleados-global-container');
    const empleadosContent = document.getElementById('empleados-global-content');
    const pill = document.getElementById('nombre-dep-pill');
    if (pill) pill.innerText = nomDep;

    if (empleadosContainer) empleadosContainer.style.display = 'block';
    if (empleadosContent) {
        empleadosContent.innerHTML = `
            <div class="text-center py-4">
                <div class="spinner-border text-success" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
                <p class="mt-2 text-muted fw-bold">Cargando lista de usuarios...</p>
            </div>`;
    }

    try {
        const response = await fetch('controllers/admcadep.php?action=get_empleados&id_dependencia=' + id, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        if (!response.ok) {
            throw new Error(`Error del servidor (${response.status})`);
        }

        const data = await response.json();

        if (!data.success) {
            throw new Error(data.error || 'Error al obtener usuarios');
        }

        empleadosSubLista = data.data || [];
        paginaActualSub = 1;
        filtroEstadoSub = 'todos';
        idDependenciaActualSub = id;
        nombreDependenciaActualSub = nomDep;

        renderizarTablaEmpleadosSub();
    } catch(error) {
        if (empleadosContent) {
            empleadosContent.innerHTML = `
                <div class="alert alert-danger border-0 shadow-sm mb-0">
                    <i class="fas fa-exclamation-triangle me-2"></i> ${error.message}
                </div>`;
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Escuchar clicks en los botones '+' / '-' de las dependencias
    document.querySelectorAll('.add-empleados-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const nomDep = this.getAttribute('data-nombre') || 'Dependencia';

            if (dependenciaDesplegadaActual === id) {
                recogerDespliegueEmpleados();
                return;
            }

            document.querySelectorAll('.add-empleados-btn').forEach(b => {
                b.innerHTML = '+';
            });
            this.innerHTML = '<span class="text-danger fw-bold">−</span>';
            dependenciaDesplegadaActual = id;

            cargarEmpleadosGlobales(id, nomDep);
        });
    });

    // Carga inicial automática de usuarios al abrir la vista
    cargarEmpleadosGlobales(0, 'Todas las Dependencias');
});

// Función para filtrar dependencias en tiempo real por Nombre o Número de Documento
function filtrarDependencias() {
    const input = document.getElementById('filtroTabla').value.toLowerCase().trim();
    const filas = document.querySelectorAll('#tablaDependencias tbody tr');

    filas.forEach(fila => {
        const textData = (fila.getAttribute('data-search') || fila.textContent).toLowerCase();
        if (textData.includes(input)) {
            fila.style.display = '';
        } else {
            fila.style.display = 'none';
        }
    });
}

// Abrir Modal Crear Dependencia
function abrirModalCrear() {
    document.getElementById('formDependencia').reset();
    document.getElementById('id_dependencia').value = '';
    document.getElementById('modalDependenciaTitulo').innerHTML = '<i class="fas fa-building me-2"></i>Nueva Dependencia';
    const modal = new bootstrap.Modal(document.getElementById('modalDependencia'));
    modal.show();
}

// Abrir Modal Editar Dependencia
async function editarDependencia(id) {
    try {
        const response = await fetch(`controllers/admcadep.php?action=get_one&id_dependencia=${id}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const res = await response.json();
        
        if (res.success && res.data) {
            document.getElementById('id_dependencia').value = res.data.id_dependencia;
            document.getElementById('nombre').value = res.data.nombre;
            document.getElementById('descripcion').value = res.data.descripcion || '';
            document.getElementById('modalDependenciaTitulo').innerHTML = '<i class="fas fa-edit me-2"></i>Editar Dependencia';
            
            const modal = new bootstrap.Modal(document.getElementById('modalDependencia'));
            modal.show();
        } else {
            Swal.fire('Error', res.error || 'No se pudo cargar la dependencia', 'error');
        }
    } catch(e) {
        Swal.fire('Error', 'Error de conexión con el servidor', 'error');
    }
}

// Guardar Dependencia (Crear o Editar)
async function guardarDependencia(e) {
    e.preventDefault();
    const form = document.getElementById('formDependencia');
    const formData = new FormData(form);
    formData.append('action', 'guardar');

    try {
        const response = await fetch('controllers/admcadep.php', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const res = await response.json();

        if (res.success) {
            Swal.fire({
                icon: 'success',
                title: '¡Éxito!',
                text: res.message,
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                location.reload();
            });
        } else {
            Swal.fire('Error', res.error || 'No se pudo guardar la dependencia', 'error');
        }
    } catch(err) {
        Swal.fire('Error', 'Ocurrió un error inesperado al guardar', 'error');
    }
}

// Eliminar Dependencia con SweetAlert2
function eliminarDependencia(id, nombre) {
    Swal.fire({
        title: '¿Eliminar Dependencia?',
        text: `¿Estás seguro de eliminar la dependencia "${nombre}"?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then(async (result) => {
        if (result.isConfirmed) {
            try {
                const formData = new FormData();
                formData.append('action', 'eliminar');
                formData.append('id_dependencia', id);

                const response = await fetch('controllers/admcadep.php', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const res = await response.json();

                if (res.success) {
                    Swal.fire('Eliminado', res.message, 'success').then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Error', res.error || 'No se pudo eliminar la dependencia', 'error');
                }
            } catch(e) {
                Swal.fire('Error', 'Error al procesar la solicitud', 'error');
            }
        }
    });
}

// Abrir Modal Asignar Usuario
async function abrirModalAsignar(idDep, nomDep) {
    document.getElementById('id_dep_asignar').value = idDep;
    document.getElementById('nombreDepAsignar').innerText = `Dependencia: ${nomDep}`;
    const select = document.getElementById('selectUsuario');
    select.innerHTML = '<option value="">Cargando usuarios...</option>';

    const modal = new bootstrap.Modal(document.getElementById('modalAsignarUsuario'));
    modal.show();

    try {
        const response = await fetch('controllers/admcadep.php?action=get_usuarios_disponibles', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const res = await response.json();

        if (res.success && res.data) {
            let options = '<option value="">-- Seleccione un Usuario --</option>';
            res.data.forEach(u => {
                options += `<option value="${u.idusu}">${u.nombre} (${u.ndocusu || 'Sin Doc'}) - ${u.email || ''}</option>`;
            });
            select.innerHTML = options;
        } else {
            select.innerHTML = '<option value="">Error al cargar usuarios</option>';
        }
    } catch(e) {
        select.innerHTML = '<option value="">Error de conexión</option>';
    }
}

// Guardar Asignación de Usuario
async function guardarAsignacion(e) {
    e.preventDefault();
    const form = document.getElementById('formAsignarUsuario');
    const formData = new FormData(form);
    formData.append('action', 'asignar_usuario');

    try {
        const response = await fetch('controllers/admcadep.php', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const res = await response.json();

        if (res.success) {
            bootstrap.Modal.getInstance(document.getElementById('modalAsignarUsuario')).hide();
            Swal.fire('Asignado', res.message, 'success').then(() => {
                location.reload();
            });
        } else {
            Swal.fire('Error', res.error || 'No se pudo asignar el usuario', 'error');
        }
    } catch(err) {
        Swal.fire('Error', 'Ocurrió un error al asignar el usuario', 'error');
    }
}

// Deshabilitar o Habilitar Usuario (sin borrarlo de la base de datos)
function deshabilitarUsuario(idusu, idDep, nombre, activar) {
    const titulo = activar == 1 ? '¿Habilitar Usuario?' : '¿Deshabilitar Usuario?';
    const mensaje = activar == 1 
        ? `¿Desea habilitar a "${nombre}" en esta dependencia?` 
        : `¿Desea deshabilitar a "${nombre}"? El usuario quedará deshabilitado pero NO se borrará del sistema.`;

    Swal.fire({
        title: titulo,
        text: mensaje,
        icon: activar == 1 ? 'info' : 'warning',
        showCancelButton: true,
        confirmButtonColor: activar == 1 ? '#28a745' : '#ffc107',
        cancelButtonColor: '#6c757d',
        confirmButtonText: activar == 1 ? 'Sí, habilitar' : 'Sí, deshabilitar',
        cancelButtonText: 'Cancelar'
    }).then(async (result) => {
        if (result.isConfirmed) {
            try {
                const formData = new FormData();
                formData.append('action', 'cambiar_estado_usuario');
                formData.append('idusu', idusu);
                formData.append('id_dependencia', idDep);
                formData.append('activo', activar);

                const response = await fetch('controllers/admcadep.php', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const res = await response.json();

                if (res.success) {
                    Swal.fire('¡Éxito!', res.message, 'success');
                    const emp = empleadosSubLista.find(e => e.idusu == idusu);
                    if (emp) emp.activo = activar;
                    renderizarTablaEmpleadosSub();
                } else {
                    Swal.fire('Error', res.error || 'No se pudo cambiar el estado', 'error');
                }
            } catch(e) {
                Swal.fire('Error', 'Error de conexión al actualizar estado', 'error');
            }
        }
    });
}

// Eliminar / Desvincular Usuario definitivamente de la Dependencia
function eliminarUsuarioDependencia(idusu, idDep, nombre) {
    Swal.fire({
        title: '¿Eliminar Usuario de Dependencia?',
        text: `¿Estás seguro de ELIMINAR a "${nombre}" de esta dependencia?`,
        icon: 'error',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then(async (result) => {
        if (result.isConfirmed) {
            try {
                const formData = new FormData();
                formData.append('action', 'remover_usuario');
                formData.append('idusu', idusu);
                formData.append('id_dependencia', idDep);

                const response = await fetch('controllers/admcadep.php', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const res = await response.json();

                if (res.success) {
                    Swal.fire('Eliminado', res.message, 'success');
                    empleadosSubLista = empleadosSubLista.filter(e => e.idusu != idusu);
                    renderizarTablaEmpleadosSub();
                } else {
                    Swal.fire('Error', res.error || 'No se pudo eliminar el usuario', 'error');
                }
            } catch(e) {
                Swal.fire('Error', 'Error al procesar la eliminación', 'error');
            }
        }
    });
}
*/
</script>
-->
