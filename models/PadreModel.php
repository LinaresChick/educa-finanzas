<?php
/**
 * Modelo para la gestión de padres o tutores
 */
namespace Models;

require_once __DIR__ . '/../core/Modelo.php';
require_once __DIR__ . '/../models/UsuarioModel.php';

use Core\Modelo;
use \Exception;
use \PDO;

class PadreModel extends Modelo {
    
    /**
     * Constructor de la clase
     */
    public function __construct() {
        parent::__construct('padres', 'id_padre');
        $this->allowedFields = [
            'id_usuario',
            'nombres',
            'apellidos',
            'dni',
            'telefono',
            'correo',
            'direccion',
            'relacion',
            'estado'
        ];
    }
    
    /**
     * Cuenta el total de padres activos
     *
     * @return int Total de padres
     */
    public function contarPadres() {
        $sql = "SELECT COUNT(*) as total FROM padres WHERE estado = 'activo'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return intval($resultado['total']);
    }
    
    /**
     * Obtiene un padre por su ID de usuario
     *
     * @param int $idUsuario ID del usuario asociado
     * @return array|false Datos del padre o false si no existe
     */
    public function obtenerPadrePorUsuarioId($idUsuario) {
        $sql = "SELECT * FROM padres WHERE id_usuario = :id_usuario AND estado = 'activo'";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_usuario', $idUsuario, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtiene los estudiantes asociados a un padre
     *
     * @param int $idPadre ID del padre
     * @return array Lista de estudiantes
     */
    public function obtenerEstudiantesPorPadreId($idPadre) {
        $sql = "SELECT e.*, pe.relacion, s.nombre as salon, s.grado, s.nivel
                FROM estudiantes e
                JOIN padre_estudiante pe ON e.id_estudiante = pe.id_estudiante
                LEFT JOIN salones s ON e.id_salon = s.id_salon
                WHERE pe.id_padre = :id_padre
                AND e.estado = 'activo'
                ORDER BY e.apellidos, e.nombres";
                
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_padre', $idPadre, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtiene todos los padres con información adicional
     * 
     * @param array $condiciones Las condiciones de filtrado
     * @return array Lista de padres con información de usuario
     */
    public function obtenerPadresConInfo($condiciones = []) {
        $sql = "SELECT p.*, 
                CONCAT(p.nombres, ' ', p.apellidos) as nombre_completo,
                u.correo as usuario_correo
                FROM padres p
                LEFT JOIN usuarios u ON p.id_usuario = u.id_usuario";
        
        // Agregar condiciones WHERE
        if (!empty($condiciones)) {
            $sql .= " WHERE ";
            $where = [];
            foreach ($condiciones as $campo => $valor) {
                $where[] = "p.{$campo} = :{$campo}";
            }
            $sql .= implode(' AND ', $where);
        }
        
        $sql .= " ORDER BY p.apellidos, p.nombres";
        
        $stmt = $this->db->prepare($sql);
        
        // Vincular los valores de las condiciones
        if (!empty($condiciones)) {
            foreach ($condiciones as $campo => $valor) {
                $stmt->bindValue(":{$campo}", $valor);
            }
        }
        
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Obtiene un padre por su ID con información adicional
     * 
     * @param int $id El ID del padre
     * @return array Datos del padre con información adicional
     */
    public function obtenerPadreDetalle($id) {
        $sql = "SELECT p.*, 
                CONCAT(p.nombres, ' ', p.apellidos) as nombre_completo,
                u.correo, u.id_usuario
                FROM padres p
                LEFT JOIN usuarios u ON p.id_usuario = u.id_usuario
                WHERE p.id_padre = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }
    
    /**
     * Busca padres según un término de búsqueda
     * 
     * @param string $termino El término de búsqueda
     * @return array Lista de padres que coinciden con la búsqueda
     */
    public function buscarPadres($termino) {
        $termino = "%{$termino}%";
        $sql = "SELECT p.*, 
                CONCAT(p.nombres, ' ', p.apellidos) as nombre_completo,
                u.correo
                FROM padres p
                LEFT JOIN usuarios u ON p.id_usuario = u.id_usuario
                WHERE p.nombres LIKE :termino 
                OR p.apellidos LIKE :termino 
                OR p.dni LIKE :termino 
                OR p.correo LIKE :termino
                OR CONCAT(p.nombres, ' ', p.apellidos) LIKE :termino
                ORDER BY p.apellidos, p.nombres";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':termino', $termino);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Crea un nuevo padre y opcionalmente su cuenta de usuario
     * 
     * @param array $datosPadre Los datos del padre
     * @param array|null $datosUsuario Los datos del usuario (opcional)
     * @return int|false El ID del padre creado o false si falla
     */
    /**
 * Crea un padre con posible usuario asociado
 */
public function crearPadreConUsuario($datosPadre, $datosUsuario = null) {
    try {

        $this->db->beginTransaction();
        $idUsuario = null;

        // Si viene cuenta de usuario
        if ($datosUsuario !== null) {
            $sqlUsuario = "INSERT INTO usuarios (nombre, correo, password, rol, estado)
                           VALUES (:nombre, :correo, :password, :rol, :estado)";
            $stmt = $this->db->prepare($sqlUsuario);
            $stmt->execute($datosUsuario);
            $idUsuario = $this->db->lastInsertId();
        }

        // Agregar id_usuario si existe
        if ($idUsuario) {
            $datosPadre['id_usuario'] = $idUsuario;
        }

        // Insertar padre
        $this->insertar($datosPadre);
        $idPadre = $this->db->lastInsertId();

        $this->db->commit();
        return $idPadre;

    } catch (Exception $e) {
        $this->db->rollBack();
        return false;
    }
}
    
    /**
     * Actualiza un padre y opcionalmente su cuenta de usuario
     * 
     * @param int $idPadre El ID del padre
     * @param array $datosPadre Los datos del padre
     * @param array|null $datosUsuario Los datos del usuario (opcional)
     * @return bool True si la actualización fue exitosa
     */
    /**
 * Actualiza un padre y su usuario asociado (si existe)
 */
public function actualizarPadreConUsuario($idPadre, $datosPadre, $datosUsuario = null) {
    try {

        $this->db->beginTransaction();

        // Actualizar usuario si corresponde
        if ($datosUsuario !== null) {

            $sqlUsuario = "UPDATE usuarios
                           SET nombre = :nombre, correo = :correo"
                           . (isset($datosUsuario['password']) ? ", password = :password" : "") .
                           " WHERE id_usuario = (SELECT id_usuario FROM padres WHERE id_padre = :id_padre)";

            $stmtUser = $this->db->prepare($sqlUsuario);

            foreach ($datosUsuario as $campo => $valor) {
                $stmtUser->bindValue(":$campo", $valor);
            }
            $stmtUser->bindValue(":id_padre", $idPadre);

            $stmtUser->execute();
        }

        // Actualizar padre
        $this->actualizar($idPadre, $datosPadre);

        $this->db->commit();
        return true;

    } catch (Exception $e) {
        $this->db->rollBack();
        return false;
    }
}
    
    /**
     * Obtiene los estudiantes asociados a un padre
     * 
     * @param int $idPadre El ID del padre
     * @return array Lista de estudiantes asociados
     */
    public function obtenerEstudiantesDePadre($idPadre) {
        $sql = "SELECT e.*, ep.parentesco,
                CONCAT(e.nombres, ' ', e.apellidos) as nombre_completo,
                CONCAT(g.nombre, ' ', g.nivel) as grado,
                s.nombre as seccion,
                sal.anio as anio_escolar
                FROM estudiantes e
                INNER JOIN estudiante_padre ep ON e.id_estudiante = ep.id_estudiante
                LEFT JOIN salones sal ON e.id_salon = sal.id_salon
                LEFT JOIN grados g ON sal.id_grado = g.id_grado
                LEFT JOIN secciones s ON sal.id_seccion = s.id_seccion
                WHERE ep.id_padre = :id_padre
                ORDER BY e.apellidos, e.nombres";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id_padre', $idPadre);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Asocia un padre a un estudiante
     * 
     * @param int $idPadre El ID del padre
     * @param int $idEstudiante El ID del estudiante
     * @param string $parentesco El parentesco
     * @return bool True si la asociación fue exitosa
     */
    public function asociarEstudiante($idPadre, $idEstudiante, $parentesco = 'Padre/Madre') {
        $sql = "INSERT INTO estudiante_padre (id_padre, id_estudiante, parentesco) 
                VALUES (:id_padre, :id_estudiante, :parentesco)
                ON DUPLICATE KEY UPDATE parentesco = :parentesco";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id_padre', $idPadre);
        $stmt->bindValue(':id_estudiante', $idEstudiante);
        $stmt->bindValue(':parentesco', $parentesco);
        
        return $stmt->execute();
    }
    
    /**
     * Elimina la asociación entre un padre y un estudiante
     * 
     * @param int $idPadre El ID del padre
     * @param int $idEstudiante El ID del estudiante
     * @return bool True si la desasociación fue exitosa
     */
    public function desasociarEstudiante($idPadre, $idEstudiante) {
        $sql = "DELETE FROM estudiante_padre 
                WHERE id_padre = :id_padre 
                AND id_estudiante = :id_estudiante";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id_padre', $idPadre);
        $stmt->bindValue(':id_estudiante', $idEstudiante);
        
        return $stmt->execute();
    }
    
    /**
     * Obtiene todos los padres en formato JSON para ser usado en select2 o similar
     * 
     * @return array Lista de padres en formato adecuado para componentes de selección
     */
    public function obtenerPadresParaSelect() {
        $sql = "SELECT id_padre, 
                CONCAT(nombres, ' ', apellidos, ' (', relacion, ')') as texto,
                dni, relacion
                FROM padres 
                WHERE estado = 'activo'
                ORDER BY apellidos, nombres";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Obtiene estudiantes no asociados a un padre específico
     * 
     * @param int $idPadre El ID del padre
     * @return array Lista de estudiantes no asociados
     */
    public function obtenerEstudiantesNoAsociados($idPadre) {
        $sql = "SELECT e.id_estudiante, 
                CONCAT(e.nombres, ' ', e.apellidos) as nombre_completo,
                e.dni,
                CONCAT(g.nombre, ' ', g.nivel, ' - Sección ', s.nombre) as grado_seccion
                FROM estudiantes e
                LEFT JOIN salones sal ON e.id_salon = sal.id_salon
                LEFT JOIN grados g ON sal.id_grado = g.id_grado
                LEFT JOIN secciones s ON sal.id_seccion = s.id_seccion
                WHERE e.id_estudiante NOT IN (
                    SELECT id_estudiante FROM estudiante_padre WHERE id_padre = :id_padre
                )
                AND e.estado = 'activo'
                ORDER BY e.apellidos, e.nombres";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id_padre', $idPadre);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
