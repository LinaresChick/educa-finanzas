<?php
/**
 * Modelo para la gestión de estudiantes
 */
namespace Models;require_once __DIR__ . '/../core/Modelo.php';
require_once __DIR__ . '/../models/PadreModel.php';
require_once __DIR__ . '/../models/UsuarioModel.php';

use Core\Modelo;
use Models\UsuarioModelModel;

use \PDO;
use \Exception;

class EstudianteModel extends Modelo {
    
    /**
     * Constructor de la clase
     */
    public function __construct() {
        parent::__construct('estudiantes', 'id_estudiante');
        $this->allowedFields = [
            'id_usuario',
            'id_salon',
            'nombres',
            'apellidos',
            'dni',
            'mencion',
            'fecha_nacimiento',
            'direccion',
            'telefono',
            'estado',
            'monto',
            'fecha_vencimiento',
            'estado_pago'
        ];
    }

    /**
     * Cuenta el total de estudiantes activos
     *
     * @return int Total de estudiantes
     */
    public function contarEstudiantes() {
        $sql = "SELECT COUNT(*) as total FROM estudiantes WHERE estado = 'activo'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return intval($resultado['total']);
    }

    /**
     * Obtiene todos los estudiantes activos
     *
     * @return array Lista de estudiantes
     */
    public function obtenerEstudiantesActivos() {
        try {
            $sql = "SELECT 
                    e.*,
                    sal.id_salon,
                    s.nombre as salon_nombre,
                    (SELECT COALESCE(SUM(monto), 0) FROM pagos WHERE id_estudiante = e.id_estudiante) as total_pagado
                   FROM estudiantes e
                   LEFT JOIN salones sal ON e.id_salon = sal.id_salon
                   LEFT JOIN secciones s ON sal.id_seccion = s.id_seccion
                   WHERE e.estado = 'activo'
                   ORDER BY e.apellidos, e.nombres";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en EstudianteModel->obtenerTodos: " . $e->getMessage());
            throw new Exception("Error al obtener los estudiantes");
        }
    }

    /**
     * Obtiene los últimos estudiantes registrados
     *
     * @param int $limite Número de estudiantes a obtener
     * @return array Lista de estudiantes
     */
    public function obtenerUltimosEstudiantes($limite = 5) {
        $sql = "SELECT id_estudiante, nombres, apellidos, dni, mencion, estado, 
                       fecha_nacimiento, direccion, telefono
                FROM estudiantes
                WHERE estado = 'activo'
                ORDER BY id_estudiante DESC
                LIMIT :limite";
                
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene un estudiante por su ID de usuario
     *
     * @param int $idUsuario ID del usuario asociado
     * @return array|false Datos del estudiante o false si no existe
     */
    public function obtenerEstudiantePorUsuarioId($idUsuario) {
        $sql = "SELECT e.*, s.nombre as salon, s.grado, s.nivel
                FROM estudiantes e
                LEFT JOIN salones s ON e.id_salon = s.id_salon
                WHERE e.id_usuario = :id_usuario";
                
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_usuario', $idUsuario, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene los padres asociados a un estudiante
     *
     * @param int $idEstudiante ID del estudiante
     * @return array Lista de padres asociados
     */
    public function obtenerPadresPorEstudianteId($idEstudiante) {
        $sql = "SELECT p.*, pe.relacion
                FROM padres p
                JOIN padre_estudiante pe ON p.id_padre = pe.id_padre
                WHERE pe.id_estudiante = :id_estudiante
                AND p.estado = 'activo'";
                
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_estudiante', $idEstudiante, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtiene todos los estudiantes con información adicional
     * 
     * @param array $condiciones Las condiciones de filtrado
     * @return array Lista de estudiantes con información de salón y usuario
     */
    public function obtenerEstudiantesConInfo($condiciones = []) {
        $sql = "SELECT e.*, 
                CONCAT(e.nombres, ' ', e.apellidos) as nombre_completo,
                u.correo,
                CONCAT(g.nombre, ' ', g.nivel) as grado,
                s.nombre as seccion,
                sal.anio as anio_escolar
                FROM estudiantes e
                LEFT JOIN usuarios u ON e.id_usuario = u.id_usuario
                LEFT JOIN salones sal ON e.id_salon = sal.id_salon
                LEFT JOIN grados g ON sal.id_grado = g.id_grado
                LEFT JOIN secciones s ON sal.id_seccion = s.id_seccion";
        
        // Agregar condiciones WHERE
        if (!empty($condiciones)) {
            $sql .= " WHERE ";
            $where = [];
            foreach ($condiciones as $campo => $valor) {
                $where[] = "e.{$campo} = :{$campo}";
            }
            $sql .= implode(' AND ', $where);
        }
        
        $sql .= " ORDER BY e.apellidos, e.nombres";
        
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
     * Obtiene un estudiante por su ID con información adicional
     * 
     * @param int $id El ID del estudiante
     * @return array Datos del estudiante con información adicional
     */
    public function obtenerEstudianteDetalle($id) {
        $sql = "SELECT e.*, 
                CONCAT(e.nombres, ' ', e.apellidos) as nombre_completo,
                u.correo, u.id_usuario,
                g.nombre as grado_nombre, g.nivel as nivel_educativo,
                s.id_seccion as id_seccion, s.nombre as seccion_nombre,
                sal.anio as anio_escolar,
                sal.id_salon
                FROM estudiantes e
                LEFT JOIN usuarios u ON e.id_usuario = u.id_usuario
                LEFT JOIN salones sal ON e.id_salon = sal.id_salon
                LEFT JOIN grados g ON sal.id_grado = g.id_grado
                LEFT JOIN secciones s ON sal.id_seccion = s.id_seccion
                WHERE e.id_estudiante = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }
    
    /**
     * Busca estudiantes según un término de búsqueda
     * 
     * @param string $termino El término de búsqueda
     * @return array Lista de estudiantes que coinciden con la búsqueda
     */
    public function buscarEstudiantes($termino) {
        $termino = "%{$termino}%";
        $sql = "SELECT e.*, 
                CONCAT(e.nombres, ' ', e.apellidos) as nombre_completo,
                u.correo,
                CONCAT(g.nombre, ' ', g.nivel) as grado,
                s.nombre as seccion,
                sal.anio as anio_escolar
                FROM estudiantes e
                LEFT JOIN usuarios u ON e.id_usuario = u.id_usuario
                LEFT JOIN salones sal ON e.id_salon = sal.id_salon
                LEFT JOIN grados g ON sal.id_grado = g.id_grado
                LEFT JOIN secciones s ON sal.id_seccion = s.id_seccion
                WHERE e.nombres LIKE :termino 
                OR e.apellidos LIKE :termino 
                OR e.dni LIKE :termino 
                OR CONCAT(e.nombres, ' ', e.apellidos) LIKE :termino
                ORDER BY e.apellidos, e.nombres";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':termino', $termino);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Crea un nuevo estudiante y opcionalmente su cuenta de usuario
     * 
     * @param array $datosEstudiante Los datos del estudiante
     * @param array|null $datosUsuario Los datos del usuario (opcional)
     * @return int|false El ID del estudiante creado o false si falla
     */
    public function crearEstudianteConUsuario($datosEstudiante, $datosUsuario = null) {
        $this->db->beginTransaction();
        
        try {
            // Si se incluyen datos de usuario, crear el usuario primero
            if ($datosUsuario) {
                $usuarioModel = new UsuarioModel();
                $idUsuario = $usuarioModel->insertar($datosUsuario);
                $datosEstudiante['id_usuario'] = $idUsuario;
            }
            
            // Crear el estudiante
            $idEstudiante = $this->insertar($datosEstudiante);
            
            $this->db->commit();
            return $idEstudiante;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log($e->getMessage());
            return false;
        }
    }
    
    /**
     * Actualiza un estudiante y opcionalmente su cuenta de usuario
     * 
     * @param int $idEstudiante El ID del estudiante
     * @param array $datosEstudiante Los datos del estudiante
     * @param array|null $datosUsuario Los datos del usuario (opcional)
     * @return bool True si la actualización fue exitosa
     */
    public function actualizarEstudianteConUsuario($idEstudiante, $datosEstudiante, $datosUsuario = null) {
        $this->db->beginTransaction();
        
        try {
            // Si se incluyen datos de usuario, actualizar el usuario primero
            if ($datosUsuario && !empty($datosEstudiante['id_usuario'])) {
                $usuarioModel = new UsuarioModel();
                $usuarioModel->actualizar($datosEstudiante['id_usuario'], $datosUsuario);
            }
            
            // Actualizar el estudiante
            $this->actualizar($idEstudiante, $datosEstudiante);
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log($e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtiene los padres asociados a un estudiante
     * 
     * @param int $idEstudiante El ID del estudiante
     * @return array Lista de padres asociados
     */
    public function obtenerPadresDeEstudiante($idEstudiante) {
        $sql = "SELECT p.*, ep.parentesco,
                CONCAT(p.nombres, ' ', p.apellidos) as nombre_completo
                FROM padres p
                INNER JOIN estudiante_padre ep ON p.id_padre = ep.id_padre
                WHERE ep.id_estudiante = :id_estudiante
                ORDER BY p.apellidos, p.nombres";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id_estudiante', $idEstudiante);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Obtiene las deudas pendientes de un estudiante
     * 
     * @param int $idEstudiante El ID del estudiante
     * @return array Lista de deudas pendientes
     */
    public function obtenerDeudasPendientes($idEstudiante) {
        $sql = "SELECT d.*, c.descripcion as costo_descripcion, c.tipo as tipo_costo
                FROM deudas d
                LEFT JOIN costos c ON d.id_costo = c.id_costo
                WHERE d.id_estudiante = :id_estudiante AND d.estado = 'pendiente'
                ORDER BY d.fecha_vencimiento ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id_estudiante', $idEstudiante);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Obtiene el historial de pagos de un estudiante
     * 
     * @param int $idEstudiante El ID del estudiante
     * @return array Historial de pagos
     */
    public function obtenerHistorialPagos($idEstudiante) {
        $sql = "SELECT p.*, 
                u.nombre as registrado_por,
                fr.numero as numero_comprobante,
                fr.tipo as tipo_comprobante
                FROM pagos p
                LEFT JOIN usuarios u ON p.usuario_registro = u.id_usuario
                LEFT JOIN facturas_recibos fr ON p.id_pago = fr.id_pago
                WHERE p.id_estudiante = :id_estudiante
                ORDER BY p.fecha_pago DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id_estudiante', $idEstudiante);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Asocia un estudiante a un padre
     * 
     * @param int $idEstudiante El ID del estudiante
     * @param int $idPadre El ID del padre
     * @param string $parentesco El parentesco
     * @return bool True si la asociación fue exitosa
     */
    public function asociarPadre($idEstudiante, $idPadre, $parentesco = 'Padre/Madre') {
        $sql = "INSERT INTO estudiante_padre (id_estudiante, id_padre, parentesco) 
                VALUES (:id_estudiante, :id_padre, :parentesco)
                ON DUPLICATE KEY UPDATE parentesco = :parentesco";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id_estudiante', $idEstudiante);
        $stmt->bindValue(':id_padre', $idPadre);
        $stmt->bindValue(':parentesco', $parentesco);
        
        return $stmt->execute();
    }
    
    /**
     * Elimina la asociación entre un estudiante y un padre
     * 
     * @param int $idEstudiante El ID del estudiante
     * @param int $idPadre El ID del padre
     * @return bool True si la desasociación fue exitosa
     */
    public function desasociarPadre($idEstudiante, $idPadre) {
        $sql = "DELETE FROM estudiante_padre 
                WHERE id_estudiante = :id_estudiante 
                AND id_padre = :id_padre";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id_estudiante', $idEstudiante);
        $stmt->bindValue(':id_padre', $idPadre);
        
        return $stmt->execute();
    }
    
    /**
     * Obtiene todas las secciones disponibles para matrícula
     * 
     * @return array Lista de salones con información de grado y sección
     */
    public function obtenerSalonesDisponibles() {
        try {
            $sql = "SELECT sal.id_salon, 
                    sal.id_seccion,
                    CONCAT(g.nombre, ' - ', s.nombre, ' (', sal.anio, ')') as descripcion,
                    g.nombre as grado_nombre, 
                    g.nivel as nivel_educativo,
                    s.nombre as seccion_nombre,
                    sal.anio
                    FROM salones sal
                    INNER JOIN grados g ON sal.id_grado = g.id_grado
                    INNER JOIN secciones s ON sal.id_seccion = s.id_seccion
                    WHERE sal.estado = 'activo'
                    ORDER BY 
                        CASE g.nivel 
                            WHEN 'Inicial' THEN 1
                            WHEN 'Primaria' THEN 2  
                            WHEN 'Secundaria' THEN 3
                        END,
                        g.nombre, s.nombre";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // ✅ DEBUG: Verificar qué devuelve la consulta
            error_log("Salones obtenidos: " . print_r($resultado, true));
            
            return $resultado;
            
        } catch (Exception $e) {
            error_log("Error en obtenerSalonesDisponibles: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca un salón activo por id_seccion (devuelve el primer salón activo)
     * @param int $id_seccion
     * @return array|false
     */
    public function obtenerSalonPorSeccion($id_seccion) {
        $sql = "SELECT * FROM salones WHERE id_seccion = :id_seccion AND estado = 'activo' LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id_seccion', $id_seccion, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Crea un salón placeholder para una sección cuando no existe uno activo.
     * Devuelve el id_salon creado o false en caso de error.
     * @param int $id_seccion
     * @return int|false
     */
    public function crearSalonPlaceholder($id_seccion) {
        try {
            $anio = date('Y');
            $sql = "INSERT INTO salones (id_grado, id_seccion, id_docente, anio, cupo_maximo, estado) 
                    VALUES (NULL, :id_seccion, NULL, :anio, 0, 'activo')";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id_seccion', $id_seccion, PDO::PARAM_INT);
            $stmt->bindValue(':anio', $anio);
            $stmt->execute();
            return $this->db->lastInsertId();
        } catch (Exception $e) {
            error_log('Error crearSalonPlaceholder: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Crea un salón asociado a una sección y docente
     * @param int $id_seccion
     * @param int|null $id_docente
     * @param int|null $cupo
     * @return int|false
     */
    public function crearSalonConDocente($id_seccion, $id_docente = null, $cupo = null) {
        try {
            $anio = date('Y');
            $sql = "INSERT INTO salones (id_grado, id_seccion, id_docente, anio, cupo_maximo, estado) 
                    VALUES (NULL, :id_seccion, :id_docente, :anio, :cupo, 'activo')";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id_seccion', $id_seccion, PDO::PARAM_INT);
            $stmt->bindValue(':id_docente', $id_docente);
            $stmt->bindValue(':anio', $anio);
            $stmt->bindValue(':cupo', $cupo ?? 0, PDO::PARAM_INT);
            $stmt->execute();
            return $this->db->lastInsertId();
        } catch (Exception $e) {
            error_log('Error crearSalonConDocente: ' . $e->getMessage());
            return false;
        }
    }
    public function obtenerPorId($id_estudiante) {
    try {
        $sql = "SELECT e.*, 
                       g.nombre as grado,
                       s.nombre as seccion
                FROM estudiantes e 
                LEFT JOIN salones sa ON e.id_salon = sa.id_salon
                LEFT JOIN grados g ON sa.id_grado = g.id_grado
                LEFT JOIN secciones s ON sa.id_seccion = s.id_seccion
                WHERE e.id_estudiante = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id_estudiante]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    } catch (\PDOException $e) {
        error_log("Error en obtenerEstudiantePorId: " . $e->getMessage());
        return null;
    }
}
}

