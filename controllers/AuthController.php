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
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
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
                    // Compatibilidad: agregar 'id' esperado por otros controladores
                    if (isset($usuario['id_usuario'])) {
                        $_SESSION['usuario']['id'] = $usuario['id_usuario'];
                    }

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
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_destroy();
        header("Location: index.php?controller=Auth&action=login");
        exit;
    }
    public function register() {

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $nombre    = $_POST['usuario'] ?? '';
        $correo    = $_POST['correo'] ?? '';
        $clave     = $_POST['clave'] ?? '';
        $rolSolic  = $_POST['rol_solicitado'] ?? null; // 2 o 3

        if (!$rolSolic) {
            die("Error: Debes seleccionar un rol.");
        }

        $claveHash = password_hash($clave, PASSWORD_BCRYPT);

        // Rol que irá a la tabla usuarios
        $rolNombre = ($rolSolic == 2 ? 'Administrador' : 'Colaborador');

        // Crear usuario
        $idUsuario = $this->usuarioModel->crear([
            'usuario'  => $nombre,
            'correo'   => $correo,
            'password' => $claveHash,
            'rol'      => $rolNombre,
            'estado'   => 'inactivo'
        ]);

        // Guardar rol solicitado en usuarios_roles (inactivo)
        $this->usuarioModel->asignarRol($idUsuario, $rolSolic, 0);

        echo "<h3>Registro enviado</h3>";
        echo "Un administrador debe activar tu rol para acceder.";
        exit;
    }

    $this->render("auth/login");
}



}
