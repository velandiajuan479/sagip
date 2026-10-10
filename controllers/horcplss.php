<?php
// Controlador Plan de Sesión (pg 1512). Estructura: agenda -> sesion -> actividad -> ficha/resultado/competencia/usuario/valor
require_once __DIR__ . '/../models/hormplss.php';

if (session_status() === PHP_SESSION_NONE) { session_start(); }

$mpse = new Mpse();

// Token exacto de la sesión del sistema (models/control.php y models/seguridad.php)
$PLS_AUT = 'jY238Jn&5Hhass.??44aa@@fg(80';
$plsAutenticado = (isset($_SESSION['aut']) && $_SESSION['aut'] === $PLS_AUT);
$plsIdusu = isset($_SESSION['idusu']) ? (int)$_SESSION['idusu'] : 0;

function pls_json($arr, $code = 200) {
    while (ob_get_level() > 0) { ob_end_clean(); }
    if ($code !== 200) { http_response_code($code); }
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($arr, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Comprueba la relación real competencia -> resultado usando el dato maestro
 * (resultado.idcom). Se resuelve con un método público ya existente
 * (Mjui::validarRelacionCompetenciaResultado) y se cachea en memoria.
 */
function pls_relacion_valida($idcom, $idres) {
    static $mjui = null;
    static $cache = [];
    $idcom = (int)$idcom;
    $idres = (int)$idres;
    if ($idcom <= 0 || $idres <= 0) { return false; }
    $k = $idcom . '-' . $idres;
    if (array_key_exists($k, $cache)) { return $cache[$k]; }
    try {
        if ($mjui === null) {
            require_once __DIR__ . '/../models/hormjuev.php';
            $mjui = new Mjui();
        }
        $cache[$k] = (bool)$mjui->validarRelacionCompetenciaResultado($idcom, $idres);
    } catch (Throwable $e) {
        $cache[$k] = false;
    }
    return $cache[$k];
}

/**
 * Depura el listado del Plan de Sesión.
 *
 * El modelo compartido (hormplss.php, que NO se modifica) obtiene la competencia con:
 *      LEFT JOIN competencia AS c ON (r.idcom = c.idcom OR ag.idcom = c.idcom)
 * Cuando agenda.idcom y resultado.idcom son competencias distintas y ambas existen
 * (47 agendas reales), ese LEFT JOIN devuelve DOS filas de la misma actividad: una con
 * la competencia real del resultado y otra con la competencia de la agenda, que NO
 * contiene ese resultado. Aquí se elimina la fila duplicada y se conserva la que
 * respeta la relación real (resultado.idcom). No se inventan datos ni se agregan
 * registros: sólo se descarta la variante incoherente.
 */
function pls_depurar_competencias(array $filas) {
    $salida = [];
    $vistos = [];
    foreach ($filas as $fila) {
        $idres = (int)($fila['idres'] ?? 0);
        $llave = ($fila['idage'] ?? '') . '|' . ($fila['idses'] ?? 0) . '|' . ($fila['idact'] ?? 0) . '|' . $idres;
        $idcom = (int)($fila['idcom'] ?? 0);
        if (!isset($vistos[$llave])) {
            $vistos[$llave] = ['idx' => count($salida), 'coherente' => pls_relacion_valida($idcom, $idres)];
            $salida[] = $fila;
            continue;
        }
        // Fila repetida (misma agenda, sesión, actividad y resultado): solo se acepta
        // si es la variante coherente y la guardada todavía no lo era.
        if (!empty($vistos[$llave]['coherente'])) { continue; }
        if (pls_relacion_valida($idcom, $idres)) {
            $salida[$vistos[$llave]['idx']] = $fila;
            $vistos[$llave]['coherente'] = true;
        }
    }
    return $salida;
}

/**
 * Prepara datos de presentación que el modelo compartido entrega "planos":
 *  - número de sesión dentro de su agenda (idses es la PK global, no el consecutivo)
 *  - si la fila proviene de una sesión real (sesion.idses) o sólo de la agenda
 *  - fechas de la agenda saneadas (se descartan 0000-00-00 y vacíos)
 * Usa métodos públicos existentes del modelo compartido (getSesionesPorFicha).
 */
function pls_enriquecer_filas($mpse, array $filas) {
    static $cacheSesiones = [];
    $sanear = function ($v) {
        $v = trim((string)$v);
        if ($v === '' || $v === '0000-00-00' || $v === '0000-00-00 00:00:00') { return ''; }
        return $v;
    };
    $salida = [];
    foreach ($filas as $fila) {
        $idses = (int)($fila['idses'] ?? 0);
        $idfic = trim((string)($fila['idfic'] ?? ''));

        $fila['ses_real'] = ($idses > 0);
        $fila['ses_ord'] = 0;
        if ($idses > 0 && $idfic !== '') {
            if (!array_key_exists($idfic, $cacheSesiones)) {
                $mapa = [];
                $porAgenda = [];
                foreach ($mpse->getSesionesPorFicha($idfic) as $s) {
                    $porAgenda[(int)$s['idage']][] = ['idses' => (int)$s['idses'], 'fecses' => (string)($s['fecses'] ?? '')];
                }
                foreach ($porAgenda as $lista) {
                    usort($lista, function ($a, $b) {
                        if ($a['fecses'] === $b['fecses']) { return $a['idses'] <=> $b['idses']; }
                        return strcmp($a['fecses'], $b['fecses']);
                    });
                    $ord = 1;
                    foreach ($lista as $s) { $mapa[$s['idses']] = $ord; $ord++; }
                }
                $cacheSesiones[$idfic] = $mapa;
            }
            $fila['ses_ord'] = (int)($cacheSesiones[$idfic][$idses] ?? 0);
        }
        if ($fila['ses_ord'] <= 0 && $idses > 0) { $fila['ses_ord'] = 1; }

        $fila['age_fecini'] = $sanear($fila['fchinc'] ?? '');
        $fila['age_fecfin'] = $sanear($fila['fchfnl'] ?? '');
        $fila['fecha_ses'] = $fila['ses_real'] ? $sanear($fila['fecses'] ?? '') : '';
        if ($fila['age_fecini'] !== '' && $fila['age_fecfin'] !== '') {
            $fila['age_fechas'] = $fila['age_fecini'] . ' a ' . $fila['age_fecfin'];
        } else {
            $fila['age_fechas'] = $fila['age_fecini'] !== '' ? $fila['age_fecini'] : ($fila['age_fecfin'] !== '' ? $fila['age_fecfin'] : '');
        }

        // Indicadores de datos maestros realmente existentes (los "textos de relleno"
        // del modelo compartido no deben mostrarse como si fueran datos reales).
        $fila['tiene_ficha']       = ($idfic !== '');
        $fila['tiene_competencia'] = ((int)($fila['idcom'] ?? 0) > 0);
        $fila['tiene_resultado']   = ((int)($fila['idres'] ?? 0) > 0);
        $fila['tiene_instructor']  = ((int)($fila['idusu'] ?? 0) > 0);

        $salida[] = $fila;
    }
    return $salida;
}

$pg    = isset($_REQUEST['pg']) && $_REQUEST['pg'] !== '' ? $_REQUEST['pg'] : '1512';
$opera = isset($_REQUEST['opera']) ? $_REQUEST['opera'] : null;
$idact = isset($_REQUEST['idact']) && $_REQUEST['idact'] !== '' ? (int)$_REQUEST['idact'] : null;
$datOne = null;
$msg = null;
$tipoMsg = null;

// ===== AJAX (JSON) — requiere sesión válida =====
if ($opera !== null && $opera !== '') {
    if (!$plsAutenticado) {
        pls_json(['success' => false, 'message' => 'Su sesión expiró. Vuelva a iniciar sesión.'], 401);
    }
    require_once 'models/hormacceso.php';
    $haccesoAjax = new HormAcceso();
    $scopeCtxAjax = $haccesoAjax->getContexto();

    if ($opera === 'get_sesiones_ficha') {
        $idfic = isset($_REQUEST['idfic']) ? trim($_REQUEST['idfic']) : '';
        if ($idfic === '') {
            pls_json(['success' => false, 'message' => 'Debe seleccionar la ficha.', 'sesiones' => [], 'agendas' => []]);
        }
        if (!$haccesoAjax->puedeVerFicha($idfic, $scopeCtxAjax)) {
            pls_json(['success' => false, 'message' => 'No tiene permiso sobre esta ficha.', 'sesiones' => [], 'agendas' => []], 403);
        }
        pls_json([
            'success' => true,
            'sesiones' => $mpse->getSesionesPorFicha($idfic),
            'agendas' => $mpse->getAgendasPorFicha($idfic)
        ]);
    }

    if ($opera === 'detalle_actividad') {
        if (empty($idact)) { pls_json(['success' => false, 'message' => 'No se indicó la actividad.']); }
        $reg = $mpse->getActividad($idact);
        if (!$reg) { pls_json(['success' => false, 'message' => 'La actividad indicada no existe.']); }
        pls_json(['success' => true, 'data' => $reg]);
    }

    if ($opera === 'guardar_actividad') {
        try {
            $idses = isset($_POST['idses']) ? (int)$_POST['idses'] : 0;
            $nomact = isset($_POST['nomact']) ? trim($_POST['nomact']) : '';
            $desact = isset($_POST['desact']) ? trim($_POST['desact']) : '';
            $duract = isset($_POST['duract']) ? (int)$_POST['duract'] : 0;
            $tipact = isset($_POST['tipact']) ? (int)$_POST['tipact'] : 1;
            $ordact = isset($_POST['ordact']) ? (int)$_POST['ordact'] : 1;
            $opc = isset($_POST['opc']) ? $_POST['opc'] : '';
            $idficAjax = isset($_POST['idfic']) ? trim($_POST['idfic']) : '';

            // INTEGRIDAD: la actividad se guarda siempre dentro de la cadena real
            // ficha -> agenda -> sesion -> actividad. Se comprueba que la agenda o la
            // sesión recibidas pertenezcan a la ficha seleccionada, usando métodos
            // públicos ya existentes del modelo (sin SQL en el controlador).
            if ($idficAjax === '') {
                pls_json(['success' => false, 'message' => 'Debe seleccionar la ficha.']);
            }
            if (!$haccesoAjax->puedeVerFicha($idficAjax, $scopeCtxAjax)) {
                pls_json(['success' => false, 'message' => 'No tiene permiso sobre esta ficha.'], 403);
            }

            if ($opc === 'nueva_sesion') {
                $idage = isset($_POST['idage']) ? (int)$_POST['idage'] : 0;
                $fecses = isset($_POST['fecses']) ? trim($_POST['fecses']) : '';
                if ($idage <= 0) { pls_json(['success' => false, 'message' => 'Debe seleccionar la agenda para la nueva sesión.']); }
                if ($fecses === '') { pls_json(['success' => false, 'message' => 'Debe indicar la fecha de la nueva sesión.']); }
                $agendaValida = false;
                foreach ($mpse->getAgendasPorFicha($idficAjax) as $ag) {
                    if ((int)$ag['idage'] === $idage) { $agendaValida = true; break; }
                }
                if (!$agendaValida) {
                    pls_json(['success' => false, 'message' => 'La agenda seleccionada no pertenece a la ficha indicada.']);
                }
                $nuevoIds = $mpse->saveSesion($idage, $fecses);
                if (!$nuevoIds) { pls_json(['success' => false, 'message' => 'No fue posible crear la sesión.']); }
                $idses = $nuevoIds;
            } elseif ($idses > 0) {
                $sesionValida = false;
                foreach ($mpse->getSesionesPorFicha($idficAjax) as $s) {
                    if ((int)$s['idses'] === $idses) { $sesionValida = true; break; }
                }
                if (!$sesionValida) {
                    pls_json(['success' => false, 'message' => 'La sesión seleccionada no pertenece a la ficha indicada.']);
                }
            }

            if ($idses <= 0) { pls_json(['success' => false, 'message' => 'Debe seleccionar o crear una sesión.']); }
            if ($nomact === '') { pls_json(['success' => false, 'message' => 'El nombre de la actividad es obligatorio.']); }

            $mpse->setIdses($idses);
            $mpse->setNomact($nomact);
            $mpse->setDesact($desact);
            $mpse->setDuract($duract);
            $mpse->setTipact($tipact > 0 ? $tipact : 1);
            $mpse->setOrdact($ordact > 0 ? $ordact : 1);

            if (!empty($idact)) {
                $mpse->setIdact($idact);
                $ok = $mpse->edit();
                pls_json(['success' => (bool)$ok, 'idact' => $idact, 'message' => $ok ? 'Actividad actualizada correctamente.' : 'No fue posible actualizar la actividad.']);
            }

            $nuevoId = $mpse->save();
            if ($nuevoId === false) { pls_json(['success' => false, 'message' => 'No fue posible registrar la actividad.']); }
            pls_json(['success' => true, 'idact' => $nuevoId, 'message' => 'Actividad registrada correctamente.']);
        } catch (Throwable $e) {
            pls_json(['success' => false, 'message' => 'Error al guardar: ' . $e->getMessage()]);
        }
    }

    if ($opera === 'eliminar_actividad') {
        if (empty($idact)) { pls_json(['success' => false, 'message' => 'No se indicó la actividad a eliminar.']); }
        $ok = $mpse->del($idact);
        pls_json(['success' => (bool)$ok, 'message' => $ok ? 'Actividad eliminada correctamente.' : 'No fue posible eliminar la actividad.']);
    }

    pls_json(['success' => false, 'message' => 'Operación no soportada.']);
}

// ============================================================
// PREPARACIÓN DE DATOS PARA LA VISTA (listado con filtros y paginación)
// ============================================================
$filtros = [
    'idfic'    => isset($_REQUEST['idfic']) ? trim($_REQUEST['idfic']) : '',
    'idcom'    => isset($_REQUEST['idcom']) ? trim($_REQUEST['idcom']) : '',
    'idres'    => isset($_REQUEST['idres']) ? trim($_REQUEST['idres']) : '',
    'idusu'    => isset($_REQUEST['idusu']) ? trim($_REQUEST['idusu']) : '',
    'desde'    => isset($_REQUEST['desde']) ? trim($_REQUEST['desde']) : '',
    'hasta'    => isset($_REQUEST['hasta']) ? trim($_REQUEST['hasta']) : '',
    'busqueda' => isset($_REQUEST['busqueda']) ? trim($_REQUEST['busqueda']) : ''
];
$plsPage  = isset($_REQUEST['pls_page']) ? max(1, (int)$_REQUEST['pls_page']) : 1;
$plsLimit = 10;

// ---------------------------------------------------------------------------
// Listado del plan de sesión
// ---------------------------------------------------------------------------
// El modelo compartido devuelve el total de filas del JOIN y, cuando una agenda
// tiene "idcom" distinto del "idcom" de su resultado, duplica la fila (ver
// pls_depurar_competencias()). Por eso el listado se pagina aquí, sobre el
// conjunto ya depurado, para que el total, las páginas y lo que se ve coincidan.
$plsTope = 1000; // mismo tope que ya usaba el listado imprimible completo
$plsConsulta = $mpse->getActividadesFiltradas($filtros, 1, $plsTope);
$plsTotalModelo = (int)($plsConsulta['total'] ?? 0);
$plsFilas = pls_enriquecer_filas($mpse, pls_depurar_competencias($plsConsulta['data']));

if ($plsTotalModelo > count($plsConsulta['data'])) {
    // El listado supera el tope: se conserva la página del modelo y se depura dentro
    // de ella (se informa en pantalla que el total mostrado puede ser aproximado).
    $datPag = $mpse->getActividadesFiltradas($filtros, $plsPage, $plsLimit);
    $datPag['data'] = pls_enriquecer_filas($mpse, pls_depurar_competencias($datPag['data']));
    $datAll = $plsFilas;
} else {
    $plsTotal   = count($plsFilas);
    $plsPaginas = max(1, (int)ceil($plsTotal / $plsLimit));
    if ($plsPage > $plsPaginas) { $plsPage = $plsPaginas; }
    $datPag = [
        'data'          => array_slice($plsFilas, ($plsPage - 1) * $plsLimit, $plsLimit),
        'total'         => $plsTotal,
        'total_paginas' => $plsPaginas,
        'pagina'        => $plsPage
    ];
    $datAll = $plsFilas;
}

// Dashboard
$estadisticas = $mpse->getEstadisticas();

// Alcance por rol (módulo HyA): fichas visibles según el usuario autenticado.
require_once 'models/hormacceso.php';
$hacceso = new HormAcceso();
$scopeCtx = $hacceso->getContexto();
$scopeFichas = $hacceso->getFichasPermitidas($scopeCtx);
$scopeRol = $hacceso->etiquetaRol($scopeCtx);
$scopeN = count($scopeFichas);
$scopeTotal = !empty($scopeCtx['is_admin']);

$datFicDash = $hacceso->filtrarPorFicha($mpse->getActividadesPorFicha(), 'idfic', $scopeCtx);
$datCompeDash = $mpse->getActividadesPorCompetencia();

// Catálogos para filtros y formulario
$datFic   = $hacceso->filtrarPorFicha($mpse->getFichas(), 'idfic', $scopeCtx);
$datIns   = $mpse->getInstructores();
$datComF  = $mpse->getCompetenciasFiltro();
$datResF  = $mpse->getResultadosFiltro();
$datTipos = $mpse->getTiposActividad();

// Datos institucionales de la ficha seleccionada (encabezado tipo reporte)
$datFichaSel = (!empty($filtros['idfic'])) ? $mpse->getFichaDetalle($filtros['idfic']) : [];

// La tabla planses aporta fase/estado/contenidos/materiales (solo se muestra si tiene datos)
$plsConDatos = ((int)($estadisticas['total_planes_tabla'] ?? 0)) > 0;

// Mensaje flash (alta/edición/eliminación)
$msg = '';
$tipoMsg = 'success';
if (!empty($_SESSION['pls_flash'])) {
    $msg = $_SESSION['pls_flash']['texto'] ?? '';
    $tipoMsg = $_SESSION['pls_flash']['tipo'] ?? 'success';
    unset($_SESSION['pls_flash']);
}

