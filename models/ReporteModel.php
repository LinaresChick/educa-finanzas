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

    public function generarReporteFinanciero($fechaInicio, $fechaFin, $periodo = 'mensual', $filtros = []) 
    {
        try {
            if (!$this->validarFechas($fechaInicio, $fechaFin)) {
                return ['error' => 'Las fechas proporcionadas no son válidas'];
            }

            // Determinar el formato de agrupación según el periodo
            $formatoPeriodo = match($periodo) {
                'semanal' => '%Y-%u',  // Año-Semana
                'mensual' => '%Y-%m',   // Año-Mes
                'anual' => '%Y',        // Año
                default => '%Y-%m'
            };

            $etiquetaPeriodo = match($periodo) {
                'semanal' => "CONCAT('Semana ', WEEK(p.fecha_pago), ' - ', YEAR(p.fecha_pago))",
                'mensual' => "DATE_FORMAT(p.fecha_pago, '%M %Y')",
                'anual' => "YEAR(p.fecha_pago)",
                default => "DATE_FORMAT(p.fecha_pago, '%M %Y')"
            };

            // Construir WHERE con filtros
            $whereConditions = ["p.fecha_pago BETWEEN :fecha_inicio AND :fecha_fin"];
            $params = [
                ':fecha_inicio' => $fechaInicio,
                ':fecha_fin' => $fechaFin
            ];

            if (!empty($filtros['id_seccion'])) {
                $whereConditions[] = "s.id_seccion = :id_seccion";
                $params[':id_seccion'] = $filtros['id_seccion'];
            }

            if (!empty($filtros['grado'])) {
                $whereConditions[] = "s.grado = :grado";
                $params[':grado'] = $filtros['grado'];
            }

            if (!empty($filtros['nivel'])) {
                $whereConditions[] = "s.nivel = :nivel";
                $params[':nivel'] = $filtros['nivel'];
            }

            $whereClause = implode(' AND ', $whereConditions);

            $sql = "SELECT 
                    DATE_FORMAT(p.fecha_pago, '$formatoPeriodo') AS periodo_key,
                    $etiquetaPeriodo AS periodo,
                    COUNT(1) as total_pagos,
                    COALESCE(SUM(p.monto + COALESCE(p.aumento, 0) - COALESCE(p.descuento, 0)), 0) as total_ingresos,
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
                    COUNT(CASE WHEN LOWER(p.concepto) LIKE '%mensual%' THEN 1 END) as pagos_mensualidad,
                    COUNT(CASE WHEN LOWER(p.concepto) LIKE '%matric%' THEN 1 END) as pagos_matricula
                FROM pagos p
                LEFT JOIN estudiantes e ON p.id_estudiante = e.id_estudiante
                LEFT JOIN secciones s ON e.id_salon = s.id_seccion
                WHERE $whereClause
                GROUP BY DATE_FORMAT(p.fecha_pago, '$formatoPeriodo')
                ORDER BY periodo_key DESC";

            $stmt = $this->db->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            
            $periodos = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Si no hay periodos, crear un array vacío
            if (empty($periodos)) {
                $periodos = [];
            }
            
            $estadisticas = $this->calcularEstadisticasPeriodo($fechaInicio, $fechaFin, $filtros);
            
            return [
                'periodos' => $periodos,
                'estadisticas' => $estadisticas
            ];
        } catch (\PDOException $e) {
            error_log('Error PDO al generar reporte financiero: ' . $e->getMessage());
            throw new \Exception('Error en la consulta de base de datos al generar el reporte financiero: ' . $e->getMessage());
        } catch (\Exception $e) {
            error_log('Error general al generar reporte financiero: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            throw new \Exception('Error al generar el reporte financiero: ' . $e->getMessage());
        }
    }

    private function calcularEstadisticasPeriodo($fechaInicio, $fechaFin, $filtros = []) 
    {
        try {
            $whereConditions = ["p.fecha_pago BETWEEN :fecha_inicio AND :fecha_fin"];
            $params = [
                ':fecha_inicio' => $fechaInicio,
                ':fecha_fin' => $fechaFin
            ];

            if (!empty($filtros['id_seccion'])) {
                $whereConditions[] = "s.id_seccion = :id_seccion";
                $params[':id_seccion'] = $filtros['id_seccion'];
            }

            if (!empty($filtros['grado'])) {
                $whereConditions[] = "s.grado = :grado";
                $params[':grado'] = $filtros['grado'];
            }

            if (!empty($filtros['nivel'])) {
                $whereConditions[] = "s.nivel = :nivel";
                $params[':nivel'] = $filtros['nivel'];
            }

            $whereClause = implode(' AND ', $whereConditions);

            $sql = "SELECT 
                    COALESCE(SUM(p.monto + COALESCE(p.aumento, 0) - COALESCE(p.descuento, 0)), 0) as total_periodo,
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
                LEFT JOIN estudiantes e ON p.id_estudiante = e.id_estudiante
                LEFT JOIN secciones s ON e.id_salon = s.id_seccion
                WHERE $whereClause";
            
            $stmt = $this->db->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log('Error PDO al calcular estadísticas: ' . $e->getMessage());
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
            
            // UTF-8 BOM para Excel
            fprintf($archivo, chr(0xEF).chr(0xBB).chr(0xBF));
            
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

    public function obtenerSecciones()
    {
        try {
            $sql = "SELECT DISTINCT id_seccion, nombre, grado, nivel 
                    FROM secciones 
                    ORDER BY nivel, grado, nombre";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log('Error al obtener secciones: ' . $e->getMessage());
            return [];
        }
    }

    public function obtenerGrados()
    {
        try {
            $sql = "SELECT DISTINCT grado FROM secciones WHERE grado IS NOT NULL ORDER BY grado";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_COLUMN);
        } catch (\Exception $e) {
            error_log('Error al obtener grados: ' . $e->getMessage());
            return [];
        }
    }

    public function obtenerNiveles()
    {
        try {
            $sql = "SELECT DISTINCT nivel FROM secciones WHERE nivel IS NOT NULL ORDER BY nivel";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_COLUMN);
        } catch (\Exception $e) {
            error_log('Error al obtener niveles: ' . $e->getMessage());
            return [];
        }
    }
}
