<?php

require_once ('models/votmnvot.php');

$mnvot = new Mnvot();
$idusu = isset($_POST['idusu']) ? $_POST['idusu']:NULL;
$actusu = isset($_POST['actusu']) ? $_POST['actusu']:NULL;
$idfic = isset($_POST['idfic']) ? $_POST['idfic']:NULL;

$ope = isset($_REQUEST['ope']) ? $_REQUEST['ope']:NULL;

if($ope=="save" OR $ope=="edit"){
    $mnvot->setIdfic($idfic);
	$mnvot->setActusu($actusu);
	$mnvot->setIdusu($idusu);
    if($ope=="edit") $mnvot->edit();
	else $mnvot->save();
}
if($ope=="act" && $idusu && $actusu){
	$mnvot->setActusu($actusu);
	$mnvot->editAct();
}
// Mostrar todos los datos
$dat = $mnvot->getAll();
$gaf = $mnvot->getGraphic();

$no_votaron = isset($gaf[0]['no_votaron']) ? (int)$gaf[0]['no_votaron'] : 0;
$votaron = isset($gaf[0]['votaron']) ? (int)$gaf[0]['votaron'] : 0;
$votos_blanco = isset($gaf[0]['votos_blanco']) ? (int)$gaf[0]['votos_blanco'] : 0;
$total_personas = isset($gaf[0]['total_personas']) ? (int)$gaf[0]['total_personas'] : 0;

$porc_votaron = $total_personas > 0 ? round(($votaron / $total_personas) * 100, 1) : 0;
$porc_no_votaron = $total_personas > 0 ? round(($no_votaron / $total_personas) * 100, 1) : 0;
$porc_blanco = $total_personas > 0 ? round(($votos_blanco / $total_personas) * 100, 1) : 0;

?>
<script>
// Filtro por estado aplicado solo a la tabla #example
var filtroEstadoVotantes = 'todos';

$.fn.dataTable.ext.search.push(function (settings, searchData, dataIndex, rowData, counter) {
    if (!settings.nTable || settings.nTable.id !== 'example') return true;
    var estado = $(settings.aoData[dataIndex].nTr).attr('data-estado');
    if (filtroEstadoVotantes === 'todos') return true;
    return (filtroEstadoVotantes === 'voto' && estado === '1') ||
           (filtroEstadoVotantes === 'novoto' && estado === '0');
});

function aplicarFiltroEstado(filtro) {
    filtroEstadoVotantes = filtro;
    document.querySelectorAll('.btn-filtro-estado').forEach(function (btn) {
        btn.classList.toggle('active', btn.getAttribute('data-estado-filtro') === filtro);
    });
    if (typeof $.fn.DataTable !== 'undefined' && $.fn.DataTable.isDataTable('#example')) {
        $('#example').DataTable().draw();
    }
}

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.btn-filtro-estado').forEach(function (btn) {
        btn.addEventListener('click', function () {
            aplicarFiltroEstado(this.getAttribute('data-estado-filtro'));
        });
    });
    setTimeout(function () { aplicarFiltroEstado('todos'); }, 300);
});
</script>