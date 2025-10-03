<?php
/**
 * Controlador para la gestión de pagos
 */
namespace Controllers;

require_once __DIR__ . '/../core/BaseController.php';
require_once __DIR__ . '/../core/Sesion.php';
require_once __DIR__ . '/../core/Vista.php';
require_once __DIR__ . '/../models/PagoModel.php';
require_once __DIR__ . '/../models/EstudianteModel.php';
require_once __DIR__ . '/../models/UsuarioModel.php';

use Core\BaseController;
use Core\Sesion;
use Core\Vista;
use Models\PagoModel;
use Models\EstudianteModel;
use Models\UsuarioModel;

class PagoController extends BaseController 
{
    private PagoModel $pagoModel;
    private EstudianteModel $estudianteModel;
    private UsuarioModel $usuarioModel;
    
    /**
     * Constructor de la clase
     */
    public function __construct() 
    {
        parent::__construct();
        
        // Verificar si el usuario ha iniciado sesión
        if (!$this->sesion->get('usuario')) {
            $this->redireccionar('auth/login');
            exit;
        }
        
        // Cargar modelos necesarios
        $this->pagoModel = new PagoModel();
        $this->estudianteModel = new EstudianteModel();
        $this->usuarioModel = new UsuarioModel();
    }
    
    /**
     * Muestra la lista de pagos registrados
     */
    public function index(): void
    {
        // Verificar permisos
        if (!$this->verificarRol(['admin', 'tesoreria', 'superadmin'])) {
            $this->accesoDenegado();
            return;
        }
        
        $filtros = $this->obtenerFiltros();
        $pagos = $this->aplicarFiltros($filtros);
        
        $this->vista->mostrar('pagos/listado', [
            'pagos' => $pagos,
            'busqueda' => $filtros['busqueda'],
            'estado' => $filtros['estado'],
            'fecha_inicio' => $filtros['fecha_inicio'],
            'fecha_fin' => $filtros['fecha_fin']
        ]);
    }
    
    /**
     * Muestra el formulario para registrar un nuevo pago
     */
    public function registrar(): void
    {
        // Verificar permisos
        if (!$this->verificarRol(['admin', 'tesoreria', 'superadmin'])) {
            $this->accesoDenegado();
            return;
        }
        
        $idEstudiante = $_GET['id_estudiante'] ?? 0;
        $estudiante = null;
        $deudas = [];
        
        if ($idEstudiante) {
            $estudiante = $this->estudianteModel->buscarPorId($idEstudiante);
            $deudas = $this->pagoModel->obtenerDeudasPendientes($idEstudiante);
        }
        
        $this->vista->mostrar('pagos/registrar', [
            'estudiantes' => $this->estudianteModel->obtenerTodos(),
            'estudiante_seleccionado' => $estudiante,
            'deudas' => $deudas,
            'metodos_pago' => $this->obtenerMetodosPago()
        ]);
    }
    
    /**
     * Procesa el formulario de registro de pago
     */
    public function guardar(): void
    {
        // Verificar permisos
        if (!$this->verificarRol(['admin', 'tesoreria', 'superadmin'])) {
            $this->accesoDenegado();
            return;
        }
        
        $this->verificarMetodoPOST();
        
        $datosPago = $this->validarDatosPago();
        
        if (isset($datosPago['error'])) {
            $this->redireccionarConError('pagos/registrar', $datosPago['error']);
        }
        
        $idPago = $this->pagoModel->registrarPago(
            $datosPago['pago'], 
            $datosPago['comprobante']
        );
        
        if ($idPago) {
            $this->procesarExitoRegistro($idPago, $datosPago['emitir_comprobante']);
        } else {
            $this->redireccionarConError('pagos/registrar', 'Ocurrió un error al registrar el pago');
        }
    }
    
    /**
     * Anula un pago existente
     */
    public function anular($idPago): void
    {
        // Verificar permisos
        if (!$this->verificarRol(['admin', 'tesoreria', 'superadmin'])) {
            $this->accesoDenegado();
            return;
        }
        
        $idPago = $this->validarIdPago($idPago);
        $pago = $this->obtenerPagoValido($idPago);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->procesarAnulacion($idPago);
            return;
        }
        
        $this->vista->mostrar('pagos/anular', ['pago' => $pago]);
    }
    
    /**
     * Muestra el historial de pagos de un estudiante
     */
    public function historial($idEstudiante = null): void
    {
        $idEstudiante = $this->determinarIdEstudiante($idEstudiante);
        $idEstudiante = $this->validarIdEstudiante($idEstudiante);
        
        $estudiante = $this->obtenerEstudianteValido($idEstudiante);
        $this->verificarPermisosHistorial($idEstudiante);
        
        $this->vista->mostrar('pagos/historial', [
            'estudiante' => $estudiante,
            'pagos' => $this->pagoModel->obtenerHistorialPagosEstudiante($idEstudiante),
            'deudas' => $this->pagoModel->obtenerDeudasPendientes($idEstudiante)
        ]);
    }
    
    /**
     * Muestra e imprime un comprobante de pago
     */
    public function comprobante($idPago): void
    {
        $idPago = $this->validarIdPago($idPago);
        $pago = $this->obtenerPagoValido($idPago);
        
        $this->verificarPermisosComprobante($pago);
        
        $this->vista->mostrar('pagos/comprobante', [
            'pago' => $this->formatearDatosComprobante($pago),
            'estudiante' => $this->estudianteModel->buscarPorId($pago['id_estudiante']),
            'registrado_por' => $pago['usuario_registro'] ? 
                $this->usuarioModel->buscarPorId($pago['usuario_registro']) : null,
            'modo_impresion' => isset($_GET['imprimir'])
        ]);
    }
    
    // ==================== MÉTODOS PRIVADOS DE APOYO ====================
    
    /**
     * Obtiene y valida los filtros de búsqueda
     */
    private function obtenerFiltros(): array
    {
        return [
            'busqueda' => $_GET['busqueda'] ?? '',
            'estado' => $_GET['estado'] ?? 'todos',
            'fecha_inicio' => $_GET['fecha_inicio'] ?? '',
            'fecha_fin' => $_GET['fecha_fin'] ?? ''
        ];
    }
    
    /**
     * Aplica filtros a la lista de pagos
     */
    private function aplicarFiltros(array $filtros): array
    {
        $condiciones = [];
        
        if ($filtros['estado'] && $filtros['estado'] != 'todos') {
            $condiciones['p.estado'] = $filtros['estado'];
        }
        
        $pagos = $this->pagoModel->obtenerPagosConInfo($condiciones);
        
        // Aplicar filtro de búsqueda
        if ($filtros['busqueda']) {
            $pagos = $this->filtrarPorBusqueda($pagos, $filtros['busqueda']);
        }
        
        // Aplicar filtros de fecha
        if ($filtros['fecha_inicio']) {
            $pagos = $this->filtrarPorFecha($pagos, $filtros['fecha_inicio'], 'inicio');
        }
        
        if ($filtros['fecha_fin']) {
            $pagos = $this->filtrarPorFecha($pagos, $filtros['fecha_fin'], 'fin');
        }
        
        return $pagos;
    }
    
    /**
     * Filtra pagos por texto de búsqueda
     */
    private function filtrarPorBusqueda(array $pagos, string $busqueda): array
    {
        $busqueda = strtolower($busqueda);
        
        return array_filter($pagos, function($pago) use ($busqueda) {
            return (
                strpos(strtolower($pago['estudiante_nombre_completo']), $busqueda) !== false ||
                strpos(strtolower($pago['concepto']), $busqueda) !== false ||
                strpos(strtolower($pago['numero_comprobante'] ?? ''), $busqueda) !== false
            );
        });
    }
    
    /**
     * Filtra pagos por fecha
     */
    private function filtrarPorFecha(array $pagos, string $fecha, string $tipo): array
    {
        return array_filter($pagos, function($pago) use ($fecha, $tipo) {
            if ($tipo === 'inicio') {
                return $pago['fecha_pago'] >= $fecha;
            } else {
                return $pago['fecha_pago'] <= $fecha;
            }
        });
    }
    
    /**
     * Obtiene los métodos de pago disponibles
     */
    private function obtenerMetodosPago(): array
    {
        return [
            'efectivo' => 'Efectivo',
            'transferencia' => 'Transferencia bancaria',
            'tarjeta' => 'Tarjeta de crédito/débito',
            'otro' => 'Otro'
        ];
    }
    
    /**
     * Valida los datos del formulario de pago
     */
    private function validarDatosPago(): array
    {
        $idEstudiante = filter_input(INPUT_POST, 'id_estudiante', FILTER_VALIDATE_INT);
        $concepto = filter_input(INPUT_POST, 'concepto', FILTER_SANITIZE_STRING);
        $monto = filter_input(INPUT_POST, 'monto', FILTER_VALIDATE_FLOAT);
        $metodoPago = filter_input(INPUT_POST, 'metodo_pago', FILTER_SANITIZE_STRING);
        $idDeuda = filter_input(INPUT_POST, 'id_deuda', FILTER_VALIDATE_INT) ?: null;
        $observaciones = filter_input(INPUT_POST, 'observaciones', FILTER_SANITIZE_STRING);
        $emitirComprobante = isset($_POST['emitir_comprobante']);
        $tipoComprobante = filter_input(INPUT_POST, 'tipo_comprobante', FILTER_SANITIZE_STRING);
        
        // Validar datos obligatorios
        if (!$idEstudiante || !$concepto || !$monto || !$metodoPago) {
            return ['error' => 'Todos los campos marcados con * son obligatorios'];
        }
        
        $datosPago = [
            'id_estudiante' => $idEstudiante,
            'id_deuda' => $idDeuda,
            'concepto' => $concepto,
            'monto' => $monto,
            'metodo_pago' => $metodoPago,
            'fecha_pago' => date('Y-m-d H:i:s'),
            'estado' => 'completado',
            'observaciones' => $observaciones,
            'usuario_registro' => $this->sesion->get('usuario')['id_usuario']
        ];
        
        $datosComprobante = null;
        if ($emitirComprobante) {
            $numeroComprobante = $this->pagoModel->generarNumeroComprobante($tipoComprobante);
            $datosComprobante = $this->calcularDatosComprobante($monto, $tipoComprobante, $numeroComprobante);
        }
        
        return [
            'pago' => $datosPago,
            'comprobante' => $datosComprobante,
            'emitir_comprobante' => $emitirComprobante
        ];
    }
    
    /**
     * Calcula los datos del comprobante
     */
    private function calcularDatosComprobante(float $monto, string $tipo, string $numero): array
    {
        $subtotal = $tipo === 'factura' ? round($monto / 1.18, 2) : $monto;
        $igv = $tipo === 'factura' ? round($monto - $subtotal, 2) : 0;
        
        return [
            'tipo' => $tipo,
            'numero' => $numero,
            'fecha_emision' => date('Y-m-d'),
            'subtotal' => $subtotal,
            'igv' => $igv,
            'total' => $monto
        ];
    }
    
    /**
     * Procesa el éxito del registro de pago
     */
    private function procesarExitoRegistro(int $idPago, bool $emitirComprobante): void
    {
        $this->sesion->set('exito', 'Pago registrado correctamente');
        
        if ($emitirComprobante) {
            header("Location: /pagos/comprobante/{$idPago}");
        } else {
            header("Location: /pagos");
        }
        exit;
    }
    
    /**
     * Procesa la anulación de un pago
     */
    private function procesarAnulacion(int $idPago): void
    {
        $motivo = filter_input(INPUT_POST, 'motivo', FILTER_SANITIZE_STRING);
        
        if (empty($motivo)) {
            $this->redireccionarConError("pagos/anular/{$idPago}", 'Debe indicar el motivo de la anulación');
        }
        
        $resultado = $this->pagoModel->anularPago($idPago, $motivo);
        
        if ($resultado) {
            $this->redireccionarConExito('pagos', 'Pago anulado correctamente');
        } else {
            $this->redireccionarConError("pagos/anular/{$idPago}", 'Ocurrió un error al anular el pago');
        }
    }
    
    /**
     * Determina el ID del estudiante según el contexto
     */
    private function determinarIdEstudiante(?int $idEstudiante): int
    {
        if ($idEstudiante !== null) {
            return $idEstudiante;
        }
        
        // Para usuarios administrativos, no hay estudiante por defecto
        if ($this->verificarRol(['admin', 'tesoreria', 'superadmin'])) {
            return 0;
        }
        
        // Para padres, obtener el ID del estudiante seleccionado o del primer hijo
        if ($this->verificarRol(['padre'])) {
            $idPadre = $this->sesion->get('usuario')['id_padre'];
            $estudiantes = $this->estudianteModel->obtenerEstudiantesPorPadre($idPadre);
            
            if (empty($estudiantes)) {
                $this->redireccionarConError('panel', 'No tiene estudiantes asociados a su cuenta');
            }
            
            return $_GET['id_estudiante'] ?? $estudiantes[0]['id_estudiante'];
        }
        
        // Para estudiantes, obtener su propio ID
        if ($this->verificarRol(['estudiante'])) {
            return $this->sesion->get('usuario')['id_estudiante'];
        }
        
        $this->accesoDenegado();
        return 0;
    }
    
    /**
     * Verifica permisos para ver historial
     */
    private function verificarPermisosHistorial(int $idEstudiante): void
    {
        // Padres solo pueden ver historial de sus hijos
        if ($this->verificarRol(['padre'])) {
            $idPadre = $this->sesion->get('usuario')['id_padre'];
            $esHijo = $this->estudianteModel->verificarRelacionPadreEstudiante($idPadre, $idEstudiante);
            
            if (!$esHijo) {
                $this->accesoDenegado();
            }
        }
        
        // Estudiantes solo pueden ver su propio historial
        if ($this->verificarRol(['estudiante'])) {
            $idEstudianteUsuario = $this->sesion->get('usuario')['id_estudiante'];
            
            if ($idEstudiante != $idEstudianteUsuario) {
                $this->accesoDenegado();
            }
        }
    }
    
    /**
     * Verifica permisos para ver comprobante
     */
    private function verificarPermisosComprobante(array $pago): void
    {
        $puedeVer = false;
        
        // Administradores y tesorería pueden ver todos los comprobantes
        if ($this->verificarRol(['admin', 'tesoreria', 'superadmin'])) {
            $puedeVer = true;
        } 
        // Estudiantes solo pueden ver sus propios comprobantes
        elseif ($this->verificarRol(['estudiante'])) {
            $idEstudiante = $this->sesion->get('usuario')['id_estudiante'];
            if ($pago['id_estudiante'] == $idEstudiante) {
                $puedeVer = true;
            }
        } 
        // Padres pueden ver los comprobantes de sus hijos
        elseif ($this->verificarRol(['padre'])) {
            $idPadre = $this->sesion->get('usuario')['id_padre'];
            $esHijo = $this->estudianteModel->verificarRelacionPadreEstudiante($idPadre, $pago['id_estudiante']);
            if ($esHijo) {
                $puedeVer = true;
            }
        }
        
        if (!$puedeVer) {
            $this->accesoDenegado();
        }
    }
    
    /**
     * Formatea datos para el comprobante
     */
    private function formatearDatosComprobante(array $pago): array
    {
        $pago['fecha_pago_formateada'] = date('d/m/Y H:i', strtotime($pago['fecha_pago']));
        
        if (!empty($pago['fecha_emision'])) {
            $pago['fecha_emision_formateada'] = date('d/m/Y', strtotime($pago['fecha_emision']));
        }
        
        return $pago;
    }
    
    /**
     * Valida ID de pago
     */
    private function validarIdPago($id): int
    {
        $idPago = filter_var($id, FILTER_VALIDATE_INT);
        if (!$idPago) {
            $this->redireccionarConError('pagos', 'ID de pago no válido');
        }
        return $idPago;
    }
    
    /**
     * Obtiene pago válido por ID
     */
    private function obtenerPagoValido(int $id): array
    {
        $pago = $this->pagoModel->obtenerPagoDetalle($id);
        if (!$pago) {
            $this->redireccionarConError('pagos', 'El pago no existe o ya fue eliminado');
        }
        return $pago;
    }
    
    /**
     * Valida ID de estudiante
     */
    private function validarIdEstudiante($id): int
    {
        $idEstudiante = filter_var($id, FILTER_VALIDATE_INT);
        if (!$idEstudiante) {
            $this->redireccionarConError('panel', 'ID de estudiante no válido');
        }
        return $idEstudiante;
    }
    
    /**
     * Obtiene estudiante válido por ID
     */
    private function obtenerEstudianteValido(int $id): array
    {
        $estudiante = $this->estudianteModel->buscarPorId($id);
        if (!$estudiante) {
            $this->redireccionarConError('panel', 'El estudiante no existe');
        }
        return $estudiante;
    }
    
    /**
     * Verifica si el usuario actual tiene alguno de los roles requeridos
     */
    private function verificarRol(array $roles): bool
    {
        $usuario = $this->sesion->get('usuario');
        return in_array($usuario['rol'], $roles);
    }
    
    /**
     * Verifica que el método de solicitud sea POST
     */
    private function verificarMetodoPOST(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /pagos');
            exit;
        }
    }
    
    /**
     * Redirige a la página de acceso denegado
     */
    private function accesoDenegado(): void
    {
        header('Location: /auth/acceso_denegado');
        exit;
    }
    
    /**
     * Redirige con mensaje de éxito
     */
    private function redireccionarConExito(string $ruta, string $mensaje): void
    {
        $this->sesion->set('exito', $mensaje);
        header("Location: /{$ruta}");
        exit;
    }
    
    /**
     * Redirige con mensaje de error
     */
    private function redireccionarConError(string $ruta, string $mensaje): void
    {
        $this->sesion->set('error', $mensaje);
        header("Location: /{$ruta}");
        exit;
    }
}