<?php

require_once __DIR__ . '/BolsaDinosaurios.php';

class GestorManos
{
    private PDO $db;
    
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }
    
    public function guardarMano(int $partidaId, int $jugadorId, array $dinosaurios): void
    {
        $json = json_encode($dinosaurios, JSON_UNESCAPED_UNICODE);
        
        $stmt = $this->db->prepare("
            INSERT INTO manos_partida (partida_id, jugador_id, dinosaurios)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE dinosaurios = VALUES(dinosaurios), actualizado = CURRENT_TIMESTAMP
        ");
        
        $stmt->execute([$partidaId, $jugadorId, $json]);
    }
    
    public function obtenerMano(int $partidaId, int $jugadorId): array
    {
        $stmt = $this->db->prepare("
            SELECT dinosaurios 
            FROM manos_partida 
            WHERE partida_id = ? AND jugador_id = ?
        ");
        
        $stmt->execute([$partidaId, $jugadorId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$row) {
            return [];
        }
        
        $dinos = json_decode($row['dinosaurios'], true);
        return is_array($dinos) ? $dinos : [];
    }
    
    public function obtenerTodasLasManos(int $partidaId): array
    {
        $stmt = $this->db->prepare("
            SELECT jugador_id, dinosaurios 
            FROM manos_partida 
            WHERE partida_id = ?
        ");
        
        $stmt->execute([$partidaId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $manos = [];
        foreach ($rows as $row) {
            $dinos = json_decode($row['dinosaurios'], true);
            $manos[(int)$row['jugador_id']] = is_array($dinos) ? $dinos : [];
        }
        
        return $manos;
    }
    
    public function quitarDinosaurio(int $partidaId, int $jugadorId, string $especie): bool
    {
        $mano = $this->obtenerMano($partidaId, $jugadorId);
        
        $key = array_search($especie, $mano, true);
        if ($key === false) {
            return false;
        }
        
        unset($mano[$key]);
        $mano = array_values($mano);
        
        $this->guardarMano($partidaId, $jugadorId, $mano);
        return true;
    }
    
    public function tieneDinosaurio(int $partidaId, int $jugadorId, string $especie): bool
    {
        $mano = $this->obtenerMano($partidaId, $jugadorId);
        return in_array($especie, $mano, true);
    }
    
    public function rotarManos(int $partidaId, array $ordenJugadores): void
    {
        if (count($ordenJugadores) < 2) {
            return;
        }
        
        // Guardar snapshot de manos antes de rotar
        $manosActuales = [];
        foreach ($ordenJugadores as $jugadorId) {
            $manosActuales[$jugadorId] = $this->obtenerMano($partidaId, $jugadorId);
        }
        
        // Rotar: cada jugador recibe la mano del siguiente (sentido izquierda)
        $numJugadores = count($ordenJugadores);
        for ($i = 0; $i < $numJugadores; $i++) {
            $jugadorActual = $ordenJugadores[$i];
            $jugadorSiguiente = $ordenJugadores[($i + 1) % $numJugadores];
            $this->guardarMano($partidaId, $jugadorActual, $manosActuales[$jugadorSiguiente]);
        }
    }
    
    public function repartirATodos(int $partidaId, array $jugadores, array &$bolsa, int $cantidad = 6): void
    {
        $totalNecesario = count($jugadores) * $cantidad;
        
        if (!BolsaDinosaurios::haySuficientes($bolsa, $totalNecesario)) {
            throw new RuntimeException(
                "Bolsa insuficiente: se necesitan $totalNecesario dinosaurios, solo quedan " . count($bolsa)
            );
        }
        
        foreach ($jugadores as $jugadorId) {
            $mano = BolsaDinosaurios::repartir($bolsa, $cantidad);
            $this->guardarMano($partidaId, $jugadorId, $mano);
        }
    }
    
    public function limpiarManos(int $partidaId): void
    {
        $stmt = $this->db->prepare("DELETE FROM manos_partida WHERE partida_id = ?");
        $stmt->execute([$partidaId]);
    }
}

