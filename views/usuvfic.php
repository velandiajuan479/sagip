<?php require_once 'controllers/usucfic.php'; ?>

<div class="conte">
    <?php echo titulo2("<i class='".$icono."'></i> Ficha",1); ?>

    <?php if(isset($dash) && $dash): ?>
    <div class="row my-4 g-4 justify-content-center">
        <div class="col-12 col-md-5">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #3e4a3d;">Total de Fichas</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #0b1c30;"><?= $dash[0]['total']; ?></span>
                        </div>
                    </div>
                    <div class="kpi-icon-box" style="background-color: #b5dcfe; color: #3c627f;">
                        <i class="fa-solid fa-layer-group fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 11px; color: #3c627f;">
                        <i class="fa-solid fa-layer-group" style="font-size: 12px;"></i> Registradas en el sistema
                    </span>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-5">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #006b29;">Fichas Activas</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #006b29;"><?= $dash[0]['activas']; ?></span>
                        </div>
                    </div>
                    <div class="kpi-icon-box" style="background-color: #7afd88; color: #006b24;">
                        <i class="fa-solid fa-circle-check fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="fw-semibold d-inline-flex align-items-center gap-2" style="font-size: 11px; color: #006b29;">
                        <span class="pulse-dot"></span> En formación actualmente
                    </span>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?> 

    <div class="inser">
        <form id="frmins" class="filter-toolbar-container" action="home.php?pg=<?=$pg;?>" method="POST">
            <h2><i class="fa-solid fa-file-circle-plus"></i> <?php if($datOne) echo "Actualizar ficha"; else echo "Nueva ficha"; ?></h2>
            <div class="row">
                <div class="form-group col-md-6">
                    <label for="idfic" class="fw-semibold">Ficha</label>
                    <input type="text" name="idfic" id="idfic" class="form-control" maxlength="20" required value="<?php if($datOne) echo $datOne[0]['idfic']; ?>" <?php if($datOne) echo " readonly "; ?>>
                </div>
                <div class="form-group col-md-6">
                    <label for="nomfic" class="fw-semibold">Nombre de la ficha</label>
                    <input type="text" name="nomfic" id="nomfic" maxlength="70" class="form-control" required value="<?php if($datOne) echo $datOne[0]['nomfic']; ?>">
                </div>
                <div class="form-group col-md-4">
                    <label for="jornada" class="fw-semibold">Jornada</label>
                    <select name="jornada" id="jornada" class="form-select">
                        <?php
                            if($djo){
                                foreach($djo AS $dj){
                        ?>
                                    <option value="<?=$dj['idval'];?>" 
                                    <?php if($datOne AND $datOne[0]['jornada']==$dj['idval']) echo " selected "; ?>
                                    >
                                        <?=$dj['nomval'];?>
                                    </option>
                        <?php 
                                }
                            } 
                        ?>
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <label for="mun" class="fw-semibold">Departamento</label>
                    <select class="form-select" onchange="recCiudad(this.value);">
                    <?php if($dubi){ foreach($dubi AS $ub){ ?>
                        <option value="<?=$ub['codubi'];?>"><?=$ub['nomubi'];?></option>
                    <?php }} ?>
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <label for="mun" class="fw-semibold">Municipio</label>
                    <div id="reloadMun">
                        <input type="text" name="mun" id="mun" maxlength="255" class="form-control" value="<?php if($datOne) echo $datOne[0]['mun']; ?>">
                    </div>
                </div>
                <div class="form-group col-md-6">
                    <label for="idcen" class="fw-semibold">Centro</label>
                    <select name="idcen" id="idcen" class="form-select">
                        
                        <?php
                            if($dce){
                                foreach($dce AS $dj){
                        ?>
                                    <option value="<?=$dj['idcen'];?>" 
                                    <?php if($datOne AND $datOne[0]['idcen']==$dj['idcen']) echo " selected "; ?>
                                    >
                                        <?=$dj['nomcen'];?>
                                    </option>
                        <?php 
                                }
                            } 
                        ?>
                    </select>
                </div>
                <div class="form-group col-md-6">
                    <label for="finific" class="fw-semibold">Inicio de ficha</label>
                    <input type="date" name="finific" id="finific" class="form-control" required value="<?php if($datOne) echo $datOne[0]['finific']; ?>">
                </div>
                <div class="form-group col-md-6">
                    <label for="ffinfic" class="fw-semibold">Fin de ficha</label>
                    <input type="date" name="ffinfic" id="ffinfic" class="form-control" required value="<?php if($datOne) echo $datOne[0]['ffinfic']; ?>">
                </div>
                <div class="form-group col-md-12 d-flex align-items-center gap-2 mt-2">
                    <input type="submit" class="btn btn-success" value="<?php if($datOne) echo "Actualizar"; else echo "Registrar"; ?>">
                    <input type="hidden" name="opera" value="<?php if($datOne) echo "Actualizar"; else echo "Insertar"; ?>">
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Toolbar de Control, Filtros y Búsqueda (Diseño Toolbar Institucional) -->
<div class="filter-toolbar-container">
    <div class="toolbar-left-group">
        <div class="filter-pills-group" id="tabFichas" role="tablist">
            <button class="btn-filter-pill active" id="fic-general-tab" type="button" role="tab" onclick="filterFicTab('general')">
                <span class="pill-dot" style="background-color: #3c627f;"></span>
                <span>General</span>
                <span class="pill-count"><?= count($dat ?? []); ?></span>
            </button>
            <button class="btn-filter-pill" id="fic-titulada-tab" type="button" role="tab" onclick="filterFicTab('titulada')">
                <span class="pill-dot" style="background-color: #006b29;"></span>
                <span>Titulada</span>
                <span class="pill-count"><?= count($datTitulada ?? []); ?></span>
            </button>
            <button class="btn-filter-pill" id="fic-complementaria-tab" type="button" role="tab" onclick="filterFicTab('complementaria')">
                <span class="pill-dot" style="background-color: #ba1a1a;"></span>
                <span>Complementaria</span>
                <span class="pill-count"><?= count($datComplementaria ?? []); ?></span>
            </button>
        </div>

        <div class="toolbar-divider d-none d-sm-block"></div>

        <div class="toolbar-export-buttons">
            <button type="button" class="toolbar-export-btn" title="Copiar al portapapeles" onclick="triggerFicExport('copy')">
                <i class="fa-solid fa-copy"></i>
            </button>
            <button type="button" class="toolbar-export-btn" title="Descargar CSV" onclick="triggerFicExport('csv')">
                <i class="fa-solid fa-file-csv"></i>
            </button>
            <button type="button" class="toolbar-export-btn" title="Exportar a Excel" onclick="triggerFicExport('excel')">
                <i class="fa-solid fa-file-excel"></i>
            </button>
        </div>
    </div>

    <div class="toolbar-search-sort-group">
        <div class="toolbar-search-box">
            <i class="fa-solid fa-magnifying-glass icoldiz"></i>
            <input type="text" id="customFichaSearch" placeholder="Buscar por número o nombre de ficha" onkeyup="syncFicSearch(this.value)">
        </div>
    </div>
</div>

<?php
function renderTablaFichas($dataset, $tableId, $tituloTabla, $mfic, $pg) {
?>
    <h2 class="mt-4 mb-3">
        <i class="fa-solid fa-list-check"></i> <?= $tituloTabla; ?>
    </h2>
    <table id="<?= $tableId; ?>" class="table table-striped" style="width:100%">
        <thead>
            <tr>
                <th>Ficha</th>
                <th>No. Apr.</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($dataset) {
                foreach ($dataset as $d) {
            ?>
                    <tr>
                        <td>
                            <?=$d['idfic'];?> - <?=$d['nomfic'];?><br>
                            <small>
                                <strong>Jornada: </strong><?=$d['nomval'];?>
                                <?php if($d['mun']){ ?>
                                    <strong>Municipio: </strong><?=$d['mun'];?>
                                <?php } ?>
                                <br>
                                <strong>Centro: </strong><?=$d['idcen']." ".$d['nomcen']; ?>
                                <br>
                                <strong>Inicio: </strong><?=$d['finific'];?>
                                -
                                <strong>Fin: </strong><?=$d['ffinfic'];?>
                            </small>
                        </td>
                        <td style="text-align: center;">
                            <?php
                                $dtnApr = $mfic->selNoApr($d['idfic']);
                                $canApr = isset($dtnApr[0]['can']) ? $dtnApr[0]['can'] : 0;
                            ?>
                            <span style="font-size: 30px;font-weight: bold;"><?=$canApr;?></span>
                        </td>
                        <td style="text-align: right;">
                           <?php
                                $actas = $mfic->getAllActasFic($d['idfic']);
                                if ($actas && count($actas) > 0) {
                                    foreach ($actas as $acta) {
                                        echo '<a href="views/pdffic.php?idactdc='.$acta['idactdc'].'&idfic='.$d['idfic'].'" target="_blank"
                                                title="Imprimir Acta #'.$acta['numact'].'">
                                                <i class="fa-solid fa-print fa-2x"></i>
                                            </a>';
                                    }
                                }
                                echo '<i class="fa-solid fa-file-circle-plus fa-2x" 
                                    title="Crear Actas" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#CrearActModal'.$d['idfic'].'"></i>';
                                echo modActCierreModal(
                                    $d['idfic'], 
                                    $d['nomfic'], 
                                    $d['finific'], 
                                    $d['ffinfic']." ".$d['nomval'], 
                                    $pg
                                );
                           ?>
                            <a href="views/vrfic.php?fic=<?=$d['idfic'];?>" title="Imprimir" target="_blank">
                                <i class="fa-solid fa-print fa-2x"></i>
                            </a>
                            <?php if(isset($_SESSION["idper"]) && ($_SESSION["idper"]==1 OR $_SESSION["idper"]==18)){ ?>
                            <a href="home.php?pg=<?=$pg;?>&idfic=<?=$d['idfic'];?>&ope=edi" title="Actualizar">
                                <i class="fa-solid fa-pen-to-square fa-2x"></i></a>
                            <?php } ?>
                            <?php 
                            $res = $mfic->selNoApr($d['idfic']);
                            $totalAprendices = isset($res[0]['can']) ? (int)$res[0]['can'] : 0;
                            if ($totalAprendices === 0) { 
                            ?>
                            <a href="home.php?pg=<?= $pg; ?>&idfic=<?= $d['idfic']; ?>&opera=Eliminar" 
                               title="Eliminar" 
                               onclick="return eli(this);">
                                <i class="fa-solid fa-trash-can fa-2x"></i>
                            </a>
                            <?php } ?>
                        </td>
                    </tr>
            <?php
                }
            }
            ?>
        </tbody>
        <tfoot>
            <tr>
                <th>Ficha</th>
                <th>No. Apr.</th>
                <th>Acciones</th>
            </tr>
        </tfoot>
    </table>
    <br>
<?php
}

// 1. Tabla: ÚLTIMAS 50 FICHAS REGISTRADAS
?>
<div class="fic-sec" id="secGeneral">
    <?php renderTablaFichas($dat, "example_general", "Últimas 50 Fichas Registradas", $mfic, $pg); ?>
</div>

<div class="fic-sec" id="secTitulada" style="display: none;">
    <?php renderTablaFichas($datTitulada, "example_titulada", "Fichas de Formación Titulada (Técnicos y Tecnólogos) - Oferta Regular (Últimas 50)", $mfic, $pg); ?>
</div>

<div class="fic-sec" id="secComplementaria" style="display: none;">
    <?php renderTablaFichas($datComplementaria, "example_complementarias", "Cursos Complementarios (Últimas 50 Fichas)", $mfic, $pg); ?>
</div>
<?php ?>

<script>
const ficTables = {
    general: '#example_general',
    titulada: '#example_titulada',
    complementaria: '#example_complementarias'
};
let ficActive = 'general';

window.filterFicTab = function(tab) {
    ficActive = tab;
    Object.keys(ficTables).forEach(function (key) {
        const sec = document.getElementById('sec' + key.charAt(0).toUpperCase() + key.slice(1));
        if (sec) sec.style.display = (key === tab) ? '' : 'none';
        const pill = document.getElementById('fic-' + key + '-tab');
        if (pill) pill.classList.toggle('active', key === tab);
    });
    setTimeout(function () {
        if (typeof $.fn.dataTable !== 'undefined') {
            $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
        }
    }, 150);
};

window.syncFicSearch = function(query) {
    if (typeof $.fn.dataTable !== 'undefined') {
        Object.keys(ficTables).forEach(function (key) {
            const $t = $(ficTables[key]);
            if ($t.length && $.fn.DataTable.isDataTable($t)) {
                $t.DataTable().search(query).draw();
            }
        });
    }
};

window.triggerFicExport = function(type) {
    const $t = $(ficTables[ficActive]);
    if ($t.length && typeof $.fn.DataTable !== 'undefined' && $.fn.DataTable.isDataTable($t)) {
        const dtApi = $t.DataTable();
        if (type === 'copy') dtApi.button('.buttons-copy').trigger();
        else if (type === 'csv') dtApi.button('.buttons-csv').trigger();
        else if (type === 'excel') dtApi.button('.buttons-excel').trigger();
    }
};

$(document).ready(function () {
    const tableConfigs = [
        { id: '#example_general', tipo: 'all' },
        { id: '#example_titulada', tipo: 'titulada' },
        { id: '#example_complementarias', tipo: 'complementaria' }
    ];

    tableConfigs.forEach(function(cfg) {
        if ($(cfg.id).length) {
            if ($.fn.DataTable.isDataTable(cfg.id)) {
                $(cfg.id).DataTable().destroy();
            }

            $(cfg.id).DataTable({
                "processing": true,
                "serverSide": true,
                "ajax": {
                    "url": "controllers/usucfic.php?ajax_dt=1&tipo=" + cfg.tipo,
                    "type": "POST"
                },
                "pageLength": 50,
                "searching": true,
                "language": {
                    "lengthMenu": "Mostrar _MENU_ registros",
                    "zeroRecords": "No se encontraron resultados",
                    "info": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
                    "infoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
                    "infoFiltered": "(filtrado de un total de _MAX_ registros)",
                    "sSearch": "Buscar en todas las fichas:",
                    "oPaginate": {
                        "sFirst": "Primero",
                        "sLast": "Último",
                        "sNext": "Siguiente",
                        "sPrevious": "Anterior"
                    },
                    "sProcessing": "Buscando en todas las fichas..."
                },
                "order": [[0, "desc"]],
                dom: 'Bfrtip',
                buttons: [
                    { extend: 'copyHtml5', text: '<i class="fa-solid fa-copy fa-2x"></i>', titleAttr: 'Copiar', className: 'btn' },
                    { extend: 'csvHtml5', text: '<i class="fa-solid fa-file-csv fa-2x"></i>', titleAttr: 'Exportar a CSV', className: 'btn' },
                    { extend: 'excelHtml5', text: '<i class="fa-solid fa-file-excel fa-2x"></i>', titleAttr: 'Exportar a Excel', className: 'btn' }
                ]
            });
        }
    });
});
</script>

<br><br><br><br>


