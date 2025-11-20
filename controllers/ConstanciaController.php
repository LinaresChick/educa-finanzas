<?php
namespace Controllers;
require_once __DIR__ . '/../core/BaseController.php';
require_once __DIR__ . '/../models/ConstanciaModel.php';
require_once __DIR__ . '/../models/EstudianteModel.php';

use Core\BaseController;
use Models\ConstanciaModel;
use Models\EstudianteModel;

class ConstanciaController extends BaseController {
    private $model;
    private $estModel;

    public function __construct() {
        parent::__construct();
        $this->model = new ConstanciaModel();
        $this->estModel = new EstudianteModel();
    }

    public function index() {
        $data['titulo'] = 'Constancias de Estudios';
        $data['constancias'] = $this->model->listarTodos();
        $this->render('constancias/listado', $data);
    }

    public function crear() {
        $data['titulo'] = 'Solicitar Constancia de Estudios';
        $data['estudiantes'] = $this->estModel->obtenerEstudiantesActivos();
        $this->render('constancias/crear', $data);
    }

    public function registrar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?controller=Constancia');
            exit;
        }

        $datos = [
            'id_estudiante' => !empty($_POST['id_estudiante']) ? intval($_POST['id_estudiante']) : null,
            'nombre_solicitante' => trim($_POST['nombre_solicitante'] ?? ''),
            'dni_solicitante' => trim($_POST['dni_solicitante'] ?? ''),
            'estado' => 'pendiente'
        ];

        try {
            $this->model->crear($datos);
            $_SESSION['exito'] = 'Constancia registrada correctamente';
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error al registrar constancia: ' . $e->getMessage();
        }

        header('Location: index.php?controller=Constancia');
        exit;
    }

    public function toggle() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?controller=Constancia');
            exit;
        }
        $id = intval($_POST['id_constancia'] ?? 0);
        if ($id) {
            $this->model->toggleEstado($id);
        }
        header('Location: index.php?controller=Constancia');
        exit;
    }

    public function imprimir() {
        $id = intval($_GET['id'] ?? 0);
        if (!$id) {
            header('Location: index.php?controller=Constancia');
            exit;
        }
        $c = $this->model->obtenerPorId($id);
        if (!$c) {
            header('Location: index.php?controller=Constancia');
            exit;
        }
        $data['titulo'] = 'Constancia de Estudios';
        $data['constancia'] = $c;
        $this->render('constancias/imprimir', $data);
    }
}
