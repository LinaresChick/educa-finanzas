<?php
namespace Controllers;

require_once __DIR__ . '/../core/BaseController.php';
require_once __DIR__ . '/../models/UsuarioModel.php';

use Core\BaseController;
use Models\UsuarioModel;

class ConfiguracionController extends BaseController {
    private $usuarioModel;

    public function __construct() {
        $this->usuarioModel = new UsuarioModel();
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $correo = $_POST['correo'] ?? '';
            $clave = $_POST['clave'] ?? '';

            $usuario = $this->usuarioModel->obtenerPorCorreo($correo);

            // Ojo: en la BD el campo es "password"
            if ($usuario && password_verify($clave, $usuario['password'])) {
                $_SESSION['usuario'] = $usuario;
                header("Location: index.php?controller=Panel&action=dashboard");
                exit;
            } else {
                $error = "Credenciales incorrectas";
                $this->render("auth/login", compact('error'));
            }
        } else {
            $this->render("auth/login");
        }
    }

    public function logout() {
        session_destroy();
        header("Location: index.php?controller=Auth&action=login");
    }
}
