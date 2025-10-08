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

    // Si la vista pertenece a la carpeta "auth", no mostrar header ni footer
    if (strpos($view, 'auth/') === 0) {
        require_once __DIR__ . "/../views/$view.php";
        return;
    }

    // Para el resto de vistas, incluir la plantilla completa
    require_once __DIR__ . "/../views/templates/header.php";
    require_once __DIR__ . "/../views/$view.php";
    require_once __DIR__ . "/../views/templates/footer.php";
}


    protected function redireccionar($ruta) {
        $partes = explode('/', $ruta);
        $controller = ucfirst($partes[0]);
        $action = $partes[1] ?? 'index';
        
        header("Location: /educa-finanzas/public/index.php?controller={$controller}&action={$action}");
        exit();
    }
}
