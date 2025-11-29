<?php
namespace Models;

require_once __DIR__ . '/../core/Modelo.php';

use Core\Modelo;
use \PDO;

class DocenteModel extends Modelo {
    public function __construct() {
        parent::__construct('docentes', 'id_docente');
    }

    /**
     * Devuelve todos los docentes activos
     * @return array
     */
    public function obtenerTodosActivos() {
        $sql = "SELECT id_docente, nombres, apellidos, dni, correo FROM docentes WHERE estado = 'activo' ORDER BY apellidos, nombres";
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
}
