<?php
class Dinosaurio {
    const TRICERATOPS = 'triceratops';
    const TREX = 'trex';
    const VELOCIRAPTOR = 'velociraptor';
    const STEGOSAURUS = 'stegosaurus';
    const BRACHIOSAURUS = 'brachiosaurus';
    const PTERODACTILO = 'pterodactilo';

    public static function normalizar(string $especie): string {
        $especie = strtolower(trim($especie));
        $mapa = [
            'triceratops' => self::TRICERATOPS,
            't-rex' => self::TREX,
            'trex' => self::TREX,
            'velociraptor' => self::VELOCIRAPTOR,
            'stegosaurus' => self::STEGOSAURUS,
            'brachiosaurus' => self::BRACHIOSAURUS,
            'pterodactilo' => self::PTERODACTILO,
            'pterodáctilo' => self::PTERODACTILO,
        ];
        return $mapa[$especie] ?? $especie;
    }

    public static function todasLasEspecies(): array {
        return [
            self::TRICERATOPS,
            self::TREX,
            self::VELOCIRAPTOR,
            self::STEGOSAURUS,
            self::BRACHIOSAURUS,
            self::PTERODACTILO,
        ];
    }

    public static function obtenerImagen(string $especie): string {
        $especie = self::normalizar($especie);
        $imagenes = [
            self::TRICERATOPS => '/Public/images/dinos/dinosaur_1.png',
            self::TREX => '/Public/images/dinos/dinosaur_2.png',
            self::VELOCIRAPTOR => '/Public/images/dinos/dinosaur_3.png',
            self::STEGOSAURUS => '/Public/images/dinos/dinosaur_4.png',
            self::BRACHIOSAURUS => '/Public/images/dinos/dinosaur_5.png',
            self::PTERODACTILO => '/Public/images/dinos/dinosaur_6.png',
        ];
        return $imagenes[$especie] ?? '/Public/images/dinos/dinosaur_1.png';
    }

    public static function obtenerNombre(string $especie): string {
        $especie = self::normalizar($especie);
        $nombres = [
            self::TRICERATOPS => 'Triceratops',
            self::TREX => 'T-Rex',
            self::VELOCIRAPTOR => 'Velociraptor',
            self::STEGOSAURUS => 'Stegosaurus',
            self::BRACHIOSAURUS => 'Brachiosaurus',
            self::PTERODACTILO => 'Pterodáctilo',
        ];
        return $nombres[$especie] ?? ucfirst($especie);
    }
}
