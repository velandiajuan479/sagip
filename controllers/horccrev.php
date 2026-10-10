<?php
/**
 * Controlador de Criterios de Evaluación.
 *
 * Recibe y valida las operaciones de la página 1546 y las delega al modelo
 * Hormcrev. Las operaciones que escriben datos exigen POST y token CSRF.
 */

require_once __DIR__ . '/../models/hormcrev.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$autCorrecto = isset($_SESSION['aut'], $_SESSION['idusu'])
    && hash_equals('jY238Jn&5Hhass.??44aa@@fg(80', (string)$_SESSION['aut']);

if (!$autCorrecto) {
    http_response_code(403);
    exit('No tiene una sesión autenticada válida.');
}

$idperActual = (int)($_SESSION['idper'] ?? ($_SESSION['idpergen'] ?? 0));
$idusuActual = (int)($_SESSION['idusu'] ?? 0);

$esAdmin = in_array($idperActual, [1, 2, 21, 28], true);
$esInstructor = in_array($idperActual, [7, 32, 42], true);
$esAprendiz = in_array($idperActual, [4, 8, 22, 23, 39, 41], true);
$puedeGestionar = $idusuActual > 0 && ($esAdmin || $esInstructor);

$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$pg = trim((string)($_REQUEST['pg'] ?? '1546'));
$ope = trim((string)($_POST['ope'] ?? ($_GET['ope'] ?? '')));

$parsearId = static function ($valor) {
    if ($valor === null || $valor === '') {
        return 0;
    }
    $id = filter_var($valor, FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1]
    ]);
    return $id === false ? 0 : (int)$id;
};

$filtroIdins = trim((string)($_REQUEST['filtro_idins'] ?? ($_REQUEST['idins'] ?? '')));
$filtroIdfic = trim((string)($_REQUEST['filtro_idfic'] ?? ($_REQUEST['idfic'] ?? '')));
$buscar = trim((string)($_REQUEST['buscar'] ?? ''));
$idcriSolicitado = $parsearId($_REQUEST['idcri'] ?? 0);

$modelo = new Hormcrev();
$editar = null;
$idcriSeleccionado = null;
$mensaje = null;
$error = null;
$formRows = null;
$formIdins = $filtroIdins;
$formIdfic = $filtroIdfic;

if (empty($_SESSION['criterios_csrf'])) {
    $_SESSION['criterios_csrf'] = bin2hex(random_bytes(32));
}
$csrfToken = (string)$_SESSION['criterios_csrf'];

$formGuard = bin2hex(random_bytes(16));
$guardesProcesados = $_SESSION['criterios_guards_procesados'] ?? [];
if (!is_array($guardesProcesados)) {
    $guardesProcesados = [];
}

/**
 * Guardar uno o varios criterios nuevos, o actualizar uno existente.
 */
if ($requestMethod === 'POST' && $ope === 'save_criterio') {
    $tokenEnviado = (string)($_POST['csrf_token'] ?? '');
    $idcriPost = $parsearId($_POST['idcri'] ?? 0);
    $idinsPost = $parsearId($_POST['idins'] ?? 0);

    if (!$puedeGestionar) {
        $error = 'No tiene permisos para registrar o modificar criterios de evaluación.';
    } elseif ($idusuActual <= 0) {
        $error = 'No fue posible obtener el usuario autenticado de la sesión.';
    } elseif ($tokenEnviado === '' || !hash_equals($csrfToken, $tokenEnviado)) {
        $error = 'La sesión del formulario venció o es inválida. Recargue la página e intente nuevamente.';
    } else {
        $createGuard = trim((string)($_POST['create_guard'] ?? ''));
        $guardKey = '';

        if ($idcriPost === 0) {
            if (!preg_match('/^[a-f0-9]{32}$/D', $createGuard)) {
                $error = 'No fue posible validar el envío del formulario.';
            } else {
                $guardKey = hash('sha256', $createGuard);
                if (isset($guardesProcesados[$guardKey])) {
                    $mensaje = 'Este formulario ya había sido procesado. No se creó un criterio duplicado.';
                }
            }
        }

        if ($error === null && $mensaje === null) {
            $nomcriEnviado = $_POST['nomcri'] ?? null;
            $vscumEnviado = $_POST['vscum'] ?? null;
            $vparEnviado = $_POST['vpar'] ?? null;
            $vncumEnviado = $_POST['vncum'] ?? null;

            if (!is_array($nomcriEnviado) || !is_array($vscumEnviado)
                || !is_array($vparEnviado) || !is_array($vncumEnviado)) {
                $error = 'La estructura de los criterios enviados no es válida.';
            } else {
                $cantidad = count($nomcriEnviado);
                if ($cantidad === 0
                    || count($vscumEnviado) !== $cantidad
                    || count($vparEnviado) !== $cantidad
                    || count($vncumEnviado) !== $cantidad) {
                    $error = 'Complete todos los datos de los criterios antes de guardar.';
                } elseif ($idcriPost > 0 && $cantidad !== 1) {
                    $error = 'Una modificación solo puede enviar un criterio y un idcri real.';
                }
            }

            $filas = [];
            $formRows = [];

            if ($error === null) {
                foreach (array_values($nomcriEnviado) as $indice => $nombreEnviado) {
                    $nomcri = trim((string)$nombreEnviado);
                    $longitud = function_exists('mb_strlen')
                        ? mb_strlen($nomcri, 'UTF-8')
                        : strlen($nomcri);

                    $vscum = filter_var($vscumEnviado[$indice] ?? null, FILTER_VALIDATE_INT, [
                        'options' => ['min_range' => 0, 'max_range' => 100]
                    ]);
                    $vpar = filter_var($vparEnviado[$indice] ?? null, FILTER_VALIDATE_INT, [
                        'options' => ['min_range' => 0, 'max_range' => 100]
                    ]);
                    $vncum = filter_var($vncumEnviado[$indice] ?? null, FILTER_VALIDATE_INT, [
                        'options' => ['min_range' => 0, 'max_range' => 100]
                    ]);

                    $formRows[] = [
                        'idcri' => $idcriPost,
                        'nomcri' => $nomcri,
                        'vscum' => (string)($vscumEnviado[$indice] ?? ''),
                        'vpar' => (string)($vparEnviado[$indice] ?? ''),
                        'vncum' => (string)($vncumEnviado[$indice] ?? '')
                    ];

                    if ($nomcri === '' || $longitud > 255) {
                        $error = 'Cada criterio debe tener un nombre no vacío de máximo 255 caracteres.';
                        break;
                    }
                    if ($vscum === false || $vpar === false || $vncum === false) {
                        $error = 'Cada criterio debe tener valores numéricos enteros entre 0 y 100.';
                        break;
                    }

                    $filas[] = [
                        'nomcri' => $nomcri,
                        'vscum' => (int)$vscum,
                        'vpar' => (int)$vpar,
                        'vncum' => (int)$vncum
                    ];
                }
            }

            if ($error === null) {
                try {
                    if ($idcriPost > 0) {
                        $criterioExistente = $modelo->getCriterioById($idcriPost);
                        if ($criterioExistente === null) {
                            throw new DomainException('No se encontró el criterio solicitado para modificar.');
                        }

                        $fila = $filas[0];
                        $modelo->setIdcri($idcriPost);
                        $modelo->setNomcri($fila['nomcri']);
                        $modelo->setVscum($fila['vscum']);
                        $modelo->setVpar($fila['vpar']);
                        $modelo->setVncum($fila['vncum']);
                        $modelo->edit();

                        $editar = $modelo->getCriterioById($idcriPost);
                        $idcriSeleccionado = $idcriPost;
                        $formIdins = (string)$editar['idins'];
                        $formIdfic = (string)($editar['idfic'] ?? '');
                        $filtroIdins = $formIdins;
                        $filtroIdfic = $formIdfic;
                        $formRows = null;
                        $mensaje = "Criterio #{$idcriPost} actualizado correctamente en la base de datos.";
                    } else {
                        if ($idinsPost <= 0) {
                            throw new DomainException('Seleccione un instrumento válido.');
                        }

                        $idsGenerados = $modelo->saveBatch($filas, $idinsPost, $idusuActual);

                        $guardesProcesados[$guardKey] = [
                            'fecha' => date('Y-m-d H:i:s'),
                            'ids' => $idsGenerados
                        ];
                        if (count($guardesProcesados) > 30) {
                            $guardesProcesados = array_slice($guardesProcesados, -30, null, true);
                        }
                        $_SESSION['criterios_guards_procesados'] = $guardesProcesados;

                        $filtroIdins = (string)$idinsPost;
                        $filtroIdfic = '';
                        $formIdins = (string)$idinsPost;
                        $formIdfic = '';
                        $formRows = null;
                        $editar = null;
                        $idcriSeleccionado = null;

                        $idsTexto = implode(', #', $idsGenerados);
                        $mensaje = count($idsGenerados) === 1
                            ? "Criterio registrado correctamente con el ID generado #{$idsTexto}."
                            : count($idsGenerados) . ' criterios registrados correctamente con los IDs: #' . $idsTexto . '.';
                    }
                } catch (DomainException $e) {
                    $error = $e->getMessage();
                } catch (Throwable $e) {
                    error_log('horccrev::save_criterio - ' . $e->getMessage());
                    $error = 'Error de base de datos al guardar el criterio: ' . $e->getMessage();
                }
            }
        }
    }
}

/**
 * Eliminar un criterio mediante POST, token CSRF e idcri real.
 */
if ($requestMethod === 'POST' && $ope === 'del_criterio') {
    $tokenEnviado = (string)($_POST['csrf_token'] ?? '');
    $idcriPost = $parsearId($_POST['idcri'] ?? 0);
    $filtroIdins = trim((string)($_POST['filtro_idins'] ?? ''));
    $filtroIdfic = trim((string)($_POST['filtro_idfic'] ?? ''));

    if (!$puedeGestionar) {
        $error = 'No tiene permisos para eliminar criterios de evaluación.';
    } elseif ($tokenEnviado === '' || !hash_equals($csrfToken, $tokenEnviado)) {
        $error = 'La sesión del formulario venció o es inválida. Recargue la página e intente nuevamente.';
    } elseif ($idcriPost <= 0) {
        $error = 'El criterio seleccionado no tiene un idcri válido.';
    } else {
        try {
            $modelo->setIdcri($idcriPost);
            $modelo->delete();
            $mensaje = "Criterio #{$idcriPost} eliminado correctamente.";
            $editar = null;
            $idcriSeleccionado = null;
        } catch (DomainException $e) {
            $error = $e->getMessage();
        } catch (Throwable $e) {
            error_log('horccrev::del_criterio - ' . $e->getMessage());
            $error = 'Error de base de datos al eliminar el criterio: ' . $e->getMessage();
        }
    }
}

/**
 * Seleccionar un criterio para edición. En este modo la tabla se filtra por
 * idcri y muestra únicamente la información del registro seleccionado.
 */
if ($ope === 'edi_criterio' && $idcriSolicitado > 0) {
    if (!$puedeGestionar) {
        $error = 'No tiene permisos para consultar o modificar ese criterio.';
    } else {
        try {
            $editar = $modelo->getCriterioById($idcriSolicitado);
            if ($editar === null) {
                $error = 'No se encontró el criterio solicitado.';
            } else {
                $idcriSeleccionado = (int)$editar['idcri'];
                $formIdins = (string)$editar['idins'];
                $formIdfic = (string)($editar['idfic'] ?? '');
                $filtroIdins = $formIdins;
                $filtroIdfic = $formIdfic;
            }
        } catch (Throwable $e) {
            error_log('horccrev::edi_criterio - ' . $e->getMessage());
            $error = 'Error de base de datos al consultar el criterio: ' . $e->getMessage();
        }
    }
}

if ($idcriSeleccionado === null) {
    $idcriSeleccionado = $editar ? (int)$editar['idcri'] : null;
}

$criterios = [];
$fichas = [];
$instrumentos = [];
$dashboard = [
    'total_criterios' => 0,
    'instrumentos_con_criterios' => 0,
    'fichas_con_criterios' => 0,
    'calificaciones_asociadas' => 0
];

try {
    $filtros = [
        'idfic' => $filtroIdfic,
        'idins' => $filtroIdins,
        'buscar' => $buscar
    ];
    if ($idcriSeleccionado !== null) {
        $filtros['idcri'] = $idcriSeleccionado;
    }

    $criterios = $modelo->getAllCriterios($filtros);
    $fichas = $modelo->getFichas();
    $instrumentos = $modelo->getInstrumentos();
    $dashboard = $modelo->getDashboardMetrics();
} catch (Throwable $e) {
    error_log('horccrev::cargar_datos - ' . $e->getMessage());
    $error = 'Error al consultar los datos del módulo: ' . $e->getMessage();
}

if ($formRows === null) {
    if ($editar !== null) {
        $formRows = [[
            'idcri' => (int)$editar['idcri'],
            'nomcri' => (string)$editar['nomcri'],
            'vscum' => (string)$editar['vscum'],
            'vpar' => (string)$editar['vpar'],
            'vncum' => (string)$editar['vncum']
        ]];
    } else {
        $formRows = [[
            'idcri' => 0,
            'nomcri' => '',
            'vscum' => '100',
            'vpar' => '50',
            'vncum' => '0'
        ]];
    }
}

if ($editar === null) {
    $formIdins = $filtroIdins;
    $formIdfic = $filtroIdfic;
}
