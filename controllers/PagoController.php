<?php
namespace Controllers;

require_once __DIR__ . '/../core/BaseController.php';
require_once __DIR__ . '/../models/PagoModel.php';
require_once __DIR__ . '/../models/EstudianteModel.php';
require_once __DIR__ . '/../core/Vista.php';

class PagoController extends \Core\BaseController
{
    protected $modelo;
    protected $modeloEstudiante;
    protected $vista;

    public function __construct()
    {
        parent::__construct();
        $this->modelo = new \Models\PagoModel();
        $this->modeloEstudiante = new \Models\EstudianteModel();
        $this->vista = new \Core\Vista();
    }

    public function index()
    {
        try {
            $filtros = [
                'fecha_inicio' => $_GET['fecha_inicio'] ?? null,
                'fecha_fin' => $_GET['fecha_fin'] ?? null,
                'estudiante' => $_GET['estudiante'] ?? null,
                'concepto' => $_GET['concepto'] ?? null,
                'metodo_pago' => $_GET['metodo_pago'] ?? null
            ];

            if (array_filter($filtros)) {
                $pagos = $this->modelo->obtenerPagosFiltrados($filtros);
            } else {
                $pagos = $this->modelo->obtenerPagosConEstudiantes();
            }

            $this->vista->mostrar('pagos/listado', [
                'pagos' => $pagos,
                'filtros' => $filtros
            ]);
        } catch (\Exception $e) {
            error_log('Error en PagoController->index: ' . $e->getMessage());
            $this->vista->mostrar('pagos/listado', [
                'error' => 'Hubo un error al cargar los pagos',
                'pagos' => [],
                'filtros' => []
            ]);
        }
    }

    public function registrar()
    {
        try {
            // Obtener la lista de estudiantes activos para el formulario
            $estudiantes = $this->modeloEstudiante->obtenerEstudiantesActivos();
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $datos = $_POST;
                if (isset($_FILES['voucher']) && $_FILES['voucher']['error'] === UPLOAD_ERR_OK) {
                    $archivo = $_FILES['voucher'];
                    $nombreArchivo = uniqid() . '_' . basename($archivo['name']);
                    $rutaDestino = __DIR__ . '/../public/uploads/vouchers/' . $nombreArchivo;
                    if (move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
                        $datos['foto_baucher'] = $nombreArchivo;
                    } else {
                        throw new \Exception("Error al subir el archivo");
                    }
                }
                
                // Validar que el estudiante exista
                if (empty($datos['id_estudiante'])) {
                    throw new \Exception("Debe seleccionar un estudiante");
                }
                
                $idPago = $this->modelo->crear($datos);
                if ($idPago) {
                    $_SESSION['exito'] = 'Pago registrado exitosamente';
                    header('Location: /index.php?controller=Pago&action=index');
                    exit;
                }
            }
            
            // Mostrar el formulario
            $this->vista->mostrar('pagos/registrar', [
                'estudiantes' => $estudiantes,
                'error' => $_SESSION['error'] ?? null
            ]);
            
            // Limpiar mensajes de error de la sesión
            if (isset($_SESSION['error'])) {
                unset($_SESSION['error']);
            }
        } catch (\Exception $e) {
            error_log("Error en PagoController->registrar: " . $e->getMessage());
            $_SESSION['error'] = 'Error al registrar el pago: ' . $e->getMessage();
            header('Location: /index.php?controller=Pago&action=registrar');
            exit;
        }
    }

    public function eliminar()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_pago'])) {
            try {
                $idPago = (int)$_POST['id_pago'];
                if ($this->modelo->eliminar($idPago)) {
                    echo json_encode(['success' => true, 'message' => 'Pago eliminado correctamente']);
                } else {
                    throw new \Exception("No se pudo eliminar el pago");
                }
            } catch (\Exception $e) {
                error_log("Error en PagoController->eliminar: " . $e->getMessage());
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Error al eliminar el pago']);
            }
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Solicitud inválida']);
        }
    }

    public function comprobante($params = [])
    {
        if (isset($params['id'])) {
            try {
                $idPago = (int)$params['id'];
                $pago = $this->modelo->buscarPorId($idPago);
                if ($pago) {
                    // Verificar si es modo impresión
                    $modo_impresion = isset($params['print']) && $params['print'] === '1';
                    
                    // Formatear la fecha
                    $fecha = new \DateTime($pago['fecha_pago']);
                    $fecha_formateada = $fecha->format('d/m/Y');
                    
                    // Obtener información del estudiante
                    $estudiante = $this->modelo->obtenerEstudiantePorPago($pago['id_pago']);
                    
                    // Calcular el monto total
                    $monto_total = $pago['monto'];
                    if (!empty($pago['descuento'])) {
                        $monto_total -= $pago['descuento'];
                    }
                    if (!empty($pago['aumento'])) {
                        $monto_total += $pago['aumento'];
                    }
                    $monto_total = number_format($monto_total, 2);
                    
                    $this->vista->mostrar('pagos/comprobante', [
                        'pago' => $pago,
                        'modo_impresion' => $modo_impresion,
                        'fecha_formateada' => $fecha_formateada,
                        'estudiante' => $estudiante,
                        'monto_total' => $monto_total
                    ]);
                } else {
                    throw new \Exception("Pago no encontrado");
                }
            } catch (\Exception $e) {
                error_log("Error en PagoController->comprobante: " . $e->getMessage());
                $_SESSION['error'] = 'Error al generar el comprobante: ' . $e->getMessage();
                header('Location: /index.php?controller=Pago&action=index');
                exit;
            }
        } else {
            header('Location: /index.php?controller=Pago&action=index');
            exit;
        }
    }

    public function historial()
    {
        try {
            $pagos = $this->modelo->obtenerPagosConEstudiantes();
            $this->vista->mostrar('pagos/historial', ['pagos' => $pagos]);
        } catch (\Exception $e) {
            error_log("Error en PagoController->historial: " . $e->getMessage());
            $this->vista->mostrar('pagos/historial', [
                'error' => 'Hubo un error al cargar el historial de pagos',
                'pagos' => []
            ]);
        }
    }

}