<?php

require_once __DIR__ . '/../Database/config.php';
require_once __DIR__ . '/../Controllers/GameController.php';
require_once __DIR__ . '/../Controllers/AuthController.php';

function api_router(string $ruta, string $metodo = 'GET'): void {
    if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
    }

    $partes = array_values(array_filter(explode('/', $ruta))); 
    $accion = $_GET['accion'] ?? $_POST['accion'] ?? ($partes[1] ?? '');

    $pdo  = getPDO();
    $game = new GameController($pdo);
    $auth = new AuthController();

    try {
        switch ($accion) {
            case 'registrar':
                echo json_encode($auth->registrar($_POST['nombre'] ?? '', $_POST['email'] ?? '', $_POST['password'] ?? ''));
                break;

            case 'login':
                echo json_encode($auth->login($_POST['email'] ?? '', $_POST['password'] ?? ''));
                break;

            case 'logout':
                $auth->logout();
                echo json_encode(['ok' => true, 'msg' => 'Sesión cerrada']);
                break;

            case 'usuario_actual':
                echo json_encode(['ok' => true, 'usuario' => $auth->usuarioActual()]);
                break;

            case 'crear_partida': {
                $modo = $_POST['modo'] ?? 'seguimiento';
                $jugadores = $_POST['jugadores'] ?? null;
                if (is_string($jugadores)) {
                    $tmp = json_decode($jugadores, true);
                    if (json_last_error() === JSON_ERROR_NONE) $jugadores = $tmp;
                }
                echo json_encode($game->crearPartida($modo, $jugadores));
                break;
            }

            case 'lanzar_dado':
                echo json_encode($game->lanzarDado(
                    (int)($_POST['partida_id'] ?? 0)
                ));
                break;

            case 'colocar':
                echo json_encode($game->colocar(
                    (int)($_POST['partida_id'] ?? 0),
                    $_POST['recinto'] ?? '',
                    (int)($_POST['slot'] ?? -1),
                    $_POST['especie'] ?? ''
                ));
                break;

            case 'estado_partida':
                echo json_encode($game->estadoPartida((int)($_GET['partida_id'] ?? $_POST['partida_id'] ?? 0)));
                break;

            case 'colocaciones':
                echo json_encode($game->colocaciones((int)($_GET['partida_id'] ?? $_POST['partida_id'] ?? 0)));
                break;

            case 'puntaje':
                echo json_encode($game->puntaje((int)($_GET['partida_id'] ?? $_POST['partida_id'] ?? 0)));
                break;

            case 'fin_turno':
                echo json_encode($game->finTurno(
                    (int)($_POST['partida_id'] ?? 0),
                    (int)($_POST['jugador_id'] ?? 0)
                ));
                break;

            case '':
                echo json_encode(['ok' => true, 'msg' => 'API OK']);
                break;

            default:
                http_response_code(404);
                echo json_encode(['ok' => false, 'msg' => 'Acción desconocida']);
        }
    } catch (Throwable $e) {
        http_response_code(500);
        error_log("Error en API: " . $e->getMessage() . " en " . $e->getFile() . ":" . $e->getLine());
        echo json_encode(['ok' => false, 'msg' => 'Error interno del servidor']);
    }
}
