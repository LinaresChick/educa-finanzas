<?php
namespace Controllers;

require_once __DIR__ . '/../core/BaseController.php';
require_once __DIR__ . '/../models/UsuarioModel.php';

use Core\BaseController;
use Models\UsuarioModel;

class AuthController extends BaseController {
    private $usuarioModel;

    public function __construct() {
        $this->usuarioModel = new UsuarioModel();
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $correo = $_POST['correo'] ?? '';
            $clave = $_POST['clave'] ?? '';

            // Obtener usuario desde el modelo
            $usuario = $this->usuarioModel->obtenerPorCorreo($correo);

            if ($usuario) {
                // Debug temporal para confirmar qué devuelve el modelo
                // Quitar después de probar
                // var_dump($usuario); die;

                if (password_verify($clave, $usuario['password'])) {
                    $_SESSION['usuario'] = $usuario;

                    // Debug temporal para confirmar login
                    // Quitar después de probar
                    // echo "Login exitoso. Redirigiendo..."; exit;
                    
                    $this->redireccionar("Panel/dashboard");
                } else {
                    $error = "La contraseña es incorrecta";
                    $this->render("auth/login", compact('error'));
                }
            } else {
                $error = "No existe un usuario con ese correo";
                $this->render("auth/login", compact('error'));
            }
        } else {
            $this->render("auth/login");
        }
    }

    public function logout() {
        session_destroy();
        header("Location: index.php?controller=Auth&action=login");
        exit;
    }
}
