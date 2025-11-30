<?php
namespace Controllers;

require_once __DIR__ . '/../core/BaseController.php';
require_once __DIR__ . '/../core/Modelo.php';
require_once __DIR__ . '/../models/EstudianteModel.php';
require_once __DIR__ . '/../models/PadreModel.php';
require_once __DIR__ . '/../models/PagoModel.php';
require_once __DIR__ . '/../models/UsuarioModel.php';
require_once __DIR__ . '/../models/ReporteModel.php';

use Core\BaseController;
use Models\EstudianteModel;
use Models\PadreModel;
use Models\PagoModel;
use Models\UsuarioModel;
use Models\ReporteModel;
use \Exception;

class PanelController extends BaseController {
    private $estudianteModel;
    private $padreModel;
    private $pagoModel;
    private $usuarioModel;
    private $reporteModel;

    public function __construct() {
        parent::__construct();
        $this->estudianteModel = new EstudianteModel();
        $this->padreModel = new PadreModel();
        $this->pagoModel = new PagoModel();
        $this->usuarioModel = new UsuarioModel();
        $this->reporteModel = new ReporteModel();
    }

    public function dashboard() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['usuario'])) {
            header("Location: index.php?controller=Auth&action=login");
            exit();
        }

        $rol = isset($_SESSION['usuario']['rol']) ? strtolower($_SESSION['usuario']['rol']) : '';
        $datos = [];

        switch ($rol) {
            case 'superadmin':
                $datos = $this->getDatosSuperadmin();
                $this->render("panel/dashboard_superadmin", $datos);
                break;
            case 'administrador':
                $datos = $this->getDatosAdmin();
                $this->render("panel/dashboard_admin", $datos);
                break;
            case 'contador':
                // Contador debe ver su dashboard específico
                $datos = $this->getDatosAdmin();
                $this->render("panel/dashboard_contador", $datos);
                break;
            case 'director':
                $datos = $this->getDatosAdmin();
                $this->render("panel/dashboard_director", $datos);
                break;
            case 'secretario':
                $datos = $this->getDatosAdmin();
                $this->render("panel/dashboard_secretario", $datos);
                break;
            case 'colaborador':
                $datos = $this->getDatosColaborador();
                $this->render("panel/dashboard_colaborador", $datos);
                break;
            case 'padre':
                $datos = $this->getDatosPadre($_SESSION['usuario']['id']);
                $this->render("panel/dashboard_padre", $datos);
                break;
            case 'estudiante':
                $datos = $this->getDatosEstudiante($_SESSION['usuario']['id']);
                $this->render("panel/dashboard_estudiante", $datos);
                break;
            default:
                header("Location: index.php?controller=Auth&action=acceso_denegado");
                exit();
                break;
        }
    }

    private function getDatosSuperadmin() {
        return [
            'totalUsuarios' => $this->usuarioModel->contarUsuarios(),
            'totalEstudiantes' => $this->estudianteModel->contarEstudiantes(),
            'totalPadres' => $this->padreModel->contarPadres(),
            'totalPagos' => $this->pagoModel->contarPagos(),
            'ingresoTotal' => $this->pagoModel->calcularIngresoTotal(),
            'pagosPendientes' => $this->pagoModel->contarPagosPendientes(),
            'pagosProcesados' => $this->pagoModel->contarPagosProcesados(),
            'ultimosUsuarios' => $this->usuarioModel->obtenerUltimosUsuarios(5),
            'ultimosPagos' => $this->pagoModel->obtenerUltimosPagos(5)
        ];
    }

    private function getDatosAdmin() {
        return [
            'totalEstudiantes' => $this->estudianteModel->contarEstudiantes(),
            'totalPadres' => $this->padreModel->contarPadres(),
            'totalPagos' => $this->pagoModel->contarPagos(),
            'ingresoMensual' => $this->pagoModel->calcularIngresoMensual(),
            'pagosPendientes' => $this->pagoModel->contarPagosPendientes(),
            'ultimosPagos' => $this->pagoModel->obtenerUltimosPagos(5),
            'ultimosEstudiantes' => $this->estudianteModel->obtenerUltimosEstudiantes(5)
        ];
    }

    private function getDatosColaborador() {
        return [
            'totalEstudiantes' => $this->estudianteModel->contarEstudiantes(),
            'pagosPendientes' => $this->pagoModel->contarPagosPendientes(),
            'pagosProcesados' => $this->pagoModel->contarPagosProcesadosHoy(),
            'ultimosPagos' => $this->pagoModel->obtenerUltimosPagos(5)
        ];
    }

    private function getDatosPadre($idUsuario) {
        $padre = $this->padreModel->obtenerPadrePorUsuarioId($idUsuario);
        
        if (!$padre) {
            return [
                'error' => 'No se encontró información del padre asociada a este usuario'
            ];
        }

        return [
            'hijos' => $this->padreModel->obtenerEstudiantesPorPadreId($padre['id']),
            'pagosPendientes' => $this->pagoModel->obtenerPagosPendientesPorPadreId($padre['id']),
            'ultimosPagos' => $this->pagoModel->obtenerUltimosPagosPorPadreId($padre['id'], 5),
            'padre' => $padre
        ];
    }

    private function getDatosEstudiante($idUsuario) {
        $estudiante = $this->estudianteModel->obtenerEstudiantePorUsuarioId($idUsuario);
        
        if (!$estudiante) {
            return [
                'error' => 'No se encontró información del estudiante asociada a este usuario'
            ];
        }

        return [
            'estudiante' => $estudiante,
            'pagos' => $this->pagoModel->obtenerPagosPorEstudianteId($estudiante['id']),
            'deudas' => $this->pagoModel->obtenerDeudasPorEstudianteId($estudiante['id']),
            'padres' => $this->estudianteModel->obtenerPadresPorEstudianteId($estudiante['id'])
        ];
    }
}
