<div class="contenedor reestablecer">
  <?php include_once __DIR__ . '/../templates/nombre-sitio.php'; ?>

  <div class="contenedor-sm">
    <p class="descripcion-pagina">Coloca tu nueva contraseña</p>

    <?php include_once __DIR__ . '/../templates/alertas.php'; ?>

    <?php if ($mostrar) : ?>

      <form class="formulario" method="POST">

        <div class="campo">
          <label for="password">Contraseña</label>
          <input type="password" name="password" id="password" placeholder="Tu Contraseña">
        </div>

        <input type="submit" value="Guardar Contraseña" class="boton boton-verde">

      </form>

    <?php endif; ?>

    <div class="acciones">
      <a href="/crear">¿Aún no tienes cuenta? Crea una nueva</a>
      <a href="/">¿Ya tienes una cuenta? Iniciar Sesión</a>
    </div>
  </div> <!--.contenedor-sm-->
</div>