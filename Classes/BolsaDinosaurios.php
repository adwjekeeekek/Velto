<?php

class BolsaDinosaurios
{
    private const ESPECIES = [
        'triceratops',
        'trex',
        'velociraptor',
        'stegosaurus',
        'brachiosaurus',
        'pterodactilo'
    ];
    
    private const UNIDADES_POR_ESPECIE_BASE = 10;
    
    public static function construir(int $numJugadores): array
    {
        if ($numJugadores < 2 || $numJugadores > 5) {
            throw new InvalidArgumentException('Número de jugadores debe estar entre 2 y 5');
        }
        
        // 2 jugadores: 48 dinos (4 rondas × 12)
        // 3-4 jugadores: 48 dinos (2 rondas × 18/24)
        // 5 jugadores: 60 dinos (2 rondas × 30)
        $unidadesPorEspecie = self::UNIDADES_POR_ESPECIE_BASE;
        
        switch ($numJugadores) {
            case 2:
                $unidadesPorEspecie = 8;
                break;
            case 3:
            case 4:
                $unidadesPorEspecie = 8;
                break;
            case 5:
                $unidadesPorEspecie = 10;
                break;
        }
        
        $bolsa = [];
        foreach (self::ESPECIES as $especie) {
            for ($i = 0; $i < $unidadesPorEspecie; $i++) {
                $bolsa[] = $especie;
            }
        }
        
        shuffle($bolsa);
        
        return $bolsa;
    }
    
    public static function repartir(array &$bolsa, int $cantidad): array
    {
        if (count($bolsa) < $cantidad) {
            throw new RuntimeException("Bolsa insuficiente: solo quedan " . count($bolsa) . " dinosaurios, se necesitan $cantidad");
        }
        
        $repartidos = array_splice($bolsa, 0, $cantidad);
        return $repartidos;
    }
    
    public static function haySuficientes(array $bolsa, int $cantidad): bool
    {
        return count($bolsa) >= $cantidad;
    }
    
    public static function serializar(array $bolsa): string
    {
        return json_encode($bolsa, JSON_UNESCAPED_UNICODE);
    }
    
    public static function deserializar(?string $json): array
    {
        if (empty($json)) {
            return [];
        }
        
        $bolsa = json_decode($json, true);
        return is_array($bolsa) ? $bolsa : [];
    }
    
    public static function getEspecies(): array
    {
        return self::ESPECIES;
    }
}

