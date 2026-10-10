<?php
require_once __DIR__ . '/../models/conexion.php';
require_once __DIR__ . '/../models/hormhrgn.php';
require_once __DIR__ . '/../models/hormacceso.php';
ini_set('memory_limit', '512M');
// require_once '../vendor/autoload.php';

$idhor = isset($_GET['idhor']) ? $_GET['idhor'] : NULL;
//$idare = isset($_GET['idare']) ? $_GET['idare'] : NULL;

$ancho = 750;
$alto = 500;
$alto2 = 300;
$html = "";

$mrphg = new Hormhrgn();
$mrphg->setIdhor($idhor);
$datcent = $mrphg->getCent();
//$idare = ...;
// Alcance por rol (módulo HyA): solo fichas permitidas al usuario (admin ve todas).
$hacceso = new HormAcceso();
$scopeCtx = $hacceso->getContexto();
$datfic = $hacceso->filtrarPorFicha($mrphg->getAll(), 'idfic', $scopeCtx);
// Filtros del modal de reporte (home.php?pg=1506): ficha, área y mes/año por
// solape con la vigencia de la ficha [finific, ffinfic]. Todo validado.
$reqFic = isset($_GET['fic']) && $_GET['fic'] !== '' ? trim($_GET['fic']) : null;
if ($reqFic !== null && !$hacceso->puedeVerFicha($reqFic, $scopeCtx)) $reqFic = null;
$reqIdare = isset($_GET['idare']) && $_GET['idare'] !== '' ? trim($_GET['idare']) : null;
$reqAnio = isset($_GET['anio']) && (int)$_GET['anio'] > 0 ? (int)$_GET['anio'] : null;
$reqMes = isset($_GET['mes']) && (int)$_GET['mes'] >= 1 && (int)$_GET['mes'] <= 12 ? (int)$_GET['mes'] : null;
if ($reqFic !== null || $reqIdare !== null || ($reqAnio !== null && $reqMes !== null)) {
    $mesIni = ($reqAnio !== null && $reqMes !== null) ? sprintf('%04d-%02d-01', $reqAnio, $reqMes) : null;
    $mesFin = ($mesIni !== null) ? date('Y-m-t', strtotime($mesIni)) : null;
    $datfic = array_values(array_filter($datfic, function ($f) use ($reqFic, $reqIdare, $mesIni, $mesFin) {
        if ($reqFic !== null && (string)$f['idfic'] !== (string)$reqFic) return false;
        if ($reqIdare !== null && (string)$f['idare'] !== (string)$reqIdare) return false;
        if ($mesIni !== null && !empty($f['finific']) && !empty($f['ffinfic'])) {
            if ($f['ffinfic'] < $mesIni || $f['finific'] > $mesFin) return false;
        }
        return true;
    }));
}
$ddi = $mrphg->getDia();

$cantDias = ($ddi && count($ddi) > 0) ? count($ddi) : 7;
$anchoDia = number_format((100 - 8 - 20 - 8) / $cantDias, 2, '.', '') . '%';

if (!function_exists('pintit')) {
function pintit($ddi){
    $dias = "";
    if($ddi){ foreach ($ddi as $d) {
      $dias .= "<th class='col-dia'>".$d['nomval']."</th>";
    }}
    return $dias;
}
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * {
            margin: 0;
            padding: 0;
            text-align: center;
            font-size: 9px;
            font-family: Arial, Helvetica, sans-serif;
            box-sizing: border-box;
        }
        table {
            width: 100%;
            margin: auto;
            table-layout: fixed;
        }
        table, th, td {
            border: 1px solid #aaa;
            border-collapse: collapse;
            word-wrap: break-word;
            overflow-wrap: break-word;
            word-break: normal;
        }
        th, td {
            padding: 2px;
        }
        img {
            height: 35px;
            width: 35px;
        }
        .version, .gpf {
            max-width: 30px;
        }
        .pri {
            text-align: left;
            height: 10px;
        }
        .dato {
            font-size: 8.5px;
            padding: 2px;
            line-height: 1.15;
        }
        input {
            width: 100%;
        }
        .col-fic { width: 8%; }
        .col-prog { width: 20%; }
        .col-jor { width: 8%; }
        .col-dia { width: <?= $anchoDia ?>; }
        .item-ins {
            border-bottom: 1px dashed #ccc;
            padding: 1px 0;
            margin-bottom: 1px;
        }
        .item-ins:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        .nom-usu {
            font-weight: bold;
            display: block;
            font-size: 8.5px;
            line-height: 1.1;
            margin-bottom: 0px;
        }
        .nom-aul {
            color: #555;
            font-size: 8px;
            display: block;
            line-height: 1.1;
        }
        @media print {
            tr { page-break-inside: avoid; break-inside: avoid; }
            table { page-break-inside: auto; }
            * { font-size: 8px; }
            .nom-usu { font-size: 8px; }
            .nom-aul { font-size: 7.5px; }
            th, td { padding: 1px 2px; }
        }
    </style>
</head>
<body>
    <table>
        <tbody>
            <tr>
                <td style="padding:4px;">
                    <img src="img/sena.png" alt="logoSena">
                    <?php if ($datcent): foreach ($datcent as $dtc): ?>
                        <h1 style="font-size:11px; margin-top:2px;"><?= $dtc['nomcen'] ?></h1>
                        <h5 style="font-size:9px;"><?= $dtc['dircen'] ?></h5>
                    <?php endforeach; endif; ?>
                </td>
            </tr>
            <tr>
                <th style="padding:4px;">
                    <h1 style="font-size:11px;">REPORTE DE HORARIOS GENERALES</h1>
                </th>
            </tr>
        </tbody>
    </table>

    <table style="margin-top:4px;">
        <colgroup>
            <col class="col-fic">
            <col class="col-prog">
            <col class="col-jor">
            <?php if ($ddi): foreach ($ddi as $d): ?>
                <col class="col-dia">
            <?php endforeach; endif; ?>
        </colgroup>
        <thead>
            <tr>
                <th colspan="3">PROGRAMA DE FORMACION</th>
                <th colspan="<?= $cantDias ?>">HORARIO</th>
            </tr>
            <tr>
                <th class="col-fic">Ficha</th>
                <th class="col-prog">Programa</th>
                <th class="col-jor">Jornada</th>
                <?= pintit($ddi) ?>
            </tr>
        </thead>
        <tbody>
            <?php if ($datfic): foreach ($datfic as $dtf): ?>
                <tr>
                    <td class="dato col-fic"><?= $dtf['idfic'] ?></td>
                    <td class="dato col-prog"><?= $dtf['nomfic'] ?></td>
                    <td class="dato col-jor"><?= $dtf['nomval'] ?></td>
                    <?php if ($ddi): foreach ($ddi as $d): ?>
                        <td class="col-dia" style="vertical-align:top; text-align:left; padding:2px;">
                            <?php 
                            $mrphg->setIdfic($dtf["idfic"]);
                            $mrphg->setIddia($d["idval"]);
                            $dat = $mrphg->getIns(); 
                            ?>
                            <?php if ($dat): foreach ($dat as $dti): ?>
                                <div class="item-ins">
                                    <span class="nom-usu"><?= htmlspecialchars($dti["nomusu"]) ?></span>
                                    <span class="nom-aul"><?= htmlspecialchars($dti['idaul'] . ' ' . $dti['nomaul']) ?></span>
                                </div>
                            <?php endforeach; endif; ?>
                        </td>
                    <?php endforeach; endif; ?>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</body>
</html>
<?php
//$html = ob_get_clean();
//echo $html;
echo "<script>window.print();</script>";
?>