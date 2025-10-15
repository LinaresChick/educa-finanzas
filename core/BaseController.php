<?php
namespace Core;

require_once __DIR__ . '/Sesion.php';
require_once __DIR__ . '/Vista.php';

class BaseController {
    protected $sesion;
    protected $vista;

    public function __construct() {
        $this->sesion = new Sesion();
        $this->vista = new Vista();
    }
    
    protected function render($view, $data = []) {
        extract($data);
        require_once __DIR__ . "/../views/templates/header.php";
        require_once __DIR__ . "/../views/$view.php";
        require_once __DIR__ . "/../views/templates/footer.php";
    }
    // En core/BaseController.php - REEMPLAZAR el método redireccionar()
    protected function redireccionar($ruta) {
    // Construir URL correctamente
    if (strpos($ruta, '/') !== false) {
        // Ruta como "controller/action"
        $partes = explode('/', $ruta);
        $url = BASE_URL . "/index.php?controller=" . ucfirst($partes[0]) . "&action=" . $partes[1];
    } else {
        // Ruta solo como "controller" (usa acción index)
        $url = BASE_URL . "/index.php?controller=" . ucfirst($ruta) . "&action=index";
    }
    
    header("Location: $url");
    exit();
}
}
