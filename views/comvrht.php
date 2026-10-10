<?php
if (!defined('COMVHTR_RUN')) {
	define('COMVHTR_RUN', true);
}

require_once 'controllers/comchtr.php';

if (empty($datos) && !empty($dat) && is_array($dat)) {
	$primeraHoja = $dat[0];
	$commhtr->setIdnorad($primeraHoja['idnorad']);
	$datOne = $commhtr->getOne();
	if (!empty($datOne)) {
		$res = $datOne[0];
		$datos = [
			'idnorad' => $res['idnorad'],
			'codpro' => $res['codpro'],
			'idemp' => $res['idemp'],
			'codproesp' => !empty($res['proesp']) ? $res['proesp'] : $res['codproesp'],
			'codslem' => !empty($res['codslem']) ? $res['codslem'] : '',
			'feclini' => !empty($res['feclini']) ? $res['feclini'] : '',
			'feclin' => !empty($res['feclin']) ? $res['feclin'] : '',
			'fecha_inicio' => !empty($res['feclini']) ? $res['feclini'] : '',
			'fecha_fin' => !empty($res['feclin']) ? $res['feclin'] : '',
			'cupo' => $res['cupo'],
			'jornada' => !empty($res['nomjornada']) ? $res['nomjornada'] : $res['jornada'],
			'codigo_ficha' => !empty($res['idfic']) ? $res['idfic'] : '',
			'idfic' => !empty($res['idfic']) ? $res['idfic'] : '',
			'nomfic' => !empty($res['nomfic']) ? $res['nomfic'] : '',
			'nompro' => !empty($res['nompro']) ? $res['nompro'] : '',
			'nomemp' => !empty($res['nomemp']) ? $res['nomemp'] : '',
			'numdocemp' => !empty($res['numdocemp']) ? $res['numdocemp'] : '',
			'convenio' => !empty($res['convht']) ? $res['convht'] : '',
			'convht' => !empty($res['convht']) ? $res['convht'] : '',
			'duracion' => !empty($res['horlpro']) ? ($res['horlpro'] . ' horas') : '',
			'direccion' => !empty($res['diremp']) ? $res['diremp'] : '',
			'municipio' => !empty($res['nommun']) ? $res['nommun'] : '',
			'contacto' => !empty($res['nomconemp']) ? $res['nomconemp'] : '',
			'telefono' => !empty($res['telemp']) ? $res['telemp'] : '',
			'correo_empresa' => !empty($res['coremp']) ? $res['coremp'] : '',
			'dias_formacion_json' => '',
			'nombre_instructor' => !empty($res['nombre_instructor']) ? $res['nombre_instructor'] : '',
			'documento_instructor' => !empty($res['documento_instructor']) ? $res['documento_instructor'] : '',
			'cel_instructor' => !empty($res['cel_instructor']) ? $res['cel_instructor'] : '',
			'correo_instructor' => !empty($res['correo_instructor']) ? $res['correo_instructor'] : ''
		];
	}
}

// Configuración de matriz de días semanales
$dias_semana = ['LUNES', 'MARTES', 'MIÉRCOLES', 'JUEVES', 'VIERNES', 'SÁBADO', 'DOMINGO'];
$dias = [];
if (!empty($datos['dias_formacion_json'])) {
	$decoded = json_decode($datos['dias_formacion_json'], true);
	if (is_array($decoded)) {
		$dias = $decoded;
	}
}

// Carga del logo institucional en Base64 para garantizar fidelidad en impresión y exportación
$logoBase64 = '';
$posiblesRutasLogo = [
	__DIR__ . '/../img/logoSena.png',
	__DIR__ . '/img/logoSena.png',
	'img/logoSena.png',
	'../img/logoSena.png'
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

// Formateo del radicado con ceros a la izquierda (Ej: #00025)
$radicadoFormateado = !empty($datos['idnorad']) ? str_pad($datos['idnorad'], 5, '0', STR_PAD_LEFT) : '00000';
?>

<style>
	:root {
		--sena-green: #0d701c;
		--sena-green-hover: #094e13;
		--sena-green-light: #e8f5e9;
		--sena-navy: #1b365d;
		--sena-navy-hover: #0f172a;
		--sena-border: #000000;
		--sena-border-light: #cbd5e1;
		--sena-bg-table-th: #f1f5f9;
		--sena-bg-table-alt: #f8fafc;
	}

	/* Barra superior de herramientas y navegación (Solo en Pantalla) */
	.action-bar {
		max-width: 960px;
		margin: 0 auto 20px auto;
		display: flex;
		justify-content: space-between;
		align-items: center;
		background: #ffffff;
		padding: 12px 20px;
		border-radius: 10px;
		box-shadow: 0 4px 14px rgba(0, 0, 0, 0.08);
		border: 1px solid #e2e8f0;
		flex-wrap: wrap;
		gap: 12px;
	}
	.action-bar-title {
		font-size: 1.05rem;
		font-weight: 800;
		color: var(--sena-navy);
		display: flex;
		align-items: center;
		gap: 10px;
	}
	.action-bar-title i {
		color: var(--sena-green);
		font-size: 1.25rem;
	}
	.action-tools {
		display: flex;
		align-items: center;
		gap: 10px;
		flex-wrap: wrap;
	}
	.select-hoja-rapido {
		height: 38px;
		font-size: 0.86rem;
		font-weight: 600;
		border: 1.5px solid #cbd5e1;
		border-radius: 6px;
		padding: 4px 10px;
		background-color: #f8fafc;
		color: #1e293b;
		max-width: 290px;
		outline: none;
		transition: all 0.2s ease;
	}
	.select-hoja-rapido:focus {
		border-color: var(--sena-green);
		background-color: #ffffff;
		box-shadow: 0 0 0 3px rgba(13, 112, 28, 0.15);
	}
	.btn-reporte-action {
		display: inline-flex;
		align-items: center;
		gap: 8px;
		padding: 8px 18px;
		font-size: 0.88rem;
		font-weight: 700;
		border-radius: 6px;
		text-decoration: none;
		cursor: pointer;
		transition: all 0.2s ease;
		border: none;
	}
	.btn-reporte-print {
		background-color: var(--sena-green);
		color: #ffffff !important;
		box-shadow: 0 2px 8px rgba(13, 112, 28, 0.3);
	}
	.btn-reporte-print:hover {
		background-color: var(--sena-green-hover);
		transform: translateY(-1px);
		box-shadow: 0 4px 12px rgba(13, 112, 28, 0.4);
	}
	.btn-reporte-back {
		background-color: var(--sena-navy);
		color: #ffffff !important;
	}
	.btn-reporte-back:hover {
		background-color: var(--sena-navy-hover);
		transform: translateY(-1px);
	}

	/* Contenedor Principal del Formato Oficial SENA */
	.hoja-documento {
		max-width: 960px;
		margin: 0 auto 30px auto;
		background: #ffffff;
		border: 2px solid var(--sena-border);
		padding: 26px 30px;
		box-shadow: 0 10px 25px rgba(0, 0, 0, 0.10);
		box-sizing: border-box;
	}

	/* Encabezado Institucional */
	.doc-header-table {
		width: 100%;
		border-collapse: collapse;
		margin-bottom: 14px;
		border-bottom: 2px solid var(--sena-border);
		padding-bottom: 10px;
	}
	.doc-header-table td {
		vertical-align: middle;
		border: none;
		padding: 4px;
	}
	.doc-header-logo {
		width: 105px;
		text-align: center;
	}
	.doc-header-logo img {
		max-width: 90px;
		max-height: 80px;
		object-fit: contain;
	}
	.doc-header-text {
		text-align: center;
		padding: 0 12px;
	}
	.doc-main-inst {
		font-size: 14px;
		font-weight: 900;
		letter-spacing: 0.5px;
		color: #000000;
		text-transform: uppercase;
		line-height: 1.25;
	}
	.doc-sub-inst {
		font-size: 12px;
		font-weight: 800;
		color: var(--sena-green);
		margin-top: 3px;
		text-transform: uppercase;
		line-height: 1.25;
	}
	.doc-doc-title {
		font-size: 13.5px;
		font-weight: 900;
		letter-spacing: 0.8px;
		margin-top: 5px;
		text-transform: uppercase;
		color: #000000;
		line-height: 1.2;
	}
	.doc-doc-subtitle {
		font-size: 11px;
		font-weight: 700;
		color: #475569;
		letter-spacing: 0.3px;
		text-transform: uppercase;
		margin-top: 2px;
		line-height: 1.2;
	}
	.doc-header-meta {
		width: 190px;
		text-align: right;
	}
	.meta-radicado-card {
		border: 2px solid var(--sena-border);
		background-color: #f8fafc;
		padding: 8px 10px;
		text-align: center;
		border-radius: 4px;
	}
	.meta-radicado-label {
		font-size: 9.5px;
		font-weight: 800;
		text-transform: uppercase;
		color: #475569;
		letter-spacing: 0.5px;
	}
	.meta-radicado-num {
		font-size: 18px;
		font-weight: 900;
		color: var(--sena-green);
		margin: 2px 0;
		font-family: Consolas, monospace;
	}
	.meta-fecha-gen {
		font-size: 10px;
		color: #475569;
		font-weight: 700;
		border-top: 1px dashed var(--sena-border-light);
		padding-top: 4px;
		margin-top: 4px;
	}

	/* Títulos de Sección */
	.seccion-titulo {
		background: var(--sena-bg-table-th);
		border: 1px solid var(--sena-border);
		border-left: 6px solid var(--sena-green);
		padding: 5px 8px;
		font-size: 11.5px;
		font-weight: 900;
		text-transform: uppercase;
		letter-spacing: 0.5px;
		color: #000000;
		margin-top: 12px;
		margin-bottom: 5px;
		page-break-after: avoid;
	}

	/* Tablas de Información Estructurada */
	.doc-table {
		width: 100%;
		border-collapse: collapse;
		margin-bottom: 8px;
		table-layout: fixed;
	}
	.doc-table td, 
	.doc-table th {
		border: 1px solid var(--sena-border);
		padding: 5px 7px;
		font-size: 11px;
		line-height: 1.35;
		vertical-align: top;
	}
	.td-label {
		background-color: var(--sena-bg-table-alt);
		font-weight: 700;
		width: 22%;
		color: #1e293b;
	}
	.td-val {
		width: 28%;
		color: #000000;
		word-break: break-word;
	}
	.td-val-wide {
		width: 78%;
		color: #000000;
		word-break: break-word;
	}
	.td-highlight {
		font-weight: 700;
		color: #000000;
	}

	/* Tabla Matricial de Días y Horarios */
	.horario-table {
		width: 100%;
		border-collapse: collapse;
		margin-top: 5px;
		margin-bottom: 10px;
		table-layout: fixed;
	}
	.horario-table th {
		background-color: var(--sena-bg-table-th);
		border: 1px solid var(--sena-border);
		padding: 5px 6px;
		font-size: 10.5px;
		font-weight: 900;
		text-align: center;
		text-transform: uppercase;
		color: #000000;
	}
	.horario-table td {
		border: 1px solid var(--sena-border);
		padding: 4px 6px;
		font-size: 10.5px;
		text-align: center;
	}
	.horario-dia-nom {
		font-weight: 700;
		text-align: left !important;
		background-color: var(--sena-bg-table-alt);
		width: 28%;
	}

	/* Cuadro de Observaciones */
	.observaciones-box {
		border: 1px solid var(--sena-border);
		padding: 8px 10px;
		font-size: 10.5px;
		min-height: 42px;
		background-color: #ffffff;
		color: #1e293b;
		text-align: justify;
		line-height: 1.4;
	}

	/* Bloque de Firmas */
	.firmas-container {
		display: flex;
		justify-content: space-between;
		gap: 16px;
		margin-top: 24px;
		margin-bottom: 8px;
		page-break-inside: avoid;
	}
	.firma-col {
		flex: 1;
		text-align: center;
	}
	.firma-linea {
		border-top: 1.5px solid var(--sena-border);
		margin: 36px auto 6px auto;
		width: 85%;
	}
	.firma-titulo {
		font-size: 10.5px;
		font-weight: 800;
		color: #000000;
		text-transform: uppercase;
	}
	.firma-subtitulo {
		font-size: 9.5px;
		color: #475569;
		font-weight: 600;
	}

	/* Pie de Página Institucional */
	.doc-footer-meta {
		text-align: center;
		font-size: 9px;
		color: #64748b;
		margin-top: 14px;
		border-top: 1px solid var(--sena-border-light);
		padding-top: 5px;
	}

	/* Badge institucional */
	.badge-estado-doc {
		display: inline-block;
		padding: 2px 7px;
		font-size: 10px;
		font-weight: 800;
		background-color: #dcfce7;
		color: #166534;
		border: 1px solid #bbf7d0;
		border-radius: 3px;
	}

	/* Mensaje de ausencia de registros */
	.empty-state-card {
		max-width: 960px;
		margin: 40px auto;
		background: #ffffff;
		border: 1.5px solid #e2e8f0;
		border-radius: 12px;
		padding: 40px 20px;
		text-align: center;
		box-shadow: 0 4px 12px rgba(0,0,0,0.05);
	}
	.empty-state-icon {
		font-size: 3rem;
		color: #94a3b8;
		margin-bottom: 16px;
	}

	/* Reglas de Impresión Oficial (Tamaño Carta / A4) */
	@media print {
		@page {
			size: letter portrait;
			margin: 10mm 12mm;
		}
		html, body {
			background-color: #ffffff !important;
			padding: 0 !important;
			margin: 0 !important;
			color: #000000 !important;
			-webkit-print-color-adjust: exact !important;
			print-color-adjust: exact !important;
		}
		.action-bar,
		.no-print {
			display: none !important;
		}
		.hoja-documento {
			border: 1.5px solid #000000 !important;
			padding: 8px 12px !important;
			box-shadow: none !important;
			max-width: 100% !important;
			width: 100% !important;
			margin: 0 !important;
			page-break-inside: auto;
		}
		.doc-header-table {
			margin-bottom: 8px !important;
			border-bottom: 1.5px solid #000000 !important;
		}
		.seccion-titulo {
			background: #f1f5f9 !important;
			border: 1px solid #000000 !important;
			border-left: 5px solid #0d701c !important;
			-webkit-print-color-adjust: exact !important;
			print-color-adjust: exact !important;
			margin-top: 8px !important;
			margin-bottom: 4px !important;
			padding: 4px 6px !important;
			font-size: 10.5px !important;
		}
		.doc-table td, 
		.doc-table th,
		.horario-table td,
		.horario-table th {
			border: 1px solid #000000 !important;
			padding: 3px 5px !important;
			font-size: 9.5px !important;
			line-height: 1.25 !important;
		}
		.horario-table {
			margin-top: 3px !important;
			margin-bottom: 6px !important;
		}
		.observaciones-box {
			border: 1px solid #000000 !important;
			font-size: 9px !important;
			min-height: 36px !important;
			padding: 5px 8px !important;
			line-height: 1.3 !important;
		}
		.meta-radicado-card {
			border: 1.5px solid #000000 !important;
			background-color: #ffffff !important;
			padding: 4px 6px !important;
		}
		.meta-radicado-num {
			font-size: 15px !important;
		}
		.firmas-container {
			margin-top: 18px !important;
			margin-bottom: 4px !important;
			page-break-inside: avoid !important;
		}
		.firma-linea {
			margin-top: 26px !important;
			border-top: 1.5px solid #000000 !important;
		}
		.firma-titulo {
			font-size: 9.5px !important;
		}
		.firma-subtitulo {
			font-size: 8.5px !important;
		}
		.doc-footer-meta {
			margin-top: 8px !important;
			font-size: 8px !important;
		}
	}

	/* Adaptabilidad en Dispositivos Móviles */
	@media (max-width: 768px) {
		.hoja-documento {
			padding: 14px 10px;
		}
		.action-bar {
			flex-direction: column;
			align-items: stretch;
			text-align: center;
		}
		.action-tools {
			justify-content: center;
		}
		.select-hoja-rapido {
			max-width: 100%;
		}
		.doc-header-table td {
			display: block;
			width: 100% !important;
			text-align: center !important;
		}
		.meta-radicado-card {
			margin-top: 10px;
		}
		.td-label, .td-val, .td-val-wide {
			display: block;
			width: 100% !important;
		}
		.firmas-container {
			flex-direction: column;
			gap: 14px;
		}
		.firma-linea {
			margin-top: 20px;
		}
	}
</style>

<!-- Barra Superior de Acciones (Solo en Pantalla - Oculta al Imprimir) -->
<div class="action-bar no-print">
	<div class="action-bar-title">
		<i class="fa-solid fa-file-invoice"></i>
		<span>Reporte Hoja de Trabajo #<?= htmlspecialchars($radicadoFormateado); ?></span>
	</div>
	<div class="action-tools">
		<!-- Selector para consultar rápidamente cualquier hoja de trabajo registrada -->
		<?php if(!empty($dat) && is_array($dat)): ?>
			<select class="select-hoja-rapido" onchange="if(this.value) window.location.href='home.php?pg=2010&id='+this.value;" title="Seleccione una hoja de trabajo para consultar">
				<option value="">🔍 Consultar otra Hoja...</option>
				<?php foreach($dat as $dh): ?>
					<option value="<?= (int)$dh['idnorad']; ?>" <?= (!empty($datos['idnorad']) && $dh['idnorad'] == $datos['idnorad']) ? 'selected' : ''; ?>>
						#<?= str_pad($dh['idnorad'], 5, '0', STR_PAD_LEFT); ?> — <?= htmlspecialchars(mb_strimwidth($dh['nompro'] ?? $dh['codpro'], 0, 30, '...')); ?>
					</option>
				<?php endforeach; ?>
			</select>
		<?php endif; ?>

		<a class="btn-reporte-action btn-reporte-back" href="home.php?pg=2004" title="Regresar al módulo de gestión de Hojas de Trabajo">
			<i class="fa-solid fa-arrow-left"></i> <span>Volver al listado</span>
		</a>

		<button type="button" class="btn-reporte-action btn-reporte-print" onclick="window.print()" title="Imprimir documento oficial en tamaño Carta o A4">
			<i class="fa-solid fa-print"></i> <span>Imprimir Reporte</span>
		</button>
	</div>
</div>

<?php if (!empty($datos)): ?>
	<!-- Documento Oficial SENA -->
	<div class="hoja-documento">
		<!-- Encabezado Institucional -->
		<table class="doc-header-table">
			<tr>
				<td class="doc-header-logo">
					<?php if (!empty($logoBase64)): ?>
						<img src="<?= $logoBase64; ?>" alt="Logo SENA">
					<?php elseif (file_exists('img/logoSena.png')): ?>
						<img src="img/logoSena.png" alt="Logo SENA">
					<?php elseif (file_exists('../img/logoSena.png')): ?>
						<img src="../img/logoSena.png" alt="Logo SENA">
					<?php else: ?>
						<div style="font-weight:900; color:var(--sena-green); font-size:22px;">SENA</div>
					<?php endif; ?>
				</td>
				<td class="doc-header-text">
					<div class="doc-main-inst">SERVICIO NACIONAL DE APRENDIZAJE – SENA | CENTRO DE DESARROLLO AGROEMPRESARIAL</div>
					<div class="doc-sub-inst">REGIONAL CUNDINAMARCA — SEDE CHÍA / SEDES ADSCRITAS</div>
					<div class="doc-doc-title">HOJA DE TRABAJO — SOLICITUD DE CURSOS DE FORMACIÓN COMPLEMENTARIA - TITULADA</div>
					<div class="doc-doc-subtitle">DOCUMENTO INSTITUCIONAL DE PROGRAMACIÓN ACADÉMICA Y CONTROL DE GESTIÓN</div>
				</td>
				<td class="doc-header-meta">
					<div class="meta-radicado-card">
						<div class="meta-radicado-label">Radicado Oficial</div>
						<div class="meta-radicado-num">#<?= htmlspecialchars($radicadoFormateado); ?></div>
						<div class="meta-fecha-gen">
							Expedición: <?= date('d/m/Y'); ?>
						</div>
					</div>
				</td>
			</tr>
		</table>

		<!-- Sección 1: Información General del Programa -->
		<div class="seccion-titulo">1. Información General del Programa</div>
		<table class="doc-table">
			<tr>
				<td class="td-label">Radicado Oficial:</td>
				<td class="td-val td-highlight">#<?= htmlspecialchars($radicadoFormateado); ?></td>
				<td class="td-label">Código del Programa:</td>
				<td class="td-val td-highlight"><?= htmlspecialchars(!empty($datos['codpro']) ? $datos['codpro'] : '---'); ?></td>
			</tr>
			<tr>
				<td class="td-label">Nombre del Programa:</td>
				<td class="td-val-wide" colspan="3">
					<strong><?= htmlspecialchars(!empty($datos['nompro']) ? $datos['nompro'] : '---'); ?></strong>
				</td>
			</tr>
			<tr>
				<td class="td-label">Duración Estimada:</td>
				<td class="td-val"><?= htmlspecialchars(!empty($datos['duracion']) ? $datos['duracion'] : '---'); ?></td>
				<td class="td-label">Programa Especial:</td>
				<td class="td-val"><?= htmlspecialchars(!empty($datos['codproesp']) ? $datos['codproesp'] : 'Regular / Ninguno'); ?></td>
			</tr>
			<tr>
				<td class="td-label">Jornada de Formación:</td>
				<td class="td-val"><?= htmlspecialchars(!empty($datos['jornada']) ? $datos['jornada'] : '---'); ?></td>
				<td class="td-label">Código Solicitud Empresa:</td>
				<td class="td-val"><?= htmlspecialchars(!empty($datos['codslem']) ? $datos['codslem'] : '---'); ?></td>
			</tr>
			<tr>
				<td class="td-label">Convenio Asociado:</td>
				<td class="td-val-wide" colspan="3"><?= htmlspecialchars(!empty($datos['convenio']) ? $datos['convenio'] : 'No aplica / Sin convenio específico'); ?></td>
			</tr>
		</table>

		<!-- Sección 2: Datos de la Entidad Solicitante / Empresario -->
		<div class="seccion-titulo">2. Datos de la Entidad Solicitante / Empresario</div>
		<table class="doc-table">
			<tr>
				<td class="td-label">Razón Social / Empresa:</td>
				<td class="td-val-wide" colspan="3">
					<strong><?= htmlspecialchars(!empty($datos['nomemp']) ? $datos['nomemp'] : '---'); ?></strong>
				</td>
			</tr>
			<tr>
				<td class="td-label">NIT / Identificación:</td>
				<td class="td-val"><?= htmlspecialchars(!empty($datos['numdocemp']) ? $datos['numdocemp'] : '---'); ?></td>
				<td class="td-label">Municipio de Formación:</td>
				<td class="td-val"><?= htmlspecialchars(!empty($datos['municipio']) ? $datos['municipio'] : '---'); ?></td>
			</tr>
			<tr>
				<td class="td-label">Dirección del Lugar:</td>
				<td class="td-val"><?= htmlspecialchars(!empty($datos['direccion']) ? $datos['direccion'] : 'Instalaciones de la entidad / Sede asignada'); ?></td>
				<td class="td-label">Persona de Contacto:</td>
				<td class="td-val"><?= htmlspecialchars(!empty($datos['contacto']) ? $datos['contacto'] : '---'); ?></td>
			</tr>
			<tr>
				<td class="td-label">Teléfono / Celular:</td>
				<td class="td-val"><?= htmlspecialchars(!empty($datos['telefono']) ? $datos['telefono'] : '---'); ?></td>
				<td class="td-label">Correo Electrónico:</td>
				<td class="td-val"><?= htmlspecialchars(!empty($datos['correo_empresa']) ? $datos['correo_empresa'] : '---'); ?></td>
			</tr>
		</table>

		<!-- Sección 3: Programación y Parámetros de la Ficha -->
		<div class="seccion-titulo">3. Programación y Parámetros de la Ficha</div>
		<table class="doc-table">
			<tr>
				<td class="td-label">Código Ficha (SOFIA Plus):</td>
				<td class="td-val td-highlight">
					<?= htmlspecialchars(!empty($datos['codigo_ficha']) ? $datos['codigo_ficha'] : 'Pendiente por vincular'); ?>
					<?php if(!empty($datos['nomfic'])): ?>
						<br><small class="text-muted" style="font-size:10px;">(<?= htmlspecialchars($datos['nomfic']); ?>)</small>
					<?php endif; ?>
				</td>
				<td class="td-label">Cupo de Aprendices:</td>
				<td class="td-val td-highlight">
					<strong><?= htmlspecialchars(!empty($datos['cupo']) ? $datos['cupo'] . ' cupos' : '0 cupos'); ?></strong>
				</td>
			</tr>
			<tr>
				<td class="td-label">Fecha de Inicio:</td>
				<td class="td-val"><?= !empty($datos['fecha_inicio']) ? htmlspecialchars(date('d/m/Y', strtotime($datos['fecha_inicio']))) : '---'; ?></td>
				<td class="td-label">Fecha de Terminación:</td>
				<td class="td-val"><?= !empty($datos['fecha_fin']) ? htmlspecialchars(date('d/m/Y', strtotime($datos['fecha_fin']))) : '---'; ?></td>
			</tr>
			<tr>
				<td class="td-label">Jornada Programada:</td>
				<td class="td-val"><?= htmlspecialchars(!empty($datos['jornada']) ? $datos['jornada'] : '---'); ?></td>
				<td class="td-label">Estado de la Ficha / Solicitud:</td>
				<td class="td-val">
					<span class="badge-estado-doc">Registrada</span>
				</td>
			</tr>
		</table>

		<!-- Sección 4: Horarios y Días de Formación -->
		<div class="seccion-titulo">4. Horarios y Días de Formación</div>
		<table class="horario-table">
			<thead>
				<tr>
					<th class="horario-dia-nom">Día de la Semana</th>
					<th>Hora Inicio</th>
					<th>Hora Fin</th>
					<th>Programación</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($dias_semana as $dia): ?>
					<?php
					$hIni = !empty($dias[$dia]['inicio']) ? $dias[$dia]['inicio'] : '---';
					$hFin = !empty($dias[$dia]['fin']) ? $dias[$dia]['fin'] : '---';
					$tieneHorario = ($hIni !== '---' && $hFin !== '---');
					?>
					<tr>
						<td class="horario-dia-nom"><?= htmlspecialchars($dia); ?></td>
						<td><?= htmlspecialchars($hIni); ?></td>
						<td><?= htmlspecialchars($hFin); ?></td>
						<td><?= $tieneHorario ? 'Sesión programada' : 'No programado'; ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<!-- Sección 5: Datos del Instructor Asignado -->
		<div class="seccion-titulo">5. Datos del Instructor Asignado</div>
		<table class="doc-table">
			<tr>
				<td class="td-label">Nombres y Apellidos:</td>
				<td class="td-val td-highlight" colspan="3">
					<?= htmlspecialchars(!empty($datos['nombre_instructor']) ? $datos['nombre_instructor'] : 'Pendiente por asignar'); ?>
				</td>
			</tr>
			<tr>
				<td class="td-label">Documento de Identidad:</td>
				<td class="td-val"><?= htmlspecialchars(!empty($datos['documento_instructor']) ? $datos['documento_instructor'] : '---'); ?></td>
				<td class="td-label">Teléfono / Celular:</td>
				<td class="td-val"><?= htmlspecialchars(!empty($datos['cel_instructor']) ? $datos['cel_instructor'] : '---'); ?></td>
			</tr>
			<tr>
				<td class="td-label">Correo Institucional:</td>
				<td class="td-val-wide" colspan="3"><?= htmlspecialchars(!empty($datos['correo_instructor']) ? $datos['correo_instructor'] : '---'); ?></td>
			</tr>
		</table>

		<!-- Sección 6: Observaciones y Seguimiento -->
		<div class="seccion-titulo">6. Observaciones y Seguimiento</div>
		<div class="observaciones-box">
			La presente Hoja de Trabajo se genera bajo la normatividad académica e institucional vigente para formación complementaria y titulada del Centro de Desarrollo Agroempresarial (SENA Regional Cundinamarca). La ejecución efectiva de la acción formativa se encuentra sujeta al cumplimiento estricto del cupo mínimo de aprendices formalmente matriculados en la plataforma SOFIA Plus y a la verificación previa de ambientes, materiales e insumos de formación requeridos.
		</div>

		<!-- Sección 7: Firmas y Control Institucional -->
		<div class="firmas-container">
			<div class="firma-col">
				<div class="firma-linea"></div>
				<div class="firma-titulo">Responsable de Formación</div>
				<div class="firma-subtitulo">Instructor / Gestor de Solicitud</div>
			</div>
			<div class="firma-col">
				<div class="firma-linea"></div>
				<div class="firma-titulo">Revisión Académica CEDA</div>
				<div class="firma-subtitulo">Coordinación de Formación</div>
			</div>
			<div class="firma-col">
				<div class="firma-linea"></div>
				<div class="firma-titulo">Aprobación Final</div>
				<div class="firma-subtitulo">Subdirección de Centro / Apoyo</div>
			</div>
		</div>

		<div class="doc-footer-meta">
			Documento oficial de control y gestión académica — Servicio Nacional de Aprendizaje SENA — Centro de Desarrollo Agroempresarial (CEDA) — Generado el <?= date('d/m/Y H:i:s'); ?>
		</div>
	</div>

<?php else: ?>
	<!-- Estado cuando no existen registros en la base de datos -->
	<div class="empty-state-card">
		<div class="empty-state-icon"><i class="fa-regular fa-folder-open"></i></div>
		<h4 style="font-weight: 800; color: var(--sena-navy);">No hay Hojas de Trabajo disponibles</h4>
		<p class="text-muted" style="max-width: 500px; margin: 8px auto 20px auto;">
			No se encontraron registros de solicitudes u Hojas de Trabajo en el sistema para generar el reporte oficial.
		</p>
		<a href="home.php?pg=2004" class="btn-reporte-action btn-reporte-back">
			<i class="fa-solid fa-arrow-left"></i> <span>Ir a Gestión de Hojas de Trabajo</span>
		</a>
	</div>
<?php endif; ?>