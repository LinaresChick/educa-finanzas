<?php
namespace Controllers;

require_once __DIR__ . '/../core/BaseController.php';
require_once __DIR__ . '/../models/DocenteModel.php';
require_once __DIR__ . '/../models/GradoModel.php';
require_once __DIR__ . '/../models/SalonModel.php';
require_once __DIR__ . '/../models/SeccionModel.php';

use Core\BaseController;
use Models\DocenteModel;
use Models\GradoModel;
use Models\SalonModel;
use Models\SeccionModel;

class DocenteController extends BaseController {

    private $model;
    private $gradoModel;
    private $salonModel;
    private $seccionModel;

    public function __construct() {
        parent::__construct();
        $this->model        = new DocenteModel();
        $this->gradoModel   = new GradoModel();
        $this->salonModel   = new SalonModel();
        $this->seccionModel = new SeccionModel();
    }

    public function index() {
        $docentes = $this->model->obtenerDocentesConSalon();
        $datos = [
            'titulo' => 'Listado de Docentes',
            'docentes' => $docentes
        ];
        $this->render('docentes/listado', $datos);
    }
    public function crear() {
        // Grados y secciones (si aún los usas)
        $grados    = $this->gradoModel->obtenerTodos();
        $secciones = $this->seccionModel->obtenerTodas();

        // Salones disponibles correctamente
        $salones = $this->salonModel->getSalonesDisponibles();

        $datos = [
            'titulo' => 'Registrar Nuevo Docente',
            'grados' => $grados,
            'secciones' => $secciones,
            'salones' => $salones
        ];
        $this->render('docentes/crear', $datos);
    }

    public function guardar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('docentes');
            return;
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

        $this->sesion->setFlash('exito', 'Docente registrado correctamente.');
        $this->redireccionar('docentes');
    }



    public function editar() {
        if (!isset($_GET['id'])) {
            $this->redireccionar('docente/index');
            return;
        }

        $id = intval($_GET['id']);
        $docente = $this->model->obtenerPorId($id);

        if (!$docente) {
            $this->sesion->setFlash('error', 'Docente no encontrado');
            $this->redireccionar('docente/index');
            return;
        }

        $grados    = $this->gradoModel->obtenerTodos();
        $secciones = $this->seccionModel->obtenerTodas();
        $salones   = $this->salonModel->obtenerTodos();

        // Obtener el salón actualmente asignado (si existe) y adjuntar id_salon para preselección
        $salonAsignado = $this->salonModel->obtenerPorDocente($id);
        if ($salonAsignado && isset($salonAsignado['id_salon'])) {
            $docente['id_salon'] = $salonAsignado['id_salon'];
        }

        $datos = [
            'titulo' => 'Editar Docente',
            'docente' => $docente,
            'grados' => $grados,
            'secciones' => $secciones,
            'salones' => $salones
        ];
        $this->render('docentes/editar', $datos);
    }

    public function actualizar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('docente/index');
            return;
        }

        $id = intval($_POST['id_docente']);

        // Actualizar solo campos de la tabla docentes
        $dataDocente = [
            'nombres'       => $_POST['nombres'] ?? '',
            'apellidos'     => $_POST['apellidos'] ?? '',
            'dni'           => $_POST['dni'] ?? null,
            'telefono'      => $_POST['telefono'] ?? null,
            'correo'        => $_POST['correo'] ?? null,
            'especialidad'  => $_POST['especialidad'] ?? null,
            'estado'        => $_POST['estado'] ?? 'activo'
        ];
        $this->model->update($id, $dataDocente);

        // Manejar la asignación de salón (tabla salones)
        $idSalonSeleccionado = $_POST['id_salon'] ?? null;
        if ($idSalonSeleccionado === '' || $idSalonSeleccionado === null) {
            // Si no se seleccionó salón, liberar cualquier asignación existente
            $this->salonModel->liberarDocenteDeSalones($id);
        } else {
            // Liberar asignaciones previas para evitar múltiples salones asignados
            $this->salonModel->liberarDocenteDeSalones($id);
            // Asignar al salón elegido
            $this->salonModel->asignarDocenteASalon(intval($idSalonSeleccionado), $id);
        }

        $this->sesion->setFlash('exito', 'Docente actualizado correctamente');
        $this->redireccionar('docente/index');
    }

    public function ver() {
        if (!isset($_GET['id'])) {
            $this->redireccionar('docente/index');
            return;
        }

        $id = intval($_GET['id']);
        $docente = $this->model->obtenerPorId($id);

        if (!$docente) {
            $this->sesion->setFlash('error', 'Docente no encontrado');
            $this->redireccionar('docente/index');
            return;
        }

        // Enriquecer con datos del salón asignado (si existe)
        $salon = $this->salonModel->obtenerPorDocente($id);
        if ($salon) {
            $docente = array_merge($docente, $salon);
        }

        $datos = [
            'titulo' => 'Detalle del Docente',
            'docente' => $docente
        ];
        $this->render('docentes/detalle', $datos);
    }

    public function detalle() {
        $this->ver(); // reutilizo el mismo método
    }

    public function eliminar() {
        if (!isset($_GET['id'])) {
            $this->redireccionar('docente/index');
            return;
        }

        $id = intval($_GET['id']);
        // Liberar cualquier salón asignado antes de eliminar para no violar FK
        $this->salonModel->liberarDocenteDeSalones($id);

        // Eliminar definitivamente el docente
        $this->model->eliminar($id);

        $this->sesion->setFlash('exito', 'Docente eliminado permanentemente');
        $this->redireccionar('docente/index');
        exit;
    }
}
