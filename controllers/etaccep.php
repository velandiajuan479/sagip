<?php
require_once 'models/etamcep.php';

$mcep = new Mcep();
// La vista se accede por GET desde Gestión de Instructores (pg=2404&idusu=...&idficha=...)
$pg      = $_GET['pg']      ?? 2409;   // lo usan los enlaces de las píldoras del toolbar
$idficha = $_GET['idficha'] ?? null;
$idusu   = $_GET['idusu']   ?? null;

// Filtro de píldoras por GET: el filtrado es server-side para que la vista
// no lleve JavaScript (regla de buenas prácticas del instructor)
$filtro = $_GET['filtro'] ?? 'todas';
$etiquetasFiltro = [
    'todas'      => 'Todas',
    'aprobadas'  => 'Bitácoras aprobadas',
    'pendientes' => 'Bitácoras pendientes',
    'rechazadas' => 'Bitácoras rechazadas',
];
if (!isset($etiquetasFiltro[$filtro])) {
    $filtro = 'todas';
}
$filtroEtiqueta = $etiquetasFiltro[$filtro];

$instructorSel   = [];
$fichaInfo       = [];
$aprendices      = [];
$totalAprobadas  = 0;
$totalRechazadas = 0;
$totalPendientes = 0;
$totalAprendices = 0;

// Conteo de aprendices (filas) por estado — alimenta las píldoras del toolbar
$filAprobadas  = 0;
$filRechazadas = 0;
$filPendientes = 0;

if (!empty($idficha) && !empty($idusu)) {
    $instructorSel = $mcep->getInstructorById($idusu) ?: [];
    $fichaInfo     = $mcep->getFichaInfo($idficha) ?: [];
    $aprendices    = $mcep->getAprendicesResumenPorFicha($idficha);
    $totalAprendices = count($aprendices);

    foreach ($aprendices as &$ap) {
        $totalAprobadas  += (int)$ap['aprobado'];
        $totalRechazadas += (int)$ap['rechazado'];
        $totalPendientes += (int)$ap['pendiente'];

        $filAprobadas  += ((int)$ap['aprobado'] > 0)  ? 1 : 0;
        $filRechazadas += ((int)$ap['rechazado'] > 0) ? 1 : 0;
        $filPendientes += ((int)$ap['pendiente'] > 0) ? 1 : 0;

        // Cálculo de bitácoras faltantes (SENA requiere 12 bitácoras en total)
        $entregadas = !empty($ap['bitacoras_entregadas'])
            ? array_map('intval', explode(',', $ap['bitacoras_entregadas']))
            : [];
        $ap['numeros_faltantes'] = array_values(array_diff(range(1, 12), $entregadas));
    }
    unset($ap);
}

// Filas a mostrar según la píldora activa. Los KPIs y los conteos de las
// píldoras siempre reflejan el total de la ficha (globales, no filtrados).
$filasVista = $aprendices;
if ($filtro === 'aprobadas') {
    $filasVista = array_filter($aprendices, function ($ap) { return (int)$ap['aprobado'] > 0; });
} elseif ($filtro === 'pendientes') {
    $filasVista = array_filter($aprendices, function ($ap) { return (int)$ap['pendiente'] > 0; });
} elseif ($filtro === 'rechazadas') {
    $filasVista = array_filter($aprendices, function ($ap) { return (int)$ap['rechazado'] > 0; });
}
$filasVista = array_values($filasVista);

// Función para formatear las bitácoras faltantes en formato legible / rangos
if (!function_exists('formatearRangoBitacoras')) {
    function formatearRangoBitacoras(array $numeros): string {
        if (empty($numeros)) {
            return '<span class="badge bg-success"><i class="fa fa-check-circle"></i> Al día (0)</span>';
        }
        if (count($numeros) === 12) {
            return '<span class="badge bg-danger">Todas (1 a 12)</span>';
        }

        sort($numeros);
        $rangos = [];
        $inicio = $numeros[0];
        $prev   = $numeros[0];

        for ($i = 1; $i < count($numeros); $i++) {
            if ($numeros[$i] == $prev + 1) {
                $prev = $numeros[$i];
            } else {
                $rangos[] = ($inicio == $prev) ? "#$inicio" : "#$inicio-#$prev";
                $inicio   = $numeros[$i];
                $prev     = $numeros[$i];
            }
        }
        $rangos[] = ($inicio == $prev) ? "$inicio" : "#$inicio-#$prev";

        return '<span class="badge bg-secondary mb-1">' . count($numeros) . ' faltantes</span><br><small class="text-danger fw-bold">' . implode(', ', $rangos) . '</small>';
    }
}
