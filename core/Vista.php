<?php
namespace Core;

class Vista {
    public function mostrar($vista, $datos = []) {
        extract($datos);
        
        require_once __DIR__ . "/../views/templates/header.php";
        require_once __DIR__ . "/../views/{$vista}.php";
        require_once __DIR__ . "/../views/templates/footer.php";
    }
    
    public static function renderizar($vista, $datos = []) {
        extract($datos);
        
        require_once __DIR__ . "/../views/templates/header.php";
        require_once __DIR__ . "/../views/{$vista}.php";
        require_once __DIR__ . "/../views/templates/footer.php";
    }
}
 