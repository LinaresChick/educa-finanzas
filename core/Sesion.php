<?php
class Sesion {
    public static function iniciar() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    public static function set($key, $value) {
        $_SESSION[$key] = $value;
    }
    
    public static function get($key) {
        return $_SESSION[$key] ?? null;
    }
    
    public static function destroy() {
        session_destroy();
    }
    
    public static function estaLogueado() {
        return isset($_SESSION['usuario_id']);
    }
    
    public static function getRol() {
        return $_SESSION['usuario_rol'] ?? null;
    }
}
?>