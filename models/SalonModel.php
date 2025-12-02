<?php
namespace Models;

require_once __DIR__ . '/../core/Modelo.php';

use Core\Modelo;
use PDO;

class SalonModel extends Modelo {

    public function __construct() {
        parent::__construct('salones', 'id_salon');
        $this->allowedFields = [
            'nombre',
            'id_grado',
            'id_seccion',
            'aforo'
        ];
    }

    /**
     * Obtener todos los salones con sección y grado
     */
    public function obtenerTodos($columnas = ['*'], $condiciones = [], $orden = '', $limite = null, $offset = null) {
    $sql = "
        SELECT 
            sal.*,
            g.nombre AS grado_nombre,
            g.nivel AS grado_nivel,
            s.nombre AS seccion_nombre
        FROM salones sal
        INNER JOIN grados g ON sal.id_grado = g.id_grado
        INNER JOIN secciones s ON sal.id_seccion = s.id_seccion
        ORDER BY sal.id_salon ASC
    ";

    $stmt = $this->db->query($sql);
    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
}




    /**
     * Obtener un salón con sus relaciones
     */
    public function obtenerCompleto($idSalon) {
        $sql = "SELECT sal.*,
                       g.nombre AS grado_nombre,
                       g.nivel AS grado_nivel,
                       s.nombre AS seccion_nombre,
                       COUNT(e.id_estudiante) AS total_estudiantes
                FROM salones sal
                INNER JOIN grados g ON sal.id_grado = g.id_grado
                INNER JOIN secciones s ON sal.id_seccion = s.id_seccion
                LEFT JOIN estudiantes e ON sal.id_salon = e.id_salon
                WHERE sal.id_salon = :id
                GROUP BY sal.id_salon";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $idSalon, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Obtener salones por grado o sección
     */
    public function obtenerPorGradoSeccion($idGrado, $idSeccion) {
        $sql = "SELECT * FROM salones 
                WHERE id_grado = :id_grado 
                AND id_seccion = :id_seccion
                ORDER BY nombre ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id_grado' => $idGrado,
            ':id_seccion' => $idSeccion
        ]);

        return $stmt->fetchAll();
    }
    /**
 * Obtener salones que todavía NO tienen docente asignado
 */
public function obtenerDisponibles() {
    $sql = "
        SELECT sal.id_salon,
               g.nombre AS grado_nombre,
               g.nivel AS grado_nivel,
               s.nombre AS seccion_nombre
        FROM salones sal
        INNER JOIN grados g     ON sal.id_grado = g.id_grado
        INNER JOIN secciones s  ON sal.id_seccion = s.id_seccion
        WHERE sal.id_docente IS NULL
        ORDER BY g.nivel ASC, g.nombre ASC, s.nombre ASC
    ";

    $stmt = $this->db->query($sql);
    return $stmt->fetchAll();
}
public function insertar($data) {
    $sql = "INSERT INTO salones (id_grado, id_seccion, id_docente, anio, cupo_maximo, estado)
            VALUES (:id_grado, :id_seccion, :id_docente, :anio, :cupo_maximo, :estado)";

    $stmt = $this->db->prepare($sql);
    $stmt->execute($data);
    
    return $this->db->lastInsertId();
}
public function getSalonesDisponibles() {
    $sql = "SELECT s.id_salon, g.nombre AS grado, sec.nombre AS seccion
            FROM salones s
            INNER JOIN grados g ON s.id_grado = g.id_grado
            INNER JOIN secciones sec ON s.id_seccion = sec.id_seccion
            WHERE s.id_docente IS NULL";

    $stmt = $this->db->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}



}
