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
            'fecha_creacion',
            'fecha_actualizacion'
        ];
    }

    /**
     * Obtiene la lista de roles disponibles
     */
    public function obtenerRoles(): array {
        return [
            'Superadmin' => 'Super Administrador',
            'Administrador' => 'Administrador',
            'Secretario' => 'Secretario',
            'Contador' => 'Contador',
            'Colaborador' => 'Colaborador',
            'Estudiante' => 'Estudiante',
            'Padre' => 'Padre/Tutor',
            'Docente' => 'Docente'
        ];
    }

    /**
     * Obtiene un usuario por su ID
     */
    public function obtenerPorId(int $id): ?array {
        $sql = "SELECT 
            id_usuario,
            nombre,
            correo,
            password,
            rol,
            estado,
            fecha_creacion,
            fecha_actualizacion
        FROM {$this->tabla} 
        WHERE {$this->primaryKey} = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$usuario) {
            return null;
        }

        // Mapear los nombres de las columnas a los esperados por la vista
        $usuario['creado'] = $usuario['fecha_creacion'];
        $usuario['actualizado'] = $usuario['fecha_actualizacion'];
        $usuario['ultimo_acceso'] = null; // Este campo no existe en la DB

        // Verificar si el usuario está asociado a un estudiante
        $stmt = $this->db->prepare("SELECT id_estudiante FROM estudiantes WHERE id_usuario = ?");
        $stmt->execute([$id]);
        $estudiante = $stmt->fetch(PDO::FETCH_ASSOC);
        $usuario['id_estudiante'] = $estudiante ? $estudiante['id_estudiante'] : null;

        // Verificar si el usuario está asociado a un padre
        $stmt = $this->db->prepare("SELECT id_padre FROM padres WHERE id_usuario = ?");
        $stmt->execute([$id]);
        $padre = $stmt->fetch(PDO::FETCH_ASSOC);
        $usuario['id_padre'] = $padre ? $padre['id_padre'] : null;
        
        unset($usuario['fecha_creacion']);
        unset($usuario['fecha_actualizacion']);
        
        return $usuario;
    }

    /**
     * Busca un usuario por su ID (alias de obtenerPorId para consistencia)
     */
    public function buscarPorId($id)
{
    try {
        $sql = "SELECT * FROM usuarios WHERE id_usuario = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    } catch (Exception $e) {
        error_log("Error al buscar usuario por ID: " . $e->getMessage());
        return null;
    }
}


    /**
     * Verifica si un correo ya existe en la base de datos
     */
    public function correoExiste(string $correo, ?int $exceptUserId = null): bool {
        $sql = "SELECT COUNT(*) FROM {$this->tabla} WHERE correo = ?";
        $params = [$correo];

        if ($exceptUserId !== null) {
            $sql .= " AND {$this->primaryKey} != ?";
            $params[] = $exceptUserId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return (int)$stmt->fetchColumn() > 0;
    }

    /**
     * Elimina un usuario (lo marca como inactivo)
     */
    public function eliminar($id) {
        try {
            $sql = "UPDATE {$this->tabla} SET estado = 'inactivo' WHERE {$this->primaryKey} = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$id]);
        } catch (\PDOException $e) {
            error_log("Error al eliminar usuario: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Busca un usuario por su correo electrónico
     */
    public function buscarPorCorreo(string $correo) {
        $sql = "SELECT * FROM usuarios WHERE correo = :correo LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':correo', $correo, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Crea un nuevo usuario
     */
    public function crearUsuario(array $datos): int {
        try {
            $this->db->beginTransaction();

            // Validar datos mínimos requeridos
            if (empty($datos['nombre']) || empty($datos['correo']) || empty($datos['password']) || empty($datos['rol'])) {
                throw new Exception('Faltan datos requeridos para crear el usuario');
            }

            // Preparar datos para insertar
            $datosInsertar = [
                'nombre' => $datos['nombre'],
                'correo' => $datos['correo'],
                'password' => password_hash($datos['password'], PASSWORD_DEFAULT),
                'rol' => $datos['rol'],
                'estado' => $datos['estado'] ?? 'activo',
                'fecha_creacion' => date('Y-m-d H:i:s'),
                'fecha_actualizacion' => date('Y-m-d H:i:s')
            ];

            // Si es estudiante o padre, agregar el ID correspondiente
            if ($datos['rol'] === 'estudiante' && !empty($datos['id_estudiante'])) {
                $datosInsertar['id_estudiante'] = $datos['id_estudiante'];
            }
            if ($datos['rol'] === 'padre' && !empty($datos['id_padre'])) {
                $datosInsertar['id_padre'] = $datos['id_padre'];
            }

            // Insertar el usuario
            $columnas = implode(', ', array_keys($datosInsertar));
            $valores = ':' . implode(', :', array_keys($datosInsertar));
            
            $sql = "INSERT INTO usuarios ($columnas) VALUES ($valores)";
            $stmt = $this->db->prepare($sql);
            
            foreach ($datosInsertar as $campo => $valor) {
                $stmt->bindValue(":$campo", $valor);
            }
            
            $stmt->execute();
            $id = $this->db->lastInsertId();
            
            $this->db->commit();
            return $id;

        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
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
        $sql = "SELECT id_usuario, nombre, correo, rol, estado, fecha_creacion, fecha_actualizacion
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
    $sql = "SELECT e.id_estudiante, e.nombres, e.apellidos, e.dni, 
                   s.grado, s.seccion, e.id_usuario
            FROM estudiantes e
            LEFT JOIN salones s ON e.id_salon = s.id_salon
            WHERE e.id_estudiante = ?";
    
    $stmt = $this->db->prepare($sql);
    $stmt->execute([$idEstudiante]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
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
     * Actualiza un usuario existente
     * 
     * @param int $idUsuario ID del usuario a actualizar
     * @param array $datos Datos a actualizar
     * @return bool True si la actualización fue exitosa
     */
    public function actualizarUsuario($idUsuario, $datos) {
        // Verificar si el correo ya está registrado por otro usuario
        if (isset($datos['correo']) && $this->correoExiste($datos['correo'], $idUsuario)) {
            return false;
        }
        
        // Si se incluye password, encriptarla
        if (isset($datos['password']) && !empty($datos['password'])) {
            $datos['password'] = password_hash($datos['password'], PASSWORD_DEFAULT);
        } else {
            // Si no se incluye password, no la actualizamos
            unset($datos['password']);
        }
        
        $datos['fecha_actualizacion'] = date('Y-m-d H:i:s');
        
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
            'fecha_actualizacion' => date('Y-m-d H:i:s')
        ];
        
        return $this->actualizar($idUsuario, $datos);
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
    public function cambiarEstado(int $idUsuario, string $estado): bool
    {
        // Validación básica
        if (!in_array($estado, ['activo', 'inactivo'])) {
            return false;
        }

        try {
            $sql = "UPDATE usuarios SET estado = :estado, fecha_actualizacion = NOW() WHERE id_usuario = :id_usuario";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':estado', $estado, PDO::PARAM_STR);
            $stmt->bindValue(':id_usuario', $idUsuario, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (\PDOException $e) {
            error_log("UsuarioModel::cambiarEstado error: " . $e->getMessage());
            return false;
        }
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
    try {
        $this->db->beginTransaction();

        // Verificar si ya existe un usuario para este estudiante
        $sql = "SELECT COUNT(*) as count FROM usuarios WHERE id_estudiante = :id_estudiante";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id_estudiante', $idEstudiante);
        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($resultado['count'] > 0) {
            throw new Exception('Ya existe un usuario para este estudiante');
        }

        // Obtener datos del estudiante
        $estudiante = $this->obtenerDatosEstudiante($idEstudiante);
        if (!$estudiante) {
            throw new Exception('Estudiante no encontrado');
        }

        // Crear usuario
        $datos = [
            'nombre' => $estudiante['nombres'] . ' ' . $estudiante['apellidos'],
            'correo' => $correo,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'rol' => 'Estudiante',
            'estado' => 'activo',
            'id_estudiante' => $idEstudiante,
            'id_padre' => null
        ];

        $idUsuario = $this->crearUsuario($datos);
        
        $this->db->commit();
        return $idUsuario;

    } catch (Exception $e) {
        $this->db->rollBack();
        error_log("Error crearUsuarioEstudiante: " . $e->getMessage());
        return false;
    }
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
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'rol' => 'padre',
            'estado' => 'activo',
            'id_estudiante' => null,
            'id_padre' => $idPadre
        ];
        
        return $this->crearUsuario($datos);
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