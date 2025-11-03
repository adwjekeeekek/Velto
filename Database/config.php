<?php
function getPDO(): PDO {
    static $pdo = null;
    if ($pdo) return $pdo;

    $dsn  = 'mysql:host=localhost;dbname=velto;charset=utf8mb4';
    $user = 'root';
    $pass = '';

    try {
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        return $pdo;
    } catch (PDOException $e) {
        error_log("Error de conexion a la base de datos: " . $e->getMessage());
        throw new Exception("No se pudo conectar a la base de datos. Verifica la configuracion.");
    }
}

// Crear la variable $pdo para compatibilidad
try {
    $pdo = getPDO();
} catch (Exception $e) {
    // Solo crear la variable si la conexion es exitosa
    $pdo = null;
}