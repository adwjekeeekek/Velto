<?php
session_start();

/* Constantes de ruta */
define('RUTA_RAIZ', __DIR__);
define('RUTA_VISTAS', RUTA_RAIZ . '/View');
define('RUTA_PUBLICA', RUTA_RAIZ . '/Public');

/* Enrutadores */
require_once RUTA_RAIZ . '/Routes/routes.php';
require_once RUTA_RAIZ . '/Routes/api.php';

/* Servir archivos estaticos */
function servirEstatico(string $rutaRel): bool {
    $path = RUTA_PUBLICA . '/' . ltrim($rutaRel, '/');
    if (!is_file($path)) return false;

    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    $types = [
        'css'=>'text/css','js'=>'application/javascript',
        'png'=>'image/png','jpg'=>'image/jpeg','jpeg'=>'image/jpeg',
        'gif'=>'image/gif','webp'=>'image/webp','svg'=>'image/svg+xml',
        'ico'=>'image/x-icon','mp4'=>'video/mp4'
    ];
    header('Content-Type: ' . ($types[$ext] ?? 'application/octet-stream'));
    readfile($path);
    return true;
}

$uri  = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$base = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
$ruta = preg_replace('#^'.preg_quote($base,'#').'#', '', $uri);
$ruta = trim((string)$ruta, '/');

/* Estaticos bajo /Public */
if ($ruta !== '' && servirEstatico($ruta)) {
    exit;
}

/* API */
if ($ruta === 'api' || strpos($ruta, 'api/') === 0) {
    api_router($ruta, $_SERVER['REQUEST_METHOD'] ?? 'GET');
    exit;
}

/* Vistas */
rutas($ruta, $_SERVER['REQUEST_METHOD'] ?? 'GET');
