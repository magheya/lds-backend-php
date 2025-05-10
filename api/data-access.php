<?php
// api/data-access.php
require_once 'database.php';

class DataAccess {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    // Product functions
    public function getAllProducts() {
        $stmt = $this->db->prepare("SELECT * FROM products");
        $stmt->execute();
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get sizes for each product
        foreach ($products as &$product) {
            $sizeStmt = $this->db->prepare("SELECT size FROM product_sizes WHERE product_id = ?");
            $sizeStmt->execute([$product['id']]);
            $sizes = $sizeStmt->fetchAll(PDO::FETCH_COLUMN);
            $product['sizes'] = $sizes;
        }
        
        return $products;
    }
    
    public function getProductsByCategory($category) {
        $stmt = $this->db->prepare("SELECT * FROM products WHERE category = ?");
        $stmt->execute([$category]);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get sizes for each product
        foreach ($products as &$product) {
            $sizeStmt = $this->db->prepare("SELECT size FROM product_sizes WHERE product_id = ?");
            $sizeStmt->execute([$product['id']]);
            $sizes = $sizeStmt->fetchAll(PDO::FETCH_COLUMN);
            $product['sizes'] = $sizes;
        }
        
        return $products;
    }
    
    public function addProduct($product) {
        try {
            $conn = $this->db->getConnection();
            $conn->beginTransaction();
            
            $stmt = $conn->prepare("INSERT INTO products (name, price, description, image, category, stock) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $product['name'],
                $product['price'],
                $product['description'] ?? '',
                $product['image'] ?? '',
                $product['category'] ?? '',
                $product['stock'] ?? 0
            ]);
            
            $productId = $conn->lastInsertId();
            
            if (isset($product['sizes']) && is_array($product['sizes']) && !empty($product['sizes'])) {
                $sizeStmt = $conn->prepare("INSERT INTO product_sizes (product_id, size) VALUES (?, ?)");
                foreach ($product['sizes'] as $size) {
                    $sizeStmt->execute([$productId, $size]);
                }
            }
            
            $conn->commit();
            
            $product['id'] = $productId;
            return $product;
        } catch (Exception $e) {
            $conn->rollBack();
            throw $e;
        }
    }

/**
 * Update a product
 * 
 * @param int $id The product ID
 * @param array $product The updated product data
 * @return bool Success status
 */
public function updateProduct($id, $product) {
    try {
        $conn = $this->db->getConnection();
        $conn->beginTransaction();
        
        // Update product fields
        $fields = [];
        $params = [];
        
        if (isset($product['name'])) {
            $fields[] = "name = ?";
            $params[] = $product['name'];
        }
        
        if (isset($product['price'])) {
            $fields[] = "price = ?";
            $params[] = $product['price'];
        }
        
        if (isset($product['description'])) {
            $fields[] = "description = ?";
            $params[] = $product['description'];
        }
        
        if (isset($product['category'])) {
            $fields[] = "category = ?";
            $params[] = $product['category'];
        }
        
        if (isset($product['stock'])) {
            $fields[] = "stock = ?";
            $params[] = $product['stock'];
        }
        
        if (isset($product['image'])) {
            $fields[] = "image = ?";
            $params[] = $product['image'];
        }
        
        // Only update if there are fields to update
        if (!empty($fields)) {
            $sql = "UPDATE products SET " . implode(", ", $fields) . " WHERE id = ?";
            $params[] = $id;
            
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
        }
        
        // Update sizes if provided
        if (isset($product['sizes']) && is_array($product['sizes'])) {
            // Delete existing sizes
            $deleteStmt = $conn->prepare("DELETE FROM product_sizes WHERE product_id = ?");
            $deleteStmt->execute([$id]);
            
            // Add new sizes
            if (!empty($product['sizes'])) {
                $sizeStmt = $conn->prepare("INSERT INTO product_sizes (product_id, size) VALUES (?, ?)");
                foreach ($product['sizes'] as $size) {
                    $sizeStmt->execute([$id, $size]);
                }
            }
        }
        
        $conn->commit();
        return true;
    } catch (Exception $e) {
        $conn->rollBack();
        throw $e;
    }
}
    /**
 * Delete a product by ID
 * 
 * @param int $id The product ID to delete
 * @return bool Success status
 */
public function deleteProduct($id) {
    try {
        $conn = $this->db->getConnection();
        $conn->beginTransaction();
        
        // First delete related sizes (due to foreign key constraints)
        $sizeStmt = $conn->prepare("DELETE FROM product_sizes WHERE product_id = ?");
        $sizeStmt->execute([$id]);
        
        // Then delete the product
        $productStmt = $conn->prepare("DELETE FROM products WHERE id = ?");
        $productStmt->execute([$id]);
        
        $conn->commit();
        return true;
    } catch (Exception $e) {
        $conn->rollBack();
        throw $e;
    }
}
    // Event functions
    public function getAllEvents() {
        $stmt = $this->db->prepare("SELECT * FROM events");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getEventsByType($type) {
        $stmt = $this->db->prepare("SELECT * FROM events WHERE type = ?");
        $stmt->execute([$type]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function addEvent($event) {
        $stmt = $this->db->prepare("INSERT INTO events (name, date, description, image, type) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $event['name'],
            $event['date'],
            $event['description'] ?? '',
            $event['image'] ?? '',
            $event['type'] ?? ''
        ]);
        
        $event['id'] = $this->db->lastInsertId();
        return $event;
    }
    
    // Order functions
    public function saveOrder($order) {
        try {
            $conn = $this->db->getConnection();
            $conn->beginTransaction();
            
            $stmt = $conn->prepare("INSERT INTO orders (customer_name, customer_email, total, status) VALUES (?, ?, ?, ?)");
            $stmt->execute([
                $order['customer_name'],
                $order['customer_email'],
                $order['total'],
                $order['status'] ?? 'pending'
            ]);
            
            $orderId = $conn->lastInsertId();
            
            $itemStmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, size, quantity, price) VALUES (?, ?, ?, ?, ?)");
            foreach ($order['items'] as $item) {
                $itemStmt->execute([
                    $orderId,
                    $item['id'] ?? $item['product_id'],
                    $item['size'] ?? null,
                    $item['quantity'],
                    $item['price']
                ]);
            }
            
            $conn->commit();
            
            $order['id'] = $orderId;
            return $order;
        } catch (Exception $e) {
            $conn->rollBack();
            throw $e;
        }
    }
    
    // Registration functions
    public function saveRegistration($registration) {
        $stmt = $this->db->prepare("INSERT INTO registrations (event_id, name, email, phone) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            $registration['event_id'],
            $registration['name'],
            $registration['email'],
            $registration['phone'] ?? null
        ]);
        
        $registration['id'] = $this->db->lastInsertId();
        return $registration;
    }
    
    public function getAllRegistrations() {
        $stmt = $this->db->prepare("SELECT * FROM registrations ORDER BY timestamp DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getRegistrationsByEvent($eventId) {
        $stmt = $this->db->prepare("SELECT * FROM registrations WHERE event_id = ? ORDER BY timestamp DESC");
        $stmt->execute([$eventId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Donation functions
    public function saveDonation($donation) {
        $stmt = $this->db->prepare("INSERT INTO donations (type, amount, name, email, description) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $donation['type'],
            $donation['amount'],
            $donation['name'],
            $donation['email'],
            $donation['description'] ?? null
        ]);
        
        $donation['id'] = $this->db->lastInsertId();
        return $donation;
    }
    
    public function getAllDonations() {
        $stmt = $this->db->prepare("SELECT * FROM donations ORDER BY timestamp DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getDonationsByType($type) {
        $stmt = $this->db->prepare("SELECT * FROM donations WHERE type = ? ORDER BY timestamp DESC");
        $stmt->execute([$type]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Message functions
    public function saveMessage($message) {
        $stmt = $this->db->prepare("INSERT INTO messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            $message['name'],
            $message['email'],
            $message['subject'] ?? '',
            $message['message']
        ]);
        
        $message['id'] = $this->db->lastInsertId();
        return $message;
    }
    
    public function getAllMessages() {
        $stmt = $this->db->prepare("SELECT * FROM messages ORDER BY timestamp DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getUnreadMessages() {
        $stmt = $this->db->prepare("SELECT * FROM messages WHERE read = 0 ORDER BY timestamp DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getMessageById($id) {
        $stmt = $this->db->prepare("SELECT * FROM messages WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function markMessageAsRead($id) {
        $stmt = $this->db->prepare("UPDATE messages SET read = 1 WHERE id = ?");
        $stmt->execute([$id]);
        return true;
    }
    
    // Order functions
    public function getAllOrders() {
        $stmt = $this->db->prepare("SELECT * FROM orders ORDER BY timestamp DESC");
        $stmt->execute();
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get items for each order
        foreach ($orders as &$order) {
            $itemStmt = $this->db->prepare("SELECT * FROM order_items WHERE order_id = ?");
            $itemStmt->execute([$order['id']]);
            $order['items'] = $itemStmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        return $orders;
    }
    
    public function getOrderById($id) {
        $stmt = $this->db->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->execute([$id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($order) {
            $itemStmt = $this->db->prepare("SELECT * FROM order_items WHERE order_id = ?");
            $itemStmt->execute([$id]);
            $order['items'] = $itemStmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        return $order;
    }
    
    public function updateOrderStatus($id, $status) {
        $stmt = $this->db->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);
        return true;
    }
    
    // Admin functions
    public function getAdminByUsername($username) {
        $stmt = $this->db->prepare("SELECT * FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Recent activities for dashboard
    public function getRecentActivities($limit = 10) {
        $activities = [];
        
        // Get recent registrations
        $stmt = $this->db->prepare("
            SELECT id, name, event_id, timestamp, 'registration' as type 
            FROM registrations 
            ORDER BY timestamp DESC 
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        $registrations = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($registrations as $reg) {
            $eventStmt = $this->db->prepare("SELECT name FROM events WHERE id = ?");
            $eventStmt->execute([$reg['event_id']]);
            $event = $eventStmt->fetch(PDO::FETCH_ASSOC);
            
            $activities[] = [
                'id' => 'reg_' . $reg['id'],
                'type' => 'registration',
                'timestamp' => $reg['timestamp'],
                'message' => $reg['name'] . ' s\'est inscrit(e) à "' . ($event ? $event['name'] : 'un événement') . '"',
                'related_id' => $reg['id']
            ];
        }
        
        // Get recent donations
        $stmt = $this->db->prepare("
            SELECT id, name, type, amount, timestamp, 'donation' as type 
            FROM donations 
            ORDER BY timestamp DESC 
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        $donations = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($donations as $donation) {
            $donationMsg = '';
            if ($donation['type'] === 'money') {
                $donationMsg = $donation['name'] . ' a fait un don de ' . $donation['amount'] . '€';
            } else {
                $donationMsg = $donation['name'] . ' a fait un don de type ' . $donation['type'];
            }
            
            $activities[] = [
                'id' => 'don_' . $donation['id'],
                'type' => 'donation',
                'timestamp' => $donation['timestamp'],
                'message' => $donationMsg,
                'related_id' => $donation['id']
            ];
        }
        
        // Get recent orders
        $stmt = $this->db->prepare("
            SELECT id, customer_name, total, timestamp, 'order' as type 
            FROM orders 
            ORDER BY timestamp DESC 
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($orders as $order) {
            $activities[] = [
                'id' => 'ord_' . $order['id'],
                'type' => 'order',
                'timestamp' => $order['timestamp'],
                'message' => $order['customer_name'] . ' a passé une commande de ' . $order['total'] . '€',
                'related_id' => $order['id']
            ];
        }
        
        // Get recent messages
        $stmt = $this->db->prepare("
            SELECT id, name, subject, timestamp, 'message' as type 
            FROM messages 
            ORDER BY timestamp DESC 
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($messages as $msg) {
            $activities[] = [
                'id' => 'msg_' . $msg['id'],
                'type' => 'message',
                'timestamp' => $msg['timestamp'],
                'message' => 'Nouveau message de ' . $msg['name'] . ': "' . ($msg['subject'] ?: 'Sans objet') . '"',
                'related_id' => $msg['id']
            ];
        }
        
        // Sort all activities by timestamp descending
        usort($activities, function($a, $b) {
            return strtotime($b['timestamp']) - strtotime($a['timestamp']);
        });
        
        // Limit the result
        return array_slice($activities, 0, $limit);
    }
/**
 * Delete an event by ID
 * 
 * @param int $id The event ID to delete
 * @return bool Success status
 */
public function deleteEvent($id) {
    $stmt = $this->db->prepare("DELETE FROM events WHERE id = ?");
    $stmt->execute([$id]);
    return true;
}

/**
 * Delete a registration by ID
 * 
 * @param int $id The registration ID to delete
 * @return bool Success status
 */
public function deleteRegistration($id) {
    $stmt = $this->db->prepare("DELETE FROM registrations WHERE id = ?");
    $stmt->execute([$id]);
    return true;
}

/**
 * Delete a message by ID
 * 
 * @param int $id The message ID to delete
 * @return bool Success status
 */
public function deleteMessage($id) {
    $stmt = $this->db->prepare("DELETE FROM messages WHERE id = ?");
    $stmt->execute([$id]);
    return true;
}

/**
 * Delete an order by ID
 * 
 * @param int $id The order ID to delete
 * @return bool Success status
 */
public function deleteOrder($id) {
    try {
        $conn = $this->db->getConnection();
        $conn->beginTransaction();
        
        // First delete related order items
        $itemsStmt = $conn->prepare("DELETE FROM order_items WHERE order_id = ?");
        $itemsStmt->execute([$id]);
        
        // Then delete the order
        $orderStmt = $conn->prepare("DELETE FROM orders WHERE id = ?");
        $orderStmt->execute([$id]);
        
        $conn->commit();
        return true;
    } catch (Exception $e) {
        $conn->rollBack();
        throw $e;
    }
}
/**
 * Update an event
 * 
 * @param int $id The event ID
 * @param array $event The updated event data
 * @return bool Success status
 */
public function updateEvent($id, $event) {
    try {
        $conn = $this->db->getConnection();
        $conn->beginTransaction();
        
        // Update event fields
        $fields = [];
        $params = [];
        
        if (isset($event['name'])) {
            $fields[] = "name = ?";
            $params[] = $event['name'];
        }
        
        if (isset($event['date'])) {
            $fields[] = "date = ?";
            $params[] = $event['date'];
        }
        
        if (isset($event['description'])) {
            $fields[] = "description = ?";
            $params[] = $event['description'];
        }
        
        if (isset($event['type'])) {
            $fields[] = "type = ?";
            $params[] = $event['type'];
        }
        
        if (isset($event['image'])) {
            $fields[] = "image = ?";
            $params[] = $event['image'];
        }
        
        // Only update if there are fields to update
        if (!empty($fields)) {
            $sql = "UPDATE events SET " . implode(", ", $fields) . " WHERE id = ?";
            $params[] = $id;
            
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
        }
        
        $conn->commit();
        return true;
    } catch (Exception $e) {
        $conn->rollBack();
        throw $e;
    }
}
}