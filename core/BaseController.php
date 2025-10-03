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

    protected function redireccionar($ruta) {
        header("Location: index.php?ruta=" . $ruta);
        exit();
    }
}
