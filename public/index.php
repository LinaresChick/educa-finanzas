<?php
// Configuración de seguridad de sesión
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 0); // cambiar a 1 si usas HTTPS
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Strict');

session_start();

// Regenerar ID de sesión periódicamente
if (!isset($_SESSION['last_regeneration'])) {
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
} elseif (time() - $_SESSION['last_regeneration'] > 300) { // cada 5 minutos
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
}

require_once "../config/config.php";
require_once "../core/Router.php";

use Core\Router;

// Headers de seguridad HTTP
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://code.jquery.com; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://fonts.googleapis.com; img-src 'self' data: https:; font-src 'self' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://fonts.gstatic.com; connect-src 'self';");

// DEPURACIÓN
error_log("=== INDEX.PHP INICIADO ===");
error_log("REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'N/A'));
error_log("GET params: " . print_r($_GET, true));
error_log("SESSION: " . print_r($_SESSION, true));

$router = new Router();
$router->run();