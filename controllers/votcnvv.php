<?php
require_once ('models/votmnvv.php');

$mnvv = new Mnvv();
$idusu = isset($_POST['idusu']) ? $_POST['idusu'] : NULL;
$actusu = isset($_POST['actusu']) ? $_POST['actusu'] : NULL;
$idfic = isset($_POST['idfic']) ? $_POST['idfic'] : NULL;
$fidfic = isset($_POST['fidfic']) && $_POST['fidfic'] !== '' ? $_POST['fidfic'] : (isset($_GET['fidfic']) && $_GET['fidfic'] !== '' ? $_GET['fidfic'] : NULL);
$ope = isset($_REQUEST['ope']) ? $_REQUEST['ope'] : NULL;

// Si fidfic viene con texto (por ejemplo "321456 - ADSO ..."), extraer el numero de ficha
if ($fidfic && preg_match('/^(\d+)/', trim($fidfic), $matches)) {
    $fidfic = $matches[1];
}

if($ope == "save" || $ope == "edit") {
    $mnvv->setIdfic($idfic);
    $mnvv->setActusu($actusu);
    $mnvv->setIdusu($idusu);
    
    if($ope == "edit") {
        $mnvv->edit();
    } else {
        $mnvv->save();
    }
}

if($ope == "act" && $idusu && $actusu) {
    $mnvv->setActusu($actusu);
    $mnvv->editAct();
}

// se obtienen las fichas
$fichas = $mnvv->getFichas();

// Correccion de codificacion (por ejemplo MaÃ±ana -> Mañana)
foreach ($fichas as &$f) {
    if (isset($f['nomval'])) {
        if (strpos($f['nomval'], 'Ã') !== false) {
            $f['nomval'] = @utf8_decode($f['nomval']);
        }
        $f['nomval'] = str_replace(['MaÃ±ana', 'maÃ±ana'], ['Mañana', 'mañana'], $f['nomval']);
    }
}
unset($f);

// se obtiene los aprendices y las estadisticas de acuerdo a la ficha seleccionada
$dat = [];
$candidatos = [];
$aprendices = [];
$gaf = ['total_personas' => 0, 'votaron' => 0, 'no_votaron' => 0];

if($fidfic) {
    $candidatos = $mnvv->getCandidatosVoceroPorFicha($fidfic);
    $aprendices = $mnvv->getSoloAprendicesPorFicha($fidfic);

    foreach ($candidatos as &$c) {
        if (isset($c['nomval'])) {
            if (strpos($c['nomval'], 'Ã') !== false) $c['nomval'] = @utf8_decode($c['nomval']);
            $c['nomval'] = str_replace(['MaÃ±ana', 'maÃ±ana'], ['Mañana', 'mañana'], $c['nomval']);
        }
        if (isset($c['nomusu']) && strpos($c['nomusu'], 'Ã') !== false) {
            $c['nomusu'] = @utf8_decode($c['nomusu']);
        }
    }
    unset($c);

    foreach ($aprendices as &$a) {
        if (isset($a['nomval'])) {
            if (strpos($a['nomval'], 'Ã') !== false) $a['nomval'] = @utf8_decode($a['nomval']);
            $a['nomval'] = str_replace(['MaÃ±ana', 'maÃ±ana'], ['Mañana', 'mañana'], $a['nomval']);
        }
        if (isset($a['nomusu']) && strpos($a['nomusu'], 'Ã') !== false) {
            $a['nomusu'] = @utf8_decode($a['nomusu']);
        }
    }
    unset($a);

    $dat = array_merge($candidatos, $aprendices);
    $resGaf = $mnvv->getEstadisticasPorFicha($fidfic);
    if ($resGaf && is_array($resGaf)) {
        $gaf = [
            'total_personas' => (int)($resGaf['total_personas'] ?? 0),
            'votaron'        => (int)($resGaf['votaron'] ?? 0),
            'no_votaron'     => (int)($resGaf['no_votaron'] ?? 0)
        ];
    }
}

$gafGlobal = $mnvv->getEstadisticasGlobalesNoVotantes();
if (!$gafGlobal || !is_array($gafGlobal)) {
    $gafGlobal = ['total_personas' => 0, 'votaron' => 0, 'no_votaron' => 0];
} else {
    $gafGlobal['total_personas'] = (int)($gafGlobal['total_personas'] ?? 0);
    $gafGlobal['votaron']        = (int)($gafGlobal['votaron'] ?? 0);
    $gafGlobal['no_votaron']     = (int)($gafGlobal['no_votaron'] ?? 0);
}
?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var searchInput = document.getElementById('nvvv-buscar-input');
    var fichaHidden = document.getElementById('fidfic-hidden');
    var btnLimpiar  = document.getElementById('nvvv-btn-limpiar');
    var dropdown    = document.getElementById('nvvv-dropdown-fichas');
    var form        = document.getElementById('form-filtro-ficha');

    if (!searchInput) return;

    var listaFichas = [
        <?php foreach ($fichas as $ficha):
            $jornadaFic = (isset($ficha['nomval']) && strpos($ficha['nomval'], 'Ã') !== false) ? utf8_decode($ficha['nomval']) : ($ficha['nomval'] ?? '');
            $jornadaFic = str_replace(['MaÃ±ana', 'maÃ±ana'], ['Mañana', 'mañana'], $jornadaFic);
        ?>
        { id: "<?= $ficha['idfic']; ?>", nombre: <?= json_encode($ficha['nomfic']); ?>, jornada: <?= json_encode($jornadaFic); ?> },
        <?php endforeach; ?>
    ];

    var activeIndex = -1;

    function normalizar(t) {
        return (t || '').toString().toLowerCase()
            .normalize('NFD').replace(/[\u0300-\u036f]/g, '').trim();
    }

    function badgeJornada(jornada) {
        var j = normalizar(jornada);
        if (j.includes('manana') || j.includes('ma\u00f1ana')) {
            return '<span class="badge bg-warning text-dark px-2 py-1"><i class="fas fa-sun me-1"></i>' + jornada + '</span>';
        } else if (j.includes('tarde')) {
            return '<span class="badge bg-light text-dark border px-2 py-1"><i class="fas fa-cloud-sun me-1"></i>' + jornada + '</span>';
        } else if (j.includes('noche') || j.includes('nocturna')) {
            return '<span class="badge bg-dark text-white px-2 py-1"><i class="fas fa-moon me-1"></i>' + jornada + '</span>';
        } else if (j.includes('fin de semana') || j.includes('sabado') || j.includes('s\u00e1bado')) {
            return '<span class="badge bg-secondary text-white px-2 py-1"><i class="fas fa-calendar-week me-1"></i>' + jornada + '</span>';
        } else if (j.includes('virtual')) {
            return '<span class="badge bg-primary text-white px-2 py-1"><i class="fas fa-laptop me-1"></i>' + jornada + '</span>';
        }
        return '<span class="badge bg-light text-dark border px-2 py-1">' + jornada + '</span>';
    }

    function renderDropdown(lista) {
        dropdown.innerHTML = '';
        activeIndex = -1;
        if (lista.length === 0) {
            dropdown.innerHTML = '<div class="p-3 text-muted text-center small"><i class="fas fa-exclamation-circle text-warning me-1"></i> No se encontraron fichas</div>';
            dropdown.style.display = 'block';
            return;
        }
        lista.forEach(function (item) {
            var div = document.createElement('div');
            div.className = 'p-2 px-3 border-bottom d-flex justify-content-between align-items-center nvvv-item-ficha';
            div.style.cursor = 'pointer';
            div.style.transition = 'background-color 0.15s ease';
            div.innerHTML =
                '<div class="text-truncate me-2"><strong class="text-success">' + item.id + '</strong> &ndash; <span class="text-dark">' + item.nombre + '</span></div>' +
                badgeJornada(item.jornada);

            div.addEventListener('mouseenter', function () {
                this.style.backgroundColor = '#e8f5e9';
            });
            div.addEventListener('mouseleave', function () {
                if (!this.classList.contains('nvvv-active')) {
                    this.style.backgroundColor = '';
                }
            });
            div.addEventListener('click', function () { seleccionar(item); });
            dropdown.appendChild(div);
        });
        dropdown.style.display = 'block';
    }

    function seleccionar(item) {
        searchInput.value = item.id + ' - ' + item.nombre + ' (' + item.jornada + ')';
        fichaHidden.value = item.id;
        dropdown.style.display = 'none';
        form.submit();
    }

    function filtrar() {
        var query = normalizar(searchInput.value);
        if (!query) { renderDropdown(listaFichas); return; }
        var esNumero = /^\d+$/.test(query);
        var res = listaFichas.filter(function (item) {
            var idN  = normalizar(item.id);
            var nomN = normalizar(item.nombre);
            return esNumero ? idN.indexOf(query) === 0 : (nomN.indexOf(query) !== -1 || idN.indexOf(query) === 0);
        });
        renderDropdown(res);
    }

    searchInput.addEventListener('input', filtrar);
    searchInput.addEventListener('focus', filtrar);

    searchInput.addEventListener('keydown', function (e) {
        var items = dropdown.querySelectorAll('.nvvv-item-ficha');
        if (dropdown.style.display === 'none' || items.length === 0) return;
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            activeIndex = (activeIndex + 1) % items.length;
            items.forEach(function (el, i) {
                var isActive = (i === activeIndex);
                el.classList.toggle('nvvv-active', isActive);
                el.style.backgroundColor = isActive ? '#e8f5e9' : '';
            });
            items[activeIndex].scrollIntoView({ block: 'nearest' });
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            activeIndex = (activeIndex - 1 + items.length) % items.length;
            items.forEach(function (el, i) {
                var isActive = (i === activeIndex);
                el.classList.toggle('nvvv-active', isActive);
                el.style.backgroundColor = isActive ? '#e8f5e9' : '';
            });
            items[activeIndex].scrollIntoView({ block: 'nearest' });
        } else if (e.key === 'Enter') {
            e.preventDefault();
            if (activeIndex >= 0 && items[activeIndex]) { items[activeIndex].click(); }
            else if (items.length > 0) { items[0].click(); }
        } else if (e.key === 'Escape') {
            dropdown.style.display = 'none';
        }
    });

    document.addEventListener('click', function (e) {
        if (!searchInput.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.style.display = 'none';
        }
    });

    if (btnLimpiar) {
        btnLimpiar.addEventListener('click', function () {
            searchInput.value = '';
            fichaHidden.value = '';
            dropdown.style.display = 'none';
            searchInput.focus();
        });
    }

    /* Mostrar ficha ya seleccionada al cargar */
    <?php if ($fidfic): ?>
    (function () {
        var actual = listaFichas.find(function (f) { return f.id == "<?= $fidfic; ?>"; });
        if (actual) searchInput.value = actual.id + ' - ' + actual.nombre + ' (' + actual.jornada + ')';
    })();
    <?php endif; ?>
});
</script>