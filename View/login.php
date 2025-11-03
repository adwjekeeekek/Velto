<?php
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Draftosaurios - Iniciar Sesión</title>
    <link href="https://fonts.googleapis.com/css2?family=League+Spartan:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="/Public/images/index/Isotipo.png">
    <link rel="stylesheet" href="/Public/css/login.css">
</head>
<body>
    <!-- Mensajes de error/éxito -->
    <?php if (isset($mensaje) && $mensaje): ?>
        <div class="mensaje-<?php echo $mensaje['exito'] ? 'exito' : 'error'; ?>">
            <?php echo htmlspecialchars($mensaje['mensaje']); ?>
        </div>
    <?php endif; ?>

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
            <a href="/" class="btn-volver">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m12 19-7-7 7-7"/>
                    <path d="M19 12H5"/>
                </svg>
                Volver
            </a>
        </div>
    </header>

    <!-- Contenido Principal -->
    <main class="main-content">
        <div class="auth-container">
            <!-- Panel de Login -->
            <section class="auth-panel login-panel active" id="login-panel">
                <div class="panel-header">
                    <h2 class="panel-title">¡Bienvenido de vuelta!</h2>
                    <p class="panel-subtitle">Inicia sesión para continuar tu aventura prehistórica</p>
                </div>
                
                <form method="POST" action="/login" class="auth-form" id="login-form">
                    <div class="form-group">
                        <label for="login-usuario" class="form-label">Usuario</label>
                        <input type="text" id="login-usuario" name="nombre_usuario" class="form-input" placeholder="Ingresa tu usuario" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="login-contraseña" class="form-label">Contraseña</label>
                        <input type="password" id="login-contraseña" name="contraseña" class="form-input" placeholder="Ingresa tu contraseña" required>
                    </div>
                    
                    <button type="submit" class="btn-primary">
                        Iniciar Sesión
                    </button>
                </form>
                
                <div class="panel-footer">
                    <p class="switch-text">¿No tienes cuenta?</p>
                    <button type="button" class="btn-switch" onclick="switchToRegister()">
                        Regístrate aquí
                    </button>
                </div>
            </section>

            <!-- Panel de Registro -->
            <section class="auth-panel register-panel" id="register-panel">
                <div class="panel-header">
                    <h2 class="panel-title">¡Únete a la aventura!</h2>
                    <p class="panel-subtitle">Crea tu cuenta y comienza a construir tu zoológico</p>
                </div>
                
                <form method="POST" action="/registro" class="auth-form" id="register-form">
                    <div class="form-group">
                        <label for="register-usuario" class="form-label">Usuario</label>
                        <input type="text" id="register-usuario" name="nombre_usuario" class="form-input" placeholder="Elige un nombre de usuario" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="register-email" class="form-label">Correo Electrónico</label>
                        <input type="email" id="register-email" name="email" class="form-input" placeholder="tu@email.com" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="register-contraseña" class="form-label">Contraseña</label>
                        <input type="password" id="register-contraseña" name="contraseña" class="form-input" placeholder="Crea una contraseña segura" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="register-confirmar" class="form-label">Confirmar Contraseña</label>
                        <input type="password" id="register-confirmar" name="confirmar_contraseña" class="form-input" placeholder="Confirma tu contraseña" required>
                    </div>
                    
                    <button type="submit" class="btn-primary" id="register-btn">
                        <span class="btn-text">Crear Cuenta</span>
                    </button>
                </form>
                
                <div class="panel-footer">
                    <p class="switch-text">¿Ya tienes cuenta?</p>
                    <button type="button" class="btn-switch" onclick="switchToLogin()">
                        Inicia sesión aquí
                    </button>
                </div>
            </section>
        </div>
    </main>

    <script>
        function switchToRegister() {
            document.getElementById('login-panel').classList.remove('active');
            document.getElementById('register-panel').classList.add('active');
        }

        function switchToLogin() {
            document.getElementById('register-panel').classList.remove('active');
            document.getElementById('login-panel').classList.add('active');
        }

        // Validación y manejo del formulario de registro
        document.getElementById('register-form').addEventListener('submit', function(e) {
            const password = document.getElementById('register-contraseña').value;
            const confirmPassword = document.getElementById('register-confirmar').value;
            const registerBtn = document.getElementById('register-btn');
            const btnText = registerBtn.querySelector('.btn-text');
            const btnIcon = registerBtn.querySelector('.btn-icon');
            
            // Validaciones
            if (password !== confirmPassword) {
                e.preventDefault();
                showMessage('Las contraseñas no coinciden', 'error');
                return false;
            }
            
            if (password.length < 6) {
                e.preventDefault();
                showMessage('La contraseña debe tener al menos 6 caracteres', 'error');
                return false;
            }
            
            // Feedback visual durante el envío
            registerBtn.disabled = true;
            btnText.textContent = 'Creando cuenta...';
            btnIcon.textContent = '⏳';
            registerBtn.style.opacity = '0.7';
        });

        // Función para mostrar mensajes
        function showMessage(text, type) {
            const messageDiv = document.createElement('div');
            messageDiv.className = `mensaje-${type}`;
            messageDiv.textContent = text;
            messageDiv.style.position = 'fixed';
            messageDiv.style.top = '20px';
            messageDiv.style.right = '20px';
            messageDiv.style.zIndex = '2000';
            messageDiv.style.padding = '1rem 1.5rem';
            messageDiv.style.borderRadius = '10px';
            messageDiv.style.fontWeight = '600';
            messageDiv.style.animation = 'slideIn 0.3s ease';
            
            if (type === 'error') {
                messageDiv.style.background = '#f44336';
                messageDiv.style.color = 'white';
                messageDiv.style.border = '2px solid #da190b';
            } else {
                messageDiv.style.background = '#4caf50';
                messageDiv.style.color = 'white';
                messageDiv.style.border = '2px solid #45a049';
            }
            
            document.body.appendChild(messageDiv);
            
            // Remover después de 5 segundos
            setTimeout(() => {
                messageDiv.remove();
            }, 5000);
        }

        // Animación de entrada
        document.addEventListener('DOMContentLoaded', function() {
            const panels = document.querySelectorAll('.auth-panel');
            panels.forEach((panel, index) => {
                panel.style.animationDelay = `${index * 0.1}s`;
            });
            
            // Si hay un mensaje de éxito, cambiar al panel de login
            const successMessage = document.querySelector('.mensaje-exito');
            if (successMessage) {
                setTimeout(() => {
                    switchToLogin();
                }, 1000);
            }
        });
    </script>
</body>
</html>