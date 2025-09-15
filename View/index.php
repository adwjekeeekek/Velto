<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Draftosaurios - Juego de Mesa</title>
  <meta name="description" content="Aprende a jugar Draftosaurios, el juego de mesa donde construyes tu propio zoologico de dinosaurios">

  <link href="https://fonts.googleapis.com/css2?family=League+Spartan:wght@400;700&display=swap" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous" />
  <link rel="icon" type="image/png" href="/Public/images/index/Isotipo.png" />
  <link rel="stylesheet" href="/Public/css/index.css" />
</head>

<body>
  <!-- Elementos decorativos -->
  <aside class="elementos-decorativos" aria-hidden="true">
    <img src="/Public/images/index/dino-vol.webp" alt="Dinosaurio volador decorativo" class="dinosaurio-volador">
    <img src="/Public/images/index/Rama4.webp" alt="Rama decorativa" class="rama-decorativa">
  </aside>


  <div class="contenedor-principal">
    <header class="row">
      <div class="col-12">
        <section class="encabezado text-center">
          <hgroup class="titulo-bienvenida">
            <h1 class="titulo-principal">DRAFTOSAURIOS</h1>
          </hgroup>
        </section>
      </div>
    </header>

    <main class="row contenido-principal">
      <section class="col-lg-7 col-md-12 col-video">
        <article class="contenedor-video">
          <div class="video-responsivo ratio-16x9">
            <video src="/Public/images/index/video.mp4" class="video-elemento" controls playsinline title="Tutorial de Draftosaurios">
            </video>
          </div>
        </article>
      </section>

      <section class="col-lg-5 col-md-12 col-texto">
        <article class="seccion-texto">
          <p class="texto-principal">
            Este video te guia paso a paso por las reglas del juego, el modo de juego y todo lo que necesitas saber
            para empezar a jugar Draftosaurios. Aprende como colocar tus dinosaurios estrategicamente, que zonas del zoologico
            puntuan mas, y descubre consejos para convertirte en el mejor paleozoologo. Ideal para nuevos jugadores y para
            quienes quieren repasar antes de jugar!
          </p>
        </article>
      </section>

      <footer class="col-12 text-center">
        <section class="seccion-boton">
          <nav aria-label="Accion principal">
            <a href="/login" class="boton-madera" role="button" aria-label="Comenzar a jugar">
              <img src="/Public/images/index/Madera.png" alt="Boton de madera para comenzar" class="imagen-madera">
            </a>
          </nav>
        </section>
      </footer>
    </main>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q"
    crossorigin="anonymous"></script>
</body>
</html>