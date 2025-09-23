<?php
class BaseDeDatos {
    protected $db;
    
    public function __construct() {
        $this->db = Database::getConnection();
    }
}
?>