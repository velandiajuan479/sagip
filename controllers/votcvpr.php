<?php
require_once 'models/votmvpr.php';

$mvpr = new Mvvpr();
$idusu = isset($_REQUEST['idusu']) ? $_REQUEST['idusu'] : $_SESSION['idusu'];
$pg = 1204;

/* Definiciones de los campos de propuesta (slogan/estrategias, condiciones, manifiesto) */
$dvpr = $mvpr->getVal(3);
$dcon = $mvpr->getVal(2);
$dman = $mvpr->getVal(4);

/* Personas que han registrado su propuesta */
$dcand = $mvpr->getCand();

/* Para cada candidato, sus valores registrados agrupados por dominio */
foreach ($dcand as $i => $c) {
	$mvpr->setIdusu($c['idusu']);
	$props3 = array();
	$conds  = array();
	$mans   = array();
	foreach ($mvpr->getProp() as $r) {
		if ($r['texpro'] === NULL || $r['texpro'] === '') continue;
		if ($r['iddom'] == 2)      $conds[$r['idval']]  = $r['texpro'];
		elseif ($r['iddom'] == 4)  $mans[$r['idval']]   = $r['texpro'];
		else                       $props3[]            = $r['texpro'];
	}
	$vid = $mvpr->getVideo();
	$dcand[$i]['props3'] = $props3;
	$dcand[$i]['conds']  = $conds;
	$dcand[$i]['mans']   = $mans;
	$dcand[$i]['video']  = ($vid && isset($vid[0]['rutvid']) && $vid[0]['rutvid']) ? $vid[0]['rutvid'] : NULL;
}

/**
 * Decodifica entidades HTML de los textos administrativos (valor/usuario)
 * para conservar la presentación de la aplicación original.
 */
if (!function_exists('txlbl')) {
	function txlbl($s) {
		return html_entity_decode($s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
	}
}

/* Conjunto de candidatos para la grilla y el modal (JSON embebido en la vista) */
$candJson = array();
foreach ($dcand as $c) {
	$candJson[] = array(
		'idusu'  => (int)$c['idusu'],
		'noca'   => ($c['noca'] !== NULL && $c['noca'] !== '') ? txlbl($c['noca']) : '',
		'nomusu' => txlbl($c['nomusu']),
		'fotcan' => $c['fotcan'],
		'idfic'  => $c['idfic'],
		'nomfic' => ($c['nomfic'] !== NULL) ? txlbl($c['nomfic']) : '',
		'nomjor' => ($c['nomjor'] !== NULL) ? txlbl($c['nomjor']) : '',
		'nomcen' => ($c['nomcen'] !== NULL) ? txlbl($c['nomcen']) : '',
		'props3' => array_values($c['props3']),
		'conds'  => $c['conds'],
		'mans'   => $c['mans'],
		'video'  => $c['video'],
		'videoOk' => ($c['video'] !== NULL && $c['video'] !== '' && file_exists('videos/' . $c['video']))
	);
}

/* Definición de campos para renderizar el modal estilo Formato */
$camposDef = array(
	2 => $dcon,
	3 => $dvpr,
	4 => $dman
);
$defJson = array();
foreach (array(2, 3, 4) as $dd) {
	$arr = array();
	foreach ($camposDef[$dd] as $r) {
		$sub = array();
		if ($r['parval'] !== NULL && $r['parval'] !== '') $sub = array_map('txlbl', explode(';', $r['parval']));
		$arr[] = array('idval' => (int)$r['idval'], 'nomval' => txlbl($r['nomval']), 'subs' => $sub);
	}
	$defJson[$dd] = $arr;
}
?>