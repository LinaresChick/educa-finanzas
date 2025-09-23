<?php
session_start();
require_once "../config/config.php";
require_once "../core/Router.php";

use Core\Router;

$router = new Router();
$router->run();
