<?php

require_once 'models/votmecv.php';

$mecv = new Mecv();
$idusu = isset($_REQUEST['idusu']) ? $_REQUEST['idusu']:NULL;
$ndocusu = isset($_POST['ndocusu']) ? $_POST['ndocusu']:NULL;
$nomusu = isset($_POST['nomusu']) ? $_POST['nomusu']:NULL;
$idper = isset($_POST['idper']) ? $_POST['idper']:NULL;
$idfic = isset($_REQUEST['idfic']) ? $_REQUEST['idfic']:NULL;
$pasusu = isset($_POST['pasusu']) ? $_POST['pasusu']:NULL;
$idcen = isset($_POST['idcen']) ? $_POST['idcen']:NULL;
$actusu = isset($_POST['actusu']) ? $_POST['actusu']:NULL;
$emausu = isset($_POST['emausu']) ? $_POST['emausu']:NULL;
$telcan = isset($_POST['telcan']) ? $_POST['telcan']:NULL;
$noca = isset($_POST['noca']) ? $_POST['noca']:NULL;
$fotcan = isset($_POST['fotcan']) ? $_POST['fotcan']:NULL;


$idficfil = isset($_REQUEST['idficfil']) ? $_REQUEST['idficfil']:NULL;
$nodocfil = isset($_REQUEST['nodocfil']) ? $_REQUEST['nodocfil']:80546098;

$foto = isset($_FILES['foto']['name']) ? $_FILES['foto']['name']:NULL;

$opera = isset($_REQUEST['opera']) ? $_REQUEST['opera']:NULL;
if($foto){
    $fotcan = opti($_FILES['foto'], $idusu, 'fcan', "");
}

$ips = isset($_POST['inico']) ? $_POST['inico']:NULL;
$fps = isset($_POST['finco']) ? $_POST['finco']:NULL;

$datOne = NULL;

if($pg<>1324){
	$pg = 1117;
}else{
	$idusu = $_SESSION["idusu"];
}


$mecv->setIdusu($idusu);
//Insertar
if($opera=="save"){
	$mecv->setNdocusu($ndocusu);
	$mecv->setNomusu($nomusu);
	$mecv->setIdper($idper);
	$mecv->setPasusu($pasusu);
	$mecv->setEmausu($emausu);
	$mecv->setIdcen($idcen);
	$mecv->setActusu($actusu);
	$mecv->setFotcan($fotcan);
	$mecv->setTelcan($telcan);
	$mecv->setNoca($noca);
	if(!$idusu){
		$mecv->save();
	}else{
		$mecv->edi();
	}
	
}
//Actualizar
if($opera=="edit"){
	$datOne=$mecv->getOne();
}
if($opera=="CamCon"){
	if($ips AND $fps){
		// $apr = $musu->selApren();
		// foreach ($apr as $ap) {
		// 	$pas = $ips.$ap['ndocusu'].$fps;
		// 	//echo $pas."<br>";
			$mecv->updPasc($ips,$fps);
		// }
	}
	$idusu="";
}
//Actualizacion
if($opera=="InUP"){
    // se elimina los perfiles secundarios antes de todo
    if($idusu) $mecv->delUxP();
    
    // solo se asignan nuevos perfiles si se ajusta
    if($idper) {
        foreach($idper AS $idx){
            if($idx){
                $mecv->setIdper($idx);
                $mecv->insUxP();
            }
        }
    }
    // se actualiza el perfil principal si es diferente
    $perfilPrincipal = isset($_POST['perfil_principal']) ? $_POST['perfil_principal'] : null;
    if($perfilPrincipal) {
        $mecv->setIdper($perfilPrincipal);
        $mecv->edi(); // se actualiza el perfil principal en la tabla usuario
    }
}

// Eliminar candidato vocero 
if ($opera == "remove_vocero" && $idusu) {
    $res = $mecv->eliminarCandidatoVocero($idusu);
    $idficRet = isset($_REQUEST['idficfil']) ? htmlspecialchars($_REQUEST['idficfil'], ENT_QUOTES, 'UTF-8') : '';
    if ($res['success']) {
        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Candidato retirado',
                text: 'El aprendiz ha vuelto a su perfil regular',
                confirmButtonText: 'Aceptar'
            }).then(() => {
                window.location.href = 'home.php?pg=" . $pg . "&idficfil=" . $idficRet . "';
            });
        </script>";
    } else {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: '" . addslashes($res['message']) . "',
                confirmButtonText: 'Aceptar'
            }).then(() => {
                window.location.href = 'home.php?pg=" . $pg . "&idficfil=" . $idficRet . "';
            });
        </script>";
    }
    exit;
}

// Asignar candidato vocero
if ($opera == "make_vocero" && $idusu && !empty($_REQUEST['idficfil'])) {
    $res = $mecv->asignarCandidatoVocero($idusu, $_REQUEST['idficfil']);
    $idficRet = htmlspecialchars($_REQUEST['idficfil'], ENT_QUOTES, 'UTF-8');
    if ($res['success']) {
        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Candidato asignado',
                text: 'Se ha postulado al aprendiz como candidato a vocero exitosamente',
                confirmButtonText: 'Aceptar'
            }).then(() => {
                window.location.href = 'home.php?pg=" . $pg . "&idficfil=" . $idficRet . "';
            });
        </script>";
    } else {
        echo "<script>
            Swal.fire({
                icon: 'warning',
                title: 'Atención',
                text: '" . addslashes($res['message']) . "',
                confirmButtonText: 'Aceptar'
            }).then(() => {
                window.location.href = 'home.php?pg=" . $pg . "&idficfil=" . $idficRet . "';
            });
        </script>";
    }
    exit;
}

if($opera=="InUF"){
	if($idfic AND $idusu){
		$mecv->ediUxF();
		$dtuxf = $mecv->getFicUsuSec($idusu,$idfic);
		if(!$dtuxf) $mecv->insUxF($idusu, $idfic);
	}
}
//Eliminar
if($opera=="Eliminar"&& $idusu)$mecv->del();

if($opera=="duxf"){
	if($idfic AND $idusu) $mecvu->delUxPF($idusu,$idfic);
}

//mostrar aprendices de la ficha seleccionada
$dat = NULL;
if($idficfil) {
    if($pg == 1117) { 
        $dat = $mecv->getAprendicesPorFicha($idficfil);
    } else {
        $dat = $mecv->getAll($idficfil);
    }
}
$dce = $mecv->getCentro();
$dfi = $mecv->getFicha();
$dpe = $mecv->getPerfil();
$tfichas = $mecv->getTficha();

// Metricas para el dashboard de vota sena
$totalFichas = $mecv->contarFichas();
$totalAprendices = $mecv->contarAprendices();
$totalCandidatosVocero = $mecv->contarTotalCandidatosVocero();
$conteoCandidatos = 0;
$conteoAprendicesFicha = 0;
if($idficfil) {
    $conteoCandidatos = $mecv->contarCandidatosVocero($idficfil);
    $conteoAprendicesFicha = ($dat) ? count($dat) : 0;
}

if($idusu){
	$datOne = $mecv->getOne();
}


function modmulsel($id, $nom,$pg){
	$musu = new musu();
	$datmd = $musu->getMod();
	
	$html = '';
	$html .= '<div class="modal" id="muxp'.$id.'" tabindex="-1" role="dialog">';
		$html .= '<div class="modal-dialog">';
			$html .= '<form action="home.php?pg='.$pg.'" method="POST">';
				$html .= '<div class="modal-content">';
					$html .= '<div class="modal-header">';
						$html .= '<h3>Perfiles de Usuario</h3>';
						$html .= '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>';
					$html .= '</div>';
					$html .= '<div class="modal-body">';
						$html .= '<h5>Usuario: '.$nom.'</h5>';
						$html .= '<div class="row">';
						//
							if($datmd){ foreach($datmd AS $dmd){
								$datpu = $musu->getPefus($dmd['idmod']);//por cada modulo se obtiene los perfiles que estan activos  
								$datup = $musu->getUsPe($dmd['idmod'],$id);// por cada modulo obtiene el perfil actual del usuario
								$html .= '<div class="form-group col-md-6">';
									$html .= '<label for="idper">'.$dmd['nommod'].'</label>';
									$html .= '<select name="idper[]" id="idper" class="form-select">';
										$html .= '<option value="0">Sin perfil</option>';
										if($datpu){ foreach($datpu AS $dp){
											$html .= '<option value="'.$dp['idper'].'" ';
											if(($datup AND $datup[0]['idper']==$dp['idper'])) $html .= 'selected';
											$html .= '>'.$dp['nomper'].'</option>';
										}}
									$html .= '</select>';
								$html .= '</div>';
							}}
						$html .= '</div>';
					$html .= '</div>';
					$html .= '<div class="modal-footer">';
						$html .= '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>';
						$html .= '<input type="submit" class="btn btn-primary" value="Guardar">';
						$html .= '<input type="hidden" name="idusu" value="'.$id.'">';
						$html .= '<input type="hidden" name="opera" value="InUP">';
					$html .= '</div>';
				$html .= '</div>';
			$html .= '</form>';
		$html .= '</div>';
	$html .= '</div>';
	return $html;
}
function modsimsel($id, $nom,$pg){
	$musu = new musu();
	$datpu = $musu->getFicha();
	$datfr = $musu->getFicUsu($id);
	$html = '';
	$html .= '<div class="modal" id="muxf'.$id.'" tabindex="-1" role="dialog">';
		$html .= '<div class="modal-dialog">';
			$html .= '<form action="home.php?pg='.$pg.'" method="POST">';
				$html .= '<div class="modal-content">';
					$html .= '<div class="modal-header">';
						$html .= '<h3>Fichas de Usuario</h3>';
						$html .= '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>';
					$html .= '</div>';
					$html .= '<div class="modal-body">';
						$html .= '<h5>Usuario: '.$nom.'</h5>';
						$html .= '<div class="row">';
								$html .= '<div class="form-group col-md-12">';
									$html .= '<label for="idfic">Ficha</label>';
									$html .= '<select name="idfic" id="idfic" class="form-select">';
										$html .= '<option value="0">Seleccione Ficha</option>';
										if($datpu){ foreach($datpu AS $dp){
											$html .= '<option value="'.$dp['idfic'].'" ';
											$html .= '>'.$dp['idfic']." - ".$dp['nomfic'].'</option>';
										}}
									$html .= '</select>';
								$html .= '</div>';
								$html .= '<label>Fichas Registradas</label><br>';
								$html .= '<div class="form-group col-md-12">';
										if($datfr){ foreach($datfr AS $dp){
											$html .= '<div class="row">';
												$html .= '<div class="form-group col-md-9">';
													$html .= $dp['idfic']." - ".$dp['nomfic'];
												$html .= '</div>';
												$html .= '<div class="form-group col-md-3" style="text-align: center;">';
													if($dp['actfic']==1) $ctx = "#ff0"; else $ctx = "#333"; 
													$html .= '<i class="fa-solid fa-star" style="color: '.$ctx.';text-shadow: 0px 0px 4px #000"></i>';
													$html .= '&nbsp;&nbsp;&nbsp;';
													$html .= '<a href="home.php?pg='.$pg.'&idfic='.$dp['idfic'].'&idusu='.$id.'&opera=duxf">';
														$html .= '<i class="fa-solid fa-trash"></i>';
													$html .= '</a>';
												$html .= '</div>';
											$html .= '</div>';
										}}else{
											$html .= '<label>Sin ficha registrada</label>';
										}
									
								$html .= '</div>';
						$html .= '</div>';
					$html .= '</div>';
					$html .= '<div class="modal-footer">';
						$html .= '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>';
						$html .= '<input type="submit" class="btn btn-primary" value="Guardar">';
						$html .= '<input type="hidden" name="idusu" value="'.$id.'">';
						$html .= '<input type="hidden" name="opera" value="InUF">';
					$html .= '</div>';
				$html .= '</div>';
			$html .= '</form>';
		$html .= '</div>';
	$html .= '</div>';
	return $html;
}

?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('buscar-ficha-input');
    const fichaHidden = document.getElementById('idficfil-hidden');
    const btnLimpiar = document.getElementById('btn-limpiar-busqueda');
    const dropdownResultados = document.getElementById('dropdown-fichas-resultados');
    const formFicha = document.getElementById('formFicha');

    if (!searchInput || !fichaHidden) return;

    // Lista de fichas inyectada desde PHP
    const listaFichas = [
        <?php foreach ($dfi ?? [] as $de):
            $jornadaLimpia = (isset($de['nomval']) && strpos($de['nomval'], 'Ã') !== false) ? @utf8_decode($de['nomval']) : ($de['nomval'] ?? '');
            $jornadaLimpia = str_replace(['MaÃ±ana', 'maÃ±ana'], ['Mañana', 'mañana'], $jornadaLimpia);
        ?>
        { numero: <?= json_encode($de['idfic']); ?>, nombre: <?= json_encode($de['nomfic']); ?>, jornada: <?= json_encode($jornadaLimpia); ?> },
        <?php endforeach; ?>
    ];

    let activeIndex = -1;

    // Función para normalizar cadenas (quitar tildes y pasar a minúsculas)
    function normalizar(texto) {
        return (texto || '')
            .toString()
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .trim();
    }

    // Badge de jornada con icono
    function badgeJornada(jornada) {
        const j = normalizar(jornada);
        let badgeClass = 'bg-light text-dark border';
        let iconoJornada = 'fa-clock';
        if (j.includes('manana')) {
            badgeClass = 'bg-warning-subtle text-dark border border-warning';
            iconoJornada = 'fa-sun';
        } else if (j.includes('tarde')) {
            badgeClass = 'bg-info-subtle text-dark border border-info';
            iconoJornada = 'fa-cloud-sun';
        } else if (j.includes('virtual')) {
            badgeClass = 'bg-primary-subtle text-primary border border-primary';
            iconoJornada = 'fa-laptop';
        } else if (j.includes('noche') || j.includes('nocturna')) {
            badgeClass = 'bg-dark text-white';
            iconoJornada = 'fa-moon';
        } else if (j.includes('fin de semana') || j.includes('sabado')) {
            badgeClass = 'bg-secondary text-white';
            iconoJornada = 'fa-calendar-week';
        }
        return '<span class="badge ' + badgeClass + ' text-nowrap px-2 py-1"><i class="fas ' + iconoJornada + ' me-1"></i>' + (jornada || 'Jornada N/A') + '</span>';
    }

    // Coincidencia inteligente por frase completa o por cada palabra
    function coincideFicha(query, numero, nombre, jornada, textoCompleto) {
        if (!query) return true;
        if (numero.includes(query) || nombre.includes(query) || jornada.includes(query) || textoCompleto.includes(query)) {
            return true;
        }
        const palabras = query.split(/\s+/).filter(p => p.length > 0);
        if (palabras.length > 1) {
            return palabras.every(p =>
                numero.includes(p) || nombre.includes(p) || jornada.includes(p) || textoCompleto.includes(p)
            );
        }
        return false;
    }

    function filtrarOpciones() {
        const query = normalizar(searchInput.value);
        activeIndex = -1;

        if (!dropdownResultados) return;
        dropdownResultados.innerHTML = '';

        const coincidencias = listaFichas.filter(function (item) {
            const numero = normalizar(item.numero);
            const nombre = normalizar(item.nombre);
            const jornada = normalizar(item.jornada);
            const textoCompleto = normalizar(item.numero + ' ' + item.nombre);
            return coincideFicha(query, numero, nombre, jornada, textoCompleto);
        });

        if (query.length > 0 && coincidencias.length > 0) {
            coincidencias.forEach(function (item, index) {
                const itemBtn = document.createElement('button');
                itemBtn.type = 'button';
                itemBtn.className = 'list-group-item list-group-item-action d-flex justify-content-between align-items-center py-2 px-3 border-bottom';
                itemBtn.dataset.index = index;
                itemBtn.innerHTML =
                    '<div class="text-truncate me-2"><strong class="text-success">' + item.numero + '</strong> - <span class="text-dark">' + item.nombre + '</span></div>' +
                    badgeJornada(item.jornada);
                itemBtn.addEventListener('click', function () { seleccionarFicha(item); });
                dropdownResultados.appendChild(itemBtn);
            });
            dropdownResultados.style.display = 'block';
        } else if (query.length > 0) {
            dropdownResultados.innerHTML =
                '<div class="p-3 text-muted text-center small bg-white"><i class="fas fa-circle-exclamation text-warning me-1"></i> No se encontraron fichas para "<strong>' + searchInput.value + '</strong>"</div>';
            dropdownResultados.style.display = 'block';
        } else {
            dropdownResultados.style.display = 'none';
        }
    }

    function seleccionarFicha(item) {
        searchInput.value = item.numero + ' - ' + item.nombre + ' (' + item.jornada + ')';
        fichaHidden.value = item.numero;
        if (dropdownResultados) dropdownResultados.style.display = 'none';
        formFicha.submit();
    }

    searchInput.addEventListener('input', filtrarOpciones);

    searchInput.addEventListener('focus', function () {
        if (searchInput.value.trim().length > 0) {
            filtrarOpciones();
        }
    });

    searchInput.addEventListener('keydown', function (e) {
        if (!dropdownResultados || dropdownResultados.style.display === 'none') {
            if (e.key === 'Enter') {
                e.preventDefault();
                if (listaFichas.length > 0) seleccionarFicha(listaFichas[0]);
            }
            return;
        }

        const items = dropdownResultados.querySelectorAll('.list-group-item');
        if (items.length === 0) return;

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            activeIndex = (activeIndex + 1) % items.length;
            actualizarItemActivo(items);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            activeIndex = (activeIndex - 1 + items.length) % items.length;
            actualizarItemActivo(items);
        } else if (e.key === 'Enter') {
            e.preventDefault();
            if (activeIndex >= 0 && items[activeIndex]) {
                items[activeIndex].click();
            } else if (items.length > 0) {
                items[0].click();
            }
        } else if (e.key === 'Escape') {
            dropdownResultados.style.display = 'none';
        }
    });

    function actualizarItemActivo(items) {
        items.forEach((item, idx) => {
            if (idx === activeIndex) {
                item.classList.add('active');
                item.scrollIntoView({ block: 'nearest' });
            } else {
                item.classList.remove('active');
            }
        });
    }

    document.addEventListener('click', function (e) {
        if (!searchInput.contains(e.target) && (!dropdownResultados || !dropdownResultados.contains(e.target))) {
            if (dropdownResultados) dropdownResultados.style.display = 'none';
        }
    });

    if (btnLimpiar) {
        btnLimpiar.addEventListener('click', function () {
            searchInput.value = '';
            fichaHidden.value = '';
            if (dropdownResultados) dropdownResultados.style.display = 'none';
            searchInput.focus();
        });
    }

    // Mostrar ficha ya seleccionada al cargar
    const fichaInicial = fichaHidden.value;
    if (fichaInicial) {
        const actual = listaFichas.find(function (f) { return String(f.numero) === String(fichaInicial); });
        if (actual) searchInput.value = actual.numero + ' - ' + actual.nombre + ' (' + actual.jornada + ')';
    }
});

function confirmarVocero(idusu, nombre) {
    Swal.fire({
        title: '¿Confirmar Candidato?',
        html: '¿Deseas postular a <strong>' + nombre + '</strong> como candidato a vocero de esta ficha?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#198754',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="fas fa-check me-1"></i> Sí, postular',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `home.php?pg=<?= $pg; ?>&idusu=${idusu}&opera=make_vocero&idficfil=<?= isset($_REQUEST['idficfil']) ? urlencode($_REQUEST['idficfil']) : ''; ?>`;
        }
    });
}

function confirmarEliminarVocero(idusu, nombre) {
    Swal.fire({
        title: '¿Quitar Candidato?',
        html: '¿Estás seguro de retirar a <strong>' + nombre + '</strong> como candidato a vocero?<br><small class="text-muted">Volverá a su estado regular de aprendiz.</small>',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="fas fa-user-minus me-1"></i> Sí, quitar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `home.php?pg=<?= $pg; ?>&idusu=${idusu}&opera=remove_vocero&idficfil=<?= isset($_REQUEST['idficfil']) ? urlencode($_REQUEST['idficfil']) : ''; ?>`;
        }
    });
}
</script>