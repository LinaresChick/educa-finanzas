<?php
namespace Models;

require_once __DIR__ . '/../core/Modelo.php';

use Core\Modelo;
use PDO;

class GradoModel extends Modelo {

    public function __construct() {
        parent::__construct('grados', 'id_grado');
        $this->allowedFields = [
            'nombre',
            'nivel' // Inicial, Primaria, Secundaria
        ];
    }

    /**
     * Obtener todos los grados ordenados por nivel y nombre
     */
    public function obtenerTodos($columnas = ['*'], $condiciones = [], $orden = '', $limite = null, $offset = null) {
    $sql = "SELECT * FROM grados ORDER BY nivel ASC, nombre ASC";
    $stmt = $this->db->query($sql);
    return $stmt->fetchAll();
}


    /**
     * Obtener grados por nivel (Inicial / Primaria / Secundaria)
     */
    public function obtenerPorNivel($nivel) {
        $sql = "SELECT * FROM grados WHERE nivel = :nivel ORDER BY nombre ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':nivel', $nivel);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Obtener un grado por ID (con conteo de salones)
     */
    public function obtenerPorIdConSalones($id) {
        $sql = "SELECT g.*,
                COUNT(sal.id_salon) AS total_salones
                FROM grados g
                LEFT JOIN salones sal ON g.id_grado = sal.id_grado
                WHERE g.id_grado = :id
                GROUP BY g.id_grado";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }
}
