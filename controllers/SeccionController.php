<?php
namespace Controllers;

require_once __DIR__ . '/../core/BaseController.php';
require_once __DIR__ . '/../models/SeccionModel.php';

use Core\BaseController;
use Models\SeccionModel;

class SeccionController extends BaseController {
    private $seccionModel;
    
    public function __construct() {
        parent::__construct();
        
        // Verificar si el usuario ha iniciado sesión
        if (!$this->sesion->get('usuario')) {
            $this->redireccionar('auth/login');
            exit;
        }
        
        // Verificar si el usuario tiene permisos de administración
        if (!$this->tienePermiso(['Superadmin', 'Administrador'])) {
            $this->redireccionar('auth/acceso_denegado');
            exit;
        }
        
        $this->seccionModel = new SeccionModel();
    }
    
    /**
     * Lista todas las secciones
     */
    public function index() {
        $secciones = $this->seccionModel->obtenerTodas();
        return $this->vista->mostrar('secciones/listado', ['secciones' => $secciones]);
    }
    
    /**
     * Muestra el formulario para crear una nueva sección
     */
    public function crear() {
        return $this->vista->mostrar('secciones/crear');
    }
    
    /**
     * Procesa el formulario de creación de sección
     */
    public function guardar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('secciones');
            exit;
        }
        
        $nombre = filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_STRING);
        $descripcion = filter_input(INPUT_POST, 'descripcion', FILTER_SANITIZE_STRING);
        
        if (!$nombre) {
            $this->sesion->set('error', 'El nombre de la sección es obligatorio');
            $this->redireccionar('secciones/crear');
            exit;
        }
        
        // Verificar si ya existe una sección con el mismo nombre
        if ($this->seccionModel->existeNombre($nombre)) {
            $this->sesion->set('error', 'Ya existe una sección con ese nombre');
            $this->redireccionar('secciones/crear');
            exit;
        }
        
        $resultado = $this->seccionModel->crear([
            'nombre' => $nombre,
            'descripcion' => $descripcion
        ]);
        
        if ($resultado) {
            $this->sesion->set('exito', 'Sección creada correctamente');
            $this->redireccionar('secciones');
        } else {
            $this->sesion->set('error', 'Error al crear la sección');
            $this->redireccionar('secciones/crear');
        }
    }
    
    /**
     * Muestra el formulario para editar una sección
     */
    public function editar($id) {
        $seccion = $this->seccionModel->obtenerPorId($id);
        
        if (!$seccion) {
            $this->sesion->set('error', 'Sección no encontrada');
            $this->redireccionar('secciones');
            exit;
        }
        
        return $this->vista->mostrar('secciones/editar', ['seccion' => $seccion]);
    }
    
    /**
     * Procesa el formulario de edición de sección
     */
    public function actualizar($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('secciones');
            exit;
        }
        
        $nombre = filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_STRING);
        $descripcion = filter_input(INPUT_POST, 'descripcion', FILTER_SANITIZE_STRING);
        
        if (!$nombre) {
            $this->sesion->set('error', 'El nombre de la sección es obligatorio');
            $this->redireccionar("secciones/editar/{$id}");
            exit;
        }
        
        // Verificar si ya existe una sección con el mismo nombre (excluyendo la actual)
        if ($this->seccionModel->existeNombre($nombre, $id)) {
            $this->sesion->set('error', 'Ya existe otra sección con ese nombre');
            $this->redireccionar("secciones/editar/{$id}");
            exit;
        }
        
        $resultado = $this->seccionModel->actualizar($id, [
            'nombre' => $nombre,
            'descripcion' => $descripcion
        ]);
        
        if ($resultado) {
            $this->sesion->set('exito', 'Sección actualizada correctamente');
            $this->redireccionar('secciones');
        } else {
            $this->sesion->set('error', 'Error al actualizar la sección');
            $this->redireccionar("secciones/editar/{$id}");
        }
    }
    
    /**
     * Elimina una sección
     */
    public function eliminar($id) {
        if (!$this->tienePermiso(['Superadmin'])) {
            $this->sesion->set('error', 'No tiene permisos para eliminar secciones');
            $this->redireccionar('secciones');
            exit;
        }
        
        $resultado = $this->seccionModel->eliminar($id);
        
        if ($resultado) {
            $this->sesion->set('exito', 'Sección eliminada correctamente');
        } else {
            $this->sesion->set('error', 'No se puede eliminar la sección porque tiene salones asociados');
        }
        
        $this->redireccionar('secciones');
    }
    
    /**
     * Verifica si el usuario tiene alguno de los roles permitidos
     */
    private function tienePermiso($roles) {
        $usuarioRol = $this->sesion->get('usuario')['rol'];
        return in_array($usuarioRol, $roles);
    }
}