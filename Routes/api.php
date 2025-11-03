<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../Database/config.php';
require_once __DIR__ . '/../Controllers/GameController.php';
require_once __DIR__ . '/../Controllers/AuthController.php';

function json_ok(array $data = [], string $msg = ''): never {
    http_response_code(200);
    echo json_encode(['ok' => true] + $data + ['msg' => $msg], JSON_UNESCAPED_UNICODE);
    exit;
}

function json_err(string $msg, int $code = 422, array $extra = []): never {
    http_response_code($code);
    echo json_encode(['ok' => false, 'msg' => $msg] + $extra, JSON_UNESCAPED_UNICODE);
    exit;
}

function api_router(string $ruta, string $metodo = 'GET'): void {

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

            case 'actualizar_nombre':
                echo json_encode($auth->actualizarNombre($_POST['nombre'] ?? ''));
                break;

            case 'actualizar_email':
                echo json_encode($auth->actualizarEmail($_POST['email'] ?? ''));
                break;

            case 'actualizar_password':
                echo json_encode($auth->actualizarPassword(
                    $_POST['password_actual'] ?? '',
                    $_POST['password_nuevo'] ?? ''
                ));
                break;

            case 'simular_sesion':
                $_SESSION['usuario_id'] = 1;
                echo json_encode(['ok' => true, 'msg' => 'Sesión simulada']);
                break;

            case 'crear_partida': {
                $modo = $_POST['modo'] ?? 'digitalizado';
                if (!in_array($modo, ['digitalizado', 'seguimiento'])) {
                    $modo = 'digitalizado';
                }
                
                $jugadores = null;
                if (isset($_POST['jugadores'])) {
                    $jugadores = json_decode($_POST['jugadores'], true);
                }
                
                try {
                    $resultado = $game->crearPartida($modo, $jugadores);
                    
                    if ($resultado['ok']) {
                        json_ok(['partida_id' => $resultado['partida_id']]);
                    } else {
                        json_err($resultado['msg'] ?? 'No se pudo crear la partida', 500);
                    }
                } catch (Exception $e) {
                    error_log("Error creando partida: " . $e->getMessage());
                    json_err('No se pudo crear la partida', 500);
                }
                break;
            }

            case 'lanzar_dado':
                echo json_encode($game->lanzarDado(
                    (int)($_POST['partida_id'] ?? 0)
                ));
                break;

            case 'colocar': {
                $partida_id = (int)($_POST['partida_id'] ?? 0);
                $recinto = trim($_POST['recinto'] ?? '');
                $slot = (int)($_POST['slot'] ?? -1);
                $especie = trim($_POST['especie'] ?? '');
                
                if ($partida_id <= 0) {
                    json_err('partida_id inválido', 400);
                }
                
                if (empty($recinto)) {
                    json_err('recinto requerido', 400);
                }
                
                if ($slot < 0) {
                    json_err('slot inválido', 400);
                }
                
                if (empty($especie)) {
                    json_err('especie requerida', 400);
                }
                
                try {
                    $resultado = $game->colocar($partida_id, $recinto, $slot, $especie);
                    
                    if ($resultado['ok']) {
                        json_ok();
                    } else {
                        $msg = $resultado['msg'] ?? 'Error desconocido';
                        
                        if (strpos($msg, 'no encontrada') !== false) {
                            json_err($msg, 404);
                        } elseif (strpos($msg, 'ya está ocupado') !== false || strpos($msg, 'ocupado') !== false) {
                            json_err($msg, 409);
                        } elseif (strpos($msg, 'inválido') !== false || strpos($msg, 'inválida') !== false) {
                            json_err($msg, 422);
                        } elseif (strpos($msg, 'sesión') !== false) {
                            json_err($msg, 401);
                        } else {
                            json_err($msg, 422);
                        }
                    }
                    
                } catch (PDOException $e) {
                    error_log("Error PDO en colocar: " . $e->getMessage());
                    
                    if ($e->getCode() == 23000) {
                        json_err('Ese slot ya está ocupado', 409);
                    } elseif ($e->getCode() == 1452) {
                        json_err('Partida o datos inválidos', 422);
                    } else {
                        json_err('Error de base de datos', 500);
                    }
                    
                } catch (Exception $e) {
                    error_log("Error inesperado en colocar: " . $e->getMessage());
                    json_err('Error interno del servidor', 500);
                }
                break;
            }

            case 'estado_partida': {
                $partida_id = (int)($_GET['partida_id'] ?? $_POST['partida_id'] ?? 0);
                if ($partida_id <= 0) {
                    json_err('partida_id requerido', 400);
                }
                
                $stmt = $pdo->prepare("
                    SELECT modo, jugada, estado, dado, turno_id, ronda, usuario_id,
                           ronda_actual, turno_actual, jugadores_colocaron
                    FROM partidas
                    WHERE id = :pid
                ");
                $stmt->execute([':pid' => $partida_id]);
                $partida = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$partida) {
                    json_err('Partida no encontrada', 404);
                }
                
                $stmtJugadores = $pdo->prepare("
                    SELECT id, usuario_id, nombre, orden, puntos
                    FROM jugadores_partida
                    WHERE partida_id = :pid
                    ORDER BY orden
                ");
                $stmtJugadores->execute([':pid' => $partida_id]);
                $jugadores = $stmtJugadores->fetchAll(PDO::FETCH_ASSOC);
                
                if (empty($jugadores) && $partida['usuario_id']) {
                    $stmtUsuario = $pdo->prepare("SELECT nombre FROM usuarios WHERE id = :uid");
                    $stmtUsuario->execute([':uid' => $partida['usuario_id']]);
                    $usuario = $stmtUsuario->fetch(PDO::FETCH_ASSOC);
                    
                    if ($usuario) {
                        $stmtInsert = $pdo->prepare("
                            INSERT INTO jugadores_partida (partida_id, usuario_id, nombre, orden)
                            VALUES (:pid, :uid, :nombre, 1)
                        ");
                        $stmtInsert->execute([
                            ':pid' => $partida_id,
                            ':uid' => $partida['usuario_id'],
                            ':nombre' => $usuario['nombre']
                        ]);
                        
                        $stmtJugadores->execute([':pid' => $partida_id]);
                        $jugadores = $stmtJugadores->fetchAll(PDO::FETCH_ASSOC);
                    }
                }
                
                $jugadorEnTurno = null;
                if ($partida['turno_id']) {
                    foreach ($jugadores as $j) {
                        if ($j['id'] == $partida['turno_id']) {
                            $jugadorEnTurno = $j;
                            break;
                        }
                    }
                } else if (!empty($jugadores)) {
                    $jugadorEnTurno = $jugadores[0];
                }
                
                $miMano = [];
                $jugadorEnTurnoId = $partida['turno_id'] ?? null;
                
                if ($jugadorEnTurnoId) {
                    try {
                        require_once __DIR__ . '/../Classes/GestorManos.php';
                        $gestorManos = new GestorManos($pdo);
                        $miMano = $gestorManos->obtenerMano($partida_id, $jugadorEnTurnoId);
                    } catch (Exception $e) {
                        $miMano = [];
                    }
                }
                
                $colocaron = json_decode($partida['jugadores_colocaron'] ?? '[]', true);
                if (!is_array($colocaron)) $colocaron = [];
                
                $jugadoresPendientes = [];
                foreach ($jugadores as $j) {
                    if (!in_array($j['id'], $colocaron)) {
                        $jugadoresPendientes[] = $j['id'];
                    }
                }
                
                json_ok([
                    'ok' => true,
                    'partida_id' => $partida_id,
                    'modo' => $partida['modo'],
                    'estado' => $partida['estado'] ?? 'colocar',
                    'dado' => $partida['dado'],
                    'ronda' => (int)($partida['ronda_actual'] ?? 1),
                    'jugada' => (int)($partida['turno_actual'] ?? 1),
                    'turno_id' => $partida['turno_id'],
                    'jugador_en_turno' => $jugadorEnTurno,
                    'jugadores' => $jugadores,
                    'mi_mano' => $miMano,
                    'jugadores_pendientes' => $jugadoresPendientes,
                    'finalizada' => ($partida['estado'] === 'fin')
                ]);
                break;
            }

            case 'colocaciones': {
                $partida_id = (int)($_GET['partida_id'] ?? $_POST['partida_id'] ?? 0);
                if ($partida_id <= 0) {
                    json_err('partida_id requerido', 400);
                }
                
                // Obtener el turno actual
                $stmtPartida = $pdo->prepare("SELECT turno_id FROM partidas WHERE id = :pid");
                $stmtPartida->execute([':pid' => $partida_id]);
                $turnoId = $stmtPartida->fetchColumn();
                
                // Devolver SOLO las colocaciones del jugador en turno (hot-seat)
                $stmt = $pdo->prepare("
                    SELECT recinto, slot, especie
                    FROM colocaciones
                    WHERE partida_id = :pid AND jugador_id = :jid
                    ORDER BY creado ASC
                ");
                $stmt->execute([':pid' => $partida_id, ':jid' => $turnoId]);
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                json_ok(['colocaciones' => $rows, 'jugador_id' => $turnoId]);
                break;
            }

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
