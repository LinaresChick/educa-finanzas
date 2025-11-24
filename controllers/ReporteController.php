<?php
namespace Controllers;

require_once __DIR__ . '/../core/BaseController.php';
require_once __DIR__ . '/../models/ReporteModel.php';
require_once __DIR__ . '/../models/PagoModel.php';
require_once __DIR__ . '/../models/EstudianteModel.php';

use Core\BaseController;

use Models\ReporteModel;
use Models\PagoModel;
use Models\EstudianteModel;
use \Exception;

class ReporteController extends BaseController {
    private $reporteModel;
    private $pagoModel;
    private $estudianteModel;
    
    public function __construct() {
        parent::__construct();
        $this->reporteModel = new ReporteModel();
        $this->pagoModel = new PagoModel();
        $this->estudianteModel = new EstudianteModel();
    }
    
    /**
     * Muestra la página principal de reportes
     */
    public function index() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['usuario']) || !in_array($_SESSION['usuario']['rol'], ['Superadmin', 'Administrador', 'Secretario', 'Contador'])) {
            header("Location: index.php?controller=Auth&action=acceso_denegado");
            exit();
        }
        
        // Obtener fechas para el reporte
        $fechaFin = date('Y-m-d');
        $fechaInicio = date('Y-m-d', strtotime('-12 months'));
        
        // Obtener reporte financiero y estadísticas
        $reporteFinanciero = $this->reporteModel->generarReporteFinanciero($fechaInicio, $fechaFin);
        
        // Datos para mostrar en la vista
        $datos = [
            'titulo' => 'Reportes del Sistema',
            'estadisticas' => $this->reporteModel->obtenerEstadisticasGenerales(),
            'reporte' => $reporteFinanciero['periodos'] ?? [],
            'resumen' => $reporteFinanciero['estadisticas'] ?? [],
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin
        ];
        
        $this->render("reportes/financiero", $datos);
    }
    
    /**
     * Muestra el reporte de deudas
     */
    public function deudas() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['usuario']) || !in_array($_SESSION['usuario']['rol'], ['Superadmin', 'Administrador', 'Colaborador', 'Secretario', 'Contador'])) {
            header("Location: index.php?controller=Auth&action=acceso_denegado");
            exit();
        }
        
        // Generar reporte de deudas
        $deudas = $this->reporteModel->generarReporteDeudas();
        
        // Datos para mostrar en la vista
        $datos = [
            'titulo' => 'Reporte de Deudas',
            'deudas' => $deudas
        ];
        
        $this->render("reportes/deudas", $datos);
    }
    
    /**
     * Muestra el reporte financiero
     */
    public function financiero() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['usuario']) || !in_array($_SESSION['usuario']['rol'], ['Superadmin', 'Administrador', 'Secretario', 'Contador'])) {
            header("Location: index.php?controller=Auth&action=acceso_denegado");
            exit();
        }
        
        // Parámetros de periodo
        $periodo = isset($_GET['periodo']) ? $_GET['periodo'] : 'mensual';
        
        // Calcular fechas según el periodo seleccionado
        switch ($periodo) {
            case 'semanal':
                $fechaInicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : date('Y-m-d', strtotime('-7 days'));
                $fechaFin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : date('Y-m-d');
                break;
            case 'anual':
                $fechaInicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : date('Y-01-01');
                $fechaFin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : date('Y-12-31');
                break;
            case 'mensual':
            default:
                $fechaInicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : date('Y-m-01');
                $fechaFin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : date('Y-m-t');
                break;
        }
        
        // Filtros adicionales
        $filtros = [];
        if (!empty($_GET['id_seccion'])) {
            $filtros['id_seccion'] = $_GET['id_seccion'];
        }
        if (!empty($_GET['grado'])) {
            $filtros['grado'] = $_GET['grado'];
        }
        if (!empty($_GET['nivel'])) {
            $filtros['nivel'] = $_GET['nivel'];
        }
        
        // Generar reporte financiero
        $reporteFinanciero = $this->reporteModel->generarReporteFinanciero($fechaInicio, $fechaFin, $periodo, $filtros);
        
        // Obtener listas para filtros
        $secciones = $this->reporteModel->obtenerSecciones();
        $grados = $this->reporteModel->obtenerGrados();
        $niveles = $this->reporteModel->obtenerNiveles();
        
        // Datos para mostrar en la vista
        $datos = [
            'titulo' => 'Reporte Financiero',
            'reporte' => $reporteFinanciero['periodos'] ?? [],
            'estadisticas' => $reporteFinanciero['estadisticas'] ?? [],
            'fechaInicio' => $fechaInicio,
            'fechaFin' => $fechaFin,
            'periodo' => $periodo,
            'secciones' => $secciones,
            'grados' => $grados,
            'niveles' => $niveles,
            'filtros' => $filtros
        ];
        
        $this->render("reportes/financiero", $datos);
    }
    
    /**
     * Exporta reportes en formato CSV
     */
    public function exportar() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['usuario']) || !in_array($_SESSION['usuario']['rol'], ['Superadmin', 'Administrador', 'Secretario', 'Contador'])) {
            header("Location: index.php?controller=Auth&action=acceso_denegado");
            exit();
        }
        
        $tipo = isset($_GET['tipo']) ? $_GET['tipo'] : null;
        $fechaInicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : date('Y-m-01');
        $fechaFin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : date('Y-m-t');
        $periodo = isset($_GET['periodo']) ? $_GET['periodo'] : 'mensual';
        
        // Filtros
        $filtros = [];
        if (!empty($_GET['id_seccion'])) {
            $filtros['id_seccion'] = $_GET['id_seccion'];
        }
        if (!empty($_GET['grado'])) {
            $filtros['grado'] = $_GET['grado'];
        }
        if (!empty($_GET['nivel'])) {
            $filtros['nivel'] = $_GET['nivel'];
        }
        
        $datos = [];
        $nombreArchivo = '';
        
        try {
            switch ($tipo) {
                case 'deudas':
                    $datos = $this->reporteModel->generarReporteDeudas();
                    $nombreArchivo = 'reporte_deudas_' . date('Ymd');
                    break;
                    
                case 'financiero':
                    $resultado = $this->reporteModel->generarReporteFinanciero($fechaInicio, $fechaFin, $periodo, $filtros);
                    $datos = $resultado['periodos'] ?? [];
                    $nombreArchivo = 'reporte_financiero_' . $periodo . '_' . date('Ymd');
                    break;
                    
                case 'pagos':
                    $datos = $this->pagoModel->obtenerPagosFiltrados([
                        'fecha_inicio' => $fechaInicio,
                        'fecha_fin' => $fechaFin
                    ]);
                    $nombreArchivo = 'reporte_pagos_' . date('Ymd');
                    break;
                    
                case 'estudiantes':
                    $datos = $this->estudianteModel->obtenerEstudiantesActivos();
                    $nombreArchivo = 'reporte_estudiantes_' . date('Ymd');
                    break;
                    
                default:
                    throw new Exception("Tipo de reporte no válido");
            }
            
            if (empty($datos)) {
                throw new Exception("No hay datos para exportar");
            }
            
            $rutaArchivo = $this->reporteModel->exportarCSV($datos, $nombreArchivo);
            
            if ($rutaArchivo) {
                // Preparar para descarga
                header('Content-Type: text/csv; charset=utf-8');
                header('Content-Disposition: attachment; filename="' . $nombreArchivo . '.csv"');
                
                readfile($rutaArchivo);
                
                // Eliminar archivo temporal
                @unlink($rutaArchivo);
                exit();
            } else {
                throw new Exception("Error al generar el archivo CSV");
            }
            
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            header("Location: index.php?controller=Reporte&action=financiero");
            exit();
        }
    }
}
