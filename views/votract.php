<?php
// Vista Oficial: Acta Resultados Votaciones (ID 1217)
// Responsable: Yeison Stiven Molina Balceras
// Cronograma SAGI: views/votract.php | Modelo: models/votmact.php

// Si se invoca directamente para emitir y descargar PDF
if (isset($_GET['pdf']) && $_GET['pdf'] === 'ok') {
    if (file_exists('../models/votmact.php')) {
        chdir(dirname(__DIR__));
    }
    ini_set('memory_limit', '512M');
}

require_once("models/votmact.php");
$votmact = new Votmact();

date_default_timezone_set('America/Bogota');
$mes = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
$mesIndex = (int)date('m') - 1;
$mesNombre = isset($mes[$mesIndex]) ? $mes[$mesIndex] : date('F');
$dia = date('d');
$fecha = $dia . " de " . $mesNombre . " de " . date('Y');
$fecha2 = date('YmdHis');
$año = date('Y');
$fcan = date('Y') + 1;
$hora_inicio = date("H:i", strtotime("-30 minutes")) . " Hrs";
$hora_fin = date("H:i") . " Hrs";

if (!function_exists('imagenes')) {
    function imagenes($imgn){
        if (file_exists($imgn)) {
            return "data:image/png;base64," . base64_encode(file_get_contents($imgn));
        } else {
            $default = "img/user.jpg";
            if (file_exists($default)) {
                return "data:image/png;base64," . base64_encode(file_get_contents($default));
            }
            return "";
        }
    }
}

if (!function_exists('urlimg')) {
    function urlimg($url) {
        if (file_exists($url)) {
            return "data:image/png;base64," . base64_encode(file_get_contents($url));
        }
        return "";
    }
}

// ==================================================================================
// 1. PLANTILLA INSTITUCIONAL SENA - ACTA DE REPRESENTANTES
// ==================================================================================
if (!function_exists('renderizarActaInstitucionalSena')) {
    function renderizarActaInstitucionalSena($cfg) {
        $ancho        = isset($cfg['ancho']) ? $cfg['ancho'] : 750;
        $titulo       = isset($cfg['titulo']) ? $cfg['titulo'] : 'ACTA DE VOTACIÓN SENA';
        $numeroActa   = isset($cfg['numero_acta']) ? $cfg['numero_acta'] : '001';
        $fecha        = isset($cfg['fecha']) ? $cfg['fecha'] : date('d/m/Y');
        $horaInicio   = isset($cfg['hora_inicio']) ? $cfg['hora_inicio'] : '08:00 Hrs';
        $horaFin      = isset($cfg['hora_fin']) ? $cfg['hora_fin'] : '18:00 Hrs';
        $lugar        = isset($cfg['lugar']) ? $cfg['lugar'] : 'Centro de Desarrollo Agroempresarial - Chía';
        $regional     = isset($cfg['regional']) ? $cfg['regional'] : 'Regional Cundinamarca, Centro de Desarrollo Agroempresarial';
        $año          = isset($cfg['año']) ? $cfg['año'] : date('Y');
        $fcan         = isset($cfg['fcan']) ? $cfg['fcan'] : ((int)$año + 1);

        $temaDefault = "Escrutinio final, consolidación democrática y proclamación oficial de resultados de la jornada electoral para la elección de Representantes de Aprendices del Centro de Desarrollo Agroempresarial - Regional Cundinamarca, correspondiente a la vigencia institucional " . $año . " - " . $fcan . ", en estricto cumplimiento del Reglamento del Aprendiz SENA (Acuerdo 007 de 2012) y las directrices emanadas por la Dirección de Formación Profesional y la Coordinación Nacional de Bienestar al Aprendiz.";

        $objetivoDefault = "<p style='margin: 0 0 6px 0; text-align: justify;'><strong>1. Consolidación y Verificación Técnica:</strong> Consolidar, verificar y refrendar los resultados electorales obtenidos mediante el voto electrónico en el aplicativo institucional SAGISENA para la elección de Representantes de Aprendices en todas las jornadas formativas (Diurna, Nocturna, Mixta / Fines de Semana).</p>"
            . "<p style='margin: 0 0 6px 0; text-align: justify;'><strong>2. Garantía Democrática y Transparencia:</strong> Garantizar los principios constitucionales de transparencia, equidad, participación democrática y debido proceso en la consolidación de los votos válidos escrutados por cada candidato aspirante.</p>"
            . "<p style='margin: 0; text-align: justify;'><strong>3. Proclamación y Asignación de Funciones:</strong> Declarar formalmente el cierre del certamen electoral y efectuar la proclamación del Representante de Aprendices electo por mayoría absoluta de sufragios, asignando los compromisos y deberes institucionales de representación ante el Consejo Directivo, el Comité de Evaluación y Seguimiento y demás órganos colegiados del Centro de Formación.</p>";

        $desarrolloDefault = "En las instalaciones del Centro de Desarrollo Agroempresarial - Regional Cundinamarca, sede Chía, y a través de los entornos digitales habilitados en el sistema institucional SAGISENA, se reunieron los delegados del proceso electoral bajo la coordinación de la Líder de Bienestar al Aprendiz, con el propósito de adelantar la sesión formal de escrutinio y cierre de los comicios electorales para la vigencia " . $año . " - " . $fcan . ".<br><br>"
            . "Previa apertura de la urna digital y finalizado el horario reglamentario establecido para el sufragio de los aprendices en sus respectivas jornadas de formación, se procedió a la verificación técnica de integridad de la base de datos, constatando la correcta recepción y cómputo de cada sufragio emitido de forma libre, secreta e individual, garantizando la inviolabilidad del voto y la confidencialidad de la información registrada.<br><br>"
            . "A continuación, se detalla el cómputo final discriminado por cada jornada formativa registrada en el Centro de Formación, evidenciando el número de votos obtenidos por cada candidato inscrito, los votos en blanco y la distribución porcentual correspondiente sobre el total general escrutado:";

        $tema         = (!empty($cfg['tema']) && strpos($cfg['tema'], 'Votaciones SENA') === false && strpos($cfg['tema'], '<strong>1.</strong>') === false) ? $cfg['tema'] : $temaDefault;
        $objetivo     = (!empty($cfg['objetivo']) && strpos($cfg['objetivo'], 'Consolidar y formalizar los resultados de las votaciones') === false) ? $cfg['objetivo'] : $objetivoDefault;
        $desarrollo   = (!empty($cfg['desarrollo']) && strpos($cfg['desarrollo'], 'En sesión oficial de escrutinio electoral') === false) ? $cfg['desarrollo'] : $desarrolloDefault;
        $secciones    = isset($cfg['secciones']) ? $cfg['secciones'] : [];

        $logoSena = '';
        $rutasLogo = ['img/sena.png', 'img/senab.png', 'image/sena.png'];
        foreach ($rutasLogo as $rl) {
            if (file_exists($rl)) {
                $logoSena = 'data:image/png;base64,' . base64_encode(file_get_contents($rl));
                break;
            }
        }

        $h = '';
        $h .= '<body>';
        $h .= '<table width="'.$ancho.'px" style="margin: 0 auto; font-family: Arial, sans-serif;">';
        $h .= '<tr><td style="text-align: center; padding-bottom: 8px;">';
        if ($logoSena) {
            $h .= '<div style="text-align: center; margin: 0 auto;"><img src="' . $logoSena . '" width="70px" style="display: inline-block; margin: 0 auto;"></div><br>';
        } else {
            $h .= '<h2 style="color: #39a900; margin: 0;">SENA</h2><br>';
        }
        $h .= '</td></tr>';
        $h .= '</table>';

        $h .= '<table width="'.$ancho.'px" cellpadding="5px" cellspacing="0" style="margin: 0 auto; font-family: Arial, sans-serif; border-collapse: collapse;">';
        $h .= '<tr>';
        $h .= '<td style="text-align: center; border: 1px solid #000;" colspan="3"><strong>ACTA No. ' . htmlspecialchars($numeroActa) . '</strong></td>';
        $h .= '</tr>';
        $h .= '<tr>';
        $h .= '<th style="text-align: center; border: 1px solid #000; background-color: #f2f2f2; padding: 10px;" colspan="3">' . mb_strtoupper($titulo, 'UTF-8') . '</th>';
        $h .= '</tr>';
        $h .= '<tr>';
        $h .= '<td style="border: 1px solid #000; width: 34%;"><strong>CIUDAD Y FECHA</strong><br>' . htmlspecialchars($fecha) . '</td>';
        $h .= '<td style="border: 1px solid #000; width: 33%;"><strong>HORA INICIO</strong><br>' . htmlspecialchars($horaInicio) . '</td>';
        $h .= '<td style="border: 1px solid #000; width: 33%;"><strong>HORA FIN</strong><br>' . htmlspecialchars($horaFin) . '</td>';
        $h .= '</tr>';
        $h .= '<tr>';
        $h .= '<td style="border: 1px solid #000;"><strong>LUGAR Y/O ENLACE</strong><br>' . htmlspecialchars($lugar) . '</td>';
        $h .= '<td colspan="2" style="border: 1px solid #000;"><strong>DIRECCIÓN GENERAL / REGIONAL / CENTRO</strong><br>' . htmlspecialchars($regional) . '</td>';
        $h .= '</tr>';
        $h .= '<tr><td colspan="3" style="border: 1px solid #000;"><strong>TEMA</strong><br>' . $tema . '</td></tr>';
        $h .= '<tr><td colspan="3" style="border: 1px solid #000;"><strong>OBJETIVOS</strong><br>' . $objetivo . '</td></tr>';
        $h .= '<tr><td colspan="3" style="border: 1px solid #000; text-align: justify;"><strong>DESARROLLO</strong><br>' . $desarrollo . '<br><br></td></tr>';

        foreach ($secciones as $sec) {
            $subtitulo  = isset($sec['subtitulo']) ? $sec['subtitulo'] : 'RESULTADOS';
            $candidatos = isset($sec['candidatos']) ? $sec['candidatos'] : [];
            $totalVotos = isset($sec['total_votos']) ? (int)$sec['total_votos'] : 0;

            $h .= '<tr>';
            $h .= '<td colspan="3" style="border: 1px solid #000; text-align: center; background-color: #e9ecef; font-weight: bold; padding: 8px;">' . mb_strtoupper($subtitulo, 'UTF-8') . '</td>';
            $h .= '</tr>';
            $h .= '<tr><td colspan="3" style="padding: 0;">';
            $h .= '<table width="100%" cellpadding="6px" cellspacing="0" border="1" style="border-collapse: collapse; margin: 0; font-family: Arial, sans-serif;">';
            $h .= '<tr style="background-color: #d1cfcf;">';
            $h .= '<th style="border: 1px solid #000; width: 45%; text-align: center;">CANDIDATO</th>';
            $h .= '<th style="border: 1px solid #000; width: 25%; text-align: center;">N° VOTOS</th>';
            $h .= '<th style="border: 1px solid #000; width: 30%; text-align: center;">PORCENTAJE</th>';
            $h .= '</tr>';

            if (!empty($candidatos)) {
                foreach ($candidatos as $idx => $can) {
                    $nom = htmlspecialchars($can['nomusu']);
                    $v   = (int)$can['total_votos'];
                    $pct = ($totalVotos > 0) ? round(($v / $totalVotos) * 100, 1) . '%' : '0%';
                    $badgeRol = ($idx === 0 && $v > 0) ? '<span style="color: #117f09; font-weight: bold;">(REPRESENTANTE ELECTO)</span><br>' : '';

                    $fotoCan = '';
                    $rutaFoto = !empty($can['fotcan']) ? $can['fotcan'] : 'img/user.jpg';
                    if (function_exists('imagenes')) {
                        $fotoCan = imagenes($rutaFoto);
                    }

                    $h .= '<tr>';
                    $h .= '<td style="border: 1px solid #000; text-align: center; vertical-align: middle; padding: 8px;">';
                    $h .= $badgeRol . '<strong>' . $nom . '</strong><br>';
                    if ($fotoCan) {
                        $h .= '<div style="width: 100%; text-align: center; margin: 6px auto 2px auto;">';
                        $h .= '<img src="' . $fotoCan . '" width="65px" height="65px" style="display: inline-block; margin: 0 auto; border-radius: 50%; border: 2px solid #999; object-fit: cover;">';
                        $h .= '</div>';
                    }
                    $h .= '</td>';
                    $h .= '<td style="border: 1px solid #000; text-align: center; vertical-align: middle; font-size: 16px; font-weight: bold;">' . $v . '</td>';
                    $h .= '<td style="border: 1px solid #000; text-align: center; vertical-align: middle; font-size: 15px;">' . $pct . '</td>';
                    $h .= '</tr>';
                }
            } else {
                $h .= '<tr><td colspan="3" style="text-align: center; padding: 15px; border: 1px solid #000;">No se registraron candidatos ni votos.</td></tr>';
            }

            $h .= '<tr style="background-color: #f8f9fa;">';
            $h .= '<td style="border: 1px solid #000; text-align: right; font-weight: bold;">TOTAL VOTACIÓN:</td>';
            $h .= '<td style="border: 1px solid #000; text-align: center; font-weight: bold; font-size: 16px;">' . $totalVotos . '</td>';
            $h .= '<td style="border: 1px solid #000; text-align: center; font-weight: bold;">100%</td>';
            $h .= '</tr>';
            $h .= '</table>';
            $h .= '</td></tr>';
        }

        $repElecto = isset($cfg['representante_electo']) && !empty($cfg['representante_electo']) ? $cfg['representante_electo'] : '';

        // SECCIÓN 1: CONCLUSIONES
        $conclusiones = isset($cfg['conclusiones']) ? $cfg['conclusiones'] : '';
        if (empty($conclusiones)) {
            $repElectoTxt = !empty($repElecto) ? "<strong>" . htmlspecialchars($repElecto) . "</strong>" : "el candidato con mayor respaldo en las urnas virtuales";
            $conclusiones = "<p style='margin: 0 0 8px 0; text-align: justify;'><strong>1. Cumplimiento del Calendario Electoral:</strong> Se dio estricto cumplimiento al cronograma electoral fijado institucionalmente para la elección de Representantes de Aprendices SENA vigencia " . htmlspecialchars($año) . " - " . htmlspecialchars($fcan) . ", garantizando plenas garantías democráticas, acceso equitativo y participación activa de los aprendices en todas las jornadas formativas (Diurna, Nocturna, Mixta / Fines de Semana).</p>";
            $conclusiones .= "<p style='margin: 0 0 8px 0; text-align: justify;'><strong>2. Proclamación Oficial de Resultados:</strong> Verificado y consolidado el escrutinio de los sufragios computados en el Centro de Desarrollo Agroempresarial - Regional Cundinamarca, se constata que la mayor votación popular fue obtenida por el aprendiz " . $repElectoTxt . ", proclamándose formalmente como Representante de Aprendices SENA Electo para el período institucional " . htmlspecialchars($año) . " - " . htmlspecialchars($fcan) . ".</p>";
            $conclusiones .= "<p style='margin: 0; text-align: justify;'><strong>3. Cierre y Transparencia del Proceso:</strong> La jornada culminó en absoluta normalidad y sin reclamaciones ni impugnaciones radicadas por candidatos o testigos electorales, refrendando la transparencia, legitimidad, confiabilidad e inalterabilidad de los registros consolidados en el sistema institucional SAGISENA.</p>";
        }

        $h .= '<tr><td colspan="3" style="border: 1px solid #000; text-align: center; background-color: #e9ecef; font-weight: bold; padding: 7px;">CONCLUSIONES</td></tr>';
        $h .= '<tr><td colspan="3" style="border: 1px solid #000; padding: 10px 12px; font-size: 11px; line-height: 1.45;">' . $conclusiones . '</td></tr>';

        // SECCIÓN 2: ESTABLECIMIENTO Y ACEPTACIÓN DE COMPROMISOS (4 COLUMNAS)
        $h .= '<tr><td colspan="3" style="border: 1px solid #000; text-align: center; background-color: #e9ecef; font-weight: bold; padding: 7px;">ESTABLECIMIENTO Y ACEPTACIÓN DE COMPROMISOS</td></tr>';
        $h .= '<tr><td colspan="3" style="padding: 0;">';
        $h .= '<table width="100%" cellpadding="6px" cellspacing="0" border="1" style="border-collapse: collapse; margin: 0; font-family: Arial, sans-serif; font-size: 10.5px;">';
        $h .= '<tr style="background-color: #d1cfcf; text-align: center; font-weight: bold;">';
        $h .= '<th style="border: 1px solid #000; width: 44%; padding: 6px;">ACTIVIDAD / DECISIÓN</th>';
        $h .= '<th style="border: 1px solid #000; width: 16%; padding: 6px;">FECHA</th>';
        $h .= '<th style="border: 1px solid #000; width: 22%; padding: 6px;">RESPONSABLE</th>';
        $h .= '<th style="border: 1px solid #000; width: 18%; padding: 6px;">FIRMA</th>';
        $h .= '</tr>';

        $h .= '<tr>';
        $h .= '<td style="border: 1px solid #000; padding: 8px 6px;">Formalización del acto administrativo de posesión y suscripción del acta de compromiso institucional del Representante de Aprendices electo período ' . htmlspecialchars($año) . ' - ' . htmlspecialchars($fcan) . '.</td>';
        $h .= '<td style="border: 1px solid #000; text-align: center; padding: 8px 4px;">Inmediata</td>';
        $h .= '<td style="border: 1px solid #000; text-align: center; padding: 8px 4px; font-weight: 600;">Líder de Bienestar al Aprendiz</td>';
        $h .= '<td style="border: 1px solid #000; height: 42px; text-align: center;">&nbsp;</td>';
        $h .= '</tr>';

        $h .= '<tr>';
        $h .= '<td style="border: 1px solid #000; padding: 8px 6px;">Remisión de la presente acta y archivo formal del expediente electoral ante la Subdirección de Centro y la Dirección Regional.</td>';
        $h .= '<td style="border: 1px solid #000; text-align: center; padding: 8px 4px;">' . htmlspecialchars($fecha) . '</td>';
        $h .= '<td style="border: 1px solid #000; text-align: center; padding: 8px 4px; font-weight: 600;">Líder de Bienestar al Aprendiz</td>';
        $h .= '<td style="border: 1px solid #000; height: 42px; text-align: center;">&nbsp;</td>';
        $h .= '</tr>';

        $h .= '<tr>';
        $h .= '<td style="border: 1px solid #000; padding: 8px 6px;">Instalación de la mesa de trabajo con Bienestar al Aprendiz para la formulación del plan de acción estudiantil ' . htmlspecialchars($año) . ' - ' . htmlspecialchars($fcan) . '.</td>';
        $h .= '<td style="border: 1px solid #000; text-align: center; padding: 8px 4px;">A partir de la posesión</td>';
        $h .= '<td style="border: 1px solid #000; text-align: center; padding: 8px 4px; font-weight: 600;">Líder de Bienestar al Aprendiz</td>';
        $h .= '<td style="border: 1px solid #000; height: 42px; text-align: center;">&nbsp;</td>';
        $h .= '</tr>';
        $h .= '</table></td></tr>';

        // SECCIÓN 3: ASISTENTES Y APROBACIÓN DECISIONES (3 COLUMNAS)
        $h .= '<tr><td colspan="3" style="border: 1px solid #000; text-align: center; background-color: #e9ecef; font-weight: bold; padding: 7px;">ASISTENTES Y APROBACIÓN DECISIONES</td></tr>';
        $h .= '<tr><td colspan="3" style="padding: 0;">';
        $h .= '<table width="100%" cellpadding="6px" cellspacing="0" border="1" style="border-collapse: collapse; margin: 0; font-family: Arial, sans-serif; font-size: 10.5px;">';
        $h .= '<tr style="background-color: #d1cfcf; text-align: center; font-weight: bold;">';
        $h .= '<th style="border: 1px solid #000; width: 40%; padding: 6px;">NOMBRE</th>';
        $h .= '<th style="border: 1px solid #000; width: 35%; padding: 6px;">DEPENDENCIA</th>';
        $h .= '<th style="border: 1px solid #000; width: 25%; padding: 6px;">FIRMA</th>';
        $h .= '</tr>';

        $h .= '<tr>';
        $h .= '<td style="border: 1px solid #000; padding: 12px 8px; vertical-align: middle;"><strong>Líder de Bienestar al Aprendiz</strong></td>';
        $h .= '<td style="border: 1px solid #000; padding: 12px 8px; vertical-align: middle;">Bienestar al Aprendiz / Centro de Desarrollo Agroempresarial</td>';
        $h .= '<td style="border: 1px solid #000; height: 55px; vertical-align: bottom; text-align: center; padding: 6px 4px;"><br>_____________________________</td>';
        $h .= '</tr>';

        $nomRepMostrar = !empty($repElecto) ? htmlspecialchars($repElecto) : 'Representante Electo';
        $h .= '<tr>';
        $h .= '<td style="border: 1px solid #000; padding: 12px 8px; vertical-align: middle;"><strong style="text-transform: uppercase;">' . $nomRepMostrar . '</strong><br><small style="color: #555;">Representante de Aprendices Electo</small></td>';
        $h .= '<td style="border: 1px solid #000; padding: 12px 8px; vertical-align: middle;">Representación Estudiantil SENA / Comunidad de Aprendices</td>';
        $h .= '<td style="border: 1px solid #000; height: 55px; vertical-align: bottom; text-align: center; padding: 6px 4px;"><br>_____________________________</td>';
        $h .= '</tr>';

        $h .= '</table></td></tr>';
        $h .= '</table></body>';

        return $h;
    }
}

// ==================================================================================
// 2. PLANTILLA OFICIAL ACTA DE ELECCIÓN DE VOCEROS (FORMATO GOR-F-084 V02)
// ==================================================================================
if (!function_exists('renderizarActaVocerosOficialSena')) {
    function renderizarActaVocerosOficialSena(array $cfg): string {
        $logoBase64 = isset($cfg['logo_base64']) ? $cfg['logo_base64'] : '';
        if (empty($logoBase64)) {
            $rutasLogo = ['img/sena.png', 'img/senab.png', 'image/sena.png'];
            foreach ($rutasLogo as $rl) {
                if (file_exists($rl)) {
                    $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($rl));
                    break;
                }
            }
        }

        $ciudadFecha     = isset($cfg['ciudad_fecha']) ? $cfg['ciudad_fecha'] : ('Chía, ' . date('d/m/Y'));
        $dia             = isset($cfg['dia']) ? $cfg['dia'] : date('d');
        $mesNombre       = isset($cfg['mes_nombre']) ? $cfg['mes_nombre'] : date('F');
        $año             = isset($cfg['año']) ? $cfg['año'] : date('Y');
        $fcan            = isset($cfg['fcan']) ? $cfg['fcan'] : ((int)$año + 1);
        $lugar           = isset($cfg['lugar']) ? $cfg['lugar'] : 'Ambiente de formación y sede/subsede';
        $regionalCentro  = 'Dirección General / Regional Cundinamarca / Centro de Desarrollo Agroempresarial';

        $ficha           = isset($cfg['ficha']) ? $cfg['ficha'] : [];
        $idfic           = isset($ficha['idfic']) ? $ficha['idfic'] : 'N/A';
        $nomfic          = isset($ficha['nomfic']) ? $ficha['nomfic'] : 'PROGRAMA NO DEFINIDO';
        $nomjor          = isset($ficha['nomjor']) ? $ficha['nomjor'] : (isset($ficha['nomval']) ? $ficha['nomval'] : 'N/A');

        $vocero          = isset($cfg['vocero']) ? $cfg['vocero'] : null;
        $nomVocero       = $vocero && !empty($vocero['nomusu']) ? $vocero['nomusu'] : '________________________________________';
        $tipoDocVocero   = $vocero && !empty($vocero['tipodoc']) ? $vocero['tipodoc'] : 'CC';
        $docVocero       = $vocero && !empty($vocero['ndocusu']) ? ($tipoDocVocero . ' ' . $vocero['ndocusu']) : '________________________';
        $telVocero       = $vocero && !empty($vocero['telcan']) ? $vocero['telcan'] : ($vocero && !empty($vocero['telusu']) ? $vocero['telusu'] : '________________________');
        $corVocero       = $vocero && !empty($vocero['emausu']) ? $vocero['emausu'] : '________________________';

        $suplente        = isset($cfg['suplente']) ? $cfg['suplente'] : null;
        $nomSuplente     = $suplente && !empty($suplente['nomusu']) ? $suplente['nomusu'] : '________________________________________';
        $tipoDocSuplente = $suplente && !empty($suplente['tipodoc']) ? $suplente['tipodoc'] : 'CC';
        $docSuplente     = $suplente && !empty($suplente['ndocusu']) ? ($tipoDocSuplente . ' ' . $suplente['ndocusu']) : '________________________';
        $telSuplente     = $suplente && !empty($suplente['telcan']) ? $suplente['telcan'] : ($suplente && !empty($suplente['telusu']) ? $suplente['telusu'] : '________________________');
        $corSuplente     = $suplente && !empty($suplente['emausu']) ? $suplente['emausu'] : '________________________';

        $candidatos      = isset($cfg['candidatos']) && is_array($cfg['candidatos']) ? $cfg['candidatos'] : [];
        $instructor      = isset($cfg['instructor']) ? $cfg['instructor'] : [];
        $nomInstructor   = !empty($instructor['nomins']) ? $instructor['nomins'] : 'Instructor Lider / Delegado';
        $docInstructor   = !empty($instructor['ndocins']) ? $instructor['ndocins'] : '';
        $emaInstructor   = !empty($instructor['emains']) ? $instructor['emains'] : '';
        $aprendices      = isset($cfg['aprendices']) && is_array($cfg['aprendices']) ? $cfg['aprendices'] : [];
        $isPdf           = isset($cfg['is_pdf']) ? (bool)$cfg['is_pdf'] : false;

        ob_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Acta Oficial Elección de Voceros - SENA</title>
<style>
<?php if ($isPdf): ?>
  @page { margin: 16px 24px; size: letter portrait; }
  body { font-family: Arial, Helvetica, sans-serif; font-size: 9.5px; line-height: 1.3; color: #000; margin: 0; padding: 0; background: #fff; }
  .acta-page { width: 100%; box-sizing: border-box; page-break-after: always; position: relative; margin: 0; padding: 0; background: #fff; border: none; }
<?php else: ?>
  body { font-family: Arial, Helvetica, sans-serif; font-size: 10px; line-height: 1.32; color: #000; margin: 0; padding: 0; background: transparent; }
  .acta-page { max-width: 820px; width: 100%; box-sizing: border-box; margin: 0 auto 30px auto; padding: 22px 25px; background: #fff; border: 1px solid #ddd; box-shadow: 0 4px 15px rgba(0,0,0,0.08); }
<?php endif; ?>
  .acta-page:last-child { page-break-after: avoid; margin-bottom: 0; }
  .header-logo { text-align: center; margin: 0 auto 6px auto; width: 100%; }
  .header-logo img { width: 55px; height: auto; display: block; margin: 0 auto; }
  .tbl-acta { width: 100%; border-collapse: collapse; border: 1px solid #000; margin-bottom: 6px; font-family: Arial, sans-serif; font-size: 9.5px; }
  .tbl-acta th, .tbl-acta td { border: 1px solid #000; padding: 3px 5px; vertical-align: top; }
  .border-box { border: 1px solid #000; padding: 8px 10px; text-align: justify; box-sizing: border-box; margin-bottom: 6px; font-size: 9.5px; }
  .footer-code { text-align: center; font-size: 9px; color: #000; margin-top: 10px; font-weight: bold; }
  .txt-center { text-align: center; }
  .txt-bold   { font-weight: bold; }
  .bg-gray    { background-color: #f2f2f2; }
  .box-tag { border: 1px solid #000; padding: 2px 6px; font-weight: bold; display: inline-block; margin-bottom: 4px; font-size: 9.5px; background: #f9f9f9; }
  .signature-line { height: 26px; border-bottom: 1px solid #555; margin-bottom: 2px; }
  .signature-label { font-size: 8px; color: #333; text-transform: uppercase; font-weight: bold; text-align: center; display: block; }
  .law-clause { font-size: 8.5px; line-height: 1.25; text-align: justify; margin: 6px 0; }
  .tbl-assist { width: 100%; border-collapse: collapse; border: 1px solid #000; font-size: 8px; font-family: Arial, sans-serif; }
  .tbl-assist th, .tbl-assist td { border: 1px solid #000; padding: 2px 3px; text-align: center; }
  .tbl-assist tr { page-break-inside: avoid; }
  .tbl-assist-13 { width: 100%; border-collapse: collapse; border: 1px solid #000; table-layout: fixed; font-size: 6.2px; font-family: Arial, sans-serif; }
  .tbl-assist-13 th, .tbl-assist-13 td { border: 1px solid #000; padding: 2px 1px; text-align: center; word-wrap: break-word; overflow: hidden; }
  .tbl-assist-13 th { background: #f2f2f2; font-weight: bold; }
  .tbl-assist-13 tr { page-break-inside: avoid; }
  .signature-line-sm { height: 11px; border-bottom: 1px solid #000; margin: 0 auto; width: 90%; }
</style>
</head>
<body>

<!-- PÁGINA 1: Encabezado y Articulado 8 - 8.1 -->
<div class="acta-page">
  <div class="header-logo"><?php if ($logoBase64): ?><img src="<?= $logoBase64 ?>" alt="SENA" style="display: block; margin: 0 auto;"><?php endif; ?></div>
  <table class="tbl-acta">
    <tr><td colspan="4" class="txt-center txt-bold" style="padding: 5px; font-size: 11.5px;">ACTA No. 001</td></tr>
    <tr>
      <td colspan="4" style="padding: 6px;">
        <span class="txt-bold">NOMBRE DEL COMITÉ O DE LA REUNIÓN:</span><br>
        <div class="txt-center txt-bold" style="margin-top: 3px; font-size: 11px;">Elección de voceros y Suplentes de aprendices</div>
      </td>
    </tr>
    <tr>
      <td style="width: 44%;"><span class="txt-bold">CIUDAD Y FECHA:</span> <?= htmlspecialchars($ciudadFecha) ?></td>
      <td style="width: 28%;"><span class="txt-bold">HORA INICIO:</span></td>
      <td colspan="2" style="width: 28%;"><span class="txt-bold">HORA FIN:</span></td>
    </tr>
    <tr>
      <td><span class="txt-bold">LUGAR Y/O ENLACE:</span> <?= htmlspecialchars($lugar) ?></td>
      <td colspan="3"><span class="txt-bold">DIRECCIÓN / REGIONAL / CENTRO:</span><br><?= $regionalCentro ?></td>
    </tr>
    <tr>
      <td colspan="4" style="padding: 6px;">
        <span class="txt-bold">AGENDA O PUNTOS PARA DESARROLLAR:</span>
        <ol style="margin: 4px 0 2px 18px; padding: 0;">
          <li>Socialización requisitos del vocero</li>
          <li>Elección democrática de vocero y suplente</li>
          <li>Firma del acta</li>
        </ol>
      </td>
    </tr>
    <tr>
      <td colspan="4" style="padding: 6px;">
        <span class="txt-bold">OBJETIVO(S) DE LA REUNIÓN:</span>
        <p style="margin: 3px 0; text-align: justify;">Generar escenarios de reconocimiento para aprendices en ejes de liderazgo, proyección social y formación de talentos. Adelantar la elección de voceros y representantes de aprendices.</p>
      </td>
    </tr>
    <tr><td colspan="4" class="txt-center txt-bold bg-gray" style="padding: 5px; font-size: 11px;">DESARROLLO DE LA REUNIÓN</td></tr>
    <tr>
      <td colspan="4" style="padding: 8px; text-align: justify;">
        Esta elección se realizará en el marco de la guía para la elección de representantes y voceros de aprendices SENA GFPI-G-050 del 14 de febrero del año 2025, teniendo en cuenta los siguientes artículos:<br><br>
        <strong>8. Elección de voceros de grupo</strong><br><br>
        <strong>8.1 Requisitos para ser voceros de grupo</strong><br><br>
        El aprendiz que desee postularse deberá cumplir con los siguientes requisitos:<br><br>
        A. Estar activo en su programa de formación en etapa lectiva (estado de inducción o de formación) y debe pertenecer al grupo al que represente.
      </td>
    </tr>
  </table>
  <div class="footer-code">GOR-F-084 V02</div>
</div>

<!-- PÁGINA 2: Articulado 8.1 (cont) a 8.4 -->
<div class="acta-page">
  <div class="header-logo"><?php if ($logoBase64): ?><img src="<?= $logoBase64 ?>" alt="SENA" style="display: block; margin: 0 auto;"><?php endif; ?></div>
  <div class="border-box">
    B. Postularse o dar su consentimiento para ser postulado, por los aprendices del mismo grupo de programa de formación.<br><br>
    C. Tener disponibilidad, cuando se requiera, para trabajar en equipo con el representante de centro de formación profesional integral (según modalidad y jornada) y con los demás integrantes de la comunidad educativa, en la medida que pueda dar cumplimiento a sus obligaciones académicas en su jornada de formación.<br><br>
    <strong>8.2 Procedimiento para la elección de vocero de grupo</strong><br><br>
    La elección de los voceros se realizará de la siguiente forma:<br><br>
    A. La elección se realizará dentro de los quince (15) días calendario siguientes a la fecha de inicio del programa de formación.<br><br>
    B. El proceso debe estar acompañado por el instructor del grupo o el instructor delegado.<br><br>
    C. Esta elección se realizará en el ambiente de aprendizaje, se postularán los aprendices que deseen ser candidatos y se realizará la votación por los candidatos, no se puede votar por el mismo candidato dos veces.<br><br>
    D. Será elegido, vocero principal el aprendiz candidato que cuente con mayoría de votos y el segundo con la mayor votación será el vocero suplente, en caso de empate se definirá por sorteo.<br><br>
    E. Las evidencias que sustentan la elección de voceros se gestionarán de acuerdo con lineamientos emitidos por el comité electoral del centro de formación.<br><br>
    <strong>8.3 Periodo de representación Vocero de grupo</strong><br><br>
    El periodo de representación de los aprendices voceros de grupo electos, será hasta la finalización de su etapa lectiva.<br><br>
    <strong>8.4 Responsabilidad de los voceros de grupo</strong><br><br>
    Los voceros de grupos de aprendices tendrán las siguientes responsabilidades:<br><br>
    A. Participar en las estrategias de fortalecimiento de liderazgo que se convoquen desde el centro de formación profesional integral o regional.<br><br>
    B. Promover la participación de los aprendices en las diferentes actividades que aborden en su proceso formativo, así como en las que programe el centro de formación profesional integral para el desarrollo del Plan Nacional Integral de Bienestar al Aprendiz.
  </div>
  <div class="footer-code">GOR-F-084V02</div>
</div>

<!-- PÁGINA 3: Articulado 8.4 (cont) a 8.6 -->
<div class="acta-page">
  <div class="header-logo"><?php if ($logoBase64): ?><img src="<?= $logoBase64 ?>" alt="SENA" style="display: block; margin: 0 auto;"><?php endif; ?></div>
  <div class="border-box">
    C. Asistir y participar activamente, cada vez que sea citado, en los comités de evaluación y seguimiento, acompañamiento formativo, o disciplinario de su grupo.<br><br>
    D. Participar con los demás voceros y el (los) representante (s) del centro de formación profesional integral, en la formulación y realización de actividades, servicios y proyectos que beneficie a los aprendices que representa.<br><br>
    E. Invitar a los integrantes de su grupo, para el proceso formativo se desarrolle de forma armónica y de diálogo directo con los actores vinculados a la formación.<br><br>
    F. Actuar como canal de comunicación e interlocutor entre los estamentos de la Comunidad Educativa SENA y los aprendices de su grupo.<br><br>
    G. Ser parte activa y garante del proceso electoral de aprendices representantes y los voceros de enfoque diferencial.<br><br>
    H. Estar atento a sus compañeros y alertar si alguno se encuentra en riesgo de deserción activar la ruta con el instructor o con el coordinador misional para que designe a algún profesional que tenga funciones del PNIBA.<br><br>
    La participación como vocero de grupo no lo exime de las responsabilidades de su formación profesional integral según rutas de aprendizaje.<br><br>
    <strong>8.5 Revocatoria de la designación de los voceros de grupo</strong><br><br>
    Los voceros de grupo serán relevados de la representación por sus compañeros de grupo, en caso de incumplimiento de sus responsabilidades, por falta(s) académica(s) o disciplinaria(s) contempladas en el reglamento del aprendiz acuerdo 009 de 2024, por postularse como candidato a representante de los aprendices por el centro de formación profesional integral, por renuncia voluntaria o por decisión mayoritaria del grupo que representa.<br><br>
    <strong>8.6 Procedimiento para la revocatoria del vocero de grupo</strong><br><br>
    Los voceros de grupo podrán ser revocados de su responsabilidad a través del mismo mecanismo por el que fueron elegidos y el procedimiento que se atenderá será el siguiente:<br><br>
    A. La revocatoria podrá ser solicitada en el centro de Formación, en un formato específico, para dicho fin es necesario presentar:<br>
    &nbsp;&nbsp;&nbsp;&nbsp;✓ Un documento con la motivación sustentada.
  </div>
  <div class="footer-code">GOR-F-084 V02</div>
</div>

<!-- PÁGINA 4: Articulado 8.6 (cont) a 8.7 y Tabla de Candidatos -->
<div class="acta-page">
  <div class="header-logo"><?php if ($logoBase64): ?><img src="<?= $logoBase64 ?>" alt="SENA" style="display: block; margin: 0 auto;"><?php endif; ?></div>
  <div class="border-box">
    &nbsp;&nbsp;&nbsp;&nbsp;✓ Las firmas de los aprendices del grupo, que se deberán ser superiores o iguales a la mitad más uno de los votos obtenidos en las elecciones por el vocero que se pretende revocar.<br>
    &nbsp;&nbsp;&nbsp;&nbsp;✓ El comité electoral del centro de formación, en máximo tres (3) días hábiles, después de que se presente la revocatoria deberá aprobar o rechazar la solicitud de proceso de revocatoria.<br><br>
    B. Una vez agotado lo anterior, el Centro de formación, notificará a la persona cuya representación pretende revocarse y a quienes realizaron la solicitud, en un plazo de dos (2) días hábiles. Asumirá el suplente.<br><br>
    C. Si la mitad más uno de los votantes elige “Si” a la pregunta de si desea revocar el vocero, la revocatoria operará y quedará como vocero el suplente.<br><br>
    En cualquier caso, la cantidad de votos positivos o negativos al respecto de la revocatoria no podrá ser inferior al 50% de los votos totales con los que el representante ganó en las elecciones generales.<br><br>
    Si la revocatoria prospera, se procederá a remover el vocero de su cargo mediante acta en donde quede la constancia de lo sucedido.<br><br>
    <strong>8.7 Suplencia de vocero de grupo</strong><br><br>
    En caso de que se requiera suplir o remover al vocero de grupo el vocero suplente asumirá las responsabilidades del vocero principal. La suplencia durará el tiempo que le faltaba al vocero principal para concluir el periodo. Si no hay vocero suplente, se desarrollará una nueva votación con los aprendices del grupo para elegir nuevo vocero y suplente de la misma forma que se realizó inicialmente.
  </div>

  <div class="txt-center txt-bold" style="font-size: 11px; margin: 12px 0 6px 0;">APRENDICES CANDIDATOS</div>
  <table class="tbl-acta">
    <tr class="bg-gray txt-bold txt-center">
      <td style="width: 8%;">No.</td>
      <td style="width: 20%;">TIPO DE DOCUMENTO</td>
      <td style="width: 24%;">No. DOCUMENTO</td>
      <td style="width: 33%;">NOMBRES Y APELLIDOS</td>
      <td style="width: 15%;">NÚMERO DE VOTOS</td>
    </tr>
    <?php
    $candsDos = [
        [
            'num'     => 1,
            'tipodoc' => ($vocero && !empty($vocero['tipodoc'])) ? $vocero['tipodoc'] : 'CC',
            'ndocusu' => ($vocero && !empty($vocero['ndocusu'])) ? $vocero['ndocusu'] : '',
            'nomusu'  => ($vocero && !empty($vocero['nomusu'])) ? $vocero['nomusu'] : ''
        ],
        [
            'num'     => 2,
            'tipodoc' => ($suplente && !empty($suplente['tipodoc'])) ? $suplente['tipodoc'] : 'CC',
            'ndocusu' => ($suplente && !empty($suplente['ndocusu'])) ? $suplente['ndocusu'] : '',
            'nomusu'  => ($suplente && !empty($suplente['nomusu'])) ? $suplente['nomusu'] : ''
        ]
    ];
    foreach ($candsDos as $cd):
    ?>
    <tr>
      <td class="txt-center txt-bold"><?= $cd['num'] ?>.</td>
      <td class="txt-center"><?= htmlspecialchars($cd['tipodoc']) ?></td>
      <td class="txt-center"><?= htmlspecialchars($cd['ndocusu']) ?></td>
      <td><?= htmlspecialchars($cd['nomusu']) ?></td>
      <td class="txt-center txt-bold">&nbsp;</td>
    </tr>
    <?php endforeach; ?>
  </table>
  <div class="footer-code">GOR-F-084V02</div>
</div>

<!-- PÁGINA 5: Datos de Vocero, Suplente, Conclusiones y Compromisos (Parte 1) -->
<div class="acta-page">
  <div class="header-logo"><?php if ($logoBase64): ?><img src="<?= $logoBase64 ?>" alt="SENA" style="display: block; margin: 0 auto;"><?php endif; ?></div>
  <div class="border-box">
    <div class="box-tag">VOCERO:</div><br>
    Los aprendices suscritos mediante la presente acta hemos elegido vocero de esta formación al Aprendiz.<br><br>
    <strong>Nombre Completo del aprendiz:</strong> <?= htmlspecialchars($nomVocero) ?><br>
    <strong>Tipo y Número de documento:</strong> <?= htmlspecialchars($docVocero) ?><br>
    <strong>Teléfono:</strong> <?= htmlspecialchars($telVocero) ?><br>
    <strong>Correo:</strong> <?= htmlspecialchars($corVocero) ?><br>
    <strong>Programa de formación:</strong> <?= htmlspecialchars($nomfic) ?><br>
    <strong>N. de Ficha:</strong> <?= htmlspecialchars($idfic) ?><br><br>

    <div class="box-tag">VOCERO SUPLENTE:</div><br>
    Los aprendices suscritos mediante la presente acta hemos elegido vocero suplente de esta formación al Aprendiz.<br><br>
    <strong>Nombre Completo del aprendiz:</strong> <?= htmlspecialchars($nomSuplente) ?><br>
    <strong>Tipo y Número de documento:</strong> <?= htmlspecialchars($docSuplente) ?><br>
    <strong>Teléfono:</strong> <?= htmlspecialchars($telSuplente) ?><br>
    <strong>Correo:</strong> <?= htmlspecialchars($corSuplente) ?><br>
    <strong>Programa de formación:</strong> <?= htmlspecialchars($nomfic) ?><br>
    <strong>N. de Ficha:</strong> <?= htmlspecialchars($idfic) ?><br><br>

    <em>Aprendices Electores: Se anexa a la presente acta el listado de asistentes a esta elección.</em>
  </div>

  <table class="tbl-acta">
    <tr><td colspan="4" class="txt-center txt-bold bg-gray" style="padding: 5px;">CONCLUSIONES</td></tr>
    <tr>
      <td colspan="4" style="padding: 6px; text-align: justify;">
        Se obtiene como resultado la elección del vocero y suplente del programa de formación.
      </td>
    </tr>
    <tr><td colspan="4" class="txt-center txt-bold bg-gray" style="padding: 5px;">ESTABLECIMIENTO Y ACEPTACIÓN DE COMPROMISOS</td></tr>
    <tr class="bg-gray txt-bold txt-center">
      <td style="width: 42%;">ACTIVIDAD / DECISIÓN</td>
      <td style="width: 16%;">FECHA</td>
      <td style="width: 22%;">RESPONSABLE</td>
      <td style="width: 20%;">FIRMA O PARTICIPACIÓN VIRTUAL</td>
    </tr>
    <tr>
      <td style="padding: 6px;">Participar en las estrategias de fortalecimiento de liderazgo que se convoquen desde el centro de formación profesional integral o regional.</td>
      <td class="txt-center" style="padding: 6px;">Vigencia <?= htmlspecialchars($año) ?></td>
      <td class="txt-center" style="padding: 6px; font-weight: bold;">Aprendices Vocero y suplente</td>
      <td style="padding: 4px; vertical-align: middle;">
        <div class="signature-line"></div>
        <span class="signature-label">FIRMA VOCERO</span>
        <div class="signature-line" style="margin-top: 6px;"></div>
        <span class="signature-label">FIRMA SUPLENTE</span>
      </td>
    </tr>
    <tr>
      <td style="padding: 6px;">Participar con los demás voceros y el (los) representante (s) del centro de formación profesional integral,</td>
      <td class="txt-center" style="padding: 6px;">Vigencia <?= htmlspecialchars($año) ?></td>
      <td class="txt-center" style="padding: 6px; font-weight: bold;">Aprendices Vocero y suplente</td>
      <td style="padding: 4px; vertical-align: middle;">
        <div class="signature-line"></div>
        <span class="signature-label">FIRMA VOCERO</span>
      </td>
    </tr>
  </table>
  <div class="footer-code">GOR-F-084 V02</div>
</div>

<!-- PÁGINA 6: Compromisos (cont), Asistentes y Aprobación Decisiones -->
<div class="acta-page">
  <div class="header-logo"><?php if ($logoBase64): ?><img src="<?= $logoBase64 ?>" alt="SENA" style="display: block; margin: 0 auto;"><?php endif; ?></div>
  <table class="tbl-acta">
    <tr class="bg-gray txt-bold txt-center">
      <td style="width: 42%;">ACTIVIDAD / DECISIÓN (Continuación)</td>
      <td style="width: 16%;">FECHA</td>
      <td style="width: 22%;">RESPONSABLE</td>
      <td style="width: 20%;">FIRMA O PARTICIPACIÓN VIRTUAL</td>
    </tr>
    <tr>
      <td style="padding: 6px;">en la formulación y realización de actividades, servicios y proyectos que beneficie a los aprendices que representa.</td>
      <td class="txt-center" style="padding: 6px;">Vigencia <?= htmlspecialchars($año) ?></td>
      <td class="txt-center" style="padding: 6px; font-weight: bold;">Aprendices Vocero y suplente</td>
      <td style="padding: 4px; vertical-align: middle;">
        <div class="signature-line"></div>
        <span class="signature-label">FIRMA SUPLENTE</span>
      </td>
    </tr>
    <tr>
      <td style="padding: 6px;">Invitar a los integrantes de su grupo, para el proceso formativo se desarrolle de forma armónica y de diálogo directo con los actores vinculados a la formación.</td>
      <td class="txt-center" style="padding: 6px;">Vigencia <?= htmlspecialchars($año) ?></td>
      <td class="txt-center" style="padding: 6px; font-weight: bold;">Aprendices Vocero y suplente</td>
      <td style="padding: 4px; vertical-align: middle;">
        <div class="signature-line"></div>
        <span class="signature-label">FIRMA VOCERO</span>
        <div class="signature-line" style="margin-top: 6px;"></div>
        <span class="signature-label">FIRMA SUPLENTE</span>
      </td>
    </tr>
  </table>

  <div class="txt-center txt-bold" style="font-size: 11px; margin: 10px 0 6px 0;">DE: ASISTENTES Y APROBACIÓN DECISIONES</div>
  <table class="tbl-acta">
    <tr class="bg-gray txt-bold txt-center">
      <td style="width: 32%;">NOMBRE</td>
      <td style="width: 28%;">DEPENDENCIA/ EMPRESA</td>
      <td style="width: 12%;">APRUEBA (SI/NO)</td>
      <td style="width: 12%;">OBSERVACIÓN</td>
      <td style="width: 16%;">FIRMA O PARTICIPACIÓN VIRTUAL</td>
    </tr>
    <tr>
      <td style="padding: 8px 6px;"><strong><?= htmlspecialchars($nomInstructor) ?></strong></td>
      <td style="padding: 8px 6px;">Instructor Líder</td>
      <td class="txt-center" style="padding: 8px 6px; font-weight: bold;">SI</td>
      <td class="txt-center" style="padding: 8px 6px;">Conforme</td>
      <td style="padding: 4px; vertical-align: bottom;"><div class="signature-line"></div></td>
    </tr>
    <tr>
      <td style="padding: 8px 6px;"><strong>Profesional de Bienestar</strong></td>
      <td style="padding: 8px 6px;">Bienestar al Aprendiz / CDA</td>
      <td class="txt-center" style="padding: 8px 6px; font-weight: bold;">SI</td>
      <td class="txt-center" style="padding: 8px 6px;">Conforme</td>
      <td style="padding: 4px; vertical-align: bottom;"><div class="signature-line"></div></td>
    </tr>
    <tr>
      <td style="padding: 8px 6px;">&nbsp;</td>
      <td style="padding: 8px 6px;">&nbsp;</td>
      <td class="txt-center" style="padding: 8px 6px;">&nbsp;</td>
      <td class="txt-center" style="padding: 8px 6px;">&nbsp;</td>
      <td style="padding: 4px; vertical-align: bottom;"><div class="signature-line"></div></td>
    </tr>
  </table>

  <!-- CUADRO DE LEY 1581 DE 2012 CON BORDE/MARGEN -->
  <table class="tbl-acta" style="margin-top: 8px; margin-bottom: 8px;">
    <tr>
      <td style="padding: 5px 8px; font-size: 8px; line-height: 1.25; text-align: justify;">
        De acuerdo con La Ley 1581 de 2012, Protección de Datos Personales, el Servicio Nacional de Aprendizaje SENA, se compromete a garantizar la seguridad y protección de los datos personales que se encuentran almacenados en este documento, y les dará el tratamiento correspondiente en cumplimiento de lo establecido legalmente.
      </td>
    </tr>
  </table>

  <!-- TÍTULO ANEXOS CENTRADO Y RECUADRO VACÍO ENMARCADO -->
  <div class="txt-center txt-bold" style="font-size: 10px; margin: 8px 0 4px 0;">ANEXOS</div>
  <table class="tbl-acta" style="margin-bottom: 8px;">
    <tr>
      <td style="height: 120px;">&nbsp;</td>
    </tr>
  </table>

  <div class="footer-code">GOR-F-084V02</div>
</div>

<!-- PÁGINA 7: Formato GOR-F-085 V02 - Registro de Asistencia -->
<div class="acta-page">
  <div class="header-logo"><?php if ($logoBase64): ?><img src="<?= $logoBase64 ?>" alt="SENA" style="display: block; margin: 0 auto;"><?php endif; ?></div>
  <table class="tbl-acta">
    <tr>
      <td colspan="11" class="txt-center txt-bold" style="padding: 6px; font-size: 10.5px;">
        REGISTRO DE ASISTENCIA / DÍA <?= htmlspecialchars($dia) ?> DEL MES DE <?= mb_strtoupper($mesNombre, 'UTF-8') ?> DEL AÑO <?= htmlspecialchars($año) ?>
      </td>
    </tr>
    <tr>
      <td colspan="11" style="padding: 5px; font-size: 9px; text-align: justify;">
        <strong>OBJETIVO (S):</strong> Generar escenarios de reconocimiento para aprendices en ejes de liderazgo, proyección social y formación de talentos. Adelantar la elección de voceros y representantes de aprendices - Actividad elección de voceros y suplentes de aprendices.
      </td>
    </tr>
    <tr class="bg-gray txt-bold txt-center" style="font-size: 8px;">
      <td style="width: 4%;">No</td>
      <td style="width: 22%;">NOMBRES Y APELLIDOS</td>
      <td style="width: 12%;">No. DOCUMENTO</td>
      <td style="width: 5%;">PLANTA</td>
      <td style="width: 6%;">CONTRATISTA</td>
      <td style="width: 5%;">OTRO</td>
      <td style="width: 14%;">DEPENDENCIA/ EMPRESA</td>
      <td style="width: 14%;">CORREO ELECTRÓNICO</td>
      <td style="width: 7%;">TELÉFONO</td>
      <td style="width: 5%;">GRABACIÓN</td>
      <td style="width: 6%;">FIRMA</td>
    </tr>
    <tr style="font-size: 8px;">
      <td class="txt-center">1.</td>
      <td><strong><?= htmlspecialchars($nomInstructor) ?></strong></td>
      <td class="txt-center"><?= htmlspecialchars($docInstructor) ?></td>
      <td></td>
      <td class="txt-center">X</td>
      <td></td>
      <td class="txt-center">Instructor Líder</td>
      <td><?= htmlspecialchars($emaInstructor) ?></td>
      <td></td>
      <td class="txt-center">SI</td>
      <td><div class="signature-line" style="height: 18px;"></div></td>
    </tr>
    <tr style="font-size: 8px;">
      <td class="txt-center">2.</td>
      <td><strong>Profesional de Bienestar</strong></td>
      <td class="txt-center"></td>
      <td class="txt-center">X</td>
      <td></td>
      <td></td>
      <td class="txt-center">Bienestar al Aprendiz</td>
      <td>bienestar@sena.edu.co</td>
      <td></td>
      <td class="txt-center">SI</td>
      <td><div class="signature-line" style="height: 18px;"></div></td>
    </tr>
    <?php for ($ra = 3; $ra <= 7; $ra++): ?>
    <tr style="font-size: 8px;">
      <td class="txt-center"><?= $ra ?>.</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td></td>
      <td></td>
      <td></td>
      <td></td>
      <td></td>
      <td></td>
      <td></td>
      <td><div class="signature-line" style="height: 18px;"></div></td>
    </tr>
    <?php endfor; ?>
  </table>
  <p class="law-clause">De acuerdo con La Ley 1581 de 2012, Protección de Datos Personales, el Servicio Nacional de Aprendizaje SENA, se compromete a garantizar la seguridad y protección de los datos personales que se encuentran almacenados en este documento, y les dará el tratamiento correspondiente en cumplimiento de lo establecido legalmente.</p>
  <div class="footer-code">GOR-F-085 V02</div>
</div>

<!-- PÁGINA 8: Formato GFPI-F-121 - Control Participación Actividades Bienestar -->
<div class="acta-page">
  <!-- LOGO SENA ENMARCADO DENTRO DE CUADRÍCULA -->
  <table class="tbl-acta" style="margin-bottom: 0; border-bottom: none;">
    <tr>
      <td style="padding: 6px; text-align: center; border: 1px solid #000;">
        <?php if ($logoBase64): ?>
          <img src="<?= $logoBase64 ?>" alt="SENA" style="display: block; margin: 0 auto; width: 50px; height: auto;">
        <?php endif; ?>
      </td>
    </tr>
  </table>
  <table class="tbl-acta" style="font-size: 8px;">
    <tr>
      <td colspan="10" class="txt-center txt-bold" style="padding: 4px; font-size: 9px;">
        PROCESO GESTIÓN DE FORMACIÓN PROFESIONAL INTEGRAL<br>
        FORMATO CONTROL PARTICIPACIÓN ACTIVIDADES DE BIENESTAR DEL APRENDIZ
      </td>
      <td colspan="3" class="txt-center" style="padding: 4px; font-size: 7.5px;">
        Versión: 03<br>Código: GFPI-F-121
      </td>
    </tr>
    <tr>
      <td colspan="13"><strong>CIUDAD Y FECHA:</strong> <?= htmlspecialchars($ciudadFecha) ?></td>
    </tr>
    <tr>
      <td colspan="13"><strong>REGIONAL Y CENTRO DE FORMACIÓN:</strong> Cundinamarca - Centro de Desarrollo Agroempresarial</td>
    </tr>
    <tr>
      <td colspan="13"><strong>OBJETIVO ESTRATEGICO DEL PLAN:</strong> Reconocer al aprendiz en su proceso de formación profesional integral mediante la implementación de un programa de estímulos que se materializa en los objetivos operativos descritos.</td>
    </tr>
    <tr>
      <td colspan="13"><strong>OBJETIVO OPERATIVO DEL PLAN:</strong> Generar escenarios de reconocimiento para aprendices en ejes de liderazgo, proyección social, formación y de talentos.</td>
    </tr>
    <tr>
      <td colspan="13"><strong>ACTIVIDAD A REALIZAR:</strong> Actividades de elección de voceros y Suplentes de aprendices.</td>
    </tr>
    <tr>
      <td colspan="13"><strong>PROPÓSITO DE LA ACTIVIDAD:</strong> Adelantar la elección de voceros y representantes de aprendices.</td>
    </tr>
  </table>

  <table class="tbl-assist-13">
    <thead>
      <tr class="bg-gray txt-bold" style="font-size: 6px;">
        <th style="width: 15%;">NOMBRE Y APELLIDO DEL APRENDIZ</th>
        <th style="width: 5%;">TIPO DE DOCUMENTO</th>
        <th style="width: 8%;"># DOCUMENTO IDENTIFICACIÓN</th>
        <th style="width: 4%;">GÉNERO</th>
        <th style="width: 6.5%;">NÚMERO DE FICHA</th>
        <th style="width: 6.5%;">MODALIDAD DE FORMACIÓN</th>
        <th style="width: 14.5%;">PROGRAMA DE FORMACIÓN</th>
        <th style="width: 6%;">JORNADA DE FORMACIÓN</th>
        <th style="width: 6%;">NIVEL DE FORMACIÓN</th>
        <th style="width: 8.5%;">CENTRO DE FORMACIÓN DEL APRENDIZ/ LUGAR</th>
        <th style="width: 9%;">CORREO ELECTRÓNICO</th>
        <th style="width: 6%;">TELÉFONO DE CONTACTO</th>
        <th style="width: 5%;">FIRMA DEL APRENDIZ</th>
      </tr>
    </thead>
    <tbody>
      <?php
      if (!empty($aprendices)):
          foreach ($aprendices as $ia => $ap):
              $gen = (!empty($ap['genero']) && $ap['genero'] !== 'N/A') ? $ap['genero'] : 'M';
              $mod = !empty($ap['modalidad']) ? $ap['modalidad'] : 'Presencial';
              $niv = !empty($ap['nivel_formacion']) ? $ap['nivel_formacion'] : 'Tecnólogo';
              $cen = !empty($ap['nomcen']) ? $ap['nomcen'] : 'Centro de Desarrollo Agroempresarial';
              $jor = !empty($ap['jornada']) && $ap['jornada'] !== 'N/A' ? $ap['jornada'] : $nomjor;
      ?>
      <tr>
        <td style="text-align: left; padding-left: 2px;"><?= htmlspecialchars($ap['nomusu'] ?? '') ?></td>
        <td><?= htmlspecialchars($ap['tipodoc'] ?? 'CC') ?></td>
        <td><?= htmlspecialchars($ap['ndocusu'] ?? '') ?></td>
        <td><?= htmlspecialchars($gen) ?></td>
        <td><?= htmlspecialchars($idfic) ?></td>
        <td><?= htmlspecialchars($mod) ?></td>
        <td style="text-align: left; padding-left: 2px;"><?= htmlspecialchars($nomfic) ?></td>
        <td><?= htmlspecialchars($jor) ?></td>
        <td><?= htmlspecialchars($niv) ?></td>
        <td><?= htmlspecialchars($cen) ?></td>
        <td style="text-align: left; padding-left: 1px;"><?= htmlspecialchars($ap['emausu'] ?? '') ?></td>
        <td><?= htmlspecialchars($ap['telcan'] ?? '') ?></td>
        <td><div class="signature-line-sm"></div></td>
      </tr>
      <?php
          endforeach;
      else:
          for ($ie = 1; $ie <= 6; $ie++):
      ?>
      <tr>
        <td>&nbsp;</td>
        <td></td>
        <td></td>
        <td></td>
        <td><?= htmlspecialchars($idfic) ?></td>
        <td>Presencial</td>
        <td><?= htmlspecialchars($nomfic) ?></td>
        <td><?= htmlspecialchars($nomjor) ?></td>
        <td>Tecnólogo</td>
        <td>CDA Chía</td>
        <td></td>
        <td></td>
        <td><div class="signature-line-sm"></div></td>
      </tr>
      <?php
          endfor;
      endif;
      ?>
    </tbody>
  </table>
  <div class="footer-code">GFPI-F-121</div>
</div>

</body>
</html>
<?php
        return ob_get_clean();
    }
}

// ==================================================================================
// 3. DESCARGA / COMPILACIÓN DIRECTA DE PDF SI `pdf=ok`
// ==================================================================================
if (isset($_GET['pdf']) && $_GET['pdf'] === 'ok') {
    $tipo   = isset($_REQUEST['tipo']) ? $_REQUEST['tipo'] : 'representante';
    $fidcen = isset($_REQUEST['fidcen']) ? $_REQUEST['fidcen'] : NULL;
    $fidjor = isset($_REQUEST['fidjor']) ? $_REQUEST['fidjor'] : NULL;
    $idfic  = isset($_REQUEST['idfic']) ? $_REQUEST['idfic'] : (isset($_REQUEST['fidfic']) ? $_REQUEST['fidfic'] : NULL);

    $html = '';

    if ($tipo === 'vocero') {
        $fichas = $votmact->getFichas();
        $fichaActual = [];
        $dat = [];
        $aprendicesFicha = [];
        $instructorActual = [];

        if ($idfic) {
            $fichaActual = $votmact->getFichaDetalle($idfic);
            $dat = $votmact->getVocerosElectosPorFicha($idfic);
            $aprendicesFicha = $votmact->getAprendicesPorFicha($idfic);
            $instructorActual = [
                'nomins'  => !empty($fichaActual['nomins']) ? $fichaActual['nomins'] : 'Instructor Lider / Delegado',
                'ndocins' => isset($fichaActual['ndocins']) ? $fichaActual['ndocins'] : '',
                'emains'  => isset($fichaActual['emains']) ? $fichaActual['emains'] : ''
            ];
        }

        $cfgVocero = [
            'ciudad_fecha' => 'Chía, ' . date('d') . ' de ' . $mesNombre . ' de ' . date('Y'),
            'dia'          => date('d'),
            'mes_nombre'   => $mesNombre,
            'año'          => $año,
            'fcan'         => $fcan,
            'lugar'        => 'Ambiente de formación y sede/subsede',
            'ficha'        => $fichaActual,
            'vocero'       => isset($dat[0]) ? $dat[0] : null,
            'suplente'     => isset($dat[1]) ? $dat[1] : null,
            'candidatos'   => $dat,
            'instructor'   => $instructorActual,
            'aprendices'   => $aprendicesFicha,
            'is_pdf'       => true
        ];
        $html = renderizarActaVocerosOficialSena($cfgVocero);
        $nombrePdf = "Acta_Vocero_Ficha_" . ($idfic ? $idfic : '0') . "_" . $fecha2 . ".pdf";

    } else {
        // Acta de Representante
        $dat = $votmact->selAll($fidcen, $fidjor);
        $djor = $votmact->getJor();
        if ($fidjor) {
            $djor = array_filter($djor, function($j) use ($fidjor) {
                return $j['idval'] == $fidjor;
            });
        }

        $candidatosPorJornada = [];
        $totalVotosPorJornada = [];
        foreach ($dat as $d) {
            $jorId = $d['jornada'];
            if (!isset($candidatosPorJornada[$jorId])) {
                $candidatosPorJornada[$jorId] = [];
                $totalVotosPorJornada[$jorId] = 0;
            }
            $candidatosPorJornada[$jorId][] = $d;
            $totalVotosPorJornada[$jorId] += (int)$d['total_votos'];
        }

        $secciones = [];
        if ($djor) {
            foreach ($djor as $dj) {
                $jorId = $dj['idval'];
                if (isset($candidatosPorJornada[$jorId])) {
                    $secciones[] = [
                        'subtitulo'   => 'RESULTADOS JORNADA ' . $dj['nomval'],
                        'candidatos'  => $candidatosPorJornada[$jorId],
                        'total_votos' => $totalVotosPorJornada[$jorId]
                    ];
                }
            }
        }

        $cfg = [
            'ancho'       => 750,
            'titulo'      => 'ACTA DE VOTACIÓN DE REPRESENTANTES DE APRENDICES SENA',
            'numero_acta' => '001',
            'fecha'       => $fecha,
            'hora_inicio' => $hora_inicio,
            'hora_fin'    => $hora_fin,
            'lugar'       => 'Bienestar al Aprendiz - Centro de Desarrollo Agroempresarial',
            'regional'    => 'Regional Cundinamarca, Centro de Desarrollo Agroempresarial',
            'año'         => $año,
            'fcan'        => $fcan,
            'secciones'   => $secciones,
            'representante_electo' => (!empty($dat) && $dat[0]['total_votos'] > 0) ? $dat[0]['nomusu'] : ''
        ];
        $html = renderizarActaInstitucionalSena($cfg);
        $nombrePdf = "Acta_Representantes_" . $fecha2 . ".pdf";
    }

    // Autoloader autónomo y selectivo de Dompdf (evita platform_check global de Composer en PHP 8.0)
    if (!class_exists('Dompdf\Dompdf')) {
        $vendorDir = file_exists('vendor') ? 'vendor' : (file_exists('../vendor') ? '../vendor' : '');
        if ($vendorDir) {
            spl_autoload_register(function ($class) use ($vendorDir) {
                $prefixes = [
                    'Dompdf\\'  => $vendorDir . '/dompdf/dompdf/src/',
                    'FontLib\\' => $vendorDir . '/phenx/php-font-lib/src/FontLib/',
                    'Svg\\'     => $vendorDir . '/phenx/php-svg-lib/src/Svg/',
                ];
                foreach ($prefixes as $prefix => $base_dir) {
                    $len = strlen($prefix);
                    if (strncmp($prefix, $class, $len) !== 0) continue;
                    $relative_class = substr($class, $len);
                    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
                    if (file_exists($file)) {
                        require_once $file;
                        return;
                    }
                }

                // Clases auxiliares HTML5 de Dompdf
                if (strpos($class, 'HTML5_') === 0) {
                    $html5File = $vendorDir . '/dompdf/dompdf/lib/html5lib/' . substr($class, 6) . '.php';
                    if (file_exists($html5File)) {
                        require_once $html5File;
                        return;
                    }
                }
            });

            if (file_exists($vendorDir . '/dompdf/dompdf/lib/Cpdf.php')) {
                require_once $vendorDir . '/dompdf/dompdf/lib/Cpdf.php';
            }
        }

        // Fallback secundario a autoload de Composer solo si no se pudo cargar directamente
        if (!class_exists('Dompdf\Dompdf') && file_exists('vendor/autoload.php')) {
            @include_once('vendor/autoload.php');
        }
    }

    if (class_exists('Dompdf\Dompdf')) {
        $dompdf = new Dompdf\Dompdf();
        $dompdf->setPaper(array(0, 0, 612, 792));
        $dompdf->loadHtml($html);
        $dompdf->render();
        while (ob_get_level()) {
            ob_end_clean();
        }
        if (php_sapi_name() === 'cli') {
            echo $dompdf->output();
        } else {
            $dompdf->stream($nombrePdf, array("Attachment" => false));
        }
    } else {
        echo $html;
    }
    exit;
}

// ==================================================================================
// 4. MODO VISTA WEB INTERACTIVA (home.php?pg=1217)
// ==================================================================================
$tab    = isset($_REQUEST['tab']) ? $_REQUEST['tab'] : 'representante';
$fidcen = isset($_REQUEST['fidcen']) ? $_REQUEST['fidcen'] : NULL;
$fidjor = isset($_REQUEST['fidjor']) ? $_REQUEST['fidjor'] : NULL;
$fidfic = isset($_REQUEST['fidfic']) ? $_REQUEST['fidfic'] : NULL;

// Datos para métricas y opciones
$dcen = $votmact->getCen();
$djor = $votmact->getJor();
$fichas = $votmact->getFichas($fidjor);

if ($tab == 'representante') {
    $datRep = $votmact->selAll($fidcen, $fidjor);
    $totVotos = 0;
    foreach ($datRep as $dr) {
        $totVotos += (int)$dr['total_votos'];
    }
    $cantCandidatos = count($datRep);
    $lider = !empty($datRep) && $datRep[0]['total_votos'] > 0 ? $datRep[0] : null;
    $nombreJornadaTxt = "General";
    if ($fidjor && $djor) {
        foreach ($djor as $dj) {
            if ($dj['idval'] == $fidjor) $nombreJornadaTxt = $dj['nomval'];
        }
    }
} else {
    $fichaActual = [];
    $datVoc = [];
    $aprendicesFicha = [];
    $totVotos = 0;
    if ($fidfic) {
        $fichaActual = $votmact->getFichaDetalle($fidfic);
        $datVoc = $votmact->getVocerosElectosPorFicha($fidfic);
        $aprendicesFicha = $votmact->getAprendicesPorFicha($fidfic);
        foreach ($datVoc as $dv) {
            $totVotos += (int)$dv['total_votos'];
        }
    }
    $cantCandidatos = count($datVoc);
    $lider = !empty($datVoc) && $datVoc[0]['total_votos'] > 0 ? $datVoc[0] : null;
}
?>

<div class="conte">
    <?php echo titulo2("<i class='" . (isset($icono) ? $icono : 'fa-file-signature') . "'></i> Generación de Actas de Votación", 2); ?>

    <!-- TARJETAS DE MÉTRICAS KPI (ESTILO VVIDGEN) -->
    <div class="row my-4 g-4">
        <!-- KPI 1: Total Votación Escrutada -->
        <div class="col-12 col-md-4">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #3e4a3d;">
                            <?= ($tab == 'representante') ? 'Votos Escrutados' : 'Votos en Ficha'; ?>
                        </span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #0b1c30;">
                                <?= number_format($totVotos, 0, ',', '.'); ?>
                            </span>
                            <span style="font-size: 13px; color: #3e4a3d;">votos</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box" style="background-color: #d1e7dd; color: #0f5132;">
                        <i class="fa-solid fa-check-to-slot fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 11px; color: #0f5132;">
                        <i class="fa-solid fa-shield-halved" style="font-size: 12px;"></i> Escrutinio Oficial
                    </span>
                    <span style="font-size: 11px; color: #3e4a3d;">
                        <?= ($tab == 'representante') ? 'Jornada: ' . htmlspecialchars($nombreJornadaTxt) : ($fidfic ? 'Ficha: ' . htmlspecialchars($fidfic) : 'Seleccione ficha'); ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- KPI 2: Candidato Líder / Ganador -->
        <div class="col-12 col-md-4">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div style="max-width: 75%;">
                        <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #006b29;">
                            <?= ($tab == 'representante') ? 'Representante Electo' : 'Vocero Electo'; ?>
                        </span>
                        <div class="mt-2 text-truncate">
                            <span class="fw-bold text-truncate d-block" style="font-size: 20px; line-height: 1.2; color: #006b29;" title="<?= $lider ? htmlspecialchars($lider['nomusu']) : 'Sin datos'; ?>">
                                <?= $lider ? htmlspecialchars($lider['nomusu']) : 'Pendiente'; ?>
                            </span>
                            <span style="font-size: 12px; color: rgba(0, 107, 41, 0.85);">
                                <?= $lider ? $lider['total_votos'] . ' votos obtenidos' : 'A la espera de escrutinio'; ?>
                            </span>
                        </div>
                    </div>
                    <div class="kpi-icon-box" style="background-color: #fef3c7; color: #b45309;">
                        <i class="fa-solid fa-crown fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="fw-semibold d-inline-flex align-items-center gap-2" style="font-size: 11px; color: #006b29;">
                        <span class="pulse-dot"></span> Mayoría Absoluta
                    </span>
                    <span style="font-size: 11px; color: #3e4a3d;">
                        <?= ($tab == 'representante') ? ($lider && isset($lider['nomcen']) ? htmlspecialchars($lider['nomcen']) : 'Centro Chía') : ($lider && isset($lider['nomfic']) ? htmlspecialchars($lider['nomfic']) : 'Sede Chía'); ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- KPI 3: Estado de Emisión del Documento -->
        <div class="col-12 col-md-4">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #ba1a1a;">Estado del Documento</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #0b1c30;">100%</span>
                            <span style="font-size: 13px; color: #3e4a3d;">listo para emisión</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box" style="background-color: #ffdad6; color: #ba1a1a;">
                        <i class="fa-solid fa-file-pdf fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="d-inline-flex align-items-center gap-1" style="font-size: 11px; color: #3e4a3d;">
                        <i class="fa-solid fa-file-signature" style="font-size: 12px;"></i> <?= ($tab == 'representante') ? 'Formato GOR-F-084' : 'GOR-F-084 / GFPI-F-121'; ?>
                    </span>
                    <span class="fw-semibold text-success" style="font-size: 11px;">PDF Autoverificado</span>
                </div>
            </div>
        </div>
    </div>

    <!-- TOOLBAR PRINCIPAL DE CONTROL, FILTROS Y PÍLDORAS (ESTILO VVIDGEN) -->
    <div class="filter-toolbar-container">
        <!-- Píldoras de Navegación de Actas -->
        <div class="toolbar-left-group">
            <div class="filter-pills-group">
                <a href="home.php?pg=<?= $pg ?>&tab=representante" class="btn-filter-pill <?= ($tab == 'representante') ? 'active' : ''; ?>">
                    <span class="pill-dot" style="background-color: #006b29;"></span>
                    <span>Acta de Representante</span>
                    <span class="pill-count"><?= isset($datRep) ? count($datRep) : '0'; ?></span>
                </a>
                <a href="home.php?pg=<?= $pg ?>&tab=vocero" class="btn-filter-pill <?= ($tab == 'vocero') ? 'active' : ''; ?>">
                    <span class="pill-dot" style="background-color: #0d6efd;"></span>
                    <span>Acta de Vocero</span>
                    <span class="pill-count"><?= count($fichas); ?></span>
                </a>
            </div>
        </div>

        <!-- Acciones Directas de Emisión y Filtro -->
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <?php if ($tab == 'representante'): ?>
                <a href="views/votract.php?pdf=ok&tipo=representante&fidcen=<?= $fidcen; ?>&fidjor=<?= $fidjor; ?>" target="_blank" class="btn btn-success btn-sm fw-bold shadow-sm d-inline-flex align-items-center gap-2 px-3 py-2">
                    <i class="fas fa-print"></i> Emitir PDF Oficial
                </a>
            <?php else: ?>
                <?php if ($fidfic): ?>
                    <a href="views/votract.php?pdf=ok&tipo=vocero&idfic=<?= $fidfic; ?>" target="_blank" class="btn btn-success btn-sm fw-bold shadow-sm d-inline-flex align-items-center gap-2 px-3 py-2">
                        <i class="fas fa-print"></i> Emitir PDF Vocero
                    </a>
                <?php else: ?>
                    <button type="button" class="btn btn-secondary btn-sm shadow-sm px-3 py-2" disabled>
                        <i class="fas fa-print me-1"></i> Seleccione una Ficha
                    </button>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- BARRA SECUNDARIA DE FILTROS EN LÍNEA -->
    <div class="card shadow-sm border-0 p-3 mb-4 bg-light">
        <?php if ($tab == 'representante'): ?>
            <form action="home.php?pg=<?= $pg ?>&tab=representante" method="POST" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label for="fidjor" class="form-label fw-semibold text-secondary small">
                        <i class="fa-solid fa-clock text-success me-1"></i> Filtrar por Jornada Formativa
                    </label>
                    <select name="fidjor" id="fidjor" class="form-select form-select-sm" onchange="this.form.submit();">
                        <option value="">-- Todas las jornadas (Escrutinio General) --</option>
                        <?php if ($djor): ?>
                            <?php foreach ($djor as $dj): ?>
                                <option value="<?= $dj['idval']; ?>" <?= ($fidjor == $dj['idval']) ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($dj['nomval']); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="col-md-5">
                    <label for="fidcen" class="form-label fw-semibold text-secondary small">
                        <i class="fa-solid fa-building text-success me-1"></i> Centro de Formación
                    </label>
                    <select name="fidcen" id="fidcen" class="form-select form-select-sm" onchange="this.form.submit();">
                        <option value="">-- Todos los centros --</option>
                        <?php if ($dcen): ?>
                            <?php foreach ($dcen as $dc): ?>
                                <option value="<?= $dc['idcen']; ?>" <?= ($fidcen == $dc['idcen']) ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($dc['nomcen']); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="col-md-2 text-end">
                    <button type="submit" class="btn btn-outline-secondary btn-sm w-100">
                        <i class="fa-solid fa-rotate me-1"></i> Actualizar
                    </button>
                </div>
            </form>
        <?php else: ?>
            <form action="home.php?pg=<?= $pg ?>&tab=vocero" method="POST" id="fichaForm" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="fidjor_vocero" class="form-label fw-semibold text-secondary small">
                        <i class="fa-solid fa-clock text-primary me-1"></i> Filtrar por Jornada
                    </label>
                    <select name="fidjor" id="fidjor_vocero" class="form-select form-select-sm" onchange="this.form.submit();">
                        <option value="">-- Todas las jornadas --</option>
                        <?php if ($djor): ?>
                            <?php foreach ($djor as $dj): ?>
                                <option value="<?= $dj['idval']; ?>" <?= ($fidjor == $dj['idval']) ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($dj['nomval']); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="col-md-5">
                    <label for="idfic_vocero" class="form-label fw-semibold text-secondary small">
                        <i class="fa-solid fa-keyboard text-primary me-1"></i> Número de Ficha de Formación
                    </label>
                    <input type="number" name="fidfic" id="idfic_vocero" class="form-control form-control-sm"
                           placeholder="Escriba número de ficha (Ej: 2996491)"
                           value="<?= htmlspecialchars($fidfic ?? ''); ?>" required list="listadoFichas">
                    <datalist id="listadoFichas">
                        <?php if ($fichas): ?>
                            <?php foreach ($fichas as $f): ?>
                                <option value="<?= $f['idfic']; ?>"><?= htmlspecialchars($f['idfic']); ?> - <?= htmlspecialchars($f['nomfic']); ?> (<?= htmlspecialchars($f['nomval']); ?>)</option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </datalist>
                </div>
                <div class="col-md-3 text-end d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100 fw-semibold">
                        <i class="fa-solid fa-magnifying-glass me-1"></i> Buscar Ficha
                    </button>
                    <?php if ($fidfic): ?>
                        <a href="home.php?pg=<?= $pg ?>&tab=vocero" class="btn btn-outline-secondary btn-sm" title="Limpiar búsqueda">
                            <i class="fa-solid fa-xmark"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        <?php endif; ?>
    </div>

    <!-- CONTENEDOR DE VISTA PREVIA DEL DOCUMENTO SENA EN VIVO -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-dark text-white fw-bold d-flex justify-content-between align-items-center py-3 px-4">
            <span class="d-flex align-items-center gap-2">
                <i class="fa-solid fa-file-contract text-success fs-5"></i>
                <span>Vista Previa del Acta Oficial SENA</span>
                <small class="text-white-50 ms-2">(<?= ($tab == 'representante') ? 'Elección de Representantes' : 'Elección de Voceros de Grupo'; ?>)</small>
            </span>
            <span class="badge bg-success px-3 py-2">
                <i class="fa-solid fa-circle-check me-1"></i> Formato Oficial Verificado
            </span>
        </div>
        <div class="card-body p-4 bg-white" style="overflow-x: auto;">
            <?php
            if ($tab == 'representante') {
                $candidatosPorJornada = [];
                $totalVotosPorJornada = [];
                foreach ($datRep as $d) {
                    $jorId = $d['jornada'];
                    if (!isset($candidatosPorJornada[$jorId])) {
                        $candidatosPorJornada[$jorId] = [];
                        $totalVotosPorJornada[$jorId] = 0;
                    }
                    $candidatosPorJornada[$jorId][] = $d;
                    $totalVotosPorJornada[$jorId] += (int)$d['total_votos'];
                }

                $seccionesPrev = [];
                if ($djor) {
                    foreach ($djor as $dj) {
                        $jorId = $dj['idval'];
                        if (isset($candidatosPorJornada[$jorId])) {
                            $seccionesPrev[] = [
                                'subtitulo'   => 'RESULTADOS JORNADA ' . $dj['nomval'],
                                'candidatos'  => $candidatosPorJornada[$jorId],
                                'total_votos' => $totalVotosPorJornada[$jorId]
                            ];
                        }
                    }
                }

                $cfgPrev = [
                    'ancho'       => 750,
                    'titulo'      => 'ACTA DE VOTACIÓN DE REPRESENTANTES DE APRENDICES SENA',
                    'numero_acta' => '001',
                    'fecha'       => $fecha,
                    'hora_inicio' => $hora_inicio,
                    'hora_fin'    => $hora_fin,
                    'lugar'       => 'Bienestar al Aprendiz - Centro de Desarrollo Agroempresarial',
                    'regional'    => 'Regional Cundinamarca, Centro de Desarrollo Agroempresarial',
                    'año'         => $año,
                    'fcan'        => $fcan,
                    'secciones'   => $seccionesPrev,
                    'representante_electo' => $lider ? $lider['nomusu'] : ''
                ];
                echo renderizarActaInstitucionalSena($cfgPrev);
            } else {
                if ($fidfic) {
                    $instructorActual = [
                        'nomins'  => !empty($fichaActual['nomins']) ? $fichaActual['nomins'] : 'Instructor Lider / Delegado',
                        'ndocins' => isset($fichaActual['ndocins']) ? $fichaActual['ndocins'] : '',
                        'emains'  => isset($fichaActual['emains']) ? $fichaActual['emains'] : ''
                    ];
                    $cfgVoceroPrev = [
                        'ciudad_fecha' => 'Chía, ' . date('d') . ' de ' . $mesNombre . ' de ' . date('Y'),
                        'dia'          => date('d'),
                        'mes_nombre'   => $mesNombre,
                        'año'          => $año,
                        'fcan'         => $fcan,
                        'lugar'        => 'Ambiente de formación y sede/subsede',
                        'ficha'        => $fichaActual,
                        'vocero'       => isset($datVoc[0]) ? $datVoc[0] : null,
                        'suplente'     => isset($datVoc[1]) ? $datVoc[1] : null,
                        'candidatos'   => $datVoc,
                        'instructor'   => $instructorActual,
                        'aprendices'   => $aprendicesFicha,
                        'is_pdf'       => false
                    ];
                    echo renderizarActaVocerosOficialSena($cfgVoceroPrev);
                } else {
                    echo '<div class="alert alert-info shadow-sm d-flex align-items-center mb-0" role="alert">
                            <i class="fa-solid fa-circle-info fa-2x me-3 text-info"></i>
                            <div>
                                <h6 class="alert-heading mb-1 fw-bold">Selección requerida</h6>
                                Por favor, elija una jornada y ficha de formación en la barra de filtros superior para previsualizar y emitir el acta oficial de elección de voceros.
                            </div>
                          </div>';
                }
            }
            ?>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    let h1 = document.querySelector(".title-page");
    if (h1) {
        document.title = h1.textContent.trim();
    }
});
</script>
