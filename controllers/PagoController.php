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

        // Recoger filtros desde GET
        $filtros = [];
        $allowed = ['fecha_inicio', 'fecha_fin', 'estudiante', 'concepto', 'metodo_pago'];
        foreach ($allowed as $k) {
            if (isset($_GET[$k]) && $_GET[$k] !== '') {
                $filtros[$k] = trim($_GET[$k]);
            }
        }

        try {
            // Usar el método de filtros si existe
            if (method_exists($this->pagoModel, 'obtenerPagosFiltrados')) {
                $pagos = $this->pagoModel->obtenerPagosFiltrados($filtros);
            } else {
                $pagos = $this->pagoModel->obtenerPagosConEstudiantes();
            }
        } catch (\Exception $e) {
            error_log('Error al obtener pagos en index: ' . $e->getMessage());
            $pagos = [];
            $_SESSION['error'] = 'Error al obtener la lista de pagos';
        }

        $datos = [
            'titulo' => 'Listado de Pagos',
            'pagos' => $pagos,
            'filtros' => $filtros
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

            // Obtener lista de secciones (grado y sección) para filtrar estudiantes
            require_once __DIR__ . '/../models/SeccionModel.php';
            $seccionModel = new \Models\SeccionModel();
            $secciones = $seccionModel->obtenerTodas();

            $datos = [
                'titulo' => 'Registrar Nuevo Pago',
                'estudiantes' => $estudiantes,
                'secciones' => $secciones
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
            $attempt = 0;
            while (true) {
                try {
                    // Log de depuración: registrar lo que llega al servidor
                    error_log("[PagoController::registrar] POST recibidos: " . print_r($_POST, true));
                    error_log("[PagoController::registrar] FILES recibidos: " . print_r($_FILES, true));

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
                        // Nota: asignación posterior sobreescribirá este valor
                        $datos['foto_baucher'] = '';

                        if (!in_array($extension, $extensionesPermitidas)) {
                            throw new \Exception("Tipo de archivo no permitido. Use: " . implode(', ', $extensionesPermitidas));
                        }

                        $nombreArchivo = time() . '_' . uniqid() . '.' . $extension;
                        $rutaDestino = __DIR__ . '/../public/uploads/vouchers/' . $nombreArchivo;

                        if (!file_exists(dirname($rutaDestino))) {
                            mkdir(dirname($rutaDestino), 0777, true);
                        }

                        if (!move_uploaded_file($_FILES['foto_baucher']['tmp_name'], $rutaDestino)) {
                            error_log("[PagoController::registrar] Error al mover archivo a: {$rutaDestino}");
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
                    // Registro de depuración adicional en archivo dentro del proyecto
                    try {
                        $logDir = __DIR__ . '/../storage/logs';
                        if (!is_dir($logDir)) {
                            mkdir($logDir, 0777, true);
                        }
                        $logFile = $logDir . '/pago_debug.log';
                        $debugData = "[" . date('Y-m-d H:i:s') . "] Error en PagoController::registrar: " . $e->getMessage() . PHP_EOL;
                        $debugData .= $e->getTraceAsString() . PHP_EOL;
                        $debugData .= "POST: " . print_r($_POST, true) . PHP_EOL;
                        $debugData .= "FILES: " . print_r($_FILES, true) . PHP_EOL;
                        $debugData .= str_repeat('-', 80) . PHP_EOL;
                        file_put_contents($logFile, $debugData, FILE_APPEND);
                    } catch (\Exception $inner) {
                        error_log("No se pudo escribir pago_debug.log: " . $inner->getMessage());
                    }
                    // Si es posible error de esquema, intentar corregir y reintentar una vez
                    $msg = $e->getMessage();
                    $schemaIndicators = ['Unknown column', "doesn't exist", 'Incorrect', 'cannot be null', 'column count'];
                    $isSchemaError = false;
                    foreach ($schemaIndicators as $ind) {
                        if (stripos($msg, $ind) !== false) {
                            $isSchemaError = true;
                            break;
                        }
                    }

                    if ($isSchemaError && $attempt === 0) {
                        error_log("PagoController::registrar detectó posible error de esquema: " . $msg . " - intentando ensureSchema()");
                        $applied = false;
                        try {
                            $applied = $this->pagoModel->ensureSchema();
                        } catch (\Exception $inner) {
                            error_log("PagoController::registrar ensureSchema falló: " . $inner->getMessage());
                        }
                        $attempt++;
                        if ($applied) {
                            // reintentar ciclo
                            continue;
                        }
                        // si no se aplicó esquema, caemos al manejo de error normal
                    }

                    error_log("Error en PagoController->registrar: " . $e->getMessage());
                    // Preparar resumen seguro de POST/FILES para mostrar en la UI durante la depuración
                    $postKeys = array_keys($_POST ?? []);
                    $fileInfo = [];
                    if (!empty($_FILES)) {
                        foreach ($_FILES as $k => $v) {
                            $fileInfo[$k] = [
                                'name' => $v['name'] ?? null,
                                'size' => $v['size'] ?? null,
                                'error' => $v['error'] ?? null
                            ];
                        }
                    }
                    $detalle = "Error: " . $e->getMessage() . " | POST keys: " . implode(',', $postKeys) . " | FILES: " . json_encode($fileInfo);
                    // Limitar longitud a algo razonable
                    if (strlen($detalle) > 1000) $detalle = substr($detalle, 0, 1000) . '...';
                    $_SESSION['error'] = "❌ Ocurrió un error al registrar. Detalle: " . $detalle;
                    header("Location: index.php?controller=Pago&action=crear");
                    exit();
                }
            }
        } else {
            $_SESSION['error'] = "⚠ Método no permitido";
            header("Location: index.php?controller=Pago&action=crear");
            exit();
        }
    }

    public function editar() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // permisos
        if (!isset($_SESSION['usuario']) || !in_array($_SESSION['usuario']['rol'], ['Superadmin', 'Administrador', 'Colaborador', 'Secretario', 'Contador'])) {
            header("Location: index.php?controller=Auth&action=acceso_denegado");
            exit();
        }

        $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            if (!$id) {
                $_SESSION['error'] = 'ID de pago no especificado';
                header('Location: index.php?controller=Pago&action=index');
                exit();
            }

            $pago = $this->pagoModel->obtenerPorId($id);
            if (!$pago) {
                $_SESSION['error'] = 'Pago no encontrado';
                header('Location: index.php?controller=Pago&action=index');
                exit();
            }

            // cargar estudiantes y padres asociados al estudiante
            $estudiantes = $this->estudianteModel->obtenerEstudiantesActivos();
            $padres = [];
            try {
                if (!empty($pago['id_estudiante'])) {
                    $padres = $this->estudianteModel->obtenerPadresPorEstudianteId($pago['id_estudiante']);
                }
            } catch (\Exception $e) {
                error_log('Error al obtener padres para editar pago: ' . $e->getMessage());
            }

            // Si el pago tiene id_padre pero ese padre no está en la lista (p.ej. padre inactivo), cargarlo para que aparezca en el select
            if (!empty($pago['id_padre'])) {
                $found = false;
                foreach ($padres as $pad) {
                    if (isset($pad['id_padre']) && $pad['id_padre'] == $pago['id_padre']) {
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    try {
                        $padreModel = new PadreModel();
                        $padreExtra = $padreModel->buscarPorId($pago['id_padre']);
                        if ($padreExtra) {
                            // Asegurar que tenga los mismos campos esperados por la vista
                            $padres[] = $padreExtra;
                        }
                    } catch (\Exception $e) {
                        error_log('No se pudo cargar padre adicional para editar: ' . $e->getMessage());
                    }
                }
            }

            // Si el pago fue guardado con pagador_nombre (texto) pero no tiene id_padre, intentar hacer "match" por nombre
            if (empty($pago['id_padre']) && !empty($pago['pagador_nombre']) && !empty($padres)) {
                foreach ($padres as $pad) {
                    $full = trim(($pad['nombres'] ?? '') . ' ' . ($pad['apellidos'] ?? ''));
                    if ($full !== '' && strcasecmp($full, $pago['pagador_nombre']) === 0) {
                        // asignar id_padre para que el select muestre la opción correcta
                        $pago['id_padre'] = $pad['id_padre'];
                        break;
                    }
                }
            }

            $datos = [
                'titulo' => 'Editar Pago',
                'pago' => $pago,
                'estudiantes' => $estudiantes,
                'padres' => $padres
            ];

            $this->render('pagos/editar', $datos);
            return;
        }

        // POST -> guardar cambios
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                if (!$id) throw new \Exception('ID de pago no especificado');

                $pagoActual = $this->pagoModel->obtenerPorId($id);
                if (!$pagoActual) throw new \Exception('Pago no encontrado');

                $datos = [
                    'id_estudiante'   => filter_var($_POST['id_estudiante'], FILTER_VALIDATE_INT),
                    'id_padre'        => isset($_POST['id_padre']) && $_POST['id_padre'] !== '' ? filter_var($_POST['id_padre'], FILTER_VALIDATE_INT) : null,
                    'pagador_nombre'  => isset($_POST['pagador_nombre']) ? htmlspecialchars($_POST['pagador_nombre']) : null,
                    'pagador_dni'     => isset($_POST['pagador_dni']) ? htmlspecialchars($_POST['pagador_dni']) : null,
                    'concepto'        => htmlspecialchars($_POST['concepto'] ?? ''),
                    'banco'           => htmlspecialchars($_POST['banco'] ?? ''),
                    'monto'           => filter_var($_POST['monto'], FILTER_VALIDATE_FLOAT),
                    'metodo_pago'     => htmlspecialchars($_POST['metodo_pago'] ?? ''),
                    'fecha_pago'      => $_POST['fecha_pago'] ?? null,
                    'descuento'       => filter_var($_POST['descuento'] ?? 0, FILTER_VALIDATE_FLOAT),
                    'aumento'         => filter_var($_POST['aumento'] ?? 0, FILTER_VALIDATE_FLOAT),
                    'observaciones'   => htmlspecialchars($_POST['observaciones'] ?? ''),
                ];

                // validar
                if (!$datos['id_estudiante']) throw new \Exception('ID de estudiante inválido');
                if ($datos['monto'] === false || $datos['monto'] === null) throw new \Exception('Monto inválido');

                // Si seleccionaron un padre en el select y no enviaron nombre/dni, rellenarlos desde la tabla padres
                if (!empty($datos['id_padre'])) {
                    try {
                        $padreModel = new PadreModel();
                        $padreInfo = $padreModel->buscarPorId($datos['id_padre']);
                        if ($padreInfo) {
                            if (empty($datos['pagador_nombre'])) {
                                $datos['pagador_nombre'] = trim(($padreInfo['nombres'] ?? '') . ' ' . ($padreInfo['apellidos'] ?? ''));
                            }
                            if (empty($datos['pagador_dni'])) {
                                $datos['pagador_dni'] = $padreInfo['dni'] ?? null;
                            }
                        }
                    } catch (\Exception $e) {
                        error_log('No se pudo cargar datos del padre en editar POST: ' . $e->getMessage());
                    }
                }

                // manejar archivo: si se sube uno nuevo, reemplazar
                if (isset($_FILES['foto_baucher']) && !empty($_FILES['foto_baucher']['name'])) {
                    $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'pdf'];
                    $extension = strtolower(pathinfo($_FILES['foto_baucher']['name'], PATHINFO_EXTENSION));
                    if (!in_array($extension, $extensionesPermitidas)) {
                        throw new \Exception('Tipo de archivo no permitido');
                    }
                    $nombreArchivo = time() . '_' . uniqid() . '.' . $extension;
                    $rutaDestino = __DIR__ . '/../public/uploads/vouchers/' . $nombreArchivo;
                    if (!file_exists(dirname($rutaDestino))) mkdir(dirname($rutaDestino), 0777, true);
                    if (!move_uploaded_file($_FILES['foto_baucher']['tmp_name'], $rutaDestino)) {
                        throw new \Exception('Error al subir el voucher');
                    }
                    // eliminar antiguo
                    if (!empty($pagoActual['foto_baucher'])) {
                        $ant = __DIR__ . '/../public/uploads/vouchers/' . $pagoActual['foto_baucher'];
                        if (file_exists($ant)) @unlink($ant);
                    }
                    $datos['foto_baucher'] = $nombreArchivo;
                }

                $this->pagoModel->actualizar($id, $datos);

                $_SESSION['exito'] = 'Pago actualizado correctamente';
                header('Location: index.php?controller=Pago&action=index');
                exit();

            } catch (\Exception $e) {
                error_log('Error en PagoController::editar POST: ' . $e->getMessage());
                $_SESSION['error'] = 'Error al actualizar el pago: ' . $e->getMessage();
                header('Location: index.php?controller=Pago&action=editar&id=' . ($id ?? ''));
                exit();
            }
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