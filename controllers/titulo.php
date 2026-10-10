<?php
    function tit($ti,$ic,$arc,$pg,$alto,$tipo=1){
        $txt = '';
        $txt .= '<div class="err"></div>';
        $txt .= '<div class="tit">';
            $txt .= '<div class="tita">';
            $txt .= '<i class="'.$ic.'"></i>';
            $txt .= ''.$ti;
            $txt .= '<hr class="hrtit">';
            $txt .= '</div>';
        
/*    if($tipo==1)
        $tx = 'Nuevo';
    else
        $tx = 'Nueva';*/
    $txt .= '<div style="text-align: center;">';
        $txt .= '<h2>'.$ti.'</h2>';
/*        if(isset($_GET["pg"])){
            if($_GET["pg"]!=290 AND $_GET["pg"]!=291 AND $_GET["pg"]!=212){
                $txt .= '<button id="mos" class="btn btn-dark" onclick="ocultar(1,\''.$alto.'\');" title="'.$tx.' '.$ti.'">';
                    $txt .= '<i class="'.$ic.'" style="color:#fff;"></i>';
                    //$txt .= 'Nuevo';
                $txt .= '</button>';
            }
            $txt .= '<a href="'.$arc.'?pg='.$pg.'" id="ocu">';
                $txt .= '<input type="button" id="ocu" class="btn-close" title="Cerrar">';
            $txt .= '</a>';
        }*/
    $txt .= '</div>';
    return $txt;
    }
// function ManejoError($e){
//     if(strpos($e->getMessage(),'1115')){
//                 echo '<script>err("No se puede eliminar este registro. Por que se encuentra relacionado en otra opcion.");</script>';
//     }elseif(strpos($e->getMessage(),'1116')){
//                 echo '<script>err("Registro duplicado. Intente nuevamente con otro numero de identificacion o comuniquese con el administrador del sistema.");</script>';

//             }else{
//                 echo '<script>err("Se genero un error comuniquese con el administrador del sistema.");</script>';
//     }
// }  


function sbtit($ti,$ic,$arc,$pg,$alto,$cdvl,$tipo=1){
    $txt = '';
    $txt .= '<div>';
        $txt .= '<h2>'.$ti.'</h2>';
        /*$txt .= '<button id="mos'.$cdvl.'" class="btn btn-dark mos" onclick="ocudt(1,\''.$alto.'\',\''.$cdvl.'\');" title="'.$ti.'">';
            $txt .= '<i class="'.$ic.'"></i>';
        $txt .= '</button>';*/
        $txt .= '<button id="ocu'.$cdvl.'" class="btn-close ocu" onclick="ocudt(2,\'0px\',\''.$cdvl.'\');" title="Cerrar">';
            //$txt .= '<i class="'.$ic.'"></i>';
        $txt .= '</button>';
    $txt .= '</div>';
    return $txt;
}
    
?>