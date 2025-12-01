<?php
namespace Controllers;

require_once __DIR__ . '/../models/DocenteModel.php';
require_once __DIR__ . '/../models/GradoModel.php';
require_once __DIR__ . '/../models/SalonModel.php';
require_once __DIR__ . '/../models/SeccionModel.php';

use Models\DocenteModel;
use Models\GradoModel;
use Models\SalonModel;
use Models\SeccionModel;

class DocenteController {

    private $model;
    private $gradoModel;
    private $salonModel;
    private $seccionModel;

    public function __construct() {
        $this->model        = new DocenteModel();
        $this->gradoModel   = new GradoModel();
        $this->salonModel   = new SalonModel();
        $this->seccionModel = new SeccionModel();
    }

    public function index() {
        $docentes = $this->model->obtenerDocentesConSalon();
        require __DIR__ . '/../views/docentes/listado.php';
    }

    public function crear() {

        // 🔥 NUEVO: enviar grados, secciones y salones a la vista
        $grados    = $this->gradoModel->obtenerTodos();
        $secciones = $this->seccionModel->obtenerTodas();
        $salones   = $this->salonModel->obtenerDisponibles();

        require __DIR__ . '/../views/docentes/crear.php';
    }

    public function guardar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?controller=Docente&action=index");
            exit;
        }

        $data = [
            'nombres'       => $_POST['nombres'] ?? '',
            'apellidos'     => $_POST['apellidos'] ?? '',
            'dni'           => $_POST['dni'] ?? null,
            'telefono'      => $_POST['telefono'] ?? null,
            'correo'        => $_POST['correo'] ?? null,
            'especialidad'  => $_POST['especialidad'] ?? null,
            'id_grado'      => $_POST['id_grado'] ?? null,
            'id_seccion'    => $_POST['id_seccion'] ?? null,
            'id_salon'      => $_POST['id_salon'] ?? null,
            'estado'        => 'activo'
        ];

        $this->model->insert($data);

        header("Location: index.php?controller=Docente&action=index");
        exit;
    }

    public function editar() {
        if (!isset($_GET['id'])) {
            header("Location: index.php?controller=Docente&action=index");
            exit;
        }

        $id = intval($_GET['id']);
        $docente = $this->model->obtenerPorId($id);

        if (!$docente) {
            die("Docente no encontrado");
        }

        // 🔥 NUEVO: cargamos listas
        $grados    = $this->gradoModel->obtenerTodos();
        $secciones = $this->seccionModel->obtenerTodas();
        $salones   = $this->salonModel->obtenerTodos();

        require __DIR__ . '/../views/docentes/editar.php';
    }

    public function actualizar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?controller=Docente&action=index");
            exit;
        }

        $id = intval($_POST['id_docente']);

        $data = [
            'nombres'       => $_POST['nombres'] ?? '',
            'apellidos'     => $_POST['apellidos'] ?? '',
            'dni'           => $_POST['dni'] ?? null,
            'telefono'      => $_POST['telefono'] ?? null,
            'correo'        => $_POST['correo'] ?? null,
            'especialidad'  => $_POST['especialidad'] ?? null,
            'id_grado'      => $_POST['id_grado'] ?? null,
            'id_seccion'    => $_POST['id_seccion'] ?? null,
            'id_salon'      => $_POST['id_salon'] ?? null,
            'estado'        => $_POST['estado'] ?? 'activo'
        ];

        $this->model->update($id, $data);

        header("Location: index.php?controller=Docente&action=index");
        exit;
    }

    public function ver() {
        if (!isset($_GET['id'])) {
            header("Location: index.php?controller=Docente&action=index");
            exit;
        }

        $id = intval($_GET['id']);
        $docente = $this->model->obtenerPorId($id);

        if (!$docente) {
            die("Docente no encontrado");
        }

        require __DIR__ . '/../views/docentes/detalle.php';
    }

    public function detalle() {
        $this->ver(); // reutilizo el mismo método
    }

    public function eliminar() {
        if (!isset($_GET['id'])) {
            header("Location: index.php?controller=Docente&action=index");
            exit;
        }

        $id = intval($_GET['id']);

        $this->model->update($id, ['estado' => 'inactivo']);

        header("Location: index.php?controller=Docente&action=index");
        exit;
    }
}
