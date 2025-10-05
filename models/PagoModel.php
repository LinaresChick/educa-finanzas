<?php
/**
 * Modelo para la gestión de pagos
 */
namespace Models;

require_once __DIR__ . '/../core/Modelo.php';

use Core\Modelo;
use \Exception;
use \PDO;

class PagoModel extends Modelo {
    
    /**
     * Constructor de la clase
     */
    public function __construct() {
        parent::__construct('pagos', 'id_pago');
        $this->allowedFields = [
            'id_estudiante',
            'id_deuda',
            'concepto',
            'monto',
            'metodo_pago',
            'fecha_pago',
            'estado',
            'observaciones',
            'usuario_registro'
        ];
    }

    /**
     * Calcula el ingreso total de todos los pagos procesados
     * 
     * @return float Total de ingresos
     */
    public function calcularIngresoTotal(): float {
        $sql = "SELECT COALESCE(SUM(monto), 0) as total FROM pagos WHERE estado = 'procesado'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return (float)$resultado['total'];
    }

    /**
     * Cuenta el total de pagos procesados
     * 
     * @return int Total de pagos procesados
     */
    public function contarPagosProcesados(): int {
        $sql = "SELECT COUNT(*) as total FROM pagos WHERE estado = 'procesado'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$resultado['total'];
    }

    /**
     * Cuenta el número de pagos procesados hoy
     * 
     * @return int Número de pagos procesados hoy
     */
    public function contarPagosProcesadosHoy() {
        $sql = "SELECT COUNT(*) as total 
                FROM pagos 
                WHERE DATE(fecha_pago) = CURDATE() 
                AND estado = 'procesado'";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return (int) $resultado['total'];
    }
    
    /**
     * Obtiene todos los pagos con información adicional
     * 
     * @param array $condiciones Las condiciones de filtrado
     * @param string $orden El orden de los resultados
     * @param int $limite El límite de registros
     * @param int $offset El desplazamiento para paginación
     * @return array Lista de pagos con información detallada
     */
    public function obtenerPagosConInfo($condiciones = [], $orden = 'p.fecha_pago DESC', $limite = null, $offset = null) {
        $sql = "SELECT p.*, 
                e.nombres as estudiante_nombres, e.apellidos as estudiante_apellidos,
                CONCAT(e.nombres, ' ', e.apellidos) as estudiante_nombre_completo,
                u.nombre as registrado_por,
                fr.numero as numero_comprobante, fr.tipo as tipo_comprobante
                FROM pagos p
                LEFT JOIN estudiantes e ON p.id_estudiante = e.id_estudiante
                LEFT JOIN usuarios u ON p.usuario_registro = u.id_usuario
                LEFT JOIN facturas_recibos fr ON p.id_pago = fr.id_pago";
        
        // Agregar condiciones WHERE
        if (!empty($condiciones)) {
            $sql .= " WHERE ";
            $where = [];
            foreach ($condiciones as $campo => $valor) {
                $where[] = "{$campo} = :{$campo}";
            }
            $sql .= implode(' AND ', $where);
        }
        
        // Agregar ORDER BY
        if (!empty($orden)) {
            $sql .= " ORDER BY {$orden}";
        }
        
        // Agregar LIMIT y OFFSET
        if ($limite !== null) {
            $sql .= " LIMIT {$limite}";
            if ($offset !== null) {
                $sql .= " OFFSET {$offset}";
            }
        }
        
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
     * Obtiene un pago por su ID con información detallada
     * 
     * @param int $id El ID del pago
     * @return array|false El pago encontrado o false si no existe
     */
    public function obtenerPagoDetalle($id) {
        $sql = "SELECT p.*, 
                e.nombres as estudiante_nombres, e.apellidos as estudiante_apellidos,
                CONCAT(e.nombres, ' ', e.apellidos) as estudiante_nombre_completo,
                e.dni as estudiante_dni,
                u.nombre as registrado_por,
                fr.id_comprobante, fr.numero as numero_comprobante, fr.tipo as tipo_comprobante,
                fr.fecha_emision, fr.subtotal, fr.igv, fr.total,
                fr.xml_path, fr.pdf_path, fr.estado as estado_comprobante,
                d.concepto as deuda_concepto, d.estado as estado_deuda,
                c.descripcion as costo_descripcion, c.tipo as tipo_costo
                FROM pagos p
                LEFT JOIN estudiantes e ON p.id_estudiante = e.id_estudiante
                LEFT JOIN usuarios u ON p.usuario_registro = u.id_usuario
                LEFT JOIN facturas_recibos fr ON p.id_pago = fr.id_pago
                LEFT JOIN deudas d ON p.id_deuda = d.id_deuda
                LEFT JOIN costos c ON d.id_costo = c.id_costo
                WHERE p.id_pago = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }
    
    /**
     * Obtiene el historial de pagos de un estudiante
     * 
     * @param int $idEstudiante El ID del estudiante
     * @return array Historial de pagos
     */
    public function obtenerHistorialPagosEstudiante($idEstudiante) {
        $sql = "SELECT p.*, 
                u.nombre as registrado_por,
                fr.numero as numero_comprobante, fr.tipo as tipo_comprobante
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
     * Registra un nuevo pago y actualiza la deuda asociada
     * 
     * @param array $datosPago Los datos del pago
     * @param array|null $datosComprobante Los datos del comprobante (opcional)
     * @return int|false El ID del pago registrado o false si falla
     */
    public function registrarPago($datosPago, $datosComprobante = null) {
        $this->db->beginTransaction();
        
        try {
            // Insertar el pago
            $idPago = $this->insertar($datosPago);
            
            // Si hay una deuda asociada, actualizarla
            if (!empty($datosPago['id_deuda'])) {
                $sql = "UPDATE deudas SET estado = 'pagado' WHERE id_deuda = :id_deuda";
                $stmt = $this->db->prepare($sql);
                $stmt->bindValue(':id_deuda', $datosPago['id_deuda']);
                $stmt->execute();
            }
            
            // Si se requiere generar comprobante, insertarlo
            if ($datosComprobante) {
                $datosComprobante['id_pago'] = $idPago;
                $this->generarComprobante($datosComprobante);
            }
            
            $this->db->commit();
            return $idPago;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log($e->getMessage());
            return false;
        }
    }
    
    /**
     * Anula un pago y actualiza la deuda asociada
     * 
     * @param int $idPago El ID del pago a anular
     * @param string $motivo El motivo de la anulación
     * @return bool True si la anulación fue exitosa
     */
    public function anularPago($idPago, $motivo = '') {
        $this->db->beginTransaction();
        
        try {
            // Obtener datos del pago
            $pago = $this->buscarPorId($idPago);
            if (!$pago) {
                return false;
            }
            
            // Actualizar el estado del pago
            $sql = "UPDATE pagos SET estado = 'anulado', observaciones = CONCAT(IFNULL(observaciones, ''), ' [Anulado: ', :motivo, ']') WHERE id_pago = :id_pago";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id_pago', $idPago);
            $stmt->bindValue(':motivo', $motivo);
            $stmt->execute();
            
            // Si hay una deuda asociada, restablecerla a pendiente
            if (!empty($pago['id_deuda'])) {
                $sql = "UPDATE deudas SET estado = 'pendiente' WHERE id_deuda = :id_deuda";
                $stmt = $this->db->prepare($sql);
                $stmt->bindValue(':id_deuda', $pago['id_deuda']);
                $stmt->execute();
            }
            
            // Si hay un comprobante, marcarlo como anulado
            $sql = "UPDATE facturas_recibos SET estado = 'anulado' WHERE id_pago = :id_pago";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id_pago', $idPago);
            $stmt->execute();
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log($e->getMessage());
            return false;
        }
    }
    
    /**
     * Genera un comprobante para un pago
     * 
     * @param array $datos Los datos del comprobante
     * @return int|false El ID del comprobante generado o false si falla
     */
    public function generarComprobante($datos) {
        $sql = "INSERT INTO facturas_recibos (
                id_pago, tipo, numero, fecha_emision, 
                subtotal, igv, total, estado
            ) VALUES (
                :id_pago, :tipo, :numero, :fecha_emision,
                :subtotal, :igv, :total, 'generado'
            )";
            
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id_pago', $datos['id_pago']);
        $stmt->bindValue(':tipo', $datos['tipo']);
        $stmt->bindValue(':numero', $datos['numero']);
        $stmt->bindValue(':fecha_emision', $datos['fecha_emision']);
        $stmt->bindValue(':subtotal', $datos['subtotal']);
        $stmt->bindValue(':igv', $datos['igv']);
        $stmt->bindValue(':total', $datos['total']);
        
        if ($stmt->execute()) {
            return $this->db->lastInsertId();
        } else {
            return false;
        }
    }
    
    /**
     * Obtiene todas las deudas pendientes de un estudiante
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
     * Genera un número de comprobante único
     * 
     * @param string $tipo El tipo de comprobante ('factura' o 'recibo')
     * @return string El número de comprobante generado
     */
    public function generarNumeroComprobante($tipo) {
        $prefijo = $tipo === 'factura' ? 'F' : 'R';
        
        // Obtener el último número de ese tipo
        $sql = "SELECT MAX(SUBSTRING(numero, 2)) as ultimo 
                FROM facturas_recibos 
                WHERE tipo = :tipo";
                
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':tipo', $tipo);
        $stmt->execute();
        $resultado = $stmt->fetch();
        
        $ultimo = $resultado['ultimo'] ?? 0;
        $siguiente = str_pad($ultimo + 1, 8, '0', STR_PAD_LEFT);
        
        return $prefijo . $siguiente;
    }
    
    /**
     * Obtiene estadísticas de pagos por período
     * 
     * @param string $periodo El período ('hoy', 'semana', 'mes', 'anio')
     * @return array Estadísticas de pagos
     */
    public function obtenerEstadisticasPagos($periodo = 'mes') {
        $condicion = "";
        
        switch ($periodo) {
            case 'hoy':
                $condicion = "WHERE DATE(p.fecha_pago) = CURDATE()";
                break;
            case 'semana':
                $condicion = "WHERE YEARWEEK(p.fecha_pago) = YEARWEEK(NOW())";
                break;
            case 'mes':
                $condicion = "WHERE YEAR(p.fecha_pago) = YEAR(NOW()) AND MONTH(p.fecha_pago) = MONTH(NOW())";
                break;
            case 'anio':
                $condicion = "WHERE YEAR(p.fecha_pago) = YEAR(NOW())";
                break;
            default:
                $condicion = "";
        }
        
        $sql = "SELECT 
                COUNT(*) as total_pagos,
                SUM(p.monto) as total_monto,
                COUNT(CASE WHEN p.metodo_pago = 'efectivo' THEN 1 END) as pagos_efectivo,
                COUNT(CASE WHEN p.metodo_pago = 'transferencia' THEN 1 END) as pagos_transferencia,
                COUNT(CASE WHEN p.metodo_pago = 'tarjeta' THEN 1 END) as pagos_tarjeta,
                SUM(CASE WHEN p.metodo_pago = 'efectivo' THEN p.monto ELSE 0 END) as monto_efectivo,
                SUM(CASE WHEN p.metodo_pago = 'transferencia' THEN p.monto ELSE 0 END) as monto_transferencia,
                SUM(CASE WHEN p.metodo_pago = 'tarjeta' THEN p.monto ELSE 0 END) as monto_tarjeta
                FROM pagos p
                {$condicion}
                AND p.estado = 'completado'";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetch();
    }
    
    /**
     * Busca pagos según varios criterios
     * 
     * @param array $criterios Los criterios de búsqueda
     * @return array Lista de pagos que cumplen los criterios
     */
    public function buscarPagos($criterios) {
        $where = [];
        $params = [];
        
        // Filtrar por estudiante (nombre o apellido)
        if (!empty($criterios['estudiante'])) {
            $where[] = "(e.nombres LIKE :estudiante OR e.apellidos LIKE :estudiante)";
            $params['estudiante'] = '%' . $criterios['estudiante'] . '%';
        }
        
        // Filtrar por concepto
        if (!empty($criterios['concepto'])) {
            $where[] = "p.concepto LIKE :concepto";
            $params['concepto'] = '%' . $criterios['concepto'] . '%';
        }
        
        // Filtrar por método de pago
        if (!empty($criterios['metodo_pago'])) {
            $where[] = "p.metodo_pago = :metodo_pago";
            $params['metodo_pago'] = $criterios['metodo_pago'];
        }
        
        // Filtrar por estado
        if (!empty($criterios['estado'])) {
            $where[] = "p.estado = :estado";
            $params['estado'] = $criterios['estado'];
        }
        
        // Filtrar por rango de fechas
        if (!empty($criterios['fecha_inicio'])) {
            $where[] = "p.fecha_pago >= :fecha_inicio";
            $params['fecha_inicio'] = $criterios['fecha_inicio'];
        }
        
        if (!empty($criterios['fecha_fin'])) {
            $where[] = "p.fecha_pago <= :fecha_fin";
            $params['fecha_fin'] = $criterios['fecha_fin'];
        }
        
        // Filtrar por rango de montos
        if (!empty($criterios['monto_min'])) {
            $where[] = "p.monto >= :monto_min";
            $params['monto_min'] = $criterios['monto_min'];
        }
        
        if (!empty($criterios['monto_max'])) {
            $where[] = "p.monto <= :monto_max";
            $params['monto_max'] = $criterios['monto_max'];
        }
        
        // Construir la consulta SQL
        $sql = "SELECT p.*, 
                e.nombres as estudiante_nombres, e.apellidos as estudiante_apellidos,
                CONCAT(e.nombres, ' ', e.apellidos) as estudiante_nombre_completo,
                u.nombre as registrado_por,
                fr.numero as numero_comprobante, fr.tipo as tipo_comprobante
                FROM pagos p
                LEFT JOIN estudiantes e ON p.id_estudiante = e.id_estudiante
                LEFT JOIN usuarios u ON p.usuario_registro = u.id_usuario
                LEFT JOIN facturas_recibos fr ON p.id_pago = fr.id_pago";
        
        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        
        $sql .= " ORDER BY p.fecha_pago DESC";
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($params as $param => $valor) {
            $stmt->bindValue(":{$param}", $valor);
        }
        
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Obtiene los últimos pagos registrados
     * 
     * @param int $limite El número de pagos a retornar
     * @return array Los últimos pagos registrados
     */
    public function obtenerUltimosPagos($limite = 5) {
        $sql = "SELECT p.*, 
                e.nombres as estudiante_nombres, e.apellidos as estudiante_apellidos,
                CONCAT(e.nombres, ' ', e.apellidos) as estudiante_nombre_completo,
                u.nombre as registrado_por
                FROM pagos p
                LEFT JOIN estudiantes e ON p.id_estudiante = e.id_estudiante
                LEFT JOIN usuarios u ON p.usuario_registro = u.id_usuario
                ORDER BY p.fecha_pago DESC 
                LIMIT :limite";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Cuenta el total de pagos según los criterios especificados
     * 
     * @param array $criterios Los criterios de filtrado (opcional)
     * @return int El número total de pagos
     */
    public function contarPagos($criterios = []) {
        $sql = "SELECT COUNT(*) FROM pagos p";
        $where = [];
        $params = [];

        if (!empty($criterios['fecha_inicio'])) {
            $where[] = "p.fecha_pago >= :fecha_inicio";
            $params['fecha_inicio'] = $criterios['fecha_inicio'];
        }

        if (!empty($criterios['fecha_fin'])) {
            $where[] = "p.fecha_pago <= :fecha_fin";
            $params['fecha_fin'] = $criterios['fecha_fin'];
        }

        if (!empty($criterios['estado'])) {
            $where[] = "p.estado = :estado";
            $params['estado'] = $criterios['estado'];
        }

        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }

        $stmt = $this->db->prepare($sql);
        foreach ($params as $param => $valor) {
            $stmt->bindValue(":{$param}", $valor);
        }
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    /**
     * Calcula la suma total de pagos según los criterios especificados
     * 
     * @param array $criterios Los criterios de filtrado (opcional)
     * @return float El monto total de los pagos
     */
    public function calcularTotalPagos($criterios = []) {
        $sql = "SELECT COALESCE(SUM(monto), 0) FROM pagos p";
        $where = [];
        $params = [];

        if (!empty($criterios['fecha_inicio'])) {
            $where[] = "p.fecha_pago >= :fecha_inicio";
            $params['fecha_inicio'] = $criterios['fecha_inicio'];
        }

        if (!empty($criterios['fecha_fin'])) {
            $where[] = "p.fecha_pago <= :fecha_fin";
            $params['fecha_fin'] = $criterios['fecha_fin'];
        }

        if (!empty($criterios['estado'])) {
            $where[] = "p.estado = :estado";
            $params['estado'] = $criterios['estado'];
        }

        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }

        $stmt = $this->db->prepare($sql);
        foreach ($params as $param => $valor) {
            $stmt->bindValue(":{$param}", $valor);
        }
        $stmt->execute();
        return (float)$stmt->fetchColumn();
    }

    /**
     * Calcula el ingreso total del mes actual
     * 
     * @return float El monto total de ingresos del mes actual
     */
    public function calcularIngresoMensual() {
        $sql = "SELECT COALESCE(SUM(monto), 0) 
                FROM pagos 
                WHERE YEAR(fecha_pago) = YEAR(CURRENT_DATE)
                AND MONTH(fecha_pago) = MONTH(CURRENT_DATE)
                AND estado = 'completado'";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return (float)$stmt->fetchColumn();
    }

    /**
     * Cuenta los pagos pendientes
     * 
     * @return int El número de pagos pendientes
     */
    public function contarPagosPendientes() {
        $sql = "SELECT COUNT(*) FROM pagos WHERE estado = 'pendiente'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }
}
