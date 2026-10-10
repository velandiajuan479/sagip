<?php require_once 'controllers/votccdt.php'; ?>

<?php echo titulo2("<i class='" . $icono . "'></i> Candidatos - Votación Representante", 2); ?>

<?php if ($mensaje): ?>
<div class="alert alert-<?= $tipoMensaje === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show" role="alert">
    <?= $mensaje ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Buscar Candidato por Documento -->
<div class="card mb-4">
    <div class="card-header bg-success text-dark">
        <i class="fas fa-search"></i> Registrar Candidato Representante
    </div>
    <div class="card-body">
        <form method="GET" action="home.php" class="mb-0">
            <input type="hidden" name="pg" value="<?= $pg ?>">
            <input type="hidden" name="fidcen" value="<?= $fidcen ?>">
            <?php if ($fidfic): ?><input type="hidden" name="fidfic" value="<?= $fidfic ?>"><?php endif; ?>
            <?php if ($fidval): ?><input type="hidden" name="fidval" value="<?= $fidval ?>"><?php endif; ?>
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="ndocusu_buscar" class="form-label">Buscar por Número de Documento</label>
                    <input type="number" class="form-control" id="ndocusu_buscar" name="ndocusu_buscar" 
                           value="<?= htmlspecialchars($_GET['ndocusu_buscar'] ?? '') ?>" 
                           placeholder="Ingrese número de documento" required>
                </div>
                <div class="col-md-2">
                    <button type="submit" name="opera" value="Buscar" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i> Buscar
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Resultado de búsqueda: encontrado -->
<?php if ($encontrado): ?>
<div class="card mb-4 border-success">
    <div class="card-header bg-success text-white">
        <i class="fas fa-user-check"></i> Usuario encontrado — Complete los datos del candidato
    </div>
    <div class="card-body">
        <div class="row mb-4">
            <div class="col-md-2 text-center">
                <?php if (!empty($encontrado['fotcan'])): ?>
                    <img src="<?= htmlspecialchars($encontrado['fotcan']) ?>" alt="Foto" class="rounded-circle" style="width:100px; height:100px; object-fit:cover;">
                <?php else: ?>
                    <img src="image/usuario.png" alt="Sin foto" class="rounded-circle" style="width:100px; height:100px; object-fit:cover;">
                <?php endif; ?>
            </div>
            <div class="col-md-5">
                <h5 class="text-success"><?= htmlspecialchars(strtoupper($encontrado['nomusu'])) ?></h5>
                <p class="mb-1"><strong>Documento:</strong> <?= htmlspecialchars($encontrado['ndocusu']) ?></p>
                <p class="mb-1"><strong>Email:</strong> <?= htmlspecialchars($encontrado['emausu']) ?></p>
                <p class="mb-1"><strong>Teléfono:</strong> <?= htmlspecialchars($encontrado['telcan'] ?? '—') ?></p>
            </div>
            <div class="col-md-5">
                <p class="mb-1"><strong>Ficha actual:</strong> <?= $encontrado['idfic'] ? htmlspecialchars($encontrado['idfic'] . ' - ' . $encontrado['nomfic'] . ' (' . $encontrado['nomval'] . ')') : '<em>Sin ficha asignada</em>' ?></p>
            </div>
        </div>

        <form method="POST" action="home.php?pg=<?= $pg ?>" enctype="multipart/form-data">
            <input type="hidden" name="pg" value="<?= $pg ?>">
            <input type="hidden" name="ndocusu" value="<?= htmlspecialchars($encontrado['ndocusu']) ?>">
            <input type="hidden" name="fidcen" value="<?= $fidcen ?>">

            <div class="row g-3">
                <div class="col-md-2">
                    <label class="form-label">N° Candidato *</label>
                    <input type="number" class="form-control" name="noca" min="1" max="999" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Estado</label>
                    <select name="actusu" class="form-select">
                        <option value="1">Activo</option>
                        <option value="0">Inactivo</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Foto (opcional)</label>
                    <input type="file" class="form-control" name="arch" accept="image/*">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" name="opera" value="Registrar" class="btn btn-success w-100">
                        <i class="fas fa-plus"></i> Registrar
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- Edición de candidato existente -->
<?php if ($idusu && $datOne): ?>
<div class="card mb-4 border-warning">
    <div class="card-header bg-warning text-dark">
        <i class="fas fa-edit"></i> Editar Candidato — <?= htmlspecialchars(strtoupper($datOne[0]['nomusu'])) ?>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-2 text-center">
                <?php if (!empty($datOne[0]['fotcan'])): ?>
                    <img src="<?= htmlspecialchars($datOne[0]['fotcan']) ?>" alt="Foto" class="rounded-circle" style="width:100px; height:100px; object-fit:cover;">
                <?php else: ?>
                    <img src="image/usuario.png" alt="Sin foto" class="rounded-circle" style="width:100px; height:100px; object-fit:cover;">
                <?php endif; ?>
            </div>
            <div class="col-md-5">
                <p class="mb-1"><strong>Documento:</strong> <?= htmlspecialchars($datOne[0]['ndocusu']) ?></p>
                <p class="mb-1"><strong>Email:</strong> <?= htmlspecialchars($datOne[0]['emausu']) ?></p>
                <p class="mb-1"><strong>Teléfono:</strong> <?= htmlspecialchars($datOne[0]['telcan'] ?? '—') ?></p>
            </div>
            <div class="col-md-5">
                <p class="mb-1"><strong>Ficha actual:</strong> <?= $datOne[0]['idfic'] ? htmlspecialchars($datOne[0]['idfic'] . ' - ' . $datOne[0]['nomfic'] . ' (' . $datOne[0]['nomval'] . ')') : '<em>Sin ficha</em>' ?></p>
            </div>
        </div>

        <form method="POST" action="home.php?pg=<?= $pg ?>" enctype="multipart/form-data">
            <input type="hidden" name="pg" value="<?= $pg ?>">
            <input type="hidden" name="idusu" value="<?= $idusu ?>">
            <input type="hidden" name="fidcen" value="<?= $fidcen ?>">
            <input type="hidden" name="fidfic" value="<?= $fidfic ?>">

            <div class="row g-3">
                <div class="col-md-2">
                    <label class="form-label">N° Candidato *</label>
                    <input type="number" class="form-control" name="noca" min="1" max="999" required
                           value="<?= htmlspecialchars($datOne[0]['noca']) ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Estado</label>
                    <select name="actusu" class="form-select">
                        <option value="1" <?= $datOne[0]['actusu'] == 1 ? 'selected' : '' ?>>Activo</option>
                        <option value="0" <?= $datOne[0]['actusu'] == 0 ? 'selected' : '' ?>>Inactivo</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Foto (opcional)</label>
                    <input type="file" class="form-control" name="arch" accept="image/*">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" name="opera" value="Actualizar" class="btn btn-warning w-100">
                        <i class="fas fa-save"></i> Actualizar
                    </button>
                </div>
            </div>
            <div class="mt-2">
                <a href="home.php?pg=<?= $pg ?>&fidcen=<?= $fidcen ?>&fidfic=<?= $fidfic ?>&fidval=<?= $fidval ?>" class="btn btn-sm btn-secondary">
                    <i class="fas fa-times"></i> Cancelar edición
                </a>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <i class="fas fa-filter"></i> Filtros de Búsqueda
    </div>
    <div class="card-body">
        <form method="GET" action="home.php">
            <input type="hidden" name="pg" value="<?= $pg ?>">
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="fidcen" class="form-label">Centro</label>
                    <select name="fidcen" id="fidcen" class="form-select" onchange="this.form.submit();">
                        <?php if ($dcentros): foreach ($dcentros as $dc): ?>
                            <option value="<?= $dc['idcen'] ?>" <?= $fidcen == $dc['idcen'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($dc['nomcen']) ?>
                            </option>
                        <?php endforeach; endif; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="fidfic" class="form-label">Ficha</label>
                    <select name="fidfic" id="fidfic" class="form-select" onchange="this.form.submit();">
                        <option value="">-- Todas las fichas --</option>
                        <?php if ($dfichas): foreach ($dfichas as $df): ?>
                            <option value="<?= $df['idfic'] ?>" <?= $fidfic == $df['idfic'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($df['idfic'] . ' - ' . $df['nomfic'] . ' (' . $df['nomval'] . ')') ?>
                            </option>
                        <?php endforeach; endif; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="fidval" class="form-label">Jornada</label>
                    <select name="fidval" id="fidval" class="form-select" onchange="this.form.submit();">
                        <?php if ($djornadas): foreach ($djornadas as $dj): ?>
                            <option value="<?= $dj['idval'] ?>" <?= $fidval == $dj['idval'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($dj['nomval']) ?>
                            </option>
                        <?php endforeach; endif; ?>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <a href="home.php?pg=<?= $pg ?>" class="btn btn-secondary">Limpiar filtros</a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Listado de Candidatos -->
<div class="card">
    <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
        <span><i class="fas fa-list"></i> Listado de Candidatos <?= $fidfic ? 'de la Ficha ' . htmlspecialchars($fidfic) : '(' . ($dat ? count($dat) : 0) . ')' ?></span>
        <span class="badge bg-light text-dark"><?= $dat ? count($dat) : 0 ?> candidatos</span>
    </div>
    <div class="card-body">
        <?php if ($dat && count($dat) > 0): ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Foto</th>
                            <th>N° Candidato</th>
                            <th>Documento</th>
                            <th>Nombre</th>
                            <th>Ficha</th>
                            <th>Jornada</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($dat as $i => $d): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td>
                                <?php if (!empty($d['fotcan'])): ?>
                                    <img src="<?= htmlspecialchars($d['fotcan']) ?>" alt="Foto" style="width: 50px; height: 50px; object-fit: cover; border-radius: 50%;">
                                <?php else: ?>
                                    <img src="image/usuario.png" alt="Sin foto" style="width: 50px; height: 50px; object-fit: cover; border-radius: 50%;">
                                <?php endif; ?>
                            </td>
                            <td><strong class="text-primary">#<?= htmlspecialchars($d['noca']) ?></strong></td>
                            <td><?= htmlspecialchars($d['ndocusu']) ?></td>
                            <td><?= htmlspecialchars(strtoupper($d['nomusu'])) ?></td>
                            <td><?= htmlspecialchars($d['idfic'] . ' - ' . $d['nomfic']) ?></td>
                            <td><span class="badge bg-info"><?= htmlspecialchars($d['nomval']) ?></span></td>
                            <td>
                                <?php if ($d['actusu'] == 1): ?>
                                    <span class="badge bg-success">Activo</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Inactivo</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="home.php?pg=<?= $pg ?>&idusu=<?= $d['idusu'] ?>&fidcen=<?= $fidcen ?>&fidfic=<?= $fidfic ?>&fidval=<?= $fidval ?>" 
                                       class="btn btn-outline-primary" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-outline-danger" 
                                            onclick="confirmarEliminar(<?= $d['idusu'] ?>, '<?= htmlspecialchars($d['nomusu']) ?>')"
                                            title="Eliminar candidatura">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No hay candidatos registrados</h5>
                <p class="text-muted">Busque un aprendiz por número de documento para registrar como candidato</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function confirmarEliminar(idusu, nombre) {
    Swal.fire({
        title: '¿Eliminar candidatura?',
        text: "Está a punto de retirar la postulación de " + nombre,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#dc3545'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'home.php?pg=<?= $pg ?>&opera=Eliminar&idusu=' + idusu + '&fidcen=<?= $fidcen ?>&fidfic=<?= $fidfic ?>&fidval=<?= $fidval ?>';
        }
    });
}
</script>
