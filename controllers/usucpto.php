<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once(__DIR__ . '/../models/usumpto.php');

$mod = new usumpto();
$ope = isset($_POST['ope']) ? $_POST['ope'] : (isset($_GET['ope']) ? $_GET['ope'] : null);

$isApiRequest = $ope !== null && in_array($ope, [
    'dashboard', 'datatable', 'empresas', 'aprendicesSin', 'miPatrocinio',
    'guardar', 'empresasRanking', 'aprendicesEmpresa', 'cancelar',
    'actaCancelacion', 'actaSeguimiento', 'miEmpresa'
]);

if ($isApiRequest) {
    header('Content-Type: application/json; charset=utf-8');
    try {
        switch ($ope) {

        // Datos para las tarjetas del dashboard
        case 'dashboard':
            echo json_encode($mod->dashboard());
            break;

        // Datos para el DataTable general de aprendices
        case 'datatable':
            $filas = $mod->listarAprendices();
            $data = array_map(function ($f) {
                $badge = '<span class="badge bg-secondary">Sin registrar</span>';
                if ($f['estado_codigo'] == 1) {
                    $badge = '<span class="badge bg-success"><i class="fa-solid fa-check"></i> Activo (' . htmlspecialchars($f['nomemp'] ?? '') . ')</span>';
                } elseif ($f['estado_codigo'] == 2) {
                    $badge = '<span class="badge bg-danger"><i class="fa-solid fa-xmark"></i> Cancelado (' . htmlspecialchars($f['nomemp'] ?? '') . ')</span>';
                } elseif ($f['estado_codigo'] == 3) {
                    $badge = '<span class="badge bg-info"><i class="fa-solid fa-flag-checkered"></i> Finalizado</span>';
                }

                $jefe = !empty($f['nomconemp']) 
                    ? htmlspecialchars($f['nomconemp']) . ' (' . htmlspecialchars($f['telemp'] ?? '') . ')' 
                    : '<span class="text-muted small">No registrado</span>';

                $fec = !empty($f['fecinipat']) ? htmlspecialchars(date('Y-m-d', strtotime($f['fecinipat']))) : '<span class="text-muted small">N/A</span>';

                return [
                    'idusu'       => $f['idusu'],
                    'nomusu'      => $f['nomusu'],
                    'ndocusu'     => $f['ndocusu'],
                    'nomemp'      => $f['nomemp'] ?? '<span class="text-muted">Ninguna</span>',
                    'fecinipat'   => $fec,
                    'jefe'        => $jefe,
                    'vinculado'   => $badge,
                    'estado_num'  => $f['estado_codigo'],
                    'idpat'       => $f['idpat'] ?? null
                ];
            }, $filas);
            echo json_encode(['data' => $data]);
            break;

        // Combo de empresas para el formulario
        case 'empresas':
            echo json_encode($mod->listarEmpresas());
            break;

        // Combo de aprendices sin patrocinio activo
        case 'aprendicesSin':
            echo json_encode($mod->listarAprendicesSinPatrocinio());
            break;

        // Obtener el patrocinio del usuario actual (aprendiz)
        case 'miPatrocinio':
            $idusu = isset($_REQUEST['idusu']) ? intval($_REQUEST['idusu']) : (isset($_SESSION['idusu']) ? intval($_SESSION['idusu']) : 0);
            $pat = $mod->obtenerPatrocinioUsuario($idusu);
            echo json_encode(['ok' => true, 'data' => $pat]);
            break;

        // Guardar nuevo patrocinio
        case 'guardar':
            $idusu     = isset($_POST['idusu']) ? intval($_POST['idusu']) : (isset($_SESSION['idusu']) ? intval($_SESSION['idusu']) : 0);
            $idemp     = isset($_POST['idemp']) ? intval($_POST['idemp']) : 0;
            $fecinipat = isset($_POST['fecinipat']) ? trim($_POST['fecinipat']) : date('Y-m-d');
            $nomconemp = isset($_POST['nomconemp']) ? trim($_POST['nomconemp']) : (isset($_POST['nomjefpat']) ? trim($_POST['nomjefpat']) : '');
            $telemp    = isset($_POST['telemp']) ? trim($_POST['telemp']) : (isset($_POST['teljefpat']) ? trim($_POST['teljefpat']) : '');
            $usucre    = isset($_SESSION['idusu']) ? intval($_SESSION['idusu']) : 1;

            if (!$idusu || !$idemp) {
                echo json_encode(['ok' => false, 'msg' => 'Debe seleccionar un aprendiz y una empresa.']);
                break;
            }

            if (empty($nomconemp)) {
                echo json_encode(['ok' => false, 'msg' => 'Debe ingresar el nombre del jefe o encargado.']);
                break;
            }

            echo json_encode($mod->registrarPatrocinio($idusu, $idemp, $fecinipat, $nomconemp, $telemp, $usucre));
            break;

        // Listado de empresas ordenadas de mayor a menor cantidad de aprendices
        case 'empresasRanking':
            $empresas = $mod->listarEmpresasRanking();
            echo json_encode(['ok' => true, 'data' => $empresas]);
            break;

        // Listar aprendices de una empresa con métricas de inasistencia, llamados y rendimiento académico
        case 'aprendicesEmpresa':
            $idemp = isset($_REQUEST['idemp']) ? intval($_REQUEST['idemp']) : 0;
            if (!$idemp) {
                echo json_encode(['ok' => false, 'msg' => 'Empresa no especificada.', 'data' => []]);
                break;
            }
            $aprendices = $mod->listarAprendicesPorEmpresa($idemp);
            echo json_encode(['ok' => true, 'data' => $aprendices]);
            break;

        // Cancelar patrocinio (empresario o instructor)
        case 'cancelar':
            $idpat  = isset($_POST['idpat']) ? intval($_POST['idpat']) : 0;
            $motivo = isset($_POST['motivo']) ? trim($_POST['motivo']) : 'Cancelación de contrato de patrocinio';

            if (!$idpat) {
                echo json_encode(['ok' => false, 'msg' => 'ID de patrocinio inválido.']);
                break;
            }

            echo json_encode($mod->cancelarPatrocinio($idpat, $motivo));
            break;

        // Datos para Acta de Cancelación
        case 'actaCancelacion':
            $idpat  = isset($_REQUEST['idpat']) ? intval($_REQUEST['idpat']) : 0;
            $motivo = isset($_REQUEST['motivo']) ? trim($_REQUEST['motivo']) : null;
            $datos  = $mod->obtenerDatosActaCancelacion($idpat, $motivo);
            if ($datos) {
                echo json_encode(['ok' => true, 'data' => $datos]);
            } else {
                echo json_encode(['ok' => false, 'msg' => 'No se encontraron datos del patrocinio para generar el acta.']);
            }
            break;

        // Datos para Acta de Seguimiento
        case 'actaSeguimiento':
            $idemp = isset($_REQUEST['idemp']) ? intval($_REQUEST['idemp']) : 0;
            $datos = $mod->obtenerDatosActaSeguimiento($idemp);
            if ($datos) {
                echo json_encode(['ok' => true, 'data' => $datos]);
            } else {
                echo json_encode(['ok' => false, 'msg' => 'No se encontraron datos de la empresa.']);
            }
            break;

        // Obtener datos contextuales del empresario logueado
        case 'miEmpresa':
            $idusu   = isset($_SESSION['idusu']) ? intval($_SESSION['idusu']) : 0;
            $ndocusu = isset($_SESSION['ndocusu']) ? $_SESSION['ndocusu'] : null;
            $empresa = $mod->obtenerEmpresaDeUsuario($idusu, $ndocusu);
            if ($empresa) {
                $aprendices = $mod->listarAprendicesPorEmpresa($empresa['idemp']);
                echo json_encode(['ok' => true, 'empresa' => $empresa, 'aprendices' => $aprendices]);
            } else {
                echo json_encode(['ok' => false, 'msg' => 'No se encontró empresa vinculada a su usuario.']);
            }
            break;

default:
                http_response_code(400);
                echo json_encode(['ok' => false, 'msg' => 'Operación no reconocida.']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'msg' => 'Error en el servidor: ' . $e->getMessage()]);
    }
    exit;
}

$idpat     = isset($_REQUEST['idpat'])    ? $_REQUEST['idpat']    : NULL;
$idusu     = isset($_POST['idusu'])       ? $_POST['idusu']       : NULL;
$idemp     = isset($_POST['idemp'])       ? $_POST['idemp']       : NULL;
$fecinipat = isset($_POST['fecinipat'])   ? $_POST['fecinipat']   : date("Y-m-d");
$nomjefpat = isset($_POST['nomjefpat'])   ? $_POST['nomjefpat']   : NULL;
$teljefpat = isset($_POST['teljefpat'])   ? $_POST['teljefpat']   : NULL;
$corjefpat = isset($_POST['corjefpat'])   ? $_POST['corjefpat']   : NULL;
$estpat    = isset($_POST['estpat'])      ? $_POST['estpat']      : 1;
$ope       = isset($_REQUEST['ope'])      ? $_REQUEST['ope']      : NULL;
$dtOn      = NULL;

$musuvpto = new usumpto();

// Operación: Guardar nuevo patrocinio
if ($ope == "save") {
    $idusuActual = isset($_SESSION['idusu']) ? intval($_SESSION['idusu']) : 0;
    $idempInt    = $idemp ? intval($idemp) : 0;
    $idusuReg    = $idusu ? intval($idusu) : $idusuActual;

    if ($idusuReg && $idempInt && $nomjefpat && $teljefpat) {
        $musuvpto->registrarPatrocinio(
            $idusuReg,
            $idempInt,
            $fecinipat,
            $nomjefpat,
            $teljefpat,
            $idusuActual
        );
    }
}

// Operación: Editar (cargar datos)
if ($ope == "edi" && $idpat) {
    // Pendiente: agregar método en model para obtener un patrocinio por id
    $dtOn = NULL;
}

// Operación: Eliminar patrocinio
if ($ope == "eli" && $idpat) {
    $db  = $musuvpto->get_conexion();
    $sql = "DELETE FROM patrocinio WHERE idpat = :idpat";
    $stmt = $db->prepare($sql);
    $stmt->execute([':idpat' => intval($idpat)]);
}

// Operación: Cambiar estado (cancelar o reactivar)
if ($ope == "act" && $idpat && $estpat) {
    $db  = $musuvpto->get_conexion();
    $sql = "UPDATE patrocinio SET estpat = :estpat WHERE idpat = :idpat";
    $stmt = $db->prepare($sql);
    $stmt->execute([':estpat' => intval($estpat), ':idpat' => intval($idpat)]);
}

// Cargar todos los patrocinios con datos completos
$datAll = $musuvpto->listarAprendices();

// Separar por estado: activos (estado_codigo=1) y cancelados (2)
$datAct = [];
$datCan = [];
foreach ($datAll as $row) {
    // Normalizar alias para que la vista use los mismos nombres
    $row['nomjefpat'] = $row['nomconemp'] ?? '';
    $row['teljefpat'] = $row['telemp']    ?? '';
    if ($row['estado_codigo'] == 1) {
        $datAct[] = $row;
    } elseif ($row['estado_codigo'] == 2) {
        $datCan[] = $row;
    }
}

$totAct = count($datAct);
$totCan = count($datCan);
$totPat = $totAct + $totCan;

?>

