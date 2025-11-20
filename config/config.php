<?php
// Configuración de la aplicación
define('APP_NAME', 'Sistema Gestión Educativa');
define('APP_VERSION', '1.0.0');
define('BASE_URL', 'http://localhost/educa-finanzas/public');

// Configuración de paths
define('ROOT_PATH', dirname(dirname(__FILE__)));
define('CONTROLLERS_PATH', ROOT_PATH . '/controllers/');
define('MODELS_PATH', ROOT_PATH . '/models/');
define('VIEWS_PATH', ROOT_PATH . '/views/');
define('CORE_PATH', ROOT_PATH . '/core/');

// Configuración de sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configuración de seguridad
define('PASSWORD_COST', 12);
?>