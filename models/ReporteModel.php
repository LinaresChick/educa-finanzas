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
                    COALESCE(DATE_FORMAT(p.fecha_pago, '%Y-%m'), 'Sin Fecha') AS periodo,
                    COUNT(1) as total_pagos,
                    COALESCE(SUM(p.monto), 0) as total_ingresos,
                    COUNT(DISTINCT p.id_estudiante) as estudiantes_pagaron,
                    COALESCE(SUM(CASE WHEN p.metodo_pago = 'efectivo' THEN p.monto ELSE 0 END), 0) as monto_efectivo,
                    COALESCE(SUM(CASE WHEN p.metodo_pago = 'transferencia' THEN p.monto ELSE 0 END), 0) as monto_transferencia,
                    COALESCE(SUM(CASE WHEN p.metodo_pago = 'tarjeta' THEN p.monto ELSE 0 END), 0) as monto_tarjeta,
                    COUNT(DISTINCT p.banco) as total_bancos,
                    COALESCE(SUM(CASE WHEN LOWER(p.concepto) LIKE '%mensualidad%' THEN p.monto ELSE 0 END), 0) as monto_mensualidad,
                    COALESCE(SUM(CASE WHEN LOWER(p.concepto) LIKE '%matric%' THEN p.monto ELSE 0 END), 0) as monto_matricula,
                    COALESCE(SUM(CASE WHEN LOWER(p.concepto) LIKE '%material%' THEN p.monto ELSE 0 END), 0) as monto_material,
                    COALESCE(SUM(CASE WHEN LOWER(p.concepto) LIKE '%uniforme%' THEN p.monto ELSE 0 END), 0) as monto_uniforme,
                    COALESCE(SUM(CASE WHEN LOWER(p.concepto) LIKE '%actividad%' THEN p.monto ELSE 0 END), 0) as monto_actividad,
                    COALESCE(SUM(CASE 
                        WHEN LOWER(p.concepto) NOT LIKE '%mensualidad%'
                        AND LOWER(p.concepto) NOT LIKE '%matric%'
                        AND LOWER(p.concepto) NOT LIKE '%material%'
                        AND LOWER(p.concepto) NOT LIKE '%uniforme%'
                        AND LOWER(p.concepto) NOT LIKE '%actividad%'
                        THEN p.monto ELSE 0 END), 0) as monto_otro,
                    COUNT(CASE WHEN LOWER(p.concepto) LIKE '%mensual%' THEN 1 END) as pagos_mensualidad
                FROM pagos p
                WHERE p.fecha_pago BETWEEN :fecha_inicio AND :fecha_fin
                GROUP BY DATE_FORMAT(p.fecha_pago, '%Y-%m')
                HAVING periodo IS NOT NULL
                ORDER BY periodo DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':fecha_inicio', $fechaInicio);
            $stmt->bindParam(':fecha_fin', $fechaFin);
            $stmt->execute();
            
            $periodos = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Si no hay periodos, crear un array vacío para evitar el error null
            if (empty($periodos)) {
                $periodos = [
                    [
                        'periodo' => date('Y-m', strtotime($fechaInicio)),
                        'total_pagos' => 0,
                        'total_ingresos' => 0,
                        'estudiantes_pagaron' => 0,
                        'monto_efectivo' => 0,
                        'monto_transferencia' => 0,
                        'monto_tarjeta' => 0,
                        'total_bancos' => 0,
                        'monto_mensualidad' => 0,
                        'monto_matricula' => 0,
                        'monto_material' => 0,
                        'monto_uniforme' => 0,
                        'monto_actividad' => 0,
                        'monto_otro' => 0,
                        'pagos_mensualidad' => 0
                    ]
                ];
            }
            
            $estadisticas = $this->calcularEstadisticasPeriodo($fechaInicio, $fechaFin);

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
                    COALESCE(SUM(p.monto), 0) as total_periodo,
                    COUNT(1) as total_transacciones,
                    COUNT(DISTINCT p.id_estudiante) as total_estudiantes,
                    COUNT(DISTINCT p.banco) as total_bancos_usados,
                    COUNT(DISTINCT p.usuario_registro) as total_usuarios_registro,
                    COALESCE(AVG(p.monto), 0) as promedio_transaccion,
                    COALESCE(MIN(p.monto), 0) as min_transaccion,
                    COALESCE(MAX(p.monto), 0) as max_transaccion,
                    COALESCE(SUM(CASE WHEN LOWER(p.metodo_pago) = 'efectivo' THEN p.monto ELSE 0 END), 0) as total_efectivo,
                    COALESCE(SUM(CASE WHEN LOWER(p.metodo_pago) = 'transferencia' THEN p.monto ELSE 0 END), 0) as total_transferencia,
                    COALESCE(SUM(CASE WHEN LOWER(p.metodo_pago) = 'tarjeta' THEN p.monto ELSE 0 END), 0) as total_tarjeta
                FROM pagos p
                JOIN estudiantes e ON p.id_estudiante = e.id_estudiante
                WHERE p.fecha_pago BETWEEN :fecha_inicio AND :fecha_fin
                AND e.estado_pago = 'pagado'";
            
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
                        e.monto AS total_deuda,
                        CASE WHEN e.estado_pago = 'pendiente' THEN 1 ELSE 0 END AS pagos_pendientes,
                        e.fecha_vencimiento AS proxima_fecha_vencimiento
                    FROM estudiantes e
                    LEFT JOIN salones s ON e.id_salon = s.id_salon
                    WHERE e.estado_pago = 'pendiente'
                    GROUP BY e.id_estudiante, e.nombres, e.apellidos, e.dni, s.nombre, s.grado, s.nivel, e.monto, e.estado_pago, e.fecha_vencimiento
                    HAVING e.monto > 0
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
                    SUM(CASE WHEN e.estado_pago = 'pagado' THEN p.monto ELSE 0 END) as total_ingresos,
                    COUNT(p.id_pago) as total_pagos,
                    COUNT(CASE WHEN e.estado_pago = 'pagado' THEN 1 END) as pagos_completados,
                    COUNT(CASE WHEN e.estado_pago = 'pendiente' THEN 1 END) as pagos_pendientes,
                    SUM(CASE WHEN e.estado_pago = 'pendiente' THEN e.monto ELSE 0 END) as total_deuda,
                    COALESCE(AVG(CASE WHEN e.estado_pago = 'pagado' THEN p.monto END), 0) as promedio_pago
                FROM pagos p
                JOIN estudiantes e ON p.id_estudiante = e.id_estudiante
                WHERE DATE(p.fecha_pago) BETWEEN DATE_FORMAT(NOW(), '%Y-%m-01')
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
