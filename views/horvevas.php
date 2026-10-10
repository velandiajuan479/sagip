<?php require_once 'controllers/horcevas.php'; ?>

<div class="conte">

    <?php echo titulo2("<i class='".$icono."'></i> Evaluación Sesión",1); ?>

    <div class="inser">

        <form id="frmins" action="home.php?pg=<?=$pg;?>" method="POST">

            <div class="row">

                <div class="form-group col-md-6">
                    <label for="idses">Sesión</label>

                    <input
                        type="number"
                        name="idses"
                        id="idses"
                        class="form-control"
                        required
                        value="<?php
                            if(isset($datOne) && !empty($datOne))
                                echo $datOne[0]['idses'];
                        ?>"
                        <?php
                            if(isset($datOne) && !empty($datOne))
                                echo "readonly";
                        ?>
                    >
                </div>


                <div class="form-group col-md-6">
                    <label for="idusu">Usuario</label>

                    <input
                        type="number"
                        name="idusu"
                        id="idusu"
                        class="form-control"
                        required
                        value="<?php
                            if(isset($datOne) && !empty($datOne))
                                echo $datOne[0]['idusu'];
                        ?>"
                    >
                </div>


                <div class="form-group col-md-6">
                    <label for="comeva">Comentario</label>

                    <textarea
                        name="comeva"
                        id="comeva"
                        class="form-control"
                        rows="3"
                        required
                    ><?php
                        if(isset($datOne) && !empty($datOne))
                            echo $datOne[0]['comeva'];
                    ?></textarea>

                </div>


                <div class="form-group col-md-6">

                    <label for="valeva">Valoración</label>

                    <select
                        name="valeva"
                        id="valeva"
                        class="form-select"
                        required
                    >

                        <option value="">
                            Despliegue el menú y seleccione la opción deseada
                        </option>

                        <option
                            value="1"
                            <?php
                            if(
                                isset($datOne) &&
                                !empty($datOne) &&
                                $datOne[0]['valeva'] == 1
                            )
                                echo "selected";
                            ?>
                        >
                            Se vio
                        </option>

                        <option
                            value="2"
                            <?php
                            if(
                                isset($datOne) &&
                                !empty($datOne) &&
                                $datOne[0]['valeva'] == 2
                            )
                                echo "selected";
                            ?>
                        >
                            No se vio
                        </option>

                        <option
                            value="3"
                            <?php
                            if(
                                isset($datOne) &&
                                !empty($datOne) &&
                                $datOne[0]['valeva'] == 3
                            )
                                echo "selected";
                            ?>
                        >
                            Se vio parcialmente
                        </option>

                    </select>

                </div>


                <div class="form-group col-md-6">

                    <input
                        type="submit"
                        class="btn btn-primary"
                        value="<?php
                            if(isset($datOne) && !empty($datOne))
                                echo 'Actualizar';
                            else
                                echo 'Registrar';
                        ?>"
                    >

                    <input
                        type="hidden"
                        name="opera"
                        value="<?php
                            if(isset($datOne) && !empty($datOne))
                                echo 'Actualizar';
                            else
                                echo 'Insertar';
                        ?>"
                    >

                    <br><br><br>

                </div>

            </div>

        </form>

    </div>

</div>


<table id="example" class="table table-striped" style="width:100%">

    <thead>

        <tr>
            <th>Sesión</th>
            <th>Usuario</th>
            <th>Comentario</th>
            <th>Valoración</th>
            <th></th>
        </tr>

    </thead>


    <tbody>

        <?php

        if(isset($dat) && !empty($dat)){

            foreach($dat as $d){

        ?>

                <tr>

                    <td>
                        <?=isset($d['idses']) ? $d['idses'] : '';?>
                    </td>

                    <td>
                        <?=isset($d['idusu']) ? $d['idusu'] : '';?>
                    </td>

                    <td>
                        <?=isset($d['comeva']) ? $d['comeva'] : '';?>
                    </td>

                    <td style="text-align:center;">

                        <?php

                        if(isset($d['valeva'])){

                            if($d['valeva'] == 1){

                                echo "Se vio";

                            }elseif($d['valeva'] == 2){

                                echo "No se vio";

                            }elseif($d['valeva'] == 3){

                                echo "Se vio parcialmente";

                            }else{

                                echo $d['valeva'];

                            }

                        }

                        ?>

                    </td>

                    <td style="text-align:right;">

                        <?php

                        if(isset($_SESSION["idses"]) && $_SESSION["idses"] == 1){

                        ?>

                            <a
                                href="home.php?pg=<?=$pg;?>&ideva=<?=isset($d['ideva']) ? $d['ideva'] : '';?>&ope=edi"
                                title="Actualizar"
                            >
                                <i class="fa-solid fa-pen-to-square fa-2x"></i>
                            </a>

                        <?php

                        }

                        ?>

                        <a
                            href="home.php?pg=<?=$pg;?>&ideva=<?=isset($d['ideva']) ? $d['ideva'] : '';?>&opera=Eliminar"
                            title="Eliminar"
                            onclick="return eliminar();"
                        >
                            <i class="fa-solid fa-trash-can fa-2x"></i>
                        </a>

                    </td>

                </tr>

        <?php

            }

        }

        ?>

    </tbody>


    <tfoot>

        <tr>
            <th>Sesión</th>
            <th>Usuario</th>
            <th>Comentario</th>
            <th>Valoración</th>
            <th></th>
        </tr>

    </tfoot>

</table>

<br><br><br><br>