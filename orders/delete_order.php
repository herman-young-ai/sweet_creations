<?php 
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once '../includes/config.php';
require_once '../includes/functions.php';

// Ensure user is logged in
requireLogin();

// Check if order ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: orders.php?status=invalid_id');
    exit;
}

$orderId = (int)$_GET['id'];

// Get order details to check if it can be deleted
$order = getOrderById($orderId);

if (!$order) {
    header('Location: orders.php?status=not_found');
    exit;
}

// Check if order can be deleted (not "Delivered")
if (strtolower($order['order_status']) == 'delivered') {
    header('Location: orders.php?status=delivered_order');
    exit;
}

// If we get here, the order can be deleted
// Delete the order (cascade will delete order items automatically)
$conn = connectDB();
if (!$conn) {
    header('Location: orders.php?status=error');
    exit;
}

try {
    $stmt = $conn->prepare("DELETE FROM ORDERS WHERE order_id = :id");
    $stmt->bindParam(':id', $orderId, PDO::PARAM_INT);
    
    if ($stmt->execute()) {
        header('Location: orders.php?status=deleted');
    } else {
        header('Location: orders.php?status=error');
    }
} catch (PDOException $e) {
    error_log("Delete order error: " . $e->getMessage());
    header('Location: orders.php?status=error');
} finally {
    $conn = null;
}
exit;
?> 