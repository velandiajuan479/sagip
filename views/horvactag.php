<?php require_once 'controllers/horcactag.php'; ?>

<?php
$iconoVista = isset($icono) && !empty($icono) ? $icono : 'fa-solid fa-file-signature';
echo titulo2("<i class='" . $iconoVista . "'></i> Actividades", 0);
?>

<!-- Dashboard de Métricas / Indicadores Superiores de Actividad -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-xl-2">
        <a href="home.php?pg=<?= $pg; ?>" class="text-decoration-none" title="Ver todas las actividades">
            <div class="card border-0 shadow-sm rounded-3 h-100 text-center py-2" style="background: linear-gradient(135deg, #00af00 0%, #008700 100%); color: #fff; cursor: pointer;">
                <div class="card-body p-2">
                    <i class="fa-solid fa-list-check fa-2x mb-1" style="opacity: 0.9;"></i>
                    <div class="text-uppercase fw-semibold" style="font-size: 1.1rem; letter-spacing: 0.5px;">Actividades</div>
                    <h4 class="mb-0 text-white fw-bold" style="font-size: 1.8rem;"><?= $kpiActividad['total_actividades'] ?? 0 ?></h4>
                    <small style="font-size: 0.95rem; opacity: 0.9;">Registradas</small>
                </div>
            </div>
        </a>
    </div>

    <div class="col-6 col-md-4 col-xl-2">
        <a href="home.php?pg=1520" class="text-decoration-none" title="Ver programación de horarios">
            <div class="card border-0 shadow-sm rounded-3 h-100 text-center py-2" style="background: #ffffff; border-left: 4px solid #198754 !important; cursor: pointer;">
                <div class="card-body p-2">
                    <i class="fa-solid fa-hourglass-half fa-2x mb-1 text-success"></i>
                    <div class="text-uppercase fw-semibold text-muted" style="font-size: 1.1rem;">Duración Total</div>
                    <h3 class="mb-0 fw-bold text-dark" style="font-size: 2.2rem;"><?= $kpiActividad['total_duracion'] ?? '0 min' ?></h3>
                    <small class="text-muted" style="font-size: 1rem;">Tiempo acumulado</small>
                </div>
            </div>
        </a>
    </div>

    <div class="col-6 col-md-4 col-xl-3">
        <a href="home.php?pg=1506" class="text-decoration-none" title="Ver sesiones de formación">
            <div class="card border-0 shadow-sm rounded-3 h-100 text-center py-2" style="background: #ffffff; border-left: 4px solid #0d6efd !important; cursor: pointer;">
                <div class="card-body p-2">
                    <i class="fa-solid fa-layer-group fa-2x mb-1 text-primary"></i>
                    <div class="text-uppercase fw-semibold text-muted" style="font-size: 1.1rem;">Sesiones</div>
                    <h3 class="mb-0 fw-bold text-dark" style="font-size: 2.2rem;"><?= $kpiActividad['total_sesiones'] ?? 0 ?></h3>
                    <small class="text-muted" style="font-size: 1rem;">Con actividad asignada</small>
                </div>
            </div>
        </a>
    </div>

    <div class="col-6 col-md-6 col-xl-3">
        <a href="home.php?pg=<?= $pg; ?>" class="text-decoration-none" title="Ver categorías de actividad">
            <div class="card border-0 shadow-sm rounded-3 h-100 text-center py-2" style="background: #ffffff; border-left: 4px solid #ffc107 !important; cursor: pointer;">
                <div class="card-body p-2">
                    <i class="fa-solid fa-tags fa-2x mb-1 text-warning"></i>
                    <div class="text-uppercase fw-semibold text-muted" style="font-size: 1.1rem;">Tipos de Actividad</div>
                    <h3 class="mb-0 fw-bold text-dark" style="font-size: 2.2rem;"><?= $kpiActividad['total_tipos'] ?? 0 ?></h3>
                    <small class="text-muted" style="font-size: 1rem;">Categorías registradas</small>
                </div>
            </div>
        </a>
    </div>

    <div class="col-12 col-md-6 col-xl-2">
        <a href="home.php?pg=1521" class="text-decoration-none" title="Ver reporte de formación">
            <div class="card border-0 shadow-sm rounded-3 h-100 text-center py-2" style="background: #ffffff; border-left: 4px solid #6f42c1 !important; cursor: pointer;">
                <div class="card-body p-2">
                    <i class="fa-solid fa-stopwatch fa-2x mb-1" style="color: #6f42c1;"></i>
                    <div class="text-uppercase fw-semibold text-muted" style="font-size: 1.1rem;">Promedio</div>
                    <h3 class="mb-0 fw-bold text-dark" style="font-size: 2.2rem;"><?= $kpiActividad['promedio_duracion'] ?? '0 min' ?></h3>
                    <small class="text-muted" style="font-size: 1rem;">Por actividad</small>
                </div>
            </div>
        </a>
    </div>
</div>


<!-- Formulario de Registro/Edición de Actividades -->
<?php if (!$esAprendiz): ?>
<div class="card border-0 shadow-sm p-3 mb-4" style="background: #ffffff; border-radius: 8px;">

    <form id="frm1" action="home.php?pg=<?= $pg; ?>" method="POST" enctype="multipart/form-data">  
        <div class="row g-3"> 
            <div class="form-group col-md-4"> 
                <label for="nomact" class="form-label fw-bold">
                    <i class="fa-solid fa-pen-nib text-success"></i> Nombre Actividad
                </label> 
                <input type="text" class="form-control" name="nomact" id="nomact" value="<?php if($dtOne && isset($dtOne[0]['nomact'])) echo htmlspecialchars($dtOne[0]['nomact']); ?>" required> 
            </div> 
             
            <div class="form-group col-md-4"> 
                <label for="idses" class="form-label fw-bold">
                    <i class="fa-solid fa-layer-group text-success"></i> Sesión
                </label> 
                <select name="idses" id="idses" class="form-select"> 
                <?php if (!empty($dses)): ?>
                    <?php foreach ($dses as $de): ?> 
                        <option value="<?= htmlspecialchars($de['idses']); ?>" <?php if ($dtOne && isset($dtOne[0]['idses']) && $dtOne[0]['idses'] == $de['idses']) echo " selected "; ?>>
                            Sesión <?= htmlspecialchars($de['idses']); ?>
                        </option> 
                    <?php endforeach; ?>
                <?php endif; ?> 
                </select> 
            </div> 

            <div class="form-group col-md-4"> 
                <label for="desact" class="form-label fw-bold">
                    <i class="fa-solid fa-align-left text-success"></i> Descripción
                </label> 
                <input type="text" class="form-control" name="desact" id="desact" value="<?php if($dtOne && isset($dtOne[0]['desact'])) echo htmlspecialchars($dtOne[0]['desact']); ?>" required> 
            </div> 

            <div class="form-group col-md-3"> 
                <label for="duract" class="form-label fw-bold">
                    <i class="fa-solid fa-clock text-success"></i> Duración (min)
                </label> 
                <input type="number" class="form-control" name="duract" id="duract" value="<?php if($dtOne && isset($dtOne[0]['duract'])) echo htmlspecialchars($dtOne[0]['duract']); ?>" required> 
            </div> 

            <div class="form-group col-md-3"> 
                <label for="tipact" class="form-label fw-bold">
                    <i class="fa-solid fa-tag text-success"></i> Tipo Actividad
                </label> 
                <input type="number" class="form-control" name="tipact" id="tipact" value="<?php if($dtOne && isset($dtOne[0]['tipact'])) echo htmlspecialchars($dtOne[0]['tipact']); ?>" required> 
            </div>  

            <div class="form-group col-md-3"> 
                <label for="ordact" class="form-label fw-bold">
                    <i class="fa-solid fa-arrow-down-1-9 text-success"></i> Orden Actividad
                </label> 
                <input type="number" class="form-control" name="ordact" id="ordact" value="<?php if($dtOne && isset($dtOne[0]['ordact'])) echo htmlspecialchars($dtOne[0]['ordact']); ?>" required> 
            </div>  

            <div class="form-group col-md-3 d-flex align-items-end"> 
                <input type="hidden" name="ope" value="save"> 
                <input type="hidden" name="idact" value="<?php if($dtOne && isset($dtOne[0]['idact'])) echo htmlspecialchars($dtOne[0]['idact']); ?>"> 
                <button type="submit" class="btn btn-primary w-100" style="background-color: #00af00; border-color: #00af00;">
                    <i class="fa-solid fa-paper-plane"></i> <?= ($dtOne) ? 'Guardar Cambios' : 'Registrar Actividad' ?>
                </button>
            </div> 
        </div> 
    </form> 
</div> 
<?php endif; ?>


<!-- Tabla de Actividades -->
<div class="card border-0 shadow-sm p-3" style="background: #ffffff; border-radius: 8px;">
    <div class="table-responsive">
        <table id="example" class="table table-striped table-hover align-middle" style="width:100%"> 
            <thead class="table-success" style="background-color: #f1f8f1; color: #008700;"> 
                <tr> 
                    <th>Número Actividad</th> 
                    <th>Sesión</th> 
                    <th>Nombre</th> 
                    <th>Descripción</th> 
                    <th>Duración</th> 
                    <th>Tipo</th> 
                    <th>Orden</th> 
                    <th class="text-center">Acciones</th>
                </tr> 
            </thead> 

            <tbody> 
                <?php if(!empty($dat)): ?>
                    <?php foreach ($dat as $dt): ?> 
                        <tr> 
                            <td class="fw-bold"><?= htmlspecialchars($dt["idact"]); ?></td> 
                            <td><span class="badge bg-secondary">Sesión <?= htmlspecialchars($dt["idses"]); ?></span></td> 
                            <td><?= htmlspecialchars($dt["nomact"]); ?></td> 
                            <td><?= htmlspecialchars($dt["desact"]); ?></td> 
                            <td><span class="badge bg-success"><?= htmlspecialchars($dt["duract"]); ?> min</span></td> 
                            <td><?= htmlspecialchars($dt["tipact"]); ?></td> 
                            <td><?= htmlspecialchars($dt["ordact"]); ?></td> 
                            <td class="text-center"> 
                                <a href="home.php?pg=1508&ope=edi&idact=<?= htmlspecialchars($dt["idact"]); ?>" class="btn btn-sm btn-outline-primary me-1" title="Editar">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a> 
                                <a href="home.php?pg=1508&ope=del&idact=<?= htmlspecialchars($dt["idact"]); ?>" class="btn btn-sm btn-outline-danger" onclick="return eli();" title="Eliminar">
                                    <i class="fa-solid fa-trash-can"></i>
                                </a> 
                            </td> 
                        </tr> 
                    <?php endforeach; ?>
                <?php endif; ?> 
            </tbody> 
        </table>
    </div>
</div>
