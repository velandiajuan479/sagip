<?php require_once 'controllers/votcvpr.php'; ?>

<div class="conte">
	<?php echo titulo2("<i class='" . $icono . "'></i> Visualizar Propuesta", 2); ?>

	<div class="mx-auto w-100 max-w-screen-xl mt-4 ">
		<?php if ($dcand) { ?>
		<div class="row g-4">
			<?php foreach ($dcand as $c) { ?>
			<div class="col-12 col-sm-6 col-lg-4 col-xl-3">
				<article class="card h-100 border-0 shadow-sm module-card cursor-pointer" data-id="<?= (int)$c['idusu']; ?>">
					<div class="position-relative rounded-top overflow-hidden h-56 bg-gray-100">
						<?php if (!empty($c['fotcan']) && file_exists($c['fotcan'])) { ?>
						<img class="w-100 h-100 object-fit-cover" src="<?= htmlspecialchars($c['fotcan']); ?>" alt="Foto de <?= txlbl($c['nomusu']); ?>">
						<?php } else { ?>
						<img class="w-100 h-100 object-fit-cover" src="img/user.jpg" alt="Foto de <?= txlbl($c['nomusu']); ?>">
						<?php } ?>
						<?php if ($c['noca'] !== '' && $c['noca'] !== null) { ?>
						<span class="badge bg-success position-absolute top-0 end-0 m-2 rounded-pill"><?= txlbl($c['noca']); ?></span>
						<?php } ?>
					</div>
					<div class="card-body">
						<div class="card-title fw-bold text-dark mb-2"><?= txlbl($c['nomusu']); ?></div>
						<ul class="small text-muted d-flex flex-column gap-1 list-unstyled mb-0">
							<?php if (!empty($c['idfic'])) { ?>
							<li><i class="fa-solid fa-hashtag text-success me-1"></i>Ficha <?= (int)$c['idfic']; ?> - <?= txlbl($c['nomfic']); ?></li>
							<?php } ?>
							<?php if (!empty($c['nomjor'])) { ?>
							<li><i class="fa-solid fa-clock text-success me-1"></i><?= txlbl($c['nomjor']); ?></li>
							<?php } ?>
							<?php if (!empty($c['nomcen'])) { ?>
							<li><i class="fa-solid fa-building-columns text-success me-1"></i><?= txlbl($c['nomcen']); ?></li>
							<?php } ?>
						</ul>
					</div>
					<footer class="card-footer bg-white border-0 pt-0 pb-3 px-3">
						<div class="border border-success text-success fw-bold text-center rounded-2 py-2">
							<i class="fa-solid fa-eye me-1"></i> Ver propuesta
						</div>
					</footer>
				</article>
			</div>
			<?php } ?>
		</div>
		<?php } else { ?>
		<div class="card border-0 shadow-sm text-center py-5">
			<div class="card-body">
				<i class="fa-solid fa-file-circle-question text-success fs-1 d-block mb-3"></i>
				<span class="text-muted">No hay propuestas registradas en tu jornada.</span>
			</div>
		</div>
		<?php } ?>
	</div>
</div>

<!-- Modal con la propuesta del candidato -->
<div class="modal fade" id="candModal" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
		<div class="modal-content shadow-lg border-0 rounded-3 overflow-hidden">
			<header class="modal-header bg-success text-white d-flex align-items-center gap-3 border-0 px-4 py-3">
				<div class="d-flex align-items-center gap-3 min-w-0">
					<div id="m_foto" class="rounded-circle overflow-hidden border border-2 border-white shadow w-20 h-20 flex-shrink-0 bg-white"></div>
					<div class="min-w-0">
						<div id="m_nombre" class="fs-5 fw-bold lh-sm"></div>
						<div id="m_meta" class="small d-flex flex-wrap gap-2"></div>
					</div>
				</div>
				<button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal" aria-label="Cerrar"></button>
			</header>
			<div class="modal-body bg-light py-4" id="m_body"></div>
			<footer class="modal-footer bg-white border-0 px-4 py-3">
				<button type="button" class="btn btn-success" data-bs-dismiss="modal"><i class="fa-solid fa-xmark me-1"></i> Cerrar</button>
			</footer>
		</div>
	</div>
</div>

<script>
const CAND = <?= json_encode($candJson, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
const DEF  = <?= json_encode($defJson, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

function eH(t) {
	return String(t === null || t === undefined ? '' : t).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
}

function nb(t) {
	return eH(t).replace(/\n/g, '<br>');
}

function chkVal(v) {
	const on = (v === 'Si');
	const off = (v === 'No');
	const cls = on ? 'bg-success text-white' : (off ? 'bg-danger text-white' : 'bg-light text-muted border');
	const icn = on ? 'fa-circle-check' : (off ? 'fa-circle-xmark' : 'fa-minus');
	const txt = v ? v : 'Pendiente';
	return '<span class="badge ' + cls + ' rounded-pill"><i class="fa-solid ' + icn + ' me-1"></i>' + eH(txt) + '</span>';
}

function vidMime(n) {
	const e = String(n || '').split('.').pop().toLowerCase();
	const m = { mp4: 'video/mp4', m4v: 'video/mp4', webm: 'video/webm', mov: 'video/quicktime', qt: 'video/quicktime', avi: 'video/x-msvideo', ogv: 'video/ogg' };
	return m[e] || 'video/mp4';
}

function vidBlock(c) {
	if (!c.videoOk || !c.video) return '';
	return '<div class="border-top border-light pt-3 mt-4">' +
		'<span class="d-block text-uppercase fw-bold small text-success mb-2"><i class="fa-solid fa-video me-1"></i> Video de la propuesta</span>' +
		'<span class="badge bg-light text-muted text-break rounded-pill mb-2 border" title="' + eH(c.video) + '"><i class="fa-solid fa-file-video text-success me-1"></i> ' + eH(c.video) + '</span>' +
		'<div class="bg-black rounded overflow-hidden">' +
		'<video controls preload="metadata" class="w-100">' +
		'<source src="videos/' + eH(c.video) + '" type="' + vidMime(c.video) + '">' +
		'Su navegador no soporta la visualización de video.' +
		'</video></div></div>';
}

function secTitulo(titulo) {
	return '<header class="card-header bg-success text-white fw-bold">' + eH(titulo) + '</header>';
}

function bloqueChk(titulo, def, vals) {
	if (!def || !def.length) return '';
	let h = '<section class="card border-0 shadow-sm overflow-hidden mb-4">' + secTitulo(titulo) + '<div class="card-body">';
	h += '<ul class="list-unstyled mb-0">';
	def.forEach(function (f) {
		const v = (vals && vals[f.idval]) ? vals[f.idval] : '';
		h += '<li class="d-flex justify-content-between align-items-center gap-2 py-2 border-bottom border-light"><span class="text-uppercase fw-bold small text-success">' + eH(f.nomval) + '</span>' + chkVal(v) + '</li>';
	});
	h += '</ul></div></section>';
	return h;
}

function cuerpoPropuesta(c) {
	let h = '';
	const d3 = DEF[3] || [];
	let n = 0;
	d3.forEach(function (f) {
		if (f.subs && f.subs.length) {
			h += '<section class="card border-0 shadow-sm overflow-hidden mb-4">' + secTitulo(f.nomval) + '<div class="card-body">';
			f.subs.forEach(function (sub) {
				const v = (c.props3 && c.props3[n]) ? c.props3[n] : '';
				n++;
				h += '<div class="mb-3"><span class="d-block text-uppercase fw-bold small text-success mb-1 tracking-wide">' + eH(sub) + '</span><span class="d-block border-start border-success ps-3 lh-lg">' + (v ? nb(v) : '<em class="text-muted">Sin diligenciar</em>') + '</span></div>';
			});
			if (f.idval === 50) h += vidBlock(c);
			h += '</div></section>';
		} else {
			const v = (c.props3 && c.props3[n]) ? c.props3[n] : '';
			n++;
			h += '<section class="card border-0 shadow-sm overflow-hidden mb-4">' + secTitulo(f.nomval) + '<div class="card-body"><p class="mb-0 lh-lg">' + (v ? nb(v) : '<em class="text-muted">Sin diligenciar</em>') + '</p></div></section>';
		}
	});
	h += bloqueChk('Condiciones', DEF[2] || [], c.conds);
	h += bloqueChk('Manifiesto', DEF[4] || [], c.mans);
	return h;
}

let candModal = null;
document.querySelectorAll('.card[data-id]').forEach(function (card) {
	card.addEventListener('click', function () {
		const id = card.getAttribute('data-id');
		const c = CAND.find(function (x) { return String(x.idusu) === id; });
		if (!c) return;

		let foto = '';
		if (c.fotcan) {
			foto = '<img class="w-100 h-100 object-fit-cover" src="' + eH(c.fotcan) + '" alt="Foto de ' + eH(c.nomusu) + '" onerror="this.onerror=null;this.src=\'img/user.jpg\'">';
		} else {
			foto = '<img class="w-100 h-100 object-fit-cover" src="img/user.jpg" alt="Foto de ' + eH(c.nomusu) + '">';
		}
		document.getElementById('m_foto').innerHTML = foto;

		let meta = '';
		if (c.noca) meta += '<span class="fw-semibold"><i class="fa-solid fa-star me-1"></i> Candidato # ' + eH(c.noca) + '</span>';
		if (c.idfic) meta += '<span><i class="fa-solid fa-hashtag me-1"></i> Ficha ' + eH(c.idfic) + ' - ' + eH(c.nomfic) + '</span>';
		if (c.nomjor) meta += '<span><i class="fa-solid fa-clock me-1"></i> ' + eH(c.nomjor) + '</span>';
		if (c.nomcen) meta += '<span><i class="fa-solid fa-building-columns me-1"></i> ' + eH(c.nomcen) + '</span>';

		document.getElementById('m_nombre').textContent = c.nomusu;
		document.getElementById('m_meta').innerHTML = meta;
		document.getElementById('m_body').innerHTML = cuerpoPropuesta(c);

		const dlg = document.querySelector('#candModal .modal-dialog');
		if (dlg) {
			if (c.videoOk) dlg.classList.add('modal-xl');
			else dlg.classList.remove('modal-xl');
		}

		if (!candModal) candModal = new bootstrap.Modal(document.getElementById('candModal'));
		candModal.show();
	});
});

document.addEventListener('DOMContentLoaded', function () {
	const cm = document.getElementById('candModal');
	if (!cm) return;
	cm.addEventListener('hidden.bs.modal', function () {
		cm.querySelectorAll('video').forEach(function (v) { v.pause(); });
		document.getElementById('m_body').innerHTML = '';
	});
});
</script>