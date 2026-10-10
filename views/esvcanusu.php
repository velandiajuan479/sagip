<?php 
require_once 'controllers/esccanusu.php'; 

// Datos para el Dashboard Operativo
$datosDashboard = [
  'entradas'      => (int)$cantEntradas,
  'salidas'       => (int)$cantSalidas,
  'enSede'        => (int)$enSede,
  'ausencias'     => (int)$cantAusencias
];

// Generar desglose horario (06:00 a 20:00)
$horasLabels = [];
$entradasPorHora = [];
$salidasPorHora = [];
for ($h = 6; $h <= 20; $h++) {
  $horaStr = str_pad($h, 2, "0", STR_PAD_LEFT) . ":00";
  $horasLabels[] = $horaStr;
  $entradasPorHora[$h] = 0;
  $salidasPorHora[$h] = 0;
}
if (!empty($flujoHoras)) {
  foreach ($flujoHoras as $fh) {
    $h = (int)$fh['hora'];
    if (isset($entradasPorHora[$h])) {
      $entradasPorHora[$h] = (int)$fh['entradas'];
      $salidasPorHora[$h] = (int)$fh['salidas'];
    }
  }
}
$jsonDashboard = json_encode($datosDashboard);
$jsonHoras = json_encode([
  'labels'   => $horasLabels,
  'entradas' => array_values($entradasPorHora),
  'salidas'  => array_values($salidasPorHora)
]);
?>

<div class="conte">
  <?php echo titulo2("<i class='".$icono."'></i> Control de Entradas y Salidas", 0); ?>

  <div class="d-flex flex-wrap align-items-center justify-content-between mb-3 gap-2">
    <small class="text-muted" style="font-size: 0.85rem;">Supervisión en tiempo real de asistencias, dispositivos y acceso vehicular</small>
    <span class="badge bg-white text-dark border px-3 py-2 rounded-pill shadow-xs d-inline-flex align-items-center gap-2">
      <i class="fa-solid fa-clock text-success"></i> Actualización cada 40s
    </span>
  </div>

  <!-- 1. TARJETAS KPI SUPERIORES (ESTILO EXACTO DE vvidgen.php: Total en sistema, Activos, Inactivos) -->
  <div class="row my-4 g-3 g-xl-4">
    <!-- 1. Ingresos Hoy -->
    <div class="col-12 col-sm-6 col-xl-3">
      <div class="kpi-card h-100 d-flex flex-column justify-content-between">
        <div class="d-flex align-items-center justify-content-between">
          <div>
            <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #006b29;">Ingresos Hoy</span>
            <div class="d-flex align-items-baseline gap-2 mt-2">
              <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #006b29;"><?= number_format($cantEntradas, 0, ',', '.'); ?></span>
              <span style="font-size: 13px; color: rgba(0, 107, 41, 0.8);">personas</span>
            </div>
          </div>
          <div class="kpi-icon-box" style="background-color: #7afd88; color: #006b24;">
            <i class="fa-solid fa-user-check fs-4"></i>
          </div>
        </div>
        <div class="kpi-footer-strip">
          <span class="fw-semibold d-inline-flex align-items-center gap-2" style="font-size: 11px; color: #006b29;">
            <span class="pulse-dot"></span> Registros Activos
          </span>
          <span style="font-size: 11px; color: #3e4a3d;">Entradas hoy</span>
        </div>
      </div>
    </div>

    <!-- 2. Salidas Hoy -->
    <div class="col-12 col-sm-6 col-xl-3">
      <div class="kpi-card h-100 d-flex flex-column justify-content-between">
        <div class="d-flex align-items-center justify-content-between">
          <div>
            <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #ba1a1a;">Salidas Hoy</span>
            <div class="d-flex align-items-baseline gap-2 mt-2">
              <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #0b1c30;"><?= number_format($cantSalidas, 0, ',', '.'); ?></span>
              <span style="font-size: 13px; color: #3e4a3d;">personas</span>
            </div>
          </div>
          <div class="kpi-icon-box" style="background-color: #ffdad6; color: #ba1a1a;">
            <i class="fa-solid fa-arrow-right-from-bracket fs-4"></i>
          </div>
        </div>
        <div class="kpi-footer-strip">
          <span class="d-inline-flex align-items-center gap-1" style="font-size: 11px; color: #ba1a1a;">
            <i class="fa-solid fa-clock-rotate-left" style="font-size: 12px;"></i> Confirmadas
          </span>
          <span class="fw-semibold" style="font-size: 11px; color: #3c627f;">Cierre de turno</span>
        </div>
      </div>
    </div>

    <!-- 3. En Sede Actualmente -->
    <div class="col-12 col-sm-6 col-xl-3">
      <div class="kpi-card h-100 d-flex flex-column justify-content-between">
        <div class="d-flex align-items-center justify-content-between">
          <div>
            <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #3e4a3d;">En Sede Ahora</span>
            <div class="d-flex align-items-baseline gap-2 mt-2">
              <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #0b1c30;"><?= number_format($enSede, 0, ',', '.'); ?></span>
              <span style="font-size: 13px; color: #3e4a3d;">en centro</span>
            </div>
          </div>
          <div class="kpi-icon-box" style="background-color: #b5dcfe; color: #3c627f;">
            <i class="fa-solid fa-building-user fs-4"></i>
          </div>
        </div>
        <div class="kpi-footer-strip">
          <span class="fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 11px; color: #3c627f;">
            <i class="fa-solid fa-users" style="font-size: 12px;"></i> Permanencia actual
          </span>
          <span style="font-size: 11px; color: #3e4a3d;">En instalaciones</span>
        </div>
      </div>
    </div>

    <!-- 4. Ausencias Hoy -->
    <div class="col-12 col-sm-6 col-xl-3">
      <div class="kpi-card h-100 d-flex flex-column justify-content-between">
        <div class="d-flex align-items-center justify-content-between">
          <div>
            <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #b45309;">Ausencias Hoy</span>
            <div class="d-flex align-items-baseline gap-2 mt-2">
              <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #0b1c30;"><?= number_format($cantAusencias, 0, ',', '.'); ?></span>
              <span style="font-size: 13px; color: #3e4a3d;">novedades</span>
            </div>
          </div>
          <div class="kpi-icon-box" style="background-color: #fef3c7; color: #b45309;">
            <i class="fa-solid fa-user-xmark fs-4"></i>
          </div>
        </div>
        <div class="kpi-footer-strip">
          <span class="d-inline-flex align-items-center gap-1" style="font-size: 11px; color: #b45309;">
            <i class="fa-solid fa-triangle-exclamation" style="font-size: 12px;"></i> Sin registro
          </span>
          <span class="fw-semibold" style="font-size: 11px; color: #b45309;">Reportadas</span>
        </div>
      </div>
    </div>
  </div>

  <!-- 2. PANEL DE CONTROL OPERATIVO - ASISTENCIA DIARIA (GRÁFICOS, ESTILO INSTITUCIONAL vvidgen.php) -->
  <div class="row mb-4">
    <div class="col-12">
      <div class="card shadow-sm border-0 rounded-4 overflow-hidden bg-white">
        <!-- Cabecera del Panel -->
        <div class="card-header bg-white py-3 px-4 d-flex flex-wrap align-items-center justify-content-between gap-2 border-bottom">
          <div class="d-flex align-items-center gap-2">
            <div class="kpi-icon-box" style="width: 38px; height: 38px; border-radius: 10px; background-color: #e8f5e9; color: #006b29;">
              <i class="fa-solid fa-chart-column fs-6"></i>
            </div>
            <div>
              <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.95rem; letter-spacing: 0.3px;">
                PANEL DE CONTROL OPERATIVO - ASISTENCIA DIARIA
              </h6>
              <small class="text-muted" style="font-size: 0.75rem;">Monitoreo analítico de balance de ingresos y flujo por horas</small>
            </div>
          </div>
          <div class="d-flex align-items-center gap-2">
            <span class="badge rounded-pill px-3 py-1 fw-semibold shadow-xs" style="background: #e8f5e9; color: #006b29; border: 1px solid #c8e6c9;">
              <i class="fa-solid fa-calendar-day me-1"></i> Movimientos de Hoy
            </span>
            <span class="badge bg-light text-secondary border rounded-pill px-2 py-1">En Tiempo Real</span>
          </div>
        </div>

        <div class="card-body p-3 p-md-4 bg-white">
          <div class="row g-3 align-items-stretch">
            <!-- Gráfico 1: Balance Operativo del Día -->
            <div class="col-12 col-lg-5">
              <div class="p-3 rounded-3 border border-light-subtle bg-light h-100 d-flex flex-column justify-content-between" style="min-height: 290px;">
                <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                  <div>
                    <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.9rem;">
                      <i class="fa-solid fa-chart-column me-1" style="color: #006b29;"></i> Balance Operativo del Día
                    </h6>
                    <small class="text-muted" style="font-size: 0.75rem;">Distribución de actividades de hoy</small>
                  </div>
                  <span class="badge bg-white text-secondary border">Hoy</span>
                </div>
                <div class="flex-grow-1 position-relative" style="min-height: 220px; width: 100%;">
                  <canvas id="graficoDashboardBalance"></canvas>
                </div>
              </div>
            </div>

            <!-- Gráfico 2: Flujo de Asistencia por Horas -->
            <div class="col-12 col-lg-7">
              <div class="p-3 rounded-3 border border-light-subtle bg-light h-100 d-flex flex-column justify-content-between" style="min-height: 290px;">
                <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                  <div>
                    <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.9rem;">
                      <i class="fa-solid fa-chart-line me-1" style="color: #006b29;"></i> Flujo de Asistencia por Horas
                    </h6>
                    <small class="text-muted" style="font-size: 0.75rem;">Comportamiento de entradas y salidas en la jornada (06:00 - 20:00)</small>
                  </div>
                  <span class="badge bg-white text-secondary border">Línea de Tiempo</span>
                </div>
                <div class="flex-grow-1 position-relative" style="min-height: 220px; width: 100%;">
                  <canvas id="graficoDashboardHoras"></canvas>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- 3. SECCIÓN DISPOSITIVOS: SENA (IZQUIERDA) Y PERSONAL (DERECHA) -->
  <div class="row g-4 mb-4">
    <!-- Tabla / Recuadro: Dispositivos SENA -->
    <div class="col-12 col-lg-6">
      <div class="card shadow-sm border-0 rounded-4 overflow-hidden h-100 bg-white">
        <div class="card-header bg-white py-3 px-4 d-flex align-items-center justify-content-between border-bottom">
          <div class="d-flex align-items-center gap-2">
            <div class="kpi-icon-box" style="width: 38px; height: 38px; border-radius: 10px; background-color: #e8f5e9; color: #006b29;">
              <i class="fa-solid fa-laptop-code fs-6"></i>
            </div>
            <div>
              <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.95rem; letter-spacing: 0.3px;">Dispositivos SENA</h6>
              <small class="text-muted" style="font-size: 0.75rem;">Control de inventario de equipos institucionales</small>
            </div>
          </div>
          <span class="badge rounded-pill px-3 py-1 fw-semibold shadow-xs" style="background: #e8f5e9; color: #006b29; border: 1px solid #c8e6c9;">Institucional</span>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="table-light border-bottom">
                <tr>
                  <th class="ps-4 py-3 text-muted" style="width: 46%; font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Elemento</th>
                  <th class="text-center py-3 text-muted" style="width: 27%; font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Entrada</th>
                  <th class="text-center pe-4 py-3 text-muted" style="width: 27%; font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Salida</th>
                </tr>
              </thead>
              <tbody>
                <!-- Portátiles Sena -->
                <tr>
                  <td class="ps-4 py-3">
                    <div class="d-flex align-items-center">
                      <div class="me-3 text-center" style="width: 38px; height: 38px; line-height: 38px; border-radius: 8px; background: rgba(0, 107, 41, 0.1); color: #006b29;">
                        <i class="fa-solid fa-laptop fa-lg"></i>
                      </div>
                      <div>
                        <span class="fw-bold text-dark d-block">Portátiles</span>
                        <small class="text-muted">Equipos SENA</small>
                      </div>
                    </div>
                  </td>
                  <td class="text-center py-3">
                    <span class="badge px-3 py-2 rounded-pill shadow-xs" style="background: #e8f5e9; color: #1b5e20; border: 1px solid #c8e6c9; font-size: 0.95rem;">
                      <i class="fa-solid fa-arrow-right me-1 text-success"></i>
                      <strong><?php cantidad(72, "I"); ?></strong>
                    </span>
                  </td>
                  <td class="text-center pe-4 py-3">
                    <span class="badge px-3 py-2 rounded-pill shadow-xs" style="background: #ffebee; color: #b71c1c; border: 1px solid #ffcdd2; font-size: 0.95rem;">
                      <i class="fa-solid fa-arrow-left me-1 text-danger"></i>
                      <strong><?php cantidad(72, "S"); ?></strong>
                    </span>
                  </td>
                </tr>

                <!-- Tablets Sena -->
                <tr>
                  <td class="ps-4 py-3">
                    <div class="d-flex align-items-center">
                      <div class="me-3 text-center" style="width: 38px; height: 38px; line-height: 38px; border-radius: 8px; background: rgba(0, 107, 41, 0.1); color: #006b29;">
                        <i class="fa-solid fa-tablet fa-lg"></i>
                      </div>
                      <div>
                        <span class="fw-bold text-dark d-block">Tablets</span>
                        <small class="text-muted">Dispositivos SENA</small>
                      </div>
                    </div>
                  </td>
                  <td class="text-center py-3">
                    <span class="badge px-3 py-2 rounded-pill shadow-xs" style="background: #e8f5e9; color: #1b5e20; border: 1px solid #c8e6c9; font-size: 0.95rem;">
                      <i class="fa-solid fa-arrow-right me-1 text-success"></i>
                      <strong><?php cantidad(74, "I"); ?></strong>
                    </span>
                  </td>
                  <td class="text-center pe-4 py-3">
                    <span class="badge px-3 py-2 rounded-pill shadow-xs" style="background: #ffebee; color: #b71c1c; border: 1px solid #ffcdd2; font-size: 0.95rem;">
                      <i class="fa-solid fa-arrow-left me-1 text-danger"></i>
                      <strong><?php cantidad(74, "S"); ?></strong>
                    </span>
                  </td>
                </tr>

                <!-- Herramientas Sena -->
                <tr>
                  <td class="ps-4 py-3">
                    <div class="d-flex align-items-center">
                      <div class="me-3 text-center" style="width: 38px; height: 38px; line-height: 38px; border-radius: 8px; background: rgba(0, 107, 41, 0.1); color: #006b29;">
                        <i class="fa-solid fa-screwdriver-wrench fa-lg"></i>
                      </div>
                      <div>
                        <span class="fw-bold text-dark d-block">Herramientas</span>
                        <small class="text-muted">Talleres SENA</small>
                      </div>
                    </div>
                  </td>
                  <td class="text-center py-3">
                    <span class="badge px-3 py-2 rounded-pill shadow-xs" style="background: #e8f5e9; color: #1b5e20; border: 1px solid #c8e6c9; font-size: 0.95rem;">
                      <i class="fa-solid fa-arrow-right me-1 text-success"></i>
                      <strong><?php cantidad(80, "I"); ?></strong>
                    </span>
                  </td>
                  <td class="text-center pe-4 py-3">
                    <span class="badge px-3 py-2 rounded-pill shadow-xs" style="background: #ffebee; color: #b71c1c; border: 1px solid #ffcdd2; font-size: 0.95rem;">
                      <i class="fa-solid fa-arrow-left me-1 text-danger"></i>
                      <strong><?php cantidad(80, "S"); ?></strong>
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Tabla / Recuadro: Dispositivos Personal -->
    <div class="col-12 col-lg-6">
      <div class="card shadow-sm border-0 rounded-4 overflow-hidden h-100 bg-white">
        <div class="card-header bg-white py-3 px-4 d-flex align-items-center justify-content-between border-bottom">
          <div class="d-flex align-items-center gap-2">
            <div class="kpi-icon-box" style="width: 38px; height: 38px; border-radius: 10px; background-color: #eff4ff; color: #3c627f;">
              <i class="fa-solid fa-user-shield fs-6"></i>
            </div>
            <div>
              <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.95rem; letter-spacing: 0.3px;">Dispositivos Personal</h6>
              <small class="text-muted" style="font-size: 0.75rem;">Equipos propios de aprendices y funcionarios</small>
            </div>
          </div>
          <span class="badge rounded-pill px-3 py-1 fw-semibold shadow-xs" style="background: #eff4ff; color: #3c627f; border: 1px solid #dce9ff;">Aprendices / Funcionarios</span>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="table-light border-bottom">
                <tr>
                  <th class="ps-4 py-3 text-muted" style="width: 46%; font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Elemento</th>
                  <th class="text-center py-3 text-muted" style="width: 27%; font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Entrada</th>
                  <th class="text-center pe-4 py-3 text-muted" style="width: 27%; font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Salida</th>
                </tr>
              </thead>
              <tbody>
                <!-- Portátiles Personal -->
                <tr>
                  <td class="ps-4 py-3">
                    <div class="d-flex align-items-center">
                      <div class="me-3 text-center" style="width: 38px; height: 38px; line-height: 38px; border-radius: 8px; background: rgba(0, 107, 41, 0.1); color: #006b29;">
                        <i class="fa-solid fa-laptop fa-lg"></i>
                      </div>
                      <div>
                        <span class="fw-bold text-dark d-block">Portátiles</span>
                        <small class="text-muted">Propiedad Personal</small>
                      </div>
                    </div>
                  </td>
                  <td class="text-center py-3">
                    <span class="badge px-3 py-2 rounded-pill shadow-xs" style="background: #e8f5e9; color: #1b5e20; border: 1px solid #c8e6c9; font-size: 0.95rem;">
                      <i class="fa-solid fa-arrow-right me-1 text-success"></i>
                      <strong><?php cantidad(71, "I"); ?></strong>
                    </span>
                  </td>
                  <td class="text-center pe-4 py-3">
                    <span class="badge px-3 py-2 rounded-pill shadow-xs" style="background: #ffebee; color: #b71c1c; border: 1px solid #ffcdd2; font-size: 0.95rem;">
                      <i class="fa-solid fa-arrow-left me-1 text-danger"></i>
                      <strong><?php cantidad(71, "S"); ?></strong>
                    </span>
                  </td>
                </tr>

                <!-- Tablets Personal -->
                <tr>
                  <td class="ps-4 py-3">
                    <div class="d-flex align-items-center">
                      <div class="me-3 text-center" style="width: 38px; height: 38px; line-height: 38px; border-radius: 8px; background: rgba(0, 107, 41, 0.1); color: #006b29;">
                        <i class="fa-solid fa-tablet fa-lg"></i>
                      </div>
                      <div>
                        <span class="fw-bold text-dark d-block">Tablets</span>
                        <small class="text-muted">Propiedad Personal</small>
                      </div>
                    </div>
                  </td>
                  <td class="text-center py-3">
                    <span class="badge px-3 py-2 rounded-pill shadow-xs" style="background: #e8f5e9; color: #1b5e20; border: 1px solid #c8e6c9; font-size: 0.95rem;">
                      <i class="fa-solid fa-arrow-right me-1 text-success"></i>
                      <strong><?php cantidad(73, "I"); ?></strong>
                    </span>
                  </td>
                  <td class="text-center pe-4 py-3">
                    <span class="badge px-3 py-2 rounded-pill shadow-xs" style="background: #ffebee; color: #b71c1c; border: 1px solid #ffcdd2; font-size: 0.95rem;">
                      <i class="fa-solid fa-arrow-left me-1 text-danger"></i>
                      <strong><?php cantidad(73, "S"); ?></strong>
                    </span>
                  </td>
                </tr>

                <!-- Herramientas Personal -->
                <tr>
                  <td class="ps-4 py-3">
                    <div class="d-flex align-items-center">
                      <div class="me-3 text-center" style="width: 38px; height: 38px; line-height: 38px; border-radius: 8px; background: rgba(0, 107, 41, 0.1); color: #006b29;">
                        <i class="fa-solid fa-screwdriver-wrench fa-lg"></i>
                      </div>
                      <div>
                        <span class="fw-bold text-dark d-block">Herramientas</span>
                        <small class="text-muted">Equipos Propios</small>
                      </div>
                    </div>
                  </td>
                  <td class="text-center py-3">
                    <span class="badge px-3 py-2 rounded-pill shadow-xs" style="background: #e8f5e9; color: #1b5e20; border: 1px solid #c8e6c9; font-size: 0.95rem;">
                      <i class="fa-solid fa-arrow-right me-1 text-success"></i>
                      <strong><?php cantidad(79, "I"); ?></strong>
                    </span>
                  </td>
                  <td class="text-center pe-4 py-3">
                    <span class="badge px-3 py-2 rounded-pill shadow-xs" style="background: #ffebee; color: #b71c1c; border: 1px solid #ffcdd2; font-size: 0.95rem;">
                      <i class="fa-solid fa-arrow-left me-1 text-danger"></i>
                      <strong><?php cantidad(79, "S"); ?></strong>
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- 4. SECCIÓN INFERIOR: CONTROL VEHICULAR -->
  <div class="row justify-content-center mb-4">
    <div class="col-12">
      <div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-4 bg-white">
        <div class="card-header bg-white py-3 px-4 d-flex align-items-center justify-content-between border-bottom">
          <div class="d-flex align-items-center gap-2">
            <div class="kpi-icon-box" style="width: 38px; height: 38px; border-radius: 10px; background-color: #e8f5e9; color: #006b29;">
              <i class="fa-solid fa-car fs-6"></i>
            </div>
            <div>
              <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.95rem; letter-spacing: 0.3px;">Control de Acceso Vehicular</h6>
              <small class="text-muted" style="font-size: 0.75rem;">Registro de parqueadero y movilidad en portería</small>
            </div>
          </div>
          <span class="badge rounded-pill px-3 py-1 fw-semibold shadow-xs" style="background: #f8fafc; color: #475569; border: 1px solid #e2e8f0;">Parqueadero / Portería</span>
        </div>
        <div class="card-body p-4 bg-white">
          <div class="row g-3">
            <!-- Bicicletas -->
            <div class="col-12 col-md-4">
              <div class="p-3 border border-light-subtle rounded-3 text-center h-100 d-flex flex-column justify-content-between shadow-xs bg-white">
                <div>
                  <div class="d-inline-flex align-items-center justify-content-center mb-2 rounded-circle" style="width: 52px; height: 52px; background: rgba(0, 107, 41, 0.1); color: #006b29;">
                    <i class="fa-solid fa-bicycle fa-xl"></i>
                  </div>
                  <h5 class="fw-bold text-dark mb-1">Bicicletas</h5>
                  <small class="text-muted d-block mb-3">Ciclas registradas</small>
                </div>
                <div class="d-flex justify-content-center align-items-center gap-2">
                  <span class="badge px-3 py-2 rounded-pill shadow-xs" style="background: #e8f5e9; color: #1b5e20; border: 1px solid #c8e6c9; font-size: 0.95rem;" title="Entradas">
                    <i class="fa-solid fa-arrow-right me-1 text-success"></i>
                    <strong><?php cantidad(77, "I"); ?></strong>
                  </span>
                  <span class="text-muted fw-bold">/</span>
                  <span class="badge px-3 py-2 rounded-pill shadow-xs" style="background: #ffebee; color: #b71c1c; border: 1px solid #ffcdd2; font-size: 0.95rem;" title="Salidas">
                    <i class="fa-solid fa-arrow-left me-1 text-danger"></i>
                    <strong><?php cantidad(77, "S"); ?></strong>
                  </span>
                </div>
              </div>
            </div>

            <!-- Motocicletas -->
            <div class="col-12 col-md-4">
              <div class="p-3 border border-light-subtle rounded-3 text-center h-100 d-flex flex-column justify-content-between shadow-xs bg-white">
                <div>
                  <div class="d-inline-flex align-items-center justify-content-center mb-2 rounded-circle" style="width: 52px; height: 52px; background: rgba(0, 107, 41, 0.1); color: #006b29;">
                    <i class="fa-solid fa-motorcycle fa-xl"></i>
                  </div>
                  <h5 class="fw-bold text-dark mb-1">Motocicletas</h5>
                  <small class="text-muted d-block mb-3">Motos registradas</small>
                </div>
                <div class="d-flex justify-content-center align-items-center gap-2">
                  <span class="badge px-3 py-2 rounded-pill shadow-xs" style="background: #e8f5e9; color: #1b5e20; border: 1px solid #c8e6c9; font-size: 0.95rem;" title="Entradas">
                    <i class="fa-solid fa-arrow-right me-1 text-success"></i>
                    <strong><?php cantidad(76, "I"); ?></strong>
                  </span>
                  <span class="text-muted fw-bold">/</span>
                  <span class="badge px-3 py-2 rounded-pill shadow-xs" style="background: #ffebee; color: #b71c1c; border: 1px solid #ffcdd2; font-size: 0.95rem;" title="Salidas">
                    <i class="fa-solid fa-arrow-left me-1 text-danger"></i>
                    <strong><?php cantidad(76, "S"); ?></strong>
                  </span>
                </div>
              </div>
            </div>

            <!-- Carros -->
            <div class="col-12 col-md-4">
              <div class="p-3 border border-light-subtle rounded-3 text-center h-100 d-flex flex-column justify-content-between shadow-xs bg-white">
                <div>
                  <div class="d-inline-flex align-items-center justify-content-center mb-2 rounded-circle" style="width: 52px; height: 52px; background: rgba(0, 107, 41, 0.1); color: #006b29;">
                    <i class="fa-solid fa-car fa-xl"></i>
                  </div>
                  <h5 class="fw-bold text-dark mb-1">Automóviles</h5>
                  <small class="text-muted d-block mb-3">Carros registrados</small>
                </div>
                <div class="d-flex justify-content-center align-items-center gap-2">
                  <span class="badge px-3 py-2 rounded-pill shadow-xs" style="background: #e8f5e9; color: #1b5e20; border: 1px solid #c8e6c9; font-size: 0.95rem;" title="Entradas">
                    <i class="fa-solid fa-arrow-right me-1 text-success"></i>
                    <strong><?php cantidad(75, "I"); ?></strong>
                  </span>
                  <span class="text-muted fw-bold">/</span>
                  <span class="badge px-3 py-2 rounded-pill shadow-xs" style="background: #ffebee; color: #b71c1c; border: 1px solid #ffcdd2; font-size: 0.95rem;" title="Salidas">
                    <i class="fa-solid fa-arrow-left me-1 text-danger"></i>
                    <strong><?php cantidad(75, "S"); ?></strong>
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

</div>

<!-- SCRIPT PARA EL DASHBOARD (CHART.JS) -->
<script>
document.addEventListener("DOMContentLoaded", function() {
  const datosDash = <?= $jsonDashboard; ?>;
  const datosHor  = <?= $jsonHoras; ?>;

  // 1. Gráfico de Balance Operativo del Día
  const canvasBal = document.getElementById('graficoDashboardBalance');
  if (canvasBal) {
    const ctxBal = canvasBal.getContext('2d');
    new Chart(ctxBal, {
      type: 'bar',
      data: {
        labels: ['Ingresos', 'Salidas', 'En Sede', 'Ausencias'],
        datasets: [{
          label: 'Personas',
          data: [datosDash.entradas, datosDash.salidas, datosDash.enSede, datosDash.ausencias],
          backgroundColor: [
            'rgba(0, 175, 0, 0.85)',    // Verde SENA
            'rgba(239, 68, 68, 0.85)',   // Rojo
            'rgba(2, 132, 199, 0.85)',   // Azul
            'rgba(245, 158, 11, 0.85)'   // Ámbar
          ],
          borderColor: ['#008f00', '#dc2626', '#0284c7', '#d97706'],
          borderWidth: 1.5,
          borderRadius: 8,
          borderSkipped: false,
          maxBarThickness: 46
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: {
            backgroundColor: 'rgba(15, 23, 42, 0.92)',
            padding: 10,
            cornerRadius: 8,
            callbacks: {
              label: function(context) {
                return ` ${context.label}: ${context.parsed.y} personas`;
              }
            }
          }
        },
        scales: {
          x: {
            grid: { display: false },
            ticks: { font: { weight: '600', size: 11 }, color: '#334155' }
          },
          y: {
            beginAtZero: true,
            grid: { color: 'rgba(0, 0, 0, 0.05)' },
            ticks: { precision: 0, color: '#64748b' }
          }
        }
      }
    });
  }

  // 2. Gráfico de Flujo por Horas
  const canvasHor = document.getElementById('graficoDashboardHoras');
  if (canvasHor) {
    const ctxHor = canvasHor.getContext('2d');
    new Chart(ctxHor, {
      type: 'line',
      data: {
        labels: datosHor.labels,
        datasets: [
          {
            label: 'Ingresos',
            data: datosHor.entradas,
            borderColor: '#00af00',
            backgroundColor: 'rgba(0, 175, 0, 0.12)',
            tension: 0.35,
            fill: true,
            pointBackgroundColor: '#00af00',
            pointBorderColor: '#ffffff',
            pointBorderWidth: 2,
            pointRadius: 4,
            pointHoverRadius: 6,
            borderWidth: 2.5
          },
          {
            label: 'Salidas',
            data: datosHor.salidas,
            borderColor: '#ef4444',
            backgroundColor: 'rgba(239, 68, 68, 0.08)',
            tension: 0.35,
            fill: true,
            pointBackgroundColor: '#ef4444',
            pointBorderColor: '#ffffff',
            pointBorderWidth: 2,
            pointRadius: 4,
            pointHoverRadius: 6,
            borderWidth: 2.5
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'top',
            labels: { boxWidth: 12, font: { size: 11, weight: '500' } }
          },
          tooltip: {
            backgroundColor: 'rgba(15, 23, 42, 0.92)',
            padding: 10,
            cornerRadius: 8
          }
        },
        scales: {
          x: {
            grid: { display: false },
            ticks: { font: { size: 10 }, color: '#334155' }
          },
          y: {
            beginAtZero: true,
            grid: { color: 'rgba(0, 0, 0, 0.05)' },
            ticks: { precision: 0, color: '#64748b' }
          }
        }
      }
    });
  }
});

setInterval(recargar, 40000);
function recargar() {
  window.location.href = 'home.php?pg=1405';
}
</script>