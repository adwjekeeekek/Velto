<?php
require_once __DIR__ . '/../Classes/Usuario.php';
Usuario::requerirLogin();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dashboard</title>
  <link rel="stylesheet" href="/Public/css/dashboard.css" />
</head>
<body>
  <header>
    <hgroup>
      <h1 id="title">Draftosaurios</h1>
      <h2 id="title2">Tablero de Verano</h2>
    </hgroup>
    
    <div class="contenedor-derecho">
      <?php
        $usuario_actual = Usuario::actual();
      ?>
      <p class="mensaje-bienvenida">
        ¡Hola, <strong><?php echo htmlspecialchars($usuario_actual['nombre'] ?? 'Usuario'); ?></strong>!
      </p>
      <button class="boton-configuracion" id="botonConfiguracion" aria-label="Configuración de usuario">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-list" viewBox="0 0 16 16">
  <path fill-rule="evenodd" d="M2.5 12a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5"/>
</svg>
      </button>
    </div>
  </header>

  <!-- Menú de navegación deslizable -->
  <nav class="menu-navegacion" id="menuNavegacion">
    <button class="boton-cerrar" id="botonCerrar" aria-label="Cerrar menú">
      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
        <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/>
      </svg>
    </button>
    <ul class="lista-navegacion">
        <li>Nombre de usuario<button class="boton-editar">Editar</button></li>
        <li>Correo electronico<button class="boton-editar">Editar</button></li>
        <li>contraseña<button class="boton-editar">Editar</button></li>
        <li><a href="/logout" class="boton-cerrar-sesion">Cerrar Sesión</a></li>
    </ul>
  </nav>

  <main>
    <section class="actions">
      <h1>Elige un modo de juego</h1>

      <div class="botones-container">
        <button id="botonIniciarDigitalizado" class="boton-modo-1" aria-label="Modo Juego Digitalizado" data-modo="digitalizado">
          <img src="/Public/images/dashboard/MADERA.png" alt="Modo Juego Digitalizado" />
          <span class="tooltip">
            Implementa la dinámica de selección, paso y colocación de dinosaurios. Integra la lógica de restricciones por dado y reglas de puntuación. Ofrece una experiencia de juego fluida y cercana a la versión física.
          </span>
        </button>

        <button id="botonIniciarSeguimiento" class="boton-modo-2" aria-label="Modo Seguimiento" data-modo="seguimiento">
          <img src="/Public/images/dashboard/MADERA2.png" alt="Modo Seguimiento" />
          <span class="tooltip">
            En el Modo Seguimiento podés registrar dónde colocás tus dinosaurios, el sistema aplica automáticamente las reglas y restricciones, calcula tu puntuación final y muestra quién gana.
          </span>
        </button>
      </div>
    </section>
  </main>
  
  <!-- Modal para iniciar partida -->
  <div id="modalIniciarPartida" class="modal-iniciar-juego" aria-hidden="true">
    <div class="fondo-modal" id="fondoModal"></div>
    <div class="contenido-modal" role="dialog" aria-modal="true" aria-labelledby="tituloModal">
      <header>
        <h2 id="tituloModal">Iniciar Partida</h2>
      </header>
      <form id="formularioIniciarPartida">
        <label for="selectorModo">Modo de juego</label>
        <select id="selectorModo" name="modo">
          <option value="digitalizado">Juego Digitalizado</option>
          <option value="seguimiento">Seguimiento</option>
        </select>


        <label for="cantidadJugadores">Cantidad de jugadores</label>
        <select id="cantidadJugadores" name="cantidadJugadores">
          <option value="2">2</option>
          <option value="3">3</option>
          <option value="4">4</option>
        </select>

        <div id="contenedorNombresJugadores" class="nombres-jugadores">
          <!-- Los campos de jugadores se generarán dinámicamente con JavaScript -->
        </div>

        <div class="acciones-modal">
          <button type="button" id="botonCancelar" class="boton-cancelar">Cancelar</button>
          <button type="submit" class="boton-principal">Crear Partida</button>
        </div>
      </form>
    </div>
  </div>


  <script src="/Public/javascript/dashboard.js"></script>
</body>
</html>