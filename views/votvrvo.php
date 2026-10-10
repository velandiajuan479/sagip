<?php
// Vista Oficial: Resultados Representantes (ID 1208)
// Responsable: Yeison Stiven Molina Balceras
// Cronograma SAGI: views/votvrvo.php | Controlador: controllers/votcrvo.php | Modelo: models/votmrvo.php

require_once 'controllers/votcrvo.php';

// Cálculo de estadísticas y ordenamiento de candidatos
$candidatos = [];
$totVotos = 0;
$lider = null;

if (isset($dat) && is_array($dat)) {
    foreach ($dat as $d) {
        $resNvo = isset($votmrvo) ? $votmrvo->nvoCan($d['idusu']) : 0;
        $nvo = isset($resNvo[0]['nvo']) ? (int)$resNvo[0]['nvo'] : 0;
        $d['votos_obtenidos'] = $nvo;
        $candidatos[] = $d;
        $totVotos += $nvo;
    }

    // Orden descendente estricto por número de votos
    usort($candidatos, function($a, $b) {
        return $b['votos_obtenidos'] <=> $a['votos_obtenidos'];
    });

    if (!empty($candidatos)) {
        $lider = $candidatos[0];
    }
}
$totCandidatos = count($candidatos);
?>

<div class="conte">
    <?php echo titulo2("<i class='" . (isset($icono) ? $icono : 'fa-chart-pie') . "'></i> Resultados Electorales - Representantes", 2); ?>

    <!-- Tarjetas de Métricas Ejecutivas (KPIs) -->
    <div class="row my-4 g-4">
        <!-- KPI 1: Total Votos Escrutados -->
        <div class="col-12 col-md-4">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #3e4a3d;">Total Escrutado</span>
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
                        <i class="fa-solid fa-arrow-trend-up" style="font-size: 12px;"></i> Escrutinio Oficial
                    </span>
                    <span style="font-size: 11px; color: #3e4a3d;">Censo Procesado</span>
                </div>
            </div>
        </div>

        <!-- KPI 2: Candidato Líder / Representante Electo -->
        <div class="col-12 col-md-4">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div style="max-width: calc(100% - 55px);">
                        <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #006b29;">Candidato Líder</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold text-truncate d-block" style="font-size: 20px; line-height: 1.2; color: #006b29;" title="<?= ($lider && $lider['votos_obtenidos'] > 0) ? htmlspecialchars($lider['nomusu']) : 'Sin votos aún'; ?>">
                                <?= ($lider && $lider['votos_obtenidos'] > 0) ? htmlspecialchars($lider['nomusu']) : 'Sin votos aún'; ?>
                            </span>
                        </div>
                        <?php if ($lider && $lider['votos_obtenidos'] > 0): ?>
                            <div class="small fw-semibold text-muted mt-1">
                                <?= number_format($lider['votos_obtenidos'], 0, ',', '.'); ?> votos (<?= ($totVotos > 0) ? round(($lider['votos_obtenidos'] / $totVotos) * 100, 1) : 0; ?>%)
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="kpi-icon-box" style="background-color: #7afd88; color: #006b24;">
                        <i class="fa-solid fa-crown fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="fw-semibold d-inline-flex align-items-center gap-2" style="font-size: 11px; color: #006b29;">
                        <span class="pulse-dot"></span> 1° Lugar / Electo
                    </span>
                    <span style="font-size: 11px; color: #3e4a3d;">Representante</span>
                </div>
            </div>
        </div>

        <!-- KPI 3: Candidatos Inscritos -->
        <div class="col-12 col-md-4">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #3c627f;">Candidatos Inscritos</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #0b1c30;"><?= $totCandidatos; ?></span>
                            <span style="font-size: 13px; color: #3e4a3d;">en contienda</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box" style="background-color: #e5eeff; color: #3c627f;">
                        <i class="fa-solid fa-users fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="d-inline-flex align-items-center gap-1" style="font-size: 11px; color: #3e4a3d;">
                        <i class="fa-solid fa-id-card-clip" style="font-size: 12px;"></i> Tarjetón Activo
                    </span>
                    <span class="fw-semibold" style="font-size: 11px; color: #3c627f;">Jornada / Centro</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Toolbar de Filtros y Acciones Institucionales -->
    <div class="filter-toolbar-container">
        <form class="w-100" action="home.php?pg=<?= $pg ?>" method="POST" id="formFiltroRep">
            <div class="row align-items-end g-3">
                <div class="col-12 col-md-4">
                    <label for="idfic" class="form-label fw-bold text-secondary mb-1" style="font-size: 0.85rem;">
                        <i class="fa-solid fa-calendar-day me-1 text-success"></i> Jornada
                    </label>
                    <select name="fidjor" id="idfic" class="form-select form--input-default shadow-sm" onchange="this.form.submit();">
                        <?php
                        if (isset($djor) && $djor) {
                            foreach ($djor as $dj) {
                        ?>
                                <option value="<?= $dj['idval']; ?>" <?php if (isset($fidjor) && $fidjor == $dj['idval']) echo " selected "; ?>><?= $dj['nomval']; ?></option>
                        <?php }
                        } ?>
                    </select>
                </div>
                <div class="col-12 col-md-5">
                    <label for="idcen" class="form-label fw-bold text-secondary mb-1" style="font-size: 0.85rem;">
                        <i class="fa-solid fa-building-columns me-1 text-success"></i> Centro de Formación
                    </label>
                    <select name="fidcen" id="idcen" class="form-select form--input-default shadow-sm" onchange="this.form.submit();">
                        <?php
                        if (isset($dcen) && $dcen) {
                            foreach ($dcen as $dj) {
                        ?>
                                <option value="<?= $dj['idcen']; ?>" <?php if (isset($fidcen) && $fidcen == $dj['idcen']) echo " selected "; ?>><?= $dj['nomcen']; ?></option>
                        <?php
                            }
                        }
                        ?>
                    </select>
                </div>
                <div class="col-12 col-md-3 d-flex justify-content-md-end">
                    <a href="views/votract.php?pdf=ok&tipo=representante&fidcen=<?= isset($fidcen) ? $fidcen : ''; ?>&fidjor=<?= isset($fidjor) ? $fidjor : ''; ?>" target="_blank" class="btn btn-outline-success fw-bold w-100 shadow-sm py-2" title="Imprimir Acta Oficial de Escrutinio">
                        <i class="fas fa-file-pdf me-1"></i> Imprimir Acta
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Contenedor en Tarjeta para la Tabla de Resultados -->
    <div class="card shadow-sm border-0 rounded-3 overflow-hidden mb-4">
        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
            <h6 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                <i class="fa-solid fa-square-poll-vertical text-success"></i> Cuadro Oficial de Resultados
            </h6>
            <span class="badge bg-light text-dark border">
                Total escrutado: <?= number_format($totVotos, 0, ',', '.'); ?> votos
            </span>
        </div>
        <div class="card-body p-3">
            <div class="table-responsive">
                <table id="tblResultadosRep" class="table table-striped table-hover align-middle w-100">
                    <thead class="table-dark">
                        <tr>
                            <th style="text-align: center; width: 12%;">Posición</th>
                            <th style="text-align: center; width: 10%;">Foto</th>
                            <th style="width: 38%;">Candidato a Representante</th>
                            <th style="width: 25%;">Votos Obtenidos</th>
                            <th style="text-align: center; width: 15%;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (!empty($candidatos)) {
                            foreach ($candidatos as $puesto => $d) {
                                $foto = (!empty($d['fotcan']) && file_exists($d['fotcan'])) ? $d['fotcan'] : "img/user.jpg";
                                $esGanador = ($puesto === 0 && $d['votos_obtenidos'] > 0);
                                $porcentaje = ($totVotos > 0) ? round(($d['votos_obtenidos'] / $totVotos) * 100, 1) : 0;
                        ?>
                                <tr class="<?= $esGanador ? 'table-success' : ''; ?>">
                                    <td class="text-center" data-order="<?= $puesto + 1; ?>" data-sort="<?= $puesto + 1; ?>">
                                        <?php if ($esGanador): ?>
                                            <span class="badge bg-warning text-dark fw-bold px-2 py-1 shadow-sm">
                                                <i class="fa-solid fa-crown me-1 text-dark"></i>1° Electo
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary px-2 py-1">
                                                <?= ($puesto + 1); ?>° Lugar
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <img src="<?= $foto; ?>" alt="<?= htmlspecialchars($d['nomusu']); ?>" class="shadow-sm" style="width: 65px; height: 65px; object-fit: cover; border-radius: 50%; border: 3px solid <?= $esGanador ? '#28a745' : '#dee2e6'; ?>;">
                                    </td>
                                    <td>
                                        <div class="fw-bold text-uppercase fs-6 text-dark">
                                            <?= htmlspecialchars($d['nomusu']); ?>
                                        </div>
                                        <div class="text-muted small mt-1">
                                            <span><strong>Doc:</strong> <?= htmlspecialchars($d['ndocusu']); ?></span>
                                            <?php if (!empty($d['noca'])): ?>
                                                <span class="ms-2 badge bg-light text-dark border">Tarjetón #<?= htmlspecialchars($d['noca']); ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <?php if (!empty($d['nomfic'])): ?>
                                            <div class="text-muted small">
                                                <i class="fa-solid fa-graduation-cap me-1"></i><?= htmlspecialchars($d['nomfic']); ?> (Ficha <?= htmlspecialchars($d['idfic']); ?>)
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="fs-5 fw-bold <?= $esGanador ? 'text-success' : 'text-dark'; ?>">
                                                <?= number_format($d['votos_obtenidos'], 0, ',', '.'); ?> <span class="fs-6 text-muted fw-normal">votos</span>
                                            </span>
                                            <span class="fw-bold <?= $esGanador ? 'text-success' : 'text-muted'; ?> small">
                                                <?= $porcentaje; ?>%
                                            </span>
                                        </div>
                                        <div class="progress" style="height: 8px; border-radius: 4px; background-color: #e9ecef;">
                                            <div class="progress-bar <?= $esGanador ? 'bg-success' : 'bg-primary'; ?>" role="progressbar" style="width: <?= $porcentaje; ?>%;" aria-valuenow="<?= $porcentaje; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <a href="home.php?pg=1204&idusu=<?= $d['idusu']; ?>" class="btn btn-sm btn-outline-success fw-semibold shadow-sm" title="Visualizar Propuestas de Campaña">
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
                                    <p class="mb-0 fw-semibold">No hay candidatos registrados para la jornada y centro seleccionados.</p>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                    <?php if ($totVotos > 0): ?>
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="3" class="text-end fw-bold">Total Votos Escrutados:</th>
                            <th class="fw-bold text-success fs-6"><?= number_format($totVotos, 0, ',', '.'); ?> votos</th>
                            <th></th>
                        </tr>
                    </tfoot>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    let h1 = document.querySelector(".title-page");
    if (h1) {
        document.title = h1.textContent.trim();
    }
});

$(document).ready(function() {
    if ($.fn.DataTable.isDataTable('#tblResultadosRep')) {
        $('#tblResultadosRep').DataTable().destroy();
    }
    $('#tblResultadosRep').DataTable({
        "language": {
            "sProcessing": "Procesando...",
            "sLengthMenu": "Mostrar _MENU_ candidatos",
            "sZeroRecords": "No se encontraron candidatos",
            "sEmptyTable": "Ningún candidato disponible en esta tabla",
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
</script>
