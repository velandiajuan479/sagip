<?php require_once 'controllers/edtcsedt.php'; ?>

<div class="conte">
    <?php echo titulo2("<i class='" . (isset($icono) ? $icono : "fa-solid fa-file-signature") . "'></i> Solicitud EDT", 1); ?>

    <div class="d-flex justify-content-end mb-3">
        <a href="home.php?pg=1601" class="btn btn-outline-success btn-sm d-flex align-items-center gap-1 shadow-sm">
            <i class="fa-solid fa-clipboard-check"></i>
            <span>Ir a Aprobación EDT</span>
        </a>
    </div>

    <?php
    // Cálculo de Métricas y Estadísticas para el Dashboard EDT
    $total_solicitudes = is_array($datAll) ? count($datAll) : 0;
    $pendientes = 0;
    $aprobadas_coord = 0;
    $aprobadas_admin = 0;
    $listado_cargado = 0;
    $matriculadas = 0;
    $total_cupos = 0;

    $estados_count = [
        'Pendiente Coordinador' => 0,
        'Aprobado Coordinador' => 0,
        'Aprobado Admin Educativa' => 0,
        'Listado Cargado' => 0,
        'Matriculado' => 0,
        'Rechazado Coordinador' => 0,
        'Rechazado Admin Educativa' => 0
    ];

    $programas_top = [];

    if ($datAll) {
        foreach ($datAll as $dt) {
            $raw_est = $dt['convht'];
            $est = $raw_est;
            if ($raw_est && strpos($raw_est, 'Listado Cargado:') === 0) {
                $est = 'Listado Cargado';
            }
            
            if (isset($estados_count[$est])) {
                $estados_count[$est]++;
            }
            
            if ($est === 'Pendiente Coordinador') $pendientes++;
            elseif ($est === 'Aprobado Coordinador') $aprobadas_coord++;
            elseif ($est === 'Aprobado Admin Educativa') $aprobadas_admin++;
            elseif ($est === 'Listado Cargado') $listado_cargado++;
            elseif ($est === 'Matriculado') $matriculadas++;

            $total_cupos += intval($dt['cupo']);

            $nompro = $dt['nompro'] ? $dt['nompro'] : 'Sin Programa';
            $programas_top[$nompro] = ($programas_top[$nompro] ?? 0) + 1;
        }
    }
    ?>

    <!-- ESTILOS RESPONSIVOS ESTRICTAMENTE VERTICALES SIN DESPLAZAMIENTO HORIZONTAL -->
    <style>
        .conte, .dashboard-summary, .inser, .table-responsive {
            max-width: 100% !important;
            box-sizing: border-box !important;
        }
        .dashboard-summary {
            background: #ffffff;
            padding: 16px 20px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.03);
            margin-bottom: 20px;
            overflow: hidden;
        }
        @media (max-width: 576px) {
            .conte, body {
                overflow-x: hidden !important;
            }
            .inser {
                padding: 12px !important;
                width: 100% !important;
            }
            .form-group {
                width: 100% !important;
                max-width: 100% !important;
                flex: 0 0 100% !important;
                margin-bottom: 10px !important;
            }
            .form-group label {
                font-size: 1.1rem !important;
            }
            
            /* Ajuste de Tabla sin Desbordamiento Horizontal */
            .table-responsive {
                border: 0 !important;
                padding: 0 !important;
                overflow-x: hidden !important;
            }
            #example {
                width: 100% !important;
                font-size: 0.85rem !important;
            }
            #example th, #example td {
                padding: 6px 4px !important;
                white-space: normal !important;
                word-break: break-word !important;
            }
            .dataTables_wrapper {
                width: 100% !important;
                overflow-x: hidden !important;
            }
            .dataTables_length, .dataTables_filter, .dt-buttons {
                float: none !important;
                text-align: left !important;
                width: 100% !important;
                margin-bottom: 8px !important;
            }
            .dataTables_filter input {
                width: 100% !important;
                margin-left: 0 !important;
                box-sizing: border-box !important;
            }
        }
    </style>

    <!-- SECCIÓN RESUMEN COMPACTO Y FLUJO DE ESTADOS (ESTILO VVIDGEN) -->
    <div class="row g-3 mb-4">
        <!-- Card 1: Total Solicitudes -->
        <div class="col-12 col-sm-6 col-md-4 col-xl">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #3e4a3d;">Total Solicitudes</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #0b1c30;"><?= $total_solicitudes; ?></span>
                            <span style="font-size: 13px; color: #3e4a3d;">registradas</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box" style="background-color: #b5dcfe; color: #3c627f;">
                        <i class="fa-solid fa-layer-group fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 11px; color: #3c627f;">
                        <i class="fa-solid fa-arrow-trend-up" style="font-size: 12px;"></i> Total Sistema
                    </span>
                    <span style="font-size: 11px; color: #3e4a3d;">Eventos EDT</span>
                </div>
            </div>
        </div>

        <!-- Card 2: Pendientes -->
        <div class="col-12 col-sm-6 col-md-4 col-xl">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #b45309;">Pendientes Coord.</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #b45309;"><?= $pendientes; ?></span>
                            <span style="font-size: 13px; color: #b45309;">en espera</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box" style="background-color: #fef3c7; color: #b45309;">
                        <i class="fa-solid fa-hourglass-half fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 11px; color: #b45309;">
                        <i class="fa-solid fa-clock" style="font-size: 12px;"></i> Revisión Inicial
                    </span>
                    <span style="font-size: 11px; color: #3e4a3d;">Coordinador</span>
                </div>
            </div>
        </div>

        <!-- Card 3: Aprobadas Admin -->
        <div class="col-12 col-sm-6 col-md-4 col-xl">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #0284c7;">Aprobadas Admin</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #0284c7;"><?= ($aprobadas_coord + $aprobadas_admin + $listado_cargado); ?></span>
                            <span style="font-size: 13px; color: #0284c7;">en trámite</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box" style="background-color: #e0f2fe; color: #0284c7;">
                        <i class="fa-solid fa-user-check fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 11px; color: #0284c7;">
                        <i class="fa-solid fa-circle-check" style="font-size: 12px;"></i> En Proceso
                    </span>
                    <span style="font-size: 11px; color: #3e4a3d;">Admin Educativa</span>
                </div>
            </div>
        </div>

        <!-- Card 4: Matriculadas -->
        <div class="col-12 col-sm-6 col-md-4 col-xl">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #006b29;">Matriculadas</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #006b29;"><?= $matriculadas; ?></span>
                            <span style="font-size: 13px; color: #006b29;">con ficha</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box" style="background-color: #7afd88; color: #006b24;">
                        <i class="fa-solid fa-graduation-cap fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="fw-semibold d-inline-flex align-items-center gap-2" style="font-size: 11px; color: #006b29;">
                        <span class="pulse-dot"></span> Finalizadas
                    </span>
                    <span style="font-size: 11px; color: #3e4a3d;">SOFIA Plus</span>
                </div>
            </div>
        </div>

        <!-- Card 5: Total Cupos EDT -->
        <div class="col-12 col-sm-6 col-md-4 col-xl">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #0f766e;">Total Cupos EDT</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #0b1c30;"><?= $total_cupos; ?></span>
                            <span style="font-size: 13px; color: #3e4a3d;">cupos</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box" style="background-color: #ccfbf1; color: #0f766e;">
                        <i class="fa-solid fa-users fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 11px; color: #0f766e;">
                        <i class="fa-solid fa-user-group" style="font-size: 12px;"></i> Capacidad Total
                    </span>
                    <span style="font-size: 11px; color: #3e4a3d;">Beneficiarios</span>
                </div>
            </div>
        </div>
    </div>

    <div class="inser shadow-sm p-3 mb-4 bg-white rounded border border-dark" id="frmins">
        <form action="home.php?pg=<?= $pg; ?>" method="POST">
            
            <!-- ENCABEZADO INSTITUCIONAL SENA FORMATO EDT -->
            <div class="table-responsive mb-0">
                <table class="table table-bordered border-dark text-center align-middle mb-0" style="border-width: 2px;">
                    <thead class="bg-light">
                        <tr>
                            <th class="py-2 text-uppercase text-dark fw-bold" style="font-size: 0.85rem;">
                                SERVICIO NACIONAL DE APRENDIZAJE - SENA<br>
                                REGIONAL CUNDINAMARCA<br>
                                CENTRO DE DESARROLLO AGROEMPRESARIAL
                            </th>
                        </tr>
                        <tr class="bg-white">
                            <th class="py-2 text-dark fw-bolder text-uppercase" style="font-size: 0.95rem; letter-spacing: 0.5px;">
                                FORMATO CREACIÓN EVENTO DE DIVULGACIÓN TECNOLÓGICA
                            </th>
                        </tr>
                    </thead>
                </table>
            </div>

            <!-- BLOQUE HIGHLIGHT AMARILLO: CAMPOS GENERADOS POR EL SISTEMA -->
            <div class="table-responsive mb-0">
                <table class="table table-bordered border-dark align-middle mb-0" style="border-width: 2px;">
                    <tbody>
                        <tr style="background-color: #ffff00;">
                            <td style="width: 35%; background-color: #ffff00; font-weight: bold; color: #000;" class="align-middle">
                                Código SENA (sistema lo genera):
                            </td>
                            <td style="background-color: #ffff00;">
                                <input type="text" name="codsena" class="form-control form-control-sm bg-warning text-dark border-0 fw-bold" 
                                       value="<?= ($dtOne && isset($dtOne[0]['codsena'])) ? htmlspecialchars($dtOne[0]['codsena']) : 'GENERADO POR EL SISTEMA'; ?>" readonly>
                            </td>
                        </tr>
                        <tr style="background-color: #ffff00;">
                            <td style="background-color: #ffff00; font-weight: bold; color: #000;" class="align-middle">
                                Numero de Ficha (sistema lo genera):
                            </td>
                            <td style="background-color: #ffff00;">
                                <input type="text" class="form-control form-control-sm bg-warning text-dark border-0 fw-bold" 
                                       value="<?= ($dtOne && isset($dtOne[0]['idfic']) && $dtOne[0]['idfic']) ? htmlspecialchars($dtOne[0]['idfic']) : 'SISTEMA LO GENERA'; ?>" readonly>
                            </td>
                        </tr>
                        <tr style="background-color: #ffff00;">
                            <td style="background-color: #ffff00; font-weight: bold; color: #000;" class="align-middle">
                                Codigo de Solicitud (Sistema lo genera):
                            </td>
                            <td style="background-color: #ffff00;">
                                <input type="text" class="form-control form-control-sm bg-warning text-dark border-0 fw-bold" 
                                       value="<?= ($dtOne && isset($dtOne[0]['idnorad'])) ? '#' . $dtOne[0]['idnorad'] : 'SISTEMA LO GENERA'; ?>" readonly>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- TABLA PRINCIPAL DE FORMULARIO EDT (EXCLUSIVAMENTE LO QUE APARECE EN LA IMAGEN) -->
            <div class="table-responsive">
                <table class="table table-bordered border-dark align-middle mb-0" style="border-width: 2px;">
                    <tbody>
                        <!-- Denominación -->
                        <tr>
                            <td style="width: 35%; font-weight: bold;" class="bg-white">Denominación:</td>
                            <td>
                                <textarea name="denominacion" id="denominacion" class="form-control form-control-sm" rows="2"><?= ($dtOne && isset($dtOne[0]['denominacion'])) ? htmlspecialchars($dtOne[0]['denominacion']) : (($dtOne && isset($dtOne[0]['codslem'])) ? htmlspecialchars($dtOne[0]['codslem']) : ''); ?></textarea>
                            </td>
                        </tr>

                        <!-- Ocupación -->
                        <tr>
                            <td style="font-weight: bold;" class="bg-white">Ocupación:</td>
                            <td>
                                <select name="ocupacion" class="form-select form-select-sm">
                                    <option value="">SELECCIONAR</option>
                                    <?php 
                                    $ocupaciones = ["AGROPECUARIO", "TECNOLOGÍA E INFORMÁTICA", "MECÁNICA Y REPARACIÓN", "ADMINISTRACIÓN Y COMERCIO", "AGROINDUSTRIA", "SERVICIOS", "GESTIÓN AMBIENTAL", "CONSTRUCCIÓN", "OTRO"];
                                    $cur_val = ($dtOne && isset($dtOne[0]['ocupacion'])) ? $dtOne[0]['ocupacion'] : '';
                                    foreach ($ocupaciones as $oc) {
                                        $sel = ($cur_val == $oc) ? 'selected' : '';
                                        echo "<option value='$oc' $sel>$oc</option>";
                                    }
                                    ?>
                                </select>
                            </td>
                        </tr>

                        <!-- Sector económico -->
                        <tr>
                            <td style="font-weight: bold;" class="bg-white">Sector económico:</td>
                            <td>
                                <select name="sector_econ" class="form-select form-select-sm">
                                    <option value="">SELECCIONAR</option>
                                    <?php 
                                    $sectores = ["PRIMARIO (AGRICULTURA / GANADERÍA)", "SECUNDARIO (INDUSTRIA / MANUFACTURA)", "TERCIARIO (SERVICIOS / COMERCIO)", "CUATERNARIO (TECNOLOGÍA E INVESTIGACIÓN)", "AGROINDUSTRIAL"];
                                    $cur_val = ($dtOne && isset($dtOne[0]['sector_econ'])) ? $dtOne[0]['sector_econ'] : '';
                                    foreach ($sectores as $sec) {
                                        $sel = ($cur_val == $sec) ? 'selected' : '';
                                        echo "<option value='$sec' $sel>$sec</option>";
                                    }
                                    ?>
                                </select>
                            </td>
                        </tr>

                        <!-- Gremio Empresarial -->
                        <tr>
                            <td style="font-weight: bold;" class="bg-white">Gremio Empresarial:</td>
                            <td>
                                <input type="text" name="gremio" class="form-control form-control-sm" value="<?= ($dtOne && isset($dtOne[0]['gremio'])) ? htmlspecialchars($dtOne[0]['gremio']) : ''; ?>">
                            </td>
                        </tr>

                        <!-- Duración (Horas) -->
                        <tr>
                            <td style="font-weight: bold;" class="bg-white">Duración (Horas):</td>
                            <td>
                                <input type="number" name="duracion" class="form-control form-control-sm" min="1" value="<?= ($dtOne && isset($dtOne[0]['duracion'])) ? htmlspecialchars($dtOne[0]['duracion']) : ''; ?>">
                            </td>
                        </tr>

                        <!-- Tipo EDT -->
                        <tr>
                            <td style="font-weight: bold;" class="bg-white">Tipo EDT:</td>
                            <td>
                                <select name="tipo_edt" class="form-select form-select-sm">
                                    <?php 
                                    $tipos_edt = ["EVENTO EMPRESA", "EVENTO CENTRO", "EVENTO SECTORIAL", "CONGRESO / SEMINARIO", "JORNADA TÉCNICA"];
                                    $cur_val = ($dtOne && isset($dtOne[0]['tipo_edt'])) ? $dtOne[0]['tipo_edt'] : 'EVENTO EMPRESA';
                                    foreach ($tipos_edt as $te) {
                                        $sel = ($cur_val == $te) ? 'selected' : '';
                                        echo "<option value='$te' $sel>$te</option>";
                                    }
                                    ?>
                                </select>
                            </td>
                        </tr>

                        <!-- Objetivos -->
                        <tr>
                            <td style="font-weight: bold;" class="bg-white">Objetivos:</td>
                            <td>
                                <textarea name="objetivos" class="form-control form-control-sm" rows="3"><?= ($dtOne && isset($dtOne[0]['objetivos'])) ? htmlspecialchars($dtOne[0]['objetivos']) : ''; ?></textarea>
                            </td>
                        </tr>

                        <!-- Temas -->
                        <tr>
                            <td style="font-weight: bold;" class="bg-white">Temas:</td>
                            <td>
                                <textarea name="temas" class="form-control form-control-sm" rows="3"><?= ($dtOne && isset($dtOne[0]['temas'])) ? htmlspecialchars($dtOne[0]['temas']) : ''; ?></textarea>
                            </td>
                        </tr>

                        <!-- Población Objetivo -->
                        <tr>
                            <td style="font-weight: bold;" class="bg-white">Población Objetivo:</td>
                            <td>
                                <textarea name="poblacion" class="form-control form-control-sm" rows="2"><?= ($dtOne && isset($dtOne[0]['poblacion'])) ? htmlspecialchars($dtOne[0]['poblacion']) : ''; ?></textarea>
                            </td>
                        </tr>

                        <!-- Metodología -->
                        <tr>
                            <td style="font-weight: bold;" class="bg-white">Metodología:</td>
                            <td>
                                <textarea name="metodologia" class="form-control form-control-sm" rows="3"><?= ($dtOne && isset($dtOne[0]['metodologia'])) ? htmlspecialchars($dtOne[0]['metodologia']) : ''; ?></textarea>
                            </td>
                        </tr>

                        <!-- Resultados y/o Productos -->
                        <tr>
                            <td style="font-weight: bold;" class="bg-white">Resultados y/o Productos:</td>
                            <td>
                                <textarea name="resultados" class="form-control form-control-sm" rows="3"><?= ($dtOne && isset($dtOne[0]['resultados'])) ? htmlspecialchars($dtOne[0]['resultados']) : ''; ?></textarea>
                            </td>
                        </tr>

                        <!-- Perfil Profesional del Orientador -->
                        <tr>
                            <td style="font-weight: bold;" class="bg-white">Perfil Profesional del Orientador:</td>
                            <td>
                                <textarea name="perfil_orientador" class="form-control form-control-sm" rows="2"><?= ($dtOne && isset($dtOne[0]['perfil_orientador'])) ? htmlspecialchars($dtOne[0]['perfil_orientador']) : ''; ?></textarea>
                            </td>
                        </tr>

                        <!-- Observaciones -->
                        <tr>
                            <td style="font-weight: bold;" class="bg-white">Observaciones:</td>
                            <td>
                                <textarea name="observaciones" class="form-control form-control-sm" rows="2"><?= ($dtOne && isset($dtOne[0]['observaciones'])) ? htmlspecialchars($dtOne[0]['observaciones']) : ''; ?></textarea>
                            </td>
                        </tr>

                        <!-- Sector Programa -->
                        <tr>
                            <td style="font-weight: bold;" class="bg-white">Sector Programa:</td>
                            <td>
                                <select name="sector_programa" class="form-select form-select-sm">
                                    <option value="">SELECCIONAR</option>
                                    <?php 
                                    $sec_prog = ["AGROPECUARIO", "INDUSTRIAL", "SERVICIOS", "COMERCIO", "TECNOLOGÍA", "SALUD", "OTRO"];
                                    $cur_val = ($dtOne && isset($dtOne[0]['sector_programa'])) ? $dtOne[0]['sector_programa'] : '';
                                    foreach ($sec_prog as $sp) {
                                        $sel = ($cur_val == $sp) ? 'selected' : '';
                                        echo "<option value='$sp' $sel>$sp</option>";
                                    }
                                    ?>
                                </select>
                            </td>
                        </tr>

                        <!-- Tipo Evento Capacitación -->
                        <tr>
                            <td style="font-weight: bold;" class="bg-white">Tipo Evento Capacitación:</td>
                            <td>
                                <select name="tipo_evento" class="form-select form-select-sm">
                                    <?php 
                                    $tipos_ev = ["CAPACITACION", "DIVULGACIÓN", "ACTUALIZACIÓN TÉCNICA"];
                                    $cur_val = ($dtOne && isset($dtOne[0]['tipo_evento'])) ? $dtOne[0]['tipo_evento'] : 'CAPACITACION';
                                    foreach ($tipos_ev as $tev) {
                                        $sel = ($cur_val == $tev) ? 'selected' : '';
                                        echo "<option value='$tev' $sel>$tev</option>";
                                    }
                                    ?>
                                </select>
                            </td>
                        </tr>

                        <!-- Tipo Evento Capacitación (Fila duplicada/Subtipo de la imagen) -->
                        <tr>
                            <td style="font-weight: bold;" class="bg-white">Tipo Evento Capacitación:</td>
                            <td>
                                <select name="subtipo_evento" class="form-select form-select-sm">
                                    <?php 
                                    $subtipos_ev = ["TALLERES DE MEJORAMIENTO", "SEMINARIO TÉCNICO", "JORNADA TECNOLÓGICA", "CURSO CORTO", "CONFERENCIA / CHARLA"];
                                    $cur_val = ($dtOne && isset($dtOne[0]['subtipo_evento'])) ? $dtOne[0]['subtipo_evento'] : 'TALLERES DE MEJORAMIENTO';
                                    foreach ($subtipos_ev as $stev) {
                                        $sel = ($cur_val == $stev) ? 'selected' : '';
                                        echo "<option value='$stev' $sel>$stev</option>";
                                    }
                                    ?>
                                </select>
                            </td>
                        </tr>

                        <!-- Propuesta de Horario* -->
                        <tr>
                            <td style="font-weight: bold;" class="bg-white">Propuesta de Horario*:</td>
                            <td>
                                <input type="text" name="horario_propuesto" class="form-control form-control-sm" value="<?= ($dtOne && isset($dtOne[0]['horario_propuesto'])) ? htmlspecialchars($dtOne[0]['horario_propuesto']) : ''; ?>">
                            </td>
                        </tr>

                        <!-- Fecha Inicio* -->
                        <tr>
                            <td style="font-weight: bold;" class="bg-white">Fecha Inicio*:</td>
                            <td>
                                <input type="date" name="feclini" id="feclini" class="form-control form-control-sm" value="<?= ($dtOne && isset($dtOne[0]['feclini'])) ? htmlspecialchars($dtOne[0]['feclini']) : ''; ?>">
                            </td>
                        </tr>

                        <!-- Fecha Finalización* -->
                        <tr>
                            <td style="font-weight: bold;" class="bg-white">Fecha Finalización*:</td>
                            <td>
                                <input type="date" name="feclin" id="feclin" class="form-control form-control-sm" value="<?= ($dtOne && isset($dtOne[0]['feclin'])) ? htmlspecialchars($dtOne[0]['feclin']) : ''; ?>">
                            </td>
                        </tr>

                        <!-- Lugar de Realización del EDT* -->
                        <tr>
                            <td style="font-weight: bold;" class="bg-white">Lugar de Realización del EDT*:</td>
                            <td>
                                <input type="text" name="lugar_edt" class="form-control form-control-sm" value="<?= ($dtOne && isset($dtOne[0]['lugar_edt'])) ? htmlspecialchars($dtOne[0]['lugar_edt']) : ''; ?>">
                            </td>
                        </tr>

                        <!-- Cupo Ficha de Caracterización -->
                        <tr>
                            <td style="font-weight: bold;" class="bg-white">Cupo Ficha de Caracterización:</td>
                            <td>
                                <input type="number" name="cupo" id="cupo" class="form-control form-control-sm" min="1" value="<?= ($dtOne && isset($dtOne[0]['cupo'])) ? htmlspecialchars($dtOne[0]['cupo']) : ''; ?>">
                            </td>
                        </tr>

                        <!-- Responsable -->
                        <tr>
                            <td style="font-weight: bold;" class="bg-white">Responsable:</td>
                            <td>
                                <input type="text" name="responsable" class="form-control form-control-sm" value="<?= ($dtOne && isset($dtOne[0]['responsable'])) ? htmlspecialchars($dtOne[0]['responsable']) : ''; ?>">
                            </td>
                        </tr>

                        <!-- Nit de la Empresa -->
                        <tr>
                            <td style="font-weight: bold;" class="bg-white">Nit de la Empresa:</td>
                            <td>
                                <input type="text" name="nitemp" id="nitemp" class="form-control form-control-sm" value="<?= ($dtOne && isset($dtOne[0]['nitemp'])) ? htmlspecialchars($dtOne[0]['nitemp']) : ''; ?>">
                            </td>
                        </tr>

                        <!-- Jornada -->
                        <tr>
                            <td style="font-weight: bold;" class="bg-white">Jornada:</td>
                            <td>
                                <select name="jornada" id="jornada" class="form-select form-select-sm">
                                    <option value="">SELECCIONAR</option>
                                    <?php if ($datJornada) {
                                        foreach ($datJornada as $dj) { 
                                            $sel = ($dtOne && $dtOne[0]['jornada'] == $dj['idval']) ? "selected" : "";
                                            ?>
                                            <option value="<?= $dj['idval']; ?>" <?= $sel; ?>>
                                                <?= htmlspecialchars($dj['nomval']); ?>
                                            </option>
                                        <?php }
                                    } ?>
                                </select>
                            </td>
                        </tr>

                        <!-- Modalidad de Programa -->
                        <tr>
                            <td style="font-weight: bold;" class="bg-white">Modalidad de Programa:</td>
                            <td>
                                <select name="modalidad_prog" class="form-select form-select-sm">
                                    <option value="">SELECCIONAR</option>
                                    <?php 
                                    $modalidades = ["PRESENCIAL", "VIRTUAL", "A DISTANCIA", "COMBINADA / MIXTA"];
                                    $cur_val = ($dtOne && isset($dtOne[0]['modalidad_prog'])) ? $dtOne[0]['modalidad_prog'] : '';
                                    foreach ($modalidades as $mod) {
                                        $sel = ($cur_val == $mod) ? 'selected' : '';
                                        echo "<option value='$mod' $sel>$mod</option>";
                                    }
                                    ?>
                                </select>
                            </td>
                        </tr>

                        <!-- Programa Especial -->
                        <tr>
                            <td style="font-weight: bold;" class="bg-white">Programa Especial:</td>
                            <td>
                                <select name="codproesp" id="codproesp" class="form-select form-select-sm">
                                    <option value="">SELECCIONAR</option>
                                    <?php 
                                    $prg_esp_opts = ["ATENCION A INSTITUCIONES", "CAMPESENA", "POBLACION VULNERABLE", "CONVENIO MARCO", "OTRO"];
                                    $cur_val_esp = ($dtOne && isset($dtOne[0]['nom_proesp'])) ? $dtOne[0]['nom_proesp'] : (($dtOne && isset($dtOne[0]['codproesp'])) ? $dtOne[0]['codproesp'] : 'ATENCION A INSTITUCIONES');
                                    if ($datProEsp) {
                                        foreach ($datProEsp as $dpe) { 
                                            $sel = ($dtOne && $dtOne[0]['codproesp'] == $dpe['idval']) ? "selected" : "";
                                            ?>
                                            <option value="<?= $dpe['idval']; ?>" <?= $sel; ?>>
                                                <?= htmlspecialchars($dpe['nomval']); ?>
                                            </option>
                                        <?php }
                                    } else {
                                        foreach ($prg_esp_opts as $peo) {
                                            $sel = ($cur_val_esp == $peo) ? 'selected' : '';
                                            echo "<option value='$peo' $sel>$peo</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            </td>
                        </tr>

                        <!-- Tipo de Convenio -->
                        <tr>
                            <td style="font-weight: bold;" class="bg-white">Tipo de Convenio:</td>
                            <td>
                                <input type="text" name="tipo_convenio" class="form-control form-control-sm" value="<?= ($dtOne && isset($dtOne[0]['tipo_convenio'])) ? htmlspecialchars($dtOne[0]['tipo_convenio']) : ''; ?>">
                            </td>
                        </tr>

                    </tbody>
                </table>
            </div>

            <div class="text-end mt-3">
                <input class="btn btn-success px-4 py-2 fw-bold" type="submit" value="<?php echo $dtOne ? 'Actualizar Solicitud EDT' : 'Guardar Solicitud EDT'; ?>">
                <input type="hidden" name="ope" value="save">
                <input type="hidden" name="idnorad" value="<?php if ($dtOne) echo $dtOne[0]['idnorad']; ?>">
            </div>
        </form>
    </div>

    <br>

    <div class="table-responsive shadow-sm rounded-3 p-2 bg-white mb-4">
        <table id="example" class="table table-striped align-middle" style="width:100%">
        <thead>
            <tr>
                <th>No. Radicado</th>
                <th>Curso / Info General</th>
                <th>Programa</th>
                <th>Empresa / Prg. Especial</th>
                <th>Fechas / Cupo</th>
                <th>Jornada</th>
                <th>Instructor</th>
                <th>Estado Flujo</th>
                <th>Acciones / Avance</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($datAll) {
                foreach ($datAll as $dt) { 
                    $convht_raw = $dt['convht'];
                    $estado = $convht_raw;
                    $ruta_listado = NULL;
                    if ($convht_raw && strpos($convht_raw, 'Listado Cargado:') === 0) {
                        $parts = explode('Listado Cargado:', $convht_raw);
                        $estado = 'Listado Cargado';
                        $ruta_listado = $parts[1];
                    }
                    ?>
                    <tr>
                        <td><strong>#<?= $dt['idnorad']; ?></strong></td>
                        <td><?= $dt['codslem']; ?></td>
                        <td><?= $dt['nompro']; ?></td>
                        <td>
                            <?= $dt['nomemp']; ?><br>
                            <small class="text-muted"><?= $dt['nom_proesp']; ?></small>
                        </td>
                        <td>
                            Ini: <?= $dt['feclini']; ?><br>
                            Fin: <?= $dt['feclin']; ?><br>
                            <strong>Cupo: <?= $dt['cupo']; ?></strong>
                        </td>
                        <td><?= $dt['nom_jornada'] ? $dt['nom_jornada'] : 'Por definir'; ?></td>
                        <td><?= $dt['nomusu'] ? $dt['nomusu'] : 'N/A'; ?></td>
                        <td>
                            <?php if ($estado == 'Pendiente Coordinador') { ?>
                                <span class="badge bg-warning text-dark">Pendiente Coordinación</span>
                            <?php } elseif ($estado == 'Aprobado Coordinador') { ?>
                                <span class="badge bg-info text-dark">Aprobado Coordinación</span>
                            <?php } elseif ($estado == 'Aprobado Admin Educativa') { ?>
                                <span class="badge bg-primary">Aprobado Admin Educativa</span>
                            <?php } elseif ($estado == 'Listado Cargado') { ?>
                                <span class="badge bg-secondary">Listado Cargado</span>
                            <?php } elseif ($estado == 'Matriculado') { ?>
                                <span class="badge bg-success">Matriculado (Finalizado)</span>
                            <?php } elseif (strpos($estado, 'Rechazado') !== false) { ?>
                                <span class="badge bg-danger"><?= htmlspecialchars($estado); ?></span>
                            <?php } else { ?>
                                <span class="badge bg-light text-dark"><?= htmlspecialchars($estado); ?></span>
                            <?php } ?>
                            <?php if ($dt['idfic']) { ?>
                                <br><small><strong>Ficha: <?= $dt['idfic']; ?></strong></small>
                            <?php } ?>
                        </td>
                        <td>
                            <div class="d-flex flex-column gap-1">
                                <!-- Etapa 1: Editar / Eliminar -->
                                <div class="btn-group btn-group-sm mb-1">
                                    <a href="home.php?pg=<?= $pg; ?>&idnorad=<?= $dt['idnorad']; ?>&ope=edi" class="btn btn-outline-secondary" title="Editar">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <a href="home.php?pg=<?= $pg; ?>&idnorad=<?= $dt['idnorad']; ?>&ope=del" class="btn btn-outline-danger" onclick="return confirm('¿Desea eliminar esta solicitud?');" title="Eliminar">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </a>
                                </div>

                                <!-- Etapa 2: Aprobación Coordinación -->
                                <?php if ($estado == 'Pendiente Coordinador') { ?>
                                    <form action="home.php?pg=<?= $pg; ?>" method="POST">
                                        <input type="hidden" name="idnorad" value="<?= $dt['idnorad']; ?>">
                                        <input type="hidden" name="ope" value="aprobar_coordinador">
                                        <button type="submit" class="btn btn-sm btn-warning w-100">
                                            <i class="fa-solid fa-check"></i> Aprobar Coord.
                                        </button>
                                    </form>
                                <?php } ?>

                                <!-- Etapa 3: Aprobación Administración Educativa -->
                                <?php if ($estado == 'Aprobado Coordinador') { ?>
                                    <form action="home.php?pg=<?= $pg; ?>" method="POST">
                                        <input type="hidden" name="idnorad" value="<?= $dt['idnorad']; ?>">
                                        <input type="hidden" name="ope" value="aprobar_admin">
                                        <button type="submit" class="btn btn-sm btn-info text-white w-100">
                                            <i class="fa-solid fa-user-check"></i> Aprobar Admin
                                        </button>
                                    </form>
                                <?php } ?>

                                <!-- Etapa 4: Subir Listado de Personas -->
                                <?php if ($estado == 'Aprobado Admin Educativa') { ?>
                                    <form action="home.php?pg=<?= $pg; ?>" method="POST" enctype="multipart/form-data">
                                        <input type="hidden" name="idnorad" value="<?= $dt['idnorad']; ?>">
                                        <input type="hidden" name="ope" value="subir_listado">
                                        <input type="file" name="listado" class="form-control form-control-sm mb-1" required accept=".pdf,.csv,.xlsx,.xls">
                                        <button type="submit" class="btn btn-sm btn-primary w-100">
                                            <i class="fa-solid fa-upload"></i> Subir Listado
                                        </button>
                                    </form>
                                <?php } ?>

                                <!-- Etapa 5: Matricular / Asignar Ficha -->
                                <?php if ($estado == 'Listado Cargado') { ?>
                                    <?php if ($ruta_listado && file_exists($ruta_listado)) { ?>
                                        <a href="<?= $ruta_listado; ?>" target="_blank" class="btn btn-sm btn-outline-info mb-1">
                                            <i class="fa-solid fa-file-lines"></i> Ver Listado
                                        </a>
                                    <?php } ?>
                                    <form action="home.php?pg=<?= $pg; ?>" method="POST">
                                        <input type="hidden" name="idnorad" value="<?= $dt['idnorad']; ?>">
                                        <input type="hidden" name="ope" value="matricular">
                                        <input type="text" name="idfic" class="form-control form-control-sm mb-1" placeholder="Número de Ficha" required>
                                        <button type="submit" class="btn btn-sm btn-success w-100">
                                            <i class="fa-solid fa-graduation-cap"></i> Matricular
                                        </button>
                                    </form>
                                <?php } ?>

                                <!-- Etapa 6: Proceso Finalizado -->
                                <?php if ($estado == 'Matriculado') { ?>
                                    <span class="text-success small fw-bold text-center">
                                        <i class="fa-solid fa-circle-check"></i> Proceso Finalizado
                                    </span>
                                <?php } ?>
                            </div>
                        </td>
                    </tr>
                <?php }
            } ?>
        </tbody>
    </table>
</div>
</div>

<script>
function autoFillNit(selectElem) {
    let selectedOption = selectElem.options[selectElem.selectedIndex];
    let nit = selectedOption.getAttribute('data-nit');
    if (nit) {
        document.getElementById('nitemp').value = nit;
    }
}

function agregarEmpresa() {
    Swal.fire({
        title: 'Añadir Nueva Empresa',
        html:
            '<label style="display:block; text-align:left; margin-bottom:5px;"><b>Nombre de la Empresa:</b></label>' +
            '<input id="swal-nomemp" class="swal2-input" placeholder="Ej: Aceros y Construcciones S.A.S" style="margin-top:0; width:85%;">' +
            '<label style="display:block; text-align:left; margin-bottom:5px; margin-top:10px;"><b>NIT / Documento (Opcional):</b></label>' +
            '<input id="swal-numdocemp" class="swal2-input" placeholder="Ej: 900123456-1" style="margin-top:0; width:85%;">',
        showCancelButton: true,
        confirmButtonText: '<i class="fa-solid fa-floppy-disk"></i> Guardar en BD',
        cancelButtonText: 'Cancelar',
        focusConfirm: false,
        preConfirm: () => {
            const nomemp = document.getElementById('swal-nomemp').value.trim();
            const numdocemp = document.getElementById('swal-numdocemp').value.trim();
            if (!nomemp) {
                Swal.showValidationMessage('El nombre de la empresa es obligatorio');
                return false;
            }
            return { nomemp: nomemp, numdocemp: numdocemp };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            let formData = new FormData();
            formData.append('ope', 'quick_save_emp');
            formData.append('nomemp', result.value.nomemp);
            formData.append('numdocemp', result.value.numdocemp);

            fetch('controllers/edtcsedt.php', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    let select = document.getElementById('idemp');
                    let newOption = new Option(data.nomemp, data.idemp, true, true);
                    newOption.setAttribute('data-nit', data.numdocemp || '');
                    select.add(newOption);
                    if (data.numdocemp) {
                        document.getElementById('nitemp').value = data.numdocemp;
                    }
                    Swal.fire('¡Guardada!', 'La empresa "' + data.nomemp + '" ha sido insertada en la base de datos.', 'success');
                } else {
                    Swal.fire('Error', data.message || 'No se pudo guardar la empresa.', 'error');
                }
            })
            .catch(err => {
                Swal.fire('Error', 'Ocurrió un error al comunicarse con el servidor.', 'error');
            });
        }
    });
}

function agregarPrograma() {
    Swal.fire({
        title: 'Añadir Nuevo Programa de Formación',
        html:
            '<label style="display:block; text-align:left; margin-bottom:5px;"><b>Código del Programa:</b></label>' +
            '<input id="swal-codpro" type="number" class="swal2-input" placeholder="Ej: 228106" style="margin-top:0; width:85%;">' +
            '<label style="display:block; text-align:left; margin-bottom:5px; margin-top:10px;"><b>Nombre del Programa:</b></label>' +
            '<input id="swal-nompro" class="swal2-input" placeholder="Ej: Análisis y Desarrollo de Software" style="margin-top:0; width:85%;">',
        showCancelButton: true,
        confirmButtonText: '<i class="fa-solid fa-floppy-disk"></i> Guardar en BD',
        cancelButtonText: 'Cancelar',
        focusConfirm: false,
        preConfirm: () => {
            const codpro = document.getElementById('swal-codpro').value.trim();
            const nompro = document.getElementById('swal-nompro').value.trim();
            if (!codpro || !nompro) {
                Swal.showValidationMessage('El código y el nombre del programa son obligatorios');
                return false;
            }
            return { codpro: codpro, nompro: nompro };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            let formData = new FormData();
            formData.append('ope', 'quick_save_pro');
            formData.append('codpro', result.value.codpro);
            formData.append('nompro', result.value.nompro);

            fetch('controllers/edtcsedt.php', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    let select = document.getElementById('codpro');
                    let newOption = new Option(data.nompro, data.codpro, true, true);
                    select.add(newOption);
                    Swal.fire('¡Guardado!', 'El programa "' + data.nompro + '" ha sido insertado en la base de datos.', 'success');
                } else {
                    Swal.fire('Error', data.message || 'No se pudo guardar el programa.', 'error');
                }
            })
            .catch(err => {
                Swal.fire('Error', 'Ocurrió un error al comunicarse con el servidor.', 'error');
            });
        }
    });
}

function agregarProgramaEspecial() {
    Swal.fire({
        title: 'Añadir Nuevo Programa Especial',
        html:
            '<label style="display:block; text-align:left; margin-bottom:5px;"><b>Nombre del Programa Especial:</b></label>' +
            '<input id="swal-nomval" class="swal2-input" placeholder="Ej: CampeSENA Avanzado" style="margin-top:0; width:85%;">',
        showCancelButton: true,
        confirmButtonText: '<i class="fa-solid fa-floppy-disk"></i> Guardar en BD',
        cancelButtonText: 'Cancelar',
        focusConfirm: false,
        preConfirm: () => {
            const nomval = document.getElementById('swal-nomval').value.trim();
            if (!nomval) {
                Swal.showValidationMessage('El nombre es obligatorio');
                return false;
            }
            return { nomval: nomval };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            let formData = new FormData();
            formData.append('ope', 'quick_save_proesp');
            formData.append('nomval', result.value.nomval);

            fetch('controllers/edtcsedt.php', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    let select = document.getElementById('codproesp');
                    let newOption = new Option(data.nomval, data.idval, true, true);
                    select.add(newOption);
                    Swal.fire('¡Guardado!', 'El programa especial "' + data.nomval + '" ha sido insertado en la base de datos.', 'success');
                } else {
                    Swal.fire('Error', data.message || 'No se pudo guardar el programa especial.', 'error');
                }
            })
            .catch(err => {
                Swal.fire('Error', 'Ocurrió un error al comunicarse con el servidor.', 'error');
            });
        }
    });
}
</script>
