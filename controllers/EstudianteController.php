<?php
namespace Controllers;
require_once __DIR__ . '/../core/BaseController.php';
require_once __DIR__ . '/../core/Sesion.php';
require_once __DIR__ . '/../core/Vista.php';
require_once __DIR__ . '/../models/EstudianteModel.php';
require_once __DIR__ . '/../models/UsuarioModel.php';

use Core\BaseController;
use Core\Sesion;
use Core\Vista;
use Exception;
use Models\EstudianteModel;
use Models\UsuarioModel;

class EstudianteController extends BaseController {
    private $estudianteModel;
    private $usuarioModel;

    public function __construct() {
        parent::__construct();
        $this->estudianteModel = new EstudianteModel();
        $this->usuarioModel = new UsuarioModel();

        error_log("EstudianteController initialized.");
        
        // Verificación normal de roles
        $rolesPermitidos = ['Superadmin', 'Administrador', 'Colaborador'];
        if (!$this->sesion->tieneRol($rolesPermitidos)) {
            $this->redireccionar('auth/acceso_denegado');
        }
    }

    public function index() {
        $data['titulo'] = 'Listado de Estudiantes';
        $data['estudiantes'] = $this->estudianteModel->obtenerEstudiantesConInfo();
        $this->vista->mostrar('estudiantes/listado', $data);
    }

    public function crear() {
        try {
            $data['titulo'] = 'Registrar Nuevo Estudiante';
            $data['salones'] = $this->estudianteModel->obtenerSalonesDisponibles();
            
            $this->vista->mostrar('estudiantes/crear', $data);
            
        } catch (Exception $e) {
            error_log("Error en EstudianteController::crear: " . $e->getMessage());
            $this->sesion->setFlash('error', 'Error al cargar el formulario: ' . $e->getMessage());
            $this->redireccionar('estudiantes');
        }
    }

    public function guardar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('estudiantes');
            return;
        }
        
        $datosEstudiante = [
            'nombres' => $_POST['nombres'],
            'apellidos' => $_POST['apellidos'],
            'dni' => $_POST['dni'] ?? null,
            'mencion' => $_POST['mencion'] ?? null,
            'fecha_nacimiento' => $_POST['fecha_nacimiento'] ?? null,
            'direccion' => $_POST['direccion'] ?? null,
            'telefono' => $_POST['telefono'] ?? null,
            'estado' => 'activo'
        ];
        
        if (!empty($_POST['id_salon'])) {
            $datosEstudiante['id_salon'] = $_POST['id_salon'];
        }
        
        $datosUsuario = null;
        if (isset($_POST['crear_cuenta']) && $_POST['crear_cuenta'] == '1') {
            // ✅ CORREGIDO: Ya no necesitas verificar si existe
            $correo = $_POST['correo'];
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $datosUsuario = [
                'nombre' => $datosEstudiante['nombres'] . ' ' . $datosEstudiante['apellidos'],
                'correo' => $correo,
                'password' => $password,
                'rol' => 'Estudiante',
                'estado' => 'activo'
            ];
        }
        
        $resultado = $this->estudianteModel->crearEstudianteConUsuario($datosEstudiante, $datosUsuario);
        
        if ($resultado) {
            $this->sesion->setFlash('exito', 'Estudiante registrado correctamente.');
            $this->redireccionar('estudiantes/detalle/' . $resultado);
        } else {
            $this->sesion->setFlash('error', 'Error al registrar el estudiante.');
            $this->redireccionar('estudiantes/crear');
        }
    }
    /**
 * Muestra los detalles de un estudiante
 * 
 * @param int $id ID del estudiante
 */
public function detalle($id = null) {
    // Obtener ID de GET si no viene como parámetro
    if (!$id) {
        $id = $_GET['id'] ?? null;
    }

    if (!$id) {
        $this->sesion->setFlash('error', 'ID de estudiante no especificado.');
        $this->redireccionar('estudiantes');
        return;
    }
    
    try {
        $estudiante = $this->estudianteModel->obtenerEstudianteDetalle($id);
        
        if (!$estudiante) {
            $this->sesion->setFlash('error', 'Estudiante no encontrado.');
            $this->redireccionar('estudiantes');
            return;
        }
        
        $data['titulo'] = 'Detalle de Estudiante - ' . $estudiante['nombre_completo'];
        $data['estudiante'] = $estudiante;
        $data['padres'] = $this->estudianteModel->obtenerPadresDeEstudiante($id);
        $data['deudas'] = $this->estudianteModel->obtenerDeudasPendientes($id);
        $data['pagos'] = $this->estudianteModel->obtenerHistorialPagos($id);
        
        $this->vista->mostrar('estudiantes/detalle', $data);
        
    } catch (Exception $e) {
        error_log("Error en EstudianteController::detalle: " . $e->getMessage());
        $this->sesion->setFlash('error', 'Error al cargar los detalles del estudiante.');
        $this->redireccionar('estudiantes');
    }
}

    /**
     * Retorna un JSON con los padres asociados a un estudiante
     */
    public function obtenerPadresJSON() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('Content-Type: application/json');
            echo json_encode([]);
            exit;
        }

        try {
            $padres = $this->estudianteModel->obtenerPadresDeEstudiante($id);
            header('Content-Type: application/json');
            echo json_encode($padres);
            exit;
        } catch (\Exception $e) {
            error_log('Error en obtenerPadresJSON: ' . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode([]);
            exit;
        }
    }

/**
 * Muestra el formulario para editar un estudiante
 * 
 * @param int $id ID del estudiante
 */
public function editar($id = null) {
    // Obtener ID de GET si no viene como parámetro
    if (!$id) {
        $id = $_GET['id'] ?? null;
    }
    
    if (!$id) {
        $this->redireccionar('estudiantes');
        return;
    }
    
    $estudiante = $this->estudianteModel->obtenerEstudianteDetalle($id);
    
    if (!$estudiante) {
        $this->sesion->setFlash('error', 'Estudiante no encontrado.');
        $this->redireccionar('estudiantes');
        return;
    }
    
    $data['titulo'] = 'Editar Estudiante';
    $data['estudiante'] = $estudiante;
    $data['salones'] = $this->estudianteModel->obtenerSalonesDisponibles();
    
    $this->vista->mostrar('estudiantes/editar', $data);
}

    public function actualizar($id = null) {
        // Verificar si es una solicitud POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$id) {
            $this->redireccionar('estudiantes');
            return;
        }
        
        $estudiante = $this->estudianteModel->buscarPorId($id);
        
        if (!$estudiante) {
            $this->sesion->setFlash('error', 'Estudiante no encontrado.');
            $this->redireccionar('estudiantes');
            return;
        }
        
        // Recoger datos del formulario
        $datosEstudiante = [
            'nombres' => $_POST['nombres'],
            'apellidos' => $_POST['apellidos'],
            'dni' => $_POST['dni'] ?? null,
            'mencion' => $_POST['mencion'] ?? null,
            'fecha_nacimiento' => $_POST['fecha_nacimiento'] ?? null,
            'direccion' => $_POST['direccion'] ?? null,
            'telefono' => $_POST['telefono'] ?? null,
            'estado' => $_POST['estado']
        ];
        
        if (!empty($_POST['id_salon'])) {
            $datosEstudiante['id_salon'] = $_POST['id_salon'];
        }
        
        // Si hay un usuario asociado y se actualizó su correo
        $datosUsuario = null;
        if (!empty($estudiante['id_usuario']) && isset($_POST['correo'])) {
            // ✅ CORREGIDO: Ya no necesitas verificar si existe
            $datosUsuario = [
                'nombre' => $datosEstudiante['nombres'] . ' ' . $datosEstudiante['apellidos'],
                'correo' => $_POST['correo']
            ];
            
            // Si se proporcionó una nueva contraseña
            if (!empty($_POST['password'])) {
                $datosUsuario['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
            }
        }
        
        // Actualizar el estudiante
        $resultado = $this->estudianteModel->actualizarEstudianteConUsuario($id, $datosEstudiante, $datosUsuario);
        
        if ($resultado) {
            $this->sesion->setFlash('exito', 'Estudiante actualizado correctamente.');
            $this->redireccionar('estudiantes/detalle/' . $id);
        } else {
            $this->sesion->setFlash('error', 'Error al actualizar el estudiante.');
            $this->redireccionar('estudiantes/editar/' . $id);
        }
    }
    
    /**
     * Elimina un estudiante
     * 
     * @param int $id ID del estudiante
     */
    public function eliminar($id = null) {
        // Solo administradores pueden eliminar estudiantes
        if (!$this->sesion->tieneRol(['Superadmin', 'Administrador'])) {
            $this->sesion->setFlash('error', 'No tienes permisos para eliminar estudiantes.');
            $this->redireccionar('estudiantes');
            return;
        }
        
        if (!$id || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('estudiantes');
            return;
        }
        
        $resultado = $this->estudianteModel->eliminar($id);
        
        if ($resultado) {
            $this->sesion->setFlash('exito', 'Estudiante eliminado correctamente.');
        } else {
            $this->sesion->setFlash('error', 'Error al eliminar el estudiante.');
        }
        
        $this->redireccionar('estudiantes');
    }
    
    /**
     * Asocia un estudiante a un padre
     */
    public function asociarPadre() {
        // Verificar si es una solicitud POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('estudiantes');
            return;
        }
        
        $idEstudiante = $_POST['id_estudiante'] ?? null;
        $idPadre = $_POST['id_padre'] ?? null;
        $parentesco = $_POST['parentesco'] ?? 'Padre/Madre';
        
        if (!$idEstudiante || !$idPadre) {
            $this->sesion->setFlash('error', 'Faltan datos para asociar al padre.');
            $this->redireccionar('estudiantes/detalle/' . $idEstudiante);
            return;
        }
        
        $resultado = $this->estudianteModel->asociarPadre($idEstudiante, $idPadre, $parentesco);
        
        if ($resultado) {
            $this->sesion->setFlash('exito', 'Padre asociado correctamente.');
        } else {
            $this->sesion->setFlash('error', 'Error al asociar al padre.');
        }
        
        $this->redireccionar('estudiantes/detalle/' . $idEstudiante);
    }
    
    /**
     * Desasocia un estudiante de un padre
     */
    public function desasociarPadre() {
        // Verificar si es una solicitud POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('estudiantes');
            return;
        }
        
        $idEstudiante = $_POST['id_estudiante'] ?? null;
        $idPadre = $_POST['id_padre'] ?? null;
        
        if (!$idEstudiante || !$idPadre) {
            $this->sesion->setFlash('error', 'Faltan datos para desasociar al padre.');
            $this->redireccionar('estudiantes/detalle/' . $idEstudiante);
            return;
        }
        
        $resultado = $this->estudianteModel->desasociarPadre($idEstudiante, $idPadre);
        
        if ($resultado) {
            $this->sesion->setFlash('exito', 'Padre desasociado correctamente.');
        } else {
            $this->sesion->setFlash('error', 'Error al desasociar al padre.');
        }
        
        $this->redireccionar('estudiantes/detalle/' . $idEstudiante);
    }
    
    /**
     * Busca estudiantes según un término
     */
    public function buscar() {
        // Verificar si es una solicitud GET
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->redireccionar('estudiantes');
            return;
        }
        
        $termino = $_GET['termino'] ?? '';
        
        if (empty($termino)) {
            $this->redireccionar('estudiantes');
            return;
        }
        
        $data['titulo'] = 'Resultados de búsqueda: ' . $termino;
        $data['termino'] = $termino;
        $data['estudiantes'] = $this->estudianteModel->buscarEstudiantes($termino);
        
        $this->vista->mostrar('estudiantes/listado', $data);
    }
}
