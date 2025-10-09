<?php
namespace Controllers;

require_once __DIR__ . '/../core/BaseController.php';
require_once __DIR__ . '/../models/PagoModel.php';
require_once __DIR__ . '/../models/EstudianteModel.php';

use Core\BaseController;
use Models\PagoModel;
use Models\EstudianteModel;

class PagoController extends BaseController 
{
    private $pagoModel;
    private $estudianteModel;
    
    public function __construct() {
        parent::__construct();
        $this->pagoModel = new PagoModel();
        $this->estudianteModel = new EstudianteModel();
    }
    
    public function index() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['usuario']) || !in_array($_SESSION['usuario']['rol'], ['Superadmin', 'Administrador', 'Colaborador'])) {
            header("Location: index.php?controller=Auth&action=acceso_denegado");
            exit();
        }
        
        $pagos = $this->pagoModel->obtenerPagosConEstudiantes();
        
        $datos = [
            'titulo' => 'Listado de Pagos',
            'pagos' => $pagos
        ];
        
        $this->render("pagos/listado", $datos);
    }
    
    public function registrar() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['usuario']) || !in_array($_SESSION['usuario']['rol'], ['Superadmin', 'Administrador', 'Colaborador'])) {
            header("Location: index.php?controller=Auth&action=acceso_denegado");
            exit();
        }
        
        $estudiantes = $this->estudianteModel->obtenerTodos();
        
        $datos = [
            'titulo' => 'Registrar Pago',
            'estudiantes' => $estudiantes
        ];
        
        $this->render("pagos/registrar", $datos);
    }
    
    public function guardar() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['usuario']) || !in_array($_SESSION['usuario']['rol'], ['Superadmin', 'Administrador', 'Colaborador'])) {
            header("Location: index.php?controller=Auth&action=acceso_denegado");
            exit();
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Validar campos requeridos
                $camposRequeridos = ['id_estudiante', 'concepto', 'banco', 'monto', 'metodo_pago', 'fecha_pago'];
                foreach ($camposRequeridos as $campo) {
                    if (!isset($_POST[$campo]) || empty($_POST[$campo])) {
                        throw new \Exception("El campo {$campo} es requerido");
                    }
                }

                // Procesar la imagen del voucher
                $foto_baucher = '';
                if (isset($_FILES['foto_baucher']) && $_FILES['foto_baucher']['error'] === UPLOAD_ERR_OK) {
                    $file = $_FILES['foto_baucher'];
                    $allowedTypes = ['image/jpeg', 'image/png'];
                    $maxSize = 2 * 1024 * 1024; // 2MB

                    if (!in_array($file['type'], $allowedTypes)) {
                        throw new \Exception('El archivo debe ser una imagen JPG o PNG');
                    }

                    if ($file['size'] > $maxSize) {
                        throw new \Exception('El archivo no debe superar los 2MB');
                    }

                    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $uniqueName = uniqid('voucher_') . '.' . $extension;
                    $uploadDir = __DIR__ . '/../public/uploads/vouchers/';
                    
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }
                    
                    $uploadFile = $uploadDir . $uniqueName;
                    
                    if (!move_uploaded_file($file['tmp_name'], $uploadFile)) {
                        throw new \Exception('Error al guardar el archivo');
                    }
                    
                    $foto_baucher = $uniqueName;
                }

                $datosPago = [
                    'id_estudiante' => $_POST['id_estudiante'],
                    'concepto' => $_POST['concepto'],
                    'banco' => $_POST['banco'],
                    'monto' => $_POST['monto'],
                    'metodo_pago' => $_POST['metodo_pago'],
                    'fecha_pago' => $_POST['fecha_pago'],
                    'descuento' => !empty($_POST['descuento']) ? $_POST['descuento'] : null,
                    'aumento' => !empty($_POST['aumento']) ? $_POST['aumento'] : null,
                    'observaciones' => !empty($_POST['observaciones']) ? $_POST['observaciones'] : null,
                    'foto_baucher' => $foto_baucher,
                    'estado' => 'completado',
                    'usuario_registro' => $_SESSION['usuario']['id_usuario']
                ];

                $resultado = $this->pagoModel->crear($datosPago);

                if ($resultado) {
                    $_SESSION['exito'] = "Pago registrado correctamente";
                    header("Location: index.php?controller=Pago");
                    exit();
                } else {
                    throw new \Exception("Error al registrar el pago");
                }

            } catch (\Exception $e) {
                $_SESSION['error'] = $e->getMessage();
                header("Location: index.php?controller=Pago&action=registrar");
                exit();
            }
        }
        
        header("Location: index.php?controller=Pago&action=registrar");
        exit();
    }
    
    public function comprobante() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_GET['id']) || empty($_GET['id'])) {
            header("Location: index.php?controller=Pago");
            exit();
        }

        $id_pago = (int)$_GET['id'];
        $pago = $this->pagoModel->buscarPorId($id_pago);

        if (!$pago) {
            header("Location: index.php?controller=Pago");
            exit();
        }

        // Obtener estudiante con su información completa
        $estudiante = $this->estudianteModel->obtenerEstudianteDetalle($pago['id_estudiante']);
        
        if ($estudiante) {
            // Adaptar los campos para la vista
            $estudiante['grado'] = $estudiante['grado_nombre'] ?? 'No asignado';
            $estudiante['seccion'] = $estudiante['seccion_nombre'] ?? '';
        }

        // Formatear la fecha de pago
        $pago['fecha_pago_formateada'] = date('d/m/Y', strtotime($pago['fecha_pago']));
        
        // Asegurarnos de que el estado esté definido
        $pago['estado'] = $pago['estado'] ?? 'completado';

        // Verificar si es modo impresión
        $modo_impresion = isset($_GET['imprimir']) && $_GET['imprimir'] == '1';

        $datos = [
            'titulo' => 'Comprobante de Pago',
            'pago' => $pago,
            'estudiante' => $estudiante,
            'modo_impresion' => $modo_impresion
        ];

        $this->render("pagos/comprobante", $datos);
    }
    
    public function historial() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_GET['id_estudiante']) || empty($_GET['id_estudiante'])) {
            header("Location: index.php?controller=Panel");
            exit();
        }

        $estudiante_id = (int)$_GET['id_estudiante'];
        $estudiante = $this->estudianteModel->buscarPorId($estudiante_id);
        $pagos = $this->pagoModel->obtenerPorEstudiante($estudiante_id);

        $datos = [
            'titulo' => 'Historial de Pagos',
            'estudiante' => $estudiante,
            'pagos' => $pagos
        ];

        $this->render("pagos/historial", $datos);
    }
}
