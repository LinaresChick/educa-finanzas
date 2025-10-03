<?php
/**
 * Modelo para la gestión de reportes financieros y administrativos
 */
namespace Models;

require_once __DIR__ . '/../core/Modelo.php';
require_once __DIR__ . '/../models/PagoModel.php';
require_once __DIR__ . '/../models/EstudianteModel.php';

use Core\Modelo;
use \Exception;
use \PDO;

class ReporteModel extends Modelo {
    
    /**
     * Constructor de la clase
     */
    public function __construct() {
        parent::__construct('reportes', 'id_reporte');
    }
    
    /**
     * Genera un reporte de deudas por estudiante
     * 
     * @return array Datos del reporte de deudas
     */
    public function generarReporteDeudas() {
        $sql = "SELECT e.id_estudiante, e.nombres, e.apellidos, e.dni, 
                       s.nombre AS salon, s.grado, s.nivel,
                       SUM(CASE WHEN p.estado = 'pendiente' THEN p.monto ELSE 0 END) AS total_deuda,
                       COUNT(CASE WHEN p.estado = 'pendiente' THEN 1 END) AS pagos_pendientes,
                       MAX(p.fecha_vencimiento) AS proxima_fecha_vencimiento
                FROM estudiantes e
                LEFT JOIN pagos p ON e.id_estudiante = p.id_estudiante
                LEFT JOIN salones s ON e.id_salon = s.id_salon
                GROUP BY e.id_estudiante, e.nombres, e.apellidos, e.dni, s.nombre, s.grado, s.nivel
                ORDER BY total_deuda DESC";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Genera un reporte financiero por períodos
     * 
     * @param string $fechaInicio Fecha de inicio del período
     * @param string $fechaFin Fecha de fin del período
     * @return array Datos del reporte financiero
     */
    public function generarReporteFinanciero($fechaInicio, $fechaFin) {
        $sql = "SELECT 
                    DATE_FORMAT(fecha_pago, '%Y-%m') AS periodo,
                    COUNT(*) AS total_pagos,
                    SUM(monto) AS total_ingresos,
                    COUNT(CASE WHEN metodo_pago = 'efectivo' THEN 1 END) AS pagos_efectivo,
                    SUM(CASE WHEN metodo_pago = 'efectivo' THEN monto ELSE 0 END) AS monto_efectivo,
                    COUNT(CASE WHEN metodo_pago = 'transferencia' THEN 1 END) AS pagos_transferencia,
                    SUM(CASE WHEN metodo_pago = 'transferencia' THEN monto ELSE 0 END) AS monto_transferencia,
                    COUNT(CASE WHEN metodo_pago = 'tarjeta' THEN 1 END) AS pagos_tarjeta,
                    SUM(CASE WHEN metodo_pago = 'tarjeta' THEN monto ELSE 0 END) AS monto_tarjeta,
                    COUNT(CASE WHEN tipo_pago = 'matricula' THEN 1 END) AS pagos_matricula,
                    SUM(CASE WHEN tipo_pago = 'matricula' THEN monto ELSE 0 END) AS monto_matricula,
                    COUNT(CASE WHEN tipo_pago = 'mensualidad' THEN 1 END) AS pagos_mensualidad,
                    SUM(CASE WHEN tipo_pago = 'mensualidad' THEN monto ELSE 0 END) AS monto_mensualidad,
                    COUNT(CASE WHEN tipo_pago = 'uniforme' THEN 1 END) AS pagos_uniforme,
                    SUM(CASE WHEN tipo_pago = 'uniforme' THEN monto ELSE 0 END) AS monto_uniforme,
                    COUNT(CASE WHEN tipo_pago = 'material' THEN 1 END) AS pagos_material,
                    SUM(CASE WHEN tipo_pago = 'material' THEN monto ELSE 0 END) AS monto_material,
                    COUNT(CASE WHEN tipo_pago = 'actividad' THEN 1 END) AS pagos_actividad,
                    SUM(CASE WHEN tipo_pago = 'actividad' THEN monto ELSE 0 END) AS monto_actividad,
                    COUNT(CASE WHEN tipo_pago = 'otro' THEN 1 END) AS pagos_otro,
                    SUM(CASE WHEN tipo_pago = 'otro' THEN monto ELSE 0 END) AS monto_otro
                FROM pagos
                WHERE estado = 'procesado'
                AND fecha_pago BETWEEN :fecha_inicio AND :fecha_fin
                GROUP BY periodo
                ORDER BY periodo";
                
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':fecha_inicio', $fechaInicio);
        $stmt->bindParam(':fecha_fin', $fechaFin);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtiene estadísticas generales del sistema
     * 
     * @return array Las estadísticas generales
     */
    public function obtenerEstadisticasGenerales() {
        try {
            // Calcular totales del mes actual
            $sqlMensual = "SELECT 
                SUM(CASE WHEN estado = 'completado' THEN monto ELSE 0 END) as total_ingresos,
                COUNT(CASE WHEN estado = 'pendiente' THEN 1 END) as total_pendientes,
                SUM(CASE WHEN estado = 'pendiente' THEN monto ELSE 0 END) as total_deudas
                FROM pagos
                WHERE MONTH(fecha_pago) = MONTH(CURRENT_DATE)
                AND YEAR(fecha_pago) = YEAR(CURRENT_DATE)";
            
            $stmtMensual = $this->db->prepare($sqlMensual);
            $stmtMensual->execute();
            $resultadoMensual = $stmtMensual->fetch(PDO::FETCH_ASSOC);

            // Total de estudiantes activos
            $sqlEstudiantes = "SELECT COUNT(*) AS total FROM estudiantes WHERE estado = 'activo'";
            $stmtEstudiantes = $this->db->prepare($sqlEstudiantes);
            $stmtEstudiantes->execute();
            $totalEstudiantes = $stmtEstudiantes->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Total de padres activos
            $sqlPadres = "SELECT COUNT(*) AS total FROM padres WHERE estado = 'activo'";
            $stmtPadres = $this->db->prepare($sqlPadres);
            $stmtPadres->execute();
            $totalPadres = $stmtPadres->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Total de pagos por estado
            $sqlPagos = "SELECT estado, COUNT(*) AS total, SUM(monto) AS monto_total 
                         FROM pagos 
                         GROUP BY estado";
            $stmtPagos = $this->db->prepare($sqlPagos);
            $stmtPagos->execute();
            $datosPagos = $stmtPagos->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'total_estudiantes' => $totalEstudiantes,
                'total_padres' => $totalPadres,
                'total_ingresos' => $resultadoMensual['total_ingresos'] ?? 0,
                'total_pendientes' => $resultadoMensual['total_pendientes'] ?? 0,
                'total_deudas' => $resultadoMensual['total_deudas'] ?? 0,
                'pagos' => $datosPagos
            ];
        } catch (Exception $e) {
            error_log("Error en ReporteModel::obtenerEstadisticasGenerales: " . $e->getMessage());
            return [
                'total_estudiantes' => 0,
                'total_padres' => 0,
                'total_ingresos' => 0,
                'total_pendientes' => 0,
                'total_deudas' => 0,
                'pagos' => []
            ];
        }
    }
    
    /**
     * Exporta datos a formato CSV
     * 
     * @param array $datos Los datos a exportar
     * @param string $nombreArchivo El nombre del archivo a generar
     * @return string Ruta del archivo generado
     */
    public function exportarCSV($datos, $nombreArchivo) {
        try {
            if (empty($datos)) {
                throw new Exception("No hay datos para exportar");
            }
            
            $rutaArchivo = __DIR__ . '/../temp/' . $nombreArchivo . '.csv';
            
            // Crear directorio si no existe
            if (!file_exists(__DIR__ . '/../temp/')) {
                mkdir(__DIR__ . '/../temp/', 0755, true);
            }
            
            $archivo = fopen($rutaArchivo, 'w');
            
            // Escribir encabezados
            fputcsv($archivo, array_keys($datos[0]));
            
            // Escribir datos
            foreach ($datos as $fila) {
                fputcsv($archivo, $fila);
            }
            
            fclose($archivo);
            
            return $rutaArchivo;
        } catch (Exception $e) {
            return false;
        }
    }
}
