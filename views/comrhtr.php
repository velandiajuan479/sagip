<?php
if (!defined('COMVHTR_RUN')) {
	define('COMVHTR_RUN', true);
}

date_default_timezone_set('America/Bogota');

// ── Controlador embebido ─────────────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
	session_start();
}
require_once __DIR__ . '/../models/commhtr.php';
require_once __DIR__ . '/../controllers/titulo.php';

// Acceso directo al archivo (views/comrhtr.php): misma protección que la app.
// home.php define $pg antes de incluir la vista; si no existe, es acceso directo.
$comrhtrDirecto = !isset($pg);
if ($comrhtrDirecto) {
	require_once __DIR__ . '/../models/seguridad.php';
}

// Helpers opcionales usados por otras vistas incluídas en la misma página
if (!function_exists('titulo2')) {
	function titulo2($titulo, $tipo = 1) {
		$txt  = '<div class="err"></div>';
		$txt .= '<div class="tit"><div class="tita">';
		$txt .= $titulo;
		$txt .= '<hr class="hrtit">';
		$txt .= '</div></div>';
		return $txt;
	}
}

// Página activa (home.php la define; en acceso directo se asume 2004)
if (!isset($pg)) {
	$pg = isset($_GET['pg']) ? $_GET['pg'] : 2004;
}

// ─────────────────────────────────────────────────────────────────────────
//  Funciones autocontenidas de este archivo (horarios + documento + PDF/Excel)
// ─────────────────────────────────────────────────────────────────────────

// Horarios complementarios de la hoja: tabla "horario" (módulo comvhcm.php)
// = programaciones puntuales con fecha específica + hora + instructor.
function comrhtr_horarios($idnorad) {
	$lista = [];
	$id = (int)$idnorad;
	if ($id <= 0) return $lista;
	try {
		$modelo = new conexion();
		$c = $modelo->get_conexion();
		$sql = "SELECT h.fecha_especifica, h.hinihor, h.hfinhor, h.idusu, u.nomusu, u.ndocusu
				FROM horario AS h
				LEFT JOIN usuario AS u ON h.idusu = u.idusu
				WHERE h.idnorad = :id
				  AND h.fecha_especifica IS NOT NULL AND h.fecha_especifica <> ''
				  AND h.hinihor IS NOT NULL AND h.hfinhor IS NOT NULL
				  AND NOT (h.hinihor = '00:00:00' AND h.hfinhor = '00:00:00')
				ORDER BY h.fecha_especifica ASC, h.hinihor ASC, h.idusu ASC";
		$st = $c->prepare($sql);
		$st->bindValue(':id', $id, PDO::PARAM_INT);
		$st->execute();
		$mapaDias = ['Monday' => 'LUNES', 'Tuesday' => 'MARTES', 'Wednesday' => 'MIÉRCOLES', 'Thursday' => 'JUEVES', 'Friday' => 'VIERNES', 'Saturday' => 'SÁBADO', 'Sunday' => 'DOMINGO'];
		foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $r) {
			$fec = $r['fecha_especifica'];
			$ts  = strtotime($fec);
			$lista[] = [
				'fecha'      => $ts ? date('d/m/Y', $ts) : $fec,
				'dia'        => $ts ? ($mapaDias[date('l', $ts)] ?? '') : '',
				'ini'        => substr((string)$r['hinihor'], 0, 5),
				'fin'        => substr((string)$r['hfinhor'], 0, 5),
				'instructor' => trim((string)($r['nomusu'] ?? '')),
				'docuinst'   => trim((string)($r['ndocusu'] ?? '')),
			];
		}
	} catch (Exception $e) {
		$lista = [];
	}
	return $lista;
}

// Documento oficial completo (estilo del archivo "1 HOJA DE TRABAJO
// COMPLEMENTARIA G1(1).XLS") como HTML con estilos en línea, reutilizable en
// pantalla y en la generación del PDF (dompdf).
function comrhtr_doc_html($datos, $dias, $horarios, $opts) {
	$e = function($v, $d = '---') {
		return htmlspecialchars((string)(!empty($v) ? $v : $d), ENT_QUOTES, 'UTF-8');
	};
	$fmt = function($f) {
		if (empty($f)) return '---';
		$ts = strtotime($f);
		return $ts ? date('d/m/Y', $ts) : $f;
	};
	$sec = function($t) {
		return '<div style="font-weight:800;text-transform:uppercase;font-size:11px;letter-spacing:.4px;color:#0d701c;border-bottom:2px solid #0d701c;padding-bottom:2px;margin:14px 0 8px 0;">' . $t . '</div>';
	};

	// Fila de etiqueta => valor subrayado (pares)
	$ln = function(array $pares) use ($e) {
		$tds = '';
		foreach ($pares as $p) {
			$tds .= '<td style="padding:4px 6px;vertical-align:bottom;"><span style="font-weight:800;text-transform:uppercase;font-size:8px;color:#111;display:block;">' . $e($p[0]) . '</span></td>'
			      . '<td style="padding:4px 6px;vertical-align:bottom;border-bottom:1px solid #000;font-weight:600;font-size:10px;color:#1e293b;">' . $e($p[1]) . '</td>';
		}
		return '<table style="width:100%;border-collapse:collapse;margin-bottom:2px;"><tr>' . $tds . '</tr></table>';
	};

	$h = '';

	// ── Encabezado institucional + radicado ──
	$h .= '<table style="width:100%;border-collapse:collapse;border-bottom:2px solid #000;margin-bottom:12px;">';
	$h .= '<tr>';
	$h .= '<td style="width:105px;text-align:center;vertical-align:middle;padding:4px;border:none;">';
	if (!empty($opts['logoBase64'])) {
		$h .= '<img src="' . $opts['logoBase64'] . '" alt="Logo SENA" style="max-width:90px;max-height:80px;object-fit:contain;">';
	} else {
		$h .= '<strong style="color:#0d701c;font-size:22px;">SENA</strong>';
	}
	$h .= '</td>';
	$h .= '<td style="text-align:center;padding:0 12px;vertical-align:middle;border:none;">';
	$h .= '<div style="font-weight:bold;text-transform:uppercase;font-size:13px;letter-spacing:.5px;line-height:1.25;color:#000;">SENA CENTRO DE DESARROLLO AGROEMPRESARIAL</div>';
	$h .= '<div style="font-weight:bold;text-transform:uppercase;font-size:10px;margin-top:4px;line-height:1.3;color:#000;">HOJA DE TRABAJO SOLICITUD CURSOS DE COMPLEMENTARIA - TITULADA</div>';
	$h .= '</td>';
	$h .= '<td style="width:180px;text-align:right;vertical-align:middle;padding:4px;border:none;">';
	$h .= '<div style="border:2px solid #000;background:#f8fafc;padding:8px 10px;text-align:center;border-radius:4px;">';
	$h .= '<div style="text-transform:uppercase;font-weight:bold;font-size:8px;letter-spacing:.5px;color:#475569;">Radicado Oficial</div>';
	$h .= '<div style="font-weight:bold;font-size:16px;color:#0d701c;margin:2px 0;font-family:Consolas,monospace;">#' . $e($opts['radicado'] ?? '00000') . '</div>';
	$h .= '<div style="font-size:9px;color:#475569;font-weight:bold;border-top:1px dashed #cbd5e1;padding-top:4px;margin-top:4px;">Expedición: ' . date('d/m/Y') . '</div>';
	$h .= '</div></td>';
	$h .= '</tr></table>';

	// ── Datos generales de la solicitud ──
	$durTxt = !empty($opts['durNum']) ? (int)$opts['durNum'] : $e($datos['duracion'] ?? '---');
	$h .= $ln([
		['NOMBRE DEL PROGRAMA DE FORMACION', $datos['nompro'] ?? '---'],
	]);
	$h .= $ln([
		['CODIGO DEL PROGRAMA', $datos['codpro'] ?? '---'],
	]);
	$h .= $ln([
		['DURACION MAXIMA HORAS', $durTxt],
	]);
	$h .= $ln([
		['CODIGO SOLICITUD DE EMPRESA', $datos['codslem'] ?? '---'],
	]);

	// ── CARACTERIZACIÓN DE LA FICHA ──
	$h .= $sec('CARACTERIZACIÓN DE LA FICHA');
	$h .= $ln([
		['CONVENIO', !empty($datos['convenio']) ? $datos['convenio'] : 'No aplica / Sin convenio específico'],
	]);
	$peSelec = (int)($opts['codproesp_id'] ?? 0);
	if (!empty($opts['progEsp']) && is_array($opts['progEsp'])) {
		foreach (array_chunk($opts['progEsp'], 3) as $filaPe) {
			$h .= '<div style="margin-bottom:2px;">';
			foreach ($filaPe as $pePe) {
				$marc = ($peSelec > 0 && (int)$pePe['idval'] === $peSelec) ? 'X' : '';
				$h .= '<span style="margin-right:14px;font-weight:600;font-size:9px;text-transform:uppercase;white-space:nowrap;">'
				    . '<span style="border:1px solid #000;background:#fff;display:inline-block;width:11px;height:11px;text-align:center;vertical-align:-1px;font-size:8px;font-weight:800;margin-right:4px;">' . $marc . '</span>'
				    . $e($pePe['nomval'] ?? '') . '</span>';
			}
			$h .= '</div>';
		}
	} else {
		$h .= $ln([['PROGRAMA ESPECIAL', '---']]);
	}

	// ── DATOS EMPRESARIO ──
	$h .= $sec('DATOS EMPRESARIO');
	$h .= $ln([
		['EMPRESA', $datos['nomemp'] ?? '---'],
		['NIT', $datos['numdocemp'] ?? '---'],
	]);
	$h .= $ln([
		['DIRECCION DEL LUGAR DE FORMACION', $datos['direccion'] ?? 'Sede asignada'],
		['MUNICIPIO', $datos['municipio'] ?? '---'],
	]);
	$h .= $ln([
		['CONTACTO', $datos['contacto'] ?? '---'],
		['TÉLEFONO', $datos['telefono'] ?? '---'],
	]);

	// ── PROGRAMACIÓN DE LA FICHA ──
	$h .= $sec('PROGRAMACIÓN DE LA FICHA');
	$h .= $ln([
		['FECHA DE INICIO', $fmt($datos['fecha_inicio'] ?? '')],
		['FECHA DE TERMINACION', $fmt($datos['fecha_fin'] ?? '')],
	]);
	$h .= $ln([
		['CUPO', !empty($datos['cupo']) ? (int)$datos['cupo'] : 0],
		['JORNADA', $datos['jornada'] ?? '---'],
	]);

	// ── Días de formación (matriz semanal) ──
	$h .= '<div style="font-weight:800;text-transform:uppercase;font-size:9px;margin:12px 0 4px 0;">INDICAR DÍAS DE FORMACIÓN Y HORAS EN FORMA MILITAR</div>';
	$h .= '<table style="width:100%;border-collapse:collapse;">';
	$h .= '<tr>'
	    . '<th style="border:1px solid #94a3b8;padding:4px 8px;font-size:9px;text-transform:uppercase;background:#f1f5f9;width:34%;text-align:left;">DÍA</th>'
	    . '<th style="border:1px solid #94a3b8;padding:4px 8px;font-size:9px;text-transform:uppercase;background:#f1f5f9;text-align:left;">HORA INICIO</th>'
	    . '<th style="border:1px solid #94a3b8;padding:4px 8px;font-size:9px;text-transform:uppercase;background:#f1f5f9;text-align:left;">HORA TERMINACION</th>'
	    . '</tr>';
	foreach ($diasSemana = ['LUNES', 'MARTES', 'MIÉRCOLES', 'JUEVES', 'VIERNES', 'SÁBADO', 'DOMINGO'] as $diaDoc) {
		$hIniD = !empty($dias[$diaDoc]['inicio']) ? $dias[$diaDoc]['inicio'] : '';
		$hFinD = !empty($dias[$diaDoc]['fin'])    ? $dias[$diaDoc]['fin']    : '';
		$h .= '<tr>'
		    . '<td style="border:1px solid #94a3b8;padding:4px 8px;font-size:10px;">' . $diaDoc . '</td>'
		    . '<td style="border:1px solid #94a3b8;padding:4px 8px;font-size:10px;">' . $e($hIniD) . '</td>'
		    . '<td style="border:1px solid #94a3b8;padding:4px 8px;font-size:10px;">' . $e($hFinD) . '</td>'
		    . '</tr>';
	}
	$h .= '</table>';

	// ── Horarios complementarios (comvhcm.php → tabla horario) ──
	$h .= $sec('HORARIOS COMPLEMENTARIOS (PROGRAMADOS POR FECHA)');
	if (!empty($horarios) && is_array($horarios)) {
		$h .= '<table style="width:100%;border-collapse:collapse;">';
		$h .= '<tr>'
		    . '<th style="border:1px solid #94a3b8;padding:4px 8px;font-size:9px;text-transform:uppercase;background:#f1f5f9;text-align:left;">FECHA</th>'
		    . '<th style="border:1px solid #94a3b8;padding:4px 8px;font-size:9px;text-transform:uppercase;background:#f1f5f9;text-align:left;">DÍA</th>'
		    . '<th style="border:1px solid #94a3b8;padding:4px 8px;font-size:9px;text-transform:uppercase;background:#f1f5f9;text-align:left;">HORA INICIO</th>'
		    . '<th style="border:1px solid #94a3b8;padding:4px 8px;font-size:9px;text-transform:uppercase;background:#f1f5f9;text-align:left;">HORA TERMINACION</th>'
		    . '<th style="border:1px solid #94a3b8;padding:4px 8px;font-size:9px;text-transform:uppercase;background:#f1f5f9;text-align:left;">INSTRUCTOR</th>'
		    . '</tr>';
		foreach ($horarios as $ho) {
			$h .= '<tr>'
			    . '<td style="border:1px solid #94a3b8;padding:4px 8px;font-size:10px;">' . $e($ho['fecha']) . '</td>'
			    . '<td style="border:1px solid #94a3b8;padding:4px 8px;font-size:10px;">' . $e($ho['dia']) . '</td>'
			    . '<td style="border:1px solid #94a3b8;padding:4px 8px;font-size:10px;">' . $e($ho['ini']) . '</td>'
			    . '<td style="border:1px solid #94a3b8;padding:4px 8px;font-size:10px;">' . $e($ho['fin']) . '</td>'
			    . '<td style="border:1px solid #94a3b8;padding:4px 8px;font-size:10px;">' . $e($ho['instructor'], 'No asignado') . '</td>'
			    . '</tr>';
		}
		$h .= '</table>';
	} else {
		$h .= '<table style="width:100%;border-collapse:collapse;"><tr><td style="padding:6px 8px;font-size:9px;color:#64748b;border:1px solid #e2e8f0;">No se han registrado horarios complementarios para esta hoja de trabajo.</td></tr></table>';
	}

	// ── Instructor ──
	$h .= $ln([
		['NOMBRE Y APELLIDO DEL INSTRUCTOR', !empty($datos['nombre_instructor']) ? $datos['nombre_instructor'] : 'Pendiente por asignar'],
	]);
	$h .= $ln([
		['DOCUMENTO DEL INSTRUCTOR', $datos['documento_instructor'] ?? '---'],
		['CEL', $datos['cel_instructor'] ?? '---'],
	]);
	$h .= $ln([
		['CORREO INSTRUCTOR', $datos['correo_instructor'] ?? '---'],
	]);

	// ── Controles ──
	$h .= $ln([
		['CONTROL DEL COORDINADOR', ''],
		['CODIGO DE FICHA', $datos['codigo_ficha'] ?? '---'],
	]);
	$h .= $ln([
		['CONTROL APOYO A LA SUPERVISIÓN', ''],
		['CONTROL COMPLEMENTARIA', ''],
	]);

	$h .= '<div style="text-align:center;margin-top:14px;font-size:8px;color:#64748b;">Documento oficial de control y gestión académica — SENA CEDA — Generado el ' . date('d/m/Y H:i:s') . '</div>';

	return $h;
}

// Exportación a PDF (dompdf) — autocontenida en este archivo
function comrhtr_exportar_pdf($datos, $dias, $horarios, $progEsp, $peId, $logoBase64, $radicado) {
	$autoload = __DIR__ . '/../vendor/autoload.php';
	if (file_exists($autoload)) { require_once $autoload; }

	if (ob_get_level()) { ob_clean(); }
	if (headers_sent()) {
		echo '<script>alert("No fue posible generar el PDF. Recargue la página e intente de nuevo.");window.history.back();</script>';
		exit;
	}

	$cuerpo = comrhtr_doc_html($datos, $dias, $horarios, [
		'progEsp'     => $progEsp,
		'codproesp_id'=> $peId,
		'durNum'      => (int)($datos['duracion'] ?? 0) ?: 0,
		'logoBase64'  => $logoBase64,
		'radicado'    => $radicado,
	]);

	$html = '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><style>
		@page { size: A4 portrait; margin: 14mm 11mm; }
		body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1e293b; }
		table { page-break-inside: avoid; }
	</style></head><body>' . $cuerpo . '</body></html>';

	$options = new \Dompdf\Options();
	$options->set('isHtml5ParserEnabled', true);
	$options->set('isRemoteEnabled', false);
	$options->set('defaultFont', 'DejaVu Sans');

	try {
		$dompdf = new \Dompdf\Dompdf($options);
		$dompdf->loadHtml($html);
		$dompdf->render();
		$outPDF = $dompdf->output();
	} catch (\Throwable $ex) {
		while (ob_get_level()) { ob_end_clean(); }
		echo '<script>alert("No fue posible generar el PDF (' . htmlspecialchars($ex->getMessage(), ENT_QUOTES) . '). Recargue la página e intente de nuevo.");window.history.back();</script>';
		exit;
	}

	while (ob_get_level()) { ob_end_clean(); }

	header('Content-Type: application/pdf');
	header('Content-Disposition: attachment; filename="HojaTrabajo_#' . $radicado . '_' . date('Ymd') . '.pdf"');
	header('Cache-Control: private, max-age=0, must-revalidate');
	header('Pragma: public');
	echo $outPDF;
	exit;
}

// Exportación a Excel (.xls clásico OLE2, PhpSpreadsheet) — autocontenida
function comrhtr_exportar_xls($datos, $dias, $horarios, $progEsp, $peId, $radicado) {
	$autoload = __DIR__ . '/../vendor/autoload.php';
	if (file_exists($autoload)) { require_once $autoload; }

	$restaurarEr = error_reporting(error_reporting() & ~(E_DEPRECATED | E_NOTICE | E_WARNING));

	if (ob_get_level()) { ob_clean(); }
	if (headers_sent()) {
		echo '<script>alert("No fue posible generar el Excel. Recargue la página e intente de nuevo.");window.history.back();</script>';
		exit;
	}

	$diasSemana = ['LUNES', 'MARTES', 'MIÉRCOLES', 'JUEVES', 'VIERNES', 'SÁBADO', 'DOMINGO'];
	$fmt = function($f) {
		if (empty($f)) return '---';
		$ts = strtotime($f);
		return $ts ? date('d/m/Y', $ts) : $f;
	};
	$md = function($s) { return (!is_null($s) && $s !== '') ? (string)$s : ''; };

	$VERDE   = '0D701C';
	$VERDECL = 'BBF7D0';
	$GRIS    = 'F1F5F9';

	$sp = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
	$sh = $sp->getActiveSheet();
	$sh->setTitle('HOJA DE TRABAJO');

	$cols = ['A', 'B', 'C', 'D', 'E', 'F'];
	foreach (['A' => 24, 'B' => 38, 'C' => 24, 'D' => 38, 'E' => 26, 'F' => 34] as $cl => $w) {
		$sh->getColumnDimension($cl)->setWidth($w);
	}

	$fila = 1;
	$estiloLbl = function($gris) {
		return [
			'font'      => ['bold' => true, 'size' => 9],
			'fill'      => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => $gris]],
			'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT],
		];
	};

	// Fila de datos: pares [etiqueta, valor] hasta 3 por fila (A:B, C:D, E:F)
	$filDatos = function(array $pares) use ($sh, &$fila, $cols, $estiloLbl, $GRIS) {
		$pos = 0;
		foreach ($pares as $px) {
			$c0 = $cols[$pos]; $c1 = $cols[$pos + 1];
			$sh->setCellValue($c0 . $fila, $px[0]);
			$sh->getStyle($c0 . $fila)->applyFromArray($estiloLbl($GRIS));
			$sh->setCellValue($c1 . $fila, $px[1]);
			$sh->getStyle($c1 . $fila)->getFont()->setSize(9);
			$pos += 2;
		}
		$fila++;
	};

	// Encabezado de sección
	$sec = function($t) use ($sh, &$fila, $VERDE) {
		$sh->mergeCells("A{$fila}:F{$fila}");
		$sh->setCellValue("A{$fila}", $t);
		$sh->getStyle("A{$fila}:F{$fila}")->applyFromArray([
			'font'  => ['bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
			'fill'  => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => $VERDE]],
		]);
		$sh->getRowDimension($fila)->setRowHeight(18);
		$fila++;
	};

	// ── Encabezado institucional ──
	$sh->mergeCells("A{$fila}:F{$fila}");
	$sh->setCellValue("A{$fila}", 'SENA CENTRO DE DESARROLLO AGROEMPRESARIAL');
	$sh->getStyle("A{$fila}:F{$fila}")->applyFromArray(['font' => ['bold' => true, 'size' => 13], 'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]]);
	$fila++;
	$sh->mergeCells("A{$fila}:F{$fila}");
	$sh->setCellValue("A{$fila}", 'HOJA DE TRABAJO SOLICITUD CURSOS DE COMPLEMENTARIA - TITULADA');
	$sh->getStyle("A{$fila}:F{$fila}")->applyFromArray(['font' => ['bold' => true, 'size' => 10], 'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]]);
	$fila++;
	$sh->mergeCells("A{$fila}:F{$fila}");
	$sh->setCellValue("A{$fila}", 'Radicado Oficial: #' . $radicado . '   |   Expedición: ' . date('d/m/Y'));
	$sh->getStyle("A{$fila}:F{$fila}")->applyFromArray(['font' => ['italic' => true, 'size' => 9, 'color' => ['rgb' => '64748B']], 'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]]);
	$fila += 2;

	// ── Datos generales ──
	$sec('DATOS GENERALES DE LA SOLICITUD');
	$durTxt = (int)($datos['duracion'] ?? 0) ?: $md($datos['duracion'] ?? '');
	$filDatos([['NOMBRE DEL PROGRAMA DE FORMACION', $md($datos['nompro'] ?? '')]]);
	$filDatos([['CODIGO DEL PROGRAMA', $md($datos['codpro'] ?? '')]]);
	$filDatos([['DURACION MAXIMA HORAS', $durTxt]]);
	$filDatos([['CODIGO SOLICITUD DE EMPRESA', $md($datos['codslem'] ?? '')]]);
	$fila++;

	// ── CARACTERIZACIÓN DE LA FICHA ──
	$sec('CARACTERIZACIÓN DE LA FICHA');
	$convTxt = $md($datos['convenio'] ?? '') ?: 'No aplica / Sin convenio específico';
	$filDatos([['CONVENIO', $convTxt]]);
	if (!empty($progEsp) && is_array($progEsp)) {
		$filDatos([['PROGRAMA ESPECIAL', '']]);
		foreach (array_chunk($progEsp, 3) as $filaPe) {
			$ci = 0;
			foreach ($filaPe as $pe) {
				$marc  = ($peId > 0 && (int)$pe['idval'] === (int)$peId) ? 'X' : ' ';
				$c0 = $cols[$ci]; $c1 = $cols[$ci + 1];
				$sh->mergeCells($c0 . $fila . ':' . $c1 . $fila);
				$sh->setCellValue($c0 . $fila, '[' . $marc . '] ' . $md($pe['nomval']));
				$sh->getStyle($c0 . $fila)->getFont()->setSize(9);
				$ci += 2;
			}
			$fila++;
		}
	} else {
		$filDatos([['PROGRAMA ESPECIAL', '---']]);
	}
	$fila++;

	// ── DATOS EMPRESARIO ──
	$sec('DATOS EMPRESARIO');
	$filDatos([['EMPRESA', $md($datos['nomemp'] ?? '')], ['NIT', $md($datos['numdocemp'] ?? '')]]);
	$filDatos([['DIRECCION DEL LUGAR DE FORMACION', $md($datos['direccion'] ?? 'Sede asignada')], ['MUNICIPIO', $md($datos['municipio'] ?? '')]]);
	$filDatos([['CONTACTO', $md($datos['contacto'] ?? '')], ['TÉLEFONO', $md($datos['telefono'] ?? '')]]);
	$fila++;

	// ── PROGRAMACIÓN DE LA FICHA ──
	$sec('PROGRAMACIÓN DE LA FICHA');
	$filDatos([['FECHA DE INICIO', $fmt($datos['fecha_inicio'] ?? '')], ['FECHA DE TERMINACION', $fmt($datos['fecha_fin'] ?? '')]]);
	$filDatos([['CUPO', (int)($datos['cupo'] ?? 0)], ['JORNADA', $md($datos['jornada'] ?? '')]]);
	$fila++;

	// ── Días de formación ──
	$sec('INDICAR DÍAS DE FORMACIÓN Y HORAS EN FORMA MILITAR');
	$sh->setCellValue("A{$fila}", 'DÍA');
	$sh->setCellValue("C{$fila}", 'HORA INICIO');
	$sh->setCellValue("D{$fila}", 'HORA TERMINACION');
	$sh->getStyle("A{$fila}:F{$fila}")->applyFromArray([
		'font'    => ['bold' => true, 'size' => 9],
		'fill'    => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => $VERDECL]],
		'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']]],
	]);
	$fila++;
	foreach ($diasSemana as $dia) {
		$hI = $md($dias[$dia]['inicio'] ?? '');
		$hF = $md($dias[$dia]['fin'] ?? '');
		$sh->setCellValue("A{$fila}", $dia);
		$sh->setCellValue("C{$fila}", $hI ?: '---');
		$sh->setCellValue("D{$fila}", $hF ?: '---');
		$sh->getStyle("A{$fila}:F{$fila}")->applyFromArray([
			'font'    => ['size' => 9],
			'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']]],
		]);
		$fila++;
	}
	$fila++;

	// ── Horarios complementarios ──
	$sec('HORARIOS COMPLEMENTARIOS (PROGRAMADOS POR FECHA)');
	if (!empty($horarios) && is_array($horarios)) {
		$sh->setCellValue("A{$fila}", 'FECHA');
		$sh->setCellValue("C{$fila}", 'DÍA');
		$sh->setCellValue("D{$fila}", 'HORA INICIO');
		$sh->setCellValue("E{$fila}", 'HORA TERMINACION');
		$sh->setCellValue("F{$fila}", 'INSTRUCTOR');
		$sh->getStyle("A{$fila}:F{$fila}")->applyFromArray([
			'font'    => ['bold' => true, 'size' => 9],
			'fill'    => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => $VERDECL]],
			'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']]],
		]);
		$fila++;
		foreach ($horarios as $ho) {
			$sh->setCellValue("A{$fila}", $ho['fecha']);
			$sh->setCellValue("C{$fila}", $ho['dia']);
			$sh->setCellValue("D{$fila}", $ho['ini']);
			$sh->setCellValue("E{$fila}", $ho['fin']);
			$sh->setCellValue("F{$fila}", $md($ho['instructor']) ?: 'No asignado');
			$sh->getStyle("A{$fila}:F{$fila}")->applyFromArray([
				'font'    => ['size' => 9],
				'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']]],
			]);
			$fila++;
		}
	} else {
		$sh->mergeCells("A{$fila}:F{$fila}");
		$sh->setCellValue("A{$fila}", 'No se han registrado horarios complementarios para esta hoja de trabajo.');
		$sh->getStyle("A{$fila}:F{$fila}")->applyFromArray(['font' => ['italic' => true, 'size' => 9, 'color' => ['rgb' => '64748B']]]);
		$fila++;
	}
	$fila++;

	// ── Instructor ──
	$sec('DATOS DEL INSTRUCTOR');
	$filDatos([['NOMBRE Y APELLIDO DEL INSTRUCTOR', $md($datos['nombre_instructor'] ?? '') ?: 'Pendiente por asignar']]);
	$filDatos([['DOCUMENTO DEL INSTRUCTOR', $md($datos['documento_instructor'] ?? '')], ['CELULAR DEL INSTRUCTOR', $md($datos['cel_instructor'] ?? '')]]);
	$filDatos([['CORREO DEL INSTRUCTOR', $md($datos['correo_instructor'] ?? '')]]);
	$fila++;

	// ── Controles ──
	$sec('CONTROL Y FIRMAS');
	$filDatos([['CONTROL DEL COORDINADOR', ''], ['CODIGO DE FICHA', $md($datos['codigo_ficha'] ?? '')]]);
	$filDatos([['CONTROL APOYO A LA SUPERVISIÓN', ''], ['CONTROL COMPLEMENTARIA', '']]);
	$fila++;

	$sh->mergeCells("A{$fila}:F{$fila}");
	$sh->setCellValue("A{$fila}", 'Documento oficial de control y gestión académica — SENA CEDA — Generado el ' . date('d/m/Y H:i:s'));
	$sh->getStyle("A{$fila}:F{$fila}")->applyFromArray(['font' => ['italic' => true, 'size' => 8, 'color' => ['rgb' => '64748B']], 'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]]);

	error_reporting($restaurarEr);
	while (ob_get_level()) { ob_end_clean(); }

	header('Content-Type: application/vnd.ms-excel');
	header('Content-Disposition: attachment; filename="HojaTrabajo_#' . $radicado . '_' . date('Ymd') . '.xls"');
	$writer = new \PhpOffice\PhpSpreadsheet\Writer\Xls($sp);
	$writer->save('php://output');
	exit;
}

// ── Flujo principal ──────────────────────────────────────────────────────
$commhtr = new Mhdt();

// Eliminar vía parámetro GET elhr=<id>
$eliHoja = (int)($_GET['elhr'] ?? 0);
if ($eliHoja > 0) {
    $commhtr->setIdnorad($eliHoja);
    $commhtr->del();
    echo '<script>Swal.fire({icon:"success",title:"Hoja de Trabajo eliminada",timer:1800,showConfirmButton:false}).then(function(){window.location="home.php?pg=2004";});</script>';
    exit;
}

// Opciones del programa especial (dom 25) para los checks del reporte.
// Compatibilidad: opcionesProgEsp() puede no existir en el modelo; se usa
// getAllVal(25) como alternativa equivalente (misma forma idval/nomval).
$progEsp  = method_exists($commhtr, 'opcionesProgEsp') ? $commhtr->opcionesProgEsp() : $commhtr->getAllVal(25);

// ── Modo demostración: si hojatra está vacía se ofrecen hojas de ejemplo ──
// (radicados 900001..900006 en memoria, nunca se escriben en la BD)
$datReal = $commhtr->getAll();
$esDemo  = isset($_GET['demo']) ? ((int)$_GET['demo'] === 1) : $commhtr->esHojatraVacia();
$dat     = $datReal;
if (empty($datReal) && $esDemo) {
	$dat = $commhtr->getAllDemo();
}

// Hoja activa: parámetro GET id o idnorad (id de la tabla hojatra)
$_idActivo = (int)($_GET['id'] ?? $_GET['idnorad'] ?? 0);

$datos = [];
$dias  = [];
if ($_idActivo > 0) {
	$rep = $commhtr->reporte($_idActivo);
	$datos = $rep['datos'];
	$dias  = $rep['dias'];
}

// Si no hay hoja seleccionada, cargar la primera registrada
if (empty($datos) && !empty($dat) && is_array($dat) && !empty($dat[0]['idnorad'])) {
	$rep = $commhtr->reporte($dat[0]['idnorad']);
	$datos = $rep['datos'];
	$dias  = $rep['dias'];
	$_idActivo = (int)$dat[0]['idnorad'];
}

// Programa especial seleccionado (id crudo en hojatra) para marcar el check
$peId = 0;
if ($_idActivo > 0 && !$commhtr->esDemo($_idActivo)) {
	try {
		$modelo = new conexion();
		$c = $modelo->get_conexion();
		$st = $c->prepare("SELECT codproesp FROM hojatra WHERE idnorad = :id");
		$st->bindValue(':id', $_idActivo, PDO::PARAM_INT);
		$st->execute();
		$pc = $st->fetch(PDO::FETCH_ASSOC);
		if ($pc) { $peId = (int)$pc['codproesp']; }
	} catch (Exception $e) {
		$peId = 0;
	}
}

// Duración en horas (numérica) para la columna DURACION MAXIMA HORAS
$durNum = 0;
if (!empty($datos['duracion'])) {
	if (preg_match('/(\d+)/', $datos['duracion'], $m)) { $durNum = (int)$m[1]; }
	else { $durNum = (int)$datos['duracion']; }
}

// Formateo del radicado con ceros a la izquierda (Ej: #00025)
$radicadoFormateado = !empty($datos['idnorad']) ? str_pad($datos['idnorad'], 5, '0', STR_PAD_LEFT) : '00000';

// Carga del logo institucional en Base64 (válido para pantalla y dompdf)
$logoBase64 = '';
$posiblesRutasLogo = [
	__DIR__ . '/../img/sena.png',
	__DIR__ . '/img/sena.png',
	'img/sena.png',
	'../img/sena.png',
	__DIR__ . '/../img/senab.png',
];
foreach ($posiblesRutasLogo as $rutaLogo) {
	if (file_exists($rutaLogo)) {
		$logoBin = @file_get_contents($rutaLogo);
		if ($logoBin !== false) {
			$logoBase64 = 'data:image/png;base64,' . base64_encode($logoBin);
			break;
		}
	}
}

// Horarios complementarios (tabla horario, módulo comvhcm.php) de la hoja activa
$horariosComp = ($_idActivo > 0 && !$commhtr->esDemo($_idActivo)) ? comrhtr_horarios($_idActivo) : [];

// ── Exportación directa (PDF / Excel) autocontenida en este archivo ──
$_expFmt = isset($_GET['export']) ? strtolower(trim($_GET['export'])) : '';
if (in_array($_expFmt, ['pdf', 'excel'], true)) {
	if (empty($datos) || empty($datos['idnorad'])) {
		if (ob_get_level()) { ob_clean(); }
		if (!headers_sent()) {
			header('Content-Type: text/html; charset=UTF-8');
		}
		echo '<script>alert("No hay datos de la hoja de trabajo para exportar.");window.history.back();</script>';
		exit;
	}
	if ($_expFmt === 'pdf') {
		comrhtr_exportar_pdf($datos, $dias, $horariosComp, $progEsp, $peId, $logoBase64, $radicadoFormateado);
	}
	comrhtr_exportar_xls($datos, $dias, $horariosComp, $progEsp, $peId, $radicadoFormateado);
}

// Estadísticas para el dashboard (reales o demo)
$est = $commhtr->estadisticas($dat);

// KPI: total de hojas de trabajo
$cnt = [['total' => count($dat)]];

// Conteos para el dashboard
$totHojas       = (int)$cnt[0]['total'];
$totCupos       = $est['totalCupo'];
$totVigentes    = $est['vigentes'];
$totProxVencer  = $est['proximasVencer'];

if (!isset($icono)) $icono = 'fa-solid fa-file-invoice';

// Paleta para las gráficas
$colores = ['#0d701c', '#2f9e44', '#3c627f', '#74c0fc', '#f0a202', '#ba1a1a', '#16a085', '#8e7cc3', '#e8590c', '#495057'];

// URL de exportación directa (este mismo archivo, acceso directo: cabeceras limpias)
$urlExp = function($id, $formato) {
	return 'views/comrhtr.php?id=' . (int)$id . '&export=' . rawurlencode($formato);
};
?>

<div class="conte">
    <?php echo titulo2("<i class='".$icono."'></i> Hoja de Trabajo", 0); ?>

    <!-- Elementos para compatibilidad con ocul() de js/valida.js invocado desde home.php -->
    <div style="display:none !important; width:0; height:0; overflow:hidden; position:absolute;" aria-hidden="true">
        <div id="frmins"></div>
        <span id="mas"></span>
        <span id="menos"></span>
    </div>

    <?php if ($esDemo && empty($datReal)): ?>
    <!-- Banner MODO DEMO -->
    <div class="alert alert-warning d-flex flex-wrap align-items-center gap-2 shadow-sm my-3" style="border-left:6px solid #d97706;">
        <i class="fa-solid fa-triangle-exclamation fa-lg" style="color:#d97706;"></i>
        <div class="flex-grow-1" style="min-width:260px;">
            <strong>MODO DEMO</strong> — No hay hojas de trabajo en la base de datos.
            Se muestran <strong>datos de ejemplo</strong> (radicados 900001–900006, en memoria) para probar
            "Cargar reporte" y la impresión en PDF/Excel. <em>No se guardan en la BD.</em>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="home.php?pg=<?= htmlspecialchars($pg); ?>&demo=0" class="btn btn-sm btn-outline-danger">
                <i class="fa-solid fa-eye-slash"></i> Ocultar ejemplo
            </a>
            <a href="home.php?pg=2011" class="btn btn-sm btn-primary">
                <i class="fa-solid fa-file-circle-plus"></i> Cargar hoja de trabajo
            </a>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($dat)): ?>

    <!-- ===== DASHBOARD DE REPORTES ===== -->
    <div class="my-4">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
            <h2 class="m-0" style="font-size:1.1rem;"><i class="fa-solid fa-chart-pie"></i> Dashboard de Reportes de Hojas de Trabajo</h2>
            <span class="badge rounded-pill text-bg-secondary"><i class="fa-solid fa-database"></i> <?= $esDemo && empty($datReal) ? 'Datos de ejemplo' : 'Datos de la BD'; ?></span>
        </div>

        <style>
            .kpi-clickeable { cursor: pointer; transition: transform .15s ease, box-shadow .15s ease; }
            .kpi-clickeable:hover { transform: translateY(-2px); box-shadow: 0 .6rem 1.2rem rgba(2,6,23,.08); }
            .kpi-card .kpi-icon-box { pointer-events: none; }
        </style>

        <!-- KPI -->
        <div class="row g-4 mb-4">
            <div class="col-12 col-md-6 col-xl-3">
                <div class="kpi-card kpi-clickeable h-100 d-flex flex-column justify-content-between" role="button" onclick="mhrKpi('todas')" title="Mostrar todas las hojas de trabajo">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #3e4a3d;">Hojas Registradas</span>
                            <div class="d-flex align-items-baseline gap-2 mt-2">
                                <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #0b1c30;"><?= $totHojas; ?></span>
                                <span style="font-size: 13px; color: #3e4a3d;">en sistema</span>
                            </div>
                        </div>
                        <div class="kpi-icon-box" style="background-color: #b5dcfe; color: #3c627f;"><i class="fa-solid fa-folder-open fs-4"></i></div>
                    </div>
                    <div class="kpi-footer-strip">
                        <span class="fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 11px; color: #3c627f;">
                            <i class="fa-solid fa-arrow-trend-up" style="font-size: 12px;"></i> Registradas
                        </span>
                        <span style="font-size: 11px; color: #3e4a3d;">Cobertura: 100%</span>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-xl-3">
                <div class="kpi-card kpi-clickeable h-100 d-flex flex-column justify-content-between" role="button" onclick="mhrKpi('vigentes')" title="Filtrar solo hojas vigentes (en ejecución hoy)">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #006b29;">Vigentes</span>
                            <div class="d-flex align-items-baseline gap-2 mt-2">
                                <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #006b29;"><?= $totVigentes; ?></span>
                                <span style="font-size: 13px; color: #006b29;">hoy</span>
                            </div>
                        </div>
                        <div class="kpi-icon-box" style="background-color: #7afd88; color: #006b24;"><i class="fa-solid fa-calendar-check fs-4"></i></div>
                    </div>
                    <div class="kpi-footer-strip">
                        <span class="fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 11px; color: #006b29;">
                            <span class="pulse-dot"></span> En ejecución
                        </span>
                        <span style="font-size: 11px; color: #3e4a3d;">Vigencia activa hoy</span>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-xl-3">
                <div class="kpi-card kpi-clickeable h-100 d-flex flex-column justify-content-between" role="button" onclick="mhrKpi('proximas')" title="Filtrar hojas que vencen en los próximos 30 días">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #f0a202;">Próximas a vencer</span>
                            <div class="d-flex align-items-baseline gap-2 mt-2">
                                <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #b45309;"><?= $totProxVencer; ?></span>
                                <span style="font-size: 13px; color: #b45309;">≤ 30 días</span>
                            </div>
                        </div>
                        <div class="kpi-icon-box" style="background-color: #fde68a; color: #92400e;"><i class="fa-solid fa-hourglass-half fs-4"></i></div>
                    </div>
                    <div class="kpi-footer-strip">
                        <span class="fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 11px; color: #92400e;">
                            <i class="fa-solid fa-clock" style="font-size: 12px;"></i> Por vencer
                        </span>
                        <span style="font-size: 11px; color: #3e4a3d;">Cierre de vigencia</span>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-xl-3">
                <div class="kpi-card kpi-clickeable h-100 d-flex flex-column justify-content-between" role="button" onclick="mhrKpi('todas')" title="Ver todas las hojas de trabajo">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #ba1a1a;">Total Cupo</span>
                            <div class="d-flex align-items-baseline gap-2 mt-2">
                                <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #0b1c30;"><?= number_format($totCupos); ?></span>
                                <span style="font-size: 13px; color: #3e4a3d;">cupos</span>
                            </div>
                        </div>
                        <div class="kpi-icon-box" style="background-color: #ffdad6; color: #ba1a1a;"><i class="fa-solid fa-users fs-4"></i></div>
                    </div>
                    <div class="kpi-footer-strip">
                        <span class="fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 11px; color: #ba1a1a;">
                            <i class="fa-solid fa-user-graduate" style="font-size: 12px;"></i> Autorizados
                        </span>
                        <span style="font-size: 11px; color: #3e4a3d;">Aprendices matriculables</span>
                    </div>
                </div>
            </div>
        </div>

        </div>
    <!-- ===== /DASHBOARD ===== -->

    <!-- ===== TOOLBAR (estructura vvidgen) ===== -->
    <div class="filter-toolbar-container">
        <div class="toolbar-left-group">
            <div class="filter-pills-group" id="mhrPills">
                <button class="btn-filter-pill" id="pill-vigentes" type="button" onclick="mhtrFilter('vigentes')" title="Solo hojas en ejecución hoy">
                    <span class="pill-dot" style="background-color: #006b29;"></span>
                    <span>Vigentes</span>
                    <span class="pill-count"><?= $totVigentes; ?></span>
                </button>
                <button class="btn-filter-pill" id="pill-proximas" type="button" onclick="mhtrFilter('proximas')" title="Hojas que vencen en los próximos 30 días">
                    <span class="pill-dot" style="background-color: #f0a202;"></span>
                    <span>Próximas a vencer</span>
                    <span class="pill-count"><?= $totProxVencer; ?></span>
                </button>
                <button class="btn-filter-pill active" id="pill-todas" type="button" onclick="mhtrFilter('todas')">
                    <span class="pill-dot" style="background-color: #3c627f;"></span>
                    <span>Todas</span>
                    <span class="pill-count"><?= $totHojas; ?></span>
                </button>
            </div>

            <div class="toolbar-divider d-none d-sm-block"></div>

            <div class="toolbar-export-buttons">
                <button type="button" class="toolbar-export-btn" onclick="mhtrExport('copy')" title="Copiar listado al portapapeles">
                    <i class="fa-solid fa-copy"></i>
                </button>
                <button type="button" class="toolbar-export-btn" onclick="mhtrExport('csv')" title="Descargar listado en CSV">
                    <i class="fa-solid fa-file-csv"></i>
                </button>
                <button type="button" class="toolbar-export-btn" onclick="mhtrExport('excel')" title="Descargar listado en Excel">
                    <i class="fa-solid fa-file-excel"></i>
                </button>
                <a href="home.php?pg=<?= htmlspecialchars($pg); ?>" class="toolbar-export-btn" title="Recargar listado">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
                <a href="home.php?pg=2011" class="toolbar-export-btn" title="Registrar / Cargar Hoja de Trabajo" style="color:#0d701c;">
                    <i class="fa-solid fa-file-circle-plus"></i>
                </a>
            </div>
        </div>

        <div class="toolbar-search-sort-group">
            <div class="toolbar-search-box">
                <i class="fa-solid fa-magnifying-glass icoldiz"></i>
                <input type="text" id="buscarHojas" placeholder="Buscar hoja..." autocomplete="off">
            </div>

            <div class="toolbar-secondary-controls">
                <?php if (!empty($dat) && is_array($dat)): ?>
                <select class="toolbar-select-sort" onchange="if(this.value) window.location.href='home.php?pg=<?= htmlspecialchars($pg); ?>&id='+this.value;" title="Consultar otra Hoja de Trabajo">
                    <option value="">Ir a hoja...</option>
                    <?php foreach ($dat as $dh): ?>
                    <option value="<?= (int)$dh['idnorad']; ?>" <?= (!empty($datos['idnorad']) && (int)$dh['idnorad'] === (int)$datos['idnorad']) ? 'selected' : ''; ?>>
                        #<?= str_pad($dh['idnorad'], 5, '0', STR_PAD_LEFT); ?> — <?= htmlspecialchars(mb_strimwidth($dh['nompro'] ?? $dh['codpro'] ?? '', 0, 30, '...')); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <?php endif; ?>

                <?php if (!empty($datos['idnorad'])): ?>
                <a href="#reporteOficial" class="toolbar-export-btn" title="Ver reporte cargado abajo">
                    <i class="fa-solid fa-file-lines"></i>
                </a>
                <a href="<?= $urlExp($datos['idnorad'], 'pdf'); ?>" target="_blank" class="toolbar-export-btn" title="Descargar PDF (dompdf) — pestaña nueva" style="color:#ba1a1a;">
                    <i class="fa-solid fa-file-pdf"></i>
                </a>
                <a href="<?= $urlExp($datos['idnorad'], 'excel'); ?>" target="_blank" class="toolbar-export-btn" title="Descargar Excel (PhpSpreadsheet) — pestaña nueva" style="color:#006b29;">
                    <i class="fa-solid fa-file-excel"></i>
                </a>
                <?php endif; ?>

                <div class="toolbar-view-toggle">
                    <button type="button" class="toolbar-view-btn active" onclick="window.print()" title="Imprimir Reporte Oficial">
                        <i class="fa-solid fa-print"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- ===== /TOOLBAR ===== -->

    <!-- ===== LISTADO DE HOJAS (botón "Cargar reporte" por fila) ===== -->
    <div class="inser mt-3">
        <h2><i class="fa-solid fa-list"></i> Consultar / Cargar Hoja de Trabajo</h2>
        <div class="table-responsive" style="margin-top:12px;">
            <table id="tblHojas" class="table table-striped table-hover align-middle" style="width:100%">
                <thead>
                    <tr>
                        <th>N°</th>
                        <th>Programa</th>
                        <th>Empresa</th>
                        <th>Inicio</th>
                        <th>Fin</th>
                        <th>Cupo</th>
                        <th>Jornada</th>
                        <th>Ficha</th>
                        <th style="width:220px;">Acciones</th>
                        <th class="d-none">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($dat && is_array($dat)): foreach ($dat as $dh):
                    $hoyR  = date('Y-m-d');
                    $h30R  = date('Y-m-d', strtotime('+30 days'));
                    $iniR  = (string)($dh['feclini'] ?? '');
                    $finR  = (string)($dh['feclin'] ?? '');
                    $estFila = 'otra';
                    if ($iniR !== '' && $finR !== '' && $iniR <= $hoyR && $finR >= $hoyR) $estFila = 'vigente';
                    elseif ($finR !== '' && $finR >= $hoyR && $finR <= $h30R) $estFila = 'proxima';
                    ?>
                    <tr data-est="<?= $estFila; ?>">
                        <td class="fw-bold">#<?= str_pad($dh['idnorad'], 5, '0', STR_PAD_LEFT); ?>
                            <?php if ($esDemo && empty($datReal) && $commhtr->esDemo($dh['idnorad'])): ?>
                            <span class="badge text-bg-warning" style="font-size:9px;">demo</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="text-muted d-block" style="font-size:11px;"><?= htmlspecialchars($dh['codpro'] ?? ''); ?></span>
                            <?= htmlspecialchars(mb_strimwidth($dh['nompro'] ?? '---', 0, 45, '...')); ?>
                        </td>
                        <td><?= htmlspecialchars(mb_strimwidth($dh['nomemp'] ?? '---', 0, 30, '...')); ?></td>
                        <td><?= htmlspecialchars($dh['feclini'] ?? '---'); ?></td>
                        <td><?= htmlspecialchars($dh['feclin'] ?? '---'); ?></td>
                        <td class="text-center"><?= (int)($dh['cupo'] ?? 0); ?></td>
                        <td><?= htmlspecialchars(!empty($dh['jorn']) ? $dh['jorn'] : ($dh['jornada'] ?? '---')); ?></td>
                        <td><?= htmlspecialchars($dh['idfic'] ?? '---'); ?></td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="home.php?pg=<?= htmlspecialchars($pg); ?>&id=<?= (int)$dh['idnorad']; ?>#reporteOficial"
                                   class="btn btn-sm btn-primary" title="Cargar el reporte de esta hoja">
                                    <i class="fa-solid fa-folder-open"></i> Cargar reporte
                                </a>
                                <?php if (!$esDemo || empty($datReal)): ?>
                                <a href="home.php?pg=2011&edhr=<?= (int)$dh['idnorad']; ?>"
                                   class="btn btn-sm btn-outline-secondary" title="Editar esta hoja">
                                    <i class="fa-solid fa-pen"></i>
                                </a>
                                <a href="home.php?pg=<?= htmlspecialchars($pg); ?>&elhr=<?= (int)$dh['idnorad']; ?>"
                                   class="btn btn-sm btn-outline-danger" title="Eliminar esta hoja" onclick="return confEliHoja('<?= (int)$dh['idnorad']; ?>');">
                                    <i class="fa-solid fa-trash"></i>
                                </a>
                                <?php endif; ?>
                                <a href="<?= $urlExp($dh['idnorad'], 'pdf'); ?>" target="_blank" class="btn btn-sm btn-outline-danger" title="PDF">
                                    <i class="fa-solid fa-file-pdf"></i>
                                </a>
                                <a href="<?= $urlExp($dh['idnorad'], 'excel'); ?>" target="_blank" class="btn btn-sm btn-outline-success" title="Excel">
                                    <i class="fa-solid fa-file-excel"></i>
                                </a>
                            </div>
                        </td>
                        <td class="d-none"><?= $estFila; ?></td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <!-- ===== /LISTADO ===== -->

    <?php if (!empty($datos)): ?>

    <!-- ===== DOCUMENTO OFICIAL (reporte cargado dentro de la vista) ===== -->
    <style>
        @media print {
            body * { visibility: hidden; }
            #reporteOficial, #reporteOficial * { visibility: visible; }
            #reporteOficial { position: absolute; left: 0; top: 0; width: 100%; }
        }
    </style>

    <div class="inser mt-3 htrdoc" id="reporteOficial">
        <div class="filter-toolbar-container">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                <h2 style="margin:0;"><i class="fa-solid fa-file-invoice"></i> Reporte Hoja de Trabajo #<?= htmlspecialchars($radicadoFormateado); ?>
                    <?php if ($esDemo && empty($datReal) && $commhtr->esDemo($datos['idnorad'] ?? 0)): ?>
                    <span class="badge text-bg-warning" style="font-size:10px;">demo</span>
                    <?php endif; ?>
                </h2>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="<?= $urlExp($datos['idnorad'], 'pdf'); ?>" target="_blank" class="btn btn-sm btn-danger" title="Descargar PDF con todos los datos de la hoja y horarios complementarios">
                        <i class="fa-solid fa-file-pdf"></i> PDF
                    </a>
                    <a href="<?= $urlExp($datos['idnorad'], 'excel'); ?>" target="_blank" class="btn btn-sm btn-success" title="Descargar Excel con todos los datos de la hoja y horarios complementarios">
                        <i class="fa-solid fa-file-excel"></i> Excel
                    </a>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()" title="Imprimir el Reporte Oficial">
                        <i class="fa-solid fa-print"></i> Imprimir
                    </button>
                </div>
            </div>

            <?php echo comrhtr_doc_html($datos, $dias, $horariosComp, [
                'progEsp'      => $progEsp,
                'codproesp_id' => $peId,
                'durNum'       => $durNum,
                'logoBase64'   => $logoBase64,
                'radicado'     => $radicadoFormateado,
            ]); ?>

        </div><!-- /.filter-toolbar-container -->
    </div><!-- /.inser -->
    <!-- ===== /DOCUMENTO OFICIAL ===== -->

    <?php endif; ?>

    <?php else: ?>

    <!-- Estado vacío -->
    <div class="filter-toolbar-container text-center mb-3">
        <div class="w-100 py-5">
            <i class="fa-regular fa-folder-open fa-3x text-muted mb-3 d-block"></i>
            <h4 class="fw-bold">No hay Hojas de Trabajo disponibles</h4>
            <p class="text-muted mx-auto" style="max-width:520px;">
                No se encontraron registros de solicitudes u Hojas de Trabajo en el sistema para generar el reporte oficial.
                Puedes <strong>cargar hojas reales</strong> desde el módulo de carga, o ver la aplicación con
                <strong>datos de ejemplo</strong> para probar "Cargar reporte" y la impresión en PDF/Excel.
            </p>
            <div class="d-flex justify-content-center gap-2 flex-wrap mt-3">
                <a href="home.php?pg=<?= htmlspecialchars($pg); ?>&demo=1" class="btn btn-outline-primary">
                    <i class="fa-solid fa-wand-magic-sparkles"></i> Ver con datos de ejemplo
                </a>
                <a href="home.php?pg=<?= htmlspecialchars($pg); ?>" class="btn btn-primary">
                    <i class="fa-solid fa-file-circle-plus"></i> Cargar Hoja de Trabajo
                </a>
            </div>
        </div>
    </div>

    <?php endif; ?>
</div>

<script>
$(document).ready(function(){
    // ── DataTable del listado ──
    if (typeof $.fn.DataTable !== 'undefined') {
        var $tbl = $('#tblHojas');
        if ($tbl.length && !$.fn.DataTable.isDataTable($tbl)) {
            var hasButtons = ($.fn.dataTable && $.fn.dataTable.ext) ? (typeof $.fn.dataTable.ext.buttons !== 'undefined') : (typeof $.fn.DataTable.Buttons !== 'undefined');
            var cfg = {
                "language": {
                    "lengthMenu": "Mostrar _MENU_ registros",
                    "zeroRecords": "No se encontraron resultados",
                    "info": "Mostrando _START_ a _END_ de _TOTAL_ hojas",
                    "infoEmpty": "0 hojas disponibles",
                    "infoFiltered": "(filtrado de _MAX_ registros)",
                    "sSearch": "Buscar:",
                    "oPaginate": { "sFirst": "Primero", "sLast": "Último", "sNext": "Siguiente", "sPrevious": "Anterior" }
                },
                "order": [[0, "desc"]],
                "pageLength": 10,
                "lengthMenu": [[5, 10, 25, 50, -1], [5, 10, 25, 50, "Todos"]],
                "dom": '<"row mb-2"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>><"d-none"B>rt<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
                "columnDefs": [
                    { "targets": 9, "visible": false, "searchable": true, "orderable": false }
                ]
            };
            if (hasButtons) {
                cfg.buttons = [
                    { extend: 'copyHtml5', title: 'HojasTrabajo' },
                    { extend: 'csvHtml5', title: 'HojasTrabajo' },
                    { extend: 'excelHtml5', title: 'HojasTrabajo' }
                ];
            }
            $tbl.DataTable(cfg);
        }
    }

    // Botones de exportación del listado (Copy / CSV / Excel)
    window.mhtrExport = function(type){
        if (typeof $.fn.DataTable === 'undefined') return;
        var $t = $('#tblHojas');
        if (!$t.length || !$.fn.DataTable.isDataTable($t)) return;
        var dt = $t.DataTable();
        var sel = type === 'copy' ? '.buttons-copy' : (type === 'csv' ? '.buttons-csv' : '.buttons-excel');
        var btn = dt.button(sel);
        if (btn && btn.length) btn.trigger();
    };

    // Píldoras de estado: filtra la columna oculta 9 del DataTable
    window.mhtrFilter = function(tipo){
        $('#mhrPills .btn-filter-pill').removeClass('active');
        var pil = document.getElementById('pill-' + tipo);
        if (pil) pil.classList.add('active');
        if (typeof $.fn.DataTable === 'undefined') return;
        var $t = $('#tblHojas');
        if (!$t.length || !$.fn.DataTable.isDataTable($t)) return;
        $('#tblHojas').DataTable().column(9).search(tipo === 'todas' ? '' : tipo).draw();
    };

    // KPIs interactivos: al hacer clic aplica el filtro y baja al listado
    window.mhrKpi = function(tipo){
        mhtrFilter(tipo);
        var tabla = document.getElementById('tblHojas');
        if (tabla && tabla.closest && tabla.closest('.inser')) {
            tabla.closest('.inser').scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    };

    // Confirmación de eliminación (visto aquí para no depender de js/valida.js)
    window.confEliHoja = function(id){
        if (typeof Swal === 'undefined') return confirm('¿Eliminar la Hoja de Trabajo?');
        Swal.fire({
            title: '¿Eliminar Hoja de Trabajo?',
            html: 'Se eliminará la hoja <strong>#' + String(id).padStart(5, '0') + '</strong> y sus horarios. Esta acción no se puede deshacer.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonText: 'Cancelar',
            confirmButtonText: 'Sí, eliminar'
        }).then(function(r){ if (r.isConfirmed) window.location.href = 'home.php?pg=<?= htmlspecialchars($pg); ?>&elhr=' + id; });
        return false;
    };

    // Buscador del toolbar sincronizado con el DataTable
    $('#buscarHojas').on('input keyup change paste', function(){
        if (typeof $.fn.DataTable !== 'undefined' && $.fn.DataTable.isDataTable($('#tblHojas'))) {
            $('#tblHojas').DataTable().search(this.value).draw();
        }
    });
});
</script>