<?php
/**
 * Clase base para todos los modelos del sistema
 * Proporciona métodos comunes para operaciones CRUD
 */
namespace Core;

require_once __DIR__ . '/BaseDeDatos.php';

class Modelo extends BaseDeDatos {
    protected $tabla;
    protected $primaryKey;
    protected $allowedFields = [];
    
    /**
     * Constructor
     * 
     * @param string $tabla El nombre de la tabla en la base de datos
     * @param string $primaryKey La clave primaria de la tabla
     */
    public function __construct($tabla, $primaryKey = 'id') {
        parent::__construct();
        $this->tabla = $tabla;
        $this->primaryKey = $primaryKey;
    }
    
    /**
     * Obtiene todos los registros de la tabla
     * 
     * @param array $columnas Las columnas a seleccionar
     * @param array $condiciones Las condiciones WHERE en formato clave-valor
     * @param string $orden El orden de los resultados
     * @param int $limite El límite de registros a obtener
     * @param int $offset El offset para la paginación
     * @return array Los registros encontrados
     */
    public function obtenerTodos($columnas = ['*'], $condiciones = [], $orden = '', $limite = null, $offset = null) {
        $sql = "SELECT " . implode(', ', $columnas) . " FROM {$this->tabla}";
        
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
     * Busca un registro por su clave primaria
     * 
     * @param int|string $id El valor de la clave primaria
     * @return array|false El registro encontrado o false si no existe
     */
    public function buscarPorId($id) {
        $sql = "SELECT * FROM {$this->tabla} WHERE {$this->primaryKey} = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }
    
    /**
     * Busca registros según condiciones específicas
     * 
     * @param array $condiciones Las condiciones WHERE en formato clave-valor
     * @param array $columnas Las columnas a seleccionar
     * @return array Los registros encontrados
     */
    public function buscar($condiciones, $columnas = ['*']) {
        $sql = "SELECT " . implode(', ', $columnas) . " FROM {$this->tabla}";
        
        // Agregar condiciones WHERE
        if (!empty($condiciones)) {
            $sql .= " WHERE ";
            $where = [];
            foreach ($condiciones as $campo => $valor) {
                $where[] = "{$campo} = :{$campo}";
            }
            $sql .= implode(' AND ', $where);
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
     * Inserta un nuevo registro en la tabla
     * 
     * @param array $datos Los datos a insertar en formato clave-valor
     * @return int El ID del registro insertado
     */
    public function insertar($datos) {
        // Filtrar los datos permitidos
        $datos = $this->filtrarDatos($datos);
        
        $campos = array_keys($datos);
        $placeholders = array_map(function($campo) {
            return ":{$campo}";
        }, $campos);
        
        $sql = "INSERT INTO {$this->tabla} (" . implode(', ', $campos) . ") 
                VALUES (" . implode(', ', $placeholders) . ")";
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($datos as $campo => $valor) {
            $stmt->bindValue(":{$campo}", $valor);
        }
        
        $stmt->execute();
        return $this->db->lastInsertId();
    }
    
    /**
     * Actualiza un registro existente
     * 
     * @param int|string $id El ID del registro a actualizar
     * @param array $datos Los datos a actualizar en formato clave-valor
     * @return bool True si la actualización fue exitosa
     */
    public function actualizar($id, $datos) {
        // Filtrar los datos permitidos
        $datos = $this->filtrarDatos($datos);
        
        $sets = [];
        foreach ($datos as $campo => $valor) {
            $sets[] = "{$campo} = :{$campo}";
        }
        
        $sql = "UPDATE {$this->tabla} SET " . implode(', ', $sets) . " 
                WHERE {$this->primaryKey} = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id);
        
        foreach ($datos as $campo => $valor) {
            $stmt->bindValue(":{$campo}", $valor);
        }
        
        return $stmt->execute();
    }
    
    /**
     * Elimina un registro de la tabla
     * 
     * @param int|string $id El ID del registro a eliminar
     * @return bool True si la eliminación fue exitosa
     */
    public function eliminar($id) {
        $sql = "DELETE FROM {$this->tabla} WHERE {$this->primaryKey} = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id);
        return $stmt->execute();
    }
    
    /**
     * Filtra los datos permitidos según la propiedad $allowedFields
     * 
     * @param array $datos Los datos a filtrar
     * @return array Los datos filtrados
     */
    protected function filtrarDatos($datos) {
        if (empty($this->allowedFields)) {
            return $datos;
        }
        
        return array_intersect_key($datos, array_flip($this->allowedFields));
    }
    
    /**
     * Realiza una consulta personalizada
     * 
     * @param string $sql La consulta SQL
     * @param array $params Los parámetros para la consulta
     * @return array Los registros encontrados
     */
    public function consultaPersonalizada($sql, $params = []) {
        $stmt = $this->db->prepare($sql);
        
        foreach ($params as $param => $valor) {
            $stmt->bindValue(":{$param}", $valor);
        }
        
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Cuenta el número de registros que cumplen con las condiciones
     * 
     * @param array $condiciones Las condiciones WHERE en formato clave-valor
     * @return int El número de registros
     */
    public function contar($condiciones = []) {
        $sql = "SELECT COUNT(*) as total FROM {$this->tabla}";
        
        // Agregar condiciones WHERE
        if (!empty($condiciones)) {
            $sql .= " WHERE ";
            $where = [];
            foreach ($condiciones as $campo => $valor) {
                $where[] = "{$campo} = :{$campo}";
            }
            $sql .= implode(' AND ', $where);
        }
        
        $stmt = $this->db->prepare($sql);
        
        // Vincular los valores de las condiciones
        if (!empty($condiciones)) {
            foreach ($condiciones as $campo => $valor) {
                $stmt->bindValue(":{$campo}", $valor);
            }
        }
        
        $stmt->execute();
        $resultado = $stmt->fetch();
        return $resultado['total'];
    }
}
