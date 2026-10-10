<?php require_once('controllers/usuccdiv.php'); ?>

<div class="conte">
    <?php echo titulo2("<i class='" . $icono . "'></i> Carga Individual - Documentos de Identidad", 1); ?>

    <div class="row my-4 g-4">
        <?php foreach ([['total_docs', 'Total', 'fa-layer-group'], ['total_aprobados', 'Aprobados', 'fa-circle-check'], ['total_pendientes', 'Pendientes', 'fa-hourglass-half']] as $k) { 
            $v = !empty($metricas) ? (int)($metricas[0][$k[0]] ?? 0) : 0; 
        ?>
            <div class="col-md-4">
                <div class="kpi-card p-3">
                    <span class="text-uppercase fw-bold small"><?= $k[1] ?></span>
                    <div class="fs-1 fw-bold"><?= $v ?></div>
                    <i class="fa-solid <?= $k[2] ?>"></i>
                </div>
            </div>
        <?php } ?>
    </div>

    <div class="inser">
        <form id="frmins" class="filter-toolbar-container" action="home.php?pg=<?= $pg; ?>" method="POST" enctype="multipart/form-data">
            <h2><i class="fa-solid fa-id-card"></i> Cargar documento de identidad</h2>
            <div class="row">
                <div class="form-group col-md-4">
                    <label for="idTipDoc">Tipo de documento *</label>
                    <select class="form-control" name="idTipDoc" id="idTipDoc" required>
                        <option value="">Seleccione...</option>
                        <?php foreach ((array)$docTipos as $t) { ?>
                            <option value="<?= htmlspecialchars($t['idTipDoc']) ?>" <?= !empty($datOne) && $datOne[0]['idTipDoc'] == $t['idTipDoc'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($t['nomTipDoc']) ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <div class="form-group col-md-4">
                    <label for="archivo">PDF * (máx 5MB)</label>
                    <input type="file" class="form-control" name="archivo" id="archivo" accept=".pdf, application/pdf" required>
                </div>

                <div class="form-group col-md-2">
                    <br>
                    <input type="hidden" name="pg" value="<?= htmlspecialchars($pg) ?>">
                    <input type="hidden" name="opera" value="save">
                    <button class="btn btn-success w-100" type="submit">Guardar y enviar</button>
                </div>
            </div>
        </form>
    </div>

    <?php if ($mensaje) { ?>
        <div class="alert <?= $tipoMensaje ?>"><?= htmlspecialchars($mensaje) ?></div>
    <?php } ?>

    <?php if ($errorMsg) { ?>
        <div class="alert alert-danger"><?= htmlspecialchars($errorMsg) ?></div>
    <?php } ?>

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white py-3">
            <h5 class="fw-bold text-success">
                <?= $esAprobador ? 'Documentos de identidad - Mi centro' : 'Mis documentos de identidad' ?>
            </h5>
        </div>

        <div class="table-responsive">
            <table class="table table-striped align-middle w-100">
                <thead class="table-light">
                    <tr>
                        <th>Documento</th>
                        <th>Usuario</th>
                        <th>Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($datAll)) { 
                        foreach ($datAll as $d) { 
                            $estado = (int)$d['aprEst']; 
                    ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($d['nomTipDoc']) ?></strong><br>
                                    <small><?= htmlspecialchars(basename($d['rutPdf'])) ?></small>
                                </td>
                                <td><?= htmlspecialchars($d['nomusu']) ?> (<?= htmlspecialchars($d['idUsu']) ?>)</td>
                                <td>
                                    <?= $estado === 1 ? '<span class="badge bg-success">Aprobado</span>' : ($estado === 2 ? '<span class="badge bg-danger">Rechazado</span>' : '<span class="badge bg-warning text-dark">Pendiente</span>') ?>
                                </td>
                                <td class="text-end">
                                    <a class="btn btn-sm btn-outline-primary" target="_blank" href="<?= htmlspecialchars($d['rutPdf']) ?>">Ver</a>
                                    <?php if ($esAprobador && $estado === 0) { ?>
                                        <a class="btn btn-sm btn-outline-success" href="home.php?pg=<?= htmlspecialchars($pg) ?>&opera=aprobar&idCind=<?= (int)$d['idCind'] ?>">Aprobar</a>
                                        <a class="btn btn-sm btn-outline-warning" onclick="return confirm('¿Rechazar?')" href="home.php?pg=<?= htmlspecialchars($pg) ?>&opera=rechazar&idCind=<?= (int)$d['idCind'] ?>">Rechazar</a>
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php } 
                    } else { ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">No hay documentos.</td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>