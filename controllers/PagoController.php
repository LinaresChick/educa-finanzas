<?php
namespace Controllers;

require_once __DIR__ . '/../core/BaseController.php';
require_once __DIR__ . '/../models/PagoModel.php';
require_once __DIR__ . '/../models/EstudianteModel.php';

use Core\BaseController;
use Models\PagoModel;
use Models\EstudianteModel;
use \Exception;

class PagoController extends BaseController {
    private $pagoModel;
    private $estudianteModel;
    
    public function __construct() {
        parent::__construct();
        $this->pagoModel = new PagoModel();
        $this->estudianteModel = new EstudianteModel();
    }
    
    /**
     * Muestra el listado de pagos
     */
    public function index() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['usuario']) || !in_array($_SESSION['usuario']['rol'], ['Superadmin', 'Administrador', 'Colaborador'])) {
            header("Location: index.php?controller=Auth&action=acceso_denegado");
            exit();
        }
        
        $pagos = $this->pagoModel->obtenerTodos();
        
        $datos = [
            'titulo' => 'Listado de Pagos',
            'pagos' => $pagos
        ];
        
        $this->render("pagos/listado", $datos);
    }
    
    /**
     * Muestra el formulario para registrar un nuevo pago
     */
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
    
    /**
     * Procesa el registro de un nuevo pago
     */
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
                error_log("Iniciando registro de pago");
                error_log("POST data: " . print_r($_POST, true));
                error_log("FILES data: " . print_r($_FILES, true));
                
                // Procesar la imagen del voucher si se subió
                $foto_baucher = null;
                if (isset($_FILES['foto_baucher']) && $_FILES['foto_baucher']['error'] === UPLOAD_ERR_OK) {
                    $file = $_FILES['foto_baucher'];
                    $allowedTypes = ['image/jpeg', 'image/png'];
                    $maxSize = 2 * 1024 * 1024; // 2MB

                    if (!in_array($file['type'], $allowedTypes)) {
                        throw new Exception('El archivo debe ser una imagen JPG o PNG');
                    }

                    if ($file['size'] > $maxSize) {
                        throw new Exception('El archivo no debe superar los 2MB');
                    }

                    // Crear directorio si no existe
                    $uploadDir = __DIR__ . '/../public/uploads/vouchers/';
                    if (!file_exists($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }

                    // Generar nombre único para el archivo
                    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $fileName = uniqid('voucher_') . '.' . $extension;
                    $targetPath = $uploadDir . $fileName;

                    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                        $foto_baucher = 'uploads/vouchers/' . $fileName;
                    } else {
                        throw new Exception('Error al subir el archivo');
                    }
                }

                $datosPago = [
                    'id_estudiante' => $_POST['id_estudiante'] ?? null,
                    'id_deuda' => $_POST['id_deuda'] ?? null,
                    'concepto' => $_POST['concepto'] ?? '',
                    'banco' => isset($_POST['banco']) && !empty($_POST['banco']) ? $_POST['banco'] : null,
                    'monto' => $_POST['monto'] ?? 0,
                    'metodo_pago' => $_POST['metodo_pago'] ?? '',
                    'fecha_pago' => $_POST['fecha_pago'] ?? date('Y-m-d'),
                    'descuento' => $_POST['descuento'] ?? 0,
                    'aumento' => $_POST['aumento'] ?? 0,
                    'foto_baucher' => $foto_baucher,
                    'estado' => 'completado',
                    'observaciones' => $_POST['observaciones'] ?? '',
                    'usuario_registro' => $_SESSION['usuario']['id_usuario'] ?? null
                ];
                
                error_log("Datos del pago a registrar: " . print_r($datosPago, true));
                
                $idPago = $this->pagoModel->crear($datosPago);
                error_log("Resultado de crear pago: " . ($idPago ? "ID: $idPago" : "false"));
                
                if ($idPago) {
                    $_SESSION['mensaje'] = "Pago registrado correctamente";
                    header("Location: index.php?controller=Pago&action=comprobante&id=" . $idPago);
                    exit();
                } else {
                    throw new Exception("Error al registrar el pago");
                }
                
            } catch (Exception $e) {
                error_log("Error en guardar pago: " . $e->getMessage());
                error_log("Stack trace: " . $e->getTraceAsString());
                $_SESSION['error'] = $e->getMessage();
                header("Location: index.php?controller=Pago&action=registrar");
                exit();
            }
        }
    }
    
    /**
     * Muestra el comprobante de un pago específico
     */
    public function comprobante() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['usuario'])) {
            header("Location: index.php?controller=Auth&action=acceso_denegado");
            exit();
        }
        
        $idPago = $_GET['id'] ?? null;
        
        if (!$idPago) {
            header("Location: index.php?controller=Pago");
            exit();
        }
        
        $pago = $this->pagoModel->obtenerPorId($idPago);
        
        if (!$pago) {
            $_SESSION['error'] = "Pago no encontrado";
            header("Location: index.php?controller=Pago");
            exit();
        }
        
        $estudiante = $this->estudianteModel->obtenerPorId($pago['id_estudiante']);
        
        $datos = [
            'titulo' => 'Comprobante de Pago',
            'pago' => $pago,
            'estudiante' => $estudiante
        ];
        
        $this->render("pagos/comprobante", $datos);
    }
    
    /**
     * Muestra el historial de pagos de un estudiante
     */
    public function historial() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['usuario'])) {
            header("Location: index.php?controller=Auth&action=acceso_denegado");
            exit();
        }
        
        $idEstudiante = $_GET['id_estudiante'] ?? null;
        
        if (!$idEstudiante) {
            header("Location: index.php?controller=Pago");
            exit();
        }
        
        $estudiante = $this->estudianteModel->obtenerPorId($idEstudiante);
        $pagos = $this->pagoModel->obtenerPorEstudiante($idEstudiante);
        
        $datos = [
            'titulo' => 'Historial de Pagos',
            'estudiante' => $estudiante,
            'pagos' => $pagos
        ];
        
        $this->render("pagos/historial", $datos);
    }
}
