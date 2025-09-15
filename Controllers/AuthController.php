<?php
require_once __DIR__ . '/../Database/config.php';

class AuthController {
    private PDO $db;

    public function __construct() {
        $this->db = getPDO();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    private function buscarUsuario(string $identificador): ?array {
        try {
            $stmt = $this->db->prepare("SELECT * FROM usuarios WHERE email = ? OR nombre = ? LIMIT 1");
            $stmt->execute([$identificador, $identificador]);
            return $stmt->fetch() ?: null;
        } catch (PDOException $e) {
            return null;
        }
    }

    public function registrar(string $nombre, string $email, string $password): array {
        if (empty($nombre) || empty($email) || empty($password)) {
            return ['ok' => false, 'msg' => 'Todos los campos son obligatorios'];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['ok' => false, 'msg' => 'Email inválido'];
        }

        if (strlen($password) < 6) {
            return ['ok' => false, 'msg' => 'La contraseña debe tener al menos 6 caracteres'];
        }

        if (strlen($nombre) < 3) {
            return ['ok' => false, 'msg' => 'El nombre de usuario debe tener al menos 3 caracteres'];
        }

        if (strlen($nombre) > 50) {
            return ['ok' => false, 'msg' => 'El nombre de usuario no puede tener más de 50 caracteres'];
        }

        $hash = password_hash($password, PASSWORD_BCRYPT);
        
        try {
            $stmt = $this->db->prepare("INSERT INTO usuarios (nombre, email, clave_hash) VALUES (?, ?, ?)");
            $stmt->execute([$nombre, $email, $hash]);
            return ['ok' => true, 'msg' => 'Usuario registrado correctamente'];
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                return ['ok' => false, 'msg' => 'El nombre de usuario o email ya está en uso'];
            }
            return ['ok' => false, 'msg' => 'Error al registrar usuario: ' . $e->getMessage()];
        }
    }

    public function login(string $identificador, string $password): array {
        if (empty($identificador) || empty($password)) {
            return ['ok' => false, 'msg' => 'Usuario y contraseña son obligatorios'];
        }

        $usuario = $this->buscarUsuario($identificador);
        if (!$usuario) {
            return ['ok' => false, 'msg' => 'Usuario no encontrado'];
        }

        if (!password_verify($password, $usuario['clave_hash'])) {
            return ['ok' => false, 'msg' => 'Contraseña incorrecta'];
        }

        // Campo activo no existe en el schema actual, comentado
        // if (!$usuario['activo']) {
        //     return ['ok' => false, 'msg' => 'Usuario desactivado'];
        // }

        $_SESSION['usuario_id'] = (int)$usuario['id'];
        $_SESSION['usuario_nombre'] = $usuario['nombre'];

        // Campo ultimo_acceso no existe en el schema actual, comentado
        // try {
        //     $stmt = $this->db->prepare("UPDATE usuarios SET ultimo_acceso = NOW() WHERE id = ?");
        //     $stmt->execute([$usuario['id']]);
        // } catch (PDOException $e) {
        //     // No es crítico si falla
        // }

        return ['ok' => true, 'msg' => 'Login exitoso'];
    }

    public function logout(): void {
        session_unset();
        session_destroy();
    }

    public function usuarioActual(): ?array {
        if (!isset($_SESSION['usuario_id'])) {
            return null;
        }
        
        return [
            'id' => (int)$_SESSION['usuario_id'],
            'nombre' => $_SESSION['usuario_nombre'] ?? ''
        ];
    }
}
