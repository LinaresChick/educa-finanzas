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
    // En core/Sesion.php - REEMPLAZAR el método tieneRol()
public function tieneRol($roles) {
    if (!isset($_SESSION['usuario'])) {
        return false;
    }
    
    // Verificar si es array o objeto
    $rolUsuario = is_array($_SESSION['usuario']) 
        ? ($_SESSION['usuario']['rol'] ?? null)
        : ($_SESSION['usuario']->rol ?? null);
    
    if (!$rolUsuario) {
        return false;
    }
    
    // Normalizar comparación en minúsculas para evitar problemas de casing
    $rolUsuarioLower = strtolower($rolUsuario);

    if (is_array($roles)) {
        $rolesLower = array_map('strtolower', $roles);
        return in_array($rolUsuarioLower, $rolesLower);
    }

    return $rolUsuarioLower === strtolower($roles);
}

// AGREGAR este método nuevo
public function usuarioAutenticado() {
    return isset($_SESSION['usuario']);
}
    
    public function estaLogueado() {
        return isset($_SESSION['usuario']);
    }
}
?>