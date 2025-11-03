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

        $_SESSION['usuario_id'] = (int)$usuario['id'];
        $_SESSION['usuario_nombre'] = $usuario['nombre'];
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
        
        try {
            $stmt = $this->db->prepare("SELECT id, nombre, email FROM usuarios WHERE id = ?");
            $stmt->execute([(int)$_SESSION['usuario_id']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) return null;
            
            return [
                'id' => (int)$user['id'],
                'nombre' => $user['nombre'],
                'email' => $user['email']
            ];
        } catch (PDOException $e) {
            return null;
        }
    }

    public function actualizarNombre(string $nuevoNombre): array {
        if (!isset($_SESSION['usuario_id'])) {
            return ['ok' => false, 'msg' => 'No autenticado'];
        }

        if (empty($nuevoNombre)) {
            return ['ok' => false, 'msg' => 'El nombre no puede estar vacío'];
        }

        if (strlen($nuevoNombre) < 3) {
            return ['ok' => false, 'msg' => 'El nombre debe tener al menos 3 caracteres'];
        }

        if (strlen($nuevoNombre) > 50) {
            return ['ok' => false, 'msg' => 'El nombre no puede tener más de 50 caracteres'];
        }

        try {
            $stmt = $this->db->prepare("UPDATE usuarios SET nombre = ? WHERE id = ?");
            $stmt->execute([$nuevoNombre, (int)$_SESSION['usuario_id']]);
            $_SESSION['usuario_nombre'] = $nuevoNombre;
            return ['ok' => true, 'msg' => 'Nombre actualizado'];
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                return ['ok' => false, 'msg' => 'Ese nombre ya está en uso'];
            }
            return ['ok' => false, 'msg' => 'Error al actualizar nombre'];
        }
    }

    public function actualizarEmail(string $nuevoEmail): array {
        if (!isset($_SESSION['usuario_id'])) {
            return ['ok' => false, 'msg' => 'No autenticado'];
        }

        if (empty($nuevoEmail)) {
            return ['ok' => false, 'msg' => 'El email no puede estar vacío'];
        }

        if (!filter_var($nuevoEmail, FILTER_VALIDATE_EMAIL)) {
            return ['ok' => false, 'msg' => 'Email inválido'];
        }

        try {
            $stmt = $this->db->prepare("UPDATE usuarios SET email = ? WHERE id = ?");
            $stmt->execute([$nuevoEmail, (int)$_SESSION['usuario_id']]);
            return ['ok' => true, 'msg' => 'Email actualizado'];
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                return ['ok' => false, 'msg' => 'Ese email ya está en uso'];
            }
            return ['ok' => false, 'msg' => 'Error al actualizar email'];
        }
    }

    public function actualizarPassword(string $passwordActual, string $passwordNuevo): array {
        if (!isset($_SESSION['usuario_id'])) {
            return ['ok' => false, 'msg' => 'No autenticado'];
        }

        if (empty($passwordActual) || empty($passwordNuevo)) {
            return ['ok' => false, 'msg' => 'Todos los campos son obligatorios'];
        }

        if (strlen($passwordNuevo) < 6) {
            return ['ok' => false, 'msg' => 'La nueva contraseña debe tener al menos 6 caracteres'];
        }

        try {
            $stmt = $this->db->prepare("SELECT clave_hash FROM usuarios WHERE id = ?");
            $stmt->execute([(int)$_SESSION['usuario_id']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                return ['ok' => false, 'msg' => 'Usuario no encontrado'];
            }

            if (!password_verify($passwordActual, $user['clave_hash'])) {
                return ['ok' => false, 'msg' => 'Contraseña actual incorrecta'];
            }

            $nuevoHash = password_hash($passwordNuevo, PASSWORD_BCRYPT);
            $stmt = $this->db->prepare("UPDATE usuarios SET clave_hash = ? WHERE id = ?");
            $stmt->execute([$nuevoHash, (int)$_SESSION['usuario_id']]);
            
            return ['ok' => true, 'msg' => 'Contraseña actualizada'];
        } catch (PDOException $e) {
            return ['ok' => false, 'msg' => 'Error al actualizar contraseña'];
        }
    }
}
