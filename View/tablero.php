<?php
require_once __DIR__ . '/../Classes/Usuario.php';
Usuario::requerirLogin();
$usuario_actual = Usuario::actual();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Draftosaurios · Tablero</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="/Public/css/tablero.css" />
</head>
<body>
  <header class="barra-superior d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center gap-2">
      <button class="btn btn-light btn-sm d-inline d-md-none" data-bs-toggle="offcanvas" data-bs-target="#menuJuego" aria-controls="menuJuego" aria-label="Menu de juego">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-width="2" stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
      </button>
      <div class="barra-header">
        <h1 class="mb-0">Draftosaurios</h1>
        <p class="subtitulo mb-0">Tablero de Verano</p>
      </div>
    </div>
    <nav class="barra-nav d-flex align-items-center gap-2">
      <button class="btn btn-outline-light btn-sm" type="button" id="botonScoreboard" aria-label="Ver puntuaciones">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trophy" viewBox="0 0 16 16">
          <path d="M2.5.5A.5.5 0 0 1 3 0h10a.5.5 0 0 1 .5.5c0 .538-.012 1.05-.034 1.536a3 3 0 0 1-.664 2.258c-.484.58-1.16.95-1.87.95-.672 0-1.267-.34-1.75-.85L8.5 3.5l-.5-.5-.5.5L6.5 4.5c-.483.51-1.078.85-1.75.85-.71 0-1.386-.37-1.87-.95a3 3 0 0 1-.664-2.258A30.5 30.5 0 0 1 2.5.5zm.5 2.5a.5.5 0 0 0-.5.5v.5h1v-.5a.5.5 0 0 0-.5-.5zM4 4v.5a.5.5 0 0 0 .5.5h7a.5.5 0 0 0 .5-.5V4H4z"/>
        </svg>
      </button>
      <a href="/dashboard" class="btn btn-outline-success btn-sm d-none d-md-inline-flex">Dashboard</a>
    </nav>
  </header>

  <div class="tablero">
    <aside class="sidebar d-none d-md-grid">
      <section class="estado card p-3">
        <h2 class="titulo">🎮 Estado del Juego</h2>
        <div class="info">
          <div class="item">
            <span class="numero" id="ronda-actual-ui">-</span>
            <span class="label">Ronda</span>
          </div>
          <div class="item">
            <span class="numero" id="turno-actual-ui">-</span>
            <span class="label">Turno</span>
          </div>
        </div>
        <div class="separador"></div>
        <span class="avatar">👤</span>
        <button class="puntaje" type="button" id="puntaje-actual-ui">Puntaje: -</button>
      </section>

      <section class="dado card p-3">
        <h3 class="titulo">🎲 Dado de Colocación</h3>
        <p class="descripcion">Lanza el dado para determinar tu acción.</p>
        <button class="boton-dado btn" aria-label="Lanzar dado">🎲 Lanzar</button>
        <p id="resultadoDado" class="resultado"></p>
      </section>

      <section class="inventario card p-3" aria-label="Inventario de Dinosaurios">
        <h3 class="mb-2 titulo">🦖 Inventario</h3>
        <div class="grid" role="list">
          <button class="dino" role="listitem" aria-label="Triceratops" data-especie="triceratops">
            <img src="/Public/images/dinos/t.png" alt="Triceratops" />
            <span>Triceratops</span>
          </button>
          <button class="dino" role="listitem" aria-label="T-Rex" data-especie="trex">
            <img src="/Public/images/dinos/t2.png" alt="T-Rex" />
            <span>T-Rex</span>
          </button>
          <button class="dino" role="listitem" aria-label="Velociraptor" data-especie="velociraptor">
            <img src="/Public/images/dinos/t3.png" alt="Velociraptor" />
            <span>Velociraptor</span>
          </button>
          <button class="dino" role="listitem" aria-label="Stegosaurus" data-especie="stegosaurus">
            <img src="/Public/images/dinos/t4.png" alt="Stegosaurus" />
            <span>Stegosaurus</span>
          </button>
          <button class="dino" role="listitem" aria-label="Brachiosaurus" data-especie="brachiosaurus">
            <img src="/Public/images/dinos/t5.png" alt="Brachiosaurus" />
            <span>Brachiosaurus</span>
          </button>
          <button class="dino" role="listitem" aria-label="Pterodáctilo" data-especie="pterodactilo">
              <img src="/Public/images/dinos/t6.png" alt="Pterodáctilo" />
            <span>Pterodáctilo</span>
          </button>
        </div>
      </section>
    </aside>

    <main class="juego">
      <section class="tablero-juego">
        <img src="/Public/images/tablero2.png" alt="Tablero de verano Draftosaurios" class="fondo" />

        <section class="recinto bosque-semejanza">
          <h3 class="titulo">Bosque Semejanza</h3>
          <div class="slots">
            <span class="slot"></span><span class="slot"></span><span class="slot"></span><span class="slot"></span>
          </div>
        </section>

        <section class="recinto pradera-amor">
          <h3 class="titulo">Pradera Amor</h3>
          <div class="slots">
            <span class="slot"></span><span class="slot"></span><span class="slot"></span><span class="slot"></span>
          </div>
        </section>

        <section class="recinto trio-frondoso">
          <h3 class="titulo">Trío Frondoso</h3>
          <div class="slots">
            <span class="slot"></span><span class="slot"></span><span class="slot"></span>
          </div>
        </section>

        <section class="recinto isla-solitaria">
          <h3 class="titulo">Isla Solitaria</h3>
          <div class="slots">
            <span class="slot"></span>
          </div>
        </section>

        <section class="recinto rey-selva">
          <h3 class="titulo">Rey Selva</h3>
          <div class="slots">
            <span class="slot"></span>
          </div>
        </section>

        <section class="recinto prado-diferencia">
          <h3 class="titulo">Prado Diferencia</h3>
          <div class="slots">
            <span class="slot"></span><span class="slot"></span><span class="slot"></span><span class="slot"></span>
          </div>
        </section>

        <section class="recinto recinto-rio">
          <h3 class="titulo">Recinto Río</h3>
          <div class="slots">
            <span class="slot"></span><span class="slot"></span><span class="slot"></span><span class="slot"></span><span class="slot"></span><span class="slot"></span><span class="slot"></span><span class="slot"></span>
          </div>
        </section>
      </section>
    </main>
  </div>

  <!-- Inventario móvil -->
  <input type="checkbox" id="interruptorInventario" class="toggle d-md-none" hidden>
  <label for="interruptorInventario" class="btn-inventario d-md-none" aria-controls="panelInventario" aria-expanded="false">🦕 Inventario</label>
  <section id="panelInventario" class="panel d-md-none" aria-hidden="true">
    <header class="header">
      <h3>Inventario</h3>
      <label for="interruptorInventario" class="cerrar" aria-label="Cerrar">✕</label>
    </header>
    <div class="body">
      <ul class="lista">
        <li class="dino" data-especie="triceratops"><img src="/Public/images/dinos/t.png" alt="Triceratops"><span>Triceratops</span></li>
        <li class="dino" data-especie="trex"><img src="/Public/images/dinos/t2.png" alt="T-Rex"><span>T-Rex</span></li>
        <li class="dino" data-especie="velociraptor"><img src="/Public/images/dinos/t3.png" alt="Velociraptor"><span>Velociraptor</span></li>
        <li class="dino" data-especie="stegosaurus"><img src="/Public/images/dinos/t4.png" alt="Stegosaurus"><span>Stegosaurus</span></li>
        <li class="dino" data-especie="brachiosaurus"><img src="/Public/images/dinos/t5.png" alt="Brachiosaurus"><span>Brachiosaurus</span></li>
        <li class="dino" data-especie="pterodactilo"><img src="/Public/images/dinos/t6.png" alt="Pterodáctilo"><span>Pterodáctilo</span></li>
      </ul>
    </div>
  </section>

  <!-- Offcanvas móvil (Bootstrap) -->
  <div class="offcanvas offcanvas-start d-md-none" tabindex="-1" id="menuJuego" aria-labelledby="menuJuegoLabel">
    <header class="offcanvas-header">
      <h5 id="menuJuegoLabel" class="mb-0">Panel</h5>
      <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
    </header>
    <div class="offcanvas-body">
      <section class="estado card p-3 mb-3">
        <h2 class="titulo">🎮 Estado del Juego</h2>
        <div class="info">
          <div class="item">
            <span class="numero" id="ronda-actual-ui-mobile">-</span>
            <span class="label">Ronda</span>
          </div>
          <div class="item">
            <span class="numero" id="turno-actual-ui-mobile">-</span>
            <span class="label">Turno</span>
          </div>
        </div>
        <div class="separador"></div>
        <span class="avatar">👤</span>
        <span class="usuario"><?php echo htmlspecialchars($usuario_actual['nombre'] ?? 'Usuario'); ?></span>
        <button class="puntaje" type="button" id="puntaje-actual-ui-mobile">Puntaje: -</button>
        <!-- Elementos dinámicos ocultos por defecto -->
        <div id="texto-turno-mobile" class="turno" style="display: none;">Ronda 1 — Turno de Jugador 1</div>
        <div id="scoreboard-mobile" class="tabla-puntuaciones" style="display: none;">
          <ul class="list-unstyled mb-0"></ul>
        </div>
      </section>

      <section class="dado card p-3 mb-3">
        <h3 class="titulo"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-dices-icon lucide-dices"><rect width="12" height="12" x="2" y="10" rx="2" ry="2"/><path d="m17.92 14 3.5-3.5a2.24 2.24 0 0 0 0-3l-5-4.92a2.24 2.24 0 0 0-3 0L10 6"/><path d="M6 18h.01"/><path d="M10 14h.01"/><path d="M15 6h.01"/><path d="M18 9h.01"/></svg> Dado de Colocación</h3>
        <p class="descripcion">Lanza el dado para determinar tu acción.</p>
        <button class="boton-dado btn" aria-label="Lanzar dado">🎲 Lanzar</button>
        <p class="resultado mb-0"></p>
      </section>

      
      <!-- Logout en hamburguesa móvil -->
      <div class="mt-3">
        <button class="btn btn-outline-light w-100 mb-2" type="button" id="botonReglasMobile" aria-label="Ver reglas">
          📋 Reglas
        </button>
        <a href="/logout" class="btn btn-warning w-100">Cerrar Sesión</a>
      </div>
      </div>
    </div>
  </div>

  <!-- Footer con información del turno (solo desktop) -->
  <footer class="footer">
    <div class="contenido">
      <div class="info-footer">
      <span class="turno">
        <strong>Turno:</strong> <span id="turno-actual">Cargando...</span>
      </span>
      <span class="ronda">
        <strong>Ronda:</strong> <span id="ronda-actual">-</span>
      </span>
    </div>
      <div class="botones">
        <button class="btn btn-outline-light btn-sm" type="button" id="botonReglas" aria-label="Ver reglas">
          📋 Reglas
        </button>
      </div>
    </div>
  </footer>

  <!-- Modal para scoreboard -->
  <div class="modal fade" id="modalScoreboard" tabindex="-1" aria-labelledby="modalScoreboardLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content modal-tabla-puntuaciones">
        <div class="modal-header encabezado-tabla-puntuaciones">
          <h4 class="modal-title" id="modalScoreboardLabel">
            <span class="icono-tabla-puntuaciones">🏆</span>
            Puntuaciones del Parque
          </h4>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body cuerpo-tabla-puntuaciones">
          <div class="tabla-puntuaciones-simple">
            <div class="info-tabla-puntuaciones">
              <span class="turno-tabla-puntuaciones">Jugada <span id="scoreboard-jugada">-</span>/12</span>
              <span class="ronda-tabla-puntuaciones">Ronda <span id="scoreboard-ronda">-</span>/2</span>
            </div>
            
            <div class="puntos-tabla-puntuaciones">
              <div class="puntos-total">
                <span class="etiqueta-puntos">Puntos:</span>
                <span class="valor-puntos" id="scoreboard-puntos">0</span>
              </div>
            </div>
            
            <div id="scoreboard" class="lista-tabla-puntuaciones">
            <ul class="list-unstyled mb-0"></ul>
            </div>
          </div>
        </div>
        <div class="modal-footer pie-tabla-puntuaciones">
          <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">
            <span class="icono-boton">👁️</span>
            Ver Tablero
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Pantalla final de partida -->
  <section id="pantalla-final" class="pantalla-final" style="display: none;">
    <article class="contenido-final">
      <header class="celebracion">
        <h1>¡Partida Terminada!</h1>
        <p>12 jugadas completadas</p>
      </header>
      
      <section class="puntuacion-destacada">
        <h2 class="puntos-gigantes" id="puntos-totales">0</h2>
        <p class="etiqueta-puntos">puntos</p>
      </section>
      
      <section class="resumen-juego">
        <div class="item-resumen">
          <span class="numero-resumen">12</span>
          <span class="texto-resumen">Jugadas</span>
        </div>
        <div class="item-resumen">
          <span class="numero-resumen">2</span>
          <span class="texto-resumen">Rondas</span>
        </div>
        <div class="item-resumen">
          <span class="numero-resumen">12</span>
          <span class="texto-resumen">Dinosaurios</span>
        </div>
      </section>
      
      <section class="puntos">
        <h3>Puntos</h3>
        <ul class="rejilla-puntos" id="desglose-puntos">
          <!-- Se llena dinámicamente -->
        </ul>
      </section>
      
      <nav class="acciones">
        <button class="btn-nuevo" onclick="nuevaPartida()">
          <span>🔄</span>
          <span>Nueva Partida</span>
        </button>
        <button class="btn-inicio" onclick="volverInicio()">
          <span>🏠</span>
          <span>Inicio</span>
        </button>
      </nav>
    </article>
  </section>

  <!-- Modal para reglas -->
  <div class="modal fade" id="modalReglas" tabindex="-1" aria-labelledby="modalReglasLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content modal-reglas-compacto">
        <div class="modal-header encabezado-reglas-compacto">
          <h5 class="modal-title" id="modalReglasLabel">
            <span class="icono-reglas">🦕</span>
            Reglas del Juego
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body cuerpo-reglas-compacto">
          <div class="reglas-simple">
            <div class="linea-regla">
              <span class="emoji-regla"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-target-icon lucide-target"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/></svg></span>
              <span class="texto-regla">Coloca 12 dinosaurios en recintos</span>
            </div>
            
            <div class="linea-regla">
              <span class="emoji-regla"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-repeat2-icon lucide-repeat-2"><path d="m2 9 3-3 3 3"/><path d="M13 18H7a2 2 0 0 1-2-2V6"/><path d="m22 15-3 3-3-3"/><path d="M11 6h6a2 2 0 0 1 2 2v10"/></svg></span>
              <span class="texto-regla">6 jugadas por ronda (2 rondas total)</span>
            </div>
            
            <div class="recintos-simple">
              <div class="linea-recinto">🌲 Bosque Semejanza (4) - misma especie</div>
              <div class="linea-recinto">🌾 Prado Diferencia (4) - especies distintas</div>
              <div class="linea-recinto">💕 Pradera Amor (4) - +5 por pareja</div>
              <div class="linea-recinto">🌿 Trío Frondoso (3) - +7 si exactamente 3</div>
              <div class="linea-recinto">👑 Rey Selva (1) - 0 puntos</div>
              <div class="linea-recinto">🏝️ Isla Solitaria (1) - +7 si única</div>
              <div class="linea-recinto">🌊 Río (8) - +1 por dinosaurio</div>
            </div>
            
            <div class="linea-regla">
              <span class="emoji-regla"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-star-icon lucide-star"><path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z"/></svg></span>
              <span class="texto-regla">T-Rex: +1 por recinto que lo tenga</span>
            </div>
          </div>
        </div>
        <div class="modal-footer pie-reglas-compacto">
          <button type="button" class="btn btn-primary w-100" data-bs-dismiss="modal">
            <span class="icono-boton"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-gamepad2-icon lucide-gamepad-2"><line x1="6" x2="10" y1="11" y2="11"/><line x1="8" x2="8" y1="9" y2="13"/><line x1="15" x2="15.01" y1="12" y2="12"/><line x1="18" x2="18.01" y1="10" y2="10"/><path d="M17.32 5H6.68a4 4 0 0 0-3.978 3.59c-.006.052-.01.101-.017.152C2.604 9.416 2 14.456 2 16a3 3 0 0 0 3 3c1 0 1.5-.5 2-1l1.414-1.414A2 2 0 0 1 9.828 16h4.344a2 2 0 0 1 1.414.586L17 18c.5.5 1 1 2 1a3 3 0 0 0 3-3c0-1.545-.604-6.584-.685-7.258-.007-.05-.011-.1-.017-.151A4 4 0 0 0 17.32 5z"/></svg></span>
            ¡A Jugar!
          </button>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    window.PARTIDA_ID = <?php echo json_encode($_GET['partida_id'] ?? 0); ?>;
    window.USUARIO_ID = <?php echo json_encode($_SESSION['usuario_id'] ?? 1); ?>;
    window.MODO_JUEGO = <?php echo json_encode($_GET['modo'] ?? 'digitalizado'); ?>;
  </script>
  <script src="/Public/javascript/tablero.js"></script>
</body>
</html>