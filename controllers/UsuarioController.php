<?php
/**
 * Controlador para la gestión de usuarios
 */
namespace Controllers;

require_once __DIR__ . '/../core/BaseController.php';
require_once __DIR__ . '/../core/Sesion.php';
require_once __DIR__ . '/../core/Vista.php';
require_once __DIR__ . '/../models/UsuarioModel.php';

use Core\BaseController;
use Core\Sesion;
use Core\Vista;
use Models\UsuarioModel;

class UsuarioController extends BaseController 
{
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
        
        $this->usuarioModel = new UsuarioModel();
    }
    
    /**
     * Muestra el perfil del usuario actual
     */
    public function perfil(): void
    {
        try {
            $usuario = $this->sesion->get('usuario');
            if (!$usuario) {
                $this->redireccionar('auth/login');
                exit;
            }

            // Obtener datos completos del usuario
            $datosUsuario = $this->usuarioModel->buscarPorId($usuario['id_usuario']);
            
            if (!$datosUsuario) {
                $this->redireccionarConError('panel', 'No se pudo cargar la información del perfil');
                return;
            }

            // Si es POST, procesar actualización
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->actualizarPerfil($usuario['id_usuario']);
                return;
            }

            $this->vista->mostrar('usuarios/perfil', [
                'usuario' => $datosUsuario,
                'titulo' => 'Mi Perfil'
            ]);
        } catch (\Exception $e) {
            $this->sesion->set('error', 'Error al cargar el perfil: ' . $e->getMessage());
            $this->redireccionar('panel');
        }
    }

    /**
     * Actualiza el perfil del usuario actual
     */
    private function actualizarPerfil(int $idUsuario): void
    {
        $nombre = filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_STRING);
        $correo = filter_input(INPUT_POST, 'correo', FILTER_SANITIZE_EMAIL);
        $passwordActual = filter_input(INPUT_POST, 'password_actual', FILTER_SANITIZE_STRING);
        $passwordNueva = filter_input(INPUT_POST, 'password_nueva', FILTER_SANITIZE_STRING);
        $passwordConfirm = filter_input(INPUT_POST, 'password_confirm', FILTER_SANITIZE_STRING);

        if (!$nombre || !$correo) {
            $this->redireccionarConError('usuarios/perfil', 'El nombre y correo son obligatorios');
            return;
        }

        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $this->redireccionarConError('usuarios/perfil', 'El formato del correo no es válido');
            return;
        }

        // Verificar si el correo ya existe (para otro usuario)
        $usuarioExistente = $this->usuarioModel->buscarPorCorreo($correo);
        if ($usuarioExistente && $usuarioExistente['id_usuario'] != $idUsuario) {
            $this->redireccionarConError('usuarios/perfil', 'El correo ya está en uso por otro usuario');
            return;
        }

        $datos = [
            'nombre' => $nombre,
            'correo' => $correo
        ];

        // Si desea cambiar la contraseña
        if (!empty($passwordNueva)) {
            if (empty($passwordActual)) {
                $this->redireccionarConError('usuarios/perfil', 'Debe ingresar su contraseña actual para cambiarla');
                return;
            }

            if ($passwordNueva !== $passwordConfirm) {
                $this->redireccionarConError('usuarios/perfil', 'Las contraseñas nuevas no coinciden');
                return;
            }

            if (strlen($passwordNueva) < 6) {
                $this->redireccionarConError('usuarios/perfil', 'La contraseña debe tener al menos 6 caracteres');
                return;
            }

            // Verificar contraseña actual
            $usuarioActual = $this->usuarioModel->buscarPorId($idUsuario);
            if (!password_verify($passwordActual, $usuarioActual['password'])) {
                $this->redireccionarConError('usuarios/perfil', 'La contraseña actual es incorrecta');
                return;
            }

            $datos['password'] = password_hash($passwordNueva, PASSWORD_DEFAULT);
        }

        $resultado = $this->usuarioModel->actualizar($idUsuario, $datos);

        if ($resultado) {
            // Actualizar sesión con nuevos datos
            $usuarioActualizado = $this->usuarioModel->buscarPorId($idUsuario);
            $this->sesion->set('usuario', $usuarioActualizado);
            
            $this->redireccionarConExito('usuarios/perfil', 'Perfil actualizado correctamente');
        } else {
            $this->redireccionarConError('usuarios/perfil', 'Error al actualizar el perfil');
        }
    }
    
    /**
     * Verifica si el usuario tiene permisos administrativos
     */
    private function verificarPermisosAdministrativos(): void
    {
        $usuario = $this->sesion->get('usuario');
        $rolesPermitidos = ['superadmin', 'administrador'];
        
        if (!$usuario || !in_array(strtolower($usuario['rol']), $rolesPermitidos)) {
            $this->redireccionar('auth/acceso_denegado');
            exit;
        }
    }
    
    /**
     * Muestra la lista de usuarios
     */
    public function index(): void
    {
        // Verificar permisos administrativos solo para index
        $this->verificarPermisosAdministrativos();
        
        try {
            $usuarios = $this->usuarioModel->obtenerTodos();
            $this->vista->mostrar('usuarios/listado', [
                'usuarios' => $usuarios,
                'titulo' => 'Gestión de Usuarios'
            ]);
        } catch (\Exception $e) {
            $this->sesion->set('error', 'Error al cargar la lista de usuarios: ' . $e->getMessage());
            $this->redireccionar('panel');
        }
    }
    
    /**
     * Muestra el formulario para crear un nuevo usuario
     */
    public function crear(): void
    {
        $this->verificarPermisosAdministrativos();
        $roles = $this->filtrarRolesPorPermisos(
            $this->usuarioModel->obtenerRoles()
        );
        
        $this->vista->mostrar('usuarios/crear', ['roles' => $roles]);
    }
    
    /**
     * Procesa el formulario para crear un nuevo usuario
     */
    public function guardar(): void
    {
        $this->verificarMetodoPOST();
        
        $datos = $this->validarDatosUsuario();
        
        if ($this->tieneErroresValidacion($datos)) {
            $this->redireccionarConError('usuarios/crear', $datos['error']);
        }
        
        if ($this->usuarioModel->correoExiste($datos['correo'])) {
            $this->redireccionarConError('usuarios/crear', 'El correo electrónico ya está registrado');
        }
        
        $resultado = $this->usuarioModel->crearUsuario($datos);
        
        if ($resultado) {
            $this->redireccionarConExito('usuarios', 'Usuario creado correctamente');
        } else {
            $this->redireccionarConError('usuarios/crear', 'Error al crear el usuario');
        }
    }
    
    /**
     * Muestra el formulario para editar un usuario existente
     */
    public function editar($id): void
    {
        $idUsuario = $this->validarIdUsuario($id);
        $usuario = $this->obtenerUsuarioValido($idUsuario);
        
        $this->verificarPermisosEdicion($usuario);
        
        $roles = $this->filtrarRolesPorPermisos(
            $this->usuarioModel->obtenerRoles()
        );
        
        $this->vista->mostrar('usuarios/editar', [
            'usuario' => $usuario,
            'roles' => $roles
        ]);
    }
    
    /**
     * Procesa el formulario para actualizar un usuario existente
     */
    public function actualizar(): void
    {
        $this->verificarMetodoPOST();
        
        $idUsuario = filter_input(INPUT_POST, 'id_usuario', FILTER_VALIDATE_INT);
        $datos = $this->validarDatosActualizacion($idUsuario);
        
        if ($this->tieneErroresValidacion($datos)) {
            $this->redireccionarConError("usuarios/editar/{$idUsuario}", $datos['error']);
        }
        
        $usuarioActual = $this->obtenerUsuarioValido($idUsuario);
        $this->verificarPermisosEdicion($usuarioActual);
        
        if (!empty($datos['password'])) {
            $datos['password'] = password_hash($datos['password'], PASSWORD_DEFAULT);
        }
        
        $resultado = $this->usuarioModel->actualizarUsuario($idUsuario, $datos);
        
        if ($resultado) {
            $this->redireccionarConExito('usuarios', 'Usuario actualizado correctamente');
        } else {
            $this->redireccionarConError(
                "usuarios/editar/{$idUsuario}", 
                'Error al actualizar el usuario. Verifique que el correo no esté en uso por otro usuario.'
            );
        }
    }
    
    /**
     * Elimina o desactiva un usuario
     */
    public function eliminar($id): void
    {
        $idUsuario = $this->validarIdUsuario($id);
        $usuario = $this->obtenerUsuarioValido($idUsuario);
        
        $this->verificarPermisosEliminacion($usuario, $idUsuario);
        
        $resultado = $this->usuarioModel->cambiarEstado($idUsuario, 'inactivo');
        
        if ($resultado) {
            $this->redireccionarConExito('usuarios', 'Usuario desactivado correctamente');
        } else {
            $this->redireccionarConError('usuarios', 'Error al desactivar el usuario');
        }
    }
    
    /**
     * Muestra la página de roles y permisos
     */
    public function roles(): void
    {
        $roles = $this->usuarioModel->obtenerRoles();
        
        $this->vista->mostrar('usuarios/roles', ['roles' => $roles]);
    }
    
    /**
     * Crea un usuario para un estudiante
     */
    public function crearUsuarioEstudiante($idEstudiante = null): void
    {
        if (!$idEstudiante) {
            $this->redireccionarConError('estudiantes', 'ID de estudiante no válido');
            return;
        }
        
        $idEstudiante = $this->validarIdEstudiante($idEstudiante);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->procesarCreacionUsuarioEstudiante($idEstudiante);
            return;
        }
        
        // Obtener datos del estudiante
        $estudiante = $this->usuarioModel->obtenerDatosEstudiante($idEstudiante);
        
        if (!$estudiante) {
            $this->redireccionarConError('estudiantes', 'Estudiante no encontrado');
            return;
        }
        
        // Verificar si ya tiene usuario
        if (!empty($estudiante['id_usuario'])) {
            $this->redireccionarConError('estudiantes/detalle/' . $idEstudiante, 'Este estudiante ya tiene una cuenta de acceso creada.');
            return;
        }
        
        $this->vista->mostrar('usuarios/crear_estudiante', [
            'estudiante' => $estudiante,
            'titulo' => 'Crear Acceso para Estudiante'
        ]);
    }

    /**
     * Crea un usuario para un padre
     */
    public function crearUsuarioPadre($idPadre): void
    {
        $idPadre = $this->validarIdPadre($idPadre);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->procesarCreacionUsuarioPadre($idPadre);
            return;
        }
        
        $padre = $this->usuarioModel->obtenerDatosPadre($idPadre);
        
        if (!$padre) {
            $this->redireccionarConError('padres', 'Padre/tutor no encontrado');
        }
        
        $this->vista->mostrar('usuarios/crear_padre', ['padre' => $padre]);
    }
    
    /**
     * Cambia la contraseña de un usuario
     */
    public function cambiarPassword(): void
    {
        $this->verificarMetodoPOST();
        
        $datosPassword = $this->validarDatosCambioPassword();
        
        if ($this->tieneErroresValidacion($datosPassword)) {
            $this->redireccionarConError('panel', $datosPassword['error']);
        }
        
        $resultado = $this->usuarioModel->cambiarPassword(
            $this->sesion->get('usuario')['id_usuario'],
            $datosPassword['password_actual'],
            $datosPassword['password_nueva']
        );
        
        if ($resultado) {
            $this->redireccionarConExito('panel', 'Contraseña actualizada correctamente');
        } else {
            $this->redireccionarConError('panel', 'Error al cambiar la contraseña. Verifique su contraseña actual.');
        }
    }
    
    // ==================== MÉTODOS PRIVADOS DE APOYO ====================
    
    /**
     * Valida los datos básicos de usuario
     */
    private function validarDatosUsuario(): array
    {
        $nombre = filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_STRING);
        $correo = filter_input(INPUT_POST, 'correo', FILTER_SANITIZE_EMAIL);
        $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);
        $rol = filter_input(INPUT_POST, 'rol', FILTER_SANITIZE_STRING);
        $estado = filter_input(INPUT_POST, 'estado', FILTER_SANITIZE_STRING) ?: 'activo';
        
        if (!$nombre || !$correo || !$password || !$rol) {
            return ['error' => 'Todos los campos marcados con * son obligatorios'];
        }
        
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            return ['error' => 'El formato del correo electrónico no es válido'];
        }
        
        if ($this->sesion->get('usuario')['rol'] !== 'superadmin' && $rol === 'superadmin') {
            return ['error' => 'No tiene permisos para crear usuarios superadministradores'];
        }
        
        return [
            'nombre' => $nombre,
            'correo' => $correo,
            'password' => $password,
            'rol' => $rol,
            'estado' => $estado
        ];
    }
    
    /**
     * Valida los datos para actualización de usuario
     */
    private function validarDatosActualizacion(?int $idUsuario): array
    {
        if (!$idUsuario) {
            return ['error' => 'ID de usuario no válido'];
        }
        
        $nombre = filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_STRING);
        $correo = filter_input(INPUT_POST, 'correo', FILTER_SANITIZE_EMAIL);
        $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);
        $rol = filter_input(INPUT_POST, 'rol', FILTER_SANITIZE_STRING);
        $estado = filter_input(INPUT_POST, 'estado', FILTER_SANITIZE_STRING);
        
        if (!$nombre || !$correo || !$rol || !$estado) {
            return ['error' => 'Todos los campos marcados con * son obligatorios'];
        }
        
        if ($this->sesion->get('usuario')['rol'] !== 'superadmin' && $rol === 'superadmin') {
            return ['error' => 'No tiene permisos para asignar el rol de superadministrador'];
        }
        
        $datos = [
            'nombre' => $nombre,
            'correo' => $correo,
            'rol' => $rol,
            'estado' => $estado
        ];
        
        if ($password) {
            $datos['password'] = $password;
        }
        
        return $datos;
    }
    
    /**
     * Valida datos para cambio de contraseña
     */
    private function validarDatosCambioPassword(): array
    {
        $passwordActual = filter_input(INPUT_POST, 'password_actual', FILTER_SANITIZE_STRING);
        $passwordNueva = filter_input(INPUT_POST, 'password_nueva', FILTER_SANITIZE_STRING);
        $passwordConfirm = filter_input(INPUT_POST, 'password_confirm', FILTER_SANITIZE_STRING);
        
        if (!$passwordActual || !$passwordNueva || !$passwordConfirm) {
            return ['error' => 'Todos los campos son obligatorios'];
        }
        
        if ($passwordNueva !== $passwordConfirm) {
            return ['error' => 'Las contraseñas nuevas no coinciden'];
        }
        
        return [
            'password_actual' => $passwordActual,
            'password_nueva' => $passwordNueva
        ];
    }
    
    /**
     * Filtra roles según permisos del usuario actual
     */
    private function filtrarRolesPorPermisos(array $roles): array
    {
        if ($this->sesion->get('usuario')['rol'] !== 'superadmin') {
            unset($roles['superadmin']);
        }
        return $roles;
    }
    
    /**
     * Verifica permisos para editar un usuario
     */
    private function verificarPermisosEdicion(array $usuario): void
    {
        if ($usuario['rol'] === 'superadmin' && $this->sesion->get('usuario')['rol'] !== 'superadmin') {
            $this->redireccionarConError('usuarios', 'No tiene permisos para editar este usuario');
        }
    }
    
    /**
     * Verifica permisos para eliminar un usuario
     */
    private function verificarPermisosEliminacion(array $usuario, int $idUsuario): void
    {
        if ($usuario['rol'] === 'superadmin' && $this->sesion->get('usuario')['rol'] !== 'superadmin') {
            $this->redireccionarConError('usuarios', 'No tiene permisos para eliminar este usuario');
        }
        
        if ($idUsuario == $this->sesion->get('usuario')['id_usuario']) {
            $this->redireccionarConError('usuarios', 'No puede eliminar su propio usuario');
        }
    }
    
    /**
     * Valida ID de usuario
     */
    private function validarIdUsuario($id): int
    {
        $idUsuario = filter_var($id, FILTER_VALIDATE_INT);
        if (!$idUsuario) {
            $this->redireccionarConError('usuarios', 'ID de usuario no válido');
        }
        return $idUsuario;
    }
    
    /**
     * Obtiene usuario válido por ID
     */
    private function obtenerUsuarioValido(int $id): array
    {
        $usuario = $this->usuarioModel->buscarPorId($id);
        if (!$usuario) {
            $this->redireccionarConError('usuarios', 'Usuario no encontrado');
        }
        return $usuario;
    }
    
    /**
     * Valida ID de estudiante
     */
    private function validarIdEstudiante($id): int
    {
        $idEstudiante = filter_var($id, FILTER_VALIDATE_INT);
        if (!$idEstudiante) {
            $this->redireccionarConError('estudiantes', 'ID de estudiante no válido');
        }
        return $idEstudiante;
    }
    
    /**
     * Valida ID de padre
     */
    private function validarIdPadre($id): int
    {
        $idPadre = filter_var($id, FILTER_VALIDATE_INT);
        if (!$idPadre) {
            $this->redireccionarConError('padres', 'ID de padre no válido');
        }
        return $idPadre;
    }
    
    /**
     * Procesa la creación de usuario para estudiante
     */
    private function procesarCreacionUsuarioEstudiante(int $idEstudiante): void
    {
        $correo = filter_input(INPUT_POST, 'correo', FILTER_SANITIZE_EMAIL);
        $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);
        $password_confirm = filter_input(INPUT_POST, 'password_confirm', FILTER_SANITIZE_STRING);
        $confirmar = filter_input(INPUT_POST, 'confirmar', FILTER_SANITIZE_STRING);
        
        if (!$correo || !$password || !$password_confirm) {
            $this->redireccionarConError("usuarios/crear_usuario_estudiante/{$idEstudiante}", 'Todos los campos son obligatorios');
            return;
        }
        
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $this->redireccionarConError("usuarios/crear_usuario_estudiante/{$idEstudiante}", 'El formato del correo electrónico no es válido');
            return;
        }
        
        if ($password !== $password_confirm) {
            $this->redireccionarConError("usuarios/crear_usuario_estudiante/{$idEstudiante}", 'Las contraseñas no coinciden');
            return;
        }
        
        if (strlen($password) < 8) {
            $this->redireccionarConError("usuarios/crear_usuario_estudiante/{$idEstudiante}", 'La contraseña debe tener al menos 8 caracteres');
            return;
        }
        
        if (!$confirmar) {
            $this->redireccionarConError("usuarios/crear_usuario_estudiante/{$idEstudiante}", 'Debe confirmar que la información es correcta');
            return;
        }
        
        if ($this->usuarioModel->correoExiste($correo)) {
            $this->redireccionarConError("usuarios/crear_usuario_estudiante/{$idEstudiante}", 'El correo electrónico ya está registrado');
            return;
        }
        
        $resultado = $this->usuarioModel->crearUsuarioEstudiante($idEstudiante, $correo, $password);
        
        if ($resultado) {
            $this->redireccionarConExito("estudiantes/detalle/{$idEstudiante}", 'Cuenta de acceso creada correctamente para el estudiante');
        } else {
            $this->redireccionarConError(
                "usuarios/crear_usuario_estudiante/{$idEstudiante}", 
                'Error al crear el usuario. Es posible que ya exista un usuario para este estudiante.'
            );
        }
    }
    
    /**
     * Procesa la creación de usuario para padre
     */
    private function procesarCreacionUsuarioPadre(int $idPadre): void
    {
        $correo = filter_input(INPUT_POST, 'correo', FILTER_SANITIZE_EMAIL);
        $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);
        
        if (!$correo || !$password) {
            $this->redireccionarConError("usuarios/crear_padre/{$idPadre}", 'Todos los campos son obligatorios');
        }
        
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $this->redireccionarConError("usuarios/crear_padre/{$idPadre}", 'El formato del correo electrónico no es válido');
        }
        
        if ($this->usuarioModel->correoExiste($correo)) {
            $this->redireccionarConError("usuarios/crear_padre/{$idPadre}", 'El correo electrónico ya está registrado');
        }
        
        $resultado = $this->usuarioModel->crearUsuarioPadre($idPadre, $correo, $password);
        
        if ($resultado) {
            $this->redireccionarConExito("padres/detalle/{$idPadre}", 'Usuario creado correctamente para el padre/tutor');
        } else {
            $this->redireccionarConError(
                "usuarios/crear_padre/{$idPadre}", 
                'Error al crear el usuario. Es posible que ya exista un usuario para este padre/tutor.'
            );
        }
    }
    
    /**
     * Verifica que el método de solicitud sea POST
     */
    private function verificarMetodoPOST(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /usuarios');
            exit;
        }
    }
    
    /**
     * Verifica si hay errores en la validación
     */
    private function tieneErroresValidacion(array $datos): bool
    {
        return isset($datos['error']);
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
    
    public function toggleEstado(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        // Solo POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            exit;
        }

        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        $estado = filter_input(INPUT_POST, 'estado', FILTER_SANITIZE_STRING);

        if (!$id || !in_array($estado, ['activo', 'inactivo'])) {
            echo json_encode(['success' => false, 'message' => 'Parámetros inválidos']);
            exit;
        }

        $usuarioSesion = $this->sesion->get('usuario') ?? null;
        if (!$usuarioSesion) {
            echo json_encode(['success' => false, 'message' => 'Sesión inválida. Inicie sesión.']);
            exit;
        }

        // No permitir cambiar su propio estado
        if ($usuarioSesion['id_usuario'] == $id) {
            echo json_encode(['success' => false, 'message' => 'No puede cambiar el estado de su propio usuario']);
            exit;
        }

        // Obtener usuario objetivo
        $usuarioObj = $this->usuarioModel->buscarPorId($id);
        if (!$usuarioObj) {
            echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
            exit;
        }

        // VERIFICACIÓN DE PERMISOS - ACTUALIZAR ESTA PARTE
        $rolUsuarioSesion = strtolower($usuarioSesion['rol']);
        $rolUsuarioObj = strtolower($usuarioObj['rol']);

        // Solo superadmin puede modificar a otros superadmins
        if ($rolUsuarioObj === 'superadmin' && $rolUsuarioSesion !== 'superadmin') {
            echo json_encode(['success' => false, 'message' => 'No tiene permiso para modificar usuarios Super Administrador']);
            exit;
        }

        // Administradores pueden modificar todos excepto superadmins
        if ($rolUsuarioSesion === 'administrador' && $rolUsuarioObj === 'superadmin') {
            echo json_encode(['success' => false, 'message' => 'No tiene permiso para modificar usuarios Super Administrador']);
            exit;
        }

        // Si pasa todas las validaciones, proceder con el cambio
        $resultado = $this->usuarioModel->cambiarEstado($id, $estado);

        if ($resultado) {
            echo json_encode(['success' => true, 'message' => 'Estado actualizado correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar el estado']);
        }
        exit;
    }
}