<?php
// Controlador del módulo Juicio de Evaluación - horcjuev.php
// Patrón real: la vista lo incluye, él prepara las variables, no carga la vista salvo redirecciones/JSON
require_once __DIR__ . '/../models/hormjuev.php';
require_once __DIR__ . '/../models/hormacceso.php';

// Alcance por rol (módulo HyA) para la cinta de alcance y el filtro de fichas.
$hacceso = new HormAcceso();
$scopeCtx = $hacceso->getContexto();
$scopeFichas = $hacceso->getFichasPermitidas($scopeCtx);
$scopeRol = $hacceso->etiquetaRol($scopeCtx);
$scopeN = count($scopeFichas);
$scopeTotal = !empty($scopeCtx['is_admin']);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$autValida = isset($_SESSION["aut"]) && $_SESSION["aut"] === "jY238Jn&5Hhass.??44aa@@fg(80" && !empty($_SESSION["idusu"]);
$opera = isset($_REQUEST['opera']) ? $_REQUEST['opera'] : null;

// ============================================================
// SEGURIDAD: Validar sesión y permisos existentes
// ============================================================
if (!$autValida) {
    if ($opera) {
        while (ob_get_level() > 0) { ob_end_clean(); }
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(401);
        echo json_encode(['success' => false, 'ok' => false, 'message' => 'Acceso no autorizado o sesión expirada.'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    if (basename($_SERVER['PHP_SELF'] ?? '') === 'horcjuev.php') {
        header("Location: ../index.php");
        exit;
    }
}

$mjui = new Mjui();

// SEGURIDAD: $pg llega directo de la URL y se reutiliza en atributos
// HTML y en bloques <script> de redirección; sin filtrar, un valor
// como  1512';alert(1);//  rompe el string y ejecuta script arbitrario
// (XSS reflejado). Se restringe a un identificador simple.
$pgRaw = isset($_REQUEST['pg']) ? $_REQUEST['pg'] : '2117';
$pg = preg_replace('/[^A-Za-z0-9_\-]/', '', (string)$pgRaw);
if ($pg === '') {
    $pg = '2117';
}

// SEGURIDAD: token CSRF para las operaciones que modifican datos
// (save/edit/eli/editar_calificacion_rapida/import_step2). Antes
// esas operaciones sólo dependían de la cookie de sesión, por lo
// que un enlace o formulario en un sitio externo podía disparar la
// acción a nombre del usuario logueado.
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];

function csrf_valido() {
    $enviado = $_POST['csrf_token'] ?? ($_REQUEST['csrf_token'] ?? '');
    return !empty($enviado) && !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $enviado);
}

function is_ajax_request() {
    return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
        || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)
        || (isset($_POST['ajax']) && $_POST['ajax'] === '1')
        || (isset($_REQUEST['ajax']) && $_REQUEST['ajax'] === '1');
}

/**
 * Resuelve las relaciones REALES del formulario de Juicios usando los IDs de la
 * base de datos (no se adivina ni se busca por texto):
 *   - aprendiz    : se identifica por el documento -> usuario.idusu (usuario.ndocusu).
 *                   Así el juicio nunca queda ligado a otro aprendiz cuando el
 *                   documento se cambia después de usar "Buscar Estudiante".
 *   - ficha       : debe existir en la tabla ficha.
 *   - resultado   : obligatorio (resultado.idres).
 *   - competencia : se toma de resultado.idcom, que es la relación real
 *                   competencia -> resultado. Si el resultado todavía no existe
 *                   (registrado con "OTRO (especificar)") se crea su relación;
 *                   si ya existe, se respeta el dato maestro.
 * Devuelve: ['idusu','idres','idcom','aviso','error']  ('error' = null si todo ok)
 */
function jui_resolver_relaciones($mjui, $ndoce, $idusu, $idfic, $extrComp, $extrRes) {
    // 1. Aprendiz por documento (fuente confiable)
    if (trim((string)$ndoce) !== '') {
        $usuDoc = $mjui->buscarUsuarioPorDocumento($ndoce);
        if ($usuDoc) {
            $idusu = (int)$usuDoc;
        } else {
            $infoUsu = $mjui->cargarInfoUsuarioPorDocumento($ndoce);
            if ($infoUsu && !empty($infoUsu['idusu'])) {
                $idusu = (int)$infoUsu['idusu'];
            }
        }
    }
    if (!$idusu) {
        return ['idusu' => 0, 'idres' => 0, 'idcom' => 0, 'aviso' => '', 'error' => 'No se pudo identificar al aprendiz con ese número de documento.'];
    }

    // 2. Ficha existente
    $idfic = trim((string)$idfic);
    if ($idfic === '') {
        return ['idusu' => $idusu, 'idres' => 0, 'idcom' => 0, 'aviso' => '', 'error' => 'Debe seleccionar la ficha.'];
    }
    if (!$mjui->cargarInfoFicha($idfic)) {
        return ['idusu' => $idusu, 'idres' => 0, 'idcom' => 0, 'aviso' => '', 'error' => 'La ficha seleccionada no existe en la base de datos.'];
    }

    // 3. Resultado de aprendizaje obligatorio
    $idres = !empty($extrRes['idres']) ? (int)$extrRes['idres'] : 0;
    if (!$idres) {
        return ['idusu' => $idusu, 'idres' => 0, 'idcom' => 0, 'aviso' => '', 'error' => 'Debe seleccionar el resultado de aprendizaje.'];
    }

    $idcomEnviado = !empty($extrComp['idcom']) ? (int)$extrComp['idcom'] : 0;
    $idcomMaestro = $mjui->getCompetenciaDeResultado($idres);
    $aviso = '';

    if (!empty($idcomMaestro)) {
        // 4. El resultado ya existe: la competencia es la del maestro.
        $idcom = (int)$idcomMaestro;
        if ($idcomEnviado && $idcomEnviado !== $idcom) {
            $aviso = 'La competencia indicada no corresponde al resultado seleccionado; '
                . 'se guardó la competencia registrada para ese resultado (' . $idcom . ').';
        }
    } elseif (!$mjui->resultadoExiste($idres)) {
        // 5. Resultado nuevo desde "OTRO (especificar)": se crea con su competencia.
        if (!$idcomEnviado) {
            return ['idusu' => $idusu, 'idres' => $idres, 'idcom' => 0, 'aviso' => '', 'error' => 'Debe indicar la competencia del nuevo resultado de aprendizaje.'];
        }
        $mjui->verificarOCrearCompetencia($idcomEnviado, $extrComp['descom']);
        $mjui->verificarOCrearResultado($idres, $extrRes['nomres'], $idcomEnviado);
        $idcom = $idcomEnviado;
    } else {
        // 6. Resultado existente sin idcom registrado en el maestro.
        $idcom = $idcomEnviado;
    }

    if (!$idcom) {
        return ['idusu' => $idusu, 'idres' => $idres, 'idcom' => 0, 'aviso' => '', 'error' => 'No fue posible determinar la competencia del resultado seleccionado.'];
    }

    return ['idusu' => $idusu, 'idres' => $idres, 'idcom' => $idcom, 'aviso' => $aviso, 'error' => null];
}

$ope   = isset($_REQUEST['ope']) ? $_REQUEST['ope'] : null;
$idjui = isset($_REQUEST['idjui']) ? (int)$_REQUEST['idjui'] : null;
$datOne = null;

$ndoce = isset($_POST['ndoce']) ? trim($_POST['ndoce']) : (isset($_REQUEST['ndoce']) ? trim($_REQUEST['ndoce']) : '');
$nomes = isset($_POST['nomes']) ? trim($_POST['nomes']) : '';
$apees = isset($_POST['apees']) ? trim($_POST['apees']) : '';
$estes = isset($_POST['estes']) ? trim($_POST['estes']) : '';
$caljui = isset($_POST['caljui']) ? strtoupper(trim($_POST['caljui'])) : '';
$idusu = isset($_POST['idusu']) ? (int)$_POST['idusu'] : 0;
$idfic = isset($_POST['idfic']) ? trim($_POST['idfic']) : (isset($_REQUEST['idfic']) ? trim($_REQUEST['idfic']) : '');

// Instructor logueado y perfil
$docIns = '';
if (!empty($_SESSION['idusu'])) {
    $docIns = $mjui->getDocumentoUsuario($_SESSION['idusu']) ?: '';
}
$esAdmin = isset($_SESSION['idper']) && in_array((int)$_SESSION['idper'], [1, 2, 21]);

function safe_json($arr) {
    while (ob_get_level() > 0) { ob_end_clean(); }
    header('Content-Type: application/json; charset=utf-8');
    if (isset($arr['ok']) && !isset($arr['success'])) {
        $arr['success'] = (bool)$arr['ok'];
    }
    if (isset($arr['success']) && !isset($arr['ok'])) {
        $arr['ok'] = (bool)$arr['success'];
    }
    if (isset($arr['msg']) && !isset($arr['message'])) {
        $arr['message'] = $arr['msg'];
    }
    if (isset($arr['message']) && !isset($arr['msg'])) {
        $arr['msg'] = $arr['message'];
    }
    if (isset($arr['error']) && !isset($arr['message'])) {
        $arr['message'] = $arr['error'];
    }
    echo json_encode($arr, JSON_UNESCAPED_UNICODE);
    exit;
}

// ============================================================
// OPERACIONES AJAX (JSON limpio)
// ============================================================

// Validar relación competencia-resultado
if ($opera === "validar_relacion") {
    try {
        $rawComp = isset($_REQUEST['compe']) ? trim($_REQUEST['compe']) : (isset($_REQUEST['idcom']) ? trim($_REQUEST['idcom']) : '');
        $rawRes  = isset($_REQUEST['resapr']) ? trim($_REQUEST['resapr']) : (isset($_REQUEST['idres']) ? trim($_REQUEST['idres']) : '');
        $vaIdcom = $mjui->extraerCompetencia($rawComp)['idcom'];
        $vaIdres = $mjui->extraerResultado($rawRes)['idres'];

        if (!$vaIdcom || !$vaIdres) {
            safe_json(['success' => false, 'valida' => false, 'error' => 'Faltan parámetros']);
        }
        $valida = $mjui->validarRelacionCompetenciaResultado($vaIdcom, $vaIdres);
        safe_json(['success' => true, 'valida' => $valida]);
    } catch (Exception $e) {
        safe_json(['success' => false, 'valida' => false, 'error' => $e->getMessage()]);
    }
}

// Obtener resultados por competencia (acepta compe o idcom, GET o POST)
if ($opera === "get_resultados_competencia") {
    try {
        $rawCompe = isset($_REQUEST['compe']) ? trim($_REQUEST['compe']) : (isset($_REQUEST['idcom']) ? trim($_REQUEST['idcom']) : '');
        if (!$rawCompe) {
            safe_json(['success' => true, 'resultados' => []]);
        }
        $extr = $mjui->extraerCompetencia($rawCompe);
        $rcIdcom = $extr['idcom'];
        if (!$rcIdcom) {
            safe_json(['success' => true, 'resultados' => []]);
        }
        $resultados = $mjui->getResultadosPorCompetencia($rcIdcom);
        safe_json(['success' => true, 'resultados' => $resultados]);
    } catch (Exception $e) {
        safe_json(['success' => false, 'resultados' => [], 'error' => $e->getMessage()]);
    }
}

// Edición rápida de calificación
if ($opera === "editar_calificacion_rapida") {
    try {
        if (!csrf_valido()) {
            http_response_code(403);
            safe_json(['success' => false, 'error' => 'Token de seguridad inválido o ausente. Recargue la página.']);
        }
        $ejIdjui = isset($_POST['idjui']) ? (int)$_POST['idjui'] : 0;
        $ejCal = isset($_POST['calificacion']) ? trim($_POST['calificacion']) : '';
        if (!$ejIdjui || !$ejCal) {
            safe_json(['success' => false, 'error' => 'Faltan parámetros']);
        }
        $calValidas = ['APROBADO', 'NO APROBADO', 'POR EVALUAR'];
        if (!in_array(strtoupper($ejCal), $calValidas)) {
            safe_json(['success' => false, 'error' => 'Calificación inválida']);
        }
        $ok = $mjui->actualizarJuicio($ejIdjui, strtoupper($ejCal));
        safe_json([
            'success' => (bool)$ok,
            'message' => $ok ? 'Calificación actualizada correctamente' : 'Error al actualizar calificación',
            'calificacion' => strtoupper($ejCal)
        ]);
    } catch (Exception $e) {
        safe_json(['success' => false, 'error' => $e->getMessage()]);
    }
}

// Búsqueda AJAX por documento
if ($opera === "buscar_juicio") {
    try {
        $bjNdoce = isset($_REQUEST['ndoce']) ? trim($_REQUEST['ndoce']) : '';
        if ($bjNdoce === '') {
            safe_json(['success' => false, 'error' => 'Falta el parámetro ndoce', 'message' => 'Ingrese un número de documento']);
        }
        $juicio = $mjui->buscarJuicioPorDocumento($bjNdoce);
        $infoUsuario = $mjui->cargarInfoUsuarioPorDocumento($bjNdoce);
        $infoFicha = null;
        if ($juicio && !empty($juicio['idfic'])) {
            $infoFicha = $mjui->cargarInfoFicha($juicio['idfic']);
        }

        $nomesFound = '';
        $apeesFound = '';
        $idusuFound = null;
        $idficFound = null;

        if ($infoUsuario) {
            $nomesFound = $infoUsuario['nomes'] ?? '';
            $apeesFound = $infoUsuario['apees'] ?? '';
            $idusuFound = $infoUsuario['idusu'] ?? null;
            $idficFound = $infoUsuario['idfic'] ?? null;
        }
        if ($juicio) {
            if (!$nomesFound) $nomesFound = $juicio['nomes'] ?? '';
            if (!$apeesFound) $apeesFound = $juicio['apees'] ?? '';
            if (!$idusuFound) $idusuFound = $juicio['idusu'] ?? null;
            if (!$idficFound) $idficFound = $juicio['idfic'] ?? null;
        }

        safe_json([
            'success' => true,
            'existe'  => ($juicio || $infoUsuario) ? true : false,
            'data'    => [
                'nomes' => $nomesFound,
                'apees' => $apeesFound,
                'idusu' => $idusuFound,
                'idfic' => $idficFound
            ],
            'juicio'  => $juicio ?: null,
            'usuario' => $infoUsuario ?: null,
            'ficha'   => $infoFicha ?: null,
            'message' => ($juicio || $infoUsuario) ? 'Estudiante encontrado' : 'No se encontraron registros previos para este documento'
        ]);
    } catch (Throwable $e) {
        http_response_code(500);
        safe_json(['success' => false, 'error' => 'Error del servidor', 'message' => $e->getMessage()]);
    }
}

// Obtener juicios importados en JSON (tabla de la vista)
if ($opera === "get_importados_json") {
    try {
        $page = isset($_REQUEST['page']) ? max(1, (int)$_REQUEST['page']) : 1;
        $limite = isset($_REQUEST['limite']) ? max(1, (int)$_REQUEST['limite']) : 10;

        // El filtro 'compe' puede llegar como "ID - Descripción" o solo el ID numérico
        $compeRaw = isset($_REQUEST['compe']) ? trim($_REQUEST['compe']) : (isset($_REQUEST['idcom']) ? trim($_REQUEST['idcom']) : '');
        $idcomFiltro = '';
        if ($compeRaw !== '') {
            $extr = $mjui->extraerCompetencia($compeRaw);
            $idcomFiltro = (int)$extr['idcom'];
        }

        // SEGURIDAD: Si no es administrador, filtrar obligatoriamente por el instructor de la sesión
        $ndocinsFiltro = '';
        if (!$esAdmin) {
            $ndocinsFiltro = $docIns;
        } else {
            $ndocinsFiltro = isset($_REQUEST['ndocins']) ? trim($_REQUEST['ndocins']) : '';
        }

        $filtros = [
            'idfic'    => isset($_REQUEST['idfic'])    ? trim($_REQUEST['idfic'])    : '',
            'idcom'    => $idcomFiltro,
            'caljui'   => isset($_REQUEST['caljui'])   ? trim($_REQUEST['caljui'])   : '',
            'busqueda' => isset($_REQUEST['busqueda']) ? trim($_REQUEST['busqueda']) : '',
            'ndocins'  => $ndocinsFiltro,
        ];

        $resultado = $mjui->getJuiciosFiltrados($filtros, $page, $limite);

        safe_json([
            'success' => true,
            'data' => $resultado['data'],
            'total' => $resultado['total'],
            'total_paginas' => $resultado['total_paginas'],
            'pagina' => $resultado['pagina']
        ]);
    } catch (Throwable $e) {
        http_response_code(500);
        safe_json(['success' => false, 'error' => 'Error del servidor', 'message' => $e->getMessage()]);
    }
}

// ============================================================
// OPERACIONES DE ACCIÓN (POST con redirecciones)
// ============================================================

// SAVE (Registro manual) - Soporta PRG vía fetch (AJAX) y fallback POST tradicional
if ($opera === "save") {
    try {
        if (!csrf_valido()) {
            if (is_ajax_request()) {
                http_response_code(403);
                safe_json(['success' => false, 'message' => 'Sesión de formulario expirada, por favor recargue la página e intente de nuevo.']);
            }
            echo "<script>alert('Sesión de formulario expirada, por favor recargue la página e intente de nuevo.');window.location.href='home.php?pg=$pg';</script>";
            exit;
        }
        // Compatibilidad de nombres de campos: compe / idcom / compe_otro
        $rawCompe = '';
        if (!empty($_POST['compe_otro']) && isset($_POST['compe']) && $_POST['compe'] === '__OTRO__') {
            $rawCompe = trim($_POST['compe_otro']);
        } elseif (!empty($_POST['compe'])) {
            $rawCompe = trim($_POST['compe']);
        } elseif (!empty($_POST['idcom'])) {
            $rawCompe = trim($_POST['idcom']);
        }

        // Compatibilidad: resapr / idres / resapr_otro
        $rawRes = '';
        if (!empty($_POST['resapr_otro']) && isset($_POST['resapr']) && $_POST['resapr'] === '__OTRO__') {
            $rawRes = trim($_POST['resapr_otro']);
        } elseif (!empty($_POST['resapr'])) {
            $rawRes = trim($_POST['resapr']);
        } elseif (!empty($_POST['idres'])) {
            $rawRes = trim($_POST['idres']);
        }

        $extrComp = $mjui->extraerCompetencia($rawCompe);
        $extrRes  = $mjui->extraerResultado($rawRes);

        // Relaciones reales (aprendiz por documento, ficha, resultado -> competencia)
        $rel = jui_resolver_relaciones($mjui, $ndoce, $idusu, $idfic, $extrComp, $extrRes);
        if (!empty($rel['error'])) {
            if (is_ajax_request()) {
                safe_json(['success' => false, 'message' => $rel['error']]);
            }
            echo "<script>alert('" . htmlspecialchars($rel['error'], ENT_QUOTES) . "');window.location.href='home.php?pg=$pg';</script>";
            exit;
        }
        $idusu = (int)$rel['idusu'];

        $ndocinsSave = $docIns;

        $datos = [
            'idusu' => $idusu,
            'idfic' => $idfic,
            'idres' => (int)$rel['idres'],
            'idcom' => (int)$rel['idcom'],
            'caljui' => $caljui ?: 'POR EVALUAR',
            'ndocins' => $ndocinsSave,
            'fecha_reporte' => date('Y-m-d'),
            'fecjuieva' => date('Y-m-d')
        ];

        $existente = $mjui->buscarJuicioPorIdusuIdres($idusu, $datos['idres']);
        while (ob_get_level() > 0) { ob_end_clean(); }
        if ($existente) {
            $ok = $mjui->edit($existente['idjui'], $datos);
            if ($ok) {
                $mjui->crearAprobacionResultado($idfic, $datos['idres'], $_SESSION['idusu'] ?? null,
                    ($caljui === 'APROBADO' ? 'aprobado' : ($caljui === 'NO APROBADO' ? 'no aprobado' : 'pendiente')),
                    date('Y-m-d H:i:s'));
            }
            $url = $ok ? "home.php?pg=$pg&msg=updated" : "home.php?pg=$pg&msg=error";
            if ($ok && $rel['aviso'] !== '') { $url .= '&aviso=' . rawurlencode($rel['aviso']); }
            if (is_ajax_request()) {
                safe_json(['success' => (bool)$ok, 'message' => $ok ? 'Juicio actualizado correctamente.' : 'Error al procesar', 'redirect' => $url]);
            }
            echo "<script>window.location.href='$url';</script>";
        } else {
            $ok = $mjui->save($datos);
            if ($ok) {
                $mjui->crearAprobacionResultado($idfic, $datos['idres'], $_SESSION['idusu'] ?? null,
                    ($caljui === 'APROBADO' ? 'aprobado' : ($caljui === 'NO APROBADO' ? 'no aprobado' : 'pendiente')),
                    date('Y-m-d H:i:s'));
            }
            $url = $ok ? "home.php?pg=$pg&msg=created" : "home.php?pg=$pg&msg=error";
            if ($ok && $rel['aviso'] !== '') { $url .= '&aviso=' . rawurlencode($rel['aviso']); }
            if (is_ajax_request()) {
                safe_json(['success' => (bool)$ok, 'message' => $ok ? 'Juicio registrado correctamente.' : 'Error al procesar', 'redirect' => $url]);
            }
            echo "<script>window.location.href='$url';</script>";
        }
    } catch (Exception $e) {
        error_log("ERROR save: " . $e->getMessage());
        if (is_ajax_request()) {
            safe_json(['success' => false, 'message' => $e->getMessage()]);
        }
        echo "<script>alert('Error: " . htmlspecialchars($e->getMessage()) . "');window.location.href='home.php?pg=$pg';</script>";
    }
    exit;
}

// EDIT (Actualización completa) - PRG vía fetch para evitar reenvío en F5
if ($opera === "edit") {
    try {
        if (!csrf_valido()) {
            if (is_ajax_request()) {
                http_response_code(403);
                safe_json(['success' => false, 'message' => 'Sesión de formulario expirada, por favor recargue la página.']);
            }
            echo "<script>alert('Sesión de formulario expirada, por favor recargue la página e intente de nuevo.');window.location.href='home.php?pg=$pg';</script>";
            exit;
        }
        if (!$idjui) {
            if (is_ajax_request()) {
                safe_json(['success' => false, 'message' => 'ID de juicio no proporcionado']);
            }
            echo "<script>alert('ID de juicio no proporcionado');window.location.href='home.php?pg=$pg';</script>";
            exit;
        }

        $rawCompe = '';
        if (!empty($_POST['compe_otro']) && isset($_POST['compe']) && $_POST['compe'] === '__OTRO__') {
            $rawCompe = trim($_POST['compe_otro']);
        } elseif (!empty($_POST['compe'])) {
            $rawCompe = trim($_POST['compe']);
        } elseif (!empty($_POST['idcom'])) {
            $rawCompe = trim($_POST['idcom']);
        }

        $rawRes = '';
        if (!empty($_POST['resapr_otro']) && isset($_POST['resapr']) && $_POST['resapr'] === '__OTRO__') {
            $rawRes = trim($_POST['resapr_otro']);
        } elseif (!empty($_POST['resapr'])) {
            $rawRes = trim($_POST['resapr']);
        } elseif (!empty($_POST['idres'])) {
            $rawRes = trim($_POST['idres']);
        }

        $extrComp = $mjui->extraerCompetencia($rawCompe);
        $extrRes  = $mjui->extraerResultado($rawRes);

        // Mismas reglas de integridad que en el alta (aprendiz, ficha y
        // relación real resultado -> competencia).
        $rel = jui_resolver_relaciones($mjui, $ndoce, $idusu, $idfic, $extrComp, $extrRes);
        if (!empty($rel['error'])) {
            if (is_ajax_request()) {
                safe_json(['success' => false, 'message' => $rel['error']]);
            }
            echo "<script>alert('" . htmlspecialchars($rel['error'], ENT_QUOTES) . "');window.location.href='home.php?pg=$pg';</script>";
            exit;
        }
        $idusu = (int)$rel['idusu'];

        $ndocinsEdit = $docIns;

        $datos = [
            'idusu' => $idusu,
            'idfic' => $idfic,
            'idres' => (int)$rel['idres'],
            'idcom' => (int)$rel['idcom'],
            'caljui' => $caljui ?: 'POR EVALUAR',
            'ndocins' => $ndocinsEdit,
            'fecha_reporte' => date('Y-m-d'),
            'fecjuieva' => date('Y-m-d')
        ];

        $ok = $mjui->edit($idjui, $datos);
        if ($ok) {
            $mjui->crearAprobacionResultado($idfic, $datos['idres'], $_SESSION['idusu'] ?? null,
                ($caljui === 'APROBADO' ? 'aprobado' : ($caljui === 'NO APROBADO' ? 'no aprobado' : 'pendiente')),
                date('Y-m-d H:i:s'));
        }
        while (ob_get_level() > 0) { ob_end_clean(); }
        $url = $ok ? "home.php?pg=$pg&msg=edit" : "home.php?pg=$pg&msg=error";
        if ($ok && $rel['aviso'] !== '') { $url .= '&aviso=' . rawurlencode($rel['aviso']); }
        if (is_ajax_request()) {
            safe_json(['success' => (bool)$ok, 'message' => $ok ? 'Cambios guardados correctamente.' : 'Ocurrió un error al procesar la solicitud.', 'redirect' => $url]);
        }
        echo "<script>window.location.href='$url';</script>";
    } catch (Exception $e) {
        error_log("ERROR edit: " . $e->getMessage());
        if (is_ajax_request()) {
            safe_json(['success' => false, 'message' => $e->getMessage()]);
        }
        echo "<script>alert('Error: " . htmlspecialchars($e->getMessage()) . "');window.location.href='home.php?pg=$pg';</script>";
    }
    exit;
}

// DELETE
// SEGURIDAD: antes se ejecutaba con una simple navegación GET, lo que
// permite que un enlace en un sitio externo (o un rastreador/preload
// de enlaces) borre un registro a nombre del usuario logueado (CSRF,
// y además GET dejó de ser una operación "segura"/idempotente). Ahora
// exige POST + token CSRF y responde en JSON para uso vía fetch().
if ($opera === "eli" && $idjui) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_valido()) {
        http_response_code(403);
        safe_json(['success' => false, 'error' => 'Solicitud inválida o token de seguridad ausente. Recargue la página.']);
    }
    try {
        $ok = $mjui->del($idjui);
        safe_json(['success' => (bool)$ok, 'message' => $ok ? 'Juicio eliminado del sistema.' : 'No se pudo eliminar el juicio.']);
    } catch (Exception $e) {
        error_log("ERROR eli: " . $e->getMessage());
        http_response_code(500);
        safe_json(['success' => false, 'error' => $e->getMessage()]);
    }
}

// EDI (cargar un único registro, patrón del sistema)
if ($opera === "edi" && $idjui) {
    $mjui->setIdjui($idjui);
    $datOne = $mjui->getOne();
}

// ============================================================
// IMPORTACIÓN CSV (2 pasos, JSON)
// ============================================================

// PASO 1: Validar CSV subido y guardar token temporal
if ($opera === "import" || $opera === "import_step1") {
    try {
        if (!csrf_valido()) {
            http_response_code(403);
            safe_json(['success' => false, 'error' => 'Token de seguridad inválido o ausente. Recargue la página.']);
        }
        if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            safe_json(['success' => false, 'error' => 'Debe adjuntar un archivo CSV válido']);
        }
        $tmpPath = $_FILES['csv_file']['tmp_name'];
        $origName = basename($_FILES['csv_file']['name']);
        $token = 'csv_' . time() . '_' . bin2hex(random_bytes(4));
        $destDir = sys_get_temp_dir() . '/horcjuev';
        if (!is_dir($destDir)) { @mkdir($destDir, 0775, true); }
        $destPath = $destDir . '/' . $token . '.csv';
        if (!move_uploaded_file($tmpPath, $destPath)) {
            if (!copy($_FILES['csv_file']['tmp_name'], $destPath)) {
                safe_json(['success' => false, 'error' => 'No se pudo almacenar temporalmente el CSV']);
            }
        }

        $estructura = $mjui->leerEstructuraCSV($destPath);
        if (empty($estructura['header'])) {
            @unlink($destPath);
            safe_json(['success' => false, 'error' => 'El archivo no tiene el formato esperado de juicios evaluativos']);
        }

        $_SESSION['import_csv'][$token] = [
            'path' => $destPath,
            'name' => $origName,
            'metadatos' => $estructura['metadatos']
        ];

        safe_json([
            'success' => true,
            'ok' => true,
            'message' => 'Archivo validado correctamente',
            'token' => $token,
            'name' => $origName,
            'filas' => $estructura['filas'],
            'resumen' => [
                'validos' => $estructura['filas'],
                'total'   => $estructura['filas']
            ],
            'metadatos' => $estructura['metadatos']
        ]);
    } catch (Throwable $e) {
        safe_json(['success' => false, 'error' => $e->getMessage()]);
    }
}

// PASO 2: Importar el CSV validado usando el token
if ($opera === "import_step2") {
    try {
        if (!csrf_valido()) {
            http_response_code(403);
            safe_json(['success' => false, 'error' => 'Token de seguridad inválido o ausente. Recargue la página.']);
        }
        $token = isset($_POST['token']) ? trim($_POST['token']) : '';
        $idfic = isset($_POST['idfic']) ? trim($_POST['idfic']) : '';
        if (!$token || empty($_SESSION['import_csv'][$token])) {
            safe_json(['success' => false, 'error' => 'Token inválido o expirado. Vuelva a subir el archivo.']);
        }
        $meta = $_SESSION['import_csv'][$token];
        $csvPath = $meta['path'];
        $name = $meta['name'];

        if (!file_exists($csvPath)) {
            safe_json(['success' => false, 'error' => 'Archivo CSV temporal no encontrado. Vuelva a subirlo.']);
        }

        $resultado = $mjui->procesarCSVJuicios($csvPath, $idfic, $name);

        @unlink($csvPath);
        unset($_SESSION['import_csv'][$token]);

        $_SESSION['resultado_importacion'] = $resultado;

        $msg = "Importación realizada: {$resultado['procesadas']} procesados, {$resultado['insertados']} insertados, {$resultado['actualizados']} actualizados, {$resultado['error']} errores.";
        if (!empty($resultado['advertencias'])) {
            $msg .= " Avisos: {$resultado['advertencias']} resultado(s) conservaron la competencia registrada en el maestro.";
        }
        safe_json([
            'success' => true,
            'ok' => true,
            'message' => $msg,
            'resultado' => $resultado,
            'resumen' => [
                'validos' => $resultado['procesadas'],
                'total' => $resultado['total_filas']
            ],
            'idfic' => $idfic
        ]);
    } catch (Throwable $e) {
        safe_json(['success' => false, 'error' => $e->getMessage()]);
    }
}

// CSV directo (legacy)
if ($opera === "import_csv") {
    try {
        if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            safe_json(['success' => false, 'error' => 'Debe adjuntar un archivo CSV válido']);
        }
        $idfic = isset($_POST['idfic']) ? trim($_POST['idfic']) : '';
        if ($idfic === '') {
            safe_json(['success' => false, 'error' => 'Falta el parámetro idfic']);
        }
        $tmp = $_FILES['csv_file']['tmp_name'];
        $name = basename($_FILES['csv_file']['name']);
        $resultado = $mjui->procesarCSVJuicios($tmp, $idfic, $name);
        $msgLegacy = "Importación completada: {$resultado['procesadas']} procesados, {$resultado['error']} errores";
        if (!empty($resultado['advertencias'])) {
            $msgLegacy .= ", {$resultado['advertencias']} avisos de competencia";
        }
        safe_json([
            'success' => true,
            'ok' => true,
            'resultado' => $resultado,
            'message' => $msgLegacy
        ]);
    } catch (Throwable $e) {
        safe_json(['success' => false, 'error' => $e->getMessage()]);
    }
}

// Ver resultados de la última importación (JSON)
if ($opera === "ver_importados") {
    safe_json([
        'success' => true,
        'ok' => true,
        'resultado' => $_SESSION['resultado_importacion'] ?? null
    ]);
}

// ============================================================
// PREPARACIÓN DE DATOS PARA LA VISTA
// ============================================================

// Instructor logueado (para mostrar en formulario)
$instructorLogueado = null;
$fichaInstructor = null;
if (isset($_SESSION['idusu'])) {
    $instructorLogueado = $mjui->cargarInfoUsuario($_SESSION['idusu']);
    $fichaInstructor = $mjui->cargarFichaInstructor($_SESSION['idusu']);
}

// Datos base para los selects del formulario (formulario edit/save mantiene alcance por instructor)
$datUsu   = $mjui->getAllUsu();
$datFic   = $hacceso->filtrarPorFicha($mjui->getAllfic(), 'idfic', $scopeCtx);
$datCompe = $mjui->getCompetenciasPorInstructor($_SESSION['idusu'] ?? 0);
$datRes   = $mjui->getResultadosPorInstructor($_SESSION['idusu'] ?? 0);
// Filtro de competencia: debe listar TODAS las competencias válidamente relacionadas con juicios (j.idcom = c.idcom), no solo las del instructor ni todas de la tabla
$datCompeFiltro = $mjui->getCompetenciasConJuicios();

// Datos para la tabla de juicios (respaldo)
$datAll   = $mjui->getAll();

// Dashboard
$estadisticas     = $mjui->getDashboard();
$datFichaDash     = $mjui->getJuiciosPorFicha();
$datCompeDash     = $mjui->getJuiciosPorCompetencia();
$datImportDash    = $mjui->getUltimasImportaciones();

// Mensaje de operación desde la URL (save/edit/delete)
$msgSave = '';
$tipoMsg = 'success';
if (isset($_GET['msg'])) {
    switch ($_GET['msg']) {
        case 'created':   $msgSave = 'Juicio registrado correctamente.'; $tipoMsg='success'; break;
        case 'updated':   $msgSave = 'Juicio actualizado correctamente.'; $tipoMsg='success'; break;
        case 'edit':      $msgSave = 'Cambios guardados correctamente.'; $tipoMsg='success'; break;
        case 'eliminado': $msgSave = 'Juicio eliminado del sistema.'; $tipoMsg='info'; break;
        case 'error':     $msgSave = 'Ocurrió un error al procesar la solicitud.'; $tipoMsg='danger'; break;
    }
}
// Aviso de integridad competencia-resultado devuelto por save/edit
if (!empty($_GET['aviso'])) {
    $avisoUrl = trim((string)$_GET['aviso']);
    if ($avisoUrl !== '') {
        $msgSave = trim($msgSave . ' ' . mb_substr($avisoUrl, 0, 240));
        if ($tipoMsg === 'success') { $tipoMsg = 'info'; }
    }
}
?>