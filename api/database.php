<?php
// api/database.php
class Database {
    private static $instance = null;
    private $db = null;
    
    private function __construct() {
        $dbDir = __DIR__ . '/../db';
        $dbPath = $dbDir . '/association.db';
        
        // Create database directory if it doesn't exist
        if (!file_exists($dbDir)) {
            mkdir($dbDir, 0755, true);
        }
        
        $this->db = new PDO('sqlite:' . $dbPath);
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->db->exec('PRAGMA foreign_keys = ON');
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->db;
    }
    
    public function exec($sql) {
        return $this->db->exec($sql);
    }
    
    public function prepare($statement) {
        return $this->db->prepare($statement);
    }
    
    public function lastInsertId() {
        return $this->db->lastInsertId();
    }
}