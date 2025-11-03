<?php

require_once __DIR__ . '/Dinosaurio.php';
require_once __DIR__ . '/Recinto.php';

class Partida {
    public int $id;
    public string $modo;

    public function __construct(int $id, string $modo = 'seguimiento') {
        $this->id = $id;
        $this->modo = $modo;
    }

    public function validaCara(string $cara, string $recinto, int $slot, string $especie, array $tablero): array {
        $recintos = $this->recintosVerano();
        $def = $recintos[$recinto] ?? null;
        if (!$def) return [false, "Recinto inválido"];

        if ($def['tipo'] === 'rio') return [true, "Río siempre válido"];

        $slotsRecinto = $tablero[$recinto] ?? [];

        return match ($cara) {
            'bosque' => [$def['area'] === 'bosque', $def['area'] === 'bosque' ? "Válido para bosque" : "Solo recintos del bosque"],
            'llanura' => [$def['area'] === 'llanura', $def['area'] === 'llanura' ? "Válido para llanura" : "Solo recintos de la llanura"],
            'baños' => [$def['lado'] === 'derecha', $def['lado'] === 'derecha' ? "Válido para baños" : "Solo recintos a la derecha del río"],
            'cafeteria' => [$def['lado'] === 'izquierda', $def['lado'] === 'izquierda' ? "Válido para cafetería" : "Solo recintos a la izquierda del río"],
            'vacio' => [empty($slotsRecinto), empty($slotsRecinto) ? "Válido para recinto vacío" : "Solo en recintos sin dinosaurios"],
            'sin_trex' => [!in_array('trex', $slotsRecinto), !in_array('trex', $slotsRecinto) ? "Válido sin T-Rex" : "Prohibido en recintos con T-Rex"],
            default => [true, "Cara válida"]
        };
    }

    public function validaRecinto(string $recinto, int $slot, string $especie, array $tablero, int $capacidadMaxima = null): array {
        $especie = Dinosaurio::normalizar($especie);
        
        $recintos = $this->recintosVerano();
        $def = $recintos[$recinto] ?? null;
        if (!$def) return [false, "Recinto inválido"];

        $slotsRecinto = $tablero[$recinto] ?? [];
        
        $capacidad = $capacidadMaxima ?? $def['slots_max'];
        if ($slot < 0 || $slot >= $capacidad) {
            return [false, "Slot fuera de rango"];
        }
        
        if (isset($slotsRecinto[$slot])) {
            return [false, "Slot ocupado"];
        }

        $clase = $def['class'];
        
        // Para RecintoRio, pasar capacidad explícitamente
        if ($clase === 'RecintoRio') {
            $recintoObj = new $clase($capacidad);
        } else {
            $recintoObj = new $clase();
        }
        
        return $recintoObj->puedeColocar($slotsRecinto, $slot, $especie);
    }

    public function puntuar(array $colocaciones): array {
        $puntos = 0;
        $detalle = [];

        $porRecinto = [];
        foreach ($colocaciones as $c) {
            $porRecinto[$c['recinto']][] = $c['especie'];
        }

        foreach ($this->recintosVerano() as $clave=>$def) {
            $dinos = $porRecinto[$clave] ?? [];
            $pts = 0;

            switch ($def['tipo']) {
                case 'semejanza':
                    $n = count($dinos);
                    $pts = match($n){1=>0,2=>3,3=>6,4=>10,default=>0};
                    break;
                case 'diferencia':
                    $n = count($dinos);
                    $pts = match($n){1=>1,2=>3,3=>6,4=>10,default=>0};
                    break;
                case 'amor':
                    $cont = array_count_values($dinos);
                    $pares = 0; foreach($cont as $c) $pares += intdiv($c,2);
                    $pts = $pares * 5;
                    break;
                case 'trio':
                    $pts = (count($dinos) === 3) ? 7 : 0;
                    break;
                case 'rey':
                    $pts = $dinos ? 7 : 0;
                    break;
                case 'solitaria':
                    if ($dinos) {
                        $esp = $dinos[0];
                        $totalEsp = 0;
                        foreach ($porRecinto as $rs) foreach($rs as $e) if($e===$esp) $totalEsp++;
                        if ($totalEsp === 1) $pts = 7;
                    }
                    break;
                case 'rio':
                    $pts = count($dinos) * 1;
                    break;
            }

            $detalle[] = ['recinto'=>$clave,'puntos'=>$pts];
            $puntos += $pts;
        }

        $bonus = 0;
        foreach ($this->recintosVerano() as $clave=>$def) {
            if ($def['tipo']==='rio') continue;
            $dinos = $porRecinto[$clave] ?? [];
            if (in_array('trex',$dinos,true)) $bonus++;
        }
        if ($bonus>0) {
            $detalle[] = ['recinto'=>'bonus_trex','puntos'=>$bonus];
            $puntos += $bonus;
        }

        return ['total'=>$puntos,'detalle'=>$detalle];
    }

    private function recintosVerano(): array {
        return [
            'bosque_semejanza' => ['class'=>'RecintoSemejanza','tipo'=>'semejanza','area'=>'bosque','lado'=>'izquierda','slots_max'=>4],
            'prado_diferencia' => ['class'=>'RecintoDiferencia','tipo'=>'diferencia','area'=>'llanura','lado'=>'derecha','slots_max'=>4],
            'pradera_amor' => ['class'=>'RecintoAmor','tipo'=>'amor','area'=>'llanura','lado'=>'derecha','slots_max'=>4],
            'trio_frondoso' => ['class'=>'RecintoTrio','tipo'=>'trio','area'=>'bosque','lado'=>'izquierda','slots_max'=>3],
            'rey_selva' => ['class'=>'RecintoRey','tipo'=>'rey','area'=>'bosque','lado'=>'izquierda','slots_max'=>1],
            'isla_solitaria' => ['class'=>'RecintoSolitaria','tipo'=>'solitaria','area'=>'llanura','lado'=>'derecha','slots_max'=>1],
            'rio' => ['class'=>'RecintoRio','tipo'=>'rio','area'=>null,'lado'=>null,'slots_max'=>8],
        ];
    }

}
