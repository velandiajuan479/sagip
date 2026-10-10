<?php 
require_once 'controllers/admcpys.php';

// variables
$empleado = null;
$mostrar_busqueda = true;
$dependencia_actual = null;
$viene_de_vadm = isset($_GET['desde']) && $_GET['desde'] == 'vadm';
$mensaje_planta = false;
$empleados_dependencia = [];

// obtiene dependencias si no estan definidas
if (empty($dependencias)) {
    $dependencias = $mpys->obtenerDependencias();
}

// Recupera datos de sesión si provienen de la redirección POST de admcpys.php
if (isset($_SESSION['empleado_seleccionado'])) {
    $empleado = $_SESSION['empleado_seleccionado'];
    $mostrar_busqueda = $_SESSION['mostrar_busqueda'] ?? false;
    $dependencia_actual = $_SESSION['id_dependencia'] ?? null;
    $empleados_dependencia = $_SESSION['empleados_dependencia'] ?? [];
    unset($_SESSION['empleado_seleccionado'], $_SESSION['mostrar_busqueda'], $_SESSION['id_dependencia'], $_SESSION['empleados_dependencia']);
}

if (isset($_GET['documento']) && $_GET['pg'] == '2401') {
    $mostrar_busqueda = false;
    
    $empleado = [
        'ndocusu' => $_GET['documento'],
        'nombre' => $_GET['nombre'] ?? 'N/A'
    ];

    $empleado_completo = $mpys->obtenerDatosCompletosEmpleado($_GET['documento']);
    
    if ($empleado_completo) {
        $empleado = array_merge($empleado, $empleado_completo);
    }

    // Si viene de vadm.php obteniene lista de paz y salvos
    if ($viene_de_vadm && isset($_GET['id_dependencia'])) {
        $dependencia_actual = $_GET['id_dependencia'];
        $empleados_dependencia = $mpys->obtenerListaPazYSalvos($dependencia_actual);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['documento'], $_POST['dependencia'])) {
    $doc = trim($_POST['documento']);

    $empleado = $mpys->obtenerDatosCompletosEmpleado($doc);
    if (!$empleado) {
        $_SESSION['error'] = "No se encontró ningún empleado con ese documento";
        header("Location: home.php?pg=2401");
        exit;
    }

    // Verifica el idper que no sea 34
    if ($empleado['idper'] == 34) {
        $_SESSION['mensaje_planta'] = "Los empleados de planta no tienen permiso para paz y salvos";
        header("Location: home.php?pg=2401");
        exit;
    }

    $dependencia_actual = $_POST['dependencia'];
    $empleados_dependencia = $mpys->obtenerListaPazYSalvos($dependencia_actual);

    $_SESSION['empleado_seleccionado'] = $empleado;
    $_SESSION['mostrar_busqueda'] = false;
    $_SESSION['id_dependencia'] = $dependencia_actual;
    $_SESSION['empleados_dependencia'] = $empleados_dependencia;

    header("Location: home.php?pg=2401");
    exit;
}

// Mensaje de planta
$mensaje_planta = $_SESSION['mensaje_planta'] ?? false;
unset($_SESSION['mensaje_planta']);

// 8. Obtener última observación
$ultima_observacion = isset($empleado['historial']) && !empty($empleado['historial']) ? $empleado['historial'][0] : [];

echo titulo2("<i class='$icono'></i> Paz y Salvos", 2);
$es_reportes = isset($_GET['view']) && $_GET['view'] === 'reportes';
?>

<!-- Barra de navegación rápida -->
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div class="btn-group">
        <a href="home.php?pg=2401" class="btn <?= !$es_reportes ? 'btn-success text-white' : 'btn-outline-success' ?>" style="<?= !$es_reportes ? 'background-color: #00AF00; border: none; font-weight: 500;' : '' ?>">
            <i class="fas fa-file-circle-check me-1"></i> Paz y Salvos
        </a>
        <a href="home.php?pg=2401&view=reportes" class="btn <?= $es_reportes ? 'btn-success text-white' : 'btn-outline-success' ?>" style="<?= $es_reportes ? 'background-color: #00AF00; border: none; font-weight: 500;' : '' ?>">
            <i class="fas fa-id-card me-1"></i> Aprobador de reportes
        </a>
    </div>
    <div>
        <a href="home.php?pg=2403" class="btn btn-outline-secondary">
            <i class="fas fa-building me-1"></i> Administración Dependencias
        </a>
    </div>
</div>

<?php
if ($es_reportes) {
    require_once __DIR__ . '/admvaps.php';
    return;
}
?>


<!-- Mostrar mensaje para empleados de planta -->
<?php if($mensaje_planta): ?>
    <div class="alert alert-warning mt-3">
        <i class="fas fa-exclamation-triangle me-2"></i> 
        <?= is_string($mensaje_planta) ? $mensaje_planta : "Los empleados de planta no tienen permiso para paz y salvos" ?>
    </div>
<?php endif; ?>

<!-- Mostrar datos del empleado -->
<?php if($empleado && $empleado['idper'] != 34): ?>
    <div class="card shadow-sm mb-4 border-success">
        <div class="titulo-verde p-2 mb-2">
            <h5 class="mb-0 text-white">Datos del Empleado</h5>
        </div>
        <div class="card-body bg-white">
            <div class="table-responsive">
                <table class="table table-bordered text-center align-middle" style="width: 100%;">
                    <tbody>
                        <tr>
                            <th style="width: 25%;">Documento</th>
                            <td style="width: 25%;"><?= htmlspecialchars($empleado['ndocusu'] ?? 'N/A') ?></td>
                            <th style="width: 25%;">Teléfono</th>
                            <td style="width: 25%;"><?= htmlspecialchars($empleado['telefono'] ?? 'N/A') ?></td>
                        </tr>
                        <tr>
                            <th>Nombre Completo</th>
                            <td><?= htmlspecialchars($empleado['nombre'] ?? 'N/A') ?></td>
                            <th>Perfil</th>
                            <td>
                                <span class="badge <?= $empleado['idper'] == 34 ? 'bg-success' : ($empleado['idper'] == 35 ? 'bg-warning text-dark' : 'bg-secondary') ?>">
                                    <i class="fas <?= $empleado['idper'] == 34 ? 'fa-user-shield' : ($empleado['idper'] == 35 ? 'fa-user' : 'fa-question') ?> me-1"></i>
                                    <?= htmlspecialchars($empleado['perfil'] ?? 'Sin perfil') ?>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Fecha de Contrato</th>
                            <td><?= htmlspecialchars($empleado['fecha_contrato'] ?? 'No registrada') ?></td>
                            <th>Número de Contrato</th>
                            <td><?= htmlspecialchars($empleado['numero_contrato'] ?? 'No registrado') ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <?php if (!empty($empleado['historial'])): ?>
                <hr>
                <h5 class="text-success " >Historial de Observaciones de Paz y Salvo</h5>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered text-center">
                        <thead class="table-success">
                            <tr>
                                <th>ID Detalle</th>
                                <th>ID Paz</th>
                                <th>Fecha y Hora</th>
                                <th>Observación</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($empleado['historial'] as $detalle): ?>
                                <tr>
                                    <td><?= htmlspecialchars($detalle['iddetalle'] ?? '---') ?></td>
                                    <td><?= htmlspecialchars($detalle['idpaz'] ?? '---') ?></td>
                                    <td><?= htmlspecialchars($detalle['fechayhora'] ?? '---') ?></td>
                                    <td><?= htmlspecialchars($detalle['observacion'] ?? '---') ?></td>
                                    <td>
                                        <?php if (isset($detalle['calificacion'])): ?>
                                            <?= $detalle['calificacion'] == 1 ? 
                                                '<span class="badge bg-success">Aprobado</span>' : 
                                                '<span class="badge bg-danger">Rechazado</span>' ?>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Pendiente</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info mt-3">
                    <i class="fas fa-info-circle me-2"></i> No se encontraron registros históricos de paz y salvo para este empleado.
                </div>
            <?php endif; ?>

            <div class="mt-4">
                <a href="home.php?pg=2403" class="btn btn-none">
                    <i class="fas fa-arrow-left me-1"></i> Volver a Dependencias
                </a>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Lista de empleados en la dependencia -->
<?php if(isset($empleados_dependencia) && !empty($empleados_dependencia)): ?>
<div class="card mt-4 border-success ">
    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center ">
        <h5 class="mb-0">
            <i class="fas fa-users me-2"></i> Lista de Paz y Salvos - Dependencia Actual
        </h5>
        <div>
            <a href="home.php?pg=2403" class="btn btn-sm btn-light">
                <i class="fas fa-arrow-left me-1"></i> Volver
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm table-striped table-hover datatable">
                <thead>
                    <tr>
                        <th>Documento</th>
                        <th>Nombre</th>
                        <th>Última Observación</th>
                        <th>Fecha y Hora</th>
                        <th>Estado</th>
                        <th>N° P&S</th>
                        <th>Descargar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($empleados_dependencia as $emp): ?>
                        <tr>
                            <td><?= htmlspecialchars($emp['ndocusu']) ?></td>
                            <td><?= htmlspecialchars($emp['nombre']) ?></td>
                            <td><?= htmlspecialchars($emp['observacion'] ?? '---') ?></td>
                            <td><?= htmlspecialchars($emp['ultima_fecha'] ?? '---') ?></td>
                            <td>
                                <?php if (isset($emp['calificacion'])): ?>
                                    <?= $emp['calificacion'] == 1 ? 
                                        '<span class="badge bg-success">Aprobado</span>' : 
                                        '<span class="badge bg-danger">Rechazado</span>' ?>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Pendiente</span>
                                <?php endif; ?>
                            </td>
                            <td><?= $emp['total_paz_salvos'] ?? 0 ?></td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <?php if (!empty($emp['observacion'])): ?>
                                    <a href="views/pdfps.php?documento=<?= $emp['ndocusu'] ?>&nombre=<?= urlencode($emp['nombre']) ?>&fecha=<?= urlencode($emp['ultima_fecha'] ?? '') ?>" 
                                       class="btn btn-secondary"
                                       title="Generar PDF" target="_blank" style="background-color: #00AF00; border:none " >
                                       <i class="fas fa-file-pdf"></i>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Bussqueda manual -->
<?php if($mostrar_busqueda && !$viene_de_vadm): ?>
<div class="card shadow-sm mb-4">
    <div class="card-header text-white d-flex justify-content-between align-items-center" style="background-color: #00AF00;">
        <h4 class="mb-0"><i class="fas fa-search me-2"></i>Buscar Empleado</h4>
    </div>
    <div class="card-body">
        <form id="buscar-empleado-form" method="POST">
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="dependencia" class="form-label">Dependencia</label>
                    <select class="form-select" name="dependencia" id="dependencia" required>
                        <option value="" selected disabled>Seleccione una dependencia</option>
                        <?php foreach($dependencias as $dep): ?>
                            <option value="<?= $dep['id_dependencia'] ?>"><?= htmlspecialchars($dep['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="documento" class="form-label">Documento del Empleado</label>
                    <input type="text" class="form-control" name="documento" id="documento" placeholder="Ingrese el documento..." required>
                </div>
            </div>
            <div class="mt-3">
                <button type="submit" class="btn btn-success" style="background-color: #00AF00;">
                    <i class="fas fa-search me-1"></i> Buscar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Resultados de busqueda manual -->
<div class="card shadow-sm" id="resultados-container" style="display: none;">
    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center style="background-color: #00AF00;">
        <h4 class="mb-0"><i class="fas fa-users me-2"></i>Resultados</h4>
        <button id="cerrar-resultados" class="btn btn-sm btn-light">
            <i class="fas fa-times"></i> Cerrar
        </button>
    </div>
    <div class="card-body" id="resultados-content">
    </div>
</div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    <?php if(!empty($empleados_dependencia)): ?>
        $('.datatable').DataTable({
            responsive: true,
            language: {
                url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json'
            },
            dom: '<"top"f>rt<"bottom"lip><"clear">',
            pageLength: 10
        });
    <?php endif; ?>

    <?php if($mostrar_busqueda && !$viene_de_vadm): ?>
    const buscarForm = document.getElementById('buscar-empleado-form');
    const resultadosContainer = document.getElementById('resultados-container');
    const resultadosContent = document.getElementById('resultados-content');
    const cerrarBtn = document.getElementById('cerrar-resultados');

    cerrarBtn.addEventListener('click', function() {
        resultadosContainer.style.display = 'none';
    });

    buscarForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const dependenciaId = document.getElementById('dependencia').value;
        const documento = document.getElementById('documento').value.trim();
        
        if (!dependenciaId || !documento) return;
        
        resultadosContent.innerHTML = `
            <div class="text-center py-4">
                <i class="fas fa-spinner fa-spin fa-2x text-muted"></i>
                <p class="mt-2">Buscando empleados...</p>
            </div>`;
        
        resultadosContainer.style.display = 'block';
        
        fetch(`controllers/admcpys.php?action=busqueda_manual&id_dependencia=${dependenciaId}&documento=${encodeURIComponent(documento)}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                throw new Error(data.error || 'Error en la búsqueda');
            }
            
            if (data.count === 0) {
                resultadosContent.innerHTML = `
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        No se encontraron empleados con los criterios de búsqueda
                    </div>`;
                return;
            }
            
            // resultados
            let html = `
                <div class="table-responsive">
                    <table class="table table-sm table-striped">
                        <thead>
                            <tr>
                                <th>Documento</th>
                                <th>Nombre</th>
                                <th>Última Observación</th>
                                <th>Fecha y Hora</th>
                                <th>Estado</th>
                                <th>N° P&S</th>
                                <th>Descargar</th>
                            </tr>
                        </thead>
                        <tbody>`;
            
            data.data.forEach(empleado => {
                let estado = '---';
                if (empleado.calificacion == 1) {
                    estado = '<span class="badge bg-success">Aprobado</span>';
                } else if (empleado.calificacion == 0) {
                    estado = '<span class="badge bg-danger">Rechazado</span>';
                } else if (empleado.calificacion === null) {
                    estado = '<span class="badge bg-secondary">Pendiente</span>';
                }
                
                let fechaFormateada = empleado.fechayhora || empleado.ultima_fecha || '---';
                
                html += `
                    <tr>
                        <td>${empleado.ndocusu || '---'}</td>
                        <td>${empleado.nombre || '---'}</td>
                        <td>${empleado.observacion || '---'}</td>
                        <td>${fechaFormateada}</td>
                        <td>${estado}</td>
                        <td>${empleado.total_paz_salvos || 0}</td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                ${empleado.observacion ? `
                                <a href="views/pdfps.php?documento=${empleado.ndocusu}&nombre=${encodeURIComponent(empleado.nombre)}&fecha=${encodeURIComponent(fechaFormateada)}" 
                                   class="btn btn-secondary"
                                   title="Generar PDF" target="_blank" style="background-color: #00AF00; border:none">
                                   <i class="fas fa-file-pdf"></i>
                                </a>` : ''}
                            </div>
                        </td>
                    </tr>`;
            });
            
            html += `</tbody></table></div>`;
            resultadosContent.innerHTML = html;
        })
        .catch(error => {
            resultadosContent.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    ${error.message || 'Error al realizar la búsqueda'}
                </div>`;
        });
    });
    <?php endif; ?>
});
</script>

<style>
    .empleado-card {
        transition: all 0.3s ease;
        border-left: 4px solid transparent;
    }
    .empleado-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        border-left-color: #00AF00;
    }
    .empleado-seleccionar-btn {
        transition: all 0.2s ease;
    }
    .empleado-seleccionar-btn:hover {
        transform: scale(1.05);
    }
    #resultados-container {
        max-height: 500px;
        overflow-y: auto;
    }
    .badge.bg-primary {
        background-color: #00AF00 !important;
    }
    .nombre-link {
        color: #00AF00;
        text-decoration: none;
        font-weight: 500;
        transition: all 0.2s ease;
    }
    .nombre-link:hover {
        color: #00AF00;
        text-decoration: underline;
    }

    .empleado-card {
        transition: all 0.3s ease;
        border-left: 4px solid transparent;
    }
    .empleado-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        border-left-color: #00AF00 ;
    }
    .empleado-seleccionar-btn {
        transition: all 0.2s ease;
    }
    .empleado-seleccionar-btn:hover {
        transform: scale(1.05);
    }
    #resultados-container {
        max-height: 500px;
        overflow-y: auto;
    }
    .badge.bg-primary {
        background-color: #00AF00 !important;
    }
    .nombre-link {
        color: #00AF00;
        text-decoration: none;
        font-weight: 500;
        transition: all 0.2s ease;
    }
    .nombre-link:hover {
        color:  #00AF00 ;
        text-decoration: underline;
    }
    .titulo-verde {
    background-color: #00AF00;
    color: white;
    border-radius: 4px;
    }
    .pdf-button {
        display: inline-block;
        padding: 5px 10px;
        color: #00AF00;
        text-decoration: none;
        border-radius: 3px;
        font-size: 10px;
    }

    .pdf-button:hover {
        color:rgb(13, 95, 13);
    }

</style>