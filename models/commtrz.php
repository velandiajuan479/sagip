<?php
require_once("conexion.php");

/**
 * Modelo de Trazabilidad y Flujo de Aprobación - SAGI SENA
 * Módulo 2016
 * 
 * Gestiona el historial auditado y el flujo de estados de las Hojas de Trabajo (módulo 2004).
 * Estructura de tabla: trazabilidad_hojatra
 * Flujo de 4 Pasos:
 * 1: Creación / Corrección (Instructor)
 * 2: Revisión (Coordinación)
 * 3: Viabilidad y Registro (Administración Educativa)
 * 4: Aprobación Final (Relaciones Corporativas)
 * 
 * Modo demostración: cuando la tabla hojatra está vacía se ofrecen hojas de ejemplo
 * (radicados 900001..900006) que viven SOLO en sesión y NUNCA se escriben en la BD.
 */
class Commtrz {
	private $conexion;

	/** Radicados reservados para el modo demostración (no existen en la BD). */
	private static $DEMO_IDS = [900001, 900002, 900003, 900004, 900005, 900006];

	/** Perfiles con acceso administrativo completo al flujo (bypass de paso). */
	private static $ROL_ADMIN = [1, 2, 25];

	/** Perfiles habilitados para actuar en cada paso del flujo. */
	private static $ROL_PASO = [
		1 => [42],          // Comple. Instructor
		2 => [43],          // Comple. Coordinador
		3 => [44, 46],      // Comple. Admon. Educativa / Gestor Admon Edu.
		4 => [45]           // Comple. Relaciones Corporativas
	];

	public function __construct() {
		$modelo = new conexion();
		$this->conexion = $modelo->get_conexion();
		$this->asegurarEstructuraBD();
	}

	/**
	 * Asegura que la tabla trazabilidad_hojatra y las columnas requeridas en hojatra existan.
	 */
	private function asegurarEstructuraBD() {
		try {
			// Crear tabla de trazabilidad si no existe
			$sqlTabla = "CREATE TABLE IF NOT EXISTS trazabilidad_hojatra (
				id_trazabilidad INT AUTO_INCREMENT PRIMARY KEY,
				id_hojatra BIGINT(15) NOT NULL,
				paso_flujo INT NOT NULL DEFAULT 1,
				id_usuario BIGINT(20) NULL,
				rol_usuario VARCHAR(100) NULL,
				accion VARCHAR(60) NOT NULL,
				observacion TEXT NULL,
				fecha_registro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
				INDEX (id_hojatra),
				INDEX (paso_flujo)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
			$this->conexion->exec($sqlTabla);

			// Verificar columnas en hojatra
			$cols = $this->conexion->query("SHOW COLUMNS FROM hojatra")->fetchAll(PDO::FETCH_COLUMN);
			if (!in_array('paso_actual', $cols)) {
				$this->conexion->exec("ALTER TABLE hojatra ADD COLUMN paso_actual INT NOT NULL DEFAULT 1");
			}
			if (!in_array('estado', $cols)) {
				$this->conexion->exec("ALTER TABLE hojatra ADD COLUMN estado VARCHAR(60) NOT NULL DEFAULT 'En Revisión (Instructor)'");
			}
			if (!in_array('devuelto_de', $cols)) {
				$this->conexion->exec("ALTER TABLE hojatra ADD COLUMN devuelto_de INT NULL DEFAULT NULL");
			}
		} catch (Exception $e) {
			if (function_exists('ManejoError')) ManejoError($e);
		}
	}

	/* ======================================================================
	 * MODO DEMOSTRACIÓN (en memoria / sesión — nunca toca la BD)
	 * ====================================================================== */

	/** @return bool Verdadero si el radicado pertenece al set de demostración. */
	public function esDemo($id) {
		return in_array((int)$id, self::$DEMO_IDS, true);
	}

	/** @return bool Verdadero si la tabla hojatra real no tiene filas. */
	public function esHojatraVacia() {
		try {
			$n = (int)$this->conexion->query("SELECT COUNT(*) FROM hojatra")->fetchColumn();
			return $n === 0;
		} catch (Exception $e) {
			return true;
		}
	}

	/** Datos base de las hojas de ejemplo (solo lectura). */
	private function baseDemo() {
		return [
			900001 => [
				'codpro' => 322522, 'nompro' => 'Análisis y Desarrollo de Software', 'horlpro' => 2304000,
				'idemp' => 10001, 'nomemp' => 'Alpha Tecnología SAS', 'numdocemp' => '900123456-7', 'nomconemp' => 'CAROLINA PEÑA GÓMEZ', 'telemp' => '3015551101',
				'codproesp' => 4504, 'proesp' => 'Diurno', 'codslem' => 'HT1-2026', 'feclini' => '2026-01-19', 'feclin' => '2026-12-18', 'cupo' => 30,
				'jornada' => '5', 'nomjornada' => 'Mañana', 'idfic' => '2345678', 'nomfic' => 'Ficha 2345678', 'convht' => '12345',
				'paso_actual' => 4, 'estado' => 'Aprobado Final / Cerrado', 'devuelto_de' => null,
				'nombre_instructor' => 'LAURA CELIS ORTIZ'
			],
			900002 => [
				'codpro' => 228106, 'nompro' => 'Sistemas', 'horlpro' => 2640000,
				'idemp' => 10002, 'nomemp' => 'Constructora Horizonte Ltda.', 'numdocemp' => '800654321-1', 'nomconemp' => 'JORGE ANDRÉS RUEDA', 'telemp' => '3124448877',
				'codproesp' => 4505, 'proesp' => 'Nocturna', 'codslem' => 'HT2-2026', 'feclini' => '2026-03-02', 'feclin' => '2027-03-01', 'cupo' => 25,
				'jornada' => '6', 'nomjornada' => 'Noche', 'idfic' => '2456789', 'nomfic' => 'Ficha 2456789', 'convht' => '24680',
				'paso_actual' => 3, 'estado' => 'En Revisión (Administración Educativa)', 'devuelto_de' => null,
				'nombre_instructor' => 'CARLOS EMILIO BARRERA'
			],
			900003 => [
				'codpro' => 226223, 'nompro' => 'Contabilidad y Finanzas', 'horlpro' => 2304000,
				'idemp' => 10003, 'nomemp' => 'Inversiones Delta S.A.', 'numdocemp' => '900987654-3', 'nomconemp' => 'MARTHA LUCÍA PINZÓN', 'telemp' => '3182229900',
				'codproesp' => 4504, 'proesp' => 'Diurno', 'codslem' => 'HT3-2026', 'feclini' => '2026-02-10', 'feclin' => '2027-02-09', 'cupo' => 40,
				'jornada' => '5', 'nomjornada' => 'Mañana', 'idfic' => '2567890', 'nomfic' => 'Ficha 2567890', 'convht' => '13579',
				'paso_actual' => 1, 'estado' => 'Devuelto por Coordinación', 'devuelto_de' => 2,
				'nombre_instructor' => 'ANDREA MILENA CASTELLANOS'
			],
			900004 => [
				'codpro' => 216619, 'nompro' => 'Gestión Administrativa', 'horlpro' => 2112000,
				'idemp' => 10004, 'nomemp' => 'Hospital San Carlos ESE', 'numdocemp' => '899999001-5', 'nomconemp' => 'NELLY SOFÍA RAMÍREZ', 'telemp' => '3107778899',
				'codproesp' => 4505, 'proesp' => 'Nocturna', 'codslem' => 'HT4-2026', 'feclini' => '2026-04-13', 'feclin' => '2027-04-12', 'cupo' => 20,
				'jornada' => '6', 'nomjornada' => 'Noche', 'idfic' => '2678901', 'nomfic' => 'Ficha 2678901', 'convht' => '97531',
				'paso_actual' => 2, 'estado' => 'En Revisión (Coordinación)', 'devuelto_de' => null,
				'nombre_instructor' => 'WILLIAM FERNANDO SÁNCHEZ'
			],
			900005 => [
				'codpro' => 322522, 'nompro' => 'Análisis y Desarrollo de Software', 'horlpro' => 2304000,
				'idemp' => 10005, 'nomemp' => 'Logística Global Cargo', 'numdocemp' => '901234567-8', 'nomconemp' => 'FELIPE ANDRÉS MAHECHA', 'telemp' => '3203334455',
				'codproesp' => 4504, 'proesp' => 'Diurno', 'codslem' => 'HT5-2026', 'feclini' => '2026-01-05', 'feclin' => '2027-01-04', 'cupo' => 35,
				'jornada' => '5', 'nomjornada' => 'Mañana', 'idfic' => '2789012', 'nomfic' => 'Ficha 2789012', 'convht' => '86420',
				'paso_actual' => 1, 'estado' => 'Devuelto por Relaciones Corporativas', 'devuelto_de' => 4,
				'nombre_instructor' => 'PAOLA ANDREA FONSECA'
			],
			900006 => [
				'codpro' => 226210, 'nompro' => 'Talento Humano', 'horlpro' => 2112000,
				'idemp' => 10006, 'nomemp' => 'Corporación Cultural Vida', 'numdocemp' => '800123456-2', 'nomconemp' => 'RICHARD STEVEN ACOSTA', 'telemp' => '3156667788',
				'codproesp' => 4504, 'proesp' => 'Diurno', 'codslem' => 'HT6-2026', 'feclini' => '2026-05-11', 'feclin' => '2027-05-10', 'cupo' => 15,
				'jornada' => '5', 'nomjornada' => 'Mañana', 'idfic' => null, 'nomfic' => null, 'convht' => '11223',
				'paso_actual' => 1, 'estado' => 'En Revisión (Instructor)', 'devuelto_de' => null,
				'nombre_instructor' => 'JHON SEBASTIÁN PRIETO'
			],
		];
	}

	/**
	 * Devuelve el estado actual de una hoja demo (base + avance de la sesión).
	 */
	private function estadoDemo($id) {
		$base = $this->baseDemo()[(int)$id] ?? null;
		if (!$base) return null;
		$st = $_SESSION['mhr_demo_flujo'][(int)$id] ?? null;
		if ($st && is_array($st)) {
			foreach (['paso_actual', 'estado', 'devuelto_de'] as $k) {
				if (array_key_exists($k, $st)) $base[$k] = $st[$k];
			}
		}
		return $base;
	}

	/** Listado de hojas de ejemplo para selector y monitoreo. */
	public function getHojasDemo() {
		$out = [];
		foreach (self::$DEMO_IDS as $id) {
			$d = $this->estadoDemo($id);
			if (!$d) continue;
			$out[] = [
				'idnorad' => $id,
				'codpro' => $d['codpro'], 'nompro' => $d['nompro'],
				'idemp' => $d['idemp'], 'nomemp' => $d['nomemp'],
				'feclini' => $d['feclini'], 'feclin' => $d['feclin'], 'cupo' => $d['cupo'],
				'idfic' => $d['idfic'],
				'paso_actual' => $d['paso_actual'], 'estado' => $d['estado'], 'devuelto_de' => $d['devuelto_de']
			];
		}
		return $out;
	}

	/**
	 * Movimientos base (solo lectura) de cada hoja demo, ordenados de más reciente a más antiguo.
	 */
	private function demoHistBase($id) {
		$b = $this->baseDemo()[(int)$id] ?? null;
		if (!$b) return [];
		$ins = $b['nombre_instructor'];
		$h = [];
		$h[] = ['a' => 'Hoja de Trabajo creada', 'p' => 1, 'u' => $ins, 'r' => 'Comple. Instructor', 'o' => 'Registro inicial de la solicitud de formación complementaria.', 'd' => 18];
		$h[] = ['a' => 'Enviado a Coordinación', 'p' => 1, 'u' => $ins, 'r' => 'Comple. Instructor', 'o' => 'Solicitud enviada para revisión académica.', 'd' => 15];

		if ($id === 900001) {
			$h[] = ['a' => 'Aprobado por Coordinación', 'p' => 2, 'u' => 'FABIAN ANDRÉS MURCIA', 'r' => 'Comple. Coordinador', 'o' => 'Viabilidad académica verificada favorablemente.', 'd' => 12];
			$h[] = ['a' => 'Aprobado por Administración Educativa', 'p' => 3, 'u' => 'DIANA MARCELA LEÓN', 'r' => 'Comple. Admon. Educativa', 'o' => 'Registro de ficha SOFIA Plus confirmado.', 'd' => 8];
			$h[] = ['a' => 'Aprobación Final Concedida', 'p' => 4, 'u' => 'OSCAR MAURICIO DAZA', 'r' => 'Comple. Relaciones Corporativas', 'o' => 'Acuerdo de voluntariado y programación validados. Hoja cerrada.', 'd' => 3];
		} elseif ($id === 900002) {
			$h[] = ['a' => 'Aprobado por Coordinación', 'p' => 2, 'u' => 'FABIAN ANDRÉS MURCIA', 'r' => 'Comple. Coordinador', 'o' => 'Revisión académica aprobada.', 'd' => 5];
		} elseif ($id === 900003) {
			$h[] = ['a' => 'Devuelto por Coordinación', 'p' => 2, 'u' => 'FABIAN ANDRÉS MURCIA', 'r' => 'Comple. Coordinador', 'o' => 'Cupo solicitado no corresponde con la capacidad del programa.', 'd' => 4];
		} elseif ($id === 900004) {
			// en revisión de Coordinación
		} elseif ($id === 900005) {
			$h[] = ['a' => 'Aprobado por Coordinación', 'p' => 2, 'u' => 'FABIAN ANDRÉS MURCIA', 'r' => 'Comple. Coordinador', 'o' => 'Revisión académica aprobada.', 'd' => 10];
			$h[] = ['a' => 'Aprobado por Administración Educativa', 'p' => 3, 'u' => 'DIANA MARCELA LEÓN', 'r' => 'Comple. Admon. Educativa', 'o' => 'Ficha asignada en SOFIA Plus.', 'd' => 6];
			$h[] = ['a' => 'Devuelto por Relaciones Corporativas', 'p' => 4, 'u' => 'OSCAR MAURICIO DAZA', 'r' => 'Comple. Relaciones Corporativas', 'o' => 'Falta convenio firmado con la empresa solicitante.', 'd' => 2];
		} elseif ($id === 900006) {
			// hoja nueva recién creada
		}

		$rows = [];
		foreach ($h as $mov) {
			$rows[] = [
				'paso_flujo' => $mov['p'], 'id_usuario' => null, 'rol_usuario' => $mov['r'],
				'accion' => $mov['a'], 'observacion' => $mov['o'],
				'fecha_registro' => date('Y-m-d H:i:s', strtotime("-{$mov['d']} days")),
				'nomusu' => $mov['u'], 'ndocusu' => null, 'emausu' => null
			];
		}
		// Más reciente primero
		usort($rows, function ($x, $y) { return strcmp($y['fecha_registro'], $x['fecha_registro']); });
		return $rows;
	}

	/**
	 * Historial completo de una hoja demo (base + movimientos de la sesión).
	 */
	private function demoHistTotal($id) {
		$rows = $this->demoHistBase($id);
		if (!empty($_SESSION['mhr_demo_hist'][(int)$id]) && is_array($_SESSION['mhr_demo_hist'][(int)$id])) {
			foreach (array_reverse($_SESSION['mhr_demo_hist'][(int)$id]) as $mv) {
				$rows[] = $mv;
			}
		}
		usort($rows, function ($x, $y) { return strcmp($y['fecha_registro'], $x['fecha_registro']); });
		return array_slice($rows, 0, 25);
	}

	/**
	 * Registra el avance de una hoja demo dentro de la sesión (NO toca la BD).
	 * @param int $idnorad
	 * @param int $nuevoPaso
	 * @param string $nuevoEstado
	 * @param int|null $devueltoDe
	 * @param array $mov [paso_flujo, id_usuario, rol_usuario, accion, observacion, nomusu]
	 * @return true
	 */
	public function procesarFlujoDemo($idnorad, $nuevoPaso, $nuevoEstado, $devueltoDe = null, $mov = []) {
		$id = (int)$idnorad;
		if (!isset($_SESSION['mhr_demo_flujo']) || !is_array($_SESSION['mhr_demo_flujo'])) $_SESSION['mhr_demo_flujo'] = [];
		$_SESSION['mhr_demo_flujo'][$id] = [
			'paso_actual' => (int)$nuevoPaso,
			'estado' => $nuevoEstado,
			'devuelto_de' => ($devueltoDe !== null && $devueltoDe !== '') ? (int)$devueltoDe : null
		];

		if (!isset($_SESSION['mhr_demo_hist']) || !is_array($_SESSION['mhr_demo_hist'])) $_SESSION['mhr_demo_hist'] = [];
		$_SESSION['mhr_demo_hist'][$id][] = [
			'paso_flujo' => isset($mov['paso_flujo']) ? (int)$mov['paso_flujo'] : (int)$nuevoPaso,
			'id_usuario' => !empty($mov['id_usuario']) ? (int)$mov['id_usuario'] : null,
			'rol_usuario' => $mov['rol_usuario'] ?? 'Funcionario SENA',
			'accion' => $mov['accion'] ?? 'Registro',
			'observacion' => !empty($mov['observacion']) ? trim($mov['observacion']) : null,
			'fecha_registro' => date('Y-m-d H:i:s'),
			'nomusu' => $mov['nomusu'] ?? ($_SESSION['nomusu'] ?? 'Usuario Demo'),
			'ndocusu' => null, 'emausu' => null
		];
		if (count($_SESSION['mhr_demo_hist'][$id]) > 25) {
			$_SESSION['mhr_demo_hist'][$id] = array_slice($_SESSION['mhr_demo_hist'][$id], -25);
		}
		return true;
	}

	/** Conteos (KPIs) calculados sobre el set de demostración. */
	public function getConteosDemo() {
		$total = $fre = 0; $en = $dev = $fin = $mov = 0;
		foreach ($this->getHojasDemo() as $h) {
			$state = $h['estado'];
			$esDev = (stripos($state, 'Devuelto') !== false || stripos($state, 'Rechazado') !== false);
			if ((int)$h['paso_actual'] === 4 && stripos($state, 'Aprobado') !== false) {
				$fin++;
			} elseif ($esDev) {
				$dev++;
			} else {
				$en++;
			}
			$total++;
			$mov += count($this->demoHistTotal($h['idnorad']));
		}
		return [
			'total_hojas' => $total,
			'total_movimientos' => $mov,
			'en_proceso' => $en,
			'devueltas' => $dev,
			'finalizadas' => $fin
		];
	}

	/* ======================================================================
	 * PERFILES Y ROLES DEL FLUJO
	 * ====================================================================== */

	/**
	 * Perfiles efectivos de un usuario (perfil principal + perfiles extra de usupef).
	 * @return array [idper => nomper]
	 */
	public function getPerfilesUsuario($idusu) {
		if (empty($idusu)) return [];
		try {
			$out = [];
			$q = $this->conexion->prepare(
				"SELECT p.idper, p.nomper FROM perfil p
				 JOIN usuario u ON u.idper = p.idper
				 WHERE u.idusu = :u");
			$q->bindValue(':u', (int)$idusu, PDO::PARAM_INT);
			$q->execute();
			foreach ($q->fetchAll(PDO::FETCH_ASSOC) as $r) {
				$out[(int)$r['idper']] = trim($r['nomper']);
			}

			$u = $this->conexion->prepare(
				"SELECT p.idper, p.nomper FROM usupef up
				 JOIN perfil p ON p.idper = up.idper
				 WHERE up.idusu = :u");
			$u->bindValue(':u', (int)$idusu, PDO::PARAM_INT);
			$u->execute();
			foreach ($u->fetchAll(PDO::FETCH_ASSOC) as $r) {
				$out[(int)$r['idper']] = trim($r['nomper']);
			}
			return $out;
		} catch (Exception $e) {
			return [];
		}
	}

	/**
	 * Indica si el usuario puede actuar en un paso del flujo.
	 * @return bool
	 */
	public function puedeActuarEnPaso($idusu, $paso) {
		$perf = $this->getPerfilesUsuario($idusu);
		if (empty($perf)) return false;
		foreach (array_keys($perf) as $p) {
			if (in_array((int)$p, self::$ROL_ADMIN, true)) return true;
		}
		$rol = self::$ROL_PASO[(int)$paso] ?? [];
		if (!$rol) return false;
		foreach (array_keys($perf) as $p) {
			if (in_array((int)$p, $rol, true)) return true;
		}
		return false;
	}

	/**
	 * Resuelve el rol (nombre de perfil) que un usuario tiene frente al flujo.
	 * @return string
	 */
	public function resolverRolUsuario($idusu, $paso) {
		$perf = $this->getPerfilesUsuario($idusu);
		if (empty($perf)) return $_SESSION['pefnom'] ?? 'Funcionario SENA';
		foreach (array_keys($perf) as $p) {
			if (in_array((int)$p, self::$ROL_ADMIN, true)) return $perf[$p];
		}
		$rol = self::$ROL_PASO[(int)$paso] ?? [];
		foreach (array_keys($perf) as $p) {
			if (in_array((int)$p, $rol, true)) return $perf[$p];
		}
		return reset($perf);
	}

	/* ======================================================================
	 * OPERACIONES SOBRE LA BD
	 * ====================================================================== */

	/**
	 * Aplica un avance de flujo de forma transaccional: actualiza hojatra y
	 * registra el movimiento de trazabilidad. Si algo falla, revierte todo.
	 * @param int $id_hojatra
	 * @param int $nuevoPaso
	 * @param string $nuevoEstado
	 * @param int|null $devueltoDe
	 * @param array $mov [paso_flujo, id_usuario, rol_usuario, accion, observacion]
	 * @return bool
	 */
	public function procesarFlujo($id_hojatra, $nuevoPaso, $nuevoEstado, $devueltoDe = null, $mov = []) {
		try {
			if (!$this->conexion->inTransaction()) {
				$this->conexion->beginTransaction();
			}
			$okH = $this->actualizarEstadoHoja($id_hojatra, $nuevoPaso, $nuevoEstado, $devueltoDe);
			$okT = $this->registrarTrazabilidad([
				'id_hojatra' => $id_hojatra,
				'paso_flujo' => !empty($mov['paso_flujo']) ? $mov['paso_flujo'] : $nuevoPaso,
				'id_usuario' => $mov['id_usuario'] ?? null,
				'rol_usuario' => $mov['rol_usuario'] ?? 'Funcionario SENA',
				'accion' => $mov['accion'] ?? 'Registro',
				'observacion' => !empty($mov['observacion']) ? $mov['observacion'] : null
			]);
			if (!$okH || !$okT) {
				if ($this->conexion->inTransaction()) $this->conexion->rollBack();
				return false;
			}
			$this->conexion->commit();
			return true;
		} catch (Exception $e) {
			if ($this->conexion->inTransaction()) $this->conexion->rollBack();
			if (function_exists('ManejoError')) ManejoError($e);
			return false;
		}
	}

	/**
	 * Registra un nuevo evento en la tabla de trazabilidad
	 * @param array $datos [id_hojatra, paso_flujo, id_usuario, rol_usuario, accion, observacion]
	 * @return bool
	 */
	public function registrarTrazabilidad($datos) {
		try {
			$sql = "INSERT INTO trazabilidad_hojatra (id_hojatra, paso_flujo, id_usuario, rol_usuario, accion, observacion, fecha_registro)
					VALUES (:id_hojatra, :paso_flujo, :id_usuario, :rol_usuario, :accion, :observacion, :fecha_registro)";
			$stmt = $this->conexion->prepare($sql);

			$idHojatra = (int)$datos['id_hojatra'];
			$pasoFlujo = (int)$datos['paso_flujo'];
			$idUsuario = !empty($datos['id_usuario']) ? (int)$datos['id_usuario'] : null;
			$rolUsuario = !empty($datos['rol_usuario']) ? $datos['rol_usuario'] : 'Usuario Sistema';
			$accion = !empty($datos['accion']) ? $datos['accion'] : 'Registro';
			$observacion = !empty($datos['observacion']) ? trim($datos['observacion']) : null;
			$fechaRegistro = date('Y-m-d H:i:s');

			$stmt->bindParam(':id_hojatra', $idHojatra, PDO::PARAM_INT);
			$stmt->bindParam(':paso_flujo', $pasoFlujo, PDO::PARAM_INT);
			$stmt->bindParam(':id_usuario', $idUsuario, PDO::PARAM_INT);
			$stmt->bindParam(':rol_usuario', $rolUsuario);
			$stmt->bindParam(':accion', $accion);
			$stmt->bindParam(':observacion', $observacion);
			$stmt->bindParam(':fecha_registro', $fechaRegistro);

			$res = $stmt->execute();

			// Mantiene compatibilidad con la tabla trazacom histórica si existe
			try {
				$sqlComp = "INSERT INTO trazacom (idtrht, idnorad, idpas, idusu, fectrac)
							VALUES (0, :idnorad, :idpas, :idusu, :fectrac)";
				$stmtComp = $this->conexion->prepare($sqlComp);
				$stmtComp->bindParam(':idnorad', $idHojatra);
				$stmtComp->bindParam(':idpas', $pasoFlujo);
				$stmtComp->bindParam(':idusu', $idUsuario);
				$stmtComp->bindParam(':fectrac', $fechaRegistro);
				@$stmtComp->execute();
			} catch (Exception $ex) {
				// Silenciar en caso de discrepancia en trazacom
			}

			return $res;
		} catch (Exception $e) {
			if (function_exists('ManejoError')) ManejoError($e);
			return false;
		}
	}

	/**
	 * Obtiene todo el historial de trazabilidad de una Hoja de Trabajo específica
	 * Ordenado cronológicamente descendente (más reciente primero)
	 * @param int $id_hojatra
	 * @return array
	 */
	public function obtenerHistorialTrazabilidad($id_hojatra) {
		if ($this->esDemo($id_hojatra)) {
			return $this->demoHistTotal($id_hojatra);
		}
		try {
			$sql = "SELECT t.id_trazabilidad, t.id_hojatra, t.paso_flujo, t.id_usuario,
						   t.rol_usuario, t.accion, t.observacion, t.fecha_registro,
						   u.nomusu, u.ndocusu, u.emausu
					FROM trazabilidad_hojatra t
					LEFT JOIN usuario u ON t.id_usuario = u.idusu
					WHERE t.id_hojatra = :id_hojatra
					ORDER BY t.fecha_registro DESC, t.id_trazabilidad DESC";
			$stmt = $this->conexion->prepare($sql);
			$idHoj = (int)$id_hojatra;
			$stmt->bindParam(':id_hojatra', $idHoj, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch (Exception $e) {
			if (function_exists('ManejoError')) ManejoError($e);
			return [];
		}
	}

	/**
	 * Actualiza el paso actual y estado en la tabla principal de Hojas de Trabajo (hojatra)
	 * @param int $id_hojatra
	 * @param int $nuevoPaso
	 * @param string $nuevoEstado
	 * @param int|null $devueltoDe
	 * @return bool
	 */
	public function actualizarEstadoHoja($id_hojatra, $nuevoPaso, $nuevoEstado, $devueltoDe = null) {
		try {
			$sql = "UPDATE hojatra 
					SET paso_actual = :paso_actual, 
						estado = :estado, 
						devuelto_de = :devuelto_de 
					WHERE idnorad = :idnorad";
			$stmt = $this->conexion->prepare($sql);
			$idHoj = (int)$id_hojatra;
			$paso = (int)$nuevoPaso;
			$dev = ($devueltoDe !== null && $devueltoDe !== '') ? (int)$devueltoDe : null;

			$stmt->bindParam(':paso_actual', $paso, PDO::PARAM_INT);
			$stmt->bindParam(':estado', $nuevoEstado);
			$stmt->bindParam(':devuelto_de', $dev, PDO::PARAM_INT);
			$stmt->bindParam(':idnorad', $idHoj, PDO::PARAM_INT);
			return $stmt->execute();
		} catch (Exception $e) {
			if (function_exists('ManejoError')) ManejoError($e);
			return false;
		}
	}

	/**
	 * Obtiene el detalle completo de una Hoja de Trabajo por su ID (idnorad)
	 * @param int $id_hojatra
	 * @return array|null
	 */
	public function getHojaById($id_hojatra) {
		if ($this->esDemo($id_hojatra)) {
			$d = $this->estadoDemo($id_hojatra);
			if (!$d) return null;
			$d['idnorad'] = (int)$id_hojatra;
			return $d;
		}
		try {
			$sql = "SELECT h.idnorad, h.codpro, p.nompro, p.horlpro,
						   h.idemp, e.nomemp, e.numdocemp, e.nomconemp, e.telemp,
						   h.codproesp, s.nomval AS proesp,
						   h.codslem, h.feclini, h.feclin, h.cupo, h.jornada, j.nomval AS nomjornada,
						   h.idfic, f.nomfic, h.convht,
						   COALESCE(h.paso_actual, 1) AS paso_actual,
						   COALESCE(h.estado, 'En Revisión (Instructor)') AS estado,
						   h.devuelto_de,
						   uinst.nomusu AS nombre_instructor
					FROM hojatra h
					LEFT JOIN programa p ON h.codpro = p.codpro
					LEFT JOIN empresa e ON h.idemp = e.idemp
					LEFT JOIN valor s ON h.codproesp = s.idval
					LEFT JOIN valor j ON h.jornada = j.idval
					LEFT JOIN ficha f ON h.idfic = f.idfic
					LEFT JOIN usuario uinst ON f.idusu = uinst.idusu
					WHERE h.idnorad = :idnorad";
			$stmt = $this->conexion->prepare($sql);
			$idHoj = (int)$id_hojatra;
			$stmt->bindParam(':idnorad', $idHoj, PDO::PARAM_INT);
			$stmt->execute();
			$res = $stmt->fetchAll(PDO::FETCH_ASSOC);
			return !empty($res) ? $res[0] : null;
		} catch (Exception $e) {
			if (function_exists('ManejoError')) ManejoError($e);
			return null;
		}
	}

	/**
	 * Obtiene el listado de Hojas de Trabajo para selectores y tablas resumen
	 * @param bool|null $demo null = demo solo si la BD está vacía
	 * @return array
	 */
	public function getHojas($demo = null) {
		if ($demo === null) $demo = $this->esHojatraVacia();
		if ($demo) return $this->getHojasDemo();
		try {
			$sql = "SELECT h.idnorad, h.codpro, p.nompro, h.idemp, e.nomemp,
						   h.feclini, h.feclin, h.cupo, h.idfic,
						   COALESCE(h.paso_actual, 1) AS paso_actual,
						   COALESCE(h.estado, 'En Revisión (Instructor)') AS estado,
						   h.devuelto_de
					FROM hojatra h
					LEFT JOIN programa p ON h.codpro = p.codpro
					LEFT JOIN empresa e ON h.idemp = e.idemp
					ORDER BY h.idnorad DESC";
			$stmt = $this->conexion->prepare($sql);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch (Exception $e) {
			if (function_exists('ManejoError')) ManejoError($e);
			return [];
		}
	}

	/**
	 * Métricas y conteos de trazabilidad para tarjetas estadísticas
	 * @param bool|null $demo null = demo solo si la BD está vacía (o si el id está en demo)
	 * @return array
	 */
	public function getConteos($demo = null) {
		if ($demo === null && $this->esHojatraVacia()) $demo = true;
		if ($demo) return $this->getConteosDemo();
		try {
			$sql = "SELECT
					(SELECT COUNT(*) FROM hojatra) AS total_hojas,
					(SELECT COUNT(*) FROM trazabilidad_hojatra) AS total_movimientos,
					(SELECT COUNT(*) FROM hojatra 
						WHERE (paso_actual < 4 OR paso_actual IS NULL) 
						  AND (estado NOT LIKE '%Devuelto%' AND estado NOT LIKE '%Rechazado%')
					) AS en_proceso,
					(SELECT COUNT(*) FROM hojatra 
						WHERE estado LIKE '%Devuelto%' OR estado LIKE '%Rechazado%'
					) AS devueltas,
					(SELECT COUNT(*) FROM hojatra 
						WHERE paso_actual = 4 OR estado LIKE '%Aprobado Final%' OR estado LIKE '%Cerrado%'
					) AS finalizadas";
			$stmt = $this->conexion->prepare($sql);
			$stmt->execute();
			$res = $stmt->fetchAll(PDO::FETCH_ASSOC);
			return !empty($res) ? $res[0] : [
				'total_hojas' => 0,
				'total_movimientos' => 0,
				'en_proceso' => 0,
				'devueltas' => 0,
				'finalizadas' => 0
			];
		} catch (Exception $e) {
			if (function_exists('ManejoError')) ManejoError($e);
			return [
				'total_hojas' => 0,
				'total_movimientos' => 0,
				'en_proceso' => 0,
				'devueltas' => 0,
				'finalizadas' => 0
			];
		}
	}
}
?>