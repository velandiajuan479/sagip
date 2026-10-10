<?php
require_once __DIR__ . '/../controllers/admcaps.php';
if (empty($dependencias)) $dependencias = [];
?>

<?php if (!isset($_GET['view']) || $_GET['view'] !== 'reportes'): ?>
<!-- Barra de navegación rápida -->
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <a href="home.php?pg=2401" class="btn btn-outline-success">
            <i class="fas fa-file-circle-check me-1"></i> Ir a Paz y Salvos
        </a>
        <a href="home.php?pg=2403" class="btn btn-outline-secondary ms-2">
            <i class="fas fa-building me-1"></i> Administración Dependencias
        </a>
    </div>
</div>
<?php endif; ?>

<!-- Seleccion de dependencia -->
<div class="card border-success p-3 mb-4">
    <h5 class="text-success mb-3"><i class="fas fa-building me-2"></i>Aprobaci&oacute;n Paz y Salvo por dependencia</h5>
    <div class="row g-2 align-items-end">
        <div class="col-md-8">
            <label for="dep-aprobador" class="form-label">Seleccione dependencia:</label>
            <select class="form-control form-control-sm border-success" id="dep-aprobador">
                <option value="">-- Seleccione una dependencia --</option>
                <?php foreach ($dependencias as $dep): ?>
                    <option value="<?= htmlspecialchars($dep['id_dependencia']) ?>"><?= htmlspecialchars($dep['nombre']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4 d-grid">
            <button type="button" id="btn-cargar-aprobador" style="
                background-color: #00AF00; color: white; border: none; border-radius: 4px;
                font-size: 12px; padding: 6px 12px;">
                <i class="fas fa-users me-1"></i> Cargar contratistas
            </button>
        </div>
    </div>
</div>

<!-- Herramientas -->
<div id="panel-aprobador" style="display:none;">
    <div class="card border-success mb-3">
        <div class="card-body py-2">
            <div class="row g-2 align-items-center">
                <div class="col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">Documento / Nombre</span>
                        <input type="text" id="busq-aprobador" class="form-control"
                               placeholder="Ej. 1002423 o ROBINSON..." autocomplete="off">
                        <button class="btn btn-outline-secondary" type="button" id="btn-limpiar-busq" title="Limpiar">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-2">
                    <select id="limite-aprobador" class="form-select form-select-sm border-success">
                        <option value="50" selected>50 por p&aacute;gina</option>
                        <option value="100">100 por p&aacute;gina</option>
                        <option value="200">200 por p&aacute;gina</option>
                    </select>
                </div>
                <div class="col-md-6 text-md-end">
                    <button type="button" class="btn btn-success btn-sm" id="btn-marcar-todos" title="Marcar todos">
                        <i class="fas fa-check-double me-1"></i>Marcar todos
                    </button>
                    <button type="button" class="btn btn-outline-danger btn-sm" id="btn-desmarcar-todos" title="Desmarcar todos">
                        <i class="fas fa-square-xmark me-1"></i>Desmarcar todos
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="btn-invertir" title="Invertir seleccion">
                        <i class="fas fa-arrows-rotate me-1"></i>Invertir
                    </button>
                    <button type="button" class="btn btn-sm" id="btn-guardar-aprobador"
                            style="background-color:#00AF00;color:#fff;">
                        <i class="fas fa-save me-1"></i>Guardar aprobaci&oacute;n
                    </button>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-12">
                    <span class="badge bg-success" id="resumen-seleccion">Seleccionados: 0</span>
                    <span class="badge bg-danger" id="resumen-motivo">Sin aprobar con motivo: 0</span>
                    <span class="badge bg-secondary" id="resumen-sinmotivo">Sin aprobar sin motivo: 0</span>
                    <span class="ms-2 text-muted" id="resumen-dep"></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Listado -->
    <div class="card border-success mb-3">
        <div class="card-header" style="background-color:#00AF00;color:#fff;">
            <h5 class="mb-0"><i class="fas fa-users me-2"></i>Contratistas pendientes de aprobaci&oacute;n</h5>
        </div>
        <div class="card-body p-0">
            <div id="contenedor-listado">
                <div class="text-center py-4 text-muted">Seleccione una dependencia y presione "Cargar contratistas".</div>
            </div>
        </div>
    </div>

    <!-- Paginacion -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <span class="text-muted" id="info-pagina"></span>
        </div>
        <div class="btn-group btn-group-sm">
            <button type="button" class="btn btn-outline-success" id="btn-prev"><i class="fas fa-chevron-left"></i> Anterior</button>
            <button type="button" class="btn btn-outline-success" id="btn-next">Siguiente <i class="fas fa-chevron-right"></i></button>
        </div>
    </div>
</div>

<script>
(function () {
    'use strict';

    var depSelect = document.getElementById('dep-aprobador');
    var btnCargar = document.getElementById('btn-cargar-aprobador');
    var panel = document.getElementById('panel-aprobador');
    var contenedor = document.getElementById('contenedor-listado');
    var busqInput = document.getElementById('busq-aprobador');
    var limiteSelect = document.getElementById('limite-aprobador');
    var infoPagina = document.getElementById('info-pagina');
    var totalDepSpan = document.getElementById('resumen-dep');

    var estado = {
        depId: null,
        ids: [],             // todos los ids pendientes de la dependencia (pre-marcados)
        marcado: {},         // idusu -> true|false
        obs: {},             // idusu -> observacion
        pagina: 1,
        limite: 50,
        busqueda: '',
        total: 0,
        totalPaginas: 1,
        cargando: false
    };

    function esc(v) {
        return String(v == null ? '' : v).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    function msj(tipo, titulo, texto) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({ icon: tipo, title: titulo, html: texto });
        } else {
            alert(titulo + '\n\n' + String(texto).replace(/<[^>]+>/g, ' '));
        }
    }

    function confirmar(texto, onOk) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Guardar aprobaci\u00f3n',
                html: texto,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#00AF00',
                confirmButtonText: 'S\u00ed, guardar',
                cancelButtonText: 'Cancelar'
            }).then(function (r) { if (r.isConfirmed) onOk(); });
        } else {
            if (confirm(String(texto).replace(/<[^>]+>/g, ' '))) onOk();
        }
    }

    function initEstado(ids) {
        estado.ids = ids.slice();
        estado.marcado = {};
        estado.obs = {};
        for (var i = 0; i < estado.ids.length; i++) {
            estado.marcado[estado.ids[i]] = true;
            estado.obs[estado.ids[i]] = '';
        }
    }

    function cadaId(fn) {
        for (var i = 0; i < estado.ids.length; i++) {
            fn(estado.ids[i]);
        }
    }

    function marcarTodos() { cadaId(function (id) { estado.marcado[id] = true; }); }
    function desmarcarTodos() { cadaId(function (id) { estado.marcado[id] = false; }); }
    function invertirSeleccion() { cadaId(function (id) { estado.marcado[id] = !estado.marcado[id]; }); }

    function esMarcado(id) { return estado.marcado[id] !== false; }

    function obtenerObs(id) { return (estado.obs[id] || ''); }

    function setObs(id, val) { estado.obs[id] = String(val == null ? '' : val); }

    function actualizarResumen() {
        var sel = 0, motivo = 0, sinmotivo = 0;
        for (var i = 0; i < estado.ids.length; i++) {
            var id = estado.ids[i];
            if (esMarcado(id)) { sel++; }
            else if (obtenerObs(id).trim() !== '') { motivo++; }
            else { sinmotivo++; }
        }
        document.getElementById('resumen-seleccion').textContent = 'Seleccionados: ' + sel;
        document.getElementById('resumen-motivo').textContent = 'Sin aprobar con motivo: ' + motivo;
        document.getElementById('resumen-sinmotivo').textContent = 'Sin aprobar sin motivo: ' + sinmotivo;
        totalDepSpan.textContent = 'Total pendientes en la dependencia: ' + estado.ids.length;
    }

    function renderFilas(rows) {
        if (!rows.length) {
            contenedor.innerHTML = '<div class="text-center py-4 text-muted">' +
                'No se encontraron contratistas pendientes por aprobar.' +
                '</div>';
            return;
        }

        var html = '<div class="table-responsive"><table class="table table-sm table-hover mb-0 align-middle">' +
            '<thead class="table-success"><tr>' +
            '<th style="width:50px;">Aprobar</th>' +
            '<th>Documento</th>' +
            '<th>Nombre</th>' +
            '<th>Perfil</th>' +
            '<th>Observaci&oacute;n (motivo si no se aprueba)</th>' +
            '</tr></thead><tbody>';

        for (var i = 0; i < rows.length; i++) {
            var fila = rows[i];
            var id = fila.idusu;
            var marcado = esMarcado(id);
            var obs = obtenerObs(id);
            html += '<tr id="fila-' + id + '" class="' + (marcado ? '' : 'table-danger') + '">' +
                '<td class="text-center">' +
                '<input type="checkbox" class="form-check-input chk-aprobacion" data-id="' + id + '"' +
                ' style="width:18px;height:18px;background-color:#00AF00;border-color:#00AF00;cursor:pointer;" ' +
                (marcado ? 'checked' : '') + '>' +
                '</td>' +
                '<td>' + esc(fila.ndocusu) + '</td>' +
                '<td><strong>' + esc(fila.nombre) + '</strong></td>' +
                '<td><span class="badge ' + (marcado ? 'bg-success' : 'bg-danger') + '">' + esc(fila.perfil || 'Contratista') + '</span></td>' +
                '<td><input type="text" class="form-control form-control-sm obs-aprobacion" data-id="' + id + '"' +
                ' value="' + esc(obs) + '" placeholder="' + (marcado ? 'Opcional' : 'Motivo (genera alerta al usuario)') + '"></td>' +
                '</tr>';
        }
        html += '</tbody></table></div>';
        contenedor.innerHTML = html;
    }

    function actualizarUnaFila(id) {
        var tr = document.getElementById('fila-' + id);
        if (!tr) return;
        var chk = tr.querySelector('.chk-aprobacion');
        var chkOtro = tr.querySelector('.obs-aprobacion');
        var marcado = esMarcado(id);

        if (chk) chk.checked = marcado;
        if (chkOtro) {
            chkOtro.value = obtenerObs(id);
            chkOtro.placeholder = marcado ? 'Opcional' : 'Motivo (genera alerta al usuario)';
        }
        tr.classList.toggle('table-danger', !marcado);
        var badge = tr.querySelector('.badge');
        if (badge) badge.className = 'badge ' + (marcado ? 'bg-success' : 'bg-danger');
    }

    function cargarLista(mostrarCarga) {
        if (!estado.depId || estado.cargando) return;
        estado.cargando = true;

        if (mostrarCarga) {
            contenedor.innerHTML = '<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x text-muted"></i>' +
                '<p class="mt-2">Buscando contratistas...</p></div>';
        }

        var url = 'controllers/admcaps.php?action=get_contratistas' +
            '&id_dependencia=' + encodeURIComponent(estado.depId) +
            '&busqueda=' + encodeURIComponent(estado.busqueda) +
            '&limite=' + estado.limite +
            '&pagina=' + estado.pagina;

        fetch(url)
            .then(function (res) { return res.ok ? res.json() : Promise.reject(new Error('HTTP ' + res.status)); })
            .then(function (data) {
                estado.cargando = false;
                if (!data.success) throw new Error(data.error || 'Error al cargar');

                estado.total = data.total;
                estado.totalPaginas = data.totalPaginas;
                renderFilas(data.data);
                actualizarResumen();
                actualizarPaginacion();
            })
            .catch(function (err) {
                estado.cargando = false;
                contenedor.innerHTML = '<div class="alert alert-danger m-2 mb-0">Error al cargar los datos: ' + esc(err.message) + '</div>';
            });
    }

    function cargarIdsYLista() {
        if (!estado.depId) return;
        fetch('controllers/admcaps.php?action=get_ids&id_dependencia=' + encodeURIComponent(estado.depId))
            .then(function (res) { return res.ok ? res.json() : Promise.reject(new Error('HTTP ' + res.status)); })
            .then(function (data) {
                if (!data.success) throw new Error(data.error || 'Error al cargar ids');
                initEstado(data.ids);
                estado.pagina = 1;
                actualizarResumen();
                cargarLista(true);
            })
            .catch(function (err) {
                msj('error', 'Error', 'Error al cargar la lista: ' + esc(err.message));
            });
    }

    function actualizarPaginacion() {
        var desde = (estado.pagina - 1) * estado.limite + 1;
        var hasta = Math.min(estado.pagina * estado.limite, estado.total);
        var permitido = estado.total > 0;
        if (!permitido) { desde = 0; hasta = 0; }
        infoPagina.textContent = 'Mostrando ' + desde + ' - ' + hasta + ' de ' + estado.total +
            ' (p\u00e1gina ' + estado.pagina + ' de ' + estado.totalPaginas + ')';
        document.getElementById('btn-prev').disabled = !permitido || estado.pagina <= 1;
        document.getElementById('btn-next').disabled = !permitido || estado.pagina >= estado.totalPaginas;
    }

    function irPagina(p) {
        if (p < 1 || p > estado.totalPaginas) return;
        estado.pagina = p;
        cargarLista(true);
    }

    function guardar() {
        if (!estado.depId) return;
        var aprobados = [];
        var rechazados = [];
        var sinCambio = 0;

        for (var i = 0; i < estado.ids.length; i++) {
            var id = estado.ids[i];
            if (esMarcado(id)) {
                aprobados.push(id);
            } else if (obtenerObs(id).trim() !== '') {
                rechazados.push({ idusu: id, observacion: obtenerObs(id).trim() });
            } else {
                sinCambio++;
            }
        }

        var confirmText =
            'Se <strong>aprobar&aacute;n ' + aprobados.length + '</strong> contratistas.<br>' +
            'Se <strong>rechazar&aacute;n ' + rechazados.length + '</strong> contratistas con observaci&oacute;n (se les notificar&aacute;).<br>' +
            (sinCambio > 0 ? 'Se <strong>dejar&aacute;n pendientes sin notificar ' + sinCambio + '</strong> contratistas.<br>' : '');

        confirmar(confirmText, function () {
            fetch('controllers/admcaps.php?action=guardar_masivo', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    id_dependencia: estado.depId,
                    aprobados: aprobados,
                    rechazados: rechazados
                })
            })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (!data.success) throw new Error(data.error || 'Error al guardar');
                msj('success', 'Aprobaci\u00f3n guardada',
                    'Se aprobaron <strong>' + data.aprobados + '</strong> contratistas y se rechazaron con motivo <strong>' +
                    data.rechazados + '</strong>.');
                cargarIdsYLista();
            })
            .catch(function (err) {
                msj('error', 'Error', 'No se pudo guardar: ' + esc(err.message));
            });
        });
    }

    btnCargar.addEventListener('click', function () {
        estado.depId = depSelect.value;
        estado.busqueda = '';
        busqInput.value = '';
        if (!estado.depId) {
            msj('warning', 'Verifique', 'Debe seleccionar una dependencia.');
            return;
        }
        panel.style.display = 'block';
        cargarIdsYLista();
    });

    depSelect.addEventListener('change', function () {
        estado.depId = depSelect.value;
        estado.busqueda = '';
        busqInput.value = '';
        if (!estado.depId) {
            panel.style.display = 'none';
            return;
        }
        panel.style.display = 'block';
        cargarIdsYLista();
    });

    busqInput.addEventListener('input', function () {
        var valor = busqInput.value.trim();
        if (valor === estado.busqueda) return;
        estado.busqueda = valor;
        if (estado.busqueda === '') {
            estado.pagina = 1;
            cargarLista(true);
        }
    });

    var tiempoBusq = null;
    busqInput.addEventListener('keyup', function () {
        clearTimeout(tiempoBusq);
        var valor = busqInput.value.trim();
        if (valor === '') return;
        tiempoBusq = setTimeout(function () {
            estado.busqueda = valor;
            estado.pagina = 1;
            cargarLista(true);
        }, 500);
    });

    document.getElementById('btn-limpiar-busq').addEventListener('click', function () {
        busqInput.value = '';
        estado.busqueda = '';
        estado.pagina = 1;
        cargarLista(true);
    });

    limiteSelect.addEventListener('change', function () {
        estado.limite = parseInt(limiteSelect.value, 10) || 50;
        estado.pagina = 1;
        cargarLista(true);
    });

    document.getElementById('btn-prev').addEventListener('click', function () { irPagina(estado.pagina - 1); });
    document.getElementById('btn-next').addEventListener('click', function () { irPagina(estado.pagina + 1); });

    document.getElementById('btn-marcar-todos').addEventListener('click', function () {
        marcarTodos();
        refrescarFilasVisibles();
    });

    document.getElementById('btn-desmarcar-todos').addEventListener('click', function () {
        desmarcarTodos();
        refrescarFilasVisibles();
    });

    document.getElementById('btn-invertir').addEventListener('click', function () {
        invertirSeleccion();
        refrescarFilasVisibles();
    });

    document.getElementById('btn-guardar-aprobador').addEventListener('click', guardar);

    function refrescarFilasVisibles() {
        var chks = contenedor.querySelectorAll('.chk-aprobacion');
        for (var i = 0; i < chks.length; i++) {
            actualizarUnaFila(parseInt(chks[i].getAttribute('data-id'), 10));
        }
        actualizarResumen();
    }

    contenedor.addEventListener('change', function (e) {
        if (e.target && e.target.classList.contains('chk-aprobacion')) {
            var id = parseInt(e.target.getAttribute('data-id'), 10);
            estado.marcado[id] = e.target.checked;
            actualizarUnaFila(id);
            actualizarResumen();
        }
    });

    contenedor.addEventListener('input', function (e) {
        if (e.target && e.target.classList.contains('obs-aprobacion')) {
            var id = parseInt(e.target.getAttribute('data-id'), 10);
            setObs(id, e.target.value);
            actualizarResumen();
        }
    });
})();
</script>

<style>
    #contenedor-listado .chk-aprobacion:checked {
        background-color: #00AF00 !important;
        border-color: #00AF00 !important;
    }
</style>