<div class="conte">
    <?php echo titulo2("<i class='".$icono."'></i> Carga masiva de aspirantes", 1); ?> 

    <!-- Tarjetas de Resumen / KPIs -->
    <div class="row my-4 g-4">
        <!-- Total Registros en Archivo -->
        <div class="col-12 col-md-6">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #3e4a3d;">Registros en Archivo</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #0b1c30;"><?php echo !empty($filas) ? count($filas) : 0; ?></span>
                            <span style="font-size: 13px; color: #3e4a3d;">aspirantes</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box" style="background-color: #b5dcfe; color: #3c627f;">
                        <i class="fa-solid fa-file-excel fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 11px; color: #3c627f;">
                        <i class="fa-solid fa-cloud-arrow-up" style="font-size: 12px;"></i> Listo para validar
                    </span>
                    <span style="font-size: 11px; color: #3e4a3d;">Formato XLSX / CSV</span>
                </div>
            </div>
        </div>

        <!-- Estado de Guía / Plantilla -->
        <div class="col-12 col-md-6">
            <div class="kpi-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.6px; color: #006b29;">Estructura de Columnas</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="fw-bold" style="font-size: 32px; line-height: 1; color: #006b29;">6</span>
                            <span style="font-size: 13px; color: rgba(0, 107, 41, 0.8);">campos clave</span>
                        </div>
                    </div>
                    <div class="kpi-icon-box" style="background-color: #7afd88; color: #006b24;">
                        <i class="fa-solid fa-table-cells fs-4"></i>
                    </div>
                </div>
                <div class="kpi-footer-strip">
                    <span class="fw-semibold d-inline-flex align-items-center gap-2" style="font-size: 11px; color: #006b29;">
                        <span class="pulse-dot"></span> Plantilla Oficial (100% OK)
                    </span>
                    <a href="https://senacda.com/sagipr/EXCEL/fcapre.xlsx" target="_blank" rel="noopener" class="text-decoration-none fw-semibold" style="font-size: 11px; color: #006b29;">
                        <i class="fa-solid fa-download me-1"></i> Descargar plantilla
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Formulario de Inserción / Selección de Archivo -->
    <div class="inser">
        <form id="frmins" class="filter-toolbar-container" action="home.php?pg=<?php echo htmlspecialchars($pg ?? 'carga_aspirantes', ENT_QUOTES, 'UTF-8'); ?>&amp;accion=procesar" method="POST" enctype="multipart/form-data">
            <h2><i class="fa-solid fa-file-circle-plus"></i> Carga masiva de aspirantes</h2>
            <p class="text-muted small mb-3">Seleccione un archivo Excel o CSV para revisar sus registros antes de guardarlos.</p>
            
            <?php if (!empty($mensaje)): ?>
                <div class="alert <?php echo isset($exito) && $exito ? 'alert-success' : 'alert-warning'; ?> py-2 mb-3" role="status">
                    <strong><?php echo isset($exito) && $exito ? 'Operación completada:' : 'Atención:'; ?></strong>
                    <?php echo $mensaje; ?>
                </div>
            <?php endif; ?>

            <div class="row align-items-end">
                <div class="form-group col-md-9">
                    <label for="excel_file" class="fw-semibold mb-1">Archivo (.xlsx, .xls o .csv):</label>
                    <input type="file" class="form-control" name="excel_file" id="excel_file" accept=".xlsx,.xls,.csv" required>
                </div>
                <div class="form-group col-md-3">
                    <input type="hidden" name="pg" value="<?php echo htmlspecialchars($pg ?? 'carga_aspirantes', ENT_QUOTES, 'UTF-8'); ?>">
                    <button type="submit" class="btn btn-success w-100 shadow-sm">
                        <i class="fa-solid fa-check-to-slot me-1"></i> Cargar y validar
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Toolbar informativo de columnas esperadas -->
    <div class="filter-toolbar-container mt-4">
        <div class="d-flex align-items-center gap-3">
            <div class="kpi-icon-box" style="background-color: #b5dcfe; color: #3c627f; width: 40px; height: 40px; border-radius: 8px; display: flex; align-items-center; justify-content-center;">
                <i class="fa-solid fa-circle-info"></i>
            </div>
            <div>
                <h6 class="fw-bold mb-1" style="color: #0b1c30;">Orden esperado de columnas</h6>
                <p class="mb-0 text-muted" style="font-size: 0.85rem;">La primera fila se interpreta como encabezado. Las columnas siguientes deben ser: <strong>tipo de documento, documento, codigo de ficha, poblacion, organizacion y empresa</strong>.</p>
            </div>
        </div>
    </div>

    <!-- Vista Previa de Registros (Si existen en sesión) -->
    <?php if (!empty($filas)): ?>
        <div class="card shadow-sm border-0 mt-4" style="border-radius: 12px; overflow: hidden;">
            <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold text-dark"><i class="fa-solid fa-table me-2 text-primary"></i> Vista previa</h5>
                <span class="badge bg-primary px-3 py-2" style="font-size: 0.85rem;">
                    Se encontraron <?php echo count($filas); ?> registros (Mostrando primeros 20)
                </span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0 align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th scope="col" class="py-3 px-3">#</th>
                                <th scope="col" class="py-3">Tipo de documento</th>
                                <th scope="col" class="py-3">Documento</th>
                                <th scope="col" class="py-3">Ficha</th>
                                <th scope="col" class="py-3">Población</th>
                                <th scope="col" class="py-3">Organización</th>
                                <th scope="col" class="py-3 px-3">Empresa</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($filas, 0, 20) as $indice => $fila): ?>
                                <tr>
                                    <td class="px-3 fw-bold text-muted"><?php echo $indice + 1; ?></td>
                                    <td><?php echo htmlspecialchars($fila[1] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><span class="fw-semibold text-dark"><?php echo htmlspecialchars($fila[2] ?? '', ENT_QUOTES, 'UTF-8'); ?></span></td>
                                    <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($fila[3] ?? '', ENT_QUOTES, 'UTF-8'); ?></span></td>
                                    <td><?php echo htmlspecialchars($fila[4] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($fila[5] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td class="px-3"><?php echo htmlspecialchars($fila[6] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-light py-3 px-4 d-flex justify-content-between align-items-center">
                <span class="text-muted small">Verifique que los datos correspondan antes de confirmar la inserción definitiva a la base de datos.</span>
                <form action="home.php?pg=<?php echo htmlspecialchars($pg ?? 'carga_aspirantes', ENT_QUOTES, 'UTF-8'); ?>&amp;accion=guardar" method="POST" class="m-0">
                    <button type="submit" class="btn btn-success shadow-sm px-4">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Confirmar e insertar registros
                    </button>
                </form>
            </div>
        </div>
    <?php endif; ?>

</div>