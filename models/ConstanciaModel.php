<?php
namespace Models;
require_once __DIR__ . '/../core/Modelo.php';

class ConstanciaModel extends \Core\Modelo {
    public function __construct() {
        parent::__construct('constancias', 'id_constancia');
    }

    public function crear($datos) {
        try {
            $campos = ['id_estudiante','nombre_solicitante','dni_solicitante','estado','id_pago'];
            $insert = [];
            foreach ($campos as $c) {
                if (isset($datos[$c])) $insert[$c] = $datos[$c];
            }
            if (empty($insert['estado'])) $insert['estado'] = 'pendiente';
            return $this->insertar($insert);
        } catch (\Exception $e) {
            error_log('Error en ConstanciaModel::crear: ' . $e->getMessage());
            throw $e;
        }
    }

    public function listarTodos() {
        try {
            $sql = "SELECT c.*, CONCAT(e.nombres,' ',e.apellidos) as estudiante_nombre FROM constancias c LEFT JOIN estudiantes e ON c.id_estudiante = e.id_estudiante ORDER BY c.fecha_creacion DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log('Error en ConstanciaModel::obtenerTodos: '. $e->getMessage());
            return [];
        }
    }

    public function toggleEstado($id) {
        try {
            $c = $this->buscarPorId($id);
            if (!$c) return false;
            $nuevo = $c['estado'] === 'pendiente' ? 'pagado' : 'pendiente';
            $sql = "UPDATE constancias SET estado = :estado WHERE id_constancia = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':estado' => $nuevo, ':id' => $id]);
        } catch (\Exception $e) {
            error_log('Error en toggleEstado constancias: ' . $e->getMessage());
            return false;
        }
    }

    public function obtenerPorId($id) {
        try {
            $sql = "SELECT c.*, CONCAT(e.nombres,' ',e.apellidos) as estudiante_nombre FROM constancias c LEFT JOIN estudiantes e ON c.id_estudiante = e.id_estudiante WHERE c.id_constancia = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log('Error en ConstanciaModel::obtenerPorId: '. $e->getMessage());
            return null;
        }
    }
}
