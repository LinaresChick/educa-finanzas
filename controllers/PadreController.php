<?php
/**
 * Controlador para la gestión de padres o tutores
 */
namespace Controllers;

require_once __DIR__ . '/../core/BaseController.php';
require_once __DIR__ . '/../core/Sesion.php';
require_once __DIR__ . '/../core/Vista.php';
require_once __DIR__ . '/../models/PadreModel.php';
require_once __DIR__ . '/../models/UsuarioModel.php';
require_once __DIR__ . '/../models/EstudianteModel.php';

use Core\BaseController;
use Core\Sesion;
use Core\Vista;
use Models\PadreModel;
use Models\UsuarioModel;
use Models\EstudianteModel;

class PadreController extends BaseController {
    private $padreModel;
    private $usuarioModel;
    private $estudianteModel;
    
    /**
     * Constructor de la clase
     */
    public function __construct() {
        parent::__construct();
        $this->padreModel = new PadreModel();
        
        // Verificar si el usuario tiene acceso a este controlador
        // Se amplían roles permitidos: Administrador, Contador, Director, Secretario
        $rolesPermitidos = ['Superadmin', 'Administrador', 'Contador', 'Director', 'Secretario', 'Colaborador'];
        if (!$this->sesion->tieneRol($rolesPermitidos)) {
            $this->redireccionar('auth/acceso_denegado');
        }
    }
    
    /**
     * Método por defecto, muestra el listado de padres
     */
    public function index() {
        $data['titulo'] = 'Listado de Padres o Tutores';
        $data['padres'] = $this->padreModel->obtenerPadresConInfo();
        $this->vista->mostrar('padres/listado', $data);
    }
    
    /**
     * Muestra el formulario para crear un nuevo padre
     */
    public function crear() {
        $data['titulo'] = 'Registrar Nuevo Padre o Tutor';
        $this->vista->mostrar('padres/crear', $data);
    }
    
    /**
     * Procesa el formulario de creación de padre
     */
    public function guardar() {
        // Verificar si es una solicitud POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('padres');
            return;
        }
        
        // Recoger datos del formulario
        $datosPadre = [
            'nombres' => $_POST['nombres'],
            'apellidos' => $_POST['apellidos'],
            'dni' => $_POST['dni'] ?? null,
            'telefono' => $_POST['telefono'] ?? null,
            'correo' => $_POST['correo'] ?? null,
            'direccion' => $_POST['direccion'] ?? null,
            'relacion' => $_POST['relacion'],
            'estado' => 'activo'
        ];
        
        // Si se indica crear cuenta de usuario
        $datosUsuario = null;
        if (isset($_POST['crear_cuenta']) && $_POST['crear_cuenta'] == '1') {
            // Incluir el modelo de usuarios si aún no está disponible
            if (!isset($this->usuarioModel)) {
                require_once MODELS_PATH . 'UsuarioModel.php';
                $this->usuarioModel = new UsuarioModel();
            }
            
            $correo = $_POST['correo_usuario'];
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            
            $datosUsuario = [
                'nombre' => $datosPadre['nombres'] . ' ' . $datosPadre['apellidos'],
                'correo' => $correo,
                'password' => $password,
                'rol' => 'Padre',
                'estado' => 'activo'
            ];
        }
        
        // Guardar el padre
        $resultado = $this->padreModel->crearPadreConUsuario($datosPadre, $datosUsuario);
        
        if ($resultado) {
            $this->sesion->setFlash('exito', 'Padre o tutor registrado correctamente.');
            $this->redireccionar('padres/detalle/' . $resultado);
        } else {
            $this->sesion->setFlash('error', 'Error al registrar el padre o tutor.');
            $this->redireccionar('padres/crear');
        }
    }
    
    /**
     * Muestra los detalles de un padre
     * 
     * @param int $id ID del padre
     */
    public function detalle($id = null) {
        if (!$id) {
            $this->redireccionar('padres');
            return;
        }
        
        $padre = $this->padreModel->obtenerPadreDetalle($id);
        
        if (!$padre) {
            $this->sesion->setFlash('error', 'Padre o tutor no encontrado.');
            $this->redireccionar('padres');
            return;
        }
        
        $data['titulo'] = 'Detalle de Padre o Tutor';
        $data['padre'] = $padre;
        $data['estudiantes'] = $this->padreModel->obtenerEstudiantesDePadre($id);
        
        $this->vista->mostrar('padres/detalle', $data);
    }
    
    /**
     * Muestra el formulario para editar un padre
     * 
     * @param int $id ID del padre
     */
    public function editar($id = null) {
        if (!$id) {
            $this->redireccionar('padres');
            return;
        }
        
        $padre = $this->padreModel->obtenerPadreDetalle($id);
        
        if (!$padre) {
            $this->sesion->setFlash('error', 'Padre o tutor no encontrado.');
            $this->redireccionar('padres');
            return;
        }
        
        $data['titulo'] = 'Editar Padre o Tutor';
        $data['padre'] = $padre;
        
        $this->vista->mostrar('padres/editar', $data);
    }
    
    /**
     * Procesa el formulario de edición de padre
     * 
     * @param int $id ID del padre
     */
    public function actualizar($id = null) {
        // Verificar si es una solicitud POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$id) {
            $this->redireccionar('padres');
            return;
        }
        
        $padre = $this->padreModel->buscarPorId($id);
        
        if (!$padre) {
            $this->sesion->setFlash('error', 'Padre o tutor no encontrado.');
            $this->redireccionar('padres');
            return;
        }
        
        // Recoger datos del formulario
        $datosPadre = [
            'nombres' => $_POST['nombres'],
            'apellidos' => $_POST['apellidos'],
            'dni' => $_POST['dni'] ?? null,
            'telefono' => $_POST['telefono'] ?? null,
            'correo' => $_POST['correo'] ?? null,
            'direccion' => $_POST['direccion'] ?? null,
            'relacion' => $_POST['relacion'],
            'estado' => $_POST['estado']
        ];
        
        // Si hay un usuario asociado y se actualizó su correo
        $datosUsuario = null;
        if (!empty($padre['id_usuario']) && isset($_POST['correo_usuario'])) {
            // Incluir el modelo de usuarios si aún no está disponible
            if (!isset($this->usuarioModel)) {
                require_once MODELS_PATH . 'UsuarioModel.php';
                $this->usuarioModel = new UsuarioModel();
            }
            
            $datosUsuario = [
                'nombre' => $datosPadre['nombres'] . ' ' . $datosPadre['apellidos'],
                'correo' => $_POST['correo_usuario']
            ];
            
            // Si se proporcionó una nueva contraseña
            if (!empty($_POST['password'])) {
                $datosUsuario['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
            }
        }
        
        // Actualizar el padre
        $resultado = $this->padreModel->actualizarPadreConUsuario($id, $datosPadre, $datosUsuario);
        
        if ($resultado) {
            $this->sesion->setFlash('exito', 'Padre o tutor actualizado correctamente.');
            $this->redireccionar('padres/detalle/' . $id);
        } else {
            $this->sesion->setFlash('error', 'Error al actualizar el padre o tutor.');
            $this->redireccionar('padres/editar/' . $id);
        }
    }
    
    /**
     * Elimina un padre
     * 
     * @param int $id ID del padre
     */
    public function eliminar($id = null) {
        // Solo administradores pueden eliminar padres
            if (!$this->sesion->tieneRol(['Superadmin', 'Administrador', 'Director', 'Secretario'])) {
            $this->sesion->setFlash('error', 'No tienes permisos para eliminar padres.');
            $this->redireccionar('padres');
            return;
        }
        
        if (!$id || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('padres');
            return;
        }
        
        $resultado = $this->padreModel->eliminar($id);
        
        if ($resultado) {
            $this->sesion->setFlash('exito', 'Padre o tutor eliminado correctamente.');
        } else {
            $this->sesion->setFlash('error', 'Error al eliminar el padre o tutor.');
        }
        
        $this->redireccionar('padres');
    }
    
    /**
     * Muestra la vista para asociar un estudiante a un padre
     * 
     * @param int $id ID del padre
     */
    public function asociarEstudiante($id = null) {
        if (!$id) {
            $this->redireccionar('padres');
            return;
        }
        
        $padre = $this->padreModel->obtenerPadreDetalle($id);
        
        if (!$padre) {
            $this->sesion->setFlash('error', 'Padre o tutor no encontrado.');
            $this->redireccionar('padres');
            return;
        }
        
        $data['titulo'] = 'Asociar Estudiante';
        $data['padre'] = $padre;
        $data['estudiantes'] = $this->padreModel->obtenerEstudiantesNoAsociados($id);
        
        $this->vista->mostrar('padres/asociar_estudiante', $data);
    }
    
    /**
     * Procesa el formulario de asociación de estudiante
     */
    public function guardarAsociacion() {
        // Verificar si es una solicitud POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('padres');
            return;
        }
        
        $idPadre = $_POST['id_padre'] ?? null;
        $idEstudiante = $_POST['id_estudiante'] ?? null;
        $parentesco = $_POST['parentesco'] ?? 'Padre/Madre';
        
        if (!$idPadre || !$idEstudiante) {
            $this->sesion->setFlash('error', 'Faltan datos para asociar al estudiante.');
            $this->redireccionar('padres/detalle/' . $idPadre);
            return;
        }
        
        $resultado = $this->padreModel->asociarEstudiante($idPadre, $idEstudiante, $parentesco);
        
        if ($resultado) {
            $this->sesion->setFlash('exito', 'Estudiante asociado correctamente.');
        } else {
            $this->sesion->setFlash('error', 'Error al asociar al estudiante.');
        }
        
        $this->redireccionar('padres/detalle/' . $idPadre);
    }
    
    /**
     * Desasocia un estudiante de un padre
     */
    public function desasociarEstudiante() {
        // Verificar si es una solicitud POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('padres');
            return;
        }
        
        $idPadre = $_POST['id_padre'] ?? null;
        $idEstudiante = $_POST['id_estudiante'] ?? null;
        
        if (!$idPadre || !$idEstudiante) {
            $this->sesion->setFlash('error', 'Faltan datos para desasociar al estudiante.');
            $this->redireccionar('padres/detalle/' . $idPadre);
            return;
        }
        
        $resultado = $this->padreModel->desasociarEstudiante($idPadre, $idEstudiante);
        
        if ($resultado) {
            $this->sesion->setFlash('exito', 'Estudiante desasociado correctamente.');
        } else {
            $this->sesion->setFlash('error', 'Error al desasociar al estudiante.');
        }
        
        $this->redireccionar('padres/detalle/' . $idPadre);
    }
    
    /**
     * Busca padres según un término
     */
    public function buscar() {
        // Verificar si es una solicitud GET
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->redireccionar('padres');
            return;
        }
        
        $termino = $_GET['termino'] ?? '';
        
        if (empty($termino)) {
            $this->redireccionar('padres');
            return;
        }
        
        $data['titulo'] = 'Resultados de búsqueda: ' . $termino;
        $data['termino'] = $termino;
        $data['padres'] = $this->padreModel->buscarPadres($termino);
        
        $this->vista->mostrar('padres/listado', $data);
    }
    
    /**
     * Retorna un JSON con todos los padres para seleccionar en formularios
     */
    public function obtenerPadresJSON() {
        $padres = $this->padreModel->obtenerPadresParaSelect();
        
        header('Content-Type: application/json');
        echo json_encode($padres);
        exit;
    }
}
