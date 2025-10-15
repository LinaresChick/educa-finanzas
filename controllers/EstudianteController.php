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
use Models\EstudianteModel;
use Models\UsuarioModel;
class EstudianteController extends BaseController {
    private $estudianteModel;
    private $usuarioModel;
    // En controllers/EstudianteController.php - MODIFICAR el constructor
    public function __construct() {
    parent::__construct();
    $this->estudianteModel = new EstudianteModel();
    
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

    $data['titulo'] = 'Registrar Nuevo Estudiante';
    $data['salones'] = $this->estudianteModel->obtenerSalonesDisponibles();
    $this->vista->mostrar('estudiantes/crear', $data);
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
            if (!isset($this->usuarioModel)) {
                $this->usuarioModel = new UsuarioModel();
            }            $correo = $_POST['correo'];
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
        
        $data['titulo'] = 'Detalle de Estudiante';
        $data['estudiante'] = $estudiante;
        $data['padres'] = $this->estudianteModel->obtenerPadresDeEstudiante($id);
        $data['deudas'] = $this->estudianteModel->obtenerDeudasPendientes($id);
        $data['pagos'] = $this->estudianteModel->obtenerHistorialPagos($id);
        
        $this->vista->mostrar('estudiantes/detalle', $data);
    }
    
    /**
     * Muestra el formulario para editar un estudiante
     * 
     * @param int $id ID del estudiante
     */
    public function editar($id = null) {
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
    
    /**
     * Procesa el formulario de edición de estudiante
     * 
     * @param int $id ID del estudiante
     */
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
            // Inicializar el modelo de usuarios si aún no está disponible
            if (!isset($this->usuarioModel)) {
                $this->usuarioModel = new UsuarioModel();
            }            $datosUsuario = [
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
