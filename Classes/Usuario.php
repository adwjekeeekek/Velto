<?php
// Classes/Usuario.php
class Usuario {
    public static function estaLogueado(): bool {
        if (session_status() === PHP_SESSION_NONE) session_start();
        return isset($_SESSION['usuario_id']);
    }

    public static function requerirLogin(): void {
        if (!self::estaLogueado()) {
            header('Location: /login');
            exit;
        }
    }

    public static function actual(): ?array {
        if (!self::estaLogueado()) return null;
        return [
            'id' => (int)$_SESSION['usuario_id'],
            'nombre' => $_SESSION['usuario_nombre'] ?? ''
        ];
    }
}
