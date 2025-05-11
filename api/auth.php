<?php
// api/auth.php
require_once 'database.php';


class Auth {
    private $db;
    private $tokenExpiration = 86400; // 24 hours
    private $debug = true; // Set to false in production
    
    public function __construct() {
        $this->db = Database::getInstance();
        
        // Create tokens table if it doesn't exist
        $this->createTokensTableIfNotExists();
        $this->debugLog("Auth class initialized");
    }
    
    /**
     * Log debug messages to the error log
     */
    private function debugLog($message, $data = null) {
        if ($this->debug) {
            $logMessage = "[Auth Debug] $message";
            if ($data !== null) {
                $logMessage .= ": " . print_r($data, true);
            }
            error_log($logMessage);
        }
    }
    
    /**
     * Create tokens table if it doesn't exist
     */
    private function createTokensTableIfNotExists() {
        $query = "CREATE TABLE IF NOT EXISTS auth_tokens (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            token VARCHAR(255) NOT NULL,
            expires_at DATETIME NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES admins (id) ON DELETE CASCADE
        )";
        
        $this->db->exec($query);
        $this->debugLog("Token table check completed");
    }
    
    /**
     * Authenticate a user and return a token
     * 
     * @param string $username The username
     * @param string $password The password
     * @return array|bool User data with token or false on failure
     */
    public function login($username, $password) {
        $this->debugLog("Login attempt", ['username' => $username]);
        
        $stmt = $this->db->prepare("SELECT * FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            $this->debugLog("Login failed: User not found");
            return false;
        }
        
        if (!password_verify($password, $user['password'])) {
            $this->debugLog("Login failed: Invalid password");
            return false;
        }
        
        // Generate a secure token
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', time() + $this->tokenExpiration);
        
        // Store the token in the database
        $tokenStmt = $this->db->prepare("INSERT INTO auth_tokens (user_id, token, expires_at) VALUES (?, ?, ?)");
        $tokenStmt->execute([$user['id'], $token, $expiresAt]);
        
        $this->debugLog("Login successful", [
            'user_id' => $user['id'],
            'username' => $user['username'],
            'token_length' => strlen($token),
            'expires_at' => $expiresAt
        ]);
        
        // Return user data with token (excluding password)
        return [
            'user_id' => $user['id'],
            'username' => $user['username'],
            'role' => $user['role'],
            'token' => $token,
            'expires_at' => $expiresAt
        ];
    }
    
    /**
     * Check if a user is authenticated by token
     * 
     * @return array|bool User data or false if not authenticated
     */
    public function checkAuth() {
        // Get token from Authorization header
        $token = $this->getTokenFromHeader();
        
        if (!$token) {
            $this->debugLog("Auth check failed: No token in header");
            return false;
        }
        
        $this->debugLog("Checking token", ['token_prefix' => substr($token, 0, 8) . '...']);
        
        // Verify token in database
        $stmt = $this->db->prepare("
            SELECT t.*, a.username, a.role 
            FROM auth_tokens t
            JOIN admins a ON t.user_id = a.id
            WHERE t.token = ? AND t.expires_at > CURRENT_TIMESTAMP
        ");
        $stmt->execute([$token]);
        $tokenData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$tokenData) {
            $this->debugLog("Auth check failed: Invalid or expired token");
            
            // Check if token exists but expired
            $expiredCheck = $this->db->prepare("SELECT expires_at FROM auth_tokens WHERE token = ?");
            $expiredCheck->execute([$token]);
            $expiredData = $expiredCheck->fetch(PDO::FETCH_ASSOC);
            
            if ($expiredData) {
                $this->debugLog("Token expired", ['expires_at' => $expiredData['expires_at'], 'current' => date('Y-m-d H:i:s')]);
            } else {
                $this->debugLog("Token not found in database");
            }
            
            return false;
        }
        
        $this->debugLog("Auth check successful", [
            'user_id' => $tokenData['user_id'],
            'username' => $tokenData['username'],
            'role' => $tokenData['role']
        ]);
        
        // Return user data
        return [
            'user_id' => $tokenData['user_id'],
            'username' => $tokenData['username'],
            'role' => $tokenData['role'],
            'token' => $tokenData['token']
        ];
    }
    
    /**
     * Get token from Authorization header
     * 
     * @return string|null Token or null if not found
     */
    private function getTokenFromHeader() {
        // Get headers - cross-platform approach
        $headers = function_exists('getallheaders') ? getallheaders() : $this->getRequestHeaders();
        
        $this->debugLog("Request headers", $headers);
        
        // Check Authorization header
        $authHeader = null;
        if (isset($headers['Authorization'])) {
            $authHeader = $headers['Authorization'];
        } elseif (isset($headers['authorization'])) {
            $authHeader = $headers['authorization'];
        } elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
        }
        
        if (!$authHeader) {
            $this->debugLog("No Authorization header found");
            return null;
        }
        
        $this->debugLog("Authorization header", ['header' => $authHeader]);
        
        // Check if it's a Bearer token
        if (strpos($authHeader, 'Bearer ') === 0) {
            $token = substr($authHeader, 7);
            $this->debugLog("Bearer token extracted", ['token_prefix' => substr($token, 0, 8) . '...']);
            return $token;
        }
        
        $this->debugLog("Invalid Authorization header format (not Bearer)");
        return null;
    }
    
    /**
     * Helper function to get headers if getallheaders doesn't exist
     * 
     * @return array Headers
     */
    private function getRequestHeaders() {
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (substr($key, 0, 5) === 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($key, 5)))))] = $value;
            }
        }
        $this->debugLog("Headers from SERVER", $headers);
        return $headers;
    }
    
    /**
     * Log out a user by invalidating their token
     * 
     * @return bool Success status
     */
    public function logout() {
        $token = $this->getTokenFromHeader();
        
        if (!$token) {
            $this->debugLog("Logout failed: No token in header");
            return false;
        }
        
        $this->debugLog("Attempting to logout token", ['token_prefix' => substr($token, 0, 8) . '...']);
        
        // Delete the token
        $stmt = $this->db->prepare("DELETE FROM auth_tokens WHERE token = ?");
        $stmt->execute([$token]);
        
        $rowCount = $stmt->rowCount();
        $this->debugLog("Logout result", ['tokens_deleted' => $rowCount]);
        
        return $rowCount > 0;
    }
    
    /**
     * Clean up expired tokens
     * 
     * @return int Number of tokens deleted
     */
    public function cleanupExpiredTokens() {
        $this->debugLog("Cleaning up expired tokens");
        
        $stmt = $this->db->prepare("DELETE FROM auth_tokens WHERE expires_at < CURRENT_TIMESTAMP");
        $stmt->execute();
        
        $rowCount = $stmt->rowCount();
        $this->debugLog("Cleanup result", ['tokens_deleted' => $rowCount]);
        
        return $rowCount;
    }
    
    /**
     * Invalidate all tokens for a user
     * 
     * @param int $userId User ID
     * @return bool Success status
     */
    public function invalidateAllTokensForUser($userId) {
        $this->debugLog("Invalidating all tokens for user", ['user_id' => $userId]);
        
        $stmt = $this->db->prepare("DELETE FROM auth_tokens WHERE user_id = ?");
        $stmt->execute([$userId]);
        
        $rowCount = $stmt->rowCount();
        $this->debugLog("Invalidation result", ['tokens_deleted' => $rowCount]);
        
        return $rowCount > 0;
    }
}