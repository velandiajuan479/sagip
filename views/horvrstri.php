<?php
require_once 'controllers/horcrstri.php';

$iconoVista = isset($icono) && !empty($icono) ? $icono : 'fa-solid fa-square-poll-horizontal';
echo titulo2("<i class='" . $iconoVista . "'></i> Resultados por Trimestre", 2);
require_once("views/vscopehya.php");

$idfic = $idfic ?? null;
$trimestre = $trimestre ?? null;
$dtFichas = $dtFichas ?? [];
$trimestresList = $trimestresList ?? [];
$dtPrg = $dtPrg ?? [];
$dtComRes = $dtComRes ?? [];
$kpi = $kpi ?? [
    'total_competencias' => 0,
    'total_resultados' => 0,
    'total_instructores' => 0,
    'total_horas' => 0,
    'total_trimestres' => 0,
    'trimestre_actual' => 'Todos'
];
?>

<!-- Dashboard de Métricas / Indicadores Superiores -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card border-0 shadow-sm rounded-3 h-100 text-center py-2" style="background: linear-gradient(135deg, #00af00 0%, #008700 100%); color: #fff;">
            <div class="card-body p-2">
                <i class="fa-solid fa-id-badge fa-2x mb-1" style="opacity: 0.9;"></i>
                <div class="text-uppercase fw-semibold" style="font-size: 1.1rem; letter-spacing: 0.5px;">Ficha</div>
                <h4 class="mb-0 text-white fw-bold" style="font-size: 1.8rem;"><?= $idfic ? htmlspecialchars($idfic) : 'N/A' ?></h4>
                <small style="font-size: 0.95rem; opacity: 0.9;"><?= !empty($dtPrg[0]['nomfic']) ? htmlspecialchars(substr($dtPrg[0]['nomfic'], 0, 16)) . '...' : 'Seleccione ficha' ?></small>
            </div>
        </div>
    </div>

    <div class="col-6 col-md-4 col-xl-2">
        <div class="card border-0 shadow-sm rounded-3 h-100 text-center py-2" style="background: #ffffff; border-left: 4px solid #198754 !important;">
            <div class="card-body p-2">
                <i class="fa-solid fa-calendar-week fa-2x mb-1 text-success"></i>
                <div class="text-uppercase fw-semibold text-muted" style="font-size: 1.1rem;">Trimestre</div>
                <h3 class="mb-0 fw-bold text-dark" style="font-size: 2.2rem;"><?= $trimestre ? 'Trim. ' . $trimestre : 'Todos' ?></h3>
                <small class="text-muted" style="font-size: 1rem;">Total: <?= $kpi['total_trimestres'] ?> trimestres</small>
            </div>
        </div>
    </div>

    <div class="col-6 col-md-4 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3 h-100 text-center py-2" style="background: #ffffff; border-left: 4px solid #0d6efd !important;">
            <div class="card-body p-2">
                <i class="fa-solid fa-book-open fa-2x mb-1 text-primary"></i>
                <div class="text-uppercase fw-semibold text-muted" style="font-size: 1.1rem;">Competencias</div>
                <h3 class="mb-0 fw-bold text-dark" style="font-size: 2.2rem;"><?= $kpi['total_competencias'] ?></h3>
                <small class="text-muted" style="font-size: 1rem;">En el trimestre / programa</small>
            </div>
        </div>
    </div>

    <div class="col-6 col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3 h-100 text-center py-2" style="background: #ffffff; border-left: 4px solid #ffc107 !important;">
            <div class="card-body p-2">
                <i class="fa-solid fa-list-check fa-2x mb-1 text-warning"></i>
                <div class="text-uppercase fw-semibold text-muted" style="font-size: 1.1rem;">Resultados</div>
                <h3 class="mb-0 fw-bold text-dark" style="font-size: 2.2rem;"><?= $kpi['total_resultados'] ?></h3>
                <small class="text-muted" style="font-size: 1rem;">Aprendizajes a evaluar</small>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-6 col-xl-2">
        <div class="card border-0 shadow-sm rounded-3 h-100 text-center py-2" style="background: #ffffff; border-left: 4px solid #6f42c1 !important;">
            <div class="card-body p-2">
                <i class="fa-solid fa-chalkboard-user fa-2x mb-1" style="color: #6f42c1;"></i>
                <div class="text-uppercase fw-semibold text-muted" style="font-size: 1.1rem;">Instructores</div>
                <h3 class="mb-0 fw-bold text-dark" style="font-size: 2.2rem;"><?= $kpi['total_instructores'] ?></h3>
                <small class="text-muted" style="font-size: 1rem;">Asignados</small>
            </div>
        </div>
    </div>
</div>

<!-- Formulario y Filtros de Búsqueda -->
<div class="conte mb-3">
    <div class="card border-0 shadow-sm p-3 mb-3" style="background: #ffffff; border-radius: 8px;">
        <form method="GET" action="home.php">
            <input type="hidden" name="pg" value="<?= htmlspecialchars($pg) ?>">
            
            <div class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label for="idfic" class="form-label fw-bold" style="font-size: 1.3rem;">
                        <i class="fa-solid fa-hashtag text-success"></i> Seleccione Ficha:
                    </label>
                    <select name="idfic" id="idfic" class="form-select form--input-default" onchange="this.form.submit();" required>
                        <option value="">-- Seleccione Ficha --</option>
                        <?php if (!empty($dtFichas)): ?>
                            <?php foreach ($dtFichas as $f): ?>
                                <option value="<?= htmlspecialchars($f['idfic']) ?>" <?= ($idfic == $f['idfic']) ? 'selected' : '' ?>>
                                    Ficha <?= htmlspecialchars($f['idfic']) ?> - <?= htmlspecialchars($f['nomfic']) ?> (<?= htmlspecialchars($f['nompro'] ?? 'Programa') ?>)
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="trimestre" class="form-label fw-bold" style="font-size: 1.3rem;">
                        <i class="fa-solid fa-calendar-day text-success"></i> Seleccione Trimestre:
                    </label>
                    <select name="trimestre" id="trimestre" class="form-select form--input-default" onchange="this.form.submit();" <?= empty($idfic) ? 'disabled' : '' ?>>
                        <option value="">-- Todos los Trimestres --</option>
                        <?php if (!empty($trimestresList)): ?>
                            <?php foreach ($trimestresList as $t): ?>
                                <option value="<?= $t['numero'] ?>" <?= ($trimestre == $t['numero']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($t['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary px-3" style="background-color: #00af00; border-color: #00af00;">
                        <i class="fa fa-search"></i> Consultar
                    </button>
                    <a href="home.php?pg=<?= htmlspecialchars($pg) ?>" class="btn btn-outline-secondary">
                        <i class="fa fa-rotate-left"></i> Limpiar
                    </a>
                </div>
            </div>
        </form>

        <?php if (!empty($dtFechas) && !empty($dtFechas['finific'])): ?>
            <div class="mt-2 text-muted" style="font-size: 1.15rem;">
                <i class="fa-solid fa-clock-rotate-left text-success"></i> 
                Vigencia de la ficha: <strong><?= htmlspecialchars($dtFechas['finific']) ?></strong> al <strong><?= htmlspecialchars($dtFechas['ffinfic'] ?? '') ?></strong>
                <?php if ($trimestre): ?>
                    | <span class="badge bg-success">Filtrado por Trimestre <?= htmlspecialchars($trimestre) ?></span>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Datos Institucionales de Programa y Ficha -->
<?php if (!empty($dtPrg)): ?>
    <?php foreach ($dtPrg as $dtP): ?>
        <div class="mb-3">
            <table class="table table-striped table-bordered" style="width:100%; font-size: 1.25rem;">
                <tr>
                    <th colspan='8' style="font-size: 2rem; font-weight: bold; text-align: center; background-color: #f1f8f1; color: #008700;">
                        FICHA <?= htmlspecialchars($dtP['idfic'] ?? $idfic) ?> - <?= htmlspecialchars($dtP['nomfic'] ?? '') ?>
                    </th>
                </tr>
                <tr>
                    <th style="width: 15%;">Programa</th>
                    <td colspan='7'>
                        <strong><?= htmlspecialchars($dtP['codpro'] ?? '') ?></strong> - <?= htmlspecialchars($dtP['nompro'] ?? '') ?> 
                        <span class="badge bg-secondary">Versión: <?= htmlspecialchars($dtP['verpro'] ?? '1') ?></span>
                    </td>
                </tr>
                <tr>
                    <th>Horas Lectiva</th>
                    <td><?= htmlspecialchars($dtP['horlpro'] ?? '0') ?></td>
                    <th>Créditos Lectiva</th>
                    <td><?= htmlspecialchars($dtP['crelpro'] ?? '0') ?></td>
                    <th>Horas Productiva</th>
                    <td><?= htmlspecialchars($dtP['horppro'] ?? '0') ?></td>
                    <th>Créditos Productiva</th>
                    <td><?= htmlspecialchars($dtP['creppro'] ?? '0') ?></td>
                </tr>
                <tr>
                    <th>Tipo Formación</th>
                    <td><?= htmlspecialchars($dtP['nomval'] ?? 'Presencial') ?></td>
                    <th>Red Conocimiento</th>
                    <td colspan="3"><?= htmlspecialchars($dtP['redcon'] ?? 'No especificada') ?></td>
                    <th>Área</th>
                    <td><?= htmlspecialchars($dtP['nomare'] ?? 'General') ?></td>
                </tr>
            </table>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<!-- Tabla DataTables con Competencias, Resultados e Instructor Asignado -->
<div class="table-responsive">
    <table id="example" class="table table-striped table-hover dataTable" style="width:100%; background: #ffffff;">
        <thead style="background-color: #00af00; color: #ffffff;">
            <tr class="fila">
                <th style="width: 30%;">Competencia</th>
                <th style="width: 35%;">Resultados de Aprendizaje</th>
                <th style="width: 18%;">Instructor Asignado</th>
                <th style="width: 10%;">Fase / Duración</th>
                <th style="width: 7%;">Fechas</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            if (!empty($dtComRes)):
                $compActual = null;

                foreach ($dtComRes as $cr):
                    $esNuevaComp = ($compActual !== $cr['idcom']);
                    $compActual = $cr['idcom'];
                    $duracion = !empty($cr['horcom']) ? htmlspecialchars($cr['horcom']) . ' hrs' : '-';
                    $fase = !empty($cr['fas']) ? htmlspecialchars($cr['fas']) : 'Lectiva';
                    $instructor = !empty($cr['instructor']) ? htmlspecialchars($cr['instructor']) : 'Sin asignar';
                    $fechaRango = (!empty($cr['fch_inicio']) && $cr['fch_inicio'] != '0000-00-00') ? htmlspecialchars($cr['fch_inicio']) : '-';
            ?>
                <tr>
                    <td style="font-weight: 600; color: #1e1e1e; font-size: 1.2rem;">
                        <?= $esNuevaComp ? htmlspecialchars($cr['descom']) : '' ?>
                    </td>
                    <td style="font-size: 1.2rem; color: #2b2b2b;">
                        <i class="fa-solid fa-circle-check text-success" style="font-size: 0.9rem;"></i> 
                        <?= htmlspecialchars($cr['nomres']) ?>
                    </td>
                    <td style="font-size: 1.2rem;">
                        <span class="badge <?= ($instructor != 'Sin asignar' && $instructor != 'Por asignar') ? 'bg-success' : 'bg-light text-muted border' ?>">
                            <i class="fa-solid fa-user-tie"></i> <?= $instructor ?>
                        </span>
                        <?php if (!empty($cr['doc_instructor'])): ?>
                            <br><small class="text-muted">Doc: <?= htmlspecialchars($cr['doc_instructor']) ?></small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="badge bg-light text-dark border"><?= $fase ?></span><br>
                        <small class="text-muted"><i class="fa-regular fa-clock"></i> <?= $duracion ?></small>
                    </td>
                    <td style="font-size: 1.05rem;">
                        <?= $fechaRango ?>
                    </td>
                </tr>
            <?php 
                endforeach;
            elseif ($idfic): 
            ?>
                <tr>
                    <td colspan="5" class="text-center text-muted py-4" style="font-size: 1.3rem;">
                        <i class="fa-solid fa-circle-info fa-2x text-warning mb-2 d-block"></i>
                        No se encontraron resultados para la ficha seleccionada en este trimestre.
                    </td>
                </tr>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="text-center text-muted py-4" style="font-size: 1.3rem;">
                        <i class="fa-solid fa-arrow-pointer fa-2x text-success mb-2 d-block"></i>
                        Seleccione una Ficha en el formulario superior para consultar sus competencias, resultados por trimestre e instructores asignados.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr class="fila">
                <th>Competencia</th>
                <th>Resultados de Aprendizaje</th>
                <th>Instructor Asignado</th>
                <th>Fase / Duración</th>
                <th>Fechas</th>
            </tr>
        </tfoot>
    </table>
</div>
