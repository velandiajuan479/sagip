<?php
// Vista Oficial: Resultados Vocero (ID 1213)
// Responsable: Yeison Stiven Molina Balceras
// Cronograma SAGI: views/votvrcv.php | Controlador: controllers/votcrcv.php | Modelo: models/votmrcv.php

require_once 'controllers/votcrcv.php';

// Cálculo de estadísticas y ordenamiento de candidatos para la ficha seleccionada
$candidatos = [];
$totVotos = 0;
$voceroElecto = null;
$voceroSuplente = null;

if (!empty($dat) && is_array($dat)) {
    foreach ($dat as $d) {
        $nvo = isset($d['total_votos']) ? (int)$d['total_votos'] : 0;
        if ($nvo === 0 && isset($votmrcv)) {
            $nvo_calc = $votmrcv->nvoCan($d['idusu']);
            if (!empty($nvo_calc[0]['nvo'])) {
                $nvo = (int)$nvo_calc[0]['nvo'];
            }
        }
        $d['votos_obtenidos'] = $nvo;
        $totVotos += $nvo;
        $candidatos[] = $d;
    }

    // Orden descendente estricto por votos obtenidos
    usort($candidatos, function($a, $b) {
        return $b['votos_obtenidos'] <=> $a['votos_obtenidos'];
    });

    if (!empty($candidatos)) {
        $voceroElecto = $candidatos[0];
    }
    if (count($candidatos) > 1) {
        $voceroSuplente = $candidatos[1];
    }
}
$totCandidatos = count($candidatos);
?>

<div class="conte">
    <?php echo titulo2("<i class='" . (isset($icono) ? $icono : 'fa-users') . "'></i> Resultados Electorales - Voceros", 2); ?>

    <!-- Toolbar Institucional de Filtros y Acciones -->
    <div class="filter-toolbar-container">
        <form action="home.php?pg=<?= $pg ?>" method="POST" id="fichaForm" class="w-100">
            <div class="row align-items-end g-3">
                <div class="col-12 col-md-3">
                    <label for="idjor" class="form-label fw-bold text-secondary mb-1" style="font-size: 0.85rem;">
                        <i class="fa-solid fa-clock me-1 text-success"></i> Jornada
                    </label>
                    <select name="fidjor" id="idjor" class="form-select form--input-default shadow-sm" onchange="document.getElementById('idfic').value=''; this.form.submit();">
                        <option value="">-- Todas las jornadas --</option>
                        <?php if (isset($jornadas) && $jornadas): ?>
                            <?php foreach ($jornadas as $jor): ?>
                                <option value="<?= $jor['idval']; ?>" <?= (isset($fidjor) && $fidjor == $jor['idval']) ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($jor['nomval']); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="col-12 col-md-4">
                    <label for="idfic" class="form-label fw-bold text-secondary mb-1" style="font-size: 0.85rem;">
                        <i class="fa-solid fa-keyboard me-1 text-success"></i> Número de Ficha de Formación
                    </label>
                    <input type="number" name="fidfic" id="idfic" class="form-control form--input-default shadow-sm"
                           placeholder="Digite número de ficha (Ej: 3235291)"
                           value="<?= htmlspecialchars($fidfic ?? ''); ?>" required list="listadoFichas">
                    <datalist id="listadoFichas">
                        <?php if (isset($fichas) && $fichas): ?>
                            <?php foreach ($fichas as $f): ?>
                                <option value="<?= $f['idfic']; ?>"><?= htmlspecialchars($f['idfic']); ?> - <?= htmlspecialchars($f['nomfic']); ?> (<?= htmlspecialchars($f['nomval']); ?>)</option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </datalist>
                </div>
                <div class="col-12 col-md-2">
                    <button type="submit" class="btn btn-success fw-bold w-100 shadow-sm py-2" title="Buscar resultados de la ficha">
                        <i class="fa-solid fa-magnifying-glass me-1"></i> Consultar
                    </button>
                </div>
                <div class="col-12 col-md-3 d-flex justify-content-md-end gap-2">
                    <button type="button" class="btn btn-outline-success fw-bold w-100 shadow-sm py-2" onclick="descargarPDFVocero()" title="Imprimir Acta Oficial de Vocero">
                        <i class="fas fa-file-pdf me-1"></i> Imprimir Acta
                    </button>
                    <?php if (!empty($fidfic)): ?>
                        <a href="home.php?pg=<?= $pg ?>" class="btn btn-outline-secondary shadow-sm py-2 px-3" title="Limpiar filtro">
                            <i class="fa-solid fa-rotate-left"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>

    <?php if (!empty($fidfic) && !empty($fichaActual)): ?>
        <!-- Tarjetas de Métricas Ejecutivas (KPIs) de la Ficha -->
        <div class="row my-4 g-4">
            <!-- KPI 1: Total Votos Escrutados -->
            <div class="col-12 col-md-4">
                <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #3e4a3d;">Total Escrutado Ficha</span>
                            <div class="d-flex align-items-baseline gap-2 mt-2">
                                <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #0b1c30;"><?= number_format($totVotos, 0, ',', '.'); ?></span>
                                <span style="font-size: 13px; color: #3e4a3d;">votos</span>
                            </div>
                        </div>
                        <div class="kpi-icon-box" style="background-color: #b5dcfe; color: #3c627f;">
                            <i class="fa-solid fa-check-to-slot fs-4"></i>
                        </div>
                    </div>
                    <div class="kpi-footer-strip">
                        <span class="fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 11px; color: #3c627f;">
                            <i class="fa-solid fa-arrow-trend-up" style="font-size: 12px;"></i> Escrutinio Ficha <?= htmlspecialchars($fichaActual['idfic']); ?>
                        </span>
                        <span style="font-size: 11px; color: #3e4a3d;">Censo Procesado</span>
                    </div>
                </div>
            </div>

            <!-- KPI 2: Vocero Electo -->
            <div class="col-12 col-md-4">
                <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                    <div class="d-flex align-items-center justify-content-between">
                        <div style="max-width: calc(100% - 55px);">
                            <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #006b29;">Vocero Electo</span>
                            <div class="d-flex align-items-baseline gap-2 mt-2">
                                <span class="fw-bold text-truncate d-block" style="font-size: 20px; line-height: 1.2; color: #006b29;" title="<?= ($voceroElecto && $voceroElecto['votos_obtenidos'] > 0) ? htmlspecialchars($voceroElecto['nomusu']) : 'Sin votos aún'; ?>">
                                    <?= ($voceroElecto && $voceroElecto['votos_obtenidos'] > 0) ? htmlspecialchars($voceroElecto['nomusu']) : 'Sin votos aún'; ?>
                                </span>
                            </div>
                            <?php if ($voceroElecto && $voceroElecto['votos_obtenidos'] > 0): ?>
                                <div class="small fw-semibold text-muted mt-1">
                                    <?= number_format($voceroElecto['votos_obtenidos'], 0, ',', '.'); ?> votos (<?= ($totVotos > 0) ? round(($voceroElecto['votos_obtenidos'] / $totVotos) * 100, 1) : 0; ?>%)
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="kpi-icon-box" style="background-color: #7afd88; color: #006b24;">
                            <i class="fa-solid fa-crown fs-4"></i>
                        </div>
                    </div>
                    <div class="kpi-footer-strip">
                        <span class="fw-semibold d-inline-flex align-items-center gap-2" style="font-size: 11px; color: #006b29;">
                            <span class="pulse-dot"></span> 1° Lugar / Vocero
                        </span>
                        <span style="font-size: 11px; color: #3e4a3d;">Representante Ficha</span>
                    </div>
                </div>
            </div>

            <!-- KPI 3: Candidatos Postulados -->
            <div class="col-12 col-md-4">
                <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #3c627f;">Candidatos Postulados</span>
                            <div class="d-flex align-items-baseline gap-2 mt-2">
                                <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #0b1c30;"><?= $totCandidatos; ?></span>
                                <span style="font-size: 13px; color: #3e4a3d;">aprendices</span>
                            </div>
                        </div>
                        <div class="kpi-icon-box" style="background-color: #e5eeff; color: #3c627f;">
                            <i class="fa-solid fa-users fs-4"></i>
                        </div>
                    </div>
                    <div class="kpi-footer-strip">
                        <span class="d-inline-flex align-items-center gap-1" style="font-size: 11px; color: #3e4a3d;">
                            <i class="fa-solid fa-id-card-clip" style="font-size: 12px;"></i> Vocería Ficha
                        </span>
                        <span class="fw-semibold" style="font-size: 11px; color: #3c627f;">En Contienda</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Banner de Identificación de la Ficha Seleccionada -->
        <div class="card mb-4 border-0 shadow-sm rounded-3 overflow-hidden">
            <div class="card-header py-3 d-flex flex-wrap justify-content-between align-items-center" style="background-color: #006b29; color: white;">
                <div>
                    <h5 class="mb-1 fw-bold d-flex align-items-center gap-2">
                        <i class="fa-solid fa-graduation-cap"></i> Ficha <?= htmlspecialchars($fichaActual['idfic']); ?> - <?= htmlspecialchars($fichaActual['nomfic']); ?>
                    </h5>
                    <div class="d-flex flex-wrap gap-3 small text-white-50">
                        <span><i class="fa-solid fa-clock me-1 text-white"></i> <strong>Jornada:</strong> <?= htmlspecialchars($fichaActual['nomval'] ?? 'No especificada'); ?></span>
                        <?php if (!empty($fichaActual['nomins'])): ?>
                            <span><i class="fa-solid fa-chalkboard-user me-1 text-white"></i> <strong>Instructor Líder:</strong> <?= htmlspecialchars($fichaActual['nomins']); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="mt-2 mt-md-0">
                    <button type="button" class="btn btn-light text-success fw-bold shadow-sm d-flex align-items-center gap-2" onclick="descargarPDFVocero()" title="Emitir PDF Oficial de Voceros">
                        <i class="fas fa-print"></i> Emitir PDF Vocero
                    </button>
                </div>
            </div>
        </div>

        <!-- Tabla de Candidatos a Vocero en Tarjeta -->
        <div class="card shadow-sm border-0 rounded-3 overflow-hidden mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                    <i class="fa-solid fa-square-poll-vertical text-success"></i> Cuadro Oficial de Escrutinio de Vocería
                </h6>
                <span class="badge bg-light text-dark border">
                    Total escrutado: <?= number_format($totVotos, 0, ',', '.'); ?> votos
                </span>
            </div>
            <div class="card-body p-3">
                <div class="table-responsive">
                    <table id="tblResultadosVoc" class="table table-striped table-hover align-middle w-100">
                        <thead class="table-dark">
                            <tr>
                                <th style="text-align: center; width: 14%;">Posición</th>
                                <th style="text-align: center; width: 10%;">Foto</th>
                                <th style="width: 36%;">Candidato a Vocero</th>
                                <th style="width: 25%;">Votos Obtenidos</th>
                                <th style="text-align: center; width: 15%;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (!empty($candidatos)) {
                                foreach ($candidatos as $puesto => $d) {
                                    $foto = (!empty($d['fotcan']) && file_exists($d['fotcan'])) ? $d['fotcan'] : "img/user.jpg";
                                    $nvo = $d['votos_obtenidos'];
                                    $porcentaje = ($totVotos > 0) ? round(($nvo / $totVotos) * 100, 1) : 0;

                                    $esVocero = ($puesto === 0 && $nvo > 0);
                                    $esSuplente = ($puesto === 1 && $nvo > 0);

                                    $row_class = "";
                                    if ($esVocero) {
                                        $row_class = "table-success";
                                    } elseif ($esSuplente) {
                                        $row_class = "table-info";
                                    }
                            ?>
                                    <tr class="<?= $row_class; ?>">
                                        <td class="text-center" data-order="<?= $puesto + 1; ?>" data-sort="<?= $puesto + 1; ?>">
                                            <?php if ($esVocero): ?>
                                                <span class="badge bg-success fw-bold px-2 py-1 shadow-sm">
                                                    <i class="fa-solid fa-crown me-1"></i>1° Vocero
                                                </span>
                                            <?php elseif ($esSuplente): ?>
                                                <span class="badge bg-info text-dark fw-bold px-2 py-1 shadow-sm">
                                                    <i class="fa-solid fa-award me-1"></i>2° Suplente
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary px-2 py-1">
                                                    <?= ($puesto + 1); ?>° Lugar
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <img src="<?= $foto; ?>" alt="<?= htmlspecialchars($d['nomusu']); ?>" class="shadow-sm" style="width: 65px; height: 65px; object-fit: cover; border-radius: 50%; border: 3px solid <?= $esVocero ? '#28a745' : ($esSuplente ? '#0dcaf0' : '#dee2e6'); ?>;">
                                        </td>
                                        <td>
                                            <div class="fw-bold text-uppercase fs-6 text-dark">
                                                <?= htmlspecialchars($d['nomusu']); ?>
                                            </div>
                                            <div class="text-muted small mt-1">
                                                <span><strong>Documento:</strong> <?= htmlspecialchars($d['tipodoc'] ?? 'CC'); ?> <?= htmlspecialchars($d['ndocusu']); ?></span>
                                                <?php if (!empty($d['emausu'])): ?>
                                                    <span class="d-block mt-1"><i class="fa-regular fa-envelope me-1"></i><?= htmlspecialchars($d['emausu']); ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <span class="fs-5 fw-bold <?= $esVocero ? 'text-success' : 'text-dark'; ?>">
                                                    <?= number_format($nvo, 0, ',', '.'); ?> <span class="fs-6 text-muted fw-normal">votos</span>
                                                </span>
                                                <span class="fw-bold <?= $esVocero ? 'text-success' : 'text-muted'; ?> small">
                                                    <?= $porcentaje; ?>%
                                                </span>
                                            </div>
                                            <div class="progress" style="height: 8px; border-radius: 4px; background-color: #e9ecef;">
                                                <div class="progress-bar <?= $esVocero ? 'bg-success' : ($esSuplente ? 'bg-info' : 'bg-primary'); ?>" role="progressbar" style="width: <?= $porcentaje; ?>%;" aria-valuenow="<?= $porcentaje; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <a href="home.php?pg=1204&idusu=<?= $d['idusu']; ?>" class="btn btn-sm btn-outline-success fw-semibold shadow-sm" title="Ver Propuestas de Campaña" target="_blank">
                                                <i class="fa-solid fa-eye me-1"></i> Propuesta
                                            </a>
                                        </td>
                                    </tr>
                            <?php
                                }
                            } else {
                            ?>
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <i class="fa-solid fa-users-slash fa-3x mb-3 text-secondary opacity-50"></i>
                                        <p class="mb-0 fw-semibold">No hay candidatos a vocero inscritos para esta ficha de formación.</p>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                        <?php if ($totVotos > 0): ?>
                        <tfoot class="table-light">
                            <tr>
                                <th colspan="3" class="text-end fw-bold">Total Votos Escrutados en Ficha:</th>
                                <th class="fw-bold text-success fs-6"><?= number_format($totVotos, 0, ',', '.'); ?> votos</th>
                                <th></th>
                            </tr>
                        </tfoot>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- Estado Inicial: Ficha no seleccionada -->
        <div class="card shadow-sm border-0 rounded-3 text-center py-5 px-4 mb-4 bg-white">
            <div class="mb-3">
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width: 72px; height: 72px; background-color: #e5eeff; color: #006b29;">
                    <i class="fa-solid fa-filter fa-2x"></i>
                </div>
            </div>
            <h5 class="fw-bold text-dark mb-2">Selección de Ficha Requerida</h5>
            <p class="text-muted mx-auto mb-0" style="max-width: 520px;">
                Por favor, digite el número de ficha de formación o selecciónela de las sugerencias predictivas para visualizar los resultados de la elección de voceros y generar el acta oficial de escrutinio.
            </p>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    let h1 = document.querySelector(".title-page");
    if (h1) {
        document.title = h1.textContent.trim();
    }
});

$(document).ready(function () {
    if ($.fn.DataTable.isDataTable('#tblResultadosVoc')) {
        $('#tblResultadosVoc').DataTable().destroy();
    }
    $('#tblResultadosVoc').DataTable({
        "language": {
            "sProcessing": "Procesando...",
            "sLengthMenu": "Mostrar _MENU_ candidatos",
            "sZeroRecords": "No se encontraron candidatos a vocero",
            "sEmptyTable": "Ningún candidato disponible para esta ficha",
            "sInfo": "Mostrando _START_ a _END_ de _TOTAL_ candidatos",
            "sInfoEmpty": "Mostrando 0 a 0 de 0 candidatos",
            "sInfoFiltered": "(filtrado de un total de _MAX_ candidatos)",
            "sSearch": "Buscar candidato:",
            "oPaginate": {
                "sFirst": "Primero",
                "sLast": "Último",
                "sNext": "Siguiente",
                "sPrevious": "Anterior"
            }
        },
        "order": [[0, "asc"]],
        "pageLength": 10,
        "responsive": true,
        "dom": "<'row mb-2'<'col-sm-6'l><'col-sm-6'f>>" +
               "<'row'<'col-sm-12'tr>>" +
               "<'row mt-2'<'col-sm-5'i><'col-sm-7'p>>"
    });
});

function descargarPDFVocero() {
    var idfic = document.getElementById('idfic').value;
    if (idfic) {
        window.open('views/votract.php?pdf=ok&tipo=vocero&idfic=' + idfic, '_blank');
    } else {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'warning',
                title: 'Atención',
                text: 'Por favor digite o seleccione una ficha primero.',
                confirmButtonColor: '#006b29'
            });
        } else {
            alert('Por favor digite o seleccione una ficha primero.');
        }
    }
}
</script>
