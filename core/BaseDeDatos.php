<?php
namespace Core;

require_once __DIR__ . '/../config/database.php';

class BaseDeDatos {
    protected $db;
    
    public function __construct() {
        $this->db = \Database::getConnection();
    }
}
?>
