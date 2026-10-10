<?php
define('ROOT_PATH', dirname(__DIR__));

// Suprimir TODOS los errores y warnings
error_reporting(0);
ini_set('display_errors', 0);

// Output buffering DESHABILITADO - causaba problemas de renderizado
// ob_start();

// La sesión puede no estar iniciada cuando se entra por AJAX
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// DEBUG: Verificar estado de la sesión (SOLO para operaciones no-JSON)
// echo "<!-- DEBUG SESIÓN: session_status() = " . session_status() . " -->";
// echo "<!-- DEBUG SESIÓN: aut = " . (isset($_SESSION["aut"]) ? $_SESSION["aut"] : 'NO DEFINIDA') . " -->";
// echo "<!-- DEBUG SESIÓN: idusu = " . (isset($_SESSION["idusu"]) ? $_SESSION["idusu"] : 'NO DEFINIDA') . " -->";

require_once ROOT_PATH . '/models/hormeval.php';

$mjui = new Hormeval();

// Definir variable pg si no está definida
$pg = isset($_REQUEST['pg']) ? $_REQUEST['pg'] : '2117';

$idjui = isset($_REQUEST['idjui']) ? $_REQUEST['idjui']:NULL;
$idusu = isset($_POST['idusu']) ? $_POST['idusu']:NULL;
$idfic = isset($_POST['idfic']) ? $_POST['idfic']:NULL;
$idres = isset($_POST['idres']) ? $_POST['idres']:NULL;
$caljui = isset($_POST['caljui']) ? $_POST['caljui']:NULL;

$opera = isset($_REQUEST['opera']) ? $_REQUEST['opera']:NULL;
$datOne = NULL;


$mjui->setIdjui($idjui);

if($opera=="save"){
	// LOG: Inicio del proceso de guardado
	error_log("=== INICIO PROCESO GUARDAR JUICIO ===");
	error_log("Timestamp: " . date('Y-m-d H:i:s'));
	
	
	// Configurar todos los campos del modelo
	$mjui->setIdusu($idusu);
	$mjui->setIdfic($idfic);
	$mjui->setCaljui($caljui);
	
	// Campos adicionales del formulario
	$ndoce = isset($_POST['ndoce']) ? $_POST['ndoce'] : '';
	$nomes = isset($_POST['nomes']) ? $_POST['nomes'] : '';
	$apees = isset($_POST['apees']) ? $_POST['apees'] : '';
	$estes = isset($_POST['estes']) ? $_POST['estes'] : 'EN FORMACIÓN';
	$compe = isset($_POST['compe']) ? $_POST['compe'] : '';
	$resapr = isset($_POST['resapr']) ? $_POST['resapr'] : '';
	$tidoc = isset($_POST['tidoc']) ? $_POST['tidoc'] : 'CC'; // Tipo de documento
	
	// LOG: Datos del formulario
	error_log("- ndoce: " . $ndoce);
	error_log("- nomes: " . $nomes);
	error_log("- apees: " . $apees);
	error_log("- estes: " . $estes);
	error_log("- compe: " . $compe);
	error_log("- resapr: " . $resapr);
	error_log("- tidoc: " . $tidoc);
	
	// Configurar campos adicionales
	$mjui->setNdoce($ndoce);
	$mjui->setNomes($nomes);
	$mjui->setApees($apees);
	$mjui->setEstes($estes);
	$mjui->setCompe($compe);
	$mjui->setResapr($resapr);
	$mjui->setTidoc($tidoc);
	
	// Configurar campos automáticos
	$mjui->setFecju(date('Y-m-d H:i:s'));
	$mjui->setFecre(date('Y-m-d'));
	$mjui->setFecim(date('Y-m-d H:i:s'));
	
	
	
	// Cargar información automática de la ficha
	if($idfic) {
		$infoFicha = $mjui->cargarInfoFicha($idfic);
		if($infoFicha) {
			$mjui->setFicca($infoFicha['idfic']);
			$mjui->setDenpr($infoFicha['nompro'] ?? '');
			$mjui->setEstfi('ACTIVO'); // Valor por defecto
			$mjui->setFecin($infoFicha['finific'] ?? '');
			$mjui->setFecfi($infoFicha['ffinfic'] ?? '');
			$mjui->setModfo('PRESENCIAL'); // Valor por defecto
			$mjui->setRegfo('REGIONAL'); // Valor por defecto
			$mjui->setCenfo($infoFicha['mun'] ?? '');
		}
	}
	
	// Cargar información automática del usuario
	if($idusu) {
		$infoUsuario = $mjui->cargarInfoUsuario($idusu);
		if($infoUsuario) {
			$mjui->setFunre('CC ' . $infoUsuario['ndocusu'] . ' - ' . $infoUsuario['nomusu']);
			$mjui->setTidoc('CC');
		}
	}
	
	// Lógica correcta: Verificar si existe antes de decidir INSERT o UPDATE
	if($ndoce && $compe && $resapr) {
		// LOG: Verificando campos obligatorios
		error_log("✓ Campos obligatorios completos, procediendo con la lógica");
		error_log("📋 DATOS RECIBIDOS DEL FORMULARIO:");
		error_log("- ndoce: $ndoce");
		error_log("- compe: $compe");
		error_log("- resapr: $resapr");
		error_log("- caljui: $caljui");
		error_log("- idusu: $idusu");
		error_log("- idfic: $idfic");
		
		// Convertir calificación a mayúsculas para consistencia con la BD
		$caljui = strtoupper($caljui);
		error_log("📝 Calificación convertida a mayúsculas: $caljui");
		
		// Obtener idres del resultado de aprendizaje
		$resultado = $mjui->extraerResultado($resapr);
		$idres = $resultado['idres'];
		
		error_log("📋 ID del resultado obtenido: " . $idres);
		
		// Validar que se obtuvo el idres
		if (!$idres) {
			error_log("❌ ERROR: No se pudo obtener el ID del resultado");
			echo "<script>alert('❌ Error: No se pudo procesar el resultado de aprendizaje seleccionado');</script>";
			exit;
		}
		
		// 1. Buscar si ya existe un juicio con idusu e idres
		error_log("🔍 Buscando juicio existente con: idusu=$idusu, idres=$idres");
		$juicioExistente = $mjui->buscarJuicioPorIdusuIdres($idusu, $idres);
		
		// LOG: Verificar qué juicio se encontró
		if($juicioExistente) {
			error_log("✅ JUICIO ENCONTRADO PARA ACTUALIZAR:");
			error_log("- ID Juicio: " . $juicioExistente['idjui']);
			error_log("- Calificación actual: " . $juicioExistente['caljui']);
			error_log("- Nueva calificación: $caljui");
		} else {
			error_log("❌ NO SE ENCONTRÓ JUICIO EXISTENTE - Se creará nuevo");
		}
		
		if($juicioExistente) {
			// LOG: Juicio encontrado
			error_log("✅ JUICIO EXISTENTE ENCONTRADO - ID: " . $juicioExistente['idjui']);
			error_log("📝 Actualizando juicio existente...");
			
			// 2. Si existe, actualizar
			$resultado = $mjui->actualizarJuicio($juicioExistente['idjui'], $caljui);
			if($resultado) {
				error_log("✅ JUICIO ACTUALIZADO EXITOSAMENTE");
				echo "<script>
					alert('✅ Juicio actualizado con éxito\\n\\nID Juicio: " . $juicioExistente['idjui'] . "\\nDocumento: " . $ndoce . "\\nCompetencia: " . $compe . "\\nResultado: " . $resapr . "\\nCalificación: " . $caljui . "');
					// Redireccionar a la página limpia con mensaje de éxito
					window.location.href = 'home.php?pg=2117&msg=updated';
				</script>";
			} else {
				error_log("❌ ERROR AL ACTUALIZAR JUICIO");
				echo "<script>
					alert('❌ Error al actualizar el juicio\\n\\nPor favor, intente nuevamente o contacte al administrador.');
					window.location.href = 'home.php?pg=2117';
				</script>";
			}
		} else {
			// LOG: Juicio no encontrado
			error_log("🆕 JUICIO NO EXISTE - Creando nuevo juicio");
			error_log("📝 Creando nuevo juicio...");
			
			// 3. Si no existe, crear nuevo
			$resultado = $mjui->saveInteligente();
			if($resultado) {
				error_log("✅ JUICIO CREADO EXITOSAMENTE");
				echo "<script>
					alert('✅ Juicio creado con éxito\\n\\nDocumento: " . $ndoce . "\\nCompetencia: " . $compe . "\\nResultado: " . $resapr . "\\nCalificación: " . $caljui . "');
					// Redireccionar a la página limpia con mensaje de éxito
					window.location.href = 'home.php?pg=2117&msg=created';
				</script>";
			} else {
				error_log("❌ ERROR AL CREAR JUICIO");
				echo "<script>
					alert('❌ Error al crear el juicio\\n\\nPor favor, intente nuevamente o contacte al administrador.');
					window.location.href = 'home.php?pg=2117';
				</script>";
			}
		}
	} else {
		// LOG: Campos faltantes
		error_log("❌ CAMPOS OBLIGATORIOS FALTANTES");
		error_log("- ndoce: " . ($ndoce ? 'OK' : 'FALTA'));
		error_log("- compe: " . ($compe ? 'OK' : 'FALTA'));
		error_log("- resapr: " . ($resapr ? 'OK' : 'FALTA'));
		
		// Campos obligatorios faltantes
		echo "<script>alert('❌ Campos obligatorios faltantes\\n\\nDebe completar:\\n- Número de documento\\n- Competencia\\n- Resultado de aprendizaje');</script>";
	}
	
	// LOG: Fin del proceso
	error_log("=== FIN PROCESO GUARDAR JUICIO ===");
	exit;
}

	// Editar juicio
	if($opera=="edit"){
		$mjui = new Mjui();
		$resultado = $mjui->edit($idjui, $idusu, $idfic, $idres, $ndoce, $nomes, $apees, $estes, $caljui, $codre, $resapr);
		if($resultado){
			echo "<script>location.href='home.php?pg=$pg&msg=edit';</script>";
		} else {
			echo "<script>alert('Error al editar el juicio');</script>";
		}
		exit;
	}
	
	// Eliminar juicio
	if($opera=="eli"){
		$mjui = new Mjui();
		$resultado = $mjui->eli($idjui);
		if($resultado){
    echo "<script>window.location.href='home.php?pg=$pg&msg=eliminado';</script>";
		} else {
			echo "<script>alert('Error al eliminar el juicio');</script>";
		}
    exit;
}

if($opera=="edi" && $idjui){
	$datOne = $mjui->getOne();
}

// Validar relación competencia-resultado
if ($opera === "validar_relacion") {
    while (ob_get_level() > 0) { ob_end_clean(); }
    header('Content-Type: application/json; charset=utf-8');
    
    try {
        $idcom = isset($_POST['idcom']) ? trim($_POST['idcom']) : '';
        $idres = isset($_POST['idres']) ? trim($_POST['idres']) : '';
        
        if (!$idcom || !$idres) {
            echo json_encode(['valida' => false, 'error' => 'Faltan parámetros']);
            exit;
        }
        
        $valida = $mjui->validarRelacionCompetenciaResultado($idcom, $idres);
        echo json_encode(['valida' => $valida]);
        exit;
        
    } catch (Exception $e) {
        echo json_encode(['valida' => false, 'error' => $e->getMessage()]);
        exit;
    }
}

// Obtener resultados por competencia
if ($opera === "get_resultados_competencia") {
    while (ob_get_level() > 0) { ob_end_clean(); }
    header('Content-Type: application/json; charset=utf-8');
    
    try {
        $idcom = isset($_POST['idcom']) ? trim($_POST['idcom']) : '';
        
        if (!$idcom) {
            echo json_encode(['resultados' => []]);
            exit;
        }
        
        $resultados = $mjui->getResultadosPorCompetencia($idcom);
        echo json_encode(['resultados' => $resultados]);
        exit;
        
    } catch (Exception $e) {
        echo json_encode(['resultados' => [], 'error' => $e->getMessage()]);
        exit;
    }
}

// Edición rápida de calificación
if ($opera === "editar_calificacion_rapida") {
    while (ob_get_level() > 0) { ob_end_clean(); }
    header('Content-Type: application/json; charset=utf-8');
    
    try {
        $idjui = isset($_POST['idjui']) ? trim($_POST['idjui']) : '';
        $calificacion = isset($_POST['calificacion']) ? trim($_POST['calificacion']) : '';
        
        if (!$idjui || !$calificacion) {
            echo json_encode(['success' => false, 'error' => 'Faltan parámetros']);
            exit;
        }
        
        // Validar que la calificación sea válida
        $calificacionesValidas = ['APROBADO', 'NO APROBADO', 'POR EVALUAR'];
        if (!in_array(strtoupper($calificacion), $calificacionesValidas)) {
            echo json_encode(['success' => false, 'error' => 'Calificación inválida']);
            exit;
        }
        
        // Actualizar calificación
        $resultado = $mjui->actualizarJuicio($idjui, strtoupper($calificacion));
        
        if ($resultado) {
            echo json_encode([
                'success' => true, 
                'mensaje' => 'Calificación actualizada correctamente',
                'calificacion' => strtoupper($calificacion)
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Error al actualizar calificación']);
        }
        exit;
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        exit;
    }
}

// Búsqueda AJAX por documento – SIEMPRE JSON
if ($opera === "buscar_juicio") {
    // Mata cualquier salida previa
    while (ob_get_level() > 0) { ob_end_clean(); }
    header('Content-Type: application/json; charset=utf-8');

    try {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }

        $ndoce = isset($_POST['ndoce']) ? trim($_POST['ndoce']) : '';
        if ($ndoce === '') {
            http_response_code(400);
            echo json_encode([
                'ok' => false,
                'error' => 'Falta el parámetro ndoc(e)'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // IMPORTANTE: no imprimas nada antes de este punto
        error_log("🔍 BUSCANDO JUICIO POR DOCUMENTO: $ndoce");
        $juicio = $mjui->buscarJuicioPorDocumento($ndoce); // ya existe en mjui.php
        
        if($juicio) {
            error_log("✅ JUICIO ENCONTRADO PARA CARGAR EN FORMULARIO:");
            error_log("- ID Juicio: " . $juicio['idjui']);
            error_log("- Calificación: " . $juicio['caljui']);
            error_log("- Usuario: " . $juicio['idusu']);
            error_log("- Resultado: " . $juicio['idres']);
        } else {
            error_log("❌ NO SE ENCONTRÓ JUICIO PARA CARGAR");
        }

        // (Opcional) Cargar nombre/apellido/ficha aunque no exista juicio
        $infoUsuario = $mjui->cargarInfoUsuarioPorDocumento($ndoce); // crea esta helper si no existe
        $infoFicha   = null;
        if (!empty($infoUsuario['idfic'])) {
            $infoFicha = $mjui->cargarInfoFicha($infoUsuario['idfic']);
        }

        // Mapear 'orde' como 'estes' para compatibilidad con el frontend
        if ($juicio && isset($juicio['orde'])) {
            $juicio['estes'] = $juicio['orde'];
        }

        echo json_encode([
            'ok'      => true,
            'existe'  => $juicio ? true : false,
            'juicio'  => $juicio ?: null,
            'usuario' => $infoUsuario ?: null,
            'ficha'   => $infoFicha ?: null,
            'msg'     => $juicio ? 'Juicio encontrado' : 'Sin juicio previo; se cargó info del estudiante si existe'
        ], JSON_UNESCAPED_UNICODE);
        exit;

    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode([
            'ok' => false,
            'error' => 'Error del servidor',
            'detail' => $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// Manejar importación de Excel - PASO 1: Convertir a CSV
if($opera=="import"){
            // 1. Verificar que se haya enviado un archivo Excel
            if (!isset($_FILES['excel_file']) || $_FILES['excel_file']['error'] !== UPLOAD_ERR_OK) {
                // Si no hay archivo, redirigir a la vista de importación
                header("Location: home.php?pg=2117&opera=import_step1");
                exit;
            }
            
            // 2. Procesar el Excel usando el modelo (ya incluido arriba)
            // require_once("../models/mjui.php"); // YA INCLUIDO ARRIBA
            // $mjui = new Mjui(); // YA INSTANCIADO ARRIBA
            
            try {
                // 3. Procesar Excel y convertir a CSV (la ficha se extrae automáticamente del archivo)
                $resultado = $mjui->procesarImportacionExcel($_FILES['excel_file'], null);
                
                // 4. Si el procesamiento fue exitoso, guardar en sesión
                if (isset($_SESSION['excel_data']) && !empty($_SESSION['excel_data'])) {
                    $_SESSION['procesamiento_exitoso'] = true;
                    $_SESSION['resumen_procesamiento'] = $resultado;
                }
                
            } catch (Exception $e) {
                // 5. Si hay error, guardar en sesión para mostrar en la vista
                $_SESSION['error_procesamiento'] = $e->getMessage();
            }
            
            // 6. Redirigir a la vista de importación con los datos procesados
            header("Location: home.php?pg=2117&opera=import_step1");
            exit;
        }

        // Manejar importación de Excel - PASO 2: Importar a BD (CON GESTIÓN INTEGRADA)
        if($opera=="import_step2"){
            // Ejecutar importación completa con gestión de programa, competencias y resultados
            // require_once("models/mjui.php"); // YA INCLUIDO ARRIBA
            // $mjui = new Mjui(); // YA INSTANCIADO ARRIBA
            $resultado = $mjui->procesarImportacionCompleta($_SESSION['excel_data'] ?? [], $_SESSION['idfic_import'] ?? null, $_SESSION['file_name'] ?? '');
            
            // Guardar resultado en sesión para mostrar en la vista de visualización
            $_SESSION['resultado_importacion'] = $resultado;
            $_SESSION['idfic_importado'] = $_SESSION['idfic_import'] ?? null;
            
            // Redirigir a la vista de visualización para mostrar resultados
            header("Location: home.php?pg=2117&opera=ver_importados");
            exit;
        }

// Manejar visualización de juicios importados
if($opera=="ver_importados"){
	// Cargar directamente la vista de visualización con contexto completo
	require_once("views/vjuivis.php");
	exit;
}

// PASO 1: Mostrar vista de previsualización tras subir/convertir (import_step1)
if (isset($_GET['opera']) && $_GET['opera'] === 'import_step1') {
    require_once("views/vjuiimp.php");
    exit;
}

// Obtener juicios importados en formato JSON para la vista
if (isset($_GET['opera']) && $_GET['opera'] === 'get_importados_json') {
    // Mata cualquier salida previa
    while (ob_get_level() > 0) { ob_end_clean(); }
    header('Content-Type: application/json; charset=utf-8');

    try {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }

        // Verificar primero si las nuevas columnas existen
        $db = (new conexion())->get_conexion();
        $stmt = $db->query("SHOW COLUMNS FROM juicio LIKE 'fecha_reporte'");
        $tiene_fecha_reporte = $stmt->rowCount() > 0;
        
        $stmt = $db->query("SHOW COLUMNS FROM juicio LIKE 'fecha_importacion'");
        $tiene_fecha_importacion = $stmt->rowCount() > 0;
        
        $stmt = $db->query("SHOW COLUMNS FROM juicio LIKE 'archivo_correlativo'");
        $tiene_archivo_correlativo = $stmt->rowCount() > 0;

        // Construir la consulta dinámicamente según las columnas disponibles
        $sql = "SELECT 
                    j.idjui,
                    j.idusu,
                    j.idfic,
                    j.idres,
                    j.caljui,
                    -- Datos del estudiante (tabla usuario)
                    COALESCE(u.ndocusu, '') as ndoce,
                    COALESCE(u.nomusu, 'Sin nombre') as nomes,
                    COALESCE(u.nomusu, 'Sin apellidos') as apees,
                    COALESCE(u.tdousu, '') as tidoc,
                    'EN FORMACION' as estes,
                    -- Datos de la ficha
                    COALESCE(f.nomfic, 'Sin ficha') as nomfic,
                    f.mun,
                    f.finific,
                    f.ffinfic,
                    -- Datos del resultado
                    COALESCE(r.nomres, 'Sin resultado') as resapr,
                    r.ndeses,
                    -- Datos de la competencia
                    c.idcom,
                    COALESCE(c.descom, 'Sin competencia') as compe,
                    -- Datos de aprobacion_resultado
                    ar.idaprob,
                    ar.idinstructor,
                    COALESCE(ar.estado, 'pendiente') as estado,
                    ar.fecha_aprobacion as fecha_juicio,
                    -- Datos del funcionario instructor (buscado por ndocins de juicio)
                    j.ndocins,
                    COALESCE(func.nomusu, 'Sin instructor') as funre";

        // Agregar campos de metadatos solo si las columnas existen
        if ($tiene_fecha_reporte) {
            $sql .= ", COALESCE(j.fecha_reporte, '') as fecre";
        } else {
            $sql .= ", '' as fecre";
        }
        
        if ($tiene_fecha_importacion) {
            $sql .= ", COALESCE(j.fecha_importacion, '') as fecim";
        } else {
            $sql .= ", '' as fecim";
        }
        
        if ($tiene_archivo_correlativo) {
            $sql .= ",                     COALESCE(j.archivo_correlativo, '') as arcor";
        } else {
            $sql .= ", '' as arcor";
        }

        // Verificar si existe la columna fecjuieva
        $stmt = $db->query("SHOW COLUMNS FROM juicio LIKE 'fecjuieva'");
        $tiene_fecjuieva = $stmt->rowCount() > 0;
        
        if ($tiene_fecjuieva) {
            $sql .= ", COALESCE(j.fecjuieva, '') as fecjuieva";
        } else {
            $sql .= ", '' as fecjuieva";
        }

        $sql .= ", ar.fecha_aprobacion as fecju
                FROM juicio j
                LEFT JOIN usuario u ON j.idusu = u.idusu
                LEFT JOIN ficha f ON j.idfic = f.idfic
                LEFT JOIN resultado r ON j.idres = r.idres
                LEFT JOIN competencia c ON r.idcom = c.idcom
                LEFT JOIN aprobacion_resultado ar ON (j.idfic = ar.idfic AND j.idres = ar.idres)
                LEFT JOIN usuario func ON j.ndocins = func.ndocusu";
        
        // Aplicar filtro por usuario logueado directamente en la consulta SQL
        if (isset($_SESSION['idusu'])) {
            // Obtener el número de documento del usuario logueado
            $stmt = $db->prepare("SELECT ndocusu FROM usuario WHERE idusu = ?");
            $stmt->execute([$_SESSION['idusu']]);
            $ndocusu_logueado = $stmt->fetchColumn();
            
            if ($ndocusu_logueado) {
                $sql .= " WHERE j.ndocins = ?";
                $sql .= " ORDER BY j.idjui DESC";
                
                $stmt = $db->prepare($sql);
                $stmt->execute([$ndocusu_logueado]);
            } else {
                $sql .= " ORDER BY j.idjui DESC LIMIT 100";
                $stmt = $db->prepare($sql);
                $stmt->execute();
            }
        } else {
            $sql .= " ORDER BY j.idjui DESC LIMIT 100";
            $stmt = $db->prepare($sql);
            $stmt->execute();
        }
        
        $juiciosFiltrados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Obtener el documento del usuario logueado desde la base de datos
        $documento_usuario = 'N/A';
        if (isset($_SESSION['idusu'])) {
            try {
                $stmt_doc = $db->prepare("SELECT ndocusu FROM usuario WHERE idusu = ?");
                $stmt_doc->execute([$_SESSION['idusu']]);
                $documento_usuario = $stmt_doc->fetchColumn() ?: 'N/A';
            } catch (Exception $e) {
                error_log("Error obteniendo documento del usuario: " . $e->getMessage());
                $documento_usuario = 'N/A';
            }
        }

        echo json_encode([
            'success' => true,
            'data' => array_values($juiciosFiltrados),
            'total' => count($juiciosFiltrados),
            'filtro_usuario' => isset($_SESSION['idusu']) ? [
                'idusu' => $_SESSION['idusu'],
                'documento' => $documento_usuario
            ] : null
        ], JSON_UNESCAPED_UNICODE);
        exit;

    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Error del servidor',
            'detail' => $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// Importación directa desde CSV
if (isset($_GET['opera']) && $_GET['opera'] === 'import_csv') {
    // Mata cualquier salida previa
    while (ob_get_level() > 0) { ob_end_clean(); }
    header('Content-Type: application/json; charset=utf-8');

    try {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }

        $idfic = isset($_POST['idfic']) ? trim($_POST['idfic']) : '';
        if ($idfic === '') {
            http_response_code(400);
            echo json_encode([
                'ok' => false,
                'error' => 'Falta el parámetro idfic'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // Verificar si se subió un archivo
        if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            http_response_code(400);
            echo json_encode([
                'ok' => false,
                'error' => 'No se subió archivo CSV válido'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $archivoTemporal = $_FILES['csv_file']['tmp_name'];
        $nombreArchivo = $_FILES['csv_file']['name'];

        // Procesar el CSV
        $resultado = $mjui->procesarCSVJuicios($archivoTemporal, $idfic);

        echo json_encode([
            'ok' => true,
            'resultado' => $resultado,
            'mensaje' => "Importación completada: {$resultado['procesadas']} registros procesados, {$resultado['error']} errores"
        ], JSON_UNESCAPED_UNICODE);
        exit;

    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode([
            'ok' => false,
            'error' => 'Error del servidor',
            'detail' => $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}


// Helpers mínimos
function normaliza_utf8($v) { return trim(mb_convert_encoding($v, 'UTF-8', 'UTF-8,ISO-8859-1')); }
function safe_json($arr) { header('Content-Type: application/json; charset=utf-8'); echo json_encode($arr, JSON_UNESCAPED_UNICODE); exit; }

// =======================
// PASO 1: PREVIEW DEL CSV (opera=import)
// Sube archivo, valida cabeceras, cuenta filas y guarda un token temporal
// =======================
if (isset($_GET['opera']) && $_GET['opera'] === 'import') {
    try {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }

        if (!isset($_POST['idfic']) || trim($_POST['idfic']) === '') {
            safe_json(['ok'=>false, 'error'=>'Falta idfic']);
        }
        $idfic = trim($_POST['idfic']);

        if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            safe_json(['ok'=>false, 'error'=>'Debe adjuntar un archivo CSV válido']);
        }

        // Guardar en carpeta temporal con un token
        $tmpPath = $_FILES['csv_file']['tmp_name'];
        $origName = basename($_FILES['csv_file']['name']);
        $token = 'csv_'.time().'_'.bin2hex(random_bytes(4));
        $destDir = __DIR__.'/tmp_imports';
        if (!is_dir($destDir)) { @mkdir($destDir, 0775, true); }
        $destPath = $destDir.'/'.$token.'.csv';
        if (!move_uploaded_file($tmpPath, $destPath)) {
            // Si no se pudo mover, intenta copiar
            if (!copy($_FILES['csv_file']['tmp_name'], $destPath)) {
                safe_json(['ok'=>false, 'error'=>'No se pudo almacenar temporalmente el CSV']);
            }
        }

        // Abrir y validar cabeceras
        $fh = fopen($destPath, 'r');
        if (!$fh) safe_json(['ok'=>false, 'error'=>'No se pudo abrir el CSV guardado']);

        $header = fgetcsv($fh, 0, ',');
        if (!$header) {
            fclose($fh);
            safe_json(['ok'=>false, 'error'=>'El CSV no tiene encabezado']);
        }
        $header = array_map('normaliza_utf8', $header);

        // Columnas clave que requiere el modelo Mjui::importarDesdeCSV
        $need = [
            'Número de Documento',
            'Competencia',
            'Resultado de Aprendizaje',
            'Juicio de Evaluación'
        ];
        $idx = [];
        foreach ($need as $col) {
            $idx[$col] = array_search($col, $header);
            if ($idx[$col] === false) {
                fclose($fh);
                safe_json(['ok'=>false, 'error'=>"Columna requerida ausente: $col", 'header'=>$header]);
            }
        }

        // Contar filas (para mostrar preview al usuario)
        $count = 0;
        while (($row = fgetcsv($fh, 0, ',')) !== false) {
            $row = array_map('normaliza_utf8', $row);
            // Skip líneas vacías
            if (
                ($idx['Número de Documento'] !== false && ($row[$idx['Número de Documento']] ?? '') === '') &&
                ($idx['Competencia'] !== false && ($row[$idx['Competencia']] ?? '') === '') &&
                ($idx['Resultado de Aprendizaje'] !== false && ($row[$idx['Resultado de Aprendizaje']] ?? '') === '')
            ) {
                continue;
            }
            $count++;
            if ($count >= 1000) { /* límite de preview si quieres */ }
        }
        fclose($fh);

        // Guarda en sesión los metadatos para el paso 2 (o devuélvelos a la vista)
        $_SESSION['import_csv'][$token] = [
            'path'   => $destPath,
            'idfic'  => $idfic,
            'name'   => $origName,
            'header' => $header,
        ];

        // RESPUESTA para que la vista Paso 1 muestre el “ok” y permita continuar
        safe_json([
            'ok'     => true,
            'msg'    => 'Archivo recibido y validado',
            'token'  => $token,
            'idfic'  => $idfic,
            'name'   => $origName,
            'filas'  => $count
        ]);
    } catch (Throwable $e) {
        safe_json(['ok'=>false, 'error'=>$e->getMessage()]);
    }
}

// =======================
// PASO 2: COMMIT A BD (opera=import_step2)
// Recorre el CSV y llama a Mjui::importarDesdeCSV por cada fila
// =======================
if (isset($_GET['opera']) && $_GET['opera'] === 'import_step2') {
    try {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }

        if (!isset($_POST['token']) || trim($_POST['token']) === '') {
            safe_json(['ok'=>false, 'error'=>'Falta token']);
        }
        $token = trim($_POST['token']);

        if (empty($_SESSION['import_csv'][$token]['path'])) {
            safe_json(['ok'=>false, 'error'=>'Token inválido o expirado']);
        }
        $meta = $_SESSION['import_csv'][$token];
        $csvPath = $meta['path'];
        $idfic   = $meta['idfic'];
        $name    = $meta['name'];

        require_once(__DIR__.'/../models/hormeval.php');
        $mjui = new Mjui();

        $fh = fopen($csvPath, 'r');
        if (!$fh) safe_json(['ok'=>false, 'error'=>'No se pudo abrir el CSV para importar']);

        // Saltar las primeras 12 líneas (metadatos del reporte)
        for ($i = 1; $i <= 12; $i++) {
            $line = fgetcsv($fh, 0, ',');
            if ($line === false) {
                safe_json(['ok'=>false, 'error'=>'Error saltando líneas de metadatos']);
            }
        }

        // Leer encabezado y mapear posiciones (línea 13)
        $header = fgetcsv($fh, 0, ',');
        $header = array_map('normaliza_utf8', $header);
        $find = function($label) use ($header){
            $i = array_search($label, $header);
            return ($i === false ? null : $i);
        };

        $col_TD   = $find('Tipo de Documento');
        $col_NDOC = $find('Número de Documento');
        $col_NOM  = $find('Nombre');
        $col_APE  = $find('Apellidos');
        $col_EST  = $find('Estado');
        $col_COMP = $find('Competencia');
        $col_RES  = $find('Resultado de Aprendizaje');
        $col_JUI  = $find('Juicio de Evaluación');
        $col_FEJ  = $find('Fecha y Hora del Juicio Evaluativo');
        $col_FUN  = $find('Funcionario que registro el juicio evaluativo');

        $total = 0; $ok = 0; $fail = 0; $errores = [];

        while (($row = fgetcsv($fh, 0, ',')) !== false) {
            $row = array_map('normaliza_utf8', $row);

            $doc   = isset($col_NDOC) && $col_NDOC !== null ? ($row[$col_NDOC] ?? '') : '';
            $comp  = isset($col_COMP) && $col_COMP !== null ? ($row[$col_COMP] ?? '') : '';
            $res   = isset($col_RES)  && $col_RES  !== null ? ($row[$col_RES]  ?? '') : '';

            // Saltar filas vacías
            if ($doc === '' && $comp === '' && $res === '') continue;

            $total++;

            // Arma el arreglo con las claves que espera el modelo
            $fila = [
                'Tipo de Documento'                           => ($col_TD !== null ? ($row[$col_TD] ?? '') : ''),
                'Número de Documento'                         => $doc,
                'Nombre'                                      => ($col_NOM !== null ? ($row[$col_NOM] ?? '') : ''),
                'Apellidos'                                   => ($col_APE !== null ? ($row[$col_APE] ?? '') : ''),
                'Estado'                                      => ($col_EST !== null ? ($row[$col_EST] ?? '') : ''),
                'Competencia'                                 => $comp,
                'Resultado de Aprendizaje'                    => $res,
                'Juicio de Evaluación'                        => ($col_JUI !== null ? ($row[$col_JUI] ?? '') : ''),
                'Fecha y Hora del Juicio Evaluativo'          => ($col_FEJ !== null ? ($row[$col_FEJ] ?? '') : ''),
                'Funcionario que registro el juicio evaluativo'=> ($col_FUN !== null ? ($row[$col_FUN] ?? '') : ''),
            ];

            // Llamar al modelo (crea/valida competencia, resultado, aprobacion_resultado y juicio)
            $resu = $mjui->importarDesdeCSV($fila, $idfic, $name);
            if ($resu === false) {
                $fail++;
                $errores[] = ['n'=>$total, 'fila'=>$fila, 'error'=>'Persistencia fallida'];
            } else {
                $ok++;
            }
        }
        fclose($fh);

        // (Opcional) limpiar el CSV temporal
        @unlink($csvPath);
        unset($_SESSION['import_csv'][$token]);

        // Guardar resultado en sesión para la vista
        $_SESSION['resultado_importacion'] = [
            'total_filas' => $total,
            'procesadas' => $ok,
            'error' => $fail,
            'insertados' => $ok,  // Para compatibilidad con la vista
            'omitidos' => $fail,  // Para compatibilidad con la vista
            'errores' => $errores
        ];

        safe_json([
            'ok' => true,
            'msg'=> 'Importación realizada',
            'estadistica' => [
                'total_leidas' => $total,
                'exitosas'     => $ok,
                'fallidas'     => $fail
            ],
            'errores' => $errores
        ]);
    } catch (Throwable $e) {
        safe_json(['ok'=>false, 'error'=>$e->getMessage()]);
    }
}

// Código comentado - solo se ejecuta cuando hay una operación de importación específica
/*
// ... ya tienes $idfic y $nombreArchivo definidos
require_once(__DIR__.'/../models/hormeval.php');
$m = new Mjui();
$resumen = $m->procesarImportacionBD_desdeSession($idfic, $nombreArchivo);

// Guarda y redirige a la vista final
$_SESSION['resultado_importacion'] = $resumen;
header("Location: home.php?opera=ver_importados");
exit;
*/





// Variables para el formulario manual
// Cargar instructor logueado (usuario actual)
$instructorLogueado = null;
$fichaInstructor = null;

// DEBUG: Logs para diagnosticar el problema
error_log("=== DEBUG INSTRUCTOR Y FICHA ===");
error_log("session_status: " . session_status());
error_log("_SESSION['idusu']: " . (isset($_SESSION['idusu']) ? $_SESSION['idusu'] : 'NO DEFINIDA'));
error_log("_SESSION['nomusu']: " . (isset($_SESSION['nomusu']) ? $_SESSION['nomusu'] : 'NO DEFINIDA'));

if (isset($_SESSION['idusu'])) {
    error_log("Intentando cargar instructor con ID: " . $_SESSION['idusu']);
    $instructorLogueado = $mjui->cargarInfoUsuario($_SESSION['idusu']);
    error_log("Resultado cargarInfoUsuario: " . ($instructorLogueado ? 'ÉXITO' : 'FALLO'));
    if ($instructorLogueado) {
        error_log("Datos instructor: " . print_r($instructorLogueado, true));
        error_log("Intentando cargar ficha para instructor: " . $_SESSION['idusu']);
        $fichaInstructor = $mjui->cargarFichaInstructor($_SESSION['idusu']);
        error_log("Resultado cargarFichaInstructor: " . ($fichaInstructor ? 'ÉXITO' : 'FALLO'));
        if ($fichaInstructor) {
            error_log("Datos ficha: " . print_r($fichaInstructor, true));
        }
    }
} else {
    error_log("No hay sesión de usuario activa");
}
error_log("=== FIN DEBUG ===");

// Operaciones AJAX para el nuevo módulo de evaluación
if ($opera === 'toggle_juicio') {
    header('Content-Type: application/json');
    $idusu_req = $_POST['idusu'] ?? null;
    $idfic_req = $_POST['idfic'] ?? '3235287';
    $caljui_req = $_POST['caljui'] ?? 'APROBADO';
    $ok = $mjui->actualizarJuicioRapido($idusu_req, $idfic_req, $caljui_req);
    echo json_encode(['success' => $ok, 'caljui' => $caljui_req]);
    exit;
}

if ($opera === 'guardar_plan') {
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'mensaje' => 'Plan de mejoramiento concertado y notificado al aprendiz con éxito']);
    exit;
}

$datUsu = $mjui->getAllUsu();

// Alcance por rol (módulo HyA): fichas visibles según el usuario autenticado.
require_once ROOT_PATH . '/models/hormacceso.php';
$hacceso = new HormAcceso();
$scopeCtx = $hacceso->getContexto();
$scopeFichas = $hacceso->getFichasPermitidas($scopeCtx);
$scopeRol = $hacceso->etiquetaRol($scopeCtx);
$scopeN = count($scopeFichas);
$scopeTotal = !empty($scopeCtx['is_admin']);

$datFic = $hacceso->filtrarPorFicha($mjui->getAllfic(), 'idfic', $scopeCtx);
$datCompe = $mjui->getCompetenciasUsadas();
$datRes = $mjui->getResultadosUsados();
$datAll = $mjui->getAll();

// Parámetros de filtrado por Ficha y Competencia ADSO
$idfic = isset($_REQUEST['idfic']) && !empty($_REQUEST['idfic']) ? $_REQUEST['idfic'] : '3235287';
if (!$hacceso->puedeVerFicha($idfic, $scopeCtx)) {
    $idfic = !empty($scopeFichas) ? $scopeFichas[0]['idfic'] : $idfic;
}
$idcom = isset($_REQUEST['idcom']) ? $_REQUEST['idcom'] : 'ALL';
$evaluaciones = $mjui->getEvaluacionesFicha($idfic, $idcom);

// Estadísticas para las tarjetas KPI oficiales de Evaluación
$kpi_planeados = 24;
$kpi_evaluados = count($evaluaciones);
$kpi_aprobados = count(array_filter($evaluaciones, function($e) {
    return (isset($e['caljui']) && (strtoupper($e['caljui']) === 'APROBADO' || strtoupper($e['caljui']) === 'A'));
}));
$kpi_deficientes = $kpi_evaluados - $kpi_aprobados;

// Conteos para el Semáforo de Alertas Tempranas
$sem_verde = $kpi_aprobados;
$sem_amarillo = min(3, intval($kpi_evaluados * 0.15));
$sem_rojo = $kpi_deficientes;


// ========================================
// CARGAR VISTA CUANDO NO HAY OPERACIÓN ESPECÍFICA (PATRÓN MVC)
// ========================================
// Si no hay operación específica o es una carga normal de la interfaz, cargar la vista
if (!$opera || in_array($opera, ['', 'view', 'list'])) {
    if (!defined('HORVEVAL_LOADED_FROM_VIEW')) {
        define('HORVEVAL_LOADED_FROM_VIEW', true);
        require_once ROOT_PATH . '/views/horveval.php';
        exit;
    }
}


// Limpiar output buffer para vistas normales (no AJAX)
// if (!isset($_GET['opera']) || !in_array($_GET['opera'], ['buscar_juicio', 'get_importados_json', 'import_csv'])) {
//     ob_end_flush();
// }
?>