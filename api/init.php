<?php
// api/init.php
require_once 'database.php';

// Helper function to check if a column exists - moved outside other functions
function columnExists($table, $column) {
    $db = Database::getInstance();
    $stmt = $db->prepare("PRAGMA table_info($table)");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($columns as $col) {
        if ($col['name'] === $column) {
            return true;
        }
    }
    
    return false;
}

// Helper function to add a column if it doesn't exist - moved outside other functions
function addColumnIfNotExists($table, $column, $columnDef) {
    $db = Database::getInstance();
    if (!columnExists($table, $column)) {
        try {
            $db->exec("ALTER TABLE $table ADD COLUMN $column $columnDef");
            echo "Added column $column to table $table\n";
        } catch (Exception $e) {
            echo "Error adding column $column to table $table: " . $e->getMessage() . "\n";
        }
    }
}

function initSchema() {
    $db = Database::getInstance();
    
    // Check if products table exists
    $stmt = $db->prepare("SELECT name FROM sqlite_master WHERE type='table' AND name='products'");
    $stmt->execute();
    $tableExists = $stmt->fetchColumn();
    
    // If tables exist, just ensure they have all required columns
    if ($tableExists) {
        ensureColumnsExist();
        return;
    }
    
    // For first-time initialization, create all tables
    $db->exec("
    CREATE TABLE IF NOT EXISTS products (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        price REAL NOT NULL,
        description TEXT,
        image TEXT,
        category TEXT,
        stock INTEGER DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );
    
    CREATE TABLE IF NOT EXISTS product_sizes (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        product_id INTEGER NOT NULL,
        size TEXT NOT NULL,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
    );
    
    CREATE TABLE IF NOT EXISTS events (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        date TEXT NOT NULL,
        description TEXT,
        image TEXT,
        type TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );
    
    CREATE TABLE IF NOT EXISTS registrations (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        event_id INTEGER NOT NULL,
        name TEXT NOT NULL,
        email TEXT NOT NULL,
        phone TEXT,
        timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
    );
    
    CREATE TABLE IF NOT EXISTS donations (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        type TEXT NOT NULL,
        amount REAL,
        name TEXT NOT NULL,
        email TEXT NOT NULL,
        description TEXT,
        timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );
    
    CREATE TABLE IF NOT EXISTS messages (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        email TEXT NOT NULL,
        subject TEXT,
        message TEXT NOT NULL,
        read INTEGER DEFAULT 0,
        timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );
    
    CREATE TABLE IF NOT EXISTS orders (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        customer_name TEXT NOT NULL,
        customer_email TEXT NOT NULL,
        total REAL NOT NULL,
        status TEXT DEFAULT 'pending',
        timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );
    
    CREATE TABLE IF NOT EXISTS order_items (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        order_id INTEGER NOT NULL,
        product_id INTEGER NOT NULL,
        size TEXT,
        quantity INTEGER NOT NULL,
        price REAL NOT NULL,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
    );
    
    CREATE TABLE IF NOT EXISTS admins (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT UNIQUE NOT NULL,
        password TEXT NOT NULL,
        role TEXT DEFAULT 'admin',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );
    ");
    
    // Create admin user if not exists
    setupAdminUser();
    
    echo "Database schema initialized for the first time\n";
}

function setupAdminUser() {
    $db = Database::getInstance();
    
    // Check if admin user exists
    $stmt = $db->prepare("SELECT * FROM admins WHERE username = ?");
    $stmt->execute(['admin']);
    $adminUser = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$adminUser) {
        // Create default admin user with password 'admin123'
        $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $db->prepare("INSERT INTO admins (username, password, role) VALUES (?, ?, ?)");
        $stmt->execute(['admin', $hashedPassword, 'admin']);
        echo "Default admin user created\n";
    }
}

function ensureColumnsExist() {
    // We no longer need to define helper functions here
    // Just use the ones defined globally above
    
    // Add timestamp columns to tables that need them
    addColumnIfNotExists('registrations', 'timestamp', "TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
    addColumnIfNotExists('donations', 'timestamp', "TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
    addColumnIfNotExists('messages', 'timestamp', "TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
    addColumnIfNotExists('messages', 'read', "INTEGER DEFAULT 0");
    addColumnIfNotExists('orders', 'timestamp', "TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
    addColumnIfNotExists('orders', 'status', "TEXT DEFAULT 'pending'");
}

function initializeDatabase() {
    initSchema();
}

// Only run initialization if this file is called directly
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    initializeDatabase();
}