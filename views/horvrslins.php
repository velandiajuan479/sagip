<?php
require_once 'controllers/horcrslins.php';

$iconoVista = isset($icono) && !empty($icono) ? $icono : 'fa-solid fa-file-circle-check';
echo titulo2("<i class='" . $iconoVista . "'></i> Resultados por Instructor", 2);
require_once("views/vscopehya.php");

$idusu = $idusu ?? null;
$idfic = $idfic ?? null;
$dtInstructores = $dtInstructores ?? [];
$dtFichas = $dtFichas ?? [];
$dtInstructorInfo = $dtInstructorInfo ?? null;
$dtPrg = $dtPrg ?? [];
$dtComRes = $dtComRes ?? [];
$kpi = $kpi ?? [
    'total_instructores' => 0,
    'total_fichas' => 0,
    'total_competencias' => 0,
    'total_resultados' => 0,
    'total_horas' => 0
];
?>

<!-- Dashboard de Métricas / Indicadores Superiores -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3 col-xl-2">
        <div class="card border-0 shadow-sm rounded-3 h-100 text-center py-2" style="background: linear-gradient(135deg, #00af00 0%, #008700 100%); color: #fff;">
            <div class="card-body p-2">
                <i class="fa-solid fa-chalkboard-user fa-2x mb-1" style="opacity: 0.9;"></i>
                <div class="text-uppercase fw-semibold" style="font-size: 1.1rem; letter-spacing: 0.5px;">Instructores</div>
                <h3 class="mb-0 text-white fw-bold" style="font-size: 2.2rem;"><?= $kpi['total_instructores'] ?></h3>
                <small style="font-size: 1rem; opacity: 0.9;">En el sistema</small>
            </div>
        </div>
    </div>

    <div class="col-6 col-md-3 col-xl-2">
        <div class="card border-0 shadow-sm rounded-3 h-100 text-center py-2" style="background: #ffffff; border-left: 4px solid #00af00 !important;">
            <div class="card-body p-2">
                <i class="fa-solid fa-id-card-clip fa-2x mb-1 text-success"></i>
                <div class="text-uppercase fw-semibold text-muted" style="font-size: 1.1rem;">Fichas</div>
                <h3 class="mb-0 fw-bold text-dark" style="font-size: 2.2rem;"><?= $kpi['total_fichas'] ?></h3>
                <small class="text-muted" style="font-size: 1rem;"><?= $idusu ? 'Del instructor' : 'Asignadas' ?></small>
            </div>
        </div>
    </div>

    <div class="col-6 col-md-3 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3 h-100 text-center py-2" style="background: #ffffff; border-left: 4px solid #0d6efd !important;">
            <div class="card-body p-2">
                <i class="fa-solid fa-book-open fa-2x mb-1 text-primary"></i>
                <div class="text-uppercase fw-semibold text-muted" style="font-size: 1.1rem;">Competencias</div>
                <h3 class="mb-0 fw-bold text-dark" style="font-size: 2.2rem;"><?= $kpi['total_competencias'] ?></h3>
                <small class="text-muted" style="font-size: 1rem;"><?= $idusu ? 'A cargo' : 'Totales' ?></small>
            </div>
        </div>
    </div>

    <div class="col-6 col-md-3 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3 h-100 text-center py-2" style="background: #ffffff; border-left: 4px solid #ffc107 !important;">
            <div class="card-body p-2">
                <i class="fa-solid fa-list-check fa-2x mb-1 text-warning"></i>
                <div class="text-uppercase fw-semibold text-muted" style="font-size: 1.1rem;">Resultados</div>
                <h3 class="mb-0 fw-bold text-dark" style="font-size: 2.2rem;"><?= $kpi['total_resultados'] ?></h3>
                <small class="text-muted" style="font-size: 1rem;">Aprendizajes programados</small>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-12 col-xl-2">
        <div class="card border-0 shadow-sm rounded-3 h-100 text-center py-2" style="background: #ffffff; border-left: 4px solid #20c997 !important;">
            <div class="card-body p-2">
                <i class="fa-solid fa-clock fa-2x mb-1 text-info"></i>
                <div class="text-uppercase fw-semibold text-muted" style="font-size: 1.1rem;">Horas</div>
                <h3 class="mb-0 fw-bold text-dark" style="font-size: 2.2rem;"><?= $kpi['total_horas'] ?></h3>
                <small class="text-muted" style="font-size: 1rem;">Horas estimadas</small>
            </div>
        </div>
    </div>
</div>

<!-- Formulario y Filtros -->
<div class="conte mb-3">
    <div class="card border-0 shadow-sm p-3 mb-3" style="background: #ffffff; border-radius: 8px;">
        <form method="GET" action="home.php">
            <input type="hidden" name="pg" value="<?= htmlspecialchars($pg) ?>">
            
            <div class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label for="idusu" class="form-label fw-bold" style="font-size: 1.3rem;">
                        <i class="fa-solid fa-user-tie text-success"></i> Seleccione Instructor:
                    </label>
                    <select name="idusu" id="idusu" class="form-select form--input-default" onchange="this.form.submit();" required>
                        <option value="">-- Seleccione --</option>
                        <?php if (!empty($dtInstructores)): ?>
                            <?php foreach ($dtInstructores as $inst): ?>
                                <option value="<?= $inst['idusu'] ?>" <?= ($idusu == $inst['idusu']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($inst['nomusu']) ?> <?= !empty($inst['ndocusu']) ? '('.$inst['ndocusu'].')' : '' ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="idfic" class="form-label fw-bold" style="font-size: 1.3rem;">
                        <i class="fa-solid fa-hashtag text-success"></i> Fichas del Instructor:
                    </label>
                    <select name="idfic" id="idfic" class="form-select form--input-default" onchange="this.form.submit();" <?= empty($idusu) ? 'disabled' : '' ?>>
                        <option value="">-- Todas las Fichas del Instructor --</option>
                        <?php if (!empty($dtFichas)): ?>
                            <?php foreach ($dtFichas as $f): ?>
                                <option value="<?= $f['idfic'] ?>" <?= ($idfic == $f['idfic']) ? 'selected' : '' ?>>
                                    Ficha <?= htmlspecialchars($f['idfic']) ?> - <?= htmlspecialchars($f['nomfic']) ?> (<?= htmlspecialchars($f['jornada'] ?? 'Jornada') ?>)
                                </option>
                            <?php endforeach; ?>
                        <?php elseif ($idusu): ?>
                            <option value="" disabled>Sin fichas con agenda directa</option>
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

        <?php if ($dtInstructorInfo): ?>
            <div class="mt-3 p-2 bg-light rounded d-flex flex-wrap gap-4 align-items-center" style="font-size: 1.2rem; border: 1px solid #e0e0e0;">
                <div><strong>Instructor:</strong> <?= htmlspecialchars($dtInstructorInfo['nomusu']) ?></div>
                <?php if (!empty($dtInstructorInfo['ndocusu'])): ?>
                    <div><strong>Documento:</strong> <?= htmlspecialchars($dtInstructorInfo['ndocusu']) ?></div>
                <?php endif; ?>
                <?php if (!empty($dtInstructorInfo['emausu'])): ?>
                    <div><strong>Correo:</strong> <?= htmlspecialchars($dtInstructorInfo['emausu']) ?></div>
                <?php endif; ?>
                <div><strong>Fichas encontradas:</strong> <span class="badge bg-success"><?= count($dtFichas) ?></span></div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Datos Institucionales de Programa (cuando hay ficha seleccionada) -->
<?php if (!empty($dtPrg)): ?>
    <?php foreach ($dtPrg as $dtP): ?>
        <div class="mb-3">
            <table class="table table-striped table-bordered" style="width:100%; font-size: 1.25rem;">
                <tr>
                    <th colspan='8' style="font-size:2rem; font-weight: bold; text-align: center; background-color: #f1f8f1; color: #008700;">
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

<!-- Tabla DataTables con Competencias y Resultados -->
<div class="table-responsive">
    <table id="example" class="table table-striped table-hover dataTable" style="width:100%; background: #ffffff;">
        <thead style="background-color: #00af00; color: #ffffff;">
            <tr class="fila">
                <th style="width: 14%;">Ficha</th>
                <th style="width: 32%;">Competencia</th>
                <th style="width: 36%;">Resultados de Aprendizaje</th>
                <th style="width: 10%;">Fase / Duración</th>
                <th style="width: 8%;">Fechas</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            if (!empty($dtComRes)):
                $compActual = null;
                $ficActual = null;

                foreach ($dtComRes as $cr):
                    $esNuevaFic = ($ficActual !== $cr['idfic']);
                    $esNuevaComp = ($compActual !== $cr['idcom']) || $esNuevaFic;
                    $compActual = $cr['idcom'];
                    $ficActual = $cr['idfic'];
                    $duracion = !empty($cr['horcom']) ? htmlspecialchars($cr['horcom']) . ' hrs' : '-';
                    $fase = !empty($cr['fas']) ? htmlspecialchars($cr['fas']) : 'Lectiva';
                    $fechaRango = (!empty($cr['fecini']) && $cr['fecini'] != '0000-00-00') ? htmlspecialchars($cr['fecini']) . ' al ' . htmlspecialchars($cr['fecfin'] ?? '') : '-';
            ?>
                <tr>
                    <td style="font-size: 1.15rem;">
                        <strong><?= htmlspecialchars($cr['idfic']) ?></strong><br>
                        <small class="text-muted"><?= htmlspecialchars($cr['nomfic'] ?? '') ?></small>
                    </td>
                    <td style="font-weight: 600; color: #1e1e1e; font-size: 1.2rem;">
                        <?= $esNuevaComp ? htmlspecialchars($cr['descom']) : '' ?>
                    </td>
                    <td style="font-size: 1.2rem; color: #2b2b2b;">
                        <i class="fa-solid fa-circle-check text-success" style="font-size: 0.9rem;"></i> 
                        <?= htmlspecialchars($cr['nomres']) ?>
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
            elseif ($idusu): 
            ?>
                <tr>
                    <td colspan="5" class="text-center text-muted py-4" style="font-size: 1.3rem;">
                        <i class="fa-solid fa-circle-info fa-2x text-warning mb-2 d-block"></i>
                        No se encontraron resultados de aprendizaje o competencias programadas para este instructor<?= $idfic ? " en la ficha $idfic" : '' ?>.
                    </td>
                </tr>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="text-center text-muted py-4" style="font-size: 1.3rem;">
                        <i class="fa-solid fa-arrow-pointer fa-2x text-success mb-2 d-block"></i>
                        Seleccione un instructor en el menú superior para visualizar sus fichas, competencias y resultados de aprendizaje.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr class="fila">
                <th>Ficha</th>
                <th>Competencia</th>
                <th>Resultados de Aprendizaje</th>
                <th>Fase / Duración</th>
                <th>Fechas</th>
            </tr>
        </tfoot>
    </table>
</div>
