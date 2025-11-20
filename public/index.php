<?php
session_start();
require_once "../config/config.php";
require_once "../core/Router.php";

use Core\Router;

// DEPURACIÓN
error_log("=== INDEX.PHP INICIADO ===");
error_log("REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'N/A'));
error_log("GET params: " . print_r($_GET, true));
error_log("SESSION: " . print_r($_SESSION, true));

$router = new Router();
$router->run();