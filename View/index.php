<?php
// Asegurar Content-Type HTML para vistas
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Draftosaurios - Construye tu Zoológico Prehistórico</title>
    <link href="https://fonts.googleapis.com/css2?family=League+Spartan:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="/Public/images/index/Isotipo.png">
    <link rel="stylesheet" href="/Public/css/index.css">
</head>
<body>
    <!-- Header -->
    <header>
        <div class="header-left">
            <h2 class="Draftosaurios">Draftosaurios</h2>
            <div class="by-velto">
                <h3>BY</h3>
                <h2 class="H2-Velto">Velto</h2>
            </div>
        </div>
        <nav class="nav">
            <a href="#como-jugar" class="nav-link">Cómo jugar</a>
            <a href="#caracteristicas" class="nav-link">Características</a>
            <a href="/login" class="btn-jugar">Jugar ahora</a>
        </nav>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-background">
            <img src="/Public/images/index/FONDO.png" alt="Fondo prehistórico" class="hero-bg-image">
            <div class="hero-overlay"></div>
        </div>
        
        <div class="hero-content">
            <div class="hero-text">
                <h1 class="hero-title">
                    Construye el mejor
                    <span class="highlight">zoológico prehistórico</span>
                </h1>
                <p class="hero-subtitle">
                    Un juego de estrategia rápido donde cada decisión cuenta. 
                    Colecciona dinosaurios, completa objetivos y demuestra quién es el mejor paleontólogo.
                </p>
                
                <div class="hero-stats">
                    <div class="stat-item">
                        <div class="stat-number">15-20</div>
                        <div class="stat-label">minutos</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">2-5</div>
                        <div class="stat-label">jugadores</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">8+</div>
                        <div class="stat-label">años</div>
                    </div>
                </div>
                
                <div class="hero-actions">
                    <a href="/login" class="btn-primary">
                        <span class="btn-icon">🦕</span>
                        Comenzar a jugar
                    </a>
                    <a href="#como-jugar" class="btn-secondary">
                        <span class="btn-icon">📖</span>
                        Ver cómo funciona
                    </a>
                </div>
            </div>
            
            <div class="hero-visual">
                <div class="dino-showcase">
                    <img src="/Public/images/index/Dinosaurio.webp" alt="Dinosaurio principal" class="main-dino">
                    <div class="floating-dinos">
                        <img src="/Public/images/index/cartel.webp" alt="Cartel" class="sign">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features" id="caracteristicas">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">¿Por qué elegir Draftosaurios?</h2>
                <p class="section-subtitle">Estrategia, diversión y dinosaurios en cada partida</p>
            </div>
            
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">🎯</div>
                    <h3 class="feature-title">Estrategia Profunda</h3>
                    <p class="feature-description">
                        Cada decisión importa. Planifica tu zoológico, gestiona recursos y 
                        adapta tu estrategia según evoluciona la partida.
                    </p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">⚡</div>
                    <h3 class="feature-title">Partidas Rápidas</h3>
                    <p class="feature-description">
                        Diversión intensa en solo 15-20 minutos. Perfecto para 
                        cualquier momento del día.
                    </p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">🦕</div>
                    <h3 class="feature-title">Dinosaurios Únicos</h3>
                    <p class="feature-description">
                        Cada especie tiene su valor estratégico. Desde el poderoso T-Rex 
                        hasta el ágil Velociraptor.
                    </p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">👥</div>
                    <h3 class="feature-title">Multijugador</h3>
                    <p class="feature-description">
                        Juega con 2-5 jugadores. Cada partida es diferente gracias 
                        a la interacción entre jugadores.
                    </p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">🏆</div>
                    <h3 class="feature-title">Objetivos Variados</h3>
                    <p class="feature-description">
                        Múltiples formas de ganar puntos. Agrupa por especie, 
                        color o patrón según la estrategia.
                    </p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">🎨</div>
                    <h3 class="feature-title">Arte Hermoso</h3>
                    <p class="feature-description">
                        Disfruta de ilustraciones preciosas que te transportan 
                        a la era de los dinosaurios.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- How to Play Section -->
    <section class="how-to-play" id="como-jugar">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">¿Cómo se juega?</h2>
                <p class="section-subtitle">Aprende las reglas básicas en minutos y domina la estrategia</p>
            </div>
            
            <div class="steps-container">
                <div class="step-card">
                    <div class="step-number">1</div>
                    <div class="step-content">
                        <h3 class="step-title">Elige tu dinosaurio</h3>
                        <p class="step-description">
                            En cada turno, selecciona un dinosaurio del conjunto compartido. 
                            Cada especie tiene diferentes valores estratégicos.
                        </p>
                    </div>
                    <div class="step-visual">
                        <img src="/Public/images/dinos/t.png" alt="Triceratops" class="step-dino">
                    </div>
                </div>
                
                <div class="step-card">
                    <div class="step-number">2</div>
                    <div class="step-content">
                        <h3 class="step-title">Colócalo estratégicamente</h3>
                        <p class="step-description">
                            Decide dónde ubicar tu dinosaurio en tu zoológico para 
                            maximizar los puntos según los objetivos.
                        </p>
                    </div>
                    <div class="step-visual">
                        <img src="/Public/images/index/Madera.png" alt="Tablero" class="step-board">
                    </div>
                </div>
                
                <div class="step-card">
                    <div class="step-number">3</div>
                    <div class="step-content">
                        <h3 class="step-title">Cumple objetivos</h3>
                        <p class="step-description">
                            Agrupa dinosaurios por especie, color o patrón según 
                            los objetivos de cada recinto para ganar puntos.
                        </p>
                    </div>
                    <div class="step-visual">
                        <img src="/Public/images/index/Ramas3.png" alt="Recintos" class="step-enclosures">
                    </div>
                </div>
                
                <div class="step-card">
                    <div class="step-number">4</div>
                    <div class="step-content">
                        <h3 class="step-title">Gana puntos</h3>
                        <p class="step-description">
                            Al final de la partida, suma tus puntos de todos los recintos. 
                            ¡El mejor estratega gana!
                        </p>
                    </div>
                    <div class="step-visual">
                        <div class="score-display">
                            <span class="score-number">42</span>
                            <span class="score-label">puntos</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="cta-background">
            <img src="/Public/images/index/Rama2.png" alt="Ramas decorativas" class="cta-bg-left">
            <img src="/Public/images/index/Rama4.webp" alt="Ramas decorativas" class="cta-bg-right">
        </div>
        
        <div class="container">
            <div class="cta-content">
                <h2 class="cta-title">¿Listo para construir tu zoológico?</h2>
                <p class="cta-description">
                    Únete a miles de jugadores y demuestra tu habilidad estratégica. 
                    ¡Cada partida es una nueva aventura prehistórica!
                </p>
                <div class="cta-actions">
                    <a href="/login" class="btn-primary large">
                        <span class="btn-icon"></span>
                        Empezar a jugar gratis
                    </a>
                </div>
            </div>
        </div>
    </section>


    <script>
        // Smooth scrolling para enlaces internos
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Animación de entrada para las tarjetas
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-in');
                }
            });
        }, observerOptions);

        // Observar elementos para animación
        document.querySelectorAll('.feature-card, .step-card').forEach(el => {
            observer.observe(el);
        });
    </script>
</body>
</html>