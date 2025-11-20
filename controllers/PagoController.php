<?php
namespace Controllers;

require_once __DIR__ . '/../core/BaseController.php';
require_once __DIR__ . '/../models/PagoModel.php';
require_once __DIR__ . '/../models/EstudianteModel.php';
require_once __DIR__ . '/../models/PadreModel.php';

use Core\BaseController;
use Models\PagoModel;
use Models\EstudianteModel;
use Models\PadreModel;

class PagoController extends BaseController {
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
        
        if (!isset($_SESSION['usuario']) || !in_array($_SESSION['usuario']['rol'], ['Superadmin', 'Administrador', 'Colaborador', 'Secretario', 'Contador'])) {
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
    
    public function crear() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Verificar permisos
        if (!isset($_SESSION['usuario']) || !in_array($_SESSION['usuario']['rol'], ['Superadmin', 'Administrador', 'Colaborador', 'Secretario', 'Contador'])) {
            header("Location: index.php?controller=Auth&action=acceso_denegado");
            exit();
        }

        try {
            // Obtener lista de estudiantes activos para el select
            $estudiantes = $this->estudianteModel->obtenerEstudiantesActivos();
            
            $datos = [
                'titulo' => 'Registrar Nuevo Pago',
                'estudiantes' => $estudiantes
            ];
            
            // Verificar si hay mensajes de error en la sesión
            if (isset($_SESSION['error'])) {
                $datos['error'] = $_SESSION['error'];
                unset($_SESSION['error']);
            }
            
            // Renderizar la vista
            $this->render("pagos/registrar", $datos);
            
        } catch (\Exception $e) {
            error_log("Error en PagoController->crear: " . $e->getMessage());
            $_SESSION['error'] = "Error al cargar el formulario de registro de pago";
            header("Location: index.php?controller=Pago");
            exit();
        }
    }

    public function registrar() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Validar que se haya iniciado sesión y tenga el ID correcto
                if (!isset($_SESSION['usuario']) || !isset($_SESSION['usuario']['id'])) {
                    throw new \Exception("Debe iniciar sesión para realizar esta acción");
                }

                // Validar campos requeridos
                $camposRequeridos = ['id_estudiante', 'concepto', 'banco', 'monto', 'metodo_pago', 'fecha_pago'];
                foreach ($camposRequeridos as $campo) {
                    if (!isset($_POST[$campo]) || empty($_POST[$campo])) {
                        throw new \Exception("El campo " . ucfirst(str_replace('_', ' ', $campo)) . " es requerido");
                    }
                }

                // Capturar datos del formulario
                $datos = [
                    'id_estudiante'   => filter_var($_POST['id_estudiante'], FILTER_VALIDATE_INT),
                    'id_padre'        => isset($_POST['id_padre']) && $_POST['id_padre'] !== '' ? filter_var($_POST['id_padre'], FILTER_VALIDATE_INT) : null,
                    'pagador_nombre'  => isset($_POST['pagador_nombre']) ? htmlspecialchars($_POST['pagador_nombre']) : null,
                    'pagador_dni'     => isset($_POST['pagador_dni']) ? htmlspecialchars($_POST['pagador_dni']) : null,
                    'concepto'        => htmlspecialchars($_POST['concepto']),
                    'banco'           => htmlspecialchars($_POST['banco']),
                    'monto'           => filter_var($_POST['monto'], FILTER_VALIDATE_FLOAT),
                    'metodo_pago'     => htmlspecialchars($_POST['metodo_pago']),
                    'fecha_pago'      => $_POST['fecha_pago'],
                    'descuento'       => filter_var($_POST['descuento'] ?? 0, FILTER_VALIDATE_FLOAT),
                    'aumento'         => filter_var($_POST['aumento'] ?? 0, FILTER_VALIDATE_FLOAT),
                    'observaciones'   => htmlspecialchars($_POST['observaciones'] ?? ''),
                    'usuario_registro'=> $_SESSION['usuario']['id'],
                    'foto_baucher'    => '' // Inicializar como string vacío
                ];

                // Validar ID del estudiante
                if (!$datos['id_estudiante']) {
                    throw new \Exception("ID de estudiante inválido");
                }

                // Validar monto
                if ($datos['monto'] <= 0) {
                    throw new \Exception("El monto debe ser mayor a cero");
                }

                // Procesar y guardar el voucher si se proporciona
                if (!empty($_FILES['foto_baucher']['name'])) {
                    $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'pdf'];
                    $extension = strtolower(pathinfo($_FILES['foto_baucher']['name'], PATHINFO_EXTENSION));
                    $datos['foto_baucher'] = '$nombreArchivo';

                    if (!in_array($extension, $extensionesPermitidas)) {
                        throw new \Exception("Tipo de archivo no permitido. Use: " . implode(', ', $extensionesPermitidas));
                    }

                    $nombreArchivo = time() . '_' . uniqid() . '.' . $extension;
                    $rutaDestino = __DIR__ . '/../public/uploads/vouchers/' . $nombreArchivo;

                    if (!file_exists(dirname($rutaDestino))) {
                        mkdir(dirname($rutaDestino), 0777, true);
                    }

                    if (!move_uploaded_file($_FILES['foto_baucher']['tmp_name'], $rutaDestino)) {
                        throw new \Exception("Error al subir el voucher");
                    }

                    $datos['foto_baucher'] = $nombreArchivo;
                }

                // Registrar el pago
                $idPago = $this->pagoModel->crear($datos);

                if (!$idPago) {
                    throw new \Exception("Error al registrar el pago");
                }

                $_SESSION['exito'] = "✅ Pago registrado correctamente";
                header("Location: index.php?controller=Pago&action=index");
                exit();

            } catch (\Exception $e) {
                error_log("Error en PagoController->registrar: " . $e->getMessage());
                $_SESSION['error'] = "❌ Error: " . $e->getMessage();
                header("Location: index.php?controller=Pago&action=crear");
                exit();
            }
        } else {
            $_SESSION['error'] = "⚠ Método no permitido";
            header("Location: index.php?controller=Pago&action=crear");
            exit();
        }
    }

    public function eliminar() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['usuario']) || !in_array($_SESSION['usuario']['rol'], ['Superadmin', 'Administrador', 'Colaborador', 'Secretario', 'Contador'])) {
            header("Location: index.php?controller=Auth&action=acceso_denegado");
            exit();
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $id_pago = $_POST['id_pago'] ?? null;
                
                if (!$id_pago) {
                    throw new \Exception("ID de pago no especificado");
                }
                
                $resultado = $this->pagoModel->eliminar($id_pago);
                
                if ($resultado) {
                    $_SESSION['exito'] = "Pago eliminado correctamente";
                } else {
                    throw new \Exception("No se pudo eliminar el pago");
                }
                
            } catch (\Exception $e) {
                $_SESSION['error'] = $e->getMessage();
            }
        }
        
        header("Location: index.php?controller=Pago");
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
        $pago = $this->pagoModel->obtenerPorId($id_pago);

        if (!$pago) {
            header("Location: index.php?controller=Pago");
            exit();
        }

        $estudiante = $this->estudianteModel->obtenerPorId($pago['id_estudiante']);
        $pagador = null;
        try {
            if (!empty($pago['id_padre'])) {
                $padreModel = new PadreModel();
                $pagador = $padreModel->buscarPorId($pago['id_padre']);
            } elseif (!empty($pago['pagador_nombre'])) {
                $pagador = ['nombres' => $pago['pagador_nombre'], 'dni' => $pago['pagador_dni'] ?? ''];
            }
        } catch (\Exception $e) {
            error_log('Error al obtener pagador para comprobante: ' . $e->getMessage());
        }

        // Calcular monto total y formatear fecha
        $monto_total = $pago['monto'] - ($pago['descuento'] ?? 0) + ($pago['aumento'] ?? 0);
        $fecha_formateada = date('d/m/Y', strtotime($pago['fecha_pago']));

        $datos = [
            'titulo' => 'Comprobante de Pago',
            'pago' => $pago,
            'estudiante' => $estudiante,
            'monto_total' => number_format($monto_total, 2),
            'fecha_formateada' => $fecha_formateada
            , 'pagador' => $pagador
        ];

        $this->render("pagos/comprobante", $datos);
    }
    
    public function historial() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_GET['estudiante_id']) || empty($_GET['estudiante_id'])) {
            header("Location: index.php?controller=Panel");
            exit();
        }

        $estudiante_id = (int)$_GET['estudiante_id'];
        $estudiante = $this->estudianteModel->obtenerPorId($estudiante_id);
        $pagos = $this->pagoModel->obtenerPorEstudiante($estudiante_id);

        // Inicializar variable deudas como array vacío
        $deudas = [];

        $datos = [
            'titulo' => 'Historial de Pagos',
            'estudiante' => $estudiante,
            'pagos' => $pagos,
            'deudas' => $deudas
        ];
        
        $this->render("pagos/historial", $datos);
    }
}