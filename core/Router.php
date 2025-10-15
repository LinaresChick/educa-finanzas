<?php
namespace Core;

class Router {
    // En core/Router.php - REEMPLAZA COMPLETAMENTE el método run()
public function run() {
    // Obtener la URL desde .htaccess o desde parámetros GET
    $url = $_GET['url'] ?? '';
    
    // Si no hay URL desde .htaccess, usar los parámetros tradicionales
    if (empty($url)) {
        $controller = $_GET['controller'] ?? 'Auth';
        $action = $_GET['action'] ?? 'login';
        $params = $_GET['id'] ?? null;
    } else {
        // Parsear la URL amigable: /controller/action/param
        $urlParts = explode('/', trim($url, '/'));
        $controller = $urlParts[0] ?? 'Auth';
        $action = $urlParts[1] ?? 'index';
        $params = $urlParts[2] ?? null;
    }
    
    $controllerName = "\\Controllers\\" . ucfirst($controller) . "Controller";
    $controllerFile = __DIR__ . "/../controllers/" . ucfirst($controller) . "Controller.php";

    error_log("Router: Controller=$controller, Action=$action, Params=$params");
    error_log("Controller File: $controllerFile");
    error_log("Controller Class: $controllerName");

    if (file_exists($controllerFile)) {
        require_once $controllerFile;
        
        if (class_exists($controllerName)) {
            $objController = new $controllerName();

            if (method_exists($objController, $action)) {
                if ($params !== null) {
                    $objController->$action($params);
                } else {
                    $objController->$action();
                }
            } else {
                error_log("Acción no encontrada: $action en $controllerName");
                $this->mostrarError("Acción no encontrada: $action");
            }
        } else {
            error_log("Clase no encontrada: $controllerName");
            $this->mostrarError("Controlador no encontrado: $controller");
        }
    } else {
        error_log("Archivo no encontrado: $controllerFile");
        $this->mostrarError("Controlador no encontrado: $controller");
    }
}

private function mostrarError($mensaje) {
    http_response_code(404);
    echo "<h1>Error 404</h1>";
    echo "<p>$mensaje</p>";
    echo "<pre>URL: " . ($_GET['url'] ?? 'N/A') . "</pre>";
    echo "<pre>Controller: " . ($_GET['controller'] ?? 'N/A') . "</pre>";
    echo "<pre>Action: " . ($_GET['action'] ?? 'N/A') . "</pre>";
}
}
