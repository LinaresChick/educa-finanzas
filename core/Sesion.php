<?php
namespace Core;

class Sesion {
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    public function get($key) {
        return $_SESSION[$key] ?? null;
    }
    
    public function set($key, $value) {
        $_SESSION[$key] = $value;
    }
    
    public function has($key) {
        return isset($_SESSION[$key]);
    }
    
    public function remove($key) {
        unset($_SESSION[$key]);
    }
    
    public function destroy() {
        session_destroy();
    }
    
    public function setFlash($tipo, $mensaje) {
        $_SESSION['flash'] = [
            'tipo' => $tipo,
            'mensaje' => $mensaje
        ];
    }
    
    public function getFlash() {
        if (isset($_SESSION['flash'])) {
            $flash = $_SESSION['flash'];
            unset($_SESSION['flash']);
            return $flash;
        }
        return null;
    }
    
    public function tieneRol($roles) {
        if (!isset($_SESSION['usuario']) || !isset($_SESSION['usuario']['rol'])) {
            return false;
        }
        
        if (is_array($roles)) {
            return in_array($_SESSION['usuario']['rol'], $roles);
        }
        
        return $_SESSION['usuario']['rol'] === $roles;
    }
    
    public function estaLogueado() {
        return isset($_SESSION['usuario']);
    }
}
?>