<?php
// Classes/Recinto.php
require_once __DIR__ . '/Dinosaurio.php';

/* Recinto Semejanza */
class RecintoSemejanza {
    public string $tipo = 'semejanza';
    public string $area = 'bosque';
    public string $lado = 'izquierda';
    public int $slotsMax = 4;

    public function puedeColocar(array $slots, int $slot, string $especie): array {
        $primerLibre = 0; while (isset($slots[$primerLibre])) $primerLibre++;
        if ($slot !== $primerLibre) return [false,'Semejanza: izquierda→derecha'];
        $usadas = array_values(array_filter($slots));
        if ($usadas) {
            $primera = $usadas[0];
            foreach ($usadas as $e) if ($e !== $primera) return [false,'Semejanza: una sola especie'];
            if ($especie !== $primera) return [false,'Semejanza: misma especie'];
        }
        return [true,'OK'];
    }
}

/* Recinto Diferencia */
class RecintoDiferencia {
    public string $tipo = 'diferencia';
    public string $area = 'llanura';
    public string $lado = 'derecha';
    public int $slotsMax = 4;

    public function puedeColocar(array $slots, int $slot, string $especie): array {
        $primerLibre = 0; while (isset($slots[$primerLibre])) $primerLibre++;
        if ($slot !== $primerLibre) return [false,'Diferencia: izquierda→derecha'];
        if (in_array($especie, $slots, true)) return [false,'Diferencia: no repetir especie'];
        return [true,'OK'];
    }
}

/* Recinto Amor */
class RecintoAmor {
    public string $tipo = 'amor';
    public string $area = 'llanura';
    public string $lado = 'derecha';
    public int $slotsMax = 4;

    public function puedeColocar(array $slots, int $slot, string $especie): array {
        return [true,'OK'];
    }
}

/* Recinto Trío */
class RecintoTrio {
    public string $tipo = 'trio';
    public string $area = 'bosque';
    public string $lado = 'izquierda';
    public int $slotsMax = 3;

    public function puedeColocar(array $slots, int $slot, string $especie): array {
        return [true,'OK'];
    }
}

/* Recinto Rey */
class RecintoRey {
    public string $tipo = 'rey';
    public string $area = 'bosque';
    public string $lado = 'izquierda';
    public int $slotsMax = 1;

    public function puedeColocar(array $slots, int $slot, string $especie): array {
        return [true,'OK'];
    }
}

/* Recinto Solitaria */
class RecintoSolitaria {
    public string $tipo = 'solitaria';
    public string $area = 'llanura';
    public string $lado = 'derecha';
    public int $slotsMax = 1;

    public function puedeColocar(array $slots, int $slot, string $especie): array {
        return [true,'OK'];
    }
}

/* Recinto Río */
class RecintoRio {
    public string $tipo = 'rio';
    public ?string $area = null;
    public ?string $lado = null;
    public int $slotsMax;

    public function __construct(int $cap=8) { $this->slotsMax = $cap; }

    public function puedeColocar(array $slots, int $slot, string $especie): array {
        return [true,'OK'];
    }
}
