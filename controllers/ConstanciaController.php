<?php
namespace Controllers;
require_once __DIR__ . '/../core/BaseController.php';
require_once __DIR__ . '/../models/ConstanciaModel.php';
require_once __DIR__ . '/../models/EstudianteModel.php';
require_once __DIR__ . '/../models/ConfiguracionModel.php';

use Core\BaseController;
use Models\ConstanciaModel;
use Models\EstudianteModel;
use Models\ConfiguracionModel;

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
        // Obtener salones disponibles para mostrar "Grado y Sección" en el formulario
        if (method_exists($this->estModel, 'obtenerSalonesDisponibles')) {
            $data['salones'] = $this->estModel->obtenerSalonesDisponibles();
        } else {
            $data['salones'] = [];
        }
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
            'monto' => isset($_POST['monto']) ? floatval(str_replace(',', '.', $_POST['monto'])) : 0.00,
            'estado' => 'pendiente'
        ];

        // Campos opcionales que el usuario puede indicar al solicitar la constancia
        $anio = trim($_POST['anio'] ?? '');
        $observacion = trim($_POST['observacion'] ?? '');

        try {
            $idNuevo = $this->model->crear($datos);
            if ($idNuevo) {
                $_SESSION['exito'] = 'Constancia registrada correctamente';
                // Redirigir al listado en lugar de ir directo a imprimir
                header('Location: index.php?controller=Constancia');
                exit;
            } else {
                $_SESSION['error'] = 'No se pudo registrar la constancia.';
            }
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

    // Mostrar formulario para editar solo el monto (solo cuando la constancia está pendiente)
    public function editar() {
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
        $data['titulo'] = 'Editar monto de constancia';
        $data['constancia'] = $c;
        $this->render('constancias/editar', $data);
    }

    // Actualizar monto (solo campo monto). Si monto >= 40, marcar como pagado.
    public function actualizar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?controller=Constancia');
            exit;
        }
        $id = intval($_POST['id_constancia'] ?? 0);
        if (!$id) {
            header('Location: index.php?controller=Constancia');
            exit;
        }

        $monto = isset($_POST['monto']) ? floatval(str_replace(',', '.', $_POST['monto'])) : 0.00;
        // Validación mínima: monto no negativo
        if ($monto < 0) {
            $_SESSION['error'] = 'El monto no puede ser negativo.';
            header('Location: index.php?controller=Constancia&action=editar&id=' . $id);
            exit;
        }

        $nuevoEstado = $monto >= 40.0 ? 'pagado' : 'pendiente';

        try {
            $this->model->actualizar($id, ['monto' => $monto, 'estado' => $nuevoEstado]);
            $_SESSION['exito'] = 'Monto actualizado correctamente';
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error al actualizar monto: ' . $e->getMessage();
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
        // Obtener datos adicionales del estudiante y configuraciones (director, ciudad)
        $estudianteDetalle = $this->estModel->obtenerEstudianteDetalle($c['id_estudiante']);
        $configModel = new ConfiguracionModel();
        $directorNombre = $configModel->obtenerValor('director_nombre');
        $ciudad = $configModel->obtenerValor('ciudad');

        // Parámetros opcionales: año solicitado y observación (p. ej. "retirado en 2023")
        $anioSolicitado = !empty($_GET['anio']) ? trim($_GET['anio']) : null;
        $observacion = !empty($_GET['observacion']) ? trim($_GET['observacion']) : null;

        $data['titulo'] = 'Constancia de Estudios';
        $data['constancia'] = $c;
        $data['estudiante'] = $estudianteDetalle ?: [];
        $data['director'] = $directorNombre ?: 'Nombre del Director(a)';
        $data['ciudad'] = $ciudad ?: 'Independencia';
        $data['anio_solicitado'] = $anioSolicitado;
        $data['observacion'] = $observacion;
        $this->render('constancias/imprimir', $data);
    }
}
