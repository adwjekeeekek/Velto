<?php
class Jugador {
    public $id;
    public $nombre;
    public $dinosaurios = [];

    public function __construct(string $id, string $nombre) {
        $this->id = $id;
        $this->nombre = $nombre;
    }

    public function agregarDinosaurio(string $especie): void {
        $this->dinosaurios[] = $especie;
    }

    public function quitarDinosaurio(string $especie): void {
        $index = array_search($especie, $this->dinosaurios);
        if ($index !== false) {
            unset($this->dinosaurios[$index]);
            $this->dinosaurios = array_values($this->dinosaurios);
        }
    }

    public function toArray(): array {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'dinosaurios' => $this->dinosaurios,
        ];
    }
}
