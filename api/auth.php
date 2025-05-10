<?php
// api/auth.php
require_once 'database.php';

class Auth {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
        
        // Start a session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        // Set secure session parameters
        ini_set('session.cookie_httponly', 1);
        ini_set('session.use_only_cookies', 1);
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            ini_set('session.cookie_secure', 1);
        }
        session_start();
    }
    }
    
    /**
     * Authenticate a user and return a token
     * 
     * @param string $username The username
     * @param string $password The password
     * @return array|bool User data with token or false on failure
     */
    public function login($username, $password) {
        $stmt = $this->db->prepare("SELECT * FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user || !password_verify($password, $user['password'])) {
            return false;
        }
        
        // Generate a secure token
        $token = bin2hex(random_bytes(32));
        
        // Store the token in the session
        $_SESSION['user'] = [
            'id' => $user['id'],
            'username' => $user['username'],
            'role' => $user['role'],
            'token' => $token
        ];
        
        // Return user data with token (excluding password)
        return [
            'username' => $user['username'],
            'role' => $user['role'],
            'token' => $token
        ];
    }
    
    /**
     * Check if a user is authenticated
     * 
     * @return array|bool User data or false if not authenticated
     */
public function checkAuth() {
    // Check if user is logged in via session
    if (isset($_SESSION['user'])) {
        return $_SESSION['user'];
    }
    
    // Get headers - cross-platform approach
    $headers = function_exists('getallheaders') ? getallheaders() : $this->getRequestHeaders();
    
    // Check Authorization header
    $authHeader = null;
    if (isset($headers['Authorization'])) {
        $authHeader = $headers['Authorization'];
    } elseif (isset($headers['authorization'])) {
        $authHeader = $headers['authorization'];
    } elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
    }
    
    // Check if it's a Bearer token
    if ($authHeader && strpos($authHeader, 'Bearer ') === 0) {
        $token = substr($authHeader, 7);
        
        // Verify the token matches the one in session
        if (isset($_SESSION['user']) && $_SESSION['user']['token'] === $token) {
            return $_SESSION['user'];
        }
    }
    
    return false;
}

// Helper function to get headers if getallheaders doesn't exist
private function getRequestHeaders() {
    $headers = [];
    foreach ($_SERVER as $key => $value) {
        if (substr($key, 0, 5) === 'HTTP_') {
            $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($key, 5)))))] = $value;
        }
    }
    return $headers;
}
    
    /**
     * Log out a user
     * 
     * @return bool Success status
     */
    public function logout() {
        unset($_SESSION['user']);
        session_destroy();
        
        return true;
    }
    
    /**
     * Get the current user
     * 
     * @return array|null User data or null if not logged in
     */
    public function getCurrentUser() {
        return isset($_SESSION['user']) ? $_SESSION['user'] : null;
    }
}