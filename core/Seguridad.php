<?php
namespace Core;

/**
 * Clase helper para sanitización y validación de entrada
 */
class Seguridad {
    
    /**
     * Limpia un string de caracteres peligrosos para prevenir XSS
     */
    public static function limpiarString($input) {
        if (is_array($input)) {
            return array_map([self::class, 'limpiarString'], $input);
        }
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Valida un email
     */
    public static function validarEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Valida un entero
     */
    public static function validarEntero($valor) {
        return filter_var($valor, FILTER_VALIDATE_INT) !== false;
    }
    
    /**
     * Valida un float/decimal
     */
    public static function validarDecimal($valor) {
        return filter_var($valor, FILTER_VALIDATE_FLOAT) !== false;
    }
    
    /**
     * Limpia un nombre de archivo para prevenir directory traversal
     */
    public static function limpiarNombreArchivo($filename) {
        $filename = basename($filename);
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);
        return $filename;
    }
    
    /**
     * Valida una fecha en formato Y-m-d
     */
    public static function validarFecha($fecha) {
        $d = \DateTime::createFromFormat('Y-m-d', $fecha);
        return $d && $d->format('Y-m-d') === $fecha;
    }
    
    /**
     * Genera un hash seguro para contraseñas
     */
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }
    
    /**
     * Verifica una contraseña contra un hash
     */
    public static function verificarPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    /**
     * Limpia y valida un DNI (8 dígitos)
     */
    public static function validarDNI($dni) {
        $dni = preg_replace('/[^0-9]/', '', $dni);
        return strlen($dni) === 8 && ctype_digit($dni);
    }
    
    /**
     * Previene SQL injection asegurando que solo se usen prepared statements
     * Esta función no debe ser usada directamente; es un recordatorio
     */
    public static function recordatorioPreparedStatements() {
        // IMPORTANTE: Siempre usar prepared statements en PDO
        // Ejemplo correcto:
        // $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
        // $stmt->execute(['id' => $id]);
        return "Usar siempre prepared statements con PDO";
    }
}
