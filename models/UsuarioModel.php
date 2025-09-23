<?php
namespace Models;

require_once __DIR__ . '/../config/database.php';

use Database;
use PDO;

class UsuarioModel {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function obtenerPorCorreo($correo) {
        $sql = "SELECT * FROM usuarios WHERE correo = :correo LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':correo', $correo);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function crear($data) {
        $sql = "INSERT INTO usuarios (nombre, correo, `contraseña`, rol, estado) 
                VALUES (:nombre, :correo, :contraseña, :rol, :estado)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':nombre' => $data['nombre'],
            ':correo' => $data['correo'],
            ':contraseña' => password_hash($data['contraseña'], PASSWORD_BCRYPT, ['cost' => 12]),
            ':rol' => $data['rol'],
            ':estado' => $data['estado']
        ]);
    }
}
