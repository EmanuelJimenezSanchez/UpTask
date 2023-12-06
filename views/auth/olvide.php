<div class="contenedor olvide">
  <?php include_once __DIR__ . '/../templates/nombre-sitio.php'; ?>

  <div class="contenedor-sm">
    <p class="descripcion-pagina">Recupera tu Acceso UpTask</p>

    <?php include_once __DIR__ . '/../templates/alertas.php'; ?>

    <form class="formulario" method="POST" action="/olvide" novalidate>
      <div class="campo">
        <label for="email">Email</label>
        <input type="email" name="email" id="email" placeholder="Tu Email">
      </div>

      <input type="submit" value="Enviar Instrucciones" class="boton boton-verde">

      <div class="acciones">
        <a href="/">¿Ya tienes una cuenta? Iniciar Sesión</a>
        <a href="/crear">¿Aún no tienes cuenta? Crea una nueva</a>
      </div>
  </div> <!--.contenedor-sm-->
</div>