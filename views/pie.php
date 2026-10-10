<!-- BEGIN: InstitutionalFooter -->
<footer class="footer-sena bg-sena-900 text-white border-t border-sena-800" data-purpose="page-footer">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" style="max-width: 1400px; margin: 0 auto;">
        <div class="row g-4 align-items-start m-0">
            <!-- Left identity and address -->
            <div class="col-12 col-md-5 space-y-3">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="footer-brand-icon">
                        <i class="fa-solid fa-shield-halved text-emerald-400"></i>
                    </div>
                    <div>
                        <h4 class="footer-brand-title m-0">SAGI CDA - SENA</h4>
                        <p class="footer-brand-sub">Centro de Desarrollo Agroempresarial • Regional Cundinamarca</p>
                    </div>
                </div>
                <div class="text-xs text-slate-300 space-y-1 pt-1">
                    <p class="footer-contact-item">
                        <i class="fa-solid fa-location-dot text-emerald-400"></i>
                        <span>Vereda Bojacá, Carrera 11, Sector El Darién, Lote 1 - Chía, Cundinamarca</span>
                    </p>
                    <p class="footer-contact-item">
                        <i class="fa-solid fa-phone text-emerald-400"></i>
                        <span>Teléfono: (601) 8844545</span>
                    </p>
                    <p class="footer-contact-item">
                        <i class="fa-solid fa-envelope text-emerald-400"></i>
                        <a class="underline hover:text-white" href="mailto:serviciociudadano@sena.edu.co">serviciociudadano@sena.edu.co</a>
                    </p>
                </div>
            </div>

            <!-- Middle: Credits & Academic info -->
            <div class="col-12 col-md-4 footer-middle-col space-y-2">
                <p class="footer-credits-heading">Desarrollado por Aprendices SENA</p>
                <p class="footer-credits-text">
                    Fichas 2773071, 2773186, 2996491, 2996494<br>
                    <strong>Tecnólogo en Análisis y Desarrollo de Software</strong><br>
                    Ficha 2773096 <strong>Desarrollo de Medios Gráficos Visuales</strong>
                </p>
                <div class="footer-version-text pt-1">
                    <span>Líder de Equipo: <strong>Ing. Robinson Rincón</strong></span><br>
                    <span>Versión <?php if(isset($val) && $val) echo htmlspecialchars($val[0]['versoft']); else echo '2.0'; ?> | Última actualización: <?php if(isset($val) && $val) echo htmlspecialchars($val[0]['actsoft']); else echo 'Octubre 2025'; ?></span>
                </div>
            </div>

            <!-- Right: Social and Quick Links -->
            <div class="col-12 col-md-3 d-flex flex-column align-items-md-end justify-content-between space-y-4">
                <div class="text-md-end w-100">
                    <p class="footer-social-heading text-md-end">Canales Oficiales</p>
                    <div class="footer-redes justify-content-md-end">
                        <a aria-label="Facebook" class="btn-red-social" href="https://www.facebook.com/senachiacda/" rel="noopener" target="_blank" title="Facebook">
                            <i class="fa-brands fa-facebook-f"></i>
                        </a>
                        <a aria-label="Instagram" class="btn-red-social" href="https://www.instagram.com/senacundinamarca/" rel="noopener" target="_blank" title="Instagram">
                            <i class="fa-brands fa-instagram"></i>
                        </a>
                        <a aria-label="Blog Sena" class="btn-red-social" href="https://cdachia.blogspot.com/" rel="noopener" target="_blank" title="Blog SENA">
                            <i class="fa-solid fa-blog"></i>
                        </a>
                        <a aria-label="Portal Web Institucional" class="btn-red-social" href="https://www.sena.edu.co/es-co/Paginas/default.aspx" rel="noopener" target="_blank" title="Portal Principal SENA">
                            <i class="fa-solid fa-globe"></i>
                        </a>
                    </div>
                </div>
                <div class="footer-copy text-md-end w-100 pt-2">
                    © 2025 - <?= date('Y'); ?> SENA Centro de Desarrollo Agroempresarial.<br>Todos los derechos reservados.
                </div>
            </div>
        </div>
    </div>
</footer>
<!-- END: InstitutionalFooter -->