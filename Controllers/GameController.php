<?php

require_once __DIR__ . '/../Classes/Partida.php';
require_once __DIR__ . '/../Classes/BolsaDinosaurios.php';
require_once __DIR__ . '/../Classes/GestorManos.php';

class GameController
{
    private PDO $db;
    private GestorManos $gestorManos;
    
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
        $this->gestorManos = new GestorManos($pdo);
    }


    private function jugadorIdDeSesion(int $partidaId): ?int {
        $usuarioId = $_SESSION['usuario_id'] ?? null;
        if (!$usuarioId) return null;
        
        $st = $this->db->prepare("SELECT turno_id FROM partidas WHERE id=? LIMIT 1");
        $st->execute([$partidaId]);
        $turnoId = $st->fetchColumn();
        
        if ($turnoId) {
            $verify = $this->db->prepare("SELECT id FROM jugadores_partida WHERE id=? AND partida_id=? LIMIT 1");
            $verify->execute([$turnoId, $partidaId]);
            if ($verify->fetch()) {
                return (int)$turnoId;
            }
        }
        
        return null;
    }

    private function obtenerPasoActual(int $partidaId): int {
        $st = $this->db->prepare("SELECT COUNT(*) FROM colocaciones WHERE partida_id = ?");
        $st->execute([$partidaId]);
        return (int)$st->fetchColumn() + 1;
    }

    private function buscarPartida(int $id): ?array
    {
        if ($id <= 0) return null;
        $st = $this->db->prepare("
            SELECT id, usuario_id, modo, jugada, estado, creado, dado, turno_id, ronda, 
                   bolsa, ronda_actual, turno_actual, jugadores_colocaron 
            FROM partidas 
            WHERE id=? LIMIT 1
        ");
        $st->execute([$id]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    private function jugadorActualId(int $partidaId): ?int
    {
        $st = $this->db->prepare("SELECT turno_id FROM partidas WHERE id = ? LIMIT 1");
        $st->execute([$partidaId]);
        $turnoId = $st->fetchColumn();
        return $turnoId ? (int)$turnoId : null;
    }

    private function siguienteJugadorId(int $partidaId): ?int
    {
        $st = $this->db->prepare("
            SELECT jp.id 
            FROM jugadores_partida jp 
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

        $primerJugador = $this->db->prepare("SELECT id FROM jugadores_partida WHERE partida_id = ? ORDER BY orden ASC LIMIT 1");
        $primerJugador->execute([$partidaId]);
        $primerJugadorId = $primerJugador->fetchColumn();

        $nuevaRonda = $p['ronda'] ?? 1;
        $nuevaJugada = $p['jugada'] ?? 1;
        
        if ($siguienteJugador == $primerJugadorId) {
            $nuevaRonda++;
            $nuevaJugada = 1;
        } else {
            $nuevaJugada++;
        }

        $this->db->prepare("UPDATE partidas SET turno_id = ?, ronda = ?, jugada = ? WHERE id = ?")
                 ->execute([$siguienteJugador, $nuevaRonda, $nuevaJugada, $partidaId]);
    }

    private function obtenerEstadoTablero(int $partidaId, ?int $jugadorId = null): array
    {
        if ($jugadorId !== null) {
            $st = $this->db->prepare("SELECT recinto, slot, especie FROM colocaciones WHERE partida_id = ? AND jugador_id = ?");
            $st->execute([$partidaId, $jugadorId]);
        } else {
            $st = $this->db->prepare("SELECT recinto, slot, especie FROM colocaciones WHERE partida_id = ?");
            $st->execute([$partidaId]);
        }
        
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
        try {
            $st = $this->db->prepare("SELECT recinto, especie FROM colocaciones WHERE partida_id = ? AND jugador_id = ?");
            $st->execute([$partidaId, $jugadorId]);
            $colocaciones = $st->fetchAll(PDO::FETCH_ASSOC);
            
            $partida = new Partida($partidaId, 'digitalizado');
            $puntuacion = $partida->puntuar($colocaciones);
            
            $st = $this->db->prepare("UPDATE jugadores_partida SET puntos = ? WHERE id = ?");
            $st->execute([$puntuacion['total'], $jugadorId]);
        } catch (Exception $e) {
        }
    }

    private function obtenerOrdenJugadores(int $partidaId): array
    {
        $stmt = $this->db->prepare("
            SELECT id 
            FROM jugadores_partida 
            WHERE partida_id = ? 
            ORDER BY orden ASC
        ");
        $stmt->execute([$partidaId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    private function marcarJugadorColoco(int $partidaId, int $jugadorId): void
    {
        $partida = $this->buscarPartida($partidaId);
        $colocaron = json_decode($partida['jugadores_colocaron'] ?? '[]', true);
        if (!is_array($colocaron)) $colocaron = [];
        
        if (!in_array($jugadorId, $colocaron)) {
            $colocaron[] = $jugadorId;
        }
        
        $stmt = $this->db->prepare("UPDATE partidas SET jugadores_colocaron = ? WHERE id = ?");
        $stmt->execute([json_encode($colocaron), $partidaId]);
    }

    private function todosColocaron(int $partidaId): bool
    {
        $partida = $this->buscarPartida($partidaId);
        $colocaron = json_decode($partida['jugadores_colocaron'] ?? '[]', true);
        if (!is_array($colocaron)) $colocaron = [];
        
        $ordenJugadores = $this->obtenerOrdenJugadores($partidaId);
        return count($colocaron) === count($ordenJugadores);
    }

    private function limpiarMarcadoresTurno(int $partidaId): void
    {
        $stmt = $this->db->prepare("UPDATE partidas SET jugadores_colocaron = '[]' WHERE id = ?");
        $stmt->execute([$partidaId]);
    }

    private function avanzarTurnoYRonda(int $partidaId): void
    {
        $partida = $this->buscarPartida($partidaId);
        $rondaActual = (int)($partida['ronda_actual'] ?? 1);
        $turnoActual = (int)($partida['turno_actual'] ?? 1);
        
        $ordenJugadores = $this->obtenerOrdenJugadores($partidaId);
        $numJugadores = count($ordenJugadores);
        
        $turnosPorRonda = ($numJugadores === 2) ? 3 : 6;
        $rondasTotales = ($numJugadores === 2) ? 4 : 2;
        
        if ($numJugadores === 2) {
            $bolsa = BolsaDinosaurios::deserializar($partida['bolsa']);
            
            foreach ($ordenJugadores as $jugadorId) {
                $mano = $this->gestorManos->obtenerMano($partidaId, $jugadorId);
                
                if (count($mano) > 0) {
                    $dinoADevolver = $mano[0];
                    $this->gestorManos->quitarDinosaurio($partidaId, $jugadorId, $dinoADevolver);
                    $bolsa[] = $dinoADevolver;
                }
            }
            
            $stmt = $this->db->prepare("UPDATE partidas SET bolsa = ? WHERE id = ?");
            $stmt->execute([BolsaDinosaurios::serializar($bolsa), $partidaId]);
        }
        
        if ($numJugadores > 1) {
            $this->gestorManos->rotarManos($partidaId, $ordenJugadores);
        }
        
        $this->limpiarMarcadoresTurno($partidaId);
        
        if ($turnoActual >= $turnosPorRonda) {
            if ($rondaActual >= $rondasTotales) {
                $stmt = $this->db->prepare("
                    UPDATE partidas 
                    SET estado = 'fin', ronda_actual = ?, turno_actual = ? 
                    WHERE id = ?
                ");
                $stmt->execute([$rondasTotales, $turnosPorRonda, $partidaId]);
            } else {
                $nuevaRonda = $rondaActual + 1;
                $this->iniciarNuevaRonda($partidaId, $nuevaRonda);
            }
        } else {
            $nuevoTurno = $turnoActual + 1;
            $stmt = $this->db->prepare("UPDATE partidas SET turno_actual = ? WHERE id = ?");
            $stmt->execute([$nuevoTurno, $partidaId]);
        }
    }

    private function iniciarNuevaRonda(int $partidaId, int $numeroRonda): void
    {
        $partida = $this->buscarPartida($partidaId);
        $bolsa = BolsaDinosaurios::deserializar($partida['bolsa']);
        $ordenJugadores = $this->obtenerOrdenJugadores($partidaId);
        
        try {
            $this->gestorManos->repartirATodos($partidaId, $ordenJugadores, $bolsa, 6);
            
            $stmt = $this->db->prepare("
                UPDATE partidas 
                SET bolsa = ?, ronda_actual = ?, turno_actual = 1, jugadores_colocaron = '[]'
                WHERE id = ?
            ");
            $stmt->execute([
                BolsaDinosaurios::serializar($bolsa),
                $numeroRonda,
                $partidaId
            ]);
        } catch (RuntimeException $e) {
            throw new RuntimeException("Error iniciando ronda $numeroRonda: " . $e->getMessage());
        }
    }

    public function crearPartida(string $modo, ?array $jugadores = null): array
    {
        try {
        $modosValidos = ['seguimiento', 'digitalizado'];
        $modo = in_array($modo, $modosValidos) ? $modo : 'seguimiento';
        
        if (!is_array($jugadores) || empty($jugadores)) {
            $jugadores = [['id' => 1, 'nombre' => 'Jugador', 'usuario_id' => $_SESSION['usuario_id'] ?? 1]];
        }

        $this->db->beginTransaction();

        $estadoInicial = ($modo === 'seguimiento') ? 'colocar' : 'dado';
        $stmt = $this->db->prepare("INSERT INTO partidas (usuario_id, modo, estado, dado) VALUES (?, ?, ?, NULL)");
        $stmt->execute([$_SESSION['usuario_id'] ?? 1, $modo, $estadoInicial]);
        $partidaId = (int)$this->db->lastInsertId();

        $ins = $this->db->prepare("INSERT INTO jugadores_partida (partida_id, usuario_id, nombre, orden, puntos) VALUES (?, ?, ?, ?, 0)");
        
        $usuarioCreador = $_SESSION['usuario_id'] ?? 1;
        
        foreach ($jugadores as $index => $j) {
            $nombre = $j['nombre'] ?? 'Jugador ' . ($index + 1);
            $orden = $j['id'] ?? ($index + 1);
            
            $ins->execute([$partidaId, $usuarioCreador, $nombre, $orden]);
        }
        
        $primerJugador = $this->db->prepare("SELECT id FROM jugadores_partida WHERE partida_id = ? ORDER BY orden ASC LIMIT 1");
        $primerJugador->execute([$partidaId]);
        $primerJugadorId = $primerJugador->fetchColumn();
        
        if ($primerJugadorId) {
            $this->db->prepare("UPDATE partidas SET turno_id = ? WHERE id = ?")
                     ->execute([$primerJugadorId, $partidaId]);
        }
        
        $numJugadores = count($jugadores);
        $bolsa = BolsaDinosaurios::construir($numJugadores);
        $ordenJugadores = $this->obtenerOrdenJugadores($partidaId);
        
        $this->gestorManos->repartirATodos($partidaId, $ordenJugadores, $bolsa, 6);
        
        try {
            $stmt = $this->db->prepare("
                UPDATE partidas 
                SET bolsa = ?, ronda_actual = 1, turno_actual = 1, jugadores_colocaron = '[]'
                WHERE id = ?
            ");
            $stmt->execute([BolsaDinosaurios::serializar($bolsa), $partidaId]);
        } catch (PDOException $e) {
            if ($e->getCode() == '42S22') {
                throw new RuntimeException("Schema de BD desactualizado. Ejecuta: Database/update_schema_bolsa.sql");
            }
            throw $e;
        }
        
        $this->db->commit();
        return ['ok' => true, 'partida_id' => $partidaId];
        
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return ['ok' => false, 'msg' => 'Error al crear partida: ' . $e->getMessage()];
        }
    }

    public function lanzarDado(int $partidaId): array
    {
        $p = $this->buscarPartida($partidaId);
        if (!$p) return ['ok' => false, 'msg' => 'Partida no encontrada'];

        $usuarioId = $_SESSION['usuario_id'] ?? null;
        if (!$usuarioId) {
            return ['ok' => false, 'msg' => 'No estás autenticado'];
        }

        if ($p['modo'] !== 'digitalizado') {
            return ['ok' => false, 'msg' => 'Solo en modo digitalizado se usa dado'];
        }
        
        if ($p['estado'] !== 'dado') {
            return ['ok' => false, 'msg' => 'No es momento de tirar el dado'];
        }

        $caras = ['bosque', 'llanura', 'baños', 'cafeteria', 'vacio', 'sin_trex'];
        $cara = $caras[array_rand($caras)];

        $st = $this->db->prepare("UPDATE partidas SET dado=?, estado='colocar' WHERE id=?");
        $st->execute([$cara, $partidaId]);

        return ['ok' => true, 'dado' => $cara];
    }

    public function colocar(int $partidaId, string $recinto, int $slot, string $especie): array
    {
        $this->db->beginTransaction();
        
        try {
        $p = $this->buscarPartida($partidaId);
            if (!$p) {
                $this->db->rollBack();
                return ['ok' => false, 'msg' => 'Partida no encontrada'];
            }

            $jugadorActualId = $p['turno_id'] ?? null;
            if (!$jugadorActualId) {
                $this->db->rollBack();
                return ['ok' => false, 'msg' => 'No hay turno definido'];
            }

            $checkPartida = $this->db->prepare("SELECT jugadores_colocaron FROM partidas WHERE id = ? FOR UPDATE");
            $checkPartida->execute([$partidaId]);
            $jugadoresColocaronStr = $checkPartida->fetchColumn();
            
            $colocaron = json_decode($jugadoresColocaronStr ?? '[]', true);
            if (!is_array($colocaron)) $colocaron = [];
            
            if (in_array($jugadorActualId, $colocaron)) {
                $this->db->rollBack();
                return ['ok' => false, 'msg' => 'Ya colocaste en este turno. Espera a que todos coloquen.'];
        }

        if ($p['modo'] === 'digitalizado' && $p['estado'] !== 'colocar') {
                $this->db->rollBack();
            return ['ok' => false, 'msg' => 'Primero tira el dado'];
        }
        
        if ($p['modo'] === 'seguimiento' && $p['estado'] !== 'colocar') {
                $this->db->rollBack();
            return ['ok' => false, 'msg' => 'No es momento de colocar'];
        }

        $recinto = is_array($recinto) ? '' : trim((string)$recinto);
        $especie = is_array($especie) ? '' : strtolower(trim((string)$especie));
        
        require_once __DIR__ . '/../Classes/Dinosaurio.php';
        $especie = Dinosaurio::normalizar($especie);
            
            try {
                if (!$this->gestorManos->tieneDinosaurio($partidaId, $jugadorActualId, $especie)) {
                    $this->db->rollBack();
                    return ['ok' => false, 'msg' => "No tienes '$especie' en tu mano"];
                }
            } catch (Exception $e) {
                $this->db->rollBack();
                return ['ok' => false, 'msg' => 'Sistema de bolsa no activo. Actualiza la BD primero.'];
            }

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
            if ($limite === 0) {
                $this->db->rollBack();
                return ['ok' => false, 'msg' => "Recinto no válido"];
            }
        if ($slot < 0 || $slot >= $limite) {
                $this->db->rollBack();
            return ['ok' => false, 'msg' => 'Slot fuera de rango'];
        }

            $st = $this->db->prepare("SELECT 1 FROM colocaciones WHERE partida_id=? AND jugador_id=? AND recinto=? AND slot=?");
            $st->execute([$partidaId, $jugadorActualId, $recinto, $slot]);
        if ($st->fetchColumn()) {
                $this->db->rollBack();
                return ['ok' => false, 'msg' => 'Ya colocaste en este slot'];
            }

            $partida = new Partida($partidaId, $p['modo']);
            $tablero = $this->obtenerEstadoTablero($partidaId, $jugadorActualId);
        
        $validacionRecinto = $partida->validaRecinto($recinto, $slot, $especie, $tablero, $limite);
        if (!$validacionRecinto[0]) {
                $this->db->rollBack();
            return ['ok' => false, 'msg' => $validacionRecinto[1]];
        }
        
        if ($p['modo'] === 'digitalizado' && $p['dado']) {
            $validacionDado = $partida->validaCara($p['dado'], $recinto, $slot, $especie, $tablero);
            if (!$validacionDado[0]) {
                    $this->db->rollBack();
                return ['ok' => false, 'msg' => $validacionDado[1]];
            }
        }

            try {
                $ins = $this->db->prepare("INSERT INTO colocaciones (partida_id, jugador_id, recinto, especie, slot) VALUES (?, ?, ?, ?, ?)");
                $ins->execute([$partidaId, $jugadorActualId, $recinto, $especie, $slot]);
            } catch (PDOException $e) {
                $this->db->rollBack();
                if ($e->getCode() == '23000') {
                    return ['ok' => false, 'msg' => 'Error de BD: Ejecuta actualizar_bd.sql'];
                }
                return ['ok' => false, 'msg' => 'Error guardando colocación'];
            }
        
            $this->gestorManos->quitarDinosaurio($partidaId, $jugadorActualId, $especie);
            $this->actualizarPuntuacionJugador($partidaId, $jugadorActualId);

            $this->marcarJugadorColoco($partidaId, $jugadorActualId);
            
            $checkPartida2 = $this->db->prepare("SELECT jugadores_colocaron FROM partidas WHERE id = ? FOR UPDATE");
            $checkPartida2->execute([$partidaId]);
            $jugadoresColocaronStr2 = $checkPartida2->fetchColumn();
            $colocaron = json_decode($jugadoresColocaronStr2 ?? '[]', true);
            
            $ordenJugadores = $this->obtenerOrdenJugadores($partidaId);
            $totalJugadores = count($ordenJugadores);
            $colocaronCount = count($colocaron);
            
            $todosColocaron = ($colocaronCount === $totalJugadores);
            
            if ($todosColocaron) {
                $this->avanzarTurnoYRonda($partidaId);
                
                $primerJugador = $this->db->prepare("SELECT id FROM jugadores_partida WHERE partida_id = ? ORDER BY orden ASC LIMIT 1");
                $primerJugador->execute([$partidaId]);
                $primerJugadorId = $primerJugador->fetchColumn();
                
                if ($primerJugadorId) {
                    $this->db->prepare("UPDATE partidas SET turno_id = ? WHERE id = ?")
                             ->execute([$primerJugadorId, $partidaId]);
                }
                
        if ($p['modo'] === 'digitalizado') {
            $this->db->prepare("UPDATE partidas SET dado=NULL, estado='dado' WHERE id=?")->execute([$partidaId]);
        }
            } else {
                $siguienteJugadorId = $this->siguienteJugadorId($partidaId);
                
                if ($siguienteJugadorId) {
                    $this->db->prepare("UPDATE partidas SET turno_id = ? WHERE id = ?")
                             ->execute([$siguienteJugadorId, $partidaId]);
                }
            }

            $this->db->commit();

        return ['ok' => true, 'msg' => 'Dinosaurio colocado correctamente'];
            
        } catch (Exception $e) {
            $this->db->rollBack();
            return ['ok' => false, 'msg' => 'Error inesperado: ' . $e->getMessage()];
        }
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

        $st = $this->db->prepare("SELECT usuario_id, nombre, puntos FROM jugadores_partida WHERE partida_id = ? ORDER BY puntos DESC");
        $st->execute([$partidaId]);
        $jugadores = $st->fetchAll(PDO::FETCH_ASSOC);

        return ['ok' => true, 'jugadores' => $jugadores];
    }

    public function finTurno(int $partidaId, int $jugadorId): array
    {
        $p = $this->buscarPartida($partidaId);
        if (!$p) return ['ok' => false, 'msg' => 'Partida no encontrada'];

        // Verificar turno solo si existe turno_id en la partida
        if (isset($p['turno_id']) && $p['turno_id'] != $jugadorId) {
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

        $st = $this->db->prepare("SELECT usuario_id, nombre, orden, puntos FROM jugadores_partida WHERE partida_id = ? ORDER BY orden ASC");
        $st->execute([$partidaId]);
        $jugadores = $st->fetchAll(PDO::FETCH_ASSOC);

        $jugadorEnTurno = null;

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
            'turno_id' => null,
            'jugador_en_turno' => $jugadorEnTurno,
            'jugadores' => $jugadores,
            'colocaciones' => $coloc
        ];

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
