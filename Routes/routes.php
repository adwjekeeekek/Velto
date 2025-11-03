<?php
require_once RUTA_RAIZ . '/Controllers/AuthController.php';
require_once RUTA_RAIZ . '/Classes/Usuario.php';

function rutas(string $ruta, string $metodo = 'GET'): void
{
    switch ($ruta) {
        case '':
        case 'home':
        case 'inicio':
            include RUTA_VISTAS . '/index.php';
            break;

        case 'login':
        case 'iniciar-sesion': {
                $auth = new AuthController();
                $mensaje = null;

                if ($metodo === 'POST') {
                    $ident = $_POST['nombre_usuario'] ?? '';
                    $pass = $_POST['contraseña'] ?? '';

                    $resp = $auth->login($ident, $pass);
                    if ($resp['ok']) {
                        header('Location: /dashboard');
                        exit;
                    }
                    $mensaje = ['exito' => false, 'mensaje' => $resp['msg']];
                }

                if (Usuario::estaLogueado()) {
                    header('Location: /dashboard');
                    exit;
                }

                include RUTA_VISTAS . '/login.php';
                break;
            }

        case 'registro':
        case 'registrarse': {
                $auth = new AuthController();
                $mensaje = null;

                if ($metodo === 'POST') {
                    $nombre = $_POST['nombre_usuario'] ?? '';
                    $email = $_POST['email'] ?? '';
                    $pass = $_POST['contraseña'] ?? '';
                    $pass2 = $_POST['confirmar_contraseña'] ?? '';

                    if ($pass !== $pass2) {
                        $mensaje = ['exito' => false, 'mensaje' => 'Las contraseñas no coinciden'];
                    } else {
                        $resp = $auth->registrar($nombre, $email, $pass);
                        if ($resp['ok']) {
                            $mensaje = ['exito' => true, 'mensaje' => $resp['msg']];
                        } else {
                            $mensaje = ['exito' => false, 'mensaje' => $resp['msg']];
                        }
                    }
                }

                if (Usuario::estaLogueado()) {
                    header('Location: /dashboard');
                    exit;
                }

                include RUTA_VISTAS . '/login.php';
                break;
            }


        case 'tablero-nuevo':
        case 'new-board':
        case 'tablero2':
            Usuario::requerirLogin();
            include RUTA_VISTAS . '/tablero-nuevo.php';
            break;

        case 'dashboard':
        case 'panel':
        case 'menu':
            Usuario::requerirLogin();
            include RUTA_VISTAS . '/dashboard.php';
            break;

        

        case 'logout':
        case 'cerrar-sesion': {
                $auth = new AuthController();
                $auth->logout();
                header('Location: /login');
                exit;
            }

        case 'game':
        case 'juego':
        case 'tablero':
            Usuario::requerirLogin();
            include RUTA_VISTAS . '/tablero.php';
            break;



        case 'seguimiento':
        case 'tracking':
        case 'rastreo':
            Usuario::requerirLogin();
            include RUTA_VISTAS . '/tablero.php';
            break;

        default:
            http_response_code(404);
            echo "<h1>404 - Página no encontrada</h1><p>La página que buscas no existe.</p><a href='/'>Volver al inicio</a>";
            break;
    }
}
