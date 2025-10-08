<?php
namespace Models;

require_once __DIR__ . '/../core/Modelo.php';
require_once __DIR__ . '/../models/PagoModel.php';
require_once __DIR__ . '/../models/EstudianteModel.php';

use Core\Modelo;

class ReporteModel extends Modelo 
{
    protected $db;

    public function __construct() 
    {
        parent::__construct('reportes', 'id_reporte');
    }

    protected function validarFechas($fechaInicio, $fechaFin) 
    {
        if (empty($fechaInicio) || empty($fechaFin)) {
            return false;
        }

        $inicio = strtotime($fechaInicio);
        $fin = strtotime($fechaFin);

        if (!$inicio || !$fin) {
            return false;
        }

        return $inicio <= $fin;
    }

    public function generarReporteFinanciero($fechaInicio, $fechaFin) 
    {
        try {
            if (!$this->validarFechas($fechaInicio, $fechaFin)) {
                return ['error' => 'Las fechas proporcionadas no son válidas'];
            }

            $sql = "SELECT 
                    COALESCE(DATE_FORMAT(fecha_pago, '%Y-%m'), 'Sin Fecha') AS periodo,
                    COUNT(1) as total_pagos,
                    COALESCE(SUM(monto), 0) as total_ingresos,
                    COUNT(DISTINCT id_estudiante) as estudiantes_pagaron,
                    COALESCE(SUM(CASE WHEN LOWER(metodo_pago) = 'efectivo' THEN monto ELSE 0 END), 0) as monto_efectivo,
                    COALESCE(SUM(CASE WHEN LOWER(metodo_pago) = 'transferencia' THEN monto ELSE 0 END), 0) as monto_transferencia,
                    COALESCE(SUM(CASE WHEN LOWER(metodo_pago) = 'tarjeta' THEN monto ELSE 0 END), 0) as monto_tarjeta,
                    COUNT(CASE WHEN LOWER(concepto) LIKE '%matric%' THEN 1 END) as pagos_matricula,
                    COALESCE(SUM(CASE WHEN LOWER(concepto) LIKE '%matric%' THEN monto ELSE 0 END), 0) as monto_matricula,
                    COUNT(CASE WHEN LOWER(concepto) LIKE '%mensual%' THEN 1 END) as pagos_mensualidad,
                    COALESCE(SUM(CASE WHEN LOWER(concepto) LIKE '%mensual%' THEN monto ELSE 0 END), 0) as monto_mensualidad
                FROM pagos
                WHERE fecha_pago BETWEEN :fecha_inicio AND :fecha_fin
                AND LOWER(estado) = 'completado'
                GROUP BY DATE_FORMAT(fecha_pago, '%Y-%m')
                ORDER BY periodo DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':fecha_inicio', $fechaInicio);
            $stmt->bindParam(':fecha_fin', $fechaFin);
            $stmt->execute();
            
            $periodos = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            $estadisticas = $this->calcularEstadisticasPeriodo($fechaInicio, $fechaFin);
            
            return [
                'periodos' => $periodos,
                'estadisticas' => $estadisticas
            ];
        } catch (\PDOException $e) {
            error_log('Error PDO al generar reporte financiero: ' . $e->getMessage() . ' - SQL: ' . $sql);
            throw new \Exception('Error en la consulta de base de datos al generar el reporte financiero: ' . $e->getMessage());
        } catch (\Exception $e) {
            error_log('Error general al generar reporte financiero: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            throw new \Exception('Error al generar el reporte financiero: ' . $e->getMessage());
        }
    }

    private function calcularEstadisticasPeriodo($fechaInicio, $fechaFin) 
    {
        try {
            $sql = "SELECT 
                    COALESCE(SUM(monto), 0) as total_periodo,
                    COUNT(1) as total_transacciones,
                    COUNT(DISTINCT id_estudiante) as total_estudiantes,
                    COALESCE(AVG(monto), 0) as promedio_transaccion,
                    COALESCE(MIN(monto), 0) as min_transaccion,
                    COALESCE(MAX(monto), 0) as max_transaccion,
                    COALESCE(SUM(CASE WHEN LOWER(metodo_pago) = 'efectivo' THEN monto ELSE 0 END), 0) as total_efectivo,
                    COALESCE(SUM(CASE WHEN LOWER(metodo_pago) = 'transferencia' THEN monto ELSE 0 END), 0) as total_transferencia,
                    COALESCE(SUM(CASE WHEN LOWER(metodo_pago) = 'tarjeta' THEN monto ELSE 0 END), 0) as total_tarjeta
                FROM pagos
                WHERE fecha_pago BETWEEN :fecha_inicio AND :fecha_fin
                AND LOWER(estado) = 'completado'";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':fecha_inicio', $fechaInicio);
            $stmt->bindParam(':fecha_fin', $fechaFin);
            $stmt->execute();
            
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log('Error PDO al calcular estadísticas: ' . $e->getMessage() . ' - SQL: ' . $sql);
            throw new \Exception('Error en la consulta de base de datos al calcular estadísticas: ' . $e->getMessage());
        } catch (\Exception $e) {
            error_log('Error general al calcular estadísticas: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            throw new \Exception('Error al calcular estadísticas: ' . $e->getMessage());
        }
    }

    public function generarReporteDeudas() 
    {
        try {
            $sql = "SELECT e.id_estudiante, 
                        e.nombres, 
                        e.apellidos, 
                        e.dni, 
                        s.nombre AS salon, 
                        s.grado, 
                        s.nivel,
                        SUM(CASE WHEN p.estado = 'pendiente' THEN p.monto ELSE 0 END) AS total_deuda,
                        COUNT(CASE WHEN p.estado = 'pendiente' THEN 1 END) AS pagos_pendientes,
                        MAX(p.fecha_vencimiento) AS proxima_fecha_vencimiento
                    FROM estudiantes e
                    LEFT JOIN pagos p ON e.id_estudiante = p.id_estudiante
                    LEFT JOIN salones s ON e.id_salon = s.id_salon
                    GROUP BY e.id_estudiante, e.nombres, e.apellidos, e.dni, s.nombre, s.grado, s.nivel
                    HAVING total_deuda > 0
                    ORDER BY total_deuda DESC";
                    
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log('Error al generar reporte de deudas: ' . $e->getMessage());
            throw new \Exception('Error al generar el reporte de deudas');
        }
    }

    public function obtenerEstadisticasGenerales() 
    {
        try {
            $sql = "SELECT 
                    SUM(CASE WHEN estado = 'completado' THEN monto ELSE 0 END) as total_ingresos,
                    COUNT(1) as total_pagos,
                    COUNT(CASE WHEN estado = 'completado' THEN 1 END) as pagos_completados,
                    COUNT(CASE WHEN estado = 'pendiente' THEN 1 END) as pagos_pendientes,
                    SUM(CASE WHEN estado = 'pendiente' THEN monto ELSE 0 END) as total_deuda,
                    COALESCE(AVG(CASE WHEN estado = 'completado' THEN monto END), 0) as promedio_pago
                FROM pagos
                WHERE DATE(fecha_pago) BETWEEN DATE_FORMAT(NOW(), '%Y-%m-01')
                AND LAST_DAY(NOW())";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $estadisticas = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            // Total de estudiantes activos
            $sqlEstudiantes = "SELECT COUNT(1) AS total FROM estudiantes WHERE estado = 'activo'";
            $stmtEstudiantes = $this->db->prepare($sqlEstudiantes);
            $stmtEstudiantes->execute();
            $estadisticas['total_estudiantes'] = $stmtEstudiantes->fetch(\PDO::FETCH_ASSOC)['total'];
            
            // Total de padres activos
            $sqlPadres = "SELECT COUNT(1) AS total FROM padres WHERE estado = 'activo'";
            $stmtPadres = $this->db->prepare($sqlPadres);
            $stmtPadres->execute();
            $estadisticas['total_padres'] = $stmtPadres->fetch(\PDO::FETCH_ASSOC)['total'];
            
            if (!$estadisticas) {
                throw new \Exception('Error al obtener las estadísticas');
            }
            
            return $estadisticas;
        } catch (\Exception $e) {
            error_log('Error al obtener estadísticas generales: ' . $e->getMessage());
            throw new \Exception('Error al obtener las estadísticas generales');
        }
    }
    
    public function exportarCSV($datos, $nombreArchivo) 
    {
        try {
            if (empty($datos)) {
                throw new \Exception("No hay datos para exportar");
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
        } catch (\Exception $e) {
            error_log('Error al exportar a CSV: ' . $e->getMessage());
            return false;
        }
    }
}
