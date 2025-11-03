<?php
// Asegurar Content-Type HTML para vistas
header('Content-Type: text/html; charset=utf-8');
require_once __DIR__ . '/../Classes/Usuario.php';
Usuario::requerirLogin();
$usuario_actual = Usuario::actual();

// Obtener partida_id de la URL
$partida_id = isset($_GET['partida_id']) ? (int)$_GET['partida_id'] : 0;
$modo = isset($_GET['modo']) ? $_GET['modo'] : 'digitalizado';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Draftosaurios · Tablero de Juego</title>
  <link href="https://fonts.googleapis.com/css2?family=League+Spartan:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="icon" type="image/png" href="/Public/images/index/Isotipo.png">
  <link rel="stylesheet" href="/Public/css/tablero-nuevo.css">
</head>
<body>
  <!-- Header -->
  <header class="game-header">
    <div class="header-container">
            <div class="logo-section">
              <h1 class="logo">Draftosaurios</h1>
              <div class="by-velto">
                <span class="by-text">BY</span>
                <span class="velto-text">Velto</span>
              </div>
            </div>
      
      <nav class="header-actions">
        <div class="user-badge">
          <span class="user-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/>
              <circle cx="12" cy="7" r="4"/>
            </svg>
          </span>
          <span class="user-name"><?php echo htmlspecialchars($usuario_actual['nombre'] ?? 'Usuario'); ?></span>
        </div>
         <button class="btn-header btn-scoreboard" type="button" id="botonScoreboard" aria-label="Ver puntuaciones">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"></path>
            <path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"></path>
            <path d="M4 22h16"></path>
            <path d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22"></path>
            <path d="M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22"></path>
            <path d="M18 2H6v7a6 6 0 0 0 12 0V2Z"></path>
          </svg>
          <span class="btn-text">Puntuaciones</span>
        </button>
        <button class="btn-header btn-reglas" type="button" id="botonReglas" aria-label="Ver reglas del dado">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
            <path d="M7 7h.01"></path>
            <path d="M17 7h.01"></path>
            <path d="M7 17h.01"></path>
            <path d="M17 17h.01"></path>
            <path d="M12 12h.01"></path>
          </svg>
          <span class="btn-text">Reglas del Dado</span>
        </button>
        <a href="/dashboard" class="btn-header">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="m12 19-7-7 7-7"/>
            <path d="M19 12H5"/>
          </svg>
          Dashboard
        </a>
      </nav>
    </div>
  </header>

  <!-- Main Game Container -->
  <main class="game-container">
    <!-- Sidebar (Desktop) -->
    <aside class="game-sidebar" role="complementary" id="gameSidebar">
      <!-- Estado del Juego -->
      <article class="sidebar-card">
        <h2 class="card-title">
          <span class="card-title-icon">🎮</span>
          Estado del Juego
        </h2>
        
         <div class="game-stats">
           <div class="stat-item">
             <span class="stat-number" id="turno-actual-ui">-</span>
             <span class="stat-label">Turno</span>
           </div>
           <div class="stat-item">
             <span class="stat-number" id="jugada-actual-ui">-</span>
             <span class="stat-label">Jugada</span>
           </div>
           <div class="stat-item">
             <span class="stat-number" id="ronda-actual-ui">-</span>
             <span class="stat-label">Ronda</span>
           </div>
         </div>
         
         <div class="stat-divider"></div>
         
         <?php if ($partida_id > 0): ?>
                <div class="game-mode-badge">
                  <span class="mode-text">Modo: <?php echo ucfirst($modo); ?></span>
                  <span class="partida-id">Partida #<?php echo $partida_id; ?></span>
                </div>
              <?php endif; ?>
      </article>

      <!-- Dado de Colocación -->
      <article class="sidebar-card">
        <h2 class="card-title">
          <span class="card-title-icon">🎲</span>
          Dado de Colocación
        </h2>
        
        <p class="dice-description">
          Lanza el dado para determinar tu acción en este turno.
        </p>
        
        <button class="boton-dado" id="btnRollDice" aria-label="Lanzar dado">
          <span>🎲</span>
          <span>Lanzar Dado</span>
        </button>
        
        <div class="dice-result" id="diceResult" aria-live="polite">
          Esperando lanzamiento...
        </div>
      </article>

      <!-- Inventario de Dinosaurios -->
      <article class="sidebar-card">
        <h2 class="card-title">
          <span class="card-title-icon">🦖</span>
          Inventario
        </h2>
        
         <div class="grid" role="list">
           <button class="dino" role="listitem" data-especie="triceratops" aria-label="Seleccionar Triceratops">
             <img src="/Public/images/dinos/t.png" alt="Triceratops">
             <span>Triceratops</span>
           </button>
           
           <button class="dino" role="listitem" data-especie="trex" aria-label="Seleccionar T-Rex">
             <img src="/Public/images/dinos/t2.png" alt="T-Rex">
             <span>T-Rex</span>
           </button>
           
           <button class="dino" role="listitem" data-especie="velociraptor" aria-label="Seleccionar Velociraptor">
             <img src="/Public/images/dinos/t3.png" alt="Velociraptor">
             <span>Velociraptor</span>
           </button>
           
           <button class="dino" role="listitem" data-especie="stegosaurus" aria-label="Seleccionar Stegosaurus">
             <img src="/Public/images/dinos/t4.png" alt="Stegosaurus">
             <span>Stegosaurus</span>
           </button>
           
           <button class="dino" role="listitem" data-especie="brachiosaurus" aria-label="Seleccionar Brachiosaurus">
             <img src="/Public/images/dinos/t5.png" alt="Brachiosaurus">
             <span>Brachiosaurus</span>
           </button>
           
           <button class="dino" role="listitem" data-especie="pterodactilo" aria-label="Seleccionar Pterodáctilo">
             <img src="/Public/images/dinos/t6.png" alt="Pterodáctilo">
             <span>Pterodáctilo</span>
           </button>
         </div>
      </article>
    </aside>

    <!-- Game Board -->
    <section class="game-board-wrapper">
      <div class="game-board-container" id="gameBoard">
        <img src="/Public/images/tablero2.png" alt="Tablero de Juego Draftosaurios" class="board-image">
        
        <!-- Recintos del tablero -->
        <section class="recinto bosque_semejanza">
          <h3 class="titulo">Bosque Semejanza</h3>
          <div class="slots">
            <span class="slot"></span><span class="slot"></span><span class="slot"></span><span class="slot"></span>
          </div>
        </section>

        <section class="recinto pradera_amor">
          <h3 class="titulo">Pradera Amor</h3>
          <div class="slots">
            <span class="slot"></span><span class="slot"></span><span class="slot"></span><span class="slot"></span>
          </div>
        </section>

        <section class="recinto trio_frondoso">
          <h3 class="titulo">Trío Frondoso</h3>
          <div class="slots">
            <span class="slot"></span><span class="slot"></span><span class="slot"></span>
          </div>
        </section>

        <section class="recinto isla_solitaria">
          <h3 class="titulo">Isla Solitaria</h3>
          <div class="slots">
            <span class="slot"></span>
          </div>
        </section>

        <section class="recinto rey_selva">
          <h3 class="titulo">Rey Selva</h3>
          <div class="slots">
            <span class="slot"></span>
          </div>
        </section>

        <section class="recinto prado_diferencia">
          <h3 class="titulo">Prado Diferencia</h3>
          <div class="slots">
            <span class="slot"></span><span class="slot"></span><span class="slot"></span><span class="slot"></span>
          </div>
        </section>

        <section class="recinto rio">
          <h3 class="titulo">Recinto Río</h3>
          <div class="slots">
            <span class="slot"></span><span class="slot"></span><span class="slot"></span><span class="slot"></span><span class="slot"></span><span class="slot"></span><span class="slot"></span><span class="slot"></span>
          </div>
        </section>
      </div>
    </section>
  </main>

  <!-- Botones flotantes móvil -->
  <button class="btn-floating btn-menu-mobile" id="btnMenuMobile" aria-label="Abrir menú">
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M4 6h16M4 12h16M4 18h16"/>
    </svg>
  </button>

  <button class="btn-floating btn-inventory-mobile" id="btnInventoryMobile" aria-label="Abrir inventario">
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
      <path d="M3 9h18"/>
      <path d="M9 21V9"/>
    </svg>
    🦖
  </button>

  <!-- Menú lateral móvil (Estado + Dado) -->
  <aside class="mobile-menu" id="mobileMenu">
    <div class="mobile-menu-overlay" id="menuOverlay"></div>
    <div class="mobile-menu-content">
      <div class="mobile-menu-header">
        <h2>Panel de Juego</h2>
        <button class="btn-close-menu" id="btnCloseMenu" aria-label="Cerrar menú">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M18 6L6 18M6 6l12 12"/>
          </svg>
        </button>
      </div>

      <div class="mobile-menu-body">
        <!-- Estado del Juego -->
        <article class="sidebar-card">
          <h2 class="card-title">
            <span class="card-title-icon">🎮</span>
            Estado del Juego
          </h2>
          
          <div class="game-stats">
            <div class="stat-item">
              <span class="stat-number" id="turno-actual-ui-mobile">-</span>
              <span class="stat-label">Turno</span>
            </div>
            <div class="stat-item">
              <span class="stat-number" id="jugada-actual-ui-mobile">-</span>
              <span class="stat-label">Jugada</span>
            </div>
            <div class="stat-item">
              <span class="stat-number" id="ronda-actual-ui-mobile">-</span>
              <span class="stat-label">Ronda</span>
            </div>
          </div>
          
          <div class="stat-divider"></div>
          
          <?php if ($partida_id > 0): ?>
            <div class="game-info-display">
              <div class="info-item">
                <span class="info-label">Modo:</span>
                <span class="info-value"><?php echo ucfirst($modo); ?></span>
              </div>
              <div class="info-item">
                <span class="info-label">Partida:</span>
                <span class="info-value">#<?php echo $partida_id; ?></span>
              </div>
            </div>
          <?php endif; ?>
        </article>

        <!-- Dado de Colocación -->
        <article class="sidebar-card">
          <h2 class="card-title">
            <span class="card-title-icon">🎲</span>
            Dado de Colocación
          </h2>
          
          <p class="dice-description">
            Lanza el dado para determinar tu acción en este turno.
          </p>
          
          <button class="boton-dado" id="btnRollDice" aria-label="Lanzar dado">
            <span>🎲</span>
            <span>Lanzar Dado</span>
          </button>
          
          <div class="dice-result" id="diceResult" aria-live="polite">
            Esperando lanzamiento...
          </div>
        </article>
        
        <!-- Botones de navegación móvil -->
        <div class="mobile-nav-buttons">
          <button class="btn-mobile-nav btn-scoreboard-mobile" type="button" id="botonScoreboardMobile" aria-label="Ver puntuaciones">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"></path>
              <path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"></path>
              <path d="M4 22h16"></path>
              <path d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22"></path>
              <path d="M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22"></path>
              <path d="M18 2H6v7a6 6 0 0 0 12 0V2Z"></path>
            </svg>
            <span>Puntuaciones</span>
          </button>
          
          <a href="/dashboard" class="btn-mobile-nav btn-dashboard-mobile">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="m12 19-7-7 7-7"/>
              <path d="M19 12H5"/>
            </svg>
            <span>Volver al Dashboard</span>
          </a>
        </div>
      </div>
    </div>
  </aside>

  <!-- Inventario móvil (Drop-up desde abajo) -->
  <aside class="mobile-inventory" id="mobileInventory">
    <div class="mobile-inventory-overlay" id="inventoryOverlay"></div>
    <div class="mobile-inventory-content">
      <div class="mobile-inventory-header">
        <h2>🦖 Inventario</h2>
        <button class="btn-close-inventory" id="btnCloseInventory" aria-label="Cerrar inventario">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M18 6L6 18M6 6l12 12"/>
          </svg>
        </button>
      </div>

      <div class="mobile-inventory-body">
        <ul class="lista" role="list">
          <li class="dino" data-especie="triceratops">
            <img src="/Public/images/dinos/t.png" alt="Triceratops">
            <span>Triceratops</span>
          </li>
          
          <li class="dino" data-especie="trex">
            <img src="/Public/images/dinos/t2.png" alt="T-Rex">
            <span>T-Rex</span>
          </li>
          
          <li class="dino" data-especie="velociraptor">
            <img src="/Public/images/dinos/t3.png" alt="Velociraptor">
            <span>Velociraptor</span>
          </li>
          
          <li class="dino" data-especie="stegosaurus">
            <img src="/Public/images/dinos/t4.png" alt="Stegosaurus">
            <span>Stegosaurus</span>
          </li>
          
          <li class="dino" data-especie="brachiosaurus">
            <img src="/Public/images/dinos/t5.png" alt="Brachiosaurus">
            <span>Brachiosaurus</span>
          </li>
          
          <li class="dino" data-especie="pterodactilo">
            <img src="/Public/images/dinos/t6.png" alt="Pterodáctilo">
            <span>Pterodáctilo</span>
          </li>
        </ul>
      </div>
    </div>
  </aside>

  <!-- Elementos ocultos para JavaScript -->
  <div id="turno-actual" style="display: none;"></div>
  <div id="ronda-actual" style="display: none;"></div>
  <div id="texto-turno-mobile" style="display: none;"></div>
  <div id="puntos-totales" style="display: none;"></div>

  <!-- Scripts -->
  <script>
    // Configuración del juego
    window.PARTIDA_ID = <?php echo json_encode($partida_id ?? 0); ?>;
    window.CSRF = <?php echo json_encode($_SESSION['csrf_token'] ?? ''); ?>;
    window.USUARIO_ID = <?php echo json_encode($usuario_actual['id'] ?? 1); ?>;
    // Solo partidas reales - sin simulación
    
    // Verificar que hay partida real
    if (!window.PARTIDA_ID || window.PARTIDA_ID === 0) {
      alert('Error: No se pudo cargar la partida. Por favor, crea una nueva partida desde el dashboard.');
      window.location.href = '/dashboard';
    }
  </script>
   <script src="/Public/javascript/tablero.js"></script>
  <script>
    // Script específico para el nuevo tablero
    document.addEventListener('DOMContentLoaded', () => {
      // Actualizar inventario con dinosaurios
      if (typeof actualizarInventario === 'function') {
        actualizarInventario();
      }
      
      // Cargar estado de la partida
      if (typeof cargarEstado === 'function' && window.PARTIDA_ID) {
        setTimeout(() => {
          cargarEstado();
        }, 500);
      }
      
      // Elementos móviles
      const btnMenuMobile = document.getElementById('btnMenuMobile');
      const btnInventoryMobile = document.getElementById('btnInventoryMobile');
      const mobileMenu = document.getElementById('mobileMenu');
      const mobileInventory = document.getElementById('mobileInventory');
      const btnCloseMenu = document.getElementById('btnCloseMenu');
      const btnCloseInventory = document.getElementById('btnCloseInventory');
      const menuOverlay = document.getElementById('menuOverlay');
      const inventoryOverlay = document.getElementById('inventoryOverlay');
      
      // Abrir menú lateral móvil
      if (btnMenuMobile) {
        btnMenuMobile.addEventListener('click', () => {
          mobileMenu.classList.add('active');
          document.body.style.overflow = 'hidden';
        });
      }
      
      // Cerrar menú lateral móvil
      const closeMenu = () => {
        mobileMenu.classList.remove('active');
        document.body.style.overflow = '';
      };
      
      if (btnCloseMenu) btnCloseMenu.addEventListener('click', closeMenu);
      if (menuOverlay) menuOverlay.addEventListener('click', closeMenu);
      
      // Abrir inventario móvil (drop-up)
      if (btnInventoryMobile) {
        btnInventoryMobile.addEventListener('click', () => {
          mobileInventory.classList.add('active');
          document.body.style.overflow = 'hidden';
        });
      }
      
      // Cerrar inventario móvil
      const closeInventory = () => {
        mobileInventory.classList.remove('active');
        document.body.style.overflow = '';
      };
      
      if (btnCloseInventory) btnCloseInventory.addEventListener('click', closeInventory);
      if (inventoryOverlay) inventoryOverlay.addEventListener('click', closeInventory);
      
      // Event listeners para las cartas de dinosaurios (móvil)
      const dinoCards = document.querySelectorAll('.dino-card');
      dinoCards.forEach(card => {
        card.addEventListener('click', () => {
          if (window.innerWidth < 992) {
            closeInventory();
          }
        });
      });
    });
    
  </script>

  <!-- Modal Pantalla Final -->
  <div id="pantalla-final" class="modal-final" style="display: none;">
    <div class="modal-overlay" onclick="document.getElementById('pantalla-final').style.display='none'"></div>
    <div class="modal-final-content">
      <button class="modal-close" onclick="document.getElementById('pantalla-final').style.display='none'" aria-label="Cerrar">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M18 6L6 18M6 6l12 12"/>
        </svg>
      </button>
      
      <div class="modal-header">
        <h2 class="modal-title">🏆 Partida Finalizada</h2>
        <p class="modal-subtitle">Puntuaciones finales</p>
      </div>
      
      <div class="modal-body">
        <div class="podium-mini" id="podium-container">
          <!-- Se llenará con JavaScript -->
        </div>
        
        <div class="scores-list" id="scores-table">
          <!-- Se llenará con JavaScript -->
        </div>
      </div>
      
      <div class="modal-footer">
        <button class="btn-modal btn-dashboard" onclick="window.location.href='/dashboard'">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
            <polyline points="9 22 9 12 15 12 15 22"></polyline>
          </svg>
          Dashboard
        </button>
        <button class="btn-modal btn-new-game" onclick="window.location.href='/dashboard'">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="23 4 23 10 17 10"></polyline>
            <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path>
          </svg>
          Nueva Partida
        </button>
      </div>
    </div>
  </div>

  <!-- Modal de Cambio de Turno -->
  <div id="modal-turno" class="modal-turno" style="display: none;">
    <div class="modal-turno-overlay"></div>
    <div class="modal-turno-content">
      <div class="modal-turno-icon">🎮</div>
      <h2 class="modal-turno-title">Es el turno de</h2>
      <div class="modal-turno-jugador" id="modal-turno-nombre">Jugador</div>
      <p class="modal-turno-subtitle">Prepárate para jugar...</p>
      <div class="modal-turno-timer">
        <div class="timer-circle">
          <span id="modal-turno-countdown">3</span>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal de Scoreboard -->
  <div id="modal-scoreboard" class="modal-scoreboard" style="display: none;">
    <div class="modal-overlay" onclick="document.getElementById('modal-scoreboard').style.display='none'"></div>
    <div class="modal-scoreboard-content">
      <button class="modal-close" onclick="document.getElementById('modal-scoreboard').style.display='none'" aria-label="Cerrar">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M18 6L6 18M6 6l12 12"/>
        </svg>
      </button>
      
      <div class="modal-header">
        <h2 class="modal-title">🏆 Puntuaciones Actuales</h2>
        <p class="modal-subtitle">Tabla de posiciones</p>
      </div>
      
      <div class="modal-body">
        <div class="scoreboard-list" id="scoreboard-container">
          <!-- Se llenará con JavaScript -->
        </div>
      </div>
    </div>
  </div>

  <!-- Modal de Reglas -->
  <div id="modal-reglas" class="modal-reglas" style="display: none;">
    <div class="modal-overlay" onclick="document.getElementById('modal-reglas').style.display='none'"></div>
    <div class="modal-reglas-content">
      <button class="modal-close" onclick="document.getElementById('modal-reglas').style.display='none'" aria-label="Cerrar">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M18 6L6 18M6 6l12 12"/>
        </svg>
      </button>
      
      <div class="modal-header">
        <h2 class="modal-title">📋 Reglas del Dado - Modo Digital</h2>
        <p class="modal-subtitle">Restricciones de colocación según la cara del dado</p>
      </div>
      
      <div class="modal-body">
        <div class="reglas-list">
          <div class="regla-item">
            <div class="regla-icon">🌳</div>
            <div class="regla-content">
              <h3 class="regla-titulo">Bosque</h3>
              <p class="regla-descripcion">El dinosaurio debe ir en un recinto del área del <strong>Bosque</strong>.</p>
              <div class="regla-ejemplo">Recintos: Bosque Semejanza, Trío Frondoso, Rey Selva</div>
            </div>
          </div>
          
          <div class="regla-item">
            <div class="regla-icon">🌾</div>
            <div class="regla-content">
              <h3 class="regla-titulo">Llanura</h3>
              <p class="regla-descripcion">El dinosaurio debe ir en un recinto del área de <strong>Llanura</strong>.</p>
              <div class="regla-ejemplo">Recintos: Prado Diferencia, Pradera Amor, Isla Solitaria</div>
            </div>
          </div>
          
          <div class="regla-item">
            <div class="regla-icon">🚻</div>
            <div class="regla-content">
              <h3 class="regla-titulo">Baños</h3>
              <p class="regla-descripcion">Solo puede colocarse en recintos a la <strong>derecha del Río</strong>.</p>
              <div class="regla-ejemplo">Recintos del lado derecho del tablero</div>
            </div>
          </div>
          
          <div class="regla-item">
            <div class="regla-icon">☕</div>
            <div class="regla-content">
              <h3 class="regla-titulo">Cafetería</h3>
              <p class="regla-descripcion">Solo puede colocarse en recintos a la <strong>izquierda del Río</strong>.</p>
              <div class="regla-ejemplo">Recintos del lado izquierdo del tablero</div>
            </div>
          </div>
          
          <div class="regla-item">
            <div class="regla-icon">🈳</div>
            <div class="regla-content">
              <h3 class="regla-titulo">Recinto Vacío</h3>
              <p class="regla-descripcion">Solo puede colocarse en un recinto que esté <strong>completamente vacío</strong>.</p>
              <div class="regla-ejemplo">El recinto no debe tener ningún dinosaurio</div>
            </div>
          </div>
          
          <div class="regla-item">
            <div class="regla-icon">🦖</div>
            <div class="regla-content">
              <h3 class="regla-titulo">¡Cuidado con el T-Rex!</h3>
              <p class="regla-descripcion">Solo puede colocarse en un recinto que <strong>no contenga un T-Rex</strong>.</p>
              <div class="regla-ejemplo">Nota: Se permite colocar un T-Rex si el recinto aún no tenía uno</div>
            </div>
          </div>
        </div>
        
        <div class="reglas-nota">
          <strong>📌 Nota Importante:</strong> El Río siempre es válido para cualquier cara del dado.
        </div>
      </div>
    </div>
  </div>

</body>
</html>

