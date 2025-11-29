<?php
/**
 * Modelo para la gestión de configuración del sistema
 */
namespace Models;

require_once __DIR__ . '/../core/Modelo.php';

use Core\Modelo;
use \Exception;
use \PDO;

class ConfiguracionModel extends Modelo {
    
    /**
     * Constructor de la clase
     */
    public function __construct() {
        parent::__construct('configuraciones', 'id_configuracion');
        $this->allowedFields = [
            'clave',
            'valor',
            'descripcion',
            'tipo',
            'actualizado_por'
        ];
    }
    
    /**
     * Obtiene un valor de configuración por su clave
     * 
     * @param string $clave La clave del valor a obtener
     * @return string|null El valor de la configuración o null si no existe
     */
    public function obtenerValor($clave) {
        try {
            $sql = "SELECT valor FROM configuraciones WHERE clave = :clave";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':clave', $clave);
            $stmt->execute();

            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

            return $resultado ? $resultado['valor'] : null;
        } catch (\Exception $e) {
            error_log('ConfiguracionModel::obtenerValor - error: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Establece un valor de configuración
     * 
     * @param string $clave La clave de la configuración
     * @param string $valor El valor a establecer
     * @param string $descripcion Descripción opcional de la configuración
     * @param string $tipo El tipo de dato (texto, numero, booleano, json)
     * @param int $actualizadoPor ID del usuario que realiza la actualización
     * @return bool True en caso de éxito, false en caso contrario
     */
    public function establecerValor($clave, $valor, $descripcion = '', $tipo = 'texto', $actualizadoPor = null) {
        try {
            $sql = "SELECT id_configuracion FROM configuraciones WHERE clave = :clave";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':clave', $clave);
            $stmt->execute();
            
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($resultado) {
                // Actualizar configuración existente
                $datos = [
                    'valor' => $valor,
                    'actualizado_por' => $actualizadoPor,
                    'actualizado' => date('Y-m-d H:i:s')
                ];
                
                if (!empty($descripcion)) {
                    $datos['descripcion'] = $descripcion;
                }
                
                if (!empty($tipo)) {
                    $datos['tipo'] = $tipo;
                }
                
                return $this->actualizar($resultado['id_configuracion'], $datos);
            } else {
                // Crear nueva configuración
                $datos = [
                    'clave' => $clave,
                    'valor' => $valor,
                    'descripcion' => $descripcion,
                    'tipo' => $tipo,
                    'actualizado_por' => $actualizadoPor,
                    'creado' => date('Y-m-d H:i:s'),
                    'actualizado' => date('Y-m-d H:i:s')
                ];
                
                return $this->insertar($datos);
            }
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Obtiene las configuraciones del sistema por categoría
     * 
     * @param string $categoria Categoría de configuraciones (opcional)
     * @return array Lista de configuraciones
     */
    public function obtenerConfiguraciones($categoria = null) {
        $sql = "SELECT c.*, u.nombre as usuario_nombre 
                FROM configuraciones c
                LEFT JOIN usuarios u ON c.actualizado_por = u.id_usuario";
                
        if ($categoria) {
            $sql .= " WHERE c.clave LIKE :categoria";
            $stmt = $this->db->prepare($sql);
            $categoriaParam = $categoria . '%';
            $stmt->bindParam(':categoria', $categoriaParam);
        } else {
            $stmt = $this->db->prepare($sql);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
