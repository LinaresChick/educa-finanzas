<?php
namespace Core;

class Router {
    public function run() {
        $controller = $_GET['controller'] ?? 'Auth';
        $action = $_GET['action'] ?? 'login';

        $controllerName = "\\Controllers\\" . $controller . "Controller";
        $controllerFile = "../controllers/" . $controller . "Controller.php";

        if (file_exists($controllerFile)) {
            require_once $controllerFile;
            $objController = new $controllerName();

            if (method_exists($objController, $action)) {
                $objController->$action();
            } else {
                echo "Acción no encontrada: $action";
            }
        } else {
            echo "Controlador no encontrado: $controller";
        }
    }
}
