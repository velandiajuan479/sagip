<?php
include("controllers/horccmprag.php");
$pg = isset($_REQUEST["pg"]) ? (int)$_REQUEST["pg"] : (isset($pag) ? (int)$pag : 1505);
$pag = $pg;
$icono = isset($icono) && !empty($icono) ? $icono : 'fa-solid fa-layer-group';

$claseJornada = [1 => 'success', 2 => 'primary', 3 => 'dark', 4 => 'warning', 5 => 'info'];
$claseJornadaNombre = ['Mañana' => 'success', 'Tarde' => 'primary', 'Noche' => 'dark', 'Fin de semana' => 'warning', 'Virtual' => 'info'];
$tiposFiltro = [
    'todos' => ['label' => 'Todos', 'icono' => 'fa-layer-group'],
    'planta' => ['label' => 'Planta', 'icono' => 'fa-user-tie'],
    'contratista' => ['label' => 'Contratistas', 'icono' => 'fa-id-badge'],
    'sin_clasificar' => ['label' => 'Otros', 'icono' => 'fa-user-slash'],
    'sin_asignar' => ['label' => 'Sin asignar', 'icono' => 'fa-triangle-exclamation'],
];
$tipoQueryCarga = ($tipoCarga !== 'todos') ? '&tipo=' . urlencode($tipoCarga) : '';
?>

<style>
.carga-pagina{--carga-verde:#006b29;--carga-verde-claro:#eaf7ee;--carga-azul:#0b4f7a;--carga-naranja:#a65a00;--carga-borde:#e5e7eb}
.carga-kpi{border:1px solid var(--carga-borde);border-radius:.9rem;background:#fff;box-shadow:0 3px 12px rgba(15,23,42,.05);height:100%;padding:1rem}
.carga-kpi-label{font-size:.68rem;letter-spacing:.06em;text-transform:uppercase;font-weight:700;color:#64748b}
.carga-kpi-value{font-size:1.75rem;line-height:1;font-weight:800;color:#0f172a;margin-top:.45rem}
.carga-kpi-icon{width:2.65rem;height:2.65rem;border-radius:.8rem;display:flex;align-items:center;justify-content:center;font-size:1.1rem}
.carga-filter{border:1px solid var(--carga-borde);border-radius:1rem;background:#fff;padding:1rem 1.1rem;box-shadow:0 3px 12px rgba(15,23,42,.05)}
.carga-filter-label{font-size:.7rem;letter-spacing:.05em;text-transform:uppercase;font-weight:800;color:#64748b;margin-bottom:.4rem}
.carga-pills{display:flex;gap:.4rem;flex-wrap:wrap}
.carga-pill{display:inline-flex;align-items:center;gap:.35rem;border:1px solid #dbe4df;border-radius:999px;padding:.38rem .7rem;background:#f8faf9;color:#405348;text-decoration:none;font-size:.78rem;font-weight:700;transition:.18s ease}
.carga-pill:hover{border-color:#8ecaa5;background:#f0faf3;color:#075b35;transform:translateY(-1px)}
.carga-pill.active{border-color:#087b3d;background:#087b3d;color:#fff;box-shadow:0 4px 10px rgba(0,107,41,.18)}
.carga-pill .carga-pill-count{display:inline-flex;align-items:center;justify-content:center;min-width:1.2rem;height:1.2rem;padding:0 .28rem;border-radius:999px;background:rgba(0,0,0,.07);font-size:.68rem}
.carga-pill.active .carga-pill-count{background:rgba(255,255,255,.22)}
.carga-search{border:1px solid #dbe4df!important;border-radius:.65rem!important;min-width:220px}
.carga-search:focus{border-color:#55a875!important;box-shadow:0 0 0 .2rem rgba(0,123,61,.12)!important}
.carga-section{margin-top:1.5rem}
.carga-section-head{display:flex;align-items:center;justify-content:space-between;gap:1rem;margin-bottom:.7rem;padding:.75rem .9rem;border-left:4px solid #087b3d;border-radius:.65rem;background:#f8fbf9}
.carga-section-head h2{font-size:1rem!important;padding:0!important;margin:0!important;color:#1f3b2a!important}
.carga-section-head p{font-size:.75rem;margin:.15rem 0 0;color:#64748b}
.carga-section-icon{width:2rem;height:2rem;border-radius:.6rem;display:inline-flex;align-items:center;justify-content:center;background:#e5f5ea;color:#087b3d;margin-right:.55rem;vertical-align:middle}
.carga-section-contratista{border-left-color:#d97706!important;background:#fffaf1!important}
.carga-section-contratista .carga-section-icon{background:#ffedd5;color:#b45309}
.carga-section-otros{border-left-color:#64748b!important;background:#f8fafc!important}
.carga-section-otros .carga-section-icon{background:#e2e8f0;color:#475569}
.carga-section-pendiente{border-left-color:#dc2626!important;background:#fff7f7!important}
.carga-section-pendiente .carga-section-icon{background:#fee2e2;color:#dc2626}
.carga-card{border:1px solid #dfe8e2!important;border-radius:.9rem!important;background:#fff;box-shadow:0 4px 12px rgba(15,23,42,.05);height:100%;overflow:hidden;transition:transform .18s ease,box-shadow .18s ease,border-color .18s ease}
.carga-card:hover{transform:translateY(-3px);border-color:#a9d5b8!important;box-shadow:0 12px 24px rgba(15,23,42,.1)}
.carga-card-head{padding:.9rem 1rem;background:linear-gradient(180deg,#f7fbf8,#fff);border-bottom:1px solid #edf2ee;display:flex;align-items:flex-start;gap:.7rem}
.carga-avatar{width:2.65rem;height:2.65rem;flex:0 0 2.65rem;border-radius:.8rem;display:flex;align-items:center;justify-content:center;background:#dff3e6;color:#087b3d;font-weight:800;font-size:.95rem}
.carga-card-contratista .carga-avatar{background:#ffedd5;color:#b45309}
.carga-card-otros .carga-avatar{background:#e2e8f0;color:#475569}
.carga-card-pendiente .carga-avatar{background:#fee2e2;color:#dc2626}
.carga-card-name{font-weight:800;color:#17251d;line-height:1.15;font-size:.93rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:190px}
.carga-role{display:inline-flex;align-items:center;gap:.25rem;border-radius:999px;padding:.2rem .45rem;font-size:.66rem;font-weight:800;margin-top:.3rem;background:#e5f5ea;color:#087b3d}
.carga-role-contratista{background:#ffedd5;color:#b45309}
.carga-role-otros{background:#e2e8f0;color:#475569}
.carga-role-pendiente{background:#fee2e2;color:#dc2626}
.carga-card-count{margin-left:auto;text-align:right;white-space:nowrap}
.carga-card-count strong{display:block;font-size:1.2rem;line-height:1;color:#0f172a}
.carga-card-count small{display:block;color:#64748b;font-size:.65rem;margin-top:.2rem}
.carga-card-body{padding:.75rem}
.carga-summary{display:grid;grid-template-columns:repeat(4,1fr);gap:.35rem;margin-bottom:.65rem}
.carga-summary-item{border-radius:.55rem;background:#f8fafc;padding:.42rem .3rem;text-align:center}
.carga-summary-item strong{display:block;font-size:.9rem;color:#1e3a2a}
.carga-summary-item small{display:block;color:#64748b;font-size:.6rem;margin-top:.12rem}
.carga-details summary{cursor:pointer;list-style:none}
.carga-details summary::-webkit-details-marker{display:none}
.carga-ficha{border:1px solid #e5ebe7!important;border-radius:.7rem!important;background:#fff;margin-bottom:.5rem;overflow:hidden}
.carga-ficha[open]{border-color:#acd4b9!important;box-shadow:0 3px 10px rgba(6,95,70,.06)}
.carga-ficha>summary{display:flex!important;align-items:center;gap:.4rem;flex-wrap:wrap;padding:.6rem .65rem!important;background:#fbfdfb}
.carga-ficha-id{font-size:.8rem;font-weight:800;color:#164d2c}
.carga-ficha-name{width:100%;font-size:.7rem;color:#64748b;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.carga-ficha-body{padding:.7rem;border-top:1px solid #edf2ee}
.carga-meta{display:grid;grid-template-columns:1fr 1fr;gap:.45rem;margin-bottom:.6rem}
.carga-meta-item{background:#f8fafc;border-radius:.5rem;padding:.42rem .5rem;min-width:0}
.carga-meta-item small{display:block;color:#64748b;font-size:.62rem;margin-bottom:.15rem}
.carga-meta-item strong{display:block;color:#334155;font-size:.7rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.carga-counts{display:flex;gap:.3rem;flex-wrap:wrap;margin-bottom:.6rem}
.carga-count{font-size:.65rem;border-radius:999px;padding:.22rem .45rem;background:#eef6f0;color:#24603c;font-weight:700}
.carga-docs{border-top:1px dashed #dce6df;padding-top:.55rem;margin-top:.2rem}
.carga-doc{display:block;font-size:.68rem;color:#475569;padding:.28rem 0;border-bottom:1px solid #f1f5f2}
.carga-doc:last-child{border-bottom:0}
.carga-doc strong{color:#1f3b2a}
.carga-actions{display:flex;gap:.35rem;flex-wrap:wrap;margin-top:.65rem}
.carga-actions .btn{font-size:.7rem!important;padding:.32rem .5rem!important;border-radius:.5rem!important}
.carga-empty{border:1px dashed #cbd5e1;border-radius:1rem;background:#f8fafc;padding:2.5rem 1rem;text-align:center;color:#64748b}
.carga-chip{display:inline-flex;align-items:center;gap:.25rem;border-radius:999px;background:#f1f5f9;color:#475569;padding:.22rem .45rem;font-size:.65rem;font-weight:700}
.carga-chip-success{background:#e5f5ea;color:#087b3d}
.carga-chip-warning{background:#ffedd5;color:#b45309}
.carga-modal{display:none;position:fixed;inset:0;z-index:1055;background:rgba(15,23,42,.58);align-items:center;justify-content:center;padding:1rem}
.carga-modal:target{display:flex}
.carga-modal .carga-modal-box{background:#fff;border-radius:.8rem;max-width:560px;width:100%;max-height:90vh;overflow-y:auto}
@media(max-width:767.98px){.carga-meta{grid-template-columns:1fr}.carga-card-name{max-width:145px}.carga-summary{grid-template-columns:repeat(2,1fr)}.carga-filter{padding:.85rem}.carga-search{min-width:0;width:100%}}
</style>

<div class="conte carga-pagina">
    <div class="mb-3">
        <h1 class="mb-0">Carga Masiva</h1>
    </div>

    <?php if (!empty($mensajeCarga)): ?>
        <div class="alert alert-<?=htmlspecialchars($tipoMensajeCarga);?> alert-dismissible fade show shadow-sm mb-3" role="alert">
            <?=$mensajeCarga;?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($resumenCarga)): ?>
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-body p-3 d-flex flex-wrap gap-2 align-items-center">
                <span class="fw-bold text-dark"><i class="fa-solid fa-clipboard-check me-2 text-success"></i>Revisión de la carga</span>
                <small class="text-muted"><?=htmlspecialchars($resumenCarga['nombre_archivo'] ?? '');?> · hoja <?=htmlspecialchars($resumenCarga['hoja_procesada'] ?? '');?></small>
                <span class="carga-chip">Leídas: <?= (int)($resumenCarga['filas_leidas'] ?? 0); ?></span>
                <span class="carga-chip">Reconocidas: <?= (int)($resumenCarga['filas_reconocidas'] ?? 0); ?></span>
                <span class="carga-chip carga-chip-success">Válidas: <?= (int)($resumenCarga['filas_validas'] ?? 0); ?></span>
                <span class="carga-chip">Agendas: <?= (int)($resumenCarga['agendas'] ?? 0); ?></span>
                <span class="carga-chip">Planeaciones: <?= (int)($resumenCarga['planeaciones'] ?? 0); ?></span>
                <span class="carga-chip">Competencias: <?= (int)($resumenCarga['competencias'] ?? 0); ?></span>
                <span class="carga-chip">Resultados: <?= (int)($resumenCarga['resultados'] ?? 0); ?></span>
                <?php if (!empty($resumenCarga['ficha_detectada'])): ?><span class="carga-chip">Ficha: <?=htmlspecialchars($resumenCarga['ficha_detectada']);?></span><?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="row g-3 mb-3">
        <div class="col-6 col-xl-3"><div class="carga-kpi d-flex justify-content-between align-items-center"><div><div class="carga-kpi-label">Programas</div><div class="carga-kpi-value"><?= (int)($resumenGeneral['programas'] ?? 0); ?></div></div><div class="carga-kpi-icon" style="background:#fef3c7;color:#92400e"><i class="fa-solid fa-graduation-cap"></i></div></div></div>
        <div class="col-6 col-xl-3"><div class="carga-kpi d-flex justify-content-between align-items-center"><div><div class="carga-kpi-label">Fichas sin instructor</div><div class="carga-kpi-value"><?= (int)($resumenGeneral['fichas_sin_instructor'] ?? 0); ?></div></div><div class="carga-kpi-icon" style="background:#fee2e2;color:#dc2626"><i class="fa-solid fa-user-slash"></i></div></div></div>
        <div class="col-6 col-xl-3"><div class="carga-kpi d-flex justify-content-between align-items-center"><div><div class="carga-kpi-label">Sesiones sin instructor</div><div class="carga-kpi-value"><?= (int)($resumenGeneral['sesiones_sin_instructor'] ?? 0); ?></div></div><div class="carga-kpi-icon" style="background:#ffedd5;color:#b45309"><i class="fa-solid fa-calendar-xmark"></i></div></div></div>
        <div class="col-6 col-xl-3"><div class="carga-kpi d-flex justify-content-between align-items-center"><div><div class="carga-kpi-label">Fichas en filtro</div><div class="carga-kpi-value"><?= (int)($resumenGeneral['fichas'] ?? 0); ?></div></div><div class="carga-kpi-icon" style="background:#e5f5ea;color:#087b3d"><i class="fa-solid fa-layer-group"></i></div></div></div>
    </div>

    <div class="card shadow-sm border-0 mb-3" id="plantillasCarga">
        <div class="card-header bg-white border-bottom py-2 d-flex align-items-center justify-content-between gap-2">
            <h6 class="card-title mb-0 fw-bold text-dark"><i class="fa-solid fa-file-arrow-down me-2 text-success"></i>Plantillas de trabajo</h6>
            <small class="text-muted">Descargue, diligencie y suba el archivo</small>
        </div>
        <div class="card-body p-3">
            <div class="row g-3 justify-content-center text-center">
                <div class="col-12 col-md-5"><div class="card h-100 border-0 shadow-sm p-3 bg-light"><h6 class="fw-bold mb-2 text-dark"><i class="fa-solid fa-calendar-days me-1 text-success"></i>Plantilla de Agendas</h6><p class="small text-muted">Jornada, ficha, instructor y actividades.</p><a href="EXCEL/plantilla_agendas.xlsx" download class="btn btn-outline-success btn-sm w-100 shadow-sm"><i class="fa-solid fa-download me-1"></i>Descargar</a></div></div>
                <div class="col-12 col-md-5"><div class="card h-100 border-0 shadow-sm p-3 bg-light"><h6 class="fw-bold mb-2 text-dark"><i class="fa-solid fa-chalkboard-user me-1 text-success"></i>Plantilla de Planeación</h6><p class="small text-muted">Competencias, resultados y evidencias.</p><a href="EXCEL/plantilla_planeacion.xlsx" download class="btn btn-outline-success btn-sm w-100 shadow-sm"><i class="fa-solid fa-download me-1"></i>Descargar</a></div></div>
            </div>
        </div>
    </div>

    <?php if (!empty($puedeSubirCarga)): ?>
        <div class="card shadow-sm border-0 mb-3" id="pasoSubirCarga"><div class="card-body p-3 text-center"><a href="#modalSubida" class="btn btn-success shadow-sm px-4"><i class="fa-solid fa-cloud-arrow-up me-1"></i> Subir agenda o planeación</a></div></div>
    <?php endif; ?>

    <div class="carga-filter mb-3">
        <form method="GET" action="home.php" class="row g-3 align-items-end">
            <input type="hidden" name="pg" value="<?= (int)$pg; ?>">
            <?php if (!empty($verTodasCarga)): ?><input type="hidden" name="ver" value="todas"><?php endif; ?>
            <div class="col-12 col-xl-7">
                <div class="carga-filter-label">Filtrar por responsable</div>
                <div class="carga-pills">
                    <?php foreach ($tiposFiltro as $claveFiltro => $datosFiltro):
                        $conteoFiltro = $conteoTipos[$claveFiltro] ?? 0;
                    ?>
                        <a class="carga-pill <?=($tipoCarga === $claveFiltro) ? 'active':'';?>" href="<?=htmlspecialchars($urlPorTipoCarga[$claveFiltro] ?? $urlLimpiarCarga);?>">
                            <i class="fa-solid <?=htmlspecialchars($datosFiltro['icono']);?>"></i>
                            <?=htmlspecialchars($datosFiltro['label']);?>
                            <span class="carga-pill-count"><?= (int)$conteoFiltro; ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="col-12 col-md-7 col-xl-3">
                <div class="carga-filter-label">Vigencia</div>
                <div class="carga-pills">
                    <a class="carga-pill <?=empty($verTodasCarga) ? 'active':'';?>" href="<?=htmlspecialchars($urlVigenteCarga);?>"><i class="fa-solid fa-calendar-check"></i>Vigentes</a>
                    <a class="carga-pill <?=!empty($verTodasCarga) ? 'active':'';?>" href="<?=htmlspecialchars($urlTodasCarga);?>"><i class="fa-solid fa-layer-group"></i>Todas</a>
                </div>
            </div>
            <div class="col-12 col-md-5 col-xl-2">
                <label class="carga-filter-label" for="qCarga">Buscar</label>
                <div class="d-flex gap-1">
                    <input class="form-control form-control-sm carga-search" id="qCarga" name="q" value="<?=htmlspecialchars($qCarga ?? '');?>" placeholder="Instructor, ficha o programa">
                    <button class="btn btn-success btn-sm px-3" type="submit" title="Buscar"><i class="fa-solid fa-magnifying-glass"></i></button>
                </div>
            </div>
            <?php if (($qCarga ?? '') !== '' || $tipoCarga !== 'todos' || !empty($verTodasCarga)): ?>
                <div class="col-12 text-end"><a class="btn btn-link btn-sm text-decoration-none" href="<?=htmlspecialchars($urlLimpiarCarga);?>"><i class="fa-solid fa-rotate-left me-1"></i>Limpiar filtros</a></div>
            <?php endif; ?>
        </form>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-bottom py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div><h2 class="h5 mb-1 fw-bold text-dark"><i class="fa-solid fa-layer-group me-2 text-success"></i>Módulos por responsable</h2><p class="small text-muted mb-0">Cada ficha conserva sus conteos de agendas, sesiones y guías.</p></div>
            <span class="carga-chip carga-chip-success"><?= (int)($resumenGeneral['fichas'] ?? 0); ?> fichas visibles</span>
        </div>
        <div class="card-body p-3">
            <?php if (empty($seccionesInstructores)): ?>
                <div class="carga-empty"><i class="fa-solid fa-folder-open fa-2x mb-2 d-block opacity-50"></i><strong class="d-block text-dark">No hay fichas para este filtro</strong><span class="small">Pruebe con otra búsqueda, «Todas» o quite el filtro de responsable.</span></div>
            <?php else: ?>
                <?php foreach ($seccionesPaginadasCarga as $seccionIdx => $seccion): ?>
                    <section class="carga-section">
                        <div class="carga-section-head <?=htmlspecialchars($seccion['clase']);?>">
                            <div class="d-flex align-items-center"><span class="carga-section-icon"><i class="fa-solid <?=htmlspecialchars($seccion['icono']);?>"></i></span><div><h2><?=htmlspecialchars($seccion['titulo']);?></h2><p><?=htmlspecialchars($seccion['descripcion']);?></p></div></div>
                            <span class="carga-chip"><?= (int)$seccion['total_instructores']; ?> responsable(s)</span>
                        </div>
                        <div class="row g-3">
                            <?php foreach ($seccion['grupos'] as $grupoIdx => $grupo):
                                $tipoGrupo = $grupo['tipo'] ?? 'sin_clasificar';
                                $inicialesGrupo = strtoupper(mb_substr(trim((string)$grupo['nombre']), 0, 1));
                                $resumenGrupo = $grupo['resumen'] ?? ['fichas'=>0,'agendas'=>0,'sesiones'=>0,'actividades'=>0,'aprendices'=>0];
                            ?>
                                <div class="col-12 col-md-6 col-xxl-4">
                                    <article class="carga-card carga-card-<?=htmlspecialchars($tipoGrupo);?>">
                                        <header class="carga-card-head">
                                            <div class="carga-avatar"><?=htmlspecialchars($inicialesGrupo ?: '?');?></div>
                                            <div class="min-w-0 flex-grow-1">
                                                <div class="carga-card-name" title="<?=htmlspecialchars($grupo['nombre']);?>"><?=htmlspecialchars($grupo['nombre']);?></div>
                                                <span class="carga-role carga-role-<?=htmlspecialchars($tipoGrupo);?>"><i class="fa-solid fa-circle-info me-1"></i><?=htmlspecialchars($grupo['etiqueta_tipo'] ?? 'Instructor');?></span>
                                            </div>
                                            <div class="carga-card-count"><strong><?= (int)$resumenGrupo['fichas']; ?></strong><small>fichas</small></div>
                                        </header>
                                        <div class="carga-card-body">
                                            <div class="carga-summary">
                                                <div class="carga-summary-item"><strong><?= (int)$resumenGrupo['agendas']; ?></strong><small>agendas</small></div>
                                                <div class="carga-summary-item"><strong><?= (int)$resumenGrupo['sesiones']; ?></strong><small>sesiones</small></div>
                                                <div class="carga-summary-item"><strong><?= (int)$resumenGrupo['actividades']; ?></strong><small>guías</small></div>
                                                <div class="carga-summary-item"><strong><?= (int)$resumenGrupo['aprendices']; ?></strong><small>aprendices</small></div>
                                            </div>
                                            <details class="carga-details" <?=($seccionIdx === 0 && $grupoIdx === 0) || ($qCarga ?? '') !== '' ? 'open':'';?>>
                                                <summary class="btn btn-sm btn-outline-success w-100 fw-semibold"><i class="fa-solid fa-eye me-1"></i>Ver fichas asignadas (<?= (int)$resumenGrupo['fichas']; ?>)</summary>
                                                <div class="mt-2">
                                                    <?php foreach ($grupo['fichas'] as $fic):
                                                        $idfic = (string)($fic['idfic'] ?? '');
                                                        $cnt = $conteosFicha[$idfic] ?? ['agendas'=>0,'sesiones'=>0,'actividades'=>0];
                                                        $jornada = Mcmas::nombreJornada($fic['jornada'] ?? 0);
                                                        $badgeJornada = $claseJornada[(int)($fic['jornada'] ?? 0)] ?? 'secondary';
                                                        $instructor = $instructoresFicha[$idfic] ?? ($grupo['nombre'] ?? 'Sin asignar');
                                                        $docsFicha = array_slice($docsPorFicha[$idfic] ?? [], 0, 3);
                                                        $totalDocsFicha = count($docsPorFicha[$idfic] ?? []);
                                                        $normFic = mb_strtolower(trim((string)($fic['nomfic'] ?? '')));
                                                        $normPro = mb_strtolower(trim((string)($fic['nompro'] ?? '')));
                                                        $mostrarNomFic = $normFic !== '' && $normFic !== $normPro;
                                                    ?>
                                                        <details class="carga-ficha">
                                                            <summary>
                                                                <span class="carga-ficha-id">Ficha <?=htmlspecialchars($idfic);?></span>
                                                                <span class="badge bg-<?=htmlspecialchars($badgeJornada);?>"><?=htmlspecialchars($jornada);?></span>
                                                                <?php if (!empty($docsFicha)): ?><span class="carga-chip carga-chip-success">Cargada</span><?php else: ?><span class="carga-chip carga-chip-warning">Sin documentos</span><?php endif; ?>
                                                                <?php if ($mostrarNomFic): ?><span class="carga-ficha-name"><?=htmlspecialchars($fic['nomfic']);?></span><?php endif; ?>
                                                            </summary>
                                                            <div class="carga-ficha-body">
                                                                <div class="carga-meta">
                                                                    <?php if (!empty($fic['nompro'])): ?><div class="carga-meta-item"><small>Programa</small><strong title="<?=htmlspecialchars($fic['nompro']);?>"><?=htmlspecialchars($fic['nompro']);?><?= (!empty($fic['codpro']) && $fic['codpro'] != 1 ? ' · '.htmlspecialchars($fic['codpro']) : '');?></strong></div><?php endif; ?>
                                                                    <div class="carga-meta-item"><small>Jornada</small><strong><?=htmlspecialchars($jornada);?></strong></div>
                                                                    <div class="carga-meta-item"><small>Responsable</small><strong title="<?=htmlspecialchars($instructor);?>"><?=htmlspecialchars($instructor);?></strong></div>
                                                                    <div class="carga-meta-item"><small>Vigencia</small><strong><?=htmlspecialchars(($fic['finific'] ?? '-').' → '.($fic['ffinfic'] ?? '-'));?></strong></div>
                                                                </div>
                                                                <div class="carga-counts"><span class="carga-chip"><?= (int)$cnt['agendas']; ?> agendas</span><span class="carga-chip"><?= (int)$cnt['sesiones']; ?> sesiones</span><span class="carga-chip"><?= (int)$cnt['actividades']; ?> guías</span><?php if (isset($fic['total_aprendices'])): ?><span class="carga-chip"><?= (int)$fic['total_aprendices']; ?> aprendices</span><?php endif; ?></div>
                                                                <?php $jorAgs = $jorAgendasPorFicha[$idfic] ?? []; ?><?php if (!empty($jorAgs)): ?><div class="small text-muted mb-2">Agendas en: <?php foreach ($jorAgs as $ja): ?><span class="badge bg-<?=htmlspecialchars($claseJornadaNombre[$ja] ?? 'secondary');?> ms-1"><?=htmlspecialchars($ja);?></span><?php endforeach; ?></div><?php endif; ?>
                                                                <?php $colegasFicha = $fichasColegas[$idfic] ?? []; ?><?php if (!empty($colegasFicha)): ?><div class="small text-muted mb-2"><i class="fa-solid fa-users me-1"></i>También a cargo de: <?php foreach ($colegasFicha as $idxCol => $colFicha): ?><a href="<?=htmlspecialchars('home.php?pg='.(int)$pg.(!empty($verTodasCarga)?'&ver=todas':'').$tipoQueryCarga.'&q='.urlencode((string)($colFicha['idfic'] ?? '')));?>">Ficha <?=htmlspecialchars($colFicha['idfic'] ?? '');?></a><?=($idxCol < count($colegasFicha)-1 ? ',' : '');?><?php endforeach; ?></div><?php endif; ?>
                                                                <div class="carga-docs"><div class="carga-filter-label mb-1">Documentos</div><?php if (!empty($docsFicha)): foreach ($docsFicha as $doc): ?><span class="carga-doc"><strong>Agenda #<?= (int)($doc['idage'] ?? 0); ?></strong> · <?=htmlspecialchars($doc['actpro'] ?? 'Actividad');?><br><small><?=htmlspecialchars(($doc['fecini'] ?? '-').' → '.($doc['fecfin'] ?? '-'));?> · <?= (int)($doc['sesiones'] ?? 0); ?> sesiones · <?= (int)($doc['actividades'] ?? 0); ?> guías</small><?php $otrasAge = array_values(array_filter(($fichasPorAgenda[(int)($doc['idage'] ?? 0)] ?? []), static function ($otra) use ($idfic) { return (string)$otra !== (string)$idfic; })); ?><?php if (!empty($otrasAge)): ?><small class="d-block text-muted mt-1">Sirve también para: <?php foreach ($otrasAge as $idxOtra => $otraFicha): ?><a href="<?=htmlspecialchars('home.php?pg='.(int)$pg.(!empty($verTodasCarga)?'&ver=todas':'').$tipoQueryCarga.'&q='.urlencode((string)$otraFicha));?>"><?=htmlspecialchars($otraFicha);?></a><?=($idxOtra<count($otrasAge)-1?',':'');?><?php endforeach; ?></small><?php endif; ?></span><?php endforeach; ?><?php if ($totalDocsFicha > 3): ?><span class="small text-muted">+<?= $totalDocsFicha - 3; ?> documento(s) más</span><?php endif; ?><?php else: ?><span class="small text-muted"><i class="fa-solid fa-folder-open me-1"></i>Sin documentos cargados.</span><?php endif; ?></div>
                                                                <div class="carga-actions"><a href="home.php?pg=1509&idfic=<?=urlencode($idfic);?>" class="btn btn-outline-success flex-fill"><i class="fa-solid fa-calendar-days me-1"></i>Agenda</a><a href="home.php?pg=1512&idfic=<?=urlencode($idfic);?>" class="btn btn-outline-primary flex-fill"><i class="fa-solid fa-list-check me-1"></i>Planeación</a><a href="home.php?pg=1508&idfic=<?=urlencode($idfic);?>" class="btn btn-outline-secondary flex-fill"><i class="fa-solid fa-file-lines me-1"></i>Guías</a></div>
                                                            </div>
                                                        </details>
                                                    <?php endforeach; ?>
                                                </div>
                                            </details>
                                        </div>
                                    </article>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endforeach; ?>
                <?php if (($totalPaginasCarga ?? 1) > 1): ?>
                    <nav class="mt-3 pt-3 border-top" aria-label="Paginación de responsables">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                            <small class="text-muted">Mostrando <?= (int)$inicioCarga; ?>–<?= (int)$finCarga; ?> de <?= (int)$totalResponsablesCarga; ?> tarjetas</small>
                            <small class="text-muted">Página <?= (int)$paginaCarga; ?> de <?= (int)$totalPaginasCarga; ?></small>
                        </div>
                        <ul class="pagination pagination-sm justify-content-center mb-0">
                            <li class="page-item <?=($paginaCarga <= 1) ? 'disabled':'';?>"><a class="page-link" href="<?=htmlspecialchars($urlPaginaCarga[max(1,$paginaCarga-1)] ?? '#');?>" aria-label="Anterior"><i class="fa-solid fa-chevron-left"></i></a></li>
                            <?php for ($numeroPagina = 1; $numeroPagina <= $totalPaginasCarga; $numeroPagina++): ?>
                                <li class="page-item <?=($numeroPagina === $paginaCarga) ? 'active':'';?>"><a class="page-link" href="<?=htmlspecialchars($urlPaginaCarga[$numeroPagina] ?? '#');?>"><?= (int)$numeroPagina; ?></a></li>
                            <?php endfor; ?>
                            <li class="page-item <?=($paginaCarga >= $totalPaginasCarga) ? 'disabled':'';?>"><a class="page-link" href="<?=htmlspecialchars($urlPaginaCarga[min($totalPaginasCarga,$paginaCarga+1)] ?? '#');?>" aria-label="Siguiente"><i class="fa-solid fa-chevron-right"></i></a></li>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if (!empty($puedeSubirCarga)): ?>
<div class="carga-modal" id="modalSubida" role="dialog" aria-modal="true" aria-labelledby="modalSubidaTitulo">
    <div class="carga-modal-box shadow">
        <div class="bg-success text-white py-3 px-4 d-flex justify-content-between align-items-center" style="border-radius:.8rem .8rem 0 0;">
            <h5 class="mb-0 fw-bold" id="modalSubidaTitulo"><i class="fa-solid fa-cloud-arrow-up me-2"></i>Subir Agenda / Planeación</h5>
            <a href="#!" class="btn-close btn-close-white" aria-label="Cerrar"></a>
        </div>
        <div class="p-4">
            <div class="alert alert-info d-flex align-items-start gap-2 mb-3 shadow-sm"><i class="fa-solid fa-circle-info mt-1"></i><div>Seleccione la plantilla diligenciada. El tipo de archivo se detecta por su contenido.</div></div>
            <form id="frmDocumento" name="frmDocumento" action="home.php?pg=<?= (int)$pg; ?>" method="POST" enctype="multipart/form-data">
                <div class="mb-3"><label for="idfic_carga" class="form-label fw-semibold text-muted"><i class="fa-solid fa-hashtag me-1"></i>Ficha de destino</label><input type="text" name="idfic" id="idfic_carga" class="form-control" value="<?=htmlspecialchars($fichaDestinoCarga ?? '');?>" placeholder="Ejemplo: 2773071A" pattern="[0-9]{6,8}[A-Za-z0-9]{0,2}"><small class="form-text text-muted">Obligatoria cuando el archivo no tenga una fila FICHA.</small></div>
                <div class="mb-3"><label for="EXCEL_DOC" class="form-label fw-semibold text-muted"><i class="fa-solid fa-paperclip me-1"></i>Seleccionar archivo</label><input type="file" name="EXCEL_DOC" id="EXCEL_DOC" class="form-control" accept=".xls,.xlsx,.csv,.ods,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,text/csv" required></div>
                <button type="submit" class="btn btn-success w-100 shadow-sm" name="btnDocumento" value="Subir"><i class="fa-solid fa-upload me-1"></i>Subir archivo</button>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>
