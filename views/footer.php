<footer class="bg-sena-900 text-white border-t border-sena-800 mt-12 fooenc" data-purpose="page-footer">
    <div class="w-full px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-8 items-start">
            <div class="md:col-span-5 space-y-3">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 flex items-center justify-center text-white">
                        <a href="https://www.sena.edu.co">
                            <img src="img/senab.png">
                        </a>
                    </div>
                    <div>
                        <h4 class="font-bold text-base leading-tight">SAGI CDA - SENA</h4>
                        <p class="text-xs text-emerald-300"><?php if($val) echo $val[0]['foocof']?> • Regional Cundinamarca</p>
                    </div>
                </div>
                <div class="text-xs text-slate-300 space-y-1 pt-1">
                    <p class="flex items-center gap-2">
                        <i class="fa-solid fa-location-dot"></i>
                        <span class="">Vereda Bojacá, Carrera 11, Sector El Darién, Lote 1 - Chía, Cundinamarca</span>
                    </p>
                    <p class="flex items-center gap-2">
                        <i class="fa-solid fa-phone"></i>
                        <span class="">Teléfono: (601) 8844545</span>
                    </p>
                    <p class="flex items-center gap-2">
                        <i class="fa-solid fa-envelope"></i>
                        <a class="underline hover:text-white" href="mailto:serviciociudadano@sena.edu.co">serviciociudadano@sena.edu.co</a>
                    </p>
                </div>
            </div>

            <div class="md:col-span-4 space-y-2 border-slate-800 md:border-l md:pl-6 text-xs text-slate-300">
                <p class="font-semibold text-white tracking-wide uppercase text-[11px]">Desarrollado por Aprendices SENA</p>
                <p class="leading-relaxed text-slate-300">
                    Fichas 2773071, 2773186, 2996491, 2996494<br>
                    <strong class="text-white">Tecnólogo en Análisis y Desarrollo de Software</strong><br>
                    Ficha 2773096 <strong class="text-white">Desarrollo de Medios Gráficos Visuales</strong>
                </p>
                <div class="pt-2 text-slate-400 text-[11px]">
                    <span class="">Líder de Equipo: <strong class="text-white">Ing. Robinson Rincón</strong></span><br>
                    <span class="">Versión <?php if($val) echo $val[0]['versoft']; ?> | Última actualización: <?php if($val) echo $val[0]['actsoft']?></span>
                </div>
            </div>

            <div class="md:col-span-3 flex flex-col md:items-end justify-between space-y-4">
                <div>
                    <p class="text-xs font-semibold text-slate-200 md:text-right mb-2">Canales Oficiales</p>
                    <div class="flex items-center gap-2">
                        <a aria-label="Facebook" class="w-9 h-9 rounded-full bg-white/10 hover:bg-sena-600 flex items-center justify-center text-white transition" href="https://www.facebook.com/senachiacda/" rel="noopener" target="_blank" title="Facebook">
                            <i class="fa-brands fa-facebook-f"></i>
                        </a>
                        <a aria-label="Instagram" class="w-9 h-9 rounded-full bg-white/10 hover:bg-sena-600 flex items-center justify-center text-white transition" href="https://www.instagram.com/senacundinamarca/" rel="noopener" target="_blank" title="Instagram">
                            <i class="fa-brands fa-instagram"></i>
                        </a>
                        <a aria-label="Blogspot" class="w-9 h-9 rounded-full bg-white/10 hover:bg-sena-600 flex items-center justify-center text-white transition" href="https://cdachia.blogspot.com/" rel="noopener" target="_blank" title="Blog Sena">
                            <i class="fa-solid fa-blog"></i>
                        </a>
                        <a aria-label="Portal Web Institucional" class="w-9 h-9 rounded-full bg-white/10 hover:bg-sena-600 flex items-center justify-center text-white transition" href="https://www.sena.edu.co/es-co/Paginas/default.aspx/" rel="noopener" target="_blank" title="Pagina Principal Sena">
                            <i class="fa-solid fa-house"></i>
                        </a>
                    </div>
                </div>
                <div class="text-[11px] text-slate-400 md:text-right">
                    © <?=date("Y");?> SENA Centro de Desarrollo Agroempresarial Chía.<br>Todos los derechos reservados.
                </div>
            </div>
        </div>
    </div>
</footer>
