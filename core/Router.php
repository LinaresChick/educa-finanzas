<?php
namespace Core;

class Router {
    public function run() {
        $controller = $_GET['controller'] ?? 'Auth';
        $action = $_GET['action'] ?? 'login';
        $params = $_GET['id'] ?? null;

        $controllerName = "\\Controllers\\" . ucfirst($controller) . "Controller";
        $controllerFile = "../controllers/" . ucfirst($controller) . "Controller.php";

        if (file_exists($controllerFile)) {
            require_once $controllerFile;
            $objController = new $controllerName();

            if (method_exists($objController, $action)) {
                if ($params !== null) {
                    $objController->$action($params);
                } else {
                    $objController->$action();
                }
            } else {
                echo "Acción no encontrada: $action";
            }
        } else {
            echo "Controlador no encontrado: $controller";
        }
    }
}
