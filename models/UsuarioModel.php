<?php
/**
 * Modelo para la gestión de usuarios
 */
namespace Models;

require_once __DIR__ . '/../core/Modelo.php';

use Core\Modelo;
use \Exception;
use \PDO;

class UsuarioModel extends Modelo {
    
    /**
     * Constructor de la clase
     */
    public function __construct() {
        parent::__construct('usuarios', 'id_usuario');
        $this->allowedFields = [
            'nombre',
            'correo',
            'password',
            'rol',
            'estado',
            'ultimo_acceso',
            'creado',
            'actualizado',
            'id_estudiante',
            'id_padre'
        ];
    }

    /**
     * Cuenta el total de usuarios activos
     *
     * @return int Total de usuarios
     */
    public function contarUsuarios() {
        $sql = "SELECT COUNT(*) as total FROM usuarios WHERE estado = 'activo'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return intval($resultado['total']);
    }

    /**
     * Obtiene los últimos usuarios registrados
     *
     * @param int $limite Número de usuarios a obtener
     * @return array Lista de últimos usuarios
     */
    public function obtenerUltimosUsuarios($limite = 5) {
        $sql = "SELECT id_usuario, nombre, correo, rol, estado, ultimo_acceso, creado
                FROM usuarios
                WHERE estado = 'activo'
                ORDER BY id_usuario DESC
                LIMIT :limite";
                
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtiene un usuario por su correo electrónico
     * 
     * @param string $correo El correo electrónico del usuario
     * @return array|false Los datos del usuario o false si no existe
     */
    public function obtenerPorCorreo($correo) {
        $sql = "SELECT * FROM usuarios WHERE correo = :correo AND estado = 'activo' LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':correo', $correo, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Verifica las credenciales de un usuario para el login
     * 
     * @param string $correo El correo del usuario
     * @param string $password La contraseña del usuario
     * @return array|false Datos del usuario si las credenciales son válidas, false en caso contrario
     */
    public function verificarCredenciales($correo, $password) {
        $sql = "SELECT * FROM usuarios WHERE correo = :correo AND estado = 'activo'";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':correo', $correo);
        $stmt->execute();
        
        $usuario = $stmt->fetch();
        
        // Verificar si existe el usuario y si la contraseña coincide
        if ($usuario && password_verify($password, $usuario['password'])) {
            // Actualizar último acceso
            $this->actualizarUltimoAcceso($usuario['id_usuario']);
            
            // Cargar información adicional según el rol
            if ($usuario['rol'] === 'estudiante' && !empty($usuario['id_estudiante'])) {
                $usuario['datos_estudiante'] = $this->obtenerDatosEstudiante($usuario['id_estudiante']);
            } elseif ($usuario['rol'] === 'padre' && !empty($usuario['id_padre'])) {
                $usuario['datos_padre'] = $this->obtenerDatosPadre($usuario['id_padre']);
                $usuario['estudiantes'] = $this->obtenerEstudiantesPorPadre($usuario['id_padre']);
            }
            
            return $usuario;
        }
        
        return false;
    }
    
    /**
     * Actualiza la fecha de último acceso de un usuario
     * 
     * @param int $idUsuario El ID del usuario
     * @return bool True si se actualizó correctamente
     */
    public function actualizarUltimoAcceso($idUsuario) {
        $sql = "UPDATE usuarios SET ultimo_acceso = NOW() WHERE id_usuario = :id_usuario";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id_usuario', $idUsuario);
        return $stmt->execute();
    }
    
    /**
     * Obtiene los datos de un estudiante por su ID
     * 
     * @param int $idEstudiante El ID del estudiante
     * @return array|false Datos del estudiante o false si no existe
     */
    public function obtenerDatosEstudiante($idEstudiante) {
        $sql = "SELECT * FROM estudiantes WHERE id_estudiante = :id_estudiante";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id_estudiante', $idEstudiante);
        $stmt->execute();
        return $stmt->fetch();
    }
    
    /**
     * Obtiene los datos de un padre por su ID
     * 
     * @param int $idPadre El ID del padre
     * @return array|false Datos del padre o false si no existe
     */
    public function obtenerDatosPadre($idPadre) {
        $sql = "SELECT * FROM padres WHERE id_padre = :id_padre";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id_padre', $idPadre);
        $stmt->execute();
        return $stmt->fetch();
    }
    
    /**
     * Obtiene la lista de estudiantes asociados a un padre
     * 
     * @param int $idPadre El ID del padre
     * @return array Lista de estudiantes asociados
     */
    public function obtenerEstudiantesPorPadre($idPadre) {
        $sql = "SELECT e.* FROM estudiantes e
                INNER JOIN padres_estudiantes pe ON e.id_estudiante = pe.id_estudiante
                WHERE pe.id_padre = :id_padre
                ORDER BY e.nombres, e.apellidos";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id_padre', $idPadre);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Crea un nuevo usuario
     * 
     * @param array $datos Datos del usuario a crear
     * @return int|false ID del usuario creado o false si falla
     */
    public function crearUsuario($datos) {
        // Verificar si el correo ya está registrado
        if ($this->correoExiste($datos['correo'])) {
            return false;
        }
        
        // Encriptar la contraseña
        $datos['password'] = password_hash($datos['password'], PASSWORD_DEFAULT);
        $datos['creado'] = date('Y-m-d H:i:s');
        $datos['actualizado'] = date('Y-m-d H:i:s');
        
        return $this->insertar($datos);
    }
    
    /**
     * Actualiza un usuario existente
     * 
     * @param int $idUsuario ID del usuario a actualizar
     * @param array $datos Datos a actualizar
     * @return bool True si la actualización fue exitosa
     */
    public function actualizarUsuario($idUsuario, $datos) {
        // Verificar si el correo ya está registrado por otro usuario
        if (isset($datos['correo']) && $this->correoExisteOtroUsuario($datos['correo'], $idUsuario)) {
            return false;
        }
        
        // Si se incluye password, encriptarla
        if (isset($datos['password']) && !empty($datos['password'])) {
            $datos['password'] = password_hash($datos['password'], PASSWORD_DEFAULT);
        } else {
            // Si no se incluye password, no la actualizamos
            unset($datos['password']);
        }
        
        $datos['actualizado'] = date('Y-m-d H:i:s');
        
        return $this->actualizar($idUsuario, $datos);
    }
    
    /**
     * Cambia la contraseña de un usuario
     * 
     * @param int $idUsuario ID del usuario
     * @param string $passwordActual Contraseña actual
     * @param string $passwordNueva Nueva contraseña
     * @return bool True si el cambio fue exitoso
     */
    public function cambiarPassword($idUsuario, $passwordActual, $passwordNueva) {
        // Obtener datos del usuario
        $usuario = $this->buscarPorId($idUsuario);
        if (!$usuario) {
            return false;
        }
        
        // Verificar la contraseña actual
        if (!password_verify($passwordActual, $usuario['password'])) {
            return false;
        }
        
        // Actualizar la contraseña
        $datos = [
            'password' => password_hash($passwordNueva, PASSWORD_DEFAULT),
            'actualizado' => date('Y-m-d H:i:s')
        ];
        
        return $this->actualizar($idUsuario, $datos);
    }
    
    /**
     * Verifica si un correo ya está registrado
     * 
     * @param string $correo El correo a verificar
     * @return bool True si el correo ya existe
     */
    public function correoExiste($correo) {
        $sql = "SELECT COUNT(*) as count FROM usuarios WHERE correo = :correo";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':correo', $correo);
        $stmt->execute();
        $resultado = $stmt->fetch();
        return $resultado['count'] > 0;
    }
    
    /**
     * Verifica si un correo ya está registrado por otro usuario
     * 
     * @param string $correo El correo a verificar
     * @param int $idUsuario El ID del usuario a excluir de la verificación
     * @return bool True si el correo ya existe para otro usuario
     */
    public function correoExisteOtroUsuario($correo, $idUsuario) {
        $sql = "SELECT COUNT(*) as count FROM usuarios WHERE correo = :correo AND id_usuario != :id_usuario";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':correo', $correo);
        $stmt->bindValue(':id_usuario', $idUsuario);
        $stmt->execute();
        $resultado = $stmt->fetch();
        return $resultado['count'] > 0;
    }
    
    /**
     * Cambia el estado de un usuario (activar/desactivar)
     * 
     * @param int $idUsuario El ID del usuario
     * @param string $estado El nuevo estado ('activo' o 'inactivo')
     * @return bool True si el cambio fue exitoso
     */
    public function cambiarEstado($idUsuario, $estado) {
        if ($estado !== 'activo' && $estado !== 'inactivo') {
            return false;
        }
        
        $sql = "UPDATE usuarios SET estado = :estado, actualizado = NOW() WHERE id_usuario = :id_usuario";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':estado', $estado);
        $stmt->bindValue(':id_usuario', $idUsuario);
        return $stmt->execute();
    }
    
    /**
     * Crea un usuario para un estudiante
     * 
     * @param int $idEstudiante El ID del estudiante
     * @param string $correo El correo del usuario
     * @param string $password La contraseña del usuario
     * @return int|false ID del usuario creado o false si falla
     */
    public function crearUsuarioEstudiante($idEstudiante, $correo, $password) {
        // Verificar si ya existe un usuario para este estudiante
        $sql = "SELECT COUNT(*) as count FROM usuarios WHERE id_estudiante = :id_estudiante";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id_estudiante', $idEstudiante);
        $stmt->execute();
        $resultado = $stmt->fetch();
        
        if ($resultado['count'] > 0) {
            return false;
        }
        
        // Obtener datos del estudiante
        $estudiante = $this->obtenerDatosEstudiante($idEstudiante);
        if (!$estudiante) {
            return false;
        }
        
        // Crear usuario
        $datos = [
            'nombre' => $estudiante['nombres'] . ' ' . $estudiante['apellidos'],
            'correo' => $correo,
            'password' => $password,
            'rol' => 'estudiante',
            'estado' => 'activo',
            'id_estudiante' => $idEstudiante,
            'id_padre' => null
        ];
        
        return $this->crearUsuario($datos);
    }
    
    /**
     * Crea un usuario para un padre
     * 
     * @param int $idPadre El ID del padre
     * @param string $correo El correo del usuario
     * @param string $password La contraseña del usuario
     * @return int|false ID del usuario creado o false si falla
     */
    public function crearUsuarioPadre($idPadre, $correo, $password) {
        // Verificar si ya existe un usuario para este padre
        $sql = "SELECT COUNT(*) as count FROM usuarios WHERE id_padre = :id_padre";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id_padre', $idPadre);
        $stmt->execute();
        $resultado = $stmt->fetch();
        
        if ($resultado['count'] > 0) {
            return false;
        }
        
        // Obtener datos del padre
        $padre = $this->obtenerDatosPadre($idPadre);
        if (!$padre) {
            return false;
        }
        
        // Crear usuario
        $datos = [
            'nombre' => $padre['nombres'] . ' ' . $padre['apellidos'],
            'correo' => $correo,
            'password' => $password,
            'rol' => 'padre',
            'estado' => 'activo',
            'id_estudiante' => null,
            'id_padre' => $idPadre
        ];
        
        return $this->crearUsuario($datos);
    }
    
    /**
     * Obtiene la lista de roles disponibles en el sistema
     * 
     * @return array Lista de roles con sus descripciones
     */
    public function obtenerRoles() {
        return [
            'superadmin' => 'Super Administrador',
            'admin' => 'Administrador',
            'tesoreria' => 'Tesorería',
            'colaborador' => 'Colaborador',
            'estudiante' => 'Estudiante',
            'padre' => 'Padre/Tutor'
        ];
    }
    
    /**
     * Verifica la relación entre un padre y un estudiante
     * 
     * @param int $idPadre El ID del padre
     * @param int $idEstudiante El ID del estudiante
     * @return bool True si existe la relación
     */
    public function verificarRelacionPadreEstudiante($idPadre, $idEstudiante) {
        $sql = "SELECT COUNT(*) as count FROM padres_estudiantes 
                WHERE id_padre = :id_padre AND id_estudiante = :id_estudiante";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id_padre', $idPadre);
        $stmt->bindValue(':id_estudiante', $idEstudiante);
        $stmt->execute();
        $resultado = $stmt->fetch();
        return $resultado['count'] > 0;
    }
}
