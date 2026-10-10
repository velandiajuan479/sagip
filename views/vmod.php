<?php require_once 'controllers/cmod.php'; ?>

<div class="conte">
    <?php echo titulo2("<i class='" . $icono . "'></i> Módulo"); ?>
    
    <div class="inser">
        <form id="frmins" action="home.php?pg=<?= $pg; ?>" method="POST" enctype="multipart/form-data">
            <div class="row">
                <div class="form-group col-md-6">
                    <label for="nommod">Nombre:</label>
                    <input type="text" name="nommod" id="nommod" class="form-control" required
                        value="<?php if ($datOne)
                            echo $datOne[0]['nommod']; ?>">
                </div>
                <div class="form-group col-md-6">
                    <label for="imgmod">Imagen:</label>
                    <?php if ($datOne and $datOne[0]['imgmod']) { ?>
                        <input type="hidden" name="imgmod" id="imgmod" value="<?= $datOne[0]['imgmod']; ?>">
                    <?php } ?>
                    <input type="file" name="foto" id="imgmod" class="form-control" accept="image/png, image/jpeg">
                </div>
                <div class="form-group col-md-6">
                    <label for="actmod">Activo:</label>
                    <select id="actmod" class="form-select" name="actmod"
                        value="<?php if ($datOne)
                            echo $datOne[0]['iddom']; ?>">
                        <option value="1">Si</option>
                        <option value="2">No</option>
                    </select>
                </div>
                <div class="form-group col-md-6">
                    <label for="idper">Perfil Predeterminado:</label>
                    <select id="idper" class="form-select" name="idper">
                        <option value="0">Sin Perfil</option>
                        <?php if ($datPer) {
                            foreach ($datPer as $dtp) { ?>
                                <option value="<?= $dtp['idper']; ?>" <?php if ($datOne and $datOne[0]['idper'] == $dtp['idper'])
                                    echo "selected"; ?>>
                                    <?= $dtp['nomper']; ?>
                                </option>
                            <?php }
                        } ?>
                    </select>
                </div>

                <div class="form-group col-md-6">
                    <input type="submit" class="btn btn-primary"
                        value="<?php if ($datOne)
                            echo "Actualizar";
                        else
                            echo "Registrar"; ?>">
                    <input type="hidden" name="opera"
                        value="<?php if ($datOne)
                            echo "Actualizar";
                        else
                            echo "Insertar"; ?>">
                    <input type="hidden" name="idmod" value="<?php if ($datOne)
                        echo $datOne[0]['idmod']; ?>">
                </div>
            </div>
        </form>
    </div>
</div>
<table id="example" class="table table-striped" style="width:100%">
    <thead>
        <tr>
            <th>Imagen</th>
            <th>Módulo</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php if ($dat) {
            foreach ($dat as $dt) { ?>
                <tr>
                    <td style="width:170px; text-align:center;">
                        <span style="opacity: 0;">
                            <?= $dt['actmod']; ?>
                        </span>
                        <?php if (file_exists($dt['imgmod'])) { ?>

                            <img src="<?= $dt['imgmod']; ?>" width="100px">
                        <?php } ?>
                    </td>
                    <td>
                        <?= $dt['idmod']; ?> -
                        <?= $dt['nommod']; ?>
                        <?php if ($dt['nomper']) { ?>
                            <br><small>
                                <strong>Perfil Predeterminado:</strong>
                                <?= $dt['nomper']; ?>
                            </small>
                        <?php } ?>
                    </td>
                    <td style="text-align:right;">
                        <span style="opacity: 0;">
                            <?= $dt['actmod']; ?>
                        </span>
                        <?php if($dt['actmod']==1){ ?>
                            <a href="home.php?pg=<?=$pg;?>&idmod=<?=$dt['idmod'];?>&opera=acti&actmod=2">
                                <i class="fa-solid fa-circle-check fa-2x"></i>
                            </a>
                        <?php }else{ ?>
                            <a href="home.php?pg=<?=$pg;?>&idmod=<?=$dt['idmod'];?>&opera=acti&actmod=1">
                                <i class="fa-solid fa-circle-xmark fa-2x" style="color: #ff0000;"></i>
                            </a>
                        <?php } ?>
                        <?php 
                                $mxp = $mmod->getMxp($dt['idmod']);
                                if($mxp && $mxp[0]['can']==0){
                            ?>
                                <a href="home.php?pg=<?= $pg; ?>&idmod=<?= $dt['idmod']; ?>&opera=Eliminar" title="Eliminar"
                                onclick="return Eliminar();">
                                    <i class="fa-solid fa-trash-can fa-2x"></i>
                                </a>
                            <?php } ?>
                        
                        <a href="home.php?pg=<?= $pg; ?>&idmod=<?= $dt['idmod']; ?>&ope=edi" title="Actualizar">
                            <i class="fa-solid fa-pen-to-square fa-2x"></i>
                        </a>
                    </td>
                </tr>
            <?php }
        } ?>
    </tbody>
    <tfoot>
        <tr>
            <th>Imagen</th>
            <th>Módulo</th>
            <th></th>
        </tr>
    </tfoot>
</table>
<br><br>