<?php
namespace Controllers;

require_once __DIR__ . '/../core/BaseController.php';

use Core\BaseController;

class PanelController extends BaseController {

    public function dashboard() {
        if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


        if (!isset($_SESSION['usuario'])) {
            header("Location: index.php?controller=Auth&action=login");
            exit();
        }

        $rol = $_SESSION['usuario']['rol'];

        switch ($rol) {
            case 'Superadmin':
                $this->render("panel/dashboard_superadmin");
                break;
            case 'Administrador':
                $this->render("panel/dashboard_admin");
                break;
            case 'Colaborador':
                $this->render("panel/dashboard_colaborador");
                break;
            case 'Padre':
                $this->render("panel/dashboard_padre");
                break;
            case 'Estudiante':
                $this->render("panel/dashboard_estudiante");
                break;
            default:
                echo "Rol no reconocido.";
                break;
        }
    }
}
