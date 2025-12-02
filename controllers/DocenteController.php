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

    // Grados y secciones (si aún los usas)
    $grados    = $this->gradoModel->obtenerTodos();
    $secciones = $this->seccionModel->obtenerTodas();

    // Salones disponibles correctamente
    $salones = $this->salonModel->getSalonesDisponibles();

    require __DIR__ . '/../views/docentes/crear.php';
}

    public function guardar() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header("Location: index.php?controller=Docente&action=index");
        exit;
    }

    // 1️⃣ Guardar DOCENTE
    $data = [
        'nombres'      => $_POST['nombres'],
        'apellidos'    => $_POST['apellidos'],
        'dni'          => $_POST['dni'],
        'telefono'     => $_POST['telefono'],
        'correo'       => $_POST['correo'],
        'especialidad' => $_POST['especialidad'],
        'estado'       => 'activo'
    ];

    $idDocente = $this->model->insert($data);

    // 2️⃣ Registrar el salón basado en GRADO + SECCIÓN escogidos
    if (!empty($_POST['id_grado']) && !empty($_POST['id_seccion'])) {

        $this->salonModel->insertar([
            'id_grado'     => $_POST['id_grado'],
            'id_seccion'   => $_POST['id_seccion'],
            'id_docente'   => $idDocente,
            'anio'         => date('Y'),
            'cupo_maximo'  => 30,
            'estado'       => 'activo'
        ]);
    }

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
