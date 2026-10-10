<?php
include('controllers/concaula.php'); 
?>
<div class="conte">
    <?php echo titulo2("<i class='" . $icono . "'></i> Aula"); ?>

    <div class="inser">
        <form id="frmins" action="home.php?pg=<?= $pg; ?>" method="POST" enctype="multipart/form-data">
            <div class="row">
                <div class="form-group col-md-6">
                    <label for="nomaul">Ambiente Numero</label>
                    <input type="text" name="nomaul" id="nomaul" maxlength="255" class="form-control" required value="<?php if (!empty($datOne)) echo $datOne[0]['nomaul']; ?>">
                </div>
                <div class="form-group col-md-6">
                    <label for="piso">Piso</label>
                    <input type="number" name="piso" id="piso" class="form-control" value="<?php if(!empty($datOne)) echo $datOne[0]['piso']; else echo "1" ?>" min="1" max="99">
                </div>
                <div class="form-group col-md-6">
                    <label for="bloqau">Bloque</label>
                    <input type="number" name="bloqau" id="bloqau" class="form-control" value="<?php if(!empty($datOne)) echo $datOne[0]['bloqau']; else echo "1" ?>" min="0" max="99">
                </div>
                <div class="form-group col-md-6">
                    <label for="taula">Tamaño</label>
                    <input type="number" name="taula" id="taula" class="form-control" value="<?php if(!empty($datOne)) echo $datOne[0]['taula']; else echo "1" ?>" min="1" max="4">
                </div>
                <div class="form-group col-md-6">
                    <label for="tipaul">Tipo</label>
                    <select name="tipaul" id="tipaul" class="form-select" required>
                        <option value="1" <?php if ($datOne && $datOne[0]['tipaul'] == 1) echo 'selected'; ?>>Ambiente</option>
                        <option value="2" <?php if ($datOne && $datOne[0]['tipaul'] == 2) echo 'selected'; ?>>Especial</option>
                    </select>
                </div>
                <div class="form-group col-md-6">
                    <label for="desaula">Descripción</label>
                    <textarea name="desaula" id="desaula" class="form-control"><?php if (!empty($datOne)) echo $datOne[0]['desaula']; ?></textarea>
                </div>
                <div class="form-group col-md-6">
                    <input class="btn btn-primary" type="submit" value="Enviar">
                    <input type="hidden" name="ope" value="save">
                    <input type="hidden" name="idaul" id="idaul" value="<?php if (!empty($datOne)) echo $datOne[0]['idaul']; ?>">
                </div>
            </div>
        </form>
    </div>

    <?php $st = !empty($dtStat) ? $dtStat[0] : array(); ?>
    <div class="mb-4">
        <div class="row g-3">
            <div class="col-6 col-md-3">
                <div class="border border-success rounded-4 p-4 text-center bg-white h-100 d-flex flex-column align-items-center justify-content-center shadow-sm">
                    <i class="fa-solid fa-school fs-1 text-success mb-2"></i>
                    <div class="fs-1 fw-bold text-success lh-1 mb-2"><?= isset($st['totaus']) ? $st['totaus'] : 0; ?></div>
                    <div class="small fw-bold text-uppercase text-secondary">Total Aulas</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="border border-success rounded-4 p-4 text-center bg-white h-100 d-flex flex-column align-items-center justify-content-center shadow-sm">
                    <i class="fa-solid fa-flask fs-1 text-warning mb-2"></i>
                    <div class="fs-1 fw-bold text-warning lh-1 mb-2"><?= isset($st['espeus']) ? $st['espeus'] : 0; ?></div>
                    <div class="small fw-bold text-uppercase text-secondary">Especializadas</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="border border-success rounded-4 p-4 text-center bg-white h-100 d-flex flex-column align-items-center justify-content-center shadow-sm">
                    <i class="fa-solid fa-chalkboard-user fs-1 text-primary mb-2"></i>
                    <div class="fs-1 fw-bold text-primary lh-1 mb-2"><?= isset($st['convaus']) ? $st['convaus'] : 0; ?></div>
                    <div class="small fw-bold text-uppercase text-secondary">Convencionales</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="border border-success rounded-4 p-4 text-center bg-white h-100 d-flex flex-column align-items-center justify-content-center shadow-sm">
                    <i class="fa-solid fa-arrows-rotate fs-1 text-warning mb-2"></i>
                    <div class="fs-1 fw-bold text-warning lh-1 mb-2"><?= isset($st['presaus']) ? $st['presaus'] : 0; ?></div>
                    <div class="small fw-bold text-uppercase text-secondary">En Prestamo</div>
                </div>
            </div>
        </div>
    </div>

    <div>
        <div class="border border-2 border-success rounded-4 p-4 mb-4 bg-white shadow-sm">
            <h5 class="fw-bold text-success mb-3 d-flex align-items-center gap-2">
                <i class="fa-solid fa-circle-nodes"></i> AREAS
            </h5>
            <div>
                <?php if (!empty($dtArea)) { ?>
                    <div class="d-flex flex-column gap-3">
                        <?php foreach ($dtArea as $dta) { ?>
                            <div class="border border-success rounded-3 p-3 bg-white text-center">
                                <div class="fw-bold text-success fs-6 mb-2">
                                    <?= htmlspecialchars($dta['nomaul']); ?>
                                    <span class="badge bg-warning text-dark ms-2">Especial</span>
                                </div>
                                <div class="border-top border-secondary-subtle my-2"></div>
                                <div class="d-flex justify-content-center align-items-center gap-3">
                                    <a href="#" class="text-success fs-5 btn-accion-ver text-decoration-none" title="Ver info"
                                        data-bs-toggle="modal" data-bs-target="#modalAula"
                                        data-nomaul="<?= htmlspecialchars($dta['nomaul']); ?>"
                                        data-piso="<?= htmlspecialchars($dta['piso']); ?>"
                                        data-bloqau="<?= htmlspecialchars($dta['bloqau']); ?>"
                                        data-tipaul="<?= htmlspecialchars($dta['tipaul']); ?>"
                                        data-taula="<?= htmlspecialchars($dta['taula']); ?>"
                                        data-esaula="<?= htmlspecialchars($dta['esaula']); ?>"
                                        data-desaula="<?= htmlspecialchars($dta['desaula']); ?>">
                                        <i class="fa-solid fa-circle-info"></i>
                                    </a>
                                    <a href="home.php?pg=<?= $pg; ?>&idaul=<?= $dta['idaul']; ?>&ope=edi" class="text-primary fs-5 btn-accion-edi text-decoration-none" title="Editar">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    <a href="home.php?pg=<?= $pg; ?>&idaul=<?= $dta['idaul']; ?>&ope=eli" class="text-danger fs-5 btn-accion-eli text-decoration-none" title="Eliminar" data-eli>
                                        <i class="fa-solid fa-trash-can"></i>
                                    </a>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                <?php } else { ?>
                    <div class="alert alert-info text-center mb-0">
                        <i class="fa-solid fa-circle-info me-2"></i> No hay areas registradas.
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>

    <div>
        <?php if (!empty($dtBloque)) { foreach ($dtBloque as $dbq) {
            $maula->setBloqau($dbq['bloqau']);
            $dtPisoBloque = $maula->getPisosByBloque();
        ?>
            <div class="border border-2 border-success rounded-4 p-4 mb-4 bg-white shadow-sm">
                <h5 class="fw-bold text-success mb-3 d-flex align-items-center gap-2">
                    <i class="fa-solid fa-building"></i> BLOQUE <?= htmlspecialchars($dbq['bloqau']); ?>
                </h5>
                <div>
                    <?php if (!empty($dtPisoBloque)) { foreach ($dtPisoBloque as $dtp) {
                        $maula->setPiso($dtp['piso']);
                        $dtaula = $maula->getAllAula();
                        if (!empty($dtaula)) {
                    ?>
                        <div class="mb-4">
                            <div class="mb-3 d-flex align-items-center gap-2 border-start border-4 border-success ps-2">
                                <i class="fa-solid fa-layer-group text-secondary"></i>
                                <span class="fw-bold text-dark">Piso <?= htmlspecialchars($dtp['piso']); ?></span>
                            </div>
                            <div class="row g-3">
                                <?php foreach ($dtaula as $dtau) {
                                    $taula = !empty($dtau['taula']) ? (int)$dtau['taula'] : 1;
                                    $colMd = min(12, 3 * $taula);
                                ?>
                                    <div class="col-12 col-sm-6 col-md-<?= $colMd; ?>">
                                        <div class="border border-success rounded-3 p-3 bg-white text-center h-100 d-flex flex-column justify-content-between">
                                            <div>
                                                <div class="fw-bold text-success mb-2">
                                                    <?= htmlspecialchars($dtau['nomaul']); ?>
                                                    <?php if (isset($dtau['tipaul']) && $dtau['tipaul'] == 2) { ?>
                                                        <span class="badge bg-warning text-dark ms-1">Especial</span>
                                                    <?php } ?>
                                                </div>
                                            </div>
                                            <div>
                                                <div class="border-top border-secondary-subtle my-2"></div>
                                                <div class="d-flex justify-content-center align-items-center gap-3">
                                                    <a href="#" class="text-success fs-5 btn-accion-ver text-decoration-none" title="Ver info"
                                                        data-bs-toggle="modal" data-bs-target="#modalAula"
                                                        data-nomaul="<?= htmlspecialchars($dtau['nomaul']); ?>"
                                                        data-piso="<?= htmlspecialchars($dtau['piso']); ?>"
                                                        data-bloqau="<?= htmlspecialchars($dtau['bloqau']); ?>"
                                                        data-tipaul="<?= htmlspecialchars($dtau['tipaul']); ?>"
                                                        data-taula="<?= htmlspecialchars($dtau['taula']); ?>"
                                                        data-esaula="<?= htmlspecialchars($dtau['esaula']); ?>"
                                                        data-desaula="<?= htmlspecialchars($dtau['desaula']); ?>">
                                                        <i class="fa-solid fa-circle-info"></i>
                                                    </a>
                                                    <a href="home.php?pg=<?= $pg; ?>&idaul=<?= $dtau['idaul']; ?>&ope=edi" class="text-primary fs-5 btn-accion-edi text-decoration-none" title="Editar">
                                                        <i class="fa-solid fa-pen-to-square"></i>
                                                    </a>
                                                    <a href="home.php?pg=<?= $pg; ?>&idaul=<?= $dtau['idaul']; ?>&ope=eli" class="text-danger fs-5 btn-accion-eli text-decoration-none" title="Eliminar" data-eli>
                                                        <i class="fa-solid fa-trash-can"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    <?php }}} else {
                        $dtaula = $maula->getAulasByBloque();
                        if (!empty($dtaula)) {
                    ?>
                        <div class="row g-3">
                            <?php foreach ($dtaula as $dtau) {
                                $taula = !empty($dtau['taula']) ? (int)$dtau['taula'] : 1;
                                $colMd = min(12, 3 * $taula);
                            ?>
                                <div class="col-12 col-sm-6 col-md-<?= $colMd; ?>">
                                    <div class="border border-success rounded-3 p-3 bg-white text-center h-100 d-flex flex-column justify-content-between">
                                        <div>
                                            <div class="fw-bold text-success mb-2">
                                                <?= htmlspecialchars($dtau['nomaul']); ?>
                                                <?php if (isset($dtau['tipaul']) && $dtau['tipaul'] == 2) { ?>
                                                    <span class="badge bg-warning text-dark ms-1">Especial</span>
                                                <?php } ?>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="border-top border-secondary-subtle my-2"></div>
                                            <div class="d-flex justify-content-center align-items-center gap-3">
                                                <a href="#" class="text-success fs-5 btn-accion-ver text-decoration-none" title="Ver info"
                                                    data-bs-toggle="modal" data-bs-target="#modalAula"
                                                    data-nomaul="<?= htmlspecialchars($dtau['nomaul']); ?>"
                                                    data-piso="<?= htmlspecialchars($dtau['piso']); ?>"
                                                    data-bloqau="<?= htmlspecialchars($dtau['bloqau']); ?>"
                                                    data-tipaul="<?= htmlspecialchars($dtau['tipaul']); ?>"
                                                    data-taula="<?= htmlspecialchars($dtau['taula']); ?>"
                                                    data-esaula="<?= htmlspecialchars($dtau['esaula']); ?>"
                                                    data-desaula="<?= htmlspecialchars($dtau['desaula']); ?>">
                                                    <i class="fa-solid fa-circle-info"></i>
                                                </a>
                                                <a href="home.php?pg=<?= $pg; ?>&idaul=<?= $dtau['idaul']; ?>&ope=edi" class="text-primary fs-5 btn-accion-edi text-decoration-none" title="Editar">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </a>
                                                <a href="home.php?pg=<?= $pg; ?>&idaul=<?= $dtau['idaul']; ?>&ope=eli" class="text-danger fs-5 btn-accion-eli text-decoration-none" title="Eliminar" data-eli>
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    <?php }} ?>
                </div>
            </div>
        <?php }} else { ?>
            <div class="alert alert-info text-center mb-0">
                <i class="fa-solid fa-circle-info me-2"></i> No hay aulas ni bloques registrados.
            </div>
        <?php } ?>
    </div>
</div>

<div class="modal fade" id="modalAula" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fa-solid fa-door-open me-2"></i><span id="mNomAula"></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-3">
                <table class="table table-sm table-bordered table-striped mb-0">
                    <tr>
                        <th class="w-25">Número</th>
                        <td id="mNomAula2"></td>
                    </tr>
                    <tr>
                        <th>Piso</th>
                        <td id="mPiso"></td>
                    </tr>
                    <tr>
                        <th>Ubicación</th>
                        <td id="mUbica"></td>
                    </tr>
                    <tr>
                        <th>Tipo</th>
                        <td id="mTipo"></td>
                    </tr>
                    <tr>
                        <th>Tamaño</th>
                        <td id="mTamano"></td>
                    </tr>
                    <tr>
                        <th>Estado</th>
                        <td id="mEstado"></td>
                    </tr>
                    <tr>
                        <th>Descripción</th>
                        <td id="mDes"></td>
                    </tr>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

