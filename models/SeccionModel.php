<?php
namespace Models;

require_once __DIR__ . '/../core/Modelo.php';

use Core\Modelo;
use \PDO;

class SeccionModel extends Modelo {
    
    public function __construct() {
        parent::__construct('secciones', 'id_seccion');
        $this->allowedFields = [
            'nombre',
            'descripcion'
        ];
    }
    
    /**
     * Obtiene todas las secciones
     * 
     * @return array Lista de secciones
     */
    public function obtenerTodas() {
        $sql = "SELECT s.*, 
                COUNT(DISTINCT sal.id_salon) as total_salones,
                COUNT(DISTINCT e.id_estudiante) as total_estudiantes
                FROM secciones s
                LEFT JOIN salones sal ON s.id_seccion = sal.id_seccion
                LEFT JOIN estudiantes e ON sal.id_salon = e.id_salon
                GROUP BY s.id_seccion
                ORDER BY s.nombre ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Obtiene las secciones por nivel educativo
     * 
     * @param string $nivel Nivel educativo (Inicial, Primaria, Secundaria)
     * @return array Lista de secciones del nivel especificado
     */
    public function obtenerSeccionesPorNivel($nivel) {
        $sql = "SELECT DISTINCT s.* 
                FROM secciones s
                INNER JOIN salones sal ON s.id_seccion = sal.id_seccion
                INNER JOIN grados g ON sal.id_grado = g.id_grado
                WHERE g.nivel = :nivel
                ORDER BY s.nombre ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':nivel', $nivel);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Obtiene una sección por su ID
     * 
     * @param int $idSeccion ID de la sección
     * @return array|false Datos de la sección o false si no existe
     */
    public function obtenerPorId($idSeccion) {
        $sql = "SELECT s.*,
                COUNT(DISTINCT sal.id_salon) as total_salones,
                COUNT(DISTINCT e.id_estudiante) as total_estudiantes,
                GROUP_CONCAT(DISTINCT g.nivel) as niveles
                FROM secciones s
                LEFT JOIN salones sal ON s.id_seccion = sal.id_seccion
                LEFT JOIN estudiantes e ON sal.id_salon = e.id_salon
                LEFT JOIN grados g ON sal.id_grado = g.id_grado
                WHERE s.id_seccion = :id_seccion
                GROUP BY s.id_seccion";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id_seccion', $idSeccion, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }
    
    /**
     * Crea una nueva sección
     * 
     * @param array $datos Datos de la sección
     * @return int|false ID de la sección creada o false si falla
     */
    public function crear($datos) {
        try {
            $this->db->beginTransaction();
            
            $sql = "INSERT INTO secciones (nombre, descripcion) 
                    VALUES (:nombre, :descripcion)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':nombre' => $datos['nombre'],
                ':descripcion' => $datos['descripcion']
            ]);
            
            $idSeccion = $this->db->lastInsertId();
            $this->db->commit();
            return $idSeccion;
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
    
    /**
     * Actualiza una sección existente
     * 
     * @param int $idSeccion ID de la sección
     * @param array $datos Nuevos datos de la sección
     * @return bool True si se actualizó correctamente
     */
    public function actualizar($idSeccion, $datos) {
        try {
            $this->db->beginTransaction();
            
            $sql = "UPDATE secciones 
                    SET nombre = :nombre, descripcion = :descripcion
                    WHERE id_seccion = :id_seccion";
            
            $stmt = $this->db->prepare($sql);
            $exito = $stmt->execute([
                ':nombre' => $datos['nombre'],
                ':descripcion' => $datos['descripcion'],
                ':id_seccion' => $idSeccion
            ]);
            
            $this->db->commit();
            return $exito;
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
    
    /**
     * Elimina una sección si no tiene salones asociados
     * 
     * @param int $idSeccion ID de la sección
     * @return bool True si se eliminó correctamente
     */
    public function eliminar($idSeccion) {
        try {
            $this->db->beginTransaction();
            
            // Verificar si tiene salones asociados
            $sql = "SELECT COUNT(*) FROM salones WHERE id_seccion = :id_seccion";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id_seccion' => $idSeccion]);
            $count = $stmt->fetchColumn();
            
            if ($count > 0) {
                throw new \Exception('No se puede eliminar la sección porque tiene salones asociados');
            }
            
            // Eliminar la sección
            $sql = "DELETE FROM secciones WHERE id_seccion = :id_seccion";
            $stmt = $this->db->prepare($sql);
            $exito = $stmt->execute([':id_seccion' => $idSeccion]);
            
            $this->db->commit();
            return $exito;
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    /**
     * Verifica si existe una sección con el mismo nombre
     * 
     * @param string $nombre Nombre de la sección
     * @param int|null $idExcluir ID de la sección a excluir de la verificación
     * @return bool True si existe una sección con el mismo nombre
     */
    public function existeNombre($nombre, $idExcluir = null) {
        $sql = "SELECT COUNT(*) FROM secciones WHERE nombre = :nombre";
        $params = [':nombre' => $nombre];
        
        if ($idExcluir) {
            $sql .= " AND id_seccion != :id_excluir";
            $params[':id_excluir'] = $idExcluir;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }
    
}