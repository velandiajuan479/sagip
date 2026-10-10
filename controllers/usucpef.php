<?php 
    require_once ('models/usumpef.php');    
    $mper = new Usumpef();

    $idper = isset($_REQUEST['idper']) ? $_REQUEST['idper']:NULL;
    $nomper = isset($_POST['nomper']) ? $_POST['nomper']:NULL;    
    $idpag = isset($_POST['idpag']) ? $_POST['idpag']:NULL;
    $idmod = isset($_POST['idmod']) ? $_POST['idmod']:NULL;
    $mdl = isset($_POST['mdl']) ? $_POST['mdl']:NULL;
    $opera = isset($_REQUEST['opera']) ? $_REQUEST['opera']:NULL;
    $datOne = NULL;
    
    $mper->setIdper($idper);
    if($opera=="savepxp"){
        if($idper) $mper->delPxP();
        if($mdl){ foreach($mdl AS $md){
            $mper->setIdpag($md);
            $mper->savePxP();
        }}
        echo "<script>window.location='home.php?pg=1112';</script>";
    }    

    if($opera=="save"){
        $mper->setNomper($nomper);
        $mper->setIdpag($idpag);
        $mper->setIdmod($idmod);
        if(!$idper) $mper->save(); else $mper->edit();
    }    
    if($opera=="edi" && $idper) $datOne = $mper->getOne();

    $dat = $mper->getAll();
    $datpag = $mper->getPag();
    $datmod= $mper->getMod();

    //DASHBOARD
    $dash = $mper->getDashboard();

    //BUSCADOR DE PERFILES (por nombre de perfil)
    $busqueda = isset($_REQUEST['busqueda']) ? trim($_REQUEST['busqueda']) : NULL;
    $datBusqueda = NULL;
    if($busqueda){
        $datBusqueda = $mper->selBusqueda($busqueda);
    }

function modal($id, $nm, $mt, $pg, $dms){        
    $html = '';
    $html .= '<div class="modal" id="myModal'.$id.'" tabindex="-1" role="dialog" aria-labelledby="" aria-hidden="true">';
        $html .= '<div class="modal-dialog">';
            $html .= '<div class="modal-content">';                
                    $html .= '<div class="modal-header">';
                        $html .= '<h3 class="modal-title">Páginas - '.$nm.'</h3>';
                        $html .= '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>';
                    $html .= '</div>';
                    
                        $html .='<form action="home.php?pg='.$pg.'" method="POST">';
                        $html .= '<div class="modal-body">';        
                            $html .= '<input type="hidden" value="'.$id.'" name="idper">';
                            $html .= '<div class="row">';

                            if($mt){ foreach ($mt as $dtm) {
                                $html .= '<div class="dpag form-group col-md-6" style="margin-bottom: 0px; text-align:left !important;">';                                    
                                    $pos = strpos($dms,$dtm['idpag']);
                                    $html .= '<i class=" fa '.$dtm['icopag'].'" style="color: #117f09;"></i>';
                                    $html .= '<input type="checkbox" name="mdl[]" value="'.$dtm['idpag'].'"';
                                        if($pos>-1) $html .= " checked ";
                                    $html .= '> ';
                                    $html .= $dtm['nompag']." ";
                                $html .= '</div>';
                            }}
                            $html .= '</div>';
                        $html .= '</div>';                                                        
                    $html .= '<div class="modal-footer">';
                        $html .= '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>';
                        $html .= '<input type="hidden" value="savepxp" name="opera">';
                        $html .= '<input type="submit" class="btn btn-primary" value="Guardar">';
                    $html .= '</div>';
                $html .= '</form>';
            $html .= '</div>';
        $html .= '</div>';
    $html .= '</div>';
    echo $html;
    

}
    
?>