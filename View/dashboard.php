<?php
// Asegurar Content-Type HTML para vistas
header('Content-Type: text/html; charset=utf-8');
require_once __DIR__ . '/../Classes/Usuario.php';
require_once __DIR__ . '/../Database/config.php';
Usuario::requerirLogin();

// Inyectar variables necesarias para el frontend
$PARTIDA_ID = isset($_GET['partida_id']) ? (int)$_GET['partida_id'] : 0;
$MODO = isset($_GET['modo']) ? $_GET['modo'] : 'digitalizado';
$USUARIO_ID = $_SESSION['usuario_id'] ?? null;
$CSRF = $_SESSION['csrf'] ?? null;

// Calcular estadísticas del usuario
$pdo = getPDO();
$usuarioId = $_SESSION['usuario_id'];

// Partidas jugadas (partidas finalizadas donde el usuario participó)
$stmtPartidas = $pdo->prepare("
    SELECT COUNT(DISTINCT p.id) 
    FROM partidas p
    INNER JOIN jugadores_partida jp ON jp.partida_id = p.id
    WHERE jp.usuario_id = ? AND p.estado = 'fin'
");
$stmtPartidas->execute([$usuarioId]);
$partidasJugadas = (int)$stmtPartidas->fetchColumn();

// Victorias (partidas donde el usuario tuvo la puntuación más alta)
$stmtVictorias = $pdo->prepare("
    SELECT COUNT(*) FROM (
        SELECT p.id
        FROM partidas p
        INNER JOIN jugadores_partida jp ON jp.partida_id = p.id
        WHERE jp.usuario_id = ? AND p.estado = 'fin'
        AND jp.puntos = (
            SELECT MAX(puntos) 
            FROM jugadores_partida 
            WHERE partida_id = p.id
        )
    ) victorias
");
$stmtVictorias->execute([$usuarioId]);
$victorias = (int)$stmtVictorias->fetchColumn();

// Puntos totales (suma de todos los puntos en partidas finalizadas)
$stmtPuntos = $pdo->prepare("
    SELECT COALESCE(SUM(jp.puntos), 0)
    FROM jugadores_partida jp
    INNER JOIN partidas p ON p.id = jp.partida_id
    WHERE jp.usuario_id = ? AND p.estado = 'fin'
");
$stmtPuntos->execute([$usuarioId]);
$puntosTotales = (int)$stmtPuntos->fetchColumn();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dashboard - Draftosaurios</title>
  <link href="https://fonts.googleapis.com/css2?family=League+Spartan:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="icon" type="image/png" href="/Public/images/index/Isotipo.png">
  <link rel="stylesheet" href="/Public/css/dashboard.css" />
</head>
<body>
  <!-- Header -->
  <header class="header">
    <div class="header-content">
      <div class="logo-section">
        <h1 class="logo">Draftosaurios</h1>
        <div class="by-velto">
          <span class="by-text">BY</span>
          <span class="velto-text">Velto</span>
        </div>
      </div>
      
      <div class="header-right">
      <?php
        $usuario_actual = Usuario::actual();
      ?>
        <div class="user-welcome">
          <span class="welcome-icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user-icon lucide-user"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></span>
          <span class="welcome-text">
        ¡Hola, <strong><?php echo htmlspecialchars($usuario_actual['nombre'] ?? 'Usuario'); ?></strong>!
          </span>
        </div>
        <button class="btn-config" id="botonConfiguracion" aria-label="Configuración de usuario">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
            <path d="M3 9.5a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zm5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zm5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3z"/>
</svg>
      </button>
      </div>
    </div>
  </header>

  <!-- Menú de navegación deslizable -->
  <nav class="sidebar-menu" id="menuNavegacion">
    <button class="btn-close" id="botonCerrar" aria-label="Cerrar menú">
      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
        <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/>
      </svg>
    </button>
    
    <div class="sidebar-header">
      <div class="user-avatar">
        <span class="avatar-icon">🦖</span>
      </div>
      <h3 class="sidebar-username"><?php echo htmlspecialchars($usuario_actual['nombre'] ?? 'Usuario'); ?></h3>
      <p class="sidebar-email"><?php echo htmlspecialchars($usuario_actual['email'] ?? 'correo@ejemplo.com'); ?></p>
    </div>
    
    <ul class="sidebar-nav">
      <li class="nav-item">
        <div>
          <div class="nav-label-title">Nombre de usuario</div>
          <div class="nav-label-value"><?php echo htmlspecialchars($usuario_actual['nombre'] ?? ''); ?></div>
        </div>
        <button class="btn-edit" data-edit="nombre">Editar</button>
      </li>
      <li class="nav-item">
        <div>
          <div class="nav-label-title">Correo electrónico</div>
          <div class="nav-label-value"><?php echo htmlspecialchars($usuario_actual['email'] ?? ''); ?></div>
        </div>
        <button class="btn-edit" data-edit="email">Editar</button>
      </li>
      <li class="nav-item">
        <div>
          <div class="nav-label-title">Contraseña</div>
          <div class="nav-label-value">••••••••</div>
        </div>
        <button class="btn-edit" data-edit="password">Editar</button>
      </li>
    </ul>
    
    <div class="sidebar-footer">
      <a href="/logout" class="btn-logout">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
          <path fill-rule="evenodd" d="M10 12.5a.5.5 0 0 1-.5.5h-8a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 .5.5v2a.5.5 0 0 0 1 0v-2A1.5 1.5 0 0 0 9.5 2h-8A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h8a1.5 1.5 0 0 0 1.5-1.5v-2a.5.5 0 0 0-1 0v2z"/>
          <path fill-rule="evenodd" d="M15.854 8.354a.5.5 0 0 0 0-.708l-3-3a.5.5 0 0 0-.708.708L14.293 7.5H5.5a.5.5 0 0 0 0 1h8.793l-2.147 2.146a.5.5 0 0 0 .708.708l3-3z"/>
        </svg>
        Cerrar Sesión
      </a>
    </div>
  </nav>

  <!-- Overlay para el menú -->
  <div class="sidebar-overlay" id="sidebarOverlay"></div>

  <!-- Contenido principal -->
  <main class="main-container">
    <div class="content-wrapper">
      <!-- Sección de bienvenida -->
      <section class="welcome-section">
        <div class="welcome-content">
          <h1 class="page-title">
            Bienvenido a tu
            <span class="highlight">Zoológico Prehistórico</span>
          </h1>
          <p class="page-subtitle">
            Selecciona un modo de juego y comienza una nueva aventura estratégica. 
            Cada partida es única, cada decisión cuenta.
          </p>
        </div>
        <div class="stats-grid">
          <div class="stat-card">
            <div class="stat-icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-gamepad2-icon lucide-gamepad-2"><line x1="6" x2="10" y1="11" y2="11"/><line x1="8" x2="8" y1="9" y2="13"/><line x1="15" x2="15.01" y1="12" y2="12"/><line x1="18" x2="18.01" y1="10" y2="10"/><path d="M17.32 5H6.68a4 4 0 0 0-3.978 3.59c-.006.052-.01.101-.017.152C2.604 9.416 2 14.456 2 16a3 3 0 0 0 3 3c1 0 1.5-.5 2-1l1.414-1.414A2 2 0 0 1 9.828 16h4.344a2 2 0 0 1 1.414.586L17 18c.5.5 1 1 2 1a3 3 0 0 0 3-3c0-1.545-.604-6.584-.685-7.258-.007-.05-.011-.1-.017-.151A4 4 0 0 0 17.32 5z"/></svg></div>
            <div class="stat-info">
              <span class="stat-number"><?php echo $partidasJugadas; ?></span>
              <span class="stat-label">Partidas jugadas</span>
            </div>
          </div>
          <div class="stat-card">
            <div class="stat-icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-medal-icon lucide-medal"><path d="M7.21 15 2.66 7.14a2 2 0 0 1 .13-2.2L4.4 2.8A2 2 0 0 1 6 2h12a2 2 0 0 1 1.6.8l1.6 2.14a2 2 0 0 1 .14 2.2L16.79 15"/><path d="M11 12 5.12 2.2"/><path d="m13 12 5.88-9.8"/><path d="M8 7h8"/><circle cx="12" cy="17" r="5"/><path d="M12 18v-2h-.5"/></svg></div>
            <div class="stat-info">
              <span class="stat-number"><?php echo $victorias; ?></span>
              <span class="stat-label">Victorias</span>
            </div>
          </div>
          <div class="stat-card">
            <div class="stat-icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-star-icon lucide-star"><path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z"/></svg></div>
            <div class="stat-info">
              <span class="stat-number"><?php echo $puntosTotales; ?></span>
              <span class="stat-label">Puntos totales</span>
            </div>
          </div>
        </div>
      </section>

      <!-- Sección de modos de juego -->
      <section class="game-modes-section">
        <h2 class="section-title">Modos de Juego</h2>
        <p class="section-subtitle">Elige tu experiencia preferida</p>
        
        <div class="game-modes-grid">
          <!-- Modo Digitalizado -->
          <div class="game-mode-card" id="botonIniciarDigitalizado" data-modo="digitalizado">
            <div class="card-header">
              <div class="mode-icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-target-icon lucide-target"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/></svg></div>
              <h3 class="mode-title">Juego Digitalizado</h3>
            </div>
            <div class="card-body">
              <p class="mode-description">
                Experiencia completa con selección automática, restricciones por dado y sistema 
                de puntuación integrado. Juega como en la versión física pero digitalizado.
              </p>
              <ul class="mode-features">
                <li>
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425a.267.267 0 0 1 .02-.022z"/>
                  </svg>
                  Selección y paso de dinosaurios
                </li>
                <li>
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425a.267.267 0 0 1 .02-.022z"/>
                  </svg>
                  Restricciones automáticas
                </li>
                <li>
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425a.267.267 0 0 1 .02-.022z"/>
                  </svg>
                  Puntuación en tiempo real
                </li>
              </ul>
            </div>
            <div class="card-footer">
              <button class="btn-play">
                <span>Jugar ahora</span>
        </button>
            </div>
          </div>

          <!-- Modo Seguimiento -->
          <div class="game-mode-card" id="botonIniciarSeguimiento" data-modo="seguimiento">
            <div class="card-header">
              <div class="mode-icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chart-spline-icon lucide-chart-spline"><path d="M3 3v16a2 2 0 0 0 2 2h16"/><path d="M7 16c.5-2 1.5-7 4-7 2 0 2 3 4 3 2.5 0 4.5-5 5-7"/></svg></div>
              <h3 class="mode-title">Modo Seguimiento</h3>
            </div>
            <div class="card-body">
              <p class="mode-description">
                Registra tu partida física y deja que el sistema calcule automáticamente 
                tu puntuación. Perfecto para jugar con el tablero real.
              </p>
              <ul class="mode-features">
                <li>
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425a.267.267 0 0 1 .02-.022z"/>
                  </svg>
                  Registro manual de posiciones
                </li>
                <li>
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425a.267.267 0 0 1 .02-.022z"/>
                  </svg>
                  Validación automática
                </li>
                <li>
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425a.267.267 0 0 1 .02-.022z"/>
                  </svg>
                  Cálculo de puntuación final
                </li>
              </ul>
            </div>
            <div class="card-footer">
              <button class="btn-play">
                <span>Comenzar registro</span>
        </button>
            </div>
          </div>
      </div>
    </section>
    </div>
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

  <!-- Modal editar nombre -->
  <div id="modalEditarNombre" class="modal-editar" aria-hidden="true">
    <div class="fondo-modal"></div>
    <div class="contenido-modal">
      <h2>Editar Nombre</h2>
      <form id="formEditarNombre">
        <label for="inputNuevoNombre">Nuevo nombre</label>
        <input type="text" id="inputNuevoNombre" name="nombre" required minlength="3" maxlength="50">
        <div class="acciones-modal">
          <button type="button" class="boton-cancelar btn-cancelar-edit">Cancelar</button>
          <button type="submit" class="boton-principal">Guardar</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Modal editar email -->
  <div id="modalEditarEmail" class="modal-editar" aria-hidden="true">
    <div class="fondo-modal"></div>
    <div class="contenido-modal">
      <h2>Editar Email</h2>
      <form id="formEditarEmail">
        <label for="inputNuevoEmail">Nuevo email</label>
        <input type="email" id="inputNuevoEmail" name="email" required>
        <div class="acciones-modal">
          <button type="button" class="boton-cancelar btn-cancelar-edit">Cancelar</button>
          <button type="submit" class="boton-principal">Guardar</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Modal editar contraseña -->
  <div id="modalEditarPassword" class="modal-editar" aria-hidden="true">
    <div class="fondo-modal"></div>
    <div class="contenido-modal">
      <h2>Cambiar Contraseña</h2>
      <form id="formEditarPassword">
        <label for="inputPasswordActual">Contraseña actual</label>
        <input type="password" id="inputPasswordActual" name="password_actual" required>
        
        <label for="inputPasswordNuevo">Nueva contraseña</label>
        <input type="password" id="inputPasswordNuevo" name="password_nuevo" required minlength="6">
        
        <label for="inputPasswordConfirmar">Confirmar nueva contraseña</label>
        <input type="password" id="inputPasswordConfirmar" name="password_confirmar" required minlength="6">
        
        <div class="acciones-modal">
          <button type="button" class="boton-cancelar btn-cancelar-edit">Cancelar</button>
          <button type="submit" class="boton-principal">Cambiar</button>
        </div>
      </form>
    </div>
  </div>

  <script>
    window.PARTIDA_ID = <?= (int)$PARTIDA_ID ?>;
    window.MODO = <?= json_encode($MODO) ?>;
    window.USUARIO_ID = <?= is_null($USUARIO_ID) ? 'null' : (int)$USUARIO_ID ?>;
    window.CSRF = <?= is_null($CSRF) ? 'null' : json_encode($CSRF) ?>;
  </script>
  <script src="/Public/javascript/dashboard.js"></script>
</body>
</html>