<?php

require_once __DIR__ . '/../Classes/Partida.php';

class GameController
{
    private PDO $db;
    
    private array $planSeguimiento = [
        1 => ['recinto' => 'bosque_semejanza', 'especie' => null],
        2 => ['recinto' => 'bosque_semejanza', 'especie' => null],
        3 => ['recinto' => 'prado_diferencia', 'especie' => null],
        4 => ['recinto' => 'prado_diferencia', 'especie' => null],
        5 => ['recinto' => 'rio', 'especie' => null],
        6 => ['recinto' => 'rio', 'especie' => null],
        7 => ['recinto' => 'pradera_amor', 'especie' => null],
        8 => ['recinto' => 'pradera_amor', 'especie' => null],
        9 => ['recinto' => 'trio_frondoso', 'especie' => null],
        10 => ['recinto' => 'trio_frondoso', 'especie' => null],
        11 => ['recinto' => 'trio_frondoso', 'especie' => null],
        12 => ['recinto' => 'rey_selva', 'especie' => null],
    ];

    public function __construct(PDO $pdo)
    {
        $this->db = $pdo;
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }


    private function jugadorIdDeSesion(int $partidaId): ?int {
        $usuarioId = $_SESSION['usuario_id'] ?? null;
        if (!$usuarioId) return null;
        $st = $this->db->prepare("SELECT usuario_id FROM jug_partida WHERE partida_id=? AND usuario_id=? LIMIT 1");
        $st->execute([$partidaId, $usuarioId]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ? (int)$row['usuario_id'] : null;
    }

    private function obtenerPasoActual(int $partidaId): int {
        $st = $this->db->prepare("SELECT COUNT(*) FROM colocaciones WHERE partida_id = ?");
        $st->execute([$partidaId]);
        return (int)$st->fetchColumn() + 1;
    }

    private function buscarPartida(int $id): ?array
    {
        if ($id <= 0) return null;
        $st = $this->db->prepare("SELECT * FROM partidas WHERE id=? LIMIT 1");
        $st->execute([$id]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    private function jugadorActualId(int $partidaId): ?int
    {
        $st = $this->db->prepare("SELECT turno_id FROM partidas WHERE id=?");
        $st->execute([$partidaId]);
        return $st->fetchColumn() ?: null;
    }

    private function siguienteJugadorId(int $partidaId): ?int
    {
        $st = $this->db->prepare("
            SELECT jp.usuario_id 
            FROM jug_partida jp 
            WHERE jp.partida_id = ? 
            ORDER BY jp.orden ASC
        ");
        $st->execute([$partidaId]);
        $jugadores = $st->fetchAll(PDO::FETCH_COLUMN);
        
        if (empty($jugadores)) return null;
        
        $turnoActual = $this->jugadorActualId($partidaId);
        $indiceActual = array_search($turnoActual, $jugadores);
        
        if ($indiceActual === false) return $jugadores[0];
        
        $siguienteIndice = ($indiceActual + 1) % count($jugadores);
        return $jugadores[$siguienteIndice];
    }

    private function leerSlotsRecinto(int $partidaId, string $recinto): array
    {
        $st = $this->db->prepare("SELECT slot, especie FROM colocaciones WHERE partida_id=? AND recinto=? ORDER BY slot ASC");
        $st->execute([$partidaId, $recinto]);
        $res = [];
        foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $res[(int)$r['slot']] = $r['especie'];
        }
        return $res;
    }

    private function avanzarTurno(int $partidaId): void
    {
        $siguienteJugador = $this->siguienteJugadorId($partidaId);
        if (!$siguienteJugador) return;

        $p = $this->buscarPartida($partidaId);
        if (!$p) return;

        $primerJugador = $this->db->prepare("SELECT usuario_id FROM jug_partida WHERE partida_id = ? ORDER BY orden ASC LIMIT 1");
        $primerJugador->execute([$partidaId]);
        $primerJugadorId = $primerJugador->fetchColumn();

        $nuevaRonda = $p['ronda'];
        if ($siguienteJugador == $primerJugadorId && $p['turno_id'] != $primerJugadorId) {
            $nuevaRonda++;
        }

        $this->db->prepare("UPDATE partidas SET turno_id = ?, ronda = ? WHERE id = ?")
                 ->execute([$siguienteJugador, $nuevaRonda, $partidaId]);
    }

    private function obtenerEstadoTablero(int $partidaId): array
    {
        $st = $this->db->prepare("SELECT recinto, slot, especie FROM colocaciones WHERE partida_id = ?");
        $st->execute([$partidaId]);
        $colocaciones = $st->fetchAll(PDO::FETCH_ASSOC);
        
        require_once __DIR__ . '/../Classes/Dinosaurio.php';
        
        $tablero = [];
        foreach ($colocaciones as $col) {
            $recinto = $col['recinto'];
            $slot = (int)$col['slot'];
            $especie = Dinosaurio::normalizar($col['especie']);
            
            if (!isset($tablero[$recinto])) {
                $tablero[$recinto] = [];
            }
            $tablero[$recinto][$slot] = $especie;
        }
        
        return $tablero;
    }

    private function actualizarPuntuacionJugador(int $partidaId, int $jugadorId): void
    {
        $st = $this->db->prepare("SELECT recinto, especie FROM colocaciones WHERE partida_id = ?");
        $st->execute([$partidaId]);
        $colocaciones = $st->fetchAll(PDO::FETCH_ASSOC);
        
        $partida = new Partida($partidaId, 'digitalizado');
        $puntuacion = $partida->puntuar($colocaciones);
        
        $st = $this->db->prepare("UPDATE jug_partida SET puntos = ? WHERE partida_id = ? AND usuario_id = ?");
        $st->execute([$puntuacion['total'], $partidaId, $jugadorId]);
    }


    public function crearPartida(string $modo, ?array $jugadores = null): array
    {
        $modosValidos = ['seguimiento', 'digitalizado'];
        $modo = in_array($modo, $modosValidos) ? $modo : 'seguimiento';
        
        if (!is_array($jugadores) || empty($jugadores)) {
            $jugadores = [['id' => 1, 'nombre' => 'Jugador', 'usuario_id' => $_SESSION['usuario_id'] ?? 1]];
        }

        $this->db->beginTransaction();

        // En modo seguimiento, el estado inicial es 'colocar' (no se usa dado)
        $estadoInicial = ($modo === 'seguimiento') ? 'colocar' : 'dado';
        $stmt = $this->db->prepare("INSERT INTO partidas (usuario_id, modo, ronda, estado, dado, turno_id) VALUES (?, ?, 1, ?, NULL, NULL)");
        $stmt->execute([$_SESSION['usuario_id'] ?? 1, $modo, $estadoInicial]);
        $partidaId = (int)$this->db->lastInsertId();

        $ins = $this->db->prepare("INSERT INTO jug_partida (partida_id, usuario_id, nombre, orden, puntos) VALUES (?, ?, ?, ?, 0)");
        
        foreach ($jugadores as $j) {
            $uid = isset($j['usuario_id']) ? (int)$j['usuario_id'] : ($_SESSION['usuario_id'] ?? 1);
            $nombre = $j['nombre'] ?? 'Jugador';
            $orden = $j['id'] ?? 1;
            $ins->execute([$partidaId, $uid, $nombre, $orden]);
        }
        
        $primerJugador = $this->db->prepare("SELECT usuario_id FROM jug_partida WHERE partida_id = ? ORDER BY orden ASC LIMIT 1");
        $primerJugador->execute([$partidaId]);
        $primerJugadorId = $primerJugador->fetchColumn();
        
        if ($primerJugadorId) {
            $this->db->prepare("UPDATE partidas SET turno_id = ? WHERE id = ?")
                     ->execute([$primerJugadorId, $partidaId]);
        }
        
        $this->db->commit();
        return ['ok' => true, 'partida_id' => $partidaId];
    }

    public function lanzarDado(int $partidaId): array
    {
        $p = $this->buscarPartida($partidaId);
        if (!$p) return ['ok' => false, 'msg' => 'Partida no encontrada'];

        $jugadorId = $this->jugadorIdDeSesion($partidaId);
        if (!$jugadorId) {
            return ['ok' => false, 'msg' => 'No estás en esta partida'];
        }

        if ($p['modo'] !== 'digitalizado') {
            return ['ok' => false, 'msg' => 'Solo en modo digitalizado se usa dado'];
        }
        
        if ($p['estado'] !== 'dado') {
            return ['ok' => false, 'msg' => 'No es momento de tirar el dado'];
        }
        
        if ($p['turno_id'] != $jugadorId) {
            return ['ok' => false, 'msg' => 'No es tu turno'];
        }

        $caras = ['bosque', 'llanura', 'baños', 'cafeteria', 'vacio', 'sin_trex'];
        $cara = $caras[array_rand($caras)];

        $st = $this->db->prepare("UPDATE partidas SET dado=?, estado='colocar' WHERE id=?");
        $st->execute([$cara, $partidaId]);

        return ['ok' => true, 'dado' => $cara];
    }

    public function colocar(int $partidaId, string $recinto, int $slot, string $especie): array
    {
        $p = $this->buscarPartida($partidaId);
        if (!$p) return ['ok' => false, 'msg' => 'Partida no encontrada'];

        $jugadorId = $_SESSION['usuario_id'] ?? null;
        if (!$jugadorId) return ['ok' => false, 'msg' => 'No hay sesión activa'];

        if ($p['turno_id'] != $jugadorId) {
            return ['ok' => false, 'msg' => 'No es tu turno'];
        }

        if ($p['modo'] === 'digitalizado' && $p['estado'] !== 'colocar') {
            return ['ok' => false, 'msg' => 'Primero tira el dado'];
        }
        
        if ($p['modo'] === 'seguimiento' && $p['estado'] !== 'colocar') {
            return ['ok' => false, 'msg' => 'No es momento de colocar'];
        }

        $recinto = trim($recinto);
        $especie = strtolower(trim($especie));
        
        require_once __DIR__ . '/../Classes/Dinosaurio.php';
        $especie = Dinosaurio::normalizar($especie);

        $limites = [
            'bosque_semejanza' => 4,
            'prado_diferencia' => 4,
            'pradera_amor' => 4,
            'trio_frondoso' => 3,
            'rey_selva' => 1,
            'isla_solitaria' => 1,
            'rio' => 8,
        ];

        $limite = $limites[$recinto] ?? 0;
        if ($slot < 0 || $slot >= $limite) {
            return ['ok' => false, 'msg' => 'Slot fuera de rango'];
        }

        $st = $this->db->prepare("SELECT 1 FROM colocaciones WHERE partida_id=? AND recinto=? AND slot=?");
        $st->execute([$partidaId, $recinto, $slot]);
        if ($st->fetchColumn()) {
            return ['ok' => false, 'msg' => 'Slot ocupado'];
        }

        if ($p['modo'] === 'seguimiento') {
            $paso = $this->obtenerPasoActual($partidaId);
            $planPaso = $this->planSeguimiento[$paso] ?? null;
            
            if (!$planPaso) {
                return ['ok' => false, 'msg' => 'Tutorial completado'];
            }
            
            if ($recinto !== $planPaso['recinto']) {
                return ['ok' => false, 'msg' => "Debes colocar en: {$planPaso['recinto']}"];
            }
            
            if ($planPaso['especie'] && $especie !== $planPaso['especie']) {
                return ['ok' => false, 'msg' => "Debes colocar: {$planPaso['especie']}"];
            }
        }

        // Validar reglas usando Partida
        $partida = new Partida($partidaId, $p['modo']);
        $tablero = $this->obtenerEstadoTablero($partidaId);
        
        $validacionRecinto = $partida->validaRecinto($recinto, $slot, $especie, $tablero, $limite);
        if (!$validacionRecinto[0]) {
            return ['ok' => false, 'msg' => $validacionRecinto[1]];
        }
        
        if ($p['modo'] === 'digitalizado' && $p['dado']) {
            $validacionDado = $partida->validaCara($p['dado'], $recinto, $slot, $especie, $tablero);
            if (!$validacionDado[0]) {
                return ['ok' => false, 'msg' => $validacionDado[1]];
            }
        }

        $ins = $this->db->prepare("INSERT INTO colocaciones (partida_id, recinto, especie, slot) VALUES (?, ?, ?, ?)");
        $ins->execute([$partidaId, $recinto, $especie, $slot]);

        $this->actualizarPuntuacionJugador($partidaId, $jugadorId);

        $jugadaActual = $this->obtenerPasoActual($partidaId);
        if ($jugadaActual > 12) {
            $this->db->prepare("UPDATE partidas SET estado='fin' WHERE id=?")->execute([$partidaId]);
            return ['ok' => true, 'msg' => 'Partida completada - 12 jugadas terminadas'];
        }
        
        if ($p['modo'] === 'seguimiento') {
            $paso = $this->obtenerPasoActual($partidaId);
            $totalPasos = count($this->planSeguimiento);
            
            if ($paso > $totalPasos) {
                $this->db->prepare("UPDATE partidas SET estado='fin' WHERE id=?")->execute([$partidaId]);
                return ['ok' => true, 'msg' => 'Tutorial completado - Puntuación máxima alcanzada'];
            }
        }

        if ($p['modo'] === 'digitalizado') {
            $this->db->prepare("UPDATE partidas SET dado=NULL, estado='dado' WHERE id=?")->execute([$partidaId]);
        }
        $this->avanzarTurno($partidaId);

        return ['ok' => true, 'msg' => 'Dinosaurio colocado correctamente'];
    }

    public function colocaciones(int $partidaId): array
    {
        if ($partidaId <= 0) return ['ok' => false, 'msg' => 'partida_id faltante'];

        $p = $this->buscarPartida($partidaId);
        if (!$p) return ['ok' => false, 'msg' => 'Partida no encontrada'];

        $st = $this->db->prepare("SELECT recinto, slot, especie FROM colocaciones WHERE partida_id = ? ORDER BY recinto, slot");
        $st->execute([$partidaId]);
        $rows = $st->fetchAll(PDO::FETCH_ASSOC);

        return ['ok' => true, 'colocaciones' => $rows];
    }

    public function puntaje(int $partidaId): array
    {
        $p = $this->buscarPartida($partidaId);
        if (!$p) return ['ok' => false, 'msg' => 'Partida no encontrada'];

        $st = $this->db->prepare("SELECT usuario_id, nombre, puntos FROM jug_partida WHERE partida_id = ? ORDER BY puntos DESC");
        $st->execute([$partidaId]);
        $jugadores = $st->fetchAll(PDO::FETCH_ASSOC);

        return ['ok' => true, 'jugadores' => $jugadores];
    }

    public function finTurno(int $partidaId, int $jugadorId): array
    {
        $p = $this->buscarPartida($partidaId);
        if (!$p) return ['ok' => false, 'msg' => 'Partida no encontrada'];

        if ($p['turno_id'] != $jugadorId) {
            return ['ok' => false, 'msg' => 'No es tu turno'];
        }

        $this->avanzarTurno($partidaId);
        return ['ok' => true];
    }

    public function estadoPartida(int $partidaId): array
    {
        $p = $this->buscarPartida($partidaId);
        if (!$p) return ['ok' => false, 'msg' => 'Partida no encontrada'];

        $st = $this->db->prepare("SELECT recinto, slot, especie FROM colocaciones WHERE partida_id = ? ORDER BY recinto, slot");
        $st->execute([$partidaId]);
        $coloc = $st->fetchAll(PDO::FETCH_ASSOC);

        $st = $this->db->prepare("SELECT usuario_id, nombre, orden, puntos FROM jug_partida WHERE partida_id = ? ORDER BY orden ASC");
        $st->execute([$partidaId]);
        $jugadores = $st->fetchAll(PDO::FETCH_ASSOC);

        $jugadorEnTurno = null;
        if ($p['turno_id']) {
            foreach ($jugadores as $j) {
                if ($j['usuario_id'] == $p['turno_id']) {
                    $jugadorEnTurno = $j;
                    break;
                }
            }
        }

        $dado = ($p['modo'] === 'digitalizado') ? ($p['dado'] ?? null) : null;
        
        $jugadaActual = count($coloc) + 1;
        $rondaDerivada = $jugadaActual <= 6 ? 1 : 2;
        
        $partidaTerminada = $jugadaActual > 12;

        $resultado = [
            'ok' => true,
            'partida_id' => (int)$p['id'],
            'modo' => (string)$p['modo'],
            'ronda' => $rondaDerivada,
            'jugada' => $jugadaActual,
            'estado' => $partidaTerminada ? 'fin' : (string)$p['estado'],
            'dado' => $dado,
            'turno_id' => (int)($p['turno_id'] ?? 0),
            'jugador_en_turno' => $jugadorEnTurno,
            'jugadores' => $jugadores,
            'colocaciones' => $coloc
        ];
        

        // Agregar información del tutorial para modo seguimiento
        if ($p['modo'] === 'seguimiento') {
            $paso = count($coloc) + 1;
            $totalPasos = count($this->planSeguimiento);
            $recintoPermitido = $this->planSeguimiento[$paso]['recinto'] ?? null;
            $especiePermitida = $this->planSeguimiento[$paso]['especie'] ?? null;
            
            $resultado['paso'] = $paso;
            $resultado['total_pasos'] = $totalPasos;
            $resultado['recinto_permitido'] = $recintoPermitido;
            $resultado['especie_permitida'] = $especiePermitida;
        }

        return $resultado;
    }

}
