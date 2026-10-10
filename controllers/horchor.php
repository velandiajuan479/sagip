<?php
// =============================
// Parámetros configurables (deben estar al inicio)
// =============================
if (!defined('CONTRATISTA_MAX_HORAS')) {
    define('CONTRATISTA_MAX_HORAS', 160);
}
if (!defined('PLANTA_MAX_HORAS')) {
    define('PLANTA_MAX_HORAS', 187);
}
if (!defined('FACTOR_FORMACION')) {
    define('FACTOR_FORMACION', 6.4);
}
if (!defined('FACTOR_OTROS')) {
    define('FACTOR_OTROS', 2.1);
}
// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Incluir dependencias con rutas absolutas
include_once __DIR__ . '/../models/conexion.php';
include_once __DIR__ . '/../models/hormhor.php';

$idhor = $_REQUEST['idhor']   ?? null;
$idfic = $_REQUEST['idfic']   ?? null;
$idaul = $_REQUEST['idaul']   ?? null;
$idusu = $_REQUEST['idusu']   ?? null;
$iddia = $_REQUEST['iddia']   ?? null;
$ope   = $_REQUEST['ope']     ?? $_POST['ope'] ?? null;

// Para crear un nuevo horario, limpiar el instructor solo si viene de URL (GET) y no es una edición
// No limpiar si viene del formulario (POST) ya que el usuario puede haber seleccionado un instructor
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET' && $ope !== 'edi' && $ope !== 'edit') {
    $idusu = null;
}

$idare = $_REQUEST['idare'] ?? null;

// Nuevos campos para actividades "Otros"
$es_otros      = $_REQUEST['es_otros']      ?? null;
$actividad     = $_REQUEST['actividad']     ?? null;
$horas_otros   = $_REQUEST['horas_otros']   ?? null;
// Capturar es_formacion_directa (puede venir como array si hay checkbox + hidden)
$es_formacion_directa_raw = $_REQUEST['es_formacion_directa'] ?? 1;
if (is_array($es_formacion_directa_raw)) {
    // Si viene como array, tomar el valor más alto (el checkbox prevalece)
    $es_formacion_directa = max($es_formacion_directa_raw);
} else {
    $es_formacion_directa = $es_formacion_directa_raw;
}

// Nuevos campos para horarios "Transversales"
$es_transversal      = $_REQUEST['es_transversal']      ?? $_POST['es_transversal'] ?? null;
$nombre_transversal  = $_REQUEST['nombre_transversal']  ?? $_POST['nombre_transversal'] ?? null;

// Nuevos campos para fechas de lapsos de tiempo
$fecha_inicio = $_REQUEST['fecha_inicio'] ?? $_POST['fecha_inicio'] ?? null;
$fecha_fin    = $_REQUEST['fecha_fin']   ?? $_POST['fecha_fin'] ?? null;

// Nuevos campos para hora de inicio y fin del horario
$hinihor = $_REQUEST['hinihor'] ?? $_POST['hinihor'] ?? null;
$hfinhor = $_REQUEST['hfinhor'] ?? $_POST['hfinhor'] ?? null;

// Configurar zona horaria de Colombia
date_default_timezone_set('America/Bogota');

// ==========================================
// SISTEMA DE PERMISOS POR PERFILES - INICIO
// ==========================================

/**
 * Obtiene el perfil del usuario actual desde la sesión activa o BD
 * @param int $idusu ID del usuario
 * @return array|null Datos del perfil o null si no existe
 */
function obtenerPerfilUsuario($idusu) {
    if (isset($_SESSION['idper']) && !empty($_SESSION['idper'])) {
        return [
            'idper'  => (int)$_SESSION['idper'],
            'nomper' => $_SESSION['pefnom'] ?? 'Perfil'
        ];
    }
    if (isset($_SESSION['idpergen']) && !empty($_SESSION['idpergen'])) {
        return [
            'idper'  => (int)$_SESSION['idpergen'],
            'nomper' => $_SESSION['pefnom'] ?? 'Perfil'
        ];
    }
    try {
        $sql = "SELECT up.idper, p.nomper 
                FROM usupef up 
                INNER JOIN perfil p ON up.idper = p.idper 
                WHERE up.idusu = :idusu 
                LIMIT 1";
        
        $modelo = new Conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->bindParam(":idusu", $idusu);
        $result->execute();
        $perfil = $result->fetch(PDO::FETCH_ASSOC);
        
        return $perfil ?: null;
    } catch (Exception $e) {
        error_log("Error obteniendo perfil del usuario: " . $e->getMessage());
        return null;
    }
}

/**
 * Determina categorías de roles
 */
function esRolAdmin($idper) {
    return in_array((int)$idper, [1, 2, 21, 28], true);
}

function esRolInstructor($idper) {
    return in_array((int)$idper, [7, 32, 42], true);
}

function esRolAprendiz($idper) {
    return in_array((int)$idper, [4, 8, 22, 23, 39, 41], true);
}

/**
 * Valida si el usuario tiene permisos para realizar una acción específica
 * @param int $idusu ID del usuario
 * @param string $accion Acción a validar ('crear', 'editar', 'eliminar', 'ver', 'imprimir')
 * @return bool True si tiene permisos, false si no
 */
function validarPermisosUsuario($idusu, $accion) {
    $perfil = obtenerPerfilUsuario($idusu);
    if (!$perfil) {
        return false;
    }
    $idper = (int)$perfil['idper'];

    // 1. Administrador: acceso y gestión completa
    if (esRolAdmin($idper)) {
        return true;
    }

    // 2. Aprendiz / Alumno: solo ver e imprimir
    if (esRolAprendiz($idper)) {
        return in_array($accion, ['ver', 'imprimir'], true);
    }

    // 3. Instructor: ver, imprimir y gestionar sus propios registros
    if (esRolInstructor($idper)) {
        return true;
    }

    // Por defecto restrictivo
    return in_array($accion, ['ver', 'imprimir'], true);
}

/**
 * Obtiene la ficha asignada al usuario (para perfiles restringidos)
 * @param int $idusu ID del usuario
 * @return int|null ID de la ficha asignada o null si no tiene
 */
function obtenerFichaAsignadaUsuario($idusu) {
    try {
        $sql = "SELECT idfic FROM usufic WHERE idusu = :idusu AND actfic = '1' LIMIT 1";
        
        $modelo = new Conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->bindParam(":idusu", $idusu);
        $result->execute();
        $ficha = $result->fetch(PDO::FETCH_ASSOC);
        
        return $ficha ? $ficha['idfic'] : null;
    } catch (Exception $e) {
        error_log("Error obteniendo ficha asignada: " . $e->getMessage());
        return null;
    }
}

/**
 * Valida si el usuario puede acceder a una ficha específica
 * @param int $idusu ID del usuario
 * @param int $idfic ID de la ficha
 * @return bool True si puede acceder, false si no
 */
function validarAccesoFicha($idusu, $idfic) {
    $perfil = obtenerPerfilUsuario($idusu);
    if (!$perfil) return false;
    $idper = (int)$perfil['idper'];

    if (esRolAdmin($idper)) return true;

    if (esRolAprendiz($idper)) {
        $fichaAsignada = obtenerFichaAsignadaUsuario($idusu);
        return ((string)$fichaAsignada === (string)$idfic);
    }

    // Instructor: verificar si imparte clases en esa ficha
    if (esRolInstructor($idper)) {
        $modelo = new Conexion();
        $conexion = $modelo->get_conexion();
        $stmt = $conexion->prepare("SELECT COUNT(*) FROM horario WHERE idfic = :idfic AND idusu = :idusu");
        $stmt->execute([':idfic' => $idfic, ':idusu' => $idusu]);
        if ($stmt->fetchColumn() > 0) return true;

        $stmt2 = $conexion->prepare("SELECT COUNT(*) FROM agenda WHERE idfic = :idfic AND (idins = :idusu OR idusu = :idusu2)");
        $stmt2->execute([':idfic' => $idfic, ':idusu' => $idusu, ':idusu2' => $idusu]);
        return ($stmt2->fetchColumn() > 0);
    }

    return false;
}

/**
 * Aplica restricciones de interfaz según el perfil del usuario
 * @param int $idusu ID del usuario
 * @return array Configuración de restricciones para la interfaz
 */
function obtenerRestriccionesInterfaz($idusu) {
    $perfil = obtenerPerfilUsuario($idusu);
    if (!$perfil) {
        return [
            'mostrar_botones_crear'    => false,
            'mostrar_botones_editar'   => false,
            'mostrar_botones_eliminar' => false,
            'mostrar_boton_imprimir'   => true,
            'solo_ficha_asignada'      => true,
            'solo_propios_horarios'    => false,
            'es_admin'                 => false,
            'es_instructor'            => false,
            'es_aprendiz'              => true,
            'mensaje_restriccion'      => 'Acceso restringido.'
        ];
    }
    
    $idper = (int)$perfil['idper'];
    
    // 1. Aprendiz
    if (esRolAprendiz($idper)) {
        return [
            'mostrar_botones_crear'    => false,
            'mostrar_botones_editar'   => false,
            'mostrar_botones_eliminar' => false,
            'mostrar_boton_imprimir'   => true,
            'solo_ficha_asignada'      => true,
            'solo_propios_horarios'    => false,
            'es_admin'                 => false,
            'es_instructor'            => false,
            'es_aprendiz'              => true,
            'mensaje_restriccion'      => 'Tu perfil solo permite visualizar e imprimir horarios de tu ficha asignada.'
        ];
    }

    // 2. Instructor
    if (esRolInstructor($idper)) {
        return [
            'mostrar_botones_crear'    => true,
            'mostrar_botones_editar'   => true,
            'mostrar_botones_eliminar' => true,
            'mostrar_boton_imprimir'   => true,
            'solo_ficha_asignada'      => false,
            'solo_propios_horarios'    => true,
            'es_admin'                 => false,
            'es_instructor'            => true,
            'es_aprendiz'              => false,
            'mensaje_restriccion'      => ''
        ];
    }
    
    // 3. Administrador
    return [
        'mostrar_botones_crear'    => true,
        'mostrar_botones_editar'   => true,
        'mostrar_botones_eliminar' => true,
        'mostrar_boton_imprimir'   => true,
        'solo_ficha_asignada'      => false,
        'solo_propios_horarios'    => false,
        'es_admin'                 => true,
        'es_instructor'            => false,
        'es_aprendiz'              => false,
        'mensaje_restriccion'      => ''
    ];
}

/**
 * Construye las 9 celdas HTML de una fila del DataTable server-side de instructores.
 * Replica exactamente el marcado que antes se generaba en views/horvhor.php.
 *
 * @param array $instructor Fila agregada del modelo (incluye 'detalle' de la página)
 * @param int|null $idper Perfil del usuario en sesión (el enlace de impresión se omite para idper=4)
 * @return array<int,string> Celdas en orden de columnas
 */
function construirFilaInstructorDT($instructor, $idper = null) {
    $nom = htmlspecialchars($instructor['nombre'] ?? '', ENT_QUOTES, 'UTF-8');
    $doc = $instructor['documento'] ?? '';
    $idusu = $instructor['idusu'] ?? 0;
    $idare = $instructor['idare'] ?? 0;
    $area = htmlspecialchars($instructor['area'] ?? '', ENT_QUOTES, 'UTF-8');
    $hp = (float)($instructor['horas_planta'] ?? 0);
    $ht = (float)($instructor['horas_transversales'] ?? 0);
    $ho = (float)($instructor['otras_horas'] ?? 0);
    $total = (float)($instructor['total_horas'] ?? 0);
    $maxRaw = $instructor['max_horas'] ?? 160;
    $max = (float)$maxRaw;
    $estado = $instructor['estado'] ?? 'Déficit';

    // Normalizar estado
    if ($total < 160) {
        $estado = 'Déficit';
    } elseif ($total <= 187) {
        $estado = 'Cumple';
    } else {
        $estado = 'Sobre Carga';
    }

    // Celda 0: Instructor con enlace a su reporte individual (pg=1516)
    $celda0 = '<div>';
    if (!empty($idusu) && $idusu > 0) {
        $celda0 .= '<a href="home.php?pg=1516&idusu=' . (int)$idusu . '" class="fw-bold text-success text-decoration-none" title="Ver horario individual del instructor">' . $nom . '</a>';
    } else {
        $celda0 .= '<span class="fw-bold text-dark">' . $nom . '</span>';
    }
    $celda0 .= '</div>';
    if ($doc !== '') {
        $celda0 .= '<small class="text-muted"><i class="fa-solid fa-id-card me-1"></i>' . htmlspecialchars($doc, ENT_QUOTES, 'UTF-8') . '</small>';
    }
    if (!empty($idusu) && $idusu > 0 && (int)$idper !== 4) {
        $celda0 .= '<a href="views/horrhrper.php?idusu=' . (int)$idusu . '" title="Imprimir horario del instructor" target="_blank" class="text-success ms-2"><i class="fa-solid fa-print"></i></a>';
    }

    // Celda 1: Área con enlace al reporte consolidado de área (pg=1519)
    $celda1 = '<a href="home.php?pg=1519' . (!empty($idare) ? '&idare=' . (int)$idare : '') . '" class="badge bg-light text-dark border text-decoration-none" title="Ver horario consolidado del área">' . $area . '</a>';

    // Celdas 2-4: horas
    $celda2 = number_format($hp, 1) . 'h';
    $celda3 = number_format($ht, 1) . 'h';
    $celda4 = number_format($ho, 1) . 'h';

    // Celda 5: total
    $celda5 = number_format($total, 1) . 'h';

    // Celda 6: estado
    $badgeClass = 'bg-warning text-dark';
    if ($estado === 'Cumple' || $estado === 'Cumple horas') {
        $badgeClass = 'bg-success text-white';
        $estado = 'Cumple';
    } elseif ($estado === 'Sobre Carga' || $estado === 'Sobre horas') {
        $badgeClass = 'bg-danger text-white';
        $estado = 'Sobre Carga';
    } else {
        $badgeClass = 'bg-warning text-dark';
        $estado = 'Déficit';
    }
    $celda6 = '<span class="badge ' . $badgeClass . ' px-2 py-1">' . htmlspecialchars($estado, ENT_QUOTES, 'UTF-8') . '</span>';

    // Celda 7: gráfica
    $porcentaje = min(100, round(($total / max(1, $max)) * 100));
    $colorBarra = 'bg-warning';
    if ($total >= 160 && $total <= 187) {
        $colorBarra = 'bg-success';
    } elseif ($total > 187) {
        $colorBarra = 'bg-danger';
    }
    $celda7 = '<div class="d-flex flex-column align-items-center">'
        . '<div class="progress w-100" title="' . $instructor['total_horas'] . ' / ' . $maxRaw . ' horas">'
        . '<div class="progress-bar ' . $colorBarra . '" role="progressbar" style="width: ' . $porcentaje . '%;" aria-valuenow="' . $instructor['total_horas'] . '" aria-valuemin="0" aria-valuemax="' . $maxRaw . '"></div>'
        . '</div>'
        . '<small class="text-muted mt-1 small">' . number_format($total, 0) . ' / ' . $maxRaw . 'h (' . $porcentaje . '%)</small>'
        . '</div>';

    // Celda 8: detalle
    $json = json_encode($instructor, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE);
    $celda8 = '<button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#modalDetalle" '
        . 'data-instructor=\'' . $json . '\' onclick="abrirDetalle(this)" title="Ver todas las horas del instructor">'
        . '<i class="fa-solid fa-eye me-1"></i> Ver detalle</button>';

    return [$celda0, $celda1, $celda2, $celda3, $celda4, $celda5, $celda6, $celda7, $celda8];
}

/**
 * Celda "Ficha y Programa" de la tabla principal de horarios.
 * Con enlace al reporte individual de ficha (pg=1518).
 */
function construirCeldaFichaPrincipal($dt, $perfil_usuario)
{
    $idper = (int)($perfil_usuario['idper'] ?? 0);
    $html = '<a href="home.php?pg=1518&idfic=' . urlencode($dt['idfic']) . '" data-idfic="' . htmlspecialchars($dt['idfic'], ENT_QUOTES, 'UTF-8') . '" class="fw-bold text-success text-decoration-none" title="Ver horario de la ficha ' . htmlspecialchars($dt['idfic']) . '">' . $dt['idfic'] . '</a> - ' . htmlspecialchars($dt['nomfic']) . '<br>'
        . '<small class="text-muted">Jornada: ' . htmlspecialchars($dt['nomval'] ?? '');
    if ($idper != 4 && !empty($dt['nomare'])) {
        $html .= ' | Área: ' . htmlspecialchars($dt['nomare']);
    }
    if (!empty($dt['codpro'])) {
        $html .= ' | Cod. Prog: ' . htmlspecialchars($dt['codpro']);
    }
    $html .= '</small>';
    return $html;
}

/**
 * Celda de acciones (agenda / imprimir ficha) de la tabla principal de horarios.
 * El botón de agenda redirige a la agenda específica de la ficha (pg=1509).
 */
function construirCeldaAccionesPrincipal($dt, $restricciones)
{
    $html = '';
    if (!empty($restricciones['mostrar_botones_crear']) || !empty($restricciones['mostrar_botones_editar'])) {
        $html .= '<a href="home.php?pg=1509&idfic=' . urlencode($dt['idfic']) . '" title="Agenda Pedagógica Ficha ' . $dt['idfic'] . '" class="text-primary me-2">'
            . '<i class="fa-solid fa-file-invoice fa-2x"></i></a>';
    }
    if (!empty($restricciones['mostrar_boton_imprimir'])) {
        $html .= '<a href="views/horrhrfc.php?idfic=' . urlencode($dt['idfic']) . '" title="Imprimir horario de la ficha" target="_blank" class="text-secondary">'
            . '<i class="fa-solid fa-print fa-2x"></i></a>';
    }
    return $html;
}

/**
 * Celda correspondiente a un día para una ficha. Contiene todos los bloques de horario
 * de esa ficha para ese día (normalmente uno por semana).
 */
function construirCeldaDiaHorarioPrincipal($dat, $dt, $d, $restricciones, $perfil_usuario)
{
    if (empty($dat)) {
        return '';
    }

    $idper = (int)($perfil_usuario['idper'] ?? 0);
    $jsonFlags = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT;
    // El DataTable crea el <td> de cada celda. Se devuelven solo los datos y un marcador
    // con los colores, para configurar el <td> real (position-relative + colores) y que
    // los botones Editar/Eliminar queden anclados dentro de la celda.
    $primeraFila = reset($dat);
    $colorFondoCelda = !empty($primeraFila['colfon']) ? '#' . $primeraFila['colfon'] : '#f8f9fa';
    $colorTextoCelda = !empty($primeraFila['coltex']) ? '#' . $primeraFila['coltex'] : '#212529';
    $html = "<span class='d-none' data-hor-td='1' data-hor-bg='" . $colorFondoCelda . "' data-hor-fg='" . $colorTextoCelda . "'></span>";

    foreach ($dat as $dti) {

        // Rango horario del bloque
        $hiniCell = !empty($dti['hinihor']) ? substr($dti['hinihor'], 0, 5) : '';
        $hfinCell = !empty($dti['hfinhor']) ? substr($dti['hfinhor'], 0, 5) : '';
        if ($hiniCell === '00:00' || $hfinCell === '00:00') {
            $hiniCell = '';
            $hfinCell = '';
        }
        $html .= "<div class='badge rounded-pill mb-1 bg-success text-white small'>";
        $html .= "<i class='fa-regular fa-clock me-1'></i>";
        if ($hiniCell !== '' && $hfinCell !== '') {
            $html .= $hiniCell . " - " . $hfinCell;
        } else {
            $html .= "Jornada completa";
        }
        $html .= "</div>";

        // Horas del bloque
        $horasBloqueCell = 0;
        if (!empty($dti['hinihor']) && !empty($dti['hfinhor']) && $dti['hinihor'] !== '00:00:00' && $dti['hfinhor'] !== '00:00:00') {
            $t1b = strtotime($dti['hinihor']);
            $t2b = strtotime($dti['hfinhor']);
            if ($t2b > $t1b) {
                $horasBloqueCell = round(($t2b - $t1b) / 3600, 1);
            }
        }
        if ($horasBloqueCell <= 0 && !empty($dti['es_otros']) && !empty($dti['horas_otros'])) {
            $horasBloqueCell = (float)$dti['horas_otros'];
        }
        if ($horasBloqueCell > 0) {
            $html .= "<div class='small text-success fw-semibold'>";
            $html .= "<i class='fa-solid fa-hourglass-half me-1'></i>";
            $html .= number_format($horasBloqueCell, 1) . " hrs";
            $html .= "</div>";
        }

        // Instructor (nombre truncado; completo en tooltip)
        if (!empty($dti['idusu']) && !empty($dti['nomusu'])) {
            $nomCompleto = trim($dti['nomusu']);
            $nomCorto = function_exists('mb_substr')
                ? mb_substr($nomCompleto, 0, 18, 'UTF-8')
                : substr($nomCompleto, 0, 18);
            if ($nomCorto !== $nomCompleto) {
                $nomCorto .= '…';
            }
            $html .= "<span title='" . htmlspecialchars($nomCompleto, ENT_QUOTES, 'UTF-8') . "'>"
                . htmlspecialchars($nomCorto, ENT_QUOTES, 'UTF-8') . "</span>";
            $html .= "<br><small class='text-muted'>" . htmlspecialchars($dti['ndocusu'] ?? '', ENT_QUOTES, 'UTF-8') . "</small>";
            if ($idper != 4) {
                $html .= "<a href='views/horrhrper.php?idusu=" . $dti['idusu'] . "' title='Imprimir horario del instructor' target='_blank' class='text-success ms-1'><i class='fa-solid fa-print fa-lg'></i></a>";
            }
        } else {
            if (!empty($dti['es_transversal']) && ($dti['es_transversal'] == 1)) {
                $html .= "<em class='text-muted'>Sin instructor asignado</em>";
            } else {
                $html .= "<em class='text-muted'>Sin instructor</em>";
            }
        }

        // Actividad especial (Otros)
        if (!empty($dti['es_otros']) && ($dti['es_otros'] == 1)) {
            $html .= "<br><small class='badge bg-success text-white'>";
            $html .= "<i class='fa-solid fa-calendar-plus me-1'></i>";
            $html .= htmlspecialchars($dti['actividad']);
            $html .= "</small>";
            $html .= "<br><small class='text-muted'>";
            $html .= $dti['horas_otros'] . " hrs";
            $html .= "</small>";
        }

        // Horario transversal
        if (!empty($dti['es_transversal']) && ($dti['es_transversal'] == 1)) {
            $html .= "<br><small class='badge bg-success text-white'>";
            $html .= "<i class='fa-solid fa-book-open me-1'></i>";
            $html .= "Transversal: " . htmlspecialchars($dti['nombre_transversal']);
            $html .= "</small>";
        }

        // Fecha específica
        if (!empty($dti['fecha_especifica'])) {
            $fechaEspecifica = date('d/m/Y', strtotime($dti['fecha_especifica']));
            $diaSemana = date('N', strtotime($dti['fecha_especifica']));
            $diasSemana = [
                1 => 'Lunes',
                2 => 'Martes',
                3 => 'Miércoles',
                4 => 'Jueves',
                5 => 'Viernes',
                6 => 'Sábado',
                7 => 'Domingo'
            ];
            $diaSemanaEsp = $diasSemana[$diaSemana] ?? 'Día';
            $html .= "<br><small class='text-muted'>";
            $html .= "<i class='fa-solid fa-calendar-day me-1'></i>";
            $html .= "$diaSemanaEsp, $fechaEspecifica";
            $html .= "</small>";
        }

        // Aula
        $html .= '<BR>';
        if (!empty($dti['idaul']) && !empty($dti['nomaul'])) {
            $html .= $dti['nomaul'];
            if ($idper != 4) {
                $html .= "<a href='views/horrhrar.php?idaul=" . $dti['idaul'] . "' title='Imprimir horario del aula " . $dti['idaul'] . "' target='_blank' class='text-success ms-1'><i class='fa-solid fa-print fa-lg'></i></a>";
            }
        } else {
            $html .= "<em class='text-muted'>Sin aula asignada</em>";
        }

        // Botones editar / eliminar
        if (!empty($restricciones['mostrar_botones_eliminar']) || !empty($restricciones['mostrar_botones_editar']) || $idper != 4) {
            $html .= '<div class="position-absolute bottom-0 end-0 d-flex gap-1 p-1">';

            if (!empty($restricciones['mostrar_botones_editar']) || $idper != 4) {
                $html .= '<button type="button" class="btn btn-link p-0 text-success" '
                    . 'data-idhor="' . $dti['idhor'] . '" '
                    . 'data-nombre-transversal=\'' . json_encode($dti['nombre_transversal'] ?? '', $jsonFlags) . '\' '
                    . 'data-es-transversal="' . ($dti['es_transversal'] ?? 0) . '" '
                    . 'data-es-otros="' . ($dti['es_otros'] ?? 0) . '" '
                    . 'data-actividad=\'' . json_encode($dti['actividad'] ?? '', $jsonFlags) . '\' '
                    . 'data-horas-otros="' . ($dti['horas_otros'] ?? '') . '" '
                    . 'data-idaul="' . ($dti['idaul'] ?? '') . '" '
                    . 'data-idusu="' . ($dti['idusu'] ?? '') . '" '
                    . 'data-dia=\'' . json_encode($d['nomval'], $jsonFlags) . '\' '
                    . 'data-ficha=\'' . json_encode($dt['idfic'] . ' - ' . $dt['nomfic'], $jsonFlags) . '\' '
                    . 'data-fecha-inicio="' . ($dti['fecha_inicio'] ?? '') . '" '
                    . 'data-fecha-fin="' . ($dti['fecha_fin'] ?? '') . '" '
                    . 'data-hinihor="' . (!empty($dti['hinihor']) && $dti['hinihor'] !== '00:00:00' ? substr($dti['hinihor'], 0, 5) : '') . '" '
                    . 'data-hfinhor="' . (!empty($dti['hfinhor']) && $dti['hfinhor'] !== '00:00:00' ? substr($dti['hfinhor'], 0, 5) : '') . '" '
                    . 'data-es-formacion-directa="' . ($dti['es_formacion_directa'] ?? 1) . '" '
                    . 'onclick="onClickEditarHorario(this)" title="Editar Horario">'
                    . '<i class="fa-solid fa-pen-to-square fa-lg p-1 text-success"></i>'
                    . '</button>';
            }

            if (!empty($restricciones['mostrar_botones_eliminar']) || $idper != 4) {
                $html .= '<button type="button" class="btn btn-link p-0 text-danger" '
                    . 'data-idhor="' . $dti['idhor'] . '" '
                    . 'data-iddia="' . $dti['iddia'] . '" '
                    . 'data-idfic="' . $dt['idfic'] . '" '
                    . 'data-idusu="' . $dti['idusu'] . '" '
                    . 'data-idaul="' . $dti['idaul'] . '" '
                    . 'data-nomfic=\'' . json_encode($dt['nomfic'], $jsonFlags) . '\' '
                    . 'data-nomusu=\'' . json_encode($dti['nomusu'] ?? "Sin instructor", $jsonFlags) . '\' '
                    . 'data-nomaul=\'' . json_encode($dti['nomaul'] ?? "Sin aula", $jsonFlags) . '\' '
                    . 'data-fecha-inicio="' . ($dti['fecha_inicio'] ?? '') . '" '
                    . 'data-fecha-fin="' . ($dti['fecha_fin'] ?? '') . '" '
                    . 'data-jornada=\'' . json_encode($dt['nomval'], $jsonFlags) . '\' '
                    . 'onclick="onClickEliminar(this)" title="Eliminar Horario">'
                    . '<i class="fa-solid fa-trash-can fa-lg p-1 text-danger"></i>'
                    . '</button>';
            }

            $html .= '</div>';
        }
    }

    return $html;
}

/**
 * Construye las celdas de una fila de la tabla principal de horarios (Ficha y Programa + acciones + días).
 *
 * @param array $dt Ficha
 * @param array $ddi Días (de getDia)
 * @param array $horariosPorDia Mapa iddia => [horarios] para esa ficha
 */
function construirFilaHorarioPrincipal($dt, $ddi, $horariosPorDia, $restricciones, $perfil_usuario)
{
    $celdas = [];
    $celdas[] = construirCeldaFichaPrincipal($dt, $perfil_usuario);
    $celdas[] = construirCeldaAccionesPrincipal($dt, $restricciones);

    foreach ($ddi as $d) {
        $dat = $horariosPorDia[$d['idval']] ?? [];
        $celdas[] = construirCeldaDiaHorarioPrincipal($dat, $dt, $d, $restricciones, $perfil_usuario);
    }

    return $celdas;
}

/**
 * Devuelve la hora de inicio más temprana (HH:MM) de una lista de horarios,
 * o cadena vacía si no hay horas válidas. Se usa para ordenar por día.
 */
function minHoraDia($filas)
{
    $min = '';
    if (!is_array($filas)) {
        return $min;
    }
    foreach ($filas as $r) {
        $h = $r['hinihor'] ?? '';
        if (empty($h) || $h === '00:00:00') {
            continue;
        }
        $h = substr($h, 0, 5);
        if ($min === '' || strcmp($h, $min) < 0) {
            $min = $h;
        }
    }
    return $min;
}

// ==========================================
// SISTEMA DE PERMISOS POR PERFILES - FIN
// ==========================================

/**
 * Calcula las horas reales de un instructor dentro de un rango de fechas,
 * con horas calculadas a partir de hinihor/hfinhor (ej: 06:00 - 12:00 = 6.0 horas).
 * Devuelve un resumen y un detalle POR REGISTRO (con idhor real para editar/eliminar).
 *
 * @param string $idInstructor ID de usuario del instructor
 * @param string $fechaInicio  Fecha inicio (YYYY-MM-DD)
 * @param string $fechaFin     Fecha fin (YYYY-MM-DD)
 * @return array ['resumen' => [...], 'detalle' => [...]]
 */
function calcularHorasRango($idInstructor, $fechaInicio, $fechaFin) {
    try {
        $modelo = new Conexion();
        $conexion = $modelo->get_conexion();

        $sql = 'SELECT h.idhor, h.idfic, h.idaul, h.idusu, h.iddia,
                       h.fecha_inicio, h.fecha_fin, h.fecha_especifica,
                       h.hinihor, h.hfinhor,
                       h.es_otros, h.actividad, h.horas_otros,
                       h.es_transversal, h.nombre_transversal, h.es_formacion_directa,
                       f.nomfic, f.jornada,
                       vj.nomval AS nombre_jornada, vj.nhora AS horas_jornada,
                       vd.nomval AS nombre_dia,
                       a.nomaul, u.ndocusu, u.nomusu
                FROM horario AS h
                LEFT JOIN ficha AS f ON h.idfic = f.idfic
                LEFT JOIN valor AS vj ON f.jornada = vj.idval
                LEFT JOIN valor AS vd ON h.iddia = vd.idval
                LEFT JOIN aula AS a ON h.idaul = a.idaul
                LEFT JOIN usuario AS u ON h.idusu = u.idusu
                WHERE h.idusu = :idInstructor
                AND h.fecha_especifica IS NOT NULL
                AND h.fecha_especifica BETWEEN :fechaInicio AND :fechaFin
                ORDER BY h.fecha_especifica ASC, h.iddia ASC, h.idhor ASC';

        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':idInstructor', $idInstructor);
        $stmt->bindParam(':fechaInicio', $fechaInicio);
        $stmt->bindParam(':fechaFin', $fechaFin);
        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Cálculo real del número de días del período
        $diasPeriodo = 0;
        $tsIni = strtotime($fechaInicio);
        $tsFin = strtotime($fechaFin);
        if ($tsIni && $tsFin && $tsFin >= $tsIni) {
            $diasPeriodo = round(($tsFin - $tsIni) / 86400) + 1;
        }

        $nombresDias = [1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado', 7 => 'Domingo'];

        $detalle = [];
        $totalHoras = 0.0;
        $horasFormacion = 0.0;
        $horasOtros = 0.0;

        foreach ($rows as $h) {
            $duracion = 0.0;

            // 1) Calcular horas reales desde hinihor/hfinhor cuando existen
            if (!empty($h['hinihor']) && !empty($h['hfinhor'])
                && $h['hinihor'] !== '00:00:00' && $h['hfinhor'] !== '00:00:00') {
                $t1 = strtotime($h['hinihor']);
                $t2 = strtotime($h['hfinhor']);
                if ($t2 && $t1 && $t2 > $t1) {
                    $duracion = round(($t2 - $t1) / 3600, 2);
                }
            }

            // 2) Si no hay horas, usar horas_otros (actividad especial) o jornada de la ficha
            if ($duracion <= 0) {
                if (!empty($h['es_otros']) && !empty($h['horas_otros']) && (float)$h['horas_otros'] > 0) {
                    $duracion = (float)$h['horas_otros'];
                } elseif (!empty($h['horas_jornada']) && (float)$h['horas_jornada'] > 0) {
                    $duracion = (float)$h['horas_jornada'];
                } else {
                    $duracion = 6.0;
                }
                $duracion = round($duracion, 2);
            }

            $horaInicio = (!empty($h['hinihor']) && $h['hinihor'] !== '00:00:00') ? substr($h['hinihor'], 0, 5) : '';
            $horaFin = (!empty($h['hfinhor']) && $h['hfinhor'] !== '00:00:00') ? substr($h['hfinhor'], 0, 5) : '';

            // Texto de jornada: usar rango real de horas si está disponible
            $jornadaTexto = $h['nombre_jornada'] ?: '';
            if ($horaInicio && $horaFin) {
                $jornadaTexto = $horaInicio . ' - ' . $horaFin;
            }

            // Día: preferir el nombre real del día calculado desde la fecha
            $fechaEspecifica = $h['fecha_especifica'];
            $diaNombre = $h['nombre_dia'] ?: 'Día';
            if ($fechaEspecifica) {
                $numDia = (int)date('N', strtotime($fechaEspecifica));
                $diaNombre = isset($nombresDias[$numDia]) ? $nombresDias[$numDia] : $diaNombre;
            }

            $esOtros = (int)($h['es_otros'] ?? 0);
            $esTransversal = (int)($h['es_transversal'] ?? 0);
            $esFormacionDirecta = (int)($h['es_formacion_directa'] ?? 1);

            if ($esOtros && $esFormacionDirecta === 2) {
                $tipoHoras = 'formacion_directa';
                $tipoTexto = 'Otros/Formación directa';
                $horasFormacion += $duracion;
            } elseif ($esOtros) {
                $tipoHoras = 'otros';
                $tipoTexto = 'Actividad especial';
                $horasOtros += $duracion;
            } elseif ($esTransversal) {
                $tipoHoras = 'transversal';
                $tipoTexto = 'Transversal';
                $horasFormacion += $duracion;
            } else {
                $tipoHoras = 'formacion';
                $tipoTexto = 'Formación';
                $horasFormacion += $duracion;
            }

            $totalHoras += $duracion;

            $detalle[] = [
                'idhor' => $h['idhor'],
                'idfic' => $h['idfic'],
                'ficha' => $h['nomfic'] ?: ($h['actividad'] ?: ($h['nombre_transversal'] ?: '—')),
                'nomfic' => $h['nomfic'] ?: '',
                'iddia' => $h['iddia'],
                'dia' => $diaNombre,
                'fecha' => $fechaEspecifica,
                'fecha_especifica' => $fechaEspecifica,
                'fecha_inicio' => $h['fecha_inicio'] ?: '',
                'fecha_fin' => $h['fecha_fin'] ?: '',
                'hinihor' => $horaInicio,
                'hfinhor' => $horaFin,
                'jornada' => $jornadaTexto,
                'nomjornada' => $h['nombre_jornada'] ?: '',
                'idusu' => $h['idusu'],
                'nomusu' => $h['nomusu'] ?: 'Sin instructor',
                'ndocusu' => $h['ndocusu'] ?: '',
                'idaul' => $h['idaul'],
                'aula' => $h['nomaul'] ?: 'Sin aula',
                'horas' => $duracion,
                'es_otros' => $esOtros,
                'actividad' => $h['actividad'] ?: '',
                'horas_otros' => $h['horas_otros'] ?: '',
                'es_formacion_directa' => $esFormacionDirecta,
                'es_transversal' => $esTransversal,
                'nombre_transversal' => $h['nombre_transversal'] ?: '',
                'tipo_horas' => $tipoHoras,
                'tipo_texto' => $tipoTexto,
                'dias_count' => 1
            ];
        }

        return [
            'resumen' => [
                'total_horas' => round($totalHoras, 2),
                'horas_formacion' => round($horasFormacion, 2),
                'horas_otros' => round($horasOtros, 2),
                'dias_periodo' => $diasPeriodo,
                'num_horarios' => count($detalle)
            ],
            'detalle' => $detalle
        ];
    } catch (Exception $e) {
        error_log("Error en calcularHorasRango: " . $e->getMessage());
        return [
            'resumen' => ['total_horas' => 0, 'horas_formacion' => 0, 'horas_otros' => 0, 'dias_periodo' => 0, 'num_horarios' => 0],
            'detalle' => []
        ];
    }
}

/**
 * Genera una URL de redirección dinámica basada en el entorno actual
 * @param string $pagina Página a la que redirigir (ej: 'home.php?pg=1506')
 * @return string URL completa para redirección
 */
function generarUrlRedireccion($pagina) {
    $protocolo = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    
    // Obtener la ruta base del proyecto (sin el directorio controllers)
    $script_name = $_SERVER['SCRIPT_NAME']; // /sagi/controllers/chor.php
    $ruta_base = dirname(dirname($script_name)); // /sagi (sube dos niveles desde controllers/chor.php)
    
    // Limpiar barras dobles y asegurar formato correcto
    $url = $protocolo . '://' . $host . $ruta_base . '/' . ltrim($pagina, '/');
    $url = str_replace('//', '/', $url);
    $url = str_replace(':/', '://', $url);
    
    return $url;
}

/**
 * Obtiene el área del usuario actual desde la sesión
 * @return int ID del área
 */
function obtenerAreaUsuarioActual() {
    // Si hay un área guardada en la sesión, la usamos
    if (isset($_SESSION['idare']) && !empty($_SESSION['idare'])) {
        return $_SESSION['idare'];
    }
    
    // Si no, obtenemos la primera área disponible como por defecto
    try {
        $sql = 'SELECT idare FROM area ORDER BY idare LIMIT 1';
        $modelo = new Conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->execute();
        $area = $result->fetch(PDO::FETCH_ASSOC);
        
        return $area ? $area['idare'] : 1; // Si no hay áreas, usar 1 por defecto
    } catch (Exception $e) {
        error_log("Error obteniendo área del usuario: " . $e->getMessage());
        return 1; // Área por defecto en caso de error
    }
}

/**
 * Crea o obtiene una ficha temporal para un instructor específico
 * Verifica conflictos de horarios para determinar si reutilizar o crear nueva ficha
 * VERSIÓN ROBUSTA: Garantiza que la ficha siempre se cree correctamente
 * MANTIENE: La funcionalidad de limpieza automática de fichas temporales vacías
 * @param string $idInstructor ID del instructor
 * @param string $dia Día de la semana (iddia)
 * @param string $fechaEspecifica Fecha específica del horario (Y-m-d)
 * @return string ID de la ficha temporal
 * @throws Exception Si no se puede crear la ficha
 */
function obtenerMesesEntreFechas($fechaInicio, $fechaFin) {
    $meses = [];

    try {
        if (empty($fechaInicio) || empty($fechaFin)) {
            return $meses;
        }

        $inicio = new DateTime($fechaInicio);
        $fin = new DateTime($fechaFin);

        $inicio->modify('first day of this month');
        $fin->modify('first day of this month');

        while ($inicio <= $fin) {
            $meses[] = [
                'mes' => (int)$inicio->format('n'),
                'año' => (int)$inicio->format('Y')
            ];

            $inicio->modify('+1 month');
        }
    } catch (Exception $e) {
        error_log("Error obteniendo meses entre fechas: " . $e->getMessage());
    }

    return $meses;
}

function crearObtenerFichaTemporal($idInstructor, $dia = null, $fechaEspecifica = null) {
        $modelo = new Conexion();
        $conexion = $modelo->get_conexion();
    
    try {
        // Iniciar transacción para garantizar consistencia
        $conexion->beginTransaction();
                
        // 1. Obtener datos completos del instructor
        $sqlInstructor = 'SELECT idusu, nomusu, ndocusu, fecini, fecfin FROM usuario WHERE idusu = :id_instructor';
        $resultInstructor = $conexion->prepare($sqlInstructor);
        $resultInstructor->bindParam(':id_instructor', $idInstructor);
        $resultInstructor->execute();
        $instructor = $resultInstructor->fetch(PDO::FETCH_ASSOC);
        
        if (!$instructor) {
            throw new Exception("Instructor con ID $idInstructor no encontrado");
        }
        
        $cedulaInstructor = $instructor['ndocusu'];
        $nombreInstructor = $instructor['nomusu'];
        $nomficTemporal = "Actividades Especiales - {$nombreInstructor} ({$cedulaInstructor})";
        
        
        // 2. Buscar todas las fichas existentes para este instructor
        $sqlFichasExistentes = 'SELECT idfic, nomfic FROM ficha WHERE idfic = :cedula OR idfic LIKE :patron_cedula ORDER BY idfic';
        $resultFichas = $conexion->prepare($sqlFichasExistentes);
        $patronCedula = $cedulaInstructor . '-%';
        $resultFichas->bindParam(':cedula', $cedulaInstructor);
        $resultFichas->bindParam(':patron_cedula', $patronCedula);
        $resultFichas->execute();
        $fichasExistentes = $resultFichas->fetchAll(PDO::FETCH_ASSOC);
        
        
        // 3. Si hay día y fecha específica, verificar conflictos
        if ($dia && $fechaEspecifica && !empty($fichasExistentes)) {
            
                foreach ($fichasExistentes as $ficha) {
                $idficExistente = $ficha['idfic'];
                
                // Verificar conflictos de horario más específicos
                $sqlConflicto = 'SELECT COUNT(*) as total FROM horario 
                                 WHERE idfic = :idfic 
                                 AND iddia = :dia 
                                 AND (
                                     fecha_especifica = :fecha_especifica 
                                     OR (fecha_inicio <= :fecha_especifica AND fecha_fin >= :fecha_especifica)
                                 )
                                 AND es_otros = 1'; // Solo verificar conflictos con "Otros"
                
                $resultConflicto = $conexion->prepare($sqlConflicto);
                $resultConflicto->bindParam(':idfic', $idficExistente);
                $resultConflicto->bindParam(':dia', $dia);
                $resultConflicto->bindParam(':fecha_especifica', $fechaEspecifica);
                $resultConflicto->execute();
                $conflictos = $resultConflicto->fetch(PDO::FETCH_ASSOC)['total'];
                
                if ($conflictos == 0) {
                    
                    // Verificar que la ficha realmente existe y tiene el nomfic correcto
                    if (empty($ficha['nomfic']) || $ficha['nomfic'] !== $nomficTemporal) {
                        $sqlUpdateNomfic = 'UPDATE ficha SET nomfic = :nomfic WHERE idfic = :idfic';
                        $resultUpdate = $conexion->prepare($sqlUpdateNomfic);
                        $resultUpdate->bindParam(':nomfic', $nomficTemporal);
                        $resultUpdate->bindParam(':idfic', $idficExistente);
                        $resultUpdate->execute();
                    }
                    
                    $conexion->commit();
                    return $idficExistente;
                } else {
                    $conexion->rollback();
                    return false;
                }
            }
            
        } else if (!empty($fichasExistentes)) {
            // Sin información de día/fecha, priorizar la ficha base
            foreach ($fichasExistentes as $ficha) {
                if ($ficha['idfic'] === $cedulaInstructor) {
                    
                    // Verificar y corregir nomfic si es necesario
                    if (empty($ficha['nomfic']) || $ficha['nomfic'] !== $nomficTemporal) {
                        $sqlUpdateNomfic = 'UPDATE ficha SET nomfic = :nomfic WHERE idfic = :idfic';
                        $resultUpdate = $conexion->prepare($sqlUpdateNomfic);
                        $resultUpdate->bindParam(':nomfic', $nomficTemporal);
                        $resultUpdate->bindParam(':idfic', $cedulaInstructor);
                        $resultUpdate->execute();
                    }
                    
                    $conexion->commit();
                    return $cedulaInstructor;
                }
            }
            
            // Si no hay ficha base, usar la primera disponible
            $fichaReutilizar = $fichasExistentes[0]['idfic'];
            
            // Verificar y corregir nomfic si es necesario
            if (empty($fichasExistentes[0]['nomfic']) || $fichasExistentes[0]['nomfic'] !== $nomficTemporal) {
                $sqlUpdateNomfic = 'UPDATE ficha SET nomfic = :nomfic WHERE idfic = :idfic';
                $resultUpdate = $conexion->prepare($sqlUpdateNomfic);
                $resultUpdate->bindParam(':nomfic', $nomficTemporal);
                $resultUpdate->bindParam(':idfic', $fichaReutilizar);
                $resultUpdate->execute();
            }
            
            $conexion->commit();
            return $fichaReutilizar;
        }
        
        // 4. Crear nueva ficha temporal
        
        // Encontrar el siguiente ID disponible
        $idficTemporal = $cedulaInstructor; // Comenzar con la cédula base
        $contador = 1;
        
        // Verificar disponibilidad del ID
        while (true) {
            $sqlVerificar = 'SELECT idfic FROM ficha WHERE idfic = :idfic';
            $resultVerificar = $conexion->prepare($sqlVerificar);
            $resultVerificar->bindParam(':idfic', $idficTemporal);
            $resultVerificar->execute();
            $fichaExistente = $resultVerificar->fetch(PDO::FETCH_ASSOC);
            
            if (!$fichaExistente) {
                break; // ID disponible
            }
            
                $contador++;
                $idficTemporal = $cedulaInstructor . '-' . $contador;
            
            // Prevenir bucle infinito
            if ($contador > 100) {
                throw new Exception("No se pudo encontrar un ID disponible para la ficha temporal (intentos: $contador)");
            }
        }
        
        // 5. Insertar la nueva ficha con validación
        $sqlInsert = 'INSERT INTO ficha(idfic, nomfic, codpro, jornada, idcen, mun, finific, ffinfic) 
                      VALUES (:idfic, :nomfic, :codpro, :jornada, :idcen, :mun, :finific, :ffinfic)';
        
        $resultInsert = $conexion->prepare($sqlInsert);
        $resultInsert->bindParam(':idfic', $idficTemporal);
        $resultInsert->bindParam(':nomfic', $nomficTemporal);
        
        // Valores por defecto
        $codpro = 228118;
        $jornada = 1; // Cambiar de 1055 (Madrugada) a 1 (Mañana)
        $idcen = 951310;
        $mun = '25175';
        $finific = date('Y-m-d');
        $ffinfic = date('Y-m-d', strtotime('+2 years'));
        
        $resultInsert->bindParam(':codpro', $codpro);
        $resultInsert->bindParam(':jornada', $jornada);
        $resultInsert->bindParam(':idcen', $idcen);
        $resultInsert->bindParam(':mun', $mun);
        $resultInsert->bindParam(':finific', $finific);
        $resultInsert->bindParam(':ffinfic', $ffinfic);
        
        $insertResult = $resultInsert->execute();
        
        if (!$insertResult) {
            $errorInfo = $resultInsert->errorInfo();
            throw new Exception("Error en INSERT: " . implode(' - ', $errorInfo));
        }
        
        // 6. Verificar que la ficha se creó correctamente
        $sqlVerificarCreacion = 'SELECT idfic, nomfic FROM ficha WHERE idfic = :idfic';
        $resultVerificarCreacion = $conexion->prepare($sqlVerificarCreacion);
        $resultVerificarCreacion->bindParam(':idfic', $idficTemporal);
        $resultVerificarCreacion->execute();
        $fichaCreada = $resultVerificarCreacion->fetch(PDO::FETCH_ASSOC);
        
        if (!$fichaCreada) {
            throw new Exception("CRÍTICO: La ficha $idficTemporal no se pudo verificar después de la inserción");
        }
        
        if ($fichaCreada['nomfic'] !== $nomficTemporal) {
            error_log("DEBUG: [ROBUSTO] ⚠️ ADVERTENCIA: nomfic no coincide. Esperado: '$nomficTemporal', Obtenido: '{$fichaCreada['nomfic']}'");
        }
        
        // Confirmar transacción
        $conexion->commit();
                
        return $idficTemporal;
        
    } catch (Exception $e) {
        // Rollback en caso de error
        if ($conexion->inTransaction()) {
            $conexion->rollBack();
        }
        
        $errorMsg = "ERROR CRÍTICO en crearObtenerFichaTemporal para instructor $idInstructor: " . $e->getMessage();
        // Registro de error
        error_log($errorMsg);
        
        // Re-lanzar la excepción para que el proceso padre la maneje
        throw new Exception($errorMsg);
    }
}

/**
 * Verifica si una ficha es temporal
 * @param string $idfic ID de la ficha
 * @return bool True si es temporal, false si no
 */
function esFichaTemporal($idfic) {
    // Verificar si es una ficha temporal (empieza con cédula o tiene formato cédula-número)
    // Las fichas temporales son las que tienen formato de cédula o cédula-número
    return preg_match('/^\d{6,12}(-\d+)?$/', $idfic) && strlen($idfic) >= 6;
}

/**
 * Elimina fichas temporales que no tienen horarios asociados
 */
function limpiarFichasTemporalesVacias() {
    try {
        
        // Primero, obtener todas las fichas temporales (solo las de actividades especiales)
        $sqlBuscar = 'SELECT f.idfic, f.nomfic FROM ficha f 
                      WHERE (f.idfic REGEXP "^[0-9]{6,12}(-[0-9]+)?$" OR f.idfic LIKE "999%")
                      AND f.nomfic LIKE "Actividades Especiales%"';
        
        $modelo = new Conexion();
        $conexion = $modelo->get_conexion();
        $resultBuscar = $conexion->prepare($sqlBuscar);
        $resultBuscar->execute();
        $fichasTemporales = $resultBuscar->fetchAll(PDO::FETCH_ASSOC);
        
        $fichasEliminadas = 0;
        
        foreach ($fichasTemporales as $ficha) {
            $idfic = $ficha['idfic'];
            
            // Verificar si esta ficha tiene horarios asociados
            $sqlVerificar = 'SELECT COUNT(*) as total FROM horario WHERE idfic = :idfic';
            $resultVerificar = $conexion->prepare($sqlVerificar);
            $resultVerificar->bindParam(':idfic', $idfic);
            $resultVerificar->execute();
            $horarios = $resultVerificar->fetch(PDO::FETCH_ASSOC);
            
            if ($horarios['total'] == 0) {
                // No tiene horarios, eliminar la ficha
                $sqlEliminar = 'DELETE FROM ficha WHERE idfic = :idfic';
                $resultEliminar = $conexion->prepare($sqlEliminar);
                $resultEliminar->bindParam(':idfic', $idfic);
                $resultEliminar->execute();
                
                $fichasEliminadas++;
            } else {
                $conexion->rollback();
                return false;
            }
        }
        
        
        return $fichasEliminadas;
        
    } catch (Exception $e) {
        return 0;
    }
}

// Manejo temprano del conteo de horarios para garantizar respuesta JSON
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && $ope === 'contar_horarios_grupo') {
    $idfic_post = $_POST['idfic'] ?? null;
    $iddia_post = $_POST['iddia'] ?? null;
    $idusu_post = $_POST['idusu'] ?? '';
    $idaul_post = $_POST['idaul'] ?? '';
    $fecha_inicio_post = $_POST['fecha_inicio'] ?? null;
    $fecha_fin_post = $_POST['fecha_fin'] ?? null;
    
    header('Content-Type: application/json; charset=UTF-8');
    
    // Validación de parámetros; solo idfic, iddia, fecha_inicio y fecha_fin son obligatorios
    // idusu e idaul pueden estar vacíos (para horarios sin instructor o sin aula)
    if (!$idfic_post || !$iddia_post || !$fecha_inicio_post || !$fecha_fin_post) {
        echo json_encode([
            'success' => true,
            'cantidad' => 0,
            'message' => 'Parámetros obligatorios incompletos: idfic, iddia, fecha_inicio, fecha_fin'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    $hormhorTmp = new hOrmhor();
    $cantidadTmp = $hormhorTmp->contarHorariosGrupo($idfic_post, $iddia_post, $idusu_post, $idaul_post, $fecha_inicio_post, $fecha_fin_post);
    echo json_encode(['success' => true, 'cantidad' => $cantidadTmp], JSON_UNESCAPED_UNICODE);
    exit;
}

// Manejo temprano de la eliminación individual de horarios para garantizar respuesta JSON
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET' && $ope === 'del') {
    header('Content-Type: application/json; charset=UTF-8');

    $idhor_del = $_REQUEST['idhor'] ?? null;
    $iddia_del = $_REQUEST['iddia'] ?? null;

    if (!$idhor_del || !$iddia_del) {
        echo json_encode(['success' => false, 'message' => 'ID de horario y día requeridos.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    try {
        $hormhorDel = new hOrmhor();
        $hormhorDel->setIdhor($idhor_del);
        $hormhorDel->setIddia($iddia_del);
        $hormhorDel->del();

        echo json_encode([
            'success' => true,
            'message' => 'Horario eliminado correctamente.',
            'cantidad' => 1
        ], JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error al eliminar el horario: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
    }
    exit;
}

// Manejo temprano de la eliminación de un grupo de horarios para garantizar respuesta JSON
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET' && $ope === 'del_grupo') {
    header('Content-Type: application/json; charset=UTF-8');

    $idfic_grupo = $_REQUEST['idfic'] ?? null;
    $iddia_grupo = $_REQUEST['iddia'] ?? null;
    $idusu_grupo = ($_REQUEST['idusu'] ?? '') !== '' ? $_REQUEST['idusu'] : null;
    $idaul_grupo = ($_REQUEST['idaul'] ?? '') !== '' ? $_REQUEST['idaul'] : null;
    $fecha_inicio_grupo = $_REQUEST['fecha_inicio'] ?? null;
    $fecha_fin_grupo = $_REQUEST['fecha_fin'] ?? null;

    if (!$idfic_grupo || !$iddia_grupo || !$fecha_inicio_grupo || !$fecha_fin_grupo) {
        echo json_encode(['success' => false, 'message' => 'Parámetros obligatorios incompletos: idfic, iddia, fecha_inicio, fecha_fin.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    try {
        $hormhorGrupo = new hOrmhor();
        $cantidadGrupo = $hormhorGrupo->contarHorariosGrupo($idfic_grupo, $iddia_grupo, $idusu_grupo ?? '', $idaul_grupo ?? '', $fecha_inicio_grupo, $fecha_fin_grupo);
        $hormhorGrupo->delGrupo($idfic_grupo, $iddia_grupo, $idusu_grupo, $idaul_grupo, $fecha_inicio_grupo, $fecha_fin_grupo);

        echo json_encode([
            'success' => true,
            'message' => 'Grupo de horarios eliminado correctamente.',
            'cantidad' => $cantidadGrupo
        ], JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error al eliminar el grupo de horarios: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
    }
    exit;
}

// Manejo temprano para calcular horas de un instructor en un rango de fechas (detalle por registro con idhor)
if ((($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' || ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET') && ($ope === 'get_horas_rango' || $ope === 'get_horas_trimestre' || $ope === 'get_horarios_por_mes')) {
    header('Content-Type: application/json; charset=UTF-8');

    $idUsuRango = $_REQUEST['id_instructor'] ?? $_REQUEST['idusu'] ?? null;
    $fechaIniRango = $_REQUEST['fecha_inicio'] ?? null;
    $fechaFinRango = $_REQUEST['fecha_fin'] ?? null;

    if (!$idUsuRango || !$fechaIniRango || !$fechaFinRango) {
        echo json_encode([
            'success' => false,
            'message' => 'Parámetros requeridos: id_instructor (o idusu), fecha_inicio, fecha_fin.',
            'resumen' => ['total_horas' => 0, 'horas_formacion' => 0, 'horas_otros' => 0, 'dias_periodo' => 0, 'num_horarios' => 0],
            'detalle' => []
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    try {
        $resultadoRango = calcularHorasRango($idUsuRango, $fechaIniRango, $fechaFinRango);
        echo json_encode(array_merge(['success' => true], $resultadoRango), JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error al calcular horas del rango: ' . $e->getMessage(),
            'resumen' => ['total_horas' => 0, 'horas_formacion' => 0, 'horas_otros' => 0, 'dias_periodo' => 0, 'num_horarios' => 0],
            'detalle' => []
        ], JSON_UNESCAPED_UNICODE);
    }
    exit;
}

// Manejo temprano para obtener eventos por tipo del calendario académico
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && $ope === 'get_eventos_por_tipo') {
    $tipoEvento = $_POST['tipo_evento'] ?? null;
    
    header('Content-Type: application/json; charset=UTF-8');
    
    if (!$tipoEvento) {
        echo json_encode([
            'success' => false,
            'message' => 'Tipo de evento no especificado'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    $hormhorTmp = new hOrmhor();
    $eventos = $hormhorTmp->getEventosPorTipo($tipoEvento);
    
    echo json_encode([
        'success' => true,
        'eventos' => $eventos
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Manejo temprano para obtener tipos de eventos
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && $ope === 'get_tipos_eventos') {
    header('Content-Type: application/json; charset=UTF-8');
    
    try {
        $hormhorTmp = new hOrmhor();
        $tipos = $hormhorTmp->getTiposEventos();
        
        echo json_encode([
            'success' => true,
            'tipos' => $tipos
        ], JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error al obtener tipos de eventos: ' . $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
    }
    exit;
}

// Endpoint AJAX: Obtener carga horaria consolidada de instructores
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && ($ope === 'get_carga_instructores' || $ope === 'get_horas_instructores')) {
    header('Content-Type: application/json; charset=UTF-8');
    
    $idusu_actual = $_SESSION['idusu'] ?? null;
    $perfil_actual = obtenerPerfilUsuario($idusu_actual);
    $idper_actual = $perfil_actual['idper'] ?? ($_SESSION['idper'] ?? null);

    $idare_req = $_POST['idare'] ?? $_REQUEST['idare'] ?? null;
    $idfic_req = $_POST['idfic'] ?? $_REQUEST['idfic'] ?? null;
    $idusu_inst_req = $_POST['idusu'] ?? $_REQUEST['idusu'] ?? null;
    $fecha_inicio_req = $_POST['fecha_inicio'] ?? $_REQUEST['fecha_inicio'] ?? null;
    $fecha_fin_req = $_POST['fecha_fin'] ?? $_REQUEST['fecha_fin'] ?? null;
    $mes_req = $_POST['mes'] ?? $_REQUEST['mes'] ?? null;
    $anio_req = $_POST['anio'] ?? $_REQUEST['anio'] ?? null;

    try {
        $hormhorTmp = new hOrmhor();
        $carga = $hormhorTmp->getCargaHorariaInstructores([
            'idusu_coord' => $idusu_actual,
            'idper' => $idper_actual,
            'idare' => $idare_req,
            'idfic' => $idfic_req,
            'idusu_inst' => $idusu_inst_req,
            'fecha_inicio' => $fecha_inicio_req,
            'fecha_fin' => $fecha_fin_req,
            'mes' => $mes_req,
            'anio' => $anio_req
        ]);

        echo json_encode([
            'success' => true,
            'data' => $carga,
            'total' => count($carga)
        ], JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error al obtener carga horaria: ' . $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
    }
    exit;
}

// Endpoint AJAX: Obtener detalle completo de horarios de un instructor
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && $ope === 'get_detalle_instructor') {
    header('Content-Type: application/json; charset=UTF-8');
    
    $idInstructor = $_POST['id_instructor'] ?? $_REQUEST['id_instructor'] ?? null;
    $idusu_actual = $_SESSION['idusu'] ?? null;
    $perfil_actual = obtenerPerfilUsuario($idusu_actual);
    $idper_actual = $perfil_actual['idper'] ?? ($_SESSION['idper'] ?? null);

    if (!$idInstructor) {
        echo json_encode(['success' => false, 'message' => 'ID de instructor no especificado'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    try {
        $hormhorTmp = new hOrmhor();
        $detalle = $hormhorTmp->getDetalleHorasInstructor($idInstructor, [
            'idusu_coord' => $idusu_actual,
            'idper' => $idper_actual,
            'idare' => $_POST['idare'] ?? null,
            'idfic' => $_POST['idfic'] ?? null,
            'fecha_inicio' => $_POST['fecha_inicio'] ?? null,
            'fecha_fin' => $_POST['fecha_fin'] ?? null,
            'mes' => $_POST['mes'] ?? null,
            'anio' => $_POST['anio'] ?? null
        ]);

        echo json_encode([
            'success' => true,
            'detalle' => $detalle,
            'total' => count($detalle)
        ], JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error al obtener detalle del instructor: ' . $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
    }
    exit;
}

// Endpoint AJAX: Reporte mensual de horas por instructor
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && $ope === 'get_reporte_mensual') {
    header('Content-Type: application/json; charset=UTF-8');
    
    $idusu_actual = $_SESSION['idusu'] ?? null;
    $perfil_actual = obtenerPerfilUsuario($idusu_actual);
    $idper_actual = $perfil_actual['idper'] ?? ($_SESSION['idper'] ?? null);

    $mes = $_POST['mes'] ?? date('n');
    $anio = $_POST['anio'] ?? date('Y');
    $idare = $_POST['idare'] ?? null;
    $idfic = $_POST['idfic'] ?? null;
    $idusu_inst = $_POST['idusu'] ?? null;

    try {
        $hormhorTmp = new hOrmhor();
        $reporte = $hormhorTmp->getReporteMensualHoras($anio, $mes, [
            'idusu_coord' => $idusu_actual,
            'idper' => $idper_actual,
            'idare' => $idare,
            'idfic' => $idfic,
            'idusu_inst' => $idusu_inst
        ]);

        echo json_encode([
            'success' => true,
            'data' => $reporte,
            'mes' => (int)$mes,
            'anio' => (int)$anio,
            'total' => count($reporte)
        ], JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error al generar reporte mensual: ' . $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
    }
    exit;
}

// Endpoint AJAX: Reporte anual de horas por instructor (matriz 12 meses)
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && $ope === 'get_reporte_anual') {
    header('Content-Type: application/json; charset=UTF-8');
    
    $idusu_actual = $_SESSION['idusu'] ?? null;
    $perfil_actual = obtenerPerfilUsuario($idusu_actual);
    $idper_actual = $perfil_actual['idper'] ?? ($_SESSION['idper'] ?? null);

    $anio = $_POST['anio'] ?? date('Y');
    $idare = $_POST['idare'] ?? null;
    $idusu_inst = $_POST['idusu'] ?? null;

    try {
        $hormhorTmp = new hOrmhor();
        $reporte = $hormhorTmp->getReporteAnualHoras($anio, [
            'idusu_coord' => $idusu_actual,
            'idper' => $idper_actual,
            'idare' => $idare,
            'idusu_inst' => $idusu_inst
        ]);

        echo json_encode([
            'success' => true,
            'data' => $reporte,
            'anio' => (int)$anio,
            'total' => count($reporte)
        ], JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error al generar reporte anual: ' . $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
    }
    exit;
}

// Endpoint AJAX: Obtener fichas filtradas por área
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && $ope === 'get_fichas_por_area') {
    header('Content-Type: application/json; charset=UTF-8');
    
    $idusu_actual = $_SESSION['idusu'] ?? null;
    $perfil_actual = obtenerPerfilUsuario($idusu_actual);
    $idper_actual = $perfil_actual['idper'] ?? ($_SESSION['idper'] ?? null);
    $idare = $_POST['idare'] ?? null;

    try {
        $hormhorTmp = new hOrmhor();
        $fichas = $hormhorTmp->getFichasCoordinador($idusu_actual, $idare, $idper_actual);

        echo json_encode([
            'success' => true,
            'fichas' => $fichas,
            'data' => $fichas
        ], JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error al obtener fichas: ' . $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
    }
    exit;
}

// Endpoint AJAX: Obtener información de Ficha, Programa y Proyecto Formativo
if ($ope === 'get_info_ficha' || ($_POST['ope'] ?? '') === 'get_info_ficha' || ($_GET['ope'] ?? '') === 'get_info_ficha') {
    header('Content-Type: application/json; charset=UTF-8');
    $idfic_req = $_REQUEST['idfic'] ?? null;
    try {
        $hormhorTmp = new hOrmhor();
        $info = $hormhorTmp->getInfoFicha($idfic_req);
        if ($info) {
            echo json_encode([
                'success' => true,
                'data' => $info
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'No se encontró información para la ficha solicitada.'
            ], JSON_UNESCAPED_UNICODE);
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error al consultar ficha: ' . $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
    }
    exit;
}

$hormhor = new hOrmhor();
$hormhor->setIdhor($idhor);
$hormhor->setIdare($idare);

if ($ope == 'save') {
    // ==========================================
    // VALIDACIÓN DE PERMISOS - CREAR HORARIOS
    // ==========================================
    $idusu_actual = $_SESSION["idusu"] ?? null;
    if (!$idusu_actual) {
        echo json_encode([
            'success' => false,
            'message' => 'Usuario no autenticado.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Validar permisos para crear horarios
    if (!validarPermisosUsuario($idusu_actual, 'crear')) {
        echo json_encode([
            'success' => false,
            'message' => 'No tienes permisos para crear horarios.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Para perfiles restringidos, validar acceso a la ficha
    if ($idfic && !validarAccesoFicha($idusu_actual, $idfic)) {
        echo json_encode([
            'success' => false,
            'message' => 'No tienes acceso a esta ficha.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    if (empty($iddia)) {
        return;
    }

    $errores   = [];
    $guardados = 0;
    $totalHorariosCreados = 0;

    // Procesar cada día seleccionado
    foreach ((array)$iddia as $dia) {
        $hormhor = new hOrmhor();
        $hormhor->setIdfic($idfic);
        $hormhor->setIddia($dia);
        
        // Calcular fecha específica para este día para validaciones
        $fecha = null;
        $modo_individual = isset($_POST['modo_individual']) && $_POST['modo_individual'] == 'on';
        
        if ($modo_individual) {
            // Modo individual: usar la fecha específica directamente
            $fecha_especifica = $_POST['fecha_especifica'] ?? null;
            if ($fecha_especifica && $dia) {
                // Verificar que la fecha específica corresponda al día de la semana seleccionado
                $fechaObj = new DateTime($fecha_especifica);
                $diaSemana = $fechaObj->format('N'); // 1=Lunes, 2=Martes, etc.
                
                $diasSemana = [
                    1042 => 1,  // Lunes
                    1043 => 2,  // Martes
                    1044 => 3,  // Miércoles
                    1045 => 4,  // Jueves
                    1046 => 5,  // Viernes
                    1047 => 6,  // Sábado
                    1048 => 7,  // Domingo
                ];
                
                $diaEsperado = $diasSemana[$dia] ?? 1;
                
                if ($diaSemana == $diaEsperado) {
                    $fecha = $fecha_especifica;
                } else {
                    $errores[] = "La fecha específica seleccionada no corresponde al día de la semana seleccionado.";
                    continue;
                }
            }
        } else {
            // Modo rango: usar la lógica original
            if ($fecha_inicio && $fecha_fin && $dia) {
                $diasEspecificos = generarDiasDelHorario($fecha_inicio, $fecha_fin, $dia);
                $fecha = !empty($diasEspecificos) ? $diasEspecificos[0] : null;
            }
        }

        // Configurar campos según el tipo de horario
        if ($es_otros) {
            // Configuración para actividades "Otros"
            $hormhor->setEsOtros(true);
            $hormhor->setActividad(trim($actividad));
            $hormhor->setHorasOtros($horas_otros);
            // Para actividades "otros": si se marcó el checkbox, usar 2; si no, usar 1
            $valorFormacionDirecta = ($es_formacion_directa == '2') ? 2 : 1;
            $hormhor->setEsFormacionDirecta($valorFormacionDirecta);
            error_log("DEBUG: es_formacion_directa recibido: '$es_formacion_directa', valor asignado: $valorFormacionDirecta");
            
            // Para actividades "otros", el aula es opcional
            $hormhor->setIdaul(!empty($idaul) ? $idaul : null);
            
            // El instructor sigue siendo requerido
            $hormhor->setIdusu($idusu);
            
            // SISTEMA DE FICHAS TEMPORALES: Si no hay ficha seleccionada, crear/obtener ficha temporal
            error_log("DEBUG: Verificando ficha para actividad 'otros'. idfic recibido: '$idfic', idusu: '$idusu'");
            
            if (empty($idfic)) {
                // Solo crear ficha temporal si hay un instructor válido
                if (!empty($idusu)) {
                    error_log("DEBUG: idfic está vacío, creando ficha temporal para instructor $idusu");
                    try {
                        // Calcular fecha específica para verificar conflictos
                        $fechaEspecifica = null;
                        if ($fecha_inicio && $fecha_fin && $dia) {
                            // Obtener la primera fecha específica del rango para este día
                            $diasEspecificos = generarDiasDelHorario($fecha_inicio, $fecha_fin, $dia);
                            $fechaEspecifica = !empty($diasEspecificos) ? $diasEspecificos[0] : null;
                        }
                        
                        $idficTemporal = crearObtenerFichaTemporal($idusu, $dia, $fechaEspecifica);
                        $hormhor->setIdfic($idficTemporal);
                        error_log("DEBUG: Ficha temporal asignada al modelo: $idficTemporal para instructor $idusu");
                        
                        // Verificar que se asignó correctamente
                        $idficVerificado = $hormhor->getIdfic();
                        error_log("DEBUG: Verificación - ID de ficha en el modelo: '$idficVerificado'");
                        
                    } catch (Exception $e) {
                        error_log("ERROR: Error creando ficha temporal: " . $e->getMessage());
                        $errores[] = "Error creando ficha temporal: " . $e->getMessage();
                        continue; // Saltar al siguiente día
                    }
                } else {
                    // Si no hay instructor, no se puede crear ficha temporal
                    $errores[] = "Para actividades especiales se requiere seleccionar un instructor.";
                    continue;
                }
            } else {
                error_log("DEBUG: Usando ficha existente: $idfic");
                $hormhor->setIdfic($idfic);
            }
            
            // Campos de transversales en false
            $hormhor->setEsTransversal(false);
            $hormhor->setNombreTransversal(null);
        } elseif ($es_transversal) {
            // Configuración para horarios "Transversales"
            $hormhor->setEsOtros(false);
            $hormhor->setActividad(null);
            $hormhor->setHorasOtros(null);
            
            // Para transversales, el aula es obligatoria
            $hormhor->setIdaul($idaul);
            
            // El instructor es opcional para transversales
            $hormhor->setIdusu(!empty($idusu) ? $idusu : null);
            
            // Campos de transversales
            $hormhor->setEsTransversal(true);
            $hormhor->setNombreTransversal(trim($nombre_transversal));
            $hormhor->setEsFormacionDirecta(1); // Los transversales siempre son formación (valor por defecto)
        } else {
            // Configuración para horarios normales
            $hormhor->setEsOtros(false);
            $hormhor->setActividad(null);
            $hormhor->setHorasOtros(null);
            $hormhor->setEsFormacionDirecta(1); // Los horarios normales siempre son formación (valor por defecto)
            $hormhor->setIdaul($idaul); // Aula requerida para horarios normales
            $hormhor->setIdusu($idusu);
            
            // Campos de transversales en false
            $hormhor->setEsTransversal(false);
            $hormhor->setNombreTransversal(null);
        }

        // Configurar fechas para todos los tipos de horario
        if ($fecha_inicio && $fecha_fin) {
            $hormhor->setFechaInicio($fecha_inicio);
            $hormhor->setFechaFin($fecha_fin);
        }

        // Obtener nombre del día para mostrar en errores
        $nombreDia = $hormhor->obtenerNombreDia($dia);

        // Verificar si ya existe horario para esta ficha en este día (mismo mes)
        $idhorExistente = $hormhor->verificarHorarioExistente($idfic, $dia, $fecha);
        if ($idhorExistente) {
            $errores[] = "Ya existe un horario para esta ficha el día $nombreDia en este mes.";
            continue; // Saltar al siguiente día
        }

        // Verificar conflicto de instructor solo si se seleccionó uno
        if (!empty($idusu)) {
            $hayConflicto = $hormhor->verificarConflictoInstructor($idusu, $idfic, $dia, $fecha);
            error_log("DEBUG: Verificando conflicto instructor - idusu: $idusu, idfic: $idfic, dia: $dia, fecha: $fecha, hayConflicto: " . ($hayConflicto ? 'true' : 'false'));
            
            if ($hayConflicto) {
                $errores[] = "Este instructor ya está asignado en este mismo horario con otra ficha el día $nombreDia.";
                continue;
            }
        }

        // Verificar conflicto de aula solo si se seleccionó una
        if (!empty($idaul) && $hormhor->verificarConflictoAula($idaul, $idfic, $dia, $fecha)) {
            $tipoActividad = $es_otros ? "esta actividad especial" : ($es_transversal ? "otra transversal" : "otra ficha");
            $errores[] = "Esta aula ya está ocupada en este mismo horario con $tipoActividad el día $nombreDia.";
            continue;
        }

        // Si no hay errores, generar registros individuales por día
        try {
            // Obtener el ID de ficha correcto (puede ser temporal)
            $idficFinal = $hormhor->getIdfic();
            
            // Generar registros individuales para cada día del horario
            if ($modo_individual) {
                // Modo individual: crear solo un registro para la fecha específica
                try {
                    $diasGenerados = generarRegistroIndividual($idficFinal, $idaul, $idusu, $dia, $es_transversal, $nombre_transversal, $es_otros, $actividad, $horas_otros, $es_formacion_directa, $fecha, $hinihor, $hfinhor);
                    
                    if (!empty($diasGenerados)) {
                        $guardados++;
                        $totalHorariosCreados += count($diasGenerados);
                        
                        // Calcular y guardar horas mensuales solo si hay instructor
                        if ($idusu) {
                            // Modo individual: recalcular solo el mes de la fecha específica
                            $mes = date('n', strtotime($fecha));
                            $año = date('Y', strtotime($fecha));
                            $horasMes = calcularHorasMesParaInstructor($idusu, $mes, $año, $idficFinal, $dia);
                            if ($horasMes > 0) {
                                guardarHorasMensuales($idusu, $mes, $año, $horasMes);
                            }
                        }
                    } else {
                        error_log("No se generó registro individual para el día $dia");
                    }
                    
                } catch (Exception $e) {
                    error_log("Error generando registro individual: " . $e->getMessage());
                    $errores[] = "Error al generar horario individual: " . $e->getMessage();
                }
            } else if ($fecha_inicio && $fecha_fin) {
                try {
                    // Generar registros individuales para cada día del horario
                    $diasGenerados = generarRegistrosIndividuales($idficFinal, $idaul, $idusu, $dia, $es_transversal, $nombre_transversal, $es_otros, $actividad, $horas_otros, $es_formacion_directa, $fecha_inicio, $fecha_fin, $hinihor, $hfinhor);
                    
                    if (!empty($diasGenerados)) {
                        $guardados++;
                        $totalHorariosCreados += count($diasGenerados);
                        
                        // Calcular y guardar horas mensuales solo si hay instructor
                        if ($idusu) {
                            // Modo rango: recalcular todos los meses afectados
                            $mesesAfectados = obtenerMesesEntreFechas($fecha_inicio, $fecha_fin);
                            foreach ($mesesAfectados as $mesInfo) {
                                $horasMes = calcularHorasMesParaInstructor($idusu, $mesInfo['mes'], $mesInfo['año'], $idficFinal, $dia);
                                if ($horasMes > 0) {
                                    guardarHorasMensuales($idusu, $mesInfo['mes'], $mesInfo['año'], $horasMes);
                                }
                            }
                        }
                    } else {
                        error_log("No se generaron registros individuales para el día $dia");
                    }
                    
                } catch (Exception $e) {
                    error_log("Error generando registros individuales: " . $e->getMessage());
                    $errores[] = "Error al generar horarios para el día $nombreDia: " . $e->getMessage();
                }
            } else {
                $errores[] = "Se requieren fechas de inicio y fin para generar el horario.";
            }
            
        } catch (Exception $e) {
            $errores[] = "Error al generar horarios para el día $nombreDia: " . $e->getMessage();
            // Agregar información de debugging
            error_log("Error en generación de horarios: " . $e->getMessage());
            error_log("Datos: idfic=$idfic, idaul=$idaul, idusu=$idusu, iddia=$dia, es_otros=$es_otros, es_transversal=$es_transversal");
        }
    }

    // Preparar mensajes para mostrar en JavaScript
    $mensajesResultado = [];

    if (!empty($errores)) {
        $mensajesResultado['errores'] = $errores;
    }

    if ($guardados > 0) {
        $mensaje = $es_otros ? 
            "Se guardaron $totalHorariosCreados actividad(es) especial(es) correctamente." :
            ($es_transversal ? 
                "Se guardaron $totalHorariosCreados horario(s) transversal(es) correctamente." :
                "Se guardaron $totalHorariosCreados horario(s) correctamente.");
        $mensajesResultado['exito'] = $mensaje;
        
        // Limpiar fichas temporales vacías SOLO después de guardar actividades especiales
        if ($es_otros) {
            limpiarFichasTemporalesVacias();
        }
    }
} elseif ($ope == 'get_carga_instructores') {
    // Endpoint para obtener la carga horaria calculada de instructores
    try {
        $idusu_session = $_SESSION['idusu'] ?? null;
        $perfil_actual = obtenerPerfilUsuario($idusu_session);
        $idper_actual = $perfil_actual['idper'] ?? ($_SESSION['idper'] ?? null);

        $idare = $_REQUEST['idare'] ?? null;
        $idfic = $_REQUEST['idfic'] ?? null;
        $idusu_inst = $_REQUEST['idusu'] ?? ($_REQUEST['id_instructor'] ?? null);
        $fecha_inicio = $_REQUEST['fecha_inicio'] ?? null;
        $fecha_fin = $_REQUEST['fecha_fin'] ?? null;
        $mes = $_REQUEST['mes'] ?? null;
        $anio = $_REQUEST['anio'] ?? ($_REQUEST['año'] ?? null);

        $hormhorObj = new Hormhor();
        $filtros = [
            'idusu_coord' => $idusu_session,
            'idper' => $idper_actual,
            'idare' => $idare,
            'idfic' => $idfic,
            'idusu_inst' => $idusu_inst,
            'fecha_inicio' => $fecha_inicio,
            'fecha_fin' => $fecha_fin,
            'mes' => $mes,
            'anio' => $anio
        ];

        $carga = $hormhorObj->getCargaHorariaInstructores($filtros);

        echo json_encode([
            'success' => true,
            'data' => $carga,
            'total' => count($carga)
        ], JSON_UNESCAPED_UNICODE);
        exit;
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error al obtener carga: ' . $e->getMessage()]);
        exit;
    }

} elseif ($ope == 'get_instructores_dt') {
    // Endpoint server-side processing para el DataTable de instructores (Carga Horaria).
    // Aplica los mismos filtros/permisos que get_carga_instructores y devuelve solo la página solicitada.
    header('Content-Type: application/json; charset=UTF-8');
    try {
        $idusu_session = $_SESSION['idusu'] ?? null;
        $perfil_actual = obtenerPerfilUsuario($idusu_session);
        $idper_actual = $perfil_actual['idper'] ?? ($_SESSION['idper'] ?? null);

        // Parámetros de DataTables
        $draw = (int)($_REQUEST['draw'] ?? 1);
        $start = (int)($_REQUEST['start'] ?? 0);
        $length = (int)($_REQUEST['length'] ?? 20);
        $busqueda = $_REQUEST['search']['value'] ?? '';
        $ordenColumna = (int)($_REQUEST['order'][0]['column'] ?? 5);
        $ordenDir = $_REQUEST['order'][0]['dir'] ?? 'asc';

        // Filtros del servidor (los mismos que usa la carga inicial)
        $idare = $_REQUEST['idare'] ?? null;
        $idfic = $_REQUEST['idfic'] ?? null;
        $idusu_inst = $_REQUEST['idusu_inst'] ?? ($_REQUEST['idusu'] ?? null);
        $fecha_inicio = $_REQUEST['fecha_inicio'] ?? null;
        $fecha_fin = $_REQUEST['fecha_fin'] ?? null;
        $mes = $_REQUEST['mes'] ?? null;
        $anio = $_REQUEST['anio'] ?? ($_REQUEST['año'] ?? null);

        $filtros = [
            'idusu_coord' => $idusu_session,
            'idper' => $idper_actual,
            'idare' => $idare,
            'idfic' => $idfic,
            'idusu_inst' => $idusu_inst,
            'fecha_inicio' => $fecha_inicio,
            'fecha_fin' => $fecha_fin,
            'mes' => $mes,
            'anio' => $anio
        ];

        $opciones = [
            'busqueda' => $busqueda,
            'orden_columna' => $ordenColumna,
            'orden_dir' => $ordenDir,
            'start' => $start,
            'length' => $length
        ];

        $hormhorObj = new Hormhor();
        $res = $hormhorObj->getCargaHorariaInstructoresPaginado($filtros, $opciones);

        $data = [];
        foreach ($res['data'] as $instructor) {
            $data[] = construirFilaInstructorDT($instructor, $idper_actual);
        }

        echo json_encode([
            'draw' => $draw,
            'recordsTotal' => (int)$res['recordsTotal'],
            'recordsFiltered' => (int)$res['recordsFiltered'],
            'data' => $data
        ], JSON_UNESCAPED_UNICODE);
        exit;
    } catch (Exception $e) {
        echo json_encode([
            'draw' => 0,
            'recordsTotal' => 0,
            'recordsFiltered' => 0,
            'data' => [],
            'message' => 'Error al obtener instructores: ' . $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

} elseif ($ope == 'get_horarios_fichas_dt') {
    // Endpoint server-side processing para la tabla principal de horarios (Ficha y Programa).
    // Solo consulta/renderiza la página solicitada (evita las ~7000 consultas de la carga completa).
    header('Content-Type: application/json; charset=UTF-8');
    try {
        $idusu_session = $_SESSION['idusu'] ?? null;
        $perfil_actual = obtenerPerfilUsuario($idusu_session);
        $idper_actual = (int)($perfil_actual['idper'] ?? ($_SESSION['idper'] ?? 0));
        $restricciones = obtenerRestriccionesInterfaz($idusu_session);

        // Parámetros de DataTables
        $draw = (int)($_REQUEST['draw'] ?? 1);
        $start = (int)($_REQUEST['start'] ?? 0);
        $length = (int)($_REQUEST['length'] ?? 20);
        if ($length <= 0) {
            $length = 20;
        }
        $busqueda = trim((string)($_REQUEST['search']['value'] ?? ''));
        $ordenColumna = (int)($_REQUEST['order'][0]['column'] ?? 0);
        $ordenDir = (($_REQUEST['order'][0]['dir'] ?? 'desc') === 'asc') ? 'asc' : 'desc';

        // Filtros del servidor
        $idare = $_REQUEST['idare'] ?? null;
        $idfic = $_REQUEST['idfic'] ?? null;
        $idusu_filtro = $_REQUEST['idusu'] ?? null;
        $semanaSelector = $_REQUEST['semana_selector'] ?? null;

        $hormhorObj = new Hormhor();
        $ddi = $hormhorObj->getDia() ?: [];

        // 1) Fichas base (permisos + área), igual que la vista
        $dft = $hormhorObj->getFichasCoordinador($idusu_session, $idare, $idper_actual);
        if (!is_array($dft)) {
            $dft = [];
        }

        // Restricción perfil 4: solo la ficha asignada
        if ($idper_actual == 4) {
            $ficha_asignada = obtenerFichaAsignadaUsuario($idusu_session);
            if (!$ficha_asignada) {
                $dft = [];
            } else {
                $dft = array_values(array_filter($dft, function ($f) use ($ficha_asignada) {
                    return $f['idfic'] == $ficha_asignada;
                }));
            }
        }

        // 2) Horarios de la semana en UNA sola consulta
        $horariosSemana = $hormhorObj->getHorariosSemanaTodas($semanaSelector);
        $map = [];
        foreach ($horariosSemana as $h) {
            $map[$h['idfic']][$h['iddia']][] = $h;
        }

        // 3) Filtro ficha / instructor (selects de la vista)
        if (!empty($idfic) && $idfic !== 'todos') {
            $dft = array_values(array_filter($dft, function ($f) use ($idfic) {
                return $f['idfic'] == $idfic;
            }));
        }
        if (!empty($idusu_filtro) && $idusu_filtro !== 'todos') {
            $dft = array_values(array_filter($dft, function ($f) use ($map, $idusu_filtro) {
                if (empty($map[$f['idfic']])) {
                    return false;
                }
                foreach ($map[$f['idfic']] as $filas) {
                    foreach ($filas as $r) {
                        if (($r['idusu'] ?? null) == $idusu_filtro) {
                            return true;
                        }
                    }
                }
                return false;
            }));
        }

        $recordsTotal = count($dft);

        // 4) Búsqueda global (ficha + instructor + aula + actividad + transversal)
        if ($busqueda !== '') {
            $needle = mb_strtolower($busqueda, 'UTF-8');
            $dft = array_values(array_filter($dft, function ($f) use ($map, $needle) {
                $partes = [
                    $f['idfic'] ?? '',
                    $f['nomfic'] ?? '',
                    $f['nomare'] ?? '',
                    $f['nomval'] ?? '',
                    $f['codpro'] ?? ''
                ];
                if (!empty($map[$f['idfic']])) {
                    foreach ($map[$f['idfic']] as $filas) {
                        foreach ($filas as $r) {
                            $partes[] = $r['nomusu'] ?? '';
                            $partes[] = $r['ndocusu'] ?? '';
                            $partes[] = $r['nomaul'] ?? '';
                            $partes[] = $r['actividad'] ?? '';
                            $partes[] = $r['nombre_transversal'] ?? '';
                        }
                    }
                }
                $haystack = mb_strtolower(implode(' ', $partes), 'UTF-8');
                return mb_strpos($haystack, $needle) !== false;
            }));
        }

        $recordsFiltered = count($dft);

        // 5) Ordenamiento (columna 0 = ficha; columnas >= 2 = días por hora de inicio)
        $dayIndex = null;
        if ($ordenColumna >= 2 && isset($ddi[$ordenColumna - 2])) {
            $dayIndex = $ddi[$ordenColumna - 2]['idval'];
        }
        usort($dft, function ($a, $b) use ($ordenColumna, $ordenDir, $map, $dayIndex) {
            if ($ordenColumna == 0) {
                $cmp = strnatcasecmp((string)$a['idfic'], (string)$b['idfic']);
            } elseif ($dayIndex !== null) {
                $ta = minHoraDia($map[$a['idfic']][$dayIndex] ?? []);
                $tb = minHoraDia($map[$b['idfic']][$dayIndex] ?? []);
                if ($ta === '' && $tb === '') {
                    $cmp = strnatcasecmp((string)$a['idfic'], (string)$b['idfic']);
                } elseif ($ta === '') {
                    $cmp = 1;
                } elseif ($tb === '') {
                    $cmp = -1;
                } else {
                    $cmp = strcmp($ta, $tb);
                }
            } else {
                $cmp = strnatcasecmp((string)$a['idfic'], (string)$b['idfic']);
            }
            return $ordenDir === 'asc' ? $cmp : -$cmp;
        });

        // 6) Paginación (solo la página solicitada)
        // Las fichas CON horarios en la semana van primero para que las
        // cards muestren información desde el primer vistazo.
        $conDatos = [];
        $sinDatos = [];
        foreach ($dft as $f) {
            if (!empty($map[$f['idfic']])) {
                $conDatos[] = $f;
            } else {
                $sinDatos[] = $f;
            }
        }
        $dft = array_merge($conDatos, $sinDatos);
        $page = array_slice($dft, $start, $length);

        // 7) Render de filas
        $data = [];
        foreach ($page as $f) {
            $horariosPorDia = $map[$f['idfic']] ?? [];
            $data[] = construirFilaHorarioPrincipal($f, $ddi, $horariosPorDia, $restricciones, $perfil_actual);
        }

        echo json_encode([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data
        ], JSON_UNESCAPED_UNICODE);
        exit;
    } catch (Exception $e) {
        echo json_encode([
            'draw' => 0,
            'recordsTotal' => 0,
            'recordsFiltered' => 0,
            'data' => [],
            'message' => 'Error al obtener horarios: ' . $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

} elseif ($ope == 'get_detalle_instructor') {
    // Endpoint para obtener el detalle de horarios asignados a un instructor
    try {
        $idusu_inst = $_REQUEST['idusu'] ?? ($_REQUEST['id_instructor'] ?? null);
        if (!$idusu_inst) {
            echo json_encode(['success' => false, 'message' => 'ID de instructor no especificado']);
            exit;
        }

        $idusu_session = $_SESSION['idusu'] ?? null;
        $perfil_actual = obtenerPerfilUsuario($idusu_session);
        $idper_actual = $perfil_actual['idper'] ?? ($_SESSION['idper'] ?? null);

        $idare = $_REQUEST['idare'] ?? null;
        $idfic = $_REQUEST['idfic'] ?? null;
        $fecha_inicio = $_REQUEST['fecha_inicio'] ?? null;
        $fecha_fin = $_REQUEST['fecha_fin'] ?? null;
        $mes = $_REQUEST['mes'] ?? null;
        $anio = $_REQUEST['anio'] ?? ($_REQUEST['año'] ?? null);

        $hormhorObj = new Hormhor();
        $filtros = [
            'idusu_coord' => $idusu_session,
            'idper' => $idper_actual,
            'idare' => $idare,
            'idfic' => $idfic,
            'fecha_inicio' => $fecha_inicio,
            'fecha_fin' => $fecha_fin,
            'mes' => $mes,
            'anio' => $anio
        ];

        $detalle = $hormhorObj->getDetalleHorasInstructor($idusu_inst, $filtros);

        echo json_encode([
            'success' => true,
            'data' => $detalle,
            'total' => count($detalle)
        ], JSON_UNESCAPED_UNICODE);
        exit;
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error al obtener detalle: ' . $e->getMessage()]);
        exit;
    }

} elseif ($ope == 'get_reporte_mensual') {
    // Endpoint para obtener reporte mensual
    try {
        $idusu_session = $_SESSION['idusu'] ?? null;
        $perfil_actual = obtenerPerfilUsuario($idusu_session);
        $idper_actual = $perfil_actual['idper'] ?? ($_SESSION['idper'] ?? null);

        $anio = $_REQUEST['anio'] ?? ($_REQUEST['año'] ?? date('Y'));
        $mes = $_REQUEST['mes'] ?? date('n');
        $idare = $_REQUEST['idare'] ?? null;
        $idfic = $_REQUEST['idfic'] ?? null;
        $idusu_inst = $_REQUEST['idusu'] ?? null;

        $hormhorObj = new Hormhor();
        $filtros = [
            'idusu_coord' => $idusu_session,
            'idper' => $idper_actual,
            'idare' => $idare,
            'idfic' => $idfic,
            'idusu_inst' => $idusu_inst
        ];

        $reporte = $hormhorObj->getReporteMensualHoras($anio, $mes, $filtros);

        echo json_encode([
            'success' => true,
            'data' => $reporte,
            'anio' => (int)$anio,
            'mes' => (int)$mes,
            'total' => count($reporte)
        ], JSON_UNESCAPED_UNICODE);
        exit;
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error al generar reporte mensual: ' . $e->getMessage()]);
        exit;
    }

} elseif ($ope == 'get_reporte_anual') {
    // Endpoint para obtener matriz del reporte anual (12 meses)
    try {
        $idusu_session = $_SESSION['idusu'] ?? null;
        $perfil_actual = obtenerPerfilUsuario($idusu_session);
        $idper_actual = $perfil_actual['idper'] ?? ($_SESSION['idper'] ?? null);

        $anio = $_REQUEST['anio'] ?? ($_REQUEST['año'] ?? date('Y'));
        $idare = $_REQUEST['idare'] ?? null;
        $idusu_inst = $_REQUEST['idusu'] ?? null;

        $hormhorObj = new Hormhor();
        $filtros = [
            'idusu_coord' => $idusu_session,
            'idper' => $idper_actual,
            'idare' => $idare,
            'idusu_inst' => $idusu_inst
        ];

        $reporte = $hormhorObj->getReporteAnualHoras($anio, $filtros);

        echo json_encode([
            'success' => true,
            'data' => $reporte,
            'anio' => (int)$anio,
            'total' => count($reporte)
        ], JSON_UNESCAPED_UNICODE);
        exit;
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error al generar reporte anual: ' . $e->getMessage()]);
        exit;
    }

} elseif ($ope == 'get_fichas_por_area') {
    // Endpoint para cascada de selección de fichas según área
    try {
        $idusu_session = $_SESSION['idusu'] ?? null;
        $perfil_actual = obtenerPerfilUsuario($idusu_session);
        $idper_actual = $perfil_actual['idper'] ?? ($_SESSION['idper'] ?? null);

        $idare = $_REQUEST['idare'] ?? null;

        $hormhorObj = new Hormhor();
        $fichas = $hormhorObj->getFichasCoordinador($idusu_session, $idare, $idper_actual);

        echo json_encode([
            'success' => true,
            'data' => $fichas,
            'total' => count($fichas)
        ], JSON_UNESCAPED_UNICODE);
        exit;
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error al obtener fichas: ' . $e->getMessage()]);
        exit;
    }

} elseif ($ope == 'get_instructores_por_area') {
    // Endpoint para cascada de instructores según área y ficha
    try {
        $idusu_session = $_SESSION['idusu'] ?? null;
        $perfil_actual = obtenerPerfilUsuario($idusu_session);
        $idper_actual = $perfil_actual['idper'] ?? ($_SESSION['idper'] ?? null);

        $idare = $_REQUEST['idare'] ?? null;
        $idfic = $_REQUEST['idfic'] ?? null;

        $hormhorObj = new Hormhor();
        $instructores = $hormhorObj->getInstructoresCoordinador($idusu_session, $idare, $idfic, $idper_actual);

        echo json_encode([
            'success' => true,
            'data' => $instructores,
            'total' => count($instructores)
        ], JSON_UNESCAPED_UNICODE);
        exit;
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error al obtener instructores: ' . $e->getMessage()]);
        exit;
    }

} elseif ($ope == 'get_horas_mensuales') {
    // Endpoint para obtener horas mensuales de un instructor
    $id_instructor = $_REQUEST['id_instructor'] ?? null;
    $año = $_REQUEST['año'] ?? date('Y'); // Ya en zona horaria de Colombia
    
    if (!$id_instructor) {
        echo json_encode(['success' => false, 'message' => 'ID de instructor requerido']);
        exit;
    }
    
    try {
        // Obtener horas mensuales desde la tabla horas_mensuales
        $sql = 'SELECT mes, año, horas_totales 
                FROM horas_mensuales 
                WHERE id_instructor = :id_instructor AND año = :año 
                ORDER BY mes ASC';
        
        $modelo = new Conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->bindParam(':id_instructor', $id_instructor);
        $result->bindParam(':año', $año);
        $result->execute();
        
        $horasMensuales = $result->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true, 
            'horas_mensuales' => $horasMensuales,
            'total_registros' => count($horasMensuales)
        ]);
        exit;
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        exit;
    }
    
} elseif ($ope == 'eliminar_dia') {
    // Endpoint para eliminar un día específico del horario
    $id_horario = $_REQUEST['id_horario'] ?? null;
    $fecha_especifica = $_REQUEST['fecha_especifica'] ?? null;
    
    if (!$id_horario || !$fecha_especifica) {
        echo json_encode(['success' => false, 'message' => 'ID de horario y fecha requeridos']);
        exit;
    }
    
    try {
        $success = eliminarDiaHorario($id_horario, $fecha_especifica);
        
        if ($success) {
            echo json_encode([
                'success' => true, 
                'message' => "Día $fecha_especifica eliminado del horario"
            ]);
        } else {
            echo json_encode([
                'success' => false, 
                'message' => "Error al eliminar el día del horario"
            ]);
        }
        exit;
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        exit;
    }
    
} elseif ($ope == 'recalcular_horas') {
    // Endpoint para recalcular horas mensuales de un instructor
    $id_instructor = $_REQUEST['id_instructor'] ?? null;
    $año = $_REQUEST['año'] ?? date('Y'); // Ya en zona horaria de Colombia
    
    if (!$id_instructor) {
        echo json_encode(['success' => false, 'message' => 'ID de instructor requerido']);
        exit;
    }
    
    try {
        $success = recalcularHorasMensualesInstructor($id_instructor, $año);
        
        if ($success) {
            echo json_encode([
                'success' => true, 
                'message' => "Horas mensuales recalculadas para instructor $id_instructor, año $año"
            ]);
        } else {
            echo json_encode([
                'success' => false, 
                'message' => "Error al recalcular horas para instructor $id_instructor"
            ]);
        }
        exit;
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        exit;
    }
    
} elseif ($ope == 'verificar_sincronizacion') {
    // Endpoint para verificar la sincronización entre "ver detalle" y reconteo anual
    $id_instructor = $_REQUEST['id_instructor'] ?? null;
    $mes = $_REQUEST['mes'] ?? date('n'); // Mes actual
    $año = $_REQUEST['año'] ?? date('Y'); // Año actual
    
    if (!$id_instructor) {
        echo json_encode(['success' => false, 'message' => 'ID de instructor requerido']);
        exit;
    }
    
    try {
        $resultado = verificarSincronizacionConteo($id_instructor, $mes, $año);
        
        if ($resultado) {
            echo json_encode([
                'success' => true, 
                'resultado' => $resultado,
                'message' => $resultado['sincronizado'] ? 
                    'Lógica sincronizada correctamente' : 
                    'Diferencia detectada en el conteo'
            ]);
        } else {
            echo json_encode([
                'success' => false, 
                'message' => 'Error al verificar sincronización'
            ]);
        }
        exit;
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        exit;
    }
    
} elseif ($ope == 'get_horarios_por_mes') {
    // Endpoint para obtener horarios del instructor en un MES usando la MISMA lógica que "Por Rango"
    $id_instructor = $_REQUEST['id_instructor'] ?? null;
    $mes = $_REQUEST['mes'] ?? date('n');
    $año = $_REQUEST['año'] ?? date('Y');
    
    if (!$id_instructor) {
        echo json_encode(['success' => false, 'message' => 'ID de instructor requerido']);
        exit;
    }
    
    try {
        $fecha_inicio = sprintf('%04d-%02d-01', (int)$año, (int)$mes);
        $fecha_fin = date('Y-m-t', strtotime($fecha_inicio));
        $hormhorTmp = new hOrmhor();
        $resultado = calcularHorasRango($hormhorTmp, $id_instructor, $fecha_inicio, $fecha_fin);
        
        echo json_encode([
            'success' => true, 
            'resumen' => $resultado['resumen'] ?? [],
            'detalle' => $resultado['detalle'] ?? [],
            'total_registros' => isset($resultado['detalle']) ? count($resultado['detalle']) : 0,
            'mes' => (int)$mes,
            'año' => (int)$año,
            'fecha_inicio' => $fecha_inicio,
            'fecha_fin' => $fecha_fin
        ], JSON_UNESCAPED_UNICODE);
        exit;
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        exit;
    }
} elseif ($ope == 'get_total_horarios_instructor') {
    // Endpoint para obtener el total de horarios de un instructor
    $id_instructor = $_REQUEST['id_instructor'] ?? null;
    
    if (!$id_instructor) {
        echo json_encode(['success' => false, 'message' => 'ID de instructor requerido']);
        exit;
    }
    
    try {
        // Obtener total de horarios del instructor
        $sql = 'SELECT COUNT(*) as total
                FROM horario h
                WHERE h.idusu = :id_instructor 
                AND h.fecha_especifica IS NOT NULL';
        
        $modelo = new Conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->bindParam(':id_instructor', $id_instructor);
        $result->execute();
        
        $total = $result->fetch(PDO::FETCH_ASSOC)['total'];
        
        echo json_encode([
            'success' => true, 
            'total' => (int)$total
        ]);
        exit;
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        exit;
    }
    
} elseif ($ope == 'debug_filtro') {
    // Endpoint para debugging del filtro de horarios
    $idfic = $_REQUEST['idfic'] ?? null;
    $iddia = $_REQUEST['iddia'] ?? null;
    $semana_selector = $_REQUEST['semana_selector'] ?? null;
    
    if (!$idfic || !$iddia) {
        echo json_encode(['success' => false, 'message' => 'Ficha y día son requeridos para debugging']);
        exit;
    }
    
    try {
        $hormhor = new hOrmhor();
        $hormhor->setIdfic($idfic);
        $hormhor->setIddia($iddia);
        
        $debug = $hormhor->debugFiltroHorarios($semana_selector);
        
        echo json_encode([
            'success' => true, 
            'debug' => $debug
        ]);
        exit;
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error en debugging: ' . $e->getMessage()]);
        exit;
    }
    
} elseif ($ope == 'probar_filtro') {
    // Endpoint para probar el filtro corregido
    $idfic = $_REQUEST['idfic'] ?? null;
    $iddia = $_REQUEST['iddia'] ?? null;
    $semana_selector = $_REQUEST['semana_selector'] ?? null;
    
    if (!$idfic || !$iddia) {
        echo json_encode(['success' => false, 'message' => 'Ficha y día son requeridos para probar el filtro']);
        exit;
    }
    
    try {
        $hormhor = new hOrmhor();
        $hormhor->setIdfic($idfic);
        $hormhor->setIddia($iddia);
        
        $resultado = $hormhor->probarFiltro($semana_selector);
        
        echo json_encode([
            'success' => true, 
            'resultado' => $resultado
        ]);
        exit;
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error probando filtro: ' . $e->getMessage()]);
        exit;
    }
    
} elseif ($ope == 'edit') {
    // ==========================================
    // VALIDACIÓN DE PERMISOS - EDITAR HORARIOS
    // ==========================================
    $idusu_actual = $_SESSION["idusu"] ?? null;
    if (!$idusu_actual) {
        echo json_encode([
            'success' => false,
            'message' => 'Usuario no autenticado.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Validar permisos para editar horarios
    if (!validarPermisosUsuario($idusu_actual, 'editar')) {
        echo json_encode([
            'success' => false,
            'message' => 'No tienes permisos para editar horarios.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Lógica para editar horarios
    $idhor_edit = $_REQUEST['idhor'] ?? null;
    $nombre_transversal = $_REQUEST['nombre_transversal'] ?? null;
    $idaul = $_REQUEST['idaul'] ?? null;
    $idusu = $_REQUEST['idusu'] ?? null;
    $es_transversal = $_REQUEST['es_transversal'] ?? null;
    $es_otros = $_REQUEST['es_otros'] ?? null;
    $actividad = $_REQUEST['actividad'] ?? null;
    $horas_otros = $_REQUEST['horas_otros'] ?? null;
    $fecha_inicio = $_REQUEST['fecha_inicio'] ?? null;
    $fecha_fin = $_REQUEST['fecha_fin'] ?? null;
    $fecha_especifica = $_REQUEST['fecha_especifica'] ?? null;
    $hinihor = $_REQUEST['hinihor'] ?? null;
    $hfinhor = $_REQUEST['hfinhor'] ?? null;

    $errores = [];

    // Procesar es_formacion_directa (puede venir como checkbox + hidden)
    $es_formacion_directa_raw = $_REQUEST['es_formacion_directa'] ?? 1;
    $es_formacion_directa = is_array($es_formacion_directa_raw) ? max($es_formacion_directa_raw) : $es_formacion_directa_raw;

    if (!$idhor_edit) {
        $errores[] = "ID de horario no válido para edición.";
    } else {
        try {
            $hormhor = new hOrmhor();
            $hormhor->setIdhor($idhor_edit);
            
            // Para edición, necesitamos obtener el día actual del horario
            $horarioActual = $hormhor->getOne();
            if ($horarioActual && !empty($horarioActual[0]['iddia'])) {
                $hormhor->setIddia($horarioActual[0]['iddia']);
                $hormhor->setIdfic($horarioActual[0]['idfic']);
                
                // Determinar el tipo de horario a partir de los campos enviados
                $esTransversal = (!empty($es_transversal) || !empty($nombre_transversal));
                $esOtros = !empty($es_otros);

                if ($esTransversal) {
                    // Configurar campos para transversal
                    $hormhor->setEsTransversal(true);
                    $hormhor->setNombreTransversal(trim($nombre_transversal));
                    $hormhor->setEsOtros(false);
                    $hormhor->setActividad(null);
                    $hormhor->setHorasOtros(null);
                } elseif ($esOtros) {
                    // Configurar campos para actividad especial "otros"
                    $hormhor->setEsTransversal(false);
                    $hormhor->setNombreTransversal(null);
                    $hormhor->setEsOtros(true);
                    $hormhor->setActividad(trim($actividad));
                    $hormhor->setHorasOtros($horas_otros);
                } else {
                    // Configurar campos para horario normal
                    $hormhor->setEsOtros(false);
                    $hormhor->setActividad(null);
                    $hormhor->setHorasOtros(null);
                    $hormhor->setEsTransversal(false);
                    $hormhor->setNombreTransversal(null);
                }
                
                // Aula (puede venir vacía en actividades "otros")
                if ($idaul === '' || $idaul === null || $idaul === 'null') {
                    $hormhor->setIdaul(null);
                } else {
                    $hormhor->setIdaul($idaul);
                }
                
                // Manejar instructor (puede ser null o vacío)
                if (!empty($idusu) && $idusu !== 'null' && $idusu !== 'undefined') {
                    $hormhor->setIdusu($idusu);
                } else {
                    $hormhor->setIdusu(null);
                }

                $hormhor->setEsFormacionDirecta($es_formacion_directa);

                // Conservar fechas actuales cuando el modal no las envía o llegan vacías
                if (!isset($_REQUEST['fecha_inicio']) || $_REQUEST['fecha_inicio'] === '') {
                    $fecha_inicio = $horarioActual[0]['fecha_inicio'] ?? null;
                }
                if (!isset($_REQUEST['fecha_fin']) || $_REQUEST['fecha_fin'] === '') {
                    $fecha_fin = $horarioActual[0]['fecha_fin'] ?? null;
                }
                if (!isset($_REQUEST['fecha_especifica']) || $_REQUEST['fecha_especifica'] === '') {
                    $fecha_especifica = $horarioActual[0]['fecha_especifica'] ?? null;
                }

                // Fechas del horario
                $hormhor->setFechaInicio($fecha_inicio);
                $hormhor->setFechaFin($fecha_fin);
                $hormhor->setFechaEspecifica($fecha_especifica);

                // Conservar horas actuales cuando el modal no las envía o llegan vacías
                if (!isset($_REQUEST['hinihor']) || $_REQUEST['hinihor'] === '') {
                    $hinihor = $horarioActual[0]['hinihor'] ?? null;
                }
                if (!isset($_REQUEST['hfinhor']) || $_REQUEST['hfinhor'] === '') {
                    $hfinhor = $horarioActual[0]['hfinhor'] ?? null;
                }

                // Horas de inicio y fin (vacío = sin hora asignada => 00:00:00)
                $hormhor->setHinihor(!empty($hinihor) ? $hinihor : '00:00:00');
                $hormhor->setHfinhor(!empty($hfinhor) ? $hfinhor : '00:00:00');
                
                $hormhor->edit();
                
                // Respuesta de éxito
                echo json_encode(['success' => true, 'message' => 'Horario actualizado correctamente.']);
                exit;
            } else {
                echo json_encode(['success' => false, 'message' => 'No se pudo encontrar el horario a editar.']);
                exit;
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar: ' . $e->getMessage()]);
            exit;
        }
    }
    
    if (!empty($errores)) {
        echo json_encode(['success' => false, 'message' => implode(', ', $errores)]);
        exit;
    }
} elseif ($ope == 'edit_grupo_transversal') {
    // Lógica para editar grupo de horarios transversales
    $idhor_edit = $_POST['idhor'] ?? null;
    $nombre_transversal = $_POST['nombre_transversal'] ?? null;
    $idaul = $_POST['idaul'] ?? null;
    $idusu = $_POST['idusu'] ?? null;
    $es_transversal = $_POST['es_transversal'] ?? null;
    $fecha_inicio = $_POST['fecha_inicio'] ?? null;
    $fecha_fin = $_POST['fecha_fin'] ?? null;
    $idaul_original = $_POST['idaul_original'] ?? null;
    $idusu_original = $_POST['idusu_original'] ?? null;


    if (!$idhor_edit || !$nombre_transversal || !$idaul || !$fecha_inicio || !$fecha_fin) {
        $errores = [];
        if (!$idhor_edit) $errores[] = 'ID de horario faltante';
        if (!$nombre_transversal) $errores[] = 'Nombre de transversal faltante';
        if (!$idaul) $errores[] = 'Aula faltante';
        if (!$fecha_inicio) $errores[] = 'Fecha de inicio faltante';
        if (!$fecha_fin) $errores[] = 'Fecha de fin faltante';
        
        echo json_encode(['success' => false, 'message' => 'Campos faltantes: ' . implode(', ', $errores)]);
        exit;
    }

    try {
        // Obtener el horario base para extraer información
        $hormhor = new hOrmhor();
        $hormhor->setIdhor($idhor_edit);
        $horarioBase = $hormhor->getOne();
        
        if (!$horarioBase || empty($horarioBase[0])) {
            echo json_encode(['success' => false, 'message' => 'No se pudo encontrar el horario base.']);
            exit;
        }
        
        $horarioInfo = $horarioBase[0];
        $idfic = $horarioInfo['idfic'];
        $iddia = $horarioInfo['iddia'];
        
        // Actualizar solo los horarios que realmente pertenecen al grupo original
        // Los criterios de agrupación son: misma ficha, mismo día, mismas fechas, 
        // mismo aula original, mismo instructor original y que sea transversal
        $sql = 'UPDATE horario SET 
                nombre_transversal = :nombre_transversal,
                idaul = :idaul,
                idusu = :idusu
                WHERE idfic = :idfic 
                AND iddia = :iddia 
                AND fecha_inicio = :fecha_inicio 
                AND fecha_fin = :fecha_fin
                AND es_transversal = 1';
        
        // Agregar criterios adicionales para ser más preciso
        if ($idaul_original !== null && $idaul_original !== '') {
            $sql .= ' AND idaul = :idaul_original';
        }
        if ($idusu_original !== null && $idusu_original !== '') {
            $sql .= ' AND idusu = :idusu_original';
        } else {
            // Si no hay instructor original, buscar horarios sin instructor
            $sql .= ' AND (idusu IS NULL OR idusu = "")';
        }
        
        $modelo = new Conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        
        // Vincular solo los parámetros que están en la consulta SQL
        $result->bindParam(':nombre_transversal', $nombre_transversal);
        $result->bindParam(':idaul', $idaul);
        $result->bindParam(':idusu', $idusu);
        $result->bindParam(':idfic', $idfic);
        $result->bindParam(':iddia', $iddia);
        $result->bindParam(':fecha_inicio', $fecha_inicio);
        $result->bindParam(':fecha_fin', $fecha_fin);
        
        // Vincular parámetros adicionales para criterios precisos
        if ($idaul_original !== null && $idaul_original !== '') {
            $result->bindParam(':idaul_original', $idaul_original);
        }
        if ($idusu_original !== null && $idusu_original !== '') {
            $result->bindParam(':idusu_original', $idusu_original);
        }
        
        $result->execute();
        
        $filasAfectadas = $result->rowCount();
        
        if ($filasAfectadas > 0) {
            echo json_encode([
                'success' => true, 
                'message' => "Grupo de horarios transversales actualizado correctamente. Se afectaron $filasAfectadas horarios."
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'No se encontraron horarios para actualizar en el grupo.']);
        }
        exit;
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar grupo: ' . $e->getMessage()]);
        exit;
    }
}

$m = 2;
$hormhor->setIddia($iddia);

/**
 * Calcula las horas mensuales para un instructor específico
 * Esta función usa la nueva lógica de días específicos y considera todos los tipos de horario
 * Sincronizada con la lógica de "ver detalle"
 */
function calcularHorasMesParaInstructor($idInstructor, $mes, $año, $idfic, $iddia) {
    try {
        // Obtener todos los horarios del instructor en el mes especificado
        // Usamos fecha_especifica para obtener días individuales según la nueva implementación
        $sql = 'SELECT h.*, f.jornada, v.nomval as nombre_jornada
                FROM horario h
                INNER JOIN ficha f ON h.idfic = f.idfic
                INNER JOIN valor v ON f.jornada = v.idval
                WHERE h.idusu = :id_instructor 
                AND h.fecha_especifica IS NOT NULL
                AND MONTH(h.fecha_especifica) = :mes 
                AND YEAR(h.fecha_especifica) = :año';
        
        $modelo = new Conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->bindParam(':id_instructor', $idInstructor);
        $result->bindParam(':mes', $mes);
        $result->bindParam(':año', $año);
        $result->execute();
        
        $horarios = $result->fetchAll(PDO::FETCH_ASSOC);
        
        error_log("Horarios encontrados para instructor $idInstructor, mes $mes, año $año: " . count($horarios));
        
        $horasTotales = 0;
        
        foreach ($horarios as $horario) {
            // Verificar que no sea festivo
            if (esFechaFestiva($horario['fecha_especifica'], $año)) {
                error_log("Horario ID {$horario['idhor']}: fecha {$horario['fecha_especifica']} es festivo, se omite");
                continue;
            }
            
            // Calcular horas por horario según el tipo (considerando el nuevo campo es_formacion_directa)
            if ($horario['es_transversal'] || $horario['es_otros'] == 0) {
                // Horario normal o transversal: usar jornada de la ficha - SIEMPRE FORMACIÓN
                $jornada = $horario['nombre_jornada'] ?? 'Mañana';
                $horasJornada = obtenerHorasJornada($jornada);
                $horasTotales += $horasJornada;
                
                error_log("Horario ID {$horario['idhor']}: jornada $jornada = $horasJornada horas (normal/transversal → FORMACIÓN)");
            } elseif ($horario['es_otros'] == 1 && isset($horario['es_formacion_directa']) && $horario['es_formacion_directa'] == 2) {
                // Actividad "otros" con formación directa: usar horas_otros - CUENTA COMO FORMACIÓN
                $horasOtros = $horario['horas_otros'] ?? 0;
                $horasTotales += $horasOtros;
                
                error_log("Horario ID {$horario['idhor']}: otros formación directa = $horasOtros horas → FORMACIÓN");
            } else {
                // Actividad "otros" tradicional: usar horas_otros - NO CUENTA PARA FORMACIÓN
                // NOTA: Estas horas NO se suman a $horasTotales porque no son formación
                $horasOtros = $horario['horas_otros'] ?? 0;
                
                error_log("Horario ID {$horario['idhor']}: otros tradicional = $horasOtros horas → NO CUENTA PARA FORMACIÓN");
            }
        }
        
        return $horasTotales;
        
    } catch (Exception $e) {
        error_log("Error calculando horas del instructor $idInstructor, mes $mes: " . $e->getMessage());
        return 0;
    }
}

/**
 * Obtiene las horas de una jornada específica
 * Según la lógica de negocio implementada en "ver detalle"
 */
function obtenerHorasJornada($jornada) {
    // Mapeo de jornadas a horas según la lógica de negocio
    $jornadas = [
        1042 => 6,  // Mañana: 6 horas
        1043 => 5,  // Tarde: 5 horas
        1044 => 4,  // Noche: 4 horas
        'Mañana' => 6,
        'Tarde' => 5,
        'Noche' => 4
    ];
    
    $horas = $jornadas[$jornada] ?? 6; // Default: Mañana (6 horas)
    error_log("DEBUG obtenerHorasJornada: jornada '$jornada' = $horas horas");
    return $horas;
}

// Cache para festivos por año
$festivosCache = [];

/**
 * Obtiene festivos de Colombia con cache local
 * @param int $año Año para obtener festivos
 * @return array Array de fechas festivas
 */
function obtenerFestivosConCache($año) {
    global $festivosCache;
    
    // Si ya está en cache, devolverlo
    if (isset($festivosCache[$año])) {
        return $festivosCache[$año];
    }
    
    // Verificar si existe archivo de cache local
    $cacheFile = __DIR__ . "/../cache/festivos_{$año}.json";
    $cacheDir = dirname($cacheFile);
    
    // Crear directorio de cache si no existe
    if (!is_dir($cacheDir)) {
        mkdir($cacheDir, 0755, true);
    }
    
    // Verificar si el archivo de cache existe y es reciente (menos de 24 horas)
    if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < 86400) {
        $festivos = json_decode(file_get_contents($cacheFile), true);
        if (is_array($festivos)) {
            $festivosCache[$año] = $festivos;
            return $festivos;
        }
    }
    
    // Si no hay cache válido, obtener de la API
    $festivos = obtenerFestivosColombia($año);
    
    if (is_array($festivos) && !empty($festivos)) {
        // Guardar en cache local
        file_put_contents($cacheFile, json_encode($festivos, JSON_UNESCAPED_UNICODE));
        $festivosCache[$año] = $festivos;
        error_log("Festivos de $año obtenidos de API y guardados en cache");
    } else {
        // Si falla la API, usar festivos fijos como fallback
        $festivosFijos = [
            '01-01', '01-08', '03-25', '03-28', '03-29',
            '05-01', '05-13', '06-03', '06-10', '07-01',
            '07-20', '08-07', '08-15', '08-19', '10-14', '11-04',
            '11-11', '12-08', '12-25'
        ];
        
        $festivos = [];
        foreach ($festivosFijos as $festivo) {
            $festivos[] = $año . '-' . $festivo;
        }
        
        $festivosCache[$año] = $festivos;
        error_log("Usando festivos fijos para $año (API no disponible)");
    }
    
    return $festivosCache[$año];
}

/**
 * Verifica si una fecha es festiva en Colombia
 * @param string $fecha Fecha en formato Y-m-d
 * @param int $año Año para obtener festivos
 * @return bool True si es festivo, false si no
 */
function esFechaFestiva($fecha, $año) {
    try {
        $festivos = obtenerFestivosConCache($año);
        return in_array($fecha, $festivos);
    } catch (Exception $e) {
        error_log("Error verificando festivo para fecha $fecha: " . $e->getMessage());
        return false;
    }
}

/**
 * Filtra fechas excluyendo festivos y fines de semana
 * @param array $fechas Array de fechas en formato Y-m-d
 * @param int $año Año para verificar festivos
 * @return array Array de fechas laborales
 */
function filtrarFechasLaborales($fechas, $año) {
    $fechasLaborales = [];
    
    foreach ($fechas as $fecha) {
        $fechaObj = new DateTime($fecha);
        $diaSemana = $fechaObj->format('N'); // 1=Lunes, 7=Domingo
        
        // Solo incluir si es de lunes a viernes y no es festivo
        if ($diaSemana >= 1 && $diaSemana <= 5 && !esFechaFestiva($fecha, $año)) {
            $fechasLaborales[] = $fecha;
        }
    }
    
    return $fechasLaborales;
}

/**
 * Obtiene el nombre de una jornada a partir de su ID o nombre
 * Convierte IDs numéricos a nombres legibles
 */
function obtenerNombreJornada($jornada) {
    // Mapeo de IDs a nombres de jornada
    $nombresJornada = [
        1042 => 'Mañana',
        1043 => 'Tarde',
        1044 => 'Noche',
        'Mañana' => 'Mañana',
        'Tarde' => 'Tarde',
        'Noche' => 'Noche'
    ];
    
    $nombre = $nombresJornada[$jornada] ?? 'Mañana'; // Default: Mañana
    return $nombre;
}

/**
 * Guarda las horas mensuales en la tabla horas_mensuales
 */
function guardarHorasMensuales($idInstructor, $mes, $año, $horas) {
    try {
        // Primero verificar si ya existe un registro para este instructor/mes/año
        $sqlCheck = 'SELECT id, horas_totales FROM horas_mensuales 
                     WHERE id_instructor = :id_instructor AND mes = :mes AND año = :año';
        
        $modelo = new Conexion();
        $conexion = $modelo->get_conexion();
        
        $resultCheck = $conexion->prepare($sqlCheck);
        $resultCheck->bindParam(':id_instructor', $idInstructor);
        $resultCheck->bindParam(':mes', $mes);
        $resultCheck->bindParam(':año', $año);
        $resultCheck->execute();
        
        $existingRecord = $resultCheck->fetch(PDO::FETCH_ASSOC);
        
        if ($existingRecord) {
            // Actualizar registro existente
            $sql = 'UPDATE horas_mensuales 
                    SET horas_totales = :horas_totales, 
                        fecha_actualizacion = CURRENT_TIMESTAMP
                    WHERE id = :id';
            
            $result = $conexion->prepare($sql);
            $result->bindParam(':horas_totales', $horas);
            $result->bindParam(':id', $existingRecord['id']);
            
            
        } else {
            // Insertar nuevo registro
            $sql = 'INSERT INTO horas_mensuales (id_instructor, mes, año, horas_totales) 
                    VALUES (:id_instructor, :mes, :año, :horas_totales)';
            
            $result = $conexion->prepare($sql);
            $result->bindParam(':id_instructor', $idInstructor);
            $result->bindParam(':mes', $mes);
            $result->bindParam(':año', $año);
            $result->bindParam(':horas_totales', $horas);
            
        }
        
        $success = $result->execute();
        
        return $success;
        
    } catch (Exception $e) {
        error_log("Error guardando horas mensuales: " . $e->getMessage());
        return false;
    }
}

/**
 * Genera un registro individual para una fecha específica
 * Esta función crea un solo registro para el horario individual
 */
function generarRegistroIndividual($idfic, $idaul, $idusu, $iddia, $es_transversal, $nombre_transversal, $es_otros, $actividad, $horas_otros, $es_formacion_directa, $fecha_especifica, $hinihor = null, $hfinhor = null) {
    try {
        $modelo = new Conexion();
        $conexion = $modelo->get_conexion();

        // Horas por defecto si no se envían
        if (empty($hinihor)) $hinihor = '00:00:00';
        if (empty($hfinhor)) $hfinhor = '00:00:00';

        // Verificar si la columna existe
        $checkColumn = $conexion->prepare("SHOW COLUMNS FROM horario LIKE 'es_formacion_directa'");
        $checkColumn->execute();
        $columnExists = $checkColumn->rowCount() > 0;

        // Categoria de hora (bd/migracion_categoria_hora.sql): otros|transversal -> 'otra'.
        $checkCat = $conexion->prepare("SHOW COLUMNS FROM horario LIKE 'categoria_hora'");
        $checkCat->execute();
        $catExists = $checkCat->rowCount() > 0;
        $categoria_hora = (!empty($es_otros) || !empty($es_transversal)) ? 'otra' : 'formacion';
        
        if ($columnExists) {
            // Si existe el campo, usar la consulta completa
            $sql = 'INSERT INTO horario (idfic, idaul, idusu, iddia, es_transversal, nombre_transversal, 
                                       es_otros, actividad, horas_otros, es_formacion_directa, fecha_especifica, hinihor, hfinhor'
                . ($catExists ? ', categoria_hora' : '') . ') 
                    VALUES (:idfic, :idaul, :idusu, :iddia, :es_transversal, :nombre_transversal, 
                            :es_otros, :actividad, :horas_otros, :es_formacion_directa, :fecha_especifica, :hinihor, :hfinhor'
                . ($catExists ? ', :categoria_hora' : '') . ')';
        } else {
            // Si no existe el campo, usar consulta sin él
            $sql = 'INSERT INTO horario (idfic, idaul, idusu, iddia, es_transversal, nombre_transversal, 
                                       es_otros, actividad, horas_otros, fecha_especifica, hinihor, hfinhor) 
                    VALUES (:idfic, :idaul, :idusu, :iddia, :es_transversal, :nombre_transversal, 
                            :es_otros, :actividad, :horas_otros, :fecha_especifica, :hinihor, :hfinhor)';
        }
        
        $result = $conexion->prepare($sql);
        $result->bindParam(':idfic', $idfic);
        $result->bindParam(':idaul', $idaul);
        $result->bindParam(':idusu', $idusu);
        $result->bindParam(':iddia', $iddia);
        $result->bindParam(':es_transversal', $es_transversal);
        $result->bindParam(':nombre_transversal', $nombre_transversal);
        $result->bindParam(':es_otros', $es_otros);
        $result->bindParam(':actividad', $actividad);
        $result->bindParam(':horas_otros', $horas_otros);
        $result->bindParam(':fecha_especifica', $fecha_especifica);
        $result->bindParam(':hinihor', $hinihor);
        $result->bindParam(':hfinhor', $hfinhor);
        
        if ($columnExists) {
            $result->bindParam(':es_formacion_directa', $es_formacion_directa);
        }

        if ($catExists) {
            $result->bindParam(':categoria_hora', $categoria_hora);
        }
        
        $success = $result->execute();
        
        if ($success) {
            error_log("Registro individual creado exitosamente para fecha: $fecha_especifica");
            return [$fecha_especifica];
        } else {
            error_log("Error creando registro individual para fecha: $fecha_especifica");
            return [];
        }
        
    } catch (Exception $e) {
        error_log("Error en generarRegistroIndividual: " . $e->getMessage());
        return [];
    }
}

/**
 * Genera registros individuales por día para un horario
 * Esta función crea un registro por cada día específico del horario
 */
function generarRegistrosIndividuales($idfic, $idaul, $idusu, $iddia, $es_transversal, $nombre_transversal, $es_otros, $actividad, $horas_otros, $es_formacion_directa, $fecha_inicio, $fecha_fin, $hinihor = null, $hfinhor = null) {
    try {
        // Horas por defecto si no se envían
        if (empty($hinihor)) $hinihor = '00:00:00';
        if (empty($hfinhor)) $hfinhor = '00:00:00';

        // Obtener los días específicos que corresponden al día de la semana
        $diasEspecificos = generarDiasDelHorario($fecha_inicio, $fecha_fin, $iddia);
                
        $registrosGenerados = [];
        
        foreach ($diasEspecificos as $fecha) {
            try {
                // Crear registro individual para este día
                // Verificar si el campo es_formacion_directa existe en la tabla
                $modelo = new Conexion();
                $conexion = $modelo->get_conexion();
                
                // Verificar si la columna existe
                $checkColumn = $conexion->prepare("SHOW COLUMNS FROM horario LIKE 'es_formacion_directa'");
                $checkColumn->execute();
                $columnExists = $checkColumn->rowCount() > 0;

                // Categoria de hora (bd/migracion_categoria_hora.sql): otros|transversal -> 'otra'.
                $checkCat = $conexion->prepare("SHOW COLUMNS FROM horario LIKE 'categoria_hora'");
                $checkCat->execute();
                $catExists = $checkCat->rowCount() > 0;
                $categoria_hora = (!empty($es_otros) || !empty($es_transversal)) ? 'otra' : 'formacion';
                
                if ($columnExists) {
                    // Si existe el campo, usar la consulta completa
                    $sql = 'INSERT INTO horario (idfic, idaul, idusu, iddia, es_transversal, nombre_transversal, 
                                               es_otros, actividad, horas_otros, es_formacion_directa, fecha_inicio, fecha_fin, fecha_especifica, hinihor, hfinhor'
                        . ($catExists ? ', categoria_hora' : '') . ') 
                            VALUES (:idfic, :idaul, :idusu, :iddia, :es_transversal, :nombre_transversal, 
                                    :es_otros, :actividad, :horas_otros, :es_formacion_directa, :fecha_inicio, :fecha_fin, :fecha_especifica, :hinihor, :hfinhor'
                        . ($catExists ? ', :categoria_hora' : '') . ')';
                } else {
                    // Si no existe el campo, usar consulta sin él
                $sql = 'INSERT INTO horario (idfic, idaul, idusu, iddia, es_transversal, nombre_transversal, 
                                           es_otros, actividad, horas_otros, fecha_inicio, fecha_fin, fecha_especifica, hinihor, hfinhor) 
                        VALUES (:idfic, :idaul, :idusu, :iddia, :es_transversal, :nombre_transversal, 
                                :es_otros, :actividad, :horas_otros, :fecha_inicio, :fecha_fin, :fecha_especifica, :hinihor, :hfinhor)';
                }
                
                $result = $conexion->prepare($sql);
                
                $result->bindParam(':idfic', $idfic);
                $result->bindParam(':idaul', $idaul);
                $result->bindParam(':idusu', $idusu);
                $result->bindParam(':iddia', $iddia);
                $result->bindParam(':es_transversal', $es_transversal);
                $result->bindParam(':nombre_transversal', $nombre_transversal);
                $result->bindParam(':es_otros', $es_otros);
                $result->bindParam(':actividad', $actividad);
                $result->bindParam(':horas_otros', $horas_otros);
                $result->bindParam(':hinihor', $hinihor);
                $result->bindParam(':hfinhor', $hfinhor);
                
                // Solo vincular es_formacion_directa si la columna existe
                if ($columnExists) {
                    // Asegurar que el valor no sea null
                    $valorFormacionDirecta = ($es_formacion_directa == 2) ? 2 : 1;
                    $result->bindParam(':es_formacion_directa', $valorFormacionDirecta);
                }

                if ($catExists) {
                    $result->bindParam(':categoria_hora', $categoria_hora);
                }
                
                $result->bindParam(':fecha_inicio', $fecha_inicio);
                $result->bindParam(':fecha_fin', $fecha_fin);
                $result->bindParam(':fecha_especifica', $fecha);
                
                if ($result->execute()) {
                    $registrosGenerados[] = $fecha;
                    error_log("Registro generado para fecha: $fecha");
                } else {
                    error_log("Error generando registro para fecha: $fecha");
                }
                
            } catch (Exception $e) {
                error_log("Error generando registro para fecha $fecha: " . $e->getMessage());
            }
        }
        
        return $registrosGenerados;
        
    } catch (Exception $e) {
        error_log("Error en generarRegistrosIndividuales: " . $e->getMessage());
        return [];
    }
}

/**
 * Genera los días específicos para un horario basado en el día de la semana
 */
function generarDiasDelHorario($fecha_inicio, $fecha_fin, $iddia) {
    $dias = [];
    
    try {
        // Configurar zona horaria de Colombia
        $zonaColombia = new DateTimeZone('America/Bogota');
        
        $inicio = new DateTime($fecha_inicio, $zonaColombia);
        $fin = new DateTime($fecha_fin, $zonaColombia);
        
        // Mapeo de días de la semana (CORREGIDO para format('N'))
        $diasSemana = [
            1042 => 1,  // Lunes
            1043 => 2,  // Martes
            1044 => 3,  // Miércoles
            1045 => 4,  // Jueves
            1046 => 5,  // Viernes
            1047 => 6,  // sbaado
            1048 => 7,  // domingo 
        ];
        
        $diaSemana = $diasSemana[$iddia] ?? 1; // Default a lunes si no se encuentra
        
        $fechaActual = clone $inicio;
        
        while ($fechaActual <= $fin) {
            // Si la fecha actual corresponde al día de la semana
            if ($fechaActual->format('N') == $diaSemana) {
                $dias[] = $fechaActual->format('Y-m-d');
                error_log("Día agregado: " . $fechaActual->format('Y-m-d') . " (" . $fechaActual->format('l') . ")");
            }
            
            $fechaActual->add(new DateInterval('P1D'));
        }
        
        return $dias;
        
    } catch (Exception $e) {
        error_log("Error generando días del horario: " . $e->getMessage());
        return [];
    }
}

/**
 * Recalcula todas las horas mensuales de un instructor para un año específico
 * Usa la nueva lógica de días específicos y está sincronizada con "ver detalle"
 * Útil para corregir datos o recalcular después de cambios
 */
function recalcularHorasMensualesInstructor($idInstructor, $año) {
    try {
        // Obtener todos los horarios del instructor para el año
        // Ahora usamos fecha_especifica para obtener días individuales
        $sql = 'SELECT h.*, f.jornada, v.nomval as nombre_jornada
                FROM horario h
                INNER JOIN ficha f ON h.idfic = f.idfic
                INNER JOIN valor v ON f.jornada = v.idval
                WHERE h.idusu = :id_instructor 
                AND h.fecha_especifica IS NOT NULL
                AND YEAR(h.fecha_especifica) = :año
                ORDER BY h.fecha_especifica';
        
        $modelo = new Conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->bindParam(':id_instructor', $idInstructor);
        $result->bindParam(':año', $año);
        $result->execute();
        
        $horarios = $result->fetchAll(PDO::FETCH_ASSOC);
        
        return true;
    } catch (Exception $e) {
        error_log("Error en recalcularHorasMensualesInstructor: " . $e->getMessage());
        return false;
    }
}

if (!function_exists('obtenerHorariosInstructorEnRango')) {
    /**
     * Obtiene los horarios del instructor filtrados por rango de fechas (excluyendo festivos)
     */
    function obtenerHorariosInstructorEnRango($mhor, $idInstructor, $fechaInicio, $fechaFin) {
        try {
            if (empty($idInstructor)) return [];
            
            $mhorTemp = ($mhor instanceof Hormhor) ? $mhor : new Hormhor();
            $horarios = $mhorTemp->getHorariosPorInstructor($idInstructor);
            $horariosFiltrados = [];
            
            foreach ($horarios as $horario) {
                if (isset($horario['fecha_especifica']) && !empty($horario['fecha_especifica'])) {
                    $fechaHorario = new DateTime($horario['fecha_especifica']);
                    $inicio = new DateTime($fechaInicio);
                    $fin = new DateTime($fechaFin);
                    
                    if ($fechaHorario >= $inicio && $fechaHorario <= $fin) {
                        $año = $fechaHorario->format('Y');
                        if (!function_exists('esFechaFestiva') || !esFechaFestiva($horario['fecha_especifica'], $año)) {
                            if (!isset($horario['jornada_nombre'])) {
                                $horario['jornada_nombre'] = $horario['nombre_jornada'] ?? 'Mañana';
                            }
                            $horariosFiltrados[] = $horario;
                        }
                    }
                }
            }
            return $horariosFiltrados;
        } catch (Exception $e) {
            error_log("Error en obtenerHorariosInstructorEnRango: " . $e->getMessage());
            return [];
        }
    }
}

if (!function_exists('obtenerTipoContratoYMaxHoras')) {
    /**
     * Determina tipo de contrato del instructor (planta / contratista) y sus horas máximas:
     * - Instructor de planta: 187 horas mensuales
     * - Instructor contratista: 160 horas mensuales
     */
    function obtenerTipoContratoYMaxHoras($idUsuario) {
        try {
            if (empty($idUsuario)) {
                return ['tipo_contrato' => 'contratista', 'max_horas' => (defined('CONTRATISTA_MAX_HORAS') ? CONTRATISTA_MAX_HORAS : 160)];
            }
            $modelo = new Conexion();
            $conexion = $modelo->get_conexion();

            $sqlUsu = 'SELECT u.fecini, u.fecfin, u.idper,
                              (SELECT up.idper FROM usupef up WHERE up.idusu = u.idusu AND up.idper IN (12) LIMIT 1) AS es_perfil_contratista,
                              (SELECT up.idper FROM usupef up WHERE up.idusu = u.idusu AND up.idper IN (1, 6, 10, 11) LIMIT 1) AS es_perfil_funcionario
                       FROM usuario u 
                       WHERE u.idusu = :idusu LIMIT 1';
            $stmtUsu = $conexion->prepare($sqlUsu);
            $stmtUsu->bindParam(':idusu', $idUsuario);
            $stmtUsu->execute();
            $usu = $stmtUsu->fetch(PDO::FETCH_ASSOC);

            if (!$usu) {
                return ['tipo_contrato' => 'contratista', 'max_horas' => 160];
            }

            $fecini = $usu['fecini'] ?? null;
            $fecfin = $usu['fecfin'] ?? null;
            $idper = (int)($usu['idper'] ?? 0);
            $esPerfilContratista = !empty($usu['es_perfil_contratista']) || $idper === 12;
            $esPerfilFuncionario = !empty($usu['es_perfil_funcionario']) || in_array($idper, [1, 6, 10, 11], true);

            if ($esPerfilContratista || (!empty($fecini) && !empty($fecfin) && !$esPerfilFuncionario)) {
                return ['tipo_contrato' => 'contratista', 'max_horas' => (defined('CONTRATISTA_MAX_HORAS') ? CONTRATISTA_MAX_HORAS : 160)];
            } else {
                return ['tipo_contrato' => 'planta', 'max_horas' => (defined('PLANTA_MAX_HORAS') ? PLANTA_MAX_HORAS : 187)];
            }
        } catch (Exception $e) {
            return ['tipo_contrato' => 'contratista', 'max_horas' => 160];
        }
    }
}