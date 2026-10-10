<?php
require_once('controllers/horcage.php');

if (!function_exists('sena_utf8_clean')) {
    function sena_utf8_clean($str) {
        if ($str === null || $str === '') return '';
        if (!is_string($str)) return $str;
        $map = [
            'MaÃ±ana' => 'Mañana', 'maÃ±ana' => 'mañana', 'Ã¡' => 'á', 'Ã©' => 'é', 'Ã­' => 'í', 'Ã³' => 'ó', 'Ãº' => 'ú',
            'Ã±' => 'ñ', 'Ã‘' => 'Ñ', 'Ã ' => 'Á', 'Ã‰' => 'É', 'Ã ' => 'Í', 'Ã“' => 'Ó', 'Ãš' => 'Ú', 'Ã¼' => 'ü', 'Ãœ' => 'Ü'
        ];
        return strtr($str, $map);
    }
}

// Renderizar título estándar del sistema como en las demás vistas
$icoTit = !empty($icono) ? $icono : 'fa-solid fa-calendar-days';
echo titulo2("<i class='" . $icoTit . "'></i> Agenda", 0);
require_once("views/vscopehya.php");
?>

<!-- Estilos dedicados para el módulo de Agenda SENA -->
<style>
  /* Botones de navegación rápida */
  .sena-quick-nav {
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 15px 0 16px 0;
  }

  .sena-btn-primary {
    background-color: #008F39;
    color: #ffffff !important;
    border: none;
    padding: 6px 14px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    text-decoration: none !important;
    transition: all 0.15s ease-in-out;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
    cursor: pointer;
  }

  .sena-btn-primary:hover {
    background-color: #007A2F;
    color: #ffffff;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  }

  /* Ficha Info Card */
  .sena-ficha-card {
    background-color: #f8fafc;
    border: 1px solid #e2e8f0;
    border-left: 4px solid #008F39;
    border-radius: 8px;
    padding: 10px 16px;
    margin-bottom: 14px;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    font-size: 12px;
  }

  .sena-ficha-item {
    display: flex;
    flex-direction: column;
  }

  .sena-ficha-label {
    font-size: 10px;
    text-transform: uppercase;
    color: #64748b;
    font-weight: 700;
    letter-spacing: 0.5px;
  }

  .sena-ficha-val {
    font-size: 13px;
    font-weight: 600;
    color: #0f172a;
  }

  /* Filtros */
  .sena-filter-panel {
    background-color: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 12px 14px;
    margin-bottom: 14px;
  }

  .sena-filter-row {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 10px;
  }

  .sena-search-box {
    flex: 1;
    min-width: 250px;
    display: flex;
    align-items: center;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    overflow: hidden;
    height: 36px;
    background: #fff;
  }

  .sena-search-addon {
    background-color: #008F39;
    color: white;
    height: 100%;
    padding: 0 12px;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .sena-search-box input {
    width: 100%;
    border: none;
    outline: none;
    font-size: 12px;
    padding: 0 10px;
    color: #334155;
  }

  .sena-filter-select {
    height: 36px;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    font-size: 12px;
    padding: 0 10px;
    color: #334155;
    background-color: #ffffff;
    outline: none;
    transition: border-color 0.15s;
    min-width: 170px;
  }

  .sena-filter-select:focus, .sena-search-box input:focus {
    border-color: #008F39;
  }

  .sena-filter-actions {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: 10px;
    padding-top: 8px;
    border-top: 1px solid #f1f5f9;
  }

  .sena-date-inputs {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 11px;
    color: #64748b;
  }

  .sena-date-inputs input[type="date"] {
    height: 30px;
    border: 1px solid #cbd5e1;
    border-radius: 4px;
    padding: 0 8px;
    font-size: 11px;
    outline: none;
  }

  .sena-btn-reset {
    background: #ffffff;
    border: 1px solid #cbd5e1;
    color: #475569;
    font-size: 11px;
    font-weight: 600;
    padding: 5px 12px;
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    cursor: pointer;
    transition: all 0.15s;
  }

  .sena-btn-reset:hover {
    background-color: #f8fafc;
    color: #008F39;
    border-color: #008F39;
  }

  /* Barra de Reportes */
  .sena-reports-bar {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 12px;
    font-size: 12px;
  }

  .sena-reports-left {
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .sena-reports-title {
    font-weight: 700;
    color: #334155;
    letter-spacing: 0.5px;
  }

  .sena-reports-counter {
    color: #64748b;
    font-size: 12px;
  }

  .sena-reports-counter strong {
    color: #0f172a;
  }

  /* Tabla Principal de Agenda */
  .sena-table-responsive {
    width: 100%;
    overflow-x: auto;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
  }

  .sena-agenda-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 11px;
    text-align: left;
    min-width: 1250px;
  }

  .sena-agenda-table thead tr {
    background-color: #008F39;
    color: #ffffff;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-size: 10.5px;
  }

  .sena-agenda-table th {
    padding: 10px 8px;
    border-right: 1px solid rgba(255, 255, 255, 0.2);
    vertical-align: middle;
  }

  .sena-agenda-table th:last-child {
    border-right: none;
  }

  .sena-agenda-table td {
    padding: 10px 8px;
    border-bottom: 1px solid #f1f5f9;
    border-right: 1px solid #e2e8f0;
    vertical-align: middle;
    color: #334155;
  }

  .sena-agenda-table td:last-child {
    border-right: none;
  }

  .sena-agenda-table tbody tr:hover {
    background-color: #f8fafc;
  }

  .sena-agenda-table .col-center {
    text-align: center;
  }

  .sena-dia-check {
    color: #008F39;
    font-weight: bold;
    font-size: 13px;
    text-align: center;
    display: inline-block;
  }

  .sena-dia-dash {
    color: #cbd5e1;
    font-size: 13px;
    text-align: center;
    display: inline-block;
  }

  /* Badges de fase */
  .sena-badge-fase {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 6px;
    font-size: 10.5px;
    font-weight: 600;
    text-align: center;
    white-space: nowrap;
  }

  .sena-badge-planeacion { background-color: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
  .sena-badge-ejecucion { background-color: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
  .sena-badge-evaluacion { background-color: #fffbeb; color: #b45309; border: 1px solid #fde68a; }
  .sena-badge-analisis { background-color: #fdf4ff; color: #a21caf; border: 1px solid #f5d0fe; }
  .sena-badge-default { background-color: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }

  /* Estilos para el pill/badge de la guía (igual a la imagen de referencia) */
  .sena-guia-pill-container {
    margin-top: 5px;
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 6px;
  }

  .sena-guia-pill {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background-color: #ecfdf5;
    border: 1.5px solid #a7f3d0;
    border-radius: 8px;
    padding: 3px 9px;
    font-size: 11px;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
    transition: all 0.15s ease;
  }

  .sena-guia-pill:hover {
    background-color: #d1fae5;
    border-color: #6ee7b7;
  }

  .sena-guia-link {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    color: #065f46 !important;
    font-weight: 600;
    text-decoration: none !important;
  }

  .sena-guia-link i {
    color: #059669;
    font-size: 12.5px;
  }

  .sena-guia-actions {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    margin-left: 3px;
  }

  .sena-guia-btn-icon {
    background: none;
    border: none;
    padding: 2px 3px;
    cursor: pointer;
    font-size: 11.5px;
    color: #64748b;
    border-radius: 4px;
    transition: color 0.15s, background-color 0.15s;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
  }

  .sena-guia-btn-icon.btn-edit:hover {
    color: #008F39;
    background-color: rgba(0, 143, 57, 0.12);
  }

  .sena-guia-btn-icon.btn-del:hover {
    color: #dc2626;
    background-color: rgba(220, 38, 38, 0.12);
  }

  /* Botones de acción en tabla */
  .sena-act-actions {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
  }

  .sena-btn-action {
    background: none;
    border: none;
    color: #008F39;
    font-size: 15px;
    cursor: pointer;
    padding: 5px 6px;
    border-radius: 4px;
    transition: all 0.15s;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
  }

  .sena-btn-action.btn-edit {
    color: #008F39;
  }

  .sena-btn-action.btn-edit:hover {
    color: #006828;
    background-color: #e8f5e9;
  }

  .sena-btn-action.btn-eval {
    color: #0284c7;
  }

  .sena-btn-action.btn-eval:hover {
    color: #0369a1;
    background-color: #e0f2fe;
  }

  /* Paginación */
  .sena-pagination-footer {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    margin-top: 14px;
    font-size: 12px;
    color: #64748b;
  }

  .sena-pagination-controls {
    display: flex;
    align-items: center;
    gap: 4px;
  }

  .sena-page-btn {
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid #cbd5e1;
    background: #ffffff;
    color: #334155;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.15s;
    text-decoration: none;
  }

  .sena-page-btn:hover:not(.disabled):not(.active) {
    background-color: #f1f5f9;
    border-color: #008F39;
    color: #008F39;
  }

  .sena-page-btn.active {
    background-color: #008F39;
    border-color: #008F39;
    color: #ffffff;
  }

  .sena-page-btn.disabled {
    opacity: 0.5;
    cursor: not-allowed;
  }

  /* MODAL DE REPORTES */
  .sena-modal-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(15, 23, 42, 0.6);
    backdrop-filter: blur(4px);
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 16px;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.25s ease-in-out;
  }

  .sena-modal-backdrop.show {
    opacity: 1;
    pointer-events: auto;
  }

  .sena-modal-card {
    background: #ffffff;
    width: 100%;
    max-width: 640px;
    border-radius: 12px;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2);
    overflow: hidden;
    transform: scale(0.95);
    transition: transform 0.25s ease-in-out;
    border: 1px solid #e2e8f0;
  }

  .sena-modal-backdrop.show .sena-modal-card {
    transform: scale(1);
  }

  .sena-modal-stripe {
    height: 6px;
    background-color: #008F39;
    width: 100%;
  }

  .sena-modal-header {
    padding: 16px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid #f1f5f9;
  }

  .sena-modal-header-left {
    display: flex;
    align-items: center;
    gap: 12px;
  }

  .sena-modal-icon {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    background-color: #e8f5e9;
    color: #008F39;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
  }

  .sena-modal-header h3 {
    margin: 0;
    font-size: 16px;
    font-weight: 700;
    color: #0f172a;
  }

  .sena-modal-header p {
    margin: 2px 0 0 0;
    font-size: 11px;
    color: #64748b;
  }

  .sena-modal-close {
    background: none;
    border: none;
    font-size: 18px;
    color: #94a3b8;
    cursor: pointer;
    padding: 4px;
    border-radius: 6px;
    transition: color 0.15s;
  }

  .sena-modal-close:hover {
    color: #0f172a;
    background: #f1f5f9;
  }

  .sena-modal-body {
    padding: 18px 20px;
    max-height: 75vh;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 16px;
  }

  .sena-modal-section {
    background-color: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 12px 14px;
  }

  .sena-modal-label {
    display: block;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    color: #475569;
    margin-bottom: 8px;
    letter-spacing: 0.5px;
  }

  .sena-radio-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
    margin-bottom: 10px;
  }

  .sena-radio-card {
    display: flex;
    align-items: center;
    gap: 8px;
    background: #ffffff;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    padding: 8px 10px;
    cursor: pointer;
    font-size: 11px;
    font-weight: 500;
    color: #334155;
    transition: border-color 0.15s;
  }

  .sena-radio-card:hover {
    border-color: #008F39;
  }

  .sena-radio-card input[type="radio"] {
    accent-color: #008F39;
  }

  .sena-date-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
  }

  .sena-date-grid label {
    font-size: 10.5px;
    font-weight: 600;
    color: #64748b;
    margin-bottom: 3px;
    display: block;
  }

  .sena-date-grid input {
    width: 100%;
    height: 34px;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    padding: 0 8px;
    font-size: 11px;
    outline: none;
    box-sizing: border-box;
  }

  .sena-date-grid input:focus {
    border-color: #008F39;
  }

  .sena-select-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
  }

  .sena-select-grid .field-group label {
    display: block;
    font-size: 11px;
    font-weight: 700;
    color: #334155;
    margin-bottom: 4px;
  }

  .sena-select-grid .field-group select {
    width: 100%;
    height: 34px;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    padding: 0 8px;
    font-size: 11px;
    outline: none;
    background-color: #ffffff;
    box-sizing: border-box;
  }

  .sena-select-grid .field-group select:focus {
    border-color: #008F39;
  }

  /* Formato de exportación */
  .sena-format-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
  }

  .sena-format-card {
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    padding: 10px 12px;
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
    transition: all 0.15s;
    background-color: #ffffff;
  }

  .sena-format-card.selected {
    border-color: #008F39;
    background-color: #f0fdf4;
  }

  .sena-format-icon {
    width: 34px;
    height: 34px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    flex-shrink: 0;
  }

  .icon-pdf { background-color: #fee2e2; color: #dc2626; }
  .icon-excel { background-color: #dcfce7; color: #16a34a; }

  .sena-format-text .format-title {
    font-size: 12px;
    font-weight: 700;
    color: #0f172a;
  }

  .sena-format-text .format-desc {
    font-size: 10px;
    color: #64748b;
  }

  .sena-modal-footer {
    padding: 14px 20px;
    background-color: #f8fafc;
    border-top: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 10px;
  }

  .sena-btn-cancel {
    background: #ffffff;
    border: 1px solid #cbd5e1;
    color: #475569;
    font-size: 12px;
    font-weight: 600;
    padding: 7px 16px;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.15s;
  }

  .sena-btn-cancel:hover {
    background-color: #f1f5f9;
    color: #0f172a;
  }

  .sena-btn-reset-modal {
    background: #ffffff;
    border: 1px solid #cbd5e1;
    color: #475569;
    font-size: 12px;
    font-weight: 600;
    padding: 7px 14px;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.15s;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    margin-right: auto;
  }

  .sena-btn-reset-modal:hover {
    background-color: #f1f5f9;
    color: #008F39;
    border-color: #008F39;
  }

  .sena-btn-download {
    background-color: #008F39;
    color: #ffffff;
    border: none;
    font-size: 12px;
    font-weight: 700;
    padding: 7px 18px;
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    cursor: pointer;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
    transition: all 0.15s;
  }

  .sena-btn-download:hover {
    background-color: #007A2F;
  }

  @media (max-width: 768px) {
    .sena-select-grid, .sena-radio-grid, .sena-date-grid, .sena-format-grid { grid-template-columns: 1fr; }
  }
</style>

  <!-- BEGIN: Botones de Enlace Rápido -->
  <div class="sena-quick-nav">
    <a href="home.php?pg=1506<?= !empty($idfic) ? '&idfic=' . urlencode($idfic) : ''; ?>" class="sena-btn-primary" title="Volver a Horarios">
      <i class="fa-solid fa-arrow-left"></i>
      <span>Volver a Horarios</span>
    </a>
    <a href="home.php?pg=1548<?= !empty($idfic) ? '&idfic=' . urlencode($idfic) : ''; ?>" class="sena-btn-primary" title="Ver Resultados de Trimestre">
      <i class="fa-solid fa-square-poll-vertical"></i>
      <span>Resultados Trimestre</span>
    </a>
    <a href="home.php?pg=1513<?= !empty($idfic) ? '&idfic=' . urlencode($idfic) : ''; ?>" class="sena-btn-primary" title="Ver Resultados de Instructor">
      <i class="fa-solid fa-user-tie"></i>
      <span>Resultados Instructor</span>
    </a>
    <a href="home.php?pg=1517<?= !empty($idfic) ? '&idfic=' . urlencode($idfic) : ''; ?>" class="sena-btn-primary" title="Ver Horario por Persona">
      <i class="fa-solid fa-user-clock"></i>
      <span>Horario por Persona</span>
    </a>
    <a href="home.php?pg=1522<?= !empty($idfic) ? '&idfic=' . urlencode($idfic) : ''; ?>" class="sena-btn-primary" title="Ver Reporte Plan de Sesión">
      <i class="fa-solid fa-clipboard-list"></i>
      <span>Plan de Sesión</span>
    </a>
  </div>
  <!-- END: Botones de Enlace Rápido -->

  <!-- BEGIN: Dashboard KPI Agenda Superior -->
  <div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-xl-2">
      <a href="home.php?pg=1518<?= !empty($idfic) ? '&idfic=' . urlencode($idfic) : ''; ?>" class="text-decoration-none" title="Ver información institucional de la ficha">
        <div class="card border-0 shadow-sm rounded-3 h-100 text-center py-2" style="background: linear-gradient(135deg, #00af00 0%, #008700 100%); color: #fff; cursor: pointer;">
          <div class="card-body p-2">
            <i class="fa-solid fa-id-badge fa-2x mb-1" style="opacity: 0.9;"></i>
            <div class="text-uppercase fw-semibold" style="font-size: 1.1rem; letter-spacing: 0.5px;">Ficha</div>
            <h4 class="mb-0 text-white fw-bold" style="font-size: 1.8rem;"><?= (!empty($idfic) && $idfic !== 'all') ? htmlspecialchars($idfic) : 'Todas' ?></h4>
            <small style="font-size: 0.95rem; opacity: 0.9;"><?= !empty($fichaDetalle['nompro']) ? htmlspecialchars(substr(sena_utf8_clean($fichaDetalle['nompro']), 0, 16)) . '...' : 'Seleccione ficha' ?></small>
          </div>
        </div>
      </a>
    </div>

    <div class="col-6 col-md-4 col-xl-2">
      <a href="home.php?pg=<?= $pg; ?><?= !empty($idfic) ? '&idfic=' . urlencode($idfic) : ''; ?>" class="text-decoration-none" title="Ver listado de agendas">
        <div class="card border-0 shadow-sm rounded-3 h-100 text-center py-2" style="background: #ffffff; border-left: 4px solid #198754 !important; cursor: pointer;">
          <div class="card-body p-2">
            <i class="fa-solid fa-calendar-check fa-2x mb-1 text-success"></i>
            <div class="text-uppercase fw-semibold text-muted" style="font-size: 1.1rem;">Agendas</div>
            <h3 class="mb-0 fw-bold text-dark" style="font-size: 2.2rem;"><?= $kpiAgenda['total_agendas'] ?? 0 ?></h3>
            <small class="text-muted" style="font-size: 1rem;">Programadas</small>
          </div>
        </div>
      </a>
    </div>

    <div class="col-6 col-md-4 col-xl-3">
      <a href="home.php?pg=1520<?= !empty($idfic) ? '&idfic=' . urlencode($idfic) : ''; ?>" class="text-decoration-none" title="Ver programación horaria">
        <div class="card border-0 shadow-sm rounded-3 h-100 text-center py-2" style="background: #ffffff; border-left: 4px solid #0d6efd !important; cursor: pointer;">
          <div class="card-body p-2">
            <i class="fa-solid fa-clock fa-2x mb-1 text-primary"></i>
            <div class="text-uppercase fw-semibold text-muted" style="font-size: 1.1rem;">Horas Formación</div>
            <h3 class="mb-0 fw-bold text-dark" style="font-size: 2.2rem;"><?= $kpiAgenda['total_horas'] ?? 0 ?> h</h3>
            <small class="text-muted" style="font-size: 1rem;">Total asignadas</small>
          </div>
        </div>
      </a>
    </div>

    <div class="col-6 col-md-6 col-xl-3">
      <a href="home.php?pg=1548<?= !empty($idfic) ? '&idfic=' . urlencode($idfic) : ''; ?>" class="text-decoration-none" title="Ver resultados y competencias por trimestre">
        <div class="card border-0 shadow-sm rounded-3 h-100 text-center py-2" style="background: #ffffff; border-left: 4px solid #ffc107 !important; cursor: pointer;">
          <div class="card-body p-2">
            <i class="fa-solid fa-book-open fa-2x mb-1 text-warning"></i>
            <div class="text-uppercase fw-semibold text-muted" style="font-size: 1.1rem;">Competencias</div>
            <h3 class="mb-0 fw-bold text-dark" style="font-size: 2.2rem;"><?= $kpiAgenda['total_competencias'] ?? 0 ?></h3>
            <small class="text-muted" style="font-size: 1rem;">En la programación</small>
          </div>
        </div>
      </a>
    </div>

    <div class="col-12 col-md-6 col-xl-2">
      <a href="home.php?pg=1513<?= !empty($idfic) ? '&idfic=' . urlencode($idfic) : ''; ?>" class="text-decoration-none" title="Ver carga y resultados por instructor">
        <div class="card border-0 shadow-sm rounded-3 h-100 text-center py-2" style="background: #ffffff; border-left: 4px solid #6f42c1 !important; cursor: pointer;">
          <div class="card-body p-2">
            <i class="fa-solid fa-chalkboard-user fa-2x mb-1" style="color: #6f42c1;"></i>
            <div class="text-uppercase fw-semibold text-muted" style="font-size: 1.1rem;">Instructores</div>
            <h3 class="mb-0 fw-bold text-dark" style="font-size: 2.2rem;"><?= $kpiAgenda['total_instructores'] ?? 0 ?></h3>
            <small class="text-muted" style="font-size: 1rem;">Asignados</small>
          </div>
        </div>
      </a>
    </div>
  </div>
  <!-- END: Dashboard KPI Agenda Superior -->

  <!-- BEGIN: Información de la Ficha Seleccionada -->
  <?php if ($fichaDetalle): ?>
  <div class="sena-ficha-card">
    <div class="sena-ficha-item">
      <span class="sena-ficha-label">Ficha de Caracterización</span>
      <span class="sena-ficha-val"><?= htmlspecialchars($fichaDetalle['idfic']); ?></span>
    </div>
    <div class="sena-ficha-item">
      <span class="sena-ficha-label">Programa de Formación</span>
      <span class="sena-ficha-val"><?= htmlspecialchars(sena_utf8_clean($fichaDetalle['nompro'])); ?></span>
    </div>
    <div class="sena-ficha-item">
      <span class="sena-ficha-label">Jornada</span>
      <span class="sena-ficha-val"><?= htmlspecialchars(sena_utf8_clean($fichaDetalle['nomjornada'])); ?></span>
    </div>
  </div>
  <?php endif; ?>
  <!-- END: Información de la Ficha -->

  <!-- BEGIN: Panel de Filtros -->
  <section class="sena-filter-panel">
    <form id="formFiltrosAgenda" method="GET" action="home.php">
      <input type="hidden" name="pg" value="<?= htmlspecialchars($_REQUEST['pg'] ?? '1509'); ?>">

      <!-- Primera Fila: Buscador y Selectores Principales -->
      <div class="sena-filter-row">
        <!-- 1. Búsqueda libre -->
        <div class="sena-search-box">
          <span class="sena-search-addon">
            <i class="fa-solid fa-magnifying-glass"></i>
          </span>
          <input type="text" id="inputBuscar" name="buscar" value="<?= htmlspecialchars($buscar); ?>" placeholder="Buscar por actividad, instructor o competencia..." onkeyup="filtrarTablaLocal()">
        </div>

        <!-- 2. Selector de Ficha -->
        <select id="selectFicha" name="idfic" class="sena-filter-select" onchange="aplicarFiltrosServidor()">
          <option value="all" <?= ($idfic === 'all') ? 'selected' : ''; ?>>Todas las fichas</option>
          <?php if (!empty($dtFichas)): ?>
            <?php foreach ($dtFichas as $fic): 
              $isSelected = ($idfic == $fic['idfic']) ? 'selected' : '';
              $nomMostrar = !empty($fic['nompro']) ? $fic['idfic'] . ' - ' . sena_utf8_clean($fic['nompro']) : $fic['idfic'] . ' - ' . sena_utf8_clean($fic['nomfic']);
            ?>
              <option value="<?= htmlspecialchars($fic['idfic']); ?>" <?= $isSelected; ?>>
                <?= htmlspecialchars($nomMostrar); ?>
              </option>
            <?php endforeach; ?>
          <?php endif; ?>
        </select>

        <!-- 3. Selector de Fase -->
        <select id="selectFase" name="fas" class="sena-filter-select" onchange="filtrarTablaLocal()">
          <option value="all" <?= ($fas === 'all' || empty($fas)) ? 'selected' : ''; ?>>Todas las fases</option>
          <option value="Análisis" <?= ($fas === 'Análisis' || $fas === '1. Análisis' || $fas === '1') ? 'selected' : ''; ?>>Análisis</option>
          <option value="Planeación" <?= ($fas === 'Planeación' || $fas === '2. Planeación' || $fas === '2') ? 'selected' : ''; ?>>Planeación</option>
          <option value="Ejecución" <?= ($fas === 'Ejecución' || $fas === '3. Ejecución' || $fas === '3') ? 'selected' : ''; ?>>Ejecución</option>
          <option value="Evaluación" <?= ($fas === 'Evaluación' || $fas === '4. Evaluación' || $fas === '4') ? 'selected' : ''; ?>>Evaluación</option>
        </select>

        <!-- 4. Selector de Jornada -->
        <select id="selectJornada" name="jornada" class="sena-filter-select" onchange="filtrarTablaLocal()">
          <option value="all" <?= ($jornada === 'all' || empty($jornada)) ? 'selected' : ''; ?>>Todas las jornadas</option>
          <?php if (!empty($dtJornadas)): ?>
            <?php foreach ($dtJornadas as $j): ?>
              <option value="<?= htmlspecialchars($j['idval']); ?>" <?= ($jornada == $j['idval']) ? 'selected' : ''; ?>>
                <?= htmlspecialchars(sena_utf8_clean($j['nomval'])); ?>
              </option>
            <?php endforeach; ?>
          <?php endif; ?>
        </select>

        <!-- 5. Selector de Instructor -->
        <select id="selectInstructor" name="idins" class="sena-filter-select" onchange="filtrarTablaLocal()">
          <option value="all" <?= ($idins === 'all' || empty($idins)) ? 'selected' : ''; ?>>Todos los instructores</option>
          <?php if (!empty($dtInstructores)): ?>
            <?php foreach ($dtInstructores as $ins): ?>
              <option value="<?= htmlspecialchars($ins['idusu']); ?>" <?= ($idins == $ins['idusu']) ? 'selected' : ''; ?>>
                <?= htmlspecialchars($ins['nomusu']); ?>
              </option>
            <?php endforeach; ?>
          <?php endif; ?>
        </select>
      </div>

      <!-- Segunda Fila: Rango de fechas y Restablecer -->
      <div class="sena-filter-actions">
        <div class="sena-date-inputs">
          <span>Rango fechas:</span>
          <input type="date" id="inputFecIni" name="fecini" value="<?= htmlspecialchars($fecini); ?>" title="Fecha Inicio" onchange="filtrarTablaLocal()">
          <span>hasta</span>
          <input type="date" id="inputFecFin" name="fecfin" value="<?= htmlspecialchars($fecfin); ?>" title="Fecha Fin" onchange="filtrarTablaLocal()">
        </div>

        <button type="button" class="sena-btn-reset" onclick="restablecerTodosFiltros()" title="Restablecer todos los filtros">
          <i class="fa-solid fa-arrows-rotate"></i>
          <span>Restablecer Filtros</span>
        </button>
      </div>
    </form>
  </section>
  <!-- END: Panel de Filtros -->

  <!-- BEGIN: Barra de Reportes y Contador -->
  <div class="sena-reports-bar">
    <div class="sena-reports-left">
      <span class="sena-reports-title">REPORTES:</span>
      <button type="button" class="sena-btn-primary" onclick="abrirModalReporte()" title="Abrir asistente de reportes personalizados">
        <i class="fa-solid fa-file-export"></i>
        <span>Generar Reporte</span>
      </button>
    </div>

    <div class="sena-reports-counter">
      <strong id="lblTotalActividades"><?= $totalRegistros; ?></strong> actividades programadas
    </div>
  </div>
  <!-- END: Barra de Reportes -->

  <!-- BEGIN: Tabla Principal -->
  <div class="sena-table-responsive">
    <table class="sena-agenda-table" id="tablaAgenda">
      <thead>
        <tr>
          <th style="width: 90px;" class="col-center">FASE</th>
          <th style="width: 250px;">ACTIVIDAD PROYECTO</th>
          <th style="width: 200px;">COMPETENCIA</th>
          <th style="width: 220px;">RESULTADO APRENDIZAJE</th>
          <th style="width: 90px;" class="col-center">HORAS DE LA COMPETENCIA</th>
          <th style="width: 160px;">INSTRUCTOR</th>
          <th style="width: 95px;" class="col-center">HORARIO</th>
          <th style="width: 46px;" class="col-center">L</th>
          <th style="width: 46px;" class="col-center">M</th>
          <th style="width: 46px;" class="col-center">X</th>
          <th style="width: 46px;" class="col-center">J</th>
          <th style="width: 46px;" class="col-center">V</th>
          <th style="width: 46px;" class="col-center">S</th>
          <th style="width: 90px;" class="col-center">FECHA INICIO</th>
          <th style="width: 90px;" class="col-center">FECHA FINAL</th>
          <th style="width: 90px;" class="col-center">ACCIONES</th>
        </tr>
      </thead>
      <tbody id="tbodyAgenda">
        <?php if (!empty($dtAge)): ?>
          <?php foreach ($dtAge as $dta): 
            $fasRaw = trim((string)($dta['fas'] ?? ''));
            $fasLower = mb_strtolower($fasRaw, 'UTF-8');
            $badgeClass = 'sena-badge-default';
            $fasName = 'Análisis';

            if ($fasRaw === '2' || strpos($fasLower, 'plan') !== false) {
                $badgeClass = 'sena-badge-planeacion';
                $fasName = 'Planeación';
            } elseif ($fasRaw === '3' || strpos($fasLower, 'ejec') !== false) {
                $badgeClass = 'sena-badge-ejecucion';
                $fasName = 'Ejecución';
            } elseif ($fasRaw === '4' || strpos($fasLower, 'eval') !== false) {
                $badgeClass = 'sena-badge-evaluacion';
                $fasName = 'Evaluación';
            } elseif ($fasRaw === '1' || strpos($fasLower, 'an') !== false) {
                $badgeClass = 'sena-badge-analisis';
                $fasName = 'Análisis';
            } else {
                $fasName = !empty($fasRaw) ? preg_replace('/^\d+\.\s*/', '', $fasRaw) : 'Análisis';
            }
            $guiaAge = $dta['guia'] ?? '';
            $idactRef = $dta['idact_ref'] ?? '';
          ?>
            <tr class="fila-agenda" 
                data-fase="<?= htmlspecialchars(strtolower($fasName)); ?>" 
                data-instructor="<?= htmlspecialchars(strtolower($dta['nomusu'] ?? '')); ?>"
                data-fecini="<?= htmlspecialchars($dta['fecini'] ?? ''); ?>"
                data-fecfin="<?= htmlspecialchars($dta['fecfin'] ?? ''); ?>"
                data-jornada="<?= htmlspecialchars(strtolower($dta['nomjornada'] ?? '')); ?>"
                data-texto="<?= htmlspecialchars(strtolower(($dta['idfic'] ?? '') . ' ' . ($dta['nompro'] ?? '') . ' ' . ($dta['actpro'] ?? '') . ' ' . ($dta['descom'] ?? '') . ' ' . ($dta['nomres'] ?? '') . ' ' . ($dta['nomusu'] ?? ''))); ?>">
              <td class="col-center" style="font-weight: 700; color: #0f172a; display:none;">
                <span class="sena-badge-fase sena-badge-default"><?= htmlspecialchars($dta['idfic'] ?? '-'); ?></span>
                <?= htmlspecialchars(($dta['nompro'] ?? '')); ?>
              </td>
              <td class="col-center">
                <span class="sena-badge-fase <?= $badgeClass; ?>"><?= htmlspecialchars($fasName); ?></span>
              </td>
              <td style="font-weight: 600; color: #1e293b; line-height: 1.35;">
                <div><?= htmlspecialchars($dta['actpro'] ?? '-'); ?></div>
                <?php if (!empty($guiaAge) && file_exists($guiaAge)): ?>
                  <div class="sena-guia-pill-container">
                    <div class="sena-guia-pill">
                      <a href="<?= htmlspecialchars($guiaAge); ?>" target="_blank" class="sena-guia-link" title="Descargar / Ver Guía">
                        <i class="fa-solid fa-file-pdf"></i>
                        <span><?= htmlspecialchars(basename($guiaAge)); ?></span>
                      </a>
                      <div class="sena-guia-actions">
                        <a href="home.php?pg=1508<?= !empty($idactRef) ? '&ope=edi&idact=' . urlencode($idactRef) : ''; ?>" 
                           class="sena-guia-btn-icon btn-edit" 
                           title="Modificar en actividades">
                          <i class="fa-solid fa-pencil"></i>
                        </a>
                        <?php if (!empty($idactRef)): ?>
                          <a href="controllers/horcactag.php?ope=del_guia&idact=<?= urlencode($idactRef); ?>&return_pg=1509" 
                             class="sena-guia-btn-icon btn-del" 
                             onclick="return confirm('¿Está seguro de que desea eliminar esta guía?');" 
                             title="Eliminar guía">
                            <i class="fa-regular fa-trash-can"></i>
                          </a>
                        <?php endif; ?>
                      </div>
                    </div>
                  </div>
                <?php endif; ?>
              </td>
              <td style="color: #475569; line-height: 1.35;">
                <?= htmlspecialchars($dta['descom'] ?? '-'); ?>
              </td>
              <td style="color: #475569; line-height: 1.35;">
                <?= htmlspecialchars($dta['nomres'] ?? '-'); ?>
              </td>
              <td class="col-center" style="font-weight: 700; color: #0f172a; white-space: nowrap;">
                <?= htmlspecialchars(($dta['horcom'] ?? 0) . ' hrs'); ?>
              </td>
              <td style="font-weight: 500; color: #1e293b;">
                <?php
                $nomInsAge = trim((string)($dta['nomusu'] ?? ''));
                if ($nomInsAge === '' || strcasecmp($nomInsAge, 'Sin asignar') === 0) {
                    $insHor = isset($mapInstr[(string)($dta['idfic'] ?? '')]) ? array_keys($mapInstr[(string)($dta['idfic'] ?? '')]) : [];
                    if (!empty($insHor)) {
                        echo htmlspecialchars(implode(', ', $insHor));
                    } else {
                        echo 'Sin asignar';
                    }
                } else {
                    echo htmlspecialchars($nomInsAge);
                }
                ?>
              </td>
              <td class="col-center" style="color: #475569; white-space: nowrap;">
                <?= htmlspecialchars($dta['hor'] ?? '-'); ?>
              </td>
              <?php
              $diasMapa = ['lun' => '1042', 'mar' => '1043', 'mie' => '1044', 'jue' => '1045', 'vie' => '1046', 'sab' => '1047'];
              foreach ($diasMapa as $campoDia => $idDiaVal):
                  $horasDia = isset($mapHorario[(string)($dta['idfic'] ?? '')][$idDiaVal]) ? $mapHorario[(string)($dta['idfic'] ?? '')][$idDiaVal] : [];
                  $horasCortas = [];
                  foreach ($horasDia as $h) {
                      $p = explode('-', $h);
                      $horasCortas[] = (int)substr($p[0], 0, 2) . '-' . (int)substr($p[1], 0, 2);
                  }
              ?>
              <td class="col-center"><?= !empty($dta[$campoDia]) ? '<span class="sena-dia-check">✓</span>' : '<span class="sena-dia-dash">-</span>'; ?><?php if (!empty($horasCortas)): ?><div class="mt-1"><?php foreach ($horasCortas as $ii => $hc): ?><span class="badge rounded-pill" style="background:#e8f5e9;color:#006828;font-size:9px;font-weight:600;" title="Horario asignado: <?= htmlspecialchars(implode(', ', $horasDia)); ?>"><?= htmlspecialchars($hc); ?></span><?php endforeach; ?></div><?php endif; ?></td>
              <?php endforeach; ?>
              <td class="col-center" style="white-space: nowrap; color: #475569;">
                <?= !empty($dta['fecini']) ? date('d/m/Y', strtotime($dta['fecini'])) : '-'; ?>
              </td>
              <td class="col-center" style="white-space: nowrap; color: #475569;">
                <?= !empty($dta['fecfin']) ? date('d/m/Y', strtotime($dta['fecfin'])) : '-'; ?>
              </td>
              <td class="col-center">
                <div class="sena-act-actions">
                  <!-- Botón para evaluar (no hace nada por el momento) -->
                  
<!-- Botón para ir a Criterios de Evaluación -->
<a href="home.php?pg=1546<?= !empty($dta['idage']) ? '&idage=' . urlencode($dta['idage']) : ''; ?>"
   class="sena-btn-action btn-eval"
   title="Criterios de Evaluación">
  <i class="fa-solid fa-clipboard-check"></i>
</a>


                  <!-- Botón para editar (manda a modificar en la vista de actividades) -->
                  <a href="home.php?pg=1508<?= !empty($idactRef) ? '&ope=edi&idact=' . urlencode($idactRef) : ''; ?>" 
                     class="sena-btn-action btn-edit" 
                     title="Modificar en la vista de Actividades">
                    <i class="fa-solid fa-pen-to-square"></i>
                  </a>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr id="trSinDatos">
            <td colspan="16" class="col-center" style="padding: 30px; color: #64748b;">
              <i class="fa-solid fa-calendar-xmark" style="font-size: 24px; margin-bottom: 8px; color: #94a3b8; display: block;"></i>
              No hay actividades programadas para la selección actual.
              <?php if (!empty($idfic) && !empty($esAdmin) || !empty($idfic) && !empty($esInstructor)): ?>
              <div class="mt-2">
                <a href="home.php?pg=1507&idfic=<?= urlencode($idfic); ?>" class="sena-btn-primary" title="Cargar agenda para esta ficha">
                  <i class="fa-solid fa-file-arrow-up"></i>
                  <span>Cargar agenda de la ficha <?= htmlspecialchars($idfic); ?></span>
                </a>
              </div>
              <?php endif; ?>
            </td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
  <!-- END: Tabla Principal -->

  <?php if (!empty($horariosFicha)): ?>
  <!-- BEGIN: Horarios registrados de la ficha (vienen del módulo Horario) -->
  <div class="card shadow-sm mb-3">
    <div class="card-body py-2">
      <h6 class="fw-bold text-dark mb-2">
        <i class="fa-solid fa-clock me-1 text-success"></i>
        Horarios registrados de la ficha <?= htmlspecialchars($idfic); ?>
        <a href="home.php?pg=1517&idfic_filter=<?= urlencode($idfic); ?>" class="btn btn-sm btn-outline-success ms-2" title="Gestionar en Horario por persona">
          <i class="fa-solid fa-arrow-right me-1"></i>Gestionar horario
        </a>
      </h6>
      <div class="table-responsive">
      <table class="table table-striped table-bordered align-middle mb-0">
        <thead><tr><th>Día</th><th>Hora Inicial</th><th>Hora Final</th><th>Aula</th><th>Instructor</th><th>Actividad</th></tr></thead>
        <tbody>
        <?php foreach ($horariosFicha as $hh): ?>
          <tr>
            <td><?= htmlspecialchars($hh['nomdia'] ?? $hh['iddia']); ?></td>
            <td><?= htmlspecialchars(!empty($hh['hinihor']) && $hh['hinihor'] !== '00:00:00' ? $hh['hinihor'] : '-'); ?></td>
            <td><?= htmlspecialchars(!empty($hh['hfinhor']) && $hh['hfinhor'] !== '00:00:00' ? $hh['hfinhor'] : '-'); ?></td>
            <td><?= htmlspecialchars($hh['nomaul'] ?? 'Sin aula'); ?></td>
            <td><?= htmlspecialchars($hh['nomusu'] ?? ''); ?></td>
            <td><?= htmlspecialchars(!empty($hh['actividad']) ? $hh['actividad'] : 'Formación'); ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      </div>
    </div>
  </div>
  <!-- END: Horarios registrados -->
  <?php endif; ?>

  <!-- BEGIN: Paginación -->
  <footer class="sena-pagination-footer">
    <div id="lblPaginacionInfo">
      Mostrando <strong id="lblPagInicio">1</strong> a <strong id="lblPagFin"><?= min(10, $totalRegistros); ?></strong> de <strong id="lblPagTotal"><?= $totalRegistros; ?></strong> registros
    </div>
    <div class="sena-pagination-controls" id="contenedorPaginacion">
      <!-- Generado dinámicamente con JavaScript -->
    </div>
  </footer>
  <!-- END: Paginación -->

<!-- ============================================================== -->
<!-- BEGIN: Modal de Generación de Reportes                         -->
<!-- ============================================================== -->
<div class="sena-modal-backdrop" id="modalReporteBackdrop" onclick="cerrarModalReporteBackdrop(event)">
  <div class="sena-modal-card" id="modalReporteCard">
    <div class="sena-modal-stripe"></div>
    <div class="sena-modal-header">
      <div class="sena-modal-header-left">
        <div class="sena-modal-icon">
          <i class="fa-solid fa-file-lines"></i>
        </div>
        <div>
          <h3>Generar Reporte de Formación</h3>
          <p>Configure los parámetros y filtros para la exportación detallada</p>
        </div>
      </div>
      <button type="button" class="sena-modal-close" onclick="cerrarModalReporte()" title="Cerrar modal">&times;</button>
    </div>

    <div class="sena-modal-body">
      <!-- 1. Rango Temporal -->
      <div class="sena-modal-section">
        <span class="sena-modal-label">Rango Temporal de Programación</span>
        <div class="sena-radio-grid">
          <label class="sena-radio-card">
            <input type="radio" name="modalDateMode" value="range" checked onchange="toggleModalDateInputs()">
            <span>Rango de fechas específico</span>
          </label>
          <label class="sena-radio-card">
            <input type="radio" name="modalDateMode" value="all" onchange="toggleModalDateInputs()">
            <span>Todas las fechas (Histórico)</span>
          </label>
        </div>

        <div class="sena-date-grid" id="modalDateGrid">
          <div>
            <label>Fecha Desde:</label>
            <input type="date" id="modalFecIni" value="<?= htmlspecialchars($fecini ?: (!empty($dtAge[0]['fecini']) ? $dtAge[0]['fecini'] : date('Y-01-01'))); ?>">
          </div>
          <div>
            <label>Fecha Hasta:</label>
            <input type="date" id="modalFecFin" value="<?= htmlspecialchars($fecfin ?: date('Y-12-31')); ?>">
          </div>
        </div>
      </div>

      <!-- 2. Filtros de Selección -->
      <div class="sena-select-grid">
        <div class="field-group">
          <label>Ficha de Caracterización</label>
          <select id="modalFicha">
            <option value="all">Todas las fichas</option>
            <?php if (!empty($dtFichas)): ?>
              <?php foreach ($dtFichas as $fic): 
                $isSelected = ($idfic == $fic['idfic']) ? 'selected' : '';
                $nomMostrar = !empty($fic['nompro']) ? $fic['idfic'] . ' - ' . sena_utf8_clean($fic['nompro']) : $fic['idfic'] . ' - ' . sena_utf8_clean($fic['nomfic']);
              ?>
                <option value="<?= htmlspecialchars($fic['idfic']); ?>" <?= $isSelected; ?>>
                  <?= htmlspecialchars($nomMostrar); ?>
                </option>
              <?php endforeach; ?>
            <?php endif; ?>
          </select>
        </div>

        <div class="field-group">
          <label>Instructor Responsable</label>
          <select id="modalInstructor">
            <option value="all">Todos los instructores</option>
            <?php if (!empty($dtInstructores)): ?>
              <?php foreach ($dtInstructores as $ins): ?>
                <option value="<?= htmlspecialchars($ins['idusu']); ?>" <?= ($idins == $ins['idusu']) ? 'selected' : ''; ?>>
                  <?= htmlspecialchars($ins['nomusu']); ?>
                </option>
              <?php endforeach; ?>
            <?php endif; ?>
          </select>
        </div>

        <div class="field-group">
          <label>Fase del Proyecto</label>
          <select id="modalFase">
            <option value="all">Todas las fases</option>
            <option value="Análisis">Análisis</option>
            <option value="Planeación">Planeación</option>
            <option value="Ejecución">Ejecución</option>
            <option value="Evaluación">Evaluación</option>
          </select>
        </div>

        <div class="field-group">
          <label>Jornada de Formación</label>
          <select id="modalJornada">
            <option value="all">Todas las jornadas</option>
            <?php if (!empty($dtJornadas)): ?>
              <?php foreach ($dtJornadas as $j): ?>
                <option value="<?= htmlspecialchars($j['idval']); ?>" <?= ($jornada == $j['idval']) ? 'selected' : ''; ?>>
                  <?= htmlspecialchars(sena_utf8_clean($j['nomval'])); ?>
                </option>
              <?php endforeach; ?>
            <?php endif; ?>
          </select>
        </div>
      </div>

      <!-- 3. Formato de Exportación -->
      <div>
        <span class="sena-modal-label">Formato de Exportación</span>
        <div class="sena-format-grid">
          <div class="sena-format-card" id="cardFormatPdf" onclick="seleccionarFormato('pdf')">
            <div class="sena-format-icon icon-pdf">
              <i class="fa-solid fa-file-pdf"></i>
            </div>
            <div class="sena-format-text">
              <div class="format-title">Documento PDF</div>
              <div class="format-desc">Para impresión formal e informes (.pdf)</div>
            </div>
          </div>

          <div class="sena-format-card selected" id="cardFormatExcel" onclick="seleccionarFormato('excel')">
            <div class="sena-format-icon icon-excel">
              <i class="fa-solid fa-file-excel"></i>
            </div>
            <div class="sena-format-text">
              <div class="format-title">Libro de Excel</div>
              <div class="format-desc">Datos tabulares editables (.xls / .xlsx)</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="sena-modal-footer">
      <button type="button" class="sena-btn-reset-modal" onclick="restablecerFiltrosModal()" title="Restablecer filtros del reporte">
        <i class="fa-solid fa-arrows-rotate"></i>
        <span>Restablecer Filtros</span>
      </button>
      <button type="button" class="sena-btn-cancel" onclick="cerrarModalReporte()">Cancelar</button>
      <button type="button" class="sena-btn-download" id="btnDescargarReporte" onclick="ejecutarDescargaReporte()">
        <i class="fa-solid fa-download"></i>
        <span>Descargar Reporte</span>
      </button>
    </div>
  </div>
</div>
<!-- END: Modal de Generación de Reportes -->

<!-- ============================================================== -->
<!-- JAVASCRIPT INTERACTIVO                                         -->
<!-- ============================================================== -->
<script>
  // Variables globales de estado
  let formatoSeleccionado = 'excel';
  let paginaActual = 1;
  const filasPorPagina = 10;
  let filasFiltradas = [];

  // Inicializar al cargar el DOM
  document.addEventListener('DOMContentLoaded', () => {
    actualizarColeccionFilas();
    renderizarPaginacion();
  });

  // Actualizar filas activas según los filtros en cliente
  function actualizarColeccionFilas() {
    const filas = Array.from(document.querySelectorAll('#tbodyAgenda tr.fila-agenda'));
    const textoBuscar = document.getElementById('inputBuscar').value.toLowerCase().trim();
    const faseFiltro = document.getElementById('selectFase').value.toLowerCase().trim();
    const instructorFiltro = document.getElementById('selectInstructor').value.trim();
    const fecIniFiltro = document.getElementById('inputFecIni').value;
    const fecFinFiltro = document.getElementById('inputFecFin').value;

    filasFiltradas = filas.filter(tr => {
      // Filtro texto
      if (textoBuscar && !tr.getAttribute('data-texto').includes(textoBuscar)) {
        return false;
      }
      // Filtro fase
      if (faseFiltro !== 'all' && faseFiltro !== '') {
        const trFase = (tr.getAttribute('data-fase') || '').toLowerCase();
        const fNorm = faseFaseNorm(faseFiltro);
        let match = false;
        if (fNorm.includes('an') && (trFase.includes('an') || trFase.includes('1'))) match = true;
        else if (fNorm.includes('plan') && (trFase.includes('plan') || trFase.includes('2'))) match = true;
        else if (fNorm.includes('ejec') && (trFase.includes('ejec') || trFase.includes('3'))) match = true;
        else if (fNorm.includes('eval') && (trFase.includes('eval') || trFase.includes('4'))) match = true;
        else if (trFase.includes(fNorm)) match = true;
        if (!match) return false;
      }
      // Filtro rango fechas
      const fIni = tr.getAttribute('data-fecini');
      const fFin = tr.getAttribute('data-fecfin');
      if (fecIniFiltro && fFin && fFin < fecIniFiltro) return false;
      if (fecFinFiltro && fIni && fIni > fecFinFiltro) return false;

      return true;
    });

    // Actualizar contador
    const lblContador = document.getElementById('lblTotalActividades');
    if (lblContador) lblContador.textContent = filasFiltradas.length;

    // Mostrar/ocultar mensaje sin datos
    let trSinDatos = document.getElementById('trSinDatos');
    if (filasFiltradas.length === 0) {
      if (!trSinDatos) {
        trSinDatos = document.createElement('tr');
        trSinDatos.id = 'trSinDatos';
        trSinDatos.innerHTML = `
          <td colspan="18" class="col-center" style="padding: 30px; color: #64748b;">
            <i class="fa-solid fa-calendar-xmark" style="font-size: 24px; margin-bottom: 8px; color: #94a3b8; display: block;"></i>
            No se encontraron actividades con los filtros seleccionados.
          </td>
        `;
        document.getElementById('tbodyAgenda').appendChild(trSinDatos);
      } else {
        trSinDatos.style.display = '';
      }
    } else if (trSinDatos) {
      trSinDatos.style.display = 'none';
    }
  }


  function faseFaseNorm(fas) {
    return fas.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
  }

  // Filtrar tabla en cliente
  function filtrarTablaLocal() {
    paginaActual = 1;
    actualizarColeccionFilas();
    renderizarPaginacion();
  }

  // Cambiar página
  function cambiarPagina(pag) {
    const totalPaginas = Math.ceil(filasFiltradas.length / filasPorPagina) || 1;
    if (pag < 1 || pag > totalPaginas) return;
    paginaActual = pag;
    renderizarPaginacion();
  }

  // Renderizar controles de paginación y filas visibles
  function renderizarPaginacion() {
    const total = filasFiltradas.length;
    const totalPaginas = Math.ceil(total / filasPorPagina) || 1;
    const inicio = (paginaActual - 1) * filasPorPagina;
    const fin = Math.min(inicio + filasPorPagina, total);

    // Ocultar todas las filas primero
    const todasLasFilas = document.querySelectorAll('#tbodyAgenda tr.fila-agenda');
    todasLasFilas.forEach(tr => tr.style.display = 'none');

    // Mostrar únicamente las de la página activa
    for (let i = inicio; i < fin; i++) {
      if (filasFiltradas[i]) {
        filasFiltradas[i].style.display = '';
      }
    }

    // Actualizar textos de paginación
    const lblInicio = document.getElementById('lblPagInicio');
    const lblFin = document.getElementById('lblPagFin');
    const lblTotal = document.getElementById('lblPagTotal');
    if (lblInicio) lblInicio.textContent = total > 0 ? (inicio + 1) : 0;
    if (lblFin) lblFin.textContent = fin;
    if (lblTotal) lblTotal.textContent = total;

    // Generar botones de paginación
    const contPag = document.getElementById('contenedorPaginacion');
    if (!contPag) return;
    contPag.innerHTML = '';

    // Botón anterior
    const btnAnt = document.createElement('button');
    btnAnt.className = `sena-page-btn ${paginaActual === 1 ? 'disabled' : ''}`;
    btnAnt.innerHTML = '&lt;';
    btnAnt.onclick = () => cambiarPagina(paginaActual - 1);
    contPag.appendChild(btnAnt);

    // Botones numéricos
    for (let p = 1; p <= totalPaginas; p++) {
      if (totalPaginas <= 7 || (p >= paginaActual - 2 && p <= paginaActual + 2) || p === 1 || p === totalPaginas) {
        const btnNum = document.createElement('button');
        btnNum.className = `sena-page-btn ${p === paginaActual ? 'active' : ''}`;
        btnNum.textContent = p;
        btnNum.onclick = () => cambiarPagina(p);
        contPag.appendChild(btnNum);
      }
    }

    // Botón siguiente
    const btnSig = document.createElement('button');
    btnSig.className = `sena-page-btn ${paginaActual === totalPaginas ? 'disabled' : ''}`;
    btnSig.innerHTML = '&gt;';
    btnSig.onclick = () => cambiarPagina(paginaActual + 1);
    contPag.appendChild(btnSig);
  }

  // Aplicar filtros en servidor (por ejemplo al cambiar de ficha)
  function aplicarFiltrosServidor() {
    document.getElementById('formFiltrosAgenda').submit();
  }

  // Restablecer filtros
  function restablecerTodosFiltros() {
    document.getElementById('inputBuscar').value = '';
    document.getElementById('selectFase').value = 'all';
    document.getElementById('selectJornada').value = 'all';
    document.getElementById('selectInstructor').value = 'all';
    document.getElementById('inputFecIni').value = '';
    document.getElementById('inputFecFin').value = '';
    filtrarTablaLocal();
  }

  // Ayuda del módulo
  function mostrarAyudaAgenda() {
    if (typeof Swal !== 'undefined') {
      Swal.fire({
        title: 'Módulo de Agenda SENA',
        html: `
          <div style="text-align: left; font-size: 13px; line-height: 1.5;">
            <p>Este módulo presenta la <strong>programación detallada de formación</strong> organizada por Ficha y Fase del Proyecto.</p>
            <ul>
              <li><strong>Filtros:</strong> Puedes filtrar en tiempo real por ficha, fase, jornada, instructor y rango de fechas.</li>
              <li><strong>Días:</strong> La marca <strong>✓</strong> indica los días en que se orienta la actividad.</li>
              <li><strong>Reportes:</strong> Pulsa el botón verde <em>Generar Reporte</em> para exportar la información a PDF o Excel con filtros personalizados.</li>
            </ul>
          </div>
        `,
        icon: 'info',
        confirmButtonColor: '#008F39',
        confirmButtonText: 'Entendido'
      });
    } else {
      alert('Módulo de Agenda SENA: Consulta la programación de formación por ficha, filtra por instructor o fechas y genera reportes en PDF o Excel.');
    }
  }

  // MODAL DE REPORTES
  function abrirModalReporte() {
    // Sincronizar selectores del modal con los filtros activos en pantalla
    document.getElementById('modalFicha').value = document.getElementById('selectFicha').value;
    document.getElementById('modalFase').value = document.getElementById('selectFase').value;
    document.getElementById('modalInstructor').value = document.getElementById('selectInstructor').value;
    document.getElementById('modalJornada').value = document.getElementById('selectJornada').value;

    const modal = document.getElementById('modalReporteBackdrop');
    modal.classList.add('show');
  }

  function cerrarModalReporte() {
    const modal = document.getElementById('modalReporteBackdrop');
    modal.classList.remove('show');
  }

  function cerrarModalReporteBackdrop(e) {
    if (e.target.id === 'modalReporteBackdrop') {
      cerrarModalReporte();
    }
  }

  function restablecerFiltrosModal() {
    // 1. Rango temporal: volver a rango específico con fechas iniciales del año
    const radioRange = document.querySelector('input[name="modalDateMode"][value="range"]');
    if (radioRange) {
      radioRange.checked = true;
      toggleModalDateInputs();
    }
    const fecIniInput = document.getElementById('inputFecIni');
    const fecFinInput = document.getElementById('inputFecFin');
    document.getElementById('modalFecIni').value = (fecIniInput && fecIniInput.value) ? fecIniInput.value : '<?= date("Y-01-01"); ?>';
    document.getElementById('modalFecFin').value = (fecFinInput && fecFinInput.value) ? fecFinInput.value : '<?= date("Y-12-31"); ?>';

    // 2. Ficha: restaurar la ficha predeterminada (elegida desde Horarios o activa)
    const selectFicha = document.getElementById('selectFicha');
    if (selectFicha && selectFicha.value !== 'all') {
      document.getElementById('modalFicha').value = selectFicha.value;
    } else {
      document.getElementById('modalFicha').value = '<?= htmlspecialchars($idfic ?: "all"); ?>';
    }

    // 3. Restablecer los demás filtros a 'all'
    document.getElementById('modalInstructor').value = 'all';
    document.getElementById('modalFase').value = 'all';
    document.getElementById('modalJornada').value = 'all';

    // 4. Formato de exportación predeterminado
    seleccionarFormato('excel');
  }

  function toggleModalDateInputs() {
    const mode = document.querySelector('input[name="modalDateMode"]:checked').value;
    const grid = document.getElementById('modalDateGrid');
    if (mode === 'all') {
      grid.style.opacity = '0.35';
      grid.style.pointerEvents = 'none';
    } else {
      grid.style.opacity = '1';
      grid.style.pointerEvents = 'auto';
    }
  }

  function seleccionarFormato(formato) {
    formatoSeleccionado = formato;
    document.getElementById('cardFormatPdf').classList.toggle('selected', formato === 'pdf');
    document.getElementById('cardFormatExcel').classList.toggle('selected', formato === 'excel');
  }

  function ejecutarDescargaReporte() {
    const dateMode = document.querySelector('input[name="modalDateMode"]:checked').value;
    const fecini = (dateMode === 'range') ? document.getElementById('modalFecIni').value : '';
    const fecfin = (dateMode === 'range') ? document.getElementById('modalFecFin').value : '';
    const idfic = document.getElementById('modalFicha').value;
    const fas = document.getElementById('modalFase').value;
    const idins = document.getElementById('modalInstructor').value;
    const jornada = document.getElementById('modalJornada').value;

    // Construir URL de exportación hacia el controlador horcage
    const params = new URLSearchParams({
      export: formatoSeleccionado,
      idfic: idfic,
      fas: fas,
      idins: idins,
      jornada: jornada,
      fecini: fecini,
      fecfin: fecfin
    });

    const url = 'controllers/horcage.php?' + params.toString();

    // Animación del botón
    const btn = document.getElementById('btnDescargarReporte');
    const originalHtml = btn.innerHTML;
    btn.innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i> <span>Generando ${formatoSeleccionado.toUpperCase()}...</span>`;
    btn.disabled = true;

    // Ejecutar apertura directa para evitar bloqueos del navegador
    if (formatoSeleccionado === 'pdf') {
      window.open(url, '_blank');
    } else {
      window.location.href = url;
    }

    setTimeout(() => {
      btn.innerHTML = originalHtml;
      btn.disabled = false;
      cerrarModalReporte();
    }, 600);
  }
</script>
