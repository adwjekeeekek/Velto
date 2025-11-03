<?php
require_once __DIR__ . '/../Database/config.php';

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
        
        $pdo = getPDO();
        $stmt = $pdo->prepare("SELECT id, nombre, email FROM usuarios WHERE id = ?");
        $stmt->execute([(int)$_SESSION['usuario_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) return null;
        
        return [
            'id' => (int)$user['id'],
            'nombre' => $user['nombre'],
            'email' => $user['email']
        ];
    }
}
