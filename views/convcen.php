<?php require_once 'controllers/conccen.php';

function fmtfec($v){
    if($v) return date("d/m/Y H:i", strtotime($v));
    return "—";
}
?>

<div class="conte">
    <?php echo titulo2("<i class='".$icono."'></i> Centro"); ?>

    <!-- 1. KPI Cards: centros, asociados y candidatos -->
    <div class="row my-4 g-4">

        <!-- Centros de formación -->
        <div class="col-12 col-md-4">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold small text-secondary">Centros de Formación</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold fs-2 lh-1 text-dark"><?= $totCen; ?></span>
                            <span class="small text-secondary">centros</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box bg-primary-subtle text-primary">
                        <i class="fa-solid fa-building fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="fw-semibold d-inline-flex align-items-center gap-1 small text-primary">
                        <i class="fa-solid fa-arrow-trend-up"></i> Registrados
                    </span>
                    <span class="small text-secondary">Aulas: <?= $totAul; ?></span>
                </div>
            </div>
        </div>

        <!-- Asociados -->
        <div class="col-12 col-md-4">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold small text-success">Asociados</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold fs-2 lh-1 text-success"><?= $totAsoc; ?></span>
                            <span class="small text-success opacity-75">usuarios</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box bg-success-subtle text-success">
                        <i class="fa-solid fa-people-group fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="fw-semibold d-inline-flex align-items-center gap-2 small text-success">
                        <i class="fa-solid fa-users"></i> Personas vinculadas
                    </span>
                    <span class="small text-secondary">Fichas: <?= $totFic; ?></span>
                </div>
            </div>
        </div>

        <!-- Candidatos -->
        <div class="col-12 col-md-4">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold small text-danger">Candidatos</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold fs-2 lh-1 text-dark"><?= $totCand; ?></span>
                            <span class="small text-secondary">en proceso</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box bg-danger-subtle text-danger">
                        <i class="fa-solid fa-user-check fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="d-inline-flex align-items-center gap-1 small text-secondary">
                        <i class="fa-solid fa-square-poll-vertical"></i> Representantes y voceros
                    </span>
                    <span class="fw-semibold small text-primary">Censo electoral</span>
                </div>
            </div>
        </div>

    </div>

    <!-- Formulario de registro / edicion -->
    <div class="inser">
        <form id="frmins" action="home.php?pg=<?=$pg;?>" method="POST" enctype="multipart/form-data">
            <h4 class="subsec"><i class="fa-solid fa-building"></i> Datos del centro</h4>
            <div class="row">
                <div class="form-group col-md-3">
                    <label for="idcen">NIT del centro</label>
                    <input type="text" name="idcen" id="idcen" maxlength="70" class="form-control" required value="<?php if(!empty($datOne)) echo $datOne[0]['idcen']; ?>" <?php if(!empty($datOne)) echo "readonly"; ?>>
                </div>
                <div class="form-group col-md-9">
                    <label for="nomcen">Nombre del centro</label>
                    <input type="text" name="nomcen" id="nomcen" maxlength="70" class="form-control" required value="<?php if(!empty($datOne)) echo htmlspecialchars($datOne[0]['nomcen'], ENT_QUOTES); ?>">
                </div>
                <div class="form-group col-md-6">
                    <label for="dircen">Dirección</label>
                    <input type="text" name="dircen" id="dircen" maxlength="100" class="form-control" required value="<?php if(!empty($datOne)) echo htmlspecialchars($datOne[0]['dircen'], ENT_QUOTES); ?>">
                </div>
                <div class="form-group col-md-3">
                    <label for="telcen">Teléfono</label>
                    <input type="number" name="telcen" id="telcen" class="form-control" required value="<?php if(!empty($datOne)) echo $datOne[0]['telcen']; ?>">
                </div>
                <div class="form-group col-md-3">
                    <label for="descen">Descripción</label>
                    <input type="text" name="descen" id="descen" maxlength="70" class="form-control" value="<?php if(!empty($datOne)) echo htmlspecialchars($datOne[0]['descen'], ENT_QUOTES); ?>">
                </div>
                <div class="form-group col-md-3">
                    <label for="imgcen" class="form-label">Foto del centro</label>
                    <input type="file" name="foto" id="imgcen" class="form-control" accept="image/png,image/jpeg,image/gif" <?php if(empty($datOne)) echo "required";?>>
                    <?php if(!empty($datOne[0]['imgcen'])) echo '<img src="img/'.$datOne[0]['imgcen'].'" width="70" class="mt-1 imgprev">'; ?>
                </div>
                <div class="form-group col-md-3">
                    <label for="logocen" class="form-label">Logo del SENA</label>
                    <input type="file" name="logo" id="logocen" class="form-control" accept="image/png,image/jpeg,image/gif">
                    <?php if(!empty($datOne[0]['logocen'])) echo '<img src="img/'.$datOne[0]['logocen'].'" width="70" class="mt-1 imgprev">'; ?>
                </div>
            </div>

            <h4 class="subsec"><i class="fa-solid fa-calendar-days"></i> Fechas del proceso electoral</h4>
            <div class="row">
                <div class="form-group col-md-2">
                    <label for="fiicancen">Ini. Inscripción</label>
                    <input type="datetime-local" name="fiicancen" id="fiicancen" class="form-control" required value="<?php if(!empty($datOne)) echo date("Y-m-d\TH:i", strtotime($datOne[0]['fiicancen'])); ?>">
                </div>
                <div class="form-group col-md-2">
                    <label for="fficancen">Fin Inscripción</label>
                    <input type="datetime-local" name="fficancen" id="fficancen" class="form-control" required value="<?php if(!empty($datOne)) echo date("Y-m-d\TH:i", strtotime($datOne[0]['fficancen'])); ?>">
                </div>
                <div class="form-group col-md-2">
                    <label for="fiprocen">Ini. Propuestas</label>
                    <input type="datetime-local" name="fiprocen" id="fiprocen" class="form-control" required value="<?php if(!empty($datOne)) echo date("Y-m-d\TH:i", strtotime($datOne[0]['fiprocen'])); ?>">
                </div>
                <div class="form-group col-md-2">
                    <label for="ffprocen">Fin Propuestas</label>
                    <input type="datetime-local" name="ffprocen" id="ffprocen" class="form-control" required value="<?php if(!empty($datOne)) echo date("Y-m-d\TH:i", strtotime($datOne[0]['ffprocen'])); ?>">
                </div>
                <div class="form-group col-md-2">
                    <label for="fivotcen">Ini. Votación</label>
                    <input type="datetime-local" name="fivotcen" id="fivotcen" class="form-control" required value="<?php if(!empty($datOne)) echo date("Y-m-d\TH:i", strtotime($datOne[0]['fivotcen'])); ?>">
                </div>
                <div class="form-group col-md-2">
                    <label for="ffvotcen">Fin Votación</label>
                    <input type="datetime-local" name="ffvotcen" id="ffvotcen" class="form-control" required value="<?php if(!empty($datOne)) echo date("Y-m-d\TH:i", strtotime($datOne[0]['ffvotcen'])); ?>">
                </div>
                <div class="form-group col-md-2">
                    <br>
                    <input class="btn btn-primary" type="submit" value="Enviar">
                    <input type="hidden" name="opera" value="save">
                </div>
            </div>
        </form>
    </div>

    <br>

    <!-- Listado de centros -->
    <?php if($datAll){ ?>
    <table id="example" class="table table-striped">
        <thead>
            <tr>
                <th class="text-center">Imágenes</th>
                <th>Centro</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        <?php $modCands = array();
        foreach($datAll AS $dta){
            $mcen->setIdcen($dta['idcen']);
            $datInfo = $mcen->getInfo();
            $datCand = $mcen->getCand();
            $datCrl = $mcen->getCrl();
            $tota = $datInfo ? $datInfo[0] : array();
            if($datCand) $modCands[] = array('idcen'=>$dta['idcen'], 'nomcen'=>$dta['nomcen'], 'cands'=>$datCand);
        ?>
            <tr>
                <td class="td-img text-center align-middle">
                    <div class="d-flex justify-content-center align-items-center flex-wrap gap-2">
                        <?php if(!empty($dta['imgcen'])) echo '<img src="img/'.$dta['imgcen'].'" width="70" height="55" class="round" alt="Foto">'; ?>
                        <?php if(!empty($dta['logocen'])) echo '<img src="img/'.$dta['logocen'].'" width="55" height="55" class="round" alt="Logo">'; ?>
                    </div>
                </td>
                <td>
                    <strong><?=$dta['idcen'];?> - <?=htmlspecialchars($dta['nomcen'], ENT_QUOTES);?></strong>
                    <br>
                    <small>
                        <i class="fa-solid fa-location-dot"></i> <?=htmlspecialchars($dta['dircen'], ENT_QUOTES);?><br>
                        <i class="fa-solid fa-phone"></i> <?=$dta['telcen'];?><br>
                        <?php if($dta['descen']) echo htmlspecialchars($dta['descen'], ENT_QUOTES); ?>
                    </small>

                    <div class="mt-3 d-flex flex-column gap-2">
                        <div class="border rounded p-2">
                            <div class="fw-bold small text-success">
                                <i class="fa-solid fa-people-group"></i> Asociados
                            </div>
                            <div class="mt-1">
                                <span class="badge bg-info text-dark" title="Usuarios"><?=isset($tota['total_usu'])?$tota['total_usu']:0;?> Usuarios</span>
                                <span class="badge bg-warning text-dark" title="Fichas"><?=isset($tota['total_fic'])?$tota['total_fic']:0;?> Fichas</span>
                                <span class="badge bg-secondary" title="Aulas"><?=isset($tota['total_aul'])?$tota['total_aul']:0;?> Aulas</span>
                            </div>
                        </div>
                        <div class="border rounded p-2">
                            <div class="fw-bold small text-danger">
                                <i class="fa-solid fa-user-check"></i> Candidatos
                            </div>
                            <div class="mt-1 d-flex align-items-center gap-2">
                                <span class="badge bg-success"><?=isset($tota['total_can'])?$tota['total_can']:0;?> Candidatos</span>
                                <?php if($datCand){ ?>
                                    <button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#mccan<?=$dta['idcen'];?>">
                                        <i class="fa-solid fa-users"></i> Ver
                                    </button>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="border rounded p-2">
                            <div class="fw-bold small text-primary">
                                <i class="fa-solid fa-calendar-days"></i> Proceso
                            </div>
                            <div class="mt-1 small">
                                <strong>Inscripción:</strong> <?=fmtfec($dta['fiicancen']);?> → <?=fmtfec($dta['fficancen']);?><br>
                                <strong>Propuestas:</strong> <?=fmtfec($dta['fiprocen']);?> → <?=fmtfec($dta['ffprocen']);?><br>
                                <strong>Votación:</strong> <?=fmtfec($dta['fivotcen']);?> → <?=fmtfec($dta['ffvotcen']);?>
                            </div>
                        </div>
                    </div>
                </td>
                <td class="text-center align-middle">
                    <a href="home.php?pg=<?=$pg;?>&idcen=<?=$dta['idcen'];?>&ope=edi" title="Editar">
                        <i class="fa-solid fa-pen-to-square fa-2x"></i>
                    </a>
                    <?php if($datCrl && $datCrl[0]['con']==0){ ?>
                    <a href="home.php?pg=<?=$pg;?>&idcen=<?=$dta['idcen'];?>&opera=eli" title="Eliminar" onclick="return eliminar(this);">
                        <i class="fa-solid fa-trash-can fa-2x"></i>
                    </a>
                    <?php } ?>
                </td>
            </tr>
        <?php } ?>
        </tbody>
    </table>

    <!-- Modales de candidatos -->
    <?php foreach($modCands AS $mc){
        $idc = $mc['idcen']; ?>
        <div class="modal fade" id="mccan<?=$idc;?>" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Candidatos - <?=htmlspecialchars($mc['nomcen'], ENT_QUOTES);?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <table class="table table-bordered table-sm">
                            <thead>
                                <tr>
                                    <th>Documento</th>
                                    <th>Nombre</th>
                                    <th>Teléfono</th>
                                    <th>Perfil</th>
                                    <th>Votos</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach($mc['cands'] AS $can){ ?>
                                <tr>
                                    <td><?=$can['ndocusu'];?></td>
                                    <td><?=htmlspecialchars($can['nomusu'], ENT_QUOTES);?></td>
                                    <td><?=$can['telcan']?$can['telcan']:"—";?></td>
                                    <td><?=htmlspecialchars($can['nomper'], ENT_QUOTES);?></td>
                                    <td><?=$can['votos'];?></td>
                                </tr>
                            <?php } ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    <?php } ?>
    <?php } ?>
    <br><br><br><br><br>
</div>