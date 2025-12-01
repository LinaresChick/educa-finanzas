<?php
namespace Models;

require_once __DIR__ . '/../core/Modelo.php';

use Core\Modelo;
use PDO;

class DocenteModel extends Modelo {

    public function __construct() {
        parent::__construct('docentes', 'id_docente');
    }

    /**
     * Devuelve todos los docentes activos
     */
    public function obtenerTodosActivos() {
    $sql = "SELECT id_docente, nombres, apellidos, dni, correo, estado
            FROM docentes 
            WHERE estado = 'activo'
            ORDER BY apellidos, nombres";
    $stmt = $this->db->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


    /**
     * Obtiene un docente por id
     */
    public function obtenerPorId($id) {
        $sql = "SELECT * FROM docentes WHERE id_docente = :id_docente";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id_docente', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Inserta un docente
     */
    public function insert($data) {
        return parent::insertar($data);
    }

    /**
     * Actualiza un docente
     */
    public function update($id, $data) {
        return parent::actualizar($id, $data);
    }
    /**
 * Devuelve los docentes activos con su salón, grado y sección
 */
public function obtenerDocentesConSalon()
{
    $sql = "
        SELECT 
            d.id_docente,
            d.nombres,
            d.apellidos,
            d.dni,
            d.correo,
            d.estado,

            s.id_salon,
            g.nombre AS grado,
            g.nivel,
            sec.nombre AS seccion

        FROM docentes d
        LEFT JOIN salones s ON s.id_docente = d.id_docente
        LEFT JOIN grados g ON g.id_grado = s.id_grado
        LEFT JOIN secciones sec ON sec.id_seccion = s.id_seccion

        WHERE d.estado = 'activo'
        ORDER BY d.apellidos, d.nombres
    ";

    $stmt = $this->db->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

}
