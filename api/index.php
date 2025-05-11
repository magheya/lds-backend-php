<?php
// api/index.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// At the top of your index.php file
// Set allowed origins
$allowedOrigins = [
    'http://localhost:8000',    // Your frontend dev server
    'http://localhost:3000',    // Alternative frontend port
    'http://127.0.0.1:8000',    // Alternative localhost notation
    'http://127.0.0.1:3000',
    'https://yourdomain.com'    // Add your production domain
];

$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';

if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: $origin");
    header('Access-Control-Allow-Credentials: true');
} else {
    // Default for requests without origin (like Postman)
    // Note: Using * won't work with credentials
    header('Access-Control-Allow-Origin: *');
}

// Add these additional headers
header('Content-Type: application/json');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once 'database.php';
require_once 'data-access.php';
require_once 'upload-handler.php';
require_once 'auth.php';
require_once 'init.php';

initializeDatabase();

// Create instances
$dataAccess = new DataAccess();
$uploadHandler = new UploadHandler();
$auth = new Auth();

// Add admin authentication check function
function requireAdminAuth() {
    global $auth;
    $authResult = $auth->checkAuth();
    if (!$authResult) {
        http_response_code(401);
        echo json_encode(['error' => 'Authentication required']);
        exit;
    }
    return $authResult;
}

// Parse the URL path
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = ltrim($path, '/');
$parts = explode('/', $path);

// Remove the first part if it's "api"
if (isset($parts[0]) && $parts[0] === 'api') {
    array_shift($parts);
}

// Determine the resource type and ID (if present)
$resourceType = $parts[0] ?? '';
$id = $parts[1] ?? null;
$action = $parts[2] ?? null;

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

// Get request body for POST/PUT
$inputData = json_decode(file_get_contents('php://input'), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    $inputData = $_POST;
}

// Process file uploads
$uploadedFile = null;
if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
    try {
        $uploadedFilePath = $uploadHandler->handleUpload($_FILES['image']);
        // Add the file path to the input data
        $inputData['image'] = $uploadedFilePath;
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['error' => $e->getMessage()]);
        exit;
    }
}

// Convert sizes if they are JSON string
if (isset($inputData['sizes']) && is_string($inputData['sizes'])) {
    $inputData['sizes'] = json_decode($inputData['sizes'], true);
}

// Process the request
try {
    $response = null;
    
    switch ($resourceType) {
        case 'test':
            $response = ['message' => 'API is working!'];
            break;
            
        case 'products':
            if ($method === 'GET') {
                if ($id === 'category' && isset($parts[2])) {
                    $response = $dataAccess->getProductsByCategory($parts[2]);
                } else {
                    $response = $dataAccess->getAllProducts();
                }
            } elseif ($method === 'POST') {
                // Add admin authentication protection
                requireAdminAuth();
                $response = $dataAccess->addProduct($inputData);
                http_response_code(201);
            } elseif ($method === 'PUT' && $id !== null) {
                // Add admin authentication protection
                requireAdminAuth();
                $response = ['success' => $dataAccess->updateProduct($id, $inputData)];
            } elseif ($method === 'DELETE' && $id !== null) {
                // Add admin authentication protection
                requireAdminAuth();
                $response = ['success' => $dataAccess->deleteProduct($id)];
            }
            break;
            
        case 'events':
            if ($method === 'GET') {
                if ($id === 'type' && isset($parts[2])) {
                    $response = $dataAccess->getEventsByType($parts[2]);
                } else {
                    $response = $dataAccess->getAllEvents();
                }
            } elseif ($method === 'POST') {
                // Add admin authentication protection
                requireAdminAuth();
                $response = $dataAccess->addEvent($inputData);
                http_response_code(201);
            } elseif ($method === 'PUT' && $id !== null) {
                // Add admin authentication protection
                requireAdminAuth();
                $response = ['success' => $dataAccess->updateEvent($id, $inputData)];
            } elseif ($method === 'DELETE' && $id !== null) {
                // Add admin authentication protection
                requireAdminAuth();
                $response = ['success' => $dataAccess->deleteEvent($id)];
            }
            break;
            
        case 'registrations':
            if ($method === 'GET') {
                // Add admin authentication protection for viewing all registrations
                requireAdminAuth();
                
                if ($id === 'event' && isset($parts[2])) {
                    $response = $dataAccess->getRegistrationsByEvent($parts[2]);
                } else {
                    $response = $dataAccess->getAllRegistrations();
                }
            } elseif ($method === 'POST') {
                // Public access for submitting registrations is fine
                $response = $dataAccess->saveRegistration($inputData);
                http_response_code(201);
            } elseif ($method === 'DELETE' && $id !== null) {
                // Add admin authentication protection
                requireAdminAuth();
                $response = ['success' => $dataAccess->deleteRegistration($id)];
            }
            break;
            
        case 'donations':
            if ($method === 'GET') {
                // Add admin authentication protection for viewing donations
                requireAdminAuth();
                
                if ($id === 'type' && isset($parts[2])) {
                    $response = $dataAccess->getDonationsByType($parts[2]);
                } else {
                    $response = $dataAccess->getAllDonations();
                }
            } elseif ($method === 'POST') {
                // Public access for submitting donations is fine
                $response = $dataAccess->saveDonation($inputData);
                http_response_code(201);
            }
            break;
            
        case 'messages':
            if ($method === 'GET') {
                // Add admin authentication protection for viewing messages
                requireAdminAuth();
                
                if ($id === 'unread') {
                    $response = $dataAccess->getUnreadMessages();
                } elseif ($id !== null) {
                    $response = $dataAccess->getMessageById($id);
                    if (!$response) {
                        http_response_code(404);
                        $response = ['error' => 'Message not found'];
                    }
                } else {
                    $response = $dataAccess->getAllMessages();
                }
            } elseif ($method === 'POST') {
                // Public access for submitting messages is fine
                $response = $dataAccess->saveMessage($inputData);
                http_response_code(201);
            } elseif ($method === 'PUT' && $id !== null && $action === 'read') {
                // Add admin authentication protection
                requireAdminAuth();
                $response = ['success' => $dataAccess->markMessageAsRead($id)];
            } elseif ($method === 'DELETE' && $id !== null) {
                // Add admin authentication protection
                requireAdminAuth();
                $response = ['success' => $dataAccess->deleteMessage($id)];
            }
            break;
            
        case 'orders':
            if ($method === 'GET') {
                // Add admin authentication protection for viewing orders
                requireAdminAuth();
                
                if ($id !== null) {
                    $response = $dataAccess->getOrderById($id);
                    if (!$response) {
                        http_response_code(404);
                        $response = ['error' => 'Order not found'];
                    }
                } else {
                    $response = $dataAccess->getAllOrders();
                }
            } elseif ($method === 'POST') {
                // Public access for submitting orders is fine
                $response = $dataAccess->saveOrder($inputData);
                http_response_code(201);
            } elseif ($method === 'PUT' && $id !== null && $action === 'status') {
                // Add admin authentication protection
                requireAdminAuth();
                $response = ['success' => $dataAccess->updateOrderStatus($id, $inputData['status'])];
            } elseif ($method === 'DELETE' && $id !== null) {
                // Add admin authentication protection
                requireAdminAuth();
                $response = ['success' => $dataAccess->deleteOrder($id)];
            }
            break;
            
        case 'activities':
            if ($method === 'GET') {
                // Add admin authentication protection
                requireAdminAuth();
                $response = $dataAccess->getRecentActivities();
            }
            break;
            
        case 'admin':
            if ($id === 'login' && $method === 'POST') {
                $result = $auth->login($inputData['username'], $inputData['password']);
                if ($result) {
                    $response = $result;
                } else {
                    http_response_code(401);
                    $response = ['error' => 'Invalid credentials'];
                }
            } elseif ($id === 'check-auth' && $method === 'GET') {
                $result = $auth->checkAuth();
                if ($result) {
                    $response = $result;
                } else {
                    http_response_code(401);
                    $response = ['error' => 'Not authenticated'];
                }
            } elseif ($id === 'logout' && $method === 'POST') {
                // Ensure user is logged in before logging out
                requireAdminAuth();
                $response = ['success' => $auth->logout()];
            }
            break;
            
        default:
            http_response_code(404);
            $response = ['error' => 'Resource not found'];
    }
    
    echo json_encode($response);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}