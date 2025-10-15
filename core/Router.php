<?php
namespace Core;

class Router {
    public function run() {
        $controller = $_GET['controller'] ?? 'Auth';
        $action = $_GET['action'] ?? 'login';
        
        // Crear un array de parámetros con todos los valores GET excepto controller y action
        $params = [];
        foreach ($_GET as $key => $value) {
            if ($key !== 'controller' && $key !== 'action') {
                $params[$key] = $value;
            }
        }

        $controllerName = "\\Controllers\\" . ucfirst($controller) . "Controller";
        $controllerFile = __DIR__ . "/../controllers/" . ucfirst($controller) . "Controller.php";

        if (file_exists($controllerFile)) {
            require_once $controllerFile;
            $objController = new $controllerName();

            if (method_exists($objController, $action)) {
                if (!empty($params)) {
                    $objController->$action(['id' => $params['id'] ?? null]);
                } else {
                    $objController->$action();
                }
            } else {
                header("HTTP/1.0 404 Not Found");
                require_once __DIR__ . "/../views/errors/404.php";
            }
        } else {
            header("HTTP/1.0 404 Not Found");
            require_once __DIR__ . "/../views/errors/404.php";
        }
    }
}
