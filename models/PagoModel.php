<?php
namespace Models;
require_once __DIR__ . '/../core/Modelo.php';

class PagoModel extends \Core\Modelo {
    protected $allowedFields;

    public function obtenerEstudiantePorPago($idPago) {
        try {
            $sql = "SELECT e.* FROM estudiantes e 
                   INNER JOIN pagos p ON e.id_estudiante = p.id_estudiante 
                   WHERE p.id_pago = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$idPago]);
            return $stmt->fetch();
        } catch (\PDOException $e) {
            error_log("Error en obtenerEstudiantePorPago: " . $e->getMessage());
            return null;
        }
    }
    public function __construct() {
    parent::__construct('pagos', 'id_pago');
    $this->allowedFields = [
        'id_estudiante',
        'id_padre',
        'pagador_nombre',
        'pagador_dni',
        'concepto',
        'banco',
        'monto',
        'metodo_pago',
        'fecha_pago',
        'descuento',
        'aumento',
        'observaciones',
        'foto_baucher',
        'usuario_registro'  // Agregar este campo
    ];
}
    public function obtenerPagosConEstudiantes() {
    try {
        $sql = "SELECT 
                p.*,
                e.nombres,
                e.apellidos,
                CONCAT(e.nombres, ' ', e.apellidos) as estudiante_nombre_completo,
                (p.monto + COALESCE(p.aumento, 0) - COALESCE(p.descuento, 0)) as monto_estudiante,
                DATE_FORMAT(p.fecha_pago, '%Y-%m-%d') as fecha_vencimiento,
                CASE 
                    WHEN p.id_pago IS NOT NULL THEN 'Pagado'
                    ELSE 'Pendiente'
                END as estado_pago,
                FORMAT(p.monto + COALESCE(p.aumento, 0) - COALESCE(p.descuento, 0), 2) as monto_formateado
               FROM pagos p 
               LEFT JOIN estudiantes e ON p.id_estudiante = e.id_estudiante 
               ORDER BY p.fecha_pago DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    } catch (\Exception $e) {
        error_log("Error en obtenerPagosConEstudiantes: " . $e->getMessage());
        throw new \Exception("Error al obtener los pagos");
    }
}

    public function obtenerPagosFiltrados($filtros) {
        try {
            $sql = "SELECT p.*, 
                          e.nombres AS estudiante_nombres, 
                          e.apellidos AS estudiante_apellidos 
                   FROM pagos p 
                   LEFT JOIN estudiantes e ON p.id_estudiante = e.id_estudiante 
                   WHERE 1=1";
            $params = [];

            if (!empty($filtros['fecha_inicio'])) {
                $sql .= " AND DATE(p.fecha_pago) >= ?";
                $params[] = $filtros['fecha_inicio'];
            }

            if (!empty($filtros['fecha_fin'])) {
                $sql .= " AND DATE(p.fecha_pago) <= ?";
                $params[] = $filtros['fecha_fin'];
            }

            if (!empty($filtros['estudiante'])) {
                $sql .= " AND (e.nombres LIKE ? OR e.apellidos LIKE ?)";
                $busqueda = '%' . $filtros['estudiante'] . '%';
                $params[] = $busqueda;
                $params[] = $busqueda;
            }

            if (!empty($filtros['concepto'])) {
                $sql .= " AND p.concepto LIKE ?";
                $params[] = '%' . $filtros['concepto'] . '%';
            }

            if (!empty($filtros['metodo_pago'])) {
                $sql .= " AND p.metodo_pago = ?";
                $params[] = $filtros['metodo_pago'];
            }

            $sql .= " ORDER BY p.fecha_pago DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            error_log("Error en obtenerPagosFiltrados: " . $e->getMessage());
            throw new \Exception("Error al filtrar los pagos");
        }
    }
    public function crear($datos) {
    try {
        // Asegurémonos de que los campos requeridos estén presentes
        $camposRequeridos = ['id_estudiante', 'concepto', 'banco', 'monto', 'metodo_pago', 'fecha_pago', 'usuario_registro'];
        foreach ($camposRequeridos as $campo) {
            if (!isset($datos[$campo]) || empty($datos[$campo])) {
                throw new \Exception("El campo {$campo} es requerido");
            }
        }

        // Establecer valores por defecto para campos opcionales
        if (!isset($datos['foto_baucher'])) {
            $datos['foto_baucher'] = '';
        }
        if (!isset($datos['descuento']) || $datos['descuento'] === '') {
            $datos['descuento'] = 0;
        }
        if (!isset($datos['aumento']) || $datos['aumento'] === '') {
            $datos['aumento'] = 0;
        }
        if (!isset($datos['observaciones'])) {
            $datos['observaciones'] = '';
        }

        // Log para depuración
        error_log("Datos preparados para insertar: " . print_r($datos, true));
        error_log("Campos permitidos: " . print_r($this->allowedFields, true));
        
        // Intentar insertar el registro
        $id = $this->insertar($datos);
        
        if (!$id) {
            error_log("Error: No se obtuvo ID después de la inserción");
            throw new \Exception("No se pudo obtener el ID del pago insertado");
        }

        error_log("Pago insertado correctamente con ID: " . $id);
        return $id;
    } catch (\Exception $e) {
        error_log("Error en PagoModel::crear: " . $e->getMessage());
        throw $e;
    }
}
    public function contarPagos() {
        try {
            $sql = "SELECT COUNT(*) as total FROM pagos";
            $resultado = $this->db->query($sql)->fetch(\PDO::FETCH_ASSOC);
            return $resultado['total'];
        } catch (\Exception $e) {
            error_log("Error al contar pagos: " . $e->getMessage());
            throw $e;
        }
    }

    public function calcularIngresoMensual() {
        try {
            $sql = "SELECT COALESCE(SUM(monto + COALESCE(aumento, 0) - COALESCE(descuento, 0)), 0) as total 
                   FROM pagos 
                   WHERE YEAR(fecha_pago) = YEAR(CURRENT_DATE()) 
                   AND MONTH(fecha_pago) = MONTH(CURRENT_DATE())";
            $resultado = $this->db->query($sql)->fetch(\PDO::FETCH_ASSOC);
            return floatval($resultado['total']);
        } catch (\Exception $e) {
            error_log("Error al calcular ingreso mensual: " . $e->getMessage());
            throw $e;
        }
    }

    public function contarPagosPendientes() {
        try {
            $sql = "SELECT COUNT(DISTINCT e.id_estudiante) as total
                   FROM estudiantes e
                   LEFT JOIN pagos p ON e.id_estudiante = p.id_estudiante 
                   AND YEAR(p.fecha_pago) = YEAR(CURRENT_DATE())
                   AND MONTH(p.fecha_pago) = MONTH(CURRENT_DATE())
                   WHERE p.id_pago IS NULL
                   AND e.estado = 'activo'";
            
            $resultado = $this->db->query($sql)->fetch(\PDO::FETCH_ASSOC);
            return intval($resultado['total']);
        } catch (\Exception $e) {
            error_log("Error al contar pagos pendientes: " . $e->getMessage());
            throw $e;
        }
    }

    public function obtenerUltimosPagos($limite = 5) {
        try {
            $sql = "SELECT p.*, 
                          e.nombres, 
                          e.apellidos,
                          DATE_FORMAT(p.fecha_pago, '%d/%m/%Y') as fecha_formateada,
                          FORMAT(p.monto + COALESCE(p.aumento, 0) - COALESCE(p.descuento, 0), 2) as monto_total
                   FROM pagos p 
                   INNER JOIN estudiantes e ON p.id_estudiante = e.id_estudiante 
                   ORDER BY p.fecha_pago DESC, p.id_pago DESC 
                   LIMIT :limite";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':limite', $limite, \PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("Error al obtener últimos pagos: " . $e->getMessage());
            throw $e;
        }
    }

    public function eliminar($id_pago) {
        try {
            // Primero obtener el registro para verificar si existe y obtener la ruta del voucher
            $pago = $this->buscarPorId($id_pago);
            if (!$pago) {
                throw new \Exception("El pago no existe");
            }

            // Si hay un voucher, eliminarlo
            if (!empty($pago['foto_baucher'])) {
                $rutaVoucher = __DIR__ . '/../public/uploads/vouchers/' . $pago['foto_baucher'];
                if (file_exists($rutaVoucher)) {
                    unlink($rutaVoucher);
                }
            }

            // Eliminar el registro de la base de datos
            $sql = "DELETE FROM pagos WHERE id_pago = :id_pago";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id_pago', $id_pago, \PDO::PARAM_INT);
            
            if (!$stmt->execute()) {
                throw new \Exception("Error al eliminar el pago de la base de datos");
            }

            return true;
        } catch (\Exception $e) {
            error_log("Error al eliminar pago: " . $e->getMessage());
            throw $e;
        }
    }
    public function obtenerPorId($id_pago) {
    try {
        $sql = "SELECT p.*, 
                       e.nombres, 
                       e.apellidos,
                       CONCAT(e.nombres, ' ', e.apellidos) as estudiante_nombre_completo
                FROM pagos p 
                LEFT JOIN estudiantes e ON p.id_estudiante = e.id_estudiante 
                WHERE p.id_pago = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id_pago]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    } catch (\PDOException $e) {
        error_log("Error en obtenerPorId: " . $e->getMessage());
        return null;
    }
}

public function obtenerPorEstudiante($id_estudiante) {
    try {
        $sql = "SELECT p.*, 
                       e.nombres, 
                       e.apellidos,
                       CONCAT(e.nombres, ' ', e.apellidos) as estudiante_nombre_completo
                FROM pagos p 
                LEFT JOIN estudiantes e ON p.id_estudiante = e.id_estudiante 
                WHERE p.id_estudiante = ?
                ORDER BY p.fecha_pago DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id_estudiante]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    } catch (\PDOException $e) {
        error_log("Error en obtenerPorEstudiante: " . $e->getMessage());
        return [];
    }
}

public function buscarPorId($id) {
    return $this->obtenerPorId($id);
}
// Agrega este método en PagoModel.php para el test

public function getAllowedFields() {
    return $this->allowedFields;
}
}