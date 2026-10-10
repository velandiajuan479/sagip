<form id="olv1" action="controllers/colv.php" method="POST" style="display: none;">
	<h3>Recuperar Contraseña</h3>
	<div>
		<label for="inp-email-olv"><i class="fa-solid fa-envelope me-1"></i> Correo Electrónico Registrado</label>
		<input type="email" id="inp-email-olv" name="emausu" placeholder="ejemplo@sena.edu.co" required>
	</div>
	<div class="submit">
		<button type="submit" class="dark">
			<i class="fa-solid fa-paper-plane me-1"></i> Enviar Enlace
		</button>
	</div>
	<div class="mt-2 text-center">
		<a href="#" onclick="ingreso(1); return false;"><i class="fa-solid fa-arrow-left me-1"></i> Volver a Ingreso</a>
	</div>
</form>