<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Draftosaurios - Iniciar Sesión</title>
  <link href="https://fonts.googleapis.com/css2?family=League+Spartan&display=swap" rel="stylesheet" />
  <link rel="icon" type="image/png" href="/Public/images/index/Isotipo.png" />
  <link rel="stylesheet" href="/Public/css/style.css" />
</head>

<body>
  <?php if (isset($mensaje) && $mensaje): ?>
    <div class="mensaje-<?= $mensaje['exito'] ? 'exito' : 'error' ?>">
      <?= htmlspecialchars($mensaje['mensaje']) ?>
    </div>
  <?php endif; ?>

  <header>
    <h2 class="Draftosaurios">Draftosaurios</h2>
    <div class="by-velto">
      <h3>BY</h3>
      <h2 class="H2-Velto">Velto</h2>
    </div>
  </header>

  <section id="Register">
    <div class="cuadro">
      <h2 class="text-registrar">Registrarse</h2>
      <a href="#popup" class="cerrar">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
          stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"
          class="lucide lucide-arrow-left-icon lucide-arrow-left">
          <path d="m12 19-7-7 7-7" />
          <path d="M19 12H5" />
        </svg>
      </a>
      <form method="POST" action="/registro" id="register-form">
        <div id="register-placeholder" class="placeholder">
          <input type="text" name="nombre_usuario" placeholder="Usuario" required />
          <input type="email" name="email" placeholder="Correo Electrónico" required />
          <input type="password" name="contraseña" placeholder="Contraseña" required />
          <input type="password" name="confirmar_contraseña" placeholder="Confirmar Contraseña" required />
        </div>
        <button type="submit" class="btn-enviar">Registrarse</button>
      </form>
    </div>
  </section>

  <section id="popup" class="popup">
    <div class="cuadro">
      <a href="#login">
        <button href="#login" class="btn-iniciar">Iniciar sesión</button>
      </a>
      <a href="#Register">
        <button class="btn-registrar">Registrarse</button>
      </a>
      <a href="/" class="cerrar">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
          stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"
          class="lucide lucide-arrow-left-icon lucide-arrow-left">
          <path d="m12 19-7-7 7-7" />
          <path d="M19 12H5" />
        </svg>
      </a>
      <h2 class="Draftosaurios-2">Draftosaurios</h2>
    </div>
  </section>

  <section id="login" class="popup">
    <div class="cuadro">
      <h2 class="text-iniciar">Iniciar Sesión</h2>
      <a href="#popup" class="cerrar">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
          stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"
          class="lucide lucide-arrow-left-icon lucide-arrow-left">
          <path d="m12 19-7-7 7-7" />
          <path d="M19 12H5" />
        </svg>
      </a>
      <form method="POST" action="/login" id="login-form">
        <div id="login-placeholder" class="placeholder">
          <input type="text" name="nombre_usuario" placeholder="Usuario" required />
          <input type="password" name="contraseña" placeholder="Contraseña" required />
        </div>
        <button type="submit" class="btn-enviar">Jugar</button>
      </form>
    </div>
  </section>

  <div class="container">
    <a href="#popup" class="jugar-btn">Jugar</a>
  </div>
</body>
</html>