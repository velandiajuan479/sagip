<?php
require_once("models/mvidgen.php");

$idvid = isset($_REQUEST['idvid']) ? $_REQUEST['idvid']:NULL;
$nomvid = isset($_POST['nomvid']) ? $_POST['nomvid']:NULL;
$rutvid = isset($_POST['rutvid']) ? $_POST['rutvid']:NULL;
$ordvid = isset($_POST['ordvid']) ? $_POST['ordvid']:NULL;
$feccar = isset($_POST['feccar']) ? $_POST['feccar']:DATE("Y-m-d H:i:s");
$fecini = isset($_POST['fecini']) ? $_POST['fecini']:NULL;
$fecfin = isset($_POST['fecfin']) ? $_POST['fecfin']:NULL;
$pesvid = isset($_POST['pesvid']) ? $_POST['pesvid']:NULL;
$durvid = isset($_POST['durvid']) ? $_POST['durvid']:NULL;
$actvid = isset($_POST['actvid']) ? $_POST['actvid']:1;
$idusu = isset($_POST['idusu']) ? $_POST['idusu']:1;
$ope = isset($_REQUEST['ope']) ? $_REQUEST['ope']:NULL;
$dtOn = NULL;

$fechini = DATE("Y-m-d");
$fecha = new DateTime($fechini);
$fecha->modify('+15 days');
$fechfin = $fecha->format('Y-m-d');

if (!function_exists('getMp4DurationHelper')) {
    function getMp4DurationHelper($filename) {
        if (!file_exists($filename)) return null;
        $fp = @fopen($filename, 'rb');
        if (!$fp) return null;
        $duration = null;
        while (!feof($fp)) {
            $data = fread($fp, 8);
            if (strlen($data) < 8) break;
            $size = unpack('N', substr($data, 0, 4))[1];
            $type = substr($data, 4, 4);

            if ($size == 1) {
                $data64 = fread($fp, 8);
                if (strlen($data64) < 8) break;
                $size = unpack('J', $data64)[1];
                $headerSize = 16;
            } else {
                $headerSize = 8;
            }

            if ($type === 'moov') {
                $moovContent = fread($fp, $size - $headerSize);
                $pos = strpos($moovContent, 'mvhd');
                if ($pos !== false) {
                    $mvhd = substr($moovContent, $pos + 4);
                    $version = ord($mvhd[0]);
                    if ($version == 1) {
                        $timescale = unpack('N', substr($mvhd, 20, 4))[1];
                        $durationUnits = unpack('J', substr($mvhd, 24, 8))[1];
                    } else {
                        $timescale = unpack('N', substr($mvhd, 12, 4))[1];
                        $durationUnits = unpack('N', substr($mvhd, 16, 4))[1];
                    }
                    if ($timescale > 0) {
                        $duration = round($durationUnits / $timescale);
                    }
                }
                break;
            } else {
                if ($size < $headerSize) break;
                fseek($fp, $size - $headerSize, SEEK_CUR);
            }
        }
        fclose($fp);
        return $duration;
    }
}

if (!function_exists('secToTimeHelper')) {
    function secToTimeHelper($seconds) {
        if ($seconds === null || $seconds === '') return '00:00:00';
        $h = floor($seconds / 3600);
        $m = floor(($seconds % 3600) / 60);
        $s = $seconds % 60;
        return sprintf('%02d:%02d:%02d', $h, $m, $s);
    }
}

$imgcen = NULL;
$vid = isset($_FILES['rutvid']['name']) ? $_FILES['rutvid']['name']:NULL;
if($vid){
    if (isset($_FILES['rutvid']['size']) && $_FILES['rutvid']['size'] > 0) {
        $pesvid = $_FILES['rutvid']['size'];
    }
    $imgcen = opti($_FILES['rutvid'], "s", '../vid/vis', date("YmdHis"));
    if (empty($durvid) && !empty($imgcen)) {
        $rutaCompleta = '../vid/vis/' . $imgcen;
        $segundos = getMp4DurationHelper($rutaCompleta);
        if ($segundos !== null) {
            $durvid = secToTimeHelper($segundos);
        }
    }
}

$mvidgen = new mVidgen();

$mvidgen->setIdvid($idvid);
if($ope=="save"){
    if ($idvid && !$vid) {
        $actual = $mvidgen->getOne();
        if ($actual && isset($actual[0])) {
            if (!$imgcen) $imgcen = $actual[0]['rutvid'];
            if (!$pesvid) $pesvid = $actual[0]['pesvid'];
            if (!$durvid) $durvid = $actual[0]['durvid'];
        }
    }
    $mvidgen->setNomvid($nomvid);
    $mvidgen->setRutvid($imgcen);
    $mvidgen->setOrdvid($ordvid);
    $mvidgen->setFeccar($feccar);
    $mvidgen->setFecini($fecini);
    $mvidgen->setFecfin($fecfin);
    $mvidgen->setPesvid($pesvid);
    $mvidgen->setDurvid($durvid);
    $mvidgen->setActvid($actvid);
    $mvidgen->setIdusu($idusu);
    if($idvid) $mvidgen->upd();
    else $mvidgen->save();
}

if($ope=="act" && $idvid && $actvid) $mvidgen->updAct($actvid);

if($ope=="eli" && $idvid) $mvidgen->del();

if($ope=="edi" && $idvid) $dtOn = $mvidgen->getOne();

$datAll = $mvidgen->getAll();
if($datAll) $ctn = count($datAll)+1; else $ctn = 1;
$datOneVis = $mvidgen->getOneVis();
$dtTot = $mvidgen->getTotales();
$totVid = isset($dtTot[0]['total']) ? (int)$dtTot[0]['total'] : 0;
$totAct = isset($dtTot[0]['activos']) ? (int)$dtTot[0]['activos'] : 0;
$totInact = isset($dtTot[0]['inactivos']) ? (int)$dtTot[0]['inactivos'] : 0;
$datAct = $mvidgen->getVidAct();
$datInact = $mvidgen->getVidInact();
?>