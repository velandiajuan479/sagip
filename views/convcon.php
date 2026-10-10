<?php require_once 'controllers/conccon.php'; ?>

<?php
$logcof  = (isset($datOne[0]['logcof'])  && file_exists('img/'.$datOne[0]['logcof']))  ? $datOne[0]['logcof']  : '';
$logbcof = (isset($datOne[0]['logbcof']) && file_exists('img/'.$datOne[0]['logbcof'])) ? $datOne[0]['logbcof'] : '';
$logwcof = (isset($datOne[0]['logwcof']) && file_exists('img/'.$datOne[0]['logwcof'])) ? $datOne[0]['logwcof'] : '';
$vc = $logcof  ? (int)@filemtime('img/'.$logcof)  : 0;
$vb = $logbcof ? (int)@filemtime('img/'.$logbcof) : 0;
$vw = $logwcof ? (int)@filemtime('img/'.$logwcof) : 0;
?>

<div class="conte">
    <?php echo titulo2("<i class='".$icono."'></i> Configuración"); ?>

    <div class="inser">
        <form id="frmins" action="home.php?pg=<?=$pg;?>" method="POST" enctype="multipart/form-data">
            <h4 class="subsec"><i class="fa-solid fa-gear"></i> Datos generales</h4>
            <div class="row">
                <div class="form-group col-md-6">
                    <label for="titcof">Nombre del software</label>
                    <input type="text" name="titcof" id="titcof" maxlength="100" class="form-control" required value="<?php if(!empty($datOne)) echo htmlspecialchars($datOne[0]['titcof'], ENT_QUOTES); ?>">
                </div>
                <div class="form-group col-md-6">
                    <label for="descof">Título Empresa</label>
                    <input type="text" name="descof" id="descof" maxlength="255" class="form-control" required value="<?php if(!empty($datOne)) echo htmlspecialchars($datOne[0]['descof'], ENT_QUOTES); ?>">
                </div>
                <div class="form-group col-md-6">
                    <label for="foocof">Pie de página</label>
                    <input type="text" name="foocof" id="foocof" maxlength="500" class="form-control" required value="<?php if(!empty($datOne)) echo htmlspecialchars($datOne[0]['foocof'], ENT_QUOTES); ?>">
                </div>
                <div class="form-group col-md-6">
                    <label for="nacta">Nombre del acta</label>
                    <input type="text" name="nacta" id="nacta" maxlength="100" class="form-control" value="<?php echo htmlspecialchars(Mcon::NACTA, ENT_QUOTES); ?>">
                </div>
                <div class="form-group col-md-6">
                    <label for="veracta">Versión del acta</label>
                    <input type="text" name="veracta" id="veracta" maxlength="100" class="form-control" value="<?php echo htmlspecialchars(Mcon::VERACTA, ENT_QUOTES); ?>">
                </div>
            </div>

            <h4 class="subsec"><i class="fa-solid fa-image"></i> Logos del SENA</h4>
            <p class="text-muted small">
                Lista de los logos oficiales. El logo <strong>a color</strong> (<code>senan.png</code>) se muestra en el sistema (menú), el <strong>en negro</strong> (<code>logoSenaB.png</code>) se usa en los documentos para imprimir y el <strong>en blanco</strong> (<code>logoSenaW.png</code>) en documentos con fondo negro. Para reemplazar un logo, seleccione el archivo y guarde los cambios.
            </p>

            <table class="table table-bordered logtab">
                <thead>
                    <tr>
                        <th style="width: 40%;">Vista previa</th>
                        <th>Logo</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <div class="mb-2">
                                <?php if($logcof): ?>
                                    <img id="pC" src="img/<?=$logcof;?>?v=<?=$vc;?>" class="logo-prev" alt="Logo a color">
                                <?php else: ?>
                                    <span id="npC" class="text-muted">Sin logo</span>
                                    <img id="pC" class="logo-prev img-oculto" alt="Logo a color">
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <div class="fw-bold">A color</div>
                            <div class="text-muted small">img/senan.png</div>
                            <div class="d-flex align-items-center gap-2 mt-2">
                                <input type="file" name="foto" id="fotoC" class="form-control form-control-sm" accept="image/png,image/jpeg,image/gif,image/webp" data-prevlogo="pC,npC">
                                <label for="fotoC" class="btn btn-warning btn-sm log-lapiz mb-0" title="Editar / Cargar nueva imagen"><i class="fa-solid fa-pen-to-square"></i></label>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="mb-2">
                                <?php if($logbcof): ?>
                                    <img id="pB" src="img/<?=$logbcof;?>?v=<?=$vb;?>" class="logo-prev" alt="Logo en negro">
                                <?php else: ?>
                                    <span id="npB" class="text-muted">Sin logo</span>
                                    <img id="pB" class="logo-prev img-oculto" alt="Logo en negro">
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <div class="fw-bold">En negro</div>
                            <div class="text-muted small">img/logoSenaB.png</div>
                            <div class="d-flex align-items-center gap-2 mt-2">
                                <input type="file" name="fotob" id="fotob" class="form-control form-control-sm" accept="image/png,image/jpeg,image/gif,image/webp" data-prevlogo="pB,npB">
                                <label for="fotob" class="btn btn-warning btn-sm log-lapiz mb-0" title="Editar / Cargar nueva imagen"><i class="fa-solid fa-pen-to-square"></i></label>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="mb-2">
                                <?php if($logwcof): ?>
                                    <img id="pW" src="img/<?=$logwcof;?>?v=<?=$vw;?>" class="logo-prev logo-prev-dark" alt="Logo en blanco">
                                <?php else: ?>
                                    <span id="npW" class="text-muted">Sin logo</span>
                                    <img id="pW" class="logo-prev logo-prev-dark img-oculto" alt="Logo en blanco">
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <div class="fw-bold">En blanco</div>
                            <div class="text-muted small">img/logoSenaW.png</div>
                            <div class="d-flex align-items-center gap-2 mt-2">
                                <input type="file" name="fotow" id="fotow" class="form-control form-control-sm" accept="image/png,image/jpeg,image/gif,image/webp" data-prevlogo="pW,npW">
                                <label for="fotow" class="btn btn-warning btn-sm log-lapiz mb-0" title="Editar / Cargar nueva imagen"><i class="fa-solid fa-pen-to-square"></i></label>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div class="form-group col-md-6 mt-3">
                <input class="btn btn-primary" type="submit" value="Enviar">
                <input type="hidden" name="ope" value="save">
                <input type="hidden" name="idcof" id="idcof" value="<?php if(!empty($datOne)) echo $datOne[0]['idcof'];?>">
            </div>
        </form>
    </div>
    <br>

    <!-- Estado actual de la configuración -->
    <?php if($datOne){ foreach($datOne AS $dta){ ?>
    <table class="table table-bordered table-striped align-middle">
        <thead>
            <tr>
                <th style="width: 220px;" class="text-center">Logo</th>
                <th>Datos</th>
                <th style="width: 70px;" class="text-center"></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center align-middle">
                    <div class="d-flex flex-column align-items-center justify-content-center">
                        <div class="mb-1 fw-bold">Logo a color</div>
                        <div class="mb-2"><span class="badge bg-success badge-log">SISTEMA</span></div>
                        <?php if(!empty($dta['logcof']) && file_exists('img/'.$dta['logcof'])){ ?>
                            <img src="img/<?=$dta['logcof'];?>?v=<?=$vc;?>" class="logo-prev d-block mx-auto" alt="Logo a color">
                        <?php }else{ ?>
                            <div class="text-muted">Sin logo</div>
                        <?php } ?>
                    </div>
                </td>
                <td class="align-middle">
                    <strong class="fs-6 text-success"><?=htmlspecialchars($dta['titcof'], ENT_QUOTES);?></strong>
                    <br>
                    <small>
                        <strong>Título empresa:</strong> <?=htmlspecialchars($dta['descof'], ENT_QUOTES);?><br>
                        <strong>Pie de página:</strong> <?=htmlspecialchars($dta['foocof'], ENT_QUOTES);?><br>
                        <strong>Acta / Versión:</strong> <?=htmlspecialchars(Mcon::NACTA.' '.Mcon::VERACTA, ENT_QUOTES);?>
                    </small>
                </td>
                <td class="text-center align-middle">
                    <a href="home.php?pg=<?=$pg;?>&idcof=<?=$dta['idcof'];?>&ope=edi" title="Editar">
                        <i class="fa-solid fa-pen-to-square fa-2x"></i>
                    </a>
                </td>
            </tr>
            <tr>
                <td class="text-center align-middle">
                    <div class="d-flex flex-column align-items-center justify-content-center">
                        <div class="mb-1 fw-bold">Logo en negro</div>
                        <div class="mb-2"><span class="badge bg-dark badge-log">DOCUMENTOS</span></div>
                        <?php if(!empty($dta['logbcof']) && file_exists('img/'.$dta['logbcof'])){ ?>
                            <img src="img/<?=$dta['logbcof'];?>?v=<?=$vb;?>" class="logo-prev d-block mx-auto" alt="Logo en negro">
                        <?php }else{ ?>
                            <div class="text-muted">Sin logo</div>
                        <?php } ?>
                    </div>
                </td>
                <td class="align-middle">
                    <strong class="fs-6 text-success"><?=htmlspecialchars($dta['titcof'], ENT_QUOTES);?></strong>
                    <br>
                    <small>
                        <strong>Título empresa:</strong> <?=htmlspecialchars($dta['descof'], ENT_QUOTES);?><br>
                        <strong>Pie de página:</strong> <?=htmlspecialchars($dta['foocof'], ENT_QUOTES);?><br>
                        <strong>Acta / Versión:</strong> <?=htmlspecialchars(Mcon::NACTA.' '.Mcon::VERACTA, ENT_QUOTES);?>
                    </small>
                </td>
                <td class="text-center align-middle">
                    <a href="home.php?pg=<?=$pg;?>&idcof=<?=$dta['idcof'];?>&ope=edi" title="Editar">
                        <i class="fa-solid fa-pen-to-square fa-2x"></i>
                    </a>
                </td>
            </tr>
            <tr>
                <td class="text-center align-middle">
                    <div class="d-flex flex-column align-items-center justify-content-center">
                        <div class="mb-1 fw-bold">Logo en blanco</div>
                        <div class="mb-2"><span class="badge bg-secondary badge-log">FONDO OSCURO</span></div>
                        <?php if(!empty($dta['logwcof']) && file_exists('img/'.$dta['logwcof'])){ ?>
                            <img src="img/<?=$dta['logwcof'];?>?v=<?=$vw;?>" class="logo-prev logo-prev-dark d-block mx-auto" alt="Logo en blanco">
                        <?php }else{ ?>
                            <div class="text-muted">Sin logo</div>
                        <?php } ?>
                    </div>
                </td>
                <td class="align-middle">
                    <strong class="fs-6 text-success"><?=htmlspecialchars($dta['titcof'], ENT_QUOTES);?></strong>
                    <br>
                    <small>
                        <strong>Título empresa:</strong> <?=htmlspecialchars($dta['descof'], ENT_QUOTES);?><br>
                        <strong>Pie de página:</strong> <?=htmlspecialchars($dta['foocof'], ENT_QUOTES);?><br>
                        <strong>Acta / Versión:</strong> <?=htmlspecialchars(Mcon::NACTA.' '.Mcon::VERACTA, ENT_QUOTES);?>
                    </small>
                </td>
                <td class="text-center align-middle">
                    <a href="home.php?pg=<?=$pg;?>&idcof=<?=$dta['idcof'];?>&ope=edi" title="Editar">
                        <i class="fa-solid fa-pen-to-square fa-2x"></i>
                    </a>
                </td>
            </tr>
        </tbody>
    </table>
    <?php } } ?>
    <br><br><br><br><br>
</div>