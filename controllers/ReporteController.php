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
        
        // Parámetros de fecha (por defecto el mes actual)
        $fechaInicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : date('Y-m-01');
        $fechaFin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : date('Y-m-t');
        
        // Generar reporte financiero
        $reporteFinanciero = $this->reporteModel->generarReporteFinanciero($fechaInicio, $fechaFin);
        
        // Estadísticas generales
        $estadisticas = $this->reporteModel->obtenerEstadisticasGenerales();
        
        // Datos para mostrar en la vista
        $datos = [
            'titulo' => 'Reporte Financiero',
            'reporte' => $reporteFinanciero,
            'estadisticas' => $estadisticas,
            'fechaInicio' => $fechaInicio,
            'fechaFin' => $fechaFin
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
        
        $datos = [];
        $nombreArchivo = '';
        
        try {
            switch ($tipo) {
                case 'deudas':
                    $datos = $this->reporteModel->generarReporteDeudas();
                    $nombreArchivo = 'reporte_deudas_' . date('Ymd');
                    break;
                    
                case 'financiero':
                    $datos = $this->reporteModel->generarReporteFinanciero($fechaInicio, $fechaFin);
                    $nombreArchivo = 'reporte_financiero_' . date('Ymd');
                    break;
                    
                case 'pagos':
                    $datos = $this->pagoModel->generarReportePagos($fechaInicio, $fechaFin);
                    $nombreArchivo = 'reporte_pagos_' . date('Ymd');
                    break;
                    
                case 'estudiantes':
                    $datos = $this->estudianteModel->obtenerTodos();
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
                exit();
            } else {
                throw new Exception("Error al generar el archivo CSV");
            }
            
        } catch (Exception $e) {
            $datos = [
                'error' => $e->getMessage()
            ];
            
            $this->render("reportes/exportar", $datos);
        }
    }
}
